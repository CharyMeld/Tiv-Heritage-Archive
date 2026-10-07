"""
Research batch 166c — Kaduna (Phase 3): the peoples of Kaduna State and the LGAs where they live, as far as the sources
allow. Researched 2026-10-07. Pattern: batch 160b (Niger peoples).

Sources:
  * Federal Government of Nigeria, state profile 'Kaduna' (data/fg_kaduna_2026-10-07.html) — Tier 1: 'mostly populated
    by Hausa, Gbagyi, Adara, Ham, Gong, Atyap, Bajjuu, Ninkyob, Kurama, Koro, zango kataf, mada and Agworok ethnic
    communities. Ikulu people Moroa'a, Atuku.'
  * Wikipedia, 'Kaduna State': a numbered list of 61 ethnic groups, mostly in the form 'own name (dubbed X by the Hausa)'.
  * Wikipedia, 'Southern Kaduna': the groups arranged by language family (after James 2000 and Blench 2008).
  * Wikipedia LGA pages, for the peoples of each LGA: Jema'a, Kaura, Zangon Kataf, Kachia, Kajuru, Kauru, Lere, Sanga,
    Jaba, Kagarko, Chikun, Kaduna South, Igabi, Ikara, Kubau, Makarfi, Zaria.
  * Blench's Atlas (batches 166 and 166b) for the language each people speaks.
Rules (as 160b): a people record only for a group named by the federal profile or Wikipedia whose language is recorded;
record names follow the own name Wikipedia gives, with the Hausa name kept as an exonym (owner's keep-both-names rule).
LGA links come from the Wikipedia LGA pages: well documented where the Atlas (batches 166/166b) also places the
people's language in that LGA, reported otherwise; presence only. Where no LGA page names a people, the LGA where the
Atlas places its language is linked as reported.
Not created: Atuku (federal profile; no language identified); Nandu, Ningon, Tari, Ninte and Nungu (Sanga) and Azelle
(Lere), whose languages are not yet recorded for Kaduna; the Kanuri, Marghi and Zaar of Wikipedia's list are migrants
or belong mainly to other states (Zaar is linked to Lere, where Wikipedia names them).
"""
import json, sys

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=f"{n} Accessed {ACCESSED}.")
SOURCES = {
    "FGKD": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Kaduna State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/kaduna/", verification_status="verified",
                 notes="'Ethnic Profile': 'Kaduna State is mostly populated by Hausa, Gbagyi, Adara, Ham, Gong, Atyap, Bajjuu, Ninkyob, Kurama, Koro, zango kataf, mada and Agworok ethnic communities. Ikulu people Moroa'a, Atuku.' Capital Kaduna; created 8 August 1972 (as given). Read 2026-10-07 (copy in database/research/data/)."),
    "WKDS": WS("Kaduna State", "'Ethnic groups' section: 59 to 63 groups; a list of 61, e.g. 'Atyap (dubbed Kataf by the Hausa)', 'Bajju (dubbed Kaje)', 'Ham (dubbed Jaba in Hausa, which is a derogatory name)', 'Gwong (Kagoma in Hausa)', 'Bhazar (named Koro)'."),
    "WSKD": WS("Southern Kaduna", "'Ethnic composition': groups arranged after James (2000) and Blench (2008): the Atyap (Nerzit) group (Bajju, Atyap, Agworok, Takad, Atyecarak, Asholyio, Fantswam, Atuku); Ham, Gwong, Ninzo and Koro groups; Eastern Kainji groups (Atsam, Amap, Abisi, Agbiri, Aniragu, Akurmi, Koonu, Vono, Tumi, Kaivi, Mala-Ruma, Abin, Kuvori, Atumu, Shuwa-Zamani, Dungi)."),
    "WJEM": WS("Jema'a", "'The ethnic groups and subgroups in Jema'a LGA include: hausa …, Numana …, Fulani, Gwong, Nikyob, Nindem and Nyankpa. Others are: Atyap, Bajju, Berom, Gwong, Atuku, Ham, Igbo and Yoruba.'"),
    "WKAU": WS("Kaura, Nigeria", "The people of Kaura 'belong to the larger Atyap (Nienzit) Ethno-Linguistic Cluster', including the Asholyia ('Moro'a' in Hausa), Agworok ('Kagoro'), Takad, the Atyecarak ('Kachechere') and Atyap proper ('Kataf')."),
    "WZK": WS("Zangon Kataf", "'The people predominantly belong to the Atyap (Nenzit) Ethno-Linguistic group … the Bajju, Atyap proper, Bakulu, Anghan and Atyecarak'; also Hausa settler elements."),
    "WKAC": WS("Kachia", "'Most of the people of Kachia include the Adara, Tinor-Myamya, Gbagyi, Ham. Others include the Bajju, Bakulu, and the Hausa.'"),
    "WKAJ": WS("Kajuru", "'The major ethnic group is the Adara … otherwise known as Kadara by the Hausa. Others include the Gbagyi and settler elements such as the Hausa, Fulani, Yoruba, Ikulu, and Igbo.'"),
    "WKAR": WS("Kauru", "Ethnic groups 'such as: Abin, Abishi, Akurmi, Amala, Anu, Atsam, Avori, Irigwe, Anunu, Koonu, Ngmgbang, Atumi [Adungi Dingi dutse]. Others are: Atyap, Hausa, Igbo.'"),
    "WLER": WS("Lere, Nigeria", "Groups 'largely speaking languages belonging to the East Kainji languages group … Agbiri, Akurmi, Amala, Amap, Anaseni, Aniragu, Arumaruma, Avono, Avori, Azelle, Dungu, Koonu, Kuzamani (Lere), and Tumi. Others include Fulani (in Lere town), Hausa, Igbo, and Zaar.'"),
    "WSAN": WS("Sanga, Nigeria", "'The people of Sanga Local Government Area include the Nandu, Ningon, Tari, Ayu, Ninzam, Numana, Ninte, Mada (Mœda), Nungu and others.'"),
    "WJAB": WS("Jaba, Nigeria", "Named after 'Jaba', a Hausa word for the Ham, 'who occupy most of the local government'; 'inhabited predominantly by Ham people'."),
    "WKAG": WS("Kagarko", "'The Batinor (Koro) people is the dominant group in the area. Others are Gbagyi, Ham, Hausa and Adara.'"),
    "WCHI": WS("Chikun", "'The area was originally populated by the Gbagyi people'; named after a Gbagyi village."),
    "WKDSO": WS("Kaduna South", "'A mixed population, comprising … Adara, Atyap, Bajju, Gbagyi, Ham, Hausa, Idoma, Igala, Igbo, Nupe and Yoruba.'"),
    "WIGA": WS("Igabi", "'The indigenous people of Igabi are predominantly Muslims with the exception of Gbagyi …'."),
    "WIKA": WS("Ikara", "'The main tribes of the people of the area are Hausa and Fulani.'"),
    "WKUB": WS("Kubau", "'The major tribes are predominantly Hausa and Fulani.'"),
    "WMAK": WS("Makarfi", "'Makarfi LGA's indigenous communities members are Hausa and Fulani people.'"),
    "WZAR": WS("Zaria", "Capital of the Zazzau Emirate Council and 'one of the original seven Hausa city-states'."),
}
ATL = "batch 166/166b (Blench's Atlas)"
ST = "@admin_units:state:kaduna"
KD = lambda l: f"@admin_units:lga:kaduna/{l}"
PO = " Presence only; the nature of their presence is not established."
LGA_NAME = {"jema-a": "Jema'a", "zangon-kataf": "Zangon Kataf", "kaduna-south": "Kaduna South", "birnin-gwari": "Birnin Gwari"}
lname = lambda l: LGA_NAME.get(l, l.replace("-", " ").title())
# Atlas LGA links of each language (from batches 166 and 166b): lang slug -> {lga: level}
LANG_LGA = {}
for f in ("batch_166_kaduna_kainji_languages.json", "batch_166b_kaduna_plateau_languages.json"):
    try:
        d = json.load(open(f"{sys.argv[1] if len(sys.argv) > 1 else '.'}/{f}"))
    except FileNotFoundError:
        d = json.load(open(f))
    for r in d["relations"]:
        if "lga:kaduna/" in r["to"]:
            lang = r["from"].replace("@languages:", "")
            lang = {"kulu": "ikulu"}.get(lang, lang)
            LANG_LGA.setdefault(lang, {})[r["to"].split("/")[-1]] = r["level"]
