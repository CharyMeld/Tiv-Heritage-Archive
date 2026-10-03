# Nigeria Heritage Expansion — Stage 1: Architecture Audit & Proposal

**Date:** 2026-09-24
**Status:** Audit complete. Nothing in code, schema, or data was changed.
**Method:** Read the local codebase, and ran read-only `information_schema` / `SELECT` queries against the production database (`tiv_archive` on the VPS). The local MySQL is not running, so production was the reference.

---

## 1. CURRENT SYSTEM

| Layer | What exists |
|---|---|
| Stack | Custom PHP MVC with no framework. PHP 8.2-fpm + MySQL 8 + **nginx** in production (`.htaccess` is only used locally). |
| Front controller | `index.php` has 297 route definitions and a single `$router->dispatch()`. |
| Router | `core/Router.php` compiles each route to a regex. `{param}` matches `[a-zA-Z0-9_-]+`, and `{param:a\|b}` is a constrained alternation. The first match wins. |
| Base classes | `core/Controller.php` (`requireAuth/Role/Admin/Moderator`), `core/Model.php` (PDO CRUD), `core/View.php`, `core/Cache.php` (file cache in `storage/cache`), `core/ApiAuth.php` (bot protection and API keys). |
| URL building | `url($path)` = `SITE_URL . '/' . $path` (`config/security.php:414`). `SITE_URL` is auto-detected from `HTTP_HOST`. |
| Layout/nav | `views/layouts/main.php` + `views/partials/nav.php` (mega-menus): Home · Language · Literature · Culture · History · Archive · Community · Suggestions · References · About. |
| Users/roles | `users.role` ∈ user(1) / contributor(2) / moderator(3) / admin(4). `community_members.member_type` ∈ contributor / researcher. |
| Contribution | `submissions` (Tiv categories only) → admin review (approve/reject/needs_revision) → content tables. `content_items` has a public submit form per section/subcategory. |
| Admin | `/admin/*`: content CRUD per category, sources, links, intelligence re-index, historical figures, timeline, Bible, translation, grammar, marketing, outreach, community, suggestions, users. |
| SEO | `services/SeoHelper.php` (meta, JSON-LD, breadcrumbs, slugs, `<picture>`) and `SitemapController` (~2,976 URLs after the AdSense cleanup). Detail URLs use `/{type}/{id}-{slug}`, where the slug is computed on the fly and wrong or missing slugs 301 to the canonical form. |
| Marketing | Newsletter, social autopilot, and UTM links all read from the content tables. |

## 2. CURRENT DATABASE (production, 69 tables)

**Tiv content tables**

| Table | Rows | Purpose |
|---|---|---|
| `daily_words` | 4,403 | Tiv dictionary (self-FK `root_word_id`, `dialect_region` text) |
| `translation_phrases` | 3,662 | Phrase corpus for the translation engine |
| `tiv_plants` | 369 | Plants |
| `tiv_names` | 287 | Personal names |
| `tiv_proverbs` | 235 | Proverbs |
| `tiv_alphabet` | 67 | Alphabet |
| `historical_figures` | 65 | People (**all have `state='Benue'`**, all published) |
| `tiv_animals` / `tiv_foods` / `tiv_festivals` | 48 / 15 / 8 | Culture |
| `timeline_events` | 18 | Historical events (Tiv-specific `era` enum) |
| `content_items` | 12 | Articles/documents, grouped by `section`/`subcategory` |
| `tiv_grammar_rules` | 12 | Grammar |
| `bible_verses` / `bible_chapters` | 29,889 / 20 | Tiv Bible |
| `learning_videos` | 9 | Lessons |

**Knowledge, search, and provenance tables**

| Table | Rows | Purpose |
|---|---|---|
| `sources` | 102 | Citations. `source_type` enum(oral_tradition, book, research, interview, community_submission), plus `verification_status` enum(verified, needs_corroboration, disputed) |
| `timeline_event_sources` | 35 | Many-to-many link between events and sources (**the only multi-source junction**) |
| `archive_knowledge_links` | 3,263 | Polymorphic edges (`from_table/from_id → to_table/to_id`, `relationship`, `weight`, `auto_generated`). Nearly all are auto-generated (words↔proverbs/Bible) |
| `knowledge_links` | 8 | An older polymorphic edge table, edited manually in `/admin/links` (figure ↔ figure "succeeded", etc.) |
| `archive_search_index` | 9,275 | Unified FULLTEXT index plus an Ollama embedding per row (`uk_source(source_table, source_id)`) |
| `timeline_event_categories` | 33 | Event categories (enum) |
| `festival_gallery`, `historical_figure_gallery`, `timeline_event_gallery` | 15 / 0 / 0 | Per-entity image galleries |

