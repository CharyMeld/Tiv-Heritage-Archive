# Research batch 069b — Yobe: the peoples

Researched 2026-10-01. Created in review; published only after your approval.

- **6 new people records:** Karekare, Bolewa, Ngizim, Bade, Ngamo, Manga. Each is linked to Yobe and to its language.
- **6 existing records get Yobe links:** Kanuri, Fulani, Hausa, Shuwa Arabs, Bura and Marghi.
- **30 people–LGA links covering 16 of the 17 LGAs:** 8 *well documented* and 22 *reported*. Only **Tarmua** has no people linked, because Wikipedia's table skips it.
- **Sources:**
  - the **federal government's Yobe profile** (official). It names the nine major groups but no LGAs.
  - **Wikipedia's Yobe State article**: its ethnic groups and its languages-by-LGA table
  - each record is checked against Blench's Atlas
- **Manga:** Wikipedia calls them a separate community; the Atlas calls Manga a Kanuri dialect. Recorded as a people who speak Kanuri, with both views noted.
- **Not created:** Ɗuwai, Maaka and Kutto, because only their languages are named.

## People per LGA

| LGA | Peoples |
|---|---|
| Bade | Bade, Kanuri (existing) |
| Bursari | Kanuri (existing) |
| Damaturu | Karekare, Kanuri (existing) |
| Fika | Karekare, Bolewa, Ngamo |
| Fune | Karekare, Ngizim, Bura (existing) |
| Geidam | Kanuri (existing), Fulani (existing) |
| Gujba | Karekare, Kanuri (existing) |
| Gulani | Karekare, Kanuri (existing), Bura (existing) |
| Jakusko | Karekare, Bade, Fulani (existing) |
| Karasuwa | Kanuri (existing) |
| Machina | Manga |
| Nangere | Karekare |
| Nguru | Kanuri (existing) |
| Potiskum | Karekare, Bolewa, Ngizim |
| Tarmua | — |
| Yunusari | Kanuri (existing) |
| Yusufari | Kanuri (existing) |

## The people records

**Karekare.** The Karekare are a people of Yobe State, named among the state's major ethnic groups by the federal government's state profile and by Wikipedia. Other names include Kare-Kare, Karai-karai. Wikipedia names them, with the Kanuri and Fulani, among the major ethnic groups of the state, and its language table places Karai-karai in more LGAs than any other language except Kanuri. Blench's Atlas places the Karekare language in Fika LGA and in Gamawa and Misau LGAs of Bauchi State. Wikipedia's language table places them in Damaturu, Fika, Fune, Gujba, Gulani, Jakusko, Nangere and Potiskum LGAs.

**Bolewa.** The Bolewa are a people of Yobe State, named among the state's major ethnic groups by the federal government's state profile and by Wikipedia. Other names include Am Pìkkà, Ampika, Anika. Their language is Bole, which Blench's Atlas places in Fika LGA and in Bauchi State; the Atlas gives Bolewa and Anika as other names of the people, and Fika as a name of the language. Wikipedia's language table places them in Fika and Potiskum LGAs.

**Ngizim.** The Ngizim are a people of Yobe State, named among the state's major ethnic groups by the federal government's state profile and by Wikipedia. Blench's Atlas places the Ngizim language in Damaturu LGA, describes it as vigorous, and notes that the Ngizim also speak Hausa. Wikipedia's language table places them in Fune and Potiskum LGAs.

**Bade.** The Bade are a people of Yobe State, named among the state's major ethnic groups by the federal government's state profile and by Wikipedia. Blench's Atlas places the Bade language in Bade LGA and in Hadejia LGA of Jigawa State, with three dialects: Western, Southern and Gashua Bade. Wikipedia's language table places them in Bade and Jakusko LGAs.

**Ngamo.** The Ngamo are a people of Yobe State, named among the state's major ethnic groups by the federal government's state profile and by Wikipedia. Blench's Atlas places the Ngamo language in Fika LGA and in Darazo and Dukku LGAs. Wikipedia's language table places them in Fika LGA.

**Manga.** The Manga are a people of Yobe State, named among the state's ethnic communities by Wikipedia. Wikipedia's language table gives Manga as the language of Machina LGA; Blench's Atlas names Manga as a dialect of Kanuri, in which a Bible translation was in progress. Wikipedia's language table places them in Machina LGA.

## Research gaps

- **Yobe: Tarmua.** Wikipedia's language table omits Tarmua LGA, and no other source read names its peoples.
- **Yobe: Ɗuwai, Maaka and Kutto peoples.** Their languages are recorded (batch 069), and Wikipedia's table lists Duwai (Bade LGA) and Maaka (Gulani LGA), but no source read names them as peoples; no people records created.
- **Yobe: Manga and Kanuri.** Wikipedia lists the Manga as a separate ethnic community; Blench's Atlas names Manga as a dialect of Kanuri. Recorded as a people speaking Kanuri; their relation to the Kanuri is not stated.
## Test (1 Oct 2026, on a fresh copy of live, with 068 and 069 in it)

- **Import:** dry run, then apply. Result: 6 people records, 17 source links, 5 names, 48 relations and 3 gaps. 1 source is new (the federal Yobe profile); 2 are reused.
- **Publish:** 6 ethnic groups.
- **Pages:** the Karekare, Bolewa and Manga pages, Yobe State and Potiskum all return 200 with no PHP errors.
- **Sitemap:** 3,026, unchanged.
- **Yobe QC:** 0 problems. There are now 12 ethnic groups in scope. Coverage checks fell from 17 to 1 (only Tarmua). The 8 polities and 11 cultural records in scope come in through the Hausa and Fulani links from other states.
- **Undo:** unpublish, then roll back. Every table count matches the pre-import snapshot except two, both known side effects of the test:
  - activity_log: +6
  - archive_search_index: +6
