"""
Research batch 035 — Nasarawa LGA profiles (headquarters, 2006 population and area, short sourced
descriptions for the 13 LGAs). Researched 2026-09-26. Pattern: Benue batch 030.

Sources:
  * Statoids (reused): headquarters, 2006 census population, area. Its Obi headquarters is given as
    "Agwa Atashi (?)", and the areas of Obi and Nasarawa carry an unexplained asterisk.
  * INEC, "Nasarawa State" LGA office addresses (Word file of Sept. 2012, on inecnigeria.org, read via
    the Internet Archive copy of 15 Aug 2026; the live link returns 404). Tier 1. For Doma, Awe,
    Kokona (Garaku) and Obi the address names the LGA council secretariat, which fixes the headquarters.
    For the other LGAs it only shows that INEC's LGA office is in the town.
  * Wikipedia article of each LGA (introduction and infobox 'seat').

Headquarters grading: two sources that explicitly name the headquarters = well documented; one = reported.
  * Kokona: Statoids "Kokona"; Wikipedia and INEC (council secretariat) "Garaku" -> Garaku.
  * Obi: Statoids "Agwa Atashi (?)"; INEC (council secretariat) "Obi" -> Obi (reported), variant kept.
  * Karu: Statoids "Karu"; Wikipedia "New Karu"; INEC office at Mararaba -> LEFT OPEN (gap).
  * Nasarawa Eggon: Statoids "Nassarawa Egon", Wikipedia "Eggon" -> the town of Nasarawa Eggon.
Not used from Wikipedia: Akwanga's "513,930 at the 2006 census" (the census figure is 113,430); Awe's
"2005 estimate"; Lafia's "2021 census" (there was no 2021 census); postal codes; named officials.
"""
import json, re, sys

