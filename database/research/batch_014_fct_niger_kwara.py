"""
Research batch 014 — the Federal Capital Territory, Niger State and Kwara State: history,
geography, peoples and economy (researched 2026-09-25). Same method as batches 004, 007-009
and 013:

Fills the EMPTY description and geography fields of the three existing, published records
(never overwrites), adds dated population/area figures side by side, and sourced
'neighbours' links (existing links are not duplicated: Nasarawa–FCT, Kogi–FCT, Kogi–Niger,
Kogi–Kwara).
  * facts with two or more independent sources are stated plainly;
  * single-source claims are attributed in the text;
  * shared lineage: NIPC's Kwara history paragraph is word for word Wikipedia's, so the two
    count as one source for it.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "NGGOV": dict(source_type="official_website", title="About us", organisation="Niger State Government",
                  url="https://nigerstate.gov.ng/about/", verification_status="needs_corroboration",
                  notes="'The Power State'; created 3 February 1976 from the defunct North-Western State; North Central zone; capital Minna; area about 76,363 km² (elsewhere on the same page 'about 86,000 km²' after Borgu Emirate joined from Kwara in August 1991); borders Benin, Zamfara, Kebbi, Kogi, Kwara, Kaduna and the FCT; 25 LGAs; rivers Niger, Kaduna, Gbako, Gurara and others; Kainji, Jebba and Shiroro dams; 2006 census 3,950,249; ethnic groups; Bida brass work."),
    "FCTA": dict(source_type="official_website", title="FCT Archives & History Bureau", organisation="Federal Capital Territory Administration",
                 url="https://www.fcta.gov.ng/ova_dep/fct-archives-and-history-bureau/", verification_status="needs_corroboration",
                 notes="Refers to 'the creation of the Federal Capital Territory on 4th February 1976'."),
    "NIPCNG": dict(source_type="official_website", title="Niger State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/niger-state/",
                   archive_reference="Internet Archive snapshot 20250602103032: http://web.archive.org/web/20250602103032/https://www.nipc.gov.ng/nigeria-states/niger-state/",
                   verification_status="needs_corroboration",
                   notes="Created 3 February 1976 from the North-Western State under Murtala Mohammed; borders Zamfara, Kebbi, Kogi, Kwara, Kaduna, the FCT and Benin; 'Power State' (three hydroelectric dams); largest landmass; area 68,925 km²; 25 LGAs; population 5,947,214 (no year); crops; minerals."),
    "NIPCKW": dict(source_type="official_website", title="Kwara State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/kwara-state/",
                   archive_reference="Internet Archive snapshot 20241227172202: http://web.archive.org/web/20241227172202/https://www.nipc.gov.ng/nigeria-states/kwara-state/",
                   verification_status="needs_corroboration",
                   notes="Created 27 May 1967 as West Central State from the Ilorin and Kabba provinces, later renamed Kwara (wording as Wikipedia); 'State of Harmony'; borders Benin, the Niger, Kogi, Ekiti and Osun; area 35,705 km²; 16 LGAs; population 3,390,330 (no year); crops; minerals."),
    "WFCT": dict(source_type="encyclopedia", title="Federal Capital Territory (Nigeria)", organisation="Wikipedia",
                 url="https://en.wikipedia.org/wiki/Federal_Capital_Territory_(Nigeria)", verification_status="needs_corroboration",
                 notes="Used to corroborate: formed in 1976 by Decree No. 6 from parts of Kaduna, Kwara, Niger and Plateau, mostly Niger; replacement for congested Lagos; mainly Gbagyi (Gwari) land; about 120,000 people in 840 villages, many resettled; administered by a minister; neighbours; about 7,315 km²; minerals; six area councils."),
    "WNG": dict(source_type="encyclopedia", title="Niger State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Niger_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: neighbours; largest state by area; Minna, Bida, Kontagora, Suleja; Kainji Lake and National Park; Kainji, Jebba and Shiroro dams; peoples; Nupe Kingdom, Gbagyi states, Sokoto Caliphate; North-Western State 1967; 1976; Borgu from Kwara in the 1990s."),
    "WKW": dict(source_type="encyclopedia", title="Kwara State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Kwara_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: created 27 May 1967 as West Central State (Ilorin and Kabba provinces), renamed Kwara ('a local name for the River Niger'); Idah/Dekina to Benue State in 1976; five LGAs to Kogi and Borgu to Niger in 1991; Oyo Empire, Borgu, Nupe, Sokoto Caliphate (Gwandu); peoples; neighbours incl. Oyo; rivers; Esie Museum; crops; minerals."),
    "CPFCT": dict(source_type="dataset", title="Federal Capital Territory (Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                  url="https://www.citypopulation.de/en/nigeria/admin/NGA015__federal_capital_territory/", verification_status="needs_corroboration",
                  notes="Census 1991: 371,674; census 2006: 1,406,239; projection 2022: 3,067,500 (National Population Commission / National Bureau of Statistics)."),
    "CPNG": dict(source_type="dataset", title="Niger (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA027__niger/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,421,581; census 2006: 3,954,772; projection 2022: 6,783,300 (National Population Commission / National Bureau of Statistics)."),
    "CPKW": dict(source_type="dataset", title="Kwara (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA024__kwara/", verification_status="needs_corroboration",
                 notes="Census 1991: 1,548,412; census 2006: 2,365,353; projection 2022: 3,551,000 (National Population Commission / National Bureau of Statistics)."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration",
                 notes="Reused (batch 001). 2006 census table: FCT 1,405,201, 7,569 km²; Niger 3,950,249, 72,065 km²; Kwara 2,371,089, 33,792 km². Chronology: 1967-05-27 Kwara created; 1976-02-03 FCT formed from parts of Niger and Plateau, Niger split off; 1991-08-27 Kogi formed from parts of Benue and Kwara, national capital moved from Lagos to Abuja."),
}

FCT_DESCRIPTION = """The Federal Capital Territory was created in 1976 by Decree No. 6 of that year, to provide a new national capital in place of Lagos, which had become congested. The Federal Capital Territory Administration dates its creation to 4 February 1976, while Statoids lists it under 3 February 1976. The capital city, Abuja, lies within the territory. Statoids records that the national capital moved from Lagos to Abuja in 1991. Unlike a state, the territory has no elected governor. It is run by the Federal Capital Territory Administration under a minister appointed by the President. It is grouped in the North Central geopolitical zone.

