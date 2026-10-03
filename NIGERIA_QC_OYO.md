# Oyo State — Phase 4 quality control

Run 2 October 2026 on the **live** database, read-only (nothing was changed), after batches 123–127. The script is `database/research/qc_state.php oyo`.

## Verdict

- **No errors found:** 0 problems and **0 checks**.
- **What Oyo had before batch 123, and what it has now:**

| | Before batch 123 | Now |
|---|---|---|
| Languages linked | 1 (Yoruba) | 1 (Yoruba). The Atlas places no other language in Oyo. |
| Ethnic groups linked | 0 | **Yoruba**, in all 33 LGAs, with the federal profile's five groups (Ibadan, Ibarapa, Oyo, Oke-Ogun, Ogbomoso) in the notes |
| Traditional institutions | 0 | the **Oyo Empire**, the **Oyo Kingdom** (Alaafin), the **Olubadan of Ibadan** and the **Soun of Ogbomoso** |
| Places | 1 (Ibadan) | Mapo Hall and the Ajayi Crowther home (proposed), Old Oyo National Park, Oyo-Ile, Bower's Tower, Cocoa House, four national museums and **33 headquarters towns** |
| Wards | 0 | **351** (INEC, 2015) |
| LGAs with a description and headquarters | 0 | **33 of 33** |
| LGAs with peoples linked | 0 | **33 of 33** |

## Info notes

