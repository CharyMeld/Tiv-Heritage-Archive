#!/usr/bin/env python3
"""
Tiv Heritage Archive — Bible Extraction Script
Extracts verse-by-verse aligned WEB (English) ↔ NTIV (Tiv) dataset
using the diatheke SWORD command-line tool.

Usage:
    python3 bible-setup/extract.py

Outputs (in bible-setup/output/):
    bible_aligned.csv    — spreadsheet-friendly
    bible_aligned.json   — for the web app
    bible_aligned.sql    — ready to import into MySQL
"""

import subprocess
import csv
import json
import os
import re
import sys

# ── Config ────────────────────────────────────────────────────────────────────
OUTPUT_DIR   = os.path.join(os.path.dirname(__file__), "output")
WEB_MODULE   = "WEB"       # World English Bible (public domain, full Bible)
TIV_MODULE   = "NTIV"      # Tiv New Testament (CrossWire)
DIATHEKE     = "diatheke"  # must be on PATH after install.sh

# ── Canon data — book name → (SWORD key, chapters) ───────────────────────────
# Old Testament (WEB only; Tiv OT not yet available via SWORD)
OT_BOOKS = [
    ("Genesis",        "Gen",    50),  ("Exodus",          "Exod",   40),
    ("Leviticus",      "Lev",    27),  ("Numbers",         "Num",    36),
    ("Deuteronomy",    "Deut",   34),  ("Joshua",          "Josh",   24),
    ("Judges",         "Judg",   21),  ("Ruth",            "Ruth",    4),
    ("1 Samuel",       "1Sam",   31),  ("2 Samuel",        "2Sam",   24),
    ("1 Kings",        "1Kgs",   22),  ("2 Kings",         "2Kgs",   25),
    ("1 Chronicles",   "1Chr",   29),  ("2 Chronicles",    "2Chr",   36),
    ("Ezra",           "Ezra",   10),  ("Nehemiah",        "Neh",    13),
    ("Esther",         "Esth",   10),  ("Job",             "Job",    42),
    ("Psalms",         "Ps",    150),  ("Proverbs",        "Prov",   31),
    ("Ecclesiastes",   "Eccl",   12),  ("Song of Solomon", "Song",    8),
    ("Isaiah",         "Isa",    66),  ("Jeremiah",        "Jer",    52),
    ("Lamentations",   "Lam",     5),  ("Ezekiel",         "Ezek",   48),
    ("Daniel",         "Dan",    12),  ("Hosea",           "Hos",    14),
    ("Joel",           "Joel",    3),  ("Amos",            "Amos",    9),
    ("Obadiah",        "Obad",    1),  ("Jonah",           "Jonah",   4),
    ("Micah",          "Mic",     7),  ("Nahum",           "Nah",     3),
    ("Habakkuk",       "Hab",     3),  ("Zephaniah",       "Zeph",    3),
    ("Haggai",         "Hag",     2),  ("Zechariah",       "Zech",   14),
    ("Malachi",        "Mal",     4),
]

# New Testament (both WEB and NTIV available)
NT_BOOKS = [
    ("Matthew",           "Matt",    28), ("Mark",            "Mark",    16),
    ("Luke",              "Luke",    24), ("John",            "John",    21),
    ("Acts",              "Acts",    28), ("Romans",          "Rom",     16),
    ("1 Corinthians",     "1Cor",    16), ("2 Corinthians",   "2Cor",    13),
    ("Galatians",         "Gal",      6), ("Ephesians",       "Eph",      6),
    ("Philippians",       "Phil",     4), ("Colossians",      "Col",      4),
    ("1 Thessalonians",   "1Thess",   5), ("2 Thessalonians", "2Thess",   3),
    ("1 Timothy",         "1Tim",     6), ("2 Timothy",       "2Tim",     4),
    ("Titus",             "Titus",    3), ("Philemon",        "Phlm",     1),
    ("Hebrews",           "Heb",     13), ("James",           "Jas",      5),
    ("1 Peter",           "1Pet",     5), ("2 Peter",         "2Pet",     3),
    ("1 John",            "1John",    5), ("2 John",          "2John",    1),
    ("3 John",            "3John",    1), ("Jude",            "Jude",     1),
    ("Revelation",        "Rev",     22),
]

# Verses per chapter (approximate — diatheke stops when verse not found)
# We iterate up to this many verses and stop on empty output
MAX_VERSES_PER_CHAPTER = 176  # Psalm 119 is the longest (176 verses)


# ── Helpers ───────────────────────────────────────────────────────────────────

def get_verse(module: str, book_key: str, chapter: int, verse: int) -> str:
    """Call diatheke and return cleaned verse text, or '' if not found."""
    ref = f"{book_key} {chapter}:{verse}"
    try:
        result = subprocess.run(
            [DIATHEKE, "-b", module, "-k", ref],
            capture_output=True, text=True, timeout=5
        )
        text = result.stdout.strip()
        # diatheke output format: "BookName C:V: text (MODULE)"
        # Strip the reference prefix and module suffix
        text = re.sub(r"^[^:]+:\d+:\s*", "", text)           # remove "Book C:V: "
        text = re.sub(r"\s*\([A-Z]+\)\s*$", "", text)        # remove "(WEB)"
        text = text.strip()
        # diatheke returns the ref itself when verse not found
        if not text or text == ref or text.startswith(book_key):
            return ""
        return text
    except Exception:
        return ""