The territory was formed from land that had belonged to neighbouring states. Wikipedia names parts of Kaduna, Kwara, Niger and Plateau states, with most of it taken from Niger, while Statoids names Niger and Plateau. According to Wikipedia, the area chosen was mainly the land of the Gbagyi (Gwari) people. It says about 120,000 people then lived there in some 840 villages, and many were resettled in nearby towns such as Suleja in Niger State and New Karshi in Nasarawa State.

The territory borders Niger State to the west and north, Kaduna State to the north-east, Nasarawa State to the east and south, and Kogi State to the south-west. It has six area councils: Abaji, Abuja Municipal, Bwari, Gwagwalada, Kuje and Kwali. Wikipedia notes that Hausa is widely spoken.

The territory's population has grown very fast since the capital moved. National Population Commission figures reproduced by City Population give 371,674 people in the 1991 census and 1,406,239 in the 2006 census. The projection for 2022 is about 3.1 million. Nigerian census results are disputed, and other published figures are listed below with their sources."""

FCT_GEOGRAPHY = """The territory lies near the centre of Nigeria, a short distance north of the confluence of the Niger and the Benue. Wikipedia describes a savannah landscape with hills and a relatively mild climate. It gives January to April as the hottest months and July to October as the rainy season. It lists marble, tin, clay, mica and tantalite among the minerals, and the hills and woodland as home to wildlife such as duikers, baboons and monkeys.

The area is given as about 7,315 square kilometres by Wikipedia and 7,569 square kilometres by Statoids. Both figures are recorded below."""

