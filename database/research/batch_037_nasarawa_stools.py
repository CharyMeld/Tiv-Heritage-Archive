"""
Research batch 037 — Nasarawa (Phase 3): traditional institutions, part 2. Researched 2026-09-26.

Records: the Nasarawa State Council of Traditional Rulers and Chiefs, and the 17 first-class stools
that batch 033 left as a gap (Daily Trust 2012 lists 22; batch 033 recorded Lafia, Keffi, Nasarawa,
Doma and Keana).

Sources:
  * Nasarawa State (Creation of Additional Chiefdoms) Law, 2003 (commenced 13 March 2003): primary
    legislation, read in full via the Internet Archive copy (2023) of LawNigeria's transcription (the
    live page is now paywalled). Creates 27 third-class chiefdoms, with titles and traditional
    headquarters; s.3(1)(h) on the presidency of LGA traditional councils. Tier 1.
  * Daily Trust (2012), "The story of Nasarawa State's 22 first class chiefs" (reused): the list, the
    number of stools per LGA, and the order of creation (3 in 1996; 6 under the military; Abaga Toni
    in June 2010; 3 more announced by Governor Al-Makura on 28 July [2012] to reach 22).
  * Daily Trust (c. 26 March 2019), "Nasarawa: New Emirs, chiefs emerge tomorrow": vacant stools of
    Lafia, Awe and Gom Mama (Farin Ruwa, Wamba LGA); Awe ruling houses; the selection procedure.
    Dated from its content (before the 29 May 2019 handover) and the 27 March 2019 photo report.
  * One news report per stool for seat and people (Blueprint, Daily Trust, Tribune, the Nasarawa
    Broadcasting Service, Education Monitor, The Eagle Online, Loyal Nigerian Lawyer), and Wikipedia
    for Karshi (Muhammadu Bako III; New Karshi) and the council chairmanship (Sidi Bage).
  * Seat LGAs are checked against the INEC ward list already in the archive (batch 032).
Handling:
  * No current office-holders are named except where the source's point is the stool's history.
  * Aren Eggon: Daily Trust (2012) says the stool became first class under the military after 1996;
    search results cite the Guardian (2022) for an upgrade by Governor Solomon Lar in June 1982, but
    that page could not be read — recorded as a gap, not used.
  * She Migili: palace at Agyaragu, which the state broadcaster calls the headquarters of Obi LGA's
    Jenkwe Development Area; INEC also has an 'Agyaragun Tofa' ward in Lafia LGA — noted.
  * Azara: no report found; seat placed only through the INEC ward 'Azara' of Awe LGA (reported).
  * Gom Mama: no source links the title to the Mama (Kantana) people explicitly — not linked.
  * The 2003 law's third-class 'Nassarawa Eggon Chiefdom (Aren Nassarawa Eggon)' is not assumed to be
    the Aren Eggon stool — gap.
  * LGAs are NOT linked to councils as 'part of' anything: no source gives the stools' jurisdictions;
    each stool is linked to the LGA of its seat ('located in'), which is what the sources support.
"""
import json, re, sys

