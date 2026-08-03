<?php
/* ── Tiv Alphabet Data ─────────────────────────────────────────────── */

/* Built-in defaults (used when the DB table is empty) */
$_defaultVowels = [
    ['letter'=>'A a','ipa'=>'/a/','sound'=>'like "a" in father','example'=>'ata','meaning'=>'three'],
    ['letter'=>'E e','ipa'=>'/e/','sound'=>'like "e" in bed','example'=>'eer','meaning'=>'blood'],
    ['letter'=>'I i','ipa'=>'/i/','sound'=>'like "ee" in see','example'=>'ikyô','meaning'=>'tree'],
    ['letter'=>'O o','ipa'=>'/o/','sound'=>'like "o" in go','example'=>'or','meaning'=>'person'],
    ['letter'=>'U u','ipa'=>'/u/','sound'=>'like "oo" in food','example'=>'ukan','meaning'=>'fire'],
];
$_defaultConsonants = [
    ['letter'=>'B b','ipa'=>'/b/','sound'=>'like "b" in boy','example'=>'bam','meaning'=>'water'],
    ['letter'=>'D d','ipa'=>'/d/','sound'=>'like "d" in dog','example'=>'doo','meaning'=>'come'],
    ['letter'=>'F f','ipa'=>'/f/','sound'=>'like "f" in fish','example'=>'faan','meaning'=>'sweep'],
    ['letter'=>'G g','ipa'=>'/ɡ/','sound'=>'like "g" in go','example'=>'ga','meaning'=>'no / not'],
    ['letter'=>'H h','ipa'=>'/h/','sound'=>'like "h" in hat','example'=>'hembe','meaning'=>'shirt'],
    ['letter'=>'J j','ipa'=>'/dʒ/','sound'=>'like "j" in jump','example'=>'jôô','meaning'=>'calabash'],
    ['letter'=>'K k','ipa'=>'/k/','sound'=>'like "k" in key','example'=>'kar','meaning'=>'read / count'],
    ['letter'=>'L l','ipa'=>'/l/','sound'=>'like "l" in love','example'=>'loo','meaning'=>'go'],
    ['letter'=>'M m','ipa'=>'/m/','sound'=>'like "m" in man','example'=>'mba','meaning'=>'we / people'],
    ['letter'=>'N n','ipa'=>'/n/','sound'=>'like "n" in now','example'=>'nom','meaning'=>'thing'],
    ['letter'=>'P p','ipa'=>'/p/','sound'=>'like "p" in pen','example'=>'pan','meaning'=>'scatter'],
    ['letter'=>'R r','ipa'=>'/r/','sound'=>'like "r" in run','example'=>'ren','meaning'=>'know'],
    ['letter'=>'S s','ipa'=>'/s/','sound'=>'like "s" in sun','example'=>'sha','meaning'=>'in / on'],
    ['letter'=>'T t','ipa'=>'/t/','sound'=>'like "t" in top','example'=>'tar','meaning'=>'land / country'],
    ['letter'=>'V v','ipa'=>'/v/','sound'=>'like "v" in van','example'=>'vee','meaning'=>'eat'],
    ['letter'=>'W w','ipa'=>'/w/','sound'=>'like "w" in water','example'=>'wan','meaning'=>'word / story'],
    ['letter'=>'Y y','ipa'=>'/j/','sound'=>'like "y" in yes','example'=>'yôr','meaning'=>'person'],
    ['letter'=>'Z z','ipa'=>'/z/','sound'=>'like "z" in zebra','example'=>'za','meaning'=>'go (away)'],
];
$_defaultDigraphs = [
    ['letter'=>'GB gb','ipa'=>'/ɡ͡b/','sound'=>'labial-velar stop — "g" and "b" merged','example'=>'gba','meaning'=>'arm / branch'],
    ['letter'=>'KP kp','ipa'=>'/k͡p/','sound'=>'labial-velar stop — "k" and "p" merged','example'=>'kpam','meaning'=>'wide / broad'],
    ['letter'=>'MB mb','ipa'=>'/ᵐb/','sound'=>'prenasalized "b" — nose before lip','example'=>'mban','meaning'=>'night'],
    ['letter'=>'ND nd','ipa'=>'/ⁿd/','sound'=>'prenasalized "d" — nasal onset','example'=>'nder','meaning'=>'wall'],
    ['letter'=>'NG ng','ipa'=>'/ŋ/','sound'=>'nasal — like "ng" in sing','example'=>'nger','meaning'=>'goat'],
    ['letter'=>'NY ny','ipa'=>'/ɲ/','sound'=>'palatal nasal — like "ny" in canyon','example'=>'nyam','meaning'=>'animal'],
    ['letter'=>'TS ts','ipa'=>'/ts/','sound'=>'affricate — like "ts" in cats','example'=>'tser','meaning'=>'small'],
];

/* Try to load from DB; fall back to defaults if table is empty */
$_usingDB = false;
try {
    require_once BASE_PATH . '/models/TivAlphabetEntry.php';
    $_alphaModel = new TivAlphabetEntry();
    if ($_alphaModel->hasData()) {
        $_grouped  = $_alphaModel->getAllGrouped();
        $_usingDB  = true;
        /* Normalise DB rows to the same keys the view uses */
        $mapRow = fn($r) => [
            'letter'         => $r['letter'],
            'english_letter' => $r['english_letter'] ?? '',
            'ipa'            => $r['ipa'],
            'sound'          => $r['sound_desc'],
            'example'        => $r['tiv_example'],
            'meaning'        => $r['english_meaning'],
            'audio'          => $r['audio_file'] ?? null,
        ];
        $plainLetters = array_map($mapRow, $_grouped['plain'] ?? []);
        $vowels       = array_map($mapRow, $_grouped['vowel']);
        $consonants   = array_map($mapRow, $_grouped['consonant']);
        $digraphs     = array_map($mapRow, $_grouped['digraph']);
    }
} catch (Throwable $_e) { /* silently fall through to defaults */ }

if (!$_usingDB) {
    $plainLetters = [];
    $vowels       = $_defaultVowels;
    $consonants   = $_defaultConsonants;
    $digraphs     = $_defaultDigraphs;
}

$tones = [
    [
        'name'   => 'High Tone',
        'mark'   => '&#769;',
        'symbol' => '´',
        'desc'   => 'Marked with an acute accent (´) above the vowel. The voice rises or stays high.',
        'examples'=> [['tiv'=>'wán','en'=>'to call'],['tiv'=>'óo','en'=>'yes'],['tiv'=>'kár','en'=>'to read']],
        'color'  => '#2d5a9e',
        'bg'     => '#eff4ff',
    ],
    [
        'name'   => 'Low Tone',
        'mark'   => '&#768;',
        'symbol' => '`',
        'desc'   => 'Marked with a grave accent (`) above the vowel. The voice falls or stays low.',
        'examples'=> [['tiv'=>'wàn','en'=>'to be lost'],['tiv'=>'kàr','en'=>'to lie down'],['tiv'=>'tàr','en'=>'to be tired']],
        'color'  => '#5C3A21',
        'bg'     => '#fdf8f0',
    ],
    [
        'name'   => 'Mid / Level Tone',
        'mark'   => '&#8213;',
        'symbol' => 'unmarked',
        'desc'   => 'No accent mark. The voice stays at a middle level — the default in many written texts.',
        'examples'=> [['tiv'=>'wan','en'=>'word / story'],['tiv'=>'tar','en'=>'land'],['tiv'=>'nom','en'=>'thing']],
        'color'  => '#4a7c59',
        'bg'     => '#f0f7f3',
    ],
];

