<?php
/**
 * Owner-approved correction to batch 010 (2026-09-25), after research into the Benue
 * chieftaincy law (NIGERIA_EXPANSION_PROGRESS.md, "Research 25 Sep 2026"):
 *
 *  - The intermediate area councils rest on the Benue State Council of Chiefs and
 *    Traditional Councils Law, 2016, not the 2015 law, whose appointments (gazetted
 *    7 May 2015) were revoked the same year. The six area records said otherwise.
 *  - Five or six areas: in law six (the six Tiv first-class chiefs crowned 14 May 2018).
 *  - Ayatse: elected 20 December 2016 (39 of 46 votes), crowned 4 March 2017.
 *
 * Replaces exact sentences only (each must be found, else nothing changes), adds three
 * sources (their ids are added to batch 010's "Sources created" list, so a rollback of
 * batch 010 removes them too), links them with batch 010's id, updates two gaps and logs
 * every change in activity_log. Idempotent. Default is a DRY RUN; --apply commits.
 * Usage: php database/research/fix_010_chieftaincy_law.php [--apply]
 */

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/config/config.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/config/security.php';
require BASE_PATH . '/core/Cache.php';
require BASE_PATH . '/services/ContentIndexer.php';
Cache::init(BASE_PATH . '/storage/cache');

$apply = in_array('--apply', $argv, true);
$BATCH = 'Batch 010 — The Tor Tiv and the Tiv intermediate areas';

$SOURCES = [
    'FMINO' => ['news', 'Sankera Community Demands New Paramount Ruler', 'Samuel Anyanwu', 'Federal Ministry of Information and National Orientation', '2025-02-06',
                'https://fmino.gov.ng/sankera-community-demands-new-paramount-ruler/',
                'Federal Information Centre report (Benue). On 6 February 2025 the Benue State House of Assembly urged the governor to fill the vacant offices of the Tor Sankera Intermediate Area Traditional Council, Ter Katsina-Ala and Tor Jechira, "as provided in the section 23(1) and (2) of the Benue State Council of Chiefs and Traditional Councils law, 2016". Tor Sankera Abu King Shuluwa died on 16 January 2024.'],
    'IAMBCOR' => ['website', 'Coronation Of Ten First Class Chiefs in Benue State; Tor Tiv and Ochi’Idoma Mere Spectators', 'Terver Adom', 'I am Benue', '2018-05-15',
                  'http://www.iambenue.com/coronation-of-ten-first-class-chiefs-in-benue-state-tor-tiv-and-ochiidoma-mere-spectators/',
                  'Report and comment. Ten first-class chiefs were crowned at IBB Square, Makurdi, on 14 May 2018 under "A law to make provisions for the establishment of Benue Council of chiefs and traditional councils in the State and for purposes connected therewith, 2016", including the six Tiv chiefs: Tor Jemgbagh, Tor Kwande, Tor Jechira, Tor Lobi, Tor Sankera and Tor Gwer. The law first came into effect in 2015; appointments gazetted on 7 May 2015 were revoked the same year by the "First Class Chiefs Appointment (Revocation) Order".'],
    'WJA' => ['encyclopedia', 'James Ayatse', null, 'Wikipedia', null, 'https://en.wikipedia.org/wiki/James_Ayatse',
              'Elected Tor Tiv on 20 December 2016, with 39 of 46 votes.'],
];

$OLD_COMMON = 'Each intermediate area is headed by a first-class chief with the title Tor. According to Daily Trust, a Benue State chieftaincy law passed on 7 April 2015 '
            . 'provided for intermediate area traditional councils for Jechira, Jemgbagh, Kwande, Sankera, Gwer and Lobi. The first-class chiefs received their staffs of office on 14 May 2018. '
            . 'I am Benue credits the introduction of this tier to Tor Tiv IV, Alfred Akawe Torkula.';
$NEW_COMMON = 'Each intermediate area is headed by a first-class chief with the title Tor. According to Daily Trust, a first Benue chieftaincy law, passed on 7 April 2015, '
            . 'provided for intermediate area traditional councils for Jechira, Jemgbagh, Kwande, Sankera, Gwer and Lobi. I am Benue reports that the appointments made under it, '
            . 'gazetted on 7 May 2015, were revoked later that year by Governor Samuel Ortom. The councils now rest on the Benue State Council of Chiefs and Traditional Councils Law, 2016, '
            . 'an amended law signed under Ortom. Under it the six Tiv first-class chiefs were crowned, with four Idoma and Igede chiefs, at IBB Square in Makurdi on 14 May 2018. '
            . 'I am Benue credits the introduction of this tier to Tor Tiv IV, Alfred Akawe Torkula.';
$OLD_MINDA_END = 'ThisDay reported in 2021 that whether Minda counts as one area or as two, Lobi and Gwer, was disputed in Benue politics.';
$NEW_MINDA_END = $OLD_MINDA_END . ' In law there are two: the 2016 law has separate Lobi and Gwer councils, and a Tor Lobi and a Tor Gwer were both crowned in 2018.';
$OLD_SANKERA_END = 'alternates between the Ipusu and Ichongo houses.';
$NEW_SANKERA_END = $OLD_SANKERA_END . ' In February 2025 the Benue State House of Assembly, calling for the stool to be filled after the death of the Tor Sankera, Abu King Shuluwa, in January 2024, '
                 . 'referred to the Tor Sankera Intermediate Area Traditional Council under section 23 of the 2016 law, according to the Federal Ministry of Information.';

