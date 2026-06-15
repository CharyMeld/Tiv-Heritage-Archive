# Bible Dataset Pipeline — Tiv Heritage Archive

## Overview

This pipeline extracts a verse-by-verse aligned **English ↔ Tiv** Bible dataset
and loads it into the `bible_verses` MySQL table for use in the translation engine.

| Source | Module | Coverage | License |
|--------|--------|----------|---------|
| World English Bible | `WEB` | Full Bible (OT + NT) | Public domain |
| Tiv New Testament | `NTIV` | New Testament only | © Bible Society of Nigeria / CrossWire |

> **Why not NIV?** The New International Version is copyrighted by Biblica/Zondervan
> and cannot be freely distributed. The WEB is a modern, accurate, freely usable
> equivalent with identical verse numbering.
>
> **Tiv OT?** The full Tiv Bible (*Icighan Bibilo*) is not yet available through
> the SWORD Project. When it becomes available, re-run `extract.py` — the OT rows
> already have `tiv` columns set to NULL as placeholders.

---

## Step-by-Step Instructions

### 1. Install SWORD tools

In your terminal (type `! bash ...` in the Claude Code prompt):

```bash
bash bible-setup/install.sh
```

This installs:
- `diatheke` — SWORD command-line query tool
- `libsword-utils` — SWORD library utilities
- Downloads `WEB` and `NTIV` modules from CrossWire FTP

Verify:
```bash
diatheke -b WEB  -k "John 3:16"
diatheke -b NTIV -k "John 3:16"
```

### 2. Extract verses to CSV/JSON/SQL

```bash
python3 bible-setup/extract.py
```

Output files in `bible-setup/output/`:
| File | Format | Use |
|------|--------|-----|
| `bible_aligned.csv` | CSV | Excel, Google Sheets |
| `bible_aligned.json` | JSON | Web app / API |
| `bible_aligned.sql` | SQL | Direct MySQL import |

Expected output:
```
Total verses extracted : ~31,173
  New Testament        :  7,959
  Old Testament        : 23,214
  NT verses with Tiv   :  ~7,900
  Tiv coverage         :  ~99% of NT
```

### 3. Run the database migration

```bash
mysql -u root tiv_archive < database/migrations/create_bible_verses.sql
```

Or via phpMyAdmin on LAMPP.

### 4. Import data into MySQL

```bash
python3 bible-setup/import_to_db.py
```

Or directly from the SQL file:
```bash
mysql -u root tiv_archive < bible-setup/output/bible_aligned.sql
```

### 5. Verify

```sql
SELECT testament, COUNT(*) AS verses,
       SUM(tiv IS NOT NULL AND tiv != '') AS with_tiv
FROM bible_verses
GROUP BY testament;
```

---

## Using the Data in the Archive

### BibleVerse PHP Model

```php
require_once BASE_PATH . '/models/BibleVerse.php';
$model = new BibleVerse();

// Look up a verse
$verse = $model->getVerse('John', 3, 16);
echo $verse['english_web'];  // "For God so loved the world..."
echo $verse['tiv'];          // Tiv translation

// Get a whole chapter
$chapter = $model->getChapter('Matt', 5);

// Search
$results = $model->searchVerses('love your neighbour');

// Random aligned verse for homepage widget
$verse = $model->randomAligned();

// Stats
$stats = $model->stats();
// ['total'=>31173, 'nt_total'=>7959, 'tiv_count'=>7900, 'tiv_coverage_pct'=>99.3]
```

### Translation Memory Integration

The `getAlignedNT()` method returns all NT verses that have both English and Tiv
text, keyed as `"BookKey C:V"` (e.g. `"John 3:16"`). Feed this into the
translation engine as a high-quality parallel corpus.

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| `diatheke: command not found` | Run `install.sh` again |
| `WEB` module shows empty text | Check `~/.sword/` module path; try `sudo sword-installmgr -r CrossWire -mI WEB` |
| `NTIV` returns empty Tiv text | Module may not be on CrossWire — see alternate below |
| MySQL charset error | Ensure `CREATE DATABASE tiv_archive CHARACTER SET utf8mb4` |

### Alternative: Download Tiv NT directly from eBible.org

If NTIV is unavailable through SWORD:
1. Visit https://ebible.org/tiv/ and download the USFM or CSV export
2. Edit `extract.py` and replace the `get_verse()` calls for `NTIV`
   with lookups into a pre-loaded dict from the eBible download

---

## Dataset Schema

```
bible_verses
├── id            INT AUTO_INCREMENT
├── testament     ENUM('OT','NT')
├── book          VARCHAR(30)        — "John", "Genesis"
├── book_key      VARCHAR(10)        — SWORD key: "John", "Gen"
├── chapter       SMALLINT
├── verse         SMALLINT
├── english_web   TEXT               — World English Bible
└── tiv           TEXT NULL          — Tiv text; NULL = not yet available
```
