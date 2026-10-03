# Lagos State — Phase 4 quality control

Run 2 October 2026 on the **live** database, read-only (nothing was changed), after batches 111–115. The script is `database/research/qc_state.php lagos`.

## Verdict

- **No errors found (0 problems).**
- **What Lagos had before batch 111, and what it has now:**

| | Before batch 111 | Now |
|---|---|---|
| Languages linked | 1 (Yoruba) | **2**: Yoruba and **Gun** (Egun, a Gbe language of Badagry; new, with the new **Kwa** branch) |
| Ethnic groups linked | 0 | **6**: Yoruba and the new **Ogu** (Gun/Egun) record; Hausa, Igbo, Fulani and Nupe as migrant communities |
| Traditional institutions | 0 | the **Kingdom of Lagos (Eko)**, **Badagry Kingdom** and the **Olojo of Ojo** |
| Places | 1 (Ikeja) | 4 declared and 11 proposed NCMM monuments, the National Museum Lagos, the Vlekete slave market and **16 headquarters towns** |
| Cultural records | 0 | the **Eyo festival** and **Zangbeto**, plus others linked through shared records |
| Wards | 0 | **245** (INEC, 2015) |
| LGAs with a description | 0 | **20 of 20** (17 with a headquarters) |
| LGAs with peoples linked | 0 | **14 of 20** |

## The checks

- **6 LGAs have no people linked:** Ajeromi-Ifelodun, Amuwo-Odofin, Apapa, Eti-Osa, Lagos Mainland and Surulere. The federal profile and Wikipedia name peoples only by division, or for particular towns. These LGAs are mostly cosmopolitan, but no source read names their peoples. This remains a gap.

## Info notes

