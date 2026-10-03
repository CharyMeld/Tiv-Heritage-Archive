"""
Research batch 025 — Benue (Phase 3): culture of Benue's non-Tiv peoples, part 1 (gap G-05):
Igede Agba (the Igede new yam festival) and Alekwu (the Idoma ancestral masquerade and festival).
Researched 2026-09-26. The first cultural records of the national archive.

Evidence notes:
  * Igede Agba: NICO (a federal agency, Tier 1) for the festival itself; I am Benue (2024, by
    Ojotule Angela Omaji) for the calendar day and customs, attributed. Wikipedia's "Igede Agba"
    is NOT used: it cites nothing and is flagged as possibly machine-generated. The origin story
    (an ancestor named Agba; celebration begun in the 1950s by the Igede Youth Association) is
    found only on weak websites and is left as a gap.
  * Alekwu: Amali (1997, Ufahamu, peer-reviewed) for what the Alekwu is and its chants;
    Alachi and Tyokyaa (paper hosted by idomaland.org) for the festival (Ijah Alekwu). Beliefs are
    described as the practitioners' beliefs; the chants' historical content is oral tradition.
"""
import json, re, sys

ACCESSED = "2026-09-26"
SOURCES = {
    "NICO": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="Igede Agba Festival",
                 organisation="National Institute for Cultural Orientation (NICO)", publication_date="2025-04-17",
                 url="https://nico.gov.ng/igede-agba-festival/", verification_status="verified",
                 notes="Igede Agba is celebrated by the Igede people of Oju and Obi LGAs, held annually in September; new yam celebration and thanksgiving for a bountiful harvest; thanks to deities and ancestors; traditional dance troupes and music; yams prepared and shared; fosters unity and preserves heritage."),
    "IAMAG": dict(source_type="website", source_kind="community_organisation", source_tier=4, title="Igede Agba (New Yam Festival)",
                  author="Ojotule Angela Omaji", organisation="I am Benue", publication_date="2024-09-04",
                  url="https://www.iambenue.com/igede-agba-new-yam-festival/", verification_status="needs_corroboration",
                  notes="Held every first 'Ihigile market day' in September, the seventh moon 'Oya' in the Igede calendar; thanksgiving for a good harvest and the beginning of the planting season; pounded yam, music and dancing; awards for farmers with the largest farms or biggest yams; male children gather at the father's round hut (ugara), wives and daughters in the senior wife's hut; 'a time of peace, reconciliation and sharing'."),
    "AMALI": dict(source_type="research", source_kind="journal_article", source_tier=2,
                  title="Alekwu Poetry as a Source of Historical Reconstruction: The Pursuit of Idoma-Otukpo Origin, Genealogy and Migration",
                  author="Idris O. O. Amali", organisation="Ufahamu: A Journal of African Studies", publication_date="1997",
                  publication_details="Vol. 25, No. 3 (peer reviewed). DOI 10.5070/F7253016633",
                  url="https://escholarship.org/uc/item/4v83483q", verification_status="verified",
                  notes="Alekwu is the physical re-enactment in masquerade form of a qualified deceased father in Idoma-Otukpo; its appearance means the spiritual return of the father; Alekwu chants the history of the land and the genealogies of lineages (quoting S. O. O. Amali, 1971); the Alekwuafia masquerade tradition also exists among the Doma and the Iyala; oral poetry used as historical evidence."),
    "ALACHI": dict(source_type="research", source_kind="research_report", source_tier=2,
                   title="The Alekwu Festival among the Idoma of Central Nigeria: Implication for Curriculum Planners in the Nigerian Educational System",
                   author="Omada Virginia Alachi; Godwin Tyokyaa", organisation="idomaland.org (PDF)",
                   url="https://www.idomaland.org/sites/default/files/pdfs/the_alekwu_festival.pdf", verification_status="needs_corroboration",
                   notes="Academic paper (its first part, the authors state, appeared in the Anyigbe Journal, Vol. 1 No. 1). Alekwu festival held annually in March in many parts of Idoma land, twice elsewhere with the public performance in November–December; rotation among clans that own Alekwu; hosted in the compound of the eldest man (Ada-Alekwu), the custodian; lasts three to seven days; Aje (earth) appeased first; road clearing and in-laws' gifts with Alekwu as witness; grand finale at the chief's palace with the sub-clans' masquerades and the chief's recital of history and genealogy; gifts of yam and dried meat; expected to usher in the rains; custodians of Alekwu secrets in Ugboju, Oko Orokam and Otukpo."),
}

