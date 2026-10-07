"""
Research batch 166b — Kaduna (Phase 3): the Plateau and other languages of southern Kaduna, and the LGAs where Roger
Blench's Atlas of Nigerian Languages (2020) places them. Researched 2026-10-07. Pattern: batch 166.

27 languages are new records (all under the Plateau branch except Ajuwa–Ajegha, which neither the Atlas nor Glottolog
classifies); existing records (Ashe, Aten, Berom, Gbagyi, Gbari, Gwandara, Idun, Kadara, Mada, Ninzo, Numbu–Gbantu–Nunku)
get Kaduna links.
Boundaries: the Atlas uses LGAs from before the 1989–1997 splits (Jema'a before Kaura and Sanga; Kachia before Zangon
Kataf; Chikun before Kajuru). As in batch 166, today's LGAs of the same name are linked from the Atlas, and an LGA is
linked as 'reported' where Wikipedia or INEC's 2015 directory places the language's own name, a name the Atlas gives
for it, or a village the Atlas names (e.g. Kamantan ward, Zangon Kataf; Kagoma ward, Jema'a; Nok ward, Jaba; Kobin and
Wambe, Sanga; Kushampa between Kurmin Jibrin and Kubacha, Kagarko).
Classification: from the Atlas field 5; where the Atlas has none, from Glottolog (Ajiya: Kuturmi–Ajiya; Ekhwa:
Northern Plateau; Gwara: Koroic; Cori (Kyoli): Hyamic; Ikryo: no code, as Glottolog's single 'Kuturmi' covers both
Ikryo and Obiro, which the Atlas calls West Kuturmi).
Not linked: Izere (the Atlas: Jema'a 'probably migrants only'); Bacama (fishing camps 'north east of Kaduna town').
"""
import json, sys
import batch_087_kano_languages as L87
import batch_063_borno_languages as B63
import batch_166_kaduna_kainji_languages as K166

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
GL = lambda g, n: dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title=f"Glottolog languoid {g}", organisation="Glottolog (Max Planck Institute for Evolutionary Anthropology)",
                       url=f"https://glottolog.org/resource/languoid/id/{g}", verification_status="verified", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Kaduna Plateau entries read in full: Ajiya, Ajuwa–Ajegha, Anib, Ayu, Cori, Ekhwa, Gbǝtsu, Gwara, Gyong, Hyam cluster, Ikryo, Jju, Kamantan, Kulu, Nincut, Ninkyop–Nindem, Ningye, Ninka, Nyankpa, Obiro, Sambe, Shakara, Shamang, Shang, Tinɔr–Myamya, Tyap cluster, Zhire; also Ashe, Aten, Berom, Eda/Edra/Enezhe (Kadara), Gbagyi, Gbari, Gwandara, Idun, Mada, Ninzo, Numbu–Gbantu–Nunku, Izere."),
    "GLIDX": L87.SOURCES["GLIDX"],
    "INEC": K166.SOURCES["INEC"],
    "GLAJ": GL("idon1238", "'Idon', classified Benue-Congo Plateau > Northern > Nuclear Northern > Kuturmi-Ajiya."),
    "GLEK": GL("ikug1238", "'Iku-Gora-Ankwa', classified Benue-Congo Plateau > Northern Benue-Congo Plateau."),
    "GLGW": GL("gwar1240", "'Gwara', classified Benue-Congo Plateau > West-Central > Northwestern > Koroic > Duyaic."),
    "GLCO": GL("cori1240", "'Kyoli', classified Benue-Congo Plateau > West-Central > Northwestern > Hyamic."),
    "GLKU": GL("kutu1262", "'Kuturmi', classified Benue-Congo Plateau > Northern > Nuclear Northern > Kuturmi-Ajiya."),
    "GLNK": GL("anin1242", "'Aninka', classified Benue-Congo Plateau > Ninzic; map point near Sanga LGA."),
    "WZK": WS("Zangon Kataf", "LGA created from the old Kachia LGA in 1989; its Tyap name Á̱nietcen-A̱fakan; Zangon Kataf is also a town in the chiefdom of the Atyap; towns include Kamantan."),
    "WKAU": WS("Kaura, Nigeria", "Kaura (Tyap: Watyap), town and LGA, carved out of Jema'a LGA; other towns include Takad (Attakar), Manchok and Kagoro."),
    "WKDS": WS("Kaduna State", "'The Hausa people of Zaria & the Ham people of Jaba, are said to be the old ancestral of the region's north & south respectively.'"),
    "WJEM": WS("Jema'a", "Subdivisions include Kagoma (Gwong), Kaninkon and Kafanchan A and B."),
}
S, B, L, M = "spelling_variant", "endonym", "alternative", "alternative"
FIELD = dict(B63.FIELD, **{"1.C": "the speakers' name for themselves"})
KD = lambda l: f"@admin_units:lga:kaduna/{l}"
ST = lambda s: f"@admin_units:state:{s}"
P = "@languages:plateau"
OLD = {"jema-a": "Jema'a LGA, as it was before Kaura and Sanga were created", "kachia": "Kachia LGA, as it was before Zangon Kataf was created in 1989"}
# key: (name, parent, cls, [(name, type, field)], [atlas lgas], [(lga, source, note)], place, speakers, extra, glottocode, extra_srcs)
LANG = {
 "ajiya": ("Ajiya", P, "Plateau (Glottolog: Kuturmi–Ajiya group; the Atlas gives no classification)", [("Ajuli", L, "1.A"), ("Idon", L, "2.A"), ("Idong", L, "2.A"), ("Idon-Doka-Makyali", L, "2.A")], ["kachia"],
           [("kajuru", "INEC", "INEC has a ward Idon, with a polling unit at Makyali, in Kajuru LGA (Idon and Makyali are names the Atlas gives for the language).")],
           "The Atlas places it in Kachia LGA, as it was before the later splits.", "", "Glottolog lists it as Idon (idon1238).", "idon1238", ["GLAJ"]),
 "ajuwa-ajegha": ("Ajuwa–Ajegha", None, "unclassified (the Atlas gives no classification, and no Glottolog languoid was found)", [("Ajuwa", B, "1.B")], ["kajuru"], [],
                  "The Atlas places it in Kajuru LGA.", "", "", None, []),
 "anib": ("Anib", P, "Plateau (Ninzic)", [("Kanufi", L, "1.A"), ("Aninib", L, "1.C"), ("Karshi", L, "2.B")], ["jema-a"], [],
          "The Atlas places it in Jema'a LGA, in two villages about 5 km west of Gimi, on the Akwanga road towards Kafanchan.", "2,000 (2006 estimate)",
          "The Atlas notes two forms, Kanufi I (locally Ákpúrkpòd) and Kanufi II (Ákob). Glottolog lists it as Kanufi.", "kanu1277", []),
 "ayu": ("Ayu", P, "Plateau (Ninzic, tentatively)", [("Aya", L, "1.A")], ["jema-a"],
         [("sanga", "INEC", "INEC lists the polling unit Fadan Ayu I in Bokana ward of Sanga LGA.")], "The Atlas places it in Jema'a LGA, as it was before Sanga was created.", "2,642 (1934)", "", "ayuu1242", []),
 "cori": ("Cori", P, "Plateau (Glottolog: Hyamic; the Atlas gives no classification)", [("Chori", L, "1.A"), ("Kyoli", L, "GL")], ["jema-a"], [],
          "The Atlas places it in Jema'a LGA.", "", "Glottolog lists it as Kyoli (cori1240).", "cori1240", ["GLCO"]),
 "ekhwa": ("Ekhwa", P, "Plateau (Glottolog: Northern Plateau; the Atlas gives no classification)", [("Iku-Gora-Ankwa", L, "1.A"), ("ékhwá", B, "1.B"), ("Ahua", L, "2.A"), ("Ehwa", L, "2.C")], ["kachia"], [],
           "The Atlas places it in Kachia LGA; INEC has a ward Ankwa in Kachia LGA.", "", "Glottolog lists it as Iku-Gora-Ankwa (ikug1238).", "ikug1238", ["GLEK", "INEC"]),
 "gbetsu": ("Gbǝtsu", P, "Plateau (Ninzic: Mada cluster)", [("Katanza", L, "2.A")], ["jema-a"], [],
            "The Atlas places it in Jema'a LGA, in about six villages east of the road north of Akwanga.", "5,000 (2008 estimate)", "", None, []),
 "gwara": ("Gwara", P, "Plateau (Glottolog: Koroic; the Atlas gives no classification)", [("iGwara", B, "1.B"), ("Gora", L, "2.C")], ["kagarko", "jaba"], [],
           "The Atlas places it in Kagarko and Jaba LGAs.", "", "", "gwar1240", ["GLGW"]),
 "gyong": ("Gyong", P, "Plateau (Western: Gyongic)", [("Kagoma", L, "1.A"), ("Agoma", L, "1.A"), ("Gong", L, "1.C"), ("Gwong", L, "2.B")], ["jema-a"], [],
           "The Atlas places it in Jema'a LGA; Wikipedia and INEC list Kagoma (Gwong) among the subdivisions and wards of Jema'a LGA.", "6,250 (1934)",
           "Glottolog lists it as Kagoma.", "kago1247", ["WJEM", "INEC"]),
 "hyam": ("Hyam", P, "Plateau (Hyamic)", [("Ham", L, "1.A"), ("Hum", L, "1.A"), ("Jaba", L, "1.B"), ("Kwyeny", M, "member"), ("Yaat", M, "member"), ("Saik", M, "member"), ("Dzar", M, "member"), ("Hyam of Nok", M, "member")],
          ["kachia", "jema-a"], [("jaba", "WKDS", "Wikipedia (Kaduna State) calls the Ham the people of Jaba, and INEC has a ward Nok in Jaba LGA (Hyam of Nok is a member of the cluster).")],
          "The Atlas places the cluster in Kachia and Jema'a LGAs, as they were before the later splits.", "43,000",
          "The Atlas records Scripture portions from 1923 and an alphabet chart (1999), and notes that 'Kwak', listed elsewhere as a language, is only a Hyam town name.", "hyam1245", ["WKDS"]),
 "ikryo": ("Ikryo", P, "Plateau", [("ìkryó", B, "1.C"), ("West Kuturmi", L, "2.B")], ["kachia"], [],
           "The Atlas places it in Kachia LGA.", "", "With Obiro it is called West Kuturmi; Glottolog has a single 'Kuturmi' languoid (kutu1262) in its Kuturmi–Ajiya group, so no code is set.", None, ["GLKU"]),
 "jju": ("Jju", P, "Plateau (Central)", [("Kәjju", B, "1.B"), ("Bajju", L, "1.C"), ("Baju", L, "1.C"), ("Kaje", L, "2.B"), ("Kajji", L, "2.B"), ("Kache", L, "2.B")], ["kachia", "jema-a"], [],
         "The Atlas places it in Kachia and Jema'a LGAs, as they were before the later splits.", "26,600 (1949); possibly 200,000 (1984)",
         "The Atlas records an official orthography, a New Testament (1983) and a literacy programme.", "jjuu1238", []),
 "kamantan": ("Kamantan", P, "Plateau (Western: Gyongic)", [("Angan", L, "1.C")], ["kachia"],
              [("zangon-kataf", "INEC", "INEC has a ward Kamantan in Zangon Kataf LGA, and Wikipedia lists Kamantan among the towns of Zangon Kataf, which was created from Kachia LGA in 1989.")],
              "The Atlas places it in Kachia LGA, as it was before Zangon Kataf was created.", "3,600 (1949); 10,000 (1972)", "", "kama1358", ["WZK"]),
 "kulu": ("Ikulu", P, "Plateau (Northwestern)", [("Kulu", S, "head"), ("Ikolu", L, "1.A"), ("Ankulu", B, "1.B"), ("Bekulu", L, "1.C")], ["kachia"],
          [("zangon-kataf", "INEC", "INEC has a ward Kamuru Ikulu North in Zangon Kataf LGA.")],
          "The Atlas places it in Kachia LGA, as it was before Zangon Kataf was created.", "6,000 (1949)", "The Atlas's head name is Kulu.", "ikul1238", []),
 "nincut": ("Nincut", P, "Plateau (Beromic: Berom)", [("Aboro", L, "2.B")], [],
            [("sanga", "INEC", "INEC has a ward Fadan Karshi in Sanga LGA; the Atlas places Nincut about 7 km north of Fadan Karshe.")],
            "The Atlas places it in eight villages about 7 km north of Fadan Karshe, without naming the LGA.", "about 5,000 (2003 estimate)",
            "The Atlas notes that it is threatened by a switch to Hausa, and that it is treated as a language separate from Berom.", "ninc1234", []),
 "ninkyop-nindem": ("Ninkyop–Nindem", P, "Plateau (Ninzic)", [("Ninkyop", M, "member"), ("Nindem", M, "member"), ("Kaninkon", L, "1.A"), ("Kaningkwom", L, "1.A"), ("Ninkyob", L, "1.C")], ["jema-a"], [],
                    "The Atlas places the cluster in Jema'a LGA; Wikipedia lists Kaninkon among the subdivisions of Jema'a LGA.", "2,291 (1934, Ninkyop)", "Glottolog lists it as Kaningdon-Nindem.", "kani1277", ["WJEM"]),
 "ningye": ("Ningye", P, "Plateau (Ninzic)", [("Ningeshe", L, "1.A")], [],
            [("sanga", "INEC", "INEC lists polling units at Kobin and Wambe, two of the five villages the Atlas names, in Nandu ward of Sanga LGA.")],
            "The Atlas places it in five villages along the Fadan Karshe–Akwanga road, directly north of Gwantu: Kobin, Akwankwan, Wambe, Ningeshen Kurmi and Ningeshen Sarki.",
            "fewer than 5,000 (2003)", "", "ning1271", []),
 "ninka": ("Ninka", P, "Plateau (Ninzic)", [("Aninka", L, "GL")], ["sanga"], [],
           "The Atlas places it in Sanga LGA.", "fewer than 5,000", "The Atlas also gives Sanga as a name for it; the name is used too of the separate Sanga language and, the Atlas notes, mistakenly of the Numbu–Gbantu–Nunku cluster, so it is not stored as an alias here. Glottolog lists it as Aninka (Ninzic).", "anin1242", ["GLNK"]),
 "nyankpa": ("Nyankpa", P, "Plateau (Koro)", [("Yeskwa", L, "2.A"), ("Yasgua", L, "2.A"), ("Nnaŋkpa", B, "1.B")], ["jema-a"], [],
             "The Atlas places it in Jema'a LGA and in Nasarawa State.", "13,000 (1973)", "The Atlas names Tattara as the 'standard' form of Yeskwa.", "yesk1239", []),
 "obiro": ("Obiro", P, "Plateau (Northwestern group)", [("ìbìrò", B, "1.C"), ("West Kuturmi", L, "2.B")], ["kachia"], [],
           "The Atlas places it in Kachia LGA, at Antara village.", "", "With Ikryo it is called West Kuturmi; no Glottolog code is set.", None, []),
 "sambe": ("Sambe", P, "Plateau (Alumic)", [], [], [("sanga", "INEC", "INEC lists the polling unit Sambe in Ninzam North ward of Sanga LGA.")],
           "The Atlas places it in Kaduna State without naming the LGA.", "2 (2005)",
           "The Atlas describes it as moribund, with some rememberers in 2005 but probably extinct by 2016; its speakers have shifted to Ninzo.", "samb1307", []),
 "shakara": ("Shakara", P, "Plateau (Ndunic)", [("ìShákárá", B, "1.B"), ("Tari", L, "2.B")], [],
             [("sanga", "INEC", "INEC lists the polling unit Mayir in Ayu ward of Sanga LGA; the Atlas places Shakara 7 km due west of Mayir.")],
             "The Atlas places it in a line of villages 7 km due west of Mayir, on the Fadan Karshe–Wamba road.", "3,000 (2003 estimate)", "Its speakers also use Hausa (Atlas).", "shak1238", []),
 "shamang": ("Shamang", P, "Plateau (Hyamic)", [("Samban", L, "1.A"), ("Samang", L, "1.C")], ["kachia", "jema-a"],
             [("jaba", "INEC", "INEC has a ward Sambam in Jaba LGA, with a polling unit at Ungwan Samban Gida (Samban is the Atlas's other name for the language).")],
             "The Atlas places it in Kachia and Jema'a LGAs, as they were before the later splits.", "", "", "sham1277", []),
 "shang": ("Shang", P, "Plateau (Hyamic)", [("Kushampa", L, "1.A")], ["kachia", "jema-a"],
           [("kagarko", "INEC", "The Atlas places Kushampa A between Kurmin Jibrin and Kubacha; INEC lists both as wards of Kagarko LGA.")],
           "The Atlas places it in Kachia and Jema'a LGAs, in two settlements, Kushampa A and B; Kushampa A is on the road between Kurmin Jibrin and Kubacha.", "", "", "shan1278", []),
 "tinor-myamya": ("Tinɔr–Myamya", P, "Plateau (Koro)", [("Begbere-Ejar", L, "2.A"), ("Koro Agwe", L, "2.C"), ("Agwere", L, "2.C"), ("Koro Makama", L, "2.C")], ["kagarko"], [],
                  "The Atlas places it in Kagarko LGA.", "35,000 including Ashe (1972)",
                  "Its peoples have no common name for themselves and refer to their villages; they share the ethnonym Uzar (plural Bazar; the language Ìzar, the origin of 'Ejar') with the Ashe. Ashe, Hyam and Gbagyi are nearby languages often spoken by the Tinɔr.", "begb1241", []),
 "tyap": ("Tyap", P, "Plateau (Central)", [("Kataf", L, "1.A"), ("Katab", L, "2.A"), ("Atyap", L, "1.C"), ("Atyab", L, "1.C"), ("Gworok", M, "member"), ("Atakar", M, "member"), ("Sholio", M, "member"), ("Kacicere", M, "member"), ("Kafancan", M, "member")],
          ["kachia", "jema-a"],
          [("zangon-kataf", "WZK", "Wikipedia gives Zangon Kataf's Tyap name and calls Zangon Kataf a town in the chiefdom of the Atyap; the LGA was created from Kachia in 1989."),
           ("kaura", "WKAU", "Wikipedia gives Kaura's Tyap name (Watyap) and lists Takad (Attakar), Manchok and Kagoro among its towns; the Atlas places Atakar, Sholio (around Manchok) and Gworok (Kagoro) in the old Jema'a LGA.")],
          "The Atlas places the cluster in Kachia, Saminaka and Jema'a LGAs, as they were before the later splits; Gworok (Kagoro), Atakar, Sholio (around Manchok) and Kafancan in Jema'a.",
          "more than 130,000 (1990, Tyap); 9,300 (1949, Gworok); 5,700 (1949, Sholio)",
          "The Atlas records a New Testament in Gworok and a Bible translation in progress in Tyap. Glottolog lists Tyap (tyap1238), with Kagoro, Sholio and Kafanchan as separate languoids.", "tyap1238", ["WZK", "WKAU"]),
 "zhire": ("Zhire", P, "Plateau (Hyamic)", [("Kenyi", L, "2.B")], ["kachia", "jema-a"],
           [("kagarko", "INEC", "INEC lists the polling unit Kenyi in Aribi ward of Kagarko LGA (Kenyi is the Atlas's other name for the language).")],
           "The Atlas places it in Kachia and Jema'a LGAs, as they were before the later splits.", "", "", "zhir1238", []),
}
TEXT = {}
for k, (name, par, cls, names, lgas, rep, place, spk, extra, g, xs) in LANG.items():
    if cls.startswith("Plateau (Glottolog: "):
        grp = cls[len("Plateau (Glottolog: "):].split(";")[0]
        t = f"{name} is a language of Kaduna State, according to Roger Blench's Atlas of Nigerian Languages (2020), which gives no classification; Glottolog places it in the Plateau languages ({grp})."
    elif cls.startswith("unclassified"):
        t = f"{name} is a language of Kaduna State, according to Roger Blench's Atlas of Nigerian Languages (2020), which gives no classification; no Glottolog languoid was found for it."
    else:
        t = f"{name} is a language of Kaduna State, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as {cls}."
    t += " " + place
    alt = [n for n, _, f in names if f not in ("member", "head") and n != name]
    mem = [n for n, _, f in names if f == "member"]
    if mem: t += f" Its members include {', '.join(mem)}."
    if alt: t += f" Other names include {', '.join(alt[:5])}."
    if spk: t += f" Speaker figures in the Atlas: {spk}."
    if extra: t += " " + extra
    TEXT[k] = t

