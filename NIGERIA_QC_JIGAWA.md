# Jigawa State — Phase 4 quality control

Run 2 October 2026 on the **live** database, read-only (nothing was changed), after batches 093–097. The script is `database/research/qc_state.php jigawa`.

## Verdict

- **No errors found (0 problems).**
- **What Jigawa had at the start of today, and what it has now:**

| | Start of today | Now |
|---|---|---|
| Languages linked | 1 (Hausa) | **9**: 3 extinct Bade-group languages (Auyokawa, Shira, Teshena) plus Hausa, Bade, Kanuri, Warji, Uled Suliman Arabic and Duwai |
| Ethnic groups linked | 0 | **11**: Hausa, Fulani, Manga, Ngizim, Bade, Kanuri, Warji, and Tiv, Yoruba, Igbo and Igala at Hadejia |
| Traditional institutions | 0 | the **5 emirates** (Dutse, Hadejia, Gumel, Kazaure, Ringim), plus others linked through shared records |
| Places | 1 (Dutse) | 4 declared rock-art monuments, the Birnin Kudu old settlement, the National Museum Birnin Kudu, Baturiya Wetland, and **26 headquarters towns** |
| Wards | 0 | **287** (INEC, 2015) |
| LGAs with a description and headquarters | 0 | **27 of 27** |
| LGAs with peoples linked | 0 | **14 of 27** |

## The checks

