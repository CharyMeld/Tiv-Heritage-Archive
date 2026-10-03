"""
Research batch 125 — Oyo (Phase 3): traditional institutions. Researched 2026-10-02. Pattern: batches 113, 119.

  * Oyo Empire (historical): Wikipedia 'Oyo Empire' — the largest Yoruba-speaking state, prominent from the late 16th to
    the early 18th century, with Dahomey under its sway; founded in tradition by Oranmiyan, the first Alaafin; the
    secession of Ilorin from 1793; Ilorin destroyed the capital, Oyo-Ile, in 1835; the capital moved south to Ago d'Oyo
    under Atiba (d. 1859). 'Old Oyo': abandoned since 1835; now a national park in Oyo State (extending into Kwara).
  * Oyo Kingdom (the Alaafin): Wikipedia 'Alaafin' — title of the ruler of the Oyo Empire and of modern Oyo; Oba Akeem
    Owoade I succeeded Oba Lamidi Adeyemi III, the 45th Alaafin. The Gazette, 10 January 2025: Governor Makinde approved
    Abimbola Owoade after divination by the Oyomesi; Adeyemi III had died on 22 April 2022.
  * Olubadan of Ibadan: Wikipedia 'Olubadan' — the Oyo gained control of Ibadan in 1829; by 1850 a succession alternating
    between two lines; new palace at Oke Aremo inaugurated July 2024; Oba Owolabi Olakulehin, the 43rd Olubadan, crowned
    12 July 2024, died 7 July 2025; Oba Rashidi Adewolu Ladoja approved as the 44th on 21 August 2025. The Gazette,
    26 September 2025: Ladoja crowned the 44th Olubadan at Mapo Hall.
  * Soun of Ogbomoso: Wikipedia 'Ogbomosho' — founded in the mid-17th century by the hunter Ogunlola, whose compound
    became the Soun's palace. The Gazette, 18 February 2025: the Court of Appeal affirmed the selection of Oba Ghandi
    Afolabi Olaoye as Soun, reversing the High Court's nullification of 25 October 2023; the respondents said they would
    appeal to the Supreme Court.
Placement: the Olubadan and the Alaafin at state level (their palaces' LGAs are not given); the Soun in Ogbomosho
North, whose headquarters is Ogbomoso.
"""
import json, re, sys
import batch_123_oyo_languages_peoples as P123

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = lambda t, org, d, u, n: dict(source_type="news", source_kind="news", source_tier=3, title=t, organisation=org, publication_date=d, url=u,
                                    verification_status="needs_corroboration", notes=n)
