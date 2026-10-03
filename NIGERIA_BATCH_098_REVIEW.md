# Research batch 098 — Jigawa names and progress

Researched 2026-10-02, after the Jigawa Phase 4 QC (`NIGERIA_QC_JIGAWA.md`). Published only after your approval.

## What it adds

- **3 other names**, all from the federal government's Jigawa profile:
  - **Mangawa** for the Manga
  - **Ngizimawa** for the Ngizim
  - **Badawa** for the Bade, with a note that in Bauchi the Atlas uses Badawa for the Mbat
- **fix_098:** Jigawa's research progress (row #54) changes from "not started, 0/27" to **27/27 LGAs researched, quality control in progress**, with a summary of batches 093–098.
## Test on a copy of the live database (2 Oct 2026, after 097 went live)

- **Import:** 2 sources reused and 3 names.
- **fix_098:** applied. A second `--apply` makes 0 changes, so it is idempotent. The progress row (#54) becomes qc_in_progress, 27/27.
- **Pages:** the Manga, Ngizim and Bade pages show their new names, with no PHP errors.
- **QC:** 0 problems; alias notes fall from 5 to 2.
- **Undo:** `fix_098 --revert --apply`, then roll back. Every table count and the progress rows are identical to the live copy.
- **Deploy order:** import → fix_098 --apply → publish.
