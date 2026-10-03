"""
Research batch 096 — Jigawa (Phase 3): culture and heritage. Researched 2026-10-02. Pattern: batches 078, 084, 090.

NCMM declared national monuments (Jigawa: Nos. 24–27, all at Birnin Kudu): rock paintings at Dutsen Habude; rock
paintings at Dutsen Murufu; rock inscriptions, gong and shelter at Dutsen Mesa; rock paintings at Dutsen Zango.
NCMM proposed: No. 31 'Birnin Kudu old settlement, off Kano–Maiduguri Road' ('Historic'; hand count, see batch 090).
NCMM museums: National Museum Birni-Kudu, Kano–Bauchi Road, Birnin Kudu (Wikipedia's copy of the NCMM list: 'Rock Art
Interpretive Centre, Birnin-Kudu').
Wikipedia: Birnin Kudu (Fagg 1955: cave paintings of long-horned and short-horned humpless cattle, at least 2,000
years old; rock gongs in ceremonial use since at least AD 940; chiefdom founded in the 10th century; walls over three
miles long with twelve gates); Rock gong (first recorded at Birnin Kudu, June 1955); Jigawa State (Dutsen Habude
paintings dated to the Neolithic); Baturiya Wetland (Ramsar Site, 30 April 2008, about 101,095 ha; Kirikasamma, Auyo and
Guri LGAs; game reserve 1985; part of the Hadejia-Nguru wetlands).
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_078_bauchi_heritage as H78
import batch_093b_jigawa_peoples as P93

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMML": dict(H66.SOURCES["NCMML"], notes="Reused. Jigawa: No. 24 rock paintings at Dutsen Habude, Birnin Kudu; No. 25 rock paintings at Dutsen Murufu; No. 26 rock inscriptions, gong and shelter at Dutsen Mesa; No. 27 rock paintings at Dutsen Zango."),
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Jigawa: No. 31 'Birnin Kudu old settlement, off Kano-Maiduguri Road'."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. National Museum Birni-Kudu, Kano-Bauchi Road, Birnin Kudu."),
    "WNCMM": dict(H78.SOURCES["WNCMM"], notes="Reused. Lists 'Rock Art Interpretive Centre, Birnin- Kudu', Kano-Bauchi Road, Birnin Kudu, among the NCMM museums."),
    "WBKU": WS("Birnin Kudu", "Fagg (1955): cave paintings of long-horned and short-horned humpless cattle, at least 2,000 years old; rock gongs in ceremonial use since at least AD 940 (town of Baud'a); chiefdom founded in the 10th century; walls over three miles long with twelve gates; absorbed by Kano in the 18th century."),
    "WGONG": WS("Rock gong", "First recorded discovery of rock gongs at Birnin Kudu, June 1955 (Bernard Fagg); association of gongs with cave paintings."),
    "WJIG": P93.SOURCES["WJIG"],
    "WBAT": WS("Baturiya Wetland", "Ramsar Site designated 30 April 2008, about 101,095 ha; part of the Hadejia-Nguru wetlands, west of Hadejia, fed by the Kafin Hausa River; extends across parts of Kirikasamma, Auyo and Guri LGAs; forest reserve, then game reserve from 1985."),
}
FAGG = "In 1955 the archaeologist Bernard Fagg reported cave paintings at Birnin Kudu of long-horned and short-horned humpless cattle, which he dated to at least 2,000 years ago, and the first recorded rock gongs, believed to have been in ceremonial use since at least AD 940 (Wikipedia)."
TEXT = {
 "habude": """The rock paintings at Dutsen Habude, Birnin Kudu, are No. 24 on the National Commission for Museums and Monuments' list of declared national monuments, the first of four declared rock-art sites at Birnin Kudu. Wikipedia's article on Jigawa State calls the Dutsen Habude cave paintings the state's most famous, dating them to the Neolithic. """ + FAGG,
 "murufu": """The rock paintings at Dutsen Murufu, Birnin Kudu, are No. 25 on the National Commission for Museums and Monuments' list of declared national monuments. """ + FAGG + """ The Dutsen Murufu site itself is not described separately in a readable source.""",
 "mesa": """The rock inscriptions, gong and shelter at Dutsen Mesa, Birnin Kudu, are No. 26 on the National Commission for Museums and Monuments' list of declared national monuments. Rock gongs, slabs of rock struck like drums, were first recorded at Birnin Kudu in June 1955 by Bernard Fagg, who noted that their closeness to cave paintings 'leaves little doubt that they are associated in some way' (Wikipedia, 'Rock gong'). The Dutsen Mesa site itself is not described separately in a readable source.""",
 "zango": """The rock paintings at Dutsen Zango, Birnin Kudu, are No. 27 on the National Commission for Museums and Monuments' list of declared national monuments. """ + FAGG + """ The Dutsen Zango site itself is not described separately in a readable source.""",
 "oldsettlement": """The old settlement of Birnin Kudu, off the Kano–Maiduguri road, is No. 31 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. According to Wikipedia, Birnin Kudu was founded as a chiefdom in the 10th century, some decades before Kano, and its capital was surrounded by walls over three miles long with twelve fortified gates; it was conquered by Kano in the 18th century. Rock gongs found there are believed to have been in ceremonial use since at least AD 940, at the town of Baud'a, which preceded Birnin Kudu (Wikipedia).""",
 "museum": """The National Museum Birnin Kudu, on the Kano–Bauchi road, is one of the national museums of the National Commission for Museums and Monuments (NCMM); Wikipedia's copy of the commission's list calls it the Rock Art Interpretive Centre, Birnin Kudu. It stands in the town known for the rock paintings and rock gongs recorded by Bernard Fagg in 1955. Its collections are not described in a readable source.""",
 "baturiya": """Baturiya Wetland is a Ramsar Site, a wetland of international importance, designated on 30 April 2008 and covering about 101,095 hectares in Jigawa State (Wikipedia). It is part of the Hadejia-Nguru wetlands, lying west of Hadejia on the floodplain of the Hadejia River system, and spreads across parts of Kiri Kasama, Auyo and Guri LGAs. Once a forest reserve, it was made a game reserve in 1985, and it is known for its resident and migratory waterbirds, fisheries and floodplain farming (Wikipedia).""",
}
J = lambda l: f"@admin_units:lga:jigawa/{l}"
REC = [
    ("habude", dict(place_type="archaeological_site", name="Dutsen Habude Rock Paintings", slug="dutsen-habude-rock-paintings", admin_unit_id=J("birnin-kudu"), status="existing"), ["NCMML", "WJIG", "WBKU"], "well_documented"),
    ("murufu", dict(place_type="archaeological_site", name="Dutsen Murufu Rock Paintings", slug="dutsen-murufu-rock-paintings", admin_unit_id=J("birnin-kudu"), status="existing"), ["NCMML", "WBKU"], "verified"),
    ("mesa", dict(place_type="archaeological_site", name="Dutsen Mesa Rock Inscriptions, Gong and Shelter", slug="dutsen-mesa-rock-inscriptions-gong-and-shelter", admin_unit_id=J("birnin-kudu"), status="existing"), ["NCMML", "WGONG"], "verified"),
    ("zango", dict(place_type="archaeological_site", name="Dutsen Zango Rock Paintings", slug="dutsen-zango-rock-paintings", admin_unit_id=J("birnin-kudu"), status="existing"), ["NCMML", "WBKU"], "verified"),
    ("oldsettlement", dict(place_type="historical_place", name="Birnin Kudu Old Settlement", slug="birnin-kudu-old-settlement", admin_unit_id=J("birnin-kudu"), status="historical"), ["NCMMP", "WBKU"], "well_documented"),
    ("museum", dict(place_type="museum", name="National Museum Birnin Kudu", slug="national-museum-birnin-kudu", admin_unit_id=J("birnin-kudu"), status="existing"), ["NCMMM", "WNCMM"], "verified"),
    ("baturiya", dict(place_type="natural_feature", name="Baturiya Wetland", slug="baturiya-wetland", admin_unit_id="@admin_units:state:jigawa", status="existing"), ["WBAT"], "reported"),
]
RECORDS = []
for key, fields, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [dict(frm="baturiya", type="located_in", to=J(l), source="WBAT", evidence="single_reliable_source", level="reported",
                  notes="Wikipedia (Baturiya Wetland): the wetland extends across parts of Kirikasamma, Auyo and Guri LGAs.") for l in ["kiri-kasama", "auyo", "guri"]]
