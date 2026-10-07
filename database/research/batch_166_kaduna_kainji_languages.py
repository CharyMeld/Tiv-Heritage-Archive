"""
Research batch 166 — Kaduna (Phase 3, first batch): the Kainji languages of Kaduna State and the LGAs where Roger
Blench's Atlas of Nigerian Languages (2020) places them. Researched 2026-10-07. Pattern: batch 160 (Niger languages).

Kaduna is split in three: 166 Kainji languages (this batch), 166b Plateau and other languages, 166c peoples.
Method: the column-split Atlas text was searched for 'Kaduna State' and Kaduna LGA and town names; 63 entries were read.
17 Kainji languages concern Kaduna and are new records here; existing records (Kurama, Map, Jere, Rigwe, Kamuku, Cicipu)
get Kaduna links.
Boundaries: the Atlas uses LGAs from before the 1989–1997 splits. Most of these languages are placed in 'Saminaka LGA',
which no longer exists: Saminaka town is now the headquarters of Lere LGA (Wikipedia, 'Saminaka'; 'Lere, Nigeria').
Following batch 160, only today's LGAs of the same name are linked from the Atlas (Lere, Ikara, Kauru, Kachia, Birnin
Gwari). In addition, where INEC's 2015 polling-unit directory has a unit or ward bearing the language's own name or a
name the Atlas gives for it (Kinugu, Kitimi, Kaibi, Fadan Kono, Binawa, Rumaya, Sheni, Gure/Kahugu, Fadan Chawai,
Damakasuwa Kurama), the LGA is linked as 'reported', with the match stated in the note. Ambiguous matches are not used
(Ruma in Makarfi, Surubu in Zangon Kataf, Kuzamani in Kauru, Bina in Igabi).
Glottolog: matched by name and map point; none set for Ngmgbang, Shuwa–Zamani (Glottolog's 'Shuwa-Zamani' ksa has a
map point in the north-east; 'Kizamani' izm may be the same language) and Kere.
"""
import json, sys
import batch_087_kano_languages as L87
import batch_063_borno_languages as B63

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Kaduna Kainji entries read in full: Atsam, Bin, Bishi, Dungu, Gbiri–Niragu, Kaivi, Kono, Mala, Ngmgbang, Nu, Ruma, Sheni, Kere, Shuwa–Zamani, Tumi, Vono, Vori; also Kurama, Map, Jere, Rigwe, Cinda-Regi-Rogo-Kuki (Kamuku) and Cipu."),
    "GLIDX": L87.SOURCES["GLIDX"],
    "INEC": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Directory of Polling Units: Kaduna State (Revised January 2015)",
                 organisation="Independent National Electoral Commission (INEC)", publication_date="2015-01",
                 url="https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_Kaduna.pdf",
                 archive_reference="Internet Archive copy of the INEC PDF (no longer on the INEC site); copy in database/research/data/inec_kaduna.pdf",
                 verification_status="verified", notes="Kaduna State's 23 LGAs, registration areas (wards) and polling units. Used here only to place villages named in the Atlas. INEC's disclaimer: not a legal or administrative document for boundary or political claims."),
    "WSAM": WS("Saminaka", "Saminaka is the administrative headquarters of Lere LGA and the seat of the Saminaka Emirate Council; Saminaka LGA existed in the late 1970s and 1980s, and after later reorganisation Saminaka became the headquarters of Lere LGA."),
    "WLERE": WS("Lere, Nigeria", "LGA with its headquarters at Saminaka; its subdivisions include Gure Kahugu (Gbiri Niragu), Garu Mariri and Kudaru."),
}
S, B, L, M = "spelling_variant", "endonym", "alternative", "alternative"
FIELD = dict(B63.FIELD, **{"1.C": "the speakers' name for themselves"})
KD = lambda l: f"@admin_units:lga:kaduna/{l}"
ST = lambda s: f"@admin_units:state:{s}"
K = "@languages:kainji"
SAM = "The Atlas places it in Saminaka LGA, a former LGA; Saminaka town is now the headquarters of Lere LGA (Wikipedia)."
KAU = "Kainji (Eastern Kainji: Northern Jos group, Kauru subgroup)"
# key: (name, cls, [(name, type, field)], [atlas lgas], [(lga, inec note)], place, speakers, extra, glottocode)
LANG = {
 "atsam": ("Atsam", "Kainji (Eastern Kainji: group A)", [("Tsam", L, "1.C"), ("Chawai", L, "2.C"), ("Chawe", L, "2.C"), ("Chawi", L, "2.C")], ["kachia"],
           [("kauru", "INEC lists the polling unit Fadan Chawai in Damakasuwa ward of Kauru LGA (Chawai is the Atlas's other name for the language).")],
           "The Atlas places it in Kachia LGA, as the LGA was before the 1989 splits.", "10,200 (1931); 30,000 (1972)",
           "The Atlas gives Atsam (singular Tsam) as the people's own name, and records Scripture portions from 1923 and 1932.", "atsa1241"),
 "bishi": ("Bishi", "Kainji (Eastern Kainji: group A)", [("Abisi", L, "1.A"), ("Bisi", L, "1.A"), ("Pitti", L, "2.B")], [], [],
           SAM + " Its speakers live in at least twenty-six villages (2013).", "1,600 (1950)",
           "The Atlas notes that Ngmgbang, formerly listed as a dialect of Bishi, is a distinct language. Glottolog lists it as Piti.", "piti1243"),
 "ngmgbang": ("Ngmgbang", "Kainji (Eastern Kainji: group A)", [("Ribam", L, "1.A"), ("Rigmgbang", B, "1.B")], [], [],
              SAM + " It is spoken in a few villages (2013).", "", "The Atlas notes that it was formerly listed as a dialect of Bishi but is clearly a distinct language.", None),
 "bin": ("Bin", KAU, [("Bina", L, "1.A"), ("tìBin", B, "1.B"), ("Bogana", L, "2.B"), ("Binawa", L, "2.C")], [],
         [("kauru", "INEC lists the polling unit Binawa in Geshere ward of Kauru LGA (Binawa is the Atlas's Hausa name for the people).")],
         SAM + " The Atlas places it about 15 km west of Mariri, along the Geshere road, in four villages (2016).", "220 (1949); 2,000 (1973); about 3,000–4,000 (2016 estimate)", "", "bina1270"),
 "dungu": ("Dungu", KAU, [("Dungi", L, "1.A"), ("Dingi", L, "1.A"), ("Dwingi", L, "1.A"), ("Dunjawa", L, "1.A")], [], [], SAM, "310 (1949)", "", "dung1254"),
 "gbiri-niragu": ("Gbiri–Niragu", KAU, [("Gbiri", M, "member"), ("Niragu", M, "member"), ("Gure", L, "2.A"), ("Gura", L, "2.A"), ("Kahugu", L, "2.A"), ("Kagu", L, "2.A"), ("Kafugu", L, "2.A"), ("Anirago", L, "1.B")], [],
                  [("lere", "Wikipedia lists 'Gure Kahugu (Gbiri Niragu)' among the subdivisions of Lere LGA, and INEC has a ward Gure/Kahugu in Lere LGA.")],
                  SAM + " Wikipedia and INEC place Gure and Kahugu in Lere LGA.", "5,000 (1952)", "The Atlas records a literacy programme under way in Gbiri.", "gbir1241"),
 "kaivi": ("Kaivi", KAU, [("Kaibi", L, "1.A")], [], [("kauru", "INEC lists the polling unit Kaibi in Bital ward of Kauru LGA.")], SAM, "650 (1949)", "", "kaiv1238"),
 "kono": ("Kono", KAU, [("Konu", L, "1.A"), ("Kwono", L, "1.A")], [], [("kauru", "INEC lists the polling unit Fadan Kono in Geshere ward of Kauru LGA.")], SAM, "1,550 (1949)", "", "kono1264"),
 "mala": ("Mala", KAU, [("Tumala", B, "1.B"), ("Amala", L, "1.C"), ("Rumaya", L, "2.A"), ("Rumaiya", L, "2.A")], [],
          [("lere", "INEC lists the polling unit Rumaya in Yar Kasuwa ward of Lere LGA (Rumaya is the Atlas's other name for the language).")], SAM, "1,800 (1948)", "", "mala1471"),
 "nu": ("Nu", KAU, [("Tinu", B, "1.B"), ("Kinugu", L, "2.A"), ("Kinuka", L, "2.A"), ("Kinuku", L, "2.A")], [],
        [("kauru", "INEC lists the polling unit Kinugu in Bital ward of Kauru LGA."), ("lere", "INEC lists the polling unit Kinugu Kasuwa in Gure/Kahugu ward of Lere LGA.")],
        SAM + " It is spoken in about seven villages.", "460 (1949); 500 (1973); about 3,000 (2016 estimate)", "The Atlas describes it as vigorous.", "kinu1239"),
 "ruma": ("Ruma", KAU, [("Rurama", L, "1.A"), ("Turuma", B, "1.B"), ("Bagwama", L, "2.B")], [], [], SAM, "2,200 (1948)", "The Atlas notes that the name Bagwama also refers to Kurama.", "ruma1250"),
 "shuwa-zamani": ("Shuwa–Zamani", KAU, [], [], [], SAM, "", "", None),
 "tumi": ("Tumi", KAU, [("Tutumi", B, "1.B"), ("Kitimi", L, "2.A")], [],
          [("kauru", "INEC lists the polling unit Kitimi in Kwassam ward of Kauru LGA."), ("lere", "INEC lists the polling unit Kadigi/Kitimi in Gure/Kahugu ward of Lere LGA.")], SAM, "635 (1949)", "", "tumi1238"),
 "vono": ("Vono", KAU, [("Kivɔnɔ", B, "1.B"), ("Kibolo", L, "2.B"), ("Kiwollo", L, "2.B"), ("Kiballo", L, "2.B")], [], [], SAM, "335 (1949); 500 (1973)", "", "vono1238"),
 "vori": ("Vori", KAU, [("TiVori", B, "1.B"), ("Fiti", L, "2.B"), ("Surubu", L, "2.A"), ("Zurubu", L, "2.A"), ("Skrubu", L, "2.A")], [], [], SAM, "1,950 (1948)", "", "suru1258"),
 "sheni": ("Sheni", "Kainji (Eastern Kainji: Northern Jos group, group c)", [("Shani", L, "1.A"), ("Shaini", L, "1.A"), ("tiSeni", B, "1.B")], ["lere"],
           [("lere", "INEC lists the polling unit Sheni in Abadawa ward of Lere LGA.")],
           "The Atlas places it in Lere LGA, in two settlements, Sheni and Gurjiya.", "6 fluent speakers in an ethnic community of about 1,500 (2003)",
           "The Atlas's figures show the language close to extinction.", "shen1250"),
 "kere": ("Kere", "Kainji (Eastern Kainji: Northern Jos group, group c)", [], ["lere"], [], "The Atlas places it at Kere, in Lere LGA.", "", "The Atlas records it as extinct (2003).", None),
}
TEXT = {}
for k, (name, cls, names, lgas, inec, place, spk, extra, g) in LANG.items():
    t = f"{name} is a language of Kaduna State, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as {cls}. {place}"
    alt = [n for n, _, f in names if f != "member" and n != name]
    mem = [n for n, _, f in names if f == "member"]
    if mem: t += f" Its members are {' and '.join(mem)}."
    if alt: t += f" Other names include {', '.join(alt[:5])}."
    if spk: t += f" Speaker figures in the Atlas: {spk}."
    if extra: t += " " + extra
    TEXT[k] = t

