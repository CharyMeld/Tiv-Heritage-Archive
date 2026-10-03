# Plateau State — Phase 4 quality control

Run 30 September 2026 on the **live** database, read-only (nothing was changed), after batches 044–049. The script is `database/research/qc_state.php plateau`, the same one used for Benue, Nasarawa and Taraba.

## Verdict

- **No errors found (0 problems).** No duplicates, no records in the wrong place, no unsourced or over-graded claims, and no source-quality issues.
- **Coverage went from almost nothing to full LGA coverage in one day.**

| | Before (baseline, 30 Sep morning) | Now |
|---|---|---|
| Ethnic groups linked | 1 (Tiv) | **33** (26 new, plus Kulere, Jukun, Tiv and the Hausa, Idoma, Igbo and Yoruba as migrant communities) |
| Languages linked | 12 (Tiv and its dialects) | **60** (43 new, plus Goemai, Hone and Wapan) |
| Traditional institutions | 2 | **15**: the Council of Chiefs and Emirs, five first-class stools, the Ponzhi Tarok and the Wase Emirate |
| Cultural records | 0 | **15** (12 festivals, 10 of them from the state government's official list) |
| Places | 2 | **27**: the Jos museums, the declared and proposed national monuments, the Shere Hills and 16 headquarters towns |
| Wards | 0 | **207** (INEC, 2015) |
| Links | 15 | **259** |
| LGAs with peoples linked | 4 | **17 of 17** |
| LGAs with a description and headquarters | 0 | **17 of 17** |

- **Two classification checks.** Neither can be closed with the sources available:
  - **Shagawu** has no Glottolog entry.
  - **Vaghat–Ya–Bijim–Legeri** is a family in Glottolog, not a single language.
- **Info notes:**
  1. **LGAs not linked to a council.** All 17 LGAs show "not linked to any traditional council". The stools are linked to their LGAs (Mangu, Pankshin and Kanke, Shendam, Qua'an Pan, Langtang North, Wase and Jos North), but there are no records for the joint and local councils: the Jos Joint, Langtang Joint, Mangu and Shendam traditional councils. Those councils are named in sources but not yet described.
  2. **Contradictory figures, all kept side by side as the rules require:**
     - 15 LGAs, where Statoids' areas differ from the state government's 2022 booklet (for example Bokkos 1,682 and 3,053 km²; Jos East 1,020 and 2,540 km²)
     - the state's own population and area figures
  3. **Missing other names.** 14 peoples and 4 languages have no other names recorded. Sources already read give some: "Taroh" for the Tarok (state government), and "Ankwe" and "Yergam" as language names in the Atlas.
  4. **Short LGA text.** Mikang has 95 words; no further sourced material was found.
  5. **Items that belong to other states.** Puje, the Tor Tiv Palace and open disputes #2, #4 and #6 appear only through links shared with Benue and Taraba records. They are not Plateau issues.
- **Corrections made during the Plateau work:**
  - **fix_047:** the NCMM source #559 pointed at the museum contacts page, and was re-pointed to the real list of declared monuments. The conclusion drawn from it for Benue and Taraba was unchanged.
  - **fix_048:** removed my own unsourced sentence on Mikang's origin from the Youm record.
- **Is Plateau complete?** Not yet. By the brief's rule it now moves to *quality control in progress*, like Benue, Nasarawa and Taraba. Its progress record on live still reads "not started, 0/17 LGAs researched", which is out of date and should be corrected.

## Proposals (each needs your approval)

1. **Batch 050: Plateau names and progress (small; from sources already read):**
   - other names: Taroh (Tarok; state government), and the Atlas's Plateau name fields not yet used for peoples (for example Kofyar for the Pan speakers, Ankwe for the Goemai language)
   - a fix setting Plateau's research progress to 17/17 LGAs researched and *quality control in progress*, with a summary of batches 044–049
2. **Later, when sources are found:**
   - the joint and local traditional councils (Jos Joint, Langtang Joint, Mangu, Shendam) and the remaining paramount stools (Afizere, Anaguta, Irigwe, Ron, Montol, Tal and others)
   - whether Taroh Cultural Day and Ilum Otarok are the same festival
   - the NCMM's locations for the four proposed monuments
   - the Ponzhi Tarok's status after May 2025
   - LGA creation dates
3. **Then the next state.** The remaining Middle Belt neighbours are Kogi and Adamawa; Bauchi borders Plateau to the north.

## Full report (as generated)

Read-only check run 30 September 2026, 15:16. Nothing was changed.

**Scope:** the state, its 17 LGAs and 207 wards, and every record linked to them: 33 ethnic groups, 60 languages, 15 polities, 15 cultural records, 27 places, 259 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 18 |
| Geographic consistency | 0 | 0 | 19 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 20 |
| Missing LGAs / coverage | 0 | 0 | 1 |
| Classification problems | 0 | 2 | 7 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Anaguta (ethnic group #115, ETH-ANAGUTA) has no other names recorded.
- **info**: Bijim (ethnic group #137, ETH-BIJIM) has no other names recorded.
- **info**: Goemai (ethnic group #122, ETH-GOEMAI) has no other names recorded.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no other names recorded.
- **info**: Jipal (ethnic group #124, ETH-JIPAL) has no other names recorded.
- **info**: Kadung (ethnic group #138, ETH-KADUNG) has no other names recorded.
- **info**: Kofyar (ethnic group #125, ETH-KOFYAR) has no other names recorded.
- **info**: Kulere (ethnic group #62, ETH-KULERE) has no other names recorded.
- **info**: Miship (ethnic group #126, ETH-MISHIP) has no other names recorded.
- **info**: Montol (ethnic group #127, ETH-MONTOL) has no other names recorded.
- **info**: Mupun (ethnic group #128, ETH-MUPUN) has no other names recorded.
- **info**: Mushere (ethnic group #129, ETH-MUSHERE) has no other names recorded.
- **info**: Tarok (ethnic group #135, ETH-TAROK) has no other names recorded.
- **info**: Yoruba (ethnic group #64, ETH-YORUBA) has no other names recorded.
- **info**: Akpondu (language #318, LANG-AKPONDU) has no other names recorded.
- **info**: Mundat (language #340, LANG-MUNDAT) has no other names recorded.
- **info**: Pan (language #343, LANG-PAN) has no other names recorded.
- **info**: Sha (language #349, LANG-SHA) has no other names recorded.

## Geographic consistency

- **info**: Puje (place #162, PLACE-PUJE) is located in Taraba State, outside this state (linked through a related record).
- **info**: Tor Tiv Palace (place #82, PLACE-TOR-TIV-PALACE) is located in Gboko, outside this state (linked through a related record).
- **info**: Barkin Ladi is not linked to any traditional council or intermediate area.
- **info**: Bassa is not linked to any traditional council or intermediate area.
- **info**: Bokkos is not linked to any traditional council or intermediate area.
- **info**: Jos East is not linked to any traditional council or intermediate area.
- **info**: Jos North is not linked to any traditional council or intermediate area.
- **info**: Jos South is not linked to any traditional council or intermediate area.
- **info**: Kanam is not linked to any traditional council or intermediate area.
- **info**: Kanke is not linked to any traditional council or intermediate area.
- **info**: Langtang North is not linked to any traditional council or intermediate area.
- **info**: Langtang South is not linked to any traditional council or intermediate area.
- **info**: Mangu is not linked to any traditional council or intermediate area.
- **info**: Mikang is not linked to any traditional council or intermediate area.
- **info**: Pankshin is not linked to any traditional council or intermediate area.
- **info**: Qua'an-Pan is not linked to any traditional council or intermediate area.
- **info**: Riyom is not linked to any traditional council or intermediate area.
- **info**: Shendam is not linked to any traditional council or intermediate area.
- **info**: Wase is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Plateau State: 2 different population figures for 2006 (3178712,3206531). All are kept side by side, as the rules require.
- **info**: Plateau State: 2 different area_km2 figures for no year (26539,27147). All are kept side by side, as the rules require.
- **info**: Barkin Ladi: 2 different area_km2 figures for no year (1032,1312). All are kept side by side, as the rules require.
- **info**: Bassa: 2 different area_km2 figures for no year (1743,1776). All are kept side by side, as the rules require.
- **info**: Bokkos: 2 different area_km2 figures for no year (1682,3053). All are kept side by side, as the rules require.
- **info**: Jos East: 2 different area_km2 figures for no year (1020,2540). All are kept side by side, as the rules require.
- **info**: Jos North: 2 different area_km2 figures for no year (291,650). All are kept side by side, as the rules require.
- **info**: Kanam: 2 different area_km2 figures for no year (2600,2788). All are kept side by side, as the rules require.
- **info**: Kanke: 2 different area_km2 figures for no year (926,1000). All are kept side by side, as the rules require.
- **info**: Langtang North: 2 different area_km2 figures for no year (1188,2440). All are kept side by side, as the rules require.
- **info**: Langtang South: 2 different area_km2 figures for no year (838,1250). All are kept side by side, as the rules require.
- **info**: Mangu: 2 different area_km2 figures for no year (1588,1653). All are kept side by side, as the rules require.
- **info**: Mikang: 2 different area_km2 figures for no year (630,739). All are kept side by side, as the rules require.
- **info**: Pankshin: 2 different area_km2 figures for no year (1334,1524). All are kept side by side, as the rules require.
- **info**: Qua'an-Pan: 2 different area_km2 figures for no year (2478,2688). All are kept side by side, as the rules require.
- **info**: Shendam: 2 different area_km2 figures for no year (2437,2477). All are kept side by side, as the rules require.
- **info**: Wase: 2 different area_km2 figures for no year (4306,5031). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).

## Missing LGAs / coverage

- **info**: Mikang has only 95 words of text (no LGA history yet).

## Classification problems

- **check**: Shagawu (language #350, LANG-SHAGAWU) has no Glottocode.
- **check**: Vaghat–Ya–Bijim–Legeri (language #356, LANG-VAGHAT-YA-BIJIM-LEGERI) has no Glottocode.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no 'speaks' link to a language.
- **info**: Igbo (ethnic group #21, ETH-IGBO) has no 'speaks' link to a language.
- **info**: Yoruba (ethnic group #64, ETH-YORUBA) has no 'speaks' link to a language.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
