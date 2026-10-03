"""
Research batch 123 — Oyo (Phase 3, first batch): languages and peoples. Researched 2026-10-02. Pattern: batch 117 (Ogun).

Languages: the column-split Atlas text was searched for 'Oyo' and the Oyo LGA and town names (Saki, Kishi, Igboho,
Igbeti, Iseyin, Ogbomoso, Ibadan, Ibarapa, Okeho…). Only Yoruba No. 489 names Oyo ('Most of … Oyo … States'; Oyo is
in its North West group with Egba and Oshun); Yoruba is already linked to Oyo (relation 2407, batch 051). No new links.
Peoples:
  * Federal Government of Nigeria, state profile 'Oyo' (data/fg_oyo_2026-10-02.html), 'Ethnic Profile': 'The State which
    is homogeneous … predominantly occupied by Yoruba people. Within the State however, there are sub-ethnic groups with
    distinct dialect peculiarities … five broad groups which are: Ibadans, Ibarapas, Oyos, Oke-Oguns and Ogbomosos'.
  * The groups by LGA:
      Ibadan — Wikipedia 'Ibadan': 11 LGAs in the metropolitan area (urban: Ibadan North, North-East, North-West,
        South-East, South-West; semi-urban: Akinyele, Egbeda, Ido, Lagelu, Ona Ara, Oluyole).
      Ibarapa — Wikipedia 'Ibarapa people': a Yoruba subgroup in the south-west of Oyo State; Ibarapa North, Central and
        East (created 1996).
      Oyo — Wikipedia 'Oyo, Oyo State': the city has four LGAs, Atiba, Oyo East, Oyo West and Afijio.
      Oke-Ogun — Wikipedia 'Oyo State': its list of LGA headquarters names ten as '-Okeogun' (Atisbo, Irepo, Iseyin,
        Itesiwaju, Iwajowa, Kajola, Olorunsogo, Orelope, Saki East, Saki West); the Ibarapa article calls the people of
        Iwajowa, Kajola and Iseyin 'Yorubas of Onko extraction'.
      Ogbomoso — Wikipedia 'Ogbomosho': the city has five LGAs, but the article does not name them; Ogbomosho North
        (headquarters Ogbomoso) and Ogbomosho South are linked; Surulere, Ogo Oluwa and Ori Ire are linked to the Yoruba
        without a group (reported) — a gap.
No new records. Every Oyo LGA gets a Yoruba link with its group in the notes.
"""
import json, sys
import batch_087_kano_languages as L87

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=f"{n} Accessed {ACCESSED}.")
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Oyo entries read: Yoruba only (No. 489: 'Most of … Oyo … States'; Oyo in the North West group)."),
    "FGOY": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Oyo State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/oyo/", verification_status="verified",
                 notes="'Ethnic Profile': 'The State which is homogeneous … predominantly occupied by Yoruba people … sub-ethnic groups with distinct dialect peculiarities … five broad groups which are: Ibadans, Ibarapas, Oyos, Oke-Oguns and Ogbomosos'. Read 2026-10-02 (copy in database/research/data/)."),
    "WOYS": WS("Oyo State", "List of the 33 LGAs with their headquarters; ten given as '-Okeogun' (Atisbo, Irepo, Iseyin, Itesiwaju, Iwajowa, Kajola, Olorunsogo, Orelope, Saki East, Saki West)."),
    "WIBD": WS("Ibadan", "11 LGAs in the Ibadan metropolitan area: Ibadan North, North-East, North-West, South-East, South-West (urban); Akinyele, Egbeda, Ido, Lagelu, Ona Ara, Oluyole (semi-urban)."),
    "WIBP": WS("Ibarapa people", "A Yoruba subgroup in the south-west of Oyo State; Ibarapa North, Central and East LGAs (created 1996); bordered by 'Yorubas of Onko extraction' in Iwajowa, Kajola and Iseyin."),
    "WOYC": WS("Oyo, Oyo State", "The city has four LGAs: Atiba (Offa-Meta), Oyo East (Kosobo), Oyo West (Ojongbodu) and Afijio (Jobele)."),
    "WOGB": WS("Ogbomosho", "City founded in the mid 17th century; 'the principal inhabitants of the city are the Yoruba people'; the city has 5 LGAs (not named in the article)."),
}
GROUPS = {
    "Ibadan": (["ibadan-north", "ibadan-north-east", "ibadan-north-west", "ibadan-south-east", "ibadan-south-west", "akinyele", "egbeda", "ido", "lagelu", "ona-ara", "oluyole"],
               "WIBD", "Wikipedia (Ibadan) counts this LGA among the 11 of the Ibadan metropolitan area"),
    "Ibarapa": (["ibarapa-central", "ibarapa-east", "ibarapa-north"], "WIBP", "Wikipedia (Ibarapa people): one of the three Ibarapa LGAs, home of the Ibarapa, a Yoruba subgroup"),
    "Oyo": (["afijio", "atiba", "oyo-east", "oyo-west"], "WOYC", "Wikipedia (Oyo, Oyo State) counts this LGA among the four of the city of Oyo"),
    "Oke-Ogun": (["atisbo", "irepo", "iseyin", "itesiwaju", "iwajowa", "kajola", "olorunsogo", "orelope", "saki-east", "saki-west"], "WOYS",
                 "Wikipedia's list of Oyo LGA headquarters places this LGA's headquarters in Oke-Ogun"),
    "Ogbomoso": (["ogbomosho-north", "ogbomosho-south"], "WOGB", "Ogbomoso city, whose principal inhabitants are the Yoruba (Wikipedia, Ogbomosho)"),
}
ONKO = {"iwajowa", "kajola", "iseyin"}
UNGROUPED = ["surulere", "ogo-oluwa", "ori-ire"]
PO = " Presence only; the nature of their presence is not established."
RELATIONS = [
    dict(frm="@ethnic_groups:yoruba", type="present_in", to="@admin_units:state:oyo", source="FGOY", evidence="multiple_sources", level="well_documented",
         notes="Federal profile: 'homogeneous … predominantly occupied by Yoruba people', in five broad groups — Ibadans, Ibarapas, Oyos, Oke-Oguns and Ogbomosos; Blench's Atlas: Yoruba spoken in most of Oyo State."),
]
for g, (lgas, src, why) in GROUPS.items():
    for l in lgas:
        extra = " The Ibarapa article calls the people here 'Yorubas of Onko extraction'." if l in ONKO else ""
        RELATIONS.append(dict(frm="@ethnic_groups:yoruba", type="present_in", to=f"@admin_units:lga:oyo/{l}", source=src, evidence="multiple_sources", level="well_documented",
                              settlement_status="unknown", notes=f"The {g} group, one of the federal profile's five broad groups of Oyo's Yoruba. {why}.{extra}{PO}"))
