"""
Research batch 022 — Imo State: history, geography, peoples and economy (researched
2026-09-25). The last of the 36 states and the FCT. Same method as batches 013-021:

Fills the EMPTY description and geography fields of the existing, published Imo State record
(never overwrites) and adds dated population/area figures side by side. All three of Imo's
neighbours (Anambra, Abia, Rivers) are already linked, so no new links are needed.
  * the Imo State Government's "About Imo" pages render by script and returned no text, and
    NIPC's Imo page could not be retrieved (Internet Archive), so most statements rest on
    Wikipedia and are attributed; the 1976 creation is confirmed by Statoids, the 1991
    separation of Abia by Statoids, NIPC's Abia page and the Abia State Government;
  * the Civil War is mentioned only as Wikipedia states it; the Otokoto riots, #EndSARS and
    present-day insecurity are left for separate treatment.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "WIM": dict(source_type="encyclopedia", title="Imo State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Imo_State",
                verification_status="needs_corroboration",
                notes="Used for: borders (Anambra, Abia, Rivers); named after the Imo River; capital Owerri; slogan 'Eastern Heartland'; third smallest state; Igbo (about 98%); history (Southern Nigeria Protectorate, Women's War, Eastern Region, East Central State 1967, Owerri as Biafran capital in late 1969, Imo State 1976, Abia 1991, Ebonyi 1996); rivers (Imo, Orashi, Otamiri, Awbana) and Oguta Lake; agriculture and palm oil; oil and gas in the north and west; soil degradation, erosion and flooding; Oguta Lake and Njaba River tourism; Awo-Omamma brewery; area about 5,100 km²."),
    "NIPCAB": dict(source_type="official_website", title="Abia State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/abia-state", verification_status="needs_corroboration",
                   notes="Reused (batch 013). Abia carved out of Imo State on 27 August 1991; Imo lies to Abia's west."),
    "ABGOV": dict(source_type="official_website", title="About Abia", organisation="Abia State Government",
                  url="https://abiastate.gov.ng/about-abia/", verification_status="needs_corroboration",
                  notes="Reused (batch 013). Abia created on 27 August 1991 following the division of the former Imo State."),
    "CPIM": dict(source_type="dataset", title="Imo (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA017__imo/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,485,635; census 2006: 3,927,563; projection 2022: 5,459,300 (National Population Commission / National Bureau of Statistics)."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration",
                 notes="Reused (batch 001). 2006 census table: Imo 3,934,899, 5,135 km². Chronology: 1976-02-03 East-Central State divided into Anambra and Imo (Owerri); 1991-08-27 Abia split from Imo."),
}

IM_DESCRIPTION = """Imo State was created on 3 February 1976, when East-Central State was divided into Anambra and Imo states, according to Statoids and Wikipedia. Its capital is Owerri. Wikipedia says the state is named after the Imo River, which flows along its eastern border, and gives its slogan as "Eastern Heartland". On 27 August 1991 the eastern part of the state was separated to form Abia State. Statoids, the Nigerian Investment Promotion Commission and the Abia State Government all record this. The state is grouped in the South East geopolitical zone.

Wikipedia outlines the earlier history. The area has been home to Igbo communities for thousands of years. Early in the 20th century the British brought it into the Southern Nigeria Protectorate, and it later became a centre of resistance during the Women's War. After independence it was part of the Eastern Region, and from 1967 of East Central State. During the Civil War of 1967 to 1970, Wikipedia says, Owerri took over from Umuahia as the Biafran capital in late 1969, before federal forces took it early in 1970. According to Wikipedia, part of the area Imo lost in 1991 was later joined with part of Enugu State to form Ebonyi State in 1996.

Imo borders Anambra State to the north, Abia State to the east and Rivers State to the south and west. It has twenty-seven local government areas. Wikipedia counts it among the smallest states in Nigeria by area and among the more populous.

The people are almost entirely Igbo, about 98 per cent according to Wikipedia, and Igbo is spoken throughout the state alongside English. Besides Owerri, Wikipedia names Orlu, Okigwe, Oguta, Mbaise, Awo-Omamma and Ohaji/Egbema among the notable towns. Most people are Christian, and Wikipedia notes a revival of interest in Odinani, the traditional Igbo religion.

Farming is the main occupation. Wikipedia says the economy depends heavily on palm oil and notes that heavy use of the land has degraded much of the soil. Crude oil and natural gas are produced mainly in the north and west of the state, in areas such as Ohaji/Egbema, Oguta and Oru. Wikipedia also mentions a large brewery at Awo-Omamma.

The 2006 census counted 3,927,563 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

