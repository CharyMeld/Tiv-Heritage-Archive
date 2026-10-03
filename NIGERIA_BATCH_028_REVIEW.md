# Research batch 028 — Benue heritage sites (1)

Researched 2026-09-26. Phase 3, gap G-04. Created in review; published only after your approval.

**Finding:** the NCMM's list of declared national monuments contains **no site in Benue State**. Two Benue sites are on its list of **proposed** national monuments, and the NCMM runs a national museum in Makurdi.

- National Museum, Makurdi (64 words) · Makurdi Railway Bridge (147) · Igede iron smelting furnaces (103).
- All three are short, so they stay noindex (sitemap unchanged). They are the first heritage-site records for Benue, built on the official NCMM lists.

## National Museum, Makurdi

> The National Museum, Makurdi is one of the national museums run by the National Commission for Museums and Monuments (NCMM), the federal agency responsible for Nigeria's museums and monuments. The NCMM gives its address as GP 4, Ahmadu Bello, opposite the Deputy Governor's Office, Makurdi. When the museum was founded and what its galleries hold are not yet documented here from a readable source.

## Makurdi Railway Bridge

> The Makurdi Railway Bridge, often called the Old Bridge, carries both the railway and a road across the River Benue at Makurdi. The National Commission for Museums and Monuments lists it among the sites proposed for declaration as national monuments, in its category of indigenous, colonial and early post-colonial technology.
>
> Wikipedia, citing reports in The Engineer and other newspapers of 1928 and 1932, states that construction began in 1928 and that the bridge was opened on 24 May 1932, Empire Day. It measures about 788 metres (2,584 feet) between abutments, carries 3 ft 6 in gauge track, and was described at the time as the longest bridge in Africa. It replaced the ferry that had carried railway passengers across the Benue. Wikipedia also names Sir William Arrol & Co. as the builders and gives a cost of about £1,000,000, without citing a source for these.

## Traditional iron smelting furnaces of Igede

> The National Commission for Museums and Monuments lists the traditional iron smelting furnaces of Igede, in Benue State, among the sites proposed for declaration as national monuments, in its category of indigenous, colonial and early post-colonial technology. The Igede live in Oju and Obi local government areas; the NCMM does not name the communities where the furnaces stand.
>
> Iron working among the Igede is the subject of an article by Victor Iyanya of Benue State University, "Towards a resuscitation of indigenous iron technology among the Igede of central Nigeria" (Journal of African Cultural Studies, 2012), which could not be consulted for this record.

## Sources

- **NCMMP** — Proposed National Monuments (National Commission for Museums and Monuments (NCMM)). https://museum.ng/national-monuments-in-nigeria/proposed-national-monuments/. Tier 1.
- **NCMMD** — National Monuments (National Commission for Museums and Monuments (NCMM)). https://museum.ng/national-monuments-in-nigeria/national-monuments/. Tier 1.
- **NCMMM** — National Museums (National Commission for Museums and Monuments (NCMM)). https://museum.ng/museums/national-museums/. Tier 1.
- **WOB** — Old Bridge, Makurdi (Wikipedia). https://en.wikipedia.org/wiki/Old_Bridge,_Makurdi. Tier 3.

## Not used

- Search-engine summaries of pages that could not be opened (the museum's founding date and collections; the Iyanya abstract).

## Research gaps

- **National Museum, Makurdi: history and collections.** Search results say it was established in 1989 in a building of 1929 with ethnographic, archaeological and contemporary art collections, but the pages could not be read. Needs NCMM or museum sources.
- **Igede iron smelting sites.** Which communities hold the furnaces, their dating and condition. Iyanya (2012) could not be read; no excavation dates are known.
- **Makurdi bridge: builder and cost.** Given by Wikipedia without a source. Needs the 1932 reports or railway records.
- **Other Benue heritage sites.** Palaces (Tor Tiv at Gboko, Och'Idoma at Otukpo), shrines, colonial buildings and the state museum or cultural centre are not yet researched.
- **Declared national monuments in Benue.** None appears in the NCMM list as checked on 26 Sep 2026.
## Tool change

- **Importer:** it can now create general places (museums, sites, monuments) linked to an existing LGA or state. Until now, places could only be created as state capitals.

## Tests (copy of the live database, MySQL 8.0.46)

- **Import:** the dry run is clean (4 sources, 3 places, 1 name, 1 link, 5 gaps).
- **IDs:**
  - `PLACE-NATIONAL-MUSEUM-MAKURDI`
  - `PLACE-MAKURDI-RAILWAY-BRIDGE`
  - `PLACE-TRADITIONAL-IRON-SMELTING-FURNACES-OF-IGEDE`
- **Pages after publishing on the copy:**
  - The Places list and all three pages load.
  - Makurdi LGA's page now lists "Places (2): Makurdi Railway Bridge, National Museum, Makurdi". The furnaces are linked to Benue State, because the NCMM doesn't name an LGA.
  - The sitemap is **unchanged at 3,023**, and there are 0 PHP errors.
- **Full undo:** the rollback returns the exact starting state.

## Deploy (on your approval)

1. Back up the database.
2. Copy the importer and the batch files.
3. Import (dry run, then apply).
4. Publish the batch.
5. Clear the cache.
6. Check the pages, the sitemap (3,023) and the error log.
