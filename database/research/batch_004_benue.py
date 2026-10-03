"""
Research batch 004 — Benue State: history, geography, peoples, economy and population
(researched 2026-09-24).

Fills the EMPTY description and geography fields of the existing, published Benue State
record (never overwrites), adds dated population/area figures (conflicting figures kept
side by side) and sourced 'neighbours' links. Written in our own words:
  * facts with two or more sources are stated plainly;
  * single-source or shared-lineage claims are attributed in the text;
  * the Wikipedia article and the Benue State Pension Commission page share the same
    wording, so they count as ONE lineage (see the Munshi Province claim).
"""
import json, re, sys

ACCESSED = "2026-09-24"
SOURCES = {
    "BENUEGOV": dict(source_type="official_website", title="About Benue State", organisation="Benue State Government",
                     url="https://benuestate.gov.ng/about/", verification_status="needs_corroboration",
                     notes="Official state website: created 3 February 1976 from Benue-Plateau State; named after the Benue River; latitudes 6°25′–8°8′ N, longitudes 7°47′–10°0′ E; area about 34,059 km²; neighbours Nasarawa (N), Taraba (E), Cross River and Ebonyi (S), Enugu (SW), Kogi (W), Cameroon (SE); Tiv, Idoma and Igede peoples among others (Etulo, Abakwa, Jukun, Nyifon, Akweya, Ufia); crops; 'Food Basket of the Nation'."),
    "CITYPOP": dict(source_type="dataset", title="Benue (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                    url="https://www.citypopulation.de/en/nigeria/admin/NGA007__benue/", verification_status="needs_corroboration",
                    notes="Census 1991-11-26: 2,753,077; census 2006-03-21: 4,253,641; projection 2022-03-21: 6,141,300. Source: National Population Commission of Nigeria and National Bureau of Statistics. Notes that Nigerian population figures have high error rates and census results are disputed."),
    "WBENUE": dict(source_type="encyclopedia", title="Benue State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Benue_State",
                   verification_status="needs_corroboration",
                   notes="Used to corroborate. Its history paragraph shares its wording with the Benue State Pension Commission page."),
    "BSPC": dict(source_type="official_website", title="Benue State", organisation="Benue State Pension Commission",
                 url="https://bspc.be.gov.ng/benue-state/",
                 archive_reference="Internet Archive snapshot 20260311163704: http://web.archive.org/web/20260311163704/https://bspc.be.gov.ng/benue-state/",
                 verification_status="needs_corroboration",
                 notes="State agency page (site later suspended; consulted via the Internet Archive). Same wording as Wikipedia's history paragraph: Munshi Province until 1918; Igala areas moved to Kogi State in 1991."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration", notes="(Reused from batch 001.) 2006 census table: Benue 4,219,244; area 30,755 km²."),
    "KOGIREF": dict(source_type="encyclopedia", title="List of Nigerian states by date of statehood", organisation="Wikipedia",
                    url="https://en.wikipedia.org/wiki/List_of_Nigerian_states_by_date_of_statehood", verification_status="needs_corroboration",
                    notes="(Reused from batch 001.)"),
}

DESCRIPTION = """Benue State was created on 3 February 1976, when Benue-Plateau State was divided into two new states, Benue and Plateau. It takes its name from the Benue River, one of Nigeria's largest rivers, which flows through the state. Its capital is Makurdi, and it is grouped in the North Central geopolitical zone.

The state borders Nasarawa State to the north, Taraba State to the east, Cross River and Ebonyi states to the south, Enugu State to the south-west and Kogi State to the west, and it shares an international boundary with Cameroon to the south-east.

Its present shape dates from 27 August 1991, when Kogi State was formed from parts of Benue and Kwara states. State and encyclopaedia sources, which share the same wording, describe the areas transferred from Benue as mostly Igala-speaking.

The Tiv, Idoma and Igede are the state's largest peoples, and smaller communities include the Etulo and the Jukun. The state government also names the Abakwa, Nyifon, Akweya and Ufia among its peoples.

Farming is central to the state's economy and identity, and its official slogan is "Food Basket of the Nation". Crops named by both the state government and other sources include yams, cassava, rice, soya beans, groundnuts and sesame (beniseed).

Before independence the area was part of a province of colonial Northern Nigeria. Sources that share the same wording say it was first called Munshi Province and was renamed after the Benue River in 1918; this has not yet been checked against colonial records.

The 2006 census counted 4,253,641 people, according to the National Population Commission figures reproduced by City Population. Other published figures, and the earlier and later counts, are listed below with their sources; Nigerian census results are disputed, so none of these figures should be read as exact."""

GEOGRAPHY = """The state government gives the state's extent as roughly latitude 6°25′ to 8°8′ North and longitude 7°47′ to 10°0′ East. It puts the area at about 34,059 square kilometres, while Statoids gives 30,755 square kilometres; both figures are recorded below rather than one being chosen.

The Benue River crosses the state, and the state government credits its floodplains with supporting much of the state's farming. The landscape is described as a mix of plains, hills and fertile valleys."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


UPDATES = [dict(ref="@admin_units:state:benue", fields=dict(description=DESCRIPTION, geography_notes=GEOGRAPHY),
                srcs=[("BENUEGOV", "Creation 1976 from Benue-Plateau; name from the Benue River; neighbours; peoples; crops; slogan; extent and area"),
                      ("WBENUE", "Name from the Benue River; neighbours; Tiv, Idoma, Igede, Etulo, Jukun; crops; slogan; Igala areas to Kogi (1991); Munshi Province"),
                      ("BSPC", "Munshi Province until 1918; Igala areas to Kogi State in 1991 (same wording as Wikipedia)"),
                      ("CITYPOP", "2006 census 4,253,641 (National Population Commission)"),
                      ("KOGIREF", "Kogi State formed 27 August 1991 from Benue and Kwara states")])]

NEIGHBOURS = ["nasarawa", "taraba", "cross-river", "ebonyi", "enugu", "kogi"]
RELATIONS = [dict(frm="@admin_units:state:benue", type="neighbours", to=f"@admin_units:state:{n}", source="BENUEGOV",
                  evidence="multiple_sources", notes="Listed as a neighbouring state by the Benue State Government and by Wikipedia.") for n in NEIGHBOURS]

STATISTICS = [
    dict(record="@admin_units:state:benue", metric="population", value_low=4253641, reference_year=2006, method="census", source="CITYPOP", evidence="multiple_sources",
         notes="National Population Commission figure as reproduced by City Population; also given by Wikipedia. Nigerian census results are disputed."),
    dict(record="@admin_units:state:benue", metric="population", value_low=4219244, reference_year=2006, method="census", source="STAT", evidence="single_reliable_source",
         notes="Statoids' 2006 census table; differs from the figure published by the National Population Commission."),
    dict(record="@admin_units:state:benue", metric="population", value_low=2753077, reference_year=1991, method="census", source="CITYPOP", evidence="single_reliable_source",
         notes="1991 census (26 November 1991), as reproduced by City Population."),
    dict(record="@admin_units:state:benue", metric="population", value_low=6141300, reference_year=2022, method="projection", source="CITYPOP", evidence="single_reliable_source",
         notes="Projection, not a count (National Population Commission / National Bureau of Statistics, via City Population)."),
    dict(record="@admin_units:state:benue", metric="area_km2", value_low=34059, method="other", source="BENUEGOV", evidence="single_reliable_source",
         notes="'About 34,059 square kilometers' (Benue State Government)."),
    dict(record="@admin_units:state:benue", metric="area_km2", value_low=30755, method="other", source="STAT", evidence="single_reliable_source",
         notes="Statoids; differs from the state government's figure."),
]

GAPS = [
    ("Munshi Province (to 1918) and the colonial provinces", "Given only by two sources with the same wording (Wikipedia; Benue State Pension Commission). Needs colonial records (e.g. Northern Nigeria annual reports or gazettes)."),
    ("Origin of the name 'Benue' ('Ber-nor')", "Wikipedia's account that the name comes from the Tiv 'Ber-nor' ('river of hippopotamus') is unsourced; not recorded."),
    ("Benue's area", "34,059 km² (state government) vs 30,755 km² (Statoids); both recorded, neither chosen."),
    ("2006 population", "4,253,641 (National Population Commission via City Population; Wikipedia) vs 4,219,244 (Statoids); both recorded."),
    ("Ethnic groups named only by the state government", "Abakwa, Nyifon, Akweya and Ufia; single source."),
    ("Political history since 1976", "Governors and administrations are not yet recorded."),
    ("Pre-colonial history of the area", "Not yet researched; needs scholarly sources and clearly marked oral traditions."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="Benue State: history, geography, peoples, economy and population (fills empty fields of the existing record).")


def report():
    total = words(DESCRIPTION) + words(GEOGRAPHY)
    L = ["# Research batch 004 — Benue State: history, geography, peoples and economy", "",
         f"Researched {ACCESSED}. Benue State is already published, so **this text goes live the moment it is imported** — this report is the review.", "",
         f"New prose: **{total} words** (with the existing summary the page passes the 300-word indexing rule, so Benue State becomes the first Nigeria page Google may index).", "",
         "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    L += ["", "## Overview (description field)", ""] + [f"> {p}" if p else ">" for p in DESCRIPTION.split("\n")]
    L += ["", "## Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in GEOGRAPHY.split("\n")]
    L += ["", "## How each statement is supported", "",
          "| Statement | Sources | Handling |", "|---|---|---|",
          "| Created 3 Feb 1976 from Benue-Plateau | Benue State Government; Statoids; Wikipedia list (batch 001) | stated |",
          "| Named after the Benue River | Benue State Government; Wikipedia | stated |",
          "| Neighbouring states and Cameroon border | Benue State Government; Wikipedia | stated; 6 'neighbours' links |",
          "| Kogi formed 27 Aug 1991 from Benue and Kwara | Statoids; Wikipedia list (batch 001) | stated |",
          "| Areas moved to Kogi were mostly Igala-speaking | Wikipedia = Pension Commission (same wording) | attributed |",
          "| Tiv, Idoma, Igede largest; Etulo, Jukun smaller | Benue State Government; Wikipedia | stated |",
          "| Abakwa, Nyifon, Akweya, Ufia | Benue State Government only | attributed |",
          "| 'Food Basket of the Nation'; crops | Benue State Government; Wikipedia (crops: overlap only) | stated |",
          "| Munshi Province until 1918 | Wikipedia = Pension Commission (same wording) | attributed + caveat + gap |",
          "| 2006 census 4,253,641 | City Population (NPC); Wikipedia | stated, with 'disputed' note |",
          "| Extent (lat/long), area 34,059 km² | Benue State Government only | attributed |",
          "| Area 30,755 km² | Statoids only | attributed |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", "## Neighbours", "", "Benue State ↔ " + ", ".join(n.replace('-', ' ').title() for n in NEIGHBOURS) + " (Benue State Government; Wikipedia).",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- The Benue State page becomes indexable and joins the sitemap (+1), with the reference pages of the sources it cites (sources created by research batches become indexable once a page citing them is).",
          "- Content is original prose with every claim sourced or attributed; neutral tone; no ethnic-conflict material.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_004_benue.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_004_benue_REVIEW.md", "w").write(report())
    print(f"prose words: description={words(DESCRIPTION)} geography={words(GEOGRAPHY)} total={words(DESCRIPTION)+words(GEOGRAPHY)}")
