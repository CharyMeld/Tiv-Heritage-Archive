"""
Research batch 081 — Gombe (Phase 3, first batch): the languages of Gombe State and the LGAs where Roger Blench's
Atlas of Nigerian Languages (2020) places them. Researched 2026-10-02. Pattern: batches 063, 069 and 075.

Method: the column-split Atlas text (pages 30–135) was searched for 'Gombe', the eleven Gombe LGA names and the
pre-1996 names (Ako, Bajoga, Dukku, Kwami, Nafada, Yamaltu, Funakai); 23 entries matched and were read by hand
(data/atlas_gombe_blocks.txt). Gombe was formed from part of Bauchi State on 1 October 1996 (Wikipedia), so the Atlas
still writes 'Bauchi State' for Kwaami (Kwami LGA), Jara (Ako LGA), Kutto (Bajoga LGA) and the Dukku parts of Bole and
Ngamo: these are linked to today's Gombe LGAs with the Atlas wording quoted. Bajoga is now the headquarters of
Funakaye LGA (Wikipedia, Funakaye); Nafada, given by the Atlas as a district of Dukku LGA, is now an LGA.

Not linked: Hausa (no language record; the Atlas names Gombe among the states where it is a first language); Nyam,
Kholok and Piya–Kwonci (Taraba); Labɨr (Bauchi only, 'south of the Bauchi–Gombe road'). Tera's location line also has
'Gombe State, Gombi LGA' — Gombi is an Adamawa LGA, so that phrase is not used; its Nyimatli member's LGAs are.

Wikipedia's Gombe State article has a languages-by-LGA table (from Ethnologue 22); it is used here only for Billiri
(Tangale), which the Atlas names as a dialect but not as an LGA.

New languages (10) with Glottocodes from Glottolog's per-languoid pages: Bangjinge (Bangwinji bang1348), Burak
(bura1271), Cen Tuum (Glottolog 'Jalaa', cent2045, an isolate), Goji (Glottolog 'Kushi', kush1236), Jan Awei
(jana1236), Ma (Glottolog 'Kamo', kamo1254), Pero (pero1241), Tula (tula1252), Yebu (Glottolog 'Awak', awak1250),
Kwaami (kwaa1269).
"""
import json, sys
import batch_063_borno_languages as B63

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf",
                  verification_status="verified", notes="Reused. Gombe entries read (data/atlas_gombe_blocks.txt)."),
    "GLIDX": B63.SOURCES["GLIDX"],
    "WGOM": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Gombe State", organisation="Wikipedia", url=W("Gombe State"),
                 verification_status="needs_corroboration",
                 notes=f"Formed from part of Bauchi State on 1 October 1996; languages-by-LGA table citing Ethnologue 22 (Akko: Fulani, Jukun, Tangale; Balanga: Bangwinji, Centúúm, Dadiya, Dera, Dikaka, Dza, Kyak, Longuda, Moo, Tangale, Tso, Waja; Billiri: Tangale; Dukku: Fulani, Bolewa; Funakaye: Fulani; Kaltungo: Awak, Tangale, Tula, Kamo, Yuwar, Cham; Kwami: Fulani, Kanuri; Nafada: Fulani, Bolewa; Shongom: Tangale, Kushi, Moo, Loo, Wurkun, Pipero; Yamaltu-Deba: Tera, Fulani). Accessed {ACCESSED}."),
}
LGA = {"akko": "Akko", "balanga": "Balanga", "billiri": "Billiri", "dukku": "Dukku", "funakaye": "Funakaye", "gombe": "Gombe", "kaltungo": "Kaltungo",
       "kwami": "Kwami", "nafada": "Nafada", "shomgom": "Shongom", "yamaltu-deba": "Yamaltu/Deba"}
