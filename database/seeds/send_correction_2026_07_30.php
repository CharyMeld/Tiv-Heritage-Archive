#!/usr/bin/env php
<?php
/**
 * Correction follow-up — the 8 outreach emails sent earlier today
 * (2026-07-30) went out with a broken "http://localhost/Tiv-Heritage-Archive"
 * link instead of the real site URL, because SITE_URL derivation in
 * config/config.php didn't handle CLI (no HTTP_HOST) correctly. That's now
 * fixed. This sends a short, honest correction to the same 8 recipients
 * (all categories with a confirmed email except 'politician' — Hagher was
 * deliberately excluded from outreach entirely per Charles's instruction).
 *
 * Run: DB_CLI_PASS='...' php database/seeds/send_correction_2026_07_30.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/models/EmailCampaign.php';
require_once BASE_PATH . '/models/EmailTemplate.php';
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

function line(string $text = ''): void { echo $text . PHP_EOL; }

$ADMIN_USER_ID = 1;

$templateModel  = new EmailTemplate();
$campaignsModel = new EmailCampaign();
$peopleModel    = new InfluentialPerson();

$templateId = $pdo->query("SELECT id FROM email_templates WHERE name = 'Correction — Broken Link Follow-up'")->fetchColumn();

if (!$templateId) {
    $body = <<<'HTML'
<p>Dear {{name}},</p>

<p>Quick follow-up to my email earlier today: the link to {{site_name}} in that message was broken due to a technical issue on our end &mdash; it pointed to a placeholder address instead of the live site. Apologies for that.</p>

<p>The correct link is: <a href="{{site_url}}">{{site_url}}</a></p>

<p>Thank you for your patience, and please don't hesitate to reach out with any questions.</p>
HTML;

    $templateId = $pdo->prepare(
        'INSERT INTO email_templates (name, subject, body_html, created_by) VALUES (?, ?, ?, ?)'
    );
    $templateId->execute(['Correction — Broken Link Follow-up', 'Quick correction — broken link in my last email', $body, $ADMIN_USER_ID]);
    $templateId = $pdo->lastInsertId();
    line("Created correction template — id {$templateId}");
}

$campaignName = 'Correction Follow-up — Batch 1 (2026-07-30)';
$categories   = ['academic', 'clergy', 'diaspora', 'other'];

$campaignId = $campaignsModel->create([
    'name'              => $campaignName,
    'template_id'       => $templateId,
    'target_categories' => json_encode($categories),
    'schedule_type'     => 'once',
    'status'            => 'draft',
    'total_sent'        => 0,
    'created_by'        => $ADMIN_USER_ID,
]);

$campaign   = $campaignsModel->getDetail($campaignId);
$recipients = $peopleModel->getForCampaign($categories);
$recipients = array_filter($recipients, fn($p) => stripos($p['name'], 'Hagher') === false);

line("=== {$campaignName} — " . count($recipients) . ' recipients ===');

$sent = 0;
foreach ($recipients as $person) {
    if (empty($person['email'])) continue;

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
    $campaignsModel->recordSend($campaignId, (int) $person['id'], $person['email'], $status);

    if ($ok) {
        $sent++;
        line("  \e[0;32m✔\e[0m  {$person['name']} <{$person['email']}>");
    } else {
        line("  \e[0;31m✘\e[0m  {$person['name']} <{$person['email']}> — failed");
    }
}

$campaignsModel->markSent($campaignId, $sent);
line("Done. {$sent}/" . count($recipients) . ' sent.');
