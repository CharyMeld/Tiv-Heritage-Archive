<?php
/**
 * Image refill 001 — Benue State (NIGERIA_IMAGE_REFILL_PROGRESS.md; owner brief and decisions of 3 Oct 2026).
 *
 *   A  research_batches row "Image refill 001 — Benue State" (all rows below point to it).
 *   B  media_assets + media_links (role 'depicts') for each verified, freely licensed image in
 *      data/img_001_benue.json; the record's empty `image` column is set to the file so the hero shows.
 *      Picks marked owner_check are only applied with --include-owner-check.
 *   C  research_gaps rows "Image not found: …" for records with no suitable free image, with the reason
 *      and any non-free candidate pages (not downloaded, not shown).
 *
 * Image files must already be in uploads/images/ (with -400w/-800w variants). Never overwrites a
 * non-empty `image`. Old values go to activity_log; --revert removes everything this script added.
 * Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/services/StableId.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$ownerCheck = in_array('--include-owner-check', $argv, true);
$db = Database::getInstance();
$AGENT = 'img_001_benue.php';
$BATCH_TITLE = 'Image refill 001 — Benue State';
// Same list as HeritagePublic::LICENCE_URL — owner decision: free licences only.
$FREE = ['CC0', 'Public domain', 'CC BY 2.0', 'CC BY 3.0', 'CC BY 4.0', 'CC BY-SA 2.0', 'CC BY-SA 3.0', 'CC BY-SA 4.0'];
$picks = json_decode(file_get_contents(__DIR__ . '/data/img_001_benue.json'), true, 512, JSON_THROW_ON_ERROR);

$GAPS = [
    ['places', 78, 'National Museum, Makurdi', 'No photograph of the museum on Wikimedia Commons (searched "National Museum Makurdi", "Makurdi"). No other freely licensed photo found.'],
    ['places', 82, 'Tor Tiv Palace', 'No photograph of the palace on Wikimedia Commons. The record describes the palace as rebuilt by 2021, so older photos would not show it. Non-free candidate: Gazette Nigeria report on the 2021 palace, https://gazettengr.com/benue-spends-n1-6-billion-on-best-in-nigeria-tor-tiv-palace/ (photo licence unknown; permission needed).'],
    ['places', 80, 'Traditional iron smelting furnaces of Igede', 'No photograph on Wikimedia Commons (searched "Igede iron smelting", "Igede"; the "Igede" results are Igede-Ekiti, a different town in Ekiti State).'],
    ['cultural_records', 3, 'Igede Agba', 'No photograph of the festival on Wikimedia Commons. Non-free candidate: NICO page "Igede Agba Festival", https://nico.gov.ng/?p=109250 (images not inspected; permission needed).'],
    ['cultural_records', 4, 'Alekwu', 'No photograph identified as Alekwu on Wikimedia Commons. Idoma masquerade photos exist ("Idoma masquerade.jpg", "Eku (masquerade).jpg") but their descriptions do not say they are Alekwu masquerades, so they are not used here. Non-free candidate: "Origin of Alekwuafia: Masquerade that\'s a Poet", https://splendorsofdawn.ng/2023/12/07/origin-of-alekwuafia-masquerade-thats-a-poet/ (permission needed).'],
    ['cultural_records', 6, 'Opleka', 'No photograph of the Etulo Opleka festival found on Wikimedia Commons (searched "Opleka Etulo", "Etulo").'],
    ['ethnic_groups', 14, 'Igede', 'No photograph of the Igede people of Benue on Wikimedia Commons; the "Igede" results are Igede-Ekiti (Ekiti State), a different place.'],
    ['ethnic_groups', 15, 'Etulo', 'No photograph of the Etulo people on Wikimedia Commons.'],
    ['ethnic_groups', 16, 'Akweya', 'No photograph of the Akweya on Wikimedia Commons; the search returned only Ekoi (Ejagham) items, a different people.'],
    ['ethnic_groups', 17, 'Nyifon', 'No photograph of the Nyifon on Wikimedia Commons.'],
    ['ethnic_groups', 18, 'Ufia', 'No photograph of the Ufia (Utonkon) on Wikimedia Commons.'],
    ['ethnic_groups', 22, 'Abakpa', 'No photograph of the Abakpa (Abakwa) of Benue on Wikimedia Commons.'],
];

$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_image_added', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$db->beginTransaction();
try {
    $batchId = $db->prepare('SELECT id FROM research_batches WHERE title = ?');
    $batchId->execute([$BATCH_TITLE]);
    $batchId = $batchId->fetchColumn() ?: null;

    if ($revert) {
        foreach ($db->query("SELECT entity_type, entity_id, old_values FROM activity_log WHERE user_agent = '{$AGENT}' AND action = 'heritage_image_added' ORDER BY id DESC")->fetchAll() as $r) {
            $db->prepare("UPDATE {$r['entity_type']} SET image = ? WHERE id = ?")->execute([json_decode($r['old_values'], true)['image'], $r['entity_id']]);
            $changes++;
        }
        $db->prepare("DELETE FROM activity_log WHERE user_agent = ? AND action = 'heritage_image_added'")->execute([$AGENT]);
        foreach ($picks as $p) {
            $ids = $db->prepare('SELECT id FROM media_assets WHERE file_path = ?');
            $ids->execute([$p['file']]);
            foreach ($ids->fetchAll(PDO::FETCH_COLUMN) as $mid) {
                $db->prepare('DELETE FROM media_links WHERE media_id = ?')->execute([$mid]);
                $db->prepare('DELETE FROM media_assets WHERE id = ?')->execute([$mid]);
                $changes++;
            }
        }
        if ($batchId) {
            $changes += $db->exec('DELETE FROM research_gaps WHERE research_batch_id = ' . (int) $batchId);
            $db->exec('DELETE FROM research_batches WHERE id = ' . (int) $batchId);
        }
        $apply ? $db->commit() : $db->rollBack();
        echo "{$changes} rows reverted. " . ($apply ? "REVERTED.\n" : "DRY RUN — use --revert --apply.\n");
        exit(0);
    }

    echo "A  research batch:\n";
    if (!$batchId) {
        $db->prepare("INSERT INTO research_batches (title, scope, status, conducted_by, summary, started_at, completed_at)
                      VALUES (?, ?, 'awaiting_review', 'claude', ?, NOW(), NOW())")
           ->execute([$BATCH_TITLE, 'Image refill for existing Benue State records: heritage places, the state capital, cultural records and peoples. No history changed.',
                      'Freely licensed photographs (Wikimedia Commons) added as media_assets linked to the records they depict; records with no suitable free image recorded as research_gaps.']);
        $batchId = (int) $db->lastInsertId();
        $changes++;
        echo "  research_batches#{$batchId} created\n";
    }

    echo "B  images:\n";
    foreach ($picks as $p) {
        if (!empty($p['owner_check']) && !$ownerCheck) { echo "  SKIP {$p['file']} (owner check: {$p['owner_check']})\n"; continue; }
        if (!in_array($p['licence'], $FREE, true)) throw new RuntimeException("{$p['file']}: licence '{$p['licence']}' is not on the free list");
        if (!is_file(BASE_PATH . '/uploads/images/' . $p['file'])) throw new RuntimeException("{$p['file']}: file missing from uploads/images/");

        $mid = $db->prepare('SELECT id FROM media_assets WHERE file_path = ?');
        $mid->execute([$p['file']]);
        $mid = $mid->fetchColumn();
        if (!$mid) {
            $desc = "{$p['title']}.\n\nSource: Wikimedia Commons, {$p['source_page']}\nOriginal file: {$p['original_url']} ({$p['original_size']}; stored copy {$p['stored_size']})\n"
                  . "Creator: {$p['creator']}. Licence: {$p['licence']} ({$p['licence_url_commons']}). Attribution required: creator, licence and link. Accessed {$p['accessed']}.\n"
                  . "Verification: {$p['verified']}";
            $db->prepare("INSERT INTO media_assets (media_type, title, description, file_path, external_url, date_created, date_precision, creator, copyright_holder, licence, permission_status, sensitivity, review_status)
                          VALUES ('photo', ?, ?, ?, ?, ?, ?, ?, ?, ?, 'not_required', 'public', 'published')")
               ->execute([$p['title'], $desc, $p['file'], $p['source_page'], $p['date_created'] ?: null, $p['date_created'] ? 'exact' : 'unknown',
                          $p['creator'], $p['creator'], $p['licence']]);
            $mid = (int) $db->lastInsertId();
            StableId::assign($db, 'media_assets', $mid);
            $changes++;
            echo "  media_assets#{$mid} {$p['file']}\n";
        }
        foreach ($p['links'] as [$table, $id]) {
            $row = $db->prepare("SELECT id, image FROM {$table} WHERE id = ?");
            $row->execute([$id]);
            $row = $row->fetch();
            if (!$row) throw new RuntimeException("{$table}#{$id} not found");
            $ins = $db->prepare("INSERT IGNORE INTO media_links (media_id, entity_table, entity_id, role) VALUES (?, ?, ?, 'depicts')");
            $ins->execute([$mid, $table, $id]);
            $changes += $ins->rowCount();
            if ((string) $row['image'] === '') {
                $db->prepare("UPDATE {$table} SET image = ?, updated_at = updated_at WHERE id = ?")->execute([$p['file'], $id]);
                $log->execute([$table, $id, json_encode(['image' => $row['image']]), json_encode(['image' => $p['file'], 'media_id' => $mid])]);
                $changes++;
                echo "    {$table}#{$id} image set\n";
            } elseif ($row['image'] !== $p['file']) {
                echo "    {$table}#{$id} already has image '{$row['image']}' — linked only, not replaced\n";
            }
        }
    }

    echo "C  image gaps:\n";
    foreach ($GAPS as [$table, $id, $name, $reason]) {
        $topic = "Image not found: {$name}";
        $has = $db->prepare('SELECT 1 FROM research_gaps WHERE entity_table = ? AND entity_id = ? AND topic = ?');
        $has->execute([$table, $id, $topic]);
        if ($has->fetchColumn()) continue;
        $db->prepare("INSERT INTO research_gaps (research_batch_id, entity_table, entity_id, topic, description, status) VALUES (?, ?, ?, ?, ?, 'open')")
           ->execute([$batchId, $table, $id, $topic, $reason]);
        $changes++;
        echo "  {$table}#{$id} {$name}\n";
    }

    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
