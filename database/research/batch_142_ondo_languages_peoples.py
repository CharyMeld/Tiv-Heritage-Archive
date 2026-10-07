"""
Research batch 142 — Ondo (Phase 3, first batch): languages and peoples. Researched 2026-10-07.
Pattern: batches 051 (Kogi languages), 111 (Lagos, new people record) and 129 (Osun peoples).

Languages: the column-split Atlas text was searched for 'Ondo' and the Ondo LGA and town names. Ten entries concern Ondo:
  Ahan (No. 8), Akpes cluster (14), Arigidi cluster (24), Ehuẹun (109), Ịjọ/Ịzọn (195), Iyayu (208), Uhami (454),
  Ukaan (455), Ukue (458) and Yoruba (489). Yoruba is already linked to Ondo (relation 2409).
Boundaries: the Atlas uses the LGAs as they were before 1996, when Akoko North became Akoko North-East and North-West,
  Akoko South became Akoko South-East and South-West, and Ilaje/Ese-Odo became Ilaje and Ese Odo. Each Atlas town is
  placed in today's LGA by a second source: Wikipedia's town lists for Akoko North-West and North-East, the Isua and Ifon
  pages, and INEC's 2015 ward directory (Epinmi I and II wards are in Akoko South-East; Auga and Ikakumo in North-East).
Peoples:
  * Federal Government of Nigeria, state profile 'Ondo' (data/fg_ondo_2026-10-07.html), 'Ethnic Profile': 'Yoruba
    Sub-ethnic groups of Akoko, Akure, Ikale, Ilaje, Ondo, Owo with minorities such as Ijaw and Apoi.'
  * Wikipedia, 'Ondo State': Yoruba sub-groups 'of the Idanre, Akoko, Akure, Ikale, Ilaje, Ondo, Ese Odo, Owo and Ose
    peoples. Ijaw people, such as the Apoi and Arogbo populations inhabit the southeastern swamps'.
  * LGA pages (Wikipedia) place the sub-groups: Akoko (four Akoko LGAs), Ikale (Okitipupa, Irele, part of Odigbo),
    Ilaje, Odigbo (Ondo lineage, plus Furupagha Ijaw), Owo, Idanre, Ose (Idoani), Ile-Oluji/Okeigbo; Ese Odo is Ijaw.
New records: the Ijoid branch, nine languages (Ahan, Akpes, Arigidi, Ehuẹun, Iyayu, Uhami, Ukaan, Ukue, Ịzọn) and the
Ijaw people.
"""
import json, sys
import batch_087_kano_languages as L87
import batch_063_borno_languages as B63

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=f"{n} Accessed {ACCESSED}.")
SOURCES = {
    "ATLAS": dict(L87.SOURCES["ATLAS"], notes="Reused. Ondo entries read: Ahan (No. 8), Akpes cluster (14), Arigidi cluster (24), Ehuẹun (109), Ịjọ/Ịzọn (195), Iyayu (208), Uhami (454), Ukaan (455), Ukue (458), Yoruba (489)."),
    "GLIDX": L87.SOURCES["GLIDX"],
    "FGON": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Ondo State (state profile)",
                 organisation="Federal Government of Nigeria", url="https://nigeria.gov.ng/states/ondo/", verification_status="verified",
                 notes="'Ethnic Profile': 'Yoruba Sub-ethnic groups of Akoko, Akure, Ikale, Ilaje, Ondo, Owo with minorities such as Ijaw and Apoi.' Capital Akure; created 3 February 1976. Its LGA list stops at 15 of the 18 names. Read 2026-10-07 (copy in database/research/data/)."),
    "INEC": dict(source_type="government_publication", source_kind="government_publication", source_tier=1,
                 title="Directory of Polling Units: Ondo State (Revised January 2015)",
                 organisation="Independent National Electoral Commission (INEC)", publication_date="2015-01",
                 url="https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_Ondo.pdf",
                 archive_reference="Internet Archive copy of the INEC PDF (no longer on the INEC site)", verification_status="verified",
                 notes="Read for town locations: Epinmi I and II wards and Isua I–IV in Akoko South-East; Auga and Ikakumo polling units in Akoko North-East; Arigidi, Ajowa, Igasi, Gedegede, Iyani, Ibaramu, Ikaramu and Erusu in Akoko North-West. INEC's disclaimer: not a legal or administrative document for boundary or political claims."),
    "WOND": WS("Ondo State", "Ethnic groups: Yoruba sub-groups 'of the Idanre, Akoko, Akure, Ikale, Ilaje, Ondo, Ese Odo, Owo and Ose peoples'; 'Ijaw people, such as the Apoi and Arogbo populations inhabit the southeastern swamps close to the Edo state border'; Oke-Igbo speaks a variant 'similar to the Ife dialect'."),
    "WAKO": WS("Akoko", "The Akoko make up four LGAs (Akoko North-East, North-West, South-East, South-West) and Akoko-Edo in Edo State; its opening line calls them 'a large Edo cultural sub-group', the same article 'one of the few Yoruba clans with no distinctive local dialect'."),
    "WAKNW": WS("Akoko North-West", "HQ Oke-Agbe; towns include Ese, Okeagbe, Ikaram, Arigidi, Erusu, Ibaram, Iyani, Ase, Ajowa, Oyin, Igasi, Gedegede; traditional rulers include those of Uro Ajowa, Ojo Ajowa, Daja Ajowa and Esuku Ajowa; languages: Yoruba, Akoko (Arigidi), Akpes, Ahan, Ayere."),
    "WAKNE": WS("Akoko North-East", "HQ Ikare; towns Auga, Ugbe, Ise, Iboropa, Akunnu, Ikare."),
    "WAKSE": WS("Akoko South-East", "HQ Isua; ward list includes Epinmi-Akoko."),
    "WARIG": WS("Akoko language", "North Akoko, also called Arigidi, a dialect cluster; varieties Arigidi, Erúṣú, Oyín, Ìgáṣí, Eṣé, Urò, Ọ̀jọ̀, Àfá, Ògè, Ìdò and Àjè."),
    "WAKPES": WS("Akpes language", "Own name Àbèsàbèsì; about 7,000 speakers in nine settlements of Akoko North-East and North-West LGAs (Akunnu, Ase, Gedegede, Ibaramu, Ikaramu, Iyani and three quarters of Ajowa); endangered; affiliation disputed."),
    "WUKAAN": WS("Ukaan language", "Also Ikan, Anyaran, Auga or Kakumo; own name Ùkãã or Ìkã; affiliation uncertain; dialects in Ikakumo (Edo), Ise, Auga and Anyaran."),
    "WAHAN": WS("Ahan language", "Àhàn, an endangered language closely related only to Ayere; spoken at Ahan-Ayegunle in the Omuo Oke district; Ethnologue: Ekiti East LGA."),
    "WUKUE": WS("Ukue language", "'Ukue (Epinmi) is an Edoid language of Ondo State'; sometimes considered the same language as Ehuẹun."),
    "WEHU": WS("Ehueun language", "'Ehuẹun (Ekpimi) is an Edoid language of Ondo State'; sometimes considered the same language as Ukue."),
    "WIDOANI": WS("Idoani", "A town in Ose LGA; its Iyayu quarter speaks 'both Yoruba and Iyayu'; Iyayu 'has been described as being non-Yoruba'; the other quarters belong to the Ao group of eastern Yoruba."),
    "WOSE": WS("Ose, Nigeria", "LGA, HQ Ifon; communities include Ido-ani."),
    "WESE": WS("Ese Odo", "LGA 'populated by the Ijaw (Izon) ethnic sub groups of the Western Apoi tribe and the Arogbo tribe'; HQ Igbekebo; 'the only local government that are Ijoid in the whole of South-West'."),
    "WILAJE": WS("Ilaje", "LGA, HQ Igbokoda, 'inhabited by the Ilajes … a distinct migratory coastal group of ethnic Yoruba people'; 'the Yoruba speaking Apoi and Arogbo Ijaws are located to the north east in Ese Odo LGA'."),
    "WOKIT": WS("Okitipupa", "Okitipupa 'is a part of the Ikale-speaking nation'; Ikaleland comprises the present Okitipupa, Irele and Odigbo LGAs and part of Ogun Waterside."),
    "WODIG": WS("Odigbo", "'The people of Odigbo local government are from the Ondo lineage and the Furupagha-Ijaws that constituted the Ebijaw ward'; parts are occupied by Ikale people of Okitipupa and Irele."),
    "WOWO": WS("Owo", "City and LGA; between 1400 and 1600 the capital of a Yoruba city-state."),
    "WIDAN": WS("Idanre", "HQ of Idanre LGA; residents 'mainly a Yoruba speaking people (with a tongue similar to the Ondo dialect)'."),
    "WILE": WS("Ile Oluji-Okeigbo", "Town and LGA, HQ Ile Oluji; 'The indigenes are Yoruba people'."),
    "WAKURE": WS("Akure", "Capital of Ondo State; the Akure Kingdom, ruled by the Deji."),
    "WIJAW": WS("Ijaw people", "'The Izon people or Izon Otu, otherwise known as the Ijaw people'; mainly in Bayelsa, Delta and Rivers, also Ondo, Edo, Akwa Ibom, Abia and Ogun; Western Izon includes the Apoi and Arogbo; fishing, farming and trading; Egbesu."),
}
S, B, L = "spelling_variant", "endonym", "alternative"
FIELD = dict(B63.FIELD)
ON = lambda l: f"@admin_units:lga:ondo/{l}"
ST = lambda s: f"@admin_units:state:{s}"
PO = " Presence only; the nature of their presence is not established."

