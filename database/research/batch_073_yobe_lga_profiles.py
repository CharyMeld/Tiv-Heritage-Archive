"""
Research batch 073 — Yobe LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
17 LGAs). Researched 2026-10-01. Pattern: batches 061 and 067.

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.YO.*; Bade written 'Barde').
  I  INEC, 'Yobe State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/YOBE-STATE.pdf via the
     Internet Archive; copy in data/inec_yobe_lga_offices.pdf): counted only where the office is by the LGA secretariat
     in a named town (Fika; Yusufari; Yunusari — the last conflicts, see below). Other offices name the same towns as
     Statoids (Gashua, Dapchi, Damagum, Buni Yadi, Bara, Jajimaji, Babbangida) and are cited in the texts.
  W  Wikipedia's LGA article, where it names the headquarters explicitly.
Grading: two agreeing sources = well documented; one = reported. Yunusari is 'reported' because INEC places its office
by the LGA secretariat at Yunusari town while Statoids and Wikipedia give Kanamma.
Reused place: Damaturu (existing).
Figures: Statoids. Conflicts noted, not used: Tarmua — Wikipedia 177,204 (Statoids 77,204); Nguru — Wikipedia 270,632
(Statoids 150,632); Fika area — Wikipedia 2,852 km² (Statoids 2,208).
"""
import json, re, sys
import batch_069b_yobe_peoples as P69
import batch_072_yobe_heritage as H72
import batch_071_yobe_institutions as I71

ACCESSED = "2026-10-01"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Yobe rows (NG.YO.*): 2006 census, area, headquarters."),
    "INECY": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Yobe State: INEC state and LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/YOBE-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (2025); copy in database/research/data/inec_yobe_lga_offices.pdf.",
                  notes="State office KM6 Sir Kashim Ibrahim Way, Damaturu. LGA offices: Bade — Gashua; Bursari — Dapchi; Damaturu; Fika — behind the LGA secretariat, Fika; Fune — Damagum; Geidam; Gujba — Buni Yadi; Gulani — Bara; Jakusko; Karasuwa — Jajimaji; Machina; Nangere; Nguru; Potiskum; Tarmuwa — Babbangida; Yunusari — adjacent to the LGA secretariat, Yunusari; Yusufari — opposite the LGA secretariat, Yusufari."),
}
for k in ("WPYO", "FGY", "ATLAS"):
    SOURCES[k] = P69.SOURCES[k]
for k in ("NCMMP", "NCMMM", "WHNW"):
    SOURCES[k] = H72.SOURCES[k]
SOURCES["WDAM"] = I71.SOURCES["WDAM"]
SOURCES["WPOT"] = I71.SOURCES["WPOT"]
SOURCES["WGASH"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Gashua", organisation="Wikipedia", url=W("Gashua"),
                        verification_status="needs_corroboration", notes="Headquarters of Bade LGA since 1976; the court of the Mai Bade. Accessed 2026-10-01.")
