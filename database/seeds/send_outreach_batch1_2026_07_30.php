#!/usr/bin/env php
<?php
/**
 * One-time outreach send — first real batch, 2026-07-30.
 *
 * Creates and sends 4 campaigns (one per matched persona template) to the
 * 7 seeded targets that have a confirmed email, excluding Iyorwuese Hagher
 * (politician) per Charles's explicit instruction — his email
 * (permission@hagher.com) looks like a narrow-purpose rights/permissions
 * inbox, not a general contact address, so no politician campaign is
 * created at all this run.
 *
 * Mirrors AdminInfluentialController::sendCampaign() exactly (same models,
 * same OutreachMailer calls) so this behaves identically to clicking "Send"
 * in the admin UI — it's not a shortcut/bypass.
 *
 * Every send BCCs SITE_EMAIL (contact@tivheritage.com) automatically per
 * the OutreachMailer::send() change made earlier this session.
 *
 * Run: DB_CLI_PASS='...' php database/seeds/send_outreach_batch1_2026_07_30.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php'; // defines the Database class (not auto-required by config.php)
require_once BASE_PATH . '/models/EmailCampaign.php';
require_once BASE_PATH . '/models/InfluentialPerson.php';
require_once BASE_PATH . '/services/OutreachMailer.php';

$dbPass = getenv('DB_CLI_PASS');
if ($dbPass === false || $dbPass === '') {
    fwrite(STDERR, "Set DB_CLI_PASS environment variable before running this script.\n");
    exit(1);
}

// Database::getInstance() reads DB_USER/DB_PASS from config/database.php (root@127.0.0.1
// for CLI) which doesn't match this MySQL instance's actual root credentials — same
// pre-existing issue noted in seed_outreach_targets.php. Bypass it the same way.
$pdo = new PDO(
    'mysql:host=localhost;dbname=tiv_archive;charset=utf8mb4',
    'tivuser',
    $dbPass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Models expect Database::getInstance() internally — reflect our PDO into it.
$ref = new ReflectionClass('Database');
$prop = $ref->getProperty('instance');
$prop->setAccessible(true);
$prop->setValue(null, $pdo);

function line(string $text = ''): void { echo $text . PHP_EOL; }

$ADMIN_USER_ID = 1;

$campaignsModel = new EmailCampaign();
$peopleModel    = new InfluentialPerson();

// name => [template_id, [categories]]
$batches = [
    'Academic Outreach — Batch 1 (2026-07-30)'      => [2, ['academic']],
    'Clergy Outreach — Batch 1 (2026-07-30)'         => [3, ['clergy']],
    'Diaspora Outreach — Batch 1 (2026-07-30)'       => [5, ['diaspora']],
    'Institutional Outreach — Batch 1 (2026-07-30)'  => [1, ['other']],
];

foreach ($batches as $campaignName => [$templateId, $categories]) {
    line();
    line("=== {$campaignName} ===");

    $campaignId = $campaignsModel->create([
        'name'              => $campaignName,
        'template_id'       => $templateId,
        'target_categories' => json_encode($categories),
        'schedule_type'     => 'once',
        'status'            => 'draft',
        'total_sent'        => 0,
        'created_by'        => $ADMIN_USER_ID,
    ]);

    $campaign  = $campaignsModel->getDetail($campaignId);
    $recipients = $peopleModel->getForCampaign($categories);

    // Extra safety: explicitly exclude Hagher even if a future category change would match him.
    $recipients = array_filter($recipients, fn($p) => stripos($p['name'], 'Hagher') === false);

    if (empty($recipients)) {
        line('  No eligible recipients — skipping.');
        continue;
    }

    $siteUrl = defined('SITE_URL') ? SITE_URL : '';
    $sent = 0;

    foreach ($recipients as $person) {
        if (empty($person['email'])) continue;

        $unsubUrl = $person['unsubscribe_token']
            ? $siteUrl . '/outreach/unsubscribe/' . $person['unsubscribe_token']
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
            line("  \e[0;32m✔\e[0m  {$person['name']} <{$person['email']}>");
        } else {
            line("  \e[0;31m✘\e[0m  {$person['name']} <{$person['email']}> — mail() returned false");
        }
    }

    $campaignsModel->markSent($campaignId, $sent);
    line("  Campaign #{$campaignId}: {$sent}/" . count($recipients) . ' sent.');
}

line();
line('Done.');
