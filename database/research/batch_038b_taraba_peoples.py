"""
Research batch 038b — Taraba (Phase 3): the peoples of Taraba State and the LGAs where they live,
as far as the sources allow. Researched 2026-09-26. Pattern: Nasarawa batch 031b.

Sources:
  * Wikipedia, "Taraba State": the list of the state's main ethnic groups and their distribution by
    part of the state; a table of languages by (current) LGA citing Ethnologue, 22nd edition.
  * Blench's Atlas (batch 038) for the languages, the peoples' names and the (older) LGAs.
  * No official list of peoples was found: the Taraba State Government site (live and archived
    'About Taraba' and 2009 'History' pages), its investment guide and 2023–26 plan, and the NIPC state
    page (archived) name no ethnic groups. So people records rest on Wikipedia plus the Atlas, and
    are graded lower than Nasarawa's (which had the state government's list).

Rules:
  * People records only for groups Wikipedia names as main groups of the state, and whose language
    the Atlas places in Taraba (Yandang is named by Wikipedia but has no Taraba Atlas entry: reported).
    Not created (umbrella or unclear names): Wurkum, Wurbo, Sho, Daka (a Chamba group) — gaps.
  * A people is linked to an LGA where Ethnologue (via Wikipedia) lists its language there: presence
    only (settlement status unknown). 'Well documented' where the Atlas also places the language in
    that LGA, 'reported' otherwise.
  * The Wikipedia table is visibly edited in places: 'Jibu (Jukun Kona)' in Ardo Kola and Jalingo
    (Jukun Kona is Hõne, not Jibu) is not used; ambiguous names (Jiba, Duguri, Kwa, Pero, Kambu,
    Kpati, Lufu, Acha, Tarok, Nyong, Wurkun-Anphandi) are not mapped to language records.
  * Existing records: Jukun (Wapan, Jukun Takum, Wannu), Tiv, Fulani and Hausa get Taraba links;
    existing links (Tiv in Bali, Takum, Wukari; Etulo in Wukari) are not duplicated.
"""
import json, re, sys

