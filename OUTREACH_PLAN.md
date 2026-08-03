# Tiv Heritage Archive — Backlink & Content Outreach Plan

## Context

With the SEO technical foundation shipped (see `SEO_PLAN.md`) and the domain migration to `tivheritage.com` registered with Google, the next lever for ranking highly on anything Tiv-related is off-page authority: backlinks, citations, and awareness among people/institutions already trusted by Google for this topic. The app already has an Outreach module (`AdminInfluentialController`, nomination form, email campaign system with merge-var templates) built in June 2026 for exactly this purpose — this plan sequences how to use it plus other channels.

## Tier 1 — Highest authority, one-time high-impact targets

- **Wikipedia / Wikidata**: get `tivheritage.com` cited as an external resource on the Tiv language, Tiv people, and Benue State articles, and listed on the Tiv language Wikidata entry. Highest-leverage link available — these articles rank #1 for nearly every "Tiv X" query. **Not an email target** — the Tiv language article has no External Links section, and since Charles runs the site, direct editing is a conflict of interest under Wikipedia policy. Correct process is a Talk-page request (draft below).
- **Omniglot.com**: the web's most popular multilingual reference site. Already has three Tiv pages (writing system, phrases, numbers) — the writing-system page already has a "Links" section citing Wikipedia/Ethnologue/Glottolog. The ask is to add one more entry there, not create a new page. Contact via email (address is image-obscured on their contact page to block scrapers — grab it manually from omniglot.com/contact.htm).
- **Ethnologue** and **Glottolog**: the two standard language-catalog authorities. Both accept external resource submissions per language — via their respective "Updates and Corrections" / contact processes rather than a personal email address.
- **Endangered Languages Project** (endangeredlanguages.com): submit the archive as a documentation resource if Tiv qualifies for listing.

## Tier 2 — Outreach module targets (people-driven, ongoing)

- Academics: linguistics/African-studies departments (Benue State University, University of Jos, University of Ibadan, SOAS London, Indiana University, Wisconsin-Madison).
- Diaspora and cultural leaders: musicians, clergy, politicians, diaspora association heads.
- Use the discovery assistant (`admin/outreach/discovery`) to find contacts, the public nomination form to crowdsource names, and campaign templates personalized per person's field (linguist → dictionary/grammar angle; pastor → Bible translation angle; politician → cultural preservation angle).

## Tier 3 — Link-bait content

- Downloadable/printable Tiv Proverbs or Tiv Alphabet PDF for teachers/researchers to share.
- Long-form definitive pages on topics Wikipedia only covers thinly: history of Tiv migration, Tiv marriage customs, "who are the Tiv people."
- Press-style story pitch to Nigerian outlets (Premium Times, Daily Trust, BenueLink) — cultural-preservation-meets-technology angle.

## Tier 4 — Directories & local institutions

- Benue State Ministry of Arts & Culture, NICO, NINLAN.
- Local church/school partnerships (Bible translation + learning videos features).

## Ongoing

- Social media (`SOCIAL MEDIA POSTS.md`) and YouTube uploads — indirect ranking support via branded search volume.

## Drafted outreach content — Tier 1 (ready to send, drafted 2026-07-30)

All contact email in these drafts uses `contact@tivheritage.com`.

### 1. Wikipedia — Talk-page request, not email

