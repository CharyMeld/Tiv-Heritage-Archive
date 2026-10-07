"""
Research batch 170 — Kaduna LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
23 LGAs). Researched 2026-10-07. Pattern: batches 158, 164.

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.KD.*).
  W  Wikipedia: each LGA's page ('Its headquarters are in …', or 'X is a town and Local Government Area'), and the table
     of the 12 Southern Kaduna LGAs and their headquarters in 'Southern Kaduna'.
  I  INEC, 'Kaduna State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/KADUNA-STATE.pdf via the
     Internet Archive; copy in data/inec_kaduna_lga_offices.pdf). Counted where the office is in the headquarters town.
Differences: Kaduna North (Doka) and Kaduna South (Makera) — INEC's offices are at Magajin Gari and Barnawa, also in
Kaduna city. Igabi, Kudan, Sabon Gari and Zaria — Wikipedia names no headquarters. Soba — Statoids writes Maigama,
Wikipedia and INEC Maigana. Sanga — Wikipedia gives Gbantu (Hausa: Gwantu); Statoids and INEC write Gwantu. Jaba —
INEC writes Ngarsu-Kwoi. Doka and Makera are parts of Kaduna city and are recorded as settlements. Slugs end in '-kaduna'.
Peoples in each description come from batch 166c (its JSON is read from the output directory).
"""
import json, re, sys

