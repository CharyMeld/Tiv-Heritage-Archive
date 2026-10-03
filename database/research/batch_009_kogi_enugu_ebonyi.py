"""
Research batch 009 — Kogi, Enugu and Ebonyi states: history, geography, peoples and
economy (researched 2026-09-24/25). Same method as batches 004, 007 and 008:

Fills the EMPTY description and geography fields of the three existing, published state
records (never overwrites), adds dated population/area figures side by side, and sourced
'neighbours' links (links that already exist are not duplicated: Benue–Kogi, Benue–Enugu,
Benue–Ebonyi, Nasarawa–Kogi, Cross River–Ebonyi).
  * facts with two or more independent sources are stated plainly;
  * single-source claims are attributed in the text;
  * NIPC's Kogi page could not be retrieved (Internet Archive copies are empty);
  * the Ebonyi crop list is word-for-word the same in the state government, NIPC and
    Wikipedia pages (one lineage) — it is attributed, not counted three times.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "KGGOV": dict(source_type="official_website", title="About Kogi", organisation="Kogi State Government",
                  url="https://kogistate.gov.ng/about-kogi/", verification_status="needs_corroboration",
                  notes="Created 27 August 1991 from parts of Kwara and Benue states; formerly Kabba Province (Igala, Kabba, Ebira and Kogi divisions), divided by the 1976 state creation; Lokoja at the Niger–Benue confluence, a former colonial administrative headquarters; Mount Patti. Its border list includes Plateau State, which no other source gives."),
    "ENGOV": dict(source_type="official_website", title="Our History", organisation="Enugu State Government",
                  url="https://enugustate.gov.ng/our-history/", verification_status="needs_corroboration",
                  notes="Igbo majority; coal discovered 1909, mining from 1915 ('Coal City'); Civil War 1967–70; capital of East Central State, then part of Anambra; Enugu State created 27 August 1991; New Yam and Mmanwu festivals."),
    "EBGOV": dict(source_type="official_website", title="About Ebonyi", organisation="Ebonyi State Government",
                  url="https://ebonyistate.gov.ng/about-ebonyi/", verification_status="needs_corroboration",
                  notes="Predominantly Igbo, with other communities including the Okpoto and Ntezi; created 1996 from parts of Enugu and Abia; 13 LGAs; crops; minerals; salt at Okposi and Uburu ('Salt of the Nation'); dialects and languages; area 5,935 km². Its border list includes Imo State, which is not corroborated."),
    "NIPCEN": dict(source_type="official_website", title="Enugu State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/enugu-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "NIPCEB": dict(source_type="official_website", title="Ebonyi State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/ebonyi-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "WKG": dict(source_type="encyclopedia", title="Kogi State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Kogi_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: creation 1991; ten neighbouring states; 'Confluence State'; peoples and languages; Nupe and Igala kingdoms; Lokoja as capital of the Northern Nigeria Protectorate until 1903; rainfall; 2012 floods; crops, minerals, Ajaokuta steel and Obajana cement; tourism."),
    "WEN": dict(source_type="encyclopedia", title="Enugu State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Enugu_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: name ('top of the hill'); coal 1909 (Albert Kitson), first shipment 1914; township 1917, renamed 1928; Iva Valley 1949; East Central State 1967; Biafran capital until October 1967; Anambra 1976; Enugu State 1991; Ebonyi 1996; neighbours; Udi–Nsukka plateau; Ekulu River; crops; minerals; Oji River power station."),
    "WEB": dict(source_type="encyclopedia", title="Ebonyi State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Ebonyi_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: named after the Abonyi (Aboine) River; formed 1996 from parts of Abia and Enugu; earlier East Central, Anambra and Imo states; neighbours; rivers; climate; Korring (Oring) speech; crops by area; salt lakes; tourism."),
    "CPKG": dict(source_type="dataset", title="Kogi (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA023__kogi/", verification_status="needs_corroboration",
                 notes="Census 1991-11-26: 2,147,756; census 2006-03-21: 3,314,043; projection 2022-03-21: 4,466,800 (National Population Commission / National Bureau of Statistics)."),
    "CPEN": dict(source_type="dataset", title="Enugu (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA014__enugu/", verification_status="needs_corroboration",
                 notes="Census 1991-11-26: 2,125,068; census 2006-03-21: 3,267,837; projection 2022-03-21: 4,690,100 (National Population Commission / National Bureau of Statistics)."),
    "CPEB": dict(source_type="dataset", title="Ebonyi (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA011__ebonyi/", verification_status="needs_corroboration",
                 notes="Census 2006-03-21: 2,176,947; projection 2022-03-21: 3,242,500 (National Population Commission / National Bureau of Statistics). Its 1991 figure (1,029,312) is not recorded: it is less than half the 2006 count and looks inconsistent."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration",
                 notes="Reused (batch 001). 2006 census table: Kogi 3,278,487, 29,063 km²; Enugu 3,257,298, 7,560 km²; Ebonyi 2,173,501, 6,342 km². Chronology: 1991 Kogi from parts of Benue and Kwara, Enugu from Anambra; 1996 Ebonyi from parts of Abia and Enugu."),
}

KG_DESCRIPTION = """Kogi State was created on 27 August 1991 by the military government of Ibrahim Babangida. The Kogi State Government and Statoids describe it as formed from parts of Kwara and Benue states. Wikipedia adds a small part of Niger State. Its capital is Lokoja, and it is grouped in the North Central geopolitical zone. Wikipedia gives its nickname as the "Confluence State", because the Niger and the Benue meet beside Lokoja.

