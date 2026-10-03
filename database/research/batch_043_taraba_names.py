"""
Research batch 043 — Taraba (Phase 4 follow-up): other names and language classification.
Researched 2026-09-30, after the Taraba QC (NIGERIA_QC_TARABA.md). Owner approved the plan, with
Mambila kept as ONE record.

Names come from Blench's Atlas of Nigerian Languages (Taraba entries already saved in full,
data/atlas_taraba_blocks.txt), typed by the Atlas's own key exactly as in batch 036:
    1.A alternate spellings -> spelling_variant (language)   1.B own name for the language -> endonym (language)
    1.C own name for the people -> endonym (ethnic group, only where we have the group)
    2.A/2.B other names for the language -> alternative (language)
Most languages the QC listed as "no other names" have NO name fields in their Atlas entry
(Ambo, Batu, Bete, Bukwen, Dong, Kapya, Mashi, Mumuye, Ndunda, Pangseng, Rang, Tep, Tha, Ligri,
Jibu), or only field 1.C for a people we hold no record of (Akum 'Anyar', Kam 'Nyimwom',
Lamja-Deŋsa-Tola, Limbum 'Wimbum', Naki 'Bunaki', Nyam 'Nyambolo', Dirim 'Daka'). They stay gaps.

New records:
  * Southern Bantoid (branch; Glottolog sout3152), so that Buru can be placed: the Atlas has
    "South Bantoid: unclassified", Glottolog Bantoid > Southern Bantoid > Buru-Angwe. The change
    to Buru is in fix_043.
  * Yendang (language; Glottolog yend1241, ISO ynq), the language of the Yandang people: the Atlas
    index has "Yandang = Yendang" and lists Yendang in its Mumuye–Yendang group. Link: Yandang
    speaks Yendang. No 'spoken in' links: the Atlas has no Taraba entry for it; the group's Taraba
    LGA links (Wikipedia/Ethnologue table) are already on the people record.
fix_043: Buru -> Southern Bantoid + buru1326; Mambila mamb1312 (one record, owner's choice);
Gbaya nort2775 (probable); Glottolog notes for Dirim, Joole, Kulung (Chadic); Taraba progress ->
quality control in progress.
Not done: Hausa and Fulani 'speaks' links (no national Hausa or Fulfulde language record yet).
"""
import json, sys

ACCESSED = "2026-09-30"
G = "https://glottolog.org/resource/languoid/id/"
SOURCES = {
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf",
                  verification_status="verified", notes="Reused. Taraba entries read in full (data/atlas_taraba_blocks.txt)."),
    "GLIDX": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Glottolog language index (resourcemap)", organisation="Glottolog",
                  url="https://glottolog.org/resourcemap.json?rsc=language", verification_status="verified", notes="Reused."),
    "GSB": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Southern Bantoid (sout3152)", organisation="Glottolog",
                url=G + "sout3152", verification_status="verified",
                notes="Southern Bantoid: Atlantic-Congo > Volta-Congo > Benue-Congo > Bantoid > Southern Bantoid (family)."),
    "GBURU": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Buru (Nigeria) (buru1326)", organisation="Glottolog",
                  url=G + "buru1326", verification_status="verified",
                  notes="Buru (Nigeria), dialect of Buru-Angwe (buru1299, ISO bqw): Benue-Congo > Bantoid > Southern Bantoid > Buru-Angwe."),
    "GYEND": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Yendang (yend1241)", organisation="Glottolog",
                  url=G + "yend1241", verification_status="verified",
                  notes="Yendang, ISO 639-3 ynq: North Volta-Congo > Cameroun-Ubangian > Central Adamawa > Mumuye-Yandang > Yandangic > Waka-Yendang-Teme > Waka-Yandang."),
    "GMAMB": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Mambila (mamb1312)", organisation="Glottolog",
                  url=G + "mamb1312", verification_status="verified",
                  notes="Mambila (family), in Northern Bantoid > Mambiloid. Nigerian varieties: Western Mambila (nige1255, ISO mzk) and Donga Mambila (came1252, ISO mcu), both Cameroon and Nigeria."),
    "GGBAYA": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Northwest Gbaya (nort2775)", organisation="Glottolog",
                   url=G + "nort2775", verification_status="verified",
                   notes="Northwest Gbaya, ISO gya: Gbaya-Manza-Ngbaka > Gbaya Meridional-Occidental. Countries: CF, CG, CM, NG — the only Gbaya language Glottolog lists for Nigeria."),
}

