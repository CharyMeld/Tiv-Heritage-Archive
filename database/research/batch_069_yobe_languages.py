"""
Research batch 069 — Yobe (Phase 3, first batch): the languages of Yobe State and the LGAs where Roger Blench's
Atlas of Nigerian Languages (2020) places them. Researched 2026-10-01. Pattern: batch 063 (Borno).

Method: the 502 column-split Atlas entries (batch 063) were searched for 'Yobe' and the 17 Yobe LGA names; 28 matched
and were read by hand (data/atlas_yobe_blocks.txt). False matches removed: entries whose only hit was a classification
label ('Bade group', 'Bade/Warji') or a Bauchi/Taraba/Plateau/Edo location (Batu, Ciwogai, Diri, Fam, Fɨran, Ghotuọ,
Kariya, Ma, Maghdi, Mashi, Nyankpa, Pa'a, Siri, Warji, Zangwal), and the extinct Bade-group languages of Jigawa
(Auyokawa, Shira, Teshena).

Boundaries: Yobe was part of Borno until 1991, and the Atlas writes 'Borno State' for Bade, Fika and Damaturu LGAs
(Bade, Ɗuwai, Bole, Ngamo, Ngizim) and lists Kanuri under old Borno LGAs. These LGA names are unambiguous and are
linked to Yobe, quoting the Atlas. Parts of these languages in Bauchi, Gombe and Jigawa are not linked here.
Maaka: the Atlas places it in 'Gujba LGA. Gulani and Bara towns' — linked to Gujba as written (Gulani is now an LGA
of its own; gap).

Glottocodes from Glottolog's language index, map points checked: Bade bade1248, Ɗuwai duwa1244, Ngizim ngiz1242,
Bole nucl1695, Ngamo ngam1282, Karekare kare1348, Kutto kutt1236, Maaka maak1236, Libyan Arabic liby1240 (Uled
Suliman), Sudanese Arabic suda1236 (Baggara; the Atlas's own 1.A name).
"""
import json, sys
import batch_063_borno_languages as B63

ACCESSED = "2026-10-01"
SOURCES = {
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf",
                  verification_status="verified", notes="Reused. Yobe entries read in full (data/atlas_yobe_blocks.txt)."),
    "GLIDX": B63.SOURCES["GLIDX"],
}
LGA = {"bade": "Bade", "bursari": "Bursari", "damaturu": "Damaturu", "fika": "Fika", "fune": "Fune", "geidam": "Geidam", "gujba": "Gujba", "gulani": "Gulani",
       "jakusko": "Jakusko", "karasuwa": "Karasuwa", "machina": "Machina", "nangere": "Nangere", "nguru": "Nguru", "potiskum": "Potiskum", "tarmua": "Tarmua",
       "yunusari": "Yunusari", "yusufari": "Yusufari"}
