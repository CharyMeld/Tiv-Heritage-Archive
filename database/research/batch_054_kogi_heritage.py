"""
Research batch 054 — Kogi (Phase 3): culture and heritage. Researched 2026-09-30. Pattern: batch 047.

Places: National Museum of Colonial History, Lokoja (NCMM); the Ojogwu Atogwu Tumulus near the Attah's
palace at Idah (declared national monument No. 38); four proposed national monuments in Lokoja
(Historic Nos. 32–35: the Union Jack first-raising memorial, the ruins of Lugard's first residence and
office, Holy Trinity Bishop Crowther Primary School, the graves of the deposed emirs at Kabawa); Mount Patti.
Festivals: Ovia-Osese (Ogori) and Owiya Osese (Magongo).

Evidence notes:
  * NCMM lists (read as raw HTML for batch 047; source #559 is the corrected declared list). Proposed-list
    numbers counted by hand within the page's category order and checked against Yakoko (66) and Keana (90).
  * Mount Patti: Wikipedia states both that Flora Shaw coined 'Nigeria' in 1914 looking out from the hill
    and that she proposed the name in The Times on 8 January 1897 — the archive records the hill, and the
    naming story as a local tradition, noting the 1897 essay; the 1914 date is not used.
  * Ovia-Osese: Wikipedia (maidens' initiation festival of the Ogori, from family rites of the 1870s) and
    Leadership (2026: annual, 12–18 April 2026, organised by the Ogori Descendants Union with the LGA and
    state and federal governments). Not used: the organisers' statement that it is 'recognised by UNESCO'
    (not verified against UNESCO's lists) — gap.
"""
import json, re, sys

