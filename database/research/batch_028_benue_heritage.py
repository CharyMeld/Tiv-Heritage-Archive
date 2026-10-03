"""
Research batch 028 — Benue heritage sites (gap G-04), part 1. Researched 2026-09-26.

Official basis: the National Commission for Museums and Monuments (NCMM, museum.ng). Benue has
NO declared national monument in the NCMM list; two are PROPOSED (Makurdi Railway Bridge;
Traditional Iron Smelting Furnaces in Igede), and the NCMM runs a National Museum at Makurdi.

Evidence notes:
  * Makurdi Railway Bridge history: Wikipedia ("Old Bridge, Makurdi"), which cites contemporary
    reports (The Engineer, 27 May 1932; The Times of India, 25 May 1932; Chronicle, Adelaide, 1928).
    Facts it gives without a citation (builder, cost) are attributed to Wikipedia.
  * Igede furnaces: only the NCMM listing is used. V. Iyanya's article (Journal of African Cultural
    Studies 24(2), 2012) could not be read (access blocked), so nothing is taken from it; it is
    named as further reading.
  * National Museum, Makurdi: NCMM gives the address only. The "established 1989, in a building of
    1929" claim seen in search results could not be read at source, so it is a gap.
"""
import json, re, sys

ACCESSED = "2026-09-26"
SOURCES = {
    "NCMMP": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="Proposed National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/proposed-national-monuments/",
                  verification_status="verified",
                  notes="NCMM manages 65 national monuments with about 100 more awaiting declaration. Benue entries: No. 89 Makurdi Railway Bridge, Makurdi; No. 91 Traditional Iron Smelting Furnaces in Igede; both under Technology (Indigenous, Colonial and Early Post-Colonial Era)."),
    "NCMMD": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/national-monuments/",
                  verification_status="verified", notes="List of declared national monuments; no Benue State monument appears in it (checked 2026-09-26)."),
    "NCMMM": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="National Museums",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/museums/national-museums/",
                  verification_status="verified", notes="National Museum Makurdi: GP 4, Ahmadu Bello, opposite the Deputy Governor's Office, P.M.B. 102294, Makurdi, Benue State."),
    "WOB": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Old Bridge, Makurdi", organisation="Wikipedia",
                url="https://en.wikipedia.org/wiki/Old_Bridge,_Makurdi", verification_status="needs_corroboration",
                notes="Combined rail and road bridge over the Benue at Makurdi; construction began 1928; opened 24 May 1932 (Empire Day) (citing The Engineer, 27 May 1932); about 2,584 ft (788 m) between abutments (citing The Times of India, 1932); 3 ft 6 in gauge; longest bridge in Africa at the time (citing Chronicle, Adelaide, 1928); built by Sir William Arrol & Co. at about £1,000,000 (uncited); replaced the railway ferry."),
}

MUSEUM = """The National Museum, Makurdi is one of the national museums run by the National Commission for Museums and Monuments (NCMM), the federal agency responsible for Nigeria's museums and monuments. The NCMM gives its address as GP 4, Ahmadu Bello, opposite the Deputy Governor's Office, Makurdi. When the museum was founded and what its galleries hold are not yet documented here from a readable source."""

BRIDGE = """The Makurdi Railway Bridge, often called the Old Bridge, carries both the railway and a road across the River Benue at Makurdi. The National Commission for Museums and Monuments lists it among the sites proposed for declaration as national monuments, in its category of indigenous, colonial and early post-colonial technology.

Wikipedia, citing reports in The Engineer and other newspapers of 1928 and 1932, states that construction began in 1928 and that the bridge was opened on 24 May 1932, Empire Day. It measures about 788 metres (2,584 feet) between abutments, carries 3 ft 6 in gauge track, and was described at the time as the longest bridge in Africa. It replaced the ferry that had carried railway passengers across the Benue. Wikipedia also names Sir William Arrol & Co. as the builders and gives a cost of about £1,000,000, without citing a source for these."""

FURNACES = """The National Commission for Museums and Monuments lists the traditional iron smelting furnaces of Igede, in Benue State, among the sites proposed for declaration as national monuments, in its category of indigenous, colonial and early post-colonial technology. The Igede live in Oju and Obi local government areas; the NCMM does not name the communities where the furnaces stand.

Iron working among the Igede is the subject of an article by Victor Iyanya of Benue State University, "Towards a resuscitation of indigenous iron technology among the Igede of central Nigeria" (Journal of African Cultural Studies, 2012), which could not be consulted for this record."""

