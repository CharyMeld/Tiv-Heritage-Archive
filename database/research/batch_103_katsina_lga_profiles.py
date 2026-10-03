"""
Research batch 103 — Katsina LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
34 LGAs). Researched 2026-10-02. Pattern: batches 091 and 097.

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.KT.*).
  I  INEC, 'Katsina State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/KATSINA-STATE.pdf via
     the Internet Archive; copy in data/inec_katsina_lga_offices.pdf). Counted where the office is by or on the road to
     the LGA secretariat in a named town: Batsari, Dandume, Dan Musa, Jibia, Kankara, Kankia, Kurfi, Mai'Adua,
     Malumfashi, Matazu.
  W  Wikipedia's LGA article, where it names the headquarters explicitly: Batagarawa, Charanchi, Danja, Dutsin-Ma,
     Faskari.
Reused place: Katsina. Not used: Wikipedia's 641,576 for Bakori (Statoids 149,371) and 282 km² for Sandamu (the same as
Safana's; Statoids 1,418).
"""
import json, re, sys
import batch_099_katsina_languages_peoples as P99
import batch_101_katsina_institutions as I101
import batch_102_katsina_heritage as H102

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Katsina rows (NG.KT.*): 2006 census, area, headquarters."),
    "INECKT": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Katsina State: INEC LGA office addresses",
                   organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/KATSINA-STATE.pdf",
                   verification_status="verified", archive_reference="Read via the Internet Archive (2025); copy in database/research/data/inec_katsina_lga_offices.pdf.",
                   notes="LGA offices for all 34 LGAs; by or on the road to the LGA secretariat in a named town: Batsari, Dandume, Danmusa, Jibia, Kankara, Kankia, Kurfi, Mai'adua, Malumfashi, Matazu."),
    "NCMML": H102.SOURCES["NCMML"], "NCMMP": H102.SOURCES["NCMMP"], "WKK": I101.SOURCES["WKK"], "WDE": I101.SOURCES["WDE"], "ATLAS": P99.SOURCES["ATLAS"],
}
LGAS = {
 "bakori": ("Bakori", "Bakori", "Bakori", 149371, 679, "Bakori", "S", "Wikipedia says the LGA was created on 15 May 1989 and that Danja LGA was carved out of it in 1991.", ["W_danja"]),
 "batagarawa": ("Batagarawa", "Batagarawa", "Batagarawa", 184575, 433, "Batagarawa", "SW", "Wikipedia lists Umaru Musa Yar'adua University and other colleges in the LGA.", []),
 "batsari": ("Batsari", "Batsari", "Batsari", 208978, 1107, "Batsari", "SI", "INEC's office is behind the LGA secretariat in Batsari.", []),
 "baure": ("Baure", "Baure, Nigeria", "Baure", 197425, 707, "Baure", "S", "It borders the Republic of Niger (Wikipedia).", []),
 "bindawa": ("Bindawa", "Bindawa", "Bindawa", 152356, 398, "Bindawa", "S", "It was carved out of Mani LGA (Wikipedia, Mani).", []),
 "charanchi": ("Charanchi", "Charanchi", "Charanchi", 137613, 471, "Charanchi", "SW", "Wikipedia also spells it Cheranchi and places the town on the A9 highway.", []),
 "dandume": ("Dandume", "Dandume", "Dandume", 145739, 422, "Dandume", "SI", "INEC's office is behind the LGA secretariat in Dandume; Wikipedia describes its people as farmers.", []),
 "danja": ("Danja", "Danja, Nigeria", "Danja", 125703, 501, "Danja", "SW", "Wikipedia says the LGA was created on 27 September 1991 out of the former Bakori LGA, with Danja town as its headquarters.", []),
 "dan-musa": ("Dan Musa", "Dan Musa", "Dan Musa", 113691, 792, "Dan Musa", "SI", "INEC writes Danmusa and has its office on the secretariat road.", []),
 "daura": ("Daura", "Daura", "Daura", 219721, 316, "Daura", "S", "Daura, the seat of the Daura Emirate, is called the spiritual home of the Hausa people; the Kusugu well of the Bayajidda legend is a proposed national monument (Wikipedia; NCMM).", ["WDE", "NCMMP"]),
 "dutsi": ("Dutsi", "Dutsi, Nigeria", "Dutsi", 120023, 283, "Dutsi", "S", "It was carved out of Mani LGA (Wikipedia, Mani).", []),
 "dutsin-ma": ("Dutsin-Ma", "Dutsin-Ma", "Dutsin Ma", 169671, 527, "Dutsin-Ma", "SW", "Wikipedia says Dutsin-Ma has been the LGA headquarters since the LGA was created in 1976, and that the Zobe Dam lies south of the town.", []),
 "faskari": ("Faskari", "Faskari", "Faskari", 196035, 1750, "Faskari", "SW", "It lies in the south-west of the state, bordering Zamfara State, and had the largest area of the state's LGAs; Wikipedia mentions the Maguzawa in its early history.", []),
 "funtua": ("Funtua", "Funtua", "Funtua", 225571, 448, "Funtua", "S", "Funtua, created in 1976, is the headquarters of the Katsina South senatorial district (Wikipedia).", []),
 "ingawa": ("Ingawa", "Ingawa", "Ingawa", 169753, 892, "Ingawa", "S", "", []),
 "jibia": ("Jibia", "Jibia", "Jibia", 169748, 1037, "Jibia", "SI", "Wikipedia also spells it Jibiya; it lies on the border with Niger, and INEC's office is behind the LGA secretariat.", []),
 "kafur": ("Kafur", "Kafur, Nigeria", "Kafur", 202884, 1106, "Kafur", "S", "Wikipedia says the LGA has ten wards.", []),
 "kaita": ("Kaita", "Kaita, Nigeria", "Kaita", 184401, 925, "Kaita", "S", "It borders the Republic of Niger to the north (Wikipedia).", []),
 "kankara": ("Kankara", "Kankara", "Kankara", 245739, 1462, "Kankara", "SI", "INEC's office is beside the LGA secretariat; Wikipedia says the LGA has 11 wards and 19 village heads.", []),
 "kankia": ("Kankia", "Kankia", "Kankia", 151434, 824, "Kankia", "SI", "Wikipedia also spells it Kankiya and places it on the Kano–Katsina road; INEC's office is in the LGA secretariat.", []),
 "katsina": ("Katsina", "Katsina (city)", "Katsina", 318459, 142, "Katsina", "S", "Katsina is the state capital and the seat of the Katsina Emirate; the Gobarau Minaret and the Old Katsina Training College are declared national monuments (NCMM; Wikipedia). It had the largest population of the state's LGAs at the 2006 census.", ["NCMML", "WKK"]),
 "kurfi": ("Kurfi", "Kurfi", "Kurfi", 117581, 572, "Kurfi", "SI", "Wikipedia places it near the Gada River; INEC's office is opposite the LGA secretariat.", []),
 "kusada": ("Kusada", "Kusada", "Kusada", 99267, 390, "Kusada", "S", "It had the smallest population of the state's LGAs at the 2006 census.", []),
 "mai-adua": ("Mai'Adua", "Mai'Adua", "Mai'Adua", 201178, 528, "Mai'Adua", "SI", "Wikipedia also calls the town Birnin Mai'aduwa; it borders the Republic of Niger.", []),
 "malumfashi": ("Malumfashi", "Malumfashi", "Malumfashi", 182920, 674, "Malumfashi", "SI", "INEC's office is opposite the LGA secretariat.", []),
 "mani": ("Mani", "Mani, Nigeria", "Mani", 176966, 784, "Mani", "S", "Wikipedia says Mani was founded more than 600 years ago and the LGA created in 1976, with Mashi, Bindawa and Dutsi later carved from it; the early Katsina rulers' seat, Durbi ta Kusheyi, was near Mani.", ["WKK"]),
 "mashi": ("Mashi", "Mashi", "Mashi", 173134, 905, "Mashi", "S", "It was carved out of Mani LGA (Wikipedia, Mani).", []),
 "matazu": ("Matazu", "Matazu", "Matazu", 115325, 503, "Matazu", "SI", "INEC's office is on the road to the LGA secretariat.", []),
 "musawa": ("Musawa", "Musawa", "Musawa", 171714, 849, "Musawa", "S", "", []),
 "rimi": ("Rimi", "Rimi, Nigeria", "Rimi", 153744, 452, "Rimi", "S", "", []),
 "sabuwa": ("Sabuwa", "Sabuwa", "Sabuwa", 136050, 642, "Sabuwa", "S", "Wikipedia also spells it Sabua; it borders Kaduna State.", []),
 "safana": ("Safana", "Safana", "Safana", 183779, 282, "Safana", "S", "It borders Zamfara State to the west (Wikipedia).", []),
 "sandamu": ("Sandamu", "Sandamu", "Sandamu", 137287, 1418, "Sandamu", "S", "", []),
 "zango": ("Zango", "Zango, Nigeria", "Zango", 154743, 601, "Zango", "S", "It borders the Republic of Niger (Wikipedia).", []),
}
PEOPLES = {}
for r in P99.RELATIONS:
    if "lga:katsina" in r["to"]:
        PEOPLES.setdefault(r["to"].split("/")[-1], []).append(r["frm"].split(":")[-1].capitalize())
