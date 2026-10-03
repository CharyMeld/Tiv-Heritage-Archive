# Research batch 029 — Benue: the Tor Tiv Palace and the Tiv and Idoma dialects

Researched 2026-09-26. Phase 3, gaps G-04 (part 2) and G-07. Created in review; published only after your approval.

- **Tor Tiv Palace**, Gboko (144 words), linked to the Tor Tiv record.
- **Tiv dialects** (Glottolog): 2 dialect groups, **Icongo (Ichongo)** and **Ipusu**, with 9 dialects under them: Masev-Hyarev, Nongov, Tulan (Turan), Ugondo | Kparev, Kyurav (Ikyurav), Shitile (Shitire), Tongov, Ukum. These are the names of the Tiv clan-families.
- **Idoma dialects** (Glottolog): Idoma Central, Idoma South, Idoma West, Okpogu.
- 15 dialect records in all, each linked to its language through 'belongs to', so they appear on the Tiv and Idoma language pages under *Dialects & varieties*. 4 usual Tiv spellings are added as other names.
- All the new pages are short, so they stay noindex (sitemap unchanged).

## Tor Tiv Palace

> The Tor Tiv Palace in Gboko is the palace of the Tor Tiv, the paramount ruler of the Tiv people; Wikipedia places it in the heart of the town.
>
> Vanguard reported in May 2021 that the old palace had become, in the Tor Tiv's words, too dilapidated to be renovated, and that Governor Samuel Ortom's administration had rebuilt it in phases over about five years. The work was paid for jointly by the Benue State Government and the fourteen Tiv-speaking local government areas. The new building uses the Tiv colours, black and white, and other Tiv cultural symbols in its design and finishing, and it was furnished with artifacts meant to show Tiv heritage; Vanguard described it as a place that now draws visitors. The present Tor Tiv, James Ayatse, was its first occupant. When the first palace was built is not recorded here.

## Example dialect texts

> Icongo is one of the two dialect groups of the Tiv language in Glottolog, the language catalogue of the Max Planck Institute. It takes its name from Ichongo, one of the two sons of Tiv in Tiv genealogy, and Glottolog places these dialects in it: Masev-Hyarev, Nongov, Tulan and Ugondo. The names are those of the Tiv clan-families that trace their descent from him. Tiv scholars cited by Nomishan (2020) describe the language as homogeneous and understood by all Tiv, without dialects that divide it.

> Kparev is listed by Glottolog as a dialect of Tiv in the Ipusu group. Its name is that of a Tiv clan-family.

## Two views, both stated

Glottolog lists nine Tiv dialects. Nomishan (2020), citing Tiv scholars (Gbor, Makar and others), calls Tiv a homogeneous language 'without dialect', understood by all Tiv. The dialect-group pages state both.

## Sources

- **VG21** — How Tor Tiv palace was turned into tourist destination (Peter Duru, 2021-05-20). https://www.vanguardngr.com/2021/05/how-tor-tiv-palace-was-turned-into-tourist-destination/. Tier 3.
- **WGB** — Gboko (Wikipedia). https://en.wikipedia.org/wiki/Gboko. Tier 3.
- **GLTIV** — Tiv (tivv1240) (Glottolog). https://glottolog.org/resource/languoid/id/tivv1240. Tier 2.
- **GLIDO** — Idoma (idom1241) (Glottolog). https://glottolog.org/resource/languoid/id/idom1241. Tier 2.
- **NOM20** — Perspectives on the Origin, Genealogical Narration, Early Migrations and Settlement Morphology of the Tiv of Central Nigeria (Terngu S. Nomishan, 2020). http://ijeais.org/wp-content/uploads/2020/8/IJAPR200808.pdf. Tier 2.

## Research gaps

- **Tor Tiv Palace: first building.** When the first palace was built is not documented in a reliable source (a social-media post gives 1946–2021).
- **Where each Tiv and Idoma dialect is spoken.** Glottolog lists the dialects but not their areas; mapping them to LGAs needs linguistic or ethnographic sources (links with gap G-10).
- **Ikyurav: Ipusu or Ichongo.** Glottolog places Kyurav (Ikyurav) under Ipusu; a genealogical account seen only in a search summary lists Ikurav among the sons of Ichongo. Needs Tiv genealogy sources.
- **Tiv 'without dialect'.** Tiv scholars (via Nomishan 2020) describe Tiv as dialect-free in practice; Glottolog lists nine dialects. The two views are compatible (clan speech varieties, mutually intelligible) but this is not stated by a source.
- **Och'Idoma palace.** Only I am Benue mentions it (Otukpo). Not recorded as a place yet.
- **Hausa of Katsina-Ala and Ukum (G-08).** No independent source beyond Wikipedia found; still open.
## Tests (copy of the live database, MySQL 8.0.46)

- **Import:** the dry run is clean (4 new sources, 1 reused, 16 records, 4 names, 1 link, 6 gaps).
- **Dialect records:** all 15 get permanent IDs (e.g. `LANG-ICONGO`, `LANG-KPAREV`, `LANG-OKPOGU`) and sit under the right parent:
  - the Tiv language → Icongo and Ipusu
  - Icongo → Masev-Hyarev, Nongov, Tulan, Ugondo
  - Ipusu → Kparev, Kyurav, Shitile, Tongov, Ukum
  - the Idoma language → its four dialects
- **Pages after publishing on the copy:**
  - The Tiv language page shows "Dialects & varieties (2): Icongo, Ipusu".
  - The Icongo page lists its four dialects.
  - Dialects don't appear in the main Languages list.
  - The Tor Tiv Palace page loads, linked to the Tor Tiv and to Gboko.
  - The sitemap is **unchanged at 3,023**, and there are 0 PHP errors.
- **Full undo:** the rollback returns the exact starting state.
- **No code changes.**

## Deploy (on your approval)

1. Back up the database.
2. Import (dry run, then apply).
3. Publish the batch.
4. Clear the cache.
5. Check the pages, the sitemap (3,023) and the error log.
