# Research batch 092 — Kano names and progress

Researched 2026-10-02, after the Kano Phase 4 QC (`NIGERIA_QC_KANO.md`). Published only after your approval.

## What it adds

- **The Hausa's own name:** **Hàusàawáa** (singular Bàháushèe), from the Atlas, Hausa entry, field 1.C.
- **The Fulani's own name:** **Fulɓe** (singular Pullo), from the Atlas, Fulfulde entry, field 1.C. **Filani** is added as another spelling (field 2.C).
- Both records had no other names, which the QC flags in every state. These are national records, so the fix applies everywhere.
- **fix_092:** Kano's research progress (row #56) changes from "not started, 0/44" to **44/44 LGAs researched, quality control in progress**, with a summary of batches 087–092.
- **Not changed:** the 31 LGAs with no people linked, because no source read names their peoples.
## Test on a copy of the live database (2 Oct 2026, after 091 went live)

- **Import:** 1 source reused (the Atlas) and 5 names.
- **fix_092:** applied. A second `--apply` makes 0 changes, so it is idempotent. The progress row (#56) becomes qc_in_progress, 44/44.
- **Pages:** the Hausa page shows Hàusàawáa; the Fulani page shows Fulɓe, Pullo and Filani. No PHP errors.
- **QC:** 0 problems. Alias notes: Kano 2 → 0, Gombe 3 → 1, and Bauchi lower.
- **Undo:** `fix_092 --revert --apply`, then roll back. Every table count and the progress rows are identical to the live copy.
- **Deploy order:** import → fix_092 --apply → publish.
