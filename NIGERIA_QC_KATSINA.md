# Katsina State — Phase 4 quality control

Run 2 October 2026 on the **live** database, read-only (nothing was changed), after batches 099–103. The script is `database/research/qc_state.php katsina`.

## Verdict

- **No errors found (0 problems).**
- **What Katsina had at the start of today, and what it has now:**

| | Start of today | Now |
|---|---|---|
| Languages linked | 1 (Hausa) | **2**: Hausa and Fulfulde. The Atlas places no minority language in Katsina. |
| Ethnic groups linked | 0 | **6**: Hausa and Fulani; Yoruba, Igbo, Kanuri and Nupe as smaller groups |
| Traditional institutions | 0 | the **Kingdom of Katsina** and the **Katsina** and **Daura** emirates, plus others linked through shared records |
| Places | 1 (Katsina) | 3 declared and 3 proposed NCMM monuments, the National Museum Katsina, and **33 headquarters towns** |
| Cultural records | 0 | the Katsina and Daura Durbar, plus others linked through the Hausa and Fulani |
| Wards | 0 | **361** (INEC, 2015) |
| LGAs with a description and headquarters | 0 | **34 of 34** |
| LGAs with peoples linked | 0 | **11 of 34** |

## The checks

- **23 LGAs have no people linked.** Neither the federal profile nor Wikipedia's LGA articles name their peoples. Katsina is overwhelmingly Hausa and Fulani, but no source read says so LGA by LGA. This remains a gap.

## Info notes

