"""
Research batch 075b — Bauchi (Phase 3): the peoples of Bauchi State and the LGAs where they live, as far as the sources
allow. Researched 2026-10-01. Pattern: batches 063b and 069b.

Sources:
  * Federal Government of Nigeria, state profile 'Bauchi' (nigeria.gov.ng/states/bauchi; copy in
    data/fg_bauchi_2026-10-01.html) — Tier 1: 'Hausa, Fulani, Sayawa, Bulewa, Karekare, Kanuri, Warjawa, Zulawa and
    Badawa etc., a total of 55 ethnic groups'; Hausa and English the most widely spoken, Fulfulde widely spoken.
  * Wikipedia, 'Bauchi State': introduction (Bolewa, Butawa and Warji in the centre; Fulani, Kanuri and Karai-Karai in
    the north; Bankal, Jaku and Gerawa in and around Bauchi city; Zaar and Gwak in the south; Dugurawa in the
    south-east; Jarawa in the south-west), 'Population' (… Gerawa, Sayawa, Jarawa, Kirfawa, Turawa, Bolewa,
    Karai-Karai, Kanuri, Fa'awa, Butawa, Warjawa, Zulawa, Boyawa, MBadawa) and its table of languages by LGA.
  * Blench's Atlas (batch 075) for the links between the peoples' names and their languages:
    Butawa = Gamo (Gamo–Ningi cluster, 2.C); Jaku = Labɨr (index); Fa'awa = Pa'a (index); Dugurawa = the Doori (Duguri)
    member of the Jar cluster (2.C); Gwak = Gingwak (Jar cluster, index); Ɓankal = the Zhar member of the Jar cluster;
    Badawa / Mbadawa = the Mbat member of the Jar cluster (2.C) — so the federal profile's 'Badawa' is read as Mbat, not
    Bade (gap); Zulawa = Zul, a member of the Polci cluster (2.C).

Sensitive names: the federal profile and Wikipedia use 'Sayawa'; Blench's Atlas notes that the 'Saya' terms are now
considered derogatory. The record is named Zaar and the Saya names are not recorded as other names (batch 075).

Rules (as 063b/069b): a people record only for a group named by the federal profile or Wikipedia whose language is
recorded; LGA links from Wikipedia's language table — well documented where the Atlas also places the language there,
reported otherwise; presence only. Not created: Jarawa (a Hausa cover name, per the Atlas, for several peoples whose
members here are recorded as Bankal, Gwak and Duguri), Kirfawa, Turawa and Boyawa (no Atlas match).
"""
import json, sys
import batch_075_bauchi_languages as B75

ACCESSED = "2026-10-01"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "FGBA": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Bauchi State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/bauchi/", verification_status="verified",
                 notes="'Ethnic Profile': 'Hausa, Fulani, Sayawa, Bulewa, Karekare, Kanuri, Warjawa, Zulawa and Badawa etc., a total of 55 ethnic groups.' Read 2026-10-01 (copy in database/research/data/)."),
    "WPBA": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Bauchi State", organisation="Wikipedia", url=W("Bauchi State"),
                 verification_status="needs_corroboration", notes="Introduction (peoples by region), 'Population' list, and the table of languages by LGA. Accessed 2026-10-01."),
    "ATLAS": B75.SOURCES["ATLAS"],
}
LGA = B75.LGA
ATLAS_LGA = {k: set(v[4]) for k, v in B75.LANG.items()}
for lang, lgas, _ in B75.EXISTING:
    ATLAS_LGA[lang] = set(lgas)
