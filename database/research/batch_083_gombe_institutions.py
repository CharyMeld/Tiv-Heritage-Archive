"""
Research batch 083 — Gombe (Phase 3): traditional institutions. Researched 2026-10-02. Pattern: batches 059, 065,
071 and 077.

The list: Gombe State Government, 'Emirs' page (gombestate.gov.ng/emirs — now 404; read via the Internet Archive,
capture 20260311101024, copy in data/gombestate_emirs_wayback20260311.html). Its headings name 9 emirates (Gombe —
chairman of the State Council of Emirs and Chiefs —, Dukku, Funakaye, Deba, Akko, Pindiga, Gona, Nafada, Yamaltu),
5 chiefdoms (Mai Tangle, Folo Dadiya, Mai Kaltungo, Mai Tula, Dala Waja) and two senior district heads (Gombe,
Kwami): 14 emirates and chiefdoms, as Wikipedia's 'Gombe State' says. The 2021 capture (20211102151740; copy
data/gombestate_emirs_wayback20211102.html) is used to show changes of ruler; the 2026 page's lower biography section
is stale (it repeats the 2021 text) and is not used.

Rulers are dated to the source that names them. Conflicts kept, not resolved:
  * Funakaye: Wikipedia — Mu'azu Muhammad Kwairanga III died and Yakubu Muhammad Kwairanga IV was appointed on
    21 November 2022; the state page (2026) still names Mu'azu.
  * Deba / Yamaltu: the 2021 page names Ahmad Usman Muhammad II as Emir of Yamaltu; the 2026 page names Ahmad Usman
    Mohammed as Emir of Deba and Abubakar Ali as Emir of Yamaltu. The change is not explained in a source read.
  * Dukku Emirate created 2001 (Wikipedia, Dukku) or the Gombe Emirate broken up in 2002 (Wikipedia, Gombe Emirate).
Seats: LGA links from Wikipedia (Dukku, Nafada, Pindiga and Gona in Akko, Billiri for the Mai Tangale, Kaltungo,
Dala-Waja in Balanga) or the shared name; Funakaye, Deba, Yamaltu, Dadiya and Tula are 'reported'.
"""
import json, re, sys
import batch_081_gombe_languages as L81

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "GSE": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Emirs (Emirates & Chiefdoms)", organisation="Gombe State Government",
                url="https://gombestate.gov.ng/emirs/", verification_status="verified",
                archive_reference="Page now returns 404; read via the Internet Archive, captures 20260311101024 and 20211102151740 (copies in database/research/data/).",
                notes="March 2026 headings: Emirates — Abubakar Shehu Abubakar III (Chairman, State Council of Emirs & Chiefs; Gombe), Abdulkadir Haruna Rasheed (Dukku), Muazu Muhammad Kwairanga (Funakaye), Ahmad Usman Mohammed (Deba), Umar Muhammed Atiku (Akko), Adamu Haruna Yakubu (Pindiga), Umar Abdulsalam (Gona), Muhammed Dadum-Hamza (Nafada), Abubakar Ali (Yamaltu). Chiefdoms — Danladi Sanusi Maiyamba (Mai Tangle), Abdulkadir Abubakar Galadima III (Folo Dadiya), Suleh Muhammad Umar (Mai Kaltungo), Abubakar Kokia Atare Buba (Mai Tula), Muhammad Danjuma (Dala Waja). Senior district heads — Gombe, Kwami. November 2021: Saleh Muhammad (Deputy Chairman), Adamu Abubakar Galadima (Folo Dadiya), Muhammad Kwairanga Abubakar (Funakaye), Haruna Abdulkadir El-Rasheed (Dukku), Umar Muhammad Atiku (Akko), Ahmad Usman Muhammad II (Yamaltu), Mohammed Seyoji Ahmed (Pindiga), Muhammadu Dadun Hamza (Nafada), Abdulsalam Abdulkadir (Gona), Kokiya A. Buba (Mai Tula)."),
    "WGE": WS("Gombe Emirate", "Fula Lamorde Gombe; founded 1804 by Buba Yero; Gombe Abba headquarters for a campaign against the Jukun of Pindiga and Kalam; Muhammadu Kwairanga 1844–1882; Muri Emirate created from part of its territory in 1833; British conquest at Tongo 1902; capital to Nafada 1913 and Gombe Doma 1919; Waja separated 1930; Native Authority broken up 1976; emirate divided by Governor Hashidu in 2002; Abubakar Shehu Abubakar III from 6 June 2014."),
    "WGOM": L81.SOURCES["WGOM"],
    "ATLAS": L81.SOURCES["ATLAS"],
    "WDUK": WS("Dukku", "Headquarters of the Dukku Emirate, created out of the Gombe Emirate by Governor Abubakar Habu Hashidu in 2001; seventeen Laamɓe Dukku; Haruna Abdulkadiri Rashid II, 17th Laamɗo and 2nd Emir, from 1 January 2013; his father, the first Emir, died 24 December 2012."),
    "WFUN": WS("Funakaye", "Headquarters Bajoga; Emir Mu'azu Muhammad Kwairanga III died aged 45; Yakubu Muhammad Kwairanga IV appointed by Governor Muhammadu Inuwa Yahaya on 21 November 2022."),
    "WYD": WS("Yamaltu/Deba", "Headquarters Deba (Deba Habe); 'Deba's history began in 1375 AD with the appointment of the first Kuji Sovereign Chief'."),
    "WAKK": WS("Akko, Nigeria", "Headquarters Kumo; 'made up of three major Emirates namely: Akko, Gona and Pindiga'; Gona and Pindiga among its settlements."),
    "WPIN": WS("Pindiga Town", "Capital of the Pindiga Emirate in Akko LGA; Jukun; war with Buba Yero of Gombe from 1815; 'the fifth largest Emirates in Gombe State'."),
    "WNAF": WS("Nafada", "In the traditional land of the Bole people; capital of the Gombe Emirate 1913–1919; Emir Muhammad Dadum Hamza."),
    "WBIL": WS("Billiri", "Major settlement of the Tangale; the traditional ruler of the Tangale west people of Billiri is the Mai Tangle; Mai Abdu Buba Maisharu II died January 2021; Danladi Sanusi-Maiyamba announced by the governor in March 2021; protests turned violent ('The Billiri Crisis')."),
    "WKAL": WS("Kaltungo", "Kaltungo town is the administrative headquarters of Kaltungo LGA."),
    "WBAL": WS("Balanga, Nigeria", "Headquarters Talasse; Dala-Waja among its towns and villages; Waja among its main peoples."),
}
TEXT = {
 "gombe": """The Gombe Emirate, in Fula Lamorde Gombe, is a traditional state that roughly corresponds in area to the modern Gombe State (Wikipedia). It was founded in 1804, during the Fulani jihad, by Buba Yero, a follower of Usman dan Fodio, who made Gombe Abba his base for campaigns against the Jukun of Pindiga and Kalam; his son Muhammadu Kwairanga ruled from 1844 to 1882, and in 1833 the Muri Emirate was created from part of its territory (Wikipedia). The British conquered Gombe at the Battle of Tongo in 1902, moved its capital to Nafada in 1913 and to Gombe Doma, today's Gombe, in 1919; Waja was separated from it in 1930 (Wikipedia). In 2002 Governor Abubakar Habu Hashidu divided the emirate into parts ruled by second-class emirs and senior district heads (Wikipedia). Abubakar Shehu Abubakar III has been Emir since June 2014 and chairs the State Council of Emirs and Chiefs (Wikipedia; Gombe State Government).""",
 "dukku": """The Dukku Emirate is centred on the town of Dukku. According to Wikipedia it was created out of the Gombe Emirate in 2001 by Abubakar Habu Hashidu, the first civilian governor of Gombe State; Wikipedia's article on the Gombe Emirate dates the break-up to 2002. Its rulers also hold the Fulani title Laamɗo Dukku, and Wikipedia counts seventeen of them. Haruna Abdulkadir Rashid, the 17th Laamɗo and the second Emir, has reigned since 1 January 2013, after the death of his father, the first Emir, on 24 December 2012 (Wikipedia); the state government lists him as Emir of Dukku (2026).""",
 "funakaye": """The Funakaye Emirate is one of the nine emirates on the Gombe State Government's list of the state's traditional rulers; Funakaye LGA has its headquarters at Bajoga (Wikipedia). Wikipedia records that Emir Mu'azu Muhammad Kwairanga III died at the age of 45 and that Governor Muhammadu Inuwa Yahaya appointed Yakubu Muhammad Kwairanga IV on 21 November 2022. The state government's page, as captured in March 2026, still names Mu'azu Muhammad Kwairanga.""",
 "deba": """The Deba Emirate is one of the nine emirates on the Gombe State Government's list. It takes its name from Deba, also called Deba Habe, the headquarters of Yamaltu/Deba LGA; Wikipedia dates Deba's history to 1375, with the appointment of the first Kuji chief. The state government's page names Ahmad Usman Mohammed as Emir of Deba (March 2026); its 2021 version named Ahmad Usman Muhammad II as Emir of Yamaltu. The sources read do not explain the change.""",
 "yamaltu": """The Yamaltu Emirate is one of the nine emirates on the Gombe State Government's list, which names Abubakar Ali as Emir of Yamaltu (March 2026). Yamaltu shares its name with Yamaltu/Deba LGA; Blench's Atlas gives Yamaltu as another name of Nyimatli, a member of the Tera language cluster spoken in the LGA. The emirate's seat and its relation to the Deba Emirate are not described in a source read.""",
 "akko": """The Akko Emirate is one of the three emirates of Akko LGA, with Gona and Pindiga (Wikipedia); the LGA's headquarters is Kumo. Umar Muhammad Atiku is listed as Emir of Akko on both the 2021 and the 2026 versions of the Gombe State Government's page. The emirate's history is not described in a source read.""",
 "pindiga": """Pindiga, in Akko LGA, is the capital of the Pindiga Emirate (Wikipedia). Wikipedia describes a Jukun community that the founder of the Gombe Emirate, Buba Yero, made war on from 1815, and calls Pindiga the fifth largest emirate in the state; the Gombe Emirate's history names Pindiga among the Jukun settlements Buba Yero campaigned against. The state government lists Adamu Haruna Yakubu as Emir (March 2026); its 2021 page named Mohammed Seyoji Ahmed.""",
 "gona": """The Gona Emirate is one of the three emirates of Akko LGA, with Akko and Pindiga, and Gona is among the LGA's settlements (Wikipedia). The Gombe State Government lists Umar Abdulsalam as Emir of Gona (March 2026); its 2021 page named Abdulsalam Abdulkadir. The emirate's history is not described in a source read.""",
 "nafada": """The Nafada Emirate is centred on Nafada, in what Wikipedia calls the traditional land of the Bole people. Nafada was the capital of the Gombe Emirate from 1913, when the British moved it there, until 1919 (Wikipedia). Muhammad Dadum Hamza is the Emir of Nafada (Wikipedia; Gombe State Government, 2021 and 2026). The emirate's creation is not dated in a source read.""",
 "tangale": """The Tangale Chiefdom is ruled by the Mai Tangale (Mai Tangle), whom Wikipedia describes as the traditional ruler of the Tangale west people of Billiri. After Mai Abdu Buba Maisharu II died in January 2021, the state governor announced Danladi Sanusi Maiyamba as the new Mai in March 2021; many in the chiefdom saw this as an imposition, and the protests that followed turned violent (Wikipedia, 'The Billiri Crisis'). The Gombe State Government lists Danladi Sanusi Maiyamba as Mai Tangle (2026).""",
 "dadiya": """The Dadiya Chiefdom is ruled by the Folo Dadiya, one of the five chiefs on the Gombe State Government's list of the state's traditional rulers. The list named Adamu Abubakar Galadima as Folo Dadiya in 2021 and Abdulkadir Abubakar Galadima III in March 2026. The Dadiya are a people of Balanga LGA (Wikipedia; Blench's Atlas). The chiefdom's history and seat are not described in a source read.""",
 "kaltungo": """The Kaltungo Chiefdom is ruled by the Mai Kaltungo, one of the five chiefs on the Gombe State Government's list. The state government names Engineer Suleh Muhammad Umar as Mai Kaltungo (March 2026); its 2021 page named an Engineer Saleh Muhammad as deputy chairman of the State Council of Emirs and Chiefs, without giving his title. Kaltungo town is the headquarters of Kaltungo LGA (Wikipedia). The chiefdom's history is not described in a source read.""",
 "tula": """The Tula Chiefdom is ruled by the Mai Tula, one of the five chiefs on the Gombe State Government's list, which names Abubakar Kokia Atare Buba (March 2026; Kokiya A. Buba in 2021). Wikipedia's Gombe State article pictures him as 'Abubakar Buba Atare, Emir of Tula Chiefdom'. The Tula are a people of Kaltungo LGA (Wikipedia; Blench's Atlas). The chiefdom's history is not described in a source read.""",
 "waja": """The Waja Chiefdom is ruled by the Dala Waja, one of the five chiefs on the Gombe State Government's list, which names Muhammad Danjuma (March 2026). According to Wikipedia, Waja was separated from the Gombe Emirate in 1930 to become an independent district, but its headmen chose the Sarkin Yaki of Gombe, a brother of a former emir, as their chief. Dala-Waja is a settlement of Balanga LGA, where the Waja are among the main peoples (Wikipedia).""",
}
# key, name, slug, type, srcs, level, extra fields
REC = [
    ("gombe", "Gombe Emirate", "gombe-emirate", "emirate", ["WGE", "GSE", "WGOM"], "well_documented", dict(founded_year=1804, founded_text="1804, by Buba Yero (Wikipedia)", founded_precision="year")),
    ("dukku", "Dukku Emirate", "dukku-emirate", "emirate", ["WDUK", "GSE"], "well_documented", dict(founded_year=2001, founded_text="2001 (Wikipedia, Dukku); 2002 for the break-up of the Gombe Emirate (Wikipedia, Gombe Emirate)", founded_precision="year")),
    ("funakaye", "Funakaye Emirate", "funakaye-emirate", "emirate", ["GSE", "WFUN"], "well_documented", {}),
    ("deba", "Deba Emirate", "deba-emirate", "emirate", ["GSE", "WYD"], "well_documented", {}),
    ("yamaltu", "Yamaltu Emirate", "yamaltu-emirate", "emirate", ["GSE", "ATLAS"], "verified", {}),
    ("akko", "Akko Emirate", "akko-emirate", "emirate", ["GSE", "WAKK"], "well_documented", {}),
    ("pindiga", "Pindiga Emirate", "pindiga-emirate", "emirate", ["WPIN", "GSE", "WAKK", "WGE"], "well_documented", {}),
    ("gona", "Gona Emirate", "gona-emirate", "emirate", ["GSE", "WAKK"], "well_documented", {}),
    ("nafada", "Nafada Emirate", "nafada-emirate", "emirate", ["WNAF", "GSE", "WGE"], "well_documented", {}),
    ("tangale", "Tangale Chiefdom", "tangale-chiefdom", "chiefdom", ["WBIL", "GSE"], "well_documented", {}),
    ("dadiya", "Dadiya Chiefdom", "dadiya-chiefdom", "chiefdom", ["GSE"], "verified", {}),
    ("kaltungo", "Kaltungo Chiefdom", "kaltungo-chiefdom", "chiefdom", ["GSE", "WKAL"], "verified", {}),
    ("tula", "Tula Chiefdom", "tula-chiefdom", "chiefdom", ["GSE", "WGOM"], "well_documented", {}),
    ("waja", "Waja Chiefdom", "waja-chiefdom", "chiefdom", ["GSE", "WGE", "WBAL"], "well_documented", {}),
]
RECORDS = []
for key, name, slug, ptype, srcs, lvl, extra in REC:
    t = TEXT[key]
    f = dict(polity_type=ptype, name=name, slug=slug, is_extant=1, summary=t.split(". ")[0] + ".", description=t)
    f.update(extra)
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl, fields=f, srcs=[(s, name) for s in srcs]))

