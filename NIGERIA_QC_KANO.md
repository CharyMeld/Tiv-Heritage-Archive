# Kano State — Phase 4 quality control

Run 2 October 2026 on the **live** database, read-only (nothing was changed), after batches 087–091. The script is `database/research/qc_state.php kano`.

## Verdict

- **No errors found (0 problems).**
- **What Kano had at the start of today, and what it has now:**

| | Start of today | Now |
|---|---|---|
| Languages linked | 0 | **3**: the new national Hausa record, Kurama (Tudun Wada) and Fulfulde |
| Ethnic groups linked | 0 | **4**: Hausa, Fulani, Igbo, Kanuri |
| Traditional institutions | 0 | **16**: the Kingdom of Kano, the Kano Emirate, Gaya, Rano, Karaye, Bichi, plus others linked through shared records |
| Cultural records | 0 | **9**: the Kano Durbar, plus others linked through the Hausa and Fulani |
| Places | 1 (Kano) | **58**: 3 declared and 6 proposed NCMM monuments, Gidan Rumfa, 44 headquarters towns, and others |
| Wards | 0 | **484** (INEC, 2015) |
| LGAs with a description and headquarters | 0 | **44 of 44** |
| LGAs with peoples linked | 0 | **13 of 44** |

## The checks

- **31 LGAs have no people linked.** Neither Wikipedia's LGA articles nor the federal profile name their peoples. Kano is overwhelmingly Hausa and Fulani, but no source read says so LGA by LGA. This remains a gap.

## Info notes

