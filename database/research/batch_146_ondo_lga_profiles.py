"""
Research batch 146 — Ondo LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
18 LGAs). Researched 2026-10-07. Pattern: batch 133 (Osun).

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.ON.*).
  W  Wikipedia, 'Ondo State': list of the 18 LGAs with headquarters.
  I  INEC, 'Ondo State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/ONDO-STATE.pdf via the
     Internet Archive; copy in data/inec_ondo_lga_offices.pdf). Counted where the office is in the headquarters town.
Differences: Akoko South-East and South-West — Statoids gives Oka and Isua; Wikipedia (list and LGA pages) and INEC
(offices at Isua and Oka-Akoko; INEC wards Isua I–IV in South-East, Oka I–V in South-West) give Isua and Oka, which are
used; Statoids' two rows appear to have their headquarters (and areas) exchanged. Irele — Wikipedia 'Ode-Irele'. Odigbo
— Statoids and Wikipedia Ore; INEC's office is 'off Ore Road, Odigbo'. Okitipupa — INEC's office is at 'Bolorunduro
junction, Akure Road'. Idanre — Statoids, Wikipedia's list and INEC give Owena; Wikipedia's Idanre page calls Idanre town
the headquarters. Akure North — 'Iju/Itaogbolu' (Statoids, Wikipedia); INEC at Iju. Reused place: Akure (#64).
Town slugs end in '-ondo'.
"""
import json, re, sys
import batch_142_ondo_languages_peoples as P142

