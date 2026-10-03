"""
Research batch 080 — Bauchi (Phase 4 follow-up): names and progress. Researched 2026-10-02, after the Bauchi QC
(NIGERIA_QC_BAUCHI.md). Owner approved.

People–LGA links, only from sources already read:
  * Giade — Fulani (Wikipedia, Giade: 'Dominated mainly by the Fulani tribe').
  * Itas/Gadau — Hausa, Fulani (Wikipedia, Itas/Gadau: 'The predominant ethnic groups in the area are the Hausa and Fulani').
  * Jama'are — Fulani, Kanuri (Wikipedia, Jama'are: 'members of the Fulani, Shirawa, Kanuri'; the Shirawa have no record).
  * Kirfi — Hausa (Wikipedia, Kirfi: 'The predominant ethnic group in the area is the Hausa').
  * Warji — Warji (Blench's Atlas, Warji entry: 'Ningi LGA, Warji district', the area now Warji LGA) — reported.
Classification: Glottocode vagh1247 for Vaghat–Ya–Bijim–Legeri (#356; Glottolog 'Kwangic', Tarokoid >
Bijimic-Sur-Shall; contains Vaghat vagh1250 and Bijim biji1246, with Legeri = Kaduk, a dialect of Bijim).
fix_080: Bauchi research progress -> 20/20 LGAs researched, quality control in progress.
"""
import json, sys
import batch_075_bauchi_languages as L75
import batch_079_bauchi_lga_profiles as P79

SOURCES = {
    "ATLAS": L75.SOURCES["ATLAS"],
    "GLIDX": L75.SOURCES["GLIDX"],
    "W_giade": P79.SOURCES["W_giade"], "W_itas-gadau": P79.SOURCES["W_itas-gadau"], "W_jama-are": P79.SOURCES["W_jama-are"], "W_kirfi": P79.SOURCES["W_kirfi"],
}
LINKS = [
    ("fulani", "giade", "W_giade", "Wikipedia (Giade): 'Dominated mainly by the Fulani tribe.'"),
    ("hausa", "itas-gadau", "W_itas-gadau", "Wikipedia (Itas/Gadau): 'The predominant ethnic groups in the area are the Hausa and Fulani.'"),
    ("fulani", "itas-gadau", "W_itas-gadau", "Wikipedia (Itas/Gadau): 'The predominant ethnic groups in the area are the Hausa and Fulani.'"),
    ("fulani", "jama-are", "W_jama-are", "Wikipedia (Jama'are): 'Most of the inhabitants of Jama'are are members of the Fulani, Shirawa, Kanuri, but Fulani is the most prominent tribe.'"),
    ("kanuri", "jama-are", "W_jama-are", "Wikipedia (Jama'are): 'Most of the inhabitants of Jama'are are members of the Fulani, Shirawa, Kanuri.'"),
    ("hausa", "kirfi", "W_kirfi", "Wikipedia (Kirfi): 'The predominant ethnic group in the area is the Hausa.'"),
    ("warji", "warji", "ATLAS", "Blench's Atlas (2020), Warji entry: 'Bauchi State, Darazo LGA, Ganjuwa district, and Ningi LGA, Warji district' — the Warji district of Ningi is now Warji LGA."),
]
RELATIONS = [dict(frm=f"@ethnic_groups:{p}", type="present_in", to=f"@admin_units:lga:bauchi/{l}", source=s, evidence="single_reliable_source",
                  level="reported" if p == "warji" else "well_documented", settlement_status="unknown", notes=n) for p, l, s, n in LINKS]
UPDATES = [dict(ref="@languages:vaghat-ya-bijim-legeri", fields=dict(glottocode="vagh1247"),
                srcs=[("GLIDX", "Glottocode vagh1247 (Kwangic: contains Vaghat vagh1250 and Bijim biji1246; Legeri = Kaduk, a dialect of Bijim)")])]
GAPS = [
    ("Bauchi: peoples of Katagum and Shira", "No source read names the peoples of these two LGAs; Wikipedia's Katagum article describes Katagum town, which is in Zaki LGA."),
    ("Bauchi: the Shirawa", "Named by Wikipedia among Jama'are's inhabitants; the Atlas calls Shirawa an extinct Chadic language of the Katagum region. No people record."),
    ("Bauchi: Damlanci classification", "First described in Blench (2019); no Glottolog entry."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS, updates=UPDATES,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Bauchi QC follow-up: 7 people–LGA links (Giade, Itas/Gadau, Jama'are, Kirfi, Warji); Glottocode vagh1247 for Vaghat–Ya–Bijim–Legeri.")


def report():
    L = ["# Research batch 080 — Bauchi names and progress", "",
         "Researched 2026-10-02, after the Bauchi Phase 4 QC (`NIGERIA_QC_BAUCHI.md`). Approved by the owner; published only after \"deploy\".", "",
         "## What it adds", "",
         "- **7 people–LGA links**, taken from sources already read:", ""]
    L += [f"  - **{l}: {p.capitalize()}**{' (*reported*)' if p == 'warji' else ''}. {n}" for p, l, s, n in LINKS]
    L += ["", "- **A Glottocode for Vaghat–Ya–Bijim–Legeri** (#356, a Plateau record): `vagh1247`, Glottolog's Kwangic group. It contains Vaghat (vagh1250) and Bijim (biji1246), and Legeri (Kaduk) is a dialect of Bijim. This is the same grouping as the Atlas cluster (No. 469). The batch fills an empty field.",
          "- **fix_080:** Bauchi's research progress (row #42) changes from \"not started, 0/20\" to **20/20 LGAs researched, quality control in progress**, with a summary of batches 075–080.",
          "- **Not linked:** Katagum and Shira, because no source read names their peoples; the Shirawa, who have no record; and Damlanci's Glottocode, which Glottolog lacks.", "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_080_bauchi_names.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_080_bauchi_names_REVIEW.md", "w").write(report())
    print(f"relations={len(RELATIONS)} updates={len(UPDATES)} sources={len(SOURCES)}")