PEO = "The peoples recorded for the LGA in this archive include the {p}."
# slug: (name, Wikipedia title or None, Statoids HQ, 2006 pop, area km2, HQ used, agreeing HQ sources, text)
LGAS = {
 "bade": ("Bade", "Bade, Nigeria", "Gashua", 139782, 772, "Gashua", "SW",
          "INEC's office is also at Gashua, which Wikipedia says has been the LGA headquarters since 1976 and is the seat of the Mai Bade, the Emir of Bade. The Dagona Bird Sanctuary, a proposed national monument, is in the LGA. " + PEO.format(p="Bade and Kanuri")),
 "bursari": ("Bursari", "Bursari", "Dapchi", 109124, 3818, "Dapchi", "SW", "INEC's office is also at Dapchi. Wikipedia names Kanuri as its language. " + PEO.format(p="Kanuri")),
 "damaturu": ("Damaturu", "Damaturu", "Damaturu", 88014, 2366, "Damaturu", "S",
              "Damaturu is the capital of Yobe State and the seat of the Damaturu Emirate (Wikipedia), and the National Museum Damaturu is there. " + PEO.format(p="Karekare and Kanuri")),
 "fika": ("Fika", "Fika, Nigeria", "Fika", 136895, 2208, "Fika", "SI",
          "Wikipedia gives an area of 2,852 km², calls Gadaka the largest town in the LGA, and lists among its districts Fika, Gadaka and Gudi. Goya Gorge and the Ngeji Escarpment, in Fika, are a proposed national monument. "
          + PEO.format(p="Bolewa, Karekare and Ngamo")),
 "fune": ("Fune", "Fune", "Damagun", 300760, 4948, "Damagum", "SW",
          "INEC's office is also at Damagum. It had the largest population and the largest area of the state's LGAs at the 2006 census. The Dufuna archaeological site, find-spot of an 8,000-year-old canoe, is in the LGA. "
          + PEO.format(p="Bura, Karekare and Ngizim")),
 "geidam": ("Geidam", "Geidam", "Geidam", 157295, 4357, "Geidam", "S",
            "Geidam is the seat of the Ngazargamu Emirate, and the ruins of old Ngazargamu, capital of the Kanem–Bornu Empire, a proposed national monument, are in the LGA. Wikipedia records that ISWAP fighters seized the town on 24 April 2021 before the army retook it. "
            + PEO.format(p="Fulani and Kanuri")),
 "gujba": ("Gujba", "Gujba", "Buniyadi", 130088, 3239, "Buni Yadi", "SW", "INEC's office is also at Buni Yadi; the town of Gujba lies in the north of the LGA (Wikipedia). " + PEO.format(p="Karekare and Kanuri")),
 "gulani": ("Gulani", "Gulani", "Bara", 103510, 2090, "Bara", "SW", "INEC's office is also at Bara. Blench's Atlas places the Maaka language at Gulani and Bara towns. " + PEO.format(p="Bura, Karekare and Kanuri")),
 "jakusko": ("Jakusko", "Jakusko", "Jakusko", 229083, 3941, "Jakusko", "S", "Wikipedia names Bade, Fulani and Karai-karai as its languages. " + PEO.format(p="Bade, Fulani and Karekare")),
 "karasuwa": ("Karasuwa", "Karasuwa", "Jajimaji", 106992, 1162, "Jajimaji", "SW",
              "INEC's office is also at Jajimaji, on the Hadejia River; Wikipedia describes the LGA as 70 per cent Manga. " + PEO.format(p="Kanuri")),
 "machina": ("Machina", "Machina, Nigeria", "Machina", 61606, 1213, "Machina", "S",
             "It borders the Republic of Niger and had the smallest population of the state's LGAs at the 2006 census; Wikipedia also gives the name Matsena. " + PEO.format(p="Manga")),
 "nangere": ("Nangere", "Nangere", "Sabon Gari Nanger", 87823, 980, "Sabon Gari Nangere", "S", "INEC's office is at Nangere. " + PEO.format(p="Karekare")),
 "nguru": ("Nguru", "Nguru, Nigeria", "Nguru", 150632, 916, "Nguru", "S",
           "Wikipedia gives a population of 270,632, places the town near the Hadejia River and dates it probably to the 15th century; it is the terminus of the Western Railway, and the Hadejia-Nguru wetlands of Nguru Lake are a Ramsar Site. "
           + PEO.format(p="Kanuri")),
 "potiskum": ("Potiskum", "Potiskum", "Potiskum", 205876, 559, "Potiskum", "S",
              "Wikipedia calls Potiskum the largest and most populous city in Yobe State; it is the seat of both the Fika and Potiskum (Pataskum) emirates, and it had the smallest area of the state's LGAs. "
              + PEO.format(p="Bolewa, Karekare and Ngizim")),
 "tarmua": ("Tarmua", "Tarmuwa", "Babangida", 77204, 4594, "Babangida", "SW",
            "INEC and Wikipedia spell the LGA Tarmuwa, and INEC's office is at Babbangida; Wikipedia gives a population of 177,204 and places Tarmuwa town under the Jajere Emirate. No people is recorded for the LGA in this archive yet."),
 "yunusari": ("Yunusari", "Yunusari", "Kanamga", 125821, 3790, "Kanamma", "SW",
              "INEC places its office beside the LGA secretariat at Yunusari town, so the headquarters is uncertain. The LGA borders the Republic of Niger (Wikipedia), and Blench's Atlas places Uled Suliman Arabic in it. " + PEO.format(p="Kanuri")),
 "yusufari": ("Yusufari", "Yusufari", "Yusufari", 111086, 3928, "Yusufari", "SI", "It borders the Republic of Niger (Wikipedia). " + PEO.format(p="Kanuri")),
}
CONFLICT = {"yunusari"}
REUSE_PLACE = {"damaturu": "@places:damaturu"}
for slug, (name, wt, *_r) in LGAS.items():
    SOURCES[f"W_{slug}"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=wt, organisation="Wikipedia", url=W(wt),
                                verification_status="needs_corroboration", notes=f"Wikipedia article on {name}, consulted {ACCESSED}.")
KEYS = {"WPYO": ["peoples recorded"], "ATLAS": ["Blench's Atlas"], "NCMMP": ["proposed national monument"], "NCMMM": ["National Museum"], "WHNW": ["Ramsar"],
        "WDAM": ["Damaturu Emirate"], "WPOT": ["Pataskum"], "WGASH": ["Mai Bade"]}
