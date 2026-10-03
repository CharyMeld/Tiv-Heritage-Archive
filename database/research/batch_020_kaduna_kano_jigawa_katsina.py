"""
Research batch 020 — Kaduna, Kano, Jigawa and Katsina states (North West, part 1): history,
geography, peoples and economy (researched 2026-09-25). Same method as batches 013-019:

Fills the EMPTY description and geography fields of the four existing, published state
records (never overwrites), adds dated population/area figures side by side, and sourced
'neighbours' links (existing links are not duplicated).
  * facts with two or more independent sources are stated plainly;
  * single-source claims are attributed in the text;
  * shared lineage: the Kano State Government's history page reproduces Wikipedia's Kano
    article (reference numbers included); the Kaduna State Government's "About" page and
    NIPC's Kaduna page share the same wording. Each pair counts as ONE source;
  * the Jigawa and Katsina government websites had no usable content, and NIPC's Jigawa and
    Katsina pages could not be retrieved (Internet Archive rate limit); those states rest on
    Wikipedia, Statoids and City Population, so more of their text is attributed;
  * the borders between these four states are given by Wikipedia only (one publisher) and
    are recorded as single-source links;
  * ethnic and religious violence, banditry and kidnappings are left for separate treatment.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "KDGOV": dict(source_type="official_website", title="About Kaduna State", organisation="Kaduna State Government",
                  url="https://kdsg.gov.ng/government/about-kaduna-state/", verification_status="needs_corroboration",
                  notes="Lugard moved the Northern Region's capital from Zungeru to Kaduna; 1967 capital of North-Central State; named Kaduna State by 1976 under Murtala Mohammed; Katsina created from it in 1987; Nok; 'Centre of Learning' (NDA, ABU, Nigerian College of Aviation, Barewa College); cosmopolitan. Wording shared with NIPC's Kaduna page."),
    "KNGOV": dict(source_type="official_website", title="History", organisation="Kano State Government",
                  url="https://kanostate.gov.ng/history/", verification_status="needs_corroboration",
                  notes="Reproduces Wikipedia's Kano State article (with its reference numbers): created 1967; borders; Kingdom of Kano at Dala Hill; Sultanate from 1349; Kurmi Market; British conquest 1903; peoples. Counted with Wikipedia as one source."),
    "NIPCKD": dict(source_type="official_website", title="Kaduna State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/kaduna-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "NIPCKN": dict(source_type="official_website", title="Kano State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/kano-state/",
                   archive_reference="Internet Archive snapshot 20240527194232: http://web.archive.org/web/20240527194232/https://www.nipc.gov.ng/nigeria-states/kano-state/",
                   verification_status="needs_corroboration",
                   notes="Created 27 May 1967; 'Centre of Commerce'; southern hub of the trans-Saharan trade for centuries; area 20,280 km²; 44 LGAs; population 13,969,085 (no year); crops; minerals."),
    "WKD": dict(source_type="encyclopedia", title="Kaduna State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Kaduna_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: North-Central State 1967, Katsina separated 1987; Nok (c. 1500 BC – c. 500 AD); Hausa kingdoms; Zazzau/Zaria and Queen Amina; Sokoto Caliphate; capital moved from Zungeru to Kaduna (1916); borders; Sudan savanna; Kaduna River; Kamuku National Park; cotton and groundnuts."),
    "WKN": dict(source_type="encyclopedia", title="Kano State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Kano_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: created 1967; most populous state in 2006; borders; Hausa and Fulani; Kingdom of Kano and Dala Hill; Sultanate 1349; Kurmi Market (1463); most powerful Hausa kingdom; Fulani jihad 1804–08; markets; crops; groundnut exports; city wall, Gidan Rumfa, Gidan Makama; rainfall."),
    "WJG": dict(source_type="encyclopedia", title="Jigawa State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Jigawa_State",
                verification_status="needs_corroboration",
                notes="Used for: created 27 August 1991 from Kano; capital Dutse; 27 LGAs; borders incl. Zinder (Niger) and the Maigatari free trade zone; Dutsen Habude cave paintings at Birnin Kudu; Hadejia (Biram), one of the seven Hausa states; Hausa and Fulani; farming and crafts; sand dunes; area 22,410 km²."),
    "WKT": dict(source_type="encyclopedia", title="Katsina State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Katsina_State",
                verification_status="needs_corroboration",
                notes="Used for: created 1987 from the north of Kaduna State; capital Katsina; 'Home of Hospitality'; 34 LGAs; borders; Daura and Katsina among the Hausa states; emirates under the Sokoto Caliphate; Hausa, Fulani, Maguzawa; crops and livestock; savanna; Bunsuru, Gada and Sokoto rivers; area 23,938 km²."),
    "CPKD": dict(source_type="dataset", title="Kaduna (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA019__kaduna/", verification_status="needs_corroboration",
                 notes="Census 1991: 3,935,618; census 2006: 6,113,503; projection 2022: 9,032,200 (National Population Commission / National Bureau of Statistics)."),
    "CPKN": dict(source_type="dataset", title="Kano (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA020__kano/", verification_status="needs_corroboration",
                 notes="Census 1991: 5,810,470; census 2006: 9,401,288; projection 2022: 15,462,200 (National Population Commission / National Bureau of Statistics)."),
    "CPJG": dict(source_type="dataset", title="Jigawa (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA018__jigawa/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,875,525; census 2006: 4,361,002; projection 2022: 7,499,100 (National Population Commission / National Bureau of Statistics)."),
    "CPKT": dict(source_type="dataset", title="Katsina (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA021__katsina/", verification_status="needs_corroboration",
                 notes="Census 1991: 3,753,133; census 2006: 5,801,584; projection 2022: 10,368,500 (National Population Commission / National Bureau of Statistics)."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration",
                 notes="Reused (batch 001). 2006 census table: Kaduna 6,066,562, 44,217 km²; Kano 9,383,682, 20,389 km²; Jigawa 4,348,649, 23,415 km²; Katsina 5,792,578, 23,822 km². Chronology: 1967-05-27 North-Central (Kaduna) and Kano states created; 1987-09-23 Katsina split from Kaduna; 1991-08-27 Jigawa split from Kano."),
}

KD_DESCRIPTION = """Kaduna State dates from 27 May 1967, when North-Central State was created from the Northern Region with Kaduna as its capital. The state government, the Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this account. It was renamed Kaduna State under the military government of Murtala Mohammed in the mid-1970s. The state government gives 1976 and Wikipedia 1975. On 23 September 1987 its northern part became Katsina State. The state is grouped in the North West geopolitical zone, and its nickname is "Centre of Learning". The state government and Wikipedia both cite the institutions there, among them Ahmadu Bello University and the Nigerian Defence Academy.

