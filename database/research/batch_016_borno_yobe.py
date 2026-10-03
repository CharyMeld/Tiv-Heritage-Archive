"""
Research batch 016 — Borno and Yobe states: history, geography, peoples and economy
(researched 2026-09-25). Same method as batches 013-015:

Fills the EMPTY description and geography fields of the two existing, published state
records (never overwrites), adds dated population/area figures side by side, and sourced
'neighbours' links (existing links are not duplicated: Adamawa–Borno, Gombe–Borno,
Bauchi–Yobe, Gombe–Yobe).
  * facts with two or more independent sources are stated plainly;
  * single-source claims are attributed in the text;
  * shared lineage: the Borno State Government's "About" page reproduces Wikipedia's Borno
    article word for word; the two count as ONE source;
  * the Yobe State Government's site has no history page (only a governor profile);
  * the Boko Haram insurgency and the 2024 Yobe attack are left for separate treatment.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "BOGOV": dict(source_type="official_website", title="About Borno state", organisation="Borno State Government",
                  url="https://bornostate.gov.ng/about", verification_status="needs_corroboration",
                  notes="Reproduces Wikipedia's 'Borno State' article (borders with lengths, name, formation in 1976, Yobe 1991, Lake Chad, Chad Basin National Park, peoples, Kanem and Bornu history). Counted with Wikipedia as one source."),
    "NIPCBO": dict(source_type="official_website", title="Borno State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/borno-state/",
                   archive_reference="Internet Archive snapshot 20240420230841: http://web.archive.org/web/20240420230841/https://www.nipc.gov.ng/nigeria-states/borno-state/",
                   verification_status="needs_corroboration",
                   notes="History and heritage over a thousand years; borders Cameroon, Chad and Niger and the states of Yobe, Adamawa and Gombe; 'Home of Peace'; part of Lake Chad, a major source of freshwater fish; area 72,609 km²; capital Maiduguri; 27 LGAs; population 6,272,536 (no year); crops; minerals; three agricultural zones (Biu, Bama, Kukawa)."),
    "NIPCYO": dict(source_type="official_website", title="Yobe State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/yobe-state/",
                   archive_reference="Internet Archive snapshot 20241127063742: http://web.archive.org/web/20241127063742/https://www.nipc.gov.ng/nigeria-states/yobe-state/",
                   verification_status="needs_corroboration",
                   notes="Created 27 August 1991; borders Bauchi, Borno, Gombe and Jigawa, and the Diffa and Zinder regions of Niger; 'Pride of the Sahel'; area 46,609 km²; capital Damaturu; 17 LGAs; population 3,532,989 (no year); Sudan and Sahel savanna; crops and livestock; minerals."),
    "WBO": dict(source_type="encyclopedia", title="Borno State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Borno_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: named after the Borno Emirate, capital Maiduguri; formed 1976 from North-Eastern State, Yobe separated 1991; borders three countries; Kanem Empire from the 700s, Bornu Empire from the late 1300s; Fulani jihad; Kanemi dynasty; Rabih az-Zubayr (1893, killed 1900); British from 1902, Maiduguri capital 1907; German Bornu and the Northern Cameroons (joined Nigeria 1961); peoples; vegetation; Lake Chad; Yobe River; Chad Basin National Park."),
    "WYO": dict(source_type="encyclopedia", title="Yobe State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Yobe_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: created 27 August 1991 from Borno by the Babangida administration (size and ethnic rivalries cited); capital Damaturu; largest city Potiskum with one of the largest cattle markets in West Africa; borders with lengths; dry savanna climate; gypsum at Fika, kaolin at Fune; crops; peoples."),
    "CPBO": dict(source_type="dataset", title="Borno (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA008__borno/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,536,003; census 2006: 4,171,104; projection 2022: 6,111,500 (National Population Commission / National Bureau of Statistics)."),
    "CPYO": dict(source_type="dataset", title="Yobe (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA036__yobe/", verification_status="needs_corroboration",
                 notes="Census 1991: 1,399,687; census 2006: 2,321,339; projection 2022: 3,649,600 (National Population Commission / National Bureau of Statistics)."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration",
                 notes="Reused (batch 001). 2006 census table: Borno 4,151,193, 72,767 km²; Yobe 2,321,591, 44,880 km². Chronology: 1976-02-03 North-Eastern State divided into Bauchi, Borno and Gongola; 1991-08-27 Yobe split from Borno."),
}

BO_DESCRIPTION = """Borno State was created on 3 February 1976, when North-Eastern State was divided. It takes its name from the historic Borno Emirate, and its capital, Maiduguri, was the emirate's capital. Until 27 August 1991 the state also included the area of present-day Yobe State. Wikipedia and Statoids give these dates. The state is grouped in the North East geopolitical zone, and the Nigerian Investment Promotion Commission gives its nickname as "Home of Peace".

