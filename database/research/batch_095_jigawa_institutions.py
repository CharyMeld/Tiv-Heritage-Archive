"""
Research batch 095 — Jigawa (Phase 3): traditional institutions. Researched 2026-10-02. Pattern: batches 077, 083, 089.

Five emirates (Jigawa State Council of Chiefs; Wikipedia 'Jigawa State', 'Dutse Emirate', 'Hadejia', 'Gumel',
'Kazaure', 'Ringim Emirate'):
  * Dutse — Fulani rule from 1806 (Salihi and Musa of the Yalligawa/Jalligawa clans); earlier Sarkin Dutse in the Kano
    Chronicle (Abdullahi Burja, 1438–52). Emir Hameem Nuhu Sanusi since February 2023 (Channels TV, 6 Feb 2023).
  * Hadejia — formerly Biram, one of the Hausa Bakwai; emirate from 1808; resisted the British in 1906 under Emir
    Muhammadu Mai-Shahada. Emir Adamu Abubakar Maje, 16th Emir, crowned 13 September 2002, chairman of the Jigawa State
    Council of Chiefs (University of Uyo, chancellor profile).
  * Gumel — founded about 1750 by Dan Juma and followers from the Mangawa; tributary of Bornu; never part of the Sokoto
    Caliphate; moved from Tumbi (now in Niger) in 1845. Emir Ahmed Muhammad Sani II (1981–2026) died 3 September 2026;
    his son Dr Lawan Ahmed Muhammad Sani appointed 17th Emir (Daily Trust, 6 Sep 2026; VON, 3 Sep 2026).
  * Kazaure — founded by Dan Tunku, a Fulani flag-bearer of the jihad; headquarters Kazaure since 1819; independence
    from Kano affirmed by Muhammad Bello; Emir Najib Hussaini Adamu since 1998 (Wikipedia).
  * Ringim — created November 1991 after the creation of Jigawa State; Ringim, Taura, Garki and Babura LGAs; Emir
    Sayyadi Abubakar Mahmoud Usman since its creation (Wikipedia).
"""
import json, re, sys
import batch_093b_jigawa_peoples as P93

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = lambda t, org, d, u, n, a=None: dict(source_type="news", source_kind="news", source_tier=3, title=t, organisation=org, publication_date=d, url=u,
                                            verification_status="needs_corroboration", notes=n, **({"author": a} if a else {}))