ACCESSED = "2026-09-26"
SOURCES = {
    "LAW03": dict(source_type="government_publication", source_kind="legislation", source_tier=1,
                  title="Nasarawa State (Creation of Additional Chiefdoms) Law, 2003", organisation="Nasarawa State House of Assembly (transcribed by LawNigeria)",
                  publication_date="2003-03-13", url="https://laws.lawnigeria.com/2019/05/20/nasarawa-state-creation-of-additional-chiefdoms-law/",
                  verification_status="verified",
                  notes="Read via the Internet Archive (snapshot 2023-06-06); the live page is paywalled. Commencement 13 March 2003. s.3(1): creates 27 additional chiefdoms; First Schedule gives name, title, status (all 3rd class) and traditional headquarters, including Kwandere Chiefdom (Sangarin Kwandere, HQ Kwandere), Loko Chiefdom (Sarkin Loko, HQ Loko), Obi Chiefdom (Osuko of Obi, HQ Obi), Agwatashi (Osoho of Agwatashi), Assakio (Osakyo of Assakio), Nassarawa Eggon Chiefdom (Aren Nassarawa Eggon Chiefdom), Buh Chiefdom (Chu Buh, HQ Nakere), Agatu Chiefdom (Oche Agatu, HQ Guto); Second Schedule: ruling houses and selectors. s.3(1)(h): 'The President of the Local Government Traditional Council shall be the Paramount Ruler of the Chiefdom at the Local Government Headquarters where all the Traditional Rulers in such Local Government are of equal status.' s.3(2): existing chiefdoms remain valid."),
    "DT12": dict(source_type="news", source_kind="news", source_tier=3, title="The Story Of Nasarawa State's 22 First Class Chiefs", author="Hir Joseph",
                 organisation="Daily Trust", publication_date="2012-08-18", url="https://dailytrust.com/the-story-of-nasarawa-states-22-first-class-chiefs/",
                 verification_status="needs_corroboration", notes="Reused."),
    "DT19": dict(source_type="news", source_kind="news", source_tier=3, title="Nasarawa: New Emirs, chiefs emerge tomorrow", organisation="Daily Trust",
                 publication_details="Undated online; from its content, published shortly before the new emirs were named (Daily Trust photo report of 27 March 2019).",
                 url="https://dailytrust.com/nasarawa-new-emirs-chiefs-emerge-tomorrow/", verification_status="needs_corroboration",
                 notes="Three vacant first-class thrones: Lafia Emirate, Awe Emirate and Gom Mama Chiefdom in Farin Ruwa town, Wamba LGA. Awe: three ruling houses (Saidu, Mohammed Kiyabudi, Mohammed Dadi). Selection: the local government and the emirate council screen the princes; one or two names go to the Ministry for Local Government and Chieftaincy Affairs, which informs the governor, and 'the state's Council of Traditional Rulers meet and adopt or deny the name or names presented'."),
    "DT18": dict(source_type="news", source_kind="news", source_tier=3, title="Emir of Awe in Nasarawa state, Abubakar Umar II dies at 69", author="Victoria Bamas",
                 organisation="Daily Trust (NAN)", publication_date="2018-11-03", url="https://dailytrust.com/emir-of-awe-in-nasarawa-state-abubakar-umar-ii-dies-at-69/",
                 verification_status="needs_corroboration", notes="The first-class Emir of Awe, Abubakar Umar II, died aged 69 after 33 years on the throne; buried in Awe."),
    "BP22": dict(source_type="news", source_kind="news", source_tier=3, title="In Nasarawa, Eggon nation loses monarch Bala Angbazo at 89", organisation="Blueprint",
                 publication_date="2022-07-13", url="https://blueprint.ng/in-nasarawa-eggon-nation-loses-monarch-bala-angbazo-at-89/",
                 verification_status="needs_corroboration", notes="The late Aren Eggon, born 1933 in Wakama District, Nasarawa Eggon LGA, ascended the throne on 11 July 1981 and died after 41 years."),
    "LNL19": dict(source_type="news", source_kind="news", source_tier=3, title="MAN Nasarawa Branch Visits Oriye Rindre, Justice Nagogo", author="Halima Abiola",
                  organisation="The Loyal Nigerian Lawyer", publication_date="2019-08-03",
                  url="https://loyalnigerianlawyer.com/man-nasarawa-branch-visits-oriye-rindre-justice-nagogo-reiterates-call-for-better-magistrates-welfare/",
                  verification_status="needs_corroboration", notes="Courtesy visit to the Oriye Rindre, retired Justice Lawal Musa Nagogo, at his Wamba palace, 2 August 2019; he was the pioneer Grand Khadi of Nasarawa State (1996)."),
    "BP24": dict(source_type="news", source_kind="news", source_tier=3, title="Ohimege Opanda advocates peace, unity in Egbiraland", organisation="Blueprint",
                 publication_date="2024-04-10", url="https://blueprint.ng/ohimege-opanda-advocates-peace-unity-in-egbiraland/",
                 verification_status="needs_corroboration", notes="'The Ohimege Opanda of Egbura land in Nasarawa state'; 'the first class monarch'; his palace in Umaisha, Toto LGA."),
    "DT24": dict(source_type="news", source_kind="news", source_tier=3, title="The last 20 years have been impactful, impressive – Esu Karu", author="Baba Martins",
                 organisation="Daily Trust", publication_date="2024-06-28", url="https://dailytrust.com/the-last-20-years-have-been-impactful-impressive-esu-karu/",
                 verification_status="needs_corroboration", notes="Interview marking 20 years on the throne of the Esu Karu of New Karu; he speaks of the 'Karu Kingdom'; the Gbagyi are 'the most predominant citizens of the Chiefdom'; a Gbagyi Museum is being built."),
    "TR26": dict(source_type="news", source_kind="news", source_tier=3, title="Nasarawa govt announces new Osu Ajiri of Udege Chiefdom", author="Sandra Nwaokolo",
                 organisation="Tribune Online", url="https://tribuneonlineng.com/nasarawa-govt-announces-new-osu-ajiri-of-udege-chiefdom/",
                 publication_details="Dated 15 April 2026 on the page.", verification_status="needs_corroboration",
                 notes="The state government announced the new Osu Ajiri of Udege Chiefdom in Nasarawa LGA, 'a first-class traditional ruler of the Afo community', succeeding the late Halilu Bala-Usman (died 25 May 2025)."),
    "WBAKO": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Muhammadu Bako III", organisation="Wikipedia",
                  url="https://en.wikipedia.org/wiki/Muhammadu_Bako_III", verification_status="needs_corroboration",
                  notes="Gwandara; first-class Emir of New Karshi; senior member of the Nasarawa State Council of Traditional Rulers. Bako II crowned first Emir of New Karshi on 2 January 1981 with fourth-class status; third class 1 January 1997 (military administrator Ibrahim Abdullahi); second class August 2002 and first class May 2007 (Governor Abdullahi Adamu). Bako III appointed February 2016, staff of office August 2017."),
    "WKARSHI": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="New Karshi", organisation="Wikipedia",
                    url="https://en.wikipedia.org/wiki/New_Karshi", verification_status="needs_corroboration",
                    notes="Town in Karu LGA founded in the 1980s by Muhammadu Bako II; Karshi Development Area with secretariat at Uke; Emir of Karshi Emirate a first-class chief; Gwandara, Gbagyi, Gade, Bassa and Hausa."),
    "NBS24": dict(source_type="news", source_kind="news", source_tier=3, title="Obi Local Government Vows to Hold Perpetrators of Agyaragu Violence Accountable",
                  organisation="Nasarawa Broadcasting Service (state broadcaster)", publication_date="2024-11-06",
                  url="https://nbs.na.gov.ng/2024/11/06/obi-local-government-vows-to-hold-perpetrators-of-agyaragu-violence-accountable/",
                  verification_status="needs_corroboration", notes="Agyaragu, 'the headquarters of the Jenkwe Development Area' of Obi LGA; the Obi LGA chairman visited 'the palace of Zhe Migili'."),
    "EDM20": dict(source_type="news", source_kind="news", source_tier=3, title="Odyong Nyankpa, Joel Aninge, Clocks 20 0n Throne", author="Adam Isa Waziri",
                  organisation="Education Monitor News", publication_date="2020-10-28", url="https://educationmonitornews.com/odyong-nyankpa-joel-aninge-clocks-20-0n-throne/",
                  verification_status="needs_corroboration", notes="'The Odyong Nyankpa of Panda in Karu Local Government of Nasarawa State' marked 20 years on the throne."),
    "EAG26": dict(source_type="news", source_kind="news", source_tier=3, title="NASEMA distributes relief materials to disaster, banditry victims in Nasarawa",
                  author="Moronfolu Adeyemi", organisation="The Eagle Online", publication_date="2026-09-14",
                  url="https://theeagleonline.com.ng/nasema-distributes-relief-materials-to-disaster-banditry-victims-in-nasarawa/",
                  verification_status="needs_corroboration", notes="Distribution 'at the Chun Mada Palace in Akwanga LGA'; the Chun-Mada spoke."),
    "DT23": dict(source_type="news", source_kind="news", source_tier=3, title="Abdullahi Adamu condoles Nasarawa chiefdom over chief's death", author="Abubakar Sadiq Isah",
                 organisation="Daily Trust", publication_date="2023-07-27", url="https://dailytrust.com/abdullahi-adamu-condoles-nasarawa-chiefdom-over-chiefs-death/",
                 verification_status="needs_corroboration", notes="Gadabuke Chiefdom, Toto LGA: 'their first class traditional ruler, the late Gomo-BaBye of Gadabuke'; condolence visit at the late monarch's palace."),
    "WGADE": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Gade people", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Gade_people",
                  verification_status="needs_corroboration", notes="Byēní (a Gade person); Bàbyẹ̀ is the plural form."),
    "WKEA": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Keana", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Keana",
                 verification_status="needs_corroboration", notes="Reused (the Osuko of Obi among the most powerful Alago rulers)."),
    "WBAGE": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Sidi Bage", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Sidi_Bage",
                  verification_status="needs_corroboration", notes="As Emir of Lafia, Bage 'belongs to the Nassarawa State Council of Traditional Rulers and Chiefs and is its chairman'."),
    "INEC": dict(source_type="government_publication", title="Directory of Polling Units: Nasarawa State (Revised January 2015)", organisation="Independent National Electoral Commission (INEC)",
                 url="https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_Nasarawa.pdf",
                 verification_status="verified", notes="Reused: ward names Azara (Awe), Karshi I/II and Panda/Kare (Karu), Loko (Nasarawa), Shabu/Kwandere (Lafia), Umaisha (Toto), Obi (Obi)."),
}
SOURCES["ATLAS"] = dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                        url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified",
                        notes="Reused (Ebira entry, 1.A: Igbirra, Igbira, Egbira, Egbura).")

