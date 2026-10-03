"""
Research batch 036 — Nasarawa (Phase 4 follow-up): other names and language classification.
Researched 2026-09-26, after the Nasarawa QC (NIGERIA_QC_NASARAWA.md).

Names come from Blench's Atlas of Nigerian Languages (entries already read in full for batch 031,
data/atlas_nasarawa_blocks.txt), each read by hand against the Atlas's own key (front matter,
"Key to the Index", checked in the 2020 PDF via the Internet Archive, snapshot 2025-05-28):
    1.A alternate spellings of the head name     -> spelling_variant (language)
    1.B the people's own name for their language -> endonym (language)
    1.C the people's own name for themselves     -> endonym (ethnic group, where we have one)
    2.A other names for the language based on location -> alternative (language)
    2.B other names for the language             -> alternative (language)
    2.C other names for the people               -> not used (no matching group records)
The column extraction left fragments of neighbouring entries in some blocks (e.g. "Mofa" belongs to
Mafa, not Mada; "Kunibum" to Emai); only lines under each Nasarawa head entry are used. Tone-marked
own names (1.B/1.C) are kept as printed. Hausa is left out (not a Nasarawa-specific record).

New records:
  * Jarawan (branch; Glottolog jara1262), so that Mama can be placed: the Atlas has "Bantu: Jarawan",
    Glottolog Southern Bantoid > Jarawan > Nigerian Jarawan > Jar. The change to Mama is in fix_036.
  * Basa-Benue (language): Atlas 40.b, spoken in Nasarawa LGA (and Kogi State); own name RuBasa,
    people TuBasa. Glottolog groups the three Atlas Basa lects as one language, "Basa (Nigeria)"
    (basa1282, ISO bzw, map point in the Kogi Basa area) — recorded, and the Glottocode attached
    with that caveat. Links: the Basa people speak it; spoken in Nasarawa LGA (Atlas; the older
    Nasarawa LGA included today's Toto, where Wikipedia names the Bassa as a main people).
Also (fix_036): Glottocodes for Eloyi (eloy1241, Glottolog "Ajiri", Plateau) and Idun (idun1241,
Dũya, Koroic); an open classification dispute for Eloyi (Idomoid vs Plateau); Nasarawa progress
status -> quality control in progress.
Not linked: Koro (the Atlas uses "Koro" both for the Ashe — "Koron Ache" — and for the Migili —
"Koro of Lafia"); Kulere and Nyankpa (no Nasarawa placement; gaps #450, #460 stay open).
"""
import json, re, sys

ACCESSED = "2026-09-26"
SOURCES = {
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf",
                  verification_status="verified", notes="Reused. Key to the Index: 1.A alternate spellings; 1.B own name for the language; 1.C own name for the people; 2.A location-based names; 2.B other names for the language; 2.C other names for the people."),
    "GJAR": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Jarawan (jara1262)", organisation="Glottolog",
                 url="https://glottolog.org/resource/languoid/id/jara1262", verification_status="verified",
                 notes="Jarawan: Atlantic-Congo > Volta-Congo > Benue-Congo > Bantoid > Southern Bantoid > Jarawan. Mama (mama1272) is in Jarawan > Nigerian Jarawan > Jar."),
    "GBASA": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Basa (Nigeria) (basa1282)", organisation="Glottolog",
                  url="https://glottolog.org/resource/languoid/id/basa1282", verification_status="verified",
                  notes="Basa (Nigeria), ISO 639-3 bzw: Kainji > Central Kainji > Basa-Eastern Kainji > Basa. The Basa group has four languages: Basa (Nigeria), Basa-Gurmana, Basa-Gumna, Bassa-Kontagora. Map point 8.03 N, 7.06 E (Kogi State)."),
}

