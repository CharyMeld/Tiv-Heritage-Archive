<?php
/**
 * Timeline Pilot Seed
 *
 * Inserts a small, dual-sourced-where-possible batch of verified Tiv
 * historical events, researched via live web search (WebSearch/WebFetch)
 * against academic/government/institutional sources — not generated from
 * memory. Every event carries its actual source data below so the content
 * is reviewable before/after insertion.
 *
 * Idempotent: skips any event whose slug already exists.
 * All rows are inserted as status = 'draft' for review before publishing.
 *
 * Run: php database/seeds/seed_timeline_pilot.php
 */

define('BASE_PATH', dirname(__DIR__, 2));

require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/Source.php';
require_once BASE_PATH . '/models/TimelineEvent.php';

$sourceModel = new Source();
$eventModel  = new TimelineEvent();

/* ── Source registry — every citation used below, keyed for reuse ───────── */

$sourceData = [

    'nomishan_2022' => [
        'source_type' => 'research',
        'title'       => 'Swem: The Tangible and Intangible Cultural Heritage of the Tiv of Central Nigeria',
        'author'      => 'Nomishan, T. S.',
        'publisher'   => 'Tourism and Heritage Journal (Universitat de Barcelona)',
        'doi'         => '10.1344/THJ.2021.3.5',
        'url'         => 'https://revistes.ub.edu/index.php/tourismheritage/article/view/37430',
        'year_recorded' => 2022,
        'notes'       => 'Vol. 3, pp. 56-67. Peer-reviewed article on Swem as the Tiv ancestral-home/oath-of-justice tradition.',
    ],
    'bohannan_1954' => [
        'source_type' => 'research',
        'title'       => 'The Migration and Expansion of the Tiv',
        'author'      => 'Bohannan, Paul',
        'publisher'   => 'Africa: Journal of the International African Institute',
        'year_recorded' => 1954,
        'notes'       => 'Foundational academic study of Tiv migration, widely cited in Tiv historiography.',
    ],
    'bohannan_bohannan_1953' => [
        'source_type' => 'book',
        'title'       => 'The Tiv of Central Nigeria',
        'author'      => 'Bohannan, Paul & Bohannan, Laura',
        'publisher'   => 'International African Institute (Ethnographic Survey of Africa: Western Africa, Part VIII)',
        'year_recorded' => 1953,
        'notes'       => 'Standard ethnographic reference for the Ichongo/Ipusu segmentary lineage system.',
    ],
    'wikipedia_trenchard' => [
        'source_type' => 'research',
        'title'       => 'Hugh Trenchard in Nigeria',
        'author'      => null,
        'publisher'   => 'Wikipedia',
        'url'         => 'https://en.wikipedia.org/wiki/Hugh_Trenchard_in_Nigeria',
        'year_recorded' => 2024,
        'notes'       => 'SINGLE-SOURCED in this dataset — flagged for corroboration against a Trenchard biography or colonial office record before publishing.',
    ],
    'mutuk_colonial' => [
        'source_type' => 'community_submission',
        'title'       => 'The Clash with Colonial Rule: The Tiv Resistance and Adaptation',
        'author'      => null,
        'contributor_name' => 'Mdzough U Tiv (Tiv heritage website)',
        'url'         => 'https://www.mutuk.org/clash-with-colonial-rule',
        'year_recorded' => 2024,
        'notes'       => 'Community heritage site, no academic citations shown on page. SINGLE-SOURCED — flagged for corroboration.',
    ],
    'wikipedia_tivpeople' => [
        'source_type' => 'research',
        'title'       => 'Tiv people',
        'author'      => null,
        'publisher'   => 'Wikipedia',
        'url'         => 'https://en.wikipedia.org/wiki/Tiv_people',
        'year_recorded' => 2024,
        'notes'       => 'Tertiary source citing Bohannan, Abraham and other academic works; used for triangulation (Tier 7 — discovery, not sole basis).',
    ],
    'wikipedia_nkst' => [
        'source_type' => 'research',
        'title'       => 'Church of Christ in the Sudan Among the Tiv',
        'author'      => null,
        'publisher'   => 'Wikipedia',
        'url'         => 'https://en.wikipedia.org/wiki/Church_of_Christ_in_the_Sudan_Among_the_Tiv',
        'year_recorded' => 2024,
    ],
    'nkst_edu_dept' => [
        'source_type' => 'community_submission',
        'title'       => 'Our History',
        'author'      => null,
        'contributor_name' => 'NKST Education Department',
        'url'         => 'https://www.nksteducationdepartment.org.ng/nkst_education_history.php',
        'year_recorded' => 2024,
        'notes'       => 'Official church-affiliated institutional history page.',
    ],
    'mofep_benue' => [
        'source_type' => 'research',
        'title'       => 'History Of Benue State',
        'author'      => null,
        'publisher'   => 'Benue State Ministry of Finance, Budget & Economic Planning',
        'url'         => 'https://www.mofep.be.gov.ng/explore_benue',
        'year_recorded' => 2024,
        'notes'       => 'Official Benue State Government source.',
    ],
    'iambenue_history' => [
        'source_type' => 'research',
        'title'       => 'Historical Background',
        'author'      => null,
        'publisher'   => 'I am Benue',
        'url'         => 'https://www.iambenue.com/benue-state/benue-state/benue/',
        'year_recorded' => 2024,
    ],
    'wikipedia_makirzakpe' => [
        'source_type' => 'research',
        'title'       => 'Makir Zakpe',
        'author'      => null,
        'publisher'   => 'Wikipedia',
        'url'         => 'https://en.wikipedia.org/wiki/Makir_Zakpe',
        'year_recorded' => 2024,
        'notes'       => "Cites Tesemchi Makar (1994). Article's own infobox/text disagree on the exact installation date — see alternative_dates_notes.",
    ],
    'makar_1994' => [
        'source_type' => 'book',
        'title'       => 'The History of Political Change Among the Tiv in the 19th and 20th Centuries',
        'author'      => 'Makar, Tesemchi',
        'year_recorded' => 1994,
        'notes'       => "Standard academic history of Tiv political change; cited by Wikipedia's Makir Zakpe article as primary source for biographical/installation details.",
    ],
    'iambenue_makirzakpe' => [
        'source_type' => 'research',
        'title'       => 'HRM Chief Makir Zakpe, Tor Tiv I',
        'author'      => null,
        'publisher'   => 'I am Benue',
        'url'         => 'https://www.iambenue.com/makirzakpe/',
        'year_recorded' => 2024,
    ],
    'vaaseh_ehinmore_2011' => [
        'source_type' => 'research',
        'title'       => "Ethnic Politics and Conflicts in Nigeria's First Republic: The Misuse of Native Administrative Police Forces (NAPFS) and the Tiv Riots of Central Nigeria, 1960-1964",
        'author'      => 'Vaaseh, Godwin A. & Ehinmore, O. M.',
        'publisher'   => 'Canadian Social Science, Vol. 7, No. 3',
        'url'         => 'http://www.cscanada.net/index.php/css/article/view/j.css.1923669720110703.031',
        'year_recorded' => 2011,
        'notes'       => 'Peer-reviewed academic journal article.',
    ],
    'academia_tivriots' => [
        'source_type' => 'research',
        'title'       => 'Tiv (Nigeria) Riots of 1960, 1964: The Principle of Minimum Force and Counter Insurgency',
        'author'      => null,
        'publisher'   => 'Academia.edu',
        'url'         => 'https://www.academia.edu/7673034/',
        'year_recorded' => 2024,
        'notes'       => 'Author unverified from search listing alone; used only as secondary corroboration.',
    ],
    'wikipedia_tarka' => [
        'source_type' => 'research',
        'title'       => 'Joseph Tarka',
        'author'      => null,
        'publisher'   => 'Wikipedia',
        'url'         => 'https://en.wikipedia.org/wiki/Joseph_Tarka',
        'year_recorded' => 2024,
    ],
    'punch_tarka' => [
        'source_type' => 'research',
        'title'       => 'Tarka: Statesman who took Middle Belt to national relevance',
        'author'      => null,
        'publisher'   => 'Punch Newspapers',
        'url'         => 'https://punchng.com/tarka-statesman-who-took-middle-belt-to-national-relevance/',
        'year_recorded' => 2024,
    ],
    'wikipedia_benueplateau' => [
        'source_type' => 'research',
        'title'       => 'Benue-Plateau State',
        'author'      => null,
        'publisher'   => 'Wikipedia',
        'url'         => 'https://en.wikipedia.org/wiki/Benue-Plateau_State',
        'year_recorded' => 2024,
        'notes'       => 'Cites worldstatesmen.org and crwflags.com.',
    ],
    'worldstatesmen_nigeria' => [
        'source_type' => 'research',
        'title'       => 'Nigerian States',
        'author'      => 'Cahoon, Ben',
        'publisher'   => 'worldstatesmen.org',
        'url'         => 'https://worldstatesmen.org',
        'year_recorded' => 2024,
        'notes'       => 'Widely-used reference for historical political subdivisions and office-holders.',
    ],
    'britannica_civilwar' => [
        'source_type' => 'research',
        'title'       => 'Nigerian Civil War',
        'author'      => null,
        'publisher'   => 'Encyclopaedia Britannica',
        'url'         => 'https://www.britannica.com/topic/Nigerian-civil-war',
        'year_recorded' => 2024,
    ],
    'blackpast_civilwar' => [
        'source_type' => 'research',
        'title'       => 'Nigerian Civil War (1967-1970)',
        'author'      => null,
        'publisher'   => 'BlackPast.org',
        'url'         => 'https://blackpast.org/global-african-history/nigerian-civil-war-1967-1970/',
        'year_recorded' => 2024,
    ],
    'wikipedia_benuestate' => [
        'source_type' => 'research',
        'title'       => 'Benue State',
        'author'      => null,
        'publisher'   => 'Wikipedia',
        'url'         => 'https://en.wikipedia.org/wiki/Benue_State',
        'year_recorded' => 2024,
    ],
    'bsum_history' => [
        'source_type' => 'research',
        'title'       => 'Brief History',
        'author'      => null,
        'publisher'   => 'Benue State University, Makurdi (official site)',
        'url'         => 'https://bsum.edu.ng/w3/brief_history.php',
        'year_recorded' => 2024,
        'notes'       => 'Institutional/primary source. SINGLE-SOURCED in this dataset.',
    ],
];

