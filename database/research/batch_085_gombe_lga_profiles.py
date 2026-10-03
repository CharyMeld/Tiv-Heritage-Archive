"""
Research batch 085 — Gombe LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
11 LGAs). Researched 2026-10-02. Pattern: batches 061, 067, 073 and 079.

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.GO.*; Balanga's HQ written 'Tallase').
  W  Wikipedia's LGA article, where it names the headquarters explicitly (Akko — Kumo; Balanga — Talasse; Funakaye —
     Bajoga; Kaltungo; Kwami — Mallam Sidi; Nafada; Shongom — Boh; Yamaltu/Deba — Deba). Billiri and Dukku articles do
     not state the headquarters → Statoids only, 'reported'.
  INEC's 'GOMBE-STATE.pdf' LGA-office list (used for other states) was never archived by the Internet Archive and is
  not used.
Grading: two agreeing sources = well documented; one = reported.
Reused place: Gombe (#76, 'gombe').
Figures: Statoids. Conflicts noted, not used: Gombe — Wikipedia 280,000 (Statoids 268,000); Balanga — Wikipedia
176,944 (Statoids 212,549).
"""
import json, re, sys
import batch_081_gombe_languages as L81
import batch_083_gombe_institutions as I83
import batch_084_gombe_heritage as H84

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Gombe rows (NG.GO.*): 2006 census, area, headquarters."),
    "ATLAS": L81.SOURCES["ATLAS"],
    "WKWA": WS("Kwami", "Headquarters Mallam Sidi (also written Malam Sidi); bordered on the east by Lake Dadin Kowa."),
    "WSHO": WS("Shongom", "Headquarters Boh, in the north of the LGA; area 922 km²; 2006 population 151,520."),
    "WGCITY": WS("Gombe, Nigeria", "Capital of Gombe State; the LGA has an area of 52 km² and a 2006 population of 280,000."),
}
for k in ("WAKK", "WPIN", "WBAL", "WBIL", "WDUK", "WFUN", "WGE", "WNAF", "WYD", "WKAL"):
    SOURCES[k] = I83.SOURCES[k]
for k in ("NCMMP", "NCMMM", "WBUR", "WDKD"):
    SOURCES[k] = H84.SOURCES[k]
WIKI = {"akko": "WAKK", "balanga": "WBAL", "billiri": "WBIL", "dukku": "WDUK", "funakaye": "WFUN", "gombe": "WGCITY", "kaltungo": "WKAL",
        "kwami": "WKWA", "nafada": "WNAF", "shomgom": "WSHO", "yamaltu-deba": "WYD"}
