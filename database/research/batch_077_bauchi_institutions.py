"""
Research batch 077 — Bauchi (Phase 3): traditional institutions. Researched 2026-10-01. Pattern: batches 065 and 071.

Records (20 polities): the six older emirates (Bauchi, Katagum, Misau, Ningi, Jama'are, Dass), the thirteen emirates
created by the Bauchi State Chieftaincy (Appointment and Deposition) Law 2025, and the Zaar Chiefdom (Zaar Chiefdom
Law 2025). Governor Bala Mohammed signed both laws on Tuesday 21 October 2025 (The Sun, 21 Oct 2025; Premium Times
via allAfrica, 22 Oct 2025), bringing the state to 19 emirates and one chiefdom (The Sun; Arise News).

Evidence notes:
  * Headquarters of the new emirates and the chiefdom: Premium Times and The Sun give the same list (spellings differ:
    Burra/Bura, Katangan/Katanga Warji, Gadar/Gadan Maiwa, Jama'a/Jamma'a, Nabardo/Nabordo); the LGA of each seat is
    from Wikipedia's Bauchi State article, checked against INEC ward names (batch 076).
  * Ari: Wikipedia puts its seat, Gadar Maiwa, in Itas/Gadau LGA, but INEC's only 'Maiwa' ward is in Zaki LGA and
    Wikipedia's Ningi article lists Ari as a district of Ningi LGA. Linked to the state only (gap).
  * Zaar Chiefdom — both names kept (owner rule, batch 076b): the 2025 law repealed the 'Seyawa Chiefdom Law 2011' and
    the 'Creation of Sayawa Chiefdom (Amendment) Law 2015' (The Sun); the December 2024 announcement called it the
    Seyawa Chiefdom (21st Century Chronicle; Blueprint headline 'Sayawa Chiefdom'). Ruler's title Gun Zaar (2024
    announcement) / Gung Zaar (ThisDay column, Nov 2025). ThisDay's 'Safaya Chiefdom' is not recorded (gap).
  * Rulers: Bauchi — Rilwanu Suleiman Adamu since 2010 (Wikipedia); Katagum — Baba Umar Farouq,
    Dec 2017 (Daily Trust); Misau — Ahmed Suleiman, suspended and reinstated Oct 2020 (Daily Trust);
    Ningi — Haruna Yunusa Danyaya, 17th Emir, first class, Sept 2024 (WithinNigeria); Jama'are — Nuhu Ahmadu Wabi,
    10th Emir, Feb 2022 (THEWILL); Dass — Usman Bilyaminu Othman, Oct 2009 (Daily Trust; Wikipedia says his father
    died in 2019, but also that he reigned 32 years from 1977 — gap). New rulers named: Ari, Burra, Warji (Daily
    Trust, 24 Oct 2025, second class), Duguri (Arise, 25 Oct 2025), Zaar (ThisDay column, 14 Nov 2025).
"""
import json, re, sys

ACCESSED = "2026-10-01"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = lambda t, org, d, u, n, a=None: dict(source_type="news", source_kind="news", source_tier=3, title=t, organisation=org, publication_date=d, url=u,
                                            verification_status="needs_corroboration", notes=n, **({"author": a} if a else {}))
