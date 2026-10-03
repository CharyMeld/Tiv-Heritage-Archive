"""
Research batch 093b — Jigawa (Phase 3): the peoples of Jigawa State and the LGAs where they live, as far as the
sources allow. Researched 2026-10-02. Pattern: batches 063b … 087b.

Sources:
  * Federal Government of Nigeria, state profile 'Jigawa' (nigeria.gov.ng/states/jigawa; copy in
    data/fg_jigawa_2026-10-02.html) — Tier 1: 'Hausa culture and tradition have overshadowed others but the Fulani,
    Mangawa, Ngizimawa and Badawa still maintain their culture and tradition in their areas of concentration.'
  * Wikipedia, 'Jigawa State': Hausa/Fulani in all parts of the state; Kanuri largely in the Hadejia Emirate; Badawa
    mainly in the north-east. Languages: Bade in Guri LGA; Manga in Birniwa, Kiri Kasama, parts of Malam Madori,
    Kaugama and Guri; Warji in Birnin Kudu; Duwai in Hadejia.
  * Wikipedia's Jigawa LGA articles (read 2 Oct 2026), explicit statements only.
No new people records: every people named already has one. 'Badawa' is read here as the Bade (Jigawa borders the Bade
homeland in Yobe; Wikipedia pairs the Badawa with the Bade language of Guri) — unlike Bauchi, where the Atlas ties
'Badawa' to the Mbat.
"""
import json, sys
import batch_093_jigawa_languages as L93

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "FGJI": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Jigawa State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/jigawa/", verification_status="verified",
                 notes="'Ethnic Profile': Hausa culture dominant; 'the Fulani, Mangawa, Ngizimawa and Badawa still maintain their culture and tradition in their areas of concentration.' Created 27 August 1991. Read 2026-10-02 (copy in database/research/data/)."),
    "WJIG": WS("Jigawa State", "Hausa/Fulani found in all parts; Kanuri largely in the Hadejia Emirate; Badawa mainly in the north-east. Bade spoken in Guri LGA; Manga in Birniwa, Kiri Kasama, parts of Malam Madori, Kaugama and Guri; Warji in Birnin Kudu; Duwai in Hadejia."),
    "ATLAS": L93.SOURCES["ATLAS"],
}
ART = {  # lga: (Wikipedia title, quote)
    "auyo": ("Auyo", "'consists primarily of Hausa and Fulani ethnic groups'"),
    "biriniwa": ("Birniwa", "'a multicultural town with Kanuri, Hausa and Fulani residents'; the early settlers are 'Kanuri (Mangawa)'"),
    "dutse": ("Dutse", "'the population of Dutse is predominantly Hausa and Fulani'"),
    "gumel": ("Gumel", "the emirate 'was founded about 1750 by Dan Juma and his followers from the Mangawa tribe'"),
    "guri": ("Guri, Jigawa State", "'The Bade language is spoken in Guri LGA'"),
    "hadejia": ("Hadejia", "'Inhabitants are dominantly Hausa, Fulani and Kanuri with some other groups such as Tiv, Yoruba, Igbo, Igala etc.'"),
    "jahun": ("Jahun", "'The LGA is primarily inhabitated by the Fulani people'"),
    "kazaure": ("Kazaure", "first settled by a Hausa (Habe) hunter clan under Kutumbi; administration established on the arrival of the Yarimawan Fulani"),
    "maigatari": ("Maigatari", "'The principal inhabitants of the town include Hausa, Fulani and Kanuri'"),
    "malam-madori": ("Malam Madori", "'The original inhabitants of Mallam Madori were Fulani and Kanuri'"),
    "taura": ("Taura, Jigawa", "'Most people in Taura are Hausa people'"),
}
for l, (t, q) in ART.items():
    SOURCES[f"W_{l}"] = WS(t, f"Jigawa LGA article: {q}.")
