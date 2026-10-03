# Research batch 062 — Adamawa: classification, names and progress

Researched 2026-10-01, after the Adamawa Phase 4 QC (`NIGERIA_QC_ADAMAWA.md`). Approved by the owner; published only after "deploy".

## What it adds

- **Atlantic**, a new branch record, so that **Fulfulde** can be placed. The move itself is in fix_062.
  - The Atlas classes Fulfulde as Atlantic (Northern branch, Senegal group).
  - Glottolog does not recognise 'Atlantic' as one group. It puts Fula in North-Central Atlantic (`nort3146`) → Fula-Sereer (`peul1234`).
  - The branch uses the Atlas's name, as the archive's other branches do, and has no Glottocode. Glottolog's view is stated in its text.
- **One other name:** **Margi ti ntəm** for Margi South, from the Atlas index.
- **Correction to the QC proposal:** Lamjavu, Deŋsavu and Tolavu are the people's own names (Atlas field 1.C). The archive records those only on a people record, and it has none for the Lamja, Deŋsa or Tola, so they are not recorded. Ngwaba's 'Gombi' and 'Goba' are already on the Ngwaba people record.
- **fix_062:**
  - Fulfulde is placed under Atlantic, and a sentence on its classification is added.
  - Adamawa's research progress changes from "not started, 0/21" to **21/21 LGAs researched, quality control in progress**.

## Atlantic (text)

> Atlantic is the name Roger Blench's Atlas of Nigerian Languages (2020) gives to the group of Niger-Congo languages to which it assigns Fulfulde, classing Fulfulde in its Northern branch, Senegal group. Fulfulde, the language of the Fulɓe (Fulani), is spoken throughout Nigeria and in other countries of West and Central Africa, according to the Atlas. Glottolog does not treat Atlantic as a single group: it places the Fula languages (fula1264) in North-Central Atlantic (nort3146), under Fula-Sereer (peul1234), directly within Atlantic-Congo. The archive follows the Atlas's name, as it does for its other branches, and records no Glottocode for the branch.

## Test (1 Oct 2026, on a fresh copy of live, with 061 in it)

- **Import:** dry run, then apply. Result: 1 language record (Atlantic), 1 name, and 2 source links. One new source (Glottolog, Fula); one reused (the Atlas).
- **fix_062:** dry run, then apply. Two changes:
  - Fulfulde: parent_id and description
  - research_progress #39: status, lgas_researched and notes
- **Publish:** 1 language.
- **Pages:** the Atlantic, Fulfulde, Margi South and Adamawa pages return 200 with no PHP errors. Fulfulde names Atlantic, and Margi South shows "Margi ti ntəm".
- **Sitemap:** 3,026, unchanged.
- **Adamawa QC:** 0 problems. Checks fell from 3 to 2: only the Glottocodes for Joole and Mukta remain, and both are real gaps. Progress: qc_in_progress, 21/21.
- **Undo:** revert fix_062, unpublish, then roll back. Fulfulde's parent is NULL again and the progress row is back to "not_started 0". Every table count matches the pre-import snapshot except two, both known side effects of the test:
  - activity_log: +1
  - archive_search_index: +1
