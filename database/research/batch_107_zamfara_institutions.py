"""
Research batch 107 — Zamfara (Phase 3): traditional institutions. Researched 2026-10-02. Pattern: batches 089, 095, 101.

No complete official list of Zamfara's emirates was found (press reports speak of 17, then 18 or 19 emirate councils
after the Bazai (2019 bill) and Yandoton Daji (2022 amendment) emirates). This batch records only polities with solid
sources; the rest are a gap.
  * Kingdom of Zamfara (Wikipedia 'Kingdom of Zamfara'): Hausa kingdom; early chiefdoms Dutsi, Togai, Kiyawa and Jata;
    capital Birnin Zamfara, destroyed by Gobir about 1756 (Barth); absorbed by the Sokoto Caliphate in the 19th century.
    Wikipedia 'Zamfara State': a new capital at Anka by the second half of the 19th century. Wikipedia 'Kaura Namoda':
    Namoda was appointed Sarkin Zamfara by the jihadists, 'However, Zamfara never became an emirate'.
  * Anka Emirate: Emir Attahiru Ahmad, chairman of the Zamfara State Council of Chiefs (Blueprint, 9 January 2022).
  * Gusau Emirate: Emir Ibrahim Bello died 25 July 2025; his son Abdulkadir Ibrahim Bello appointed 16th Emir (Sarkin
    Katsinan Gusau) on 29 July 2025 (Channels TV).
  * Shinkafi Emirate: a district under Magaji rulers from 1835, upgraded in 2000 by Governor Ahmed Sani to a
    second-class emirate (Sarkin Gabas of Shinkafi); Emir Mohammadu Makwashe from 2000 (Wikipedia 'Shinkafi'). Bazai
    Emirate proposed out of it in 2019 (Daily Trust, 22 May 2019).
"""
import json, re, sys

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = lambda t, org, d, u, n, a=None: dict(source_type="news", source_kind="news", source_tier=3, title=t, organisation=org, publication_date=d, url=u,
                                            verification_status="needs_corroboration", notes=n, **({"author": a} if a else {}))
