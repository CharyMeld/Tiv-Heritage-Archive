#!/usr/bin/env php
<?php
/**
 * Sends an approved marketing_newsletter_issues row to an audience of
 * registered emails. Injects a per-recipient unsubscribe link before
 * sending, since the stored html_body has none baked in.
 *
 * Usage:
 *   php bin/send-newsletter-issue.php <issue_id> --test
 *   php bin/send-newsletter-issue.php <issue_id> --subscribers
 *   php bin/send-newsletter-issue.php <issue_id> --members
 *
 * --test sends only to cikyese@gmail.com.
 * --subscribers sends to newsletter_subscribers (status='subscribed') only
 *   — the actual newsletter opt-in list.
 * --members sends to all registered users + community_members (excluding
 *   admin@tivarchive.com) — for campaigns aimed at the whole membership,
 *   not just newsletter opt-ins.
 * Both non-test modes skip anyone already logged as 'sent' for this
 * issue_id, and anyone in email_unsubscribes.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/EmailUnsubscribe.php';
require_once BASE_PATH . '/services/OutreachMailer.php';

$issueId = isset($argv[1]) ? (int) $argv[1] : 0;
$mode = $argv[2] ?? '';

if ($issueId <= 0 || !in_array($mode, ['--test', '--subscribers', '--members'], true)) {
    fwrite(STDERR, "Usage: php bin/send-newsletter-issue.php <issue_id> --test|--subscribers|--members\n");
    exit(1);
}

$db = Database::getInstance();

$stmt = $db->prepare("SELECT * FROM marketing_newsletter_issues WHERE id = ? LIMIT 1");
$stmt->execute([$issueId]);
$issue = $stmt->fetch();

if (!$issue) {
    fwrite(STDERR, "No marketing_newsletter_issues row with id={$issueId}\n");
    exit(1);
}

if ($issue['status'] !== 'approved') {
    fwrite(STDERR, "Refusing to send: issue {$issueId} has status '{$issue['status']}', not 'approved'.\n");
    exit(1);
}

if ($mode === '--test') {
    $recipients = [
        ['email' => 'cikyese@gmail.com', 'name' => 'Charles Ihungwa Ikyese'],
    ];
} elseif ($mode === '--subscribers') {
    $rows = $db->query("
        SELECT email, name FROM newsletter_subscribers
        WHERE status = 'subscribed' AND email != 'admin@tivarchive.com'
    ")->fetchAll();
    $recipients = $rows;
} else {
    $sql = "
        SELECT email, name FROM users WHERE email != 'admin@tivarchive.com'
        UNION
        SELECT email, full_name AS name FROM community_members WHERE email != 'admin@tivarchive.com'
    ";
    $rows = $db->query($sql)->fetchAll();

    $byEmail = [];
    foreach ($rows as $row) {
        $email = strtolower(trim($row['email']));
        if (!isset($byEmail[$email])) {
            $byEmail[$email] = ['email' => $row['email'], 'name' => $row['name']];
        }
    }
    $recipients = array_values($byEmail);
}

$unsubscribes = new EmailUnsubscribe();

$insertSend = $db->prepare(
    "INSERT INTO newsletter_campaign_sends (issue_id, email, name, status, error)
     VALUES (?, ?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE status = VALUES(status), error = VALUES(error), sent_at = CURRENT_TIMESTAMP"
);
$alreadySentStmt = $db->prepare(
    "SELECT 1 FROM newsletter_campaign_sends WHERE issue_id = ? AND email = ? AND status = 'sent' LIMIT 1"
);

$sent = 0;
$skipped = 0;
$failed = 0;

foreach ($recipients as $r) {
    $email = $r['email'];
    $name = $r['name'] ?: $email;

    $alreadySentStmt->execute([$issueId, $email]);
    if ($alreadySentStmt->fetch()) {
        echo "SKIP (already sent): {$email}\n";
        $skipped++;
        continue;
    }

    if ($unsubscribes->isUnsubscribed($email)) {
        $insertSend->execute([$issueId, $email, $name, 'skipped_unsubscribed', null]);
        echo "SKIP (unsubscribed): {$email}\n";
        $skipped++;
        continue;
    }

    $token = $unsubscribes->getOrCreateToken($email);
    $unsubscribeUrl = rtrim(SITE_URL, '/') . '/newsletter/unsubscribe/' . $token;

    $unsubBlock = '<p style="font-size:11px;color:#999;margin-top:24px;">'
        . 'If you no longer wish to receive these emails, you may '
        . '<a href="' . htmlspecialchars($unsubscribeUrl, ENT_QUOTES, 'UTF-8') . '">unsubscribe here</a>.'
        . '</p>';

    // The stored html_body already has the full branded shell (header/footer)
    // baked in by NewsletterGeneratorService, so we inject the unsubscribe
    // block right before the footer row rather than re-wrapping via
    // OutreachMailer::buildBody() (which would double-wrap the shell).
    $htmlBody = $issue['html_body'];
    $footerMarker = '<tr>
            <td style="background:#f7f4ee;';
    if (str_contains($htmlBody, $footerMarker)) {
        $htmlBody = str_replace($footerMarker, $unsubBlock . $footerMarker, $htmlBody);
    } else {
        $htmlBody .= $unsubBlock;
    }

    $ok = OutreachMailer::send($email, $name, $issue['subject'], $htmlBody);

    if ($ok) {
        $insertSend->execute([$issueId, $email, $name, 'sent', null]);
        echo "SENT: {$email}\n";
        $sent++;
    } else {
        $insertSend->execute([$issueId, $email, $name, 'failed', 'mail() returned false']);
        echo "FAILED: {$email}\n";
        $failed++;
    }
}

echo "\nDone. sent={$sent} skipped={$skipped} failed={$failed}\n";
