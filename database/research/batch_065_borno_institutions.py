"""
Research batch 065 — Borno (Phase 3): traditional institutions. Researched 2026-10-01. Pattern: batch 059 (Adamawa).

Records (9 polities): the Borno Emirate (Shehu of Borno), Dikwa and Bama (the other al-Kanemi shehus), Biu, Gwoza,
Askira, Uba, Damboa and Shani emirates — the emirate councils Wikipedia lists for the state ('eight Emirate
Councils (Borno, Bama, Damboa, Dikwa, Biu, Askira, Gwoza, Shani and Uba Emirates)' — nine names; noted).

Evidence notes:
  * Dates of the Dikwa shehu: the 12th Shehu, Muhammad (Abba Tor) Masta II, died on 23 January 2021 (Vanguard and
    NAN, both 23 Jan 2021); a search summary and a site footer dated it 2020 and 2026 — not used. The 13th Shehu,
    Ibrahim Ibn Umar Ibrahim El-Kanemi, was appointed on 30 January 2021 and given the staff of office on
    9 February 2025 (New Telegraph, 9 Feb 2025).
  * Biu: Wikipedia's list of rulers gives Mai Umar Mustapha Aliyu (June 1989 – 14 September 2020) and his son
    Maidala Mustapha Umar Aliyu II (from 21 September 2020). Vanguard (23 Jan 2021) mentions the coronation in
    Biu of 'the new Emir, HRH Umar Mustapha Aliyu following the demise of his father' — the name form differs;
    Wikipedia's is used and the difference noted.
  * Askira and Uba: TheCable/NAN (24 May 2016) name the emirs 'Muhammadu Askirama of Askira, and Ismaila Mamza of
    Uba'; ICIR (31 May 2014) 'Abdullahi Ibn Muhammed Askirama II' and 'Ali Ibn Ismaila Mamza'. Both forms kept.
  * Gwoza: Emir Idrissa Timta killed in May 2014 and buried on 30 May (ICIR, 31 May 2014); Mohammed Shehu-Timta
    returned to Gwoza in July 2019 after five years' displacement (TheCable, 16 July 2019) and was Emir in April
    2025 (ThisDay, 27 April 2025).
  * Damboa and Shani: named only by Wikipedia (Borno State; Damboa) — 'reported'; rulers and grades not found.
  * Grades: only the Shehu of Dikwa is called 'first class' in a source read (Vanguard, 2021: 'second in position
    after Shehu of Borno'). No official list of grades was found; none are recorded for the others.
  * Jurisdictions as Wikipedia gives them (Borno 15 LGAs; Dikwa 3; Bama 1; Biu 4) are stated in the texts; only the
    seat LGA is linked (as batch 059). Wikipedia's LGA articles say 'one of the sixteen LGAs that constitute the
    Borno Emirate' while its Borno Emirate article lists fifteen (gap).
"""
import json, re, sys

ACCESSED = "2026-10-01"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = lambda t, org, d, u, n, a=None: dict(source_type="news", source_kind="news", source_tier=3, title=t, organisation=org, publication_date=d, url=u,
                                            verification_status="needs_corroboration", notes=n, **({"author": a} if a else {}))