ACCESSED = "2026-10-07"
OUT = sys.argv[1] if len(sys.argv) > 1 else "."
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Kaduna rows (NG.KD.*): 2006 census, area, headquarters (Zaria listed as 'Zarki')."),
    "INECS": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Kaduna State: INEC LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/KADUNA-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (7 October 2026); copy in database/research/data/inec_kaduna_lga_offices.pdf.",
                  notes="LGA office addresses for all 23 LGAs; state office at No. 109 Isa Kaita Road, Ungwar Rimi, Kaduna."),
    "WLGA": WS("Kaduna State", "Headquarters taken from each LGA's own Wikipedia page and from the table of Southern Kaduna LGAs in 'Southern Kaduna'."),
}
# slug: (name, hq, statoids pop, area, agreement, inec office, extra, place_type)
LGAS = {
 "birnin-gwari": ("Birnin Gwari", "Birnin Gwari", 252363, 6185, "SWI", "Birnin Gwari", "Birnin Gwari is the seat of the Birnin Gwari Emirate, raised to a full emirate in 1981, and the area has suffered repeated attacks by bandits (Wikipedia).", "town"),
 "chikun": ("Chikun", "Kujama", 368250, 4645, "SWI", "Kujama", "It takes its name from a Gbagyi village and was originally populated by the Gbagyi; Kajuru LGA was carved out of it in 1997 (Wikipedia).", "town"),
 "giwa": ("Giwa", "Giwa", 286427, 2066, "SWI", "Giwa", "Wikipedia gives its creation as 15 September 1991.", "town"),
 "igabi": ("Igabi", "Turunku", 430229, 3727, "SI", "Turunku", "Rigasa, one of the most populous wards in the state, is in the LGA (Wikipedia).", "town"),
 "ikara": ("Ikara", "Ikara", 193926, 853, "SWI", "Ikara", "", "town"),
 "jaba": ("Jaba", "Kwoi", 155377, 368, "SWI", "Ngarsu-Kwoi", "It is named after the Hausa name for the Ham, who occupy most of it; Nok, which gave its name to the Nok culture, is a ward of the LGA (Wikipedia; INEC).", "town"),
 "jema-a": ("Jema'a", "Kafanchan", 278735, 1661, "SWI", "Kafanchan", "Kafanchan is the seat of the Jema'a Emirate; the Fantswam, the people of Kafanchan, have had their own chiefdom since 2001 (Wikipedia).", "town"),
 "kachia": ("Kachia", "Kachia", 244274, 4632, "SWI", "Kachia", "Zangon Kataf LGA was created from it in 1989 (Wikipedia).", "town"),
 "kaduna-north": ("Kaduna North", "Doka", 357694, 72, "SW", "Magajin Gari, Kaduna", "Wikipedia calls it the pioneer local government of Kaduna, the state capital.", "settlement"),
 "kaduna-south": ("Kaduna South", "Makera", 402390, 59, "SW", "Barnawa, Kaduna", "It is part of the Kaduna metropolis (Wikipedia).", "settlement"),
 "kagarko": ("Kagarko", "Kagarko", 240943, 1864, "SWI", "Kagarko", "Wikipedia names the Batinor (Koro) as its dominant group.", "town"),
 "kajuru": ("Kajuru", "Kajuru", 110868, 2464, "SWI", "Kajuru", "It was carved out of Chikun LGA in March 1997 (Wikipedia).", "town"),
 "kaura": ("Kaura", "Kaura", 222579, 485, "SWI", "Kaura", "It was carved out of Jema'a LGA; Kagoro, seat of the Agworok chiefdom and of the Afan festival, is in the LGA (Wikipedia).", "town"),
 "kauru": ("Kauru", "Kauru", 170008, 2810, "SWI", "Kauru", "", "town"),
 "kubau": ("Kubau", "Anchau", 282045, 2505, "SWI", "Anchau", "", "town"),
 "kudan": ("Kudan", "Hunkuyi", 138992, 400, "SI", "Hunkuyi", "", "town"),
 "lere": ("Lere", "Saminaka", 331161, 2158, "SWI", "Saminaka", "Saminaka was the headquarters of the former Saminaka LGA, which Roger Blench's Atlas uses for many of the area's languages (Wikipedia).", "town"),
 "makarfi": ("Makarfi", "Makarfi", 146259, 541, "SWI", "Makarfi", "", "town"),
 "sabon-gari": ("Sabon Gari", "Sabon Gari", 286871, 263, "SI", "Sabon Gari-Zaria", "It is one of the LGAs that make up the city of Zaria, and was created on 27 August 1991 (Wikipedia).", "town"),
 "sanga": ("Sanga", "Gbantu", 149333, 1256, "SWI", "Gwantu", "Wikipedia gives Gbantu, with the Hausa form Gwantu, which Statoids and INEC use.", "town"),
 "soba": ("Soba", "Maigana", 293270, 2234, "SWI", "Maigana", "Statoids writes Maigama. The Habe mosque at Maigana is a declared national monument (NCMM).", "town"),
 "zangon-kataf": ("Zangon Kataf", "Zonkwa", 316370, 2668, "SWI", "Zonkwa", "It was created from Kachia LGA in 1989; the palace of the Agwatyap, head of the Atyap Chiefdom, is at Atak Njei in the LGA (Wikipedia).", "town"),
 "zaria": ("Zaria", "Zaria", 408198, 300, "SI", "Zaria", "Zaria, formerly Zazzau, one of the original seven Hausa city-states, is the seat of the Zazzau Emirate; its city walls are a declared national monument (Wikipedia; NCMM).", "town"),
}
# peoples linked to each LGA by batch 166c
NAME = {"hausa": "Hausa", "fulani": "Fulani", "gbagyi": "Gbagyi", "adara": "Adara", "koro": "Koro", "mada": "Mada", "amo": "Amo", "ninzam": "Ninzam",
        "nyankpa": "Nyankpa", "berom": "Berom", "irigwe": "Irigwe", "zaar": "Zaar"}
PEOPLE = {}
try:
    d = json.load(open(f"{OUT}/batch_166c_kaduna_peoples.json"))
    for r in d["records"]:
        NAME[r["key"]] = r["fields"]["name"]
    for r in d["relations"]:
        if r["type"] == "present_in" and "lga:kaduna/" in r["to"]:
            PEOPLE.setdefault(r["to"].split("/")[-1], []).append(NAME[r["from"].replace("@ethnic_groups:", "")])
except FileNotFoundError:
    pass
