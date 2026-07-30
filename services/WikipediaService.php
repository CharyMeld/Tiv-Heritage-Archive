<?php
class WikipediaService {
    private const API_BASE = 'https://en.wikipedia.org/w/api.php';
    private const TIMEOUT  = 8;
    private const UA       = 'TivHeritageArchive/1.0 (https://www.tivheritage.com; contact@tivarchive.com)';

    public function searchWithDetails(string $query, int $limit = 12): array {
        $raw = $this->fetch([
            'action'      => 'query',
            'generator'   => 'search',
            'gsrsearch'   => $query,
            'gsrlimit'    => $limit,
            'prop'        => 'extracts|info',
            'exintro'     => 1,
            'explaintext' => 1,
            'exsentences' => 3,
            'inprop'      => 'url',
            'format'      => 'json',
        ]);

        if (!$raw || empty($raw['query']['pages'])) {
            return [];
        }

        $results = [];
        foreach ($raw['query']['pages'] as $page) {
            if (isset($page['missing'])) continue;
            $extract = trim($page['extract'] ?? '');
            if (mb_strlen($extract) > 350) {
                $extract = mb_substr($extract, 0, 347) . '…';
            }
            $results[] = [
                'pageid'   => (int) $page['pageid'],
                'title'    => $page['title'],
                'extract'  => $extract,
                'url'      => $page['fullurl']
                              ?? 'https://en.wikipedia.org/wiki/' . rawurlencode(str_replace(' ', '_', $page['title'])),
                'category' => $this->guessCategory($extract, $page['title']),
            ];
        }

        usort($results, fn($a, $b) => strcmp($a['title'], $b['title']));
        return $results;
    }

    private function fetch(array $params): ?array {
        $url = self::API_BASE . '?' . http_build_query($params);
        $ctx = stream_context_create([
            'http' => [
                'timeout'    => self::TIMEOUT,
                'user_agent' => self::UA,
            ],
        ]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) return null;
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function guessCategory(string $text, string $title): string {
        $hay = strtolower($text . ' ' . $title);
        $map = [
            'senator'      => 'politician',
            'governor'     => 'politician',
            'minister'     => 'politician',
            'politician'   => 'politician',
            'house of rep' => 'politician',
            'legislat'     => 'politician',
            'professor'    => 'academic',
            'lecturer'     => 'academic',
            'university'   => 'academic',
            'scholar'      => 'academic',
            'research'     => 'researcher',
            'author'       => 'author',
            'writer'       => 'author',
            'novelist'     => 'author',
            'poet'         => 'author',
            'musician'     => 'musician',
            'singer'       => 'musician',
            'pastor'       => 'clergy',
            'bishop'       => 'clergy',
            'reverend'     => 'clergy',
            'priest'       => 'clergy',
            'traditional'  => 'traditional_leader',
            'chief '       => 'traditional_leader',
            'entrepreneur' => 'business_leader',
            'businessman'  => 'business_leader',
            ' ceo '        => 'business_leader',
        ];
        foreach ($map as $keyword => $cat) {
            if (str_contains($hay, $keyword)) return $cat;
        }
        return 'other';
    }
}