SOURCES = {
    "WPBE": WS("Borno Emirate", "Remnant of the Kanem–Bornu Empire under the al-Kanemi dynasty; shehus at Maiduguri since 1907; fifteen LGAs listed; list of rulers (20th: Abubakar ibn Umar Garba el-Kanemi, 2009–)."),
    "WPABU": WS("Abubakar ibn Umar Garba el-Kanemi", "Shehu of Borno since 2 March 2009; born 13 May 1957 at Damagum; son of Shehu Umar (1968–1974)."),
    "WPLS": WS("List of shehus of Bornu", "Numbering of shehus (incumbent counted as the 20th); Abubakar Garbai moved his capital to Yerwa (Maiduguri) on 9 January 1907."),
    "WPDE": WS("Dikwa Emirate", "Al-Kanemi junior branch since 1902 (German Borno); seat moved to Bama in 1942; divided 2009–2010; now Dikwa, Ngala and Kala-Balge LGAs."),
    "WPBA": WS("Bama Emirate", "Split from Dikwa in 2010; one LGA (Bama); Shehu Kyari (d. 2020); his son Umar Kyari crowned in Bama on 11 February 2025; rulers styled shehus."),
    "WPBI": WS("Biu Emirate", "Biu Kingdom before 1920; rulers numbered from Yamta-ra-Wala (c. 1535); main people Babur/Bura; first emir 1920; LGAs Biu, Hawul, Kwaya Kusar, Bayo; rulers list (Mai Umar Mustapha Aliyu 1989–14 Sep 2020; Mustapha Umar Aliyu II from 21 Sep 2020)."),
    "WPDAM": WS("Damboa", "'It was one of the sixteen LGAs that constituted the Borno Emirate before establishing Damboa Emirate Council'."),
    "WPBO": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Borno State", organisation="Wikipedia", url=W("Borno State"),
                 verification_status="needs_corroboration", notes="Reused. 'eight Emirate Councils (Borno, Bama, Damboa, Dikwa, Biu, Askira, Gwoza, Shani and Uba Emirates)'."),
    "VAN21": NEWS("Borno first class emir, Shehu Abba Masta II, is dead", "Vanguard", "2021-01-23", "https://www.vanguardngr.com/2021/01/borno-first-class-emir-shehu-abba-masta-ii-is-dead/",
                  "Shehu of Dikwa, 'second in position after Shehu of Borno', died 23 Jan 2021; appointed March 2010; Dikwa = Ngala, Dikwa, Kala-Balge (12 districts), Bama one LGA (12 districts). Also mentions the coronation in Biu of a new emir.", "Ndahi Marama"),
    "NAN21": NEWS("Shehu of Dikwa is dead", "Peoples Gazette (News Agency of Nigeria)", "2021-01-23", "https://gazettengr.com/just-in-shehu-of-dikwa-is-dead/",
                  "Borno government confirmed the death of the Shehu of Dikwa, Muhammad Ibn-Masta El-Kanemi (born 1946), on Saturday 23 Jan 2021 in Abuja."),
    "NT25": NEWS("Zulum Presents Staff Of Office To 13th Shehu Of Dikwa", "New Telegraph", "2025-02-09", "https://newtelegraphng.com/zulum-presents-staff-of-office-to-13th-shehu-of-dikwa/",
                 "Staff of office to Ibrahim Ibn Umar Ibrahim El-Kanemi, appointed 30 January 2021 to succeed Muhammad Masta II, the 12th Shehu; Commissioner for Local Government and Emirates Affairs present.", "Ahmed Miringa"),
    "CAB16": NEWS("Borno emirs return home 2 years after running away from B'Haram", "TheCable (News Agency of Nigeria)", "2016-05-24", "https://www.thecable.ng/?p=107182",
                  "Read via the Internet Archive. 'Two out of the five Borno emirs forced to flee ... Muhammadu Askirama of Askira, and Ismaila Mamza of Uba'; others: Kyari El-Kanemi (Bama), Muhammad Ibn Masta (Dikwa), Muhammad Timta (Gwoza)."),
    "CAB19": NEWS("Gwoza monarch returns home, five years after displacement by Boko Haram", "TheCable", "2019-07-16", "https://www.thecable.ng/?p=368221",
                  "Read via the Internet Archive. Mohammed Shehu-Timta, the emir of Gwoza, returned to his throne after fleeing in 2014.", "Femi Owolabi"),
    "ICIR14": NEWS("Minister Extols Slain Emir Of Gwoza At Burial", "The ICIR", "2014-05-31", "https://www.icirnigeria.org/minister-extols-slain-emir-of-gwoza-at-burial/",
                   "Late Emir of Gwoza, Idrissa Timta, laid to rest on Friday (30 May 2014); emirs of Askira (Abdullahi Ibn Muhammed Askirama II) and Uba (Ali Ibn Ismaila Mamza) also attacked."),
    "TD25": NEWS("Emir Seeks Military Intervention as Boko Haram Kills 12 in Borno Village", "ThisDay", "2025-04-27",
                 "https://www.thisdaylive.com/2025/04/27/emir-seeks-military-intervention-as-boko-haram-kills-12-in-borno-village/",
                 "The Emir of Gwoza, HRH Alhaji Mohammed Shehu Timta (April 2025); Pulka District, Gwoza LGA."),
}
TEXT = {
 "borno": """The Borno Emirate is the traditional state of the shehus of Borno, a remnant of the old Kanem–Bornu Empire ruled by the al-Kanemi dynasty, which supplanted the Sayfawa mais in the early 19th century under Muhammad al-Amin al-Kanemi (Wikipedia). After Rabih az-Zubayr destroyed the old capital, Kukawa, in 1893–1894, the colonial partition left Shehu Abubakar Garbai as ruler of British Borno; he moved his capital to Yerwa, later called Maiduguri, on 9 January 1907, and it has remained the seat of the emirate since. The shehus now serve as ceremonial leaders. Wikipedia lists fifteen LGAs in the emirate: Abadam, Chibok, Gubio, Guzamala, Jere, Kaga, Konduga, Kukawa, Mafa, Magumeri, Maiduguri, Marte, Mobbar, Monguno and Nganzai. The present ruler, Abubakar ibn Umar Garba el-Kanemi, born in 1957, has been Shehu since 2 March 2009 and is counted as the 20th Shehu of Borno.""",
 "dikwa": """The Dikwa Emirate is a traditional state ruled by a junior branch of the al-Kanemi dynasty. It began in 1902, when Dikwa fell in the German sphere and Shehu Abubakar Garbai left it to his relative Sanda Mandarama; its seat was moved to Bama in 1942, and in 2009–2010 the old emirate was divided, leaving Dikwa with Dikwa, Ngala and Kala/Balge LGAs and creating the Bama Emirate (Wikipedia; Vanguard). Vanguard (2021) calls the Shehu of Dikwa a first-class ruler, second in position after the Shehu of Borno. The 12th Shehu, Muhammad (Abba Tor) Masta II, appointed in March 2010 according to Vanguard (2008 according to NAN), died on 23 January 2021 (Vanguard; NAN). His cousin Ibrahim Ibn Umar Ibrahim El-Kanemi, the 13th Shehu, was appointed on 30 January 2021 and received his staff of office from Governor Babagana Zulum on 9 February 2025 (New Telegraph).""",
 "bama": """The Bama Emirate was created in 2010 from the old Dikwa Emirate, whose seat had been at Bama since 1942, and covers one LGA, Bama (Wikipedia; Vanguard). It is ruled by a branch of the al-Kanemi dynasty, and its rulers are styled shehus. Its first ruler, Kyari, who had ruled the old Dikwa Emirate since 1990, spent much of his reign in exile in Maiduguri during the Boko Haram insurgency and died in 2020; his son Umar Kyari was appointed to succeed him, but was crowned in Bama only on 11 February 2025 (Wikipedia).""",
 "biu": """The Biu Emirate is the traditional state based at Biu, known as the Biu Kingdom before 1920. Its rulers are numbered from Yamta-ra-Wala, who established his rule about 1535, and Wikipedia names the Babur/Bura as its main people. Mari Biya became the first Bura king to rule from Biu in 1878, and Mai Ari Dogo was recognised as the first Emir of Biu in 1920. Wikipedia places Biu, Hawul, Kwaya Kusar and Bayo LGAs in the emirate. Mai Umar Mustapha Aliyu ruled from 1989 until his death on 14 September 2020; his son Mustapha Umar Aliyu II succeeded him on 21 September 2020 (Wikipedia), and the new emir's coronation in Biu was reported in January 2021 (Vanguard).""",
 "gwoza": """The Gwoza Emirate is the traditional state of Gwoza LGA, one of Borno's emirate councils (Wikipedia). Its Emir, Idrissa Timta, was killed by Boko Haram in May 2014 and buried on 30 May (The ICIR). Mohammed Shehu-Timta returned to his throne in Gwoza in July 2019 after five years' displacement (TheCable), and was still Emir in April 2025 (ThisDay).""",
 "askira": """The Askira Emirate is one of Borno's emirate councils, with its seat at Askira in Askira/Uba LGA, which it shares with the Uba Emirate (Wikipedia; TheCable). Its Emir fled to Maiduguri when insurgents seized the area in August 2014 and returned to his palace in May 2016 (TheCable/NAN, which names him Muhammadu Askirama; The ICIR in 2014 gave the Emir of Askira as Abdullahi Ibn Muhammed Askirama II).""",
 "uba": """The Uba Emirate is one of Borno's emirate councils, with its seat at Uba in Askira/Uba LGA, which it shares with the Askira Emirate (Wikipedia; TheCable). Its Emir fled to Maiduguri when insurgents seized the area in August 2014 and returned to his palace in May 2016 (TheCable/NAN, which names him Ismaila Mamza; The ICIR in 2014 gave the Emir of Uba as Ali Ibn Ismaila Mamza).""",
 "damboa": """The Damboa Emirate Council is one of Borno's emirate councils (Wikipedia). According to Wikipedia, Damboa LGA was part of the Borno Emirate before the Damboa Emirate Council was established. Its date of creation, ruler and grade are not given in a readable source.""",
 "shani": """The Shani Emirate is listed by Wikipedia among the emirate councils of Borno State. Its seat, date of creation, ruler and grade are not given in a readable source; Shani is also the name of a Borno LGA.""",
}
REC = [
    ("borno", "emirate", "Borno Emirate", "borno-emirate", ["WPBE", "WPLS", "WPABU", "WPBO"], "well_documented"),
    ("dikwa", "emirate", "Dikwa Emirate", "dikwa-emirate", ["WPDE", "VAN21", "NAN21", "NT25"], "well_documented"),
    ("bama", "emirate", "Bama Emirate", "bama-emirate", ["WPBA", "VAN21", "CAB16"], "well_documented"),
    ("biu", "emirate", "Biu Emirate", "biu-emirate", ["WPBI", "VAN21"], "well_documented"),
    ("gwoza", "emirate", "Gwoza Emirate", "gwoza-emirate", ["WPBO", "ICIR14", "CAB19", "TD25"], "well_documented"),
    ("askira", "emirate", "Askira Emirate", "askira-emirate", ["WPBO", "CAB16", "ICIR14"], "well_documented"),
    ("uba", "emirate", "Uba Emirate", "uba-emirate", ["WPBO", "CAB16", "ICIR14"], "well_documented"),
    ("damboa", "emirate", "Damboa Emirate", "damboa-emirate", ["WPBO", "WPDAM"], "reported"),
    ("shani", "emirate", "Shani Emirate", "shani-emirate", ["WPBO"], "reported"),
]
RECORDS = []
for key, ptype, name, slug, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(polity_type=ptype, name=name, slug=slug, is_extant=1, summary=t.split(". ")[0] + ".", description=t),
                        srcs=[(s, name) for s in srcs]))
