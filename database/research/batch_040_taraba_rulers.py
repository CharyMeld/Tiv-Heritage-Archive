"""
Research batch 040 — Taraba (Phase 3): traditional institutions. Researched 2026-09-26.

Records: the Taraba State Council of Chiefs; the six first-class stools named by Daily Trust in
December 2018 (Aku Uka of Wukari, Emir of Muri, Kpanti Zing, Gara Donga, Lamdo Gashaka, Chief of
Mambilla); and the Ukwe Takum, whose succession has been disputed since 1996 and which a 2024 law
turned into a rotational first-class stool (an open dispute is recorded).

Sources:
  * Daily Trust (24 Dec 2018): 56 graded chiefs — six first class (above; the Aku Uka chairs the State
    Council of Emirs and Chiefs), nine second class, 40 third class across the 16 LGAs.
  * Taraba State Government (24 Jul 2018, archived): staffs of office to 11 second-class and 37
    third-class chiefs in 12 councils and the Yangtu Special Development Area. Tier 1.
  * Tribune (23 Feb 2024) and Channels TV (6 Mar 2024) on the Takum bill and appointments.
  * Fardon and Furniss (2021), Vestiges 7(2): Garbosa II, seventh Gara Donga (1931–82); Donga's
    history and its third-class staff in his time. Tier 2.
  * Wikipedia: Wukari Federation; Kuvyon II; Muri, Taraba State; Abbas Njidda Tafida; Jalingo; Gashaka.
  * Vanguard (2022): the first-class Chief of Mambilla, palace at Gembu. New Nigerian (2026): the
    Lamido Gashaka at Serti.
Handling:
  * Ukwe Takum: Wikipedia's Takum article presents the Kuteb claim as settled; it is used only as the
    Kuteb position. The dispute is recorded (claim_disputes, via fix_040) with the Kuteb, Jukun and
    Chamba positions as reported; no current holder is named.
  * Kpanti Zing: only Daily Trust found (a Champion News page blocked fetching) — no people link.
  * Present office-holders are named only where the source's point is the stool (Emir of Muri since
    1988; the late Aku Uka Kuvyon II, council chairman).
"""
import json, re, sys

