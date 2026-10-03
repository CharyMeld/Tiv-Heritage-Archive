"""
Research batch 055 — Kogi LGA profiles (headquarters, 2006 population and area, short sourced descriptions
for the 21 LGAs). Researched 2026-09-30. Pattern: batches 042 and 048.

Sources:
  * Statoids (reused): headquarters, 2006 census population, area.
  * INEC, "Kogi State" LGA office addresses (inecnigeria.org, via the Internet Archive). Tier 1. Where the
    address is at or next to the LGA secretariat (Bassa — Oguma; Dekina; Igalamela-Odolu — Ajaka; Mopa-Muro —
    Mopa; Ofu — Ugwolawo; Okehi — Obangede; Olamaboro — Okpo; Yagba East — Isanlu) it fixes the headquarters.
  * Wikipedia article of each LGA (introduction).
No official Kogi State table of LGA headquarters was found (unlike Plateau).
Headquarters grading: two sources that explicitly name the headquarters = well documented; one = reported.
  * Okehi: Statoids 'Okehi'; Wikipedia and INEC (secretariat) 'Obangede' -> Obangede.
  * Ofu: Statoids 'Ogwoawo'; Wikipedia 'Ogwoawo (or Ugwalawo or Gwalawo)'; INEC (secretariat) 'Ugwolawo'
    -> Ugwolawo (INEC's spelling, also the Igala council of that name), with the others as variants.
  * Ajaokuta: Statoids 'Egayan', Wikipedia 'Egayin' -> Egayan, with Egayin as a variant.
Not used from Wikipedia: Mopa-Muro '59100 at the 2016 census' (no census was held in 2016); Bassa '139,687'
(Statoids 139,993 is used; noted); officials; postal codes.
Also: the Etsu Bassa-Nge's seat, Gboloko, is placed in Bassa LGA by Wikipedia (Bassa, Kogi State) — a new link.
"""
import json, re, sys