E, X = "endonym", "alternative"
# slug: (name, [langs], [(other name, type, note, src)], note, [(lga, detail)], named by)
PEOPLE = {
 "zaar": ("Zaar", ["zaar"], [("Vìk Zaar", E, "Atlas, Zaar entry, 1.B (name of the language)", "ATLAS")],
          "Wikipedia places the Zaar in the south of the state. The federal profile and Wikipedia also use an older Hausa-derived name for them, which Blench's Atlas notes is now considered derogatory; it is not used here. Their language, Zaar, is spoken west of Tafawa Balewa town.",
          [("tafawa-balewa", "the Zaar language ('Za'ar')"), ("bogoro", "the Zaar language ('Za'ar')"), ("dass", "the Zaar language, under the older name"), ("toro", "the Zaar language, under the older name")], "FG, Wikipedia"),
 "warji": ("Warji", ["warji"], [("Warjawa", X, "Federal Government state profile; Wikipedia", "FGBA"), ("Sәrzakwai", E, "Atlas, Warji entry, 1.B (name of the language)", "ATLAS")],
           "Wikipedia places the Warji in the centre of the state. Blench's Atlas places their language in Ganjuwa district and in Warji district of Ningi LGA (Warji district is now Warji LGA), and in Jigawa State.",
           [("ningi", "Warji")], "FG, Wikipedia"),
 "zul": ("Zul", ["polci"], [("Zulawa", X, "Federal Government state profile; Wikipedia; Atlas, Zul, 2.C", "FGBA"), ("Nya Zule", E, "Atlas, Zul, 1.C", "ATLAS")],
         "Blench's Atlas places Zul, a member of the Polci cluster, in Bauchi and Toro LGAs, in about fifteen villages, and notes that it is mutually intelligible with Mbaram.",
         [("bauchi", "Polci; Luri"), ("toro", "Polci"), ("dass", "Polci")], "FG, Wikipedia"),
 "mbat": ("Mbat", ["jar"], [("Badawa", X, "Federal Government state profile; Atlas, Mbat, 2.C", "FGBA"), ("Mbadawa", X, "Wikipedia ('MBadawa'); Atlas, Mbat, 2.C", "WPBA")],
          "Blench's Atlas gives Badawa and Mbadawa as names of the Mbat, a member of the Jar cluster in the north-central part of Kanam LGA, Plateau State; the federal profile's 'Badawa' is read here as the Mbat, not the Bade. The archive's separate Mbat language record (Jarawan) is not linked, as the Atlas treats Mbat as a Jar member.",
          [("bauchi", "Mbat")], "FG, Wikipedia"),
 "bankal": ("Bankal", ["jar"], [("Ɓankal", "spelling_variant", "Atlas, Jar cluster, Zhar member, 2.A", "ATLAS"), ("Zhar", E, "Atlas, Jar cluster, Zhar member, 1.B (name of the language)", "ATLAS"), ("Bankalawa", X, "Atlas, Zhar member, 2.C", "ATLAS")],
            "Wikipedia places the Bankal in and around Bauchi city. Blench's Atlas places their language, the Zhar (Ɓankal) member of the Jar cluster, from Dass town north to Bauchi town, in Dass, Bauchi and Toro LGAs.",
            [("bauchi", "Bankal"), ("dass", "Bankal"), ("toro", "Bankal"), ("tafawa-balewa", "Bankal")], "Wikipedia"),
 "gera": ("Gera", ["fyandigeri"], [("Gerawa", X, "Wikipedia", "WPBA")],
          "Wikipedia places the Gerawa in and around Bauchi city. Blench's Atlas places their language, Fyandigeri, in Bauchi and Darazo LGAs in at least thirty villages, and notes that many Gera villages no longer speak it.",
          [("bauchi", "Gera"), ("ganjuwa", "Gera")], "Wikipedia"),
 "butawa": ("Butawa", ["gamo-ningi"], [("Buta", X, "Atlas, Gamo member, 2.C", "ATLAS"), ("à-ndi-Gamo", E, "Atlas, Gamo member, 1.C", "ATLAS")],
            "Wikipedia places the Butawa in the centre of the state. Blench's Atlas gives Butawa as a name of the Gamo, whose language, Gamo, belongs to the Gamo–Ningi cluster of Ningi LGA; of some thirty-two Gamo settlements, only Kurmi still spoke the language in 1974, and most of the people speak Hausa.",
            [("ningi", "Gamo-Ningi")], "Wikipedia"),
 "jaku": ("Jaku", ["labir"], [],
          "Wikipedia places the Jaku in and around Bauchi city. Blench's Atlas gives Jaku as a name for Labɨr, a Jarawan language spoken in around ten villages south of the Bauchi–Gombe road, from Bauchi LGA to Gar in Alkaleri LGA.",
          [("alkaleri", "Labir"), ("bauchi", "Labir")], "Wikipedia"),
 "gwak": ("Gwak", ["jar"], [("Gingwak", X, "Atlas, Jar cluster (index: 'Gwak = Gingwak')", "ATLAS")],
          "Wikipedia places the Gwak in the south of the state. Blench's Atlas names Gwak as Gingwak, a member of the Jar cluster spoken from Dass town south to Tafawa Balewa.",
          [("dass", "Gwak"), ("tafawa-balewa", "Gwak")], "Wikipedia"),
 "duguri": ("Duguri", ["jar"], [("Dugurawa", X, "Wikipedia; Atlas, Doori member, 2.C", "WPBA"), ("Dõõri", E, "Atlas, Doori member, 1.B (name of the language)", "ATLAS")],
            "Wikipedia places the Dugurawa in the south-east of the state. Blench's Atlas places their language, the Doori member of the Jar cluster, in Alkaleri and Tafawa Balewa LGAs and in Kanam LGA of Plateau State, and notes that it is gradually yielding to Hausa.",
            [("alkaleri", "Duguri"), ("bauchi", "Duguri")], "Wikipedia"),
 "paa": ("Pa'a", ["paa"], [("Fa'awa", X, "Wikipedia; Atlas index ('Fa'awa = Pa'a')", "WPBA"), ("FuCaka", E, "Atlas, Pa'a entry, 1.B (name of the language)", "ATLAS")],
         "Blench's Atlas places the Pa'a language in Ningi and Darazo LGAs.", [("ningi", "Pa'a"), ("bauchi", "Pa'a")], "Wikipedia"),
}
EXISTING = {  # slug: (langs, state note, state source, [(lga, detail)])
 "hausa": ([], "The federal profile names the Hausa first among the state's ethnic groups and Hausa as its most widely spoken language.", "FGBA", [("misau", "Hausa")]),
 "fulani": (["fulfulde"], "Named by the federal profile; Wikipedia places the Fulani in the north of the state.", "FGBA", [("misau", "Fulani")]),
 "kanuri": (["kanuri"], "Named by the federal profile; Wikipedia places the Kanuri in the north of the state.", "FGBA", [("misau", "Kanuri")]),
 "karekare": (["karekare"], "Named by the federal profile ('Karekare'); Wikipedia places the Karai-Karai in the north.", "FGBA", [("damban", "Karai-karai"), ("gamawa", "Karai-karai"), ("misau", "Karai-karai")]),
 "bolewa": (["bole"], "Named by the federal profile ('Bulewa'); Wikipedia places the Bolewa in the centre of the state.", "FGBA", [("alkaleri", "Bole"), ("darazo", "Bole")]),
 "ngamo": (["ngamo"], "Wikipedia's language table lists Ngamo in Darazo; the Atlas places the Ngamo language there.", "WPBA", [("darazo", "Ngamo")]),
 "bade": (["bade"], "Wikipedia's language table lists Bade in Zaki LGA.", "WPBA", [("zaki", "Bade")]),
 "shuwa-arabs": ([], "Wikipedia's language table lists Shuwa in Misau LGA.", "WPBA", [("misau", "Shuwa")]),
 "ngas": ([], "Wikipedia's language table lists Angas in Tafawa Balewa and Toro LGAs.", "WPBA", [("tafawa-balewa", "Angas"), ("toro", "Angas")]),
}


