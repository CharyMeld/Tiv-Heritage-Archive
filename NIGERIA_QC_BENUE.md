# Benue State — Phase 4 quality control

Run 26 September 2026 on the **live** database, read-only (nothing was changed). The script is `database/research/qc_state.php benue`. It's reusable, so the same checks will run for every state.

## Verdict

- **No errors found.** There are no duplicates, geographic inconsistencies, unsourced or over-graded records, or conflicting "homeland" claims. Every LGA and ward is where it should be. Each LGA belongs to exactly one traditional area; the exceptions are Oju and Obi, which are correctly marked disputed.
- **Three classification items to fix:**
  - Basa-Makurdi has no Glottocode and no language family.
  - Oring is not placed in its family. Glottolog puts it in Upper Cross, then Koring-Kukele.
- **One significant coverage gap:** **all 23 Benue LGA pages have only a one-line summary (about 18 words), and none has its headquarters recorded.**
  - The research so far covered Benue's peoples, languages, institutions, culture, wards and heritage, but not the LGAs themselves.
  - The brief lists "LGAs" and "historical information" for each state, so by its rule ("never claim a state is complete until its LGAs have been systematically reviewed") Benue is **not complete yet**.

## Proposals (each needs your approval)

1. **Batch 030: Benue LGA profiles (recommended next).** For each of the 23 LGAs:
   - its headquarters town, recorded as a place
   - its creation date (1976, 1989, 1991 or 1996, per the Constitution and Statoids)
   - a short sourced profile: location, peoples (already linked), notable towns and features

   Candidate sources: Wikipedia LGA pages, I am Benue LGA pages, the Benue State Government. After that, the research-progress row can honestly record LGAs researched and verified.
2. **Small fix, gap #51 "Tiv dialects" (from batch 006):** mark it resolved, since batch 029 recorded the dialects from Glottolog.
3. **Classification:**
   - place Oring in its family (Upper Cross, then Koring-Kukele, per Glottolog)
   - find Basa-Makurdi's Glottolog entry and family (Wikipedia calls it "Basa-Benue")

   These can be folded into batch 030.
4. **Still open, needing printed sources:** G-09 (the 2016 law's text) and G-10 (the Tiv clan map). G-08 (the Hausa of Katsina-Ala and Ukum) has no source yet.

## What the checker covers

For the state, its LGAs and wards, and every record linked to them (11 ethnic groups, 26 languages and dialects, 11 kingdoms and councils, 3 cultural records, 5 places and 117 links), it checks:
- duplicates and alias clashes
- geographic consistency
- source quality and tiers
- evidence levels against the Step 5 rules
- contradictory figures and "homeland" claims
- open disputes
- LGA coverage
- classification

## Full report (as generated)

Read-only check run 26 September 2026, 02:07. Nothing was changed.

**Scope:** the state, its 23 LGAs and 276 wards, and every record linked to them: 11 ethnic groups, 26 languages, 11 polities, 3 cultural records, 5 places, 117 links.

Severity: **problem**, an error to fix · **check**, needs a look or a decision · **info**, a known gap or a note.

| Check | Problems | Checks | Info |
|---|---|---|---|
| Duplicates | 0 | 0 | 0 |
| Ethnic-name aliases | 0 | 0 | 5 |
| Geographic consistency | 0 | 0 | 0 |
| Source quality | 0 | 0 | 0 |
| Unsupported claims | 0 | 0 | 0 |
| Contradictory claims | 0 | 0 | 3 |
| Missing LGAs / coverage | 0 | 0 | 24 |
| Classification problems | 0 | 3 | 7 |

## Duplicates

No findings.

## Ethnic-name aliases

- **info**: Akweya (ethnic group #16, ETH-AKWEYA) has no other names recorded.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no other names recorded.
- **info**: Basa-Makurdi (language #28, LANG-BASA-MAKURDI) has no other names recorded.
- **info**: Etulo (language #21, LANG-ETULO) has no other names recorded.
- **info**: Idoma (language #19, LANG-IDOMA) has no other names recorded.

## Geographic consistency

No findings.

## Source quality

No findings.

## Unsupported claims

No findings.

## Contradictory claims

- **info**: Benue State: 2 different population figures for 2006 (4219244,4253641). All are kept side by side, as the rules require.
- **info**: Benue State: 2 different area_km2 figures for no year (30755,34059). All are kept side by side, as the rules require.
- **info**: Open dispute #2: Whether the Igede (Oju and Obi) come under the Idoma Area Traditional Council and the Och'Idoma.

## Missing LGAs / coverage

- **info**: Ado has only 18 words of text (no LGA history yet).
- **info**: Agatu has only 18 words of text (no LGA history yet).
- **info**: Apa has only 18 words of text (no LGA history yet).
- **info**: Buruku has only 18 words of text (no LGA history yet).
- **info**: Gboko has only 18 words of text (no LGA history yet).
- **info**: Guma has only 18 words of text (no LGA history yet).
- **info**: Gwer East has only 19 words of text (no LGA history yet).
- **info**: Gwer West has only 19 words of text (no LGA history yet).
- **info**: Katsina-Ala has only 18 words of text (no LGA history yet).
- **info**: Konshisha has only 18 words of text (no LGA history yet).
- **info**: Kwande has only 18 words of text (no LGA history yet).
- **info**: Logo has only 18 words of text (no LGA history yet).
- **info**: Makurdi has only 18 words of text (no LGA history yet).
- **info**: Obi has only 18 words of text (no LGA history yet).
- **info**: Ogbadibo has only 18 words of text (no LGA history yet).
- **info**: Ohimini has only 18 words of text (no LGA history yet).
- **info**: Oju has only 18 words of text (no LGA history yet).
- **info**: Okpokwu has only 18 words of text (no LGA history yet).
- **info**: Oturkpo has only 18 words of text (no LGA history yet).
- **info**: Tarka has only 18 words of text (no LGA history yet).
- **info**: Ukum has only 18 words of text (no LGA history yet).
- **info**: Ushongo has only 18 words of text (no LGA history yet).
- **info**: Vandeikya has only 18 words of text (no LGA history yet).
- **info**: 23 of 23 LGAs have no headquarters recorded.

## Classification problems

- **check**: Basa-Makurdi (language #28, LANG-BASA-MAKURDI) has no Glottocode.
- **check**: Basa-Makurdi (language #28, LANG-BASA-MAKURDI) is not placed in a family or branch.
- **check**: Oring (language #23, LANG-ORING) is not placed in a family or branch.
- **info**: Abakpa (ethnic group #22, ETH-ABAKPA) has no 'speaks' link to a language.
- **info**: Hausa (ethnic group #20, ETH-HAUSA) has no 'speaks' link to a language.
- **info**: Igbo (ethnic group #21, ETH-IGBO) has no 'speaks' link to a language.
- **info**: Open gap #51: Tiv dialects.
- **info**: Open gap #404: Nyifon: language or dialect.
- **info**: Open gap #426: Where each Tiv and Idoma dialect is spoken.
- **info**: Open gap #428: Tiv 'without dialect'.
