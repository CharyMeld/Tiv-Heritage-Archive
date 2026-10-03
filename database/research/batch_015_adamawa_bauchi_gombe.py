"""
Research batch 015 — Adamawa, Bauchi and Gombe states: history, geography, peoples and
economy (researched 2026-09-25). Same method as batches 004, 007-009, 013 and 014:

Fills the EMPTY description and geography fields of the three existing, published state
records (never overwrites), adds dated population/area figures side by side, and sourced
'neighbours' links (existing links are not duplicated: Taraba–Adamawa, Taraba–Bauchi,
Taraba–Gombe, Plateau–Bauchi).
  * facts with two or more independent sources are stated plainly;
  * single-source claims are attributed in the text;
  * shared lineage: a passage of Wikipedia's Bauchi history repeats the Bauchi State
    Government's wording (including "in 1997 when Gombe State was created"); they count as
    one source for it;
  * NIPC's Adamawa paragraph on Mount Cameroon and the Kameruns is confused and not used;
  * the Boko Haram insurgency and other conflicts are left for separate treatment.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "ADGOV": dict(source_type="official_website", title="A Brief History of Adamawa State", organisation="Adamawa State Government",
                  url="https://adamawastate.gov.ng/history/", verification_status="needs_corroboration",
                  notes="'Land of Beauty'; established 27 August 1991 alongside Taraba State from the former Gongola State, capital Yola; area 36,917 km²; borders Borno, Gombe, Taraba and Cameroon; 21 LGAs in 3 senatorial districts; table of languages by LGA; 2006 census 3,178,950; projected 4,902,100."),
    "BAGOV": dict(source_type="official_website", title="History", organisation="Bauchi State Government",
                  url="https://home.bauchistate.gov.ng/history/", verification_status="needs_corroboration",
                  notes="Part of Bauchi-Plateau under colonial rule; 1967 North-Eastern State (Bauchi, Borno, Adamawa provinces); 1976 Bauchi State including present Gombe; Gombe created 'in 1997'; 20 LGAs; area 49,119 km²; borders seven states; Sudan and Sahel savanna; Jos Plateau continuation; Yankari; Bauchi city founded by Yaqub ibn Dadi, named after the hunter Baushe; Abubakar Tafawa Balewa buried there; peoples."),
    "BAPEO": dict(source_type="official_website", title="The People", organisation="Bauchi State Government",
                  url="https://home.bauchistate.gov.ng/the-people/", verification_status="needs_corroboration",
                  notes="55 ethnic groups, main ones Hausa, Fulani, Gerawa, Sayawa, Jarawa, Bolewa, Karekare, Kanuri, Fa'awa, Butawa, Warjawa, Zulawa and Badawa; Dambe boxing and Kokowa wrestling; durbar; BAFEST; embroidered babban riga; crafts; 'Home of Peace & Hospitality'."),
    "GOGOV": dict(source_type="official_website", title="Our History", organisation="Gombe State Government",
                  url="https://gombestate.gov.ng/pages/history.php", verification_status="needs_corroboration",
                  notes="Created 1 October 1996 by the military administration of Sani Abacha, carved out of the old Bauchi State; 'Jewel in the Savannah'; agrarian; bounded by Borno, Yobe, Taraba, Adamawa and Bauchi."),
    "NIPCAD": dict(source_type="official_website", title="Adamawa State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/adamawa-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "NIPCBA": dict(source_type="official_website", title="Bauchi State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/bauchi-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "NIPCGO": dict(source_type="official_website", title="Gombe State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/gombe-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "WAD": dict(source_type="encyclopedia", title="Adamawa State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Adamawa_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: named after the Adamawa Emirate; formed 1991 from Gongola; over 100 ethnic groups; Modibo Adama, 1806 flag, Yola founded 1841; Sokoto Caliphate; German and British conquest and division; neighbours; highlands and river valleys; Gashaka Gumti; crops; Numan sugar factory."),
    "WBA": dict(source_type="encyclopedia", title="Bauchi State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Bauchi_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: named after the city of Bauchi; formed 3 February 1976 from North-Eastern State; Gombe separated in 1996 (introduction); Bauchi Emirate under the Sokoto Caliphate; Bauchi Province; neighbours; vegetation; Yankari and Wikki Warm Springs; peoples; crops; tin and columbite."),
    "WGO": dict(source_type="encyclopedia", title="Gombe State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Gombe_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: formed from part of Bauchi on 1 October 1996; 'Jewel in the Savannah'; neighbours; Gongola River and Lake Dadin Kowa; Muri Mountains; peoples (Fulani, Tangale and others); Gombe Emirate; British occupation in the 1910s; crops, industries, minerals."),
    "CPAD": dict(source_type="dataset", title="Adamawa (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA002__adamawa/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,102,053; census 2006: 3,178,950; projection 2022: 4,902,100 (National Population Commission / National Bureau of Statistics)."),
    "CPBA": dict(source_type="dataset", title="Bauchi (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA005__bauchi/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,861,887; census 2006: 4,653,066; projection 2022: 8,308,800 (National Population Commission / National Bureau of Statistics)."),
    "CPGO": dict(source_type="dataset", title="Gombe (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA016__gombe/", verification_status="needs_corroboration",
                 notes="Census 1991: 1,489,120; census 2006: 2,365,040; projection 2022: 3,960,100 (National Population Commission / National Bureau of Statistics)."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration",
                 notes="Reused (batch 001). 2006 census table: Adamawa 3,168,101, 37,957 km²; Bauchi 4,676,465, 48,197 km²; Gombe 2,353,879, 17,428 km². Chronology: 1976-02-03 North-Eastern State divided into Bauchi, Borno and Gongola; 1991-08-27 Gongola divided into Adamawa and Taraba; 1996-10-01 Gombe split from Bauchi."),
}

AD_DESCRIPTION = """Adamawa State was created on 27 August 1991, when Gongola State was divided into Adamawa and Taraba states. The Adamawa State Government, the Nigerian Investment Promotion Commission and Statoids all give this account. The state takes its name from the historic Adamawa Emirate, and its capital, Yola, was the emirate's capital. It is grouped in the North East geopolitical zone, and its nickname is "Land of Beauty".