ACCESSED = "2026-09-30"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Kogi rows (NG.KO.*): 2006 census, area, headquarters."),
    "INECO": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Kogi State: INEC state and LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://www.inecnigeria.org/wp-content/uploads/2024/04/KOGI-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (no longer on the INEC site).",
                  notes="State office, Marine Road, Lokoja. LGA offices at or near the LGA secretariat: Bassa — Oguma; Dekina — Dekina; Igalamela-Odolu — Ajaka; Mopa-Muro — Mopa; Ofu — Ugwolawo; Okehi — Obangede; Olamaboro — Okpo; Yagba East — Isanlu. Other offices: Adavi — Oniyeka Okunchi; Ajaokuta — Adogo; Ankpa; Ibaji — Onyedega; Idah; Ijumu — Iyara; Kabba/Bunu — Kabba; Kogi — Koton Karfe; Lokoja; Ogori/Magongo — Akpafa; Okene; Omala — Abejukolo; Yagba West — Odo-Ere."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified", notes="Reused (batch 051)."),
}
# slug: (name, Wikipedia title or None, Statoids HQ, 2006 pop, area km2, HQ used, explicit HQ sources, text)
LGAS = {
 "adavi": ("Adavi", "Adavi, Nigeria", "Ogaminana", 202194, 718, "Ogaminana", "SW", "Wikipedia's article on the Ebira places the Ebira Tao in the LGA; INEC's office for the LGA is at Oniyeka Okunchi."),
 "ajaokuta": ("Ajaokuta", "Ajaokuta", "Egayan", 122321, 1362, "Egayan", "SW",
              "Wikipedia spells the headquarters Egayin; INEC's office for the LGA is at Adogo. The Federal Government's profile of the state names Ajaokuta Steel as the largest iron and steel industry in Nigeria. Wikipedia's articles on the Ebira and the Igala place Ebira (including the Eganyi) and Igala in the LGA."),
 "ankpa": ("Ankpa", "Ankpa", "Ankpa", 267353, 1200, "Ankpa", "S",
           "Blench's Atlas places Igala and Basa-Benue here, and names Ánkpa and Ògùgù among the dialects of Igala; the federal profile names the Ogugu as a subgroup of the Igala. Ankpa is also one of the traditional councils of the Igala Kingdom (Wikipedia)."),
 "bassa": ("Bassa", "Bassa, Kogi State", "Oguma", 139993, 1925, "Oguma", "SWI",
           "Wikipedia gives a 2006 population of 139,687. It is the home of the Bassa-Nge, whose ruler, the Etsu Bassa-Nge, has his seat at Gboloko in the LGA, and of the Bassa (Basa) and Igala; Blench's Atlas places Basa-Benue, Igala, Ebira (Koto) and Nupe Tako here."),
 "dekina": ("Dekina", "Dekina", "Dekina", 260312, 2461, "Dekina", "SI",
            "Blench's Atlas places Igala here and names Ànyìgbá and Ìfè among its dialects in the LGA; Dekina is also one of the traditional councils of the Igala Kingdom (Wikipedia)."),
 "ibaji": ("Ibaji", "Ibaji", "Onyedega", 128129, 1377, "Onyedega", "SW",
           "Wikipedia places the headquarters on the Niger. The LGA bears the name of the Ìbàjì dialect of Igala, which Blench's Atlas records in the Idah area."),
 "idah": ("Idah", "Idah", "Idah", 79815, 36, "Idah", "S",
          "It is the smallest LGA of the state by area. Idah is the capital of the Igala Kingdom and the seat of the Attah Igala, and the Ojogwu Atogwu Tumulus near the Attah's palace is Kogi's only declared national monument (No. 38)."),
 "igalamela-odolu": ("Igalamela-Odolu", "Igalamela-Odolu", "Ajaka", 148020, 2175, "Ajaka", "SWI", "Ajaka is also one of the traditional councils of the Igala Kingdom (Wikipedia)."),
 "ijumu": ("Ijumu", "Ijumu", "Iyara", 119929, 1306, "Iyara", "SW",
           "It is one of the Okun LGAs; Ijumu is among the Okun dialects of Yoruba, and the Olujumu among the Okun stools (Wikipedia; Blench's Atlas)."),
 "kabba-bunu": ("Kabba/Bunu", "Kabba/Bunu", "Kabba", 145446, 2706, "Kabba", "SW",
                "Kabba, the largest Okun town, is the seat of the Obaro of Kabba, chairman of the Okun Area Traditional Council; Bunu is another of the Okun peoples (Wikipedia; Daily Trust)."),
 "kogi": ("Kogi", "Kogi, Kogi State", "Koton Karfe", 115900, 1498, "Koton Karfe", "SW",
          "Wikipedia gives Koton Karifi as another form. It is the seat of the Ohimege-Igu of Koton Karfe, a first-class stool, and Blench's Atlas places Kakanda, Kupa, Nupe and Ebira (Koto) in the LGA."),
 "lokoja": ("Lokoja", "Lokoja", "Lokoja", 195261, 3180, "Lokoja", "S",
            "Lokoja, at the confluence of the Niger and Benue, is the state capital; Wikipedia dates its foundation to a trading post established in 1857 by William Balfour Baikie. It is the largest LGA of the state by area. The National Museum of Colonial History, Mount Patti and three of the NCMM's proposed national monuments are in Lokoja, and Wikipedia names the Bassa-Nge, Oworo and Nupe as indigenous to the area."),
 "mopa-muro": ("Mopa-Muro", "Mopa-Muro", "Mopa", 44037, 901, "Mopa", "SWI", "It had the smallest population of the state's LGAs at the 2006 census. It is one of the Okun LGAs (Wikipedia)."),
 "ofu": ("Ofu", "Ofu, Nigeria", "Ogwoawo", 192169, 1680, "Ugwolawo", "SWI",
         "Statoids and Wikipedia spell the headquarters Ogwoawo, Wikipedia also Ugwalawo or Gwalawo; INEC places the LGA secretariat at Ugwolawo, also the name of one of the traditional councils of the Igala Kingdom (Wikipedia)."),
 "ogori-magongo": ("Ogori/Magongo", "Ogori/Magongo", "Akpafa", 39622, 79, "Akpafa", "SW",
                   "According to Wikipedia it was created from the old Okene LGA for the Ogori and Magongo people; its main towns are Ogori and Magongo, where Ọkọ and Ọsayẹn are spoken, and it is the home of the Ovia-Osese and Owiya Osese festivals and the seat of the Olu of Magongo."),
 "okehi": ("Okehi", "Okehi", "Okehi", 199999, 661, "Obangede", "WI", "Statoids gives Okehi as the headquarters. Wikipedia's article on the Ebira and Blench's Atlas place the Ebira in the LGA."),
 "okene": ("Okene", "Okene", "Okene", 320260, 328, "Okene", "S",
           "It was the most populous LGA of the state at the 2006 census. Okene is the seat of the Ohinoyi of Ebiraland, and until Kogi State was created it was seen as the administrative centre of the Ebira-speaking people (Wikipedia)."),
 "olamaboro": ("Olamaboro", "Olamaboro", "Okpo", 160152, 1132, "Okpo", "SWI", "Wikipedia's article on the Igala places them in the LGA."),
 "omala": ("Omala", "Omala, Nigeria", "Abejukolo", 108402, 1667, "Abejukolo", "SW", "Omala is also one of the traditional councils of the Igala Kingdom (Wikipedia)."),
 "yagba-east": ("Yagba East", "Yagba East", "Isanlu", 140150, 1396, "Isanlu", "SWI",
                "Wikipedia describes it as populated mainly by the Yagba, one of the Okun peoples; the Agbana of Isanlu is among the Okun stools (Wikipedia)."),
 "yagba-west": ("Yagba West", None, "Odo Ere", 149023, 1276, "Odo Ere", "S", "INEC's office for the LGA is also at Odo-Ere. It is one of the Okun LGAs (Wikipedia, Okun people)."),
}
for slug, (name, wt, *_r) in LGAS.items():
    if wt:
        SOURCES[f"W_{slug}"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=wt, organisation="Wikipedia", url=W(wt),
                                    verification_status="needs_corroboration", notes=f"Wikipedia article on {name} LGA (introduction), consulted {ACCESSED}.")
