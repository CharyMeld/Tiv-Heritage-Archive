"""
Reusable builder for INEC ward (registration area) batches — one state at a time (owner decision Q1).

Input: data/inec_<state>_ras.json, parsed from INEC's "Directory of Polling Units" for the state
(revised January 2015) with every LGA's ward count and polling-unit total checked against the
directory's own summary table. Each ward becomes an admin unit (type 'ward') under its LGA, with
INEC's code (LGA-RA, e.g. 05-17) and its 2015 polling-unit count as a statistic. Wards have no pages
of their own; they are listed on their LGA's page (see NigeriaController::children).

Usage: python3 inec_wards_batch.py <state slug> "<State name>" <output dir>
       e.g. python3 inec_wards_batch.py nasarawa "Nasarawa" /tmp
"""
import json, os, re, sys

HERE = os.path.dirname(os.path.abspath(__file__))


def clean(n):
    n = n.replace("‐", "-")
    n = re.sub(r"\s*-\s*", "-", n)
    return re.sub(r"\s+", " ", n).strip()


def slug(n):
    return re.sub(r"[^a-z0-9]+", "-", n.lower()).strip("-")


def build(state, state_name):
    lgas = json.load(open(os.path.join(HERE, "data", f"inec_{state}_ras.json")))
    url = f"https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_{state_name.replace(' ', '_')}.pdf"
    sources = {"INEC": dict(source_type="government_publication", source_kind="government_publication", source_tier=1,
                            title=f"Directory of Polling Units: {state_name} State (Revised January 2015)",
                            organisation="Independent National Electoral Commission (INEC)", publication_date="2015-01", url=url,
                            archive_reference="Internet Archive copy of the INEC PDF (no longer on the INEC site)", verification_status="verified",
                            notes=f"Lists {state_name} State's {len(lgas)} LGAs, {sum(v['nra'] for v in lgas.values())} registration areas (wards) with codes, and "
                                  f"{sum(v['npu'] for v in lgas.values()):,} polling units. INEC's disclaimer: not a legal or administrative document for boundary or political claims.")}
    units, stats = [], []
    for code, v in sorted(lgas.items()):
        lname = v.get("display") or v["name"].title().replace("Nassarawa Egon", "Nasarawa Eggon")
        for r in v["ras"]:
            name = clean(r["name"])
            key = f"w{code}{r['code']}"
            oc = f"{code}-{r['code']}"
            units.append(dict(key=key, unit_type="ward", parent=f"@admin_units:lga:{state}/{v['slug']}", name=name, slug=slug(name),
                              official_code=oc, status="current", evidence="single_reliable_source", level="verified",
                              summary=f"{name} is a ward (INEC registration area {oc}) of {lname} Local Government Area, {state_name} State, "
                                      f"listed in INEC's Directory of Polling Units (revised January 2015) with {r['pus']} polling units.",
                              srcs=[("INEC", f"Registration area {oc} of {lname}; {r['pus']} polling units")]))
            stats.append(dict(record=key, metric="other", value_low=r["pus"], reference_year=2015, method="other",
                              notes="Polling units (INEC Directory of Polling Units, revised January 2015)",
                              source="INEC", evidence="single_reliable_source", level="verified"))
    gaps = [(f"INEC wards of {state_name} after 2015", "The list is from INEC's 2015 directory; whether any registration area was renamed or split since needs INEC's current list."),
            (f"Ward boundaries and headquarters ({state_name})", "INEC lists wards and polling units only; boundaries and ward headquarters are not recorded.")]
    data = dict(sources=sources, units=units, places=[], changes=[], records=[], names=[], gaps=gaps, updates=[], relations=[], statistics=stats,
                scope=f"{state_name} (Phase 3): the {len(units)} INEC wards (registration areas) with codes and polling-unit counts.")
    L = [f"# INEC wards — {state_name} State", "",
         f"**Source:** INEC, *Directory of Polling Units: {state_name} State* (revised January 2015), Tier 1, via the Internet Archive. INEC has removed the PDF from its site.", "",
         f"- **{len(units)} wards** in {len(lgas)} LGAs, each with INEC's code (LGA-ward) and its number of polling units in 2015. Every LGA's ward count and polling-unit total matches the directory's summary table.",
         "- Wards are listed on their LGA's page, with INEC's disclaimer. They have no pages of their own and stay out of the sitemap, search index, AI lookups and home-page lists (as for Benue).", "",
         "| LGA | Wards | Polling units |", "|---|---|---|"]
    for code, v in sorted(lgas.items()):
        L.append(f"| {v.get('display') or v['name'].title()} | {v['nra']} | {v['npu']} |")
    L += ["", "Ward names per LGA (as INEC writes them):", ""]
    for code, v in sorted(lgas.items()):
        L.append(f"- **{v.get('display') or v['name'].title()}**: " + "; ".join(f"{clean(r['name'])} ({code}-{r['code']})" for r in v["ras"]))
    return data, "\n".join(L)


if __name__ == "__main__":
    state, name, out = sys.argv[1], sys.argv[2], sys.argv[3]
    data, review = build(state, name)
    json.dump(data, open(f"{out}/wards_{state}.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/wards_{state}_REVIEW.md", "w").write(review)
    print(f"wards={len(data['units'])} stats={len(data['statistics'])}")
