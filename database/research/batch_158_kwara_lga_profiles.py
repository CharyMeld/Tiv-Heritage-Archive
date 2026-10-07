"""
Research batch 158 — Kwara LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
16 LGAs). Researched 2026-10-07. Pattern: batches 133, 146, 152.

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.KW.*).
  W  Wikipedia's LGA pages (the 'Kwara State' page lists the LGAs without headquarters).
  I  INEC, 'Kwara State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/KWARA-STATE.pdf via the
     Internet Archive; copy in data/inec_kwara_lga_offices.pdf). Counted where the office is in the headquarters town.
Differences: Ilorin West — Statoids 'Oja Oba', Wikipedia 'Wara Osin Area', INEC office at Ipata Oloje, Ilorin; the
existing Ilorin place (#59) is used and the other two are noted. Ekiti — Statoids and Wikipedia Araromi Opin; INEC's office is at
Osi. Ilorin East — Statoids and Wikipedia Oke Oyi; INEC's office is in Ilorin (Akerebiata). Kaiama and Moro — Wikipedia's
LGA pages do not name a headquarters. Town slugs end in '-kwara'.
"""
import json, re, sys
import batch_154_kwara_languages_peoples as P154

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Kwara rows (NG.KW.*): 2006 census, area, headquarters."),
    "INECS": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Kwara State: INEC LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/KWARA-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (7 October 2026); copy in database/research/data/inec_kwara_lga_offices.pdf.",
                  notes="LGA office addresses for all 16 LGAs; state office on Western Reservoir Road, Adewole area, Ilorin."),
    "WLGA": WS("Kwara State", "LGA list and a table of the main language of each LGA (Baatonum in Baruten, Nupe in Edu and Pategi, Bokobaru in Kaiama, Yoruba elsewhere). Headquarters taken from each LGA's own Wikipedia page."),
    "WILW": WS("Ilorin West", "LGA; 'Its headquarters are in the town of Wara Osin Area'."),
}
# slug: (name, hq, statoids pop, area, agreement, inec office, extra)
LGAS = {
 "asa": ("Asa", "Afon", 126435, 1286, "SWI", "Afon", ""),
 "baruten": ("Baruten", "Kosubosu", 209459, 9749, "SWI", "Kosubosu", "It borders Benin Republic and is the home of the Bariba, whose language, Baatọnum, is its main language; Busa and Bokobaru are also spoken (Wikipedia; Blench's Atlas). The old West African Frontier Force forts at Okuta and Yashikera are declared national monuments (NCMM). It had the largest area of the state's LGAs (Statoids)."),
 "edu": ("Edu", "Lafiagi", 201469, 2542, "SWI", "Lafiagi", "It is a Nupe-speaking area, and Lafiagi is the seat of the Emir of Lafiagi (Wikipedia; New Telegraph)."),
 "ekiti": ("Ekiti", "Araromi Opin", 54850, 480, "SW", "Osi", "It had the smallest population of the state's LGAs at the 2006 census (Statoids)."),
 "ifelodun": ("Ifelodun", "Share", 206042, 3435, "SWI", "Share", ""),
 "ilorin-east": ("Ilorin East", "Oke Oyi", 204310, 486, "SW", "Akerebiata, Sobi Road, Ilorin", ""),
 "ilorin-south": ("Ilorin South", "Fufu", 208691, 174, "SWI", "Fufu", ""),
 "ilorin-west": ("Ilorin West", "Ilorin", 364666, 105, "I", "Ipata Oloje, Ilorin", "Statoids gives Oja Oba and Wikipedia the Wara Osin area. Ilorin is the state capital and the seat of the Emir of Ilorin. It had the largest population of the state's LGAs at the 2006 census (Statoids)."),
 "irepodun": ("Irepodun", "Omu-Aran", 148610, 737, "SWI", "Omu-Aran", "The Esie Museum, which opened in 1945 as Nigeria's first museum and holds the Esie stone images, is in the LGA (Wikipedia; NCMM)."),
 "isin": ("Isin", "Owu-Isin", 59738, 633, "SWI", "Owu-Isin", "It was created from the old Irepodun LGA in 1996; the stone figures at Ijara are a declared national monument (Wikipedia; NCMM)."),
 "kaiama": ("Kaiama", "Kaiama", 124164, 6971, "SI", "Kaiama", "Bokobaru is its major language, except in Adena ward, where Yoruba and Hausa are spoken, and Bani ward, where Fulani is spoken (Wikipedia)."),
 "moro": ("Moro", "Bode Saadu", 108792, 3272, "SI", "Bode-Sadu", "The Kwara side of Jebba is in the LGA, with the relics of the steamer Dayspring, a declared national monument (Wikipedia; NCMM)."),
 "offa": ("Offa", "Offa", 89674, 95, "SWI", "Offa", "Offa is the seat of the Olofa and the cultural headquarters of the Ibolo; it holds the Onimoka festival (Wikipedia). It had the smallest area of the state's LGAs (Statoids)."),
 "oke-ero": ("Oke Ero", "Iloffa", 57619, 438, "SWI", "Illofa", ""),
 "oyun": ("Oyun", "Ilemona", 94253, 476, "SWI", "Ilemona", ""),
 "pategi": ("Pategi", "Pategi", 112317, 2913, "SWI", "Patigi", "Pategi is the seat of the Etsu Pategi, a Nupe emirate founded in 1898, and holds the Pategi Regatta on the Niger (Wikipedia)."),
}
PEOPLE = {
    "asa": " Wikipedia names the Yoruba, Hausa and Fulani among its peoples.",
    "baruten": "",
    "edu": " Its people are Nupe (Wikipedia).",
    "ekiti": " Its people are recorded as Yoruba; their sub-group is not named in a source read.",
    "ifelodun": " Its people are Yoruba, mostly of Igbomina origin (Wikipedia).",
    "ilorin-east": " Ilorin is mainly Yoruba (Wikipedia).",
    "ilorin-south": " Ilorin is mainly Yoruba (Wikipedia).",
    "ilorin-west": " Ilorin is mainly Yoruba (Wikipedia).",
    "irepodun": " Its people are Yoruba of the Igbomina sub-group (Wikipedia).",
    "isin": " Its people are Yoruba of the Igbomina sub-group (Wikipedia).",
    "kaiama": "",
    "moro": " Wikipedia gives Yoruba as its main language.",
    "offa": " Its people are Yoruba of the Ibolo sub-group (Wikipedia).",
    "oke-ero": " Its people are Yoruba of the Ekiti sub-group, akin to the people of Moba in Ekiti State (Wikipedia).",
    "oyun": " Its people are Yoruba of the Ibolo sub-group (Wikipedia).",
    "pategi": " Its people are mainly Nupe (Wikipedia).",
}
NAMES_OF = {"S": "Statoids", "W": "Wikipedia", "I": "INEC"}
REUSE = {"ilorin-west": "@places:ilorin"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SWI" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, hq, pop, area, agree, office, extra = LGAS[slug]
    t = f"{name} is a local government area of Kwara State with its headquarters at {hq} (according to {by(agree)})."
    if "I" not in agree:
        t += f" INEC's LGA office is at {office}."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    if extra: t += " " + extra
    t += PEOPLE[slug]
    return t


RECORDS, UPDATES, STATS = [], [], []
for slug, (name, hq, pop, area, agree, office, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("WLGA", f"{name}: main language"), ("INECS", f"{name}: INEC LGA office")]
    if slug == "ilorin-west":
        srcs.append(("WILW", "Ilorin West: headquarters (Wara Osin area)"))
    ref = f"@admin_units:lga:kwara/{slug}"
    if slug in REUSE:
        upd["headquarters_place_id"] = REUSE[slug]
    else:
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("WLGA", f"Headquarters of {name} (Wikipedia LGA page)")] if "W" in agree else []) + ([("INECS", f"INEC LGA office at {office}")] if "I" in agree else [])
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources", level="well_documented",
                            fields=dict(place_type="town", name=hq, slug=slugify(hq) + "-kwara", admin_unit_id=ref, status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Kwara State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=ref, fields=upd, srcs=srcs))
    STATS.append(dict(record=ref, metric="population", value_low=pop, reference_year=2006, method="census", notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=ref, metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES = [
    dict(record="hq_moro", name="Bode Sadu", name_type="spelling_variant", usage_notes="Statoids' and INEC's spelling.", srcs=["STAT"]),
    dict(record="hq_isin", name="Owu", name_type="alternative", usage_notes="Statoids gives 'Owu'.", srcs=["STAT"]),
    dict(record="hq_oke-ero", name="Illofa", name_type="spelling_variant", usage_notes="INEC's spelling.", srcs=["INECS"]),
    dict(record="hq_pategi", name="Patigi", name_type="spelling_variant", usage_notes="INEC's and the federal profile's spelling.", srcs=["INECS"]),
]
GAPS = [
    ("Kwara: Ilorin West's headquarters", "Statoids gives Oja Oba, Wikipedia the Wara Osin area, and INEC's office is at Ipata Oloje, Ilorin; the existing Ilorin place is used, and the exact seat needs a state source."),
    ("Kwara: headquarters of Ekiti, Ilorin East, Kaiama and Moro", "INEC's Ekiti office is at Osi and its Ilorin East office in Ilorin; Wikipedia's Kaiama and Moro pages name no headquarters. A state government list would settle these."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES, relations=[], statistics=STATS,
                scope="Kwara LGA profiles: headquarters, 2006 population and area, short sourced descriptions (16 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    n3 = sum(1 for v in LGAS.values() if v[4] == "SWI")
    L = ["# Research batch 158 — Kwara LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Kwara. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **16 LGA descriptions** and **{len(RECORDS)} new headquarters towns**. Ilorin West uses the existing Ilorin place. New town slugs end in '-kwara'.",
         "- **Agreement on headquarters:**",
         f"  - Statoids, Wikipedia's LGA pages and INEC agree for {n3} LGAs.",
         "  - **Ilorin West:** Ilorin, from INEC (office at Ipata Oloje, Ilorin). Statoids gives Oja Oba and Wikipedia the Wara Osin area; both are noted.",
         "  - **Ekiti** (Araromi Opin) and **Ilorin East** (Oke Oyi): Statoids and Wikipedia agree; INEC's offices are elsewhere.",
         "  - **Kaiama** (Kaiama) and **Moro** (Bode Saadu): Statoids and INEC agree; Wikipedia gives no headquarters.",
         "- **Each description** names the LGA's people and languages from batch 154, and any seat of a ruler, monument or festival from batches 156–157.",
         "- **Figures:** 2006 population and area from Statoids.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## The 16 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_158_kwara_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_158_kwara_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)}")
