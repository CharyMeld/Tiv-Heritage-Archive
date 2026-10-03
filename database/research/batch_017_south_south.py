"""
Research batch 017 — Rivers, Bayelsa, Delta and Edo states (the South South group, with
Akwa Ibom and Cross River already done): history, geography, peoples and economy
(researched 2026-09-25). Same method as batches 013-016:

Fills the EMPTY description and geography fields of the four existing, published state
records (never overwrites), adds dated population/area figures side by side, and sourced
'neighbours' links (existing links are not duplicated: Kogi–Edo, Anambra–Delta,
Anambra–Rivers, Abia–Rivers, Akwa Ibom–Rivers).
  * facts with two or more independent sources are stated plainly;
  * single-source claims are attributed in the text;
  * shared lineage: the Bayelsa State Government's "About" page and the 2016 Edo State
    Government history page reproduce Wikipedia passages; they count with Wikipedia as one
    source for those passages;
  * the Odi massacre, Isaac Boro's 1966 declaration, Niger Delta militancy, oil pollution,
    the Ogoni struggle and human trafficking are left for separate, careful treatment.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "RIGOV": dict(source_type="official_website", title="About Rivers State", organisation="Rivers State Government",
                  url="https://www.riversstate.gov.ng/about/",
                  archive_reference="Internet Archive snapshot 20250407085649: http://web.archive.org/web/20250407085649/https://www.riversstate.gov.ng/about/",
                  verification_status="needs_corroboration",
                  notes="Dominant ethnic groups Ogoni, Ijaw and Ikwerre; riverine and upland divisions; crude oil and natural gas ('more than 40% of Nigeria's output'); silica sand, glass and clay; Trans Amadi industrial estate; Eleme/Onne petrochemicals and refinery."),
    "BYGOV": dict(source_type="official_website", title="About Bayelsa State", organisation="Bayelsa State Government",
                  url="https://bayelsastate.gov.ng/about/", verification_status="needs_corroboration",
                  notes="'The Glory of all Lands'; created 1 October 1996 by the Abacha government from Rivers State; capital Yenagoa; borders Rivers and Delta and the Atlantic; about 10,773 km²; eight LGAs; ancestral homeland of the Ijaw; Ijaw, Ogbia, Nembe and Epie languages; Oloibiri, where oil was first discovered in Nigeria; 18 trillion cubic feet of gas; name from Brass, Yenagoa and Sagbama; Ijaw state movement 1941–56. Much of its wording follows Wikipedia."),
    "DEGOV": dict(source_type="official_website", title="About Delta", organisation="Delta State Government",
                  url="https://deltastate.gov.ng/about-delta/", verification_status="needs_corroboration",
                  notes="Created 27 August 1991 from Bendel State; Mid-West Region created by plebiscite in August 1963 from the Western Region, a state in 1967, renamed Bendel in February 1976; the Asaba division added to Delta province in 1991; area 17,440 km² (header: 17,108); borders Edo, Anambra, Rivers, Bayelsa, Ondo and the Atlantic; rivers; capital Asaba; 'The Big Heart'; oil and gas, refinery, petrochemicals, steel complex, export terminal; peoples Urhobo, Igbo, Ijaw, Isoko, Itsekiri; source of the Ethiope at Umuaja; Nana's Palace at Koko."),
    "EDGOV": dict(source_type="official_website", title="History of Edo State", organisation="Edo State Government",
                  url="http://www.edostate.gov.ng/2016/02/08/history-of-edo-state-from-edo-state-website/",
                  archive_reference="Internet Archive snapshot 20190517041518: http://web.archive.org/web/20190517041518/http://www.edostate.gov.ng:80/2016/02/08/history-of-edo-state-from-edo-state-website/",
                  verification_status="needs_corroboration",
                  notes="2016 page: Mid-Western Region 1963 from Benin and Delta provinces, Bendel from 1976; Edo State formed 27 August 1991; capital Benin City; Edo (Bini), Esan, Afemai (Etsako), Owan and Akoko-Edo peoples with percentages; Igue and Ekaba festivals; 2006 population 3,218,332; area 19,187 km². Its history and demography passages follow Wikipedia's wording."),
    "NIPCRI": dict(source_type="official_website", title="Rivers State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/rivers-state/",
                   archive_reference="Internet Archive snapshot 20240421023346: http://web.archive.org/web/20240421023346/https://www.nipc.gov.ng/nigeria-states/rivers-state/",
                   verification_status="needs_corroboration",
                   notes="Created 27 May 1967; Port Harcourt the centre of Nigeria's oil and gas industry; borders the Atlantic, Imo, Abia, Anambra, Akwa Ibom, Bayelsa and Delta; 'Treasure Base of the Nation'; seaports; agriculture before oil; fishing; area 10,575 km²; 23 LGAs; population 7,817,866 (no year); crops."),
    "NIPCBY": dict(source_type="official_website", title="Bayelsa State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/bayelsa-state/",
                   archive_reference="Internet Archive snapshot 20250213063403: http://web.archive.org/web/20250213063403/https://www.nipc.gov.ng/nigeria-states/bayelsa-state/",
                   verification_status="needs_corroboration",
                   notes="Created 1 October 1996 from Rivers State; name an acronym of Brass, Yenagoa and Sagbama; borders Rivers and Delta and the Gulf of Guinea; 'Glory of all Lands'; largest gas reservoir (18 trillion cubic feet); area 9,059 km²; 8 LGAs; crops."),
    "NIPCDE": dict(source_type="official_website", title="Delta State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/delta-state/",
                   archive_reference="Internet Archive snapshot 20240712113949: http://web.archive.org/web/20240712113949/https://www.nipc.gov.ng/nigeria-states/delta-state/",
                   verification_status="needs_corroboration",
                   notes="Warri the most populous city and economic centre; oil-producing Niger Delta state; fishing; 'the Big Heart'; area 17,108 km²; 25 LGAs; population 6,037,667 (no year); crops; minerals."),
    "WRI": dict(source_type="encyclopedia", title="Rivers State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Rivers_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: formed 27 May 1967; named after its many rivers; Oil Rivers Protectorate (1885), Niger Coast Protectorate (1893), Southern Nigeria (1900); Rivers movement from 1941; Bayelsa separated 1996; borders; about 30 languages; rainforest and mangroves; rainfall; refineries and seaports; palm oil; crops; area 11,077 km²."),
    "WBY": dict(source_type="encyclopedia", title="Bayelsa State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Bayelsa_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: created 1 October 1996 from Rivers; name; borders; Ijaw homeland; languages; Oloibiri; gas; riverine setting; Edumanom Forest Reserve; Akassa lighthouse (1910)."),
    "WDE": dict(source_type="encyclopedia", title="Delta State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Delta_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: named after the Niger Delta; formed 27 August 1991 from Bendel; Anioma State proposal; Asaba chosen as capital; Mid-West 1963–76, Bendel 1976–91; borders; peoples (Urhobo, Isoko, Anioma Igbo, Ijaw, Itsekiri, Olukumi); rivers; vegetation; minerals; petroleum."),
    "WED": dict(source_type="encyclopedia", title="Edo State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Edo_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: created 1991 from Bendel; Benin City capital, centre of the rubber industry; borders; Benin Kingdom and its earthworks; 1897 British punitive expedition; Portuguese missionaries in the 15th century; Mid-Western Region and 1967 occupation; peoples; tourist sites incl. Igun Street bronze casters; Okpella cement."),
    "CPRI": dict(source_type="dataset", title="Rivers (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA033__rivers/", verification_status="needs_corroboration",
                 notes="Census 1991: 3,187,844; census 2006: 5,198,716; projection 2022: 7,476,800 (National Population Commission / National Bureau of Statistics)."),
    "CPBY": dict(source_type="dataset", title="Bayelsa (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA006__bayelsa/", verification_status="needs_corroboration",
                 notes="Census 1991: 1,121,693; census 2006: 1,704,515; projection 2022: 2,537,400 (National Population Commission / National Bureau of Statistics)."),
    "CPDE": dict(source_type="dataset", title="Delta (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA010__delta/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,590,491; census 2006: 4,112,445; projection 2022: 5,636,100 (National Population Commission / National Bureau of Statistics)."),
    "CPED": dict(source_type="dataset", title="Edo (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA012__edo/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,172,005; census 2006: 3,233,366; projection 2022: 4,777,000 (National Population Commission / National Bureau of Statistics)."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration",
                 notes="Reused (batch 001). 2006 census table: Rivers 5,185,400, 10,361 km²; Bayelsa 1,703,358, 9,363 km²; Delta 4,098,391, 17,095 km²; Edo 3,218,332, 19,584 km². Chronology: 1967-05-27 Rivers created; 1991-08-27 Bendel divided into Delta and Edo; 1996-10-01 Bayelsa split from Rivers."),
}

RI_DESCRIPTION = """Rivers State was created on 27 May 1967, when Nigeria's regions were replaced by twelve states and the Eastern Region was divided. The Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this date. According to Wikipedia, it is named after the many rivers of its territory. Its capital, Port Harcourt, is regarded as the centre of Nigeria's oil and gas industry. The state is grouped in the South South geopolitical zone, and the Commission gives its nickname as "Treasure Base of the Nation". On 1 October 1996 its western part was separated to form Bayelsa State.

