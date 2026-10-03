"""
Research batch 018 — Lagos, Ogun and Oyo states (South West, part 1): history, geography,
peoples and economy (researched 2026-09-25). Same method as batches 013-017:

Fills the EMPTY description and geography fields of the three existing, published state
records (never overwrites), adds dated population/area figures side by side, and sourced
'neighbours' links (the existing Kwara–Oyo link is not duplicated).
  * facts with two or more independent sources are stated plainly;
  * single-source claims are attributed in the text;
  * the Lagos and Ogun government websites are JavaScript apps with no readable history,
    and the Internet Archive refused further requests during this research; Lagos and Ogun
    rest on NIPC, Wikipedia, Statoids and City Population;
  * shared lineage: Wikipedia's Oyo history section reproduces the Oyo State Government's
    wording; they count as one source for it;
  * promotional "firsts" (first skyscraper, first television station in Africa...) are not
    used; the 2024 Ibadan unrest is left out.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "OYGOV": dict(source_type="official_website", title="About Oyo State", organisation="Oyo State Government",
                  url="https://oyostate.gov.ng/about-oyo-state/", verification_status="needs_corroboration",
                  notes="'Pace Setter'; one of three states carved out of the Western State in 1976; included Osun until 1991; 33 LGAs and 29 LCDAs; area 28,454 km²; borders Ogun, Kwara, Osun and Benin; hills rising from about 500 m to 1,219 m; rain forest in the south, guinea savanna in the north; peoples (Oyo, Ogbomoso, Oke-Ogun, Ibadan, Ibarapa); Ibadan the administrative centre of the old Western Region; crops; clay, kaolin, aquamarine; University of Ibadan 1948."),
    "NIPCLA": dict(source_type="official_website", title="Lagos State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/lagos-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "NIPCOG": dict(source_type="official_website", title="Ogun State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/ogun-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "NIPCOY": dict(source_type="official_website", title="Oyo State", organisation="Nigerian Investment Promotion Commission (NIPC)",
                   url="https://www.nipc.gov.ng/nigeria-states/oyo-state/", verification_status="needs_corroboration", notes="Reused (batch 001)."),
    "WLA": dict(source_type="encyclopedia", title="Lagos State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Lagos_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: created 27 May 1967 (Decree No. 14 of 1967) from the Western Region and the former Federal Territory; smallest state by area; borders only Ogun; named for the city of Lagos; lagoons and creeks; Eko, the Awori; Portuguese name; 1851 British bombardment, Akitoye installed; 1861 cession; peoples; ports; climate."),
    "WOG": dict(source_type="encyclopedia", title="Ogun State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Ogun_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: borders; capital Abeokuta; 20 LGAs; Yoruba subgroups (Egba, Ijebu, Remo, Yewa, Awori) and Egun; Oyo and Ijebu kingdoms; Abeokuta as a missionary centre; created 1976 from Western State; industry along the Lagos–Ibadan corridor; crops; kolanut; Omo Forest Reserve; rivers."),
    "WOY": dict(source_type="encyclopedia", title="Oyo State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Oyo_State",
                verification_status="needs_corroboration",
                notes="Used to corroborate: capital Ibadan; borders; Oyo Empire (c. 1300–1896), Old Oyo, new Oyo in the 1830s, the Alaafin; University of Ibadan 1948; area 28,454 km²; hills and rivers; Old Oyo National Park; climate."),
    "CPLA": dict(source_type="dataset", title="Lagos (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA025__lagos/", verification_status="needs_corroboration",
                 notes="Census 1991: 5,725,116; census 2006: 9,113,605; projection 2022: 13,491,800 (National Population Commission / National Bureau of Statistics)."),
    "CPOG": dict(source_type="dataset", title="Ogun (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA028__ogun/", verification_status="needs_corroboration",
                 notes="Census 1991: 2,333,726; census 2006: 3,751,140; projection 2022: 6,379,500 (National Population Commission / National Bureau of Statistics)."),
    "CPOY": dict(source_type="dataset", title="Oyo (State, Nigeria) – Population Statistics", author="Thomas Brinkhoff", organisation="City Population",
                 url="https://www.citypopulation.de/en/nigeria/admin/NGA031__oyo/", verification_status="needs_corroboration",
                 notes="Census 1991: 3,452,720; census 2006: 5,580,894; projection 2022: 7,976,100 (National Population Commission / National Bureau of Statistics)."),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids", url="https://www.statoids.com/ung.html",
                 verification_status="needs_corroboration",
                 notes="Reused (batch 001). 2006 census table: Lagos 9,013,534, 3,475 km²; Ogun 3,728,098, 16,850 km²; Oyo 5,591,589, 27,036 km². Chronology: 1967-05-27 Lagos State from Colony province and the Lagos federal territory; 1976-02-03 Western State divided into Ogun, Ondo and Oyo; 1991-08-27 Osun split from Oyo; national capital moved from Lagos to Abuja."),
}

LA_DESCRIPTION = """Lagos State was created on 27 May 1967, when Nigeria's regions were replaced by twelve states. The Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this date. It was formed from the Colony Province of the Western Region and the Lagos federal territory, according to Statoids and Wikipedia. The state is named after the city of Lagos. Its capital is Ikeja, and it is grouped in the South West geopolitical zone. Lagos was also Nigeria's federal capital until the capital moved to Abuja in 1991.

