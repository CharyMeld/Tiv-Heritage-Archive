"""
Research batch 154 — Kwara (Phase 3, first batch): languages and peoples. Researched 2026-10-07.
Pattern: batches 111 (Lagos, new people records) and 142 (Ondo, new languages and branches).

Languages: the column-split Atlas text was searched for 'Kwara' and the Kwara LGA and town names. Entries:
  * Baatọnum No. 33 (Bariba) — 'Kwara State; mainly in Benin Republic'; Gur: South-Central: Isolate; 62,634 in Nigeria
    (1963). Glottolog baat1238 (Gur > Central Gur > Southern Central Gur).
  * Bokobaru No. 55 — 'Kwara State. Kaiama town and surrounding villages'; Mande: Southeast: Busa cluster; 30–40,000
    (2004). Glottolog boko1267 (Mande > Eastern Mande > … > Boko-Busa).
  * Busa No. 65 — 'Kwara State; Niger State, Borgu LGA; Kebbi State, Bagudo LGA; also in Benin Republic'; Southeast
    Mande. Glottolog busa1253.
  * Nupe No. 355 — 'Kwara State, Edu and Kogi LGAs' (Kogi LGA now in Kogi State); Nupe already linked to Kwara (2394).
  * Yoruba No. 489 — 'Most of Kwara …'; already linked (2404).
  * Sorko No. 425 [†] — 'Niger, Kwara & Kebbi States; fishermen on Lake Kainji'; most now speak only Hausa. No Glottolog
    entry found; not recorded (gap).
  * Kambari II No. 236 — 'Kwara State, Borgu LGA': Borgu LGA moved to Niger State in 1991; not linked to Kwara.
  The Atlas's 'Kwara State, Oyi/Okene/Kogi LGA' entries are now Kogi State (handled in batch 051).
Placement by LGA uses Wikipedia: 'Baruten' (main language Baruba), 'Busa language (Mande)' (Busa in Baruten LGA;
Bokobaru mainly in Kaiama and Baruten LGAs), 'Kaiama, Kwara State' (Bokobaru the major language except Adena and Bani
wards), 'Edu, Nigeria' (a Nupe-speaking area), 'Pategi' (Nupe; Pategi Emirate).
Peoples:
  * Federal Government of Nigeria, state profile 'Kwara' (data/fg_kwara_2026-10-07.html): 'The principal groups residing
    in Kwara State are the Yoruba, Nupe, Bariba and Fulani.'
  * Wikipedia, 'Kwara State': the majority Yoruba throughout; 'sizable minorities of Nupe people in the northeast, Bariba
    (Baatonu) and Busa (Bokobaru) peoples in the west, and Fulani people in Ilorin'.
  * Yoruba sub-groups by LGA (Wikipedia): Igbomina — Irepodun ('populated by the Igbomina people'), Ifelodun ('mostly of
    Igbomina origin'), Isin ('stock of the Igbomina'); Ibolo — Offa and Oyun ('the Ibolo sub-group of the cities of Offa,
    Oyun and Okuku', Igbomina page); Ekiti — Oke Ero ('predominantly indigenous Ekiti natives').
New records: the Gur and Mande branches; Baatọnum, Busa and Bokobaru; the Bariba and Busa peoples.
"""
import json, sys
import batch_087_kano_languages as L87
import batch_063_borno_languages as B63
import batch_148_ekiti_languages_peoples as P148

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=f"{n} Accessed {ACCESSED}.")
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Kwara entries read: Baatọnum (No. 33), Bokobaru (No. 55), Busa (No. 65), Kambari II (No. 236), Nupe (No. 355), Sorko (No. 425), Yoruba (No. 489)."),
    "GLIDX": L87.SOURCES["GLIDX"],
    "FGKW": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Kwara State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/kwara/", verification_status="verified",
                 notes="'Ethnic Profile': 'The principal groups residing in Kwara State are the Yoruba, Nupe, Bariba and Fulani. Major languages are English (official) and Yoruba.' Capital Ilorin; created 8 August 1972. Read 2026-10-07 (copy in database/research/data/)."),
    "WKWS": WS("Kwara State", "Majority Yoruba throughout; 'sizable minorities of Nupe people in the northeast, Bariba (Baatonu) and Busa (Bokobaru) peoples in the west, and Fulani people in Ilorin and moving through the state as nomadic herders'; other languages Busa, Fula, Boko and Sorko."),
    "WBAR": WS("Bariba people", "Self-designation Baatonu (plural Baatombu); co-founders of the Borgu kingdoms; in Nigeria 'domiciled in the Baruten local government area of western Kwara State'; perhaps a million, 70% in Benin; Nikki the traditional capital."),
    "WBRT": WS("Baruten", "LGA bordering Benin Republic; headquarters Kosubosu; 'The main language of Baruten is Baruba'."),
    "WBUSA": WS("Busa language (Mande)", "Busa (Bisã), the Mande language of the former Borgu Emirate; in Nigeria spoken in Borgu LGA (Niger), Bagudo LGA (Kebbi) and Baruten LGA (Kwara); the Busa (Bussawa) one of two sub-groups of the Bissa, with the Boko; 'The Bokobaru dialect … is spoken mainly in Kayama and Baruten LGA's, Kwara state'."),
    "WKAI": WS("Kaiama, Kwara State", "LGA and town; 'Bokobaru is the major language spoken across the local government area'; except Adena and Bani wards; Fulani in Bani ward; Yoruba and Hausa in Adena ward; 'The people of the Bokobaru speaking towns and villages are called Bokobaru'."),
    "WEDU": WS("Edu, Nigeria", "LGA, headquarters Lafiagi; 'A Nupe speaking area'."),
    "WPAT": WS("Pategi", "Town and LGA; 'inhabited predominantly by the Nupe people who speak the Nupe language'; headquarters of Pategi Emirate."),
    "WIGBO": WS("Igbomina", "Yoruba sub-group of southern Kwara and northern Osun; Kwara has most of Igbominaland (Omu-Aran, Isanlu Isin, Oke-Onigbin …); neighbours include 'the Ibolo sub-group of the cities of Offa, Oyun and Okuku'."),
    "WIREP": WS("Irepodun, Kwara State", "LGA, headquarters Omu-Aran; 'populated by the Igbomina people'; 'The people of Irepodun are Yorubas and mostly of Igbomina origin'."),
    "WIFEL": WS("Ifelodun, Kwara State", "LGA, headquarters Share; 'The people of Ifelodun are Yorubas and mostly of Igbomina origin'."),
    "WISIN": WS("Isin, Nigeria", "LGA created from old Irepodun in 1996, headquarters Owu-Isin; 'The Isin people are stock of the Igbomina'."),
    "WOFFA": WS("Offa, Nigeria", "City and LGA; 'Offa' means arrow in Yoruba; the Jalumi War with Ilorin."),
    "WOYUN": WS("Oyun", "LGA, headquarters Ilemona; 'Yoruba is the major language spoken'."),
    "WOKE": WS("Oke Ero", "LGA, headquarters Iloffa; its peoples 'predominantly indigenous Ekiti natives', who 'speak the Ekiti dialect with their kins in the Moba Local Government Area of Ekiti State'."),
    "WASA": WS("Asa, Kwara State", "LGA, secretariat at Afon; 'ethnic groups such as the Yoruba, Hausa, and Fulani are represented in the area'."),
    "WILO": WS("Ilorin", "Capital of Kwara; founded by the Yoruba; 'Modern Ilorin is mainly inhabited by the Yoruba people, although its traditional ruler has a Fulani heritage'."),
}
S, B, L = "spelling_variant", "endonym", "alternative"
FIELD = dict(B63.FIELD)
FIELD.update({"WP": "name given by Wikipedia", "1.C": "the speakers' own name, in tone-marked form"})
KW = lambda l: f"@admin_units:lga:kwara/{l}"
ST = lambda s: f"@admin_units:state:{s}"
PO = " Presence only; the nature of their presence is not established."

