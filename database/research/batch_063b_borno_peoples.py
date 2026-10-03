"""
Research batch 063b — Borno (Phase 3): the peoples of Borno State and the LGAs where they live, as far as the
sources allow. Researched 2026-10-01. Pattern: batch 057b (Adamawa).

Sources:
  * Federal Government of Nigeria, state profile 'Borno' (nigeria.gov.ng/states/borno; copy in
    data/fg_borno_2026-10-01.html), 'Ethnic Profile' — Tier 1. Kanuri 'about three quarters of the population';
    'Babur, Bura, Shuwa, Marghi, Fulani, Hausa, Gamergu, or Kanakuru, Chibok, Ngoshe, Guduf, Mandara, Tera and
    several other smaller groups are found in Biu, Hawul, KwayaKusar, Bayo and Shani LGAs'; 'The Chibok ...
    inhabit the newly created Chibok LGA'; 'The Hausas are mainly in Askira and Maiduguri.'
  * Wikipedia, 'Borno State': introduction (Dghwede, Glavda, Guduf, Laamang, Mafa and Mandara in the centre;
    Afade, Yedina (Buduma) and Kanembu in the extreme north-east; Waja in the extreme south; Kyibaku, Kamwe, Kilba
    and Margi in the south; Kanuri and Shuwa Arabs in the north and centre), History ('Lapang, Babur/Bura, Mafa
    and Marghi' in the south), and its table of languages by LGA (16 LGAs).
  * Wikipedia LGA articles: Guzamala ('The Kanuri ethnic group lives in the LGA'), Jere ('Most of the people in
    Jere are from the Arabic tribes Baggara and Kanuri'), Nganzai ('up to 90% Kanuris').
  * Blench's Atlas (batch 063) for the languages and the peoples' own names (fields 1.C, 2.C).

Rules (as 057b):
  * A people record only for a group named by the federal profile or Wikipedia AND whose language the Atlas
    places in Borno (batch 063 records or existing ones). Not created: Waja (the Atlas places Waja in Gombe
    only), Pabir (named only by the Atlas — 'two peoples with one language: the Bura and the Pabir'), Ngoshe
    (a place name used for Glavda and Gvoko varieties — ambiguous), Putai, Hdi, Gvoko, Cinene and Teda peoples
    (only their languages are named). 'Lapang' (Wikipedia, History) is probably the Laamang, not assumed.
  * LGA links are presence only (settlement status unknown):
      - federal profile naming the LGA explicitly: well documented (official);
      - Wikipedia's table or an LGA article AND the Atlas placing the language there: well documented;
      - the federal profile's collective list of five LGAs AND the Atlas: reported;
      - Wikipedia alone, or the collective list alone: reported.
  * Wikipedia's table lists Mafa in Bama, Konduga, Maiduguri and Monguno besides Gwoza; the Atlas places Mafa
    only in Gwoza (and mainly Cameroon), and the entries may confuse the Mafa language with Mafa LGA — only Gwoza
    is linked (gap). 'Marghi Mandara' (Gwoza) is ambiguous and not used.
  * Existing records get Borno links: Kanuri, Hausa, Fulani, Marghi, Gude, Dera (Kanakuru), Kamwe, Kilba, Hwana,
    Ga'anda, Sukur.
"""
import json, sys
import batch_063_borno_languages as B63

ACCESSED = "2026-10-01"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "FGB": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Borno State (state profile)",
                organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/borno/", verification_status="verified",
                notes="'Ethnic Profile' section, read 2026-10-01 (copy in database/research/data/fg_borno_2026-10-01.html)."),
    "WPBO": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Borno State", organisation="Wikipedia", url=W("Borno State"),
                 verification_status="needs_corroboration", notes="Introduction (ethnic groups by region), History, and table 'Languages of Borno State listed by Local Government Area'. Accessed 2026-10-01."),
    "WGUZ": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Guzamala", organisation="Wikipedia", url=W("Guzamala"),
                 verification_status="needs_corroboration", notes="'The Kanuri ethnic group lives in the LGA.' Accessed 2026-10-01."),
    "WJERE": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Jere, Nigeria", organisation="Wikipedia", url=W("Jere, Nigeria"),
                  verification_status="needs_corroboration", notes="'Most of the people in Jere are from the Arabic tribes Baggara and Kanuri.' Accessed 2026-10-01."),
    "WNGZ": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Nganzai", organisation="Wikipedia", url=W("Nganzai"),
                 verification_status="needs_corroboration", notes="'up to 90% Kanuris and 10% others.' Accessed 2026-10-01."),
    "ATLAS": B63.SOURCES["ATLAS"],
}
LGA = B63.LGA
ATLAS_LGA = {k: set(v[4]) for k, v in B63.LANG.items()}
for lang, lgas, _ in B63.EXISTING:
    ATLAS_LGA[lang] = set(lgas)
