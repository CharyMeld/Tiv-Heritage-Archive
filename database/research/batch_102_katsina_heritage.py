"""
Research batch 102 — Katsina (Phase 3): culture and heritage. Researched 2026-10-02. Pattern: batches 090 and 096.

NCMM declared national monuments (Katsina: Nos. 35–37): No. 35 Gobirau minaret; No. 36 the tumuli and baobab tree
known as Durbi-Takusheyi; No. 37 Old Katsina Training College.
NCMM proposed (hand count, checked against Elephant House 18, Yakoko 66, Goya 86, Keana 90, Dagona 99): No. 6 Gidan
Yarima ('Architectural'); No. 25 Kusugu Well, Daura ('Historic'); No. 81 Dutse Bamle ('Natural').
NCMM museums: National Museum Katsina, Kofa Uku, along Muhammadu Dikko Road.
Wikipedia: Gobarau Minaret; Durbi Takusheyi (declared 23 April 1959; Palmer's excavations 1907); Barewa College
(founded 1921 as Katsina College, opened 1922 in Katsina); Daura Emirate (Kusugu well); Faruk Umar Faruk (the Id-el-
Kabir durbar held in Daura and Katsina).
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_101_katsina_institutions as I101

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMML": dict(H66.SOURCES["NCMML"], notes="Reused. Katsina: No. 35 Gobirau minaret; No. 36 the tumuli and baobab tree known as Durbi-Takusheyi; No. 37 Old Katsina Training College."),
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Katsina: No. 6 Gidan Yarima; No. 25 Kusugu Well, Daura; No. 81 Dutse Bamle."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. National Museum Katsina, Kofa Uku, along Mohamadu Dikko Road, P.M.B. 409, Katsina."),
    "WGOB": WS("Gobarau Minaret", "15 m minaret in the centre of Katsina; part of a mosque built under Muhammadu Korau (1445–95), attributed to al-Maghili; a centre of Islamic higher learning by the 16th century; central mosque until the early 19th century; renovated under Muhammadu Kabir Usman (1981–2008); Hausa 'Hasumiyya'."),
    "WDUR": WS("Durbi Takusheyi", "'tombs of the chief priest'; burial site about 32 km east of Katsina; burials of early rulers from the 13th/14th to the 15th/16th century; ancestor shrines at baobabs; the Durbawa; Palmer's excavations 1907; declared a national monument on 23 April 1959."),
    "WBAR": WS("Barewa College", "Founded in 1921 by Governor-General Hugh Clifford as Katsina College; built in Katsina by Emir Muhammadu Dikko and opened in 1922."),
    "WDE": I101.SOURCES["WDE"], "WFUF": I101.SOURCES["WFUF"], "WKK": I101.SOURCES["WKK"],
}
TEXT = {
 "gobarau": """The Gobarau (Gobirau) Minaret, in the centre of Katsina, is No. 35 on the National Commission for Museums and Monuments' list of declared national monuments. The 15-metre tower, in Hausa Hasumiyya, is what remains of a mosque built in the 15th century under Muhammadu Korau, the first Muslim ruler of Katsina, and attributed to the scholar al-Maghili; by the early 16th century it was a famed centre of Islamic higher learning (Wikipedia). It remained Katsina's central mosque until the early 19th century and was renovated under Emir Muhammadu Kabir Usman (1981–2008). It has become a symbol of the city and a tourist attraction (Wikipedia).""",
 "durbi": """The tumuli and baobab tree known as Durbi-Takusheyi are No. 36 on the National Commission for Museums and Monuments' list of declared national monuments. The name means 'tombs of the chief priest'; the site, about 32 km east of Katsina, holds the burials of early Katsina rulers from the 13th or 14th to the 15th or 16th century, with grave goods that show trade links reaching the Islamic Near East (Wikipedia). It was the cult centre of the Durbawa, whose chief priest's title, Durbi, is still a senior title in the Katsina Emirate. Palmer first excavated the mounds in 1907, and the Antiquities Department declared the site a national monument on 23 April 1959 (Wikipedia).""",
 "college": """The Old Katsina Training College is No. 37 on the National Commission for Museums and Monuments' list of declared national monuments. Katsina College was founded in 1921 by the Governor-General, Sir Hugh Clifford, and built in Katsina by Emir Muhammadu Dikko, opening in 1922; Clifford chose Katsina because of its old repute as a seat of learning (Wikipedia, 'Barewa College'). The college later became Barewa College. The present use and condition of the old buildings are not described in a readable source.""",
 "yarima": """Gidan Yarima, in Katsina State, is No. 6 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Architectural' category. It is not described in a readable source.""",
 "kusugu": """The Kusugu well, at Daura, is No. 25 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. According to tradition, Bayajidda killed the snake called Sarki that kept the people of Daura from its water, and Queen Daurama married him in gratitude; their descendants are said to have founded the Hausa states (Wikipedia). The well is protected by a wooden shelter and has become a tourist attraction (Wikipedia).""",
 "bamle": """Dutse Bamle, in Katsina State, is No. 81 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Natural' category. It is not described in a readable source.""",
 "museum": """The National Museum Katsina is one of the national museums of the National Commission for Museums and Monuments, at Kofa Uku on Muhammadu Dikko Road, Katsina. Its history and collections are not described in a readable source.""",
 "durbar": """The Durbar of the Katsina and Daura emirates is held during Id-el-Kabir, when, according to Wikipedia, durbars are normally staged at the same time in the Daura and Katsina council areas; the Daura durbar was cancelled in December 2008 while the Emir was on pilgrimage. Its course and rites are not described in more detail in a readable source.""",
}
K = lambda l: f"@admin_units:lga:katsina/{l}"
ST = "@admin_units:state:katsina"
REC = [
    ("gobarau", "places", dict(place_type="sacred_site", name="Gobarau Minaret", slug="gobarau-minaret", admin_unit_id=K("katsina"), status="existing"), ["NCMML", "WGOB"], "well_documented"),
    ("durbi", "places", dict(place_type="archaeological_site", name="Durbi-Takusheyi", slug="durbi-takusheyi", admin_unit_id=ST, status="historical"), ["NCMML", "WDUR", "WKK"], "well_documented"),
    ("college", "places", dict(place_type="historical_place", name="Old Katsina Training College", slug="old-katsina-training-college", admin_unit_id=K("katsina"), status="existing"), ["NCMML", "WBAR"], "well_documented"),
    ("yarima", "places", dict(place_type="historical_place", name="Gidan Yarima", slug="gidan-yarima", admin_unit_id=ST, status="existing"), ["NCMMP"], "verified"),
    ("kusugu", "places", dict(place_type="heritage_site", name="Kusugu Well", slug="kusugu-well", admin_unit_id=K("daura"), status="existing"), ["NCMMP", "WDE"], "well_documented"),
    ("bamle", "places", dict(place_type="natural_feature", name="Dutse Bamle", slug="dutse-bamle", admin_unit_id=ST, status="existing"), ["NCMMP"], "verified"),
    ("museum", "places", dict(place_type="museum", name="National Museum Katsina", slug="national-museum-katsina", admin_unit_id=K("katsina"), status="existing"), ["NCMMM"], "verified"),
    ("durbar", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Katsina and Daura Durbar", slug="katsina-and-daura-durbar",
                                        timing="Id-el-Kabir (Eid al-Adha)", current_status="active", scope_level="ethnic_group"), ["WFUF"], "reported"),
]
RECORDS = []
for key, table, fields, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table=table, evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="durbi", type="located_in", to=K("mani"), source="WKK", evidence="single_reliable_source", level="reported",
         notes="Wikipedia (Kingdom of Katsina): Kumayo's seat, Durbi ta Kusheyi, was near Mani, about 18 miles south-east of Katsina; Wikipedia (Durbi Takusheyi): about 32 km east of Katsina."),
    dict(frm="durbi", type="associated_with", to="@polities:kingdom-of-katsina", role="burial site of the early rulers of Katsina", source="WDUR", evidence="multiple_sources", level="well_documented", notes="Wikipedia."),
    dict(frm="gobarau", type="associated_with", to="@polities:kingdom-of-katsina", role="mosque built under Muhammadu Korau (1445–95)", source="WGOB", evidence="single_reliable_source", level="well_documented", notes="Wikipedia."),
    dict(frm="kusugu", type="associated_with", to="@polities:daura-emirate", role="the well of the Bayajidda legend", source="WDE", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Daura Emirate)."),
    dict(frm="kusugu", type="associated_with", to="@ethnic_groups:hausa", role="site of the Bayajidda founding legend of the Hausa states", source="WDE", evidence="single_reliable_source", level="reported", notes="Wikipedia (Daura Emirate)."),
    dict(frm="durbar", type="celebrated_by", to="@ethnic_groups:hausa", source="WFUF", evidence="single_reliable_source", level="reported", notes="Wikipedia (Faruk Umar Faruk)."),
    dict(frm="@polities:katsina-emirate", type="associated_with", to="durbar", role="emirate that holds the Durbar", source="WFUF", evidence="single_reliable_source", level="reported", notes="Wikipedia (Faruk Umar Faruk)."),
    dict(frm="@polities:daura-emirate", type="associated_with", to="durbar", role="emirate that holds the Durbar", source="WFUF", evidence="single_reliable_source", level="reported", notes="Wikipedia (Faruk Umar Faruk)."),
]
NAMES = [
    dict(record="gobarau", name="Gobirau Minaret", name_type="spelling_variant", usage_notes="NCMM spelling ('Gobirau minaret'); Wikipedia also gives Goborau.", srcs=["NCMML"]),
    dict(record="gobarau", name="Hasumiyya", name_type="endonym", usage_notes="Hausa word for the minaret (Wikipedia).", srcs=["WGOB"]),
    dict(record="durbi", name="Durbi ta Kusheyi", name_type="spelling_variant", usage_notes="'tombs of the chief priest' (Wikipedia).", srcs=["WDUR"]),
    dict(record="college", name="Katsina College", name_type="historical", usage_notes="Name at its founding in 1921 (Wikipedia, Barewa College).", srcs=["WBAR"]),
]
GAPS = [
    ("Katsina: Gidan Yarima and Dutse Bamle", "Named by the NCMM without description; their location and history need a source."),
    ("Katsina: Durbi-Takusheyi's LGA", "Linked to Mani as reported (Wikipedia places the seat near Mani); an official placement is needed."),
    ("Katsina: Old Katsina Training College", "Its present use and condition are not described in a source read."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Katsina culture and heritage: 3 declared NCMM monuments (Gobarau, Durbi-Takusheyi, Old Katsina Training College), 3 proposed (Gidan Yarima, Kusugu Well, Dutse Bamle), National Museum Katsina, and the Katsina and Daura Durbar.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 102 — Katsina: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **7 places:**",
         "  - **Katsina's three declared national monuments** (NCMM Nos. 35–37):",
         "    - the **Gobarau Minaret** (15th century, under Korau)",
         "    - **Durbi-Takusheyi**, the tombs of the early Katsina rulers (declared 1959)",
         "    - the **Old Katsina Training College** (1921–22; later Barewa College)",
         "  - **three proposed monuments:**",
         "    - **Gidan Yarima** (No. 6)",
         "    - the **Kusugu Well** at Daura (No. 25), the well of the Bayajidda legend",
         "    - **Dutse Bamle** (No. 81)",
         "  - the **National Museum Katsina**",
         "- **1 cultural record:** the **Katsina and Daura Durbar** at Id-el-Kabir, *reported*.",
         "- **Links:**",
         "  - Durbi-Takusheyi and Gobarau are tied to the Kingdom of Katsina, and the Kusugu Well to the Daura Emirate and the Hausa.",
         "  - The Durbar is tied to both emirates.",
         "- **Numbering:** the proposed numbers were hand-counted and checked against five known entries.", ""]
    for key, table, fields, srcs, lvl in REC:
        L += [f"## {fields['name']} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_102_katsina_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_102_katsina_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
