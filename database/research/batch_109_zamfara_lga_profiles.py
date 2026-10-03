"""
Research batch 109 — Zamfara LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
14 LGAs). Researched 2026-10-02. Pattern: batches 097 and 103.

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.ZA.*; Kaura Namoda written 'Kauran Namoda').
  I  INEC, 'Zamfara State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/ZAMFARA-STATE.pdf via the
     Internet Archive 20260616183353; copy in data/inec_zamfara_lga_offices.pdf). By the LGA secretariat in a named
     town: Bakura.
  W  Wikipedia's LGA article, where it names the headquarters: Birnin Magaji (Birnin Magaji/Kiyaw); Tsafe ('Chafe
     town' — Chafe is Wikipedia's spelling of Tsafe).
Reused place: Gusau.
"""
import json, re, sys
import batch_105_zamfara_languages_peoples as P105
import batch_107_zamfara_institutions as I107
import batch_108_zamfara_heritage as H108

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Zamfara rows (NG.ZA.*): 2006 census, area, headquarters."),
    "INECZ": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Zamfara State: INEC LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/ZAMFARA-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (16 June 2026); copy in database/research/data/inec_zamfara_lga_offices.pdf.",
                  notes="LGA offices for all 14 LGAs, each in the LGA's own town; Bakura's is behind the LGA secretariat."),
    "WZAM": P105.SOURCES["WZAM"], "NCMMP": H108.SOURCES["NCMMP"], "WKNA": I107.SOURCES["WKNA"], "WSHI": I107.SOURCES["WSHI"], "CH25": I107.SOURCES["CH25"],
}
LGAS = {
 "anka": ("Anka", "Anka, Nigeria", "Anka", 142280, 2746, "Anka", "S", "Anka, in the west of the state, became the later capital of the Zamfara kingdom (Wikipedia, Zamfara State), and is the seat of the Anka Emirate.", []),
 "bakura": ("Bakura", None, "Bakura", 186905, 1366, "Bakura", "SI", "INEC's office is behind the LGA secretariat in Bakura.", []),
 "birnin-magaji-kiyaw": ("Birnin Magaji/Kiyaw", "Birnin Magaji/Kiyaw", "Birnin Magaji", 178619, 1188, "Birnin Magaji", "SW", "Wikipedia says the headquarters, Birnin Magaji (or Magare), is in the north of the LGA, which also takes its name from the town of Kiyaw (Kiawa) to the south.", []),
 "bukkuyum": ("Bukkuyum", "Bukkuyum", "Bukkuyum", 211633, 3214, "Bukkuyum", "S", "INEC and Wikipedia also write Bukuyum and Bukwium.", []),
 "bungudu": ("Bungudu", "Bungudu", "Bungudu", 257917, 2293, "Bungudu", "S", "Wikipedia places the town on the Sokoto River.", []),
 "gummi": ("Gummi", "Gummi, Nigeria", "Gummi", 204539, 2610, "Gummi", "S", "", []),
 "gusau": ("Gusau", "Gusau", "Gusau", 383162, 3364, "Gusau", "S", "Gusau is the capital of Zamfara State and the seat of the Gusau Emirate, whose new emir was appointed in July 2025 (Wikipedia; Channels TV); it had the largest population of the state's LGAs at the 2006 census.", ["CH25"]),
 "kaura-namoda": ("Kaura Namoda", "Kaura Namoda", "Kaura Namoda", 281367, 868, "Kaura Namoda", "S", "Kaura Namoda was founded in 1807 by Muhammadu Namoda, whose tomb is a proposed national monument; it is home to the Federal Polytechnic, Kaura-Namoda, and was a railway terminus (Wikipedia; NCMM).", ["NCMMP"]),
 "maradun": ("Maradun", "Maradun", "Maradun", 210852, 2728, "Maradun", "S", "", []),
 "maru": ("Maru", "Maru, Nigeria", "Maru", 291900, 6654, "Maru", "S", "It had the largest area of the state's LGAs.", []),
 "shinkafi": ("Shinkafi", "Shinkafi", "Shinkafi", 135649, 674, "Shinkafi", "S", "Shinkafi borders Sokoto State and the Republic of Niger, and is the seat of the Shinkafi Emirate, created in 2000; it had the smallest population of the state's LGAs at the 2006 census (Wikipedia).", []),
 "talata-mafara": ("Talata Mafara", "Talata Mafara", "Talata Mafara", 215178, 1430, "Talata Mafara", "S", "Wikipedia places the town on the southern edge of the irrigation project fed by the Bakolori Dam.", []),
 "tsafe": ("Tsafe", "Tsafe", "Tsafe", 266008, 1698, "Tsafe", "SW", "Wikipedia also writes Chafe and Tsyahe.", []),
 "zurmi": ("Zurmi", "Zurmi", "Zurmi", 293837, 2834, "Zurmi", "S", "Zurmi borders Niger and Katsina State; the giant tombs of the Zamfara rulers there are a proposed national monument (Wikipedia; NCMM).", ["NCMMP"]),
}
PEOPLES = {}
for r in P105.RELATIONS:
    if "lga:zamfara" in r["to"]:
        PEOPLES.setdefault(r["to"].split("/")[-1], []).append(r["frm"].split(":")[-1].capitalize())