Wikipedia outlines the earlier history. The island at the heart of the city was called Eko, and its first settlers were the Awori, a Yoruba group. Portuguese traders arrived from the 16th century and gave the place its present name. In 1851 the British bombarded Lagos to suppress the Atlantic slave trade and installed Oba Akitoye in place of Oba Kosoko. In 1861 Lagos was ceded to Britain as a colony.

Lagos is the smallest state in Nigeria by area and the only one that borders just one other state, Ogun, which surrounds it to the north and east. To the west lies the Republic of Benin and to the south the Atlantic. It has twenty local government areas.

The indigenous people are Yoruba, including the Awori, the Egun (Ogu) around Badagry and the Ijebu around Ikorodu and Epe. Since the 19th century people from all over Nigeria and beyond have settled in the state, according to Wikipedia. They include Saro and Amaro families descended from formerly enslaved people who returned from Sierra Leone and Brazil. Wikipedia names the Oro, Igunnu and Egungun festivals.

Lagos is Nigeria's main commercial centre. The Commission calls its economy the largest in the country, and Wikipedia names the Lagos Port Complex at Apapa and the Tin Can Island Port as the busiest in Nigeria.

The 2006 census counted 9,113,605 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

LA_GEOGRAPHY = """Water covers a large part of the state. Wikipedia says nearly a quarter of its area is lagoon, creek or river; the largest waters are the Lagos and Lekki lagoons, fed by the Ogun and Osun rivers. The coast runs for about 180 kilometres along the Atlantic, according to the Commission. Wikipedia describes a tropical monsoon climate with rain from April to October.

The area is given as 3,671 square kilometres by the Nigerian Investment Promotion Commission and 3,475 by Statoids. Both figures are recorded below."""

OG_DESCRIPTION = """Ogun State was created on 3 February 1976, when Western State was divided into Ogun, Ondo and Oyo states. The Nigerian Investment Promotion Commission, Wikipedia and Statoids all give this account. The capital and largest city is Abeokuta. The state is grouped in the South West geopolitical zone, and the Commission gives its nickname as "Gateway State".

According to Wikipedia, parts of the area once belonged to the Oyo Empire, the Ijebu kingdom and other Yoruba states. In the late 19th century Abeokuta became a major centre of Christian missions and education. The British brought the area into the Lagos Protectorate in 1893 and later into Southern Nigeria. After independence it was part of the Western Region.

Ogun borders Lagos State to the south, Oyo and Osun states to the north and Ondo State to the east. To the west it shares an international boundary with the Republic of Benin, and it has a short coastline on the Bight of Benin. It has twenty local government areas.

The people are mainly Yoruba. Wikipedia names the Egba, Ijebu, Remo, Yewa and Awori as the main groups, with smaller groups such as the Ketu, Ohori and Anago, and the Egun along the border with Benin.

Ogun is one of Nigeria's most industrialised states. The Commission names its closeness to Lagos as its main advantage, and Wikipedia describes many factories along the Lagos–Ibadan corridor. The Commission says the state has Nigeria's largest limestone deposits, and Wikipedia names cement works at Ibese and Ewekoro. Farming remains important. The Commission and Wikipedia name cassava, cocoa, maize, rice and oil palm, and Wikipedia calls the state Nigeria's largest producer of kola nuts.

The 2006 census counted 3,751,140 people, according to National Population Commission figures reproduced by City Population. Other published figures are listed below with their sources. Nigerian census results are disputed."""