E, X = "endonym", "alternative"
FG, FGC, WT, WI = "FGB", "FGC", "WPBO", "WI"  # FGC = the federal profile's collective five-LGA list; WI = Wikipedia intro/history (state only)
WL = {"guzamala": "WGUZ", "jere": "WJERE", "nganzai": "WNGZ"}
FIVE = ["biu", "hawul", "kwaya-kusar", "bayo", "shani"]
# slug: (name, [language slugs], [(other name, type, source note, source key)], note, [(lga, source, detail)], named by)
PEOPLE = {
 "shuwa-arabs": ("Shuwa Arabs", ["shuwa-arabic"], [("Shuwa", X, "Federal Government state profile", "FGB")],
                 "Wikipedia describes them as living throughout the north and centre of the state, alongside the Kanuri, and as mainly descendants of Arab peoples. Their language is Shuwa Arabic; Blench's Atlas notes that they range widely across Borno and Yobe on transhumance, that the name Shuwa is regarded as pejorative, at least in Chad, and that the Boko Haram insurgency drove many to leave Nigeria.",
                 [("bama", WT, "Shuwa Arabic"), ("dikwa", WT, "Shuwa Arabic"), ("konduga", WT, "Shuwa Arabic"), ("ngala", WT, "Shuwa Arabic"), ("kala-balge", WT, "Shuwa Arabic")], "FG, Wikipedia"),
 "bura": ("Bura", ["bura-pabir"], [("Babur", X, "Wikipedia ('Babur/Bura'); the federal profile lists Babur and Bura separately", "WPBO")],
          "Blench's Atlas describes the Bura–Pabir language as one language of two peoples, the Bura and the Pabir, with two dialects, Bura Pela (Hill Bura) and Bura Hyil Hawul (Plains Bura). The federal profile names both 'Babur' and 'Bura' among the peoples of the southern LGAs; how 'Babur' relates to the Pabir is not stated.",
          [("biu", WT, "Bura-Pabir"), ("hawul", WT, "Bura"), ("kwaya-kusar", WT, "Bura")], "FG, Wikipedia"),
 "kibaku": ("Kibaku", ["cibak"], [("Chibok", X, "Federal Government state profile", "FGB"), ("Kyibaku", X, "Wikipedia", "WPBO"), ("Cíbɔ̀k", E, "Atlas, Cibak entry, 1.C", "ATLAS"), ("Kikuk", E, "Atlas, Cibak entry, 1.C", "ATLAS")],
            "The federal profile calls them the Chibok and says they inhabit Chibok LGA. Their language, Cibak (Kibaku), is placed by Blench's Atlas south of Damboa town.",
            [("chibok", FG, "'The Chibok ... inhabit the newly created Chibok LGA'"), ("chibok", WT, "Kibaku"), ("damboa", WT, "Kibaku"), ("askira-uba", WT, "Kibaku")], "FG, Wikipedia"),
 "mandara": ("Mandara", ["wandala"], [("Wandala", E, "Atlas, Wandala entry, 1.C", "ATLAS")],
             "Wikipedia names them among the peoples of the centre of the state, and the federal profile among its smaller groups. Their language, Wandala, is a vehicular language in this part of Nigeria and Cameroon, according to Blench's Atlas.",
             [("bama", WT, "Wandala"), ("gwoza", WT, "Wandala"), ("konduga", WT, "'Wanda'")], "FG, Wikipedia"),
 "malgwa": ("Malgwa", ["wandala"], [("Gamergu", X, "Federal Government state profile; Atlas, Malgwa, 2.C", "FGB"), ("Məlgwa", E, "Atlas, Malgwa, 1.C", "ATLAS")],
            "The federal profile names them as the Gamergu. Blench's Atlas gives Məlgwa as their own name and Gamergu (Gamargu, Malgo) as other names, and places Malgwa, a member of the Wandala cluster, in Damboa, Gwoza and Konduga LGAs.",
            [("damboa", WT, "'Mulgwai'")], "FG"),
 "buduma": ("Buduma", ["yedina"], [("Yedina", X, "Wikipedia ('Yedina (Buduma)'); Atlas name of the language", "WPBO")],
            "Wikipedia places them in the extreme north-east of the state. Blench's Atlas places their language, Yedina, on the islands of Lake Chad, spoken mostly in Chad.", [], "Wikipedia"),
 "kanembu": ("Kanembu", ["kanuri"], [],
             "Wikipedia places them in the extreme north-east of the state. Blench's Atlas describes them as a separate people speaking Kanuri, placed in the Borno LGAs on the edge of Lake Chad, and also in Niger, Cameroon and Chad.", [], "Wikipedia"),
 "afade": ("Afade", ["afade"], [],
           "Wikipedia places them in the extreme north-east of the state. Blench's Atlas places their language, Afaɗə (also called Kotoko), in Ngala LGA and in Cameroon.",
           [("kala-balge", WT, "Afade")], "Wikipedia"),
 "dghwede": ("Dghwede", ["dghwede"], [], "Wikipedia names them among the peoples of the centre of the state.", [("gwoza", WT, "Dghwede")], "Wikipedia"),
 "glavda": ("Glavda", ["glavda"], [], "Wikipedia names them among the peoples of the centre of the state; their language is also spoken in Cameroon.", [("gwoza", WT, "Glavda")], "Wikipedia"),
 "guduf": ("Guduf", ["guduf-cikide"], [("Kədupaxa", E, "Atlas, Guduf and Gava, 1.C", "ATLAS")],
           "Wikipedia names them among the peoples of the centre of the state, and the federal profile among its smaller groups. Their language belongs to the Guduf–Cikide cluster of the mountains east of Gwoza town.",
           [("gwoza", WT, "Guduf-Gava")], "FG, Wikipedia"),
 "lamang": ("Lamang", ["lamang"], [("Laamang", X, "Wikipedia", "WPBO")], "Wikipedia names them among the peoples of the centre of the state.", [("gwoza", WT, "Lamang")], "Wikipedia"),
 "mafa": ("Mafa", ["mafa"], [("Matakam", X, "Atlas, Mafa entry, 2.C (marked 'not recommended')", "ATLAS")],
          "Wikipedia names them among the peoples of the centre and south of the state. Blench's Atlas places the Mafa language in Gwoza LGA and mainly in Cameroon; despite the name, the Mafa are not placed in Mafa LGA by the Atlas.",
          [("gwoza", WT, "Mafa")], "Wikipedia"),
 "tera": ("Tera", ["tera"], [],
          "The federal profile names them among the smaller peoples of the southern LGAs (Biu, Hawul, Kwaya Kusar, Bayo and Shani). Blench's Atlas places the Tera cluster in Biu and Bayo LGAs, and also in Gombe State.",
          [("biu", FGC, ""), ("bayo", FGC, ""), ("kwaya-kusar", WT, "'Tera'")], "FG"),
}
EXISTING = {  # slug: (langs, state-level note, state source, [(lga, src, detail)])
 "kanuri": (["kanuri"], "The federal profile: the Kanuri are the dominant ethnic group, about three quarters of the state's population, in 'quite a number of LGAs'.", "FGB",
            [("bama", WT, "Yerwa Kanuri"), ("damboa", WT, "Kanuri"), ("gwoza", WT, "Yerwa Kanuri"), ("kaga", WT, "Yerwa Kanuri"), ("kala-balge", WT, "Kanuri"), ("konduga", WT, "Yerwa Kanuri"),
             ("kukawa", WT, "Yerwa Kanuri"), ("maiduguri", WT, "Yerwa Kanuri"), ("monguno", WT, "Yerwa Kanuri"), ("ngala", WT, "Yerwa Kanuri"),
             ("guzamala", "WGUZ", "'The Kanuri ethnic group lives in the LGA'"), ("jere", "WJERE", "'Most of the people in Jere are from the Arabic tribes Baggara and Kanuri'"), ("nganzai", "WNGZ", "'up to 90% Kanuris'")]),
 "hausa": ([], "The federal profile names the Hausa among the smaller groups and says they live 'mainly in Askira and Maiduguri'.", "FGB",
           [("askira-uba", FG, "'The Hausas are mainly in Askira and Maiduguri'"), ("maiduguri", FG, "'The Hausas are mainly in Askira and Maiduguri'")]),
 "fulani": ([], "The federal profile names the Fulani among the smaller groups of the state.", "FGB", []),
 "marghi": (["margi", "margi-south"], "Named by the federal profile and Wikipedia (the Margi in the south).", "FGB",
            [("askira-uba", WT, "Marghi Central, Marghi South, Marghi"), ("damboa", WT, "Marghi Central"), ("bama", WT, "Marghi"), ("chibok", WT, "Marghi"), ("konduga", WT, "Marghi"), ("kwaya-kusar", WT, "Marghi South")]),
 "gude": (["gude"], "Wikipedia's language table lists Gude in Askira/Uba; the Atlas places the Guɗe language there.", "WPBO", [("askira-uba", WT, "Gude")]),
 "dera": (["dera"], "The federal profile names the Kanakuru (Dera) among the smaller groups of the southern LGAs.", "FGB", [("shani", FGC, ""), ("biu", WT, "Dera")]),
 "kamwe": ([], "Wikipedia names the Kamwe among the peoples of the south of the state; the Atlas places the Kamwe language in Adamawa and Cameroon only.", "WPBO", []),
 "kilba": ([], "Wikipedia names the Kilba among the peoples of the south of the state.", "WPBO", [("askira-uba", WT, "'Nya Huba'")]),
 "hwana": ([], "Wikipedia's language table lists Hwana in Hawul.", "WPBO", [("hawul", WT, "Hwana")]),
 "ga-anda": ([], "Wikipedia's language table lists Ga'anda in Biu.", "WPBO", [("biu", WT, "Ga'anda")]),
 "sukur": ([], "Wikipedia's language table lists Sukur in Gwoza.", "WPBO", [("gwoza", WT, "Sukur")]),
}