STATE = [  # people, source, level, note
    ("hausa", "FGJI", "well_documented", "Federal profile: Hausa culture and tradition dominate; Wikipedia: Hausa/Fulani in all parts of the state."),
    ("fulani", "FGJI", "well_documented", "Named by the federal profile; Wikipedia: Hausa/Fulani in all parts of the state."),
    ("manga", "FGJI", "well_documented", "Federal profile ('Mangawa'); Wikipedia places the Manga language in Birniwa, Kiri Kasama, Malam Madori, Kaugama and Guri."),
    ("ngizim", "FGJI", "well_documented", "Federal profile ('Ngizimawa'). No LGA named in a source read."),
    ("bade", "FGJI", "well_documented", "Federal profile ('Badawa'); Wikipedia: 'traces of Badawa mainly in its Northeastern parts', and the Bade language in Guri LGA."),
    ("kanuri", "WJIG", "reported", "Wikipedia: 'Kanuri are largely found in Hadejia Emirate'; Blench's Atlas places Kanuri in Hadejia LGA."),
    ("tiv", "W_hadejia", "reported", "Wikipedia (Hadejia): among 'some other groups' living there. Presence only."),
    ("yoruba", "W_hadejia", "reported", "Wikipedia (Hadejia): among 'some other groups' living there. Presence only."),
    ("igbo", "W_hadejia", "reported", "Wikipedia (Hadejia): among 'some other groups' living there. Presence only."),
    ("igala", "W_hadejia", "reported", "Wikipedia (Hadejia): among 'some other groups' living there. Presence only."),
]
LGA = {  # lga: [(people, source, detail)]
    "auyo": [("hausa", "W_auyo", None), ("fulani", "W_auyo", None)],
    "biriniwa": [("manga", "W_biriniwa", "'Kanuri (Mangawa)' are the early settlers; Wikipedia (Jigawa State) places the Manga language here"), ("kanuri", "W_biriniwa", None),
                 ("hausa", "W_biriniwa", None), ("fulani", "W_biriniwa", None)],
    "dutse": [("hausa", "W_dutse", None), ("fulani", "W_dutse", None)],
    "gumel": [("manga", "W_gumel", "the Gumel Emirate was founded about 1750 by Dan Juma and his followers from the Mangawa")],
    "guri": [("bade", "W_guri", "the Bade language is spoken in Guri LGA (Wikipedia, Guri and Jigawa State)"), ("manga", "WJIG", "Wikipedia (Jigawa State) places the Manga language in Guri")],
    "hadejia": [("hausa", "W_hadejia", None), ("fulani", "W_hadejia", None), ("kanuri", "W_hadejia", None),
                ("tiv", "W_hadejia", "among 'some other groups such as Tiv, Yoruba, Igbo, Igala'"), ("yoruba", "W_hadejia", "among 'some other groups'"),
                ("igbo", "W_hadejia", "among 'some other groups'"), ("igala", "W_hadejia", "among 'some other groups'")],
    "jahun": [("fulani", "W_jahun", None)],
    "kazaure": [("hausa", "W_kazaure", "first settled by a Hausa (Habe) hunter clan"), ("fulani", "W_kazaure", "the Yarimawan Fulani established its administration")],
    "maigatari": [("hausa", "W_maigatari", None), ("fulani", "W_maigatari", None), ("kanuri", "W_maigatari", None)],
    "malam-madori": [("fulani", "W_malam-madori", None), ("kanuri", "W_malam-madori", None), ("manga", "WJIG", "Wikipedia (Jigawa State) places the Manga language in parts of Malam Madori")],
    "kaugama": [("manga", "WJIG", "Wikipedia (Jigawa State) places the Manga language in Kaugama")],
    "kiri-kasama": [("manga", "WJIG", "Wikipedia (Jigawa State) places the Manga language in Kiri Kasama")],
    "taura": [("hausa", "W_taura", None)],
    "birnin-kudu": [("warji", "WJIG", "Wikipedia (Jigawa State) and Blench's Atlas place the Warji language in Birnin Kudu")],
}
ATLAS_AGREE = {("kanuri", "hadejia"), ("warji", "birnin-kudu")}
RELATIONS = [dict(frm=f"@ethnic_groups:{p}", type="present_in", to="@admin_units:state:jigawa", source=s, evidence="multiple_sources" if lvl == "well_documented" else "single_reliable_source",
                  level=lvl, notes=n) for p, s, lvl, n in STATE]