NAMES_OF = {"S": "Statoids", "W": "Wikipedia", "I": "INEC"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SWI" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def plist(xs):
    xs = list(dict.fromkeys(xs))
    return xs[0] if len(xs) == 1 else ", ".join(xs[:-1]) + " and " + xs[-1]


def text(slug):
    name, hq, pop, area, agree, office, extra, _ = LGAS[slug]
    t = f"{name} is a local government area of Kaduna State with its headquarters at {hq} (according to {by(agree)})."
    if "I" not in agree:
        t += f" INEC's LGA office is at {office}."
    elif office != hq and slug not in ("sanga",):
        t += f" INEC writes the town as {office}."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    if extra: t += " " + extra
    if PEOPLE.get(slug):
        p = PEOPLE[slug]
        t += f" Wikipedia names the {plist(p)} among {'its peoples' if len(set(p)) > 1 else 'its people'}."
    return t


RECORDS, UPDATES, STATS = [], [], []
for slug, (name, hq, pop, area, agree, office, extra, ptype) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("WLGA", f"{name}: Wikipedia"), ("INECS", f"{name}: INEC LGA office")]
    ref = f"@admin_units:lga:kaduna/{slug}"
    key = f"hq_{slug}"
    hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("WLGA", f"Headquarters of {name} (Wikipedia)")] if "W" in agree else []) + ([("INECS", f"INEC LGA office at {office}")] if "I" in agree else [])
    summ = f"{hq} is the headquarters of {name} Local Government Area, Kaduna State." + (" It is part of Kaduna city." if ptype == "settlement" else "")
    RECORDS.append(dict(key=key, table="places", evidence="multiple_sources", level="well_documented",
                        fields=dict(place_type=ptype, name=hq, slug=slugify(hq) + "-kaduna", admin_unit_id=ref, status="existing", summary=summ), srcs=hsrc))
    upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=ref, fields=upd, srcs=srcs))
    STATS.append(dict(record=ref, metric="population", value_low=pop, reference_year=2006, method="census", notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=ref, metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES = [
    dict(record="hq_sanga", name="Gwantu", name_type="exonym", usage_notes="The Hausa form, used by Statoids and INEC (Wikipedia: 'Gbantu (Hausa: Gwantu)').", srcs=["STAT", "INECS"]),
    dict(record="hq_soba", name="Maigama", name_type="spelling_variant", usage_notes="Statoids' spelling.", srcs=["STAT"]),
    dict(record="hq_jaba", name="Ngarsu-Kwoi", name_type="alternative", usage_notes="INEC's form of the office address.", srcs=["INECS"]),
]
GAPS = [
    ("Kaduna: headquarters of Igabi, Kudan, Sabon Gari and Zaria", "Wikipedia names no headquarters for these LGAs; Statoids and INEC agree (Turunku, Hunkuyi, Sabon Gari, Zaria)."),
    ("Kaduna: Kaduna North and Kaduna South", "Statoids and Wikipedia give Doka and Makera; INEC's offices are at Magajin Gari and Barnawa. All are parts of Kaduna city; the city itself (place #54) is not set as either LGA's headquarters."),
    ("Kaduna: peoples of the northern LGAs", "No source read names the peoples of Birnin Gwari, Giwa, Kudan, Sabon Gari, Soba or Kaduna North; their descriptions do not name peoples."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES, relations=[], statistics=STATS,
                scope="Kaduna LGA profiles: headquarters, 2006 population and area, short sourced descriptions (23 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    n3 = sum(1 for v in LGAS.values() if v[4] == "SWI")
    L = ["# Research batch 170 — Kaduna LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Kaduna. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **23 LGA descriptions** and **{len(RECORDS)} headquarters places** (Doka and Makera, parts of Kaduna city, as settlements). Slugs end in '-kaduna'.",
         "- **Agreement on headquarters:**",
         f"  - Statoids, Wikipedia and INEC agree for {n3} LGAs.",
         "  - **Igabi** (Turunku), **Kudan** (Hunkuyi), **Sabon Gari** and **Zaria**: Statoids and INEC agree; Wikipedia names no headquarters.",
         "  - **Kaduna North** (Doka) and **Kaduna South** (Makera): Statoids and Wikipedia agree; INEC's offices are elsewhere in Kaduna city.",
         "  - **Sanga**: Gbantu (Wikipedia's form) is the record name, with Gwantu (Statoids, INEC) kept as the Hausa name. **Soba**: Maigana, with Statoids' Maigama kept.",
         "- **Each description** names the peoples Wikipedia gives for the LGA (batch 166c) and any seat of a ruler or monument from batches 168–169.",
         "- **Figures:** 2006 population and area from Statoids.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## The 23 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    json.dump(build(), open(f"{OUT}/batch_170_kaduna_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{OUT}/batch_170_kaduna_lga_profiles_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} people-LGAs={len(PEOPLE)}")