NOT_ATLAS = {("wandala", "konduga")}  # the Atlas places only the Malgwa member of the Wandala cluster in Konduga, not Wandala proper (Mandara)


def atlas_here(langs, lga, who=None):
    return any(lga in ATLAS_LGA.get(l, ()) and not (who == "mandara" and (l, lga) in NOT_ATLAS) for l in langs)


def grade(langs, lga, evs, who=None):
    srcs = {s for s, _ in evs}
    atlas = atlas_here(langs, lga, who)
    if FG in srcs:
        return ("multiple_sources" if (len(srcs) > 1 or atlas) else "single_reliable_source"), "well_documented", "FGB"
    wiki = srcs & ({WT} | set(WL.values()))
    if wiki:
        s = WT if WT in srcs else next(iter(wiki))
        return ("multiple_sources", "well_documented", s) if atlas else ("single_reliable_source", "reported", s)
    return ("multiple_sources" if atlas else "single_reliable_source"), "reported", "FGB"


def by_lga(evs):
    out = {}
    for l, s, d in evs:
        out.setdefault(l, []).append((s, d))
    return out


def ev_note(evs, atlas):
    parts = []
    for s, d in evs:
        if s == FG: parts.append(f"Federal Government state profile: {d}.")
        elif s == FGC: parts.append("The federal profile places them, with other smaller groups, in Biu, Hawul, Kwaya Kusar, Bayo and Shani LGAs (collectively).")
        elif s == WT: parts.append(f"Wikipedia's language table lists {d} in this LGA.")
        else: parts.append(f"Wikipedia's article on the LGA: {d}.")
    if atlas:
        parts.append("Blench's Atlas places their language here.")
    return " ".join(parts) + " Presence only; the nature of their presence is not established."