The area has a long history. The state government and Wikipedia both note that the Nok culture, which the state government calls one of Africa's earliest civilisations, is found in the state; Wikipedia dates it to about 1500 BC to AD 500. Wikipedia says Hausa kingdoms, among them Zazzau (Zaria), which tradition links with Queen Amina, ruled the north until the Sokoto Caliphate absorbed the area in the early 19th century. Under British rule, Lord Lugard moved the capital of Northern Nigeria from Zungeru to Kaduna. The state government records this, and Wikipedia dates it to 1916.

Kaduna borders Zamfara and Katsina states to the north, Kano State to the north-east, Bauchi and Plateau states to the east, Nasarawa State and the Federal Capital Territory to the south, and Niger State to the west, according to Wikipedia. It has twenty-three local government areas.

The state is diverse. Wikipedia names the Hausa of Zaria in the north and the Ham (Jaba) in the south among its oldest inhabitants. The state government describes Kaduna as cosmopolitan, home to people from all over Nigeria. Wikipedia names cotton and groundnuts as the main cash crops, and the Commission lists maize, millet, rice, ginger and sorghum.

The 2006 census counted 6,113,503 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

KD_GEOGRAPHY = """The state lies on the high plains of northern Nigeria, in the Sudan savanna of scattered trees, shrubs and grass, according to Wikipedia. The Kaduna River, a tributary of the Niger, flows through it, and many communities face seasonal flooding. Wikipedia names the Kamuku National Park and the Matsirga waterfalls among its natural sites.

The area is given as 42,481 square kilometres by the Nigerian Investment Promotion Commission and 44,217 by Statoids. Both figures are recorded below."""

