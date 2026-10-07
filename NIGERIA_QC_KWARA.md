# Kwara State — Phase 4 quality control

Run 7 October 2026 on the **live** database, read-only (nothing was changed), after batches 154–158. The script is `database/research/qc_state.php kwara`.

## Verdict

- **No errors found:** 0 problems and **0 checks**.
- **What Kwara had before batch 154, and what it has now:**

| | Before batch 154 | Now |
|---|---|---|
| Languages linked | 2 (Yoruba, Nupe) | **5**: Yoruba and Nupe (also Edu and Pategi), plus **Baatọnum** (Gur), **Busa** and **Bokobaru** (Mande), with new Gur and Mande branches |
| Ethnic groups linked | 0 | **Yoruba** (Igbomina, Ibolo and Ekiti named), **Nupe**, **Fulani**, **Hausa**, and the new **Bariba** and **Busa** records |
| Traditional institutions | 0 | the **Ilorin Emirate**, **Offa** (Olofa), the **Pategi Emirate** (reported) and the **Lafiagi Emirate** (reported) |
| Places | 1 (Ilorin) | 5 declared and 4 proposed NCMM monuments (including the **Esie stone images**), the national museums at Esie and Ilorin, and **15 new headquarters towns** |
| Cultural records | 0 | the **Pategi Regatta** and **Onimoka**, plus others linked through shared records |
| Wards | 0 | **193** (INEC, 2015) |
| LGAs with a description and headquarters | 0 | **16 of 16** |
| LGAs with peoples linked | 0 | **16 of 16** (Ekiti LGA and Moro without a named people or sub-group) |

## Info notes