- **Three LGAs have no headquarters** (Ajeromi-Ifelodun, Oshodi-Isolo, Lagos Mainland). The sources give only the LGA's own name.
- **Contradictory figures:** two state populations for 2006 and two state areas are kept side by side.
- **National gaps outside Lagos:** the Yoruba record has no other names, and the Igbo record has no "speaks" link.
- **No names batch is needed.** The Ogu, Gun, Kingdom of Lagos and heritage-site name variants went in with batches 111, 113 and 114.
- **Progress record:** the live progress row (#61) still reads "not_started, 0/20".
- **Kingdom of Lagos seat:** the record has no seat set. Batch 114 could not set it, because the importer's updates take only administrative units.

## fix_116, Lagos progress and seat (built and tested)

- **A.** Lagos research progress → **20/20 LGAs researched, quality control in progress**, with a summary of batches 111–116.
- **B.** The Kingdom of Lagos's seat → **Iga Idunganran**, the Oba's palace. It is filled only while the seat is empty.
- **Test on a fresh copy of live:**
  - The fix applied once (2 changes); a second run made 0 changes.
  - QC: 0 problems. The kingdom's page shows the palace.
  - `--revert --apply` left every table, the progress rows and all polity seats identical to the live copy.

## Later, when sources are found

- the peoples of 6 LGAs, and records for the Yoruba sub-groups (a decision for the whole south-west)
- the headquarters towns of Ajeromi-Ifelodun, Oshodi-Isolo and Lagos Mainland
- the next Akran of Badagry (the throne has been vacant since 12 January 2026)
- the Ayangburen of Ikorodu, the obas of Epe, Ikeja and Ibeju, and the Ajido and Dale-Whedakoh kingdoms
- the Shitta-Bey Mosque's status: declared (Wikipedia) or proposed (NCMM)
- the Ewe at Badagry, and the Edo, Efik, Ijaw, Ibibio, Saro and Amaro communities

## Full report (as generated)

Read-only check run 2 October 2026, 15:33. Nothing was changed.

**Scope:** the state, its 20 LGAs and 245 wards, and every record linked to them: 6 ethnic groups, 2 languages, 25 polities, 11 cultural records, 48 places, 30 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 1 |
| Geographic consistency | 0 | 0 | 34 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 6 |
| Missing LGAs / coverage | 0 | 6 | 19 |
| Classification problems | 0 | 0 | 6 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Yoruba (ethnic group #64, ETH-YORUBA) has no other names recorded.

## Geographic consistency

- **info**: Dala Hill (place #588, PLACE-DALA-HILL) is located in Dala, outside this state (linked through a related record).
- **info**: Durbi-Takusheyi (place #755, PLACE-DURBI-TAKUSHEYI) is located in Katsina State, outside this state (linked through a related record).
- **info**: Emir of Gombe's Palace (place #550, PLACE-EMIR-OF-GOMBE-S-PALACE) is located in Gombe, outside this state (linked through a related record).
- **info**: Giant Tombs of the Zamfara Rulers, Zurmi (place #830, PLACE-GIANT-TOMBS-OF-THE-ZAMFARA-RULERS-ZURMI) is located in Zurmi, outside this state (linked through a related record).
- **info**: Gidan Makama Museum (place #584, PLACE-GIDAN-MAKAMA-MUSEUM) is located in Kano State, outside this state (linked through a related record).
- **info**: Gidan Rumfa (place #586, PLACE-GIDAN-RUMFA) is located in Kano State, outside this state (linked through a related record).
- **info**: Gobarau Minaret (place #754, PLACE-GOBARAU-MINARET) is located in Katsina, outside this state (linked through a related record).
- **info**: Jata (place #832, PLACE-JATA) is located in Zamfara State, outside this state (linked through a related record).
- **info**: Kano City Walls and Gates (place #585, PLACE-KANO-CITY-WALLS-AND-GATES) is located in Kano State, outside this state (linked through a related record).
- **info**: Kurmi Market (place #587, PLACE-KURMI-MARKET) is located in Kano State, outside this state (linked through a related record).
- **info**: Kusugu Well (place #758, PLACE-KUSUGU-WELL) is located in Daura, outside this state (linked through a related record).
- **info**: Mbormi Battle Ground (place #548, PLACE-MBORMI-BATTLE-GROUND) is located in Funakaye, outside this state (linked through a related record).
- **info**: Namoda Tomb, Kaura Namoda (place #831, PLACE-NAMODA-TOMB-KAURA-NAMODA) is located in Kaura-Namoda, outside this state (linked through a related record).
- **info**: National Museum Fombina (place #314, PLACE-NATIONAL-MUSEUM-FOMBINA) is located in Yola South, outside this state (linked through a related record).
- **info**: Agege is not linked to any traditional council or intermediate area.
- **info**: Ajeromi-Ifelodun is not linked to any traditional council or intermediate area.
- **info**: Alimosho is not linked to any traditional council or intermediate area.
- **info**: Amuwo-Odofin is not linked to any traditional council or intermediate area.
- **info**: Apapa is not linked to any traditional council or intermediate area.
- **info**: Badagry is not linked to any traditional council or intermediate area.
- **info**: Epe is not linked to any traditional council or intermediate area.
- **info**: Eti Osa is not linked to any traditional council or intermediate area.
- **info**: Ibeju-Lekki is not linked to any traditional council or intermediate area.
- **info**: Ifako-Ijaye is not linked to any traditional council or intermediate area.
- **info**: Ikeja is not linked to any traditional council or intermediate area.
- **info**: Ikorodu is not linked to any traditional council or intermediate area.
- **info**: Kosofe is not linked to any traditional council or intermediate area.
- **info**: Lagos Island is not linked to any traditional council or intermediate area.
- **info**: Lagos Mainland is not linked to any traditional council or intermediate area.
- **info**: Mushin is not linked to any traditional council or intermediate area.
- **info**: Ojo is not linked to any traditional council or intermediate area.
- **info**: Oshodi-Isolo is not linked to any traditional council or intermediate area.
- **info**: Shomolu is not linked to any traditional council or intermediate area.
- **info**: Surulere is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Lagos State: 2 different population figures for 2006 (9013534,9113605). All are kept side by side, as the rules require.
- **info**: Lagos State: 2 different area_km2 figures for no year (3475,3671). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).
- **info**: Open dispute #7: Kano emirship: Muhammadu Sanusi II or Aminu Ado Bayero.

## Missing LGAs / coverage

- **check**: Ajeromi-Ifelodun has no ethnic group linked to it.
- **check**: Amuwo-Odofin has no ethnic group linked to it.
- **check**: Apapa has no ethnic group linked to it.
- **check**: Eti Osa has no ethnic group linked to it.
- **check**: Lagos Mainland has no ethnic group linked to it.
- **check**: Surulere has no ethnic group linked to it.
- **info**: Agege has only 74 words of text (no LGA history yet).
- **info**: Ajeromi-Ifelodun has only 68 words of text (no LGA history yet).
- **info**: Alimosho has only 93 words of text (no LGA history yet).
- **info**: Amuwo-Odofin has only 61 words of text (no LGA history yet).
- **info**: Apapa has only 60 words of text (no LGA history yet).
- **info**: Epe has only 87 words of text (no LGA history yet).
- **info**: Eti Osa has only 67 words of text (no LGA history yet).
- **info**: Ibeju-Lekki has only 98 words of text (no LGA history yet).
- **info**: Ifako-Ijaye has only 84 words of text (no LGA history yet).
- **info**: Ikeja has only 84 words of text (no LGA history yet).
- **info**: Ikorodu has only 77 words of text (no LGA history yet).
- **info**: Kosofe has only 79 words of text (no LGA history yet).
- **info**: Lagos Mainland has only 71 words of text (no LGA history yet).
- **info**: Mushin has only 74 words of text (no LGA history yet).
- **info**: Ojo has only 89 words of text (no LGA history yet).
- **info**: Oshodi-Isolo has only 82 words of text (no LGA history yet).
- **info**: Shomolu has only 77 words of text (no LGA history yet).
- **info**: Surulere has only 74 words of text (no LGA history yet).
- **info**: 3 of 20 LGAs have no headquarters recorded.

## Classification problems

- **info**: Igbo (ethnic group #21, ETH-IGBO) has no 'speaks' link to a language.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
- **info**: Open gap #940: Bauchi: Damlanci classification.
