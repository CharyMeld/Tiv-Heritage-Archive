"""
Research batch 089 — Kano (Phase 3): traditional institutions. Researched 2026-10-02. Pattern: batches 059, 065, 071,
077 and 083. The open emirship dispute is recorded by fix_089_kano.php (run after import; revert before rollback).

Polities (6):
  * Kingdom of Kano (c. 999–1805; Wikipedia 'Kingdom of Kano'): Hausa kingdom centred on Dala Hill; Bagauda first
    Sarkin Kano (Kano Chronicle); Muhammad Rumfa 1463–99; conquered in the Fulani jihad, 1805.
  * Kano Emirate (1805–): Wikipedia 'Kano Emirate' and 'Kano Emirate Council'. Emirship disputed since May 2024:
      - Kano State Government: Muhammadu Sanusi II reinstated as 16th Emir on 23 May 2024 under the Kano State Emirate
        Council (Repeal) Law 2024, which also removed Aminu Ado Bayero (15th Emir, from 9 March 2020) and the four
        first-class emirs of 2019 (Channels TV, 26 March 2025; Wikipedia).
      - Court of Appeal (January 2025) upheld the reinstatement and set aside a Federal High Court ruling for Bayero;
        a later stay and further appeals followed (Channels TV, 26 March 2025). The Supreme Court adjourned the
        appeal of Aminu Baba Dan Agundi, for Bayero, to 19 April 2027 (The Guardian, 20 April 2026).
      - Bayero has not accepted his removal and occupies a mini-palace at Nasarawa (Wikipedia, Kano Emirate Council).
    Both are named; neither is presented as settled.
  * Gaya, Rano and Karaye emirates: created as first-class emirates in 2019, abolished 23 May 2024, re-established as
    second-class emirates under the Emir of Kano by the Kano State Emirate Council Establishment Law 2024 (17 July
    2024): Gaya (Gaya, Albasu, Ajingi) — Aliyu Ibrahim Abdulkadir Gaya; Rano (Rano, Bunkure, Kibiya) — Muhammad Isa
    Umar; Karaye (Karaye, Rogo) — Muhammad Mahraz Karaye (ICIR, 17 July 2024).
  * Bichi Emirate (2019–2024): palace at Bichi; Aminu Ado Bayero first emir 2019–2020, then Nasiru Ado Bayero;
    abolished 23 May 2024 and not re-established (Wikipedia 'Bichi Emirate'; 'Kano Emirate Council').
Not used: Wikipedia's Aminu Ado Bayero article dates the reinstatement order to 'May 2023' (all other sources: May
2024); Wikipedia's Gaya Emirate article also has '2023' for the dissolution.
"""
import json, re, sys
import batch_087_kano_languages as L87

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = lambda t, org, d, u, n, a=None: dict(source_type="news", source_kind="news", source_tier=3, title=t, organisation=org, publication_date=d, url=u,
                                            verification_status="needs_corroboration", notes=n, **({"author": a} if a else {}))
