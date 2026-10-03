# Kogi State — Phase 4 quality control

Run 30 September 2026 on the **live** database, read-only (nothing was changed), after batches 051–055. The script is `database/research/qc_state.php kogi`, the same one used for the other states.

## Verdict

- **No errors found (0 problems).** No duplicates, no records in the wrong place, no unsourced or over-graded claims, and no source-quality issues.
- **Coverage went from nothing to full LGA coverage in one day.**

| | Before (baseline, 30 Sep) | Now |
|---|---|---|
| Ethnic groups linked | 0 | **12**: Igala, Okun, Oworo, Bassa-Nge, Nupe, Kakanda, Kupa and Ogori–Magongo, plus Ebira, Basa, Gbagyi and Idoma |
| Languages linked | 0 | **11**: Igala, Nupe, Yoruba, Kakanda, Kupa, Ọkọ–Eni–Ọsayẹn, Ọkpamheri and Uwu, plus Ebira and Basa-Benue, and 3 new families |
| Traditional institutions | 0 | **12** (the state and Okun councils; the Attah Igala, the Ohinoyi of Ebiraland and two other first-class stools; the Obaro of Kabba; the Etsu Bassa-Nge) |
| Cultural records | 0 | **3** (Ovia-Osese, Owiya Osese, and one linked earlier) |
| Places | 1 | **28**: the Lokoja museum, the Idah tumulus (declared No. 38), four proposed monuments, Mount Patti and 20 headquarters towns |
| Wards | 0 | **239** (INEC, 2015) |
| Links | 11 | **96** |
| LGAs with peoples linked | 0 | **21 of 21** |
| LGAs with a description and headquarters | 0 | **21 of 21** |