W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "NCMML": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="List of National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/list-of-national-monuments/",
                  verification_status="verified", notes="Reused. Kogi: No. 38 'Ojogwu Atogwu Tumulus near the Palace of the Attah of Idah'."),
    "NCMMP": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="Proposed National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/proposed-national-monuments/",
                  verification_status="verified", notes="Reused. Kogi (Historic): No. 32 British flag (Union Jack) first raising memorial, Lokoja; No. 33 Ruins of Lord Lugard's first residence/office, Lokoja; No. 34 Holy Trinity Bishop Crowther Primary School (first primary school in Northern Nigeria); No. 35 Graves of the deposed emirs, Kabawa, Lokoja."),
    "NCMMM": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="National Museums",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/museums/national-museums/",
                  verification_status="verified", notes="Reused. National Museum of Colonial History, Lokoja (P.M.B. 1022, Lokoja)."),
    "WPATTI": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Mount Patti", organisation="Wikipedia", url=W("Mount Patti"),
                   verification_status="needs_corroboration",
                   notes="A 458 m hill and tourist attraction in Lokoja, 6 km from the Niger–Benue confluence; associated with Flora Shaw and the name 'Nigeria'; states both 1914 and her essay in The Times of 8 January 1897."),
    "WOVIA": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Ovia-Osese Festival", organisation="Wikipedia", url=W("Ovia-Osese Festival"),
                  verification_status="needs_corroboration",
                  notes="Annual festival of the Ogori (Ogori/Magongo LGA) initiating girls of at least 15 into adulthood, only for girls who have not been sexually active; grew from family rites of the 1870s; girls (ibusuke) and maidens (ivia) dance in beads; cooking competition, sports, quiz and pageant."),
    "LEAD26O": dict(source_type="news", source_kind="news", source_tier=3, title="Kogi: Ogori People Set For 2026 Ovia Osese Festival", organisation="Leadership", author="Ibrahim Obansa",
                    publication_date="2026-03-17", url="https://leadership.ng/kogi-ogori-people-set-for-2026-ovia-osese-festival/", verification_status="needs_corroboration",
                    notes="Annual Ovia Osese Cultural Festival of the Ogori; 2026 edition 12–18 April; organised by the Ogori Descendants Union with Ogori-Magongo LGA and the state and federal governments. Organisers call it 'recognised by UNESCO' (not verified)."),
    "WOGM": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Ogori/Magongo", organisation="Wikipedia", url=W("Ogori/Magongo"),
                 verification_status="needs_corroboration", notes="Reused. Ovia Osese (Ogori) two weeks after Easter; Owiya Osese (Magongo) four weeks after Easter."),
}
TEXT = {
 "museum": """The National Museum of Colonial History, Lokoja, is one of the national museums of the National Commission for Museums and Monuments (NCMM), which gives its address as P.M.B. 1022, Lokoja. Three of the NCMM's proposed national monuments are in Lokoja, at the confluence of the Niger and Benue. The museum's history and collections are not described in a readable source.""",
 "tumulus": """The Ojogwu Atogwu Tumulus, near the palace of the Attah of Idah, is No. 38 on the National Commission for Museums and Monuments' list of 65 declared national monuments, and the only declared national monument in Kogi State. Idah is the capital of the Igala Kingdom and the seat of the Attah Igala. The tumulus's age and history are not described in a readable source.""",
 "flag": """The British flag (Union Jack) first raising memorial in Lokoja is No. 32 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Its date and form are not described in a readable source.""",
 "lugard": """The ruins of Lord Lugard's first residence and office in Lokoja are No. 33 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Their date and condition are not described in a readable source.""",
 "school": """Holy Trinity Bishop Crowther Primary School in Kogi State, described by the National Commission for Museums and Monuments as the first primary school in Northern Nigeria, is No. 34 on its list of proposed national monuments, in the 'Historic' category. Its founding date is not given in the list.""",
 "graves": """The graves of the deposed emirs at Kabawa, Lokoja, are No. 35 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Which emirs are buried there is not stated in the list.""",
 "patti": """Mount Patti is a hill of about 458 metres in Lokoja, some 6 km from the confluence of the Niger and Benue, and a tourist attraction (Wikipedia). It is associated with Flora Shaw, later Lady Lugard, and the naming of Nigeria: local tradition holds that she coined the name while looking out from the hill, while Wikipedia also records that she proposed the name 'Nigeria' in an essay in The Times of 8 January 1897.""",
 "ovia": """Ovia-Osese is the annual festival of the Ogori of Ogori/Magongo LGA, in which girls of at least fifteen who have not been sexually active are initiated into adulthood (Wikipedia). According to Wikipedia it grew from the initiation of daughters within each family in the 1870s into a community festival; the girls (ibusuke) and maidens (ivia) perform traditional dances wearing beads, and the festival also includes a cooking competition, sports, a quiz and a pageant. Wikipedia's article on Ogori/Magongo places it two weeks after Easter. The 2026 edition was held from 12 to 18 April, organised by the Ogori Descendants Union with the LGA and the state and federal governments (Leadership, 2026).""",
 "owiya": """Owiya Osese is the festival of Magongo, in Ogori/Magongo LGA, held four weeks after Easter Sunday, according to Wikipedia; the Ovia Osese of neighbouring Ogori is held two weeks after Easter. Its rites are not described in a readable source.""",
}
K = lambda l: f"@admin_units:lga:kogi/{l}"
REC = [
    ("museum", "places", dict(place_type="museum", name="National Museum of Colonial History, Lokoja", slug="national-museum-of-colonial-history-lokoja", admin_unit_id=K("lokoja"), status="existing"), ["NCMMM", "NCMMP"], "well_documented"),
    ("tumulus", "places", dict(place_type="monument", name="Ojogwu Atogwu Tumulus", slug="ojogwu-atogwu-tumulus", admin_unit_id=K("idah"), status="existing"), ["NCMML"], "verified"),
    ("flag", "places", dict(place_type="historical_place", name="Union Jack First Raising Memorial, Lokoja", slug="union-jack-first-raising-memorial-lokoja", admin_unit_id=K("lokoja"), status="unknown"), ["NCMMP"], "verified"),
    ("lugard", "places", dict(place_type="historical_place", name="Ruins of Lord Lugard's First Residence, Lokoja", slug="ruins-of-lugards-first-residence-lokoja", admin_unit_id=K("lokoja"), status="unknown"), ["NCMMP"], "verified"),
    ("school", "places", dict(place_type="historical_place", name="Holy Trinity Bishop Crowther Primary School", slug="holy-trinity-bishop-crowther-primary-school", admin_unit_id="@admin_units:state:kogi", status="unknown"), ["NCMMP"], "verified"),
    ("graves", "places", dict(place_type="historical_place", name="Graves of the Deposed Emirs, Kabawa", slug="graves-of-the-deposed-emirs-kabawa", admin_unit_id=K("lokoja"), status="unknown"), ["NCMMP"], "verified"),
    ("patti", "places", dict(place_type="hill_or_mountain", name="Mount Patti", slug="mount-patti", admin_unit_id=K("lokoja"), status="existing"), ["WPATTI"], "reported"),
    ("ovia", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Ovia-Osese", local_name="Ovia Osese",
                                      slug="ovia-osese", timing="Annually, two weeks after Easter (12–18 April in 2026)", current_status="active", scope_level="community"), ["WOVIA", "LEAD26O", "WOGM"], "well_documented"),
    ("owiya", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Owiya Osese", local_name="Owiya Osese",
                                       slug="owiya-osese", timing="Annually, four weeks after Easter Sunday", current_status="unknown", scope_level="community"), ["WOGM"], "reported"),
]
RECORDS = []
for key, table, fields, srcs, lvl in REC:
    t = TEXT[key]
    f = dict(fields, summary=t.split(". ")[0] + ".", description=t)
    RECORDS.append(dict(key=key, table=table, evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl, fields=f, srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="tumulus", type="associated_with", to="@polities:attah-igala", role="near the Attah's palace", source="NCMML", evidence="single_reliable_source", level="verified",
         notes="'near the Palace of the Attah of Idah' (NCMM)."),
    dict(frm="ovia", type="celebrated_by", to="@ethnic_groups:ogori-magongo", source="WOVIA", evidence="multiple_sources", level="well_documented", notes="The Ogori (Wikipedia; Leadership)."),
    dict(frm="ovia", type="celebrated_in", to=K("ogori-magongo"), source="WOVIA", evidence="multiple_sources", level="well_documented", notes="Ogori, Ogori/Magongo LGA."),
    dict(frm="owiya", type="celebrated_by", to="@ethnic_groups:ogori-magongo", source="WOGM", evidence="single_reliable_source", level="reported", notes="Magongo (Wikipedia)."),
    dict(frm="owiya", type="celebrated_in", to=K("ogori-magongo"), source="WOGM", evidence="single_reliable_source", level="reported", notes="Magongo, Ogori/Magongo LGA."),
    dict(frm="@polities:olu-of-magongo", type="associated_with", to="owiya", role="ruler of Magongo", source="WOGM", evidence="single_reliable_source", level="reported",
         notes="The festival of Magongo; the Olu's role in it is not described."),
]
NAMES = [dict(record="ovia", name="Ovia Osese Cultural Festival", name_type="official", usage_notes="Leadership (2026).", srcs=["LEAD26O"]),
         dict(record="patti", name="Patti Hill", name_type="alternative", usage_notes="Wikipedia ('The Mount Patti Hill').", srcs=["WPATTI"])]
