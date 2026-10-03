# Research batch 080 — Bauchi names and progress

Researched 2026-10-02, after the Bauchi Phase 4 QC (`NIGERIA_QC_BAUCHI.md`). Approved by the owner; published only after "deploy".

## What it adds

- **7 people–LGA links**, taken from sources already read:

  - **giade: Fulani**. Wikipedia (Giade): 'Dominated mainly by the Fulani tribe.'
  - **itas-gadau: Hausa**. Wikipedia (Itas/Gadau): 'The predominant ethnic groups in the area are the Hausa and Fulani.'
  - **itas-gadau: Fulani**. Wikipedia (Itas/Gadau): 'The predominant ethnic groups in the area are the Hausa and Fulani.'
  - **jama-are: Fulani**. Wikipedia (Jama'are): 'Most of the inhabitants of Jama'are are members of the Fulani, Shirawa, Kanuri, but Fulani is the most prominent tribe.'
  - **jama-are: Kanuri**. Wikipedia (Jama'are): 'Most of the inhabitants of Jama'are are members of the Fulani, Shirawa, Kanuri.'
  - **kirfi: Hausa**. Wikipedia (Kirfi): 'The predominant ethnic group in the area is the Hausa.'
  - **warji: Warji** (*reported*). Blench's Atlas (2020), Warji entry: 'Bauchi State, Darazo LGA, Ganjuwa district, and Ningi LGA, Warji district' — the Warji district of Ningi is now Warji LGA.

- **A Glottocode for Vaghat–Ya–Bijim–Legeri** (#356, a Plateau record): `vagh1247`, Glottolog's Kwangic group. It contains Vaghat (vagh1250) and Bijim (biji1246), and Legeri (Kaduk) is a dialect of Bijim. This is the same grouping as the Atlas cluster (No. 469). The batch fills an empty field.
- **fix_080:** Bauchi's research progress (row #42) changes from "not started, 0/20" to **20/20 LGAs researched, quality control in progress**, with a summary of batches 075–080.
- **Not linked:** Katagum and Shira, because no source read names their peoples; the Shirawa, who have no record; and Damlanci's Glottocode, which Glottolog lacks.

## Research gaps

- **Bauchi: peoples of Katagum and Shira.** No source read names the peoples of these two LGAs; Wikipedia's Katagum article describes Katagum town, which is in Zaki LGA.
- **Bauchi: the Shirawa.** Named by Wikipedia among Jama'are's inhabitants; the Atlas calls Shirawa an extinct Chadic language of the Katagum region. No people record.
- **Bauchi: Damlanci classification.** First described in Blench (2019); no Glottolog entry.
## Test on a copy of the live database (2 Oct 2026, after 079 went live)

- **Import:** the dry run and the real import agree: 6 sources reused, 7 relations, 1 update (the Glottocode) and 3 gaps.
- **fix_080:** run as a dry run, then applied. A second `--apply` makes 0 changes, so it is idempotent.
  - The progress row (#42) becomes qc_in_progress, 20/20.
- **Publish:** the 7 links are published.
- **Pages:** Giade (Fulani), Itas/Gadau (Hausa, Fulani), Jama'are (Fulani, Kanuri), Kirfi (Hausa) and Warji (Warji) show their peoples with no PHP errors. The Vaghat–Ya–Bijim–Legeri page returns 200.
- **Sitemap:** 3,026, unchanged.
- **QC:**
  - Bauchi: 0 problems; the checks fall from 11 to 5 (the 2 explained name clashes, Katagum, Shira and Damlanci), as predicted.
  - Plateau: the classification checks fall from 2 to 1.
- **Undo:** `fix_080 --revert --apply`, then unpublish and roll back. Every table count, the progress rows and all Glottocodes are identical to the live copy.
- **Deploy order:** import → fix_080 --apply → publish. To undo: revert fix_080 → unpublish → roll back.