SOURCES["WOKUN"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Okun people", organisation="Wikipedia", url=W("Okun people"),
                        verification_status="needs_corroboration", notes="Reused.")
REUSE = {
    "WIGALA": ("Igala people", W("Igala people")), "WIGK": ("Igala Kingdom", W("Igala Kingdom")), "WEBIRA": ("Ebira people", W("Ebira people")),
    "NCMML": ("List of National Monuments", "https://museum.ng/national-monuments-in-nigeria/list-of-national-monuments/"),
    "NCMMP": ("Proposed National Monuments", "https://museum.ng/national-monuments-in-nigeria/proposed-national-monuments/"),
    "NCMMM": ("National Museums", "https://museum.ng/museums/national-museums/"),
    "BP24": ("Yahaya Bello deposes 3 first class Kogi monarchs, annoints new Ohinoyi of Ibiraland", "https://blueprint.ng/yahaya-bello-deposes-3-first-class-kogi-monarchs-annoints-new-ohinoyi-of-ibiraland/"),
    "DT18": ("Oba Owoniyi enthroned as 44th Obaro of Kabba", "https://dailytrust.com/oba-owoniyi-enthroned-as-44th-obaro-of-kabba"),
    "WILL23": ("Ohinoyi Of Ebiraland Ado Ibrahim Is Dead", "https://thewillnews.com/ohinoyi-of-ebiraland-ado-ibrahim-is-dead/"),
    "WPATTI": ("Mount Patti", W("Mount Patti")), "FGK": ("Kogi State", "https://nigeria.gov.ng/states/kogi/"),
}
for k, (t, u) in REUSE.items():
    SOURCES[k] = dict(source_type="other", title=t, url=u, verification_status="needs_corroboration", notes="Reused.")
KEYS = {"WIGALA": ["Igala"], "WIGK": ["councils of the Igala Kingdom", "capital of the Igala Kingdom"], "WEBIRA": ["article on the Ebira", "Ebira-speaking"],
        "NCMML": ["declared national monument"], "NCMMP": ["proposed national monuments"], "NCMMM": ["Museum of Colonial History"],
        "BP24": ["first-class stool", "Olu of Magongo"], "DT18": ["Obaro of Kabba"], "WILL23": ["Ohinoyi"], "WPATTI": ["Mount Patti"], "FGK": ["Ajaokuta Steel", "federal profile"]}
NAMES_OF = {"S": "Statoids", "W": "Wikipedia", "I": "INEC"}


