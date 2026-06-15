<?php

require_once BASE_PATH . '/core/Controller.php';
require_once BASE_PATH . '/models/TivAlphabetEntry.php';

class AdminAlphabetController extends Controller
{
    private TivAlphabetEntry $model;
    private array $validTypes = ['plain', 'vowel', 'consonant', 'digraph', 'tonal'];

    /* ── Built-in defaults (seeded automatically on first visit) ─────── */
    private array $defaults = [
        'plain' => [
            ['letter'=>'A a','english_letter'=>'A a'],
            ['letter'=>'B b','english_letter'=>'B b'],
            ['letter'=>'C c','english_letter'=>'C c'],
            ['letter'=>'D d','english_letter'=>'D d'],
            ['letter'=>'E e','english_letter'=>'E e'],
            ['letter'=>'F f','english_letter'=>'F f'],
            ['letter'=>'G g','english_letter'=>'G g'],
            ['letter'=>'H h','english_letter'=>'H h'],
            ['letter'=>'I i','english_letter'=>'I i'],
            ['letter'=>'J j','english_letter'=>'J j'],
            ['letter'=>'K k','english_letter'=>'K k'],
            ['letter'=>'L l','english_letter'=>'L l'],
            ['letter'=>'M m','english_letter'=>'M m'],
            ['letter'=>'N n','english_letter'=>'N n'],
            ['letter'=>'O o','english_letter'=>'O o'],
            ['letter'=>'P p','english_letter'=>'P p'],
            ['letter'=>'',   'english_letter'=>'Q q'],
            ['letter'=>'R r','english_letter'=>'R r'],
            ['letter'=>'S s','english_letter'=>'S s'],
            ['letter'=>'T t','english_letter'=>'T t'],
            ['letter'=>'U u','english_letter'=>'U u'],
            ['letter'=>'V v','english_letter'=>'V v'],
            ['letter'=>'W w','english_letter'=>'W w'],
            ['letter'=>'',   'english_letter'=>'X x'],
            ['letter'=>'Y y','english_letter'=>'Y y'],
            ['letter'=>'Z z','english_letter'=>'Z z'],
        ],
        'vowel' => [
            ['letter'=>'A a','ipa'=>'/a/','sound_desc'=>'like "a" in father','tiv_example'=>'ata','english_meaning'=>'three'],
            ['letter'=>'E e','ipa'=>'/e/','sound_desc'=>'like "e" in bed','tiv_example'=>'eer','english_meaning'=>'blood'],
            ['letter'=>'I i','ipa'=>'/i/','sound_desc'=>'like "ee" in see','tiv_example'=>'ikyô','english_meaning'=>'tree'],
            ['letter'=>'O o','ipa'=>'/o/','sound_desc'=>'like "o" in go','tiv_example'=>'or','english_meaning'=>'person'],
            ['letter'=>'U u','ipa'=>'/u/','sound_desc'=>'like "oo" in food','tiv_example'=>'ukan','english_meaning'=>'fire'],
        ],
        'consonant' => [
            ['letter'=>'B b','ipa'=>'/b/','sound_desc'=>'like "b" in boy','tiv_example'=>'bam','english_meaning'=>'water'],
            ['letter'=>'D d','ipa'=>'/d/','sound_desc'=>'like "d" in dog','tiv_example'=>'doo','english_meaning'=>'come'],
            ['letter'=>'F f','ipa'=>'/f/','sound_desc'=>'like "f" in fish','tiv_example'=>'faan','english_meaning'=>'sweep'],
            ['letter'=>'G g','ipa'=>'/ɡ/','sound_desc'=>'like "g" in go','tiv_example'=>'ga','english_meaning'=>'no / not'],
            ['letter'=>'H h','ipa'=>'/h/','sound_desc'=>'like "h" in hat','tiv_example'=>'hembe','english_meaning'=>'shirt'],
            ['letter'=>'J j','ipa'=>'/dʒ/','sound_desc'=>'like "j" in jump','tiv_example'=>'jôô','english_meaning'=>'calabash'],
            ['letter'=>'K k','ipa'=>'/k/','sound_desc'=>'like "k" in key','tiv_example'=>'kar','english_meaning'=>'read / count'],
            ['letter'=>'L l','ipa'=>'/l/','sound_desc'=>'like "l" in love','tiv_example'=>'loo','english_meaning'=>'go'],
            ['letter'=>'M m','ipa'=>'/m/','sound_desc'=>'like "m" in man','tiv_example'=>'mba','english_meaning'=>'we / people'],
            ['letter'=>'N n','ipa'=>'/n/','sound_desc'=>'like "n" in now','tiv_example'=>'nom','english_meaning'=>'thing'],
            ['letter'=>'P p','ipa'=>'/p/','sound_desc'=>'like "p" in pen','tiv_example'=>'pan','english_meaning'=>'scatter'],
            ['letter'=>'R r','ipa'=>'/r/','sound_desc'=>'like "r" in run','tiv_example'=>'ren','english_meaning'=>'know'],
            ['letter'=>'S s','ipa'=>'/s/','sound_desc'=>'like "s" in sun','tiv_example'=>'sha','english_meaning'=>'in / on'],
            ['letter'=>'T t','ipa'=>'/t/','sound_desc'=>'like "t" in top','tiv_example'=>'tar','english_meaning'=>'land / country'],
            ['letter'=>'V v','ipa'=>'/v/','sound_desc'=>'like "v" in van','tiv_example'=>'vee','english_meaning'=>'eat'],
            ['letter'=>'W w','ipa'=>'/w/','sound_desc'=>'like "w" in water','tiv_example'=>'wan','english_meaning'=>'word / story'],
            ['letter'=>'Y y','ipa'=>'/j/','sound_desc'=>'like "y" in yes','tiv_example'=>'yôr','english_meaning'=>'person'],
            ['letter'=>'Z z','ipa'=>'/z/','sound_desc'=>'like "z" in zebra','tiv_example'=>'za','english_meaning'=>'go (away)'],
        ],
        'digraph' => [
            ['letter'=>'GB gb','ipa'=>'/ɡ͡b/','sound_desc'=>'labial-velar stop — "g" and "b" merged','tiv_example'=>'gba','english_meaning'=>'arm / branch'],
            ['letter'=>'KP kp','ipa'=>'/k͡p/','sound_desc'=>'labial-velar stop — "k" and "p" merged','tiv_example'=>'kpam','english_meaning'=>'wide / broad'],
            ['letter'=>'MB mb','ipa'=>'/ᵐb/','sound_desc'=>'prenasalized "b" — nose before lip','tiv_example'=>'mban','english_meaning'=>'night'],
            ['letter'=>'ND nd','ipa'=>'/ⁿd/','sound_desc'=>'prenasalized "d" — nasal onset','tiv_example'=>'nder','english_meaning'=>'wall'],
            ['letter'=>'NG ng','ipa'=>'/ŋ/','sound_desc'=>'nasal — like "ng" in sing','tiv_example'=>'nger','english_meaning'=>'goat'],
            ['letter'=>'NY ny','ipa'=>'/ɲ/','sound_desc'=>'palatal nasal — like "ny" in canyon','tiv_example'=>'nyam','english_meaning'=>'animal'],
            ['letter'=>'TS ts','ipa'=>'/ts/','sound_desc'=>'affricate — like "ts" in cats','tiv_example'=>'tser','english_meaning'=>'small'],
        ],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->requireModerator();
        $this->model = new TivAlphabetEntry();
    }