TEXT = {
 "ijoid": """Ijoid is a branch of the Niger–Congo languages spoken in the Niger Delta of southern Nigeria. Roger Blench's Atlas of Nigerian Languages (2020) classifies Ịjọ as Ijoid and describes it as a cluster of two groups, Eastern and Western, the Western including Ịzọn. Glottolog lists Ijoid (ijoi1239) as a family. In Ondo State it is represented by the Ịzọn of Ese Odo LGA.""",
 "izon": """Ịzọn is an Ijọ language of the Niger Delta; Wikipedia calls it the most important of the Ijo languages. Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as Ijoid, places it in Rivers and Bayelsa states, in Burutu, Warri and Bomadi LGAs of Delta State, and in 'Ondo State, Ikale and Ilaje Ese–Odo LGAs', and estimates about 1,000,000 speakers (Williamson 1989). Its many dialects are named after clans; the Atlas groups Arogbo and Fụrụpagha with the Western Delta dialects. In Ondo State, Wikipedia describes Ese Odo LGA as populated by Ijaw (Izon) sub-groups, the Western Apoi and the Arogbo. A primer for a standard Ịzọn appeared in 1988, and Gospels were printed in the Kabowei dialect in 1924.""",
 "ahan": """Ahan (Àhàn) is an endangered language of the Akoko area. Roger Blench's Atlas of Nigerian Languages (2020) places it in the towns of Ajowa, Igashi and Omou in what it calls 'Ondo State, Ekiti LGA', and classifies it with Ayere as Volta–Niger: Ayere–Ahan. Ajowa and Igasi are towns of Akoko North-West LGA, and Wikipedia lists Ahan among that LGA's languages; Omuo is now in Ekiti State. Wikipedia describes Ahan as closely related only to Ayere, spoken at Ahan-Ayegunle in the Omuo Oke district, with perhaps 300 speakers in 2000 (Blench) and up to about 2,400 by a 2024 estimate.""",
 "akpes": """Akpes, called Àbèsàbèsì by its speakers, is a cluster of related speech forms in the Akoko area of northern Ondo State. Roger Blench's Atlas of Nigerian Languages (2020) places it in Akoko North LGA (since 1996 Akoko North-East and North-West), classifies it as a branch of Benue–Congo of its own, and lists the varieties Akpes (at Akunnu and Ajowa), Asẹ, Daja, Efifa (of doubtful status), Esuku, Gedegede, Ibaram, Ikorom (Ikaram) and Iyani, with 5,000–8,000 speakers at Ikaram (1986). Wikipedia gives about 7,000 speakers in nine settlements of Akoko North-East and North-West, describes the language as endangered, with Yoruba replacing it in more and more domains, and notes that its exact place within Volta–Niger is disputed. The name Àbèsàbèsì, from àbès 'we', was coined by a meeting of representatives of all nine settlements.""",
 "arigidi": """Arigidi, also called North Akoko or Akoko, is a cluster of related speech forms of the Akoko area of northern Ondo State. Roger Blench's Atlas of Nigerian Languages (2020) classifies it as Benue–Congo: Defoid: Akokoid and lists ten members, each spoken in a town of what it calls Akoko North LGA: Afa, Ese, Oge and Udo in sections of Oke-Agbe, Arigidi and Eruṣu in their towns, Igaṣi (45,000 speakers in 1986), Ọjọ and Uro in Ajowa, and Oyin at Oyin-Akoko. These towns are now in Akoko North-West LGA. Wikipedia notes, from Fadoro (2010), that the forms fall into three groups, Arigidi with Erushu, the varieties of Oke-Agbe, and those outside Oke-Agbe, which are not mutually intelligible with each other.""",
 "ehueun": """Ehuẹun is an Edoid language of Akoko, in Ondo State. Roger Blench's Atlas of Nigerian Languages (2020) places it in Akoko South LGA (since 1996 Akoko South-East and South-West), gives Ẹkpenmi, Ekpimi and Epimi as names based on location, classifies it as North-Western Edoid, and cites 5,766 speakers in 1963. The town of Epinmi is in Akoko South-East, where INEC lists the Epinmi I and II wards. Wikipedia notes that it is sometimes considered the same language as Ukue, and Glottolog groups the two as Ukue–Ehueun.""",
 "iyayu": """Iyayu is an Edoid language spoken in one quarter of the town of Idoani, in Ose LGA of Ondo State. Roger Blench's Atlas of Nigerian Languages (2020) classifies it as North-Western Edoid (Osse) and cites 9,979 speakers in 1963. Wikipedia's article on Idoani names Iyayu as one of the town's six historic quarters, whose people speak both Yoruba and Iyayu, a language described as non-Yoruba.""",
 "uhami": """Uhami is an Edoid language of Isua, the headquarters of Akoko South-East LGA in Ondo State. Roger Blench's Atlas of Nigerian Languages (2020) gives Isua as another name, places it in what it calls 'Akoko–South and Owo LGAs', classifies it as North-Western Edoid, and cites 5,498 speakers in 1963. Wikipedia gives Uhami as the native name of Akoko South-East, and Glottolog places it with Iyayu in the Osse group of North-Western Edoid.""",
 "ukaan": """Ukaan is a language of the Akoko area on the border of Ondo and Edo states, of uncertain affiliation. Roger Blench's Atlas of Nigerian Languages (2020) places it in the towns of Kakumo-Aworo, Auga and Iṣe in what it calls Akoko North LGA of Ondo State, and in Kakumo-Akoko and Anyaran in Akoko Edo LGA of Edo State, and classifies it as a branch of Benue–Congo of its own. Its speakers call it Ùkãã or Ìkã. Auga and Ise are towns of Akoko North-East LGA. Wikipedia notes that Blench considers it at least three different languages, with different words in Ise, Ikakumo and Auga.""",
 "ukue": """Ukue is an Edoid language of Epinmi, in Akoko South-East LGA of Ondo State. Roger Blench's Atlas of Nigerian Languages (2020) places it in Akoko South LGA (since 1996 Akoko South-East and South-West), gives Ukpe and Ẹkpenmi as names based on location, classifies it as North-Western Edoid, and cites 5,702 speakers in 1963. INEC lists the Epinmi I and II wards in Akoko South-East. Wikipedia notes that it is sometimes considered the same language as Ehuẹun.""",
 "ijaw": """The Ijaw, who call themselves Izon, are a people of the Niger Delta, living mainly in Bayelsa, Delta and Rivers states and also in Ondo, Edo, Akwa Ibom, Abia and Ogun states, according to Wikipedia. The federal government's state profile names the Ijaw and the Apoi as minorities of Ondo State. In Ondo they live in the south-eastern swamps near the Edo border: Wikipedia describes Ese Odo LGA as populated by the Western Apoi and Arogbo Ijaw, and names Furupagha Ijaw communities of the Ebijaw ward in Odigbo LGA. Their languages belong to the Ijoid branch of Niger–Congo; Wikipedia calls Ịzọn the most important of them. Wikipedia describes them as living by fishing, farming and trade, and as among the first Nigerian peoples to trade with Europeans, as go-betweens with the interior, and names Egbesu among their deities.""",
}
# key: (name, parent, [(name, type, field)], [(unit ref, level, note)], glottocode, [extra source keys])
LANG = {
 "izon": ("Ịzọn", "@key:ijoid", [("Izon", S, "1.A"), ("Ijo", S, "1.A"), ("Ijaw", S, "1.A"), ("Ezọn", S, "1.A")],
          [(ST("ondo"), "well_documented", "Atlas: 'Ondo State, Ikale and Ilaje Ese–Odo LGAs'."),
           (ON("ese-odo"), "well_documented", "Atlas: 'Ilaje Ese–Odo LGA' (split in 1996 into Ilaje and Ese Odo); Wikipedia: Ese Odo is 'populated by the Ijaw (Izon) ethnic sub groups of the Western Apoi tribe and the Arogbo tribe', and Ilaje places the Apoi and Arogbo Ijaws in Ese Odo."),
           (ON("odigbo"), "reported", "The Atlas names Fụrụpagha among the Western Delta dialects of Ịzọn; Wikipedia (Odigbo) names 'the Furupagha-Ijaws that constituted the Ebijaw ward' among the LGA's people (reported)."),
           (ST("bayelsa"), "well_documented", "Atlas: 'Rivers and Bayelsa State, Yenagoa, and Sagbama LGAs'."),
           (ST("rivers"), "well_documented", "Atlas: 'Rivers and Bayelsa State'."),
           (ST("delta"), "well_documented", "Atlas: 'Delta State, Burutu, Warri and Bomadi LGAs'.")], "izon1238", ["WESE", "WIJAW"]),
 "ahan": ("Ahan", "@languages:ayere-ahan", [("Àhàn", B, "1.C")],
          [(ST("ondo"), "well_documented", "Atlas: 'Ondo State, Ekiti LGA, Ajowa, Igashi, and Omou towns'."),
           (ON("akoko-north-west"), "well_documented", "Atlas: the towns of Ajowa and Igashi; Wikipedia lists Ajowa and Igasi among the towns, and Ahan among the languages, of Akoko North-West."),
           (ST("ekiti"), "reported", "Atlas: Omou town (Omuo, in Ekiti State since 1996); Wikipedia: spoken at Ahan-Ayegunle in the Omuo Oke district, Ekiti East LGA (Ethnologue) (reported).")], "ahan1244", ["WAHAN", "WAKNW"]),
 "akpes": ("Akpes", None, [("Àbèsàbèsì", B, "WP"), ("Akunnu", L, "2.A (Akpes)"), ("Asẹ", L, "member"), ("Daja", L, "member"), ("Esuku", L, "member"),
                           ("Gedegede", L, "member"), ("Ibaram", L, "member"), ("Ikorom", L, "member"), ("Iyani", L, "member")],
           [(ST("ondo"), "well_documented", "Atlas: 'Ondo State, Akoko North LGA'."),
            (ON("akoko-north-west"), "well_documented", "Atlas: the towns of Ajowa, Asẹ, Gedegede, Ibaram and Ikaram (Akoko North LGA before 1996); Wikipedia and INEC place them in Akoko North-West."),
            (ON("akoko-north-east"), "well_documented", "Atlas: Akunnu town; Wikipedia lists Akunnu among the towns of Akoko North-East and places Akpes in 'the Akoko North-East and Akoko North-West LGAs'.")], "akpe1248", ["WAKPES", "WAKNW", "WAKNE"]),
 "arigidi": ("Arigidi", None, [("North Akoko", L, "WP"), ("Akoko", L, "Atlas"), ("Afa", L, "member"), ("Eruṣu", L, "member"), ("Ese", L, "member"),
                               ("Igaṣi", L, "member"), ("Oge", L, "member"), ("Ọjọ", L, "member"), ("Oyin", L, "member"), ("Udo", L, "member"), ("Uro", L, "member")],
             [(ST("ondo"), "well_documented", "Atlas: 'Ondo State, Akoko North LGA'; all ten members are placed in towns of Akoko North LGA."),
              (ON("akoko-north-west"), "well_documented", "Atlas: the towns of Arigidi, Eruṣu, Igaṣi, Oke-Agbe, Ajowa and Oyin-Akoko (Akoko North LGA before 1996); Wikipedia and INEC place them all in Akoko North-West.")], "arig1246", ["WARIG", "WAKNW"]),
 "ehueun": ("Ehuẹun", "@languages:edoid", [("Ehueun", S, "GL"), ("Ẹkpenmi", L, "2.A"), ("Ekpimi", L, "2.A"), ("Epimi", L, "2.A")],
            [(ST("ondo"), "well_documented", "Atlas: 'Ondo State, Akoko South LGA'."),
             (ON("akoko-south-east"), "well_documented", "Atlas: Akoko South LGA (split in 1996), name based on location Ẹkpenmi; Wikipedia: 'Ehuẹun (Ekpimi)'; INEC lists the Epinmi I and II wards in Akoko South-East.")], "ehue1238", ["WEHU", "INEC"]),
 "iyayu": ("Iyayu", "@languages:edoid", [("Idoani", L, "2.C")],
           [(ST("ondo"), "well_documented", "Atlas: 'Ondo State, one quarter of Idoani town'."),
            (ON("ose"), "well_documented", "Atlas: one quarter of Idoani town; Wikipedia: Idoani is in Ose LGA, and its Iyayu quarter speaks Iyayu.")], "iyay1238", ["WIDOANI", "WOSE"]),
 "uhami": ("Uhami", "@languages:edoid", [("Isua", L, "2.B")],
           [(ST("ondo"), "well_documented", "Atlas: 'Ondo State, Akoko–South and Owo LGAs'."),
            (ON("akoko-south-east"), "well_documented", "Atlas: other name Isua; Wikipedia: Isua is the headquarters of Akoko South-East, whose native name it gives as Uhami."),
            (ON("owo"), "reported", "The Atlas names Owo LGA (1963 figures, before later LGA changes); no other source read places Uhami in today's Owo LGA (reported).")], "uham1238", ["WAKSE"]),
 "ukaan": ("Ukaan", None, [("Ìkàn", S, "1.A"), ("Ikani", S, "1.A"), ("Ikaan", S, "1.A"), ("Ùkãã", B, "1.B"), ("Ìkã", B, "1.B"), ("Anyaran", L, "2.A"), ("Aika", L, "2.B")],
           [(ST("ondo"), "well_documented", "Atlas: 'Ondo State, Akoko North LGA, towns of Kakumo–Aworo (Kakumo–Kejĩ, Auga and Iṣe)'."),
            (ON("akoko-north-east"), "well_documented", "Atlas: the towns of Auga and Iṣe (Akoko North LGA before 1996); Wikipedia lists Auga and Ise among the towns of Akoko North-East, and INEC its Auga and Ikakumo polling units."),
            (ST("edo"), "well_documented", "Atlas: 'Edo State, Akoko Edo LGA, towns of Kakumo–Akoko and Anyaran'."),
            ("@admin_units:lga:edo/akoko-edo", "well_documented", "Atlas: 'Edo State, Akoko Edo LGA, towns of Kakumo–Akoko and Anyaran'.")], "ukaa1243", ["WUKAAN", "WAKNE", "INEC"]),
 "ukue": ("Ukue", "@languages:edoid", [("Ukpe", L, "2.A"), ("Ẹkpenmi", L, "2.A"), ("Epinmi", L, "WP")],
          [(ST("ondo"), "well_documented", "Atlas: 'Ondo State, Akoko South LGA'."),
           (ON("akoko-south-east"), "well_documented", "Atlas: Akoko South LGA (split in 1996), name based on location Ẹkpenmi; Wikipedia: 'Ukue (Epinmi)'; INEC lists the Epinmi I and II wards in Akoko South-East.")], "ukue1238", ["WUKUE", "INEC"]),
}
FIELD.update({"WP": "name given by Wikipedia", "Atlas": "the Atlas: 'a term used for the Arigidi cluster, Ahan, Ayere and Ọka'", "1.C": "the speakers' own name, in tone-marked form"})
NAME = {k: v[0] for k, v in LANG.items()}

