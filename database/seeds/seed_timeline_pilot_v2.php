<?php
/**
 * Timeline Pilot Seed v2
 *
 * Adds the events from the Phase-2 spec's example list not yet covered:
 *   - 18th-century expansion of major Tiv clans across the Benue Valley
 *   - Installation of Prof. James Ayatse as Tor Tiv V (2017)
 *   - Nigerian independence and Tiv political participation (1960),
 *     split out from the existing combined "1960 and 1964" row so the
 *     1960 (independence/participation) and 1964 (crisis) angles the
 *     spec lists separately are separate events.
 *
 * Researched via live WebSearch/WebFetch, same rigor and confidence
 * rubric as Phase 1's seed_timeline_pilot.php. Idempotent — safe to re-run.
 *
 * Run: php database/seeds/seed_timeline_pilot_v2.php
 */

define('BASE_PATH', dirname(__DIR__, 2));

require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/Source.php';
require_once BASE_PATH . '/models/TimelineEvent.php';

$sourceModel = new Source();
$eventModel  = new TimelineEvent();
$db          = Database::getInstance();

/* ── New sources ──────────────────────────────────────────────────────── */

$sourceData = [
    'chia_tdar' => [
        'source_type' => 'research',
        'title'       => 'Historical Ecology of Tiv Migration and Conflicts in the Benue Valley of Nigeria: Implications for Food Security',
        'author'      => 'Chia, Richard',
        'publisher'   => 'The Digital Archaeological Record (tDAR)',
        'url'         => 'https://core.tdar.org/document/430273/',
        'year_recorded' => 2024,
        'notes'       => 'Academic archaeological/historical-ecology repository record.',
    ],
    'makurdi_majop' => [
        'source_type' => 'research',
        'title'       => 'Tiv Pre-Colonial Settlement Patterns',
        'author'      => null,
        'publisher'   => 'MAJOP (Owl Journal of Philosophy), Vol. 1, No. 1, ISSN 2734-3219 — Benue State University',
        'url'         => 'https://bsum.edu.ng/journals/majop/vol1n1/files/9.pdf',
        'year_recorded' => 2024,
        'notes'       => 'Author attribution NOT independently verified (source PDF could not be parsed directly this pass; a search-engine summary attributed it to "Sylvester I. Makurdi", unconfirmed). Journal/title/hosting institution confirmed. Treat author as unknown pending direct verification.',
    ],
    'iambenue_taraba' => [
        'source_type' => 'community_submission',
        'title'       => 'Tiv people of Taraba State and Benue State; Any link?',
        'author'      => null,
        'contributor_name' => 'I am Benue',
        'url'         => 'https://www.iambenue.com/the-tiv-people-of-taraba-state-any-link-to-benue/',
        'year_recorded' => 2024,
        'notes'       => 'Minor corroborating source for the Ukum/Shitile/Ugondo clans reaching present Taraba c. 1750-1800.',
    ],
    'wikipedia_kwararafa' => [
        'source_type' => 'research',
        'title'       => 'Kwararafa Confederacy',
        'author'      => null,
        'publisher'   => 'Wikipedia',
        'url'         => 'https://en.wikipedia.org/wiki/Kwararafa_Confederacy',
        'year_recorded' => 2024,
        'notes'       => 'Background context only (Kwararafa collapse enabling Tiv expansion).',
    ],
    'wikipedia_ayatse' => [
        'source_type' => 'research',
        'title'       => 'James Ayatse',
        'author'      => null,
        'publisher'   => 'Wikipedia',
        'url'         => 'https://en.wikipedia.org/wiki/James_Ayatse',
        'year_recorded' => 2024,
        'notes'       => 'Cites Leadership, Vanguard, Daily Post and The Nation newspapers for election/coronation details.',
    ],
    'guardian_ayatse' => [
        'source_type' => 'research',
        'title'       => 'New dawn, as Tiv land crowns Professor James Ortese Iorzua Ayatse King',
        'author'      => null,
        'publisher'   => 'The Guardian Nigeria',
        'url'         => 'https://guardian.ng/sunday-magazine/new-dawn-as-tiv-land-crowns-professor-james-ortese-iorzua-ayatse-king/',
        'year_recorded' => 2017,
    ],
    'ajol_willink' => [
        'source_type' => 'research',
        'title'       => 'The Willink Minority Commission and minority rights in Nigeria',
        'author'      => null,
        'publisher'   => 'EJOTMAS: Ekpoma Journal of Theatre and Media Arts',
        'url'         => 'https://www.ajol.info/index.php/ejotmas/article/view/141208',
        'year_recorded' => 2024,
        'notes'       => 'Peer-reviewed academic journal article on the Willink Commission and minority rights, incl. Northern Region minorities such as the Tiv.',
    ],
    'nigerianwiki_willink' => [
        'source_type' => 'research',
        'title'       => 'Willink Commission',
        'author'      => null,
        'publisher'   => 'NigerianWiki',
        'url'         => 'https://nigerianwiki.com/Willink_Commission',
        'year_recorded' => 2024,
    ],
];

