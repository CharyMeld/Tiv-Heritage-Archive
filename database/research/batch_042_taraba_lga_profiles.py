"""
Research batch 042 — Taraba LGA profiles (headquarters, 2006 population and area, short sourced
descriptions for the 16 LGAs). Researched 2026-09-26. Pattern: Nasarawa batch 035.

Sources:
  * Statoids (reused): headquarters, 2006 census population, area. Statoids also lists a Taraba row
    'Disputed Areas' (NG.TA.DA, 20,253 people in 2006, no area) — not an LGA; recorded as a gap.
  * INEC, "Taraba State" LGA office addresses (Word file of 19 Sept. 2012 on inecnigeria.org, read via
    the Internet Archive copy of 15 Aug 2026). Tier 1. For Ardo Kola (Sunkani), Donga, Gassol (Mutum
    Biyu), Kurmi (Baissa) and Yorro (Pantisawa) the address names the LGA council secretariat, which
    fixes the headquarters. For the other LGAs it only shows that INEC's LGA office is in the town.
  * Wikipedia article of each LGA (introduction). 'Kurmi' is an Indian caste; the LGA is 'Kurmi, Nigeria'.

Headquarters grading: two sources that explicitly name the headquarters = well documented; one = reported.
  * Yorro: Statoids "Yorro"; Wikipedia "Kpantisawa"; INEC (LG secretariat) "Pantisawa" -> Pantisawa
    (INEC's spelling, also the INEC ward names Pantisawa I and II); Kpantisawa kept as a variant.
  * Kurmi: Statoids and Wikipedia "Ba'Issa"; INEC (LG council secretariat) "Baissa" -> Baissa.
  * Gashaka: Statoids and Wikipedia "Serti"; INEC's LGA office is at Gashaka town -> Serti.
Not used from Wikipedia: population figures other than the 2006 census (Gassol 310,003 'JRC 2015';
Lau 149,700 'at the 2022 census' — no census was held in 2022; Donga 177,900; Jalingo 418,000/581,000);
Jalingo's ethnic percentages (unsourced); Wukari's 'major language ... Tiv and Wapan' (unsourced);
the name of the present Ukwe Takum (the stool is disputed, see batch 040); postal codes; officials.
"""
import json, re, sys

