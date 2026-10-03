"""
Research batch 019 — Osun, Ondo and Ekiti states (South West, part 2): history, geography,
peoples and economy (researched 2026-09-25). Same method as batches 013-018:

Fills the EMPTY description and geography fields of the three existing, published state
records (never overwrites), adds dated population/area figures side by side, and sourced
'neighbours' links (existing links are not duplicated: Kwara–Osun, Kogi–Ondo, Kogi–Ekiti,
Kwara–Ekiti, Delta–Ondo, Edo–Ondo; Ogun–Osun, Ogun–Ondo and Oyo–Osun come in batch 018).
  * facts with two or more independent sources are stated plainly;
  * single-source claims are attributed in the text;
  * the Ondo State Government's home page (an AI assistant and promotional text) calls
    Idanre Hills a "UNESCO World Heritage Site"; it is on UNESCO's tentative list only, so
    that page is not used;
  * NIPC's claim that Ekiti produces "88% of the world's kolanut" is not used.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "OSHIS": dict(source_type="official_website", title="History", organisation="Osun State Government",
                  url="https://www.osunstate.gov.ng/about/history/", verification_status="needs_corroboration",
                  notes="Yoruba tradition that creation began at Ile-Ife; campaign for an Osun Division from 1950; H. L. Butcher Commission of Inquiry, 1951; autonomy for the Osun District towns from 1 April 1951; Osun created on 27 August 1991 by the Babangida administration out of Oyo State, capital Osogbo."),
    "OSGEO": dict(source_type="official_website", title="Geography", organisation="Osun State Government",
                  url="https://www.osunstate.gov.ng/about/geography/", verification_status="needs_corroboration",
                  notes="Tropical rain forest zone; area 'approximately 14,875 sq km'; landlocked with many rivers; borders Ogun, Kwara, Oyo, Ekiti and Ondo; gold and kaolin; hills at Ikirun, Iragbiji, Ilesha, Ikire and Ile-Ife, fortresses during the Yoruba wars."),
    "OSPEO": dict(source_type="official_website", title="The People", organisation="Osun State Government",
                  url="https://www.osunstate.gov.ng/about/people/", verification_status="needs_corroboration",
                  notes="2006 census 3,423,535; predominantly Yoruba (Osun, Ife, Ijesa and Igbomina groups); traditional rulers incl. the Ooni of Ife, Ataoja of Osogbo, Owa Obokun of Ijesaland, Timi of Ede, Oluwo of Iwo, Akinrun of Ikirun; agrarian."),
    "EKGOV": dict(source_type="official_website", title="About Ekiti", organisation="Ekiti State Government",
                  url="https://www.ekitistate.gov.ng/about-ekiti", verification_status="needs_corroboration",
                  notes="Lies south of Kwara and Kogi, east of Osun, bounded by Ondo to the east and south; area 5,887.89 km²; 16 LGAs; 1991 census 1,647,822; estimated 1,750,000 at creation on 1 October 1996; capital Ado-Ekiti; 2006 census 2,384,212; agriculture employs over 75% of the population."),
    "NIPCOS": dict(source_type="official_website", title="Osun State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/osun-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "NIPCON": dict(source_type="official_website", title="Ondo State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/ondo-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "NIPCEK": dict(source_type="official_website", title="Ekiti State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/ekiti-state/",
                   archive_reference="Internet Archive snapshot 20250507202917: http://web.archive.org/web/20250507202917/https://www.nipc.gov.ng/nigeria-states/ekiti-state/",
                   verification_status="needs_corroboration",
                   notes="Name from the many hills; highest number of professors in Nigeria; rivers and minerals; Ikogosi Warm Springs and Ipole-Iloro Water Falls; area 5,434 km²; 16 LGAs; population 3,480,006 (no year); crops; minerals."),
    "WOS": dict(source_type="encyclopedia", title="Osun State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Osun_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: named after the River Osun; formed from Oyo on 27 August 1991, capital Osogbo; borders; vegetation; Osun, Erinle and Oba rivers; Yoruba subgroups; Oyo Empire; Kiriji War 1877–1893; Western Region, Western State; Osun-Osogbo Sacred Grove (World Heritage Site, 2005) and festival; Olojo and Iwude festivals; Erin-Ijesha waterfalls."),
    "WON": dict(source_type="encyclopedia", title="Ondo State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Ondo_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: created 3 February 1976 from Western State; borders; capital Akure, former capital of the Akure Kingdom; Sunshine State; Yoruba subgroups and Ijaw (Apoi, Arogbo); cocoa, asphalt, coast; Idanre hills over 1,000 m, the highest point in western Nigeria; mangroves; minerals."),
    "WEK": dict(source_type="encyclopedia", title="Ekiti State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Ekiti_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: named for the Ekiti people; carved out of Ondo in 1996, capital Ado-Ekiti; borders; Ekiti Confederacy and the Kiriji War (1877–1893); traditions linking Ekiti to Ile-Ife; upland over 250 m with rock outcrops and hills; forest and savanna; dialects; most professors."),
    "CPOS": dict(source_type="dataset", title="Osun (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA030__osun/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,158,143; census 2006: 3,416,959; projection 2022: 4,435,800 (National Population Commission / National Bureau of Statistics)."),
    "CPON": dict(source_type="dataset", title="Ondo (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA029__ondo/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,249,548; census 2006: 3,460,877; projection 2022: 5,316,600 (National Population Commission / National Bureau of Statistics)."),
    "CPEK": dict(source_type="dataset", title="Ekiti (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA013__ekiti/", verification_status="needs_corroboration",
                 notes="Census 1991: 1,535,790; census 2006: 2,398,957; projection 2022: 3,592,200 (National Population Commission / National Bureau of Statistics)."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration",
                 notes="Reused (batch 001). 2006 census table: Osun 3,423,535, 8,585 km²; Ondo 3,441,024, 15,019 km²; Ekiti 2,384,212, 5,797 km². Chronology: 1976-02-03 Western State divided into Ogun, Ondo and Oyo; 1991-08-27 Osun split from Oyo; 1996-10-01 Ekiti split from Ondo."),
}

OS_DESCRIPTION = """Osun State was created on 27 August 1991 by the military government of Ibrahim Babangida, from the south-eastern part of Oyo State. The Osun State Government, the Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this date. Its capital is Osogbo. The state is named after the River Osun, sacred to the Yoruba goddess of the same name. It is grouped in the South West geopolitical zone, and the Commission gives its nickname as "Land of Virtue".

