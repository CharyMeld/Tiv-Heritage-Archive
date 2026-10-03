"""
Research batch 076b — Bauchi: both names for the Zaar. Researched 2026-10-01. Owner instruction: "the system requires
keeping both variations".

  * Zaar people record (batch 075b) gets 'Sayawa' as a recorded other name (exonym), with the usage note that the
    federal state profile and Wikipedia use it and that Blench's Atlas notes the Saya terms are now considered derogatory.
  * fix_076b: the Bauchi State description's quoted state-government list reads 'Sayawa (Zaar)'.
"""
import json, sys
import batch_075b_bauchi_peoples as P75

SOURCES = {"FGBA": P75.SOURCES["FGBA"], "ATLAS": P75.SOURCES["ATLAS"]}
NAMES = [dict(record="@ethnic_groups:zaar", name="Sayawa", name_type="exonym",
              usage_notes="Used by the federal government's state profile and by Wikipedia; Blench's Atlas (2020, Zaar entry) notes that the Saya terms are now considered derogatory. The people's own name is Zaar.",
              srcs=["FGBA", "ATLAS"])]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=NAMES, gaps=[], updates=[], relations=[], statistics=[],
                scope="Bauchi: both names for the Zaar (Sayawa recorded as an exonym with its usage note).")


def report():
    return "\n".join(["# Research batch 076b — Bauchi: both names for the Zaar", "",
        "Researched 2026-10-01, on the owner's instruction that the system keeps both variations.", "",
        "- **Zaar people record:** 'Sayawa' is added as a recorded **exonym**. Its usage note says the federal profile and Wikipedia use it, and the Atlas says the Saya terms are now considered derogatory.",
        "- **fix_076b, Bauchi State page:** the quoted state-government list changes from 'Sayawa' to **'Sayawa (Zaar)'**."])


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_076b_bauchi_names.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_076b_bauchi_names_REVIEW.md", "w").write(report())
    print(f"names={len(NAMES)}")