ACCESSED = "2026-09-26"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Nasarawa rows: Obi headquarters 'Agwa Atashi (?)'; asterisks (unexplained) on the areas of Obi (967) and Nasarawa (5,704)."),
    "INECO": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Nasarawa State: INEC state and LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", publication_date="2012-09-19",
                  url="https://www.inecnigeria.org/wp-content/uploads/2024/04/NASARAWA-STATE.pdf", verification_status="verified",
                  notes="Read via the Internet Archive (snapshot 2026-08-15; live link 404). One-page list: Akwanga — behind Akwanga LGED office, Keffi Road, Akwanga; Awe — besides the SSS office along Secretariat, Awe; Doma — besides Local Government Secretariat, Lafia Road, Doma; Karu — behind the High Court, Mararaba, Karu; Keana — behind General Hospital, Keana; Keffi — Old Kaduna Road, Keffi; Kokona — Kokona/Akwanga Road, close to LG Council Secretariat, Garaku; Lafia — beside NBS, Makurdi Road, Lafia; Nasarawa — opposite GRA, Nasarawa; Nasarawa Eggon — Lafia Road, Nasarawa Eggon; Obi — along Agwabo Road, close to Obi LG Council Secretariat, Obi; Toto — opposite Police Station, Nasarawa Road, Toto; Wamba — behind Timber Shed, Wamba. State head office: off Sani Abacha Way, Kurikyo Road, Lafia."),
}
# slug: (name, Wikipedia title, Statoids HQ, 2006 pop, area km2, HQ used (None = open), explicit HQ sources, text)
# explicit sources: S = Statoids, W = Wikipedia, I = INEC (council secretariat named)
LGAS = {
 "akwanga": ("Akwanga", "Akwanga", "Akwanga", 113430, 996, "Akwanga", "S", ""),
 "awe": ("Awe", "Awe, Nigeria", "Awe", 112574, 2557, "Awe", "SI", ""),
 "doma": ("Doma", "Doma, Nigeria", "Doma", 139607, 2714, "Doma", "SI",
          "Doma is the seat of the Andoma of Doma. Wikipedia describes the Alago as the main people of the LGA, most of them farmers, in its northern part, and the Bassa as numerous in the south. It names Odu as the LGA's annual festival and lists the Doma Dam, an Olam rice farm, a Federal Science and Technical College and a Special Forces command among its features."),
 "karu": ("Karu", "Karu, Nasarawa State", "Karu", 205477, 2640, None, "conflict",
          "Its headquarters is given as Karu by Statoids and as New Karu by Wikipedia, and INEC's office for the LGA is at Mararaba; the question is left open. Wikipedia describes the area as close to the Federal Capital Territory, with New Karu built originally to house civil servants and lower-income families of the capital, and as home mainly to the Gbagyi and Bassa. It adds that the state created the Karshi Development Area within the LGA, with its administrative secretariat at Uke."),
 "keana": ("Keana", "Keana", "Keana", 79253, 1048, "Keana", "S",
           "Keana is the seat of the Osana of Keana and, with Doma, Obi, Agwatashi and Assakio, one of the main towns of the Alago, according to Wikipedia, which calls it the \"home of salt\". Its traditional salt works, Keana Salt Village, are on the NCMM list of proposed national monuments. The Federal Government Girls College, Keana is in the town."),
 "keffi": ("Keffi", "Keffi", "Keffi", 92664, 138, "Keffi", "S",
           "Keffi is the seat of the Keffi Emirate and the smallest LGA of the state by area. Wikipedia places it about 50 km from Abuja and notes that Nasarawa State University lies on the Keffi–Akwanga road."),
 "kokona": ("Kokona", "Kokona", "Kokona", 109749, 1844, "Garaku", "WI",
            "Statoids gives the headquarters as Kokona, but Wikipedia names Garaku, and INEC places its office close to the LGA council secretariat in Garaku. Wikipedia describes the LGA as home to the Bassa and other peoples."),
 "lafia": ("Lafia", "Lafia", "Lafia", 330712, 2756, "Lafia", "S",
           "Lafia town is the capital of Nasarawa State and the seat of the Lafia Emirate. At the 2006 census the LGA was the most populous in the state."),
 "nasarawa": ("Nasarawa", "Nasarawa, Nasarawa State", "Nassarawa", 189835, 5704, "Nasarawa", "S",
              "Nasarawa town is the seat of the Nasarawa Emirate, from which the state takes its name. It is the largest LGA of the state by area (Wikipedia gives the same figure)."),
 "nasarawa-eggon": ("Nasarawa Eggon", "Eggon, Nigeria", "Nassarawa Egon", 149129, 1208, "Nasarawa Eggon", "SW",
                    "Wikipedia lists among its towns and districts Ende, Ginda, Alizaga, Arugbadu, Bakyano, Sako, Umme, Agunji, Alogani, Arikpa, Ezzen, Wakama, Angbaku, Buba, Galle, Gbamze, Ogbagi, Wogan and Ubbe."),
 "obi": ("Obi", "Obi, Nasarawa State", "Agwa Atashi (?)", 148874, 967, "Obi", "I",
         "INEC places its office close to the Obi LGA council secretariat in Obi town; Statoids gives the headquarters, with a question mark, as Agwa Atashi (Agwatashi). Wikipedia names Agwatashi, Daddare, Jenkwe, Duduguru, Agyaragu, Tudun Adabu, Gude and Riri among its other towns."),
 "toto": ("Toto", "Toto, Nigeria", "Toto", 119077, 2903, "Toto", "S",
          "Wikipedia describes the LGA as bounded by the Federal Capital Territory to the north, Nasarawa LGA to the east and Kogi State to the south and west, with three districts, Gadabuke, Toto and Umaisha. It names the Bassa and the Egbura as its main peoples, with smaller Gade, Gbagyi and Fulani communities."),
 "wamba": ("Wamba", "Wamba, Nigeria", "Wamba", 72894, 1156, "Wamba", "S",
           "Wamba had the smallest population of the state's LGAs at the 2006 census. Wikipedia describes it as a farming area known for cassava, pigeon pea and palm produce, with unexploited deposits of lead, barite, tantalite, columbite, aquamarine and gold. Among its peoples it names the Arum, Atoro, Buh, Kantana, Kulere, Ninzom, Ninkada, Rindre and Yashi."),
}
SOURCES["NCMMP"] = dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="Proposed National Monuments",
                        organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/proposed-national-monuments/",
                        verification_status="verified", notes="Reused (batch 034).")
