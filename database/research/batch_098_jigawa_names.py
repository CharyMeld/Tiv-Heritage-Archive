"""
Research batch 098 — Jigawa (Phase 4 follow-up): names and progress. Researched 2026-10-02, after the Jigawa QC
(NIGERIA_QC_JIGAWA.md).

Names only from sources already read — the federal government's Jigawa profile ('the Fulani, Mangawa, Ngizimawa and
Badawa still maintain their culture and tradition'):
  * Manga — Mangawa (Hausa plural); Wikipedia (Birniwa) also writes 'Kanuri (Mangawa)'.
  * Ngizim — Ngizimawa.
  * Bade — Badawa, with a usage note: Blench's Atlas gives Badawa/Mbadawa as names of the Mbat (Bauchi, batch 075b).
The three records had no other names; the QC flags this.
fix_098: Jigawa research progress -> 27/27 LGAs researched, quality control in progress.
"""
import json, sys
import batch_093b_jigawa_peoples as P93

SOURCES = {"FGJI": P93.SOURCES["FGJI"], "W_biriniwa": P93.SOURCES["W_biriniwa"]}
NAMES = [
    dict(record="@ethnic_groups:manga", name="Mangawa", name_type="exonym", usage_notes="Hausa plural, in the federal government's Jigawa profile; Wikipedia (Birniwa) writes 'Kanuri (Mangawa)'.", srcs=["FGJI", "W_biriniwa"]),
    dict(record="@ethnic_groups:ngizim", name="Ngizimawa", name_type="exonym", usage_notes="Hausa plural, in the federal government's Jigawa profile.", srcs=["FGJI"]),
    dict(record="@ethnic_groups:bade", name="Badawa", name_type="exonym",
         usage_notes="Hausa plural, in the federal government's Jigawa profile. Not to be confused with the Badawa/Mbadawa of Bauchi, whom Blench's Atlas identifies as the Mbat.", srcs=["FGJI"]),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=NAMES, gaps=[], updates=[], relations=[], statistics=[],
                scope="Jigawa QC follow-up: Mangawa, Ngizimawa and Badawa as other names of the Manga, Ngizim and Bade.")


def report():
    return "\n".join(["# Research batch 098 — Jigawa names and progress", "",
        "Researched 2026-10-02, after the Jigawa Phase 4 QC (`NIGERIA_QC_JIGAWA.md`). Published only after your approval.", "",
        "## What it adds", "",
        "- **3 other names**, all from the federal government's Jigawa profile:",
        "  - **Mangawa** for the Manga",
        "  - **Ngizimawa** for the Ngizim",
        "  - **Badawa** for the Bade, with a note that in Bauchi the Atlas uses Badawa for the Mbat",
        "- **fix_098:** Jigawa's research progress (row #54) changes from \"not started, 0/27\" to **27/27 LGAs researched, quality control in progress**, with a summary of batches 093–098."])


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_098_jigawa_names.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_098_jigawa_names_REVIEW.md", "w").write(report())
    print(f"names={len(NAMES)}")