Wikipedia outlines the earlier history. The emirate was founded by Modibo Adama, a leader of the Fulani jihad launched by Usman dan Fodio. Adama received a flag to lead the jihad in his home region in 1806 and founded Yola in 1841. The emirate was part of the Sokoto Caliphate and reached into much of what is now northern Cameroon. About ninety years after the jihad began, German and British forces defeated it and divided the area. The British part joined the Northern Nigeria Protectorate, and the German part became part of Kamerun. The rulers kept the title of emir, lamido in Fulfulde. After independence the area belonged to the Northern Region, then from 1967 to North-Eastern State and from 1976 to Gongola State.

Adamawa borders Borno State to the north-west, Gombe State to the west and Taraba State to the south-west, and its eastern border is Nigeria's border with Cameroon. It has twenty-one local government areas.

The state is one of the most diverse in Nigeria; Wikipedia counts more than a hundred indigenous ethnic groups. The state government's table of languages by local government area includes Fulfulde, Bata, Bachama, Marghi, Kilba, Higgi, Fali, Longuda, Chamba, Mbula-Bwazza, Vere and Mafa, among many others. Fulfulde appears in several of them.

Farming and livestock are the base of the economy. The Commission and Wikipedia both name cotton, groundnuts, millet, cassava and yams among the crops, with guinea corn, maize and rice. Wikipedia notes a large sugar factory at Numan.

The 2006 census counted 3,178,950 people, according to National Population Commission figures reproduced by City Population and by the state government. Other published figures are listed below with their sources. Nigerian census results are disputed."""

AD_GEOGRAPHY = """Much of the state is highland. Wikipedia names the Mandara, Atlantika and Shebshi ranges and the Adamawa Plateau. The Commission and Wikipedia both describe a mountainous land crossed by the valleys of the Benue, the Gongola and the Yedsarem. The lowlands are savanna, drier in the north and wetter in the south. Part of the Gashaka Gumti National Park lies in the far south of the state, according to Wikipedia.

