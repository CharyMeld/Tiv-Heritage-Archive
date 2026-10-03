# Research batch 043 — Taraba names and language classification

Researched 2026-09-30, after the Phase 4 check (`NIGERIA_QC_TARABA.md`). Owner approved the plan, with Mambila kept as one record. Created in review; published only after approval.

## What it adds

- **5 other names:**
  - Utur and Eturo for the Etulo language
  - Èédzá and ídzà, the Jenjo people's own names
  - Yandang for the new Yendang record
  All come from Blench's Atlas and are typed by its key, as in batch 036.
- **Southern Bantoid**, a new branch record (Glottolog `sout3152`), so that **Buru** can be placed. The Buru change is in fix_043.
- **Yendang**, a new language record (Glottolog `yend1241`, ISO `ynq`), and the link **Yandang people speak Yendang**. This clears the QC note about the Yandang having no 'speaks' link.
- **fix_043:**
  - **Buru** → Southern Bantoid, with Glottocode `buru1326` and one explanatory sentence
  - **Mambila**: Glottocode `mamb1312`. It is kept as one record, as you chose; a sentence explains Glottolog's split into Western and Donga Mambila.
  - **Gbaya**: Glottocode `nort2775`, marked probable in its text
  - Glottolog notes for **Dirim**, **Joole** and **Kulung (Chadic)**
  - Taraba's status → *quality control in progress*

## Why so few names

The QC listed 26 languages and 8 peoples with no other names. On a hand reading of each Atlas entry:
- most entries have **no name fields at all**
- others give only the people's own name, and we hold no record for those peoples
- the rest (Gbaya's Baya, Dza's Jenjo/Janjo/Jen, Joole's èèʒìì, Kulung (Chadic)'s Wurkum, and Etulo's Utur and Turumawa for the people) are already recorded
The unresolved cases are listed as a gap below.

## New records

### Southern Bantoid

Southern Bantoid is a branch of the Bantoid languages (Benue-Congo). Glottolog (sout3152) places it directly under Bantoid; it includes the Bantu languages. In Taraba State, Roger Blench's Atlas of Nigerian Languages (2020) assigns several languages to it, among them Batu (Southern Bantoid: Tivoid), Bukwen and Mashi (South Bantoid: Beboid), and Buru, which the Atlas leaves unclassified within South Bantoid.

### Yendang

Yendang is an Adamawa language, the language of the Yandang people. Roger Blench's Atlas of Nigerian Languages (2020) gives Yandang as another name for Yendang, and in its classification places Yendang in the Yendang group of the Mumuye–Yendang group of Adamawa languages, alongside Waka and Yoti, with Maya (Bali), Kpasham and Teme in the same group. The Atlas has no Taraba State entry for the language.

Glottolog lists Yendang (yend1241, ISO 639-3 code ynq) in Central Adamawa, within the Mumuye-Yandang group (Yandangic > Waka-Yendang-Teme > Waka-Yandang), which agrees with the Atlas. Wikipedia names the Yandang among the main peoples of Taraba State; where they live is recorded on the Yandang people's page.

## Names

| Record | Name | Type | Atlas |
|---|---|---|---|
| languages:etulo | Utur | spelling_variant | Blench's Atlas (2020), entry 129 Etulo, field 1.A: alternate spelling of the name. |
| languages:etulo | Eturo | spelling_variant | Blench's Atlas (2020), entry 129 Etulo, field 1.A: alternate spelling of the name. |
| ethnic_groups:jenjo | Èédzá | endonym | Blench's Atlas (2020), entry 100 Dza (the Jenjo's language), field 1.C: the people's own name for themselves. |
| ethnic_groups:jenjo | ídzà | endonym | Blench's Atlas (2020), entry 100 Dza (the Jenjo's language), field 1.C: the people's own name for themselves. |
| yendang | Yandang | alternative | Blench's Atlas (2020), index: 'Yandang = Yendang'. |

## Research gaps

- **Dampar: Glottocode.** No Glottolog entry under Dampar (Atlas: Jukunoid, Kororofa cluster, Dampar in Wukari LGA).
- **Joole and Jaule.** Glottolog has no Joole; its nearest name is Jaule, a dialect of Dza (jaul1239) in the Jen group. The Atlas treats Joole as a separate Jen language. Not matched without a source that equates them.
- **Kulung (Chadic): Glottocode.** No Glottolog entry; the Atlas relates it to Piya (Glottolog Piya-Kwonci, piya1245). Glottolog's Kulung (Nigeria) (kulu1255) is the Jarawan Kulung, a separate record here.
- **Dirim: Glottocode.** Glottolog has only the Dirim-Nnakenyare group (diri1260) in Dakoid; the Atlas doubts Dirim is separate from Samba Daka.
- **Hausa and Fulani: language links.** No national Hausa or Fulfulde (Fula) language record exists yet; the 'speaks' links wait for them.
- **Other names without an Atlas entry.** Ambo, Batu, Bete, Bukwen, Dong, Jibu, Kapya, Ligri, Mashi, Mumuye, Ndunda, Pangseng, Rang, Tep and Tha have no name fields in their Atlas entries; Akum, Kam, Lamja-Deŋsa-Tola, Limbum, Naki, Nyam and Dirim have only the people's own name (field 1.C), and there are no records for those peoples. Other sources needed.
## Test on a copy of the live database (30 Sep 2026)

- Import: 2 records, 5 names, 1 link, 6 gaps. 5 new Glottolog sources; the Atlas and the Glottolog index were reused.
- fix_043: 7 changes. Re-running it changes nothing.
- Published. The Taraba QC then showed **0 problems and 4 checks, down from 8**: Dampar, Dirim, Joole and Kulung (Chadic), which are real gaps. The Yandang 'speaks' note is cleared.
- Pages: the new Southern Bantoid and Yendang pages, and the Buru, Mambila, Gbaya, Yandang, Jenjo and Etulo pages, all return 200. All are noindex, since they are under 300 words, as designed. The sitemap is unchanged at 3,025.
- Undo (fix revert, then unpublish, then rollback): every touched table matches a fresh copy of live, apart from the 2 publish audit rows in activity_log.