SOURCES = {
    "WBS": WS("Bauchi State", "Table of the state's emirates and chiefdom with headquarters and LGA (Bauchi, Katagum/Azare, Misau, Ningi, Jama'are, Dass, Burra (Ningi), Dambam, Darazo, Duguri/Yuli (Alkaleri), Gamawa, Giade, Toro, Warji/Katangar Warji, Ari/Gadar Maiwa (Itas/Gadau), Jama'a/Nabardo (Toro), Lame/Gumau (Toro), Bununu (Tafawa Balewa), Lere (Tafawa Balewa), Zaar Chiefdom/Mhrim (Tafawa Balewa))."),
    "WBE": WS("Bauchi Emirate", "Fula name Lamorde Bauchi; founded by Yaqubu dan Dadi, a Hausa Islamic scholar and student of Usman dan Fodio — the only definite non-Fulani ruler in the Sokoto Caliphate; Bauchi conquered 1809–1818; civil war of 1881 with Misau's intervention; Rilwanu Suleiman Adamu emir from 2010."),
    "WKAT": WS("Katagum", "Emirate founded around 1807 by Ibrahim Zakiyul Kalbi (Malam Zaki); Katagum town founded 1814 and now the headquarters of Zaki LGA; seat transferred to Azare in 1916; merged into Bauchi Province a decade later; Katagum LGA's headquarters is Azare."),
    "WNIN": WS("Ningi, Nigeria", "Ningi is a town, an LGA and an emirate; the emirate comprises Ningi and Warji LGAs; Yunusa Muhammadu Danyaya reigned from 1978 until his death on 25 August 2024; Ningi LGA has three districts: Ningi, Ari and Burra."),
    "WJAM": WS("Jamaare", "Emirate traditionally founded in 1811 by Muhammadu Wabi I; recognised by Sokoto in 1835; walls built in the 1850s; submitted to the British in 1903; transferred from Kano to Bauchi province in 1926."),
    "WDAS": WS("Dass, Nigeria", "Third-class chiefdom given in 1913 to Dukkurma, leader of the Jarawa; Usman Maleka from 1927; Bilyaminu Othman from 1977, under whom it became second class (1983) and a first-class emirate (1997); 'reigned for 32 years'; text says he died in September 2019 and was succeeded by his eldest son Usman Bilyaminu Othman II."),
    "PT25": NEWS("Bauchi Government Creates 13 New Emirates (List)", "Premium Times (via allAfrica)", "2025-10-22", "https://allafrica.com/stories/202510220515.html",
                 "Governor signed the 2025 Bauchi State Chieftaincy (Appointment and Deposition) Law and the Zaar Chiefdom Law 2025 on Tuesday; list of 13 new emirates and the Zaar Chiefdom with headquarters (Burra, Dambam, Darazo, Duguri–Yuli, Gamawa, Giade, Toro, Warji–Katangan Warji, Ari–Gadar Maiwa, Jama'a–Nabardo, Lame–Gumau, Bununu, Lere; Zaar Chiefdom–Mhrim).", "Abubakar Ahmadu Maishanu"),
    "SUN25": NEWS("Bauchi governor creates 13 new emirates, Zaar chiefdom", "The Sun", "2025-10-21", "https://thesun.ng/bauchi-governor-creates-13-new-emirates-zaar-chiefdom/",
                  "Laws signed on Tuesday bring the number of emirates to 19; the laws include one repealing the Seyawa Chiefdom Law 2011 and the Creation of Sayawa Chiefdom (Amendment) Law 2015, to provide for the Zaar Chiefdom Law 2025; same headquarters list (spellings Bura, Katanga Warji, Gadan-Maiwa, Jamma'a/Nabordo).", "Paul Orude"),
    "DT25": NEWS("Bauchi gov appoints emirs for new emirates", "Daily Trust", "2025-10-24", "https://dailytrust.com/bauchi-gov-appoints-emirs-for-new-emirates/",
                 "Muhammad Kilishi Musa first Emir of Ari, Ya'u Shehu Abubakar Emir of Burra, Ibrahim Samaila Boyi Emir of Warji — all second class; letters for Dambam, Duguri, Gamawa, Bununu, Giade, Jama'a, Lame and Toro to follow.", "Hassan Ibrahim"),
    "AR25": NEWS("Bauchi Governor Bala Mohammed Appoints Elder Brother As First Emir of Duguri", "Arise News", "2025-10-25", "https://www.arise.tv/bauchi-governor-bala-mohammed-appoints-elder-brother-as-first-emir-of-duguri",
                 "Adamu Mohammed-Duguri, the governor's elder brother, formerly District Head of Yelwan Duguri, appointed 1st Emir of Duguri Emirate in Alkaleri LGA; 'This brings to 19, the total number of emirate councils in Bauchi State and one chiefdom'.", "Armstrong Bakam"),
    "TD25": dict(source_type="news", source_kind="news", source_tier=3, title="The Audacity of Courage", organisation="ThisDay", publication_date="2025-11-14",
                 url="https://www.thisdaylive.com/2025/11/14/the-audacity-of-courage/", author="Emma Agu", verification_status="needs_corroboration",
                 notes="Opinion column. Brigadier-General Marcus Kokko Yake (rtd) received the letter affirming him as the first Gung Zaar; a first-class chiefdom carved out of the Bauchi Emirate Council; earlier agitation, including a self-declared Gung Zaar after 2019."),
    "TC24": NEWS("Bauchi governor creates Seyawa chiefdom", "21st Century Chronicle", "2024-12-09", "https://21stcenturychronicle.com/bauchi-governor-creates-seyawa-chiefdom/",
                 "Government decision: 'a chiefdom to be referred to as Seyawa Chiefdom shall be created out of the present Bauchi Emirate', headed by a chief designated Gun Zaar, chosen from the San Gami and San Gishi clans; headquarters in Tafawa Balewa LGA."),
    "BP24": NEWS("Bala approves creation of Sayawa Chiefdom from Bauchi emirate", "Blueprint", "2024-12-10", "https://blueprint.ng/bala-approves-creation-of-sayawa-chiefdom-from-bauchi-emirate/",
                 "Governor approved the creation of the Seyawa Chiefdom with headquarters in Tafawa Balewa LGA after committee reports.", "Mohammed Lawal"),
    "TD26": NEWS("15 District Heads Crowned in Toro, Lame and Jama'a Emirate Councils", "ThisDay", "2026-01-13", "https://www.thisdaylive.com/2026/01/13/15-district-heads-crowned-in-toro-lame-and-jamaa-emirate-councils/",
                 "Fifteen district heads crowned in the three newly created emirate councils in Toro LGA (Jama'a, Lame, Toro).", "Segun Awofadeji"),
    "DTK17": NEWS("How Baba Farouq emerged Emir of Katagum", "Daily Trust", "2017-12-24", "https://dailytrust.com.ng/how-baba-farouq-emerged-emir-of-katagum.html",
                  "Baba Umar Farouq, District Head of Shira and eldest son of the late Emir Kabir Umar, emerged as Emir of Katagum."),
    "DTM20": NEWS("Bauchi lifts suspension of Emir of Misau", "Daily Trust", "2020-10-14", "https://dailytrust.com/bauchi-lifts-suspension-of-emir-of-misau",
                  "State government lifted the suspension of the Emir of Misau, Ahmed Suleiman, imposed after a farmer–herder clash that killed 11 people; letter presented at his palace in Misau.", "Hassan Ibrahim"),
    "WN24": NEWS("Gov Bala appoints Haruna Danyaya as 17th Emir of Ningi", "WithinNigeria", "2024-09-01", "https://www.withinnigeria.com/news/2024/09/01/just-in-gov-bala-appoints-haruna-danyaya-as-17th-emir-of-ningi/",
                 "Haruna Yunusa Danyaya, eldest son of the late Emir and former Chiroman Ningi, confirmed as 17th Emir of Ningi with first-class status (statement by the governor's media adviser)."),
    "TW22": NEWS("Gov Bala Approves Appointment Of Nuhu Wabi As New Emir Of Jama'are", "THEWILL", "2022-02-28", "https://thewillnews.com/gov-bala-approves-appointment-of-nuhu-wabi-as-new-emir-of-jamaare/",
                 "Nuhu Ahmadu Wabi appointed 10th Emir of Jama'are after the death of Muhammadu Wabi III on 6 February 2022."),
    "DTD09": NEWS("New Emir of Dass speaks: 'I was the closest son to my father'", "Daily Trust", "2009-10-16", "https://dailytrust.com/new-emir-of-dass-speaks-i-was-the-closest-son-to-my-father/",
                  "Interview with the new Emir, Usman Bilyaminu Othman, eldest son of the late Emir; formerly Ciroma for about 15 years.", "Muhammad Abubakar"),
}

