"""
Research batch 081b — Gombe (Phase 3): the peoples of Gombe State and the LGAs where they live, as far as the sources
allow. Researched 2026-10-02. Pattern: batches 063b, 069b and 075b.

Sources:
  * Federal Government of Nigeria, state profile 'Gombe' (nigeria.gov.ng/states/gombe; copy in
    data/fg_gombe_2026-10-02.html) — Tier 1: 'Tangale, Terawa, Waja, Kumo, Fulani, Kanuri, Bolewa, Jukun, Pero/Shonge,
    Tula, Cham, Lunguda, Dadiya, Banbuka, Hausa and Kamo/Awak among others'.
  * Wikipedia, 'Gombe State': introduction (Fulani in the north and centre, Tangale in the south and centre; Cham,
    Dadiya, Jara, Kamo, Pero, Tangale, Tera, Lunguda and Waja in the east and south), 'Demographics' (Fulani in Dukku,
    Funakaye, Nafada, Akko, Kwami and Gombe LGAs; Tangale in Billiri and Kaltungo; also Hausa, Tula, Longuda, Dadiya,
    Waja, Bangunji, Filiya, Awak, Tera (Yamaltu-Deba), Bolewa and Kanuri) and its languages-by-LGA table (Ethnologue 22).
  * Blench's Atlas (batch 081) for the people–language ties and the people's own names (field 1.C).

Rules (as 075b): a people record only for a group named by the federal profile or Wikipedia whose language is
recorded; LGA links from Wikipedia (table or the 'Demographics' LGA lists) — well documented where the Atlas also
places the language there, reported otherwise; presence only.
Not created: Kumo (a town, Akko's headquarters), Banbuka (no Atlas match read), and peoples of languages named only in
the table (Kushi/Goji, Loo, Moo, Kyak, Dera, Dikaka, Dza, Yuwar, Wurkun).
Cham: the federal profile and Wikipedia say Cham; the Atlas gives Cham as a location name of the Dijim, whose language
(Dijim–Bwilim) it places at Cham town in Balanga LGA. Wikipedia's table puts Cham in Kaltungo — linked as reported,
and the Balanga placement stated in the text.
Bangunji: Wikipedia's table puts Bangwinji in Balanga; the Atlas places Bangjinge in Shongom. Balanga linked as
reported; the Atlas placement stated in the text.
"""
import json, sys
import batch_081_gombe_languages as L81

ACCESSED = "2026-10-02"
SOURCES = {
    "FGGO": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Gombe State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/gombe/", verification_status="verified",
                 notes="'Ethnic Profile': 'Tangale, Terawa, Waja, Kumo, Fulani, Kanuri, Bolewa, Jukun, Pero/Shonge, Tula, Cham, Lunguda, Dadiya, Banbuka, Hausa and Kamo/Awak among others.' Created 1 October 1996. Read 2026-10-02 (copy in database/research/data/)."),
    "WGOM": L81.SOURCES["WGOM"],
    "ATLAS": L81.SOURCES["ATLAS"],
}
LGA = L81.LGA
ATLAS_LGA = {k: set(v[4]) for k, v in L81.LANG.items()}
for lang, (lgas, *_r) in L81.EXISTING.items():
    ATLAS_LGA[lang] = set(lgas)
