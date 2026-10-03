# Ogun State — Phase 4 quality control

Run 2 October 2026 on the **live** database, read-only (nothing was changed), after batches 117–121. The script is `database/research/qc_state.php ogun`.

## Verdict

- **No errors found:** 0 problems and **0 checks**.
- **What Ogun had before batch 117, and what it has now:**

| | Before batch 117 | Now |
|---|---|---|
| Languages linked | 1 (Yoruba) | **2**: Yoruba, and **Gun** in Ipokia and Imeko Afon |
| Ethnic groups linked | 0 | **2**: Yoruba (sub-groups Egba, Ijebu, Remo, Yewa, Awori, Ketu, Ohori, Anago, Ikale and Ilaje in the notes) and the **Ogu (Egun)** |
| Traditional institutions | 0 | **Egbaland** (Alake), the **Ijebu Kingdom** (Awujale, vacant), **Remo** (Akarigbo), the **Olu of Ilaro** and the **Olota of Ota** |
| Places | 1 (Abeokuta) | Sungbo's Shrine (declared), Sungbo's Eredo, Olumo Rock and Centenary Hall (proposed), the National Museum Abeokuta and **20 headquarters towns** |
| Cultural records | 0 | the **Ojude Oba** and **Agemo** festivals, plus others linked through shared records |
| Wards | 0 | **236** (INEC, 2015) |
| LGAs with a description and headquarters | 0 | **20 of 20** |
| LGAs with peoples linked | 0 | **20 of 20** |

## Info notes

