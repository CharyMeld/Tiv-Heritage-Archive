"""
Research batch 172 — Federal Capital Territory (Phase 3, first batch): languages and peoples. Researched 2026-10-07.
Patterns: batches 160 and 160b (Niger), 166/166c (Kaduna). The FCT is small, so languages and peoples are one batch.

Languages (Blench's Atlas, 2020; 9 entries name the FCT):
  new: Basa-Gurara (40.a; 2.A Basa-Kwali; 'Federal Capital Territory, Yaba and Kwali LGAs, along the Gurara river';
       Kainji: Western Kainji: Kamuku–Basa group); Koro Ija (256; 'Near Lambata'; one village; Plateau: Jili group);
       Koro Zuba (257; 'near Zuba'; one village; Plateau: Jili group; Glottolog koro1324).
  existing, linked to the FCT: Gbagyi, Gbari, Gwandara, Dibo, Gade, Nupe.
  Placement: Kwali from the Atlas; Yaba is a ward of Abaji and Zuba a ward of Gwagwalada in INEC's FCT directory
  (data/inec_fct.pdf), linked as reported; Lambata is not in INEC's FCT directory, so Koro Ija is linked to the FCT only.
Peoples (all records exist already):
  Federal Government profile 'Federal Capital Territory' (data/fg_fct_2026-10-07.html): 'The indigenous inhabitants of
  Abuja are the Gbagyi (Gwari) as the major language, Bassa, Gwandara, Gade, Ganagana, Koro'.
  Wikipedia: 'Abuja' (indigenous Abawa (Ganagana), Basa, Gwandara, Gbagyi; others Egburra, Nupe, Koro; Gwandara mostly in
  AMAC and Bwari); 'Abaji' (indigenous Bassa, Egbura, Gbagyi and Ganagana); 'Bwari' (original inhabitants Gbagyi; Koro
  district head; Hausa); 'Gwagwalada' (traditional headquarters of the Bassa in the FCT); 'Federal Capital Territory
  (Nigeria)' (the area was principally Gwari land).
  Ganagana = Dibo (Atlas, Dibo 2.C); Egbura is linked to the Ebira record as reported (Wikipedia writes Egbira/Egbura).
"""
import json, sys
import batch_087_kano_languages as L87
import batch_063_borno_languages as B63

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=f"{n} Accessed {ACCESSED}.")
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. FCT entries read in full: Basa-Gurara (40.a), Dibo, Gade, Gbagyi, Gbari, Gwandara, Koro Ija, Koro Zuba, Nupe–Nupe Tako."),
    "GLIDX": L87.SOURCES["GLIDX"],
    "INECF": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Directory of Polling Units: Federal Capital Territory (Revised January 2015)",
                  organisation="Independent National Electoral Commission (INEC)", publication_date="2015-01",
                  url="https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_FCT.pdf",
                  archive_reference="Internet Archive copy of the INEC PDF (no longer on the INEC site); copy in database/research/data/inec_fct.pdf",
                  verification_status="verified", notes="6 area councils, 62 wards, 562 polling units. Used here to place villages named in the Atlas (Yaba ward, Abaji; Zuba ward, Gwagwalada)."),
    "FGFCT": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Federal Capital Territory (state profile)",
                  organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/federal-capital-territory/", verification_status="verified",
                  notes="'Ethnic Profile': 'The indigenous inhabitants of Abuja are the Gbagyi (Gwari) as the major language, Bassa, Gwandara, Gade, Ganagana, Koro'. Created 3 February 1976; area councils Abaji, Abuja Municipal, Gwagwalada, Kuje, Bwari and Kwali. Read 2026-10-07 (copy in database/research/data/)."),
    "WABJ": WS("Abuja", "'The indigenous inhabitants of Abuja are the Abawa (Ganagana), Basa, Gwandara, Gbagyi (Gwari) having the majority population … Other groups in the area include Egburra, Nupe and Koro. The Gwandara speaking people … are mostly found in AMAC and Bwari Area Council.'"),
    "WABA": WS("Abaji", "'The indigenous people are Bassa people, Egbura, Gbagyi and Ganagana people'; the council created in 1986."),
    "WBWA": WS("Bwari", "'The original inhabitants of the town are the Gbagyi speaking people'; Musa Ijakoro, 'of Koro ethnic minority', turbaned District Head of Bwari in 1976; a 2017 clash between Hausa and Gbagyi communities over the chieftaincy."),
    "WGWA": WS("Gwagwalada", "'Gwagwalada serves as the traditional headquarters of the Bassa people in FCT, with their traditional leader known as the Agụma'."),
    "WFCT": WS("Federal Capital Territory (Nigeria)", "The area chosen for the capital was 'principally Gwari Land (the home of the tribes referred to as the Gbagyis …)'; about 120,000 residents in 840 villages before the takeover, many relocated to Suleja and New Karshi."),
}
S_, B, L, M = "spelling_variant", "endonym", "alternative", "alternative"
FIELD = dict(B63.FIELD, **{"1.C": "the speakers' name for themselves"})
FC = lambda l: f"@admin_units:other:federal-capital-territory/{l}"
FCT = "@admin_units:federal_capital_territory:federal-capital-territory"
TEXT = {
 "basa-gurara": "Basa-Gurara, also called Basa-Kwali, is a language of the Federal Capital Territory, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as Kainji (Western Kainji: Kamuku–Basa group) and groups it with Basa-Benue and Basa-Makurdi. The Atlas places it in 'Yaba and Kwali LGAs, along the Gurara river'; Yaba is a ward of Abaji Area Council in INEC's directory. No Glottolog code was matched.",
 "koro-ija": "Koro Ija is a language of the Federal Capital Territory, spoken in a single village near Lambata, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as Plateau (Jili group). Lambata is not listed in INEC's FCT directory, so its area council is not given. No Glottolog code was matched.",
 "koro-zuba": "Koro Zuba is a language of the Federal Capital Territory, spoken in a single village near Zuba, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as Plateau (Jili group). Zuba is a ward of Gwagwalada Area Council in INEC's directory. Glottolog lists it as Koro Zuba (koro1324).",
}
NEWL = [("basa-gurara", "Basa-Gurara", "@languages:kainji", None, [("Basa-Kwali", L, "2.A")], ["kwali"], [("abaji", "INEC's directory has a ward Yaba in Abaji Area Council; the Atlas places Basa-Gurara in 'Yaba and Kwali LGAs'.")]),
        ("koro-ija", "Koro Ija", "@languages:plateau", None, [], [], []),
        ("koro-zuba", "Koro Zuba", "@languages:plateau", "koro1324", [], [], [("gwagwalada", "INEC's directory has a ward Zuba in Gwagwalada Area Council; the Atlas places Koro Zuba near Zuba.")])]
