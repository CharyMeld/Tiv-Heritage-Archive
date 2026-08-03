<?php
/**
 * ContentIndexer — Builds and maintains the archive_search_index table.
 *
 * Called automatically when content is created/updated/deleted.
 * Also exposes indexAll() for admin-triggered full re-index.
 *
 * Every new piece of content is immediately searchable by ArchiveIntelligence
 * without any model retraining.
 */
class ContentIndexer
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ─────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────

    public function indexAll(): int
    {
        $total = 0;
        $total += $this->indexWords();
        $total += $this->indexNames();
        $total += $this->indexProverbs();
        $total += $this->indexPlants();
        $total += $this->indexFoods();
        $total += $this->indexFestivals();
        $total += $this->indexAnimals();
        $total += $this->indexBibleVerses();
        $total += $this->indexLearningVideos();
        $total += $this->indexAlphabetEntries();
        $total += $this->indexHistoricalFigures();

        // Extract text from uploaded files before indexing content_items
        require_once BASE_PATH . '/services/FileIndexer.php';
        $fileIndexer = new FileIndexer($this->db);
        $fileIndexer->processPending(100);

        $total += $this->indexContentItems();
        $total += $this->indexPhrases();
        $this->buildAutoLinks();
        $this->buildNgramStats();
        return $total;
    }

    /**
     * Index or re-index a single record when it is saved.
     * Call this from admin controllers after create/update.
     */
    public function indexRecord(string $table, int $id): void
    {
        switch ($table) {
            case 'daily_words':         $this->indexWordById($id);         break;
            case 'tiv_names':           $this->indexNameById($id);         break;
            case 'tiv_proverbs':        $this->indexProverbById($id);      break;
            case 'tiv_plants':          $this->indexPlantById($id);        break;
            case 'tiv_foods':           $this->indexFoodById($id);         break;
            case 'tiv_festivals':       $this->indexFestivalById($id);     break;
            case 'tiv_animals':         $this->indexAnimalById($id);       break;
            case 'content_items':       $this->indexContentItemById($id);  break;
            case 'translation_phrases': $this->indexPhraseById($id);       break;
            case 'learning_videos':     $this->indexVideoById($id);        break;
            case 'tiv_alphabet':        $this->indexAlphabetById($id);     break;
            case 'historical_figures':  $this->indexHistoricalFigureById($id); break;
        }
    }

    public function removeRecord(string $table, int $id): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM archive_search_index WHERE source_table = ? AND source_id = ?"
        );
        $stmt->execute([$table, $id]);
    }

    // ─────────────────────────────────────────────────────────────
    // Table indexers
    // ─────────────────────────────────────────────────────────────

    private function indexWords(): int
    {
        $rows = $this->db->query("SELECT * FROM daily_words WHERE is_active = 1")->fetchAll();
        foreach ($rows as $r) {
            $this->upsertIndex(
                table: 'daily_words',
                id: $r['id'],
                type: 'word',
                primary: $r['tiv_word'] . ' ' . ($r['example_tiv'] ?? ''),
                secondary: $r['english_meaning'] . ' ' . ($r['alternate_meaning'] ?? '') . ' ' . ($r['example_english'] ?? ''),
                excerpt: $r['english_meaning'],
                url: '/word/' . $r['id'],
                meta: ['part_of_speech' => $r['part_of_speech'] ?? '', 'pronunciation' => $r['pronunciation'] ?? '']
            );
        }
        return count($rows);
    }

    private function indexWordById(int $id): void
    {
        $r = $this->db->prepare("SELECT * FROM daily_words WHERE id = ?")->execute([$id]) ? null : null;
        $stmt = $this->db->prepare("SELECT * FROM daily_words WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { $this->removeRecord('daily_words', $id); return; }
        $this->upsertIndex(
            table: 'daily_words', id: $id, type: 'word',
            primary: $r['tiv_word'] . ' ' . ($r['example_tiv'] ?? ''),
            secondary: $r['english_meaning'] . ' ' . ($r['alternate_meaning'] ?? ''),
            excerpt: $r['english_meaning'],
            url: '/word/' . $id,
            meta: ['part_of_speech' => $r['part_of_speech'] ?? '', 'pronunciation' => $r['pronunciation'] ?? '']
        );
    }

    private function indexNames(): int
    {
        $rows = $this->db->query("SELECT * FROM tiv_names")->fetchAll();
        foreach ($rows as $r) {
            $primary = $r['tiv_name'] . ' ' . ($r['description'] ?? '') . ' ' . ($r['origin_story'] ?? '') . ' ' . ($r['usage_context'] ?? '');
            $this->upsertIndex(
                table: 'tiv_names', id: $r['id'], type: 'name',
                primary: $primary,
                secondary: $r['english_meaning'],
                excerpt: $r['english_meaning'],
                url: '/name/' . $r['id'],
                meta: ['gender' => $r['gender'] ?? '']
            );
        }
        return count($rows);
    }

    private function indexNameById(int $id): void
    {
        $stmt = $this->db->prepare("SELECT * FROM tiv_names WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { $this->removeRecord('tiv_names', $id); return; }
        $primary = $r['tiv_name'] . ' ' . ($r['description'] ?? '') . ' ' . ($r['usage_context'] ?? '');
        $this->upsertIndex('tiv_names', $id, 'name', $primary, $r['english_meaning'], $r['english_meaning'], '/name/' . $id, ['gender' => $r['gender'] ?? '']);
    }

    private function indexProverbs(): int
    {
        $rows = $this->db->query("SELECT * FROM tiv_proverbs")->fetchAll();
        foreach ($rows as $r) {
            $primary = $r['tiv_text'] . ' ' . ($r['usage_context'] ?? '');
            $secondary = $r['english_translation'] . ' ' . ($r['deeper_meaning'] ?? '');
            $this->upsertIndex(
                table: 'tiv_proverbs', id: $r['id'], type: 'proverb',
                primary: $primary, secondary: $secondary,
                excerpt: $r['english_translation'],
                url: '/proverb/' . $r['id'],
                meta: ['category' => $r['category'] ?? '']
            );
        }
        return count($rows);
    }

    private function indexProverbById(int $id): void
    {
        $stmt = $this->db->prepare("SELECT * FROM tiv_proverbs WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { $this->removeRecord('tiv_proverbs', $id); return; }
        $this->upsertIndex('tiv_proverbs', $id, 'proverb',
            $r['tiv_text'] . ' ' . ($r['usage_context'] ?? ''),
            $r['english_translation'] . ' ' . ($r['deeper_meaning'] ?? ''),
            $r['english_translation'], '/proverb/' . $id, ['category' => $r['category'] ?? '']);
    }

    private function indexPlants(): int
    {
        $rows = $this->db->query("SELECT * FROM tiv_plants")->fetchAll();
        foreach ($rows as $r) {
            $primary = $r['tiv_name'] . ' ' . ($r['description'] ?? '') . ' ' . ($r['medicinal_uses'] ?? '') . ' ' . ($r['ritual_uses'] ?? '') . ' ' . ($r['food_uses'] ?? '');
            $this->upsertIndex(
                table: 'tiv_plants', id: $r['id'], type: 'plant',
                primary: $primary,
                secondary: $r['english_name'] . ' ' . ($r['scientific_name'] ?? ''),
                excerpt: mb_substr($r['description'] ?? '', 0, 200),
                url: '/plant/' . $r['id'],
                meta: ['scientific_name' => $r['scientific_name'] ?? '', 'is_medicinal' => $r['is_medicinal'] ?? 0]
            );
        }
        return count($rows);
    }

    private function indexPlantById(int $id): void
    {
        $stmt = $this->db->prepare("SELECT * FROM tiv_plants WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { $this->removeRecord('tiv_plants', $id); return; }
        $primary = $r['tiv_name'] . ' ' . ($r['description'] ?? '') . ' ' . ($r['medicinal_uses'] ?? '');
        $this->upsertIndex('tiv_plants', $id, 'plant', $primary, $r['english_name'], mb_substr($r['description'] ?? '', 0, 200), '/plant/' . $id, ['scientific_name' => $r['scientific_name'] ?? '']);
    }

    private function indexFoods(): int
    {
        $rows = $this->db->query("SELECT * FROM tiv_foods")->fetchAll();
        foreach ($rows as $r) {
            $primary = $r['tiv_name'] . ' ' . ($r['description'] ?? '') . ' ' . ($r['cultural_significance'] ?? '') . ' ' . ($r['preparation_method'] ?? '');
            $this->upsertIndex(
                table: 'tiv_foods', id: $r['id'], type: 'food',
                primary: $primary,
                secondary: $r['english_name'] . ' ' . ($r['ingredients'] ?? ''),
                excerpt: mb_substr($r['description'] ?? '', 0, 200),
                url: '/food/' . $r['id'],
                meta: ['category' => $r['category'] ?? '']
            );
        }
        return count($rows);
    }

    private function indexFoodById(int $id): void
    {
        $stmt = $this->db->prepare("SELECT * FROM tiv_foods WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { $this->removeRecord('tiv_foods', $id); return; }
        $primary = $r['tiv_name'] . ' ' . ($r['description'] ?? '') . ' ' . ($r['cultural_significance'] ?? '');
        $this->upsertIndex('tiv_foods', $id, 'food', $primary, $r['english_name'], mb_substr($r['description'] ?? '', 0, 200), '/food/' . $id, ['category' => $r['category'] ?? '']);
    }

    private function indexFestivals(): int
    {
        $rows = $this->db->query("SELECT * FROM tiv_festivals")->fetchAll();
        foreach ($rows as $r) {
            $primary = $r['tiv_name'] . ' ' . ($r['description'] ?? '') . ' ' . ($r['significance'] ?? '') . ' ' . ($r['activities'] ?? '') . ' ' . ($r['timing'] ?? '');
            $this->upsertIndex(
                table: 'tiv_festivals', id: $r['id'], type: 'festival',
                primary: $primary,
                secondary: $r['english_name'],
                excerpt: mb_substr($r['description'] ?? '', 0, 200),
                url: '/festival/' . $r['id'],
                meta: ['timing' => $r['timing'] ?? '', 'festival_type' => $r['festival_type'] ?? '']
            );
        }
        return count($rows);
    }

    private function indexFestivalById(int $id): void
    {
        $stmt = $this->db->prepare("SELECT * FROM tiv_festivals WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { $this->removeRecord('tiv_festivals', $id); return; }
        $primary = $r['tiv_name'] . ' ' . ($r['description'] ?? '') . ' ' . ($r['significance'] ?? '');
        $this->upsertIndex('tiv_festivals', $id, 'festival', $primary, $r['english_name'], mb_substr($r['description'] ?? '', 0, 200), '/festival/' . $id, ['timing' => $r['timing'] ?? '']);
    }

    private function indexAnimals(): int
    {
        $rows = $this->db->query("SELECT * FROM tiv_animals")->fetchAll();
        foreach ($rows as $r) {
            $primary = $r['tiv_name'] . ' ' . ($r['description'] ?? '') . ' ' . ($r['cultural_use'] ?? '');
            $this->upsertIndex(
                table: 'tiv_animals', id: $r['id'], type: 'animal',
                primary: $primary,
                secondary: $r['name'] ?? '',
                excerpt: mb_substr($r['description'] ?? '', 0, 200),
                url: '/animal/' . $r['id'],
                meta: ['animal_type' => $r['animal_type'] ?? '']
            );
        }
        return count($rows);
    }

    private function indexAnimalById(int $id): void
    {
        $stmt = $this->db->prepare("SELECT * FROM tiv_animals WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { $this->removeRecord('tiv_animals', $id); return; }
        $primary = $r['tiv_name'] . ' ' . ($r['description'] ?? '') . ' ' . ($r['cultural_use'] ?? '');
        $this->upsertIndex('tiv_animals', $id, 'animal', $primary, $r['name'] ?? '', mb_substr($r['description'] ?? '', 0, 200), '/animal/' . $id, ['animal_type' => $r['animal_type'] ?? '']);
    }

    private function indexBibleVerses(): int
    {
        $stmt = $this->db->query("SELECT id, book, chapter, verse, english_web, tiv FROM bible_verses WHERE tiv IS NOT NULL AND tiv != '' LIMIT 5000");
        $rows = $stmt->fetchAll();
        foreach ($rows as $r) {
            $ref = $r['book'] . ' ' . $r['chapter'] . ':' . $r['verse'];
            $this->upsertIndex(
                table: 'bible_verses', id: $r['id'], type: 'bible',
                primary: ($r['tiv'] ?? '') . ' ' . $ref,
                secondary: $r['english_web'],
                excerpt: mb_substr($r['english_web'], 0, 200),
                url: '/bible/' . ($r['book'] ?? '') . '/' . $r['chapter'] . '#v' . $r['verse'],
                meta: ['ref' => $ref, 'book' => $r['book'], 'chapter' => $r['chapter'], 'verse' => $r['verse']]
            );
        }
        return count($rows);
    }

    private function indexContentItems(): int
    {
        $rows = $this->db->query(
            "SELECT * FROM content_items WHERE status = 'published'"
        )->fetchAll();
        foreach ($rows as $r) {
            $this->indexContentItemRow($r);
        }
        return count($rows);
    }

    private function indexContentItemById(int $id): void
    {
        $stmt = $this->db->prepare("SELECT * FROM content_items WHERE id = ? AND status = 'published'");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { $this->removeRecord('content_items', $id); return; }
        $this->indexContentItemRow($r);
    }

    private function indexContentItemRow(array $r): void
    {
        // Include extracted_text (from PDF/OCR/Word) so file contents are fully searchable
        $primary = ($r['title'] ?? '') . ' ' . ($r['tiv_title'] ?? '') . ' '
                 . ($r['content'] ?? '') . ' ' . ($r['tiv_content'] ?? '') . ' '
                 . ($r['extracted_text'] ?? '');
        $secondary = ($r['excerpt'] ?? '') . ' ' . ($r['tiv_excerpt'] ?? '');
        $type = match($r['subcategory'] ?? '') {
            'documents'    => 'document',
            'audio'        => 'audio',
            'publications' => 'publication',
            'folktales'    => 'folktale',
            'stories'      => 'story',
            'poems'        => 'poem',
            default        => 'content_item',
        };
        $this->upsertIndex(
            table: 'content_items', id: $r['id'], type: $type,
            section: $r['section'] ?? null,
            primary: $primary, secondary: $secondary,
            excerpt: mb_substr($r['excerpt'] ?? '', 0, 200),
            url: '/content-item/' . $r['id'],
            meta: ['subcategory' => $r['subcategory'] ?? '', 'media_type' => $r['media_type'] ?? '']
        );
    }

    private function indexPhrases(): int
    {
        $rows = $this->db->query(
            "SELECT * FROM translation_phrases WHERE status = 'active'"
        )->fetchAll();
        foreach ($rows as $r) {
            $this->indexPhraseById((int) $r['id']);
        }
        return count($rows);
    }

    private function indexPhraseById(int $id): void
    {
        $stmt = $this->db->prepare("SELECT * FROM translation_phrases WHERE id = ? AND status = 'active'");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) { $this->removeRecord('translation_phrases', $id); return; }

        $srcLang = $r['source_language'];
        $primary   = $srcLang === 'tiv' ? $r['source_text'] : $r['target_text'];
        $secondary = $srcLang === 'tiv' ? $r['target_text'] : $r['source_text'];

        $qParam = mb_strlen($primary) <= 120 ? urlencode($primary) : '';
        $this->upsertIndex(
            table: 'translation_phrases', id: $id, type: 'phrase',
            primary: $primary, secondary: $secondary,
            excerpt: $secondary,
            url: $qParam ? '/translate?q=' . $qParam : '/translate',
            meta: ['context_tag' => $r['context_tag'] ?? '', 'source_lang' => $srcLang]
        );
    }

    // ─────────────────────────────────────────────────────────────
    // Learning videos indexer
    // ─────────────────────────────────────────────────────────────

    private function indexLearningVideos(): int
    {
        try {
            $rows = $this->db->query(
                "SELECT * FROM learning_videos WHERE is_active = 1"
            )->fetchAll();
        } catch (\PDOException $e) {
            return 0;
        }

        foreach ($rows as $r) {
            $primary = ($r['title'] ?? '') . ' ' . ($r['description'] ?? '') . ' ' . ($r['category'] ?? '');
            $this->upsertIndex(
                table: 'learning_videos', id: $r['id'], type: 'video',
                primary: $primary, secondary: $r['category'] ?? '',
                excerpt: mb_substr($r['description'] ?? '', 0, 200),
                url: '/learn/' . $r['id'],
                meta: ['category' => $r['category'] ?? '', 'difficulty' => $r['difficulty'] ?? '', 'youtube_id' => $r['youtube_id'] ?? '']
            );
        }
        return count($rows);
    }

    private function indexVideoById(int $id): void
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM learning_videos WHERE id = ? AND is_active = 1");
            $stmt->execute([$id]);
            $r = $stmt->fetch();
        } catch (\PDOException $e) { return; }

        if (!$r) { $this->removeRecord('learning_videos', $id); return; }
        $primary = ($r['title'] ?? '') . ' ' . ($r['description'] ?? '') . ' ' . ($r['category'] ?? '');
        $this->upsertIndex('learning_videos', $id, 'video', $primary, $r['category'] ?? '', mb_substr($r['description'] ?? '', 0, 200), '/learn/' . $id, ['category' => $r['category'] ?? '']);
    }

    // ─────────────────────────────────────────────────────────────
    // Alphabet / grammar indexer
    // ─────────────────────────────────────────────────────────────

    private function indexAlphabetEntries(): int
    {
        try {
            $rows = $this->db->query(
                "SELECT * FROM tiv_alphabet ORDER BY sort_order"
            )->fetchAll();
        } catch (\PDOException $e) {
            return 0;
        }

        foreach ($rows as $r) {
            $primary   = ($r['letter'] ?? '') . ' ' . ($r['sound_desc'] ?? '') . ' ' . ($r['tiv_example'] ?? '');
            $secondary = ($r['english_letter'] ?? '') . ' ' . ($r['ipa'] ?? '') . ' ' . ($r['english_meaning'] ?? '');
            $this->upsertIndex(
                table: 'tiv_alphabet', id: $r['id'], type: 'grammar',
                primary: $primary, secondary: $secondary,
                excerpt: $r['sound_desc'] ?? '',
                url: '/alphabet#letter-' . $r['id'],
                meta: ['type' => $r['type'] ?? '', 'ipa' => $r['ipa'] ?? '', 'letter' => $r['letter'] ?? '']
            );
        }
        return count($rows);
    }

    private function indexAlphabetById(int $id): void
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM tiv_alphabet WHERE id = ?");
            $stmt->execute([$id]);
            $r = $stmt->fetch();
        } catch (\PDOException $e) { return; }

        if (!$r) { $this->removeRecord('tiv_alphabet', $id); return; }
        $primary   = ($r['letter'] ?? '') . ' ' . ($r['sound_desc'] ?? '') . ' ' . ($r['tiv_example'] ?? '');
        $secondary = ($r['english_letter'] ?? '') . ' ' . ($r['ipa'] ?? '');
        $this->upsertIndex('tiv_alphabet', $id, 'grammar', $primary, $secondary, $r['sound_desc'] ?? '', '/alphabet#letter-' . $id, ['type' => $r['type'] ?? '']);
    }

    // ─────────────────────────────────────────────────────────────
    // Historical figures indexer
    // ─────────────────────────────────────────────────────────────

    private function indexHistoricalFigures(): int
    {
        try {
            $rows = $this->db->query("SELECT * FROM historical_figures WHERE status = 'published'")->fetchAll();
        } catch (\PDOException $e) {
            return 0;
        }
        foreach ($rows as $r) {
            $this->indexHistoricalFigureRow($r);
        }
        return count($rows);
    }

    private function indexHistoricalFigureById(int $id): void
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM historical_figures WHERE id = ? AND status = 'published'");
            $stmt->execute([$id]);
            $r = $stmt->fetch();
        } catch (\PDOException $e) { return; }

        if (!$r) { $this->removeRecord('historical_figures', $id); return; }
        $this->indexHistoricalFigureRow($r);
    }

    private function indexHistoricalFigureRow(array $r): void
    {
        $primary = ($r['english_name'] ?? '') . ' ' . ($r['tiv_name'] ?? '') . ' '
                 . ($r['biography'] ?? '') . ' ' . ($r['achievements'] ?? '') . ' ' . ($r['legacy'] ?? '');
        $secondary = ($r['title'] ?? '') . ' ' . ($r['category'] ?? '') . ' ' . ($r['occupation'] ?? '');
        $this->upsertIndex(
            table: 'historical_figures', id: $r['id'], type: 'historical_figure',
            primary: $primary, secondary: $secondary,
            excerpt: mb_substr($r['short_summary'] ?? '', 0, 200),
            url: '/historical-figure/' . $r['id'],
            meta: [
                'category'    => $r['category'] ?? '',
                'subcategory' => $r['subcategory'] ?? '',
                'historical_period' => $r['historical_period'] ?? '',
            ]
        );
    }

    // ─────────────────────────────────────────────────────────────
    // Knowledge graph auto-linking
    // ─────────────────────────────────────────────────────────────

    public function buildAutoLinks(): void
    {
        // Core cross-type links
        $this->linkProverbsToWords();
        $this->linkFestivalsToFoods();
        $this->linkPlantsToFoods();
        // Extended links
        $this->linkProverbsToStories();
        $this->linkWordsToAlphabet();
        $this->linkVideosToContentItems();
        $this->linkBibleToWords();
        $this->linkNamesToHistory();
    }

    private function linkProverbsToWords(): void
    {
        $proverbs = $this->db->query("SELECT id, tiv_text FROM tiv_proverbs")->fetchAll();
        $words    = $this->db->query("SELECT id, tiv_word FROM daily_words WHERE is_active = 1")->fetchAll();

        $wordMap = [];
        foreach ($words as $w) {
            $wordMap[mb_strtolower($w['tiv_word'])] = $w['id'];
        }

        $insert = $this->db->prepare(
            "INSERT IGNORE INTO archive_knowledge_links
             (from_table, from_id, to_table, to_id, relationship, weight, auto_generated)
             VALUES (?, ?, ?, ?, 'contains_word', 1, 1)"
        );

        foreach ($proverbs as $p) {
            $tokens = preg_split('/\s+/', mb_strtolower($p['tiv_text']));
            foreach ($tokens as $token) {
                $clean = preg_replace('/[^\p{L}\p{N}]/u', '', $token);
                if (isset($wordMap[$clean])) {
                    $insert->execute(['tiv_proverbs', $p['id'], 'daily_words', $wordMap[$clean]]);
                }
            }
        }
    }

    private function linkFestivalsToFoods(): void
    {
        $festivals = $this->db->query("SELECT id, tiv_name, english_name, description FROM tiv_festivals")->fetchAll();
        $foods     = $this->db->query("SELECT id, tiv_name, english_name FROM tiv_foods")->fetchAll();

        $insert = $this->db->prepare(
            "INSERT IGNORE INTO archive_knowledge_links
             (from_table, from_id, to_table, to_id, relationship, weight, auto_generated)
             VALUES (?, ?, ?, ?, 'associated_food', 1, 1)"
        );

        foreach ($festivals as $f) {
            $text = mb_strtolower(($f['description'] ?? '') . ' ' . $f['tiv_name'] . ' ' . $f['english_name']);
            foreach ($foods as $food) {
                $name = mb_strtolower($food['tiv_name']);
                if (mb_strlen($name) > 2 && mb_strpos($text, $name) !== false) {
                    $insert->execute(['tiv_festivals', $f['id'], 'tiv_foods', $food['id']]);
                }
            }
        }
    }

    /** Proverbs ↔ Stories/Folktales: link a proverb to content_items that quote it */
    private function linkProverbsToStories(): void
    {
        try {
            $proverbs = $this->db->query("SELECT id, tiv_text FROM tiv_proverbs LIMIT 500")->fetchAll();
            $stories  = $this->db->query(
                "SELECT id, content FROM content_items WHERE subcategory IN ('folktales','stories') AND status='published' AND content IS NOT NULL"
            )->fetchAll();

            $insert = $this->db->prepare(
                "INSERT IGNORE INTO archive_knowledge_links (from_table,from_id,to_table,to_id,relationship,weight,auto_generated)
                 VALUES (?,?,?,?,'cited_in',2,1)"
            );

            foreach ($proverbs as $p) {
                $snippet = mb_strtolower(mb_substr($p['tiv_text'], 0, 30));
                if (mb_strlen($snippet) < 5) continue;
                foreach ($stories as $s) {
                    if (mb_strpos(mb_strtolower($s['content'] ?? ''), $snippet) !== false) {
                        $insert->execute(['tiv_proverbs', $p['id'], 'content_items', $s['id']]);
                    }
                }
            }
        } catch (\PDOException $e) {}
    }

    /** Words ↔ Alphabet: link each dictionary word to its first-letter alphabet entry */
    private function linkWordsToAlphabet(): void
    {
        try {
            $alphaEntries = $this->db->query(
                "SELECT id, LOWER(letter) AS letter FROM tiv_alphabet WHERE type IN ('vowel','consonant','digraph')"
            )->fetchAll();

            if (empty($alphaEntries)) return;

            $letterMap = [];
            foreach ($alphaEntries as $a) {
                $letterMap[$a['letter']] = $a['id'];
            }

            $words = $this->db->query("SELECT id, tiv_word FROM daily_words WHERE is_active=1 LIMIT 5000")->fetchAll();

            $insert = $this->db->prepare(
                "INSERT IGNORE INTO archive_knowledge_links (from_table,from_id,to_table,to_id,relationship,weight,auto_generated)
                 VALUES (?,?,?,?,'starts_with',1,1)"
            );

            foreach ($words as $w) {
                $first = mb_strtolower(mb_substr($w['tiv_word'], 0, 1));
                if (isset($letterMap[$first])) {
                    $insert->execute(['daily_words', $w['id'], 'tiv_alphabet', $letterMap[$first]]);
                }
                // Also check digraphs (first 2 chars)
                $first2 = mb_strtolower(mb_substr($w['tiv_word'], 0, 2));
                if (isset($letterMap[$first2])) {
                    $insert->execute(['daily_words', $w['id'], 'tiv_alphabet', $letterMap[$first2]]);
                }
            }
        } catch (\PDOException $e) {}
    }

    /** Videos ↔ Content items: link learning videos to articles on the same topic */
    private function linkVideosToContentItems(): void
    {
        try {
            $videos  = $this->db->query("SELECT id, title, category FROM learning_videos WHERE is_active=1")->fetchAll();
            $items   = $this->db->query("SELECT id, title, subcategory FROM content_items WHERE status='published'")->fetchAll();

            $insert = $this->db->prepare(
                "INSERT IGNORE INTO archive_knowledge_links (from_table,from_id,to_table,to_id,relationship,weight,auto_generated)
                 VALUES (?,?,?,?,'related_article',1,1)"
            );

            foreach ($videos as $v) {
                $videoWords = preg_split('/\s+/', mb_strtolower($v['title'] . ' ' . $v['category']));
                foreach ($items as $item) {
                    $itemWords = preg_split('/\s+/', mb_strtolower($item['title'] . ' ' . $item['subcategory']));
                    $shared = count(array_intersect($videoWords, $itemWords));
                    if ($shared >= 2) {
                        $insert->execute(['learning_videos', $v['id'], 'content_items', $item['id']]);
                    }
                }
            }
        } catch (\PDOException $e) {}
    }

    /** Bible ↔ Words: link Bible verses to dictionary words they contain */
    private function linkBibleToWords(): void
    {
        try {
            // Only process verses that have Tiv text
            $verses = $this->db->query(
                "SELECT id, tiv FROM bible_verses WHERE tiv IS NOT NULL AND tiv != '' AND CHAR_LENGTH(tiv) < 100 LIMIT 1000"
            )->fetchAll();
            $words = $this->db->query("SELECT id, LOWER(tiv_word) AS tiv_word FROM daily_words WHERE is_active=1")->fetchAll();

            $wordMap = [];
            foreach ($words as $w) {
                if (mb_strlen($w['tiv_word']) >= 3) {
                    $wordMap[$w['tiv_word']] = $w['id'];
                }
            }

            $insert = $this->db->prepare(
                "INSERT IGNORE INTO archive_knowledge_links (from_table,from_id,to_table,to_id,relationship,weight,auto_generated)
                 VALUES (?,?,?,?,'uses_word',1,1)"
            );

            foreach ($verses as $v) {
                $tokens = preg_split('/\s+/u', mb_strtolower($v['tiv']));
                foreach ($tokens as $t) {
                    $clean = preg_replace('/[^\p{L}]/u', '', $t);
                    if (isset($wordMap[$clean])) {
                        $insert->execute(['bible_verses', $v['id'], 'daily_words', $wordMap[$clean]]);
                    }
                }
            }
        } catch (\PDOException $e) {}
    }

    /** Names ↔ Historical content in content_items and historical_figures */
    private function linkNamesToHistory(): void
    {
        try {
            $names = $this->db->query("SELECT id, tiv_name FROM tiv_names")->fetchAll();
            $history = $this->db->query(
                "SELECT id, title, content FROM content_items WHERE subcategory IN ('origins','migration','timeline') AND status='published'"
            )->fetchAll();
            $figures = $this->db->query(
                "SELECT id, english_name AS title, biography AS content FROM historical_figures WHERE status='published'"
            )->fetchAll();

            $insert = $this->db->prepare(
                "INSERT IGNORE INTO archive_knowledge_links (from_table,from_id,to_table,to_id,relationship,weight,auto_generated)
                 VALUES (?,?,?,?,'mentioned_in',2,1)"
            );

            foreach ($names as $n) {
                $nameLower = mb_strtolower($n['tiv_name']);
                if (mb_strlen($nameLower) < 3) continue;
                foreach ($history as $h) {
                    $text = mb_strtolower(($h['title'] ?? '') . ' ' . ($h['content'] ?? ''));
                    if (mb_strpos($text, $nameLower) !== false) {
                        $insert->execute(['tiv_names', $n['id'], 'content_items', $h['id']]);
                    }
                }
                foreach ($figures as $f) {
                    $text = mb_strtolower(($f['title'] ?? '') . ' ' . ($f['content'] ?? ''));
                    if (mb_strpos($text, $nameLower) !== false) {
                        $insert->execute(['tiv_names', $n['id'], 'historical_figures', $f['id']]);
                    }
                }
            }
        } catch (\PDOException $e) {}
    }

    private function linkPlantsToFoods(): void
    {
        $plants = $this->db->query("SELECT id, tiv_name, food_uses FROM tiv_plants WHERE food_uses IS NOT NULL AND food_uses != ''")->fetchAll();
        $foods  = $this->db->query("SELECT id, tiv_name, english_name FROM tiv_foods")->fetchAll();

        $insert = $this->db->prepare(
            "INSERT IGNORE INTO archive_knowledge_links
             (from_table, from_id, to_table, to_id, relationship, weight, auto_generated)
             VALUES (?, ?, ?, ?, 'ingredient_of', 2, 1)"
        );

        foreach ($plants as $p) {
            $foodUses = mb_strtolower($p['food_uses']);
            foreach ($foods as $food) {
                $name = mb_strtolower($food['tiv_name']);
                if (mb_strlen($name) > 2 && mb_strpos($foodUses, $name) !== false) {
                    $insert->execute(['tiv_plants', $p['id'], 'tiv_foods', $food['id']]);
                }
            }
        }
    }

    // ─────────────────────────────────────────────────────────────
    // N-gram learning from approved translation logs
    // ─────────────────────────────────────────────────────────────

    public function buildNgramStats(): void
    {
        // Learn bigrams from high-confidence translation logs
        $logs = $this->db->query(
            "SELECT source_text, translated_text, source_language, target_language
             FROM translation_logs
             WHERE confidence_score >= 80
               AND match_type NOT IN ('none', 'ai')
               AND source_text NOT LIKE '% %'
               AND CHAR_LENGTH(source_text) <= 200
               AND CHAR_LENGTH(translated_text) <= 200
             LIMIT 2000"
        )->fetchAll();

        $upsert = $this->db->prepare(
            "INSERT INTO translation_ngrams (source_ngram, target_ngram, source_lang, target_lang, frequency, confidence)
             VALUES (?, ?, ?, ?, 1, ?)
             ON DUPLICATE KEY UPDATE frequency = frequency + 1, confidence = GREATEST(confidence, VALUES(confidence))"
        );

        foreach ($logs as $log) {
            $src  = mb_strtolower(trim($log['source_text']));
            $tgt  = mb_strtolower(trim($log['translated_text']));
            if ($src === '' || $tgt === '' || $src === $tgt) continue;
            $upsert->execute([$src, $tgt, $log['source_language'], $log['target_language'], 80]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Core upsert
    // ─────────────────────────────────────────────────────────────

    private function upsertIndex(
        string $table,
        int    $id,
        string $type,
        string $primary,
        ?string $secondary = null,
        ?string $excerpt = null,
        ?string $url = null,
        array  $meta = [],
        ?string $section = null
    ): void {
        $primary   = $this->sanitizeText($primary);
        $secondary = $secondary !== null ? $this->sanitizeText($secondary) : null;
        $excerpt   = $excerpt   !== null ? $this->sanitizeText($excerpt)   : null;

        $stmt = $this->db->prepare(
            "INSERT INTO archive_search_index
                 (source_table, source_id, content_type, section, primary_text, secondary_text, excerpt, url, meta_json, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                 content_type = VALUES(content_type),
                 section      = VALUES(section),
                 primary_text = VALUES(primary_text),
                 secondary_text = VALUES(secondary_text),
                 excerpt      = VALUES(excerpt),
                 url          = VALUES(url),
                 meta_json    = VALUES(meta_json),
                 updated_at   = NOW()"
        );
        $stmt->execute([
            $table, $id, $type, $section,
            $primary, $secondary, $excerpt, $url,
            !empty($meta) ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    private function sanitizeText(string $text): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        return mb_substr(trim($text), 0, 4000);
    }
}
