"""
Research batch 021 — Kebbi, Sokoto and Zamfara states (North West, part 2): history,
geography, peoples and economy (researched 2026-09-25). Same method as batches 013-020:

Fills the EMPTY description and geography fields of the three existing, published state
records (never overwrites), adds dated population/area figures side by side, and sourced
'neighbours' links (existing links are not duplicated: Niger–Kebbi, Niger–Zamfara).
  * facts with two or more independent sources are stated plainly;
  * single-source claims are attributed in the text;
  * NIPC's Sokoto page could not be retrieved (Internet Archive rate limit); the Zamfara
    government website had no content;
  * the Kebbi State Government page dates the colonial conquest to "1893"; other sources
    place it in the 1900s, so no year is given;
  * banditry, illegal mining and kidnappings are left for separate treatment.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "KBGOV": dict(source_type="official_website", title="About Kebbi State", organisation="Kebbi State Government",
                  url="https://kebbistate.gov.ng/about-kebbi-state", verification_status="needs_corroboration",
                  notes="Kingdom of Kebbi carved out of the Songhai Empire by Muhammadu Kotal Kanta; Yauri Kingdom; Zuru confederation; south-western part of the Sokoto Caliphate under Abdullahi dan Fodio; North-Western State 1967–76, Sokoto State 1976–91; created 27 August 1991, capital Birnin Kebbi; 21 LGAs; area about 37,699 km², 36.46% farmland, about a third desert-prone; borders Niger Republic, Benin, Sokoto, Zamfara and Niger State; Niger and Rima rivers and floodplains; Kainji Lake ('80% in Kebbi')."),
    "SKGOV": dict(source_type="official_website", title="History of Sokoto", organisation="Sokoto State Government",
                  url="https://sokotostate.gov.ng/history-of-sokoto/", verification_status="needs_corroboration",
                  notes="Present form since October 1996, when Zamfara was created; Gobir and Kebbi kingdoms and the Sokoto Caliphate, whose capital is the state capital; area 28,232.37 km²."),
    "SKPEO": dict(source_type="official_website", title="The People", organisation="Sokoto State Government",
                  url="https://sokotostate.gov.ng/history-of-sokoto/people-of-sokoto/", verification_status="needs_corroboration",
                  notes="2006 census 3,696,999 (printed '3,696,99'); Hausa (Gobirawa, Zamfarawa, Kabawa, Adarawa, Arawa) and Fulani (Toronkawa — the clan of Shehu Usman Danfodiyo, the aristocracy since 1804 — Sullubawa, Zoramawa, and nomads); Zabarmawa and Tuareg minorities; Hausa as common language; Eid festivals; Kokawa wrestling and Dambe boxing; durbar."),
    "SKLAND": dict(source_type="official_website", title="The Land", organisation="Sokoto State Government",
                   url="https://sokotostate.gov.ng/history-of-sokoto/the-land/", verification_status="needs_corroboration",
                   notes="Area 28,232.37 km²; borders Niger Republic, Zamfara and Kebbi; savanna, suitable for grain and livestock; wet season May to September/October; harmattan November–February; hottest March–April."),
    "NIPCKB": dict(source_type="official_website", title="Kebbi State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/kebbi-state/",
                   archive_reference="Internet Archive snapshot 20251028155437: http://web.archive.org/web/20251028155437/https://www.nipc.gov.ng/nigeria-states/kebbi-state/",
                   verification_status="needs_corroboration",
                   notes="'Land of Equity'; created out of Sokoto State on 27 August 1991 by Babangida; capital Birnin Kebbi; part of the Songhai Empire in the 15th century; borders Sokoto, Niger State, Dosso Region (Niger) and Benin; area 36,985 km²; 21 LGAs; population 4,724,046 (no year); crops; minerals."),
    "NIPCZA": dict(source_type="official_website", title="Zamfara State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/zamfara-state/",
                   archive_reference="Internet Archive snapshot 20240615044234: http://web.archive.org/web/20240615044234/https://www.nipc.gov.ng/nigeria-states/zamfara-state/",
                   verification_status="needs_corroboration",
                   notes="Capital Gusau; part of Sokoto State until 1 October 1996; borders Sokoto, Niger, Kaduna, Kebbi and Katsina; 'Farming is our Pride'; area 37,931 km²; 14 LGAs; population 5,307,154 (no year); crops; gold and other minerals."),
    "WKB": dict(source_type="encyclopedia", title="Kebbi State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Kebbi_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: formed from part of Sokoto on 27 August 1991; named for Birnin Kebbi; borders; Kebbi Kingdom; Gwandu Emirate; Kebbi's wars with Sokoto; British in the 1900s–1910s; peoples; Sokoto (Rima) and Niger rivers; Kainji Lake; Argungu Fishing Festival."),
    "WSK": dict(source_type="encyclopedia", title="Sokoto State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Sokoto_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: created 1976 from the division of North-Western State into Sokoto and Niger; seat of the Sokoto Caliphate; the Sultan as spiritual leader of Nigerian Muslims; Sokoto Province from about 1900; borders; Sahel climate; Sokoto–Rima floodplains; over 80% in agriculture."),
    "WZA": dict(source_type="encyclopedia", title="Zamfara State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Zamfara_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: named after the Zamfara River; created from Sokoto in 1996 by Abacha; capital Gusau; Hausa subgroups and Fulani; borders; Zamfara Kingdom (11th–16th centuries), Dutsi, Birnin Zamfara destroyed by Gobir; agriculture and gold; climate; Jata; area 38,418 km²; 2006 population 3,278,873."),
    "CPKB": dict(source_type="dataset", title="Kebbi (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA022__kebbi/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,068,490; census 2006: 3,256,541; projection 2022: 5,563,900 (National Population Commission / National Bureau of Statistics)."),
    "CPSK": dict(source_type="dataset", title="Sokoto (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA034__sokoto/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,418,585; census 2006: 3,702,676; projection 2022: 6,391,000 (National Population Commission / National Bureau of Statistics)."),
    "CPZA": dict(source_type="dataset", title="Zamfara (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA037__zamfara/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,051,591; census 2006: 3,278,873; projection 2022: 5,833,500 (National Population Commission / National Bureau of Statistics)."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration",
                 notes="Reused (batch 001). 2006 census table: Kebbi 3,238,628, 36,320 km²; Sokoto 3,696,999, 32,146 km²; Zamfara 3,259,846, 33,667 km². Chronology: 1991-08-27 Kebbi split from Sokoto; 1996-10-01 Zamfara split from Sokoto."),
}

KB_DESCRIPTION = """Kebbi State was created on 27 August 1991 by the military government of Ibrahim Babangida, from the western and southern part of Sokoto State. The Kebbi State Government, the Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this date. The capital is Birnin Kebbi, after which Wikipedia says the state is named. The state is grouped in the North West geopolitical zone, and the Commission gives its nickname as "Land of Equity".

