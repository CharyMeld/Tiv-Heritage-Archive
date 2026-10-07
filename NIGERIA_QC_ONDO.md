# Ondo State — Phase 4 quality control

Run 7 October 2026 on the **live** database, read-only (nothing was changed), after batches 142–146. The script is `database/research/qc_state.php ondo`.

## Verdict

- **No errors found:** 0 problems and **3 checks**, all expected. Akpes, Arigidi and Ukaan have no parent family. The Atlas classes Akpes and Ukaan as branches of Benue–Congo on their own, and Glottolog puts Arigidi directly under Defoid, a family the archive does not have yet. The same was accepted for Ọkọ–Eni–Ọsayẹn (Kogi) and Cen Tuum (Gombe). No change.
- **What Ondo had before batch 142, and what it has now:**

| | Before batch 142 | Now |
|---|---|---|
| Languages linked | 1 (Yoruba) | **10**: Yoruba plus Arigidi, Ahan, Akpes, Ukaan, Ehuẹun, Ukue, Uhami, Iyayu and Ịzọn, and a new Ijoid branch |
| Ethnic groups linked | 0 | **Yoruba** in all 18 LGAs (Akoko, Ikale, Ilaje, Owo, Idanre and Ose named in the notes) and the **Ijaw** (Ese Odo; Odigbo, reported) |
| Traditional institutions | 0 | the **Akure Kingdom** (Deji), **Owo Kingdom** (Olowo), **Ondo Kingdom** (Osemawe), **Ugbo Kingdom** (Olugbo) and the **Idoani Confederacy** (Alani, reported) |
| Places | 1 (Akure) | 3 declared and 3 proposed NCMM monuments (including **Iho Eleru** and **Idanre Hill**), the Olowo's palace, 2 national museums, and **17 new headquarters towns** |
| Cultural records | 0 | the **Igogo** and **Ulefunta** festivals, plus others linked through shared records |
| Wards | 0 | **203** (INEC, 2015) |
| LGAs with a description and headquarters | 0 | **18 of 18** |
| LGAs with peoples linked | 0 | **18 of 18** (5 without a named sub-group) |

## Info notes