RECORDS, NAMES, RELATIONS = [], [], []
for k, (name, cls, names, lgas, inec, place, spk, extra, g) in LANG.items():
    f = dict(lang_type="language", name=name, slug=k, parent_id=K, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k])
    if g: f["glottocode"] = g
    srcs = [("ATLAS", name)] + ([("GLIDX", f"Glottolog {g}")] if g else []) + ([("INEC", f"{name}: village placement")] if inec else [])
    if k == "gbiri-niragu": srcs.append(("WLERE", "Gure Kahugu (Gbiri Niragu), Lere LGA"))
    if place.startswith(SAM[:40]): srcs.append(("WSAM", "Saminaka, headquarters of Lere LGA"))
    RECORDS.append(dict(key=k, table="languages", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level="well_documented", fields=f, srcs=srcs))
    for n, t, fld in names:
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=f"Blench's Atlas (2020), field {fld}: {FIELD.get(fld, 'member of the cluster')}.", srcs=["ATLAS"]))
    RELATIONS.append(dict(frm=k, type="spoken_in", to=ST("kaduna"), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes="Atlas: Kaduna State."))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=KD(l), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=f"Blench's Atlas: {place}"))
    for l, note in inec:
        if l in lgas: continue
        RELATIONS.append(dict(frm=k, type="spoken_in", to=KD(l), source="INEC", evidence="single_reliable_source", level="reported",
                              notes=f"Placement by village name, not stated by the Atlas: {note}"))