def by(agree):
    n = [NAMES_OF[c] for c in "SWI" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, wt, s_hq, pop, area, hq, agree, extra = LGAS[slug]
    t = f"{name} is a local government area of Kogi State with its headquarters at {hq} (according to {by(agree)})."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    return (t + (" " + extra if extra else "")).strip()


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("INECO", f"{name}: INEC LGA office address")] + ([(f"W_{slug}", f"{name}: Wikipedia introduction")] if wt else [])
    if "Atlas" in extra:
        srcs.append(("ATLAS", f"{name}: languages placed in the LGA"))
    if re.search(r"\bOkun\b", extra):
        srcs.append(("WOKUN", f"{name}: Okun LGA"))
    for k, kws in KEYS.items():
        if any(w in extra for w in kws):
            srcs.append((k, f"{name}: {SOURCES[k]['title']}"))
    if slug != "lokoja":
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([(f"W_{slug}", f"Headquarters of {name}")] if "W" in agree else []) \
               + ([("INECO", f"LGA secretariat at {hq}")] if "I" in agree else [])
        two = len(agree) >= 2
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if two else "single_reliable_source", level="well_documented" if two else "reported",
                            fields=dict(place_type="town", name=hq, slug=re.sub(r"[^a-z0-9]+", "-", hq.lower()).strip("-"), admin_unit_id=f"@admin_units:lga:kogi/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Kogi State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    else:
        upd["headquarters_place_id"] = "@places:lokoja"
    UPDATES.append(dict(ref=f"@admin_units:lga:kogi/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:kogi/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:kogi/{slug}", metric="area_km2", value_low=area, method="other",
                      notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_ajaokuta", name="Egayin", name_type="spelling_variant", usage_notes="Wikipedia.", srcs=["W_ajaokuta"]),
    dict(record="hq_ofu", name="Ogwoawo", name_type="spelling_variant", usage_notes="Statoids and Wikipedia.", srcs=["STAT", "W_ofu"]),
    dict(record="hq_ofu", name="Ugwalawo", name_type="spelling_variant", usage_notes="Wikipedia.", srcs=["W_ofu"]),
    dict(record="hq_kogi", name="Koton Karifi", name_type="spelling_variant", usage_notes="Wikipedia.", srcs=["W_kogi"]),
    dict(record="hq_yagba-west", name="Odo-Ere", name_type="spelling_variant", usage_notes="INEC's form.", srcs=["INECO"]),
]
RELATIONS = [
    dict(frm="@polities:etsu-bassa-nge", type="located_in", to="@admin_units:lga:kogi/bassa", source="W_bassa", evidence="multiple_sources", level="reported",
         notes="Wikipedia (Bassa, Kogi State): the Etsu Bassa-Nge's throne at Gboloko. Seat of the stool (not a statement of its jurisdiction)."),
]
GAPS = [
    ("Kogi: no official table of LGA headquarters", "Ankpa, Idah, Lokoja, Okene and Yagba West rest on Statoids alone (INEC shows only its office in the town); an official Kogi State list would settle them."),
    ("Kogi: headquarters spellings", "Ofu: Ugwolawo (INEC), Ogwoawo (Statoids, Wikipedia), Ugwalawo; Ajaokuta: Egayan (Statoids), Egayin (Wikipedia)."),
    ("Kogi: Bassa population", "Statoids gives 139,993 and Wikipedia 139,687 for 2006; Statoids' figure is recorded."),
    ("Kogi: LGA creation dates", "Not found except Ogori/Magongo's origin in the old Okene LGA (Wikipedia, no date)."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATS,
                scope="Kogi LGA profiles: headquarters, 2006 population and area, short sourced descriptions (21 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 055 — Kogi LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Kogi. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **21 LGA descriptions**, filling fields that were empty.",
         f"- **{len(RECORDS)} headquarters towns**, created as places. Lokoja reuses the existing place.",
         "- **Figures:** the 2006 population and the area of each of the 21 LGAs, from Statoids.",
         "- **New official source:** INEC's list of its Kogi LGA offices. For 8 LGAs it names the LGA secretariat.",
         "- **Headquarters decided on the evidence:**",
         "  - **Okehi → Obangede** (Wikipedia and INEC), not 'Okehi' as Statoids has it.",
         "  - **Ofu → Ugwolawo**, INEC's spelling; Ogwoawo and Ugwalawo are kept as variants.",
         "- **No official Kogi table of headquarters was found**, so five LGAs rest on Statoids alone. This is listed as a gap.",
         "- **New link:** the Etsu Bassa-Nge's seat, Gboloko, is in Bassa LGA, per Wikipedia.",
         "- **Not used:** Wikipedia's Mopa-Muro figure 'at the 2016 census' (there was no census in 2016).",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Statoids | Sources | Level |", "|---|---|---|---|---|"]
    for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
        L.append(f"| {name} | {hq} | {s_hq} | {by(agree)} | {'well documented' if len(agree) >= 2 else 'reported'} |")
    L += ["", "## The 21 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_055_kogi_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_055_kogi_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)} names={len(NAMES)}")
