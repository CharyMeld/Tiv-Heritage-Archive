# Research batch 056 — Kogi names and progress

Researched 2026-09-30, after the Kogi Phase 4 QC (`NIGERIA_QC_KOGI.md`). Approved by the owner; published only after "deploy".

## What it adds

- **3 other names**, from sources already read:
  - **Anebira** for the Ebira (Wikipedia, Kogi State).
  - **Anufawa** and **Nyffe** for the Nupe (Blench's Atlas, the field for other names of the people).
- **fix_056:** Kogi's research progress changes from "not started, 0/21" to **21/21 LGAs researched, quality control in progress**, with a summary of batches 051–056.
## Test on a copy of the live database (30 Sep 2026, after batch 055)

- **Import:** 3 names; both sources were reused.
- **fix_056:** 1 change. Kogi progress becomes *qc_in_progress*, 21/21. A second run makes no change. The fix was made from fix_050 using exact, checked text replacements (no generated substrings).
- **Kogi QC:** 0 problems. The "no other names" notes fell from 7 to 5.
- **Undo:** fix_056 --revert, then rollback. Every touched table matches a fresh copy of live **exactly**, with no publish rows.

**Deploy order:** import --apply → fix_056 --apply. **Rollback order:** fix_056 --revert --apply → rollback.
