<?php
/**
 * Ogun batch 122 — changes to EXISTING records (NIGERIA_QC_OGUN.md; owner approved 2026-10-02).
 *
 *   A  Ogun research progress: 20/20 LGAs researched, status 'quality control in progress' (Phase 4 QC run,
 *      NIGERIA_QC_OGUN.md). The row still read 'not started, 0/20'.
 *   B  LGA names: 'Egbado North' → 'Yewa North' and 'Egbado South' → 'Yewa South', the names used by the federal
 *      government's Ogun profile and Wikipedia (the Egbado renamed themselves Yewa in 1995). Slugs and stable IDs are
 *      kept, so page addresses do not change. The old names are kept as historical names (entity_names), and each
 *      rename is logged in admin_unit_changes ('renamed'; date not given in a source read). The First Schedule to the
 *      1999 Constitution still prints 'Egbado North/South'; the summary says so.
 *
 * Every change and every inserted row goes to activity_log; --revert restores old values and deletes inserted rows.
 * Idempotent. Default DRY RUN; --apply commits.
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';

$apply = in_array('--apply', $argv, true);
$revert = in_array('--revert', $argv, true);
$db = Database::getInstance();
$AGENT = 'fix_122_ogun.php';
$log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                     VALUES (NULL, 'heritage_record_corrected', ?, ?, ?, ?, 'cli', '{$AGENT}')");
$changes = 0;
$db->beginTransaction();
try {
    if ($revert) {
        foreach ($db->query("SELECT entity_type, entity_id, old_values FROM activity_log WHERE user_agent = '{$AGENT}' AND action = 'heritage_record_corrected' ORDER BY id DESC")->fetchAll() as $r) {
            $old = json_decode($r['old_values'], true);
            if (!empty($old['__inserted'])) {
                $db->prepare("DELETE FROM {$r['entity_type']} WHERE id = ?")->execute([$r['entity_id']]);
            } else {
                $db->prepare("UPDATE {$r['entity_type']} SET " . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($old))) . " WHERE id = ?")->execute([...array_values($old), $r['entity_id']]);
            }
            $changes++;
        }
        $db->prepare("DELETE FROM activity_log WHERE user_agent = ? AND action = 'heritage_record_corrected'")->execute([$AGENT]);
        $apply ? $db->commit() : $db->rollBack();
        echo "{$changes} restored. " . ($apply ? "REVERTED.\n" : "DRY RUN — use --revert --apply.\n");
        exit(0);
    }
    echo "A  Ogun research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-OGUN'")->fetch();
    if (!$row) throw new RuntimeException('Ogun research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 20,
            'notes' => 'Batches 117–122 (2 Oct 2026): languages and peoples (Yoruba linked to all 20 LGAs, with the Egba, Ijebu, Remo, Yewa (formerly Egbado), Awori, Ketu, Ohori, Anago, Ikale and Ilaje in the notes; Ogu and Gun in Ipokia and Imeko Afon); 236 INEC wards; traditional institutions (Egbaland, Ijebu Kingdom — Awujale vacant since 13 July 2025 — Remo, Olu of Ilaro, Olota of Ota); culture and heritage (Sungbo\'s shrine, declared; Sungbo\'s Eredo; Olumo Rock and Centenary Hall, proposed; National Museum Abeokuta; Ojude Oba and Agemo festivals); LGA profiles with headquarters for all 20 LGAs; Egbado North/South renamed Yewa North/South. Phase 4 QC run (NIGERIA_QC_OGUN.md, 0 problems). Open: the next Awujale; the Remo North and Obafemi Owode headquarters; the LGAs of the heritage sites; the Egba section rulers and other obaships.'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 122)'], JSON_UNESCAPED_UNICODE)]);
        $changes++;
        echo "  research_progress#{$row['id']}: " . implode(', ', array_keys($new)) . "\n";
    }
    echo "B  Yewa North / Yewa South:\n";
    $fg = $db->query("SELECT id FROM sources WHERE url = 'https://nigeria.gov.ng/states/ogun/' ORDER BY id LIMIT 1")->fetchColumn();
    if (!$fg) throw new RuntimeException('federal Ogun profile source not found');
    $note = 'The federal government\'s Ogun profile and Wikipedia name the LGA Yewa North/Yewa South; the First Schedule to the 1999 Constitution, Statoids and INEC\'s lists still say Egbado. The Egbado renamed themselves Yewa in 1995 (Wikipedia, Yewa); the date of the LGA\'s renaming is not given in a source read.';
    foreach (['NG-LGA-OGUN-EGBADO-NORTH' => ['Egbado North', 'Yewa North'], 'NG-LGA-OGUN-EGBADO-SOUTH' => ['Egbado South', 'Yewa South']] as $sid => [$oldName, $newName]) {
        $u = $db->prepare('SELECT id, name, summary FROM admin_units WHERE stable_id = ?');
        $u->execute([$sid]);
        $u = $u->fetch();
        if (!$u) throw new RuntimeException("{$sid} not found");
        if ($u['name'] === $oldName) {
            $db->prepare('UPDATE admin_units SET name = ? WHERE id = ?')->execute([$newName, $u['id']]);
            $log->execute(['admin_units', $u['id'], json_encode(['name' => $oldName]), json_encode(['name' => $newName, 'by' => 'owner approval (batch 122)'])]);
            $changes++;
            echo "  admin_units#{$u['id']}: name {$oldName} → {$newName}\n";
        } elseif ($u['name'] !== $newName) {
            echo "  admin_units#{$u['id']}: name is '{$u['name']}' — left unchanged\n";
            continue;
        }
        $oldSum = "{$oldName} is a local government area of Ogun State, listed in the First Schedule to the 1999 Constitution.";
        $newSum = "{$newName} is a local government area of Ogun State, listed as '{$oldName}' in the First Schedule to the 1999 Constitution.";
        if ($u['summary'] === $oldSum) {
            $db->prepare('UPDATE admin_units SET summary = ? WHERE id = ?')->execute([$newSum, $u['id']]);
            $log->execute(['admin_units', $u['id'], json_encode(['summary' => $oldSum]), json_encode(['summary' => $newSum, 'by' => 'owner approval (batch 122)'])]);
            $changes++;
            echo "  admin_units#{$u['id']}: summary names the Constitution's '{$oldName}'\n";
        } elseif ($u['summary'] !== $newSum) {
            echo "  admin_units#{$u['id']}: summary differs from the expected text — left unchanged\n";
        }
        $has = $db->prepare("SELECT COUNT(*) FROM entity_names WHERE entity_table = 'admin_units' AND entity_id = ? AND name = ?");
        $has->execute([$u['id'], $oldName]);
        if (!$has->fetchColumn()) {
            $db->prepare("INSERT INTO entity_names (entity_table, entity_id, name, name_type, usage_notes, source_id) VALUES ('admin_units', ?, ?, 'historical', ?, ?)")
               ->execute([$u['id'], $oldName, "The LGA's former name, still printed in the First Schedule to the 1999 Constitution and used by Statoids and INEC. " . 'The Egbado renamed themselves Yewa in 1995 (Wikipedia).', $fg]);
            $nid = (int) $db->lastInsertId();
            $log->execute(['entity_names', $nid, json_encode(['__inserted' => true]), json_encode(['name' => $oldName, 'entity' => "admin_units#{$u['id']}"])]);
            $changes++;
            echo "  entity_names#{$nid}: historical name {$oldName}\n";
        }
        $has = $db->prepare("SELECT COUNT(*) FROM admin_unit_changes WHERE change_type = 'renamed' AND to_unit_id = ? AND new_value = ?");
        $has->execute([$u['id'], $newName]);
        if (!$has->fetchColumn()) {
            $db->prepare("INSERT INTO admin_unit_changes (change_type, to_unit_id, old_value, new_value, date_precision, notes, source_id, evidence_status) VALUES ('renamed', ?, ?, ?, 'unknown', ?, ?, 'multiple_sources')")
               ->execute([$u['id'], $oldName, $newName, $note, $fg]);
            $cid = (int) $db->lastInsertId();
            $log->execute(['admin_unit_changes', $cid, json_encode(['__inserted' => true]), json_encode(['renamed' => "{$oldName} → {$newName}"])]);
            $changes++;
            echo "  admin_unit_changes#{$cid}: renamed {$oldName} → {$newName}\n";
        }
    }
    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
