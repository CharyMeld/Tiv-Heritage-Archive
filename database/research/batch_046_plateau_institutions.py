"""
Research batch 046 — Plateau (Phase 3): traditional institutions. Researched 2026-09-30.
Pattern: Taraba batch 040.

Records: the Plateau State Council of Chiefs and Emirs; five stools a readable source calls FIRST
CLASS (Gbong Gwom Jos, Ngolong Ngas, Mishkaham Mwaghavul, Long Goemai, Long Pan); the Ponzhi Tarok
(vacant in May 2025; grade not found) and the Wase Emirate (grade not found).

Sources:
  * Wikipedia, "Gbong Gwom Jos": origin 1935, first class at Fom Bot's coronation (20 Mar 1970),
    holders, Jos Joint Traditional Council, state council chairman, palace at Jishe.
  * Leadership (14 Mar 2026): SUVs to first-class rulers; Da Jacob Gyang Buba speaks as chairman of
    the Plateau State Council of Chiefs and Emirs. Leadership (26 Mar 2026): Long Pan, first class.
  * The Guardian: Ngolong Ngas first class (NAN report on Joshua Dimlong's death; undated in the
    archived copy); interview with the Long Goemai (2017; crowned 27 Feb 2017; six electoral
    colleges; the first-class selection procedure); Lalong (17 Mar 2023): remuneration law, Langtang
    Joint Traditional Council, the Ponzhi Tarok's palace. Read via the Internet Archive (the site
    blocks automated readers).
  * Vanguard (30 Dec 2021): Mishkaham Mwaghavul, first class; Da Nelson Bakfur (1999–2021).
  * Plateau State Government press release (6 May 2025, the governor's site): the Ponzhi Tarok stool
    to be filled 'soon'; Ilum Otarok festival at Langtang North.
  * Wikipedia, "Wase, Nigeria": the Wase Emirate (founded 1817–20; 14th emir from 2010).
Handling:
  * No official list of first-class stools was found (the state's LGA pages are gone and the Internet
    Archive was intermittently offline); only stools with their own source are recorded, and a grade
    is stated only where a source gives it.
  * Present holders are named only where the source's point is the stool (appointments, deaths,
    coronations); the Ngolong Ngas's present holder rests on a Substack post and is not named.
"""
import json, re, sys

