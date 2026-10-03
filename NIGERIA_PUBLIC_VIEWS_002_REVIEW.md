# Public views of the migration-002 fields — review

Owner's instruction: "next" (25 Sep 2026), after the admin screens were deployed. This is the last step of the Step 4 rollout. It was built and tested locally and is **not deployed**.

## What visitors will see

| Where | Before | After |
|---|---|---|
| Evidence badge on every Nigeria record | "Evidence: Multiple sources" (earlier scale) | "Evidence: Verified" / "Well documented" / "Reported" / … (the six levels), with a plain explanation on hover. Records without a level keep the old badge |
| Figures table | "(census)" | "(census · well documented)": each figure shows its own level |
| Sources list | title, publisher, link | plus **"Official source" / "Academic source" / "Reference source" / "Community source" / "General web source"** |
| Foot of each record | "Last updated …" | "**Archive ID: NG-STATE-BENUE** · Last updated …", plus a short **"How evidence is graded"** note (collapsed) explaining the six levels and source types |
| Search engines | — | The archive ID is added to each page's structured data (`identifier`) |
| **Geopolitical zones** | a plain word on state pages | **Six zone pages** (e.g. `/nigeria/units/north-central`), each listing its states. The zone on every state page and the zone headings on the States page link to them |
| AI answers from the graph | "— multiple sources" | "— verified" / "— well documented" |

Also ready, though there is no data for them yet:
- **Facts:** LGA headquarters, official code and coordinates (only with a source); place protection and condition; group level; controlled vitality (only with a source); a cultural record's category, season, usual months, current status and scope; a **Details** list on cultural records (ingredients, instruments…).
- **Settlement status** (e.g. "indigenous (core homeland)") and **speaker role** next to links.

These show automatically once research fills them in; nothing is shown when a field is empty.

## The zone pages

- The script `database/research/publish_zones_002.php` publishes the six zone records. It is a dry run by default, with `--apply` and `--revert`, and every change is logged.
- Each page has its one-sentence summary, its states, "Part of: Nigeria", its two sources (Wikipedia "States of Nigeria"; EUAA) and its evidence level (Well documented).
- The pages are short, so the existing Google rule serves them **noindex** and keeps them out of the sitemap. The **sitemap stays at 3,018**.
- The script also corrects the North Central summary's grammar to "… Plateau **and the** Federal Capital Territory" (logged), matching what the backfill review showed you.
- The 37 state→zone links stay as drafts in the database. The zone pages list their states from each state's zone field, so the list isn't shown twice.

## Unchanged

- **Google rules:** which pages are indexed still follows the same rule (published, 300+ words of the record's own text, ≥1 source, evidence better than unverified). The new explainer is page text, not record text, so it counts toward nothing. The sitemap is identical before and after.
- **Pages without Nigeria records:** the Nigeria home, search, historical figures, timeline, references and the dictionary are byte-identical.
- **Not yet public:** communities, oral histories, claims and media have no public pages. None are published yet, and oral histories need community permission first. Their public pages can be a later step, when there is content.

## Tests (copy of production, MySQL 8.0.46)

- Live code vs new code on the same database, 17 pages:
  - The 10 Nigeria record pages differ only by the intended changes: the badge, the figures' levels, the source labels, the archive ID, the explainer, and the structured-data identifier.
  - The States listing differs by one word ("evidence status" → "evidence level").
  - The rest are identical.
- After publishing the zones on the copy:
  - The zone page renders its 7 states and is served "noindex, follow".
  - Benue's "Geopolitical zone" links to North Central, and all six headings on the States page link to their zones.
  - Zones don't appear under "Historical administrative units".
  - The sitemap is still 3,018, with no zone URLs.
- **Script:** the dry run changes nothing, the apply makes 7 changes (6 publications and 1 grammar fix), a second run makes 0, and the revert returns the zones to drafts.
- **Cultural record page:** a test record, published on the copy only, shows the new season, months, status and Details list, and its archive ID `CULT-…`.
- **AI answers** use the new wording.
- **Admin screens** still work, and there are 0 PHP warnings or errors.

## Files

Changed:
- `services/HeritagePublic.php`
- `controllers/NigeriaController.php`
- `views/nigeria/record.php`
- `views/nigeria/list.php`
- `services/HeritageGraph.php`
- `services/HeritageRegistry.php` (the list of cultural detail kinds moved here)
- `controllers/AdminHeritageController.php` (uses that list)

New:
- `database/research/publish_zones_002.php`

## Deploy (on "deploy")

1. Back up the database and the current files.
2. Copy the 8 files.
3. Run the zone script: dry run, then `--apply`.
4. Clear the cache.
5. Check the pages, zone pages, sitemap (3,018) and error log.
