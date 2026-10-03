# Research batch 025 — Benue culture (1): Igede Agba and Alekwu

Researched 2026-09-26. Phase 3, gap G-05 (culture of the non-Tiv peoples), first part. These are the **first cultural records** of the national archive. They are created in review and published only after your approval.

- **Igede Agba** (festival, Igede, Oju and Obi): 229 words.
- **Alekwu** (belief and ritual, Idoma; festival Ijah Alekwu): 276 words.
- Both are under 300 words, so their pages stay noindex until more is researched; the sitemap is unchanged.
- 5 links: Igede Agba → the Igede, Oju, Obi; Alekwu → the Idoma, Otukpo.

## Igede Agba

> Igede Agba is the new yam festival of the Igede people of Oju and Obi local government areas in Benue State. The National Institute for Cultural Orientation (NICO), a federal agency, describes it as an annual festival held in September to give thanks for a good harvest, with yams at its centre. The community thanks the deities and ancestors for the farming season, dance troupes perform to indigenous music, and yams are prepared in traditional dishes and shared with visitors. NICO presents the festival as a source of unity for the Igede and a way of keeping their heritage alive.
>
> An article on I am Benue by Ojotule Angela Omaji (2024) dates the festival to the first Ihigile market day of September, which it describes as the seventh moon, Oya, in the Igede calendar. It calls the festival a thanksgiving for a good harvest and for the beginning of the planting season, and "a time of peace, reconciliation and sharing". Families gather by household: the sons meet at their father's round hut, the ugara, while the wives and daughters gather in the senior wife's hut. Farmers with the largest farms or the biggest yams receive awards, and the day is marked by pounded yam, music and dancing.
>
> How and when the festival began is not established here: the accounts found online differ and are not supported by reliable sources.

## Alekwu

> Alekwu is the ancestral spirit and masquerade tradition of the Idoma. In a peer-reviewed study of the Idoma of Otukpo, Idris O. O. Amali (1997) describes the Alekwu as the re-enactment, in masquerade form, of a qualified deceased father: when the masquerade appears to perform or chant, it represents the father's spiritual return to his family and society. Quoting S. O. O. Amali, he writes that the Alekwu chants the history of the land and the genealogies of its lineages, and he treats this oral poetry as a source, to be read as oral tradition, for the origin and migrations of the Otukpo Idoma. He notes that a related masquerade tradition, the Alekwuafia, also exists among the Doma and the Iyala.
>
> A paper by Omada Virginia Alachi and Godwin Tyokyaa describes the Alekwu festival, Ijah Alekwu. In many parts of Idoma land it is held every year in March; elsewhere it is celebrated twice, with a public performance in November or December. The clans that own an Alekwu host it in turn, in the compound of the clan's eldest man, the custodian of the Alekwu, and it lasts three to seven days. It opens with offerings to Aje, the earth. Communities then clear the roads, and sons-in-law bring gifts to their in-laws, with the Alekwu as witness to the settling of grievances. At the close, the ancestral masquerades of each sub-clan enter the chief's arena, the chief recites the history and genealogy of the people, and the villages present yams and dried meat. The authors write that the rites are expected to bring the rains and that the festival renews the people's faith in their ancestors.

## Sources

- **NICO** — Igede Agba Festival (National Institute for Cultural Orientation (NICO), 2025-04-17). https://nico.gov.ng/igede-agba-festival/. Tier 1.
- **IAMAG** — Igede Agba (New Yam Festival) (Ojotule Angela Omaji, 2024-09-04). https://www.iambenue.com/igede-agba-new-yam-festival/. Tier 4.
- **AMALI** — Alekwu Poetry as a Source of Historical Reconstruction: The Pursuit of Idoma-Otukpo Origin, Genealogy and Migration (Idris O. O. Amali, 1997). https://escholarship.org/uc/item/4v83483q. Tier 2.
- **ALACHI** — The Alekwu Festival among the Idoma of Central Nigeria: Implication for Curriculum Planners in the Nigerian Educational System (Omada Virginia Alachi; Godwin Tyokyaa). https://www.idomaland.org/sites/default/files/pdfs/the_alekwu_festival.pdf. Tier 2.

## Not used

- Wikipedia, "Igede Agba": it cites nothing, and Wikipedia flags it as possibly machine-generated (October 2025).
- Blogs and tourism sites on the festival's origin (Agba the ancestor; the 1950s): weak, so left as a gap.
- A photo-tour site's dating of Alekwu (late March–early April): the academic paper is used instead.

## Research gaps

- **Origin of Igede Agba.** Websites say the festival is named after an ancestor, Agba, and was first organised in the 1950s by the Igede Youth Association (now Omi Ny'Igede). Not found in a reliable source.
- **Igede Agba: the Ny'Igede's role and the venue.** Who presides and where the central celebration is held are not documented in the sources used.
- **Alekwu: which communities hold it when.** The March / November–December pattern is described generally; which clans and LGAs follow which calendar is not recorded.
- **Other Benue peoples (G-05 continues).** Etulo, Akweya, Nyifon, Ufia and Jukun culture, and Idoma and Igede food, dress and institutions, are for the next culture batches.
## Tests (copy of the live database, MySQL 8.0.46)

- **Import:** the dry run is clean (4 sources, 2 records, 5 links, 4 gaps).
- **Records:** they get the IDs `CULT-IGEDE-AGBA` and `CULT-ALEKWU`, with evidence level *verified* (an official source for Igede Agba; two academic sources for Alekwu).
- **Pages after publishing on the copy:**
  - The Culture section, the Festival list and both record pages load.
  - The Igede Agba page shows its facts: September, the new yam harvest, active, ethnic-group scope.
  - The Igede page shows "Igede celebrates: Igede Agba", and the Idoma page "Idoma practises: Alekwu".
  - All the new pages are noindex (under 300 words), and the sitemap is **unchanged at 3,021**.
  - There are 0 PHP errors.
- **Full undo:** the rollback removes both records, their links, sources and gaps.
- **No code changes** in this batch, and **no changes to existing records**.

## Deploy (on your approval)

1. Back up the database.
2. Import (dry run, then apply).
3. Publish the batch.
4. Clear the cache.
5. Check the pages, the sitemap (3,021) and the error log.