Wikipedia outlines the earlier history. The coast was part of the British Oil Rivers Protectorate from 1885, named for its trade in palm oil. It became part of the Niger Coast Protectorate in 1893 and of Southern Nigeria in 1900. From 1941 organisations such as the Ijo Rivers People's League and later the Rivers Chiefs and People's Conference campaigned for a separate Rivers province or state.

Rivers borders Imo, Abia and Anambra states to the north, Akwa Ibom State to the east, and Bayelsa and Delta states to the west. To the south lies the Atlantic. It has twenty-three local government areas.

The state is known for its many peoples and languages. The state government names the Ogoni, the Ijaw and the Ikwerre as the dominant groups. Wikipedia counts about thirty indigenous languages and dialects, among them Ikwerre, Ogba, Ekpeye, Igbo, Ogoni and the Ijaw varieties Kalabari, Okrika, Ibani and Andoni.

Oil and gas dominate the economy. The state government and Wikipedia both describe large reserves of crude oil and natural gas. They also name refineries, petrochemical plants at Eleme and Onne, industrial estates and seaports. Before oil, farming and fishing were the mainstays, and the Commission and Wikipedia name cassava, yams, maize and rice among the crops.

The 2006 census counted 5,198,716 people, according to National Population Commission figures reproduced by City Population and Wikipedia. Other published figures are listed below with their sources. Nigerian census results are disputed."""

RI_GEOGRAPHY = """Rivers State lies in the eastern part of the Niger Delta and is mostly low-lying. Wikipedia describes tropical rainforest inland and mangrove swamps towards the coast, crossed by many rivers and creeks, including the Bonny River. Rainfall is heavy: Wikipedia gives almost 4,700 millimetres a year at Bonny on the coast, falling to about 1,700 in the far north.

