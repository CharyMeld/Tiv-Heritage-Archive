"""
Research batch 079 — Bauchi LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
20 LGAs). Researched 2026-10-02. Pattern: batches 061, 067 and 073.

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.BA.*).
  I  INEC, 'Bauchi State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/BAUCHI-STATE.pdf via the
     Internet Archive 20260616184412; copy in data/inec_bauchi_lga_offices.pdf). Counted only where the office is by
     the LGA secretariat in a named town (Bogoro, Gamawa, Toro, Zaki — 'adjacent Zaki LGA secretariat, Katagum').
     Other offices name the same towns as Statoids and are cited in the texts, except Shira (office at Shira town;
     HQ Yana) and Tafawa Balewa (office at Bununu).
  W  Wikipedia's LGA article, where it names the headquarters explicitly.
Grading: two agreeing sources = well documented; one = reported.
Tafawa Balewa: Wikipedia — headquarters moved from Tafawa Balewa town to Bununu in 2011 because of unrest; INEC's
office is at Bununu (not by the secretariat); Statoids still gives Tafawa-Balewa. Bununu recorded, 'reported'.
Reused place: Bauchi (#41, 'bauchi'). The slug 'azare' is taken by Azare in Hawul (Borno) → 'azare-bauchi'.
Figures: Statoids. Conflicts noted, not used: Jama'are — Wikipedia 176,883 and 341.6 km² (Statoids 117,883, 493);
Toro area — Wikipedia 6,705 km² (Statoids 6,932); Bogoro area — Wikipedia 834.5 km² (Statoids 894); Ganjuwa —
Wikipedia 280,486 (Statoids 280,468); Zaki area — Wikipedia 1,436 km² (= Katagum's; Statoids 1,476).
Peoples: from the archive's published people–LGA links (075b). Seven LGAs have none yet.
"""
import json, re, sys
import batch_077_bauchi_institutions as I77
import batch_078_bauchi_heritage as H78

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Bauchi rows (NG.BA.*): 2006 census, area, headquarters."),
    "INECB": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Bauchi State: INEC state and LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/BAUCHI-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (16 June 2026); copy in database/research/data/inec_bauchi_lga_offices.pdf.",
                  notes="State office High Court Close, off Ahmadu Bello Way, Bauchi. LGA offices: Alkaleri; Bauchi (Kofar Ran); Bogoro — adjacent LGA secretariat; Dambam; Darazo; Dass; Gamawa — opposite the LGA secretariat; Ganjuwa — Kafin Madaki; Giade; Itas Gadau — Itas; Jama'are; Katagum — Azare; Kirfi; Misau; Ningi; Shira — Giade–Misau road, Shira; Tafawa Balewa — beside DSS office, Bununu; Toro — LGA secretariat road; Warji; Zaki — old secretariat, adjacent Zaki LGA secretariat, Katagum. (The sheet's heading says 'Abia State' in error.)"),
    "WBS": I77.SOURCES["WBS"], "PT25": I77.SOURCES["PT25"], "WKAT": I77.SOURCES["WKAT"], "WJAM": I77.SOURCES["WJAM"], "WDAS": I77.SOURCES["WDAS"],
    "NCMML": H78.SOURCES["NCMML"], "NCMMP": H78.SOURCES["NCMMP"], "NCMMM": H78.SOURCES["NCMMM"], "WSUM": H78.SOURCES["WSUM"], "WGAN": H78.SOURCES["WGAN"],
    "WATB": H78.SOURCES["WATB"], "ICIR22": H78.SOURCES["ICIR22"], "WNCMM": H78.SOURCES["WNCMM"],
}
PEO = "The peoples recorded for the LGA in this archive include the {p}."
NOPEO = "No people is recorded for the LGA in this archive yet."
NEW = "created in 2025 (Premium Times)"
# slug: (name, Wikipedia title, Statoids HQ, 2006 pop, area km2, HQ used, agreeing HQ sources, text, extra source keys)
LGAS = {
 "alkaleri": ("Alkaleri", "Alkaleri", "Alkaleri", 329424, 5918, "Alkaleri", "SW",
              "Wikipedia gives its districts as Pali, Duguri and Gwana, with the headquarters in Pali district, and names the Fulani as the main group, with Kanuri, Dugurawa, Guruntawa and Labur (Jaku) people. "
              f"The Duguri Emirate, {NEW}, has its seat at Yuli in the LGA. " + PEO.format(p="Bolewa, Duguri and Jaku"), ["WBS", "PT25"]),
 "bauchi": ("Bauchi", "Bauchi (city)", "Bauchi", 493810, 3687, "Bauchi", "SW",
            "Bauchi is the capital of Bauchi State and the seat of the Bauchi Emirate; Wikipedia says Yakubu founded the town in 1809 and built walls about 10.5 km round. "
            "The National Museum Bauchi and the Mausoleum of Abubakar Tafawa Balewa, a proposed national monument, are in the city, and Wikipedia's copy of the monuments list places the Shadawanka rock paintings at Bauchi. "
            + PEO.format(p="Bankal, Duguri, Gera, Jaku, Mbat, Pa'a and Zul"), ["NCMMM", "NCMMP", "WNCMM"]),
 "bogoro": ("Bogoro", "Bogoro", "Bogoro", 84215, 894, "Bogoro", "SWI",
            "Wikipedia says the Zaar, also called Sayawa, are the majority of its people, and gives an area of 834.5 km². " + PEO.format(p="Zaar"), []),
 "damban": ("Dambam", "Damban", "Damban", 150922, 1077, "Dambam", "SW",
            "Statoids and the archive spell the LGA Damban; Wikipedia, INEC and the 2025 emirate list write Dambam. Wikipedia gives it two districts, Dagauda and Jalam, and says it borders Darazo, Misau, Katagum and Gamawa. "
            f"Dambam is the seat of the Dambam Emirate, {NEW}. " + PEO.format(p="Karekare"), ["PT25"]),
 "darazo": ("Darazo", "Darazo", "Darazo", 251597, 3015, "Darazo", "SW",
            "Wikipedia names the Fulani, Hausa and Karai-karai as its main peoples and the Zumbun language at Jimbim; Ganjuwa LGA was carved out of Darazo in September 1991 (Wikipedia). "
            f"Darazo is the seat of the Darazo Emirate, {NEW}. " + PEO.format(p="Bolewa and Ngamo"), ["PT25", "WGAN"]),
 "dass": ("Dass", "Dass, Nigeria", "Dass", 89943, 535, "Dass", "SW",
          "Wikipedia records settlement around Mbula hill before the jihad and the arrival of the Jarawa there in the early 19th century; the third-class chiefdom the colonial government created in 1913 is now the first-class Dass Emirate. "
          + PEO.format(p="Bankal, Gwak, Zaar and Zul"), ["WDAS"]),
 "gamawa": ("Gamawa", "Gamawa", "Gamawa", 286388, 2925, "Gamawa", "SWI",
            "It borders Yobe State; Wikipedia names the Fulani and, in the east, the Karai-Karai as its main peoples. "
            f"Gamawa is the seat of the Gamawa Emirate, {NEW}. " + PEO.format(p="Karekare"), ["PT25"]),
 "ganjuwa": ("Ganjuwa", "Ganjuwa", "Kafin-Madaki", 280468, 5059, "Kafin Madaki", "SW",
             "INEC's office is also at Kafin Madaki. Wikipedia says the LGA was carved out of Darazo in September 1991 and that the Madaki of Bauchi, a kingmaker of the Bauchi Emirate, is its District Head; it gives a 2006 population of 280,486. "
             "Gidan Madaki, a declared national monument, and Sumu Wildlife Park are in the LGA. " + PEO.format(p="Gera"), ["NCMML", "WSUM"]),
 "giade": ("Giade", "Giade", "Giade", 156969, 668, "Giade", "S",
           "INEC's office is also at Giade. Wikipedia says it became a local government in 1996 and that Hausa and Fulfulde are the most spoken languages. "
           f"Giade is the seat of the Giade Emirate, {NEW}. " + NOPEO, ["PT25"]),
 "itas-gadau": ("Itas/Gadau", "Itas/Gadau", "Itas", 229996, 1398, "Itas", "SW",
                "INEC's office is also at Itas. The main campus of Bauchi State University is at Gadau, in the east of the LGA (Wikipedia). "
                f"Wikipedia's table places Gadar Maiwa, seat of the Ari Emirate {NEW}, in this LGA; its LGA is given differently elsewhere. " + NOPEO, ["PT25", "WBS"]),
 "jama-are": ("Jama'are", "Jama'are", "Jama'are", 117883, 493, "Jama'are", "S",
              "INEC's office is also at Jama'are. It is the seat of the Jama'are Emirate, founded in 1811 (Wikipedia), and had the smallest area of the state's LGAs in Statoids' figures; Wikipedia gives 341.6 km² and a 2006 population of 176,883. "
              "Wikipedia names the Fulani as its most prominent people and notes the Federal College of Education Jama'are. " + NOPEO, ["WJAM"]),
 "katagum": ("Katagum", "Katagum", "Azare", 295970, 1436, "Azare", "SW",
             "INEC's office is also at Azare, which has been the seat of the Katagum Emirate since 1916; the town of Katagum itself is in Zaki LGA, not in Katagum LGA (Wikipedia). " + NOPEO, ["WKAT"]),
 "kirfi": ("Kirfi", "Kirfi", "Kirfi", 147618, 2371, "Kirfi", "SW",
           "Wikipedia calls the town Kirfin Kasa, names the Hausa as the main group, and notes the Kirfanci and Bure languages. "
           "The Kirfin Sama hill site ruins, a proposed national monument, bear the name of Kirfi, though no source read places them in the LGA. " + NOPEO, ["NCMMP"]),
 "misau": ("Misau", "Misau", "Misau", 263487, 1226, "Misau", "SW",
           "INEC's office is also at Misau. It is the seat of the Misau Emirate (Wikipedia), and Wikipedia says its people are mostly Fulani and Kanuri. "
           + PEO.format(p="Fulani, Hausa, Kanuri, Karekare and Shuwa Arabs"), ["WBS"]),
 "ningi": ("Ningi", "Ningi, Nigeria", "Ningi", 387192, 4625, "Ningi", "SW",
           "Ningi is the seat of the Ningi Emirate; Wikipedia gives the LGA three districts, Ningi, Ari and Burra, and says the Gamo-Ningi language lost its last speaker in the 1980s. "
           f"Burra is the seat of the Burra Emirate, {NEW}. In 2022 the state revoked land allocations made by the LGA chairman in the Lame-Burra forest reserve (ICIR). "
           + PEO.format(p="Butawa, Pa'a and Warji"), ["PT25", "ICIR22"]),
 "shira": ("Shira", "Shira, Nigeria", "Yana", 234014, 1321, "Yana", "SW",
           "INEC's office, however, is at Shira town, on the Giade–Misau road. The Shira rock paintings, a declared national monument, are at Shira. " + NOPEO, ["NCMML"]),
 "tafawa-balewa": ("Tafawa Balewa", "Tafawa Balewa, Bauchi State", "Tafawa-Balewa", 219988, 2515, "Bununu", "W",
                   "Wikipedia says the headquarters moved from Tafawa Balewa town to Bununu in 2011 because of repeated unrest, and INEC's office is at Bununu; Statoids still gives Tafawa Balewa. "
                   "Abubakar Tafawa Balewa, Nigeria's first prime minister, was born at Tafawa Balewa town (Wikipedia). "
                   f"Bununu and Lere are seats of emirates {NEW}, and the Zaar Chiefdom has its seat at Mhrim in the LGA. " + PEO.format(p="Bankal, Gwak, Ngas and Zaar"), ["WATB", "PT25"]),
 "toro": ("Toro", "Toro, Nigeria", "Toro", 350404, 6932, "Toro", "SI",
          "It had the largest area of the state's LGAs in Statoids' figures; Wikipedia gives 6,705 km². Wikipedia lists three districts, Toro, Jama'a and Lame, which are also the names of three emirates "
          f"{NEW}, all seated in the LGA. The Dutsen Zane Geji rock paintings and the Kwandonkaya cairn, both declared national monuments, are in Toro. "
          + PEO.format(p="Bankal, Ngas, Zaar and Zul"), ["PT25", "NCMML"]),
 "warji": ("Warji", "Warji", "Warji", 114720, 625, "Warji", "SW",
           f"INEC's office is also at Warji. The Warji Emirate, {NEW}, has its seat at Katangar Warji; Wikipedia's article on Ningi counts Warji LGA as part of the Ningi Emirate. "
           + NOPEO, ["PT25", "WBS"]),
 "zaki": ("Zaki", "Zaki, Nigeria", "Katagum", 191457, 1476, "Katagum", "SWI",
          "Katagum town gives its name to the Katagum Emirate, whose seat moved to Azare in 1916 (Wikipedia). Wikipedia names the Fulani as the main group, with Hausa, Kanuri and Karai-Karai in the east, and notes the Bade language. "
          + PEO.format(p="Bade"), ["WKAT"]),
}
REUSE_PLACE = {"bauchi": "@places:bauchi"}
HQ_SLUG = {"katagum": "azare-bauchi"}
for slug, (name, wt, *_r) in LGAS.items():
    SOURCES[f"W_{slug}"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=wt, organisation="Wikipedia", url=W(wt),
                                verification_status="needs_corroboration", notes=f"Wikipedia article on {name}, consulted {ACCESSED}.")
