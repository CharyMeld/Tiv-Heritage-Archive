<?php
/**
 * Seeds the tiv_grammar_rules table with curated grammar rules drawn from
 * "Tiv Grammar F 625 Docx.docx" (~/Downloads/TIV ARCHIVE MATERIALS/).
 *
 * The source document is a Tiv phrasebook/course (prose explanations, large
 * vocabulary lists, and fill-in-the-blank exercises), not a formal grammar
 * reference. This script extracts only the genuinely rule-shaped content —
 * pronoun paradigm, verb types, noun pluralisation, possessive adjectives,
 * sentence word order, and question formation — and leaves out vocabulary
 * dumps and exercises that have no answer key provided in the source (so no
 * translation had to be guessed). Every example below was copied from a
 * Tiv/English pair the source document supplies directly, with paragraph
 * indices noted per rule for traceability back to the docx. Two entries are
 * explicitly flagged in `source_note` as patterns inferred from repeated
 * examples rather than an explicit rule statement in the source (the
 * comparative "hemba" pattern, and the De + pronoun + verb combinations,
 * which are built from the docx's own substitution-table template).
 *
 * Idempotent — skips any rule whose title already exists in tiv_grammar_rules.
 *
 * Run: php database/seeds/seed_grammar_from_docx.php
 */

define('BASE_PATH', dirname(__DIR__, 2));

require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/TivGrammarRule.php';

$model = new TivGrammarRule();

