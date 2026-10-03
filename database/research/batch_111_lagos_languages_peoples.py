"""
Research batch 111 — Lagos (Phase 3, first batch): languages and peoples. Researched 2026-10-02.
Pattern: batches 099 (Katsina) and 105 (Zamfara), with new records as in 081/081b.

Languages: the column-split Atlas text was searched for 'Lagos' and the 20 Lagos LGA names. Two entries:
  * Yoruba No. 489 — 'Most of Kwara, Lagos, Osun, Oyo, Ogun and Ondo States'; dialects include Awori and Ijebu.
    Yoruba is already linked to Lagos (relation #2405) — no new link.
  * Gbe cluster No. 148 — 'Lagos State, Badagry LGA; and mainly in the Republics of Benin and Togo'; Volta–Congo: Kwa:
    Left Bank; sub-entries Aja, Alada, Asento, Gbekon and Gun ('Gũ, Egun'; 300,000, Atinwore 1986).
    → new records: Kwa (branch; Glottolog kwav1236, 'Kwa Volta-Congo') and Gun (gunn1250, Glottolog: Kwa > Gbe >
    Eastern Gbe > Fongbeic). Wikipedia (Gun language): spoken by the Ogu people, in Nigeria 'particularly Badagry,
    Maun, Tube'; own name gungbe.
Peoples:
  * Federal Government of Nigeria, state profile 'Lagos' (data/fg_lagos_2026-10-02.html), 'Ethnic Profile': 'essentially
    Yoruba-speaking … Indigenous inhabitants include the Aworis and Eguns in Ikeja and Badagry Divisions respectively,
    with the Eguns being found mainly in Badagry … other pioneer settlers collectively known as the Ekos. The indigenes
    of Ikorodu and Epe Divisions are mainly the Ijebus with pockets of Eko-Awori settlers along the coastland'.
  * Wikipedia, 'Lagos State': the same passage ('Awori and Ogu a.k.a. Egun'); Alimosho and Ifako-Ijaiye 'predominantly
    populated by the Egba and Egbado Yoruba'; Ojo 'predominantly inhabited by the Awori'; Eko, the island, first
    settled by the Awori; migrant Edo, Efik, Fulani, Hausa, Igbo, Ijaw, Ibibio and Nupe; Ewe in the far west.
    Its LGA table gives the five divisions: Ikeja (Agege, Alimosho, Ifako-Ijaye, Ikeja, Kosofe, Mushin, Oshodi-Isolo,
    Shomolu), Lagos (Apapa, Eti-Osa, Lagos Island, Lagos Mainland, Surulere), Badagry (Ajeromi-Ifelodun, Amuwo-Odofin,
    Ojo, Badagry), Ikorodu (Ikorodu), Epe (Ibeju-Lekki, Epe).
  * Wikipedia, 'Gun people' (Ogu, Ogun or Egun; Badagry; about 15% of Lagos's indigenous population) and 'Awori
    people' (a Yoruba subgroup in Lagos and Ogun). 'Makoko': Egun on the mainland Lagos waterfront — no LGA given.
New record: the Ogu people. Yoruba sub-groups (Awori, Eko, Ijebu, Egba, Egbado) go in the link notes, as the Hausa
sub-groups did for Zamfara.
"""
import json, sys
import batch_087_kano_languages as L87
import batch_063_borno_languages as B63

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=f"{n} Accessed {ACCESSED}.")
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Lagos entries read: Gbe cluster (No. 148: 'Lagos State, Badagry LGA'; Gun 'Gũ, Egun'), Yoruba (No. 489; dialects Awori, Ijebu)."),
    "GLIDX": L87.SOURCES["GLIDX"],
    "FGLA": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Lagos State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/lagos/", verification_status="verified",
                 notes="'Ethnic Profile': 'essentially Yoruba-speaking … Indigenous inhabitants include the Aworis and Eguns in Ikeja and Badagry Divisions respectively, with the Eguns being found mainly in Badagry … pioneer settlers collectively known as the Ekos. The indigenes of Ikorodu and Epe Divisions are mainly the Ijebus with pockets of Eko-Awori settlers along the coastland and riverine areas.' Read 2026-10-02 (copy in database/research/data/)."),
    "WLAG": WS("Lagos State", "Demographics (Awori, Ogu a.k.a. Egun, Eko, Ijebu; Egba and Egbado in Alimosho and Ifako-Ijaiye; migrant groups); Ojo 'predominantly inhabited by the Awori'; LGA table with the five divisions."),
    "WGUNP": WS("Gun people", "'also rendered Ogu, Ogun or Egun'; Badagry, Yewa, Ipokia, Makoko; about 15% of the indigenous population of Lagos State."),
    "WGUNL": WS("Gun language", "Gbe language 'spoken by the Ogu people'; in Nigeria 'particularly Badagry, Maun, Tube'; own name gungbe."),
    "WAWO": WS("Awori people", "'a subgroup of the Yoruba people'; homeland in Lagos and Ogun states."),
    "WMAK": WS("Makoko", "Waterfront 'largely harboured by the Egun people who migrated from Badagary and Republic of Benin'."),
}
S, L, X = "spelling_variant", "alternative", "exonym"
FIELD = dict(B63.FIELD)
TEXT = {
 "kwa": """Kwa is a branch of the Volta–Congo languages of Niger–Congo, spoken mainly in Ghana, Togo, Benin and Côte d'Ivoire. In Nigeria it is represented by the Gbe languages of the Badagry area of Lagos State, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies the Gbe cluster as 'Volta–Congo: Kwa: Left Bank'. Glottolog lists the family as Kwa Volta-Congo (kwav1236), containing Gbe.""",
 "gun": """Gun (Gungbe), also written Egun, is a Gbe language spoken by the Ogu people in the Badagry area of Lagos State and, mainly, in the Republic of Benin. Roger Blench's Atlas of Nigerian Languages (2020) places the Gbe cluster in 'Lagos State, Badagry LGA', classifies it as Kwa, and gives 300,000 speakers of Gun (1986). Wikipedia names Badagry, Maun and Tube as Nigerian centres, and Glottolog places Gun in the Fongbeic group of Eastern Gbe. The New Testament was published in 1892 and the Bible in 1923.""",
 "ogu": """The Ogu, also called the Gun or Egun, are a Gbe-speaking people of the Badagry area of Lagos State, and of the Republic of Benin, where most of them live. The federal government's state profile names the Eguns among the indigenous inhabitants of Lagos, found mainly in Badagry, and Wikipedia gives them about 15% of Lagos State's indigenous population. Wikipedia traces their origin to the old kingdom of Allada, with migration to Badagry as early as the 15th century. Many live by fishing, coconut processing and salt production, and the Zangbeto night-watch spirit is part of their tradition. They also live in the Makoko waterfront community of mainland Lagos.""",
}
RECORDS = [
    dict(key="kwa", table="languages", evidence="multiple_sources", level="well_documented",
         fields=dict(lang_type="branch", name="Kwa", slug="kwa", glottocode="kwav1236", summary=TEXT["kwa"].split(". ")[0] + ".", description=TEXT["kwa"]),
         srcs=[("ATLAS", "Gbe cluster classified 'Volta–Congo: Kwa: Left Bank'"), ("GLIDX", "Kwa Volta-Congo (kwav1236), containing Gbe")]),
    dict(key="gun", table="languages", evidence="multiple_sources", level="well_documented",
         fields=dict(lang_type="language", name="Gun", slug="gun", parent_id="@key:kwa", glottocode="gunn1250", summary=TEXT["gun"].split(". ")[0] + ".", description=TEXT["gun"]),
         srcs=[("ATLAS", "Gun: location, classification, other names, speakers, scripture"), ("GLIDX", "Glottocode gunn1250"), ("WGUNL", "Gun: speakers, Nigerian centres, own name")]),
    dict(key="ogu", table="ethnic_groups", evidence="multiple_sources", level="well_documented",
         fields=dict(name="Ogu", slug="ogu", summary=TEXT["ogu"].split(". ")[0] + ".", description=TEXT["ogu"]),
         srcs=[("FGLA", "Eguns: indigenous inhabitants, mainly in Badagry"), ("WLAG", "Ogu a.k.a. Egun in the Badagry Division"), ("WGUNP", "Gun people: names, origin, share of Lagos population"),
               ("WMAK", "Egun at Makoko")]),
]
NAMES = [
    dict(record="gun", name="Egun", name_type=S, usage_notes=f"Blench's Atlas (2020), field 1.A: {FIELD['1.A']}.", srcs=["ATLAS"]),
    dict(record="gun", name="Gũ", name_type=S, usage_notes=f"Blench's Atlas (2020), field 1.A: {FIELD['1.A']}.", srcs=["ATLAS"]),
    dict(record="gun", name="Gungbe", name_type="endonym", usage_notes="Wikipedia (Gun language): 'Gun (Gun: gungbe)'.", srcs=["WGUNL"]),
    dict(record="ogu", name="Gun", name_type=L, usage_notes="Wikipedia's article title ('Gun people'; 'Goun' in French).", srcs=["WGUNP"]),
    dict(record="ogu", name="Egun", name_type=L, usage_notes="The name used by the federal government's state profile ('Eguns') and by Wikipedia ('Ogu a.k.a. Egun').", srcs=["FGLA"]),
    dict(record="ogu", name="Ogun", name_type=S, usage_notes="Wikipedia (Gun people): 'also rendered Ogu, Ogun or Egun'.", srcs=["WGUNP"]),
]
DIV = {"ikeja": ["agege", "alimosho", "ifako-ijaye", "ikeja", "kosofe", "mushin", "oshodi-isolo", "shomolu"],
       "ikorodu": ["ikorodu"], "epe": ["ibeju-lekki", "epe"]}