RECORDS = [
    dict(key="ijoid", table="languages", evidence="multiple_sources", level="well_documented",
         fields=dict(lang_type="branch", name="Ijoid", slug="ijoid", glottocode="ijoi1239", summary=TEXT["ijoid"].split(". ")[0] + ".", description=TEXT["ijoid"]),
         srcs=[("ATLAS", "Ịjọ: classification 'Niger-Congo: Ijoid'"), ("GLIDX", "Ijoid (ijoi1239)")]),
]
for k, (name, parent, names, links, g, extra) in LANG.items():
    f = dict(lang_type="language", name=name, slug=k, glottocode=g, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k])
    if parent:
        f["parent_id"] = parent
    RECORDS.append(dict(key=k, table="languages", evidence="multiple_sources", level="well_documented", fields=f,
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers"), ("GLIDX", f"Glottocode {g}")] + [(s, f"{name}: location or description") for s in extra]))
RECORDS.append(dict(key="ijaw", table="ethnic_groups", evidence="multiple_sources", level="well_documented",
                    fields=dict(name="Ijaw", slug="ijaw", endonym="Izon", summary=TEXT["ijaw"].split(". ")[0] + ".", description=TEXT["ijaw"]),
                    srcs=[("FGON", "Ijaw and Apoi: minorities of Ondo State"), ("WOND", "Apoi and Arogbo Ijaw in the south-eastern swamps"), ("WESE", "Western Apoi and Arogbo in Ese Odo"),
                          ("WODIG", "Furupagha Ijaw in Odigbo"), ("WIJAW", "Ijaw people: names, states, languages, occupations, religion")]))

NAMES = []
for k, (name, parent, names, links, g, extra) in LANG.items():
    for n, t, fld in names:
        key = fld.split(" ")[0]
        src = [e for e in extra if e.startswith("W")][:1] if key == "WP" else (["GLIDX"] if key == "GL" else ["ATLAS"])
        if key in ("WP", "GL"):
            note = f"{FIELD[key][0].upper()}{FIELD[key][1:]}."
        elif key == "Atlas":
            note = "Blench's Atlas (2020): 'Akoko - a term used for the Arigidi cluster, Ahan, Ayere and Ọka'."
        else:
            note = f"Blench's Atlas (2020), field {fld}: {FIELD.get(key, 'head entry')}."
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=note, srcs=src))
NAMES += [
    dict(record="ijaw", name="Izon", name_type="endonym", usage_notes="Wikipedia (Ijaw people): 'The Izon people or Izon Otu, otherwise known as the Ijaw people due to the historic mispronunciation of the name Izon'. Ijaw is kept as the record name because the federal profile and Wikipedia use it for the whole people, of whom the Izon-speaking clans are the largest part.", srcs=["WIJAW"]),
    dict(record="ijaw", name="Ijo", name_type=S, usage_notes="The Atlas's name for the language cluster ('Ịjọ … Ijaw').", srcs=["ATLAS"]),
]

