"""
Research batch 087b — Kano (Phase 3): the peoples of Kano State and the LGAs where they live, as far as the sources
allow. Researched 2026-10-02. Pattern: batches 063b, 069b, 075b and 081b.

Sources:
  * Federal Government of Nigeria, state profile 'Kano' (nigeria.gov.ng/states/kano; copy in
    data/fg_kano_2026-10-02.html) — its 'Ethnic Profile' describes religion only and names no ethnic group; not cited.
  * Wikipedia, 'Kano State': 'Two major ethnic groups, the Hausa and Fulani, make up the majority of Kano State's
    population.'
  * Wikipedia's 44 Kano LGA articles (read 2 Oct 2026; Makoda and Rogo could not be fetched). Explicit statements
    only:
      - Hausa and Fulani the majority: Bebeji, Bunkure, Dawakin Tofa, Doguwa, Fagge, Garun Mallam, Gezawa, Gwarzo, Kabo.
      - Dawakin Kudu: '99% of its inhabitants are Hausa people'.
      - Fagge: 'There is also a significant Igbo population'.
      - Karaye: early inhabitants the Maguzawa; 'the second settlers in the area are Hausa, Fulani, Kanuri'.
      - Minjibir: founded in the early 18th century by the Fulani of the Yerimawa clan.
      - Sumaila: established as a Jobawa (Jobe-Fulani) settlement in the 1740s.
No new people records: every people named already has a record, except the Maguzawa (no record; gap) and the
Kurama (language only; no source names them as a Kano people).
"""
import json, sys
import batch_087_kano_languages as L87

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
LGA_ART = {  # lga slug: (Wikipedia title, note)
    "bebeji": ("Bebeji", "'the vast majority being members of the Hausa and Fulani'"),
    "bunkure": ("Bunkure", "'The Hausa and Fulani ethnic groups make up the majority'"),
    "dawakin-tofa": ("Dawakin Tofa", "'Hausa and Fulani ethnic divides being the two most notable tribes'"),
    "doguwa": ("Doguwa", "'the Hausa and Fulani tribes accounting for the great majority of the population'; many Fulani are cattle rearers"),
    "fagge": ("Fagge", "'Hausa and Fulani ethnic groups making up the bulk of the population. There is also a significant Igbo population.'"),
    "garum-mallam": ("Garun Mallam", "'Hausa and Fulani ethnic groups making up the majority of the population'"),
    "gezawa": ("Gezawa", "'Hausa and Fulani ethnic groups make up the majority of the local population'"),
    "gwarzo": ("Gwarzo", "'Hausa and Fulani ethnic groups making up the bulk of the population'"),
    "kabo": ("Kabo, Nigeria", "'Hausa and Fulani ethnic groups accounting for the great majority of the population'"),
    "dawakin-kudu": ("Dawakin Kudu", "'99% of its inhabitants are Hausa people'"),
    "karaye": ("Karaye", "early inhabitants the Maguzawa; 'the second settlers in the area are Hausa, Fulani, Kanuri, and other'"),
    "minjibir": ("Minjibir", "founded in the early 18th century by the Fulani of the Yerimawa clan"),
    "sumaila": ("Sumaila", "established as a Jobawa (Jobe-Fulani) settlement in the 1740s"),
}
SOURCES = {"WKAN": L87.SOURCES["WKAN"]}
for l, (t, n) in LGA_ART.items():
    SOURCES[f"W_{l}"] = WS(t, f"Kano LGA article: {n}.")