SOURCES = {
    "WKK": WS("Kingdom of Kano", "Hausa kingdom centred on Kano, established around 1000 CE, conquered in 1805 in the jihad of Usman dan Fodio; originally called Dala after the hill; Bagauda first Sarkin Kano (Kano Chronicle); Gijimasu began the walls; Muhammad Rumfa 1463–99 (Kurmi market, council of nine); Muhammadu Alwali, the last sultan, deposed 1805."),
    "WKE": WS("Kano Emirate", "Formed in 1805 in the Fulani jihad, a vassal of the Sokoto Caliphate; civil war 1893–95; British capture February 1903; Emir Aliyu Babba exiled to Lokoja; continued as the Kano Emirate Council."),
    "WKEC": WS("Kano Emirate Council", "Headquarters Kano city; formed 1903; Ado Bayero emir 1963–2014; Muhammadu Sanusi II from 8 June 2014; 2019 split into Kano, Bichi, Karaye, Gaya and Rano; Sanusi removed March 2020, Aminu Ado Bayero emir; May 2024 Sanusi reinstated and the four emirates disbanded; conflicting court orders; Bayero stays in the Nasarawa palace; July 2024 Karaye, Gaya and Rano re-established as second-class emirates under Kano; Bichi not re-established; the Emir of Kano leads the Tijaniyya in Nigeria."),
    "WSAN": WS("Sanusi Lamido Sanusi", "Muhammadu Sanusi II, born 31 July 1961; Emir of Kano from 8 June 2014; dethroned 9 March 2020; reinstated 23 May 2024 by Governor Abba Kabir Yusuf; khalifa of the Tijaniyya in Nigeria."),
    "WAAB": WS("Aminu Ado Bayero", "Born 21 August 1961, son of Emir Ado Bayero; 15th Emir of Kano from 9 March 2020; staff of office 3 July 2021; first Emir of Bichi 2019–2020; has not acknowledged his removal and occupies one of the emirate's mini-palaces."),
    "WBIC": WS("Bichi Emirate", "Established 2019 with palace at Bichi; nine LGAs; Aminu Ado Bayero first emir, replaced 12 March 2020 by Nasiru Ado Bayero; abolished 23 May 2024."),
    "WRAN": WS("Rano Emirate", "Emirs: Tafida Abubakar Ila (2019–2020), Kabiru Muhammad Inuwa (2020–2024), Muhammad Isa Umar (July 2024–); Rano one of the seven Hausa states (Hausa Bakwai)."),
    "WKAR": WS("Karaye Emirate", "Headquarters Karaye town; town founded 1085 (tradition); Habe rulers 1101–1793; emirs Ibrahim Abubakar II (2020–2024), Muhammad Mahraz (July 2024–)."),
    "WGAY": WS("Gaya Emirate", "Second-class emirate; Sarkin Gaya Aliyu Ibrahim Gaya, appointed 2021 after the death of Ibrahim Abdulkadir; re-established July 2024 (Gaya, Albasu, Ajingi)."),
    "ICIR24": NEWS("Emirates tussle: Yusuf appoints three 2nd class emirs in Kano", "International Centre for Investigative Reporting (ICIR)", "2024-07-17",
                   "https://www.icirnigeria.org/emirates-tussle-yusuf-appoints-three-2nd-class-emirs-in-kano/",
                   "Kano State Emirate Council Establishment Law 2024 signed; second-class emirate councils in Rano, Gaya and Karaye, advising the Emir of Kano; emirs Muhammad Mahraz Karaye (Karaye; formerly District Head of Rogo), Muhammad Isa Umar (Rano; formerly District Head of Bunkure), Aliyu Ibrahim Abdulkadir Gaya (Gaya; emir of the defunct Gaya emirate); Rano = Rano, Bunkure, Kibiya; Gaya = Gaya, Albasu, Ajingi; Karaye = Karaye, Rogo.", "Bankole Abe"),
    "CHAN25": NEWS("Kano Emirship: Appeal Court Returns Case To Supreme Court", "Channels Television", "2025-03-26",
                   "https://www.channelstv.com/2025/03/26/kano-emirship-appeal-court-returns-case-to-supreme-court",
                   "Court of Appeal, Abuja, stayed actions against the reinstatement of Muhammadu Sanusi II as 16th Emir pending appeals at the Supreme Court; Sanusi reinstated under the Kano State Emirate Council (Repeal) Law 2024, which validated the removal of Aminu Ado Bayero as 15th Emir and of four first-class emirs; Justice Okon Abang's stay of 14 March 2025.", "Emmanuella Ekele"),
    "GUA26": NEWS("Emirate Tussle: Supreme Court Adjourns Sanusi, Bayero Case to 2027", "The Guardian (Nigeria)", "2026-04-20",
                  "https://guardian.ng/news/emirate-tussle-supreme-court-adjourns-sanusi-bayero-case-to-2027/",
                  "The Supreme Court adjourned to 19 April 2027 the appeal of Aminu Baba Dan Agundi, a senior emirate councillor, in defence of Aminu Ado Bayero (15th Emir), against the Kano State Government and House of Assembly; it had been fixed for 20 April 2026.", "Murtala Adewale"),
    "WKAN": L87.SOURCES["WKAN"],
    "KSG25": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Kano Throne: Emir Sanusi secures victory against Bayero as Appeal Court upturns Justice Liman's verdict", organisation="Kano State Government", publication_date="2025-01-10", url="https://kanostate.gov.ng/kano-throne-emir-sanusi-secures-victory-against-bayero-as-appeal-court-upturns-justice-limans-verdict/", verification_status="needs_corroboration", notes="Court of Appeal, Abuja, on 10 January 2025 set aside the Federal High Court (Justice Abdullahi Liman) judgment that had nullified Sanusi's appointment, holding that the Federal High Court lacked jurisdiction over chieftaincy matters. The publisher is a party to the case."),
}
TEXT = {
 "kingdom": """The Kingdom of Kano was a Hausa kingdom centred on the city of Kano, established around 1000 CE and conquered in the jihad of Usman dan Fodio in 1805 (Wikipedia). Kano was first known as Dala, after the hill around which it grew; according to the Kano Chronicle, Bagauda, a descendant of Bayajidda of Daura, became the first Sarkin Kano, and his grandson Gijimasu began the city's walls. Muhammad Rumfa (1463–99) extended the walls, founded the Kurmi market and created a council of nine senior office-holders (Wikipedia). In 1805 Muhammadu Bakatsine, leader of the Jobawa Fulani, deposed the last sultan, Muhammadu Alwali, and Kano became an emirate of the Sokoto Caliphate (Wikipedia).""",
 "kano": """The Kano Emirate was formed in 1805, during the Fulani jihad, when the Hausa Sultanate of Kano was replaced by an emirate owing allegiance to the Sokoto Caliphate; Kano became its largest and most prosperous province (Wikipedia). The British took Kano in February 1903 and exiled Emir Aliyu Babba to Lokoja, and the emirate continued as the Kano Emirate Council, centred on the city of Kano (Wikipedia). Ado Bayero reigned from 1963 to 2014; Muhammadu Sanusi II succeeded him, was removed in March 2020 and replaced by Aminu Ado Bayero, after the state had split the emirate in 2019 (Wikipedia). On 23 May 2024 the Kano State Government reinstated Sanusi as the 16th Emir under the Kano State Emirate Council (Repeal) Law 2024, which also removed Bayero (Channels TV). The emirship is disputed: Bayero has not accepted his removal, the Court of Appeal ruled for the reinstatement on 10 January 2025 (Kano State Government), and the Supreme Court has adjourned the final appeal to 19 April 2027 (The Guardian). The Emir of Kano also leads the Tijaniyya order in Nigeria (Wikipedia).""",
 "gaya": """The Gaya Emirate is a second-class emirate under the Emir of Kano, covering Gaya, Albasu and Ajingi LGAs. It was one of four emirates carved out of the Kano Emirate in 2019, was abolished on 23 May 2024 and was re-established as a second-class emirate by the Kano State Emirate Council Establishment Law of July 2024 (Wikipedia; ICIR). On 17 July 2024 the governor reappointed Aliyu Ibrahim Abdulkadir Gaya, emir of the former emirate, as Emir of Gaya (ICIR); Wikipedia says he first became Sarkin Gaya in 2021, after the death of Ibrahim Abdulkadir.""",
 "rano": """The Rano Emirate is a second-class emirate under the Emir of Kano, covering Rano, Bunkure and Kibiya LGAs (ICIR, 2024). Rano was one of the seven Hausa states, the Hausa Bakwai, of tradition (Wikipedia). Created as a first-class emirate in 2019, it was abolished on 23 May 2024 and re-established as a second-class emirate in July 2024 (Wikipedia; ICIR). Its emirs have been Tafida Abubakar Ila (2019–2020) and Kabiru Muhammad Inuwa (2020–2024), and since 17 July 2024 Muhammad Isa Umar, formerly District Head of Bunkure (Wikipedia; ICIR).""",
 "karaye": """The Karaye Emirate is a second-class emirate under the Emir of Kano, with its headquarters at Karaye and covering Karaye and Rogo LGAs (Wikipedia; ICIR, 2024). Wikipedia records a tradition that Karaye town was founded in 1085 and ruled by Habe kings from 1101 to 1793. It was created as a first-class emirate in the 2019–2020 reorganisation, abolished on 23 May 2024 and re-established as a second-class emirate in July 2024; Muhammad Mahraz Karaye, formerly District Head of Rogo, was appointed Emir on 17 July 2024 (ICIR), succeeding Ibrahim Abubakar II (2020–2024; Wikipedia).""",
 "bichi": """The Bichi Emirate was one of the four emirates created out of the Kano Emirate in 2019, with its palace at Bichi and nine LGAs in the north of Kano State (Wikipedia). Aminu Ado Bayero was its first emir until March 2020, when he became Emir of Kano and his brother Nasiru Ado Bayero succeeded him at Bichi (Wikipedia). The emirate was abolished on 23 May 2024, and unlike Gaya, Rano and Karaye it was not re-established in July 2024 (Wikipedia, Kano Emirate Council).""",
}
REC = [  # key, name, slug, type, srcs, level, extra
    ("kingdom", "Kingdom of Kano", "kingdom-of-kano", "kingdom", ["WKK"], "well_documented",
     dict(is_extant=0, founded_text="c. 999 / around 1000 CE (Kano Chronicle; Wikipedia)", founded_year=999, founded_precision="circa", ended_year=1805, ended_text="1805, Fulani jihad", ended_precision="year")),
    ("kano", "Kano Emirate", "kano-emirate", "emirate", ["WKE", "WKEC", "WSAN", "WAAB", "CHAN25", "GUA26", "KSG25"], "well_documented",
     dict(is_extant=1, founded_year=1805, founded_text="1805, Fulani jihad (Wikipedia)", founded_precision="year")),
    ("gaya", "Gaya Emirate", "gaya-emirate", "emirate", ["ICIR24", "WGAY", "WKEC"], "well_documented", dict(is_extant=1, founded_year=2019, founded_text="2019; re-established as second class 17 July 2024", founded_precision="year")),
    ("rano", "Rano Emirate", "rano-emirate", "emirate", ["ICIR24", "WRAN", "WKEC"], "well_documented", dict(is_extant=1, founded_year=2019, founded_text="2019; re-established as second class 17 July 2024", founded_precision="year")),
    ("karaye", "Karaye Emirate", "karaye-emirate", "emirate", ["ICIR24", "WKAR", "WKEC"], "well_documented", dict(is_extant=1, founded_year=2019, founded_text="2019–2020; re-established as second class 17 July 2024", founded_precision="year")),
    ("bichi", "Bichi Emirate", "bichi-emirate", "emirate", ["WBIC", "WKEC"], "well_documented",
     dict(is_extant=0, founded_year=2019, founded_text="2019 (Wikipedia)", founded_precision="year", ended_year=2024, ended_text="abolished 23 May 2024", ended_precision="year")),
]
RECORDS = []
for key, name, slug, ptype, srcs, lvl, extra in REC:
    t = TEXT[key]
    f = dict(polity_type=ptype, name=name, slug=slug, summary=t.split(". ")[0] + ".", description=t)
    f.update(extra)
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl, fields=f, srcs=[(s, name) for s in srcs]))
K = lambda l: f"@admin_units:lga:kano/{l}"
SEAT = "(not a statement of full jurisdiction)"
RELATIONS = [
    dict(frm="kano", type="located_in", to="@admin_units:state:kano", source="WKEC", evidence="single_reliable_source", level="well_documented",
         notes="Headquarters in the city of Kano; its borders are contiguous with Kano State (Wikipedia, Kano Emirate Council)."),
    dict(frm="kingdom", type="located_in", to="@admin_units:state:kano", source="WKK", evidence="single_reliable_source", level="well_documented", notes="Centred on the city of Kano (Wikipedia)."),
    dict(frm="bichi", type="located_in", to=K("bichi"), source="WBIC", evidence="single_reliable_source", level="well_documented", notes=f"Palace in Bichi town, Bichi LGA (Wikipedia) {SEAT}."),
    dict(frm="karaye", type="located_in", to=K("karaye"), source="WKAR", evidence="multiple_sources", level="well_documented", notes=f"Headquarters Karaye town (Wikipedia; ICIR) {SEAT}."),
]
for k, lgas in (("gaya", ["gaya", "albasu", "ajingi"]), ("rano", ["rano", "bunkure", "kibiya"]), ("karaye", ["rogo"])):
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="located_in", to=K(l), source="ICIR24", evidence="single_reliable_source", level="well_documented",
                              notes="One of the LGAs of the emirate under the Kano State Emirate Council Establishment Law 2024 (ICIR, 17 July 2024)."))