REUSE = {"katsina": "@places:katsina"}
for slug, v in LGAS.items():
    if slug == "mashi":
        continue
    SOURCES[f"W_{slug}"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=v[1], organisation="Wikipedia", url=W(v[1]),
                                verification_status="needs_corroboration", notes=f"Wikipedia article on {v[0]}, consulted {ACCESSED}.")
NAMES_OF = {"S": "Statoids", "I": "INEC", "W": "Wikipedia"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SIW" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, wt, s_hq, pop, area, hq, agree, extra, _k = LGAS[slug]
    t = f"{name} is a local government area of Katsina State with its headquarters at {hq} (according to {by(agree)})."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    if extra: t += " " + extra
    ps = sorted(set(PEOPLES.get(slug, [])))
    if ps:
        t += f" The peoples recorded for the LGA in this archive include the {', '.join(ps[:-1]) + ' and ' + ps[-1] if len(ps) > 1 else ps[0]}."
    return t


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra, keys) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("INECKT", f"{name}: INEC LGA office address")]
    if slug != "mashi" and ("W" in agree or "Wikipedia" in extra):
        srcs.append((f"W_{slug}", f"{name}: Wikipedia article"))
    if slug in ("bindawa", "dutsi", "mashi"):
        srcs.append(("W_mani", f"{name}: carved out of Mani LGA"))
    srcs += [(k, f"{name}: {SOURCES[k]['title']}") for k in keys]
    if slug in REUSE:
        upd["headquarters_place_id"] = REUSE[slug]
    else:
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("INECKT", f"LGA office by the LGA secretariat at {hq}")] if "I" in agree else []) \
               + ([(f"W_{slug}", f"Headquarters of {name}")] if "W" in agree else [])
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if len(agree) >= 2 else "single_reliable_source", level="well_documented" if len(agree) >= 2 else "reported",
                            fields=dict(place_type="town", name=hq, slug=slugify(hq), admin_unit_id=f"@admin_units:lga:katsina/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Katsina State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:katsina/{slug}", fields=upd, srcs=srcs))
    note = {"bakori": "; Wikipedia's 641,576 is not used"}.get(slug, "")
    anote = {"sandamu": "; Wikipedia's 282 km² (the same as Safana's) is not used"}.get(slug, "")
    STATS.append(dict(record=f"@admin_units:lga:katsina/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids" + note, source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:katsina/{slug}", metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids" + anote, source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_jibia", name="Jibiya", name_type="spelling_variant", usage_notes="Wikipedia.", srcs=["W_jibia"]),
    dict(record="hq_kankia", name="Kankiya", name_type="spelling_variant", usage_notes="Wikipedia.", srcs=["W_kankia"]),
    dict(record="hq_mai-adua", name="Birnin Mai'aduwa", name_type="alternative", usage_notes="Wikipedia.", srcs=["W_mai-adua"]),
]
GAPS = [
    ("Katsina: LGA headquarters", "Nineteen headquarters rest on Statoids alone; all match the LGA names."),
    ("Katsina: Mashi article", "No Wikipedia article on Mashi LGA was found."),
    ("Katsina: figures", "Wikipedia's 641,576 for Bakori and 282 km² for Sandamu look wrong (Statoids 149,371 and 1,418 km²) and are not used."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES, relations=[], statistics=STATS,
                scope="Katsina LGA profiles: headquarters, 2006 population and area, short sourced descriptions (34 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 103 — Katsina LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Katsina. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **34 LGA descriptions**, filling fields that were empty.",
         f"- **{len(RECORDS)} headquarters towns**, created as places. Katsina reuses the existing place.",
         "- **Figures:** the 2006 population and area of each LGA, from Statoids.",
         "- **Sources for the headquarters:** Statoids, INEC (ten offices by the secretariat) and Wikipedia (five). Every headquarters is the LGA's own town.",
         "- **Not used:** two Wikipedia figures that look wrong (Bakori 641,576; Sandamu 282 km²).",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Sources | Level |", "|---|---|---|---|"]
    for slug, (name, wt, s_hq, pop, area, hq, agree, extra, _k) in LGAS.items():
        L.append(f"| {name} | {hq} | {by(agree)} | {'reported' if len(agree) < 2 else 'well documented'}{' (reused place)' if slug in REUSE else ''} |")
    L += ["", "## The 34 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_103_katsina_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_103_katsina_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)} names={len(NAMES)}")
