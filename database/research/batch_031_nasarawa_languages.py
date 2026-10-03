"""
Research batch 031 — Nasarawa (Phase 3, first batch): the languages of Nasarawa State and the LGAs
where Roger Blench's Atlas of Nigerian Languages (2020, Internet Archive copy) places them.
Researched 2026-09-26. Peoples (ethnic groups) are a separate batch with ethnographic sources.

Method: the Atlas PDF was re-extracted column by column and every entry naming a Nasarawa LGA was
read in full (see data/atlas_nasarawa_blocks.txt); only what the entry states is used.

Caveats recorded on every LGA link:
  * The Atlas often names the LGA as it was when the data were collected. Several Nasarawa LGAs
    (Wamba, Kokona, Karu, Toto, Doma, Keana, Obi, Nasarawa Eggon) were created later from older
    ones, so "Akwanga LGA" may today be Wamba, "Nasarawa LGA" may be Toto, and so on.
  * Atlas errors not copied: Shendam and Langtang (Plateau State) listed under Nasarawa for Goemai
    and Kororofa; "Kauru LGA" (Kaduna) for Nyankpa; "Akwanga West LGA", which does not exist (Nko).
Speaker figures are those the Atlas cites, with their dates; they are given in the text only.
Also: Basa-Makurdi placed in the Kainji branch (Atlas 40.c) — in fix_031, with Oring-style handling.
"""
import json, re, sys

