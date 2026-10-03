"""
Research batch 031b — Nasarawa (Phase 3): the peoples of Nasarawa State and the LGAs where they
live, as far as the sources allow. Researched 2026-09-26.

Sources:
  * Nasarawa State Government, "About Nasarawa" (Tier 1): names the peoples of the state and calls
    the Igbo, Yoruba and Hausa settler groups.
  * Wikipedia, "Nasarawa State": broad distribution by part of the state; a table of languages by
    (current) LGA taken from Ethnologue, 22nd edition.
  * Blench's Atlas (batch 031) for the languages and their older LGA names.

Rules:
  * A people is linked to an LGA where its language is listed there. Presence only: settlement
    status 'unknown' (a language list does not say who is indigenous). Level 'reported' from
    Ethnologue alone; 'well documented' where Blench's Atlas also places the language in that LGA.
  * Settlers (Igbo, Yoruba): 'migrant community' at state level, as the state government words it.
  * Not linked: Agatu (named by the state, but recorded as an Idoma subgroup in batch 023 — gap),
    Kofyar, Wapan (as a people), 'Bare-Bari', 'Koro Wachi' as a separate language.
"""
import json, re, sys

ACCESSED = "2026-09-26"
SOURCES = {
    "NSG": dict(source_type="official_website", title="About Nasarawa State", organisation="Nasarawa State Government",
                url="https://nasarawastate.gov.ng/about-nasarawa/", verification_status="needs_corroboration", notes="Reused."),
    "WNS": dict(source_type="encyclopedia", title="Nasarawa State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Nasarawa_State",
                verification_status="needs_corroboration", notes="Reused. Ethnic distribution by part of the state; table 'Languages of Nasarawa State listed by LGA' citing Ethnologue (22nd ed.)."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified", notes="Reused."),
}
LGA = {"akwanga": "Akwanga", "awe": "Awe", "doma": "Doma", "keffi": "Keffi", "karu": "Karu", "keana": "Keana", "kokona": "Kokona", "lafia": "Lafia",
       "nasarawa": "Nasarawa", "nasarawa-eggon": "Nasarawa Eggon", "obi": "Obi", "toto": "Toto", "wamba": "Wamba"}
# Ethnologue (via Wikipedia) languages by LGA, as printed there.
ETH = {
 "akwanga": ["Mada", "Eggon", "Fulani", "Hausa", "Ninzo", "Numana"],
 "awe": ["Hausa", "Fulani", "Gwandara", "Eggon", "Goemai", "Lijili", "Tiv", "Wapan"],
 "doma": ["Alago", "Eggon", "Agatu", "Fulani", "Tiv"],
 "keffi": ["Hausa", "Fulani", "Mada", "Gade", "Eggon", "Gbagyi", "Gwandara", "Koro Wachi"],
 "karu": ["Gbagyi", "Mada", "Gwandara", "Gade"],
 "keana": ["Alago", "Gwandara", "Fulani", "Hausa", "Tiv"],
 "kokona": ["Gwandara", "Mada", "Afo", "Eggon", "Ninzo"],
 "lafia": ["Bare-Bari", "Hausa", "Fulani", "Ake", "Mada", "Alago", "Agatu", "Eggon", "Goemai", "Gwandara", "Tiv", "Kofyar", "Lijili", "Wapan"],
 "nasarawa": ["Afo", "Hausa", "Fulani", "Mada", "Tiv", "Agatu", "Alago", "Basa", "Egbira", "Eloyi", "Gade", "Gbagyi", "Gwandara", "Eggon"],
 "nasarawa-eggon": ["Eggon", "Mada", "Fulani", "Hausa"],
 "obi": ["Alago", "Tiv", "Migili", "Eggon", "Gwandara"],
 "toto": ["Egbura", "Agatu", "Eggon", "Gade", "Fulani", "Mada", "Hausa", "Gbagyi"],
 "wamba": ["Alumu-Tesu", "Ninzo", "Mada", "Rindre", "Buh", "Eggon", "Hausa", "Fulani", "Kantana", "Nungu", "Toro"],
}
# Atlas LGA placements of the languages (batch 031) — to grade agreement.
ATLAS_LGA = {"eggon": {"akwanga", "nasarawa-eggon", "lafia"}, "mada": {"akwanga", "kokona", "keffi"}, "gwandara": {"nasarawa", "keffi", "lafia", "akwanga"},
             "alago": {"awe", "lafia"}, "jili": {"lafia", "awe"}, "gade": {"nasarawa"}, "gbagyi": {"keffi", "nasarawa"}, "ninzo": {"akwanga"},
             "eloyi": {"nasarawa", "awe"}, "ebira": {"nasarawa"}, "goemai": {"awe", "lafia"}, "alumu-tesu": {"akwanga"}, "rindre": {"akwanga"},
             "bu-ningkada": {"akwanga"}, "mama": {"akwanga"}, "toro": {"akwanga"}, "numbu-gbantu-nunku": {"akwanga"}, "ake": {"lafia"}}
# Ethnologue name -> language record slug (existing or batch 031)
LANGMAP = {"Mada": "mada", "Eggon": "eggon", "Ninzo": "ninzo", "Numana": "numbu-gbantu-nunku", "Gwandara": "gwandara", "Goemai": "goemai", "Lijili": "jili",
           "Migili": "jili", "Tiv": "tiv", "Alago": "alago", "Gade": "gade", "Gbagyi": "gbagyi", "Afo": "eloyi", "Eloyi": "eloyi", "Ake": "ake",
           "Egbira": "ebira", "Egbura": "ebira", "Alumu-Tesu": "alumu-tesu", "Rindre": "rindre", "Buh": "bu-ningkada", "Kantana": "mama", "Toro": "toro"}
# People records: slug -> (name, state list?, region (Wikipedia), language slug or None, Ethnologue names that indicate them, other names, note)
PEOPLE = {
 "gwandara": ("Gwandara", True, "the north", "gwandara", ["Gwandara"], "", ""),
 "alago": ("Alago", True, "the east", "alago", ["Alago"], "Arago", ""),
 "eggon": ("Eggon", True, "the north", "eggon", ["Eggon"], "", ""),
 "gbagyi": ("Gbagyi", True, "the west", "gbagyi", ["Gbagyi"], "Gbagi, Gwari", ""),
 "ebira": ("Ebira", True, None, "ebira", ["Egbira", "Egbura"], "Egbira, Igbirra", ""),
 "migili": ("Migili", True, "the east", "jili", ["Lijili", "Migili"], "Megili, Lijili; Koro of Lafia", "Wikipedia's list of major groups calls them Migili (Koro)."),
 "kantana": ("Kantana", True, None, "mama", ["Kantana"], "Mama, Kwarra", "Blench's Atlas gives Kantana and Kwarra as other names of the Mama language."),
 "fulani": ("Fulani", True, "all parts of the state", None, ["Fulani"], "Fulbe", ""),
 "kanuri": ("Kanuri", True, None, None, [], "", ""),
 "afo": ("Afo", True, "the south", "eloyi", ["Afo", "Eloyi"], "Eloyi, Ajiri", "Wikipedia calls them Eloyi (Ajiri/Afo)."),
 "gade": ("Gade", True, "the west", "gade", ["Gade"], "", ""),
 "nyankpa": ("Nyankpa", True, "the far north-west", None, [], "Yeskwa", "Wikipedia places the Yeskwa in the far north-west of the state."),
 "koro": ("Koro", True, "the far north-west", None, [], "", "The Koro languages of the area include Ashe (Koron Ache) and Idun (Blench's Atlas)."),
 "mada": ("Mada", True, "the north", "mada", ["Mada"], "", ""),
 "ninzam": ("Ninzam", True, "the north", "ninzo", ["Ninzo"], "Ninzo", "Wikipedia calls them Ninzo."),
 "buh": ("Buh", True, "the north", "bu-ningkada", ["Buh"], "Bu", ""),
 "basa": ("Basa", True, "the west", None, ["Basa"], "Bassa", ""),
 "arum": ("Arum", True, None, "alumu-tesu", ["Alumu-Tesu"], "Alumu", "Their language is Alumu (Arum), of the Alumu–Tesu cluster (Blench's Atlas)."),
 "kulere": ("Kulere", True, None, None, [], "", ""),
 "rindre": ("Rindre", False, "the north", "rindre", ["Rindre"], "Nungu", "Named among the major groups by Wikipedia (not in the state government's list); Wikipedia's regional list calls them Nungu."),
}
EXISTING = {"tiv": ["Tiv"], "hausa": ["Hausa"], "jukun": ["Wapan"]}


def text(slug):
    name, in_state, region, lang, _eth, alt, note = PEOPLE[slug]
    t = f"The {name} are one of the peoples of Nasarawa State" + (", named by the state government in its list of the state's peoples." if in_state else ".")
    if region: t += f" Wikipedia places them in {region} of the state." if region != "all parts of the state" else " Wikipedia describes them, with the Hausa, as living throughout the state."
    if alt: t += f" Other names include {alt}."
    if note: t += " " + note
    lgas = [l for l, names in ETH.items() if any(n in names for n in _eth)]
    if lgas: t += f" Ethnologue lists their language in {', '.join(LGA[l] for l in lgas[:-1])}{' and ' if len(lgas) > 1 else ''}{LGA[lgas[-1]]} LGA{'s' if len(lgas) > 1 else ''}."
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for slug, (name, in_state, region, lang, eth, alt, note) in PEOPLE.items():
    srcs = ([("NSG", f"{name} named among the peoples of Nasarawa State")] if in_state else []) + [("WNS", f"{name}: distribution; Ethnologue language table")]
    RECORDS.append(dict(key=slug, table="ethnic_groups", evidence="multiple_sources" if in_state else "single_reliable_source",
                        level="verified" if in_state else "reported",
                        fields=dict(name=name, slug=slug,
                                    summary=text(slug).split(". ")[0] + ".", description=text(slug)),
                        srcs=srcs))
    RELATIONS.append(dict(frm=slug, type="present_in", to="@admin_units:state:nasarawa", source="NSG" if in_state else "WNS",
                          evidence="single_reliable_source", level="verified" if in_state else "reported",
                          notes="Named among the peoples of the state by the Nasarawa State Government." if in_state else "Named by Wikipedia among the major groups."))
    if lang:
        RELATIONS.append(dict(frm=slug, type="speaks", to=f"@languages:{lang}" if lang in ("tiv",) else f"@languages:{lang}", source="ATLAS",
                              evidence="single_reliable_source", level="well_documented", notes="Their language, per Blench's Atlas."))
    for l, names in ETH.items():
        if any(n in names for n in eth):
            both = lang in ATLAS_LGA and l in ATLAS_LGA[lang]
            RELATIONS.append(dict(frm=slug, type="present_in", to=f"@admin_units:lga:nasarawa/{l}", source="WNS",
                                  evidence="multiple_sources" if both else "single_reliable_source", level="well_documented" if both else "reported",
                                  settlement_status="unknown",
                                  notes="Their language is listed in this LGA by Ethnologue (via Wikipedia)" + ("; Blench's Atlas agrees." if both else ".") + " Presence only; the nature of their presence is not established."))
# Existing people records: Hausa, Tiv, Jukun (Wapan) in the LGAs Ethnologue lists (Tiv's Lafia link exists).
for slug, names in EXISTING.items():
    for l, lst in ETH.items():
        if any(n in lst for n in names) and not (slug == "tiv" and l == "lafia"):
            RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to=f"@admin_units:lga:nasarawa/{l}", source="WNS",
                                  evidence="single_reliable_source", level="reported", settlement_status="unknown",
                                  notes=("Their language (Wapan, the Jukun language of Wukari)" if slug == "jukun" else "Their language") + " is listed in this LGA by Ethnologue (via Wikipedia). Presence only."))
