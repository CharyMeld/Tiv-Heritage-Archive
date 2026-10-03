# Adamawa State — Phase 4 quality control

Run 1 October 2026 on the **live** database, read-only (nothing was changed), after batches 057–061. The script is `database/research/qc_state.php adamawa`, the same one used for the other states.

## Verdict

- **No errors found (0 problems).** No duplicates, no records in the wrong place, no unsourced or over-graded claims, and no source-quality issues.
- **Coverage went from nothing to full LGA coverage** in two days (30 Sep – 1 Oct).

| | Before (baseline, 30 Sep) | Now |
|---|---|---|
| Ethnic groups linked | 0 | **34**: 29 new records (Bachama, Bata, Mbula, Kamwe, Kilba, Marghi, Sukur, Lunguda and others), plus Fulani, Chamba, Mumuye, Yandang and Jibu |
| Languages linked | 0 | **61**: 50 new (from Blench's Atlas, 49 with Glottocodes) plus 11 existing; new Biu–Mandara branch |
| Traditional institutions | 0 | **15**: the Council of Chiefs; the Adamawa Emirate, Hama Bachama, Gangwari Ganye and Mubi Emirate; the seven traditional states of December 2024; plus 3 linked through Taraba records |
| Cultural records | 0 | **44**: the 43 festivals of the state's table, plus 1 linked earlier |
| Places | 1 (Yola) | **38**: Sukur (UNESCO; declared No. 5), four NCMM museums, two proposed monuments, nine state tourist sites, 20 headquarters towns, Yola, and Yakoko (Taraba, through a link) |
| Wards | 0 | **226** (INEC, 2015) |
| Links | — | **286** |
| LGAs with peoples linked | 0 | **21 of 21** |
| LGAs with a description and headquarters | 0 | **21 of 21** |

- **Three classification checks:**
  1. **Fulfulde** (#445) has no branch. It has Glottolog's Fula family code (`fula1264`), and the Atlas classes it as "Atlantic (Northern branch, Senegal group)". The archive has no Atlantic branch record yet, so it can't be placed. **Fix proposed:** an Atlantic branch record, with Fulfulde under it.
  2. **Joole** (#220) has no Glottocode. This is already known from the Taraba QC: Glottolog has no "Joole". Its nearest match, Jaule, is a dialect of Dza, and it is not recorded as Joole's code. It is a real gap; no action.
  3. **Mukta** (#472) has no Glottocode. Batch 057 found no Glottolog entry, and the Atlas places it at Mukta village without naming an LGA. It is a real gap; no action.
- **Info notes:**
  1. **Missing other names.** 9 peoples and 12 languages have none. The Atlas gives a few that are already saved in `data/atlas_adamawa_blocks.txt`:
     - Lamjavu, Deŋsavu and Tolavu (Lamja-Deŋsa-Tola, field 1.C)
     - "Margi ti ntəm" (Margi South, 2.C)
     - Gombi and Goba (Ngwaba, 2.C)

     Most of the others have no other name in the Atlas.
  2. **LGAs not linked to a council.** All 21 LGAs show "not linked to any traditional council". The stools are linked to their LGAs, but the state Council of Chiefs is the only council record. This is the same as for Kogi; no source gives the LGAs' council structure.
  3. **Short LGA texts.** Maiha and Mubi South have under 100 words. Their Wikipedia articles are stubs, and no LGA histories were found.
  4. **Contradictory figures.** Two state population figures for 2006 and three state areas are kept side by side, as the rules require.
  5. **Items that belong to other states.** Disputes #2, #4 and #6, gaps #404, #426, #428 and #442, and the Yakoko site appear only through links shared with Benue, Nasarawa and Taraba records. They are not Adamawa issues.
- **Is Adamawa complete?** Not yet. By the brief's rule it now moves to *quality control in progress*. Its progress record on live still reads "not_started, 0/21" ("LGA-by-LGA research under the master brief not started"), which is out of date.

## Proposals (each needs your approval)

1. **Batch 062: Adamawa names, classification and progress (small, as batch 056 was for Kogi):**
   - an **Atlantic** branch record (Glottocode to be checked against Glottolog before use), with Fulfulde placed under it, as the Atlas says
   - the other names from the Atlas listed above
   - a fix setting Adamawa's research progress to 21/21 LGAs researched and *quality control in progress*
2. **Later, when sources are found:**
   - an official Adamawa table of LGA headquarters (16 rest on Statoids alone)
   - LGA creation dates
   - the titles and holders of the Gombi, Yungur and Maiha rulers
   - a second source for the Mubi Emirate's grade
   - festival rites and current practice
   - whether "Gumti Park" is part of Gashaka-Gumti
   - a Bagale people record
   - Glottocodes for Joole and Mukta
3. **Then the next state.** Adamawa's Nigerian neighbours not yet researched in depth are Borno and Gombe (Taraba is done).

## Full report (as generated)

Read-only check run 1 October 2026, 09:44. Nothing was changed.

**Scope:** the state, its 21 LGAs and 226 wards, and every record linked to them: 34 ethnic groups, 61 languages, 15 polities, 44 cultural records, 38 places, 286 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 21 |
| Geographic consistency | 0 | 0 | 22 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 5 |
| Missing LGAs / coverage | 0 | 0 | 2 |
| Classification problems | 0 | 3 | 4 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Fulani (ethnic group #51, ETH-FULANI) has no other names recorded.
- **info**: Ga'anda (ethnic group #197, ETH-GA-ANDA) has no other names recorded.
- **info**: Gudu (ethnic group #194, ETH-GUDU) has no other names recorded.
- **info**: Jibu (ethnic group #81, ETH-JIBU) has no other names recorded.
- **info**: Koma (ethnic group #207, ETH-KOMA) has no other names recorded.
- **info**: Mboi (ethnic group #209, ETH-MBOI) has no other names recorded.
- **info**: Mbula (ethnic group #186, ETH-MBULA) has no other names recorded.
- **info**: Mumuye (ethnic group #76, ETH-MUMUYE) has no other names recorded.
- **info**: Yandang (ethnic group #86, ETH-YANDANG) has no other names recorded.
- **info**: Daba (language #441, LANG-DABA) has no other names recorded.
- **info**: Dong (language #213, LANG-DONG) has no other names recorded.
- **info**: Kirya-Konzəl (language #457, LANG-KIRYA-KONZ-L) has no other names recorded.
- **info**: Lamja-Deŋsa-Tola (language #235, LANG-LAMJA-DENSA-TOLA) has no other names recorded.
- **info**: Margi South (language #467, LANG-MARGI-SOUTH) has no other names recorded.
- **info**: Mukta (language #472, LANG-MUKTA) has no other names recorded.
- **info**: Mumuye (language #248, LANG-MUMUYE) has no other names recorded.
- **info**: Ngwaba (language #473, LANG-NGWABA) has no other names recorded.
- **info**: Tha (language #262, LANG-THA) has no other names recorded.
- **info**: Vemgo–Mabas (language #480, LANG-VEMGO-MABAS) has no other names recorded.
- **info**: Waka (language #482, LANG-WAKA) has no other names recorded.
- **info**: Yoti (language #483, LANG-YOTI) has no other names recorded.

## Geographic consistency

- **info**: Yakoko Stone Burial Ground (place #158, PLACE-YAKOKO-STONE-BURIAL-GROUND) is located in Zing, outside this state (linked through a related record).
- **info**: Demsa is not linked to any traditional council or intermediate area.
- **info**: Fufore is not linked to any traditional council or intermediate area.
- **info**: Ganye is not linked to any traditional council or intermediate area.
- **info**: Girei is not linked to any traditional council or intermediate area.
- **info**: Gombi is not linked to any traditional council or intermediate area.
- **info**: Guyuk is not linked to any traditional council or intermediate area.
- **info**: Hong is not linked to any traditional council or intermediate area.
- **info**: Jada is not linked to any traditional council or intermediate area.
- **info**: Lamurde is not linked to any traditional council or intermediate area.
- **info**: Madagali is not linked to any traditional council or intermediate area.
- **info**: Maiha is not linked to any traditional council or intermediate area.
- **info**: Mayo Belwa is not linked to any traditional council or intermediate area.
- **info**: Michika is not linked to any traditional council or intermediate area.
- **info**: Mubi North is not linked to any traditional council or intermediate area.
- **info**: Mubi South is not linked to any traditional council or intermediate area.
- **info**: Numan is not linked to any traditional council or intermediate area.
- **info**: Shelleng is not linked to any traditional council or intermediate area.
- **info**: Song is not linked to any traditional council or intermediate area.
- **info**: Toungo is not linked to any traditional council or intermediate area.
- **info**: Yola North is not linked to any traditional council or intermediate area.
- **info**: Yola South is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Adamawa State: 2 different population figures for 2006 (3168101,3178950). All are kept side by side, as the rules require.
- **info**: Adamawa State: 3 different area_km2 figures for no year (36917,37957,38700). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).

## Missing LGAs / coverage

- **info**: Maiha has only 96 words of text (no LGA history yet).
- **info**: Mubi South has only 93 words of text (no LGA history yet).

## Classification problems

- **check**: Fulfulde (language #445, LANG-FULFULDE) is not placed in a family or branch.
- **check**: Joole (language #220, LANG-JOOLE) has no Glottocode.
- **check**: Mukta (language #472, LANG-MUKTA) has no Glottocode.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
