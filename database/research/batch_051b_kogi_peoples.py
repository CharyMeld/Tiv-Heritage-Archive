"""
Research batch 051b — Kogi (Phase 3): the peoples of Kogi State and the LGAs where they live, as far as
the sources allow. Researched 2026-09-30. Pattern: batches 038b and 044b.

Sources:
  * Federal Government of Nigeria, state page for Kogi (nigeria.gov.ng, 2024), 'Ethnic Profile' — Tier 1:
    three main groups Igala, Ebira and Okun ('similar to Yoruba'), minorities Bassa, a small fraction of
    Nupe mainly in Lokoja, the Ogugu subgroup of the Igala, Gwari, Kakanda, Oworo ('similar to Yoruba'),
    Ogori Magongo and the Eggan community. The same page's LGA list wrongly appends seven Benue LGAs, so
    the page is used for the ethnic profile only.
  * Wikipedia: Kogi State (peoples by part of the state), Igala people, Ebira people, Okun people, Oworo
    people, Bassa Nge people, Ogori/Magongo (LGA) — the LGAs named there.
  * Blench's Atlas (batch 051) for the languages and their LGAs.
No Ethnologue table by LGA exists for Kogi on Wikipedia (unlike Plateau), so people–LGA links come from
the Wikipedia people articles; 'well documented' where the Atlas places the people's language in that
LGA too, 'reported' otherwise. All are presence only.
Handling:
  * Okun is recorded as a subgroup of the Yoruba, and the Oworo as one of the Okun peoples (Wikipedia;
    the federal page: 'similar to Yoruba'); the Bassa Nge as a Nupe group, and the Kakanda and Kupa as
    peoples of Nupe extraction (Wikipedia, Kogi State; Bassa Nge people).
  * Wikipedia's 'Koto' LGA for the Bassa (Basa) is read as Kogi LGA (headquarters Koton Karfe) — reported.
  * Not created: the Eggan community (federal page and the Okun article name it; no further source) and
    the Ogugu (a subgroup of the Igala; also an Igala dialect in the Atlas) — mentioned in the Igala text.
"""
import json, sys

