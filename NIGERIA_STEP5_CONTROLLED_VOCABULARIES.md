# Master prompt — Step 5: controlled vocabularies

Prepared 25 September 2026. **This is a proposal only; nothing in the database has been changed.**

It follows the owner's Step 4 decisions: the brief's six-level evidence scale is adopted, INEC wards, zones as records, audio and video deferred, and the sensitivity defaults.

Conventions:
- Codes are lowercase snake_case and stable. Labels may change; codes never do.
- Every list has a documented default. "Rule" says when the value may be used.

---

## 1. Evidence status (brief §16) and mapping of the current values

### 1.1 The six levels (new column `evidence_level`)

| Code | Label | Definition | Rule for use |
|---|---|---|---|
| `verified` | Verified | Supported by a strong authoritative source, or by two or more independent credible sources that agree | At least one Tier 1 source that states the fact directly, **or** ≥2 independent sources of Tier 1–3 that agree (copies of each other count as one) |
| `well_documented` | Well documented | Supported by credible sources, but not independently confirmed | One Tier 1–2 source, or ≥2 sources of which some share a lineage |
| `reported` | Reported | Found in a credible source; needs corroboration | One Tier 3–4 source (encyclopedia, community source, news) |
| `needs_corroboration` | Needs corroboration | Evidence is weak, incomplete or thin | Tier 5 only, an unclear source, or a source that only implies the claim |
| `disputed` | Disputed | Credible sources or community accounts disagree | A `claim_dispute` must exist that names the competing claims and their sources |
| `uncertain` | Uncertain | Not enough information to classify responsibly | No usable source yet. The field stays empty in public views, and a research gap is recorded |

Default for new claims: `needs_corroboration` until sources are attached. The level is then set by the rules above, never upgraded by hand without a source.

### 1.2 Mapping from the current `evidence_status` values

The current column is kept, so pages keep working. A new `evidence_level` is filled by these rules, and public displays can switch to it later, in an approved step.

| Current value (records today) | Maps to | Rule |
|---|---|---|
| `verified` (770 admin units) | `verified` | These rest on the Constitution (Tier 1) |
| `multiple_sources` (298) | `verified` **if** at least one source is Tier 1–2 and the sources are independent; **otherwise** `well_documented` | Decided per record from `source_tier` (to be backfilled) |
| `well_documented` (0) | `well_documented` | — |
| `single_reliable_source` (323, mostly census and area figures) | `well_documented` if that source is Tier 1–2 (e.g. National Population Commission figures), **else** `reported` | Per record |
| `community_source` (0) | `reported` + claim_nature `community_tradition` | — |
| `oral_tradition` (0) | `reported` + claim_nature `oral_tradition` | The existence of the tradition may be `well_documented`, but its content is never "fact" |
| `scholarly_interpretation` (0) | `well_documented` + claim_nature `academic_interpretation` | — |
| `disputed` (0) | `disputed` | A dispute record is required |
| `needs_corroboration` (18) | `needs_corroboration` | — |
| `unverified` (0) | `uncertain` | — |
| `outdated` (0) | keep the level from the sources, and set temporal_scope = `historical` | "Outdated" describes time, not evidence |
| empty (83 Tiv-collection people/events) | not mapped | Outside the national layer; the Tiv collection keeps its own conventions |

**Note on "nature" vs "evidence":** oral tradition, community tradition and academic interpretation are *kinds of claim* (`claim_nature`, §7.2), not evidence levels. The old scale mixed the two; the new model separates them, as the brief requires (§9).

---

## 2. Settlement status (brief §5): how a group is present in a place

Used on `present_in` / `indigenous_to` / `settled_in` relations (group or community → state / LGA / ward / community).

| Code | Label | Definition | Rule / test | Example |
|---|---|---|---|---|
| `indigenous_core` | Indigenous — core | Traditional homeland of the group; the group is the main indigenous population | A source describes the area as the group's homeland or as indigenous to it | Tiv in Gboko LGA |
| `indigenous_shared` | Indigenous — shared | Indigenous, together with one or more other groups | Sources name the group as indigenous alongside others | Tiv and Jukun around Wukari |
| `historically_present` | Historically present | Lived there or held influence in the past; present population limited or unknown | A source dates the presence to the past | — |
| `significant_contemporary` | Significant contemporary settlement | Established modern community, not indigenous | Sources describe a substantial present-day community | Igbo communities in Kano |
| `migrant_community` | Migrant community | Recent or seasonal migrant population | Sources describe migration or settler status | — |
| `mixed_community` | Mixed community | A community whose population is described as ethnically mixed | Source says "mixed" | — |
| `disputed` | Disputed | Sources disagree on indigeneity or presence (e.g. "settlers" vs "indigenes") | A dispute record is required; wording stays neutral | Tiv–Yache boundary areas |
| `unknown` | Unknown | Presence documented but its nature is not | Default when a source gives presence only | Tiv in Wase LGA (Ethnologue) |