The state government traces the state to the colonial Kabba Province, which was made up of the Igala, Kabba, Ebira and Kogi divisions, and says its people were divided between states in 1976. Wikipedia describes the earlier history. Much of the west was part of the Nupe Kingdom until the Fulani jihad of the early 1800s brought it under the Sokoto Caliphate, while the east was the Igala Kingdom. Lokoja was a British administrative centre, and Wikipedia says it was the capital of the Northern Nigeria Protectorate until 1903. Mount Patti overlooks the town.

According to Wikipedia, Kogi borders ten states, more than any other state in Nigeria. The state government and Wikipedia both list Niger State and the Federal Capital Territory to the north, Nasarawa and Benue to the east, Enugu and Anambra to the south-east, Edo and Ondo to the south-west and Kwara to the west. Wikipedia also lists Ekiti to the west. The state has twenty-one local government areas.

Wikipedia names the Igala, Ebira and Okun (a Yoruba group) as the three main peoples. Others include Nupe-speaking groups such as the Bassa Nge, Kakanda and Kupa, and the Bassa, Oworo, Ogori, Magongo and Idoma. It describes the state as religiously mixed, with Muslims, Christians and followers of traditional religion.

Farming is the main occupation. Wikipedia lists coffee, cocoa, oil palm, cashew, groundnuts, maize, cassava, yams, rice and melon, and minerals including coal, limestone, iron ore and tin. The state is home to the Ajaokuta Steel Company and the Obajana cement factory.

The 2006 census counted 3,314,043 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

KG_GEOGRAPHY = """The Niger enters the state from the north-west and the Benue from the north-east. They meet near Lokoja and flow south through the middle of the state as one river. Wikipedia places the state in the Guinean forest–savanna mosaic, with annual rainfall of about 1,100 to 1,300 millimetres, a rainy season from April to October and a dusty harmattan in the dry season. Towns along the two rivers are prone to flooding. Wikipedia records severe floods in 2012 and 2022 in the nine local government areas on the Niger and Benue.

Statoids gives the area as 29,063 square kilometres. No second figure was found."""

