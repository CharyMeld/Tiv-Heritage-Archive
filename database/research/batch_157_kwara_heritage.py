"""
Research batch 157 — Kwara (Phase 3): culture and heritage. Researched 2026-10-07. Pattern: batches 132, 145, 151.

NCMM declared list: 39 'Relics of the Steamer "Dayspring" at Jebba Station'; 40 'Old West African Frontier Force Fort at
Okuta'; 41 'Old West African Frontier Force Fort at Yashikera'; 42 'Stone Figures at Ofaro'; 43 'Stone Figures at Ijara'.
NCMM proposed list (hand count, calibrated: Yakoko 66, Namoda 76, Baturiya 94): Historic 70 'Esie Stone Images
(National Museum, Esie)'; Historic 71 'Alimi Mosque, Ilorin'; Historic 73 'Queen Elizabeth Girls School, Ilorin'; Wild
Life/Forest Reserve 100 'Oya Shrine and Grove, Iraa'. NCMM museums: National Museum Esie (Esie Museum Road); National
Museum Ilorin (14 Abdulkadri Road, GRA Ilorin).
Wikipedia: 'Esiẹ Museum' (opened 1945, the first museum in Nigeria; over a thousand soapstone figures; a festival every
April); 'Esiẹ' (founded c. 1770; Igbonna dialect); 'Igbomina' (over 800 carved stones around Esie, Iji-Isin, Ijara and
Ofaro); 'Jebba' (the Dayspring wrecked at Jebba in 1857, part of the wreck still at the railway station; Jebba South in
Moro LGA); 'Pategi Emirate' (the Pategi Regatta, founded 1949, first held 1952); 'Offa, Nigeria' (the Onimoka festival).
Placement: Okuta and Yashikera → Baruten (INEC wards Okuta 02-09, Yashikira 02-11); Ijara → Isin (INEC ward 10-05;
Wikipedia, Isin); Esie and its museum → Irepodun (INEC ward Esie/Ijan 09-04); the Dayspring relics → Moro (reported: the
station's side of Jebba from Wikipedia); Ofaro, Iraa and the Ilorin sites → state only.
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_154_kwara_languages_peoples as P154
import batch_156_kwara_institutions as I156

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMML": dict(H66.SOURCES["NCMML"], notes="Reused. Kwara: 39 Relics of the Steamer 'Dayspring' at Jebba Station; 40 Old West African Frontier Force Fort at Okuta; 41 Old West African Frontier Force Fort at Yashikera; 42 Stone Figures at Ofaro; 43 Stone Figures at Ijara."),
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Kwara: Historic 70 Esie Stone Images (National Museum, Esie); 71 Alimi Mosque, Ilorin; 73 Queen Elizabeth Girls School, Ilorin; Wild Life/Forest Reserve 100 Oya Shrine and Grove, Iraa."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. Kwara: National Museum Esie (Esie Museum Road, Esie); National Museum Ilorin (14 Abdulkadri Road, GRA Ilorin)."),
    "WESM": WS("Esiẹ Museum", "Opened in 1945, the first museum in Nigeria; once housed over a thousand figures representing human beings; 'reputed to have the largest collection of soapstone images in the world'; hosts a festival every April."),
    "WESIE": WS("Esiẹ", "Town in Kwara State founded by Prince Baragbon c. 1770; Igbonna dialect; home of the Esie Museum."),
    "WIGBO": dict(P154.SOURCES["WIGBO"], notes="Reused. 'Over 800 carved stones, mostly representing human figures, have been found around Esie in western Igbomina, Iji-Isin, Ijara and Ofaro villages.'"),
    "WJEB": WS("Jebba", "Jebba South belongs to Moro LGA of Kwara State, Jebba North to Mokwa LGA of Niger State; the Dayspring 'got wreckage in Jebba … in 1857. Part of the wreckage is still at the Railway Station, Jebba'."),
    "WPATE": I156.SOURCES["WPATE"],
    "WOFFA": dict(P154.SOURCES["WOFFA"], notes="Reused. The Onimoka festival, an annual event in memory of Queen Moremi, an Offa indigene, with mock wrestling (Ijakadi) by the chiefs and the Olofa."),
    "WISIN": P154.SOURCES["WISIN"],
    "INEC": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Directory of Polling Units: Kwara State (Revised January 2015)",
                 organisation="Independent National Electoral Commission (INEC)", publication_date="2015-01",
                 url="https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_Kwara.pdf", verification_status="verified", notes="Reused (batch 155)."),
}
D = "is No. {n} on the National Commission for Museums and Monuments' list of declared national monuments"
P = "is No. {n} on the National Commission for Museums and Monuments' list of proposed national monuments, in the '{c}' category"
TEXT = {
 "dayspring": f"""The relics of the steamer Dayspring at Jebba railway station {D.format(n=39)}. According to Wikipedia, the Dayspring was wrecked at Jebba on the Niger in 1857, and part of the wreck still lies at the railway station; the Kwara side of the town, Jebba South, is in Moro LGA. Wikipedia also records that Bishop Ajayi Crowther was at Jebba at that time. The steamer's voyage is not described in more detail in a source read.""",
 "okuta": f"""The old fort of the West African Frontier Force at Okuta, in Baruten LGA, {D.format(n=40)}. Okuta is a ward of Baruten in INEC's directory. The fort's history is not described in a source read.""",
 "yashikera": f"""The old fort of the West African Frontier Force at Yashikera, in Baruten LGA, {D.format(n=41)}. Yashikira is a ward of Baruten in INEC's directory. The fort's history is not described in a source read.""",
 "ofaro": f"""The stone figures at Ofaro {D.format(n=42)}. Wikipedia records that more than 800 carved stones, mostly human figures, have been found around Esie, Iji-Isin, Ijara and Ofaro in western Igbominaland. Ofaro's LGA is not given in a source read.""",
 "ijara": f"""The stone figures at Ijara, in Isin LGA, {D.format(n=43)}. Ijara is one of the carved-stone sites of western Igbominaland, with Esie, Iji-Isin and Ofaro (Wikipedia), and an Isin ward in INEC's directory.""",
 "esie": f"""The Esie stone images, at the National Museum Esie in Irepodun LGA, {P.format(n=70, c='Historic')}. According to Wikipedia, the museum, opened in 1945, was the first in Nigeria, and it once housed more than a thousand figures representing human beings, reputed to be the largest collection of soapstone images in the world. The images are now also a focus of religious activity, with a festival every April. The town of Esie, founded about 1770, speaks the Igbonna (Igbomina) dialect of Yoruba.""",
 "alimi": f"""The Alimi Mosque in Ilorin {P.format(n=71, c='Historic')}. It bears the name of Shehu Alimi, the Muslim scholar whose alliance with Afonja about 1810 led to the founding of the Ilorin Emirate (Wikipedia, Ilorin Emirate). The mosque itself is not described in a source read.""",
 "qegs": f"""Queen Elizabeth Girls School in Ilorin {P.format(n=73, c='Historic')}. The school's history is not described in a source read.""",
 "oya": f"""The Oya shrine and grove at Iraa {P.format(n=100, c='Wild Life/Forest Reserve')}. The site's history and Iraa's LGA are not described in a source read.""",
 "museum_ilorin": """The National Museum Ilorin is one of the national museums of the National Commission for Museums and Monuments, at 14 Abdulkadri Road, GRA, Ilorin. Its history and collections are not described in a source read.""",
 "regatta": """The Pategi Regatta is a canoe festival held on the Niger at Pategi, the seat of the Pategi Emirate, with canoe races and displays at Gbaradogi on the west bank and Nupe cultural displays at the Etsu's palace (Wikipedia). It was founded by the emirate's traditional council in 1949 and first held in 1952, and it involves the people of the emirate and others living along the Niger in Kwara, Niger and Kogi states. Wikipedia describes it as biennial.""",
 "onimoka": """Onimoka is the major traditional festival of Offa, held every year in memory of Queen Moremi, who in tradition was an Offa woman who saved the kingdom of Ile-Ife from invaders (Wikipedia). Wrestling contests are held during it, and the chiefs and the Olofa take part in mock wrestling, ijakadi.""",
}
ST = "@admin_units:state:kwara"
LG = lambda l: f"@admin_units:lga:kwara/{l}"
REC = [
    ("dayspring", "places", dict(place_type="monument", name="Relics of the Steamer Dayspring, Jebba", slug="dayspring-relics-jebba", admin_unit_id=LG("moro"), status="existing"), ["NCMML", "WJEB"], "verified"),
    ("okuta", "places", dict(place_type="historical_place", name="West African Frontier Force Fort, Okuta", slug="waff-fort-okuta", admin_unit_id=LG("baruten"), status="existing"), ["NCMML", "INEC"], "verified"),
    ("yashikera", "places", dict(place_type="historical_place", name="West African Frontier Force Fort, Yashikera", slug="waff-fort-yashikera", admin_unit_id=LG("baruten"), status="existing"), ["NCMML", "INEC"], "verified"),
    ("ofaro", "places", dict(place_type="archaeological_site", name="Stone Figures, Ofaro", slug="stone-figures-ofaro", admin_unit_id=ST, status="existing"), ["NCMML", "WIGBO"], "verified"),
    ("ijara", "places", dict(place_type="archaeological_site", name="Stone Figures, Ijara", slug="stone-figures-ijara", admin_unit_id=LG("isin"), status="existing"), ["NCMML", "WIGBO", "INEC"], "verified"),
    ("esie", "places", dict(place_type="museum", name="Esie Museum and Stone Images", slug="esie-museum", admin_unit_id=LG("irepodun"), status="existing"), ["NCMMP", "NCMMM", "WESM", "WESIE", "INEC"], "verified"),
    ("alimi", "places", dict(place_type="sacred_site", name="Alimi Mosque, Ilorin", slug="alimi-mosque-ilorin", admin_unit_id=ST, status="existing"), ["NCMMP"], "verified"),
    ("qegs", "places", dict(place_type="institution", name="Queen Elizabeth Girls School, Ilorin", slug="queen-elizabeth-girls-school-ilorin", admin_unit_id=ST, status="existing"), ["NCMMP"], "verified"),
    ("oya", "places", dict(place_type="sacred_site", name="Oya Shrine and Grove, Iraa", slug="oya-shrine-iraa", admin_unit_id=ST, status="existing"), ["NCMMP"], "verified"),
    ("museum_ilorin", "places", dict(place_type="museum", name="National Museum Ilorin", slug="national-museum-ilorin", admin_unit_id=ST, status="existing"), ["NCMMM"], "verified"),
    ("regatta", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Pategi Regatta", slug="pategi-regatta",
                                         timing="Biennial (Wikipedia); first held 1952", current_status="unknown", scope_level="community"), ["WPATE"], "reported"),
    ("onimoka", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Onimoka Festival", slug="onimoka-festival",
                                         timing="Annually", current_status="unknown", scope_level="community"), ["WOFFA"], "reported"),
]
RECORDS = [dict(key=k, table=t, evidence="multiple_sources" if len(s) > 1 else "single_reliable_source", level=lvl,
                fields=dict(f, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k]), srcs=[(x, f["name"]) for x in s]) for k, t, f, s, lvl in REC]
