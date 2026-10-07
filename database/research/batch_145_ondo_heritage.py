"""
Research batch 145 — Ondo (Phase 3): culture and heritage. Researched 2026-10-07. Pattern: batch 132 (Osun).

NCMM declared list: 53 'Igbara Oke Petroglyphs, near Igbara Oke, Ondo State'; 54 'Iwo Eleru Cave, Near Owo, Ondo State';
55 'The Old Palace of The Deji Of Akure, Ondo State'. NCMM proposed list (hand count, calibrated: Ifa Temple 15, Kusugu 25,
Oranmiyan Staff 47, Dutse Bamle 81, Erin Ijesa 83): Historic 48 'Oke-Idanre Cultural Landscape, Idanre'; Natural 84
'Ashuba Marble Stone, Araromi Obu'; Wild Life/Forest Reserve 96 'Igbo Olodumare – "Forest of a Thousand Daemons"'. NCMM
museums: National Museum Akure (Opposite Post Office, Oba Adesida Road); National Museum Owo (Olowo's Palace, Owo).
Wikipedia: 'Iho Eleru' (rock shelter at Isarun; Later Stone Age; reported in 1961; the skull about 13,000 years old);
'Isarun' (in Ifedore; the cave found by the hunter Chief Obele in 1922; Shaw's excavation 1965; about 11,200 years);
'Idanre Hill' (cultural sites; nominated for the UNESCO shortlist; Ogun and Ije festivals; Soyinka's 'Idanre');
'Palace of Olowo of Owo' (said to be the largest palace in Africa; built 1340 in Wikipedia's account; national monument
2000 per Wikipedia, though it is not on the NCMM declared list read); 'Igbara-Oke' (headquarters of Ifedore);
'Akure Kingdom' (the palace dated to 1150 AD); 'Igogo festival'. Peoples Gazette (6 Oct 2024; 14 Sep 2025): Ulefunta.
Placement: Igbara-Oke and Isarun → Ifedore (Wikipedia); Deji's palace and Akure museum → Akure South; Idanre Hill →
Idanre; Owo palace, museum and Igogo → Owo; Araromi Obu → Odigbo (INEC ward 13-04); Igbo Olodumare → state only.
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_142_ondo_languages_peoples as P142
import batch_144_ondo_institutions as I144

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = I144.NEWS
SOURCES = {
    "NCMML": dict(H66.SOURCES["NCMML"], notes="Reused. Ondo: 53 Igbara Oke Petroglyphs, near Igbara Oke; 54 Iwo Eleru Cave, Near Owo; 55 The Old Palace of The Deji Of Akure."),
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Ondo: Historic 48 Oke-Idanre Cultural Landscape, Idanre; Natural 84 Ashuba Marble Stone, Araromi Obu; Wild Life/Forest Reserve 96 Igbo Olodumare – 'Forest of a Thousand Daemons'."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. Ondo: National Museum Akure (Opposite Post Office, Oba Adeside Road, Akure); National Museum Owo (Olowo's Palace, Owo)."),
    "WIHO": WS("Iho Eleru", "Rock shelter at Isarun, Ondo State, with Later Stone Age artifacts; first reported by J. Akeredolu in 1961; the name formerly written Iwo Eleru, now Iho Eleru, 'Cave of Ashes'; the skull about 13,000 years old."),
    "WISA": WS("Isarun", "Town in Ifedore; the Cave of Ashes found by the hunter Chief Obele in 1922; excavated by Thurstan Shaw's team in 1965; bones dated to about 11,200 years."),
    "WIDH": WS("Idanre Hill", "Oke Idanre, in the town of Idanre; cultural sites (Owa's palace, shrines, the Old Court, Agboogun footprint, Omi Apaara, burial grounds); nominated for the UNESCO shortlist; about 914 m above sea level; Ogun festival in October and Ije festival; Soyinka's 'Idanre and other Poems'."),
    "WPOW": WS("Palace of Olowo of Owo", "Said to be the largest palace in Africa; built under Olowo Irengenje in 1340, with about 1,000 rooms; 'pronounced a national monument by the Nigerian government in 2000'; the Action Group was formed there."),
    "WIGB": WS("Igbara-Oke", "Headquarters of Ifedore Local Government; a Yoruba town on a hilltop; a trading post between Benin and the Ilesa and Oyo kingdoms."),
    "WAKK": I144.SOURCES["WAKK"],
    "WIGOGO": I144.SOURCES["WIGOGO"],
    "WOLOWO": I144.SOURCES["WOLOWO"],
    "PG24": NEWS("Aiyedatiwa, Deji of Akure call for use of culture to promote unity, economic growth", "Peoples Gazette", "2024-10-06",
                 "https://gazettengr.com/aiyedatiwa-deji-of-akure-call-for-use-of-culture-to-promote-unity-economic-growth/",
                 "The annual Ulefunta festival: 'a yearly celebration of the Deji of Akureland's annual leave', a seclusion in which the Deji 'is expected to commune with the ancestors and pray for his people'."),
    "PG25B": I144.SOURCES["PG25B"],
    "INEC": P142.SOURCES["INEC"],
}
D = "is No. {n} on the National Commission for Museums and Monuments' list of declared national monuments"
P = "is No. {n} on the National Commission for Museums and Monuments' list of proposed national monuments, in the '{c}' category"
TEXT = {
 "iho": f"""Iho Eleru, the 'Cave of Ashes', is a rock shelter near Isarun in Ifedore LGA, and {D.format(n=54)} under its older name, Iwo Eleru Cave, which the list places 'near Owo'. According to Wikipedia, it holds Later Stone Age artifacts from the Late Pleistocene–Holocene transition, and a human skull found there is about 13,000 years old. Wikipedia's two accounts of its discovery differ: one says it was first reported by J. Akeredolu of the Department of Antiquities in 1961, the other that a hunter, Chief Obele, found it in 1922, and that Thurstan Shaw's team excavated it in 1965, dating bones to about 11,200 years.""",
 "petro": f"""The Igbara-Oke petroglyphs, rock engravings near the town of Igbara-Oke, the headquarters of Ifedore LGA, {D.format(n=53)}. The engravings themselves are not described in a source read.""",
 "deji_palace": f"""The Old Palace of the Deji of Akure, in Akure, {D.format(n=55)}. According to Wikipedia, tradition says the palace was built for Asodeboyede, the founder of the Akure dynasty, between the three main settlements of the time; it still stands and is dated to 1150 AD. The Deji is the ruler of the Akure Kingdom.""",
 "idanre": f"""Oke Idanre, the Idanre Hill, rises above the town of Idanre, and its cultural landscape {P.format(n=48, c='Historic')}. According to Wikipedia, the hill stands about 914 metres above sea level, and its cultural sites include the Owa's palace, shrines, the Old Court, a belfry, the Agboogun footprint, the 'thunder water' Omi Apaara and burial grounds; it has been put forward for the UNESCO World Heritage shortlist. The people of Idanre lived among its boulders for almost a thousand years, and the Ogun festival in October and the Ije festival reunite those now living in the lowlands with it. Wole Soyinka's 'Idanre and other Poems' refers to it.""",
 "ashuba": f"""The Ashuba marble stone, at Araromi Obu, {P.format(n=84, c='Natural')}. Araromi Obu is a ward of Odigbo LGA in INEC's directory. The stone is not described in a source read.""",
 "olodumare": f"""Igbo Olodumare, which the list calls the 'Forest of a Thousand Daemons', {P.format(n=96, c='Wild Life/Forest Reserve')}. Its location within Ondo State and its extent are not given in a source read.""",
 "owo_palace": """The Palace of the Olowo of Owo, in Owo, is described by Wikipedia as the largest palace in Africa, with about 1,000 rooms, some of them shrines, and as built under Olowo Irengenje in 1340 in its account. Wikipedia also says it was pronounced a national monument in 2000, but it is not on the National Commission for Museums and Monuments' declared list as read in October 2026. The Action Group party was formed there, and the palace houses the National Museum Owo.""",
 "museum_akure": """The National Museum Akure is one of the national museums of the National Commission for Museums and Monuments, opposite the post office on Oba Adesida Road, Akure. Its history and collections are not described in a source read.""",
 "museum_owo": """The National Museum Owo is one of the national museums of the National Commission for Museums and Monuments, housed at the Olowo's palace in Owo. Its history and collections are not described in a source read.""",
 "igogo": """The Igogo festival is held every September in Owo in honour of Queen Oronsen, in tradition a wife of Olowo Rerengejen who was secretly an orisha and who fled the palace when her taboos were broken (Wikipedia). During the festival the Olowo and his high chiefs dress as women, with coral beads, beaded gowns and plaited hair, and headgear, drumming and gunfire are forbidden. Tradition holds that it began more than 600 years ago.""",
 "ulefunta": """Ulefunta is the annual festival of the Deji of Akure's seclusion, which the Peoples Gazette describes as 'a yearly celebration of the Deji of Akureland's annual leave', during which the Deji is expected to commune with the ancestors and pray for his people. In 2025 the leave lasted seven days, and drumming was prohibited in Akure throughout it (Peoples Gazette). The festival has drawn the state governor and other rulers.""",
}
ST = "@admin_units:state:ondo"
LG = lambda l: f"@admin_units:lga:ondo/{l}"
REC = [
    ("iho", "places", dict(place_type="archaeological_site", name="Iho Eleru", slug="iho-eleru", admin_unit_id=LG("ifedore"), status="existing"), ["NCMML", "WIHO", "WISA"], "verified"),
    ("petro", "places", dict(place_type="archaeological_site", name="Igbara-Oke Petroglyphs", slug="igbara-oke-petroglyphs", admin_unit_id=LG("ifedore"), status="existing"), ["NCMML", "WIGB"], "verified"),
    ("deji_palace", "places", dict(place_type="monument", name="Old Palace of the Deji of Akure", slug="old-palace-deji-of-akure", admin_unit_id=LG("akure-south"), status="existing"), ["NCMML", "WAKK"], "verified"),
    ("idanre", "places", dict(place_type="heritage_site", name="Idanre Hill (Oke Idanre)", slug="idanre-hill", admin_unit_id=LG("idanre"), status="existing"), ["NCMMP", "WIDH"], "well_documented"),
    ("ashuba", "places", dict(place_type="natural_feature", name="Ashuba Marble Stone, Araromi Obu", slug="ashuba-marble-stone", admin_unit_id=LG("odigbo"), status="existing"), ["NCMMP", "INEC"], "verified"),
    ("olodumare", "places", dict(place_type="natural_feature", name="Igbo Olodumare", slug="igbo-olodumare", admin_unit_id=ST, status="existing"), ["NCMMP"], "verified"),
    ("owo_palace", "places", dict(place_type="historical_place", name="Palace of the Olowo of Owo", slug="palace-olowo-of-owo", admin_unit_id=LG("owo"), status="existing"), ["WPOW", "NCMMM"], "well_documented"),
    ("museum_akure", "places", dict(place_type="museum", name="National Museum Akure", slug="national-museum-akure", admin_unit_id=LG("akure-south"), status="existing"), ["NCMMM"], "verified"),
    ("museum_owo", "places", dict(place_type="museum", name="National Museum Owo", slug="national-museum-owo", admin_unit_id=LG("owo"), status="existing"), ["NCMMM"], "verified"),
    ("igogo", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Igogo Festival", slug="igogo-festival",
                                       timing="Annually in September", current_status="active", scope_level="community"), ["WIGOGO", "WOLOWO"], "well_documented"),
    ("ulefunta", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Ulefunta Festival", slug="ulefunta-festival",
                                          timing="Annually (September in 2025)", current_status="active", scope_level="community"), ["PG24", "PG25B"], "well_documented"),
]
RECORDS = []
for key, table, fields, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table=table, evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="deji_palace", type="associated_with", to="@polities:akure-kingdom", role="palace of the Deji", source="WAKK", evidence="multiple_sources", level="well_documented", notes="NCMM No. 55; Wikipedia (Akure Kingdom)."),
    dict(frm="owo_palace", type="associated_with", to="@polities:owo-kingdom", role="palace of the Olowo", source="WPOW", evidence="multiple_sources", level="well_documented", notes="Wikipedia; NCMM (the museum at the Olowo's palace)."),
    dict(frm="museum_owo", type="associated_with", to="owo_palace", role="national museum housed at the Olowo's palace", source="NCMMM", evidence="single_reliable_source", level="well_documented", notes="NCMM: 'Olowo's Palace, Owo'."),
    dict(frm="igogo", type="celebrated_by", to="@ethnic_groups:yoruba", source="WIGOGO", evidence="single_reliable_source", level="well_documented", notes="Wikipedia: 'a Yoruba festival held in Owo'."),
    dict(frm="igogo", type="celebrated_in", to=LG("owo"), source="WIGOGO", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Igogo festival)."),
    dict(frm="igogo", type="associated_with", to="@polities:owo-kingdom", role="the Olowo and his high chiefs lead the festival", source="WIGOGO", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Igogo festival); ThisDay (July 2026) on the Olowo."),
    dict(frm="ulefunta", type="celebrated_in", to=LG("akure-south"), source="PG24", evidence="multiple_sources", level="well_documented", notes="Peoples Gazette (2024, 2025): held in Akure."),
    dict(frm="ulefunta", type="associated_with", to="@polities:akure-kingdom", role="the Deji's annual seclusion", source="PG24", evidence="multiple_sources", level="well_documented", notes="Peoples Gazette (2024, 2025)."),
    dict(frm="ulefunta", type="celebrated_by", to="@ethnic_groups:yoruba", source="PG24", evidence="single_reliable_source", level="reported", notes="Peoples Gazette: 'one of the most respected cultural heritage of the Akure people'."),
]
NAMES = [
    dict(record="iho", name="Iwo Eleru", name_type="alternative", usage_notes="The NCMM's name (declared list, No. 54) and the older name; Wikipedia: 'formerly known as Iwo Eleeru … the correct name is now regarded as Ihò Eléérú'.", srcs=["NCMML"]),
    dict(record="iho", name="Ihò Eléérú", name_type="endonym", usage_notes="Yoruba name, 'Cave of Ashes' (Wikipedia).", srcs=["WIHO"]),
    dict(record="idanre", name="Oke-Idanre Cultural Landscape", name_type="official", usage_notes="The NCMM's name (proposed list, Historic No. 48).", srcs=["NCMMP"]),
    dict(record="olodumare", name="Forest of a Thousand Daemons", name_type="alternative", usage_notes="The NCMM's description (proposed list, No. 96).", srcs=["NCMMP"]),
]
GAPS = [
    ("Ondo: Iho Eleru's location and discovery", "The NCMM places the cave 'near Owo'; Wikipedia places it at Isarun, Ifedore. Wikipedia's two pages also give different discoveries (1961 report; 1922 hunter) and ages (about 13,000; about 11,200 years). All are kept; the place is recorded in Ifedore."),
    ("Ondo: the Owo palace's status", "Wikipedia says the Olowo's palace was pronounced a national monument in 2000, but it is not on the NCMM declared list read in October 2026."),
    ("Ondo: undescribed monuments", "The Igbara-Oke petroglyphs, the Ashuba marble stone and Igbo Olodumare are not described in a source read; Igbo Olodumare's location is not given (D. O. Fagunwa's novel of that name was not linked, as no source read connects them)."),
    ("Ondo: other festivals and sites", "The Ekimogun Day of Ondo, the Ogun and Ije festivals of Idanre, the Oka new-yam festival and the Oke-Maria grotto at Oka need their own sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Ondo culture and heritage: 3 declared and 3 proposed NCMM monuments, the Olowo's palace, 2 national museums, and the Igogo and Ulefunta festivals.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 145 — Ondo: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **9 places:**",
         "  - **3 declared national monuments:** **Iho Eleru** (the 'Iwo Eleru' cave, Later Stone Age, with a skull about 13,000 years old), the Igbara-Oke petroglyphs, and the **Old Palace of the Deji of Akure**",
         "  - **3 proposed:** the **Idanre Hill** cultural landscape, the Ashuba marble stone (Araromi Obu, Odigbo) and Igbo Olodumare",
         "  - the **Palace of the Olowo of Owo**, and the **national museums** at Akure and Owo",
         "- **2 festivals:** the **Igogo festival** of Owo (September), and **Ulefunta**, the Deji of Akure's annual seclusion",
         "- **Disagreements kept side by side:** for Iho Eleru, the NCMM says 'near Owo' and Wikipedia says Isarun (Ifedore); Wikipedia also gives two discovery stories and two ages. For the Owo palace, Wikipedia says it was declared a monument in 2000, but it is not on the NCMM list.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, table, fields, srcs, lvl in REC:
        L += [f"## {fields['name']} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_145_ondo_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_145_ondo_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
