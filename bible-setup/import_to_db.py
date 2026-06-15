#!/usr/bin/env python3
"""
Tiv Heritage Archive — Bible Data DB Importer
Reads bible-setup/output/bible_aligned.csv and inserts into MySQL.

Usage:
    python3 bible-setup/import_to_db.py

Requires:
    pip3 install mysql-connector-python
"""

import csv
import os
import sys

try:
    import mysql.connector
except ImportError:
    print("Installing mysql-connector-python...")
    os.system(f"{sys.executable} -m pip install mysql-connector-python -q")
    import mysql.connector

# ── DB config (matches config/database.php) ──────────────────────────────────
DB_CONFIG = {
    "host":     "127.0.0.1",
    "database": "tiv_archive",
    "user":     "root",
    "password": "",
    "charset":  "utf8mb4",
}

MIGRATION_SQL = os.path.join(
    os.path.dirname(__file__),
    "../database/migrations/create_bible_verses.sql"
)
CSV_PATH = os.path.join(os.path.dirname(__file__), "output/bible_aligned.csv")
BATCH    = 500   # rows per INSERT batch


def run():
    if not os.path.exists(CSV_PATH):
        print(f"ERROR: {CSV_PATH} not found. Run extract.py first.")
        sys.exit(1)

    print("Connecting to MySQL...")
    conn = mysql.connector.connect(**DB_CONFIG)
    cur  = conn.cursor()

    # Run migration to create table
    print("Creating bible_verses table (if not exists)...")
    with open(MIGRATION_SQL, "r") as f:
        ddl = f.read()
    for statement in ddl.split(";"):
        stmt = statement.strip()
        if stmt:
            cur.execute(stmt)
    conn.commit()

    # Load CSV
    print(f"Loading {CSV_PATH}...")
    with open(CSV_PATH, newline="", encoding="utf-8") as f:
        reader = csv.DictReader(f)
        rows = list(reader)

    total = len(rows)
    print(f"Inserting {total:,} verses in batches of {BATCH}...")

    inserted = 0
    for i in range(0, total, BATCH):
        batch = rows[i : i + BATCH]
        values = []
        for r in batch:
            values.append((
                r["testament"],
                r["book"],
                r["book_key"],
                int(r["chapter"]),
                int(r["verse"]),
                r["english_web"],
                r["tiv"] or None,
            ))
        cur.executemany(
            """INSERT INTO bible_verses
               (testament, book, book_key, chapter, verse, english_web, tiv)
               VALUES (%s, %s, %s, %s, %s, %s, %s)
               ON DUPLICATE KEY UPDATE
                 english_web = VALUES(english_web),
                 tiv         = COALESCE(VALUES(tiv), tiv)
            """,
            values,
        )
        conn.commit()
        inserted += len(batch)
        pct = inserted / total * 100
        print(f"  {inserted:,}/{total:,} ({pct:.1f}%)", end="\r")

    cur.close()
    conn.close()

    print(f"\nDone — {inserted:,} verses imported into bible_verses.")


if __name__ == "__main__":
    run()
