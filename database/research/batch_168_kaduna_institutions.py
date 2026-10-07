"""
Research batch 168 — Kaduna (Phase 3): traditional institutions. Researched 2026-10-07. Pattern: batches 144, 156, 162.

Seven polities, each with its seat and people:
  Zazzau Emirate (Zaria) — State House, 11 June 2026: Ahmed Nuhu Bamalli, '19th Emir since 1804', on the throne since
    7 October 2020; Katsina State Government, 8 June 2026; Wikipedia ('Zaria': capital of the Hausa kingdom of Zazzau,
    one of the original seven Hausa city-states; founded c. 1536 per Wikipedia).
  Atyap Chiefdom (Atak Njei, Zangon Kataf) — Ahmadu Bello University, 27 June 2026: 'Dominic Gambo Yahaya, Agwatyap III';
    Wikipedia ('Atyap Chiefdom': created 1995, first class 2007; 'Dominic Yahaya': staff of office 12 November 2016).
  Agworok (Kagoro) Chiefdom (Kaura) — State House, 16 September 2025: Dr Ufuwai Bonet, Chief of Kagoro, first-class
    chief, deputy chairman of the Kaduna State Council of Chiefs and Emirs; Wikipedia ('Kagoro': created 1905 by the
    British as one of three independent districts of Southern Zaria; first class; 'Ufuwai Bonet': Chief of Kagoro).
  Jema'a Emirate (Kafanchan) — Daily Trust, 12 April 2025: Dr Muhammad Isa Muhammadu II, 11th Emir of Jama'a; Wikipedia
    ('Kafanchan': founded by Usman Yabo from Kajuru at Jama'a Dororo; a vassal of Zaria; the 1999 succession and the
    creation of the Fantswam and Nikyob-Nindem chiefdoms in 2001) — stated neutrally.
  Birnin Gwari Emirate — Leadership (2026; the page shows no exact date): 'Alhaji Zubairu Maigwari II … Emir of
    Birnin-Gwari'; Wikipedia ('Birnin Gwari Emirate': under the Sokoto Caliphate system; upgraded to an emirate in 1981).
    Reported.
  Kajju (Bajju) Chiefdom — Wikipedia only ('Nuhu Bature': created 1995 after the 1992 Zangon Kataf crises; 'Luka Kogi
    Yabwat': Agwam Kajju II from 24 December 2022). Reported.
  Ham Chiefdom — Wikipedia only ('Ham people'; 'Jonathan Gyet Maude': Kpop Ham since 1974). Reported.
Not included (gaps): Fantswam, Nikyob-Nindem, Gwong, Adara, Kauru, Kajuru, Lere/Saminaka and other stools.
"""
import json, re, sys
import batch_144_ondo_institutions as I144

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = I144.NEWS
GOV = lambda t, org, d, u, n: dict(source_type="official_website", source_kind="government_publication", source_tier=1, title=t, organisation=org, publication_date=d, url=u,
                                   verification_status="verified", notes=n + f" Read {ACCESSED}.")
