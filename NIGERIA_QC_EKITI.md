# Ekiti State — Phase 4 quality control

Run 7 October 2026 on the **live** database, read-only (nothing was changed), after batches 148–152. The script is `database/research/qc_state.php ekiti`.

## Verdict

- **No errors found:** 0 problems and **0 checks**.
- **What Ekiti had before batch 148, and what it has now:**

| | Before batch 148 | Now |
|---|---|---|
| Languages linked | 1 (Ahan, reported, from batch 142) | 2: **Yoruba** (the Ekiti dialect) and **Ahan** (also Ekiti East) |
| Ethnic groups linked | 0 | **Yoruba**, the Ekiti sub-group, in all 16 LGAs; Moba and Ekiti East have dialect notes |
| Traditional institutions | 0 | **Ado** (Ewi), **Efon-Alaaye** (Alaaye), **Ikere** (Ogoga) and **Otun** (Oore) |
| Places | 1 (Ado Ekiti) | the **Ogun Onire Grove** (NCMM proposed No. 95), **Ikogosi Warm Springs**, the **Ise Forest Reserve**, **Olosunta Hill**, and **15 new headquarters towns** |
| Cultural records | 0 | the **Ogun Onire** and **Udiroko** festivals, plus others linked through shared records |
| Wards | 0 | **177** (INEC, 2015) |
| LGAs with a description and headquarters | 0 | **16 of 16** |
| LGAs with peoples linked | 0 | **16 of 16** |

## Info notes

