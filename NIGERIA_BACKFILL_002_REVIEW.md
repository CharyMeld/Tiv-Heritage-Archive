# Backfill for migration 002 — review

Approved by the owner on 25 Sep 2026 ("YES GO AHEAD"). It was built and tested locally and is **not deployed**.
Script: `database/research/backfill_002.php` (dry run by default; `--apply` commits; `--revert --apply` undoes).

It fills only the new, empty fields that migration 002 added, using the rules approved in Step 5 and applied in the Benue sample (Step 6). **No existing text, value or date is changed**, and each record's "last updated" time is kept.

## What it does

| # | Item | Result on a copy of production |
|---|---|---|
| 1 | Source type and tier | 269 of 280 sources classified (table below); 11 left empty (see "Left for you") |
| 1b | Sources that copy each other | 6 pairs linked, counted as one source: Kano, Bayelsa, Borno, Edo (named in Step 5), plus Kwara and Bauchi (found in batches 014–015) |
| 2 | The six geopolitical zones as records | 6 zone records + 37 state→zone links, saved as **drafts**: they are not shown publicly until the public-views step |
| 3 | Permanent IDs | 982 set, no clashes. Examples: `NG`, `NG-ZONE-NORTH-CENTRAL`, `NG-STATE-BENUE`, `NG-FCT`, `NG-LGA-BENUE-GBOKO`, `NG-LGA-FCT-ABAJI`, `NG-LGA-PLATEAU-QUA-AN-PAN`, `NG-REGION-NORTHERN`, `NG-STATE-BENUE-PLATEAU` (abolished), `ETH-TIV`, `LANG-TIV`, `POL-TOR-TIV`, `PLACE-ABUJA`, `PER-…`, `EVT-…` |
| 4 | Six-level evidence scale | 1,452 records and links mapped (table below). Tiv-collection people and events are not mapped (their evidence field is empty), as Step 5 says |
| 5 | Research-progress table | One row per state and the FCT (37), status **not started**, with the LGA count and number of sources, and the note "State-level history published; LGA-by-LGA research under the master brief not started." |

### Source tiers

| Tier | Types (count) |
|---|---|
| 1 Official | Constitution (legislation) 1; federal/state government websites, including NIPC and the Benue Finance Ministry, 67 |
| 2 Academic | journal articles 10; academic books 3 (Blench's Atlas, Makar, the Ethnographic Survey); linguistic databases 3 (Glottolog, ISO 639-3); research report 1 (tDAR); Bohannan's encyclopedia article 1 |
| 3 Reference | encyclopedias 121 (mostly Wikipedia); reference databases 40 (City Population, Statoids, WorldStatesmen); news 8; EUAA 1 |
| 4 Community | community organisations 9 (I am Benue, Mutuk, NKST); community submissions 2 |
| 5 General web | 2 (First Class Nigeria; an Academia.edu paper whose author is unverified) |

Three sources differ from the default tier for their type. The reason is recorded in the activity log:
- **Bohannan, "Tiv" (Encyclopedia of World Cultures):** raised to Tier 2 because the author is a scholar, as in the Benue sample.
- **EU Asylum Agency (EUAA) country guidance:** lowered from 1 to 3. It is a foreign agency's summary, so it is secondary for Nigerian administration. *You may prefer Tier 1.*
- **Academia.edu paper:** Tier 5 because its author is not verified.

### Evidence mapping (old → new)

| Records | Old value | New level | Count |
|---|---|---|---|
| LGAs and states resting on the Constitution | verified | verified | 770 |
| States, regions, historical states | multiple sources | verified (a Tier 1–2 source and ≥2 independent sources) | 43 |
| | multiple sources | well documented (all Tier 3 or copies): 3 abolished states, the 5 LGAs missing from the Constitution transcription, the 6 zones | 14 |
| | single source | reported (Western and Mid-Western Regions) | 2 |
| Capitals and places | multiple sources | verified | 37 |
| Ethnic groups | multiple sources → verified 10; needs corroboration → needs corroboration 1 | | 11 |
| Languages | multiple sources → verified 13; single source (Tier 1–2) → well documented 1 | | 14 |
| Kingdoms and titles | verified 2 (Jechira, Sankera); well documented 6 (including Tor Tiv) | | 8 |
| Links between records | verified 128; well documented 108; reported 25; needs corroboration 17 | | 278 |
| Statistics | verified 2; well documented 159 (2006 census figures, NPC origin, as in the sample); reported 114 (areas and other figures from Tier 3 sources) | | 275 |

Links and statistics store one main source, and their notes name any other sources that agree. They are graded from that main source, as the Benue sample did (for example, Atlas-backed links are verified; I am Benue plus Wikipedia is well documented).

**One difference from the sample:** the sample called the 1946 creation of the Tor Tiv office "verified". The rule gives the Tor Tiv record **well documented**, because none of its sources is Tier 1–2. The rule's result is used; an official or academic source would raise it.

### The zone records (drafts)

Each has the same wording, sourced from Wikipedia "States of Nigeria" and EUAA, with the Constitution search from batch 001:
> *North Central is one of Nigeria's six geopolitical zones. It comprises Benue, Kogi, Kwara, Nasarawa, Niger, Plateau and the Federal Capital Territory. The zones are not defined in the 1999 Constitution.*

The existing zone field on each state is kept. The open gap "when and how the zones were adopted" (batch 001) remains open.

## Left for you

- **11 sources are not classified.** These are old Tiv-collection biography sources with no URL or publisher that shows their type: #6, #8, #9, #11, #12, #15, #49 (no URL), and #23 blerf.org, #36 madeinbenue.com, #52 beegeagle.wordpress.com, #53 vmi.edu. They can be classified by hand later.
- **Not in this step:** LGA headquarters and coordinates (these need sources, and belong to Phase 3), setting records to "approved", and the public display of the new fields.

## Tests (copy of production, MySQL 8.0.46)

- Every existing value in all 98 tables is unchanged, including "last updated" times (checksum of the original columns of all existing rows).
- A second run changes nothing, because it only fills what is empty.
- `--revert` returns the database exactly to its state after migration 002. Only the audit entries in activity_log remain.
- The site was run against the copy. 27 public pages (the Nigeria home, states, the FCT and its area councils, ethnic groups, languages, Tor Tiv, places, search for "north central", historical figures, timeline, sitemap) are byte-identical before and after. The only exception is the home page's random "featured food". The sitemap has 3,018 pages, and there are 0 PHP errors.

## Deploy (on "deploy")

1. Back up the database to `/root/backups/nigeria_backfill002_*`.
2. Copy the script.
3. Dry run, then `--apply`.
4. Re-check the checksums, pages, sitemap and error log.