Other tables cover users, community, submissions, marketing, email, and outreach. None of them is affected by this expansion.

## 3. CURRENT TIV ARCHIVE — how Tiv is represented

- **"Tiv" is implicit, not an entity.** No table holds a row for the Tiv people, the Tiv language, Benue State, or any LGA. Tiv identity comes from table names (`tiv_*`) and column names (`tiv_name`, `tiv_word`). Every record is assumed to be Tiv.
- **Location is free text.** Examples: `historical_figures.state` (default `'Benue'`), `local_government` (49 of 65 are NULL, and the rest hold values like "Gboko" and "Vandeikya"), `clan`, `district`. `timeline_events.location` holds strings like *"Benue Province, Northern Nigeria Protectorate"*, *"Tivland, Northern Region"*, *"Nigeria, including Benue-Plateau State"*. `tiv_festivals.location` holds *"Tiv communities, especially in Benue State"*.
  → The data already describes **historical administrative units** (Province, Region, Benue-Plateau State), but nothing can connect them.
- **Periods are Tiv-specific.** `timeline_events.era` has values like "Migration Era (1600-1750)" and "Expansion Era (1750-1850)". `historical_figures.historical_period` uses national-style eras.
- **Categories are Tiv-specific.** `historical_figures.subcategory` enum is (Tor Tiv, Ator, Uter, Tor-Kpande…).

## 4. WHAT CAN BE REUSED

| Keep as-is | Why |
|---|---|
| Router, Controller, Model, View, layout, CSS | Enough for `/nigeria/...` with no framework change |
| `SeoHelper`, breadcrumb partial, `SitemapController` | Already generic |
| `sources` table | Extend it (additive enum values and columns); do not replace it |
| `archive_search_index` + `ContentIndexer` + `EmbeddingSearch` | Already polymorphic (`source_table/source_id`). New entity types only need new `index*()` methods |
| `ArchiveIntelligence` retrieval pipeline | Already follows the intent → search → graph traversal → cited answer pattern |
| `historical_figures` | Becomes the **single Person table** for Tiv and national people, so people are never duplicated |
| `timeline_events` | Becomes the **single Event table** |
| All `tiv_*` tables, `daily_words`, Bible, translation tables | Unchanged. They are linked into the national graph through relations, not migrated |
| Roles and review workflow | Reused for research review |

## 5. WHAT MUST CHANGE (minimum)

1. **Real entities for geography, peoples, and languages.** These don't exist yet. Add tables for admin units, places, ethnic groups, and languages.
2. **An evidence-aware relation table.** The two existing edge tables have no source, no evidence status, and no validity dates. Nigerian relationships change over time: Tiv territory was in Benue Province, then the Northern Region, then Benue-Plateau State (1967), then Benue State (1976). So each edge needs `valid_from/valid_to`, `source_id`, and `evidence_status`.
3. **Collection membership.** Tiv listing pages (`/timeline`, `/historical-figures`) currently show *every* row. Once national events and people go into those shared tables, the Tiv pages would fill up with non-Tiv records. A membership flag or table is needed **before** any national person or event is inserted.
4. **A general many-to-many for sources.** Only events have one today (`timeline_event_sources`).
5. **Evidence status and temporal status** on events: historical vs announced/planned/projected.
6. **Additive enum extensions:** `sources.source_type`, `sources.verification_status`, `historical_figures.category`, `timeline_events.era`.

Nothing needs to be renamed or deleted.

## 6. PROPOSED NATIONAL ARCHITECTURE

