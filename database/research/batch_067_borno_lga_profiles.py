"""
Research batch 067 — Borno LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
27 LGAs). Researched 2026-10-01. Pattern: batches 055 and 061.

Headquarters sources (letters used in the table):
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.BO.*).
  G  Borno State Government, 'Borno State Local Gov't Administration' (bornostate.gov.ng/wb/local-government-
     administration; Internet Archive snapshot of 2025-12-22, copy in data/bornostate_lga_admin_wayback20251222.html):
     a table of LGA, headquarters and chairman (2024–2025). Tier 1, but with evident errors: Guzamala 'Gajiram'
     (Gajiram is Nganzai's headquarters), Nganzai 'Rann' (Rann is Kala/Balge's), Hawul 'Hawul', Jere 'Warabe',
     Bayo 'Bayo'. Where it agrees with the others it counts; where it disagrees it is noted.
  I  INEC, 'Borno State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/BORNO-STATE.pdf via the
     Internet Archive; copy in data/inec_borno_lga_offices.pdf): counted only where the office is by the LGA secretariat
     in a named town (Bayo — Briyel; Chibok; Damboa; Guzamala — Gudumbali; Hawul — Azare; Kaga — Benesheikh;
     Mobbar — Damasak; Nganzai — Gajiram).
  W  Wikipedia's LGA article, where it names the headquarters explicitly.
Grading: two agreeing sources = well documented; one = reported. Two LGAs are graded 'reported' because the
sources conflict: Bayo (Briyel: S, I; Wikipedia 'Fikhayel'; state 'Bayo') and Ngala (Gamboru Ngala: S 'Gambara
Ngala', W; state and INEC 'Ngala').
Reused places: Maiduguri (existing) and Kukawa (batch 066).
Not used from Wikipedia: Konduga LGA 'about 13,400' people (the 2006 census gives 156,564); Damboa '233,200' (Statoids
231,573 used; noted); Abadam '140,000 projected in 2016'.
"""
import json, re, sys
import batch_063b_borno_peoples as P63
import batch_066_borno_heritage as H66

ACCESSED = "2026-10-01"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Borno rows (NG.BO.*): 2006 census, area, headquarters."),
    "BSG": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Borno State Local Gov't Administration",
                organisation="Borno State Government", url="https://bornostate.gov.ng/wb/local-government-administration", verification_status="verified",
                archive_reference="Read via the Internet Archive snapshot of 2025-12-22 (the live page returned 404 on 2026-10-01); copy in database/research/data/.",
                notes="Table of the 27 LGAs with headquarters and chairmen (elected 20 January 2024). Evident errors: Guzamala 'Gajiram', Nganzai 'Rann', Jere 'Warabe', Hawul 'Hawul', Bayo 'Bayo'."),
    "INECB": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Borno State: INEC state and LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/BORNO-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (2025); copy in database/research/data/inec_borno_lga_offices.pdf.",
                  notes="State office No. 2 Airport Road, Maiduguri. Offices by the LGA secretariat: Bayo — Briyel; Chibok; Damboa; Guzamala — Gudumbali; Hawul — Azare; Kaga — Benesheikh; Mobbar — Damasak; Ngala; Nganzai — Gajiram; Bama (town not named). Others: Abadam — Malamfatori; Askira; Biu; Dikwa; Gubio; Gwoza; Jere — Ngudda Addamari; Kala-Balge — Rann; Konduga; Kukawa; Kwaya-Kusar — Kwaya; Mafa; Magumeri; Maiduguri (MMC); Marte; Monguno; Shani."),
}
for k in ("WPBO", "FGB", "ATLAS"):
    SOURCES[k] = P63.SOURCES[k]
for k in ("NCMML", "NCMMP", "NCMMM"):
    SOURCES[k] = H66.SOURCES[k]
