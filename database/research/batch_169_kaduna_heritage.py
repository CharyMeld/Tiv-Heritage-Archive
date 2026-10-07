"""
Research batch 169 — Kaduna (Phase 3): culture and heritage. Researched 2026-10-07. Pattern: batches 157, 163.

NCMM declared list: 28 'Zaria City Walls, Zaria'; 29 'Kufena Hills, Near Zaria'; 30 'Steel foot Bridge, Built by Lord
Lugard'; 31 'Habe Mosque at Maigana'.
NCMM proposed list (hand count, calibrated: Kusugu 25, Yakoko 66, Namoda 76): Architectural 9 'Arewa House'; 10
'St. Bartholomew Church, Wusasa Zaria'; Historic 29 'Lugard Hall'; 30 'Nok Archaeological Site, Nok'.
NCMM museums: National Museum Kaduna (No. 33, Ali Akilu Road, Kaduna).
Wikipedia: 'Lugard Footbridge' (built 1904 at Zungeru, moved to Kaduna and rebuilt 1920; declared a monument 16 February
1956; in General Hassan Katsina Park; 14.2 m long); 'Arewa House' (residence and office of the Premier of Northern
Nigeria; a research centre of Ahmadu Bello University from 1970, No. 1 Rabah Road); 'Kaduna State House of Assembly'
(Lugard Hall: Lugard Memorial Council Chamber; legislature of colonial Northern Nigeria 1914–1954 and of the Northern
Region 1954–1967); 'Kaduna State' (Zaria's walls built in the reign of Queen Amina, 14–16 km, eight gates; St
Bartholomew's Church built by the CMS in 1929 in Hausa traditional style); 'Nok culture' (named after the Ham village of
Nok, where terracottas were first found in 1928; c. 1500 BCE – 1 BCE); 'Afan festival'; 'Ayet Atyap annual cultural
festival'; 'Zaria' (Sallah durbar).
Placement: Zaria walls → Zaria; Kufena and Wusasa → Zaria (INEC: ward Kufena 13-…, polling unit Wusasa Kuregu); Maigana
→ Soba (INEC ward Maigana; Soba's headquarters, Statoids); Nok → Jaba (INEC ward Nok); the Kaduna city sites → state only
(Kaduna city spans Kaduna North and Kaduna South; the LGA of each site is not given in a source read).
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_166_kaduna_kainji_languages as K166

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMML": dict(H66.SOURCES["NCMML"], notes="Reused. Kaduna: 28 Zaria City Walls, Zaria; 29 Kufena Hills, Near Zaria; 30 Steel foot Bridge, Built by Lord Lugard; 31 Habe Mosque at Maigana."),
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Kaduna: Architectural 9 Arewa House; 10 St. Bartholomew Church, Wusasa Zaria; Historic 29 Lugard Hall; 30 Nok Archaeological Site, Nok."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. Kaduna: National Museum Kaduna, No. 33, Alik Akilu Road, P.M.B. 2127, Kaduna."),
    "INEC": K166.SOURCES["INEC"],
    "WLFB": WS("Lugard Footbridge", "Built by Lugard in 1904 at Zungeru; moved to Gamji Gate, Kaduna, and reconstructed in 1920; declared a historic monument on 16 February 1956; now in General Hassan Katsina Park; iron, 14.2 m by 1.75 m, 42 wooden steps."),
    "WARH": WS("Arewa House", "Centre for research and historical documentation of Ahmadu Bello University at No. 1 Rabah Road, Kaduna, in the former residence and office of the Premier of Northern Nigeria, Sir Ahmadu Bello; established in 1970 under Professor Abdullahi Smith; transferred to the university in 1975."),
    "WKSHA": WS("Kaduna State House of Assembly", "Based at Lugard Hall, which houses the Lugard Memorial Council Chamber (Northern Nigeria Council of Chiefs); formerly the legislative house of Northern Nigeria (1954–1967) and of the British colonial government (1914–1954)."),
    "WKDS": WS("Kaduna State", "Zaria's walls 'constructed during the reigns of Queen Amina of Zazzau … between 14 and 16 km long, and are closed by eight gates'; the Emir's Palace of Zaria; 'St. Bartholomew's Church Zaria, built by the Church Missionary Society in 1929 … based on Hausa traditional architecture'."),
    "WZAR": WS("Zaria", "The old city, Birnin Zazzau, was surrounded by walls, mostly removed; the Emir's palace is in the old city; the Sallah durbar is held twice a year in phases: Hawan Sallah, Hawan Bariki and Hawan Daushe."),
    "WNOK": WS("Nok culture", "Named after the Ham village of Nok in southern Kaduna State, where terracotta sculptures were first discovered in 1928 (by Colonel Dent Young, in a tin mine); more found near Nok in 1943; the culture may date from about 1500 BCE to 1 BCE."),
    "WAFAN": WS("Afan festival", "Celebrated every 1 January by the Agworok (Kagoro) at the palace of the Chief of Kagoro in Kaura LGA; said to have been observed for over 400 years; afan means 'hill' in Gworok; formerly held in April; the chief priest climbs the hills to pray."),
    "WAYET": WS("Ayet Atyap annual cultural festival", "Festival of the Atyap, traditionally between mid-March and mid-April to usher in the farming season; now held in December at the Agwatyap's palace square in Atak Njei, Zangon Kataf LGA."),
    "WWUS": WS("Wusasa", "A town just outside Zaria."),
    "WSOB": WS("Soba, Nigeria", "LGA; 'Its headquarters is in the town of Maigana'."),
}
D = "is No. {n} on the National Commission for Museums and Monuments' list of declared national monuments"
P = "is No. {n} on the National Commission for Museums and Monuments' list of proposed national monuments, in the '{c}' category"
TEXT = {
 "zaria-walls": f"""The city walls of Zaria {D.format(n=28)}. According to Wikipedia, the walls were built in the reign of Queen Amina of Zazzau to protect the city; they were between 14 and 16 km long and closed by eight gates, and most of them have since been removed. The Emir's palace stands in the old walled city, Birnin Zazzau.""",
 "kufena": f"""The Kufena Hills, near Zaria, {D.format(n=29)}. Kufena is a ward of Zaria LGA in INEC's directory. The hills' history is not described in a source read.""",
 "lugard-footbridge": f"""The Lugard Footbridge in Kaduna {D.format(n=30)}, listed as the 'steel foot bridge built by Lord Lugard'. According to Wikipedia, Frederick Lugard built it in 1904 at Zungeru, then the capital of the Northern Protectorate; it was moved to Gamji Gate in Kaduna and rebuilt in 1920, and it was declared a historic monument on 16 February 1956. It stands in the General Hassan Katsina Park: an iron bridge 14.2 m long and 1.75 m wide, with 42 wooden steps.""",
 "maigana-mosque": f"""The Habe mosque at Maigana, in Soba LGA, {D.format(n=31)}. Maigana is the headquarters of Soba LGA (Wikipedia; Statoids writes Maigama) and a ward in INEC's directory. The mosque's date and history are not described in a source read.""",
 "arewa-house": f"""Arewa House in Kaduna {P.format(n=9, c='Architectural')}. According to Wikipedia, it was the residence and office of Sir Ahmadu Bello, Premier of Northern Nigeria, and since 1970 it has been a centre for historical documentation and research, at No. 1 Rabah Road, under the first director, Professor Abdullahi Smith; control passed to Ahmadu Bello University in 1975.""",
 "wusasa-church": f"""St Bartholomew's Church at Wusasa, outside Zaria, {P.format(n=10, c='Architectural')}. According to Wikipedia, it was built by the Church Missionary Society in 1929 in the Hausa traditional style of architecture. Wusasa is in Kufena ward of Zaria LGA in INEC's directory.""",
 "lugard-hall": f"""Lugard Hall in Kaduna {P.format(n=29, c='Historic')}. According to Wikipedia, it houses the Lugard Memorial Council Chamber (of the Northern Nigeria Council of Chiefs) and the Kaduna State House of Assembly, and it was the legislative house of the British colonial government of the north from 1914 to 1954 and of the Northern Region from 1954 to 1967.""",
 "nok-site": f"""The Nok archaeological site, at Nok in Jaba LGA, {P.format(n=30, c='Historic')}. Nok, a Ham village, gave its name to the Nok culture: terracotta sculptures were first found there in 1928, in a tin mine, and more near the village in 1943 (Wikipedia). Wikipedia dates the culture from about 1500 BCE to 1 BCE. Nok is a ward of Jaba LGA in INEC's directory.""",
 "museum-kaduna": """The National Museum Kaduna is one of the national museums of the National Commission for Museums and Monuments, at No. 33 Ali Akilu Road, Kaduna. Its history and collections are not described in a source read.""",
 "afan": """The Afan festival is held every 1 January by the Agworok (Kagoro) at the palace of the Chief of Kagoro in Kaura LGA (Wikipedia). Afan means 'hill' in Gworok, after the hills of Kagoro, where the Agworok once lived. Wikipedia says the festival is believed to have been observed for over 400 years; it was formerly held in April, at the end of the harvest and the start of the hunting season, and it opens with the chief priest climbing the hills to pray.""",
 "ayet-atyap": """Ayet Atyap is the annual cultural festival of the Atyap (Wikipedia). It was traditionally held between mid-March and mid-April to open the farming season, organised by initiated men of the Aku clan; it is now held in December at the palace square of the Agwatyap at Atak Njei, in Zangon Kataf LGA, and is attended by political and traditional leaders.""",
 "zaria-durbar": """The Zaria Sallah durbar is held twice a year, at the end of Ramadan and at Eid al-Adha (Wikipedia). It is celebrated in phases: on the first day, Hawan Sallah, after the Eid prayers the Emir of Zazzau rides from the prayer ground to his palace with his district heads and royal guards; on the following days, Hawan Bariki and Hawan Daushe, he makes further tours of the city.""",
}
ST = "@admin_units:state:kaduna"
LG = lambda l: f"@admin_units:lga:kaduna/{l}"
REC = [
    ("zaria-walls", "places", dict(place_type="monument", name="Zaria City Walls", slug="zaria-city-walls", admin_unit_id=LG("zaria"), status="existing"), ["NCMML", "WKDS", "WZAR"], "verified"),
    ("kufena", "places", dict(place_type="hill_or_mountain", name="Kufena Hills", slug="kufena-hills", admin_unit_id=LG("zaria"), status="existing"), ["NCMML", "INEC"], "verified"),
    ("lugard-footbridge", "places", dict(place_type="monument", name="Lugard Footbridge, Kaduna", slug="lugard-footbridge-kaduna", admin_unit_id=ST, status="relocated"), ["NCMML", "WLFB"], "verified"),
    ("maigana-mosque", "places", dict(place_type="sacred_site", name="Habe Mosque, Maigana", slug="habe-mosque-maigana", admin_unit_id=LG("soba"), status="existing"), ["NCMML", "INEC", "WSOB"], "verified"),
    ("arewa-house", "places", dict(place_type="historical_place", name="Arewa House, Kaduna", slug="arewa-house-kaduna", admin_unit_id=ST, status="existing"), ["NCMMP", "WARH"], "verified"),
    ("wusasa-church", "places", dict(place_type="sacred_site", name="St Bartholomew's Church, Wusasa", slug="st-bartholomews-church-wusasa", admin_unit_id=LG("zaria"), status="existing"), ["NCMMP", "WKDS", "INEC"], "verified"),
    ("lugard-hall", "places", dict(place_type="historical_place", name="Lugard Hall, Kaduna", slug="lugard-hall-kaduna", admin_unit_id=ST, status="existing"), ["NCMMP", "WKSHA"], "verified"),
    ("nok-site", "places", dict(place_type="archaeological_site", name="Nok Archaeological Site", slug="nok-archaeological-site", admin_unit_id=LG("jaba"), status="existing"), ["NCMMP", "WNOK", "INEC"], "verified"),
    ("museum-kaduna", "places", dict(place_type="museum", name="National Museum Kaduna", slug="national-museum-kaduna", admin_unit_id=ST, status="existing"), ["NCMMM"], "verified"),
    ("afan", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Afan Festival", slug="afan-festival",
                                      timing="1 January each year (formerly April)", current_status="unknown", scope_level="community"), ["WAFAN"], "reported"),
    ("ayet-atyap", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Ayet Atyap Festival", slug="ayet-atyap-festival",
                                            timing="December (formerly mid-March to mid-April)", current_status="unknown", scope_level="community"), ["WAYET"], "reported"),
    ("zaria-durbar", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Zaria Durbar", slug="zaria-durbar",
                                              timing="Twice a year, at Eid al-Fitr and Eid al-Adha", current_status="unknown", scope_level="community"), ["WZAR"], "reported"),
]
RECORDS = [dict(key=k, table=t, evidence="multiple_sources" if len(s) > 1 else "single_reliable_source", level=lvl,
                fields=dict(f, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k]), srcs=[(x, f["name"]) for x in s]) for k, t, f, s, lvl in REC]
