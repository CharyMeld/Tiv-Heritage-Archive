"""
Research batch 117 — Ogun (Phase 3, first batch): languages and peoples. Researched 2026-10-02. Pattern: batch 111 (Lagos).

Languages: the column-split Atlas text was searched for 'Ogun' and the Ogun LGA and town names. Only Yoruba No. 489
names Ogun ('Most of … Ogun … States'; the Ketu dialect in 'border areas of Kwara and Ogun States'); Yoruba is already
linked to Ogun (relation from batch 051). Wikipedia's 'Ogun State' table of languages by LGA (cited to Ethnologue, 22nd
edition) lists Gun in Imeko Afon and Ipokia → Gun (batch 111) linked to Ogun and to those two LGAs, reported.
Peoples:
  * Federal Government of Nigeria, state profile 'Ogun' (data/fg_ogun_2026-10-02.html), 'Ethnic Profile': 'made up of six
    ethnic groups: the Egba, the Ijebu, the Remo, the Egbado, the Awori and the Egun. The language of the majority …
    is Yoruba'.
  * Wikipedia, 'Ogun State': the Yoruba are the largest group (Awori, Egba, Ijebu and Yewa sub-groups; smaller Ketu,
    Ohori, Ilaje, Ikale and Anago), with 'indigenous Egun people along the border with Benin'. Senatorial districts:
    Ogun Central, mostly Egba (Abeokuta North, Abeokuta South, Ewekoro, Ifo, Obafemi Owode, Odeda); Ogun East, mostly
    Ijebu and Remo (Ijebu East, Ijebu North, Ijebu North East, Ijebu Ode, Ikenne, Odogbolu, Ogun Waterside, Remo North,
    Sagamu); Ogun West, mostly Yewa (Ado-Odo/Ota, Imeko Afon, Ipokia, Yewa North, Yewa South). Its LGA table names the
    sub-groups per LGA (below).
  * Wikipedia, 'Egba people' (Ogun Central's six LGAs), 'Yewa' (the Egbado renamed themselves Yewa in 1995; mainly Yewa
    South, Yewa North, Imeko-Afon and Ipokia; Ado-Odo/Ota the Awori part), 'Ketu people' (Ketu towns in Imeko Afon and
    Yewa North), 'Gun people' (Ipokia and Yewa).
Every Ogun LGA gets a Yoruba link with its sub-groups in the notes (as for Lagos). The LGAs the archive names
'Egbado North' and 'Egbado South' are Yewa North and Yewa South in both sources; their renaming is for batch 121.
"""
import json, sys
import batch_087_kano_languages as L87
import batch_111_lagos_languages_peoples as P111

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=f"{n} Accessed {ACCESSED}.")
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Ogun entries read: Yoruba (No. 489: 'Most of … Ogun … States'; Ketu in border areas of Kwara and Ogun). No other language is placed in Ogun."),
    "FGOG": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Ogun State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/ogun/", verification_status="verified",
                 notes="'Ethnic Profile': 'Ogun State is made up of six ethnic groups: the Egba, the Ijebu, the Remo, the Egbado, the Awori and the Egun. The language of the majority of the people of Ogun State is Yoruba but this is however broken into scores of dialects.' Capital Abeokuta; created 3 February 1976. Read 2026-10-02 (copy in database/research/data/)."),
    "WOGS": WS("Ogun State", "Yoruba the largest group (Awori, Egba, Ijebu, Yewa; smaller Ketu, Ohori, Ilaje, Ikale, Anago); indigenous Egun along the Benin border; senatorial districts with their LGAs and peoples; table of languages by LGA (cited to Ethnologue, 22nd edition)."),
    "WEGB": WS("Egba people", "Yoruba subgroup, mostly from Ogun Central Senatorial District: Abeokuta North, Abeokuta South, Ewekoro, Ifo, Obafemi Owode and Odeda."),
    "WYEW": WS("Yewa", "The Egbado, now Yewa, a Yoruba subgroup of Ogun West; renamed after the Yewa River in 1995; mainly Yewa South, Yewa North, Imeko-Afon and Ipokia, with Ado-Odo/Ota the Awori part of the district."),
    "WKET": WS("Ketu people", "Yoruba subgroup and historical kingdom straddling Benin and Nigeria; Ketu towns in Imeko Afon (Iwoye Ketu, Ijoun, Ijale Ketu) and Yewa North (Ijaka, Igan Alade)."),
    "WGUNP": dict(P111.SOURCES["WGUNP"], notes="Reused. The Gun (Ogu, Egun) live 'in the Yewa, in Ipokia region of Ogun State'."),
}
SUBS = {  # lga: (sub-groups as Wikipedia's LGA table gives them, district)
 "abeokuta-north": ("Egba and Yewa", "Central"), "abeokuta-south": ("Egba", "Central"), "ewekoro": ("Egba", "Central"), "ifo": ("Egba", "Central"),
 "obafemi-owode": ("Egba", "Central"), "odeda": ("Egba and Oyo", "Central"),
 "ijebu-east": ("Ijebu", "East"), "ijebu-north": ("Ijebu", "East"), "ijebu-north-east": ("Ijebu", "East"), "ijebu-ode": ("Ijebu", "East"),
 "ikenne": ("Remo and Ijebu", "East"), "odogbolu": ("Ijebu", "East"), "ogun-waterside": ("Ijebu, Ikale and Ilaje", "East"),
 "remo-north": ("Remo and Ijebu", "East"), "shagamu": ("Remo and Ijebu", "East"),
 "ado-odo-ota": ("Awori", "West"), "imeko-afon": ("Ketu, Ohori and Yewa", "West"), "ipokia": ("Anago, Awori (Eyo) and Yewa", "West"),
 "egbado-north": ("Ketu, Ohori and Yewa", "West"), "egbado-south": ("Ketu, Ohori and Yewa", "West"),
}
DISTRICT = {"Central": "Ogun Central, which Wikipedia says consists mostly of the Egba", "East": "Ogun East, which Wikipedia says consists mostly of the Ijebu and the Remo",
            "West": "Ogun West, which Wikipedia says consists mostly of the Yewa (formerly Egbado)"}