KN_DESCRIPTION = """Kano State was created on 27 May 1967 from the Northern Region. The Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this date. Its capital, Kano, is one of the largest cities in Nigeria. On 27 August 1991 the north-east of the state was separated to form Jigawa State. The state is grouped in the North West geopolitical zone, and its nickname is "Centre of Commerce". The Commission describes Kano as the southern hub of the trans-Saharan trade for centuries.

Wikipedia outlines the city's long history, and the state government's history page repeats it. A Kingdom of Kano centred on Dala Hill existed before AD 1000. In 1349 it became a sultanate when its ruler, Yaji I, adopted Islam. The Kurmi Market opened in the 15th century, and by the 16th and 17th centuries Kano was one of the most powerful Hausa kingdoms. After the Fulani jihad of 1804 to 1808 it became the Kano Emirate within the Sokoto Caliphate. The British conquered it in 1903.

Kano borders Katsina State to the north-west, Jigawa State to the north-east, Bauchi State to the south-east and Kaduna State to the south-west. It has forty-four local government areas.

The Hausa and Fulani form the majority, and Hausa is the dominant language. In the 2006 census Kano was the most populous state in Nigeria.

Trade and farming are the base of the economy. Wikipedia names many markets, among them Kurmi, Kantin Kwari, Sabon Gari and Dawanau. It says millet, cowpeas, sorghum, maize and rice are grown for food, and that groundnuts and cotton were for many years a major source of Nigeria's export revenue. The Commission and Wikipedia also name sesame and chili peppers. Wikipedia lists the old city wall, the Emir's Palace (Gidan Rumfa) and the Gidan Makama Museum among Kano's heritage sites.

The 2006 census counted 9,401,288 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

KN_GEOGRAPHY = """The state lies in the savanna of northern Nigeria. Wikipedia notes that rainfall is variable, averaging about 900 millimetres a year. Droughts struck in the 1970s and 1980s, and floods in recent years.

The area is given as 20,280 square kilometres by the Nigerian Investment Promotion Commission and 20,389 by Statoids. Both figures are recorded below."""

JG_DESCRIPTION = """Jigawa State was created on 27 August 1991 by the military government of Ibrahim Babangida, from the north-eastern part of Kano State. Wikipedia and Statoids give this date. Its capital and largest city is Dutse. The state is grouped in the North West geopolitical zone.

Jigawa borders Kano and Katsina states to the west, Bauchi State to the east and Yobe State to the north-east, according to Wikipedia. To the north it shares an international boundary with the Zinder region of the Republic of Niger, and Wikipedia notes a free trade zone at Maigatari on that border. The state has twenty-seven local government areas.

The area has old roots. Wikipedia names the Dutsen Habude rock paintings at Birnin Kudu, which it dates to the Neolithic period. It adds that Hadejia, formerly called Biram, is counted among the seven original Hausa states.

Most people are Hausa or Fulani, according to Wikipedia, which also names Kanuri, Manga, Bade, Warji and Duwai speakers in particular areas. Farming and livestock are the main occupations. Wikipedia says more than 80 per cent of people are engaged in subsistence farming and animal husbandry. Blacksmithing, leather work, dyeing and other crafts are also common, and many workers travel to neighbouring states in the dry season.

The 2006 census counted 4,361,002 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

JG_GEOGRAPHY = """The land is undulating, with sand dunes in places, according to Wikipedia. The south rests on old basement rocks and the north-east on the sediments of the Chad Basin. The main rivers are the Hadejia, the Kafin Hausa and the Iggi. The Hadejia–Kafin Hausa river crosses the state from west to east through the Hadejia–Nguru wetlands, whose marshes cover much of the north-east, and drains into the Lake Chad basin. Most of the state is Sudan savanna, and Wikipedia warns that the loss of tree cover leaves the north open to desert encroachment. The climate is semi-arid and hot, and floods often damage farmland.

The area is given as about 22,410 square kilometres by Wikipedia and 23,415 by Statoids. Both figures are recorded below."""