IM_GEOGRAPHY = """The state lies in the forest belt of south-eastern Nigeria. Wikipedia names the Imo, Orashi, Otamiri and Awbana rivers, and Oguta Lake in the west, as its main waters. It names Oguta Lake and the banks of the Njaba River among the state's attractions. It describes soil erosion as the state's most common environmental hazard, with more than 360 erosion sites, mostly gullies around Ideato, Orlu and Njaba, and recurring floods around Owerri and Oguta.

The area is given as about 5,100 square kilometres by Wikipedia and 5,135 by Statoids. Both figures are recorded below."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


IM = "@admin_units:state:imo"

UPDATES = [
    dict(ref=IM, fields=dict(description=IM_DESCRIPTION, geography_notes=IM_GEOGRAPHY),
         srcs=[("WIM", "Name; Owerri; Eastern Heartland; history; borders; 27 LGAs; Igbo; towns; religion; agriculture; oil and gas; brewery; rivers; Oguta Lake; erosion; floods; area about 5,100 km²"),
               ("STAT", "1976 East-Central divided into Anambra and Imo; 1991 Abia split from Imo; area 5,135 km²"),
               ("NIPCAB", "Abia carved out of Imo on 27 August 1991"),
               ("ABGOV", "Abia created in 1991 from the former Imo State"),
               ("CPIM", "2006 census 3,927,563 (National Population Commission)")]),
]


def stat(record, metric, value, source, notes, year=None, method="other"):
    d = dict(record=record, metric=metric, value_low=value, method=method, source=source, evidence="single_reliable_source", notes=notes)
    if year:
        d["reference_year"] = year
    return d


STATISTICS = [
    stat(IM, "population", 3927563, "CPIM", "National Population Commission figure as reproduced by City Population. Nigerian census results are disputed.", 2006, "census"),
    stat(IM, "population", 3934899, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(IM, "population", 2485635, "CPIM", "1991 census for the area of today's state (without Abia), as reproduced by City Population.", 1991, "census"),
    stat(IM, "population", 5459300, "CPIM", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(IM, "area_km2", 5100, "WIM", "Wikipedia ('around')."),
    stat(IM, "area_km2", 5135, "STAT", "Statoids."),
]

GAPS = [
    ("Imo official sources", "The Imo State Government's 'About Imo' pages render by script and returned no text; NIPC's Imo page could not be retrieved. Most of the text rests on Wikipedia and is attributed. Add an official source when available."),
    ("Igbo history in Imo", "Only outlined from Wikipedia. Igbo settlement history, Mbari art, and the Women's War need their own scholarly-sourced records."),
    ("The Civil War in Imo", "Only the fall of Owerri (Wikipedia) is mentioned. The war needs its own careful treatment."),
    ("Recent unrest", "The Otokoto riots (1996), #EndSARS protests (2020) and present-day insecurity are not summarised. They need separate, careful treatment."),
    ("Enugu–Imo border", "Listed by NIPC's Enugu page only (batch 009 gap). Wikipedia's Imo article does not list Enugu. Still not recorded."),
    ("Areas and population figures", "The areas and populations from each source differ. All are recorded; none is chosen."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[], statistics=STATISTICS,
                scope="Imo State: history, geography, peoples, economy and population (fills empty fields of the existing record). Completes the 36 states and the FCT.")


def report():
    w = words(IM_DESCRIPTION) + words(IM_GEOGRAPHY)
    L = ["# Research batch 022 — Imo State (the last of the 36 states and the FCT)", "",
         f"Researched {ACCESSED}. The Imo page is already published, so **this text goes live the moment it is imported**. This report is the review.", "",
         f"- New prose: **{w} words**. The page passes the 300-word indexing rule and becomes indexable.",
         "- No new neighbour links: Anambra, Abia and Rivers are already linked.", "",
         "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    L += ["", "## Imo State — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in IM_DESCRIPTION.split("\n")]
    L += ["", "## Imo State — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in IM_GEOGRAPHY.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Created 3 Feb 1976 from East-Central State | Statoids; Wikipedia | stated |",
          "| Abia separated 27 Aug 1991 | Statoids; NIPC (Abia); Abia State Government | stated |",
          "| Name; Eastern Heartland; history; Owerri in the Civil War; Ebonyi 1996 | Wikipedia | attributed |",
          "| Borders | Wikipedia; neighbours' sources (Anambra and Abia governments, NIPC Rivers) | stated |",
          "| Peoples, towns, religion, economy, geography | Wikipedia | attributed |", "",
          "## Figures recorded (side by side)", ""] + [f"- Imo — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- One more state page becomes indexable. With it, all 36 states and the FCT have a history page. The sitemap should grow by 1 (3,017 → 3,018).",
          "- The text is original prose, and every claim is sourced or attributed.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_022_imo.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_022_imo_REVIEW.md", "w").write(report())
    print(f"Imo={words(IM_DESCRIPTION) + words(IM_GEOGRAPHY)} statistics={len(STATISTICS)}")