G = lambda l: f"@admin_units:lga:gombe/{l}"
SEAT = "Seat of the traditional state (not a statement of its full jurisdiction)."
SEATS = [  # key, lga, source, level, notes
    ("gombe", "gombe", "WGE", "well_documented", "Capital at Gombe (Gombe Doma) since 1919 (Wikipedia)."),
    ("dukku", "dukku", "WDUK", "well_documented", "Dukku town is the headquarters of the Dukku Emirate (Wikipedia)."),
    ("funakaye", "funakaye", "GSE", "reported", "Linked by its name to Funakaye LGA (headquarters Bajoga); the emirate's seat is not stated in a source read."),
    ("deba", "yamaltu-deba", "WYD", "reported", "Named after Deba, headquarters of Yamaltu/Deba LGA (Wikipedia); the emirate's seat is not stated in a source read."),
    ("yamaltu", "yamaltu-deba", "GSE", "reported", "Linked by its name to Yamaltu/Deba LGA; the emirate's seat is not stated in a source read."),
    ("akko", "akko", "WAKK", "well_documented", "One of the three emirates of Akko LGA (Wikipedia)."),
    ("pindiga", "akko", "WPIN", "well_documented", "Pindiga, capital of the emirate, is in Akko LGA (Wikipedia); INEC has a Pindiga ward in Akko LGA."),
    ("gona", "akko", "WAKK", "well_documented", "One of the three emirates of Akko LGA (Wikipedia)."),
    ("nafada", "nafada", "WNAF", "well_documented", "Nafada town (Wikipedia)."),
    ("tangale", "billiri", "WBIL", "well_documented", "The Mai Tangle is the traditional ruler of the Tangale west people of Billiri (Wikipedia)."),
    ("dadiya", "balanga", "GSE", "reported", "The Dadiya live in Balanga LGA (Wikipedia's languages table; Blench's Atlas), where INEC has a Dadiya ward; the chiefdom's seat is not stated in a source read."),
    ("kaltungo", "kaltungo", "WKAL", "well_documented", "Kaltungo town, headquarters of Kaltungo LGA (Wikipedia)."),
    ("tula", "kaltungo", "GSE", "reported", "Tula is in Kaltungo LGA (Blench's Atlas), where INEC has the Tula Baule, Tula Wange and Tula-Yiri wards; the chiefdom's seat is not stated in a source read."),
    ("waja", "balanga", "WBAL", "reported", "Dala-Waja is a settlement of Balanga LGA (Wikipedia); the chiefdom's seat is not stated in a source read."),
]
RELATIONS = [dict(frm=k, type="located_in", to=G(l), source=s, evidence="single_reliable_source", level=lvl, notes=n + " " + SEAT) for k, l, s, lvl, n in SEATS]
PEOPLE_LINKS = [  # key, people slug, role, source, notes
    ("tangale", "tangale", "chiefdom of the Tangale (Billiri)", "WBIL", "Wikipedia (Billiri): the Mai Tangle rules the Tangale west people of Billiri."),
    ("dadiya", "dadiya", "chiefdom of the Dadiya", "GSE", "Named after the people; the Folo Dadiya is on the state's list of chiefs."),
    ("tula", "tula", "chiefdom of the Tula", "GSE", "Named after the people; the Mai Tula is on the state's list of chiefs."),
    ("waja", "waja", "chiefdom of the Waja", "WGE", "Wikipedia (Gombe Emirate): Waja separated from Gombe in 1930."),
    ("gombe", "fulani", "emirate founded by a Fulani jihad leader", "WGE", "Founded in 1804 by Buba Yero during the Fulani jihad (Wikipedia)."),
    ("pindiga", "jukun", "Jukun community", "WPIN", "Wikipedia (Pindiga Town): a Jukun community; the Gombe Emirate article names Pindiga among Jukun settlements."),
    ("nafada", "bolewa", "in the traditional land of the Bole people", "WNAF", "Wikipedia (Nafada)."),
]
RELATIONS += [dict(frm=k, type="associated_with", to=f"@ethnic_groups:{p}", role=r, source=s, evidence="single_reliable_source", level="reported", notes=n) for k, p, r, s, n in PEOPLE_LINKS]
RELATIONS += [
    dict(frm="dukku", type="associated_with", to="gombe", role="created out of the Gombe Emirate", source="WDUK", evidence="multiple_sources", level="well_documented",
         notes="Wikipedia (Dukku): created out of the Gombe Emirate in 2001; Wikipedia (Gombe Emirate): divided in 2002."),
    dict(frm="waja", type="associated_with", to="gombe", role="separated from the Gombe Emirate in 1930", source="WGE", evidence="single_reliable_source", level="reported", notes="Wikipedia (Gombe Emirate)."),
    dict(frm="gombe", type="associated_with", to="@polities:muri-emirate", role="the Muri Emirate was created from part of its territory (1833)", source="WGE", evidence="single_reliable_source", level="reported",
         notes="Wikipedia (Gombe Emirate): 'in 1833 the Muri Emirate was created from part of its territory to form a state for the Emir's brother'."),
]
NAMES = [
    dict(record="gombe", name="Lamorde Gombe", name_type="alternative", usage_notes="Fula name (Wikipedia).", srcs=["WGE"]),
    dict(record="dukku", name="Laamɗo Dukku", name_type="alternative", usage_notes="Fulani title of the ruler (plural Laamɓe); Wikipedia (Dukku).", srcs=["WDUK"]),
    dict(record="tangale", name="Mai Tangle", name_type="alternative", usage_notes="Title of the ruler, as the state government and Wikipedia write it.", srcs=["GSE", "WBIL"]),
    dict(record="dadiya", name="Folo Dadiya", name_type="alternative", usage_notes="Title of the ruler (Gombe State Government).", srcs=["GSE"]),
    dict(record="kaltungo", name="Mai Kaltungo", name_type="alternative", usage_notes="Title of the ruler (Gombe State Government).", srcs=["GSE"]),
    dict(record="tula", name="Mai Tula", name_type="alternative", usage_notes="Title of the ruler (Gombe State Government).", srcs=["GSE"]),
    dict(record="waja", name="Dala Waja", name_type="alternative", usage_notes="Title of the ruler (Gombe State Government).", srcs=["GSE"]),
]
GAPS = [
    ("Gombe: rulers on a stale page", "The state's 'Emirs' page now returns 404; its last capture (March 2026) lists the rulers in its headings but repeats 2021 biographies below. Funakaye shows a ruler Wikipedia says died (successor appointed 21 November 2022). A current official list is needed."),
    ("Gombe: Deba and Yamaltu", "In 2021 the state listed Ahmad Usman Muhammad II as Emir of Yamaltu; in 2026 it lists him as Emir of Deba and Abubakar Ali as Emir of Yamaltu. When and how the emirates were separated or renamed, and their seats, need a source."),
    ("Gombe: creation dates and grades", "Only Gombe (1804) and Dukku (2001; or 2002) are dated. Grades (first/second class) are not given in a source read, nor the dates of the chiefdoms."),
    ("Gombe: senior district heads", "The state lists senior district heads for Gombe and Kwami (Wikipedia: created 2002–2003); not recorded as polities."),
    ("Gombe: seats", "Seats are not stated for Funakaye, Deba, Yamaltu, Dadiya, Tula and Waja; linked to LGAs by name or by their people, as reported."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Gombe traditional institutions: the 9 emirates and 5 chiefdoms on the state government's list.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 083 — Gombe: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **{len(RECORDS)} traditional states**, the 14 on the Gombe State Government's list (via the Internet Archive, March 2026; the page now returns 404):",
         "  - **9 emirates:** Gombe (1804; its emir chairs the State Council), Dukku, Funakaye, Deba, Akko, Pindiga, Gona, Nafada and Yamaltu",
         "  - **5 chiefdoms:** Tangale (Mai Tangle), Dadiya (Folo Dadiya), Kaltungo (Mai Kaltungo), Tula (Mai Tula) and Waja (Dala Waja)",
         f"- **{len(SEATS)} seat links to LGAs.** Funakaye, Deba, Yamaltu, Dadiya, Tula and Waja are *reported*, because their seats are not stated.",
         f"- **{len(PEOPLE_LINKS)} links to peoples** (Tangale, Dadiya, Tula, Waja, Fulani, Jukun of Pindiga, Bolewa of Nafada) and **3 links between emirates**: Dukku from Gombe, Waja separated in 1930, and the Muri Emirate created from Gombe's territory in 1833.",
         "- **Handled with care:**",
         "  - **Rulers are dated to their source.** The 2021 and 2026 captures of the state's page are compared.",
         "  - **Funakaye:** Wikipedia records a new emir appointed in November 2022, while the state page still shows the old one. Both are stated.",
         "  - **Deba and Yamaltu:** in 2021 the state called Ahmad Usman Muhammad II Emir of Yamaltu; in 2026 it calls him Emir of Deba and names a separate Emir of Yamaltu. Stated as found, not explained.",
         "  - **The 2021 Billiri crisis** over the Mai Tangle's appointment is described as Wikipedia gives it.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, name, slug, ptype, srcs, lvl, extra in REC:
        L += [f"## {name} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_083_gombe_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_083_gombe_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
