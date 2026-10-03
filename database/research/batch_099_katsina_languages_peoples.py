"""
Research batch 099 — Katsina (Phase 3, first batch): languages and peoples. Researched 2026-10-02.
Pattern: batches 087 and 087b (Kano), combined because Katsina, like Kano, has no minority language in the Atlas.

Languages: the column-split Atlas text was searched for 'Katsina' and the 34 Katsina LGA names (Katsina was created
from Kaduna State in 1987; no 'Kaduna State' entry names a present Katsina LGA). Only Hausa (first-language area; 'Katsina
– northern dialect of Hausa') and Fulfulde ('Katsina – dialect of Fulfulde'; Central dialect Kano–Katsina–Bauchi–Borno)
are placed in Katsina. Hausa was linked to Katsina in batch 087; Fulfulde gets its Katsina link here.
Peoples:
  * Federal Government of Nigeria, state profile 'Katsina' (data/fg_katsina_2026-10-02.html) — Tier 1: 'Katsina is a
    predominantly Hausa Fulani State. Most people speak only Hausa … a considerable number of nomadic cattle Fulanis'.
  * Wikipedia, 'Katsina State': Hausa the largest group, with minorities of Fulani and others.
  * Wikipedia's Katsina LGA articles (read 2 Oct 2026; Mashi not found), explicit statements only.
No new records. Maguzawa (Faskari: 'the pagan Maguzawa people dwelling in the rocks', ancestors of Danboka) have no
record — gap. The Atlas lists the Agalawa as a 'Hausa subgroup in Katsina State' — noted, not recorded.
"""
import json, sys
import batch_087_kano_languages as L87

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Katsina: Hausa (northern dialect 'Katsina'); Fulfulde (dialect 'Katsina'; Central dialect Kano–Katsina–Bauchi–Borno); 'Agalawa – Hausa subgroup in Katsina State'."),
    "FGKT": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Katsina State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/katsina/", verification_status="verified",
                 notes="'Ethnic Profile': 'Katsina is a predominantly Hausa Fulani State. Most people speak only Hausa … a considerable number of nomadic cattle Fulanis'. Created 23 September 1987. Read 2026-10-02 (copy in database/research/data/)."),
    "WKAT": WS("Katsina State", "'the Hausa people are the largest ethnic group in the state with minorities of Fulani and other groups'."),
}
ART = {  # lga: (Wikipedia title, quote)
    "danja": ("Danja, Nigeria", "'The predominant tribes … are the Hausas, Fulanis and some small number of Yorubas, Kanuris and Igbos'"),
    "daura": ("Daura", "'the spiritual home of the Hausa people'; one of the Hausa Bakwai"),
    "dutsin-ma": ("Dutsin-Ma", "'predominantly Hausa and Fulani by tribe'"),
    "faskari": ("Faskari", "'The predominant ethnic groups are the Hausa and Fulani. Other ethnic groups found in the area include Igbos, Yorubas, Nupes'"),
    "funtua": ("Funtua", "'The inhabitants are Hausa … other tribes such as Fulani, Yoruba, Igbo, Nupe, Bendel among others'"),
    "ingawa": ("Ingawa", "'predominantly Hausa and Fulani by tribe'"),
    "kankia": ("Kankia", "'the majority of the area's dwellers being members of the Hausa and the Fulani'"),
    "katsina": ("Katsina (city)", "'a largely Muslim population, mainly from the Hausa and Fulani ethnic groups'"),
    "kusada": ("Kusada", "'The major ethnic groups are Hausa and Fulani'"),
    "mani": ("Mani, Nigeria", "'of Hausa Fulani ethnicity'"),
    "dutsi": ("Dutsi, Nigeria", "'Hausa and Fulani ethnic groups making up the great bulk of the local population'"),
}
for l, (t, q) in ART.items():
    SOURCES[f"W_{l}"] = WS(t, f"Katsina LGA article: {q}.")
LINKS = {l: ["hausa", "fulani"] for l in ["dutsin-ma", "ingawa", "kankia", "katsina", "kusada", "mani", "dutsi"]}
LINKS.update({"danja": ["hausa", "fulani", "yoruba", "kanuri", "igbo"], "faskari": ["hausa", "fulani", "igbo", "yoruba", "nupe"],
              "funtua": ["hausa", "fulani", "yoruba", "igbo", "nupe"], "daura": ["hausa"]})