RECORDS, NAMES, RELATIONS = [], [], []
for k, (name, par, cls, names, lgas, rep, place, spk, extra, g, xs) in LANG.items():
    f = dict(lang_type="language", name=name, slug=k if k != "kulu" else "ikulu", parent_id=par, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k])
    if par is None: f.pop("parent_id")
    if g: f["glottocode"] = g
    srcs = [("ATLAS", name)] + ([("GLIDX", f"Glottolog {g}")] if g else []) + [(x, name) for x in xs] + ([("INEC", f"{name}: village placement")] if any(s == "INEC" for _, s, _ in rep) and "INEC" not in xs else [])
    RECORDS.append(dict(key=k, table="languages", evidence="multiple_sources", level="well_documented" if par else "reported", fields=f, srcs=srcs))
    for n, t, fld in names:
        if fld == "head" and n == name: continue
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=("Glottolog's name for the language." if fld == "GL" else f"Blench's Atlas (2020), field {fld}: {FIELD.get(fld, 'the Atlas head entry')}."), srcs=["GLIDX" if fld == "GL" else "ATLAS"]))
    RELATIONS.append(dict(frm=k, type="spoken_in", to=ST("kaduna"), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes="Atlas: Kaduna State."))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=KD(l), source="ATLAS", evidence="single_reliable_source", level="well_documented",
                              notes=f"Blench's Atlas: {place}" + (f" (The Atlas's {OLD[l]}.)" if l in OLD else "")))
    for l, src, note in rep:
        if l in lgas: continue
        RELATIONS.append(dict(frm=k, type="spoken_in", to=KD(l), source=src, evidence="single_reliable_source", level="reported", notes=f"Placement not stated by the Atlas: {note}"))