RELATIONS += [
    dict(frm="baturiya", type="part_of", to="@places:hadejia-nguru-wetlands", source="WBAT", evidence="single_reliable_source", level="reported", notes="Wikipedia: part of the larger Hadejia-Nguru wetlands complex."),
    dict(frm="museum", type="associated_with", to="habude", role="rock-art interpretive centre for the Birnin Kudu sites", source="WNCMM", evidence="single_reliable_source", level="reported",
         notes="Wikipedia's copy of the NCMM museum list calls it the Rock Art Interpretive Centre, Birnin Kudu."),
]
for k in ("murufu", "mesa", "zango"):
    RELATIONS.append(dict(frm=k, type="associated_with", to="oldsettlement", role="rock site of Birnin Kudu", source="NCMML", evidence="single_reliable_source", level="verified",
                          notes="NCMM lists it at Birnin Kudu."))
RELATIONS.append(dict(frm="habude", type="associated_with", to="oldsettlement", role="rock site of Birnin Kudu", source="NCMML", evidence="single_reliable_source", level="verified", notes="NCMM lists it at Birnin Kudu."))
NAMES = [
    dict(record="museum", name="Rock Art Interpretive Centre, Birnin Kudu", name_type="alternative", usage_notes="Name in Wikipedia's copy of the NCMM museum list.", srcs=["WNCMM"]),
    dict(record="museum", name="National Museum Birni-Kudu", name_type="spelling_variant", usage_notes="NCMM spelling.", srcs=["NCMMM"]),
]
GAPS = [
    ("Jigawa: the Birnin Kudu rock sites", "The four declared sites are named by the NCMM without separate descriptions; Fagg's 1955 report and later studies would give each site's paintings and dates."),
    ("Jigawa: festivals", "No Jigawa festival was found in the sources read (the Durbar is held in the emirates, but no Jigawa-specific source was read)."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Jigawa culture and heritage: the four declared Birnin Kudu rock-art monuments, the proposed old settlement, the National Museum Birnin Kudu and Baturiya Wetland.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 096 — Jigawa: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **7 places:**",
         "  - **Jigawa's four declared national monuments** (NCMM Nos. 24–27), all rock-art sites at Birnin Kudu: Dutsen Habude, Dutsen Murufu, Dutsen Mesa (inscriptions, a rock gong and a shelter) and Dutsen Zango. Bernard Fagg reported the cattle paintings and the first known rock gongs there in 1955.",
         "  - the **Birnin Kudu old settlement** (proposed No. 31), a 10th-century walled chiefdom",
         "  - the **National Museum Birnin Kudu**, also called the Rock Art Interpretive Centre",
         "  - **Baturiya Wetland**, a Ramsar Site since 2008 and part of the Hadejia-Nguru wetlands",
         "- **Links:**",
         "  - The rock sites are tied to the old settlement, and the museum to the sites.",
         "  - Baturiya is linked to Kiri Kasama, Auyo and Guri, and to the Hadejia-Nguru wetlands (batch 072).",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, fields, srcs, lvl in REC:
        L += [f"## {fields['name']} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_096_jigawa_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_096_jigawa_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