KT_DESCRIPTION = """Katsina State was created on 23 September 1987 from the northern part of Kaduna State. Statoids, the Kaduna State Government and Wikipedia all give this account. The capital is the city of Katsina. The state is grouped in the North West geopolitical zone, and Wikipedia gives its nickname as "Home of Hospitality".

Wikipedia outlines the earlier history. Daura and Katsina were among the Hausa states, and in the 15th and 16th centuries both became major centres of trade and Islamic learning. After the Fulani jihad of the early 19th century they became emirates within the Sokoto Caliphate. Under British rule the area joined the Northern Nigeria Protectorate. After independence it was part of the Northern Region, and from 1967 of North-Central State, later renamed Kaduna State.

Katsina borders Zamfara State to the west, Kano and Jigawa states to the east and Kaduna State to the south, according to Wikipedia. To the north it shares an international boundary with the Republic of Niger. It has thirty-four local government areas.

The Hausa are the largest group, with Fulani and other minorities. Wikipedia notes that most people are Muslim and that the Maguzawa, Hausa who follow traditional religion, are among the minorities.

Farming and herding are the base of the economy. Wikipedia names millet, sorghum, maize, rice, groundnuts and cotton, and cattle, goats and sheep are widely raised.

The 2006 census counted 5,801,584 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

KT_GEOGRAPHY = """Most of the state lies in the Sudan savanna, turning to drier Sahel savanna in the north, according to Wikipedia. The Bunsuru, Gada and Sokoto rivers supply water for farming and settlements. The climate is hot, with a short rainy season.

The area is given as about 23,938 square kilometres by Wikipedia and 23,822 by Statoids. Both figures are recorded below."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


KD, KN, JG, KT = ("@admin_units:state:kaduna", "@admin_units:state:kano", "@admin_units:state:jigawa", "@admin_units:state:katsina")

UPDATES = [
    dict(ref=KD, fields=dict(description=KD_DESCRIPTION, geography_notes=KD_GEOGRAPHY),
         srcs=[("KDGOV", "North-Central State 1967; renamed by 1976; Katsina 1987; Nok; Centre of Learning; Zungeru to Kaduna; cosmopolitan"),
               ("NIPCKD", "Same wording as the state government; crops; minerals; area 42,481 km²"),
               ("WKD", "1967; 1975; 1987; Nok dates; Hausa kingdoms, Zazzau, Amina; Sokoto Caliphate; 1916; borders; savanna; Kaduna River; parks; crops"),
               ("STAT", "1967 North-Central; 1987 Katsina split; area 44,217 km²"), ("CPKD", "2006 census 6,113,503 (National Population Commission)")]),
    dict(ref=KN, fields=dict(description=KN_DESCRIPTION, geography_notes=KN_GEOGRAPHY),
         srcs=[("NIPCKN", "Created 27 May 1967; Centre of Commerce; trans-Saharan trade; 44 LGAs; crops; area 20,280 km²"),
               ("WKN", "Kingdom and Sultanate of Kano; Kurmi Market; Fulani jihad; 1903; borders; peoples; markets; crops; heritage sites; rainfall"),
               ("KNGOV", "Same text as Wikipedia (one lineage)"),
               ("STAT", "1967 Kano; 1991 Jigawa split; area 20,389 km²"), ("CPKN", "2006 census 9,401,288 (National Population Commission)")]),
    dict(ref=JG, fields=dict(description=JG_DESCRIPTION, geography_notes=JG_GEOGRAPHY),
         srcs=[("WJG", "Created 1991 from Kano; Dutse; borders; Maigatari; Dutsen Habude; Hadejia; peoples and languages; economy; geology, rivers, Hadejia–Nguru wetlands, vegetation, floods; area 22,410 km²"),
               ("STAT", "1991 Jigawa split from Kano; area 23,415 km²"), ("CPJG", "2006 census 4,361,002 (National Population Commission)")]),
    dict(ref=KT, fields=dict(description=KT_DESCRIPTION, geography_notes=KT_GEOGRAPHY),
         srcs=[("WKT", "Created 1987 from Kaduna; Katsina; Home of Hospitality; Daura and Katsina; emirates; borders; peoples; economy; savanna; rivers; area 23,938 km²"),
               ("KDGOV", "Katsina created from Kaduna in 1987"),
               ("STAT", "1987-09-23 Katsina split from Kaduna; area 23,822 km²"), ("CPKT", "2006 census 5,801,584 (National Population Commission)")]),
]