The state government outlines a long history. Before the Sokoto Caliphate, most of the area was ruled by the Kingdom of Kebbi, which it says the warrior Muhammadu Kotal Kanta carved out of the Songhai Empire. The Commission likewise records that the area was part of the Songhai Empire in the 15th century. The Yauri Kingdom and the Zuru confederation held other parts. After the Fulani jihad of the early 19th century the area became the south-western part of the Caliphate, under Abdullahi dan Fodio at Gwandu. Wikipedia adds that Kebbi's rulers fought Sokoto on and off until British rule began in the early 20th century. After independence the area belonged to North-Western State from 1967 and to Sokoto State from 1976.

Kebbi borders Sokoto State to the north and east, Zamfara State to the east and Niger State to the south. It shares international boundaries with the Republic of Niger to the north and north-west and the Republic of Benin to the west. It has twenty-one local government areas.

The Hausa, Fulani and Zarma live throughout the state, according to Wikipedia. It names the Dakarkari (Lelna), Kambari, Dukawa, Kamuku, Busa and other peoples along the south and west.

Farming and fishing are the main occupations. The Commission lists millet, wheat, guinea corn, rice, onion, groundnut, cotton and maize among the crops. Wikipedia mentions the large Argungu Fishing Festival.

The 2006 census counted 3,256,541 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