ACCESSED = "2026-09-26"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "DT18": dict(source_type="news", source_kind="news", source_tier=3, title="Taraba now has 56 graded chiefs", organisation="Daily Trust (North-East Trust)",
                 publication_date="2018-12-24", url="https://dailytrust.com/taraba-now-has-56-graded-chiefs/", verification_status="needs_corroboration",
                 notes="Six first-class chiefs: the Aku-Uka of Wukari (chairman of the State Council of Emirs and Chiefs), the Emir of Muri, the Kpanti Zing, the Gara of Donga, the Lamdo Gashaka and the Chief of Mambilla. Second class: the Chiefs of Mutum Biyu, Lau, old Muri, Dakka, Bali, Wurkum, Kwaji and Kona, and the Lamido of Gassol. Forty third-class chiefs across the 16 LGAs. Nine new chiefs presented, including the Chiefs of Jen, Ichen (Kurmi), Kwaji and Ndola."),
    "TSG18": dict(source_type="official_website", source_kind="official_website", source_tier=1, title="Gov. Ishaku Upgrades 48 Traditional Rulers to Second/Third Class Status",
                  organisation="Taraba State Government", publication_date="2018-07-24",
                  url="https://www.tarabastate.gov.ng/2018/07/24/gov-ishaku-upgrades-48-traditional-rulers-to-second-third-class-status/",
                  verification_status="verified", notes="Read via the Internet Archive (snapshot 2020-10-25). Staffs of office to 11 second-class and 37 third-class chiefs in Bali, Donga, Gashaka, Gassol, Ibi, Jalingo, Karim Lamido, Lau, Takum, Sardauna, Ussa, Wukari, Yorro, Zing and the Yangtu Special Development Area; the governor said more chiefdoms might be created."),
    "TRIB24": dict(source_type="news", source_kind="news", source_tier=3, title="Taraba Assembly passes Takum chieftaincy rotation bill", organisation="Tribune Online",
                   publication_date="2024-02-23", url="https://tribuneonlineng.com/taraba-assembly-passes-takum-chieftaincy-rotation-bill/", verification_status="needs_corroboration",
                   notes="The House passed the 'Establishment of One Rotational 1st Class Chief and Three (3) 3rd Class Chiefs Bill, 2024' for Takum. At the public hearing the Taraba State Council of Chiefs, ALGON, the Jukun Takum, the Chamba Takum and the Ministry of Justice supported it; the Kuteb Yatso of Nigeria, the Ukwe Takum Royal Palace, Kuteb Youths and NCWS opposed it, regarding the Ukwe stool as the Kuteb's exclusive preserve."),
    "CH24": dict(source_type="news", source_kind="news", source_tier=3, title="Taraba Govt Appoints Two Traditional Rulers To End Over 28 Years Of Dispute Over Throne",
                 organisation="Channels Television", publication_date="2024-03-06",
                 url="https://www.channelstv.com/2024/03/06/taraba-govt-appoints-two-traditional-rulers-to-end-over-28-years-of-dispute-over-throne/", verification_status="needs_corroboration",
                 notes="Dispute over the first-class stool of Takum (Ukwe Takum) since 1996, after the death of Ukwe Ali Kufang. An executive bill created three third-class chiefdoms and one first-class stool rotating among the Chamba, Jukun and Kuteb. Two third-class chiefs approved (Tsohon Jukun Takum; Gar Chamba Takum); the Kuteb Yatso declined to take part, opposing rotation of 'our ancestral stool'."),
    "FARDON": dict(source_type="journal_article", source_kind="journal_article", source_tier=2,
                   title="The histories of an enlightened ruler: Malam Muhamman Bitemya Sambo, Garbosa II of Donga, Central Nigeria",
                   author="Richard Fardon; Graham Furniss", organisation="Vestiges: Traces of Record", publication_date="2021",
                   publication_details="Vol. 7 (2). ISSN 2058-1963. Introduces the authors' English translation (same issue) of Garbosa II's Labarun Chambawa da Al'Amurransu (1956).",
                   url="https://vestiges-journal.info/2021/pdf/fardon_2021.pdf", verification_status="verified",
                   notes="Muhamman Bitemya Sambo (1902–82), Garbosa II, the seventh Gara Donga (1931–82); son of the fourth Gara Donga, Garbosa I (1892–1911); grandson of the second Gara Donga, Nubumga Donzomga (Garbasa I), after whom Donga was named; Donga then had only a third-class staff of chieftaincy; at Takum the British had replaced the Chamba chief with one drawn from people local to the area; Meek: Donga recognised the suzerainty of the Fulani of Kundi in the 19th century."),
    "WWUK": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Wukari Federation", organisation="Wikipedia", url=W("Wukari Federation"),
                 verification_status="needs_corroboration",
                 notes="Traditional state based at Wukari, successor to the Kwararafa state of the Jukun; ruler titled Aku Uka; Jukun at Wukari by the 17th century; in 1780 the Aku Uka endorsed the ruler of Bornu migrants at Lafia (Sarkin Lafia Bare-Bari); regional power from the 1840s; in 1958 the Aku Uka was one of four rulers serving as minister without portfolio in the Northern Region's Executive Council."),
    "WKUV": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Kuvyon II", organisation="Wikipedia", url=W("Kuvyon II"), verification_status="needs_corroboration",
                 notes="Shekarau Angyu Masa-Ibi (1937–2021), crowned Kuvyon II in 1976, the 27th Aku Uka of Kwararafa and 13th since the founding of the Wukari Federation; chairman of the Taraba State Council of Traditional Rulers; died 10 October 2021."),
    "WMURI": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Muri, Taraba State", organisation="Wikipedia", url=W("Muri, Taraba State"),
                  verification_status="needs_corroboration",
                  notes="Muri founded in 1817 as a Fulbe jihad state (founder Hamman Ruwa, d. 1833); disunity to 1880; wars with the Jukun chiefdoms, especially Kona; Royal Niger Company at Ibi from 1883; submitted to the British in 1901 under Emir Hassan; capital Jalingo."),
    "WABBAS": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Abbas Njidda Tafida", organisation="Wikipedia", url=W("Abbas Njidda Tafida"),
                   verification_status="needs_corroboration", notes="Emir of Muri in Jalingo; the 12th emir, on the throne since 12 July 1988; of the family of Lamido Nya Jatau, founder of Jalingo."),
    "WJAL": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Jalingo", organisation="Wikipedia", url=W("Jalingo"),
                 verification_status="needs_corroboration", notes="Jalingo is the seat of the Muri Emirate."),
    "WGAS": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Gashaka", organisation="Wikipedia", url=W("Gashaka"),
                 verification_status="needs_corroboration", notes="Gashaka LGA headquarters at Serti; names the Lamdo (Emir) of Gashaka."),
    "VG22": dict(source_type="news", source_kind="news", source_tier=3, title="Mambilla Plateau: Untapped goldmine laced with death traps", organisation="Vanguard",
                 publication_date="2022-08-19", url="https://www.vanguardngr.com/2022/08/mambilla-plateau-untapped-goldmine-laced-with-death-traps/",
                 verification_status="needs_corroboration", notes="'the palace of the first class chief of Mambilla ... in Gembu, headquarters of Sardauna LGA'."),
    "NN26": dict(source_type="news", source_kind="news", source_tier=3, title="Be Law Abiding, Support Govt, Lamido Gashaka Tasks Subjects", organisation="New Nigerian Newspapers",
                 publication_date="2026-03-23", url="https://www.newnigeriannewspapers.ng/be-law-abiding-support-govt-lamido-gashaka-tasks-subjects/",
                 verification_status="needs_corroboration", notes="The Lamido Gashaka spoke at the Eid-el-Fitr celebrations in Serti, headquarters of Gashaka LGA."),
    "WTAK": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Takum", organisation="Wikipedia", url=W("Takum"),
                 verification_status="needs_corroboration", notes="Presents the Kuteb position: the Kuteb as 'the aborigines of Takum' governed by the Ukwe; the colonial government appointed Ahmadu Gankwe (Kuteb) first Ukwe Takum in 1914. Used only as the Kuteb position."),
}

