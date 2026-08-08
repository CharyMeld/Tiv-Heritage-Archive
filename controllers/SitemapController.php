<?php

require_once BASE_PATH . '/services/SeoHelper.php';

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
            ['archive/words',     '0.9', 'weekly'],
            ['archive/names',     '0.9', 'weekly'],
            ['archive/proverbs',  '0.9', 'weekly'],
            ['archive/plants',    '0.9', 'weekly'],
            ['archive/festivals', '0.9', 'weekly'],
            ['archive/foods',     '0.9', 'weekly'],
            ['archive/animals',   '0.9', 'weekly'],
            ['archive/documents',    '0.6', 'monthly'],
            ['archive/audio',        '0.6', 'monthly'],
            ['archive/publications', '0.6', 'monthly'],
            ['learn',             '0.9', 'weekly'],
            ['bible',             '0.9', 'weekly'],
            ['translate',         '0.8', 'monthly'],
            ['community',         '0.7', 'monthly'],
            ['community/join',    '0.5', 'monthly'],
            ['contribute',        '0.7', 'monthly'],
            ['about',             '0.5', 'monthly'],
            ['contact',           '0.4', 'monthly'],
            ['privacy-policy',    '0.3', 'yearly'],
            ['terms-of-service',  '0.3', 'yearly'],
            ['historical-figures', '0.8', 'weekly'],
            ['timeline',           '0.8', 'weekly'],
            ['references',         '0.6', 'monthly'],
            ['suggestions',        '0.4', 'monthly'],
            ['nominate-influential', '0.4', 'monthly'],
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

        foreach ($static as [$path, $priority, $freq]) {
            $url = $base . ($path ? '/' . $path : '');
            echo $this->url($url, $priority, $freq);
        }

        /* ── Learning videos ── */
        $rows = $db->query("SELECT id FROM learning_videos WHERE is_active = 1")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/learn/{$r['id']}", '0.9', 'weekly');
        }

        /* ── Dictionary words ── */
        $rows = $db->query("SELECT id, tiv_word FROM daily_words ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/" . SeoHelper::canonicalSlugPath('word', (int) $r['id'], $r['tiv_word']), '0.8', 'monthly');
        }

        /* ── Names ── */
        $rows = $db->query("SELECT id, tiv_name FROM tiv_names ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/" . SeoHelper::canonicalSlugPath('name', (int) $r['id'], $r['tiv_name']), '0.8', 'monthly');
        }

        /* ── Proverbs ── */
        $rows = $db->query("SELECT id, tiv_text FROM tiv_proverbs ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/" . SeoHelper::canonicalSlugPath('proverb', (int) $r['id'], $r['tiv_text']), '0.8', 'monthly');
        }

        /* ── Plants ── */
        $rows = $db->query("SELECT id, tiv_name FROM tiv_plants ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/" . SeoHelper::canonicalSlugPath('plant', (int) $r['id'], $r['tiv_name']), '0.8', 'monthly');
        }

        /* ── Festivals ── */
        $rows = $db->query("SELECT id, tiv_name FROM tiv_festivals ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/" . SeoHelper::canonicalSlugPath('festival', (int) $r['id'], $r['tiv_name']), '0.8', 'monthly');
        }

        /* ── Foods ── */
        $rows = $db->query("SELECT id, tiv_name FROM tiv_foods ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/" . SeoHelper::canonicalSlugPath('food', (int) $r['id'], $r['tiv_name']), '0.8', 'monthly');
        }

        /* ── Animals ── */
        $rows = $db->query("SELECT id, tiv_name, name FROM tiv_animals ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/" . SeoHelper::canonicalSlugPath('animal', (int) $r['id'], $r['tiv_name'] ?: $r['name']), '0.8', 'monthly');
        }

        /* ── Historical figures ── */
        $rows = $db->query("SELECT DISTINCT category FROM historical_figures WHERE status = 'published'")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/historical-figures/" . SeoHelper::slugify($r['category']), '0.7', 'monthly');
        }
        $rows = $db->query("SELECT id, english_name FROM historical_figures WHERE status = 'published' ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/" . SeoHelper::canonicalSlugPath('historical-figure', (int) $r['id'], $r['english_name']), '0.8', 'monthly');
        }

        /* ── Timeline events ── */
        $rows = $db->query("SELECT id, title FROM timeline_events WHERE status = 'published' ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/" . SeoHelper::canonicalSlugPath('timeline-event', (int) $r['id'], $r['title']), '0.7', 'monthly');
        }

        /* ── References ── */
        $rows = $db->query("SELECT id FROM sources ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/references/{$r['id']}", '0.5', 'monthly');
        }

        /* ── Content items (Language/Literature/Culture/History) ── */
        $rows = $db->query("SELECT id FROM content_items WHERE status = 'published' ORDER BY id")->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/content-item/{$r['id']}", '0.6', 'monthly');
        }

        /* ── Bible chapters ── */
        $rows = $db->query(
            "SELECT DISTINCT book_key, chapter FROM bible_verses ORDER BY book_key, chapter"
        )->fetchAll();
        foreach ($rows as $r) {
            echo $this->url("$base/bible/{$r['book_key']}/{$r['chapter']}", '0.7', 'monthly');
        }

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
