# Master prompt — Step 6: sample implementation for ONE state (Benue)

Prepared 25 September 2026 as option **(a)**, chosen by the owner: a sample built from the existing, sourced archive data and shown in the structures proposed in Step 4 (schema) and Step 5 (vocabularies). **Nothing in the database was changed; no new research was added.** Where the archive has no sourced information, the field is left empty and a research gap is recorded (brief §20).

Why Benue: it is the archive's most researched state (batches 001–012). It shows every part of the model: state, LGAs, ethnic groups, languages, traditional institutions, disputes and gaps, and the link to the specialised Tiv Heritage Archive (brief §33).

How to read the evidence columns:
- *Evidence (now)* is today's stored value.
- *Evidence level* is the Step 5 six-level value it would map to, derived from the source tiers (Tier 1 official, Tier 2 academic, Tier 3 reference, Tier 4 community, Tier 5 general web).

---

## 1. State profile

| Field | Value | Evidence level | Source (tier) |
|---|---|---|---|
| stable_id | NG-STATE-BENUE | — | — |
| Name (normalised) | Benue | — | Constitution s.3 (T1) |
| Geopolitical zone | North Central → NG-ZONE-NORTH-CENTRAL (zone record per Q2) | verified | Two agreeing sources (batch 001); Wikipedia confirmed all zones (Step 2) |
| Capital | Makurdi (PLACE-MAKURDI) | verified | Constitution First Schedule (T1) |
| Created | 3 Feb 1976, from Benue-Plateau State | verified | Constitution/Statoids (T1/T3), batch 001 |
| LGAs | 23 (see §2) | verified | Constitution First Schedule (T1); Statoids (Step 2) |
| Neighbours | Nasarawa, Taraba, Cross River, Ebonyi, Enugu, Kogi; international: none | verified / well documented | batches 001–009 |
| Population 2006 (census) | 4,253,641 (NPC via City Population); 4,219,244 (Statoids) | well documented (T1 origin) | City Population (T3, reproducing NPC T1); Statoids (T3) |
| Population 1991 (census) | 2,753,077 | well documented | City Population |
| Population 2022 (projection) | 6,141,300 (projection, not a count) | well documented | City Population |
| Area km² | 34,059 (state government); 30,755 (Statoids) | reported (sources differ) | Benue State Government (T1); Statoids (T3) |
| History & geography text | 382 words (batch 004); Origins & Migration on the Tiv page (batch 012) | — | cited per sentence (entity_sources) |
| Traditional councils (state level) | Tiv Traditional Council (POL-TIV-TRADITIONAL-COUNCIL); Idoma Traditional Council — **not yet a record** | verified / gap | batch 010; gap G-06 |
| Sensitivity | public | — | — |
| Research status (Phase 5) | `in_progress` — see §13 | — | — |

## 2. All 23 LGAs