- **13 LGAs have no people linked:** Babura, Buji, Gagarawa, Garki, Gwaram, Gwiwa, Kafin Hausa, Kiyawa, Miga, Ringim, Roni, Sule Tankarkar and Yankwashi. No source read names their peoples. This remains a gap.
- **Shira (#669) has no Glottocode.** Glottolog's Shira family (shir1276) contains Auyokawa and Teshenawa, but not the Shira language itself. This is a real gap.

## Info notes

- **Missing other names:** the Manga, Ngizim and Bade have none. The federal profile gives Mangawa, Ngizimawa and Badawa, so batch 098 adds them.
- **The Igbo record has no "speaks" link:** there is no Igbo language record yet. This is a national gap.
- **Contradictory figures:** two state population figures for 2006 and two state areas are kept side by side.
- **Progress record:** the live progress row (#54) still reads "not_started, 0/27".

## Batch 098 + fix_098, Jigawa names and progress (built and tested)

- **Batch 098:** Mangawa, Ngizimawa and Badawa. The Badawa name carries a note that in Bauchi the Atlas uses it for the Mbat.
- **fix_098:** Jigawa research progress → **27/27 LGAs researched, quality control in progress**.
- **Expected QC after:** 0 problems; alias notes fall from 5 to 2.

## Full report (as generated)

Read-only check run 2 October 2026, 13:16. Nothing was changed.

**Scope:** the state, its 27 LGAs and 287 wards, and every record linked to them: 11 ethnic groups, 9 languages, 28 polities, 10 cultural records, 44 places, 73 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 5 |
| Geographic consistency | 0 | 0 | 37 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 6 |
| Missing LGAs / coverage | 0 | 13 | 26 |
| Classification problems | 0 | 1 | 6 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Bade (ethnic group #250, ETH-BADE) has no other names recorded.
- **info**: Manga (ethnic group #252, ETH-MANGA) has no other names recorded.
- **info**: Ngizim (ethnic group #249, ETH-NGIZIM) has no other names recorded.
- **info**: Yoruba (ethnic group #64, ETH-YORUBA) has no other names recorded.
- **info**: Auyokawa (language #668, LANG-AUYOKAWA) has no other names recorded.

## Geographic consistency

- **info**: Dala Hill (place #588, PLACE-DALA-HILL) is located in Dala, outside this state (linked through a related record).
- **info**: Emir of Gombe's Palace (place #550, PLACE-EMIR-OF-GOMBE-S-PALACE) is located in Gombe, outside this state (linked through a related record).
- **info**: Gidan Makama Museum (place #584, PLACE-GIDAN-MAKAMA-MUSEUM) is located in Kano State, outside this state (linked through a related record).
- **info**: Gidan Rumfa (place #586, PLACE-GIDAN-RUMFA) is located in Kano State, outside this state (linked through a related record).
- **info**: Kano City Walls and Gates (place #585, PLACE-KANO-CITY-WALLS-AND-GATES) is located in Kano State, outside this state (linked through a related record).
- **info**: Kurmi Market (place #587, PLACE-KURMI-MARKET) is located in Kano State, outside this state (linked through a related record).
- **info**: Mbormi Battle Ground (place #548, PLACE-MBORMI-BATTLE-GROUND) is located in Funakaye, outside this state (linked through a related record).
- **info**: National Museum Fombina (place #314, PLACE-NATIONAL-MUSEUM-FOMBINA) is located in Yola South, outside this state (linked through a related record).
- **info**: Ojogwu Atogwu Tumulus (place #249, PLACE-OJOGWU-ATOGWU-TUMULUS) is located in Idah, outside this state (linked through a related record).
- **info**: Tor Tiv Palace (place #82, PLACE-TOR-TIV-PALACE) is located in Gboko, outside this state (linked through a related record).
- **info**: Auyo is not linked to any traditional council or intermediate area.
- **info**: Babura is not linked to any traditional council or intermediate area.
- **info**: Biriniwa is not linked to any traditional council or intermediate area.
- **info**: Birnin Kudu is not linked to any traditional council or intermediate area.
- **info**: Buji is not linked to any traditional council or intermediate area.
- **info**: Dutse is not linked to any traditional council or intermediate area.
- **info**: Gagarawa is not linked to any traditional council or intermediate area.
- **info**: Garki is not linked to any traditional council or intermediate area.
- **info**: Gumel is not linked to any traditional council or intermediate area.
- **info**: Guri is not linked to any traditional council or intermediate area.
- **info**: Gwaram is not linked to any traditional council or intermediate area.
- **info**: Gwiwa is not linked to any traditional council or intermediate area.
- **info**: Hadejia is not linked to any traditional council or intermediate area.
- **info**: Jahun is not linked to any traditional council or intermediate area.
- **info**: Kafin Hausa is not linked to any traditional council or intermediate area.
- **info**: Kaugama is not linked to any traditional council or intermediate area.
- **info**: Kazaure is not linked to any traditional council or intermediate area.
- **info**: Kiri Kasama is not linked to any traditional council or intermediate area.
- **info**: Kiyawa is not linked to any traditional council or intermediate area.
- **info**: Maigatari is not linked to any traditional council or intermediate area.
- **info**: Malam Madori is not linked to any traditional council or intermediate area.
- **info**: Miga is not linked to any traditional council or intermediate area.
- **info**: Ringim is not linked to any traditional council or intermediate area.
- **info**: Roni is not linked to any traditional council or intermediate area.
- **info**: Sule Tankarkar is not linked to any traditional council or intermediate area.
- **info**: Taura is not linked to any traditional council or intermediate area.
- **info**: Yankwashi is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Jigawa State: 2 different population figures for 2006 (4348649,4361002). All are kept side by side, as the rules require.
- **info**: Jigawa State: 2 different area_km2 figures for no year (22410,23415). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).
- **info**: Open dispute #7: Kano emirship: Muhammadu Sanusi II or Aminu Ado Bayero.

## Missing LGAs / coverage

- **check**: Babura has no ethnic group linked to it.
- **check**: Buji has no ethnic group linked to it.
- **check**: Gagarawa has no ethnic group linked to it.
- **check**: Garki has no ethnic group linked to it.
- **check**: Gwaram has no ethnic group linked to it.
- **check**: Gwiwa has no ethnic group linked to it.
- **check**: Kafin Hausa has no ethnic group linked to it.
- **check**: Kiyawa has no ethnic group linked to it.
- **check**: Miga has no ethnic group linked to it.
- **check**: Ringim has no ethnic group linked to it.
- **check**: Roni has no ethnic group linked to it.
- **check**: Sule Tankarkar has no ethnic group linked to it.
- **check**: Yankwashi has no ethnic group linked to it.
- **info**: Auyo has only 90 words of text (no LGA history yet).
- **info**: Babura has only 81 words of text (no LGA history yet).
- **info**: Biriniwa has only 79 words of text (no LGA history yet).
- **info**: Buji has only 61 words of text (no LGA history yet).
- **info**: Dutse has only 95 words of text (no LGA history yet).
- **info**: Gagarawa has only 62 words of text (no LGA history yet).
- **info**: Garki has only 85 words of text (no LGA history yet).
- **info**: Gumel has only 96 words of text (no LGA history yet).
- **info**: Guri has only 77 words of text (no LGA history yet).
- **info**: Gwaram has only 66 words of text (no LGA history yet).
- **info**: Gwiwa has only 73 words of text (no LGA history yet).
- **info**: Hadejia has only 81 words of text (no LGA history yet).
- **info**: Jahun has only 62 words of text (no LGA history yet).
- **info**: Kafin Hausa has only 86 words of text (no LGA history yet).
- **info**: Kaugama has only 69 words of text (no LGA history yet).
- **info**: Kazaure has only 79 words of text (no LGA history yet).
- **info**: Kiri Kasama has only 76 words of text (no LGA history yet).
- **info**: Kiyawa has only 70 words of text (no LGA history yet).
- **info**: Maigatari has only 90 words of text (no LGA history yet).
- **info**: Malam Madori has only 97 words of text (no LGA history yet).
- **info**: Miga has only 65 words of text (no LGA history yet).
- **info**: Ringim has only 66 words of text (no LGA history yet).
- **info**: Roni has only 66 words of text (no LGA history yet).
- **info**: Sule Tankarkar has only 63 words of text (no LGA history yet).
- **info**: Taura has only 71 words of text (no LGA history yet).
- **info**: Yankwashi has only 55 words of text (no LGA history yet).

## Classification problems

- **check**: Shira (language #669, LANG-SHIRA) has no Glottocode.
- **info**: Igbo (ethnic group #21, ETH-IGBO) has no 'speaks' link to a language.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
- **info**: Open gap #940: Bauchi: Damlanci classification.
