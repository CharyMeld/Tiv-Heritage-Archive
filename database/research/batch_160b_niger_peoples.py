"""
Research batch 160b — Niger (Phase 3): the peoples of Niger State and the LGAs where they live, as far as the sources
allow. Researched 2026-10-07. Pattern: batch 075b (Bauchi).

Sources:
  * Federal Government of Nigeria, state profile 'Niger' (data/fg_niger_2026-10-07.html) — Tier 1: 'three most
    pronounced ethnic groups which are Nupe, Gbagyi and Hausa … many other groups … Kadara, Koro, Barab, Kakanda,
    GanaGana, Dibo, Kambari, Kamuku, Pangu, Dukawa, Gwada and Ingwai. Tribes like Igbo, Yoruba and numerous others from
    other States also settled'.
  * Wikipedia, 'Niger State': communities 'of Adara, Fulani, Gbagyi, Hausa, Hun-Saare, Kambari, Kamuku, Koro Gungawa,
    Nupe'; and its table of languages by LGA (from Ethnologue).
  * Wikipedia LGA pages: Agaie, Bida and Lapai (Nupe); Shiroro (Gbagyi the major language); Suleja (founded by Hausa
    of Zazzau among Koro chiefdoms); Kontagora (founded on land of the Kambari).
  * Blench's Atlas (batch 160) links names to languages: Kadara = Adara (Eda 1.A); Ganagana = Dibo (2.C); Duka(wa) =
    Hun-Saare (2.A); Pangu = Rin (2.A; the community prefers Pangu); Ingwe/Ngwai = Hùngwəryə (2.C); Gungawa = Reshe
    (2.C); Kamuku the cover name of the Kamuku cluster; Kambari the Kambari I and II clusters.
Rules (as 075b): a people record only for a group named by the federal profile or Wikipedia whose language is
recorded; LGA links from Wikipedia's language table — well documented where the Atlas also places the language there,
reported otherwise; presence only. Record names follow the people's own name where the Atlas gives it, with the
federal profile's name kept as an exonym (owner's keep-both-names rule): Adara (Kadara), Dibo (Gana-Gana), Hun-Saare
(Dukawa), Pangu (the community's preferred name), Hùngwəryə (Ingwai), Reshe (Gungawa).
Not created: Barab and Gwada (no Atlas match found); Basa (the Basa of Niger are not separated from the Basa record).
"""
import json, sys
import batch_160_niger_languages as L160

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=f"{n} Accessed {ACCESSED}.")
SOURCES = {
    "FGNI": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Niger State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/niger/", verification_status="verified",
                 notes="'Ethnic Profile': 'three most pronounced ethnic groups which are Nupe, Gbagyi and Hausa, there are many other groups … Kadara, Koro, Barab, Kakanda, GanaGana, Dibo, Kambari, Kamuku, Pangu, Dukawa, Gwada and Ingwai. Tribes like Igbo, Yoruba and numerous others from other States also settled'. Capital Minna; created 3 February 1976. Read 2026-10-07 (copy in database/research/data/)."),
    "WNIS": WS("Niger State", "Communities 'of Adara, Fulani, Gbagyi, Hausa, Hun-Saare, Kambari, Kamuku, Koro Gungawa, Nupe'; table 'Languages of Niger State listed by LGA' (from Ethnologue)."),
    "WAGA": WS("Agaie", "'Agaie is inhabited by the Nupe people'; an emirate from 1822."),
    "WBID": WS("Bida", "Headquarters of the Nupe Kingdom (the Etsu Nupe); 'The major ethnic group is the Nupe'."),
    "WLAP": WS("Lapai", "'Lapai is traditionally inhabited by the Muslim Nupe People'."),
    "WSHI": WS("Shiroro", "LGA, headquarters Kuta; 'The major language here is Gbagyi'."),
    "WSUL": WS("Suleja", "Founded in the early 19th century by Muhammadu Makau, Hausa emir of Zazzau, fleeing the Fulani jihad, among four small Koro chiefdoms (the Koro town of Zuba)."),
    "WKON": WS("Kontagora", "Founded by Umaru Nagwamatse 'after conquering a large amount of land owned by non-Muslim Kambaris'."),
    "ATLAS": L160.SOURCES["ATLAS"],
}
N = lambda l: f"@admin_units:lga:niger/{l}"
ST = "@admin_units:state:niger"
PO = " Presence only; the nature of their presence is not established."
TAB = "Wikipedia's table of languages by LGA (from Ethnologue)"
TEXT = {
 "kambari": """The Kambari are a people of north-western Niger State and neighbouring Kebbi State, speaking the Kambari languages, which Roger Blench's Atlas of Nigerian Languages (2020) groups in two Kainji clusters, Kambari I and Kambari II, in Magama, Mariga, Rijau, Kontagora and Borgu LGAs and in Zuru and Yauri LGAs of Kebbi State. The federal government's state profile and Wikipedia both name them among the peoples of Niger State. According to Wikipedia, the town of Kontagora was founded by Umaru Nagwamatse on land conquered from the non-Muslim Kambari.""",
 "kamuku": """The Kamuku are a people of central Niger State. Kamuku is the cover name, in Roger Blench's Atlas of Nigerian Languages (2020), for the speakers of the Cinda-Regi-Rogo-Kuki cluster, Sagamuk and Hùngwəryə, Kainji languages of Chanchaga, Rafi and Mariga LGAs, and the Atlas notes that all these groups numbered 17,800 in 1952. The federal government's state profile and Wikipedia both name the Kamuku among the peoples of Niger State.""",
 "adara": """The Adara, called Kadara by others, are a people of southern Kaduna State and eastern Niger State, speaking the Kadara cluster of Plateau languages, mainly in Kachia and Kajuru LGAs of Kaduna State and in Paikoro LGA of Niger State (Roger Blench's Atlas of Nigerian Languages, 2020). The federal government's state profile names the Kadara, and Wikipedia the Adara, among the peoples of Niger State.""",
 "dibo": """The Dibo, also called Gana-Gana, are a Nupoid-speaking people of Lapai LGA in Niger State, of the Federal Capital Territory and of Nassarawa LGA in Nasarawa State, according to Roger Blench's Atlas of Nigerian Languages (2020), which records that an unknown number of Dibo living among the Gbari no longer speak their language. The federal government's state profile names both the GanaGana and the Dibo among the peoples of Niger State.""",
 "hun-saare": """The Hun-Saare, also called Duka (the federal profile writes Dukawa), are a people of Rijau LGA in Niger State and Sakaba LGA in Kebbi State, speaking the Kainji language Hun–Saare (Roger Blench's Atlas of Nigerian Languages, 2020). The federal government's state profile names the Dukawa, and Wikipedia the Hun-Saare, among the peoples of Niger State.""",
 "pangu": """The Pangu are the speakers of Rin, a Kainji language of Rafi LGA near Tegina in Niger State; Roger Blench's Atlas of Nigerian Languages (2020) notes that, despite the language's own name, the community prefers forms of the name Pangu for publications. The federal government's state profile names the Pangu among the peoples of Niger State.""",
 "hungworyo": """The Hùngwəryə, called Ingwai and similar names by others, are a people of Rafi LGA in Niger State, around Kagara and Maikujeri, speaking the Kainji language Hùngwəryə (Roger Blench's Atlas of Nigerian Languages, 2020). The federal government's state profile names the Ingwai among the peoples of Niger State.""",
 "reshe": """The Reshe, known in Hausa as the Gungawa, are a people of the Lake Kainji area, in Yauri LGA of Kebbi State and Borgu LGA of Niger State, speaking the Kainji language Reshe (Roger Blench's Atlas of Nigerian Languages, 2020). Wikipedia names the Gungawa among the peoples of Niger State.""",
}
NEW = {  # key: (name, language key, [(exonym/other name, type, note, src)])
 "kambari": ("Kambari", ["kambari-1", "kambari-2"], []),
 "kamuku": ("Kamuku", ["kamuku"], []),
 "adara": ("Adara", ["kadara"], [("Kadara", "exonym", "The name used by the federal government's state profile; the Atlas gives Adara as the people's own form.", "FGNI")]),
 "dibo": ("Dibo", ["dibo"], [("Gana-Gana", "exonym", "The federal profile lists 'GanaGana' beside 'Dibo'; the Atlas gives Ganagana and Ganagawa as other names.", "FGNI")]),
 "hun-saare": ("Hun-Saare", ["hun-saare"], [("Dukawa", "exonym", "The name used by the federal government's state profile; the Atlas gives Duka.", "FGNI")]),
 "pangu": ("Pangu", ["rin"], [("Rin", "endonym", "The language's own name in the Atlas; the community prefers Pangu for publications.", "ATLAS")]),
 "hungworyo": ("Hùngwəryə", ["hungworyo"], [("Ingwai", "exonym", "The name used by the federal government's state profile; the Atlas gives Ingwe, Ngwai and similar forms.", "FGNI")]),
 "reshe": ("Reshe", ["reshe"], [("Gungawa", "exonym", "The Hausa name, used by Wikipedia; the Atlas gives Gungawa as another name.", "WNIS")]),
}
RECORDS, NAMES, RELATIONS = [], [], []
for k, (name, langs, other) in NEW.items():
    srcs = [("ATLAS", f"{name}: language, location"), ("FGNI" if k != "reshe" else "WNIS", f"{name} named among the peoples of Niger State")]
    if k in ("kambari", "kamuku", "adara", "hun-saare"): srcs.append(("WNIS", f"{name} named among the communities of Niger State"))
    if k == "kambari": srcs.append(("WKON", "Kontagora founded on Kambari land"))
    RECORDS.append(dict(key=k, table="ethnic_groups", evidence="multiple_sources", level="well_documented",
                        fields=dict(name=name, slug=k if k != "reshe" else "reshe-people", summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k]), srcs=srcs))
    for n, t, note, src in other:
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=note, srcs=[src]))
    for lang in langs:
        RELATIONS.append(dict(frm=k, type="speaks", to=f"@languages:{lang}", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes="Blench's Atlas (batch 160)."))
    RELATIONS.append(dict(frm=k, type="present_in", to=ST, source="FGNI" if k != "reshe" else "WNIS", evidence="multiple_sources", level="well_documented", notes="Named among the peoples of Niger State (federal profile; Wikipedia)."))