/* ── Pilot events ─────────────────────────────────────────────────────── */

$events = [

    [
        'title' => 'Tiv Migration Tradition and the Sacred Site of Swem',
        'event_date' => 'circa 1600 CE (oral tradition)',
        'year' => 1600, 'is_estimated' => 1, 'century' => '16th–17th century', 'decade' => null,
        'era' => 'Pre-Colonial Era',
        'short_summary' => 'Tiv oral tradition holds that the people migrated from the southeast and settled near Swem, a sacred site on the Nigeria–Cameroon border regarded as both ancestral home and oath-of-justice shrine.',
        'description' => "According to Tiv oral tradition, the people trace descent from a single ancestor, Tiv, and migrated over generations before settling near Swem, a mountain/rock site on the Nigeria–Cameroon border. Swem holds a dual role in Tiv culture: it is remembered as an ancestral homeland along the migration route, and it also functions as a sacred oath — Tiv people historically 'swore by Swem' to prove innocence or to cleanse the land of wrongdoing. Academic historiography (Bohannan 1954) treats the precise migration chronology as unresolved, since it rests on oral genealogy rather than archaeological or documentary evidence.",
        'historical_significance' => 'Swem anchors Tiv identity and customary justice; it remains one of the most cited elements of Tiv cultural heritage in both oral tradition and recent academic study.',
        'location' => 'Swem, Nigeria–Cameroon border area',
        'alternative_dates_notes' => 'No fixed historical date exists — oral tradition and secondary literature place the migration/settlement anywhere from the 1600s to 1700s CE. Treat "1600" as an illustrative midpoint, not an established date.',
        'references_text' => "Nomishan, T. S. (2022). Swem: The Tangible and Intangible Cultural Heritage of the Tiv of Central Nigeria. Tourism and Heritage Journal, 3, 56–67. https://doi.org/10.1344/THJ.2021.3.5\nBohannan, P. (1954). The Migration and Expansion of the Tiv. Africa: Journal of the International African Institute.",
        'primary_source' => 'nomishan_2022',
        'additional_sources' => ['bohannan_1954'],
    ],

    [
        'title' => 'Formation of the Ichongo–Ipusu Segmentary Lineage System',
        'event_date' => 'Pre-colonial (undated)',
        'year' => null, 'is_estimated' => 1, 'century' => null, 'decade' => null,
        'era' => 'Pre-Colonial Era',
        'short_summary' => 'Tiv genealogy traces every clan to two founding lines, Ichongo and Ipusu, descended from the ancestor Tiv\'s two sons — the basis of the Tiv segmentary lineage system studied by anthropologists as a classic West African example.',
        'description' => "All Tiv people are understood, genealogically, to belong to one of two great lineages: Ichongo ('circumcised') or Ipusu ('uncircumcised'), named for the two sons of the ancestor Tiv. These lineages subdivide repeatedly into major and minor branches down to the ipaven, the smallest kin-based residential unit. This structure — a single vast patrilineage governing land rights, social organization and conflict resolution without centralized chieftaincy — made the Tiv the best-documented West African case study of a segmentary lineage system in 20th-century anthropology.",
        'historical_significance' => "The segmentary lineage system shaped Tiv political organization long before (and alongside) colonial administration, and directly explains why British indirect rule — designed for centralized chieftaincies — struggled to take hold in Tivland.",
        'related_institutions' => 'Ichongo lineage\nIpusu lineage',
        'references_text' => "Bohannan, P. & Bohannan, L. (1953). The Tiv of Central Nigeria. International African Institute (Ethnographic Survey of Africa: Western Africa, Part VIII).",
        'primary_source' => 'bohannan_bohannan_1953',
        'additional_sources' => ['wikipedia_tivpeople'],
    ],

    [
        'title' => 'First Recorded European Contact with the Tiv (Trenchard Expedition)',
        'event_date' => 'November 1907 – Spring 1908',
        'year' => 1907, 'is_estimated' => 0, 'century' => '20th century', 'decade' => '1900s',
        'era' => 'Colonial Era',
        'short_summary' => 'A British patrol led by Hugh Trenchard (later Marshal of the Royal Air Force) made the first recorded European contact with the Tiv, then referred to by the exonym "Munshi", during a small expedition into the interior.',
        'description' => 'From November 1907 to spring 1908, Hugh Trenchard led a small British patrol — four officers, an interpreter, 25 men, and three machine guns — into the interior of what is now Benue State, making what colonial records describe as the first European contact with the Tiv people, then known to colonial administrators by the exonym "Munshi". Trenchard\'s patrol distributed gifts to local chiefs; roads and initial trade contacts followed.',
        'historical_significance' => 'Marks the start of direct British engagement with the Tiv, preceding the more sustained "pacification" patrols of 1908–1911.',
        'location' => 'Benue Valley, Northern Nigeria Protectorate',
        'alternative_dates_notes' => 'SINGLE-SOURCED (Wikipedia only, no independent academic corroboration found this pass) — flag for verification against a Trenchard biography or Northern Nigeria colonial office records before publishing.',
        'references_text' => 'Hugh Trenchard in Nigeria. Wikipedia. https://en.wikipedia.org/wiki/Hugh_Trenchard_in_Nigeria',
        'primary_source' => 'wikipedia_trenchard',
        'additional_sources' => [],
    ],

    [
        'title' => "British Pacification Reaches Southern Tivland (\"The Eruption\")",
        'event_date' => '1911',
        'year' => 1911, 'is_estimated' => 0, 'century' => '20th century', 'decade' => '1910s',
        'era' => 'Colonial Era',
        'short_summary' => 'British colonial patrols reached southern Tivland for the first time in 1911, an event southern Tiv oral tradition remembers as "the eruption" — the point at which sustained colonial administration began.',
        'description' => 'Following the 1900–1908 British military expeditions that subdued Tiv communities organized through decentralized, segmentary lineages rather than a central army, the first colonial patrols did not reach southern Tivland until 1911. Southern Tiv oral tradition refers to this as the British "eruption". Indirect rule — the administrative model Britain used successfully with the centralized Hausa-Fulani emirates — proved poorly suited to Tiv segmentary society; colonial officers experimented with placing the Tiv under neighbouring Jukun authority and ruling through elder councils, with limited success.',
        'historical_significance' => 'Set the pattern of friction between British indirect-rule administration and Tiv decentralized governance that recurred through the colonial period.',
        'causes' => 'Extension of British "pacification" military campaigns begun around 1900 following the defeat of the Sokoto Caliphate.',
        'consequences' => 'Imposition of indirect-rule structures (Native Authority) on a society without a pre-existing centralized chieftaincy, a mismatch later cited as a source of persistent colonial administrative difficulty in Tivland.',
        'location' => 'Southern Tivland, Benue Province',
        'references_text' => "Tiv people. Wikipedia. https://en.wikipedia.org/wiki/Tiv_people\nThe Clash with Colonial Rule: The Tiv Resistance and Adaptation. Mdzough U Tiv. https://www.mutuk.org/clash-with-colonial-rule",
        'primary_source' => 'wikipedia_tivpeople',
        'additional_sources' => ['mutuk_colonial'],
    ],

    [
        'title' => 'Arrival of the Dutch Reformed Church Mission at Sai',
        'event_date' => '17 April 1911',
        'year' => 1911, 'is_estimated' => 0, 'century' => '20th century', 'decade' => '1910s',
        'era' => 'Colonial Era',
        'short_summary' => 'Missionary Carl Zimmermann established the first Christian mission station in Tivland at Sai on 17 April 1911, on behalf of the Dutch Reformed Church of South Africa working with the Sudan United Mission.',
        'description' => "The Dutch Reformed Church in South Africa, in partnership with the Sudan United Mission (SUM), began missionary work among the Tiv on 17 April 1911, when Carl Zimmermann established a mission station at Sai after C. W. Guinter of the SUM negotiated the site with the local Tiv chief. Growth was initially very slow — reportedly only 25 baptized converts in the mission's first 25 years. The SUM later expanded east of the Katsina-Ala River, establishing further stations at Zaki Biam (1913) and Sevav (1919, including a girls' school).",
        'historical_significance' => "Founded what became the Church of Christ in the Sudan Among the Tiv (NKST), now one of the largest denominations in Benue State, and introduced formal mission education into Tivland.",
        'location' => 'Sai, Tivland',
        'related_institutions' => 'Dutch Reformed Church (South Africa)\nSudan United Mission\nChurch of Christ in the Sudan Among the Tiv (NKST)',
        'references_text' => "Church of Christ in the Sudan Among the Tiv. Wikipedia. https://en.wikipedia.org/wiki/Church_of_Christ_in_the_Sudan_Among_the_Tiv\nOur History. NKST Education Department. https://www.nksteducationdepartment.org.ng/nkst_education_history.php",
        'primary_source' => 'wikipedia_nkst',
        'additional_sources' => ['nkst_edu_dept'],
    ],

    [
        'title' => 'NKST Becomes an Autonomous Self-Governing Church',
        'event_date' => '1957',
        'year' => 1957, 'is_estimated' => 0, 'century' => '20th century', 'decade' => '1950s',
        'era' => 'Colonial Era',
        'short_summary' => 'The Tiv church founded by the 1911 Dutch Reformed/Sudan United Mission was formally organized as an autonomous, self-supporting, self-propagating denomination with four Nigerian pastors.',
        'description' => "In 1957, the mission church founded at Sai in 1911 became a formally autonomous, self-supporting and self-propagating denomination, ordaining its first four Nigerian pastors and adopting the name Nongo u Kristu u ken Sudan hen Tiv (Church of Christ in the Sudan among the Tiv, NKST). The church later renamed itself the Universal Reformed Christian Church in 2012 while retaining the NKST acronym.",
        'historical_significance' => 'Marked the transition from missionary-led to indigenous Tiv church leadership, three years before Nigerian political independence.',
        'related_institutions' => 'Church of Christ in the Sudan Among the Tiv (NKST)',
        'alternative_dates_notes' => 'SINGLE-SOURCED this pass (Wikipedia) — flag for corroboration from NKST\'s own denominational archives.',
        'references_text' => 'Church of Christ in the Sudan Among the Tiv. Wikipedia. https://en.wikipedia.org/wiki/Church_of_Christ_in_the_Sudan_Among_the_Tiv',
        'primary_source' => 'wikipedia_nkst',
        'additional_sources' => [],
    ],

    [
        'title' => 'Tiv Resistance to Colonial Taxation and Forced Labour',
        'event_date' => '1920s, culminating 1929',
        'year' => 1929, 'is_estimated' => 1, 'century' => '20th century', 'decade' => '1920s',
        'era' => 'Colonial Era',
        'short_summary' => 'Through the 1920s, Tiv communities resisted colonial monetary taxation and forced labour on infrastructure projects, escalating to armed confrontation with colonial outposts by 1929.',
        'description' => 'Colonial taxation and compulsory labour on roads, railways and administrative buildings — both alien impositions on Tiv society — met sustained resistance through the 1920s. Tiv warriors, armed with spears and bows against British firearms, attacked colonial outposts and infrastructure; the colonial response reportedly included burning villages and killing or capturing resistance leaders.',
        'causes' => 'Imposition of colonial monetary taxation and forced labour requirements on a society with no prior tradition of centralized tribute.',
        'consequences' => 'Village burnings and suppression of resistance leaders by colonial forces; contributed to the long-running friction between Tivland and indirect-rule administration.',
        'location' => 'Tivland, Benue Province',
        'alternative_dates_notes' => 'SINGLE-SOURCED (a Tiv heritage community website with no academic citations of its own) — this event needs corroboration from an academic/archival source (e.g. colonial district reports, a peer-reviewed history of Tiv taxation resistance) before publishing. Treat the 1929 date as approximate.',
        'references_text' => 'The Clash with Colonial Rule: The Tiv Resistance and Adaptation. Mdzough U Tiv. https://www.mutuk.org/clash-with-colonial-rule',
        'primary_source' => 'mutuk_colonial',
        'additional_sources' => [],
    ],

    [
        'title' => 'Munshi Province Renamed Benue Province; Makurdi Becomes Provincial Headquarters',
        'event_date' => '1918 (renamed); 1927 (Makurdi becomes HQ)',
        'year' => 1927, 'is_estimated' => 0, 'century' => '20th century', 'decade' => '1920s',
        'era' => 'Colonial Era',
        'short_summary' => 'The colonial administrative territory covering Tivland, originally called Munshi Province, was renamed Benue Province in 1918; Makurdi, founded in the early 1920s, became its headquarters in 1927.',
        'description' => 'The administrative territory carved out of the Northern Nigeria Protectorate at the start of the 20th century was initially named Munshi Province, after the Fulani exonym for the Tiv. In 1918 it was renamed Benue Province after the Benue River — itself derived from the Tiv phrase "Ber-nor" ("river/lake of the hippopotamus"), corrupted by colonial administrators to "Benue". Makurdi, established in the early 1920s, gained prominence in 1927 when it became the headquarters of Benue Province.',
        'historical_significance' => 'Established the administrative geography and the river-derived name that would eventually become Benue State in 1976.',
        'location' => 'Benue Province, Northern Nigeria Protectorate',
        'references_text' => "History Of Benue State. Benue State Ministry of Finance, Budget & Economic Planning. https://www.mofep.be.gov.ng/explore_benue\nHistorical Background. I am Benue. https://www.iambenue.com/benue-state/benue-state/benue/",
        'primary_source' => 'mofep_benue',
        'additional_sources' => ['iambenue_history'],
    ],

    [
        'title' => 'Selection and Installation of Makir Zakpe as First Tor Tiv',
        'event_date' => 'September 1946 (selected) – 1 November 1947 (installed)',
        'year' => 1946, 'is_estimated' => 0, 'century' => '20th century', 'decade' => '1940s',
        'era' => 'Colonial Era',
        'short_summary' => 'The British colonial administration allowed the Tiv to select a paramount ruler for the first time; Makir Zakpe of Mbaduku District was chosen in September 1946 and became the first Tor Tiv.',
        'description' => "In 1944 the Governor of Nigeria, Sir Arthur Richards, formally permitted the Tiv to select a paramount chief. By September 1946 the contest had narrowed to two candidates representing the two great Tiv lineages: Makir Zakpe (born 11 April 1896, Mbayar, Mbaduku District — representing Ipusu) and Gondo Aluor (representing Ichongo). Zakpe was chosen 25 votes to 11, with 18 abstentions, becoming the first holder of the newly created Tor Tiv paramount stool. He was installed as a Second Class Chief by the Governor of Northern Province at Gboko. He died on 11 October 1956 in Gboko, aged 60, and was buried at Abagu.",
        'historical_significance' => "Created the paramount traditional-ruler institution that still heads Tiv traditional governance today, and marked British recognition of a unified Tiv political leadership for the first time.",
        'related_institutions' => 'Tor Tiv paramount stool',
        'location' => 'Gboko, Tivland',
        'alternative_dates_notes' => "Sources disagree on the exact installation date: the Wikipedia infobox gives a reign start of 19 September 1946, while its own article text describes installation on 1 November 1947, with a coronation ceremony rescheduled to 3 April 1947. Recorded here as 'selected Sept 1946, installed Nov 1947' pending resolution against a primary source (e.g. Northern Province government gazette).",
        'references_text' => "Makir Zakpe. Wikipedia. https://en.wikipedia.org/wiki/Makir_Zakpe (citing Makar, T. (1994), The History of Political Change Among the Tiv in the 19th and 20th Centuries)\nHRM Chief Makir Zakpe, Tor Tiv I. I am Benue. https://www.iambenue.com/makirzakpe/",
        'primary_source' => 'wikipedia_makirzakpe',
        'additional_sources' => ['makar_1994', 'iambenue_makirzakpe'],
    ],

    [
        'title' => 'Tiv Riots of 1960 and 1964',
        'event_date' => '1960 and 1964',
        'year' => 1960, 'is_estimated' => 0, 'century' => '20th century', 'decade' => '1960s',
        'era' => 'First Republic',
        'short_summary' => 'Widespread unrest broke out across Tivland in 1960 and again, more severely, in 1964, as Tiv supporters of the United Middle Belt Congress clashed with the Native Authority Police enforcing the ruling NPC\'s dominance.',
        'description' => "Following bitterness from the 1959 pre-independence election violence, serious riots erupted across Tivland in 1960 and intensified in 1964. Tiv opposition to the Northern People's Congress (NPC)-dominated regional government — expressed through support for the United Middle Belt Congress (UMBC) — triggered forceful suppression by Native Authority Police forces loyal to the NPC-controlled regional government. Academic analysis (Vaaseh & Ehinmore, 2011) identifies the misuse of these Native Administrative Police Forces as central to the scale of violence and casualties.",
        'causes' => "Tiv political support for the UMBC against the ruling NPC in a Northern Region government perceived by Tiv and other Middle Belt minorities as dominated by Hausa-Fulani elites; resistance to NPC-aligned Native Authority tribute demands.",
        'consequences' => "Significant loss of life during suppression by regional government security forces; hardened Tiv/Middle Belt demands for regional autonomy, contributing to the eventual creation of Benue-Plateau State in 1967.",
        'location' => 'Tivland, Northern Region, Nigeria',
        'references_text' => "Vaaseh, G. A. & Ehinmore, O. M. (2011). Ethnic Politics and Conflicts in Nigeria's First Republic: The Misuse of Native Administrative Police Forces (NAPFS) and the Tiv Riots of Central Nigeria, 1960-1964. Canadian Social Science, 7(3).\nTiv (Nigeria) Riots of 1960, 1964: The Principle of Minimum Force and Counter Insurgency. Academia.edu.",
        'primary_source' => 'vaaseh_ehinmore_2011',
        'additional_sources' => ['academia_tivriots'],
    ],

    [
        'title' => 'Joseph Tarka and the United Middle Belt Congress Movement',
        'event_date' => '1954–1966',
        'year' => 1954, 'is_estimated' => 0, 'century' => '20th century', 'decade' => '1950s',
        'era' => 'First Republic',
        'short_summary' => 'Tiv politician Joseph Sarwuan Tarka founded and led the United Middle Belt Congress, the leading political vehicle for Middle Belt (including Tiv) opposition to Hausa-Fulani-dominated Northern Region rule.',
        'description' => "Joseph Sarwuan Tarka (born 10 July 1932, Igbor, Tiv division) founded the United Middle Belt Congress and won his first seat on its platform in the 1954 federal election, becoming its president. In Federal Parliament he allied with the Action Group opposition and, after winning re-election in 1959, served as shadow minister for Commerce and Industry. Following the January 1966 military coup and General Yakubu Gowon's assumption of power in August 1966, Tarka was appointed Federal Commissioner of Transport, later Communications (serving in the latter role from 1971 until his 1974 resignation amid corruption allegations). He died in London on 30 March 1980, shortly after being elected Senator for Benue East Central.",
        'historical_significance' => "Tarka was the most prominent Tiv/Middle Belt political figure of the First Republic; the UMBC's agitation for Middle Belt autonomy directly fed the creation of Benue-Plateau State in 1967.",
        'related_institutions' => 'United Middle Belt Congress',
        'location' => 'Igbor / Benue Province',
        'references_text' => "Joseph Tarka. Wikipedia. https://en.wikipedia.org/wiki/Joseph_Tarka\nTarka: Statesman who took Middle Belt to national relevance. Punch Newspapers. https://punchng.com/tarka-statesman-who-took-middle-belt-to-national-relevance/",
        'primary_source' => 'wikipedia_tarka',
        'additional_sources' => ['punch_tarka'],
    ],

    [
        'title' => 'Creation of Benue-Plateau State',
        'event_date' => '27 May 1967',
        'year' => 1967, 'is_estimated' => 0, 'century' => '20th century', 'decade' => '1960s',
        'era' => 'Military Era',
        'short_summary' => 'Military Head of State Yakubu Gowon\'s restructuring of Nigeria into twelve states created Benue-Plateau State, uniting Tiv-majority Benue Province with Plateau Province under Governor Joseph Gomwalk.',
        'description' => "On 27 May 1967, as part of General Yakubu Gowon's declaration of a twelve-state federal structure (replacing Nigeria's four regions), Benue-Plateau State was created from the Northern Region's Benue and Plateau Provinces. Joseph Gomwalk served as its first governor (May 1967 – July 1975). The state was subsequently split into separate Benue and Plateau States on 3 February 1976.",
        'causes' => 'Federal military government response to regional minority agitation (including sustained Tiv/UMBC demands) for autonomy from Hausa-Fulani-dominated Northern Region government.',
        'consequences' => 'Gave Tiv-majority Benue Province its first distinct state-level administrative existence, a precursor to the standalone Benue State created in 1976.',
        'location' => 'Benue and Plateau Provinces, Nigeria',
        'references_text' => "Benue-Plateau State. Wikipedia. https://en.wikipedia.org/wiki/Benue-Plateau_State\nCahoon, B. Nigerian States. worldstatesmen.org.",
        'primary_source' => 'wikipedia_benueplateau',
        'additional_sources' => ['worldstatesmen_nigeria'],
    ],

    [
        'title' => 'Nigerian Civil War and Its Impact on the Benue/Tiv Area',
        'event_date' => '6 July 1967 – 15 January 1970',
        'year' => 1967, 'is_estimated' => 0, 'century' => '20th century', 'decade' => '1960s',
        'era' => 'Military Era',
        'short_summary' => 'The Nigerian Civil War, fought between the federal government and the secessionist Republic of Biafra, placed Benue-Plateau State (including Tivland) on the federal side of a conflict bordering the front lines.',
        'description' => "The Nigerian Civil War began on 6 July 1967, following the Eastern Region's secession as the Republic of Biafra on 30 May 1967, and ended with Biafra's defeat on 15 January 1970. As a state on the federal side bordering the conflict zone, Benue-Plateau State's population — including Tiv communities — was affected by wartime mobilization and the broader humanitarian crisis, though Tiv-specific casualty or displacement figures were not found in this research pass and are not claimed here.",
        'historical_significance' => "One of the deadliest conflicts in modern African history (widely cited death toll over one million, chiefly from war-induced famine); shaped the newly-created Benue-Plateau State's earliest years as a federal-side administrative unit.",
        'location' => 'Nigeria, including Benue-Plateau State',
        'alternative_dates_notes' => 'This event captures general Nigerian Civil War context, not Tiv-specific granular history — a research gap flagged here rather than filled with unverified specifics.',
        'references_text' => "Nigerian Civil War. Encyclopaedia Britannica. https://www.britannica.com/topic/Nigerian-civil-war\nNigerian Civil War (1967-1970). BlackPast.org. https://blackpast.org/global-african-history/nigerian-civil-war-1967-1970/",
        'primary_source' => 'britannica_civilwar',
        'additional_sources' => ['blackpast_civilwar'],
    ],

    [
        'title' => 'Creation of Benue State',
        'event_date' => '3 February 1976',
        'year' => 1976, 'is_estimated' => 0, 'century' => '20th century', 'decade' => '1970s',
        'era' => 'Military Era',
        'short_summary' => 'Benue State was carved out of the former Benue-Plateau State on 3 February 1976, one of seven new states created by General Murtala Muhammed\'s military government, giving the Tiv-majority Benue Province its own state.',
        'description' => "Benue State was created on 3 February 1976 when General Murtala Muhammed's military government split the federation from twelve to nineteen states, dividing Benue-Plateau State into separate Benue and Plateau States. The new state took its name from the Benue River, whose name derives from the Tiv phrase 'Ber-nor' (river/lake of the hippopotamus).",
        'historical_significance' => 'Gave the Tiv, as the state\'s largest ethnic group, their own state-level government for the first time, a long-standing goal of Tiv/Middle Belt political agitation dating back to the UMBC era.',
        'location' => 'Benue State, Nigeria',
        'references_text' => "History Of Benue State. Benue State Ministry of Finance, Budget & Economic Planning. https://www.mofep.be.gov.ng/explore_benue\nBenue State. Wikipedia. https://en.wikipedia.org/wiki/Benue_State",
        'primary_source' => 'mofep_benue',
        'additional_sources' => ['wikipedia_benuestate'],
    ],

    [
        'title' => 'Founding of Benue State University, Makurdi',
        'event_date' => '27 December 1991 (Edict); first academic session 1992/93',
        'year' => 1991, 'is_estimated' => 0, 'century' => '20th century', 'decade' => '1990s',
        'era' => 'Military Era',
        'short_summary' => 'Benue State University was established by Benue State University Edict No. 1 of 1991 under Military Governor Col. Attahiru Makka, taking off in the 1992/93 academic year with four founding faculties.',
        'description' => "Benue State University, Makurdi was established by Benue State University Edict No. 1 of 1991, formalized on 27 December 1991 under Military Governor Colonel Attahiru Makka (in office 1989–1992). It was the culmination of efforts dating to 1980, when Civilian Governor Aper Aku first proposed a state university (the Benue State University of Technology was briefly established in 1982 under pioneer Vice Chancellor Prof. Ochapa C. Onazi). The university took off in the 1992/93 academic session with four founding faculties — Arts, Education, Science, and Social Sciences — followed by Law and Management Sciences in 1993/94.",
        'historical_significance' => "Benue State's flagship state university, addressing an educational imbalance identified by successive state administrations since the state's creation in 1976.",
        'related_institutions' => 'Benue State University, Makurdi',
        'location' => 'Makurdi, Benue State',
        'alternative_dates_notes' => 'SINGLE-SOURCED this pass (the university\'s own official history page) — an acceptable institutional/primary source, but a second independent source should be added before publishing.',
        'references_text' => 'Brief History. Benue State University, Makurdi. https://bsum.edu.ng/w3/brief_history.php',
        'primary_source' => 'bsum_history',
        'additional_sources' => [],
    ],
];

