"""
Research batch 160 — Niger (Phase 3, first batch): the languages of Niger State and the LGAs where Roger Blench's Atlas
of Nigerian Languages (2020) places them. Researched 2026-10-07. Pattern: batch 051 (Kogi languages).

Method: the column-split Atlas text was searched for 'Niger State', the 25 Niger LGA names and old names (Suleija,
Chanchagga, Kusheriki) and towns (Minna, Kagara, Zungeru). 41 entries were read in full; 30 concern Niger and are new
records here; existing records (Nupe, Gbagyi, Gbari, Kakanda, Gwandara, Busa) get Niger LGA links.
Boundaries: the Atlas uses LGAs from before later splits (e.g. Kusheriki LGA, now gone; Chanchaga before Bosso; Suleja
before Tafa). Only today's LGAs whose names the Atlas uses are linked; 'Kusheriki' is not linked (gap).
Classification: Kainji, Nupoid, Plateau and Mande members go under the existing branch records; a new Songhai branch
holds Zarma. Glottolog codes are given where Glottolog has a matching languoid; where it has only a combined one
(Wayam-Rubu; Lopa for Rop and Tsupamini) or none (Mɨn, Samburu of Rafi, Basa-Gurmana, the Kambari clusters), no code is
set. (Glottolog's 'Samburu' samb1315 is the Kenyan language and is not used.)
Excluded false matches of the search: Agwagwune, Bangjinge, Kaan, Kono, Mingang Doso (other states).
"""
import json, sys
import batch_087_kano_languages as L87
import batch_063_borno_languages as B63