SOURCES = {
    "SHZ": GOV("President Tinubu congratulates Emir of Zazzau, Amb. Ahmed Bamalli, on 60th birthday", "The State House, Abuja", "2026-06-11",
               "https://statehouse.gov.ng/president-tinubu-congratulates-emir-of-zazzau-amb-ahmed-bamalli-on-60th-birthday/",
               "'Since ascending the exalted throne of Zazzau on October 7, 2020, as the 19th Emir since 1804'; ambassador to Thailand 2017–2020."),
    "KTZ": GOV("Governor Radda rejoices Emir of Zazzau on 60th birthday", "Katsina State Government", "2026-06-08",
               "https://katsinastate.gov.ng/2026/06/08/governor-radda-rejoices-emir-of-zazzau-on-60th-birthday/", "'Malam Ahmed Nuhu Bamalli, CFR, the 19th Emir of Zazzau'."),
    "SHK": GOV("President Tinubu celebrates Chief of Kagoro Ufuwai Bonet on 90th birthday", "The State House, Abuja", "2025-09-16",
               "https://statehouse.gov.ng/president-tinubu-celebrates-chief-of-kagoro-ufuwai-bonet-on-90th-birthday/",
               "'Dr Ufuwai Bonet, the Chief of Kagoro … a first-class chief and deputy chairman of the Kaduna State Council of Chiefs and Emirs'."),
    "ABU": dict(source_type="official_website", source_kind="official_website", source_tier=2, title="Agwatyap honours ABU Deputy Vice-Chancellor Prof Raymond Bako with traditional title of TsinTsat Atyap",
                organisation="Ahmadu Bello University, Zaria", publication_date="2026-06-27",
                url="https://abu.edu.ng/agwatyap-honours-abu-deputy-vice-chancellor-prof-raymond-bako-with-traditional-title-of-tsintsat-atyap/", verification_status="verified",
                notes=f"Investiture on Saturday 27 June 2026 'at the Agwatyap's Palace in Zango Kataf' by 'His Royal Highness Dominic Gambo Yahaya, Agwatyap III'. Read {ACCESSED}."),
    "DTJ": NEWS("Jama'a Emirate Sallah durbar unites diverse tribes in Kafanchan", "Daily Trust", "2025-04-12", "https://dailytrust.com/jamaa-emirate-sallah-durbar-unites-diverse-tribes-in-kafanchan/",
                "'Dr. Muhammad Isa Muhammadu II, the 11th Emir of Jama'a'; Christians and Muslims take part in the Sallah durbar in Kafanchan."),
    "LBG": NEWS("Emir hails Tinubu over reconstruction of Kaduna–Birnin Gwari road", "Leadership", None, "https://leadership.ng/emir-hails-tinubu-over-reconstruction-of-kaduna-birnin-gwari-road/",
                "'Alhaji Zubairu Maigwari II', 'The Emir of Birnin-Gwari'. The page shows no exact date (2026)."),
    "WZAR": WS("Zaria", "Formerly Zazzau; capital of the Zazzau Emirate Council and one of the original seven Hausa city-states; Zazzau thought to have been founded in or about 1536."),
    "WATC": WS("Atyap Chiefdom", "Traditional state of the Atyap; headquarters at Atak Njei, Zangon Kataf; created in 1995; first class status in 2007; ruler styled Agwatyap; four royal houses."),
    "WDY": WS("Dominic Yahaya", "Agwatyap III; received the staff of office on 12 November 2016 at the Atak Njei palace; predecessor Harrison Bungwon (Agwatyap II)."),
    "WKAG": WS("Kagoro", "'The Gworog (Kagoro) Chiefdom was created in 1905 by the British colonial administration as one of the three independent Districts in Southern Zaria province'; a first-class chiefdom; in Kaura LGA."),
    "WUB": WS("Ufuwai Bonet", "Monarch of the Gworok (Kagoro) Chiefdom, 'Chief of Kagoro (Gworok)'; deputy chairman of the state council of chiefs and emirs as of 2016."),
    "WKAF": WS("Kafanchan", "Usman Yabo led his people from Kajuru to Jama'a Dororo and founded an emirate; the Jama'a Emirate a vassal of the Zaria Emirate; after the emir's death in 1998 his son was turbaned in 1999 amid an uprising; in 2001 the Fantswam and Nikyob-Nindem chiefdoms were created; the emirate 'remains an institution of the Hausa-Fulani inhabitants'."),
    "WBGE": WS("Birnin Gwari Emirate", "A Hausa-Fulani emirate in Birnin Gwari LGA; came under the Sokoto Caliphate system in the early 19th century; in Zaria Province under colonial rule; upgraded to a full emirate in 1981; affected by banditry."),
    "WNB": WS("Nuhu Bature", "First paramount ruler of Kajju (also Bajju) Chiefdom, created in 1995 after the Zangon Kataf crises of 1992; died 18 December 2021."),
    "WLKY": WS("Luka Kogi Yabwat", "Agwam Kajju II, second paramount ruler of Kajju Chiefdom, appointed by Governor Nasir el-Rufai, in office from 24 December 2022."),
    "WJGM": WS("Jonathan Gyet Maude", "Kpop of the Ham (Jaba) Chiefdom since 1974; born 1938."),
    "WHAM": WS("Ham people", "'Ham rulers are called Kpop Ham. Since 1974, the Kpop Ham is … Dr. Jonathan Danladi Gyet Maude'."),
    "WJAB": WS("Jaba, Nigeria", "The LGA is named after 'Jaba', a Hausa word for the Ham, 'who occupy most of the local government'."),
}
TEXT = {
 "zazzau": """The Zazzau Emirate is the traditional state centred on Zaria, formerly called Zazzau, which Wikipedia describes as the capital of the Hausa kingdom of Zazzau, one of the original seven Hausa city-states, thought to have been founded in or about 1536. Its present ruler, Ahmed Nuhu Bamalli, a former banker and ambassador to Thailand, ascended the throne on 7 October 2020 as the 19th Emir since 1804, according to the State House (June 2026).""",
 "atyap": """The Atyap Chiefdom is the traditional state of the Atyap, with its headquarters and the palace of its ruler, the Agwatyap, at Atak Njei in Zangon Kataf LGA. According to Wikipedia, it was created in 1995 and raised to first-class status in 2007, and it has four royal houses, one for each of the four Atyap clans. Dominic Gambo Yahaya, Agwatyap III, received the staff of office on 12 November 2016 (Wikipedia) and was conferring titles at his palace in June 2026 (Ahmadu Bello University).""",
 "agworok": """The Agworok (Kagoro) Chiefdom is the traditional state of the Agworok, in Kaura LGA. According to Wikipedia, it was created in 1905 by the British colonial administration as one of three independent districts of Southern Zaria Province, and it is a first-class chiefdom. Its ruler, the Agwam Agworok, also called the Chief of Kagoro, is Dr Ufuwai Bonet; in September 2025 the State House marked his 90th birthday and described him as deputy chairman of the Kaduna State Council of Chiefs and Emirs. The start of his reign is not given in a source read.""",
 "jemaa": """The Jema'a (Jama'a) Emirate has its seat at Kafanchan. According to Wikipedia, it was founded by Usman Yabo, who led his people from Kajuru to a place they named Jama'a Dororo, and it was a vassal of the Zaria Emirate. Wikipedia records that after the emir's death in 1998 the turbaning of his son in 1999 led to an uprising in Kafanchan, and that in 2001 the state government created the Fantswam and Nikyob-Nindem chiefdoms, while the emirate remains an institution of the Hausa-Fulani. Dr Muhammad Isa Muhammadu II, the 11th Emir of Jama'a, led the Sallah durbar in Kafanchan in April 2025 (Daily Trust, which writes Jama'a). It is distinct from the Jama'a Emirate of Bauchi State, created in 2025 with its seat at Nabardo.""",
 "birnin-gwari": """The Birnin Gwari Emirate is a Hausa-Fulani emirate in Birnin Gwari LGA. According to Wikipedia, Birnin Gwari came under the Sokoto Caliphate system in the early 19th century, was part of Zaria Province under colonial rule, and was upgraded to a full emirate in 1981; the area has suffered repeated attacks by bandits. Its emir, Alhaji Zubairu Maigwari II, welcomed the reconstruction of the Kaduna–Birnin Gwari road in 2026 (Leadership).""",
 "kajju": """The Kajju (Bajju) Chiefdom is the traditional state of the Bajju. According to Wikipedia, it was created in 1995, after the Zangon Kataf crises of 1992, when separate chiefdoms for the Atyap and the Bajju were created from the Zazzau Emirate; its first ruler, Nuhu Bature Achi, died in December 2021, and Luka Kogi Yabwat, Agwam Kajju II, was appointed by Governor Nasir el-Rufai and took office on 24 December 2022. Its seat is not given in a source read.""",
 "ham": """The Ham Chiefdom is the traditional state of the Ham, whose ruler is styled the Kpop Ham. According to Wikipedia, Jonathan Danladi Gyet Maude, born in 1938, has been Kpop Ham since 1974. Jaba LGA is named after the Hausa name for the Ham, who occupy most of it (Wikipedia). A dated recent source on the chiefdom was not found.""",
}
ST = "@admin_units:state:kaduna"
LG = lambda l: f"@admin_units:lga:kaduna/{l}"
REC = [
    ("zazzau", "Zazzau Emirate", "zazzau-emirate", "emirate", ["SHZ", "KTZ", "WZAR"], "well_documented"),
    ("atyap", "Atyap Chiefdom", "atyap-chiefdom", "chiefdom", ["ABU", "WATC", "WDY"], "well_documented"),
    ("agworok", "Agworok Chiefdom", "agworok-chiefdom", "chiefdom", ["SHK", "WKAG", "WUB"], "well_documented"),
    ("jemaa", "Jema'a Emirate", "jemaa-emirate", "emirate", ["DTJ", "WKAF"], "well_documented"),
    ("birnin-gwari", "Birnin Gwari Emirate", "birnin-gwari-emirate", "emirate", ["LBG", "WBGE"], "reported"),
    ("kajju", "Kajju Chiefdom", "kajju-chiefdom", "chiefdom", ["WNB", "WLKY"], "reported"),
    ("ham", "Ham Chiefdom", "ham-chiefdom", "chiefdom", ["WHAM", "WJGM", "WJAB"], "reported"),
]
RECORDS = [dict(key=k, table="polities", evidence="multiple_sources", level=lvl,
                fields=dict(name=n, slug=s, polity_type=t, is_extant=1, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k]),
                srcs=[(x, n) for x in srcs]) for k, n, s, t, srcs, lvl in REC]