TEXT = {
 "gur": """Gur is a branch of the Niger–Congo languages spoken mainly in Burkina Faso, northern Ghana, Togo and Benin. Roger Blench's Atlas of Nigerian Languages (2020) classifies Baatọnum, the language of the Bariba of Kwara State, as Gur (South-Central), and Glottolog lists Gur (gura1261) as a family that contains it.""",
 "mande": """Mande is a family of languages of West Africa, centred on Mali, Guinea and their neighbours. In Nigeria it is represented by Busa and Bokobaru, spoken in the old Borgu region of Kwara, Niger and Kebbi states, which Roger Blench's Atlas of Nigerian Languages (2020) classifies as Southeast Mande; Glottolog lists Mande (mand1469) as a family, with Busa and Boko in its Eastern Mande branch.""",
 "baatonum": """Baatọnum, also called Bariba, is the language of the Bariba people of Kwara State and, mainly, of the Republic of Benin. Roger Blench's Atlas of Nigerian Languages (2020) places it in 'Kwara State; mainly in Benin Republic', classifies it as Gur (South-Central), and cites 62,634 speakers in Nigeria in 1963 and 220,000 in all in 1987; a complete Bible appeared in 1996. In Kwara it is the main language of Baruten LGA, on the Benin border (Wikipedia), and Wikipedia notes that it is one of the languages of the traditional state of Borgu.""",
 "busa": """Busa, which its speakers call Bisã, is a Mande language of the old Borgu region of Nigeria and Benin. Roger Blench's Atlas of Nigerian Languages (2020) places it in Kwara State, in Borgu LGA of Niger State, in Bagudo LGA of Kebbi State and in Benin Republic, classifies it as Southeast Mande, and cites about 50,000 speakers in Nigeria and 50,000 in Benin (1987). In Kwara, Wikipedia names Baruten LGA, and towns including Kosubosu and Kaiama; it calls Busa the most populous Mande language of Nigeria. Its Hausa name is Busanci.""",
 "bokobaru": """Bokobaru is a Mande language of Kaiama, in Kwara State. Roger Blench's Atlas of Nigerian Languages (2020) places it in 'Kaiama town and surrounding villages', classifies it in the Busa cluster of Southeast Mande, and estimates 30,000–40,000 speakers (2004); Mark and Titus were published in it in 1970. Wikipedia treats it as a dialect of Busa, spoken mainly in Kaiama and Baruten LGAs and not in Benin, and describes it as the major language of Kaiama LGA.""",
 "bariba": """The Bariba, who call themselves Baatonu (plural Baatombu), are a people of the old Borgu region, living mainly in northern Benin and, in Nigeria, in Baruten LGA of western Kwara State, according to Wikipedia. The federal government's state profile names them among the principal groups of Kwara State. Wikipedia describes them as co-founders of the Borgu kingdoms, with Nikki in Benin as their traditional capital, and gives perhaps a million Bariba in all, about 70% of them in Benin. Their language is Baatọnum; most are Muslims and farmers.""",
 "busa_p": """The Busa, also called Bussawa in Hausa, are a Mande-speaking people of the old Borgu region of Nigeria and Benin. Wikipedia names 'Busa (Bokobaru) peoples' among the minorities of western Kwara State, and describes the people of the Bokobaru-speaking towns of Kaiama LGA as Bokobaru. They speak Busa, and many also speak Bariba; Wikipedia counts them, with the Boko, as one of the two sub-groups of the Bissa.""",
}
LANG = {
 "baatonum": ("Baatọnum", "@key:gur", [("Batonu", S, "1.A"), ("Bariba", L, "2.B"), ("Barba", L, "2.B"), ("Baruba", L, "WP")],
              [(ST("kwara"), "well_documented", "Atlas: 'Kwara State; mainly in Benin Republic'."),
               (KW("baruten"), "well_documented", "Wikipedia (Baruten): 'The main language of Baruten is Baruba'; Wikipedia (Bariba people): the Bariba are domiciled in Baruten.")],
              "baat1238", ["WBRT", "WBAR"]),
 "busa": ("Busa", "@key:mande", [("Bisã", B, "1.B"), ("Boussa", S, "1.A"), ("Busanci", L, "2.B"), ("Busagwe", L, "2.B")],
          [(ST("kwara"), "well_documented", "Atlas: 'Kwara State'."),
           (KW("baruten"), "well_documented", "Wikipedia (Busa language): spoken 'in Baruten LGA of Kwara state', in towns including Kosubosu."),
           (KW("kaiama"), "reported", "Wikipedia (Busa language) names Kaiama among the towns where Busa is spoken; Bokobaru is the major language there (reported)."),
           (ST("niger"), "well_documented", "Atlas: 'Niger State, Borgu LGA'."),
           (ST("kebbi"), "well_documented", "Atlas: 'Kebbi State, Bagudo LGA'.")],
          "busa1253", ["WBUSA"]),
 "bokobaru": ("Bokobaru", "@key:mande", [("Kaiama", L, "2.C"), ("Kaama", L, "2.B"), ("Zugweya", L, "2.B"), ("Bokhobaru", S, "WP")],
              [(ST("kwara"), "well_documented", "Atlas: 'Kwara State. Kaiama town and surrounding villages'."),
               (KW("kaiama"), "well_documented", "Atlas: Kaiama town and surrounding villages; Wikipedia (Kaiama): 'Bokobaru is the major language spoken across the local government area'."),
               (KW("baruten"), "reported", "Wikipedia (Busa language): Bokobaru 'is spoken mainly in Kayama and Baruten LGA's' (reported).")],
              "boko1267", ["WKAI", "WBUSA"]),
}
RECORDS = [
    dict(key="gur", table="languages", evidence="multiple_sources", level="well_documented",
         fields=dict(lang_type="branch", name="Gur", slug="gur", glottocode="gura1261", summary=TEXT["gur"].split(". ")[0] + ".", description=TEXT["gur"]),
         srcs=[("ATLAS", "Baatọnum classified 'Gur: South-Central'"), ("GLIDX", "Gur (gura1261)")]),
    dict(key="mande", table="languages", evidence="multiple_sources", level="well_documented",
         fields=dict(lang_type="branch", name="Mande", slug="mande", glottocode="mand1469", summary=TEXT["mande"].split(". ")[0] + ".", description=TEXT["mande"]),
         srcs=[("ATLAS", "Busa and Bokobaru classified Southeast Mande"), ("GLIDX", "Mande (mand1469)")]),
]
for k, (name, parent, names, links, g, extra) in LANG.items():
    RECORDS.append(dict(key=k, table="languages", evidence="multiple_sources", level="well_documented",
                        fields=dict(lang_type="language", name=name, slug=k, parent_id=parent, glottocode=g, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k]),
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers"), ("GLIDX", f"Glottocode {g}")] + [(s, f"{name}: location") for s in extra]))
RECORDS += [
    dict(key="bariba", table="ethnic_groups", evidence="multiple_sources", level="well_documented",
         fields=dict(name="Bariba", slug="bariba", endonym="Baatonu", summary=TEXT["bariba"].split(". ")[0] + ".", description=TEXT["bariba"]),
         srcs=[("FGKW", "Bariba among the principal groups of Kwara"), ("WBAR", "Bariba people: name, Borgu, Baruten, numbers"), ("WKWS", "Bariba (Baatonu) minority in the west")]),
    dict(key="busa_p", table="ethnic_groups", evidence="multiple_sources", level="well_documented",
         fields=dict(name="Busa", slug="busa-people", summary=TEXT["busa_p"].split(". ")[0] + ".", description=TEXT["busa_p"]),
         srcs=[("WKWS", "Busa (Bokobaru) peoples in the west"), ("WBUSA", "Busa people: Bussawa, Bissa sub-group, Bariba spoken"), ("WKAI", "the people of the Bokobaru-speaking towns")]),
]
NAMES = []
for k, (name, parent, names, links, g, extra) in LANG.items():
    for n, t, fld in names:
        key = fld.split(" ")[0]
        if key == "WP":
            NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=f"{FIELD['WP'][0].upper()}{FIELD['WP'][1:]}.", srcs=[extra[0]]))
        else:
            NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=f"Blench's Atlas (2020), field {fld}: {FIELD.get(key, 'head entry')}.", srcs=["ATLAS"]))
