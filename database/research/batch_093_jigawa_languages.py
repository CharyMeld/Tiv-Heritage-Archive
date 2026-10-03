"""
Research batch 093 — Jigawa (Phase 3, first batch): the languages of Jigawa State. Researched 2026-10-02.
Pattern: batches 063, 069, 075, 081 and 087.

Method: the column-split Atlas text (pages 30–135) was searched for 'Jigawa' and the 27 Jigawa LGA names (Jigawa was
split from Kano in 1991; the Atlas writes 'Jigawa State' throughout, and no 'Kano State' entry names a Jigawa LGA).
Entries placing a language in Jigawa (data/atlas_jigawa_blocks.txt):
  * Auyokawa [†] No. 29 — 'Jigawa State, Keffin Hausa LGA, Auyo'; extinct; Chadic West B, Bade group.
    Auyo is now an LGA of its own → linked to Auyo, reported. Glottolog auyo1240.
  * Shira [†] No. 420 — 'Shira town, Jigawa State, Keffin Hausa LGA; extinct'; Bade group. Glottolog: the Shira
    family (shir1276) contains Auyokawa and Teshenawa; no languoid for Shira itself was found → no code.
  * Teshena [†] No. 439 — 'Teshena town, Jigawa State, Keffin Hausa LGA; extinct'; Bade group. Glottolog tesh1239.
  * Bade No. 34 — 'Borno State, Bade LGA; Jigawa State, Hadejia LGA' (existing record; Bade LGA is now in Yobe).
  * Kanuri–Kanembu No. 240 — '… Jigawa State, Hadejia LGA' (existing Kanuri record).
  * Warji No. 477 — '… Jigawa State, Birnin Kudu LGA' (existing record).
  * Uled Suliman Arabic — migratory loops 'into Yobe and Jigawa states in the Hadejia-Nguru wetlands' (existing).
  * Hausa — already linked to Jigawa (batch 087).
'Keffin Hausa' is the Atlas spelling of Kafin Hausa.
"""
import json, sys
import batch_087_kano_languages as L87
import batch_063_borno_languages as B63

ACCESSED = "2026-10-02"
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Jigawa entries read (data/atlas_jigawa_blocks.txt): Auyokawa (29), Bade (34), Kanuri–Kanembu (240), Shira (420), Teshena (439), Warji (477), Uled Suliman Arabic."),
    "GLIDX": L87.SOURCES["GLIDX"],
}
S, L = "spelling_variant", "alternative"
FIELD = dict(B63.FIELD)
BADE = "Chadic (West B: Bade group)"
TEXT = {
 "auyokawa": """Auyokawa is an extinct language that was spoken at Auyo, in Jigawa State, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as Chadic (West B: Bade group). The Atlas places it in 'Keffin Hausa LGA, Auyo'; Auyo is now an LGA of its own. Glottolog groups it with Teshenawa in a Shira family within the Ngizim–Southwestern Bade languages.""",
 "shira": """Shira, also called Shirawa, is an extinct language once spoken at Shira town in Kafin Hausa LGA of Jigawa State, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as Chadic (West B: Bade group). Glottolog names a Shira family, which includes Auyokawa and Teshenawa, but no Glottolog entry for the Shira language itself was found. The Atlas also mentions Shirawa as an extinct Chadic language of the Katagum region.""",
 "teshena": """Teshena, also called Teshenawa, is an extinct language once spoken at Teshena town in Kafin Hausa LGA of Jigawa State, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as Chadic (West B: Bade group). Glottolog lists it as Teshenawa, in the Shira family within the Ngizim–Southwestern Bade languages.""",
}
LANG = {  # key: (name, [(name, type, field)], glottocode, [jigawa lgas], lga note)
 "auyokawa": ("Auyokawa", [], "auyo1240", ["auyo"], "Atlas: 'Jigawa State, Keffin Hausa LGA, Auyo' — Auyo is now an LGA of its own."),
 "shira": ("Shira", [("Shirawa", L, "1.A")], "", ["kafin-hausa"], "Atlas: 'Shira town, Jigawa State, Keffin Hausa LGA; extinct'."),
 "teshena": ("Teshena", [("Teshenawa", L, "1.A")], "tesh1239", ["kafin-hausa"], "Atlas: 'Teshena town, Jigawa State, Keffin Hausa LGA; extinct'."),
}
RECORDS, RELATIONS, NAMES = [], [], []
for k, (name, names, g, lgas, note) in LANG.items():
    t = TEXT[k]
    f = dict(lang_type="language", name=name, slug=k, parent_id="@languages:west-chadic", vitality_status="extinct", summary=t.split(". ")[0] + ".", description=t)
    if g: f["glottocode"] = g
    RECORDS.append(dict(key=k, table="languages", evidence="single_reliable_source", level="well_documented", fields=f,
                        srcs=[("ATLAS", f"{name}: location, classification, status")] + ([("GLIDX", f"Glottocode {g}")] if g else [])))
    RELATIONS.append(dict(frm=k, type="spoken_in", to="@admin_units:state:jigawa", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes="Blench's Atlas (extinct)."))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=f"@admin_units:lga:jigawa/{l}", source="ATLAS", evidence="single_reliable_source",
                              level="reported" if l == "auyo" else "well_documented", notes=note + " Former use; the language is extinct."))
    for n, ty, fld in names:
        NAMES.append(dict(record=k, name=n, name_type=ty, usage_notes=f"Blench's Atlas (2020), field {fld}: {FIELD[fld]}.", srcs=["ATLAS"]))