RELATIONS = []
for k, (name, parent, names, links, g, extra) in LANG.items():
    for ref, lvl, note in links:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=ref, source="ATLAS", evidence="multiple_sources" if ("Wikipedia" in note or "INEC" in note) else "single_reliable_source",
                              level=lvl, notes=note))
RELATIONS += [
    dict(frm="ijaw", type="speaks", to="izon", source="WIJAW", evidence="multiple_sources", level="well_documented",
         notes="Wikipedia: the Izon people, 'otherwise known as the Ijaw'; Izon the most important Ijo language. In Ondo, Ese Odo is 'populated by the Ijaw (Izon)'. The Atlas also lists 'Ìjọ̀–Àpọ̀ì' as a Yoruba dialect, and Wikipedia (Ilaje) calls the Apoi and Arogbo 'Yoruba speaking'."),
    dict(frm="ijaw", type="present_in", to=ST("ondo"), source="FGON", evidence="multiple_sources", level="well_documented",
         notes="Federal profile: 'minorities such as Ijaw and Apoi'; Wikipedia: 'Ijaw people, such as the Apoi and Arogbo populations inhabit the southeastern swamps close to the Edo state border'."),
    dict(frm="ijaw", type="present_in", to=ON("ese-odo"), source="WESE", evidence="multiple_sources", level="well_documented", settlement_status="unknown",
         notes=f"Wikipedia (Ese Odo): 'populated by the Ijaw (Izon) ethnic sub groups of the Western Apoi tribe and the Arogbo tribe'; Wikipedia (Ilaje) places the Apoi and Arogbo Ijaws in Ese Odo. The federal profile names the Apoi separately from the Ijaw.{PO}"),
    dict(frm="ijaw", type="present_in", to=ON("odigbo"), source="WODIG", evidence="single_reliable_source", level="reported", settlement_status="unknown",
         notes=f"Wikipedia (Odigbo): 'the Furupagha-Ijaws that constituted the Ebijaw ward', with the communities Eluju-Iyaradina, Ebijaw, Ebijaw Zion, Taribor, Gbunuwei, Ukuregbene and Abadigbene.{PO}"),
]
for s in ("bayelsa", "delta", "rivers"):
    RELATIONS.append(dict(frm="ijaw", type="present_in", to=ST(s), source="WIJAW", evidence="single_reliable_source", level="reported",
                          notes="Wikipedia (Ijaw people): 'majorly found in the Niger Delta in Nigeria, with significant population clusters in Bayelsa, in Delta, and in Rivers'. Linked at state level only; the LGAs are left for that state's own batch."))