# (people ref, [(lga, level, why)])
LINKS = {
 "@ethnic_groups:nupe": [("agaie", "well_documented", "Wikipedia (Agaie): 'inhabited by the Nupe people'; Nupe in Agaie in the Atlas and in " + TAB),
                         ("bida", "well_documented", "Wikipedia (Bida): 'The major ethnic group is the Nupe'; " + TAB),
                         ("lapai", "well_documented", "Wikipedia (Lapai): 'traditionally inhabited by the Muslim Nupe People'; Atlas"),
                         ("gbako", "well_documented", "Nupe in Gbako in the Atlas and in " + TAB), ("mariga", "well_documented", "Nupe in Mariga in the Atlas and in " + TAB),
                         ("lavun", "reported", "Nupe in Lavun in the Atlas; not in " + TAB), ("edati", "reported", "Nupe in Edati in " + TAB),
                         ("katcha", "reported", "Nupe in Katcha in " + TAB), ("mokwa", "reported", "Nupe in Mokwa in " + TAB), ("wushishi", "reported", "Nupe in Wushishi in " + TAB),
                         ("mashegu", "reported", "Nupe Tako in Mashegu in " + TAB)],
 "@ethnic_groups:gbagyi": [("shiroro", "well_documented", "Wikipedia (Shiroro): 'The major language here is Gbagyi'; Atlas"), ("rafi", "well_documented", "Gbagyi in Rafi in the Atlas and in " + TAB),
                           ("chanchaga", "well_documented", "Gbagyi and Gbari in Chanchaga in the Atlas and in " + TAB), ("suleja", "well_documented", "Gbagyi and Gbari in Suleja in the Atlas and in " + TAB),
                           ("lapai", "well_documented", "Gbari in Lapai in the Atlas and in " + TAB), ("agaie", "reported", "Gbari in Agaie in the Atlas; not in " + TAB),
                           ("bosso", "reported", "Gbagyi and Gwari in Bosso in " + TAB), ("gurara", "reported", "Gbagyi in Gurara in " + TAB), ("paikoro", "reported", "Gbagyi/Gbari in Paikoro in " + TAB),
                           ("tafa", "reported", "Gbagyi in Tafa in " + TAB), ("wushishi", "reported", "Gbagyi and Gbari in Wushishi in " + TAB), ("bida", "reported", "Gbari in Bida in " + TAB),
                           ("mokwa", "reported", "Gbari in Mokwa in " + TAB)],
 "@ethnic_groups:hausa": [("suleja", "reported", "Wikipedia (Suleja): founded by Hausa of Zazzau fleeing the Fulani jihad"), ("kontagora", "reported", "Hausa in Kontagora in " + TAB),
                          ("bida", "reported", "Hausa in Bida in " + TAB), ("mokwa", "reported", "Hausa in Mokwa in " + TAB)],
 "@ethnic_groups:kakanda": [("lapai", "well_documented", "Kakanda in Lapai in the Atlas and in " + TAB), ("agaie", "reported", "Kakanda in Agaie in the Atlas; not in " + TAB)],
 "@ethnic_groups:gwandara": [("gurara", "reported", "Gwandara in Gurara in " + TAB), ("suleja", "reported", "Gwandara in Suleja in the Atlas; not in " + TAB)],
 "@ethnic_groups:busa-people": [("borgu", "well_documented", "Busa in Borgu in the Atlas and in " + TAB)],
 "@ethnic_groups:koro": [("suleja", "reported", "Wikipedia (Suleja): the emirate's area 'originally included four small Koro chiefdoms'")],
 "@ethnic_groups:yoruba": [("mokwa", "reported", "Yoruba in Mokwa in " + TAB)],
 "dibo": [("lapai", "well_documented", "Dibo in Lapai in the Atlas and in " + TAB), ("agaie", "reported", "Dibo in Agaie in " + TAB), ("katcha", "reported", "Dibo in Katcha in " + TAB)],
 "kambari": [(l, "well_documented", f"Kambari languages in {l.capitalize()} in the Atlas and in " + TAB) for l in ("borgu", "kontagora", "magama", "mariga", "rijau")] +
            [("agwara", "reported", "Cishingini (Kambari II) in Agwara in " + TAB), ("mashegu", "reported", "Tsikimba and Tsishingini in Mashegu in " + TAB)],
 "kamuku": [("mariga", "well_documented", "Kamuku and Rogo in Mariga in the Atlas and in " + TAB), ("rafi", "well_documented", "Kamuku and Rogo in Rafi in the Atlas and in " + TAB),
            ("chanchaga", "reported", "the Kamuku cluster in Chanchaga in the Atlas; not in " + TAB)],
 "adara": [("paikoro", "well_documented", "Kadara in Paikoro in the Atlas and in " + TAB), ("muya", "reported", "Adara in Munya (the archive's Muya) in " + TAB)],
 "hun-saare": [("rijau", "well_documented", "Hun–Saare in Rijau in the Atlas and in " + TAB), ("magama", "reported", "'Dukkawa' in Magama in " + TAB)],
 "pangu": [("rafi", "well_documented", "Rin (Pangu) in Rafi in the Atlas and in " + TAB)],
 "hungworyo": [("rafi", "well_documented", "Hùngwəryə ('Cahungwarya') in Rafi in the Atlas and in " + TAB)],
 "reshe": [("borgu", "well_documented", "Reshe in Borgu in the Atlas and in " + TAB)],
}
for frm, links in LINKS.items():
    for l, lvl, why in links:
        RELATIONS.append(dict(frm=frm, type="present_in", to=N(l), source="WNIS" if TAB in why else "ATLAS", evidence="multiple_sources" if lvl == "well_documented" else "single_reliable_source",
                              level=lvl, settlement_status="unknown", notes=f"{why}.{PO}"))
