"""
Research batch 097 — Jigawa LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
27 LGAs). Researched 2026-10-02. Pattern: batches 079, 085 and 091.

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.JI.*).
  I  INEC, 'Jigawa State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/JIGAWA-STATE.pdf via the
     Internet Archive; copy in data/inec_jigawa_lga_offices.pdf). Counted only where the office is by the LGA
     secretariat in a named town: Miga. (Buji's office is in 'Gansa town', not by the secretariat — cited in the text.)
  W  Wikipedia's LGA article, where it names the headquarters explicitly: Birnin Kudu, Buji (Gantsa), Dutse (state
     capital), Gwiwa (made an LGA headquarters in 1992), Maigatari, Yankwashi (Karkarna).
Reused place: Dutse. Conflicts noted, not used: Malam Madori — Wikipedia 164,791 (Statoids 161,413).
"""
import json, re, sys
import batch_093b_jigawa_peoples as P93
import batch_095_jigawa_institutions as I95
import batch_096_jigawa_heritage as H96
import batch_093_jigawa_languages as L93

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Jigawa rows (NG.JI.*): 2006 census, area, headquarters."),
    "INECJ": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Jigawa State: INEC LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/JIGAWA-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (2025); copy in database/research/data/inec_jigawa_lga_offices.pdf.",
                  notes="LGA offices for all 27 LGAs; Miga's is beside the LGA secretariat in Miga town; Buji's is in Gansa town; Birniwa, Gagarawa, Jahun, Maigatari, Roni and Sule Tankarkar offices are by their secretariats without a town named."),
    "ATLAS": L93.SOURCES["ATLAS"], "WJIG": P93.SOURCES["WJIG"], "WBAT": H96.SOURCES["WBAT"], "NCMML": H96.SOURCES["NCMML"], "WRIN": I95.SOURCES["WRIN"],
    "DT26": I95.SOURCES["DT26"],
}
# slug: (name, wiki title, Statoids HQ, pop, area, HQ used, agree, text, extra keys)
LGAS = {
 "auyo": ("Auyo", "Auyo", "Auyo", 132001, 512, "Auyo", "S", "Blench's Atlas records Auyokawa, an extinct Chadic language, at Auyo, and part of Baturiya Wetland, a Ramsar Site, lies in the LGA (Wikipedia).", ["ATLAS", "WBAT"]),
 "babura": ("Babura", "Babura", "Babura", 208101, 992, "Babura", "S", "It lies in the north of the state, bordering Katsina State and Danbatta LGA of Kano State, and is one of the LGAs of the Ringim Emirate (Wikipedia).", ["WRIN"]),
 "biriniwa": ("Biriniwa", "Birniwa", "Biriniwa", 142329, 1567, "Biriniwa", "S", "Wikipedia writes Birniwa and describes the town as home to Kanuri (Mangawa), Hausa and Fulani; the Manga language is spoken in the LGA (Wikipedia, Jigawa State).", ["WJIG"]),
 "birnin-kudu": ("Birnin Kudu", "Birnin Kudu", "Birnin Kudu", 313373, 1418, "Birnin Kudu", "SW", "Birnin Kudu was a chiefdom from the 10th century; its rock paintings and rock gongs, recorded in 1955, include four declared national monuments, and Gwaram and Buji LGAs were carved from it in 1996 (Wikipedia; NCMM).", ["NCMML"]),
 "buji": ("Buji", "Buji, Jigawa State", "Gantsa", 97371, 548, "Gantsa", "SW", "INEC's office is in Gansa town.", []),
 "dutse": ("Dutse", "Dutse", "Dutse", 246143, 1099, "Dutse", "SW", "Dutse is the capital of Jigawa State and the seat of the Dutse Emirate, and is home to the Federal University Dutse, founded in 2011 (Wikipedia).", []),
 "gagarawa": ("Gagarawa", "Gagarawa", "Gagarawa", 80394, 654, "Gagarawa", "S", "It lies in the north of the state (Wikipedia).", []),
 "garki": ("Garki", "Garki, Jigawa State", "Garki", 152233, 1408, "Garki", "S", "Wikipedia also calls the town Garkin Dirani and notes that it was the site of the Garki Project, a malaria research study; it is one of the LGAs of the Ringim Emirate.", ["WRIN"]),
 "gumel": ("Gumel", "Gumel", "Gumel", 107161, 223, "Gumel", "S", "Gumel is the seat of the Gumel Emirate, founded about 1750; its emir since 1981 died on 3 September 2026 and his son was appointed the 17th emir (Wikipedia; Daily Trust).", ["DT26"]),
 "guri": ("Guri", "Guri, Jigawa State", "Guri", 115018, 1060, "Guri", "S", "Wikipedia says the Bade language is spoken in the LGA, and places the Manga language there too; part of Baturiya Wetland lies in it.", ["WJIG", "WBAT"]),
 "gwaram": ("Gwaram", "Gwaram", "Gwaram", 272582, 1912, "Gwaram", "S", "It was carved out of Birnin Kudu LGA in 1996 (Wikipedia, Birnin Kudu).", []),
 "gwiwa": ("Gwiwa", "Gwiwa", "Gwiwa", 124517, 450, "Gwiwa", "SW", "Wikipedia says Gwiwa was a district headquarters early in British rule and became an LGA headquarters in 1992.", []),
 "hadejia": ("Hadejia", "Hadejia", "Hadejia", 105628, 32, "Hadejia", "S", "Hadejia, once the Hausa state of Biram, is the seat of the Hadejia Emirate; the town lies north of the Hadejia River, upstream of the Hadejia-Nguru wetlands (Wikipedia).", []),
 "jahun": ("Jahun", "Jahun", "Jahun", 229094, 1172, "Jahun", "S", "Wikipedia says the LGA is inhabited mainly by Fulani.", []),
 "kafin-hausa": ("Kafin Hausa", "Kafin Hausa", "Kafin Hausa", 271058, 1380, "Kafin Hausa", "S", "Blench's Atlas records two extinct Chadic languages, Shira and Teshena, at towns in the LGA, which it calls Keffin Hausa; Sule Lamido University is at Kafin Hausa (Wikipedia, Jigawa State).", ["ATLAS", "WJIG"]),
 "kaugama": ("Kaugama", "Kaugama", "Kaugama", 127956, 883, "Kaugama", "S", "It lies in the north of the state; Wikipedia places the Manga language in the LGA.", ["WJIG"]),
 "kazaure": ("Kazaure", "Kazaure", "Kazaure", 161494, 368, "Kazaure", "S", "Kazaure has been the seat of the Kazaure Emirate since 1819 (Wikipedia).", []),
 "kiri-kasama": ("Kiri Kasama", "Kiri Kasama", "Kiri Kasama", 191523, 797, "Kiri Kasama", "S", "INEC writes Kirika Samma. Wikipedia places the Manga language in the LGA, and part of Baturiya Wetland lies in it.", ["WJIG", "WBAT"]),
 "kiyawa": ("Kiyawa", "Kiyawa", "Kiyawa", 172913, 1030, "Kiyawa", "S", "Wikipedia places the town on the road between Kano and Azare, about 30 km east of Dutse.", []),
 "maigatari": ("Maigatari", "Maigatari", "Maigatari", 179715, 870, "Maigatari", "SW", "Maigatari is a border town on the frontier with Niger, known for a large livestock market founded in 1870 (Wikipedia).", []),
 "malam-madori": ("Malam Madori", "Malam Madori", "Malam Maduri", 161413, 766, "Malam Madori", "S", "Statoids and INEC's ward directory write Malam Maduri. Wikipedia says the town was founded by Malam Madu around 1930 and gives a 2006 population of 164,791.", []),
 "miga": ("Miga", "Miga, Jigawa State", "Miga", 128424, 586, "Miga", "SI", "INEC's office is beside the LGA secretariat in Miga town.", []),
 "ringim": ("Ringim", "Ringim", "Ringim", 192024, 1057, "Ringim", "S", "Ringim is the headquarters of the Ringim Emirate, created in November 1991 (Wikipedia).", ["WRIN"]),
 "roni": ("Roni", "Roni, Jigawa State", "Roni", 77819, 322, "Roni", "S", "It had the smallest population of the state's LGAs at the 2006 census.", []),
 "sule-tankarkar": ("Sule Tankarkar", "Sule Tankarkar", "Sule Tankakar", 130849, 1283, "Sule Tankarkar", "S", "It borders the Republic of Niger (Wikipedia).", []),
 "taura": ("Taura", "Taura, Jigawa", "Taura", 131757, 653, "Taura", "S", "Wikipedia says most of its people are Hausa; it is one of the LGAs of the Ringim Emirate.", ["WRIN"]),
 "yankwashi": ("Yankwashi", "Yankwashi", "Karkarna", 95759, 371, "Karkarna", "SW", "", []),
}
PEOPLES = {}
for r in P93.RELATIONS:
    if "lga:jigawa" in r["to"] and r["frm"].startswith("@ethnic"):
        PEOPLES.setdefault(r["to"].split("/")[-1], []).append(r["frm"].split(":")[-1].capitalize())
