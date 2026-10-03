"""
Research batch 062 — Adamawa (Phase 4 follow-up): classification, names and progress. Researched 2026-10-01,
after the Adamawa QC (NIGERIA_QC_ADAMAWA.md). Owner approved.

  * Atlantic (branch, new record) so that Fulfulde can be placed: the Atlas classes Fulfulde as 'Atlantic
    (Northern branch, Senegal group)'. Glottolog does not recognise Atlantic as one group: it places the Fula
    family (fula1264) in North-Central Atlantic (nort3146) > Fula-Sereer (peul1234), directly under
    Atlantic-Congo. The archive follows the Atlas's name, as for its other branches; no Glottocode recorded.
    The Fulfulde change is in fix_062.
  * Margi ti ntəm (Margi South): the Atlas index, 'Margi ti ntәm = Margi South' — recorded on the language,
    as batch 043 did for 'Yandang = Yendang'.
  * Not recorded: Lamjavu, Deŋsavu, Tolavu (Atlas field 1.C, the people's own names) — the archive has no
    Lamja, Deŋsa or Tola people record (rule of batch 043). Ngwaba's 'Gombi' and 'Goba' (2.C) are already on
    the Ngwaba people record (batch 057b).
fix_062: Fulfulde -> Atlantic; Adamawa research progress -> 21/21 LGAs researched, quality control in progress.
"""
import json, sys

G = "https://glottolog.org/resource/languoid/id/"
SOURCES = {
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified", notes="Reused."),
    "GFULA": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Fula (fula1264)", organisation="Glottolog",
                  url=G + "fula1264", verification_status="verified",
                  notes="Fula family: Atlantic-Congo (atla1278) > North-Central Atlantic (nort3146) > Fula-Sereer (peul1234) > Fula. Consulted 2026-10-01."),
}
ATLANTIC = """Atlantic is the name Roger Blench's Atlas of Nigerian Languages (2020) gives to the group of Niger-Congo languages to which it assigns Fulfulde, classing Fulfulde in its Northern branch, Senegal group. Fulfulde, the language of the Fulɓe (Fulani), is spoken throughout Nigeria and in other countries of West and Central Africa, according to the Atlas. Glottolog does not treat Atlantic as a single group: it places the Fula languages (fula1264) in North-Central Atlantic (nort3146), under Fula-Sereer (peul1234), directly within Atlantic-Congo. The archive follows the Atlas's name, as it does for its other branches, and records no Glottocode for the branch."""
RECORDS = [
    dict(key="atlantic", table="languages", evidence="multiple_sources", level="well_documented",
         fields=dict(lang_type="branch", name="Atlantic", slug="atlantic",
                     summary="Atlantic is the Niger-Congo group to which Blench's Atlas assigns Fulfulde (Northern branch, Senegal group).", description=ATLANTIC),
         srcs=[("ATLAS", "Fulfulde: 'Atlantic (Northern branch, Senegal group)'"), ("GFULA", "Fula: North-Central Atlantic > Fula-Sereer")]),
]
NAMES = [dict(record="@languages:margi-south", name="Margi ti ntəm", name_type="alternative", usage_notes="Blench's Atlas (2020), index: 'Margi ti ntәm = Margi South'.", srcs=["ATLAS"])]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=[], updates=[], relations=[], statistics=[],
                scope="Adamawa QC follow-up: Atlantic branch (for Fulfulde), one other name (Margi South).")


def report():
    return "\n".join(["# Research batch 062 — Adamawa: classification, names and progress", "",
        "Researched 2026-10-01, after the Adamawa Phase 4 QC (`NIGERIA_QC_ADAMAWA.md`). Approved by the owner; published only after \"deploy\".", "",
        "## What it adds", "",
        "- **Atlantic**, a new branch record, so that **Fulfulde** can be placed. The move itself is in fix_062.",
        "  - The Atlas classes Fulfulde as Atlantic (Northern branch, Senegal group).",
        "  - Glottolog does not recognise 'Atlantic' as one group. It puts Fula in North-Central Atlantic (`nort3146`) → Fula-Sereer (`peul1234`).",
        "  - The branch uses the Atlas's name, as the archive's other branches do, and has no Glottocode. Glottolog's view is stated in its text.",
        "- **One other name:** **Margi ti ntəm** for Margi South, from the Atlas index.",
        "- **Correction to the QC proposal:** Lamjavu, Deŋsavu and Tolavu are the people's own names (Atlas field 1.C). The archive records those only on a people record, and it has none for the Lamja, Deŋsa or Tola, so they are not recorded. Ngwaba's 'Gombi' and 'Goba' are already on the Ngwaba people record.",
        "- **fix_062:**",
        "  - Fulfulde is placed under Atlantic, and a sentence on its classification is added.",
        "  - Adamawa's research progress changes from \"not started, 0/21\" to **21/21 LGAs researched, quality control in progress**.", "",
        "## Atlantic (text)", "", f"> {ATLANTIC}"])


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_062_adamawa_names.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_062_adamawa_names_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} names={len(NAMES)}")