LGAN = {"agege": "Agege", "alimosho": "Alimosho", "ifako-ijaye": "Ifako-Ijaye", "ikeja": "Ikeja", "kosofe": "Kosofe", "mushin": "Mushin", "oshodi-isolo": "Oshodi-Isolo",
        "shomolu": "Shomolu", "ikorodu": "Ikorodu", "ibeju-lekki": "Ibeju-Lekki", "epe": "Epe", "ojo": "Ojo", "lagos-island": "Lagos Island", "badagry": "Badagry"}
PO = " Presence only; the nature of their presence is not established."
RELATIONS = [
    dict(frm="gun", type="spoken_in", to="@admin_units:state:lagos", source="ATLAS", evidence="multiple_sources", level="well_documented",
         notes="Blench's Atlas: Gbe cluster, 'Lagos State, Badagry LGA'; Wikipedia: Badagry, Maun, Tube."),
    dict(frm="gun", type="spoken_in", to="@admin_units:lga:lagos/badagry", source="ATLAS", evidence="multiple_sources", level="well_documented",
         notes="Blench's Atlas: 'Lagos State, Badagry LGA'; Wikipedia (Gun language): 'particularly Badagry, Maun, Tube'."),
    dict(frm="ogu", type="speaks", to="gun", source="WGUNL", evidence="multiple_sources", level="well_documented",
         notes="Wikipedia: Gun 'is spoken by the Ogu people'; the Atlas gives Egun as a name of Gun."),
    dict(frm="ogu", type="present_in", to="@admin_units:state:lagos", source="FGLA", evidence="multiple_sources", level="well_documented",
         notes="Federal profile: 'Indigenous inhabitants include … Eguns … found mainly in Badagry'; Wikipedia: about 15% of the indigenous population of Lagos State."),
    dict(frm="ogu", type="present_in", to="@admin_units:lga:lagos/badagry", source="FGLA", evidence="multiple_sources", level="well_documented", settlement_status="unknown",
         notes="Federal profile: the Eguns are 'found mainly in Badagry'; Wikipedia (Lagos State; Gun people) agrees. The federal profile calls them indigenous inhabitants."),
    dict(frm="@ethnic_groups:yoruba", type="present_in", to="@admin_units:state:lagos", source="FGLA", evidence="multiple_sources", level="well_documented",
         notes="Federal profile: 'The State is essentially Yoruba-speaking'; Wikipedia: 'primarily the majority Yoruba people who live throughout the state'. Indigenous sub-groups: Awori, Eko, Ijebu."),
]
for l in DIV["ikeja"]:
    extra = " Wikipedia adds that Alimosho and Ifako-Ijaiye are 'predominantly populated by the Egba and Egbado Yoruba people'." if l in ("alimosho", "ifako-ijaye") else ""
    RELATIONS.append(dict(frm="@ethnic_groups:yoruba", type="present_in", to=f"@admin_units:lga:lagos/{l}", source="FGLA", evidence="multiple_sources", level="well_documented",
                          settlement_status="unknown", notes=f"The federal profile and Wikipedia name the Awori, a Yoruba sub-group, as indigenous inhabitants of the Ikeja Division, which includes {LGAN[l]} (Wikipedia's LGA table).{extra}{PO}"))