ACCESSED = "2026-09-26"
SOURCES = {
    "WTAR": dict(source_type="encyclopedia", title="Taraba State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Taraba_State",
                 verification_status="needs_corroboration",
                 notes="Reused. Demographics: main ethnic groups Jukun, Jenjo, Fulani, Mumuye, Mambilla, Kuteb, Karimjo, Wurkun, Tiv, Yandang, Ndola, Ichen, Tigon, Jibu; north mostly Fulani, Mumuye and Sho; south Jukun, Wurkum, Tiv, Chamba, Kuteb, Ichen; centre Fulani, Mambilla, Ndola, Tigon, Jibu, Wurbo, Daka. Languages section: table of languages by LGA citing Ethnologue (22nd ed.)."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified", notes="Reused."),
}
LGA = {"ardo-kola": "Ardo Kola", "bali": "Bali", "donga": "Donga", "gashaka": "Gashaka", "gassol": "Gassol", "ibi": "Ibi", "jalingo": "Jalingo",
       "karim-lamido": "Karim Lamido", "kurmi": "Kurmi", "lau": "Lau", "sardauna": "Sardauna", "takum": "Takum", "ussa": "Ussa", "wukari": "Wukari",
       "yorro": "Yorro", "zing": "Zing"}
# Ethnologue (via Wikipedia) languages by LGA, as printed there (unusable entries left out, see above).
ETH = {
 "ardo-kola": ["Fulfulde", "Mumuye", "Hausa"],
 "bali": ["Fulfulde", "Ichen", "Fam", "Gbaya", "Jibu", "Jukun Takum", "Kam", "Mumuye", "Ndoola", "Chamba Dakka", "Chamba Leko", "Tiv", "Hausa"],
 "donga": ["Ichen", "Ekpan", "Chamba Leko", "Tiv"],
 "gashaka": ["Fulfulde", "Jibu", "Ndola", "Chamba Daka", "Yamba", "Tiv", "Hausa"],
 "gassol": ["Fulfulde", "Wapan", "Tiv"],
 "ibi": ["Fulfulde", "Dza", "Tiv", "Wanu"],
 "jalingo": ["Fulfulde", "Mumuye", "Hausa"],
 "karim-lamido": ["Fulfulde", "Dadiya", "Dza", "Sho", "Jiru", "Kodei", "Kulung", "Kyak", "Laka", "Lelau", "Loo", "Maghdi", "Mak", "Munga Doso", "Mumuye", "Nyam",
                  "Pangseng", "Shoo-Minda-Nye", "Yandang", "Hõne", "Hausa", "Bambuka", "Jenjo", "Karimjo", "Gomu", "Panya"],
 "kurmi": ["Ndoro", "Ichen", "Tigun", "Abon", "Bitare", "Tiv"],
 "lau": ["Kunini", "Fulfulde", "Dza", "Loo", "Yandang", "Laka", "Hausa"],
 "sardauna": ["Mambila", "Kaka", "Banso", "Fulfulde", "Tiv"],
 "takum": ["Mashi", "Bete", "Ichen", "Chamba Daka", "Jukun Takum", "Kapya", "Kpan", "Kuteb", "Tiv", "Yukuben"],
 "ussa": ["Kuteb"],
 "wukari": ["Wapan", "Ichen", "Ekpan", "Kulung", "Tiv"],
 "yorro": ["Fulfulde", "Mumuye", "Hausa"],
 "zing": ["Mumuye", "Rang", "Yandang"],
}
# Ethnologue name -> language record slug (batch 038 or existing)
LANGMAP = {"Mumuye": "mumuye", "Ichen": "etkywan", "Fam": "fam", "Gbaya": "gbaya-taraba", "Jibu": "jibu", "Jukun Takum": "jukun-takum", "Kam": "kam",
           "Ndoola": "ndoola", "Ndola": "ndoola", "Ndoro": "ndoola", "Chamba Dakka": "samba-daka", "Chamba Daka": "samba-daka", "Chamba Leko": "samba-leko",
           "Tiv": "tiv", "Ekpan": "kpan", "Kpan": "kpan", "Yamba": "yamba", "Kaka": "yamba", "Wapan": "wapan", "Dza": "dza", "Jenjo": "dza", "Dadiya": "dadiya",
           "Jiru": "jiru", "Kodei": "kholok", "Kulung": "kulung", "Kyak": "kyak", "Bambuka": "kyak", "Laka": "laka-lau", "Lelau": "leelau", "Loo": "loo",
           "Maghdi": "maghdi", "Mak": "mak", "Panya": "mak", "Munga Doso": "mingang-doso", "Nyam": "nyam", "Pangseng": "pangseng", "Shoo-Minda-Nye": "shoo-minda-nye",
           "Sho": "shoo-minda-nye", "Kunini": "shoo-minda-nye", "Hõne": "hone", "Tigun": "mbembe-tigong", "Abon": "abon", "Bitare": "bitare", "Banso": "lamnso",
           "Mashi": "mashi", "Bete": "bete", "Kapya": "kapya", "Kuteb": "kuteb", "Yukuben": "yukuben", "Rang": "rang", "Gomu": "moo", "Wanu": "wannu",
           "Karimjo": "como-karim"}
# Atlas LGA placements (batch 038), to grade agreement.
ATLAS_LGA = {"mumuye": {"jalingo", "zing", "yorro"}, "mambila": {"sardauna"}, "kuteb": {"takum"}, "samba-daka": {"jalingo", "bali", "zing"},
             "samba-leko": {"wukari", "takum"}, "etkywan": {"takum", "sardauna"}, "jibu": {"gashaka"}, "ndoola": {"sardauna", "gashaka"},
             "mbembe-tigong": {"sardauna"}, "dza": {"karim-lamido"}, "como-karim": {"karim-lamido", "jalingo"}, "wapan": {"wukari"},
             "jukun-takum": {"takum", "sardauna", "bali"}, "fam": {"bali"}, "gbaya-taraba": {"bali"}, "kam": {"bali"}, "kpan": {"wukari", "takum", "sardauna"},
             "yamba": {"sardauna", "gashaka"}, "dadiya": {"karim-lamido"}, "jiru": {"karim-lamido"}, "kholok": {"karim-lamido"}, "kulung": {"karim-lamido", "wukari"},
             "kyak": {"karim-lamido"}, "laka-lau": {"karim-lamido"}, "leelau": {"karim-lamido"}, "loo": {"karim-lamido"}, "maghdi": {"karim-lamido"},
             "mak": {"karim-lamido"}, "mingang-doso": {"karim-lamido"}, "nyam": {"karim-lamido"}, "pangseng": {"karim-lamido"}, "shoo-minda-nye": {"karim-lamido"},
             "hone": {"karim-lamido"}, "abon": {"sardauna"}, "bitare": {"sardauna"}, "lamnso": {"sardauna", "takum"}, "mashi": {"takum"}, "bete": {"wukari"},
             "kapya": {"takum"}, "yukuben": {"takum"}, "rang": {"zing"}, "moo": {"karim-lamido"}, "wannu": {"wukari"}, "tiv": {"wukari", "takum", "bali"}}
# slug: (name, language slugs, Ethnologue names that indicate them, region per Wikipedia, other names, Atlas-based note)
PEOPLE = {
 "mumuye": ("Mumuye", ["mumuye"], ["Mumuye"], "the north", "", "Blench's Atlas records their language as a large cluster spoken in Jalingo, Zing and Yorro LGAs."),
 "mambila": ("Mambila", ["mambila"], ["Mambila"], "the centre", "Mambilla", "Blench's Atlas places their language on the Mambila Plateau of Sardauna LGA and in Cameroon, and gives Nɔr as their own name."),
 "kuteb": ("Kuteb", ["kuteb"], ["Kuteb"], "the south", "Kutev, Kutep", "Blench's Atlas places their language in Takum LGA (from which Ussa was later created) and in Cameroon."),
 "chamba": ("Chamba", ["samba-daka", "samba-leko"], ["Chamba Dakka", "Chamba Daka", "Chamba Leko"], "the south", "Samba",
            "Blench's Atlas records two unrelated Chamba languages: Samba Daka (Chamba Daka, a Dakoid language) and Samba Leko (Chamba Leko, an Adamawa language). Wikipedia also names the Daka as a people of the centre of the state."),
 "ichen": ("Ichen", ["etkywan"], ["Ichen"], "the south", "Icen, Itchen", "Blench's Atlas calls their language Etkywan and places it in Takum and Sardauna LGAs."),
 "jibu": ("Jibu", ["jibu"], ["Jibu"], "the centre", "", "Blench's Atlas places their language, a member of the Jukun cluster, in Gashaka LGA."),
 "ndoola": ("Ndoola", ["ndoola"], ["Ndoola", "Ndola", "Ndoro"], "the centre", "Ndola, Ndoro", "Blench's Atlas places their language in Sardauna and Gashaka LGAs and in one village in Cameroon."),
 "tigon": ("Tigon", ["mbembe-tigong"], ["Tigun"], "the centre", "Tigong, Tigun", "Blench's Atlas calls their language Mbembe Tigong and places it in Sardauna LGA, but mainly in Cameroon."),
 "jenjo": ("Jenjo", ["dza"], ["Dza", "Jenjo"], None, "Dza, Jen", "Blench's Atlas calls their language Dza (other names Jenjo, Jen) and places it along the Benue in Karim Lamido LGA and in Numan LGA, Adamawa State."),
 "karimjo": ("Karimjo", ["como-karim"], ["Karimjo"], None, "Karim", "Blench's Atlas gives Karim as a name of the Como Karim language, spoken in Karim Lamido and Jalingo LGAs."),
 "yandang": ("Yandang", [], ["Yandang"], None, "", "Their language is not among the Taraba entries of Blench's Atlas, which places the Yendang language in Adamawa State."),
}
EXISTING = {"jukun": ["Wapan", "Jukun Takum", "Wanu"], "tiv": ["Tiv"], "fulani": ["Fulfulde"], "hausa": ["Hausa"]}
HAVE = {("tiv", "bali"), ("tiv", "takum"), ("tiv", "wukari")}


def lgas_of(eth):
    return [l for l, names in ETH.items() if any(n in names for n in eth)]


def text(slug):
    name, langs, eth, region, alt, note = PEOPLE[slug]
    t = f"The {name} are one of the main peoples of Taraba State named by Wikipedia."
    if region: t += f" Wikipedia places them mainly in {region} of the state."
    if alt: t += f" Other names include {alt}."
    if note: t += " " + note
    ls = lgas_of(eth)
    if ls: t += f" Ethnologue lists their language in {', '.join(LGA[l] for l in ls[:-1])}{' and ' if len(ls) > 1 else ''}{LGA[ls[-1]]} LGA{'s' if len(ls) > 1 else ''}."
    return t


RECORDS, RELATIONS = [], []
for slug, (name, langs, eth, region, alt, note) in PEOPLE.items():
    atlas = bool(langs)
    RECORDS.append(dict(key=slug, table="ethnic_groups", evidence="multiple_sources" if atlas else "single_reliable_source",
                        level="well_documented" if atlas else "reported",
                        fields=dict(name=name, slug=slug, summary=text(slug).split(". ")[0] + ".", description=text(slug)),
                        srcs=[("WTAR", f"{name} named among the main peoples of Taraba; Ethnologue language table")] + ([("ATLAS", f"{name}: their language in Taraba")] if atlas else [])))
    RELATIONS.append(dict(frm=slug, type="present_in", to="@admin_units:state:taraba", source="WTAR", evidence="multiple_sources" if atlas else "single_reliable_source",
                          level="well_documented" if atlas else "reported", notes="Named among the main peoples of the state by Wikipedia" + ("; their language is placed in Taraba by Blench's Atlas." if atlas else ".")))
    for lg in langs:
        RELATIONS.append(dict(frm=slug, type="speaks", to=f"@languages:{lg}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                              notes="Their language, per Blench's Atlas."))
    for l in lgas_of(eth):
        both = any(l in ATLAS_LGA.get(lg, ()) for lg in langs)
        RELATIONS.append(dict(frm=slug, type="present_in", to=f"@admin_units:lga:taraba/{l}", source="WTAR",
                              evidence="multiple_sources" if both else "single_reliable_source", level="well_documented" if both else "reported",
                              settlement_status="unknown",
                              notes="Their language is listed in this LGA by Ethnologue (via Wikipedia)" + ("; Blench's Atlas agrees." if both else ".") + " Presence only; the nature of their presence is not established."))
# Existing people records.
RELATIONS += [
    dict(frm="@ethnic_groups:jukun", type="speaks", to="@languages:wapan", source="ATLAS", evidence="single_reliable_source", level="well_documented",
         notes="Wapan is the Jukun of Wukari (Blench's Atlas, Kororofa cluster)."),
    dict(frm="@ethnic_groups:jukun", type="speaks", to="@languages:jukun-takum", source="ATLAS", evidence="single_reliable_source", level="well_documented",
         notes="Jukun Takum, a member of the Jukun cluster (Blench's Atlas)."),
]
for g in ("fulani", "hausa"):
    RELATIONS.append(dict(frm=f"@ethnic_groups:{g}", type="present_in", to="@admin_units:state:taraba", source="WTAR", evidence="single_reliable_source", level="reported",
                          notes="Wikipedia: the Fulani are among the main peoples, mostly in the north and centre." if g == "fulani" else "Hausa is listed in seven LGAs by Ethnologue (via Wikipedia)."))
for slug, names in EXISTING.items():
    for l, lst in ETH.items():
        if any(n in lst for n in names) and (slug, l) not in HAVE:
            lang = next(LANGMAP.get(n) for n in names if n in lst) if slug in ("jukun", "tiv") else None
            both = bool(lang) and l in ATLAS_LGA.get(lang, ())
            RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to=f"@admin_units:lga:taraba/{l}", source="WTAR",
                                  evidence="multiple_sources" if both else "single_reliable_source", level="well_documented" if both else "reported", settlement_status="unknown",
                                  notes="Their language (" + ", ".join(n for n in names if n in lst) + ") is listed in this LGA by Ethnologue (via Wikipedia)" + ("; Blench's Atlas agrees." if both else ".") + " Presence only."))
# New language–LGA links from Ethnologue where batch 038 (or earlier) had none.
for l, names in ETH.items():
    for n in names:
        s = LANGMAP.get(n)
        if not s or l in ATLAS_LGA.get(s, ()):
            continue
        RELATIONS.append(dict(frm=f"@languages:{s}", type="spoken_in", to=f"@admin_units:lga:taraba/{l}", source="WTAR", evidence="single_reliable_source", level="reported",
                              notes=f"Listed in {LGA[l]} LGA by Ethnologue (22nd ed., via Wikipedia) as '{n}'."))
seen, RELS = set(), []
for r in RELATIONS:
    k = (r["frm"], r["type"], r["to"])
    if k not in seen:
        seen.add(k); RELS.append(r)
RELATIONS = RELS
NAMES = [dict(record="mambila", name="Mambilla", name_type="spelling_variant", usage_notes="Wikipedia's spelling.", srcs=["WTAR"]),
         dict(record="mambila", name="Nɔr", name_type="endonym", usage_notes="Blench's Atlas (2020), Mambila entry, field 1.C: the people's own name for themselves.", srcs=["ATLAS"]),
         dict(record="ichen", name="Icen", name_type="spelling_variant", usage_notes="Blench's Atlas (2020), Etkywan entry, field 1.A.", srcs=["ATLAS"]),
         dict(record="ndoola", name="Ndola", name_type="spelling_variant", usage_notes="Wikipedia.", srcs=["WTAR"]),
         dict(record="tigon", name="Tigong", name_type="spelling_variant", usage_notes="Blench's Atlas (2020), Mbembe Tigong entry, field 2.A.", srcs=["ATLAS"]),
         dict(record="chamba", name="Samba", name_type="spelling_variant", usage_notes="Blench's Atlas (2020), Samba Leko entry, field 1.C.", srcs=["ATLAS"]),
         dict(record="chamba", name="Samabu", name_type="endonym", usage_notes="Blench's Atlas (2020), Samba Daka entry, field 1.C.", srcs=["ATLAS"])]
GAPS = [
    ("No official list of Taraba's peoples", "The state government's site (live and archived), its investment guide and plan, and the NIPC page name none. A state or academic ethnographic source would raise the people records above 'well documented'."),
    ("Wurkum, Wurbo, Sho, Daka", "Named by Wikipedia; 'Wurkum' is an umbrella name the Atlas gives for Kulung, Piya and Kholok; 'Wurbo' is an Atlas cluster (Como Karim, Jiru, Shoo-Minda-Nye); Daka is a Chamba group. Not recorded as separate peoples."),
    ("The many small peoples of Karim Lamido, Lau and the Mambila Plateau", "About 60 language communities are recorded as languages (batch 038) but not as peoples."),
    ("Who is indigenous where", "All LGA links are presence only (settlement status unknown)."),
    ("Wikipedia's Ethnologue table", "Visibly edited ('Jibu (Jukun Kona)', mixed links). Unmapped names: Jiba, Duguri, Kwa, Pero, Kambu, Kpati, Lufu, Acha, Tarok, Nyong, Wurkun-Anphandi."),
    ("Yandang", "Named by Wikipedia and listed in Karim Lamido, Lau and Zing by Ethnologue; the Atlas's Yendang entry is in Adamawa State. No language link."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Taraba (Phase 3): 11 peoples (Wikipedia's main groups, checked against Blench's Atlas), LGA links from Ethnologue, language links; Jukun, Tiv, Fulani, Hausa links.")


def report():
    L = ["# Research batch 038b — Taraba: the peoples", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "**Important:** unlike Nasarawa, **no official list of Taraba's peoples** could be found: not on the state government's site (live or archived), not in its investment guide or plan, and not on the NIPC page. The people records therefore rest on Wikipedia's list of the state's main groups, checked against Blench's Atlas, and are graded *well documented* at most, not *verified*.", "",
         f"- **{len(RECORDS)} new people records:** " + ", ".join(p[0] for p in PEOPLE.values()) + ". Each is linked to Taraba and to its language(s).",
         f"- **{sum(1 for r in RELATIONS if r['type'] == 'present_in' and 'lga:' in r['to'])} people–LGA links** from Ethnologue's language table (via Wikipedia): *well documented* where the Atlas agrees, *reported* otherwise. All are presence only.",
         "- **Existing records:** the Jukun now 'speak' Wapan and Jukun Takum (as well as Wannu). The Jukun, Tiv, Fulani and Hausa are linked to the LGAs where Ethnologue lists their languages, and the Fulani and Hausa to Taraba State.",
         f"- **{sum(1 for r in RELATIONS if r['type'] == 'spoken_in')} new language–LGA links** from Ethnologue, mostly for **today's** LGAs the Atlas does not use (Ussa, Donga, Kurmi, Lau, Ibi, Gassol, Ardo Kola). An example: Kuteb in Ussa.",
         "- The Chamba record links to **both** Chamba languages (Samba Daka and Samba Leko), which the Atlas says are unrelated.",
         "- All records are short (noindex; sitemap unchanged).", "",
         "## People–LGA coverage", "", "| LGA | Peoples linked |", "|---|---|"]
    for l in ETH:
        who = [PEOPLE[s][0] for s in PEOPLE if any(n in ETH[l] for n in PEOPLE[s][2])] + [e.title() for e, n in EXISTING.items() if any(x in ETH[l] for x in n)]
        L.append(f"| {LGA[l]} | {', '.join(who)} |")
    L += ["", "## The people records", ""] + [f"**{PEOPLE[s][0]}.** {text(s)}" + "\n" for s in PEOPLE] + \
         ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_038b_taraba_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_038b_taraba_peoples_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} (present_in LGA={sum(1 for r in RELATIONS if r['type'] == 'present_in' and 'lga:' in r['to'])}, spoken_in={sum(1 for r in RELATIONS if r['type'] == 'spoken_in')}) names={len(NAMES)}")