The area is given as 36,917 square kilometres by the state government and Wikipedia, 38,700 by the Nigerian Investment Promotion Commission and 37,957 by Statoids. The figures are recorded below."""

BA_DESCRIPTION = """Bauchi State was created on 3 February 1976, when North-Eastern State was divided. The Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this date. The state takes its name from the city of Bauchi, its capital. It is grouped in the North East geopolitical zone. The Commission calls it the "Pearl of Tourism", and the state government calls it the "Home of Peace and Hospitality". At its creation it also included the area of present-day Gombe State. Gombe was separated on 1 October 1996, according to the Gombe State Government, the Commission and Statoids; the Bauchi State Government's page gives 1997.

According to Wikipedia, much of the area became part of the Sokoto Caliphate in the early 19th century as the Bauchi Emirate, after the Fulani jihad. The British later occupied it and made it Bauchi Province of the Northern Nigeria Protectorate. The state government records that the city of Bauchi was founded by Yaqub ibn Dadi, the only non-Fulani flag-bearer of the Sokoto jihad. It says the city was named after a hunter, Baushe, who advised him where to build, and notes that Abubakar Tafawa Balewa is buried in the city. According to the state government, the Bauchi, Borno and Adamawa provinces formed North-Eastern State in 1967.

Bauchi borders seven states: Kano and Jigawa to the north, Yobe to the north-east, Gombe to the east, Taraba and Plateau to the south, and Kaduna to the west. It has twenty local government areas.

The state government counts fifty-five ethnic groups. The main ones are the Hausa, Fulani, Gerawa, Sayawa, Jarawa, Bolewa, Karekare, Kanuri, Butawa, Warjawa, Zulawa and Badawa. It highlights the traditional boxing called Dambe and the wrestling called Kokowa, durbar horse festivals, and embroidered gowns and caps (babban riga).

Farming is central to the economy. The Commission and Wikipedia both name cotton, groundnuts, millet and yams among the crops, supported by irrigation dams. Tin and columbite are mined. Yankari, which the state government calls the most developed wildlife park in Nigeria, draws visitors, and Wikipedia mentions its Wikki warm springs.

The 2006 census counted 4,653,066 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

BA_GEOGRAPHY = """The state spans two vegetation zones, according to the state government, the Commission and Wikipedia. Sudan savanna covers the south, and the drier Sahel savanna, with scattered thorny shrubs, covers the north. The south-west is hilly, an extension of the Jos Plateau, and the north is generally sandy. The Commission names the Gongola and Jama'are rivers, with floodplains (fadama) and dams used for farming, and notes that much of the Hadejia–Jama'are river basin lies in the state.

The area is given as 49,119 square kilometres by the state government, the Commission and Wikipedia, and 48,197 by Statoids. Both figures are recorded below."""

GO_DESCRIPTION = """Gombe State was created on 1 October 1996 by the military government of Sani Abacha, from part of Bauchi State. The Gombe State Government, the Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this date. Its capital is the city of Gombe, it is grouped in the North East geopolitical zone, and its nickname is "Jewel in the Savannah".

Wikipedia outlines the earlier history. In the early 19th century the Fulani jihad brought much of the north of the area into the Gombe Emirate, part of the Sokoto Caliphate. British expeditions occupied the emirate and the surrounding areas in the 1910s, and the area joined the Northern Nigeria Protectorate. After independence it was part of the Northern Region, then from 1967 of North-Eastern State and from 1976 of Bauchi State.

Gombe borders Yobe State to the north, Borno State to the north-east, Adamawa State to the south-east, Taraba State to the south and Bauchi State to the west. It has eleven local government areas.

Wikipedia names the Fulani and the Tangale as the largest groups. It places the Fulani mainly in the north and centre and the Tangale in the south, around Billiri and Kaltungo. Other peoples include the Hausa, Tera, Waja, Tula, Dadiya, Lunguda, Bolewa and Kanuri.

Most people farm. The Commission and Wikipedia both name yams, cassava, maize, tomatoes, groundnuts and cotton, and Wikipedia mentions a groundnut oil mill, a cotton gin and cement production. Both list uranium, gypsum and limestone among the minerals.

The 2006 census counted 2,365,040 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

GO_GEOGRAPHY = """Gombe lies in the savanna. The Gongola River flows through the north and east of the state into Lake Dadin Kowa, and the small Muri Mountains rise in the far south, according to Wikipedia. The Commission describes a warm climate. It says the hottest months are March to May, when temperatures do not exceed about 30 °C, and gives average rainfall as about 850 millimetres a year.