NAMES_OF = {"S": "Statoids", "I": "INEC", "W": "Wikipedia"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SIW" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, wt, s_hq, pop, area, hq, agree, extra = LGAS[slug]
    t = f"{name} is a local government area of Yobe State with its headquarters at {hq} (according to {by(agree)})."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    return (t + (" " + extra if extra else "")).strip()


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("INECY", f"{name}: INEC LGA office address"), (f"W_{slug}", f"{name}: Wikipedia article")]
    for k, kws in KEYS.items():
        if any(w in extra for w in kws):
            srcs.append((k, f"{name}: {SOURCES[k]['title']}"))
    if slug in REUSE_PLACE:
        upd["headquarters_place_id"] = REUSE_PLACE[slug]
    else:
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("INECY", f"LGA office by the LGA secretariat at {hq}")] if "I" in agree else []) \
               + ([(f"W_{slug}", f"Headquarters of {name}")] if "W" in agree else [])
        two = len(agree) >= 2 and slug not in CONFLICT
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if len(agree) >= 2 else "single_reliable_source", level="well_documented" if two else "reported",
                            fields=dict(place_type="town", name=hq, slug=slugify(hq), admin_unit_id=f"@admin_units:lga:yobe/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Yobe State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:yobe/{slug}", fields=upd, srcs=srcs))
    pnote = {"tarmua": "; Wikipedia gives 177,204", "nguru": "; Wikipedia gives 270,632"}.get(slug, "")
    STATS.append(dict(record=f"@admin_units:lga:yobe/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids" + pnote, source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:yobe/{slug}", metric="area_km2", value_low=area, method="other",
                      notes="Area as given by Statoids" + ("; Wikipedia gives 2,852 km²" if slug == "fika" else ""), source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_fune", name="Damagun", name_type="spelling_variant", usage_notes="Statoids.", srcs=["STAT"]),
    dict(record="hq_gujba", name="Buniyadi", name_type="spelling_variant", usage_notes="Statoids.", srcs=["STAT"]),
    dict(record="hq_nangere", name="Sabon Gari Nanger", name_type="spelling_variant", usage_notes="Statoids' form.", srcs=["STAT"]),
    dict(record="hq_tarmua", name="Babbangida", name_type="spelling_variant", usage_notes="INEC.", srcs=["INECY"]),
    dict(record="hq_yunusari", name="Kanamga", name_type="spelling_variant", usage_notes="Statoids.", srcs=["STAT"]),
    dict(record="hq_yunusari", name="Kanama", name_type="spelling_variant", usage_notes="Wikipedia ('Kanamma (or Kanama)').", srcs=["W_yunusari"]),
]
RELATIONS = [dict(frm="@places:hadejia-nguru-wetlands", type="located_in", to="@admin_units:lga:yobe/nguru", source="W_nguru", evidence="single_reliable_source", level="reported",
                  notes="Wikipedia (Nguru): 'the protected Hadejia-Nguru wetlands of Nguru Lake' in the Nguru area.")]
GAPS = [
    ("Yobe: LGA headquarters", "Seven headquarters rest on Statoids alone (Damaturu, Geidam, Jakusko, Machina, Nangere, Nguru, Potiskum); Yunusari is Kanamma (Statoids, Wikipedia) or Yunusari town (INEC office by the secretariat). An official Yobe State list would settle them."),
    ("Yobe: Nangere headquarters", "Statoids writes 'Sabon Gari Nanger' (apparently truncated); recorded as Sabon Gari Nangere, with Statoids' form kept as a variant. INEC's office is at Nangere. A source giving the full name is needed."),
    ("Yobe: figures", "Tarmua 2006: Statoids 77,204, Wikipedia 177,204; Nguru: Statoids 150,632, Wikipedia 270,632; Fika area: Statoids 2,208 km², Wikipedia 2,852 km². Statoids recorded."),
    ("Yobe: Tarmua/Tarmuwa", "INEC, the federal profile and Wikipedia write Tarmuwa; the archive's LGA record is Tarmua (not renamed)."),
    ("Yobe: Jajere Emirate's date", "Wikipedia (Tarmuwa) says the Jajere Emirate was created in 1992; Wikipedia's list of emirates gives 6 January 2000."),
    ("Yobe: LGA creation dates", "Not found except Gashua as Bade's headquarters since 1976 (Wikipedia)."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATS,
                scope="Yobe LGA profiles: headquarters, 2006 population and area, short sourced descriptions (17 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 073 — Yobe LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Yobe. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **17 LGA descriptions**, filling fields that were empty.",
         f"- **{len(RECORDS)} headquarters towns**, created as places. Damaturu reuses the existing place.",
         "- **Figures:** the 2006 population and area of each LGA, from Statoids.",
         "- **Sources for the headquarters:** Statoids, INEC's LGA offices that sit by the LGA secretariat, and Wikipedia. INEC's office list is saved in the repo.",
         "- **Yunusari is *reported*:** Statoids and Wikipedia say Kanamma, but INEC's office is beside the LGA secretariat at Yunusari town.",
         "- **Conflicting figures:** Wikipedia's Tarmua and Nguru populations and Fika's area differ from Statoids. They are noted; Statoids' figures are used.",
         "- **The Hadejia-Nguru Wetlands** (batch 072) are linked to Nguru LGA.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Statoids | Sources | Level |", "|---|---|---|---|---|"]
    for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
        lvl = "reported" if (slug in CONFLICT or len(agree) < 2) else "well documented"
        L.append(f"| {name} | {hq} | {s_hq} | {by(agree)} | {lvl}{' (reused place)' if slug in REUSE_PLACE else ''} |")
    L += ["", "## The 17 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_073_yobe_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_073_yobe_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)} names={len(NAMES)}")