ACCESSED = "2026-09-30"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "WGBONG": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Gbong Gwom Jos", organisation="Wikipedia", url=W("Gbong Gwom Jos"),
                   verification_status="needs_corroboration",
                   notes="Berom paramount stool; established 1935 (Dachung Gyang); Berom Central Tribal Court recognised 22 Feb 1936; first class at Fom Bot's coronation, 20 Mar 1970; holders Dachung Gyang (1935–41), Rwang Pam (1947–69), Fom Bot (1969–2002), Victor Pam (2004–09), Jacob Gyang Buba (from 1 Aug 2009); chairs the Jos Joint Traditional Council and the Plateau State Council of Chiefs; palace at Jishe, Jos."),
    "LEAD26V": dict(source_type="news", source_kind="news", source_tier=3, title="Mutfwang Presents Vehicles To Traditional Rulers", organisation="Leadership", author="Achor Abimaje",
                    publication_date="2026-03-14", url="https://leadership.ng/gov-mutfwang-presents-vehicles-to-traditional-rulers/", verification_status="needs_corroboration",
                    notes="The state government presented SUVs to first-class traditional rulers; 'Chairman of the Plateau State Council of Chiefs and Emirs, Da Jacob Gyang Buba' thanked the governor."),
    "LEAD26P": dict(source_type="news", source_kind="news", source_tier=3, title="Mutfwang Endorses Leklat As New Long Pan", organisation="Leadership", author="Achor Abimaje",
                    publication_date="2026-03-26", url="https://leadership.ng/mutfwang-endorses-leklat-as-new-long-pan/", verification_status="needs_corroboration",
                    notes="The governor 'has approved the selection of Miskagam Jerome Leklat as the new Long Pan of Pan Chiefdom in Qua'anpan local government area, with first class status'; previously District Head of Bwall."),
    "GUADIM": dict(source_type="news", source_kind="news", source_tier=3, title="Plateau first class chief, Dimlong, dies at 58", organisation="The Guardian (Nigeria), News Agency of Nigeria report",
                   url="https://guardian.ng/news/plateau-first-class-chief-dimlong-dies-at-58/", verification_status="needs_corroboration",
                   archive_reference="Read via the Internet Archive; the archived copy shows no date.",
                   notes="'A first class chief, Joshua Dimlong, the Ngolong Ngas and traditional ruler of Ngas Community in Pankshin and Kanke Local Government Areas of Plateau, is dead'; aged 58; died at JUTH."),
    "GUA17": dict(source_type="news", source_kind="news", source_tier=3,
                  title="Shendam Stool: My integrity saw me through, while others bribed with millions of naira and cars – Miskoom Martin Muduutrie Shaldas III",
                  organisation="The Guardian (Nigeria), Sunday Magazine", publication_date="2017",
                  url="https://guardian.ng/sunday-magazine/shendam-stool-my-integrity-saw-me-through-while-others-bribed-with-millions-of-naira-and-cars-miskoom-martin-muduutrie-shaldas-iii/",
                  archive_reference="Read via the Internet Archive; the archived copy shows no exact date (after the coronation of 27 Feb 2017).", verification_status="needs_corroboration",
                  notes="Crowned Long Goemai of Shendam on 27 Feb 2017; 15 princes, one died, 14 aspirants; 'six electoral colleges, and the state government has gazetted this process'; the draft goes to the traditional council, the Ministry of Local Government and the governor: 'This process is meant for all the first class Chiefs in Plateau State'; candidate must be a prince and already a chief."),
    "GUA23": dict(source_type="news", source_kind="news", source_tier=3, title="Lalong restates commitment to improving welfare of traditional rulers", organisation="The Guardian (Nigeria)",
                  publication_date="2023-03-17", url="https://guardian.ng/news/lalong-restates-commitment-to-improving-welfare-of-traditional-rulers/",
                  archive_reference="Read via the Internet Archive.", verification_status="needs_corroboration",
                  notes="Installation of three third-class chiefs (Ponzhi Gani, Ponzhi Timmwat, Ponzhi Bwarat) in Langtang North at the Ponzhi Tarok's Palace; Lalong signed the Plateau State Traditional Rulers Remuneration Bill 2020, providing for the appointment, salaries and allowances of traditional rulers; the acting President of the Langtang Joint Traditional Council, the Ponzhi Zinni, spoke."),
    "VG21": dict(source_type="news", source_kind="news", source_tier=3, title="Plateau loses 1st Class Chief, Da Nelson Bakfur", organisation="Vanguard", publication_date="2021-12-30",
                 url="https://www.vanguardngr.com/2021/12/plateau-loses-1st-class-chief-da-nelson-bakfur/", verification_status="needs_corroboration",
                 notes="'A first-class traditional ruler in Plateau State, the Mishkaham Mwaghavul, Da Nelson Bakfur is dead'; paramount ruler of the Mwaghavul in Mangu LGA since 1999; chairman of the Mangu Traditional Council."),
    "GOV25": dict(source_type="official_website", source_kind="official_website", source_tier=1,
                  title="Governor Mutfwang celebrates 2025 Ilum Otarok with the Tarok nation, promises to install new Ponzhi Tarok",
                  organisation="Plateau State Government (Director of Press and Public Affairs to the Governor)", author="Gyang Bere", publication_date="2025-05-06",
                  url="https://calebmutfwang.org/2025/05/governor-mutfwang-celebrates-2025-ilum-otarok-with-the-tarok-nation-promises-to-install-new-ponzhi-tarok/",
                  verification_status="verified",
                  notes="At the 2025 Ilum Otarok Annual Cultural Festival (Langtang North Mini Stadium) the governor said consultations on a new Ponzhi Tarok were in their final stages and that he would return to install him and present the staff of office. Published on the governor's own website."),
    "WWASE": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Wase, Nigeria", organisation="Wikipedia", url=W("Wase, Nigeria"),
                  verification_status="needs_corroboration",
                  notes="Giwa, a Fulani at Wuro Mayo in Bauchi, founded the Wase dynasty (Kirk-Greene, Gazetteers of the Northern Provinces, p. 48); the Fulani Emirate of Wase founded by his eldest son Hassan between 1817 and 1820; Hassan was Madaki of Bauchi under Mallam Yakubu; Muhammadu Sambo Haruna 14th Emir from 28 Oct 2010 (Daily Trust, 29 Oct 2010); peoples of Wase: Hausa/Fulani, Yankam, Boghom, Jukun, Tarok."),
}

