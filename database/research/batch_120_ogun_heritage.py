"""
Research batch 120 — Ogun (Phase 3): culture and heritage. Researched 2026-10-02. Pattern: batch 114 (Lagos).

NCMM declared list: No. 52 'Sungbo's shrine at Oke-Eri, near Ijebu-Ode'. Wikipedia 'Ogun State': 'Bilikisu Sungbo Shrine,
Oke-Eiri, near Ijebu-Ode … declared a part of the national heritage in 1964', believed by the Ijebu to be the burial
place of the Queen of Sheba; a place of pilgrimage. NCMM proposed list (calibrated count; King's College 39): Historic 37
Centenary Hall, Abeokuta; 40 Olumo Rock, Abeokuta. NCMM museums: National Museum Abeokuta, Baptist Girls' College
compound, Idi-Aba, Abeokuta.
Wikipedia: 'Sungbo's Eredo' (walls and ditches over 160 km round the Ijebu heartland, south-west of Ijebu-Ode; on
Nigeria's UNESCO tentative list; dates disputed — 10th–11th century (Darling) or late 14th–early 15th; Bilikisu Sungbo
legend; grave believed at Oke-Eiri); 'Olumo Rock' (natural fortress of the Egba in the 19th-century wars; 137 m; stone
tools; Abeokuta 'under the rock'); 'Abeokuta' (Ake, the Alake's residence, and Centenary Hall (1930), in the Egba Alake's
territory); 'Ojude Oba festival' (Ijebu-Ode, the third day after Eid al-Kabir, homage to the Awujale); 'Agemo festival'
(Ijebu masquerade festival, June–August, timing set by the Awujale and the 16 Agemo heads; shrine at Imosan).
Placement: the sites are placed at state level, as no source names their LGA.
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_117_ogun_languages_peoples as P117
import batch_119_ogun_institutions as I119

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMML": dict(H66.SOURCES["NCMML"], notes="Reused. Ogun: No. 52 Sungbo's shrine at Oke-Eri, near Ijebu-Ode."),
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Ogun: Historic 37 Centenary Hall, Abeokuta; 40 Olumo Rock, Abeokuta."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. National Museum Abeokuta, inside the Baptist Girls College compound, Idi-Aba, Abeokuta, P.M.B. 2004."),
    "WOGS": dict(P117.SOURCES["WOGS"], notes="Reused. 'Bilikisu Sungbo Shrine, Oke-Eiri, near Ijebu-Ode. It was declared a part of the national heritage in 1964, and is believed by the Ijebus to be the burial place of the fabled Queen of Sheba'; a place of pilgrimage."),
    "WSUN": WS("Sungbo's Eredo", "Walls and ditches over 160 km long round the Ijebu heartland, south-west of Ijebu Ode; on Nigeria's UNESCO tentative list; dating disputed (10th–11th century per Darling; late 14th–early 15th century per later excavations); legend of Bilikisu Sungbo, whose grave is believed to be at Oke-Eiri; annual pilgrimage."),
    "WOLU": WS("Olumo Rock", "In Abeokuta; a natural fortress of the Egba during the 19th-century wars; its patron spirit venerated as an orisha; stone tools; 137 m above sea level; one of Nigeria's most popular tourist sites."),
    "WABK": WS("Abeokuta", "Ake, the traditional residence of the Alake, and Centenary Hall (1930) are in the Egba Alake's territory; remnants of the 29 km city wall."),
    "WOJO": WS("Ojude Oba festival", "Annual festival of Ijebu-Ode on the third day after Eid al-Kabir (Ileya), paying homage to the Awujale; parades of the regberegbe age groups at the palace forecourt."),
    "WAGE": WS("Agemo festival", "Masquerade festival linked with the Ijebu; honours the deity Agemo, protector of children; June–August; timing set by the Awujale and the heads of the 16 Agemo (the Olofas); pilgrimage to the shrine at Imosan via Ijebu-Ode."),
    "WIJK": I119.SOURCES["WIJK"],
}
TEXT = {
 "shrine": """Sungbo's shrine at Oke-Eri (Oke-Eiri), near Ijebu-Ode, is No. 52 on the National Commission for Museums and Monuments' list of declared national monuments; Wikipedia says it was declared part of the national heritage in 1964. It is held to be the grave of Bilikisu Sungbo, the noblewoman of Ijebu legend in whose honour Sungbo's Eredo was built, and some Ijebu believe her to have been the Queen of Sheba. It is a place of annual pilgrimage for Yoruba traditionalists, Muslims and Christians alike (Wikipedia).""",
 "eredo": """Sungbo's Eredo is a system of defensive walls and ditches, more than 160 km long, that rings the heartland of the old Ijebu Kingdom south-west of Ijebu-Ode, and is on Nigeria's tentative list for UNESCO World Heritage status (Wikipedia). Its laterite ditch is in places 20 metres deep from bank to bottom. Its date is disputed: the archaeologist Patrick Darling placed it in the 10th and 11th centuries, while later excavations point to the late 14th and early 15th centuries. Ijebu legend ties it to the wealthy widow Bilikisu Sungbo, whose grave is believed to be at Oke-Eiri.""",
 "olumo": """Olumo Rock, in Abeokuta, is No. 40 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. According to Wikipedia, it served the Egba as a natural fortress and lookout during the wars of the 19th century, and Abeokuta, whose name means 'under the rock', grew from the settlers who sheltered there. Its patron spirit is venerated as an orisha, and stone tools have been found at the site. It rises to 137 metres above sea level and is one of Nigeria's most visited tourist sites.""",
 "centenary": """Centenary Hall, in Abeokuta, is No. 37 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Wikipedia dates it to 1930 and places it, with Ake, the traditional residence of the Alake, in the Egba Alake's part of the city. Its history and present use are not otherwise described in a source read.""",
 "museum": """The National Museum Abeokuta is one of the national museums of the National Commission for Museums and Monuments, inside the Baptist Girls' College compound at Idi-Aba, Abeokuta. Its history and collections are not described in a source read.""",
 "ojude": """Ojude Oba, 'the King's forecourt', is an annual festival of Ijebu-Ode, held on the third day after Eid al-Kabir (Ileya), when the people pay homage to the Awujale of Ijebuland (Wikipedia). Age-grade groups called regberegbe, made up of indigenes and their friends from far and near, parade in the forecourt of the palace, many on horseback. Wikipedia traces its origins to the open practice of Islam in Ijebu-Ode from the reign of Awujale Ademuyewo Afidipotemole in 1878. It draws very large crowds from Nigeria and abroad.""",
 "agemo": """The Agemo festival is a masquerade festival most closely linked with the Ijebu, honouring the deity Agemo, believed to protect children (Wikipedia). It is held between June and August, once linked to the maize harvest, and lasts seven days; its timing is fixed after a meeting of the Awujale with the heads of the sixteen titled Agemo, the Olofas. The Agemo masquerades travel from their villages to the shrine at Imosan, by way of Ijebu-Ode, and women may not see the procession; movement is restricted during parts of the festival.""",
}
ST = "@admin_units:state:ogun"
REC = [
    ("shrine", "places", dict(place_type="sacred_site", name="Sungbo's Shrine, Oke-Eri", slug="sungbos-shrine-oke-eri", admin_unit_id=ST, status="existing"), ["NCMML", "WOGS", "WSUN"], "well_documented"),
    ("eredo", "places", dict(place_type="archaeological_site", name="Sungbo's Eredo", slug="sungbos-eredo", admin_unit_id=ST, status="existing"), ["WSUN", "WIJK"], "well_documented"),
    ("olumo", "places", dict(place_type="natural_feature", name="Olumo Rock", slug="olumo-rock", admin_unit_id=ST, status="existing"), ["NCMMP", "WOLU"], "well_documented"),
    ("centenary", "places", dict(place_type="historical_place", name="Centenary Hall, Abeokuta", slug="centenary-hall-abeokuta", admin_unit_id=ST, status="existing"), ["NCMMP", "WABK"], "well_documented"),
    ("museum", "places", dict(place_type="museum", name="National Museum Abeokuta", slug="national-museum-abeokuta", admin_unit_id=ST, status="existing"), ["NCMMM"], "verified"),
    ("ojude", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Ojude Oba Festival", slug="ojude-oba-festival",
                                       timing="The third day after Eid al-Kabir (Ileya)", current_status="active", scope_level="community"), ["WOJO"], "well_documented"),
    ("agemo", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Agemo Festival", slug="agemo-festival",
                                       timing="Seven days between June and August, fixed by the Awujale and the Agemo heads", current_status="active", scope_level="subgroup"), ["WAGE"], "well_documented"),
]
RECORDS = []
for key, table, fields, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table=table, evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="eredo", type="associated_with", to="@polities:ijebu-kingdom", role="earthworks round the Ijebu heartland", source="WSUN", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Sungbo's Eredo; Ijebu Kingdom)."),
    dict(frm="shrine", type="associated_with", to="@polities:ijebu-kingdom", role="grave of Bilikisu Sungbo of Ijebu legend", source="WSUN", evidence="multiple_sources", level="reported", notes="Wikipedia (Sungbo's Eredo; Ogun State); NCMM No. 52."),
    dict(frm="olumo", type="associated_with", to="@polities:egbaland", role="the Egba refuge and fortress from which Abeokuta grew", source="WOLU", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Olumo Rock)."),
    dict(frm="centenary", type="associated_with", to="@polities:egbaland", role="in the Egba Alake's part of Abeokuta", source="WABK", evidence="single_reliable_source", level="reported", notes="Wikipedia (Abeokuta)."),
    dict(frm="ojude", type="celebrated_by", to="@ethnic_groups:yoruba", source="WOJO", evidence="single_reliable_source", level="well_documented", notes="Wikipedia: 'celebrated by the Yoruba people of Ijebu-Ode'."),
    dict(frm="ojude", type="celebrated_in", to="@admin_units:lga:ogun/ijebu-ode", source="WOJO", evidence="single_reliable_source", level="well_documented", notes="Wikipedia: held at the forecourt of the Awujale's palace in Ijebu-Ode."),
    dict(frm="@polities:ijebu-kingdom", type="associated_with", to="ojude", role="the festival pays homage to the Awujale", source="WOJO", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Ojude Oba festival)."),
    dict(frm="agemo", type="celebrated_by", to="@ethnic_groups:yoruba", source="WAGE", evidence="single_reliable_source", level="well_documented", notes="Wikipedia: held in many Yoruba cities, most closely linked with the Ijebu."),
    dict(frm="agemo", type="celebrated_in", to=ST, source="WAGE", evidence="single_reliable_source", level="well_documented", notes="Wikipedia: the Ijebu people of Ogun State; pilgrimage to Imosan via Ijebu-Ode."),
    dict(frm="@polities:ijebu-kingdom", type="associated_with", to="agemo", role="the Awujale fixes the festival's timing with the Agemo heads", source="WAGE", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Agemo festival)."),
]
NAMES = [
    dict(record="shrine", name="Bilikisu Sungbo Shrine", name_type="alternative", usage_notes="Wikipedia (Ogun State).", srcs=["WOGS"]),
    dict(record="ojude", name="King's Forecourt", name_type="alternative", usage_notes="English meaning of 'Ojude Oba' (Wikipedia).", srcs=["WOJO"]),
]
GAPS = [
    ("Ogun: LGAs of the heritage sites", "Oke-Eri, Sungbo's Eredo, Olumo Rock, Centenary Hall and the National Museum Abeokuta are placed by town only; their LGAs are not given in a source read, so they are at state level."),
    ("Ogun: Centenary Hall and the National Museum Abeokuta", "Neither is described in more than a line in a source read."),
    ("Ogun: other festivals and crafts", "The Lisabi festival (Egba), adire cloth of Abeokuta, the Oro festival and Ake Palace have no usable article; sources needed."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Ogun culture and heritage: Sungbo's shrine (NCMM declared), Sungbo's Eredo, Olumo Rock and Centenary Hall (NCMM proposed), the National Museum Abeokuta, and the Ojude Oba and Agemo festivals.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 120 — Ogun: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **5 places:**",
         "  - **Sungbo's Shrine** at Oke-Eri (declared national monument No. 52)",
         "  - **Sungbo's Eredo**, the great Ijebu earthworks on Nigeria's UNESCO tentative list. Its dates are disputed, and both views are given.",
         "  - **Olumo Rock** (proposed No. 40) and **Centenary Hall** (proposed No. 37), both in Abeokuta",
         "  - the **National Museum Abeokuta**",
         "- **2 festivals:** **Ojude Oba** (Ijebu-Ode, homage to the Awujale) and **Agemo** (the Ijebu masquerade festival). Both are linked to the Ijebu Kingdom.",
         "- **Placement:** all sites are placed at state level, because no source names their LGA.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, table, fields, srcs, lvl in REC:
        L += [f"## {fields['name']} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_120_ogun_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_120_ogun_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
