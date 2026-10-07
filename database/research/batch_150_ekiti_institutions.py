"""
Research batch 150 — Ekiti (Phase 3): traditional institutions. Researched 2026-10-07. Pattern: batches 131, 144.

  * Ado (the Ewi): Wikipedia 'Ado Ekiti' — founded by Ewi Awamaro, son of Biritikolu, who left Ile-Ife with his father
    and his uncle Oranmiyan; the Agbado tradition; 'mainly of the Ekiti sub-ethnic group'. Wikipedia 'Ekiti people': the
    Ewi represented the Ekiti obas at the 1939 conference at Ibadan. Peoples Gazette (23 Nov 2025): the governor
    congratulates Oba Rufus Adejugbe Aladesanmi III on his 80th birthday and 35th coronation anniversary.
  * Efon (the Alaaye): Wikipedia 'Efon-Alaaye' — dates to 1200 AD; founded by Obalufon Alaayemore, who reigned as Ooni of
    Ife; three ruling houses (Ogbenuote, Obologun, Asemojo); six high chiefs (iwara mefa); Oba Emmanuel Aladejare
    Agunsoye II, the 46th Alaaye. Ekiti State Government, 'Efon' LGA page (undated, read 7 Oct 2026): 'one recognized
    paramount ruler … Currently, the Oba is … Dr. Emmanuel Adesanya Aladejare (JP) CON, Agunsoye II, the Alaaye'.
  * Ikere (the Ogoga): AllAfrica (1 Aug 2025): Oba Samuel Adejimi Adu Alagbado, Ogoga of Ikere, named chairman of the
    State Council of Traditional Rulers. Peoples Gazette (14 Oct 2021): the Ogoga-in-Council rejected the government's
    recognition of the Olukere, Ganiyu Obasoyin, as an autonomous ruler of Odo-Oja; a 1987 commission had called the
    Olukere a priest of Olosunta. Wikipedia 'Olukere': the title predates the Ogoga's, and the Olukere claims to rule
    Ikere, though the town recognises the Ogoga.
  * Otun (the Oore): Ekiti State Government (2020, COVID-19 period): Oba Adekunle Adeayo Adeagbo of the Ile Iyaba ruling
    house given the staff of office, succeeding Oba James Adedapo Popoola (19-year reign). Ekiti State Government: the
    governor congratulated him on his appointment as South-West coordinator of the National Council of Traditional
    Rulers. New Telegraph (23 Jan 2026): 'Oore of Otun Ekiti and Permanent Chairman of Traditional Rulers in Moba Land'.
    Wikipedia 'Ekiti people': historians disagree over claims that the Oore is the most senior Ekiti oba.
Placement: Ado → Ado Ekiti; Efon-Alaaye → Efon; Ikere → Ikere; Otun → Moba (Wikipedia: Moba's headquarters is Otun).
"""
import json, re, sys
import batch_144_ondo_institutions as I144
import batch_148_ekiti_languages_peoples as P148

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = I144.NEWS
GOV = lambda t, u, n, d=None: dict(source_type="official_website", source_kind="government_publication", source_tier=1, title=t, organisation="Ekiti State Government",
                                   url=u, verification_status="verified", notes=n, **({"publication_date": d} if d else {}))