$sourceIds = [];
foreach ($sourceData as $key => $data) {
    $stmt = $db->prepare('SELECT id FROM sources WHERE title = ? AND (author <=> ?) LIMIT 1');
    $stmt->execute([$data['title'], $data['author'] ?? null]);
    $row = $stmt->fetch();
    if ($row) {
        $sourceIds[$key] = (int) $row['id'];
        continue;
    }
    $sourceIds[$key] = $sourceModel->create($data);
    echo "Created source [{$key}] -> id {$sourceIds[$key]}: {$data['title']}\n";
}

/* ── New events ───────────────────────────────────────────────────────── */

$events = [
    [
        'title' => 'Expansion of Major Tiv Clans Across the Benue Valley',
        'event_date' => 'circa 1750-1800 CE',
        'year' => 1750, 'is_estimated' => 1,
        'century' => '18th century', 'decade' => '1750s',
        'short_summary' => 'Following the collapse of the Kwararafa Confederacy, Tiv clans pushed outward from hilltop settlements to occupy much of the Middle Benue Valley through the second half of the 18th century, with clans such as Ukum, Shitile and Ugondo reaching present-day Taraba State alongside the Chamba between 1750 and 1800.',
        'description' => "The collapse of the Kwararafa Confederacy and defeat of the Chamba (Ugenyi) in battle gave Tiv communities room to expand, pushing neighbouring groups outward and occupying much of the Middle Benue Valley. Pre-colonial Tiv settlement favoured hilltop locations (documented both archaeologically and in oral tradition) for defence, with dispersed 'ikyar-ya' compounds organized for security and land control; from these bases Tiv clans migrated from hilltop to hilltop across the 18th century. Clans including Ukum, Shitile and Ugondo are attested reaching the area of present-day Taraba State at the same period as the Chamba migration, between 1750 and 1800 CE. Effective Tiv presence is evidenced as far as Keana, Doma and Awe by the 18th century.",
        'historical_significance' => 'Established the geographic footprint of Tiv settlement across the Middle Benue Valley that persists into the present day, and set clan-territory patterns that still shape local government boundaries in Benue and Taraba States.',
        'causes' => 'Collapse of the Kwararafa Confederacy and military defeat of the Chamba, removing a major check on Tiv territorial expansion.',
        'consequences' => 'Displacement of other ethnic groups from parts of the Middle Benue Valley; establishment of the clan-territory pattern (Ukum, Shitile, Ugondo and others) still recognized today, including Tiv communities in present-day Taraba State.',
        'location' => 'Middle Benue Valley (present-day Benue and Taraba States)',
        'era' => TimelineEvent::eraForYear(1750),
        'alternative_dates_notes' => 'Dating is approximate — oral tradition and secondary academic sources place this expansion broadly across the 18th century (some estimates run 500-600 years before present for the earliest phase); 1750-1800 reflects the best-attested sub-period (the Ukum/Shitile/Ugondo migration alongside the Chamba).',
        'references_text' => "Chia, R. Historical Ecology of Tiv Migration and Conflicts in the Benue Valley of Nigeria: Implications for Food Security. The Digital Archaeological Record. https://core.tdar.org/document/430273/\nTiv Pre-Colonial Settlement Patterns. MAJOP, Vol. 1, No. 1 (Benue State University). https://bsum.edu.ng/journals/majop/vol1n1/files/9.pdf\nTiv people of Taraba State and Benue State; Any link? I am Benue.\nKwararafa Confederacy. Wikipedia.",
        'confidence_score' => 70,
        'primary_source' => 'chia_tdar',
        'additional_sources' => ['makurdi_majop', 'iambenue_taraba', 'wikipedia_kwararafa'],
        'categories' => ['Migration', 'Conflict'],
    ],

    [
        'title' => 'Installation of Prof. James Ayatse as Tor Tiv V',
        'event_date' => '20 December 2016 (elected) – 4 March 2017 (installed)',
        'year' => 2017, 'is_estimated' => 0,
        'century' => '21st century', 'decade' => '2010s',
        'short_summary' => 'Professor James Ortese Iorzua Ayatse, a biochemist and twice a federal university Vice Chancellor, was elected the fifth Tor Tiv on 20 December 2016 and crowned by the Tiv Supreme Council (Ijir Tamen) on 4 March 2017.',
        'description' => "Professor James Ortese Iorzua Ayatse (born 12 May 1956, Mbakaan, Shangev-Ya, Kwande LGA, Benue State) was elected Tor Tiv V on Tuesday, 20 December 2016, polling 39 of 46 possible votes to defeat three other contenders. He was formally crowned by the Kingmakers following a meeting of the Tiv Supreme Council (Ijir Tamen) on 4 March 2017, with Benue State Governor Samuel Ortom presenting him the Staff of Office. Before his installation, Ayatse was a professor of biochemistry (attaining the rank at age 37) who served as Vice Chancellor of the University of Agriculture, Makurdi (2001-2006) and as pioneer Vice Chancellor of Federal University, Dutsin-Ma (2011-2016) — the first Tiv person to hold such federal university leadership positions.",
        'historical_significance' => 'The fifth holder of the Tor Tiv paramount stool created in 1946, and the first with a background as a two-time federal university Vice Chancellor rather than a career traditional administrator.',
        'related_institutions' => 'Tor Tiv paramount stool\nTiv Supreme Council (Ijir Tamen)',
        'location' => 'Gboko, Benue State',
        'era' => TimelineEvent::eraForYear(2017),
        'references_text' => "James Ayatse. Wikipedia. https://en.wikipedia.org/wiki/James_Ayatse (citing Leadership, Vanguard, Daily Post, The Nation)\nNew dawn, as Tiv land crowns Professor James Ortese Iorzua Ayatse King. The Guardian Nigeria. https://guardian.ng/sunday-magazine/new-dawn-as-tiv-land-crowns-professor-james-ortese-iorzua-ayatse-king/",
        'confidence_score' => 85,
        'primary_source' => 'wikipedia_ayatse',
        'additional_sources' => ['guardian_ayatse'],
        'categories' => ['Leadership', 'Traditional Governance'],
    ],

    [
        'title' => 'Nigerian Independence and Tiv Political Participation',
        'event_date' => '1957-1960 (Willink Commission through independence)',
        'year' => 1960, 'is_estimated' => 0,
        'century' => '20th century', 'decade' => '1960s',
        'short_summary' => "As Nigeria approached independence, Tiv fears of domination by the Hausa-Fulani-led NPC in the Northern Region fed into the 1957 Willink Commission inquiry into minority fears; the commission rejected new-state creation but recommended constitutional minority-rights guarantees, while Tiv political participation continued chiefly through the United Middle Belt Congress.",
        'description' => "On 26 September 1957 the British government appointed Sir Henry Willink to chair a commission investigating the fears of Nigeria's minorities — including the Tiv, who faced domination by the Hausa-Fulani-allied Northern People's Congress (NPC) in the Northern Region — ahead of independence. The commission toured Nigeria from 23 November 1957 to 12 April 1958, holding public and private sittings in each region, and reported on 30 July 1958. It rejected the popular minority demand for new, separate states, instead recommending that minority rights be written into the independence constitution as a path to national integration. Tiv political engagement through this period, and through Nigeria's independence on 1 October 1960, continued primarily via the United Middle Belt Congress (UMBC, see the separate Joseph Tarka/UMBC timeline event), which opposed NPC-aligned Native Authority rule.",
        'historical_significance' => "Set the constitutional framework — rights guarantees rather than new states — under which Tiv and other Middle Belt minorities entered independent Nigeria; the underlying tensions the Commission catalogued were not resolved and fed directly into the 1960 and 1964 unrest in Tivland.",
        'causes' => "Minority fears, including Tiv fears of Hausa-Fulani/NPC domination, that political independence would entrench regional-majority control over minority areas.",
        'consequences' => "Minority-rights guarantees included in the 1960 independence constitution rather than the creation of new states; Tiv-NPC tensions remained unresolved and recurred as unrest in 1960 and again, more severely, in 1964 (see the separate 'Tiv Political Crisis of 1964' event).",
        'location' => 'Northern Region, Nigeria',
        'era' => TimelineEvent::eraForYear(1960),
        'related_institutions' => 'Willink Commission\nUnited Middle Belt Congress',
        'alternative_dates_notes' => "This event captures the general Willink Commission / independence-era minority-rights process, in which the Tiv were one of several affected minority groups, rather than Tiv-exclusive primary-source history — flagged rather than overclaimed. See also the separate 'Joseph Tarka and the United Middle Belt Congress Movement' and 'Tiv Political Crisis of 1964' timeline events for the more Tiv-specific angles.",
        'references_text' => "The Willink Minority Commission and minority rights in Nigeria. EJOTMAS: Ekpoma Journal of Theatre and Media Arts. https://www.ajol.info/index.php/ejotmas/article/view/141208\nWillink Commission. NigerianWiki. https://nigerianwiki.com/Willink_Commission",
        'confidence_score' => 80,
        'primary_source' => 'ajol_willink',
        'additional_sources' => ['nigerianwiki_willink'],
        'categories' => ['Politics', 'Diplomacy'],
    ],
];

