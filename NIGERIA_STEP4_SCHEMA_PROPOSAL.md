# Master prompt — Step 4: proposed schema for the cultural layer

Prepared 25 September 2026. **This is a proposal only; nothing in the database has been changed.** It builds on the existing Nigeria Heritage tables (inspected in Step 1) and does not touch the 36 states, the FCT, the 774 LGAs or their names (brief §1).

The controlled values (the enum lists) are only named here. **Step 5** proposes them in full, including how the brief's evidence scale maps to the current one.

---

## 1. Design principles

1. **Extend, don't rebuild.** The archive already has most of the national model: administrative units, ethnic groups, languages, polities, cultural records, places, relations, names, statistics, per-claim source links, research gaps and research batches. New pieces are added alongside; existing rows keep their IDs and addresses.
2. **Everything is an entity; everything connects through relations** (brief §22 and §32). "Ethnic group ≠ language ≠ location" is enforced structurally: groups, languages and places are separate entities joined by typed, dated, sourced relation rows. They are never merged into one hierarchy.
3. **Every factual item is traceable** (brief §34): Claim → Entity → Location → Source → Evidence status. The existing `entity_sources` links a source to a whole record; the new `claims` layer links sources to individual statements.
4. **Uncertainty is data** (brief §19): disagreements are stored as competing claims in a dispute group. The archive never silently picks one.
5. **Stable IDs** (brief §29): a human-readable, never-changing `stable_id` on every public entity, separate from the database id and the web address.
6. **Sensitivity and permission are first-class fields** (brief §25): on cultural records, places, oral histories, media, claims and relations.
7. **Additive, reversible migrations only.** Every change is a new table, a new nullable column or a new enum value, with a rollback script (as for migration 001).

---

## 2. Brief §23 model → where each part lives

| Brief §23 | Implementation | Status |
|---|---|---|
| geopolitical_zones | `admin_units` with a new `unit_type = 'geopolitical_zone'`; states `part_of` their zone. The existing `geopolitical_zone` field is kept for compatibility | **extend** |
| states, lgas | `admin_units` (state / federal_capital_territory / lga / other) | existing, unchanged |
| wards | `admin_units` with a new `unit_type = 'ward'`, parent = LGA | **extend** |
| localities | `places` (city / town / village / settlement …), `admin_unit_id` = LGA or ward | existing |
| communities | **new `communities`**: a social/lineage community (clan area, kindred, traditional community), optionally tied to a place | **new** |
| ethnic_groups, ethnic_subgroups, clans | `ethnic_groups` with a new `group_level` (ethnic_group / subgroup / clan / lineage) and the existing `parent_id` | **extend** |
| languages, dialects, language_variants | `languages` (`lang_type` already includes family / branch / language / dialect; `variety` is added) | **extend** |
| ethnic_group_locations, community_ethnic_groups | `entity_relations` `present_in` / `indigenous_to` / `settled_in`, with new qualifier columns `settlement_status` and `location_type` | **extend** |
| ethnic_group_languages, community_languages | `entity_relations` `speaks` / `spoken_in` (+ `speaker_role`: first language / second language / lingua franca / heritage) | **extend** |
| ethnic_group_relationships | `entity_relations` with new relation types (§5 below) | **extend** |
| festivals, foods, clothing, music, dances, crafts, occupations, traditions | `cultural_records` (`record_type` already covers these) + a new `cultural_record_attributes` table for structured details | **extend** |
| traditional_institutions | `polities` (traditional_title / traditional_council / chiefdom / emirate …) | existing |
| historical_events, migration_histories | `timeline_events` (national) with a new `event_kind` (incl. `migration`) + relations `migrated_from` / `migrated_to` | **extend** |
| historical_sites, heritage_sites, museums, monuments, sacred_sites, archaeological_sites | `places` (place_type already covers these) + new heritage columns | **extend** |
| historical_figures | `historical_figures` (national_slug for Nigeria-only people) | existing |
| oral_histories | **new `oral_histories`** (linked to field records and media) | **new** |
| sources | `sources` + new `source_tier` (1–5, brief §17) | **extend** |
| claims, evidence_records | **new `claims`** + **new `claim_sources`** | **new** |
| disputed_information | **new `claim_disputes`** (groups competing claims) | **new** |
| research_notes | `research_batches` (summary, unresolved_notes) + `research_gaps` (extended) | extend |
| media (photographs, audio, video, documents, maps, manuscripts) | **new `media_assets`** + **new `media_links`** | **new** |
| field research (§36) | **new `field_records`** + **new `contributors`** | **new** |
| progress tracking (§26 Phase 5) | **new `research_status`** | **new** |
| link to the Tiv Heritage Archive (§33) | `collections` / `collection_items` (existing) + relations from national records to Tiv collection records (already supported) | existing |

