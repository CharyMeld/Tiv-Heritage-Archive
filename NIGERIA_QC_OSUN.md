# Osun State — Phase 4 quality control

Run 2 October 2026 on the **live** database, read-only (nothing was changed), after batches 129–133. The script is `database/research/qc_state.php osun`.

## Verdict

- **No errors found:** 0 problems and **0 checks**.
- **What Osun had before batch 129, and what it has now:**

| | Before batch 129 | Now |
|---|---|---|
| Languages linked | 1 (Yoruba) | 1 (Yoruba). The Atlas places no other language in Osun. |
| Ethnic groups linked | 0 | **Yoruba**, in all 30 LGAs: the Ijesha (6 LGAs), the Ife (2) and the Igbomina (2) are named in the notes |
| Traditional institutions | 0 | the **Ife Kingdom** (Ooni), **Ijesaland** (Owa Obokun), the **Ataoja of Osogbo** and the **Orangun of Ila** |
| Places | 1 (Oshogbo) | 5 declared and 3 proposed NCMM monuments, including the **Osun-Osogbo Sacred Grove** (UNESCO); 2 national museums; **29 new headquarters towns** |
| Cultural records | 0 | the **Osun-Osogbo festival**, plus others linked through shared records |
| Wards | 0 | **332** (INEC, 2015) |
| LGAs with a description and headquarters | 0 | **30 of 30** |
| LGAs with peoples linked | 0 | **30 of 30** (20 without a named sub-group) |

## Info notes