def grade(langs, lga):
    atlas = any(lga in ATLAS_LGA.get(l, ()) for l in langs)
    return (("multiple_sources", "well_documented") if atlas else ("single_reliable_source", "reported")), atlas


def note(detail, atlas):
    return f"Wikipedia's language table lists {detail} in this LGA." + (" Blench's Atlas places their language here." if atlas else "") + " Presence only; the nature of their presence is not established."


def text(slug):
    name, langs, alt, nt, evs, by = PEOPLE[slug]
    who = {"FG, Wikipedia": ", named among the state's ethnic groups by the federal government's state profile and by Wikipedia",
           "Wikipedia": ", named among the state's ethnic groups by Wikipedia"}[by]
    t = f"The {name} are a people of Bauchi State{who}."
    names = [n for n, _, _, _ in alt]
    if names: t += f" Other names include {', '.join(names)}."
    t += " " + nt
    ls = [l for l, _ in evs]
    if ls:
        t += f" Wikipedia's language table places them in {', '.join(LGA[l] for l in ls[:-1])}{' and ' if len(ls) > 1 else ''}{LGA[ls[-1]]} LGA{'s' if len(ls) > 1 else ''}."
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for slug, (name, langs, alt, nt, evs, by) in PEOPLE.items():
    srcs = ([("FGBA", f"{name}: named in the state's ethnic profile")] if "FG" in by else []) + [("WPBA", f"{name}: named among the state's peoples and in its language table"), ("ATLAS", f"{name}: their language and names")]
    RECORDS.append(dict(key=slug, table="ethnic_groups", evidence="multiple_sources", level="well_documented",
                        fields=dict(name=name, slug=slug, summary=text(slug).split(". ")[0] + ".", description=text(slug)), srcs=srcs))
    RELATIONS.append(dict(frm=slug, type="present_in", to="@admin_units:state:bauchi", source=srcs[0][0], evidence="multiple_sources", level="well_documented",
                          notes="Named by " + ("the federal government's state profile and Wikipedia" if "FG" in by else "Wikipedia") + "; their language is placed in Bauchi by Blench's Atlas."))
    for lg in langs:
        RELATIONS.append(dict(frm=slug, type="speaks", to=f"@languages:{lg}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                              notes="Their language, per Blench's Atlas."))
    for l, d in evs:
        (ev, lvl), atlas = grade(langs, l)
        RELATIONS.append(dict(frm=slug, type="present_in", to=f"@admin_units:lga:bauchi/{l}", source="WPBA", evidence=ev, level=lvl, settlement_status="unknown", notes=note(d, atlas)))
    for n, t, nt2, sk in alt:
        NAMES.append(dict(record=slug, name=n, name_type=t, usage_notes=nt2 + ".", srcs=[sk]))
for slug, (langs, st_note, st_src, evs) in EXISTING.items():
    RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to="@admin_units:state:bauchi", source=st_src, evidence="single_reliable_source",
                          level="well_documented" if st_src == "FGBA" else "reported", notes=st_note))
    for l, d in evs:
        (ev, lvl), atlas = grade(langs, l)
        RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to=f"@admin_units:lga:bauchi/{l}", source="WPBA", evidence=ev, level=lvl, settlement_status="unknown",
                              notes=note(d, atlas)))