SOURCES = {
    "WDUT": WS("Dutse Emirate", "Legend of the hunter Duna-Magu; Kano Chronicle: Dutse defeated by Abdullahi Burja (1438–52); Fulani ascendancy 1806 under Salihi and Musa (Yalligawa and Jalligawa clans, from Birnin Gazargamo); list of emirs from Salihi (c. 1807); Nuhu Muhammad Sanusi 1995–2023; Hameem Nuhu Sanusi 2023–; palace town Garu."),
    "W_hadejia": P93.SOURCES["W_hadejia"],
    "W_gumel": P93.SOURCES["W_gumel"],
    "W_kazaure": P93.SOURCES["W_kazaure"],
    "WRIN": WS("Ringim Emirate", "Headquarters Ringim; created November 1991; Emir Sayyadi Abubakar Mahmoud Usman since its creation; Ringim, Taura, Garki and Babura LGAs."),
    "CHAN23": NEWS("43-Year-Old Sanusi Emerges New Emir Of Dutse", "Channels Television", "2023-02-06", "https://www.channelstv.com/?p=716136",
                   "Hameem Nuhu Sanusi named Emir of Dutse by the seven kingmakers, succeeding his father Nuhu Muhammad Sanusi, who died five days earlier.", "Sadiq Ilyasu"),
    "UYO": dict(source_type="official_website", source_kind="official_website", source_tier=2, title="Chancellor: profile of HRH Alhaji Adamu Abubakar Maje, Emir of Hadejia",
                organisation="University of Uyo", url="https://uniuyo.edu.ng/chancellor/", verification_status="needs_corroboration",
                notes=f"Born in Hadejia 15 October 1960; crowned 16th Emir of Hadejia and chairman of the Jigawa State Council of Chiefs on 13 September 2002, succeeding his father Abubakar Maje Haruna. Accessed {ACCESSED}."),
    "DT26": NEWS("Gov Namadi Names New Emir of Gumel", "Daily Trust", "2026-09-06", "https://dailytrust.com/gov-namadi-names-new-emir-of-gumel",
                 "Dr Lawan Ahmed Muhammad Sani appointed 17th Emir of Gumel, succeeding his father Ahmed Muhammad Sani II, who died on 3 September 2026 after about 46 years on the throne; chosen by the kingmakers from nine aspirants; formerly Chiroman Gumel.", "Ali Rabiu Ali"),
    "VON26": NEWS("President Tinubu Mourns Death of Gumel Emir", "Voice of Nigeria", "2026-09-03", "https://von.gov.ng/president-tinubu-mourns-death-of-gumel-emir/",
                  "Condolences on the death of the Emir of Gumel.", "Temitope Mustapha"),
    "WJIG": P93.SOURCES["WJIG"],
}
TEXT = {
 "dutse": """The Dutse Emirate is centred on Dutse, now the capital of Jigawa State. The Kano Chronicle records the defeat of Dutse by the Kano ruler Abdullahi Burja (1438–52), who then married a daughter of the Sarkin Dutse; by the early 18th century Dutse was a walled town of about seventy wards with twelve gates (Wikipedia). In 1806 Fulani led by Salihi and Musa, of the Yalligawa and Jalligawa clans, took control, and Dutse recognised the Emir of Kano as its spiritual leader in return for local autonomy (Wikipedia). The emirs' palace town is Garu. Hameem Nuhu Sanusi was chosen as Emir in February 2023, succeeding his father, Nuhu Muhammad Sanusi, who had reigned since 1995 (Channels TV; Wikipedia).""",
 "hadejia": """The Hadejia Emirate is centred on Hadejia, a Hausa town north of the Hadejia River in eastern Jigawa State. Under its earlier name, Biram, Hadejia is counted among the seven Hausa states of tradition, the Hausa Bakwai, said to have been ruled by descendants of Bayajidda and Daurama (Wikipedia). It became an emirate in 1808 during the Fulani jihad, and in 1906, under Emir Muhammadu Mai-Shahada, it resisted British occupation (Wikipedia). Adamu Abubakar Maje was crowned the 16th Emir on 13 September 2002, succeeding his father, and is chairman of the Jigawa State Council of Chiefs (University of Uyo). Wikipedia says the Kanuri of Jigawa live largely within the Hadejia Emirate.""",
 "gumel": """The Gumel Emirate was founded about 1750 by Dan Juma and his followers from the Mangawa, and soon became a tributary of Bornu; it survived the jihad of Usman dan Fodio and never became part of the Sokoto Caliphate (Wikipedia). Its present seat at Gumel, about 120 km north-east of Kano, dates from an 1845 move from Tumbi, now in the Republic of Niger, and it fought repeated wars with Hadejia, Kano and Zinder before accepting British rule in 1903 (Wikipedia). Ahmed Muhammad Sani II reigned from 1981 until his death on 3 September 2026; his son, Dr Lawan Ahmed Muhammad Sani, was appointed the 17th Emir after a selection among nine aspirants (Daily Trust, 6 September 2026).""",
 "kazaure": """The Kazaure Emirate was founded by Dan Tunku, a Fulani warrior and one of the flag-bearers of Usman dan Fodio's jihad, who came from Dambatta and carved an emirate out of the neighbouring Kano, Katsina and Daura emirates; Kazaure has been its seat since 1819 (Wikipedia). Its independence from Kano, long contested in war, was affirmed by Muhammad Bello of Sokoto, and under Dan Tunku's son Ibrahim Dambo (1824–57) the emirate grew (Wikipedia). Tradition says the town was first settled by Hausa hunters under Kutumbi, and that its name comes from 'kamar zaure', 'like an inner room' (Wikipedia). Najib Hussaini Adamu has been Emir since 1998 (Wikipedia).""",
 "ringim": """The Ringim Emirate, with its headquarters at Ringim, was created in November 1991, after Jigawa State was created on 27 August 1991, and covers Ringim, Taura, Garki and Babura LGAs (Wikipedia). Sayyadi Abubakar Mahmoud Usman has been Emir since its creation (Wikipedia).""",
}
REC = [  # key, name, slug, srcs, level, extra
    ("dutse", "Dutse Emirate", "dutse-emirate", ["WDUT", "CHAN23"], "well_documented", dict(founded_year=1806, founded_text="1806, Fulani ascendancy (Wikipedia); Sarkin Dutse attested in the Kano Chronicle, 15th century", founded_precision="year")),
    ("hadejia", "Hadejia Emirate", "hadejia-emirate", ["W_hadejia", "UYO", "WJIG"], "well_documented", dict(founded_year=1808, founded_text="1808, as an emirate (Wikipedia); earlier the Hausa state of Biram", founded_precision="year")),
    ("gumel", "Gumel Emirate", "gumel-emirate", ["W_gumel", "DT26", "VON26"], "well_documented", dict(founded_year=1750, founded_text="about 1750, by Dan Juma (Wikipedia)", founded_precision="circa")),
    ("kazaure", "Kazaure Emirate", "kazaure-emirate", ["W_kazaure"], "well_documented", dict(founded_year=1819, founded_text="founded by Dan Tunku in the jihad; seat at Kazaure since 1819 (Wikipedia)", founded_precision="circa")),
    ("ringim", "Ringim Emirate", "ringim-emirate", ["WRIN"], "reported", dict(founded_year=1991, founded_text="November 1991 (Wikipedia)", founded_precision="month")),
]
RECORDS = []
for key, name, slug, srcs, lvl, extra in REC:
    t = TEXT[key]
    f = dict(polity_type="emirate", name=name, slug=slug, is_extant=1, summary=t.split(". ")[0] + ".", description=t)
    f.update(extra)
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl, fields=f, srcs=[(s, name) for s in srcs]))
J = lambda l: f"@admin_units:lga:jigawa/{l}"
SEAT = "Seat of the emirate (not a statement of its full jurisdiction)."
RELATIONS = [
    dict(frm="dutse", type="located_in", to=J("dutse"), source="WDUT", evidence="single_reliable_source", level="well_documented", notes="Dutse town (Wikipedia). " + SEAT),
    dict(frm="hadejia", type="located_in", to=J("hadejia"), source="W_hadejia", evidence="single_reliable_source", level="well_documented", notes="Hadejia town (Wikipedia). " + SEAT),
    dict(frm="gumel", type="located_in", to=J("gumel"), source="W_gumel", evidence="single_reliable_source", level="well_documented", notes="Gumel town, since 1845 (Wikipedia). " + SEAT),
    dict(frm="kazaure", type="located_in", to=J("kazaure"), source="W_kazaure", evidence="single_reliable_source", level="well_documented", notes="Kazaure, the emirate's headquarters since 1819 (Wikipedia). " + SEAT),
]
for l in ["ringim", "taura", "garki", "babura"]:
    RELATIONS.append(dict(frm="ringim", type="located_in", to=J(l), source="WRIN", evidence="single_reliable_source", level="reported",
                          notes="Wikipedia (Ringim Emirate): one of the LGAs under the emirate" + ("; its headquarters is Ringim." if l == "ringim" else ".")))
