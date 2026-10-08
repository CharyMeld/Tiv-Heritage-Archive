<?php
require_once BASE_PATH . '/services/OutreachMailer.php';
require_once BASE_PATH . '/services/SeoHelper.php';
require_once BASE_PATH . '/models/DailyWord.php';
require_once BASE_PATH . '/models/TivProverb.php';
require_once BASE_PATH . '/models/TivFestival.php';
require_once BASE_PATH . '/models/TivName.php';
require_once BASE_PATH . '/models/HistoricalFigure.php';
require_once BASE_PATH . '/models/TimelineEvent.php';
require_once BASE_PATH . '/models/MarketingNewsletterIssue.php';

/**
 * Builds a newsletter issue (subject + HTML + plain text) from real archive
 * data — never AI-hallucinated content. The intro paragraph is templated
 * from the same items listed below it; it used to come from Ollama, but the
 * model had no access to the items and invented words, meanings and
 * articles that don't exist in the archive (e.g. "Etshe" = clan).
 */
class NewsletterGeneratorService
{
    public static function generate(string $issueType): array
    {
        return match ($issueType) {
            'new_words'      => self::buildNewWords(),
            'featured_items' => self::buildFeaturedItems(),
            'top_five'       => self::buildTopFive(),
            'weekly_summary', 'custom' => self::buildWeeklySummary(),
            default => throw new RuntimeException("Unknown newsletter type: {$issueType}"),
        };
    }

    private static function buildWeeklySummary(): array
    {
        $words = self::selectWords(3, (new MarketingNewsletterIssue())->previousSentId('weekly_summary'));
        $featured = self::collectFeatured(4);
        $popular = self::collectPopular(3);

        $intro = 'Here is this week\'s roundup from the Tiv Heritage Archive.';
        if ($words['new']) {
            $intro .= ' New to the dictionary since our last newsletter: ' . self::wordPhrase($words['new']) . '.';
        }
        if ($words['rotation']) {
            $intro .= ($words['new'] ? ' We have also picked some words from the archive for you to learn: ' : ' This week\'s words to learn: ')
                . self::wordPhrase($words['rotation']) . '.';
        }
        if ($popular['items']) {
            $intro .= $popular['mode'] === 'gain'
                ? ' The most-read page since our last newsletter was ' . $popular['items'][0]['title'] . '.'
                : ' Our most-read page of all time is ' . $popular['items'][0]['title'] . '.';
        }
        $intro .= ' Thank you for helping us preserve Tiv language, history, and culture.';

        $sections = array_merge(self::wordSections($words), [
            ['key' => 'featured', 'title' => 'Featured Content', 'items' => $featured],
            ['key' => 'popular', 'title' => $popular['title'], 'items' => $popular['items']],
            ['key' => MarketingNewsletterIssueItem::SECTION_BASELINE, 'hidden' => true, 'items' => $popular['baseline']],
        ]);

        return self::render('This Week at the Tiv Heritage Archive', $intro, $sections);
    }

    private static function buildNewWords(): array
    {
        $words = self::selectWords(10, (new MarketingNewsletterIssue())->previousSentId('new_words'));
        if ($words['new']) {
            $subject = 'New Tiv Words to Learn';
            $intro = 'Here are the words added to the Tiv dictionary since our last newsletter'
                . ($words['rotation'] ? ', plus a few more from the archive to learn' : '')
                . '. Click any word to see its full entry, examples, and pronunciation notes.';
        } else {
            $subject = 'Tiv Words to Learn This Week';
            $intro = 'Here are some words from the Tiv dictionary to learn this week. Click any word to see its full entry, examples, and pronunciation notes.';
        }
        return self::render($subject, $intro, self::wordSections($words));
    }