Default: `unknown`. **Rule:** never infer `indigenous_*` from presence in a city (§4). Contested "indigene vs settler" cases are always `disputed`.

### 2.1 Location type (brief §4 A–E), an optional companion field

| Code | Label | Brief |
|---|---|---|
| `core_homeland` | Traditional / core homeland | A |
| `current_indigenous` | Current indigenous territory | B |
| `secondary_settlement` | Secondary settlement outside the homeland | C |
| `urban_contemporary` | Urban / contemporary settlement | D |
| `historical_presence` | Historical presence | E |

---

## 3. Ethnic relationship (brief §13): relations between groups and communities

Relation codes in `relation_types`. Direction matters where marked (→); otherwise the relation is symmetric.

| Code | Label / inverse | Definition | Rule | Example |
|---|---|---|---|---|
| `subgroup_of` → | is a subgroup of / has subgroup | Recognised subdivision of a larger group | A source names it as a subgroup; if sources differ, record a dispute | Ukum → Tiv |
| `clan_of` → | is a clan of / has clan | Lineage/clan unit within a group or subgroup | Genealogical or ethnographic source | Kparev → Ipusu (Tiv) |
| `linguistically_related` | is linguistically related to | Their languages belong to the same family or branch | Linguistic source (Glottolog, Ethnologue, academic); about languages, not ancestry | Tiv — Otank (Tivoid) |
| `historically_related` | is historically related to | Shared history documented (past union, common polity) | Historical source | Jukun — Tiv (Kwararafa era) |
| `shared_ancestry_tradition` | shares an ancestry tradition with | The groups' **traditions** claim common descent | Always labelled as tradition; never stated as genetic or proven fact | Utanga "brothers of the Tiv" |
| `geographically_neighbouring` | neighbours | Territories adjoin | Source or documented shared border/LGA | Tiv — Idoma |
| `political_alliance` | was allied with | Documented alliance, confederacy or treaty | Dated where possible | Ekiti Parapo members |
| `historical_conflict` | had a historical conflict with | Documented war or conflict | Dated, neutral wording; ongoing conflicts are sensitive | — |
| `trade_relationship` | traded with | Documented trade links | Source | — |
| `cultural_exchange` | exchanged culture with | Documented borrowing (titles, festivals, crafts) | Source says who borrowed from whom | Tor Agbande title from the Jukun |
| `shared_festival` | shares a festival with | Both celebrate the same named festival | Source | — |
| `shared_territory` | shares territory with | Co-inhabit the same area | Source | — |
| `often_confused_with` | is often confused with | Different groups sometimes mistaken for each other (§14) | Source showing the confusion | Abakpa (Benue) vs Abakpa (Ejagham) |
| `disputed_relationship` | has a disputed relationship with | Sources disagree about how they relate | A dispute record is required | — |

**Aliases are not relations:** alternative names, spellings and colonial names are stored as `entity_names` of the same record (§14–15), not as separate groups.

---

## 4. Source type and tier (brief §17–18)

### 4.1 Source types (new list; each has a default tier)

| Code | Label | Default tier | Replaces current value |
|---|---|---|---|
| `legislation` | Constitution, acts, decrees, gazettes | 1 | part of `government_publication` |
| `government_publication` | Government report, statistical or cultural publication | 1 | `government_publication` |
| `census_statistics` | NPC / NBS data (incl. faithful reproductions, source noted) | 1 | part of `dataset` |
| `official_website` | Federal / state / LGA government or agency website | 1 | `official_website` |
| `heritage_body` | NCMM, National Library, museums, UNESCO | 1 | new |
| `archival_record` | Government or colonial archives | 1 | `archival_record` |
| `journal_article` | Peer-reviewed article | 2 | `journal_article` |
| `academic_book` | Scholarly book or chapter | 2 | `book` (scholarly) |
| `thesis` | Thesis or dissertation | 2 | `thesis` |
| `linguistic_database` | Glottolog, Ethnologue, WALS … | 2 | part of `dataset` |
| `research_report` | Institutional research report | 2 | `research` |
| `encyclopedia` | Encyclopedias (Britannica, Encyclopedia of World Cultures, Wikipedia) | 3 | `encyclopedia` |
| `reference_database` | Reputable compilations (Statoids, City Population) | 3 | part of `dataset` |
| `news` | Newspapers and news agencies | 3 | `news` |
| `general_book` | Non-academic book | 3 | `book` (general) |
| `community_organisation` | Traditional council, cultural association, community website | 4 | part of `website` |
| `oral_history` | Recorded oral history or interview | 4 | `oral_tradition`, `interview` |
| `community_submission` | Contribution to this archive | 4 | `community_submission` |
| `website` | General website, blog | 5 | `website` |
| `map` | Map (tier by publisher) | by publisher | `map` |
| `other` | Other | 5 | `other` |