for d in ("ikorodu", "epe"):
    for l in DIV[d]:
        RELATIONS.append(dict(frm="@ethnic_groups:yoruba", type="present_in", to=f"@admin_units:lga:lagos/{l}", source="FGLA", evidence="multiple_sources", level="well_documented",
                              settlement_status="unknown", notes=f"The federal profile and Wikipedia: 'The indigenes of Ikorodu and Epe Divisions are mainly the Ijebus', a Yoruba sub-group, 'with pockets of Eko-Awori settlers along the coastland'; {LGAN[l]} is in the {d.capitalize()} Division (Wikipedia's LGA table).{PO}"))
RELATIONS += [
    dict(frm="@ethnic_groups:yoruba", type="present_in", to="@admin_units:lga:lagos/ojo", source="WLAG", evidence="single_reliable_source", level="reported", settlement_status="unknown",
         notes=f"Wikipedia (Lagos State): Ojo 'is a town predominantly inhabited by the Awori people', a Yoruba sub-group.{PO}"),
    dict(frm="@ethnic_groups:yoruba", type="present_in", to="@admin_units:lga:lagos/lagos-island", source="WLAG", evidence="single_reliable_source", level="reported", settlement_status="unknown",
         notes=f"Wikipedia (Lagos State): Eko, the island, was first settled by the Awori, a Yoruba sub-group.{PO}"),
]
MIGRANT = ["hausa", "igbo", "fulani", "nupe"]
for p in MIGRANT:
    RELATIONS.append(dict(frm=f"@ethnic_groups:{p}", type="present_in", to="@admin_units:state:lagos", source="WLAG", evidence="single_reliable_source", level="reported",
                          settlement_status="migrant_community", notes="Wikipedia (Lagos State): 'As a result of migration since the nineteenth century, Lagos State also has large populations of non-native Nigerian ethnic groups' — Edo, Efik, Fulani, Hausa, Igbo, Ijaw, Ibibio and Nupe."))