RECORDS, NAMES, RELATIONS = [], [], []
for k, name, par, g, names, lgas, rep in NEWL:
    f = dict(lang_type="language", name=name, slug=k, parent_id=par, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k])
    if g: f["glottocode"] = g
    srcs = [("ATLAS", name), ("INECF", f"{name}: village placement")] + ([("GLIDX", f"Glottolog {g}")] if g else [])
    RECORDS.append(dict(key=k, table="languages", evidence="multiple_sources", level="well_documented", fields=f, srcs=srcs))
    for n, t, fld in names:
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=f"Blench's Atlas (2020), field {fld}: {FIELD[fld]}.", srcs=["ATLAS"]))
    RELATIONS.append(dict(frm=k, type="spoken_in", to=FCT, source="ATLAS", evidence="single_reliable_source", level="well_documented", notes="Atlas: Federal Capital Territory."))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=FC(l), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes="Atlas: 'Federal Capital Territory, Yaba and Kwali LGAs, along the Gurara river'."))
    for l, note in rep:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=FC(l), source="INECF", evidence="single_reliable_source", level="reported", notes=f"Placement not stated by the Atlas: {note}"))
for lang, note in [("gbagyi", "Atlas: Gbagyi in the 'Federal Capital Territory'."), ("gbari", "Atlas: Gbari in the 'Federal Capital Territory'."),
                   ("gwandara", "Atlas: Gwandara in the 'Federal Capital Territory'."), ("dibo", "Atlas: Dibo in the 'Federal Capital Territory'."),
                   ("gade", "Atlas: Gade in the 'Federal Capital Territory; Nasarawa State, Nassarawa LGA'."), ("nupe", "Atlas: Nupe–Nupe Tako cluster also in the 'Federal Capital Territory'.")]:
    RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=FCT, source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