# Existing records
EXIST = [
    ("kurama", False, [("ikara", "ATLAS", "well_documented", "Atlas: 'Kaduna State, Saminaka and Ikara LGAs; Kano State, Tudun Wada LGA'."),
                       ("kauru", "INEC", "reported", "Placement by village name, not stated by the Atlas: INEC lists the polling unit D/Kasuwa Kurama in Damakasuwa ward of Kauru LGA.")]),
    ("map", True, []),
    ("jere", True, []),
    ("rigwe", True, [("kauru", "ATLAS", "well_documented", "Atlas: 'Bassa local government, Plateau State and Kauru local government, Kaduna State'.")]),
    ("kamuku", False, [("birnin-gwari", "ATLAS", "well_documented", "Atlas: Cinda, Regi and Kuki also in 'Kaduna State, Birnin Gwari LGA'; Kwacika (†) in Birnin Gwari.")]),
    ("cipu", False, [("birnin-gwari", "ATLAS", "well_documented", "Atlas: 'Kebbi State, Sakaba LGA; Niger State, Mariga and Rafi LGA, Kaduna State Birnin Gwari LGA'.")]),
]
STATE_NOTES = {"map": "Atlas: 'Plateau State, Bassa LGA; Kaduna State, Saminaka LGA' (Saminaka LGA no longer exists).",
               "jere": "Atlas (Jere, of the Jera cluster): 'Plateau State, Bassa LGA; Kaduna State, Saminaka LGA' (Saminaka LGA no longer exists).",
               "rigwe": "Atlas: Kauru local government, Kaduna State."}