RECORDS[1]["fields"].update(founded_year=1995, founded_text="Created in 1995 (Wikipedia)", founded_precision="year")
RECORDS[2]["fields"].update(founded_year=1905, founded_text="Created in 1905 by the British colonial administration (Wikipedia)", founded_precision="year")
RECORDS[5]["fields"].update(founded_year=1995, founded_text="Created in 1995 (Wikipedia)", founded_precision="year")
SEAT = "Seat (not a statement of full jurisdiction)."
RELATIONS = [
    dict(frm="zazzau", type="located_in", to=LG("zaria"), source="WZAR", evidence="multiple_sources", level="well_documented", notes=f"Zaria, capital of the Zazzau Emirate Council (Wikipedia). {SEAT}"),
    dict(frm="atyap", type="located_in", to=LG("zangon-kataf"), source="WATC", evidence="multiple_sources", level="well_documented", notes=f"Atak Njei, Zangon Kataf (Wikipedia; Ahmadu Bello University). {SEAT}"),
    dict(frm="agworok", type="located_in", to=LG("kaura"), source="WKAG", evidence="single_reliable_source", level="well_documented", notes=f"Kagoro, in Kaura LGA (Wikipedia). {SEAT}"),
    dict(frm="jemaa", type="located_in", to=LG("jema-a"), source="WKAF", evidence="multiple_sources", level="well_documented", notes=f"Kafanchan (Wikipedia; Daily Trust). {SEAT}"),
    dict(frm="birnin-gwari", type="located_in", to=LG("birnin-gwari"), source="WBGE", evidence="single_reliable_source", level="well_documented", notes=f"Birnin Gwari LGA (Wikipedia). {SEAT}"),
    dict(frm="kajju", type="located_in", to=ST, source="WNB", evidence="single_reliable_source", level="reported", notes="Southern Kaduna State (Wikipedia); the seat is not given in a source read."),
    dict(frm="ham", type="located_in", to=LG("jaba"), source="WJAB", evidence="single_reliable_source", level="reported", notes="Jaba LGA, which the Ham occupy for the most part (Wikipedia); the seat is not given in a source read."),
    dict(frm="zazzau", type="associated_with", to="@ethnic_groups:hausa", role="Hausa kingdom of Zazzau, one of the seven Hausa city-states", source="WZAR", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Zaria)."),
    dict(frm="atyap", type="associated_with", to="@ethnic_groups:atyap", role="traditional state of the Atyap", source="WATC", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Atyap Chiefdom)."),
    dict(frm="agworok", type="associated_with", to="@ethnic_groups:agworok", role="traditional state of the Agworok (Kagoro)", source="WKAG", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Kagoro)."),
    dict(frm="jemaa", type="associated_with", to="@ethnic_groups:hausa", role="institution of the Hausa-Fulani of Jema'a", source="WKAF", evidence="single_reliable_source", level="reported", notes="Wikipedia (Kafanchan)."),
    dict(frm="jemaa", type="associated_with", to="@ethnic_groups:fulani", role="institution of the Hausa-Fulani of Jema'a", source="WKAF", evidence="single_reliable_source", level="reported", notes="Wikipedia (Kafanchan)."),
    dict(frm="birnin-gwari", type="associated_with", to="@ethnic_groups:hausa", role="Hausa-Fulani emirate", source="WBGE", evidence="single_reliable_source", level="reported", notes="Wikipedia (Birnin Gwari Emirate)."),
    dict(frm="birnin-gwari", type="associated_with", to="@ethnic_groups:fulani", role="Hausa-Fulani emirate; Fulani rulers installed under the Sokoto Caliphate", source="WBGE", evidence="single_reliable_source", level="reported", notes="Wikipedia (Birnin Gwari Emirate)."),
    dict(frm="kajju", type="associated_with", to="@ethnic_groups:bajju", role="traditional state of the Bajju", source="WNB", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Nuhu Bature; Luka Kogi Yabwat)."),
    dict(frm="ham", type="associated_with", to="@ethnic_groups:ham", role="traditional state of the Ham", source="WHAM", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Ham people; Jonathan Gyet Maude)."),
    dict(frm="jemaa", type="associated_with", to="zazzau", role="a vassal of the Zaria Emirate (Wikipedia)", source="WKAF", evidence="single_reliable_source", level="reported", notes="Wikipedia (Kafanchan)."),
]
NAMES = [
    dict(record="zazzau", name="Zaria Emirate", name_type="alternative", usage_notes="Wikipedia (Kafanchan) also calls it the Zaria Emirate.", srcs=["WKAF"]),
    dict(record="zazzau", name="Emir of Zazzau", name_type="alternative", usage_notes="Title of the ruler.", srcs=["SHZ"]),
    dict(record="atyap", name="Agwatyap", name_type="alternative", usage_notes="Title of the ruler (Tyap: 'monarch of the Atyap').", srcs=["WATC"]),
    dict(record="agworok", name="Kagoro Chiefdom", name_type="alternative", usage_notes="The Hausa name of the chiefdom (Wikipedia; the State House says 'Chief of Kagoro').", srcs=["WKAG", "SHK"]),
    dict(record="agworok", name="Gworok Chiefdom", name_type="spelling_variant", usage_notes="Wikipedia writes Gworog and Gworok.", srcs=["WKAG"]),
    dict(record="kajju", name="Bajju Chiefdom", name_type="alternative", usage_notes="Its earlier name in Wikipedia ('Kajju (also Bajju) Chiefdom').", srcs=["WNB"]),
    dict(record="kajju", name="Agwam Kajju", name_type="alternative", usage_notes="Title of the ruler.", srcs=["WLKY"]),
    dict(record="ham", name="Jaba Chiefdom", name_type="exonym", usage_notes="Wikipedia ('Ham (Jaba) Chiefdom'); Wikipedia calls the Hausa name Jaba derogatory.", srcs=["WJGM"]),
    dict(record="ham", name="Kpop Ham", name_type="alternative", usage_notes="Title of the ruler.", srcs=["WHAM"]),
]
GAPS = [
    ("Kaduna: other traditional stools", "The Fantswam, Nikyob-Nindem, Gwong, Adara, Asholio, Takad, Atyecarak and other Southern Kaduna chiefdoms, and the Kauru, Kajuru, Lere (Saminaka) and Zangon Kataf emirates, are not yet recorded; dated sources are needed."),
    ("Kaduna: Kajju and Ham rulers", "The Kajju (Bajju) and Ham chiefdoms are described from Wikipedia only; no dated 2025–26 source was found. The Leadership report on the Emir of Birnin Gwari shows no exact date."),
    ("Kaduna: Zazzau history", "The Zazzau Emirate's Fulani dynasty (from 1804), its four ruling houses and Queen Amina are not described from a source read here; the Zazzau kingdom's history before 1804 needs its own record."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kaduna traditional institutions: the Zazzau, Jema'a and Birnin Gwari emirates and the Atyap, Agworok (Kagoro), Kajju (Bajju) and Ham chiefdoms.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 168 — Kaduna: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **3 emirates:**",
         "  - **Zazzau** (Zaria): Emir Ahmed Nuhu Bamalli, the 19th Emir since 1804, on the throne since 7 October 2020 (State House, June 2026)",
         "  - **Jema'a** (Kafanchan): Emir Muhammad Isa Muhammadu II, the 11th Emir (Daily Trust, April 2025). The 1999 succession dispute and the 2001 creation of the Fantswam and Nikyob-Nindem chiefdoms are stated neutrally from Wikipedia.",
         "  - **Birnin Gwari**: Emir Zubairu Maigwari II (Leadership, 2026, no exact date). *Reported.*",
         "- **4 chiefdoms of Southern Kaduna:**",
         "  - **Atyap** (Atak Njei, Zangon Kataf): Agwatyap III Dominic Gambo Yahaya, confirmed June 2026 (Ahmadu Bello University)",
         "  - **Agworok (Kagoro)** (Kaura): Agwam Agworok Dr Ufuwai Bonet, 90 in September 2025 (State House); the chiefdom was created by the British in 1905",
         "  - **Kajju (Bajju)**: Agwam Kajju II Luka Kogi Yabwat, from December 2022. *Reported*: Wikipedia only.",
         "  - **Ham**: Kpop Ham Jonathan Danladi Gyet Maude, since 1974. *Reported*: Wikipedia only.",
         "- **Links:** each polity to its seat and to the people it serves (Hausa for Zazzau; Hausa and Fulani for Jema'a and Birnin Gwari; Atyap, Agworok, Bajju and Ham for the chiefdoms); Jema'a to Zazzau as its former suzerain.", "",
         "## The records", ""]
    for k, n, s, t, srcs, lvl in REC:
        L += [f"### {n} ({words(TEXT[k])} words; {lvl})", "", f"> {TEXT[k]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_168_kaduna_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_168_kaduna_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} sources={len(SOURCES)}")
