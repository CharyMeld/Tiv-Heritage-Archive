"""
Research batch 129 — Osun (Phase 3, first batch): languages and peoples. Researched 2026-10-02. Pattern: batch 123 (Oyo).

Languages: the column-split Atlas text was searched for 'Osun' and Osun town and LGA names; only Yoruba No. 489 names
Osun ('Most of … Osun … States'; Oshun in its North West group; Ife and Ijesha in its Central group). Yoruba is already
linked to Osun (relation 2406, batch 051). No new links.
Peoples:
  * Federal Government of Nigeria, state profile 'Osun' (data/fg_osun_2026-10-02.html), 'Ethnic Profile': 'The people are
    mainly Yoruba; composed of Osun, Ifes, Ijesas and Igbominas. Language is Yoruba but there are variations in
    intonation and accent across the towns and cities.'
  * Wikipedia, 'Osun State': 'primarily inhabited by the Yoruba people, mainly of the Ibolo, Ife, Igbomina, Ijesha, and
    Oyo subgroups'.
  * The groups by LGA, where a source names them:
      Ijesha — Wikipedia 'Ijesha': 'six local government councils within Osun state' (infobox: Ilesha West, Ilesha East,
        Atakunmosa East, Atakunmosa West, Oriade, Obokun); Ilesa the cultural capital.
      Ife — Wikipedia 'Ifẹ': the city is divided into Ife East and Ife Central.
      Igbomina — Wikipedia 'Igbomina': Igbominaland includes two Osun LGAs, Ifedayo and Ila.
    The other 20 LGAs are linked to the Yoruba without a group (reported): no source read assigns the Ibolo (the
    federal profile's 'Osun'), the Oyo, or the rest of the Ife to particular LGAs.
"""
import json, sys
import batch_087_kano_languages as L87

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=f"{n} Accessed {ACCESSED}.")
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Osun entries read: Yoruba only (No. 489: 'Most of … Osun … States'; Oshun in the North West group; Ife and Ijesha in the Central group)."),
    "FGOS": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Osun State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/osun/", verification_status="verified",
                 notes="'Ethnic Profile': 'The people are mainly Yoruba; composed of Osun, Ifes, Ijesas and Igbominas.' Capital Osogbo; created 27 August 1991. Read 2026-10-02 (copy in database/research/data/)."),
    "WOSS": WS("Osun State", "'primarily inhabited by the Yoruba people, mainly of the Ibolo, Ife, Igbomina, Ijesha, and Oyo subgroups'; list of the 30 LGAs with headquarters."),
    "WIJE": WS("Ijesha", "A major Yoruba sub-group; Ilesa the cultural capital; the Ijesa area covers six LGAs of Osun State (Ilesha West, Ilesha East, Atakunmosa East, Atakunmosa West, Oriade, Obokun)."),
    "WIFE": WS("Ifẹ", "Ancient Yoruba city in Osun State; the city is divided into Ife East (headquarters Oke-Ogbo) and Ife Central."),
    "WIGB": WS("Igbomina", "Yoruba sub-group of southern Kwara and northern Osun; Igbominaland includes Ifedayo and Ila LGAs of Osun State; Ila Orangun and Oke-Ila the major Osun towns."),
}
GROUPS = {
    "Ijesha": (["ilesha-west", "ilesha-east", "atakunmosa-east", "atakunmosa-west", "oriade", "obokun"], "WIJE", "Wikipedia (Ijesha) names this LGA among the six of the Ijesa cultural area"),
    "Ife": (["ife-central", "ife-east"], "WIFE", "Wikipedia (Ifẹ): one of the two LGAs of the city of Ife"),
    "Igbomina": (["ila", "ifedayo"], "WIGB", "Wikipedia (Igbomina): one of the two Osun LGAs of Igbominaland"),
}
ALL = ["aiyedade", "aiyedire", "atakunmosa-east", "atakunmosa-west", "boluwaduro", "boripe", "ede-north", "ede-south", "egbedore", "ejigbo", "ife-central", "ife-east",
       "ife-north", "ife-south", "ifedayo", "ifelodun", "ila", "ilesha-east", "ilesha-west", "irepodun", "irewole", "isokan", "iwo", "obokun", "odo-otin", "ola-oluwa",
       "olorunda", "oriade", "orolu", "osogbo"]