EN_DESCRIPTION = """Enugu State was created on 27 August 1991 by the military government of Ibrahim Babangida, when the eastern part of Anambra State was separated. It is named after its capital, Enugu. The name is usually explained as "top of the hill". The state is grouped in the South East geopolitical zone, and most of its people are Igbo.

The modern city grew from coal mining. The state government, the Nigerian Investment Promotion Commission and Wikipedia all say coal was found in the area in 1909, and the state is still called the "Coal City State". Wikipedia credits the find to a British mining engineer, Albert Kitson, in the Udi ridge. The state government dates mining from 1915, while Wikipedia says the first coal was shipped to Britain in 1914. According to Wikipedia, Enugu became a township in 1917 and took its present name in 1928. It adds that the shooting of striking coal miners at Iva Valley in 1949 made the city a symbol of resistance to colonial rule.

Enugu was the capital of the Eastern Region. In 1967 the region was replaced by East Central State, with Enugu as its capital. Wikipedia says the city was the first capital of Biafra during the Civil War of 1967 to 1970, until federal forces took it in October 1967. In 1976 the area became part of Anambra State, and in 1991 Enugu State was formed from its eastern part. In 1996 part of the state was joined with part of Abia State to create Ebonyi State.

Enugu State borders Kogi State to the north-west, Benue State to the north-east, Ebonyi State to the east, Abia State to the south and Anambra State to the west. It has seventeen local government areas: Aninri, Awgu, Enugu East, Enugu North, Enugu South, Ezeagu, Igbo Etiti, Igbo Eze North, Igbo Eze South, Isi Uzo, Nkanu East, Nkanu West, Nsukka, Oji River, Udenu, Udi and Uzo-Uwani.

The state government names the New Yam festival and the Mmanwu (masquerade) festival among its cultural traditions. For farming, the Nigerian Investment Promotion Commission and Wikipedia both name yams, rice, oil palm and cassava. The Commission calls the state Nigeria's largest producer of melon seed. Coal, lead and zinc are found in the state.

The 2006 census counted 3,267,837 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

EN_GEOGRAPHY = """The state lies at the foot of the Udi plateau, which Wikipedia calls the Udi–Nsukka plateau. Its highest point, about 592 metres, is on the ridge of the Enugu escarpment. The Ekulu River flows through the city of Enugu. Wikipedia describes a tropical wet-and-dry climate, with most of the state in the Guinean forest–savanna mosaic.

The area is given as 7,534 square kilometres by the Nigerian Investment Promotion Commission and 7,560 square kilometres by Statoids. Both figures are recorded below."""

EB_DESCRIPTION = """Ebonyi State was created on 1 October 1996 by the military government of Sani Abacha, from parts of Enugu and Abia states. Wikipedia says it is named after the Abonyi (Aboine) River. Its capital is Abakaliki, and it is grouped in the South East geopolitical zone.

Wikipedia outlines how the area reached its present form. It was part of the Eastern Region, then of East Central State from 1967. In 1976 its north went to Anambra State and its south to Imo State. In 1991 those parts became part of the new Enugu and Abia states, and in 1996 they were joined to form Ebonyi.

Ebonyi borders Benue State to the north, Enugu State to the west, Cross River State to the east and Abia State to the south-west. It has thirteen local government areas: Abakaliki, Afikpo North, Afikpo South, Ebonyi, Ezza North, Ezza South, Ikwo, Ishielu, Ivo, Izzi, Ohaozara, Ohaukwu and Onicha.

The state government describes the people as predominantly Igbo, with other communities including the Okpoto and Ntezi. Igbo dialect areas named by the state government include Izzi, Ezza, Ikwo, Mgbo, Edda and Afikpo. The state government also lists Kukele, Mbembe and Oring, which are separate languages. Wikipedia notes that Korring (Oring) is spoken around Ntezi, Okpoto and Effium, and that it is related to the speech of the Ukelle of Cross River State and the Ufia of Benue State.

The state is known as the "Salt of the Nation" for the salt lakes at Okposi and Uburu, according to the state government, the Nigerian Investment Promotion Commission and Wikipedia. The three sources give the same list of crops: rice, yams, potatoes, maize, beans and cassava. Wikipedia names Ikwo for rice and Izzi for yams, and says Ntezi is known for hand-made baskets. Wikipedia locates deposits of lead, zinc and limestone around Abakaliki. The state government and the Commission also list lead, crude oil and natural gas.

The 2006 census counted 2,176,947 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

