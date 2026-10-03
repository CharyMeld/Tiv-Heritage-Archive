"""
Research batch 087 — Kano (Phase 3, first batch): the languages of Kano State. Researched 2026-10-02.
Pattern: batches 063, 069, 075 and 081.

Method: the column-split Atlas text (pages 30–135) was searched for 'Kano' and the 44 Kano LGA names. Only two
entries place a language in Kano: Hausa (No. 173, 'spoken as a first language in large areas of Sokoto, Zamfara,
Kaduna, Kano, Katsina, Jigawa, Gombe and Bauchi States …') and Kurama (No. 273, 'Kaduna State, Saminaka and Ikara
LGAs; Kano State, Tudun Wada LGA'). The Atlas also lists 'Kano' as a dialect of Fulfulde and as the Eastern dialect
of Hausa. Kano was split in 1991 (Jigawa); none of these entries names a Jigawa LGA.

New records (2):
  * Hausa (Glottolog haus1257; West Chadic A) — the archive had a Hausa people record but no Hausa language record,
    a gap the QC reports in every state ('Hausa has no speaks link'). Linked to the eight states the Atlas names and
    to the Hausa people (speaks).
  * Kurama (Glottolog kura1249; Kainji > Eastern Kainji > Jos > Kauru) — Tudun Wada LGA; Kaduna State.
Existing: Fulfulde gets a Kano link (Wikipedia: Hausa and Fulfulde are the state's official languages, citing the
Kano State Government's 2003 profile; Atlas: 'Kano' is a dialect of Fulfulde).
Not used: Wikipedia's sentence that Moro, Kurama and Map are spoken in Doguwa LGA is marked 'citation needed'.
"""
import json, sys
import batch_081_gombe_languages as L81
import batch_063_borno_languages as B63

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "ATLAS": dict(L81.SOURCES["ATLAS"], notes="Reused. Kano entries read: Hausa (No. 173), Kurama (No. 273), Fulfulde (dialects)."),
    "GLIDX": L81.SOURCES["GLIDX"],
    "WKAN": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Kano State", organisation="Wikipedia", url=W("Kano State"),
                 verification_status="needs_corroboration",
                 notes=f"'The official languages of Kano State are Hausa and Fulfulde' (citing the Kano State Government profile, 2003, 'verify source'); 'Hausa is the dominant language in the state'. Accessed {ACCESSED}."),
}
S, B, L = "spelling_variant", "endonym", "alternative"
FIELD = dict(B63.FIELD)
HAUSA_STATES = ["sokoto", "zamfara", "kaduna", "kano", "katsina", "jigawa", "gombe", "bauchi"]
TEXT = {
 "hausa": """Hausa is spoken as a first language in large areas of Sokoto, Zamfara, Kaduna, Kano, Katsina, Jigawa, Gombe and Bauchi states and in the Republic of Niger, and as a regional language far beyond them, in the Middle Belt of Nigeria, northern Ghana and Benin Republic, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as Chadic (West A: Hausa group). The Atlas cites 5,700,000 speakers in 1952 and some 25 million first- and second-language speakers (SIL 1973), including about 3.5 million outside Nigeria. Its dialects fall into an Eastern group (Kano, Katagum, Hadejiya), a Western group (Sokoto, Gobirawa, Adarawa, Kebbawa, Zamfarawa) and a Northern group (Katsina, Arewa). It has an official orthography and a large literature, including scripture portions from 1853, a complete Bible from 1932 and Muslim literature in the Arabic-based Ajami script; an indigenous Hausa sign language is also recorded. Wikipedia names Hausa as the dominant language of Kano State and, with Fulfulde, its official language.""",
 "kurama": """Kurama is a language spoken in Tudun Wada LGA of Kano State and in Saminaka and Ikara LGAs of Kaduna State, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as Benue–Congo (Kainji: Eastern Kainji, Northern Jos group, Kauru subgroup). Its speakers call it Tikurumi and themselves Akurumi; Bagwama, another name, is also used of the Ruma. The Atlas cites 11,300 speakers in 1949 and notes a scripture project in progress.""",
}
LANG = {  # key: (name, parent, [(name, type, field)], glottocode, states, [kano lgas])
 "hausa": ("Hausa", "@languages:west-chadic", [("Haussa", S, "1.A"), ("Haoussa", S, "1.A"), ("Háusá", B, "1.B"), ("Abakwariga", L, "2.B"), ("Haɓe", L, "2.B"), ("Kaɗo", L, "2.B")],
           "haus1257", HAUSA_STATES, []),
 "kurama": ("Kurama", "@languages:kainji", [("Tikurumi", B, "1.B"), ("Bagwama", L, "2.B")], "kura1249", ["kano", "kaduna"], ["tudun-wada"]),
}
RECORDS, RELATIONS, NAMES = [], [], []
for k, (name, parent, names, g, states, lgas) in LANG.items():
    t = TEXT[k]
    RECORDS.append(dict(key=k, table="languages", evidence="multiple_sources" if k == "hausa" else "single_reliable_source", level="well_documented",
                        fields=dict(lang_type="language", name=name, slug=k, parent_id=parent, glottocode=g, summary=t.split(". ")[0] + ".", description=t),
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers"), ("GLIDX", f"Glottocode {g}")] + ([("WKAN", "Hausa: dominant and official language of Kano State")] if k == "hausa" else [])))
    for st in states:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=f"@admin_units:state:{st}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                              notes="Blench's Atlas" + (": 'spoken as a first language in large areas of' this state." if k == "hausa" else ".")))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=f"@admin_units:lga:kano/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                              notes="Atlas: 'Kano State, Tudun Wada LGA'."))
    for n, ty, fld in names:
        NAMES.append(dict(record=k, name=n, name_type=ty, usage_notes=f"Blench's Atlas (2020), field {fld}: {FIELD[fld]}.", srcs=["ATLAS"]))
RELATIONS += [
    dict(frm="@ethnic_groups:hausa", type="speaks", to="hausa", source="ATLAS", evidence="single_reliable_source", level="well_documented",
         notes="Atlas, Hausa entry, field 1.C: the people (Bàháushèe, pl. Hàusàawáa)."),
    dict(frm="@languages:fulfulde", type="spoken_in", to="@admin_units:state:kano", source="WKAN", evidence="multiple_sources", level="well_documented",
         notes="Wikipedia: Hausa and Fulfulde are the official languages of Kano State; the Atlas lists 'Kano' as a dialect of Fulfulde and Kano–Katsina–Bauchi–Borno as its Central dialect."),
]
GAPS = [
    ("Kano: languages by LGA", "The Atlas places only Kurama by LGA (Tudun Wada). Hausa and Fulfulde are statewide; Wikipedia's claim that Moro, Kurama and Map are spoken in Doguwa LGA is marked 'citation needed' and is not used."),
    ("Hausa: wider links", "The Hausa language is linked to the eight states the Atlas names as first-language areas; its regional use in the Middle Belt and elsewhere is not linked state by state."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kano languages (Blench Atlas): Hausa (new national record, eight states, Hausa people) and Kurama (Tudun Wada); Fulfulde linked to Kano.")


def report():
    L = ["# Research batch 087 — Kano: the languages", "",
         f"Researched {ACCESSED}. Phase 3, the first Kano batch. Created in review; published only after your approval.", "",
         "## Summary", "",
         "- **Kano is almost wholly Hausa-speaking.** The Atlas places only two languages in Kano: Hausa, statewide, and Kurama, in Tudun Wada.",
         "- **New: the Hausa language record** (`haus1257`). The archive had a Hausa *people* record but no language record, so every state's QC reported 'Hausa has no speaks link'. Hausa is now linked to the 8 states the Atlas names as first-language areas (Sokoto, Zamfara, Kaduna, Kano, Katsina, Jigawa, Gombe and Bauchi), and the Hausa people are linked to it.",
         "- **New: Kurama** (`kura1249`), a Kainji language of Tudun Wada LGA and of southern Kaduna.",
         "- **Fulfulde** gets a Kano link. Wikipedia names Hausa and Fulfulde as the state's official languages.",
         "- **Not used:** Wikipedia's 'citation needed' claim that Moro, Kurama and Map are spoken in Doguwa.",
         f"- **{len(RELATIONS)} links** and **{len(NAMES)} other names**.", "",
         "## The new languages", ""] + [f"**{LANG[k][0]}** ({LANG[k][3]}). {TEXT[k]}\n" for k in LANG]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_087_kano_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_087_kano_languages_REVIEW.md", "w").write(report())
    print(f"languages={len(LANG)} relations={len(RELATIONS)} names={len(NAMES)}")
