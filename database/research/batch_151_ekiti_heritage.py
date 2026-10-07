"""
Research batch 151 — Ekiti (Phase 3): culture and heritage. Researched 2026-10-07. Pattern: batches 132, 145.

NCMM: no declared monument and no national museum in Ekiti; proposed list (hand count, calibrated as for batch 145:
Wild Life/Forest Reserve section, Baturiya 94) No. 95 'Ogun Onire Grove, Ire-Ekiti, Oye L.G.A'.
The Sun (4 Aug 2025): the Ogun Onire festival at Ire-Ekiti, Oye LGA, every August; the Onire, Oba Victor Adeleke
Bobade: 'Ogun is the father of Ire'; appeal to make 'the place where the Ogun entered the earth' a tourist site.
The Sun (24 Oct 2019): Ire-Ekiti 'believed in Yoruba history, to be the place where Ogun disappeared into the earth';
the festival 'usually held towards the end of the year'; it places Ire in 'Ekiti West' (NCMM and the 2025 article: Oye).
Wikipedia: 'Ikogosi Warm Springs' (warm and cold springs meeting at a confluence; about 70 °C at the source, 37 °C at
the confluence; Baptist youth camp from 1952); 'Ise Forest Reserve' (142 km²; Nigeria–Cameroon chimpanzees; about 661
butterfly species); 'Ikere-Ekiti' (Orole and Olosunta hills). Peoples Gazette (2021): the Olukere 'a priest of
Olosunta'. Ekiti State Government (2025 Udiroko festival, undated page): the Ewi hosted it; it has funded projects.
Placement: the grove and Ogun Onire festival → Oye; Ikogosi → Ekiti West (INEC ward 04-06 Ikogosi); Olosunta → Ikere;
Udiroko → Ado Ekiti; Ise Forest Reserve → state only (its LGA is not given).
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_150_ekiti_institutions as I150

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = I150.NEWS
SOURCES = {
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Ekiti: Wild Life/Forest Reserve No. 95 Ogun Onire Grove, Ire-Ekiti, Oye L.G.A."),
    "SUN25": NEWS("Ekiti agog as community celebrates Ogun Onire festival", "The Sun (Nigeria)", "2025-08-04", "https://thesun.ng/ekiti-agog-as-community-celebrates-ogun-onire-festival/",
                  "The festival at Ire-Ekiti, Oye LGA, on 3 August 2025; the Onire, Oba Victor Adeleke Bobade: 'Ogun Onire festival is an annual cultural celebration and it is celebrated in August of every year … Ogun is the father of Ire'; appeal to make 'the place where the Ogun entered the earth' a tourist site."),
    "SUN19": NEWS("Ogun Onire", "The Sun (Nigeria)", "2019-10-24", "https://thesun.ng/ogun-onire/",
                  "Ire-Ekiti 'believed in Yoruba history, to be the place where Ogun disappeared into the earth'; a priest's account of Ogun's end; the festival 'usually held towards the end of the year'. Places Ire in Ekiti West LGA."),
    "WIKO": WS("Ikogosi Warm Springs", "Warm and cold springs side by side meeting at a confluence; about 70 °C at the source and 37 °C at the confluence; a Baptist youth camp founded there from 1952."),
    "WISE": WS("Ise Forest Reserve", "142 km² in Ekiti State; a priority site for the endangered Nigeria–Cameroon chimpanzee; about 661 butterfly species; under pressure from farming, logging and hunting."),
    "WIKE": WS("Ikere-Ekiti", "Ikere LGA 'distinguished by several hills, including the Orole and Olosunta hills'."),
    "PG21": I150.SOURCES["PG21"],
    "GUDI": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Gov. Oyebanji Cautions Against Politicizing Ekiti Federal Roads",
                 organisation="Ekiti State Government", url="https://www.ekitistate.gov.ng/?p=31656", verification_status="verified",
                 notes="Report of the 2025 Udiroko Festival in Ado-Ekiti, hosted by the Ewi; the Ewi says the festival has been 'the springboard for many developmental projects', including the Great Fajuyi Hall and a modern palace. Undated page."),
    "INEC": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Directory of Polling Units: Ekiti State (Revised January 2015)",
                 organisation="Independent National Electoral Commission (INEC)", publication_date="2015-01",
                 url="https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_Ekiti.pdf", verification_status="verified", notes="Reused (batch 149)."),
}
TEXT = {
 "grove": """The Ogun Onire Grove at Ire-Ekiti, in Oye LGA, is No. 95 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Wild Life/Forest Reserve' category. Ire-Ekiti is held in Yoruba tradition to be the place where Ogun, the Yoruba deity of war, disappeared into the earth (The Sun, 2019), and the Onire of Ire has asked governments to develop 'the place where the Ogun entered the earth' as a site for visitors (The Sun, 2025). The grove is the setting of the town's annual Ogun Onire festival.""",
 "ikogosi": """The Ikogosi Warm Springs, at Ikogosi in Ekiti West LGA, are a warm spring and a cold spring that flow side by side and meet at a confluence, each keeping its own temperature (Wikipedia). The warm spring is about 70 °C at its source and 37 °C at the confluence. Local tradition once held that no one should visit the source; from 1952 a Baptist missionary, John S. McGee, began a youth camp there, and the springs are now a visitor attraction.""",
 "ise": """The Ise Forest Reserve, in Ekiti State, covers about 142 km² and is one of the remaining forest fragments of south-western Nigeria (Wikipedia). It is a priority conservation area for the endangered Nigeria–Cameroon chimpanzee, and it is home to about 661 species of butterflies. Farming, logging and hunting have degraded much of it.""",
 "olosunta": """Olosunta is one of the hills that mark the landscape of Ikere LGA, with Orole (Wikipedia). It has a priesthood: in 2021 the Ogoga's chiefs described the Olukere, whose title is older than the Ogoga's, as 'a priest of Olosunta', citing a 1987 commission (Peoples Gazette). The hill's own traditions and festival are not described in a source read.""",
 "ogun_onire": """The Ogun Onire festival is held every year at Ire-Ekiti, in Oye LGA, in honour of Ogun, whom the town regards as its founder; the Onire of Ire, Oba Victor Adeleke Bobade, told The Sun in 2025 that 'Ogun is the father of Ire' and that his own line descends from Ogun's son Ogundahunsi. It is held in August, according to the Onire, though a 2019 report said it was usually held towards the end of the year. Thousands of indigenes and visitors attend, and the Onire has called for it to be developed for tourism.""",
 "udiroko": """Udiroko is the annual festival of Ado-Ekiti, hosted by the Ewi of Ado. Reporting on the 2025 festival, the Ekiti State Government quoted the Ewi as saying that it has been turned from a purely traditional event into a platform for community development, and that it has been the springboard for projects including the Great Fajuyi Hall and a modern palace. The governor described it as a testament to the Ewi's leadership in sustaining Ekiti tradition.""",
}
ST = "@admin_units:state:ekiti"
LG = lambda l: f"@admin_units:lga:ekiti/{l}"
REC = [
    ("grove", "places", dict(place_type="sacred_site", name="Ogun Onire Grove, Ire-Ekiti", slug="ogun-onire-grove", admin_unit_id=LG("oye"), status="existing"), ["NCMMP", "SUN25", "SUN19"], "verified"),
    ("ikogosi", "places", dict(place_type="natural_feature", name="Ikogosi Warm Springs", slug="ikogosi-warm-springs", admin_unit_id=LG("ekiti-west"), status="existing"), ["WIKO", "INEC"], "well_documented"),
    ("ise", "places", dict(place_type="natural_feature", name="Ise Forest Reserve", slug="ise-forest-reserve", admin_unit_id=ST, status="existing"), ["WISE"], "reported"),
    ("olosunta", "places", dict(place_type="hill_or_mountain", name="Olosunta Hill", slug="olosunta-hill", admin_unit_id=LG("ikere"), status="existing"), ["WIKE", "PG21"], "reported"),
    ("ogun_onire", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Ogun Onire Festival", slug="ogun-onire-festival",
                                            timing="Annually in August (2025); 'towards the end of the year' in a 2019 report", current_status="active", scope_level="community"), ["SUN25", "SUN19"], "well_documented"),
    ("udiroko", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Udiroko Festival", slug="udiroko-festival",
                                         timing="Annually", current_status="active", scope_level="community"), ["GUDI"], "reported"),
]
RECORDS = [dict(key=k, table=t, evidence="multiple_sources" if len(s) > 1 else "single_reliable_source", level=lvl,
                fields=dict(f, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k]), srcs=[(x, f["name"]) for x in s]) for k, t, f, s, lvl in REC]