S, B, L = "spelling_variant", "endonym", "alternative"
WC, AD, JK = "@languages:west-chadic", "@languages:adamawa", "@languages:jukunoid"
BT = "Chadic (West A: Bole–Ngas major group, Bole–Tangale group)"
WAJA = "Adamawa–Ubangi (Adamawa: Waja group)"
# key: (name, parent, class, [(name, type, field)], [lgas], note, speakers, extra, glottocode)
LANG = {
 "bangjinge": ("Bangjinge", AD, WAJA, [("Bangunji", S, "1.A"), ("Bangunje", S, "1.A"), ("Bangwinji", S, "1.A"), ("Báŋjìŋè", B, "1.B")], ["shomgom"],
               "It is spoken in about 25 villages; its dialects are Nabang and Kaloh, and the orthography is based on Nabang.", "8,000 (CAPRO 1995)",
               "A reading and writing book appeared in 2007, and the Gospel of Luke has been translated. Glottolog lists it as Bangwinji.", "bang1348"),
 "burak": ("Burak", AD, "Adamawa–Ubangi (Adamawa: Bikwin group)", [("Ɓuurak", B, "1.B"), ("'Yele", L, "2.A")], ["shomgom"],
           "It is spoken at Burak town and in about 25 villages; Tadam village speaks a highly distinctive form. The Atlas notes that 'Shongom', the LGA's name, is also used for the people.",
           "4,000 (1992 estimate)", "A reading and writing book appeared in 2008.", "bura1271"),
 "cen-tuum": ("Cen Tuum", None, "a language isolate", [("Centúúm", B, "1.B"), ("Jalaa", L, "2.C")], ["balanga"],
              "It was spoken at Cham town by a small number of old people among the Dijim, all of whom are fluent in Dijim. The Atlas records it as moribund or extinct: a search in 2010 failed to find any speakers.",
              "", "Glottolog lists it as Jalaa (cent2045), the Dijim name for it.", "cent2045"),
 "goji": ("Goji", WC, BT, [("Kushi", L, "2.A"), ("Kushe", L, "2.A"), ("Chong'e", L, "2.B")], ["shomgom"],
          "It is spoken in about twenty villages.", "4,000 (SIL 1973) and 5,000 (1990)",
          "A reading and writing book appeared in 2006 and New Testament extracts in 2007. Glottolog lists it as Kushi.", "kush1236"),
 "jan-awei": ("Jan Awei", JK, "Benue–Congo (Central Jukunoid)", [], [],
              "The Atlas places it west of the Muri mountains, north of the Benue, without a precise location.", "12 (1997, with a question mark)", "", "jana1236"),
 "ma": ("Ma", AD, WAJA, [("Kamo", L, "2.A"), ("Kamu", L, "2.A")], ["kaltungo", "akko"], "", "3,000 (SIL)",
        "A reading and writing book appeared in 2006 and New Testament extracts in 2007. Glottolog lists it as Kamo.", "kamo1254"),
 "pero": ("Pero", WC, "Chadic (West A: Bole–Ngas major group, Bole group)", [("Walo", L, "1.A"), ("Péerò", B, "1.B"), ("Filiya", L, "2.A")], ["shomgom"],
          "It is spoken around Filiya, in three main villages, Gwandum, Gundale and Filiya, with dialects tied to each.", "6,664 (Meek 1925) and 20,000 (SIL 1973)",
          "Primers appeared in 1931, and scripture portions and other literature in 1936–40.", "pero1241"),
 "tula": ("Tula", AD, WAJA, [("Ture", L, "1.A"), ("Kitule", B, "1.B")], ["kaltungo"],
          "Tula is 30 km east of Billiri; the language is spoken in about fifty villages, and its dialects are Baule, Wangke (used for literacy) and Yiri.",
          "19,209 (1952), 12,204 (1961–2), 19,000 (SIL 1973), and perhaps 100,000 (estimate)", "Reading and writing books appeared in 1991 and 2001, and the Gospel of John in 1929.", "tula1252"),
 "yebu": ("Yebu", AD, WAJA, [("Yěbù", B, "1.B"), ("Awok", L, "2.A"), ("Awak", L, "GL")], ["kaltungo"],
          "It is spoken 10 km north-east of Kaltungo.", "2,035 (1962)", "A reading and writing book appeared in 2007. Glottolog lists it as Awak.", "awak1250"),
 "kwaami": ("Kwaami", WC, BT, [("Kwami", S, "1.A"), ("Kwom", S, "1.A"), ("Kwáámì", B, "1.B"), ("Komawa", L, "2.A")], ["kwami"],
            "Its dialects are Kafarati and Ɗolli. The Atlas gives 'Bauchi State, Kwami LGA'; Kwami has been in Gombe State since 1996.", "10,000 (1990)", "", "kwaa1269"),
}
FIELD = dict(B63.FIELD)
CAVEAT = "The Atlas may name an LGA as it was when its data were collected; Gombe State was formed from Bauchi in 1996, and Billiri, Funakaye, Nafada, Shongom and Yamaltu/Deba are later LGAs."