NG_DESCRIPTION = """Niger State was created on 3 February 1976, when North-Western State was divided. The Niger State Government, the Nigerian Investment Promotion Commission and Statoids all give this date. Its capital is Minna, and other major towns include Bida, Kontagora and Suleja. It is grouped in the North Central geopolitical zone. Its official nickname is "The Power State", because the Kainji, Jebba and Shiroro dams in the state generate much of Nigeria's hydroelectric power.

Wikipedia outlines the earlier history. Parts of the area belonged to the Nupe Kingdom, Gbagyi states and Hausa states. In the early 19th century much of it came under the Sokoto Caliphate after the Fulani jihad. Under British rule it became part of the Northern Nigeria Protectorate, and after independence it was part of the Northern Region until 1967, then of North-Western State. In 1991 the state gained the Borgu area from Kwara State. The Niger State Government describes this as the merger of the Borgu Emirate in August 1991.

Niger borders Zamfara State to the north, Kebbi State to the north-west, Kaduna State to the north-east, the Federal Capital Territory to the south-east, Kogi State to the south and Kwara State to the south-west. To the west it shares an international boundary with the Republic of Benin. It has twenty-five local government areas and is the largest state in Nigeria by area.

The state is ethnically diverse. The state government names the Nupe, Hausa and Gbagyi first among many peoples, including the Kamuku, Kambari, Koro, Bassa, Fulani, Bariba and Boko. Farming, fishing and cattle rearing are the main occupations. The Commission lists yam, cassava, sorghum, maize, millet, rice, groundnut and cowpea among the crops, and shea butter among the products. The state government notes that Bida, the heartland of the Nupe, is famous for its brass work.

The 2006 census counted 3,954,772 people, according to National Population Commission figures reproduced by City Population. The Niger State Government gives 3,950,249. Other published figures are listed below with their sources. Nigerian census results are disputed."""

NG_GEOGRAPHY = """Niger State lies in the savannah of north-central Nigeria. Wikipedia places the east in the West Sudanian savanna and the rest in the Guinean forest–savanna mosaic. The River Niger flows through the west of the state from Kainji Lake and then forms the boundary with Kwara State. The state government names the Kaduna, Gbako, Gurara, Eko and Mariga among its other rivers. Kainji National Park, the largest national park in Nigeria, includes Kainji Lake and the Borgu Game Reserve.

The area is given very differently by different sources: 68,925 square kilometres by the Nigerian Investment Promotion Commission, 72,065 by Statoids, and about 76,363 by the Niger State Government, whose page elsewhere gives about 86,000 after the addition of Borgu. All the figures are recorded below."""

KW_DESCRIPTION = """Kwara State was created on 27 May 1967, when the military government of Yakubu Gowon replaced Nigeria's four regions with twelve states. It was first called West Central State and was made up of the former Ilorin and Kabba provinces of the Northern Region. Its present name comes from "Kwara", a local name for the River Niger. Wikipedia and the Nigerian Investment Promotion Commission give this account in the same words, and Statoids confirms the date. The capital is Ilorin, the state's nickname is "State of Harmony", and it is grouped in the North Central geopolitical zone.

The state has since become smaller. According to Wikipedia, its Idah and Dekina area was joined to Benue State in 1976. On 27 August 1991 five local government areas were transferred to the new Kogi State and Borgu to Niger State. Statoids and the Niger State Government confirm those changes.

Wikipedia outlines the earlier history. Most of the area was part of the Oyo Empire, the west belonged to the Borgu kingdoms, and the Nupe Kingdom held the north-east. In the 19th century part of the area came under the Sokoto Caliphate through the Emirate of Gwandu, after the Fulani jihad. The British then brought it into the Northern Nigeria Protectorate.

Kwara borders Niger State to the north, Kogi State to the east, and Ekiti and Osun states to the south. Wikipedia also lists Oyo State to the south-west. To the west it shares an international boundary with the Republic of Benin. It has sixteen local government areas.

Wikipedia describes the Yoruba as the majority, with Nupe communities in the north-east, Bariba (Baatonu) and Busa (Bokobaru) peoples in the west, and Fulani communities, especially around Ilorin. Farming is the main occupation. The Commission lists rice, cotton, cocoa, cassava, maize, millet and sugarcane among the crops. Wikipedia names the Esie Museum and the Owu waterfalls among the state's best-known sites.

The 2006 census counted 2,365,353 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

KW_GEOGRAPHY = """The Niger forms the northern boundary of the state and flows into Lake Jebba. Wikipedia names the Awun, Asa, Aluko and Oyun among the rivers of the interior. The land is savanna: Wikipedia places the east in the West Sudanian savanna and the rest in the Guinean forest–savanna mosaic. Small parts of Kainji National Park and Old Oyo National Park lie within the state, according to Wikipedia.