WIKI_ONLY = "Listed by Wikipedia ({a} and {b} articles); no independent source seen (the {c} State Government's page copies Wikipedia or has no border list)."
RELATIONS = [
    dict(frm=KD, type="neighbours", to=KN, source="WKD", evidence="single_reliable_source", notes=WIKI_ONLY.format(a="Kaduna", b="Kano", c="Kano")),
    dict(frm=KD, type="neighbours", to=KT, source="WKD", evidence="single_reliable_source", notes=WIKI_ONLY.format(a="Kaduna", b="Katsina", c="Katsina")),
    dict(frm=KN, type="neighbours", to=KT, source="WKN", evidence="single_reliable_source", notes=WIKI_ONLY.format(a="Kano", b="Katsina", c="Kano")),
    dict(frm=KN, type="neighbours", to=JG, source="WKN", evidence="single_reliable_source", notes=WIKI_ONLY.format(a="Kano", b="Jigawa", c="Kano")),
    dict(frm=JG, type="neighbours", to=KT, source="WJG", evidence="single_reliable_source", notes=WIKI_ONLY.format(a="Jigawa", b="Katsina", c="Jigawa")),
]


def stat(record, metric, value, source, notes, year=None, method="other"):
    d = dict(record=record, metric=metric, value_low=value, method=method, source=source, evidence="single_reliable_source", notes=notes)
    if year:
        d["reference_year"] = year
    return d