| stable_id | LGA | Ethnic groups recorded | Languages recorded | Tiv intermediate area | LGA research status |
|---|---|---|---|---|---|
| NG-LGA-BENUE-ADO | Ado | Idoma, Igbo, Ufia | — | — | in_progress |
| NG-LGA-BENUE-AGATU | Agatu | Idoma | — | — | in_progress |
| NG-LGA-BENUE-APA | Apa | Idoma | — | — | in_progress |
| NG-LGA-BENUE-BURUKU | Buruku | Tiv, Nyifon, Etulo (disputed) | Tiv, Nyifon | Jemgbagh | in_progress |
| NG-LGA-BENUE-GBOKO | Gboko | Tiv | Tiv | Jemgbagh | in_progress |
| NG-LGA-BENUE-GUMA | Guma | Tiv | Tiv | Lobi | in_progress |
| NG-LGA-BENUE-GWER-EAST | Gwer East | Tiv | Tiv | Gwer | in_progress |
| NG-LGA-BENUE-GWER-WEST | Gwer West | Tiv | Tiv | Gwer | in_progress |
| NG-LGA-BENUE-KATSINA-ALA | Katsina-Ala | Tiv, Etulo (disputed), Hausa | Tiv | Sankera | in_progress |
| NG-LGA-BENUE-KONSHISHA | Konshisha | Tiv | Tiv | Jechira | in_progress |
| NG-LGA-BENUE-KWANDE | Kwande | Tiv | Tiv, Iyive | Kwande | in_progress |
| NG-LGA-BENUE-LOGO | Logo | Tiv | Tiv | Sankera | in_progress |
| NG-LGA-BENUE-MAKURDI | Makurdi | Tiv, Hausa | Tiv, Basa-Makurdi | Lobi | in_progress |
| NG-LGA-BENUE-OBI | Obi | Idoma, Igede, Igbo | — | — | in_progress |
| NG-LGA-BENUE-OGBADIBO | Ogbadibo | Idoma | — | — | in_progress |
| NG-LGA-BENUE-OHIMINI | Ohimini | Idoma, Akweya | — | — | in_progress |
| NG-LGA-BENUE-OJU | Oju | Igede, Igbo | Igede | — | in_progress |
| NG-LGA-BENUE-OKPOKWU | Okpokwu | Idoma, Ufia (disputed) | Idoma | — | in_progress |
| NG-LGA-BENUE-OTURKPO | Oturkpo | Idoma, Akweya | Idoma, Akpa | — | in_progress |
| NG-LGA-BENUE-TARKA | Tarka | Tiv | Tiv | Jemgbagh | in_progress |
| NG-LGA-BENUE-UKUM | Ukum | Tiv, Hausa | Tiv | Sankera | in_progress |
| NG-LGA-BENUE-USHONGO | Ushongo | Tiv | Tiv | Kwande | in_progress |
| NG-LGA-BENUE-VANDEIKYA | Vandeikya | Tiv | Tiv | Jechira | in_progress |

All 23 LGAs have at least one sourced ethnic group; **none is `complete`** because wards, communities, culture and heritage are not yet researched (brief §26: never claim complete early).

## 3. Ethnic group master table (Benue-related)

| ID | Canonical name | Alternative names (type) | Language | Family | Core states | Evidence level | Sources |
|---|---|---|---|---|---|---|---|
| ETH-TIV | Tiv | Munshi (colonial exonym); Munchi (spelling); Or-Tiv (singular) | LANG-TIV | Tivoid < Southern Bantoid | Benue; also Taraba, Nasarawa, Plateau, Cross River | verified | Glottolog (T2), Bohannan EWC (T2), I am Benue (T4), Wikipedia (T3) |
| ETH-IDOMA | Idoma | Akpoto (alternative) | LANG-IDOMA | Idomoid | Benue | well documented | Blench Atlas (T2), Benue govt (T1) |
| ETH-IGEDE | Igede | Egede (spelling) | LANG-IGEDE | Idomoid | Benue; also Cross River | verified | Atlas (T2), Wikipedia (T3) |
| ETH-ETULO | Etulo | Utur (alternative); Turumawa (exonym) | LANG-ETULO | Idomoid | Benue (also Taraba per batch 005) | well documented | Benue govt (T1), Atlas (T2) |
| ETH-AKWEYA | Akweya | — | LANG-AKPA | Idomoid | Benue | well documented | Benue govt (T1), Atlas (T2) |
| ETH-NYIFON | Nyifon | Iordaa (alternative) | LANG-NYIFON | Jukunoid | Benue | verified | Atlas (T2), Wikipedia (T3) |
| ETH-UFIA | Ufia | Utonkon (alternative); Ufia Orring | LANG-ORING | (Oring: classification not recorded) | Benue | well documented | Benue govt (T1), Atlas (T2) |
| ETH-JUKUN | Jukun | Njuku (alternative); Wapa (endonym) | LANG-WANNU | Jukunoid | Taraba; Benue (minority) | well documented | Benue govt (T1) |
| ETH-HAUSA | Hausa | — | (not recorded) | — | outside Benue | well documented (presence in Benue) | Benue Finance Ministry (T1), Wikipedia (T3) |
| ETH-IGBO | Igbo | Ibo (spelling) | (not recorded) | — | outside Benue | well documented (presence) | Wikipedia (T3), others (batch 005) |
| ETH-ABAKPA | Abakpa | Abakwa (spelling) | — | — | Benue (named only) | **uncertain** (identity) | named by 4 sources; none describes the group — gap G-03 |

**Duplicate and alias checks applied** (brief §14–15): spellings (Munchi, Egede, Ibo, Abakwa), colonial names (Munshi) and endonyms (Wapa) are names of one record, never separate groups. "Abakpa" is flagged `often_confused_with` the Abakpa (Ekin) of Cross River (an Ejagham variety in the Atlas), not merged.

