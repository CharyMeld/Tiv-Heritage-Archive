"""
Research batch 162 — Niger (Phase 3): traditional institutions. Researched 2026-10-07. Pattern: batches 144, 156.

  * Bida (the Etsu Nupe): Wikipedia 'Bida Emirate' — successor to the old Nupe Kingdom (mid-15th century, between the
    Niger and Kaduna rivers); Jibiri (c. 1770) the first Muslim king; Ma'azu's period of greatest power (d. 1818); under
    Gwandu after the succession wars; Masaba (son of the Fulani leader Mallam Dendo and a Nupe mother) from 1841; Bida a
    military power under Masaba (to 1873); taken by the Royal Niger Company in 1897; Nupe Cultural Day. Wikipedia 'Bida':
    the headquarters of the Nupe Kingdom led by the Etsu Nupe. Peoples Gazette (11 Sep 2026): Yahaya Abubakar, 74, 23
    years on the throne, chairman of the Niger State Council of Traditional Rulers, a retired Brigadier-General.
  * Kontagora (the Sarkin Sudan): Wikipedia 'Kontagora Emirate' — minor chiefdoms (Aguarra, Dakka-Karri, Kambari,
    Dukawa, Ngaski) conquered by the Fula between 1858 and 1864, a dependency of the Sokoto Caliphate; under British
    rule from 1901. Voice of Nigeria (12 Feb 2023): the 7th Sarkin Sudan, Alhaji Muhammad Barau Mu'azu II, given the
    staff of office.
  * Borgu (the Emir, at New Bussa): Wikipedia 'Borgu Emirate' — formed in 1954 by merging the Bussa and Kaiama emirates,
    parts of the Borgu state partitioned between the French and British in 1898. State House (undated): Tinubu greets
    Muhammed Haliru Dantoro Kitoro IV (Mai Borgu), the 17th Emir of Borgu, on his 10th anniversary on the throne.
  * Suleja (the Emir, Sarkin Zazzau): Wikipedia 'Suleja Emirate' — founded as the Abuja Emirate by the Hausa nobility of
    Zazzau fleeing the Fulani jihad (Abu Ja founded Abuja town in 1828); renamed after 1976; Awwal Ibrahim emir from
    1993, deposed 10 May 1994, restored 17 January 2000, both times amid violence. Niger State Government (7 May 2024):
    the 30th anniversary of Mallam Muhammadu Awwal Ibrahim on the throne.
Placement: Bida → Bida; Kontagora → Kontagora; New Bussa → Borgu (INEC ward New Bussa 04-06); Suleja → Suleja.
"""
import json, re, sys
import batch_144_ondo_institutions as I144
import batch_160b_niger_peoples as P160b

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = I144.NEWS
SOURCES = {
    "WBIE": WS("Bida Emirate", "Successor to the old Nupe Kingdom (mid-15th century); Jibiri (c. 1770) the first Muslim Nupe king; Ma'azu (d. 1818); under Gwandu; Masaba from 1841; Bida taken by Royal Niger Company troops in 1897; Nupe Cultural Day."),
    "WBID": P160b.SOURCES["WBID"],
    "PG26N": NEWS("Tinubu congratulates Etsu Nupe at 74", "Peoples Gazette", "2026-09-11", "https://gazettengr.com/tinubu-congratulates-etsu-nupe-at-74/",
                  "Yahaya Abubakar, Etsu Nupe, 74 and 23 years on the throne; chairman of the Niger State Council of Traditional Rulers; retired Brigadier-General."),
    "WKOE": WS("Kontagora Emirate", "Chiefdoms (Aguarra, Dakka-Karri, Kambari, Dukawa, Ngaski) conquered by the Fula between 1858 and 1864, a dependency of the Sokoto Caliphate; British rule from 1901."),
    "VON23K": NEWS("Emir of Kontagora in Niger State gets Staff of Office", "Voice of Nigeria", "2023-02-12", "https://von.gov.ng/emir-of-kontagora-in-niger-state-gets-staff-of-office/",
                   "The governor presented the staff of office to the 7th Sarkin Sudan of Kontagora, Alhaji Muhammad Barau Mu'azu II."),
    "WKON": P160b.SOURCES["WKON"],
    "WBOE": WS("Borgu Emirate", "Capital New Bussa; formed in 1954 by merging the Bussa and Kaiama emirates, formerly part of the Borgu state partitioned between French Dahomey and British Nigeria in 1898."),
    "SH25": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="President Tinubu greets Emir of Borgu, HRH Muhammed Haliru Dantoro Kitoro IV (Mai Borgu) on 10th anniversary",
                 organisation="The State House, Abuja", url="https://statehouse.gov.ng/president-tinubu-greets-emir-of-borgu-hrh-muhammed-haliru-dantoro-kitoro-iv-mai-borgu-on-10th-anniversary/",
                 verification_status="verified", notes="The 17th Emir of Borgu marks 10 years on the throne. Undated page, read 7 October 2026."),
    "WSUE": WS("Suleja Emirate", "Founded as the Abuja Emirate by the Hausa nobility of Zazzau fleeing the Fulani jihad; Abu Ja founded Abuja town in 1828; renamed Suleja after 1976; Awwal Ibrahim emir from 1993, deposed 10 May 1994, restored 17 January 2000, amid violence."),
    "NSG24": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Niger State Governor to facilitate the establishment of tertiary institutions in Suleja Emirate",
                  organisation="Niger State Government", publication_date="2024-05-07", url="https://nigerstate.gov.ng/niger-state-governor-to-facilitate-the-establishment-of-tertiary-institutions-in-suleja-emirate/",
                  verification_status="verified", notes="The 30th anniversary on the throne of the Emir of Zazzau Suleja, Mallam Muhammadu Awwal Ibrahim (May 2024)."),
    "WSUL": P160b.SOURCES["WSUL"],
}
TEXT = {
 "bida": """The Bida Emirate is the traditional state of the Nupe, with its seat at Bida, and its ruler, the Etsu Nupe, is the leader of the Nupe people. According to Wikipedia, it succeeds the old Nupe Kingdom, founded in the mid-fifteenth century in the basin between the Niger and Kaduna rivers; Jibiri, about 1770, was its first Muslim king, and Ma'azu, who died in 1818, brought it to its greatest power. In the succession wars that followed, Nupe came under the control of the Gwandu Emirate, and Masaba, son of the Fulani leader Mallam Dendo and a Nupe mother, ruled from 1841 and made Bida a strong military power. Royal Niger Company troops took Bida in 1897. The present Etsu Nupe, Yahaya Abubakar, a retired Brigadier-General, marked 23 years on the throne in September 2026 and chairs the Niger State Council of Traditional Rulers (Peoples Gazette).""",
 "kontagora": """The Kontagora Emirate is a traditional state with its capital at Kontagora, and its ruler is styled the Sarkin Sudan. According to Wikipedia, its territory was divided among minor chiefdoms, including Aguarra, Dakka-Karri, Kambari, Dukawa and Ngaski, which the Fulani conquered between 1858 and 1864, making Kontagora a dependency of the Sokoto Caliphate; the town itself was founded by Umaru Nagwamatse on Kambari land, and the emirate came under British rule after an attack in 1901. In February 2023 the state governor presented the staff of office to the seventh Sarkin Sudan, Alhaji Muhammad Barau Mu'azu II (Voice of Nigeria).""",
 "borgu": """The Borgu Emirate is a traditional state with its capital at New Bussa, in Borgu LGA. According to Wikipedia, it was formed in 1954 by merging the emirates of Bussa and Kaiama, which, with Illa, had been part of the old Borgu state divided between French Dahomey and British Nigeria in 1898. The rulers of Bussa took the title Kibe. The present ruler, Muhammed Haliru Dantoro Kitoro IV, the Mai Borgu and 17th Emir of Borgu, has marked ten years on the throne (State House).""",
 "suleja": """The Suleja Emirate is a Hausa emirate in Niger State whose ruler also bears the title Sarkin Zazzau. According to Wikipedia, it began as the Abuja Emirate: after the Fulani jihad captured Zaria about 1804, Muhammadu Makau, king of Zazzau, led Hausa nobles to the Koro town of Zuba, and his successor Abu Ja founded Abuja town in 1828, which remained an independent Hausa refuge. In 1976 much of the emirate became part of the Federal Capital Territory, and the emirate and its capital took the name Suleja. Awwal Ibrahim became emir in 1993 and was deposed in 1994; his accession and his restoration in 2000 were both followed by violence; in May 2024 he marked thirty years on the throne (Niger State Government).""",
}
REC = [
    ("bida", "Bida Emirate", "bida-emirate", ["WBIE", "WBID", "PG26N"], "well_documented"),
    ("kontagora", "Kontagora Emirate", "kontagora-emirate", ["WKOE", "VON23K", "WKON"], "reported"),
    ("borgu", "Borgu Emirate", "borgu-emirate", ["WBOE", "SH25"], "well_documented"),
    ("suleja", "Suleja Emirate", "suleja-emirate", ["WSUE", "NSG24", "WSUL"], "reported"),
]
RECORDS = [dict(key=k, table="polities", evidence="multiple_sources", level=lvl,
                fields=dict(name=n, slug=s, polity_type="emirate", is_extant=1, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k]),
                srcs=[(x, n) for x in srcs]) for k, n, s, srcs, lvl in REC]
