"""
Research batch 066 — Borno (Phase 3): culture and heritage. Researched 2026-10-01. Pattern: batches 054 and 060.

Places (7): Rabeh's House/Fort, Dikwa (NCMM declared national monument No. 13); the El-Kanemi Prayer House, Ngala,
and the Tomb of the First Four Shehus (NCMM proposed Nos. 60 and 61); the National Museum Maiduguri (NCMM);
Kukawa, the old capital of the al-Kanemi shehus; Chad Basin National Park (its Borno sector) and Sambisa Forest.
Cultural record (1): the Kanem-Borno Cultural Summit, Maiduguri (Voice of Nigeria, 2 January 2026).

Evidence notes:
  * NCMM lists read as raw HTML on 2026-10-01 (batch 060). Proposed-list numbers counted in page order across
    categories, checked against Union Jack (32), Elephant House (18) and Yakoko (66).
  * The Tomb of the First Four Shehus is given only as 'Borno State' — state link only. (Kukawa, where the early
    shehus ruled, is a likely place, but no source read says so — gap.)
  * Chad Basin National Park: Wikipedia — three sectors, Chingurmi-Duguma in Borno, Bade-Nguru Wetlands and
    Bulatura in Yobe; about 2,258 km²; established 1991. Only the Borno link is made; no LGA named.
  * Sambisa Forest: Wikipedia — in the south-west of Chad Basin National Park, ~60 km south-east of Maiduguri,
    518 km² (the colonial game reserve is also given as 2,258 km²); taken over by Boko Haram in 2013.
  * Not recorded: the Eid durbar inscribed by UNESCO in December 2024 (News Central TV) concerns Kano; it belongs
    to a Kano or national batch. Festivals of Borno's peoples: no reliable source read (gap).
"""
import json, re, sys