REUSE = {"dutse": "@places:dutse"}
for slug, v in LGAS.items():
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
    t = f"{name} is a local government area of Jigawa State with its headquarters at {hq} (according to {by(agree)})."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    if extra: t += " " + extra
    ps = sorted(set(PEOPLES.get(slug, [])))
    if ps and not any(p in extra for p in ps):
        t += f" The peoples recorded for the LGA in this archive include the {', '.join(ps[:-1]) + ' and ' + ps[-1] if len(ps) > 1 else ps[0]}."
    return t


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra, keys) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("INECJ", f"{name}: INEC LGA office address"), (f"W_{slug}", f"{name}: Wikipedia article")]
    srcs += [(k, f"{name}: {SOURCES[k]['title']}") for k in keys]
    if slug in REUSE:
        upd["headquarters_place_id"] = REUSE[slug]
    else:
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("INECJ", f"LGA office by the LGA secretariat at {hq}")] if "I" in agree else []) \
               + ([(f"W_{slug}", f"Headquarters of {name}")] if "W" in agree else [])
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if len(agree) >= 2 else "single_reliable_source", level="well_documented" if len(agree) >= 2 else "reported",
                            fields=dict(place_type="town", name=hq, slug=slugify(hq), admin_unit_id=f"@admin_units:lga:jigawa/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Jigawa State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:jigawa/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:jigawa/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids" + ("; Wikipedia gives 164,791" if slug == "malam-madori" else ""), source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:jigawa/{slug}", metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_buji", name="Gansa", name_type="spelling_variant", usage_notes="INEC ('Gansa town').", srcs=["INECJ"]),
    dict(record="hq_malam-madori", name="Malam Maduri", name_type="spelling_variant", usage_notes="Statoids.", srcs=["STAT"]),
    dict(record="hq_biriniwa", name="Birniwa", name_type="spelling_variant", usage_notes="Wikipedia's spelling.", srcs=["W_biriniwa"]),
]
GAPS = [
    ("Jigawa: LGA headquarters", "Twenty headquarters rest on Statoids alone; INEC's offices are mostly not tied to a secretariat in a named town."),
    ("Jigawa: figures", "Malam Madori 2006: Statoids 161,413, Wikipedia 164,791. Wikipedia's 419,800 for Birnin Kudu 'town' is not used. Statoids recorded."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES, relations=[], statistics=STATS,
                scope="Jigawa LGA profiles: headquarters, 2006 population and area, short sourced descriptions (27 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 097 — Jigawa LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Jigawa. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **27 LGA descriptions**, filling fields that were empty.",
         f"- **{len(RECORDS)} headquarters towns**, created as places. Dutse reuses the existing place.",
         "- **Figures:** the 2006 population and area of each LGA, from Statoids.",
         "- **Sources for the headquarters:** Statoids, Wikipedia (where it names the headquarters) and INEC (Miga). INEC's office list is saved in the repo.",
         "- **Spellings kept as names:** Gansa/Gantsa, Malam Maduri, Birniwa.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Statoids | Sources | Level |", "|---|---|---|---|---|"]
    for slug, (name, wt, s_hq, pop, area, hq, agree, extra, _k) in LGAS.items():
        L.append(f"| {name} | {hq} | {s_hq} | {by(agree)} | {'reported' if len(agree) < 2 else 'well documented'}{' (reused place)' if slug in REUSE else ''} |")
    L += ["", "## The 27 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_097_jigawa_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_097_jigawa_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)} names={len(NAMES)}")