    /**
     * Words for an issue: dictionary words genuinely added after the
     * previous sent issue ($sinceIssueId) was created, topped up — only if there are
     * fewer than $count — with existing words least recently used in a sent
     * issue. The two groups are kept apart so old words are never labelled new.
     *
     * @return array{new: array, rotation: array}
     */
    public static function selectWords(int $count, ?int $sinceIssueId): array
    {
        $model = new DailyWord();
        $new = $model->addedSince($sinceIssueId, $count);
        $rotation = count($new) < $count
            ? $model->leastRecentlyInNewsletter($count - count($new), array_column($new, 'id'))
            : [];
        return ['new' => $new, 'rotation' => $rotation];
    }

    private static function wordSections(array $words): array
    {
        return [
            ['key' => 'new_words', 'title' => 'New in the Dictionary', 'items' => self::formatItems($words['new'], 'daily_words', 'tiv_word', 'english_meaning', 'word')],
            ['key' => 'words_to_learn', 'title' => 'Words to Learn This Week', 'items' => self::formatItems($words['rotation'], 'daily_words', 'tiv_word', 'english_meaning', 'word')],
        ];
    }

    private static function wordPhrase(array $words): string
    {
        return self::listPhrase(array_map(
            fn($w) => SeoHelper::displayWord($w['tiv_word']) . (!empty($w['english_meaning']) ? ' ("' . $w['english_meaning'] . '")' : ''),
            $words
        ));
    }

    private static function buildFeaturedItems(): array
    {
        $featured = self::collectFeatured(10);
        $intro = 'A hand-picked selection of proverbs, festivals, and names from the Tiv Heritage Archive.';
        return self::render('Featured on the Tiv Heritage Archive', $intro, [
            ['key' => 'featured', 'title' => 'Featured Content', 'items' => $featured],
        ]);
    }

    private static function buildTopFive(): array
    {
        $popular = self::collectPopular(5);
        $intro = $popular['mode'] === 'gain'
            ? 'These are the historical figures and events on the Tiv Heritage Archive that gained the most views since our last newsletter.'
            : 'These are the most-read historical figures and events of all time on the Tiv Heritage Archive.';
        return self::render($popular['mode'] === 'gain' ? 'Top 5 Since Our Last Newsletter' : 'Top 5 Most Read of All Time', $intro, [
            ['key' => 'popular', 'title' => $popular['title'], 'items' => $popular['items']],
            ['key' => MarketingNewsletterIssueItem::SECTION_BASELINE, 'hidden' => true, 'items' => $popular['baseline']],
        ]);
    }

    /**
     * Featured (is_featured = 1) proverbs, festivals and names, dealt out
     * round-robin so every category gets a share of $limit — an empty or
     * small category just hands its remaining places to the others. Within
     * a category, records rotate least-recently-used first (see
     * Model::newsletterRotation), so e.g. the 4 featured festivals each
     * appear once before any repeats.
     *
     * @return array<int, array{title:string, url:string, meta:string}>
     */
    private static function collectFeatured(int $limit): array
    {
        $sources = [
            ['model' => new TivProverb(), 'table' => 'tiv_proverbs', 'title' => 'tiv_text', 'path' => 'proverb', 'label' => 'Proverb'],
            ['model' => new TivFestival(), 'table' => 'tiv_festivals', 'title' => 'tiv_name', 'path' => 'festival', 'label' => 'Festival'],
            ['model' => new TivName(), 'table' => 'tiv_names', 'title' => 'tiv_name', 'path' => 'name', 'label' => 'Name'],
        ];

        $candidates = [];
        foreach ($sources as $i => $src) {
            $candidates[$i] = $src['model']->newsletterRotation($limit, 't.is_featured = 1');
        }

        $picked = array_fill_keys(array_keys($sources), []);
        $total = 0;
        while ($total < $limit && array_filter($candidates)) {
            foreach ($candidates as $i => &$rows) {
                if ($total >= $limit || !$rows) continue;
                $picked[$i][] = array_shift($rows);
                $total++;
            }
            unset($rows);
        }

        $items = [];
        foreach ($sources as $i => $src) {
            foreach ($picked[$i] as $row) {
                $items[] = [
                    'title' => $row[$src['title']] ?? '(untitled)',
                    'url' => rtrim(SITE_URL, '/') . '/' . $src['path'] . '/' . $row['id'],
                    'meta' => $src['label'],
                    'item_table' => $src['table'],
                    'item_id' => (int) $row['id'],
                    'views' => isset($row['view_count']) ? (int) $row['view_count'] : null,
                ];
            }
        }

        return $items;
    }

