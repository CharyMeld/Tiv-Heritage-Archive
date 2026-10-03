# Borno State — Phase 4 quality control

Run 1 October 2026 on the **live** database, read-only (nothing was changed), after batches 063–067. The script is `database/research/qc_state.php borno`, the same one used for the other states.

## Verdict

- **No errors found (0 problems)** and **no classification checks**. There are no duplicates, no unsourced or over-graded claims, and no source-quality issues.
- **Coverage went from nothing to near-full in one day:**

| | Before (baseline, 1 Oct) | Now |
|---|---|---|
| Ethnic groups linked | 0 | **25**: 14 new (Shuwa Arabs, Bura, Kibaku, Mandara, Malgwa, Buduma, Kanembu, Afade, Dghwede, Glavda, Guduf, Lamang, Mafa, Tera) plus Kanuri, Hausa, Fulani, Marghi, Gude, Dera, Kamwe, Kilba, Hwana, Ga'anda and Sukur |
| Languages linked | 0 | **26**: 18 new (all with Glottocodes) plus 8 existing; new Saharan and Semitic branches |
| Traditional institutions | 0 | **17**: the 9 Borno emirates (Borno, Dikwa, Bama, Biu, Gwoza, Askira, Uba, Damboa, Shani), plus 8 linked through other states' records |
| Cultural records | 0 | **21**: the Kanem-Borno Cultural Summit, plus 20 linked through the Hausa and Fulani records of other states |
| Places | 1 (Maiduguri) | **36**: Rabeh's Fort (declared No. 13), two proposed monuments, the National Museum Maiduguri, Kukawa, Chad Basin National Park, Sambisa Forest, 25 headquarters towns, and 3 Adamawa places linked through shared records |
| Wards | 0 | **312** (INEC, 2015) |
| Links | 4 | **157** |
| LGAs with peoples linked | 0 | **21 of 27** |
| LGAs with a description and headquarters | 0 | **27 of 27** |

- **Six checks:** Abadam, Gubio, Mafa, Magumeri, Marte and Mobbar have no people linked.
  - These are the northern Lake Chad and Borno Emirate LGAs.
  - No source read names their peoples. The federal profile says only that the Kanuri live in "quite a number of LGAs".
  - Nothing was guessed. These remain a real gap until a state or ethnographic source is found.
- **Info notes:**
  1. **Missing other names.** 9 peoples and 3 languages have none. Few are available from the sources already read; the Atlas gives the Kanuri's own name, Kànúrí, and Beriberi (field 2.C).
  2. **LGAs not linked to a council.** All 27 LGAs show "not linked to any traditional council". The emirates are linked to their seat LGAs, and Wikipedia's jurisdiction lists are stated in their texts. This is the same as for Kogi and Adamawa.
  3. **Short LGA texts.** 15 LGAs have under 100 words. The sources (Statoids, the state table, INEC and short Wikipedia articles) give little more.
  4. **Contradictory figures.** Two state population figures for 2006 and two state areas are kept side by side.
  5. **Hausa has no "speaks" link.** There is no Hausa language record yet; this is a national gap.
  6. **Items from other states.** The Adamawa places (Sukur, Fombina, Hamayaji) and the open disputes and gaps appear only through shared links.
- **Corrections made during the Borno work:**
  - The Atlas uses Borno's pre-1991 borders, so its Yobe LGAs were excluded.
  - Mandara–Konduga was downgraded to *reported*.
  - The Shehu of Dikwa's death is dated 23 January 2021 (2020 and 2026 were rejected).
  - Five errors in the state government's table of headquarters were overridden.
- **Is Borno complete?** Not yet. By the brief's rule it now moves to *quality control in progress*. Its progress record on live still reads "not_started, 0/27", which is out of date.

## Proposal: batch 068, Borno names and progress (small, like 056 for Kogi and 062 for Adamawa)

- **Names for the Kanuri people record**, from the Atlas's Kanuri entry:
  - **Kànúrí**: their own name (field 1.C)
  - **Beriberi**: an outside name (field 2.C)
- **fix_068:** sets Borno's research progress to 27/27 LGAs researched, quality control in progress, with a summary of batches 063–068.

## Later, when sources are found

- the peoples of Abadam, Gubio, Mafa, Magumeri, Marte and Mobbar
- festivals of Borno's peoples
- grades of the Borno emirates, and the state council of chiefs
- the rulers of Damboa and Shani
- the Bayo and Ngala headquarters
- LGA creation dates
- a Pabir record
- a Hausa language record (national)

## Full report (as generated)

Read-only check run 1 October 2026, 10:54. Nothing was changed.