for l in UNGROUPED:
    RELATIONS.append(dict(frm="@ethnic_groups:yoruba", type="present_in", to=f"@admin_units:lga:oyo/{l}", source="FGOY", evidence="single_reliable_source", level="reported",
                          settlement_status="unknown", notes=f"Federal profile: the state is 'homogeneous' and 'predominantly occupied by Yoruba people'. No source read names this LGA's group (possibly the Ogbomoso group).{PO}"))
GAPS = [
    ("Oyo: Surulere, Ogo Oluwa and Ori Ire", "Wikipedia says Ogbomoso has five LGAs but does not name them; these three are linked to the Yoruba without a group."),
    ("Oyo: other peoples", "Neither the federal profile nor Wikipedia names non-Yoruba peoples in Oyo. Wikipedia lists 15 nomadic schools (in Irepo, Itesiwaju, Iwajowa, Ibarapa North and other LGAs) without naming the pastoralists they serve; the Fulani are not linked."),
    ("Oyo: Yoruba sub-groups", "The Ibadan, Ibarapa, Oyo, Oke-Ogun (Onko) and Ogbomoso groups are recorded in the Yoruba link notes, as in Lagos and Ogun."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Oyo (Phase 3): Yoruba linked to all 33 LGAs, with the federal profile's five groups (Ibadan, Ibarapa, Oyo, Oke-Ogun, Ogbomoso) in the notes.")


def report():
    lg = [r for r in RELATIONS if "lga:" in r["to"]]
    L = ["# Research batch 123 — Oyo: languages and peoples", "",
         f"Researched {ACCESSED}. Phase 3, the first Oyo batch. Created in review; published only after your approval.", "",
         "## Summary", "",
         "- **Languages:** the Atlas places only Yoruba in Oyo, and Yoruba is already linked. **No new language links.**",
         "- **Peoples:** no new records. The federal profile calls Oyo 'homogeneous' and predominantly Yoruba, in **five broad groups: the Ibadan, Ibarapa, Oyo, Oke-Ogun and Ogbomoso**.",
         f"- **{len(lg)} Yoruba–LGA links covering all 33 LGAs.** Each note names the LGA's group:",
         "  - **Ibadan:** the 11 metropolitan LGAs",
         "  - **Ibarapa:** 3 LGAs",
         "  - **Oyo:** the 4 LGAs of Oyo city",
         "  - **Oke-Ogun:** 10 LGAs, with Iseyin, Iwajowa and Kajola noted as Onko",
         "  - **Ogbomoso:** Ogbomosho North and South",
         "  - All of the above are *well documented*.",
         "- **Surulere, Ogo Oluwa and Ori Ire** are linked to the Yoruba without a group (*reported*). Wikipedia says Ogbomoso has five LGAs but doesn't name them.",
         "- **The Fulani are not linked.** Wikipedia lists nomadic schools but doesn't name the pastoralists they serve.", "",
         "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_123_oyo_languages_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_123_oyo_languages_peoples_REVIEW.md", "w").write(report())
    lg = [r for r in RELATIONS if "lga:" in r["to"]]
    print(f"relations={len(RELATIONS)} people-LGA={len(lg)} LGAs={len({r['to'] for r in lg})}")