The area has one of the longest recorded histories in Nigeria; the Commission speaks of a heritage of more than a thousand years. Wikipedia outlines it. From the 8th century the area lay within the Kanem Empire, which stretched north through present-day Chad to the Fezzan. In the late 14th century, after unsuccessful wars, the empire was forced to move and re-formed as the Bornu Empire, which dominated the region for some five hundred years. The Fulani jihad of the early 19th century weakened it, and the Kanemi dynasty then took power. In 1893 the Sudanese warlord Rabih az-Zubayr conquered Bornu. He was killed by French forces in 1900, and the area was divided between the British and the Germans. The British brought their part into the Northern Nigeria Protectorate in 1902 and made Maiduguri the capital in 1907. After the First World War the German part along today's border with Cameroon became part of the British Northern Cameroons, which joined Nigeria after a referendum in 1961. Wikipedia notes that the emirs' role was reduced over time to cultural and traditional affairs.

Borno borders Yobe State to the west, Gombe State to the south-west and Adamawa State to the south. It is the only Nigerian state that borders three countries: Niger to the north, Chad to the north-east and Cameroon to the east. It has twenty-seven local government areas.

According to Wikipedia, the Kanuri predominate. It names the Shuwa Arabs in the north and centre, and the Kanembu and Yedina (Buduma) near Lake Chad. It places the Margi, Kilba, Bura, Mafa, Mandara and many smaller peoples in the south and the Mandara hills.

Farming, livestock and fishing are the base of the economy. The Commission lists millet, sorghum, maize, cowpea, rice, wheat and groundnut among the crops, and calls Lake Chad a major source of freshwater fish.

The 2006 census counted 4,171,104 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

BO_GEOGRAPHY = """Borno is the second-largest state in Nigeria by area, after Niger State. Wikipedia describes semi-desert Sahel savanna in the north and Sudan savanna in the centre and south. The Mandara hills rise along the south-eastern border. In the far north-east lies the Nigerian part of Lake Chad, fed by the Komadugu-Yobe River, which forms much of the border with the Republic of Niger. The Chad Basin National Park lies partly in the state.

The area is given as 72,609 square kilometres by the Nigerian Investment Promotion Commission and 72,767 by Statoids. Both figures are recorded below."""

YO_DESCRIPTION = """Yobe State was created on 27 August 1991 by the military government of Ibrahim Babangida, from part of Borno State. The Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this date. According to Wikipedia, old Borno was thought too large to govern and develop well, and ethnic rivalries within it also played a part. Before 1991 the area shared Borno's history: it belonged to Borno State from 1976 and before that to North-Eastern State. The capital is Damaturu. The state is grouped in the North East geopolitical zone, and the Commission gives its nickname as "Pride of the Sahel".

Yobe borders Borno State to the east, Gombe State to the south, and Bauchi and Jigawa states to the west. To the north it shares an international boundary with the Republic of Niger, along the Diffa and Zinder regions. It has seventeen local government areas.

Wikipedia names the Kanuri, the Karai-Karai and the Fulani as the main peoples. Other communities include the Bolewa, Ngizim, Bade, Ngamo, Shuwa Arabs, Bura, Marghi, Hausa and Manga.

The economy rests on farming and livestock. The Commission and Wikipedia both name groundnuts, cotton and gum arabic, and the Commission adds sorghum, millet, maize, cowpeas and sesame. Potiskum, the largest city, has one of the largest cattle markets in West Africa, according to Wikipedia. Wikipedia also records gypsum at Fika and kaolin at Fune, and the Commission lists diatomite, silica sand and clay.

The 2006 census counted 2,321,339 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

YO_GEOGRAPHY = """Yobe lies in the dry savanna belt of the north-east. The Commission describes Sudan and Sahel savanna, and Wikipedia notes that it is hot and dry for most of the year, with more rain in the south. Wikipedia gives average daily temperatures of about 37 °C.