NEW = "one of the thirteen emirates created by the Bauchi State Chieftaincy (Appointment and Deposition) Law 2025, which Governor Bala Mohammed signed on 21 October 2025 (Premium Times; The Sun)"
TEXT = {
 "bauchi": """The Bauchi Emirate, in Fula Lamorde Bauchi, is the traditional state centred on the city of Bauchi. According to Wikipedia it was founded in the early 19th century by Yaqubu dan Dadi, a Hausa Islamic scholar and student of Usman dan Fodio, whose forces conquered the region between 1809 and 1818; Yaqubu was the only definite non-Fulani ruler in the Sokoto Caliphate. In 1881 a succession dispute led to a civil war in which the Emir of Misau intervened. Rilwanu Suleiman Adamu has been Emir since 2010 (Wikipedia). In 2025 the Zaar Chiefdom was carved out of the emirate (ThisDay; 21st Century Chronicle).""",
 "katagum": """The Katagum Emirate was founded around 1807 by Ibrahim Zakiyul Kalbi (Malam Zaki), a commander in the Fulani jihad who in 1812 destroyed Ngazargamu, the capital of the Kanem–Bornu Empire (Wikipedia). Katagum town, founded in 1814, is now the headquarters of Zaki LGA; in 1916 the emirate's seat moved to Azare, the headquarters of Katagum LGA, and about ten years later the emirate became part of Bauchi Province (Wikipedia). Baba Umar Farouq, District Head of Shira and eldest son of the late Emir Muhammad Kabir Umar, emerged as Emir in December 2017 (Daily Trust).""",
 "misau": """The Misau Emirate is one of the six emirates Bauchi State had before 2025 (Wikipedia). Its forces intervened in the Bauchi civil war of 1881 under Emir Salih (Wikipedia). Its Emir, Ahmed Suleiman, was suspended by the state government after a farmer–herder clash in which 11 people died and was reinstated in October 2020, when the letter lifting the suspension was presented at his palace in Misau (Daily Trust, 14 October 2020).""",
 "ningi": """The Ningi Emirate is centred on the town of Ningi; Wikipedia describes it as comprising Ningi and Warji LGAs, and Ningi LGA as having three districts, Ningi, Ari and Burra; Warji, Ari and Burra are among the emirates created in 2025 (Premium Times). Yunusa Muhammadu Danyaya reigned from 1978 until his death on 25 August 2024 (Wikipedia), and his eldest son Haruna Yunusa Danyaya, formerly Chiroman Ningi, was confirmed as the 17th Emir with first-class status (WithinNigeria, 1 September 2024).""",
 "jamaare": """The Jama'are Emirate was traditionally founded in 1811 by Muhammadu Wabi I, a leader in the Fulani jihad, and recognised by the Sultan of Sokoto in 1835 (Wikipedia). Its emirs built the walls of Jama'are town in the 1850s; Muhammadu Wabi II submitted to the British in 1903, and the emirate was moved from Kano to Bauchi province in 1926 (Wikipedia). After Muhammadu Wabi III died on 6 February 2022, his son Nuhu Ahmadu Wabi was appointed the 10th Emir (THEWILL, 28 February 2022).""",
 "dass": """The Dass Emirate grew from a third-class chiefdom created by the colonial government in 1913 for Dukkurma, leader of the Jarawa who had settled at the foot of Mbula hill; under his grandson Bilyaminu Othman, ruler from 1977, it became second class in 1983 and a first-class emirate in 1997 (Wikipedia). Usman Bilyaminu Othman, his eldest son and formerly the Ciroma, spoke to Daily Trust as the new Emir in October 2009. Wikipedia gives his father's death as September 2019, which does not agree with its own 32-year reign or the 2009 interview.""",
 "burra": f"""The Burra Emirate, headquartered at Burra in Ningi LGA, is {NEW}. Ya'u Shehu Abubakar received his letter of appointment as the second-class Emir of Burra (Daily Trust, 24 October 2025). Burra was formerly one of the three districts of Ningi LGA (Wikipedia).""",
 "dambam": f"""The Dambam Emirate, headquartered at Dambam, is {NEW}. In October 2025 its emir's letter of appointment was among those still to be presented (Daily Trust).""",
 "darazo": f"""The Darazo Emirate, headquartered at Darazo, is {NEW}. Its first emir was not named in a source read.""",
 "duguri": f"""The Duguri Emirate, headquartered at Yuli in Alkaleri LGA, is {NEW}. On 24 October 2025 the governor appointed his elder brother, Adamu Mohammed-Duguri, a retired Assistant Comptroller-General of Customs and formerly District Head of Yelwan Duguri, as its first Emir (Arise News).""",
 "gamawa": f"""The Gamawa Emirate, headquartered at Gamawa, is {NEW}. In October 2025 its emir's letter of appointment was among those still to be presented (Daily Trust).""",
 "giade": f"""The Giade Emirate, headquartered at Giade, is {NEW}. In October 2025 its emir's letter of appointment was among those still to be presented (Daily Trust).""",
 "toro": f"""The Toro Emirate, headquartered at Toro, is {NEW}. In January 2026 district heads were crowned in its emirate council and in those of Jama'a and Lame, the two other new emirates in Toro LGA (ThisDay).""",
 "warji": f"""The Warji Emirate, headquartered at Katangar Warji (Katangan or Katanga Warji in the press) in Warji LGA, is {NEW}. Ibrahim Samaila Boyi received his letter of appointment as its second-class Emir (Daily Trust, 24 October 2025). Wikipedia describes Warji LGA as formerly part of the Ningi Emirate.""",
 "ari": f"""The Ari Emirate, headquartered at Gadar Maiwa (Gadan-Maiwa in The Sun), is {NEW}. Muhammad Kilishi Musa was appointed its first Emir, with second-class status (Daily Trust, 24 October 2025). Sources disagree on the LGA of its seat: Wikipedia's state article gives Itas/Gadau, while its Ningi article lists Ari as a district of Ningi LGA.""",
 "jamaa": f"""The Jama'a Emirate, headquartered at Nabardo in Toro LGA, is {NEW}. In January 2026 district heads were crowned in its emirate council (ThisDay). It is unrelated to Jema'a LGA in Kaduna State.""",
 "lame": f"""The Lame Emirate, headquartered at Gumau in Toro LGA, is {NEW}. In January 2026 district heads were crowned in its emirate council (ThisDay).""",
 "bununu": f"""The Bununu Emirate, headquartered at Bununu in Tafawa Balewa LGA, is {NEW}. In October 2025 its emir's letter of appointment was among those still to be presented (Daily Trust).""",
 "lere": f"""The Lere Emirate, headquartered at Lere in Tafawa Balewa LGA, is {NEW}. It is unrelated to Lere LGA in Kaduna State. Its first emir was not named in a source read.""",
 "zaar": """The Zaar Chiefdom, headquartered at Mhrim in Tafawa Balewa LGA, was created by the Zaar Chiefdom Law 2025, signed on 21 October 2025; the same package repealed the Seyawa Chiefdom Law 2011 and the Sayawa Chiefdom amendment law of 2015 (The Sun). The government had announced a 'Seyawa Chiefdom' carved out of the Bauchi Emirate in December 2024, headed by a chief titled Gun Zaar and chosen from the San Gami and San Gishi clans (21st Century Chronicle). A ThisDay column describes the end of a half-century of agitation, some of it violent, and the presentation of the instrument of office to Brigadier-General Marcus Kokko Yake (rtd) as the first Gung Zaar of a first-class chiefdom.""",
}
REC = [  # key, name, slug, srcs, level, extra fields
    ("bauchi", "Bauchi Emirate", "bauchi-emirate", ["WBE", "WBS"], "well_documented", dict(founded_text="conquest of 1809–1818 (Wikipedia)", founded_year=1809, founded_precision="circa")),
    ("katagum", "Katagum Emirate", "katagum-emirate", ["WKAT", "WBS", "DTK17"], "well_documented", dict(founded_text="around 1807 (Wikipedia)", founded_year=1807, founded_precision="circa")),
    ("misau", "Misau Emirate", "misau-emirate", ["WBS", "DTM20", "WBE"], "well_documented", {}),
    ("ningi", "Ningi Emirate", "ningi-emirate", ["WNIN", "WBS", "WN24"], "well_documented", {}),
    ("jamaare", "Jama'are Emirate", "jamaare-emirate", ["WJAM", "WBS", "TW22"], "well_documented", dict(founded_text="1811 by tradition; recognised by Sokoto in 1835 (Wikipedia)", founded_year=1811, founded_precision="year")),
    ("dass", "Dass Emirate", "dass-emirate", ["WDAS", "WBS", "DTD09"], "well_documented", dict(founded_text="chiefdom 1913; first-class emirate 1997 (Wikipedia)", founded_year=1913, founded_precision="year")),
    ("burra", "Burra Emirate", "burra-emirate", ["PT25", "SUN25", "DT25", "WBS"], "well_documented", {}),
    ("dambam", "Dambam Emirate", "dambam-emirate", ["PT25", "SUN25", "DT25", "WBS"], "well_documented", {}),
    ("darazo", "Darazo Emirate", "darazo-emirate", ["PT25", "SUN25", "WBS"], "well_documented", {}),
    ("duguri", "Duguri Emirate", "duguri-emirate", ["PT25", "SUN25", "AR25", "WBS"], "well_documented", {}),
    ("gamawa", "Gamawa Emirate", "gamawa-emirate", ["PT25", "SUN25", "DT25", "WBS"], "well_documented", {}),
    ("giade", "Giade Emirate", "giade-emirate", ["PT25", "SUN25", "DT25", "WBS"], "well_documented", {}),
    ("toro", "Toro Emirate", "toro-emirate", ["PT25", "SUN25", "TD26", "WBS"], "well_documented", {}),
    ("warji", "Warji Emirate", "warji-emirate", ["PT25", "SUN25", "DT25", "WBS"], "well_documented", {}),
    ("ari", "Ari Emirate", "ari-emirate", ["PT25", "SUN25", "DT25", "WBS", "WNIN"], "well_documented", {}),
    ("jamaa", "Jama'a Emirate", "jamaa-emirate", ["PT25", "SUN25", "TD26", "WBS"], "well_documented", {}),
    ("lame", "Lame Emirate", "lame-emirate", ["PT25", "SUN25", "TD26", "WBS"], "well_documented", {}),
    ("bununu", "Bununu Emirate", "bununu-emirate", ["PT25", "SUN25", "DT25", "WBS"], "well_documented", {}),
    ("lere", "Lere Emirate", "lere-emirate", ["PT25", "SUN25", "WBS"], "well_documented", {}),
    ("zaar", "Zaar Chiefdom", "zaar-chiefdom", ["SUN25", "PT25", "TC24", "BP24", "TD25", "WBS"], "well_documented", {}),
]
NEW_KEYS = ["burra", "dambam", "darazo", "duguri", "gamawa", "giade", "toro", "warji", "ari", "jamaa", "lame", "bununu", "lere"]
RECORDS = []
for key, name, slug, srcs, lvl, extra in REC:
    t = TEXT[key]
    f = dict(polity_type="chiefdom" if key == "zaar" else "emirate", name=name, slug=slug, is_extant=1, summary=t.split(". ")[0] + ".", description=t)
    if key in NEW_KEYS or key == "zaar":
        f.update(founded_text="21 October 2025, by state law (Premium Times; The Sun)", founded_year=2025, founded_precision="year")
    f.update(extra)
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources", level=lvl, fields=f, srcs=[(s, name) for s in srcs]))