def text(slug):
    name, langs, alt, note, evs, by = PEOPLE[slug]
    who = {"FG, Wikipedia": ", named among the state's peoples by the federal government's state profile and by Wikipedia",
           "FG": ", named among the state's peoples by the federal government's state profile", "Wikipedia": ", named among the state's peoples by Wikipedia"}[by]
    t = f"The {name} are a people of Borno State{who}."
    names = [n for n, _, _, _ in alt]
    if names: t += f" Other names include {', '.join(names)}."
    t += " " + note
    ls = list(by_lga(evs))
    if ls:
        t += f" The sources place them in {', '.join(LGA[l] for l in ls[:-1])}{' and ' if len(ls) > 1 else ''}{LGA[ls[-1]]} LGA{'s' if len(ls) > 1 else ''}."
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for slug, (name, langs, alt, note, evs, by) in PEOPLE.items():
    srcs = []
    if "FG" in by or any(s in (FG, FGC) for _, s, _ in evs): srcs.append(("FGB", f"{name}: named in the state's ethnic profile"))
    if "Wikipedia" in by or any(s == WT for _, s, _ in evs): srcs.append(("WPBO", f"{name}: named among the state's peoples or in its language table"))
    srcs.append(("ATLAS", f"{name}: their language in Borno"))
    RECORDS.append(dict(key=slug, table="ethnic_groups", evidence="multiple_sources", level="well_documented",
                        fields=dict(name=name, slug=slug, summary=text(slug).split(". ")[0] + ".", description=text(slug)), srcs=srcs))
    RELATIONS.append(dict(frm=slug, type="present_in", to="@admin_units:state:borno", source=srcs[0][0], evidence="multiple_sources", level="well_documented",
                          notes="Named by " + ("the federal government's state profile" if srcs[0][0] == "FGB" else "Wikipedia") + "; their language is placed in Borno by Blench's Atlas."))
    for lg in langs:
        RELATIONS.append(dict(frm=slug, type="speaks", to=f"@languages:{lg}" if lg not in B63.LANG else f"@languages:{lg}", source="ATLAS", evidence="single_reliable_source",
                              level="well_documented", notes="Their language, per Blench's Atlas."))
    for l, e in by_lga(evs).items():
        ev, lvl, src = grade(langs, l, e, slug)
        atlas = atlas_here(langs, l, slug)
        RELATIONS.append(dict(frm=slug, type="present_in", to=f"@admin_units:lga:borno/{l}", source=src, evidence=ev, level=lvl, settlement_status="unknown", notes=ev_note(e, atlas)))
    for n, t, note, sk in alt:
        NAMES.append(dict(record=slug, name=n, name_type=t, usage_notes=note + ".", srcs=[sk]))