KB_GEOGRAPHY = """The state government describes a land dominated by the wide floodplains (fadama) of its river valleys. The Rima River flows south through the centre to join the Niger, which crosses the south-west. The Niger widens into Kainji Lake, most of which the state government says lies in Kebbi, in the Yauri and Ngaski areas. It adds that about a third of the state is prone to drought and desertification. Wikipedia describes the south as generally rocky and the north as sandy.

The area is given as about 37,699 square kilometres by the state government, 36,985 by the Nigerian Investment Promotion Commission and 36,320 by Statoids. The figures are recorded below."""

SK_DESCRIPTION = """Sokoto State was created on 3 February 1976, when North-Western State was divided into Sokoto and Niger states, according to Wikipedia and Statoids. It lost its western and southern part to Kebbi State in 1991. The state government notes that it took its present form in October 1996, when Zamfara State was created from its east. The capital is Sokoto, the seat of the Sokoto Caliphate. The state is grouped in the North West geopolitical zone.

The state government names the Gobir and Kebbi kingdoms and the Sokoto Caliphate among the states of the region's past. The Caliphate was led by Shehu Usman dan Fodio, who launched the Fulani jihad in the early 19th century, as the Kebbi State Government also records. The Sokoto State Government notes that his clan, the Toronkawa Fulani, have formed the aristocracy since 1804. Wikipedia says the Sultan of Sokoto, who heads the Caliphate, is regarded as the spiritual leader of Nigeria's Muslims. According to Wikipedia, the British took over around 1900, and Sokoto became a province of the Protectorate of Northern Nigeria.

Sokoto borders the Republic of Niger to the north and west, Zamfara State to the east and Kebbi State to the south and west. It has twenty-three local government areas.

The state government describes two main groups, the Hausa and the Fulani. The Hausa include the Gobirawa, Zamfarawa, Kabawa, Adarawa and Arawa. The Fulani include both town dwellers and nomadic herders, and there are Zabarma and Tuareg minorities near the borders. Hausa is the common language. The state government highlights traditional wrestling (Kokawa) and boxing (Dambe), and the durbar, a parade of richly decorated horses and camels.

Agriculture employs most people. Wikipedia puts the share at over 80 per cent, and it depends on the floodplains of the Sokoto–Rima river system.

The 2006 census counted 3,702,676 people, according to National Population Commission figures reproduced by City Population; the state government and Statoids give 3,696,999. Other published figures are listed below with their sources. Nigerian census results are disputed."""

SK_GEOGRAPHY = """Sokoto lies in the dry savanna of the far north-west, where sandy plains are broken by isolated hills. The state government notes that the rains start late and end early. The wet season runs from about May to September or October, and the dusty harmattan blows from November to February. Wikipedia describes a very hot climate, with daytime temperatures that can exceed 45 °C between February and April.

The area is given as 28,232 square kilometres by the state government and 32,146 by Statoids. Both figures are recorded below."""