```
                ┌──────────── ONE DATABASE (tiv_archive) ─────────────┐
                │                                                     │
  Tiv tables ───┤  tiv_* , daily_words, bible, translation (unchanged)│
                │         │ linked via entity_relations               │
  Shared   ─────┤  historical_figures (Person)  timeline_events (Event)│
                │  sources (+entity_sources)                          │
  New      ─────┤  admin_units ─ admin_unit_changes                   │
  national      │  places   ethnic_groups   languages   polities       │
                │  cultural_records   historical_periods              │
                │  entity_names   entity_statistics                   │
                │  entity_relations (the knowledge network)           │
                │  collections + collection_items                     │
                │  research_batches  research_gaps                    │
                └─────────────────────────────────────────────────────┘
   Public: /… (Tiv, unchanged)         /nigeria/… (national)
```

- **Tiv is a collection, not a silo.** `collections` holds `tiv` and `nigeria`, and later could hold `yoruba`, `igbo`, and so on. `collection_items(entity_table, entity_id, collection)`. Tiv pages filter on `collection=tiv`.
- **Domain ≠ DB.** Nothing in the schema mentions a hostname. See §8 for the routing layer.

## 7. PROPOSED ENTITY/RELATIONSHIP MODEL

Of the ~35 entity types in the brief, **11 new tables** cover all of them. The rest are *types* inside those tables, or existing tables.

| New table | Covers | Key columns |
|---|---|---|
| `admin_units` | Country, Region, Province, Division, State, FCT, LGA (self-parented) | `unit_type`, `parent_id`, `name`, `slug`, `capital_place_id`, `geopolitical_zone`, `created_on`, `abolished_on`, `date_precision`, `evidence_status` |
| `admin_unit_changes` | Administrative history | `from_unit_id`, `to_unit_id`, `change_type` (created_from, split_into, merged_into, renamed, boundary_change, capital_change, abolished), `effective_date`, `date_precision`, `source_id`, `evidence_status` |
| `places` | City, town, village, historical place, heritage site, archaeological site, museum, archive | `place_type`, `admin_unit_id` (current), `lat/lng` + `coords_source_id` (nullable, never guessed) |
| `ethnic_groups` | Ethnic groups, sub-groups | `parent_id`, `name`, `slug`, `summary`, history fields, `evidence_status` |
| `languages` | Languages, dialects, families | `lang_type` (family/language/dialect), `parent_id`, `iso639_3`, `glottocode`, `writing_system`, `vitality`, `evidence_status` |
| `polities` | Kingdom, empire, emirate, chiefdom, caliphate, confederacy, traditional institution | `polity_type`, `founded`, `ended`, precision fields |
| `cultural_records` | Festival, food, clothing, music, dance, craft, architecture, naming, marriage, burial, ceremony, oral tradition, folklore, proverb, occupation, agricultural/indigenous knowledge | `record_type`, `nature` (documented_practice / oral_tradition / folklore / scholarly_interpretation), `local_name`, `language_id` |
| `historical_periods` | Historical periods (national and group-specific) | `name`, `start_year`, `end_year`, `scope` |
| `entity_names` | Alternative, historical, and colonial names and spellings (e.g. "Munshi" for Tiv) | `entity_table`, `entity_id`, `name`, `name_type`, `valid_from/to`, `source_id` |
| `entity_statistics` | Population and speaker estimates. **Kept separate so conflicting figures can coexist** | `entity_table`, `entity_id`, `metric`, `value`, `year`, `method`, `source_id`, `evidence_status` |
| `entity_relations` | The knowledge network | `from_table`, `from_id`, `relation_type`, `to_table`, `to_id`, `valid_from`, `valid_to`, `date_precision`, `source_id`, `evidence_status`, `notes`, `research_batch_id` |

Supporting tables: `entity_sources` (entity ↔ source, with `page_section`, `claim`, and `supports|contradicts`), `collections`, `collection_items`, `research_batches`, `research_gaps`, and `relation_types` (a controlled vocabulary with inverse labels).

**Reused as entities:** Person = `historical_figures`, Event/Future plan/Projection = `timeline_events` (+ `temporal_status`), Document = `content_items`, Source = `sources`, Tiv festival/food/proverb = the existing `tiv_*` rows.

**Deferred** until real content needs them: Artifact, Research Record (covered by `research_batches`), Dialect-level geography.

Relation examples (`relation_types`):

```
ethnic_groups ─speaks→ languages          ethnic_groups ─present_in→ admin_units/places
languages ─spoken_in→ admin_units         historical_figures ─member_of→ ethnic_groups
historical_figures ─born_in/buried_in→ places   historical_figures ─ruled→ polities (valid_from/to = reign)
timeline_events ─occurred_in→ places/admin_units ─involved→ historical_figures/ethnic_groups
tiv_festivals ─celebrated_by→ ethnic_groups     polities ─controlled_territory→ admin_units (dated)
places ─formerly_part_of→ admin_units (dated)   sources ─documents→ any
```

