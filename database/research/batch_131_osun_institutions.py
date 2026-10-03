"""
Research batch 131 — Osun (Phase 3): traditional institutions. Researched 2026-10-02. Pattern: batches 119, 125.

  * Ife (the Ooni): Wikipedia 'List of rulers of Ife' — the Ooni of Ile-Ife; the dynasty predates Oduduwa (7th–9th
    century, per historians); four ruling houses (Lafogido, Osinkola, Ogboru, Giesi); Adeyeye Enitan Ogunwusi of the
    Giesi house elected on 26 October 2015. 'Adeyeye Enitan Ogunwusi': Ojaja II, the 51st Ooni. 'Ifẹ': ancient city,
    founded between 1000 and 500 BC; by AD 900 an important emporium producing sophisticated art. AllAfrica (8 December
    2025): the 10th anniversary of his installation.
  * Ijesaland (the Owa Obokun): Wikipedia 'Ijesha' — Ilesa, the cultural capital, founded about 1250 by Owaluse, a
    descendant of Oduduwa; ruler styled Owa Obokun Adimula of Ijesaland. The Gazette, 27 December 2024: Governor Adeleke
    approved Adesuyi Haastrup as the new Owa Obokun after Oba Adekunle Aromolaran's death on 11 September (the year,
    2024, follows from the article's date and context). ThisDay
    (5 January 2025): the Ofokutu and Fajemisin families of the Bilaro Olu-Odo ruling house alleged that the selection
    was manipulated.
  * Osogbo (the Ataoja): Wikipedia 'Osogbo' — the Ataoja, 'the one that stretches out his hand and takes the fish'; the
    founding move under Oba Larooye from Ipole Omu to Osogbo, by the Osun grove; list of Ataojas from Larooye (d. 1760) to
    Oba Jimoh Oyetunji Laaroye II (2010–present); Osogbo hosts the Osun-Osogbo festival. Sahara Reporters (14 June 2026)
    names Oba Jimoh Oyetunji Olanipekun, Larooye II, as Ataoja.
  * Ila (the Orangun): Wikipedia 'Ila Orangun' — capital of an ancient city-state in the Igbomina area; the Orangun is
    the paramount ruler, assisted by the Obaala; the present Orangun is Oba Abdul Wahab Olukayode Oyedotun Bibiire I.
Placement: each in the LGA whose headquarters is the seat (Wikipedia's Osun LGA table): Ile-Ife → Ife Central; Ilesa →
Ilesha East; Osogbo → Osogbo; Ila Orangun → Ila.
"""
import json, re, sys
import batch_129_osun_languages_peoples as P129

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = lambda t, org, d, u, n: dict(source_type="news", source_kind="news", source_tier=3, title=t, organisation=org, publication_date=d, url=u,
                                    verification_status="needs_corroboration", notes=n)
