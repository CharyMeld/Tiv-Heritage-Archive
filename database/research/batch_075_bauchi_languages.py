"""
Research batch 075 — Bauchi (Phase 3, first batch): the languages of Bauchi State and the LGAs where Roger Blench's
Atlas of Nigerian Languages (2020) places them. Researched 2026-10-01. Pattern: batches 063 and 069.

Method: the 502 column-split Atlas entries (batch 063) were searched for 'Bauchi' and the 20 Bauchi LGA names in their
location lines; 63 matched and were read (data/atlas_bauchi_blocks.txt). Removed: Ɗuwai (Yobe), Toro (a Nasarawa language
sharing the LGA's name), Hausa (not yet a language record), and entries placed only in LGAs now in Gombe State (Gombe was
created from Bauchi in 1996): Jara (Ako), Kutto (Bajoga), Kwaami (Kwami). Bole and Ngamo keep only their Bauchi LGAs
(Dukku is now Gombe). Tangale is given under 'Gombe State, Kaltungo, Alkaleri and Akko LGAs' — Alkaleri is a Bauchi LGA
and is linked. Sheni–Ziriya–Kere (Ziriya in Toro LGA; moribund/extinct) is left as a gap.

Sensitive names: the Atlas notes that 'Saya terms are now considered derogatory' (Zaar entry); Saya, Sayawa, Seya and
Seiyara are therefore NOT recorded as names. Glottolog files Zaar as a dialect of a language it calls Saya (saya1246);
the code is used and its naming stated. 'Ɓarawa' (a collective Hausa name used in several Zaar-group entries) is not
recorded as a name of any single language.

Glottocodes from Glottolog's index and per-languoid pages; cluster records take group codes (Gejic geji1246, Polcic
polc1245, Jar jarr1236). No code found: Damlanci.
"""
import json, sys
import batch_063_borno_languages as B63

ACCESSED = "2026-10-01"
SOURCES = {
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf",
                  verification_status="verified", notes="Reused. Bauchi entries read (data/atlas_bauchi_blocks.txt)."),
    "GLIDX": B63.SOURCES["GLIDX"],
}
LGA = {"alkaleri": "Alkaleri", "bauchi": "Bauchi", "bogoro": "Bogoro", "damban": "Damban", "darazo": "Darazo", "dass": "Dass", "gamawa": "Gamawa", "ganjuwa": "Ganjuwa",
       "giade": "Giade", "itas-gadau": "Itas/Gadau", "jama-are": "Jama'are", "katagum": "Katagum", "kirfi": "Kirfi", "misau": "Misau", "ningi": "Ningi", "shira": "Shira",
       "tafawa-balewa": "Tafawa Balewa", "toro": "Toro", "warji": "Warji", "zaki": "Zaki"}