S, B, L = "spelling_variant", "endonym", "alternative"
WC = "@languages:west-chadic"
BADE = "Chadic (West branch B: Bade/Warji major group, Bade group)"
BOLE = "Chadic (West branch A: Bole–Ngas major group, Bole group)"
OLDB = "The Atlas writes 'Borno State' for this LGA, which has been in Yobe State since 1991."
# key: (name, parent, class text, [(name, type, field)], [lgas], note, speakers, extra, glottocode, link note)
LANG = {
 "bade": ("Bade", WC, BADE, [("Bedde", S, "1.A"), ("Gidgid", L, "2.B")], ["bade"],
          "It is also spoken in Hadejia LGA of Jigawa State. Its dialects are Western Bade (Magwaram), Southern Bade (Bade k-Aɗo) and Gashua Bade (Mazgarwa).",
          "31,933 including Ɗuwai and Ngizim (1952) and 100,000 (SIL 1973)", "Folktales were published in 1975.", "bade1248", OLDB),
 "duwai": ("Ɗuwai", WC, BADE, [("Duwai", S, "1.A"), ("Eastern Bade", L, "2.B")], ["bade"], "", "", "", "duwa1244", OLDB),
 "ngizim": ("Ngizim", WC, BADE, [("Ngezzim", S, "1.A")], ["damaturu"], "The Atlas describes it as vigorous, with Hausa as a second language.",
            "39,200 including Bade and Ɗuwai (1952) and 25,000 (Schuh 1972)", "", "ngiz1242", OLDB),
 "bole": ("Bole", WC, BOLE, [("Bòò Pìkkà", B, "1.B"), ("Bopika", B, "1.B"), ("Fika", L, "2.A"), ("Piika", L, "2.A"), ("Bolanci", L, "2.B")], ["fika"],
          "It is also spoken in Bauchi State (Dukku, Alkaleri and Darazo LGAs, as the Atlas gives them). Its dialects are Bara and Fika.",
          "32,000 (1952) and more than 100,000 (1990)", "Glottolog lists it as Bole (nucl1695).", "nucl1695", OLDB),
 "ngamo": ("Ngamo", WC, BOLE, [("Gamo", S, "1.A")], ["fika"], "It is also spoken in Darazo and Dukku LGAs, which the Atlas gives under Bauchi State.", "17,800 (1952)", "", "ngam1282", OLDB),
 "karekare": ("Karekare", WC, BOLE, [("Kerekere", S, "1.A"), ("Karaikarai", S, "1.A"), ("Kerikeri", S, "1.A")], ["fika"],
              "It is also spoken in Gamawa and Misau LGAs of Bauchi State. Its dialects are Western Jalalum, northern Pakaro and eastern Ngwajum.", "39,000 (1952)", "", "kare1348",
              "Atlas: 'Bauchi State, Gamawa and Misau LGAs, Yobe State, Fika LGA'."),
 "kutto": ("Kutto", WC, BOLE, [("Kupto", S, "1.A"), ("Kúttò", B, "1.B")], ["gujba"], "It is spoken in two villages, and also in Bajoga LGA, which the Atlas gives under Bauchi State.",
           "3,000 (1990 estimate)", "", "kutt1236", "Atlas: 'Bauchi State, Bajoga LGA, Yobe State, Gujba LGA'."),
 "maaka": ("Maaka", WC, BOLE, [("Magha", S, "1.A"), ("Maga", S, "1.A"), ("Maha", S, "1.A")], ["gujba"],
           "The Atlas places it at Gulani and Bara towns and their hamlets, north-east of the Dadin Kowa Reservoir, and names two dialects, Maaka (at Gulani) and Maha (at Vara).",
           "more than 4,000 (1990)", "", "maak1236", "Atlas: 'Yobe State, Gujba LGA. Gulani and Bara towns'. Gulani is now an LGA of its own; the Atlas's wording is kept."),
 "uled-suliman-arabic": ("Uled Suliman Arabic", "@languages:semitic", "Afroasiatic (Semitic), the Uled Suliman member of its Arabic cluster",
                         [("Libyan Arabic", L, "1.A"), ("Arabiyye", B, "1.B")], ["geidam", "yunusari"],
                         "The Atlas also names Mober (Mobbar LGA, Borno). The Uled Suliman, formerly seasonal migrants, are now based in north-eastern Borno, and their migrations extend south into Yobe and Jigawa as far as the Hadejia–Nguru wetlands; they are also in Chad and Niger.",
                         "perhaps 20,000 regularly moving through Nigeria", "The Atlas has no data on the Nigerian variety. Glottolog lists Libyan Arabic as liby1240.", "liby1240", OLDB),
 "baggara-arabic": ("Baggara Arabic", "@languages:semitic", "Afroasiatic (Semitic), the Baggara member of its Arabic cluster", [("Sudanese Arabic", L, "1.A"), ("Arabiyye", B, "1.B")], [],
                    "The Atlas places it in Yobe State without naming an LGA; it is also spoken in Chad and Sudan.", "",
                    "The Atlas has no data on the Nigerian variety and notes that the impact of the Boko Haram insurgency is unknown. Glottolog's Sudanese Arabic (suda1236) is linked, as the Atlas gives that name.", "suda1236", ""),
}
FIELD = B63.FIELD


