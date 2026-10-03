"""
Research batch 133 — Osun LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
30 LGAs). Researched 2026-10-02. Pattern: batch 127 (Oyo).

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.OS.*; the areas of Ifelodun and Irepodun are
     marked with an asterisk by Statoids).
  W  Wikipedia, 'Osun State': table of the 30 LGA headquarters.
  I  INEC, 'Osun State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/OSUN-STATE.pdf via the
     Internet Archive; copy in data/inec_osun_lga_offices.pdf). Counted where the office is in the headquarters town.
Differences: Ilesha East — Wikipedia 'Ilesa' and INEC (Iyemogun Road, Ilesa), Statoids 'Iyemogun' → Ilesa, noted.
Ilesha West — Statoids 'Oja Oba (Ereja Square)', Wikipedia 'Ereja Square' → Ereja Square. Ede North — Statoids 'Oja
Timi Ede', Wikipedia 'Oja Timi' → Oja Timi. Olorunda — Statoids 'Igbona', Wikipedia 'Igbonna, Osogbo' → Igbona. Ife East
— Statoids and Wikipedia 'Oke-Ogbo'; INEC at Opa, Ile-Ife. Town slugs end in '-osun'. Reused place: Oshogbo (the
state capital) for Osogbo LGA.
"""
import json, re, sys
import batch_129_osun_languages_peoples as P129