S, B, L = "spelling_variant", "endonym", "alternative"
WC, JA, KA, PL, TA = "@languages:west-chadic", "@languages:jarawan", "@languages:kainji", "@languages:plateau", "@languages:tarokoid"
BOLE = "Chadic (West A: Bole–Ngas major group, Bole group)"
WARJI = "Chadic (West B: Bade–Warji major group, Warji group)"
ZAAR = "Chadic (West B: Zaar group)"
GURU = "Chadic (West B: Zaar group, Guruntum subgroup)"
BOGH = "Chadic (West B: Zaar group, Boghom subgroup)"
JARW = "Benue–Congo (Bantu: Jarawan)"
NJOS = "Benue–Congo (Kainji: Eastern Kainji, Northern Jos group)"
# key: (name, parent, class, [(name, type, field)], [lgas], note, speakers, extra, glottocode)
LANG = {
 "bure": ("Bure", WC, BOLE, [("BuBure", B, "1.B")], ["darazo"], "It is spoken in a single village south-east of Darazo town.", "", "", "bure1242"),
 "beele": ("Ɓeele", WC, BOLE, [("Bele", S, "1.A"), ("Àɓéelé", B, "1.B"), ("Bellawa", L, "2.B")], [], "The Atlas places it in Bauchi State without naming an LGA.", "120 (Temple 1922); a few villages", "", "beel1236"),
 "ciwogai": ("Ciwogai", WC, WARJI, [("Tsagu", S, "1.A")], ["ningi", "darazo"], "", "3,000 (Skinner 1977)", "", "ciwo1236"),
 "damlanci": ("Damlanci", JA, JARW, [("Damlawa", S, "1.A")], ["alkaleri"], "It is spoken at Maccido village. The Atlas notes that the ethnic population is 500 to 1,000 but that the language is now spoken by those over fifty, although it is not moribund.", "", "No Glottolog entry was found.", ""),
 "das": ("Das", WC, ZAAR, [("Lukshi", L, "member"), ("Durr–Baraza", L, "member"), ("Zumbul", L, "member"), ("Wandi", L, "member"), ("Dot", L, "member")], ["toro", "dass"],
         "It is a cluster of Lukshi, Durr–Baraza, Zumbul, Wandi and Dot (Zoɗi); Dot, a single large village south of Bauchi on the Dass road, borrows heavily from Hausa but appears to be thriving.",
         "8,830 (1971); Durr–Baraza 4,700 (1971) or 30,000–40,000 (Caron 2005)", "Glottolog lists the language as Dass (dass1243).", "dass1243"),
 "daza": ("Daza", WC, "Chadic (West A: Bole–Ngas major group)", [], ["darazo"], "It is spoken in a few villages.", "", "The Atlas has no further data. It is not to be confused with Daza (Dazaga), a Saharan name the Atlas gives for Teda.", "daza1244"),
 "deno": ("Deno", WC, BOLE, [], ["darazo"], "The Atlas places it 45 km north-east of Bauchi town.", "9,900 (1971)", "", "deno1239"),
 "diri": ("Diri", WC, WARJI, [("Diriya", S, "1.A"), ("Dirya", S, "1.A"), ("Sago", B, "1.B")], ["ningi", "darazo"], "", "3,750 (1971)", "", "diri1259"),
 "dulbu": ("Dulbu", JA, "Benue–Congo (Bantu: Jarawan, Lábír group)", [], ["bauchi"], "", "80 (1971)", "", "dulb1238"),
 "fyandigeri": ("Fyandigeri", WC, BOLE, [("Fyandigere", B, "1.B")], ["bauchi", "darazo"], "It is spoken in at least thirty villages, though the Atlas notes that many Gera villages no longer speak it.", "13,300 (1971)", "Glottolog lists it as Gera (gera1246).", "gera1246"),
 "galambu": ("Galambu", WC, BOLE, [("Galembi", S, "1.A"), ("Galambe", S, "1.A")], ["bauchi"], "It is spoken in at least fifteen villages.", "8,505 (Temple 1922), 2,020 (Meek 1925) and 1,000 (SIL)", "", "gala1264"),
 "gamo-ningi": ("Gamo–Ningi", KA, NJOS, [("tì-Gamo", B, "1.B"), ("Gamo", L, "member"), ("Ningi", L, "member")], ["ningi"], "It is a cluster of Gamo and Ningi; the Atlas notes that most of its people speak Hausa.", "15,000", "", "gamo1241"),
 "geji": ("Geji", WC, ZAAR, [("Kayauri", L, "2.A"), ("Mәgang", L, "member"), ("Pyaalu", L, "member"), ("Buu", L, "member")], ["toro", "bauchi"],
          "It is a cluster of Mәgang (Bolu), Pyaalu (Pelu), Geji (Gyaazә) and Buu (Zaranda); the Geji member is spoken in about twenty villages.", "650 (1971) to 1,000 (Caron 2005) for Geji itself", "A reading and writing book appeared in 2006 and New Testament extracts in 2007. Glottolog groups the cluster as Gejic (geji1246).", "geji1246"),
 "geruma": ("Geruma", WC, BOLE, [("Gerema", S, "1.A"), ("Germa", S, "1.A"), ("Geerum", B, "1.B")], ["toro", "darazo"], "It is spoken in at least ten villages.", "4,700 (1971)", "", "geru1240"),
 "giiwo": ("Giiwo", WC, BOLE, [("Kirifi", S, "1.A"), ("Bu Giiwo", B, "1.B")], ["alkaleri", "bauchi", "darazo"], "It is spoken in 24 villages.", "3,620 (Temple 1922) and 14,000 (SIL)", "", "giiw1236"),
 "guruntum": ("Guruntum–Mbaaru", WC, GURU, [("Gurutum", S, "1.A"), ("Gùrduŋ", B, "1.B")], ["bauchi", "alkaleri"], "", "10,000 (Jaggar 1988)", "", "guru1271"),
 "guus": ("Guus", WC, ZAAR, [("Sigidi", L, "2.A"), ("Sugudi", L, "2.A")], ["tafawa-balewa"], "It is spoken west of Tafawa Balewa town, in seventeen villages, close to Zaar.", "775 (1950)", "Glottolog lists it as Sigidi.", "sigi1234"),
 "gwa": ("Gwa", JA, JARW, [], ["toro"], "", "fewer than 1,000 (1971)", "", "gwaa1239"),
 "gyem": ("Gyem", KA, "Benue–Congo (Kainji: Eastern Kainji, Northern Jos group)", [("Gema", S, "1.A")], ["toro"], "It is spoken in Lame district.", "2,000 (estimate 2015)", "", "gyem1238"),
 "jar": ("Jar", JA, JARW, [("Jarawa", L, "1.A"), ("Zhar", L, "member"), ("Ɓankal", L, "2.A")], ["dass", "bauchi", "toro"],
         "It is a cluster spread over Plateau, Bauchi and Adamawa states; its Zhar (Ɓankal) member is spoken from Dass town north to Bauchi town, west of the Gongola River, in Dass, Bauchi and Toro LGAs. The Atlas notes that 'Jarawa' is a Hausa name used for many language groups.",
         "Zhar 20,000 (1971)", "A Zhar reading and writing book appeared in 2006 and New Testament extracts in 2007. It is not to be confused with Mbat, another Jarawan language for which 'Jar' is also recorded as a name.", "jarr1236"),
 "jimi": ("Jimi", WC, ZAAR, [], ["darazo"], "", "250 (1971) and 400 (SIL 1973)", "", "jimi1255"),
 "ju": ("Ju", WC, GURU, [], ["bauchi"], "", "150 (1971)", "", "juuu1243"),
 "kariya": ("Kariya", WC, WARJI, [("Kauyawa", L, "1.A"), ("Keriya", S, "1.A"), ("Vinahә", B, "1.B")], ["darazo"], "It is spoken at Kariya Wuro, 30 km south-east of Ningi.", "2,200 (1971) and 3,000 (Skinner 1977)", "", "kari1316"),
 "kir-balar": ("Kir–Balar", WC, BOGH, [("Kir", L, "member"), ("Balar", L, "member")], ["bauchi"], "", "360 for Kir (1971)", "", "kirb1236"),
 "kubi": ("Kubi", WC, BOLE, [("Kuba", S, "1.A")], ["darazo"], "It is spoken 40 km north-east of Bauchi town.", "1,090 (Temple 1922) and 500 (SIL 1973)", "", "kubi1239"),
 "kudu-camo": ("Kudu–Camo", KA, "Benue–Congo (Kainji: Eastern Kainji, Northern Jos group, Ningi cluster)", [("Kuda", S, "1.A"), ("Kudu", L, "member"), ("Camo", L, "member")], ["ningi"],
               "The Atlas describes the language as moribund, perhaps extinct.", "", "", "kudu1241"),
 "labir": ("Labɨr", JA, JARW, [("Lábɨ̀r", S, "1.A")], ["bauchi", "alkaleri"], "It is spoken south of the Bauchi–Gombe road, from the Gongola River at Kanyallo, in Bauchi LGA, to Gar in Alkaleri LGA, in around ten villages.", "perhaps 5,000 (2019 estimate)", "", "labi1245"),
 "lame": ("Lame", JA, JARW, [("Rufu", L, "1.A"), ("Ruhu", L, "member"), ("Mbaru", L, "member"), ("Gura", L, "member")], ["toro"], "It is a cluster of Ruhu, Mbaru and Gura, spoken in Lame district.", "2,000 (SIL 1973)", "", "lame1257"),
 "lere": ("Lere", KA, NJOS, [("Si", L, "member"), ("Gana", L, "member"), ("Takaya", L, "member")], ["toro"],
          "It is a cluster of Si, Gana and Takaya; the Atlas records Gana and Takaya as extinct and the cluster's languages as extinct.", "765 (1949) and 1,000 (SIL 1973)", "", "lere1241"),
 "mangas": ("Mangas", WC, BOGH, [("Maás", B, "1.B")], ["bauchi"], "", "180 (1971)", "", "mang1416"),
 "mburku": ("Mburku", WC, "Chadic (West B: Warji group)", [("Barko", L, "1.A"), ("Barke", L, "1.A"), ("Vә Mvәran", B, "1.B")], ["darazo"], "", "210 (1949–50) and 4,000 (Skinner 1977)", "", "mbur1239"),
 "miya": ("Miya", WC, "Chadic (West B: Warji group)", [("Muya", S, "1.A")], ["darazo"], "It is spoken at Miya town and its hamlets, in Ganjuwa district (the Atlas gives Darazo LGA; Ganjuwa is now an LGA of its own).", "5,200 (1971)", "", "miya1266"),
 "paa": ("Pa'a", WC, WARJI, [("Paha", S, "1.A"), ("Afa", L, "1.A"), ("FuCaka", B, "1.B")], ["ningi", "darazo"], "", "8,500 (1971) and 20,000 (Skinner 1977)", "", "paaa1242"),
 "polci": ("Polci", WC, ZAAR, [("Zul", L, "member"), ("Mbaram", L, "member"), ("Dir", L, "member"), ("Buli", L, "member"), ("Langas", L, "member"), ("Luri", L, "member")], ["bauchi", "toro"],
           "It is a cluster of Zul, Mbaram, Dir, Buli, Langas, Luri and Polci; Zul, spoken in about fifteen villages, is mutually intelligible with Mbaram.", "6,150 or more (1971)", "Glottolog groups the cluster as Polcic (polc1245).", "polc1245"),
 "sanga": ("Sanga", KA, "Benue–Congo (Kainji: Eastern Kainji, Northern Jos group)", [("Aŋma Asanga", B, "1.B")], ["toro"], "It is spoken in Lame district.", "1,700 (1950) and 5,000 (SIL 1973)", "Glottolog lists it as Sanga (Nigeria).", "sang1329"),
 "shall-zwall": ("Shall–Zwall", PL, "Benue–Congo (Plateau: Beromic)", [("Shall", L, "member"), ("Zwall", L, "member")], ["dass"], "It is a cluster of Shall and Zwall.", "", "", "shal1242"),
 "shau": ("Shau", KA, "Benue–Congo (Kainji: Eastern Kainji, Northern Jos group)", [("Sho", S, "1.A"), ("Lìsháù", B, "1.B")], ["toro"], "It is spoken in the villages of Shau and Mana, and the Atlas describes it as almost extinct.", "", "", "shau1238"),
 "shiki": ("Shɨkɨ", JA, JARW, [], ["bauchi"], "", "300 (1971)", "Glottolog lists it as Shiki.", "shik1242"),
 "siri": ("Siri", WC, WARJI, [], ["darazo", "ningi"], "", "2,000 (1971) and 3,000 (Skinner 1977)", "", "siri1278"),
 "sur": ("Sur", TA, "Benue–Congo (Tarokoid)", [("Suru", S, "1.A"), ("Tapshin", L, "1.A")], ["dass"], "It is spoken in the villages of Tapshin and Myet.", "", "", "surr1238"),
 "tala": ("Tala", WC, GURU, [], ["bauchi"], "It is spoken in Zungur district.", "", "", "tala1295"),
 "tangale": ("Tangale", WC, "Chadic (West A: Bole–Ngas major group, Bole–Tangale group)", [("Tangle", S, "1.A"), ("Táŋlɛ̀", B, "1.B")], ["alkaleri"],
             "The Atlas places it mainly in Gombe State (Kaltungo and Akko LGAs), and also in Alkaleri LGA of Bauchi State.", "36,000 (1952) and 100,000 (SIL 1973)", "", "nucl1696"),
 "warji": ("Warji", WC, WARJI, [("Sәrzakwai", B, "1.B"), ("Sirzakwai", B, "1.B")], ["darazo", "ningi"],
           "It is spoken in Ganjuwa district (the Atlas gives Darazo LGA) and in Warji district of Ningi LGA (Warji is now an LGA of its own), and also in Birnin Kudu LGA of Jigawa State.", "28,000 (1971) and 50,000 (Skinner 1977)",
           "Glottolog lists it as Warji-Gala (warj1253).", "warj1253"),
 "zaar": ("Zaar", WC, ZAAR, [("Za'r", S, "1.A"), ("Zar", S, "1.A"), ("Vìk Zaar", B, "1.B"), ("Vigzar", B, "1.B")], ["tafawa-balewa"],
          "It is spoken west of Tafawa Balewa town; its dialects are Kal, Gambar Leere and Lusa. The Atlas records other names for the people but notes that the 'Saya' names are now considered derogatory; they are not recorded here.",
          "50,000 (1971 and SIL 1973)", "A newsletter and a reading and writing book have been published since 2004–2006, and New Testament extracts in 2007. Glottolog treats Zaar as a dialect of a language it lists as saya1246.", "saya1246"),
 "zangwal": ("Zangwal", WC, GURU, [], ["bauchi"], "", "", "The Atlas has no further data.", "zang1255"),
 "zeem": ("Zeem–Caari–Danshe–Dyarim", WC, ZAAR, [("Zeem", L, "member"), ("Dyarim", L, "member"), ("Caari", L, "member")], ["toro"],
          "It is a cluster whose Zeem, Tule and Danshe members are extinct; Dyarim, whose main settlement is about 7 km south of Toro town, has about 2,000 ethnic Dyarim but only about 100 fluent speakers, and is threatened by a switch to Hausa.",
          "Caari 'a few hundred' (Caron 2005)", "", "zeem1242"),
 "zumbun": ("Zumbun", WC, "Chadic (West B: Warji group)", [("Jimbin", S, "1.A"), ("Vina Zumbun", B, "1.B")], ["darazo"], "", "1,500 (1971)", "", "zumb1240"),
}
FIELD = dict(B63.FIELD)
CAVEAT = "The Atlas may name the LGA as it was when its data were collected; several Bauchi LGAs (e.g. Ganjuwa, Warji, Kirfi, Bogoro, Dass) were created from older ones."