- **Missing other names:** the Hausa and Fulani have none. The Atlas gives their own names (field 1.C), so batch 092 adds them.
- **Open dispute #7:** the Kano emirship (batch 089). Its Supreme Court hearing is fixed for 19 April 2027.
- **Short LGA texts:** most LGAs have under 100 words.
- **Contradictory figures:** two state population figures for 2006 and two state areas are kept side by side.
- **The Igbo record has no "speaks" link:** there is no Igbo language record yet. This is a national gap.
- **Progress record:** the live progress row (#56) still reads "not_started, 0/44".

## Batch 092 + fix_092, Kano names and progress (built and tested)

- **Batch 092:** the Hausa's own name, Hàusàawáa (singular Bàháushèe), and the Fulani's, Fulɓe (singular Pullo), plus Filani. All come from the Atlas.
- **fix_092:** Kano research progress → **44/44 LGAs researched, quality control in progress**.
- **Expected QC after:** 0 problems. Kano's alias notes fall from 2 to 0, and other states' fall too (Gombe from 3 to 1).

## Later, when sources are found

- the peoples of the 31 unlinked LGAs, and the Maguzawa
- the emirship (the Supreme Court, 19 April 2027)
- 20 headquarters resting on Statoids alone; Makoda (Koguna?) and Kunchi/Ghari
- descriptions of the proposed monuments

## Full report (as generated)

Read-only check run 2 October 2026, 11:50. Nothing was changed.

**Scope:** the state, its 44 LGAs and 484 wards, and every record linked to them: 4 ethnic groups, 3 languages, 16 polities, 9 cultural records, 58 places, 49 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 2 |
| Geographic consistency | 0 | 0 | 47 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 6 |
| Missing LGAs / coverage | 0 | 31 | 42 |
| Classification problems | 0 | 0 | 6 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Fulani (ethnic group #51, ETH-FULANI) has no other names recorded.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no other names recorded.

## Geographic consistency

- **info**: Emir of Gombe's Palace (place #550, PLACE-EMIR-OF-GOMBE-S-PALACE) is located in Gombe, outside this state (linked through a related record).
- **info**: Mbormi Battle Ground (place #548, PLACE-MBORMI-BATTLE-GROUND) is located in Funakaye, outside this state (linked through a related record).
- **info**: National Museum Fombina (place #314, PLACE-NATIONAL-MUSEUM-FOMBINA) is located in Yola South, outside this state (linked through a related record).
- **info**: Ajingi is not linked to any traditional council or intermediate area.
- **info**: Albasu is not linked to any traditional council or intermediate area.
- **info**: Bagwai is not linked to any traditional council or intermediate area.
- **info**: Bebeji is not linked to any traditional council or intermediate area.
- **info**: Bichi is not linked to any traditional council or intermediate area.
- **info**: Bunkure is not linked to any traditional council or intermediate area.
- **info**: Dala is not linked to any traditional council or intermediate area.
- **info**: Dambatta is not linked to any traditional council or intermediate area.
- **info**: Dawakin Kudu is not linked to any traditional council or intermediate area.
- **info**: Dawakin Tofa is not linked to any traditional council or intermediate area.
- **info**: Doguwa is not linked to any traditional council or intermediate area.
- **info**: Fagge is not linked to any traditional council or intermediate area.
- **info**: Gabasawa is not linked to any traditional council or intermediate area.
- **info**: Garko is not linked to any traditional council or intermediate area.
- **info**: Garum Mallam is not linked to any traditional council or intermediate area.
- **info**: Gaya is not linked to any traditional council or intermediate area.
- **info**: Gezawa is not linked to any traditional council or intermediate area.
- **info**: Gwale is not linked to any traditional council or intermediate area.
- **info**: Gwarzo is not linked to any traditional council or intermediate area.
- **info**: Kabo is not linked to any traditional council or intermediate area.
- **info**: Kano Municipal is not linked to any traditional council or intermediate area.
- **info**: Karaye is not linked to any traditional council or intermediate area.
- **info**: Kibiya is not linked to any traditional council or intermediate area.
- **info**: Kiru is not linked to any traditional council or intermediate area.
- **info**: Kumbotso is not linked to any traditional council or intermediate area.
- **info**: Kunchi is not linked to any traditional council or intermediate area.
- **info**: Kura is not linked to any traditional council or intermediate area.
- **info**: Madobi is not linked to any traditional council or intermediate area.
- **info**: Makoda is not linked to any traditional council or intermediate area.
- **info**: Minjibir is not linked to any traditional council or intermediate area.
- **info**: Nasarawa is not linked to any traditional council or intermediate area.
- **info**: Rano is not linked to any traditional council or intermediate area.
- **info**: Rimin Gado is not linked to any traditional council or intermediate area.
- **info**: Rogo is not linked to any traditional council or intermediate area.
- **info**: Shanono is not linked to any traditional council or intermediate area.
- **info**: Sumaila is not linked to any traditional council or intermediate area.
- **info**: Takai is not linked to any traditional council or intermediate area.
- **info**: Tarauni is not linked to any traditional council or intermediate area.
- **info**: Tofa is not linked to any traditional council or intermediate area.
- **info**: Tsanyawa is not linked to any traditional council or intermediate area.
- **info**: Tudun Wada is not linked to any traditional council or intermediate area.
- **info**: Ungogo is not linked to any traditional council or intermediate area.
- **info**: Warawa is not linked to any traditional council or intermediate area.
- **info**: Wudil is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Kano State: 2 different population figures for 2006 (9383682,9401288). All are kept side by side, as the rules require.
- **info**: Kano State: 2 different area_km2 figures for no year (20280,20389). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).
- **info**: Open dispute #7: Kano emirship: Muhammadu Sanusi II or Aminu Ado Bayero.

## Missing LGAs / coverage

- **check**: Ajingi has no ethnic group linked to it.
- **check**: Albasu has no ethnic group linked to it.
- **check**: Bagwai has no ethnic group linked to it.
- **check**: Bichi has no ethnic group linked to it.
- **check**: Dala has no ethnic group linked to it.
- **check**: Dambatta has no ethnic group linked to it.
- **check**: Gabasawa has no ethnic group linked to it.
- **check**: Garko has no ethnic group linked to it.
- **check**: Gaya has no ethnic group linked to it.
- **check**: Gwale has no ethnic group linked to it.
- **check**: Kano Municipal has no ethnic group linked to it.
- **check**: Kibiya has no ethnic group linked to it.
- **check**: Kiru has no ethnic group linked to it.
- **check**: Kumbotso has no ethnic group linked to it.
- **check**: Kunchi has no ethnic group linked to it.
- **check**: Kura has no ethnic group linked to it.
- **check**: Madobi has no ethnic group linked to it.
- **check**: Makoda has no ethnic group linked to it.
- **check**: Nasarawa has no ethnic group linked to it.
- **check**: Rano has no ethnic group linked to it.
- **check**: Rimin Gado has no ethnic group linked to it.
- **check**: Rogo has no ethnic group linked to it.
- **check**: Shanono has no ethnic group linked to it.
- **check**: Takai has no ethnic group linked to it.
- **check**: Tarauni has no ethnic group linked to it.
- **check**: Tofa has no ethnic group linked to it.
- **check**: Tsanyawa has no ethnic group linked to it.
- **check**: Tudun Wada has no ethnic group linked to it.
- **check**: Ungogo has no ethnic group linked to it.
- **check**: Warawa has no ethnic group linked to it.
- **check**: Wudil has no ethnic group linked to it.
- **info**: Ajingi has only 76 words of text (no LGA history yet).
- **info**: Albasu has only 79 words of text (no LGA history yet).
- **info**: Bagwai has only 80 words of text (no LGA history yet).
- **info**: Bebeji has only 82 words of text (no LGA history yet).
- **info**: Bichi has only 84 words of text (no LGA history yet).
- **info**: Dala has only 93 words of text (no LGA history yet).
- **info**: Dambatta has only 75 words of text (no LGA history yet).
- **info**: Dawakin Kudu has only 86 words of text (no LGA history yet).
- **info**: Dawakin Tofa has only 67 words of text (no LGA history yet).
- **info**: Doguwa has only 88 words of text (no LGA history yet).
- **info**: Fagge has only 82 words of text (no LGA history yet).
- **info**: Gabasawa has only 61 words of text (no LGA history yet).
- **info**: Garko has only 71 words of text (no LGA history yet).
- **info**: Garum Mallam has only 98 words of text (no LGA history yet).
- **info**: Gaya has only 94 words of text (no LGA history yet).
- **info**: Gezawa has only 64 words of text (no LGA history yet).
- **info**: Gwale has only 77 words of text (no LGA history yet).
- **info**: Gwarzo has only 64 words of text (no LGA history yet).
- **info**: Kabo has only 85 words of text (no LGA history yet).
- **info**: Kano Municipal has only 91 words of text (no LGA history yet).
- **info**: Kibiya has only 83 words of text (no LGA history yet).
- **info**: Kiru has only 66 words of text (no LGA history yet).
- **info**: Kumbotso has only 62 words of text (no LGA history yet).
- **info**: Kunchi has only 74 words of text (no LGA history yet).
- **info**: Kura has only 65 words of text (no LGA history yet).
- **info**: Madobi has only 53 words of text (no LGA history yet).
- **info**: Makoda has only 89 words of text (no LGA history yet).
- **info**: Minjibir has only 86 words of text (no LGA history yet).
- **info**: Nasarawa has only 84 words of text (no LGA history yet).
- **info**: Rano has only 79 words of text (no LGA history yet).
- **info**: Rimin Gado has only 72 words of text (no LGA history yet).
- **info**: Rogo has only 83 words of text (no LGA history yet).
- **info**: Shanono has only 68 words of text (no LGA history yet).
- **info**: Sumaila has only 74 words of text (no LGA history yet).
- **info**: Takai has only 69 words of text (no LGA history yet).
- **info**: Tarauni has only 72 words of text (no LGA history yet).
- **info**: Tofa has only 76 words of text (no LGA history yet).
- **info**: Tsanyawa has only 62 words of text (no LGA history yet).
- **info**: Tudun Wada has only 75 words of text (no LGA history yet).
- **info**: Ungogo has only 65 words of text (no LGA history yet).
- **info**: Warawa has only 80 words of text (no LGA history yet).
- **info**: Wudil has only 64 words of text (no LGA history yet).

## Classification problems

- **info**: Igbo (ethnic group #21, ETH-IGBO) has no 'speaks' link to a language.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
- **info**: Open gap #940: Bauchi: Damlanci classification.
