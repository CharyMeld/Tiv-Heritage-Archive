# Research batch 068 — Borno names and progress

Researched 2026-10-01, after the Borno Phase 4 QC (`NIGERIA_QC_BORNO.md`). Approved by the owner; published only after "deploy".

## What it adds

- **2 other names for the Kanuri**, from the Atlas's Kanuri entry:
  - **Kànúrí**: their own name (field 1.C)
  - **Beriberi**: an outside name (field 2.C)
- **Not recorded:** 'Kamberi', also in field 2.C, because it is the usual name of a different people (the Kambari).
- **fix_068:** Borno's research progress changes from "not started, 0/27" to **27/27 LGAs researched, quality control in progress**, with a summary of batches 063–068.

## Test (1 Oct 2026, on a fresh copy of live, with 067 in it)

- **Import:** dry run, then apply. Result: 2 names; the Atlas source is reused.
- **fix_068:** dry run, then apply. One change, to research_progress #45: status, lgas_researched and notes.
- **Publish:** nothing new to publish, since names follow their record.
- **Pages:** the Kanuri page shows "Beriberi" and the Borno page returns 200, with no PHP errors.
- **Sitemap:** 3,026, unchanged.
- **Borno QC:** 0 problems. Alias notes fell from 12 to 11. Progress now reads qc_in_progress, 27/27.
- **Undo:** revert fix_068, then roll back. The progress row is back to "not_started 0", and every table count is identical to the pre-import snapshot.