for slug, (langs, st_note, st_src, evs) in EXISTING.items():
    RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to="@admin_units:state:borno", source=st_src,
                          evidence="single_reliable_source", level="well_documented" if st_src == "FGB" else "reported", notes=st_note))
    for l, e in by_lga(evs).items():
        ev, lvl, src = grade(langs, l, e)
        atlas = any(l in ATLAS_LGA.get(x, ()) for x in langs)
        RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to=f"@admin_units:lga:borno/{l}", source=src, evidence=ev, level=lvl,
                              settlement_status="unknown", notes=ev_note(e, atlas)))

GAPS = [
    ("Borno: LGAs with no people linked", "Abadam, Gubio, Mafa, Magumeri, Marte and Mobbar: no source read names their peoples. The federal profile says the Kanuri live in 'quite a number of LGAs', and Wikipedia places the Kanembu in the extreme north-east, but neither names these LGAs. A current source (e.g. the state government's LGA pages) is needed."),
    ("Borno: Mafa outside Gwoza", "Wikipedia's language table lists Mafa in Bama, Konduga, Maiduguri and Monguno; the Atlas places the Mafa language only in Gwoza (mainly Cameroon). The entries may confuse the language with Mafa LGA; only Gwoza is linked."),
    ("Borno: Pabir and Babur", "The Atlas names two peoples, the Bura and the Pabir, speaking Bura–Pabir; Wikipedia writes 'Babur/Bura'; the federal profile lists 'Babur' and 'Bura' separately. A Pabir record and the exact relation of Babur to Pabir need a source."),
    ("Borno: groups not recorded", "Waja (Wikipedia: extreme south; the Atlas places Waja in Gombe only), Ngoshe (federal profile; a place name used for Glavda and Gvoko varieties), Lapang (Wikipedia, History; probably the Laamang), Baggara Arabs (Wikipedia, Jere; the Atlas places Baggara Arabic in Yobe) and the speakers of Putai, Hdi, Gvoko, Cinene, Jilbe and Teda."),
    ("Borno: Hausa in 'Askira'", "The federal profile says 'Askira', linked to Askira/Uba LGA."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Borno (Phase 3): {len(PEOPLE)} peoples (federal state profile, Wikipedia, checked against Blench's Atlas), LGA links, language links; links for {len(EXISTING)} existing peoples.")


def report():
    lga_people = {l: [] for l in LGA}
    for s, p in PEOPLE.items():
        for l in by_lga(p[4]):
            lga_people[l].append(p[0])
    for s, v in EXISTING.items():
        for l in by_lga(v[3]):
            lga_people[l].append({"ga-anda": "Ga'anda"}.get(s, s.title()) + " (existing)")
    lg = [r for r in RELATIONS if r["type"] == "present_in" and "lga:" in r["to"]]
    covered = sum(1 for v in lga_people.values() if v)
    L = ["# Research batch 063b — Borno: the peoples", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         f"- **{len(RECORDS)} new people records:** " + ", ".join(p[0] for p in PEOPLE.values()) + ". Each is linked to Borno and to its language.",
         f"- **{len(EXISTING)} existing people records get Borno links:** Kanuri, Hausa, Fulani, Marghi, Gude, Dera (Kanakuru), Kamwe, Kilba, Hwana, Ga'anda and Sukur.",
         f"- **{len(lg)} people–LGA links covering {covered} of the 27 LGAs:** {sum(1 for r in lg if r['level'] == 'well_documented')} *well documented* and {sum(1 for r in lg if r['level'] == 'reported')} *reported*.",
         "  - **No source names the peoples of six LGAs:** Abadam, Gubio, Mafa, Magumeri, Marte and Mobbar. This is listed as a gap; nothing was guessed.",
         "- **Sources:**",
         "  - the **federal government's state profile** (official). It is the only source that ties LGAs to particular peoples: Chibok in Chibok LGA, and the Hausa in Askira and Maiduguri.",
         "  - **Wikipedia:** the Borno State article (its list of peoples and its table of languages by LGA) and the articles on Guzamala, Jere and Nganzai",
         "  - every record and link is checked against Blench's Atlas",
         "- **Not created:**",
         "  - Waja: the Atlas places the Waja language in Gombe only",
         "  - Pabir: named by the Atlas only",
         "  - Ngoshe: ambiguous",
         "  - Lapang: probably the Laamang, but not assumed",
         "  - Baggara: the Atlas places Baggara Arabic in Yobe",
         "  - Mafa outside Gwoza: a probable mix-up with Mafa LGA", "",
         "## People per LGA", "", "| LGA | Peoples |", "|---|---|"]
    for l, ps in lga_people.items():
        L.append(f"| {LGA[l]} | {', '.join(ps) or '—'} |")
    L += ["", "## The people records", ""] + [f"**{PEOPLE[s][0]}.** {text(s)}" + "\n" for s in PEOPLE] + \
         ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_063b_borno_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_063b_borno_peoples_REVIEW.md", "w").write(report())
    lg = [r for r in RELATIONS if r["type"] == "present_in" and "lga:" in r["to"]]
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} (people-LGA={len(lg)}, well={sum(1 for r in lg if r['level'] == 'well_documented')}) names={len(NAMES)}")