NAMES = [
    dict(record="@languages:etulo", name="Utur", name_type="spelling_variant", usage_notes="Blench's Atlas (2020), entry 129 Etulo, field 1.A: alternate spelling of the name.", srcs=["ATLAS"]),
    dict(record="@languages:etulo", name="Eturo", name_type="spelling_variant", usage_notes="Blench's Atlas (2020), entry 129 Etulo, field 1.A: alternate spelling of the name.", srcs=["ATLAS"]),
    dict(record="@ethnic_groups:jenjo", name="Èédzá", name_type="endonym", usage_notes="Blench's Atlas (2020), entry 100 Dza (the Jenjo's language), field 1.C: the people's own name for themselves.", srcs=["ATLAS"]),
    dict(record="@ethnic_groups:jenjo", name="ídzà", name_type="endonym", usage_notes="Blench's Atlas (2020), entry 100 Dza (the Jenjo's language), field 1.C: the people's own name for themselves.", srcs=["ATLAS"]),
]

SBANTOID = """Southern Bantoid is a branch of the Bantoid languages (Benue-Congo). Glottolog (sout3152) places it directly under Bantoid; it includes the Bantu languages. In Taraba State, Roger Blench's Atlas of Nigerian Languages (2020) assigns several languages to it, among them Batu (Southern Bantoid: Tivoid), Bukwen and Mashi (South Bantoid: Beboid), and Buru, which the Atlas leaves unclassified within South Bantoid."""

YENDANG = """Yendang is an Adamawa language, the language of the Yandang people. Roger Blench's Atlas of Nigerian Languages (2020) gives Yandang as another name for Yendang, and in its classification places Yendang in the Yendang group of the Mumuye–Yendang group of Adamawa languages, alongside Waka and Yoti, with Maya (Bali), Kpasham and Teme in the same group. The Atlas has no Taraba State entry for the language.

Glottolog lists Yendang (yend1241, ISO 639-3 code ynq) in Central Adamawa, within the Mumuye-Yandang group (Yandangic > Waka-Yendang-Teme > Waka-Yandang), which agrees with the Atlas. Wikipedia names the Yandang among the main peoples of Taraba State; where they live is recorded on the Yandang people's page."""

RECORDS = [
    dict(key="sbantoid", table="languages", evidence="multiple_sources", level="well_documented",
         fields=dict(lang_type="branch", name="Southern Bantoid", slug="southern-bantoid", glottocode="sout3152",
                     summary="Southern Bantoid is the branch of Bantoid that includes the Bantu languages; in Taraba it includes Batu, Bukwen, Mashi and Buru.", description=SBANTOID),
         srcs=[("GSB", "Southern Bantoid within Bantoid"), ("ATLAS", "Batu, Bukwen, Mashi, Buru: South(ern) Bantoid")]),
    dict(key="yendang", table="languages", evidence="multiple_sources", level="well_documented",
         fields=dict(lang_type="language", name="Yendang", slug="yendang", glottocode="yend1241", iso639_3="ynq", parent_id="@languages:adamawa",
                     summary="Yendang is an Adamawa language of the Mumuye–Yendang group, the language of the Yandang people.", description=YENDANG),
         srcs=[("ATLAS", "Index 'Yandang = Yendang'; classification list: Mumuye-Yendang group > Yendang group"), ("GYEND", "yend1241, ISO ynq, Central Adamawa > Mumuye-Yandang")]),
]
NAMES.append(dict(record="yendang", name="Yandang", name_type="alternative", usage_notes="Blench's Atlas (2020), index: 'Yandang = Yendang'.", srcs=["ATLAS"]))

