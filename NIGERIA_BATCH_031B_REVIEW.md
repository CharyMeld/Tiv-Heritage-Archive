# Research batch 031b — Nasarawa: the peoples

Researched 2026-09-26. Created in review; published only after your approval.

**Sources:**
- the Nasarawa State Government's list of the state's peoples (Tier 1)
- Wikipedia's account of where they live, including its table of **languages by current LGA from Ethnologue**
- Blench's Atlas (batch 031) to check agreement

- **21 new people records**: the 19 peoples the state names, Rindre (named by Wikipedia), and the Yoruba (named by the state as settlers). Each is linked to Nasarawa State and, where known, to its language.
- **82 people–LGA links**, one wherever Ethnologue lists the people's language in that LGA:
  - *well documented* where the Atlas agrees
  - *reported* from Ethnologue alone
  - all marked **presence only** (settlement status unknown), because a language list doesn't say who is indigenous
- **43 new language–LGA links** from Ethnologue. Many use **today's** LGAs: Rindre, Ninzo, Alumu-Tesu, Toro and Buh in Wamba, which fixes the Atlas's older 'Akwanga'.
- **Settlers:** the state government calls the Igbo and Yoruba settler groups, so they are marked *migrant community* at state level.
- Every one of the 13 LGAs now has peoples linked (before: only Lafia).
- All records are short, so they stay noindex (sitemap unchanged).

## People–LGA coverage

| LGA | Peoples linked (from Ethnologue's language list) |
|---|---|
| Akwanga | Eggon, Fulani, Mada, Ninzam, Hausa |
| Awe | Gwandara, Eggon, Migili, Fulani, Tiv, Hausa, Jukun |
| Doma | Alago, Eggon, Fulani, Tiv |
| Keffi | Gwandara, Eggon, Gbagyi, Fulani, Gade, Mada, Hausa |
| Karu | Gwandara, Gbagyi, Gade, Mada |
| Keana | Gwandara, Alago, Fulani, Tiv, Hausa |
| Kokona | Gwandara, Eggon, Afo, Mada, Ninzam |
| Lafia | Gwandara, Alago, Eggon, Migili, Fulani, Mada, Tiv, Hausa, Jukun |
| Nasarawa | Gwandara, Alago, Eggon, Gbagyi, Ebira, Fulani, Afo, Gade, Mada, Basa, Tiv, Hausa |
| Nasarawa Eggon | Eggon, Fulani, Mada, Hausa |
| Obi | Gwandara, Alago, Eggon, Migili, Tiv |
| Toto | Eggon, Gbagyi, Ebira, Fulani, Gade, Mada, Hausa |
| Wamba | Eggon, Kantana, Fulani, Mada, Ninzam, Buh, Arum, Rindre, Hausa |

## Example texts

> The Eggon are one of the peoples of Nasarawa State, named by the state government in its list of the state's peoples. Wikipedia places them in the north of the state. Ethnologue lists their language in Akwanga, Awe, Doma, Keffi, Kokona, Lafia, Nasarawa, Nasarawa Eggon, Obi, Toto and Wamba LGAs.
> The Migili are one of the peoples of Nasarawa State, named by the state government in its list of the state's peoples. Wikipedia places them in the east of the state. Other names include Megili, Lijili; Koro of Lafia. Wikipedia's list of major groups calls them Migili (Koro). Ethnologue lists their language in Awe, Lafia and Obi LGAs.
> The Afo are one of the peoples of Nasarawa State, named by the state government in its list of the state's peoples. Wikipedia places them in the south of the state. Other names include Eloyi, Ajiri. Wikipedia calls them Eloyi (Ajiri/Afo). Ethnologue lists their language in Kokona and Nasarawa LGAs.
> The Fulani are one of the peoples of Nasarawa State, named by the state government in its list of the state's peoples. Wikipedia describes them, with the Hausa, as living throughout the state. Other names include Fulbe. Ethnologue lists their language in Akwanga, Awe, Doma, Keffi, Keana, Lafia, Nasarawa, Nasarawa Eggon, Toto and Wamba LGAs.

## Not linked

- Agatu (a classification question; see the gaps), Kofyar, Wapan as a people, 'Bare-Bari', 'Koro Wachi'.

## Research gaps

- **Agatu in Nasarawa.** The state government lists the Agatu separately; batch 023 records Agatu as an Idoma area of Benue. Ethnologue lists Agatu in Doma, Lafia, Nasarawa and Toto LGAs. Their record and classification need a decision.
- **Who is indigenous where.** All LGA links are presence only (settlement status unknown). Indigeneity by LGA needs state or ethnographic sources, handled neutrally.
- **Kanuri, Kulere, Nyankpa and Koro: LGAs.** Named by the state but not placed in LGAs by the sources used. 'Bare-Bari' in Lafia (Ethnologue) may be the Kanuri (Beriberi) — not assumed.
- **Kofyar, Wapan, Koro Wachi.** Listed by Ethnologue in Lafia, Awe and Keffi; not recorded as peoples here.
- **Descriptions of the peoples.** Each record is short; histories, institutions and cultures come in later batches.
## Tests (copy of the live database, MySQL 8.0.46)

- **Import:** the dry run is clean (3 reused sources, 21 people records, 162 links, 5 gaps).
- **Pages after publishing on the copy:**
  - The Ethnic Groups list and the Eggon, Migili, Yoruba, Wamba and Nasarawa State pages load.
  - Wamba shows 9 peoples, including Rindre, Arum, Buh, Ninzam, Mada and Kantana.
  - The sitemap is **unchanged at 3,023**, and there are 0 PHP errors.
- **Quality check (Nasarawa):**
  - 0 errors.
  - "LGA with no ethnic group linked" falls **from 12 to 0**. Every Nasarawa LGA now has peoples.
  - The rest are notes: no other names yet for many new peoples, and short LGA texts (the LGA-profile batch comes later).
- **Full undo:** the rollback gives a database identical to a fresh copy of the live one.
- **No code changes; no changes to existing records** (the Hausa, Tiv, Jukun and Igbo links are new links).

## Deploy (on your approval)

1. Back up the database.
2. Import (dry run, then apply).
3. Publish the batch.
4. Clear the cache.
5. Check the pages, the sitemap (3,023) and the error log.
6. Re-run the Nasarawa quality check.