AGBA = """Igede Agba is the new yam festival of the Igede people of Oju and Obi local government areas in Benue State. The National Institute for Cultural Orientation (NICO), a federal agency, describes it as an annual festival held in September to give thanks for a good harvest, with yams at its centre. The community thanks the deities and ancestors for the farming season, dance troupes perform to indigenous music, and yams are prepared in traditional dishes and shared with visitors. NICO presents the festival as a source of unity for the Igede and a way of keeping their heritage alive.

An article on I am Benue by Ojotule Angela Omaji (2024) dates the festival to the first Ihigile market day of September, which it describes as the seventh moon, Oya, in the Igede calendar. It calls the festival a thanksgiving for a good harvest and for the beginning of the planting season, and "a time of peace, reconciliation and sharing". Families gather by household: the sons meet at their father's round hut, the ugara, while the wives and daughters gather in the senior wife's hut. Farmers with the largest farms or the biggest yams receive awards, and the day is marked by pounded yam, music and dancing.

How and when the festival began is not established here: the accounts found online differ and are not supported by reliable sources."""

ALEKWU = """Alekwu is the ancestral spirit and masquerade tradition of the Idoma. In a peer-reviewed study of the Idoma of Otukpo, Idris O. O. Amali (1997) describes the Alekwu as the re-enactment, in masquerade form, of a qualified deceased father: when the masquerade appears to perform or chant, it represents the father's spiritual return to his family and society. Quoting S. O. O. Amali, he writes that the Alekwu chants the history of the land and the genealogies of its lineages, and he treats this oral poetry as a source, to be read as oral tradition, for the origin and migrations of the Otukpo Idoma. He notes that a related masquerade tradition, the Alekwuafia, also exists among the Doma and the Iyala.

A paper by Omada Virginia Alachi and Godwin Tyokyaa describes the Alekwu festival, Ijah Alekwu. In many parts of Idoma land it is held every year in March; elsewhere it is celebrated twice, with a public performance in November or December. The clans that own an Alekwu host it in turn, in the compound of the clan's eldest man, the custodian of the Alekwu, and it lasts three to seven days. It opens with offerings to Aje, the earth. Communities then clear the roads, and sons-in-law bring gifts to their in-laws, with the Alekwu as witness to the settling of grievances. At the close, the ancestral masquerades of each sub-clan enter the chief's arena, the chief recites the history and genealogy of the people, and the villages present yams and dried meat. The authors write that the rites are expected to bring the rains and that the festival renews the people's faith in their ancestors."""

RECORDS = [
    dict(key="agba", table="cultural_records", evidence="multiple_sources", level="verified",
         fields=dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice",
                     name="Igede Agba", slug="igede-agba", language_id="@languages:igede",
                     timing="Annually in September (first Ihigile market day, per I am Benue)", season="New yam harvest",
                     month_from=9, month_to=9, current_status="active", scope_level="ethnic_group",
                     summary="Igede Agba is the annual new yam festival of the Igede people of Oju and Obi, Benue State, held in September as a thanksgiving for the harvest.",
                     description=AGBA),
         srcs=[("NICO", "Igede of Oju and Obi; annual, September; new yam thanksgiving; dances, music, shared yams; unity"),
               ("IAMAG", "First Ihigile market day of September (seventh moon, Oya); customs; awards; peace and reconciliation")]),
    dict(key="alekwu", table="cultural_records", evidence="multiple_sources", level="verified",
         fields=dict(record_type="belief_ritual", cultural_category="knowledge_belief", nature="documented_practice",
                     name="Alekwu", local_name="Alekwu", slug="alekwu", language_id="@languages:idoma",
                     timing="Festival (Ijah Alekwu) annually in March in many Idoma areas; elsewhere twice, with a public performance in November–December",
                     season="Before the rains", month_from=3, month_to=3, current_status="active", scope_level="ethnic_group",
                     summary="Alekwu is the ancestral spirit and masquerade tradition of the Idoma; its festival, Ijah Alekwu, is held in many Idoma areas in March.",
                     description=ALEKWU, significance=None),
         srcs=[("AMALI", "Alekwu as masquerade re-enactment of a deceased father; chants of history and genealogy; Alekwuafia among Doma and Iyala"),
               ("ALACHI", "Ijah Alekwu: March (or twice); rotation; custodian; 3–7 days; Aje; in-laws; finale; rains")]),
]
for r in RECORDS:
    r["fields"] = {k: v for k, v in r["fields"].items() if v is not None}