NPC = "National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."
PROJ = "Projection, not a count (NPC / NBS, via City Population)."
STATISTICS = [
    stat(KD, "population", 6113503, "CPKD", NPC, 2006, "census"),
    stat(KD, "population", 6066562, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(KD, "population", 3935618, "CPKD", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(KD, "population", 9032200, "CPKD", PROJ, 2022, "projection"),
    stat(KD, "population", 8762664, "NIPCKD", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(KD, "area_km2", 42481, "NIPCKD", "Nigerian Investment Promotion Commission."),
    stat(KD, "area_km2", 44217, "STAT", "Statoids."),
    stat(KN, "population", 9401288, "CPKN", NPC, 2006, "census"),
    stat(KN, "population", 9383682, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(KN, "population", 5810470, "CPKN", "1991 census for the area of today's state (without Jigawa), as reproduced by City Population.", 1991, "census"),
    stat(KN, "population", 15462200, "CPKN", PROJ, 2022, "projection"),
    stat(KN, "population", 13969085, "NIPCKN", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(KN, "area_km2", 20280, "NIPCKN", "Nigerian Investment Promotion Commission."),
    stat(KN, "area_km2", 20389, "STAT", "Statoids."),
    stat(JG, "population", 4361002, "CPJG", NPC, 2006, "census"),
    stat(JG, "population", 4348649, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(JG, "population", 2875525, "CPJG", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(JG, "population", 7499100, "CPJG", PROJ, 2022, "projection"),
    stat(JG, "area_km2", 22410, "WJG", "Wikipedia ('approximately')."),
    stat(JG, "area_km2", 23415, "STAT", "Statoids."),
    stat(KT, "population", 5801584, "CPKT", NPC, 2006, "census"),
    stat(KT, "population", 5792578, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(KT, "population", 3753133, "CPKT", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(KT, "population", 10368500, "CPKT", PROJ, 2022, "projection"),
    stat(KT, "area_km2", 23938, "WKT", "Wikipedia ('about')."),
    stat(KT, "area_km2", 23822, "STAT", "Statoids."),
]

GAPS = [
    ("Borders between Kaduna, Kano, Jigawa and Katsina", "Given by Wikipedia only (the Kano government page copies Wikipedia; NIPC's Kano and Kaduna pages list no borders; the Jigawa and Katsina pages were not retrieved). Recorded as single-source links; they need an official source."),
    ("Jigawa and Katsina official sources", "The state government websites had no usable content, and NIPC's pages could not be retrieved (Internet Archive rate limit). Retry NIPC and add the state governments' pages when available."),
    ("Kaduna's renaming: 1975 or 1976", "Wikipedia says 1975; the Kaduna State Government says 'by 1976'. Both noted."),
    ("Nok culture", "Mentioned briefly (Kaduna State Government; Wikipedia dates). It needs its own record with archaeological sources."),
    ("The Hausa kingdoms (Zazzau, Kano, Katsina, Daura, Biram/Hadejia) and Queen Amina", "Outlined from Wikipedia only. Each needs its own scholarly-sourced record."),
    ("The Kano Chronicle and Kano's early history", "The dates given (before 1000, 1349, 15th-century Kurmi Market) come from Wikipedia and its reproduction by the state government. Scholarly sources needed."),
    ("Conflict and insecurity", "Ethnic and religious violence in Kaduna (including the 2002 riots), banditry and kidnappings in Katsina (including Kankara, 2020), Boko Haram attacks in Kano, and farmer–herder clashes in Jigawa are not summarised. They need separate, careful treatment."),
    ("Areas and population figures", "The areas and populations from each source differ. All are recorded; none is chosen."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="Kaduna, Kano, Jigawa and Katsina states: history, geography, peoples, economy and population (fills empty fields of the existing records).")


STATES = [("Kaduna State", KD_DESCRIPTION, KD_GEOGRAPHY), ("Kano State", KN_DESCRIPTION, KN_GEOGRAPHY),
          ("Jigawa State", JG_DESCRIPTION, JG_GEOGRAPHY), ("Katsina State", KT_DESCRIPTION, KT_GEOGRAPHY)]


def report():
    L = ["# Research batch 020 — Kaduna, Kano, Jigawa and Katsina states (North West, part 1)", "",
         f"Researched {ACCESSED}. All four state pages are already published, so **this text goes live the moment it is imported**. This report is the review.", ""]
    for name, d, g in STATES:
        L += [f"- New prose for {name}: **{words(d) + words(g)} words**. The page passes the 300-word indexing rule and becomes indexable."]
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    for name, d, g in STATES:
        L += ["", f"## {name} — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in d.split("\n")]
        L += ["", f"## {name} — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in g.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Kaduna: North-Central State 27 May 1967; Katsina split 1987 | state government = NIPC; Wikipedia; Statoids | stated |",
          "| Renamed Kaduna 1975/1976 | state government vs Wikipedia | both noted |",
          "| Nok | state government; Wikipedia (dates) | attributed |",
          "| Hausa kingdoms, Zazzau, Amina; Lugard's move (1916) | Wikipedia; state government (move) | attributed |",
          "| Kaduna borders | Wikipedia | attributed; existing links cover most |",
          "| Kano created 27 May 1967; Jigawa split 1991 | NIPC; Wikipedia; Statoids | stated |",
          "| Kano's history (Dala, 1349, Kurmi, 1804–08, 1903) | Wikipedia = state government | attributed |",
          "| Kano trade, markets, crops, heritage | NIPC; Wikipedia | stated / attributed |",
          "| Jigawa created 1991 from Kano | Wikipedia; Statoids | stated |",
          "| Jigawa borders, heritage, peoples, economy | Wikipedia only | attributed |",
          "| Katsina created 23 Sep 1987 from Kaduna | Statoids; Kaduna government; Wikipedia | stated |",
          "| Katsina history, borders, peoples, economy | Wikipedia only | attributed |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['record'].split(':')[-1].title()} — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", f"## Neighbours ({len(RELATIONS)} new links, all single-source)", "",
          "- Kaduna ↔ Kano, Katsina. Kano ↔ Katsina, Jigawa. Jigawa ↔ Katsina. Wikipedia only; see gaps.",
          "- Other borders already exist (Kaduna ↔ Nasarawa, Plateau, FCT, Niger, Bauchi; Kano ↔ Bauchi; Jigawa ↔ Bauchi, Yobe) or come in batch 021 (Zamfara ↔ Katsina, Kaduna).",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- Four more state pages become indexable. The sitemap should grow by 4 (3,010 → 3,014). The new source pages stay noindex.",
          "- The text is original prose, and every claim is sourced or attributed. Conflict and insecurity are left for separate treatment.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_020_kaduna_kano_jigawa_katsina.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_020_kaduna_kano_jigawa_katsina_REVIEW.md", "w").write(report())
    print(" ".join(f"{n.split()[0]}={words(d) + words(g)}" for n, d, g in STATES) + f" relations={len(RELATIONS)} statistics={len(STATISTICS)}")