for l, ps in LGA.items():
    for p, s, d in ps:
        q = ART[l][1] if l in ART else ""
        agree = (p, l) in ATLAS_AGREE
        RELATIONS.append(dict(frm=f"@ethnic_groups:{p}", type="present_in", to=f"@admin_units:lga:jigawa/{l}", source=s,
                              evidence="multiple_sources" if agree else "single_reliable_source", level="well_documented" if agree else "reported", settlement_status="unknown",
                              notes=(d or f"Wikipedia ({ART[l][0]}): {q}") + ("; Blench's Atlas places their language here" if agree else "") + ". Presence only; the nature of their presence is not established."))
RELATIONS.append(dict(frm="@languages:duwai", type="spoken_in", to="@admin_units:lga:jigawa/hadejia", source="WJIG", evidence="single_reliable_source", level="reported",
                      notes="Wikipedia (Jigawa State): 'the Duwai language is spoken in Hadejia LGA'."))
RELATIONS.append(dict(frm="@languages:duwai", type="spoken_in", to="@admin_units:state:jigawa", source="WJIG", evidence="single_reliable_source", level="reported", notes="Wikipedia (Jigawa State)."))
GAPS = [
    ("Jigawa: peoples by LGA", "Explicit statements cover 14 of the 27 LGAs; the federal profile names the Ngizim without an LGA. Babura, Buji, Gagarawa, Garki, Gwaram, Gwiwa, Kafin Hausa, Kiyawa, Miga, Ringim, Roni, Sule Tankarkar and Yankwashi have none."),
    ("Jigawa: 'Badawa'", "Read as the Bade (Wikipedia pairs the Badawa with the Bade language of Guri). In Bauchi the Atlas ties 'Badawa' to the Mbat; the two uses should not be merged."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Jigawa (Phase 3): Hausa, Fulani, Manga, Ngizim, Bade, Kanuri and others linked to Jigawa, with people–LGA links for 14 LGAs; Duwai linked to Hadejia.")


def report():
    lg = [r for r in RELATIONS if "lga:jigawa" in r["to"] and r["frm"].startswith("@ethnic")]
    per = {}
    for r in lg:
        per.setdefault(r["to"].split("/")[-1], []).append(r["frm"].split(":")[-1].title())
    L = ["# Research batch 093b — Jigawa: the peoples", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "- **No new people records.** Every people the sources name already has one.",
         "- **State links (10):**",
         "  - **Hausa, Fulani, Manga, Ngizim, Bade** (the federal profile)",
         "  - **Kanuri** (Wikipedia)",
         "  - **Tiv, Yoruba, Igbo, Igala**: named by Wikipedia among the smaller groups at Hadejia; *reported*",
         f"- **{len(lg)} people–LGA links covering {len(per)} of the 27 LGAs.** {sum(1 for r in lg if r['level']=='well_documented')} are *well documented* (Kanuri in Hadejia and Warji in Birnin Kudu, where the Atlas agrees); the rest are *reported*.",
         "- **The Duwai language** gets a Hadejia link (Wikipedia).",
         "- **'Badawa'** is read as the Bade here, not the Mbat as in Bauchi. This is noted as a gap so the two uses are not merged.", "",
         "## People per LGA", "", "| LGA | Peoples |", "|---|---|"]
    for l, ps in per.items():
        L.append(f"| {l.replace('-', ' ').title()} | {', '.join(ps)} |")
    L += ["", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_093b_jigawa_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_093b_jigawa_peoples_REVIEW.md", "w").write(report())
    lg = [r for r in RELATIONS if "lga:jigawa" in r["to"] and r["frm"].startswith("@ethnic")]
    print(f"relations={len(RELATIONS)} people-LGA={len(lg)} LGAs={len({r['to'] for r in lg})}")