COUNCIL = """The Plateau State Council of Chiefs and Emirs brings together the state's first-class traditional rulers. Its chairman is the Gbong Gwom Jos, the paramount ruler of the Berom (Wikipedia); in March 2026 Da Jacob Gyang Buba spoke as the council's chairman when the state government presented vehicles to the first-class rulers (Leadership, 2026).

A first-class chief is chosen through a procedure gazetted by the state. In the account of the Long Goemai of Shendam, crowned in 2017, the choice of the traditional electoral colleges goes to the traditional council, then to the Ministry of Local Government, and finally to the governor for approval, 'the process ... meant for all the first class Chiefs in Plateau State' (The Guardian, 2017). Below the state council are joint and local councils, such as the Jos Joint Traditional Council, chaired by the Gbong Gwom Jos, the Langtang Joint Traditional Council and the Mangu Traditional Council. In March 2023 Governor Simon Lalong said he had signed a law providing for the appointment, salaries and allowances of traditional rulers (The Guardian, 2023). The law constituting the council, and a complete list of its members, were not found."""

GBONG = """The Gbong Gwom Jos is the paramount ruler of the Berom, whose domain, according to Wikipedia, covers Jos and the Berom areas of Jos North, Jos South, Barkin Ladi and Riyom, and parts of southern Kaduna. The stool dates from 1935, when Dachung Gyang required the other Berom district heads and chiefs to recognise his authority; the Berom Central Tribal Court was officially recognised on 22 February 1936. The stool was raised to first class at the coronation of Fom Bot on 20 March 1970.

Wikipedia lists five holders: Dachung Gyang (1935–1941), Rwang Pam (1947–1969), Fom Bot (1969–2002), Victor Pam (2004–2009) and Jacob Gyang Buba (since 1 August 2009). The Gbong Gwom Jos chairs the Jos Joint Traditional Council and the Plateau State Council of Chiefs and Emirs, and the palace is at Jishe, Jos."""

NGAS = """The Ngolong Ngas is the traditional ruler of the Ngas. Reporting the death of Joshua Dimlong at 58, The Guardian, carrying a News Agency of Nigeria report, described him as 'a first class chief ... the Ngolong Ngas and traditional ruler of Ngas Community in Pankshin and Kanke Local Government Areas of Plateau'. The date of his death is not shown in the archived copy of the report. The history of the stool and its present holder are not recorded here from a readable source."""

MWAGHAVUL = """The Mishkaham Mwaghavul is the paramount ruler of the Mwaghavul people of Mangu LGA and a first-class traditional ruler of Plateau State (Vanguard, 2021). Da Nelson Bakfur held the stool from 1999 until his death in December 2021, having also served as chairman of the Mangu Traditional Council."""

GOEMAI = """The Long Goemai is the traditional ruler of Shendam and a first-class chief of Plateau State. Miskoom Martin Muduutrie Shaldas III was crowned Long Goemai of Shendam on 27 February 2017, after a contest in which fifteen princes first came forward and fourteen remained (The Guardian, 2017).

In his account, the Goemai choose their ruler through six electoral colleges, in a process gazetted by the state government; the choice then passes through the traditional council and the Plateau State Ministry of Local Government to the governor for approval. A candidate must be a prince and already a chief. His great-grandfather, grandfather and father had been chiefs, and his elder brother was the immediate past holder."""

PAN = """The Long Pan is the ruler of the Pan Chiefdom in Qua'an Pan LGA, the chiefdom of the Pan, whom Blench's Atlas identifies with the Kofyar, speakers of the Pan cluster. In March 2026 Governor Caleb Mutfwang approved the kingmakers' selection of Miskagam Jerome Leklat, previously District Head of Bwall, as the new Long Pan, 'with first class status' (Leadership, 2026)."""

TAROK = """The Ponzhi Tarok is the traditional ruler of the Tarok, whose palace is in Langtang North LGA. In March 2023 Governor Simon Lalong installed three new third-class chiefs of the Langtang area at the Ponzhi Tarok's palace; the acting President of the Langtang Joint Traditional Council, the Ponzhi Zinni, spoke for the traditional rulers (The Guardian, 2023).

In May 2025, at the Ilum Otarok cultural festival in Langtang North, Governor Caleb Mutfwang said that consultations on a new Ponzhi Tarok were in their final stages and that he would return to install him and present the staff of office (Plateau State Government, 2025). Whether the stool has since been filled, and its grade, are not recorded here from a readable source."""