ACCESSED = "2026-10-07"
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Niger entries read in full: Asu, Basa-Gumna–Basa-Kontagora, Basa-Gurmana, Boko, Busa, Cinda-Regi-Rogo-Kuki (Kamuku), Cipu, Dibo, Fungwa, Gbagyi, Gbagyi Nkwa, Gbari, Gupa–Abawa, Gurmana, Gwandara, Hɨpɨna, Hun–Saare, Hùngwəryə, Jijili, Kadara, Kakanda, Kambari I and II, Kami, Kyenga, cLela, Mɨn, Ndəkə, Nupe, Reshe, Rin, Rop, Rubu, Samburu, Shama–Sambuga, Shen, Tsupamini, Wãyã, Zarma."),
    "GLIDX": L87.SOURCES["GLIDX"],
}
S, B, L, M = "spelling_variant", "endonym", "alternative", "alternative"
FIELD = dict(B63.FIELD)
N = lambda l: f"@admin_units:lga:niger/{l}"
ST = lambda s: f"@admin_units:state:{s}"
K = "@languages:kainji"
NUP = "@languages:nupoid"
PLA = "@languages:plateau"
MAN = "@languages:mande"
STATE_NOTE = "Atlas: Niger State."
# key: (name, parent, class text, [(name, type, field)], [lga slugs], [(other ref, note)], place text, speakers, extra, glottocode)
LANG = {
 "asu": ("Asu", NUP, "Nupoid (Nupe group)", [("Abewa", L, "2.A"), ("Ebe", L, "2.B")], ["mariga"], [],
         "The Atlas places it in several villages of Mariga LGA, south of Kontagora on the Mokwa road.", "5,000 (Blench 1987)", "", "asun1235"),
 "basa-gumna": ("Basa-Gumna", K, "Kainji (Western Kainji: Kamuku–Basa group)", [("Basa Kuta", L, "2.B"), ("Basa-Kaduna", L, "2.B"), ("Gwadara-Basa", L, "2.B")], ["chanchaga"], [],
                "The Atlas places it in Chanchaga LGA.", "only two known semi-speakers in 1987",
                "The Atlas describes it as probably extinct: the population known as Basawa speaks only Hausa.", "basa1280"),
 "basa-kontagora": ("Basa-Kontagora", K, "Kainji (Western Kainji: Kamuku–Basa group)", [], ["mariga"], [],
                    "The Atlas places it in Mariga LGA, north-east of Kontagora.", "fewer than ten speakers in 1987",
                    "The Atlas describes it as probably extinct, and lists it with Basa-Gumna in a single cluster.", "bass1259"),
 "basa-gurmana": ("Basa-Gurmana", K, "Kainji (Western Kainji: Kamuku–Basa group)", [("Kɔrɔmba", B, "1.B")], ["rafi", "chanchaga"], [],
                  "The Atlas places it at Kafin Gurmana, on the border of Rafi and Chanchaga LGAs.", "more than 2,000 (1987)", "", None),
 "boko": ("Boko", MAN, "Mande (Southeast: Busa cluster)", [("Boo", B, "1.B")], ["borgu"], [],
          "The Atlas places it in Borgu LGA and in the Nikki–Kande area of Benin Republic.", "120,000 in all (2004 est.)",
          "The Atlas notes literacy programmes in Boko in Benin Republic.", "boko1266"),
 "kamuku": ("Kamuku (Cinda-Regi-Rogo-Kuki)", K, "Kainji (Western Kainji: Kamuku group)",
            [("Kamuku", L, "2.C"), ("Cinda", M, "member"), ("Regi", M, "member"), ("Rogo", M, "member"), ("Kuki", M, "member"), ("Kwagere", M, "member")],
            ["chanchaga", "rafi", "mariga"], [(ST("kaduna"), "Atlas: Cinda and Regi also in Kaduna State, Birnin Gwari LGA; Kwacika (†) in Birnin Gwari.")],
            "The Atlas places the cluster in Chanchaga, Rafi and Mariga LGAs, with Cinda and Regi also in Birnin Gwari LGA of Kaduna State.", "",
            "The Atlas notes that the Kamuku, a cover name for this cluster, Sagamuk and Hùngwəryə, numbered 17,800 in 1952 (HDG); its member Kwacika, in Kaduna State, had only one old speaker in the 1980s.", "kamu1262"),
 "cipu": ("Cicipu", K, "Kainji (Western Kainji: Kambari cluster)", [("Cipu", S, "head"), ("Acipa", L, "2.A"), ("Achipa", L, "2.A"), ("Achipawa", L, "2.A")],
          ["mariga", "rafi"], [(ST("kebbi"), "Atlas: Kebbi State, Sakaba LGA."), (ST("kaduna"), "Atlas: Kaduna State, Birnin Gwari LGA.")],
          "The Atlas places it in Sakaba LGA of Kebbi State, in Mariga and Rafi LGAs of Niger State, and in Birnin Gwari LGA of Kaduna State.", "3,600 (1949)",
          "Its own name is Cicipu, and the Atlas lists seven dialects, among them Kumbashi and Tikula.", "cici1237"),
 "dibo": ("Dibo", NUP, "Nupoid", [("Ganagana", L, "2.C"), ("Ganagawa", L, "2.C"), ("Zitako", L, "2.B"), ("Shitako", L, "2.B")],
          ["lapai"], [(ST("nasarawa"), "Atlas: Nasarawa State, Nassarawa LGA.")],
          "The Atlas places it in Lapai LGA, in the Federal Capital Territory and in Nassarawa LGA of Nasarawa State.", "18,200 (1931); more than 100,000 (1990 estimate)",
          "The Atlas notes that an unknown number of Dibo living among the Gbari no longer speak the language.", "dibo1247"),
 "fungwa": ("Fungwa", K, "Kainji (Western Kainji: Kamuku–Basa group)", [("Tufungwa", B, "1.B"), ("Ura", L, "2.A"), ("Ula", L, "2.A")], ["rafi"], [],
            "The Atlas places it in Rafi LGA, at Gulbe, Gabi Tukurbe, Urenciki, Renge and Utana.", "900 (1949)", "", "fung1245"),
 "gupa-abawa": ("Gupa–Abawa", NUP, "Nupoid (Nupe)", [("Gupa", M, "member"), ("Abawa", M, "member")], ["lapai"], [],
                "The Atlas places it in Lapai LGA, around the villages of Gupa and Edzu.", "more than 10,000 Gupa and 5,000 Abawa (1989 estimate)", "", "gupa1248"),
 "gurmana": ("Gurmana", K, "Kainji (Western Kainji: Eastern group)", [], ["shiroro"], [],
             "The Atlas places it at Gurmana town and nearby hamlets in Shiroro LGA.", "more than 3,000 (1989 estimate)", "", "gurm1246"),
 "hipina": ("Hɨpɨna", K, "Kainji (West: Baushi cluster)", [("Supana", S, "1.A"), ("Tihɨpɨna", B, "1.B")], ["rafi"], [],
            "The Atlas places it at Supana town in Rafi LGA.", "", "", "supa1245"),
 "hun-saare": ("Hun–Saare", K, "Kainji (Western Kainji: Northern group)", [("Duka", L, "2.A"), ("Dukanci", L, "2.B"), ("Ethun", S, "1.A")],
               ["rijau"], [(ST("kebbi"), "Atlas: Kebbi State, Sakaba LGA.")],
               "The Atlas places it in Sakaba LGA of Kebbi State and Rijau LGA of Niger State, with its Eastern dialect, tHun, around Rijau.", "19,700 (1949); 30,000 (1980)",
               "It is also called Duka. Primers appeared in 1976, and the New Testament was nearly complete in 2003.", "huns1239"),
 "hungworyo": ("Hùngwəryə", K, "Kainji (Western Kainji: Kamuku–Basa group)", [("Ngwoi", L, "2.C"), ("Ingwe", L, "2.C"), ("Hungworo", L, "2.C")], ["rafi"], [],
               "The Atlas places it in Rafi LGA (and the former Kusheriki LGA), around the towns of Kagara and Maikujeri.", "1,000 (1949); 5,000 (2007 estimate)",
               "The Atlas records an alphabet booklet (2004) and good language maintenance in 2007, with Hausa as the main second language.", "hung1276"),
 "jijili": ("Jijili", PLA, "Plateau (Southern: Jili group)", [("Tanjijili", B, "1.B"), ("Koro Funtu", L, "2.C")], ["chanchaga", "suleja"], [],
            "The Atlas places it in Chanchaga and Suleja LGAs, north of the road from Minna to Suleja around Kafin Koro.", "about 8,000 in some eight settlements (1999)", "", "tanj1247"),
 "kadara": ("Kadara", PLA, "Plateau (Northern group)", [("Adara", S, "1.A"), ("Eda", M, "member"), ("Edra", M, "member"), ("Enezhe", M, "member")],
            ["paikoro"], [(ST("kaduna"), "Atlas: Kaduna State, Kachia LGA (Eda; Edra and Enezhe also in Kajuru).")],
            "The Atlas places the cluster mainly in Kachia and Kajuru LGAs of Kaduna State, with Eda also in Paikoro LGA of Niger State.", "22,000 (1949); 40,000 (1972)",
            "The Atlas notes a South dialect of Kadara named after Minna, and reading materials published in 2006.", "kada1284"),
 "kambari-1": ("Kambari I", K, "Kainji (Western Kainji: Kambari group)", [("Agaɗi", M, "member"), ("Avaɗi", M, "member"), ("Baangi", M, "member"), ("Tsishingini", M, "member")],
               ["magama", "mariga", "kontagora", "borgu"], [(ST("kebbi"), "Atlas: Kebbi State, Zuru and Yauri LGAs.")],
               "The Atlas places the cluster in Magama, Mariga and Borgu LGAs of Niger State and Zuru and Yauri LGAs of Kebbi State, with its Baangi member at Ukata town in Kontagora LGA.",
               "with Kambari II, 67,000 (1952) and 100,000 (1973)",
               "Glottolog treats its members as separate languages (for example Kakihum, Tsuvadi and Salka-Tsishingini); broadcasts in Salka from Radio Kontagora have halted.", None),
 "kambari-2": ("Kambari II", K, "Kainji (Western Kainji: Kambari group)", [("Agaushi", M, "member"), ("Akimba", M, "member"), ("Cishingini", M, "member"), ("Nwanci", M, "member"), ("Agwara", L, "2.A")],
               ["magama", "rijau", "borgu"], [(ST("kebbi"), "Atlas: Kebbi State, Zuru and Yauri LGAs.")],
               "The Atlas places the cluster in Magama, Rijau and Borgu LGAs of Niger State and Zuru and Yauri LGAs of Kebbi State; it also names Borgu LGA under Kwara State, before Borgu moved to Niger.",
               "with Kambari I, 67,000 (1952) and 100,000 (1973)", "Its Cishingini member is also called Nwanci or Agwara, after the town.", None),
 "kami": ("Kami", NUP, "Nupoid (Nupe)", [], ["lapai"], [], "The Atlas places it at Ebo town and eleven villages in Lapai LGA.", "more than 5,000 (1989 estimate)", "", "kami1258"),
 "kyenga": ("Kyenga", MAN, "Mande (Southeast Mande)", [("Kyangganya", B, "1.B"), ("Kenga", L, "2.A"), ("Tyenga", L, "2.A")], ["borgu"], [],
            "The Atlas places it in Borgu LGA, north of Illo, and in Benin and Niger Republics.", "five villages on the Nigerian side; 7,591 (1925); 10,000 including Shanga (1973)", "", "kyen1242"),
 "clela": ("cLela", K, "Kainji (Western Kainji: Northwestern)", [("Lelna", B, "1.B"), ("Dakarkari", L, "2.C"), ("Lalawa", L, "2.C")],
           ["rijau"], [(ST("kebbi"), "Atlas: Kebbi State, Zuru, Sakaba and Wasagu LGAs, around Zuru town.")],
           "The Atlas places it mainly in Zuru, Sakaba and Wasagu LGAs of Kebbi State, around Zuru town, and in Rijau LGA of Niger State.", "47,000 (1949); 69,000 (1971)",
           "Its dialects are Zuru and Ribah; a reader appeared in 1934 and scripture portions from 1931.", "clel1238"),
 "min": ("Mɨn", K, "Kainji (West: Baushi cluster)", [("Tiimɨn", B, "1.B"), ("Bauchi Guda", L, "2.A"), ("Kukoki", L, "2.A")], ["rafi"], [],
         "The Atlas places it in Rafi LGA, in twenty-seven villages under eight chiefships; Kukoki is the largest town.", "", "", None),
 "ndeke": ("Ndəkə", K, "Kainji (West: Baushi cluster)", [("Madaka", S, "1.A"), ("Tundəkə", B, "1.B")], ["rafi"], [],
           "The Atlas places it at Madaka town in Rafi LGA, and notes that Shena may be a dialect.", "", "", "mada1283"),
 "reshe": ("Reshe", K, "Kainji (Western Kainji: Lake group)", [("Tsureshe", B, "1.B"), ("Gungawa", L, "2.C"), ("Gunganci", L, "2.B")],
           ["borgu"], [(ST("kebbi"), "Atlas: Kebbi State, Yauri LGA.")],
           "The Atlas places it in Yauri LGA of Kebbi State and Borgu LGA of Niger State.", "15,000 (1931); 30,000 (1973)",
           "It has three dialects; seven readers were published before 1967, and a Bible translation is in progress.", "resh1242"),
 "rin": ("Rin", K, "Kainji (Western Kainji: Shiroro group)", [("Pongu", L, "2.A"), ("Pangu", L, "2.A"), ("Tàrĩ", B, "1.B")], ["rafi"], [],
         "The Atlas places it in Rafi LGA, near Tegina.", "3,675 (1949); more than 20,000 (1988)",
         "The Atlas notes that, despite the own name, the community prefers forms of the name Pangu for publications; a literacy programme has run since 2004.", "pong1250"),
 "rop": ("Rop", K, "Kainji (Western Kainji: Lake group)", [("Lopa", L, "1.A"), ("Lupa", L, "1.A"), ("Kirikjir", B, "1.B")],
         ["borgu"], [(ST("kebbi"), "Atlas: Kebbi State, Yauri LGA.")],
         "The Atlas places it in Borgu LGA of Niger State and Yauri LGA of Kebbi State, in at least six villages on the east shore of Lake Kainji and two on the west.", "960 (1950); 5,000 (1992 estimate)",
         "Glottolog groups it with Tsupamini under Lopa (lopa1238).", None),
 "rubu": ("Rubu", K, "Kainji (West: Baushi cluster)", [], ["rafi"], [], "The Atlas places it at Rubu town in Rafi LGA.", "", "Glottolog groups it with Wãyã (Wayam-Rubu).", None),
 "samburu": ("Samburu (Rafi)", K, "Kainji (West: Baushi cluster)", [("Samburu", L, "head")], ["rafi"], [], "The Atlas places it at Samburu town in Rafi LGA and gives no further data.", "", "It is not the Samburu language of Kenya.", None),
 "shama-sambuga": ("Shama–Sambuga", K, "Kainji (Western Kainji: Kamuku–Basa group)", [("Tushama", B, "1.B"), ("Shama", M, "member"), ("Sambuga", M, "member")], ["rafi"], [],
                   "The Atlas places it in Rafi LGA: Shama at Ushama (Kawo) town, 15 km north-west of Kagara, and Sambuga at Sambuga town, 10 km north-west of Kagara.", "",
                   "The Atlas marks Sambuga as possibly extinct (2008).", "sham1278"),
 "shen": ("Shen", K, "Kainji (Western Kainji: Kainji Lake group)", [("Laru", L, "1.A"), ("Laro", L, "1.A"), ("Laruwa", L, "2.C")], ["borgu"], [],
          "The Atlas places it in Borgu LGA.", "1,000 (1992 estimate)", "", "laru1238"),
 "tsupamini": ("Tsupamini", K, "Kainji (Western Kainji: Lake group)", [("Lopa", L, "1.A"), ("Lopanic", L, "2.B")],
               ["borgu"], [(ST("kebbi"), "Atlas: Kebbi State, Yauri LGA.")],
               "The Atlas places it in Borgu LGA of Niger State and Yauri LGA of Kebbi State, in villages on both shores of Lake Kainji.", "5,000 with Rop (1992 estimate)",
               "Glottolog groups it with Rop under Lopa (lopa1238).", None),
 "waya": ("Wãyã", K, "Kainji (West: Baushi cluster)", [("Wayam", S, "1.A"), ("Tũwãyã", B, "1.B")], ["rafi", "shiroro"], [],
          "The Atlas places it at Wayam town, in Rafi and Shiroro LGAs.", "", "Glottolog groups it with Rubu (Wayam-Rubu, waya1265).", None),
 "zarma": ("Zarma", "@key:songhai", "Nilo-Saharan (Songhai)", [("Djerma", S, "1.A"), ("Zabarma", L, "2.C"), ("Zabermawa", L, "2.C")],
           [], [(ST("kebbi"), "Atlas: Kebbi State, Argungu, Birnin Kebbi and Bunza LGAs.")],
           "The Atlas places it in Argungu, Birnin Kebbi and Bunza LGAs of Kebbi State, in villages of Niger State between Mokwa and Kontagora, and in Benin, Burkina Faso and Niger Republics.",
           "50,000 in Nigeria (1973); 1,495,000 in Niger Republic (1986)",
           "The New Testament appeared in 1954 and the complete Bible in 1990.", "zarm1239"),
}
GBN = ("Gbagyi Nkwa", "rafi", "gbag1257")
TEXT = {}
for k, (name, parent, cls, names, lgas, other, place, spk, extra, g) in LANG.items():
    t = f"{name} is a language of Niger State, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as {cls}. {place}"
    alt = [n for n, _, f in names if f not in ("member", "head") and n != name]
    mem = [n for n, _, f in names if f == "member"]
    if mem: t += f" Its members include {', '.join(mem)}."
    if alt: t += f" Other names include {', '.join(alt[:5])}."
    if spk: t += f" Speaker figures in the Atlas: {spk}."
    if extra: t += " " + extra
    TEXT[k] = t