- **Oyo has no declared NCMM monument.**
- **No festival record was made:** no usable source was found for the Egungun, Oke'Badan or Sango festivals.
- **Contradictory figures:** state population and area figures from different sources are kept side by side.
- **National gaps outside Oyo:** the Yoruba record has no other names, and the Igbo record has no "speaks" link.
- **No names batch is needed.** The names for the kingdoms, places and headquarters went in with batches 125–127.
- **Progress record:** the live progress row (#67) still reads "not_started, 0/33".

## fix_128, Oyo progress (built and tested)

- Oyo research progress → **33/33 LGAs researched, quality control in progress**, with a summary of batches 123–128.
- **Test on a fresh copy of live:**
  - The fix applied once; a second run made 0 changes.
  - QC: 0 problems and 0 checks.
  - `--revert --apply` left every table and the progress rows identical to the live copy.

## Later, when sources are found

- the groups of Surulere, Ogo Oluwa and Ori Ire (probably Ogbomoso), and any Fulani presence
- the outcome of the Soun of Ogbomoso's Supreme Court appeal
- the Alaafin's coronation date and number
- the Egungun, Oke'Badan and Sango festivals
- the headquarters of Ibadan South-West, Ogbomosho North and Surulere
- the Oke-Ogun and Ibarapa obaships (Okere of Saki, Aseyin of Iseyin and others)
- records for the Yoruba sub-groups (a decision for the whole south-west)

## Full report (as generated)

Read-only check run 2 October 2026, 18:03. Nothing was changed.

**Scope:** the state, its 33 LGAs and 351 wards, and every record linked to them: 1 ethnic groups, 1 languages, 11 polities, 3 cultural records, 49 places, 43 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 1 |
| Geographic consistency | 0 | 0 | 38 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 6 |
| Missing LGAs / coverage | 0 | 0 | 33 |
| Classification problems | 0 | 0 | 5 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Yoruba (ethnic group #64, ETH-YORUBA) has no other names recorded.

## Geographic consistency

- **info**: Centenary Hall, Abeokuta (place #933, PLACE-CENTENARY-HALL-ABEOKUTA) is located in Ogun State, outside this state (linked through a related record).
- **info**: Iga Idunganran (place #877, PLACE-IGA-IDUNGANRAN) is located in Lagos Island, outside this state (linked through a related record).
- **info**: Olumo Rock (place #932, PLACE-OLUMO-ROCK) is located in Ogun State, outside this state (linked through a related record).
- **info**: Sungbo's Eredo (place #931, PLACE-SUNGBO-S-EREDO) is located in Ogun State, outside this state (linked through a related record).
- **info**: Sungbo's Shrine, Oke-Eri (place #930, PLACE-SUNGBO-S-SHRINE-OKE-ERI) is located in Ogun State, outside this state (linked through a related record).
- **info**: Afijio is not linked to any traditional council or intermediate area.
- **info**: Akinyele is not linked to any traditional council or intermediate area.
- **info**: Atiba is not linked to any traditional council or intermediate area.
- **info**: Atisbo is not linked to any traditional council or intermediate area.
- **info**: Egbeda is not linked to any traditional council or intermediate area.
- **info**: Ibadan North is not linked to any traditional council or intermediate area.
- **info**: Ibadan North-East is not linked to any traditional council or intermediate area.
- **info**: Ibadan North-West is not linked to any traditional council or intermediate area.
- **info**: Ibadan South-East is not linked to any traditional council or intermediate area.
- **info**: Ibadan South-West is not linked to any traditional council or intermediate area.
- **info**: Ibarapa Central is not linked to any traditional council or intermediate area.
- **info**: Ibarapa East is not linked to any traditional council or intermediate area.
- **info**: Ibarapa North is not linked to any traditional council or intermediate area.
- **info**: Ido is not linked to any traditional council or intermediate area.
- **info**: Irepo is not linked to any traditional council or intermediate area.
- **info**: Iseyin is not linked to any traditional council or intermediate area.
- **info**: Itesiwaju is not linked to any traditional council or intermediate area.
- **info**: Iwajowa is not linked to any traditional council or intermediate area.
- **info**: Kajola is not linked to any traditional council or intermediate area.
- **info**: Lagelu is not linked to any traditional council or intermediate area.
- **info**: Ogbomosho North is not linked to any traditional council or intermediate area.
- **info**: Ogbomosho South is not linked to any traditional council or intermediate area.
- **info**: Ogo Oluwa is not linked to any traditional council or intermediate area.
- **info**: Olorunsogo is not linked to any traditional council or intermediate area.
- **info**: Oluyole is not linked to any traditional council or intermediate area.
- **info**: Ona Ara is not linked to any traditional council or intermediate area.
- **info**: Orelope is not linked to any traditional council or intermediate area.
- **info**: Ori Ire is not linked to any traditional council or intermediate area.
- **info**: Oyo East is not linked to any traditional council or intermediate area.
- **info**: Oyo West is not linked to any traditional council or intermediate area.
- **info**: Saki East is not linked to any traditional council or intermediate area.
- **info**: Saki West is not linked to any traditional council or intermediate area.
- **info**: Surulere is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Oyo State: 2 different population figures for 2006 (5580894,5591589). All are kept side by side, as the rules require.
- **info**: Oyo State: 3 different area_km2 figures for no year (26500,27036,28454). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).
- **info**: Open dispute #7: Kano emirship: Muhammadu Sanusi II or Aminu Ado Bayero.

## Missing LGAs / coverage

- **info**: Afijio has only 71 words of text (no LGA history yet).
- **info**: Akinyele has only 71 words of text (no LGA history yet).
- **info**: Atiba has only 82 words of text (no LGA history yet).
- **info**: Atisbo has only 81 words of text (no LGA history yet).
- **info**: Egbeda has only 81 words of text (no LGA history yet).
- **info**: Ibadan North has only 73 words of text (no LGA history yet).
- **info**: Ibadan North-East has only 85 words of text (no LGA history yet).
- **info**: Ibadan North-West has only 88 words of text (no LGA history yet).
- **info**: Ibadan South-East has only 96 words of text (no LGA history yet).
- **info**: Ibadan South-West has only 77 words of text (no LGA history yet).
- **info**: Ibarapa Central has only 79 words of text (no LGA history yet).
- **info**: Ibarapa East has only 73 words of text (no LGA history yet).
- **info**: Ibarapa North has only 73 words of text (no LGA history yet).
- **info**: Ido has only 71 words of text (no LGA history yet).
- **info**: Irepo has only 76 words of text (no LGA history yet).
- **info**: Iseyin has only 85 words of text (no LGA history yet).
- **info**: Itesiwaju has only 71 words of text (no LGA history yet).
- **info**: Iwajowa has only 71 words of text (no LGA history yet).
- **info**: Kajola has only 71 words of text (no LGA history yet).
- **info**: Lagelu has only 72 words of text (no LGA history yet).
- **info**: Ogbomosho North has only 89 words of text (no LGA history yet).
- **info**: Ogbomosho South has only 73 words of text (no LGA history yet).
- **info**: Ogo Oluwa has only 87 words of text (no LGA history yet).
- **info**: Olorunsogo has only 71 words of text (no LGA history yet).
- **info**: Oluyole has only 71 words of text (no LGA history yet).
- **info**: Ona Ara has only 73 words of text (no LGA history yet).
- **info**: Orelope has only 71 words of text (no LGA history yet).
- **info**: Ori Ire has only 73 words of text (no LGA history yet).
- **info**: Oyo East has only 78 words of text (no LGA history yet).
- **info**: Oyo West has only 73 words of text (no LGA history yet).
- **info**: Saki East has only 73 words of text (no LGA history yet).
- **info**: Saki West has only 77 words of text (no LGA history yet).
- **info**: Surulere has only 75 words of text (no LGA history yet).

## Classification problems

- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
- **info**: Open gap #940: Bauchi: Damlanci classification.