The area is given as 10,575 square kilometres by the Nigerian Investment Promotion Commission, 11,077 by Wikipedia and 10,361 by Statoids. The figures are recorded below."""

BY_DESCRIPTION = """Bayelsa State was created on 1 October 1996 by the military government of Sani Abacha, from part of Rivers State. The Bayelsa State Government, the Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this date. Its name combines the first letters of the three local government areas from which it was formed: Brass, Yenagoa and Sagbama. The capital is Yenagoa. The state is grouped in the South South geopolitical zone, and its nickname is "Glory of all Lands".

The state government and Wikipedia describe Bayelsa as the ancestral homeland of the Ijaw people. They say that from the 1940s Ijaw organisations campaigned for an Ijaw-majority state in the Niger Delta. Ijaw, Ogbia, Nembe and Epie are among the languages spoken, according to the state government.

Bayelsa borders Rivers State and Delta State, and the Atlantic lies to the south. It has eight local government areas: Brass, Ekeremor, Kolokuma/Opokuma, Nembe, Ogbia, Sagbama, Southern Ijaw and Yenagoa. With about 1.7 million people counted in 2006, it is the least populous state in Nigeria.

Oil and gas are central to the economy. The state government and Wikipedia both say that oil was first discovered in Nigeria at Oloibiri, in Bayelsa. The state government and the Commission both say the state holds the country's largest gas reserve, about 18 trillion cubic feet. The state government itself notes that widespread poverty and oil spills remain serious problems. The Commission lists cassava, rice, oil palm, plantain and rubber among the crops.