REUSE = {"gusau": "@places:gusau"}
for slug, v in LGAS.items():
    if v[1]:
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
    t = f"{name} is a local government area of Zamfara State with its headquarters at {hq} (according to {by(agree)})."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    if extra: t += " " + extra
    ps = sorted(set(PEOPLES.get(slug, [])))
    if ps:
        t += f" The peoples recorded for the LGA in this archive include the {' and '.join(ps)}."
    return t


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra, keys) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("INECZ", f"{name}: INEC LGA office address"), ("WZAM", f"{name}: peoples")]
    if wt: srcs.append((f"W_{slug}", f"{name}: Wikipedia article"))
    srcs += [(k, f"{name}: {SOURCES[k]['title']}") for k in keys]
    if slug in REUSE:
        upd["headquarters_place_id"] = REUSE[slug]
    else:
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("INECZ", f"LGA office by the LGA secretariat at {hq}")] if "I" in agree else []) \
               + ([(f"W_{slug}", f"Headquarters of {name}")] if "W" in agree else [])
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if len(agree) >= 2 else "single_reliable_source", level="well_documented" if len(agree) >= 2 else "reported",
                            fields=dict(place_type="town", name=hq, slug=slugify(hq), admin_unit_id=f"@admin_units:lga:zamfara/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Zamfara State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:zamfara/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:zamfara/{slug}", metric="population", value_low=pop, reference_year=2006, method="census", notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:zamfara/{slug}", metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_tsafe", name="Chafe", name_type="spelling_variant", usage_notes="Wikipedia ('Chafe town'); also Tsyahe.", srcs=["W_tsafe"]),
    dict(record="hq_birnin-magaji-kiyaw", name="Magare", name_type="alternative", usage_notes="Wikipedia ('Birnin Magaji (or Magare)').", srcs=["W_birnin-magaji-kiyaw"]),
]
GAPS = [("Zamfara: LGA headquarters", "Eleven headquarters rest on Statoids alone (all match the LGA towns, where INEC also has its offices)."),
        ("Zamfara: Bakura article", "No Wikipedia article on Bakura LGA was found.")]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES, relations=[], statistics=STATS,
                scope="Zamfara LGA profiles: headquarters, 2006 population and area, short sourced descriptions (14 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 109 — Zamfara LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Zamfara. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **14 LGA descriptions** and **{len(RECORDS)} headquarters towns** (Gusau reuses the existing place).",
         "- **Figures:** the 2006 population and area of each LGA, from Statoids.",
         "- **Sources for the headquarters:** Statoids, INEC (Bakura) and Wikipedia (Birnin Magaji; Tsafe, which Wikipedia writes 'Chafe').",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## The 14 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_109_zamfara_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_109_zamfara_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)}")