FC = "one of the 22 first-class traditional stools of Nasarawa State listed by Daily Trust in 2012"
# key: (polity_type, name, slug, lga (None = not placed), people slug or None, [sources+claims], text, summary)
STOOLS = {
 "awe": ("emirate", "Awe Emirate", "awe-emirate", "awe", None,
         [("DT12", "First class; raised to first class by the military administration after 1996"), ("DT18", "Abubakar Umar II, 33 years; buried in Awe"), ("DT19", "Vacant 2019; three ruling houses")],
         f"The Emir of Awe, seated at Awe, holds {FC}. Daily Trust records that the military administration that governed the new state after 1996 raised the stool to first class. Abubakar Umar II, described as a first-class monarch, died in November 2018 at 69 after 33 years on the throne and was buried in Awe. In 2019 Daily Trust named three ruling houses eligible for the vacant throne: Saidu, Mohammed Kiyabudi and Mohammed Dadi.",
         "The Awe Emirate, seated at Awe in Awe LGA, is a first-class traditional stool of Nasarawa State."),
 "aren-eggon": ("chiefdom", "Aren Eggon", "aren-eggon", "nasarawa-eggon", "eggon",
         [("DT12", "First class; Nasarawa-Eggon raised to first class by the military administration"), ("BP22", "Bala Angbazo installed 11 July 1981; 41 years")],
         f"The Aren Eggon is the traditional ruler of the Eggon and holds {FC}. Daily Trust names the Nasarawa Eggon stool among those the military administration raised to first class after the state's creation in 1996. Blueprint (2022) reports that the Aren Eggon Bala Angbazo, born in 1933 in Wakama District of Nasarawa Eggon LGA, ascended the throne on 11 July 1981 and died in 2022 after 41 years.",
         "The Aren Eggon is the first-class traditional ruler of the Eggon people of Nasarawa Eggon, Nasarawa State."),
 "oriye-rindre": ("chiefdom", "Oriye Rindre", "oriye-rindre", "wamba", "rindre",
         [("DT12", "First class (as 'Oriye Rindri')"), ("LNL19", "Palace at Wamba; retired Justice Lawal Musa Nagogo, pioneer Grand Khadi")],
         f"The Oriye Rindre (written Oriye Rindri by Daily Trust) is the traditional ruler of the Rindre and holds {FC}. The Loyal Nigerian Lawyer (2019) reports a visit to the Oriye Rindre at his palace in Wamba; the holder at that time, retired Justice Lawal Musa Nagogo, had been the first Grand Khadi of Nasarawa State in 1996.",
         "The Oriye Rindre is the first-class traditional ruler of the Rindre, with his palace at Wamba, Nasarawa State."),
 "ohimege-opanda": ("chiefdom", "Ohimege Opanda", "ohimege-opanda", "toto", "ebira",
         [("DT12", "First class"), ("BP24", "Ohimege Opanda of Egbura land; palace in Umaisha, Toto LGA"), ("ATLAS", "Egbura a name of the Ebira language")],
         f"The Ohimege Opanda holds {FC}. Blueprint (2024) calls him the Ohimege Opanda of Egbura land and a first-class monarch, with his palace at Umaisha in Toto LGA. The Egbura are a group of the Ebira; Blench's Atlas lists Egbura as a name of the Ebira language.",
         "The Ohimege Opanda is the first-class traditional ruler of the Egbura (Ebira), with his palace at Umaisha, Toto LGA, Nasarawa State."),
 "esu-karu": ("kingdom", "Esu Karu", "esu-karu", "karu", "gbagyi",
         [("DT12", "First class"), ("DT24", "Esu Karu of New Karu; Karu Kingdom; Gbagyi predominant; 20 years (2024)")],
         f"The Esu Karu holds {FC}. In a Daily Trust interview (2024) marking twenty years on the throne, the Esu Karu of New Karu spoke of the Karu Kingdom, described the Gbagyi as the most numerous of its people and said a Gbagyi Museum was being built.",
         "The Esu Karu is the first-class traditional ruler of the Karu Kingdom, seated at New Karu, whose people are mainly Gbagyi."),
 "osu-ajiri": ("chiefdom", "Osu Ajiri", "osu-ajiri", "nasarawa", "afo",
         [("DT12", "First class; one of three first-class rulers in Nasarawa LGA"), ("TR26", "Osu Ajiri of Udege Chiefdom, Nasarawa LGA; first-class ruler of the Afo")],
         f"The Osu Ajiri holds {FC}; Daily Trust describes him as one of the three first-class rulers in Nasarawa LGA. Tribune reports the state government's announcement of a new Osu Ajiri of Udege Chiefdom in Nasarawa LGA, 'a first-class traditional ruler of the Afo community', after the death in May 2025 of Halilu Bala-Usman, a former deputy governor of Plateau State.",
         "The Osu Ajiri of Udege Chiefdom is the first-class traditional ruler of the Afo, in Nasarawa LGA, Nasarawa State."),
 "karshi": ("emirate", "Karshi Emirate", "karshi-emirate", "karu", "gwandara",
         [("DT12", "First class ('Emir of Karshi')"), ("WBAKO", "Grading 1981–2007; Gwandara; council membership"), ("WKARSHI", "New Karshi, Karu LGA; development area")],
         f"The Emir of Karshi, seated at New Karshi in Karu LGA, holds {FC}. According to Wikipedia, New Karshi was founded around 1980 by Muhammadu Bako II, who was crowned its first emir on 2 January 1981 with fourth-class status. The stool became third class in 1997, second class in August 2002 and first class in May 2007. The emirs are Gwandara, and the town is also home to Gbagyi, Gade, Bassa and Hausa people. Wikipedia adds that the area forms the Karshi Development Area, with its secretariat at Uke.",
         "The Karshi Emirate, seated at New Karshi in Karu LGA, is a Gwandara emirate that became a first-class stool of Nasarawa State in 2007."),
 "she-migili": ("chiefdom", "She Migili", "she-migili", "obi", "migili",
         [("DT12", "First class ('She Migili')"), ("NBS24", "Palace of Zhe Migili at Agyaragu, HQ of Jenkwe Development Area, Obi LGA")],
         f"The She Migili (also written Zhe Migili) is the traditional ruler of the Migili and holds {FC}. The Nasarawa Broadcasting Service (2024) places the palace of the Zhe Migili at Agyaragu, which it calls the headquarters of the Jenkwe Development Area of Obi LGA. INEC's ward list also has an Agyaragun Tofa ward in Lafia LGA.",
         "The She (Zhe) Migili is the first-class traditional ruler of the Migili, with his palace at Agyaragu in Obi LGA, Nasarawa State."),
 "odyong-nyankpa": ("chiefdom", "Odyong Nyankpa", "odyong-nyankpa", "karu", "nyankpa",
         [("DT12", "First class"), ("EDM20", "Odyong Nyankpa of Panda, Karu LGA; 20 years on the throne (2020)")],
         f"The Odyong Nyankpa is the traditional ruler of the Nyankpa and holds {FC}. Education Monitor News (2020) calls him the Odyong Nyankpa of Panda in Karu LGA, and reports that the ruler had then been on the throne for twenty years. Panda is one of Karu's INEC wards (Panda/Kare).",
         "The Odyong Nyankpa is the first-class traditional ruler of the Nyankpa, seated at Panda in Karu LGA, Nasarawa State."),
 "azara": ("emirate", "Azara Emirate", "azara-emirate", "awe", None,
         [("DT12", "First class ('Emir of Azara')"), ("INEC", "Azara, a ward of Awe LGA")],
         f"The Emir of Azara holds {FC}. Azara is a ward of Awe LGA in INEC's list, and Daily Trust counts two first-class stools in Awe LGA. No further account of the emirate was found in a readable source.",
         "The Emir of Azara holds a first-class traditional stool of Nasarawa State; Azara is in Awe LGA."),
 "chun-mada": ("chiefdom", "Chun Mada", "chun-mada", "akwanga", "mada",
         [("DT12", "First class"), ("EAG26", "Chun Mada Palace in Akwanga LGA")],
         f"The Chun Mada is the traditional ruler of the Mada and holds {FC}. The Eagle Online (2026) reports an official relief distribution held at the Chun Mada Palace in Akwanga LGA.",
         "The Chun Mada is the first-class traditional ruler of the Mada, with his palace in Akwanga LGA, Nasarawa State."),
 "abaga-toni": ("chiefdom", "Abaga Toni", "abaga-toni", "kokona", "gwandara",
         [("DT12", "Created first class in June 2010 by Governor Aliyu Akwe Doma for the Gwandara of Kokona LGA")],
         f"The Abaga Toni holds {FC}. Daily Trust records that Governor Aliyu Akwe Doma created it as a first-class stool in June 2010, 'for the Gwandara people of Kokona LGA', the only first-class stool created in his term.",
         "The Abaga Toni is a first-class stool created in 2010 for the Gwandara of Kokona LGA, Nasarawa State."),
 "gomo-babye": ("chiefdom", "Gomo Babye", "gomo-babye", "toto", "gade",
         [("DT12", "First class"), ("DT23", "Gomo-Babye of Gadabuke; first-class ruler of Gadabuke Chiefdom, Toto LGA"), ("WGADE", "Babye: Gade plural")],
         f"The Gomo Babye holds {FC}. Daily Trust (2023) calls him the first-class traditional ruler of Gadabuke Chiefdom in Toto LGA, with his palace at Gadabuke. According to Wikipedia, Babye is the plural of the Gade people's name for a Gade person.",
         "The Gomo Babye of Gadabuke is the first-class traditional ruler of the Gade, in Toto LGA, Nasarawa State."),
 "osuko-obi": ("chiefdom", "Osuko of Obi", "osuko-of-obi", "obi", "alago",
         [("DT12", "First class"), ("LAW03", "Obi Chiefdom, Osuko of Obi, 3rd class, HQ Obi (2003)"), ("WKEA", "Among the most powerful Alago rulers")],
         f"The Osuko of Obi holds {FC}. The Nasarawa State (Creation of Additional Chiefdoms) Law of 2003 created the Obi Chiefdom, with the title Osuko of Obi, as a third-class chiefdom with its headquarters at Obi; it was first class by 2012. Wikipedia counts the Osuko of Obi, with the Osana of Keana and the Andoma of Doma, among the most powerful Alago rulers.",
         "The Osuko of Obi is an Alago traditional ruler at Obi, Nasarawa State: a chiefdom created in 2003 and a first-class stool by 2012."),
 "gom-mama": ("chiefdom", "Gom Mama", "gom-mama", "wamba", None,
         [("DT12", "First class"), ("DT19", "Gom Mama Chiefdom in Farin Ruwa town, Wamba LGA; vacant 2019")],
         f"The Gom Mama holds {FC}. Daily Trust (2019) places the Gom Mama Chiefdom in Farin Ruwa town, Wamba LGA, and lists it with Lafia and Awe as the three first-class thrones then vacant.",
         "The Gom Mama Chiefdom, at Farin Ruwa in Wamba LGA, is a first-class traditional stool of Nasarawa State."),
 "sarkin-loko": ("chiefdom", "Sarkin Loko", "sarkin-loko", "nasarawa", None,
         [("DT12", "First class"), ("LAW03", "Loko Chiefdom, Sarkin Loko, 3rd class, HQ Loko (2003)")],
         f"The Sarkin Loko holds {FC}. The 2003 Creation of Additional Chiefdoms Law created the Loko Chiefdom, with the title Sarkin Loko, as a third-class chiefdom with its headquarters at Loko; it was first class by 2012. Loko is a ward of Nasarawa LGA.",
         "The Sarkin Loko rules the Loko Chiefdom in Nasarawa LGA: created in 2003 and a first-class stool by 2012."),
 "sangarin-kwandere": ("chiefdom", "Sangarin Kwandere", "sangarin-kwandere", "lafia", None,
         [("DT12", "First class"), ("LAW03", "Kwandere Chiefdom, Sangarin Kwandere, 3rd class, HQ Kwandere (2003)")],
         f"The Sangarin Kwandere holds {FC}. The 2003 Creation of Additional Chiefdoms Law created the Kwandere Chiefdom, with the title Sangarin Kwandere, as a third-class chiefdom with its headquarters at Kwandere; it was first class by 2012. Kwandere is in Lafia LGA (INEC ward Shabu/Kwandere).",
         "The Sangarin Kwandere rules the Kwandere Chiefdom in Lafia LGA: created in 2003 and a first-class stool by 2012."),
}