EB_GEOGRAPHY = """The Cross River and its tributary the Aloma flow along the state's south-eastern and eastern borders. Three other Cross River tributaries, the Abonyi (Aboine), the Asu and the Eze Aku, run through the interior. Wikipedia describes a humid tropical climate, with a rainy season of about eight months and a dry season of about four, and Guinean forest–savanna mosaic across most of the state. Wikipedia names the Abakaliki Green Lake, the Uburu salt lake and the Unwana and Ikwo beaches among places visitors go.

The area is given as 5,935 square kilometres by the state government, 6,400 by the Nigerian Investment Promotion Commission and 6,342 by Statoids. All three figures are recorded below."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


KG, EN, EB = "@admin_units:state:kogi", "@admin_units:state:enugu", "@admin_units:state:ebonyi"

UPDATES = [
    dict(ref=KG, fields=dict(description=KG_DESCRIPTION, geography_notes=KG_GEOGRAPHY),
         srcs=[("KGGOV", "Created 27 August 1991 from parts of Kwara and Benue; Kabba Province; Lokoja at the confluence; Mount Patti; neighbours"),
               ("WKG", "Creation; ten neighbours; 'Confluence State'; Nupe and Igala kingdoms; Lokoja capital of the Northern Nigeria Protectorate until 1903; peoples; religion; crops; minerals; industries; rivers; climate; floods"),
               ("STAT", "1991: Kogi from parts of Benue and Kwara; area 29,063 km²"),
               ("CPKG", "2006 census 3,314,043 (National Population Commission)")]),
    dict(ref=EN, fields=dict(description=EN_DESCRIPTION, geography_notes=EN_GEOGRAPHY),
         srcs=[("NIPCEN", "Created 27 August 1991 by Babangida; named after the capital ('top of the hill'); coal 1909; neighbours; 17 LGAs; crops; melon seed; minerals; area 7,534 km²"),
               ("ENGOV", "Igbo majority; coal 1909, mining from 1915; East Central State; Anambra; 1991; festivals"),
               ("WEN", "Name; coal and Albert Kitson; township 1917, renamed 1928; Iva Valley 1949; Biafran capital; 1976; 1991; 1996; neighbours; plateau, escarpment, Ekulu River; crops; minerals"),
               ("STAT", "1991: Enugu from Anambra; 'top of the hill'; area 7,560 km²"),
               ("CPEN", "2006 census 3,267,837 (National Population Commission)")]),
    dict(ref=EB, fields=dict(description=EB_DESCRIPTION, geography_notes=EB_GEOGRAPHY),
         srcs=[("NIPCEB", "Created 1 October 1996 by Abacha from parts of Enugu and Abia; mostly Igbo; 'Salt of the Nation'; 13 LGAs; crops; minerals; area 6,400 km²"),
               ("EBGOV", "Created 1996 from parts of Enugu and Abia; Igbo, Okpoto and Ntezi; dialects and languages; salt at Okposi and Uburu; crops; minerals; 13 LGAs; area 5,935 km²"),
               ("WEB", "Named after the Abonyi River; earlier states; neighbours; Korring; rivers; climate; crops by area; minerals around Abakaliki; tourism"),
               ("STAT", "1996: Ebonyi from parts of Abia and Enugu; area 6,342 km²"),
               ("CPEB", "2006 census 2,176,947 (National Population Commission)")]),
]

RELATIONS = []
for n in ["niger", "federal-capital-territory", "anambra", "ondo", "kwara", "edo"]:
    RELATIONS.append(dict(frm=KG, type="neighbours", to=f"@admin_units:{'federal_capital_territory' if n.startswith('federal') else 'state'}:{n}",
                          source="KGGOV", evidence="multiple_sources",
                          notes="Listed as a neighbouring state by the Kogi State Government and by Wikipedia."))
RELATIONS.append(dict(frm=KG, type="neighbours", to="@admin_units:state:ekiti", source="WKG", evidence="single_reliable_source",
                      notes="Listed by Wikipedia (about 92 km of shared border); the Kogi State Government's list omits it."))
for n in ["kogi", "anambra", "abia", "ebonyi"]:
    RELATIONS.append(dict(frm=EN, type="neighbours", to=f"@admin_units:state:{n}", source="NIPCEN", evidence="multiple_sources",
                          notes="Listed as a neighbouring state by the Nigerian Investment Promotion Commission and by Wikipedia."))
RELATIONS.append(dict(frm=EB, type="neighbours", to="@admin_units:state:abia", source="EBGOV", evidence="multiple_sources",
                      notes="Listed as a neighbouring state by the Ebonyi State Government and by Wikipedia."))


def stat(record, metric, value, source, notes, year=None, method="other"):
    d = dict(record=record, metric=metric, value_low=value, method=method, source=source, evidence="single_reliable_source", notes=notes)
    if year:
        d["reference_year"] = year
    return d


NPC = "National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."
STATISTICS = [
    stat(KG, "population", 3314043, "CPKG", NPC, 2006, "census"),
    stat(KG, "population", 3278487, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(KG, "population", 2147756, "CPKG", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(KG, "population", 4466800, "CPKG", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(KG, "area_km2", 29063, "STAT", "Statoids."),
    stat(EN, "population", 3267837, "CPEN", NPC, 2006, "census"),
    stat(EN, "population", 3257298, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(EN, "population", 2125068, "CPEN", "1991 census for the area of today's state (before Ebonyi was separated in 1996), as reproduced by City Population.", 1991, "census"),
    stat(EN, "population", 4690100, "CPEN", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(EN, "population", 4683887, "NIPCEN", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(EN, "area_km2", 7534, "NIPCEN", "Nigerian Investment Promotion Commission."),
    stat(EN, "area_km2", 7560, "STAT", "Statoids."),
    stat(EB, "population", 2176947, "CPEB", NPC, 2006, "census"),
    stat(EB, "population", 2173501, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(EB, "population", 3242500, "CPEB", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(EB, "population", 3046287, "NIPCEB", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(EB, "area_km2", 5935, "EBGOV", "Ebonyi State Government."),
    stat(EB, "area_km2", 6400, "NIPCEB", "Nigerian Investment Promotion Commission."),
    stat(EB, "area_km2", 6342, "STAT", "Statoids."),
]

GAPS = [
    ("Kogi: was part of Niger State included in 1991?", "Wikipedia says Kogi was formed from parts of Benue, Kwara and Niger states. The Kogi State Government and Statoids name only Benue and Kwara. Recorded as Wikipedia's claim; needs the 1991 creation decree."),
    ("Kogi–Plateau border", "The Kogi State Government's border list includes Plateau State. No other source does, and the two states do not appear to touch. Not recorded as a link."),
    ("Enugu–Imo and Ebonyi–Imo borders", "The Nigerian Investment Promotion Commission lists Imo as a neighbour of Enugu, and the Ebonyi State Government lists Imo as a neighbour of Ebonyi. Wikipedia lists neither. Not recorded as links."),
    ("Nri and Nsukka in Enugu's history", "The Enugu State Government's history names the Nri and Nsukka kingdoms, and Wikipedia says the area was part of the Kingdom of Nri and the Aro Confederacy. Nri's centre is in Anambra State. Left out until scholarly sources are found."),
    ("Enugu and the Civil War", "Only the capital's fall in October 1967 (Wikipedia) is mentioned. The war needs its own careful, well-sourced treatment. The state government's wording ('Republic of South East') is not used."),
    ("Kogi's Lokoja and the name 'Nigeria'", "Wikipedia repeats the story that Flora Shaw coined the name 'Nigeria' at Lokoja. It is not recorded; the usual source is her 1897 article in The Times, which does not place it at Lokoja."),
    ("Kogi LGA spelling: Olamabolo / Olamaboro", "The LGA record (batch 002, from the Constitution) is spelt 'Olamabolo'; the common form is 'Olamaboro'. Owner decision, as with Ogbomosho South and Yenagoa."),
    ("Korring (Oring) links", "Wikipedia relates Korring in Ebonyi to Ukelle (Cross River) and Ufia (Benue, batch 005). Not recorded as a language link without a linguistic source such as Glottolog."),
    ("Peoples of Kogi, Enugu and Ebonyi as national records", "Igala, Ebira, Okun, Nupe, Bassa, Igbo, Okpoto and the others named here are not yet records."),
    ("Ebonyi 1991 population", "City Population's 1991 figure for today's Ebonyi (1,029,312) is less than half the 2006 count. Not recorded."),
    ("Areas and population figures", "The areas and populations from each source differ. All are recorded; none is chosen."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="Kogi, Enugu and Ebonyi states: history, geography, peoples, economy and population (fills empty fields of the existing records).")


STATES = [("Kogi State", KG_DESCRIPTION, KG_GEOGRAPHY), ("Enugu State", EN_DESCRIPTION, EN_GEOGRAPHY), ("Ebonyi State", EB_DESCRIPTION, EB_GEOGRAPHY)]


def report():
    L = ["# Research batch 009 — Kogi, Enugu and Ebonyi states", "",
         f"Researched {ACCESSED}. All three state pages are already published, so **this text goes live the moment it is imported**. This report is the review.", ""]
    for name, d, g in STATES:
        L += [f"- New prose for {name}: **{words(d) + words(g)} words**. The page passes the 300-word indexing rule and becomes indexable."]
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    for name, d, g in STATES:
        L += ["", f"## {name} — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in d.split("\n")]
        L += ["", f"## {name} — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in g.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Kogi created 27 Aug 1991 from parts of Kwara and Benue | state government; Statoids; Wikipedia; batch 001 | stated; Niger State part attributed + gap |",
          "| 'Confluence State'; Niger–Benue confluence at Lokoja | Wikipedia (nickname); state government + Wikipedia (confluence) | nickname attributed |",
          "| Kabba Province and its four divisions | state government only | attributed |",
          "| Nupe and Igala kingdoms; Lokoja protectorate capital to 1903 | Wikipedia only | attributed |",
          "| Kogi's neighbours | state government + Wikipedia (Ekiti: Wikipedia only; Plateau: state government only) | 6 links multiple, Ekiti single, Plateau gap |",
          "| Kogi peoples, religion, crops, minerals, industries | Wikipedia only | attributed |",
          "| Enugu created 27 Aug 1991 from Anambra, by Babangida | NIPC; state government; Wikipedia; Statoids | stated |",
          "| Name 'top of the hill' | NIPC; Statoids; Wikipedia | stated |",
          "| Coal found 1909; 'Coal City' | state government; NIPC; Wikipedia | stated; Kitson, 1914/1915 attributed |",
          "| Township 1917, renamed 1928; Iva Valley 1949; Biafran capital | Wikipedia only | attributed |",
          "| East Central State 1967; Anambra 1976 | state government; Wikipedia | stated |",
          "| Enugu's neighbours; 17 LGAs | NIPC; Wikipedia; batch 002 | 4 links multiple; Imo gap |",
          "| New Yam and Mmanwu festivals | state government only | attributed |",
          "| Crops (yams, rice, oil palm, cassava); coal, lead, zinc | NIPC; Wikipedia | stated; melon seed attributed to NIPC |",
          "| Ebonyi created 1 Oct 1996 by Abacha, from Enugu and Abia | NIPC; state government; Wikipedia; Statoids; batch 001 | stated |",
          "| Named after the Abonyi River; earlier states | Wikipedia only | attributed |",
          "| Ebonyi's neighbours; 13 LGAs | state government; Wikipedia; NIPC (LGAs) | Abia link multiple; Imo gap |",
          "| Igbo majority; Okpoto, Ntezi; dialects; Kukele, Mbembe, Oring | state government (+ NIPC: mostly Igbo) | attributed; non-Igbo languages not called Igbo |",
          "| Korring and its relatives | Wikipedia only | attributed + gap |",
          "| 'Salt of the Nation'; Okposi and Uburu | state government; NIPC; Wikipedia | stated |",
          "| Crop list | state government = NIPC = Wikipedia (same wording) | attributed as one list |",
          "| Lead, zinc, limestone at Abakaliki; crude oil, gas | Wikipedia; NIPC and state government | attributed |",
          "| Rivers, climate, tourism (all three states) | Wikipedia only | attributed |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['record'].split(':')[-1].title()} — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", "## Neighbours (12 new links)", "",
          "- Kogi ↔ Niger, Federal Capital Territory, Anambra, Ondo, Kwara, Edo (two sources); Ekiti (Wikipedia only). Links with Benue and Nasarawa already exist; Enugu is added from Enugu's side.",
          "- Enugu ↔ Kogi, Anambra, Abia, Ebonyi (two sources). The Benue link already exists.",
          "- Ebonyi ↔ Abia (two sources). Links with Benue and Cross River already exist.",
          "- Not recorded: Kogi–Plateau, Enugu–Imo, Ebonyi–Imo (one source each, see gaps).",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- Three more state pages become indexable, making eight in all. The sitemap should grow by 3 (2,983 → 2,986). The new source pages stay noindex.",
          "- The text is original prose. Every claim is sourced or attributed, and the tone is neutral. The Civil War is mentioned only as a date and is left for its own careful treatment.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_009_kogi_enugu_ebonyi.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_009_kogi_enugu_ebonyi_REVIEW.md", "w").write(report())
    print(" ".join(f"{n.split()[0]}={words(d) + words(g)}" for n, d, g in STATES) + f" relations={len(RELATIONS)} statistics={len(STATISTICS)}")