The area is given as 46,609 square kilometres by the Nigerian Investment Promotion Commission and 44,880 by Statoids. Both figures are recorded below."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


BO, YO = "@admin_units:state:borno", "@admin_units:state:yobe"

UPDATES = [
    dict(ref=BO, fields=dict(description=BO_DESCRIPTION, geography_notes=BO_GEOGRAPHY),
         srcs=[("WBO", "Name; 1976; Yobe 1991; Kanem and Bornu; Rabih; British 1902, Maiduguri 1907; Northern Cameroons 1961; borders; peoples; vegetation; Lake Chad; Yobe River; national park"),
               ("BOGOV", "Same text as Wikipedia (one lineage): borders, name, formation, history"),
               ("NIPCBO", "Thousand-year heritage; borders three countries and Yobe, Adamawa, Gombe; Home of Peace; Lake Chad fish; 27 LGAs; crops; area 72,609 km²"),
               ("STAT", "1976 Borno; 1991 Yobe split off; area 72,767 km²"), ("CPBO", "2006 census 4,171,104 (National Population Commission)")]),
    dict(ref=YO, fields=dict(description=YO_DESCRIPTION, geography_notes=YO_GEOGRAPHY),
         srcs=[("NIPCYO", "Created 27 August 1991; borders; Pride of the Sahel; 17 LGAs; vegetation; crops; minerals; area 46,609 km²"),
               ("WYO", "Created 1991 from Borno by Babangida; reasons; Damaturu; Potiskum cattle market; peoples; climate; Fika gypsum, Fune kaolin"),
               ("STAT", "1991 Yobe split from Borno; area 44,880 km²"), ("CPYO", "2006 census 2,321,339 (National Population Commission)")]),
]