ACCESSED = "2026-10-02"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Osun rows (NG.OS.*): 2006 census, area, headquarters."),
    "INECS": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Osun State: INEC LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/OSUN-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (2 October 2026); copy in database/research/data/inec_osun_lga_offices.pdf.",
                  notes="LGA office addresses for all 30 LGAs (and an Ife East area office at Modakeke); state headquarters at Abere, Osogbo."),
    "WOSS": dict(P129.SOURCES["WOSS"], notes="Reused. Table of the 30 LGA headquarters."),
}
GROUP = {l: g for g, (ls, _s, _w) in P129.GROUPS.items() for l in ls}
STAR = {"ifelodun", "irepodun"}
# slug: (name, hq, statoids pop, area, agreement, inec office place, extra)
LGAS = {
 "aiyedade": ("Aiyedade", "Gbongan", 150392, 1113, "SWI", "Gbongan", "It had the largest area of the state's LGAs (Statoids)."),
 "aiyedire": ("Aiyedire", "Ile-Ogbo", 75846, 262, "SWI", "Ile-Ogbo", ""),
 "atakunmosa-east": ("Atakunmosa East", "Iperindo", 76197, 238, "SWI", "Iperindo", ""),
 "atakunmosa-west": ("Atakunmosa West", "Osu", 68643, 577, "SWI", "Osu", ""),
 "boluwaduro": ("Boluwaduro", "Otan Ayegbaju", 70775, 144, "SW", "the LGA quarters (no town named)", "The carved stone figure at Igbajo, in this LGA, is a declared national monument (NCMM; Wikipedia)."),
 "boripe": ("Boripe", "Iragbiji", 139358, 132, "SWI", "Iragbiji", ""),
 "ede-north": ("Ede North", "Oja Timi", 83831, 111, "SW", "Ede", "Statoids writes 'Oja Timi Ede'."),
 "ede-south": ("Ede South", "Ede", 76035, 219, "SWI", "Ede", ""),
 "egbedore": ("Egbedore", "Awo", 74435, 270, "SWI", "Awo", ""),
 "ejigbo": ("Ejigbo", "Ejigbo", 132641, 373, "SWI", "Ejigbo", ""),
 "ife-central": ("Ife Central", "Ile-Ife", 167254, 111, "SWI", "Ile-Ife", "Ile-Ife is the seat of the Ooni of Ife (Wikipedia)."),
 "ife-east": ("Ife East", "Oke-Ogbo", 188087, 172, "SW", "Opa, Ile-Ife", ""),
 "ife-north": ("Ife North", "Ipetumodu", 153694, 889, "SWI", "Ipetumodu", ""),
 "ife-south": ("Ife South", "Ifetedo", 135338, 730, "SWI", "Ifetedo", ""),
 "ifedayo": ("Ifedayo", "Oke-Ila", 37058, 128, "SWI", "Oke-Ila", "It had the smallest population of the state's LGAs at the 2006 census (Statoids). Wikipedia writes Oke-Ila Orangun."),
 "ifelodun": ("Ifelodun", "Ikirun", 96748, 114, "SWI", "Ikirun", ""),
 "ila": ("Ila", "Ila Orangun", 62049, 303, "SWI", "Ila", "Ila Orangun is the seat of the Orangun of Ila (Wikipedia)."),
 "ilesha-east": ("Ilesha East", "Ilesa", 106586, 71, "WI", "Iyemogun Road, Ilesa", "Statoids gives Iyemogun, a part of Ilesa. Ilesa is the capital of Ijesaland, the seat of the Owa Obokun (Wikipedia)."),
 "ilesha-west": ("Ilesha West", "Ereja Square", 103555, 63, "SW", "Omi-Aladie, Ilesa", "Statoids writes 'Oja Oba (Ereja Square)'."),
 "irepodun": ("Irepodun", "Ilobu", 119497, 64, "SWI", "Ilobu", ""),
 "irewole": ("Irewole", "Ikire", 143599, 271, "SWI", "Ikire", ""),
 "isokan": ("Isokan", "Apomu", 103177, 179, "SWI", "Apomu", ""),
 "iwo": ("Iwo", "Iwo", 191377, 214, "SWI", "Iwo", "It had the largest population of the state's LGAs at the 2006 census (Statoids)."),
 "obokun": ("Obokun", "Ibokun", 116511, 527, "SWI", "Ibokun", ""),
 "odo-otin": ("Odo Otin", "Okuku", 134110, 294, "SWI", "Okuku", ""),
 "ola-oluwa": ("Ola Oluwa", "Bode Osi", 76593, 328, "SWI", "Bode Osi", ""),
 "olorunda": ("Olorunda", "Igbona", 131761, 97, "SW", "Powerline area, Osogbo", "Wikipedia writes 'Igbonna, Osogbo'."),
 "oriade": ("Oriade", "Ijebu-Jesa", 148617, 465, "SWI", "Ijebu-Jesa", "The Erin-Ijesa (Olumirin) waterfalls, a proposed national monument, are in this LGA (NCMM; Wikipedia)."),
 "orolu": ("Orolu", "Ifon", 103077, 80, "SWI", "Ifon-Osun", "Wikipedia writes Ifon Osun."),
 "osogbo": ("Osogbo", "Osogbo", 156694, 47, "SWI", "Osogbo", "Osogbo is the state capital and the seat of the Ataoja; the Osun-Osogbo Sacred Grove, a UNESCO World Heritage Site, lies just outside the city (Wikipedia; NCMM)."),
}
NAMES_OF = {"S": "Statoids", "W": "Wikipedia", "I": "INEC"}
REUSE = {"osogbo": "@places:oshogbo"}  # the state capital (place #65, 'Oshogbo', alias Osogbo)


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SWI" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, hq, pop, area, agree, office, extra = LGAS[slug]
    t = f"{name} is a local government area of Osun State with its headquarters at {hq} (according to {by(agree)})."
    if "I" not in agree:
        t += f" INEC's LGA office is at {office}."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids{', which marks the area figure with an asterisk' if slug in STAR else ''})."
    if extra: t += " " + extra
    g = GROUP.get(slug)
    t += f" Its people are Yoruba, of the {g} sub-group." if g else " Its people are recorded as Yoruba; their sub-group is not named in a source read."
    return t