NAMES += [
    dict(record="bariba", name="Baatonu", name_type="endonym", usage_notes="Wikipedia (Bariba people): 'self designation Baatonu (plural Baatombu)'.", srcs=["WBAR"]),
    dict(record="bariba", name="Baruba", name_type=S, usage_notes="Wikipedia (Kwara State; Baruten).", srcs=["WKWS"]),
    dict(record="busa_p", name="Bussawa", name_type="exonym", usage_notes="Wikipedia (Busa language): 'The Busa people are referred to as Bussawa in Hausa.'", srcs=["WBUSA"]),
    dict(record="busa_p", name="Bokobaru", name_type=L, usage_notes="Wikipedia (Kaiama): 'The people of the Bokobaru speaking towns and villages are called Bokobaru'; Wikipedia (Kwara State): 'Busa (Bokobaru) peoples'.", srcs=["WKAI"]),
]
RELATIONS = []
for k, (name, parent, names, links, g, extra) in LANG.items():
    for ref, lvl, note in links:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=ref, source="ATLAS", evidence="multiple_sources" if "Wikipedia" in note else "single_reliable_source", level=lvl, notes=note))
RELATIONS += [
    dict(frm="@languages:nupe", type="spoken_in", to=KW("edu"), source="ATLAS", evidence="multiple_sources", level="well_documented",
         notes="Atlas: 'Kwara State, Edu and Kogi LGAs'; Wikipedia (Edu): 'A Nupe speaking area'."),
    dict(frm="@languages:nupe", type="spoken_in", to=KW("pategi"), source="WPAT", evidence="single_reliable_source", level="well_documented",
         notes="Wikipedia (Pategi): 'inhabited predominantly by the Nupe people who speak the Nupe language'. The Atlas's Edu LGA predates Pategi LGA."),
    dict(frm="bariba", type="speaks", to="baatonum", source="WBAR", evidence="multiple_sources", level="well_documented", notes="Wikipedia: Baatonum 'is the language of the Bariba people of Benin and Nigeria'; Atlas: own name Bàrgú."),
    dict(frm="busa_p", type="speaks", to="busa", source="WBUSA", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Busa language): the language of the Busano/Bussawa people."),
    dict(frm="busa_p", type="speaks", to="bokobaru", source="WKAI", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Kaiama): the people of the Bokobaru-speaking towns; Wikipedia (Busa language): Bokobaru a Busa dialect."),
    dict(frm="bariba", type="present_in", to=ST("kwara"), source="FGKW", evidence="multiple_sources", level="well_documented", notes="Federal profile: among the principal groups of Kwara State; Wikipedia: Bariba (Baatonu) in the west."),
    dict(frm="bariba", type="present_in", to=KW("baruten"), source="WBAR", evidence="multiple_sources", level="well_documented", settlement_status="unknown",
         notes=f"Wikipedia (Bariba people): 'In Nigeria, they are domiciled in the Baruten local government area'; Wikipedia (Baruten): the main language is Baruba.{PO}"),
    dict(frm="busa_p", type="present_in", to=ST("kwara"), source="WKWS", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Kwara State): 'Busa (Bokobaru) peoples in the west'."),
    dict(frm="busa_p", type="present_in", to=KW("kaiama"), source="WKAI", evidence="multiple_sources", level="well_documented", settlement_status="unknown",
         notes=f"Wikipedia (Kaiama): the Bokobaru-speaking people of the LGA, all wards except Adena and Bani; Blench's Atlas: Bokobaru at Kaiama town.{PO}"),
    dict(frm="busa_p", type="present_in", to=KW("baruten"), source="WBUSA", evidence="single_reliable_source", level="reported", settlement_status="unknown",
         notes=f"Wikipedia (Busa language): Busa spoken in Baruten LGA, including Kosubosu (reported).{PO}"),
    dict(frm="@ethnic_groups:nupe", type="present_in", to=ST("kwara"), source="FGKW", evidence="multiple_sources", level="well_documented", notes="Federal profile: among the principal groups; Wikipedia: 'sizable minorities of Nupe people in the northeast'."),
    dict(frm="@ethnic_groups:nupe", type="present_in", to=KW("edu"), source="WEDU", evidence="multiple_sources", level="well_documented", settlement_status="unknown", notes=f"Wikipedia (Edu): 'A Nupe speaking area'; Blench's Atlas: Nupe in Edu LGA.{PO}"),
    dict(frm="@ethnic_groups:nupe", type="present_in", to=KW("pategi"), source="WPAT", evidence="single_reliable_source", level="well_documented", settlement_status="unknown", notes=f"Wikipedia (Pategi): 'inhabited predominantly by the Nupe people'.{PO}"),
    dict(frm="@ethnic_groups:fulani", type="present_in", to=ST("kwara"), source="FGKW", evidence="multiple_sources", level="well_documented", notes="Federal profile: among the principal groups; Wikipedia: 'Fulani people in Ilorin and moving through the state as nomadic herders'."),
    dict(frm="@ethnic_groups:fulani", type="present_in", to=KW("asa"), source="WASA", evidence="single_reliable_source", level="reported", settlement_status="unknown", notes=f"Wikipedia (Asa): Fulani among the groups 'represented in the area'.{PO}"),
    dict(frm="@ethnic_groups:fulani", type="present_in", to=KW("kaiama"), source="WKAI", evidence="single_reliable_source", level="reported", settlement_status="unknown", notes=f"Wikipedia (Kaiama): 'The major language spoken in Bani ward is Fulani'.{PO}"),
    dict(frm="@ethnic_groups:hausa", type="present_in", to=KW("asa"), source="WASA", evidence="single_reliable_source", level="reported", settlement_status="unknown", notes=f"Wikipedia (Asa): Hausa among the groups 'represented in the area'.{PO}"),
    dict(frm="@ethnic_groups:hausa", type="present_in", to=KW("kaiama"), source="WKAI", evidence="single_reliable_source", level="reported", settlement_status="unknown", notes=f"Wikipedia (Kaiama): Yoruba and Hausa dominant in Adena ward.{PO}"),
    dict(frm="@ethnic_groups:yoruba", type="present_in", to=ST("kwara"), source="FGKW", evidence="multiple_sources", level="well_documented", notes="Federal profile: among the principal groups, Yoruba a major language; Wikipedia: 'primarily the majority Yoruba people that live throughout the state'."),
]
YOR = [
    ("irepodun", "WIREP", "well_documented", "the Igbomina: 'populated by the Igbomina people' (Wikipedia, Irepodun)"),
    ("ifelodun", "WIFEL", "well_documented", "the Igbomina: 'Yorubas and mostly of Igbomina origin' (Wikipedia, Ifelodun)"),
    ("isin", "WISIN", "well_documented", "the Igbomina: 'The Isin people are stock of the Igbomina' (Wikipedia, Isin)"),
    ("offa", "WIGBO", "well_documented", "the Ibolo: 'the Ibolo sub-group of the cities of Offa, Oyun and Okuku' (Wikipedia, Igbomina)"),
    ("oyun", "WOYUN", "well_documented", "the Ibolo (Wikipedia, Igbomina); 'Yoruba is the major language spoken' (Wikipedia, Oyun)"),
    ("oke-ero", "WOKE", "well_documented", "the Ekiti: 'predominantly indigenous Ekiti natives' (Wikipedia, Oke Ero)"),
    ("asa", "WASA", "reported", "Wikipedia (Asa) names the Yoruba among the groups 'represented in the area'"),
    ("kaiama", "WKAI", "reported", "Wikipedia (Kaiama): Yoruba dominant in Adena ward"),
    ("ilorin-west", "WILO", "reported", "the LGA is named after the city of Ilorin, 'mainly inhabited by the Yoruba people' (Wikipedia, Ilorin); the LGA's own people are not described in a source read"),
    ("ilorin-east", "WILO", "reported", "the LGA is named after the city of Ilorin, 'mainly inhabited by the Yoruba people' (Wikipedia, Ilorin); the LGA's own people are not described in a source read"),
    ("ilorin-south", "WILO", "reported", "the LGA is named after the city of Ilorin, 'mainly inhabited by the Yoruba people' (Wikipedia, Ilorin); the LGA's own people are not described in a source read"),
    ("ekiti", "WKWS", "reported", "no source read names this LGA's people; Wikipedia: the Yoruba 'live throughout the state'"),
    ("moro", "WKWS", "reported", "no source read names this LGA's people; Wikipedia: the Yoruba 'live throughout the state'"),
]
for l, src, lvl, why in YOR:
    RELATIONS.append(dict(frm="@ethnic_groups:yoruba", type="present_in", to=KW(l), source=src, evidence="multiple_sources" if lvl == "well_documented" else "single_reliable_source",
                          level=lvl, settlement_status="unknown", notes=f"Yoruba: {why}.{PO}"))
