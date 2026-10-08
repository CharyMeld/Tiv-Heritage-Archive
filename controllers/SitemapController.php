<?php

require_once BASE_PATH . '/services/SeoHelper.php';
require_once BASE_PATH . '/services/EntryQuality.php';
require_once BASE_PATH . '/controllers/CollectionController.php';
require_once BASE_PATH . '/models/DailyWord.php';
require_once BASE_PATH . '/models/ContentItem.php';
require_once BASE_PATH . '/models/HistoricalFigure.php';
require_once BASE_PATH . '/models/TimelineEvent.php';

class SitemapController extends Controller
{
    public function index(): void
    {
        $base = rtrim(SITE_URL, '/');
        $db   = $this->db();

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        /* ── Static pages ── */
        $static = [
            ['',                  '1.0', 'daily'],
            ['archive',           '0.9', 'weekly'],
            // Dictionary, names, proverbs, plants, festivals, foods and animals are listed
            // through their full-text pages (collections below); archive/{section} redirects there.
            ['archive/documents',    '0.6', 'monthly'],
            ['archive/audio',        '0.6', 'monthly'],
            ['archive/publications', '0.6', 'monthly'],
            ['learn',             '0.9', 'weekly'],
            ['translate',         '0.8', 'monthly'],
            ['community',         '0.7', 'monthly'],
            ['about',             '0.5', 'monthly'],
            ['contact',           '0.4', 'monthly'],
            ['privacy-policy',    '0.3', 'yearly'],
            ['terms-of-service',  '0.3', 'yearly'],
            ['historical-figures', '0.8', 'weekly'],
            ['timeline',           '0.8', 'weekly'],
            ['references',         '0.6', 'monthly'],
            ['language',           '0.7', 'monthly'],
            ['literature',         '0.7', 'monthly'],
            ['culture',            '0.7', 'monthly'],
            ['history',            '0.7', 'monthly'],
            ['language/alphabet',  '0.6', 'monthly'],
            ['language/grammar',   '0.6', 'monthly'],
            ['literature/folktales',      '0.5', 'monthly'],
            ['literature/stories',        '0.5', 'monthly'],
            ['literature/poems',          '0.5', 'monthly'],
            ['culture/traditions',        '0.5', 'monthly'],
            ['culture/attire',            '0.5', 'monthly'],
            ['culture/marriage-customs',  '0.5', 'monthly'],
            ['history/origins',           '0.5', 'monthly'],
            ['history/migration',         '0.5', 'monthly'],
        ];

        // Sections backed by content_items: listed only once they have
        // published items (the pages are noindex while empty).
        $contentBacked = [
            'archive/documents', 'archive/audio', 'archive/publications',
            'literature/folktales', 'literature/stories', 'literature/poems',
            'culture/traditions', 'culture/attire', 'culture/marriage-customs',
            'history/origins', 'history/migration',
        ];
        $contentItems = new ContentItem();

        foreach ($static as [$path, $priority, $freq]) {
            if (in_array($path, $contentBacked, true)) {
                [$section, $sub] = explode('/', $path, 2);
                if ($contentItems->countBySubcategory($section, $sub) === 0) continue;
            }
            $url = $base . ($path ? '/' . $path : '');
            echo $this->url($url, $priority, $freq);
        }

        // Forms (join, contribute, suggestions, nominate) are left out: no content of
        // their own, and they are noindex.

        /* ── Full-text collections: every page passing CollectionController's word rule ── */
        foreach (CollectionController::sitemapPaths() as $path) {
            echo $this->url("$base/$path", $path === 'collections' ? '0.9' : '0.8', 'weekly');
        }

        /* ── Learning videos ── */
        $rows = $db->query("SELECT id FROM learning_videos WHERE is_active = 1")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/learn/{$r['id']}", '0.9', 'weekly');
        }