PO = " Presence only; the nature of their presence is not established."
FG_NOTE = "Federal profile: among 'the indigenous inhabitants of Abuja'"
PEOPLE = [  # (slug, [(target, source, evidence, level, note)])
    ("gbagyi", [(FCT, "FGFCT", "multiple_sources", "well_documented", f"{FG_NOTE} ('Gbagyi (Gwari) as the major language'); Wikipedia (Abuja; Federal Capital Territory): the area was principally Gwari land."),
                (FC("bwari"), "WBWA", "single_reliable_source", "reported", "Wikipedia (Bwari): 'The original inhabitants of the town are the Gbagyi speaking people'." + PO),
                (FC("abaji"), "WABA", "single_reliable_source", "reported", "Wikipedia (Abaji): among 'the indigenous people'." + PO)]),
    ("basa", [(FCT, "FGFCT", "multiple_sources", "well_documented", f"{FG_NOTE} ('Bassa'); Wikipedia (Abuja): 'Basa'."),
              (FC("abaji"), "WABA", "single_reliable_source", "reported", "Wikipedia (Abaji): the council 'includes the land inhabited by the Bassa people'." + PO),
              (FC("gwagwalada"), "WGWA", "single_reliable_source", "reported", "Wikipedia (Gwagwalada): 'the traditional headquarters of the Bassa people in FCT', seat of the Aguma." + PO),
              (FC("kwali"), "ATLAS", "single_reliable_source", "reported", "Blench's Atlas: Basa-Gurara, also called Basa-Kwali, is spoken in Kwali; no source read names the Basa among Kwali's peoples." + PO)]),
    ("gwandara", [(FCT, "FGFCT", "multiple_sources", "well_documented", f"{FG_NOTE}; Wikipedia (Abuja)."),
                  (FC("abuja-municipal-area-council"), "WABJ", "single_reliable_source", "reported", "Wikipedia (Abuja): Gwandara speakers are 'mostly found in AMAC and Bwari Area Council'." + PO),
                  (FC("bwari"), "WABJ", "single_reliable_source", "reported", "Wikipedia (Abuja): Gwandara speakers are 'mostly found in AMAC and Bwari Area Council'." + PO)]),
    ("gade", [(FCT, "FGFCT", "single_reliable_source", "well_documented", f"{FG_NOTE} ('Gade')."),]),
    ("dibo", [(FCT, "FGFCT", "multiple_sources", "well_documented", f"{FG_NOTE} ('Ganagana'); Wikipedia (Abuja): 'Abawa (Ganagana)'. The Atlas gives Ganagana as another name of the Dibo."),
              (FC("abaji"), "WABA", "single_reliable_source", "reported", "Wikipedia (Abaji): 'Ganagana people' among the indigenous people." + PO)]),
    ("koro", [(FCT, "FGFCT", "multiple_sources", "well_documented", f"{FG_NOTE} ('Koro'); Wikipedia (Abuja): among other groups."),
              (FC("bwari"), "WBWA", "single_reliable_source", "reported", "Wikipedia (Bwari): Musa Ijakoro, 'of Koro ethnic minority', turbaned District Head of Bwari in 1976." + PO)]),
    ("nupe", [(FCT, "WABJ", "single_reliable_source", "reported", "Wikipedia (Abuja): the Nupe among 'other groups in the area'.")]),
    ("ebira", [(FCT, "WABJ", "single_reliable_source", "reported", "Wikipedia (Abuja; Abaji): the Egburra (Egbura, Egbira); linked to the Ebira record."),
               (FC("abaji"), "WABA", "single_reliable_source", "reported", "Wikipedia (Abaji): 'Egbura' among the indigenous people; the Egbura ruled Abaji after winning the kingship from the Bassa." + PO)]),
    ("hausa", [(FC("bwari"), "WBWA", "single_reliable_source", "reported", "Wikipedia (Bwari): Hausa and Gbagyi communities in Bwari district." + PO)]),
]
for slug, links in PEOPLE:
    for to, src, ev, lvl, note in links:
        RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to=to, source=src, evidence=ev, level=lvl, notes=note,
                              **({"settlement_status": "unknown"} if "other:" in to else {})))