**Scope:** the state, its 27 LGAs and 312 wards, and every record linked to them: 25 ethnic groups, 26 languages, 17 polities, 21 cultural records, 36 places, 157 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 12 |
| Geographic consistency | 0 | 0 | 30 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 5 |
| Missing LGAs / coverage | 0 | 6 | 15 |
| Classification problems | 0 | 0 | 5 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Afade (ethnic group #234, ETH-AFADE) has no other names recorded.
- **info**: Dghwede (ethnic group #235, ETH-DGHWEDE) has no other names recorded.
- **info**: Fulani (ethnic group #51, ETH-FULANI) has no other names recorded.
- **info**: Ga'anda (ethnic group #197, ETH-GA-ANDA) has no other names recorded.
- **info**: Glavda (ethnic group #236, ETH-GLAVDA) has no other names recorded.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no other names recorded.
- **info**: Kanembu (ethnic group #233, ETH-KANEMBU) has no other names recorded.
- **info**: Kanuri (ethnic group #52, ETH-KANURI) has no other names recorded.
- **info**: Tera (ethnic group #240, ETH-TERA) has no other names recorded.
- **info**: Cinene (language #513, LANG-CINENE) has no other names recorded.
- **info**: Jilbe (language #518, LANG-JILBE) has no other names recorded.
- **info**: Vemgo–Mabas (language #480, LANG-VEMGO-MABAS) has no other names recorded.

## Geographic consistency

- **info**: Hamayaji Old Palace (place #319, PLACE-HAMAYAJI-OLD-PALACE) is located in Madagali, outside this state (linked through a related record).
- **info**: National Museum Fombina (place #314, PLACE-NATIONAL-MUSEUM-FOMBINA) is located in Yola South, outside this state (linked through a related record).
- **info**: Sukur Cultural Landscape (place #311, PLACE-SUKUR-CULTURAL-LANDSCAPE) is located in Madagali, outside this state (linked through a related record).
- **info**: Abadam is not linked to any traditional council or intermediate area.
- **info**: Askira/Uba is not linked to any traditional council or intermediate area.
- **info**: Bama is not linked to any traditional council or intermediate area.
- **info**: Bayo is not linked to any traditional council or intermediate area.
- **info**: Biu is not linked to any traditional council or intermediate area.
- **info**: Chibok is not linked to any traditional council or intermediate area.
- **info**: Damboa is not linked to any traditional council or intermediate area.
- **info**: Dikwa is not linked to any traditional council or intermediate area.
- **info**: Gubio is not linked to any traditional council or intermediate area.
- **info**: Guzamala is not linked to any traditional council or intermediate area.
- **info**: Gwoza is not linked to any traditional council or intermediate area.
- **info**: Hawul is not linked to any traditional council or intermediate area.
- **info**: Jere is not linked to any traditional council or intermediate area.
- **info**: Kaga is not linked to any traditional council or intermediate area.
- **info**: Kala/Balge is not linked to any traditional council or intermediate area.
- **info**: Konduga is not linked to any traditional council or intermediate area.
- **info**: Kukawa is not linked to any traditional council or intermediate area.
- **info**: Kwaya Kusar is not linked to any traditional council or intermediate area.
- **info**: Mafa is not linked to any traditional council or intermediate area.
- **info**: Magumeri is not linked to any traditional council or intermediate area.
- **info**: Maiduguri is not linked to any traditional council or intermediate area.
- **info**: Marte is not linked to any traditional council or intermediate area.
- **info**: Mobbar is not linked to any traditional council or intermediate area.
- **info**: Monguno is not linked to any traditional council or intermediate area.
- **info**: Ngala is not linked to any traditional council or intermediate area.
- **info**: Nganzai is not linked to any traditional council or intermediate area.
- **info**: Shani is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Borno State: 2 different population figures for 2006 (4151193,4171104). All are kept side by side, as the rules require.
- **info**: Borno State: 2 different area_km2 figures for no year (72609,72767). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).

## Missing LGAs / coverage

- **check**: Abadam has no ethnic group linked to it.
- **check**: Gubio has no ethnic group linked to it.
- **check**: Mafa has no ethnic group linked to it.
- **check**: Magumeri has no ethnic group linked to it.
- **check**: Marte has no ethnic group linked to it.
- **check**: Mobbar has no ethnic group linked to it.
- **info**: Abadam has only 93 words of text (no LGA history yet).
- **info**: Gubio has only 99 words of text (no LGA history yet).
- **info**: Guzamala has only 87 words of text (no LGA history yet).
- **info**: Hawul has only 88 words of text (no LGA history yet).
- **info**: Jere has only 91 words of text (no LGA history yet).
- **info**: Kaga has only 86 words of text (no LGA history yet).
- **info**: Kala/Balge has only 89 words of text (no LGA history yet).
- **info**: Kukawa has only 91 words of text (no LGA history yet).
- **info**: Mafa has only 84 words of text (no LGA history yet).
- **info**: Magumeri has only 68 words of text (no LGA history yet).
- **info**: Marte has only 88 words of text (no LGA history yet).
- **info**: Mobbar has only 66 words of text (no LGA history yet).
- **info**: Monguno has only 82 words of text (no LGA history yet).
- **info**: Nganzai has only 86 words of text (no LGA history yet).
- **info**: Shani has only 80 words of text (no LGA history yet).

## Classification problems

- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no 'speaks' link to a language.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
