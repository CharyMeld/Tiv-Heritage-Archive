"""
Research batch 092 — Kano (Phase 4 follow-up): names and progress. Researched 2026-10-02, after the Kano QC
(NIGERIA_QC_KANO.md).

Names only from sources already read (Blench's Atlas, field 1.C = the people's own name):
  * Hausa people — Hàusàawáa (plural), Bàháushèe (singular, masculine): Atlas No. 173 (Hausa), 1.C.
  * Fulani people — Fulɓe (plural), Pullo (singular): Atlas No. 134 (Fulfulde), 1.C; 2.C gives 'Fulani, Filani,
    Rumada' as other names for the people — Filani recorded as a spelling variant.
Both records had no other names; the QC flags this in every state.
fix_092: Kano research progress -> 44/44 LGAs researched, quality control in progress.
"""
import json, sys
import batch_087_kano_languages as L87

SOURCES = {"ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Hausa (No. 173) and Fulfulde (No. 134) entries, fields 1.C and 2.C.")}
NAMES = [
    dict(record="@ethnic_groups:hausa", name="Hàusàawáa", name_type="endonym", usage_notes="Blench's Atlas (2020), Hausa entry, field 1.C: the people's own name (plural); singular Bàháushèe (m.), Bàháushìyáa (f.).", srcs=["ATLAS"]),
    dict(record="@ethnic_groups:hausa", name="Bàháushèe", name_type="endonym", usage_notes="Blench's Atlas (2020), Hausa entry, field 1.C: singular (masculine).", srcs=["ATLAS"]),
    dict(record="@ethnic_groups:fulani", name="Fulɓe", name_type="endonym", usage_notes="Blench's Atlas (2020), Fulfulde entry, field 1.C: the people's own name (plural); singular Pullo.", srcs=["ATLAS"]),
    dict(record="@ethnic_groups:fulani", name="Pullo", name_type="endonym", usage_notes="Blench's Atlas (2020), Fulfulde entry, field 1.C: singular.", srcs=["ATLAS"]),
    dict(record="@ethnic_groups:fulani", name="Filani", name_type="spelling_variant", usage_notes="Blench's Atlas (2020), Fulfulde entry, field 2.C: other name for the people.", srcs=["ATLAS"]),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=NAMES, gaps=[], updates=[], relations=[], statistics=[],
                scope="Kano QC follow-up: the Hausa and Fulani peoples' own names (Atlas, field 1.C).")


def report():
    return "\n".join(["# Research batch 092 — Kano names and progress", "",
        "Researched 2026-10-02, after the Kano Phase 4 QC (`NIGERIA_QC_KANO.md`). Published only after your approval.", "",
        "## What it adds", "",
        "- **The Hausa's own name:** **Hàusàawáa** (singular Bàháushèe), from the Atlas, Hausa entry, field 1.C.",
        "- **The Fulani's own name:** **Fulɓe** (singular Pullo), from the Atlas, Fulfulde entry, field 1.C. **Filani** is added as another spelling (field 2.C).",
        "- Both records had no other names, which the QC flags in every state. These are national records, so the fix applies everywhere.",
        "- **fix_092:** Kano's research progress (row #56) changes from \"not started, 0/44\" to **44/44 LGAs researched, quality control in progress**, with a summary of batches 087–092.",
        "- **Not changed:** the 31 LGAs with no people linked, because no source read names their peoples."])


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_092_kano_names.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_092_kano_names_REVIEW.md", "w").write(report())
    print(f"names={len(NAMES)}")