RECORDS, UPDATES, STATS, NAMES = [], [], [], []
for slug, (name, hq, pop, area, agree, office, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("WOSS", f"{name}: headquarters"), ("INECS", f"{name}: INEC LGA office")]
    if slug in REUSE:
        upd["headquarters_place_id"] = REUSE[slug]
        UPDATES.append(dict(ref=f"@admin_units:lga:osun/{slug}", fields=upd, srcs=srcs))
        STATS.append(dict(record=f"@admin_units:lga:osun/{slug}", metric="population", value_low=pop, reference_year=2006, method="census", notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
        STATS.append(dict(record=f"@admin_units:lga:osun/{slug}", metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
        continue
    key = f"hq_{slug}"
    hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("WOSS", f"Headquarters of {name}")] if "W" in agree else []) + ([("INECS", f"INEC LGA office at {office}")] if "I" in agree else [])
    RECORDS.append(dict(key=key, table="places", evidence="multiple_sources", level="well_documented",
                        fields=dict(place_type="town", name=hq, slug=slugify(hq) + "-osun", admin_unit_id=f"@admin_units:lga:osun/{slug}", status="existing",
                                    summary=f"{hq} is the headquarters of {name} Local Government Area, Osun State."), srcs=hsrc))
    upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:osun/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:osun/{slug}", metric="population", value_low=pop, reference_year=2006, method="census", notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:osun/{slug}", metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids" + (" (marked with an asterisk)" if slug in STAR else ""), source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_boluwaduro", name="Otan Aiyegbaju", name_type="spelling_variant", usage_notes="Statoids' spelling.", srcs=["STAT"]),
    dict(record="hq_ilesha-west", name="Oja Oba", name_type="alternative", usage_notes="Statoids: 'Oja Oba (Ereja Square)'.", srcs=["STAT"]),
    dict(record="hq_olorunda", name="Igbonna", name_type="spelling_variant", usage_notes="Wikipedia's spelling.", srcs=["WOSS"]),
    dict(record="hq_ilesha-east", name="Ilesha", name_type="spelling_variant", usage_notes="Also written Ilesha (Wikipedia, Ijesha).", srcs=["WOSS"]),
]
GAPS = [("Osun: headquarters within Ilesa and Osogbo", "Statoids gives Iyemogun for Ilesha East and Oja Oba (Ereja Square) for Ilesha West; Olorunda's headquarters, Igbona, is a part of Osogbo. A state government source would settle the exact seats."),
        ("Osun: asterisked areas", "Statoids marks the areas of Ifelodun and Irepodun with an asterisk; the reason is not stated in the rows read.")]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES, relations=[], statistics=STATS,
                scope="Osun LGA profiles: headquarters, 2006 population and area, short sourced descriptions (30 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    n3 = sum(1 for v in LGAS.values() if v[4] == "SWI")
    L = ["# Research batch 133 — Osun LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Osun. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **30 LGA descriptions** and **{len(RECORDS)} new headquarters towns**. Osogbo reuses the existing state-capital place, Oshogbo. Town slugs end in '-osun'.",
         f"- **Agreement on headquarters:**",
         f"  - Statoids, Wikipedia and INEC agree for {n3} LGAs.",
         "  - **Ilesha East:** Ilesa (Wikipedia and INEC); Statoids gives Iyemogun, a part of Ilesa.",
         "  - **Ilesha West, Ede North, Olorunda and Ife East:** the headquarters are districts of Ilesa, Ede, Osogbo and Ife. Statoids and Wikipedia agree; INEC's offices are elsewhere in the same towns.",
         "  - **Boluwaduro:** Otan Ayegbaju (Statoids and Wikipedia).",
         "- **Each description names** the LGA's Yoruba sub-group where a source gives it, and any seat of a ruler or monument recorded there.",
         "- **Figures:** 2006 population and area from Statoids. Statoids marks two area figures with an asterisk; this is noted.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## The 30 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_133_osun_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_133_osun_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)}")