B = lambda l: f"@admin_units:lga:bauchi/{l}"
SEAT = "Seat of the traditional state (not a statement of its full jurisdiction)."
HQ = "Headquarters per the 2025 list (Premium Times; The Sun); LGA per Wikipedia's Bauchi State table"
SEATS = [  # key, lga, level, evidence, notes
    ("bauchi", "bauchi", "well_documented", "Capital at Bauchi (Wikipedia, Bauchi Emirate and Bauchi State)."),
    ("katagum", "katagum", "well_documented", "Seat at Azare since 1916, the headquarters of Katagum LGA (Wikipedia). Katagum town itself is in Zaki LGA."),
    ("misau", "misau", "well_documented", "Emir's palace in Misau (Daily Trust, 2020); Wikipedia's state table."),
    ("ningi", "ningi", "well_documented", "Ningi town, LGA and emirate (Wikipedia)."),
    ("jamaare", "jama-are", "well_documented", "Jama'are town (Wikipedia)."),
    ("dass", "dass", "well_documented", "Dass town (Wikipedia)."),
    ("burra", "ningi", "well_documented", HQ + "; Burra is a district of Ningi LGA (Wikipedia, Ningi) and an INEC ward of Ningi LGA (Burra / Kyata)."),
    ("dambam", "damban", "well_documented", HQ + "; INEC ward Dambam is in the LGA (archive spelling Damban)."),
    ("darazo", "darazo", "well_documented", HQ + "."),
    ("duguri", "alkaleri", "well_documented", "Headquarters Yuli (Premium Times; The Sun); in Alkaleri LGA (Arise News; Wikipedia); INEC ward Yuli/Lim is in Alkaleri."),
    ("gamawa", "gamawa", "well_documented", HQ + "."),
    ("giade", "giade", "well_documented", HQ + "."),
    ("toro", "toro", "well_documented", HQ + "; ThisDay (2026) places the Toro, Lame and Jama'a councils in Toro LGA."),
    ("warji", "warji", "well_documented", HQ + "; INEC ward Katanga is in Warji LGA."),
    ("jamaa", "toro", "well_documented", "Headquarters Nabardo (Premium Times; Nabordo in The Sun); in Toro LGA (Wikipedia; ThisDay 2026)."),
    ("lame", "toro", "well_documented", "Headquarters Gumau (Premium Times; The Sun); in Toro LGA (Wikipedia; ThisDay 2026); INEC ward Lame is in Toro LGA."),
    ("bununu", "tafawa-balewa", "reported", HQ + "; INEC has a Bununu ward in Tafawa Balewa LGA and Bununu Central/South wards in Dass LGA."),
    ("lere", "tafawa-balewa", "well_documented", HQ + "; INEC wards Lere North and Lere South are in Tafawa Balewa LGA."),
    ("zaar", "tafawa-balewa", "well_documented", "Headquarters Mhrim (Premium Times; The Sun); in Tafawa Balewa LGA (21st Century Chronicle 2024; Blueprint 2024; ThisDay 2025; Wikipedia)."),
]
RELATIONS = [dict(frm=k, type="located_in", to=B(l), source="WBS", evidence="multiple_sources", level=lvl, notes=n + " " + SEAT) for k, l, lvl, n in SEATS] + [
    dict(frm="ari", type="located_in", to="@admin_units:state:bauchi", source="PT25", evidence="multiple_sources", level="well_documented",
         notes="Headquarters Gadar Maiwa (Premium Times; Gadan-Maiwa in The Sun). Its LGA is disputed: Itas/Gadau (Wikipedia, Bauchi State) or Ningi (Wikipedia, Ningi, which lists Ari as a district); INEC's only Maiwa ward is in Zaki LGA."),
    dict(frm="zaar", type="associated_with", to="@ethnic_groups:zaar", role="chiefdom of the Zaar (Sayawa) people", source="TD25", evidence="multiple_sources", level="well_documented",
         notes="ThisDay (2025): 'the Zaar people's quest for their own kingdom'; 21st Century Chronicle (2024): the chief is designated Gun Zaar."),
]
RELATIONS.append(dict(frm="zaar", type="associated_with", to="bauchi", role="carved out of the Bauchi Emirate", source="TC24", evidence="multiple_sources", level="well_documented",
                      notes="'a chiefdom to be referred to as Seyawa Chiefdom shall be created out of the present Bauchi Emirate' (21st Century Chronicle, 2024); 'carved out of the Bauchi Emirate Council' (ThisDay, 2025)."))

