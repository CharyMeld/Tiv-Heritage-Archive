"""
Research batch 069b — Yobe (Phase 3): the peoples of Yobe State and the LGAs where they live, as far as the sources
allow. Researched 2026-10-01. Pattern: batch 063b (Borno).

Sources:
  * Federal Government of Nigeria, state profile 'Yobe' (nigeria.gov.ng/states/yobe; copy in
    data/fg_yobe_2026-10-01.html), 'Ethnic Profile' — Tier 1: 'The major ethnic groups in the State include Kanuri,
    Fulani, Kare-Kare, Bolewa, Ngizim, Bade, Hausa, Ngamo and Shuwa.'
  * Wikipedia, 'Yobe State': 'Ethnic groups' ('the Kanuri and Karai-karai, Fulani, while other ethnic communities
    include Bolewa, Ngizim, Bade, Ngamo, Shuwa, Bura, Marghi, Hausa and Manga') and its table 'Languages of Yobe State
    listed by LGA' (16 of the 17 LGAs; Tarmua missing).
  * Blench's Atlas (batch 069) for the languages and the peoples' own names.

Rules (as 057b/063b):
  * A people record only for a group named by the federal profile or Wikipedia AND whose language the Atlas places
    in Yobe or Borno. New: Karekare, Bolewa, Ngizim, Bade, Ngamo, Manga (Wikipedia; the Atlas names Manga as a Kanuri
    dialect, so the Manga are linked as speaking Kanuri). Not created: Ɗuwai and Maaka (only their languages are named
    in Wikipedia's table), Kutto (not named).
  * LGA links (presence only, settlement status unknown): Wikipedia's table AND the Atlas placing the language there =
    well documented; Wikipedia's table alone = reported. The federal profile names no LGAs.
  * Existing records get Yobe links: Kanuri, Fulani, Hausa, Shuwa Arabs (federal profile), Bura and Marghi (Wikipedia).
"""
import json, sys
import batch_069_yobe_languages as B69

ACCESSED = "2026-10-01"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "FGY": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Yobe State (state profile)",
                organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/yobe/", verification_status="verified",
                notes="'Ethnic Profile': 'The major ethnic groups in the State include Kanuri, Fulani, Kare-Kare, Bolewa, Ngizim, Bade, Hausa, Ngamo and Shuwa.' Read 2026-10-01 (copy in database/research/data/)."),
    "WPYO": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Yobe State", organisation="Wikipedia", url=W("Yobe State"),
                 verification_status="needs_corroboration", notes="'Ethnic groups' section and the table 'Languages of Yobe State listed by LGA'. Accessed 2026-10-01."),
    "ATLAS": B69.SOURCES["ATLAS"],
}
LGA = B69.LGA
ATLAS_LGA = {k: set(v[4]) for k, v in B69.LANG.items()}
ATLAS_LGA["kanuri"] = {"nguru", "geidam", "damaturu", "fune", "gujba", "fika"}
E, X = "endonym", "alternative"
WT = "WPYO"
# slug: (name, [langs], [(other name, type, note, src)], note, [(lga, detail)], named by)
PEOPLE = {
 "karekare": ("Karekare", ["karekare"], [("Kare-Kare", X, "Federal Government state profile", "FGY"), ("Karai-karai", X, "Wikipedia", "WPYO")],
              "Wikipedia names them, with the Kanuri and Fulani, among the major ethnic groups of the state, and its language table places Karai-karai in more LGAs than any other language except Kanuri. Blench's Atlas places the Karekare language in Fika LGA and in Gamawa and Misau LGAs of Bauchi State.",
              [("damaturu", "Yerwa Kanuri/kare-kare"), ("fika", "Karai-karai"), ("fune", "Karai-karai"), ("gujba", "Karai-karai"), ("gulani", "Karai-karai"), ("jakusko", "Karai-karai"), ("nangere", "Karai-karai"), ("potiskum", "Karai-karai")], "FG, Wikipedia"),
 "bolewa": ("Bolewa", ["bole"], [("Am Pìkkà", E, "Atlas, Bole entry, 1.C", "ATLAS"), ("Ampika", E, "Atlas, Bole entry, 1.C", "ATLAS"), ("Anika", X, "Atlas, Bole entry, 2.C", "ATLAS")],
            "Their language is Bole, which Blench's Atlas places in Fika LGA and in Bauchi State; the Atlas gives Bolewa and Anika as other names of the people, and Fika as a name of the language.",
            [("fika", "Bolewa Ngamo"), ("potiskum", "Bolewa")], "FG, Wikipedia"),
 "ngizim": ("Ngizim", ["ngizim"], [], "Blench's Atlas places the Ngizim language in Damaturu LGA, describes it as vigorous, and notes that the Ngizim also speak Hausa.",
            [("fune", "Ngizim"), ("potiskum", "Ngizim")], "FG, Wikipedia"),
 "bade": ("Bade", ["bade"], [], "Blench's Atlas places the Bade language in Bade LGA and in Hadejia LGA of Jigawa State, with three dialects: Western, Southern and Gashua Bade.",
          [("bade", "Bade"), ("jakusko", "Bade")], "FG, Wikipedia"),
 "ngamo": ("Ngamo", ["ngamo"], [], "Blench's Atlas places the Ngamo language in Fika LGA and in Darazo and Dukku LGAs.", [("fika", "Bolewa Ngamo")], "FG, Wikipedia"),
 "manga": ("Manga", ["kanuri"], [], "Wikipedia's language table gives Manga as the language of Machina LGA; Blench's Atlas names Manga as a dialect of Kanuri, in which a Bible translation was in progress.",
           [("machina", "Manga")], "Wikipedia"),
}
EXISTING = {  # slug: (langs, state note, state source, [(lga, detail)])
 "kanuri": (["kanuri"], "The federal profile names the Kanuri first among the state's major ethnic groups; Wikipedia likewise.", "FGY",
            [("bade", "Kanuri"), ("bursari", "Kanuri"), ("damaturu", "Yerwa Kanuri"), ("geidam", "Kanuri"), ("gujba", "Kanuri"), ("gulani", "Kanuri"), ("nguru", "Kanuri"),
             ("yunusari", "Kanuri"), ("yusufari", "Kanuri"), ("karasuwa", "Kanuri")]),
 "fulani": ([], "Named among the major ethnic groups by the federal profile and Wikipedia.", "FGY", [("geidam", "Fulani"), ("jakusko", "Fulani")]),
 "hausa": ([], "Named among the major ethnic groups by the federal profile; Wikipedia lists the Hausa among other communities.", "FGY", []),
 "shuwa-arabs": ([], "Named by the federal profile ('Shuwa') and Wikipedia; Blench's Atlas notes that the Shuwa range across Borno and Yobe on transhumance.", "FGY", []),
 "bura": ([], "Wikipedia lists the Bura among the state's ethnic communities; its table places Bura-Pabir in Fune and Gulani.", "WPYO", [("fune", "Bura-Pabir"), ("gulani", "Bura-Pabir")]),
 "marghi": ([], "Wikipedia lists the Marghi among the state's ethnic communities; no LGA is named.", "WPYO", []),
}