    public function index(): void
    {
        /* Auto-seed built-in defaults on first visit */
        if (!$this->model->hasData()) {
            $this->seedDefaults();
            $this->flash('Built-in alphabet entries have been loaded. You can now edit or correct any of them.', 'success');
        }

        $grouped = $this->model->getAllGrouped();

        $this->render('admin/alphabet/index', [
            'title'       => 'Manage Alphabet | Admin',
            'currentPage' => 'alphabet',
            'grouped'     => $grouped,
            'counts'      => [
                'plain'     => count(array_filter($grouped['plain'], fn($e) => $e['letter'] !== '')),
                'vowel'     => count($grouped['vowel']),
                'consonant' => count($grouped['consonant']),
                'digraph'   => count($grouped['digraph']),
                'tonal'     => count($grouped['tonal']),
            ],
        ], 'admin');
    }

    public function create(): void
    {
        $this->render('admin/alphabet/form', [
            'title'       => 'Add Alphabet Entry | Admin',
            'currentPage' => 'alphabet',
            'entry'       => null,
        ], 'admin');
    }

    public function store(): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }

        $type = trim($_POST['type'] ?? '');

        if (!in_array($type, $this->validTypes, true)) {
            $this->flash('Type is required.', 'error');
            $this->back();
            return;
        }

        /* For plain type, combine cap+small inputs into letter field */
        if ($type === 'plain') {
            $cap   = trim($_POST['letter_cap']   ?? '');
            $small = trim($_POST['letter_small'] ?? '');
            $letter = trim($cap . ' ' . $small);
            $engCap   = trim($_POST['english_cap']   ?? '');
            $engSmall = trim($_POST['english_small'] ?? '');
            $englishLetter = trim($engCap . ' ' . $engSmall);
        } else {
            $letter        = trim($_POST['letter']         ?? '');
            $englishLetter = trim($_POST['english_letter'] ?? '');
        }

        if (!$letter) {
            $this->flash('Letter is required.', 'error');
            $this->back();
            return;
        }

        /* Recorded base64 takes priority; fall back to file upload */
        $audioData = trim($_POST['audio_data'] ?? '');
        $audioFile = $audioData
            ? $this->saveAudioData($audioData)
            : $this->handleAudioUpload();

        $this->model->create([
            'type'            => $type,
            'letter'          => $letter,
            'english_letter'  => $englishLetter,
            'ipa'             => trim($_POST['ipa']             ?? ''),
            'sound_desc'      => trim($_POST['sound_desc']      ?? ''),
            'tiv_example'     => trim($_POST['tiv_example']     ?? ''),
            'english_meaning' => trim($_POST['english_meaning'] ?? ''),
            'audio_file'      => $audioFile,
            'sort_order'      => (int) ($_POST['sort_order']    ?? 0),
        ]);

        $this->flash(ucfirst($type) . ' "' . $letter . '" added.', 'success');
        $this->redirect(url('admin/alphabet'));
    }

    public function edit(string $id): void
    {
        $entry = $this->model->find((int) $id);
        if (!$entry) { $this->redirect(url('admin/alphabet')); return; }

        $this->render('admin/alphabet/form', [
            'title'       => 'Edit: ' . $entry['letter'] . ' | Admin',
            'currentPage' => 'alphabet',
            'entry'       => $entry,
        ], 'admin');
    }

    public function update(string $id): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }

        $entry = $this->model->find((int) $id);
        if (!$entry) { $this->redirect(url('admin/alphabet')); return; }

        $type = trim($_POST['type'] ?? $entry['type']);

        if (!in_array($type, $this->validTypes, true)) {
            $this->flash('Type is required.', 'error');
            $this->back();
            return;
        }

        /* For plain type, combine cap+small inputs into letter field */
        if ($type === 'plain') {
            $cap   = trim($_POST['letter_cap']   ?? '');
            $small = trim($_POST['letter_small'] ?? '');
            $letter = trim($cap . ' ' . $small) ?: $entry['letter'];
            $engCap   = trim($_POST['english_cap']   ?? '');
            $engSmall = trim($_POST['english_small'] ?? '');
            $englishLetter = trim($engCap . ' ' . $engSmall);
        } else {
            $letter        = trim($_POST['letter']         ?? $entry['letter']);
            $englishLetter = trim($_POST['english_letter'] ?? $entry['english_letter'] ?? '');
        }

        /* Keep existing audio unless replaced or removed */
        $audioFile = $entry['audio_file'];
        if (!empty($_POST['remove_audio'])) {
            $audioFile = null;
        } elseif (!empty(trim($_POST['audio_data'] ?? ''))) {
            $audioFile = $this->saveAudioData(trim($_POST['audio_data'])) ?? $audioFile;
        } elseif (!empty($_FILES['audio_file']['name'])) {
            $audioFile = $this->handleAudioUpload() ?? $audioFile;
        }

        $this->model->update((int) $id, [
            'type'            => $type,
            'letter'          => $letter,
            'english_letter'  => $englishLetter,
            'ipa'             => trim($_POST['ipa']             ?? ''),
            'sound_desc'      => trim($_POST['sound_desc']      ?? ''),
            'tiv_example'     => trim($_POST['tiv_example']     ?? ''),
            'english_meaning' => trim($_POST['english_meaning'] ?? ''),
            'audio_file'      => $audioFile,
            'sort_order'      => (int) ($_POST['sort_order']    ?? 0),
        ]);

        $this->flash('"' . $letter . '" updated.', 'success');
        $this->redirect(url('admin/alphabet'));
    }

    public function delete(string $id): void
    {
        if (!$this->validateCSRF()) { $this->back(); return; }
        $this->model->delete((int) $id);
        $this->flash('Entry deleted.', 'info');
        $this->redirect(url('admin/alphabet'));
    }

    /* ── Private helpers ─────────────────────────────────────────────── */

    private function seedDefaults(): void
    {
        $order = 1;
        foreach ($this->defaults as $type => $entries) {
            foreach ($entries as $e) {
                $this->model->create([
                    'type'            => $type,
                    'letter'          => $e['letter'],
                    'english_letter'  => $e['english_letter']  ?? '',
                    'ipa'             => $e['ipa']             ?? '',
                    'sound_desc'      => $e['sound_desc']      ?? '',
                    'tiv_example'     => $e['tiv_example']     ?? '',
                    'english_meaning' => $e['english_meaning'] ?? '',
                    'audio_file'      => null,
                    'sort_order'      => $order++,
                ]);
            }
        }
    }

    private function handleAudioUpload(): ?string
    {
        if (empty($_FILES['audio_file']['name'])) return null;

        $file = $_FILES['audio_file'];
        $allowedMime = ['audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav',
                         'audio/x-wav', 'audio/mp4', 'audio/webm'];
        $allowedExt  = ['mp3', 'ogg', 'wav', 'm4a', 'webm'];

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            $this->flash('Invalid audio format. Allowed: MP3, OGG, WAV, M4A, WebM.', 'error');
            return null;
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->flash('Audio file too large (max 5 MB).', 'error');
            return null;
        }

        $dir = UPLOADS_PATH . '/audio/alphabet';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = 'alpha_' . uniqid() . '.' . $ext;
        $dest     = $dir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'audio/alphabet/' . $filename;
        }
        return null;
    }

    private function saveAudioData(string $audioData): ?string
    {
        if (!preg_match('/^data:(audio\/[^;]+);base64,(.+)$/s', $audioData, $m)) {
            return null;
        }

        $binary = base64_decode($m[2]);
        if ($binary === false || strlen($binary) > 5 * 1024 * 1024) {
            return null;
        }

        $ext = match (true) {
            str_contains($m[1], 'mp3'), str_contains($m[1], 'mpeg') => 'mp3',
            str_contains($m[1], 'ogg')  => 'ogg',
            str_contains($m[1], 'wav')  => 'wav',
            default                     => 'webm',
        };

        $dir = UPLOADS_PATH . '/audio/alphabet';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = 'alpha_' . uniqid() . '.' . $ext;
        if (file_put_contents($dir . '/' . $filename, $binary)) {
            return 'audio/alphabet/' . $filename;
        }
        return null;
    }
}