COUNCIL = """The Taraba State Council of Chiefs (also called the Council of Emirs and Chiefs, or of Traditional Rulers) brings together the state's graded traditional rulers. Daily Trust (2018) and Wikipedia name the Aku Uka of Wukari as its chairman.

In December 2018 Daily Trust counted 56 graded chiefs: six first class (the Aku Uka of Wukari, the Emir of Muri, the Kpanti Zing, the Gara of Donga, the Lamdo Gashaka and the Chief of Mambilla), nine second class (the Chiefs of Mutum Biyu, Lau, old Muri, Dakka, Bali, Wurkum, Kwaji and Kona and the Lamido of Gassol) and 40 third class across all 16 LGAs. Earlier that year, according to the state government, Governor Darius Ishaku had presented staffs of office to 11 second-class and 37 third-class chiefs in twelve councils and the Yangtu Special Development Area, saying that more chiefdoms might follow.

In February 2024 the House of Assembly passed a bill establishing one rotational first-class stool and three third-class chiefdoms in Takum; the council supported the bill at its public hearing. The law under which the council itself is constituted was not found."""

AKU = """The Aku Uka is the paramount ruler of the Wukari Federation, the traditional state of the Jukun based at Wukari, which Wikipedia describes as the successor to the Kwararafa state. According to Wikipedia, the Jukun were established at Wukari by the seventeenth century. In 1780 the Aku Uka endorsed the leader of Bornu migrants who had settled at Lafia, and after the Kwararafa state was destroyed in the aftermath of the Fulani jihad, Wukari became a regional power from the 1840s. In 1958 the Aku Uka was one of four traditional rulers serving as ministers without portfolio in the Executive Council of the Northern Region.

The Aku Uka holds one of Taraba's six first-class stools and chairs the State Council of Chiefs (Daily Trust, 2018). Kuvyon II, crowned in 1976 as the 27th Aku Uka of Kwararafa and the 13th since the founding of the Wukari Federation, chaired the council until his death in October 2021."""

MURI = """The Muri Emirate, with its seat at Jalingo, holds one of Taraba's six first-class stools (Daily Trust, 2018). According to Wikipedia, Muri was founded in 1817 as a Fulbe jihad state by Hamman Ruwa, who died in 1833. The emirate was deeply divided until about 1880, fought the Jukun chiefdoms, especially Kona, and submitted peacefully to the British in 1901 under Emir Hassan. Abbas Njidda Tafida, of the family of Lamido Nya Jatau, the founder of Jalingo, has been the twelfth Emir of Muri since 12 July 1988."""

