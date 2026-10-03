"""
Research batch 091 — Kano LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
44 LGAs). Researched 2026-10-02. Pattern: batches 061, 067, 073, 079 and 085.

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.KN.*).
  I  INEC, 'Kano State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/KANO-STATE.pdf via the
     Internet Archive; copy in data/inec_kano_lga_offices.pdf). Counted only where the office is in or by the LGA
     secretariat in a named town (Albasu, Bunkure, Dawakin Kudu, Gaya, Kabo, Karaye, Kibiya, Kumbotso, Kura, Minjibir,
     Rogo, Takai, Tofa, Tsanyawa, Warawa; Makoda — 'beside Makoda LGA secretariat, Koguna').
  W  Wikipedia's LGA article, where it names the headquarters explicitly (Bebeji, Dala — Gwammaja, Doguwa — Riruwai,
     Fagge — Waje, Gabasawa — Zakirai, Kano Municipal — Kofar Kudu, Kunchi (Wikipedia 'Ghari') — Kunchi, Makoda —
     Koguna, Nasarawa — Bompai, Sumaila, Tarauni — Unguwa Uku).
Grading: two agreeing sources = well documented; one = reported. Makoda: Statoids gives Makoda, Wikipedia and INEC
point to Koguna → Koguna, 'reported', conflict stated.
Figures: Statoids. Conflicts noted, not used: Nasarawa — Wikipedia 678,669 (Statoids 596,669); Kibiya — Wikipedia
189,870 (Statoids 136,736). Kunchi LGA appears on Wikipedia as 'Ghari' (headquarters Kunchi) — noted, not renamed.
"""
import json, re, sys
import batch_087b_kano_peoples as P87
import batch_089_kano_institutions as I89
import batch_090_kano_heritage as H90
import batch_087_kano_languages as L87

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Kano rows (NG.KN.*): 2006 census, area, headquarters."),
    "INECK": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Kano State: INEC state and LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/KANO-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (2025); copy in database/research/data/inec_kano_lga_offices.pdf.",
                  notes="State office No. 1 Pilgrim's Camp Way, Kano. LGA offices for all 44 LGAs; those in or by the LGA secretariat: Albasu, Bunkure, Dawakin Kudu, Gaya, Kabo, Karaye, Kibiya, Kumbotso, Kura, Makoda (Koguna), Minjibir (old secretariat), Rogo, Takai, Tofa, Tsanyawa, Warawa. Doguwa's office is in Riruwai town."),
    "ICIR24": I89.SOURCES["ICIR24"], "WKEC": I89.SOURCES["WKEC"], "WRAN": I89.SOURCES["WRAN"], "WKAR": I89.SOURCES["WKAR"], "WBIC": I89.SOURCES["WBIC"],
    "NCMML": H90.SOURCES["NCMML"], "ATLAS": L87.SOURCES["ATLAS"],
}
# slug: (archive name, Wikipedia title, Statoids HQ, pop, area, HQ used, agree, text, extra keys)
LGAS = {
 "ajingi": ("Ajingi", "Ajingi", "Ajingi", 174137, 714, "Ajingi", "S", "Wikipedia dates the LGA to 1996. It is one of the three LGAs of the Gaya Emirate, a second-class emirate since 2024 (ICIR).", ["ICIR24"]),
 "albasu": ("Albasu", "Albasu", "Albasu", 190153, 398, "Albasu", "SI", "INEC's office is opposite the LGA secretariat in Albasu town. It is one of the three LGAs of the Gaya Emirate since 2024 (ICIR).", ["ICIR24"]),
 "bagwai": ("Bagwai", "Bagwai", "Bagwai", 162847, 405, "Bagwai", "S", "Wikipedia places it in the north-west of the state, about 55 km from Kano, and notes the Watari dam and its irrigation scheme of about 1,000 hectares.", []),
 "bebeji": ("Bebeji", "Bebeji", "Bebeji", 188859, 717, "Bebeji", "SW", "Wikipedia names the Hausa and Fulani as the great majority of its people. The Habe Mosque at Bebeji is one of Kano's three declared national monuments (NCMM).", ["NCMML"]),
 "bichi": ("Bichi", "Bichi", "Bichi", 277099, 612, "Bichi", "S", "Wikipedia says Bichi town was founded by the Danejawa, a Fulani clan of herders. Bichi was the seat of the Bichi Emirate from 2019 until it was abolished in 2024 (Wikipedia).", ["WBIC"]),
 "bunkure": ("Bunkure", "Bunkure", "Bunkure", 170891, 487, "Bunkure", "SI", "INEC's office is in the LGA secretariat. Wikipedia says the LGA was split from the former Rano LGA; it is one of the three LGAs of the Rano Emirate since 2024 (ICIR).", ["ICIR24"]),
 "dala": ("Dala", "Dala, Nigeria", "Gwamaja", 418777, 19, "Gwammaja", "SW", "Statoids writes Gwamaja. Dala lies in the north-west of the Kano metropolis; it was created in May 1989 from the old Kano Municipal LGA and takes its name from Dala Hill, after which Kano was first named (Wikipedia).", []),
 "dambatta": ("Dambatta", "Dambatta", "Dambatta", 207968, 732, "Dambatta", "S", "Wikipedia also spells it Danbatta and Dambarta, and places it about 79 km north of Kano, on the border with Jigawa State.", []),
 "dawakin-kudu": ("Dawakin Kudu", "Dawakin Kudu", "Dawakin Kudu", 225389, 384, "Dawakin Kudu", "SI", "INEC's office is beside the LGA secretariat. Wikipedia says 99 per cent of its people are Hausa and that it has the oldest dyeing pit in Kano State.", []),
 "dawakin-tofa": ("Dawakin Tofa", "Dawakin Tofa", "Dawakin Tofa", 247875, 479, "Dawakin Tofa", "S", "Wikipedia names the Hausa and Fulani as its two main peoples.", []),
 "doguwa": ("Doguwa", "Doguwa", "Riruwai", 151181, 1473, "Riruwai", "SW", "INEC's office is also in Riruwai town. It lies in the far south of the state; Wikipedia names the Falgore woodland among its features and says many of its Fulani are cattle herders.", []),
 "fagge": ("Fagge", "Fagge", "Waje", 198828, 21, "Waje", "SW", "Fagge lies within the Kano metropolis and was created from Nasarawa LGA on 4 December 1996; Wikipedia notes a significant Igbo population alongside the Hausa and Fulani.", []),
 "gabasawa": ("Gabasawa", "Gabasawa", "Zakirai", 211055, 605, "Zakirai", "SW", "INEC's office is also at Zakirai.", []),
 "garko": ("Garko", "Garko, Nigeria", "Garko", 162500, 450, "Garko", "S", "Wikipedia places it on the A237 highway and names the Fulani and Hausa as most of its people.", []),
 "garum-mallam": ("Garum Mallam", "Garun Mallam", "Garum Mallam", 116494, 214, "Garum Mallam", "S", "INEC and Wikipedia write Garun Malam or Garun Mallam. Wikipedia places it on the A2 highway and names the Kano seed processing centre at Kadawa as a landmark.", []),
 "gaya": ("Gaya", "Gaya, Nigeria", "Gaya", 201016, 613, "Gaya", "SI", "INEC's office is opposite the LGA secretariat. Wikipedia calls Gaya, a town, emirate and LGA, the oldest and most significant site in Kano's history, older than Kano itself; the Gaya Emirate has been a second-class emirate since 2024 (ICIR).", ["ICIR24"]),
 "gezawa": ("Gezawa", "Gezawa", "Gezawa", 282069, 340, "Gezawa", "S", "Wikipedia names the Hausa and Fulani as most of its people.", []),
 "gwale": ("Gwale", "Gwale", "Gwale", 362059, 18, "Gwale", "S", "Gwale lies within greater Kano; Wikipedia notes its many Islamic scholars and that the politician Malam Aminu Kano was born in its Sudawa quarter.", []),
 "gwarzo": ("Gwarzo", "Gwarzo", "Gwarzo", 183987, 393, "Gwarzo", "S", "Wikipedia names the Hausa and Fulani as most of its people.", []),
 "kabo": ("Kabo", "Kabo, Nigeria", "Kabo", 153828, 341, "Kabo", "SI", "INEC's office is beside the LGA secretariat. Wikipedia names the Kabo forest reserves among its sights.", []),
 "kano-municipal": ("Kano Municipal", "Kano Municipal", "Kofar Kudu", 365525, 17, "Kofar Kudu", "SW", "Its secretariat is at Kofar Kudu, the gate of the Emir's palace, Gidan Rumfa, in the south of the old city of Kano (Wikipedia); it had the smallest area of the state's LGAs.", []),
 "karaye": ("Karaye", "Karaye", "Karaye", 141407, 479, "Karaye", "SI", "INEC's office is opposite the LGA secretariat. Karaye is the headquarters of the Karaye Emirate, a second-class emirate since 2024, and Wikipedia names the Challawa Gorge Dam there; it records the Maguzawa as the area's early inhabitants.", ["WKAR", "ICIR24"]),
 "kibiya": ("Kibiya", "Kibiya", "Kibiya", 136736, 404, "Kibiya", "SI", "INEC's office is beside the LGA secretariat. Wikipedia gives a 2006 population of 189,870. It is one of the three LGAs of the Rano Emirate since 2024 (ICIR).", ["ICIR24"]),
 "kiru": ("Kiru", "Kiru, Nigeria", "Kiru", 264781, 927, "Kiru", "S", "Wikipedia describes it as an agrarian area of farming, trade and rural settlements.", []),
 "kumbotso": ("Kumbotso", "Kumbotso", "Kumbotso", 295979, 158, "Kumbotso", "SI", "INEC's office is behind the LGA secretariat.", []),
 "kunchi": ("Kunchi", "Ghari, Nigeria", "Kunchi", 111018, 671, "Kunchi", "SW", "Wikipedia gives the LGA under the name Ghari, with its headquarters at Kunchi; the archive keeps the name Kunchi.", []),
 "kura": ("Kura", "Kura, Nigeria", "Kura", 144601, 206, "Kura", "SI", "INEC's office is inside the LGA secretariat in Kura town.", []),
 "madobi": ("Madobi", "Madobi", "Madobi", 136623, 273, "Madobi", "S", "", []),
 "makoda": ("Makoda", "Makoda", "Makoda", 222399, 441, "Koguna", "WI", "Statoids gives Makoda as the headquarters, but Wikipedia names Koguna and INEC's office is beside the LGA secretariat at Koguna, so the headquarters is uncertain. Wikipedia says the LGA was carved out of Dambatta.", []),
 "minjibir": ("Minjibir", "Minjibir", "Minjibir", 213794, 416, "Minjibir", "SI", "INEC's office is in the old LGA secretariat. Wikipedia says Minjibir, about 20 km north-east of Kano, was founded in the early 18th century by the Fulani of the Yerimawa clan.", []),
 "nasarawa": ("Nasarawa", "Nasarawa, Kano State", "Bompai", 596669, 34, "Bompai", "SW", "INEC and Statoids write Nassarawa. Its headquarters is the Bompai area of Kano city; Wikipedia gives a 2006 population of 678,669, the largest of the state's LGAs either way.", []),
 "rano": ("Rano", "Rano", "Rano", 145439, 520, "Rano", "S", "Wikipedia counts Rano among the seven Hausa states of tradition (Hausa Bakwai); it is the seat of the Rano Emirate, a second-class emirate since 2024 (ICIR).", ["WRAN", "ICIR24"]),
 "rimin-gado": ("Rimin Gado", "Rimin Gado", "Rimin Gado", 104790, 225, "Rimin Gado", "S", "Wikipedia also gives the name Rafin Gado and places it about 20 km west of Kano.", []),
 "rogo": ("Rogo", "Rogo", "Rogo", 227742, 802, "Rogo", "SI", "INEC's office is beside the LGA secretariat. Rogo borders Kaduna and Katsina states (Wikipedia) and is one of the two LGAs of the Karaye Emirate since 2024 (ICIR).", ["ICIR24"]),
 "shanono": ("Shanono", "Shanono", "Shanono", 140607, 697, "Shanono", "S", "Wikipedia traces the town to two herding brothers, Jaulere and Shanu, who came from Hadejia.", []),
 "sumaila": ("Sumaila", "Sumaila", "Sumaila", 253661, 1250, "Sumaila", "SW", "Wikipedia says Sumaila was founded in the 1740s as a settlement of the Jobawa Fulani, first called Garun Sam'ila.", []),
 "takai": ("Takai", "Takai", "Takai", 202743, 598, "Takai", "SI", "INEC's office is near the LGA secretariat. Wikipedia places it on the A237 highway.", []),
 "tarauni": ("Tarauni", "Tarauni", "Ungwa Uku", 221367, 28, "Unguwa Uku", "SW", "Statoids writes Ungwa Uku. Tarauni lies within the city of Kano and has ten wards (Wikipedia).", []),
 "tofa": ("Tofa", "Tofa, Nigeria", "Tofa", 97734, 202, "Tofa", "SI", "INEC's office is on the LGA secretariat road. It had the smallest population of the state's LGAs at the 2006 census.", []),
 "tsanyawa": ("Tsanyawa", "Tsanyawa", "Tsanyawa", 157680, 492, "Tsanyawa", "SI", "INEC's office is beside the LGA secretariat.", []),
 "tudun-wada": ("Tudun Wada", "Tudun Wada", "Tudun Wada", 231742, 1204, "Tudun Wada", "S", "Blench's Atlas places the Kurama language here, the only Kano LGA it names for a language other than Hausa.", ["ATLAS"]),
 "ungogo": ("Ungogo", "Ungogo", "Ungogo", 369657, 204, "Ungogo", "S", "Wikipedia places its secretariat in the north of the city of Kano.", []),
 "warawa": ("Warawa", "Warawa", "Warawa", 128787, 360, "Warawa", "SI", "INEC's office is beside the LGA secretariat. Wikipedia says it was created out of Dawakin Kudu LGA in the early 1990s and has 15 wards.", []),
 "wudil": ("Wudil", "Wudil", "Wudil", 185189, 362, "Wudil", "S", "Wikipedia places it on the A237 highway near the Hadejia River.", []),
}
PEOPLES = {}
for p, l, s, n in [(r["frm"].split(":")[-1], r["to"].split("/")[-1], r["source"], r["notes"]) for r in P87.RELATIONS if "lga:" in r["to"]]:
    PEOPLES.setdefault(l, []).append(p.capitalize())