        /* ── Single records: only those whose page is indexable (services/EntryQuality.php).
           Thinner records are noindex; their text is listed through the collections above. ── */
        $entryTables = [
            'word'     => ['daily_words',   'tiv_word', 'WHERE is_active = 1'],
            'name'     => ['tiv_names',     'tiv_name', ''],
            'proverb'  => ['tiv_proverbs',  'tiv_text', ''],
            'plant'    => ['tiv_plants',    'tiv_name', ''],
            'festival' => ['tiv_festivals', 'tiv_name', ''],
            'food'     => ['tiv_foods',     'tiv_name', ''],
            'animal'   => ['tiv_animals',   "COALESCE(NULLIF(tiv_name, ''), name)", ''],
        ];
        foreach ($entryTables as $prefix => [$table, $label, $where]) {
            $rows = $db->query("SELECT id, {$label} AS label, " . EntryQuality::columns($table) . " FROM {$table} {$where} ORDER BY id")->fetchAll();
            foreach ($rows as $r) {
                if (!EntryQuality::isIndexable($table, $r)) continue;
                echo $this->url("$base/" . SeoHelper::canonicalSlugPath($prefix, (int) $r['id'], (string) $r['label']), '0.8', 'monthly');
            }
        }

        /* ── Historical figures (Tiv collection; national ones get /nigeria/ URLs) ── */
        $hfScope = (new HistoricalFigure())->publicScopeSql();
        $rows = $db->query("SELECT DISTINCT category FROM historical_figures WHERE status = 'published' AND {$hfScope}")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/historical-figures/" . SeoHelper::slugify($r['category']), '0.7', 'monthly');
        }
        $rows = $db->query("SELECT id, english_name, " . EntryQuality::columns('historical_figures') . " FROM historical_figures WHERE status = 'published' AND {$hfScope} ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            if (!EntryQuality::isIndexable('historical_figures', $r)) continue;
            echo $this->url("$base/" . SeoHelper::canonicalSlugPath('historical-figure', (int) $r['id'], $r['english_name']), '0.8', 'monthly');
        }

        /* ── Timeline events ── */
        $teScope = (new TimelineEvent())->publicScopeSql();
        $rows = $db->query("SELECT id, title, " . EntryQuality::columns('timeline_events') . " FROM timeline_events WHERE status = 'published' AND {$teScope} ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            if (!EntryQuality::isIndexable('timeline_events', $r)) continue;
            echo $this->url("$base/" . SeoHelper::canonicalSlugPath('timeline-event', (int) $r['id'], $r['title']), '0.7', 'monthly');
        }

        /* ── References ── */
        require_once BASE_PATH . '/services/HeritagePublic.php';
        $rows = array_filter($db->query("SELECT s.id FROM sources s WHERE " . HeritagePublic::sourceIsPublicSql('s') . " ORDER BY s.id")->fetchAll(),
                             fn($r) => HeritagePublic::sourceIsIndexable((int) $r['id']));
        foreach ($rows as $r) {
            echo $this->url("$base/references/{$r['id']}", '0.5', 'monthly');
        }

        /* ── Content items (Language/Literature/Culture/History) ── */
        $rows = $db->query("SELECT id FROM content_items WHERE status = 'published' AND {$contentItems->publicScopeSql()} ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/content-item/{$r['id']}", '0.6', 'monthly');
        }

        /* ── Nigeria Heritage: only pages that pass the indexability gate ── */
        require_once BASE_PATH . '/services/HeritagePublic.php';
        foreach (HeritagePublic::sitemapEntries() as [$loc, $priority]) {
            echo $this->url($loc, $priority, 'monthly');
        }

        /* Bible chapters are not listed: the text isn't original to this site
           and the pages are noindex (see BibleReaderController). */

        echo '</urlset>';
        exit;
    }

    private function url(string $loc, string $priority, string $freq): string
    {
        $today = date('Y-m-d');
        return "  <url>\n"
             . "    <loc>" . htmlspecialchars($loc) . "</loc>\n"
             . "    <lastmod>{$today}</lastmod>\n"
             . "    <changefreq>{$freq}</changefreq>\n"
             . "    <priority>{$priority}</priority>\n"
             . "  </url>\n";
    }

    private function db(): \PDO
    {
        return \Database::getInstance();
    }
}
