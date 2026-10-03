"""
Research batch 078 — Bauchi (Phase 3): culture and heritage. Researched 2026-10-02. Pattern: batches 060, 066, 072.

Places (15):
  * the seven declared national monuments of Bauchi State (NCMM list Nos. 6–12): Gidan Madaki (Kafin Madaki),
    Dutsen Damisa rock painting (near Gumje), Dutsen Zane Geji rock paintings (Toro), Shadawanka rock paintings,
    Shira rock paintings, the cairn of stones at Panshanu Pass ('Kwandonkaya', Toro), the first mining beacon at
    Tilden Fulani;
  * two proposed monuments (NCMM 'Historic', counted in page order: Ngazargamu 58 … Mausoleum 62, Kirfin Sama 63,
    Yakoko 66 — checks out): the Mausoleum of Abubakar Tafawa Balewa and the Kirfin Sama hill site ruins;
  * the National Museum Bauchi (NCMM; its address is the Mausoleum, Kofar Ran Road);
  * Yankari Game Reserve and its Wikki Warm Spring; Sumu Wildlife Park; Lame-Burra Game Reserve.
Cultural record (1): the Bauchi Durbar (Wikipedia, two one-line mentions — 'reported').

Evidence notes:
  * LGA links: Gidan Madaki → Ganjuwa (Kafin Madaki is its HQ, Wikipedia); Dutsen Zane Geji and the Kwandonkaya cairn
    → Toro (NCMM text says 'Toro'); Shira → Shira and Shadawanka → Bauchi (only Wikipedia's copy of the list gives the
    place; said so in the text); Mausoleum + museum → Bauchi (NCMM address); Sumu → Ganjuwa (Wikipedia).
    State link only: Dutsen Damisa (Gumje not placed), Tilden Fulani (Wikipedia's list says 'Bauchi' — not trusted),
    Kirfin Sama (the name points to Kirfi, but no source says so), Yankari and Lame-Burra (span several LGAs; no source
    names them).
  * Wikipedia's NCMM article lists 'Tafawa Balewa's Tomb' among the declared monuments; the NCMM's own declared list
    (65 entries) does not — it is No. 62 on the proposed list. NCMM followed.
  * Yankari: Wikipedia — game reserve 1956, opened to the public 1 December 1962, national park 1991 (decree 36),
    status lost 2006 at the state's request; 2,244 km². Wikki: Salamatu Fada, 'Why Yankari is a big deal', ACT
    (Society for Conservation Biology, Africa Section) vol. 9 no. 2 — 21,000,000 litres a day into the Gaji River,
    31.1 °C. The issue's year is not shown on the page.
  * Sumu: Wikipedia gives 2006 as the opening year; its Kafin Madaki article dates the plan to bring about 300 animals
    from Namibia to September 2009. Both reported, not reconciled.
  * Lame-Burra: WACN project page (22 July 2026) — c. 205,900 ha; ICIR (27 January 2022) — state revoked illegal land
    allocations by the Ningi LGA chairman.
"""
import json, re, sys
import batch_066_borno_heritage as H66

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMML": dict(H66.SOURCES["NCMML"], notes="Reused. Bauchi: No. 6 Gidan Madaki in Kafin Madaki; No. 7 Dutsen Damisa Rock Painting near Gumje; No. 8 Dutsen Zane Geji, Rock Paintings, Toro; No. 9 Shadawanka Rock Paintings; No. 10 Shira Rock Paintings at Shira; No. 11 The Cairn of Stones at the foot of Panshanu Pass, near Mile 31 on the Jos-Bauchi Road, known as Kwandonkaya, Toro; No. 12 First Mining Beacon, Tilden Fulani."),
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Bauchi (Historic): No. 62 'Mausoleum of late Abubakar Tafawa Balewa'; No. 63 'Kirfin Sama Hill Site Ruins'."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. National Museum Bauchi, Abubakar Tafawa Balewa Mausoleum, Kofar Ran Road, Bauchi."),
    "WNCMM": WS("National Commission for Museums and Monuments", "Copy of the declared list with locations: Gidan Madaki (Kafin Madaki), Dutsen Damisa (near Gumje), Dutsen Zane Geji (Toro), Shadawanka (Bauchi), Shira (Shira), Kwandonkaya (Toro), First Mining Beacon (Bauchi); also lists 'Tafawa Balewa's Tomb' (Bauchi), which the NCMM's own list does not."),
    "WPWA": WS("Prehistoric West Africa", "Rock art at the Shira and Geji sites, Bauchi State: two traditions at Shira (dark reddish monochrome anthropomorphous, and realistic — lactating cattle with calves, humans); a painted horse at Geji."),
    "WKAF": WS("Kafin Madaki", "Headquarters of Ganjuwa LGA, about 45 km north of Bauchi; September 2009 plan to house about 300 animals from Namibia near Kafin Madaki."),
    "WGAN": WS("Ganjuwa", "The Madaki of Bauchi is the District Head of Ganjuwa and a kingmaker of the Bauchi Emirate; HQ Kafin Madaki."),
    "WATB": WS("Abubakar Tafawa Balewa", "Born December 1912 at Tafawa Balewa; first and only Prime Minister of Nigeria; killed in the coup of 15 January 1966; buried in Bauchi, in Tafawa Balewa's tomb."),
    "WKIR": WS("Kirfi", "LGA bordering Gombe State; headquarters Kirfi (Kirfin Kasa)."),
    "UEA13": dict(source_type="research", source_kind="research_report", source_tier=2, title="An archaeological investigation of the Kirfi area, northern Nigeria: craft, identity and landscape",
                  organisation="University of East Anglia (doctoral thesis)", author="Abubakar Sule Sani", publication_date="2013", url="https://ueaeprints.uea.ac.uk/id/eprint/52604/",
                  verification_status="needs_corroboration", notes=f"Abstract read only: first sustained archaeological investigation of Kirfi; test pits at three sites; pottery typology; Hausa influence over the past 1,000 years. Accessed {ACCESSED}."),
    "WYAN": WS("Yankari Game Reserve", "South-central Bauchi State; about 2,244 km²; several warm springs; game reserve 1956, opened to the public 1 December 1962, national park 1991 (decree 36), national-park status lost in 2006 at the state's request; iron-smelting sites and caves; over 50 mammal and 350 bird species."),
    "ACT": dict(source_type="research", source_kind="research_report", source_tier=3, title="Why Yankari is a big deal", author="Salamatu Fada",
                organisation="Society for Conservation Biology, Africa Section (ACT, vol. 9, issue 2)", url="https://conbio.org/groups/sections/africa/act/why-yankari-is-a-big-deal",
                verification_status="needs_corroboration", notes=f"Dukkey Wells, Marshall Caves, Tunga iron smelting, Kalban, Kariyo and Paliyaram hills, Tonlong Gorge; Wikki warm spring: 21,000,000 litres a day into the Gaji River, constant 31.1 °C; Wikki Camp has a museum. Issue year not shown. Accessed {ACCESSED}."),
    "WBS": WS("Bauchi State", "Reused. 'tourism in Yankari National Park and its Wikki Warm Springs'; 'The Durbar Festival is a major annual attraction.'"),
    "WSUM": WS("Sumu Wildlife Park", "Small game reserve in the Sumu forest, Ganjuwa LGA; opened 2006; 279 animals donated by Namibia (giraffe, zebra, eland, wildebeest, hartebeest, oryx, kudu, springbok, impala); fenced."),
    "WACN": dict(source_type="website", source_kind="research_report", source_tier=3, title="Lame-Burra Game Reserve", organisation="West African Conservation Network (WACN)",
                 publication_date="2026-07-22", url="https://www.westafricanconservation.org/?p=9103", verification_status="needs_corroboration",
                 notes=f"Project page, in partnership with the Bauchi State Government: about 205,900 ha (2,059 km²); Northern Guinea savanna woodland grading to Sudan savanna in the north; riparian forest. Accessed {ACCESSED}."),
    "ICIR22": dict(source_type="news", source_kind="news", source_tier=3, title="Bauchi State revokes illegal land allocations in Lame Burra forest after WikkiTimes' investigation",
                   organisation="International Centre for Investigative Reporting (ICIR)", publication_date="2022-01-27",
                   url="https://www.icirnigeria.org/bauchi-state-revokes-illegal-land-allocations-in-lame-burra-forest-after-wikkitimes-investigation/",
                   verification_status="needs_corroboration", notes="The state government revoked allocations of hundreds of hectares in the Lame Burra Forest Reserve made by the Ningi LGA chairman, and banned all activity, including farming, in the forest."),
    "WDUR": WS("Durbar festival", "Lists Bauchi among the emirates where the Durbar is performed; usually held at the end of Ramadan and at Eid al-Adha."),
}
TEXT = {
 "madaki": """Gidan Madaki in Kafin Madaki is No. 6 on the National Commission for Museums and Monuments' list of declared national monuments. Bauchi State has seven declared monuments, Nos. 6 to 12 on that list. Kafin Madaki is the headquarters of Ganjuwa LGA, about 45 km north of Bauchi (Wikipedia). The Madaki of Bauchi is the District Head of Ganjuwa and a kingmaker of the Bauchi Emirate (Wikipedia). The building's history and present condition are not described in a readable source.""",
 "damisa": """The Dutsen Damisa rock painting, near Gumje, is No. 7 on the National Commission for Museums and Monuments' list of declared national monuments. Neither the paintings nor the site's location within Bauchi State are described in a readable source.""",
 "geji": """The Dutsen Zane rock paintings at Geji, in Toro, are No. 8 on the National Commission for Museums and Monuments' list of declared national monuments. Wikipedia names Geji, with Shira, as one of the rock-art sites of Bauchi State. It notes a painted horse among the Geji paintings, which it takes to show that the art is no older than the 15th century BCE. No fuller description of the site was found in a readable source.""",
 "shadawanka": """The Shadawanka rock paintings are No. 9 on the National Commission for Museums and Monuments' list of declared national monuments. The NCMM list names only the state; Wikipedia's copy of the list places them at Bauchi. The paintings are not described in a readable source.""",
 "shira": """The Shira rock paintings, at Shira, are No. 10 on the National Commission for Museums and Monuments' list of declared national monuments. According to Wikipedia, Shira has two rock-art traditions. One is of dark reddish, monochrome human-like figures. The other is realistic, showing humans and cattle, including cows suckling their calves. The site itself is not described in more detail in a readable source.""",
 "cairn": """The cairn of stones at the foot of the Panshanu Pass is No. 11 on the National Commission for Museums and Monuments' list of declared national monuments. The NCMM places it near Mile 31 on the Jos–Bauchi road, at a spot known as Kwandonkaya, in Toro. Who raised the cairn, when and why is not described in a readable source.""",
 "beacon": """The first mining beacon at Tilden Fulani is No. 12 on the National Commission for Museums and Monuments' list of declared national monuments. It is not described in a readable source. Its date, and the mining it marked, still need a source.""",
 "mausoleum": """The Mausoleum of Abubakar Tafawa Balewa, in Bauchi, is No. 62 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Balewa was born at Tafawa Balewa in December 1912 and was the first and only Prime Minister of Nigeria (Wikipedia). He was killed in the military coup of 15 January 1966; his body was found by a roadside near Lagos six days later, and he was buried in Bauchi (Wikipedia). The NCMM gives the mausoleum, on Kofar Ran Road, as the address of the National Museum Bauchi. Wikipedia's copy of the monuments list counts 'Tafawa Balewa's Tomb' as a declared monument, but the NCMM's own declared list does not include it.""",
 "museum": """The National Museum Bauchi is one of the national museums of the National Commission for Museums and Monuments (NCMM). The NCMM gives its address as the Abubakar Tafawa Balewa Mausoleum, Kofar Ran Road, Bauchi. Its history and collections are not described in a readable source.""",
 "kirfin": """The Kirfin Sama hill site ruins are No. 63 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. The NCMM names only the state. In 2013 Abubakar Sule Sani's doctoral thesis at the University of East Anglia presented the first sustained archaeological investigation of the Kirfi area. It dug test pits at three sites, built a pottery sequence and asked how far the Hausa world to the north-west shaped Kirfi over the past thousand years. The abstract does not say whether Kirfin Sama was one of the three sites.""",
 "yankari": """Yankari Game Reserve is a large wildlife park in south-central Bauchi State, covering about 2,244 km² (Wikipedia). It was created as a game reserve in 1956 and opened to the public on 1 December 1962. In 1991 it became a national park by decree 36, and in 2006 it lost that status at the request of the Bauchi State Government (Wikipedia). No one has lived in the park for over a century, but there are old iron-smelting sites and caves; by the late 1990s more than fifty furnaces survived in the Delimiri and Ampara area (Wikipedia). It is home to over 50 mammal species, among them the African bush elephant, buffalo, hippopotamus and a lion population near extinction, and over 350 bird species (Wikipedia). A conservation newsletter also lists the Dukkey Wells, the Marshall Caves, iron smelting at Tunga, the Kalban, Kariyo and Paliyaram hills and the Tonlong Gorge (Fada, ACT). The park is famous for its Wikki Warm Spring (Fada, ACT).""",
 "wikki": """The Wikki Warm Spring, in Yankari Game Reserve, is the warm spring for which the reserve is famous, and it gives its name to Wikki Camp, the park's tourist base (Fada, ACT). It feeds about 21,000,000 litres of clear water a day into the Gaji River and keeps a constant temperature of 31.1 °C all year, and it has been developed for recreation (Fada, ACT). Wikipedia names the park and its Wikki Warm Springs as the centre of Bauchi State's tourism.""",
 "sumu": """Sumu Wildlife Park is a small, fenced game reserve in the Sumu forest, in Ganjuwa LGA, north of Bauchi (Wikipedia). Wikipedia says it opened in 2006 and that Namibia donated 279 animals for it, including giraffe, zebra, eland, wildebeest, hartebeest, oryx, kudu, springbok and impala. Wikipedia's article on Kafin Madaki dates the state's plan to house about 300 animals from Namibia near Kafin Madaki to September 2009; the two dates are not reconciled in a source read.""",
 "lameburra": """Lame-Burra Game Reserve is a protected area in Bauchi State of about 205,900 hectares (2,059 km²), mostly Northern Guinea savanna woodland that grades into Sudan savanna in the north, with riparian forest along its rivers (West African Conservation Network, 2026). In January 2022 the state government revoked land allocations in the reserve made by the chairman of Ningi LGA, after an investigation by WikkiTimes, and banned all activity in the forest, including farming (ICIR). In 2026 the West African Conservation Network announced a long-term restoration project with the state government.""",
 "durbar": """The Bauchi Durbar is the Durbar festival as held in Bauchi. Wikipedia's article on Bauchi State calls the Durbar a major annual attraction, and its article on the festival lists Bauchi among the emirates where it is performed. Durbars are usually held at the end of Ramadan and at Eid al-Adha (Wikipedia). The Bauchi Durbar's own history and course are not described in a readable source.""",
}
B = lambda l: f"@admin_units:lga:bauchi/{l}"
ST = "@admin_units:state:bauchi"
REC = [
    ("madaki", "places", dict(place_type="monument", name="Gidan Madaki, Kafin Madaki", slug="gidan-madaki-kafin-madaki", admin_unit_id=B("ganjuwa"), status="existing"), ["NCMML", "WKAF", "WGAN"], "verified"),
    ("damisa", "places", dict(place_type="archaeological_site", name="Dutsen Damisa Rock Painting", slug="dutsen-damisa-rock-painting", admin_unit_id=ST, status="existing"), ["NCMML"], "verified"),
    ("geji", "places", dict(place_type="archaeological_site", name="Dutsen Zane Geji Rock Paintings", slug="dutsen-zane-geji-rock-paintings", admin_unit_id=B("toro"), status="existing"), ["NCMML", "WPWA"], "verified"),
    ("shadawanka", "places", dict(place_type="archaeological_site", name="Shadawanka Rock Paintings", slug="shadawanka-rock-paintings", admin_unit_id=B("bauchi"), status="existing"), ["NCMML", "WNCMM"], "verified"),
    ("shira", "places", dict(place_type="archaeological_site", name="Shira Rock Paintings", slug="shira-rock-paintings", admin_unit_id=B("shira"), status="existing"), ["NCMML", "WPWA"], "verified"),
    ("cairn", "places", dict(place_type="monument", name="Kwandonkaya Cairn, Panshanu Pass", slug="kwandonkaya-cairn-panshanu-pass", admin_unit_id=B("toro"), status="existing"), ["NCMML"], "verified"),
    ("beacon", "places", dict(place_type="monument", name="First Mining Beacon, Tilden Fulani", slug="first-mining-beacon-tilden-fulani", admin_unit_id=ST, status="existing"), ["NCMML"], "verified"),
    ("mausoleum", "places", dict(place_type="monument", name="Mausoleum of Abubakar Tafawa Balewa", slug="mausoleum-of-abubakar-tafawa-balewa", admin_unit_id=B("bauchi"), status="existing"), ["NCMMP", "NCMMM", "WATB"], "well_documented"),
    ("museum", "places", dict(place_type="museum", name="National Museum Bauchi", slug="national-museum-bauchi", admin_unit_id=B("bauchi"), status="existing"), ["NCMMM"], "verified"),
    ("kirfin", "places", dict(place_type="archaeological_site", name="Kirfin Sama Hill Site Ruins", slug="kirfin-sama-hill-site-ruins", admin_unit_id=ST, status="historical"), ["NCMMP", "UEA13"], "verified"),
    ("yankari", "places", dict(place_type="natural_feature", name="Yankari Game Reserve", slug="yankari-game-reserve", admin_unit_id=ST, status="existing"), ["WYAN", "ACT", "WBS"], "well_documented"),
    ("wikki", "places", dict(place_type="natural_feature", name="Wikki Warm Spring", slug="wikki-warm-spring", admin_unit_id=ST, status="existing"), ["ACT", "WBS"], "reported"),
    ("sumu", "places", dict(place_type="natural_feature", name="Sumu Wildlife Park", slug="sumu-wildlife-park", admin_unit_id=B("ganjuwa"), status="existing"), ["WSUM", "WKAF"], "reported"),
    ("lameburra", "places", dict(place_type="natural_feature", name="Lame-Burra Game Reserve", slug="lame-burra-game-reserve", admin_unit_id=ST, status="existing"), ["WACN", "ICIR22"], "reported"),
    ("durbar", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Bauchi Durbar",
                                        slug="bauchi-durbar", timing="End of Ramadan and Eid al-Adha (the Durbar generally)", current_status="unknown", scope_level="ethnic_group"), ["WBS", "WDUR"], "reported"),
]
RECORDS = []
for key, table, fields, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table=table, evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="wikki", type="part_of", to="yankari", source="ACT", evidence="single_reliable_source", level="reported", notes="ACT: 'Yankari is also famous for the Wikki warm spring'; Wikki Camp is the park's tourist base."),
    dict(frm="museum", type="associated_with", to="mausoleum", role="the museum's address", source="NCMMM", evidence="single_reliable_source", level="verified",
         notes="NCMM: National Museum Bauchi, Abubakar Tafawa Balewa Mausoleum, Kofar Ran Road."),
    dict(frm="madaki", type="associated_with", to="@polities:bauchi-emirate", role="seat of the Madaki of Bauchi, a kingmaker of the emirate", source="WGAN", evidence="single_reliable_source", level="reported",
         notes="Wikipedia (Ganjuwa): the Madaki of Bauchi is District Head of Ganjuwa (HQ Kafin Madaki) and a kingmaker of the Bauchi Emirate. That Gidan Madaki is his house is read from the name."),
    dict(frm="durbar", type="celebrated_by", to="@ethnic_groups:hausa", source="WDUR", evidence="single_reliable_source", level="reported", notes="Wikipedia: the Durbar is a core part of Hausa culture; Bauchi among the emirates where it is performed."),
    dict(frm="@polities:bauchi-emirate", type="associated_with", to="durbar", role="emirate that holds the Durbar", source="WDUR", evidence="single_reliable_source", level="reported", notes="Wikipedia (Durbar festival)."),
]
NAMES = [
    dict(record="cairn", name="Kwandonkaya", name_type="alternative", usage_notes="Name of the spot given in the NCMM list.", srcs=["NCMML"]),
    dict(record="mausoleum", name="Tafawa Balewa's Tomb", name_type="alternative", usage_notes="Wikipedia's name (and its copy of the declared list).", srcs=["WNCMM", "WATB"]),
    dict(record="wikki", name="Wikki Warm Springs", name_type="alternative", usage_notes="Plural form used by Wikipedia (Bauchi State).", srcs=["WBS"]),
    dict(record="yankari", name="Yankari National Park", name_type="historical", usage_notes="Its name as a national park, 1991–2006 (Wikipedia).", srcs=["WYAN"]),
]
GAPS = [
    ("Bauchi: the seven declared monuments", "The NCMM lists them without descriptions; dates, makers and condition need sources. Gumje (Dutsen Damisa) and Tilden Fulani (mining beacon) are not placed in an LGA."),
    ("Bauchi: Tafawa Balewa's tomb", "Wikipedia's copy of the NCMM list counts it as declared; the NCMM's own list has it as proposed No. 62. Which is current needs the NCMM gazette."),
    ("Bauchi: Kirfin Sama", "Not described; the LGA (presumably Kirfi) and whether Sule Sani's 2013 excavations included it need the full thesis."),
    ("Bauchi: Yankari", "The LGAs it spans are not named in a source read; the Marshall Caves and Dukkey Wells need a fuller source; the ACT issue is undated."),
    ("Bauchi: festivals", "Only the Durbar was found. Festivals of the Zaar, Jarawa, Gerawa, Polchi and other peoples, the Bauchi State Festival of Arts and Culture and the 'Bala Baràà ma Jalàm' festival (names only, Wikipedia list) need sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Bauchi culture and heritage: 7 declared NCMM monuments, 2 proposed (Tafawa Balewa mausoleum, Kirfin Sama), National Museum Bauchi, Yankari and Wikki, Sumu, Lame-Burra, Bauchi Durbar.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 078 — Bauchi: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **15 places:**",
         "  - **the seven declared national monuments** (NCMM Nos. 6–12): Gidan Madaki (Kafin Madaki), four rock-art sites (Dutsen Damisa, Dutsen Zane Geji, Shadawanka, Shira), the Kwandonkaya cairn at the Panshanu Pass, and the first mining beacon at Tilden Fulani",
         "  - **two proposed monuments**: the Mausoleum of Abubakar Tafawa Balewa (No. 62) and the Kirfin Sama hill site ruins (No. 63)",
         "  - the **National Museum Bauchi**, which the NCMM places at the mausoleum",
         "  - **Yankari Game Reserve** and its **Wikki Warm Spring**, **Sumu Wildlife Park** and **Lame-Burra Game Reserve**",
         "- **1 cultural record:** the **Bauchi Durbar**. Only two one-line mentions were found, so it is *reported*.",
         "- **Links:** Wikki is part of Yankari; the museum is tied to the mausoleum; Gidan Madaki and the Durbar are tied to the Bauchi Emirate; the Durbar to the Hausa.",
         "- **Handled with care:**",
         "  - **LGA links** only where a source names the place: five places are linked to the state only.",
         "  - **Tafawa Balewa's tomb:** Wikipedia counts it as declared, but the NCMM's own list has it as proposed. The NCMM is followed and the conflict is in the text.",
         "  - **Sumu:** Wikipedia's 2006 opening and the 2009 Namibian-animals plan are both reported, not reconciled.",
         "- **Page length:** check the word counts below. A page over 300 words would be indexable.", ""]
    for key, table, fields, srcs, lvl in REC:
        t = TEXT[key]
        L += [f"## {fields['name']} ({words(t)} words; {lvl})", "", f"> {t}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_078_bauchi_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_078_bauchi_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