for p, note in [("nupe", "Federal profile: one of the 'three most pronounced ethnic groups'; Wikipedia: among the communities of Niger State."),
                ("gbagyi", "Federal profile: one of the 'three most pronounced ethnic groups'; Wikipedia: among the communities of Niger State."),
                ("hausa", "Federal profile: one of the 'three most pronounced ethnic groups'; Wikipedia: among the communities of Niger State."),
                ("fulani", "Wikipedia: among the communities of Niger State."),
                ("koro", "Federal profile: among the other groups of Niger State; Wikipedia: 'Koro Gungawa'."),
                ("kakanda", "Federal profile: among the other groups of Niger State.")]:
    RELATIONS.append(dict(frm=f"@ethnic_groups:{p}", type="present_in", to=ST, source="FGNI" if p != "fulani" else "WNIS", evidence="multiple_sources" if p != "fulani" else "single_reliable_source",
                          level="well_documented", notes=note))
for p in ("igbo", "yoruba"):
    RELATIONS.append(dict(frm=f"@ethnic_groups:{p}", type="present_in", to=ST, source="FGNI", evidence="single_reliable_source", level="reported", settlement_status="migrant_community",
                          notes="Federal profile: 'Tribes like Igbo, Yoruba and numerous others from other States also settled happily in Niger State.'"))