RELATIONS += [
    dict(frm="@ethnic_groups:basa", type="speaks", to="basa-gurara", source="ATLAS", evidence="single_reliable_source", level="reported",
         notes="The Atlas's Basa of the FCT (Basa-Gurara, also called Basa-Kwali); the federal profile's 'Bassa'. The Basa record also covers the Basa-Benue speakers of Kogi and Nasarawa."),
    dict(frm="@ethnic_groups:koro", type="speaks", to="koro-zuba", source="ATLAS", evidence="single_reliable_source", level="reported", notes="Koro Zuba, one of the Koro lects of the FCT (Atlas)."),
    dict(frm="@ethnic_groups:koro", type="speaks", to="koro-ija", source="ATLAS", evidence="single_reliable_source", level="reported", notes="Koro Ija, one of the Koro lects of the FCT (Atlas)."),
]
GAPS = [
    ("FCT: Kuje and Kwali peoples", "No source read names the peoples of Kuje or Kwali area councils; Kwali is linked to the Basa only through the Atlas's 'Basa-Kwali'."),
    ("FCT: Abawa", "Wikipedia (Abuja) writes 'Abawa (Ganagana)'; the Atlas lists Abawa with Gupa as a separate language (Gupa–Abawa) of Niger State. The name is not stored as an alias of the Dibo."),
    ("FCT: Egbura", "The Egbura of Abaji are linked to the Ebira record (Wikipedia writes Egbira/Egbura); whether they should be a separate record (Ebira Koto) is for review."),
    ("FCT: Koro Ija", "The Atlas places Koro Ija near Lambata, which is not in INEC's FCT directory; its area council is not known."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="FCT languages and peoples: 3 new languages (Basa-Gurara, Koro Ija, Koro Zuba); Gbagyi, Gbari, Gwandara, Dibo, Gade and Nupe linked as languages; Gbagyi, Basa, Gwandara, Gade, Dibo (Ganagana), Koro, Nupe, Ebira (Egbura) and Hausa linked as peoples.")


def report():
    lga = sorted({r["to"].split("/")[-1] for r in RELATIONS if "federal-capital-territory/" in r["to"]})
    L_ = ["# Research batch 172 — Federal Capital Territory: languages and peoples", "",
          f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
          "## What it adds", "",
          "- **3 new languages:** **Basa-Gurara** (Basa-Kwali; Kwali, and Abaji by INEC's Yaba ward), **Koro Ija** (one village near Lambata) and **Koro Zuba** (one village near Zuba, Gwagwalada).",
          "- **Existing languages linked to the FCT:** Gbagyi, Gbari, Gwandara, Dibo, Gade, Nupe.",
          "- **Peoples** (all records already exist): Gbagyi, Basa, Gwandara, Gade, Dibo (the federal profile's Ganagana) and Koro, named by the federal profile as the indigenous inhabitants; Nupe, Ebira (Egbura) and Hausa from Wikipedia, as reported.",
          f"- **Area councils with peoples or languages linked:** {', '.join(lga)} (Kuje has none yet).", "",
          "## The new language records", ""] + [f"**{n}.** {TEXT[k]}\n" for k, n, *_ in NEWL]
    L_ += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_172_fct_languages_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_172_fct_languages_peoples_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)}")
