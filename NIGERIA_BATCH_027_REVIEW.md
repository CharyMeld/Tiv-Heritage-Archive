# Research batch 027 — Benue culture (3): the Akweya, Ufia and Nyifon

Researched 2026-09-26. Phase 3, gap G-05, part 3. Fills **empty** description fields only; nothing already written changes. No new records and no fix script.

- Akweya: 153 words · Ufia: 131 · Nyifon: 99 · Akpa language: 37. All stay under 300 words (noindex); sitemap unchanged.
- The sources for these small peoples are thin (community site, a news feature, an opinion article, Glottolog, Wikipedia), so every statement is attributed, and origins are left as gaps.

## Akweya

> The Akweya are a people of the Idoma area of Benue State. According to akweya.com, a community website, they call themselves Akweya, "sons of Akwu", while outsiders call them Akpa, a name the site explains as a distortion of a word meaning "people from Apa" and which government has used officially since 1950.
>
> The same site places them in Akpa District of Otukpo Local Government Area. The district borders Otukpo to the north, Oglewu in Ohimini to the north-west, Edumoga in Okpokwu to the west, Obi and the Igede to the east, and the Ufia of Utonkon in Ado to the south, and the Ohmenyi (Okpokwu) river runs through it.
>
> Their language, Akpa, is classified by Glottolog as an Idomoid language of the Yatye–Akpa group, and Wikipedia places it in Ohimini and Otukpo local government areas. Accounts of Akweya origins found online differ and are not yet supported by reliable sources.

## Ufia (Utonkon)

> The Ufia, also called Utonkon, live at Utonkon in Ado Local Government Area of Benue State. Pulse (2025) describes them as a small minority with their own dialect, rites of passage, marriage customs and music. Their language belongs to the Oring (Korring) cluster of the Upper Cross River languages, not to the Idomoid group to which Idoma belongs.
>
> Whether the Ufia are Idoma is debated. In a 2021 opinion article in National Record, Adakole Ijogi argued that the answer depends on what "Idoma" means: by language the Ufia are not Idoma, but as members of the wider Idoma people they are. He also wrote that the Ufia came from northern Cross River State through Ebonyi State in the early sixteenth century. This is one writer's account and has not been confirmed.

## Nyifon

> The Nyifon live in a small part of Buruku Local Government Area, Benue State; Pulse (2025) places them mostly near the Benue River. Their language, also called Iordaa, is little documented. Glottolog classifies Nyifon as a dialect of Wapan, the Jukun language of Wukari, within the Jukunoid group, whereas other listings, and this archive, treat it as a language of its own. Wikipedia records an estimate of about 1,000 speakers in the 1990s, and Pulse describes the language as endangered, spoken by a few thousand people. No reliable account of Nyifon history or customs has been found yet.

## Akpa language

> Akpa, also called Akweya, is the language of the Akweya people of Benue State. Glottolog classifies it as an Idomoid language of the Yatye–Akpa group, and Wikipedia places it in Ohimini and Otukpo local government areas.

## Sources

- **AKW** — About Akweya People (akweya.com (AkweyaTV)). https://www.akweya.com/p/about-akweya-people.html. Tier 4.
- **GLAKPA** — Akpa (akpa1238) (Glottolog). https://glottolog.org/resource/languoid/id/akpa1238. Tier 2.
- **GLNYI** — Nyifon (nyif1234) (Glottolog). https://glottolog.org/resource/languoid/id/nyif1234. Tier 2.
- **WNYI** — Nyifon language (Wikipedia). https://en.wikipedia.org/wiki/Nyifon_language. Tier 3.
- **WAKPA** — Akpa language (Wikipedia). https://en.wikipedia.org/wiki/Akpa_language. Tier 3.
- **PULSE** — Benue State Tribes: Meet The Indigenous Groups (Anna Ajayi, 2025-06-17). https://www.pulse.ng/articles/lifestyle/tribes-in-benue-state-2025061717333829037. Tier 3.
- **NR** — DEBATE: Ufia People are NOT Idoma (Adakole Ijogi, 2021-03-06). https://www.nationalrecord.com.ng/debate-ufia-people-are-not-idoma/. Tier 4.

## Not used

- Blog accounts of Akweya and Ufia migrations (unsourced).
- Pulse's placing of Benue's Jukun 'in border areas like Wukari' (Wukari is in Taraba).

## Research gaps

- **Nyifon: language or dialect.** Glottolog classes Nyifon as a dialect of Wapan (Jukun of Wukari); the archive and other listings treat it as a language. Needs a linguistic study (e.g. Blench's Jukunoid work).
- **Akweya and Ufia origins.** Online accounts (migration from Cross River in the 16th century; links to Kwararafa, Igala, Igbo) are unsourced or one writer's view.
- **Akweya traditional institutions.** The Akweya ruler and his place under the Idoma Area Traditional Council are not documented.
- **Jukun in Benue.** Beyond the Abinsi (Wannu) community already recorded, no reliable source was found. Pulse's 'Wukari' is in Taraba.
- **Culture of the Akweya, Ufia and Nyifon.** Festivals, food, dress and crafts: no reliable source found yet.
## Tests (copy of the live database, MySQL 8.0.46)

- **Import:**
  - The dry run is clean: 5 new sources, 2 reused (the two Glottolog entries already existed), 4 filled fields and 5 gaps.
  - The Akweya, Ufia, Nyifon and Akpa pages all load with the new text.
- **Sitemap:** unchanged at 3,023. All four pages stay under 300 words, so they stay noindex.
- **Errors:** 0 PHP errors.
- **Full undo:** the rollback empties the four fields and removes the 5 new sources and the gaps, returning the exact starting state.
- **A note on testing:** a first test run was invalid because the test database wasn't ready when the data loaded. It was repeated with a readiness check, and the results above are from the repeated run.

## Deploy (on your approval)

1. Back up the database.
2. Import (dry run, then apply).
3. Clear the cache.
4. Check the pages, the sitemap (3,023) and the error log.

Nothing needs publishing, because only existing published records are filled.
