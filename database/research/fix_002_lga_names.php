<?php
/**
 * Owner corrections to batch 002: use the correct forms "Ogbomosho South" (Oyo) and
 * "Yenagoa" (Bayelsa) (2026-09-24), and "Olamaboro" (Kogi) (2026-09-25) as the LGA names. The spellings printed in the
 * Constitution transcription / Statoids ("Ogbmosho South", "Yenegoa") are kept as
 * source-attributed spelling variants; the old addresses 301 to the new ones.
 *
 * Idempotent. Default is a DRY RUN; --apply commits.
 * Usage: php database/research/fix_002_lga_names.php [--apply]
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/config/security.php';
require BASE_PATH . '/core/Cache.php';
require BASE_PATH . '/services/ContentIndexer.php';
Cache::init(BASE_PATH . '/storage/cache');

$apply = in_array('--apply', $argv, true);
$FIXES = [
    // state slug, current (printed) name, correct name, date of the owner's decision
    ['oyo',     'Ogbmosho South', 'Ogbomosho South', '2026-09-24'],
    ['bayelsa', 'Yenegoa',        'Yenagoa',         '2026-09-24'],
    ['kogi',    'Olamabolo',      'Olamaboro',       '2026-09-25'],
];
$slug = fn(string $s) => trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($s)), '-');

$db = Database::getInstance();
$con = (int) $db->query("SELECT id FROM sources WHERE url = 'https://nigeriarights.gov.ng/files/constitution.pdf' LIMIT 1")->fetchColumn();
$done = [];
$db->beginTransaction();
try {
    foreach ($FIXES as [$state, $printed, $correct, $decided]) {
        $st = $db->prepare("SELECT l.* FROM admin_units l JOIN admin_units s ON s.id = l.parent_id
                            WHERE s.unit_type = 'state' AND s.slug = ? AND l.unit_type = 'lga' AND l.name IN (?, ?)");
        $st->execute([$state, $printed, $correct]);
        $u = $st->fetch();
        if (!$u) throw new RuntimeException("LGA {$printed} ({$state}) not found");
        if ($u['name'] === $correct) { $done[] = "{$correct}: already correct"; continue; }

        $newSlug = $slug($correct);
        $db->prepare('UPDATE admin_units SET name = ?, slug = ?, summary = REPLACE(summary, ?, ?) WHERE id = ?')
           ->execute([$correct, $newSlug, $printed, $correct, $u['id']]);
        $db->prepare('INSERT IGNORE INTO slug_redirects (entity_table, entity_id, old_path) VALUES (?, ?, ?)')
           ->execute(['admin_units', $u['id'], "states/{$state}/lgas/{$u['slug']}"]);
        // The correct form was stored as a variant; it is now the name. The printed form becomes the variant.
        $db->prepare("DELETE FROM entity_names WHERE entity_table = 'admin_units' AND entity_id = ? AND name = ?")->execute([$u['id'], $correct]);
        $db->prepare("INSERT INTO entity_names (entity_table, entity_id, name, name_type, usage_notes, source_id, research_batch_id)
                      VALUES ('admin_units', ?, ?, 'spelling_variant', ?, ?, ?)")
           ->execute([$u['id'], $printed, 'Spelling as printed in the transcription of the 1999 Constitution consulted, and in Statoids. Displayed as "' . $correct . '" by decision of the archive owner (' . $decided . ').',
                      $con ?: null, $u['research_batch_id']]);
        $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                      VALUES (NULL, 'heritage_name_corrected', 'admin_units', ?, ?, ?, 'cli', 'fix_002_lga_names.php')")
           ->execute([$u['id'], json_encode(['name' => $printed, 'slug' => $u['slug']]), json_encode(['name' => $correct, 'slug' => $newSlug, 'by' => 'owner decision ' . $decided])]);
        $done[] = "{$printed} -> {$correct} (#{$u['id']}, /states/{$state}/lgas/{$u['slug']} -> {$newSlug})";
        $ids[] = (int) $u['id'];
    }
    $apply ? $db->commit() : $db->rollBack();
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
if ($apply) {
    foreach ($ids ?? [] as $id) (new ContentIndexer($db))->indexRecord('admin_units', $id);
}
echo ($apply ? '[APPLIED] ' : '[DRY RUN - rolled back] ') . implode('; ', $done) . "\n";
