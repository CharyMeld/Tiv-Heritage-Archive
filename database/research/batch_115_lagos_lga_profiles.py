"""
Research batch 115 — Lagos LGA profiles (headquarters, 2006 population and area, short sourced descriptions for the
20 LGAs). Researched 2026-10-02. Pattern: batches 103 and 109.

Headquarters sources:
  S  Statoids (reused): headquarters, 2006 census population, area (rows NG.LA.*).
  W  Wikipedia, 'Lagos State': the LGA table's 'Administrative capital' column and the five divisions. It agrees with
     Statoids on every headquarters.
  I  INEC, 'Lagos State' LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/LAGOS-STATE.pdf via the
     Internet Archive; copy in data/inec_lagos_lga_offices.pdf). Counted as agreeing where the office is in the
     headquarters town.
Three LGAs get no headquarters town: Statoids and Wikipedia give only the LGA's own compound name ('Ajeromi/Ifelodun',
'Oshodi/Isolo', 'Lagos Mainland'), and INEC's offices are at Ajegunle, Oshodi and Yaba. Recorded as a gap.
Reused place: Ikeja. Population and area: Statoids (the 2006 census). Wikipedia's table gives slightly different 2006
figures, cited to the Lagos State government; those are not recorded.
"""
import json, re, sys
import batch_111_lagos_languages_peoples as P111
import batch_113_lagos_institutions as I113
import batch_114_lagos_heritage as H114

ACCESSED = "2026-10-02"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Lagos rows (NG.LA.*): 2006 census, area, headquarters."),
    "INECL": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Lagos State: INEC LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/LAGOS-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (2 October 2026); copy in database/research/data/inec_lagos_lga_offices.pdf.",
                  notes="LGA office addresses for all 20 LGAs; state headquarters at 6 Birrel Avenue, Sabo Yaba."),
    "WLAG": dict(P111.SOURCES["WLAG"], notes="Reused. LGA table: area, 2006 population, administrative capital and division for each of the 20 LGAs."),
}
for k in ("NCMML", "NCMMP", "NCMMM"):
    SOURCES[k] = H114.SOURCES[k]
DIV = {"ikeja": ["agege", "alimosho", "ifako-ijaye", "ikeja", "kosofe", "mushin", "oshodi-isolo", "shomolu"],
       "lagos": ["apapa", "eti-osa", "lagos-island", "lagos-mainland", "surulere"],
       "badagry": ["ajeromi-ifelodun", "amuwo-odofin", "ojo", "badagry"], "ikorodu": ["ikorodu"], "epe": ["ibeju-lekki", "epe"]}
DIVOF = {l: d.capitalize() for d, ls in DIV.items() for l in ls}
# slug: (name, hq or None, statoids pop, area km2, agreement, inec office town, extra, [extra source keys])
LGAS = {
 "agege": ("Agege", "Agege", 459939, 11, "SWI", "Orile Agege", "", []),
 "ajeromi-ifelodun": ("Ajeromi-Ifelodun", None, 684105, 12, "", "Ajegunle", "", []),
 "alimosho": ("Alimosho", "Ikotun", 1277714, 185, "SWI", "Ikotun", "It had the largest population of the state's LGAs at the 2006 census (Statoids).", []),
 "amuwo-odofin": ("Amuwo-Odofin", "Festac Town", 318166, 135, "SWI", "Festac Town", "", []),
 "apapa": ("Apapa", "Apapa", 217362, 27, "SWI", "Apapa", "", []),
 "badagry": ("Badagry", "Badagry", 241093, 441, "SWI", "Badagry", "It is the seat of the Badagry Kingdom, whose ruler is the Akran, and its heritage sites include the First Storey Building in Nigeria, the Brazilian Barracoon and Point of No Return, both proposed national monuments, and the Vlekete slave market (NCMM; Wikipedia).", ["NCMMP"]),
 "epe": ("Epe", "Epe", 181409, 1185, "SWI", "Epe", "It had the largest area of the state's LGAs (Statoids).", []),
 "eti-osa": ("Eti-Osa", "Ikoyi", 287785, 192, "SW", "Igbo Efon", "", []),
 "ibeju-lekki": ("Ibeju-Lekki", "Akodo", 117481, 455, "SW", "Orofun/Orimedu, near Akodo", "It had the smallest population of the state's LGAs at the 2006 census (Statoids).", []),
 "ifako-ijaye": ("Ifako-Ijaye", "Ifako", 427878, 27, "SW", "Alagbado", "", []),
 "ikeja": ("Ikeja", "Ikeja", 313196, 46, "SWI", "Ikeja", "Ikeja is the capital of Lagos State (federal state profile).", []),
 "ikorodu": ("Ikorodu", "Ikorodu", 535619, 394, "SWI", "Ikorodu", "", []),
 "kosofe": ("Kosofe", "Kosofe", 665393, 81, "SW", "Ogudu", "", []),
 "lagos-island": ("Lagos Island", "Lagos Island", 209437, 9, "SWI", "Lagos Island", "It is the seat of the Kingdom of Lagos, whose palace, Iga Idunganran, is a declared national monument, and it holds the National Museum Lagos and many of the state's other listed monuments (NCMM; Wikipedia).", ["NCMML", "NCMMM"]),
 "lagos-mainland": ("Lagos Mainland", None, 317720, 19, "", "Yaba", "", []),
 "mushin": ("Mushin", "Mushin", 633009, 17, "SWI", "Mushin", "", []),
 "ojo": ("Ojo", "Ojo", 598071, 158, "SW", "Igbede", "It is the seat of the Olojo of Ojo (Wikipedia).", []),
 "oshodi-isolo": ("Oshodi-Isolo", None, 621509, 45, "", "Oshodi", "", []),
 "shomolu": ("Shomolu", "Shomolu", 402673, 12, "SWI", "Shomolu", "INEC writes Somolu.", []),
 "surulere": ("Surulere", "Surulere", 503975, 23, "SWI", "Surulere", "The National Theatre at Iganmu, a proposed national monument, is in Surulere (NCMM; Wikipedia).", ["NCMMP"]),
}
SOURCES["FGLA"] = P111.SOURCES["FGLA"]
LGAS["ikeja"] = LGAS["ikeja"][:7] + (["FGLA"],)
SUB = {"Awori": "the Awori", "Ijebu": "the Ijebu", "Egba": "the Egba and Egbado"}
PEOPLES = {}
for r in P111.RELATIONS:
    if "lga:lagos" in r["to"] and r["type"] == "present_in":
        l = r["to"].split("/")[-1]
        if r["frm"] == "ogu":
            PEOPLES.setdefault(l, []).append("Ogu")
        else:
            subs = [v for k, v in SUB.items() if k in r["notes"]]
            PEOPLES.setdefault(l, []).append("Yoruba (" + " and ".join(subs) + ")" if subs else "Yoruba")