- **Contradictory figures:** state population and area figures from different sources are kept side by side.
- **National gaps outside Ogun:** the Yoruba record has no other names, and the Igbo record has no "speaks" link.
- **No names batch is needed.** The names for the kingdoms, headquarters towns and sites went in with batches 119–121.
- **Progress record:** the live progress row (#64) still reads "not_started, 0/20".
- **LGA names:** the archive still calls two LGAs "Egbado North" and "Egbado South". The federal profile and Wikipedia call them Yewa North and Yewa South.

## fix_122, Ogun progress and the Yewa names (built and tested)

- **A.** Ogun research progress → **20/20 LGAs researched, quality control in progress**, with a summary of batches 117–122.
- **B.** The two LGAs are renamed **Yewa North** and **Yewa South**. This follows your keep-both-names rule:
  - **The old names are kept** as historical names on each LGA ("Egbado North", "Egbado South").
  - **Each rename is logged** as a 'renamed' administrative change. No source gives its date.
  - **The summaries say the 1999 Constitution's First Schedule still prints the Egbado names**, as do Statoids and INEC's lists.
  - **Page addresses and IDs do not change** (`/nigeria/states/ogun/lgas/egbado-north`).
- **Test on a fresh copy of live:**
  - The fix applied once (9 changes); a second run made 0 changes.
  - QC: 0 problems and 0 checks. The LGA pages show "Yewa North" and "Yewa South".
  - `--revert --apply` restored the names and summaries, deleted the inserted rows, and left every table identical to the live copy.

## Later, when sources are found

- the next Awujale (vacant since 13 July 2025; selection suspended in June 2026)
- the Remo North (Isara or Ilisan) and Obafemi Owode headquarters
- the LGAs of Sungbo's Shrine, Sungbo's Eredo, Olumo Rock, Centenary Hall and the National Museum Abeokuta
- the Egba section rulers (Osile, Agura, Olowu) and other obaships
- the Lisabi festival, adire cloth and Ake Palace
- records for the Yoruba sub-groups (a decision for the whole south-west)

## Full report (as generated)

Read-only check run 2 October 2026, 17:02. Nothing was changed.

**Scope:** the state, its 20 LGAs and 236 wards, and every record linked to them: 2 ethnic groups, 2 languages, 8 polities, 4 cultural records, 29 places, 40 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 1 |
| Geographic consistency | 0 | 0 | 23 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 6 |
| Missing LGAs / coverage | 0 | 0 | 18 |
| Classification problems | 0 | 0 | 5 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Yoruba (ethnic group #64, ETH-YORUBA) has no other names recorded.

## Geographic consistency

- **info**: Brazilian Barracoon and Point of No Return, Badagry (place #886, PLACE-BRAZILIAN-BARRACOON-AND-POINT-OF-NO-RETURN-BADAGRY) is located in Badagry, outside this state (linked through a related record).
- **info**: Iga Idunganran (place #877, PLACE-IGA-IDUNGANRAN) is located in Lagos Island, outside this state (linked through a related record).
- **info**: Vlekete Slave Market (place #892, PLACE-VLEKETE-SLAVE-MARKET) is located in Badagry, outside this state (linked through a related record).
- **info**: Abeokuta North is not linked to any traditional council or intermediate area.
- **info**: Abeokuta South is not linked to any traditional council or intermediate area.
- **info**: Ado-Odo/Ota is not linked to any traditional council or intermediate area.
- **info**: Egbado North is not linked to any traditional council or intermediate area.
- **info**: Egbado South is not linked to any traditional council or intermediate area.
- **info**: Ewekoro is not linked to any traditional council or intermediate area.
- **info**: Ifo is not linked to any traditional council or intermediate area.
- **info**: Ijebu East is not linked to any traditional council or intermediate area.
- **info**: Ijebu North is not linked to any traditional council or intermediate area.
- **info**: Ijebu North East is not linked to any traditional council or intermediate area.
- **info**: Ijebu Ode is not linked to any traditional council or intermediate area.
- **info**: Ikenne is not linked to any traditional council or intermediate area.
- **info**: Imeko Afon is not linked to any traditional council or intermediate area.
- **info**: Ipokia is not linked to any traditional council or intermediate area.
- **info**: Obafemi Owode is not linked to any traditional council or intermediate area.
- **info**: Odeda is not linked to any traditional council or intermediate area.
- **info**: Odogbolu is not linked to any traditional council or intermediate area.
- **info**: Ogun Waterside is not linked to any traditional council or intermediate area.
- **info**: Remo North is not linked to any traditional council or intermediate area.
- **info**: Shagamu is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Ogun State: 2 different population figures for 2006 (3728098,3751140). All are kept side by side, as the rules require.
- **info**: Ogun State: 2 different area_km2 figures for no year (16400,16850). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).
- **info**: Open dispute #7: Kano emirship: Muhammadu Sanusi II or Aminu Ado Bayero.

## Missing LGAs / coverage

- **info**: Abeokuta North has only 79 words of text (no LGA history yet).
- **info**: Abeokuta South has only 93 words of text (no LGA history yet).
- **info**: Ado-Odo/Ota has only 99 words of text (no LGA history yet).
- **info**: Egbado North has only 86 words of text (no LGA history yet).
- **info**: Egbado South has only 97 words of text (no LGA history yet).
- **info**: Ewekoro has only 89 words of text (no LGA history yet).
- **info**: Ifo has only 75 words of text (no LGA history yet).
- **info**: Ijebu East has only 87 words of text (no LGA history yet).
- **info**: Ijebu North has only 78 words of text (no LGA history yet).
- **info**: Ijebu North East has only 96 words of text (no LGA history yet).
- **info**: Ikenne has only 77 words of text (no LGA history yet).
- **info**: Imeko Afon has only 84 words of text (no LGA history yet).
- **info**: Ipokia has only 83 words of text (no LGA history yet).
- **info**: Obafemi Owode has only 86 words of text (no LGA history yet).
- **info**: Odeda has only 77 words of text (no LGA history yet).
- **info**: Odogbolu has only 75 words of text (no LGA history yet).
- **info**: Remo North has only 86 words of text (no LGA history yet).
- **info**: Shagamu has only 92 words of text (no LGA history yet).

## Classification problems

- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
- **info**: Open gap #940: Bauchi: Damlanci classification.