RELATIONS.append(dict(frm="@ethnic_groups:hausa", type="present_in", to="@admin_units:state:nasarawa", source="NSG", evidence="single_reliable_source", level="verified",
                      notes="Named by the state government both among the peoples of the state and among settler groups; Wikipedia: living throughout the state."))
for g, key in (("igbo", "@ethnic_groups:igbo"), ("yoruba", "yoruba")):
    if g == "yoruba":
        RECORDS.append(dict(key="yoruba", table="ethnic_groups", evidence="single_reliable_source", level="verified",
                            fields=dict(name="Yoruba", slug="yoruba", summary="The Yoruba are one of the largest peoples of Nigeria, based mainly in the south-west.",
                                        description="The Yoruba are one of the largest peoples of Nigeria, based mainly in the south-west of the country. The Nasarawa State Government names them among the settler groups living in Nasarawa State. A fuller record of the Yoruba will come with the research on the south-western states."),
                            srcs=[("NSG", "Yoruba among the settler groups of Nasarawa")]))
    RELATIONS.append(dict(frm=key, type="present_in", to="@admin_units:state:nasarawa", source="NSG", evidence="single_reliable_source", level="verified",
                          settlement_status="migrant_community", notes="Named by the Nasarawa State Government among the settler groups of the state."))