for lang, state, links in EXIST:
    if state:
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=ST("kaduna"), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=STATE_NOTES[lang]))
    for l, src, lvl, note in links:
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=KD(l), source=src, evidence="single_reliable_source", level=lvl, notes=note))
GAPS = [
    ("Kaduna: Saminaka LGA", "The Atlas places most Kauru-subgroup languages (Bishi, Ngmgbang, Bin, Dungu, Gbiri–Niragu, Kaivi, Kono, Mala, Nu, Ruma, Shuwa–Zamani, Tumi, Vono, Vori) in Saminaka LGA, which no longer exists; its area now lies at least partly in Lere and Kauru LGAs. Where an INEC polling unit carries the language's name, the LGA is linked as reported; Bishi, Ngmgbang, Dungu, Ruma, Shuwa–Zamani, Vono and Vori are linked at state level only. A current survey (e.g. Ajaegbu et al. 2013) would settle the LGAs."),
    ("Kaduna: Atsam and Kachia", "The Atlas places Atsam (Chawai) in Kachia LGA as it was before 1989; INEC's Fadan Chawai is in Kauru LGA. Both links are kept until a current source settles it."),
    ("Kaduna: Sheni's second settlement", "The Atlas places Gurjiya, Sheni's second settlement, in Lere LGA; INEC lists a polling unit Gurjiya in Kargi ward of Kubau LGA. Kubau is not linked."),
    ("Kaduna: glottocodes", "Ngmgbang, Shuwa–Zamani and Kere have no clearly matching Glottolog languoid; none is set. Glottolog's Kizamani (izm) may correspond to Shuwa–Zamani."),
    ("Kaduna: extinct and moribund languages", "The Atlas records Kere as extinct and Sheni with six fluent speakers (2003), and Kwacika (a Kamuku variety in Birnin Gwari) with one old speaker in the 1980s; their current status needs a recent source."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kaduna Kainji languages: 17 new language records from Blench's Atlas, with Kaduna links for Kurama, Map, Jere, Rigwe, Kamuku and Cicipu.")


def report():
    lga = sorted({r["to"].split("/")[-1] for r in RELATIONS if "lga:kaduna" in r["to"]})
    rep = [r for r in RELATIONS if r["level"] == "reported"]
    L_ = ["# Research batch 166 — Kaduna: Kainji languages", "",
          f"Researched {ACCESSED}. First of three Kaduna language and people batches (166 Kainji languages, 166b Plateau and other languages, 166c peoples). Created in review; published only after your approval.", "",
          "## What it adds", "",
          f"- **17 new languages**, all Kainji, mostly of the Kauru subgroup north of the Jos Plateau: {', '.join(v[0] for v in LANG.values())}.",
          "- **Glottolog codes** for 14; none for Ngmgbang, Shuwa–Zamani and Kere (see gaps).",
          "- **Existing records linked to Kaduna:** Kurama (Ikara; Kauru reported), Map and Jere (state), Rigwe (Kauru), Kamuku and Cicipu (Birnin Gwari).",
          f"- **LGAs linked:** {', '.join(lga)}.",
          "",
          "## A method choice for you to check", "",
          "The Atlas places most of these languages in **Saminaka LGA**, which no longer exists (Saminaka town is now the headquarters of Lere LGA). "
          "Linking only by the Atlas would leave them at state level. Where INEC's 2015 polling-unit directory has a unit or ward bearing the language's own name or a name the Atlas gives for it, "
          f"I linked that LGA as **reported** and wrote the match in the note ({len(rep)} links). Ambiguous name matches were not used. If you prefer Atlas-only links, these {len(rep)} links can be dropped before deployment.", "",
          "## The records", ""]
    for k in LANG:
        L_ += [f"**{LANG[k][0]}.** {TEXT[k]}", ""]
    L_ += ["## Reported links (INEC name matches)", ""] + [f"- {r['frm'].replace('@languages:', '')} → {r['to'].split('/')[-1]}: {r['notes']}" for r in rep] + [""]
    L_ += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_166_kaduna_kainji_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_166_kaduna_kainji_languages_REVIEW.md", "w").write(report())
    lga = {r["to"] for r in RELATIONS if "lga:kaduna" in r["to"]}
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} kaduna-LGAs={len(lga)}")