- **The Yoruba record has no other names**, and **the Igbo record has no "speaks" link** (there is no Igbo language record). Both are national gaps, outside Katsina's research.
- **Contradictory figures:** two state population figures for 2006 and two state areas are kept side by side.
- **No names batch is needed.** The sources read give no new other names for Katsina's peoples.
- **Progress record:** the live progress row (#57) still reads "not_started, 0/34".

## fix_104, Katsina progress (built and tested)

- Katsina research progress → **34/34 LGAs researched, quality control in progress**, with a summary of batches 099–104.
- **Test on a fresh copy of live:**
  - The fix applied once; a second run made 0 changes.
  - QC: 0 problems.
  - `--revert --apply` left every table and the progress rows identical to the live copy.

## Later, when sources are found

- the peoples of 23 LGAs, and the Maguzawa and Agalawa
- the Emir of Daura's status in 2026 (latest source: June 2025)
- descriptions of Gidan Yarima and Dutse Bamle
- 19 headquarters resting on Statoids alone

## Full report (as generated)

Read-only check run 2 October 2026, 13:56. Nothing was changed.

**Scope:** the state, its 34 LGAs and 361 wards, and every record linked to them: 6 ethnic groups, 2 languages, 22 polities, 10 cultural records, 49 places, 48 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 1 |
| Geographic consistency | 0 | 0 | 42 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 6 |
| Missing LGAs / coverage | 0 | 23 | 31 |
| Classification problems | 0 | 0 | 6 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Yoruba (ethnic group #64, ETH-YORUBA) has no other names recorded.

## Geographic consistency

- **info**: Dala Hill (place #588, PLACE-DALA-HILL) is located in Dala, outside this state (linked through a related record).
- **info**: Emir of Gombe's Palace (place #550, PLACE-EMIR-OF-GOMBE-S-PALACE) is located in Gombe, outside this state (linked through a related record).
- **info**: Gidan Makama Museum (place #584, PLACE-GIDAN-MAKAMA-MUSEUM) is located in Kano State, outside this state (linked through a related record).
- **info**: Gidan Rumfa (place #586, PLACE-GIDAN-RUMFA) is located in Kano State, outside this state (linked through a related record).
- **info**: Kano City Walls and Gates (place #585, PLACE-KANO-CITY-WALLS-AND-GATES) is located in Kano State, outside this state (linked through a related record).
- **info**: Kurmi Market (place #587, PLACE-KURMI-MARKET) is located in Kano State, outside this state (linked through a related record).
- **info**: Mbormi Battle Ground (place #548, PLACE-MBORMI-BATTLE-GROUND) is located in Funakaye, outside this state (linked through a related record).
- **info**: National Museum Fombina (place #314, PLACE-NATIONAL-MUSEUM-FOMBINA) is located in Yola South, outside this state (linked through a related record).
- **info**: Bakori is not linked to any traditional council or intermediate area.
- **info**: Batagarawa is not linked to any traditional council or intermediate area.
- **info**: Batsari is not linked to any traditional council or intermediate area.
- **info**: Baure is not linked to any traditional council or intermediate area.
- **info**: Bindawa is not linked to any traditional council or intermediate area.
- **info**: Charanchi is not linked to any traditional council or intermediate area.
- **info**: Dan Musa is not linked to any traditional council or intermediate area.
- **info**: Dandume is not linked to any traditional council or intermediate area.
- **info**: Danja is not linked to any traditional council or intermediate area.
- **info**: Daura is not linked to any traditional council or intermediate area.
- **info**: Dutsi is not linked to any traditional council or intermediate area.
- **info**: Dutsin Ma is not linked to any traditional council or intermediate area.
- **info**: Faskari is not linked to any traditional council or intermediate area.
- **info**: Funtua is not linked to any traditional council or intermediate area.
- **info**: Ingawa is not linked to any traditional council or intermediate area.
- **info**: Jibia is not linked to any traditional council or intermediate area.
- **info**: Kafur is not linked to any traditional council or intermediate area.
- **info**: Kaita is not linked to any traditional council or intermediate area.
- **info**: Kankara is not linked to any traditional council or intermediate area.
- **info**: Kankia is not linked to any traditional council or intermediate area.
- **info**: Katsina is not linked to any traditional council or intermediate area.
- **info**: Kurfi is not linked to any traditional council or intermediate area.
- **info**: Kusada is not linked to any traditional council or intermediate area.
- **info**: Mai'Adua is not linked to any traditional council or intermediate area.
- **info**: Malumfashi is not linked to any traditional council or intermediate area.
- **info**: Mani is not linked to any traditional council or intermediate area.
- **info**: Mashi is not linked to any traditional council or intermediate area.
- **info**: Matazu is not linked to any traditional council or intermediate area.
- **info**: Musawa is not linked to any traditional council or intermediate area.
- **info**: Rimi is not linked to any traditional council or intermediate area.
- **info**: Sabuwa is not linked to any traditional council or intermediate area.
- **info**: Safana is not linked to any traditional council or intermediate area.
- **info**: Sandamu is not linked to any traditional council or intermediate area.
- **info**: Zango is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Katsina State: 2 different population figures for 2006 (5792578,5801584). All are kept side by side, as the rules require.
- **info**: Katsina State: 2 different area_km2 figures for no year (23822,23938). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).
- **info**: Open dispute #7: Kano emirship: Muhammadu Sanusi II or Aminu Ado Bayero.

## Missing LGAs / coverage

- **check**: Bakori has no ethnic group linked to it.
- **check**: Batagarawa has no ethnic group linked to it.
- **check**: Batsari has no ethnic group linked to it.
- **check**: Baure has no ethnic group linked to it.
- **check**: Bindawa has no ethnic group linked to it.
- **check**: Charanchi has no ethnic group linked to it.
- **check**: Dan Musa has no ethnic group linked to it.
- **check**: Dandume has no ethnic group linked to it.
- **check**: Jibia has no ethnic group linked to it.
- **check**: Kafur has no ethnic group linked to it.
- **check**: Kaita has no ethnic group linked to it.
- **check**: Kankara has no ethnic group linked to it.
- **check**: Kurfi has no ethnic group linked to it.
- **check**: Mai'Adua has no ethnic group linked to it.
- **check**: Malumfashi has no ethnic group linked to it.
- **check**: Mashi has no ethnic group linked to it.
- **check**: Matazu has no ethnic group linked to it.
- **check**: Musawa has no ethnic group linked to it.
- **check**: Rimi has no ethnic group linked to it.
- **check**: Sabuwa has no ethnic group linked to it.
- **check**: Safana has no ethnic group linked to it.
- **check**: Sandamu has no ethnic group linked to it.
- **check**: Zango has no ethnic group linked to it.
- **info**: Bakori has only 74 words of text (no LGA history yet).
- **info**: Batagarawa has only 67 words of text (no LGA history yet).
- **info**: Batsari has only 64 words of text (no LGA history yet).
- **info**: Baure has only 60 words of text (no LGA history yet).
- **info**: Bindawa has only 62 words of text (no LGA history yet).
- **info**: Charanchi has only 68 words of text (no LGA history yet).
- **info**: Dan Musa has only 69 words of text (no LGA history yet).
- **info**: Dandume has only 70 words of text (no LGA history yet).
- **info**: Danja has only 94 words of text (no LGA history yet).
- **info**: Daura has only 95 words of text (no LGA history yet).
- **info**: Dutsi has only 76 words of text (no LGA history yet).
- **info**: Dutsin Ma has only 95 words of text (no LGA history yet).
- **info**: Funtua has only 84 words of text (no LGA history yet).
- **info**: Ingawa has only 67 words of text (no LGA history yet).
- **info**: Jibia has only 75 words of text (no LGA history yet).
- **info**: Kafur has only 60 words of text (no LGA history yet).
- **info**: Kaita has only 63 words of text (no LGA history yet).
- **info**: Kankara has only 73 words of text (no LGA history yet).
- **info**: Kankia has only 88 words of text (no LGA history yet).
- **info**: Kurfi has only 69 words of text (no LGA history yet).
- **info**: Kusada has only 80 words of text (no LGA history yet).
- **info**: Mai'Adua has only 68 words of text (no LGA history yet).
- **info**: Malumfashi has only 62 words of text (no LGA history yet).
- **info**: Mashi has only 62 words of text (no LGA history yet).
- **info**: Matazu has only 65 words of text (no LGA history yet).
- **info**: Musawa has only 53 words of text (no LGA history yet).
- **info**: Rimi has only 53 words of text (no LGA history yet).
- **info**: Sabuwa has only 62 words of text (no LGA history yet).
- **info**: Safana has only 61 words of text (no LGA history yet).
- **info**: Sandamu has only 53 words of text (no LGA history yet).
- **info**: Zango has only 60 words of text (no LGA history yet).

## Classification problems

- **info**: Igbo (ethnic group #21, ETH-IGBO) has no 'speaks' link to a language.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
- **info**: Open gap #940: Bauchi: Damlanci classification.