- **Contradictory figures:** state population and area figures from different sources are kept side by side.
- **National gaps outside Kwara:** the Yoruba record has no other names; the open disputes listed belong to other states.
- **Short LGA texts:** 15 LGA descriptions are under 100 words. There is no LGA history yet, as in every state.
- **Progress record:** the live progress row (#60) still reads "not_started, 0/16".
- **During building:** fix_159 was first written with a doubled quote, and PHP refused to run it, so nothing changed. It was corrected and retested.

## fix_159, Kwara progress (built and tested; needs your approval)

- Kwara research progress → **16/16 LGAs researched, quality control in progress**, with a summary of batches 154–159.
- **Test on a copy of live, with 154–158 applied:**
  - The fix made 1 change; a second run made 0.
  - QC: 0 problems.
  - `--revert --apply` left every table and every progress row identical to the copy.

## Later, when sources are found

- Sorko (no Glottolog entry; speakers have shifted to Hausa)
- the people of Moro, Ekiti LGA and the three Ilorin LGAs
- 2025–26 sources for the Etsu Pategi and the Emir of Lafiagi
- other thrones: Emir of Kaiama, Emir of Shonga, Olomu of Omu-Aran, Elerin of Erin-Ile
- descriptions of the forts, stone figures, the Alimi Mosque, Queen Elizabeth Girls School and the Oya grove
- a record for the Sokoto Caliphate, to link Ilorin
- your decision: Bariba or Baatonu as the record name

## Raw QC output

# Phase 4 quality control — Kwara State

Read-only check run 7 October 2026, 15:49. Nothing was changed.

**Scope:** the state, its 16 LGAs and 193 wards, and every record linked to them: 6 ethnic groups, 5 languages, 50 polities, 19 cultural records, 57 places, 51 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 1 |
| Geographic consistency | 0 | 0 | 47 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 6 |
| Missing LGAs / coverage | 0 | 0 | 15 |
| Classification problems | 0 | 0 | 5 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Yoruba (ethnic group #64, ETH-YORUBA) has no other names recorded.

## Geographic consistency

- **info**: Centenary Hall, Abeokuta (place #933, PLACE-CENTENARY-HALL-ABEOKUTA) is located in Ogun State, outside this state (linked through a related record).
- **info**: Dala Hill (place #588, PLACE-DALA-HILL) is located in Dala, outside this state (linked through a related record).
- **info**: Durbi-Takusheyi (place #755, PLACE-DURBI-TAKUSHEYI) is located in Katsina State, outside this state (linked through a related record).
- **info**: Emir of Gombe's Palace (place #550, PLACE-EMIR-OF-GOMBE-S-PALACE) is located in Gombe, outside this state (linked through a related record).
- **info**: Giant Tombs of the Zamfara Rulers, Zurmi (place #830, PLACE-GIANT-TOMBS-OF-THE-ZAMFARA-RULERS-ZURMI) is located in Zurmi, outside this state (linked through a related record).
- **info**: Gidan Makama Museum (place #584, PLACE-GIDAN-MAKAMA-MUSEUM) is located in Kano State, outside this state (linked through a related record).
- **info**: Gidan Rumfa (place #586, PLACE-GIDAN-RUMFA) is located in Kano State, outside this state (linked through a related record).
- **info**: Gobarau Minaret (place #754, PLACE-GOBARAU-MINARET) is located in Katsina, outside this state (linked through a related record).
- **info**: Iga Idunganran (place #877, PLACE-IGA-IDUNGANRAN) is located in Lagos Island, outside this state (linked through a related record).
- **info**: Jata (place #832, PLACE-JATA) is located in Zamfara State, outside this state (linked through a related record).
- **info**: Kano City Walls and Gates (place #585, PLACE-KANO-CITY-WALLS-AND-GATES) is located in Kano State, outside this state (linked through a related record).
- **info**: Kurmi Market (place #587, PLACE-KURMI-MARKET) is located in Kano State, outside this state (linked through a related record).
- **info**: Kusugu Well (place #758, PLACE-KUSUGU-WELL) is located in Daura, outside this state (linked through a related record).
- **info**: Mapo Hall (place #985, PLACE-MAPO-HALL) is located in Ibadan South-East, outside this state (linked through a related record).
- **info**: Mbormi Battle Ground (place #548, PLACE-MBORMI-BATTLE-GROUND) is located in Funakaye, outside this state (linked through a related record).
- **info**: Namoda Tomb, Kaura Namoda (place #831, PLACE-NAMODA-TOMB-KAURA-NAMODA) is located in Kaura-Namoda, outside this state (linked through a related record).
- **info**: National Museum Fombina (place #314, PLACE-NATIONAL-MUSEUM-FOMBINA) is located in Yola South, outside this state (linked through a related record).
- **info**: National Museum Ile-Ife (place #1079, PLACE-NATIONAL-MUSEUM-ILE-IFE) is located in Osun State, outside this state (linked through a related record).
- **info**: National Museum Osogbo (place #1080, PLACE-NATIONAL-MUSEUM-OSOGBO) is located in Osun State, outside this state (linked through a related record).
- **info**: National Museum Oyo (place #992, PLACE-NATIONAL-MUSEUM-OYO) is located in Oyo State, outside this state (linked through a related record).
- **info**: Old Oyo (Oyo-Ile) (place #988, PLACE-OLD-OYO-OYO-ILE) is located in Oyo State, outside this state (linked through a related record).
- **info**: Old Oyo National Park (place #987, PLACE-OLD-OYO-NATIONAL-PARK) is located in Oyo State, outside this state (linked through a related record).
- **info**: Old Palace of the Deji of Akure (place #1150, PLACE-OLD-PALACE-OF-THE-DEJI-OF-AKURE) is located in Akure South, outside this state (linked through a related record).
- **info**: Olosunta Hill (place #1198, PLACE-OLOSUNTA-HILL) is located in Ikere, outside this state (linked through a related record).
- **info**: Olumo Rock (place #932, PLACE-OLUMO-ROCK) is located in Ogun State, outside this state (linked through a related record).
- **info**: Opa Oranmiyan (Staff of Oranmiyan) (place #1077, PLACE-OPA-ORANMIYAN-STAFF-OF-ORANMIYAN) is located in Osun State, outside this state (linked through a related record).
- **info**: Osun-Osogbo Sacred Grove (place #1071, PLACE-OSUN-OSOGBO-SACRED-GROVE) is located in Osun State, outside this state (linked through a related record).
- **info**: Palace of the Olowo of Owo (place #1154, PLACE-PALACE-OF-THE-OLOWO-OF-OWO) is located in Owo, outside this state (linked through a related record).
- **info**: Shrine of Osun, Ataoja's Palace, Osogbo (place #1073, PLACE-SHRINE-OF-OSUN-ATAOJA-S-PALACE-OSOGBO) is located in Osun State, outside this state (linked through a related record).
- **info**: Sungbo's Eredo (place #931, PLACE-SUNGBO-S-EREDO) is located in Ogun State, outside this state (linked through a related record).
- **info**: Sungbo's Shrine, Oke-Eri (place #930, PLACE-SUNGBO-S-SHRINE-OKE-ERI) is located in Ogun State, outside this state (linked through a related record).
- **info**: Asa is not linked to any traditional council or intermediate area.
- **info**: Baruten is not linked to any traditional council or intermediate area.
- **info**: Edu is not linked to any traditional council or intermediate area.
- **info**: Ekiti is not linked to any traditional council or intermediate area.
- **info**: Ifelodun is not linked to any traditional council or intermediate area.
- **info**: Ilorin East is not linked to any traditional council or intermediate area.
- **info**: Ilorin South is not linked to any traditional council or intermediate area.
- **info**: Ilorin West is not linked to any traditional council or intermediate area.
- **info**: Irepodun is not linked to any traditional council or intermediate area.
- **info**: Isin is not linked to any traditional council or intermediate area.
- **info**: Kaiama is not linked to any traditional council or intermediate area.
- **info**: Moro is not linked to any traditional council or intermediate area.
- **info**: Offa is not linked to any traditional council or intermediate area.
- **info**: Oke Ero is not linked to any traditional council or intermediate area.
- **info**: Oyun is not linked to any traditional council or intermediate area.
- **info**: Pategi is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Kwara State: 2 different population figures for 2006 (2365353,2371089). All are kept side by side, as the rules require.
- **info**: Kwara State: 2 different area_km2 figures for no year (33792,35705). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).
- **info**: Open dispute #7: Kano emirship: Muhammadu Sanusi II or Aminu Ado Bayero.

## Missing LGAs / coverage

- **info**: Asa has only 66 words of text (no LGA history yet).
- **info**: Edu has only 79 words of text (no LGA history yet).
- **info**: Ekiti has only 91 words of text (no LGA history yet).
- **info**: Ifelodun has only 65 words of text (no LGA history yet).
- **info**: Ilorin East has only 72 words of text (no LGA history yet).
- **info**: Ilorin South has only 54 words of text (no LGA history yet).
- **info**: Ilorin West has only 97 words of text (no LGA history yet).
- **info**: Irepodun has only 88 words of text (no LGA history yet).
- **info**: Isin has only 87 words of text (no LGA history yet).
- **info**: Kaiama has only 78 words of text (no LGA history yet).
- **info**: Moro has only 85 words of text (no LGA history yet).
- **info**: Offa has only 95 words of text (no LGA history yet).
- **info**: Oke Ero has only 76 words of text (no LGA history yet).
- **info**: Oyun has only 65 words of text (no LGA history yet).
- **info**: Pategi has only 85 words of text (no LGA history yet).

## Classification problems

- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
- **info**: Open gap #940: Bauchi: Damlanci classification.