ZING = """The Kpanti Zing holds one of Taraba's six first-class stools, according to Daily Trust (2018). The title names Zing, the town and LGA in the Mumuye country of northern Taraba. No further account of the stool was found in a readable source."""

DONGA = """The Gara Donga is the ruler of Donga, a Chamba kingdom in southern Taraba, and holds one of the state's six first-class stools (Daily Trust, 2018). In a study published in 2021, Richard Fardon and Graham Furniss introduce their translation of the history of the Chamba written in Hausa by Muhamman Bitemya Sambo, Garbosa II, the seventh Gara Donga (1931–82), completed in 1956. Garbosa II was the son of the fourth Gara Donga, Garbosa I (1892–1911), and the grandson of the second, Nubumga Donzomga, after whom Donga was named. In Garbosa II's time, the authors note, Donga held only a third-class staff of chieftaincy."""

GASHAKA = """The Lamdo (Lamido) Gashaka holds one of Taraba's six first-class stools (Daily Trust, 2018). His seat is at Serti, the headquarters of Gashaka LGA, where he presides over public celebrations such as the Eid festivals (New Nigerian, 2026). Wikipedia also records the Lamdo of Gashaka as the traditional ruler of the LGA."""

MAMBILLA = """The Chief of Mambilla holds one of Taraba's six first-class stools (Daily Trust, 2018). His palace is at Gembu, the headquarters of Sardauna LGA on the Mambila Plateau (Vanguard, 2022)."""

TAKUM = """The Ukwe Takum is the first-class stool of Takum, whose succession has been disputed since the death of Ukwe Ali Kufang in 1996 (Channels TV, 2024). In February 2024 the Taraba House of Assembly passed a law establishing one rotational first-class stool, to rotate among the Chamba, Jukun and Kuteb of Takum, and three third-class chiefdoms. At the public hearing the State Council of Chiefs, the Jukun Takum and the Chamba Takum supported the bill, while Kuteb organisations and the Ukwe Takum Royal Palace opposed it, regarding the Ukwe stool as the Kuteb's exclusive preserve (Tribune, 2024). In March 2024 the governor approved a Jukun and a Chamba third-class chief for Takum; the Kuteb declined to take part in the selection.

The positions differ on history as well. Wikipedia's article on Takum presents the Kuteb account: the Kuteb as the first inhabitants, governed by the Ukwe, with Ahmadu Gankwe appointed the first Ukwe Takum by the colonial government in 1914. Fardon and Furniss (2021), writing on the Chamba history of Donga, note that at Takum the British had replaced the Chamba chief with one drawn from the local people. The archive records the question as an open dispute."""