def grade(langs, lga):
    atlas = any(lga in ATLAS_LGA.get(l, ()) for l in langs)
    return ("multiple_sources", "well_documented") if atlas else ("single_reliable_source", "reported")


def note(detail, atlas):
    return f"Wikipedia's language table lists {detail} in this LGA." + (" Blench's Atlas places their language here." if atlas else "") + " Presence only; the nature of their presence is not established."


def text(slug):
    name, langs, alt, nt, evs, by = PEOPLE[slug]
    who = {"FG, Wikipedia": ", named among the state's major ethnic groups by the federal government's state profile and by Wikipedia",
           "Wikipedia": ", named among the state's ethnic communities by Wikipedia"}[by]
    t = f"The {name} are a people of Yobe State{who}."
    names = [n for n, _, _, _ in alt]
    if names: t += f" Other names include {', '.join(names)}."
    t += " " + nt
    ls = [l for l, _ in evs]
    if ls:
        t += f" Wikipedia's language table places them in {', '.join(LGA[l] for l in ls[:-1])}{' and ' if len(ls) > 1 else ''}{LGA[ls[-1]]} LGA{'s' if len(ls) > 1 else ''}."
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for slug, (name, langs, alt, nt, evs, by) in PEOPLE.items():
    srcs = ([("FGY", f"{name}: named in the state's ethnic profile")] if "FG" in by else []) + [("WPYO", f"{name}: named among the state's peoples and in its language table"), ("ATLAS", f"{name}: their language")]
    RECORDS.append(dict(key=slug, table="ethnic_groups", evidence="multiple_sources", level="well_documented",
                        fields=dict(name=name, slug=slug, summary=text(slug).split(". ")[0] + ".", description=text(slug)), srcs=srcs))
    RELATIONS.append(dict(frm=slug, type="present_in", to="@admin_units:state:yobe", source=srcs[0][0], evidence="multiple_sources", level="well_documented",
                          notes="Named by " + ("the federal government's state profile and Wikipedia" if "FG" in by else "Wikipedia") + "; their language is placed in Yobe by Blench's Atlas."))
    for lg in langs:
        RELATIONS.append(dict(frm=slug, type="speaks", to=f"@languages:{lg}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                              notes="Their language, per Blench's Atlas." if slug != "manga" else "Blench's Atlas names Manga as a dialect of Kanuri."))
    for l, d in evs:
        ev, lvl = grade(langs, l)
        RELATIONS.append(dict(frm=slug, type="present_in", to=f"@admin_units:lga:yobe/{l}", source=WT, evidence=ev, level=lvl, settlement_status="unknown",
                              notes=note(d, any(l in ATLAS_LGA.get(x, ()) for x in langs))))
    for n, t, nt2, sk in alt:
        NAMES.append(dict(record=slug, name=n, name_type=t, usage_notes=nt2 + ".", srcs=[sk]))
for slug, (langs, st_note, st_src, evs) in EXISTING.items():
    RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to="@admin_units:state:yobe", source=st_src, evidence="single_reliable_source",
                          level="well_documented" if st_src == "FGY" else "reported", notes=st_note))
    for l, d in evs:
        ev, lvl = grade(langs, l)
        RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to=f"@admin_units:lga:yobe/{l}", source=WT, evidence=ev, level=lvl, settlement_status="unknown",
                              notes=note(d, any(l in ATLAS_LGA.get(x, ()) for x in langs))))
