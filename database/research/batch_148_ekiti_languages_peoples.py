"""
Research batch 148 — Ekiti (Phase 3, first batch): languages and peoples. Researched 2026-10-07. Pattern: batch 129 (Osun).

Languages: the column-split Atlas text was searched for 'Ekiti' and the Ekiti LGA and town names. Ekiti State was created
in 1996 from Ondo State, after the Atlas's LGA data, so the Atlas names no Ekiti State. Two entries concern it:
  * Yoruba No. 489 — 'Ekiti' among its dialects; 'Central, including Ìfè ̣, Ìjèshà, Èkìtì'. Yoruba is not yet linked to
    Ekiti State → linked (Atlas + Wikipedia: the Ekiti 'speak a dialect of Yoruba language known as Ekiti').
  * Ahan No. 8 — 'Ondo State, Ekiti LGA, Ajowa, Igashi, and Omou towns'; linked to Ekiti State (reported) in batch 142.
    Wikipedia: Omuo-Ekiti is the headquarters of Ekiti East, and Ahan is one of its communities; 'Ahan language': spoken at
    Ahan-Ayegunle in the Omuo Oke district (Ethnologue: Ekiti East LGA) → Ahan linked to Ekiti East.
Peoples:
  * Federal Government of Nigeria, state profile 'Ekiti' (data/fg_ekiti_2026-10-07.html), 'Ethnic Profile': 'Ekiti dialet
    [sic] is common to the people of the State.'
  * Wikipedia, 'Ekiti State': 'primarily inhabited for centuries by the Ekiti people, a Yoruba subgroup, with minorities
    of the Akoko Yoruba subgroup, and Yagba–Ekiti Yoruba subgroup'; Otun (Moba) speaks a dialect close to Igbomina;
    Oke-Ako, Irele and Omuo speak a dialect similar to Yagba-Ekiti.
  * Wikipedia, 'Ekiti people': a Central Yoruba group; 'Ekiti State is populated exclusively by Ekiti people'.
  → Yoruba linked to the state and all 16 LGAs, the Ekiti sub-group in the notes; Moba and Ekiti East get the dialect notes.
"""
import json, sys
import batch_087_kano_languages as L87
import batch_142_ondo_languages_peoples as P142

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=f"{n} Accessed {ACCESSED}.")
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Ekiti entries read: Yoruba (No. 489: dialect 'Ekiti'; Central group 'Ìfè ̣, Ìjèshà, Èkìtì'), Ahan (No. 8: 'Ondo State, Ekiti LGA, … Omou')."),
    "FGEK": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Ekiti State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/ekiti/", verification_status="verified",
                 notes="'Ethnic Profile': 'Ekiti dialet is common to the people of the State.' Capital Ado-Ekiti; created 1 October 1996; land area 5,887.89 km². Read 2026-10-07 (copy in database/research/data/)."),
    "WEKS": WS("Ekiti State", "'primarily inhabited for centuries by the Ekiti people, a Yoruba subgroup, with minorities of the Akoko Yoruba subgroup, and Yagba–Ekiti Yoruba subgroup'; the Ekiti 'speak a dialect of Yoruba language known as Ekiti'; Otun (Moba) speaks a dialect close to Igbomina; Oke-Ako, Irele and Omuo one similar to Yagba-Ekiti."),
    "WEKP": WS("Ekiti people", "One of the largest historical sub-groups of the Yoruba, a Central Yoruba group; 'Ekiti State is populated exclusively by Ekiti people'; the name from Okiti, 'hilly'."),
    "WOMUO": WS("Omuo", "Omuo-Ekiti, 'the seat of the Ekiti-East local government'; chiefs administer communities including Ahan."),
    "WMOBA": WS("Moba, Nigeria", "LGA with headquarters at Otun."),
    "WAHAN": P142.SOURCES["WAHAN"],
}
LGAS = ["ado-ekiti", "aiyekire", "efon", "ekiti-east", "ekiti-south-west", "ekiti-west", "emure", "idosi-osi", "ijero", "ikere", "ikole", "ilejemeje",
        "irepodun-ifelodun", "ise-orun", "moba", "oye"]