- **Contradictory figures:** state population and area figures from different sources are kept side by side.
- **National gaps outside Osun:** the Yoruba record has no other names, and the Igbo record has no "speaks" link.
- **No names batch is needed.** The names for the kingdoms, places and headquarters went in with batches 131–133.
- **Progress record:** the live progress row (#66) still reads "not_started, 0/30".
- **During testing:** QC's duplicate check caught a second Osogbo place. Batch 133 was fixed to reuse the existing state-capital place before it went live.

## fix_134, Osun progress (built and tested)

- Osun research progress → **30/30 LGAs researched, quality control in progress**, with a summary of batches 129–134.
- **Test on a fresh copy of live:**
  - The fix applied once; a second run made 0 changes.
  - QC: 0 problems and 0 checks.
  - `--revert --apply` left every table and the progress rows identical to the live copy.

## Later, when sources are found

- the sub-groups (Ibolo/Osun, Oyo, Ife) of 20 LGAs
- the outcome of the dispute over the Owa Obokun's selection
- the Orangun of Ila's accession, and other obaships (Oluwo of Iwo, Timi of Ede, Akirun of Ikirun)
- descriptions of Ita Yemoo, the Ifa temple, the Osogbo shrines and the Igbajo figure
- the Olojo festival of Ile-Ife and the Ife heads
- records for the Yoruba sub-groups (a decision for the whole south-west)

## Full report (as generated)

Read-only check run 2 October 2026, 18:40. Nothing was changed.

**Scope:** the state, its 30 LGAs and 332 wards, and every record linked to them: 1 ethnic groups, 1 languages, 15 polities, 4 cultural records, 49 places, 43 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 1 |
| Geographic consistency | 0 | 0 | 39 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 6 |
| Missing LGAs / coverage | 0 | 0 | 30 |
| Classification problems | 0 | 0 | 5 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Yoruba (ethnic group #64, ETH-YORUBA) has no other names recorded.

## Geographic consistency

- **info**: Centenary Hall, Abeokuta (place #933, PLACE-CENTENARY-HALL-ABEOKUTA) is located in Ogun State, outside this state (linked through a related record).
- **info**: Iga Idunganran (place #877, PLACE-IGA-IDUNGANRAN) is located in Lagos Island, outside this state (linked through a related record).
- **info**: Mapo Hall (place #985, PLACE-MAPO-HALL) is located in Ibadan South-East, outside this state (linked through a related record).
- **info**: National Museum Oyo (place #992, PLACE-NATIONAL-MUSEUM-OYO) is located in Oyo State, outside this state (linked through a related record).
- **info**: Old Oyo (Oyo-Ile) (place #988, PLACE-OLD-OYO-OYO-ILE) is located in Oyo State, outside this state (linked through a related record).
- **info**: Old Oyo National Park (place #987, PLACE-OLD-OYO-NATIONAL-PARK) is located in Oyo State, outside this state (linked through a related record).
- **info**: Olumo Rock (place #932, PLACE-OLUMO-ROCK) is located in Ogun State, outside this state (linked through a related record).
- **info**: Sungbo's Eredo (place #931, PLACE-SUNGBO-S-EREDO) is located in Ogun State, outside this state (linked through a related record).
- **info**: Sungbo's Shrine, Oke-Eri (place #930, PLACE-SUNGBO-S-SHRINE-OKE-ERI) is located in Ogun State, outside this state (linked through a related record).
- **info**: Aiyedade is not linked to any traditional council or intermediate area.
- **info**: Aiyedire is not linked to any traditional council or intermediate area.
- **info**: Atakunmosa East is not linked to any traditional council or intermediate area.
- **info**: Atakunmosa West is not linked to any traditional council or intermediate area.
- **info**: Boluwaduro is not linked to any traditional council or intermediate area.
- **info**: Boripe is not linked to any traditional council or intermediate area.
- **info**: Ede North is not linked to any traditional council or intermediate area.
- **info**: Ede South is not linked to any traditional council or intermediate area.
- **info**: Egbedore is not linked to any traditional council or intermediate area.
- **info**: Ejigbo is not linked to any traditional council or intermediate area.
- **info**: Ife Central is not linked to any traditional council or intermediate area.
- **info**: Ife East is not linked to any traditional council or intermediate area.
- **info**: Ife North is not linked to any traditional council or intermediate area.
- **info**: Ife South is not linked to any traditional council or intermediate area.
- **info**: Ifedayo is not linked to any traditional council or intermediate area.
- **info**: Ifelodun is not linked to any traditional council or intermediate area.
- **info**: Ila is not linked to any traditional council or intermediate area.
- **info**: Ilesha East is not linked to any traditional council or intermediate area.
- **info**: Ilesha West is not linked to any traditional council or intermediate area.
- **info**: Irepodun is not linked to any traditional council or intermediate area.
- **info**: Irewole is not linked to any traditional council or intermediate area.
- **info**: Isokan is not linked to any traditional council or intermediate area.
- **info**: Iwo is not linked to any traditional council or intermediate area.
- **info**: Obokun is not linked to any traditional council or intermediate area.
- **info**: Odo-Otin is not linked to any traditional council or intermediate area.
- **info**: Ola-Oluwa is not linked to any traditional council or intermediate area.
- **info**: Olorunda is not linked to any traditional council or intermediate area.
- **info**: Oriade is not linked to any traditional council or intermediate area.
- **info**: Orolu is not linked to any traditional council or intermediate area.
- **info**: Osogbo is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Osun State: 2 different population figures for 2006 (3416959,3423535). All are kept side by side, as the rules require.
- **info**: Osun State: 3 different area_km2 figures for no year (8585,9026,14875). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).
- **info**: Open dispute #7: Kano emirship: Muhammadu Sanusi II or Aminu Ado Bayero.

## Missing LGAs / coverage

- **info**: Aiyedade has only 81 words of text (no LGA history yet).
- **info**: Aiyedire has only 71 words of text (no LGA history yet).
- **info**: Atakunmosa East has only 66 words of text (no LGA history yet).
- **info**: Atakunmosa West has only 66 words of text (no LGA history yet).
- **info**: Boluwaduro has only 98 words of text (no LGA history yet).
- **info**: Boripe has only 71 words of text (no LGA history yet).
- **info**: Ede North has only 84 words of text (no LGA history yet).
- **info**: Ede South has only 73 words of text (no LGA history yet).
- **info**: Egbedore has only 71 words of text (no LGA history yet).
- **info**: Ejigbo has only 71 words of text (no LGA history yet).
- **info**: Ife Central has only 76 words of text (no LGA history yet).
- **info**: Ife East has only 72 words of text (no LGA history yet).
- **info**: Ife North has only 73 words of text (no LGA history yet).
- **info**: Ife South has only 73 words of text (no LGA history yet).
- **info**: Ifedayo has only 82 words of text (no LGA history yet).
- **info**: Ifelodun has only 79 words of text (no LGA history yet).
- **info**: Ila has only 76 words of text (no LGA history yet).
- **info**: Ilesha East has only 85 words of text (no LGA history yet).
- **info**: Ilesha West has only 79 words of text (no LGA history yet).
- **info**: Irepodun has only 79 words of text (no LGA history yet).
- **info**: Irewole has only 71 words of text (no LGA history yet).
- **info**: Isokan has only 71 words of text (no LGA history yet).
- **info**: Iwo has only 85 words of text (no LGA history yet).
- **info**: Obokun has only 64 words of text (no LGA history yet).
- **info**: Odo-Otin has only 72 words of text (no LGA history yet).
- **info**: Ola-Oluwa has only 73 words of text (no LGA history yet).
- **info**: Olorunda has only 82 words of text (no LGA history yet).
- **info**: Oriade has only 78 words of text (no LGA history yet).
- **info**: Orolu has only 75 words of text (no LGA history yet).
- **info**: Osogbo has only 98 words of text (no LGA history yet).

## Classification problems

- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
- **info**: Open gap #940: Bauchi: Damlanci classification.