GAPS = [
    ("Yobe: Tarmua", "Wikipedia's language table omits Tarmua LGA, and no other source read names its peoples."),
    ("Yobe: Ɗuwai, Maaka and Kutto peoples", "Their languages are recorded (batch 069), and Wikipedia's table lists Duwai (Bade LGA) and Maaka (Gulani LGA), but no source read names them as peoples; no people records created."),
    ("Yobe: Manga and Kanuri", "Wikipedia lists the Manga as a separate ethnic community; Blench's Atlas names Manga as a dialect of Kanuri. Recorded as a people speaking Kanuri; their relation to the Kanuri is not stated."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Yobe (Phase 3): {len(PEOPLE)} peoples (federal state profile, Wikipedia, checked against Blench's Atlas), LGA links, language links; links for {len(EXISTING)} existing peoples.")


def report():
    per = {l: [] for l in LGA}
    for s, p in PEOPLE.items():
        for l, _ in p[4]:
            per[l].append(p[0])
    for s, v in EXISTING.items():
        for l, _ in v[3]:
            per[l].append(s.replace("-", " ").title() + " (existing)")
    lg = [r for r in RELATIONS if r["type"] == "present_in" and "lga:" in r["to"]]
    L = ["# Research batch 069b — Yobe: the peoples", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         f"- **{len(RECORDS)} new people records:** " + ", ".join(p[0] for p in PEOPLE.values()) + ". Each is linked to Yobe and to its language.",
         f"- **{len(EXISTING)} existing records get Yobe links:** Kanuri, Fulani, Hausa, Shuwa Arabs, Bura and Marghi.",
         f"- **{len(lg)} people–LGA links covering {sum(1 for v in per.values() if v)} of the 17 LGAs:** {sum(1 for r in lg if r['level'] == 'well_documented')} *well documented* and {sum(1 for r in lg if r['level'] == 'reported')} *reported*. Only **Tarmua** has no people linked, because Wikipedia's table skips it.",
         "- **Sources:**",
         "  - the **federal government's Yobe profile** (official). It names the nine major groups but no LGAs.",
         "  - **Wikipedia's Yobe State article**: its ethnic groups and its languages-by-LGA table",
         "  - each record is checked against Blench's Atlas",
         "- **Manga:** Wikipedia calls them a separate community; the Atlas calls Manga a Kanuri dialect. Recorded as a people who speak Kanuri, with both views noted.",
         "- **Not created:** Ɗuwai, Maaka and Kutto, because only their languages are named.", "",
         "## People per LGA", "", "| LGA | Peoples |", "|---|---|"]
    for l, ps in per.items():
        L.append(f"| {LGA[l]} | {', '.join(ps) or '—'} |")
    L += ["", "## The people records", ""] + [f"**{PEOPLE[s][0]}.** {text(s)}\n" for s in PEOPLE] + ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_069b_yobe_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_069b_yobe_peoples_REVIEW.md", "w").write(report())
    lg = [r for r in RELATIONS if r["type"] == "present_in" and "lga:" in r["to"]]
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} (people-LGA={len(lg)}, well={sum(1 for r in lg if r['level'] == 'well_documented')}) names={len(NAMES)}")