The area is given as 17,100 square kilometres by the Nigerian Investment Promotion Commission and 17,428 by Statoids. Both figures are recorded below."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


AD, BA, GO = "@admin_units:state:adamawa", "@admin_units:state:bauchi", "@admin_units:state:gombe"

UPDATES = [
    dict(ref=AD, fields=dict(description=AD_DESCRIPTION, geography_notes=AD_GEOGRAPHY),
         srcs=[("ADGOV", "Created 27 August 1991 from Gongola with Taraba; Yola; Land of Beauty; neighbours; 21 LGAs; languages by LGA; 2006 census; area 36,917 km²"),
               ("NIPCAD", "Created 27 August 1991 from Gongola; neighbours; river valleys; crops; area 38,700 km²"),
               ("WAD", "Adamawa Emirate, Modibo Adama, Yola 1841; Sokoto Caliphate; German–British division; over 100 groups; highlands; Gashaka Gumti; crops; Numan sugar"),
               ("STAT", "1976 Gongola; 1991 Adamawa; area 37,957 km²"), ("CPAD", "2006 census 3,178,950 (National Population Commission)")]),
    dict(ref=BA, fields=dict(description=BA_DESCRIPTION, geography_notes=BA_GEOGRAPHY),
         srcs=[("NIPCBA", "Formed 3 February 1976 from North-Eastern State; seven neighbours; rivers, fadama, dams; Pearl of Tourism; crops; minerals; area 49,119 km²"),
               ("BAGOV", "North-Eastern State 1967; Bauchi State 1976 with Gombe; 'Gombe 1997'; vegetation; Yankari; Bauchi city, Yaqub, Baushe; Tafawa Balewa"),
               ("BAPEO", "55 ethnic groups; Dambe, Kokowa; durbar; babban riga; Home of Peace & Hospitality"),
               ("WBA", "Bauchi Emirate; Bauchi Province; Gombe 1996; neighbours; Wikki Warm Springs; crops; tin and columbite"),
               ("STAT", "1976 Bauchi; 1996 Gombe; area 48,197 km²"), ("CPBA", "2006 census 4,653,066 (National Population Commission)")]),
    dict(ref=GO, fields=dict(description=GO_DESCRIPTION, geography_notes=GO_GEOGRAPHY),
         srcs=[("GOGOV", "Created 1 October 1996 by Abacha from Bauchi; Jewel in the Savannah; neighbours"),
               ("NIPCGO", "Created 1 October 1996 from Bauchi; neighbours; climate; crops; minerals; area 17,100 km²"),
               ("WGO", "Gombe Emirate; British occupation 1910s; peoples; Gongola, Lake Dadin Kowa, Muri Mountains; industries"),
               ("STAT", "1996 Gombe from Bauchi; area 17,428 km²"), ("CPGO", "2006 census 2,365,040 (National Population Commission)")]),
]

THREE = "Listed by the {gov}, the Nigerian Investment Promotion Commission and Wikipedia."
RELATIONS = [dict(frm=AD, type="neighbours", to=f"@admin_units:state:{n}", source="ADGOV", evidence="multiple_sources",
                  notes=THREE.format(gov="Adamawa State Government")) for n in ["borno", "gombe"]]
