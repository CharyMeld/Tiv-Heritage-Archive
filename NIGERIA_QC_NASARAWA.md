# Nasarawa State — Phase 4 quality control

Run 26 September 2026 on the **live** database, read-only (nothing was changed), after batches 031–035. The script is `database/research/qc_state.php nasarawa`, the same one used for Benue.

## Verdict

- **No errors found (0 problems).** No duplicates, no records in the wrong place, no unsourced or over-graded claims, and no source-quality issues. Contradictory figures (3 state population figures for 2006, 2 area figures) are kept side by side, as the rules require.
- **Coverage is much better than at the baseline.** Before batch 031, only 3 groups were linked and 12 of the 13 LGAs had no people linked to them. Now:
  - 26 ethnic groups, 41 languages, 9 kingdoms and councils, 3 cultural records, 15 places and 230 links
  - 147 wards
  - headquarters for 12 of the 13 LGAs
- **Four classification items to fix (the "checks"):**
  - **Mama** has no family. Both Blench's Atlas ("Benue–Congo: Bantu: Jarawan") and Glottolog (Southern Bantoid > Jarawan > Nigerian Jarawan > Jar, `mama1272`) place it in **Jarawan Bantoid**, so this was simply missed in batch 031.
  - **Eloyi** has no Glottocode. Glottolog lists it as "Ajiri" (`eloy1241`, ISO afo) directly under Benue-Congo Plateau. Our record says Idomoid, but the Atlas itself says "Plateau **or** Idomoid". The record should state both views instead of only one. (The Afo ruler's title in the Daily Trust list is "Osu Ajiri", which fits.)
  - **Idun** has no Glottocode. Glottolog has it as Dũya (`idun1241`, ISO ldb), in the Koroic group of Plateau.
  - **Karfa** has no Glottocode. There is no Glottolog entry under this name; the Atlas gives "Kerifa" and places it in Chadic, West A, Ron group. Recorded as a gap unless a code turns up.
- **Coverage gaps (the "info" items):**
  1. **No other names** are recorded for 22 ethnic groups and 25 languages, for example Gbagyi/Gwari, Mama/Kantana/Kwarra and Eloyi/Afo. The Atlas lists these names, and its text is already saved in `database/research/data/atlas_nasarawa_blocks.txt`.
  2. **No 'speaks' link** for 9 groups. Four are Nasarawa peoples whose languages need matching (Basa, Koro, Kulere, Nyankpa); five are the widespread Hausa, Fulani, Kanuri, Yoruba and Igbo.
  3. **No LGA is linked to a traditional council.** The five stools researched in batch 033 are linked to their towns, not placed under a council. The Nasarawa State Council of Chiefs and the other 17 first-class stools (Daily Trust 2012) are not yet researched.
  4. **Short LGA texts:** 8 LGAs have under 100 words, and no LGA has a creation date or history. No source for creation dates has been found yet.
  5. **Karu headquarters** is open (Karu, New Karu or Mararaba).
- **Is Nasarawa complete?** Not yet. By the brief's rule it can move to *quality control in progress*, the status Benue got after its QC, while the gaps below are worked through.

## Proposals (each needs your approval)

1. **Batch 036: Nasarawa names and classification (recommended next; mostly from sources already read):**
   - other names for the groups and languages, from the Atlas (Tier 2) and Glottolog
   - Mama → Jarawan (a new branch record, as Upper Cross was for Benue)
   - Glottocodes for Eloyi (with both classifications recorded) and Idun
   - 'speaks' links for Basa, Koro, Kulere and Nyankpa where the Atlas matches a language record
   - a fix setting Nasarawa's progress status to *quality control in progress*
2. **Batch 037: traditional institutions, part 2:** the Nasarawa State Council of Chiefs and its law, the remaining first-class stools (Aren Eggon, Emir of Awe, Osu Ajiri, She Migili, Chun Mada and others), and links from each LGA to its traditional authority. This depends on finding sources beyond Daily Trust 2012.
3. **Later, lower priority:**
   - LGA creation dates and histories (need the state or federal record)
   - culture and heritage, part 2 (the Eggon Anzhili festival, Ogiri of Agwatashi, Mada, Migili)
   - Karu headquarters (needs an official state list)
4. **Then the next state** (suggested: Taraba), with Nasarawa's open items carried forward, as Benue's were.

## Full report (as generated)

Read-only check run 26 September 2026, 10:21. Nothing was changed.

**Scope:** the state, its 13 LGAs and 147 wards, and every record linked to them: 26 ethnic groups, 41 languages, 9 polities, 3 cultural records, 15 places, 230 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 47 |
| Geographic consistency | 0 | 0 | 14 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 3 |
| Missing LGAs / coverage | 0 | 0 | 9 |
| Classification problems | 0 | 4 | 13 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Afo (ethnic group #53, ETH-AFO) has no other names recorded.
- **info**: Alago (ethnic group #45, ETH-ALAGO) has no other names recorded.
- **info**: Arum (ethnic group #61, ETH-ARUM) has no other names recorded.
- **info**: Basa (ethnic group #60, ETH-BASA) has no other names recorded.
- **info**: Buh (ethnic group #59, ETH-BUH) has no other names recorded.
- **info**: Ebira (ethnic group #48, ETH-EBIRA) has no other names recorded.
- **info**: Eggon (ethnic group #46, ETH-EGGON) has no other names recorded.
- **info**: Fulani (ethnic group #51, ETH-FULANI) has no other names recorded.
- **info**: Gade (ethnic group #54, ETH-GADE) has no other names recorded.
- **info**: Gbagyi (ethnic group #47, ETH-GBAGYI) has no other names recorded.
- **info**: Gwandara (ethnic group #44, ETH-GWANDARA) has no other names recorded.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no other names recorded.
- **info**: Kantana (ethnic group #50, ETH-KANTANA) has no other names recorded.
- **info**: Kanuri (ethnic group #52, ETH-KANURI) has no other names recorded.
- **info**: Koro (ethnic group #56, ETH-KORO) has no other names recorded.
- **info**: Kulere (ethnic group #62, ETH-KULERE) has no other names recorded.
- **info**: Mada (ethnic group #57, ETH-MADA) has no other names recorded.
- **info**: Migili (ethnic group #49, ETH-MIGILI) has no other names recorded.
- **info**: Ninzam (ethnic group #58, ETH-NINZAM) has no other names recorded.
- **info**: Nyankpa (ethnic group #55, ETH-NYANKPA) has no other names recorded.
- **info**: Rindre (ethnic group #63, ETH-RINDRE) has no other names recorded.
- **info**: Yoruba (ethnic group #64, ETH-YORUBA) has no other names recorded.
- **info**: Ake (language #93, LANG-AKE) has no other names recorded.
- **info**: Alago (language #94, LANG-ALAGO) has no other names recorded.
- **info**: Alumu–Tesu (language #95, LANG-ALUMU-TESU) has no other names recorded.
- **info**: Ashe (language #96, LANG-ASHE) has no other names recorded.
- **info**: Bu-Ningkada (language #97, LANG-BU-NINGKADA) has no other names recorded.
- **info**: Ebira (language #116, LANG-EBIRA) has no other names recorded.
- **info**: Eggon (language #98, LANG-EGGON) has no other names recorded.
- **info**: Eloyi (language #99, LANG-ELOYI) has no other names recorded.
- **info**: Gade (language #100, LANG-GADE) has no other names recorded.
- **info**: Gbagyi (language #101, LANG-GBAGYI) has no other names recorded.
- **info**: Gbari (language #102, LANG-GBARI) has no other names recorded.
- **info**: Goemai (language #103, LANG-GOEMAI) has no other names recorded.
- **info**: Gwandara (language #104, LANG-GWANDARA) has no other names recorded.
- **info**: Hasha (language #105, LANG-HASHA) has no other names recorded.
- **info**: Idoma (language #19, LANG-IDOMA) has no other names recorded.
- **info**: Idun (language #106, LANG-IDUN) has no other names recorded.
- **info**: Jili (Migili) (language #107, LANG-JILI-MIGILI) has no other names recorded.
- **info**: Karfa (language #108, LANG-KARFA) has no other names recorded.
- **info**: Mada (language #109, LANG-MADA) has no other names recorded.
- **info**: Mama (language #110, LANG-MAMA) has no other names recorded.
- **info**: Ninzo (language #111, LANG-NINZO) has no other names recorded.
- **info**: Nko (language #112, LANG-NKO) has no other names recorded.
- **info**: Numbu–Gbantu–Nunku (language #113, LANG-NUMBU-GBANTU-NUNKU) has no other names recorded.
- **info**: Rindre (language #114, LANG-RINDRE) has no other names recorded.
- **info**: Toro (language #115, LANG-TORO) has no other names recorded.

## Geographic consistency

- **info**: Tor Tiv Palace (place #82, PLACE-TOR-TIV-PALACE) is located in Gboko, outside this state (linked through a related record).
- **info**: Akwanga is not linked to any traditional council or intermediate area.
- **info**: Awe is not linked to any traditional council or intermediate area.
- **info**: Doma is not linked to any traditional council or intermediate area.
- **info**: Karu is not linked to any traditional council or intermediate area.
- **info**: Keana is not linked to any traditional council or intermediate area.
- **info**: Keffi is not linked to any traditional council or intermediate area.
- **info**: Kokona is not linked to any traditional council or intermediate area.
- **info**: Lafia is not linked to any traditional council or intermediate area.
- **info**: Nasarawa is not linked to any traditional council or intermediate area.
- **info**: Nasarawa Eggon is not linked to any traditional council or intermediate area.
- **info**: Obi is not linked to any traditional council or intermediate area.
- **info**: Toto is not linked to any traditional council or intermediate area.
- **info**: Wamba is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Nasarawa State: 3 different population figures for 2006 (1826883,1863275,1869377). All are kept side by side, as the rules require.
- **info**: Nasarawa State: 2 different area_km2 figures for no year (26633,26876). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.

## Missing LGAs / coverage

- **info**: Akwanga has only 53 words of text (no LGA history yet).
- **info**: Awe has only 55 words of text (no LGA history yet).
- **info**: Keffi has only 89 words of text (no LGA history yet).
- **info**: Kokona has only 90 words of text (no LGA history yet).
- **info**: Lafia has only 81 words of text (no LGA history yet).
- **info**: Nasarawa has only 84 words of text (no LGA history yet).
- **info**: Nasarawa Eggon has only 85 words of text (no LGA history yet).
- **info**: Obi has only 95 words of text (no LGA history yet).
- **info**: 1 of 13 LGAs have no headquarters recorded.

## Classification problems

- **check**: Eloyi (language #99, LANG-ELOYI) has no Glottocode.
- **check**: Idun (language #106, LANG-IDUN) has no Glottocode.
- **check**: Karfa (language #108, LANG-KARFA) has no Glottocode.
- **check**: Mama (language #110, LANG-MAMA) is not placed in a family or branch.
- **info**: Basa (ethnic group #60, ETH-BASA) has no 'speaks' link to a language.
- **info**: Fulani (ethnic group #51, ETH-FULANI) has no 'speaks' link to a language.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no 'speaks' link to a language.
- **info**: Igbo (ethnic group #21, ETH-IGBO) has no 'speaks' link to a language.
- **info**: Kanuri (ethnic group #52, ETH-KANURI) has no 'speaks' link to a language.
- **info**: Koro (ethnic group #56, ETH-KORO) has no 'speaks' link to a language.
- **info**: Kulere (ethnic group #62, ETH-KULERE) has no 'speaks' link to a language.
- **info**: Nyankpa (ethnic group #55, ETH-NYANKPA) has no 'speaks' link to a language.
- **info**: Yoruba (ethnic group #64, ETH-YORUBA) has no 'speaks' link to a language.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
