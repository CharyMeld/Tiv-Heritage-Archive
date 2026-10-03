"""
Research batch 030 — Benue LGA profiles (Phase 3 completion after the Phase 4 QC finding that all
23 Benue LGAs had only a one-line summary and no headquarters). Researched 2026-09-26.

Sources: Statoids' LGA table (headquarters, 2006 census population, area; reused source) and the
Wikipedia article of each LGA (headquarters, creation, location, features). Wikipedia's LGA
stubs repeat Statoids' figures, so the two are treated as one lineage for the figures and graded
accordingly; for headquarters, agreement of the two is 'well documented', one alone 'reported'.

Handling:
  * Ukum: Statoids gives Zaki Biam, Wikipedia "Sankera" (also the name of the Tiv intermediate
    area) — both stated, no headquarters set, gap recorded.
  * Ohimini: "Idekpa-Okpiko" (Statoids) / "Idekpa-Okpikwu" (Wikipedia) — Wikipedia's form is used
    (it matches its list of villages, which includes Okpikwu), the other kept as a variant.
  * Statoids misspells two headquarters ("Markurdi", "Oturpko"); the usual spellings are used.
  * Not used from Wikipedia: political comment on the present Och'Idoma (Otukpo article), accounts
    of killings in Agatu (need separate, careful treatment), named current officials.
Also: the Upper Cross branch (Glottolog) so that Oring can be placed in its family (the change to
the existing Oring record is in fix_030_benue.php, with the resolution of gap #51).
"""
import json, re, sys