---

## 3. Field specifications

Columns: **Field · Description · Type · Req. · Example · Source requirement.** "Req." = required (R) or optional (O). "Step 5" marks a controlled vocabulary proposed in Step 5.

### 3.1 Common columns added to every public entity table

(admin_units, ethnic_groups, languages, communities, places, polities, cultural_records, timeline_events, historical_figures, oral_histories, media_assets)

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| stable_id | Permanent human-readable ID; never reused or changed | varchar(80), unique | R (backfilled) | `NG-LGA-BENUE-GBOKO`, `ETH-TIV`, `LANG-TIV` | none |
| sensitivity | Who may see or reuse it | enum (Step 5) | R, default `public` | `public`, `community_sensitive`, `restricted`, `permission_required`, `unknown` | Required when not `public`: the reason is recorded in a claim or note |
| research_status | Research progress of the record | enum (Step 5) | R, default `not_started` | `in_progress` | none |

(The existing `evidence_status` and `review_status` stay on every table.)

**Stable ID pattern** (brief §29): `NG-ZONE-NORTH-CENTRAL`, `NG-STATE-BENUE`, `NG-FCT`, `NG-LGA-BENUE-GBOKO`, `NG-WARD-BENUE-GBOKO-<code>`, `COMM-…`, `PLACE-…`, `ETH-TIV`, `ETH-TIV-SUB-…`, `LANG-TIV`, `LANG-TIV-DIA-…`, `POL-TOR-TIV`, `CULT-FEST-…`, `EVT-…`, `PER-…`, `CLAIM-000123`, `SRC-000123`, `MEDIA-000123`. IDs are generated once from the name at creation (uppercase ASCII, hyphens) and never regenerated when the name changes.

### 3.2 Geography

**admin_units** (existing; states/LGAs unchanged). New rows and columns:

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| unit_type (+ `geopolitical_zone`, `ward`) | New values for zones and wards | enum | R | `ward` | Ward list: INEC or the state government (Tier 1) |
| official_code | Official code (INEC ward code, NBS LGA code) | varchar(30) | O | `BN/07/03` | Must cite the issuing body |
| latitude, longitude | Reference point (not a boundary) | decimal(10,7) | O | 7.3239, 9.0104 | Required: coords_source_id (never invented, §31) |
| coords_source_id | Source of the coordinates | int → sources | O (R if coords) | SRC-000412 | GIS dataset / official gazetteer |
| gis_status | Mapping readiness | enum (Step 5) | R, default `needs_gis` | `point_only` | none |
| headquarters_place_id | LGA headquarters (Step 3 D7) | int → places | O | Gboko | Statoids + one official source |

**communities** (new): a named social/traditional community.

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| id / stable_id | IDs | int / varchar(80) | R | `COMM-BENUE-GBOKO-MBAYION` | none |
| name / slug | Community name as used locally | varchar(200) | R | Mbayion | ≥1 source (Tier 1–4) |
| community_type | Kind of community | enum (Step 5) | R | `clan_area`, `kindred`, `town_quarter`, `village_group`, `traditional_community` | none |
| admin_unit_id | Smallest known administrative unit (LGA or ward), never more precise than the evidence (§6) | int → admin_units | R | Gboko LGA | The source must place it there |
| place_id | Physical settlement, if one exists | int → places | O | Gboko town | as above |
| parent_community_id | Nesting (kindred within clan area) | int | O | — | source |
| summary / description | Prose | text | O | — | every claim cited (via claims) |
| evidence_status, review_status, sensitivity, research_status | common | enum | R | — | — |

**places** (existing; localities and heritage): new columns

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| protection_status | Legal/heritage protection | enum (Step 5) | O | `national_monument`, `unesco_world_heritage`, `unesco_tentative`, `state_protected`, `none_known` | Official list (NCMM / UNESCO) |
| condition_status | Current condition where documented | enum (Step 5) | O | `good`, `threatened`, `damaged`, `destroyed`, `unknown` | dated source |
| condition_as_of | Date of the condition claim | date | O | 2024-06-01 | same source |
| (existing) place_type, admin_unit_id, lat/long, coords_source_id, status | — | — | — | Osun-Osogbo Sacred Grove, `sacred_site` | — |

### 3.3 People and languages