COUNCIL = """The Nasarawa State Council of Traditional Rulers and Chiefs brings together the state's traditional rulers. Wikipedia names the Emir of Lafia as its chairman and the Emir of Karshi as a senior member. Daily Trust (2019) describes its part in filling a vacant stool: the local government and the emirate council screen the candidates, one or two names go to the Ministry for Local Government and Chieftaincy Affairs, which informs the governor, and the council then meets to adopt or reject the names.

At each LGA there is a local government traditional council. Under the Nasarawa State (Creation of Additional Chiefdoms) Law of 2003, where all the traditional rulers in an LGA are of equal status, its president is the ruler of the chiefdom at the LGA headquarters.

Daily Trust (2012) traces the growth of the first-class stools. At the state's creation in 1996 there were three, the emirs of Lafia, Keffi and Nasarawa. The military administration added Doma, Awe and Nasarawa Eggon; most of the rest were created under Governor Abdullahi Adamu (1999–2007); Governor Aliyu Akwe Doma added the Abaga Toni in 2010; and Governor Umaru Tanko Al-Makura announced three more in July 2012, bringing the total to 22. In the same year a critic put the number at 19. The 2003 law created 27 further chiefdoms, all third class; some, such as Obi, Loko and Kwandere, were first class by 2012. The law that establishes the state council itself was not found."""

