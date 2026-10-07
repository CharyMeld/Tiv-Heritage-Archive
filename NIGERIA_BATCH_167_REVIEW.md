# Research batch 167 — Kaduna: INEC wards

Built 7 October 2026 by `python3 inec_wards_batch.py kaduna "Kaduna" <dir>` from `data/inec_kaduna_ras.json`, which `inec_parse_directory.py` made from INEC's Directory of Polling Units, Kaduna State (revised January 2015; `data/inec_kaduna.pdf`, the Internet Archive copy). Created in review; published only after your approval.

## What it adds

- **255 wards** (INEC registration areas) in all 23 Kaduna LGAs, each with its INEC code and its number of polling units (5,101 polling units in all).
- The parser's checks passed: for every LGA, the number of wards and the sum of their polling units match INEC's summary table.
- All 23 LGA names in the directory match the archive's LGAs; no hand mapping was needed.
- Ward names are kept as INEC prints them, e.g. "Kafanchan 'A'", "Kwarbai \"A\"", "Gure/Kahugu". "S/Ggarin Arewa Tirkaniya" (Zaria) looks like an INEC typo and is kept as printed.

## Wards per LGA

- birnin-gwari: 11
- chikun: 12
- giwa: 11
- igabi: 12
- ikara: 10
- jaba: 10
- jema-a: 12
- kachia: 12
- kaduna-north: 12
- kaduna-south: 13
- kagarko: 10
- kajuru: 10
- kaura: 10
- kauru: 11
- kubau: 11
- kudan: 10
- lere: 11
- makarfi: 10
- sabon-gari: 11
- sanga: 11
- soba: 11
- zangon-kataf: 11
- zaria: 13

## Test (fresh copy of production including 166c, 7 Oct 2026)

- Import: 255 wards, 255 polling-unit statistics, 255 source links (INEC source reused), 2 gaps.
- Kaduna QC: 0 problems (the 6 people-coverage and 8 language checks from 166–166c are unchanged).
- LGA pages list their wards (e.g. Jema'a: Kafanchan 'A', Kagoma, Godogodo, Jagindi). Sitemap 3,099. 0 PHP fatals.
- Rollback: exact (only activity_log grew).