    /**
     * Popular published historical figures and timeline events, ranked by
     * views GAINED since the previous sent newsletter:
     *
     *   gain = current view_count - the record's snapshot from the most
     *          recent sent issue that recorded one
     *
     * Every issue snapshots all candidates (the hidden popular_baseline
     * section), so each record has a snapshot from the previous issue. A
     * record with no snapshot at all wasn't published when the last baseline
     * was taken, so all its views are new: gain = view_count. Gains are
     * computed per record within its own table and only then merged, and
     * records with no gain are left out (a quiet week shows fewer items, or
     * none, rather than "+0 views").
     *
     * Before any baseline exists (the first issue after this was deployed)
     * gains can't be known, so the section falls back to lifetime totals and
     * says so ("all time"); that issue's baseline enables gains from the next.
     *
     * @return array{mode:string, title:string, items:array, baseline:array}
     */
    private static function collectPopular(int $limit): array
    {
        $snapshots = new MarketingNewsletterIssueItem();
        $sources = [
            ['model' => new HistoricalFigure(), 'table' => 'historical_figures', 'path' => 'historical-figure',
             'title' => fn($r) => $r['english_name'] ?? $r['tiv_name'] ?? '(untitled)'],
            ['model' => new TimelineEvent(), 'table' => 'timeline_events', 'path' => 'timeline-event',
             'title' => fn($r) => $r['title'] ?? '(untitled)'],
        ];

        $hasBaseline = false;
        foreach ($sources as $src) {
            $hasBaseline = $hasBaseline || $snapshots->hasViewSnapshots($src['table']);
        }
        $mode = $hasBaseline ? 'gain' : 'lifetime';

        $candidates = [];
        foreach ($sources as $src) {
            $latest = $hasBaseline ? $snapshots->latestViewSnapshots($src['table']) : [];
            foreach ($src['model']->allPublished() as $row) {
                $views = (int) $row['view_count'];
                $candidates[] = [
                    'title' => ($src['title'])($row),
                    'url' => rtrim(SITE_URL, '/') . '/' . $src['path'] . '/' . $row['id'],
                    'item_table' => $src['table'],
                    'item_id' => (int) $row['id'],
                    'views' => $views,
                    'rank' => $mode === 'gain' ? $views - ($latest[(int) $row['id']] ?? 0) : $views,
                ];
            }
        }

        usort($candidates, fn($a, $b) => [$b['rank'], $b['views']] <=> [$a['rank'], $a['views']]);

        $items = [];
        foreach ($candidates as $c) {
            if (count($items) >= $limit || $c['rank'] <= 0) break;
            $c['meta'] = $mode === 'gain'
                ? '+' . number_format($c['rank']) . ' views'
                : number_format($c['views']) . ' views all-time';
            $items[] = $c;
        }

        return [
            'mode' => $mode,
            'title' => $mode === 'gain' ? 'Most Read Since Our Last Newsletter' : 'Most Read on the Archive (All Time)',
            'items' => $items,
            'baseline' => $candidates,
        ];
    }

    private static function formatItems(array $rows, string $table, string $titleField, string $metaField, string $path): array
    {
        return array_map(fn($r) => [
            // Homograph markers ("korough2") render as superscripts, as on the site.
            'title' => $table === 'daily_words' ? SeoHelper::displayWord($r[$titleField] ?? '') : ($r[$titleField] ?? '(untitled)'),
            'url' => rtrim(SITE_URL, '/') . '/' . $path . '/' . $r['id'],
            'meta' => $r[$metaField] ?? '',
            'item_table' => $table,
            'item_id' => (int) $r['id'],
            'views' => isset($r['view_count']) ? (int) $r['view_count'] : null,
        ], $rows);
    }