**ethnic_groups** (existing): new columns

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| group_level | Level in the classification (brief §3, §15) | enum (Step 5) | R, default `ethnic_group` | `subgroup` (Ukum under Tiv) | Classification must be sourced; if sources differ, a dispute is recorded |
| parent_id (existing) | Parent group for subgroup/clan/lineage | int | O | ETH-TIV | as above |
| classification_notes | How sources classify it, and disagreements | text | O | "Some sources treat X as a dialect community of Y…" | cited |
| canonical_name (= name) + entity_names | Canonical name; aliases, historical, colonial, exonyms and spellings in `entity_names` (§14) | — | R | Tiv; Munshi (colonial exonym) | each alias cited (entity_names.source_id) |

**languages** (existing): new/changed

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| lang_type (+ `variety`) | Adds "variety" for undetermined language-vs-dialect cases | enum | R | `dialect` | Glottolog / Ethnologue / academic |
| vitality_status | Controlled vitality (the free-text `vitality` is kept) | enum (Step 5) | O | `stable`, `threatened`, `endangered`, `moribund`, `extinct`, `unknown` | vitality_source_id required |
| documentation_notes | Grammars, dictionaries, archives | text | O | — | cited |

### 3.4 Relations (the knowledge graph)

**entity_relations** (existing): new qualifier columns

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| settlement_status | Brief §5: nature of a group's presence in a place | enum (Step 5) | R for present_in / indigenous_to / settled_in | `indigenous_core`, `indigenous_shared`, `historically_present`, `significant_contemporary`, `migrant_community`, `mixed`, `disputed`, `unknown` | source_id required; `disputed` needs a claim_dispute |
| location_type | Brief §4 A–E | enum (Step 5) | O | `core_homeland`, `current_indigenous`, `secondary`, `urban_contemporary`, `historical_presence` | as above |
| speaker_role | For speaks / spoken_in | enum (Step 5) | O | `first_language`, `second_language`, `lingua_franca`, `heritage` | source |
| sensitivity | As §3.1 | enum | R, default `public` | — | — |
| claim_id | The claim this relation expresses (optional bridge) | int → claims | O | CLAIM-000123 | — |
| (existing) source_id, evidence_status, valid_from/to, role, notes | unchanged | — | — | — | source_id required for published relations |

**relation_types** (existing, 29 codes) + proposed new codes (labels in Step 5): `indigenous_to`, `settled_in`, `subgroup_of`, `clan_of`, `dialect_of`, `linguistically_related`, `historically_related`, `shared_ancestry_tradition`, `political_alliance`, `historical_conflict`, `trade_relationship`, `cultural_exchange`, `shared_festival`, `shared_territory`, `disputed_relationship`, `migrated_from`, `migrated_to`, `headquarters_of`, `custodian_of` (institution → site/festival), `recorded_in` (oral history → community).
**Rule** (§13): relations between groups never imply common ancestry unless the type is `shared_ancestry_tradition`, which is labelled as tradition.

### 3.5 Culture

**cultural_records** (existing: festival, food, clothing, music, dance, art, craft, occupation, ceremony, oral tradition …): new columns

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| current_status | Is it practised today? | enum (Step 5) | O | `active`, `declining`, `revived`, `historical`, `unknown` | dated source |
| cultural_category | Top-level category for browsing (Step 5) | enum | R | `festivals_ceremonies` | — |
| season / month_from / month_to | When (festivals) | varchar / tinyint | O | "Late August" / 8 / 8 | cited |
| scope_level | Level at which the practice is documented (§8: no over-generalisation) | enum | R | `community`, `lga`, `subgroup`, `ethnic_group`, `regional` | The source must support this scope |

**cultural_record_attributes** (new): structured details without a column per item

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| id | — | int | R | — | — |
| cultural_record_id | Parent record | int | R | CULT-FOOD-… | — |
| attribute | Controlled key (Step 5) | enum | R | `ingredient`, `material`, `instrument`, `garment`, `occasion`, `preparation_step`, `performer` | — |
| value / local_value | Value in English / local language | varchar(255) | R / O | yam / ruam | source_id required |
| source_id | Source | int | R | — | — |

### 3.6 History and oral tradition

**timeline_events** (existing national events): new `event_kind` enum (Step 5): `political`, `administrative`, `migration`, `conflict`, `founding`, `religious`, `cultural`, `economic`, `colonial`, `archaeological`, `other`. Plus `nature`: `documented_history`, `oral_tradition`, `academic_interpretation`, `archaeological_evidence` (brief §9 and §10 labels). A migration uses `migrated_from` / `migrated_to` relations to places, groups and dates, with its nature label.

