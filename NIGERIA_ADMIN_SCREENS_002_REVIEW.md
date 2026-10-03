# Admin screens for the migration-002 fields — review

Owner's instruction: "next" (25 Sep 2026), after the backfill was deployed. The Step 4 rollout lists "admin screens" and "public views" as separate steps, so this step is the **admin screens only**. Public pages are unchanged; the public views are the next step.

It was built and tested locally and is **not deployed**.

## Why this step was needed now

The admin's "Type" lists did not know the new values (geopolitical zone, ward, variety, and the three new culture types). Opening a zone record and pressing Save would have silently changed it to "Country". This is fixed. As an extra guard, any stored value missing from a list is now shown as "(unrecognised — choose a value)" and the save is refused, instead of the value being replaced.

## What the admin can now do

| Area | New in the edit forms |
|---|---|
| All national records | **Permanent ID** shown next to the title. It is created automatically for new records (e.g. `COMM-BENUE-GBOKO-…`, `CLAIM-000001`) and never changes. The forms also gain the **six-level evidence scale**, **sensitivity** and **research status**. The earlier evidence field is kept, since the public pages still use it |
| States, LGAs, zones, wards | Official code (e.g. INEC ward code), LGA headquarters, coordinates (a source is required), map status |
| Places | Protection (national monument, UNESCO…), condition, condition date |
| Ethnic groups | Level (group / subgroup / clan / lineage / community identity), classification notes |
| Languages | "Variety" type, controlled vitality (a source is required), documentation notes |
| Culture | 3 new types (age grade, adornment, belief/ritual), category, current status, scope, season, usual months (1–12), a **details panel** (ingredients, instruments, occasions…, each with a source) |
| Links between records | Settlement status (indigenous core / shared / migrant …), location type, speaker role, evidence level, sensitivity |
| Statistics | Evidence level |
| Sources (Sources admin) | Type (Step 5 list), tier (filled from the type), copy group, rights notes |
| Research gaps | State/LGA, kind of gap, why it is missing, recommended research, priority |

**New screens** (Nigeria Heritage dashboard → "Cultural layer, claims & field work"):
- Communities
- Claims, with their own sources panel
- Disputes
- Oral histories
- Contributors & informants
- Field records
- Photos, documents & maps (audio/video deferred, as you decided), with links to the records they show
- Research progress per state (also shown as a column in the dashboard's state table)

## Rules the admin now enforces

1. **Only records marked Public can be published.** This applies to records, links and media. Oral histories and field records start as "Permission required".
2. **Media** can be published only when permission is granted (or not required).
3. **Evidence levels:**
   - Verified, Well documented and Reported need an attached source.
   - **Verified needs a Tier 1 source, or two independent sources.** Sources in the same copy group count as one.
   - A **Disputed** claim must name its dispute record.
4. **A resolved dispute** must say how it was resolved.
5. **A state can be marked Complete** only when every LGA is verified (brief: never claim complete before every LGA is reviewed).
6. **Coordinates and vitality** need a cited source.
7. **Claims must point to a record that exists.**
8. **Deleting** a claim or media item also removes its source links and record links.

## A bug fixed along the way (affects the live site today)

After a refused save, the error message and the rejected values stayed in the session. They then pre-filled the **next form opened, even another record's**, so pressing Save there could write the wrong values. The same thing shows on the public login page: after a wrong password, the error and email reappear on later visits.

Cause: `core/Controller.php` closes the session before the page is drawn, so the forms' "clear" was never saved. The fix is 8 lines. Errors and old input are now shown once, then cleared. No other behaviour changes.

## Public side

- **Unchanged:** 20 public pages are byte-identical with the live code and the new code on the same database: the Nigeria home, states, an LGA, the FCT, ethnic groups, languages, Tor Tiv, places, search, historical figures, timeline, references, the dictionary, the sitemap and login. The only exception is the home page's random featured item.
- **One protective change** in `HeritagePublic::linkedRecord`: a linked record is shown only if its sensitivity is Public and it has a public page. Nothing today is affected; it prevents future communities or oral histories from appearing as broken or unapproved links.

## Tests (copy of production after the backfill, MySQL 8.0.46, test admin user in the copy only)

- 41 admin pages load with no errors: every list and "add" screen, zone/state/progress/ethnic group/language edit pages, and the source form.
- 31 form tests pass. They cover:
  - the zone keeping its type on save
  - Verified allowed with two independent sources and refused with one Tier 2 source
  - a published record refused when set to Restricted
  - Complete refused
  - coordinates refused without a source
  - a community created with `COMM-BENUE-GBOKO-…`
  - claims (a missing record refused; Disputed and Reported rules)
  - oral history / field record defaults
  - media permission and links
  - month 13 refused
  - the new "Age grade" type
  - relation settlement status (a sensitive link stays a draft)
  - the source copy group (other fields unchanged)
  - deletes that clean up child rows
- 0 PHP warnings or errors in the server log.

## Files

New:
- `services/StableId.php`

Changed:
- `services/HeritageRegistry.php`
- `controllers/AdminHeritageController.php`
- `views/admin/heritage/form.php`
- `views/admin/heritage/index.php`
- `views/admin/heritage/dashboard.php`
- `controllers/KnowledgeController.php`
- `models/Source.php`
- `views/admin/knowledge/source-form.php`
- `index.php` (2 routes)
- `services/HeritagePublic.php` (the guard above)
- `core/Controller.php` (the bug fix)

**No database change.**

## Deploy (on "deploy")

1. Copy the 12 files with rsync (keeping ownership as in earlier deploys).
2. Clear the cache.
3. Check the admin pages, the public pages, the sitemap (3,018) and the error log.

No backup is needed because nothing in the database changes, but the current live files will be kept in `/root/backups/` so they can be restored.