ACCESSED = "2026-09-30"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "FGK": dict(source_type="official_website", source_kind="official_website", source_tier=1, title="Kogi State", organisation="Federal Government of Nigeria (nigeria.gov.ng)",
                publication_date="2024-03-06", url="https://nigeria.gov.ng/states/kogi/", verification_status="verified",
                notes="Ethnic Profile: 'three main ethnic groups and languages in Kogi: Igala, Ebira, and Okun (similar to Yoruba) with other minorities like Bassa, a small fraction of Nupe mainly in Lokoja, the Ogugu subgroup of the Igala, Gwari, Kakanda, Oworo people (similar to Yoruba), Ogori Magongo and the Eggan community.' Created 27 August 1991; capital Lokoja. The page's list of LGAs wrongly appends seven Benue LGAs."),
    "WKOGI": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Kogi State", organisation="Wikipedia", url=W("Kogi State"), verification_status="needs_corroboration",
                  notes="Ebira, Gbagyi, Nupe (Bassa Nge, Kakanda, Kupa) and Oko in the centre; Igala and Bassa in the east; Yoruba (Okun, Ogori, Oworo, Magongo) in the west. Three main groups Igala, Ebira and Okun; others: Bassa Nge of Bassa LGA, Kupa and Kakanda 'of Nupe extraction under Lokoja LGA', Bassa of Bassa, Lokoja and Koto LGAs, Oworo, Ogori Magongo and Idoma."),
    "WIGALA": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Igala people", organisation="Wikipedia", url=W("Igala people"), verification_status="needs_corroboration",
                   notes="Native to the region south of the Niger–Benue confluence; Idah the ancestral home and capital; the Attah; in Kogi in Idah, Igalamela/Odolu, Ofu, Olamaboro, Dekina, Bassa, Ankpa, Omala, Lokoja, Ibaji and Ajaokuta LGAs; formerly the Igala Division of Kabba Province."),
    "WEBIRA": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Ebira people", organisation="Wikipedia", url=W("Ebira people"), verification_status="needs_corroboration",
                   notes="Ebira Tao predominantly in Adavi, Ajaokuta, Okehi, Okene and Ogori/Magongo LGAs; Eganyi in Ajaokuta; Ebira Koto in Kogi (Koton Karfe), Bassa and Lokoja LGAs, Abaji (FCT) and Akoko-Edo (Edo); also Toto (Nasarawa)."),
    "WOKUN": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Okun people", organisation="Wikipedia", url=W("Okun people"), verification_status="needs_corroboration",
                  notes="Yoruba-speaking people mainly of Kogi; Northeast Yoruba dialects; Owé, Iyagba (Yagba), Addé, Gbẹdẹ, Bunu, Ikiri and Oworo; in Kabba-Bunu, Yagba West, Yagba East, Mopa-Muro, Ijumu and Lokoja LGAs; Kabba their largest town; stools include the Obaro of Kabba (chairman of the Okun traditional council), Olubunu, Olujumu, Agbana of Isanlu and Olu of Oworo."),
    "WOWORO": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Oworo people", organisation="Wikipedia", url=W("Oworo people"), verification_status="needs_corroboration",
                   notes="A people around the Niger–Benue confluence speaking the Oworo dialect of Yoruba (Northeast Yoruba); counted among the Okun; towns include Agbaja, Obajana and Felele (a northern suburb of Lokoja)."),
    "WBNGE": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Bassa Nge people", organisation="Wikipedia", url=W("Bassa Nge people"), verification_status="needs_corroboration",
                  notes="Traced to Gbara, former Nupe capital; migrated from the Bida area about 1820; speak the Nupe-Tako dialect of Nupe; formerly the largest Nupe group; live in Bassa LGA and mostly in Lokoja; ruler the Etsu Bassa-Nge at Gboloko; language unlike that of the Bassa Nkomo who live in the same area."),
    "WOGM": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Ogori/Magongo", organisation="Wikipedia", url=W("Ogori/Magongo"), verification_status="needs_corroboration",
                 notes="LGA created from the old Okene LGA for the Ogori and Magongo people; headquarters Akpafa; two main towns, Ogori and Magongo; festivals Ovia Osese (Ogori) and Owiya Osese (Magongo)."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified", notes="Reused (batch 051)."),
}
K = lambda l: f"@admin_units:lga:kogi/{l}"
# Atlas placements (batch 051) to grade agreement
ATLAS_LGA = {"igala": {"ankpa", "dekina", "idah", "bassa", "ibaji"}, "ebira": {"okene", "okehi", "kogi", "bassa"}, "basa-benue": {"bassa", "ankpa"},
             "kakanda": {"kogi"}, "kupa": {"kogi"}, "nupe": {"kogi", "bassa"}, "oko-eni-osayen": {"ogori-magongo"}, "yoruba": set()}