RELATIONS = [
    dict(frm="zaria-walls", type="associated_with", to="@polities:zazzau-emirate", role="walls of the old city of Zazzau, seat of the emirate", source="WKDS", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Kaduna State; Zaria)."),
    dict(frm="nok-site", type="associated_with", to="@historical_periods:nok-culture", role="the village that gave its name to the Nok culture", source="WNOK", evidence="multiple_sources", level="well_documented", notes="NCMM; Wikipedia (Nok culture)."),
    dict(frm="nok-site", type="associated_with", to="@ethnic_groups:ham", role="Nok is a Ham village", source="WNOK", evidence="single_reliable_source", level="reported", notes="Wikipedia (Nok culture)."),
    dict(frm="afan", type="associated_with", to="@polities:agworok-chiefdom", role="held at the palace of the Chief of Kagoro", source="WAFAN", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Afan festival)."),
    dict(frm="afan", type="celebrated_in", to=LG("kaura"), source="WAFAN", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Afan festival)."),
    dict(frm="afan", type="celebrated_by", to="@ethnic_groups:agworok", source="WAFAN", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Afan festival)."),
    dict(frm="ayet-atyap", type="associated_with", to="@polities:atyap-chiefdom", role="held at the Agwatyap's palace square", source="WAYET", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Ayet Atyap annual cultural festival)."),
    dict(frm="ayet-atyap", type="celebrated_in", to=LG("zangon-kataf"), source="WAYET", evidence="single_reliable_source", level="well_documented", notes="Wikipedia: Atak Njei, Zangon Kataf LGA."),
    dict(frm="ayet-atyap", type="celebrated_by", to="@ethnic_groups:atyap", source="WAYET", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Ayet Atyap annual cultural festival)."),
    dict(frm="zaria-durbar", type="associated_with", to="@polities:zazzau-emirate", role="the Emir of Zazzau's procession", source="WZAR", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Zaria)."),
    dict(frm="zaria-durbar", type="celebrated_in", to=LG("zaria"), source="WZAR", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Zaria)."),
    dict(frm="zaria-durbar", type="celebrated_by", to="@ethnic_groups:hausa", source="WZAR", evidence="single_reliable_source", level="reported", notes="Wikipedia (Zaria): a durbar of the Hausa city of Zaria."),
]
NAMES = [
    dict(record="lugard-footbridge", name="Steel Foot Bridge built by Lord Lugard", name_type="official", usage_notes="The NCMM's name on the declared list (No. 30).", srcs=["NCMML"]),
    dict(record="arewa-house", name="Gidan Arewa", name_type="alternative", usage_notes="Hausa name (Wikipedia).", srcs=["WARH"]),
    dict(record="ayet-atyap", name="Swong A̠yet", name_type="endonym", usage_notes="Tyap name (Wikipedia; also Song A̠yet).", srcs=["WAYET"]),
]
GAPS = [
    ("Kaduna: undescribed monuments", "The Kufena Hills, the Habe mosque at Maigana and the National Museum Kaduna are not described in a source read."),
    ("Kaduna: LGAs of the Kaduna city sites", "Lugard Footbridge, Arewa House, Lugard Hall and the National Museum are in Kaduna city, which spans Kaduna North and Kaduna South; their LGAs are not given in a source read, so they are placed at state level."),
    ("Kaduna: festivals' recent editions", "The Afan, Ayet Atyap and Zaria durbar records are from Wikipedia only; dated reports of recent editions were not read."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kaduna culture and heritage: 4 declared and 4 proposed NCMM monuments (Zaria walls, Kufena Hills, Lugard Footbridge, Maigana mosque, Arewa House, Wusasa church, Lugard Hall, Nok), the National Museum Kaduna, and the Afan, Ayet Atyap and Zaria durbar festivals.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 169 — Kaduna: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **9 places:**",
         "  - **4 declared national monuments:** the **Zaria city walls**; the **Kufena Hills**; the **Lugard Footbridge** (built 1904 at Zungeru, moved to Kaduna 1920, declared 1956); the **Habe mosque at Maigana** (Soba)",
         "  - **4 proposed:** **Arewa House** (Ahmadu Bello's residence, now a research centre); **St Bartholomew's Church, Wusasa** (CMS, 1929, Hausa style); **Lugard Hall** (the old Northern legislature); the **Nok archaeological site** (Jaba), linked to the archive's Nok Culture period",
         "  - the **National Museum Kaduna**",
         "- **3 festivals** (*reported*, Wikipedia only): **Afan** (Agworok, 1 January, Kaura), **Ayet Atyap** (December, Atak Njei) and the **Zaria durbar**.",
         "- **NCMM numbers:** declared 28–31 are printed on the list; the proposed numbers 9, 10, 29 and 30 were hand-counted and checked against known points (Kusugu 25, Yakoko 66, Namoda 76).", ""]
    for k, t, f, s, lvl in REC:
        L += [f"## {f['name']} ({words(TEXT[k])} words; {lvl})", "", f"> {TEXT[k]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_169_kaduna_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_169_kaduna_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} sources={len(SOURCES)}")
