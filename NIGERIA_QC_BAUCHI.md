# Bauchi State — Phase 4 quality control

Run 2 October 2026 on the **live** database, read-only (nothing was changed), after batches 075–079. The script is `database/research/qc_state.php bauchi`.

## Verdict

- **No errors found (0 problems).**
- **Coverage on live now:**

| | Now |
|---|---|
| Ethnic groups linked | **20** |
| Languages linked | **56** (47 from the Atlas in batch 075) |
| Traditional institutions | **32**: the 19 emirates and the Zaar Chiefdom (batch 077), plus 12 linked through other records |
| Cultural records | **10**, including the Bauchi Durbar |
| Places | **35**: 7 declared and 2 proposed national monuments, the National Museum Bauchi, Yankari and Wikki, Sumu, Lame-Burra, 19 headquarters towns, Bauchi city, and places linked through shared records |
| Wards | **212** (INEC, 2015) |
| Links | **215** |
| LGAs with peoples linked | **13 of 20** |
| LGAs with a description and headquarters | **20 of 20** |

## The checks, researched

1. **Daza (#599) has the same name as an alias of Teda (#523). Both are correct; no change.**
   - The Bauchi Daza is a West Chadic language of Darazo LGA (Atlas No. 85; Glottolog `daza1244`, West Chadic A).
   - "Daza" on the Teda record is the usual name of the Saharan Daza/Dazaga (Glottolog `teda1241`, Tebu).
   - They are two different languages that share a name.
2. **Jar (#613) has the same name as an alias of Mbat (#333). Both come from the Atlas; no change.**
   - The Atlas's Mbat entry (Plateau) gives "Jar, Jarawan Kogi, Garaka" as its other names (field 2.B).
   - "Jar" is also the Atlas name of the whole cluster (No. 214), to which Mbat belongs.
3. **Seven LGAs have no people linked.** Sources already read cover five of them:
   - **Giade → Fulani.** Wikipedia (Giade): "Dominated mainly by the Fulani tribe".
   - **Itas/Gadau → Hausa and Fulani.** Wikipedia (Itas/Gadau): "The predominant ethnic groups in the area are the Hausa and Fulani".
   - **Jama'are → Fulani and Kanuri.** Wikipedia (Jama'are) names the Fulani, Shirawa and Kanuri. The Shirawa have no record; the Atlas calls Shirawa an extinct Chadic language of the Katagum region.
   - **Kirfi → Hausa.** Wikipedia (Kirfi): "The predominant ethnic group in the area is the Hausa".
   - **Warji → Warji.** The Atlas places Warji in "Ningi LGA, Warji district", the area that is now Warji LGA. It is recorded with that note at the *reported* level.
   - **Katagum and Shira: no source found.** Wikipedia's Katagum article describes the town of Katagum, which is in Zaki LGA, not Katagum LGA. Both stay as gaps.
4. **Two languages have no Glottocode.**
   - **Vaghat–Ya–Bijim–Legeri (#356, a Plateau record) → `vagh1247`.** Glottolog's languoid vagh1247 is the Kwangic family (Tarokoid > Bijimic-Sur-Shall). It contains the Vaghat family (vagh1250) and Bijim (biji1246), with Legeri (Kaduk) a dialect of Bijim. This is the same grouping as the Atlas cluster (No. 469).
   - **Damlanci (#597): a real gap.** It is first described in Blench (2019), and Glottolog has no entry.

## Info notes

- **Missing other names:** Bade, Fulani, Hausa, Jaku and Ngamo, plus 10 languages, have none. No other names are available in the sources read.
- **LGAs not linked to a council:** the same pattern as in the other states. The emirates are linked to their seat LGAs.
- **Short LGA texts:** 6 LGAs have under 100 words.
- **Contradictory figures:** two state population figures for 2006 and two state areas are kept side by side.
- **Hausa has no "speaks" link.** This is a national gap.
- **Progress record:** the live progress row (#42) still reads "not_started, 0/20".

## Proposal: batch 080 + fix_080, Bauchi names and progress

- **Batch 080:**
  - 7 people–LGA links, all sourced as above: Giade–Fulani, Itas/Gadau–Hausa, Itas/Gadau–Fulani, Jama'are–Fulani, Jama'are–Kanuri, Kirfi–Hausa, Warji–Warji.
  - The Glottocode `vagh1247` for Vaghat–Ya–Bijim–Legeri, filling an empty field.
- **fix_080:** Bauchi research progress → **20/20 LGAs researched, quality control in progress**, with a summary of batches 075–080.
- **Expected QC after:** 0 problems. The checks fall from 11 to 5: 2 Katagum/Shira coverage checks, the 2 duplicate-name checks (both explained above) and the Damlanci Glottocode.

## Later, when sources are found

- the peoples of Katagum and Shira LGAs
- festivals of the Zaar, Jarawa, Gerawa, Polchi and other peoples
- descriptions of the seven declared monuments
- the Ari Emirate's LGA, and the first emirs of the new emirates
- Damlanci's classification (Glottolog)
- LGA creation dates

## Full report (as generated)

Read-only check run 2 October 2026, 08:48. Nothing was changed.

**Scope:** the state, its 20 LGAs and 212 wards, and every record linked to them: 20 ethnic groups, 56 languages, 32 polities, 10 cultural records, 35 places, 215 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 2 | 0 |
| Ethnic-name aliases | 0 | 0 | 15 |
| Geographic consistency | 0 | 0 | 21 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 5 |
| Missing LGAs / coverage | 0 | 7 | 6 |
| Classification problems | 0 | 2 | 5 |

## Duplicates

- **check**: Daza (language #599, LANG-DAZA) has the same name as an alias of languages #523.
- **check**: Jar (language #613, LANG-JAR) has the same name as an alias of languages #333.

## Ethnic-name aliases

- **info**: Bade (ethnic group #250, ETH-BADE) has no other names recorded.
- **info**: Fulani (ethnic group #51, ETH-FULANI) has no other names recorded.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no other names recorded.
- **info**: Jaku (ethnic group #271, ETH-JAKU) has no other names recorded.
- **info**: Ngamo (ethnic group #251, ETH-NGAMO) has no other names recorded.
- **info**: Daza (language #599, LANG-DAZA) has no other names recorded.
- **info**: Deno (language #600, LANG-DENO) has no other names recorded.
- **info**: Dulbu (language #602, LANG-DULBU) has no other names recorded.
- **info**: Gwa (language #611, LANG-GWA) has no other names recorded.
- **info**: Jimi (language #614, LANG-JIMI) has no other names recorded.
- **info**: Ju (language #615, LANG-JU) has no other names recorded.
- **info**: Shɨkɨ (language #631, LANG-SHIKI) has no other names recorded.
- **info**: Siri (language #632, LANG-SIRI) has no other names recorded.
- **info**: Tala (language #634, LANG-TALA) has no other names recorded.
- **info**: Zangwal (language #638, LANG-ZANGWAL) has no other names recorded.

## Geographic consistency

- **info**: National Museum Fombina (place #314, PLACE-NATIONAL-MUSEUM-FOMBINA) is located in Yola South, outside this state (linked through a related record).
- **info**: Alkaleri is not linked to any traditional council or intermediate area.
- **info**: Bauchi is not linked to any traditional council or intermediate area.
- **info**: Bogoro is not linked to any traditional council or intermediate area.
- **info**: Damban is not linked to any traditional council or intermediate area.
- **info**: Darazo is not linked to any traditional council or intermediate area.
- **info**: Dass is not linked to any traditional council or intermediate area.
- **info**: Gamawa is not linked to any traditional council or intermediate area.
- **info**: Ganjuwa is not linked to any traditional council or intermediate area.
- **info**: Giade is not linked to any traditional council or intermediate area.
- **info**: Itas/Gadau is not linked to any traditional council or intermediate area.
- **info**: Jama'are is not linked to any traditional council or intermediate area.
- **info**: Katagum is not linked to any traditional council or intermediate area.
- **info**: Kirfi is not linked to any traditional council or intermediate area.
- **info**: Misau is not linked to any traditional council or intermediate area.
- **info**: Ningi is not linked to any traditional council or intermediate area.
- **info**: Shira is not linked to any traditional council or intermediate area.
- **info**: Tafawa Balewa is not linked to any traditional council or intermediate area.
- **info**: Toro is not linked to any traditional council or intermediate area.
- **info**: Warji is not linked to any traditional council or intermediate area.
- **info**: Zaki is not linked to any traditional council or intermediate area.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Bauchi State: 2 different population figures for 2006 (4653066,4676465). All are kept side by side, as the rules require.
- **info**: Bauchi State: 2 different area_km2 figures for no year (48197,49119). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.
- **info**: Open dispute #4: Eloyi (Afo): Idomoid or Plateau.
- **info**: Open dispute #6: Ukwe Takum: Kuteb stool or rotational first-class stool (Chamba, Jukun, Kuteb).

## Missing LGAs / coverage

- **check**: Giade has no ethnic group linked to it.
- **check**: Itas/Gadau has no ethnic group linked to it.
- **check**: Jama'are has no ethnic group linked to it.
- **check**: Katagum has no ethnic group linked to it.
- **check**: Kirfi has no ethnic group linked to it.
- **check**: Shira has no ethnic group linked to it.
- **check**: Warji has no ethnic group linked to it.
- **info**: Bogoro has only 88 words of text (no LGA history yet).
- **info**: Gamawa has only 90 words of text (no LGA history yet).
- **info**: Katagum has only 97 words of text (no LGA history yet).
- **info**: Misau has only 98 words of text (no LGA history yet).
- **info**: Shira has only 88 words of text (no LGA history yet).
- **info**: Warji has only 99 words of text (no LGA history yet).

## Classification problems

- **check**: Damlanci (language #597, LANG-DAMLANCI) has no Glottocode.
- **check**: Vaghat–Ya–Bijim–Legeri (language #356, LANG-VAGHAT-YA-BIJIM-LEGERI) has no Glottocode.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no 'speaks' link to a language.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
- **info**: Open gap #442: Basa-Makurdi classification.