TEXT["songhai"] = "Songhai is a group of languages of the middle Niger valley, centred on Mali and Niger Republic, which Roger Blench's Atlas of Nigerian Languages (2020) classifies as Nilo-Saharan. In Nigeria it is represented by Zarma, spoken in Kebbi State and in villages of Niger State between Mokwa and Kontagora. Glottolog lists Songhay (song1307) as a family."
TEXT["gbagyi-nkwa"] = "Gbagyi Nkwa is a variety of Gbagyi spoken in Rafi LGA of Niger State, which Roger Blench's Atlas of Nigerian Languages (2020) lists as a separate entry, with more than 50,000 speakers (1989 estimate); its speakers call it Gbagyi. Glottolog lists it as Gbagyi Nkwa (gbag1257)."

RECORDS = [dict(key="songhai", table="languages", evidence="multiple_sources", level="well_documented",
                fields=dict(lang_type="branch", name="Songhai", slug="songhai", glottocode="song1307", summary=TEXT["songhai"].split(". ")[0] + ".", description=TEXT["songhai"]),
                srcs=[("ATLAS", "Zarma classified Nilo-Saharan: Songhai"), ("GLIDX", "Songhay (song1307)")]),
           dict(key="gbagyi-nkwa", table="languages", evidence="multiple_sources", level="well_documented",
                fields=dict(lang_type="dialect", name="Gbagyi Nkwa", slug="gbagyi-nkwa", parent_id="@languages:gbagyi", glottocode="gbag1257", summary=TEXT["gbagyi-nkwa"].split(". ")[0] + ".", description=TEXT["gbagyi-nkwa"]),
                srcs=[("ATLAS", "Gbagyi Nkwa: Rafi LGA, speakers"), ("GLIDX", "Gbagyi Nkwa (gbag1257)")])]