**oral_histories** (new)

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| id / stable_id | IDs | int / varchar | R | `ORAL-000001` | — |
| title | Descriptive title | varchar(255) | R | "Settlement of Mbayion (as told in 2027)" | — |
| tradition_type | origin / migration / founding / ancestral / creation / conflict / settlement | enum (Step 5) | R | `migration` | — |
| nature | Always labelled (§9) | enum | R | `oral_tradition`, `community_tradition`, `recorded_historical_account` | — |
| community_id / ethnic_group_id / admin_unit_id | Where and whose | int | O (≥1 R) | — | — |
| summary / transcript / translation | Text | text | O | — | — |
| field_record_id | Recording session | int → field_records | O | — | — |
| sensitivity | Often restricted | enum | R | `permission_required` | — |
| evidence_status / review_status | — | enum | R | `oral_tradition` | — |

### 3.7 Sources, claims, disputes, gaps

**sources** (existing): new `source_tier` tinyint 1–5 (brief §17; R for new sources), `language_id` (O) and `rights_notes` (O).

**claims** (new): the unit of knowledge for AI and citation (brief §34)

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| id / stable_id | IDs | int / varchar | R | `CLAIM-000123` | — |
| subject_table / subject_id | Entity the claim is about | varchar / int | R | ethnic_groups / ETH-TIV | — |
| predicate | What is claimed (controlled where possible) | varchar(60) | R | `present_in`, `founded_in_year`, `name_meaning`, `population` | — |
| object_table / object_id | Linked entity, if any | varchar / int | O | admin_units / NG-LGA-BENUE-GBOKO | — |
| value_text / value_number / value_date | Literal value, if any | text / decimal / varchar | O | "1946" | — |
| statement | The claim in plain words | varchar(500) | R | "The Tor Tiv stool was created in 1946." | — |
| geographic_level | Level it applies to (§37) | enum (Step 5) | R | `state` | — |
| claim_nature | fact / interpretation / oral tradition / disputed | enum (Step 5) | R | `documented_fact` | — |
| temporal_scope | current / historical / both | enum | R | `historical` | — |
| evidence_status | Brief's six-level scale (Step 5) | enum | R | `verified` | derived from claim_sources |
| dispute_id | Dispute group, if contested | int → claim_disputes | O | — | — |
| sensitivity / review_status | — | enum | R | — | — |

**claim_sources** (new)

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| claim_id / source_id | Link | int | R | — | — |
| stance | supports / contradicts / mentions | enum | R | `supports` | — |
| page_section / quote | Exact location, short quote | varchar / text | O (R when available) | "s.298" | — |
| accessed_on | Access date | date | R for web sources | 2026-09-25 | — |

**claim_disputes** (new): brief §19

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| id | — | int | R | — | — |
| topic | What is disputed | varchar(255) | R | "Five Tiv blocs or six intermediate areas" | — |
| nature | Kind of disagreement | enum (Step 5) | R | `classification`, `date`, `location`, `name`, `boundary`, `homeland`, `population`, `other` | — |
| status | open / resolved / unresolvable | enum | R | `resolved` | Resolution must cite a source |
| resolution_note | How it was resolved, if ever | text | O | — | cited |

(Competing claims point to the same dispute_id: Claim A + its sources, Claim B + its sources.)

**research_gaps** (existing): new columns for the brief's §28 gap table

| Field | Description | Type | Req. | Example |
|---|---|---|---|---|
| admin_unit_id | State/LGA the gap concerns | int | O | NG-LGA-… |
| gap_type | Controlled (Step 5) | enum | R | `community_level_missing`, `classification_disputed`, `oral_only`, `no_population`, `source_conflict`, `name_variants` |
| why_missing | Reason | text | O | "No published ward-level ethnography" |
| recommended_research | Next action (§35 field targets) | text | O | "Interview clan heads in …" |
| priority | high / medium / low | enum | R | `high` |

### 3.8 Media and field research

**media_assets** (new): brief §24. *Owner decision 25 Sep 2026: audio and video deferred; for now `media_type` covers photo / document / map / manuscript only.*

| Field | Description | Type | Req. | Example | Source requirement |
|---|---|---|---|---|---|
| id / stable_id | IDs | int / varchar | R | `MEDIA-000001` | — |
| media_type | photo / audio / video / document / map / manuscript | enum | R | `audio` | — |
| title / description | — | varchar / text | R / O | — | — |
| file_path / external_url | Stored file or external link | varchar | R (one) | — | — |
| date_created / date_precision | When recorded | varchar / enum | O | 1978 / year | — |
| place_id / admin_unit_id | Where | int | O | — | — |
| creator | Photographer / recorder | varchar | O | — | — |
| contributor_id | Who provided it | int → contributors | O | — | — |
| copyright_holder / licence | Rights | varchar | R | "© contributor; CC BY-NC" | — |
| permission_status | Permission to publish | enum (Step 5) | R | `granted`, `pending`, `refused`, `not_required`, `unknown` | written consent reference |
| cultural_restriction | Sensitivity reason | text | O | "Initiation masquerade — not for public display" | — |
| sensitivity | As §3.1 | enum | R | — | — |
| source_id | Where it came from | int | O | — | — |