RELATIONS = [
    dict(frm="agba", type="celebrated_by", to="@ethnic_groups:igede", source="NICO", evidence="multiple_sources", level="verified",
         notes="The Igede people (NICO; I am Benue)."),
    dict(frm="agba", type="celebrated_in", to="@admin_units:lga:benue/oju", source="NICO", evidence="single_reliable_source", level="verified",
         notes="Oju and Obi LGAs (NICO)."),
    dict(frm="agba", type="celebrated_in", to="@admin_units:lga:benue/obi", source="NICO", evidence="single_reliable_source", level="verified",
         notes="Oju and Obi LGAs (NICO)."),
    dict(frm="alekwu", type="practised_by", to="@ethnic_groups:idoma", source="AMALI", evidence="multiple_sources", level="verified",
         notes="Idoma ancestral masquerade (Amali 1997; Alachi and Tyokyaa)."),
    dict(frm="alekwu", type="celebrated_in", to="@admin_units:lga:benue/oturkpo", source="AMALI", evidence="multiple_sources", level="well_documented",
         notes="Amali's study concerns the Idoma of Otukpo; Alachi and Tyokyaa name Otukpo and Ugboju among the custodians. Held in many other Idoma areas too."),
]

GAPS = [
    ("Origin of Igede Agba", "Websites say the festival is named after an ancestor, Agba, and was first organised in the 1950s by the Igede Youth Association (now Omi Ny'Igede). Not found in a reliable source."),
    ("Igede Agba: the Ny'Igede's role and the venue", "Who presides and where the central celebration is held are not documented in the sources used."),
    ("Alekwu: which communities hold it when", "The March / November–December pattern is described generally; which clans and LGAs follow which calendar is not recorded."),
    ("Other Benue peoples (G-05 continues)", "Etulo, Akweya, Nyifon, Ufia and Jukun culture, and Idoma and Igede food, dress and institutions, are for the next culture batches."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=[], gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Benue (Phase 3) culture, part 1: Igede Agba and Alekwu.")


def quote(t):
    return [f"> {p}" if p else ">" for p in t.split("\n")]


def report():
    L = ["# Research batch 025 — Benue culture (1): Igede Agba and Alekwu", "",
         f"Researched {ACCESSED}. Phase 3, gap G-05 (culture of the non-Tiv peoples), first part. These are the **first cultural records** of the national archive. They are created in review and published only after your approval.", "",
         f"- **Igede Agba** (festival, Igede, Oju and Obi): {words(AGBA)} words.",
         f"- **Alekwu** (belief and ritual, Idoma; festival Ijah Alekwu): {words(ALEKWU)} words.",
         "- Both are under 300 words, so their pages stay noindex until more is researched; the sitemap is unchanged.",
         f"- {len(RELATIONS)} links: Igede Agba → the Igede, Oju, Obi; Alekwu → the Idoma, Otukpo.", "",
         "## Igede Agba", ""] + quote(AGBA) + ["", "## Alekwu", ""] + quote(ALEKWU)
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s.get('author', s['organisation'])}{', ' + s['publication_date'] if s.get('publication_date') else ''}). {s['url']}. Tier {s['source_tier']}." for k, s in SOURCES.items()]
    L += ["", "## Not used", "",
          "- Wikipedia, \"Igede Agba\": it cites nothing, and Wikipedia flags it as possibly machine-generated (October 2025).",
          "- Blogs and tourism sites on the festival's origin (Agba the ancestor; the 1950s): weak, so left as a gap.",
          "- A photo-tour site's dating of Alekwu (late March–early April): the academic paper is used instead.", "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_025_benue_culture.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_025_benue_culture_REVIEW.md", "w").write(report())
    print(f"agba={words(AGBA)} alekwu={words(ALEKWU)} relations={len(RELATIONS)}")
