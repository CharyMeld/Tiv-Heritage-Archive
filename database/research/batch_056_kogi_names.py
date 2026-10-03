"""
Research batch 056 — Kogi (Phase 4 follow-up): other names. Researched 2026-09-30, after the Kogi QC
(NIGERIA_QC_KOGI.md). Owner approved.

Names only from sources already read:
  * Anebira (Ebira people): Wikipedia, Kogi State ('Igala people, Anebira, and Okun').
  * Anufawa and Nyffe (Nupe people): Blench's Atlas, Nupe (Central), field 2.C — other names for the people.
fix_056: Kogi research progress -> 21/21 LGAs researched, quality control in progress.
"""
import json, sys

SOURCES = {
    "WKOGI": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Kogi State", organisation="Wikipedia",
                  url="https://en.wikipedia.org/wiki/Kogi_State", verification_status="needs_corroboration", notes="Reused."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified", notes="Reused."),
}
NAMES = [
    dict(record="@ethnic_groups:ebira", name="Anebira", name_type="alternative", usage_notes="Wikipedia (Kogi State): 'Igala people, Anebira, and Okun'.", srcs=["WKOGI"]),
    dict(record="@ethnic_groups:nupe", name="Anufawa", name_type="alternative", usage_notes="Blench's Atlas (2020), Nupe (Central), field 2.C: other name for the people.", srcs=["ATLAS"]),
    dict(record="@ethnic_groups:nupe", name="Nyffe", name_type="alternative", usage_notes="Blench's Atlas (2020), Nupe (Central), field 2.C: other name for the people.", srcs=["ATLAS"]),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=NAMES, gaps=[], updates=[], relations=[], statistics=[],
                scope="Kogi QC follow-up: 3 other names (Anebira; Anufawa, Nyffe).")


def report():
    return "\n".join(["# Research batch 056 — Kogi names and progress", "",
        "Researched 2026-09-30, after the Kogi Phase 4 QC (`NIGERIA_QC_KOGI.md`). Approved by the owner; published only after \"deploy\".", "",
        "## What it adds", "",
        "- **3 other names**, from sources already read:",
        "  - **Anebira** for the Ebira (Wikipedia, Kogi State).",
        "  - **Anufawa** and **Nyffe** for the Nupe (Blench's Atlas, the field for other names of the people).",
        "- **fix_056:** Kogi's research progress changes from \"not started, 0/21\" to **21/21 LGAs researched, quality control in progress**, with a summary of batches 051–056."])


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_056_kogi_names.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_056_kogi_names_REVIEW.md", "w").write(report())
    print(f"names={len(NAMES)}")
