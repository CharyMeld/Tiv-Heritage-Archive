"""
Research batch 059 — Adamawa (Phase 3): traditional institutions. Researched 2026-09-30. Pattern: batch 053.

Records: the Adamawa State Council of Chiefs; the four first-class stools named by Wikipedia (Adamawa
Emirate, Hama Bachama/Numan, Gangwari Ganye, Mubi Emirate); the seven emirates and chiefdoms created in
December 2024 (Huba, Madagali, Michika, Fufore: second class; Gombi, Yungur, Maiha: third class).

Sources:
  * Adamawa State Government (adamawastate.gov.ng), 'Governor Fintiri establishes seven new emirates and
    chiefdoms' (Dec 2024): the seven, their headquarters and classes (official; its list calls Hong,
    Madagali and Michika 'Emirate' and Maiha 'Chiefdom').
  * Daily Trust (23 Dec 2024): the same seven announced in a statewide broadcast, 'already gazetted', calling
    Huba, Madagali and Michika chiefdoms and Maiha an emirate (as the later coronation reports do).
  * 21st Century Chronicle (12 Dec 2024): the Adamawa State Chiefs (Appointment and Deposition) law passed
    within 48 hours; the Lamido removed as permanent chairman of the council, whose chairmanship will
    rotate annually among first-class rulers; the emirate narrowed from eight LGAs to three; District
    Creation Law 2024 (83 new districts, signed 4 Dec 2024).
  * Wikipedia: 'Adamawa Emirate' (history, Lamibe list), 'Muhammadu Barkindo Aliyu Musdafa', 'Adamawa State'
    (table of 11 traditional states and their classes).
  * The Will (23 Dec 2020): Daniel Ismaila Shaga, 29th Hama Bachama, first-class staff of office.
  * Voice of Nigeria (3 Jun 2025): Gangwari Ganye's silver jubilee; 3rd class 1972, 1st class 2004, 7 -> 36
    districts.
  * NAN (12 Feb 2025): Huba Chiefdom ends a 120-year struggle; Töl Huba.
  * The Guardian / NAN (19 and 20 Feb 2025, via the Internet Archive): Ptil Madagali and Mbege Ka Michika.
  * Peoples Gazette / NAN (5 Feb 2025): first Emir of Fufore, second class.
  * ADSPC (reused): Fombina Palace Museum at the Lamido's palace, Yola South LGA.
Handling: grades as stated; present holders named where a report is about the stool; where the official
page and the news reports differ on 'emirate' or 'chiefdom', both are given and the type follows the
coronation reports. Mubi's first-class grade rests on Wikipedia alone (reported).
"""
import json, re, sys