$rules = [
    // ── NOUN ──────────────────────────────────────────────────────────
    [
        'category' => 'noun',
        'title' => 'Proper vs. Common Nouns',
        'summary' => 'Proper nouns name specific people, places, and things; common nouns name everything else, touchable or not.',
        'explanation' => 'A noun represents people (ior), places (ajiir), or things (akaa). Proper nouns name specific people, countries, and places (e.g. Gboko, Nigeria, River Benue) and begin with a capital letter. Common nouns name objects or things — some we can touch (e.g. "rock"/iwen, "bull"/nombua) and some we cannot (e.g. "beauty"/mdoom, "love"/dooishima).',
        'examples' => [
            ['tiv' => 'iwen', 'english' => 'rock'],
            ['tiv' => 'nombua', 'english' => 'bull'],
            ['tiv' => 'mongol', 'english' => 'mango'],
            ['tiv' => 'fon', 'english' => 'phone'],
            ['tiv' => 'mdoom', 'english' => 'beauty', 'note' => 'abstract noun — cannot be touched'],
            ['tiv' => 'mel', 'english' => 'mile', 'note' => 'abstract noun — cannot be touched'],
            ['tiv' => 'dooishima', 'english' => 'love', 'note' => 'abstract noun — cannot be touched'],
        ],
        'source_note' => 'Tiv Grammar F 625, ¶2-18.',
        'sort_order' => 10,
    ],
    [
        'category' => 'noun',
        'title' => 'Singular & Plural Nouns',
        'summary' => 'Tiv has no fixed suffix rule for plurals — singular/plural pairs must be learned individually.',
        'explanation' => 'Unlike English (-s/-es), Tiv has no set rule for forming a plural noun from its singular form. While patterns can appear across related words, only practice with real singular/plural pairs builds reliable recognition.',
        'examples' => [
            ['tiv' => 'ijondough → mzôndom', 'english' => 'calabash/gourd → calabashes, gourds'],
            ['tiv' => 'bua → ibua', 'english' => 'cattle → cattle'],
            ['tiv' => 'nombua → anom a ibua', 'english' => 'bull → bulls'],
            ['tiv' => 'bagu → ibagu', 'english' => 'monkey → monkeys'],
            ['tiv' => 'itiough → mtom', 'english' => 'head → heads'],
            ['tiv' => 'butu → ubutu', 'english' => 'mat → mats'],
            ['tiv' => 'alôm → mbaalom', 'english' => 'hare, rabbit → hares'],
            ['tiv' => 'or → ior', 'english' => 'person → persons, people'],
            ['tiv' => 'kwase → kasev', 'english' => 'woman, female → women'],
            ['tiv' => 'ortiv → ônovtiv, ôntiv', 'english' => 'a Tiv person → Tiv people'],
            ['tiv' => 'wanye → mbayev', 'english' => 'child → children'],
            ['tiv' => 'wankwase → mbayev kasev', 'english' => 'girl → girls'],
            ['tiv' => 'lu → ilu', 'english' => 'mortar → mortars'],
            ['tiv' => 'zwa → ijwa', 'english' => 'mouth → mouths'],
        ],
        'source_note' => 'Tiv Grammar F 625, ¶133-134 and the singular/plural noun table.',
        'sort_order' => 20,
    ],
    [
        'category' => 'noun',
        'title' => 'Homonyms & Tone-Dependent Meaning',
        'summary' => 'Some words share the exact same spelling but mean different things depending on how they are pronounced.',
        'explanation' => 'In Tiv, a word may keep the exact same spelling while carrying entirely different meanings depending on pronunciation (tone). Sometimes both spelling and pronunciation are identical and only context disambiguates. See the Tonal System section on the Alphabet page for how tone marks distinguish these in writing.',
        'examples' => [
            ['tiv' => 'tor', 'english' => 'chief'],
            ['tiv' => 'tor', 'english' => 'a mouse that lives in the ground'],
            ['tiv' => 'tor', 'english' => 'mortar'],
            ['tiv' => 'tor', 'english' => 'roof'],
            ['tiv' => 'kor', 'english' => 'sew'],
            ['tiv' => 'kor', 'english' => 'rope'],
            ['tiv' => 'ya', 'english' => 'home', 'note' => 'same spelling and pronunciation as "eat" — context disambiguates'],
            ['tiv' => 'ya', 'english' => 'eat'],
            ['tiv' => 'yem', 'english' => 'gone'],
            ['tiv' => 'yem', 'english' => 'well cleaned'],
        ],
        'source_note' => 'Tiv Grammar F 625, ¶120.',
        'sort_order' => 30,
    ],

    // ── PRONOUN ───────────────────────────────────────────────────────
    [
        'category' => 'pronoun',
        'title' => 'Subject Pronouns',
        'summary' => 'The seven Tiv subject pronouns and how they attach directly to a following verb or state.',
        'explanation' => 'Tiv has seven subject pronouns. They are frequently used directly before "ngu"/"mba" (am/is/are) or a verb, and the pronoun itself ("un", "i", "ve") is often dropped in casual speech once the subject is already clear from context.',
        'examples' => [
            ['tiv' => 'M', 'english' => 'I'],
            ['tiv' => 'U', 'english' => 'You (singular)'],
            ['tiv' => 'Un', 'english' => 'He/She'],
            ['tiv' => 'I', 'english' => 'It'],
            ['tiv' => 'Se', 'english' => 'We'],
            ['tiv' => 'Ne', 'english' => 'You (plural)'],
            ['tiv' => 'Ve', 'english' => 'They'],
            ['tiv' => 'm ngu', 'english' => 'I am'],
            ['tiv' => 'u ngu', 'english' => 'you (sing.) are'],
            ['tiv' => 'un ngu', 'english' => 'he/she is', 'note' => '"un" is often omitted in speech'],
            ['tiv' => 'se mba', 'english' => 'we are'],
            ['tiv' => 'ne mba', 'english' => 'you (plural) are'],
            ['tiv' => 've mba', 'english' => 'they are', 'note' => '"ve" is often omitted in speech'],
        ],
        'source_note' => 'Tiv Grammar F 625, ¶292-320.',
        'sort_order' => 10,
    ],

    // ── VERB ──────────────────────────────────────────────────────────
    [
        'category' => 'verb',
        'title' => 'Three Verb Types: Action, Linking & Helping',
        'summary' => 'Every Tiv sentence needs a verb — an action verb, a linking verb, or a helping verb paired with one of the other two.',
        'explanation' => 'A verb describes an act, occurrence, or state of being — a complete sentence must have one. Action verbs describe an action or possession (transitively, with a direct object, or intransitively, without one). Linking verbs connect the subject to a noun or adjective that describes it. Helping verbs come before an action or linking verb to add information (e.g. tense); the main verb plus its helping verb together form a verb phrase.',
        'examples' => [
            ['tiv' => 'Nyen m sôr udeseke dedo', 'english' => 'I arranged the desks yesterday', 'note' => 'action verb, transitive — "udeseke" (desks) is the direct object'],
            ['tiv' => 'M unde lada sha u aren sha itiakeda', 'english' => 'I climbed the ladder to reach the books', 'note' => 'action verb, intransitive — no direct object'],
            ['tiv' => 'Inyam ne yua bar', 'english' => 'This meat tastes salty', 'note' => 'linking verb — connects "meat" to the adjective "salty"'],
            ['tiv' => 'Mekaniki una wase se a tse mato', 'english' => 'The mechanic will help us with the used car', 'note' => 'helping verb "una" (will) + main verb "wase" (help)'],
        ],
        'source_note' => 'Tiv Grammar F 625, ¶340-346.',
        'sort_order' => 10,
    ],
    [
        'category' => 'verb',
        'title' => 'Transitive vs. Intransitive Verbs',
        'summary' => 'Transitive verbs act on a direct object; intransitive verbs stand alone.',
        'explanation' => 'A transitive verb requires a noun that receives the action — the direct object. An intransitive verb has no direct object, only (optionally) a modifier. Some Tiv verbs are always intransitive (e.g. yem "go", tema "sit"), while others, like ya ("eat"), can be used either way.',
        'examples' => [
            ['tiv' => 'Wankwase too mngerem', 'english' => 'The girl carried the water', 'note' => 'transitive — direct object "mngerem" (water)'],
            ['tiv' => 'Ve tsuwe', 'english' => 'They jumped', 'note' => 'intransitive — no object'],
            ['tiv' => 'Rumun gbidye bol', 'english' => 'Rumun hit the ball', 'note' => 'transitive'],
            ['tiv' => 'Iwa yevese', 'english' => 'The dog ran', 'note' => 'intransitive'],
            ['tiv' => 'Erdoo ôr takeda', 'english' => 'Erdoo read the book', 'note' => 'transitive'],
            ['tiv' => 'Baba yav shin ikôn', 'english' => 'Baba slept in the couch', 'note' => 'intransitive'],
        ],
        'source_note' => 'Tiv Grammar F 625, transitivity table (verbs section).',
        'sort_order' => 20,
    ],

    // ── ADJECTIVE ─────────────────────────────────────────────────────
    [
        'category' => 'adjective',
        'title' => 'Possessive Adjectives',
        'summary' => 'Possessive adjectives (my, your, his, her, its, our, their) go before the noun they modify, avoiding awkward repetition of a name.',
        'explanation' => 'Possessive adjectives show ownership and modify a noun directly (unlike possessive pronouns, which replace a noun). They make sentences less awkward — instead of repeating a name ("Msendo likes Msendo\'s Bible"), Tiv uses a possessive adjective just as English does ("Msendo likes her Bible").',
        'examples' => [
            ['tiv' => 'Msendo soo Bibilo na', 'english' => "Msendo likes her Bible", 'note' => 'possessive "na" (her) replaces repeating "Msendo\'s"'],
            ['tiv' => 'Girgi yam va fere ga', 'english' => 'My plane is delayed'],
            ['tiv' => 'I ver kwaghyan wou', 'english' => 'Your dinner is served'],
            ['tiv' => 'We a rumun yô due un a kwaghyan na', 'english' => 'Please bring his food out to him'],
            ['tiv' => 'Iyô ne nyuma tsa na', 'english' => 'This snake bit its tail'],
            ['tiv' => 'Ve lu ken iyou ve shie u m va nyôr yô', 'english' => 'They were in their house when I arrived'],
        ],
        'source_note' => 'Tiv Grammar F 625, ¶865-878.',
        'sort_order' => 10,
    ],
    [
        'category' => 'adjective',
        'title' => 'Comparative Forms with "hemba"',
        'summary' => 'A recurring pattern: "hemba" + adjective forms a comparative/superlative ("more/most ___").',
        'explanation' => 'Across the source material\'s adjective examples, "hemba" ("more") consistently precedes an adjective to form a comparative or superlative, similar to English "more ___" / "___-est". This is a pattern inferred from repeated examples, not a rule the source states explicitly — treat it as observational, not exhaustive.',
        'examples' => [
            ['tiv' => 'hemba doon', 'english' => 'better (more good)'],
            ['tiv' => 'hemba vihin ashe', 'english' => 'ugliest (most bad-looking)'],
        ],
        'source_note' => 'Inferred pattern — Tiv Grammar F 625, adjective vocabulary lists. Not an explicit rule statement in the source.',
        'sort_order' => 20,
    ],

    // ── SENTENCE STRUCTURE ────────────────────────────────────────────
    [
        'category' => 'sentence_structure',
        'title' => 'Word Order & Pronoun-Object Substitution',
        'summary' => 'A full noun/name in subject or object position can be swapped for a pronoun without changing the sentence\'s word order.',
        'explanation' => 'Tiv sentences keep their word order when a full noun or proper name is replaced by a pronoun — only the noun/name itself changes. The negation particle "ga" appears at the end of a clause to negate it (e.g. "do not buy them" / "it is not yours").',
        'examples' => [
            ['tiv' => 'Dr. Abunku ser mbauangev', 'english' => 'Dr. Abunku treats patients'],
            ['tiv' => 'Un (Dr. Abunku) ser ve', 'english' => 'He (Dr. Abunku) treats them', 'note' => 'name replaced by pronoun "un"'],
            ['tiv' => 'Claudia kaa ikegh', 'english' => 'Claudia roasted chicken'],
            ['tiv' => 'Un (Claudia) kaa i', 'english' => 'She (Claudia) roasted it', 'note' => 'object "ikegh" replaced by pronoun "i"'],
            ['tiv' => 'Akôv ne taver ishe gande', 'english' => 'These shoes are too expensive'],
            ['tiv' => 'De yamen a ga', 'english' => 'Do not buy them', 'note' => 'negation particle "ga"'],
            ['tiv' => 'Ka i yen ga; ka i yase', 'english' => 'It is not yours (pl); it is ours', 'note' => 'negation particle "ga"'],
        ],
        'source_note' => 'Tiv Grammar F 625, pronoun-substitution table (pronouns section).',
        'sort_order' => 10,
    ],
    [
        'category' => 'sentence_structure',
        'title' => 'Hortative "De" + Pronoun + Verb',
        'summary' => '"De" ("let") + a pronoun + a verb forms a hortative ("let [pronoun] [verb]").',
        'explanation' => 'The particle "De" ("Let") combines with a following pronoun (m "me", se "us", ve "them", un "him/her", i/un "it") and a verb (yevese "run", yem "go", tema "sit", ya "eat", lam "speak", ulugh "pull") to form a hortative construction. The combinations below are built directly from the source\'s own substitution table to illustrate the template, not quoted as standalone sentences.',
        'examples' => [
            ['tiv' => 'De m yevese', 'english' => 'Let me run'],
            ['tiv' => 'De se tema', 'english' => 'Let us sit'],
            ['tiv' => 'De ve ya', 'english' => 'Let them eat'],
            ['tiv' => 'De un yem', 'english' => 'Let him/her go'],
        ],
        'source_note' => 'Constructed from the docx\'s De + pronoun + verb substitution table (verbs/pronouns section) — combinations are illustrative, not verbatim example sentences.',
        'sort_order' => 20,
    ],

    // ── QUESTION FORMATION ────────────────────────────────────────────
    [
        'category' => 'question_formation',
        'title' => 'Question Words',
        'summary' => 'The core Tiv question words: who, whose, where, what, why, how.',
        'explanation' => 'Tiv questions are built around a small set of question words, typically placed where the answer would go (e.g. "Ihungwa ngi hana?" — literally "The pit is where?" — for "Where is the lavatory?"). The word "ka" functions like English "is" in both questions and statements.',
        'examples' => [
            ['tiv' => 'ana, an', 'english' => 'who'],
            ['tiv' => 'u ana?', 'english' => 'whose'],
            ['tiv' => 'hana', 'english' => 'where'],
            ['tiv' => 'nyi', 'english' => 'what'],
            ['tiv' => 'sha aci u nyi', 'english' => 'why'],
            ['tiv' => 'nena', 'english' => 'how'],
        ],
        'source_note' => 'Tiv Grammar F 625, ¶2078-2084.',
        'sort_order' => 10,
    ],
    [
        'category' => 'question_formation',
        'title' => 'Sample Questions & Answers',
        'summary' => 'Real question-and-answer pairs showing question words in use.',
        'explanation' => 'Worked question-and-answer exchanges from the source material, showing each question word applied in context along with a natural Tiv answer.',
        'examples' => [
            ['tiv' => 'Ihungwa ngi hana?', 'english' => 'Where is the lavatory? (Lit. The pit is where?)'],
            ['tiv' => 'Ka ana?', 'english' => 'Who is it?'],
            ['tiv' => 'Ka mo, Terna', 'english' => "It's me, Terna"],
            ['tiv' => 'Ishe na ka nena?', 'english' => 'How much is it?'],
            ['tiv' => 'Ka han ma shie ne va nyere', 'english' => 'When did you arrive?'],
            ['tiv' => 'Se va nyôr tugh', 'english' => 'We arrived at night'],
            ['tiv' => 'Ka nyi i ere?', 'english' => 'What happened?'],
            ['tiv' => 'Kwaghyan ngu nena?', 'english' => 'How is the food?'],
            ['tiv' => 'Kwaghyan doo kpishi', 'english' => 'The food is very good (delicious)'],
            ['tiv' => 'Ka nyi kwagh(a)?', 'english' => 'What is it?'],
            ['tiv' => 'Ka kwagh ga', 'english' => "It's nothing"],
            ['tiv' => 'U ngu zan hana?', 'english' => 'Where are you going?'],
            ['tiv' => 'M ngu zan kasua', 'english' => 'I am going to the market'],
            ['tiv' => 'U hide ve?', 'english' => 'Welcome (Lit.: Are you back?)'],
            ['tiv' => 'Ka han Ami a lu?', 'english' => 'Where is Ami?'],
            ['tiv' => 'Ami yar tom', 'english' => 'Ami has gone to work'],
        ],
        'source_note' => 'Tiv Grammar F 625, ¶2087-2170 (Questions Examples 1 & 2).',
        'sort_order' => 20,
    ],
];

$inserted = 0;
$skipped  = 0;

foreach ($rules as $rule) {
    if ($model->existsByField('title', $rule['title'])) {
        echo "Skipping (already exists): {$rule['title']}\n";
        $skipped++;
        continue;
    }

    $id = $model->create($rule);
    echo "Inserted [{$rule['category']}] {$rule['title']} -> id {$id}\n";
    $inserted++;
}

echo "\nDone. Inserted {$inserted}, skipped {$skipped} (of " . count($rules) . " total).\n";