ACCESSED = "2026-09-26"
SOURCES = {
    "GLIDX": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Glottolog language index (resourcemap)", organisation="Glottolog",
                  url="https://glottolog.org/resourcemap.json?rsc=language", verification_status="verified",
                  notes="Index of languoids with names and map points; used to add Glottocodes (matched by name, map points checked)."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf",
                  organisation="Roger Blench", verification_status="verified", notes="Reused."),
}
BR = {  # new branches: key -> (name, description)
    "plateau": ("Plateau", "Plateau is a large group of Benue-Congo languages spoken mainly in central Nigeria, in Plateau, Nasarawa and Kaduna states. Roger Blench's Atlas of Nigerian Languages divides it into subgroups such as Eggonic, Ninzic, Alumic, Koro and a Southern group."),
    "nupoid": ("Nupoid", "Nupoid is a group of Benue-Congo languages of central Nigeria that includes Nupe, Gbagyi, Gbari, Gade and Ebira, according to Roger Blench's Atlas of Nigerian Languages."),
    "west-chadic": ("West Chadic", "West Chadic is a branch of the Chadic languages (Afro-Asiatic family). Roger Blench's Atlas of Nigerian Languages places Hausa and Gwandara in its Hausa group, Goemai in its Bole–Ngas group and Karfa in its Ron group."),
    "kainji": ("Kainji", "Kainji is a group of Benue-Congo languages of north-central Nigeria. Roger Blench's Atlas of Nigerian Languages places the Basa languages (Basa-Gurara, Basa-Benue and Basa-Makurdi) in its Western Kainji subgroup."),
}
# key: (name, parent, class text, other names, [lgas], extra-state note, speakers text, extra)
LANG = {
 "ake": ("Ake", "plateau", "Plateau (Southern; Eggonic)", "Akye, Aike", ["lafia"], "", "about 3,000 (Blench 1999)", ""),
 "alago": ("Alago", "@languages:idomoid", "Idomoid", "Arago; Idoma Nokwu", ["awe", "lafia"], "", "at least 100,000 (Blench 2017)",
           "Its varieties are named after the towns of Agwatashi, Assaikio, Doma and Keana."),
 "alumu-tesu": ("Alumu–Tesu", "plateau", "Plateau (Alumic)", "Arum–Chessu", ["akwanga"], "",
                "Alumu (Arum) about 5,000 in seven villages and Tesu (Chessu) about 1,000 in two villages (Blench 1999)", "It is a cluster of two languages, Alumu (Arum) and Tesu (Chessu)."),
 "ashe": ("Ashe", "plateau", "Plateau (Koro)", "Ache; Koron Ache", ["karu"], "It is also spoken in Kagarko LGA of Kaduna State.",
          "35,000 including Tinor-Myamya (Barrett 1972); eight villages between Katugal and Kubacha (2008)", "The Ashe share their name for themselves with the Tinor-Myamya, and the Atlas notes that this name is the origin of the term Ejar."),
 "bu-ningkada": ("Bu-Ningkada", "plateau", "Plateau (Ninzic)", "Jidda, Ibut; Nakare", ["akwanga"], "", "not given", "It is a cluster of Bu and Ningkada; its varieties include Jida and Abu."),
 "eggon": ("Eggon", "plateau", "Plateau (Eggonic)", "Egon; Mada Eggon, Hill Mada", ["akwanga", "nasarawa-eggon", "lafia"], "", "52,000 (Welmers 1971)",
           "About 25 dialects are recognised locally, though their status is unclear. The New Testament was published in Eggon in 1975."),
 "eloyi": ("Eloyi", "@languages:idomoid", "Plateau or Idomoid (classification uncertain)", "Afo, Epe, Aho, Afu", ["nasarawa", "awe"], "It is also spoken in Otukpo LGA of Benue State.",
           "20,000 (Mackay 1964); 25,000 (SIL)", "The Atlas leaves its place between Plateau and Idomoid open."),
 "gade": ("Gade", "nupoid", "Nupoid", "Gede", ["nasarawa"], "It is also spoken in the Federal Capital Territory.", "60,000 (Sterk 1977)", ""),
 "gbagyi": ("Gbagyi", "nupoid", "Nupoid (Gwari)", "Ibagyi, Gbagye; East Gwari", ["keffi", "nasarawa"], "It is also spoken in Niger and Kaduna states and the Federal Capital Territory.",
            "200,000 including Gbari (1952); 250,000 (1985)", ""),
 "gbari": ("Gbari", "nupoid", "Nupoid (Gwari)", "West Gwari, Gwari Yamma", ["nasarawa"], "It is also spoken in Niger and Kaduna states and the Federal Capital Territory.", "200,000 including Gbagyi (1952)", ""),
 "goemai": ("Goemai", "west-chadic", "West Chadic (Bole–Ngas group; Goemaic)", "Ankwai, Ankwe", ["awe", "lafia"], "The Atlas also lists Shendam, which is in Plateau State.",
            "80,000 (SIL 1973)", ""),
 "gwandara": ("Gwandara", "west-chadic", "West Chadic (Hausa group)", "", ["nasarawa", "keffi", "lafia", "akwanga"], "It is also spoken in Niger and Kaduna states and the Federal Capital Territory.",
              "30,000 (SIL 1973)", "The Atlas names its varieties Gwandara Karashi (central), Gwandara Koro (western), Kyan Kyar (southern), Toni (eastern), Gitata and Nimbia."),
 "hasha": ("Hasha", "plateau", "Plateau (Alumic)", "Iyashi, Yashi", ["akwanga"], "", "about 3,000 (Blench estimate, 1999)", ""),
 "idun": ("Idun", "plateau", "Plateau (Koro; Nyankpa–Idun cluster)", "Adong; Jaba Lungu", ["karu"], "It is also spoken in Jema'a and Jaba LGAs of Kaduna State.", "twenty-one villages (2008)", ""),
 "jili": ("Jili (Migili)", "plateau", "Plateau (Southern group; Jilic)", "Migili, Megili; Lijili; Koro of Lafia", ["lafia", "awe"], "The Atlas names the state as Plateau, as it was before Nasarawa State was created in 1996.",
          "50,000 (UBS 1985)", "The New Testament appeared in 1987."),
 "karfa": ("Karfa", "west-chadic", "West Chadic (Ron group)", "Kerifa", ["akwanga"], "", "800 (SIL 1973)", ""),
 "mada": ("Mada", "plateau", "Plateau (Ninzic)", "Yidda", ["akwanga", "kokona", "keffi"], "It is also spoken in Jema'a LGA of Kaduna State.", "30,000 (SIL 1973)",
          "It has northern and western dialect clusters, and the New Testament appeared in 2000."),
 "mama": ("Mama", None, "Bantu (Jarawan)", "Kwarra, Kantana", ["akwanga"], "", "20,000 (SIL 1973)", ""),
 "ninzo": ("Ninzo", "plateau", "Plateau (Ninzic)", "Ninzam, Ninzom; Gbhu", ["akwanga"], "It is also spoken in Jema'a LGA of Kaduna State.", "about 50,000 (Blench 2003)", ""),
 "nko": ("Nko", "plateau", "Plateau (Ninzic; Mada cluster)", "Agyaga", [], "The Atlas places it in a single village about 15 km south-west of Nunku, north of Akwanga, but names an 'Akwanga West LGA' that does not exist, so no LGA is linked.",
         "about 1,000 (2008 estimate)", ""),
 "numbu-gbantu-nunku": ("Numbu–Gbantu–Nunku", "plateau", "Plateau (Ninzic)", "Gwanto", ["akwanga"], "It is also spoken in Jema'a LGA of Kaduna State.", "15,000 (SIL)",
                        "It is a cluster whose members include Numbu, Gbantu (Gwanto) and Nunku."),
 "rindre": ("Rindre", "plateau", "Plateau (Ninzic)", "Rendre, Rindiri, Lindiri; Wamba, Nungu", ["akwanga"], "", "25,000 (SIL)",
            "The Atlas gives Wamba and Nungu as other names for the language; its data place it in Akwanga LGA, from which Wamba LGA was later created."),
 "toro": ("Toro", "plateau", "Plateau (Alumic)", "Turkwam", ["akwanga"], "", "about 2,000 (Blench 1999)",
          "The Toro live in one large village, Turkwam, about two kilometres south-east of Kanja on the Wamba–Fadan Karshi road."),
 "ebira": ("Ebira", "nupoid", "Nupoid (Ebira cluster)", "Igbirra, Igbira, Egbira", ["nasarawa"], "It is spoken mainly in Kogi and Edo states; in Nasarawa the Atlas names Toto and Umaisha towns, which are now in Toto LGA.",
           "about 1 million in all (1989)", ""),
}
# Glottocodes matched by name in Glottolog's language index (map points checked to lie in or near Nasarawa).
# Rindre takes the code of "Nungu" (one of its other names in the Atlas); Jili = Glottolog "Lijili".
GLOTTO = {"ake": "akee1238", "alago": "alag1242", "alumu-tesu": "alum1247", "ashe": "ashe1269", "bu-ningkada": "buuu1244", "eggon": "eggo1239",
          "gade": "gade1242", "gbagyi": "gbag1258", "gbari": "gbar1246", "goemai": "goem1240", "gwandara": "gwan1268", "hasha": "hash1238",
          "jili": "liji1238", "mada": "mada1282", "mama": "mama1272", "ninzo": "ninz1246", "nko": "nkoo1238", "numbu-gbantu-nunku": "numa1252",
          "rindre": "nung1292", "toro": "toro1249", "ebira": "ebir1243"}