# key: (name, area, language slug, language description, [(other name, type, src)], [(lga, src)], named_by)
P = {
 "atyap": ("Atyap", "southern", "tyap", "the Tyap cluster of Central Plateau languages", [("Kataf", "exonym", "WKDS"), ("Katab", "exonym", "WSKD")],
           [("zangon-kataf", "WZK"), ("kaura", "WKAU"), ("jema-a", "WJEM"), ("kauru", "WKAR"), ("kaduna-south", "WKDSO")], ["FGKD", "WKDS", "WSKD"]),
 "bajju": ("Bajju", "southern", "jju", "Jju, a Central Plateau language", [("Kaje", "exonym", "WKDS"), ("Bajjuu", "spelling_variant", "FGKD")],
           [("zangon-kataf", "WZK"), ("jema-a", "WJEM"), ("kachia", "WKAC"), ("kaduna-south", "WKDSO")], ["FGKD", "WKDS", "WSKD"]),
 "agworok": ("Agworok", "southern", "tyap", "Gworok, a member of the Tyap cluster", [("Kagoro", "exonym", "WKDS"), ("Oegworok", "spelling_variant", "WKDS")],
             [("kaura", "WKAU")], ["FGKD", "WKDS", "WSKD"]),
 "asholio": ("Asholio", "southern", "tyap", "Sholio, a member of the Tyap cluster", [("Moroa", "exonym", "FGKD"), ("Asholyio", "spelling_variant", "WSKD"), ("Osholio", "spelling_variant", "WSKD")],
             [("kaura", "WKAU")], ["FGKD", "WKDS", "WSKD"]),
 "takad": ("Takad", "southern", "tyap", "Atakar, a member of the Tyap cluster", [("Attakar", "exonym", "WKDS")], [("kaura", "WKAU")], ["WKDS", "WSKD"]),
 "atyecarak": ("Atyecarak", "southern", "tyap", "Kacicere, a member of the Tyap cluster", [("Kachechere", "exonym", "WKDS"), ("Atachaat", "spelling_variant", "WKDS")],
               [("kaura", "WKAU"), ("zangon-kataf", "WZK")], ["WKDS", "WSKD"]),
 "fantswam": ("Fantswam", "southern", "tyap", "Kafancan, a member of the Tyap cluster", [("Kafanchan", "exonym", "WKDS")], [], ["WKDS", "WSKD"]),
 "ham": ("Ham", "southern", "hyam", "the Hyam cluster of Hyamic Plateau languages", [("Jaba", "exonym", "WKDS")],
         [("jaba", "WJAB"), ("kachia", "WKAC"), ("kagarko", "WKAG"), ("jema-a", "WJEM"), ("kaduna-south", "WKDSO")], ["FGKD", "WKDS", "WSKD"]),
 "gwong": ("Gwong", "southern", "gyong", "Gyong, a Western Plateau (Gyongic) language", [("Kagoma", "exonym", "WKDS"), ("Gong", "spelling_variant", "FGKD")], [("jema-a", "WJEM")], ["FGKD", "WKDS", "WSKD"]),
 "anghan": ("Anghan", "southern", "kamantan", "Kamantan, a Western Plateau (Gyongic) language", [("Kamantan", "exonym", "WKDS")], [("zangon-kataf", "WZK")], ["WKDS", "WSKD"]),
 "bakulu": ("Bakulu", "southern", "ikulu", "Ikulu, a Northwestern Plateau language", [("Ikulu", "exonym", "FGKD")], [("zangon-kataf", "WZK"), ("kachia", "WKAC")], ["FGKD", "WKDS", "WSKD"]),
 "nikyob": ("Nikyob", "southern", "ninkyop-nindem", "the Ninkyop–Nindem cluster of Ninzic Plateau languages", [("Kaninkon", "exonym", "WSKD"), ("Ninkyob", "spelling_variant", "FGKD")], [("jema-a", "WJEM")], ["FGKD", "WKDS", "WSKD"]),
 "nindem": ("Nindem", "southern", "ninkyop-nindem", "the Ninkyop–Nindem cluster of Ninzic Plateau languages", [], [("jema-a", "WJEM")], ["WKDS", "WSKD"]),
 "ayu": ("Ayu", "southern", "ayu", "Ayu, a Ninzic Plateau language", [], [("sanga", "WSAN")], ["WKDS", "WSKD"]),
 "numana": ("Numana", "southern", "numbu-gbantu-nunku", "the Numbu–Gbantu–Nunku cluster of Ninzic Plateau languages", [], [("jema-a", "WJEM"), ("sanga", "WSAN")], ["WKDS", "WSKD"]),
 "kanufi": ("Kanufi", "southern", "anib", "Anib, a Ninzic Plateau language", [], [], ["WKDS", "WSKD"]),
 "ningeshe": ("Ningeshe", "southern", "ningye", "Ningye, a Ninzic Plateau language", [], [], ["WKDS", "WSKD"]),
 "idun-people": ("Idun", "southern", "idun", "Idun, a Koro Plateau language", [("Nduyah", "alternative", "WKDS"), ("Jaba Lungu", "exonym", "WSKD")], [], ["WKDS", "WSKD"]),
 "atsam": ("Atsam", "northern", "atsam", "Atsam, an Eastern Kainji language", [("Chawai", "exonym", "WKDS")], [("kauru", "WKAR")], ["WKDS", "WSKD"]),
 "abin": ("Abin", "northern", "bin", "Bin, an Eastern Kainji language", [("Binawa", "exonym", "WKDS"), ("Abinu", "spelling_variant", "WKDS")], [("kauru", "WKAR")], ["WKDS", "WSKD"]),
 "abisi": ("Abisi", "northern", "bishi", "Bishi, an Eastern Kainji language", [("Pitti", "exonym", "WKDS"), ("Piti", "spelling_variant", "WSKD"), ("Abishi", "spelling_variant", "WKAR")], [("kauru", "WKAR")], ["WKDS", "WSKD"]),
 "akurmi": ("Akurmi", "northern", "kurama", "Kurama, an Eastern Kainji language", [("Kurama", "exonym", "FGKD")], [("lere", "WLER"), ("kauru", "WKAR")], ["FGKD", "WKDS", "WSKD"]),
 "amala": ("Amala", "northern", "mala", "Mala, an Eastern Kainji language", [("Rumaya", "exonym", "WKDS")], [("lere", "WLER"), ("kauru", "WKAR")], ["WKDS", "WSKD"]),
 "anu": ("Anu", "northern", "nu", "Nu, an Eastern Kainji language", [("Kinugu", "exonym", "WKDS"), ("Atumu", "alternative", "WSKD")], [("kauru", "WKAR")], ["WKDS", "WSKD"]),
 "avori": ("Avori", "northern", "vori", "Vori, an Eastern Kainji language", [("Surubu", "exonym", "WKDS"), ("Kuvori", "spelling_variant", "WSKD")], [("lere", "WLER"), ("kauru", "WKAR")], ["WKDS", "WSKD"]),
 "koonu": ("Koonu", "northern", "kono", "Kono, an Eastern Kainji language", [("Kono", "exonym", "WSKD"), ("Kigono", "spelling_variant", "WKDS")], [("lere", "WLER"), ("kauru", "WKAR")], ["WKDS", "WSKD"]),
 "ngmgbang-people": ("Ngmgbang", "northern", "ngmgbang", "Ngmgbang, an Eastern Kainji language", [("Ribang", "alternative", "WKDS"), ("Ribam", "alternative", "WSKD")], [("kauru", "WKAR")], ["WKDS", "WSKD"]),
 "dungi": ("Dungi", "northern", "dungu", "Dungu, an Eastern Kainji language", [("Dingi", "spelling_variant", "WKDS"), ("Dungu", "spelling_variant", "WLER")], [("lere", "WLER"), ("kauru", "WKAR")], ["WKDS", "WSKD"]),
 "agbiri": ("Agbiri", "northern", "gbiri-niragu", "Gbiri, of the Gbiri–Niragu cluster of Eastern Kainji languages", [("Gure", "exonym", "WKDS")], [("lere", "WLER")], ["WKDS", "WSKD"]),
 "aniragu": ("Aniragu", "northern", "gbiri-niragu", "Niragu, of the Gbiri–Niragu cluster of Eastern Kainji languages", [("Kahugu", "exonym", "WKDS")], [("lere", "WLER")], ["WKDS", "WSKD"]),
 "arumaruma": ("Arumaruma", "northern", "ruma", "Ruma, an Eastern Kainji language", [("Ruruma", "exonym", "WKDS"), ("Aruruma", "spelling_variant", "WKDS")], [("lere", "WLER")], ["WKDS", "WSKD"]),
 "avono": ("Avono", "northern", "vono", "Vono, an Eastern Kainji language", [("Kiballo", "exonym", "WSKD"), ("Kiwollo", "exonym", "WKDS")], [("lere", "WLER")], ["WKDS", "WSKD"]),
 "anaseni": ("Anaseni", "northern", "sheni", "Sheni, an Eastern Kainji language", [], [("lere", "WLER")], ["WLER"]),
 "tumi-people": ("Tumi", "northern", "tumi", "Tumi, an Eastern Kainji language", [("Kitimi", "exonym", "WKDS")], [("lere", "WLER")], ["WKDS", "WSKD"]),
 "kuzamani": ("Kuzamani", "northern", "shuwa-zamani", "Shuwa–Zamani, an Eastern Kainji language", [("Shuwa-Zamani", "alternative", "WSKD")], [("lere", "WLER")], ["WSKD", "WLER"]),
 "kaivi-people": ("Kaivi", "northern", "kaivi", "Kaivi, an Eastern Kainji language", [("Kaibi", "exonym", "WKDS")], [], ["WKDS", "WSKD"]),
}
SRCNAME = {"FGKD": "the federal government's state profile", "WKDS": "Wikipedia's list of the ethnic groups of Kaduna State", "WSKD": "Wikipedia's 'Southern Kaduna'", "WLER": "Wikipedia's page on Lere LGA"}
TEXT = {}
for k, (name, area, lang, ldesc, others, lgas, named) in P.items():
    exo = [n for n, t, s in others if t == "exonym"]
    t = f"The {name}" + (f", called {' or '.join(exo[:2])} by the Hausa," if exo else "") + f" are a people of {area} Kaduna State, named among its peoples by {' and '.join(SRCNAME[s] for s in named[:2])}."
    t += f" They speak {ldesc} (Roger Blench's Atlas of Nigerian Languages, 2020)."
    if lgas:
        t += f" Wikipedia names them among the peoples of {', '.join(lname(l) for l, _ in lgas[:-1]) + (' and ' if len(lgas) > 1 else '') + lname(lgas[-1][0])} LGA{'s' if len(lgas) > 1 else ''}."
    elif LANG_LGA.get(lang):
        t += f" The Atlas places their language in {', '.join(lname(l) for l in LANG_LGA[lang])} LGA{'s' if len(LANG_LGA[lang]) > 1 else ''}."
    TEXT[k] = t
