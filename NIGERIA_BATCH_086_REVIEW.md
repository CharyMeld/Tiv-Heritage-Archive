# Research batch 086 — Gombe names and progress

Researched 2026-10-02, after the Gombe Phase 4 QC (`NIGERIA_QC_GOMBE.md`). Published only after your approval.

## What it adds

- **Terawa**, recorded as another name of the **Tera**, from the federal government's Gombe profile. The Tera record had no other names.
- **fix_086:** Gombe's research progress (row #52) changes from "not started, 0/11" to **11/11 LGAs researched, quality control in progress**, with a summary of batches 081–086.
- **Not changed:**
  - Fulani and Hausa have no other names; this is a national gap.
  - Cen Tuum has no family, which is correct because it is a language isolate.
  - Jan Awei has no other names; the Atlas gives none.
## Test on a copy of the live database (2 Oct 2026, after 085 went live)

- **Import:** 1 source reused (the federal profile) and 1 name.
- **fix_086:** run as a dry run, then applied. A second `--apply` makes 0 changes, so it is idempotent. The progress row (#52) becomes qc_in_progress, 11/11.
- **Pages:** the Tera page shows "Terawa".
- **Sitemap:** 3,026, unchanged.
- **Gombe QC:** 0 problems; alias notes fall from 4 to 3; 1 check (Cen Tuum, an isolate).
- **Undo:** `fix_086 --revert --apply`, then roll back. Every table count and the progress rows are identical to the live copy.
- **Deploy order:** import → fix_086 --apply → publish. To undo: revert fix_086 → roll back.