E, X, SV = "endonym", "alternative", "spelling_variant"
T, P = "the languages-by-LGA table", "the 'Demographics' section"
# slug: (name, [langs], [(other name, type, note, src)], note, [(lga, how Wikipedia gives it)], named by)
PEOPLE = {
 "tangale": ("Tangale", ["tangale"], [("Táŋlɛ̀", E, "Atlas, Tangale entry, 1.B (name of the language)", "ATLAS")],
             "Wikipedia places the Tangale in the south and centre of the state, and names Billiri and Kaltungo as their LGAs. Blench's Atlas places their language in Kaltungo and Akko LGAs, and its dialects are Ture, Kaltungo, Shongom and Billiri.",
             [("billiri", f"Tangale ({T} and {P})"), ("kaltungo", f"Tangale ({T} and {P})"), ("akko", f"Tangale ({T})"), ("balanga", f"Tangale ({T})"), ("shomgom", f"Tangale ({T})")], "FG, Wikipedia"),
 "waja": ("Waja", ["wiyaa"], [("Wịyáà", E, "Atlas, Wiyaa entry, 1.C (the people)", "ATLAS")],
          "Blench's Atlas calls their language Wiyaa and places it in Balanga and Kaltungo LGAs, in Waja district, and in Bali LGA of Taraba State.",
          [("balanga", f"Waja ({T})")], "FG, Wikipedia"),
 "pero": ("Pero", ["pero"], [("Pìpéerò", E, "Atlas, Pero entry, 1.C (plural, the people)", "ATLAS"), ("Shonge", X, "Federal Government state profile ('Pero/Shonge')", "FGGO"), ("Filiya", X, "Wikipedia; Atlas, 2.A (town name)", "WGOM")],
          "Blench's Atlas places their language around Filiya in Shongom LGA, in three main villages, Gwandum, Gundale and Filiya.",
          [("shomgom", f"Pipero ({T})")], "FG, Wikipedia"),
 "tula": ("Tula", ["tula"], [("Kitule", E, "Atlas, Tula entry, 1.C (plural, the people)", "ATLAS"), ("Ture", X, "Atlas, Tula entry, 1.A", "ATLAS")],
          "Blench's Atlas places their language in Kaltungo LGA, in about fifty villages; Tula itself is 30 km east of Billiri.",
          [("kaltungo", f"Tula ({T})")], "FG, Wikipedia"),
 "cham": ("Cham", ["dijim-bwilim"], [("Dìjím", E, "Atlas, Dijim member of Dijim–Bwilim, 1.C (plural, the people)", "ATLAS"), ("Cam", SV, "Atlas, Dijim member, 2.A", "ATLAS")],
          "Blench's Atlas gives Cham as a name of the Dijim, whose language, Dijim–Bwilim, it places at Cham town in Balanga LGA and in Lamurde LGA of Adamawa State; the related Bwilim are also called Mwana.",
          [("kaltungo", f"Cham ({T})")], "FG, Wikipedia"),
 "dadiya": ("Dadiya", ["dadiya"], [("Nyíyò Daddiya", E, "Atlas, Dadiya entry, 1.C (the people)", "ATLAS")],
            "Blench's Atlas places their language in Balanga LGA, between Dadiya and Bambam, and in neighbouring parts of Taraba and Adamawa states.",
            [("balanga", f"Dadiya ({T})")], "FG, Wikipedia"),
 "kamo": ("Kamo", ["ma"], [("Ma", E, "Atlas, Ma entry, 1.B and 1.C ('nyii Ma')", "ATLAS"), ("Kamu", SV, "Atlas, Ma entry, 2.A", "ATLAS")],
          "The federal profile names them together with the Awak ('Kamo/Awak'). Blench's Atlas calls their language Ma and places it in Kaltungo and Akko LGAs.",
          [("kaltungo", f"Kamo ({T})")], "FG, Wikipedia"),
 "awak": ("Awak", ["yebu"], [("Nìín Yěbù", E, "Atlas, Yebu entry, 1.C (the people)", "ATLAS"), ("Awok", SV, "Atlas, Yebu entry, 2.A", "ATLAS")],
          "The federal profile names them together with the Kamo ('Kamo/Awak'). Blench's Atlas calls their language Yebu and places it 10 km north-east of Kaltungo.",
          [("kaltungo", f"Awak ({T})")], "FG, Wikipedia"),
 "bangunji": ("Bangunji", ["bangjinge"], [("Bangwinji", SV, "Wikipedia's table; Atlas, 1.A", "WGOM"), ("Bánjòŋ", E, "Atlas, Bangjinge entry, 1.C ('nyii Bánjòŋ')", "ATLAS")],
              "Blench's Atlas places their language, Bangjinge, in Shongom LGA, in about 25 villages; Wikipedia's table lists it under Balanga.",
              [("balanga", f"Bangwinji ({T})")], "Wikipedia"),
 "jara": ("Jara", ["jara"], [("Jera", SV, "Atlas, Jara entry, 1.A", "ATLAS")],
          "Wikipedia places them in the east and south of the state. Blench's Atlas places their language in Akko LGA (given as 'Bauchi State, Ako LGA', before Gombe was created) and in Biu LGA of Borno State.",
          [], "Wikipedia"),
}
EXISTING = {  # slug: (langs, state note, state source, [(lga, detail)])
 "fulani": (["fulfulde"], "Named by the federal profile; Wikipedia calls them the largest group, in the north and centre of the state.", "FGGO",
            [("akko", f"Fulani ({T} and {P})"), ("dukku", f"Fulani ({T} and {P})"), ("funakaye", f"Fulani ({T} and {P})"), ("kwami", f"Fulani ({T} and {P})"),
             ("nafada", f"Fulani ({T} and {P})"), ("yamaltu-deba", f"Fulani ({T})"), ("gombe", f"Fulani ({P})")]),
 "hausa": ([], "Named by the federal profile and by Wikipedia among the state's peoples.", "FGGO", []),
 "kanuri": (["kanuri"], "Named by the federal profile and by Wikipedia.", "FGGO", [("kwami", f"Kanuri ({T})")]),
 "bolewa": (["bole"], "Named by the federal profile and by Wikipedia.", "FGGO", [("dukku", f"Bolewa ({T})"), ("nafada", f"Bolewa ({T})")]),
 "jukun": ([], "Named by the federal profile ('Jukun').", "FGGO", [("akko", f"Jukun ({T})")]),
 "tera": (["tera"], "Named by the federal profile ('Terawa') and by Wikipedia ('Tera (Yamaltu-Deba)').", "FGGO", [("yamaltu-deba", f"Tera ({T} and {P})")]),
 "lunguda": (["longuda"], "Named by the federal profile ('Lunguda') and by Wikipedia ('Longuda (Lunguda)').", "FGGO", [("balanga", f"Longuda ({T})")]),
 "tsobo": (["tsobo"], "Wikipedia's languages-by-LGA table lists Tso in Balanga LGA.", "WGOM", [("balanga", f"Tso ({T})")]),
}


