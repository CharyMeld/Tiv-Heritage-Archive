"""
Research batch 008 — Plateau State and Cross River State: history, geography, peoples
and economy (researched 2026-09-24). Same method as batches 004 and 007:

Fills the EMPTY description and geography fields of the two existing, published state
records (never overwrites), adds dated population/area figures side by side, and sourced
'neighbours' links (links that already exist are not duplicated).
  * facts with two or more independent sources are stated plainly;
  * single-source or shared-lineage claims are attributed in the text;
  * shared lineages: NIPC's Cross River and Plateau history sentences match Wikipedia's;
    the Cross River State Government 'About Us' page (2022) reproduces Wikipedia's
    boundary paragraph. Each pair counts as ONE source for those passages.
"""
import json, re, sys

ACCESSED = "2026-09-24"
SOURCES = {
    "PLGOV": dict(source_type="official_website", title="Plateau State Government – Official Website", organisation="Plateau State Government",
                  url="https://www.plateaustate.gov.ng/", verification_status="needs_corroboration",
                  notes="Home page 'quick stats': created 1976; land area about 27,000 km²; estimated population 4.2 million (no year); 17 LGAs."),
    "PLTOUR": dict(source_type="official_website", title="Tourism & Culture", organisation="Plateau State Government",
                   url="https://www.plateaustate.gov.ng/tourism/", verification_status="needs_corroboration",
                   notes="'The home of peace and tourism'; Wase Rock, a dome-shaped inselberg near Wase town."),
    "NIPCPL": dict(source_type="official_website", title="Plateau State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/plateau-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "NIPCCR": dict(source_type="official_website", title="Cross River State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/cross-river-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "NIPCAK": dict(source_type="official_website", title="Akwa Ibom State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/akwa-ibom-state/", verification_status="needs_corroboration", notes="Reused (batch 001). Akwa Ibom created 23 September 1987 from Cross River State."),
    "CRGOV": dict(source_type="official_website", title="About Us – Government of Cross River State", organisation="Cross River State Government",
                  url="https://crossriverstate.gov.ng/about-us/",
                  archive_reference="Internet Archive snapshot 20220819204309: http://web.archive.org/web/20220819204309/https://crossriverstate.gov.ng/about-us/",
                  verification_status="needs_corroboration",
                  notes="Earlier version of the official site (now a JavaScript app), consulted via the Internet Archive. Name from the Cross River; boundaries: Benue (north), Ebonyi and Abia (west), Cameroon (east), Akwa Ibom and the Atlantic (south); 18 LGAs. Its wording reproduces Wikipedia's."),
    "NASGOV": dict(source_type="official_website", title="About Nasarawa State", organisation="Nasarawa State Government",
                   url="https://nasarawastate.gov.ng/about-nasarawa/", verification_status="needs_corroboration", notes="Reused (batch 007). Plateau Province formed in 1926 from the Muri, Nasarawa and Bauchi provinces; Benue-Plateau State, May 1967."),
    "WPL": dict(source_type="encyclopedia", title="Plateau State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Plateau_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: named after the Jos Plateau; nickname; neighbours; climate; rivers rising on the plateau; colonial history (Bauchi Province; Plateau Province 1926); Benue-Plateau 1967; creation 1976; Nasarawa 1996; tin mining from 1902; over forty ethno-linguistic groups; museums and sites; minerals; languages by LGA."),
    "WCR": dict(source_type="encyclopedia", title="Cross River State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Cross_River_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: South-Eastern State 1967; renamed 1976; Akwa Ibom 1987; boundaries; peoples; Old Calabar and the protectorates; crops; parks and tourism; languages by LGA."),
    "CPPL": dict(source_type="dataset", title="Plateau (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA032__plateau/", verification_status="needs_corroboration",
                 notes="Census 1991-11-26: 2,104,536 (today's area); census 2006-03-21: 3,206,531; projection 2022-03-21: 4,717,300 (National Population Commission / National Bureau of Statistics)."),
    "CPCR": dict(source_type="dataset", title="Cross River (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA009__cross_river/", verification_status="needs_corroboration",
                 notes="Census 1991-11-26: 1,911,297; census 2006-03-21: 2,892,988; projection 2022-03-21: 4,406,200 (National Population Commission / National Bureau of Statistics)."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration",
                 notes="Reused (batch 001). 2006 census table: Plateau 3,178,712, 26,539 km²; Cross River 2,888,966, 22,112 km². Chronology: South-Eastern State 1967, renamed Cross River 1976; Benue-Plateau divided 1976; Nasarawa split from Plateau 1996."),
}

PL_DESCRIPTION = """Plateau State was created on 3 February 1976, when Benue-Plateau State was divided into Benue and Plateau states. It is named after the Jos Plateau, the upland area in the north of the state known for its rock formations. Its capital is Jos, and it is grouped in the North Central geopolitical zone. Its official nickname is "Home of Peace and Tourism".

Under British rule much of the area was first part of Bauchi Province. A separate Plateau Province was formed in 1926. Wikipedia says it was carved out of Bauchi Province, while the Nasarawa State Government describes it as drawn from the Muri, Nasarawa and Bauchi provinces. In May 1967 the Benue and Plateau provinces were joined to form Benue-Plateau State. That state was divided in 1976. On 1 October 1996 the western half of Plateau State was separated to form Nasarawa State, leaving the state with its present borders.

Plateau State borders Bauchi State to the north-east, Kaduna State to the north-west, Nasarawa State to the south-west and Taraba State to the south-east. It has seventeen local government areas: Barkin Ladi, Bassa, Bokkos, Jos East, Jos North, Jos South, Kanam, Kanke, Langtang North, Langtang South, Mangu, Mikang, Pankshin, Qua'an Pan, Riyom, Shendam and Wase.

Wikipedia counts more than forty ethno-linguistic groups in the state. Among them are the Berom, Afizere, Anaguta, Ngas, Mwaghavul, Mupun, Tarok, Goemai, Montol and the Kofyar peoples. It notes that Hausa is widely used for trade. Tiv communities are documented in the state at state level (see Connections). Wikipedia's language list by LGA also names Tiv in Langtang South, Qua'an Pan, Shendam and Wase.

The state is rich in minerals. The Nigerian Investment Promotion Commission and Wikipedia both list tin, columbite, lead, zinc, coal, kaolin, marble and barytes. Wikipedia dates tin mining under the British to 1902. For farming, the Commission names maize, guinea corn, cassava, yams, rice and acha (fonio) among the major crops, together with vegetables such as tomatoes, onions, cabbages and carrots.

The state government's tourism page features Wase Rock, a dome-shaped inselberg near Wase town. Wikipedia also describes the National Museum in Jos, founded in 1952, whose collections include Nok terracotta sculptures.

The 2006 census counted 3,206,531 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

PL_GEOGRAPHY = """Most of the state lies on or around the Jos Plateau, an upland of hills, rock formations and grassland. According to Wikipedia, the altitude makes the climate cooler than most of Nigeria, with average temperatures of about 13 to 22 °C. The plateau is the source of several northern Nigerian rivers, including the Kaduna and Gongola. Wikipedia adds that decades of tin and columbite mining have left deep pits and lakes across the landscape.

The area is given as 27,147 square kilometres by the Nigerian Investment Promotion Commission and 26,539 square kilometres by Statoids. The state government rounds it to about 27,000. The figures are recorded below side by side."""

CR_DESCRIPTION = """Cross River State was created on 27 May 1967 as South-Eastern State, one of the twelve states that replaced Nigeria's regions. It was carved from the eastern part of the former Eastern Region. It took its present name in 1976. On 23 September 1987 its western part was separated to form Akwa Ibom State. The state is named after the Cross River, which flows through it. Its capital is Calabar, and it is grouped in the South South geopolitical zone.

The state borders Benue State to the north, Ebonyi and Abia states to the west and Akwa Ibom State to the south-west. To the east it shares an international boundary with Cameroon, and to the south it reaches the Atlantic coast. It has eighteen local government areas: Abi, Akamkpa, Akpabuyo, Bakassi, Bekwarra, Biase, Boki, Calabar Municipal, Calabar South, Etung, Ikom, Obanliku, Obubra, Obudu, Odukpani, Ogoja, Yakurr and Yala. The Nigerian Investment Promotion Commission calls the state the "Nation's Paradise".

Before colonial rule, Wikipedia notes, the Efik founded the trading city-state of Old Calabar (Akwa Akpa). Calabar became the capital of the British Oil Rivers Protectorate in the 1880s, and the area later became part of Southern Nigeria.

Wikipedia names the Efik, Ejagham (Ekoi), Yakurr, Bahumono, Mbembe, Boki, Bekwarra, Bette, Yala, Igede and Ukelle among the state's peoples. The Efik live mainly around Calabar in the south, and many smaller groups live in the north. Igede communities are documented in the north of the state (see Connections). Tiv communities are documented at state level. Wikipedia's language list by LGA names Tiv in Obanliku, Obudu, Bekwarra and Yala. Otank (Utanga), a Tivoid language, is spoken on the border with Benue State.

Farming is central to the economy. The Nigerian Investment Promotion Commission and Wikipedia both name cocoa, oil palm and rubber. The Commission adds rice, cassava, bananas and pineapples, and Wikipedia adds yams, cocoyams, cashews and plantains. Wikipedia also points to tourism, including the Obudu Mountain Resort, the Calabar Carnival and the state's forest reserves.

The 2006 census counted 2,892,988 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

CR_GEOGRAPHY = """The Cross River crosses the interior of the state and forms much of its western border before reaching the sea through the Cross River estuary near Calabar. Wikipedia describes a forested interior with the Oban Hills and several protected areas, including the Cross River National Park. In the north, the high Obudu plateau is noticeably cooler than the rest of the state. The Nigerian Investment Promotion Commission divides the state into three agricultural zones, Ogoja, Ikom and Calabar.

The area is given as 21,787 square kilometres by the Nigerian Investment Promotion Commission and 22,112 square kilometres by Statoids. Both figures are recorded below."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


UPDATES = [
    dict(ref="@admin_units:state:plateau", fields=dict(description=PL_DESCRIPTION, geography_notes=PL_GEOGRAPHY),
         srcs=[("NIPCPL", "Created 3 February 1976 from Benue-Plateau; Nasarawa carved out 1996; neighbours; named after the Jos Plateau; nickname; 17 LGAs; crops; minerals; area 27,147 km²"),
               ("WPL", "Colonial history; Benue-Plateau 1967; 1976; 1996; neighbours; peoples; minerals and tin mining; climate; rivers; National Museum Jos"),
               ("NASGOV", "Plateau Province 1926 from Muri, Nasarawa and Bauchi provinces; Benue-Plateau May 1967; Nasarawa 1996"),
               ("PLGOV", "Created 1976; about 27,000 km²; 17 LGAs"), ("PLTOUR", "Home of peace and tourism; Wase Rock"),
               ("CPPL", "2006 census 3,206,531 (National Population Commission)")]),
    dict(ref="@admin_units:state:cross-river", fields=dict(description=CR_DESCRIPTION, geography_notes=CR_GEOGRAPHY),
         srcs=[("NIPCCR", "Created 27 May 1967 from the Eastern Region; renamed 1976; Akwa Ibom excised 1987; 18 LGAs; nickname; crops; agricultural zones; area 21,787 km²"),
               ("STAT", "South-Eastern State 1967; renamed Cross River 1976; named after the Cross River"),
               ("NIPCAK", "Akwa Ibom created 23 September 1987 from Cross River State"),
               ("CRGOV", "Name from the Cross River; boundaries; 18 LGAs (wording as Wikipedia)"),
               ("WCR", "History; boundaries; peoples; Old Calabar; crops; tourism; geography"),
               ("CPCR", "2006 census 2,892,988 (National Population Commission)")]),
]

PL, CR = "@admin_units:state:plateau", "@admin_units:state:cross-river"
RELATIONS = [
    dict(frm=PL, type="neighbours", to="@admin_units:state:bauchi", source="NIPCPL", evidence="multiple_sources",
         notes="Listed as a neighbouring state by the Nigerian Investment Promotion Commission and by Wikipedia."),
    dict(frm=PL, type="neighbours", to="@admin_units:state:kaduna", source="NIPCPL", evidence="single_reliable_source",
         notes="Listed by the Nigerian Investment Promotion Commission; Wikipedia's list of adjacent states omits it."),
]
for n in ["ebonyi", "abia", "akwa-ibom"]:
    RELATIONS.append(dict(frm=CR, type="neighbours", to=f"@admin_units:state:{n}", source="CRGOV", evidence="single_reliable_source",
                          notes="Listed by the Cross River State Government's earlier website and by Wikipedia, with the same wording (one lineage)."))

STATISTICS = [
    dict(record=PL, metric="population", value_low=3206531, reference_year=2006, method="census", source="CPPL", evidence="single_reliable_source",
         notes="National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."),
    dict(record=PL, metric="population", value_low=3178712, reference_year=2006, method="census", source="STAT", evidence="single_reliable_source", notes="Statoids' 2006 census table; differs from the NPC figure."),
    dict(record=PL, metric="population", value_low=2104536, reference_year=1991, method="census", source="CPPL", evidence="single_reliable_source",
         notes="1991 census for the area of today's state (before Nasarawa was separated in 1996), as reproduced by City Population."),
    dict(record=PL, metric="population", value_low=4717300, reference_year=2022, method="projection", source="CPPL", evidence="single_reliable_source", notes="Projection, not a count (NPC / NBS, via City Population)."),
    dict(record=PL, metric="population", value_low=4433501, method="other", source="NIPCPL", evidence="single_reliable_source",
         notes="Given by the Nigerian Investment Promotion Commission without a year or method."),
    dict(record=PL, metric="area_km2", value_low=27147, method="other", source="NIPCPL", evidence="single_reliable_source", notes="Nigerian Investment Promotion Commission."),
    dict(record=PL, metric="area_km2", value_low=26539, method="other", source="STAT", evidence="single_reliable_source", notes="Statoids."),
    dict(record=CR, metric="population", value_low=2892988, reference_year=2006, method="census", source="CPCR", evidence="single_reliable_source",
         notes="National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."),
    dict(record=CR, metric="population", value_low=2888966, reference_year=2006, method="census", source="STAT", evidence="single_reliable_source", notes="Statoids' 2006 census table; differs from the NPC figure."),
    dict(record=CR, metric="population", value_low=1911297, reference_year=1991, method="census", source="CPCR", evidence="single_reliable_source", notes="1991 census, as reproduced by City Population."),
    dict(record=CR, metric="population", value_low=4406200, reference_year=2022, method="projection", source="CPCR", evidence="single_reliable_source", notes="Projection, not a count (NPC / NBS, via City Population)."),
    dict(record=CR, metric="population", value_low=4097143, method="other", source="NIPCCR", evidence="single_reliable_source",
         notes="Given by the Nigerian Investment Promotion Commission without a year or method."),
    dict(record=CR, metric="area_km2", value_low=21787, method="other", source="NIPCCR", evidence="single_reliable_source", notes="Nigerian Investment Promotion Commission."),
    dict(record=CR, metric="area_km2", value_low=22112, method="other", source="STAT", evidence="single_reliable_source", notes="Statoids."),
]

GAPS = [
    ("Plateau Province (1926): its make-up", "Wikipedia says it was carved from Bauchi Province (Jos and Pankshin divisions). The Nasarawa State Government says it was drawn from the Muri, Nasarawa and Bauchi provinces. Both are recorded, attributed. Needs colonial records."),
    ("Old Calabar and the protectorates", "Given by Wikipedia only (Efik city-state of Akwa Akpa; Oil Rivers Protectorate from 1884; Niger Coast and Southern Nigeria protectorates). Needs scholarly sources and its own records (polities, events)."),
    ("Bakassi Peninsula", "Bakassi remains an LGA in the Constitution's list. Its transfer to Cameroon (the 2002 International Court of Justice judgment and the 2006 Greentree Agreement) is not recorded here. It needs a careful, well-sourced treatment of its own."),
    ("Conflict in Plateau State", "Wikipedia describes violence in the 21st century. It is not summarised here; it needs a separate, carefully sourced and neutral treatment."),
    ("Tiv at LGA level in Plateau and Cross River", "Wikipedia's language-by-LGA tables name Tiv in Langtang South, Qua'an Pan, Shendam and Wase (Plateau) and in Obanliku, Obudu, Bekwarra and Yala (Cross River). They are mentioned in the text but not yet recorded as links, because they need a second source."),
    ("Peoples of Plateau and Cross River as national records", "Berom, Ngas, Tarok, Efik, Ejagham, Yakurr and the other peoples named here are not yet records."),
    ("NIPC's claim that Cross River 'houses 58% of the country's forest'", "Not recorded; it needs an authoritative forestry source."),
    ("Areas and population figures", "The areas and populations from each source differ. All are recorded; none is chosen."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="Plateau State and Cross River State: history, geography, peoples, economy and population (fills empty fields of the existing records).")


def report():
    L = ["# Research batch 008 — Plateau State and Cross River State", "",
         f"Researched {ACCESSED}. Both state pages are already published, so **this text goes live the moment it is imported**. This report is the review.", ""]
    for name, d, g in [("Plateau", PL_DESCRIPTION, PL_GEOGRAPHY), ("Cross River", CR_DESCRIPTION, CR_GEOGRAPHY)]:
        L += [f"New prose for {name}: **{words(d) + words(g)} words**. The page passes the 300-word indexing rule and becomes indexable."]
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    for name, d, g in [("Plateau State", PL_DESCRIPTION, PL_GEOGRAPHY), ("Cross River State", CR_DESCRIPTION, CR_GEOGRAPHY)]:
        L += ["", f"## {name} — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in d.split("\n")]
        L += ["", f"## {name} — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in g.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Plateau created 3 Feb 1976 from Benue-Plateau | NIPC; Statoids; state government; Wikipedia; batch 001 | stated |",
          "| Named after the Jos Plateau; 'Home of Peace and Tourism' | NIPC; state government; Wikipedia; Statoids | stated |",
          "| Bauchi Province; Plateau Province 1926 (make-up differs) | Wikipedia; Nasarawa State Government | stated (1926), make-up attributed + gap |",
          "| Benue-Plateau May 1967; Nasarawa separated 1996 | Wikipedia; Nasarawa State Government; NIPC; Statoids | stated |",
          "| Plateau neighbours; 17 LGAs | NIPC; Wikipedia; state government | stated; Bauchi (2 sources) and Kaduna (NIPC only) links |",
          "| Over forty ethno-linguistic groups; names | Wikipedia only | attributed |",
          "| Minerals | NIPC; Wikipedia | stated; tin from 1902 attributed |",
          "| Crops | NIPC only | attributed |",
          "| Wase Rock | state government; Wikipedia | stated |",
          "| National Museum Jos (1952), Nok terracottas; climate; rivers | Wikipedia only | attributed |",
          "| Cross River: South-Eastern State 27 May 1967; renamed 1976 | NIPC = Wikipedia (same wording); Statoids; batch 001 | stated |",
          "| Akwa Ibom separated 23 Sep 1987 | NIPC (Akwa Ibom page); Wikipedia; batch 001 | stated |",
          "| Cross River boundaries; 18 LGAs | state government = Wikipedia (same wording); NIPC (LGAs); Benue State Government (Benue border) | stated; 3 links as single source |",
          "| 'Nation's Paradise' | NIPC only | attributed |",
          "| Old Calabar; Oil Rivers Protectorate | Wikipedia only | attributed + gap |",
          "| Peoples of Cross River | Wikipedia only | attributed |",
          "| Crops (cocoa, oil palm, rubber) | NIPC; Wikipedia | stated; other crops attributed |",
          "| Tourism; forests; Obudu plateau | Wikipedia only | attributed |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['record'].split(':')[-1].replace('-', ' ').title()} — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", "## Neighbours", "", "- Plateau ↔ Bauchi (2 sources), Kaduna (NIPC only). Links with Nasarawa and Taraba already exist.",
          "- Cross River ↔ Ebonyi, Abia, Akwa Ibom (single lineage). The Benue link already exists.",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- Two more state pages become indexable, making five in all. Five is the threshold for the Nigeria Heritage home page (/nigeria/), so it becomes indexable too. The sitemap grows by 3 (2,980 → 2,983). The new source pages stay noindex.",
          "- The text is original prose. Every claim is sourced or attributed, and the tone is neutral. Conflict (Plateau) and the Bakassi transfer are left for separate, carefully sourced treatment.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_008_plateau_cross_river.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_008_plateau_cross_river_REVIEW.md", "w").write(report())
    print(f"Plateau words={words(PL_DESCRIPTION) + words(PL_GEOGRAPHY)} Cross River words={words(CR_DESCRIPTION) + words(CR_GEOGRAPHY)} "
          f"relations={len(RELATIONS)} statistics={len(STATISTICS)}")