Post on **[Talk:Tiv language](https://en.wikipedia.org/wiki/Talk:Tiv_language)** (new section), then repeat on **Talk:Tiv people** and **Talk:Benue State**:

> **Suggest adding External link — Tiv Heritage Archive**
>
> Disclosure: I'm affiliated with this resource (conflict of interest), so I'm proposing here rather than editing directly, per WP:COI.
>
> I'd like to suggest adding an External Links section (or an entry in the existing one) linking to the Tiv Heritage Archive (https://www.tivheritage.com/) — a free, public digital archive of Tiv dictionary entries, proverbs, names, historical figures, and cultural documentation. Happy to answer any questions about the resource. If an editor agrees it's a good fit, please feel free to add it, or let me know if you'd like more detail first.
>
> — Charles Ikyese, contact@tivheritage.com

Optionally tag `{{edit COI}}` at the top of the post to flag it for uninvolved editors — see [WP:EDITREQ](https://en.wikipedia.org/wiki/Wikipedia:Edit_requests).

**How-to (Charles hasn't used Wikipedia editing before):**
1. Create a free account at en.wikipedia.org ("Create account," top-right) — not required but more credible than an anonymous IP edit.
2. Go directly to the Talk page URL (e.g. `https://en.wikipedia.org/wiki/Talk:Tiv_language`).
3. Click "Add topic" / "New section."
4. Subject: `Suggest adding External link — Tiv Heritage Archive`. Body: the drafted paragraph above.
5. Sign with `~~~~` (four tildes) at the end instead of typing a name manually — Wikipedia auto-converts it to username + timestamp.
6. Click Publish/Save. Repeat on Talk:Tiv_people and Talk:Benue_State.

### 2. Omniglot — email to Simon Ager

**To:** feedback@omniglot.com

**Subject:** Resource suggestion for your Tiv language page

> Hi Simon,
>
> I run the Tiv Heritage Archive (https://www.tivheritage.com/) — a free digital archive documenting the Tiv language and culture: a dictionary with IPA/tone/usage examples, proverbs, traditional names, historical figures, and more.
>
> I noticed your Tiv writing page (omniglot.com/writing/tiv.htm) already links to Wikipedia, Ethnologue, and Glottolog in the Links section — would you consider adding the archive there too? It's free, ad-light, and actively maintained.
>
> Happy to answer any questions. Thanks for all the work you put into Omniglot — it's a fantastic resource.
>
> Best,
> Charles Ikyese
> contact@tivheritage.com

### 3. Ethnologue — via "Updates and Corrections" process

Submit via their site process (fallback: `customer_service@ethnologue.com`, or the chat widget on ethnologue.com):

> I'd like to suggest an additional external resource for the Tiv [tiv] language entry: the Tiv Heritage Archive (https://www.tivheritage.com/), a free online dictionary and cultural documentation resource for the Tiv language of Benue State, Nigeria — includes word entries with IPA/tone, example usage, proverbs, and historical documentation. Happy to provide any further detail needed for verification.
>
> Contact: Charles Ikyese, contact@tivheritage.com

### 4. Glottolog — email

**To:** glottolog@eva.mpg.de

> I'd like to suggest the Tiv Heritage Archive (https://www.tivheritage.com/) as an additional resource for the Tiv language entry — a free online dictionary and documentation project for Tiv (Benue State, Nigeria), including lexical entries with phonetic/tonal information, proverbs, and cultural-historical documentation.
>
> Contact: Charles Ikyese, contact@tivheritage.com

## Measuring progress

Search Console's **Links** report tracks new referring domains as they land.

## Status

- [x] Plan drafted — 2026-07-30
- [x] Tier 1 drafts finalized — 2026-07-30. Ready-to-copy files in `outreach_drafts/`: Wikipedia talk-page post (`01-`), Omniglot email (`02-`), Ethnologue submission (`03-`), Glottolog email (`04-`), and a newly-drafted Endangered Languages Project submission (`05-`, flagged as needing a scope check — Tiv may not qualify as "endangered" by their criteria). None have been sent/posted yet — that step requires Charles's own Wikipedia account and email, which the assistant has no access to.
- [x] Wikipedia talk-page requests posted — 2026-07-30, on Talk:Tiv_language, Talk:Tiv_people, and Talk:Benue_State (see `outreach_drafts/01-wikipedia-talk-page-post.md`). Citation not yet added to any article — pending an editor acting on the request. Wikidata entry still untouched.
- [x] Omniglot outreach sent — 2026-07-30 (see `outreach_drafts/02-omniglot-email.md`)
- [x] Ethnologue outreach sent — 2026-07-30 (see `outreach_drafts/03-ethnologue-submission.md`)
- [x] Glottolog outreach sent — 2026-07-30 (see `outreach_drafts/04-glottolog-email.md`)
- [x] Endangered Languages Project submission — marked not-applicable, 2026-07-30 (see `outreach_drafts/05-endangered-languages-project-submission.md`). Tiv (5.2M+ speakers) isn't in scope for their endangered-languages criteria.
- [x] Outreach module populated with first batch — 7 real, sourced targets added 2026-07-30 via `database/seeds/seed_outreach_targets.php` (D.T. Karshima, BSU Linguistics Dept., IJOTIL journal, Mzough U Tiv's President General, MUTUK, NKST Abuja, Rapizo). Most have no confirmed public email yet — manual follow-up needed via `admin/outreach/people` before any campaign send.
- [x] Outreach module — second batch seeded and live — 2026-07-30, via `database/seeds/seed_outreach_targets_2026_07_30_b.php` (all 7 inserted, 0 skipped). Tier 2: Prof. Oye Taiwo (UI linguistics, published on Tiv ergativity), University of Jos Linguistics & Nigerian Languages Dept., Mutual Union of the Tiv in America (MUTA), HRM Prof. James Ayatse (Tor Tiv V, paramount ruler). Tier 4: Benue State Bureau for Arts, Culture & Tourism (confirmed email/phone), NINLAN (confirmed email/phone), NICO (confirmed email). SOAS and UW-Madison were searched but skipped — no specific Tiv-connected person/program found there worth seeding as a "sourced" target. Combined with the first batch, 14 real targets now live in `admin/outreach/people` — most still need manual email confirmation before any campaign send.
- [x] **First real outreach send completed — 2026-07-30.** Email delivery infra stood up from scratch this session: VPS had no MTA at all, so installed `msmtp` relaying through Brevo (Charles's account, domain-authenticated for tivheritage.com), wired into PHP-FPM 8.2's `sendmail_path`. Sent 4 persona-matched campaigns (academic/clergy/diaspora/institutional templates) to the 7 of 14 targets with a confirmed email — deliberately excluding Iyorwuese Hagher (politician) since his only address (`permission@hagher.com`) looks like a narrow rights-permissions inbox, not general contact. Every send BCCs `contact@tivheritage.com` (added to `OutreachMailer::send()` permanently). Two real bugs found and fixed live during the send: (1) unquoted RFC 5322 display names broke recipient parsing for any name containing a comma (BACT's send failed, retried after fix); (2) `SITE_URL`/`ENVIRONMENT` in `config/config.php` fell back to `localhost` under CLI (no `HTTP_HOST`), so all 7 initial sends went out with a broken `http://localhost/Tiv-Heritage-Archive` link — fixed in config.php (CLI now resolves via `BASE_PATH`), then a short correction email sent to all 7 recipients with the real link. All 14 recipients (7 original + 7 correction) confirmed `sent` via Brevo (`250 OK`).
- [x] Proverbs PDF built and live — 2026-07-30. All 232 proverbs, generated via `generate_proverbs_pdf.py` (fpdf2 + DejaVu Sans for full Tiv-orthography Unicode support), hosted at `https://www.tivheritage.com/uploads/downloads/tiv-proverbs.pdf`, linked from the archive/proverbs page. Note: some `deeper_meaning` DB entries contain Tiv-language text instead of an English explanation (data-quality issue in the source content, not the PDF generation) — worth a cleanup pass in the admin panel at some point.
- [x] Alphabet PDF built and live — 2026-07-30, same pattern as the proverbs PDF (`generate_alphabet_pdf.py`, data via `alphabet_export.json`). Covers all 5 vowels, 19 consonants, 13 digraphs, and the 4-way tone system (67 DB rows total). Live at `https://www.tivheritage.com/uploads/downloads/tiv-alphabet.pdf`, linked from the alphabet page banner. Deployed by scp'ing just the PDF + the one changed view file directly, **not** via `deploy.sh` — the local working tree has ~150 unrelated modified files from other in-progress work that a full deploy would have pushed unintentionally.
- [x] Long-form pages drafted and loaded into the CMS as drafts — 2026-07-30. Source drafts in `content_drafts/`; published into `content_items` (as **status='draft'**, not public) via `database/seeds/seed_longform_content_2026_07_30.php`: "The History of Tiv Migration" (history/migration, id 10), "Tiv Marriage Customs" (culture/marriage-customs, id 11), "Who Are the Tiv People?" (history/origins, id 12). Each still carries its `[VERIFY]` flags as in-content review notes (Tor Tiv's 1948 colonial-era founding, bride-price abolition timing, population figures). **Next step: review each in `admin/content-items/{section}/{sub}`, fix anything wrong, remove the review-notes block, then flip status to "published."**
- [x] Press pitch drafted — 2026-07-30, see `outreach_drafts/06-press-pitch.md`. Daily Trust general contact confirmed (`contact@dailytrust.com`); Premium Times contact page found but email is Cloudflare-obscured (needs a real browser to read); **"BenueLink" could not be confirmed as a real outlet** — only found "Benue Links Nigeria Limited," a transport company — substituted two verified alternatives (Benue Press, Benue Today) pending Charles's pick. Not yet sent.
