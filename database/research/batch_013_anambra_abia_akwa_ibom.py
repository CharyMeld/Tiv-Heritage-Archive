"""
Research batch 013 — Anambra, Abia and Akwa Ibom states: history, geography, peoples and
economy (researched 2026-09-25). Same method as batches 004 and 007–009:

Fills the EMPTY description and geography fields of the three existing, published state
records (never overwrites), adds dated population/area figures side by side, and sourced
'neighbours' links (existing links are not duplicated: Kogi–Anambra, Enugu–Anambra,
Enugu–Abia, Ebonyi–Abia, Cross River–Abia, Cross River–Akwa Ibom).
  * facts with two or more independent sources are stated plainly;
  * single-source claims are attributed in the text;
  * promotional claims on the government pages ("4500 BC", "first set of God's creation",
    lists of "firsts") are not used.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "ANGOV": dict(source_type="official_website", title="History", organisation="Anambra State Government",
                  url="https://anambrastate.gov.ng/history/",
                  archive_reference="Internet Archive snapshot 20260726150819: http://web.archive.org/web/20260726150819/https://anambrastate.gov.ng/history/",
                  verification_status="needs_corroboration",
                  notes="Created 27 August 1991 by Ibrahim Babangida; named after the Anambra (Omambala) River, a tributary of the Niger; area 4,416 km²; borders Abia, Delta, Enugu, Imo and Kogi; estimated population 5,084,195 (2015 estimate); crops, minerals, industries; Awka blacksmithing; Onitsha market; Nnewi spare parts; Igbo mother tongue; a small Igala-speaking group in Anambra West LGA."),
    "ABGOV": dict(source_type="official_website", title="About Abia", organisation="Abia State Government",
                  url="https://abiastate.gov.ng/about-abia/",
                  archive_reference="Internet Archive snapshot 20260301230242: http://web.archive.org/web/20260301230242/https://abiastate.gov.ng/about-abia/",
                  verification_status="needs_corroboration",
                  notes="'God's Own State'; created 27 August 1991 from the former Imo State; capital Umuahia, commercial centre Aba; the Aba Women's Riot of 1929 against colonial taxation; Ariaria market; predominantly Igbo; Akwete cloth of Ukwa East; Ekpe and masquerade festivals; borders Enugu, Imo, Ebonyi, Rivers and Akwa Ibom; oil palm, yam, cassava; crude oil and gas."),
    "AKGOV": dict(source_type="official_website", title="About Akwa Ibom", organisation="Akwa Ibom State Government",
                  url="https://akwaibomstate.gov.ng/about-akwa-ibom/", verification_status="needs_corroboration",
                  notes="Created 23 September 1987; estimated population about 7,200,000 (no year); area 7,249 km²; bounded by Rivers, Cross River, Abia and the Gulf of Guinea; 129 km coastline from Oron to Ikot Abasi; 31 LGAs, capital Uyo; Qua Iboe Church founded by Rev. Samuel Bill in 1887 at Ibeno; Mary Slessor lived and died at Use Ikot Oku; women killed at Ikot Abasi in 1929."),
    "NIPCAN": dict(source_type="official_website", title="Anambra State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/anambra-state/",
                   archive_reference="Internet Archive snapshot 20250121015556: http://web.archive.org/web/20250121015556/https://www.nipc.gov.ng/nigeria-states/anambra-state/",
                   verification_status="needs_corroboration",
                   notes="Created 27 August 1991 out of the old Anambra State; capital Awka; Nri civilisation and bronzes around 800 AD; 'Land of Beauty'; Onitsha market; Ogbunike Cave, Agulu Lake, Igbo-Ukwu excavations; area 4,865 km²; 21 LGAs; population 5,846,198 (no year); crops and minerals."),
    "NIPCAB": dict(source_type="official_website", title="Abia State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/abia-state",
                   archive_reference="Internet Archive snapshot 20251214101851: http://web.archive.org/web/20251214101851/https://www.nipc.gov.ng/nigeria-states/abia-state",
                   verification_status="needs_corroboration",
                   notes="Name as an acronym of Aba, Bende, Isuikwuato and Afikpo; 'God's Own State'; carved out of Imo State on 27 August 1991; bounded by Anambra, Enugu, Ebonyi, Imo, Cross River, Akwa Ibom and Rivers; Aba shoe and garment factories; area 4,900 km²; 17 LGAs; population 3,934,157 (no year); crops and minerals."),
    "NIPCAK": dict(source_type="official_website", title="Akwa Ibom State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/akwa-ibom-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "WAN": dict(source_type="encyclopedia", title="Anambra State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Anambra_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: name from the Omambala River; old Anambra State 1976 from East Central State (capital Enugu), divided in 1991; Igbo-Ukwu finds from the 9th century; Nri; Uli airstrip in the Civil War; neighbours (Delta, Imo, Rivers for 4 km, Abia, Enugu, Kogi; its geography section also names Edo); crops; oil since 2012."),
    "WAB": dict(source_type="encyclopedia", title="Abia State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Abia_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: name as an acronym of Aba, Bende, Isuikwuato and Arochukwu; created 27 August 1991 from Imo; Aro Confederacy and Anglo-Aro War; East Central State; Imo 1976; 1996 part to Ebonyi; neighbours; Imo and Aba rivers; crops; oil and gas; Aba industries; Arochukwu, National War Museum, Akwete cloth."),
    "WAK": dict(source_type="encyclopedia", title="Akwa Ibom State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Akwa_Ibom_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: name from the Qua Iboe River; split from Cross River 1987; Enyong Division 1904 (HQ Ikot Ekpene); Ibibio State Union; South-Eastern State 1967; peoples (Ibibio, Annang, Oron and others) and languages by LGA; neighbours; oil and gas; crops; Stubb Creek Forest Reserve."),
    "CPAN": dict(source_type="dataset", title="Anambra (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA004__anambra/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,796,475; census 2006: 4,177,828; projection 2022: 5,953,500 (National Population Commission / National Bureau of Statistics)."),
    "CPAB": dict(source_type="dataset", title="Abia (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA001__abia/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,338,487; census 2006: 2,845,380; projection 2022: 4,143,100 (National Population Commission / National Bureau of Statistics)."),
    "CPAK": dict(source_type="dataset", title="Akwa Ibom (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA003__akwa_ibom/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,409,613; census 2006: 3,902,051; projection 2022: 4,979,400 (National Population Commission / National Bureau of Statistics)."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration",
                 notes="Reused (batch 001). 2006 census table: Abia 2,833,999, 4,857 km²; Akwa Ibom 3,920,208, 6,788 km²; Anambra 4,182,032, 4,761 km². Chronology: 1987 Akwa Ibom split from Cross River; 1991 Abia split from Imo, Enugu split from Anambra."),
}

AN_DESCRIPTION = """Anambra State takes its name from the Anambra River, whose Igbo name is Omambala. The river is a tributary of the Niger. The first Anambra State was created in 1976 from part of East Central State, with Enugu as its capital. On 27 August 1991 the military government of Ibrahim Babangida divided it, creating Enugu State in the east. The present Anambra State, with its capital at Awka, dates from that division. The Anambra State Government and the Nigerian Investment Promotion Commission both give 27 August 1991 as its creation date. The state is grouped in the South East geopolitical zone.

