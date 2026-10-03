"""
Research batch 121 — Ogun LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
20 LGAs). Researched 2026-10-02. Pattern: batch 115 (Lagos).

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.OG.*; Statoids writes Aiyetoro, Ake Abeokuta).
  W  Wikipedia, 'Ogun State': the senatorial districts list each LGA with its headquarters in brackets (Akomoje, Ake,
     Otta, Ayetoro, Ilaro, Itori, Ifo, Ogbere, Ijebu Igbo, Attan, Ijebu Ode, Ikenne Remo, Imeko, Ipokia, Owode Egba, Odeda,
     Odogbolu, Abigi, Ilisan Remo, Sagamu).
  I  INEC, 'Ogun State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/OGUN-STATE.pdf via the
     Internet Archive; copy in data/inec_ogun_lga_offices.pdf). Counted where the office is in the headquarters town;
     several are at or beside the LGA secretariat.
Disagreements: Remo North — Statoids and INEC (office on Local Govt. Secretariat Road, Isara) give Isara, Wikipedia
Ilisan Remo → Isara, noted. Obafemi Owode — Statoids and Wikipedia give Owode; INEC's office address is at Odeda →
Owode, noted. Ijebu North East — INEC's address names no town.
The archive's 'Egbado North' and 'Egbado South' are Yewa North and Yewa South in the federal profile and Wikipedia;
the descriptions say 'Yewa North, formerly Egbado North'. The record names are changed in the QC fix (batch 122).
Reused place: none (Abeokuta, the existing place, is the state capital; Ake is the Abeokuta South headquarters).
"""
import json, re, sys
import batch_117_ogun_languages_peoples as P117

ACCESSED = "2026-10-02"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Ogun rows (NG.OG.*): 2006 census, area, headquarters."),
    "INECO": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Ogun State: INEC LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/OGUN-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (2 October 2026); copy in database/research/data/inec_ogun_lga_offices.pdf.",
                  notes="LGA office addresses for all 20 LGAs; state headquarters opposite the Olusegun Obasanjo Presidential Library, Magbon, Abeokuta."),
    "WOGS": dict(P117.SOURCES["WOGS"], notes="Reused. Senatorial districts list each LGA with its headquarters; Yewa North and Yewa South named as such."),
    "FGOG": P117.SOURCES["FGOG"],
}
DIST = {"Central": ["abeokuta-north", "abeokuta-south", "ewekoro", "ifo", "obafemi-owode", "odeda"],
        "East": ["ijebu-east", "ijebu-north", "ijebu-north-east", "ijebu-ode", "ikenne", "odogbolu", "ogun-waterside", "remo-north", "shagamu"],
        "West": ["ado-odo-ota", "imeko-afon", "ipokia", "egbado-north", "egbado-south"]}
DISTOF = {l: d for d, ls in DIST.items() for l in ls}
# slug: (display name, hq, statoids pop, area, agreement, inec office town, extra, [extra source keys])
LGAS = {
 "abeokuta-north": ("Abeokuta North", "Akomoje", 201329, 808, "SWI", "Akomoje", "", []),
 "abeokuta-south": ("Abeokuta South", "Ake", 250278, 71, "SW", "Abeokuta", "Ake is the traditional residence of the Alake of Egbaland (Wikipedia).", []),
 "ado-odo-ota": ("Ado-Odo/Ota", "Ota", 526565, 878, "SWI", "Ota", "It had the largest population of the state's LGAs at the 2006 census (Statoids). Ota is the seat of the Olota of Ota (Wikipedia).", []),
 "egbado-north": ("Yewa North, formerly Egbado North,", "Ayetoro", 181826, 2087, "SWI", "Ayetoro", "Statoids writes Aiyetoro.", []),
 "egbado-south": ("Yewa South, formerly Egbado South,", "Ilaro", 168850, 629, "SWI", "Ilaro", "Ilaro is the seat of the Olu of Ilaro, Paramount Ruler of Yewaland (Wikipedia).", []),
 "ewekoro": ("Ewekoro", "Itori", 55156, 594, "SWI", "Itori", "It had the smallest population of the state's LGAs at the 2006 census (Statoids).", []),
 "ifo": ("Ifo", "Ifo", 524837, 521, "SWI", "Ifo", "", []),
 "ijebu-east": ("Ijebu East", "Ogbere", 110196, 2234, "SWI", "Ogbere", "It had the largest area of the state's LGAs (Statoids).", []),
 "ijebu-north": ("Ijebu North", "Ijebu Igbo", 284336, 967, "SWI", "Ijebu Igbo", "", []),
 "ijebu-north-east": ("Ijebu North East", "Atan", 67634, 118, "SW", "an address at the LGA secretariat with no town named", "Wikipedia writes Attan.", []),
 "ijebu-ode": ("Ijebu Ode", "Ijebu Ode", 154032, 192, "SWI", "Ijebu Ode", "Ijebu-Ode is the capital of the Ijebu Kingdom, whose ruler is the Awujale, and the home of the Ojude Oba festival (Wikipedia).", []),
 "ikenne": ("Ikenne", "Ikenne", 118735, 144, "SWI", "Ikenne", "", []),
 "imeko-afon": ("Imeko Afon", "Imeko", 82217, 1655, "SWI", "Imeko", "", []),
 "ipokia": ("Ipokia", "Ipokia", 150426, 629, "SWI", "Ipokia", "", []),
 "obafemi-owode": ("Obafemi Owode", "Owode", 228851, 1410, "SW", "Odeda", "Wikipedia writes Owode Egba.", []),
 "odeda": ("Odeda", "Odeda", 109449, 1560, "SWI", "Odeda", "", []),
 "odogbolu": ("Odogbolu", "Odogbolu", 127123, 541, "SWI", "Odogbolu", "", []),
 "ogun-waterside": ("Ogun Waterside", "Abigi", 72935, 1000, "SWI", "Abigi", "Wikipedia places Araromi beach, on the state's short Atlantic coastline, in Ogun Waterside, and notes that Ondo State also claims it.", []),
 "remo-north": ("Remo North", "Isara", 59911, 199, "SI", "Isara", "Wikipedia gives Ilisan Remo as the headquarters instead.", []),
 "shagamu": ("Shagamu", "Shagamu", 253412, 614, "SWI", "Sagamu", "Shagamu (Sagamu) is the capital of the Remo kingdom, whose ruler is the Akarigbo (Wikipedia).", []),
}
NAMES_OF = {"S": "Statoids", "W": "Wikipedia", "I": "INEC"}
DNAME = {"Central": "Ogun Central", "East": "Ogun East", "West": "Ogun West"}
PEOPLES = {}
for r in P117.RELATIONS:
    if "lga:ogun" in r["to"] and r["type"] == "present_in":
        l = r["to"].split("/")[-1]
        if r["frm"].endswith(":ogu"):
            PEOPLES.setdefault(l, []).append("the Ogu (Egun)")
        else:
            subs = P117.SUBS[l][0]
            PEOPLES.setdefault(l, []).append(f"the Yoruba ({subs})")


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SWI" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, hq, pop, area, agree, office, extra, _k = LGAS[slug]
    t = f"{name} is a local government area of Ogun State, in the {DNAME[DISTOF[slug]]} senatorial district, with its headquarters at {hq} (according to {by(agree)})."
    if "I" not in agree:
        t += f" INEC's LGA office is at {office}."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    if extra: t += " " + extra
    ps = PEOPLES.get(slug, [])
    if ps:
        t += f" The peoples recorded for the LGA in this archive include {' and '.join(ps)}."
    return t