PEO = "The peoples recorded for the LGA in this archive include the {p}."
# slug: (name, Wikipedia title or None, Statoids HQ, 2006 pop, area km2, HQ used, agreeing HQ sources, text)
LGAS = {
 "abadam": ("Abadam", "Abadam", "Malumfatori", 100180, 3973, "Malam Fatori", "SGW",
            "Wikipedia places it on the western shore of Lake Chad, bordering Chad and Niger and close to Cameroon, and spells the headquarters Mallam Fatori; it is one of the LGAs of the Borno Emirate."),
 "askira-uba": ("Askira/Uba", "Askira/Uba", "Askira", 138091, 2362, "Askira", "SGW",
                "It has two emirate councils, Askira and Uba, and Askira town was founded by Muhammadu Mai Maina in 1921 (Wikipedia). The village of Lassa in the LGA gave its name to the Lassa virus, first identified in returning missionaries (Wikipedia). "
                + PEO.format(p="Gude, Hausa, Kibaku, Kilba and Marghi")),
 "bama": ("Bama", "Bama, Nigeria", "Bama", 269986, 4997, "Bama", "SG",
          "Bama, about 60 km from Maiduguri, is the seat of the Bama Emirate, of which it is the only LGA; it was captured by Boko Haram in 2014 (Wikipedia). " + PEO.format(p="Kanuri, Mandara, Marghi and Shuwa Arabs")),
 "bayo": ("Bayo", "Bayo, Borno State", "Biriyel", 78978, 956, "Briyel", "SI",
          "The sources disagree on the headquarters: INEC places its office by the LGA secretariat at Briyel (Statoids: Biriyel), Wikipedia names Fikhayel and the state government's table gives Bayo. It is one of the four LGAs of the Biu Emirate (Wikipedia). " + PEO.format(p="Tera")),
 "biu": ("Biu", "Biu, Nigeria", "Biu", 176072, 3315, "Biu", "SGW",
         "Biu lies on the Biu Plateau at about 626 metres and is the capital of the Biu Emirate, the former Biu Kingdom (Wikipedia). Wikipedia names the Babur and Bura, Tera, Marghi, Mina and Fulani as its main peoples. " + PEO.format(p="Bura, Dera, Ga'anda and Tera")),
 "chibok": ("Chibok", "Chibok", "Chibok", 66105, 1350, "Chibok", "SGI",
            "According to Wikipedia most of its people are Kibaku, and the federal profile says the Chibok (Kibaku) inhabit the LGA. The kidnapping of schoolgirls from Chibok in April 2014 led to the closure of most schools in the state (Wikipedia, Borno State). " + PEO.format(p="Kibaku and Marghi")),
 "damboa": ("Damboa", "Damboa", "Damboa", 231573, 6219, "Damboa", "SGI",
            "Wikipedia gives a 2006 population of 233,200, and describes the Marghi as the original settlers, joined by Kanuri drawn by the calabash trade. It is the seat of the Damboa Emirate Council. " + PEO.format(p="Kanuri, Kibaku, Malgwa and Marghi")),
 "dikwa": ("Dikwa", "Dikwa", "Dikwa", 105909, 1774, "Dikwa", "SG",
           "Dikwa was the fortified capital of Rabih az-Zubayr from 1893 to 1900 (Wikipedia), and Rabeh's Fort there is Borno's only declared national monument (No. 13). It is the seat of the Dikwa Emirate. " + PEO.format(p="Shuwa Arabs")),
 "gubio": ("Gubio", "Gubio", "Gubio", 152778, 2464, "Gubio", "SG",
           "Gubio, also written Gobiyo, lies about 97 km from both Damasak and Maiduguri on the road between them; it is one of the LGAs of the Borno Emirate, and 81 people were killed there in a massacre on 9 June 2020 (Wikipedia)."),
 "guzamala": ("Guzamala", "Guzamala", "Gudumbali", 95648, 2517, "Gudumbali", "SIW",
              "The state government's table gives Gajiram, which is Nganzai's headquarters. Gudumbali lies about 125 km north of Maiduguri (Wikipedia). " + PEO.format(p="Kanuri")),
 "gwoza": ("Gwoza", "Gwoza", "Gwoza", 276312, 2883, "Gwoza", "SG",
           "Gwoza, about 135 km south-east of Maiduguri on the Cameroon border, lies below the Gwoza Hills of the Mandara Mountains, which reach about 1,300 metres (Wikipedia). It is the seat of the Gwoza Emirate, and Blench's Atlas places more languages in Gwoza than in any other Borno LGA (eleven). "
           + PEO.format(p="Dghwede, Glavda, Guduf, Kanuri, Lamang, Mafa, Mandara and Sukur")),
 "hawul": ("Hawul", "Hawul", "Azare", 120314, 2098, "Azare", "SIW",
           "The state government's table gives Hawul. It is one of the four LGAs of the Biu Emirate (Wikipedia). " + PEO.format(p="Bura and Hwana")),
 "jere": ("Jere", "Jere, Nigeria", "Khaddamari", 211204, 868, "Khaddamari", "SW",
          "The state government's table gives Warabe, and INEC's office is at Ngudda Addamari. Wikipedia says most of its people are Baggara Arabs and Kanuri. " + PEO.format(p="Kanuri")),
 "kaga": ("Kaga", "Kaga, Nigeria", "Benisheikh", 90015, 2700, "Benisheikh", "SGIW", "INEC spells it Benesheikh. It is one of the LGAs of the Borno Emirate (Wikipedia). " + PEO.format(p="Kanuri")),
 "kala-balge": ("Kala/Balge", "Kala/Balge", "Rann", 60797, 1896, "Rann", "SGW",
                "Wikipedia calls it the easternmost LGA of Nigeria. It is part of the Dikwa Emirate. " + PEO.format(p="Afade, Kanuri and Shuwa Arabs")),
 "konduga": ("Konduga", "Konduga", "Konduga", 156564, 5855, "Konduga", "SG",
             "Konduga lies about 25 km south-east of Maiduguri on the north bank of the Ngadda River, and pottery from about 6300 BP has been found there (Wikipedia). " + PEO.format(p="Kanuri, Mandara, Marghi and Shuwa Arabs")),
 "kukawa": ("Kukawa", "Kukawa", "Kukawa", 203864, 4901, "Kukawa", "SG",
            "Kukawa, founded as Kuka in 1814, was the capital of the al-Kanemi shehus from 1846 until Rabih destroyed it in 1893 (Wikipedia). " + PEO.format(p="Kanuri")),
 "kwaya-kusar": ("Kwaya Kusar", "Kwaya Kusar", "Kwaya-Kusar", 56500, 732, "Kwaya Kusar", "SG",
                 "It had the smallest population of the state's LGAs at the 2006 census. Wikipedia describes its people as mainly Bura and places it in the Biu Emirate. " + PEO.format(p="Bura, Marghi and Tera")),
 "mafa": ("Mafa", None, "Mafa", 103518, 2869, "Mafa", "SG",
          "Despite the name, Blench's Atlas places the Mafa language in Gwoza LGA, not here. It is one of the LGAs of the Borno Emirate (Wikipedia, Borno Emirate)."),
 "magumeri": ("Magumeri", "Magumeri", "Magumeri", 140231, 4856, "Magumeri", "SG", "It is one of the LGAs of the Borno Emirate (Wikipedia)."),
 "maiduguri": ("Maiduguri", "Maiduguri", "Maiduguri", 521492, 132, "Maiduguri", "SG",
               "Maiduguri, the state capital, was founded in 1907 and consists of Yerwa to the west and Old Maiduguri to the east (Wikipedia); it is the seat of the Shehu of Borno and has the National Museum Maiduguri. It had the largest population and the smallest area of the state's LGAs at the 2006 census. "
               + PEO.format(p="Hausa and Kanuri")),
 "marte": ("Marte", "Marte, Nigeria", "Marte", 129370, 3154, "Marte", "SG",
           "Marte lies on the western shore of Lake Chad; its traditional head, the mai of Marte, is described by Wikipedia as one of the oldest continuous political offices in Borno State."),
 "mobbar": ("Mobbar", "Mobbar", "Damasak", 116654, 2790, "Damasak", "SGIW", "It borders the Republic of Niger (Wikipedia)."),
 "monguno": ("Monguno", "Monguno", "Monguno", 109851, 1913, "Monguno", "SGW", "It is an administrative and commercial centre for the surrounding area (Wikipedia). " + PEO.format(p="Kanuri")),
 "ngala": ("Ngala", "Ngala", "Gambara Ngala", 237071, 1465, "Gamboru Ngala", "SW",
           "The state government's table and INEC write the headquarters as Ngala. It borders Cameroon and is part of the Dikwa Emirate (Wikipedia); the El-Kanemi Prayer House at Ngala is a proposed national monument. "
           + PEO.format(p="Kanuri and Shuwa Arabs")),
 "nganzai": ("Nganzai", "Nganzai", "Gajiram", 99799, 2467, "Gajiram", "SIW",
             "The state government's table gives Rann, which is Kala/Balge's headquarters. Wikipedia describes it as up to 90% Kanuri. " + PEO.format(p="Kanuri")),
 "shani": ("Shani", "Shani, Nigeria", "Shani", 102317, 1262, "Shani", "SGW", "It borders Bayo, Hawul and Kwaya Kusar LGAs (Wikipedia). " + PEO.format(p="Dera (Kanakuru)")),
}
CONFLICT = {"bayo", "ngala"}
REUSE_PLACE = {"maiduguri": "@places:maiduguri", "kukawa": "@places:kukawa"}
for slug, (name, wt, *_r) in LGAS.items():
    if wt:
        SOURCES[f"W_{slug}"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=wt, organisation="Wikipedia", url=W(wt),
                                    verification_status="needs_corroboration", notes=f"Wikipedia article on {name}, consulted {ACCESSED}.")
