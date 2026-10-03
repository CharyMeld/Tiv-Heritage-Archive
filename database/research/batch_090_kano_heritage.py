"""
Research batch 090 — Kano (Phase 3): culture and heritage. Researched 2026-10-02. Pattern: batches 060, 066, 072,
078 and 084.

NCMM declared national monuments (Kano: Nos. 32–34): Habe Mosque at Bebeji; Gidan Makama; Kano City Walls and Gates.
NCMM proposed national monuments, counted by hand in page order (Elephant House = 18 and Yakoko = 66 check out):
  No. 7 Gidan Dan Hausa; No. 8 Gidan Beminister ('Architectural'); No. 26 Kurmi Market; No. 27 'Associated sites of
  Kano City Walls — Dala Hills and Kofar Mata Dye Pit'; No. 28 Late General Murtala Ramat Mohammed Tomb ('Historic').
NCMM museums: National Museum Kano, 'Opposite Emir's Palace, Kano city' (Wikipedia: Gidan Makama Museum, managed by
the NCMM — recorded as one place, with 'Kano Museum' as another name).
Also: Gidan Rumfa, the Emir's palace (Wikipedia); the Kano Durbar (Wikipedia, 'Durbar festival').
LGA links only where a source names the place in an LGA: Habe Mosque → Bebeji ('Bebeji is also the location of Habe
mosque, declared a monument in 1964'); Dala Hill → Dala ('It contains Dalla Hill from which it got its name').
Everything in the old city is linked to the state only.
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_078_bauchi_heritage as H78
import batch_087_kano_languages as L87
import batch_087b_kano_peoples as P87

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMML": dict(H66.SOURCES["NCMML"], notes="Reused. Kano: No. 32 Habe Mosque at Bebeji; No. 33 Gidan Makama; No. 34 Kano City Walls and Gate."),
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Kano: No. 7 Gidan Dan Hausa; No. 8 Gidan Beminister; No. 26 Kurmi Market; No. 27 Associated sites of Kano City Walls — Dala Hills and Kofar Mata Dye Pit; No. 28 Late General Murtala Ramat Mohammed Tomb."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. National Museum Kano, Opposite Emir's Palace, Kano city, P.M.B. 2023."),
    "WMAK": WS("Gidan Makama Museum Kano", "Also Kano Museum; 15th-century building, a national monument; built for Muhammad Rumfa when Makama Kano; colonial offices after 1903; later divided into museum, primary school and residence; 11 galleries; managed by the NCMM."),
    "WRUM": WS("Gidan Rumfa", "Palace of the Emir of Kano (Gidan Sarki); built in the late 15th century; continuously the residence of Kano's traditional authority; about 13 ha with walls up to 4.6 m; Kofar Kudu gate built by Emir Abdullahi Maje Karofi in the 19th century."),
    "WKUR": WS("Kurmi Market", "Founded by Muhammad Rumfa in the 15th century in the Jakara district; Trans-Saharan trade; also a slave market; rebuilt 1904–1909 with 755 clay stalls."),
    "WDAL": WS("Dalla Hill", "Hill in Kano, 534 m; 101-step stairway; 7th-century iron-working community; Tsumburbura shrine of Barbushe; Kano originally known as Dala."),
    "WWAL": WS("Ancient Kano City Walls", "Hausa Ganuwa; foundations laid by Sarki Gijimasu (1095–1134), completed in the mid-14th century under Zamnagawa and expanded in the 16th; 9–15 m high, about 12 m thick at the base, 15 gates; Lugard (1903): 'never seen anything like it in Africa'."),
    "WMUR": WS("Murtala Muhammed", "Born in Kano, 8 November 1938; fourth head of state of Nigeria, 29 July 1975 until his assassination on 13 February 1976."),
    "WDUR": H78.SOURCES["WDUR"],
    "WKAN": L87.SOURCES["WKAN"],
    "W_bebeji": P87.SOURCES["W_bebeji"],
    "WDALA": WS("Dala, Nigeria", "Dala LGA, Kano State; headquarters Gwammaja; 'It contains Dalla Hill from which it got its name and was once the capital of the Sultanate of Kano'."),
}
TEXT = {
 "habe": """The Habe Mosque at Bebeji, in Bebeji LGA, is No. 32 on the National Commission for Museums and Monuments' list of declared national monuments, one of three in Kano State; Wikipedia says it was declared a monument in 1964 and calls it ancient. Its date of building and architecture are not described in a readable source.""",
 "makama": """Gidan Makama, also called the Kano Museum, is No. 33 on the National Commission for Museums and Monuments' list of declared national monuments and houses a museum run by the commission, which places its National Museum Kano opposite the Emir's Palace (NCMM; Wikipedia). According to Wikipedia the house was built in the 15th century for Muhammad Rumfa when he held the title Makama Kano; when he became king he moved to a new palace, and later Makamas lived in the building. After the British capture of Kano in 1903 it briefly housed colonial offices, and it was later divided into a museum, a primary school and a residence. The museum has eleven galleries, on the city walls and maps of Kano, the history of statehood, Kano in the 19th century, the Civil War, the economy, industry and music, and an open space where a Koroso dance and drama group performs (Wikipedia).""",
 "walls": """The Kano City Walls and Gates are No. 34 on the National Commission for Museums and Monuments' list of declared national monuments. In Hausa the walls are called Ganuwa. According to the Kano Chronicle, as Wikipedia reports it, their foundations were laid by Sarki Gijimasu (1095–1134), the third king of Kano; they were completed in the mid-14th century under Zamnagawa and enlarged in the 16th century. They stood 9 to 15 metres high, about 12 metres thick at the base, with 15 gates, and Lord Lugard wrote in 1903 that he had 'never seen anything like it in Africa' (Wikipedia). The commission's list of proposed monuments adds Dala Hill and the Kofar Mata dye pit as 'associated sites' of the walls.""",
 "rumfa": """Gidan Rumfa, also called Gidan Sarki, 'the Emir's house', is the palace of the Emir of Kano in the old city of Kano (Wikipedia). It was built in the late 15th century on what was then the outskirts of the town, under Muhammad Rumfa, and has been the residence of Kano's traditional authority ever since; the Fulani rulers kept it after the jihad of the early 19th century. It covers about 13 hectares within walls up to 4.6 metres high, and includes the Kofar Kudu or southern gate, built by Emir Abdullahi Maje Karofi in the second half of the 19th century, a mosque, royal courtrooms and schools; more than a thousand people live within it (Wikipedia).""",
 "kurmi": """Kurmi Market, in the old city of Kano, is No. 26 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Wikipedia says Muhammad Rumfa founded it in the 15th century, in the Jakara district, as a trading and warehousing centre for Kano's growing regional and trans-Saharan trade, which drew traders from the Western Sudan, Tripoli and Ghadames. It was also a slave market. The old market was demolished in 1904, and a new one of 755 clay stalls, with a mosque and a courthouse, opened in 1909 (Wikipedia).""",
 "dala": """Dala Hill, in Dala LGA, is part of No. 27 on the National Commission for Museums and Monuments' list of proposed national monuments, as one of the 'associated sites of Kano City Walls'. The hill is 534 metres high, with a stairway of 101 steps (Wikipedia). It was the site of an iron-working community in the seventh century and of the Tsumburbura shrine, which tradition links with Barbushe, until the spread of Islam; Kano itself was originally known as Dala, after the hill (Wikipedia). Dala LGA takes its name from it (Wikipedia).""",
 "kofarmata": """The Kofar Mata dye pit, in the old city of Kano, is part of No. 27 on the National Commission for Museums and Monuments' list of proposed national monuments, as one of the 'associated sites of Kano City Walls'. Wikipedia's article on Kurmi Market records cloth dyeing among the city's industries when the market was founded in the 15th century. The dye pit's age and present use are not described in a readable source.""",
 "danhausa": """Gidan Dan Hausa is No. 7 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Architectural' category; Wikipedia lists the Gidan Dan Hausa Museum among Kano's tourist attractions. Its history is not described in a readable source.""",
 "beminister": """Gidan Beminister, in Kano State, is No. 8 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Architectural' category. It is not described in a readable source.""",
 "murtala": """The tomb of General Murtala Ramat Mohammed, in Kano State, is No. 28 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Murtala Mohammed, born in Kano on 8 November 1938, was Nigeria's fourth head of state, from 29 July 1975 until his assassination on 13 February 1976 (Wikipedia). The tomb itself is not described in a readable source.""",
 "durbar": """The Kano Durbar is the Durbar festival as held in Kano. Wikipedia describes it as a four-day festival of horsemanship and street parades, held after the end of Ramadan and at Eid al-Adha, made up of the Hawan Sallah, the Hawan Daushe, the Hawan Nassarawa and the Hawan Doriya, and dates the Durbar to over two hundred years ago, when horses were first used in warfare in Kano. Its rites in the present emirate are not described in more detail in a readable source.""",
}
K = lambda l: f"@admin_units:lga:kano/{l}"
ST = "@admin_units:state:kano"
REC = [
    ("habe", "places", dict(place_type="sacred_site", name="Habe Mosque, Bebeji", slug="habe-mosque-bebeji", admin_unit_id=K("bebeji"), status="existing"), ["NCMML", "W_bebeji"], "well_documented"),
    ("makama", "places", dict(place_type="museum", name="Gidan Makama Museum", slug="gidan-makama-museum", admin_unit_id=ST, status="existing"), ["NCMML", "NCMMM", "WMAK"], "well_documented"),
    ("walls", "places", dict(place_type="monument", name="Kano City Walls and Gates", slug="kano-city-walls-and-gates", admin_unit_id=ST, status="existing"), ["NCMML", "NCMMP", "WWAL"], "well_documented"),
    ("rumfa", "places", dict(place_type="heritage_site", name="Gidan Rumfa", slug="gidan-rumfa", admin_unit_id=ST, status="existing"), ["WRUM"], "reported"),
    ("kurmi", "places", dict(place_type="historical_place", name="Kurmi Market", slug="kurmi-market", admin_unit_id=ST, status="existing"), ["NCMMP", "WKUR"], "well_documented"),
    ("dala", "places", dict(place_type="hill_or_mountain", name="Dala Hill", slug="dala-hill", admin_unit_id=K("dala"), status="existing"), ["NCMMP", "WDAL", "WDALA"], "well_documented"),
    ("kofarmata", "places", dict(place_type="heritage_site", name="Kofar Mata Dye Pit", slug="kofar-mata-dye-pit", admin_unit_id=ST, status="existing"), ["NCMMP", "WKUR"], "verified"),
    ("danhausa", "places", dict(place_type="museum", name="Gidan Dan Hausa", slug="gidan-dan-hausa", admin_unit_id=ST, status="existing"), ["NCMMP", "WKAN"], "verified"),
    ("beminister", "places", dict(place_type="historical_place", name="Gidan Beminister", slug="gidan-beminister", admin_unit_id=ST, status="existing"), ["NCMMP"], "verified"),
    ("murtala", "places", dict(place_type="monument", name="Tomb of General Murtala Mohammed", slug="tomb-of-general-murtala-mohammed", admin_unit_id=ST, status="existing"), ["NCMMP", "WMUR"], "verified"),
    ("durbar", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Kano Durbar", slug="kano-durbar",
                                        timing="After Ramadan (Eid al-Fitr) and at Eid al-Adha; four days", current_status="active", scope_level="ethnic_group"), ["WDUR"], "reported"),
]
RECORDS = []
for key, table, fields, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table=table, evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="rumfa", type="associated_with", to="@polities:kano-emirate", role="palace of the Emir of Kano", source="WRUM", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Gidan Rumfa)."),
    dict(frm="rumfa", type="associated_with", to="@polities:kingdom-of-kano", role="built under Muhammad Rumfa (late 15th century)", source="WRUM", evidence="single_reliable_source", level="reported", notes="Wikipedia (Gidan Rumfa; Gidan Makama)."),
    dict(frm="makama", type="associated_with", to="@polities:kingdom-of-kano", role="built for Muhammad Rumfa as Makama Kano (15th century)", source="WMAK", evidence="single_reliable_source", level="reported", notes="Wikipedia (Gidan Makama Museum Kano)."),
    dict(frm="kurmi", type="associated_with", to="@polities:kingdom-of-kano", role="founded by Muhammad Rumfa (15th century)", source="WKUR", evidence="single_reliable_source", level="reported", notes="Wikipedia (Kurmi Market)."),
    dict(frm="walls", type="associated_with", to="@polities:kingdom-of-kano", role="begun under Sarki Gijimasu (1095–1134)", source="WWAL", evidence="single_reliable_source", level="reported", notes="Wikipedia (Ancient Kano City Walls), after the Kano Chronicle."),
    dict(frm="dala", type="associated_with", to="@polities:kingdom-of-kano", role="the hill after which Kano was first called Dala", source="WDAL", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Dalla Hill; Kingdom of Kano)."),
    dict(frm="dala", type="associated_with", to="walls", role="associated site of the Kano City Walls (NCMM)", source="NCMMP", evidence="single_reliable_source", level="verified", notes="NCMM proposed list, No. 27."),
    dict(frm="kofarmata", type="associated_with", to="walls", role="associated site of the Kano City Walls (NCMM)", source="NCMMP", evidence="single_reliable_source", level="verified", notes="NCMM proposed list, No. 27."),
    dict(frm="durbar", type="celebrated_by", to="@ethnic_groups:hausa", source="WDUR", evidence="single_reliable_source", level="reported", notes="Wikipedia (Durbar festival): a core part of Hausa culture; the Kano durbar described."),
    dict(frm="@polities:kano-emirate", type="associated_with", to="durbar", role="emirate that holds the Durbar", source="WDUR", evidence="single_reliable_source", level="reported", notes="Wikipedia (Durbar festival)."),
]
NAMES = [
    dict(record="makama", name="Kano Museum", name_type="alternative", usage_notes="Wikipedia ('Gidan Makama Museum Kano or Kano Museum').", srcs=["WMAK"]),
    dict(record="walls", name="Ganuwa", name_type="endonym", usage_notes="Hausa name of the walls (Wikipedia).", srcs=["WWAL"]),
    dict(record="rumfa", name="Gidan Sarki", name_type="alternative", usage_notes="'Emir's house' (Wikipedia).", srcs=["WRUM"]),
    dict(record="dala", name="Dalla Hill", name_type="spelling_variant", usage_notes="Wikipedia's spelling.", srcs=["WDAL"]),
]
GAPS = [
    ("Kano: proposed monuments without description", "Gidan Dan Hausa, Gidan Beminister, the Kofar Mata dye pit and the Murtala Mohammed tomb are named by the NCMM without description; their history and condition need sources."),
    ("Kano: Habe Mosque", "Its date and builders are not described; Wikipedia gives 1964 as the year it was declared."),
    ("Kano: old-city LGA links", "The walls, Gidan Rumfa, Gidan Makama, Kurmi Market and the Kofar Mata dye pit are linked to the state only; the old city spans several LGAs, and no source read assigns them."),
    ("Kano: National Museum Kano", "The NCMM lists it 'opposite Emir's Palace'; Wikipedia says Gidan Makama is managed by the NCMM. Recorded as one place; an NCMM source naming Gidan Makama as the National Museum Kano would confirm it."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kano culture and heritage: 3 declared and 6 proposed NCMM monuments (incl. Dala Hill and Kofar Mata), Gidan Rumfa, and the Kano Durbar.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 090 — Kano: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **10 places:**",
         "  - **Kano's three declared national monuments** (NCMM Nos. 32–34):",
         "    - the Habe Mosque at Bebeji",
         "    - Gidan Makama, now the Kano Museum",
         "    - the Kano City Walls and Gates",
         "  - **six proposed monuments:**",
         "    - Gidan Dan Hausa (No. 7)",
         "    - Gidan Beminister (No. 8)",
         "    - Kurmi Market (No. 26)",
         "    - Dala Hill and the Kofar Mata dye pit (No. 27, both 'associated sites' of the walls)",
         "    - the tomb of General Murtala Mohammed (No. 28)",
         "  - **Gidan Rumfa**, the Emir's palace since the late 15th century",
         "- **1 cultural record:** the **Kano Durbar** (four days, after Ramadan and at Eid al-Adha).",
         "- **Links:**",
         "  - Gidan Rumfa, Gidan Makama, Kurmi Market, the walls and Dala Hill are tied to the Kingdom of Kano.",
         "  - The palace and the Durbar are tied to the Kano Emirate.",
         "  - Dala Hill and Kofar Mata are tied to the walls.",
         "- **LGA links** only where a source names the LGA (Habe Mosque → Bebeji; Dala Hill → Dala). The old city's sites are linked to the state only.",
         "- **Numbering:** the proposed numbers were counted by hand in page order and checked against known entries (Elephant House 18, Yakoko 66).", ""]
    for key, table, fields, srcs, lvl in REC:
        L += [f"## {fields['name']} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_090_kano_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_090_kano_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