The 2006 census counted 1,704,515 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

BY_GEOGRAPHY = """Bayelsa lies in the heart of the Niger Delta. The state government and Wikipedia both describe a riverine and estuarine land in which many communities are surrounded by water and can be reached only by boat, which makes road building difficult. Wikipedia notes that Yenagoa is prone to annual flooding. It names the Edumanom Forest Reserve as the last known site of chimpanzees in the Niger Delta (in 2008), and says the Akassa lighthouse has stood on the coast since 1910. The climate is tropical monsoon, with about 2,900 millimetres of rain a year, according to Wikipedia.

The area is given as 10,773 square kilometres by the state government and Wikipedia, 9,059 by the Nigerian Investment Promotion Commission and 9,363 by Statoids. The figures are recorded below."""

DE_DESCRIPTION = """Delta State was created on 27 August 1991, when Bendel State was divided into Delta and Edo states. The Delta State Government, Wikipedia and Statoids all give this date. It is named after the Niger Delta, much of which lies in the state. The capital is Asaba, on the River Niger, and the main commercial city is Warri. The state is grouped in the South South geopolitical zone, and its nickname is "The Big Heart".

The state government outlines its administrative history. The Mid-West Region was created by plebiscite in August 1963 from the Benin and Delta provinces of the Western Region. It became a state in 1967 and was renamed Bendel, from "Benin" and "Delta", in February 1976. When Delta State was formed in 1991, the Asaba division of the old Benin Province was added to the Delta Province. Wikipedia adds that a separate "Anioma State" had also been proposed, and that the military government chose Asaba as the capital.

Delta borders Edo State to the north, Anambra State to the east, Rivers and Bayelsa states to the south-east and south, and Ondo State. It has a coastline on the Atlantic. It has twenty-five local government areas.

The state government and Wikipedia both name the Urhobo, Isoko, Igbo, Ijaw and Itsekiri as the main peoples. Wikipedia describes the Igbo-speaking Ika, Ukwuani, Aniocha and Oshimili communities as the Anioma people. It names the Okpe, who speak a dialect of Urhobo, and the small Olukumi community.

Delta is one of Nigeria's main oil and gas producers. The state government lists a refinery, a petrochemical complex, a steel complex, gas-fired power stations and an oil export terminal. The Commission lists cassava, rubber, oil palm, cashew and cocoa among the crops and notes a thriving fishing industry. Among the heritage sites the state government names are the source of the Ethiope River at Umuaja and Nana's Palace at Koko.

The 2006 census counted 4,112,445 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

DE_GEOGRAPHY = """The state is low-lying, with no notable hills, according to Wikipedia, and the state government says about a third of it is swampy or waterlogged. Its rivers include the Niger, the Forcados, the Escravos, the Warri, the Ethiope and the Benin. Wikipedia describes mangroves on the coast and lowland forest over most of the interior, and notes that flooding is a recurring feature of the climate.

The area is given in several ways: 17,108 square kilometres by the Nigerian Investment Promotion Commission, 17,440 in the text of the state government's page, about 18,050 by Wikipedia and 17,095 by Statoids. The figures are recorded below."""