TEXT["ham"] += " Wikipedia calls the Hausa name Jaba derogatory, and says that the Ham of Jaba and the Hausa of Zaria are held to be the old peoples of the south and north of the region."
TEXT["atyap"] += " Wikipedia describes the Atyap, Bajju, Agworok, Asholio, Takad, Atyecarak and Fantswam as one ethno-linguistic group (Nerzit or Nenzit); Zangon Kataf is a town in the chiefdom of the Atyap."

RECORDS, NAMES, RELATIONS = [], [], []
for k, (name, area, lang, ldesc, others, lgas, named) in P.items():
    srcs = [(s, f"{name} named among the peoples of Kaduna State") for s in named] + [(s, f"{name} in {lname(l)} LGA") for l, s in lgas if s not in named]
    srcs = list(dict.fromkeys(srcs))
    RECORDS.append(dict(key=k, table="ethnic_groups", evidence="multiple_sources", level="well_documented" if len(named) > 1 else "reported",
                        fields=dict(name=name, slug=k, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k]), srcs=srcs))
    for n, t, s in others:
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=f"{'The Hausa name' if t == 'exonym' else 'Another form of the name'}, as given by {SRCNAME.get(s, 'Wikipedia (' + SOURCES[s]['title'] + ')')}.", srcs=[s]))
    RELATIONS.append(dict(frm=k, type="speaks", to=f"@languages:{lang}", source="WSKD" if "WSKD" in named else named[0], evidence="multiple_sources", level="well_documented",
                          notes=f"Wikipedia ('Southern Kaduna') and {ATL}."))
    RELATIONS.append(dict(frm=k, type="present_in", to=ST, source=named[0], evidence="multiple_sources" if len(named) > 1 else "single_reliable_source", level="well_documented",
                          notes=f"Named among the peoples of Kaduna State ({'; '.join(SRCNAME[s] for s in named)})."))
    done = set()
    for l, s in lgas:
        lvl = "well_documented" if LANG_LGA.get(lang, {}).get(l) == "well_documented" else "reported"
        RELATIONS.append(dict(frm=k, type="present_in", to=KD(l), source=s, evidence="multiple_sources" if lvl == "well_documented" else "single_reliable_source", level=lvl,
                              settlement_status="unknown", notes=f"Wikipedia ({SOURCES[s]['title']}) names them among the peoples of the LGA" + ("; the Atlas places their language there." if lvl == "well_documented" else ".") + PO))
        done.add(l)
    if not lgas:
        for l in LANG_LGA.get(lang, {}):
            RELATIONS.append(dict(frm=k, type="present_in", to=KD(l), source="WKDS", evidence="single_reliable_source", level="reported", settlement_status="unknown",
                                  notes=f"No LGA page read names them; the Atlas places their language in {lname(l)} LGA." + PO))