def grade(langs, lga):
    atlas = any(lga in ATLAS_LGA.get(l, ()) for l in langs)
    return (("multiple_sources", "well_documented") if atlas else ("single_reliable_source", "reported")), atlas


def note(detail, atlas):
    return f"Wikipedia lists {detail} for this LGA." + (" Blench's Atlas places their language here." if atlas else "") + " Presence only; the nature of their presence is not established."


def text(slug):
    name, langs, alt, nt, evs, by = PEOPLE[slug]
    who = {"FG, Wikipedia": ", named among the state's ethnic groups by the federal government's state profile and by Wikipedia",
           "Wikipedia": ", named among the state's ethnic groups by Wikipedia"}[by]
    t = f"The {name} are a people of Gombe State{who}."
    names = [n for n, _, _, _ in alt]
    if names: t += f" Other names include {', '.join(names)}."
    t += " " + nt
    ls = [l for l, _ in evs]
    if ls:
        t += f" Wikipedia places them in {', '.join(LGA[l] for l in ls[:-1])}{' and ' if len(ls) > 1 else ''}{LGA[ls[-1]]} LGA{'s' if len(ls) > 1 else ''}."
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for slug, (name, langs, alt, nt, evs, by) in PEOPLE.items():
    srcs = ([("FGGO", f"{name}: named in the state's ethnic profile")] if "FG" in by else []) + [("WGOM", f"{name}: named among the state's peoples"), ("ATLAS", f"{name}: their language and names")]
    RECORDS.append(dict(key=slug, table="ethnic_groups", evidence="multiple_sources", level="well_documented",
                        fields=dict(name=name, slug=slug, summary=text(slug).split(". ")[0] + ".", description=text(slug)), srcs=srcs))
    RELATIONS.append(dict(frm=slug, type="present_in", to="@admin_units:state:gombe", source=srcs[0][0], evidence="multiple_sources", level="well_documented",
                          notes="Named by " + ("the federal government's state profile and Wikipedia" if "FG" in by else "Wikipedia") + "; their language is placed in Gombe by Blench's Atlas."))
    for lg in langs:
        RELATIONS.append(dict(frm=slug, type="speaks", to=f"@languages:{lg}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                              notes="Their language, per Blench's Atlas."))
    for l, d in evs:
        (ev, lvl), atlas = grade(langs, l)
        RELATIONS.append(dict(frm=slug, type="present_in", to=f"@admin_units:lga:gombe/{l}", source="WGOM", evidence=ev, level=lvl, settlement_status="unknown", notes=note(d, atlas)))
    for n, t, nt2, sk in alt:
        NAMES.append(dict(record=slug, name=n, name_type=t, usage_notes=nt2 + ".", srcs=[sk]))
for slug, (langs, st_note, st_src, evs) in EXISTING.items():
    RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to="@admin_units:state:gombe", source=st_src, evidence="single_reliable_source",
                          level="well_documented" if st_src == "FGGO" else "reported", notes=st_note))
    for l, d in evs:
        (ev, lvl), atlas = grade(langs, l)
        RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to=f"@admin_units:lga:gombe/{l}", source="WGOM", evidence=ev, level=lvl, settlement_status="unknown",
                              notes=note(d, atlas)))
