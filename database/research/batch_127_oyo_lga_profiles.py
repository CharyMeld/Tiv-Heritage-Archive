"""
Research batch 127 — Oyo LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
33 LGAs). Researched 2026-10-02. Pattern: batch 121 (Ogun).

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.OY.*).
  W  Wikipedia, 'Oyo State': the list of LGAs with their headquarters (Oke-Ogun ones written '-Okeogun').
  I  INEC, 'Oyo State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/OYO-STATE.pdf via the
     Internet Archive; copy in data/inec_oyo_lga_offices.pdf). Counted where the office is in the headquarters town.
Disagreements: Ibadan South-West — Statoids 'Oluyole' and INEC 'Oluyole Extension', Wikipedia 'Ring Road' → Oluyole,
noted. Ogbomosho North — Wikipedia 'Ogbomoso' and INEC (Oke Owode, Ogbomoso), Statoids 'Sabo' → Ogbomoso, noted.
Surulere — Wikipedia 'Iresa Adu' and INEC 'Iresaadu'; Statoids gives only 'Surulere' → Iresa Adu. Atiba — Statoids 'Ofa
Mefa', Wikipedia 'Ofa Meta' (and 'Offa-Meta' in 'Oyo, Oyo State'), INEC at Boroboro, Oyo → Ofa Meta.
The existing place 'Ibadan' is the city as a whole and is not reused. Town slugs end in '-oyo'.
"""
import json, re, sys
import batch_123_oyo_languages_peoples as P123

