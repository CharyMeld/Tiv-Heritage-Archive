"""
Research batch 048 — Plateau LGA profiles (headquarters, 2006 population and area, short sourced
descriptions for the 17 LGAs). Researched 2026-09-30. Pattern: Taraba batch 042.

Sources:
  * Statoids (reused): headquarters, 2006 census population, area.
  * INEC, "Plateau State" LGA office addresses (one-page list on inecnigeria.org, read via the Internet
    Archive). Tier 1. Where the address is at or next to the LGA council secretariat (Kanke 'opposite
    LGC Secretariat, Kwal'; Langtang South 'opposite LGC Secretariat ... Mabudi'; Riyom; Wase) it fixes
    the headquarters; otherwise it only shows that INEC's office is in the town.
  * Wikipedia article of each LGA (introduction).
  * Plateau State Government, "Plateau State is open for business" (investment booklet, July 2022),
    Tier 1: the official table of the 17 LGAs and their headquarters, and a table of 2006 population,
    land area and distance from Jos. Its areas differ from Statoids' and are recorded beside them; its
    population column has evident typing errors (Jos East 85,607, Jos South 309,716, Kanke 124,424,
    Riyom 131,575 against the census's 85,602, 306,716, 121,424, 131,557) and is not used. Its list of
    annual cultural festivals is kept for a later heritage batch.

Headquarters grading: two sources that explicitly name the headquarters = well documented; one = reported.
With the state government's table (G) every headquarters has at least two explicit sources.
  * STATOIDS SWAPS THE LANGTANGS: it gives Langtang North -> Mabudi and Langtang South -> Langtang.
    Wikipedia gives Langtang North -> Langtang and Langtang South -> Mabudi, and INEC places Langtang
    South's council secretariat at Mabudi (its Langtang North office is in Langtang town). The archive
    follows Wikipedia and INEC; the Statoids reading is noted.
  * Kanke: Statoids 'Kwali'; Wikipedia 'Kwal'; INEC 'opposite LGC Secretariat, Kwal' -> Kwal.
  * Qua'an Pan: Statoids and Wikipedia 'Baap'; INEC's office is at Doemak. Bassa: Statoids 'Bassa'; INEC's
    office is at Rukuba. Jos South: Statoids and Wikipedia 'Bukuru'; INEC's office is at Dadin Kowa.
Not used from Wikipedia: Jos North '729,300 at the 2006 census' (Statoids and the census give 429,300 —
a gap note); Kanke's '95% Angas' (unsourced); officials and council chairmen; postal codes; hotels.
Also: the Gbong Gwom Jos palace at Jishe is placed in Jos North LGA (Wikipedia, Jos North) — a new link.
"""
import json, re, sys