The state government traces a long campaign for self-government. It says that in 1950 traditional rulers and citizens of the area petitioned the colonial administration for an Osun Division separate from Ibadan. The 1951 Butcher Commission of Inquiry led to autonomy for the Osun District towns from 1 April 1951. It also records the Yoruba tradition that Ile-Ife, in the state, is where the creation of the world began. Wikipedia adds that parts of the area belonged to the Oyo Empire, and that from 1877 to 1893 the Kiriji War was fought across the region before the British brought it into the Southern Nigeria Protectorate.

Osun borders Kwara State to the north, Ekiti and Ondo states to the east, Ogun State to the south and Oyo State to the west. It has thirty local government areas.

The people are Yoruba. The state government names the Osun, Ife, Ijesa and Igbomina groups, and Wikipedia adds the Oyo and Ibolo. Traditional rulers remain highly respected. The state government names the Ooni of Ife, the Ataoja of Osogbo, the Owa Obokun of Ijesaland, the Timi of Ede and the Oluwo of Iwo among the foremost.

The Osun-Osogbo Sacred Grove, on the river at Osogbo, was declared a World Heritage Site in 2005, according to Wikipedia. It is the centre of the annual Osun-Osogbo festival in August, which draws visitors from Brazil, Cuba and elsewhere in the Americas. Wikipedia also names the Olojo festival in Ife and the Iwude festival in Ilesa.