CAVEAT = "The Atlas may name the LGA as it was when its data were collected; several Nasarawa LGAs were created later from older ones."


def text(k):
    name, parent, cls, alt, lgas, extra_state, spk, extra = LANG[k]
    lga_names = {"akwanga": "Akwanga", "awe": "Awe", "karu": "Karu", "keffi": "Keffi", "kokona": "Kokona", "lafia": "Lafia", "nasarawa": "Nasarawa",
                 "nasarawa-eggon": "Nasarawa Eggon"}
    where = (f" in {', '.join(lga_names[l] for l in lgas[:-1])}{' and ' if len(lgas) > 1 else ''}{lga_names[lgas[-1]]} LGA{'s' if len(lgas) > 1 else ''} of Nasarawa State") if lgas else " in Nasarawa State"
    t = f"{name} is a language spoken{where}, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as {cls}."
    if alt: t += f" Other names include {alt}."
    if extra_state: t += " " + extra_state
    if spk and spk != "not given": t += f" The Atlas cites speaker figures of {spk}."
    if extra: t += " " + extra
    return t


RECORDS = []
for k, (name, desc) in BR.items():
    RECORDS.append(dict(key=f"br_{k}", table="languages", evidence="single_reliable_source", level="well_documented",
                        fields=dict(lang_type="branch", name=name, slug=k, summary=desc.split(". ")[0] + ".", description=desc),
                        srcs=[("ATLAS", f"{name}: classification")]))