PEO = "The peoples recorded for the LGA in this archive include the {p}."
# slug: (name, Statoids HQ, 2006 pop, area km2, HQ used, agreeing HQ sources, text, extra source keys)
LGAS = {
 "akko": ("Akko", "Kumo", 337853, 2627, "Kumo", "SW",
          "Wikipedia calls Kumo the second-largest centre in the state after Gombe, and says the LGA has three emirates, Akko, Gona and Pindiga; Pindiga is the capital of the Pindiga Emirate. "
          + PEO.format(p="Fulani, Jukun and Tangale"), ["WPIN"]),
 "balanga": ("Balanga", "Tallase", 212549, 1626, "Talasse", "SW",
             "Statoids writes the headquarters Tallase. Wikipedia gives a population of 176,944 and names the Hausa, Fulani and Waja as its main peoples; the Dadiya and Waja chiefdoms are linked to the LGA, and Blench's Atlas places Cen Tuum, a language isolate now probably extinct, at Cham town. "
             + PEO.format(p="Bangunji, Dadiya, Lunguda, Tangale, Tsobo and Waja"), ["ATLAS"]),
 "billiri": ("Billiri", "Billiri", 202144, 737, "Billiri", "S",
             "Wikipedia says Billiri, also known as Tangle, is historically and today a major settlement of the Tangale, whose ruler, the Mai Tangle, reigns there; it describes the protests that followed the appointment of a new Mai in 2021. "
             + PEO.format(p="Tangale"), []),
 "dukku": ("Dukku", "Dukku", 207190, 3815, "Dukku", "S",
           "Dukku is the seat of the Dukku Emirate, which Wikipedia says was created out of the Gombe Emirate in 2001; the Gongola River flows through the west and north of the LGA, and Wikipedia names the Fulani as the main group. "
           + PEO.format(p="Bolewa and Fulani"), []),
 "funakaye": ("Funakaye", "Bajoga", 236087, 1415, "Bajoga", "SW",
              "Blench's Atlas and the National Commission for Museums and Monuments still use the older name 'Bajoga LGA'; the commission places there the Mbormi (Burmi) battle ground of 1903, a proposed national monument. Wikipedia notes limestone mining by Lafarge since the 1990s. "
              + PEO.format(p="Fulani"), ["NCMMP", "WBUR", "ATLAS"]),
 "gombe": ("Gombe", "Gombe", 268000, 52, "Gombe", "SW",
           "Gombe is the capital of Gombe State and the seat of the Gombe Emirate, whose capital the British moved there, then called Gombe Doma, in 1919 (Wikipedia). It is the smallest LGA in area; Wikipedia gives a 2006 population of 280,000. The National Museum Gombe and the Emir's Palace are in the city. "
           + PEO.format(p="Fulani"), ["WGE", "NCMMM"]),
 "kaltungo": ("Kaltungo", "Kaltungo", 149805, 881, "Kaltungo", "SW",
              "Wikipedia lists among its festivals the Kamo Cultural Festival and the Tangale (Dog) Festival; the Kaltungo and Tula chiefdoms are seated in the LGA, and Blench's Atlas places the Tula, Ma (Kamo) and Yebu (Awak) languages here. "
              + PEO.format(p="Awak, Cham, Kamo, Tangale and Tula"), ["ATLAS"]),
 "kwami": ("Kwami", "Mallam Sidi", 195298, 1787, "Mallam Sidi", "SW",
           "Wikipedia writes the headquarters both Mallam Sidi and Malam Sidi, and says the LGA is bordered on the east by Lake Dadin Kowa. Blench's Atlas places the Kwaami language here. "
           + PEO.format(p="Fulani and Kanuri"), ["ATLAS"]),
 "nafada": ("Nafada", "Nafada", 138185, 1586, "Nafada", "SW",
            "Wikipedia says Nafada lies in the traditional land of the Bole people and was the capital of the Gombe Emirate from 1913 to 1919; it is the seat of the Nafada Emirate. "
            + PEO.format(p="Bolewa and Fulani"), []),
 "shomgom": ("Shongom", "Boh", 151520, 922, "Boh", "SW",
             "INEC, Statoids and the archive write the LGA Shomgom; Wikipedia writes Shongom and places Boh in the north of the LGA. Blench's Atlas places the Bangjinge, Burak, Goji and Pero languages here, Pero around Filiya. "
             + PEO.format(p="Pero and Tangale"), ["ATLAS"]),
 "yamaltu-deba": ("Yamaltu/Deba", "Deba", 255248, 1981, "Deba", "SW",
                  "Wikipedia says the LGA is inhabited mainly by Tera and Fulani people and dates Deba's history to 1375; the Deba and Yamaltu emirates and the Dadin Kowa Dam are in the LGA. "
                  + PEO.format(p="Fulani and Tera"), ["WDKD"]),
}
REUSE_PLACE = {"gombe": "@places:gombe"}
NAMES_OF = {"S": "Statoids", "W": "Wikipedia"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SW" if c in agree]
    return n[0] if len(n) == 1 else " and ".join(n)


def text(slug):
    name, s_hq, pop, area, hq, agree, extra, _k = LGAS[slug]
    t = f"{name} is a local government area of Gombe State with its headquarters at {hq} (according to {by(agree)})."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    return (t + (" " + extra if extra else "")).strip()


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, s_hq, pop, area, hq, agree, extra, keys) in LGAS.items():
    w = WIKI[slug]
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), (w, f"{name}: Wikipedia article")]
    srcs += [(k, f"{name}: {SOURCES[k]['title']}") for k in keys if k != w]
    if slug in REUSE_PLACE:
        upd["headquarters_place_id"] = REUSE_PLACE[slug]
    else:
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([(w, f"Headquarters of {name}")] if "W" in agree else [])
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if len(agree) >= 2 else "single_reliable_source", level="well_documented" if len(agree) >= 2 else "reported",
                            fields=dict(place_type="town", name=hq, slug=slugify(hq), admin_unit_id=f"@admin_units:lga:gombe/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Gombe State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:gombe/{slug}", fields=upd, srcs=srcs))
    pnote = {"gombe": "; Wikipedia gives 280,000", "balanga": "; Wikipedia gives 176,944"}.get(slug, "")
    STATS.append(dict(record=f"@admin_units:lga:gombe/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids" + pnote, source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:gombe/{slug}", metric="area_km2", value_low=area, method="other",
                      notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_balanga", name="Tallase", name_type="spelling_variant", usage_notes="Statoids.", srcs=["STAT"]),
    dict(record="hq_kwami", name="Malam Sidi", name_type="spelling_variant", usage_notes="Wikipedia (Kwami) uses both spellings.", srcs=["WKWA"]),
    dict(record="hq_yamaltu-deba", name="Deba Habe", name_type="alternative", usage_notes="Wikipedia: 'Deba (or Deba Habe)'.", srcs=["WYD"]),
]
RELATIONS = []
GAPS = [
    ("Gombe: LGA headquarters", "Billiri and Dukku rest on Statoids alone (their Wikipedia articles do not state the headquarters). INEC's Gombe LGA-office list, used for other states, was never archived; an official Gombe State list would settle them."),
    ("Gombe: figures", "Gombe LGA 2006: Statoids 268,000, Wikipedia 280,000; Balanga: Statoids 212,549, Wikipedia 176,944. Statoids recorded."),
    ("Gombe: Shomgom/Shongom", "INEC, Statoids and the archive write Shomgom; Wikipedia, the federal profile and Blench's Atlas write Shongom. Not renamed."),
    ("Gombe: LGA creation dates", "The federal profile says most LGAs were created in 1989 or 1992 and three more in 1996, without naming them."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=RELATIONS, statistics=STATS,
                scope="Gombe LGA profiles: headquarters, 2006 population and area, short sourced descriptions (11 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 085 — Gombe LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Gombe. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **11 LGA descriptions**, filling fields that were empty.",
         f"- **{len(RECORDS)} headquarters towns**, created as places. Gombe reuses the existing place.",
         "- **Figures:** the 2006 population and area of each LGA, from Statoids.",
         "- **Sources for the headquarters:** Statoids and Wikipedia. INEC's Gombe office list was never archived, so it is not used.",
         "- **Billiri and Dukku are *reported*:** their Wikipedia articles do not name the headquarters.",
         "- **Conflicting figures:** Wikipedia's populations for Gombe and Balanga differ from Statoids. They are noted; Statoids' figures are used.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Statoids | Sources | Level |", "|---|---|---|---|---|"]
    for slug, (name, s_hq, pop, area, hq, agree, extra, _k) in LGAS.items():
        lvl = "reported" if len(agree) < 2 else "well documented"
        L.append(f"| {name} | {hq} | {s_hq} | {by(agree)} | {lvl}{' (reused place)' if slug in REUSE_PLACE else ''} |")
    L += ["", "## The 11 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_085_gombe_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_085_gombe_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)} names={len(NAMES)}")