def text(k):
    name, parent, cls, names, lgas, note, spk, extra, g = LANG[k]
    where = (f" in {', '.join(LGA[l] for l in lgas[:-1])}{' and ' if len(lgas) > 1 else ''}{LGA[lgas[-1]]} LGA{'s' if len(lgas) > 1 else ''} of Gombe State") if lgas else " in Gombe State"
    t = f"{name} is a language spoken{where}, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as {cls}."
    alt = [n for n, _, f in names if n != name and f not in ("GL", "member")]
    if alt: t += f" Other names include {', '.join(alt[:6])}."
    if note: t += " " + note
    if spk: t += f" The Atlas cites speaker figures of {spk}."
    if extra: t += " " + extra
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for k, (name, parent, cls, names, lgas, note, spk, extra, g) in LANG.items():
    f = dict(lang_type="language", name=name, slug=k, summary=text(k).split(". ")[0] + ".", description=text(k))
    if parent: f["parent_id"] = parent
    if g: f["glottocode"] = g
    RECORDS.append(dict(key=k, table="languages", evidence="single_reliable_source", level="well_documented", fields=f,
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers")] + ([("GLIDX", f"Glottocode {g}")] if g else [])))
    RELATIONS.append(dict(frm=k, type="spoken_in", to="@admin_units:state:gombe", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes="Blench's Atlas."))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=f"@admin_units:lga:gombe/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=CAVEAT))
    for n, t, fld in names:
        if n == name:
            continue
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=f"Blench's Atlas (2020), {('field ' + fld + ': ') if fld[0].isdigit() else ''}{FIELD[fld]}." if fld != "GL" else "Glottolog's name for the language.",
                          srcs=["ATLAS"] if fld != "GL" else ["GLIDX"]))
# existing language: ([gombe lgas], note, source, level)
EXISTING = {
    "tangale": (["kaltungo", "akko"], "Atlas: 'Gombe State, Kaltungo, Alkaleri and Akko LGAs' (Alkaleri is in Bauchi, linked in batch 075); dialects Ture, Kaltungo, Shongom, Billiri.", "ATLAS", "well_documented"),
    "dadiya": (["balanga"], "Atlas: 'Gombe State, Balanga LGA, Taraba State, Karim Lamido LGA and Adamawa State, Lamurde LGA'.", "ATLAS", "well_documented"),
    "dijim-bwilim": (["balanga"], "Atlas: 'Gombe State, Balanga LGA, Adamawa State, Lamurde LGA'; the Dijim member is spoken at Cham.", "ATLAS", "well_documented"),
    "longuda": (["balanga"], "Atlas: 'Adamawa State, Guyuk LGA; Gombe State, Balanga LGA'.", "ATLAS", "well_documented"),
    "loo": (["kaltungo"], "Atlas: 'Kaltungo LGA, Gombe State, Taraba State, Karim Lamido LGA'.", "ATLAS", "well_documented"),
    "tsobo": (["kaltungo"], "Atlas: 'Gombe State, Kaltungo LGA, Adamawa State, Numan LGA'.", "ATLAS", "well_documented"),
    "wiyaa": (["balanga", "kaltungo"], "Atlas: 'Gombe State, Balanga and Kaltungo LGAs, Waja district. Taraba State, Bali LGA'.", "ATLAS", "well_documented"),
    "tera": (["akko", "gombe", "kwami", "funakaye", "yamaltu-deba"], "Atlas, Nyimatli (Yamaltu) member of the Tera cluster: 'Gombe State, Ako, Gombe, Kwami, Funakai, Yamaltu LGAs; Borno State, Ɓayo LGA'.", "ATLAS", "well_documented"),
    "jara": (["akko"], "Atlas: 'Borno State, Biu LGA; Bauchi State, Ako LGA' — Akko has been in Gombe State since 1996.", "ATLAS", "well_documented"),
    "kutto": (["funakaye"], "Atlas: 'Bauchi State, Bajoga LGA, Yobe State, Gujba LGA' — Bajoga is now the headquarters of Funakaye LGA, Gombe State (Wikipedia, Funakaye).", "ATLAS", "reported"),
    "bole": (["dukku"], "Atlas: 'Bauchi State, Dukku, Alkaleri, and Darazo LGAs' — Dukku has been in Gombe State since 1996.", "ATLAS", "well_documented"),
    "ngamo": (["nafada"], "Atlas: 'Bauchi State, Darazo LGA, Darazo district and Dukku LGA, Nafada district' — Nafada is now an LGA of Gombe State.", "ATLAS", "reported"),
}
for lang, (lgas, note, src, lvl) in EXISTING.items():
    RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to="@admin_units:state:gombe", source=src, evidence="single_reliable_source", level="well_documented", notes=note))
    for l in lgas:
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=f"@admin_units:lga:gombe/{l}", source=src, evidence="single_reliable_source", level=lvl, notes=note))
RELATIONS.append(dict(frm="@languages:tangale", type="spoken_in", to="@admin_units:lga:gombe/billiri", source="WGOM", evidence="single_reliable_source", level="reported",
                      notes="Wikipedia's languages-by-LGA table (citing Ethnologue 22) gives Tangale for Billiri; the Atlas lists Billiri as a Tangale dialect."))