RECORDS = [dict(key="council", table="polities", evidence="multiple_sources", level="reported",
                fields=dict(polity_type="traditional_council", name="Nasarawa State Council of Traditional Rulers and Chiefs", slug="nasarawa-state-council-of-traditional-rulers",
                            is_extant=1, summary="The Nasarawa State Council of Traditional Rulers and Chiefs, chaired by the Emir of Lafia, brings together the state's traditional rulers; Nasarawa had 22 first-class stools by 2012.",
                            description=COUNCIL),
                srcs=[("WBAGE", "Chaired by the Emir of Lafia"), ("DT19", "Role in adopting selections"), ("LAW03", "LGA traditional councils; 27 chiefdoms of 2003"),
                      ("DT12", "Growth of first-class stools 1996–2012"), ("WBAKO", "Emir of Karshi a senior member")])]
RELATIONS = [
    dict(frm="council", type="located_in", to="@admin_units:state:nasarawa", source="WBAGE", evidence="multiple_sources", level="reported", notes="State-level council."),
    dict(frm="@polities:lafia-emirate", type="member_of", to="council", role="chair (the Emir of Lafia)", source="WBAGE", evidence="single_reliable_source", level="reported",
         notes="Wikipedia (Sidi Bage): the Emir of Lafia is the council's chairman."),
]
for k, (ptype, name, slug, lga, people, srcs, text, summ) in STOOLS.items():
    ev = "multiple_sources" if len(srcs) > 1 else "single_reliable_source"
    RECORDS.append(dict(key=k, table="polities", evidence=ev, level="reported",
                        fields=dict(polity_type=ptype, name=name, slug=slug, is_extant=1, summary=summ, description=text), srcs=srcs))
    seat_src = next((s for s, _ in srcs if s != "DT12"), "DT12")
    if lga:
        RELATIONS.append(dict(frm=k, type="located_in", to=f"@admin_units:lga:nasarawa/{lga}", source=seat_src, evidence=ev, level="reported",
                              notes="Seat of the stool (not a statement of its jurisdiction)."))
    if people:
        RELATIONS.append(dict(frm=k, type="associated_with", to=f"@ethnic_groups:{people}", role="traditional ruler", source={"gomo-babye": "WGADE", "osuko-obi": "WKEA"}.get(k, seat_src),
                              evidence="single_reliable_source", level="reported", notes="As named in the sources (see the record's text)."))