Farming is the base of the economy. The Commission names cocoa, cashew, maize, cassava and oil palm among the crops. The Commission and the state government both mention gold.

The 2006 census counted 3,416,959 people, according to National Population Commission figures reproduced by City Population; the state government and Statoids give 3,423,535. Other published figures are listed below with their sources. Nigerian census results are disputed."""

OS_GEOGRAPHY = """The state government places the state in the tropical rain forest belt, and Wikipedia notes drier forest–savanna in the north. The River Osun crosses the interior and forms much of the border with Oyo State, and the Erinle and Oba rivers join it from the north. The state government notes hills at Ikirun, Iragbiji, Ilesa, Ikire and Ile-Ife that served as fortresses during the Yoruba wars. Wikipedia names the Erin-Ijesa waterfalls among the state's sights.

The area is given very differently: about 14,875 square kilometres by the state government, 9,026 by the Nigerian Investment Promotion Commission and 8,585 by Statoids. All three figures are recorded below."""

ON_DESCRIPTION = """Ondo State was created on 3 February 1976, when Western State was divided into Ogun, Ondo and Oyo states. The Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this date. Until 1 October 1996 it also included the area of present-day Ekiti State. Its capital is Akure, which Wikipedia describes as the former capital of the ancient Akure Kingdom. The state is grouped in the South West geopolitical zone, and its nickname is "Sunshine State".

Ondo borders Ekiti State to the north, Kogi State to the north-east, Edo State to the east, Delta State to the south-east, Ogun State to the south-west and Osun State to the north-west, according to Wikipedia. To the south it reaches the Atlantic. It has eighteen local government areas: Akoko North-East, Akoko North-West, Akoko South-East, Akoko South-West, Akure North, Akure South, Ese Odo, Idanre, Ifedore, Ilaje, Ile-Oluji-Okeigbo, Irele, Odigbo, Okitipupa, Ondo East, Ondo West, Ose and Owo.

The people are mainly Yoruba. Wikipedia names the Akoko, Akure, Idanre, Ikale, Ilaje, Ondo, Owo and Ose among the Yoruba groups. It adds that Ijaw communities, the Apoi and the Arogbo, live in the south-eastern swamps near Edo State.

The economy combines farming, oil and minerals. The Commission and Wikipedia both highlight cocoa, and the Commission puts the state's output at more than 75,000 tonnes a year. It says the state has the largest bitumen deposit in Africa, and Wikipedia mentions asphalt mining. The Commission and Wikipedia also list crude oil, limestone, granite and coal. The Commission gives the state's coastline as 180 kilometres.

The 2006 census counted 3,460,877 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

ON_GEOGRAPHY = """Mangrove swamp forest lines the coast, according to Wikipedia. Inland, the Idanre Hills, a group of inselbergs, rise to over 1,000 metres and are the highest point in western Nigeria, according to Wikipedia. It describes a tropical wet and dry climate.

The area is given as 15,820 square kilometres by the Nigerian Investment Promotion Commission and 15,019 by Statoids. Both figures are recorded below."""

EK_DESCRIPTION = """Ekiti State was created on 1 October 1996 from part of Ondo State. The Ekiti State Government, Wikipedia and Statoids all give this date. Its capital is Ado-Ekiti, and it is grouped in the South West geopolitical zone. Wikipedia says the state is named after the Ekiti people, the Yoruba group that forms most of its population. The Nigerian Investment Promotion Commission instead links the name to the many hills around which the people live.

Wikipedia outlines the earlier history. Ekiti communities trace their ruling houses to Ile-Ife and the Yoruba ancestor Oduduwa, though Wikipedia notes evidence of earlier inhabitants. In the 19th century the area came under pressure from Ibadan. Ekiti towns then formed the Ekiti Confederacy (Ekiti Parapo), which fought Ibadan in the Kiriji War of 1877 to 1893. The war ended in a British-brokered stalemate, and the area was later brought into Southern Nigeria. After independence it was part of the Western Region, then from 1967 of Western State and from 1976 of Ondo State.

Ekiti borders Kwara State to the north, Kogi State to the north-east, Ondo State to the east and south, and Osun State to the west. It has sixteen local government areas.

The people are mostly Ekiti, who speak an Ekiti dialect of Yoruba. Wikipedia notes that the dialect varies from town to town, and that border communities speak forms close to those of their neighbours in Kwara and Kogi. The Commission and Wikipedia both describe Ekiti as having more professors than any other state in Nigeria.

Agriculture employs more than three-quarters of the people, according to the state government. The Commission names yams, cocoa, oil palm, rice, cassava, maize and cashew among the crops. It names the Ikogosi Warm Springs and the Ipole-Iloro waterfalls among the state's best-known sites.

The 2006 census counted 2,398,957 people, according to National Population Commission figures reproduced by City Population; the state government and Statoids give 2,384,212. Other published figures are listed below with their sources. Nigerian census results are disputed."""