SOURCES = {
    "WROI": WS("List of rulers of Ife", "The Ooni of Ile-Ife; the dynasty predates Oduduwa; four ruling houses (Lafogido, Osinkola, Ogboru, Giesi); Adeyeye Enitan Ogunwusi (Giesi) elected 26 October 2015."),
    "WOGW": WS("Adeyeye Enitan Ogunwusi", "Ojaja II, born 17 October 1974; the 51st Ooni; of the Giesi ruling house."),
    "WIFE": dict(P129.SOURCES["WIFE"], notes="Reused. Ancient Yoruba city founded between 1000 and 500 BC; by AD 900 an important West African emporium producing sophisticated art."),
    "AA25": NEWS("Obasanjo, Governors Honour Ooni, First Lady At 10th Anniversary, Installation", "AllAfrica (from Nigerian press)", "2025-12-08", "https://allafrica.com/stories/202512080109.html",
                 "The 10th anniversary of the Ooni's installation, December 2025."),
    "WIJE": dict(P129.SOURCES["WIJE"], notes="Reused. Ilesa founded about 1250 by Owaluse, descendant of Oduduwa; the ruler styled Owa Obokun Adimula of Ijesaland."),
    "GZOWA": NEWS("Gov Adeleke approves Adesuyi Haastrup as new Owa Obokun of Ijeshaland", "The Gazette (Nigeria)", "2024-12-27", "https://gazettengr.com/gov-adeleke-approves-adesuyi-haastrup-as-new-owa-obokun-of-ijeshaland/",
                  "The governor approved Adesuyi Haastrup as the new Owa Obokun at a State Executive Council meeting, following the death of Oba Adekunle Aromolaran on 11 September."),
    "TDOWA": NEWS("Royal rumble over Haastrup nomination as Owa of Obokun", "ThisDay", "2025-01-05", "https://thisdaylive.com/index.php/2025/01/05/royal-rumble-over-haastup-nomination-as-owa-of-obokun",
                  "The Ofokutu and Fajemisin families of the Bilaro Olu-Odo ruling house alleged that the selection was manipulated by the government."),
    "WOSO": WS("Osogbo", "The Ataoja ('the one that stretches out his hand and takes the fish'), ruler of Osogbo; the move under Oba Larooye from Ipole Omu to Osogbo; list of Ataojas from Larooye (d. 1760) to Oba Jimoh Oyetunji Laaroye II (2010–present); the Osun-Osogbo festival and sacred grove (UNESCO)."),
    "SR26": NEWS("Governor Adeleke accuses Osun APC of sponsoring assassination attempt on prominent monarch", "Sahara Reporters", "2026-06-14",
                 "https://saharareporters.com/2026/06/14/governor-adeleke-accuses-osun-apc-sponsoring-assassination-attempt-prominent-monarch",
                 "Names Oba Jimoh Oyetunji Olanipekun, Larooye II, as the Ataoja of Osogbo (June 2026)."),
    "WILA": WS("Ila Orangun", "Capital of an ancient city-state in the Igbomina area; the Orangun is the paramount ruler, assisted by the Obaala; the present Orangun is Oba Abdul Wahab Olukayode Oyedotun Bibiire I; headquarters of Ila LGA."),
    "WOSS": dict(P129.SOURCES["WOSS"], notes="Reused. LGA headquarters: Ife Central at Ile-Ife; Ilesa East at Ilesa; Osogbo at Osogbo; Ila at Ila Orangun."),
}
TEXT = {
 "ife": """Ife is the traditional kingdom of Ile-Ife, the city that the Yoruba hold to be their place of origin, and its ruler is the Ooni of Ife. According to Wikipedia, Ife was founded in the first millennium BC and by AD 900 was an important West African centre producing sophisticated art; the Ooni line, which historians place before the reign of Oduduwa, is now drawn from four ruling houses — Lafogido, Osinkola, Ogboru and Giesi. Oba Adeyeye Enitan Ogunwusi, Ojaja II, of the Giesi house, was elected on 26 October 2015 as the 51st Ooni (Wikipedia), and the 10th anniversary of his installation was marked in December 2025 (AllAfrica).""",
 "ijesa": """Ijesaland is the traditional kingdom of the Ijesha, a Yoruba sub-group, with its capital at Ilesa; its ruler is styled the Owa Obokun Adimula of Ijesaland. According to Wikipedia, Ilesa was founded about 1250 by Owaluse, a descendant of Oduduwa. After the death of Oba Adekunle Aromolaran on 11 September 2024, the Osun State governor approved Clement Adesuyi Haastrup as the new Owa Obokun in December 2024 (The Gazette). Two families of the Bilaro Olu-Odo ruling house, the Ofokutu and the Fajemisin, publicly alleged that the selection had been manipulated (ThisDay, January 2025).""",
 "osogbo": """The Ataoja of Osogbo is the traditional ruler of Osogbo, the capital of Osun State; the title means 'the one that stretches out his hand and takes the fish' (Wikipedia). According to the town's tradition, Oba Larooye led his people from Ipole Omu to a more open site by the River Osun, and the Osun grove, now a UNESCO World Heritage Site, is central to the town's identity and its annual Osun-Osogbo festival. Wikipedia lists the Ataojas from Larooye, who died in 1760, to Oba Jimoh Oyetunji Laaroye II, who has reigned since 2010 and was still Ataoja in June 2026 (Sahara Reporters).""",
 "ila": """The Orangun of Ila is the paramount ruler of Ila Orangun, the capital of an ancient city-state in the Igbomina area of Yorubaland, now the headquarters of Ila LGA (Wikipedia). He is assisted by the Obaala, the second-in-command of the kingdom, and the senior chiefs. Wikipedia names the present Orangun as Oba Abdul Wahab Olukayode Oyedotun Bibiire I. The kingdom's history is not described in more detail in a source read.""",
}
REC = [
    ("ife", "Ife Kingdom", "ife-kingdom", ["WROI", "WOGW", "WIFE", "AA25"], "well_documented", dict(polity_type="kingdom", is_extant=1)),
    ("ijesa", "Ijesaland", "ijesaland", ["WIJE", "GZOWA", "TDOWA"], "well_documented", dict(polity_type="kingdom", is_extant=1, founded_year=1250, founded_text="Ilesa founded about 1250 (Wikipedia)", founded_precision="circa")),
    ("osogbo", "Ataoja of Osogbo", "ataoja-of-osogbo", ["WOSO", "SR26"], "well_documented", dict(polity_type="traditional_title", is_extant=1)),
    ("ila", "Orangun of Ila", "orangun-of-ila", ["WILA"], "reported", dict(polity_type="kingdom", is_extant=1)),
]
RECORDS = []
for key, name, slug, srcs, lvl, extra in REC:
    t = TEXT[key]
    f = dict(name=name, slug=slug, summary=t.split(". ")[0] + ".", description=t)
    f.update(extra)
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl, fields=f, srcs=[(s, name) for s in srcs]))
LG = lambda l: f"@admin_units:lga:osun/{l}"
SEAT = "Seat (not a statement of full jurisdiction)."
RELATIONS = [
    dict(frm="ife", type="located_in", to=LG("ife-central"), source="WOSS", evidence="multiple_sources", level="well_documented", notes=f"Ile-Ife, headquarters of Ife Central (Wikipedia's LGA table). {SEAT}"),
    dict(frm="ijesa", type="located_in", to=LG("ilesha-east"), source="WOSS", evidence="multiple_sources", level="well_documented", notes=f"Ilesa, headquarters of Ilesha East (Wikipedia's LGA table). {SEAT}"),
    dict(frm="osogbo", type="located_in", to=LG("osogbo"), source="WOSO", evidence="multiple_sources", level="well_documented", notes=f"Osogbo. {SEAT}"),
    dict(frm="ila", type="located_in", to=LG("ila"), source="WILA", evidence="single_reliable_source", level="well_documented", notes=f"Ila Orangun, headquarters of Ila LGA (Wikipedia). {SEAT}"),
    dict(frm="ife", type="associated_with", to="@ethnic_groups:yoruba", role="kingdom of Ile-Ife, held by the Yoruba to be their place of origin", source="WIFE", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Ifẹ; List of rulers of Ife)."),
    dict(frm="ijesa", type="associated_with", to="@ethnic_groups:yoruba", role="kingdom of the Ijesha, a Yoruba sub-group", source="WIJE", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Ijesha)."),
    dict(frm="osogbo", type="associated_with", to="@ethnic_groups:yoruba", role="Yoruba town kingship", source="WOSO", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Osogbo)."),
    dict(frm="ila", type="associated_with", to="@ethnic_groups:yoruba", role="Igbomina (Yoruba) city-state", source="WILA", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Ila Orangun; Igbomina)."),
    dict(frm="@polities:oyo-empire", type="associated_with", to="ife", role="Oranmiyan, founder of Oyo in tradition, was a prince of Ife", source="WROI", evidence="single_reliable_source", level="reported", notes="Wikipedia (Oyo Empire; List of rulers of Ife)."),
]
NAMES = [
    dict(record="ife", name="Ooni of Ife", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WROI"]),
    dict(record="ijesa", name="Owa Obokun Adimula of Ijesaland", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WIJE"]),
    dict(record="ijesa", name="Ilesa Kingdom", name_type="alternative", usage_notes="Wikipedia: Ilesa 'is home to a large kingdom of the same name'.", srcs=["WIJE"]),
]
GAPS = [
    ("Osun: the Owa Obokun's selection", "Two families of the Bilaro Olu-Odo ruling house contested Oba Haastrup's selection (ThisDay, January 2025); the outcome of any challenge is not known from a source read."),
    ("Osun: the Orangun of Ila", "Named only by Wikipedia; his accession date and a 2025–26 confirmation are needed."),
    ("Osun: other obaships", "The Oluwo of Iwo, the Timi of Ede, the Akirun of Ikirun, the Aragbiji of Iragbiji, the Orangun of Oke-Ila and others need sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Osun traditional institutions: Ife (Ooni), Ijesaland (Owa Obokun), the Ataoja of Osogbo and the Orangun of Ila.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 131 — Osun: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **4 polities:**",
         "  - the **Ife Kingdom**, ruled by the Ooni: Oba Ogunwusi, Ojaja II, the 51st Ooni, reigning since 2015 (10th anniversary December 2025)",
         "  - **Ijesaland**, ruled by the Owa Obokun: Oba Clement Adesuyi Haastrup, approved December 2024. His selection was contested by two families of one ruling house; this is stated neutrally.",
         "  - the **Ataoja of Osogbo**: Oba Jimoh Oyetunji since 2010, confirmed in June 2026",
         "  - the **Orangun of Ila** (Igbomina): *reported*, from Wikipedia only",
         "- **Link:** the Oyo Empire is tied to Ife through Oranmiyan.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, name, slug, srcs, lvl, extra in REC:
        L += [f"## {name} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_131_osun_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_131_osun_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
