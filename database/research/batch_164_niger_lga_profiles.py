"""
Research batch 164 — Niger LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
25 LGAs). Researched 2026-10-07. Pattern: batches 146, 152, 158.

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.NI.*).
  W  Wikipedia's LGA pages ('Its headquarters are in the town of …', or 'X is a town and Local Government Area').
  I  INEC, 'Niger State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/NIGER-STATE.pdf via the
     Internet Archive; copy in data/inec_niger_lga_offices.pdf). Counted where the office is in the headquarters town.
Differences: Kontagora — Wikipedia's page is about the town and does not call it an LGA headquarters. Paikoro — INEC's
address ('Opp. LEA Paikoro LGA office') names no town. Gurara — INEC writes 'Gawu Babangida'; Tafa — INEC writes 'Sabon
Wuse'; Muya — INEC and Wikipedia call the LGA Munya (the archive keeps Muya, the Constitution's name, with Munya as a
variant). Chanchaga uses the existing Minna place (#62). Town slugs end in '-niger'.
"""
import json, re, sys

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Niger rows (NG.NI.*): 2006 census, area, headquarters."),
    "INECS": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Niger State: INEC LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/NIGER-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (7 October 2026); copy in database/research/data/inec_niger_lga_offices.pdf.",
                  notes="LGA office addresses for all 25 LGAs (Muya listed as Munya)."),
    "WLGA": WS("Niger State", "Headquarters taken from each Niger LGA's own Wikipedia page (Borgu: 'Borgu (local government area)'; Bosso, Gurara, Katcha, Mariga, Munya and Rafi: '<name>, Nigeria')."),
}
# slug: (name, hq, statoids pop, area, agreement, inec office, extra)
LGAS = {
 "agaie": ("Agaie", "Agaie", 132907, 1903, "SWI", "Agaie", "Wikipedia says it is inhabited by the Nupe and that Agaie is an old town, founded about the fourteenth century."),
 "agwara": ("Agwara", "Agwara", 57413, 1538, "SWI", "Agwara", "It had the smallest population of the state's LGAs at the 2006 census (Statoids)."),
 "bida": ("Bida", "Bida", 188181, 51, "SWI", "Bida", "Bida is the seat of the Etsu Nupe, ruler of the Bida Emirate, and the katamba of his palace is a declared national monument (Wikipedia; NCMM). Nupe Day is celebrated there each June. It had the smallest area of the state's LGAs (Statoids)."),
 "borgu": ("Borgu", "New Bussa", 171965, 11267, "SWI", "New Bussa", "It was transferred from Kwara State to Niger State on 27 August 1991 and has the same extent as the Borgu Emirate (Wikipedia). Part of Kainji National Park, the Borgu Game Reserve, lies in it. It had the largest area of the state's LGAs (Statoids)."),
 "bosso": ("Bosso", "Maikunkele", 147359, 1592, "SWI", "Maikunkele", "Wikipedia names Gbagyi, Hausa and Nupe as its most common languages."),
 "chanchaga": ("Chanchaga", "Minna", 201429, 72, "SWI", "Minna", "Minna, the state capital, occupies much of the LGA (Wikipedia), and the National Museum Minna is in the Federal Secretariat Complex there (NCMM)."),
 "edati": ("Edati", "Enagi", 160321, 1752, "SWI", "Enagi", "The Kaduna River divides it into two parts (Wikipedia)."),
 "gbako": ("Gbako", "Lemu", 127466, 1753, "SWI", "Lemu", "The Kaduna River forms its western boundary (Wikipedia)."),
 "gurara": ("Gurara", "Gawu", 90974, 954, "SWI", "Gawu Babangida", "It adjoins the Federal Capital Territory; Wikipedia names the Gwari (Gbagyi) and Bassa as its major inhabitants, and the Gurara Waterfalls are in the LGA."),
 "katcha": ("Katcha", "Katcha", 122176, 1681, "SWI", "Katcha", "It was carved out of the old Gbako LGA in 1981, and Badeggi is one of its major towns (Wikipedia)."),
 "kontagora": ("Kontagora", "Kontagora", 151944, 2081, "SI", "Kontagora", "Kontagora is the seat of the Kontagora Emirate, whose ruler is styled the Sarkin Sudan (Voice of Nigeria); Wikipedia says Umaru Nagwamatse made it his capital in 1864."),
 "lapai": ("Lapai", "Lapai", 110127, 3051, "SWI", "Lapai", "It adjoins the Federal Capital Territory and is roughly coterminous with the Lapai Emirate; Wikipedia names Nupe Day and Gwari Day among its festivals. The Dabo Mosque at Gulu is a proposed national monument (NCMM)."),
 "lavun": ("Lavun", "Kutigi", 209917, 2835, "SWI", "Kutigi", "The Kaduna River forms its eastern border (Wikipedia)."),
 "magama": ("Magama", "Nasko", 181653, 4107, "SWI", "Nasko", ""),
 "mariga": ("Mariga", "Bangi", 199430, 5552, "SWI", "Bangi", "Wikipedia names the Kamuku as its principal inhabitants and says several endangered Kamuku languages, Hausa and Kambari languages are spoken there."),
 "mashegu": ("Mashegu", "Mashegu", 215022, 9182, "SWI", "Mashegu", "It is bounded by the Niger in the west and the Kaduna River in the north-east (Wikipedia)."),
 "mokwa": ("Mokwa", "Mokwa", 244937, 4338, "SWI", "Mokwa", "Its long southern border is the Niger, from Lake Jebba to beyond the Kaduna confluence, and Mokwa is a market town where traders from the south buy food from northern growers (Wikipedia). It had the largest population of the state's LGAs at the 2006 census (Statoids)."),
 "muya": ("Muya", "Sarkin Pawa", 103651, 2176, "SWI", "Sarkin Pawa", "INEC and Wikipedia call it Munya; the archive keeps Muya, the name in the First Schedule to the 1999 Constitution and in Statoids, with Munya as a variant. Wikipedia names the Gbari as its main inhabitants."),
 "paikoro": ("Paikoro", "Paiko", 158086, 2066, "SW", "an address naming no town", "Paiko lies about 25 km south-east of Minna (Wikipedia)."),
 "rafi": ("Rafi", "Kagara", 181929, 3680, "SWI", "Kagara", "Tegina and Pandogari are among its towns, and the Kaduna River forms its southern border (Wikipedia)."),
 "rijau": ("Rijau", "Rijau", 176053, 3196, "SWI", "Rijau", ""),
 "shiroro": ("Shiroro", "Kuta", 235404, 5015, "SWI", "Kuta", "Wikipedia gives Gbagyi as its major language."),
 "suleja": ("Suleja", "Suleja", 216578, 119, "SWI", "Suleja", "Suleja, first named Abuja, was founded in the early 19th century by Hausa refugees from Zazzau and is the seat of the Suleja Emirate; the Ladi Kwali Pottery Centre was established there by Michael Cardew in 1950 (Wikipedia). Zuma Rock, at Madalla, is a proposed national monument (NCMM)."),
 "tafa": ("Tafa", "Wuse", 83544, 222, "SWI", "Sabon Wuse", "It adjoins the Federal Capital Territory (Wikipedia)."),
 "wushishi": ("Wushishi", "Wushishi", 81783, 1879, "SWI", "Wushishi", "Zungeru, the capital of the Protectorate of Northern Nigeria from 1902 to 1916, is in the LGA; its colonial Government House ruins and the site of Mai Jimina's house at Wushishi are declared national monuments (Wikipedia; NCMM)."),
}
PEOPLE = {  # peoples linked to the LGA in batch 160b
 "agaie": "Dibo, Gbagyi, Kakanda and Nupe", "agwara": "Kambari", "bida": "Gbagyi, Hausa and Nupe", "borgu": "Busa, Kambari and Reshe",
 "bosso": "Gbagyi", "chanchaga": "Gbagyi and Kamuku", "edati": "Nupe", "gbako": "Nupe", "gurara": "Gbagyi and Gwandara",
 "katcha": "Dibo and Nupe", "kontagora": "Hausa and Kambari", "lapai": "Dibo, Gbagyi, Kakanda and Nupe", "lavun": "Nupe",
 "magama": "Hun-Saare and Kambari", "mariga": "Kambari, Kamuku and Nupe", "mashegu": "Kambari and Nupe",
 "mokwa": "Gbagyi, Hausa, Nupe and Yoruba", "muya": "Adara", "paikoro": "Adara and Gbagyi", "rafi": "Gbagyi, Hùngwəryə, Kamuku and Pangu",
 "rijau": "Hun-Saare and Kambari", "shiroro": "Gbagyi", "suleja": "Gbagyi, Gwandara, Hausa and Koro", "tafa": "Gbagyi", "wushishi": "Gbagyi and Nupe",
}
NAMES_OF = {"S": "Statoids", "W": "Wikipedia", "I": "INEC"}
REUSE = {"chanchaga": "@places:minna"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SWI" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, hq, pop, area, agree, office, extra = LGAS[slug]
    t = f"{name} is a local government area of Niger State with its headquarters at {hq} (according to {by(agree)})."
    if "I" not in agree:
        t += f" INEC lists its LGA office at {office}."
    elif office != hq:
        t += f" INEC writes the town as {office}."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    if extra: t += " " + extra
    p = PEOPLE[slug]
    t += f" The archive links the {p} {'people' if ' and ' not in p else 'peoples'} to it (batch 160b: Blench's Atlas, the federal state profile and Wikipedia)."
    return t


RECORDS, UPDATES, STATS = [], [], []
for slug, (name, hq, pop, area, agree, office, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("WLGA", f"{name}: Wikipedia LGA page"), ("INECS", f"{name}: INEC LGA office")]
    ref = f"@admin_units:lga:niger/{slug}"
    if slug in REUSE:
        upd["headquarters_place_id"] = REUSE[slug]
    else:
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("WLGA", f"Headquarters of {name} (Wikipedia LGA page)")] if "W" in agree else []) + ([("INECS", f"INEC LGA office at {office}")] if "I" in agree else [])
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources", level="well_documented",
                            fields=dict(place_type="town", name=hq, slug=slugify(hq) + "-niger", admin_unit_id=ref, status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Niger State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=ref, fields=upd, srcs=srcs))
    STATS.append(dict(record=ref, metric="population", value_low=pop, reference_year=2006, method="census", notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=ref, metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES = [
    dict(record="hq_gurara", name="Gawu Babangida", name_type="alternative", usage_notes="INEC's name for the town.", srcs=["INECS"]),
    dict(record="hq_tafa", name="Sabon Wuse", name_type="alternative", usage_notes="INEC's name for the town.", srcs=["INECS"]),
    dict(record="hq_agwara", name="Agwarra", name_type="spelling_variant", usage_notes="Wikipedia gives 'Agwara (or Agwarra)'.", srcs=["WLGA"]),
]
GAPS = [
    ("Niger: headquarters of Kontagora and Paikoro", "Wikipedia's Kontagora page is about the town and does not call it the LGA headquarters; INEC's Paikoro address names no town. Statoids gives Kontagora and Paiko; a state government list would settle both."),
    ("Niger: LGA descriptions", "Several Wikipedia LGA pages (Magama, Rijau, Agwara) give little beyond census figures and climate; the history and peoples of those LGAs need better sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES, relations=[], statistics=STATS,
                scope="Niger LGA profiles: headquarters, 2006 population and area, short sourced descriptions (25 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    n3 = sum(1 for v in LGAS.values() if v[4] == "SWI")
    L = ["# Research batch 164 — Niger LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Niger. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **25 LGA descriptions** and **{len(RECORDS)} new headquarters towns**. Chanchaga uses the existing Minna place. New town slugs end in '-niger'.",
         "- **Agreement on headquarters:**",
         f"  - Statoids, Wikipedia's LGA pages and INEC agree for {n3} LGAs.",
         "  - **Kontagora:** Statoids and INEC agree; Wikipedia's page is about the town and does not call it the LGA headquarters.",
         "  - **Paikoro** (Paiko): Statoids and Wikipedia agree; INEC's address names no town.",
         "  - INEC writes **Gawu Babangida** (Gurara) and **Sabon Wuse** (Tafa); both are kept as other names of the towns.",
         "- **Muya / Munya:** the record keeps Muya (the Constitution and Statoids) and says that INEC and Wikipedia use Munya. Munya is already stored as a variant.",
         "- **Each description** names the peoples linked to the LGA in batch 160b, and any ruler's seat, monument or festival from batches 162–163.",
         "- **Figures:** 2006 population and area from Statoids.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## The 25 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_164_niger_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_164_niger_lga_profiles_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)}")