ACCESSED = "2026-09-26"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused."),
    "GLUC": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Upper Cross (uppe1418)", organisation="Glottolog",
                 url="https://glottolog.org/resource/languoid/id/uppe1418", verification_status="verified",
                 notes="Upper Cross: Atlantic-Congo > Volta-Congo > Benue-Congo > Delta Cross > Upper Cross. Oring (orin1239) is in Upper Cross > Central Upper Cross > North-South Central Delta Cross > Koring-Kukele."),
}
# lga slug: (name, Wikipedia title, Statoids HQ, 2006 pop, area km2, HQ used (None if unsettled), HQ source agreement, text)
LGAS = {
 "ado": ("Ado", "Ado, Benue State", "Igumale", 178882, 1220, "Igumale", "S",
         "Wikipedia states that Ado was created in 1989 and that it borders Cross River State to the south and Ebonyi State to the south-east. It is one of the nine LGAs of Benue South."),
 "agatu": ("Agatu", "Agatu", "Obagaji", 115523, 1015, "Obagaji", "SW",
           "Wikipedia states that Agatu was created in 1996 from the Agatu district of the old Otukpo division, and describes most of its people as farmers."),
 "apa": ("Apa", "Apa, Benue State", "Ugbokpo", 96765, 995, "Ugbokpo", "S",
         "According to Wikipedia, Apa was first created on 23 March 1981, abolished on 31 December 1983 and re-created in August 1991. It borders Agatu to the north, Gwer West to the east, Otukpo to the south and Omala LGA of Kogi State to the west."),
 "buruku": ("Buruku", "Buruku", "Buruku", 203721, 1246, "Buruku", "S", ""),
 "gboko": ("Gboko", "Gboko", "Gboko", 358936, 1835, "Gboko", "SW",
           "Wikipedia calls Gboko the most populous of Benue's LGAs at the 2006 census. It describes the town as the ancestral headquarters of the Tiv, with the Tor Tiv's palace at its heart, and as home to the former Benue Cement Company, now Dangote Cement. It names Tiv, Hausa and English as the most widely spoken languages."),
 "guma": ("Guma", "Guma, Nigeria", "Gbajimba", 191599, 2882, "Gbajimba", "SW", ""),
 "gwer-east": ("Gwer East", "Gwer East", "Aliade", 163647, 2294, "Aliade", "SW",
               "Wikipedia lists three districts in the LGA: Yonov, Njiriv and Ngyohov."),
 "gwer-west": ("Gwer West", "Gwer West", "Naka", 122145, 1094, "Naka", "SW",
               "Besides Naka, Wikipedia names Orawe, Bunaka, Agagbe, Nagi, Aondoana, Kula, Jimba, Anguhar, Atukpu and Ajigba among its settlements, and Ikyande as a well-known market."),
 "katsina-ala": ("Katsina-Ala", "Katsina-Ala", "Katsina-Ala", 224718, 2402, "Katsina-Ala", "SW",
                 "Wikipedia describes Katsina-Ala as the starting point of the A344 highway and as the site of archaeological finds of the Nok culture, and lists markets held on different days of the week, including Tomanyiin, Tor Donga, Gbor Tongov, Abaji and Amaafu."),
 "konshisha": ("Konshisha", "Konshisha", "Tse-Agberagba", 225672, 1673, "Tse-Agberagba", "SW",
               "Wikipedia describes the area as mainly Tiv, with Christianity the most common religion."),
 "kwande": ("Kwande", "Kwande", "Adikpo", 248697, 2891, "Adikpo", "SW",
            "Wikipedia places Vandeikya to its west, Ushongo to the north, Katsina-Ala to the north-west, Cross River State to the south, the Republic of Cameroon to the south-east and Takum LGA of Taraba State to the east. It describes the area as mountainous and cooler than the rest of the state, with fifteen council wards and sixteen traditional districts."),
 "logo": ("Logo", "Logo, Nigeria", "Ugba", 169063, 1408, "Ugba", "SW",
          "Wikipedia states that Logo was established in December 1996, when it was separated from Katsina-Ala, and that its name comes from the Logo stream, which crosses the area from east to west."),
 "makurdi": ("Makurdi", "Makurdi", "Markurdi [sic]", 297398, 820, "Makurdi", "SW",
             "Makurdi is the capital of Benue State. Wikipedia describes the town as divided by the River Benue into north and south banks, joined by two bridges: the railway bridge of 1932 and a dual-carriage bridge commissioned in 1978. Its neighbourhoods include Wadata, High Level, Wurukum, Old and New GRA, Ankpa Ward, Nyiman Layout and Achusa."),
 "obi": ("Obi", "Obi, Benue State", "Obarike Ito", 98855, 423, "Obarike-Ito", "S", ""),
 "ogbadibo": ("Ogbadibo", "Ogbadibo", "Otukpa Town", 128707, 598, "Otukpa", "SW",
              "Wikipedia names three districts: Orokam, Owukpa and Otukpa."),
 "ohimini": ("Ohimini", "Ohimini", "Idekpa-Okpiko", 71482, 632, "Idekpa-Okpikwu", "SW",
             "Wikipedia states that Ohimini was created out of Otukpo LGA, and that its main district, Onyagede, borders Kogi State."),
 "oju": ("Oju", "Oju, Benue State", "Oju", 170236, 1283, "Oju", "S",
         "Wikipedia places Obi and Gwer East to its north, Konshisha and Yala (Cross River State) to the east, Izzi (Ebonyi State) to the south and Ado to the west."),
 "okpokwu": ("Okpokwu", "Okpokwu", "Okpoga", 176647, 731, "Okpoga", "SW",
             "Benue State Polytechnic is at Ugbokolo in this LGA, according to Wikipedia."),
 "oturkpo": ("Oturkpo", "Otukpo", "Oturpko [sic]", 261666, 1269, "Otukpo", "SW",
             "Wikipedia describes Otukpo as the headquarters of the Idoma nation and the seat of the Och'Idoma. The LGA's name is written Oturkpo in the Constitution and Otukpo in most other sources."),
 "tarka": ("Tarka", "Tarka, Nigeria", "Wannune", 79494, 371, "Wannune", "SW", ""),
 "ukum": ("Ukum", "Ukum", "Zaki Biam", 216930, 1514, None, "conflict",
          "Its headquarters is given as Zaki Biam by Statoids and as Sankera by Wikipedia (Sankera is also the name of the Tiv intermediate area that includes Ukum); the question is left open. Wikipedia describes Zaki Biam as the largest town of the LGA and the home of the Zaki Biam yam market, which it calls the largest yam market in the world."),
 "ushongo": ("Ushongo", "Ushongo", "Lessel", 188341, 1228, "Lessel", "SW", ""),
 "vandeikya": ("Vandeikya", "Vandeikya", "Vandeikya", 230120, 931, "Vandeikya", "S",
               "Wikipedia states that Vandeikya LGA was carved out of Gboko in 1976, that its indigenous community is the Kunav, who speak Tiv, and that the area has deposits of barite, kaolin and iron ore. Most people are farmers, and small industries include rice milling."),
}
for slug, (name, wt, *_rest) in LGAS.items():
    SOURCES[f"W_{slug}"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=wt, organisation="Wikipedia", url=W(wt),
                                verification_status="needs_corroboration", notes=f"Wikipedia article on {name} LGA (introduction), consulted {ACCESSED}.")


def fmt(n):
    return f"{n:,}"


