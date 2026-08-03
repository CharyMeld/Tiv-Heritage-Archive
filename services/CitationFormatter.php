<?php
/**
 * Citation Formatter
 *
 * Generates plain-text citations from a `sources` row in APA, MLA,
 * Chicago (author-date), Harvard, BibTeX, and RIS. Oral tradition /
 * interview / community-submission sources (no publisher/URL) are
 * cited as attributed oral sources rather than forced through a
 * book/journal template — following the convention (recommended by
 * APA style guidance) of giving oral communications from community
 * knowledge-holders a full reference-list entry rather than treating
 * them as in-text-only "personal communication".
 */
class CitationFormatter
{
    private array $source;
    private string $author;
    private string $year;
    private bool $isOral;
    private ?string $canonicalUrl;

    private const ORAL_TYPES = ['oral_tradition', 'interview', 'community_submission'];

    /** $canonicalUrl: this archive's own permanent link for the source, used as a "Retrieved from" fallback only when the source has no DOI/URL of its own. */
    public function __construct(array $source, ?string $canonicalUrl = null)
    {
        $this->source = $source;
        $this->isOral = in_array($source['source_type'] ?? '', self::ORAL_TYPES, true);

        // `contributor_name` records who added this reference to the
        // archive — it is NOT the work's author, and must never stand
        // in for one except for oral/interview/community-submission
        // sources, where the contributor genuinely is the "as told by"
        // attribution. For everything else, fall back to the
        // publisher as an organizational author (a normal citation
        // convention for byline-less institutional/web sources)
        // before finally falling back to the archive's own name.
        if (trim($source['author'] ?? '') !== '') {
            $this->author = $source['author'];
        } elseif ($this->isOral && trim($source['contributor_name'] ?? '') !== '') {
            $this->author = $source['contributor_name'];
        } elseif (trim($source['publisher'] ?? '') !== '') {
            $this->author = $source['publisher'];
        } else {
            $this->author = 'Tiv Heritage Archive';
        }

        $this->year = (string) ($source['year_recorded'] ?? ($source['access_date'] ? date('Y', strtotime($source['access_date'])) : date('Y')));
        $this->canonicalUrl = $canonicalUrl;
    }

    public static function make(array $source, ?string $canonicalUrl = null): self
    {
        return new self($source, $canonicalUrl);
    }

    /** All six formats keyed by their export-URL slug. */
    public function all(): array
    {
        return [
            'apa' => $this->apa(),
            'mla' => $this->mla(),
            'chicago' => $this->chicago(),
            'harvard' => $this->harvard(),
            'bibtex' => $this->bibtex(),
            'ris' => $this->ris(),
        ];
    }

    public function apa(): string
    {
        $title = $this->title();
        if ($this->isOral) {
            $container = $this->oralContainer();
            return "{$this->author} ({$this->year}). {$title} [{$container}]." . $this->suffix();
        }
        $publisher = $this->source['publisher'] ?? null;
        $parts = ["{$this->author} ({$this->year}). {$title}."];
        if ($publisher) $parts[] = "{$publisher}.";
        return implode(' ', $parts) . $this->suffix();
    }

    public function mla(): string
    {
        $title = $this->title();
        if ($this->isOral) {
            return "{$this->author}. {$title}. {$this->oralContainer()}, {$this->year}." . $this->suffix();
        }
        $publisher = $this->source['publisher'] ?? null;
        $parts = ["{$this->author}. \"{$title}.\""];
        if ($publisher) $parts[] = "{$publisher},";
        $parts[] = "{$this->year}.";
        return implode(' ', $parts) . $this->suffix();
    }

    public function chicago(): string
    {
        // Author-date style.
        $title = $this->title();
        if ($this->isOral) {
            return "{$this->author}. {$this->year}. {$title}. {$this->oralContainer()}." . $this->suffix();
        }
        $publisher = $this->source['publisher'] ?? null;
        $parts = ["{$this->author}. {$this->year}. \"{$title}.\""];
        if ($publisher) $parts[] = "{$publisher}.";
        return implode(' ', $parts) . $this->suffix();
    }

    public function harvard(): string
    {
        $title = $this->title();
        if ($this->isOral) {
            return "{$this->author} ({$this->year}) {$title}. {$this->oralContainer()}." . $this->suffix();
        }
        $publisher = $this->source['publisher'] ?? null;
        $parts = ["{$this->author} ({$this->year}) {$title}."];
        if ($publisher) $parts[] = "{$publisher}.";
        return implode(' ', $parts) . $this->suffix();
    }

