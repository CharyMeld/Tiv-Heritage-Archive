# Gombe State — Phase 4 quality control

Run 2 October 2026 on the **live** database, read-only (nothing was changed), after batches 081–085. The script is `database/research/qc_state.php gombe`.

## Verdict

- **No errors found (0 problems).** There is one check, which is expected.
- **Coverage went from almost nothing to complete in one day:**

| | Before (baseline, 2 Oct) | Now |
|---|---|---|
| Ethnic groups linked | 0 | **18**: 10 new (Tangale, Waja, Pero, Tula, Cham, Dadiya, Kamo, Awak, Bangunji, Jara) plus Fulani, Hausa, Kanuri, Bolewa, Jukun, Tera, Lunguda and Tsobo |
| Languages linked | 0 | **22**: 10 new plus 12 existing (Tangale, Dadiya, Dijim–Bwilim, Longuda, Loo, Tsobo, Wiyaa, Tera, Jara, Kutto, Bole, Ngamo) |
| Traditional institutions | 0 | **26**: the 9 emirates and 5 chiefdoms on the state's list, plus 12 linked through other records |
| Cultural records | 0 | **14**: Pissi Tangale, Bai and Kamo festivals, plus 11 linked through the Fulani and Hausa records |
| Places | 1 (Gombe) | **20**: Tula Prison Yard and Mbormi (NCMM proposed), National Museum Gombe, the Emir's Palace, Dadin Kowa Dam, the Muri Mountains, 10 headquarters towns, plus 3 places in other states linked through shared records |
| Wards | 0 | **114** (INEC, 2015) |
| LGAs with peoples linked | 0 | **11 of 11** |
| LGAs with a description and headquarters | 0 | **11 of 11** |

## The check, researched

- **Cen Tuum (#653) is not placed in a family.** This is correct. The Atlas classes it as a language isolate, and Glottolog lists it (as Jalaa, `cent2045`) with no family. No change.

## Info notes

- **Missing other names:**
  - The Fulani and Hausa have none. This is a national gap.
  - The **Tera** have none. The federal profile writes "Terawa", so batch 086 adds it.
  - Jan Awei has none; the Atlas gives none.
- **LGAs not linked to a council:** the same pattern as in the other states. The emirates and chiefdoms are linked to their seat LGAs.
- **Contradictory figures:** two state population figures for 2006 and two state areas are kept side by side.
- **Items from other states:** Elephant House (Guyuk), National Museum Fombina and Puje appear through shared links.
- **Progress record:** the live progress row (#52) still reads "not_started, 0/11".

## Batch 086 + fix_086, Gombe names and progress (built and tested)

- **Batch 086:** "Terawa" is added as another name of the Tera (federal profile).
- **fix_086:** Gombe research progress → **11/11 LGAs researched, quality control in progress**, with a summary of batches 081–086.
- **Expected QC after:** 0 problems and 1 check (Cen Tuum, as explained above); alias notes fall from 4 to 3.

## Later, when sources are found

- a current official list of rulers (Funakaye; Deba and Yamaltu), emirate grades and creation dates
- the Billiri and Dukku headquarters; an INEC Gombe LGA-office list
- peoples for the languages named only in Wikipedia's table (Kushi, Loo, Moo, Kyak, Dera, Dikaka, Dza, Yuwar, Wurkun)
- festival dates and rites; the Tula Prison Yard's history
- LGA creation dates

## Full report (as generated)

Read-only check run 2 October 2026, 09:45. Nothing was changed.

**Scope:** the state, its 11 LGAs and 114 wards, and every record linked to them: 18 ethnic groups, 22 languages, 26 polities, 14 cultural records, 20 places, 116 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 4 |
| Geographic consistency | 0 | 0 | 14 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 5 |
| Missing LGAs / coverage | 0 | 0 | 0 |
| Classification problems | 0 | 1 | 6 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Fulani (ethnic group #51, ETH-FULANI) has no other names recorded.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no other names recorded.
- **info**: Tera (ethnic group #240, ETH-TERA) has no other names recorded.
- **info**: Jan Awei (language #655, LANG-JAN-AWEI) has no other names recorded.

## Geographic consistency

- **info**: Elephant House, Guyuk (place #316, PLACE-ELEPHANT-HOUSE-GUYUK) is located in Guyuk, outside this state (linked through a related record).
- **info**: National Museum Fombina (place #314, PLACE-NATIONAL-MUSEUM-FOMBINA) is located in Yola South, outside this state (linked through a related record).
- **info**: Puje (place #162, PLACE-PUJE) is located in Taraba State, outside this state (linked through a related record).
- **info**: Akko is not linked to any traditional council or intermediate area.
- **info**: Balanga is not linked to any traditional council or intermediate area.
- **info**: Billiri is not linked to any traditional council or intermediate area.
- **info**: Dukku is not linked to any traditional council or intermediate area.
- **info**: Funakaye is not linked to any traditional council or intermediate area.
- **info**: Gombe is not linked to any traditional council or intermediate area.
- **info**: Kaltungo is not linked to any traditional council or intermediate area.
- **info**: Kwami is not linked to any traditional council or intermediate area.
- **info**: Nafada is not linked to any traditional council or intermediate area.
- **info**: Shomgom is not linked to any traditional council or intermediate area.
- **info**: Yamaltu/Deba is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Gombe State: 2 different population figures for 2006 (2353879,2365040). All are kept side by side, as the rules require.
- **info**: Gombe State: 2 different area_km2 figures for no year (17100,17428). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).

## Missing LGAs / coverage

No findings.

## Classification problems

- **check**: Cen Tuum (language #653, LANG-CEN-TUUM) is not placed in a family or branch.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no 'speaks' link to a language.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
- **info**: Open gap #940: Bauchi: Damlanci classification.