W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WB = "Read via the Internet Archive (the site blocks automated readers)."
SOURCES = {
    "ADGOV": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Governor Fintiri Establishes Seven New Emirates and Chiefdoms in Adamawa State",
                  organisation="Adamawa State Government", publication_date="2024-12",
                  url="https://adamawastate.gov.ng/governor-fintiri-establishes-seven-new-emirates-and-chiefdoms-in-adamawa-state/", verification_status="verified",
                  notes="Lists: Hoba Emirate (Hong, 2nd Class), Madagali Emirate (Gulak, 2nd Class), Michika Emirate (Michika, 2nd Class), Fufore Emirate (Fufore, 2nd Class), Gombi Chiefdom (Gombi, 3rd Class), Maiha Chiefdom (Maiha, 3rd Class), Yungur Chiefdom (Dumne, 3rd Class). Accessed 2026-09-30."),
    "DT24": dict(source_type="news", source_kind="news", source_tier=3, title="Fintiri establishes 7 new chiefdoms, emirates", organisation="Daily Trust", author="Amina Abdullahi",
                 publication_date="2024-12-23", url="https://dailytrust.com/fintiri-establishes-7-new-chiefdoms-emirates/", verification_status="needs_corroboration",
                 notes="Statewide broadcast: Huba Chiefdom (Hong), Madagali Chiefdom (Gulak), Michika Chiefdom (Michika), Fufore Emirate (Fufore) — second class; Gombi Chiefdom (Gombi), Yungur Chiefdom (Dumne), Maiha Emirate (Maiha) — third class; 'already gazetted'."),
    "C21": dict(source_type="news", source_kind="news", source_tier=3, title="Adamawa gov't sacks Lamido as council of chiefs head, balkanises emirate", organisation="21st Century Chronicle",
                publication_date="2024-12-12", url="https://21stcenturychronicle.com/adamawa-govt-sacks-lamido-as-council-of-chiefs-head-balkanises-emirate/", verification_status="needs_corroboration",
                notes="New law (Adamawa State Chiefs (Appointment and Deposition) Bill, passed within 48 hours, awaiting assent): Lamido removed as permanent chairman of the state council of chiefs; chairmanship to rotate annually among first-class emirs and chiefs; emirate narrowed from Hong, Song, Gombi, Fufore, Girei, Yola North, Yola South and Mayo-Belwa to Girei, Jimeta and Yola; governor empowered to establish emirates and appoint or remove rulers; District Creation Law 2024 signed 4 December (83 new districts)."),
    "WAE": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Adamawa Emirate", organisation="Wikipedia", url=W("Adamawa Emirate"), verification_status="needs_corroboration",
                notes="Founded by Modibbo Adama (flag from Usman dan Fodio, March 1809); capital Gurin, Ribadu (1831), Yola (1841); Fombina = 'southlands'; Lamido Fombina = 'ruler of the southlands'; British occupation of Yola 2 Sep 1901; Bobbo Ahmadu installed 10 Sep 1901; over 42 sub-emirates; list of Lamibe (WorldStatesmen); the Palace and Emirate Council now 'Fombina Palace' and 'Fombina Emirate Council'."),
    "WBARK": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Muhammadu Barkindo Aliyu Musdafa", organisation="Wikipedia", url=W("Muhammadu Barkindo Aliyu Musdafa"),
                  verification_status="needs_corroboration",
                  notes="Born 13 Feb 1944; turbaned 18 Mar 2010 as the 12th Lamido of Adamawa after his father Aliyu Musdafa (reigned 57 years, died 13 Mar 2010); chosen by eleven kingmakers from candidates of the three ruling houses Yelwa, Sanda and Toungo; in Sept 2010 'Chairman of the Adamawa State Council of Chiefs and Emirs'."),
    "WPAD": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Adamawa State", organisation="Wikipedia", url=W("Adamawa State"), verification_status="needs_corroboration",
                 notes="Reused. 'Traditional states' table: 11 traditional states. First class: Adamawa Emirate (Girei, Mayo-Belwa, Song, Yola North, Yola South; HQ Yola), Ganye Chiefdom, Mubi Emirate (Mubi North, Mubi South), Numan Chiefdom. Second class: Fufore Emirate, Huba Chiefdom (Hong), Madagali Chiefdom (Gulak), Michika Chiefdom. Third class: Gombi Chiefdom, Maiha Emirate, Yungur Chiefdom (Dumne)."),
    "WILL20": dict(source_type="news", source_kind="news", source_tier=3, title="Adamawa's New Hama Bachama, Daniel Shaga Receives First Class Staff Of Office", organisation="The Will",
                   publication_date="2020-12-23", url="https://thewillnews.com/adamawas-new-hama-bachama-daniel-shaga-receives-first-class-staff-of-office", verification_status="needs_corroboration",
                   notes="Dr Daniel Ismaila Shaga, new paramount ruler of the Bachama Kingdom and 29th Hama Bachama, succeeding the late Irmiya Stephen (Kwire Mana Kpafrato II), presented with a first-class staff of office by Governor Fintiri."),
    "VON25": dict(source_type="news", source_kind="news", source_tier=2, title="Silver Jubilee of Gangwari Ganye: Royal Celebration In Adamawa State", organisation="Voice of Nigeria", author="Foluke Ibitomi",
                  publication_date="2025-06-03", url="https://von.gov.ng/silver-jubilee-of-gangwari-ganye-royal-celebration-in-adamawa-state/", verification_status="needs_corroboration",
                  notes="25th anniversary (31 May 2025) of Dr Umaru Adamu Sanda as Gangwari Ganye; ascended 2000 after his father Adamu Sanda, first paramount ruler of Ganye, installed 3rd class 15 May 1972; 1st class 2004; 7 districts grown to 36; Chamba majority with Mumuye, Fulani and others."),
    "NAN25": dict(source_type="news", source_kind="news", source_tier=2, title="Fintiri ends 120-year-old chieftaincy struggle", organisation="News Agency of Nigeria", publication_date="2025-02-12",
                  url="https://nannews.ng/fintiri-ends-120-year-old-chieftaincy-struggle/", verification_status="needs_corroboration",
                  notes="Huba Chiefdom (Hong LGA); Töl Alheri Nyako approved as Töl Huba on 3 January; under British rule reduced to an ungraded district headship under the Adamawa Emirate; approvals for restoration in 1906, 1986 and 1988 not implemented; 14 additional districts."),
    "GMAD": dict(source_type="news", source_kind="news", source_tier=3, title="Fintiri presents staff of office to paramount ruler of Madagali", organisation="The Guardian (Nigeria) / NAN", publication_date="2025-02-19",
                 url="https://guardian.ng/news/fintiri-presents-staff-of-office-to-paramount-ruler-of-madagali/", verification_status="needs_corroboration",
                 notes=WB + " Dr Ali Danburam, 'Ptil Madagali', paramount ruler of Madagali Chiefdom; district heads directed to transfer allegiance to the new emirates and chiefdoms."),
    "GMIC": dict(source_type="news", source_kind="news", source_tier=3, title="Fintiri presents staff of office to Gadiga as Mbege Ka Michika", organisation="The Guardian (Nigeria)", publication_date="2025-02-20",
                 url="https://guardian.ng/news/nigeria/fintiri-presents-staff-of-office-to-gadiga-as-mbege-ka-michika/", verification_status="needs_corroboration",
                 notes=WB + " Prof. Bulus Luka Gadiga installed as Mbege Ka Michika; 'the rich cultural heritage of the Kamwe people'; the Lamido Adamawa, represented by the Wali Adamawa, pledged the emirate's collaboration with the new chiefdoms."),
    "GAZ25": dict(source_type="news", source_kind="news", source_tier=3, title="Gov Fintiri presents staff of office to first Emir of Fufore", organisation="Peoples Gazette / NAN", publication_date="2025-02-05",
                  url="https://gazettengr.com/gov-fintiri-presents-staff-of-office-to-first-emir-of-fufore/", verification_status="needs_corroboration",
                  notes="Sani Ribadu presented with the staff of office as the first Emir of Fufore, a second-class emir; the Adamawa State Chiefs (Appointment and Deposition) Law 2024."),
    "ADSPC": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Adamawa State (state profile)", organisation="Adamawa State Planning Commission",
                  url="https://adspc.ad.gov.ng/adamawa-state/", verification_status="verified", notes="Reused (batch 057b). Tourist sites: 'Fombina Palace Museum, Lamido's Palace, Yola South LGA'."),
}
TEXT = {
 "council": """The Adamawa State Council of Chiefs (also called the Council of Chiefs and Emirs) brings together the state's leading traditional rulers. For many years it was chaired by the Lamido of Adamawa; Wikipedia describes Lamido Muhammadu Barkindo Aliyu Mustapha as its chairman in 2010. In December 2024 the State Assembly passed the Adamawa State Chiefs (Appointment and Deposition) law, which removed the Lamido as permanent chairman: the chairmanship is to rotate annually among the first-class emirs and chiefs. The law also empowers the governor to establish emirates and to appoint or remove traditional rulers (21st Century Chronicle, 12 December 2024). Earlier that month the governor had signed the District Creation Law 2024, creating 83 new districts, and on 23 December he announced seven new emirates and chiefdoms (Daily Trust). The council's full membership was not found.""",
 "emirate": """The Adamawa Emirate, also called Fombina ('the southlands', south of Bornu), is the traditional state founded by Modibbo Adama, who received a flag from Usman dan Fodio in March 1809 to lead the jihad in the region. Its capital moved from Gurin to Ribadu in 1831 and to Yola in 1841. At its height it stretched across much of today's Adamawa and Taraba states and northern Cameroon, with more than forty sub-emirates, and paid tribute to the Sultan of Sokoto. British forces occupied Yola on 2 September 1901 and installed Bobbo Ahmadu as Lamido; European treaties of 1893 and 1894 divided the emirate, and about three-quarters of its area is now in Cameroon (Wikipedia).

Its ruler is the Lamido Fombina, 'ruler of the southlands', also styled Lamido Adamawa. Muhammadu Barkindo Aliyu Mustapha was turbaned as the 12th Lamido on 18 March 2010, five days after the death of his father, Aliyu Mustafa, who had reigned for 57 years; the eleven kingmakers chose him from candidates of the three ruling houses, Yelwa, Sanda and Toungo (Wikipedia). The palace and council are now called the Fombina Palace and the Fombina Emirate Council, and the state lists a Fombina Palace Museum at the Lamido's palace in Yola South LGA (Adamawa State Planning Commission).

Wikipedia lists the emirate as first class, covering Girei, Mayo-Belwa, Song, Yola North and Yola South LGAs. A state law of December 2024 removed the Lamido as permanent chairman of the Council of Chiefs; according to 21st Century Chronicle it also narrowed the emirate from eight LGAs (Hong, Song, Gombi, Fufore, Girei, Yola North, Yola South and Mayo-Belwa) to three (Girei, Jimeta and Yola). The new Huba, Fufore and Gombi traditional states lie in LGAs that the same report lists as formerly part of the emirate.""",
 "hama": """The Hama Bachama is the paramount ruler of the Bachama (Bwatiye) Kingdom, with his palace at Numan, and a first-class traditional ruler of Adamawa State. Dr Daniel Ismaila Shaga, the 29th Hama Bachama, received his first-class staff of office from Governor Ahmadu Fintiri in December 2020; he succeeded the late Irmiya Stephen (Kwire Mana Kpafrato II) (The Will, 2020). Wikipedia lists the stool as the Numan Chiefdom, first class.""",
 "gangwari": """The Gangwari Ganye is the paramount ruler of the Ganye Chiefdom, a first-class traditional state whose people are mostly Chamba, with Mumuye, Fulani and other groups. The first paramount ruler, Adamu Sanda, was installed as a third-class chief on 15 May 1972; his son, Dr Umaru Adamu Sanda, succeeded him in 2000, and the chiefdom was raised to first class in 2004. It has grown from seven districts to thirty-six. In May 2025 the Gangwari marked his silver jubilee, at which the title Gangpaan, 'Shield of the Chamba Race', was conferred on General Theophilus Danjuma (Voice of Nigeria, 2025).""",
 "mubi": """The Mubi Emirate is a traditional state with its headquarters at Mubi. Wikipedia lists it as a first-class emirate covering Mubi North and Mubi South LGAs; a second source for its grade was not found. Mubi was one of the sub-emirates of the nineteenth-century Adamawa Emirate (Wikipedia, 'Adamawa Emirate').""",
 "huba": """The Huba Chiefdom, with its headquarters at Hong, is a second-class traditional state created by Governor Ahmadu Fintiri in December 2024 for the Huba (Kilba) people (Daily Trust; Adamawa State Government, whose list calls it the 'Hoba Emirate'). According to the News Agency of Nigeria, its creation ended a struggle of about 120 years: under British rule the Huba monarchy had been reduced to an ungraded district headship under the Adamawa Emirate, and approvals for its restoration in 1906, 1986 and 1988 were never implemented. The governor approved Töl Alheri Nyako as Töl Huba on 3 January 2025 and created fourteen more districts in the chiefdom, among them Hong, Shangui, Pella, Uding, Kulinyi, Hyema and Gaya.""",
 "madagali": """The Madagali Chiefdom, with its headquarters at Gulak, is a second-class traditional state created in December 2024 (Daily Trust; the Adamawa State Government's list calls it the 'Madagali Emirate'). On 19 February 2025 the governor presented the staff of office to Dr Ali Danburam as the Ptil Madagali, its paramount ruler, and directed district heads to transfer their allegiance to the new emirates and chiefdoms (The Guardian / NAN).""",
 "michika": """The Michika Chiefdom, with its headquarters at Michika, is a second-class traditional state created in December 2024 (Daily Trust; the Adamawa State Government's list calls it the 'Michika Emirate'). In February 2025 Professor Bulus Luka Gadiga was installed as the Mbege Ka Michika, its paramount ruler; the governor described the coronation as recognising 'the rich cultural heritage of the Kamwe people' (The Guardian).""",
 "fufore": """The Fufore Emirate, with its headquarters at Fufore, is a second-class emirate created in December 2024 (Adamawa State Government; Daily Trust). On 5 February 2025 Sani Ribadu received the staff of office as its first Emir (Peoples Gazette / NAN).""",
 "gombi": """The Gombi Chiefdom, with its headquarters at Gombi, is a third-class traditional state created in December 2024 (Adamawa State Government; Daily Trust). The title and holder of its ruler were not found in a readable source.""",
 "yungur": """The Yungur Chiefdom, with its headquarters at Dumne, is a third-class traditional state created in December 2024 (Adamawa State Government; Daily Trust). Dumne is an INEC ward of Song LGA. The title and holder of its ruler were not found in a readable source.""",
 "maiha": """The Maiha Emirate, with its headquarters at Maiha, is a third-class traditional state created in December 2024 (Daily Trust; Wikipedia; the Adamawa State Government's list calls it the 'Maiha Chiefdom'). The title and holder of its ruler were not found in a readable source.""",
}
REC = [
    ("council", "traditional_council", "Adamawa State Council of Chiefs", "adamawa-state-council-of-chiefs", ["WBARK", "C21", "DT24"], "reported"),
    ("emirate", "emirate", "Adamawa Emirate", "adamawa-emirate", ["WAE", "WBARK", "WPAD", "C21", "ADSPC"], "well_documented"),
    ("hama", "kingdom", "Hama Bachama", "hama-bachama", ["WILL20", "WPAD"], "reported"),
    ("gangwari", "chiefdom", "Gangwari Ganye", "gangwari-ganye", ["VON25", "WPAD"], "reported"),
    ("mubi", "emirate", "Mubi Emirate", "mubi-emirate", ["WPAD", "WAE"], "reported"),
    ("huba", "chiefdom", "Huba Chiefdom", "huba-chiefdom", ["ADGOV", "DT24", "NAN25"], "well_documented"),
    ("madagali", "chiefdom", "Madagali Chiefdom", "madagali-chiefdom", ["ADGOV", "DT24", "GMAD"], "well_documented"),
    ("michika", "chiefdom", "Michika Chiefdom", "michika-chiefdom", ["ADGOV", "DT24", "GMIC"], "well_documented"),
    ("fufore", "emirate", "Fufore Emirate", "fufore-emirate", ["ADGOV", "DT24", "GAZ25"], "well_documented"),
    ("gombi", "chiefdom", "Gombi Chiefdom", "gombi-chiefdom", ["ADGOV", "DT24"], "well_documented"),
    ("yungur", "chiefdom", "Yungur Chiefdom", "yungur-chiefdom", ["ADGOV", "DT24"], "well_documented"),
    ("maiha", "emirate", "Maiha Emirate", "maiha-emirate", ["ADGOV", "DT24", "WPAD"], "well_documented"),
]
RECORDS = []
for key, ptype, name, slug, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(polity_type=ptype, name=name, slug=slug, is_extant=1, summary=t.split(". ")[0] + ".", description=t),
                        srcs=[(s, name) for s in srcs]))