# Existing peoples: (slug, state note or None, [(lga, src, level)], [(other name, type, src)])
EXIST = [
    ("hausa", "Federal profile: 'mostly populated by Hausa, …'; Wikipedia (Kaduna State): 'the Hausa and Fulani as the dominant ethnic groups'.", [("zaria", "WZAR", "well_documented"), ("ikara", "WIKA", "reported"), ("kubau", "WKUB", "reported"), ("makarfi", "WMAK", "reported"), ("jema-a", "WJEM", "reported"),
                     ("lere", "WLER", "reported"), ("kachia", "WKAC", "reported"), ("kagarko", "WKAG", "reported"), ("kaduna-south", "WKDSO", "reported"), ("kauru", "WKAR", "reported"),
                     ("zangon-kataf", "WZK", "reported")], []),
    ("fulani", "Wikipedia (Kaduna State): 'the Hausa and Fulani as the dominant ethnic groups'.", [("ikara", "WIKA", "reported"), ("kubau", "WKUB", "reported"), ("makarfi", "WMAK", "reported"), ("jema-a", "WJEM", "reported"), ("lere", "WLER", "reported")], []),
    ("gbagyi", "Federal profile: 'Gbagyi' among the communities of Kaduna State; Wikipedia: 'Gbagyi-Gbari (Gwari in Hausa)'.", [("chikun", "WCHI", "reported"), ("igabi", "WIGA", "reported"), ("kachia", "WKAC", "well_documented"), ("kajuru", "WKAJ", "reported"), ("kagarko", "WKAG", "reported"), ("kaduna-south", "WKDSO", "reported")], []),
    ("adara", "Federal profile: 'Adara' among the communities of Kaduna State; Wikipedia: 'Adara (dubbed Kadara)'.", [("kachia", "WKAC", "well_documented"), ("kajuru", "WKAJ", "well_documented"), ("kagarko", "WKAG", "reported"), ("kaduna-south", "WKDSO", "reported")], []),
    ("koro", "Federal profile: 'Koro' among the communities of Kaduna State; Wikipedia (Kaduna State): 'Bhazar (named Koro)'.", [("kagarko", "WKAG", "well_documented"), ("kachia", "WKAC", "reported")],
     [("Bhazar", "endonym", "WKDS"), ("Batinor", "alternative", "WKAG")]),
    ("mada", "Federal profile: 'mada' among the communities of Kaduna State.", [("sanga", "WSAN", "reported")], []),
    ("amo", "Wikipedia (Kaduna State): 'Amap (dubbed Amo by the Hausa)'.", [("lere", "WLER", "reported")], []),
    ("ninzam", "Wikipedia (Kaduna State): 'Ninzo' among the ethnic groups of Kaduna State.", [("sanga", "WSAN", "reported")], [("Ninzo", "alternative", "WKDS")]),
    ("nyankpa", "Wikipedia (Kaduna State): 'Nyenkpa (Yeskwa)' among the ethnic groups of Kaduna State.", [("jema-a", "WJEM", "well_documented")], [("Nyenkpa", "spelling_variant", "WKDS"), ("Yeskwa", "exonym", "WKDS")]),
    ("berom", None, [("jema-a", "WJEM", "well_documented")], []),
    ("irigwe", None, [("kauru", "WKAR", "well_documented")], []),
    ("zaar", None, [("lere", "WLER", "reported")], []),
]
STATE_SRC = {"ninzam": "WKDS", "nyankpa": "WKDS", "fulani": "WKDS", "koro": "FGKD", "mada": "FGKD", "amo": "WKDS", "hausa": "FGKD", "gbagyi": "FGKD", "adara": "FGKD"}
for slug, snote, links, names in EXIST:
    ref = f"@ethnic_groups:{slug}"
    if snote:
        RELATIONS.append(dict(frm=ref, type="present_in", to=ST, source=STATE_SRC[slug], evidence="multiple_sources", level="well_documented", notes=snote))
    for l, s, lvl in links:
        RELATIONS.append(dict(frm=ref, type="present_in", to=KD(l), source=s, evidence="multiple_sources" if lvl == "well_documented" else "single_reliable_source", level=lvl,
                              settlement_status="unknown", notes=f"Wikipedia ({SOURCES[s]['title']}) names them among the peoples of the LGA" + ("; the Atlas places their language there." if lvl == "well_documented" else ".") + PO))
    for n, t, s in names:
        NAMES.append(dict(record=ref, name=n, name_type=t, usage_notes=f"As given by Wikipedia ({SOURCES[s]['title']}).", srcs=[s]))