# language slug -> [(name, name_type, Atlas field)]
LANG_NAMES = {
    "ake": [("Akye", "spelling_variant", "1.A"), ("Aike", "spelling_variant", "1.A")],
    "alago": [("Arago", "spelling_variant", "1.A")],
    "alumu-tesu": [("Arum–Chessu", "spelling_variant", "1.A")],
    "ashe": [("Ache", "spelling_variant", "1.A")],
    "bu-ningkada": [("Jidda", "spelling_variant", "1.A"), ("Ibut", "spelling_variant", "1.A"), ("Nakare", "alternative", "2.B")],
    "ebira": [("Igbirra", "spelling_variant", "1.A"), ("Igbira", "spelling_variant", "1.A"), ("Egbira", "spelling_variant", "1.A"), ("Egbura", "spelling_variant", "1.A")],
    "eggon": [("Egon", "spelling_variant", "1.A"), ("onumu Egon", "endonym", "1.B"), ("Mada Eggon", "alternative", "2.B"), ("Hill Mada", "alternative", "2.B")],
    "eloyi": [("Afo", "alternative", "2.B"), ("Epe", "alternative", "2.B"), ("Aho", "alternative", "2.B"), ("Afu", "alternative", "2.B"), ("Afao", "alternative", "2.B")],
    "gade": [("Gede", "spelling_variant", "1.A")],
    "gbagyi": [("East Gwari", "alternative", "2.A"), ("Gwari Matai", "alternative", "2.A"), ("Gwari", "alternative", "2.B")],
    "gbari": [("Gwari Yamma", "alternative", "2.A"), ("West Gwari", "alternative", "2.A")],
    "goemai": [("Ankwai", "alternative", "2.B"), ("Ankwe", "alternative", "2.B")],
    "hasha": [("Iyashi", "spelling_variant", "1.A"), ("Yashi", "spelling_variant", "1.A")],
    "idun": [("Idṹ", "endonym", "1.B"), ("Dũya", "alternative", "2.A"), ("Adong", "alternative", "2.B")],
    "jili": [("Megili", "spelling_variant", "1.A"), ("Migili", "spelling_variant", "1.A"), ("Lijili", "endonym", "1.B"), ("Koro of Lafia", "alternative", "2.B")],
    "karfa": [("Kerifa", "spelling_variant", "1.A")],
    "mada": [("Yidda", "alternative", "2.B")],
    "mama": [("Kwarra", "alternative", "2.B"), ("Kantana", "alternative", "2.B")],
    "ninzo": [("Ninzam", "spelling_variant", "1.A"), ("Ninzom", "spelling_variant", "1.A"), ("Gbhu", "alternative", "2.B")],
    "nko": [("Agyaga", "alternative", "2.A")],
    "rindre": [("Rendre", "spelling_variant", "1.A"), ("Rindiri", "spelling_variant", "1.A"), ("Lindiri", "spelling_variant", "1.A"),
               ("Wamba", "alternative", "2.A"), ("Nungu", "alternative", "2.A")],
    "toro": [("Turkwam", "alternative", "2.A")],
}
# ethnic group slug -> [(name, Atlas language entry)]  (1.C: the people's own name for themselves)
GROUP_NAMES = {
    "alago": [("Idoma Nokwu", "Alago")],
    "eggon": [("Mo Egon", "Eggon")],
    "gbagyi": [("Ibagyi", "Gbagyi"), ("Gbagye", "Gbagyi")],
    "migili": [("Mijili", "Jili; plural form (singular Jijili)")],
    "mada": [("Məda", "Mada")],
}
FIELD = {"1.A": "alternate spelling of the name", "1.B": "the speakers' own name for the language", "2.A": "name based on location", "2.B": "other name for the language"}

NAMES = []
for slug, lst in LANG_NAMES.items():
    for name, nt, fld in lst:
        NAMES.append(dict(record=f"@languages:{slug}", name=name, name_type=nt, usage_notes=f"Blench's Atlas (2020), field {fld}: {FIELD[fld]}.", srcs=["ATLAS"]))
for slug, lst in GROUP_NAMES.items():
    for name, entry in lst:
        NAMES.append(dict(record=f"@ethnic_groups:{slug}", name=name, name_type="endonym",
                          usage_notes=f"Blench's Atlas (2020), entry {entry}, field 1.C: the people's own name for themselves.", srcs=["ATLAS"]))