# key: (name, [languages], [(lga, source)], other names [(name, type, note, src)], text, sources)
PEOPLE = {
 "igala": ("Igala", ["igala"],
           [(l, "WIGALA") for l in ("idah", "igalamela-odolu", "ofu", "olamaboro", "dekina", "bassa", "ankpa", "omala", "lokoja", "ibaji", "ajaokuta")],
           [("Igara", "alternative", "Wikipedia ('Igala or Igara people'); also Atlas field 2.C, other name for the people", "WIGALA")],
           "The Igala are one of the three main peoples of Kogi State, according to the Federal Government's state profile and Wikipedia. Wikipedia describes them as native to the region south of the confluence of the Niger and Benue, with Idah as their ancestral home and the capital of the Igala kingdom, whose ruler is the Attah; the area was formerly the Igala Division of Kabba Province. The federal profile names the Ogugu as a subgroup of the Igala, and Blench's Atlas lists Ògùgù among the dialects of Igala, spoken in Ankpa LGA. Wikipedia places the Igala in Idah, Igalamela-Odolu, Ofu, Olamaboro, Dekina, Bassa, Ankpa, Omala, Lokoja, Ibaji and Ajaokuta LGAs.",
           ["FGK", "WKOGI", "WIGALA", "ATLAS"]),
 "okun": ("Okun", ["yoruba"],
          [(l, "WOKUN") for l in ("kabba-bunu", "yagba-west", "yagba-east", "mopa-muro", "ijumu", "lokoja")], [],
          "The Okun are one of the three main peoples of Kogi State; the Federal Government's state profile describes them as similar to the Yoruba. According to Wikipedia they are Yoruba-speaking, with dialects classed as Northeast Yoruba, and the name, a greeting in their dialects, covers the Owé, Yagba, Addé, Gbẹdẹ, Bunu, Ikiri and Oworo peoples. Blench's Atlas groups the Yagba, Gbede and Ijumu dialects of Yoruba as 'Okun', and places Yoruba in the western LGAs of Kogi State. Kabba is their largest town, and their stools include the Obaro of Kabba, who chairs the Okun traditional council, the Olubunu, the Olujumu, the Agbana of Isanlu and the Olu of Oworo. Wikipedia places them in Kabba/Bunu, Yagba West, Yagba East, Mopa-Muro, Ijumu and Lokoja LGAs.",
          ["FGK", "WKOGI", "WOKUN", "ATLAS"]),
 "oworo": ("Oworo", ["yoruba"], [], [("Ọwọrọ", "spelling_variant", "Wikipedia", "WOWORO")],
           "The Oworo are named among the peoples of Kogi State by the Federal Government's state profile, which describes them as similar to the Yoruba. According to Wikipedia they live around the Niger–Benue confluence, speak the Oworo dialect of Yoruba (Northeast Yoruba), and are counted among the Okun; their towns include Agbaja, Obajana and Felele, a northern suburb of Lokoja. Their ruler, the Olu of Oworo, was given a supervisory role over the Kakanda, Kupa and Eggan districts in the early twentieth century.",
           ["FGK", "WOWORO", "WOKUN"]),
 "bassa-nge": ("Bassa-Nge", ["nupe"], [("bassa", "WBNGE"), ("lokoja", "WBNGE")], [("Bassa Nupe", "alternative", "Wikipedia", "WBNGE")],
               "The Bassa-Nge are named among the peoples of Kogi State by Wikipedia. They trace their origin to Gbara, a former capital of the Nupe kingdom, from which they migrated about 1820 after a dynastic feud; they speak the Nupe Tako dialect of Nupe, which Blench's Atlas places in Bassa LGA. Wikipedia places them in Bassa LGA and mostly in Lokoja, with their ruler, the Etsu Bassa-Nge, at Gboloko, and notes that their language differs from that of the Bassa Komo (Basa) who live in the same area.",
               ["WKOGI", "WBNGE", "ATLAS"]),
 "nupe": ("Nupe", ["nupe"], [("lokoja", "FGK")], [],
          "The Nupe are a people of the middle Niger, whose language Blench's Atlas places mainly in Niger State, and also in Kwara, Kogi and the Federal Capital Territory. The Federal Government's profile of Kogi State counts 'a small fraction of Nupe mainly in Lokoja' among the state's minorities, and Wikipedia describes the Bassa-Nge, Kakanda and Kupa of Kogi as peoples of Nupe origin.",
          ["FGK", "WKOGI", "ATLAS"]),
 "kakanda": ("Kakanda", ["kakanda"], [("lokoja", "WKOGI")], [],
             "The Kakanda are named among the minorities of Kogi State by the Federal Government's state profile. Wikipedia describes them as a people of Nupe extraction under Lokoja LGA, and Blench's Atlas places their language, a member of the Nupe group, along the Niger in the old Kogi LGA and in Niger State.",
             ["FGK", "WKOGI", "ATLAS"]),
 "kupa": ("Kupa", ["kupa"], [("lokoja", "WKOGI")], [],
          "The Kupa are named among the peoples of Kogi State by Wikipedia, which describes them as a people of Nupe extraction under Lokoja LGA. Blench's Atlas places their language, a member of the Nupe group, in 52 villages around Abugi in the old Kogi LGA.",
          ["WKOGI", "ATLAS"]),
 "ogori-magongo": ("Ogori–Magongo", ["oko-eni-osayen"], [("ogori-magongo", "WOGM")], [("Oko", "alternative", "Wikipedia (Kogi State): 'Oko'", "WKOGI")],
                   "The Ogori and Magongo people are named among the peoples of Kogi State by the Federal Government's state profile and by Wikipedia. Ogori/Magongo LGA, with its headquarters at Akpafa, was created from the old Okene LGA for them; its two main towns are Ogori and Magongo. They speak Ọkọ (at Ogori) and Ọsayẹn (at Magongo), members of the Ọkọ–Eni–Ọsayẹn cluster, which Blench's Atlas treats as a branch of its own within Benue–Congo.",
                   ["FGK", "WKOGI", "WOGM", "ATLAS"]),
}
RECORDS, RELATIONS, NAMES = [], [], []
for slug, (name, langs, lgas, names, desc, srcs) in PEOPLE.items():
    RECORDS.append(dict(key=slug, table="ethnic_groups", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level="well_documented",
                        fields=dict(name=name, slug=slug, summary=desc.split(". ")[0] + ".", description=desc),
                        srcs=[(s, f"{name}") for s in srcs]))
    RELATIONS.append(dict(frm=slug, type="present_in", to="@admin_units:state:kogi", source=srcs[0], evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source",
                          level="well_documented", notes="Named among the peoples of Kogi State (" + ", ".join({"FGK": "federal profile", "WKOGI": "Wikipedia"}.get(s, s) for s in srcs if s in ("FGK", "WKOGI")) + ")."))
    for lg in langs:
        RELATIONS.append(dict(frm=slug, type="speaks", to=f"@languages:{lg}", source="ATLAS" if lg != "yoruba" else srcs[-2], evidence="multiple_sources", level="well_documented",
                              notes="Their language (Blench's Atlas; Wikipedia)."))
    for l, src in lgas:
        both = any(l in ATLAS_LGA.get(lg, ()) for lg in langs)
        RELATIONS.append(dict(frm=slug, type="present_in", to=K(l), source=src, evidence="multiple_sources" if both else "single_reliable_source",
                              level="well_documented" if both else "reported", settlement_status="unknown",
                              notes=f"Named for this LGA by {'the federal profile' if src == 'FGK' else 'Wikipedia'}" + ("; Blench's Atlas places their language here too." if both else ".") + " Presence only."))
    for n, t, note, src in names:
        NAMES.append(dict(record=slug, name=n, name_type=t, usage_notes=note + ".", srcs=[src]))