GAPS = [
    ("Kwara: Sorko", "The Atlas lists Sorko [†], fishermen on Lake Kainji in Niger, Kwara and Kebbi states, most of whom now speak only Hausa; no Glottolog entry was found and it is not recorded."),
    ("Kwara: Bokobaru and Busa", "The Atlas and Glottolog treat Bokobaru as a language of the Busa cluster; Wikipedia calls it a dialect of Busa. Both are recorded as languages, with this noted."),
    ("Kwara: Kambari II and the old Borgu LGA", "The Atlas's 'Kwara State, Borgu LGA' (Kambari II, Busa) refers to Borgu before it moved to Niger State in 1991; not linked to Kwara."),
    ("Kwara: peoples of Moro, Ekiti and the Ilorin LGAs", "No source read describes the people of Moro, Ekiti LGA or the three Ilorin LGAs; the Yoruba links are reported. The Fulani of Ilorin are linked at state level only."),
    ("Kwara: Yoruba sub-groups", "The Igbomina, Ibolo and Ekiti are recorded in the Yoruba link notes, as in the other south-western states."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kwara (Phase 3): Gur and Mande branches; Baatọnum, Busa and Bokobaru; the Bariba and Busa peoples; Nupe in Edu and Pategi; Yoruba (Igbomina, Ibolo, Ekiti) by LGA; Fulani and Hausa.")


def report():
    lg = {r["to"] for r in RELATIONS if r["type"] == "present_in" and "lga:" in r["to"]}
    L_ = ["# Research batch 154 — Kwara: languages and peoples", "",
          f"Researched {ACCESSED}. Phase 3, the first Kwara batch. Created in review; published only after your approval.", "",
          "## Summary", "",
          "- **Languages:** Yoruba and Nupe were already linked to Kwara. The new records are:",
          "  - **Baatọnum** (Bariba), a **Gur** language: Baruten",
          "  - **Busa** and **Bokobaru**, **Mande** languages: Kaiama and Baruten, and Niger and Kebbi states",
          "  - 2 new branches, Gur and Mande",
          "  - **Nupe** is now also linked to Edu and Pategi LGAs",
          "- **Peoples:** 2 new records, the **Bariba** (own name Baatonu) and the **Busa** (Bussawa in Hausa; called Bokobaru at Kaiama). The federal profile names the Yoruba, Nupe, Bariba and Fulani as the state's principal groups.",
          f"- **People–LGA links in {len(lg)} of 16 LGAs:**",
          "  - **Yoruba:** the Igbomina in Irepodun, Ifelodun and Isin; the Ibolo in Offa and Oyun; the Ekiti in Oke Ero (*well documented*). Asa, Kaiama, the three Ilorin LGAs, Ekiti LGA and Moro are *reported*.",
          "  - **Nupe:** Edu and Pategi",
          "  - **Bariba:** Baruten",
          "  - **Busa:** Kaiama (and Baruten, *reported*)",
          "  - **Fulani and Hausa:** in Asa and Kaiama (*reported*)",
          "- **Not recorded:** **Sorko**. The Atlas marks it as nearly extinct (its speakers have shifted to Hausa), and no Glottolog entry was found.",
          "- **Decision for you:** the people record is named **Bariba**, with **Baatonu** as their own name. Wikipedia and the federal profile both use Bariba, and it is not a flagged name, so your keep-both-names rule only needs Baatonu recorded beside it. Say if you prefer Baatonu as the record name.", "",
          "## The new records", ""] + [f"**{k}.** {TEXT[k]}\n" for k in TEXT]
    L_ += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_154_kwara_languages_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_154_kwara_languages_peoples_REVIEW.md", "w").write(report())
    lg = {r["to"] for r in RELATIONS if r["type"] == "present_in" and "lga:" in r["to"]}
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} LGAs-with-people={len(lg)}")