SOURCES = {
    "WADO": WS("Ado Ekiti", "Founded by Ewi Awamaro, son of Biritikolu, who left Ile-Ife with his father and his uncle Oranmiyan; Agbado named for elders left behind on the migration; people 'mainly of the Ekiti sub-ethnic group of the Yoruba'."),
    "WEKP": dict(P148.SOURCES["WEKP"], notes="Reused. The Ewi represented the Ekiti obas at the 1939 conference of Yoruba obas at Ibadan; historians (Atolagbe; Babatola) disagree over the Oore's claimed seniority."),
    "PG25E": NEWS("Gov. Oyebanji congratulates Ewi of Ado Ekiti on 80th birthday, 35th coronation anniversary", "Peoples Gazette", "2025-11-23",
                  "https://gazettengr.com/gov-oyebanji-congratulates-ewi-of-ado-ekiti-on-80th-birthday-35th-coronation-anniversary/",
                  "The governor congratulated the Ewi of Ado Ekiti, Oba Rufus Adejugbe Aladesanmi III, on his 80th birthday and 35th coronation anniversary (November 2025)."),
    "WEFON": WS("Efon-Alaaye", "Dates back to 1200 AD; founder Obalufon Alaayemore, who reigned as Ooni of Ife; three ruling houses (Ogbenuote, Obologun, Asemojo) in rotation; six high chiefs; Oba Emmanuel Aladejare Agunsoye II, the 46th Alaaye."),
    "GEFON": GOV("Efon Local Government", "https://www.ekitistate.gov.ng/about-ekiti/local-government/efon/",
                 "Efon LGA created 4 December 1996 with headquarters at Efon Alaaye; 'one recognized paramount ruler … Currently, the Oba is His Royal Majesty, Dr. Emmanuel Adesanya Aladejare (JP) CON, Agunsoye II, the Alaaye of Efon Kingdom'. Undated page, read 7 October 2026."),
    "AA25O": NEWS("Ekiti Govt Names Ogoga of Ikere As New Chair of Traditional Rulers' Council", "AllAfrica (from Nigerian press)", "2025-08-01", "https://allafrica.com/stories/202508010058.html",
                  "The Ekiti State Government appointed the Ogoga of Ikere Ekiti, Oba Samuel Adejimi Adu Alagbado, chairman of the State Council of Traditional Rulers."),
    "PG21": NEWS("Ikere-Ekiti chiefs reject Gov. Fayemi's recognition of Olukere", "Peoples Gazette", "2021-10-14", "https://gazettengr.com/ikere-ekiti-chiefs-reject-gov-fayemis-recognition-of-olukere/",
                 "The Ogoga-in-Council rejected the recognition of the Olukere, Ganiyu Obasoyin, as an autonomous traditional ruler of Odo-Oja; they cite a 1987 commission that called the Olukere 'just a priest of Olosunta'."),
    "WOLUK": WS("Olukere", "Traditional ruler of Odo Oja, Ikere-Ekiti; the title predates the Ogoga's; 'While the Olukere claims to be the ruler of Ikere, the town recognize the Ogoga as the traditional King of Ikere'; recently given official recognition."),
    "GOORE": GOV("COVID-19: Governor Fayemi Installs New Monarch at Governor's Office", "https://www.ekitistate.gov.ng/?p=20719",
                 "The governor presented the staff of office to the new Oore of Otun Ekiti, Oba Adekunle Adeayo Adeagbo, of the Ile Iyaba ruling house, succeeding Oba James Adedapo Popoola of the Imoya ruling house (19-year reign). During the COVID-19 restrictions of 2020; undated page."),
    "GOORE2": GOV("Gov. Oyebanji Congratulates Oore on appointment as S/W Coordinator of National Council of Traditional Rulers", "https://www.ekitistate.gov.ng/?p=29091",
                  "Oba Adekunle Adeagbo, 'paramount ruler of Otun Ekiti', appointed South-West coordinator of the National Council of Traditional Rulers of Nigeria. Undated page."),
    "NT26": NEWS("Oore Lauds Olumegbon On First Anniversary, Pledges Support", "New Telegraph", "2026-01-23", "https://newtelegraphng.com/oore-lauds-olumegbon-on-first-anniversary-pledges-support/",
                 "'The Oore of Otun Ekiti and Permanent Chairman of Traditional Rulers in Moba Land, Oba Adeagbo' (January 2026)."),
    "WMOBA": P148.SOURCES["WMOBA"],
}
TEXT = {
 "ado": """Ado is the traditional kingdom of Ado-Ekiti, the capital of Ekiti State, and its ruler is the Ewi of Ado. According to Wikipedia, tradition says the town was founded by Ewi Awamaro, son of Biritikolu, who had left Ile-Ife with his father and his uncle Oranmiyan; after a long migration Awamaro settled at Ulesun, the site of Ado-Ekiti, and elders who stayed behind on the way gave their camp the name Agbado. The Ewi represented the Ekiti obas at the conference of Yoruba obas held at Ibadan in 1939. The present Ewi, Oba Rufus Adejugbe Aladesanmi III, marked his 80th birthday and 35th coronation anniversary in November 2025 (Peoples Gazette).""",
 "efon": """Efon-Alaaye is a Yoruba kingdom of Ekiti State, and its ruler is the Alaaye of Efon. According to Wikipedia, it dates back to about 1200 AD and was founded by Obalufon Alaayemore, who also reigned as Ooni of Ife; he left Efon to his son, whose line still reigns. The Alaaye is chosen in rotation from three ruling houses, Ogbenuote, Obologun and Asemojo, and rules with six high chiefs who head the town's six quarters. The Ekiti State Government names Oba Emmanuel Adesanya Aladejare, Agunsoye II, as the present Alaaye and the one recognised paramount ruler of Efon LGA; Wikipedia calls him the 46th Alaaye.""",
 "ikere": """Ikere is a Yoruba kingdom of Ekiti State, and its ruler is the Ogoga of Ikere. In August 2025 the state government named the Ogoga, Oba Samuel Adejimi Adu Alagbado, chairman of the Ekiti State Council of Traditional Rulers (AllAfrica). The town also has an older title, the Olukere, whose holders, according to Wikipedia, claim to be the rulers of Ikere, while the town recognises the Ogoga. In October 2021 the Ogoga's chiefs rejected the state government's recognition of the Olukere as an autonomous ruler of the Odo-Oja quarter, citing a 1987 commission that had described the Olukere as a priest of Olosunta (Peoples Gazette).""",
 "otun": """Otun is a Yoruba kingdom of the Moba area of Ekiti State, and its ruler is the Oore of Otun. In 2020 the state government presented the staff of office to Oba Adekunle Adeayo Adeagbo of the Ile Iyaba ruling house, who succeeded Oba James Adedapo Popoola of the Imoya house after a nineteen-year reign (Ekiti State Government). He was later named South-West coordinator of the National Council of Traditional Rulers, and in January 2026 was styled the permanent chairman of the traditional rulers of Moba land (New Telegraph). Some historians present the Oore as the most senior Ekiti oba, a claim that others dispute (Wikipedia).""",
}
REC = [
    ("ado", "Ado Kingdom", "ado-ekiti-kingdom", ["WADO", "WEKP", "PG25E"], "well_documented"),
    ("efon", "Efon-Alaaye Kingdom", "efon-alaaye-kingdom", ["WEFON", "GEFON"], "well_documented"),
    ("ikere", "Ikere Kingdom", "ikere-kingdom", ["AA25O", "PG21", "WOLUK"], "well_documented"),
    ("otun", "Otun Kingdom", "otun-ekiti-kingdom", ["GOORE", "GOORE2", "NT26", "WEKP"], "well_documented"),
]
RECORDS = [dict(key=k, table="polities", evidence="multiple_sources", level=lvl,
                fields=dict(name=n, slug=s, polity_type="kingdom", is_extant=1, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k]),
                srcs=[(x, n) for x in srcs]) for k, n, s, srcs, lvl in REC]