/* ── Sounds, Symbols & Spellings worksheets (seeded from the docx source files) ── */
$_worksheets           = json_decode(file_get_contents(__DIR__ . '/data/alphabet_worksheets.json'), true) ?: [];
$introductionRows      = $_worksheets['introduction'] ?? [];
$vowelWorksheets       = $_worksheets['vowel_worksheets'] ?? [];
$consonantWorksheets   = $_worksheets['consonant_worksheets'] ?? [];
$digraphWorksheets     = $_worksheets['digraph_worksheets'] ?? [];
$ghPronunciationNote   = $_worksheets['gh_pronunciation_note'] ?? '';

if (!function_exists('tiv_ws_slug')) {
    function tiv_ws_slug(string $symbol): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower($symbol)) ?: 'x';
    }
}
if (!function_exists('tiv_ws_table')) {
    function tiv_ws_table(array $columns, array $rows): void
    {
        echo '<div class="ws-table-wrap"><table class="ws-table"><thead><tr>';
        foreach ($columns as $col) {
            echo '<th>' . htmlspecialchars($col) . '</th>';
        }
        echo '</tr></thead><tbody>';
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($columns as $col) {
                $val = trim((string)($row[$col] ?? ''));
                echo '<td>' . ($val !== '' ? htmlspecialchars($val) : '<span class="ws-empty">—</span>') . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }
}
?>

<style>
/* ── Alphabet page styles ─────────────────────────────────────────── */
.alpha-banner { background: linear-gradient(135deg, #5C3A21 0%, #3d2610 100%); }

.alpha-strip {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem;
    margin: 1.5rem 0 2.5rem;
}
.alpha-strip-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 .6rem;
    border-radius: 8px;
    font-size: .95rem;
    font-weight: 700;
    text-decoration: none;
    transition: background .15s, transform .15s;
    cursor: default;
}
.alpha-strip-vowel   { background: #2d5a9e; color: #fff; }
.alpha-strip-cons    { background: #5C3A21; color: #fff; }
.alpha-strip-digraph { background: #4a7c59; color: #fff; }
.alpha-strip-pill:hover { transform: translateY(-2px); filter: brightness(1.12); }

.alpha-section-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #2d1b0e;
    margin: 2.5rem 0 1rem;
    padding-bottom: .5rem;
    border-bottom: 2px solid #e5e0d5;
    display: flex;
    align-items: center;
    gap: .5rem;
}

/* Letter card */
.alpha-grid {
    display: grid;
    gap: 1rem;
}
.alpha-grid-5 { grid-template-columns: repeat(5, 1fr); }
.alpha-grid-4 { grid-template-columns: repeat(4, 1fr); }
.alpha-grid-7 { grid-template-columns: repeat(7, 1fr); }

.alpha-card {
    background: #fff;
    border: 1px solid #e5e0d5;
    border-radius: 12px;
    padding: 1.1rem .9rem 1rem;
    text-align: center;
    transition: box-shadow .2s, transform .2s;
    cursor: default;
}
.alpha-card:hover {
    box-shadow: 0 4px 18px rgba(0,0,0,.1);
    transform: translateY(-2px);
}
.alpha-card-letter {
    font-size: 2.4rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: .3rem;
}
.alpha-card-ipa {
    font-size: .72rem;
    font-family: monospace;
    color: #7a6a5a;
    margin-bottom: .25rem;
}
.alpha-card-sound {
    font-size: .7rem;
    color: #9a8a7a;
    margin-bottom: .5rem;
    line-height: 1.3;
}
.alpha-card-example {
    display: inline-block;
    font-size: .78rem;
    font-weight: 700;
    padding: .18rem .5rem;
    border-radius: 6px;
    margin-bottom: .15rem;
}
.alpha-card-meaning {
    font-size: .68rem;
    color: #7a6a5a;
}

/* Vowel card */
.alpha-card--vowel   { border-color: #2d5a9e55; }
.alpha-card--vowel   .alpha-card-letter { color: #2d5a9e; }
.alpha-card--vowel   .alpha-card-example { background: #eff4ff; color: #2d5a9e; }
/* Consonant card */
.alpha-card--cons    { border-color: #5C3A2155; }
.alpha-card--cons    .alpha-card-letter { color: #5C3A21; }
.alpha-card--cons    .alpha-card-example { background: #fdf8f0; color: #5C3A21; }
/* Digraph card */
.alpha-card--digraph { border-color: #4a7c5955; background: #f8fbf9; }
.alpha-card--digraph .alpha-card-letter { color: #4a7c59; font-size: 1.9rem; }
.alpha-card--digraph .alpha-card-example { background: #edf7f0; color: #4a7c59; }

/* Letter pair display — capital + small clearly split */
.letter-pair {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: .25rem;
    line-height: 1;
}
.letter-pair .lp-cap  { font-size: 2.2rem; font-weight: 800; }
.letter-pair .lp-sep  { font-size: 1.1rem; color: #bbb; font-weight: 300; }
.letter-pair .lp-low  { font-size: 1.55rem; font-weight: 700; opacity: .75; }

/* Plain letters grid */
.plain-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: .65rem;
    margin-bottom: .5rem;
}
.plain-card {
    background: #fff;
    border: 1px solid #ddd5c5;
    border-radius: 10px;
    padding: .75rem .5rem .7rem;
    text-align: center;
    transition: box-shadow .18s, transform .18s;
}
.plain-card:hover { box-shadow: 0 3px 14px rgba(0,0,0,.1); transform: translateY(-2px); }
/* Row layout: letter on left, play button on right */
.plain-card .pc-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .3rem;
    width: 100%;
}
.plain-card .pc-tiv, .plain-card .pc-en { flex: 1; text-align: left; }
.plain-card .pc-play { margin-top: 0; flex-shrink: 0; width: 28px; height: 28px; font-size: .75rem; }
.plain-card .pc-div  { font-size: .58rem; color: #c0b0a0; margin: .2rem 0 .1rem; letter-spacing: .05em; text-transform: uppercase; text-align: left; }
.plain-card--en-only { border-color: #e0d8cc; background: #faf8f5; opacity: .82; }
/* Tiv letter sizes — bigger */
.plain-card .pc-tiv .lp-cap { font-size: 2.1rem; }
.plain-card .pc-tiv .lp-low { font-size: 1.5rem; }
/* English letter sizes — smaller */
.plain-card .pc-en .lp-cap  { font-size: 1.25rem; }
.plain-card .pc-en .lp-low  { font-size: .9rem; }
/* Q/X — English is the main letter, size it up */
.plain-card--en-only .pc-en .lp-cap { font-size: 2.1rem; }
.plain-card--en-only .pc-en .lp-low { font-size: 1.5rem; }
.plain-card .pc-not-in-tiv {
    font-size: .6rem; color: #b0a090; font-style: italic;
    margin-top: .25rem; letter-spacing: .02em; text-align: left;
}

/* Tone cards */
.tone-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.2rem; margin-bottom: 1rem; }
.tone-card {
    border-radius: 12px;
    padding: 1.4rem;
    border: 1px solid #e5e0d5;
}
.tone-card-mark   { font-size: 3rem; font-weight: 800; line-height: 1; margin-bottom: .5rem; }
.tone-card-name   { font-size: .95rem; font-weight: 700; margin-bottom: .4rem; }
.tone-card-desc   { font-size: .8rem; line-height: 1.6; color: #5a4a3a; margin-bottom: .9rem; }
.tone-examples    { display: flex; flex-direction: column; gap: .3rem; }
.tone-example-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .3rem .6rem;
    background: rgba(255,255,255,.7);
    border-radius: 6px;
    font-size: .82rem;
}
.tone-example-tiv  { font-weight: 700; }
.tone-example-en   { color: #7a6a5a; font-style: italic; }

@media (max-width: 900px) {
    .alpha-grid-7 { grid-template-columns: repeat(4, 1fr); }
    .alpha-grid-5 { grid-template-columns: repeat(3, 1fr); }
    .alpha-grid-4 { grid-template-columns: repeat(3, 1fr); }
    .tone-grid    { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .alpha-grid-7,
    .alpha-grid-5,
    .alpha-grid-4 { grid-template-columns: repeat(2, 1fr); }
    .alpha-card-letter { font-size: 1.8rem; }
    .alpha-card-sound  { display: none; }
}

/* Audio play button */
.alpha-play-btn {
    display: inline-flex; align-items: center; justify-content: center;
    margin-top: .55rem;
    width: 34px; height: 34px;
    border-radius: 50%;
    border: 2px solid transparent;
    background: #f0ece5;
    color: #5C3A21;
    font-size: .82rem;
    cursor: pointer;
    transition: background .15s, transform .15s, box-shadow .15s;
    position: relative;
}
.alpha-play-btn:hover {
    background: #5C3A21; color: #fff;
    transform: scale(1.12);
    box-shadow: 0 3px 10px rgba(92,58,33,.35);
}
.alpha-play-btn.playing {
    background: #5C3A21; color: #fff;
    border-color: #C8A951;
    animation: btn-pulse 1.2s ease-in-out infinite;
}
.alpha-play-btn.tts-btn { background: #edf7f0; color: #4a7c59; }
.alpha-play-btn.tts-btn:hover { background: #4a7c59; color: #fff; }
.alpha-play-btn.tts-btn.playing { background: #4a7c59; border-color: #C8A951; }
@keyframes btn-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(92,58,33,.45); }
    50%       { box-shadow: 0 0 0 7px rgba(92,58,33,0); }
}
/* Card glow when playing */
.alpha-card.is-playing {
    box-shadow: 0 0 0 3px #C8A951, 0 6px 20px rgba(0,0,0,.12);
}
.plain-card.is-playing {
    box-shadow: 0 0 0 3px #C8A951, 0 6px 18px rgba(0,0,0,.1);
}

/* ── Museum-style accordion ───────────────────────────────────────── */
.acc { display: flex; flex-direction: column; gap: 1.1rem; margin: 1.5rem 0 2.5rem; }

.acc-card {
    background: #fff;
    border: 1px solid #e5e0d5;
    border-radius: 18px;
    box-shadow: 0 2px 10px rgba(45,27,14,.06);
    overflow: hidden;
    transition: box-shadow .25s;
}
.acc-card.is-open { box-shadow: 0 8px 28px rgba(45,27,14,.12); }

.acc-card-header {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: left;
    font-family: inherit;
    color: inherit;
}
.acc-card-header:hover { background: #faf8f5; }
.acc-card-header:focus-visible {
    outline: none;
    box-shadow: inset 0 0 0 3px #C8A95199;
}
.acc-card-icon {
    flex-shrink: 0;
    width: 46px; height: 46px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    background: #fdf8f0;
}
.acc-card-heading { flex: 1; min-width: 0; }
.acc-card-title {
    font-size: 1.08rem;
    font-weight: 700;
    color: #2d1b0e;
    margin: 0 0 .2rem;
}
.acc-card-desc {
    font-size: .82rem;
    color: #7a6a5a;
    margin: 0;
    line-height: 1.5;
}
.acc-card-count {
    flex-shrink: 0;
    font-size: .72rem;
    font-weight: 700;
    color: #5C3A21;
    background: #fdf8f0;
    border: 1px solid #e5d5b8;
    border-radius: 20px;
    padding: .3rem .7rem;
    white-space: nowrap;
}
.acc-chevron {
    flex-shrink: 0;
    width: 22px; height: 22px;
    color: #9a8a7a;
    transition: transform .3s ease;
}
.acc-card.is-open > .acc-card-header .acc-chevron { transform: rotate(180deg); color: #5C3A21; }

.acc-card-body {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows .35s ease;
}
.acc-card.is-open > .acc-card-body { grid-template-rows: 1fr; }
.acc-card-body-inner { overflow: hidden; min-height: 0; }
.acc-card-body-pad { padding: 0 1.5rem 1.75rem; }

/* Nested worksheet accordion */
.acc-sub-intro {
    font-size: .82rem;
    color: #7a6a5a;
    margin: 0 0 .8rem;
}
.acc-sub { display: flex; flex-direction: column; gap: .6rem; }
.acc-sub-item {
    border: 1px solid #e9e3d6;
    border-radius: 12px;
    overflow: hidden;
    background: #fcfbf8;
}
.acc-sub-header {
    width: 100%;
    display: flex;
    align-items: center;
    gap: .7rem;
    padding: .85rem 1rem;
    background: transparent;
    border: none;
    cursor: pointer;
    text-align: left;
    font-family: inherit;
    color: inherit;
}
.acc-sub-header:hover { background: #f5f0e6; }
.acc-sub-header:focus-visible { outline: none; box-shadow: inset 0 0 0 3px #C8A95199; }
.acc-sub-symbol {
    flex-shrink: 0;
    min-width: 44px;
    text-align: center;
    font-weight: 800;
    font-size: .95rem;
    color: #5C3A21;
    background: #fdf8f0;
    border-radius: 8px;
    padding: .3rem .4rem;
}
.acc-sub-title { flex: 1; font-size: .88rem; font-weight: 600; color: #2d1b0e; }
.acc-sub-badge { flex-shrink: 0; font-size: .68rem; color: #9a8a7a; }
.acc-sub-chev { flex-shrink: 0; width: 16px; height: 16px; color: #9a8a7a; transition: transform .3s ease; }
.acc-sub-item.is-open .acc-sub-chev { transform: rotate(180deg); color: #5C3A21; }
.acc-sub-body { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .3s ease; }
.acc-sub-item.is-open .acc-sub-body { grid-template-rows: 1fr; }
.acc-sub-body-inner { overflow: hidden; min-height: 0; }
.acc-sub-body-pad { padding: 0 1rem 1rem; }

/* Worksheet tables — never let them force page-wide horizontal scroll */
.ws-table-wrap { overflow-x: auto; border-radius: 8px; border: 1px solid #ece6d8; }
.ws-table { width: 100%; min-width: 460px; border-collapse: collapse; font-size: .8rem; }
.ws-table th, .ws-table td { padding: .5rem .7rem; text-align: left; border-bottom: 1px solid #f0ebe0; white-space: nowrap; }
.ws-table th:nth-child(4), .ws-table td:nth-child(4),
.ws-table th:nth-child(5), .ws-table td:nth-child(5) { white-space: normal; }
.ws-table thead th { background: #faf7f2; font-weight: 700; color: #5C3A21; font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; }
.ws-table tbody tr:last-child td { border-bottom: none; }
.ws-table tbody tr:hover { background: #fbf9f4; }
.ws-empty { color: #c9beac; }

.intro-table { display: flex; flex-direction: column; gap: .7rem; margin-bottom: 1.6rem; }
.intro-row {
    display: grid;
    grid-template-columns: 150px 1fr;
    gap: 1rem;
    padding: .85rem 1rem;
    background: #faf8f5;
    border: 1px solid #ece6d8;
    border-radius: 10px;
}
.intro-row-label { font-size: .78rem; font-weight: 700; color: #5C3A21; text-transform: uppercase; letter-spacing: .03em; }
.intro-row-desc { font-size: .87rem; color: #3a2a1a; line-height: 1.6; }

@media (max-width: 700px) {
    .acc-card-header { padding: 1rem 1.1rem; gap: .7rem; }
    .acc-card-body-pad { padding: 0 1.1rem 1.4rem; }
    .acc-card-icon { width: 38px; height: 38px; font-size: 1.15rem; border-radius: 11px; }
    .acc-card-title { font-size: .96rem; }
    .acc-card-desc { display: none; }
    .acc-card-count { font-size: .66rem; padding: .25rem .55rem; }
    .intro-row { grid-template-columns: 1fr; gap: .3rem; }
}
</style>

<script>
(function () {
    var _audio     = null;
    var _activeBtn = null;
    var _activeCard = null;

    function stopActive() {
        if (_audio && !_audio.paused) { _audio.pause(); }
        if (window.speechSynthesis && speechSynthesis.speaking) { speechSynthesis.cancel(); }
        if (_activeBtn)  { _activeBtn.classList.remove('playing');  _activeBtn.textContent  = '▶'; }
        if (_activeCard) { _activeCard.classList.remove('is-playing'); }
        _activeBtn  = null;
        _activeCard = null;
    }

    window.playAlpha = function (btn) {
        var src   = btn.dataset.src  || '';
        var tts   = btn.dataset.tts  || '';
        var card  = btn.closest('.alpha-card, .plain-card');

        /* Toggle off if already playing this button */
        if (_activeBtn === btn) { stopActive(); return; }

        /* Stop whatever was playing */
        stopActive();

        _activeBtn  = btn;
        _activeCard = card;
        btn.classList.add('playing');
        btn.textContent = '⏸';
        if (card) card.classList.add('is-playing');

        function onDone() {
            btn.classList.remove('playing');
            btn.textContent = '▶';
            if (card) card.classList.remove('is-playing');
            _activeBtn  = null;
            _activeCard = null;
        }

        if (src) {
            /* ── Real audio file ── */
            if (!_audio) _audio = new Audio();
            _audio.src = src;
            _audio.play().catch(onDone);
            _audio.onended = onDone;
            _audio.onerror = onDone;
        } else if (tts && window.speechSynthesis) {
            /* ── Web Speech API fallback ── */
            var utter = new SpeechSynthesisUtterance(tts);
            utter.lang  = 'en-NG';   /* Nigerian English — closest available */
            utter.rate  = 0.78;
            utter.pitch = 1.05;
            /* Prefer a female voice if available */
            var voices = speechSynthesis.getVoices();
            var pick = voices.find(function(v){ return /en.*(NG|ZA|GB)/i.test(v.lang); })
                    || voices.find(function(v){ return /en/i.test(v.lang) && v.name.match(/female|woman|zira|hazel/i); })
                    || voices.find(function(v){ return /en/i.test(v.lang); });
            if (pick) utter.voice = pick;
            utter.onend   = onDone;
            utter.onerror = onDone;
            speechSynthesis.speak(utter);
        } else {
            onDone();
        }
    };

    /* Re-pick voices after they load asynchronously */
    if (window.speechSynthesis) {
        speechSynthesis.onvoiceschanged = function(){ /* voices cached on next call */ };
    }
})();

/* ── Accordion interaction ────────────────────────────────────────── */
(function () {
    function toggleTopCard(header) {
        var card = header.closest('.acc-card');
        var open = !card.classList.contains('is-open');
        card.classList.toggle('is-open', open);
        header.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function toggleSubItem(header) {
        var item  = header.closest('.acc-sub-item');
        var group = item.closest('.acc-sub');
        var open  = !item.classList.contains('is-open');

        if (group) {
            var openSiblings = group.querySelectorAll(':scope > .acc-sub-item.is-open');
            openSiblings.forEach(function (sibling) {
                if (sibling !== item) {
                    sibling.classList.remove('is-open');
                    var sibHeader = sibling.querySelector(':scope > .acc-sub-header');
                    if (sibHeader) sibHeader.setAttribute('aria-expanded', 'false');
                }
            });
        }

        item.classList.toggle('is-open', open);
        header.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    document.addEventListener('click', function (e) {
        var topHeader = e.target.closest('.acc-card-header');
        if (topHeader) { toggleTopCard(topHeader); return; }

        var subHeader = e.target.closest('.acc-sub-header');
        if (subHeader) { toggleSubItem(subHeader); return; }
    });
})();
</script>

<!-- Breadcrumb -->
<div style="background:#faf8f5;border-bottom:1px solid #e5e0d5;padding:.6rem 0;">
    <div class="container" style="font-size:.83rem;color:#7a6a5a;">
        <a href="<?= url('/') ?>" style="color:#5C3A21;text-decoration:none;">Home</a>
        <span style="margin:0 .4rem;">›</span>
        <a href="<?= url('language') ?>" style="color:#5C3A21;text-decoration:none;">Language</a>
        <span style="margin:0 .4rem;">›</span>
        <span>Alphabet</span>
    </div>
</div>

<!-- Banner -->
<div class="contribute-banner alpha-banner">
    <div class="container">
        <h1 class="page-banner-title">The Tiv Alphabet</h1>
        <p class="page-banner-sub">Vowels, consonants, digraphs, and the tonal system of the Tiv language</p>
        <p style="margin-top: .9rem;">
            <a href="<?= UPLOADS_URL ?>/downloads/tiv-alphabet.pdf" download
               style="display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .9rem;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.35);border-radius:6px;color:#fff;font-size:.8rem;font-weight:600;text-decoration:none;transition:background .15s;"
               onmouseover="this.style.background='rgba(255,255,255,.25)'"
               onmouseout="this.style.background='rgba(255,255,255,.15)'">
                &#128196; Download the Tiv Alphabet as PDF
            </a>
        </p>
        <?php if (is_moderator()): ?>
        <a href="<?= url('admin/alphabet') ?>"
           style="display:inline-flex;align-items:center;gap:.4rem;margin-top:.9rem;padding:.45rem .9rem;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.35);border-radius:6px;color:#fff;font-size:.8rem;font-weight:600;text-decoration:none;transition:background .15s;"
           onmouseover="this.style.background='rgba(255,255,255,.25)'"
           onmouseout="this.style.background='rgba(255,255,255,.15)'">
            &#9998; Edit Alphabet Entries
        </a>
        <?php endif; ?>
    </div>
</div>

<section style="padding:2rem 0 4rem;">
    <div class="container">

        <!-- ── Quick letter strip ────────────────────────────────────── -->
        <div class="alpha-strip">
            <?php foreach ($vowels as $v): ?>
                <span class="alpha-strip-pill alpha-strip-vowel"><?= explode(' ',$v['letter'])[0] ?></span>
            <?php endforeach; ?>
            <?php foreach ($consonants as $c): ?>
                <span class="alpha-strip-pill alpha-strip-cons"><?= explode(' ',$c['letter'])[0] ?></span>
            <?php endforeach; ?>
            <?php foreach ($digraphs as $d): ?>
                <span class="alpha-strip-pill alpha-strip-digraph"><?= explode(' ',$d['letter'])[0] ?></span>
            <?php endforeach; ?>
        </div>

        <!-- Legend -->
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
            <?php if (!empty($plainLetters)): ?>
            <span style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;"><span style="width:14px;height:14px;background:#7a4a2a;border-radius:3px;display:inline-block;"></span>Plain Letters (<?= count(array_filter($plainLetters, fn($p) => $p['letter'] !== '')) ?>)</span>
            <?php endif; ?>
            <span style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;"><span style="width:14px;height:14px;background:#2d5a9e;border-radius:3px;display:inline-block;"></span>Vowels (<?= count($vowels) ?>)</span>
            <span style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;"><span style="width:14px;height:14px;background:#5C3A21;border-radius:3px;display:inline-block;"></span>Consonants (<?= count($consonants) ?>)</span>
            <span style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;"><span style="width:14px;height:14px;background:#4a7c59;border-radius:3px;display:inline-block;"></span>Digraphs (<?= count($digraphs) ?>)</span>
        </div>

        <!-- ══════════════════════════════════════════════════════════════
             MUSEUM-STYLE ACCORDION — all existing content preserved below,
             only re-organised for navigation.
             ══════════════════════════════════════════════════════════════ -->
        <div class="acc">

            <!-- ── 1. INTRODUCTION ──────────────────────────────────────── -->
            <div class="acc-card" id="acc-introduction">
                <button class="acc-card-header" aria-expanded="false" aria-controls="acc-introduction-body">
                    <span class="acc-card-icon">&#128214;</span>
                    <span class="acc-card-heading">
                        <p class="acc-card-title">Introduction</p>
                        <p class="acc-card-desc">Sounds, symbols, and spellings of Tiv words — overview, plain letters, and the writing system.</p>
                    </span>
                    <svg class="acc-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="acc-card-body" id="acc-introduction-body">
                    <div class="acc-card-body-inner"><div class="acc-card-body-pad">

                        <div class="intro-table">
                            <?php foreach ($introductionRows as $row): ?>
                            <div class="intro-row">
                                <div class="intro-row-label"><?= htmlspecialchars($row['Section'] ?? '') ?></div>
                                <div class="intro-row-desc"><?= htmlspecialchars($row['Description'] ?? '') ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="acc-sub">

                            <!-- Nested: Plain Letters (existing content, unchanged) -->
                            <?php if (!empty($plainLetters)): ?>
                            <div class="acc-sub-item" id="acc-sub-plain-letters">
                                <button class="acc-sub-header" aria-expanded="false" aria-controls="acc-sub-plain-letters-body">
                                    <span class="acc-sub-symbol">Aa</span>
                                    <span class="acc-sub-title">Tiv Alphabet — Plain Letters</span>
                                    <span class="acc-sub-badge">Tiv &amp; English capital and small forms</span>
                                    <svg class="acc-sub-chev" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <div class="acc-sub-body" id="acc-sub-plain-letters-body">
                                    <div class="acc-sub-body-inner"><div class="acc-sub-body-pad">
                                        <div class="plain-grid">
                                            <?php foreach ($plainLetters as $pl):
                                                $hasTiv   = trim($pl['letter']) !== '';
                                                $tivParts = explode(' ', $pl['letter'], 2);
                                                $tivCap   = htmlspecialchars($tivParts[0] ?? '');
                                                $tivSmall = htmlspecialchars($tivParts[1] ?? '');
                                                $engParts = explode(' ', $pl['english_letter'], 2);
                                                $engCap   = htmlspecialchars($engParts[0] ?? '');
                                                $engSmall = htmlspecialchars($engParts[1] ?? '');
                                            ?>
                                            <div class="plain-card<?= $hasTiv ? '' : ' plain-card--en-only' ?>">

                                                <?php if ($hasTiv): ?>
                                                <!-- ── Tiv row ── -->
                                                <div class="pc-row">
                                                    <div class="pc-tiv">
                                                        <div class="letter-pair" style="color:#7a4a2a;">
                                                            <span class="lp-cap"><?= $tivCap ?></span>
                                                            <span class="lp-sep">/</span>
                                                            <span class="lp-low"><?= $tivSmall ?></span>
                                                        </div>
                                                    </div>
                                                    <!-- Tiv audio: uploaded file when available, TTS fallback -->
                                                    <button class="alpha-play-btn pc-play<?= empty($pl['audio']) ? ' tts-btn' : '' ?>"
                                                            onclick="playAlpha(this)"
                                                            data-src="<?= !empty($pl['audio']) ? UPLOADS_URL.'/'.htmlspecialchars($pl['audio']) : '' ?>"
                                                            data-tts="<?= $tivCap ?>"
                                                            title="<?= !empty($pl['audio']) ? 'Play Tiv pronunciation' : 'Hear Tiv letter (voice)' ?>"
                                                            aria-label="Play Tiv <?= $tivCap ?>">
                                                        &#9654;
                                                    </button>
                                                </div>

                                                <!-- ── English row ── -->
                                                <div class="pc-div">English</div>
                                                <div class="pc-row">
                                                    <div class="pc-en">
                                                        <div class="letter-pair" style="color:#8a7a6a;">
                                                            <span class="lp-cap" style="font-size:1.3rem;"><?= $engCap ?></span>
                                                            <span class="lp-sep" style="font-size:.9rem;">/</span>
                                                            <span class="lp-low" style="font-size:.95rem;"><?= $engSmall ?></span>
                                                        </div>
                                                    </div>
                                                    <!-- English: always TTS -->
                                                    <button class="alpha-play-btn pc-play tts-btn"
                                                            onclick="playAlpha(this)"
                                                            data-src=""
                                                            data-tts="<?= $engCap ?>"
                                                            title="Hear English letter (voice)"
                                                            aria-label="Play English <?= $engCap ?>">
                                                        &#9654;
                                                    </button>
                                                </div>

                                                <?php else: ?>
                                                <!-- English only (Q and X — not in Tiv) -->
                                                <div class="pc-row">
                                                    <div class="pc-en">
                                                        <div class="letter-pair" style="color:#8a7a6a;">
                                                            <span class="lp-cap"><?= $engCap ?></span>
                                                            <span class="lp-sep">/</span>
                                                            <span class="lp-low"><?= $engSmall ?></span>
                                                        </div>
                                                    </div>
                                                    <button class="alpha-play-btn pc-play tts-btn"
                                                            onclick="playAlpha(this)"
                                                            data-src=""
                                                            data-tts="<?= $engCap ?>"
                                                            title="Hear English letter (voice)"
                                                            aria-label="Play <?= $engCap ?>">
                                                        &#9654;
                                                    </button>
                                                </div>
                                                <div class="pc-not-in-tiv">not in Tiv</div>
                                                <?php endif; ?>

                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div></div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Nested: Writing System (existing content, unchanged) -->
                            <div class="acc-sub-item" id="acc-sub-writing-system">
                                <button class="acc-sub-header" aria-expanded="false" aria-controls="acc-sub-writing-system-body">
                                    <span class="acc-sub-symbol">&#9998;</span>
                                    <span class="acc-sub-title">Writing System</span>
                                    <span class="acc-sub-badge">Direction, script &amp; orthography</span>
                                    <svg class="acc-sub-chev" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <div class="acc-sub-body" id="acc-sub-writing-system-body">
                                    <div class="acc-sub-body-inner"><div class="acc-sub-body-pad">
                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;">
                                            <div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:1.2rem;">
                                                <h3 style="font-size:.95rem;font-weight:700;color:#5C3A21;margin:0 0 .6rem;">Direction &amp; Script</h3>
                                                <p style="font-size:.84rem;color:#5a4a3a;line-height:1.7;margin:0;">
                                                    Tiv is written <strong>left to right</strong> using the <strong>Latin script</strong>. There is no traditional indigenous script — the current writing system was developed in the 20th century with the aid of linguists and missionaries.
                                                </p>
                                            </div>
                                            <div style="background:#fff;border:1px solid #e5e0d5;border-radius:10px;padding:1.2rem;">
                                                <h3 style="font-size:.95rem;font-weight:700;color:#5C3A21;margin:0 0 .6rem;">Orthography</h3>
                                                <p style="font-size:.84rem;color:#5a4a3a;line-height:1.7;margin:0;">
                                                    The standard Tiv orthography used in schools and the Bible translation treats digraphs (gb, kp, mb, nd, ng, ny, ts) as single letters. Tone marks are used in formal linguistic texts but are often omitted in everyday writing.
                                                </p>
                                            </div>
                                        </div>
                                    </div></div>
                                </div>
                            </div>

                        </div>
                    </div></div>
                </div>
            </div>

            <!-- ── 2. VOWEL SOUNDS ──────────────────────────────────────── -->
            <div class="acc-card" id="acc-vowels">
                <button class="acc-card-header" aria-expanded="false" aria-controls="acc-vowels-body">
                    <span class="acc-card-icon">&#128292;</span>
                    <span class="acc-card-heading">
                        <p class="acc-card-title">Vowel Sounds</p>
                        <p class="acc-card-desc">The 5 vowel letters of Tiv, plus <?= count($vowelWorksheets) ?> worksheets of example words.</p>
                    </span>
                    <span class="acc-card-count"><?= count($vowelWorksheets) ?> worksheets</span>
                    <svg class="acc-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="acc-card-body" id="acc-vowels-body">
                    <div class="acc-card-body-inner"><div class="acc-card-body-pad">

                        <h2 class="alpha-section-title" style="margin-top:0;">
                            <span style="background:#2d5a9e;color:#fff;width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;flex-shrink:0;">V</span>
                            Vowels
                            <span style="font-weight:400;font-size:.85rem;color:#7a6a5a;margin-left:.3rem;">— 5 vowel sounds</span>
                        </h2>
                        <div class="alpha-grid alpha-grid-5">
                            <?php foreach ($vowels as $v):
                                $_vp = explode(' ', $v['letter'], 2); ?>
                            <div class="alpha-card alpha-card--vowel">
                                <div class="alpha-card-letter">
                                    <div class="letter-pair">
                                        <span class="lp-cap"><?= htmlspecialchars($_vp[0] ?? $v['letter']) ?></span>
                                        <?php if (!empty($_vp[1])): ?>
                                        <span class="lp-sep">/</span>
                                        <span class="lp-low"><?= htmlspecialchars($_vp[1]) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="alpha-card-ipa"><?= $v['ipa'] ?></div>
                                <div class="alpha-card-sound"><?= $v['sound'] ?></div>
                                <div class="alpha-card-example"><?= htmlspecialchars($v['example']) ?></div>
                                <div class="alpha-card-meaning">"<?= htmlspecialchars($v['meaning']) ?>"</div>
                                <button class="alpha-play-btn<?= empty($v['audio']) ? ' tts-btn' : '' ?>"
                                        onclick="playAlpha(this)"
                                        data-src="<?= !empty($v['audio']) ? UPLOADS_URL.'/'.htmlspecialchars($v['audio']) : '' ?>"
                                        data-tts="<?= htmlspecialchars($v['example']) ?>"
                                        title="<?= !empty($v['audio']) ? 'Play recording' : 'Hear example word (voice)' ?>"
                                        aria-label="Play <?= htmlspecialchars($v['letter']) ?>">
                                    &#9654;
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Vowel note -->
                        <div style="margin-top:.9rem;background:#eff4ff;border-left:3px solid #2d5a9e;border-radius:0 8px 8px 0;padding:.85rem 1.1rem;font-size:.82rem;color:#2d3a5a;line-height:1.65;">
                            <strong>Note on Tiv vowels:</strong> Vowels can be short or long. A long vowel is written by doubling the letter — <em>a</em> (short) vs <em>aa</em> (long). Long vowels carry a distinct meaning: <strong>or</strong> (person) vs <strong>oor</strong> (to be sick).
                        </div>

                        <?php if (!empty($vowelWorksheets)): ?>
                        <h3 style="font-size:.95rem;font-weight:700;color:#2d1b0e;margin:2rem 0 .3rem;">Vowel Sound Worksheets</h3>
                        <p class="acc-sub-intro">Contains <?= count($vowelWorksheets) ?> vowel sound<?= count($vowelWorksheets) === 1 ? '' : 's' ?>. Click a worksheet to reveal its word list.</p>
                        <div class="acc-sub">
                            <?php foreach ($vowelWorksheets as $ws):
                                $wsId = 'acc-sub-vowel-' . tiv_ws_slug($ws['sound_label'] ?? $ws['num']); ?>
                            <div class="acc-sub-item" id="<?= $wsId ?>">
                                <button class="acc-sub-header" aria-expanded="false" aria-controls="<?= $wsId ?>-body">
                                    <span class="acc-sub-symbol"><?= htmlspecialchars($ws['sound_label'] ?? '') ?></span>
                                    <span class="acc-sub-title"><?= htmlspecialchars($ws['title'] ?? '') ?></span>
                                    <span class="acc-sub-badge"><?= count($ws['rows'] ?? []) ?> words</span>
                                    <svg class="acc-sub-chev" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <div class="acc-sub-body" id="<?= $wsId ?>-body">
                                    <div class="acc-sub-body-inner"><div class="acc-sub-body-pad">
                                        <?php tiv_ws_table($ws['columns'] ?? [], $ws['rows'] ?? []); ?>
                                    </div></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    </div></div>
                </div>
            </div>

            <!-- ── 3. CONSONANT SOUNDS ──────────────────────────────────── -->
            <div class="acc-card" id="acc-consonants">
                <button class="acc-card-header" aria-expanded="false" aria-controls="acc-consonants-body">
                    <span class="acc-card-icon">&#128288;</span>
                    <span class="acc-card-heading">
                        <p class="acc-card-title">Consonant Sounds</p>
                        <p class="acc-card-desc"><?= count($consonants) ?> consonants, plus <?= count($consonantWorksheets) ?> pronunciation worksheets.</p>
                    </span>
                    <span class="acc-card-count"><?= count($consonantWorksheets) ?> worksheets</span>
                    <svg class="acc-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="acc-card-body" id="acc-consonants-body">
                    <div class="acc-card-body-inner"><div class="acc-card-body-pad">

                        <h2 class="alpha-section-title" style="margin-top:0;">
                            <span style="background:#5C3A21;color:#fff;width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;flex-shrink:0;">C</span>
                            Consonants
                            <span style="font-weight:400;font-size:.85rem;color:#7a6a5a;margin-left:.3rem;">— <?= count($consonants) ?> consonants</span>
                        </h2>
                        <div class="alpha-grid alpha-grid-4" style="grid-template-columns:repeat(auto-fill,minmax(160px,1fr));">
                            <?php foreach ($consonants as $c):
                                $_cp = explode(' ', $c['letter'], 2); ?>
                            <div class="alpha-card alpha-card--cons">
                                <div class="alpha-card-letter">
                                    <div class="letter-pair">
                                        <span class="lp-cap"><?= htmlspecialchars($_cp[0] ?? $c['letter']) ?></span>
                                        <?php if (!empty($_cp[1])): ?>
                                        <span class="lp-sep">/</span>
                                        <span class="lp-low"><?= htmlspecialchars($_cp[1]) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="alpha-card-ipa"><?= $c['ipa'] ?></div>
                                <div class="alpha-card-sound"><?= $c['sound'] ?></div>
                                <div class="alpha-card-example"><?= htmlspecialchars($c['example']) ?></div>
                                <div class="alpha-card-meaning">"<?= htmlspecialchars($c['meaning']) ?>"</div>
                                <button class="alpha-play-btn<?= empty($c['audio']) ? ' tts-btn' : '' ?>"
                                        onclick="playAlpha(this)"
                                        data-src="<?= !empty($c['audio']) ? UPLOADS_URL.'/'.htmlspecialchars($c['audio']) : '' ?>"
                                        data-tts="<?= htmlspecialchars($c['example']) ?>"
                                        title="<?= !empty($c['audio']) ? 'Play recording' : 'Hear example word (voice)' ?>"
                                        aria-label="Play <?= htmlspecialchars($c['letter']) ?>">
                                    &#9654;
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($consonantWorksheets)): ?>
                        <h3 style="font-size:.95rem;font-weight:700;color:#2d1b0e;margin:2rem 0 .3rem;">Consonant Sound Worksheets</h3>
                        <p class="acc-sub-intro">Contains <?= count($consonantWorksheets) ?> consonant sounds, drawn from the Consonant-Consonant Symbols and Spellings reference. Click a symbol to reveal its word list.</p>
                        <div class="acc-sub">
                            <?php foreach ($consonantWorksheets as $cw):
                                $wsId = 'acc-sub-cons-' . tiv_ws_slug($cw['symbol']); ?>
                            <div class="acc-sub-item" id="<?= $wsId ?>">
                                <button class="acc-sub-header" aria-expanded="false" aria-controls="<?= $wsId ?>-body">
                                    <span class="acc-sub-symbol"><?= htmlspecialchars($cw['symbol']) ?></span>
                                    <span class="acc-sub-title">Consonant Sound <?= htmlspecialchars($cw['symbol']) ?></span>
                                    <span class="acc-sub-badge"><?= count($cw['rows'] ?? []) ?> words</span>
                                    <svg class="acc-sub-chev" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <div class="acc-sub-body" id="<?= $wsId ?>-body">
                                    <div class="acc-sub-body-inner"><div class="acc-sub-body-pad">
                                        <?php tiv_ws_table(['Tiv Word', 'English Meaning', 'Notes'], $cw['rows'] ?? []); ?>
                                    </div></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    </div></div>
                </div>
            </div>

            <!-- ── 4. DIGRAPHS ──────────────────────────────────────────── -->
            <div class="acc-card" id="acc-digraphs">
                <button class="acc-card-header" aria-expanded="false" aria-controls="acc-digraphs-body">
                    <span class="acc-card-icon">&#128279;</span>
                    <span class="acc-card-heading">
                        <p class="acc-card-title">Digraphs</p>
                        <p class="acc-card-desc"><?= count($digraphs) ?> digraphs, plus <?= count($digraphWorksheets) ?> consonant-consonant worksheets.</p>
                    </span>
                    <span class="acc-card-count"><?= count($digraphWorksheets) ?> worksheets</span>
                    <svg class="acc-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="acc-card-body" id="acc-digraphs-body">
                    <div class="acc-card-body-inner"><div class="acc-card-body-pad">

                        <h2 class="alpha-section-title" style="margin-top:0;">
                            <span style="background:#4a7c59;color:#fff;width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;flex-shrink:0;">D</span>
                            Digraphs &amp; Special Consonants
                            <span style="font-weight:400;font-size:.85rem;color:#7a6a5a;margin-left:.3rem;">— <?= count($digraphs) ?> unique blended sounds</span>
                        </h2>
                        <p style="color:#5a4a3a;font-size:.88rem;line-height:1.65;margin-bottom:1.2rem;max-width:720px;">
                            Digraphs are two-letter combinations counted as a single sound. They are unique features of the Tiv language and must not be split when reading or writing.
                        </p>
                        <div class="alpha-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));">
                            <?php foreach ($digraphs as $d):
                                $_dp = explode(' ', $d['letter'], 2); ?>
                            <div class="alpha-card alpha-card--digraph" style="text-align:left;padding:1.1rem;">
                                <div style="display:flex;align-items:center;gap:.8rem;margin-bottom:.5rem;">
                                    <div class="letter-pair" style="color:#4a7c59;">
                                        <span class="lp-cap" style="font-size:1.85rem;"><?= htmlspecialchars($_dp[0] ?? $d['letter']) ?></span>
                                        <?php if (!empty($_dp[1])): ?>
                                        <span class="lp-sep">/</span>
                                        <span class="lp-low" style="font-size:1.3rem;"><?= htmlspecialchars($_dp[1]) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="alpha-card-ipa" style="font-size:.8rem;"><?= $d['ipa'] ?></span>
                                </div>
                                <div class="alpha-card-sound" style="text-align:left;font-size:.75rem;margin-bottom:.5rem;color:#5a4a3a;"><?= $d['sound'] ?></div>
                                <div style="display:flex;align-items:center;gap:.5rem;margin-top:.3rem;">
                                    <span class="alpha-card-example"><?= htmlspecialchars($d['example']) ?></span>
                                    <span class="alpha-card-meaning" style="font-size:.75rem;">"<?= htmlspecialchars($d['meaning']) ?>"</span>
                                </div>
                                <button class="alpha-play-btn<?= empty($d['audio']) ? ' tts-btn' : '' ?>"
                                        onclick="playAlpha(this)"
                                        data-src="<?= !empty($d['audio']) ? UPLOADS_URL.'/'.htmlspecialchars($d['audio']) : '' ?>"
                                        data-tts="<?= htmlspecialchars($d['example']) ?>"
                                        title="<?= !empty($d['audio']) ? 'Play recording' : 'Hear example word (voice)' ?>"
                                        aria-label="Play <?= htmlspecialchars($d['letter']) ?>">
                                    &#9654;
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($digraphWorksheets)): ?>
                        <h3 style="font-size:.95rem;font-weight:700;color:#2d1b0e;margin:2rem 0 .3rem;">Consonant-Consonant Worksheets</h3>
                        <p class="acc-sub-intro">Contains <?= count($digraphWorksheets) ?> consonant-consonant combinations. Click a symbol to reveal its word list.</p>
                        <div class="acc-sub">
                            <?php foreach ($digraphWorksheets as $dw):
                                $wsId = 'acc-sub-dig-' . tiv_ws_slug($dw['symbol']); ?>
                            <div class="acc-sub-item" id="<?= $wsId ?>">
                                <button class="acc-sub-header" aria-expanded="false" aria-controls="<?= $wsId ?>-body">
                                    <span class="acc-sub-symbol"><?= htmlspecialchars($dw['symbol']) ?></span>
                                    <span class="acc-sub-title">Consonant-Consonant <?= htmlspecialchars($dw['symbol']) ?></span>
                                    <span class="acc-sub-badge"><?= count($dw['rows'] ?? []) ?> words</span>
                                    <svg class="acc-sub-chev" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <div class="acc-sub-body" id="<?= $wsId ?>-body">
                                    <div class="acc-sub-body-inner"><div class="acc-sub-body-pad">
                                        <?php if ($dw['symbol'] === '/gh/' && $ghPronunciationNote): ?>
                                        <p style="font-size:.8rem;color:#5a4a3a;line-height:1.6;margin:0 0 .8rem;font-style:italic;"><?= htmlspecialchars($ghPronunciationNote) ?></p>
                                        <?php endif; ?>
                                        <?php tiv_ws_table(['Tiv Word', 'English Meaning', 'Notes'], $dw['rows'] ?? []); ?>
                                    </div></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    </div></div>
                </div>
            </div>

            <!-- ── 5. TONAL SYSTEM ──────────────────────────────────────── -->
            <div class="acc-card" id="acc-tonal">
                <button class="acc-card-header" aria-expanded="false" aria-controls="acc-tonal-body">
                    <span class="acc-card-icon">&#127925;</span>
                    <span class="acc-card-heading">
                        <p class="acc-card-title">Tonal System</p>
                        <p class="acc-card-desc">Tiv is a tonal language — <?= count($tones) ?> tone types change meaning.</p>
                    </span>
                    <span class="acc-card-count"><?= count($tones) ?> tone types</span>
                    <svg class="acc-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="acc-card-body" id="acc-tonal-body">
                    <div class="acc-card-body-inner"><div class="acc-card-body-pad">

                        <h2 class="alpha-section-title" style="margin-top:0;">
                            <span style="background:#C8A951;color:#5C3A21;width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:700;flex-shrink:0;">T</span>
                            Tone Markers
                            <span style="font-weight:400;font-size:.85rem;color:#7a6a5a;margin-left:.3rem;">— Tiv is a tonal language</span>
                        </h2>
                        <p style="color:#5a4a3a;font-size:.88rem;line-height:1.65;margin-bottom:1.4rem;max-width:740px;">
                            Tone is part of the meaning in Tiv. The same sequence of letters can mean completely different things depending on the pitch at which vowels are spoken. Tones are marked using diacritical accents on vowels.
                        </p>

                        <div class="acc-sub">
                            <?php foreach ($tones as $tIdx => $tone):
                                $wsId = 'acc-sub-tone-' . $tIdx; ?>
                            <div class="acc-sub-item" id="<?= $wsId ?>">
                                <button class="acc-sub-header" aria-expanded="false" aria-controls="<?= $wsId ?>-body">
                                    <span class="acc-sub-symbol" style="color:<?= $tone['color'] ?>;"><?= $tone['mark'] ?></span>
                                    <span class="acc-sub-title"><?= $tone['name'] ?></span>
                                    <span class="acc-sub-badge"><?= count($tone['examples']) ?> examples</span>
                                    <svg class="acc-sub-chev" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <div class="acc-sub-body" id="<?= $wsId ?>-body">
                                    <div class="acc-sub-body-inner"><div class="acc-sub-body-pad">
                                        <div class="tone-card" style="background:<?= $tone['bg'] ?>;border-color:<?= $tone['color'] ?>44;">
                                            <div class="tone-card-mark" style="color:<?= $tone['color'] ?>;">
                                                <?= $tone['mark'] ?> &nbsp; <span style="font-style:italic;">a<?= $tone['symbol'] !== 'unmarked' ? $tone['symbol'] : '' ?></span>
                                            </div>
                                            <div class="tone-card-name" style="color:<?= $tone['color'] ?>;"><?= $tone['name'] ?></div>
                                            <div class="tone-card-desc"><?= $tone['desc'] ?></div>
                                            <div class="tone-examples">
                                                <?php foreach ($tone['examples'] as $ex): ?>
                                                <div class="tone-example-row">
                                                    <span class="tone-example-tiv" style="color:<?= $tone['color'] ?>;"><?= $ex['tiv'] ?></span>
                                                    <span class="tone-example-en"><?= $ex['en'] ?></span>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Tone minimal pairs note -->
                        <div style="margin-top:1.2rem;background:#fdf8f0;border-left:3px solid #C8A951;border-radius:0 8px 8px 0;padding:.85rem 1.1rem;font-size:.82rem;color:#3a2a1a;line-height:1.7;">
                            <strong>Tonal minimal pairs — same letters, different meaning:</strong><br>
                            <span style="font-style:italic;">wán</span> (to call) &nbsp;|&nbsp; <span style="font-style:italic;">wàn</span> (to be lost) &nbsp;|&nbsp; <span style="font-style:italic;">wan</span> (word / story)<br>
                            <span style="font-style:italic;">kár</span> (to read / count) &nbsp;|&nbsp; <span style="font-style:italic;">kàr</span> (to lie down)
                        </div>

                    </div></div>
                </div>
            </div>

        </div>
        <!-- ── End accordion ─────────────────────────────────────────── -->

        <!-- ── Admin-added alphabet items ────────────────────────────── -->
        <?php if (!empty($extraItems)): ?>
        <h2 class="alpha-section-title" style="margin-top:2.5rem;">
            <span style="font-size:1.3rem;">&#128218;</span>
            Additional Alphabet Resources
        </h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.2rem;">
            <?php foreach ($extraItems as $item): ?>
            <a href="<?= url('content-item/' . $item['id']) ?>"
               style="display:flex;flex-direction:column;gap:.5rem;padding:1rem;background:#fff;border:1px solid #e5e0d5;border-radius:10px;text-decoration:none;color:inherit;transition:box-shadow .15s;"
               onmouseover="this.style.boxShadow='0 3px 14px rgba(0,0,0,.09)'" onmouseout="this.style.boxShadow=''">
                <h3 style="font-size:.95rem;font-weight:700;color:#2d1b0e;margin:0;"><?= htmlspecialchars($item['title']) ?></h3>
                <?php if (!empty($item['tiv_title'])): ?>
                    <p style="font-size:.82rem;color:#5C3A21;font-style:italic;margin:0;"><?= htmlspecialchars($item['tiv_title']) ?></p>
                <?php endif; ?>
                <?php if (!empty($item['excerpt'])): ?>
                    <p style="font-size:.8rem;color:#5a4a3a;margin:0;line-height:1.5;"><?= htmlspecialchars(mb_substr($item['excerpt'], 0, 100)) ?>…</p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- CTAs -->
        <div style="margin-top:3rem;padding-top:2rem;border-top:1px solid #e5e0d5;display:flex;gap:1rem;flex-wrap:wrap;">
            <a href="<?= url('archive/words') ?>" class="btn btn-primary">&#128218; Browse Dictionary</a>
            <a href="<?= url('learn') ?>" class="btn btn-secondary">&#127979; Language Lessons</a>
            <a href="<?= url('translate') ?>" class="btn btn-secondary">&#127760; Translator</a>
            <a href="<?= url('community/join') ?>" class="btn btn-secondary">&#128101; Join Community</a>
        </div>

    </div>
</section>