ACCESSED = "2026-09-26"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Taraba rows (2006 census, area, headquarters); also a row 'Disputed Areas' NG.TA.DA with 20,253 people and no area or headquarters."),
    "INECO": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Taraba State: INEC state and LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", publication_date="2012-09-19",
                  url="https://www.inecnigeria.org/wp-content/uploads/2024/04/TARABA-STATE.pdf", verification_status="verified",
                  notes="Read via the Internet Archive (snapshot 2026-08-15). One-page list: State HQ — Wuro Sembe Road, Jalingo; Ardo Kola — Sunkani, opposite Local Government Council Secretariat; Bali — opposite Government Lodge, Bali; Donga — Local Government Council Secretariat, Donga; Gashaka — opposite Police Station, Gashaka; Gassol — Pipeline Road, behind Local Government Council Secretariat, Mutum Biyu; Ibi — near Ibi Local Government Council Guest House, Ibi; Jalingo — Wuro Sembe Road; Karim Lamido — New Market Road, Karim Lamido; Kurmi — behind Local Government Council Secretariat, Baissa; Lau — behind Local Government Secretariat, Lau LGA; Sardauna — Gembu, Kabri Road; Takum — Yola Road, Takum; Ussa — Lissam, Takum-Ussa Road; Wukari — Takum Road, Wukari; Yorro — near Local Government Secretariat, Pantisawa; Zing — Hospital Road, Zing."),
}
# slug: (name, Wikipedia title, Statoids HQ, 2006 pop, area km2, HQ used, explicit HQ sources, text)
# explicit sources: S = Statoids, W = Wikipedia, I = INEC (council secretariat named)
LGAS = {
 "ardo-kola": ("Ardo Kola", "Ardo-Kola", "Sunkani", 86921, 2262, "Sunkani", "SWI", ""),
 "bali": ("Bali", "Bali, Nigeria", "Bali", 208935, 9146, "Bali", "S",
          "It is the largest LGA of the state by area. Wikipedia names the Federal Polytechnic, Bali among its institutions."),
 "donga": ("Donga", "Donga, Nigeria", "Donga", 134111, 3121, "Donga", "SWI",
           "Wikipedia describes Donga as a town on the Donga River, created as an LGA in 1991, home mainly to the Chamba with Tiv, Ichen, Hausa and Fulani communities. Donga is the seat of the Gara Donga, the Chamba ruler, and the home of the Purma festival."),
 "gashaka": ("Gashaka", "Gashaka", "Serti", 87781, 8393, "Serti", "SW",
             "INEC's office for the LGA is in Gashaka town. Serti is the seat of the Lamdo Gashaka, and Wikipedia names Fulfulde as the main language of Gashaka and Serti. The Gashaka sector of Gashaka-Gumti National Park is reached through Serti (Nigeria National Park Service); with 8,393 km² the LGA is the second-largest of the state by area after Bali."),
 "gassol": ("Gassol", "Gassol", "Mutum Biyu", 244749, 5548, "Mutum Biyu", "SWI",
            "At the 2006 census it was the most populous LGA of the state. Wikipedia describes the Benue River as its northern border, with the Taraba River flowing north through the LGA to their confluence, and names the Fulani, Wurkun, Tiv and Jukun as its main peoples, with the Dan-Anacha yam market and Jukun Wanu fishing communities at Tella."),
 "ibi": ("Ibi", "Ibi, Taraba State", "Ibi", 84054, 2672, "Ibi", "S",
         "Wikipedia places the town on the south bank of the Benue, notes that the Taraba and Donga rivers flow into the Benue within the LGA, and describes it as home to Jukun Wanu and Wurbo fishing communities. The Niger Company opened a trading station at Ibi by 1899, and from 1900 the British made it the administrative headquarters of western Muri. Lake Nwonyo, five kilometres north of the town, is the site of the Nwonyo Fishing Festival."),
 "jalingo": ("Jalingo", "Jalingo", "Jalingo", 139845, 191, "Jalingo", "S",
             "Jalingo town is the capital of Taraba State and the seat of the Muri Emirate; with 191 km² it is the smallest LGA of the state by area. The National Museum, Jalingo, and the state office of INEC (Wuro Sembe Road) are in the town."),
 "karim-lamido": ("Karim Lamido", "Karim Lamido", "Karim Lamido", 195844, 6620, "Karim Lamido", "S",
                  "Wikipedia describes the Benue River as its southern border and names among its peoples the Karimjo, Jenjo, Bambuka, Munga Lelau, Munga Dosso, Kodei, Dadiya, Bandawa, Wurkun and Fulani."),
 "kurmi": ("Kurmi", "Kurmi, Nigeria", "Ba'Issa", 91531, 4353, "Baissa", "SWI",
           "Wikipedia describes the LGA as bordering Cameroon to the south and Sardauna, Gashaka, Bali, Donga and Ussa LGAs, as a producer of cash and food crops and of timber, and notes the abandoned state-owned Baissa Timber Development Corporation."),
 "lau": ("Lau", "Lau, Nigeria", "Lau", 96590, 1660, "Lau", "S",
         "According to Wikipedia the name means 'mud' in the local Lau language; the LGA borders Ardo Kola, Jalingo, Yorro and Zing LGAs and Numan in Adamawa State."),
 "sardauna": ("Sardauna", "Sardauna, Taraba State", "Gembu", 224437, 4603, "Gembu", "SW",
              "Formerly called Mambilla, the LGA is, in Wikipedia's words, synonymous with the Mambilla Plateau. Gembu is the seat of the Chief of Mambilla."),
 "takum": ("Takum", "Takum", "Takum", 135349, 2503, "Takum", "S",
           "Wikipedia records that it was created out of Wukari LGA in June 1976 and that it borders Cameroon to the south, Ussa to the west and Donga to the north. Among its peoples it names the Kuteb, Ichen, Jukun (Kpanzon), Tiv, Chamba and Hausa. Takum is the seat of the Ukwe Takum, a stool whose succession is disputed, and the home of the Kuteb Kuchicheb festival."),
 "ussa": ("Ussa", "Ussa", "Lissam", 92017, 1495, "Lissam", "SW",
          "INEC's office for the LGA is at Lissam. Wikipedia records that Ussa was created in 1996, after an earlier attempt failed in 1983, that it borders Cameroon to the south with the Donga River as its northern boundary, and that the Kuteb are its main people."),
 "wukari": ("Wukari", "Wukari", "Wukari", 241546, 4308, "Wukari", "S",
            "Wikipedia describes the Donga River flowing through the LGA and the Benue forming its boundary with Nasarawa State to the north-west. Wukari is the seat of the Aku Uka, the ruler of the Jukun Wukari Federation; Puje, the Jukun sacred site, gives its name to one of the LGA's wards. Federal University Wukari and Kwararafa University are in the town."),
 "yorro": ("Yorro", "Yorro", "Yorro", 89410, 1275, "Pantisawa", "WI",
           "Statoids gives the headquarters as Yorro, but Wikipedia names Kpantisawa and INEC places its office near the Local Government Secretariat in Pantisawa. Wikipedia describes the LGA as predominantly Mumuye."),
 "zing": ("Zing", "Zing, Taraba State", "Zing", 127363, 1030, "Zing", "S",
          "Wikipedia describes Zing as predominantly Mumuye, made up of twelve clans. It is the seat of the Kpanti Zing; the Yakoko ward of the LGA shares its name with the Yakoko Stone Burial Ground, a proposed national monument."),
}
for slug, (name, wt, *_r) in LGAS.items():
    SOURCES[f"W_{slug}"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=wt, organisation="Wikipedia", url=W(wt),
                                verification_status="needs_corroboration", notes=f"Wikipedia article on {name} LGA (introduction), consulted {ACCESSED}.")