SOURCES["WPBE"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Borno Emirate", organisation="Wikipedia", url=W("Borno Emirate"),
                       verification_status="needs_corroboration", notes="Reused.")
KEYS = {"WPBO": ["peoples recorded", "Borno State)"], "FGB": ["federal profile"], "ATLAS": ["Blench's Atlas"], "WPBE": ["Wikipedia, Borno Emirate"],
        "NCMML": ["declared national monument"], "NCMMP": ["proposed national monument"], "NCMMM": ["National Museum"]}
NAMES_OF = {"S": "Statoids", "G": "the state government", "I": "INEC", "W": "Wikipedia"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SGIW" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, wt, s_hq, pop, area, hq, agree, extra = LGAS[slug]
    t = f"{name} is a local government area of Borno State with its headquarters at {hq} (according to {by(agree)})."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    return (t + (" " + extra if extra else "")).strip()


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("BSG", f"{name}: headquarters (state table)"), ("INECB", f"{name}: INEC LGA office address")]
    if wt:
        srcs.append((f"W_{slug}", f"{name}: Wikipedia article"))
    for k, kws in KEYS.items():
        if any(w in extra for w in kws):
            srcs.append((k, f"{name}: {SOURCES[k]['title']}"))
    if slug in REUSE_PLACE:
        upd["headquarters_place_id"] = REUSE_PLACE[slug]
    else:
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("BSG", f"Headquarters of {name}")] if "G" in agree else []) \
               + ([("INECB", f"LGA office by the LGA secretariat at {hq}")] if "I" in agree else []) + ([(f"W_{slug}", f"Headquarters of {name}")] if "W" in agree else [])
        two = len(agree) >= 2 and slug not in CONFLICT
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if len(agree) >= 2 else "single_reliable_source", level="well_documented" if two else "reported",
                            fields=dict(place_type="town", name=hq, slug=slugify(hq), admin_unit_id=f"@admin_units:lga:borno/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Borno State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:borno/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:borno/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids" + ("; Wikipedia gives 233,200" if slug == "damboa" else ""), source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:borno/{slug}", metric="area_km2", value_low=area, method="other",
                      notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_abadam", name="Mallam Fatori", name_type="spelling_variant", usage_notes="Wikipedia.", srcs=["W_abadam"]),
    dict(record="hq_abadam", name="Malumfatori", name_type="spelling_variant", usage_notes="Statoids.", srcs=["STAT"]),
    dict(record="hq_bayo", name="Biriyel", name_type="spelling_variant", usage_notes="Statoids.", srcs=["STAT"]),
    dict(record="hq_kaga", name="Benesheikh", name_type="spelling_variant", usage_notes="INEC.", srcs=["INECB"]),
    dict(record="hq_ngala", name="Gambara Ngala", name_type="spelling_variant", usage_notes="Statoids.", srcs=["STAT"]),
]
RELATIONS = []
GAPS = [
    ("Borno: the state's table of LGA headquarters", "The Borno State Government's own table (2024–2025) gives wrong headquarters for Guzamala (Gajiram), Nganzai (Rann), Jere (Warabe), Hawul (Hawul) and Bayo (Bayo); the archive follows Statoids, INEC and Wikipedia there. A corrected official list would settle them."),
    ("Borno: Bayo and Ngala headquarters", "Bayo: Briyel (Statoids, INEC), Fikhayel (Wikipedia) or Bayo (state). Ngala: Gamboru Ngala (Wikipedia; Statoids 'Gambara Ngala') or Ngala (state, INEC). Both recorded at 'reported'."),
    ("Borno: figures", "Damboa's 2006 population: Statoids 231,573, Wikipedia 233,200 (Statoids recorded). Wikipedia's Konduga figure ('about 13,400') is not used."),
    ("Borno: LGA creation dates", "Not found in the sources read."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=RELATIONS, statistics=STATS,
                scope="Borno LGA profiles: headquarters, 2006 population and area, short sourced descriptions (27 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 067 — Borno LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Borno. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **27 LGA descriptions**, filling fields that were empty.",
         f"- **{len(RECORDS)} headquarters towns**, created as places. Maiduguri and Kukawa reuse existing places.",
         "- **Figures:** the 2006 population and area of each LGA, from Statoids.",
         "- **Sources for the headquarters:**",
         "  - Statoids",
         "  - the **Borno State Government's own table** (official, read via the Internet Archive)",
         "  - INEC's LGA offices that sit by the LGA secretariat",
         "  - Wikipedia",
         "- **The state's table has five evident errors.** For example, it gives Guzamala 'Gajiram' (Nganzai's headquarters) and Nganzai 'Rann' (Kala/Balge's). These are overridden by the other sources and noted in each text.",
         "- **Two LGAs are recorded as *reported*, because the sources conflict:**",
         "  - **Bayo:** Briyel, Fikhayel or Bayo",
         "  - **Ngala:** Gamboru Ngala or Ngala",
         "- **Each description draws on the archive's own records:** the LGA's peoples, its emirate and its heritage (batches 063–066).",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Statoids | Sources | Level |", "|---|---|---|---|---|"]
    for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
        lvl = "reported" if (slug in CONFLICT or len(agree) < 2) else "well documented"
        L.append(f"| {name} | {hq} | {s_hq} | {by(agree)} | {lvl}{' (reused place)' if slug in REUSE_PLACE else ''} |")
    L += ["", "## The 27 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_067_borno_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_067_borno_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)} names={len(NAMES)}")