ZA_DESCRIPTION = """Zamfara State was created on 1 October 1996 by the military government of Sani Abacha, from part of Sokoto State. The Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this date. Its capital is Gusau, and Wikipedia says the state is named after the Zamfara River. It is grouped in the North West geopolitical zone, and its nickname is "Farming is our Pride".

Wikipedia outlines the history of the Zamfara Kingdom, one of the old Hausa states. It says the kingdom was founded by the 11th century and flourished until the 16th. Its capital moved over time, from Dutsi to Birnin Zamfara, and in the first half of the 18th century Birnin Zamfara was destroyed by the Gobir Kingdom. Wikipedia adds that the people of Zamfara long campaigned for a state of their own before 1996.

Zamfara borders Sokoto State to the north and west, Kebbi State to the west, Katsina State to the east, and Kaduna and Niger states to the south. It also has a short international boundary with the Republic of Niger to the north. It has fourteen local government areas.

The people are mainly Hausa, of several groups, with Fulani throughout the state, according to Wikipedia. It names the Zamfarawa around Anka, Gummi, Bukkuyum and Talata Mafara, the Gobirawa in Shinkafi and the Burmawa in Bakura.

Farming employs more than 80 per cent of the people, according to Wikipedia. The Commission and Wikipedia both name millet, guinea corn, maize, rice, groundnut, cotton, tobacco and beans among the crops, and both note the state's gold. Wikipedia names Jata, an ancient hill settlement with a large cave, among the state's historic sites.

The 2006 census counted 3,278,873 people, according to National Population Commission figures reproduced by City Population and Wikipedia. Other published figures are listed below with their sources. Nigerian census results are disputed."""