The area has a long history. Archaeological finds at Igbo-Ukwu, including elaborate bronze castings, date to around the 9th century, according to Wikipedia and the Commission. Both link them to the Kingdom of Nri, which the Commission calls the cradle of Igbo civilisation. During the Civil War of 1967 to 1970, Wikipedia notes, relief flights for Biafra landed at the Uli airstrip in the state.

Anambra borders Kogi State to the north, Enugu State to the east, Abia State to the south-east, Imo State to the south and Delta State to the west, across the Niger. Wikipedia adds a short border with Rivers State in the south. The state has twenty-one local government areas.

Most of the people are Igbo, and Igbo is the main language. The state government notes a small Igala-speaking community in Anambra West local government area. Anambra is one of the most densely populated states in Nigeria. Onitsha on the Niger is a major trading city. The state government calls its market the largest in West Africa, and the Commission calls it the largest in Africa. Nnewi is known for motor spare parts and manufacturing, and Awka for its blacksmiths, according to the state government.

The state government and the Commission both list oil palm, rice, maize and cassava among the main crops. Minerals include kaolin, gypsum, lead and iron ore, and the state has natural gas and crude oil. Wikipedia dates commercial oil production in the state to 2012.

The 2006 census counted 4,177,828 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

AN_GEOGRAPHY = """The state lies on the eastern plains of the Niger. The state government describes rolling savannah dotted with hills, lakes, forests and caves. The Anambra River flows through the state to join the Niger. The Commission names the Ogbunike Cave and Agulu Lake among its best-known natural sites, and the Igbo-Ukwu excavations among its heritage sites.

The area is given as 4,416 square kilometres by the state government, 4,865 by the Nigerian Investment Promotion Commission and 4,761 by Statoids. The figures are recorded below side by side."""