GAPS = [
    ("Bauchi: LGAs with no people linked", "Giade, Itas/Gadau, Jama'are, Katagum, Kirfi, Shira and Warji: Wikipedia's language table either omits them or lists only a language whose people are not named (Bure in Kirfi). The north is largely Hausa and Fulani, but no source read names peoples by these LGAs."),
    ("Bauchi: Jarawa", "Wikipedia places 'the Jarawa in the south-west'; Blench's Atlas notes that 'Jarawa' is a Hausa name used for many language groups. Not recorded as a people; its Jar-cluster members here are Bankal, Gwak, Duguri and Mbat."),
    ("Bauchi: 'Badawa'", "The federal profile's 'Badawa' (Wikipedia 'MBadawa') is read as the Mbat (Atlas 2.C: Badawa, Mbadawa), not the Bade; Wikipedia's language table separately lists Bade in Zaki LGA."),
    ("Bauchi: Kirfawa, Turawa, Boyawa", "Named in Wikipedia's population list with no matching Atlas entry; not recorded."),
    ("Bauchi: 'Sayawa'", "The federal profile and Wikipedia use 'Sayawa'; Blench's Atlas says the Saya terms are now considered derogatory. The people are recorded as Zaar; the existing Bauchi State page text (an earlier batch) still quotes 'Sayawa' — owner decision pending."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Bauchi (Phase 3): {len(PEOPLE)} peoples (federal state profile, Wikipedia, linked to their languages through Blench's Atlas), LGA links; links for {len(EXISTING)} existing peoples.")


def report():
    per = {l: [] for l in LGA}
    for s, p in PEOPLE.items():
        for l, _ in p[4]:
            per[l].append(p[0])
    for s, v in EXISTING.items():
        for l, _ in v[3]:
            per[l].append(s.replace("-", " ").title() + " (existing)")
    lg = [r for r in RELATIONS if r["type"] == "present_in" and "lga:" in r["to"]]
    L = ["# Research batch 075b — Bauchi: the peoples", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         f"- **{len(RECORDS)} new people records:** " + ", ".join(p[0] for p in PEOPLE.values()) + ". Each is linked to Bauchi and to its language.",
         f"- **{len(EXISTING)} existing records get Bauchi links:** Hausa, Fulani, Kanuri, Karekare, Bolewa, Ngamo, Bade, Shuwa Arabs and Ngas (Angas).",
         f"- **{len(lg)} people–LGA links covering {sum(1 for v in per.values() if v)} of the 20 LGAs:** {sum(1 for r in lg if r['level'] == 'well_documented')} *well documented* and {sum(1 for r in lg if r['level'] == 'reported')} *reported*.",
         "- **Sources:**",
         "  - the **federal government's Bauchi profile** (official). It names 9 of the state's 55 groups.",
         "  - **Wikipedia:** peoples by region, its population list, and its table of languages by LGA",
         "  - the Atlas, which ties each name to its language:",
         "    - Butawa = Gamo",
         "    - Jaku = Labɨr",
         "    - Fa'awa = Pa'a",
         "    - Dugurawa, Gwak, Bankal and Mbat (Badawa) = members of the Jar cluster",
         "    - Zulawa = Zul (Polci cluster)",
         "- **Sensitive name:** the people are recorded as **Zaar**. 'Sayawa' is used by the sources but marked derogatory by the Atlas, so it is not recorded.",
         "- **'Badawa'** is read as the Mbat, per the Atlas, not the Bade. This is noted as a gap.",
         "- **Not created:**",
         "  - Jarawa: a Hausa cover name",
         "  - Kirfawa, Turawa and Boyawa: no Atlas match",
         "- **Seven LGAs have no people linked:** Giade, Itas/Gadau, Jama'are, Katagum, Kirfi, Shira and Warji. This is listed as a gap.", "",
         "## People per LGA", "", "| LGA | Peoples |", "|---|---|"]
    for l, ps in per.items():
        L.append(f"| {LGA[l]} | {', '.join(ps) or '—'} |")
    L += ["", "## The people records", ""] + [f"**{PEOPLE[s][0]}.** {text(s)}\n" for s in PEOPLE] + ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_075b_bauchi_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_075b_bauchi_peoples_REVIEW.md", "w").write(report())
    lg = [r for r in RELATIONS if r["type"] == "present_in" and "lga:" in r["to"]]
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} (people-LGA={len(lg)}, well={sum(1 for r in lg if r['level'] == 'well_documented')}) names={len(NAMES)}")