RELATIONS.append(dict(frm="@ethnic_groups:yoruba", type="present_in", to=ST("ondo"), source="FGON", evidence="multiple_sources", level="well_documented",
                      notes="Federal profile: 'Yoruba Sub-ethnic groups of Akoko, Akure, Ikale, Ilaje, Ondo, Owo'; Wikipedia adds the Idanre, Ese Odo and Ose; 'The state is predominantly Yoruba'."))
# (lga, source, level, sub-group note)
YOR = [
    ("akoko-north-east", "WAKO", "well_documented", "the Akoko, one of the four Akoko LGAs (Wikipedia, Akoko)"),
    ("akoko-north-west", "WAKO", "well_documented", "the Akoko, one of the four Akoko LGAs (Wikipedia, Akoko)"),
    ("akoko-south-east", "WAKO", "well_documented", "the Akoko, one of the four Akoko LGAs (Wikipedia, Akoko); Isua, the headquarters, also speaks Uhami"),
    ("akoko-south-west", "WAKO", "well_documented", "the Akoko, one of the four Akoko LGAs (Wikipedia, Akoko)"),
    ("okitipupa", "WOKIT", "well_documented", "the Ikale: Okitipupa 'is a part of the Ikale-speaking nation' (Wikipedia, Okitipupa)"),
    ("irele", "WOKIT", "well_documented", "the Ikale: Ikaleland includes the present Irele LGA (Wikipedia, Okitipupa and Odigbo)"),
    ("odigbo", "WODIG", "well_documented", "people 'from the Ondo lineage' (Wikipedia, Odigbo), with Ikale in parts (Wikipedia, Okitipupa)"),
    ("ilaje", "WILAJE", "well_documented", "the Ilaje, 'a distinct migratory coastal group of ethnic Yoruba people' (Wikipedia, Ilaje)"),
    ("owo", "WOWO", "well_documented", "the Owo, named in the federal profile; Owo was the capital of a Yoruba city-state (Wikipedia, Owo)"),
    ("idanre", "WIDAN", "well_documented", "the Idanre, 'mainly a Yoruba speaking people (with a tongue similar to the Ondo dialect)' (Wikipedia, Idanre)"),
    ("ose", "WIDOANI", "well_documented", "the Ose, named by Wikipedia (Ondo State); Idoani's people are Yoruba of the Ao group (Wikipedia, Idoani)"),
    ("ile-oluji-okeigbo", "WILE", "well_documented", "'The indigenes are Yoruba people' (Wikipedia, Ile Oluji-Okeigbo); Oke-Igbo speaks a variant 'similar to the Ife dialect' (Wikipedia, Ondo State)"),
    ("akure-south", "WAKURE", "reported", "the Akure, named in the federal profile; Akure, the old Akure Kingdom, is the LGA's headquarters. No source read names the LGA's people"),
    ("ondo-west", "WOND", "reported", "the Ondo, named in the federal profile after the town of Ondo, the LGA's headquarters. No source read names the LGA's people"),
    ("ese-odo", "WOND", "reported", "Wikipedia (Ondo State) lists the 'Ese Odo' among the Yoruba sub-groups, while Wikipedia (Ese Odo) describes the LGA as Ijaw; the Atlas lists 'Ìjọ̀–Àpọ̀ì' as a Yoruba dialect"),
]
for l in ("akure-north", "ondo-east", "ifedore"):
    YOR.append((l, "FGON", "reported", "no source read names this LGA's sub-group; the federal profile describes the state's people as Yoruba sub-ethnic groups"))
