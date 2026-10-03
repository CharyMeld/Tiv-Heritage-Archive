# Research batch 050 — Plateau names and progress

Researched 2026-09-30, after the Plateau Phase 4 QC (`NIGERIA_QC_PLATEAU.md`). Approved by the owner; published only after "deploy".

## What it adds

- **10 other names**, all from sources already read:
  - **Glottolog's names** for 8 Plateau languages, where they differ from the Atlas. Batch 044 already gave these in the text but did not record them as names:
    Eten (aten); Amo (map); Mindat (mundat); Sya (sha); Fyam (pyam); Pye (pe); Izora (zora); Duguri (doori).
  - **The state government's spellings**: Taroh (Tarok) and Mwaaghvul (Mwaghavul).
- **fix_050:** Plateau's research progress changes from "not started, 0/17" to **17/17 LGAs researched, quality control in progress**, with a summary of batches 044–050.
- **Not added:** Atlas names for the peoples. The Atlas's people fields (1.C and 2.C) for the Plateau peoples were already used in batch 044b, and the rest are language names, which stay on the language records.
## Test on a copy of the live database (30 Sep 2026, after batch 049)

- **Import:** 10 names; both sources were reused.
- **fix_050:** 1 change. Plateau progress becomes *qc_in_progress*, 17/17. A second run makes no change.
- **Plateau QC:** 0 problems. The "no other names" notes fell from 18 to 15.
- **Undo:** fix_050 --revert, then rollback. Every touched table matches a fresh copy of live **exactly**. There are no publish rows, because names need no publishing.

**Deploy order:** import --apply → fix_050 --apply. **Rollback order:** fix_050 --revert --apply → rollback.
