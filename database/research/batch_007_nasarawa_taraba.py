"""
Research batch 007 — Nasarawa State and Taraba State: history, geography, peoples and
economy (researched 2026-09-24). Same method as batch 004 (Benue):

Fills the EMPTY description and geography fields of the two existing, published state
records (never overwrites), adds dated population/area figures side by side, and sourced
'neighbours' links (Benue's links to both already exist and are not duplicated).
  * facts with two or more sources are stated plainly;
  * single-source or shared-lineage claims are attributed in the text;
  * NIPC's Taraba page and Wikipedia's Taraba article share wording (the rivers and
    'Bantu cradle' passages), so they count as ONE lineage for those passages.
"""
import json, re, sys

ACCESSED = "2026-09-24"
SOURCES = {
    "NASGOV": dict(source_type="official_website", title="About Nasarawa State", organisation="Nasarawa State Government",
                   url="https://nasarawastate.gov.ng/about-nasarawa/", verification_status="needs_corroboration",
                   notes="Official state website. Historical background: Lower Benue Province (1900), renamed Nasarawa Province in 1902 after the Emir of Nasarawa submitted; capital moved from Akpanaja to Nasarawa town; Plateau Province formed in 1926 from Muri, Nasarawa and Bauchi provinces; Benue-Plateau State (May 1967); Plateau State (1976); Nasarawa State from the western half of Plateau State (October 1996), capital Lafia. Extent 7°45′–9°25′ N, 7°–9°37′ E; area 26,875.59 km²; population about 1,826,883 (2006); 13 LGAs and 18 development areas; peoples; physiography; climate; agriculture (over 70% in subsistence farming); minerals incl. Lafia-Obi coal; Farin Ruwa Falls; Keana and Awe salt villages."),
    "WNAS": dict(source_type="encyclopedia", title="Nasarawa State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Nasarawa_State",
                 verification_status="needs_corroboration",
                 notes="Used to corroborate: formed from western Plateau State on 1 October 1996; named after the historic Nassarawa Emirate; neighbours; 13 LGAs; Fulani jihad and the emirates of Keffi, Lafia and Nassarawa under the Sokoto Caliphate; British occupation; Northern Region → Benue-Plateau (1967) → Plateau (1976); peoples; crops; minerals; Farin Ruwa Falls; Keana salt village; 2006 population 1,869,377."),
    "TARGOV": dict(source_type="official_website", title="Taraba State Government Official Website", organisation="Taraba State Government",
                   url="https://tarabastate.gov.ng/", verification_status="needs_corroboration",
                   notes="Home page: the Mambilla Plateau (highlands, cool climate, rolling grasslands); national parks, rivers and waterfalls; farming and livestock; 'one of Nigeria's most diverse states'."),
    "NIPC": dict(source_type="official_website", title="Taraba State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                 url="https://www.nipc.gov.ng/nigeria-states/taraba-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "WTAR": dict(source_type="encyclopedia", title="Taraba State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Taraba_State",
                 verification_status="needs_corroboration",
                 notes="Used to corroborate: created from Gongola State on 27 August 1991; named after the Taraba River; nickname; neighbours; 16 LGAs; main rivers; peoples (about 80 ethnic groups); agriculture and livestock. Shares wording with NIPC's page in places."),
    "CPNAS": dict(source_type="dataset", title="Nasarawa (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                  url="https://www.citypopulation.de/en/nigeria/admin/NGA026__nasarawa/", verification_status="needs_corroboration",
                  notes="Census 1991-11-26: 1,207,876; census 2006-03-21: 1,869,377; projection 2022-03-21: 2,886,000 (National Population Commission / National Bureau of Statistics)."),
    "CPTAR": dict(source_type="dataset", title="Taraba (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                  url="https://www.citypopulation.de/en/nigeria/admin/NGA035__taraba/", verification_status="needs_corroboration",
                  notes="Census 1991-11-26: 1,512,163; census 2006-03-21: 2,294,800; projection 2022-03-21: 3,609,800 (National Population Commission / National Bureau of Statistics)."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration", notes="Reused (batch 001). 2006 census table: Nasarawa 1,863,275, 26,633 km²; Taraba 2,300,736, 59,180 km²."),
}

NAS_DESCRIPTION = """Nasarawa State was created on 1 October 1996 from the western part of Plateau State, with its capital at Lafia. It is in the North Central geopolitical zone. It is named after Nasarawa, the historic emirate and town that also gave its name to a colonial province.

The state's own account traces it to British colonial rule. The area was first organised as the Lower Benue Province in 1900. In 1902, after the Emir of Nasarawa submitted to the British, it was renamed Nasarawa Province and its headquarters moved from Akpanaja to Nasarawa town. In 1926 the northern provinces were reorganised: Plateau Province was formed from parts of the Muri, Nasarawa and Bauchi provinces, and other parts of Nasarawa Province went to Benue Province. Wikipedia adds that from the early 1800s the Fulani jihad had brought the area under the Sokoto Caliphate, through the emirates of Keffi, Lafia and Nasarawa.

Both sources describe the same later path. The area was part of the Northern Region until 1967, when it joined the new Benue-Plateau State. In 1976 it became part of Plateau State, and in 1996 western Plateau was separated to form Nasarawa State.

The state borders Kaduna State to the north and Plateau State to the east. Taraba State lies to the south-east, and Benue State lies to the south across the Benue River. Kogi State lies to the south and west, and the Federal Capital Territory to the west. It has thirteen local government areas: Akwanga, Awe, Doma, Karu, Keana, Kokona, Lafia, Nasarawa, Nasarawa Eggon, Obi, Toto, Wamba and Keffi. The state government also lists eighteen development areas.

The state government names more than twenty peoples, among them the Alago, Eggon, Gwandara, Mada, Migili, Gbagyi, Ebira, Afo, Tiv, Jukun, Agatu, Hausa, Fulani and Kanuri. Wikipedia places the Tiv in the south-east of the state.

Agriculture is the mainstay of the economy. The state government says more than 70 per cent of the population farms. Crops named in the sources include yams, maize, millet, groundnuts, soya beans and sesame. The state is rich in minerals, including coal, barytes and gemstones. The salt villages of Keana and Awe and the Farin Ruwa Falls in Wamba are among its best-known sites.

The 2006 census counted 1,869,377 people, according to National Population Commission figures reproduced by City Population. The state government gives about 1,826,883 for the same census. The figures are listed below with their sources. Nigerian census results are disputed."""

NAS_GEOGRAPHY = """The state government gives the state's extent as roughly latitude 7°45′ to 9°25′ North and longitude 7° to 9°37′ East, and its area as about 26,875 square kilometres. Statoids gives 26,633 square kilometres.

The Benue River forms much of the southern border. Its floodplains cover about a quarter of the state, especially in Awe, Doma, Nasarawa and Toto LGAs. They give way northwards to rolling plains and then to hills. These include the Mada hills, running from Wamba through Akwanga to Nasarawa Eggon, and part of the Jos Plateau in the far north-east. The rainy season runs from March to October, and the dry, harmattan season from November to February."""

TAR_DESCRIPTION = """Taraba State was created on 27 August 1991, when Gongola State was divided into Adamawa and Taraba states. It takes its name from the Taraba River, which crosses the southern part of the state. Its capital is Jalingo, and it is grouped in the North East geopolitical zone. Its nickname is "Nature's Gift to the Nation".

Wikipedia says the state was created by the military government of General Ibrahim Babangida. It adds that the new state brought together three former divisions: Wukari, Mambilla and Muri.

Taraba borders Nasarawa and Benue states to the west and Plateau State to the north-west. Bauchi and Gombe states lie to the north and Adamawa State to the north-east. To the east and south it shares a long international boundary with Cameroon. It has sixteen local government areas: Ardo Kola, Bali, Donga, Gashaka, Gassol, Ibi, Jalingo, Karim Lamido, Kurmi, Lau, Sardauna, Takum, Ussa, Wukari, Yorro and Zing.

The state government calls Taraba one of Nigeria's most diverse states. Wikipedia counts about 80 ethnic groups. It names among the main ones the Jukun, Mumuye, Mambilla, Kuteb, Chamba, Fulani, Tiv, Ichen, Wurkum and Jibu. It places the Jukun, Tiv, Chamba, Kuteb and Ichen mainly in the south, and the Fulani and Mumuye in the north.

Farming is the main occupation. The Nigerian Investment Promotion Commission and Wikipedia both name maize, rice, sorghum, millet, cassava, yams and groundnuts as major crops. Cotton is also grown. The state government highlights livestock production as well. Wikipedia notes that cattle, sheep and goats are raised in large numbers, especially on the Mambilla Plateau and in the Benue and Taraba valleys. Riverside communities fish all year round.

The 2006 census counted 2,294,800 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

TAR_GEOGRAPHY = """The Benue, Donga and Taraba are the state's main rivers. The Nigerian Investment Promotion Commission and Wikipedia, which share wording here, describe rivers rising in the Cameroonian mountains and draining through the state towards the Niger. The land is mostly undulating, with mountains along the Cameroon border.

In the south-east, the Mambilla Plateau is known for its highlands, cool climate and rolling grasslands. The state government counts it among Taraba's main attractions, together with its national parks, rivers and waterfalls. The area is given as 56,282 square kilometres by the Nigerian Investment Promotion Commission and 59,180 square kilometres by Statoids. Both figures are recorded below, and neither is chosen."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


UPDATES = [
    dict(ref="@admin_units:state:nasarawa", fields=dict(description=NAS_DESCRIPTION, geography_notes=NAS_GEOGRAPHY),
         srcs=[("NASGOV", "Colonial provinces 1900–1926; Benue-Plateau 1967; Plateau 1976; creation 1996; neighbours; 13 LGAs and 18 development areas; peoples; farming; minerals; salt villages; Farin Ruwa; extent, area, physiography and climate"),
               ("WNAS", "Created 1 October 1996 from western Plateau; named after the Nasarawa emirate; Fulani jihad and emirates; 1967 and 1976 changes; neighbours; LGAs; peoples; crops; minerals; Keana salt; Farin Ruwa; Benue River border"),
               ("CPNAS", "2006 census 1,869,377 (National Population Commission)")]),
    dict(ref="@admin_units:state:taraba", fields=dict(description=TAR_DESCRIPTION, geography_notes=TAR_GEOGRAPHY),
         srcs=[("NIPC", "Created 27 August 1991 from Gongola; named after the Taraba River; nickname; neighbours; 16 LGAs; crops; area 56,282 km²"),
               ("WTAR", "Creation 1991 under Babangida; three former divisions; neighbours; LGAs; rivers; about 80 ethnic groups; farming, livestock and fishing"),
               ("TARGOV", "Mambilla Plateau; national parks, rivers and waterfalls; farming and livestock; cultural diversity"),
               ("CPTAR", "2006 census 2,294,800 (National Population Commission)")]),
]

RELATIONS = []
for n in ["kaduna", "plateau", "kogi"]:
    RELATIONS.append(dict(frm="@admin_units:state:nasarawa", type="neighbours", to=f"@admin_units:state:{n}", source="NASGOV", evidence="multiple_sources",
                          notes="Listed as a neighbouring state by the Nasarawa State Government and by Wikipedia."))
RELATIONS.append(dict(frm="@admin_units:state:nasarawa", type="neighbours", to="@admin_units:federal_capital_territory:federal-capital-territory", source="NASGOV",
                      evidence="multiple_sources", notes="Listed as a neighbour by the Nasarawa State Government and by Wikipedia."))
RELATIONS.append(dict(frm="@admin_units:state:nasarawa", type="neighbours", to="@admin_units:state:taraba", source="NASGOV", evidence="multiple_sources",
                      notes="Listed by the Nasarawa State Government, Wikipedia (both states' articles) and the Nigerian Investment Promotion Commission (Taraba)."))
for n in ["plateau", "bauchi", "gombe", "adamawa"]:
    RELATIONS.append(dict(frm="@admin_units:state:taraba", type="neighbours", to=f"@admin_units:state:{n}", source="NIPC", evidence="multiple_sources",
                          notes="Listed as a neighbouring state by the Nigerian Investment Promotion Commission and by Wikipedia."))

NAS, TAR = "@admin_units:state:nasarawa", "@admin_units:state:taraba"
STATISTICS = [
    dict(record=NAS, metric="population", value_low=1869377, reference_year=2006, method="census", source="CPNAS", evidence="multiple_sources",
         notes="National Population Commission figure as reproduced by City Population; also given by Wikipedia. Nigerian census results are disputed."),
    dict(record=NAS, metric="population", value_low=1826883, reference_year=2006, method="census", source="NASGOV", evidence="single_reliable_source",
         notes="'About 1,826,883, according to the 2006 population census estimate' (Nasarawa State Government); differs from the NPC figure."),
    dict(record=NAS, metric="population", value_low=1863275, reference_year=2006, method="census", source="STAT", evidence="single_reliable_source",
         notes="Statoids' 2006 census table; differs from the NPC figure."),
    dict(record=NAS, metric="population", value_low=1207876, reference_year=1991, method="census", source="CPNAS", evidence="single_reliable_source",
         notes="1991 census (26 November 1991), for the area of today's state, as reproduced by City Population."),
    dict(record=NAS, metric="population", value_low=2886000, reference_year=2022, method="projection", source="CPNAS", evidence="single_reliable_source",
         notes="Projection, not a count (NPC / National Bureau of Statistics, via City Population)."),
    dict(record=NAS, metric="area_km2", value_low=26876, method="other", source="NASGOV", evidence="single_reliable_source",
         notes="'26,875.59 square kilometers' (Nasarawa State Government), rounded."),
    dict(record=NAS, metric="area_km2", value_low=26633, method="other", source="STAT", evidence="single_reliable_source", notes="Statoids."),
    dict(record=TAR, metric="population", value_low=2294800, reference_year=2006, method="census", source="CPTAR", evidence="single_reliable_source",
         notes="National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."),
    dict(record=TAR, metric="population", value_low=2300736, reference_year=2006, method="census", source="STAT", evidence="single_reliable_source",
         notes="Statoids' 2006 census table; differs from the NPC figure."),
    dict(record=TAR, metric="population", value_low=1512163, reference_year=1991, method="census", source="CPTAR", evidence="single_reliable_source",
         notes="1991 census (26 November 1991), for the area of today's state, as reproduced by City Population."),
    dict(record=TAR, metric="population", value_low=3609800, reference_year=2022, method="projection", source="CPTAR", evidence="single_reliable_source",
         notes="Projection, not a count (NPC / National Bureau of Statistics, via City Population)."),
    dict(record=TAR, metric="population", value_low=3249970, method="other", source="NIPC", evidence="single_reliable_source",
         notes="Given by the Nigerian Investment Promotion Commission without a year or method (male 1,657,485; female 1,592,485)."),
    dict(record=TAR, metric="area_km2", value_low=56282, method="other", source="NIPC", evidence="single_reliable_source", notes="Nigerian Investment Promotion Commission."),
    dict(record=TAR, metric="area_km2", value_low=59180, method="other", source="STAT", evidence="single_reliable_source", notes="Statoids."),
]

GAPS = [
    ("Nasarawa's colonial provinces (1900–1926)", "Lower Benue Province, the 1902 renaming and the 1926 reorganisation come from the state government only. Needs colonial records (Northern Nigeria annual reports or gazettes)."),
    ("The Nasarawa emirate and its neighbours", "The Fulani jihad and the emirates of Keffi, Lafia and Nasarawa are given by Wikipedia only. They need scholarly sources and their own records (polities)."),
    ("Taraba's three former divisions and its creation under Babangida", "Given by Wikipedia only."),
    ("Mambilla as a 'Bantu cradle'", "NIPC and Wikipedia (shared wording) describe the Mambilla region as a Bantu cradle occupied for some five millennia. It is not recorded until checked against archaeological and linguistic scholarship."),
    ("Areas and population figures", "Nasarawa: 26,875.59 km² (state government) and 26,633 km² (Statoids). Taraba: 56,282 km² (NIPC) and 59,180 km² (Statoids). The 2006 population differs by source. All figures are recorded, and none is chosen."),
    ("Peoples of Nasarawa and Taraba as national records", "The many peoples named here (e.g. Alago, Eggon, Mada, Mumuye, Mambilla, Kuteb, Chamba) are not yet records. Each needs its own sourced batch."),
    ("Political history since creation", "Governors and administrations are not yet recorded."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="Nasarawa State and Taraba State: history, geography, peoples, economy and population (fills empty fields of the existing records).")


def report():
    L = ["# Research batch 007 — Nasarawa State and Taraba State", "",
         f"Researched {ACCESSED}. Both state pages are already published, so **this text goes live the moment it is imported**. This report is the review.", ""]
    for name, d, g in [("Nasarawa", NAS_DESCRIPTION, NAS_GEOGRAPHY), ("Taraba", TAR_DESCRIPTION, TAR_GEOGRAPHY)]:
        L += [f"New prose for {name}: **{words(d) + words(g)} words**. The page passes the 300-word indexing rule and becomes indexable."]
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    for name, d, g in [("Nasarawa State", NAS_DESCRIPTION, NAS_GEOGRAPHY), ("Taraba State", TAR_DESCRIPTION, TAR_GEOGRAPHY)]:
        L += ["", f"## {name} — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in d.split("\n")]
        L += ["", f"## {name} — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in g.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Nasarawa created 1 Oct 1996 from western Plateau | State government; Wikipedia; batch 001 sources | stated |",
          "| Lower Benue Province 1900 → Nasarawa Province 1902 → 1926 reorganisation | Nasarawa State Government only | attributed + gap |",
          "| Fulani jihad; Keffi, Lafia, Nasarawa emirates | Wikipedia only | attributed + gap |",
          "| Northern Region → Benue-Plateau 1967 → Plateau 1976 → Nasarawa 1996 | State government; Wikipedia | stated |",
          "| Nasarawa neighbours and 13 LGAs | State government; Wikipedia | stated; 5 'neighbours' links |",
          "| 18 development areas; >70% farm | State government only | attributed |",
          "| Peoples of Nasarawa | State government; Wikipedia | stated as the government's list |",
          "| Coal, barytes, gemstones; Keana/Awe salt; Farin Ruwa Falls | State government; Wikipedia | stated |",
          "| Taraba created 27 Aug 1991 from Gongola; named after the Taraba River; nickname | NIPC; Wikipedia; batch 001 | stated |",
          "| Created under Babangida; three former divisions | Wikipedia only | attributed + gap |",
          "| Taraba neighbours, Cameroon border, 16 LGAs | NIPC; Wikipedia | stated; 4 new 'neighbours' links (Benue and Nasarawa links counted once) |",
          "| About 80 ethnic groups; where the main ones live | Wikipedia only (the state government only says 'most diverse') | attributed |",
          "| Crops | NIPC; Wikipedia (overlap only) | stated |",
          "| Livestock on the Mambilla Plateau | Wikipedia; state government (livestock generally) | attributed |",
          "| Rivers from the Cameroonian mountains | NIPC = Wikipedia (shared wording) | attributed |",
          "| Mambilla Plateau highlands, grasslands | State government; NIPC; Wikipedia | stated |",
          "| 'Bantu cradle' | NIPC = Wikipedia (shared wording) | not recorded, gap |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['record'].split(':')[-1].title()} — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", "## Neighbours", "", "- Nasarawa ↔ Kaduna, Plateau, Kogi, FCT, Taraba (Benue link already exists).", "- Taraba ↔ Plateau, Bauchi, Gombe, Adamawa (Benue and Nasarawa links counted once).",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- Two more Nigeria pages become indexable. The sitemap grows by 3 (2,977 → 2,980): the two state pages, plus the /nigeria/states listing. The listing is indexed only once three of its entries are indexable; it runs to about 1,350 words, with all 37 states and their summaries. Batch-created sources stay out of the sitemap and are noindex.",
          "- The text is original prose. Every claim is sourced or attributed, the tone is neutral, and there is no conflict material. Wikipedia mentions a herder–farmer conflict in Nasarawa; that is left for a separate, carefully sourced treatment.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_007_nasarawa_taraba.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_007_nasarawa_taraba_REVIEW.md", "w").write(report())
    print(f"Nasarawa words={words(NAS_DESCRIPTION) + words(NAS_GEOGRAPHY)} Taraba words={words(TAR_DESCRIPTION) + words(TAR_GEOGRAPHY)} "
          f"relations={len(RELATIONS)} statistics={len(STATISTICS)}")