- **Contradictory figures:** state population and area figures from different sources are kept side by side.
- **National gaps outside Ondo:** the Yoruba record has no other names; the open disputes and gaps listed belong to other states.
- **Short LGA texts:** 13 LGA descriptions are under 100 words. There is no LGA history yet, as in every state so far.
- **No names batch is needed.** The names for the kingdoms, places and headquarters went in with batches 144–146.
- **Progress record:** the live progress row (#65) still reads "not_started, 0/18".

## fix_147, Ondo progress (built and tested; needs your approval)

- Ondo research progress → **18/18 LGAs researched, quality control in progress**, with a summary of batches 142–147.
- **Test on a copy of live, with 142–146 applied:**
  - The fix applied once; a second run made 0 changes.
  - QC: 0 problems and the same 3 checks.
  - `--revert --apply` left every table and every progress row identical to the copy.

## Later, when sources are found

- the sub-groups of Akure North, Akure South, Ondo East, Ondo West and Ifedore
- Iho Eleru: the NCMM says "near Owo", Wikipedia says Isarun (Ifedore); the two discovery dates and two ages
- the other obaships: Owa-Ale of Idanre, Jegun of Ile-Oluji, Abodi of Ikale, Amapetu of Mahin, Olukare of Ikare, Olubaka of Oka
- descriptions of the Igbara-Oke petroglyphs, the Ashuba marble stone and Igbo Olodumare
- a current (2025–26) source for the Alani of Idoani
- the Yoruba sub-group decision for the whole south-west

## Raw QC output

# Phase 4 quality control — Ondo State

Read-only check run 7 October 2026, 14:06. Nothing was changed.

**Scope:** the state, its 18 LGAs and 203 wards, and every record linked to them: 2 ethnic groups, 10 languages, 20 polities, 6 cultural records, 41 places, 58 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 1 |
| Geographic consistency | 0 | 0 | 32 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 6 |
| Missing LGAs / coverage | 0 | 0 | 13 |
| Classification problems | 0 | 3 | 5 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Yoruba (ethnic group #64, ETH-YORUBA) has no other names recorded.

## Geographic consistency

- **info**: Centenary Hall, Abeokuta (place #933, PLACE-CENTENARY-HALL-ABEOKUTA) is located in Ogun State, outside this state (linked through a related record).
- **info**: Iga Idunganran (place #877, PLACE-IGA-IDUNGANRAN) is located in Lagos Island, outside this state (linked through a related record).
- **info**: Mapo Hall (place #985, PLACE-MAPO-HALL) is located in Ibadan South-East, outside this state (linked through a related record).
- **info**: National Museum Ile-Ife (place #1079, PLACE-NATIONAL-MUSEUM-ILE-IFE) is located in Osun State, outside this state (linked through a related record).
- **info**: National Museum Osogbo (place #1080, PLACE-NATIONAL-MUSEUM-OSOGBO) is located in Osun State, outside this state (linked through a related record).
- **info**: National Museum Oyo (place #992, PLACE-NATIONAL-MUSEUM-OYO) is located in Oyo State, outside this state (linked through a related record).
- **info**: Old Oyo (Oyo-Ile) (place #988, PLACE-OLD-OYO-OYO-ILE) is located in Oyo State, outside this state (linked through a related record).
- **info**: Old Oyo National Park (place #987, PLACE-OLD-OYO-NATIONAL-PARK) is located in Oyo State, outside this state (linked through a related record).
- **info**: Olumo Rock (place #932, PLACE-OLUMO-ROCK) is located in Ogun State, outside this state (linked through a related record).
- **info**: Opa Oranmiyan (Staff of Oranmiyan) (place #1077, PLACE-OPA-ORANMIYAN-STAFF-OF-ORANMIYAN) is located in Osun State, outside this state (linked through a related record).
- **info**: Osun-Osogbo Sacred Grove (place #1071, PLACE-OSUN-OSOGBO-SACRED-GROVE) is located in Osun State, outside this state (linked through a related record).
- **info**: Shrine of Osun, Ataoja's Palace, Osogbo (place #1073, PLACE-SHRINE-OF-OSUN-ATAOJA-S-PALACE-OSOGBO) is located in Osun State, outside this state (linked through a related record).
- **info**: Sungbo's Eredo (place #931, PLACE-SUNGBO-S-EREDO) is located in Ogun State, outside this state (linked through a related record).
- **info**: Sungbo's Shrine, Oke-Eri (place #930, PLACE-SUNGBO-S-SHRINE-OKE-ERI) is located in Ogun State, outside this state (linked through a related record).
- **info**: Akoko North-East is not linked to any traditional council or intermediate area.
- **info**: Akoko North-West is not linked to any traditional council or intermediate area.
- **info**: Akoko South-East is not linked to any traditional council or intermediate area.
- **info**: Akoko South-West is not linked to any traditional council or intermediate area.
- **info**: Akure North is not linked to any traditional council or intermediate area.
- **info**: Akure South is not linked to any traditional council or intermediate area.
- **info**: Ese Odo is not linked to any traditional council or intermediate area.
- **info**: Idanre is not linked to any traditional council or intermediate area.
- **info**: Ifedore is not linked to any traditional council or intermediate area.
- **info**: Ilaje is not linked to any traditional council or intermediate area.
- **info**: Ile-Oluji-Okeigbo is not linked to any traditional council or intermediate area.
- **info**: Irele is not linked to any traditional council or intermediate area.
- **info**: Odigbo is not linked to any traditional council or intermediate area.
- **info**: Okitipupa is not linked to any traditional council or intermediate area.
- **info**: Ondo East is not linked to any traditional council or intermediate area.
- **info**: Ondo West is not linked to any traditional council or intermediate area.
- **info**: Ose is not linked to any traditional council or intermediate area.
- **info**: Owo is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Ondo State: 2 different population figures for 2006 (3441024,3460877). All are kept side by side, as the rules require.
- **info**: Ondo State: 2 different area_km2 figures for no year (15019,15820). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).
- **info**: Open dispute #7: Kano emirship: Muhammadu Sanusi II or Aminu Ado Bayero.

## Missing LGAs / coverage

- **info**: Akoko North-East has only 91 words of text (no LGA history yet).
- **info**: Akoko North-West has only 89 words of text (no LGA history yet).
- **info**: Akure North has only 79 words of text (no LGA history yet).
- **info**: Ese Odo has only 93 words of text (no LGA history yet).
- **info**: Ifedore has only 91 words of text (no LGA history yet).
- **info**: Ilaje has only 93 words of text (no LGA history yet).
- **info**: Ile-Oluji-Okeigbo has only 70 words of text (no LGA history yet).
- **info**: Irele has only 67 words of text (no LGA history yet).
- **info**: Okitipupa has only 82 words of text (no LGA history yet).
- **info**: Ondo East has only 95 words of text (no LGA history yet).
- **info**: Ondo West has only 86 words of text (no LGA history yet).
- **info**: Ose has only 91 words of text (no LGA history yet).
- **info**: Owo has only 89 words of text (no LGA history yet).

## Classification problems

- **check**: Akpes (language #688, LANG-AKPES) is not placed in a family or branch.
- **check**: Arigidi (language #689, LANG-ARIGIDI) is not placed in a family or branch.
- **check**: Ukaan (language #693, LANG-UKAAN) is not placed in a family or branch.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
- **info**: Open gap #940: Bauchi: Damlanci classification.