Tiv is connected as: `ethnic_groups(Tiv) ─speaks→ languages(Tiv) ─spoken_in→ admin_units(Benue, Nasarawa, Taraba, …, each only when a source supports it)`. Every existing Tiv row joins `collection=tiv`, and relevant rows get a `celebrated_by / member_of → Tiv` edge.

**Why a new relation table instead of extending `archive_knowledge_links`?** That table is auto-populated by `ContentIndexer::buildAutoLinks()` and holds machine-inferred word matches. Mixing sourced, dated, human-reviewed facts with auto-inferred edges would blur the line between evidence and inference. `entity_relations` holds curated facts only. The AI reads both but labels them differently.

## 8. URL STRATEGY

- **Every existing URL stays unchanged**: `/`, `/archive/*`, `/word/{id}-{slug}`, `/historical-figure/…`, `/timeline…`, `/language|literature|culture|history/*`, `/bible/*`, `/translate`, and the rest. No redirects are needed.
- New routes live under the `nigeria` prefix, which conflicts with no existing route:
  `/nigeria/`, `/nigeria/states/`, `/nigeria/states/benue`, `/nigeria/states/benue/lgas/gboko`, `/nigeria/ethnic-groups/tiv`, `/nigeria/languages/tiv`, `/nigeria/places/{slug}`, `/nigeria/events/`, `/nigeria/people/`, `/nigeria/kingdoms/{slug}`, `/nigeria/culture/{type}/{slug}`, `/nigeria/periods/{slug}`, `/nigeria/timeline`.
- **National entities get stored, unique, name-only slugs** (e.g. `benue`), unlike the Tiv `{id}-{slug}` pattern. Clean URLs matter here and state names are stable. A slug that changes gets a `slug_history` 301.
- **One canonical URL per record.** People and events keep their existing canonical URLs (`/historical-figure/…`, `/timeline-event/…`) even when listed under `/nigeria/people/`, so the same page never appears twice.
- **Subdomain readiness:** every national link is built with one helper, `nigeria_url($path)`. Today it returns `SITE_URL/nigeria/…`. Later, a config switch `NIGERIA_BASE_URL=https://nigeria.tivheritage.com` plus an nginx `server_name` block that internally rewrites to `/nigeria/…` moves the whole section with no DB change. The same approach works for a separate domain.
- **SEO guard** (keeps the 2026-09-23 AdSense cleanup intact): national entity pages carry `noindex` and stay out of the sitemap until they meet a content threshold (e.g. a summary plus at least one source). Empty stubs never get indexed.

## 9. FRONTEND STRATEGY

- Same `main.php` layout, typography, spacing, cards, breadcrumbs, and responsive nav.
- Add one nav item, **"Nigeria Heritage"**, as a mega-menu: Explore Nigeria · States & FCT · Ethnic Groups · Languages · History & Timeline · People · Places · Kingdoms & Institutions · Culture · Heritage Sites & Museums.
- A subtle distinction: a `body.section-nigeria` class switches the accent colour (e.g. a deep green) and uses a national banner on `/nigeria/`. Everything else stays shared.
- Cross-links in both directions: Tiv pages show a "Tiv in the Nigeria Heritage Archive →" card, and `/nigeria/ethnic-groups/tiv` links heavily back into the Tiv collection (dictionary, proverbs, names, and so on).
- Every entity page shows its evidence badge (e.g. "Oral Tradition", "Disputed") and a Sources block.

## 10. AI STRATEGY

1. `ContentIndexer` gets `index*()` methods for each new table. `archive_search_index` needs no schema change, and embeddings are backfilled through Ollama as they are today.
2. `ArchiveIntelligence` gets **graph intents** that follow `entity_relations`:
   - "Which states have documented Tiv communities?" → `ethnic_groups(Tiv) ─present_in→ admin_units(type=state)`
   - "What ethnic groups are in Benue?" → the inverse traversal
   - "What happened in Makurdi?" → `places ← occurred_in ─ timeline_events ─ involved → people`, with sources