ED_DESCRIPTION = """Edo State was created on 27 August 1991, when Bendel State was divided into Edo and Delta states. The Edo State Government, Wikipedia and Statoids all give this date. The capital is Benin City. The state is grouped in the South South geopolitical zone, and Wikipedia gives its nickname as "Heartbeat of the Nation".

The state takes in the heartland of the Benin Kingdom, whose king is the Oba of Benin. Wikipedia notes that the ancient city of Edo, on the site of modern Benin City, was surrounded by some of the largest earthworks in the world. It says Portuguese missionaries brought Christianity to the area in the 15th century. In 1897 a British punitive expedition destroyed much of the city, and the territory was brought into what became the Southern Nigeria Protectorate.

The state government and Wikipedia outline the modern history in the same words. The Mid-Western Region was formed in June 1963 from the Benin and Delta provinces of the Western Region, with Benin City as its capital. It became Mid-Western State in 1967 and was renamed Bendel in 1976. They add that early in the Civil War, Biafran forces occupied the region and a "Republic of Benin" was declared there, which collapsed within a day when federal troops retook Benin City.

Edo borders Kogi State to the north and north-east, Delta State to the south and south-east, and Ondo State to the west, according to Wikipedia. Wikipedia also gives a short border with Anambra State across the Niger. The state has eighteen local government areas.

The state government and Wikipedia describe the Edo (Bini) as the largest group, occupying seven of the eighteen local government areas, followed by the Esan, the Etsako (Afemai), the Owan and the Akoko-Edo. They note that many communities trace their origins or their ruling dynasties to Benin. The state government names the Igue and Ekaba festivals of the Bini and the age-grade initiation of the Etsako among its traditions. Wikipedia names the Igun Street bronze casters in Benin City among its heritage sites.

Wikipedia describes Benin City as a centre of the rubber industry. It also records crude oil, limestone and a cement factory at Okpella.

The 2006 census counted 3,233,366 people, according to National Population Commission figures reproduced by City Population; the state government and Statoids give 3,218,332. Other published figures are listed below with their sources. Nigerian census results are disputed."""