RELATIONS = [
    dict(frm="ogun_onire", type="celebrated_in", to=LG("oye"), source="SUN25", evidence="multiple_sources", level="well_documented", notes="Ire-Ekiti, Oye LGA (The Sun, 2025; NCMM)."),
    dict(frm="ogun_onire", type="celebrated_by", to="@ethnic_groups:yoruba", source="SUN25", evidence="single_reliable_source", level="well_documented", notes="The Sun (2025)."),
    dict(frm="ogun_onire", type="associated_with", to="grove", source="SUN25", evidence="multiple_sources", level="reported", notes="The festival honours Ogun at the place where, in tradition, he entered the earth (The Sun, 2019 and 2025)."),
    dict(frm="udiroko", type="celebrated_in", to=LG("ado-ekiti"), source="GUDI", evidence="single_reliable_source", level="well_documented", notes="Ado-Ekiti (Ekiti State Government)."),
    dict(frm="udiroko", type="associated_with", to="@polities:ado-ekiti-kingdom", role="festival hosted by the Ewi", source="GUDI", evidence="single_reliable_source", level="well_documented", notes="Ekiti State Government (2025 festival)."),
    dict(frm="udiroko", type="celebrated_by", to="@ethnic_groups:yoruba", source="GUDI", evidence="single_reliable_source", level="reported", notes="Ekiti State Government: 'the Ekiti tradition'."),
    dict(frm="olosunta", type="associated_with", to="@polities:ikere-kingdom", role="the Olukere, a rival title in Ikere, is described as a priest of Olosunta", source="PG21", evidence="single_reliable_source", level="reported", notes="Peoples Gazette (2021)."),
]
NAMES = [dict(record="grove", name="Ogun Onire Grove", name_type="official", usage_notes="The NCMM's name (proposed list, No. 95).", srcs=["NCMMP"])]
GAPS = [
    ("Ekiti: Ire-Ekiti's LGA", "The NCMM and The Sun (2025) place Ire-Ekiti in Oye LGA; The Sun (2019) says Ekiti West. Oye is used."),
    ("Ekiti: the Ogun Onire festival's date", "The Onire said in 2025 that it is held every August; a 2019 report said 'towards the end of the year'. Both are kept."),
    ("Ekiti: undescribed sites", "Olosunta's own traditions, the Ise Forest Reserve's LGA, the Udiroko festival's history and the Arinta waterfalls at Ipole-Iloro need sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Ekiti culture and heritage: the Ogun Onire Grove (NCMM proposed No. 95), Ikogosi Warm Springs, Ise Forest Reserve, Olosunta Hill, and the Ogun Onire and Udiroko festivals.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 151 — Ekiti: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **4 places:**",
         "  - the **Ogun Onire Grove** at Ire-Ekiti (NCMM proposed No. 95; Ekiti has no declared monument or national museum)",
         "  - the **Ikogosi Warm Springs**",
         "  - the **Ise Forest Reserve** (chimpanzees)",
         "  - **Olosunta Hill** at Ikere",
         "- **2 festivals:** **Ogun Onire** at Ire-Ekiti and **Udiroko** at Ado-Ekiti",
         "- **Differences kept:** Ire-Ekiti is in Oye LGA according to the NCMM and The Sun (2025), but The Sun (2019) said Ekiti West. The festival month is August according to the Onire (2025), but 'towards the end of the year' in a 2019 report.", ""]
    for k, t, f, s, lvl in REC:
        L += [f"## {f['name']} ({words(TEXT[k])} words; {lvl})", "", f"> {TEXT[k]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_151_ekiti_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_151_ekiti_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} sources={len(SOURCES)}")