GAPS = [
    ("Gombe: peoples of table-only languages", "Wikipedia's table lists Kushi (Goji), Loo, Moo, Kyak, Dera, Dikaka, Dza, Yuwar and Wurkun as languages of Balanga, Kaltungo and Shongom; their peoples are not named by the federal profile or Wikipedia, so no records were created."),
    ("Gombe: Kumo and Banbuka", "The federal profile names 'Kumo' (a town, Akko's headquarters) and 'Banbuka' among the ethnic groups; no matching Atlas entry was read. Not recorded."),
    ("Gombe: Cham and Bangunji placement", "Wikipedia's table puts Cham in Kaltungo (the Atlas: Cham town, Balanga) and Bangwinji in Balanga (the Atlas: Shongom). Linked as Wikipedia gives them, reported; the Atlas placements are stated in the records."),
    ("Gombe: Jara", "Named by Wikipedia without an LGA; the Atlas places the language in Akko ('Ako LGA'). No LGA link."),
    ("Gombe: Hausa", "Named by the federal profile and Wikipedia, but no source read places them by LGA."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Gombe (Phase 3): {len(PEOPLE)} peoples (federal state profile, Wikipedia, linked to their languages through Blench's Atlas), LGA links; links for {len(EXISTING)} existing peoples.")


def report():
    per = {l: [] for l in LGA}
    for s, p in PEOPLE.items():
        for l, _ in p[4]:
            per[l].append(p[0])
    for s, v in EXISTING.items():
        for l, _ in v[3]:
            per[l].append(s.replace("-", " ").title() + " (existing)")
    lg = [r for r in RELATIONS if r["type"] == "present_in" and "lga:" in r["to"]]
    L = ["# Research batch 081b — Gombe: the peoples", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         f"- **{len(RECORDS)} new people records:** " + ", ".join(p[0] for p in PEOPLE.values()) + ". Each is linked to Gombe and to its language.",
         f"- **{len(EXISTING)} existing records get Gombe links:** Fulani, Hausa, Kanuri, Bolewa, Jukun, Tera, Lunguda and Tsobo.",
         f"- **{len(lg)} people–LGA links covering {sum(1 for v in per.values() if v)} of the 11 LGAs:** {sum(1 for r in lg if r['level'] == 'well_documented')} *well documented* (the Atlas agrees) and {sum(1 for r in lg if r['level'] == 'reported')} *reported*.",
         "- **Sources:**",
         "  - the **federal government's Gombe profile** (official). It names 16 groups.",
         "  - **Wikipedia:** peoples by region, Fulani and Tangale LGAs, and its languages-by-LGA table (from Ethnologue 22)",
         "  - the Atlas, which ties each name to its language and gives the people's own name: Waja = Wiyaa, Kamo = Ma, Awak = Yebu, Cham = Dijim, Bangunji = Bangjinge",
         "- **Where the sources disagree:** Wikipedia's table puts Cham in Kaltungo (the Atlas: Cham town, Balanga) and Bangwinji in Balanga (the Atlas: Shongom). They are linked as Wikipedia gives them, *reported*, with the Atlas placement in the text.",
         "- **Not created:** Kumo (a town), Banbuka (no Atlas match), and the peoples of languages named only in the table.", "",
         "## People per LGA", "", "| LGA | Peoples |", "|---|---|"]
    for l, ps in per.items():
        L.append(f"| {LGA[l]} | {', '.join(ps) or '—'} |")
    L += ["", "## The people records", ""] + [f"**{PEOPLE[s][0]}.** {text(s)}\n" for s in PEOPLE] + ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_081b_gombe_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_081b_gombe_peoples_REVIEW.md", "w").write(report())
    lg = [r for r in RELATIONS if r["type"] == "present_in" and "lga:" in r["to"]]
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} (people-LGA={len(lg)}, well={sum(1 for r in lg if r['level'] == 'well_documented')}) names={len(NAMES)}")
