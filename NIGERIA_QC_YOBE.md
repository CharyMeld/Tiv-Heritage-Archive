# Yobe State — Phase 4 quality control

Run 1 October 2026 on the **live** database, read-only (nothing was changed), after batches 069–073. The script is `database/research/qc_state.php yobe`.

## Verdict

- **No errors found (0 problems)** and **no classification checks**.
- **Coverage went from nothing to near-full in one day:**

| | Before (baseline, 1 Oct) | Now |
|---|---|---|
| Ethnic groups linked | 0 | **12**: 6 new (Karekare, Bolewa, Ngizim, Bade, Ngamo, Manga) plus Kanuri, Fulani, Hausa, Shuwa Arabs, Bura and Marghi |
| Languages linked | 0 | **12**: 10 new (Bade, Ɗuwai, Ngizim, Bole, Ngamo, Karekare, Kutto, Maaka, Uled Suliman and Baggara Arabic), plus Kanuri and Shuwa Arabic |
| Traditional institutions | 0 | **20**: the 12 Yobe emirates, plus 8 linked through other states' records |
| Cultural records | 0 | **12**: the Bade Fishing Festival, plus 11 linked through the Hausa and Fulani records of other states |
| Places | 1 (Damaturu) | **26**: four proposed monuments (Ngazargamu, Dufuna, Goya Gorge, Dagona), the National Museum Damaturu, the Hadejia-Nguru Wetlands, Chad Basin National Park, 16 headquarters towns, and 3 Adamawa places linked through shared records |
| Wards | 0 | **178** (INEC, 2015) |
| Links | 5 | **89** |
| LGAs with peoples linked | 0 | **16 of 17** |
| LGAs with a description and headquarters | 0 | **17 of 17** |

- **One check:** Tarmua has no people linked. Wikipedia's language table skips it, and no other source read names its peoples. It remains a gap.
- **Info notes:**
  1. **Missing other names.** Bade, Fulani, Hausa, Manga, Ngamo and Ngizim have none. The Atlas gives other names only for their languages (Bedde, Gamo, Ngezzim), not for the peoples, so nothing can be added from the sources already read.
  2. **LGAs not linked to a council.** This is the same pattern as for the other states. The emirates are linked to their seat LGAs.
  3. **Short LGA texts.** 10 LGAs have under 100 words.
  4. **Contradictory figures.** Two state population figures for 2006 and two state areas are kept side by side.
  5. **Hausa has no "speaks" link.** This is a national gap.
  6. **Items from other states.** Chad Basin National Park (recorded under Borno, with a Yobe link) and three Adamawa places appear through shared links.
- **Corrections made during the Yobe work:**
  - The Atlas writes "Borno State" for Bade, Fika and Damaturu; these were linked to Yobe, quoting the Atlas.
  - INEC's "Tarmuwa" was mapped to the archive's Tarmua, and the LGA was not renamed.
  - "Ngazargamu takes its name from the old capital" was rewritten to what the NCMM says.
  - Conflicting figures were noted, not used.
- **Is Yobe complete?** Not yet. By the brief's rule it now moves to *quality control in progress*. Its progress record on live still reads "not_started, 0/17".

## Proposal: fix_074, Yobe progress

- Sets Yobe's research progress to **17/17 LGAs researched, quality control in progress**, with a summary of batches 069–073. No names batch is needed, because no new other names are available from the sources read.

## Later, when sources are found

- the peoples of Tarmua
- Yobe festivals, beyond the one-line Bade fishing festival
- Ɗuwai, Maaka and Kutto people records
- the present rulers and grades of the emirates; Tikau's successor
- whether Machina is an emirate, and which four emirates existed in 1991
- the Nangere and Yunusari headquarters
- LGA creation dates

## Full report (as generated)

Read-only check run 1 October 2026, 12:04. Nothing was changed.

**Scope:** the state, its 17 LGAs and 178 wards, and every record linked to them: 12 ethnic groups, 12 languages, 20 polities, 12 cultural records, 26 places, 89 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 6 |
| Geographic consistency | 0 | 0 | 20 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 5 |
| Missing LGAs / coverage | 0 | 1 | 10 |
| Classification problems | 0 | 0 | 5 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Bade (ethnic group #250, ETH-BADE) has no other names recorded.
- **info**: Fulani (ethnic group #51, ETH-FULANI) has no other names recorded.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no other names recorded.
- **info**: Manga (ethnic group #252, ETH-MANGA) has no other names recorded.
- **info**: Ngamo (ethnic group #251, ETH-NGAMO) has no other names recorded.
- **info**: Ngizim (ethnic group #249, ETH-NGIZIM) has no other names recorded.

## Geographic consistency

- **info**: Chad Basin National Park (place #379, PLACE-CHAD-BASIN-NATIONAL-PARK) is located in Borno State, outside this state (linked through a related record).
- **info**: Hamayaji Old Palace (place #319, PLACE-HAMAYAJI-OLD-PALACE) is located in Madagali, outside this state (linked through a related record).
- **info**: National Museum Fombina (place #314, PLACE-NATIONAL-MUSEUM-FOMBINA) is located in Yola South, outside this state (linked through a related record).
- **info**: Bade is not linked to any traditional council or intermediate area.
- **info**: Bursari is not linked to any traditional council or intermediate area.
- **info**: Damaturu is not linked to any traditional council or intermediate area.
- **info**: Fika is not linked to any traditional council or intermediate area.
- **info**: Fune is not linked to any traditional council or intermediate area.
- **info**: Geidam is not linked to any traditional council or intermediate area.
- **info**: Gujba is not linked to any traditional council or intermediate area.
- **info**: Gulani is not linked to any traditional council or intermediate area.
- **info**: Jakusko is not linked to any traditional council or intermediate area.
- **info**: Karasuwa is not linked to any traditional council or intermediate area.
- **info**: Machina is not linked to any traditional council or intermediate area.
- **info**: Nangere is not linked to any traditional council or intermediate area.
- **info**: Nguru is not linked to any traditional council or intermediate area.
- **info**: Potiskum is not linked to any traditional council or intermediate area.
- **info**: Tarmua is not linked to any traditional council or intermediate area.
- **info**: Yunusari is not linked to any traditional council or intermediate area.
- **info**: Yusufari is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Yobe State: 2 different population figures for 2006 (2321339,2321591). All are kept side by side, as the rules require.
- **info**: Yobe State: 2 different area_km2 figures for no year (44880,46609). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).

## Missing LGAs / coverage

- **check**: Tarmua has no ethnic group linked to it.
- **info**: Bursari has only 79 words of text (no LGA history yet).
- **info**: Damaturu has only 89 words of text (no LGA history yet).
- **info**: Gujba has only 89 words of text (no LGA history yet).
- **info**: Gulani has only 87 words of text (no LGA history yet).
- **info**: Jakusko has only 77 words of text (no LGA history yet).
- **info**: Karasuwa has only 86 words of text (no LGA history yet).
- **info**: Machina has only 90 words of text (no LGA history yet).
- **info**: Nangere has only 72 words of text (no LGA history yet).
- **info**: Tarmua has only 93 words of text (no LGA history yet).
- **info**: Yusufari has only 74 words of text (no LGA history yet).

## Classification problems

- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no 'speaks' link to a language.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
