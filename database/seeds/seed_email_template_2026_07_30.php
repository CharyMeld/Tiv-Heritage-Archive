#!/usr/bin/env php
<?php
/**
 * Creates one general-purpose outreach email template so a campaign can be
 * created (admin/outreach/campaigns blocks campaign creation until at least
 * one template exists). Inserted directly, not sent — this only creates the
 * template row; sending a campaign is a separate, explicit action.
 *
 * body_html is the INNER content only — OutreachMailer::wrapHtml() adds the
 * header/footer/unsubscribe chrome automatically at send time.
 *
 * Run: DB_CLI_PASS='...' php database/seeds/seed_email_template_2026_07_30.php
 */

define('BASE_PATH', dirname(__DIR__, 2));
require_once BASE_PATH . '/config/config.php';

$dbPass = getenv('DB_CLI_PASS');
if ($dbPass === false || $dbPass === '') {
    fwrite(STDERR, "Set DB_CLI_PASS environment variable before running this script.\n");
    exit(1);
}

$pdo = new PDO(
    'mysql:host=localhost;dbname=tiv_archive;charset=utf8mb4',
    'tivuser',
    $dbPass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$ADMIN_USER_ID = 1;

$name    = 'General Introduction';
$subject = 'Introducing the Tiv Heritage Archive';
$body    = <<<'HTML'
<p>Dear {{name}},</p>

<p>My name is Charles Ikyese, and I'm writing to introduce a project I think may be of interest to you: <strong>{{site_name}}</strong> ({{site_url}}), a free, public digital archive documenting the Tiv language and culture.</p>

<p>It brings together a Tiv-English dictionary with phonetic and tonal detail, the Tiv alphabet and tone system, traditional proverbs, names, festivals, foods, historical figures, and more &mdash; built with the goal of preserving this material for the next generation, and making it freely accessible to anyone researching, teaching, or simply reconnecting with Tiv heritage.</p>

<p>Given your work as {{title}}, I thought this might be relevant to you &mdash; whether as a resource to reference, share with others, or contribute to directly. I'd welcome the chance to tell you more, and any thoughts or corrections you might have would be genuinely valuable.</p>

<p>Thank you for your time, and for everything you've done for the Tiv community.</p>

<p>Warm regards,<br>Charles Ikyese<br>{{site_name}}</p>
HTML;

$stmt = $pdo->prepare('SELECT id FROM email_templates WHERE name = ? LIMIT 1');
$stmt->execute([$name]);
if ($stmt->fetch()) {
    echo "Template \"{$name}\" already exists — skipped.\n";
    exit(0);
}

$stmt = $pdo->prepare(
    'INSERT INTO email_templates (name, subject, body_html, created_by) VALUES (?, ?, ?, ?)'
);
$stmt->execute([$name, $subject, $body, $ADMIN_USER_ID]);

echo "Created template \"{$name}\" (id " . $pdo->lastInsertId() . ").\n";