/* ── Insert ───────────────────────────────────────────────────────────── */

// Source::$db is protected, so look up existing rows directly via PDO
// rather than reaching into the model's internals.
function sourceExists(PDO $db, string $title, ?string $author): ?int
{
    $stmt = $db->prepare('SELECT id FROM sources WHERE title = ? AND (author <=> ?) LIMIT 1');
    $stmt->execute([$title, $author]);
    $row = $stmt->fetch();
    return $row ? (int) $row['id'] : null;
}

$db = Database::getInstance();

$sourceIds = [];
foreach ($sourceData as $key => $data) {
    $existingId = sourceExists($db, $data['title'], $data['author'] ?? null);
    if ($existingId) {
        $sourceIds[$key] = $existingId;
        continue;
    }
    $sourceIds[$key] = $sourceModel->create($data);
    echo "Created source [{$key}] -> id {$sourceIds[$key]}: {$data['title']}\n";
}

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

    $data = $event;
    unset($data['primary_source'], $data['additional_sources']);
    $data['slug']      = $slug;
    $data['source_id'] = $sourceIds[$primarySourceKey];
    $data['status']    = 'draft';
    $data['is_featured'] = 0;

    $eventId = $eventModel->create($data);

    // Record every cited source (primary + additional) in the pivot table.
    $eventModel->addSource($eventId, $sourceIds[$primarySourceKey]);
    foreach ($additionalKeys as $key) {
        $eventModel->addSource($eventId, $sourceIds[$key]);
    }

    echo "Inserted event -> id {$eventId}: {$event['title']}\n";
    $inserted++;
}

echo "\nDone. Inserted {$inserted} event(s), skipped {$skipped} already-existing.\n";