NAMES += [
    dict(record="basabenue", name="Basa", name_type="spelling_variant", usage_notes="Blench's Atlas (2020), entry 40.b, field 1.A.", srcs=["ATLAS"]),
    dict(record="basabenue", name="RuBasa", name_type="endonym", usage_notes="Blench's Atlas (2020), entry 40.b, field 1.B: the speakers' own name for the language.", srcs=["ATLAS"]),
    dict(record="basabenue", name="Abacha", name_type="alternative", usage_notes="Blench's Atlas (2020), entry 40.b, field 2.B.", srcs=["ATLAS"]),
    dict(record="basabenue", name="Abatsa", name_type="alternative", usage_notes="Blench's Atlas (2020), entry 40.b, field 2.B.", srcs=["ATLAS"]),
    dict(record="basabenue", name="Basa (Nigeria)", name_type="alternative", usage_notes="Glottolog's name (basa1282), which covers the Atlas's Basa-Gurara, Basa-Benue and Basa-Makurdi.", srcs=["GBASA"]),
    dict(record="@ethnic_groups:basa", name="TuBasa", name_type="endonym", usage_notes="Blench's Atlas (2020), entry 40.b (Basa-Benue), field 1.C: the people's own name for themselves.", srcs=["ATLAS"]),
]

JARAWAN = """Jarawan is a group of Bantoid languages (Benue-Congo) spoken in north-eastern and central Nigeria and in Cameroon. Glottolog places it within Southern Bantoid, and Roger Blench's Atlas of Nigerian Languages lists its languages as "Bantu: Jarawan". In Nasarawa State the group is represented by Mama (also called Kwarra or Kantana), which Glottolog places in the Nigerian Jarawan subgroup."""

BASA = """Basa-Benue is a language of the Basa people, spoken in Bassa and Ankpa LGAs of Kogi State and in Nasarawa LGA of Nasarawa State, according to Roger Blench's Atlas of Nigerian Languages (2020). The Atlas records the speakers' own name for the language as RuBasa and for themselves as TuBasa, and other names as Abacha and Abatsa. It classifies Basa-Benue in the Western Kainji group, in a cluster with Basa-Gurara (Federal Capital Territory) and Basa-Makurdi (Benue State). Glottolog does not separate these three and lists a single language, Basa (Nigeria), ISO code bzw. The Atlas cites speaker figures of 30,000 (1944–50) and 100,000 (SIL, 1973), and records a complete Bible translation (2009).

The Atlas names the LGA as it was when the data were gathered; Toto LGA was later created from Nasarawa LGA, and Wikipedia names the Bassa as one of Toto's main peoples."""

