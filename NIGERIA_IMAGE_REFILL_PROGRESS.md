# Nigeria Heritage Archive — State-by-State Image Refill

Brief: the owner's "STATE-BY-STATE IMAGE REFILL" prompt (3 Oct 2026). This file is the persistent resume point. **Read the RESUME block first.**

## ▶ RESUME HERE

- **Benue: COMPLETED_WITH_GAPS — DEPLOYED 3 Oct 2026** (img_001_benue.php, research_batches#282, media_assets #4–6; backup `/root/backups/LATEST_NIGERIA_IMG001`, which also holds the pre-edit copies of the 3 code files). Live checks: 3 images + credit lines render, sitemap 3,026, 0 PHP fatals.
- **Open owner question:** the Tiv photo (children in A'nger, `ng-tiv-anger-attire.jpg`, NOT uploaded to the server). If the owner says yes: rsync its 5 files to uploads/images, then `php database/research/img_001_benue.php --apply --include-owner-check`.
- **Current state:** Nasarawa — IN_PROGRESS (image batch `img_002_nasarawa`).

## Owner decisions (3 Oct 2026)

1. **Licence:** publish only public-domain / CC0 / CC-BY / CC-BY-SA images (in practice mostly Wikimedia Commons), with credit shown. Good images with an unknown or all-rights-reserved licence are recorded as **candidates** (source link kept, not downloaded, not shown) so the owner can seek permission. Never invent licence information.
2. **Storage:** existing `media_assets` (source, creator, copyright holder, licence, permission) + `media_links` (one file reused across records, role `depicts`). The record's own `image` column is set so the existing hero image shows. A small credit line is added under the hero image on the public record page.
3. **Scope:** heritage places (sites, museums, monuments, natural features, palaces), festivals/cultural records, ethnic groups (dress/people, respectfully). Towns only when clearly notable (state capitals, historic cities). No LGAs/wards. Polities have no image column, so they are covered through their seat/palace place.
4. **Deploy:** pause after each state. Build and test locally, report the state audit, deploy only on the owner's yes, then continue to the next state.

## Rules carried from the brief

- No new history. If a record looks wrong, flag it in the state audit; don't edit it.
- Every image is verified: subject, location, ethnic attribution, person identity, source support, duplicate/already-in-archive.
- No AI-generated, unrelated, generic, stock, other-group or other-location images. If nothing suitable is found → **IMAGE NOT FOUND** with the reason, stored as a `research_gaps` row (topic `Image not found: <record>`, status `open`).
- Source priority: Nigerian govt/state institutions, NCMM, universities, museums, heritage bodies, reputable news, Wikimedia Commons, other credible sources. (Under decision 1, non-free sources become candidates only.)
- Reuse before download: the same file is linked to several records through `media_links`.
- Image status per state is tracked here, not in the database (no schema change): NOT_STARTED / IN_PROGRESS / IMAGE_REFILLING / REVIEW_REQUIRED / COMPLETED / COMPLETED_WITH_GAPS.

## Starting inventory (production, 3 Oct 2026)

- 0 images on any Nigeria record (places 0/569, ethnic_groups 0/148, cultural_records 0/78); `media_assets` and `media_links` empty.
- Image-worthy published records per researched state (heritage places / towns / festivals+culture / peoples linked):

| State | Heritage places | Towns | Culture | Peoples | Image status |
|---|---|---|---|---|---|
| Benue | 4 | 22 | 3 | 11 | COMPLETED_WITH_GAPS (deployed 3 Oct) |
| Nasarawa | 2 | 12 | 2 | 26 | IN_PROGRESS |
| Taraba | 6 | 16 | 3 | 16 | NOT_STARTED |
| Plateau | 8 | 17 | 12 | 33 | NOT_STARTED |
| Kogi | 7 | 21 | 2 | 12 | NOT_STARTED |
| Adamawa | 16 | 21 | 43 | 34 | NOT_STARTED |
| Borno | 7 | 26 | 1 | 25 | NOT_STARTED |
| Yobe | 6 | 17 | 0 | 12 | NOT_STARTED |
| Bauchi | 14 | 20 | 0 | 20 | NOT_STARTED |
| Gombe | 6 | 11 | 0 | 18 | NOT_STARTED |
| Kano | 10 | 45 | 0 | 4 | NOT_STARTED |
| Jigawa | 7 | 27 | 0 | 11 | NOT_STARTED |
| Katsina | 7 | 34 | 0 | 6 | NOT_STARTED |
| Zamfara | 3 | 14 | 0 | 2 | NOT_STARTED |
| Lagos | 17 | 17 | 2 | 6 | NOT_STARTED |
| Ogun | 5 | 21 | 2 | 2 | NOT_STARTED |
| Oyo | 10 | 34 | 0 | 1 | NOT_STARTED |
| Osun | 10 | 30 | 1 | 1 | NOT_STARTED |

Queue order = the order the states were researched (Benue first). The 19 not-started states have only their capital and are skipped until their research is done.

## State audits

(Each state's audit is appended here when its batch is ready for the owner.)

## Tools

- `scratchpad/img/cs.php "<terms>"` — Wikimedia Commons file search printing licence, author, date, description and categories (UA `TivHeritageResearch/1.0`, ≥5 s between calls). Rebuild if the scratchpad was wiped (`generator=search&gsrnamespace=6&prop=imageinfo|categories&iiprop=url|size|extmetadata`).
- Always read each candidate's file-page wikitext: `source={{own}}` + matching author = clean; "own work" but a different named photographer, or tiny re-saved social-media copies = licence doubtful → manual review only.
- Always look at a preview (`Special:FilePath/<file>?width=640`) before accepting.
- Picks go in `database/research/data/img_NNN_<state>_picks.json`; `scratchpad/img/fetch.php picks.json img_NNN_<state>.json uploads/images` adds Commons metadata and downloads a ~1600–1920 px copy; then `ImageVariantGenerator::generate()` for -400w/-800w.
- Test: docker `m002` (root/t, port 33069) loaded from a fresh `mysqldump tiv_archive`; scratch site with config/database.php → `127.0.0.1;port=33069`; router.php must set `$_GET['url']`; curl pages with a browser User-Agent (the site 403s `curl/`). Dry run → apply → apply again (0 changes) → check pages → `--revert --apply` (all counts back to 0). Stop the test server with `kill <pid>`, not `pkill -f` (it kills its own shell).

### BENUE IMAGE AUDIT — img_001_benue (DEPLOYED 3 Oct 2026)

| | |
|---|---|
| Records examined | 40 (4 heritage places, 22 towns/settlement, 3 cultural records, 11 peoples) |
| Already illustrated | 0 |
| In scope / needing an image | 16 (4 heritage places, Makurdi, 3 cultural records, 8 peoples) |
| Out of scope | 21 LGA headquarters towns (scope rule: notable towns only); Jukun, Hausa, Igbo → done with their home states |
| Free images found and verified | 4 — Makurdi Railway Bridge, Makurdi, Idoma, Tiv (Tiv = owner check) |
| IMAGE NOT FOUND (research_gaps) | 12 — National Museum Makurdi, Tor Tiv Palace, Igede iron-smelting furnaces, Igede Agba, Alekwu, Opleka, Igede, Etulo, Akweya, Nyifon, Ufia, Abakpa |
| Non-free candidates recorded (permission needed) | 3 — Tor Tiv Palace (Gazette Nigeria), Igede Agba (NICO), Alekwu (Splendors of Dawn) |
| Manual review | 4 — Tiv photo shows children (owner check); 2 "River Benue (in Makurdi…)" photos by Ashinze say "taken by Bandele Femi" (authorship ≠ uploader, not used); "Tiv elders.jpg" by Wilses looks like a re-saved copy (not used); Idoma "Eku" masquerade photo not identified as Alekwu (not used for Alekwu) |
| Duplicate downloads avoided | 1 ("Eku (masquerade).jpg" = same photo as "Idoma masquerade.jpg") |
| Images reused across records | 0 |
| Licensing issues | 2 (Ashinze authorship conflict, Wilses likely copied) |
| Wrong-subject results rejected | Igede-Ekiti (Ekiti State) for Igede; Ekoi items for Akweya; new concrete bridge ("River Benue 2.jpg") for the 1932 bridge |
| Record facts flagged | none |
| Status | COMPLETED_WITH_GAPS (Tiv photo pending owner yes/no) |