GAPS = [
    ("Kogi: Ovia-Osese and UNESCO", "The organisers (Leadership, 2026) describe Ovia-Osese as 'recognised by UNESCO'; not verified against UNESCO's lists and not recorded."),
    ("Kogi: monuments in Lokoja and Idah", "The NCMM names the tumulus and the four proposed monuments but gives no dates or descriptions; which emirs are buried at Kabawa and when Holy Trinity school was founded need a source."),
    ("Kogi: Mount Patti and the name 'Nigeria'", "Wikipedia gives both 1914 (on the hill) and 8 January 1897 (Flora Shaw's essay in The Times); the local tradition is recorded, the 1914 date is not."),
    ("Kogi: other festivals", "Igala (Ocho, Ibegwu), Ebira (Ekuechi, Echane), Okun and Bassa-Nge festivals: no reliable source read."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kogi culture and heritage: Lokoja museum, Idah tumulus (declared No. 38), four proposed monuments, Mount Patti, Ovia-Osese, Owiya Osese.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 054 — Kogi: culture and heritage", "",
         "Researched 2026-09-30. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **7 places:**",
         "  - the **National Museum of Colonial History, Lokoja**",
         "  - the **Ojogwu Atogwu Tumulus** at Idah: Kogi's only *declared* national monument (No. 38)",
         "  - **four proposed national monuments** (Nos. 32–35): in Lokoja, the Union Jack first-raising memorial, the ruins of Lugard's first residence and the graves of the deposed emirs at Kabawa; and Holy Trinity Bishop Crowther Primary School, the first primary school in Northern Nigeria, which the NCMM places only in 'Kogi State'",
         "  - **Mount Patti**",
         "- **2 festivals:** **Ovia-Osese** (Ogori; the maidens' festival) and **Owiya Osese** (Magongo).",
         "- **Links:**",
         "  - the tumulus to the Attah Igala, since the NCMM places it near the Attah's palace",
         "  - both festivals to the Ogori–Magongo people and their LGA",
         "  - Owiya Osese to the Olu of Magongo",
         "- **Handled with care:**",
         "  - Mount Patti's 'naming of Nigeria' is recorded as a tradition, alongside Flora Shaw's 1897 essay. Wikipedia's 1914 date is not used.",
         "  - The claim that Ovia-Osese is 'recognised by UNESCO' is not recorded, because it has not been checked against UNESCO's lists.",
         "- All records are short, so they are noindex and the sitemap is unchanged.", ""]
    for key, table, fields, srcs, lvl in REC:
        t = TEXT[key]
        L += [f"## {fields['name']} ({words(t)} words)", "", f"> {t}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_054_kogi_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_054_kogi_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