EXISTING = {
    "bade": (["hadejia"], "Atlas: 'Borno State, Bade LGA; Jigawa State, Hadejia LGA'."),
    "kanuri": (["hadejia"], "Atlas, Kanuri–Kanembu cluster: '… Jigawa State, Hadejia LGA'."),
    "warji": (["birnin-kudu"], "Atlas: '… Ningi LGA, Warji district; Jigawa State, Birnin Kudu LGA'."),
    "uled-suliman-arabic": ([], "Atlas: their migratory loops 'are now extending far southwards into Yobe and Jigawa states in the Hadejia-Nguru wetlands'."),
}
for lang, (lgas, note) in EXISTING.items():
    RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to="@admin_units:state:jigawa", source="ATLAS", evidence="single_reliable_source",
                          level="reported" if not lgas else "well_documented", notes=note))
    for l in lgas:
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=f"@admin_units:lga:jigawa/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
GAPS = [
    ("Jigawa: languages by LGA", "The Atlas places living languages other than Hausa only in Hadejia (Bade, Kanuri) and Birnin Kudu (Warji); 23 LGAs have no language placed by name. Hausa and Fulfulde are statewide."),
    ("Jigawa: Shira", "No Glottolog languoid for the Shira language was found; Glottolog's Shira family (shir1276) contains Auyokawa and Teshenawa."),
    ("Jigawa: Fulfulde", "Fulfulde is not placed in Jigawa by the Atlas entries read; to be checked in the peoples batch."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Jigawa languages (Blench Atlas): 3 extinct Bade-group languages (Auyokawa, Shira, Teshena); Jigawa links for Bade, Kanuri, Warji and Uled Suliman Arabic.")


def report():
    L = ["# Research batch 093 — Jigawa: the languages", "",
         f"Researched {ACCESSED}. Phase 3, the first Jigawa batch. Created in review; published only after your approval.", "",
         "## Summary", "",
         "- **Jigawa, like Kano, is overwhelmingly Hausa-speaking.** Hausa was linked to Jigawa in batch 087.",
         "- **3 new records:** extinct Bade-group (Chadic) languages of the old Kafin Hausa area:",
         "  - **Auyokawa** (`auyo1240`): at Auyo, now its own LGA, so the link is *reported*",
         "  - **Shira** (no Glottolog entry found)",
         "  - **Teshena** (`tesh1239`)",
         "- **4 existing languages get Jigawa links:**",
         "  - **Bade** and **Kanuri**: Hadejia LGA",
         "  - **Warji**: Birnin Kudu LGA",
         "  - **Uled Suliman Arabic**: herders in the Hadejia-Nguru wetlands, *reported*",
         f"- **{len(RELATIONS)} links** and **{len(NAMES)} other names**.", "",
         "## The new languages", ""] + [f"**{LANG[k][0]}** ({LANG[k][2] or 'no Glottocode'}). {TEXT[k]}\n" for k in LANG]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_093_jigawa_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_093_jigawa_languages_REVIEW.md", "w").write(report())
    print(f"languages={len(LANG)} relations={len(RELATIONS)} names={len(NAMES)}")