NAMES, RELATIONS = [], []
for k, (name, parent, cls, names, lgas, other, place, spk, extra, g) in LANG.items():
    f = dict(lang_type="language", name=name, slug=k, parent_id=parent, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k])
    if g: f["glottocode"] = g
    RECORDS.append(dict(key=k, table="languages", evidence="multiple_sources" if g else "single_reliable_source", level="well_documented", fields=f,
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers")] + ([("GLIDX", f"Glottocode {g}")] if g else [])))
    for n, t, fld in names:
        if fld == "head" and n == name: continue
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=f"Blench's Atlas (2020), field {fld}: {FIELD.get(fld, 'the Atlas head entry')}." if fld != "head" else "The Atlas's head name.", srcs=["ATLAS"]))
    RELATIONS.append(dict(frm=k, type="spoken_in", to=ST("niger"), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=STATE_NOTE))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=N(l), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=f"Blench's Atlas: {place}"))
    for ref, note in other:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=ref, source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
RELATIONS.append(dict(frm="gbagyi-nkwa", type="spoken_in", to=N("rafi"), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes="Atlas: 'Niger State, Rafi LGA'."))
# Existing records
EXIST = [
    ("nupe", ["lavun", "mariga", "gbako", "agaie", "lapai"], "Atlas: 'Niger State, Lavun, Mariga, Gbako, Agaie, and Lapai LGAs'."),
    ("gbagyi", ["rafi", "chanchaga", "shiroro", "suleja"], "Atlas: 'Niger State, Rafi, Chanchaga, Shiroro and Suleija LGAs'."),
    ("gbari", ["chanchaga", "suleja", "agaie", "lapai"], "Atlas: 'Niger State, Chanchaga, Suleija, Agaie and Lapai LGAs'."),
    ("kakanda", ["agaie", "lapai"], "Atlas: 'Niger state, Agaie and Lapai LGAs'."),
    ("gwandara", ["suleja"], "Atlas: 'Niger State, Suleija LGA'."),
    ("busa", ["borgu"], "Atlas: 'Niger State, Borgu LGA'."),
]
for lang, lgas, note in EXIST:
    if lang not in ("nupe", "busa"):
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=ST("niger"), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
    for l in lgas:
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=N(l), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
GAPS = [
    ("Niger: pre-1991 and pre-1996 LGAs", "The Atlas uses older LGAs: Kusheriki (no longer an LGA), Chanchaga before Bosso was split off, Suleja before Tafa and Shiroro before Munya. Only today's LGAs of the same name are linked; the presence of these languages in Bosso, Tafa, Munya and other later LGAs needs a current source."),
    ("Niger: Zarma's LGAs", "The Atlas places Zarma in 'villages between Mokwa and Kontagora' without naming the LGAs; it is linked at state level only."),
    ("Niger: glottocodes", "Mɨn, Rubu, Wãyã, Samburu (Rafi), Basa-Gurmana, Rop, Tsupamini and the two Kambari clusters have no single matching Glottolog languoid; none is set."),
    ("Niger: extinct or vanishing languages", "The Atlas marks Basa-Gumna and Basa-Kontagora as probably extinct, and Sambuga (Shama–Sambuga) as possibly extinct; their current status needs a recent source."),
    ("Niger: peoples", "The peoples of Niger State (the federal profile names Nupe, Gbagyi, Hausa, Kadara, Koro, Kakanda, Gana-Gana, Dibo, Kambari, Kamuku, Pangu, Dukawa and others) are left for batch 160b."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Niger languages (Blench Atlas, read in full): Songhai branch, 32 new language records and Gbagyi Nkwa, with LGA links; Niger LGA links for Nupe, Gbagyi, Gbari, Kakanda, Gwandara and Busa.")


def report():
    lga = {r["to"] for r in RELATIONS if "lga:niger" in r["to"]}
    L_ = ["# Research batch 160 — Niger: the languages", "",
          f"Researched {ACCESSED}. Phase 3, the first Niger batch (peoples follow in 160b). Created in review; published only after your approval.", "",
          "## Summary", "",
          f"- **{len(LANG)} new language records** from Blench's Atlas, plus **Gbagyi Nkwa** (a variety of Gbagyi) and a new **Songhai** branch (for Zarma).",
          "  - **Kainji (most of them):** the Kamuku, Basa and Kambari clusters; Cicipu, Fungwa, Gurmana and Hùngwəryə; the Baushi cluster of Rafi (Hɨpɨna, Mɨn, Ndəkə, Rubu, Samburu, Wãyã); the Lake languages of Borgu (Reshe, Rop, Tsupamini, Shen); Rin (Pongu), Hun–Saare (Duka) and cLela (Dakarkari)",
          "  - **Nupoid:** Asu, Dibo (Gana-Gana), Gupa–Abawa and Kami",
          "  - **Plateau:** Jijili and Kadara",
          "  - **Mande:** Boko and Kyenga",
          "  - **Songhai:** Zarma",
          "- **Existing records** gain Niger LGA links: Nupe, Gbagyi, Gbari, Kakanda, Gwandara and Busa.",
          f"- **Language links reach {len(lga)} of Niger's 25 LGAs.** The others (Bida, Bosso, Edati, Gurara, Katcha, Mashegu, Mokwa, Munya, Tafa, Wushishi and others) are not named in the Atlas, often because they were created later.",
          "- **Endangered:** Basa-Gumna and Basa-Kontagora are probably extinct (1987), and Sambuga is possibly extinct (2008).",
          "- **Not set:** where Glottolog has no matching entry, or only a combined one, no Glottolog code is set. Glottolog's 'Samburu' is a Kenyan language, so the Rafi Samburu record has no code.", "",
          "## The new records", ""] + [f"**{LANG[k][0]}.** {TEXT[k]}\n" for k in LANG] + [f"**Gbagyi Nkwa.** {TEXT['gbagyi-nkwa']}\n", f"**Songhai.** {TEXT['songhai']}\n"]
    L_ += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_160_niger_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_160_niger_languages_REVIEW.md", "w").write(report())
    lga = {r["to"] for r in RELATIONS if "lga:niger" in r["to"]}
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} niger-LGAs={len(lga)}")
