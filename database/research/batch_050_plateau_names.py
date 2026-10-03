"""
Research batch 050 — Plateau (Phase 4 follow-up): other names. Researched 2026-09-30, after the
Plateau QC (NIGERIA_QC_PLATEAU.md). Owner approved.

Names only from sources already read:
  * Glottolog's names where they differ from the Atlas head name (batch 044 matched these codes; the
    Glottolog name was given in the text but not recorded as a name): Eten (Aten), Amo (Map), Mindat
    (Mundat), Sya (Sha), Fyam (Pyam), Pye (Pe), Izora (Zora), Duguri (Doori).
  * The state government's spellings (2022 booklet, festival list): Taroh (Tarok people), Mwaaghvul
    (Mwaghavul people).
fix_050: Plateau research progress -> 17/17 LGAs researched, quality control in progress.
"""
import json, sys

SOURCES = {
    "GLIDX": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Glottolog language index (resourcemap)", organisation="Glottolog",
                  url="https://glottolog.org/resourcemap.json?rsc=language", verification_status="verified", notes="Reused."),
    "OSS": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Plateau State is open for business (investment booklet)",
                organisation="Plateau State Government (One-Stop Shop)", publication_date="2022-07",
                url="https://plateaustate.gov.ng/uploads/Investing-in-Plateau-State-OSS-booklet.pdf", verification_status="verified", notes="Reused."),
}
GLOTTO = [("aten", "Eten", "eten1239"), ("map", "Amo", "amoo1242"), ("mundat", "Mindat", "mund1334"), ("sha", "Sya", "shaa1247"),
          ("pyam", "Fyam", "fyam1238"), ("pe", "Pye", "peee1238"), ("zora", "Izora", "izor1238"), ("doori", "Duguri", "dugu1249")]
NAMES = [dict(record=f"@languages:{s}", name=n, name_type="alternative", usage_notes=f"Glottolog's name ({g}).", srcs=["GLIDX"]) for s, n, g in GLOTTO]
NAMES += [
    dict(record="@ethnic_groups:tarok", name="Taroh", name_type="spelling_variant", usage_notes="Plateau State Government (2022): 'Taroh Cultural Day ... Taroh ethnic group'.", srcs=["OSS"]),
    dict(record="@ethnic_groups:mwaghavul", name="Mwaaghvul", name_type="spelling_variant", usage_notes="Plateau State Government (2022): 'Mwaaghvul annual festival of arts and culture' (Puskaat).", srcs=["OSS"]),
]
GAPS = []


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=NAMES, gaps=GAPS, updates=[], relations=[], statistics=[],
                scope="Plateau QC follow-up: 8 Glottolog names for batch-044 languages; state-government spellings Taroh and Mwaaghvul.")


def report():
    L = ["# Research batch 050 — Plateau names and progress", "",
         "Researched 2026-09-30, after the Plateau Phase 4 QC (`NIGERIA_QC_PLATEAU.md`). Approved by the owner; published only after \"deploy\".", "",
         "## What it adds", "",
         f"- **{len(NAMES)} other names**, all from sources already read:",
         "  - **Glottolog's names** for 8 Plateau languages, where they differ from the Atlas. Batch 044 already gave these in the text but did not record them as names:",
         "    " + "; ".join(f"{n} ({s})" for s, n, g in GLOTTO) + ".",
         "  - **The state government's spellings**: Taroh (Tarok) and Mwaaghvul (Mwaghavul).",
         "- **fix_050:** Plateau's research progress changes from \"not started, 0/17\" to **17/17 LGAs researched, quality control in progress**, with a summary of batches 044–050.",
         "- **Not added:** Atlas names for the peoples. The Atlas's people fields (1.C and 2.C) for the Plateau peoples were already used in batch 044b, and the rest are language names, which stay on the language records."]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_050_plateau_names.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_050_plateau_names_REVIEW.md", "w").write(report())
    print(f"names={len(NAMES)}")