WASE = """The Wase Emirate, seated at Wase, was founded between 1817 and 1820 by Hassan, the eldest son of Giwa, a Fulani of Wuro Mayo in Bauchi and founder of the Wase dynasty; Hassan served as Madaki of Bauchi under Mallam Yakubu, the leader of the jihad in the Bauchi region (Wikipedia, citing Kirk-Greene's Gazetteers of the Northern Provinces). Muhammadu Sambo Haruna became the 14th Emir of Wase on 28 October 2010. Wikipedia names the Hausa-Fulani, Yankam, Boghom, Jukun and Tarok as the peoples of Wase. The emirate's grade was not found in a readable source."""

RECORDS = [
    dict(key="council", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="traditional_council", name="Plateau State Council of Chiefs and Emirs", slug="plateau-state-council-of-chiefs-and-emirs", is_extant=1,
                     summary="The Plateau State Council of Chiefs and Emirs brings together the state's first-class traditional rulers and is chaired by the Gbong Gwom Jos.",
                     description=COUNCIL),
         srcs=[("LEAD26V", "Name; chairman; first-class rulers"), ("WGBONG", "Gbong Gwom Jos as chairman; Jos Joint Traditional Council"), ("GUA17", "First-class selection procedure"),
               ("GUA23", "2023 law; Langtang Joint Traditional Council"), ("VG21", "Mangu Traditional Council")]),
    dict(key="gbong", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="traditional_title", name="Gbong Gwom Jos", slug="gbong-gwom-jos", is_extant=1, founded_year=1935, founded_text="1935 (Wikipedia)", founded_precision="year",
                     summary="The Gbong Gwom Jos, a stool dating from 1935 and first class since 1970, is the paramount ruler of the Berom and chairs the Plateau State Council of Chiefs and Emirs.",
                     description=GBONG),
         srcs=[("WGBONG", "Origin, first class 1970, holders, councils, palace"), ("LEAD26V", "Chairman of the state council (2026)")]),
    dict(key="ngas", table="polities", evidence="single_reliable_source", level="reported",
         fields=dict(polity_type="traditional_title", name="Ngolong Ngas", slug="ngolong-ngas", is_extant=1,
                     summary="The Ngolong Ngas, a first-class chief, is the traditional ruler of the Ngas of Pankshin and Kanke LGAs.", description=NGAS),
         srcs=[("GUADIM", "First class; Ngas of Pankshin and Kanke")]),
    dict(key="mwaghavul", table="polities", evidence="single_reliable_source", level="reported",
         fields=dict(polity_type="traditional_title", name="Mishkaham Mwaghavul", slug="mishkaham-mwaghavul", is_extant=1,
                     summary="The Mishkaham Mwaghavul, a first-class traditional ruler, is the paramount ruler of the Mwaghavul of Mangu LGA.", description=MWAGHAVUL),
         srcs=[("VG21", "First class; Mangu; Da Nelson Bakfur 1999–2021; Mangu Traditional Council")]),
    dict(key="goemai", table="polities", evidence="single_reliable_source", level="reported",
         fields=dict(polity_type="traditional_title", name="Long Goemai", slug="long-goemai", is_extant=1,
                     summary="The Long Goemai, a first-class chief chosen through six gazetted electoral colleges, is the traditional ruler of Shendam.", description=GOEMAI),
         srcs=[("GUA17", "Coronation 27 Feb 2017; electoral colleges; first-class procedure")]),
    dict(key="pan", table="polities", evidence="single_reliable_source", level="reported",
         fields=dict(polity_type="chiefdom", name="Long Pan", slug="long-pan", is_extant=1,
                     summary="The Long Pan, with first-class status, rules the Pan Chiefdom in Qua'an Pan LGA.", description=PAN),
         srcs=[("LEAD26P", "First class; Pan Chiefdom, Qua'an Pan; 2026 selection")]),
    dict(key="tarok", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="traditional_title", name="Ponzhi Tarok", slug="ponzhi-tarok", is_extant=1,
                     summary="The Ponzhi Tarok is the traditional ruler of the Tarok, with a palace in Langtang North; the stool was awaiting a new holder in May 2025.", description=TAROK),
         srcs=[("GUA23", "Palace in Langtang North; Langtang Joint Traditional Council"), ("GOV25", "New Ponzhi Tarok to be installed (May 2025)")]),
    dict(key="wase", table="polities", evidence="single_reliable_source", level="reported",
         fields=dict(polity_type="emirate", name="Wase Emirate", slug="wase-emirate", is_extant=1, founded_text="between 1817 and 1820 (Wikipedia)", founded_year=1817, founded_precision="circa",
                     summary="The Wase Emirate, a Fulani emirate founded between 1817 and 1820 by Hassan son of Giwa, is seated at Wase.", description=WASE),
         srcs=[("WWASE", "Founding; 14th emir 2010; peoples of Wase")]),
]
SEAT = "Seat of the stool (not a statement of its jurisdiction)."
RELATIONS = [
    dict(frm="council", type="located_in", to="@admin_units:state:plateau", source="LEAD26V", evidence="multiple_sources", level="reported", notes="State-level council."),
    dict(frm="gbong", type="member_of", to="council", role="chairman", source="WGBONG", evidence="multiple_sources", level="reported",
         notes="Wikipedia; Leadership (2026) names Da Jacob Gyang Buba as chairman."),
    dict(frm="gbong", type="located_in", to="@admin_units:state:plateau", source="WGBONG", evidence="single_reliable_source", level="reported",
         notes="Palace at Jishe, Jos (Wikipedia); the LGA of the palace is not stated, and Jos is now divided into three LGAs."),
    dict(frm="ngas", type="located_in", to="@admin_units:lga:plateau/pankshin", source="GUADIM", evidence="single_reliable_source", level="reported",
         notes="The Guardian: traditional ruler of the Ngas in Pankshin and Kanke LGAs; the seat is not stated."),
    dict(frm="ngas", type="located_in", to="@admin_units:lga:plateau/kanke", source="GUADIM", evidence="single_reliable_source", level="reported",
         notes="The Guardian: traditional ruler of the Ngas in Pankshin and Kanke LGAs; the seat is not stated."),
    dict(frm="mwaghavul", type="located_in", to="@admin_units:lga:plateau/mangu", source="VG21", evidence="single_reliable_source", level="reported",
         notes="Paramount ruler of the Mwaghavul in Mangu LGA (Vanguard)."),
    dict(frm="goemai", type="located_in", to="@admin_units:lga:plateau/shendam", source="GUA17", evidence="single_reliable_source", level="reported", notes="Long Goemai of Shendam. " + SEAT),
    dict(frm="pan", type="located_in", to="@admin_units:lga:plateau/qua-an-pan", source="LEAD26P", evidence="single_reliable_source", level="reported",
         notes="Pan Chiefdom in Qua'an Pan LGA (Leadership)."),
    dict(frm="tarok", type="located_in", to="@admin_units:lga:plateau/langtang-north", source="GUA23", evidence="single_reliable_source", level="reported",
         notes="The Ponzhi Tarok's palace is in Langtang North LGA (The Guardian, 2023). " + SEAT),
    dict(frm="wase", type="located_in", to="@admin_units:lga:plateau/wase", source="WWASE", evidence="single_reliable_source", level="reported", notes="Seat of the emirate at Wase."),
    dict(frm="gbong", type="associated_with", to="@ethnic_groups:berom", role="Berom paramount ruler", source="WGBONG", evidence="single_reliable_source", level="reported", notes="Wikipedia."),
    dict(frm="ngas", type="associated_with", to="@ethnic_groups:ngas", role="traditional ruler", source="GUADIM", evidence="single_reliable_source", level="reported", notes="The Guardian (NAN)."),
    dict(frm="mwaghavul", type="associated_with", to="@ethnic_groups:mwaghavul", role="paramount ruler", source="VG21", evidence="single_reliable_source", level="reported", notes="Vanguard (2021)."),
    dict(frm="goemai", type="associated_with", to="@ethnic_groups:goemai", role="traditional ruler", source="GUA17", evidence="single_reliable_source", level="reported",
         notes="The Goemai electoral colleges choose the Long Goemai (The Guardian, 2017)."),
    dict(frm="pan", type="associated_with", to="@ethnic_groups:kofyar", role="traditional ruler (Pan Chiefdom)", source="LEAD26P", evidence="single_reliable_source", level="reported",
         notes="Pan Chiefdom (Leadership, 2026); the Atlas identifies the Pan-speaking people as the Kofyar."),
    dict(frm="tarok", type="associated_with", to="@ethnic_groups:tarok", role="traditional ruler", source="GOV25", evidence="multiple_sources", level="reported",
         notes="Plateau State Government (2025); The Guardian (2023)."),
    dict(frm="wase", type="associated_with", to="@ethnic_groups:fulani", role="Fulani emirate", source="WWASE", evidence="single_reliable_source", level="reported",
         notes="Founded by Hassan, son of the Fulani Giwa (Wikipedia, citing Kirk-Greene)."),
]
NAMES = [
    dict(record="council", name="Plateau State Traditional Council of Chiefs", name_type="alternative", usage_notes="Wikipedia (Gbong Gwom Jos).", srcs=["WGBONG"]),
    dict(record="council", name="Plateau State Council of Chiefs", name_type="alternative", usage_notes="Short form.", srcs=["WGBONG"]),
]
GAPS = [
    ("Plateau: list of first-class stools", "No official list was found: the state government's LGA pages are gone and the Internet Archive was intermittently offline. Only stools with their own source are recorded."),
    ("Plateau: law constituting the Council of Chiefs and Emirs", "Not found online; nor the text of the 2023 remuneration law (Plateau State Traditional Rulers Remuneration Bill 2020, signed March 2023)."),
    ("Plateau: Ngolong Ngas today", "The present holder rests on a Substack post only; the stool's history is not documented from a readable source."),
    ("Plateau: Ponzhi Tarok after May 2025", "Whether a new Ponzhi Tarok was installed, and the stool's grade, are not documented."),
    ("Plateau: grades of the Wase Emirate and of Kanam", "Wikipedia and Britannica describe the Wase Emirate but not its grade; the Kanam (Dengi) stool is not researched."),
    ("Plateau: other paramount stools", "The stools of the Afizere, Anaguta, Irigwe, Ron, Montol, Tal, Pyem, Mupun and others are not yet researched, nor second- and third-class chiefdoms."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Plateau traditional institutions: State Council of Chiefs and Emirs; five first-class stools; Ponzhi Tarok; Wase Emirate.")


def report():
    L_ = ["# Research batch 046 — Plateau: traditional institutions", "",
          f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
          "## What it adds", "",
          "- **8 records:**",
          "  - the **Plateau State Council of Chiefs and Emirs**",
          "  - **five first-class stools**, each confirmed by its own source: the Gbong Gwom Jos (Berom), Ngolong Ngas, Mishkaham Mwaghavul, Long Goemai (Shendam) and Long Pan (Qua'an Pan)",
          "  - the **Ponzhi Tarok**, which the state government reported in May 2025 was about to receive a new holder",
          "  - the **Wase Emirate**",
          "- **Grades:** the Ponzhi Tarok's and the Wase Emirate's grades were not found, so their records claim none.",
          f"- **{len(RELATIONS)} links:**",
          "  - each stool to the LGA of its seat or domain",
          "  - each stool to its people: Berom, Ngas, Mwaghavul, Goemai, Kofyar, Tarok and Fulani",
          "  - the Gbong Gwom Jos to the council, as chairman",
          "- **Sources:**",
          "  - an official state press release (2025)",
          "  - Leadership (2026, two reports)",
          "  - The Guardian (2017, 2023, and an undated NAN report, read via the Internet Archive)",
          "  - Vanguard (2021)",
          "  - Wikipedia (Gbong Gwom Jos, Wase)",
          "- **No official list of Plateau's first-class stools was found.** Only stools with their own source are recorded.",
          "- **Present holders** are named only where the report is about the stool.",
          "- All records are short, so they are noindex and the sitemap is unchanged.", ""]
    for n, t in (("Plateau State Council of Chiefs and Emirs", COUNCIL), ("Gbong Gwom Jos", GBONG), ("Ngolong Ngas", NGAS), ("Mishkaham Mwaghavul", MWAGHAVUL),
                 ("Long Goemai", GOEMAI), ("Long Pan", PAN), ("Ponzhi Tarok", TAROK), ("Wase Emirate", WASE)):
        L_ += [f"## {n} ({words(t)} words)", ""] + [f"> {p}" if p else ">" for p in t.split("\n")] + [""]
    L_ += ["## Not used", "",
           "- A Substack post on the coronation of a new Ngolong Ngas (Nde Jika Golit): not a reliable source.",
           "- Search-engine summaries of Guardian articles that could not be read, such as the claim that Lalong 'restored two first-class chiefdoms'.",
           "- The Plateau State Government's LGA pages: they returned 404, and archived copies could not be reached.", "",
           "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_046_plateau_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_046_plateau_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
