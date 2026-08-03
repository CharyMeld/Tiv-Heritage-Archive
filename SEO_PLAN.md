# Tiv Heritage Archive — SEO Overhaul Plan

## Context

The site just moved from `tiv.teamodigitalsolutions.com` (subdomain) to `tivheritage.com` / `www.tivheritage.com` (DNS, nginx vhost, SSL, and a 301 redirect from the old subdomain are all live on the VPS at `72.62.119.237`). AdSense re-verification is in progress (meta tag + `ads.txt` deployed).

The next goal: rank highly for anything related to Tiv culture, language, and history — "wherever something related to this site is mentioned, the site should pop up." An audit of the codebase found the app has decent bones (dynamic sitemap, canonical tags, OG/Twitter tags, mostly-good alt text, `loading="lazy"` in places) but real gaps: most detail pages have no meta description, no page uses its own photo for social sharing, there is zero structured data (JSON-LD), only one content type has breadcrumbs, the sitemap is missing roughly half the site's content-bearing routes, detail-page URLs are bare numeric IDs with no keyword slug, and 27MB of uploaded images are unoptimized (no WebP, no responsive sizes).

User decided: do both the on-page/technical tier AND the deeper infrastructure tier (image pipeline + URL slugs) in this push. GSC verification: user will create the `tivheritage.com` property in Search Console themselves and paste the HTML-tag verification code back for me to add.

## Key facts discovered during investigation (change the risk profile — read before implementing)

- `core/Router.php:19` already turns `{id}` into `(?P<id>[a-zA-Z0-9_-]+)` — hyphens are already legal. `/word/482-vihi` matches the existing route with **zero Router.php changes**.
- Every `DetailController` action does `$model->find((int) $id)`. PHP's `(int)` cast on `"482-vihi"` yields `482`. So slugged URLs already resolve correctly today — the only new work is generating the canonical slug and 301-redirecting non-canonical requests to it.
- `historical_figures.slug` and `timeline_events.slug` columns **already exist** (added "for future SEO routing"), unique, currently unused by any route. `AdminHistoricalFigureController::store()` already populates `historical_figures.slug`. `timeline_events.slug` is not populated anywhere yet (no admin CRUD exists for timeline events).
- `views/detail/plant.php` already renders `scientific_name`, `medicinal_uses`, `food_uses`, `ritual_uses`, `cultivation` — no fix needed there.
- **`deploy.sh` excludes `uploads/*`** from rsync. Any image-optimization script must run directly on the VPS against the live `uploads/images/`, never locally + pushed.
- VPS PHP (8.2.31) has **GD but not Imagick** — use GD (`imagewebp()` etc.) for the image pipeline.
- No Composer/`vendor/`, no `package.json`/`node_modules` anywhere — this is a zero-build-tool PHP app. Minification tooling must respect that.
- Three near-duplicate image-upload blocks exist: `AdminController::saveImageFile()` (~line 866), `AdminHistoricalFigureController::saveImageFile()` (~line 257), `AdminContentItemController::handleUpload()` (~line 316) — natural seam for a shared upload service.
- `.htaccess` is Apache-only and irrelevant to production (nginx). All redirect/slug logic must live in PHP, not `.htaccess`.
- `main.php` already has an `isset($extraCss)`/`$extraJs` conditional-echo convention (lines 37-39, 72-74) — reuse this pattern for a new `$jsonLd` hook.
- `ApiAuth::protectPage()` gates by path **prefix** (`word/`, `historical-figure/`, etc.) — a slug suffix after the id does not interfere.
- Confirmed real column names (corrects earlier guesses):
  - `tiv_names`: `tiv_name`, `english_meaning`, `description`, `origin_story`, `usage_context`
  - `tiv_proverbs`: `tiv_text`, `english_translation`, `deeper_meaning`, `usage_context`, `category`
  - `daily_words`: `tiv_word`, `english_meaning`, `example_tiv`, `example_english`, plus enrichment fields (`ipa`, `tone`, `root_word_id`, `literal_meaning`, `figurative_meaning`, `usage_notes`, `alternate_meaning`)
  - `tiv_plants`/`tiv_festivals`/`tiv_foods`: have an `image` column; `tiv_animals` got one added later via migration
  - `content_items`: `section`, `subcategory`, `status` (`'published'`) — confirms the two-key section/subcategory shape used by `ContentCategoryController`

## Phase 0 — Shared infrastructure (build first)