EXTRA = {
    "moba": " Wikipedia (Ekiti State): Otun, the LGA's headquarters (Wikipedia, Moba), 'speaks a dialect close to the one spoken by the Igbominas in Kwara State'.",
    "ekiti-east": " Wikipedia (Ekiti State): the people of Omuo, the LGA's headquarters, 'speak a similar dialect to that of YagbaEkiti of Kogi State'.",
}
PO = " Presence only; the nature of their presence is not established."
RELATIONS = [
    dict(frm="@languages:yoruba", type="spoken_in", to="@admin_units:state:ekiti", source="ATLAS", evidence="multiple_sources", level="well_documented",
         notes="Blench's Atlas names Ekiti among the dialects of Yoruba (Central group, with Ife and Ijesha); Wikipedia: the Ekiti 'speak a dialect of Yoruba language known as Ekiti'. The Atlas's state list predates Ekiti State (1996)."),
    dict(frm="@languages:ahan", type="spoken_in", to="@admin_units:lga:ekiti/ekiti-east", source="ATLAS", evidence="multiple_sources", level="well_documented",
         notes="Atlas: Omou town (then in Ondo State); Wikipedia: Omuo-Ekiti is the headquarters of Ekiti East and Ahan one of its communities; Wikipedia (Ahan language): spoken at Ahan-Ayegunle in the Omuo Oke district."),
    dict(frm="@ethnic_groups:yoruba", type="present_in", to="@admin_units:state:ekiti", source="FGEK", evidence="multiple_sources", level="well_documented",
         notes="Federal profile: 'Ekiti dialet is common to the people of the State'; Wikipedia: 'primarily inhabited for centuries by the Ekiti people, a Yoruba subgroup, with minorities of the Akoko Yoruba subgroup, and Yagba–Ekiti Yoruba subgroup'."),
]
for l in LGAS:
    RELATIONS.append(dict(frm="@ethnic_groups:yoruba", type="present_in", to=f"@admin_units:lga:ekiti/{l}", source="WEKP", evidence="multiple_sources", level="well_documented",
                          settlement_status="unknown", notes=f"The Ekiti, a Yoruba sub-group: 'Ekiti State is populated exclusively by Ekiti people' (Wikipedia, Ekiti people); the federal profile: the Ekiti dialect 'is common to the people of the State'.{EXTRA.get(l, '')}{PO}"))
GAPS = [
    ("Ekiti: Akoko and Yagba-Ekiti minorities", "Wikipedia names Akoko and Yagba-Ekiti Yoruba minorities in Ekiti State without saying where; they are recorded in the notes only."),
    ("Ekiti: Arigidi", "Wikipedia, citing Ethnologue, places the Arigidi cluster in Ekiti East as well as Akoko; the Atlas places all its members in Akoko North-West (Ondo). Not linked to Ekiti."),
    ("Ekiti: Yoruba sub-groups", "The Ekiti are recorded in the Yoruba link notes, as the sub-groups of the other south-western states are; whether they get their own record is the open south-western decision."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Ekiti (Phase 3): Yoruba language linked to the state; Ahan linked to Ekiti East; Yoruba people (the Ekiti) linked to the state and all 16 LGAs.")


def report():
    L = ["# Research batch 148 — Ekiti: languages and peoples", "",
         f"Researched {ACCESSED}. Phase 3, the first Ekiti batch. Created in review; published only after your approval.", "",
         "## Summary", "",
         "- **Languages:** Ekiti State was created in 1996, after the Atlas's survey, so the Atlas names no Ekiti State. Two Atlas entries concern it:",
         "  - **Yoruba**, whose dialects include **Ekiti** (Central group, with Ife and Ijesha). The Yoruba language was not yet linked to Ekiti State; it now is.",
         "  - **Ahan**, at Omuo, now linked to **Ekiti East** (Wikipedia: Omuo is the LGA's headquarters, and Ahan one of its communities). The state link was made in batch 142.",
         "- **Peoples:** no new records. The federal profile says the Ekiti dialect 'is common to the people of the State', and Wikipedia says the state 'is populated exclusively by Ekiti people'.",
         f"- **Yoruba linked to the state and all 16 LGAs** (*well documented*), with the Ekiti sub-group in the notes.",
         "  - **Moba:** Otun speaks a dialect close to Igbomina.",
         "  - **Ekiti East:** Omuo speaks one close to Yagba-Ekiti.",
         "- Wikipedia also names small **Akoko** and **Yagba-Ekiti** minorities without saying where they live; these are in the gaps.", "",
         "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_148_ekiti_languages_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_148_ekiti_languages_peoples_REVIEW.md", "w").write(report())
    print(f"relations={len(RELATIONS)}")
