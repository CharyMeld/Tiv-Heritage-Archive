#!/usr/bin/env php
<?php
/**
 * One-time "Call for Support" campaign — asks existing influential_people
 * outreach contacts (mostly already introduced to the site in earlier
 * batches) for either hands-on help or a financial contribution, with
 * Charles's personal bank details as the donation route until a dedicated
 * project account exists.
 *
 * Creates the email_templates + email_campaigns rows on first run (idempotent
 * by name — reruns reuse the existing rows instead of duplicating them), then
 * sends. Mirrors AdminInfluentialController::sendCampaign() / the 2026-07-30
 * batch script: same models, same OutreachMailer calls.
 *
 * Usage:
 *   php bin/send-support-campaign.php --test
 *   php bin/send-support-campaign.php --send
 *
 * --test sends only to cikyese@gmail.com (rendered exactly as a real
 *   recipient would see it) and does NOT touch campaign_sends/emails_sent.
 * --send sends to every influential_people contact eligible via
 *   InfluentialPerson::getForCampaign([]) (all categories), minus the
 *   explicit EXCLUDE_IDS below, and logs each send.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/EmailCampaign.php';
require_once BASE_PATH . '/models/InfluentialPerson.php';
require_once BASE_PATH . '/services/OutreachMailer.php';

$mode = $argv[1] ?? '';
if (!in_array($mode, ['--test', '--send'], true)) {
    fwrite(STDERR, "Usage: php bin/send-support-campaign.php --test|--send\n");
    exit(1);
}

// Explicitly excluded per Charles's instruction (2026-08-12): these 4 should
// not receive the donation ask.
//   13 = HRM Prof. James Ayatse (Tor Tiv V)
//   18 = Sam Ode
//    8 = NKST Church — Abuja Branch
//   31 = Association of Tiv Authors (ATA)
const EXCLUDE_IDS = [13, 18, 8, 31];

const CAMPAIGN_NAME = 'Call for Support — Time or Funds (Aug 2026)';
const TEMPLATE_NAME = 'Call for Support — Financial & Volunteer';

const SUBJECT = 'How you can help grow the Tiv Heritage Archive';

const BODY_HTML = <<<HTML
<p>Dear {{name}},</p>

<p>I previously wrote to introduce {{site_name}} ({{site_url}}), a free, public digital archive documenting Tiv language, history, and culture. I am writing again with a specific request.</p>

<p>The Archive is built entirely through community effort, and there is still a great deal of work ahead: collecting words, proverbs, oral histories, festivals, and biographies before they are lost. Much of this work depends on paying researchers, writers, and data-entry contributors to carry it out properly and at pace.</p>

<p>If you or your organization are able to support this work, whether through your time, your network, or a financial contribution, it would make a real difference. Contributions can be sent directly to:</p>

<p><strong>Beneficiary name:</strong> Charles Ihungwa Ikyese<br>
<strong>Account number:</strong> 1034641024<br>
<strong>Bank:</strong> Guaranty Trust Bank (GTBank)</p>

<p>(A dedicated project account is in the works; for now, gifts go directly toward funding contributors' work.)</p>

<p>If you would simply like to point us to a resource, or connect us with someone who can help, that is just as valuable, you are welcome to reply directly to this email.</p>

<p>Thank you for considering this, and for your support of Tiv heritage.</p>

<p>Warm regards,<br>
Charles Ikyese<br>
{{site_name}}</p>
HTML;

$db = Database::getInstance();

function line(string $text = ''): void { echo $text . PHP_EOL; }

if ($mode === '--test') {
    $body = OutreachMailer::buildBody(BODY_HTML, [
        'name'            => 'Test Recipient',
        'title'           => '',
        'unsubscribe_url' => SITE_URL . '/outreach/unsubscribe/test-token-preview',
    ]);
    $ok = OutreachMailer::send('cikyese@gmail.com', 'Charles Ihungwa Ikyese', SUBJECT, $body);
    line($ok ? 'SENT test to cikyese@gmail.com' : 'FAILED to send test');
    exit($ok ? 0 : 1);
}

// --send: create template + campaign if they don't already exist (idempotent by name).
$stmt = $db->prepare("SELECT id FROM email_templates WHERE name = ? LIMIT 1");
$stmt->execute([TEMPLATE_NAME]);
$templateId = $stmt->fetchColumn();

if (!$templateId) {
    $stmt = $db->prepare("INSERT INTO email_templates (name, subject, body_html, created_by) VALUES (?, ?, ?, 1)");
    $stmt->execute([TEMPLATE_NAME, SUBJECT, BODY_HTML]);
    $templateId = (int) $db->lastInsertId();
    line("Created template #{$templateId}");
} else {
    $templateId = (int) $templateId;
    line("Reusing existing template #{$templateId}");
}

$campaignsModel = new EmailCampaign();
$peopleModel    = new InfluentialPerson();

$stmt = $db->prepare("SELECT id FROM email_campaigns WHERE name = ? LIMIT 1");
$stmt->execute([CAMPAIGN_NAME]);
$campaignId = $stmt->fetchColumn();

if (!$campaignId) {
    $campaignId = $campaignsModel->create([
        'name'              => CAMPAIGN_NAME,
        'template_id'       => $templateId,
        'target_categories' => null,
        'schedule_type'     => 'once',
        'status'            => 'draft',
        'total_sent'        => 0,
        'created_by'        => 1,
    ]);
    line("Created campaign #{$campaignId}");
} else {
    $campaignId = (int) $campaignId;
    line("Reusing existing campaign #{$campaignId}");
}

$campaign = $campaignsModel->getDetail($campaignId);
$recipients = $peopleModel->getForCampaign([]); // all categories
$recipients = array_filter($recipients, fn($p) => !in_array((int) $p['id'], EXCLUDE_IDS, true));

if (empty($recipients)) {
    line('No eligible recipients.');
    exit(0);
}

$sent = 0;
$alreadySent = 0;

// Avoid double-sending on reruns: skip anyone already logged as 'sent' for this campaign.
$sentStmt = $db->prepare("SELECT 1 FROM campaign_sends WHERE campaign_id = ? AND person_id = ? AND status = 'sent' LIMIT 1");

foreach ($recipients as $person) {
    if (empty($person['email'])) continue;

    $sentStmt->execute([$campaignId, $person['id']]);
    if ($sentStmt->fetch()) {
        line("  SKIP (already sent): {$person['name']} <{$person['email']}>");
        $alreadySent++;
        continue;
    }

    $unsubUrl = $person['unsubscribe_token']
        ? rtrim(SITE_URL, '/') . '/outreach/unsubscribe/' . $person['unsubscribe_token']
        : '';

    $body = OutreachMailer::buildBody($campaign['body_html'], [
        'name'            => $person['name'],
        'title'           => $person['title'] ?? '',
        'unsubscribe_url' => $unsubUrl,
    ]);

    $ok = OutreachMailer::send($person['email'], $person['name'], $campaign['subject'], $body);
    $status = $ok ? 'sent' : 'failed';
    $campaignsModel->recordSend($campaignId, (int) $person['id'], $person['email'], $status);

    if ($ok) {
        $peopleModel->markContacted((int) $person['id']);
        $sent++;
        line("  \xE2\x9C\x94  {$person['name']} <{$person['email']}>");
    } else {
        line("  \xE2\x9C\x98  {$person['name']} <{$person['email']}> — mail() returned false");
    }
}

$totalPriorSent = (int) $db->query("SELECT COUNT(*) FROM campaign_sends WHERE campaign_id = {$campaignId} AND status = 'sent'")->fetchColumn();
$campaignsModel->markSent($campaignId, $totalPriorSent);

line();
line("Done. sent={$sent} already_sent_skipped={$alreadySent} total_recipients=" . count($recipients));