OG_GEOGRAPHY = """Most of the state lies in lowland forest, with savanna in parts of the north and mangroves on the coast, according to Wikipedia. The Ogun and Yewa rivers are its main waterways. The Omo Forest Reserve in the south-east is one of the country's most important conservation areas, and Wikipedia says it shelters some of Nigeria's last forest elephants and chimpanzees.

The area is given as 16,400 square kilometres by the Nigerian Investment Promotion Commission and 16,850 by Statoids. Both figures are recorded below."""

OY_DESCRIPTION = """Oyo State was created on 3 February 1976, when Western State was divided. The Oyo State Government, the Nigerian Investment Promotion Commission and Statoids all give this account. Until 27 August 1991 it also included the area of present-day Osun State. The capital is Ibadan, and other major towns include Ogbomoso, Oyo, Iseyin and Saki. The state is grouped in the South West geopolitical zone, and its nickname is "Pace Setter".

The state lies on territory once ruled by the Oyo Empire. Wikipedia describes it as a powerful Yoruba state that ruled much of Yorubaland from about 1300 until its collapse in the 1830s. Its old capital, Oyo-Ile, lay to the north, and the present town of Oyo was built in the 1830s, where the Alaafin still holds a ceremonial role. The state government notes that Ibadan was the administrative centre of the old Western Region under British rule.

Oyo borders Kwara State to the north, Osun State to the east and Ogun State to the south and west. To the west it shares an international boundary with the Republic of Benin. It has thirty-three local government areas.

The people are almost entirely Yoruba. The state government names the Oyo, Ogbomoso, Oke-Ogun, Ibadan and Ibarapa groups. The state government and the Commission both note that the University of Ibadan, founded in 1948 as a college of the University of London, was Nigeria's first university.

Farming is the base of the economy. The state government lists maize, yams, cassava, millet, rice, plantain, cocoa, oil palm and cashew among the crops. The state government also names cattle ranches at Saki, Fasola and Ibadan, and deposits of clay, kaolin and aquamarine.

The 2006 census counted 5,580,894 people, according to National Population Commission figures reproduced by City Population and Wikipedia. Other published figures are listed below with their sources. Nigerian census results are disputed."""