    /** "a", "a and b", "a, b and c" */
    private static function listPhrase(array $parts): string
    {
        $last = array_pop($parts);
        return $parts ? implode(', ', $parts) . ' and ' . $last : (string) $last;
    }

    /**
     * @param array<int, array{key:string, title?:string, items:array, hidden?:bool}> $sections
     *   A 'hidden' section is recorded in 'items' but not rendered (it must
     *   come after the visible sections so shown records keep their section).
     * @return array{subject:string, html_body:string, text_body:string, items:array}
     *   'items' lists exactly the records rendered into the issue, for
     *   MarketingNewsletterIssue::createWithItems().
     */
    private static function render(string $subject, string $intro, array $sections): array
    {
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'Tiv Heritage Archive';
        $siteUrl = defined('SITE_URL') ? SITE_URL : '';

        $htmlSections = '';
        $textSections = '';
        $selected = [];

        foreach ($sections as $section) {
            if (empty($section['items'])) continue;

            if (!empty($section['hidden'])) {
                foreach ($section['items'] as $item) {
                    $selected[$item['item_table'] . ':' . $item['item_id']] ??= [
                        'section' => $section['key'],
                        'item_table' => $item['item_table'],
                        'item_id' => $item['item_id'],
                        'view_count_snapshot' => $item['views'] ?? null,
                    ];
                }
                continue;
            }

            $htmlSections .= '<h3 style="color:#5C3A21;font-size:16px;margin:24px 0 10px;">' . htmlspecialchars($section['title']) . '</h3><ul style="padding-left:20px;margin:0;">';
            $textSections .= "\n" . strtoupper($section['title']) . "\n" . str_repeat('-', strlen($section['title'])) . "\n";

            foreach ($section['items'] as $item) {
                // First occurrence wins if a record ever lands in two sections.
                $selected[$item['item_table'] . ':' . $item['item_id']] ??= [
                    'section' => $section['key'],
                    'item_table' => $item['item_table'],
                    'item_id' => $item['item_id'],
                    'view_count_snapshot' => $item['views'] ?? null,
                ];

                $htmlSections .= '<li style="margin-bottom:8px;"><a href="' . htmlspecialchars($item['url']) . '" style="color:#5C3A21;font-weight:600;">' . htmlspecialchars($item['title']) . '</a>'
                    . (!empty($item['meta']) ? ' <span style="color:#8a7a6a;font-size:13px;">— ' . htmlspecialchars($item['meta']) . '</span>' : '')
                    . '</li>';
                $textSections .= '- ' . $item['title'] . (!empty($item['meta']) ? ' (' . $item['meta'] . ')' : '') . ': ' . $item['url'] . "\n";
            }
            $htmlSections .= '</ul>';
        }

        // Never produce an issue with nothing in it — callers (the Friday
        // cron, the admin Generate button) treat the exception as "abort".
        if ($htmlSections === '') {
            throw new RuntimeException('Newsletter generation produced no content; nothing was created.');
        }

        $htmlBody = '<p style="margin:0 0 20px;">' . nl2br(htmlspecialchars($intro)) . '</p>' . $htmlSections
            . '<p style="margin-top:28px;"><a href="' . htmlspecialchars($siteUrl) . '" style="color:#C8A951;">Visit the Tiv Heritage Archive &rarr;</a></p>';

        $textBody = $intro . "\n" . $textSections . "\nVisit: " . $siteUrl . "\n";

        return [
            'subject' => $subject,
            'html_body' => OutreachMailer::buildBody($htmlBody, []),
            'text_body' => $textBody,
            'items' => array_values($selected),
        ];
    }
}