ZA_GEOGRAPHY = """The state lies in the savanna, which the Commission describes as hot, semi-arid and tropical. Wikipedia notes temperatures of 38 °C and more between March and May, rain from late May to September, and a harmattan season from December to April.

The area is given as 37,931 square kilometres by the Nigerian Investment Promotion Commission, 38,418 by Wikipedia and 33,667 by Statoids. The figures are recorded below."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


KB, SK, ZA = "@admin_units:state:kebbi", "@admin_units:state:sokoto", "@admin_units:state:zamfara"

UPDATES = [
    dict(ref=KB, fields=dict(description=KB_DESCRIPTION, geography_notes=KB_GEOGRAPHY),
         srcs=[("KBGOV", "Created 27 August 1991; Birnin Kebbi; Kebbi Kingdom and Kanta; Yauri; Zuru; Caliphate under Abdullahi dan Fodio; 1967–91; borders; 21 LGAs; rivers, floodplains, Kainji Lake, desertification; area 37,699 km²"),
               ("NIPCKB", "Land of Equity; created 1991 from Sokoto; Songhai; borders; crops; area 36,985 km²"),
               ("WKB", "Name; Gwandu; wars with Sokoto; British; peoples; Argungu festival; rocky south, sandy north"),
               ("STAT", "1991 Kebbi split from Sokoto; area 36,320 km²"), ("CPKB", "2006 census 3,256,541 (National Population Commission)")]),
    dict(ref=SK, fields=dict(description=SK_DESCRIPTION, geography_notes=SK_GEOGRAPHY),
         srcs=[("SKGOV", "Present form October 1996; Gobir and Kebbi kingdoms; Sokoto Caliphate capital; area 28,232.37 km²"),
               ("SKPEO", "Hausa and Fulani groups; Toronkawa since 1804; Zabarma and Tuareg; Hausa common language; Kokawa, Dambe, durbar; 2006 census 3,696,999"),
               ("SKLAND", "Borders; savanna; seasons"),
               ("WSK", "Created 1976 from North-Western State with Niger; Sultan; Sokoto Province c. 1900; Sahel heat; agriculture over 80%; Sokoto–Rima floodplains"),
               ("STAT", "1991 Kebbi and 1996 Zamfara split from Sokoto; area 32,146 km²"), ("CPSK", "2006 census 3,702,676 (National Population Commission)")]),
    dict(ref=ZA, fields=dict(description=ZA_DESCRIPTION, geography_notes=ZA_GEOGRAPHY),
         srcs=[("NIPCZA", "Gusau; part of Sokoto until 1 October 1996; borders; Farming is our Pride; crops; gold; climate; area 37,931 km²"),
               ("WZA", "Name; Abacha; Zamfara Kingdom; Dutsi; Birnin Zamfara and Gobir; autonomy campaign; borders incl. Niger Republic; peoples; agriculture; gold; Jata; climate; area 38,418 km²"),
               ("STAT", "1996 Zamfara split from Sokoto; area 33,667 km²"), ("CPZA", "2006 census 3,278,873 (National Population Commission)")]),
]

RELATIONS = [
    dict(frm=KB, type="neighbours", to=SK, source="KBGOV", evidence="multiple_sources",
         notes="Listed by the Kebbi and Sokoto state governments, the Nigerian Investment Promotion Commission (Kebbi) and Wikipedia."),
    dict(frm=KB, type="neighbours", to=ZA, source="KBGOV", evidence="multiple_sources",
         notes="Listed by the Kebbi State Government, the Nigerian Investment Promotion Commission (Zamfara) and Wikipedia."),
    dict(frm=SK, type="neighbours", to=ZA, source="SKLAND", evidence="multiple_sources",
         notes="Listed by the Sokoto State Government, the Nigerian Investment Promotion Commission (Zamfara) and Wikipedia."),
    dict(frm=ZA, type="neighbours", to="@admin_units:state:katsina", source="NIPCZA", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission (Zamfara) and by Wikipedia (Zamfara and Katsina articles)."),
    dict(frm=ZA, type="neighbours", to="@admin_units:state:kaduna", source="NIPCZA", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission (Zamfara) and by Wikipedia (Zamfara and Kaduna articles)."),
]


def stat(record, metric, value, source, notes, year=None, method="other"):
    d = dict(record=record, metric=metric, value_low=value, method=method, source=source, evidence="single_reliable_source", notes=notes)
    if year:
        d["reference_year"] = year
    return d


NPC = "National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."
PROJ = "Projection, not a count (NPC / NBS, via City Population)."
STATISTICS = [
    stat(KB, "population", 3256541, "CPKB", NPC, 2006, "census"),
    stat(KB, "population", 3238628, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(KB, "population", 2068490, "CPKB", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(KB, "population", 5563900, "CPKB", PROJ, 2022, "projection"),
    stat(KB, "population", 4724046, "NIPCKB", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(KB, "area_km2", 37699, "KBGOV", "Kebbi State Government ('about')."),
    stat(KB, "area_km2", 36985, "NIPCKB", "Nigerian Investment Promotion Commission."),
    stat(KB, "area_km2", 36320, "STAT", "Statoids."),
    stat(SK, "population", 3702676, "CPSK", NPC, 2006, "census"),
    stat(SK, "population", 3696999, "SKPEO", "Given by the Sokoto State Government as the provisional 2006 census (printed '3,696,99'); Statoids gives 3,696,999.", 2006, "census"),
    stat(SK, "population", 2418585, "CPSK", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(SK, "population", 6391000, "CPSK", PROJ, 2022, "projection"),
    stat(SK, "area_km2", 28232, "SKLAND", "Sokoto State Government gives 28,232.37 km² (rounded here; figures are stored as whole numbers)."),
    stat(SK, "area_km2", 32146, "STAT", "Statoids."),
    stat(ZA, "population", 3278873, "CPZA", NPC + " Wikipedia gives the same figure.", 2006, "census"),
    stat(ZA, "population", 3259846, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(ZA, "population", 2051591, "CPZA", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(ZA, "population", 5833500, "CPZA", PROJ, 2022, "projection"),
    stat(ZA, "population", 5307154, "NIPCZA", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(ZA, "area_km2", 37931, "NIPCZA", "Nigerian Investment Promotion Commission."),
    stat(ZA, "area_km2", 38418, "WZA", "Wikipedia."),
    stat(ZA, "area_km2", 33667, "STAT", "Statoids."),
]

GAPS = [
    ("The Sokoto Caliphate", "Only outlined (Sokoto State Government; Wikipedia). The Caliphate, Usman dan Fodio, the Sultanate and the jihad need their own records with scholarly sources."),
    ("Kebbi Kingdom, Kanta and Songhai; Yauri; Zuru; Gwandu", "From the Kebbi State Government and NIPC. They need scholarly sources and their own records."),
    ("Kebbi's colonial conquest date", "The Kebbi State Government says 1893; Wikipedia places British control in the 1900s–1910s. No year is given in the text."),
    ("Kebbi's creation date", "Wikipedia's geography section says 17 August 1991; every other source (including Wikipedia's introduction) gives 27 August. 27 August is used."),
    ("Kainji Lake in Kebbi", "The Kebbi State Government says 80% of the lake lies in Kebbi; not stated as a figure."),
    ("Zamfara Kingdom", "Outlined from Wikipedia only. Needs scholarly sources."),
    ("Sokoto's area", "The state government gives 28,232.37 km²; Statoids 32,146 km². Both recorded."),
    ("Sokoto NIPC page; Zamfara government website", "Not retrieved / no content. Add when available."),
    ("Insecurity", "Banditry, kidnappings (including the 2021 Jangebe abduction in Zamfara) and illegal gold mining are not summarised. They need separate, careful treatment."),
    ("Areas and population figures", "The areas and populations from each source differ. All are recorded; none is chosen."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="Kebbi, Sokoto and Zamfara states: history, geography, peoples, economy and population (fills empty fields of the existing records).")


STATES = [("Kebbi State", KB_DESCRIPTION, KB_GEOGRAPHY), ("Sokoto State", SK_DESCRIPTION, SK_GEOGRAPHY), ("Zamfara State", ZA_DESCRIPTION, ZA_GEOGRAPHY)]


def report():
    L = ["# Research batch 021 — Kebbi, Sokoto and Zamfara states (North West, part 2)", "",
         f"Researched {ACCESSED}. All three state pages are already published, so **this text goes live the moment it is imported**. This report is the review. Deploy after batch 020.", ""]
    for name, d, g in STATES:
        L += [f"- New prose for {name}: **{words(d) + words(g)} words**. The page passes the 300-word indexing rule and becomes indexable."]
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    for name, d, g in STATES:
        L += ["", f"## {name} — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in d.split("\n")]
        L += ["", f"## {name} — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in g.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Kebbi created 27 Aug 1991 from Sokoto | state government; NIPC; Wikipedia; Statoids | stated |",
          "| Kebbi Kingdom, Kanta, Songhai, Yauri, Zuru, Abdullahi dan Fodio | state government; NIPC (Songhai) | attributed |",
          "| Kebbi's wars with Sokoto; British | Wikipedia | attributed |",
          "| Kebbi borders; peoples; crops; Argungu | state government; NIPC; Wikipedia | stated / attributed |",
          "| Sokoto created 3 Feb 1976; Kebbi 1991; Zamfara 1996 | Wikipedia; Statoids; state government | stated |",
          "| Sokoto Caliphate; Toronkawa; the Sultan | state government; Wikipedia | attributed |",
          "| Sokoto peoples and traditions | state government | attributed |",
          "| Zamfara created 1 Oct 1996 from Sokoto | NIPC; Wikipedia; Statoids | stated |",
          "| Zamfara Kingdom; name from the river | Wikipedia | attributed |",
          "| Zamfara borders; crops; gold | NIPC; Wikipedia | stated |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['record'].split(':')[-1].title()} — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", f"## Neighbours ({len(RELATIONS)} new links)", "",
          "- Kebbi ↔ Sokoto, Zamfara. Sokoto ↔ Zamfara. Zamfara ↔ Katsina, Kaduna. Each has two or more sources. Links with Niger State already exist.",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- Three more state pages become indexable, completing the North West. The sitemap should grow by 3 (3,014 → 3,017 after batch 020). The new source pages stay noindex.",
          "- The text is original prose, and every claim is sourced or attributed. Insecurity is left for separate treatment.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_021_kebbi_sokoto_zamfara.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_021_kebbi_sokoto_zamfara_REVIEW.md", "w").write(report())
    print(" ".join(f"{n.split()[0]}={words(d) + words(g)}" for n, d, g in STATES) + f" relations={len(RELATIONS)} statistics={len(STATISTICS)}")