RELATIONS = [
    dict(frm="@ethnic_groups:yandang", type="speaks", to="yendang", source="ATLAS", evidence="multiple_sources", level="well_documented",
         notes="Atlas index 'Yandang = Yendang'; Glottolog names the language Yendang and its group Mumuye-Yandang."),
]
GAPS = [
    ("Dampar: Glottocode", "No Glottolog entry under Dampar (Atlas: Jukunoid, Kororofa cluster, Dampar in Wukari LGA)."),
    ("Joole and Jaule", "Glottolog has no Joole; its nearest name is Jaule, a dialect of Dza (jaul1239) in the Jen group. The Atlas treats Joole as a separate Jen language. Not matched without a source that equates them."),
    ("Kulung (Chadic): Glottocode", "No Glottolog entry; the Atlas relates it to Piya (Glottolog Piya-Kwonci, piya1245). Glottolog's Kulung (Nigeria) (kulu1255) is the Jarawan Kulung, a separate record here."),
    ("Dirim: Glottocode", "Glottolog has only the Dirim-Nnakenyare group (diri1260) in Dakoid; the Atlas doubts Dirim is separate from Samba Daka."),
    ("Hausa and Fulani: language links", "No national Hausa or Fulfulde (Fula) language record exists yet; the 'speaks' links wait for them."),
    ("Other names without an Atlas entry", "Ambo, Batu, Bete, Bukwen, Dong, Jibu, Kapya, Ligri, Mashi, Mumuye, Ndunda, Pangseng, Rang, Tep and Tha have no name fields in their Atlas entries; Akum, Kam, Lamja-Deŋsa-Tola, Limbum, Naki, Nyam and Dirim have only the people's own name (field 1.C), and there are no records for those peoples. Other sources needed."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Taraba QC follow-up: other names (Etulo, Jenjo), Southern Bantoid branch, Yendang language and the Yandang 'speaks' link.")


def report():
    L = ["# Research batch 043 — Taraba names and language classification", "",
         f"Researched {ACCESSED}, after the Phase 4 check (`NIGERIA_QC_TARABA.md`). Owner approved the plan, with Mambila kept as one record. Created in review; published only after approval.", "",
         "## What it adds", "",
         "- **5 other names:**",
         "  - Utur and Eturo for the Etulo language",
         "  - Èédzá and ídzà, the Jenjo people's own names",
         "  - Yandang for the new Yendang record",
         "  All come from Blench's Atlas and are typed by its key, as in batch 036.",
         "- **Southern Bantoid**, a new branch record (Glottolog `sout3152`), so that **Buru** can be placed. The Buru change is in fix_043.",
         "- **Yendang**, a new language record (Glottolog `yend1241`, ISO `ynq`), and the link **Yandang people speak Yendang**. This clears the QC note about the Yandang having no 'speaks' link.",
         "- **fix_043:**",
         "  - **Buru** → Southern Bantoid, with Glottocode `buru1326` and one explanatory sentence",
         "  - **Mambila**: Glottocode `mamb1312`. It is kept as one record, as you chose; a sentence explains Glottolog's split into Western and Donga Mambila.",
         "  - **Gbaya**: Glottocode `nort2775`, marked probable in its text",
         "  - Glottolog notes for **Dirim**, **Joole** and **Kulung (Chadic)**",
         "  - Taraba's status → *quality control in progress*", "",
         "## Why so few names", "",
         "The QC listed 26 languages and 8 peoples with no other names. On a hand reading of each Atlas entry:",
         "- most entries have **no name fields at all**",
         "- others give only the people's own name, and we hold no record for those peoples",
         "- the rest (Gbaya's Baya, Dza's Jenjo/Janjo/Jen, Joole's èèʒìì, Kulung (Chadic)'s Wurkum, and Etulo's Utur and Turumawa for the people) are already recorded",
         "The unresolved cases are listed as a gap below.", "",
         "## New records", "", "### Southern Bantoid", "", SBANTOID, "", "### Yendang", "", YENDANG, "",
         "## Names", "", "| Record | Name | Type | Atlas |", "|---|---|---|---|"]
    for n in NAMES:
        L.append(f"| {n['record'].replace('@', '')} | {n['name']} | {n['name_type']} | {n['usage_notes']} |")
    L += ["", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_043_taraba_names.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_043_taraba_names_REVIEW.md", "w").write(report())
    print(f"names={len(NAMES)} records={len(RECORDS)} relations={len(RELATIONS)} gaps={len(GAPS)}")