RECORDS[1]["fields"].update(founded_year=1200, founded_text="Dates back to 1200 AD (Wikipedia)", founded_precision="circa")
LG = lambda l: f"@admin_units:lga:ekiti/{l}"
SEAT = "Seat (not a statement of full jurisdiction)."
RELATIONS = [
    dict(frm="ado", type="located_in", to=LG("ado-ekiti"), source="WADO", evidence="multiple_sources", level="well_documented", notes=f"Ado-Ekiti. {SEAT}"),
    dict(frm="efon", type="located_in", to=LG("efon"), source="GEFON", evidence="multiple_sources", level="well_documented", notes=f"Efon Alaaye, headquarters of Efon LGA (Ekiti State Government). {SEAT}"),
    dict(frm="ikere", type="located_in", to=LG("ikere"), source="PG21", evidence="multiple_sources", level="well_documented", notes=f"Ikere-Ekiti, in Ikere LGA (Peoples Gazette). {SEAT}"),
    dict(frm="otun", type="located_in", to=LG("moba"), source="WMOBA", evidence="multiple_sources", level="well_documented", notes=f"Otun, headquarters of Moba LGA (Wikipedia). {SEAT}"),
]
for k, n, s, srcs, lvl in REC:
    RELATIONS.append(dict(frm=k, type="associated_with", to="@ethnic_groups:yoruba", role="kingdom of the Ekiti, a Yoruba sub-group", source=srcs[0], evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Ekiti people; Ado Ekiti; Efon-Alaaye)."))
RELATIONS += [
    dict(frm="efon", type="associated_with", to="@polities:ife-kingdom", role="founded by Obalufon Alaayemore, who also reigned as Ooni of Ife", source="WEFON", evidence="single_reliable_source", level="reported", notes="Wikipedia (Efon-Alaaye)."),
    dict(frm="ado", type="associated_with", to="@polities:ife-kingdom", role="founders traced to Ile-Ife", source="WADO", evidence="single_reliable_source", level="reported", notes="Wikipedia (Ado Ekiti)."),
]
NAMES = [
    dict(record="ado", name="Ewi of Ado", name_type="alternative", usage_notes="Title of the ruler (Wikipedia; Peoples Gazette).", srcs=["WADO"]),
    dict(record="efon", name="Alaaye of Efon", name_type="alternative", usage_notes="Title of the ruler (Wikipedia; Ekiti State Government).", srcs=["WEFON"]),
    dict(record="ikere", name="Ogoga of Ikere", name_type="alternative", usage_notes="Title of the ruler (AllAfrica; Peoples Gazette).", srcs=["AA25O"]),
    dict(record="otun", name="Oore of Otun", name_type="alternative", usage_notes="Title of the ruler (Ekiti State Government).", srcs=["GOORE"]),
    dict(record="otun", name="Moba Kingdom", name_type="alternative", usage_notes="The Oore is styled chairman of the traditional rulers of Moba land (New Telegraph, 2026).", srcs=["NT26"]),
]
GAPS = [
    ("Ekiti: the Olukere of Ikere", "The Olukere's claim and the government's 2021 recognition of him for Odo-Oja were contested by the Ogoga's chiefs, who said a court case was pending (Peoples Gazette, 2021); its outcome was not found. The Olukere has no record of his own."),
    ("Ekiti: the Oore's seniority", "Wikipedia (Ekiti people) reports historians who present the Oore as the most senior Ekiti oba and others who dispute it; not decided here."),
    ("Ekiti: the Alaaye of Efon", "The Ekiti State Government page naming Oba Aladejare is undated; a dated 2025–26 source is needed."),
    ("Ekiti: other obaships", "The Elekole of Ikole, the Ajero of Ijero, the Olojudo of Ido, the Owa-Ooye of Okemesi, the Alawe of Ilawe and others need sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Ekiti traditional institutions: Ado (Ewi), Efon-Alaaye (Alaaye), Ikere (Ogoga) and Otun (Oore).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 150 — Ekiti: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **4 polities**, each with its present ruler from a dated or official source:",
         "  - **Ado** (the Ewi): Oba Rufus Adejugbe Aladesanmi III, 35th coronation anniversary in November 2025",
         "  - **Efon-Alaaye** (the Alaaye): Oba Emmanuel Aladejare, Agunsoye II, from the state government's LGA page (undated)",
         "  - **Ikere** (the Ogoga): Oba Samuel Adejimi Adu Alagbado, made chairman of the state's traditional council in August 2025. The Olukere's rival claim is stated neutrally, with its date.",
         "  - **Otun** (the Oore): Oba Adekunle Adeagbo, installed in 2020, confirmed January 2026. The debate over the Oore's seniority is stated neutrally.",
         "- **Links:** each polity to its seat LGA and the Yoruba; Ado and Efon to Ife through their founding traditions.", "",
         "## The records", ""]
    for k, n, s, srcs, lvl in REC:
        L += [f"### {n} ({words(TEXT[k])} words; {lvl})", "", f"> {TEXT[k]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_150_ekiti_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_150_ekiti_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