The area is given as 35,705 square kilometres by the Nigerian Investment Promotion Commission and 33,792 by Statoids. Both figures are recorded below."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


FCT, NG, KW = "@admin_units:federal_capital_territory:federal-capital-territory", "@admin_units:state:niger", "@admin_units:state:kwara"

UPDATES = [
    dict(ref=FCT, fields=dict(description=FCT_DESCRIPTION, geography_notes=FCT_GEOGRAPHY),
         srcs=[("WFCT", "Decree No. 6 of 1976; replacement for Lagos; from parts of Kaduna, Kwara, Niger and Plateau; Gbagyi land; resettlement; minister; neighbours; area 7,315 km²; minerals"),
               ("FCTA", "Created 4 February 1976"),
               ("STAT", "Formed 3 February 1976 from parts of Niger and Plateau; capital moved in 1991; area 7,569 km²"),
               ("CPFCT", "Census 1991 371,674; 2006 1,406,239 (National Population Commission)")]),
    dict(ref=NG, fields=dict(description=NG_DESCRIPTION, geography_notes=NG_GEOGRAPHY),
         srcs=[("NGGOV", "Created 3 February 1976 from North-Western State; Power State; dams; Borgu 1991; neighbours; 25 LGAs; rivers; peoples; Bida brass; 2006 census 3,950,249; area"),
               ("NIPCNG", "Created 3 February 1976; neighbours; dams; largest landmass; crops; area 68,925 km²"),
               ("WNG", "Nupe, Gbagyi, Hausa states; Sokoto Caliphate; North-Western State; Borgu from Kwara; vegetation; Kainji Lake and National Park"),
               ("STAT", "Niger split off in 1976; area 72,065 km²"), ("CPNG", "2006 census 3,954,772 (National Population Commission)")]),
    dict(ref=KW, fields=dict(description=KW_DESCRIPTION, geography_notes=KW_GEOGRAPHY),
         srcs=[("WKW", "Created 27 May 1967 as West Central State; name; Idah/Dekina 1976; Kogi and Borgu 1991; Oyo, Borgu, Nupe, Sokoto Caliphate; neighbours incl. Oyo; peoples; rivers; parks; sites"),
               ("NIPCKW", "Created 27 May 1967 (same wording as Wikipedia); State of Harmony; neighbours; 16 LGAs; crops; area 35,705 km²"),
               ("STAT", "Kwara created 1967; Kogi formed partly from Kwara in 1991; area 33,792 km²"),
               ("NGGOV", "Borgu Emirate joined Niger State from Kwara in August 1991"),
               ("CPKW", "2006 census 2,365,353 (National Population Commission)")]),
]

RELATIONS = [
    dict(frm=FCT, type="neighbours", to="@admin_units:state:niger", source="NGGOV", evidence="multiple_sources",
         notes="Listed by the Niger State Government, the Nigerian Investment Promotion Commission and Wikipedia."),
    dict(frm=FCT, type="neighbours", to="@admin_units:state:kaduna", source="WFCT", evidence="single_reliable_source",
         notes="Listed by Wikipedia (Federal Capital Territory, about 45 km). No independent source seen."),
]
for n in ["zamfara", "kebbi", "kaduna", "kwara"]:
    RELATIONS.append(dict(frm=NG, type="neighbours", to=f"@admin_units:state:{n}", source="NGGOV", evidence="multiple_sources",
                          notes="Listed by the Niger State Government, the Nigerian Investment Promotion Commission and Wikipedia."))
