#!/usr/bin/env php
<?php
/**
 * One-off retry for the single failed send from campaign #4 (Institutional
 * Outreach — Batch 1) — BACT's display name contains a comma
 * ("Benue State Bureau for Arts, Culture, and Tourism (BACT)") which broke
 * unquoted RFC 5322 address parsing in the old OutreachMailer::send().
 * That's now fixed (see services/OutreachMailer.php); this just resends
 * to the one recipient that failed, without re-touching NICO (already sent).
 *
 * Run: DB_CLI_PASS='...' php database/seeds/retry_bact_send_2026_07_30.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/models/EmailCampaign.php';
require_once BASE_PATH . '/models/InfluentialPerson.php';
require_once BASE_PATH . '/services/OutreachMailer.php';

$dbPass = getenv('DB_CLI_PASS');
if (!$dbPass) { fwrite(STDERR, "Set DB_CLI_PASS.\n"); exit(1); }

$pdo = new PDO('mysql:host=localhost;dbname=tiv_archive;charset=utf8mb4', 'tivuser', $dbPass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$ref = new ReflectionClass('Database');
$prop = $ref->getProperty('instance');
$prop->setAccessible(true);
$prop->setValue(null, $pdo);

$campaignsModel = new EmailCampaign();
$peopleModel    = new InfluentialPerson();

$campaignId = 4;
$personId   = 14; // BACT

$campaign = $campaignsModel->getDetail($campaignId);
$person   = $peopleModel->find($personId);

$unsubUrl = $person['unsubscribe_token']
    ? SITE_URL . '/outreach/unsubscribe/' . $person['unsubscribe_token']
    : '';

$body = OutreachMailer::buildBody($campaign['body_html'], [
    'name'            => $person['name'],
    'title'           => $person['title'] ?? '',
    'unsubscribe_url' => $unsubUrl,
]);

$ok = OutreachMailer::send($person['email'], $person['name'], $campaign['subject'], $body);
$status = $ok ? 'sent' : 'failed';

$pdo->prepare("INSERT INTO campaign_sends (campaign_id, person_id, email, status) VALUES (?, ?, ?, ?)")
    ->execute([$campaignId, $personId, $person['email'], $status]);

if ($ok) {
    $peopleModel->markContacted($personId);
    $pdo->prepare("UPDATE email_campaigns SET total_sent = total_sent + 1 WHERE id = ?")->execute([$campaignId]);
    echo "OK: sent to {$person['name']} <{$person['email']}>\n";
} else {
    echo "FAILED again: {$person['name']} <{$person['email']}>\n";
}