RELATIONS += [
    dict(frm="kano", type="associated_with", to="kingdom", role="replaced the Hausa Sultanate of Kano in 1805", source="WKE", evidence="multiple_sources", level="well_documented",
         notes="Wikipedia (Kano Emirate; Kingdom of Kano)."),
    dict(frm="gaya", type="associated_with", to="kano", role="second-class emirate under the Emir of Kano (2024)", source="ICIR24", evidence="multiple_sources", level="well_documented",
         notes="The 2024 law gives the second-class emirate councils power to advise the Emir of Kano (ICIR)."),
    dict(frm="rano", type="associated_with", to="kano", role="second-class emirate under the Emir of Kano (2024)", source="ICIR24", evidence="multiple_sources", level="well_documented", notes="ICIR, 2024."),
    dict(frm="karaye", type="associated_with", to="kano", role="second-class emirate under the Emir of Kano (2024)", source="ICIR24", evidence="multiple_sources", level="well_documented", notes="ICIR, 2024."),
    dict(frm="bichi", type="associated_with", to="kano", role="carved out of the Kano Emirate 2019; merged back 2024", source="WBIC", evidence="multiple_sources", level="well_documented",
         notes="Wikipedia (Bichi Emirate; Kano Emirate Council)."),
    dict(frm="kano", type="associated_with", to="@ethnic_groups:fulani", role="Fulani ruling house since the 1805 jihad", source="WKK", evidence="multiple_sources", level="well_documented",
         notes="The jihad leader Muhammadu Bakatsine led the Jobawa Fulani (Wikipedia, Kingdom of Kano); Sanusi II and Ado Bayero's line are of the Fulani Sullubawa clan (Wikipedia)."),
    dict(frm="kingdom", type="associated_with", to="@ethnic_groups:hausa", role="Hausa kingdom", source="WKK", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Kingdom of Kano)."),
]
NAMES = [
    dict(record="kingdom", name="Dala", name_type="historical", usage_notes="Kano's early name, after Dala Hill (Wikipedia, citing Bornoan sources).", srcs=["WKK"]),
    dict(record="kingdom", name="Sultanate of Kano", name_type="alternative", usage_notes="Wikipedia's name for the later Hausa state, before 1805.", srcs=["WKE"]),
    dict(record="kano", name="Kano Emirate Council", name_type="official", usage_notes="Its name since 1903 (Wikipedia).", srcs=["WKEC"]),
]
GAPS = [
    ("Kano: the emirship", "Recorded as an open dispute (fix_089): Muhammadu Sanusi II (Kano State Government, Court of Appeal 2025) or Aminu Ado Bayero (pending appeal). The Supreme Court hearing is fixed for 19 April 2027."),
    ("Kano: 2019 emirate boundaries", "Wikipedia gives different LGA lists for the 2019 emirates (e.g. Karaye with eight LGAs) from the 2024 law (Karaye and Rogo only). Only the 2024 lists are linked."),
    ("Kano: Bichi's emir after 2024", "Whether Nasiru Ado Bayero still claims the Bichi title, and the state of any related court case, was not found."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kano traditional institutions: Kingdom of Kano, Kano Emirate (disputed emirship), Gaya, Rano and Karaye (second class, 2024), Bichi (2019–2024).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 089 — Kano: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **6 polities:**",
         "  - the **Kingdom of Kano** (c. 999–1805)",
         "  - the **Kano Emirate** (from 1805)",
         "  - the second-class **Gaya, Rano and Karaye** emirates (July 2024)",
         "  - the abolished **Bichi Emirate** (2019–2024)",
         "- **The Kano emirship is recorded as disputed, and neither claim is presented as settled:**",
         "  - **Kano State Government:** Muhammadu Sanusi II, reinstated as 16th Emir on 23 May 2024 under the Repeal Law 2024. The Court of Appeal ruled for the reinstatement in January 2025.",
         "  - **Aminu Ado Bayero:** 15th Emir from March 2020. He has not accepted his removal, and his side's appeal is at the Supreme Court, adjourned to **19 April 2027** (The Guardian, 20 April 2026).",
         "  - **fix_089** creates an open dispute record with both positions and their sources.",
         "- **Second-class emirates:** their LGAs and emirs come from the July 2024 law (ICIR, 17 July 2024).",
         "  - Gaya: Gaya, Albasu, Ajingi",
         "  - Rano: Rano, Bunkure, Kibiya",
         "  - Karaye: Karaye, Rogo",
         "- **Not used:** two Wikipedia dates that contradict every other source ('May 2023').",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, name, slug, ptype, srcs, lvl, extra in REC:
        L += [f"## {name} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_089_kano_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_089_kano_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