SOURCES["NPS"] = dict(source_type="official_website", source_kind="official_website", source_tier=1, title="Gashaka-Gumti National Park",
                      organisation="Nigeria National Park Service", url="https://nigeriaparkservice.gov.ng/gashaka-gumti/", verification_status="verified",
                      notes="Reused (batch 041). Access: the Gashaka sector via ...-Bali-Serti-Bodel Gate; airstrip at Serti.")


def fmt(n):
    return f"{n:,}"


BY = {"S": "Statoids", "SW": "Statoids and Wikipedia", "SWI": "Statoids, Wikipedia and INEC", "WI": "Wikipedia and INEC"}


def text(slug):
    name, wt, s_hq, pop, area, hq, agree, extra = LGAS[slug]
    first = f"{name} is a local government area of Taraba State with its headquarters at {hq} (according to {BY[agree]})."
    fig = f" Statoids gives it an area of about {fmt(area)} km² and a population of {fmt(pop)} at the 2006 census."
    return (first + fig + (" " + extra if extra else "")).strip()


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), (f"W_{slug}", f"{name}: Wikipedia introduction"), ("INECO", f"{name}: INEC LGA office address")]
    if slug == "gashaka":
        srcs.append(("NPS", "Gashaka sector of the park reached through Serti"))
    if slug != "jalingo":
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([(f"W_{slug}", f"Headquarters of {name}")] if "W" in agree else []) \
               + ([("INECO", f"LGA council secretariat at {hq}")] if "I" in agree else [])
        two = len(agree) >= 2
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if two else "single_reliable_source",
                            level="well_documented" if two else "reported",
                            fields=dict(place_type="town", name=hq, slug=re.sub(r"[^a-z0-9]+", "-", hq.lower()).strip("-"),
                                        admin_unit_id=f"@admin_units:lga:taraba/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Taraba State."),
                            srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    else:
        upd["headquarters_place_id"] = "@places:jalingo"
    UPDATES.append(dict(ref=f"@admin_units:lga:taraba/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:taraba/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:taraba/{slug}", metric="area_km2", value_low=area, method="other",
                      notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_kurmi", name="Ba'Issa", name_type="spelling_variant", usage_notes="Form given by Statoids and Wikipedia.", srcs=["STAT", "W_kurmi"]),
    dict(record="hq_yorro", name="Kpantisawa", name_type="spelling_variant", usage_notes="Form given by Wikipedia.", srcs=["W_yorro"]),
    dict(record="hq_gassol", name="Mutumbiyu", name_type="spelling_variant", usage_notes="Wikipedia: 'Mutum Biyu (or Mutumbiyu or Mutum Mbiyu)'.", srcs=["W_gassol"]),
    dict(record="hq_gassol", name="Mutum Mbiyu", name_type="spelling_variant", usage_notes="Wikipedia: 'Mutum Biyu (or Mutumbiyu or Mutum Mbiyu)'.", srcs=["W_gassol"]),
]
RELATIONS = []

GAPS = [
    ("Statoids 'Disputed Areas' of Taraba", "Statoids lists a Taraba row 'Disputed Areas' with 20,253 people at the 2006 census and no area or headquarters. Which areas these are (and with which state or LGA they are disputed) needs the census publication."),
    ("Creation dates of Taraba LGAs", "Only three dates were found, all from Wikipedia: Takum (June 1976, from Wukari), Donga (1991) and Ussa (1996, after a failed attempt in 1983). An official list is needed."),
    ("Headquarters resting on one source", "Bali, Ibi, Jalingo, Karim Lamido, Lau, Takum, Wukari and Zing rest on Statoids alone (INEC shows only that its office is in the town). An official Taraba State list would settle them."),
    ("Yorro headquarters spelling", "INEC writes Pantisawa (also its ward names), Wikipedia Kpantisawa; Statoids gives Yorro as the headquarters. Pantisawa is used, with Kpantisawa as a variant."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATS,
                scope="Taraba LGA profiles: headquarters, 2006 population and area, short sourced descriptions (16 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    places = [r for r in RECORDS if r["table"] == "places"]
    L = ["# Research batch 042 — Taraba LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Taraba. Created in review; published only after your approval.", "",
         f"- **16 LGA descriptions** filled (the field was empty), **{len(places)} headquarters towns** created as places (Jalingo reuses the existing place), 16 × 2006 population and area figures.",
         "- **New official source:** INEC's list of its Taraba LGA offices (2012). For five LGAs it names the council secretariat's town.",
         "- **Yorro → Pantisawa** (INEC + Wikipedia 'Kpantisawa'), not \"Yorro\" as Statoids has it. **Kurmi → Baissa** (INEC; Statoids and Wikipedia 'Ba'Issa'). **Gashaka → Serti** (INEC's office is at Gashaka town).",
         "- Wikipedia figures not copied: Lau '149,700 at the 2022 census' (no census in 2022); Gassol's JRC 2015 estimate; Jalingo's ethnic percentages. The disputed Ukwe Takum is not named.",
         "- The LGA pages stay under 300 words (noindex); sitemap unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Statoids | Wikipedia | INEC office | Level |", "|---|---|---|---|---|---|"]
    winf = {"ardo-kola": "Sunkani", "donga": "Donga", "gashaka": "Serti", "gassol": "Mutum Biyu", "kurmi": "Ba'Issa", "sardauna": "Gembu", "ussa": "Lissam", "yorro": "Kpantisawa"}
    inec = {"ardo-kola": "Sunkani (LG Council Secretariat)", "bali": "Bali", "donga": "Donga (LG Council Secretariat)", "gashaka": "Gashaka",
            "gassol": "Mutum Biyu (LG Council Secretariat)", "ibi": "Ibi", "jalingo": "Jalingo", "karim-lamido": "Karim Lamido",
            "kurmi": "Baissa (LG Council Secretariat)", "lau": "Lau LGA (LG Secretariat; town not named)", "sardauna": "Gembu", "takum": "Takum",
            "ussa": "Lissam", "wukari": "Wukari", "yorro": "Pantisawa (LG Secretariat)", "zing": "Zing"}
    for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
        lvl = "well documented" if len(agree) >= 2 else "reported"
        L.append(f"| {name} | {hq} | {s_hq} | {winf.get(slug, 'not stated')} | {inec[slug]} | {lvl} |")
    L += ["", "## The 16 descriptions", ""]
    for slug in LGAS:
        L += [f"**{LGAS[slug][0]}** ({words(text(slug))} words). {text(slug)}", ""]
    L += ["## Sources", "", "- **STAT** — Statoids, Local Government Areas of Nigeria (2006 census; areas). Reused.",
          "- **INECO** — INEC, Taraba State LGA office addresses (2012), https://www.inecnigeria.org/wp-content/uploads/2024/04/TARABA-STATE.pdf (via the Internet Archive). Tier 1.",
          "- **W_…** — the 16 Wikipedia LGA articles. Tier 3.", "",
          "## Not used", "",
          "- Wikipedia population figures that are not the 2006 census (Gassol 310,003 'JRC 2015'; Lau 149,700 '2022 census'; Donga 177,900; Jalingo 418,000 (2018) and 581,000 (2022)).",
          "- Jalingo's ethnic percentages (Fulani 60%, Mumuye 25% …) and Wukari's 'major language … Tiv and Wapan': unsourced.",
          "- The name of the present Ukwe Takum given by Wikipedia (the stool is disputed; batch 040), and Wikipedia's account of the Ukwe's history as the Kuteb's alone.",
          "- Climate figures, postal codes, and named council chairmen and officials.", "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_042_taraba_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_042_taraba_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len([r for r in RECORDS if r['table'] == 'places'])} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)} names={len(NAMES)}",
          sorted((words(text(s)), s) for s in LGAS)[-3:])