REUSE = {"ikeja": "@places:ikeja"}
NAMES_OF = {"S": "Statoids", "W": "Wikipedia", "I": "INEC"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SWI" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, hq, pop, area, agree, office, extra, _k = LGAS[slug]
    if hq:
        t = f"{name} is a local government area of Lagos State, in the {DIVOF[slug]} Division, with its headquarters at {hq} (according to {by(agree)})."
        if "I" not in agree:
            t += f" INEC's LGA office is at {office}."
    else:
        t = (f"{name} is a local government area of Lagos State, in the {DIVOF[slug]} Division. Statoids and Wikipedia give its headquarters only as "
             f"'{name.replace('-', '/') if slug != 'lagos-mainland' else name}', the LGA's own name; INEC's LGA office is at {office}.")
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    if extra: t += " " + extra
    ps = PEOPLES.get(slug, [])
    if ps:
        t += f" The peoples recorded for the LGA in this archive include the {' and the '.join(ps)}."
    return t


RECORDS, UPDATES, STATS = [], [], []
for slug, (name, hq, pop, area, agree, office, extra, keys) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("WLAG", f"{name}: administrative capital and division"), ("INECL", f"{name}: INEC LGA office at {office}")]
    srcs += [(k, f"{name}: {SOURCES[k]['title']}") for k in keys]
    if hq and slug in REUSE:
        upd["headquarters_place_id"] = REUSE[slug]
    elif hq:
        key = f"hq_{slug}"
        hsrc = [("STAT", f"Headquarters of {name}"), ("WLAG", f"Administrative capital of {name}")] + ([("INECL", f"INEC LGA office at {office}")] if "I" in agree else [])
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources", level="well_documented",
                            fields=dict(place_type="town", name=hq, slug=slugify(hq) + "-lagos", admin_unit_id=f"@admin_units:lga:lagos/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Lagos State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:lagos/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:lagos/{slug}", metric="population", value_low=pop, reference_year=2006, method="census", notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:lagos/{slug}", metric="area_km2", value_low=area, method="other", notes="Area as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
GAPS = [("Lagos: three LGA headquarters", "Statoids and Wikipedia give Ajeromi-Ifelodun, Oshodi-Isolo and Lagos Mainland only their own names as headquarters; INEC's offices are at Ajegunle, Oshodi and Yaba. A source naming the secretariat town is needed."),
        ("Lagos: 2006 figures", "Wikipedia's LGA table gives slightly different 2006 populations and areas, cited to the Lagos State government; Statoids' census figures are recorded."),
        ("Lagos: headquarters differing from INEC offices", "For Eti-Osa (Ikoyi; INEC at Igbo Efon), Ibeju-Lekki (Akodo; INEC near Akodo), Ifako-Ijaye (Ifako; INEC at Alagbado), Kosofe (INEC at Ogudu) and Ojo (INEC at Igbede) the headquarters rests on Statoids and Wikipedia.")]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=[], gaps=GAPS, updates=UPDATES, relations=[], statistics=STATS,
                scope="Lagos LGA profiles: headquarters (17 LGAs), 2006 population and area, short sourced descriptions (20 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 115 — Lagos LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Lagos. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **20 LGA descriptions** and **{len(RECORDS)} new headquarters towns**. Ikeja reuses the existing place.",
         "- **Agreement on headquarters:** Statoids and Wikipedia's LGA table agree for all 17. INEC's office is in the same town for 12 of them.",
         "- **No town recorded for 3 LGAs:** Ajeromi-Ifelodun, Oshodi-Isolo and Lagos Mainland. The sources give only the LGA's own name, so INEC's office town is stated in the description instead.",
         "- **Figures:** the 2006 population and area from Statoids. Wikipedia's slightly different state-government figures are noted as a gap.",
         "- **Town slugs end in '-lagos'** (e.g. `badagry-lagos`), so they don't clash with later states.",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## The 20 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_115_lagos_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_115_lagos_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)}")
