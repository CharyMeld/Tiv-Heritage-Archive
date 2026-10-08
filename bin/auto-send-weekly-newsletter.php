#!/usr/bin/env php
<?php
/**
 * Weekly cron entrypoint: generates a fresh "weekly_summary" newsletter
 * issue from live archive data, auto-approves it, and sends it to
 * registered members (users + community_members) AND newsletter_subscribers
 * — deliberately NOT the influential_people outreach list, per Charles's
 * instructions (2026-08-12).
 *
 * Intended to run via cron every Friday. Reuses bin/send-newsletter-issue.php
 * in --members and --subscribers modes for the actual send (run back to
 * back against the same issue), so audience logic, the unsubscribe-link
 * injection, and the newsletter_campaign_sends log all stay identical to a
 * manual run. Anyone present in both lists (e.g. a registered member who
 * also opted into the newsletter) is only sent to once — the second pass
 * skips them as already-sent for this issue_id.
 *
 * Safety: aborts (exit 1, nothing sent) if generation or saving fails or
 * produces no content; holds a MySQL named lock so runs can't overlap; and a
 * re-run on the same date resumes that date's issue instead of creating a
 * second one.
 *
 * Run: php bin/auto-send-weekly-newsletter.php
 *      php bin/auto-send-weekly-newsletter.php --dry-run   (generate + print only)
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/MarketingNewsletterIssue.php';
require_once BASE_PATH . '/services/NewsletterGeneratorService.php';

$issues = new MarketingNewsletterIssue();
$dryRun = in_array('--dry-run', $argv, true);
$issueDate = date('Y-m-d');

function abortRun(string $reason): never
{
    echo date('Y-m-d H:i:s') . " ABORTED: {$reason} — nothing was sent.\n";
    exit(1);
}

// One run at a time: an overlapping run (cron + a manual retry) waits for
// nothing — it just stops, rather than generating a second issue.
$db = Database::getInstance();
if (!$dryRun && (int) $db->query("SELECT GET_LOCK('tiv_weekly_newsletter', 0)")->fetchColumn() !== 1) {
    abortRun('another weekly newsletter run is already in progress');
}

// Re-running on the same date (e.g. after a partial send failure) resumes
// that date's issue instead of generating and sending a second one;
// send-newsletter-issue.php skips everyone already sent it.
$existing = $dryRun ? null : $issues->approvedForDate('weekly_summary', $issueDate);

if ($existing) {
    $id = (int) $existing['id'];
    echo date('Y-m-d H:i:s') . " Resuming issue #{$id} already generated for {$issueDate}: \"{$existing['subject']}\"\n";
} else {
    try {
        $content = NewsletterGeneratorService::generate('weekly_summary');
    } catch (\Throwable $e) {
        abortRun('generation failed: ' . $e->getMessage());
    }

    if ($dryRun) {
        echo "DRY RUN — not stored, not sent.\nSubject: {$content['subject']}\n\n{$content['text_body']}";
        exit(0);
    }

    try {
        $id = $issues->createWithItems([
            'issue_type' => 'weekly_summary',
            'issue_date' => $issueDate,
            'subject'    => $content['subject'],
            'html_body'  => $content['html_body'],
            'text_body'  => $content['text_body'],
            'status'     => 'approved',
            'created_by' => 1, // Administrator
        ], $content['items']);
    } catch (\Throwable $e) {
        abortRun('could not save the issue (rolled back): ' . $e->getMessage());
    }

    echo date('Y-m-d H:i:s') . " Generated + auto-approved issue #{$id}: \"{$content['subject']}\"\n";
}

$sendScript = BASE_PATH . '/bin/send-newsletter-issue.php';

passthru(PHP_BINARY . ' ' . escapeshellarg($sendScript) . ' ' . (int) $id . ' --members', $membersExit);
passthru(PHP_BINARY . ' ' . escapeshellarg($sendScript) . ' ' . (int) $id . ' --subscribers', $subscribersExit);

exit(max($membersExit, $subscribersExit));