NAMES_OF = {"S": "Statoids", "I": "INEC", "W": "Wikipedia"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SIW" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, wt, s_hq, pop, area, hq, agree, extra, _k = LGAS[slug]
    t = f"{name} is a local government area of Bauchi State with its headquarters at {hq} (according to {by(agree)})."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    return (t + (" " + extra if extra else "")).strip()


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra, keys) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("INECB", f"{name}: INEC LGA office address"), (f"W_{slug}", f"{name}: Wikipedia article")]
    srcs += [(k, f"{name}: {SOURCES[k]['title']}") for k in keys]
    if slug in REUSE_PLACE:
        upd["headquarters_place_id"] = REUSE_PLACE[slug]
    else:
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([("INECB", f"LGA office by the LGA secretariat at {hq}")] if "I" in agree else []) \
               + ([(f"W_{slug}", f"Headquarters of {name}")] if "W" in agree else [])
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if len(agree) >= 2 else "single_reliable_source", level="well_documented" if len(agree) >= 2 else "reported",
                            fields=dict(place_type="town", name=hq, slug=HQ_SLUG.get(slug, slugify(hq)), admin_unit_id=f"@admin_units:lga:bauchi/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Bauchi State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:bauchi/{slug}", fields=upd, srcs=srcs))
    pnote = {"jama-are": "; Wikipedia gives 176,883", "ganjuwa": "; Wikipedia gives 280,486"}.get(slug, "")
    anote = {"jama-are": "; Wikipedia gives 341.6 km²", "toro": "; Wikipedia gives 6,705 km²", "bogoro": "; Wikipedia gives 834.5 km²"}.get(slug, "")
    STATS.append(dict(record=f"@admin_units:lga:bauchi/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids" + pnote, source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:bauchi/{slug}", metric="area_km2", value_low=area, method="other",
                      notes="Area as given by Statoids" + anote, source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_ganjuwa", name="Kafin-Madaki", name_type="spelling_variant", usage_notes="Statoids.", srcs=["STAT"]),
    dict(record="hq_kirfi", name="Kirfin Kasa", name_type="alternative", usage_notes="Wikipedia: 'Kirfi (Kirfin Kasa)'.", srcs=["W_kirfi"]),
    dict(record="hq_damban", name="Damban", name_type="spelling_variant", usage_notes="Statoids' form (also the archive's LGA name).", srcs=["STAT"]),
]
RELATIONS = []
GAPS = [
    ("Bauchi: LGA headquarters", "Giade and Jama'are rest on Statoids alone (INEC offices in the same towns, not by the secretariat). Shira: Yana (Statoids, Wikipedia) but INEC's office is at Shira town. Tafawa Balewa: Bununu since 2011 (Wikipedia; INEC office there), Statoids still Tafawa Balewa. An official Bauchi State list would settle them."),
    ("Bauchi: figures", "Jama'are 2006: Statoids 117,883 / 493 km², Wikipedia 176,883 / 341.6 km²; Toro area 6,932 vs 6,705 km²; Bogoro 894 vs 834.5 km²; Ganjuwa 280,468 vs 280,486; Wikipedia's Zaki area copies Katagum's. Statoids recorded."),
    ("Bauchi: peoples per LGA", "No people is linked yet to Giade, Itas/Gadau, Jama'are, Katagum, Kirfi, Shira or Warji; the Warji people are linked to Ningi but not to Warji LGA. Needs a people–LGA source (075b)."),
    ("Bauchi: Damban/Dambam", "The archive's LGA is 'Damban' (Statoids); Wikipedia, INEC and the 2025 emirate law write Dambam. Not renamed."),
    ("Bauchi: LGA creation dates", "Only Ganjuwa (September 1991, from Darazo) and Giade (1996) are dated by Wikipedia."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATS,
                scope="Bauchi LGA profiles: headquarters, 2006 population and area, short sourced descriptions (20 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 079 — Bauchi LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Bauchi. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **20 LGA descriptions**, filling fields that were empty.",
         f"- **{len(RECORDS)} headquarters towns**, created as places. Bauchi reuses the existing place.",
         "- **Figures:** the 2006 population and area of each LGA, from Statoids.",
         "- **Sources for the headquarters:** Statoids, INEC's LGA offices that sit by the LGA secretariat, and Wikipedia. INEC's office list is saved in the repo.",
         "- **Tafawa Balewa → Bununu (*reported*):** Wikipedia says the headquarters moved there in 2011, and INEC's office is there. Statoids still gives Tafawa Balewa.",
         "- **Shira → Yana:** Statoids and Wikipedia agree; INEC's office is at Shira town, which the text says.",
         "- **Conflicting figures** (Jama'are, Toro, Bogoro, Ganjuwa) are noted in the text or statistics notes; Statoids' figures are used.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Statoids | Sources | Level |", "|---|---|---|---|---|"]
    for slug, (name, wt, s_hq, pop, area, hq, agree, extra, _k) in LGAS.items():
        lvl = "reported" if len(agree) < 2 else "well documented"
        L.append(f"| {name} | {hq} | {s_hq} | {by(agree)} | {lvl}{' (reused place)' if slug in REUSE_PLACE else ''} |")
    L += ["", "## The 20 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_079_bauchi_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_079_bauchi_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)} names={len(NAMES)}")