RELATIONS += [
    dict(frm="gumel", type="associated_with", to="@ethnic_groups:manga", role="founded by Dan Juma and followers from the Mangawa (c. 1750)", source="W_gumel", evidence="single_reliable_source", level="reported", notes="Wikipedia (Gumel)."),
    dict(frm="kazaure", type="associated_with", to="@ethnic_groups:fulani", role="founded by the Fulani flag-bearer Dan Tunku", source="W_kazaure", evidence="single_reliable_source", level="reported", notes="Wikipedia (Kazaure)."),
    dict(frm="dutse", type="associated_with", to="@ethnic_groups:fulani", role="Fulani rulers since 1806", source="WDUT", evidence="single_reliable_source", level="reported", notes="Wikipedia (Dutse Emirate)."),
    dict(frm="hadejia", type="associated_with", to="@ethnic_groups:hausa", role="formerly the Hausa state of Biram (Hausa Bakwai)", source="W_hadejia", evidence="single_reliable_source", level="reported", notes="Wikipedia (Hadejia)."),
    dict(frm="hadejia", type="associated_with", to="@ethnic_groups:kanuri", role="the Kanuri of Jigawa live largely within the emirate", source="WJIG", evidence="single_reliable_source", level="reported", notes="Wikipedia (Jigawa State)."),
    dict(frm="kazaure", type="associated_with", to="@polities:kano-emirate", role="independence from Kano affirmed by Muhammad Bello", source="W_kazaure", evidence="single_reliable_source", level="reported", notes="Wikipedia (Kazaure)."),
    dict(frm="dutse", type="associated_with", to="@polities:kano-emirate", role="recognised the Emir of Kano as spiritual leader after 1806", source="WDUT", evidence="single_reliable_source", level="reported", notes="Wikipedia (Dutse Emirate)."),
]
NAMES = [
    dict(record="hadejia", name="Biram", name_type="historical", usage_notes="Earlier name of Hadejia as one of the Hausa Bakwai (Wikipedia).", srcs=["W_hadejia"]),
    dict(record="hadejia", name="Haɗejiya", name_type="spelling_variant", usage_notes="Hausa spelling (Wikipedia); also Haɗeja.", srcs=["W_hadejia"]),
    dict(record="dutse", name="Sarkin Dutse", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WDUT"]),
]
GAPS = [
    ("Jigawa: emirate jurisdictions", "Only Ringim's LGAs are given (Wikipedia). The LGAs of the Dutse, Hadejia, Gumel and Kazaure emirates are not listed in a source read."),
    ("Jigawa: Ringim", "Rests on a short Wikipedia article; an official or press source on its creation and emir is needed."),
    ("Jigawa: Kazaure's emir", "Dated to Wikipedia only ('1998 to date')."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Jigawa traditional institutions: the Dutse, Hadejia, Gumel, Kazaure and Ringim emirates.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 095 — Jigawa: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **The 5 emirates of Jigawa:**",
         "  - **Dutse**: Fulani rulers since 1806; Emir Hameem Nuhu Sanusi since 2023",
         "  - **Hadejia**: formerly the Hausa state of Biram; an emirate from 1808; Emir Adamu Abubakar Maje since 2002, chairman of the State Council of Chiefs",
         "  - **Gumel**: founded about 1750 by Mangawa and never part of the Sokoto Caliphate. Its emir of about 46 years died on **3 September 2026**, and his son Dr Lawan Ahmed Muhammad Sani was appointed the 17th Emir (Daily Trust, 6 September 2026).",
         "  - **Kazaure**: founded by the Fulani flag-bearer Dan Tunku; seat since 1819",
         "  - **Ringim**: created in 1991",
         "- **Links:**",
         "  - **Seat LGAs:** each emirate is linked to the LGA of its seat; Ringim also to the LGAs it covers.",
         "  - **Peoples:** Manga (Gumel), Fulani (Kazaure, Dutse), Hausa and Kanuri (Hadejia).",
         "  - **The Kano Emirate:** Kazaure and Dutse.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, name, slug, srcs, lvl, extra in REC:
        L += [f"## {name} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_095_jigawa_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_095_jigawa_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