LINKS = {  # lga: [(people, detail)]
    "bebeji": [("hausa", None), ("fulani", None)], "bunkure": [("hausa", None), ("fulani", None)], "dawakin-tofa": [("hausa", None), ("fulani", None)],
    "doguwa": [("hausa", None), ("fulani", None)], "fagge": [("hausa", None), ("fulani", None), ("igbo", "a significant Igbo population")],
    "garum-mallam": [("hausa", None), ("fulani", None)], "gezawa": [("hausa", None), ("fulani", None)], "gwarzo": [("hausa", None), ("fulani", None)],
    "kabo": [("hausa", None), ("fulani", None)], "dawakin-kudu": [("hausa", "'99% of its inhabitants are Hausa people'")],
    "karaye": [("hausa", "settlers after the Maguzawa"), ("fulani", "settlers after the Maguzawa"), ("kanuri", "settlers after the Maguzawa")],
    "minjibir": [("fulani", "the town was founded in the early 18th century by the Fulani of the Yerimawa clan")],
    "sumaila": [("fulani", "established as a Jobawa (Jobe-Fulani) settlement in the 1740s")],
}
RELATIONS = [
    dict(frm="@ethnic_groups:hausa", type="present_in", to="@admin_units:state:kano", source="WKAN", evidence="single_reliable_source", level="well_documented",
         notes="Wikipedia (Kano State): 'Two major ethnic groups, the Hausa and Fulani, make up the majority'; Blench's Atlas names Kano among Hausa first-language areas."),
    dict(frm="@ethnic_groups:fulani", type="present_in", to="@admin_units:state:kano", source="WKAN", evidence="single_reliable_source", level="well_documented",
         notes="Wikipedia (Kano State): the Hausa and Fulani make up the majority of the population."),
    dict(frm="@ethnic_groups:igbo", type="present_in", to="@admin_units:state:kano", source="W_fagge", evidence="single_reliable_source", level="reported",
         notes="Wikipedia (Fagge): 'a significant Igbo population'. Presence only."),
    dict(frm="@ethnic_groups:kanuri", type="present_in", to="@admin_units:state:kano", source="W_karaye", evidence="single_reliable_source", level="reported",
         notes="Wikipedia (Karaye): among the settlers after the Maguzawa. Presence only."),
]
for l, ps in LINKS.items():
    for p, d in ps:
        RELATIONS.append(dict(frm=f"@ethnic_groups:{p}", type="present_in", to=f"@admin_units:lga:kano/{l}", source=f"W_{l}", evidence="single_reliable_source", level="reported",
                              settlement_status="unknown", notes=f"Wikipedia ({LGA_ART[l][0]}): " + (d or LGA_ART[l][1]) + ". Presence only; the nature of their presence is not established."))
GAPS = [
    ("Kano: peoples by LGA", "Wikipedia's LGA articles name peoples for only 13 of the 44 LGAs (Makoda and Rogo could not be read); the federal profile names none. The other 31 LGAs are presumably Hausa and Fulani, but no source read says so."),
    ("Kano: Maguzawa", "Wikipedia (Karaye) names the Maguzawa as the area's early inhabitants; there is no Maguzawa record."),
    ("Kano: Kurama", "Blench's Atlas places the Kurama language in Tudun Wada LGA, but no source read names the Kurama people in Kano."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kano (Phase 3): Hausa, Fulani, Igbo and Kanuri linked to Kano, with people–LGA links for 13 LGAs from Wikipedia's LGA articles.")


def report():
    lg = [r for r in RELATIONS if "lga:" in r["to"]]
    per = {}
    for r in lg:
        per.setdefault(r["to"].split("/")[-1], []).append(r["frm"].split(":")[-1].title())
    L = ["# Research batch 087b — Kano: the peoples", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "- **No new people records.** Every people the sources name already has a record. The Maguzawa are the exception and are listed as a gap.",
         "- **State links:**",
         "  - **Hausa and Fulani**: Wikipedia calls them the majority.",
         "  - **Igbo**: a significant population in Fagge.",
         "  - **Kanuri**: settlers at Karaye.",
         "  - The federal profile names no ethnic group for Kano.",
         f"- **{len(lg)} people–LGA links covering {len(per)} of the 44 LGAs**, all *reported*, each from Wikipedia's article on that LGA. Only explicit statements are used. The other 31 LGAs have none; this is listed as a gap.", "",
         "## People per LGA", "", "| LGA | Peoples | Wikipedia says |", "|---|---|---|"]
    for l, ps in per.items():
        L.append(f"| {LGA_ART[l][0]} | {', '.join(ps)} | {LGA_ART[l][1]} |")
    L += ["", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_087b_kano_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_087b_kano_peoples_REVIEW.md", "w").write(report())
    print(f"relations={len(RELATIONS)} people-LGA={sum(1 for r in RELATIONS if 'lga:' in r['to'])}")