def chapter_verse_count(module: str, book_key: str, chapter: int) -> int:
    """Find actual verse count for this chapter via binary-ish scan."""
    # Quick scan: try up to MAX and stop at first empty
    for v in range(1, MAX_VERSES_PER_CHAPTER + 1):
        if not get_verse(module, book_key, chapter, v):
            return v - 1
    return MAX_VERSES_PER_CHAPTER


# ── Main extraction ───────────────────────────────────────────────────────────

def extract_all():
    os.makedirs(OUTPUT_DIR, exist_ok=True)

    csv_path  = os.path.join(OUTPUT_DIR, "bible_aligned.csv")
    json_path = os.path.join(OUTPUT_DIR, "bible_aligned.json")
    sql_path  = os.path.join(OUTPUT_DIR, "bible_aligned.sql")

    rows = []  # list of dicts

    # Check diatheke is available
    try:
        subprocess.run([DIATHEKE, "-b", "system", "-k", "modulelist"],
                       capture_output=True, timeout=5)
    except FileNotFoundError:
        print("ERROR: diatheke not found. Run install.sh first.")
        sys.exit(1)

    # ── Process NT (both English + Tiv) ──────────────────────────────────────
    print("=== Extracting New Testament (WEB + NTIV) ===")
    for book_name, book_key, num_chapters in NT_BOOKS:
        print(f"  {book_name}...", end=" ", flush=True)
        for ch in range(1, num_chapters + 1):
            verse_count = chapter_verse_count(WEB_MODULE, book_key, ch)
            for v in range(1, verse_count + 1):
                eng = get_verse(WEB_MODULE, book_key, ch, v)
                tiv = get_verse(TIV_MODULE, book_key, ch, v)
                if not eng:
                    continue
                rows.append({
                    "testament":     "NT",
                    "book":          book_name,
                    "book_key":      book_key,
                    "chapter":       ch,
                    "verse":         v,
                    "english_web":   eng,
                    "tiv":           tiv,
                })
        print(f"✓ {num_chapters} ch")

    # ── Process OT (English only — Tiv OT not in SWORD) ──────────────────────
    print("\n=== Extracting Old Testament (WEB only) ===")
    for book_name, book_key, num_chapters in OT_BOOKS:
        print(f"  {book_name}...", end=" ", flush=True)
        for ch in range(1, num_chapters + 1):
            verse_count = chapter_verse_count(WEB_MODULE, book_key, ch)
            for v in range(1, verse_count + 1):
                eng = get_verse(WEB_MODULE, book_key, ch, v)
                if not eng:
                    continue
                rows.append({
                    "testament":   "OT",
                    "book":        book_name,
                    "book_key":    book_key,
                    "chapter":     ch,
                    "verse":       v,
                    "english_web": eng,
                    "tiv":         "",   # placeholder — add when OT Tiv available
                })
        print(f"✓ {num_chapters} ch")

    # ── Write CSV ─────────────────────────────────────────────────────────────
    print(f"\nWriting CSV → {csv_path}")
    with open(csv_path, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=[
            "testament", "book", "book_key", "chapter", "verse",
            "english_web", "tiv"
        ])
        writer.writeheader()
        writer.writerows(rows)

    # ── Write JSON ────────────────────────────────────────────────────────────
    print(f"Writing JSON → {json_path}")
    with open(json_path, "w", encoding="utf-8") as f:
        json.dump(rows, f, ensure_ascii=False, indent=2)

    # ── Write SQL ─────────────────────────────────────────────────────────────
    print(f"Writing SQL  → {sql_path}")
    with open(sql_path, "w", encoding="utf-8") as f:
        f.write("-- Tiv Heritage Archive — Bible verses (WEB English + NTIV Tiv)\n")
        f.write("-- Generated by bible-setup/extract.py\n\n")
        f.write("SET NAMES utf8mb4;\n\n")
        f.write("INSERT INTO bible_verses\n")
        f.write("  (testament, book, book_key, chapter, verse, english_web, tiv)\nVALUES\n")
        chunks = []
        for r in rows:
            def esc(s):
                return s.replace("\\", "\\\\").replace("'", "\\'")
            chunks.append(
                f"  ('{esc(r['testament'])}','{esc(r['book'])}','{esc(r['book_key'])}'"
                f",{r['chapter']},{r['verse']}"
                f",'{esc(r['english_web'])}','{esc(r['tiv'])}')"
            )
        f.write(",\n".join(chunks))
        f.write(";\n")

    # ── Summary ───────────────────────────────────────────────────────────────
    nt_rows = [r for r in rows if r["testament"] == "NT"]
    tiv_rows = [r for r in nt_rows if r["tiv"]]
    print(f"\n{'='*50}")
    print(f"Total verses extracted : {len(rows):,}")
    print(f"  New Testament        : {len(nt_rows):,}")
    print(f"  Old Testament        : {len(rows) - len(nt_rows):,}")
    print(f"  NT verses with Tiv   : {len(tiv_rows):,}")
    print(f"  Tiv coverage         : {len(tiv_rows)/len(nt_rows)*100:.1f}% of NT")
    print(f"{'='*50}")
    print("Done. Run: python3 bible-setup/import_to_db.py")


if __name__ == "__main__":
    extract_all()