RECORDS = [
    dict(key="council", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="traditional_council", name="Taraba State Council of Chiefs", slug="taraba-state-council-of-chiefs", is_extant=1,
                     summary="The Taraba State Council of Chiefs, chaired by the Aku Uka of Wukari, brings together the state's graded rulers: six first class, nine second class and 40 third class in 2018.",
                     description=COUNCIL),
         srcs=[("DT18", "Chairman; 6/9/40 graded chiefs in 2018"), ("TSG18", "48 upgrades in 2018"), ("TRIB24", "2024 Takum law; council's support"), ("WKUV", "Aku Uka as chairman")]),
    dict(key="aku", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="kingdom", name="Aku Uka of Wukari", slug="aku-uka-of-wukari", is_extant=1, founded_text="17th century (Jukun at Wukari, per Wikipedia)",
                     founded_precision="century", summary="The Aku Uka rules the Wukari Federation, the Jukun successor state of Kwararafa, and chairs the Taraba State Council of Chiefs.",
                     description=AKU),
         srcs=[("WWUK", "Wukari Federation, Kwararafa, history"), ("WKUV", "Kuvyon II; council chairman"), ("DT18", "First class; council chairman")]),
    dict(key="muri", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="emirate", name="Muri Emirate", slug="muri-emirate", is_extant=1, founded_year=1817, founded_text="1817 (Wikipedia)", founded_precision="year",
                     summary="The Muri Emirate, founded in 1817 as a Fulbe jihad state and seated at Jalingo, is a first-class traditional stool of Taraba State.", description=MURI),
         srcs=[("WMURI", "Founding 1817; history"), ("WABBAS", "12th emir since 1988; Jalingo"), ("WJAL", "Seat at Jalingo"), ("DT18", "First class")]),
    dict(key="zing", table="polities", evidence="single_reliable_source", level="reported",
         fields=dict(polity_type="traditional_title", name="Kpanti Zing", slug="kpanti-zing", is_extant=1,
                     summary="The Kpanti Zing holds one of Taraba State's six first-class traditional stools.", description=ZING),
         srcs=[("DT18", "First class")]),
    dict(key="donga", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="kingdom", name="Gara Donga", slug="gara-donga", is_extant=1,
                     summary="The Gara Donga rules Donga, a Chamba kingdom of southern Taraba State, and holds a first-class stool.", description=DONGA),
         srcs=[("FARDON", "Garbosa II, seventh Gara Donga; Donga's history; third-class staff then"), ("DT18", "First class")]),
    dict(key="gashaka", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="emirate", name="Lamdo Gashaka", slug="lamdo-gashaka", is_extant=1,
                     summary="The Lamdo (Emir) of Gashaka, seated at Serti, holds a first-class traditional stool of Taraba State.", description=GASHAKA),
         srcs=[("DT18", "First class"), ("NN26", "At Serti, Gashaka LGA headquarters"), ("WGAS", "Lamdo of Gashaka")]),
    dict(key="mambilla", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="chiefdom", name="Chief of Mambilla", slug="chief-of-mambilla", is_extant=1,
                     summary="The Chief of Mambilla, with his palace at Gembu on the Mambila Plateau, holds a first-class traditional stool of Taraba State.", description=MAMBILLA),
         srcs=[("DT18", "First class"), ("VG22", "First-class chief; palace at Gembu, Sardauna LGA")]),
    dict(key="takum", table="polities", evidence="multiple_sources", level="disputed",
         fields=dict(polity_type="traditional_title", name="Ukwe Takum", slug="ukwe-takum", is_extant=1,
                     summary="The Ukwe Takum is the first-class stool of Takum, disputed since 1996 and made rotational among the Chamba, Jukun and Kuteb by a 2024 law opposed by the Kuteb.",
                     description=TAKUM),
         srcs=[("CH24", "Dispute since 1996; 2024 appointments; Kuteb refusal"), ("TRIB24", "2024 law; positions at the public hearing"),
               ("WTAK", "Kuteb position (first Ukwe 1914)"), ("FARDON", "British replaced the Chamba chief at Takum")]),
]
L = lambda k, lga, src, note="Seat of the stool (not a statement of its jurisdiction).": dict(frm=k, type="located_in", to=f"@admin_units:lga:taraba/{lga}", source=src,
                                                                                              evidence="multiple_sources", level="reported", notes=note)