REUSE = {}
HQ_SLUG = {"kano-municipal": "kofar-kudu-kano"}
for slug, v in LGAS.items():
    SOURCES[f"W_{slug}"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=v[1], organisation="Wikipedia", url=W(v[1]),
                                verification_status="needs_corroboration", notes=f"Wikipedia article on {v[0]} LGA, consulted {ACCESSED}.")
SOURCES["W_tudun-wada"]["notes"] += " The title is a disambiguation page; no Kano article was found."
NAMES_OF = {"S": "Statoids", "I": "INEC", "W": "Wikipedia"}
ATLAS_KEYS = {"tudun-wada"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SIW" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, wt, s_hq, pop, area, hq, agree, extra, _k = LGAS[slug]
    t = f"{name} is a local government area of Kano State with its headquarters at {hq} (according to {by(agree)})."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    if extra: t += " " + extra
    ps = sorted(set(PEOPLES.get(slug, [])))
    if ps and "Hausa" not in extra and "Fulani" not in extra:
        t += f" The peoples recorded for the LGA in this archive include the {', '.join(ps[:-1]) + ' and ' + ps[-1] if len(ps) > 1 else ps[0]}."
    return t


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra, keys) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("INECK", f"{name}: INEC LGA office address"), (f"W_{slug}", f"{name}: Wikipedia article")]
    srcs += [(k, f"{name}: {SOURCES[k]['title']}") for k in keys]
    key = f"hq_{slug}"
    hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("INECK", f"LGA office by the LGA secretariat at {hq}")] if "I" in agree else []) \
           + ([(f"W_{slug}", f"Headquarters of {name}")] if "W" in agree else [])
    two = len(agree) >= 2 and slug != "makoda"
    RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if len(agree) >= 2 else "single_reliable_source", level="well_documented" if two else "reported",
                        fields=dict(place_type="town", name=hq, slug=HQ_SLUG.get(slug, slugify(hq)), admin_unit_id=f"@admin_units:lga:kano/{slug}", status="existing",
                                    summary=f"{hq} is the headquarters of {name} Local Government Area, Kano State."), srcs=hsrc))
    upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:kano/{slug}", fields=upd, srcs=srcs))
    pnote = {"nasarawa": "; Wikipedia gives 678,669", "kibiya": "; Wikipedia gives 189,870"}.get(slug, "")
    STATS.append(dict(record=f"@admin_units:lga:kano/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids" + pnote, source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:kano/{slug}", metric="area_km2", value_low=area, method="other",
                      notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_dala", name="Gwamaja", name_type="spelling_variant", usage_notes="Statoids.", srcs=["STAT"]),
    dict(record="hq_tarauni", name="Ungwa Uku", name_type="spelling_variant", usage_notes="Statoids.", srcs=["STAT"]),
]
GAPS = [
    ("Kano: LGA headquarters", "Twenty LGAs rest on Statoids alone (their INEC office is not by the secretariat and their Wikipedia article does not name the headquarters). Makoda: Statoids gives Makoda; Wikipedia and INEC point to Koguna."),
    ("Kano: Kunchi or Ghari", "Wikipedia gives the LGA as 'Ghari' with headquarters at Kunchi; INEC, Statoids and the archive use Kunchi. Whether the LGA has been renamed needs an official source."),
    ("Kano: figures", "Nasarawa 2006: Statoids 596,669, Wikipedia 678,669; Kibiya: Statoids 136,736, Wikipedia 189,870. Statoids recorded."),
    ("Kano: Tudun Wada article", "Wikipedia's 'Tudun Wada' is a disambiguation page; no article on the Kano LGA was found."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=[], statistics=STATS,
                scope="Kano LGA profiles: headquarters, 2006 population and area, short sourced descriptions (44 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 091 — Kano LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Kano. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **44 LGA descriptions**, filling fields that were empty.",
         f"- **{len(RECORDS)} headquarters towns**, created as places.",
         "- **Figures:** the 2006 population and area of each LGA, from Statoids.",
         "- **Sources for the headquarters:** Statoids, INEC's LGA offices in or by the secretariat, and Wikipedia. INEC's office list is saved in the repo.",
         "- **Makoda is *reported*:** Statoids gives Makoda, but Wikipedia and INEC point to Koguna.",
         "- **Kunchi:** Wikipedia calls the LGA 'Ghari'. This is noted; the record is not renamed.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Statoids | Sources | Level |", "|---|---|---|---|---|"]
    for slug, (name, wt, s_hq, pop, area, hq, agree, extra, _k) in LGAS.items():
        lvl = "reported" if (len(agree) < 2 or slug == "makoda") else "well documented"
        L.append(f"| {name} | {hq} | {s_hq} | {by(agree)} | {lvl} |")
    L += ["", "## The 44 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_091_kano_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_091_kano_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)} names={len(NAMES)}")
