# Zamfara State — Phase 4 quality control

Run 2 October 2026 on the **live** database, read-only (nothing was changed), after batches 105–109. The script is `database/research/qc_state.php zamfara`.

## Verdict

- **No errors found:** 0 problems and 0 checks.
- **What Zamfara had before batch 105, and what it has now:**

| | Before batch 105 | Now |
|---|---|---|
| Languages linked | 1 (Hausa, batch 087) | **1**. The Atlas places no minority language in Zamfara, only Hausa's western "Zamfarawa" dialect. |
| Ethnic groups linked | 0 | **2**: Hausa and Fulani. The Hausa sub-groups by LGA are in the link notes. |
| Traditional institutions | 0 | the **Kingdom of Zamfara** and the **Anka**, **Gusau** and **Shinkafi** emirates, plus others linked through shared records |
| Places | 1 (Gusau) | 2 proposed NCMM monuments (the Zurmi tombs and the Namoda tomb), Jata, and **13 headquarters towns** |
| Wards | 0 | **147** (INEC, 2015) |
| LGAs with a description and headquarters | 0 | **14 of 14** |
| LGAs with peoples linked | 0 | **14 of 14** |

## Info notes

- **There is no declared national monument and no national museum.** No Zamfara festival was found in the sources read.
- **LGA pages are short.** All 14 LGA pages are under 300 words, as in the other states, so they stay noindex and the sitemap is unchanged at 3,026.
- **Contradictory figures:** the two 2006 state populations and the three state areas are kept side by side.
- **No names batch is needed.** Hausa and Fulani own-names were added nationally in batch 092. Chafe and Magare are recorded in batch 109.
- **Progress record:** the live progress row (#73) still reads "not_started, 0/14".

## fix_110, Zamfara progress (built and tested)

- Zamfara research progress → **14/14 LGAs researched, quality control in progress**, with a summary of batches 105–110.
- **Test on a fresh copy of live:**
  - The fix applied once; a second run made 0 changes.
  - QC: 0 problems.
  - `--revert --apply` left every table and the progress rows identical to the live copy.

## Later, when sources are found

- the full list of Zamfara emirates (only Anka, Gusau and Shinkafi are well sourced)
- a source on each Hausa sub-group, and on any minority peoples in the state
- descriptions of the Zurmi tombs and the Namoda tomb, and the location of Jata
- 11 headquarters resting on Statoids alone

## Full report (as generated)

Read-only check run 2 October 2026, 14:38. Nothing was changed.

**Scope:** the state, its 14 LGAs and 147 wards, and every record linked to them: 2 ethnic groups, 1 languages, 24 polities, 9 cultural records, 28 places, 28 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 0 |
| Geographic consistency | 0 | 0 | 25 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 6 |
| Missing LGAs / coverage | 0 | 0 | 13 |
| Classification problems | 0 | 0 | 5 |

## Duplicates

No findings.

## Ethnic-name aliases

No findings.

## Geographic consistency

- **info**: Dala Hill (place #588, PLACE-DALA-HILL) is located in Dala, outside this state (linked through a related record).
- **info**: Durbi-Takusheyi (place #755, PLACE-DURBI-TAKUSHEYI) is located in Katsina State, outside this state (linked through a related record).
- **info**: Emir of Gombe's Palace (place #550, PLACE-EMIR-OF-GOMBE-S-PALACE) is located in Gombe, outside this state (linked through a related record).
- **info**: Gidan Makama Museum (place #584, PLACE-GIDAN-MAKAMA-MUSEUM) is located in Kano State, outside this state (linked through a related record).
- **info**: Gidan Rumfa (place #586, PLACE-GIDAN-RUMFA) is located in Kano State, outside this state (linked through a related record).
- **info**: Gobarau Minaret (place #754, PLACE-GOBARAU-MINARET) is located in Katsina, outside this state (linked through a related record).
- **info**: Kano City Walls and Gates (place #585, PLACE-KANO-CITY-WALLS-AND-GATES) is located in Kano State, outside this state (linked through a related record).
- **info**: Kurmi Market (place #587, PLACE-KURMI-MARKET) is located in Kano State, outside this state (linked through a related record).
- **info**: Kusugu Well (place #758, PLACE-KUSUGU-WELL) is located in Daura, outside this state (linked through a related record).
- **info**: Mbormi Battle Ground (place #548, PLACE-MBORMI-BATTLE-GROUND) is located in Funakaye, outside this state (linked through a related record).
- **info**: National Museum Fombina (place #314, PLACE-NATIONAL-MUSEUM-FOMBINA) is located in Yola South, outside this state (linked through a related record).
- **info**: Anka is not linked to any traditional council or intermediate area.
- **info**: Bakura is not linked to any traditional council or intermediate area.
- **info**: Birnin Magaji/Kiyaw is not linked to any traditional council or intermediate area.
- **info**: Bukkuyum is not linked to any traditional council or intermediate area.
- **info**: Bungudu is not linked to any traditional council or intermediate area.
- **info**: Gummi is not linked to any traditional council or intermediate area.
- **info**: Gusau is not linked to any traditional council or intermediate area.
- **info**: Kaura-Namoda is not linked to any traditional council or intermediate area.
- **info**: Maradun is not linked to any traditional council or intermediate area.
- **info**: Maru is not linked to any traditional council or intermediate area.
- **info**: Shinkafi is not linked to any traditional council or intermediate area.
- **info**: Talata-Mafara is not linked to any traditional council or intermediate area.
- **info**: Tsafe is not linked to any traditional council or intermediate area.
- **info**: Zurmi is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Zamfara State: 2 different population figures for 2006 (3259846,3278873). All are kept side by side, as the rules require.
- **info**: Zamfara State: 3 different area_km2 figures for no year (33667,37931,38418). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).
- **info**: Open dispute #7: Kano emirship: Muhammadu Sanusi II or Aminu Ado Bayero.

## Missing LGAs / coverage

- **info**: Anka has only 91 words of text (no LGA history yet).
- **info**: Bakura has only 76 words of text (no LGA history yet).
- **info**: Birnin Magaji/Kiyaw has only 99 words of text (no LGA history yet).
- **info**: Bukkuyum has only 73 words of text (no LGA history yet).
- **info**: Bungudu has only 75 words of text (no LGA history yet).
- **info**: Gummi has only 65 words of text (no LGA history yet).
- **info**: Kaura-Namoda has only 98 words of text (no LGA history yet).
- **info**: Maradun has only 65 words of text (no LGA history yet).
- **info**: Maru has only 74 words of text (no LGA history yet).
- **info**: Shinkafi has only 99 words of text (no LGA history yet).
- **info**: Talata-Mafara has only 84 words of text (no LGA history yet).
- **info**: Tsafe has only 73 words of text (no LGA history yet).
- **info**: Zurmi has only 86 words of text (no LGA history yet).

## Classification problems

- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
- **info**: Open gap #940: Bauchi: Damlanci classification.