AB_DESCRIPTION = """Abia State was created on 27 August 1991 by the military government of Ibrahim Babangida, from the eastern part of Imo State. Its capital is Umuahia and its main commercial city is Aba. The state is grouped in the South East geopolitical zone, and its official nickname is "God's Own State". The name is an acronym of four areas. The Nigerian Investment Promotion Commission gives them as Aba, Bende, Isuikwuato and Afikpo, while Wikipedia gives Arochukwu instead of Afikpo.

Wikipedia outlines the earlier history. Much of the area was part of the Aro Confederacy, based at Arochukwu, until the British defeated it in the Anglo-Aro War in the early 1900s. The area then became part of the Southern Nigeria Protectorate. After independence it was part of the Eastern Region, then of East Central State from 1967 and of Imo State from 1976. In 1996 part of the north-east of the state was joined with part of Enugu State to form Ebonyi State. The state government recalls the Women's War of 1929, which it calls the Aba Women's Riot, a protest led by women against colonial taxation.

Abia borders Enugu and Ebonyi states to the north, Cross River and Akwa Ibom states to the east, Rivers State to the south, Imo State to the west and Anambra State to the north-west. It has seventeen local government areas.

The people are predominantly Igbo. The state government names the Ekpe and masquerade festivals among its cultural traditions, and Akwete cloth, woven by the Akwete people of Ukwa East, as a distinctive craft. Wikipedia names the National War Museum in Umuahia among the state's visitor sites.

Aba is a centre of manufacturing and trade, known especially for shoes and garments; the state government and the Commission both highlight it, and the state government names the Ariaria market. The state also produces crude oil and natural gas. For farming, the state government, the Commission and Wikipedia all name oil palm and cassava, and the Commission and Wikipedia add rice, maize, cocoyam and cashew.

The 2006 census counted 2,845,380 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

AB_GEOGRAPHY = """The state lies in the forest belt of south-eastern Nigeria. Wikipedia places its southern tip in the Niger Delta swamp forests and the rest in the Cross–Niger transition forests, with heavy rainfall in the south. The Imo River runs along its western border and the Aba River along its southern border. Both flow on to the Atlantic through Akwa Ibom State.

The area is given as 4,900 square kilometres by the Nigerian Investment Promotion Commission, 4,902 by Wikipedia and 4,857 by Statoids. The figures are recorded below."""

AK_DESCRIPTION = """Akwa Ibom State was created on 23 September 1987, when it was separated from Cross River State. The state government, the Nigerian Investment Promotion Commission and Statoids all give this date. The state takes its name from the Qua Iboe River, which crosses it before reaching the sea. Its capital is Uyo, and it is grouped in the South South geopolitical zone.

Wikipedia outlines the earlier history. The area came under British rule as part of the Oil Rivers Protectorate from 1884 and later of Southern Nigeria. In 1904 the British formed the Enyong Division, with its headquarters at Ikot Ekpene. The Ibibio State Union was founded in the area as an organisation for development and political representation. After independence the area was part of the Eastern Region, then of South-Eastern State from 1967. That state was renamed Cross River State in 1976.

The state government records that the Qua Iboe Church was founded at Ibeno by Samuel Bill in 1887. It also notes that the Scottish missionary Mary Slessor spent much of her life at Use Ikot Oku and died there. It recalls that women were killed at Ikot Abasi during the Women's War of 1929.

Akwa Ibom borders Cross River State to the east, Abia State to the north-west and Rivers State to the west. To the south it reaches the Atlantic. The state government and the Commission both give its coastline as 129 kilometres, from Oron to Ikot Abasi. It has thirty-one local government areas.

The Commission and Wikipedia both name the Ibibio, Annang and Oron as the main peoples. The Commission adds the Eket, Ibeno, Mbo, Okobo and Andoni. Wikipedia lists more than twenty languages, among them Ibibio, Annang, Oro, Ekid, Obolo and Efik.

Both the Commission and Wikipedia describe Akwa Ibom as the country's largest producer of crude oil. Wikipedia names Eket, Esit Eket, Ibeno, Mbo, Onna, Mkpat Enin, Ikot Abasi and Eastern Obolo as its oil-producing local government areas. Farming and fishing remain important. The Commission names oil palm, cassava, yam, plantain and cocoa among the crops.