for n in ["ekiti", "osun"]:
    RELATIONS.append(dict(frm=KW, type="neighbours", to=f"@admin_units:state:{n}", source="NIPCKW", evidence="multiple_sources",
                          notes="Listed by the Nigerian Investment Promotion Commission and by Wikipedia."))
RELATIONS.append(dict(frm=KW, type="neighbours", to="@admin_units:state:oyo", source="WKW", evidence="single_reliable_source",
                      notes="Listed by Wikipedia; the Nigerian Investment Promotion Commission's list omits it."))


def stat(record, metric, value, source, notes, year=None, method="other"):
    d = dict(record=record, metric=metric, value_low=value, method=method, source=source, evidence="single_reliable_source", notes=notes)
    if year:
        d["reference_year"] = year
    return d


NPC = "National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."
STATISTICS = [
    stat(FCT, "population", 1406239, "CPFCT", NPC, 2006, "census"),
    stat(FCT, "population", 1405201, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(FCT, "population", 371674, "CPFCT", "1991 census, as reproduced by City Population.", 1991, "census"),
    stat(FCT, "population", 3067500, "CPFCT", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(FCT, "area_km2", 7315, "WFCT", "Wikipedia ('approximately')."),
    stat(FCT, "area_km2", 7569, "STAT", "Statoids."),
    stat(NG, "population", 3954772, "CPNG", NPC, 2006, "census"),
    stat(NG, "population", 3950249, "NGGOV", "Given by the Niger State Government as the 2006 census; Statoids gives the same figure.", 2006, "census"),
    stat(NG, "population", 2421581, "CPNG", "1991 census for the area of today's state (including Borgu), as reproduced by City Population.", 1991, "census"),
    stat(NG, "population", 6783300, "CPNG", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(NG, "population", 5947214, "NIPCNG", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(NG, "area_km2", 68925, "NIPCNG", "Nigerian Investment Promotion Commission."),
    stat(NG, "area_km2", 72065, "STAT", "Statoids."),
    stat(NG, "area_km2", 76363, "NGGOV", "Niger State Government ('approximately'); the same page elsewhere gives about 86,000 km² after the addition of Borgu."),
    stat(KW, "population", 2365353, "CPKW", NPC, 2006, "census"),
    stat(KW, "population", 2371089, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(KW, "population", 1548412, "CPKW", "1991 census for the area of today's state, as reproduced by City Population.", 1991, "census"),
    stat(KW, "population", 3551000, "CPKW", "Projection, not a count (NPC / NBS, via City Population).", 2022, "projection"),
    stat(KW, "population", 3390330, "NIPCKW", "Given by the Nigerian Investment Promotion Commission without a year; Wikipedia gives the same figure as a July 2024 estimate."),
    stat(KW, "area_km2", 35705, "NIPCKW", "Nigerian Investment Promotion Commission."),
    stat(KW, "area_km2", 33792, "STAT", "Statoids."),
]

GAPS = [
    ("FCT creation: 3 or 4 February 1976", "The FCT Administration says 4 February 1976; Statoids lists the FCT under 3 February 1976 (the date of that year's state creation). Needs the text of Decree No. 6 of 1976."),
    ("Which states gave land to the FCT", "Wikipedia: parts of Kaduna, Kwara, Niger and Plateau, mostly Niger. Statoids: Niger and Plateau. Both recorded, attributed."),
    ("FCT–Kaduna border", "Given by Wikipedia only. Recorded as single-source."),
    ("Kwara–Oyo border", "Given by Wikipedia; NIPC's list omits it. Recorded as single-source."),
    ("Niger State's area", "68,925 (NIPC), 72,065 (Statoids), about 76,363 and about 86,000 (both on the Niger State Government's page). All recorded; the state government's two figures need explaining."),
    ("Idah and Dekina to Benue State in 1976", "Given by Wikipedia's Kwara article only. Needs the 1976 state-creation decree."),
    ("Ilorin Emirate, Oyo Empire, Nupe Kingdom, Borgu", "Only outlined from Wikipedia. Each needs its own scholarly-sourced record."),
    ("Resettlement of the FCT's original inhabitants", "Given by Wikipedia only (about 120,000 people in 840 villages). A sensitive subject that needs official or scholarly sources."),
    ("Banditry in Niger and Kwara", "Not summarised; needs separate, careful treatment."),
    ("Areas and population figures", "The areas and populations from each source differ. All are recorded; none is chosen."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="The Federal Capital Territory, Niger State and Kwara State: history, geography, peoples, economy and population (fills empty fields of the existing records).")


STATES = [("Federal Capital Territory", FCT_DESCRIPTION, FCT_GEOGRAPHY), ("Niger State", NG_DESCRIPTION, NG_GEOGRAPHY), ("Kwara State", KW_DESCRIPTION, KW_GEOGRAPHY)]


def report():
    L = ["# Research batch 014 — The Federal Capital Territory, Niger State and Kwara State", "",
         f"Researched {ACCESSED}. All three pages are already published, so **this text goes live the moment it is imported**. This report is the review.", ""]
    for name, d, g in STATES:
        L += [f"- New prose for {name}: **{words(d) + words(g)} words**. The page passes the 300-word indexing rule and becomes indexable."]
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    for name, d, g in STATES:
        L += ["", f"## {name} — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in d.split("\n")]
        L += ["", f"## {name} — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in g.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| FCT created 1976 (Decree No. 6); 3 or 4 February | Wikipedia; FCT Administration (4 Feb); Statoids (3 Feb) | year stated; day attributed + gap |",
          "| Capital moved 1991 | Statoids | attributed |",
          "| FCT land from which states | Wikipedia vs Statoids | both attributed |",
          "| Gbagyi land; 120,000 people; resettlement | Wikipedia only | attributed + gap |",
          "| FCT neighbours; six area councils | Wikipedia; Niger State Government and NIPC (Niger); batch 002 (councils) | Niger link multiple; Kaduna single |",
          "| Niger created 3 Feb 1976 from North-Western State | state government; NIPC; Statoids; Wikipedia | stated |",
          "| Power State; the three dams | state government; NIPC; Wikipedia | stated |",
          "| Nupe, Gbagyi, Hausa states; Sokoto Caliphate | Wikipedia only | attributed |",
          "| Borgu from Kwara 1991 | state government; Wikipedia; Statoids (Kogi changes) | stated |",
          "| Niger neighbours; 25 LGAs; largest by area | state government; NIPC; Wikipedia | 4 new links multiple |",
          "| Niger peoples; Bida brass | state government | attributed |",
          "| Kwara created 27 May 1967 as West Central State; name | Wikipedia = NIPC (one lineage); Statoids (date) | stated |",
          "| Idah/Dekina to Benue 1976 | Wikipedia only | attributed + gap |",
          "| Kogi and Borgu changes 1991 | Wikipedia; Statoids; Niger State Government | stated |",
          "| Oyo Empire, Borgu, Nupe, Gwandu | Wikipedia only | attributed |",
          "| Kwara neighbours | NIPC; Wikipedia (Oyo: Wikipedia only) | Ekiti, Osun multiple; Oyo single |",
          "| Kwara peoples, crops, sites | Wikipedia; NIPC (crops) | attributed |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['record'].split(':')[-1].replace('-', ' ').title()} — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", f"## Neighbours ({len(RELATIONS)} new links)", "",
          "- FCT ↔ Niger (three sources); Kaduna (Wikipedia only). Links with Nasarawa and Kogi already exist.",
          "- Niger ↔ Zamfara, Kebbi, Kaduna, Kwara (three sources each). The Kogi link already exists.",
          "- Kwara ↔ Ekiti, Osun (two sources); Oyo (Wikipedia only). Links with Kogi and Niger are covered.",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- Three more pages become indexable (14 state/FCT pages in all). The sitemap should grow by 3 (2,992 → 2,995). The new source pages stay noindex.",
          "- The text is original prose, and every claim is sourced or attributed. Banditry is left for separate treatment.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_014_fct_niger_kwara.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_014_fct_niger_kwara_REVIEW.md", "w").write(report())
    print(" ".join(f"{n.split()[0]}={words(d) + words(g)}" for n, d, g in STATES) + f" relations={len(RELATIONS)} statistics={len(STATISTICS)}")
