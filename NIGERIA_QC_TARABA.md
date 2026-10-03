# Taraba State — Phase 4 quality control

Run on 30 September 2026 against the **live** database. The run was read-only and changed nothing. It covers batches 038–042 and their fixes, and uses `database/research/qc_state.php taraba`, the same script used for Benue and Nasarawa. The results match the first run on 26 September.

## Verdict

- **No errors found (0 problems).** There are no duplicates, nothing is filed in the wrong place, and no claim is unsourced or given more evidence than it has. There are no source-quality issues. Where sources disagree the figures are kept side by side, as the rules require: 2 population figures for 2006 and 2 area figures. Dispute #6, Ukwe Takum, is open and recorded correctly.
- **What the research covers:**
  - 16 ethnic groups, 82 languages, 11 kingdoms and councils, 5 cultural records, 23 places and 285 links
  - all 168 wards
  - all 16 LGAs researched
- **Eight classification items (the "checks").** I looked each one up in Glottolog, using its published CLDF data and its languoid pages, and compared it with Blench's Atlas (2020), whose text is saved in `database/research/data/atlas_taraba_blocks.txt`.

| Language | What Glottolog has | Proposal |
|---|---|---|
| **Buru** (no code, and no family) | "Buru (Nigeria)", `buru1326`, listed as a **dialect** of Buru-Angwe (`buru1299`, ISO `bqw`). Glottolog classifies it as Benue-Congo > Bantoid > **Southern Bantoid**. The Atlas says "South Bantoid: unclassified". | Place Buru under Southern Bantoid, which both sources agree on. Record `buru1326`, and note that Glottolog treats it as a dialect of Buru-Angwe. |
| **Mambila** (no code) | No single language: Glottolog splits Nigerian Mambila into **Western Mambila** (`nige1255`, ISO `mzk`) and **Donga Mambila** (`came1252`, ISO `mcu`), both in Cameroon and Nigeria, under the Mambila group (`mamb1312`). | **Your decision:** keep one record and give it the group code `mamb1312`, or split it into the two Glottolog languages. |
| **Dirim** (no code) | Only a group, "Dirim-Nnakenyare" (`diri1260`), under **Dakoid**. That agrees with the Atlas. The Atlas also notes that "doubts persist" about whether Dirim is separate from Samba Daka. | Don't put a group code on a language. Record both points in the text and leave the gap open. |
| **Joole** (no code) | Glottolog has no "Joole". The nearest match is **Jaule**, a dialect of Dza (`jaul1239`), in the Jen group, the same group the Atlas gives. The Atlas treats Joole as its own Jen language. | Mention the possible match in the text, but don't record Jaule as Joole's code. The gap stays open. |
| **Kulung (Chadic)** (no code) | No entry. Glottolog's "Kulung (Nigeria)" (`kulu1255`) is the *Jarawan Bantu* Kulung, which we already hold as a separate record. The Atlas explains the overlap: the speakers "consider themselves Kulung… although their language is Chadic and related to Piya". Piya-Kwonci is `piya1245`. | Add that sentence from the Atlas to the text. There's no code to record, so it stays a gap. |
| **Gbaya** (no code) | Northwest Gbaya (`nort2775`, ISO `gya`) is the only Gbaya language that Glottolog lists in Nigeria. | Record `nort2775` as "probable", and say why. |
| **Dampar** (no code) | No entry under this name. | Stays a gap. |

- **Coverage gaps (the "info" items):**
  1. **No other names** are recorded for 8 groups and 26 languages. The Atlas gives most of them in its fields 1.A, 1.B and 1.C, for example Buru, Dirim/Daka, Gbaya/Baya, Joole/èèʒìì, and Kulung/Kúkùlúŋ/Bambur. The text is already saved.
  2. **No 'speaks' link** for Fulani, Hausa and Yandang.
  3. **No LGA is linked to a traditional council.** Batch 040 linked the stools to their towns. The Taraba State Council of Chiefs and its composition since 2022 are still open.
  4. **Short LGA texts:** 9 LGAs have under 100 words: Ardo Kola, Bali, Jalingo, Karim Lamido, Kurmi, Lau, Sardauna, Yorro and Zing. They stay noindex and out of the sitemap, as designed. They can be lengthened as sources allow.
  5. **Non-issue:** the Tor Tiv Palace (Gboko) shows up only because it is linked through the Tiv records.