RELATIONS = [
    dict(frm="council", type="located_in", to="@admin_units:state:taraba", source="DT18", evidence="multiple_sources", level="reported", notes="State-level council."),
    dict(frm="aku", type="member_of", to="council", role="chairman", source="DT18", evidence="multiple_sources", level="reported", notes="Daily Trust (2018); Wikipedia (Kuvyon II)."),
    L("aku", "wukari", "WWUK"), L("muri", "jalingo", "WJAL"), L("donga", "donga", "FARDON"), L("gashaka", "gashaka", "NN26"), L("mambilla", "sardauna", "VG22"),
    L("takum", "takum", "CH24"),
    dict(frm="zing", type="located_in", to="@admin_units:lga:taraba/zing", source="DT18", evidence="single_reliable_source", level="reported",
         notes="The title names Zing; the seat is not stated in the source."),
    dict(frm="aku", type="associated_with", to="@ethnic_groups:jukun", role="Jukun paramount ruler", source="WWUK", evidence="single_reliable_source", level="reported",
         notes="Wikipedia (Wukari Federation)."),
    dict(frm="muri", type="associated_with", to="@ethnic_groups:fulani", role="Fulbe jihad emirate", source="WMURI", evidence="single_reliable_source", level="reported",
         notes="Founded as a Fulbe jihad state (Wikipedia)."),
    dict(frm="donga", type="associated_with", to="@ethnic_groups:chamba", role="Chamba kingdom", source="FARDON", evidence="single_reliable_source", level="well_documented",
         notes="Fardon and Furniss (2021)."),
    dict(frm="mambilla", type="associated_with", to="@ethnic_groups:mambila", role="traditional ruler", source="VG22", evidence="single_reliable_source", level="reported",
         notes="Chief of Mambilla (Vanguard, 2022)."),
]
NAMES = [
    dict(record="council", name="Taraba State Council of Emirs and Chiefs", name_type="alternative", usage_notes="Daily Trust (2018).", srcs=["DT18"]),
    dict(record="council", name="Taraba State Council of Traditional Rulers", name_type="alternative", usage_notes="Wikipedia (Kuvyon II).", srcs=["WKUV"]),
    dict(record="gashaka", name="Lamido Gashaka", name_type="spelling_variant", usage_notes="New Nigerian (2026).", srcs=["NN26"]),
    dict(record="donga", name="Gara of Donga", name_type="alternative", usage_notes="Daily Trust (2018).", srcs=["DT18"]),
]
GAPS = [
    ("Law constituting the Taraba State Council of Chiefs", "Not found online; nor the text of the 2024 Takum law."),
    ("Kpanti Zing", "Only Daily Trust (2018) found; the stool's seat, history and people (presumably the Mumuye) need a source."),
    ("Second- and third-class stools", "Nine second-class and about 40 third-class chiefs (2018) are not recorded individually."),
    ("Current Aku Uka and council chairman", "Kuvyon II died in October 2021; his successor and whether he chairs the council now are not recorded from a readable source."),
    ("Ukwe Takum after 2024", "Whether the rotational first-class stool has been filled, and any court action, is not documented."),
    ("Chief of Mambilla: when first class", "A search snippet said 2017; not found in a readable source."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Taraba traditional institutions: State Council of Chiefs; six first-class stools; the disputed Ukwe Takum.")


def report():
    L_ = ["# Research batch 040 — Taraba: traditional institutions", "",
          f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
          f"- **8 records:** the State Council of Chiefs; the **six first-class stools** listed by Daily Trust in 2018 (Aku Uka of Wukari, Emir of Muri, Kpanti Zing, Gara Donga, Lamdo Gashaka, Chief of Mambilla); and the **Ukwe Takum**, recorded as **disputed**.",
          "- **Sources include:**",
          "  - an official state-government notice (2018, archived)",
          "  - a scholarly study of the Gara Donga (Fardon and Furniss 2021)",
          "  - news reports on the 2024 Takum law",
          f"- **{len(RELATIONS)} links:** each stool to the LGA of its seat; the Aku Uka to the Jukun (and to the council as chairman); Muri to the Fulani; Donga to the Chamba; Mambilla to the Mambila.",
          "- **Ukwe Takum:** the succession has been disputed since 1996. A 2024 law made the stool rotate among the Chamba, Jukun and Kuteb, and the Kuteb opposed it. The record gives each side as reported. An open **claim dispute** is created by fix_040, and no present holder is named.",
          "- All records are short (noindex; sitemap unchanged).", ""]
    for n, t in (("Taraba State Council of Chiefs", COUNCIL), ("Aku Uka of Wukari", AKU), ("Muri Emirate", MURI), ("Kpanti Zing", ZING), ("Gara Donga", DONGA),
                 ("Lamdo Gashaka", GASHAKA), ("Chief of Mambilla", MAMBILLA), ("Ukwe Takum", TAKUM)):
        L_ += [f"## {n} ({words(t)} words)", ""] + [f"> {p}" if p else ">" for p in t.split("\n")] + [""]
    L_ += ["## Not used", "",
           "- Wikipedia's Takum article as fact (it presents one side of the dispute); used only as the Kuteb position.",
           "- The Ukwe named in Wikipedia's Takum article (conflicts with reports that the stool has been unfilled since 1996).",
           "- Blogs (nigeriagalleria, ibifoundry), Facebook posts, search snippets (Mambilla '2017').", "",
           "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_040_taraba_rulers.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_040_taraba_rulers_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