RELATIONS.append(dict(frm="@ethnic_groups:nyankpa", type="speaks", to="@languages:nyankpa", source="WSKD", evidence="multiple_sources", level="well_documented",
                      notes="Wikipedia ('Southern Kaduna': Nyenkpa) and batch 166b (Blench's Atlas: Nyankpa, also Yeskwa)."))
GAPS = [
    ("Kaduna: Atuku", "The federal profile and Wikipedia name the Atuku (in Jema'a and the Atyap group); their language is not identified in a source read, so no record is made."),
    ("Kaduna: peoples of Sanga and Lere not yet recorded", "Wikipedia names the Nandu, Ningon, Tari, Ninte and Nungu of Sanga and the Azelle of Lere; their languages are not yet recorded for Kaduna, so no records are made."),
    ("Kaduna: northern LGAs", "No source read names the peoples of Birnin Gwari, Giwa, Kudan, Sabon Gari, Soba or Kaduna North; they are left for the LGA profiles."),
    ("Kaduna: migrant and other-state groups", "Wikipedia's list includes the Kanuri and Marghi (from Borno) and the Zaar (Bauchi); they are not linked at state level. Settler groups named on LGA pages (Igbo, Yoruba, Idoma, Igala, Nupe) are not linked."),
    ("Kaduna: Koro", "The Koro of Kagarko and Kachia (Bhazar, Batinor; Ashe and Tinɔr–Myamya speakers) are linked to the existing Koro record, which also covers the Koro of Niger State and the FCT; whether they should be separate records is for review."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Kaduna peoples: {len(P)} new records (the Atyap group, Ham, Gwong, Bakulu, the Ninzic and Eastern Kainji peoples and others); Hausa, Fulani, Gbagyi, Adara, Koro, Mada, Amo, Ninzam, Nyankpa, Berom, Irigwe and Zaar linked by LGA or state.")


def report():
    lga = sorted({r["to"].split("/")[-1] for r in RELATIONS if "lga:kaduna" in r["to"] and r["type"] == "present_in"})
    L_ = ["# Research batch 166c — Kaduna: peoples", "",
          f"Researched {ACCESSED}. Third of three Kaduna language and people batches. Created in review; published only after your approval.", "",
          "## What it adds", "",
          f"- **{len(P)} new peoples:** {', '.join(v[0] for v in P.values())}.",
          "- **Names:** each record uses the own name Wikipedia gives; the Hausa name is kept as an exonym (e.g. Atyap/Kataf, Bajju/Kaje, Ham/Jaba, Gwong/Kagoma, Bakulu/Ikulu, Akurmi/Kurama).",
          "- **Existing peoples linked:** Hausa, Fulani, Gbagyi, Adara, Koro (with the names Bhazar and Batinor), Mada, Amo, Ninzam (with the name Ninzo), Nyankpa (with Nyenkpa and Yeskwa, and now linked to its language), Berom, Irigwe, Zaar.",
          f"- **LGAs with peoples linked:** {len(lga)} of 23 ({', '.join(lga)}).",
          "- **LGA links** come from Wikipedia's LGA pages; *well documented* where the Atlas also places the language there, *reported* otherwise.", "",
          "## The records", ""]
    for k in P:
        L_ += [f"**{P[k][0]}.** {TEXT[k]}", ""]
    L_ += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_166c_kaduna_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_166c_kaduna_peoples_REVIEW.md", "w").write(report())
    lga = {r["to"] for r in RELATIONS if "lga:kaduna" in r["to"] and r["type"] == "present_in"}
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} kaduna-LGAs-with-peoples={len(lga)}")