for l, src, lvl, why in YOR:
    RELATIONS.append(dict(frm="@ethnic_groups:yoruba", type="present_in", to=ON(l), source=src, evidence="multiple_sources" if lvl == "well_documented" else "single_reliable_source",
                          level=lvl, settlement_status="unknown", notes=f"Yoruba: {why}.{PO}"))

GAPS = [
    ("Ondo: Arigidi outside Akoko North-West", "The Atlas's head line adds 'Kwara State, Kogi LGA', and Wikipedia (citing Ethnologue) adds Akoko North-East, Ekiti East and Ijumu, but every member the Atlas lists is in a town of Akoko North-West. Only Akoko North-West is linked."),
    ("Ondo: Ahan in Ekiti", "The Atlas places Ahan in 'Ondo State, Ekiti LGA' with Omou town; Omuo is now in Ekiti State (Ekiti East LGA, per Wikipedia citing Ethnologue). Ekiti State is linked as reported; the LGA is left for the Ekiti batch."),
    ("Ondo: Uhami in Owo", "The Atlas names Owo LGA as well as Akoko South; no current source places Uhami in today's Owo LGA (reported link)."),
    ("Ondo: Ịzọn and the Ikale area", "The Atlas places Ịzọn in 'Ikale and Ilaje Ese–Odo LGAs'. Wikipedia (Ilaje) places the Apoi and Arogbo Ijaw in Ese Odo, not Ilaje; whether Ịzọn is spoken in the Ikale LGAs (Irele, Okitipupa) is not established."),
    ("Ondo: the Apoi", "The federal profile names the Apoi beside the Ijaw; Wikipedia calls them an Ijaw sub-group, Wikipedia (Ilaje) 'Yoruba speaking', and the Atlas lists Ìjọ̀–Àpọ̀ì as a Yoruba dialect. They are described in the Ijaw notes, not as a separate record."),
    ("Ondo: Ehuẹun and Ukue", "Wikipedia notes that the two are sometimes considered one language; both are kept, as the Atlas and Glottolog keep them."),
    ("Ondo: Yoruba sub-groups", "The Akoko, Akure, Ikale, Ilaje, Ondo, Owo, Idanre and Ose are recorded in the Yoruba link notes, as in the other south-western states. Wikipedia's Akoko article also calls the Akoko 'a large Edo cultural sub-group'; the federal profile calls them Yoruba."),
    ("Ondo: sub-groups of five LGAs", "No source read names the sub-group of Akure North, Akure South, Ondo East, Ondo West or Ifedore; the links are reported."),
    ("Ondo: Ijaw elsewhere", "The Ijaw are linked to Bayelsa, Delta and Rivers at state level only (Wikipedia, reported); their LGAs are left for those states."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Ondo (Phase 3): Ijoid branch, nine Atlas languages of Ondo with LGA links, the Ijaw people; Yoruba linked to all 18 LGAs with sub-groups in the notes.")


def report():
    yl = [r for r in RELATIONS if r["frm"] == "@ethnic_groups:yoruba" and "lga:" in r["to"]]
    sl = [r for r in RELATIONS if r["type"] == "spoken_in" and "lga:ondo" in r["to"]]
    L_ = ["# Research batch 142 — Ondo: languages and peoples", "",
          f"Researched {ACCESSED}. Phase 3, the first Ondo batch. Created in review; published only after your approval.", "",
          "## Summary", "",
          "- **Languages:** the Atlas names ten languages in Ondo. **Yoruba** is already linked. The other nine are new records, most of them small languages of the Akoko hills:",
          "  - **Arigidi** (North Akoko, ten town varieties) and **Ahan**: Akoko North-West",
          "  - **Akpes** (Àbèsàbèsì): Akoko North-West and North-East",
          "  - **Ukaan**: Akoko North-East, and Akoko-Edo in Edo State",
          "  - **Ehuẹun** and **Ukue** (Epinmi) and **Uhami** (Isua): Akoko South-East, Edoid",
          "  - **Iyayu**: one quarter of Idoani, Ose LGA, Edoid",
          "  - **Ịzọn**: Ese Odo, under a new **Ijoid** branch",
          f"  - {len(sl)} language–LGA links. The Atlas uses the LGAs from before 1996; each town was placed in today's LGA from Wikipedia's town lists or INEC's ward directory.",
          "- **Peoples:** one new record, the **Ijaw** (own name Izon). The federal profile names the Ijaw and Apoi as Ondo's minorities; Wikipedia places the Apoi and Arogbo Ijaw in Ese Odo and the Furupagha Ijaw in Odigbo.",
          f"- **Yoruba linked to all 18 LGAs ({len(yl)} links):**",
          "  - **Akoko** in the four Akoko LGAs; **Ikale** in Okitipupa and Irele; **Ilaje**; **Ondo lineage** in Odigbo; **Owo**; **Idanre**; **Ose**; Ile-Oluji/Okeigbo: *well documented*",
          "  - Akure South, Ondo West, Akure North, Ondo East and Ifedore: *reported*. No source read names their people.",
          "  - Ese Odo: *reported*. Wikipedia's Ondo State page lists 'Ese Odo' among the Yoruba sub-groups, but its Ese Odo page calls the LGA Ijaw. Both statements are in the note.",
          "- **Decisions for you:**",
          "  - **Record name: Ijaw, with Izon as the own name.** Wikipedia explains that 'Ijaw' came from a mispronunciation of Izon. Following your keep-both-names rule, the record could instead be named **Izon**. I kept **Ijaw** because the federal profile uses it for the whole people, and some Ijaw clans do not speak Izon. Say if you want it switched.",
          "  - **Yoruba sub-groups** (Akoko, Ikale, Ilaje and others) are still in the link notes rather than separate records. This is the open south-western decision.", "",
          "## The new records", ""] + [f"**{(NAME.get(k) or k.capitalize())}.** {TEXT[k]}\n" for k in TEXT]
    L_ += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_142_ondo_languages_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_142_ondo_languages_peoples_REVIEW.md", "w").write(report())
    yl = [r for r in RELATIONS if r["frm"] == "@ethnic_groups:yoruba" and "lga:" in r["to"]]
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} yoruba-LGA={len(yl)} LGAs={len({r['to'] for r in yl})}")