RECORDS, UPDATES, STATS, NAMES = [], [], [], []
for slug, (name, hq, pop, area, agree, office, extra, keys) in LGAS.items():
    plain = name.split(",")[0]
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{plain}: headquarters, 2006 population, area"), ("WOGS", f"{plain}: headquarters and senatorial district"), ("INECO", f"{plain}: INEC LGA office")]
    srcs += [(k, f"{plain}: {SOURCES[k]['title']}") for k in keys]
    key = f"hq_{slug}"
    hsrc = [("STAT", f"Headquarters of {plain}")] + ([("WOGS", f"Headquarters of {plain}")] if "W" in agree else []) + ([("INECO", f"INEC LGA office at {office}")] if "I" in agree else [])
    RECORDS.append(dict(key=key, table="places", evidence="multiple_sources", level="well_documented",
                        fields=dict(place_type="town", name=hq, slug=slugify(hq) + "-ogun", admin_unit_id=f"@admin_units:lga:ogun/{slug}", status="existing",
                                    summary=f"{hq} is the headquarters of {plain} Local Government Area, Ogun State."), srcs=hsrc))
    upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:ogun/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:ogun/{slug}", metric="population", value_low=pop, reference_year=2006, method="census", notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:ogun/{slug}", metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_egbado-north", name="Aiyetoro", name_type="spelling_variant", usage_notes="Statoids' spelling.", srcs=["STAT"]),
    dict(record="hq_shagamu", name="Sagamu", name_type="spelling_variant", usage_notes="Wikipedia's and INEC's spelling.", srcs=["WOGS"]),
    dict(record="hq_ijebu-north-east", name="Attan", name_type="spelling_variant", usage_notes="Wikipedia's spelling.", srcs=["WOGS"]),
    dict(record="hq_ado-odo-ota", name="Otta", name_type="spelling_variant", usage_notes="Wikipedia's spelling.", srcs=["WOGS"]),
]
GAPS = [("Ogun: Remo North and Obafemi Owode headquarters", "Remo North: Statoids and INEC give Isara, Wikipedia Ilisan Remo. Obafemi Owode: Statoids and Wikipedia give Owode, but INEC's office address is at Odeda. A state government source is needed."),
        ("Ogun: Yewa North and Yewa South", "The federal profile and Wikipedia name these LGAs Yewa North and Yewa South; Statoids and INEC's 2015 and 2024 lists still say Egbado North and Egbado South. The date of the official change is not given in a source read.")]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES, relations=[], statistics=STATS,
                scope="Ogun LGA profiles: headquarters, 2006 population and area, short sourced descriptions (20 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 121 — Ogun LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Ogun. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **20 LGA descriptions** and **{len(RECORDS)} headquarters towns**, one per LGA. Town slugs end in '-ogun'.",
         "- **Agreement on headquarters:**",
         "  - Statoids, Wikipedia and INEC agree for 16 LGAs.",
         "  - **Remo North:** Isara (Statoids and INEC). Wikipedia says Ilisan Remo; this is noted.",
         "  - **Obafemi Owode:** Owode (Statoids and Wikipedia). INEC's office is at Odeda; this is noted.",
         "  - **Abeokuta South (Ake)** and **Ijebu North East (Atan):** INEC's addresses name no matching town.",
         "- **Yewa North and South:** the descriptions say 'Yewa North, formerly Egbado North'. Renaming the LGA records themselves is proposed in the QC fix, for your approval. The old names will be kept.",
         "- **Figures:** 2006 population and area from Statoids.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## The 20 descriptions", ""] + [f"**{LGAS[s][0].split(',')[0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_121_ogun_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_121_ogun_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)}")