1. **`services/SeoHelper.php`** (new, static utility, follows the `services/` convention):
   - `truncate(string $text, int $len = 155): string`
   - `describe(array $candidates, string $fallback): string` — first non-empty candidate, truncated
   - `ogImage(?string $filename, string $subdir = 'images'): string` — filename → full URL, else favicon fallback
   - `jsonLd(array $graphs): string` — wraps in `@graph` if >1, `json_encode` with safe flags, returns ready `<script type="application/ld+json">` string
   - `breadcrumbListSchema(array $crumbs): array` — converts the existing `['label'=>,'url'=>]` shape to schema.org `BreadcrumbList`
   - `slugify(string $text): string` — canonical version, lifted from `HistoricalFigure::slugify()` (`models/HistoricalFigure.php:162-167`); that method becomes a thin delegating wrapper (keep for compatibility)
   - `canonicalSlugPath(string $prefix, int $id, string $name): string` → `"{prefix}/{id}-{slug}"`
   - `variantUrl(string $original, int $width, string $ext): string` — deterministic responsive-image filename builder (for Phase 6)

2. **`views/layouts/main.php`**:
   - After the `google-adsense-account` meta tag: add `<meta name="google-site-verification" content="<?= GSC_VERIFICATION_CODE ?>">`, backed by a new `GSC_VERIFICATION_CODE` constant in `config/config.php` (empty string default; user supplies the real value once GSC issues it — same static-unconditional pattern as the AdSense tag).
   - Near the existing `$extraCss`/`$extraJs` block, add a `$jsonLd` hook right before `</head>`:
     ```php
     <?php if (!empty($jsonLd)): ?>
         <?= $jsonLd ?>
     <?php endif; ?>
     ```
   - Homepage-only `WebSite` + `Organization` + `SearchAction` JSON-LD emitted from `HomeController::index()` via the same `jsonLd` key (not hardcoded in the layout, so it only fires once).

3. **Breadcrumb generalization**: rename/generalize `views/partials/historical-figures-breadcrumb.php` → `views/partials/breadcrumb.php` (3 call sites to update: `HistoricalFigureController`, `TimelineController`, `ReferencesController` views). CSS selectors currently prefixed `.hf-breadcrumb-*` in `assets/css/styles.css` — fine to keep or rename, low-risk either way.

4. **`config/security.php`**: add `redirect301(string $url): void` next to the existing `redirect()` (~line 361). No `301` usage exists anywhere yet — genuinely new.

## Phase 1 — Meta descriptions + og:image (do first, fastest win)

In `controllers/DetailController.php`, add `description` (and `ogImage` where a photo field exists) to each action's `render()` call, using `SeoHelper::describe()`:

- `name()`: description from `[$item['description'], $item['origin_story']]`, fallback mentioning `$item['tiv_name']`/`english_meaning`. No image field — leave `ogImage` unset (favicon fallback is correct).
- `proverb()`: fix hardcoded `'title' => 'Tiv Proverb'` → `$item['tiv_text'] . ' - Tiv Proverb'`. Description from `[$item['deeper_meaning'], $item['english_translation']]`.
- `plant()`, `festival()`, `food()`, `animal()`: description from `$item['description']` (or type-specific field), `'ogImage' => SeoHelper::ogImage($item['image'] ?? null)`.
- `word()`: description from `[$item['figurative_meaning'] ?? null, $item['literal_meaning'] ?? null, $item['english_meaning']]`.

`HistoricalFigureController::show()`: description from `[$item['short_summary'], $item['biography']]`, `ogImage` from `$item['image']`.

`ArchiveController.php`'s 7 `browse*()` category-listing methods (currently title-only): add one static per-category description string each (e.g. "Browse the full collection of Tiv proverbs — sayings, meanings, and cultural context.").

`HomeController::about()`/`contact()`: add static descriptions.

**Verification**: `curl -s https://www.tivheritage.com/word/1 | grep -A1 'meta name="description"'` for a sample of each type; spot-check with Search Console's URL Inspection tool post-deploy.

## Phase 2 — JSON-LD structured data

Add one `'jsonLd' => SeoHelper::jsonLd([...])` array to the same `render()` calls touched in Phase 1 — no new abstraction beyond `SeoHelper::jsonLd()`, schema content stays inline per type:

| Content type | Schema.org type |
|---|---|
| word | `DefinedTerm` |
| historical-figure | `Person` |
| festival, timeline-event | `Event` |
| proverb, plant, food, name | `CreativeWork` |
| any page with breadcrumbs | `BreadcrumbList` (merged into the same graph) |
| homepage | `WebSite` + `Organization` + `SearchAction` |

**Verification**: Google Rich Results Test against each URL pattern post-deploy.

## Phase 3 — Sitewide breadcrumbs

Roll the generalized `breadcrumb.php` partial (Phase 0) into the 7 `DetailController` views that currently only have a "← Back to category" link. Build `$breadcrumb` arrays in each `DetailController` action (mirror the shape `HistoricalFigureController::show()` already uses), pass to `render()`, render via the partial alongside the existing back-link (supplement, don't replace). Feed the same array into `SeoHelper::breadcrumbListSchema()` for Phase 2's JSON-LD.

**Verification**: visual diff of each detail template; Rich Results Test flags `BreadcrumbList` errors.

## Phase 4 — Sitemap completeness

`controllers/SitemapController.php` stays consistent with its existing flat `$db->query()` + `foreach` + `$this->url()` style (no model instantiation). Add, after the current animals loop:

- Historical figures: index + per-category + per-figure (`historical_figures WHERE status='published'`)
- Timeline: index + per-event (`timeline_events WHERE status='published'`)
- References: index + per-source (`sources`)
- Content-category tree: section/subcategory index pages + per-item (`content_items WHERE status='published'`, grouped by `section`/`subcategory`)
- Missing statics: `archive/documents`, `archive/audio`, `archive/publications`, `community/join`, `suggestions`, `nominate-influential`

**Sequencing note**: once Phase 5 ships, every `$this->url()` call in this file must emit the canonical `{id}-{slug}` form directly (not bare `{id}`) to avoid the sitemap pointing at URLs that immediately 301. Do a final sync pass after Phase 5.

**Verification**: `curl -s https://www.tivheritage.com/sitemap.xml | xmllint --noout -`; submit via GSC, watch Discovered/Indexed counts.

## Phase 5 — URL slug redesign

**Approach**: derived-at-request slugs for the 7 tables without a slug column (no migration, no backfill — slug is always in sync with current data since it's computed from the name field on every request). Use the existing persisted `slug` column for `historical_figures` (already populated) and `timeline_events` (needs a one-time backfill script, since no admin CRUD exists for it yet).

- No `Router.php` change needed (confirmed above).
- Each `DetailController` action gets a canonical-slug check right after `find()`: compute expected `"{id}-{slug}"`, if the request's `$id` param doesn't match, `redirect301(url("{prefix}/{expected}"))`. Since all 7 actions live in one class, add one small private helper `DetailController::enforceCanonicalSlug(...)` rather than repeating the block 7 times. `HistoricalFigureController`/`TimelineController` get their own inline checks (2 call sites, not worth a shared trait), comparing against the stored `slug` column.
- **Every link-building call site** that currently does `url('word/' . $item['id'])` (and the plant/proverb/name/festival/food/animal/historical-figure/timeline-event equivalents, ~20-40 occurrences across `views/archive/*.php`, `views/detail/*.php`, related-item sections) needs to switch to `url(SeoHelper::canonicalSlugPath(...))`. Enumerate exact call sites via `grep -rn "url('word/\|url('plant/\|url('proverb/\|url('name/\|url('festival/\|url('food/\|url('animal/\|url('historical-figure/\|url('timeline-event/" views/` before editing.
- `SitemapController.php`: emit canonical slugged URLs directly (see Phase 4 sequencing note).
- `ApiAuth::protectPage()`: confirmed no change needed (prefix-based matching, unaffected by slug suffix).

**Rollback**: purely additive + redirect-based. Old bare-numeric URLs keep working (301 to canonical, never 404). No DB state to unwind for 7 of 9 content types if reverted.

**Verification**: `curl -sI https://www.tivheritage.com/word/482` → `301` + correct `Location`; `curl -sI .../word/482-vihi` → `200`; `curl -sI .../word/482-wrong-slug` → `301` to the correct slug; confirm the canonical URL itself never redirects to itself (no loop).

## Phase 6 — Image optimization pipeline (highest operational risk — do after URL scheme is stable)

**(a) Batch conversion of the 134 existing images**: new `bin/optimize-images.php` (pure PHP, GD-based — confirmed available on the VPS, no Imagick). For each original in `uploads/images/`, generate a WebP encode plus 2 responsive raster sizes (e.g. 400w/800w) in both original format and WebP, flat into the same directory using deterministic filenames (`{basename}-400w.webp` etc.). Idempotent (skip if target is newer than source). **Must run directly on the VPS via SSH** (`deploy.sh` excludes `uploads/*` — this never reaches production via the normal deploy flow). Script only ever adds files, never touches originals — safe to abort/re-run.

**(b) Upload-time hook for new images**: extract the generation logic into `services/ImageVariantGenerator.php`, used by both `bin/optimize-images.php` and a new `services/ImageUploadService.php` that consolidates the 3 duplicated upload-handling blocks (`AdminController::saveImageFile()`, `AdminHistoricalFigureController::saveImageFile()`, `AdminContentItemController::handleUpload()`). Keep existing method signatures/return shapes so callers don't need further changes. Update the 3 call sites to use the shared service.

**(c) View-layer `<picture>`/`srcset` rollout**: affects hero images and thumbnails in `views/detail/plant.php`, `festival.php`, `food.php`, `animal.php`, `historical-figure.php`, `timeline-event`, plus `views/archive/*.php` listings. Use `SeoHelper::variantUrl()` to build deterministic variant paths — sequence (a) and (b) before (c) so variants are guaranteed to exist by the time this view code ships (avoid per-render `file_exists()` checks). Swap `loading="lazy"` for `fetchpriority="high"` on above-the-fold hero images (LCP candidates).

**Pre-flight**: already confirmed — GD present on VPS PHP 8.2.31, no Imagick.

**Verification**: Lighthouse/PageSpeed Insights before/after on 2-3 representative pages; confirm `Content-Type: image/webp` in Network tab; crawl all detail pages post-deploy to confirm no broken images.

## Phase 7 — CSS/JS minification

No Node/Composer tooling in the repo. Recommended: **Option A** — one-time local dev step (Node only needed on the dev machine, never on the server) using `npx clean-css-cli`/`terser` to produce `assets/css/styles.min.css` / `assets/js/script.min.js`, committed to the repo like any other asset (ships via the normal `deploy.sh` rsync, since `assets/` isn't excluded). Re-run manually when source files change. `views/layouts/main.php` switches its `asset()` calls to the `.min.` filenames — `asset()` itself (`config/security.php:372-377`) needs no change, cache-busting `?v=filemtime()` keeps working automatically. Keep unminified source as the editable original.

Fallback **Option B** (only if Node is categorically unwanted even at dev-time): a hand-rolled regex-based PHP minifier — flagged as lossy/riskier, needs manual visual QA.

**Verification**: confirm minified files are served; Lighthouse "Minify CSS/JS" audit clears; manual QA pass across representative pages.

## Phase 8 — Small content-completeness fixes

- `views/detail/word.php`: add an "Also translated as…" line near the existing meaning display, conditional on `!empty($item['alternate_meaning'])` — field already on the row, purely additive, no controller change.
- `views/detail/plant.php`: confirmed already complete, no fix needed.

## Suggested execution order

1. Phase 0 (shared helpers/hooks) — prerequisite for everything else
2. Phase 1 (meta descriptions + og:image) — fastest win
3. Phase 8 (alternate_meaning) — trivial, bundle with Phase 1
4. Phase 2 (JSON-LD) — depends on Phase 0's `jsonLd` hook
5. Phase 3 (breadcrumbs) — depends on Phase 0's partial; feeds Phase 2's `BreadcrumbList`
6. Phase 4 (sitemap) — mostly independent; hold final URL-format edits until Phase 5 lands
7. Phase 5 (URL slugs) — highest architectural risk; verify redirects thoroughly, then finalize Phase 4
8. Phase 6 (image pipeline) — highest operational risk (VPS-side execution); do after the URL scheme is stable
9. Phase 7 (minification) — safe anytime, listed last as lowest SEO urgency

## Manual steps (not code — flagged for the user)

- Create the `tivheritage.com` property in Google Search Console, choose "HTML tag" verification, paste the code value back for Phase 0 step 2.
- Submit `sitemap.xml` in GSC once Phase 4 ships.
- Bing Webmaster Tools: use "Import from Google Search Console" after GSC is verified (no separate code needed unless that import path fails).
- Off-page SEO (backlinks, Wikipedia/Wikidata citations, social signals, directory listings) is outside what code changes can achieve — a separate outreach effort, not part of this plan.