ACCESSED = "2026-10-01"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMML": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="List of National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/list-of-national-monuments/",
                  verification_status="verified", notes="Reused. Borno: No. 13 'Rabeh's House/Fort, Dikwa Borno State'."),
    "NCMMP": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="Proposed National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/proposed-national-monuments/",
                  verification_status="verified", notes="Reused. Borno (Historic): No. 60 'El-Kanemi Prayer House, Ngala'; No. 61 'Tomb of the First Four Shehu's, Borno State'."),
    "NCMMM": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="National Museums",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/museums/national-museums/",
                  verification_status="verified", notes="Reused. National Museum Maiduguri, Custom Area, Maiduguri, P.M.B. 1029."),
    "WDIK": WS("Dikwa", "Rabih az-Zubayr made Dikwa his capital after destroying Kukawa in 1893; 'Dikwa was heavily fortified and remained Rabih's capital'; Rabih killed at Kousséri in 1900."),
    "WKUK": WS("Kukawa", "Founded as Kuka in 1814 by Muhammad al-Amin al-Kanemi; capital of the Kanem–Bornu Empire 1846–1893; rebuilt by Umar Kura as two walled towns within a 20-foot outer wall; destroyed by Rabih in May 1893."),
    "WLSH": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="List of shehus of Bornu", organisation="Wikipedia", url=W("List of shehus of Bornu"),
                 verification_status="needs_corroboration", notes="Reused. Abubakar Garbai invested as shehu among the ruins of Kukawa by Lugard in 1904; moved to Yerwa (Maiduguri) on 9 January 1907."),
    "WCBNP": WS("Chad Basin National Park", "About 2,258 km² in three sectors: Chingurmi-Duguma (Borno), Bade-Nguru Wetlands and Bulatura (Yobe); established 1991."),
    "WSAM": WS("Sambisa Forest", "In the south-western part of Chad Basin National Park, about 60 km south-east of Maiduguri; 518 km²; incorporated into the park in 1991; taken over by Boko Haram in February 2013."),
    "VON26": dict(source_type="news", source_kind="news", source_tier=3, title="Governor Zulum Hosts Kanem-Borno Cultural Summit", organisation="Voice of Nigeria",
                  author="Abubakar Mohammed", publication_date="2026-01-02", url="https://von.gov.ng/governor-zulum-hosts-kanem-borno-cultural-summit/",
                  verification_status="needs_corroboration",
                  notes="Kanem-Borno Cultural Summit in Maiduguri: 161 emirs and Kanuri delegations from at least ten countries (Ghana, Sudan, Gabon, Niger, CAR, Senegal, Libya, Chad, Cameroon, Benin); cultural performances, dances and heritage displays; 150 scholarships announced."),
}
TEXT = {
 "rabeh": """Rabeh's House or Fort at Dikwa is No. 13 on the National Commission for Museums and Monuments' list of declared national monuments, and the only declared national monument in Borno State. Dikwa was the capital of the Sudanese warlord Rabih az-Zubayr, who conquered the Kanem–Bornu Empire in 1893, destroyed its capital at Kukawa and settled at Dikwa because of its better communications and water supply; according to Wikipedia the town was heavily fortified and remained his capital until he was killed at the battle of Kousséri in 1900. The fort's present condition is not described in a readable source.""",
 "prayer": """The El-Kanemi Prayer House at Ngala is No. 60 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Its age and its link to Muhammad al-Amin al-Kanemi, founder of the dynasty of the shehus of Borno, are not described in a readable source.""",
 "tomb": """The Tomb of the First Four Shehus is No. 61 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category; the list places it only in 'Borno State'. The shehus are the rulers of the al-Kanemi dynasty, whose line began with Muhammad al-Amin al-Kanemi in the early 19th century. Which shehus are buried there, and where, is not stated in the list.""",
 "museum": """The National Museum Maiduguri is one of the national museums of the National Commission for Museums and Monuments (NCMM), which gives its address as the Custom Area, Maiduguri, P.M.B. 1029. Its history and collections are not described in a readable source.""",
 "kukawa": """Kukawa, near Lake Chad, was founded as Kuka in 1814 by Muhammad al-Amin al-Kanemi, the scholar and military leader whose descendants became the shehus of Borno, and it served as the capital of the Kanem–Bornu Empire from 1846 to 1893 (Wikipedia). Umar Kura rebuilt it as two towns, each with a wall of white clay, enclosed with villages, farms and a cemetery by a mud wall some 6 metres high. Rabih az-Zubayr captured and destroyed Kukawa in May 1893. Shehu Abubakar Garbai was invested among its ruins in 1904, but moved his capital to Yerwa, now Maiduguri, in 1907.""",
 "chad": """Chad Basin National Park is a national park of about 2,258 km² in the Chad Basin of north-eastern Nigeria, established in 1991 (Wikipedia). It is fragmented into three sectors: the Chingurmi-Duguma sector, in the Sudanian savanna of Borno State, and the Bade-Nguru Wetlands and Bulatura sectors, in the Sahel of Yobe State. Wikipedia places the Sambisa Forest in its south-western part.""",
 "sambisa": """The Sambisa Forest lies about 60 km south-east of Maiduguri, at the edge of the West Sudanian and Sahel savannas, and has an area of about 518 km² (Wikipedia). The colonial Sambisa Game Reserve was incorporated into Chad Basin National Park in 1991. After Boko Haram insurgents took over the forest in February 2013, its management was abandoned and most of its large animals disappeared, according to Wikipedia.""",
 "summit": """The Kanem-Borno Cultural Summit, hosted in Maiduguri by Governor Babagana Zulum and reported by Voice of Nigeria on 2 January 2026, brought together 161 emirs and delegations of Kanuri people from at least ten African countries, among them Chad, Niger, Cameroon, Sudan, Libya and Ghana. It aimed to strengthen cross-border kinship among Kanuri communities and preserve the legacy of the Kanem–Bornu civilisation, with cultural performances, traditional dances and heritage displays. Whether it will be held regularly is not stated.""",
}
B = lambda l: f"@admin_units:lga:borno/{l}"
REC = [
    ("rabeh", "places", dict(place_type="monument", name="Rabeh's Fort, Dikwa", slug="rabehs-fort-dikwa", admin_unit_id=B("dikwa"), status="existing"), ["NCMML", "WDIK"], "verified"),
    ("prayer", "places", dict(place_type="historical_place", name="El-Kanemi Prayer House, Ngala", slug="el-kanemi-prayer-house-ngala", admin_unit_id=B("ngala"), status="unknown"), ["NCMMP"], "verified"),
    ("tomb", "places", dict(place_type="historical_place", name="Tomb of the First Four Shehus", slug="tomb-of-the-first-four-shehus", admin_unit_id="@admin_units:state:borno", status="unknown"), ["NCMMP"], "verified"),
    ("museum", "places", dict(place_type="museum", name="National Museum Maiduguri", slug="national-museum-maiduguri", admin_unit_id=B("maiduguri"), status="existing"), ["NCMMM"], "verified"),
    ("kukawa", "places", dict(place_type="historical_place", name="Kukawa", slug="kukawa", admin_unit_id=B("kukawa"), status="existing"), ["WKUK", "WLSH"], "reported"),
    ("chad", "places", dict(place_type="natural_feature", name="Chad Basin National Park", slug="chad-basin-national-park", admin_unit_id="@admin_units:state:borno", status="existing"), ["WCBNP", "WSAM"], "reported"),
    ("sambisa", "places", dict(place_type="natural_feature", name="Sambisa Forest", slug="sambisa-forest", admin_unit_id="@admin_units:state:borno", status="existing"), ["WSAM"], "reported"),
    ("summit", "cultural_records", dict(record_type="ceremony", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Kanem-Borno Cultural Summit",
                                        slug="kanem-borno-cultural-summit", timing="Held in Maiduguri; reported 2 January 2026", current_status="unknown", scope_level="regional"), ["VON26"], "reported"),
]
RECORDS = []
for key, table, fields, srcs, lvl in REC:
    t = TEXT[key]
    f = dict(fields, summary=t.split(". ")[0] + ".", description=t)
    RECORDS.append(dict(key=key, table=table, evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl, fields=f, srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="kukawa", type="associated_with", to="@polities:borno-emirate", role="capital of the al-Kanemi shehus, 1846–1893", source="WKUK", evidence="multiple_sources", level="reported",
         notes="Capital of the Kanem–Bornu Empire under the al-Kanemi dynasty (Wikipedia, Kukawa); the shehus moved to Maiduguri in 1907 (Wikipedia, List of shehus)."),
    dict(frm="tomb", type="associated_with", to="@polities:borno-emirate", role="burial of the first four shehus", source="NCMMP", evidence="single_reliable_source", level="reported",
         notes="Named in the NCMM list; the link to the Borno Emirate rests on the title 'shehu'."),
    dict(frm="sambisa", type="part_of", to="chad", source="WSAM", evidence="single_reliable_source", level="reported", notes="Wikipedia: in the south-western part of Chad Basin National Park; incorporated in 1991."),
    dict(frm="summit", type="celebrated_by", to="@ethnic_groups:kanuri", source="VON26", evidence="single_reliable_source", level="reported", notes="'thousands of Kanuri kinsmen from at least ten African countries' (Voice of Nigeria)."),
    dict(frm="summit", type="celebrated_in", to=B("maiduguri"), source="VON26", evidence="single_reliable_source", level="reported", notes="Held in Maiduguri (Voice of Nigeria, 2 January 2026)."),
]
NAMES = [
    dict(record="rabeh", name="Rabeh's House", name_type="official", usage_notes="NCMM list: 'Rabeh's House/Fort, Dikwa'.", srcs=["NCMML"]),
    dict(record="kukawa", name="Kuka", name_type="historical", usage_notes="Original name of the town (Wikipedia).", srcs=["WKUK"]),
]
GAPS = [
    ("Borno: monuments", "The NCMM names Rabeh's Fort, the El-Kanemi Prayer House and the Tomb of the First Four Shehus without dates, descriptions or (for the tomb) a town. Their condition after the insurgency is not known."),
    ("Borno: festivals", "No reliable source was read for the festivals of Borno's peoples (Kanuri, Shuwa, Bura, Marghi and others). The Eid durbar inscribed by UNESCO in December 2024 concerns Kano and is left for a Kano or national batch."),
    ("Borno: Chad Basin National Park and Sambisa", "The park's Borno sector (Chingurmi-Duguma) is not tied to an LGA in a source read; Wikipedia gives two areas for the Sambisa game reserve (518 and 2,258 km²)."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Borno culture and heritage: Rabeh's Fort (declared No. 13), two proposed monuments, National Museum Maiduguri, Kukawa, Chad Basin National Park, Sambisa Forest, the Kanem-Borno Cultural Summit.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 066 — Borno: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **7 places:**",
         "  - **Rabeh's Fort, Dikwa**: Borno's only *declared* national monument (No. 13), and Rabih az-Zubayr's fortified capital from 1893 to 1900",
         "  - **two proposed national monuments:** the El-Kanemi Prayer House at Ngala (No. 60) and the Tomb of the First Four Shehus (No. 61). The NCMM places the tomb only in 'Borno State'.",
         "  - the **National Museum Maiduguri**",
         "  - **Kukawa**: the al-Kanemi capital from 1846 to 1893",
         "  - **Chad Basin National Park** (Borno sector) and **Sambisa Forest**",
         "- **1 cultural record:** the **Kanem-Borno Cultural Summit** in Maiduguri, reported on 2 January 2026, at *reported* level.",
         "- **Links:** Kukawa and the tomb to the Borno Emirate; Sambisa to the park; the summit to the Kanuri and Maiduguri.",
         "- **Not recorded:**",
         "  - the UNESCO-listed Eid durbar, which concerns Kano",
         "  - festivals of Borno's peoples: no reliable source was found",
         "- All texts are under 300 words, so the sitemap is unchanged.", ""]
    for key, table, fields, srcs, lvl in REC:
        t = TEXT[key]
        L += [f"## {fields['name']} ({words(t)} words; {lvl})", "", f"> {t}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_066_borno_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_066_borno_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