RECORDS[2]["fields"].update(founded_year=1954, founded_text="Formed in 1954 by merging Bussa and Kaiama (Wikipedia)", founded_precision="year")
LG = lambda l: f"@admin_units:lga:niger/{l}"
SEAT = "Seat (not a statement of full jurisdiction)."
RELATIONS = [
    dict(frm="bida", type="located_in", to=LG("bida"), source="WBID", evidence="multiple_sources", level="well_documented", notes=f"Bida (Wikipedia). {SEAT}"),
    dict(frm="kontagora", type="located_in", to=LG("kontagora"), source="WKOE", evidence="multiple_sources", level="well_documented", notes=f"Kontagora (Wikipedia). {SEAT}"),
    dict(frm="borgu", type="located_in", to=LG("borgu"), source="WBOE", evidence="multiple_sources", level="well_documented", notes=f"New Bussa, a ward of Borgu LGA in INEC's directory. {SEAT}"),
    dict(frm="suleja", type="located_in", to=LG("suleja"), source="WSUE", evidence="multiple_sources", level="well_documented", notes=f"Suleja (Wikipedia). {SEAT}"),
    dict(frm="bida", type="associated_with", to="@ethnic_groups:nupe", role="the Etsu Nupe, leader of the Nupe people", source="WBIE", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Bida Emirate; Bida)."),
    dict(frm="bida", type="associated_with", to="@ethnic_groups:fulani", role="ruling dynasty descended from the Fulani leader Mallam Dendo", source="WBIE", evidence="single_reliable_source", level="reported", notes="Wikipedia (Bida Emirate)."),
    dict(frm="kontagora", type="associated_with", to="@ethnic_groups:kambari", role="Kontagora founded on Kambari land; Kambari chiefdoms within the emirate", source="WKOE", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Kontagora Emirate; Kontagora)."),
    dict(frm="kontagora", type="associated_with", to="@ethnic_groups:hun-saare", role="Dukawa chiefdom conquered into the emirate", source="WKOE", evidence="single_reliable_source", level="reported", notes="Wikipedia (Kontagora Emirate)."),
    dict(frm="borgu", type="associated_with", to="@ethnic_groups:busa-people", role="emirate of Bussa, the old Borgu state", source="WBOE", evidence="single_reliable_source", level="reported", notes="Wikipedia (Borgu Emirate); Busa spoken in Borgu (Atlas)."),
    dict(frm="borgu", type="associated_with", to="@ethnic_groups:bariba", role="the old Borgu state was shared by the Bariba and Busa", source="WBOE", evidence="single_reliable_source", level="reported", notes="Wikipedia (Borgu Emirate; Bariba people)."),
    dict(frm="suleja", type="associated_with", to="@ethnic_groups:hausa", role="Hausa emirate founded by the nobility of Zazzau", source="WSUE", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Suleja Emirate; Suleja)."),
    dict(frm="suleja", type="associated_with", to="@ethnic_groups:koro", role="founded among four Koro chiefdoms", source="WSUE", evidence="single_reliable_source", level="reported", notes="Wikipedia (Suleja Emirate)."),
]
NAMES = [
    dict(record="bida", name="Etsu Nupe", name_type="alternative", usage_notes="Title of the ruler.", srcs=["WBIE"]),
    dict(record="bida", name="Nupe Kingdom", name_type="historical", usage_notes="The old kingdom to which the Bida Emirate succeeded (Wikipedia).", srcs=["WBIE"]),
    dict(record="kontagora", name="Sarkin Sudan", name_type="alternative", usage_notes="Title of the ruler (Voice of Nigeria).", srcs=["VON23K"]),
    dict(record="borgu", name="Emir of Borgu", name_type="alternative", usage_notes="Title of the ruler, also Mai Borgu (State House).", srcs=["SH25"]),
    dict(record="suleja", name="Abuja Emirate", name_type="historical", usage_notes="Its name until after 1976 (Wikipedia).", srcs=["WSUE"]),
    dict(record="suleja", name="Emir of Suleja", name_type="alternative", usage_notes="Title of the ruler, also Sarkin Zazzau (Niger State Government).", srcs=["NSG24"]),
]
GAPS = [
    ("Niger: Sokoto Caliphate and Gwandu", "Bida and Kontagora were dependencies of the Sokoto Caliphate (Bida under Gwandu); the archive has no Sokoto Caliphate or Gwandu record yet."),
    ("Niger: Kontagora and Suleja rulers", "The latest sources read are from 2023 (Kontagora) and 2024 (Suleja); both records are reported until a 2025–26 source is found. The State House statement on the Emir of Borgu is undated."),
    ("Niger: other emirates", "The Agaie Emirate (latest source read 2014), the Lapai Emirate (Wikipedia lists Umaru Bago Tafida from 2002), the Kagara Emirate (Wikipedia: Ahmad Garba Gunna), and the Minna, Kwongoma and Kotonkoro thrones need dated sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Niger traditional institutions: the Bida Emirate (Etsu Nupe), Kontagora, Borgu and Suleja emirates.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 162 — Niger: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **4 emirates:**",
         "  - the **Bida Emirate** (the Etsu Nupe): Yahaya Abubakar, 23 years on the throne, confirmed September 2026",
         "  - **Borgu** (New Bussa): Emir Muhammed Haliru Dantoro Kitoro IV, whose 10th anniversary on the throne was marked by a State House statement (undated)",
         "  - **Kontagora** (the Sarkin Sudan): Muhammad Barau Mu'azu II, given the staff of office in 2023. *Reported*: the latest source is from 2023.",
         "  - **Suleja**: Muhammadu Awwal Ibrahim, 30 years on the throne in May 2024. *Reported*: the latest source is from 2024. His 1994 deposition and 2000 restoration are stated neutrally.",
         "- **Links:** Bida to the Nupe and (its dynasty) the Fulani; Kontagora to the Kambari and Hun-Saare (Dukawa); Borgu to the Busa and Bariba; Suleja to the Hausa and Koro.",
         "- **Not included:** Agaie (latest source 2014), Lapai and Kagara are research gaps for now.", "",
         "## The records", ""]
    for k, n, s, srcs, lvl in REC:
        L += [f"### {n} ({words(TEXT[k])} words; {lvl})", "", f"> {TEXT[k]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_162_niger_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_162_niger_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