- **Contradictory figures:** state population and area figures from different sources are kept side by side.
- **National gaps outside Ekiti:** the Yoruba record has no other names; the open disputes and gaps listed belong to other states.
- **Short LGA texts:** 13 LGA descriptions are under 100 words. There is no LGA history yet, as in every state.
- **Progress record:** the live progress row (#50) still reads "not_started, 0/16".
- **LGA names:**
  - The archive's **Aiyekire** is **Gbonyin** in INEC's 2015 and 2024 lists and in Wikipedia; the Constitution and Statoids say Aiyekire.
  - **Idosi-Osi** is the Constitution's spelling, which you chose to keep on 25 September 2026 (Step 3, decision B).
  - The batch-152 description of Idosi-Osi began "Ido-Osi, also written Idosi-Osi". That goes against the decision, so batch 153 rewords it.

## fix_153, Ekiti (built and tested; needs your approval)

- **A:** Ekiti research progress → **16/16 LGAs researched, quality control in progress**, with a summary of batches 148–153.
- **B:** **Aiyekire → Gbonyin**, as was done for Yewa North and South in Ogun.
  - The slug and stable ID stay the same, so the page address does not change.
  - "Aiyekire" is kept as a historical name, and the rename is logged as an administrative change (date unknown).
  - The summary says the Constitution lists the LGA as "Aiyekire".
- **C:** the **Idosi-Osi** description is reworded to lead with "Idosi-Osi, also written Ido-Osi". The record name does not change.
- **Test on a copy of live, with 148–152 applied:**
  - The fix made 6 changes; a second run made 0.
  - QC: 0 problems and 0 checks. The LGA page title reads "Gbonyin".
  - `--revert --apply` left every table and every progress row identical to the copy.

## Later, when sources are found

- where the Akoko and Yagba-Ekiti minorities live
- the outcome of the Olukere's claim at Ikere
- a dated (2025–26) source for the Alaaye of Efon
- the other obaships: Elekole of Ikole, Ajero of Ijero, Olojudo of Ido, Owa-Ooye of Okemesi, Alawe of Ilawe
- the date of the change from Aiyekire to Gbonyin
- the Yoruba sub-group decision for the whole south-west

## Raw QC output

# Phase 4 quality control — Ekiti State

Read-only check run 7 October 2026, 14:35. Nothing was changed.

**Scope:** the state, its 16 LGAs and 177 wards, and every record linked to them: 1 ethnic groups, 2 languages, 24 polities, 8 cultural records, 36 places, 31 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 1 |
| Geographic consistency | 0 | 0 | 32 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 7 |
| Missing LGAs / coverage | 0 | 0 | 13 |
| Classification problems | 0 | 0 | 5 |

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
- **info**: Old Palace of the Deji of Akure (place #1150, PLACE-OLD-PALACE-OF-THE-DEJI-OF-AKURE) is located in Akure South, outside this state (linked through a related record).
- **info**: Olumo Rock (place #932, PLACE-OLUMO-ROCK) is located in Ogun State, outside this state (linked through a related record).
- **info**: Opa Oranmiyan (Staff of Oranmiyan) (place #1077, PLACE-OPA-ORANMIYAN-STAFF-OF-ORANMIYAN) is located in Osun State, outside this state (linked through a related record).
- **info**: Osun-Osogbo Sacred Grove (place #1071, PLACE-OSUN-OSOGBO-SACRED-GROVE) is located in Osun State, outside this state (linked through a related record).
- **info**: Palace of the Olowo of Owo (place #1154, PLACE-PALACE-OF-THE-OLOWO-OF-OWO) is located in Owo, outside this state (linked through a related record).
- **info**: Shrine of Osun, Ataoja's Palace, Osogbo (place #1073, PLACE-SHRINE-OF-OSUN-ATAOJA-S-PALACE-OSOGBO) is located in Osun State, outside this state (linked through a related record).
- **info**: Sungbo's Eredo (place #931, PLACE-SUNGBO-S-EREDO) is located in Ogun State, outside this state (linked through a related record).
- **info**: Sungbo's Shrine, Oke-Eri (place #930, PLACE-SUNGBO-S-SHRINE-OKE-ERI) is located in Ogun State, outside this state (linked through a related record).
- **info**: Ado Ekiti is not linked to any traditional council or intermediate area.
- **info**: Aiyekire is not linked to any traditional council or intermediate area.
- **info**: Efon is not linked to any traditional council or intermediate area.
- **info**: Ekiti East is not linked to any traditional council or intermediate area.
- **info**: Ekiti South-West is not linked to any traditional council or intermediate area.
- **info**: Ekiti West is not linked to any traditional council or intermediate area.
- **info**: Emure is not linked to any traditional council or intermediate area.
- **info**: Idosi-Osi is not linked to any traditional council or intermediate area.
- **info**: Ijero is not linked to any traditional council or intermediate area.
- **info**: Ikere is not linked to any traditional council or intermediate area.
- **info**: Ikole is not linked to any traditional council or intermediate area.
- **info**: Ilejemeje is not linked to any traditional council or intermediate area.
- **info**: Irepodun/Ifelodun is not linked to any traditional council or intermediate area.
- **info**: Ise/Orun is not linked to any traditional council or intermediate area.
- **info**: Moba is not linked to any traditional council or intermediate area.
- **info**: Oye is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Ekiti State: 2 different population figures for 1991 (1535790,1647822). All are kept side by side, as the rules require.
- **info**: Ekiti State: 2 different population figures for 2006 (2384212,2398957). All are kept side by side, as the rules require.
- **info**: Ekiti State: 3 different area_km2 figures for no year (5434,5797,5888). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).
- **info**: Open dispute #7: Kano emirship: Muhammadu Sanusi II or Aminu Ado Bayero.

## Missing LGAs / coverage

- **info**: Aiyekire has only 81 words of text (no LGA history yet).
- **info**: Ekiti South-West has only 70 words of text (no LGA history yet).
- **info**: Ekiti West has only 82 words of text (no LGA history yet).
- **info**: Emure has only 71 words of text (no LGA history yet).
- **info**: Idosi-Osi has only 80 words of text (no LGA history yet).
- **info**: Ijero has only 71 words of text (no LGA history yet).
- **info**: Ikere has only 97 words of text (no LGA history yet).
- **info**: Ikole has only 71 words of text (no LGA history yet).
- **info**: Ilejemeje has only 74 words of text (no LGA history yet).
- **info**: Irepodun/Ifelodun has only 71 words of text (no LGA history yet).
- **info**: Ise/Orun has only 71 words of text (no LGA history yet).
- **info**: Moba has only 98 words of text (no LGA history yet).
- **info**: Oye has only 89 words of text (no LGA history yet).

## Classification problems

- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
- **info**: Open gap #940: Bauchi: Damlanci classification.