RELATIONS = []
for k, v in LANG.items():
    name, parent, cls, alt, lgas, *_ = v
    f = dict(lang_type="language", name=name, slug=k, summary=text(k).split(". ")[0] + ".", description=text(k))
    if k in GLOTTO: f["glottocode"] = GLOTTO[k]
    if parent:
        f["parent_id"] = parent if parent.startswith("@") else f"@key:br_{parent}"
    RECORDS.append(dict(key=k, table="languages", evidence="single_reliable_source", level="well_documented", fields=f,
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers")] + ([("GLIDX", f"Glottocode {GLOTTO[k]}")] if k in GLOTTO else [])))
    RELATIONS.append(dict(frm=k, type="spoken_in", to="@admin_units:state:nasarawa", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                          notes="Blench, Atlas of Nigerian Languages (2020)."))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=f"@admin_units:lga:nasarawa/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                              notes=f"Placed in {l.replace('-', ' ').title()} LGA by Blench's Atlas (2020). {CAVEAT}"))
# Idoma (existing record): the Atlas places it in Nasarawa and Awe LGAs of Nasarawa State.
for l in ["nasarawa", "awe"]:
    RELATIONS.append(dict(frm="@languages:idoma", type="spoken_in", to=f"@admin_units:lga:nasarawa/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                          notes=f"The Atlas's Idoma cluster lists Nasarawa and Awe LGAs of Nasarawa State. {CAVEAT}"))

GAPS = [
    ("Atlas LGAs and today's LGAs", "Much Atlas data predates the Nasarawa LGAs created in 1991–1996 (Wamba, Kokona, Karu, Toto, Doma, Keana, Obi, Nasarawa Eggon). Which current LGA each language is in needs the state's own data or recent surveys."),
    ("Kororofa (Wapan) and Pan in Nasarawa", "The Atlas lists Wapan (Jukun) and the Pan cluster in Lafia and Awe 'with precise areas uncertain', mixed with Plateau LGAs. Not linked."),
    ("Nyankpa (Yeskwa)", "The Atlas places it in 'Nasarawa State, Kauru LGA' — Kauru is in Kaduna State; probably Karu. Not linked until checked."),
    ("Languages of Doma, Keana, Obi, Toto, Wamba and Kokona", "The Atlas names these LGAs rarely or not at all (it names Alago towns Doma and Keana). Needs the peoples batch and LGA profiles."),
    ("Hausa, Fulfulde, Kanuri and Tiv", "Widely spoken in the state; not taken from the Atlas here (Tiv in Lafia is already recorded). The peoples batch will cover them."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=[], gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Nasarawa (Phase 3): 24 languages from Blench's Atlas with the LGAs it gives, 4 language branches; Idoma in Nasarawa and Awe LGAs.")


def report():
    L = ["# Research batch 031 — Nasarawa: the languages and where they are spoken", "",
         f"Researched {ACCESSED}. First Nasarawa batch (Phase 3). Created in review; published only after your approval. The **peoples** come in the next batch, from ethnographic and official sources.", "",
         "**Source:** Roger Blench, *An Atlas of Nigerian Languages* (2020), Tier 2, via the Internet Archive copy (Blench's site blocks downloads). The PDF was re-extracted column by column, and every entry naming a Nasarawa LGA was **read in full**. Only what each entry says is used.", "",
         f"- **{len(LANG)} languages** (new records), each with its classification, other names, the Atlas's speaker figures (with their dates), and the Nasarawa LGAs it names.",
         f"- **4 new branches** to hold them: {', '.join(v[0] for v in BR.values())}. Alago and Eloyi go under the existing Idomoid.",
         f"- **{len(RELATIONS)} links**: each language to Nasarawa State and to its LGAs; the existing Idoma language to Nasarawa and Awe LGAs.",
         "- **A caveat on every LGA link.** The Atlas often names the LGA as it was when its data were collected. Wamba, Kokona, Karu, Toto, Doma, Keana, Obi and Nasarawa Eggon were created later from older LGAs, so \"Akwanga LGA\" may today be Wamba.",
         "- **Atlas errors not copied:**",
         "  - Shendam and Langtang (Plateau State) listed under Nasarawa",
         "  - \"Kauru LGA\" (in Kaduna) for Nyankpa",
         "  - an \"Akwanga West LGA\" that doesn't exist",
         "- All pages are short, so they stay noindex (sitemap unchanged).", "",
         "## Languages and LGAs", "", "| Language | Classification (Atlas) | Nasarawa LGAs (Atlas) |", "|---|---|---|"]
    for k, (name, parent, cls, alt, lgas, *_r) in LANG.items():
        L.append(f"| {name} | {cls} | {', '.join(l.replace('-', ' ').title() for l in lgas) or '— (see note)'} |")
    L += ["", "## Example texts", "", "> " + text("eggon"), "", "> " + text("rindre"), "", "> " + text("jili"), "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_031_nasarawa_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_031_nasarawa_languages_REVIEW.md", "w").write(report())
    print(f"languages={len(LANG)} branches={len(BR)} relations={len(RELATIONS)}")