GROUPED = {l for ls, _s, _w in GROUPS.values() for l in ls}
PO = " Presence only; the nature of their presence is not established."
RELATIONS = [
    dict(frm="@ethnic_groups:yoruba", type="present_in", to="@admin_units:state:osun", source="FGOS", evidence="multiple_sources", level="well_documented",
         notes="Federal profile: 'mainly Yoruba; composed of Osun, Ifes, Ijesas and Igbominas'; Wikipedia: mainly the Ibolo, Ife, Igbomina, Ijesha and Oyo sub-groups."),
]
for g, (lgas, src, why) in GROUPS.items():
    for l in lgas:
        RELATIONS.append(dict(frm="@ethnic_groups:yoruba", type="present_in", to=f"@admin_units:lga:osun/{l}", source=src, evidence="multiple_sources", level="well_documented",
                              settlement_status="unknown", notes=f"The {g}, a Yoruba sub-group named in the federal profile and Wikipedia. {why}.{PO}"))
for l in ALL:
    if l not in GROUPED:
        RELATIONS.append(dict(frm="@ethnic_groups:yoruba", type="present_in", to=f"@admin_units:lga:osun/{l}", source="FGOS", evidence="single_reliable_source", level="reported",
                              settlement_status="unknown", notes=f"Federal profile: the people of Osun State are 'mainly Yoruba'. No source read names this LGA's sub-group (Osun/Ibolo, Oyo or Ife).{PO}"))
GAPS = [
    ("Osun: sub-groups of 20 LGAs", "No source read assigns the Ibolo (the federal profile's 'Osun'), the Oyo or the rest of the Ife to particular LGAs; 20 LGAs are linked to the Yoruba without a group."),
    ("Osun: other peoples", "Wikipedia notes 'people from other parts of Nigeria' without naming them; none is linked."),
    ("Osun: Yoruba sub-groups", "The Ijesha, Ife, Igbomina, Ibolo and Oyo are recorded in the Yoruba link notes, as in the other south-western states."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Osun (Phase 3): Yoruba linked to all 30 LGAs; the Ijesha (6 LGAs), Ife (2) and Igbomina (2) named in the notes.")


def report():
    lg = [r for r in RELATIONS if "lga:" in r["to"]]
    L = ["# Research batch 129 — Osun: languages and peoples", "",
         f"Researched {ACCESSED}. Phase 3, the first Osun batch. Created in review; published only after your approval.", "",
         "## Summary", "",
         "- **Languages:** the Atlas places only Yoruba in Osun, and Yoruba is already linked. **No new language links.**",
         "- **Peoples:** no new records. The federal profile: 'mainly Yoruba; composed of Osun, Ifes, Ijesas and Igbominas'. Wikipedia adds the Ibolo and Oyo.",
         f"- **{len(lg)} Yoruba–LGA links covering all 30 LGAs:**",
         "  - **Ijesha:** Ilesha East and West, Atakunmosa East and West, Oriade, Obokun (*well documented*)",
         "  - **Ife:** Ife Central and Ife East, the city of Ife (*well documented*)",
         "  - **Igbomina:** Ila and Ifedayo (*well documented*)",
         "  - **The other 20 LGAs:** Yoruba without a named sub-group (*reported*). No source read places the Ibolo/Osun or Oyo groups by LGA.", "",
         "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_129_osun_languages_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_129_osun_languages_peoples_REVIEW.md", "w").write(report())
    lg = [r for r in RELATIONS if "lga:" in r["to"]]
    print(f"relations={len(RELATIONS)} people-LGA={len(lg)} LGAs={len({r['to'] for r in lg})}")