ACCESSED = "2026-10-02"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Oyo rows (NG.OY.*): 2006 census, area, headquarters."),
    "INECY": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Oyo State: INEC LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/OYO-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (2 October 2026); copy in database/research/data/inec_oyo_lga_offices.pdf.",
                  notes="LGA office addresses for all 33 LGAs; state headquarters on Parliament Road, Agodi Gate, Ibadan."),
    "WOYS": dict(P123.SOURCES["WOYS"], notes="Reused. List of the 33 LGAs with their headquarters."),
}
GROUP = {l: g for g, (ls, _s, _w) in P123.GROUPS.items() for l in ls}
# slug: (name, hq, statoids pop, area, agreement, inec office place, extra)
LGAS = {
 "afijio": ("Afijio", "Jobele", 134173, 722, "SWI", "Jobele", ""),
 "akinyele": ("Akinyele", "Moniya", 211359, 518, "SWI", "Moniya", ""),
 "atiba": ("Atiba", "Ofa Meta", 169702, 1757, "SW", "Boroboro, Oyo", "Statoids writes Ofa Mefa."),
 "atisbo": ("Atisbo", "Tede", 110792, 2997, "SWI", "Tede", "It had the largest area of the state's LGAs (Statoids)."),
 "egbeda": ("Egbeda", "Egbeda", 281573, 191, "SW", "Ife Road, off Oluwo bus stop", ""),
 "ibadan-north": ("Ibadan North", "Agodi", 306795, 27, "SWI", "Agodi Gate", ""),
 "ibadan-north-east": ("Ibadan North-East", "Iwo Road", 330399, 18, "SW", "Agugu–Oremeji road", "It had the largest population of the state's LGAs at the 2006 census (Statoids)."),
 "ibadan-north-west": ("Ibadan North-West", "Onireke", 152834, 26, "SWI", "Onireke", "Statoids and Wikipedia give Dugbe/Onireke. Cocoa House, West Africa's first skyscraper, stands at Dugbe (Wikipedia)."),
 "ibadan-south-east": ("Ibadan South-East", "Mapo", 266046, 17, "SW", "Orita Aperin", "Mapo Hall, Ibadan's colonial city hall and a proposed national monument, stands on Mapo Hill (NCMM; Wikipedia)."),
 "ibadan-south-west": ("Ibadan South-West", "Oluyole", 282585, 40, "SI", "Oluyole Extension", "Wikipedia gives Ring Road instead."),
 "ibarapa-central": ("Ibarapa Central", "Igbo-Ora", 102979, 440, "SW", "Igbo-Ora–Idere road", ""),
 "ibarapa-east": ("Ibarapa East", "Eruwa", 118226, 838, "SWI", "Eruwa", ""),
 "ibarapa-north": ("Ibarapa North", "Ayete", 101092, 1218, "SWI", "Ayete", ""),
 "ido": ("Ido", "Ido", 103261, 986, "SWI", "Ido", ""),
 "irepo": ("Irepo", "Kishi", 122553, 984, "SWI", "Kishi", "Statoids and Wikipedia write Kisi."),
 "iseyin": ("Iseyin", "Iseyin", 256926, 1348, "SWI", "Iseyin", "The home of Bishop Ajayi Crowther at Iseyin is a proposed national monument (NCMM)."),
 "itesiwaju": ("Itesiwaju", "Otu", 128652, 1514, "SWI", "Otu", ""),
 "iwajowa": ("Iwajowa", "Iwere-Ile", 102980, 2529, "SWI", "Iwere-Ile", ""),
 "kajola": ("Kajola", "Okeho", 200997, 609, "SWI", "Okeho", ""),
 "lagelu": ("Lagelu", "Iyana Offa", 147957, 338, "SWI", "Iyana Offa", ""),
 "ogbomosho-north": ("Ogbomosho North", "Ogbomoso", 198720, 185, "WI", "Oke Owode, Ogbomoso", "Statoids gives Sabo, a quarter of Ogbomoso. Ogbomoso is the seat of the Soun of Ogbomoso (Wikipedia)."),
 "ogbomosho-south": ("Ogbomosho South", "Arowomole", 100815, 68, "SWI", "Arowomole", ""),
 "ogo-oluwa": ("Ogo Oluwa", "Ajaawa", 65184, 369, "SWI", "Ajaawa", "It had the smallest population of the state's LGAs at the 2006 census (Statoids)."),
 "olorunsogo": ("Olorunsogo", "Igbeti", 81759, 1069, "SWI", "Igbeti", ""),
 "oluyole": ("Oluyole", "Idi-Ayunre", 202725, 629, "SWI", "Idi-Ayunre", ""),
 "ona-ara": ("Ona Ara", "Akanran", 265059, 290, "SWI", "Akanran", ""),
 "orelope": ("Orelope", "Igboho", 104441, 917, "SWI", "Igboho", ""),
 "ori-ire": ("Ori Ire", "Ikoyi-Ile", 150628, 2116, "SWI", "Ikoyi-Ile", ""),
 "oyo-east": ("Oyo East", "Kosobo", 123846, 144, "SW", "Akunlemu", ""),
 "oyo-west": ("Oyo West", "Ojongbodu", 136236, 526, "SWI", "Ojongbodu", ""),
 "saki-east": ("Saki East", "Ago-Amodu", 110223, 1569, "SWI", "Ago-Amodu", ""),
 "saki-west": ("Saki West", "Saki", 278002, 2014, "SWI", "Saki", "Wikipedia also writes Shaki."),
 "surulere": ("Surulere", "Iresa Adu", 142070, 23, "WI", "Iresa Adu", "Statoids gives only 'Surulere'."),
}
NAMES_OF = {"S": "Statoids", "W": "Wikipedia", "I": "INEC"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SWI" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, hq, pop, area, agree, office, extra = LGAS[slug]
    t = f"{name} is a local government area of Oyo State with its headquarters at {hq} (according to {by(agree)})."
    if "I" not in agree:
        t += f" INEC's LGA office is at {office}."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    if extra: t += " " + extra
    g = GROUP.get(slug)
    t += f" Its people are Yoruba, of the {g} group named in the federal government's state profile." if g else " Its people are recorded as Yoruba; their group is not named in a source read."
    return t


RECORDS, UPDATES, STATS, NAMES = [], [], [], []
for slug, (name, hq, pop, area, agree, office, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("WOYS", f"{name}: headquarters"), ("INECY", f"{name}: INEC LGA office")]
    key = f"hq_{slug}"
    hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("WOYS", f"Headquarters of {name}")] if "W" in agree else []) + ([("INECY", f"INEC LGA office at {office}")] if "I" in agree else [])
    RECORDS.append(dict(key=key, table="places", evidence="multiple_sources", level="well_documented",
                        fields=dict(place_type="town", name=hq, slug=slugify(hq) + "-oyo", admin_unit_id=f"@admin_units:lga:oyo/{slug}", status="existing",
                                    summary=f"{hq} is the headquarters of {name} Local Government Area, Oyo State."), srcs=hsrc))
    upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:oyo/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:oyo/{slug}", metric="population", value_low=pop, reference_year=2006, method="census", notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:oyo/{slug}", metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_atiba", name="Ofa Mefa", name_type="spelling_variant", usage_notes="Statoids' spelling; also 'Offa-Meta' (Wikipedia, Oyo).", srcs=["STAT"]),
    dict(record="hq_irepo", name="Kisi", name_type="spelling_variant", usage_notes="Statoids' and Wikipedia's spelling.", srcs=["STAT"]),
    dict(record="hq_saki-west", name="Shaki", name_type="spelling_variant", usage_notes="Wikipedia's spelling.", srcs=["WOYS"]),
    dict(record="hq_ibadan-north-west", name="Dugbe/Onireke", name_type="alternative", usage_notes="Statoids and Wikipedia give the headquarters as Dugbe/Onireke.", srcs=["STAT"]),
]
GAPS = [("Oyo: disputed headquarters", "Ibadan South-West (Oluyole per Statoids and INEC; Ring Road per Wikipedia), Ogbomosho North (Ogbomoso per Wikipedia and INEC; Sabo per Statoids) and Surulere (Iresa Adu; Statoids gives only 'Surulere') need a state government source."),
        ("Oyo: Ibadan LGA headquarters", "The Ibadan LGAs are headquartered in districts of the city (Agodi, Iwo Road, Onireke, Mapo, Oluyole); these are recorded as places of type 'town', as for other headquarters.")]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES, relations=[], statistics=STATS,
                scope="Oyo LGA profiles: headquarters, 2006 population and area, short sourced descriptions (33 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 127 — Oyo LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Oyo. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **33 LGA descriptions** and **{len(RECORDS)} headquarters towns**. Town slugs end in '-oyo'. The existing 'Ibadan' place is the whole city, so it is not reused.",
         "- **Agreement on headquarters:** Statoids, Wikipedia and INEC agree for 24 LGAs; Statoids and Wikipedia agree for 6 more. They differ for:",
         "  - **Ibadan South-West:** Oluyole (Statoids and INEC) or Ring Road (Wikipedia)",
         "  - **Ogbomosho North:** Ogbomoso (Wikipedia and INEC) or Sabo (Statoids)",
         "  - **Surulere:** Iresa Adu (Wikipedia and INEC)",
         "  - **Atiba:** Ofa Meta (also spelled Ofa Mefa or Offa-Meta)",
         "- **Each description names the LGA's Yoruba group** from the federal profile, where a source gives it.",
         "- **Figures:** 2006 population and area from Statoids.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## The 33 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_127_oyo_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_127_oyo_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)}")