ED_GEOGRAPHY = """According to Wikipedia, the Niger forms part of the state's north-eastern border. The area is given as 19,187 square kilometres by the state government and 19,584 by Statoids. Both figures are recorded below."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


RI, BY, DE, ED = ("@admin_units:state:rivers", "@admin_units:state:bayelsa", "@admin_units:state:delta", "@admin_units:state:edo")

UPDATES = [
    dict(ref=RI, fields=dict(description=RI_DESCRIPTION, geography_notes=RI_GEOGRAPHY),
         srcs=[("NIPCRI", "Created 27 May 1967; Port Harcourt; borders; Treasure Base; seaports; crops; area 10,575 km²"),
               ("RIGOV", "Ogoni, Ijaw, Ikwerre; oil and gas; Eleme/Onne; industry"),
               ("WRI", "Name; Oil Rivers, Niger Coast, Southern Nigeria; Rivers movement; Bayelsa 1996; languages; rainforest, mangroves, rainfall; area 11,077 km²"),
               ("STAT", "1967 Rivers; 1996 Bayelsa; area 10,361 km²"), ("CPRI", "2006 census 5,198,716 (National Population Commission)")]),
    dict(ref=BY, fields=dict(description=BY_DESCRIPTION, geography_notes=BY_GEOGRAPHY),
         srcs=[("BYGOV", "Created 1 October 1996 by Abacha from Rivers; name; Yenagoa; borders; 8 LGAs; Ijaw homeland; languages; Oloibiri; gas; poverty and oil spills; area 10,773 km²"),
               ("NIPCBY", "Created 1996 from Rivers; name; Glory of all Lands; gas 18 tcf; crops; area 9,059 km²"),
               ("WBY", "Name; Ijaw; Oloibiri; riverine setting; flooding; Edumanom; Akassa lighthouse; climate"),
               ("STAT", "1996 Bayelsa; area 9,363 km²"), ("CPBY", "2006 census 1,704,515 (National Population Commission)")]),
    dict(ref=DE, fields=dict(description=DE_DESCRIPTION, geography_notes=DE_GEOGRAPHY),
         srcs=[("DEGOV", "Created 27 August 1991 from Bendel; Mid-West 1963, 1967, Bendel 1976; Asaba division; borders; rivers; Big Heart; industries; peoples; heritage sites; area 17,440 km²"),
               ("NIPCDE", "Warri; Big Heart; oil; fishing; crops; area 17,108 km²"),
               ("WDE", "Name; Anioma proposal; Asaba; borders; peoples; vegetation; flooding; area 18,050 km²"),
               ("STAT", "1991 Delta; area 17,095 km²"), ("CPDE", "2006 census 4,112,445 (National Population Commission)")]),
    dict(ref=ED, fields=dict(description=ED_DESCRIPTION, geography_notes=ED_GEOGRAPHY),
         srcs=[("EDGOV", "Mid-Western Region 1963, Bendel 1976; Edo State 27 August 1991; Benin City; peoples; festivals; 2006 population 3,218,332; area 19,187 km²"),
               ("WED", "Benin Kingdom; earthworks; 1897 expedition; Portuguese missionaries; 1967 occupation; borders; peoples; Igun Street; rubber; Okpella cement"),
               ("DEGOV", "Bendel from Benin and Delta provinces; 1991 division"),
               ("STAT", "1991 Edo; area 19,584 km²"), ("CPED", "2006 census 3,233,366 (National Population Commission)")]),
]

RELATIONS = [
    dict(frm=RI, type="neighbours", to="@admin_units:state:imo", source="NIPCRI", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission and by Wikipedia."),
    dict(frm=RI, type="neighbours", to="@admin_units:state:bayelsa", source="NIPCRI", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission (Rivers and Bayelsa pages), the Bayelsa State Government and Wikipedia."),
    dict(frm=RI, type="neighbours", to="@admin_units:state:delta", source="NIPCRI", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission, the Delta State Government and Wikipedia."),
    dict(frm=DE, type="neighbours", to="@admin_units:state:edo", source="DEGOV", evidence="multiple_sources",
         notes="Listed by the Delta State Government and by Wikipedia (Delta and Edo articles)."),
    dict(frm=DE, type="neighbours", to="@admin_units:state:bayelsa", source="DEGOV", evidence="multiple_sources",
         notes="Listed by the Delta State Government, the Bayelsa State Government, the Nigerian Investment Promotion Commission (Bayelsa) and Wikipedia."),
    dict(frm=DE, type="neighbours", to="@admin_units:state:ondo", source="DEGOV", evidence="multiple_sources",
         notes="Listed by the Delta State Government and by Wikipedia."),
    dict(frm=ED, type="neighbours", to="@admin_units:state:ondo", source="WED", evidence="single_reliable_source",
         notes="Listed by Wikipedia; no other source seen."),
    dict(frm=ED, type="neighbours", to="@admin_units:state:anambra", source="WED", evidence="single_reliable_source",
         notes="Listed by Wikipedia (Edo and Anambra articles; about 4 km across the Niger). The Anambra State Government's list omits it."),
]


def stat(record, metric, value, source, notes, year=None, method="other"):
    d = dict(record=record, metric=metric, value_low=value, method=method, source=source, evidence="single_reliable_source", notes=notes)
    if year:
        d["reference_year"] = year
    return d


NPC = "National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."
PROJ = "Projection, not a count (NPC / NBS, via City Population)."
STATISTICS = [
    stat(RI, "population", 5198716, "CPRI", NPC, 2006, "census"),
    stat(RI, "population", 5185400, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(RI, "population", 3187844, "CPRI", "1991 census for the area of today's state (without Bayelsa), as reproduced by City Population.", 1991, "census"),
    stat(RI, "population", 7476800, "CPRI", PROJ, 2022, "projection"),
    stat(RI, "population", 7817866, "NIPCRI", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(RI, "area_km2", 10575, "NIPCRI", "Nigerian Investment Promotion Commission."),
    stat(RI, "area_km2", 11077, "WRI", "Wikipedia."),
    stat(RI, "area_km2", 10361, "STAT", "Statoids."),
    stat(BY, "population", 1704515, "CPBY", NPC + " The Nigerian Investment Promotion Commission gives the same figure without a year.", 2006, "census"),
    stat(BY, "population", 1703358, "STAT", "Statoids' 2006 census table; differs slightly from the NPC figure.", 2006, "census"),
    stat(BY, "population", 1121693, "CPBY", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(BY, "population", 2537400, "CPBY", PROJ, 2022, "projection"),
    stat(BY, "area_km2", 10773, "BYGOV", "Bayelsa State Government ('approximately'; Wikipedia gives the same)."),
    stat(BY, "area_km2", 9059, "NIPCBY", "Nigerian Investment Promotion Commission."),
    stat(BY, "area_km2", 9363, "STAT", "Statoids."),
    stat(DE, "population", 4112445, "CPDE", NPC, 2006, "census"),
    stat(DE, "population", 4098391, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(DE, "population", 2590491, "CPDE", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(DE, "population", 5636100, "CPDE", PROJ, 2022, "projection"),
    stat(DE, "population", 6037667, "NIPCDE", "Given by the Nigerian Investment Promotion Commission (and on the state government's page) without a year or method."),
    stat(DE, "area_km2", 17108, "NIPCDE", "Nigerian Investment Promotion Commission (repeated in the state government's page header)."),
    stat(DE, "area_km2", 17440, "DEGOV", "Delta State Government (page text)."),
    stat(DE, "area_km2", 18050, "WDE", "Wikipedia ('about')."),
    stat(DE, "area_km2", 17095, "STAT", "Statoids."),
    stat(ED, "population", 3233366, "CPED", NPC, 2006, "census"),
    stat(ED, "population", 3218332, "EDGOV", "Given by the Edo State Government (2016 page) as the 2006 census; Statoids gives the same figure.", 2006, "census"),
    stat(ED, "population", 2172005, "CPED", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(ED, "population", 4777000, "CPED", PROJ, 2022, "projection"),
    stat(ED, "area_km2", 19187, "EDGOV", "Edo State Government (2016 page)."),
    stat(ED, "area_km2", 19584, "STAT", "Statoids."),
]

GAPS = [
    ("Niger Delta conflict and oil pollution", "The Odi massacre (1999), Isaac Adaka Boro's 1966 declaration, militancy, oil spills and the Ogoni struggle are mentioned by the state governments and Wikipedia but not summarised. They need separate, careful, well-sourced treatment."),
    ("The Benin Kingdom", "Only outlined (Wikipedia). The kingdom, the Oba, the 1897 expedition and the looted Benin Bronzes need their own records with scholarly sources."),
    ("The kingdoms of the eastern Niger Delta", "Bonny, Opobo, Kalabari, Okrika, Nembe and the Itsekiri kingdom of Warri are not described. They need their own records."),
    ("Oloibiri and the first oil discovery", "Named by the Bayelsa State Government and Wikipedia; the date is not given in the sources read and is not stated."),
    ("Share of national oil output", "The Rivers State Government says more than 40%; Wikipedia says more than 60%; the Delta State Government says about 30% for Delta and Bayelsa sources 30–40% (2015). Not stated in the text."),
    ("Bayelsa's borders", "The Nigerian Investment Promotion Commission gives compass directions that contradict the state government's; the text gives the neighbours without directions."),
    ("Delta–Ondo direction", "The Delta State Government puts Ondo to the north-east, Wikipedia to the west; the text gives no direction."),
    ("Edo–Ondo and Edo–Anambra borders", "Given by Wikipedia only; recorded as single-source links. (The Anambra–Edo question was a gap in batch 013.)"),
    ("Edo State Government page (2016)", "Much of its wording follows Wikipedia; it is counted with Wikipedia as one source for those passages. Its claim that the Mid-West lost Ughelli to Rivers State in 1976 is not used."),
    ("Human trafficking (Edo)", "Wikipedia has a section on it. Not summarised; needs separate, careful treatment."),
    ("Areas and population figures", "The areas and populations from each source differ. All are recorded; none is chosen."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="Rivers, Bayelsa, Delta and Edo states: history, geography, peoples, economy and population (fills empty fields of the existing records).")


STATES = [("Rivers State", RI_DESCRIPTION, RI_GEOGRAPHY), ("Bayelsa State", BY_DESCRIPTION, BY_GEOGRAPHY),
          ("Delta State", DE_DESCRIPTION, DE_GEOGRAPHY), ("Edo State", ED_DESCRIPTION, ED_GEOGRAPHY)]


def report():
    L = ["# Research batch 017 — Rivers, Bayelsa, Delta and Edo states (South South)", "",
         f"Researched {ACCESSED}. All four state pages are already published, so **this text goes live the moment it is imported**. This report is the review.", ""]
    for name, d, g in STATES:
        L += [f"- New prose for {name}: **{words(d) + words(g)} words**. The page passes the 300-word indexing rule and becomes indexable."]
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    for name, d, g in STATES:
        L += ["", f"## {name} — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in d.split("\n")]
        L += ["", f"## {name} — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in g.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Rivers created 27 May 1967; Bayelsa separated 1996 | NIPC; Wikipedia; Statoids | stated |",
          "| Named after its rivers; protectorates; Rivers movement | Wikipedia only | attributed |",
          "| Rivers peoples and languages | state government; Wikipedia | attributed |",
          "| Oil and gas, refineries, Eleme/Onne, seaports | state government; Wikipedia; NIPC | stated (no percentages) |",
          "| Bayelsa created 1 Oct 1996 by Abacha; name acronym | state government; NIPC; Wikipedia; Statoids | stated |",
          "| Ijaw homeland; Ijaw movement; languages | state government = Wikipedia (one lineage) | attributed |",
          "| Oloibiri first oil; 18 tcf gas | state government; Wikipedia; NIPC | stated |",
          "| Poverty and oil spills | Bayelsa State Government | attributed; details left to a separate treatment |",
          "| Delta created 27 Aug 1991 from Bendel; named after the Niger Delta | state government; Wikipedia; Statoids | stated |",
          "| Mid-West by plebiscite 1963; Bendel 1976; Asaba division | Delta State Government; Wikipedia | attributed |",
          "| Delta peoples | state government; Wikipedia | stated / attributed |",
          "| Delta industries; heritage sites | state government | attributed |",
          "| Edo created 27 Aug 1991 | state government; Wikipedia; Statoids | stated |",
          "| Benin Kingdom, earthworks, Portuguese, 1897 | Wikipedia only | attributed + gap |",
          "| Mid-Western Region 1963, 1967 occupation | Edo government = Wikipedia (one lineage); Delta government (1963) | attributed |",
          "| Edo peoples; festivals | Edo government = Wikipedia; Edo government (festivals) | attributed |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['record'].split(':')[-1].title()} — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", f"## Neighbours ({len(RELATIONS)} new links)", "",
          "- Rivers ↔ Imo, Bayelsa, Delta (two or more sources). Links with Anambra, Abia and Akwa Ibom already exist.",
          "- Delta ↔ Edo, Bayelsa, Ondo (two or more sources). Links with Anambra and Rivers are covered.",
          "- Edo ↔ Ondo, Anambra (Wikipedia only). The Kogi link already exists; Delta is covered.",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- Four more state pages become indexable (23 of 37). The sitemap should grow by 4 (3,000 → 3,004). The new source pages stay noindex.",
          "- The text is original prose, and every claim is sourced or attributed. Conflict, pollution and trafficking are left for separate treatment.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_017_south_south.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_017_south_south_REVIEW.md", "w").write(report())
    print(" ".join(f"{n.split()[0]}={words(d) + words(g)}" for n, d, g in STATES) + f" relations={len(RELATIONS)} statistics={len(STATISTICS)}")