def text(k):
    name, parent, cls, names, lgas, note, spk, extra, g = LANG[k]
    where = (f" in {', '.join(LGA[l] for l in lgas[:-1])}{' and ' if len(lgas) > 1 else ''}{LGA[lgas[-1]]} LGA{'s' if len(lgas) > 1 else ''} of Bauchi State") if lgas else " in Bauchi State"
    t = f"{name} is a language spoken{where}, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as {cls}."
    alt = [n for n, _, f in names if n != name and f not in ("GL", "member")]
    if alt: t += f" Other names include {', '.join(alt[:6])}."
    if note: t += " " + note
    if spk: t += f" The Atlas cites speaker figures of {spk}."
    if extra: t += " " + extra
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for k, (name, parent, cls, names, lgas, note, spk, extra, g) in LANG.items():
    f = dict(lang_type="language", name=name, slug=k, parent_id=parent, summary=text(k).split(". ")[0] + ".", description=text(k))
    if g: f["glottocode"] = g
    RECORDS.append(dict(key=k, table="languages", evidence="single_reliable_source", level="well_documented", fields=f,
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers")] + ([("GLIDX", f"Glottocode {g}")] if g else [])))
    RELATIONS.append(dict(frm=k, type="spoken_in", to="@admin_units:state:bauchi", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes="Blench's Atlas."))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=f"@admin_units:lga:bauchi/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=CAVEAT))
    for n, t, fld in names:
        if n == name:
            continue
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=f"Blench's Atlas (2020), {('field ' + fld + ': ') if fld[0].isdigit() else ''}{FIELD[fld]}.", srcs=["ATLAS"]))
EXISTING = [
    ("izere", ["toro"], "Atlas: 'Bauchi State, Toro LGA; Plateau State, Jos South and Barkin Ladi LGAs'."),
    ("jere", ["toro"], "Atlas: 'Plateau State, Bassa LGA; Bauchi State, Toro LGA'."),
    ("lemoro", ["toro"], "Atlas: 'Plateau State, Bassa LGA; Bauchi State, Toro LGA'."),
    ("tunzu", ["toro"], "Atlas: 'Plateau State, Jos East ... Bauchi State, Toro LGA (2 villages)'."),
    ("vaghat-ya-bijim-legeri", ["tafawa-balewa"], "Atlas: 'Plateau State, Mangu LGA; Bauchi State, Tafawa Balewa LGA' (the Ya member: 10 villages, 20 km south of Tafawa Balewa)."),
    ("zari", ["toro", "tafawa-balewa"], "Atlas: 'Bauchi State, Toro and Tafawa Balewa LGAs; Plateau State, Jos LGA'."),
    ("bole", ["alkaleri", "darazo"], "Atlas: 'Bauchi State, Dukku, Alkaleri, and Darazo LGAs' (Dukku is now in Gombe State; not linked)."),
    ("ngamo", ["darazo"], "Atlas: 'Bauchi State, Darazo LGA, Darazo district and Dukku LGA, Nafada district' (Dukku/Nafada now in Gombe; not linked)."),
    ("karekare", ["gamawa", "misau"], "Atlas: 'Bauchi State, Gamawa and Misau LGAs, Yobe State, Fika LGA'."),
]
for lang, lgas, note in EXISTING:
    RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to="@admin_units:state:bauchi", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
    for l in lgas:
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=f"@admin_units:lga:bauchi/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
GAPS = [
    ("Bauchi: LGAs with no language placed by name", "Bogoro, Damban, Ganjuwa, Giade, Itas/Gadau, Jama'are, Katagum, Kirfi, Shira, Warji and Zaki have no Atlas language placed in them by name; most were created after the Atlas's sources, and the northern LGAs are largely Hausa- and Fulfulde-speaking, which the Atlas does not place by LGA."),
    ("Bauchi: Hausa and Fulfulde", "The Atlas names Bauchi among the states where Hausa is a first language, but Hausa is not yet a language record."),
    ("Bauchi: Sheni–Ziriya–Kere", "The Atlas places the Ziriya member in Toro LGA (moribund/extinct); not recorded."),
    ("Bauchi: Damlanci", "No Glottolog entry found."),
    ("Bauchi: homonyms", "Daza (Bauchi, Chadic) shares its name with Daza, an alias of Teda (Saharan); Jar (Jarawan cluster) shares its name with an alias of Mbat. Disambiguating notes added; the QC lists them as checks."),
    ("Bauchi: sensitive names", "The Atlas notes that the 'Saya' names (Saya, Sayawa, Seya, Seiyara) are now considered derogatory; they are not recorded. 'Ɓarawa' is a collective Hausa name for several Zaar-group peoples; not recorded as a language name."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Bauchi languages (Blench Atlas): {len(LANG)} languages, LGA links, other names; Bauchi links for {len(EXISTING)} existing languages.")


def report():
    per = {l: [] for l in LGA}
    for k, v in LANG.items():
        for l in v[4]:
            per[l].append(v[0])
    for lang, lgas, _ in EXISTING:
        for l in lgas:
            per[l].append(lang.split('-')[0].title() + " (existing)")
    L = ["# Research batch 075 — Bauchi: the languages", "",
         f"Researched {ACCESSED}. Phase 3, the first Bauchi batch. Created in review; published only after your approval.", "",
         "## Summary", "",
         f"- **{len(LANG)} languages** from Blench's Atlas, {sum(1 for v in LANG.values() if v[8])} with Glottocodes. They go under the existing West Chadic, Jarawan, Kainji, Plateau and Tarokoid branches.",
         "- **Bauchi is one of Nigeria's densest language areas.** Most of these are small Chadic and Jarawan Bantu languages of Toro, Darazo, Bauchi and Ningi LGAs.",
         f"- **{len(EXISTING)} existing languages get Bauchi links:** Izere, Jere, Lemoro, Tunzu, Vaghat–Ya, Zari, Bole, Ngamo and Karekare.",
         "- **Gombe** was created from Bauchi in 1996. Entries placed only in today's Gombe LGAs (Jara, Kutto, Kwaami) are left for a Gombe batch, and Dukku and Nafada parts are not linked.",
         "- **Sensitive names:** the Atlas notes that the 'Saya' names for the Zaar are now considered derogatory, so they are **not recorded**.",
         f"- **{len(RELATIONS)} links** and **{len(NAMES)} other names**.",
         "- **11 LGAs have no language placed in them by name.** Most were created later, and the northern LGAs are largely Hausa- and Fulfulde-speaking. This is listed as a gap.", "",
         "## Languages per LGA (as the Atlas names them)", "", "| LGA | Count | Languages |", "|---|---|---|"]
    for l, ns in per.items():
        L.append(f"| {LGA[l]} | {len(ns)} | {', '.join(ns) or '—'} |")
    L += ["", "## The languages", ""] + [f"**{LANG[k][0]}** ({LANG[k][8] or 'no Glottocode'}). {text(k)}\n" for k in LANG]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_075_bauchi_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_075_bauchi_languages_REVIEW.md", "w").write(report())
    print(f"languages={len(LANG)} relations={len(RELATIONS)} names={len(NAMES)} glotto={sum(1 for v in LANG.values() if v[8])}")