$inserted = 0;
$skipped  = 0;

foreach ($events as $event) {
    $slug = TimelineEvent::slugify($event['title']);

    if ($eventModel->existsByField('slug', $slug)) {
        echo "Skipping (already exists): {$event['title']}\n";
        $skipped++;
        continue;
    }

    $primarySourceKey = $event['primary_source'];
    $additionalKeys   = $event['additional_sources'] ?? [];
    $categories       = $event['categories'] ?? [];

    $data = $event;
    unset($data['primary_source'], $data['additional_sources'], $data['categories']);
    $data['slug']      = $slug;
    $data['source_id'] = $sourceIds[$primarySourceKey];
    $data['status']    = 'published';
    $data['is_featured'] = 0;

    $eventId = $eventModel->create($data);

    $eventModel->addSource($eventId, $sourceIds[$primarySourceKey]);
    foreach ($additionalKeys as $key) {
        $eventModel->addSource($eventId, $sourceIds[$key]);
    }
    $eventModel->setCategories($eventId, $categories);

    echo "Inserted event -> id {$eventId}: {$event['title']}\n";
    $inserted++;
}

/* ── Split the existing combined "Tiv Riots of 1960 and 1964" row ───────
   Refocus it specifically on the 1964 crisis, since the spec lists 1960
   (independence/participation, now its own event above) and 1964 (crisis)
   separately. Only runs once — guarded by matching the original title. */