EXTRA = {"egbado-north": " Both sources call this LGA Yewa North.", "egbado-south": " Both sources call this LGA Yewa South.",
         "imeko-afon": " Wikipedia (Ketu people) names Ketu towns here: Iwoye Ketu, Ijoun and Ijale Ketu."}
PO = " Presence only; the nature of their presence is not established."
RELATIONS = [
    dict(frm="@ethnic_groups:yoruba", type="present_in", to="@admin_units:state:ogun", source="FGOG", evidence="multiple_sources", level="well_documented",
         notes="Federal profile: six groups — Egba, Ijebu, Remo, Egbado, Awori and Egun — with Yoruba the language of the majority; Wikipedia: the Yoruba are the largest group (Awori, Egba, Ijebu and Yewa; smaller Ketu, Ohori, Ilaje, Ikale and Anago)."),
    dict(frm="@ethnic_groups:ogu", type="present_in", to="@admin_units:state:ogun", source="FGOG", evidence="multiple_sources", level="well_documented",
         notes="Federal profile: the Egun are one of the state's six ethnic groups; Wikipedia: 'indigenous Egun people along the border with Benin'; Gun people: 'in the Yewa, in Ipokia region of Ogun State'."),
    dict(frm="@ethnic_groups:ogu", type="present_in", to="@admin_units:lga:ogun/ipokia", source="WGUNP", evidence="multiple_sources", level="well_documented", settlement_status="unknown",
         notes=f"Wikipedia (Gun people): 'in Ipokia region of Ogun State'; Wikipedia's Ogun table lists Gun for Ipokia.{PO}"),
    dict(frm="@ethnic_groups:ogu", type="present_in", to="@admin_units:lga:ogun/imeko-afon", source="WOGS", evidence="single_reliable_source", level="reported", settlement_status="unknown",
         notes=f"Wikipedia's Ogun table of languages by LGA (from Ethnologue) lists Gun for Imeko Afon.{PO}"),
    dict(frm="@languages:gun", type="spoken_in", to="@admin_units:state:ogun", source="WOGS", evidence="multiple_sources", level="reported",
         notes="Wikipedia's Ogun table (from Ethnologue): Gun in Imeko Afon and Ipokia; Wikipedia (Gun people): the Gun of Ipokia and Yewa speak Gun. The Atlas places Gun only in Lagos (Badagry)."),
    dict(frm="@languages:gun", type="spoken_in", to="@admin_units:lga:ogun/ipokia", source="WOGS", evidence="multiple_sources", level="reported", notes="Wikipedia's Ogun table (from Ethnologue); Wikipedia (Gun people)."),
    dict(frm="@languages:gun", type="spoken_in", to="@admin_units:lga:ogun/imeko-afon", source="WOGS", evidence="single_reliable_source", level="reported", notes="Wikipedia's Ogun table (from Ethnologue)."),
]
for l, (subs, d) in SUBS.items():
    src = {"Central": "WEGB", "West": "WYEW"}.get(d, "WOGS")
    if l == "ado-odo-ota":
        src = "WYEW"
    RELATIONS.append(dict(frm="@ethnic_groups:yoruba", type="present_in", to=f"@admin_units:lga:ogun/{l}", source=src, evidence="multiple_sources", level="well_documented", settlement_status="unknown",
                          notes=f"Wikipedia's Ogun table of languages by LGA gives Yoruba, with the {subs} sub-groups; the LGA is in {DISTRICT[d]}.{EXTRA.get(l, '')}{PO}"))