# Relations between peoples
RELATIONS += [
    dict(frm="okun", type="subgroup_of", to="@ethnic_groups:yoruba", source="WOKUN", evidence="multiple_sources", level="well_documented",
         notes="Yoruba-speaking (Wikipedia); 'similar to Yoruba' (federal profile)."),
    dict(frm="oworo", type="subgroup_of", to="okun", source="WOKUN", evidence="multiple_sources", level="reported", notes="Wikipedia (Okun people; Oworo people): counted among the Okun."),
    dict(frm="bassa-nge", type="subgroup_of", to="nupe", source="WBNGE", evidence="single_reliable_source", level="reported", notes="Wikipedia: formerly the largest of the Nupe groups."),
    dict(frm="kakanda", type="historically_related", to="nupe", source="WKOGI", evidence="single_reliable_source", level="reported", notes="Wikipedia: 'of Nupe extraction'."),
    dict(frm="kupa", type="historically_related", to="nupe", source="WKOGI", evidence="single_reliable_source", level="reported", notes="Wikipedia: 'of Nupe extraction'."),
]
# Existing people records in Kogi
EXIST = [
    ("ebira", ["FGK", "WEBIRA"], [("adavi", "WEBIRA"), ("ajaokuta", "WEBIRA"), ("okehi", "WEBIRA"), ("okene", "WEBIRA"), ("ogori-magongo", "WEBIRA"),
                                  ("kogi", "WEBIRA"), ("bassa", "WEBIRA"), ("lokoja", "WEBIRA")], ["ebira"]),
    ("basa", ["FGK", "WKOGI"], [("bassa", "WKOGI"), ("lokoja", "WKOGI"), ("kogi", "WKOGI"), ("ankpa", "ATLAS")], ["basa-benue"]),
    ("gbagyi", ["FGK", "WKOGI"], [], []),
    ("idoma", ["WKOGI"], [], []),
]
for slug, srcs, lgas, langs in EXIST:
    RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to="@admin_units:state:kogi", source=srcs[0], evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source",
                          level="well_documented" if len(srcs) > 1 else "reported", notes="Named among the peoples of Kogi State (" + ", ".join({"FGK": "federal profile (as 'Gwari' for the Gbagyi)" if slug == "gbagyi" else "federal profile", "WKOGI": "Wikipedia", "WEBIRA": "Wikipedia"}.get(s, s) for s in srcs) + ")."))
    for l, src in lgas:
        both = any(l in ATLAS_LGA.get(lg, ()) for lg in langs)
        note = "Wikipedia names 'Koto' LGA, read as Kogi LGA (Koton Karfe)." if (slug == "basa" and l == "kogi") else ""
        RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to=K(l), source=src, evidence="multiple_sources" if both else "single_reliable_source",
                              level="well_documented" if both else "reported", settlement_status="unknown",
                              notes=(note + " " if note else "") + ("Blench's Atlas places their language here." if src == "ATLAS" else "Named for this LGA by Wikipedia" + ("; Blench's Atlas agrees." if both else ".")) + " Presence only."))