SOURCES = {
    "WKZ": WS("Kingdom of Zamfara", "Hausa kingdom (Masarautar Zamfara); early chiefdoms Dutsi, Togai, Kiyawa/Kiawa and Jata; Birnin Zamfara near the Gagare River; destroyed by Gobir about 1756 (Barth); absorbed by the Sokoto Caliphate; Muhammadu Fari, Sarkin Zamfara of Anka, a source of its history; the Zamfarawa claim descent from Maguzawa hunters."),
    "WZAM": WS("Zamfara State", "Reused. Birnin Zamfara destroyed by Gobir in the first half of the 18th century; a new capital at Anka by the second half of the 19th century."),
    "WKNA": WS("Kaura Namoda", "Namoda fought with the Sokoto jihadists and was appointed Sarkin Zamfara; 'However, Zamfara never became an emirate'."),
    "WSHI": WS("Shinkafi", "District under Magaji rulers from 1835; upgraded in 2000 by Governor Ahmed Sani (Yariman Bakura) to an emirate, Sarkin Gabas of Shinkafi (second class); Emir Mohammadu Makwashe 2000 to date."),
    "WGUS": WS("Gusau", "The Emir of Gusau, Sarkin Katsinan Gusau, is Abdulkadir Ibrahim Bello, crowned after the death of his father Ibrahim Muhammad Bello in July 2025."),
    "BP22": NEWS("Zamfara: Bandits killed 58 in our emirates – Monarchs", "Blueprint", "2022-01-09", "https://blueprint.ng/zamfara-bandits-killed-58-in-our-emirates-monarchs/",
                 "'the emir of Anka and chairman Zamfara state council of chiefs, Alhaji Attahiru Ahmad'."),
    "CH25": NEWS("Governor Lawal Appoints New Emir Of Gusau Emirate", "Channels Television", "2025-07-29", "https://www.channelstv.com/2025/07/29/governor-lawal-appoints-new-emir-of-gusau-emirate/",
                 "Abdulkadir Ibrahim Bello appointed Emir and Sarkin Katsina of Gusau, the 16th Emir, after his father Ibrahim Bello died on 25 July 2025.", "Adeniyi Salaudeen"),
    "DT19": NEWS("Zamfara creates additional emirate council", "Daily Trust", "2019-05-22", "https://dailytrust.com/zamfara-creates-additional-emirate-council/",
                 "The House of Assembly passed a bill creating the Bazai Emirate out of the Shinkafi Emirate."),
}
TEXT = {
 "kingdom": """The Kingdom of Zamfara, in Hausa Masarautar Zamfara, was a Hausa kingdom in what is now north-western Nigeria (Wikipedia). It grew from the early chiefdoms of Dutsi, Togai, Kiyawa and Jata, and its people, the Zamfarawa, claim descent from Maguzawa hunters. Its walled capital, Birnin Zamfara, was destroyed by Gobir about 1756, according to Barth; the kingdom was weakened by wars with Gobir and absorbed by the Sokoto Caliphate in the 19th century, and a new capital was established at Anka (Wikipedia). Although the jihad leader Namoda was appointed Sarkin Zamfara, Wikipedia notes that 'Zamfara never became an emirate'.""",
 "anka": """The Anka Emirate is centred on Anka, which Wikipedia names as the later capital of the Zamfara kingdom, established in the second half of the 19th century; the Sarkin Zamfara of Anka is cited as a source for the kingdom's history. In January 2022 Blueprint named Alhaji Attahiru Ahmad as Emir of Anka and chairman of the Zamfara State Council of Chiefs, speaking on bandit attacks on communities in his emirate. A later confirmation of the emir and the emirate's history is not given in a source read.""",
 "gusau": """The Gusau Emirate is centred on Gusau, the capital of Zamfara State; its emir holds the title Sarkin Katsinan Gusau (Wikipedia; Channels TV). Emir Ibrahim Bello died on 25 July 2025 after about ten years on the throne, and on 29 July 2025 Governor Dauda Lawal approved the appointment of his eldest son, Abdulkadir Ibrahim Bello, as the 16th Emir (Channels TV).""",
 "shinkafi": """The Shinkafi Emirate is centred on Shinkafi, in north-eastern Zamfara State. According to Wikipedia, Shinkafi was a district under rulers with the title Magaji from 1835 until 2000, when the first executive governor of Zamfara, Ahmed Sani, raised it to a second-class emirate whose ruler is styled Sarkin Gabas of Shinkafi; Mohammadu Makwashe has been Emir since 2000. In 2019 the House of Assembly passed a bill to create the Bazai Emirate out of the Shinkafi Emirate (Daily Trust).""",
}
REC = [
    ("kingdom", "Kingdom of Zamfara", "kingdom-of-zamfara", ["WKZ", "WZAM", "WKNA"], "well_documented", dict(polity_type="kingdom", is_extant=0, founded_precision="unknown", ended_text="absorbed by the Sokoto Caliphate in the 19th century", ended_precision="century")),
    ("anka", "Anka Emirate", "anka-emirate", ["BP22", "WZAM"], "reported", dict(polity_type="emirate", is_extant=1)),
    ("gusau", "Gusau Emirate", "gusau-emirate", ["CH25", "WGUS"], "well_documented", dict(polity_type="emirate", is_extant=1)),
    ("shinkafi", "Shinkafi Emirate", "shinkafi-emirate", ["WSHI", "DT19"], "well_documented", dict(polity_type="emirate", is_extant=1, founded_year=2000, founded_text="2000, as a second-class emirate (Wikipedia)", founded_precision="year")),
]
RECORDS = []
for key, name, slug, srcs, lvl, extra in REC:
    t = TEXT[key]
    f = dict(name=name, slug=slug, summary=t.split(". ")[0] + ".", description=t)
    f.update(extra)
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl, fields=f, srcs=[(s, name) for s in srcs]))
Z = lambda l: f"@admin_units:lga:zamfara/{l}"
RELATIONS = [
    dict(frm="kingdom", type="located_in", to="@admin_units:state:zamfara", source="WKZ", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Kingdom of Zamfara)."),
    dict(frm="anka", type="located_in", to=Z("anka"), source="BP22", evidence="single_reliable_source", level="reported", notes="Anka town. Seat (not a statement of full jurisdiction)."),
    dict(frm="gusau", type="located_in", to=Z("gusau"), source="CH25", evidence="multiple_sources", level="well_documented", notes="Gusau town. Seat (not a statement of full jurisdiction)."),
    dict(frm="shinkafi", type="located_in", to=Z("shinkafi"), source="WSHI", evidence="single_reliable_source", level="well_documented", notes="Shinkafi town. Seat (not a statement of full jurisdiction)."),
    dict(frm="anka", type="associated_with", to="kingdom", role="Anka became the Zamfara kingdom's capital in the later 19th century", source="WZAM", evidence="multiple_sources", level="reported",
         notes="Wikipedia (Zamfara State; Kingdom of Zamfara: 'Sarkin Zamfara of Anka')."),
    dict(frm="kingdom", type="associated_with", to="@ethnic_groups:hausa", role="Hausa kingdom of the Zamfarawa", source="WKZ", evidence="single_reliable_source", level="well_documented", notes="Wikipedia."),
]
NAMES = [
    dict(record="kingdom", name="Masarautar Zamfara", name_type="endonym", usage_notes="Hausa name (Wikipedia).", srcs=["WKZ"]),
    dict(record="gusau", name="Sarkin Katsinan Gusau", name_type="alternative", usage_notes="Title of the ruler (Wikipedia; Channels TV).", srcs=["CH25"]),
    dict(record="shinkafi", name="Sarkin Gabas of Shinkafi", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WSHI"]),
]
GAPS = [
    ("Zamfara: the list of emirates", "Press reports speak of 17 emirate councils, then 18–19 after the Bazai (2019 bill) and Yandoton Daji (2022 amendment) emirates; no complete official list was found. Only Anka, Gusau and Shinkafi are recorded; others mentioned in the press (Kaura Namoda, Maru, Tsafe, Talata Mafara, Bungudu, Maradun, Zurmi, Moriki, Bukkuyum…) need an official list."),
    ("Zamfara: Bazai and Yandoton Daji", "Bills passed in 2019 and 2022; whether the governor assented and who the emirs are was not confirmed."),
    ("Zamfara: Anka's emir", "Dated to January 2022 (Blueprint); a 2025–26 confirmation is needed."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Zamfara traditional institutions: the Kingdom of Zamfara and the Anka, Gusau and Shinkafi emirates (others a gap).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 107 — Zamfara: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **4 polities:**",
         "  - the **Kingdom of Zamfara** (Hausa; capital Birnin Zamfara, destroyed by Gobir about 1756; later Anka)",
         "  - the **Anka Emirate** (emir and council chairman Attahiru Ahmad, dated January 2022)",
         "  - the **Gusau Emirate** (new 16th emir appointed 29 July 2025, after his father's death)",
         "  - the **Shinkafi Emirate** (raised to an emirate in 2000)",
         "- **Handled with care:**",
         "  - **Only emirates with solid sources are recorded.** No complete official list of Zamfara's emirates was found: the press speaks of 17, then 18–19.",
         "  - **The rest are listed as a gap:** Kaura Namoda, Maru, Tsafe, Talata Mafara, Bungudu, Maradun, Zurmi, Moriki and others, plus the 2019/2022 Bazai and Yandoton Daji bills.",
         "  - **Anka's emir** is dated to the 2022 source.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, name, slug, srcs, lvl, extra in REC:
        L += [f"## {name} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_107_zamfara_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_107_zamfara_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
