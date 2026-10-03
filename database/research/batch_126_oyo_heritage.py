"""
Research batch 126 — Oyo (Phase 3): culture and heritage. Researched 2026-10-02. Pattern: batches 114, 120.

NCMM declared list: no entry for Oyo State. NCMM proposed list (calibrated count; Kusugu 25, Dutse Bamle 81):
Architectural 11 'Mapo Hall, Ibadan'; Historic 36 'Bishop Ajayi Crowther Home, Oke Ogun Iseyin'. NCMM museums: National
Museum Ibadan (Alesinloye); National Museum Oko-Surulere (Oko); National Museum Ogbomosho (3 Museum Street, off Sunsun
Road); National Museum Oyo (1 Palace Road, Alafin, Oyo).
Wikipedia: 'Mapo Hall' (Ibadan city hall on Mapo Hill, built 1925–29, engineer Robert Jones; renovated 2006–07); 'Samuel
Ajayi Crowther' (born at Osogun, near Ado Awaye, about 1809; captured by slave raiders in 1821; first African Anglican
bishop, 1864); 'Old Oyo National Park' (2,512 km² across northern Oyo and southern Kwara; contains the ruins of Oyo-Ile;
from forest reserves of 1936 and 1941; head office in Oyo); 'Old Oyo' (Oyo-Ile, abandoned since 1835); 'Layipo' (Bower's
Tower, Oke-Are, Ibadan, 1936); 'Ibadan'; 'Cocoa House' (Dugbe, Ibadan; completed 1964, commissioned 1965; 105 m; the
first skyscraper in West Africa).
Placement: an LGA only where a source puts the place in a named LGA headquarters or town: Mapo Hall (Ibadan South-East,
headquarters Mapo), Cocoa House (Ibadan North-West, headquarters Dugbe/Onireke) and the Crowther home (Iseyin); the
rest, including the four museums, at state level.
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_123_oyo_languages_peoples as P123
import batch_125_oyo_institutions as I125

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Oyo: Architectural 11 Mapo Hall, Ibadan; Historic 36 Bishop Ajayi Crowther Home, Oke Ogun Iseyin. No Oyo entry on the declared list."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. Oyo: National Museum Ibadan (Alesinloye Area); National Museum Oko-Surulere (Oko, Ogbomosho); National Museum Ogbomosho (3 Museum Street, off Sunsun Road); National Museum Oyo (1 Palace Road, Alafin, Oyo)."),
    "WMAP": WS("Mapo Hall", "Colonial-style Ibadan City Hall on Mapo Hill; built 1925–29 by engineer Robert Jones at a cost of £24,000; opened during the time of Alaafin Ladugbolu and Baale Oyewole; renovated 2006–07; host to major political events."),
    "WCRO": WS("Samuel Ajayi Crowther", "Born at Osogun (around Ado Awaye, Oyo State) about 1809; captured by Fulani slave raiders in 1821; freed, educated, ordained; the first African Anglican bishop (1864); died 31 December 1891."),
    "WOOP": WS("Old Oyo National Park", "2,512 km² across northern Oyo State and southern Kwara State; contains the ruins of Oyo-Ile; from the Upper Ogun (1936) and Oyo-Ile (1941) forest reserves; game reserves from 1952; head office in Oyo; threatened by poaching, logging and encroachment."),
    "WOLD": I125.SOURCES["WOLD"], "GZOLU": I125.SOURCES["GZOLU"],
    "WLAY": WS("Layipo", "Layipo or Laipo, also Bower's Tower, at Oke-Are in Ibadan; built in 1936 to commemorate Captain Robert Lister Bower, the first British Resident in Ibadan."),
    "WCOC": WS("Cocoa House", "Skyscraper at Dugbe, Ibadan; initiated by Obafemi Awolowo under the Western Region Development Plan; completed July 1964, commissioned July 1965; 105 m; the first skyscraper in West Africa; houses the Odua Museum."),
    "WOYS": dict(P123.SOURCES["WOYS"], notes="Reused. LGA headquarters: Ibadan South-East at Mapo; Ibadan North-West at Dugbe/Onireke."),
}
TEXT = {
 "mapo": """Mapo Hall, the colonial city hall of Ibadan on Mapo Hill, is No. 11 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Architectural' category. According to Wikipedia, it was built between 1925 and 1929 to the design of the engineer Robert Jones, at a cost of £24,000, and opened in the time of Alaafin Ladugbolu and Baale Oyewole of Ibadan. Its neoclassical hall, visible across the city, has hosted major political events, and it was renovated in 2006–07. Olubadan Rashidi Ladoja was crowned there in September 2025 (The Gazette).""",
 "crowther": """The home of Bishop Ajayi Crowther, at Iseyin in Oke-Ogun, is No. 36 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Samuel Ajayi Crowther was born about 1809 at Osogun, near Ado-Awaye in present Oyo State; captured with his family by slave raiders in 1821 and freed, he became a linguist and missionary and, in 1864, the first African Anglican bishop (Wikipedia). The home itself is not described in a source read.""",
 "park": """Old Oyo National Park covers 2,512 km² of northern Oyo State, extending into southern Kwara State, and contains the ruins of Oyo-Ile, the old capital of the Oyo Empire (Wikipedia). It grew from the Upper Ogun forest reserve of 1936 and the Oyo-Ile reserve of 1941, which became game reserves in 1952 and were later combined as a national park. It shelters buffalo, bushbuck and many birds, and its head office is in Oyo; towns around it include Saki, Iseyin, Igboho, Sepeteri, Tede, Kishi and Igbeti. By 2022 it was threatened by poaching, logging and encroachment (Wikipedia).""",
 "oyoile": """Old Oyo, or Oyo-Ile (also Katunga), is the ruined site of the capital of the Oyo Empire, abandoned since 1835, when Ilorin destroyed it (Wikipedia). It was the seat of the Alaafin, with great markets such as the Akesan market, later recreated in the new Oyo, and archaeologists have worked there for more than four decades. The site covers nearly 3,000 hectares and lies within Old Oyo National Park.""",
 "bower": """Bower's Tower, also called Layipo, stands on Oke-Are, Aare's Hill, in Ibadan, and was built in 1936 in memory of Captain Robert Lister Bower, the first British Resident in Ibadan (Wikipedia). The tower can be seen from almost anywhere in the city and gives a view over the whole of it.""",
 "cocoa": """Cocoa House, at Dugbe in Ibadan, was the first skyscraper in West Africa and, from 1965 to 1979, the tallest building in Nigeria (Wikipedia). It was initiated by Chief Obafemi Awolowo under the Western Region Development Plan, paid for from the proceeds of cocoa and other farm produce, completed in 1964 and commissioned in 1965; it is 105 metres tall. It houses the Odua Museum and offices.""",
 "museum_ibadan": """The National Museum Ibadan is one of the national museums of the National Commission for Museums and Monuments, in the Alesinloye area of Ibadan. Its history and collections are not described in a source read.""",
 "museum_oyo": """The National Museum Oyo is one of the national museums of the National Commission for Museums and Monuments, at 1 Palace Road, beside the palace of the Alaafin in Oyo. Its history and collections are not described in a source read.""",
 "museum_ogbomoso": """The National Museum Ogbomosho is one of the national museums of the National Commission for Museums and Monuments, at 3 Museum Street, off Sunsun Road, Ogbomoso. Its history and collections are not described in a source read.""",
 "museum_oko": """The National Museum Oko-Surulere is one of the stations of the National Commission for Museums and Monuments, at Oko in the Ogbomoso area. Its history and collections are not described in a source read.""",
}
ST = "@admin_units:state:oyo"
LG = lambda l: f"@admin_units:lga:oyo/{l}"
REC = [
    ("mapo", dict(place_type="historical_place", name="Mapo Hall", slug="mapo-hall", admin_unit_id=LG("ibadan-south-east"), status="existing"), ["NCMMP", "WMAP", "WOYS", "GZOLU"], "well_documented"),
    ("crowther", dict(place_type="historical_place", name="Bishop Ajayi Crowther Home, Iseyin", slug="bishop-ajayi-crowther-home-iseyin", admin_unit_id=LG("iseyin"), status="existing"), ["NCMMP", "WCRO"], "well_documented"),
    ("park", dict(place_type="natural_feature", name="Old Oyo National Park", slug="old-oyo-national-park", admin_unit_id=ST, status="existing"), ["WOOP"], "well_documented"),
    ("oyoile", dict(place_type="archaeological_site", name="Old Oyo (Oyo-Ile)", slug="old-oyo-oyo-ile", admin_unit_id=ST, status="historical"), ["WOLD", "WOOP"], "well_documented"),
    ("bower", dict(place_type="monument", name="Bower's Tower", slug="bowers-tower", admin_unit_id=ST, status="existing"), ["WLAY"], "well_documented"),
    ("cocoa", dict(place_type="historical_place", name="Cocoa House", slug="cocoa-house", admin_unit_id=LG("ibadan-north-west"), status="existing"), ["WCOC", "WOYS"], "well_documented"),
    ("museum_ibadan", dict(place_type="museum", name="National Museum Ibadan", slug="national-museum-ibadan", admin_unit_id=ST, status="existing"), ["NCMMM"], "verified"),
    ("museum_oyo", dict(place_type="museum", name="National Museum Oyo", slug="national-museum-oyo", admin_unit_id=ST, status="existing"), ["NCMMM"], "verified"),
    ("museum_ogbomoso", dict(place_type="museum", name="National Museum Ogbomosho", slug="national-museum-ogbomosho", admin_unit_id=ST, status="existing"), ["NCMMM"], "verified"),
    ("museum_oko", dict(place_type="museum", name="National Museum Oko-Surulere", slug="national-museum-oko-surulere", admin_unit_id=ST, status="existing"), ["NCMMM"], "verified"),
]
RECORDS = []
for key, fields, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="oyoile", type="associated_with", to="@polities:oyo-empire", role="the empire's capital, abandoned in 1835", source="WOLD", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Old Oyo; Oyo Empire)."),
    dict(frm="park", type="associated_with", to="@polities:oyo-empire", role="national park containing the ruins of Oyo-Ile", source="WOOP", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Old Oyo National Park)."),
    dict(frm="mapo", type="associated_with", to="@polities:olubadan-of-ibadan", role="Ibadan's city hall, where the 44th Olubadan was crowned", source="WMAP", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Mapo Hall); The Gazette, 26 September 2025."),
    dict(frm="museum_oyo", type="associated_with", to="@polities:oyo-kingdom", role="national museum on Palace Road beside the Alaafin's palace", source="NCMMM", evidence="single_reliable_source", level="reported", notes="NCMM: '1 Palace Road, Alafin, Oyo'."),
]
NAMES = [
    dict(record="bower", name="Layipo", name_type="alternative", usage_notes="Wikipedia (also 'Laipo').", srcs=["WLAY"]),
    dict(record="oyoile", name="Katunga", name_type="historical", usage_notes="Wikipedia: 'Old Oyo, also known as Oyo-Ile, Katunga, Oyo-Oro, and Eyo'.", srcs=["WOLD"]),
    dict(record="cocoa", name="Ile Awon Agbe", name_type="historical", usage_notes="Original name, 'House of Farmers' (Wikipedia).", srcs=["WCOC"]),
]
GAPS = [
    ("Oyo: no declared monument", "The NCMM's declared list has no Oyo entry; Mapo Hall and the Crowther home are proposed."),
    ("Oyo: festivals", "The Egungun, Oke'Badan and Sango festivals of Ibadan, Oyo and Ogbomoso have no usable article; sources needed."),
    ("Oyo: museums and the Crowther home", "The four national museums and the Crowther home are not described in a source read; the LGAs of the museums and Bower's Tower are not given."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Oyo culture and heritage: Mapo Hall and the Ajayi Crowther home (NCMM proposed), Old Oyo National Park and the ruins of Oyo-Ile, Bower's Tower, Cocoa House and four national museums.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 126 — Oyo: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **{len(RECORDS)} places:**",
         "  - **Mapo Hall** (proposed No. 11), Ibadan's city hall, where Ladoja was crowned",
         "  - **Bishop Ajayi Crowther's home**, Iseyin (proposed No. 36)",
         "  - **Old Oyo National Park** and the ruins of **Old Oyo (Oyo-Ile)**, both linked to the Oyo Empire",
         "  - **Bower's Tower** (1936) and **Cocoa House**, West Africa's first skyscraper (1965)",
         "  - **four national museums:** Ibadan, Oyo, Ogbomosho and Oko-Surulere",
         "- **No declared monument:** Oyo has none on the NCMM declared list.",
         "- **Festivals:** none recorded, because no usable source was found. This is a gap.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, fields, srcs, lvl in REC:
        L += [f"## {fields['name']} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_126_oyo_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_126_oyo_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