- **One classification check:** Ọkọ–Eni–Ọsayẹn has no family. This is accurate, because the Atlas treats it as a branch of its own within Benue–Congo.
- **Info notes:**
  1. **Short LGA texts.** 17 LGAs have under 100 words. The sources used (Statoids, INEC, and short Wikipedia stubs) give little more, and no LGA histories or creation dates were found.
  2. **LGAs not linked to a council.** All 21 LGAs show "not linked to any traditional council". The stools are linked to their LGAs, but only the state council and the Okun Area Traditional Council exist as records. The Igala councils (Ankpa, Ajaka, Ugwolawo, Egume, Dekina, Omala, Olamaboro) are named by Wikipedia but not yet recorded.
  3. **Missing other names.** Five peoples and two languages have none. Some are available from sources already read: "Anebira" for the Ebira (Wikipedia, Kogi State), and "Anufawa" and "Nyffe" for the Nupe (the Atlas's people-name field 2.C).
  4. **Contradictory figures:** two state population figures for 2006, kept side by side.
  5. **Items that belong to other states.** Open disputes #2, #4 and #6 and some gaps appear only through links shared with Benue, Nasarawa and Taraba records. They are not Kogi issues.
- **Corrections made during the Kogi work:** fix_051b removed my inference "of Kogi State" from the Yoruba language text. The batch 055 descriptions were checked sentence by sentence against their sources before testing.
- **Is Kogi complete?** Not yet. By the brief's rule it now moves to *quality control in progress*. Its progress record on live still reads "not started, 0/21", which is out of date.

## Proposals (each needs your approval)

1. **Batch 056: Kogi names and progress (small, as batch 050 was for Plateau):**
   - other names from sources already read: Anebira (Ebira), and Anufawa and Nyffe (Nupe)
   - a fix setting Kogi's research progress to 21/21 LGAs researched and *quality control in progress*
2. **Later, when sources are found:**
   - the Igala traditional councils and the remaining Okun stools (Olubunu, Olujumu, Agbana of Isanlu, Olu of Oworo)
   - the grades of the Obaro of Kabba and the Etsu Bassa-Nge
   - Igala and Ebira festivals
   - whether Ovia-Osese is recognised by UNESCO
   - an official Kogi table of LGA headquarters
   - LGA creation dates
3. **Then the next state.** Neighbours not yet researched in depth include Adamawa, Bauchi, Kwara, Edo, Enugu and Ekiti.

## Full report (as generated)

Read-only check run 30 September 2026, 17:52. Nothing was changed.

**Scope:** the state, its 21 LGAs and 239 wards, and every record linked to them: 12 ethnic groups, 10 languages, 12 polities, 3 cultural records, 28 places, 96 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 7 |
| Geographic consistency | 0 | 0 | 21 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 4 |
| Missing LGAs / coverage | 0 | 0 | 17 |
| Classification problems | 0 | 1 | 4 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Ebira (ethnic group #48, ETH-EBIRA) has no other names recorded.
- **info**: Kakanda (ethnic group #152, ETH-KAKANDA) has no other names recorded.
- **info**: Kupa (ethnic group #153, ETH-KUPA) has no other names recorded.
- **info**: Nupe (ethnic group #151, ETH-NUPE) has no other names recorded.
- **info**: Okun (ethnic group #148, ETH-OKUN) has no other names recorded.
- **info**: Igala (language #375, LANG-IGALA) has no other names recorded.
- **info**: Kupa (language #377, LANG-KUPA) has no other names recorded.

## Geographic consistency

- **info**: Adavi is not linked to any traditional council or intermediate area.
- **info**: Ajaokuta is not linked to any traditional council or intermediate area.
- **info**: Ankpa is not linked to any traditional council or intermediate area.
- **info**: Bassa is not linked to any traditional council or intermediate area.
- **info**: Dekina is not linked to any traditional council or intermediate area.
- **info**: Ibaji is not linked to any traditional council or intermediate area.
- **info**: Idah is not linked to any traditional council or intermediate area.
- **info**: Igalamela-Odolu is not linked to any traditional council or intermediate area.
- **info**: Ijumu is not linked to any traditional council or intermediate area.
- **info**: Kabba/Bunu is not linked to any traditional council or intermediate area.
- **info**: Kogi is not linked to any traditional council or intermediate area.
- **info**: Lokoja is not linked to any traditional council or intermediate area.
- **info**: Mopa-Muro is not linked to any traditional council or intermediate area.
- **info**: Ofu is not linked to any traditional council or intermediate area.
- **info**: Ogori/Magongo is not linked to any traditional council or intermediate area.
- **info**: Okehi is not linked to any traditional council or intermediate area.
- **info**: Okene is not linked to any traditional council or intermediate area.
- **info**: Olamaboro is not linked to any traditional council or intermediate area.
- **info**: Omala is not linked to any traditional council or intermediate area.
- **info**: Yagba East is not linked to any traditional council or intermediate area.
- **info**: Yagba West is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Kogi State: 2 different population figures for 2006 (3278487,3314043). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).

## Missing LGAs / coverage

- **info**: Adavi has only 76 words of text (no LGA history yet).
- **info**: Ankpa has only 95 words of text (no LGA history yet).
- **info**: Dekina has only 84 words of text (no LGA history yet).
- **info**: Ibaji has only 81 words of text (no LGA history yet).
- **info**: Idah has only 95 words of text (no LGA history yet).
- **info**: Igalamela-Odolu has only 69 words of text (no LGA history yet).
- **info**: Ijumu has only 80 words of text (no LGA history yet).
- **info**: Kabba/Bunu has only 85 words of text (no LGA history yet).
- **info**: Kogi has only 89 words of text (no LGA history yet).
- **info**: Mopa-Muro has only 77 words of text (no LGA history yet).
- **info**: Ofu has only 89 words of text (no LGA history yet).
- **info**: Okehi has only 75 words of text (no LGA history yet).
- **info**: Okene has only 93 words of text (no LGA history yet).
- **info**: Olamaboro has only 66 words of text (no LGA history yet).
- **info**: Omala has only 68 words of text (no LGA history yet).
- **info**: Yagba East has only 82 words of text (no LGA history yet).
- **info**: Yagba West has only 75 words of text (no LGA history yet).

## Classification problems

- **check**: Ọkọ–Eni–Ọsayẹn (language #379, LANG-OKO-ENI-OSAYEN) is not placed in a family or branch.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
