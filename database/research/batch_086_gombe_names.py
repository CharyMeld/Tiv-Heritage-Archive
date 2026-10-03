"""
Research batch 086 — Gombe (Phase 4 follow-up): names and progress. Researched 2026-10-02, after the Gombe QC
(NIGERIA_QC_GOMBE.md).

Names only from sources already read:
  * Terawa — the federal government's Gombe profile names 'Terawa' among the state's ethnic groups -> alternative
    name on the existing Tera people record (which had none).
fix_086: Gombe research progress -> 11/11 LGAs researched, quality control in progress.
"""
import json, sys
import batch_081b_gombe_peoples as P81

SOURCES = {"FGGO": P81.SOURCES["FGGO"]}
NAMES = [
    dict(record="@ethnic_groups:tera", name="Terawa", name_type="alternative", usage_notes="Federal Government state profile (Gombe): 'Tangale, Terawa, Waja, …' — the Hausa plural form.", srcs=["FGGO"]),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=NAMES, gaps=[], updates=[], relations=[], statistics=[],
                scope="Gombe QC follow-up: 'Terawa' as another name of the Tera.")


def report():
    return "\n".join(["# Research batch 086 — Gombe names and progress", "",
        "Researched 2026-10-02, after the Gombe Phase 4 QC (`NIGERIA_QC_GOMBE.md`). Published only after your approval.", "",
        "## What it adds", "",
        "- **Terawa**, recorded as another name of the **Tera**, from the federal government's Gombe profile. The Tera record had no other names.",
        "- **fix_086:** Gombe's research progress (row #52) changes from \"not started, 0/11\" to **11/11 LGAs researched, quality control in progress**, with a summary of batches 081–086.",
        "- **Not changed:**",
        "  - Fulani and Hausa have no other names; this is a national gap.",
        "  - Cen Tuum has no family, which is correct because it is a language isolate.",
        "  - Jan Awei has no other names; the Atlas gives none."])


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_086_gombe_names.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_086_gombe_names_REVIEW.md", "w").write(report())
    print(f"names={len(NAMES)}")