GAPS = [
    ("Lagos: peoples of six LGAs", "No source read names the peoples of Apapa, Eti-Osa, Surulere, Lagos Mainland, Ajeromi-Ifelodun or Amuwo-Odofin. The federal profile places the Eguns in the Badagry Division but 'mainly in Badagry', so only Badagry LGA is linked; Makoko (Egun) is on mainland Lagos, but its LGA is not given."),
    ("Lagos: Yoruba sub-groups", "The Awori, Eko, Ijebu, Egba and Egbado are recorded in the Yoruba link notes, not as separate peoples. Whether Yoruba sub-groups get their own records is a decision for the whole south-west."),
    ("Lagos: Ewe", "Wikipedia (Lagos State) twice names the Ewe with the Ogu in Badagry and the far west; no other source read does. Not recorded."),
    ("Lagos: migrant groups without records", "Wikipedia names Edo, Efik, Ijaw and Ibibio migrants, and the Saro and Amaro returnee communities; none has a record yet."),
    ("Lagos: other Gbe varieties", "The Atlas's Gbe cluster also lists Aja, Alada, Asento and Gbekon; only Gun is recorded."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Lagos (Phase 3): Kwa and Gun (Gbe) languages and the Ogu people; Yoruba linked to 13 LGAs with sub-groups in the notes; Hausa, Igbo, Fulani and Nupe as migrant communities.")


def report():
    lg = [r for r in RELATIONS if "lga:" in r["to"] and r["type"] == "present_in"]
    L_ = ["# Research batch 111 — Lagos: languages and peoples", "",
          f"Researched {ACCESSED}. Phase 3, the first Lagos batch. Created in review; published only after your approval.", "",
          "## Summary", "",
          "- **Languages:** the Atlas places two languages in Lagos. **Yoruba** is already linked. The new one is **Gun (Egun)**, a Gbe language of Badagry LGA.",
          "  - 2 new records: the **Kwa** branch (`kwav1236`) and **Gun** (`gunn1250`).",
          "- **Peoples:** one new record, the **Ogu** (also Gun or Egun), the Gbe-speaking indigenous people of Badagry.",
          "  - The record is named **Ogu**, the name Wikipedia uses for the people in Nigeria. **Gun**, **Egun** and **Ogun** are kept as other names, with notes on who uses them.",
          f"- **{len(lg)} people–LGA links covering {len({r['to'] for r in lg})} of 20 LGAs.** The federal profile and Wikipedia place the Yoruba sub-groups by division, and Wikipedia's table maps divisions to LGAs:",
          "  - **Awori** in the Ikeja Division (8 LGAs): *well documented*",
          "  - **Ijebu** in the Ikorodu and Epe Divisions (Ikorodu, Epe, Ibeju-Lekki): *well documented*",
          "  - **Awori** in Ojo and Lagos Island: *reported*, from Wikipedia alone",
          "  - **Egba and Egbado** in Alimosho and Ifako-Ijaye: in the notes",
          "  - **Ogu** in Badagry: *well documented*",
          "- **Migrant communities:** Hausa, Igbo, Fulani and Nupe are linked to the state as *migrant communities*, *reported*.",
          "- **Decision for you (no action needed now):** Yoruba sub-groups (Awori, Eko, Ijebu, Egba, Egbado) are kept in the link notes, as the Hausa sub-groups were for Zamfara. Giving them their own records would affect every south-western state.", "",
          "## The new records", ""] + [f"**{k.capitalize()}.** {TEXT[k]}\n" for k in TEXT]
    L_ += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_111_lagos_languages_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_111_lagos_languages_peoples_REVIEW.md", "w").write(report())
    lg = [r for r in RELATIONS if "lga:" in r["to"] and r["type"] == "present_in"]
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} people-LGA={len(lg)} LGAs={len({r['to'] for r in lg})}")