ACCESSED = "2026-10-07"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Ondo rows (NG.ON.*): 2006 census, area, headquarters (the Akoko South-East and South-West rows give Oka and Isua, the reverse of Wikipedia and INEC)."),
    "INECS": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Ondo State: INEC LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/ONDO-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (7 October 2026); copy in database/research/data/inec_ondo_lga_offices.pdf.",
                  notes="LGA office addresses for all 18 LGAs; state office at Ola Akadiri Street, Alagbaka Quarters, Akure."),
    "WOND": dict(P142.SOURCES["WOND"], notes="Reused. List of the 18 LGAs with headquarters."),
}
# slug: (name, hq, statoids pop, area, agreement, inec office, extra)
LGAS = {
 "akoko-north-east": ("Akoko North-East", "Ikare", 175409, 372, "SWI", "Ikare", "Ikare was the headquarters of the old Akoko Division (Wikipedia). The Akpes and Ukaan languages are spoken in the LGA, besides Yoruba (Blench's Atlas; Wikipedia)."),
 "akoko-north-west": ("Akoko North-West", "Okeagbe", 213792, 512, "SWI", "Okeagbe", "Three small languages of the Akoko hills are spoken in its towns besides Yoruba: Arigidi (North Akoko), Ahan and Akpes (Blench's Atlas; Wikipedia)."),
 "akoko-south-east": ("Akoko South-East", "Isua", 82426, 530, "WI", "Isua Akoko", "Statoids gives Oka as the headquarters and about 530 km², while Wikipedia gives 225 km² of land; Statoids' rows for the two southern Akoko LGAs appear to be exchanged. Three Edoid languages are spoken here: Uhami at Isua, and Ehuẹun and Ukue at Epinmi (Blench's Atlas; INEC)."),
 "akoko-south-west": ("Akoko South-West", "Oka", 229486, 226, "WI", "Oka-Akoko", "Statoids gives Isua as the headquarters and about 226 km², while Wikipedia gives 340 km²; Statoids' rows for the two southern Akoko LGAs appear to be exchanged. Oka-Akoko is the seat of the Olubaka of Oka (Wikipedia)."),
 "akure-north": ("Akure North", "Iju/Itaogbolu", 131587, 660, "SWI", "Iju", "INEC's LGA office is at Iju."),
 "akure-south": ("Akure South", "Akure", 353211, 331, "SWI", "Akure", "Akure is the state capital and the seat of the Deji of Akure, whose old palace is a declared national monument; the National Museum Akure is also in the city (NCMM; Wikipedia). It had the largest population of the state's LGAs at the 2006 census (Statoids)."),
 "ese-odo": ("Ese Odo", "Igbekebo", 154978, 762, "SWI", "Igbekebo", "It is the Ijaw LGA of Ondo State, home of the Western Apoi and the Arogbo, who speak Ịzọn (Wikipedia; Blench's Atlas)."),
 "idanre": ("Idanre", "Owena", 129024, 1914, "SWI", "Owena", "Wikipedia's page on Idanre town calls the town the LGA's headquarters. Idanre Hill, whose cultural landscape is a proposed national monument, rises above the town (NCMM; Wikipedia). It had the largest area of the state's LGAs (Statoids)."),
 "ifedore": ("Ifedore", "Igbara-Oke", 176327, 295, "SWI", "Igbara Oke", "Two declared national monuments are in the LGA: the Iho Eleru cave near Isarun and the Igbara-Oke petroglyphs (NCMM; Wikipedia)."),
 "ilaje": ("Ilaje", "Igbokoda", 290615, 1318, "SWI", "Igbokoda", "The LGA covers Ondo State's Atlantic coast, and it is the home of the Ilaje, whose Ugbo Kingdom, ruled by the Olugbo, has its capital at Ode Ugbo (Wikipedia)."),
 "ile-oluji-okeigbo": ("Ile-Oluji-Okeigbo", "Ile-Oluji", 172870, 698, "SWI", "Ileoluji", ""),
 "irele": ("Irele", "Irele", 145166, 963, "SWI", "Irele", "Wikipedia writes Ode-Irele."),
 "odigbo": ("Odigbo", "Ore", 230351, 1818, "SW", "New Town, off Ore Road, Odigbo", "The Ashuba marble stone at Araromi Obu, a proposed national monument, is in the LGA (NCMM; INEC). Furupagha Ijaw communities live in its Ebijan ward (Wikipedia)."),
 "okitipupa": ("Okitipupa", "Okitipupa", 233565, 803, "SW", "Bolorunduro junction, Akure Road", "Okitipupa is the central town of the Ikale area (Wikipedia)."),
 "ondo-east": ("Ondo East", "Bolorunduro", 74758, 354, "SWI", "Bolorunduro", "It had the smallest population of the state's LGAs at the 2006 census (Statoids). Wikipedia gives an area of about 896 km²."),
 "ondo-west": ("Ondo West", "Ondo", 283672, 970, "SWI", "Ondo City", "Ondo is the seat of the Osemawe, ruler of the Ondo Kingdom (Wikipedia)."),
 "ose": ("Ose", "Ifon", 144901, 1465, "SWI", "Ifon", "Idoani, in the LGA, is the seat of the Alani and of the Idoani Confederacy, and one of its quarters speaks the Iyayu language (Wikipedia; Blench's Atlas)."),
 "owo": ("Owo", "Owo", 218886, 1027, "SWI", "Owo", "Owo is the seat of the Olowo, whose palace houses the National Museum Owo, and the town holds the Igogo festival every September (NCMM; Wikipedia)."),
}
GROUP = {
    "akoko-north-east": "Akoko", "akoko-north-west": "Akoko", "akoko-south-east": "Akoko", "akoko-south-west": "Akoko",
    "okitipupa": "Ikale", "irele": "Ikale", "ilaje": "Ilaje", "owo": "Owo", "idanre": "Idanre", "ose": "Ose",
}
PEOPLE = {
    "odigbo": " Its people are Yoruba of the Ondo lineage, with Ikale in parts, and Ijaw (Wikipedia).",
    "ese-odo": " Wikipedia's Ondo State page also lists the 'Ese Odo' among the Yoruba sub-groups.",
    "ile-oluji-okeigbo": " Its people are Yoruba; Oke-Igbo speaks a variety similar to the Ife dialect (Wikipedia).",
}
NAMES_OF = {"S": "Statoids", "W": "Wikipedia", "I": "INEC"}
REUSE = {"akure-south": "@places:akure"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SWI" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, hq, pop, area, agree, office, extra = LGAS[slug]
    t = f"{name} is a local government area of Ondo State with its headquarters at {hq} (according to {by(agree)})."
    if "I" not in agree:
        t += f" INEC's LGA office is at {office}."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    if extra: t += " " + extra
    if slug in PEOPLE:
        t += PEOPLE[slug]
    elif slug in GROUP:
        t += f" Its people are Yoruba, of the {GROUP[slug]} sub-group."
    elif slug != "ese-odo":
        t += " Its people are recorded as Yoruba; their sub-group is not named in a source read."
    return t


RECORDS, UPDATES, STATS, NAMES = [], [], [], []
for slug, (name, hq, pop, area, agree, office, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("WOND", f"{name}: headquarters"), ("INECS", f"{name}: INEC LGA office")]
    ref = f"@admin_units:lga:ondo/{slug}"
    if slug in REUSE:
        upd["headquarters_place_id"] = REUSE[slug]
    else:
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("WOND", f"Headquarters of {name}")] if "W" in agree else []) + ([("INECS", f"INEC LGA office at {office}")] if "I" in agree else [])
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources", level="well_documented",
                            fields=dict(place_type="town", name=hq, slug=slugify(hq) + "-ondo", admin_unit_id=ref, status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Ondo State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=ref, fields=upd, srcs=srcs))
    STATS.append(dict(record=ref, metric="population", value_low=pop, reference_year=2006, method="census", notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=ref, metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids" + (" (Statoids' southern Akoko rows appear exchanged; see the description)" if slug in ("akoko-south-east", "akoko-south-west") else ""),
                      source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_akoko-north-west", name="Oke-Agbe", name_type="spelling_variant", usage_notes="Wikipedia (Akoko North-West) and the Atlas write Oke-Agbe.", srcs=["WOND"]),
    dict(record="hq_akoko-south-west", name="Oka-Akoko", name_type="alternative", usage_notes="INEC and Wikipedia ('Oka-Akoko').", srcs=["INECS"]),
    dict(record="hq_akoko-south-east", name="Isua-Akoko", name_type="alternative", usage_notes="INEC ('Isua Akoko') and Wikipedia ('Isua-Akoko').", srcs=["INECS"]),
    dict(record="hq_irele", name="Ode-Irele", name_type="alternative", usage_notes="Wikipedia's list of LGA headquarters.", srcs=["WOND"]),
    dict(record="hq_akoko-north-east", name="Ikare-Akoko", name_type="alternative", usage_notes="Wikipedia ('Ìkàré-Àkókó').", srcs=["WOND"]),
]
GAPS = [
    ("Ondo: Statoids' southern Akoko rows", "Statoids gives Oka as the headquarters of Akoko South-East and Isua of Akoko South-West; Wikipedia, its LGA pages and INEC give the reverse, which is used. Statoids' areas for the two (530 and 226 km²) also look exchanged against Wikipedia (225 and 340 km²)."),
    ("Ondo: headquarters of Idanre, Odigbo and Okitipupa", "Wikipedia's Idanre page calls Idanre town the headquarters (Owena in the other sources); INEC's Odigbo and Okitipupa offices are outside the headquarters towns. A state government source would settle these."),
    ("Ondo: differing LGA figures", "Wikipedia's LGA pages give some different figures from Statoids (for example Ondo East about 896 km², Owo 222,262 people); only Statoids' figures are recorded."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES, relations=[], statistics=STATS,
                scope="Ondo LGA profiles: headquarters, 2006 population and area, short sourced descriptions (18 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    n3 = sum(1 for v in LGAS.values() if v[4] == "SWI")
    L = ["# Research batch 146 — Ondo LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Ondo. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **18 LGA descriptions** and **{len(RECORDS)} new headquarters towns**. Akure South reuses the existing Akure place. Town slugs end in '-ondo'.",
         "- **Agreement on headquarters:**",
         f"  - Statoids, Wikipedia and INEC agree for {n3} LGAs.",
         "  - **Akoko South-East (Isua) and Akoko South-West (Oka):** Wikipedia and INEC agree. Statoids has the two the other way round, which is noted in each description.",
         "  - **Odigbo (Ore) and Okitipupa:** Statoids and Wikipedia agree; INEC's offices are elsewhere.",
         "  - **Idanre:** Owena in all three sources, though Wikipedia's town page calls Idanre the headquarters.",
         "- **Each description names** the LGA's people where a source gives them, its smaller languages, and any seat of a ruler, monument or festival recorded in batches 142–145.",
         "- **Figures:** 2006 population and area from Statoids.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## The 18 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_146_ondo_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_146_ondo_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)}")