    public function bibtex(): string
    {
        $key = $this->citeKey();
        $entryType = $this->isOral ? 'misc' : (($this->source['source_type'] ?? '') === 'research' ? 'article' : 'book');

        $fields = [
            'author' => $this->author,
            'title' => $this->title(),
            'year' => $this->year,
        ];
        if (!empty($this->source['publisher'])) $fields['publisher'] = $this->source['publisher'];
        if (!empty($this->source['isbn'])) $fields['isbn'] = $this->source['isbn'];
        if (!empty($this->source['doi'])) $fields['doi'] = $this->source['doi'];
        if (!empty($this->source['url'])) {
            $fields['url'] = $this->source['url'];
        } elseif ($this->canonicalUrl) {
            $fields['url'] = $this->canonicalUrl;
        }
        if ($this->isOral) {
            $fields['howpublished'] = $this->oralContainer();
            if (!empty($this->source['location'])) $fields['address'] = $this->source['location'];
        }
        if (!empty($this->source['access_date'])) $fields['note'] = 'Accessed ' . $this->source['access_date'];

        $lines = ["@{$entryType}{{$key},"];
        $entries = [];
        foreach ($fields as $k => $v) {
            $entries[] = "    {$k} = {" . $this->bibtexEscape($v) . "}";
        }
        $lines[] = implode(",\n", $entries);
        $lines[] = "}";
        return implode("\n", $lines);
    }

    public function ris(): string
    {
        $type = $this->isOral ? 'GEN' : (($this->source['source_type'] ?? '') === 'research' ? 'JOUR' : 'BOOK');

        $lines = ["TY  - {$type}"];
        $lines[] = "AU  - {$this->author}";
        $lines[] = "TI  - {$this->title()}";
        $lines[] = "PY  - {$this->year}";
        if (!empty($this->source['publisher'])) $lines[] = "PB  - {$this->source['publisher']}";
        if (!empty($this->source['location'])) $lines[] = "CY  - {$this->source['location']}";
        if (!empty($this->source['isbn'])) $lines[] = "SN  - {$this->source['isbn']}";
        if (!empty($this->source['doi'])) $lines[] = "DO  - {$this->source['doi']}";
        if (!empty($this->source['url'])) {
            $lines[] = "UR  - {$this->source['url']}";
        } elseif ($this->canonicalUrl) {
            $lines[] = "UR  - {$this->canonicalUrl}";
        }
        if (!empty($this->source['access_date'])) $lines[] = "Y2  - {$this->source['access_date']}";
        if (!empty($this->source['notes'])) $lines[] = "N1  - {$this->source['notes']}";
        $lines[] = "ER  - ";
        return implode("\n", $lines);
    }

    /* ── Helpers ──────────────────────────────────────────────── */

    private function title(): string
    {
        return trim($this->source['title'] ?? '') !== '' ? $this->source['title'] : 'Untitled source';
    }

    /** Human label for how an oral-type source was recorded (used as the "container" in place of a publisher). */
    private function oralContainer(): string
    {
        $labels = [
            'oral_tradition' => 'Oral tradition',
            'interview' => 'Interview',
            'community_submission' => 'Community submission',
        ];
        $label = $labels[$this->source['source_type'] ?? ''] ?? 'Oral source';
        if (!empty($this->source['location'])) {
            $label .= ', ' . $this->source['location'];
        }
        return $label;
    }

    private function suffix(): string
    {
        if (!empty($this->source['doi'])) {
            $doi = $this->source['doi'];
            $hasScheme = stripos($doi, 'doi.org') !== false || stripos($doi, 'http') === 0;
            return ' ' . ($hasScheme ? $doi : 'https://doi.org/' . $doi);
        }
        if (!empty($this->source['url'])) return ' ' . $this->source['url'];
        if ($this->canonicalUrl) return ' Retrieved from ' . $this->canonicalUrl;
        return '';
    }

    private function citeKey(): string
    {
        $authorSlug = strtolower(preg_replace('/[^a-zA-Z]/', '', explode(' ', $this->author)[0] ?: 'tiv'));
        return ($authorSlug ?: 'tiv') . $this->year;
    }

    private function bibtexEscape(string $value): string
    {
        return str_replace(['{', '}'], ['\\{', '\\}'], $value);
    }
}