def text(slug):
    name, wt, s_hq, pop, area, hq, agree, extra = LGAS[slug]
    if agree == "conflict":
        first = f"{name} is a local government area of Benue State."
    else:
        by = "Statoids and Wikipedia" if agree == "SW" else "Statoids"
        first = f"{name} is a local government area of Benue State with its headquarters at {hq} (according to {by})."
    fig = f" Statoids gives it an area of about {fmt(area)} km² and a population of {fmt(pop)} at the 2006 census."
    return (first + fig + (" " + extra if extra else "")).strip()


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), (f"W_{slug}", f"{name}: Wikipedia introduction")]
    if hq and slug != "makurdi":
        key = f"hq_{slug}"
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if agree == "SW" else "single_reliable_source",
                            level="well_documented" if agree == "SW" else "reported",
                            fields=dict(place_type="town", name=hq, slug=re.sub(r"[^a-z0-9]+", "-", hq.lower()).strip("-"),
                                        admin_unit_id=f"@admin_units:lga:benue/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Benue State."),
                            srcs=[("STAT", f"Headquarters of {name}")] + ([(f"W_{slug}", f"Headquarters of {name}")] if agree == "SW" else [])))
        upd["headquarters_place_id"] = f"@key:{key}"
    elif slug == "makurdi":
        upd["headquarters_place_id"] = "@places:makurdi"
    UPDATES.append(dict(ref=f"@admin_units:lga:benue/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:benue/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:benue/{slug}", metric="area_km2", value_low=area, method="other",
                      notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES.append(dict(record="hq_ohimini", name="Idekpa-Okpiko", name_type="spelling_variant", usage_notes="Form given by Statoids.", srcs=["STAT"]))
RECORDS.append(dict(key="uppercross", table="languages", evidence="single_reliable_source", level="well_documented",
                    fields=dict(lang_type="branch", name="Upper Cross", slug="upper-cross", glottocode="uppe1418",
                                summary="Upper Cross is a branch of the Delta Cross languages (Benue-Congo) of south-eastern Nigeria; it includes Oring.",
                                description="Upper Cross is a branch of the Delta Cross group of Benue-Congo languages, spoken mainly in the Cross River basin. Glottolog places the Oring (Koring) language, spoken by the Ufia of Benue State among others, in its Koring-Kukele group."),
                    srcs=[("GLUC", "Upper Cross branch; Oring within it")]))

GAPS = [
    ("Ukum headquarters: Zaki Biam or Sankera", "Statoids gives Zaki Biam; Wikipedia gives Sankera (also the Tiv intermediate area's name). Needs the Benue State Government's list of LGA headquarters."),
    ("Creation dates of most Benue LGAs", "Only Ado (1989), Agatu (1996), Apa (1981; re-created 1991), Logo (1996) and Vandeikya (1976) are dated, from Wikipedia. The rest need the state or federal record of LGA creation."),
    ("Gboko LGA '11 May 1970'", "Wikipedia gives this date, before Nigeria's 1976 local government reform; not used. Needs checking."),
    ("Katsina-Ala Nok finds", "Wikipedia mentions archaeological finds of the Nok culture at Katsina-Ala; the site and finds need an archaeological source."),
    ("Official headquarters list", "Headquarters rest on Statoids and Wikipedia (Tier 3). An official Benue State list would raise them."),
    ("Basa-Makurdi classification", "No Glottolog entry was found under this name (Wikipedia calls it Basa-Benue). Needs a linguistic source."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=[], statistics=STATS, scope="Benue LGA profiles: headquarters, 2006 population and area, short sourced descriptions (23 LGAs); Upper Cross branch.")


def report():
    L = ["# Research batch 030 — Benue LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA part of Phase 3 for Benue, which the Phase 4 quality check found missing. Created in review; published only after your approval.", "",
         f"- **23 LGA descriptions** filled (the field was empty), {len([r for r in RECORDS if r['table'] == 'places'])} **headquarters towns** created as places (Makurdi reuses the existing place), 23 × 2006 population and area figures.",
         "- Headquarters: 16 agreed by Statoids and Wikipedia (*well documented*); 5 from Statoids alone (*reported*): Ado, Apa, Obi, Oju, Vandeikya; **Ukum left open** (Zaki Biam vs Sankera).",
         "- A new language branch, **Upper Cross** (Glottolog), so that Oring can be placed in its family (fix script).",
         "- All LGA pages stay under 300 words (noindex); sitemap unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Statoids | Wikipedia | Level |", "|---|---|---|---|---|"]
    for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
        L.append(f"| {name} | {hq or '— (open)'} | {s_hq} | {'agrees' if agree == 'SW' else ('Sankera' if agree == 'conflict' else 'not stated')} | {'well documented' if agree == 'SW' else ('—' if agree == 'conflict' else 'reported')} |")
    L += ["", "## The 23 descriptions", ""]
    for slug in LGAS:
        L += [f"**{LGAS[slug][0]}.** {text(slug)}", ""]
    L += ["## Not used", "", "- Political comment on the present Och'Idoma (Wikipedia, Otukpo).", "- Accounts of killings in Agatu (2014, 2016): these need separate, careful treatment.",
          "- Names of current council chairmen and officials (they change).", "- Gboko LGA 'created 11 May 1970' (before the 1976 reform; to be checked).", "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_030_benue_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_030_benue_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len([r for r in RECORDS if r['table'] == 'places'])} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)}")
