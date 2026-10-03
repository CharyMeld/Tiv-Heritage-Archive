"""
Research batch 105 — Zamfara (Phase 3, first batch): languages and peoples. Researched 2026-10-02.
Pattern: batch 099 (Katsina), combined because Zamfara has no minority language in the Atlas.

Languages: the column-split Atlas text was searched for 'Zamfara', 'Sokoto State' (Zamfara was created from Sokoto in
1996) and the 14 Zamfara LGA names. Only Hausa is placed in Zamfara ('spoken as a first language in large areas of …
Zamfara'; 'Zamfarawa – Western dialect of Hausa'); Hausa was linked to Zamfara in batch 087. No new language links.
Peoples:
  * Federal Government of Nigeria, state profile 'Zamfara' (data/fg_zamfara_2026-10-02.html): its 'Ethnic Profile'
    gives only area and borders — not cited for peoples.
  * Wikipedia, 'Zamfara State' (introduction and Demographics): 'densely populated … with the Hausa'. Hausa sub-groups by
    LGA: Zamfarawa in Anka, Gummi, Bukkuyum and Talata Mafara; Gobirawa (from the Gobir Kingdom) in Shinkafi; Burmawa in
    Bakura; Katsinawa, Garewatawa and Hadejawa in Chafe (Tsafe), Bungudu and Maru; Alibawa in Kaura Namoda and Zurmi.
    Fulani 'scattered all over the State', concentrated in Bungudu, Maradun and Gusau; the 'Alawan Shehu Usmanu Fulani'
    in Birnin Magaji.
No new records; the Hausa sub-groups are recorded in the link notes, not as separate peoples. Every Zamfara LGA gets at
least one people link.
"""
import json, sys
import batch_087_kano_languages as L87

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "WZAM": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Zamfara State", organisation="Wikipedia", url=W("Zamfara State"),
                 verification_status="needs_corroboration",
                 notes=f"Hausa sub-groups by LGA: Zamfarawa (Anka, Gummi, Bukkuyum, Talata Mafara); Gobirawa (Shinkafi); Burmawa (Bakura); Katsinawa, Garewatawa and Hadejawa (Chafe, Bungudu, Maru); Alibawa (Kaura Namoda, Zurmi). Fulani scattered over the state, concentrated in Bungudu, Maradun and Gusau; 'Alawan Shehu Usmanu Fulani' in Birnin Magaji. Accessed {ACCESSED}."),
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. 'Zamfarawa – Western dialect of Hausa'; Hausa first-language area includes Zamfara."),
}
HAUSA = {  # lga: sub-group note
    "anka": "the Zamfarawa, a section of the Hausa", "gummi": "the Zamfarawa, a section of the Hausa", "bukkuyum": "the Zamfarawa, a section of the Hausa",
    "talata-mafara": "the Zamfarawa, a section of the Hausa", "shinkafi": "the Gobirawa, a Hausa sub-group from the Gobir Kingdom", "bakura": "the Burmawa, a Hausa sub-group",
    "tsafe": "Hausa sub-groups, mainly Katsinawa, Garewatawa and Hadejawa (Wikipedia writes Chafe)", "bungudu": "Hausa sub-groups, mainly Katsinawa, Garewatawa and Hadejawa",
    "maru": "Hausa sub-groups, mainly Katsinawa, Garewatawa and Hadejawa", "kaura-namoda": "the Alibawa, a Hausa sub-group", "zurmi": "the Alibawa, a Hausa sub-group",
}
FULANI = {"bungudu": "a significant concentration of the Fulani", "maradun": "a significant concentration of the Fulani", "gusau": "a significant concentration of the Fulani",
          "birnin-magaji-kiyaw": "the 'Alawan Shehu Usmanu Fulani'"}
RELATIONS = [
    dict(frm="@ethnic_groups:hausa", type="present_in", to="@admin_units:state:zamfara", source="WZAM", evidence="multiple_sources", level="well_documented",
         notes="Wikipedia: Zamfara is 'densely populated … with the Hausa' and 'mainly populated by Hausa'; Blench's Atlas names Zamfara among Hausa first-language areas (the Zamfarawa dialect)."),
    dict(frm="@ethnic_groups:fulani", type="present_in", to="@admin_units:state:zamfara", source="WZAM", evidence="single_reliable_source", level="reported",
         notes="Wikipedia: the Fulani 'are scattered all over the State'."),
]
for l, n in HAUSA.items():
    RELATIONS.append(dict(frm="@ethnic_groups:hausa", type="present_in", to=f"@admin_units:lga:zamfara/{l}", source="WZAM", evidence="single_reliable_source", level="reported",
                          settlement_status="unknown", notes=f"Wikipedia (Zamfara State): {n}. Presence only; the nature of their presence is not established."))
for l, n in FULANI.items():
    RELATIONS.append(dict(frm="@ethnic_groups:fulani", type="present_in", to=f"@admin_units:lga:zamfara/{l}", source="WZAM", evidence="single_reliable_source", level="reported",
                          settlement_status="unknown", notes=f"Wikipedia (Zamfara State): {n}. Presence only; the nature of their presence is not established."))
GAPS = [
    ("Zamfara: Hausa sub-groups", "Wikipedia names Zamfarawa, Gobirawa, Burmawa, Katsinawa, Garewatawa, Hadejawa and Alibawa by LGA; they are recorded in the Hausa link notes, not as separate peoples. A source on each sub-group would be needed before giving them records."),
    ("Zamfara: other peoples", "No source read names minority peoples (such as Kamuku, Dukawa or Gwari, found in neighbouring states) in Zamfara."),
    ("Zamfara: federal profile", "The federal profile's 'Ethnic Profile' gives only area and borders."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Zamfara (Phase 3): Hausa and Fulani linked to Zamfara and all 14 LGAs (Hausa sub-groups and Fulani concentrations per Wikipedia).")


def report():
    lg = [r for r in RELATIONS if "lga:" in r["to"]]
    L = ["# Research batch 105 — Zamfara: languages and peoples", "",
         f"Researched {ACCESSED}. Phase 3, the first Zamfara batch. Created in review; published only after your approval.", "",
         "- **Languages:** as in Kano and Katsina, the Atlas places only Hausa in Zamfara (its western 'Zamfarawa' dialect). Hausa was linked in batch 087, so **no new language links** are needed.",
         "- **Peoples:** no new records. Hausa and Fulani are linked to the state.",
         f"- **{len(lg)} people–LGA links covering all 14 LGAs**, all *reported*, from Wikipedia's state article. It places the Hausa **sub-groups** by LGA:",
         "  - **Zamfarawa:** Anka, Gummi, Bukkuyum, Talata Mafara",
         "  - **Gobirawa:** Shinkafi",
         "  - **Burmawa:** Bakura",
         "  - **Katsinawa, Garewatawa and Hadejawa:** Tsafe (Chafe), Bungudu, Maru",
         "  - **Alibawa:** Kaura Namoda, Zurmi",
         "  - **Fulani concentrations:** Bungudu, Maradun, Gusau and Birnin Magaji",
         "- **How the sub-groups are recorded:** they go in the link notes, not as separate peoples, until a source on each is found.",
         "- The federal profile gives no peoples for Zamfara.", "",
         "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_105_zamfara_languages_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_105_zamfara_languages_peoples_REVIEW.md", "w").write(report())
    print(f"relations={len(RELATIONS)} people-LGA={sum(1 for r in RELATIONS if 'lga:' in r['to'])} LGAs={len({r['to'] for r in RELATIONS if 'lga:' in r['to']})}")