EXIST = [
    ("ashe", True, ["kagarko"], "Atlas: 'Kaduna State, Kagarko LGA, Nasarawa State, Karu LGA'; 8 villages (2008) between Katugal and Kubacha."),
    ("aten", True, ["jema-a"], "Atlas: 'Plateau State, Barkin Ladi LGA; Kaduna State, Jema'a LGA'."),
    ("berom", True, ["jema-a"], "Atlas: 'Plateau State, Jos and Barkin Ladi LGAs; Kaduna State, Jema'a LGA'."),
    ("gbagyi", True, ["kachia"], "Atlas: Gbagyi also in 'Kaduna State, Kachia LGA'."),
    ("gbari", True, ["kachia"], "Atlas: Gbari also in 'Kaduna State, Kachia LGA'."),
    ("gwandara", True, ["kachia"], "Atlas: Gwandara also in 'Kaduna State, Kachia LGA'."),
    ("idun", True, ["jema-a", "jaba"], "Atlas: 'Kaduna State, Jema'a, Jaba LGAs; Nasarawa State, Karu LGA'; twenty-one villages (2008)."),
    ("kadara", False, ["kachia", "kajuru"], "Atlas (Eda, Edra and Enezhe, the Adara varieties): 'Kaduna State, Kachia, Kajuru LGAs'; towns Maru, Kufana, Rimau, Kasuwan Magani and Iri."),
    ("mada", True, ["jema-a"], "Atlas: 'Nasarawa State, Akwanga, Kokona and Keffi LGAs; Kaduna State, Jema'a LGA'."),
    ("ninzo", True, ["jema-a"], "Atlas: 'Kaduna State, Jema'a LGA; Nasarawa State, Akwanga LGA'."),
    ("numbu-gbantu-nunku", True, ["jema-a"], "Atlas: 'Kaduna State, Jema'a LGA; Nasarawa State, Akwanga LGA'."),
]
for lang, state, lgas, note in EXIST:
    if state:
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=ST("kaduna"), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
    for l in lgas:
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=KD(l), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
GAPS = [
    ("Kaduna: pre-1989 LGAs", "The Atlas uses Jema'a before Kaura and Sanga were created, Kachia before Zangon Kataf and Chikun before Kajuru. Links to today's Jema'a and Kachia follow the Atlas; links to Kaura, Sanga, Zangon Kataf, Jaba, Kagarko and Kajuru that rest on Wikipedia or INEC place names are marked reported."),
    ("Kaduna: Ajuwa–Ajegha", "Neither the Atlas nor Glottolog classifies Ajuwa–Ajegha; it has no family in the archive."),
    ("Kaduna: Ikryo and Obiro", "The Atlas calls both West Kuturmi; Glottolog has one 'Kuturmi' languoid. No code is set for either."),
    ("Kaduna: Izere and Bacama", "The Atlas places Izere speakers in Jema'a LGA as 'probably migrants only', and Bacama fishing camps north-east of Kaduna town; neither is linked to Kaduna."),
    ("Kaduna: moribund languages", "The Atlas records Sambe with 2 speakers in 2005 and probably extinct by 2016, and Nincut as threatened; their current status needs a recent source."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kaduna Plateau and other languages: 27 new language records from Blench's Atlas, with Kaduna links for 11 existing records.")


def report():
    lga = sorted({r["to"].split("/")[-1] for r in RELATIONS if "lga:kaduna" in r["to"]})
    rep = [r for r in RELATIONS if r["level"] == "reported"]
    L_ = ["# Research batch 166b — Kaduna: Plateau and other languages", "",
          f"Researched {ACCESSED}. Second of three Kaduna language and people batches. Created in review; published only after your approval.", "",
          "## What it adds", "",
          f"- **{len(LANG)} new languages** of southern Kaduna: {', '.join(v[0] for v in LANG.values())}.",
          f"- **Glottolog codes** for {sum(1 for v in LANG.values() if v[9])}; none for Ajuwa–Ajegha, Gbǝtsu, Ikryo and Obiro.",
          "- **Existing records linked to Kaduna:** Ashe (Kagarko), Aten, Berom, Mada, Ninzo and Numbu–Gbantu–Nunku (Jema'a), Gbagyi, Gbari and Gwandara (Kachia), Idun (Jema'a, Jaba), Kadara (Kachia, Kajuru).",
          f"- **LGAs linked:** {', '.join(lga)}.",
          f"- **{len(rep)} reported links**, placed by Wikipedia or INEC place names in the LGAs created after the Atlas's survey (the method you accepted for batch 166).", "",
          "## The records", ""]
    for k in LANG:
        L_ += [f"**{LANG[k][0]}.** {TEXT[k]}", ""]
    L_ += ["## Reported links", ""] + [f"- {r['frm']} → {r['to'].split('/')[-1]}: {r['notes']}" for r in rep] + [""]
    L_ += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_166b_kaduna_plateau_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_166b_kaduna_plateau_languages_REVIEW.md", "w").write(report())
    lga = {r["to"] for r in RELATIONS if "lga:kaduna" in r["to"]}
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} kaduna-LGAs={len(lga)}")