## 4. State ethnic table

| State | Ethnic group | Relationship (settlement status) | LGAs recorded | Language | Evidence level | Sources |
|---|---|---|---|---|---|---|
| Benue | Tiv | indigenous_core | 14 | Tiv | verified | Benue govt (T1: Tiv Traditional Council area), I am Benue (T4), Glottolog (T2) |
| Benue | Idoma | indigenous_core (state level) | 8 recorded (see §5) | Idoma | well documented | Benue govt (T1), I am Benue (T4: Och'Idoma "occupying seven LGAs") |
| Benue | Igede | indigenous_core (state level) | 2 | Igede | well documented | Benue govt (T1), Atlas (T2) |
| Benue | Etulo | indigenous (state level) | 1–2 (disputed) | Etulo | well documented | Benue govt (T1) |
| Benue | Akweya | indigenous (state level) | 2 | Akpa | well documented | Benue govt (T1) |
| Benue | Nyifon | indigenous (state level) | 1 | Nyifon | well documented | Benue govt (T1), Atlas (T2) |
| Benue | Ufia | indigenous (state level) | 1 (disputed) | Oring | well documented | Benue govt (T1) |
| Benue | Jukun | unknown | — | Wannu | well documented | Benue govt (T1) |
| Benue | Hausa | unknown (not described as indigenous) | 3 | — | well documented | Finance Ministry (T1), Wikipedia (T3) |
| Benue | Igbo | unknown | 3 | — | reported | Wikipedia (T3) |
| Benue | Abakpa | unknown | — | — | uncertain | 4 sources name it only |

## 5. LGA ethnic table (all 48 recorded LGA-level links)

| State | LGA | Ethnic group | Settlement status | Communities | Language | Evidence (now → level) | Sources |
|---|---|---|---|---|---|---|---|
| Benue | Gboko, Katsina-Ala, Kwande, Makurdi, Vandeikya (5 rows) | Tiv | indigenous_core | intermediate areas (§7) | Tiv | multiple_sources → **verified** | Atlas (T2) + I am Benue (T4) + Wikipedia (T3) |
| Benue | Buruku, Guma, Gwer East, Gwer West, Konshisha, Logo, Tarka, Ukum, Ushongo (9 rows) | Tiv | indigenous_core | intermediate areas (§7) | Tiv | multiple_sources → **well documented** | I am Benue (T4) + Wikipedia (T3); Finance Ministry count of 14 (T1) |
| Benue | Okpokwu, Oturkpo | Idoma | unknown (LGA level; gap G-01) | — | Idoma | multiple_sources → **verified** (presence) | Atlas (T2) + Ethnologue via Wikipedia (T3) |
| Benue | Ado, Agatu, Apa, Obi, Ogbadibo, Ohimini | Idoma | unknown (gap G-01) | — | — | needs_corroboration → **needs corroboration** | Ethnologue via Wikipedia (T3, not checked at origin) |
| Benue | Oturkpo | Akweya | unknown | — | Akpa | multiple_sources → **verified** | Atlas (T2) + Ethnologue via Wikipedia |
| Benue | Ohimini | Akweya | unknown | — | — | needs_corroboration | Wikipedia (T3) |
| Benue | Oju | Igede | unknown | — | Igede | multiple_sources → **verified** | Atlas (T2) + Wikipedia (T3) |
| Benue | Obi | Igede | unknown | — | — | needs_corroboration | Wikipedia (one publisher) |
| Benue | Buruku | Nyifon | unknown | — | Nyifon | multiple_sources → **verified** | Atlas (T2) + Wikipedia (T3) |
| Benue | Buruku, Katsina-Ala | Etulo | **disputed** (D-01) | — | — | needs_corroboration → **disputed** | Wikipedia (T3) vs Atlas (T2: Gboko) |
| Benue | Ado | Ufia | **disputed** (D-02) | — | — | needs_corroboration → **disputed** | Ethnologue via Wikipedia vs Atlas (T2: Okpokwu) |
| Benue | Katsina-Ala, Makurdi, Ukum | Hausa | unknown | Zaki Biam (Ukum), per Wikipedia | — | needs_corroboration | Wikipedia (T3) |
| Benue | Ado, Obi, Oju | Igbo | unknown | — | — | needs_corroboration | Ethnologue via Wikipedia (T3) |

The Tiv rows are `indigenous_core` because the sources describe these 14 LGAs as the Tiv homeland and traditional-council area. For every other group, the recorded sources only say the group or language is *found* there, so settlement status stays `unknown` until a source describes the nature of the presence (brief §4–5).

## 6. Language table

| Language | Ethnic groups | States | LGAs recorded (Benue) | Dialects | Family | Evidence level | Sources |
|---|---|---|---|---|---|---|---|
| LANG-TIV Tiv (tiv / tivv1240) | Tiv | Benue, Taraba, Nasarawa, Plateau, Cross River | 14 | not yet recorded (gap G-07) | Tivoid | verified | Glottolog (T2), SIL ISO 639-3 (T1-equivalent registry) |
| LANG-IDOMA Idoma (idu) | Idoma | Benue | Okpokwu, Oturkpo | not recorded | Idomoid | verified | Atlas (T2), Glottolog |
| LANG-IGEDE Igede (ige) | Igede | Benue | Oju | — | Idomoid | verified | Atlas (T2) |
| LANG-ETULO Etulo (utr) | Etulo | Benue | — | — | Idomoid | well documented | Atlas (T2) |
| LANG-AKPA Akpa (akf) | Akweya | Benue | Oturkpo | — | Idomoid | verified | Atlas (T2) |
| LANG-NYIFON Nyifon | Nyifon | Benue | Buruku | — | Jukunoid | verified | Atlas (T2) |
| LANG-WANNU Wannu (jub) | Jukun | Benue | — | — | Jukunoid | well documented | Atlas (T2) |
| LANG-ORING Oring (org) | Ufia (and others outside Benue) | Benue | — | — | not recorded | well documented | Atlas (T2) |
| LANG-OTANK Otank (uta) | (Utanga, Cross River) | Benue | — | — | Tivoid | well documented | Atlas (T2) |
| LANG-IYIVE Iyive (uiv) | (not linked) | Benue | Kwande | — | Tivoid | reported | Atlas only (T2, single) |
| LANG-BASA-MAKURDI Basa-Makurdi | (not linked) | Benue | Makurdi | — | not recorded | reported | Atlas only |

Brief §22 is shown in practice: Tiv the *language* and Tiv the *people* are separate records linked by `speaks`; Iyive and Basa-Makurdi are languages with no ethnic group linked yet. Nothing is inferred.

## 7. Community table

| State | LGA | Community | Ethnic group | Language | Status | Evidence level | Source |
|---|---|---|---|---|---|---|---|
| Benue | Buruku, Gboko, Tarka | Jemgbagh (Tiv intermediate area; POL record) | Tiv | Tiv | traditional_community | verified | I am Benue (T4) + Daily Trust (T3) + 2016 law as reported; batch 010 |
| Benue | Vandeikya, Konshisha | Jechira | Tiv | Tiv | traditional_community | verified | as above |
| Benue | Kwande, Ushongo | Kwande (intermediate area) | Tiv | Tiv | traditional_community | verified | as above |
| Benue | Katsina-Ala, Ukum, Logo | Sankera | Tiv | Tiv | traditional_community | verified | as above + Federal Ministry of Information (T1, 2025) |
| Benue | Makurdi, Guma | Lobi | Tiv | Tiv | traditional_community | verified | as above + ThisDay (T3) |
| Benue | Gwer East, Gwer West | Gwer | Tiv | Tiv | traditional_community | verified | as above |

**No other community, village or ward is recorded for Benue.** None will be added without a source (brief §6, §20). INEC wards (owner decision Q1) will be loaded when Benue is researched in Phase 3.

## 8. Historical information (sample claims)

| Claim | Entity | Claim nature | Evidence level | Source |
|---|---|---|---|---|
| Benue State created 3 Feb 1976 from Benue-Plateau State | NG-STATE-BENUE | documented_fact | verified | Constitution/Statoids; batch 001 |
| The Tor Tiv office was created in 1946 by the colonial administration | POL-TOR-TIV | documented_fact | verified | Wikipedia (T3) + I am Benue (T4) + archive Tiv records |
| The Tiv "came down" from the south-east | ETH-TIV | **oral_tradition** | reported (as tradition) | Bohannan EWC (T2), reporting Tiv tradition |
| Tiv presence in the Middle Benue Valley dated to the 15th–16th centuries | ETH-TIV | archaeological_evidence (as summarised) | well documented | Nomishan 2021 (T2), summarising excavation reports |
| Swem's location | ETH-TIV | academic_interpretation — **disputed** (D-04: five placements) | disputed | Nomishan 2021 |
| Six Tiv intermediate areas in law (2016 law) vs "five blocs" with Minda | POL-TIV-TRADITIONAL-COUNCIL | government_documentation vs political usage | resolved dispute (D-03) | FMINO (T1), I am Benue 2018, ThisDay, Vanguard |

## 9. Cultural information

| State | LGA | Community | Ethnic group | Item | Category | Status | Source |
|---|---|---|---|---|---|---|---|
| Benue | (state level) | — | Tiv | 8 festivals, 15 foods, 232 proverbs, 288 names, 9 articles | festivals_ceremonies, food, oral_literature … | held in the **Tiv Heritage Archive** (linked, not duplicated — brief §33) | Tiv collection |
| Benue | — | — | Idoma, Igede, Etulo, Akweya, Nyifon, Ufia, Jukun | — | — | **not yet researched** | gap G-05 |

No national `cultural_records` exist yet for Benue. The Tiv material stays in the Tiv collection and is linked from the national Tiv page ("From the Tiv Heritage collection").

## 10. Cultural heritage table

| State | LGA | Community | Ethnic group | Heritage item | Type | Description | Source |
|---|---|---|---|---|---|---|---|
| Benue | — | — | — | *(none recorded)* | — | — | gap G-04 |

Swem (the Tiv ancestral hill and oath) is recorded only as a disputed *claim*, not as a heritage site, because its location is not established (D-04).

## 11. Sources used for Benue (with proposed tiers)

| Source | Type → proposed type | Tier | Used for |
|---|---|---|---|
| Constitution of Nigeria 1999 | government_publication → legislation | 1 | state, LGAs, capital |
| About Benue State (Benue State Government) | official_website | 1 | groups at state level, area |
| History of Benue State (Benue Ministry of Finance) | *research* → **official_website** (mis-typed today) | 1 | 14 Tiv LGAs (count) |
| An Atlas of Nigerian Languages (Blench, 2020) | book → academic_book | 2 | 32 LGA/state links |
| Glottolog | dataset → linguistic_database | 2 | Tiv classification |
| Encyclopedia of World Cultures, "Tiv" (Bohannan) | encyclopedia | 2 (scholarly author) | Tiv tradition, dates |
| Nomishan 2021 (Tourism & Heritage Journal) | journal_article | 2 | Swem, dating |
| "Benue State" (Wikipedia) | *research* → **encyclopedia** (mis-typed today) | 3 | 16 LGA links |
| Wikipedia (Igede people, Akpa language, Tor Tiv) | encyclopedia | 3 | single links |
| City Population; Statoids | dataset → reference_database / census_statistics | 3 (NPC origin T1) | population, area |
| I am Benue (3 pages) | website → community_organisation | 4 | Tiv LGAs, intermediate areas |
| Daily Trust, ThisDay, Vanguard, Nigerian Voice | news | 3 | intermediate areas, law |
| Federal Ministry of Information (FIC report, 2025) | official_website | 1 | 2016 law, Sankera council |

Two source records are mis-typed as "research"; Step 5's mapping would correct their type and tier on approval.

## 12. Disputes (brief §19)

| ID | Topic | Claim A (source) | Claim B (source) | Nature | Status |
|---|---|---|---|---|---|
| D-01 | Etulo LGA | Buruku and Katsina-Ala (Wikipedia, "Benue State") | Gboko (Blench Atlas) | location | open |
| D-02 | Ufia LGA | Ado (Ethnologue via Wikipedia) | Okpokwu (Blench Atlas) | location | open |
| D-03 | Tiv intermediate areas | Five blocs incl. Minda (Vanguard 2016; Nigerian Voice 2016) | Six areas incl. Lobi and Gwer (2016 law, FMINO 2025; I am Benue) | classification | **resolved** (six in law; Minda a customary grouping) |
| D-04 | Location of Swem | Iyon area (Akiga 1939) · Ngol-Kedju (Bohannans 1954) · Nyiev-Ya (Makar 1975) · Nigeria–Cameroon border (Gbor, Orkar) · Akwaya (Dzurgba 2007) | — | location | open (Nomishan: not identified) |
| D-05 | Benue area | 34,059 km² (state government) | 30,755 km² (Statoids) | population/area figure | open (both recorded) |

## 13. Research gaps (brief §28 format)

| ID | State | LGA | Missing information | Why missing | Recommended research | Priority |
|---|---|---|---|---|---|---|
| G-01 | Benue | Ado, Agatu, Apa, Obi, Ogbadibo, Ohimini, Okpokwu, Oturkpo | Which LGAs are the "seven Idoma LGAs"; the nature of Idoma presence per LGA | Only language-location sources recorded | Idoma Traditional Council / Benue State Government documents; Idoma ethnographies | high |
| G-02 | Benue | all | Wards | Not loaded yet (owner decision Q1) | Load INEC ward list for Benue | high |
| G-03 | Benue | — | Who the Abakpa (Abakwa) of Benue are | Four sources name them; none describes them | Benue government records; academic literature | medium |
| G-04 | Benue | all | Heritage sites (palaces, shrines, museums, sites) | Not yet researched | NCMM lists; state tourism bureau; academic archaeology | medium |
| G-05 | Benue | Idoma/Igede/Etulo/Akweya/Nyifon/Ufia/Jukun areas | Festivals, food, clothing, institutions of non-Tiv groups | Not yet researched | Ethnographies; traditional councils; community sources | high |
| G-06 | Benue | Idoma LGAs | Idoma Traditional Council and Och'Idoma as records | Not yet researched | Benue State Council of Chiefs law (2016); I am Benue; academic | high |
| G-07 | Benue | all | Tiv dialects; Idoma dialects | Not recorded | Glottolog; linguistic studies | low |
| G-08 | Benue | Makurdi, Katsina-Ala, Ukum | Nature of Hausa presence (contemporary / migrant / historical) | Source lists presence only | Local histories; census/NBS | medium |
| G-09 | Benue | — | Text of the 2016 chieftaincy law (rotation clause) | Not online | Printed copy from Benue Ministry of Justice | medium |
| G-10 | Benue | Tiv LGAs | Clan/lineage map (Ipusu/Ichongo lineages per LGA) | Only an opinion article so far | Tiv genealogy literature (Bohannan, Akiga, Makar) | medium |

## 14. Research-status table (Phase 5)

| State | LGAs | Researched | Verified | Needs review | Sources | Status |
|---|---|---|---|---|---|---|
| Benue | 23 | 23 (ethnic presence at least) | 14 LGAs with ≥1 `verified` group link (Tiv ×5, Idoma ×2, Akweya, Igede, Nyifon …); 9 without | 23 (communities, wards, culture, heritage missing; 2 disputes open) | 21 distinct sources for the state | **in_progress** |

## 15. How three records would look in the new tables (illustration)

**Claim** CLAIM-000001 — subject ETH-TIV · predicate `present_in` · object NG-LGA-BENUE-GBOKO · statement "Tiv communities are indigenous to Gboko LGA." · geographic_level `lga` · claim_nature `documented_fact` · temporal_scope `current` · evidence_level `verified` · sensitivity `public` · claim_sources: Blench Atlas (supports), I am Benue "The Tiv People of Benue State" (supports), Wikipedia "Benue State" (supports).

**Relation** ETH-ETULO `present_in` NG-LGA-BENUE-BURUKU · settlement_status `disputed` · evidence_level `disputed` · dispute D-01 (claim A: Wikipedia → Buruku/Katsina-Ala; claim B: Blench Atlas → Gboko).

**Research gap** G-01 — admin_unit NG-STATE-BENUE · gap_type `classification_disputed`/`community_level_missing` · priority `high` · recommended_research "Idoma Traditional Council documents …".

---

## What approval of Step 6 means

Per the brief, **Step 7 is to stop and wait for approval** before any other state is processed. After that approval the work moves to Phase 3 (state-by-state research), with Phase 4 quality control before each next state and the Phase 5 table kept in `NIGERIA_EXPANSION_PROGRESS.md`. Migration 002 (the Step 4 schema) would be its own approved step, first on a test copy.