SOURCES = {
    "WOYE": WS("Oyo Empire", "Largest Yoruba-speaking state; prominent from the late 16th to the early 18th century; Dahomey under its sway; founded in tradition by Oranmiyan, first Alaafin; Ilorin's secession from 1793; Oyo-Ile destroyed by Ilorin in 1835; capital moved south to Ago d'Oyo under Atiba (d. 1859)."),
    "WOLD": WS("Old Oyo", "Oyo-Ile (Katunga), capital of the Oyo Empire, abandoned since 1835; now a national park in Oyo State, extending into Kwara."),
    "WALA": WS("Alaafin", "Title of the ruler of the Oyo Empire and of modern Oyo ('owner of the palace'); the Alaafin and the Oyo Mesi formed the central government; Oba Akeem Owoade I succeeded Oba Lamidi Adeyemi III, the 45th Alaafin."),
    "GZALA": NEWS("Makinde approves Abimbola Owoade as new Alaafin of Oyo", "The Gazette (Nigeria)", "2025-01-10", "https://gazettengr.com/makinde-approves-abimbola-owoade-as-new-alaafin-of-oyo/",
                  "Governor Seyi Makinde approved Abimbola Owoade as Alaafin after recommendation and divination by the Oyomesi; Oba Lamidi Olayiwola Adeyemi III died on 22 April 2022."),
    "WOLU": WS("Olubadan", "Title of the ruler of Ibadan; the Oyo gained control in 1829; succession alternating between two lines by 1850; new palace at Oke Aremo (July 2024); Olakulehin (43rd) 2024–2025; Ladoja approved as 44th on 21 August 2025."),
    "GZOLU": NEWS("From governor to king: Ibadan agog as Rashidi Ladoja becomes 44th Olubadan", "The Gazette (Nigeria)", "2025-09-26",
                  "https://gazettengr.com/from-governor-to-king-ibadan-agog-as-rashidi-ladoja-becomes-44th-olubadan/",
                  "Rashidi Ladoja crowned the 44th Olubadan at Mapo Hall, Ibadan."),
    "WOGB": dict(P123.SOURCES["WOGB"], notes="Reused. Founded in the mid-17th century by the hunter Ogunlola, whose compound became the Soun's palace."),
    "GZSOU": NEWS("Appeal Court affirms Olaoye as Soun of Ogbomoso, rivals head for Supreme Court", "The Gazette (Nigeria)", "2025-02-18",
                  "https://gazettengr.com/appeal-court-affirms-olaoye-as-soun-of-ogbomoso-rivals-head-for-supreme-court/",
                  "The Court of Appeal (Ibadan) set aside the High Court's judgment of 25 October 2023 that had nullified Oba Ghandi Olaoye's selection, holding that the lower court lacked jurisdiction; the respondents' counsel said they would appeal to the Supreme Court."),
}
TEXT = {
 "empire": """The Oyo Empire was a Yoruba state in what is now western Nigeria and Benin, and became the largest Yoruba-speaking state; it was among the most powerful states of West Africa from the late 16th to the early 18th century, with the kingdom of Dahomey under its sway (Wikipedia). Tradition names Oranmiyan, a prince of Ile-Ife, as its founder and first Alaafin. Its strength rested on trade and cavalry, and its capital was Oyo-Ile, or Old Oyo. From 1793 the war camp of Ilorin broke away, and in 1835 Ilorin destroyed Oyo-Ile; the capital moved south to Ago d'Oyo, the present Oyo, under Alaafin Atiba, but the empire never regained its prominence (Wikipedia).""",
 "oyo": """The Oyo Kingdom is the traditional kingdom of Oyo town, whose ruler, the Alaafin ('owner of the palace'), heads the line of the Oyo Empire; after Oyo-Ile was destroyed in 1835 the capital moved to the present Oyo under Alaafin Atiba (Wikipedia). In the empire the Alaafin governed with the Oyo Mesi, a council of chiefs. Oba Lamidi Adeyemi III, the 45th Alaafin, died on 22 April 2022. In January 2025, after recommendation and divination by the Oyomesi, the state governor approved Abimbola Owoade as the new Alaafin (The Gazette), and Wikipedia names him as the present holder of the title.""",
 "ibadan": """The Olubadan, 'Lord of Ibadan', is the traditional ruler of Ibadan, the capital of Oyo State. According to Wikipedia, an army of Egba, Ijebu, Ife and Oyo people took the town around 1820 and the Oyo gained control of it in 1829; by 1850 an unusual succession had taken shape in which the throne alternates between two lines of chiefs, each of whom rises over decades through a ladder of titles. A new palace at Oke Aremo was inaugurated in July 2024. Oba Owolabi Olakulehin, the 43rd Olubadan, was crowned on 12 July 2024 and died on 7 July 2025 (Wikipedia). Rashidi Adewolu Ladoja, a former governor of Oyo State, was crowned the 44th Olubadan at Mapo Hall on 26 September 2025 (The Gazette).""",
 "ogbomoso": """The Soun of Ogbomoso is the traditional ruler of Ogbomoso, the second city of Oyo State. According to Wikipedia, the town was founded in the mid-17th century by the hunter Ogunlola, whose compound became the Soun's palace and the rallying point of the town, and whose authority the Alaafin recognised. The selection of Oba Ghandi Afolabi Olaoye as Soun was nullified by the Oyo State High Court on 25 October 2023, but in February 2025 the Court of Appeal set that judgment aside and affirmed his selection; the other side said it would appeal to the Supreme Court (The Gazette).""",
}
REC = [
    ("empire", "Oyo Empire", "oyo-empire", ["WOYE", "WOLD", "WALA"], "well_documented", dict(polity_type="empire", is_extant=0, founded_text="by the late 16th century (Wikipedia)", founded_precision="century",
                                                                                    ended_year=1835, ended_text="Oyo-Ile destroyed by Ilorin in 1835; the capital moved south (Wikipedia)", ended_precision="year")),
    ("oyo", "Oyo Kingdom", "oyo-kingdom", ["WALA", "WOYE", "GZALA"], "well_documented", dict(polity_type="kingdom", is_extant=1)),
    ("ibadan", "Olubadan of Ibadan", "olubadan-of-ibadan", ["WOLU", "GZOLU"], "well_documented", dict(polity_type="traditional_title", is_extant=1, founded_year=1829, founded_text="Oyo control from 1829; the two-line succession by 1850 (Wikipedia)", founded_precision="year")),
    ("ogbomoso", "Soun of Ogbomoso", "soun-of-ogbomoso", ["WOGB", "GZSOU"], "well_documented", dict(polity_type="traditional_title", is_extant=1, founded_text="mid-17th century, by Ogunlola (Wikipedia)", founded_precision="century")),
]
RECORDS = []
for key, name, slug, srcs, lvl, extra in REC:
    t = TEXT[key]
    f = dict(name=name, slug=slug, summary=t.split(". ")[0] + ".", description=t)
    f.update(extra)
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl, fields=f, srcs=[(s, name) for s in srcs]))
ST = "@admin_units:state:oyo"
RELATIONS = [
    dict(frm="empire", type="located_in", to=ST, source="WOLD", evidence="multiple_sources", level="well_documented", notes="Its capital, Oyo-Ile (Old Oyo), is in Oyo State; the empire extended far beyond (Wikipedia)."),
    dict(frm="oyo", type="located_in", to=ST, source="WALA", evidence="multiple_sources", level="well_documented", notes="Oyo town (four LGAs: Atiba, Oyo East, Oyo West, Afijio); the palace's LGA is not given in a source read."),
    dict(frm="ibadan", type="located_in", to=ST, source="WOLU", evidence="multiple_sources", level="well_documented", notes="Ibadan (11 LGAs); the palace at Oke Aremo — its LGA is not given in a source read."),
    dict(frm="ogbomoso", type="located_in", to="@admin_units:lga:oyo/ogbomosho-north", source="WOGB", evidence="single_reliable_source", level="reported", notes="Ogbomoso, the headquarters of Ogbomosho North (Wikipedia's Oyo LGA list). Seat (not a statement of full jurisdiction)."),
    dict(frm="oyo", type="associated_with", to="empire", role="the Alaafin's line continues that of the Oyo Empire", source="WALA", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Alaafin; Oyo Empire)."),
    dict(frm="ogbomoso", type="associated_with", to="empire", role="Ogunlola's authority was recognised by the Alaafin", source="WOGB", evidence="single_reliable_source", level="reported", notes="Wikipedia (Ogbomosho)."),
    dict(frm="ibadan", type="associated_with", to="empire", role="Ibadan was charged by Alaafin Atiba with protecting Oyo after 1835", source="WOYE", evidence="single_reliable_source", level="reported", notes="Wikipedia (Oyo Empire)."),
]
for k in ("empire", "oyo", "ibadan", "ogbomoso"):
    RELATIONS.append(dict(frm=k, type="associated_with", to="@ethnic_groups:yoruba", role="Yoruba polity", source={"empire": "WOYE", "oyo": "WALA", "ibadan": "WOLU", "ogbomoso": "WOGB"}[k],
                          evidence="multiple_sources", level="well_documented", notes="Wikipedia."))