B = lambda l: f"@admin_units:lga:borno/{l}"
SEAT = "Seat of the traditional state (not a statement of its full jurisdiction)."
RELATIONS = [
    dict(frm="borno", type="located_in", to=B("maiduguri"), source="WPLS", evidence="multiple_sources", level="well_documented", notes="Capital at Yerwa (Maiduguri) since 9 January 1907 (Wikipedia). " + SEAT),
    dict(frm="borno", type="located_in", to="@admin_units:state:borno", source="WPBE", evidence="single_reliable_source", level="well_documented",
         notes="Wikipedia: fifteen LGAs (Abadam, Chibok, Gubio, Guzamala, Jere, Kaga, Konduga, Kukawa, Mafa, Magumeri, Maiduguri, Marte, Mobbar, Monguno, Nganzai); LGA articles say 'sixteen'. Jurisdiction not linked LGA by LGA."),
    dict(frm="dikwa", type="located_in", to=B("dikwa"), source="WPDE", evidence="multiple_sources", level="well_documented", notes="Dikwa, Ngala and Kala-Balge LGAs (Wikipedia; Vanguard). " + SEAT),
    dict(frm="bama", type="located_in", to=B("bama"), source="WPBA", evidence="multiple_sources", level="well_documented", notes="One LGA, Bama (Wikipedia; Vanguard). " + SEAT),
    dict(frm="biu", type="located_in", to=B("biu"), source="WPBI", evidence="single_reliable_source", level="well_documented", notes="Biu, Hawul, Kwaya Kusar and Bayo LGAs (Wikipedia). " + SEAT),
    dict(frm="gwoza", type="located_in", to=B("gwoza"), source="CAB19", evidence="multiple_sources", level="well_documented", notes="The Emir of Gwoza's throne at Gwoza (TheCable; ThisDay). " + SEAT),
    dict(frm="askira", type="located_in", to=B("askira-uba"), source="CAB16", evidence="multiple_sources", level="well_documented", notes="'their domain in Askira/Uba local government area' (TheCable/NAN). " + SEAT),
    dict(frm="uba", type="located_in", to=B("askira-uba"), source="CAB16", evidence="multiple_sources", level="well_documented", notes="'their domain in Askira/Uba local government area' (TheCable/NAN). " + SEAT),
    dict(frm="damboa", type="located_in", to=B("damboa"), source="WPDAM", evidence="single_reliable_source", level="reported", notes="Wikipedia (Damboa): Damboa Emirate Council established from part of the Borno Emirate."),
    dict(frm="shani", type="located_in", to="@admin_units:state:borno", source="WPBO", evidence="single_reliable_source", level="reported",
         notes="Named in Wikipedia's list of Borno emirate councils; its seat is not stated (Shani is also an LGA name)."),
    dict(frm="bama", type="part_of", to="dikwa", source="WPBA", evidence="multiple_sources", level="well_documented", valid_to_year=2010,
         notes="Historical: Bama LGA was part of the Dikwa Emirate (whose seat was at Bama from 1942) until the 2010 division (Wikipedia; Vanguard)."),
    dict(frm="damboa", type="part_of", to="borno", source="WPDAM", evidence="single_reliable_source", level="reported",
         notes="Historical: Damboa LGA was one of the LGAs of the Borno Emirate before the Damboa Emirate Council was established (Wikipedia); date not given."),
    dict(frm="biu", type="associated_with", to="@ethnic_groups:bura", role="main people (Babur/Bura)", source="WPBI", evidence="single_reliable_source", level="reported",
         notes="Wikipedia: 'The main ethnic group is the Babur/Bura people'."),
]
NAMES = [
    dict(record="borno", name="Borno Sultanate", name_type="alternative", usage_notes="Wikipedia.", srcs=["WPBE"]),
    dict(record="borno", name="Bornu Emirate", name_type="alternative", usage_notes="Wikipedia.", srcs=["WPBE"]),
    dict(record="borno", name="Shehu of Borno", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WPBE"]),
    dict(record="dikwa", name="Shehu of Dikwa", name_type="alternative", usage_notes="Title of the ruler (Vanguard; New Telegraph).", srcs=["NT25"]),
    dict(record="bama", name="Shehu of Bama", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WPBA"]),
    dict(record="biu", name="Biu Kingdom", name_type="historical", usage_notes="Name before 1920 (Wikipedia).", srcs=["WPBI"]),
    dict(record="biu", name="Mai Biu", name_type="alternative", usage_notes="Title of the ruler, also styled Kuthli (Wikipedia).", srcs=["WPBI"]),
    dict(record="damboa", name="Damboa Emirate Council", name_type="official", usage_notes="Wikipedia (Damboa).", srcs=["WPDAM"]),
]
GAPS = [
    ("Borno: emirate grades and the council", "No official list of Borno's first-, second- and third-class stools, or of the state council of chiefs/emirs, was found. Only the Shehu of Dikwa is called first class in a source read."),
    ("Borno: how many emirate councils", "Wikipedia says 'eight Emirate Councils' and names nine (Borno, Bama, Damboa, Dikwa, Biu, Askira, Gwoza, Shani, Uba); the dates when Gwoza, Askira, Uba, Damboa and Shani became emirate councils were not found."),
    ("Borno: the Borno Emirate's LGAs", "Wikipedia's Borno Emirate article lists fifteen LGAs (including Chibok); its LGA articles call each 'one of the sixteen LGAs that constitute the Borno Emirate'."),
    ("Borno: names and dates of rulers", "Dikwa: the 12th Shehu appointed March 2010 (Vanguard) or 2008 (NAN). Biu: Wikipedia 'Mustapha Umar Aliyu II', Vanguard 'Umar Mustapha Aliyu'. Askira: TheCable 'Muhammadu Askirama', The ICIR 'Abdullahi Ibn Muhammed Askirama II'. Uba: 'Ismaila Mamza' / 'Ali Ibn Ismaila Mamza'. Damboa and Shani: rulers not found."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Borno traditional institutions: the Borno, Dikwa and Bama shehus; Biu, Gwoza, Askira, Uba, Damboa and Shani emirates.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 065 — Borno: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **{len(RECORDS)} records,** the emirate councils Wikipedia lists for Borno:",
         "  - the **Borno Emirate**: the Shehu of Borno, at Maiduguri since 1907; Abubakar ibn Umar Garba el-Kanemi has been Shehu since 2009",
         "  - the two other al-Kanemi shehus:",
         "    - **Dikwa**: the 13th Shehu was appointed in 2021 and received his staff of office on 9 February 2025",
         "    - **Bama**: created from Dikwa in 2010; its Shehu was crowned on 11 February 2025",
         "  - **Biu**: the old Bura kingdom; its Emir has reigned since September 2020",
         "  - **Gwoza**: its Emir was killed in 2014; his successor returned in 2019",
         "  - **Askira** and **Uba**: both emirs returned to their palaces in 2016",
         "  - **Damboa** and **Shani**: named by Wikipedia only, so they are *reported*",
         "- **Links:** each emirate to its seat LGA (or to the state, for Shani); Bama and Damboa to the emirates they were carved from; Biu to the Bura.",
         "- **Dates checked:** the 12th Shehu of Dikwa died on **23 January 2021** (Vanguard and NAN, same day). A search summary said 2020, and a news site's page footer suggested 2026; both were wrong.",
         "- **Not recorded:**",
         "  - **grades:** no official list was found; only Dikwa is called first class in a source",
         "  - **a state council of chiefs:** not found",
         "  - **Damboa's and Shani's rulers:** not found",
         "  - Where sources give different name forms for a ruler, both are kept in the text.",
         "- **Page length:** check the word counts below. A page over 300 words would be indexable.", ""]
    for key, ptype, name, slug, srcs, lvl in REC:
        t = TEXT[key]
        L += [f"## {name} ({words(t)} words; {lvl})", "", f"> {t}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_065_borno_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_065_borno_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