RELATIONS += [dict(frm=BA, type="neighbours", to=f"@admin_units:state:{n}", source="BAGOV", evidence="multiple_sources",
                   notes=THREE.format(gov="Bauchi State Government")) for n in ["kano", "jigawa", "yobe", "gombe", "kaduna"]]
RELATIONS += [dict(frm=GO, type="neighbours", to=f"@admin_units:state:{n}", source="GOGOV", evidence="multiple_sources",
                   notes=THREE.format(gov="Gombe State Government")) for n in ["yobe", "borno"]]


def stat(record, metric, value, source, notes, year=None, method="other"):
    d = dict(record=record, metric=metric, value_low=value, method=method, source=source, evidence="single_reliable_source", notes=notes)
    if year:
        d["reference_year"] = year
    return d


NPC = "National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."
STATISTICS = [
    stat(AD, "population", 3178950, "CPAD", NPC + " The Adamawa State Government gives the same figure.", 2006, "census"),
    stat(AD, "population", 3168101, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(AD, "population", 2102053, "CPAD", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(AD, "population", 4902100, "CPAD", "Projection, not a count (NPC / NBS, via City Population); the state government gives the same projection.", 2022, "projection"),
    stat(AD, "population", 4502132, "NIPCAD", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(AD, "area_km2", 36917, "ADGOV", "Adamawa State Government (Wikipedia gives the same)."),
    stat(AD, "area_km2", 38700, "NIPCAD", "Nigerian Investment Promotion Commission."),
    stat(AD, "area_km2", 37957, "STAT", "Statoids."),
    stat(BA, "population", 4653066, "CPBA", NPC, 2006, "census"),
    stat(BA, "population", 4676465, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(BA, "population", 2861887, "CPBA", "1991 census for the area of today's state (without Gombe), as reproduced by City Population.", 1991, "census"),
    stat(BA, "population", 8308800, "CPBA", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(BA, "population", 6997314, "NIPCBA", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(BA, "area_km2", 49119, "NIPCBA", "Nigerian Investment Promotion Commission (the state government and Wikipedia give the same)."),
    stat(BA, "area_km2", 48197, "STAT", "Statoids."),
    stat(GO, "population", 2365040, "CPGO", NPC, 2006, "census"),
    stat(GO, "population", 2353879, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(GO, "population", 1489120, "CPGO", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(GO, "population", 3960100, "CPGO", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(GO, "population", 3472223, "NIPCGO", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(GO, "area_km2", 17100, "NIPCGO", "Nigerian Investment Promotion Commission."),
    stat(GO, "area_km2", 17428, "STAT", "Statoids."),
]

GAPS = [
    ("Gombe: 1996 or 1997", "The Gombe State Government, NIPC, Statoids and Wikipedia's introduction give 1 October 1996; the Bauchi State Government's history page (repeated word for word in Wikipedia's Bauchi history section) says 1997. 1996 is used; 1997 is noted."),
    ("Adamawa Emirate, Modibo Adama and the German–British division", "Given by Wikipedia only. They need scholarly sources and their own records (emirate, events)."),
    ("Bauchi Emirate and Yaqub ibn Dadi", "The founding story is from the Bauchi State Government; the emirate from Wikipedia. Scholarly sources needed."),
    ("Gombe Emirate and the Tangale", "Wikipedia's account of the Tangale in the pre-colonial period is unclear and is not used. Needs scholarly sources."),
    ("Sukur", "The Sukur cultural landscape in Madagali (Adamawa) is a UNESCO World Heritage Site, but none of the sources read for this batch describes it. Needs its own place record with UNESCO as a source."),
    ("Boko Haram insurgency", "Wikipedia notes its effect on Adamawa's development. Not summarised; it needs separate, careful treatment."),
    ("Adamawa's climate", "The state government's rainfall figures (79 and 179 mm a year) are implausibly low and are not used."),
    ("Areas and population figures", "The areas and populations from each source differ. All are recorded; none is chosen."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="Adamawa, Bauchi and Gombe states: history, geography, peoples, economy and population (fills empty fields of the existing records).")


STATES = [("Adamawa State", AD_DESCRIPTION, AD_GEOGRAPHY), ("Bauchi State", BA_DESCRIPTION, BA_GEOGRAPHY), ("Gombe State", GO_DESCRIPTION, GO_GEOGRAPHY)]


def report():
    L = ["# Research batch 015 — Adamawa, Bauchi and Gombe states", "",
         f"Researched {ACCESSED}. All three state pages are already published, so **this text goes live the moment it is imported**. This report is the review.", ""]
    for name, d, g in STATES:
        L += [f"- New prose for {name}: **{words(d) + words(g)} words**. The page passes the 300-word indexing rule and becomes indexable."]
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    for name, d, g in STATES:
        L += ["", f"## {name} — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in d.split("\n")]
        L += ["", f"## {name} — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in g.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Adamawa created 27 Aug 1991 from Gongola with Taraba | state government; NIPC; Statoids; Wikipedia | stated |",
          "| Named after the Adamawa Emirate; Yola its capital | Wikipedia; state government (Yola) | stated |",
          "| Modibo Adama; Sokoto Caliphate; German–British division | Wikipedia only | attributed + gap |",
          "| Adamawa neighbours; 21 LGAs | state government; NIPC; Wikipedia | 2 new links multiple |",
          "| Over 100 groups; languages by LGA | Wikipedia; state government | attributed |",
          "| Bauchi created 3 Feb 1976 from North-Eastern State | NIPC; Wikipedia; Statoids | stated |",
          "| Gombe separated 1996 (1997 on the Bauchi government page) | Gombe government; NIPC; Statoids; Wikipedia vs Bauchi government | 1996 stated; 1997 attributed |",
          "| Bauchi Emirate; Bauchi Province | Wikipedia | attributed |",
          "| Yaqub ibn Dadi, Baushe; Tafawa Balewa's grave | Bauchi State Government | attributed |",
          "| Bauchi neighbours (seven) | state government; NIPC; Wikipedia | 5 new links multiple |",
          "| Bauchi peoples, Dambe, Kokowa, durbar | Bauchi State Government | attributed |",
          "| Bauchi vegetation, rivers, Yankari | state government; NIPC; Wikipedia | stated / attributed |",
          "| Gombe created 1 Oct 1996 by Abacha from Bauchi | Gombe government; NIPC; Wikipedia; Statoids | stated |",
          "| Gombe Emirate; British 1910s | Wikipedia | attributed |",
          "| Gombe neighbours; 11 LGAs | Gombe government; NIPC; Wikipedia | 2 new links multiple |",
          "| Gombe peoples | Wikipedia | attributed |",
          "| Crops and minerals (all three) | NIPC; Wikipedia | stated where both agree |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['record'].split(':')[-1].title()} — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", f"## Neighbours ({len(RELATIONS)} new links)", "",
          "- Adamawa ↔ Borno, Gombe. The Taraba link already exists.",
          "- Bauchi ↔ Kano, Jigawa, Yobe, Gombe, Kaduna. Links with Taraba and Plateau already exist.",
          "- Gombe ↔ Yobe, Borno. Links with Taraba, Adamawa and Bauchi are covered.",
          "- Each is listed by the state government, NIPC and Wikipedia.",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- Three more state pages become indexable (17 of 37 in all). The sitemap should grow by 3 (2,995 → 2,998). The new source pages stay noindex.",
          "- The text is original prose, and every claim is sourced or attributed. The insurgency is left for separate treatment.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_015_adamawa_bauchi_gombe.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_015_adamawa_bauchi_gombe_REVIEW.md", "w").write(report())
    print(" ".join(f"{n.split()[0]}={words(d) + words(g)}" for n, d, g in STATES) + f" relations={len(RELATIONS)} statistics={len(STATISTICS)}")