NAMES = [
    dict(record="empire", name="Oyo-Ile", name_type="alternative", usage_notes="The empire's capital, Old Oyo (also Katunga); its name is sometimes used for the empire (Wikipedia).", srcs=["WOLD"]),
    dict(record="oyo", name="Alaafin of Oyo", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WALA"]),
    dict(record="ibadan", name="Olu of Ibadan", name_type="alternative", usage_notes="Wikipedia: 'Olubadan or Olu of Ibadan … lit. Lord of Ibadan'.", srcs=["WOLU"]),
]
GAPS = [
    ("Oyo: the Soun's case", "After the Court of Appeal's judgment (February 2025), the respondents said they would go to the Supreme Court; the outcome is not known from a source read."),
    ("Oyo: the Alaafin's coronation", "The approval (January 2025) is sourced; the coronation date and number (46th) are not confirmed in a source opened."),
    ("Oyo: other obaships", "The Oke-Ogun rulers (Okere of Saki, Aseyin of Iseyin, and others), the Ibarapa obas and the Oyo State Council of Obas need sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Oyo traditional institutions: the Oyo Empire, the Oyo Kingdom (Alaafin), the Olubadan of Ibadan and the Soun of Ogbomoso.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 125 — Oyo: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **4 polities:**",
         "  - the **Oyo Empire** (historical): the largest Yoruba state, until Oyo-Ile was destroyed in 1835",
         "  - the **Oyo Kingdom**, ruled by the Alaafin: Abimbola Owoade, approved in January 2025 after a vacancy of nearly three years",
         "  - the **Olubadan of Ibadan**: Rashidi Ladoja, crowned the 44th Olubadan on 26 September 2025, after Olakulehin died in July 2025",
         "  - the **Soun of Ogbomoso**: Ghandi Olaoye, whose selection the Court of Appeal affirmed in February 2025",
         "- **Handled with care:**",
         "  - **The Soun's court case** is stated neutrally: a High Court nullification (2023), then an appeal-court reversal (2025), with a Supreme Court appeal announced.",
         "  - **Dates are as published.** A search result had dated the appeal article to 2026; the article itself says 18 February 2025.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, name, slug, srcs, lvl, extra in REC:
        L += [f"## {name} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_125_oyo_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_125_oyo_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
