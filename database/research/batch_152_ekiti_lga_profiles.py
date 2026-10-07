"""
Research batch 152 — Ekiti LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
16 LGAs). Researched 2026-10-07. Pattern: batches 133 (Osun) and 146 (Ondo).

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.EK.*).
  I  INEC, 'Ekiti State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/EKITI-STATE.pdf via the
     Internet Archive; copy in data/inec_ekiti_lga_offices.pdf). Counted where the office is in the headquarters town.
  Wikipedia's 'Ekiti State' lists the 16 LGAs without headquarters; it names 'Aiyekire (Gbonyin)' and 'Ido-Osi'.
Statoids and INEC agree on all 16 headquarters. Names: the archive's 'Aiyekire' is 'Gbonyin' in INEC's 2015 and 2024
lists and Wikipedia ('Aiyekire (Gbonyin)'); 'Idosi-Osi' (Statoids) is 'Ido/Osi' (INEC) or 'Ido-Osi' (Wikipedia). The
descriptions give both; renaming the records is proposed in the QC fix (batch 153), as for Yewa North/South (Ogun).
Reused place: Ado Ekiti (#49). Town slugs end in '-ekiti' where the name does not already.
"""
import json, re, sys
import batch_148_ekiti_languages_peoples as P148

ACCESSED = "2026-10-07"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Ekiti rows (NG.EK.*): 2006 census, area, headquarters."),
    "INECS": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Ekiti State: INEC LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/EKITI-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (7 October 2026); copy in database/research/data/inec_ekiti_lga_offices.pdf.",
                  notes="LGA office addresses for all 16 LGAs (it names 'Gbonyin' and 'Ido Osi'); state office on New Iyin Road, Ado Ekiti."),
    "WEKS": dict(P148.SOURCES["WEKS"], notes="Reused. List of the 16 LGAs, including 'Aiyekire (Gbonyin)' and 'Ido-Osi'."),
}
# slug: (name, hq, statoids pop, area, agreement, inec office, extra)
LGAS = {
 "ado-ekiti": ("Ado Ekiti", "Ado Ekiti", 308621, 293, "SI", "Ado Ekiti", "Ado-Ekiti is the state capital and the seat of the Ewi of Ado, whose kingdom holds the annual Udiroko festival (Wikipedia; Ekiti State Government). It had the largest population of the state's LGAs at the 2006 census (Statoids)."),
 "aiyekire": ("Gbonyin, also called Aiyekire,", "Ode Ekiti", 148193, 391, "SI", "Ode Ekiti", "INEC and Wikipedia name the LGA Gbonyin; Statoids keeps Aiyekire."),
 "efon": ("Efon", "Efon Alaaye", 86941, 232, "SI", "Efon-Alaaye", "Efon-Alaaye is the seat of the Alaaye of Efon, the LGA's one recognised paramount ruler; the Ekiti State Government notes that its people speak the Ekiti dialect with an intonation related to Ijesa (Ekiti State Government; Wikipedia)."),
 "ekiti-east": ("Ekiti East", "Omuo Ekiti", 137955, 1072, "SI", "Omuo-Ekiti", "The Ahan language is spoken at Ahan, one of Omuo's communities, and Omuo's Yoruba is close to Yagba-Ekiti speech (Blench's Atlas; Wikipedia). It had the largest area of the state's LGAs (Statoids)."),
 "ekiti-south-west": ("Ekiti South-West", "Ilawe Ekiti", 165277, 346, "SI", "Ilawe Ekiti", ""),
 "ekiti-west": ("Ekiti West", "Aramoko Ekiti", 179892, 366, "SI", "Aramoko Ekiti", "The Ikogosi Warm Springs are in the LGA (Wikipedia; INEC's Ikogosi ward)."),
 "emure": ("Emure", "Emure Ekiti", 93884, 301, "SI", "Emure Ekiti", "Statoids writes Emure."),
 "idosi-osi": ("Ido-Osi, also written Idosi-Osi,", "Ido Ekiti", 159114, 232, "SI", "Ido Ekiti", "INEC writes Ido/Osi and Wikipedia Ido-Osi; Statoids keeps Idosi-Osi."),
 "ijero": ("Ijero", "Ijero Ekiti", 221405, 391, "SI", "Ijero Ekiti", "Statoids writes Ijero."),
 "ikere": ("Ikere", "Ikere Ekiti", 147355, 263, "SI", "Ikere Ekiti", "Ikere-Ekiti is the seat of the Ogoga, whose authority is challenged by the Olukere; the Olosunta and Orole hills rise in the LGA (Wikipedia; Peoples Gazette). Statoids writes Ikere."),
 "ikole": ("Ikole", "Ikole Ekiti", 168436, 321, "SI", "Ikole-Ekiti", "Statoids writes Ikole."),
 "ilejemeje": ("Ilejemeje", "Iye Ekiti", 43530, 95, "SI", "Iye Ekiti", "It had the smallest population and area of the state's LGAs (Statoids), which writes Iye."),
 "irepodun-ifelodun": ("Irepodun/Ifelodun", "Igede Ekiti", 129149, 356, "SI", "Igede Ekiti", "Statoids writes Igede."),
 "ise-orun": ("Ise/Orun", "Ise Ekiti", 113754, 432, "SI", "Ise Ekiti", "Statoids writes Ise."),
 "moba": ("Moba", "Otun Ekiti", 146496, 199, "SI", "Otun Ekiti", "Otun is the seat of the Oore, styled chairman of the traditional rulers of Moba land, and its Yoruba is close to Igbomina speech (Ekiti State Government; New Telegraph; Wikipedia)."),
 "oye": ("Oye", "Oye Ekiti", 134210, 507, "SI", "Oye Ekiti", "Ire-Ekiti, in the LGA, holds the Ogun Onire festival, and its Ogun grove is a proposed national monument (NCMM; The Sun)."),
}
NAMES_OF = {"S": "Statoids", "I": "INEC"}
REUSE = {"ado-ekiti": "@places:ado-ekiti"}
HQ_PREFIX = {"aiyekire": "Gbonyin", "idosi-osi": "Ido-Osi"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SI" if c in agree]
    return n[0] if len(n) == 1 else " and ".join(n)


def text(slug):
    name, hq, pop, area, agree, office, extra = LGAS[slug]
    t = f"{name} is a local government area of Ekiti State with its headquarters at {hq} (according to {by(agree)})."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    if extra: t += " " + extra
    t += " Its people are Yoruba of the Ekiti sub-group (Wikipedia; the federal profile)."
    return t


RECORDS, UPDATES, STATS, NAMES = [], [], [], []
for slug, (name, hq, pop, area, agree, office, extra) in LGAS.items():
    short = HQ_PREFIX.get(slug, name)
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{short}: headquarters, 2006 population, area"), ("WEKS", f"{short}: name"), ("INECS", f"{short}: INEC LGA office")]
    ref = f"@admin_units:lga:ekiti/{slug}"
    if slug in REUSE:
        upd["headquarters_place_id"] = REUSE[slug]
    else:
        key = f"hq_{slug}"
        s = slugify(hq)
        s = s if s.endswith("ekiti") else s + "-ekiti"
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources", level="well_documented",
                            fields=dict(place_type="town", name=hq, slug=s, admin_unit_id=ref, status="existing",
                                        summary=f"{hq} is the headquarters of {short} Local Government Area, Ekiti State."),
                            srcs=[("STAT", f"Headquarters of {short}"), ("INECS", f"INEC LGA office at {office}")]))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=ref, fields=upd, srcs=srcs))
    STATS.append(dict(record=ref, metric="population", value_low=pop, reference_year=2006, method="census", notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=ref, metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
NAMES = [
    dict(record="hq_efon", name="Efon-Alaaye", name_type="spelling_variant", usage_notes="INEC and Wikipedia write Efon-Alaaye.", srcs=["INECS"]),
    dict(record="hq_ekiti-east", name="Omuo-Ekiti", name_type="spelling_variant", usage_notes="INEC and Wikipedia write Omuo-Ekiti.", srcs=["INECS"]),
]
GAPS = [
    ("Ekiti: Gbonyin and Ido-Osi", "INEC (2015 and 2024) and Wikipedia name these LGAs Gbonyin and Ido/Osi (Ido-Osi); Statoids and the archive have Aiyekire and Idosi-Osi. The renaming is proposed in batch 153; the date of the change to Gbonyin is not given in a source read."),
    ("Ekiti: Wikipedia's headquarters", "Wikipedia's state page does not list the headquarters; Statoids and INEC agree on all 16, but a state government list would add a third source."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES, relations=[], statistics=STATS,
                scope="Ekiti LGA profiles: headquarters, 2006 population and area, short sourced descriptions (16 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 152 — Ekiti LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Ekiti. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **16 LGA descriptions** and **{len(RECORDS)} new headquarters towns**. Ado Ekiti reuses the existing place. New town slugs end in '-ekiti'.",
         "- **Headquarters:** Statoids and INEC's LGA office list agree for all 16. Wikipedia's state page lists the LGAs without headquarters.",
         "- **Names:** the archive's **Aiyekire** is **Gbonyin**, and its **Idosi-Osi** is **Ido-Osi**, in INEC's lists and Wikipedia. The descriptions say 'Gbonyin, also called Aiyekire'.",
         "  - Renaming the two LGA records, keeping the old names as other names, will be proposed in the QC fix (batch 153), as was done for Yewa North and South.",
         "- **Each description** names any seat of a ruler, monument, festival or smaller language recorded in batches 148–151.",
         "- **Figures:** 2006 population and area from Statoids.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## The 16 descriptions", ""] + [f"**{HQ_PREFIX.get(s, LGAS[s][0])}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_152_ekiti_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_152_ekiti_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)}")