3. Every answer cites the relation's `source_id` and its evidence status ("oral tradition", "disputed"). An empty traversal returns *"not yet documented in the archive"* rather than a guess.
4. Charymeld's Tiv persona stays as it is. National answers are allowed, but scoped: if a question is outside the archive, it says so.

## 11. DATA QUALITY STRATEGY

- **`evidence_status`** (a shared enum on every new entity and relation): verified, well_documented, multiple_sources, single_reliable_source, community_source, oral_tradition, scholarly_interpretation, disputed, needs_corroboration, unverified, outdated.
- **`temporal_status`** on events: historical, current, announced, planned, proposed, projected, forecast. A planned event can't be marked historical without an explicit, sourced status change.
- **`date_precision`**: exact, month, year, circa, decade, century, range, unknown. Free-text dates are kept as they are.
- **Conflicts** are stored, never overwritten. Conflicting statistics become multiple `entity_statistics` rows. Conflicting accounts get `entity_sources.stance='contradicts'` plus `alternative_dates_notes`.
- **No automatic merging.** Duplicate candidates (from name and alternative-name matching) go to a review queue.
- **Status never upgrades silently.** Status changes are logged in `activity_log`, which already exists.
- The existing `sources.verification_status` is kept, and new values are appended.

## 12. RESEARCH STRATEGY

Each batch is one `research_batches` row with a scope (e.g. "Benue State — administrative history"), and follows the 15-step workflow from your brief. It ends with a report listing what was inserted, the sources used, the unresolved items (recorded as `research_gaps`), and duplicate candidates. **Every batch stops for your review.**
Suggested order:
1. Nigeria plus the 36 states and FCT (name, capital, creation date, zone), with predecessor lineage.
2. Benue State in depth: LGAs and administrative history back to Benue Province.
3. Tiv as an ethnic group and language, linked to what already exists.
4. Neighbours of Tiv (Idoma, Igede, Jukun, …).
5. Then outward, state by state.

## 13. RISKS

| # | Risk | Mitigation |
|---|---|---|
| R1 | National people/events flood the Tiv pages (`/timeline`, `/historical-figures`, the homepage, newsletter rotation, social autopilot, the sitemap), which all query their tables unfiltered | Add `collection_items` and the Tiv filter **before** inserting any national person or event, then audit every query of those two tables |
| R2 | The AdSense "low-value content" problem comes back through thin national stub pages | `noindex` plus sitemap exclusion until a content threshold is met |
| R3 | Enum ALTERs on large tables | Appending enum values is metadata-only in MySQL 8 (instant). Run a production DB backup first |
| R4 | The marketing autopilot and newsletter pick up national records as "Tiv" content | Filter those generators by collection (same fix as R1) |
| R5 | Router order: a greedy future route could catch `nigeria/...` | Keep `/nigeria` routes grouped. There is no `{x}` root catch-all today |
| R6 | Bot protection (`ApiAuth::protectPage`) doesn't cover `/nigeria` | Add `'nigeria'` to `$_protectedPrefixes` |
| R7 | `deploy.sh` rsyncs the whole dirty tree (about 60 files are uncommitted right now) | Deploy the national files by explicit path, or commit and clean up first |
| R8 | The AI mixes Tiv and national answers or overstates relations | Separate curated and auto edge tables, and cite evidence status in every answer |
| R9 | Unused slug columns on `historical_figures`/`timeline_events` get assumed to be canonical | Leave them alone. New tables use their own slugs |
| R10 | Local dev DB is offline | Start LAMPP MySQL and load a fresh production dump for local testing before running any migration |

## 14. IMPLEMENTATION PLAN (each step stops for your confirmation)

| Step | Work | Touches production? |
|---|---|---|
| **1** | This audit and proposal | No (read-only) |
| **2** | Design review: you confirm or adjust the open decisions below. Then I write the exact migration SQL (additive only) for review | No |
| **3** | Local environment: start MySQL, import a production dump, run the migration locally, test every existing Tiv page | No |
| **4** | Collection membership and the Tiv filter on shared tables (R1/R4), verified locally | No |
| **5** | Models and admin CRUD for the new entities, plus the research dashboard | No |
| **6** | Public `/nigeria/` section: routes, views, nav item, `nigeria_url()`, noindex guard | No |
| **7** | Search index and AI graph intents | No |
| **8** | Production deploy: DB backup → migration → explicit-path file deploy → smoke tests | **Yes (asks first)** |
| **9** | Research batch 1: Nigeria + 36 states + FCT (sourced) | Yes (data) |
| **10** | Research batch 2: Benue administrative history + LGAs; connect Tiv | Yes (data) |
| **11+** | Controlled batches, one scope at a time | Yes (data) |
| **Final (and after every batch)** | **Google Search / AdSense compliance gate.** See §15 | Read-only audit |