for slug, (name, wt, *_r) in LGAS.items():
    SOURCES[f"W_{slug}"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=wt, organisation="Wikipedia", url=W(wt),
                                verification_status="needs_corroboration", notes=f"Wikipedia article on {name} LGA (introduction and infobox), consulted {ACCESSED}.")


def fmt(n):
    return f"{n:,}"


BY = {"S": "Statoids", "SI": "Statoids and INEC", "SW": "Statoids and Wikipedia", "WI": "Wikipedia and INEC", "I": "INEC"}


def text(slug):
    name, wt, s_hq, pop, area, hq, agree, extra = LGAS[slug]
    if agree == "conflict":
        first = f"{name} is a local government area of Nasarawa State."
    else:
        first = f"{name} is a local government area of Nasarawa State with its headquarters at {hq} (according to {BY[agree]})."
    fig = f" Statoids gives it an area of about {fmt(area)} km² and a population of {fmt(pop)} at the 2006 census."
    return (first + fig + (" " + extra if extra else "")).strip()


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), (f"W_{slug}", f"{name}: Wikipedia introduction")]
    if slug == "keana":
        srcs.append(("NCMMP", "Keana Salt Village: proposed national monument No. 90"))
    if "I" in agree or slug == "karu":
        srcs.append(("INECO", f"{name}: INEC LGA office address"))
    if hq and slug != "lafia":
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([(f"W_{slug}", f"Headquarters of {name}")] if "W" in agree else []) \
               + ([("INECO", f"LGA council secretariat at {hq}")] if "I" in agree else [])
        two = len(agree) >= 2
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if two else "single_reliable_source",
                            level="well_documented" if two else "reported",
                            fields=dict(place_type="town", name=hq, slug=re.sub(r"[^a-z0-9]+", "-", hq.lower()).strip("-"),
                                        admin_unit_id=f"@admin_units:lga:nasarawa/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Nasarawa State."),
                            srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    elif slug == "lafia":
        upd["headquarters_place_id"] = "@places:lafia"
    UPDATES.append(dict(ref=f"@admin_units:lga:nasarawa/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:nasarawa/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:nasarawa/{slug}", metric="area_km2", value_low=area, method="other",
                      notes="Area as given by Statoids" + (" (marked with an unexplained asterisk)" if slug in ("obi", "nasarawa") else ""),
                      source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_obi", name="Agwa Atashi", name_type="alternative", usage_notes="Headquarters given with a question mark by Statoids; Agwatashi is a town of Obi LGA.", srcs=["STAT"]),
    dict(record="hq_nasarawa-eggon", name="Nassarawa Egon", name_type="spelling_variant", usage_notes="Form given by Statoids.", srcs=["STAT"]),
    dict(record="hq_nasarawa-eggon", name="Eggon", name_type="alternative", usage_notes="Wikipedia: 'headquartered in the town of Eggon'.", srcs=["W_nasarawa-eggon"]),
    dict(record="hq_nasarawa", name="Nassarawa", name_type="spelling_variant", usage_notes="Form given by Statoids.", srcs=["STAT"]),
]
RELATIONS = []

GAPS = [
    ("Karu headquarters: Karu, New Karu or Mararaba", "Statoids gives Karu, Wikipedia New Karu, and INEC's LGA office is at Mararaba. Needs the Nasarawa State Government's list of LGA headquarters."),
    ("Obi headquarters", "Set to Obi from INEC's address 'close to Obi LG Council Secretariat, Obi'; Statoids gives 'Agwa Atashi (?)'. A second explicit source is wanted."),
    ("Creation dates of Nasarawa LGAs", "No creation dates were found in the sources used (several LGAs date from the 1996 creation of the state or later splits)."),
    ("Karshi Development Area", "Wikipedia says the state created a Karshi Development Area in Karu LGA (secretariat at Uke). Development areas are not constitutional LGAs; not recorded as a unit. Needs the state law."),
    ("Official headquarters list", "Headquarters rest on Statoids, Wikipedia and INEC's office addresses. An official Nasarawa State list would settle them."),
    ("Statoids asterisks", "Statoids marks the areas of Obi (967 km²) and Nasarawa (5,704 km²) with an asterisk it does not explain."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATS,
                scope="Nasarawa LGA profiles: headquarters, 2006 population and area, short sourced descriptions (13 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    places = [r for r in RECORDS if r["table"] == "places"]
    L = ["# Research batch 035 — Nasarawa LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Nasarawa. Created in review; published only after your approval.", "",
         f"- **13 LGA descriptions** filled (the field was empty), **{len(places)} headquarters towns** created as places (Lafia reuses the existing place), 13 × 2006 population and area figures.",
         "- **New official source:** INEC's list of its Nasarawa LGA offices (2012), which for four LGAs gives the council secretariat's town.",
         "- **Kokona → Garaku** (Wikipedia + INEC), not \"Kokona\" as Statoids has it. **Obi → Obi** (INEC), where Statoids has \"Agwa Atashi (?)\". **Karu left open** (Karu / New Karu / Mararaba).",
         "- Wikipedia errors not copied: Akwanga's '513,930 at the 2006 census' (census: 113,430); Lafia's '2021 census' (none was held).",
         "- The LGA pages stay under 300 words (noindex); sitemap unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Statoids | Wikipedia | INEC office | Level |", "|---|---|---|---|---|---|"]
    winf = {"karu": "New Karu", "kokona": "Garaku", "nasarawa-eggon": "Eggon"}
    inec = {"akwanga": "Akwanga", "awe": "Awe (Secretariat)", "doma": "Doma (LG Secretariat)", "karu": "Mararaba", "keana": "Keana", "keffi": "Keffi",
            "kokona": "Garaku (LG Council Secretariat)", "lafia": "Lafia", "nasarawa": "Nasarawa", "nasarawa-eggon": "Nasarawa Eggon",
            "obi": "Obi (LG Council Secretariat)", "toto": "Toto", "wamba": "Wamba"}
    for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
        lvl = "—" if agree == "conflict" else ("well documented" if len(agree) >= 2 else "reported")
        L.append(f"| {name} | {hq or '— (open)'} | {s_hq} | {winf.get(slug, 'not stated')} | {inec[slug]} | {lvl} |")
    L += ["", "## The 13 descriptions", ""]
    for slug in LGAS:
        L += [f"**{LGAS[slug][0]}** ({words(text(slug))} words). {text(slug)}", ""]
    L += ["## Sources", "", "- **STAT** — Statoids, Local Government Areas of Nigeria (2006 census; areas from the Nigeria Congress website). Reused.",
          "- **INECO** — INEC, Nasarawa State LGA office addresses (2012), https://www.inecnigeria.org/wp-content/uploads/2024/04/NASARAWA-STATE.pdf (via the Internet Archive). Tier 1.",
          "- **W_…** — the 13 Wikipedia LGA articles. Tier 3.", "",
          "## Not used", "", "- Wikipedia population figures that are not the 2006 census (Akwanga 513,930; Awe 116,080 '2005 estimate'; Lafia 509,300 '2021 census'; Doma 214,600 from citypopulation.de; Nasarawa town 30,949 '2016').",
          "- Postal codes, and named council chairmen and officials.",
          "- Wikipedia's spelling 'Reindeer' for the Rindre of Wamba (the archive's name, Rindre, is used).", "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_035_nasarawa_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_035_nasarawa_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len([r for r in RECORDS if r['table'] == 'places'])} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)} names={len(NAMES)}")