**Tier override:** a source's tier may be raised or lowered from the default with a note (for example, Wikipedia used only for a claim it cites to a Tier 1 source still counts as Tier 3; a personal website written by a recognised scholar may be raised to Tier 2).
**Independence:** sources that copy each other (as found for Kano, Bayelsa, Borno and Edo) are linked as `same_lineage` and count as one for the `verified` rule.

### 4.2 Source verification status (existing column; unchanged meaning)

`verified` (the source itself is authentic and correctly cited), `needs_corroboration`, `disputed`. This says whether the *source* is reliable, not whether a claim is true.

---

## 5. Cultural category (brief §8): top-level browse categories

Mapped from the existing `cultural_records.record_type` (which stays as the detailed type).

| Category code | Label | Record types included |
|---|---|---|
| `traditional_institutions` | Traditional institutions | (`polities`: titles, councils, chiefdoms, emirates) + age grades (`record_type` `other` → new `age_grade`) |
| `festivals_ceremonies` | Festivals and ceremonies | `festival`, `ceremony`, `marriage_tradition`, `burial_tradition`, `naming_practice` |
| `food` | Food and cuisine | `food` |
| `clothing_adornment` | Clothing and adornment | `clothing` (+ new `adornment`: beads, body art, hairstyles) |
| `music` | Music and instruments | `music` |
| `dance` | Dance and performance | `dance` |
| `arts_crafts` | Arts and crafts | `art`, `craft`, `architecture` |
| `occupations` | Traditional occupations | `occupation`, `agricultural_knowledge` |
| `knowledge_belief` | Indigenous knowledge and belief | `indigenous_knowledge` (+ new `belief_ritual`: **default sensitivity `community_sensitive`**) |
| `oral_literature` | Oral literature | `oral_tradition`, `folklore`, `proverb` |
| `games_sport` | Games and sport | `game_or_sport` (e.g. Dambe, Kokawa) |
| `other` | Other | `other` |

**Craft sub-types** (attribute `craft_type`): pottery, weaving, blacksmithing, wood_carving, basketry, beadwork, leatherwork, sculpture, dyeing, metal_casting, other.
**Occupation sub-types** (attribute `occupation_type`): farming, fishing, hunting, herding, trading, craft_production, salt_production, mining, other.

---

## 6. Research status (brief §26 Phases 3–5)

### 6.1 Per record (`research_status` column)

| Code | Label | Meaning |
|---|---|---|
| `not_started` | Not started | Default |
| `in_progress` | In progress | Being researched in a batch |
| `drafted` | Drafted | Content and sources entered; not yet reviewed |
| `needs_review` | Needs review | Flagged by quality control (duplicate, weak source, contradiction …) |
| `qc_passed` | Quality-checked | Passed the Phase 4 checklist |
| `approved` | Approved | Approved by the owner (and published if public) |
| `needs_update` | Needs update | New evidence or a reported change since approval |

### 6.2 Per state and LGA (`research_status` table: the Phase 5 status column)

| Code | Label | Rule |
|---|---|---|
| `not_started` | Not started | — |
| `in_progress` | In progress | At least one LGA is being researched |
| `partially_reviewed` | Partially reviewed | Some LGAs reviewed, others not |
| `qc_in_progress` | Quality control | All LGAs researched; Phase 4 checks running |
| `complete` | Complete | **Every LGA systematically reviewed** and QC passed (brief: never claim complete before that) |
| `needs_update` | Needs update | Reopened after new evidence |

---

## 7. Other vocabularies named in the Step 4 schema