RECORDS = [
    dict(key="jarawan", table="languages", evidence="multiple_sources", level="well_documented",
         fields=dict(lang_type="branch", name="Jarawan", slug="jarawan", glottocode="jara1262",
                     summary="Jarawan is a group of Bantoid languages of Nigeria and Cameroon; in Nasarawa State it includes Mama.", description=JARAWAN),
         srcs=[("GJAR", "Jarawan within Southern Bantoid; Mama in Nigerian Jarawan"), ("ATLAS", "Mama: 'Bantu: Jarawan'")]),
    dict(key="basabenue", table="languages", evidence="single_reliable_source", level="well_documented",
         fields=dict(lang_type="language", name="Basa-Benue", slug="basa-benue", glottocode="basa1282", parent_id="@languages:kainji",
                     summary="Basa-Benue is a Kainji language of the Basa people of Kogi State and Nasarawa LGA, Nasarawa State.", description=BASA),
         srcs=[("ATLAS", "Entry 40.b: location, own names, classification, speakers, Bible"), ("GBASA", "Basa (Nigeria), bzw: one language for the Basa cluster")]),
]
RELATIONS = [
    dict(frm="@ethnic_groups:basa", type="speaks", to="basabenue", source="ATLAS", evidence="single_reliable_source", level="well_documented",
         notes="Their language, per Blench's Atlas (entry 40.b: people TuBasa, language RuBasa)."),
    dict(frm="basabenue", type="spoken_in", to="@admin_units:state:nasarawa", source="ATLAS", evidence="single_reliable_source", level="well_documented",
         notes="Atlas entry 40.b."),
    dict(frm="basabenue", type="spoken_in", to="@admin_units:lga:nasarawa/nasarawa", source="ATLAS", evidence="single_reliable_source", level="well_documented",
         notes="Atlas: 'Nasarawa State, Nassarawa LGA' (the pre-split LGA, which included today's Toto)."),
]
GAPS = [
    ("Koro in Nasarawa: which language", "The Atlas uses 'Koro' for the Ashe ('Koron Ache', Karu) and for the Migili ('Koro of Lafia'); the state's list names the Koro as a people. Which group is meant, and its language, needs an ethnographic source."),
    ("Karfa: Glottocode", "No Glottolog entry found under Karfa or Kerifa (Atlas: Chadic, West A, Ron group, Akwanga LGA, 800 speakers in 1973)."),
    ("Basa lects: Glottolog vs the Atlas", "Glottolog lists one language, Basa (Nigeria) (bzw); the Atlas three (Basa-Gurara, Basa-Benue, Basa-Makurdi). The archive follows the Atlas; which lect each Glottocode point represents is not settled."),
    ("Other names for the peoples", "Only five groups have the people's own name in the Atlas (field 1.C). Other names for the Nasarawa peoples (e.g. exonyms used by neighbours) need ethnographic sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Nasarawa QC follow-up: other names for 22 languages and 6 peoples (Atlas key), Jarawan branch, Basa-Benue language.")


def report():
    ln = [n for n in NAMES if n["record"].startswith("@languages") or n["record"] == "basabenue"]
    gn = [n for n in NAMES if n["record"].startswith("@ethnic_groups")]
    L = ["# Research batch 036 — Nasarawa names and language classification", "",
         f"Researched {ACCESSED}, after the Phase 4 check (`NIGERIA_QC_NASARAWA.md`). Created in review; published only after your approval.", "",
         f"- **{len(ln)} other names for {len(LANG_NAMES) + 1} languages** and **{len(gn)} own names for {len(GROUP_NAMES) + 1} peoples**, all from Blench's Atlas and read by hand against its key (below).",
         "- **Jarawan** branch (Glottolog `jara1262`), so that **Mama** can be placed in its family (fix_036).",
         "- **Basa-Benue**, a new language record (Atlas 40.b). The Basa people now have a 'speaks' link, and the language is linked to Nasarawa LGA.",
         "- fix_036: Glottocodes for **Eloyi** (`eloy1241`) and **Idun** (`idun1241`); an open **classification dispute** for Eloyi (Idomoid in our record, Plateau in Glottolog, 'Plateau or Idomoid' in the Atlas); Nasarawa's status → *quality control in progress*.",
         "- Left open: **Koro** (the Atlas uses the name for two different peoples), **Kulere** and **Nyankpa** (no Nasarawa placement), **Karfa** (no Glottocode).", "",
         "## The Atlas key (how each name was typed)", "",
         "| Atlas field | Meaning (Atlas, 'Key to the Index') | Stored as |", "|---|---|---|",
         "| 1.A | Alternate spellings of the head name | spelling variant of the language |",
         "| 1.B | The peoples' own name for their language | endonym of the language |",
         "| 1.C | The peoples' own name for themselves | endonym of the people |",
         "| 2.A | Other names for the language based on its location | alternative name of the language |",
         "| 2.B | Other names for the language | alternative name of the language |",
         "| 2.C | Other names for the people | not used (no matching records) |", "",
         "So names such as Afo (Eloyi), Gwari (Gbagyi) and Kantana (Mama) are recorded as names of the **language**, as the Atlas files them (field 2.B). They are not applied to the peoples.", "",
         "## Names by language", "", "| Language | Names (Atlas field) |", "|---|---|"]
    for slug, lst in LANG_NAMES.items():
        L.append(f"| {slug} | " + "; ".join(f"{n} ({f})" for n, _, f in lst) + " |")
    L.append("| basa-benue (new) | Basa (1.A); RuBasa (1.B); Abacha, Abatsa (2.B); Basa (Nigeria) (Glottolog) |")
    L += ["", "## Own names of peoples (field 1.C)", ""] + [f"- **{s}**: " + ", ".join(n for n, _ in lst) for s, lst in GROUP_NAMES.items()] + ["- **basa**: TuBasa"]
    L += ["", "## New records", "", "### Jarawan", "", JARAWAN, "", "### Basa-Benue", "", BASA, "",
          "## Not used", "",
          "- Fragments of neighbouring Atlas entries caught in the column extraction ('Mofa' under Mada is Mafa's; 'Kunibum' under Eloyi is Emai's; the Hausa fields under Hasha are Hausa's).",
          "- Field 2.C names (Koron Ache for the Ashe; Jaba Lungu for the Idun): there are no Ashe or Idun people records to attach them to.",
          "- Hausa names (Abakwariga etc.): Hausa is not a Nasarawa-specific record.", "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_036_nasarawa_names.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_036_nasarawa_names_REVIEW.md", "w").write(report())
    print(f"names={len(NAMES)} records={len(RECORDS)} relations={len(RELATIONS)} gaps={len(GAPS)}")