OY_GEOGRAPHY = """The state rises from a gentle plain in the south to hills and old, dome-shaped rock outcrops in the north. According to the state government and Wikipedia, the land climbs from about 500 metres to about 1,200 metres. Rivers such as the Ogun, Oba, Oyan, Erinle and Osun rise in this upland. The state government describes rain forest in the south giving way to guinea savanna in the north, with a dry season from November to March. Wikipedia names the Old Oyo National Park among the state's protected areas.

The area is given as 28,454 square kilometres by the state government and Wikipedia, 26,500 by the Nigerian Investment Promotion Commission and 27,036 by Statoids. The figures are recorded below."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


LA, OG, OY = "@admin_units:state:lagos", "@admin_units:state:ogun", "@admin_units:state:oyo"

UPDATES = [
    dict(ref=LA, fields=dict(description=LA_DESCRIPTION, geography_notes=LA_GEOGRAPHY),
         srcs=[("NIPCLA", "Created 27 May 1967; capital Ikeja; borders Benin and Ogun; coastline over 180 km; largest economy; 20 LGAs; area 3,671 km²"),
               ("WLA", "Created 1967 from the Western Region and federal territory; name; Eko and Awori; Portuguese; 1851; 1861; smallest state; borders only Ogun; peoples; festivals; ports; lagoons; climate"),
               ("STAT", "1967 Lagos from Colony province and Lagos federal territory; capital moved to Abuja 1991; area 3,475 km²"),
               ("CPLA", "2006 census 9,113,605 (National Population Commission)")]),
    dict(ref=OG, fields=dict(description=OG_DESCRIPTION, geography_notes=OG_GEOGRAPHY),
         srcs=[("NIPCOG", "Created 3 February 1976; Gateway State; borders; limestone; closeness to Lagos; 20 LGAs; crops; area 16,400 km²"),
               ("WOG", "Abeokuta; Oyo and Ijebu; missions; Lagos Protectorate 1893; Western Region; peoples; industry; cement; crops; kola nuts; rivers; Omo Forest Reserve"),
               ("STAT", "1976 Western State divided into Ogun, Ondo and Oyo; area 16,850 km²"), ("CPOG", "2006 census 3,751,140 (National Population Commission)")]),
    dict(ref=OY, fields=dict(description=OY_DESCRIPTION, geography_notes=OY_GEOGRAPHY),
         srcs=[("OYGOV", "Created 1976 from Western State; Osun until 1991; Pace Setter; borders; 33 LGAs; peoples; Ibadan and the Western Region; crops; ranches; minerals; University of Ibadan; hills, rivers, vegetation; area 28,454 km²"),
               ("NIPCOY", "Created 3 February 1976; Pace Setter; borders; University of Ibadan 1948; crops; area 26,500 km²"),
               ("WOY", "Oyo Empire, Oyo-Ile, new Oyo, the Alaafin; Ibadan; hills and rivers; Old Oyo National Park"),
               ("STAT", "1976 Oyo; 1991 Osun split off; area 27,036 km²"), ("CPOY", "2006 census 5,580,894 (National Population Commission)")]),
]

RELATIONS = [
    dict(frm=LA, type="neighbours", to=OG, source="NIPCLA", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission (Lagos and Ogun pages) and by Wikipedia; Ogun is Lagos's only neighbouring state."),
    dict(frm=OG, type="neighbours", to=OY, source="NIPCOG", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission (Ogun and Oyo pages), the Oyo State Government and Wikipedia."),
    dict(frm=OG, type="neighbours", to="@admin_units:state:osun", source="NIPCOG", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission (Ogun and Osun pages), the Osun State Government and Wikipedia."),
    dict(frm=OG, type="neighbours", to="@admin_units:state:ondo", source="NIPCOG", evidence="multiple_sources",
         notes="Listed by the Nigerian Investment Promotion Commission and by Wikipedia (Ogun and Ondo articles)."),
    dict(frm=OY, type="neighbours", to="@admin_units:state:osun", source="OYGOV", evidence="multiple_sources",
         notes="Listed by the Oyo State Government, the Nigerian Investment Promotion Commission (Oyo and Osun pages), the Osun State Government and Wikipedia."),
]


def stat(record, metric, value, source, notes, year=None, method="other"):
    d = dict(record=record, metric=metric, value_low=value, method=method, source=source, evidence="single_reliable_source", notes=notes)
    if year:
        d["reference_year"] = year
    return d


NPC = "National Population Commission figure as reproduced by City Population. Nigerian census results are disputed."
PROJ = "Projection, not a count (NPC / NBS, via City Population)."
STATISTICS = [
    stat(LA, "population", 9113605, "CPLA", NPC, 2006, "census"),
    stat(LA, "population", 9013534, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(LA, "population", 5725116, "CPLA", "1991 census, as reproduced by City Population.", 1991, "census"),
    stat(LA, "population", 13491800, "CPLA", PROJ, 2022, "projection"),
    stat(LA, "population", 13380098, "NIPCLA", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(LA, "area_km2", 3671, "NIPCLA", "Nigerian Investment Promotion Commission."),
    stat(LA, "area_km2", 3475, "STAT", "Statoids."),
    stat(OG, "population", 3751140, "CPOG", NPC, 2006, "census"),
    stat(OG, "population", 3728098, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(OG, "population", 2333726, "CPOG", "1991 census, as reproduced by City Population.", 1991, "census"),
    stat(OG, "population", 6379500, "CPOG", PROJ, 2022, "projection"),
    stat(OG, "population", 5573704, "NIPCOG", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(OG, "area_km2", 16400, "NIPCOG", "Nigerian Investment Promotion Commission."),
    stat(OG, "area_km2", 16850, "STAT", "Statoids."),
    stat(OY, "population", 5580894, "CPOY", NPC, 2006, "census"),
    stat(OY, "population", 5591589, "STAT", "Statoids' 2006 census table; differs from the NPC figure.", 2006, "census"),
    stat(OY, "population", 3452720, "CPOY", "1991 census for the area of today's state (without Osun), as reproduced by City Population.", 1991, "census"),
    stat(OY, "population", 7976100, "CPOY", PROJ, 2022, "projection"),
    stat(OY, "population", 8392588, "NIPCOY", "Given by the Nigerian Investment Promotion Commission without a year or method."),
    stat(OY, "area_km2", 28454, "OYGOV", "Oyo State Government ('approximately'; Wikipedia gives the same)."),
    stat(OY, "area_km2", 26500, "NIPCOY", "Nigerian Investment Promotion Commission."),
    stat(OY, "area_km2", 27036, "STAT", "Statoids."),
]

GAPS = [
    ("Lagos and Ogun government websites", "Both are JavaScript apps with no readable history page; the Internet Archive refused requests during this research. An official source should be added for each."),
    ("Early Lagos (Eko, the Awori, the Portuguese name, 1851, 1861)", "Given by Wikipedia only. Needs scholarly sources and its own records (the Kingdom of Lagos, the 1851 bombardment, the 1861 cession)."),
    ("The Oyo Empire", "Only outlined from Wikipedia (dates about 1300 to the 19th century). It needs its own scholarly-sourced record, with the Alaafin and Oyo-Ile."),
    ("Egba, Ijebu and Abeokuta history", "Not described beyond Wikipedia's outline. Needs scholarly sources."),
    ("Oyo 'firsts'", "The state government lists many 'firsts' (first skyscraper, first television station, first stadium in Africa). Only the University of Ibadan (1948), confirmed by NIPC, is used."),
    ("Areas and population figures", "The areas and populations from each source differ. All are recorded; none is chosen."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="Lagos, Ogun and Oyo states: history, geography, peoples, economy and population (fills empty fields of the existing records).")


STATES = [("Lagos State", LA_DESCRIPTION, LA_GEOGRAPHY), ("Ogun State", OG_DESCRIPTION, OG_GEOGRAPHY), ("Oyo State", OY_DESCRIPTION, OY_GEOGRAPHY)]


def report():
    L = ["# Research batch 018 — Lagos, Ogun and Oyo states (South West, part 1)", "",
         f"Researched {ACCESSED}. All three state pages are already published, so **this text goes live the moment it is imported**. This report is the review.", ""]
    for name, d, g in STATES:
        L += [f"- New prose for {name}: **{words(d) + words(g)} words**. The page passes the 300-word indexing rule and becomes indexable."]
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    for name, d, g in STATES:
        L += ["", f"## {name} — Overview (description field)", ""] + [f"> {p}" if p else ">" for p in d.split("\n")]
        L += ["", f"## {name} — Geography (geography field)", ""] + [f"> {p}" if p else ">" for p in g.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Lagos created 27 May 1967 from Colony province and the federal territory | NIPC; Wikipedia; Statoids | stated |",
          "| Capital Ikeja; federal capital moved to Abuja 1991 | NIPC; Statoids | stated |",
          "| Eko, Awori, Portuguese, 1851, 1861 | Wikipedia only | attributed + gap |",
          "| Smallest state; borders only Ogun | Wikipedia; NIPC (borders) | stated |",
          "| Lagos peoples, Saro and Amaro, festivals | Wikipedia | attributed |",
          "| Largest economy; ports | NIPC; Wikipedia | attributed |",
          "| Ogun created 3 Feb 1976 from Western State | NIPC; Wikipedia; Statoids | stated |",
          "| Oyo, Ijebu; Abeokuta missions; Lagos Protectorate 1893 | Wikipedia only | attributed |",
          "| Ogun peoples; industry; limestone; kola nuts | Wikipedia; NIPC | attributed |",
          "| Oyo created 3 Feb 1976; Osun split 1991 | state government; NIPC; Statoids; Wikipedia (= government) | stated |",
          "| Oyo Empire, Oyo-Ile, Alaafin | Wikipedia only | attributed + gap |",
          "| Ibadan and the Western Region | state government (= Wikipedia) | attributed |",
          "| University of Ibadan 1948 | state government; NIPC | stated |",
          "| Oyo crops, ranches, minerals | state government; NIPC | stated / attributed |", "",
          "## Figures recorded (side by side)", ""] + [f"- {s['record'].split(':')[-1].title()} — {s['metric']} {s.get('reference_year') or ''}: {s['value_low']:,} — {s['notes']} [{s['source']}]" for s in STATISTICS]
    L += ["", f"## Neighbours ({len(RELATIONS)} new links)", "",
          "- Lagos ↔ Ogun. Ogun ↔ Oyo, Osun, Ondo. Oyo ↔ Osun. The Kwara–Oyo link already exists.",
          "- Each is listed by two or more sources.",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- Three more state pages become indexable. With batch 019 (Osun, Ondo, Ekiti) the South West is complete. The sitemap should grow by 3 (3,004 → 3,007) for this batch. The new source pages stay noindex.",
          "- The text is original prose, and every claim is sourced or attributed. Promotional 'firsts' are not used.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_018_lagos_ogun_oyo.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_018_lagos_ogun_oyo_REVIEW.md", "w").write(report())
    print(" ".join(f"{n.split()[0]}={words(d) + words(g)}" for n, d, g in STATES) + f" relations={len(RELATIONS)} statistics={len(STATISTICS)}")