$FIXES = [
    // [slug, field, old text, new text]
    ['tor-tiv', 'description',
     'Ayatse was crowned at the J. S. Tarka Stadium in Gboko on 4 March 2017, according to Wikipedia; I am Benue dates his accession to 2016.',
     'Ayatse was elected on 20 December 2016, winning 39 of the 46 votes, and crowned at the J. S. Tarka Stadium in Gboko on 4 March 2017, according to Wikipedia.'],
    ['tor-tiv', 'description',
     'Wikipedia states that, under the Benue State law on the council of chiefs, the stool rotates',
     'Wikipedia states that, under section 16(1) and Schedule 3 of the Benue State law on the council of chiefs and traditional councils, the stool rotates'],
];
foreach (['jemgbagh', 'jechira', 'kwande', 'sankera', 'lobi', 'gwer'] as $a) $FIXES[] = ["{$a}-intermediate-area", 'description', $OLD_COMMON, $NEW_COMMON];
$FIXES[] = ['lobi-intermediate-area', 'description', $OLD_MINDA_END, $NEW_MINDA_END];
$FIXES[] = ['gwer-intermediate-area', 'description', $OLD_MINDA_END, $NEW_MINDA_END];
$FIXES[] = ['sankera-intermediate-area', 'description', $OLD_SANKERA_END, $NEW_SANKERA_END];

$FOUNDED_TEXT = 'Benue State Council of Chiefs and Traditional Councils Law, 2016';
$LINKS = [ // slug => [[source key, claim]]
    'tor-tiv' => [['WJA', 'Ayatse elected 20 December 2016 (39 of 46 votes)']],
    'tiv-traditional-council' => [['IAMBCOR', 'Six Tiv first-class chiefs crowned 14 May 2018 under the 2016 law']],
];
foreach (['jemgbagh', 'jechira', 'kwande', 'sankera', 'lobi', 'gwer'] as $a) {
    $LINKS["{$a}-intermediate-area"] = [['IAMBCOR', '2015 appointments revoked; 2016 law; Tor ' . ucfirst($a) . ' crowned 14 May 2018']];
}
$LINKS['sankera-intermediate-area'][] = ['FMINO', 'Tor Sankera Intermediate Area Traditional Council under s.23 of the 2016 law (2025)'];
$LINKS['jechira-intermediate-area'][] = ['FMINO', 'Tor Jechira vacancy under s.23 of the 2016 law (2025)'];

$GAPS = [
    'Five blocs or six intermediate areas' => ['resolved', 'Resolved 2026-09-25: in law six. The Benue State Council of Chiefs and Traditional Councils Law, 2016 has separate Lobi and Gwer councils; six Tiv first-class chiefs, including a Tor Lobi and a Tor Gwer, were crowned on 14 May 2018 (I am Benue 2018; Federal Ministry of Information 2025). "Minda" remains a customary and political grouping.'],
    'The Benue chieftaincy law' => ['open', 'Updated 2026-09-25: the law in force is the Benue State Council of Chiefs and Traditional Councils Law, 2016 (Federal Ministry of Information 2025); the 2015 law\'s appointments were revoked in 2015. The text itself is not online. The Ipusu–Ichongo rotation clause (s.16(1), Schedule 3, cited by Wikipedia) and the Schedule 3 list of lineages (reported by LinkNaija in 2016, site offline) remain unverified. Needs a printed copy from the Benue State Ministry of Justice or House of Assembly library.'],
    "Ayatse's accession: 2016 or 2017" => ['resolved', 'Resolved 2026-09-25: elected 20 December 2016 (39 of 46 votes; Wikipedia, James Ayatse), crowned 4 March 2017.'],
];