NAMES = [
    dict(record="bauchi", name="Lamorde Bauchi", name_type="alternative", usage_notes="Fula name (Wikipedia).", srcs=["WBE"]),
    dict(record="zaar", name="Seyawa Chiefdom", name_type="historical",
         usage_notes="Name of the chiefdom in the Seyawa Chiefdom Law 2011, repealed in 2025, and in the government's December 2024 announcement (The Sun; 21st Century Chronicle). Blench's Atlas notes that the Saya terms are now considered derogatory; the people's own name is Zaar.",
         srcs=["SUN25", "TC24"]),
    dict(record="zaar", name="Sayawa Chiefdom", name_type="historical",
         usage_notes="Spelling in the title of the Creation of Sayawa Chiefdom (Amendment) Law 2015, repealed in 2025 (The Sun), and in the press (Blueprint, 2024). Blench's Atlas notes that the Saya terms are now considered derogatory; the people's own name is Zaar.",
         srcs=["SUN25", "BP24"]),
    dict(record="zaar", name="Gung Zaar", name_type="alternative", usage_notes="Title of the ruler (ThisDay, 2025); 'Gun Zaar' in the 2024 announcement (21st Century Chronicle).", srcs=["TD25", "TC24"]),
    dict(record="burra", name="Bura Emirate", name_type="spelling_variant", usage_notes="Spelling in The Sun (2025). Not to be confused with the Bura people of Borno and Adamawa.", srcs=["SUN25"]),
    dict(record="jamaa", name="Jema'a Emirate", name_type="spelling_variant", usage_notes="Spelling in Arise News (2025); The Sun has Jamma'a. Not the Jema'a of Kaduna State.", srcs=["AR25", "SUN25"]),
]
GAPS = [
    ("Bauchi: first emirs", "No first emir was found for Dambam, Darazo, Gamawa, Giade, Bununu or Lere. Toro, Jama'a and Lame have emirs (ThisDay, January 2026), but their names were not found. A news snippet mentions emirs of Bununu and Lere, but no readable article was found."),
    ("Bauchi: Ari's LGA", "Gadar Maiwa, the seat of the Ari Emirate, is placed in Itas/Gadau LGA by Wikipedia's Bauchi State table. Wikipedia's Ningi article lists Ari as a district of Ningi LGA, and INEC's only 'Maiwa' ward is in Zaki LGA. The emirate is linked to the state only."),
    ("Bauchi: Dass succession date", "Daily Trust interviewed Usman Bilyaminu Othman as the new Emir in October 2009. Wikipedia says his father reigned 32 years from 1977, which gives 2009, but it also gives his death as September 2019. No death date is recorded."),
    ("Bauchi: grades and present rulers", "The sources give grades for these rulers: Ningi first class (2024); Dass first class (1997); Ari, Burra and Warji second class (2025); Zaar first class (a ThisDay column). No official gazette of grades was found. Misau's emir is dated to 2020 and Bauchi's to Wikipedia; neither is confirmed by a 2025–26 source."),
    ("Bauchi: Zaar Chiefdom names", "A ThisDay column (Nov 2025) also writes 'Safaya Chiefdom' / 'Safaya Kingdom'. This is not found elsewhere and is not recorded. The 2025 law is the 'Zaar Chiefdom Law'; the repealed laws used Seyawa and Sayawa. Both of those names are kept, with usage notes, under the owner's rule."),
    ("Bauchi: parent emirates of the 2025 emirates", "The sources do not say which older emirate each new emirate was taken from, except the Zaar Chiefdom (from Bauchi). Ningi formerly covered Warji LGA and the Burra and Ari districts (Wikipedia), but no part_of links are recorded."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Bauchi traditional institutions: 19 emirates (6 older, 13 created October 2025) and the Zaar Chiefdom.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 077 — Bauchi: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **{len(RECORDS)} traditional states.** Since 21 October 2025, Bauchi has 19 emirates and 1 chiefdom (The Sun; Arise News).",
         "  - **The 6 older emirates:**",
         "    - **Bauchi**: Yaqubu, 1809–18",
         "    - **Katagum**: c. 1807; seat at Azare since 1916",
         "    - **Misau**",
         "    - **Ningi**: 17th emir, 2024",
         "    - **Jama'are**: 1811; 10th emir, 2022",
         "    - **Dass**: chiefdom 1913; first-class emirate 1997",
         "  - **The 13 emirates created in 2025** are Burra, Dambam, Darazo, Duguri, Gamawa, Giade, Toro, Warji, Ari, Jama'a, Lame, Bununu and Lere. Each record gives the headquarters from the official list as reported by Premium Times and The Sun.",
         "    - First emirs are named for Ari, Burra and Warji (second class) and for Duguri (the governor's elder brother).",
         "  - **The Zaar Chiefdom**: seat at Mhrim, Tafawa Balewa LGA.",
         "    - **Both names are kept** (your rule from 076b): Seyawa Chiefdom and Sayawa Chiefdom are recorded as former names, with usage notes. They are the names in the repealed 2011 and 2015 laws and in the 2024 announcement.",
         "    - The ruler's title is recorded as Gung Zaar / Gun Zaar.",
         "    - The first ruler is named: Brig.-Gen. Marcus Kokko Yake (rtd).",
         "- **Links:**",
         "  - 19 seat links to LGAs. Ari is linked to the state only, because its LGA is disputed.",
         "  - The Zaar Chiefdom is linked to the Zaar people and to the Bauchi Emirate, from which it was carved.",
         "- **Handled with care:**",
         "  - **Dates on rulers:** each ruler is dated to its source.",
         "  - **Dass:** Wikipedia's 2019 death date is not used, because it contradicts both the 2009 interview and the 32-year reign.",
         "  - **Same-name places:** the notes keep Jema'a and Lere apart from their Kaduna namesakes, and 'Bura Emirate' apart from the Bura people.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, name, slug, srcs, lvl, extra in REC:
        t = TEXT[key]
        L += [f"## {name} ({words(t)} words; {lvl})", "", f"> {t}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_077_bauchi_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_077_bauchi_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