RELATIONS.append(dict(frm="karshi", type="member_of", to="council", role="senior member", source="WBAKO", evidence="single_reliable_source", level="reported",
                      notes="Wikipedia (Muhammadu Bako III)."))
NAMES = [
    dict(record="oriye-rindre", name="Oriye Rindri", name_type="spelling_variant", usage_notes="Daily Trust (2012).", srcs=["DT12"]),
    dict(record="she-migili", name="Zhe Migili", name_type="spelling_variant", usage_notes="Nasarawa Broadcasting Service (2024).", srcs=["NBS24"]),
    dict(record="osu-ajiri", name="Udege Chiefdom", name_type="alternative", usage_notes="Tribune: 'Osu Ajiri of Udege Chiefdom'.", srcs=["TR26"]),
    dict(record="gomo-babye", name="Gadabuke Chiefdom", name_type="alternative", usage_notes="Daily Trust (2023).", srcs=["DT23"]),
]
GAPS = [
    ("Law establishing the Nasarawa State Council of Traditional Rulers", "Not found online; the 2003 Creation of Additional Chiefdoms Law assumes existing councils."),
    ("Which three stools Al-Makura upgraded in 2012", "Daily Trust says three were announced on 28 July 2012 but does not name them; a critic put the total at 19, not 22."),
    ("Aren Eggon: date of first-class status", "Search results cite the Guardian (2022) for an upgrade by Governor Solomon Lar in June 1982 (old Plateau State); Daily Trust (2012) puts it after 1996. The Guardian page could not be read."),
    ("Nassarawa Eggon Chiefdom (2003)", "The 2003 law created a third-class 'Nassarawa Eggon Chiefdom' (Aren Nassarawa Eggon Chiefdom); its relation to the Aren Eggon stool is not clear."),
    ("Gom Mama and the Mama (Kantana)", "The title suggests the Mama people, but no source found says so; not linked."),
    ("Emir of Azara", "No account of the emirate found; seat placed only through the INEC ward name."),
    ("Stool jurisdictions", "Which districts or villages each stool covers is not documented in the sources used; stools are linked only to the LGA of their seat."),
    ("Second- and third-class stools", "The 27 third-class chiefdoms of 2003 (e.g. Agwatashi, Assakio, Buh, Agatu) and any later ones are not recorded individually."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Nasarawa traditional institutions (2): the State Council of Traditional Rulers and Chiefs and the 17 remaining first-class stools; the 2003 chiefdoms law.")


def report():
    L = ["# Research batch 037 — Nasarawa: traditional institutions (2)", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         f"- **18 records:** the State Council of Traditional Rulers and Chiefs ({words(COUNCIL)} words) and the **17 first-class stools** batch 033 left open. With batch 033, all **22** stools in the 2012 Daily Trust list are now recorded.",
         "- **A primary legal source:** the Nasarawa State (Creation of Additional Chiefdoms) Law, 2003 (27 third-class chiefdoms, with titles and headquarters), read in full through an archived copy.",
         f"- **{len(RELATIONS)} links:**",
         "  - each stool to the LGA of its seat (16 of 17; seats checked against the INEC wards)",
         "  - 11 stools to their people",
         "  - the Emir of Lafia (chair) and the Emir of Karshi (senior member) to the council",
         "- **Coverage check:** the stools per LGA now match Daily Trust's count exactly (Lafia 2, Nasarawa 3, Wamba 2, Toto 2, Karu 3, Obi 2, Awe 2, all others 1).",
         "- All records are short (noindex; sitemap unchanged). Evidence is *reported*: each stool rests on Daily Trust plus one report or the 2003 law.", "",
         "## The 17 stools", "", "| Stool | Seat LGA | People | Sources |", "|---|---|---|---|"]
    for k, (ptype, name, slug, lga, people, srcs, text, summ) in STOOLS.items():
        L.append(f"| {name} | {lga or '—'} | {people or '—'} | {', '.join(s for s, _ in srcs)} |")
    L += ["", "## The council", ""] + [f"> {p}" if p else ">" for p in COUNCIL.split("\n")] + ["", "## Stool texts", ""]
    for k, v in STOOLS.items():
        L += [f"**{v[1]}.** {v[6]}", ""]
    L += ["## Not used", "",
          "- The Guardian (2022) on the Aren Eggon's 1982 upgrade (page could not be read; search snippet only).",
          "- Wikipedia's 'List of Nigerian traditional states' (it gives the Keana ruler's people as Agatu, which is wrong).",
          "- Blogs and heritage sites (kingdomsofnigeria.com, rexclarkeadventures.com), Facebook posts.",
          "- Names of most present office-holders (they change); ruling-house lists beyond Awe.", "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_037_nasarawa_stools.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_037_nasarawa_stools_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
