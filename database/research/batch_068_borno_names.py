"""
Research batch 068 — Borno (Phase 4 follow-up): names and progress. Researched 2026-10-01, after the Borno QC
(NIGERIA_QC_BORNO.md). Owner approved.

Names only from sources already read (Blench's Atlas, Kanuri–Kanembu cluster, *Kanuri):
  * Kànúrí — field 1.C, the people's own name -> endonym on the existing Kanuri people record.
  * Beriberi — field 2.C, other name for the people -> exonym. (Kamberi, also in 2.C, is not recorded: it is the
    usual name of a different people, the Kambari, and could mislead.)
fix_068: Borno research progress -> 27/27 LGAs researched, quality control in progress.
"""
import json, sys

SOURCES = {
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified", notes="Reused."),
}
NAMES = [
    dict(record="@ethnic_groups:kanuri", name="Kànúrí", name_type="endonym", usage_notes="Blench's Atlas (2020), Kanuri entry, field 1.C: the people's own name.", srcs=["ATLAS"]),
    dict(record="@ethnic_groups:kanuri", name="Beriberi", name_type="exonym", usage_notes="Blench's Atlas (2020), Kanuri entry, field 2.C: other name for the people.", srcs=["ATLAS"]),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=NAMES, gaps=[], updates=[], relations=[], statistics=[],
                scope="Borno QC follow-up: 2 other names for the Kanuri (Kànúrí, Beriberi).")


def report():
    return "\n".join(["# Research batch 068 — Borno names and progress", "",
        "Researched 2026-10-01, after the Borno Phase 4 QC (`NIGERIA_QC_BORNO.md`). Approved by the owner; published only after \"deploy\".", "",
        "## What it adds", "",
        "- **2 other names for the Kanuri**, from the Atlas's Kanuri entry:",
        "  - **Kànúrí**: their own name (field 1.C)",
        "  - **Beriberi**: an outside name (field 2.C)",
        "- **Not recorded:** 'Kamberi', also in field 2.C, because it is the usual name of a different people (the Kambari).",
        "- **fix_068:** Borno's research progress changes from \"not started, 0/27\" to **27/27 LGAs researched, quality control in progress**, with a summary of batches 063–068."])


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_068_borno_names.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_068_borno_names_REVIEW.md", "w").write(report())
    print(f"names={len(NAMES)}")