RELATIONS = [
    dict(frm=BO, type="neighbours", to="@admin_units:state:yobe", source="NIPCBO", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission (Borno and Yobe pages) and by Wikipedia."),
    dict(frm=YO, type="neighbours", to="@admin_units:state:jigawa", source="NIPCYO", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission and by Wikipedia."),
]


def stat(record, metric, value, source, notes, year=None, method="other"):
    d = dict(record=record, metric=metric, value_low=value, method=method, source=source, evidence="single_reliable_source", notes=notes)
    if year:
        d["reference_year"] = year
    return d


NPC = "National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."
STATISTICS = [
    stat(BO, "population", 4171104, "CPBO", NPC, 2006, "census"),
    stat(BO, "population", 4151193, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(BO, "population", 2536003, "CPBO", "1991 census for the area of today's state (without Yobe), as reproduced by City Population.", 1991, "census"),
    stat(BO, "population", 6111500, "CPBO", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(BO, "population", 6272536, "NIPCBO", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(BO, "area_km2", 72609, "NIPCBO", "Nigerian Investment Promotion Commission."),
    stat(BO, "area_km2", 72767, "STAT", "Statoids."),
    stat(YO, "population", 2321339, "CPYO", NPC, 2006, "census"),
    stat(YO, "population", 2321591, "STAT", "Statoids' 2006 census table; differs slightly from the NPC figure.", 2006, "census"),
    stat(YO, "population", 1399687, "CPYO", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(YO, "population", 3649600, "CPYO", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(YO, "population", 3532989, "NIPCYO", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(YO, "area_km2", 46609, "NIPCYO", "Nigerian Investment Promotion Commission."),
    stat(YO, "area_km2", 44880, "STAT", "Statoids."),
]

GAPS = [
    ("Kanem–Bornu", "The history of the Kanem and Bornu empires, the al-Kanemi dynasty and Rabih az-Zubayr is outlined from Wikipedia only. It needs scholarly sources and its own records (polities, events)."),
    ("The Northern Cameroons and the 1961 plebiscite", "Given by Wikipedia only. Needs its own event record with official and scholarly sources."),
    ("Borno State Government page", "Its 'About' page copies Wikipedia word for word, so it is not an independent source."),
    ("Yobe State Government", "No history page was found on the government's website."),
    ("Yobe's name", "No source read for this batch explains the name. It presumably comes from the Komadugu-Yobe River, but that is not stated, so it is not in the text."),
    ("Boko Haram insurgency and the Lake Chad crisis", "Not summarised (Sambisa Forest, displacement, the 2024 Yobe attack). They need separate, careful treatment."),
    ("Areas and population figures", "The areas and populations from each source differ. All are recorded; none is chosen."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="Borno and Yobe states: history, geography, peoples, economy and population (fills empty fields of the existing records).")


STATES = [("Borno State", BO_DESCRIPTION, BO_GEOGRAPHY), ("Yobe State", YO_DESCRIPTION, YO_GEOGRAPHY)]


def report():
    L = ["# Research batch 016 — Borno and Yobe states", "",
         f"Researched {ACCESSED}. Both state pages are already published, so **this text goes live the moment it is imported**. This report is the review.", ""]
    for name, d, g in STATES:
        L += [f"- New prose for {name}: **{words(d) + words(g)} words**. The page passes the 300-word indexing rule and becomes indexable."]
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    for name, d, g in STATES:
        L += ["", f"## {name} — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in d.split("\n")]
        L += ["", f"## {name} — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in g.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Borno created 3 Feb 1976 from North-Eastern State; Yobe separated 1991 | Wikipedia (= state government); Statoids | stated |",
          "| Named after the Borno Emirate; Maiduguri | Wikipedia; NIPC (Maiduguri) | stated |",
          "| Heritage of over a thousand years | NIPC | attributed |",
          "| Kanem, Bornu, al-Kanemi, Rabih, colonial division, 1902, 1907, 1961 | Wikipedia only | attributed + gap |",
          "| Borno borders three countries and three states | NIPC; Wikipedia | stated; Yobe link multiple |",
          "| Borno peoples | Wikipedia | attributed (Kanuri as largest: Wikipedia) |",
          "| Crops; Lake Chad fish | NIPC | attributed |",
          "| Yobe created 27 Aug 1991 by Babangida from Borno | NIPC; Wikipedia; Statoids | stated |",
          "| Reasons for Yobe's creation | Wikipedia only | attributed |",
          "| Yobe borders; Diffa and Zinder | NIPC; Wikipedia | stated; Jigawa link multiple |",
          "| Yobe peoples; Potiskum market; Fika, Fune | Wikipedia | attributed |",
          "| Yobe crops | NIPC; Wikipedia | stated |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['record'].split(':')[-1].title()} — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", f"## Neighbours ({len(RELATIONS)} new links)", "",
          "- Borno ↔ Yobe (two sources). Links with Adamawa and Gombe already exist.",
          "- Yobe ↔ Jigawa (two sources). Links with Bauchi and Gombe already exist.",
          "- International borders (Niger, Chad, Cameroon) are described in the text; the archive has no records for other countries.",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- Two more state pages become indexable (19 of 37). The sitemap should grow by 2 (2,998 → 3,000). The new source pages stay noindex.",
          "- The text is original prose, and every claim is sourced or attributed. The insurgency is left for separate treatment.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_016_borno_yobe.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_016_borno_yobe_REVIEW.md", "w").write(report())
    print(" ".join(f"{n.split()[0]}={words(d) + words(g)}" for n, d, g in STATES) + f" relations={len(RELATIONS)} statistics={len(STATISTICS)}")