A = lambda l: f"@admin_units:lga:adamawa/{l}"
SEAT = "Seat of the traditional state (not a statement of its full jurisdiction)."
NEW = "Created December 2024 (Adamawa State Government; Daily Trust)."
RELATIONS = [
    dict(frm="council", type="located_in", to="@admin_units:state:adamawa", source="C21", evidence="multiple_sources", level="reported", notes="State-level council."),
    dict(frm="emirate", type="member_of", to="council", role="former permanent chairman (the Lamido)", source="WBARK", evidence="multiple_sources", level="reported",
         notes="Wikipedia: the Lamido as chairman in 2010; 21st Century Chronicle (Dec 2024): removed as permanent chairman, the chair now rotating annually among first-class rulers."),
    dict(frm="hama", type="member_of", to="council", role="first-class ruler", source="WILL20", evidence="single_reliable_source", level="reported", notes="First-class staff of office (The Will, 2020); membership inferred from grade, not stated. The December 2024 law rotates the council's chair among all first-class rulers (21st Century Chronicle)."),
    dict(frm="gangwari", type="member_of", to="council", role="first-class ruler", source="VON25", evidence="single_reliable_source", level="reported", notes="First class since 2004 (VON); membership inferred from grade, not stated. The December 2024 law rotates the council's chair among all first-class rulers (21st Century Chronicle)."),
    dict(frm="emirate", type="located_in", to=A("yola-south"), source="ADSPC", evidence="multiple_sources", level="well_documented", notes="The Lamido's palace, Yola South LGA (ADSPC); capital Yola since 1841 (Wikipedia). " + SEAT),
    dict(frm="emirate", type="located_in", to="@admin_units:state:adamawa", source="WPAD", evidence="multiple_sources", level="well_documented",
         notes="Wikipedia: Girei, Mayo-Belwa, Song, Yola North and Yola South LGAs; 21st Century Chronicle (Dec 2024): narrowed to Girei, Jimeta and Yola. The two accounts differ; LGA jurisdiction not linked."),
    dict(frm="hama", type="located_in", to=A("numan"), source="WILL20", evidence="multiple_sources", level="reported", notes="Palace at Numan; Wikipedia: Numan Chiefdom. " + SEAT),
    dict(frm="gangwari", type="located_in", to=A("ganye"), source="VON25", evidence="multiple_sources", level="well_documented", notes="Ganye Chiefdom, palace at Ganye. " + SEAT),
    dict(frm="mubi", type="located_in", to=A("mubi-north"), source="WPAD", evidence="single_reliable_source", level="reported", notes="Wikipedia: Mubi Emirate covers Mubi North and Mubi South; headquarters Mubi."),
    dict(frm="mubi", type="located_in", to=A("mubi-south"), source="WPAD", evidence="single_reliable_source", level="reported", notes="Wikipedia: Mubi Emirate covers Mubi North and Mubi South; headquarters Mubi."),
    dict(frm="huba", type="located_in", to=A("hong"), source="ADGOV", evidence="multiple_sources", level="well_documented", notes="Headquarters Hong. " + NEW),
    dict(frm="madagali", type="located_in", to=A("madagali"), source="ADGOV", evidence="multiple_sources", level="well_documented", notes="Headquarters Gulak. " + NEW),
    dict(frm="michika", type="located_in", to=A("michika"), source="ADGOV", evidence="multiple_sources", level="well_documented", notes="Headquarters Michika. " + NEW),
    dict(frm="fufore", type="located_in", to=A("fufore"), source="ADGOV", evidence="multiple_sources", level="well_documented", notes="Headquarters Fufore. " + NEW),
    dict(frm="gombi", type="located_in", to=A("gombi"), source="ADGOV", evidence="multiple_sources", level="well_documented", notes="Headquarters Gombi. " + NEW),
    dict(frm="yungur", type="located_in", to=A("song"), source="ADGOV", evidence="multiple_sources", level="reported", notes="Headquarters Dumne, an INEC ward of Song LGA (batch 058). " + NEW),
    dict(frm="maiha", type="located_in", to=A("maiha"), source="ADGOV", evidence="multiple_sources", level="well_documented", notes="Headquarters Maiha. " + NEW),
    dict(frm="huba", type="part_of", to="emirate", source="NAN25", evidence="single_reliable_source", level="reported", valid_to_year=2024,
         notes="Historical: under British rule the Huba leadership was an ungraded district headship under the Adamawa Emirate, until the Huba Chiefdom was created in December 2024 (NAN)."),
    dict(frm="emirate", type="associated_with", to="@ethnic_groups:fulani", role="Fulani emirate", source="WAE", evidence="multiple_sources", level="well_documented", notes="Founded in the Fulani jihad by Modibbo Adama (Wikipedia)."),
    dict(frm="hama", type="associated_with", to="@ethnic_groups:bachama", role="paramount ruler of the Bachama", source="WILL20", evidence="multiple_sources", level="well_documented", notes="The Will (2020); Adamawa State Government (Bwatiye community)."),
    dict(frm="gangwari", type="associated_with", to="@ethnic_groups:chamba", role="paramount ruler; Chamba majority", source="VON25", evidence="single_reliable_source", level="well_documented", notes="VON (2025): 'the Chamba people, who form the majority'."),
    dict(frm="huba", type="associated_with", to="@ethnic_groups:kilba", role="chiefdom of the Huba (Kilba)", source="NAN25", evidence="multiple_sources", level="well_documented", notes="NAN (2025): 'the Huba people'; the Atlas's Huba = Kilba."),
    dict(frm="michika", type="associated_with", to="@ethnic_groups:kamwe", role="chiefdom of the Kamwe", source="GMIC", evidence="single_reliable_source", level="reported", notes="The Guardian (2025): the coronation recognised 'the rich cultural heritage of the Kamwe people'."),
    dict(frm="yungur", type="associated_with", to="@ethnic_groups:yungur", role="chiefdom named after the Yungur", source="ADGOV", evidence="single_reliable_source", level="reported", notes="Name only; the sources do not describe its people."),
]
NAMES = [
    dict(record="emirate", name="Fombina", name_type="alternative", usage_notes="Wikipedia: the earliest name of the emirate, 'southlands'.", srcs=["WAE"]),
    dict(record="emirate", name="Fombina Emirate Council", name_type="alternative", usage_notes="Wikipedia: present name of the emirate council.", srcs=["WAE"]),
    dict(record="emirate", name="Laamorde Adamaawa", name_type="endonym", usage_notes="Wikipedia: Fula name.", srcs=["WAE"]),
    dict(record="emirate", name="Lamido Fombina", name_type="alternative", usage_notes="Wikipedia: title of the ruler, 'ruler of the southlands'; also Lamido Adamawa.", srcs=["WAE"]),
    dict(record="hama", name="Bachama Kingdom", name_type="alternative", usage_notes="The Will (2020).", srcs=["WILL20"]),
    dict(record="hama", name="Numan Chiefdom", name_type="alternative", usage_notes="Wikipedia's table of traditional states.", srcs=["WPAD"]),
    dict(record="gangwari", name="Ganye Chiefdom", name_type="alternative", usage_notes="VON (2025); Wikipedia.", srcs=["VON25"]),
    dict(record="huba", name="Hoba Emirate", name_type="alternative", usage_notes="Adamawa State Government's list (Dec 2024).", srcs=["ADGOV"]),
    dict(record="huba", name="Töl Huba", name_type="alternative", usage_notes="Title of its ruler (NAN, 2025).", srcs=["NAN25"]),
    dict(record="madagali", name="Ptil Madagali", name_type="alternative", usage_notes="Title of its ruler (The Guardian / NAN, 2025).", srcs=["GMAD"]),
    dict(record="madagali", name="Madagali Emirate", name_type="alternative", usage_notes="Adamawa State Government's list (Dec 2024).", srcs=["ADGOV"]),
    dict(record="michika", name="Mbege Ka Michika", name_type="alternative", usage_notes="Title of its ruler (The Guardian, 2025).", srcs=["GMIC"]),
    dict(record="michika", name="Michika Emirate", name_type="alternative", usage_notes="Adamawa State Government's list (Dec 2024).", srcs=["ADGOV"]),
    dict(record="maiha", name="Maiha Chiefdom", name_type="alternative", usage_notes="Adamawa State Government's list (Dec 2024).", srcs=["ADGOV"]),
    dict(record="council", name="Adamawa State Council of Chiefs and Emirs", name_type="alternative", usage_notes="Wikipedia (Muhammadu Barkindo Aliyu Musdafa).", srcs=["WBARK"]),
]
GAPS = [
    ("Adamawa: the 2024 chiefs law", "The text of the Adamawa State Chiefs (Appointment and Deposition) Law 2024 and the gazette creating the seven new emirates and chiefdoms were not found; their contents are known from news reports."),
    ("Adamawa: Adamawa Emirate's area after 2024", "Wikipedia gives Girei, Mayo-Belwa, Song, Yola North and Yola South; 21st Century Chronicle (Dec 2024) says the new law narrowed it to Girei, Jimeta and Yola. Not resolved."),
    ("Adamawa: Mubi Emirate's grade", "First class according to Wikipedia's table only; no second source found. Its present Emir is not recorded."),
    ("Adamawa: emirate or chiefdom", "The state government's list calls Hong, Madagali and Michika 'emirates' and Maiha a 'chiefdom'; Daily Trust, Wikipedia and the coronation reports say the opposite. The records follow the coronation reports and keep the other names."),
    ("Adamawa: Gombi, Yungur and Maiha rulers", "The titles and holders of these three third-class stools were not found in a readable source."),
    ("Adamawa: the council's chairmanship", "Who has chaired the Council of Chiefs since the rotation began is not documented."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Adamawa traditional institutions: Council of Chiefs; Adamawa Emirate, Hama Bachama, Gangwari Ganye, Mubi Emirate (first class); the seven emirates and chiefdoms of December 2024.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 059 — Adamawa: traditional institutions", "",
         "Researched 2026-09-30. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **{len(RECORDS)} records:**",
         "  - the **Adamawa State Council of Chiefs**. A December 2024 law ended the Lamido's permanent chairmanship; the chair now rotates among first-class rulers.",
         "  - **four first-class stools:**",
         "    - the **Adamawa Emirate** (Lamido Fombina), with its history since 1809 and the Lamido's installation in 2010",
         "    - the **Hama Bachama**, at Numan",
         "    - the **Gangwari Ganye**, first class since 2004",
         "    - the **Mubi Emirate**, whose grade rests on Wikipedia alone",
         "  - **the seven traditional states created in December 2024**, from the state government's own list:",
         "    - second class: **Huba** (Hong), **Madagali** (Gulak), **Michika** and **Fufore**",
         "    - third class: **Gombi**, **Yungur** (Dumne) and **Maiha**",
         "- **Links:** each stool to its seat LGA and, where a source says so, to its people (Fulani, Bachama, Chamba, Kilba, Kamwe, Yungur).",
         "- **Where the sources disagree, both versions are kept:**",
         "  - The state's list calls Hong, Madagali and Michika *emirates* and Maiha a *chiefdom*; the news and coronation reports say the opposite.",
         "  - The Adamawa Emirate's area after 2024: five LGAs (Wikipedia) or three (21st Century Chronicle).",
         "- **Page length:** the Adamawa Emirate page is long enough to be indexable, so the sitemap goes from 3,025 to 3,026 (tested). The other records are short and noindex.", ""]
    for key, ptype, name, slug, srcs, lvl in REC:
        t = TEXT[key]
        L += [f"## {name} ({words(t)} words)", ""] + [f"> {p}" if p else ">" for p in t.split("\n")] + [""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_059_adamawa_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_059_adamawa_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)} emirate_words={words(TEXT['emirate'])}")