The 2006 census counted 3,902,051 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

AK_GEOGRAPHY = """The state is low-lying and coastal. Wikipedia describes mangrove forest along the coast in the far south and the Cross–Niger transition forests inland. The Imo and Cross rivers flow along its borders, while the Qua Iboe River crosses the state to the Bight of Bonny. In the south-east lies the Stubb Creek Forest Reserve.

The area is given as 7,249 square kilometres by the state government, 6,900 by the Nigerian Investment Promotion Commission and 6,788 by Statoids. The figures are recorded below."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


AN, AB, AK = "@admin_units:state:anambra", "@admin_units:state:abia", "@admin_units:state:akwa-ibom"

UPDATES = [
    dict(ref=AN, fields=dict(description=AN_DESCRIPTION, geography_notes=AN_GEOGRAPHY),
         srcs=[("ANGOV", "Created 27 August 1991; name from the Omambala River; neighbours; Igala in Anambra West; Onitsha, Nnewi, Awka; crops; minerals; area 4,416 km²"),
               ("NIPCAN", "Created 27 August 1991 out of the old Anambra State; Nri and bronzes; Onitsha market; sites; 21 LGAs; crops; minerals; area 4,865 km²"),
               ("WAN", "Old Anambra 1976 from East Central State, divided 1991; Igbo-Ukwu; Uli airstrip; neighbours incl. Rivers; oil since 2012"),
               ("STAT", "1991: Enugu split from Anambra; area 4,761 km²"), ("CPAN", "2006 census 4,177,828 (National Population Commission)")]),
    dict(ref=AB, fields=dict(description=AB_DESCRIPTION, geography_notes=AB_GEOGRAPHY),
         srcs=[("ABGOV", "Created 27 August 1991 from Imo; Umuahia, Aba; Women's War 1929; Igbo; Akwete cloth; Ekpe; neighbours; crops; oil and gas"),
               ("NIPCAB", "Acronym (Afikpo); 'God's Own State'; created 27 August 1991 from Imo; neighbours; Aba industries; 17 LGAs; crops; area 4,900 km²"),
               ("WAB", "Acronym (Arochukwu); Aro Confederacy; East Central State; Imo 1976; Ebonyi 1996; rivers; crops; National War Museum"),
               ("STAT", "1991: Abia split from Imo; area 4,857 km²"), ("CPAB", "2006 census 2,845,380 (National Population Commission)")]),
    dict(ref=AK, fields=dict(description=AK_DESCRIPTION, geography_notes=AK_GEOGRAPHY),
         srcs=[("AKGOV", "Created 23 September 1987; neighbours; 129 km coastline; 31 LGAs; Qua Iboe Church 1887; Mary Slessor; 1929; area 7,249 km²"),
               ("NIPCAK", "Created 23 September 1987 from Cross River; largest oil producer; peoples; coastline; crops; area 6,900 km²"),
               ("WAK", "Name from the Qua Iboe River; Oil Rivers Protectorate; Enyong Division 1904; Ibibio State Union; South-Eastern State; peoples and languages; forests"),
               ("STAT", "1987: Akwa Ibom split from Cross River; area 6,788 km²"), ("CPAK", "2006 census 3,902,051 (National Population Commission)")]),
]

RELATIONS = [
    dict(frm=AN, type="neighbours", to="@admin_units:state:delta", source="ANGOV", evidence="multiple_sources",
         notes="Listed by the Anambra State Government and by Wikipedia (the border runs along the Niger)."),
    dict(frm=AN, type="neighbours", to="@admin_units:state:imo", source="ANGOV", evidence="multiple_sources",
         notes="Listed by the Anambra State Government and by Wikipedia."),
    dict(frm=AN, type="neighbours", to="@admin_units:state:abia", source="ANGOV", evidence="multiple_sources",
         notes="Listed by the Anambra State Government, by Wikipedia and by the Nigerian Investment Promotion Commission's Abia page."),
    dict(frm=AN, type="neighbours", to="@admin_units:state:rivers", source="WAN", evidence="single_reliable_source",
         notes="Listed by Wikipedia only (a border of about 4 km); the Anambra State Government's list omits it."),
    dict(frm=AB, type="neighbours", to="@admin_units:state:imo", source="NIPCAB", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission, the Abia State Government and Wikipedia."),
    dict(frm=AB, type="neighbours", to="@admin_units:state:rivers", source="NIPCAB", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission, the Abia State Government and Wikipedia."),
    dict(frm=AB, type="neighbours", to="@admin_units:state:akwa-ibom", source="NIPCAB", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission, the Abia and Akwa Ibom state governments and Wikipedia."),
    dict(frm=AK, type="neighbours", to="@admin_units:state:rivers", source="AKGOV", evidence="multiple_sources",
         notes="Listed by the Akwa Ibom State Government and by Wikipedia."),
]


def stat(record, metric, value, source, notes, year=None, method="other"):
    d = dict(record=record, metric=metric, value_low=value, method=method, source=source, evidence="single_reliable_source", notes=notes)
    if year:
        d["reference_year"] = year
    return d


NPC = "National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."
STATISTICS = [
    stat(AN, "population", 4177828, "CPAN", NPC, 2006, "census"),
    stat(AN, "population", 4182032, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(AN, "population", 2796475, "CPAN", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(AN, "population", 5953500, "CPAN", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(AN, "population", 5084195, "ANGOV", "Given by the Anambra State Government as a 2015 estimate.", 2015, "projection"),
    stat(AN, "population", 5846198, "NIPCAN", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(AN, "area_km2", 4416, "ANGOV", "Anambra State Government."),
    stat(AN, "area_km2", 4865, "NIPCAN", "Nigerian Investment Promotion Commission."),
    stat(AN, "area_km2", 4761, "STAT", "Statoids."),
    stat(AB, "population", 2845380, "CPAB", NPC, 2006, "census"),
    stat(AB, "population", 2833999, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(AB, "population", 2338487, "CPAB", "1991 census for the area of today's state (before part was transferred to Ebonyi in 1996), as reproduced by City Population.", 1991, "census"),
    stat(AB, "population", 4143100, "CPAB", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(AB, "population", 3934157, "NIPCAB", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(AB, "area_km2", 4900, "NIPCAB", "Nigerian Investment Promotion Commission."),
    stat(AB, "area_km2", 4902, "WAB", "Wikipedia."),
    stat(AB, "area_km2", 4857, "STAT", "Statoids."),
    stat(AK, "population", 3902051, "CPAK", NPC, 2006, "census"),
    stat(AK, "population", 3920208, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(AK, "population", 2409613, "CPAK", "1991 census, as reproduced by City Population.", 1991, "census"),
    stat(AK, "population", 4979400, "CPAK", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(AK, "population", 5867932, "NIPCAK", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(AK, "population", 7200000, "AKGOV", "Given by the Akwa Ibom State Government as an estimate ('about 7,200,000'), without a year."),
    stat(AK, "area_km2", 7249, "AKGOV", "Akwa Ibom State Government."),
    stat(AK, "area_km2", 6900, "NIPCAK", "Nigerian Investment Promotion Commission."),
    stat(AK, "area_km2", 6788, "STAT", "Statoids."),
]

GAPS = [
    ("Anambra's creation date in the archive", "Batch 001 recorded Anambra State as created on 3 February 1976, the date of the old Anambra State. The Anambra State Government and the Nigerian Investment Promotion Commission date the present state to 27 August 1991, when Enugu State was separated. The text explains both; the batch-001 record is not changed. Owner decision."),
    ("The Abia acronym", "The Nigerian Investment Promotion Commission gives Aba, Bende, Isuikwuato and Afikpo; Wikipedia gives Arochukwu instead of Afikpo. Afikpo is now in Ebonyi State. Both are recorded, attributed."),
    ("Anambra–Edo border", "Wikipedia's geography section names Edo State as a western neighbour across the Niger; its introduction and the state government do not. Not recorded as a link."),
    ("Nri and Igbo-Ukwu", "Mentioned briefly (Wikipedia; NIPC). The Kingdom of Nri and the Igbo-Ukwu finds need their own, scholarly-sourced records."),
    ("Aro Confederacy, Anglo-Aro War, Arochukwu and the slave trade", "Given by Wikipedia only, briefly. They need scholarly sources and their own records."),
    ("The Women's War of 1929", "Named by the Abia State Government (as the Aba Women's Riot) and the Akwa Ibom State Government (Ikot Abasi). It needs its own event record with scholarly sources."),
    ("The Civil War in these states", "Only the Uli airstrip (Wikipedia) is mentioned. The war needs its own careful treatment."),
    ("Oil production rankings", "'Largest producer' (Akwa Ibom) and Abia's and Anambra's rankings change over time and depend on how production is counted. Only Akwa Ibom's is mentioned, attributed."),
    ("Areas and population figures", "The areas and populations from each source differ. All are recorded; none is chosen."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="Anambra, Abia and Akwa Ibom states: history, geography, peoples, economy and population (fills empty fields of the existing records).")


STATES = [("Anambra State", AN_DESCRIPTION, AN_GEOGRAPHY), ("Abia State", AB_DESCRIPTION, AB_GEOGRAPHY), ("Akwa Ibom State", AK_DESCRIPTION, AK_GEOGRAPHY)]


def report():
    L = ["# Research batch 013 — Anambra, Abia and Akwa Ibom states", "",
         f"Researched {ACCESSED}. All three state pages are already published, so **this text goes live the moment it is imported**. This report is the review.", ""]
    for name, d, g in STATES:
        L += [f"- New prose for {name}: **{words(d) + words(g)} words**. The page passes the 300-word indexing rule and becomes indexable."]
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    for name, d, g in STATES:
        L += ["", f"## {name} — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in d.split("\n")]
        L += ["", f"## {name} — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in g.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Anambra: named after the Omambala River | state government; Wikipedia | stated |",
          "| Old Anambra 1976 (capital Enugu); present state 27 Aug 1991 | Wikipedia; state government; NIPC; Statoids | stated |",
          "| Igbo-Ukwu finds about 9th century; Nri | Wikipedia; NIPC | stated, attributed |",
          "| Uli airstrip | Wikipedia only | attributed |",
          "| Anambra neighbours | state government; Wikipedia (Rivers: Wikipedia only) | 3 links multiple, Rivers single; Edo gap |",
          "| Igbo; Igala in Anambra West | state government (+ Wikipedia for Igbo) | stated / attributed |",
          "| Onitsha market 'largest' | state government (West Africa) vs NIPC (Africa) | both attributed |",
          "| Anambra crops, minerals, oil and gas; oil from 2012 | state government; NIPC; Wikipedia (2012) | stated; 2012 attributed |",
          "| Abia created 27 Aug 1991 from Imo | NIPC; state government; Wikipedia; Statoids | stated |",
          "| Abia acronym | NIPC (Afikpo) vs Wikipedia (Arochukwu) | both attributed |",
          "| Aro Confederacy; Anglo-Aro War; East Central; Imo 1976; Ebonyi 1996 | Wikipedia (+ Statoids and batch 009 for 1996) | attributed |",
          "| Women's War 1929 | Abia and Akwa Ibom state governments | attributed |",
          "| Abia neighbours; 17 LGAs | NIPC; state government; Wikipedia | 3 new links, multiple |",
          "| Akwete cloth; Ekpe; Aba industry; Ariaria | state government; NIPC; Wikipedia | stated / attributed |",
          "| Akwa Ibom created 23 Sep 1987 from Cross River | state government; NIPC; Statoids; Wikipedia | stated |",
          "| Name from the Qua Iboe River; colonial history; Ibibio State Union | Wikipedia only | attributed |",
          "| Qua Iboe Church 1887; Mary Slessor; Ikot Abasi 1929 | state government only | attributed |",
          "| 129 km coastline; neighbours | state government; NIPC; Wikipedia | stated; Rivers link multiple |",
          "| Peoples; languages | NIPC; Wikipedia | stated / attributed |",
          "| Largest oil producer | NIPC; Wikipedia | attributed |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['record'].split(':')[-1].replace('-', ' ').title()} — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", "## Neighbours (8 new links)", "",
          "- Anambra ↔ Delta, Imo, Abia (two or more sources); Rivers (Wikipedia only). Links with Kogi and Enugu already exist.",
          "- Abia ↔ Imo, Rivers, Akwa Ibom (three or more sources). Links with Enugu, Ebonyi and Cross River already exist.",
          "- Akwa Ibom ↔ Rivers (two sources). The Cross River link already exists.",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- Three more state pages become indexable (11 in all). The sitemap should grow by 3 (2,989 → 2,992). The new source pages stay noindex.",
          "- The text is original prose, and every claim is sourced or attributed. Promotional claims on the government pages are left out.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_013_anambra_abia_akwa_ibom.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_013_anambra_abia_akwa_ibom_REVIEW.md", "w").write(report())
    print(" ".join(f"{n.split()[0]}={words(d) + words(g)}" for n, d, g in STATES) + f" relations={len(RELATIONS)} statistics={len(STATISTICS)}")
