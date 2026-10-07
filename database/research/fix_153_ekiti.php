<?php
/**
 * Ekiti batch 153 — changes to EXISTING records (NIGERIA_QC_EKITI.md; owner approval required before --apply on live).
 *
 *   A  Ekiti research progress: 16/16 LGAs researched, status 'quality control in progress' (Phase 4 QC run,
 *      NIGERIA_QC_EKITI.md). The row still read 'not started, 0/16'.
 *   B  LGA name: 'Aiyekire' → 'Gbonyin', the name used by INEC (2015 directory and 2024 office list) and Wikipedia
 *      ('Aiyekire (Gbonyin)'). Slug and stable ID are kept, so the page address does not change. 'Aiyekire' is kept as a
 *      historical name (entity_names) and the rename is logged in admin_unit_changes ('renamed'; date not given in a
 *      source read). The First Schedule to the 1999 Constitution prints 'Aiyekire'; the summary says so.
 *   C  Idosi-Osi: the record name stays (the Constitution's spelling; owner decision B, 25 September 2026). Its batch-152
 *      description, which began 'Ido-Osi, also written Idosi-Osi', is reworded to lead with Idosi-Osi.
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
$AGENT = 'fix_153_ekiti.php';
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

    echo "A  Ekiti research progress:\n";
    $row = $db->query("SELECT p.* FROM research_progress p JOIN admin_units u ON u.id = p.admin_unit_id WHERE u.stable_id = 'NG-STATE-EKITI'")->fetch();
    if (!$row) throw new RuntimeException('Ekiti research_progress row not found');
    $new = ['status' => 'qc_in_progress', 'lgas_researched' => 16,
            'notes' => 'Batches 148–153 (7 Oct 2026): languages and peoples (the Yoruba language linked to the state; Ahan to Ekiti East; the Yoruba people — the Ekiti sub-group — linked to the state and all 16 LGAs); 177 INEC wards; traditional institutions (Ado — Ewi Aladesanmi III; Efon-Alaaye — Alaaye Agunsoye II; Ikere — Ogoga Alagbado, the Olukere\'s claim contested; Otun — Oore Adeagbo, his seniority debated); culture and heritage (the Ogun Onire Grove, NCMM proposed No. 95; Ikogosi Warm Springs; Ise Forest Reserve; Olosunta Hill; the Ogun Onire and Udiroko festivals); LGA profiles with headquarters for all 16 LGAs; Aiyekire renamed Gbonyin (Aiyekire kept); Idosi-Osi kept (Constitution). Phase 4 QC run (NIGERIA_QC_EKITI.md, 0 problems). Open: where the Akoko and Yagba-Ekiti minorities live; the Olukere dispute; a dated source for the Alaaye; other obaships (Elekole, Ajero, Olojudo).'];
    $new = array_filter($new, fn($v, $k) => (string) ($row[$k] ?? '') !== (string) $v, ARRAY_FILTER_USE_BOTH);
    if ($new) {
        $old = array_intersect_key($row, $new);
        $db->prepare('UPDATE research_progress SET ' . implode(', ', array_map(fn($k) => "{$k} = ?", array_keys($new))) . ' WHERE id = ?')->execute([...array_values($new), $row['id']]);
        $log->execute(['research_progress', $row['id'], json_encode($old, JSON_UNESCAPED_UNICODE), json_encode($new + ['by' => 'owner approval (batch 153)'], JSON_UNESCAPED_UNICODE)]);
        $changes++;
        echo "  research_progress#{$row['id']}: " . implode(', ', array_keys($new)) . "\n";
    }

    echo "B  Aiyekire → Gbonyin:\n";
    $src = $db->query("SELECT id FROM sources WHERE url = 'https://wp1.inecnigeria.org/wp-content/uploads/2024/04/EKITI-STATE.pdf' ORDER BY id LIMIT 1")->fetchColumn();
    if (!$src) throw new RuntimeException('INEC Ekiti LGA office source not found');
    $note = 'INEC (2015 directory and 2024 LGA office list) and Wikipedia name the LGA Gbonyin; the First Schedule to the 1999 Constitution and Statoids say Aiyekire. The date of the renaming is not given in a source read.';
    [$oldName, $newName] = ['Aiyekire', 'Gbonyin'];
    $u = $db->query("SELECT id, name, summary FROM admin_units WHERE stable_id = 'NG-LGA-EKITI-AIYEKIRE'")->fetch();
    if (!$u) throw new RuntimeException('NG-LGA-EKITI-AIYEKIRE not found');
    if ($u['name'] === $oldName) {
        $db->prepare('UPDATE admin_units SET name = ? WHERE id = ?')->execute([$newName, $u['id']]);
        $log->execute(['admin_units', $u['id'], json_encode(['name' => $oldName]), json_encode(['name' => $newName, 'by' => 'owner approval (batch 153)'])]);
        $changes++;
        echo "  admin_units#{$u['id']}: name {$oldName} → {$newName}\n";
    } elseif ($u['name'] !== $newName) {
        throw new RuntimeException("admin_units#{$u['id']}: unexpected name '{$u['name']}'");
    }
    $oldSum = "{$oldName} is a local government area of Ekiti State, listed in the First Schedule to the 1999 Constitution.";
    $newSum = "{$newName} is a local government area of Ekiti State, listed as '{$oldName}' in the First Schedule to the 1999 Constitution.";
    if ($u['summary'] === $oldSum) {
        $db->prepare('UPDATE admin_units SET summary = ? WHERE id = ?')->execute([$newSum, $u['id']]);
        $log->execute(['admin_units', $u['id'], json_encode(['summary' => $oldSum]), json_encode(['summary' => $newSum, 'by' => 'owner approval (batch 153)'])]);
        $changes++;
        echo "  admin_units#{$u['id']}: summary names the Constitution's '{$oldName}'\n";
    } elseif ($u['summary'] !== $newSum) {
        echo "  admin_units#{$u['id']}: summary differs from the expected text — left unchanged\n";
    }
    $has = $db->prepare("SELECT COUNT(*) FROM entity_names WHERE entity_table = 'admin_units' AND entity_id = ? AND name = ?");
    $has->execute([$u['id'], $oldName]);
    if (!$has->fetchColumn()) {
        $db->prepare("INSERT INTO entity_names (entity_table, entity_id, name, name_type, usage_notes, source_id) VALUES ('admin_units', ?, ?, 'historical', ?, ?)")
           ->execute([$u['id'], $oldName, "The LGA's former name, printed in the First Schedule to the 1999 Constitution and used by Statoids. INEC and Wikipedia use Gbonyin.", $src]);
        $nid = (int) $db->lastInsertId();
        $log->execute(['entity_names', $nid, json_encode(['__inserted' => true]), json_encode(['name' => $oldName, 'entity' => "admin_units#{$u['id']}"])]);
        $changes++;
        echo "  entity_names#{$nid}: historical name {$oldName}\n";
    }
    $has = $db->prepare("SELECT COUNT(*) FROM admin_unit_changes WHERE change_type = 'renamed' AND to_unit_id = ? AND new_value = ?");
    $has->execute([$u['id'], $newName]);
    if (!$has->fetchColumn()) {
        $db->prepare("INSERT INTO admin_unit_changes (change_type, to_unit_id, old_value, new_value, date_precision, notes, source_id, evidence_status) VALUES ('renamed', ?, ?, ?, 'unknown', ?, ?, 'multiple_sources')")
           ->execute([$u['id'], $oldName, $newName, $note, $src]);
        $cid = (int) $db->lastInsertId();
        $log->execute(['admin_unit_changes', $cid, json_encode(['__inserted' => true]), json_encode(['renamed' => "{$oldName} → {$newName}"])]);
        $changes++;
        echo "  admin_unit_changes#{$cid}: renamed {$oldName} → {$newName}\n";
    }

    echo "C  Idosi-Osi description:\n";
    $oldD = "Ido-Osi, also written Idosi-Osi, is a local government area of Ekiti State with its headquarters at Ido Ekiti (according to Statoids and INEC). It had a population of 159,114 at the 2006 census and an area of about 232 km² (Statoids). INEC writes Ido/Osi and Wikipedia Ido-Osi; Statoids keeps Idosi-Osi. Its people are Yoruba of the Ekiti sub-group (Wikipedia; the federal profile).";
    $newD = "Idosi-Osi, also written Ido-Osi, is a local government area of Ekiti State with its headquarters at Ido Ekiti (according to Statoids and INEC). It had a population of 159,114 at the 2006 census and an area of about 232 km² (Statoids). INEC writes Ido/Osi and Wikipedia Ido-Osi; the archive keeps Idosi-Osi, the spelling of the First Schedule to the 1999 Constitution. Its people are Yoruba of the Ekiti sub-group (Wikipedia; the federal profile).";
    $d = $db->query("SELECT id, description FROM admin_units WHERE stable_id = 'NG-LGA-EKITI-IDOSI-OSI'")->fetch();
    if (!$d) throw new RuntimeException('NG-LGA-EKITI-IDOSI-OSI not found');
    if ($d['description'] === $oldD) {
        $db->prepare('UPDATE admin_units SET description = ? WHERE id = ?')->execute([$newD, $d['id']]);
        $log->execute(['admin_units', $d['id'], json_encode(['description' => $oldD], JSON_UNESCAPED_UNICODE), json_encode(['description' => $newD, 'by' => 'owner decision B (25 Sep 2026), batch 153'], JSON_UNESCAPED_UNICODE)]);
        $changes++;
        echo "  admin_units#{$d['id']}: description leads with Idosi-Osi\n";
    } elseif ($d['description'] !== $newD) {
        echo "  admin_units#{$d['id']}: description differs from the expected text — left unchanged\n";
    }

    $apply ? $db->commit() : $db->rollBack();
    echo "{$changes} changes. " . ($apply ? "APPLIED.\n" : "DRY RUN — nothing changed. Use --apply.\n");
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