| List | Codes (default in bold) |
|---|---|
| 7.1 `sensitivity` (§25; owner decision Q5) | **`public`** (published research), `community_sensitive`, `restricted`, `permission_required` (**default for oral histories and field material**), `unknown` |
| 7.2 `claim_nature` (§9, §37) | `documented_fact`, `government_documentation`, `archival_evidence`, `archaeological_evidence`, `recorded_historical_account`, `academic_interpretation`, `oral_tradition`, `community_tradition`, `contemporary_observation` |
| 7.3 `geographic_level` | `national`, `zone`, `state`, `lga`, `ward`, `community`, `site` |
| 7.4 `temporal_scope` | `current`, `historical`, `both`, **`unknown`** |
| 7.5 `group_level` | **`ethnic_group`**, `subgroup`, `clan`, `lineage`, `community_identity` (for a named community whose group-level status is unsettled) |
| 7.6 `community_type` | `clan_area`, `kindred`, `village_group`, `town_quarter`, `traditional_community`, `other` |
| 7.7 `speaker_role` | `first_language`, `second_language`, `lingua_franca`, `heritage_language`, **`unknown`** |
| 7.8 `language vitality_status` | `safe`, `stable`, `threatened`, `endangered`, `moribund`, `extinct`, **`unknown`**. Only with vitality_source_id |
| 7.9 `lang_type` | `family`, `branch`, `language`, `dialect`, `variety` (new) |
| 7.10 `event_kind` | `political`, `administrative`, `migration`, `founding`, `conflict`, `colonial`, `religious`, `cultural`, `economic`, `archaeological`, `other` |
| 7.11 `tradition_type` (oral histories) | `origin`, `migration`, `founding`, `ancestral`, `creation`, `conflict`, `settlement`, `other` |
| 7.12 `cultural current_status` | `active`, `declining`, `revived`, `historical`, **`unknown`** |
| 7.13 `scope_level` (culture) | `community`, `lga`, `subgroup`, `ethnic_group`, `regional`, `national` |
| 7.14 `cultural attribute` keys | `ingredient`, `preparation_step`, `material`, `garment`, `instrument`, `occasion`, `performer`, `season`, `craft_type`, `occupation_type`, `local_term` |
| 7.15 `dispute nature` | `classification`, `name`, `date`, `location`, `boundary`, `homeland`, `indigeneity`, `population`, `origin`, `other` |
| 7.16 `dispute status` | **`open`**, `resolved`, `unresolvable` |
| 7.17 `gap_type` (§35) | `community_level_missing`, `ward_level_missing`, `classification_disputed`, `language_classification_differs`, `oral_only`, `no_population_data`, `source_conflict`, `name_variants`, `festival_status_unknown`, `official_source_missing`, `other` |
| 7.18 `gap priority` | `high`, **`medium`**, `low` |
| 7.19 `permission_status` (media, field) | `granted`, **`pending`**, `refused`, `not_required`, `unknown` |
| 7.20 `consent_status` (field) | `written_consent`, `recorded_verbal_consent`, `institutional_consent`, **`none_recorded`** (cannot be published) |
| 7.21 `media_type` (Q4) | `photo`, `document`, `map`, `manuscript`. **`audio` and `video` deferred** |
| 7.22 `gis_status` (§31) | **`needs_gis`**, `point_only`, `boundary_available`, `not_applicable` |
| 7.23 `protection_status` (heritage) | `national_monument`, `unesco_world_heritage`, `unesco_tentative`, `state_protected`, **`none_known`** |
| 7.24 `condition_status` (heritage) | `good`, `fair`, `threatened`, `damaged`, `destroyed`, **`unknown`** |
| 7.25 `admin unit_type` (additions) | `geopolitical_zone`, `ward` (Q1, Q2) |

---

## 8. Normalisation rules (brief §29)

- **State names:** the short form without "State" ("Benue", "Cross River", "Akwa Ibom"); the FCT is "Federal Capital Territory" (code `NG-FCT`). The existing display names ("Benue State") are unchanged. Normalised forms live in `stable_id` and exports.
- **LGA names:** exactly as the archive record (Constitution form unless the owner decided otherwise: Ogbomosho South, Yenagoa, Olamaboro, Ikot Abasi); variants in `entity_names`.
- **Ethnic group and language names:** canonical name = the record name. Every other form is an `entity_names` row with a type and a source.
- **Stable ID format:** `NG-STATE-<STATE>`, `NG-LGA-<STATE>-<LGA>`, `ETH-<GROUP>`, `LANG-<LANGUAGE>`, etc. Uppercase ASCII; spaces and punctuation become hyphens; accents are dropped. Generated once and never changed.
- **Dates:** ISO where exact; otherwise year + precision code (existing `date_precision` list: exact, month, year, circa, decade, century, range, unknown).

## 9. What approval of Step 5 unlocks

Together with Step 4, these lists define migration 002. It would be written and tested on a copy, then deployed only on a separate approval. The next master-prompt step is **Step 6**: a one-state sample implementation, which would use these vocabularies (before or after migration 002, as the owner prefers).