$db = Database::getInstance();
$done = [];
$touched = [];
$db->beginTransaction();
try {
    $st = $db->prepare('SELECT id, summary FROM research_batches WHERE title = ?');
    $st->execute([$BATCH]);
    $batch = $st->fetch();
    if (!$batch) throw new RuntimeException("{$BATCH} not found");
    $batchId = (int) $batch['id'];

    $poly = function (string $slug) use ($db): array {
        $st = $db->prepare('SELECT * FROM polities WHERE slug = ?');
        $st->execute([$slug]);
        $row = $st->fetch();
        if (!$row) throw new RuntimeException("polity {$slug} not found");
        return $row;
    };
    $log = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                         VALUES (NULL, 'heritage_record_corrected', 'polities', ?, ?, ?, 'cli', 'fix_010_chieftaincy_law.php')");

    // 1. Sources (reused by URL; new ids join batch 010's "Sources created" list).
    $src = [];
    $newIds = [];
    $find = $db->prepare('SELECT id FROM sources WHERE url = ? LIMIT 1');
    $ins = $db->prepare("INSERT INTO sources (source_type, title, author, organisation, publication_date, url, notes, verification_status, access_date)
                         VALUES (?, ?, ?, ?, ?, ?, ?, 'needs_corroboration', CURDATE())");
    foreach ($SOURCES as $key => [$type, $title, $author, $org, $date, $url, $notes]) {
        $find->execute([$url]);
        if ($id = $find->fetchColumn()) { $src[$key] = (int) $id; continue; }
        $ins->execute([$type, $title, $author, $org, $date, $url, $notes]);
        $src[$key] = $newIds[] = (int) $db->lastInsertId();
        $done[] = "source {$key} #{$src[$key]}";
    }
    if ($newIds) {
        if (!preg_match('/Sources created by this batch \(ids\): ([\d,]+)/', (string) $batch['summary'], $m)) throw new RuntimeException('batch summary has no source list');
        $db->prepare('UPDATE research_batches SET summary = REPLACE(summary, ?, ?) WHERE id = ?')
           ->execute([$m[0], $m[0] . ',' . implode(',', $newIds), $batchId]);
    }

    // 2. Text corrections: exact sentences, each found once or already corrected.
    foreach ($FIXES as [$slug, $field, $old, $new]) {
        $row = $poly($slug);
        if (str_contains($row[$field], $new)) { $done[] = "{$slug}.{$field}: already corrected"; continue; }
        if (substr_count($row[$field], $old) !== 1) throw new RuntimeException("{$slug}.{$field}: expected text not found exactly once");
        $db->prepare("UPDATE polities SET {$field} = ? WHERE id = ?")->execute([str_replace($old, $new, $row[$field]), $row['id']]);
        $log->execute([$row['id'], json_encode([$field => $old], JSON_UNESCAPED_UNICODE), json_encode([$field => $new, 'by' => 'owner approval 2026-09-25'], JSON_UNESCAPED_UNICODE)]);
        $touched[$row['id']] = true;
        $done[] = "{$slug}.{$field}: corrected";
    }

    // 3. Founding date of the six areas: the 2016 law.
    foreach (['jemgbagh', 'jechira', 'kwande', 'sankera', 'lobi', 'gwer'] as $a) {
        $row = $poly("{$a}-intermediate-area");
        if ((int) $row['founded_year'] === 2016) continue;
        $db->prepare("UPDATE polities SET founded_year = 2016, founded_text = ?, founded_precision = 'year' WHERE id = ?")->execute([$FOUNDED_TEXT, $row['id']]);
        $log->execute([$row['id'], json_encode(['founded_year' => $row['founded_year'], 'founded_text' => $row['founded_text']], JSON_UNESCAPED_UNICODE),
                       json_encode(['founded_year' => 2016, 'founded_text' => $FOUNDED_TEXT, 'by' => 'owner approval 2026-09-25'], JSON_UNESCAPED_UNICODE)]);
        $touched[$row['id']] = true;
        $done[] = "{$a}: founded 2016";
    }

    // 4. Source links (tagged with batch 010, so its rollback removes them).
    $has = $db->prepare("SELECT 1 FROM entity_sources WHERE entity_table = 'polities' AND entity_id = ? AND source_id = ?");
    $link = $db->prepare("INSERT INTO entity_sources (entity_table, entity_id, source_id, claim, stance, research_batch_id) VALUES ('polities', ?, ?, ?, 'supports', ?)");
    foreach ($LINKS as $slug => $pairs) {
        $row = $poly($slug);
        foreach ($pairs as [$key, $claim]) {
            $has->execute([$row['id'], $src[$key]]);
            if ($has->fetchColumn()) continue;
            $link->execute([$row['id'], $src[$key], $claim, $batchId]);
            $done[] = "{$slug} ← {$key}";
        }
    }

    // 5. The Ayatse link note, and the gaps.
    $db->prepare("UPDATE entity_relations SET notes = ? WHERE research_batch_id = ? AND relation_type = 'held_title' AND role = 'Tor Tiv V'")
       ->execute(['Elected 20 December 2016 (39 of 46 votes), crowned 4 March 2017 (Wikipedia).', $batchId]);
    $gapExists = $db->prepare('SELECT COUNT(*) FROM research_gaps WHERE research_batch_id = ? AND topic = ?');
    $gap = $db->prepare('UPDATE research_gaps SET status = ?, description = ? WHERE research_batch_id = ? AND topic = ?');
    foreach ($GAPS as $topic => [$status, $desc]) {
        $gapExists->execute([$batchId, $topic]);
        if (!$gapExists->fetchColumn()) throw new RuntimeException("gap '{$topic}' not found");
        $gap->execute([$status, $desc, $batchId, $topic]);
        $done[] = "gap '{$topic}': {$status}";
    }

    $apply ? $db->commit() : $db->rollBack();
} catch (Throwable $e) {
    $db->rollBack();
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . " — nothing changed.\n");
    exit(1);
}
if ($apply) {
    $indexer = new ContentIndexer($db);
    foreach (array_keys($touched) as $id) $indexer->indexRecord('polities', $id);
}
echo ($apply ? '[APPLIED] ' : '[DRY RUN - rolled back] ') . implode("\n  ", $done) . "\n";