## 15. GOOGLE SEARCH & ADSENSE SAFEGUARDS (requested 2026-09-24)

The last AdSense rejection ("low value content", 2026-09-23) came from **thin pages at scale**: 65% of the sitemap was dictionary word pages with a median of 38 words, 17% was Bible text not original to the site, and several empty sections were indexed. A national archive could easily repeat this (774 LGA stubs, 500+ ethnic group stubs). These rules are built into every step:

**Indexing rules (enforced in code, not by hand)**
1. **Indexability gate.** A national entity page is indexable only when `review_status='published'`, it has at least ~300 words of original prose, it has at least one attached source, and its `evidence_status` is not `unverified`. Everything else gets `<meta name="robots" content="noindex, follow">` (the `$noindex` mechanism already exists in `main.php`) and is left out of the sitemap. One shared `isIndexable()` check drives both, so they can't drift apart.
2. **Listing pages** (`/nigeria/states/`, `/nigeria/ethnic-groups/`, …) stay noindexed until they list enough substantive records, and they are never empty shells.
3. **No mass stub generation.** Research batches create a few rich records rather than hundreds of one-line placeholders. For example, the 36 states + FCT are seeded with a sourced summary each, and LGAs start as data rows shown *on the state page* without their own indexable pages.
4. **Original writing only.** Text is written in our own words from the sources, and every source is cited. No copying from Wikipedia, government sites, or books (this avoids both scraped/duplicate-content penalties and copyright problems). Short quotes are allowed with attribution.
5. **One canonical URL per record.** Tiv records keep their existing URLs, and national records use their `/nigeria/` URLs. The same content never appears under two URLs.
6. **Existing protections stay in place:** bare words and Bible pages remain noindexed, and hidden words still return 404.

**Trust signals (E-E-A-T)**
7. Update the About page and add an **Editorial & Sources Policy** page covering how research works, what the evidence statuses mean, and how corrections work. Also make `SITE_NAME` consistent ("Tiv Culture Archive" vs "Tiv Heritage Archive").
8. Every entity page shows its evidence status, sources, and last-updated date.
9. **Sensitive topics** (ethnic conflicts, disputed histories, religion) are written neutrally with multiple perspectives and sources, and nothing is inflammatory. This avoids both Google's content-policy problems and harm to the communities involved.

**Technical**
10. Structured data must be accurate: `Place`, `Person`, `Event` (`eventStatus` for planned/announced events, never shown as having happened), `Language`, and `BreadcrumbList`. Nothing is marked up that isn't visible on the page.
11. Unique `<title>` and meta description for every indexable page, cached queries, and no layout shift from new components.
12. Ad placement stays unchanged (`ADSENSE_ENABLED` is still false). There are no ads on noindexed or thin pages.

**Compliance gate (the final step, and a check after each research batch)**
- Crawl every `/nigeria/` URL and the sitemap. Report the indexable count, word-count distribution, pages with no sources, duplicate titles/descriptions, noindex-vs-sitemap mismatches, and broken links.
- Confirm the proportion of thin pages in the sitemap has **not increased** compared with the 2026-09-23 baseline of 2,976 URLs.
- Only then submit the updated sitemap in Search Console and, when appropriate, request the AdSense review.

### Open decisions for Step 2

1. **Collection membership:** a `collection_items` table (recommended; supports future Yoruba, Igbo, and other collections) or a simple `in_tiv_collection` flag.
2. **People/events canonical URL:** keep `/historical-figure/…` for all people (recommended; one canonical, zero redirects) or give national people `/nigeria/people/{slug}`.
3. **Table naming:** plain names (`ethnic_groups`, `languages`, …), which is recommended, or a prefix such as `ng_`. Plain names follow the "domain ≠ database" rule.
4. **Nav label:** "Nigeria Heritage" (recommended) or another label.