**media_links** (new): media_id, entity_table, entity_id, role (`depicts` / `records` / `documents`). One asset can attach to a state, LGA, ward, community, ethnic group, language, festival, site, person or practice (§24).

**contributors** (new): id, name (or pseudonym), role (researcher / interviewee / community elder / institution), community_id, contact (restricted), consent_reference, notes. Personal details are never public.

**field_records** (new): brief §36

| Field | Description | Type | Req. | Example |
|---|---|---|---|---|
| id / stable_id | IDs | int / varchar | R | `FIELD-000001` |
| researcher_id / contributor_id | Who recorded / who spoke | int → contributors | R / O | — |
| recorded_on | Date | date | R | 2027-01-15 |
| place_id / admin_unit_id / community_id | Where | int | R (≥1) | — |
| consent_status / permission_status | Consent and publication permission | enum (Step 5) | R | `written_consent` / `granted` |
| recording_media_id | Audio/video | int → media_assets | O | — |
| transcript / translation / notes | Text | text | O | — |
| verification_status | Evidence scale | enum | R | `reported` |

### 3.9 Progress tracking

**research_status** (new): brief §26 Phase 5

| Field | Description | Type | Req. | Example |
|---|---|---|---|---|
| admin_unit_id | State (or LGA) | int | R | NG-STATE-BENUE |
| lgas_total / lgas_researched / lgas_verified / lgas_needs_review | Counts | smallint | R | 23 / 23 / 14 / 9 |
| sources_count | Distinct sources used | smallint | R | 41 |
| status | Controlled (Step 5) | enum | R | `in_progress` |
| last_reviewed_at / reviewer | QC trail (§26 Phase 4) | datetime / varchar | O | — |

---

## 4. What this means in practice

- **Nothing existing is renamed or removed.** The public pages, sitemap, AI answers and Tiv collection keep working unchanged.
- **Size of the change:**
  - 8 new tables: communities, cultural_record_attributes, oral_histories, claims, claim_sources, claim_disputes, media_assets / media_links, contributors / field_records, research_status.
  - New columns on 9 existing tables.
  - New enum values on 3 tables (admin_units.unit_type, languages.lang_type, timeline_events).
  - About 20 new relation types.
- **Rollout (after approval of Steps 4–5):** migration 002 with a rollback script, tested on a copy (as migration 001 was). Then backfill `stable_id`s (no content change), then zones and headquarters as records (sourced), then admin screens, then public views. Each is its own approved step.
- **Research method alignment:** new research batches will write claims + claim_sources as well as the existing entity_sources, so every published sentence can be cited in AI answers.

## 5. Open questions for the owner

1. **Wards:** use INEC's ward list (about 8,800 wards) as the official framework? It would be loaded only when a state is researched, never all at once without sources.
2. **Zones as records:** create the 6 geopolitical zones as records (with descriptions and sources) while keeping the zone field? Recommended.
3. **Evidence scale:** adopt the brief's six levels for new claims and map the existing values (Step 5 proposes the mapping).
4. **Media storage:** ~~local server or external store for large audio/video?~~ **Owner decision 25 Sep 2026: audio and video are skipped for now (later work).** The media layer, if built now, covers photographs, documents, maps and manuscripts only. Audio/video fields (and field-recording files) are deferred.
5. **Sensitivity default:** `public` for published research, and `permission_required` by default for oral histories and field media? Recommended.

## 6. Owner decisions (25 September 2026)

| Question | Decision |
|---|---|
| Q1 Wards | **Yes: INEC's ward list**, loaded one state at a time, only when that state is researched, each ward with its source. |
| Q2 Zones | **Yes: the 6 geopolitical zones become records** (with descriptions and sources); the existing zone field is kept. |
| Q3 Evidence scale | **Yes: adopt the brief's six levels** (VERIFIED / WELL DOCUMENTED / REPORTED / NEEDS CORROBORATION / DISPUTED / UNCERTAIN) for new claims; the existing values are mapped (Step 5). |
| Q4 Media | **Audio and video skipped for now** (later work); photos, documents, maps and manuscripts only. |
| Q5 Sensitivity | **Yes:** `public` by default for published research; `permission_required` by default for oral histories and field material. |