RELATIONS = [
    dict(frm="alimi", type="associated_with", to="@polities:ilorin-emirate", role="mosque named after Shehu Alimi, whose alliance founded the emirate", source="NCMMP", evidence="single_reliable_source", level="reported", notes="NCMM; Wikipedia (Ilorin Emirate)."),
    dict(frm="regatta", type="associated_with", to="@polities:pategi-emirate", role="festival founded by the emirate's traditional council", source="WPATE", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Pategi Emirate)."),
    dict(frm="regatta", type="celebrated_in", to=LG("pategi"), source="WPATE", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Pategi; Pategi Emirate)."),
    dict(frm="regatta", type="celebrated_by", to="@ethnic_groups:nupe", source="WPATE", evidence="single_reliable_source", level="well_documented", notes="Wikipedia: Nupe cultural displays at the Etsu's palace."),
    dict(frm="onimoka", type="associated_with", to="@polities:offa-kingdom", role="the Olofa and chiefs take part in its mock wrestling", source="WOFFA", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Offa)."),
    dict(frm="onimoka", type="celebrated_in", to=LG("offa"), source="WOFFA", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Offa)."),
    dict(frm="onimoka", type="celebrated_by", to="@ethnic_groups:yoruba", source="WOFFA", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Offa)."),
    dict(frm="ijara", type="associated_with", to="esie", role="one of the carved-stone sites of western Igbominaland", source="WIGBO", evidence="single_reliable_source", level="reported", notes="Wikipedia (Igbomina)."),
    dict(frm="ofaro", type="associated_with", to="esie", role="one of the carved-stone sites of western Igbominaland", source="WIGBO", evidence="single_reliable_source", level="reported", notes="Wikipedia (Igbomina)."),
]
NAMES = [
    dict(record="esie", name="National Museum Esie", name_type="official", usage_notes="The NCMM's name for the museum.", srcs=["NCMMM"]),
    dict(record="esie", name="Esie Stone Images", name_type="official", usage_notes="The NCMM's name on the proposed list (Historic No. 70).", srcs=["NCMMP"]),
    dict(record="yashikera", name="Yashikira", name_type="spelling_variant", usage_notes="INEC's spelling of the ward.", srcs=["INEC"]),
]
GAPS = [
    ("Kwara: undescribed monuments", "The Okuta and Yashikera forts, the Ofaro and Ijara figures, the Alimi Mosque, Queen Elizabeth Girls School, the Oya grove at Iraa and the Dayspring's voyage are not described in a source read; the LGAs of Ofaro and Iraa are not given."),
    ("Kwara: the Dayspring site", "Wikipedia places the wreck at Jebba railway station and Jebba South in Moro LGA; whether the station is on the Kwara side is not stated, so the Moro placement rests on that reading."),
    ("Kwara: festivals' current status", "The Pategi Regatta and the Onimoka festival are described from Wikipedia only; recent dates were not found."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kwara culture and heritage: 5 declared and 4 proposed NCMM monuments (including the Esie stone images), 2 national museums, the Pategi Regatta and the Onimoka festival.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 157 — Kwara: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **10 places:**",
         "  - **5 declared national monuments:** the relics of the steamer **Dayspring** at Jebba; the old **West African Frontier Force forts** at Okuta and Yashikera; the **stone figures** at Ofaro and Ijara",
         "  - **4 proposed:** the **Esie stone images**; the **Alimi Mosque**; **Queen Elizabeth Girls School**; the **Oya shrine and grove** at Iraa",
         "  - **Esie** and the **National Museum Ilorin**. The Esie Museum is one record, with the stone images; it opened in 1945 and was Nigeria's first museum.",
         "- **2 festivals:** the **Pategi Regatta**, a canoe festival on the Niger, and **Onimoka** at Offa. Both are *reported*, from Wikipedia only.",
         "- **NCMM numbers:** declared 39–43 are printed on the list. The proposed numbers 70, 71, 73 and 100 were hand-counted and checked against known points (Yakoko 66, Namoda 76, Baturiya 94).", ""]
    for k, t, f, s, lvl in REC:
        L += [f"## {f['name']} ({words(TEXT[k])} words; {lvl})", "", f"> {TEXT[k]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_157_kwara_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_157_kwara_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} sources={len(SOURCES)}")
