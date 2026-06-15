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
.plain-card .pc-tiv   { margin-bottom: .3rem; }
.plain-card .pc-div   { font-size: .65rem; color: #bbb; margin: .2rem 0; letter-spacing: .04em; text-transform: uppercase; }
.plain-card .pc-en    { opacity: .65; }

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
    margin-top: .5rem;
    width: 32px; height: 32px;
    border-radius: 50%;
    border: none;
    background: var(--color-surface-alt);
    color: var(--color-primary);
    font-size: .85rem;
    cursor: pointer;
    transition: background .15s, transform .15s;
}
.alpha-play-btn:hover { background: var(--color-accent); color: #fff; transform: scale(1.1); }
.alpha-play-btn.playing { background: var(--color-accent); color: #fff; }
</style>

<script>
(function () {
    var _audio = null;
    var _activeBtn = null;
    window.playAlpha = function (btn) {
        var src = btn.dataset.src;
        if (_activeBtn && _activeBtn !== btn) {
            _activeBtn.classList.remove('playing');
            _activeBtn.textContent = '▶';
        }
        if (_audio && !_audio.paused && _activeBtn === btn) {
            _audio.pause();
            btn.classList.remove('playing');
            btn.textContent = '▶';
            _activeBtn = null;
            return;
        }
        if (!_audio) _audio = new Audio();
        _audio.src = src;
        _audio.play();
        btn.classList.add('playing');
        btn.textContent = '⏸';
        _activeBtn = btn;
        _audio.onended = function () {
            btn.classList.remove('playing');
            btn.textContent = '▶';
            _activeBtn = null;
        };
    };
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
        <span class="page-banner-eyebrow">&#128172; Language</span>
        <h1 class="page-banner-title">The Tiv Alphabet</h1>
        <p class="page-banner-sub">Vowels, consonants, digraphs, and the tonal system of the Tiv language</p>
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
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2rem;">
            <?php if (!empty($plainLetters)): ?>
            <span style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;"><span style="width:14px;height:14px;background:#7a4a2a;border-radius:3px;display:inline-block;"></span>Plain Letters (<?= count($plainLetters) ?>)</span>
            <?php endif; ?>
            <span style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;"><span style="width:14px;height:14px;background:#2d5a9e;border-radius:3px;display:inline-block;"></span>Vowels (<?= count($vowels) ?>)</span>
            <span style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;"><span style="width:14px;height:14px;background:#5C3A21;border-radius:3px;display:inline-block;"></span>Consonants (<?= count($consonants) ?>)</span>
            <span style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;"><span style="width:14px;height:14px;background:#4a7c59;border-radius:3px;display:inline-block;"></span>Digraphs (<?= count($digraphs) ?>)</span>
        </div>

        <!-- ── PLAIN LETTERS ─────────────────────────────────────────── -->
        <?php if (!empty($plainLetters)): ?>
        <h2 class="alpha-section-title">
            <span style="background:#7a4a2a;color:#fff;width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;flex-shrink:0;">A</span>
            Tiv Alphabet — Plain Letters
            <span style="font-weight:400;font-size:.85rem;color:#7a6a5a;margin-left:.3rem;">Tiv &amp; English capital and small forms</span>
        </h2>
        <div class="plain-grid">
            <?php foreach ($plainLetters as $pl):
                $tivParts = explode(' ', $pl['letter'], 2);
                $tivCap   = htmlspecialchars($tivParts[0] ?? '');
                $tivSmall = htmlspecialchars($tivParts[1] ?? '');
                $engParts = explode(' ', $pl['english_letter'], 2);
                $engCap   = htmlspecialchars($engParts[0] ?? '');
                $engSmall = htmlspecialchars($engParts[1] ?? '');
            ?>
            <div class="plain-card">
                <!-- Tiv letter -->
                <div class="pc-tiv">
                    <div class="letter-pair" style="color:#7a4a2a;">
                        <span class="lp-cap"><?= $tivCap ?></span>
                        <span class="lp-sep">/</span>
                        <span class="lp-low"><?= $tivSmall ?></span>
                    </div>
                </div>
                <?php if ($engCap || $engSmall): ?>
                <div class="pc-div">English</div>
                <!-- English equivalent -->
                <div class="pc-en">
                    <div class="letter-pair" style="color:#5a4a3a;">
                        <span class="lp-cap" style="font-size:1.5rem;"><?= $engCap ?></span>
                        <span class="lp-sep">/</span>
                        <span class="lp-low" style="font-size:1.1rem;"><?= $engSmall ?></span>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($pl['audio'])): ?>
                <button class="alpha-play-btn" onclick="playAlpha(this)"
                        data-src="<?= UPLOADS_URL . '/' . htmlspecialchars($pl['audio']) ?>"
                        title="Hear pronunciation" aria-label="Play <?= htmlspecialchars($pl['letter']) ?>">
                    &#9654;
                </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ── VOWELS ─────────────────────────────────────────────────── -->
        <h2 class="alpha-section-title">
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
                <div class="alpha-card-example"><?= $v['example'] ?></div>
                <div class="alpha-card-meaning">"<?= $v['meaning'] ?>"</div>
                <?php if (!empty($v['audio'])): ?>
                <button class="alpha-play-btn" onclick="playAlpha(this)"
                        data-src="<?= UPLOADS_URL . '/' . htmlspecialchars($v['audio']) ?>"
                        title="Hear pronunciation" aria-label="Play <?= htmlspecialchars($v['letter']) ?>">
                    &#9654;
                </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Vowel note -->
        <div style="margin-top:.9rem;background:#eff4ff;border-left:3px solid #2d5a9e;border-radius:0 8px 8px 0;padding:.85rem 1.1rem;font-size:.82rem;color:#2d3a5a;line-height:1.65;">
            <strong>Note on Tiv vowels:</strong> Vowels can be short or long. A long vowel is written by doubling the letter — <em>a</em> (short) vs <em>aa</em> (long). Long vowels carry a distinct meaning: <strong>or</strong> (person) vs <strong>oor</strong> (to be sick).
        </div>

        <!-- ── CONSONANTS ─────────────────────────────────────────────── -->
        <h2 class="alpha-section-title">
            <span style="background:#5C3A21;color:#fff;width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;flex-shrink:0;">C</span>
            Consonants
            <span style="font-weight:400;font-size:.85rem;color:#7a6a5a;margin-left:.3rem;">— 18 single consonants</span>
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
                <div class="alpha-card-example"><?= $c['example'] ?></div>
                <div class="alpha-card-meaning">"<?= $c['meaning'] ?>"</div>
                <?php if (!empty($c['audio'])): ?>
                <button class="alpha-play-btn" onclick="playAlpha(this)"
                        data-src="<?= UPLOADS_URL . '/' . htmlspecialchars($c['audio']) ?>"
                        title="Hear pronunciation" aria-label="Play <?= htmlspecialchars($c['letter']) ?>">
                    &#9654;
                </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── DIGRAPHS ───────────────────────────────────────────────── -->
        <h2 class="alpha-section-title">
            <span style="background:#4a7c59;color:#fff;width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;flex-shrink:0;">D</span>
            Digraphs &amp; Special Consonants
            <span style="font-weight:400;font-size:.85rem;color:#7a6a5a;margin-left:.3rem;">— 7 unique to Tiv</span>
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
                    <span class="alpha-card-example"><?= $d['example'] ?></span>
                    <span class="alpha-card-meaning" style="font-size:.75rem;">"<?= $d['meaning'] ?>"</span>
                </div>
                <?php if (!empty($d['audio'])): ?>
                <button class="alpha-play-btn" onclick="playAlpha(this)"
                        data-src="<?= UPLOADS_URL . '/' . htmlspecialchars($d['audio']) ?>"
                        title="Hear pronunciation" aria-label="Play <?= htmlspecialchars($d['letter']) ?>">
                    &#9654;
                </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- ── TONE MARKERS ───────────────────────────────────────────── -->
        <h2 class="alpha-section-title">
            <span style="background:#C8A951;color:#5C3A21;width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.95rem;font-weight:700;flex-shrink:0;">T</span>
            Tone Markers
            <span style="font-weight:400;font-size:.85rem;color:#7a6a5a;margin-left:.3rem;">— Tiv is a tonal language</span>
        </h2>
        <p style="color:#5a4a3a;font-size:.88rem;line-height:1.65;margin-bottom:1.4rem;max-width:740px;">
            Tone is part of the meaning in Tiv. The same sequence of letters can mean completely different things depending on the pitch at which vowels are spoken. Tones are marked using diacritical accents on vowels.
        </p>
        <div class="tone-grid">
            <?php foreach ($tones as $tone): ?>
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
            <?php endforeach; ?>
        </div>

        <!-- Tone minimal pairs note -->
        <div style="margin-top:.9rem;background:#fdf8f0;border-left:3px solid #C8A951;border-radius:0 8px 8px 0;padding:.85rem 1.1rem;font-size:.82rem;color:#3a2a1a;line-height:1.7;">
            <strong>Tonal minimal pairs — same letters, different meaning:</strong><br>
            <span style="font-style:italic;">wán</span> (to call) &nbsp;|&nbsp; <span style="font-style:italic;">wàn</span> (to be lost) &nbsp;|&nbsp; <span style="font-style:italic;">wan</span> (word / story)<br>
            <span style="font-style:italic;">kár</span> (to read / count) &nbsp;|&nbsp; <span style="font-style:italic;">kàr</span> (to lie down)
        </div>

        <!-- ── Writing System note ───────────────────────────────────── -->
        <h2 class="alpha-section-title" style="margin-top:2.5rem;">
            <span style="font-size:1.3rem;">&#9998;</span>
            Writing System
        </h2>
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