- **Is Taraba complete?** Not yet. By the brief's rule it now moves to *quality control in progress*, as Benue and Nasarawa did.

## Proposals (each needs your approval)

1. **Batch 043: Taraba names and classification (recommended next, mostly from sources already saved):**
   - other names for groups and languages, from the Atlas and Glottolog
   - Buru → Southern Bantoid, with `buru1326`
   - Gbaya `nort2775` (probable)
   - Mambila, done whichever way you decide
   - explanatory notes for Dirim, Joole and Kulung (Chadic)
   - 'speaks' links for Fulani, Hausa and Yandang where a language record matches
   - a fix setting Taraba's progress to *quality control in progress*
2. **Later, when sources turn up:**
   - the Taraba State Council of Chiefs, and links from LGAs to their councils
   - LGA creation dates and histories
   - the open items already listed in the progress file (Yakoko, Puje, Purma, Kuchicheb, other festivals)
3. **Then the next state.** The remaining Middle Belt neighbours are Plateau, Kogi and Adamawa.

## Full report (as generated)

Read-only check run 30 September 2026, 13:30. Nothing was changed.

**Scope:** the state, its 16 LGAs and 168 wards, and every record linked to them: 16 ethnic groups, 82 languages, 11 polities, 5 cultural records, 23 places, 285 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 34 |
| Geographic consistency | 0 | 0 | 17 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 5 |
| Missing LGAs / coverage | 0 | 0 | 9 |
| Classification problems | 0 | 8 | 7 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Fulani (ethnic group #51, ETH-FULANI) has no other names recorded.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no other names recorded.
- **info**: Jenjo (ethnic group #84, ETH-JENJO) has no other names recorded.
- **info**: Jibu (ethnic group #81, ETH-JIBU) has no other names recorded.
- **info**: Karimjo (ethnic group #85, ETH-KARIMJO) has no other names recorded.
- **info**: Kuteb (ethnic group #78, ETH-KUTEB) has no other names recorded.
- **info**: Mumuye (ethnic group #76, ETH-MUMUYE) has no other names recorded.
- **info**: Yandang (ethnic group #86, ETH-YANDANG) has no other names recorded.
- **info**: Akum (language #202, LANG-AKUM) has no other names recorded.
- **info**: Ambo (language #203, LANG-AMBO) has no other names recorded.
- **info**: Batu (language #205, LANG-BATU) has no other names recorded.
- **info**: Bete (language #206, LANG-BETE) has no other names recorded.
- **info**: Bukwen (language #208, LANG-BUKWEN) has no other names recorded.
- **info**: Buru (language #209, LANG-BURU) has no other names recorded.
- **info**: Dampar (language #228, LANG-DAMPAR) has no other names recorded.
- **info**: Dirim (language #212, LANG-DIRIM) has no other names recorded.
- **info**: Dong (language #213, LANG-DONG) has no other names recorded.
- **info**: Etulo (language #21, LANG-ETULO) has no other names recorded.
- **info**: Fam (language #217, LANG-FAM) has no other names recorded.
- **info**: Jibu (language #221, LANG-JIBU) has no other names recorded.
- **info**: Kam (language #223, LANG-KAM) has no other names recorded.
- **info**: Kapya (language #224, LANG-KAPYA) has no other names recorded.
- **info**: Lamja-Deŋsa-Tola (language #235, LANG-LAMJA-DENSA-TOLA) has no other names recorded.
- **info**: Ligri (language #268, LANG-LIGRI) has no other names recorded.
- **info**: Limbum (language #238, LANG-LIMBUM) has no other names recorded.
- **info**: Mashi (language #243, LANG-MASHI) has no other names recorded.
- **info**: Mumuye (language #248, LANG-MUMUYE) has no other names recorded.
- **info**: Naki (language #250, LANG-NAKI) has no other names recorded.
- **info**: Ndunda (language #252, LANG-NDUNDA) has no other names recorded.
- **info**: Nyam (language #253, LANG-NYAM) has no other names recorded.
- **info**: Pangseng (language #254, LANG-PANGSENG) has no other names recorded.
- **info**: Rang (language #256, LANG-RANG) has no other names recorded.
- **info**: Tep (language #261, LANG-TEP) has no other names recorded.
- **info**: Tha (language #262, LANG-THA) has no other names recorded.

## Geographic consistency

- **info**: Tor Tiv Palace (place #82, PLACE-TOR-TIV-PALACE) is located in Gboko, outside this state (linked through a related record).
- **info**: Ardo Kola is not linked to any traditional council or intermediate area.
- **info**: Bali is not linked to any traditional council or intermediate area.
- **info**: Donga is not linked to any traditional council or intermediate area.
- **info**: Gashaka is not linked to any traditional council or intermediate area.
- **info**: Gassol is not linked to any traditional council or intermediate area.
- **info**: Ibi is not linked to any traditional council or intermediate area.
- **info**: Jalingo is not linked to any traditional council or intermediate area.
- **info**: Karim Lamido is not linked to any traditional council or intermediate area.
- **info**: Kurmi is not linked to any traditional council or intermediate area.
- **info**: Lau is not linked to any traditional council or intermediate area.
- **info**: Sardauna is not linked to any traditional council or intermediate area.
- **info**: Takum is not linked to any traditional council or intermediate area.
- **info**: Ussa is not linked to any traditional council or intermediate area.
- **info**: Wukari is not linked to any traditional council or intermediate area.
- **info**: Yorro is not linked to any traditional council or intermediate area.
- **info**: Zing is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Taraba State: 2 different population figures for 2006 (2294800,2300736). All are kept side by side, as the rules require.
- **info**: Taraba State: 2 different area_km2 figures for no year (56282,59180). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).

## Missing LGAs / coverage

- **info**: Ardo Kola has only 58 words of text (no LGA history yet).
- **info**: Bali has only 72 words of text (no LGA history yet).
- **info**: Jalingo has only 98 words of text (no LGA history yet).
- **info**: Karim Lamido has only 84 words of text (no LGA history yet).
- **info**: Kurmi has only 94 words of text (no LGA history yet).
- **info**: Lau has only 80 words of text (no LGA history yet).
- **info**: Sardauna has only 78 words of text (no LGA history yet).
- **info**: Yorro has only 84 words of text (no LGA history yet).
- **info**: Zing has only 91 words of text (no LGA history yet).

## Classification problems

- **check**: Buru (language #209, LANG-BURU) has no Glottocode.
- **check**: Buru (language #209, LANG-BURU) is not placed in a family or branch.
- **check**: Dampar (language #228, LANG-DAMPAR) has no Glottocode.
- **check**: Dirim (language #212, LANG-DIRIM) has no Glottocode.
- **check**: Gbaya (language #218, LANG-GBAYA-218) has no Glottocode.
- **check**: Joole (language #220, LANG-JOOLE) has no Glottocode.
- **check**: Kulung (Chadic) (language #231, LANG-KULUNG-CHADIC) has no Glottocode.
- **check**: Mambila (language #242, LANG-MAMBILA) has no Glottocode.
- **info**: Fulani (ethnic group #51, ETH-FULANI) has no 'speaks' link to a language.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no 'speaks' link to a language.
- **info**: Yandang (ethnic group #86, ETH-YANDANG) has no 'speaks' link to a language.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