MINOR = {"yoruba", "kanuri", "igbo", "nupe"}
RELATIONS = [
    dict(frm="@ethnic_groups:hausa", type="present_in", to="@admin_units:state:katsina", source="FGKT", evidence="multiple_sources", level="well_documented",
         notes="Federal profile: 'predominantly Hausa Fulani … Most people speak only Hausa'; Wikipedia: the largest ethnic group."),
    dict(frm="@ethnic_groups:fulani", type="present_in", to="@admin_units:state:katsina", source="FGKT", evidence="multiple_sources", level="well_documented",
         notes="Federal profile: Hausa Fulani state, with many nomadic cattle Fulani; Wikipedia: a minority."),
    dict(frm="@languages:fulfulde", type="spoken_in", to="@admin_units:state:katsina", source="ATLAS", evidence="single_reliable_source", level="well_documented",
         notes="Blench's Atlas: 'Katsina – dialect of Fulfulde'; Central dialect Kano–Katsina–Bauchi–Borno. Wikipedia's Kankia and Dutsi articles also name Fulfulde."),
]
for p in sorted(MINOR):
    lgas = [l for l, ps in LINKS.items() if p in ps]
    RELATIONS.append(dict(frm=f"@ethnic_groups:{p}", type="present_in", to="@admin_units:state:katsina", source=f"W_{lgas[0]}", evidence="single_reliable_source", level="reported",
                          notes=f"Named among smaller groups in Wikipedia's articles on {', '.join(ART[l][0] for l in lgas)}. Presence only."))
for l, ps in LINKS.items():
    for p in ps:
        RELATIONS.append(dict(frm=f"@ethnic_groups:{p}", type="present_in", to=f"@admin_units:lga:katsina/{l}", source=f"W_{l}", evidence="single_reliable_source", level="reported",
                              settlement_status="unknown", notes=f"Wikipedia ({ART[l][0]}): {ART[l][1]}. Presence only; the nature of their presence is not established."))
GAPS = [
    ("Katsina: peoples by LGA", "Wikipedia's LGA articles name peoples for 11 of the 34 LGAs (Mashi could not be read); the federal profile names none by LGA. The rest are presumably Hausa and Fulani, but no source read says so."),
    ("Katsina: Maguzawa and Agalawa", "Wikipedia (Faskari) names the Maguzawa ('dwelling in the rocks'); the Atlas names the Agalawa as a Hausa subgroup in Katsina. Neither has a record."),
    ("Katsina: minority languages", "No language other than Hausa and Fulfulde is placed in Katsina by the Atlas."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Katsina (Phase 3): Fulfulde linked to Katsina; Hausa, Fulani and smaller groups linked, with people–LGA links for 11 LGAs.")


def report():
    lg = [r for r in RELATIONS if "lga:" in r["to"]]
    L = ["# Research batch 099 — Katsina: languages and peoples", "",
         f"Researched {ACCESSED}. Phase 3, the first Katsina batch. Created in review; published only after your approval.", "",
         "- **Languages:** like Kano, Katsina has no minority language in the Atlas. Hausa (its northern 'Katsina' dialect) was linked in batch 087; **Fulfulde** gets its Katsina link here (the Atlas names a 'Katsina' dialect).",
         "- **Peoples:** no new records.",
         "  - **Hausa and Fulani** are linked to the state (the federal profile: 'a predominantly Hausa Fulani State').",
         "  - **Yoruba, Igbo, Kanuri and Nupe** are *reported*. Wikipedia names them as smaller groups at Danja, Faskari and Funtua.",
         f"- **{len(lg)} people–LGA links covering {len(LINKS)} of the 34 LGAs**, all *reported*, each from Wikipedia's article on that LGA. Daura is linked as 'the spiritual home of the Hausa people'.",
         "- **Not recorded:** the Maguzawa (Faskari) and the Agalawa (a Hausa subgroup, per the Atlas). Both are listed as gaps.", "",
         "## People per LGA", "", "| LGA | Peoples | Wikipedia says |", "|---|---|---|"]
    for l, ps in LINKS.items():
        L.append(f"| {ART[l][0]} | {', '.join(p.title() for p in ps)} | {ART[l][1]} |")
    L += ["", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_099_katsina_languages_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_099_katsina_languages_peoples_REVIEW.md", "w").write(report())
    print(f"relations={len(RELATIONS)} people-LGA={sum(1 for r in RELATIONS if 'lga:' in r['to'])}")