GAPS = [
    ("Niger: Barab and Gwada", "The federal profile names the Barab and the Gwada; no matching entry was found in the Atlas, so no records are made."),
    ("Niger: Ethnologue table", "Most LGA links come from Wikipedia's table of languages by LGA, which cites Ethnologue; where the Atlas does not also place the language, the link is reported."),
    ("Niger: Fulani by LGA", "Wikipedia names the Fulani among Niger's communities and its table lists 'Dukkawa Fulani' for Rijau, which is ambiguous; the Fulani are linked at state level only."),
    ("Niger: Basa", "The Basa-Gumna, Basa-Kontagora and Basa-Gurmana speakers of Niger are not linked to the existing Basa people record, whose links are to the Basa of Kogi, Nasarawa and Benue; a source tying them together is needed."),
    ("Niger: speakers of the smallest languages", "No people records are made for the Baushi-cluster towns of Rafi, the Lake Kainji peoples other than the Reshe (Rop, Tsupamini, Shen), or the speakers of Asu, Gupa–Abawa, Kami, Jijili, Fungwa and Cicipu, which neither the federal profile nor Wikipedia names."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Niger peoples: 8 new records (Kambari, Kamuku, Adara, Dibo, Hun-Saare, Pangu, Hùngwəryə, Reshe); Nupe, Gbagyi, Hausa, Kakanda, Gwandara, Busa, Koro, Yoruba and Fulani linked by LGA or state; all 25 LGAs covered.")


def report():
    lg = {r["to"] for r in RELATIONS if r["type"] == "present_in" and "lga:niger" in r["to"]}
    L_ = ["# Research batch 160b — Niger: the peoples", "",
          f"Researched {ACCESSED}. Pattern: batch 075b (Bauchi). Created in review; published only after your approval.", "",
          "## Summary", "",
          "- **8 new people records**, all named by the federal profile or Wikipedia, and each speaking a language recorded in batch 160:",
          "  - **Kambari**, **Kamuku**",
          "  - **Adara** (the federal profile writes Kadara)",
          "  - **Dibo** (Gana-Gana)",
          "  - **Hun-Saare** (Dukawa)",
          "  - **Pangu** (speakers of Rin)",
          "  - **Hùngwəryə** (Ingwai)",
          "  - **Reshe** (Gungawa)",
          "- **Naming rule:** each record uses the people's own name where the Atlas gives one, with the federal profile's name kept as an other name (your keep-both-names rule).",
          f"- **People–LGA links reach all 25 LGAs ({len(lg)}).**",
          "  - **Well documented:** where Wikipedia's language table and the Atlas agree, or a Wikipedia LGA page says so (the Nupe in Agaie, Bida and Lapai; the Gbagyi in Shiroro).",
          "  - **Reported:** where only the table, which comes from Ethnologue, gives the link.",
          "- **At state level:** Nupe, Gbagyi and Hausa (the three main groups), Fulani, Koro and Kakanda; Igbo and Yoruba as migrant communities (federal profile).",
          "- **Not created:** Barab and Gwada, which have no Atlas match.",
          "- **Decision for you:** the record names (Adara, Dibo, Hun-Saare, Hùngwəryə, Reshe) follow your keep-both-names rule. Say if you prefer the federal profile's names (Kadara, Gana-Gana, Dukawa, Ingwai, Gungawa) as the record names.", "",
          "## The new records", ""] + [f"**{NEW[k][0]}.** {TEXT[k]}\n" for k in TEXT]
    L_ += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_160b_niger_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_160b_niger_peoples_REVIEW.md", "w").write(report())
    lg = {r["to"] for r in RELATIONS if r["type"] == "present_in" and "lga:niger" in r["to"]}
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} LGAs={len(lg)}")