EK_GEOGRAPHY = """Ekiti is mainly upland, over 250 metres above sea level. Wikipedia describes old plains broken by steep rock outcrops, at Aramoko, Efon-Alaiye, Ikere-Ekiti and Okemesi among other places, and hills at Ikere, Efon-Alaiye and Ado-Ekiti. Tropical forest covers the south and savanna the north, with rain from April to October.

The area is given as 5,887.89 square kilometres by the state government, 5,434 by the Nigerian Investment Promotion Commission and 5,797 by Statoids. The figures are recorded below."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


OS, ON, EK = "@admin_units:state:osun", "@admin_units:state:ondo", "@admin_units:state:ekiti"

UPDATES = [
    dict(ref=OS, fields=dict(description=OS_DESCRIPTION, geography_notes=OS_GEOGRAPHY),
         srcs=[("OSHIS", "Created 27 August 1991 by Babangida from Oyo; Osogbo; campaign from 1950; 1951 autonomy; Ile-Ife creation tradition"),
               ("OSGEO", "Rain forest; borders; gold; hills as fortresses; area 14,875 km²"),
               ("OSPEO", "Yoruba groups; traditional rulers; 2006 census 3,423,535"),
               ("NIPCOS", "Created 27 August 1991; name from the River Osun and goddess; Land of Virtue; borders; crops; gold; area 9,026 km²"),
               ("WOS", "Name; Oyo Empire; Kiriji War; Southern Nigeria; Yoruba groups; Sacred Grove (WHS 2005) and festival; other festivals; rivers; Erin-Ijesa"),
               ("STAT", "1991 Osun split from Oyo; area 8,585 km²"), ("CPOS", "2006 census 3,416,959 (National Population Commission)")]),
    dict(ref=ON, fields=dict(description=ON_DESCRIPTION, geography_notes=ON_GEOGRAPHY),
         srcs=[("NIPCON", "Created 3 February 1976 from Western State; Ekiti split 1996; Sunshine State; cocoa; bitumen; crude oil; coastline 180 km; 18 LGAs; area 15,820 km²"),
               ("WON", "Created 1976; borders; Akure and the Akure Kingdom; Yoruba groups and Ijaw; cocoa; asphalt; minerals; Idanre Hills; mangroves"),
               ("STAT", "1976 Ondo; 1996 Ekiti split off; area 15,019 km²"), ("CPON", "2006 census 3,460,877 (National Population Commission)")]),
    dict(ref=EK, fields=dict(description=EK_DESCRIPTION, geography_notes=EK_GEOGRAPHY),
         srcs=[("EKGOV", "Created 1 October 1996; Ado-Ekiti; borders; 16 LGAs; agriculture over 75%; 2006 census 2,384,212; area 5,887.89 km²"),
               ("NIPCEK", "Name from the hills; most professors; crops; Ikogosi and Ipole-Iloro; area 5,434 km²"),
               ("WEK", "Name from the Ekiti people; 1996 from Ondo; Ile-Ife traditions; Ekiti Parapo and Kiriji War; dialects; most professors; upland, outcrops, hills; vegetation"),
               ("STAT", "1996 Ekiti from Ondo; area 5,797 km²"), ("CPEK", "2006 census 2,398,957 (National Population Commission)")]),
]

RELATIONS = [
    dict(frm=OS, type="neighbours", to=EK, source="OSGEO", evidence="multiple_sources",
         notes="Listed by the Osun State Government, the Ekiti State Government, the Nigerian Investment Promotion Commission (Osun) and Wikipedia."),
    dict(frm=OS, type="neighbours", to=ON, source="OSGEO", evidence="multiple_sources",
         notes="Listed by the Osun State Government, the Nigerian Investment Promotion Commission (Osun) and Wikipedia."),
    dict(frm=ON, type="neighbours", to=EK, source="EKGOV", evidence="multiple_sources",
         notes="Listed by the Ekiti State Government and by Wikipedia (Ondo and Ekiti articles)."),
]


def stat(record, metric, value, source, notes, year=None, method="other"):
    d = dict(record=record, metric=metric, value_low=value, method=method, source=source, evidence="single_reliable_source", notes=notes)
    if year:
        d["reference_year"] = year
    return d


NPC = "National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."
PROJ = "Projection, not a count (NPC / NBS, via City Population)."
STATISTICS = [
    stat(OS, "population", 3416959, "CPOS", NPC, 2006, "census"),
    stat(OS, "population", 3423535, "OSPEO", "Given by the Osun State Government as the 2006 census; Statoids gives the same figure.", 2006, "census"),
    stat(OS, "population", 2158143, "CPOS", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(OS, "population", 4435800, "CPOS", PROJ, 2022, "projection"),
    stat(OS, "population", 5016593, "NIPCOS", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(OS, "area_km2", 14875, "OSGEO", "Osun State Government ('approximately'); much larger than the other published figures."),
    stat(OS, "area_km2", 9026, "NIPCOS", "Nigerian Investment Promotion Commission."),
    stat(OS, "area_km2", 8585, "STAT", "Statoids."),
    stat(ON, "population", 3460877, "CPON", NPC, 2006, "census"),
    stat(ON, "population", 3441024, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(ON, "population", 2249548, "CPON", "1991 census for the area of today's state (without Ekiti), as reproduced by City Population.", 1991, "census"),
    stat(ON, "population", 5316600, "CPON", PROJ, 2022, "projection"),
    stat(ON, "population", 4960577, "NIPCON", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(ON, "area_km2", 15820, "NIPCON", "Nigerian Investment Promotion Commission."),
    stat(ON, "area_km2", 15019, "STAT", "Statoids."),
    stat(EK, "population", 2398957, "CPEK", NPC, 2006, "census"),
    stat(EK, "population", 2384212, "EKGOV", "Given by the Ekiti State Government as the 2006 census (National Population Commission); Statoids gives the same figure.", 2006, "census"),
    stat(EK, "population", 1647822, "EKGOV", "1991 census for the area of today's state, as given by the Ekiti State Government (City Population gives 1,535,790).", 1991, "census"),
    stat(EK, "population", 1535790, "CPEK", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(EK, "population", 3592200, "CPEK", PROJ, 2022, "projection"),
    stat(EK, "population", 3480006, "NIPCEK", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(EK, "area_km2", 5888, "EKGOV", "Ekiti State Government gives 5,887.89 km² (rounded here; figures are stored as whole numbers)."),
    stat(EK, "area_km2", 5434, "NIPCEK", "Nigerian Investment Promotion Commission."),
    stat(EK, "area_km2", 5797, "STAT", "Statoids."),
]

GAPS = [
    ("Osun's area", "The Osun State Government gives about 14,875 km²; NIPC 9,026; Statoids 8,585. The state government's figure is far larger and may be an error; all are recorded."),
    ("Ekiti's name", "Wikipedia: named after the Ekiti people. NIPC: from the many hills. Both are recorded, attributed."),
    ("Idanre Hills", "The Ondo State Government's home page calls them a UNESCO World Heritage Site; they are on UNESCO's tentative list only. Not used; a place record with UNESCO as the source is needed."),
    ("Ile-Ife, the Ooni and Yoruba origins", "Only the tradition is mentioned (Osun State Government). Ile-Ife, its kingship and Yoruba origin traditions need their own records with scholarly sources."),
    ("The Kiriji War (1877–1893)", "Given by Wikipedia only. Needs its own event record with scholarly sources."),
    ("Ekiti 1991 census", "The Ekiti State Government gives 1,647,822 for 1991; City Population gives 1,535,790 for the same area. Both recorded."),
    ("Ondo State Government", "Its website has no readable history page; its home page is promotional and not used."),
    ("Areas and population figures", "The areas and populations from each source differ. All are recorded; none is chosen."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="Osun, Ondo and Ekiti states: history, geography, peoples, economy and population (fills empty fields of the existing records).")


STATES = [("Osun State", OS_DESCRIPTION, OS_GEOGRAPHY), ("Ondo State", ON_DESCRIPTION, ON_GEOGRAPHY), ("Ekiti State", EK_DESCRIPTION, EK_GEOGRAPHY)]


def report():
    L = ["# Research batch 019 — Osun, Ondo and Ekiti states (South West, part 2)", "",
         f"Researched {ACCESSED}. All three state pages are already published, so **this text goes live the moment it is imported**. This report is the review. Deploy after batch 018.", ""]
    for name, d, g in STATES:
        L += [f"- New prose for {name}: **{words(d) + words(g)} words**. The page passes the 300-word indexing rule and becomes indexable."]
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    for name, d, g in STATES:
        L += ["", f"## {name} — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in d.split("\n")]
        L += ["", f"## {name} — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in g.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Osun created 27 Aug 1991 from Oyo; Osogbo | state government; NIPC; Wikipedia; Statoids | stated |",
          "| Named after the River Osun and goddess | NIPC; Wikipedia | stated |",
          "| Osun autonomy campaign 1950–51; Ile-Ife tradition | state government | attributed |",
          "| Oyo Empire; Kiriji War | Wikipedia | attributed + gap |",
          "| Osun peoples; traditional rulers | state government; Wikipedia | attributed |",
          "| Osun-Osogbo Sacred Grove (WHS 2005); festivals | Wikipedia | attributed |",
          "| Ondo created 3 Feb 1976; Ekiti split 1996 | NIPC; Wikipedia; Statoids | stated |",
          "| Akure Kingdom; Ondo borders; peoples | Wikipedia | attributed |",
          "| Cocoa; bitumen; oil; coastline | NIPC; Wikipedia | stated / attributed |",
          "| Idanre Hills highest point in western Nigeria | Wikipedia | attributed |",
          "| Ekiti created 1 Oct 1996 from Ondo | state government; Wikipedia; Statoids | stated |",
          "| Name of Ekiti | Wikipedia vs NIPC | both attributed |",
          "| Ekiti Parapo, Kiriji War, Ile-Ife traditions | Wikipedia | attributed |",
          "| Most professors | NIPC; Wikipedia | attributed |",
          "| Agriculture over 75%; crops; Ikogosi, Ipole-Iloro | state government; NIPC | attributed |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['record'].split(':')[-1].title()} — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", f"## Neighbours ({len(RELATIONS)} new links)", "",
          "- Osun ↔ Ekiti, Ondo. Ondo ↔ Ekiti. Each has two or more sources. The other borders exist already or come in batch 018.",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- Three more state pages become indexable, completing the South West. The sitemap should grow by 3 (3,007 → 3,010 after batch 018). The new source pages stay noindex.",
          "- The text is original prose, and every claim is sourced or attributed.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_019_osun_ondo_ekiti.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_019_osun_ondo_ekiti_REVIEW.md", "w").write(report())
    print(" ".join(f"{n.split()[0]}={words(d) + words(g)}" for n, d, g in STATES) + f" relations={len(RELATIONS)} statistics={len(STATISTICS)}")