ACCESSED = "2026-09-30"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Plateau rows (NG.PL.*): 2006 census, area, headquarters. Langtang North/South headquarters appear swapped (Mabudi/Langtang)."),
    "INECO": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Plateau State: INEC state and LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)",
                  url="https://www.inecnigeria.org/wp-content/uploads/2024/04/PLATEAU-STATE.pdf", verification_status="verified",
                  archive_reference="Read via the Internet Archive (no longer on the INEC site).",
                  notes="State HQ — Miango Road, Jos; Barkin Ladi — Lawrence Onoja Road, Barkin Ladi; Bassa — adjacent First Bank, Rukuba; Bokkos — Daffo Road, Bokkos; Jos East — beside Divisional Police HQ, Angware; Jos North — Tudun Wada GRA, Jos; Jos South — Dadin Kowa, Jos South; Kanam — Dengi; Kanke — opposite LGC Secretariat, Kwal; Langtang North — Kukwar, Garkawa Road, Langtang; Langtang South — opposite LGC Secretariat, Zamko Road, Mabudi; Mikang — Tunkus; Mangu — near Mwansat Bridge, Jos Road; Pankshin — Pankshin; Qua'an Pan — opposite LGEA, Doemak; Riyom — beside Riyom LGC Secretariat; Shendam — Shendam; Wase — adjacent LGC Secretariat, Wase."),
    "OSS": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Plateau State is open for business (investment booklet)",
                organisation="Plateau State Government (One-Stop Shop)", publication_date="2022-07",
                url="https://plateaustate.gov.ng/uploads/Investing-in-Plateau-State-OSS-booklet.pdf", verification_status="verified",
                notes="Section 1.3: the 17 LGAs and headquarters (Bassa — Bassa; Barkin Ladi — Barikin Ladi; Jos East — Angware; Jos North — Jos; Jos South — Bukuru; Riyom — Riyom; Bokkos — Bokkos; Mangu — Mangu; Pankshin — Pankshin; Kanke — Kwal; Kanam — Dengi; Langtang North — Langtang; Langtang South — Mabudi; Wase — Wase; Mikang — Tunkus; Shendam — Shendam; Qua'an Pan — Ba'ap). Section 1.6: population (2006), land area, distance from Jos. Section 1.4: annual cultural festivals."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified",
                  notes="Reused (batch 044): languages placed in Plateau LGAs."),
}
# slug: (name, Wikipedia title, Statoids HQ, 2006 pop, area km2, HQ used, explicit HQ sources, text)
# explicit sources: S = Statoids, W = Wikipedia, I = INEC (council secretariat named)
LGAS = {
 "barkin-ladi": ("Barkin Ladi", "Barkin Ladi", "Barkin Ladi", 175267, 1032, "Barkin Ladi", "SG",
          "Wikipedia gives Barakin Ladi as another form of the name and names the Plateau State Polytechnic among its institutions. Riyom LGA was carved out of the old Barkin Ladi LGA in 1996. Blench's Atlas places the Berom, Aten, Fɨran, Mwaghavul and Pyam languages and the southern dialect of Izere here."),
 "bassa": ("Bassa", "Bassa, Plateau State", "Bassa", 186859, 1743, "Bassa", "SG",
          "Wikipedia describes it as the northern LGA of the state, bordering Kaduna and Bauchi states, with towns such as Miango and Jengre, and as the home of the Nigerian Army's 3 Division at the Maxwell Khobe Cantonment. INEC's office for the LGA is at Rukuba. Blench's Atlas places here many small Plateau and Kainji languages, among them Che (Rukuba), Rigwe (Irigwe), Iguta, Janji, Jere, Lemoro, Map, Cara and the highly endangered Zora."),
 "bokkos": ("Bokkos", "Bokkos", "Bokkos", 178454, 1682, "Bokkos", "SG",
          "Three stone causeways in the LGA, at Batura, Forof and Tading, are declared national monuments of Nigeria (Nos. 61–63), the only ones in Plateau State. Blench's Atlas places the Ron (Run) and Kulere languages here."),
 "jos-east": ("Jos East", "Jos East", "Angware", 85602, 1020, "Angware", "SWG",
          "INEC's office for the LGA is also at Angware. Wikipedia names the Afizere as its predominant people, and Blench's Atlas places five villages of the Tunzu language here."),
 "jos-north": ("Jos North", "Jos North", "Jos", 429300, 291, "Jos", "SWG",
          "By Statoids' figures it is the smallest LGA of the state by area, and at the 2006 census it was the most populous. Wikipedia describes it as the commercial centre of the state and places in it the Gbong Gwom Jos palace at Jishe, the University of Jos and its teaching hospital, and the headquarters of the ECWA and COCIN churches; it names the Anaguta among its peoples."),
 "jos-south": ("Jos South", "Jos South", "Bukuru", 306716, 510, "Bukuru", "SWG",
          "INEC's office for the LGA is at Dadin Kowa. Wikipedia places the new Government House at Rayfield in the LGA and names among its institutions the National Institute for Policy and Strategic Studies at Kuru, Karl Kumm University, the Theological College of Northern Nigeria and the College of Health at Vom."),
 "kanam": ("Kanam", "Kanam, Nigeria", "Dengi", 165898, 2600, "Dengi", "SWG",
          "INEC's office for the LGA is also at Dengi. Wikipedia names Boghom and Jarawa as languages of the LGA, and Blench's Atlas places here Boghom, Ngas and the Jar-cluster languages Mbat and Doori."),
 "kanke": ("Kanke", "Kanke, Nigeria", "Kwali", 121424, 926, "Kwal", "WIG",
          "Statoids spells the headquarters Kwali. Wikipedia describes the population as mostly Ngas and records that the former head of state Yakubu Gowon is from the LGA. The Ngolong Ngas is described as the traditional ruler of the Ngas of Pankshin and Kanke LGAs."),
 "langtang-north": ("Langtang North", "Langtang North", "Mabudi", 140643, 1188, "Langtang", "WG",
          "INEC's office for the LGA is in Langtang town. Statoids gives Mabudi, which the state government, Wikipedia and INEC place in Langtang South, so Statoids appears to swap the two Langtangs. The palace of the Ponzhi Tarok is in the LGA, and the Tarok festival Ilum Otarok was held at the Langtang North Mini Stadium in 2025."),
 "langtang-south": ("Langtang South", "Langtang South", "Langtang", 106305, 838, "Mabudi", "WIG",
          "Statoids gives Langtang as the headquarters, apparently swapping the two Langtangs. Wikipedia records a Chief Magistrate Court at Mabudi and area courts at Sabon Gida, Dadin Kowa and Magama."),
 "mangu": ("Mangu", "Mangu, Nigeria", "Mangu", 294931, 1653, "Mangu", "SG",
          "It is the paramount seat of the Mwaghavul, whose ruler, the Mishkaham Mwaghavul, has chaired the Mangu Traditional Council. Blench's Atlas places here more languages than in any other Plateau LGA, among them Mwaghavul, Fyer, Tambas, Sha, Shagawu, Mundat, Cakfem–Mushere, Miship, Bo-Rukul, Horom and members of the Pan and Vaghat clusters."),
 "mikang": ("Mikang", "Mikang", "Tunkus", 97411, 739, "Tunkus", "SWG",
          "INEC's office for the LGA is also at Tunkus. Wikipedia's Ethnologue table lists Montol, Tarok and Youm there."),
 "pankshin": ("Pankshin", "Pankshin", "Pankshin", 191685, 1524, "Pankshin", "SG",
          "Wikipedia describes Pankshin as a farming and trading centre with a weekly Monday market, and mentions Pusdung as the annual cultural festival of the Ngas. Blench's Atlas places the Ngas, Tal and Pe languages here."),
 "qua-an-pan": ("Qua'an Pan", "Qua'an Pan", "Baap", 196929, 2478, "Baap", "SWG",
          "INEC's office for the LGA is at Doemak. The LGA is the seat of the Long Pan, ruler of the Pan Chiefdom, a first-class stool; Blench's Atlas places here the Tèŋ and Shindai members of the Pan (Kofyar) cluster."),
 "riyom": ("Riyom", "Riyom", "Riyom", 131557, 807, "Riyom", "SIG",
          "According to Wikipedia, Riyom was carved out of the old Barkin Ladi LGA on 1 October 1996, is predominantly Berom, borders Kaduna and Nasarawa states, and is known for the Riyom rock formation, an emblem of the state."),
 "shendam": ("Shendam", "Shendam", "Shendam", 208017, 2477, "Shendam", "SG",
          "Wikipedia describes the LGA as bordering Ibi (Taraba State) to the south, Qua'an Pan to the east, Pankshin to the north and Mikang to the west, with Goemai and other West Chadic languages spoken. Shendam is the seat of the Long Goemai, a first-class stool."),
 "wase": ("Wase", "Wase, Nigeria", "Wase", 161714, 5031, "Wase", "SIG",
          "It is the largest LGA of the state by area. Wikipedia places the town about 200 km south-east of Jos, bordering Kanam to the north, Taraba State to the south and east and Langtang North to the west. Wase is the seat of the Wase Emirate, founded between 1817 and 1820."),
}
# Plateau State Government (2022): land area (km²; blank for Jos South and Riyom) and distance from Jos.
OSS_AREA = {"barkin-ladi": 1312.5, "bassa": 1776, "bokkos": 3053, "jos-east": 2540, "jos-north": 650, "kanam": 2788.4, "kanke": 1000, "langtang-north": 2440,
            "langtang-south": 1250, "mangu": 1587.5, "mikang": 630, "pankshin": 1334, "qua-an-pan": 2688, "shendam": 2437, "wase": 4306}