def text(k):
    name, parent, cls, names, lgas, note, spk, extra, g, ln = LANG[k]
    where = (f" in {', '.join(LGA[l] for l in lgas[:-1])}{' and ' if len(lgas) > 1 else ''}{LGA[lgas[-1]]} LGA{'s' if len(lgas) > 1 else ''} of Yobe State") if lgas else " in Yobe State"
    t = f"{name} is a language spoken{where}, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as {cls}."
    alt = [n for n, _, f in names if n != name and f not in ("GL", "member")]
    if alt: t += f" Other names include {', '.join(alt[:6])}."
    if note: t += " " + note
    if spk: t += f" The Atlas cites speaker figures of {spk}."
    if extra: t += " " + extra
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for k, (name, parent, cls, names, lgas, note, spk, extra, g, ln) in LANG.items():
    f = dict(lang_type="language", name=name, slug=k, parent_id=parent, glottocode=g, summary=text(k).split(". ")[0] + ".", description=text(k))
    RECORDS.append(dict(key=k, table="languages", evidence="single_reliable_source", level="well_documented", fields=f,
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers"), ("GLIDX", f"Glottocode {g}")]))
    RELATIONS.append(dict(frm=k, type="spoken_in", to="@admin_units:state:yobe", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=ln or "Blench's Atlas."))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=f"@admin_units:lga:yobe/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=ln))
    for n, t, fld in names:
        if n == name:
            continue
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=f"Blench's Atlas (2020), field {fld}: {FIELD[fld]}.", srcs=["ATLAS"]))
RELATIONS.append(dict(frm="uled-suliman-arabic", type="spoken_in", to="@admin_units:lga:borno/mobbar", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                      notes="Atlas: 'Borno State, Geidam, Mober, Yunusari LGAs' (Mober = Mobbar, still in Borno)."))
RELATIONS.append(dict(frm="uled-suliman-arabic", type="spoken_in", to="@admin_units:state:borno", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                      notes="Atlas: 'now based in NE Borno'."))
EXISTING = [
    ("kanuri", ["nguru", "geidam", "damaturu", "fune", "gujba", "fika"], "Atlas: 'Borno State, Nguru, Geidam, Kukawa, Damaturu, Kaga, Konduga, Maiduguri, Mongumo, Fune, Gujba, Ngala, Bama, Fika and Gwoza LGAs' — the Yobe LGAs among them (in Borno until 1991)."),
    ("shuwa-arabic", [], "Atlas: 'Shuwa range widely across Borno and Yobe States on transhumance'; no Yobe LGA named."),
]
for lang, lgas, note in EXISTING:
    RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to="@admin_units:state:yobe", source="ATLAS", evidence="single_reliable_source",
                          level="well_documented" if lgas else "reported", notes=note))
    for l in lgas:
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=f"@admin_units:lga:yobe/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))

GAPS = [
    ("Yobe: LGAs with no language placed by name", "Bursari, Gulani, Jakusko, Karasuwa, Machina, Nangere, Potiskum, Tarmua and Yusufari have no Atlas language placed in them by name; most were created after the Atlas's sources (Gulani appears as a town for Maaka). A current source is needed."),
    ("Yobe: Maaka and Gulani", "The Atlas places Maaka in 'Gujba LGA. Gulani and Bara towns'; Gulani is now a separate LGA. Linked to Gujba as written."),
    ("Yobe: Hausa and Fulfulde", "Widely spoken but not placed in Yobe by name in the Atlas; Hausa is not yet a language record."),
    ("Yobe: Baggara Arabic", "Placed in 'Yobe State' only. Glottolog's Sudanese Arabic code is used because the Atlas gives that name; the Nigerian variety is undocumented."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Yobe languages (Blench Atlas, read in full): {len(LANG)} languages, LGA links, other names; Yobe links for Kanuri and Shuwa Arabic.")


def report():
    per = {l: [] for l in LGA}
    for k, v in LANG.items():
        for l in v[4]:
            per[l].append(v[0])
    for lang, lgas, _ in EXISTING:
        for l in lgas:
            per[l].append(lang.title() + " (existing)")
    L = ["# Research batch 069 — Yobe: the languages", "",
         f"Researched {ACCESSED}. Phase 3, the first Yobe batch. Created in review; published only after your approval.", "",
         "## Summary", "",
         f"- **{len(LANG)} languages**, all from Blench's Atlas, each with a Glottocode:",
         "  - the **Bade group**: Bade, Ɗuwai and Ngizim",
         "  - the **Bole group**: Bole (Fika), Ngamo, Karekare, Kutto and Maaka",
         "  - **two Arabic varieties**: Uled Suliman (Libyan) and Baggara (Sudanese)",
         "  They go under the existing West Chadic and Semitic branches.",
         "- **Method:** 28 Atlas entries mentioned Yobe or its LGAs; all were read by hand.",
         "  - Removed as false matches: classification labels, Bauchi-only entries, and three extinct Jigawa languages.",
         "  - **The Atlas writes 'Borno State'** for Bade, Fika and Damaturu (Yobe has been a separate state since 1991). Those entries are linked to Yobe, quoting the Atlas.",
         f"- **{len(RELATIONS)} links** and **{len(NAMES)} other names**.",
         "- **Existing languages:** Kanuri gets six Yobe LGAs, and Shuwa Arabic gets a state-level link.",
         "- **Nine LGAs have no language placed in them by name.** Most were created after the Atlas's sources; this is listed as a gap.", "",
         "## Languages per LGA (as the Atlas names them)", "", "| LGA | Count | Languages |", "|---|---|---|"]
    for l, ns in per.items():
        L.append(f"| {LGA[l]} | {len(ns)} | {', '.join(ns) or '—'} |")
    L += ["", "## The languages", ""] + [f"**{LANG[k][0]}** ({LANG[k][8]}). {text(k)}\n" for k in LANG]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_069_yobe_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_069_yobe_languages_REVIEW.md", "w").write(report())
    print(f"languages={len(LANG)} relations={len(RELATIONS)} names={len(NAMES)}")