$oldTitle = 'Tiv Riots of 1960 and 1964';
$stmt = $db->prepare('SELECT id FROM timeline_events WHERE title = ? LIMIT 1');
$stmt->execute([$oldTitle]);
$row = $stmt->fetch();

if ($row) {
    $eventId = (int) $row['id'];
    $newTitle = 'Tiv Political Crisis of 1964';
    $newSlug  = TimelineEvent::slugify($newTitle);

    $update = $db->prepare(
        "UPDATE timeline_events SET
            title = ?, slug = ?, event_date = ?, year = ?, decade = ?,
            short_summary = ?, description = ?
         WHERE id = ?"
    );
    $update->execute([
        $newTitle,
        $newSlug,
        '1964',
        1964,
        '1960s',
        'The most severe episode of Tiv unrest in the First Republic: in 1964, Tiv opposition to the NPC-dominated Northern Region government, expressed through support for the United Middle Belt Congress, escalated into serious violence suppressed by Native Authority Police forces.',
        "Tensions that first erupted in the 1960 riots (see the separate 'Nigerian Independence and Tiv Political Participation' event for that background) escalated sharply in 1964. Tiv opposition to the Northern People's Congress (NPC)-dominated regional government — expressed through support for the United Middle Belt Congress (UMBC) — triggered forceful suppression by Native Authority Police forces loyal to the NPC-controlled regional government. Academic analysis (Vaaseh & Ehinmore, 2011) identifies the misuse of these Native Administrative Police Forces as central to the scale of violence and casualties in 1964 specifically, the more severe of the two episodes.",
        $eventId,
    ]);

    echo "Updated event -> id {$eventId}: '{$oldTitle}' refocused as '{$newTitle}'\n";
} else {
    echo "Skipping split-update (already applied or original row not found): {$oldTitle}\n";
}

echo "\nDone. Inserted {$inserted} new event(s), skipped {$skipped} already-existing.\n";