OSS_DIST = {"barkin-ladi": "54 km south", "bassa": "30 km north-west", "bokkos": "77 km south", "jos-east": "35 km east", "jos-south": "15 km south",
            "kanam": "191 km south-west", "kanke": "152 km south-east", "langtang-north": "194 km south-east", "langtang-south": "237 km south-east",
            "mangu": "77 km south-east", "mikang": "240 km south-east", "pankshin": "120 km south-east", "qua-an-pan": "298 km south-east",
            "riyom": "51 km south-west", "shendam": "254 km south-east", "wase": "216 km south-west"}
for slug, (name, wt, *_r) in LGAS.items():
    SOURCES[f"W_{slug}"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=wt, organisation="Wikipedia", url=W(wt),
                                verification_status="needs_corroboration", notes=f"Wikipedia article on {name} LGA (introduction), consulted {ACCESSED}.")


def fmt(n):
    return f"{n:,}"


NAMES_OF = {"G": "the state government", "S": "Statoids", "W": "Wikipedia", "I": "INEC"}


def by(agree):
    names = [NAMES_OF[c] for c in "GSWI" if c in agree]
    return names[0] if len(names) == 1 else ", ".join(names[:-1]) + " and " + names[-1]


def text(slug):
    name, wt, s_hq, pop, area, hq, agree, extra = LGAS[slug]
    first = f"{name} is a local government area of Plateau State with its headquarters at {hq} (according to {by(agree)})."
    if slug == "jos-north":
        first += " Jos is the state capital."
    elif slug in OSS_DIST:
        first += f" The state government places it about {OSS_DIST[slug]} of Jos."
    oa = OSS_AREA.get(slug)
    fig = f" It had a population of {fmt(pop)} at the 2006 census (Statoids). Statoids gives its area as about {fmt(area)} km²" + \
          (f", the state government as about {oa:,.1f}".replace(".0", "") + " km²." if oa else ".")
    return (first + fig + (" " + extra if extra else "")).strip()


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("OSS", f"{name}: headquarters, land area, distance from Jos"), ("STAT", f"{name}: headquarters, 2006 population, area"), (f"W_{slug}", f"{name}: Wikipedia introduction"), ("INECO", f"{name}: INEC LGA office address")]
    if any(k in extra for k in ("Blench", "Atlas")):
        srcs.append(("ATLAS", f"{name}: languages placed in the LGA"))
    if slug != "jos-north":
        key = f"hq_{slug}"
        hsrc = [("OSS", f"Headquarters of {name} (official table)")] + ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([(f"W_{slug}", f"Headquarters of {name}")] if "W" in agree else []) \
               + ([("INECO", f"LGA council secretariat at {hq}")] if "I" in agree else [])
        two = len(agree) >= 2
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if two else "single_reliable_source",
                            level="well_documented" if two else "reported",
                            fields=dict(place_type="town", name=hq, slug=re.sub(r"[^a-z0-9]+", "-", hq.lower()).strip("-"),
                                        admin_unit_id=f"@admin_units:lga:plateau/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Plateau State."),
                            srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    else:
        upd["headquarters_place_id"] = "@places:jos"
    UPDATES.append(dict(ref=f"@admin_units:lga:plateau/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:plateau/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:plateau/{slug}", metric="area_km2", value_low=area, method="other",
                      notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    if slug in OSS_AREA:
        STATS.append(dict(record=f"@admin_units:lga:plateau/{slug}", metric="area_km2", value_low=round(OSS_AREA[slug]), method="other",
                          notes=f"Land area as given by the Plateau State Government (2022): {OSS_AREA[slug]} km²", source="OSS", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_kanke", name="Kwali", name_type="spelling_variant", usage_notes="Form given by Statoids.", srcs=["STAT"]),
    dict(record="hq_qua-an-pan", name="Ba'ap", name_type="spelling_variant", usage_notes="Spelling used by the state government and Wikipedia ('Baap (or Ba'ap)').", srcs=["OSS", "W_qua-an-pan"]),
    dict(record="hq_barkin-ladi", name="Barakin Ladi", name_type="spelling_variant", usage_notes="Wikipedia.", srcs=["W_barkin-ladi"]),
    dict(record="hq_barkin-ladi", name="Barikin Ladi", name_type="spelling_variant", usage_notes="Spelling in the state government's list.", srcs=["OSS"]),
]
RELATIONS = [
    dict(frm="@polities:gbong-gwom-jos", type="located_in", to="@admin_units:lga:plateau/jos-north", source="W_jos-north", evidence="single_reliable_source", level="reported",
         notes="Wikipedia (Jos North): the Gbong Gwom Jos palace and office are at Jishe, in Jos North. Seat of the stool (not a statement of its jurisdiction)."),
]

GAPS = [
    ("Plateau: Langtang headquarters in Statoids", "Statoids gives Langtang North -> Mabudi and Langtang South -> Langtang; Wikipedia and INEC (LGA council secretariat at Mabudi) give the reverse, which the archive follows."),
    ("Plateau: Jos North population", "Wikipedia prints '729,300 at the 2006 census'; Statoids gives 429,300. The census publication should be checked; Statoids' figure is recorded."),
    ("Plateau: creation dates of LGAs", "Only Riyom's (1 October 1996, from Barkin Ladi) was found. An official list is needed, including when Kanke, Mikang, Qua'an Pan and the Jos and Langtang divisions were created."),
    ("Plateau: LGA areas", "Statoids and the state government's 2022 booklet give different areas for most LGAs (e.g. Bokkos 1,682 and 3,053 km²; Jos East 1,020 and 2,540 km²); both are recorded. The booklet gives no area for Jos South or Riyom."),
    ("Plateau: INEC offices outside the headquarters", "INEC's office for Bassa is at Rukuba, for Qua'an Pan at Doemak and for Jos South at Dadin Kowa; the headquarters are Bassa, Ba'ap and Bukuru (state government, Statoids, Wikipedia).")
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATS,
                scope="Plateau LGA profiles: headquarters, 2006 population and area, short sourced descriptions (17 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    places = [r for r in RECORDS if r["table"] == "places"]
    winf = {"jos-east": "Angware", "jos-north": "Jos", "jos-south": "Bukuru", "kanam": "Dengi", "kanke": "Kwal", "langtang-north": "Langtang",
            "langtang-south": "Mabudi", "mikang": "Tunkus", "qua-an-pan": "Baap"}
    inec = {"barkin-ladi": "Barkin Ladi", "bassa": "Rukuba", "bokkos": "Bokkos", "jos-east": "Angware", "jos-north": "Jos", "jos-south": "Dadin Kowa",
            "kanam": "Dengi", "kanke": "Kwal (LGC Secretariat)", "langtang-north": "Langtang", "langtang-south": "Mabudi (LGC Secretariat)", "mangu": "Jos Road (town not named)",
            "mikang": "Tunkus", "pankshin": "Pankshin", "qua-an-pan": "Doemak", "riyom": "Riyom (LGC Secretariat)", "shendam": "Shendam", "wase": "Wase (LGC Secretariat)"}
    L = ["# Research batch 048 — Plateau LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Plateau. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **17 LGA descriptions**, filling fields that were empty.",
         f"- **{len(places)} headquarters towns**, created as places. Jos North reuses the existing Jos place.",
         "- **Figures:** the 2006 population and the area of each of the 17 LGAs.",
         "- **Two new official sources:** the state government's own table of LGAs and headquarters (2022 investment booklet), and INEC's list of its LGA offices. Every headquarters now has at least two sources, and all 17 are well documented.",
         "- **Statoids swaps the two Langtangs.** Wikipedia and INEC give Langtang North → Langtang and Langtang South → Mabudi, and the archive follows them.",
         "- **Kanke → Kwal:** Wikipedia and INEC agree. Statoids spells it 'Kwali'.",
         "- **New link:** the Gbong Gwom Jos palace (Jishe) is in Jos North LGA, per Wikipedia.",
         "- **Wikipedia figures not copied:** Jos North's '729,300' (Statoids gives 429,300; noted as a gap) and Kanke's '95% Angas'.",
         "- The LGA pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Statoids | Wikipedia | INEC office | Level |", "|---|---|---|---|---|---|"]
    for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
        lvl = "well documented" if len(agree) >= 2 else "reported"
        L.append(f"| {name} | {hq} | {s_hq} | {winf.get(slug, 'not stated')} | {inec[slug]} | {lvl} |")
    L += ["", "## The 17 descriptions", ""]
    for slug in LGAS:
        L += [f"**{LGAS[slug][0]}** ({words(text(slug))} words). {text(slug)}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_048_plateau_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_048_plateau_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len([r for r in RECORDS if r['table'] == 'places'])} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)} names={len(NAMES)}",
          sorted((words(text(s)), s) for s in LGAS)[-3:])