RECORDS = [
    dict(key="museum", table="places", evidence="single_reliable_source", level="well_documented",
         fields=dict(place_type="museum", name="National Museum, Makurdi", slug="national-museum-makurdi", admin_unit_id="@admin_units:lga:benue/makurdi",
                     status="existing", summary="The National Museum, Makurdi is a national museum of the National Commission for Museums and Monuments in Makurdi, Benue State.",
                     description=MUSEUM),
         srcs=[("NCMMM", "National museum of the NCMM; address")]),
    dict(key="bridge", table="places", evidence="multiple_sources", level="well_documented",
         fields=dict(place_type="historical_place", name="Makurdi Railway Bridge", slug="makurdi-railway-bridge", admin_unit_id="@admin_units:lga:benue/makurdi",
                     status="existing", summary="The Makurdi Railway Bridge (Old Bridge), opened in 1932, carries rail and road across the Benue at Makurdi; the NCMM has proposed it as a national monument.",
                     description=BRIDGE),
         srcs=[("NCMMP", "Proposed national monument No. 89 (technology)"), ("WOB", "Construction 1928, opened 24 May 1932, length, gauge, builder, cost")]),
    dict(key="furnaces", table="places", evidence="single_reliable_source", level="well_documented",
         fields=dict(place_type="archaeological_site", name="Traditional iron smelting furnaces of Igede", slug="igede-iron-smelting-furnaces",
                     admin_unit_id="@admin_units:state:benue", status="unknown",
                     summary="The traditional iron smelting furnaces of Igede, Benue State, are on the NCMM list of proposed national monuments.",
                     description=FURNACES),
         srcs=[("NCMMP", "Proposed national monument No. 91 (technology)")]),
]
NAMES = [dict(record="bridge", name="Old Bridge, Makurdi", name_type="alternative", usage_notes="Common name (Wikipedia).", srcs=["WOB"])]
RELATIONS = [
    dict(frm="furnaces", type="associated_with", to="@ethnic_groups:igede", source="NCMMP", evidence="single_reliable_source", level="well_documented",
         role="iron-working heritage", notes="The NCMM lists the furnaces as those 'in Igede'."),
]
GAPS = [
    ("National Museum, Makurdi: history and collections", "Search results say it was established in 1989 in a building of 1929 with ethnographic, archaeological and contemporary art collections, but the pages could not be read. Needs NCMM or museum sources."),
    ("Igede iron smelting sites", "Which communities hold the furnaces, their dating and condition. Iyanya (2012) could not be read; no excavation dates are known."),
    ("Makurdi bridge: builder and cost", "Given by Wikipedia without a source. Needs the 1932 reports or railway records."),
    ("Other Benue heritage sites", "Palaces (Tor Tiv at Gboko, Och'Idoma at Otukpo), shrines, colonial buildings and the state museum or cultural centre are not yet researched."),
    ("Declared national monuments in Benue", "None appears in the NCMM list as checked on 26 Sep 2026."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Benue heritage (G-04) part 1: National Museum Makurdi; the two NCMM-proposed national monuments (Makurdi Railway Bridge, Igede iron smelting furnaces).")


def quote(t):
    return [f"> {p}" if p else ">" for p in t.split("\n")]


def report():
    L = ["# Research batch 028 — Benue heritage sites (1)", "",
         f"Researched {ACCESSED}. Phase 3, gap G-04. Created in review; published only after your approval.", "",
         "**Finding:** the NCMM's list of declared national monuments contains **no site in Benue State**. Two Benue sites are on its list of **proposed** national monuments, and the NCMM runs a national museum in Makurdi.", "",
         f"- National Museum, Makurdi ({words(MUSEUM)} words) · Makurdi Railway Bridge ({words(BRIDGE)}) · Igede iron smelting furnaces ({words(FURNACES)}).",
         "- All three are short, so they stay noindex (sitemap unchanged). They are the first heritage-site records for Benue, built on the official NCMM lists.", "",
         "## National Museum, Makurdi", ""] + quote(MUSEUM) + ["", "## Makurdi Railway Bridge", ""] + quote(BRIDGE) + \
        ["", "## Traditional iron smelting furnaces of Igede", ""] + quote(FURNACES)
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. Tier {s['source_tier']}." for k, s in SOURCES.items()]
    L += ["", "## Not used", "",
          "- Search-engine summaries of pages that could not be opened (the museum's founding date and collections; the Iyanya abstract).", "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_028_benue_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_028_benue_heritage_REVIEW.md", "w").write(report())
    print(f"museum={words(MUSEUM)} bridge={words(BRIDGE)} furnaces={words(FURNACES)}")