GAPS = [
    ("Gombe: Hausa and Fulfulde", "The Atlas names Gombe among the states where Hausa is a first language, but Hausa is not yet a language record; Fulfulde (with its Gombe dialect) is not placed by LGA in the Atlas. Wikipedia's table gives Fulani for six LGAs (Akko, Dukku, Funakaye, Kwami, Nafada, Yamaltu-Deba) — for the peoples batch."),
    ("Gombe: languages in Wikipedia's table not yet matched", "Dera, Dikaka, Dza, Kyak, Moo, Yuwar, Wurkun and Cham (Balanga, Kaltungo, Shongom) are listed by Wikipedia (Ethnologue 22) but not placed in Gombe by the Atlas entries read; they need matching to Atlas names before linking."),
    ("Gombe: Tera's 'Gombi LGA'", "The Tera cluster's location line reads 'Gombe State, Gombi LGA'; Gombi is an Adamawa LGA. Not used; the Nyimatli member's LGA list is used instead."),
    ("Gombe: Jan Awei", "Location unknown beyond 'west of the Muri mountains, north of the Benue'; 12 speakers (1997, uncertain)."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Gombe languages (Blench Atlas): {len(LANG)} languages, LGA links, other names; Gombe links for {len(EXISTING)} existing languages.")


def report():
    per = {l: [] for l in LGA}
    for k, v in LANG.items():
        for l in v[4]:
            per[l].append(v[0])
    for lang, (lgas, *_r) in EXISTING.items():
        for l in lgas:
            per[l].append(lang.replace("-", "–").title() + " (existing)")
    per["billiri"].append("Tangale (existing; Wikipedia)")
    L = ["# Research batch 081 — Gombe: the languages", "",
         f"Researched {ACCESSED}. Phase 3, the first Gombe batch. Created in review; published only after your approval.", "",
         "## Summary", "",
         f"- **{len(LANG)} new languages** from Blench's Atlas, all with Glottocodes. They go under the existing West Chadic, Adamawa and Jukunoid branches; Cen Tuum is an isolate.",
         f"- **{len(EXISTING)} existing languages get Gombe links:** Tangale, Dadiya, Dijim–Bwilim, Longuda, Loo, Tsobo, Wiyaa (Waja), Tera, Jara, Kutto, Bole and Ngamo.",
         "- **Old state names:** Gombe was formed from Bauchi in 1996, and the Atlas still writes 'Bauchi State' for Kwami, Ako (Akko), Bajoga and Dukku. These are linked to today's Gombe LGAs, with the Atlas wording quoted.",
         "  - Kutto (Bajoga → Funakaye) and Ngamo (Nafada district → Nafada LGA) are *reported*, because they rest on a rename.",
         "- **Billiri–Tangale** comes from Wikipedia's languages-by-LGA table and is *reported*.",
         "- **Cen Tuum** is moribund or extinct; a 2010 search found no speakers.",
         f"- **{len(RELATIONS)} links** and **{len(NAMES)} other names**.", "",
         "## Languages per LGA", "", "| LGA | Count | Languages |", "|---|---|---|"]
    for l, ns in per.items():
        L.append(f"| {LGA[l]} | {len(ns)} | {', '.join(ns) or '—'} |")
    L += ["", "## The new languages", ""] + [f"**{LANG[k][0]}** ({LANG[k][8] or 'no Glottocode'}). {text(k)}\n" for k in LANG]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_081_gombe_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_081_gombe_languages_REVIEW.md", "w").write(report())
    print(f"languages={len(LANG)} relations={len(RELATIONS)} names={len(NAMES)} glotto={sum(1 for v in LANG.values() if v[8])}")