# New language–LGA links from Ethnologue where batch 031 had none.
for l, names in ETH.items():
    for n in names:
        s = LANGMAP.get(n)
        if not s or s == "tiv" and l in ("lafia",) or (s in ATLAS_LGA and l in ATLAS_LGA[s]):
            continue
        RELATIONS.append(dict(frm=f"@languages:{s}", type="spoken_in", to=f"@admin_units:lga:nasarawa/{l}", source="WNS", evidence="single_reliable_source", level="reported",
                              notes=f"Listed in {LGA[l]} LGA by Ethnologue (22nd ed., via Wikipedia) as '{n}'."))
# de-duplicate identical relations
seen, RELS = set(), []
for r in RELATIONS:
    k = (r["frm"], r["type"], r["to"])
    if k not in seen:
        seen.add(k); RELS.append(r)
RELATIONS = RELS

GAPS = [
    ("Agatu in Nasarawa", "The state government lists the Agatu separately; batch 023 records Agatu as an Idoma area of Benue. Ethnologue lists Agatu in Doma, Lafia, Nasarawa and Toto LGAs. Their record and classification need a decision."),
    ("Who is indigenous where", "All LGA links are presence only (settlement status unknown). Indigeneity by LGA needs state or ethnographic sources, handled neutrally."),
    ("Kanuri, Kulere, Nyankpa and Koro: LGAs", "Named by the state but not placed in LGAs by the sources used. 'Bare-Bari' in Lafia (Ethnologue) may be the Kanuri (Beriberi) — not assumed."),
    ("Kofyar, Wapan, Koro Wachi", "Listed by Ethnologue in Lafia, Awe and Keffi; not recorded as peoples here."),
    ("Descriptions of the peoples", "Each record is short; histories, institutions and cultures come in later batches."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Nasarawa (Phase 3): 21 peoples (state government list; Rindre; Yoruba settlers), their LGAs from Ethnologue, language links; Hausa, Tiv, Jukun, Igbo.")


def report():
    ppl = [r for r in RECORDS]
    L = ["# Research batch 031b — Nasarawa: the peoples", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "**Sources:**",
         "- the Nasarawa State Government's list of the state's peoples (Tier 1)",
         "- Wikipedia's account of where they live, including its table of **languages by current LGA from Ethnologue**",
         "- Blench's Atlas (batch 031) to check agreement", "",
         f"- **{len(ppl)} new people records**: the {sum(1 for p in PEOPLE.values() if p[1])} peoples the state names, Rindre (named by Wikipedia), and the Yoruba (named by the state as settlers). Each is linked to Nasarawa State and, where known, to its language.",
         f"- **{sum(1 for r in RELATIONS if r['type'] == 'present_in' and 'lga:' in r['to'])} people–LGA links**, one wherever Ethnologue lists the people's language in that LGA:",
         "  - *well documented* where the Atlas agrees",
         "  - *reported* from Ethnologue alone",
         "  - all marked **presence only** (settlement status unknown), because a language list doesn't say who is indigenous",
         f"- **{sum(1 for r in RELATIONS if r['type'] == 'spoken_in')} new language–LGA links** from Ethnologue. Many use **today's** LGAs: Rindre, Ninzo, Alumu-Tesu, Toro and Buh in Wamba, which fixes the Atlas's older 'Akwanga'.",
         "- **Settlers:** the state government calls the Igbo and Yoruba settler groups, so they are marked *migrant community* at state level.",
         "- Every one of the 13 LGAs now has peoples linked (before: only Lafia).",
         "- All records are short, so they stay noindex (sitemap unchanged).", "",
         "## People–LGA coverage", "", "| LGA | Peoples linked (from Ethnologue's language list) |", "|---|---|"]
    for l in ETH:
        who = [PEOPLE[s][0] for s in PEOPLE if any(n in ETH[l] for n in PEOPLE[s][4])] + [e.title() for e, n in EXISTING.items() if any(x in ETH[l] for x in n)]
        L.append(f"| {LGA[l]} | {', '.join(who)} |")
    L += ["", "## Example texts", ""] + [f"> {text(s)}" for s in ("eggon", "migili", "afo", "fulani")] + \
         ["", "## Not linked", "", "- Agatu (a classification question; see the gaps), Kofyar, Wapan as a people, 'Bare-Bari', 'Koro Wachi'.", "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_031b_nasarawa_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_031b_nasarawa_peoples_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} (present_in LGA={sum(1 for r in RELATIONS if r['type'] == 'present_in' and 'lga:' in r['to'])}, spoken_in={sum(1 for r in RELATIONS if r['type'] == 'spoken_in')})")