seen, RELS = set(), []
for r in RELATIONS:
    k = (r["frm"], r["type"], r["to"])
    if k not in seen:
        seen.add(k); RELS.append(r)
RELATIONS = RELS
GAPS = [
    ("Kogi: no official people–LGA list", "The Federal Government's profile names the peoples but not their LGAs, and its LGA list is faulty; Wikipedia has no Ethnologue table for Kogi. LGA links rest on Wikipedia's people articles, checked against the Atlas."),
    ("Kogi: the Eggan community", "Named by the federal profile and the Okun article (a district once supervised by the Olu of Oworo); no further source read."),
    ("Kogi: Oworo LGAs", "Wikipedia lists Oworo towns (Agbaja, Obajana, Felele near Lokoja) but no LGA; linked to the state only."),
    ("Kogi: Agatu and Igbo", "Some web pages name them among Kogi's peoples; no reliable source read."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Kogi (Phase 3): {len(PEOPLE)} peoples (federal profile and Wikipedia, checked against the Atlas), LGA links, language links; Ebira, Basa, Gbagyi, Idoma.")


def report():
    lp = {}
    for r in RELATIONS:
        if r["type"] == "present_in" and ":lga:kogi/" in r["to"]:
            lp.setdefault(r["to"].split("/")[-1], []).append(r["frm"].replace("@ethnic_groups:", ""))
    L = ["# Research batch 051b — Kogi: the peoples", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## Sources", "",
         "- **Official source (Tier 1): the Federal Government's Kogi State profile** (nigeria.gov.ng).",
         "  - It names the Igala, Ebira and Okun as the three main peoples, and the Bassa, Nupe, Ogugu, Gwari, Kakanda, Oworo, Ogori Magongo and Eggan as minorities.",
         "  - Its LGA list is faulty (it adds seven Benue LGAs), so the page is used only for its ethnic profile.",
         "- **The people–LGA links come from Wikipedia's articles on each people**, checked against the Atlas. Wikipedia has no Ethnologue table for Kogi.", "",
         "## What it adds", "",
         f"- **{len(RECORDS)} new people records:** " + ", ".join(v[0] for v in PEOPLE.values()) + ".",
         "- **Existing records get Kogi links:** Ebira, Basa, Gbagyi (the federal profile's 'Gwari') and Idoma.",
         f"- **{sum(1 for r in RELATIONS if r['type'] == 'present_in' and ':lga:kogi/' in r['to'])} people–LGA links.** They are *well documented* where the Atlas agrees and *reported* otherwise, and all record presence only.",
         "- **Links between peoples:**",
         "  - the Okun as a subgroup of the Yoruba",
         "  - the Oworo as one of the Okun peoples",
         "  - the Bassa-Nge as a Nupe group",
         "  - the Kakanda and Kupa as peoples of Nupe origin",
         "- All records are short, so they are noindex and the sitemap is unchanged.", "",
         "## People by LGA", "", "| LGA | Peoples |", "|---|---|"]
    for l in sorted(lp):
        L.append(f"| {l} | {', '.join(sorted(set(lp[l])))} |")
    L += ["", "## The people records", ""] + [f"**{v[0]}.** {v[4]}\n" for v in PEOPLE.values()] + ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_051b_kogi_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_051b_kogi_peoples_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} lga_links={sum(1 for r in RELATIONS if r['type'] == 'present_in' and ':lga:kogi/' in r['to'])}")