GAPS = [
    ("Ogun: Yoruba sub-groups", "The Egba, Ijebu, Remo, Yewa (formerly Egbado), Awori, Ketu, Ohori, Anago, Ikale, Ilaje and Oyo are recorded in the Yoruba link notes, not as separate peoples (as in Lagos). Whether Yoruba sub-groups get their own records is a decision for the whole south-west."),
    ("Ogun: non-indigenous groups", "Wikipedia notes 'ethnic minorities of non-indigenous groups in urban areas' without naming them; none is linked."),
    ("Ogun: Yewa North and Yewa South", "The archive's LGA records are named 'Egbado North' and 'Egbado South'; the federal profile and Wikipedia use Yewa North and Yewa South. To be settled in the LGA-profiles batch, keeping both names."),
    ("Ogun: Gun in the Atlas", "Blench's Atlas places the Gbe cluster only in Lagos (Badagry); the Ogun links rest on Wikipedia and its Ethnologue-based table."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Ogun (Phase 3): Yoruba linked to all 20 LGAs with sub-groups in the notes; the Ogu (Egun) and Gun linked to Ogun, Ipokia and Imeko Afon.")


def report():
    lg = [r for r in RELATIONS if "lga:" in r["to"] and r["type"] == "present_in"]
    L = ["# Research batch 117 — Ogun: languages and peoples", "",
         f"Researched {ACCESSED}. Phase 3, the first Ogun batch. Created in review; published only after your approval.", "",
         "## Summary", "",
         "- **Languages:** the Atlas places only Yoruba in Ogun, and Yoruba is already linked. **Gun** (batch 111) is linked to Ogun, Ipokia and Imeko Afon. These links are *reported*, from Wikipedia's Ethnologue-based table.",
         "- **Peoples:** no new records. The federal profile names six groups: the Egba, Ijebu, Remo, Egbado (now Yewa), Awori and Egun. Five are Yoruba sub-groups; the Egun is the **Ogu** record from Lagos.",
         f"- **{len(lg)} people–LGA links covering all 20 LGAs.** Each LGA gets a Yoruba link that names its sub-groups (all *well documented*), via the three senatorial districts:",
         "  - **Ogun Central, mostly Egba:** Abeokuta North (with Yewa), Abeokuta South, Ewekoro, Ifo, Obafemi Owode, Odeda (with Oyo)",
         "  - **Ogun East, Ijebu and Remo:** the four Ijebu LGAs, Odogbolu, Ogun Waterside (with Ikale and Ilaje), Ikenne, Remo North, Shagamu",
         "  - **Ogun West, Yewa (formerly Egbado):** Yewa North, Yewa South and Imeko Afon (with Ketu and Ohori), Ipokia (with Anago and Awori), and Ado-Odo/Ota (Awori)",
         "  - **Ogu:** Ipokia (*well documented*) and Imeko Afon (*reported*)",
         "- **Names:** the notes write 'Yewa (formerly Egbado)', keeping both names. The archive still calls two LGAs 'Egbado North/South'; both sources call them Yewa North/South. Renaming is left to the LGA-profiles batch.",
         "- **Sub-groups:** kept in the link notes, as for Lagos.", "",
         "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_117_ogun_languages_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_117_ogun_languages_peoples_REVIEW.md", "w").write(report())
    lg = [r for r in RELATIONS if "lga:" in r["to"] and r["type"] == "present_in"]
    print(f"relations={len(RELATIONS)} people-LGA={len(lg)} LGAs={len({r['to'] for r in lg})}")
