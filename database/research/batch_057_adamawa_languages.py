"""
Research batch 057 — Adamawa (Phase 3, first batch): the languages of Adamawa State and the LGAs where
Roger Blench's Atlas of Nigerian Languages (2020) places them. Researched 2026-09-30.
Peoples (ethnic groups) are a separate batch, as for Plateau and Kogi.

Method: all 491 Atlas entries (column-split, atlas_extract.py) were searched for 'Adamawa State' and the
21 Adamawa LGA names (and the older 'Mubi', 'Yola' and 'Shellen' spellings), excluding the family name
'Adamawa–Ubangi'. 69 entries were kept (data/atlas_adamawa_blocks.txt) and read by hand. False matches
removed after reading: Gwara (Kaduna), Labɨr (Bauchi), the Numbu–Gbantu cluster (Kaduna/Nasarawa),
Koenoem (Plateau; its block contains the separate entry 251 Kofa, which IS recorded) and the Kororofa
and Kuteb blocks (their Adamawa mentions are Kotopo and Kutin, now spoken only in Cameroon).
Names are typed by the Atlas's key (1.A spelling, 1.B own name for the language, 2.A location name,
2.B other name); own names of peoples (1.C, 2.C) are kept for the peoples batch.

Boundaries: Adamawa and Taraba were one state (Gongola) until 1991, and several Atlas entries write
'Taraba State' for LGAs that are in Adamawa (Ganye, Mayo Belwa, Fufore). The LGA names are
unambiguous, so these are linked, with the Atlas's wording in the note. 'Mubi LGA' and 'Yola LGA'
have since been divided (Mubi North/South, Yola North/South): state link only, with a note.

Glottocodes: matched by name (or the Atlas's other names) in Glottolog's CLDF table, each map point
checked to lie in or near Adamawa. Where Glottolog's name differs the text says so. No code: Mukta.
Fulfulde uses Glottolog's Fula family code (fula1264), since the Atlas entry covers all of Fulfulde.
"""
import json, sys

ACCESSED = "2026-09-30"
SOURCES = {
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf",
                  verification_status="verified", notes="Reused. Adamawa entries read in full (data/atlas_adamawa_blocks.txt)."),
    "GLIDX": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Glottolog language index (resourcemap)", organisation="Glottolog",
                  url="https://glottolog.org/resourcemap.json?rsc=language", verification_status="verified", notes="Reused."),
}
BR = {  # new branches
    "biu-mandara": ("Biu–Mandara", "bium1280", "Biu–Mandara, also called Central Chadic, is a branch of the Chadic languages spoken in north-eastern Nigeria, northern Cameroon and Chad. Roger Blench's Atlas of Nigerian Languages classifies most of the Chadic languages of Adamawa State, among them Bata, Huba, Kamwe, Margi and Sakun (Sukur), as Biu–Mandara, and Glottolog lists Biu-Mandara (bium1280) as a family."),
}
LGA = {"demsa": "Demsa", "fufore": "Fufore", "ganye": "Ganye", "girei": "Girei", "gombi": "Gombi", "guyuk": "Guyuk", "hong": "Hong", "jada": "Jada",
       "lamurde": "Lamurde", "madagali": "Madagali", "maiha": "Maiha", "mayo-belwa": "Mayo Belwa", "michika": "Michika", "mubi-north": "Mubi North",
       "mubi-south": "Mubi South", "numan": "Numan", "shelleng": "Shelleng", "song": "Song", "toungo": "Toungo", "yola-north": "Yola North", "yola-south": "Yola South"}
S, B, L = "spelling_variant", "endonym", "alternative"
BM = "br_biu-mandara"
MUBI = "The Atlas also names Mubi LGA, since divided into Mubi North and Mubi South."
YOLA = "The Atlas also names Yola LGA, since divided into Yola North and Yola South."
MANDARA = "Chadic (Biu–Mandara A: Mandara/Mafa/Sukur major group, Mandara group)"
YENDANG = "Adamawa (Mumuye–Yendang group, Yendang subgroup)"
# key: (name, parent, class text, [(name, type, field)], [lgas], place/other-state note, speakers, extra, glottocode)
LANG = {
 "baa": ("Baa", "adamawa", "Adamawa (Kwa group)", [("nyaa Báà", B, "1.B"), ("Kwa", L, "2.A")], ["numan"],
         "It is spoken at Gyakan and Kwa towns, after Munga.", "1,000 (SIL 1973)", "", "kwaa1262"),
 "bwatye": ("Bwatye", BM, "Chadic (Biu–Mandara A: Bata group, Bata cluster)",
            [("Gboare", S, "1.A"), ("Bwatiye", S, "1.A"), ("Kwaa–Ɓwaare", B, "1.B"), ("Bacama", L, "GL")], ["numan", "guyuk"],
            "It is also spoken north-east of Kaduna town in Kaduna State, and Bacama fishermen migrate long distances down the Benue, with camps as far as the confluence.",
            "11,250 (1952); 20,000 (1963)",
            "Its speakers are known as Bachama (Bacama). Its dialects are Mulyen (Mwulyin), Dong, Opalo and Wa-Duku. An orthography appeared in 1987, and Mark in 1915. Glottolog lists it as Bacama.", "baca1246"),
 "bata": ("Bata", BM, "Chadic (Biu–Mandara A: Bata group, Bata cluster)", [("Batta", S, "1.A"), ("Gbwata", S, "1.A")], ["numan", "song", "fufore"],
          MUBI + " It is also spoken in Cameroon.", "26,400 (1952), with an estimated 2,000 in Cameroon; 39,000 in total (Welmers 1971)",
          "Its dialects include Koboci, Wadi, Zumu (Jimo), Malabu, Bata of Ribaw, Bata of Demsa, Bata of Garoua and Jirai.", "bata1314"),
 "boga": ("Boga", BM, "Chadic (Biu–Mandara A: Tera group, Eastern cluster)", [("Boka", S, "1.A")], ["gombi"],
          "It is spoken in five villages.", "", "", "boga1251"),
 "bena": ("Ɓena", "adamawa", "Adamawa (Yungur group)",
          [("Ebina", S, "1.A"), ("Binna", S, "1.A"), ("Gbinna", S, "1.A"), ("Ebəna", B, "1.B"), ("Purra", L, "2.A (northern Ɓena)"), ("Yungur", L, "2.B"), ("Yangur", L, "2.B")],
          ["song", "guyuk"],
          "Purra is a general term for the northern Ɓena; the Atlas does not recommend the name Lala for them.",
          "44,300 (1963), probably including Lala and Roba; fewer than 100,000 (1990 estimate)",
          "The Ɓena are divided into seventeen clans, each said to have its own speech form, although these are too close to be called dialects. Ɓəna is also used as their own name by the Lala, Roba and Voro. Glottolog lists it as Bena (Nigeria).", "bena1260"),
 "bile": ("Ɓile", "jarawan", "Bantu (Jarawan)", [("Bille", S, "1.A"), ("Bili", S, "1.A"), ("Bilanci", S, "1.A"), ("Kun–Ɓíilé", B, "1.B")], ["numan"],
          "It is spoken 25 km south of Numan, east of the Wukari road, in 36 villages reported to be wholly Ɓile-speaking and 16 more where some Ɓile is spoken.",
          "30,000 (CAPRO 1992)",
          "Kun–Ɓíilé is said to be mutually intelligible with Mbula. Hausa, Fulfulde and English are widely used as second languages; Ɓile is still widely used, although young people often switch between it and Hausa. There are occasional television and radio broadcasts from Yola.", "bile1244"),
 "daba": ("Daba", BM, "Central Chadic (West Central group: Daba group)", [], [],
          "It is spoken in a single village between Mubi and Bahuli, in Mubi LGA (since divided into Mubi North and Mubi South); most of its speakers live in Cameroon.",
          "fewer than 1,000 in Nigeria", "Scripture portions have appeared since 1984, and a New Testament translation is under way.", "nucl1683"),
 "dera": ("Dera", "west-chadic", "West Chadic (A: Bole–Ngas major group, Bole group)", [("Bo Dera", B, "1.B"), ("Kanakuru", L, "2.A")], ["shelleng"],
          "The Atlas writes 'Shellen LGA'. It is also spoken in Shani LGA of Borno State.", "11,300 (Westermann & Bryan)",
          "Its dialects are Shani, Shellen and Gasi. Mark and Scripture portions appeared in 1937. Glottolog lists it as Dera (Nigeria).", "dera1248"),
 "dijim-bwilim": ("Dijim–Bwilim", "adamawa", "Adamawa (Waja group)",
          [("Cham", L, "2.A (Dijim)"), ("Cam", L, "2.A (Dijim)"), ("Kindiyo", L, "2.A (Dijim)"), ("Mwana", L, "2.A (Bwilim)"), ("Mwona", L, "2.A (Bwilim; Hausa name)"), ("Fitilai", L, "2.A (Bwilim; village name)")],
          ["lamurde"], "It is also spoken in Balanga LGA of Gombe State, in about twenty villages in all.", "7,545 (1968): Cham 3,257 and Bwilim 4,282",
          "It has two members, Dijim and Bwilim. Its orthography is based on Dijim; a reading and writing book appeared in 2006, and the Gospel of Luke is ready for printing.", "diji1241"),
 "fali-mubi": ("Fali of Mubi", BM, "Chadic (Biu–Mandara A: Bata group)", [("Fali of Muchella", L, "2.A")], [],
          "It is spoken in Mubi LGA (since divided into Mubi North and Mubi South), in four principal villages: Vimtim, north of Mubi, and Bahuli, Muchella and Bagira, north-east of Mubi.",
          "more than 20,000 (1990 estimate)",
          "It is a cluster of four members named after these towns: Vin, Huli, Madzarin and Ɓween. Glottolog lists it as Fali, with Fali of Mubi, Fali of Mucella and Fali of Vimtim as dialects.", "fali1285"),
 "fulfulde": ("Fulfulde", None, "Atlantic (Northern branch, Senegal group)", [("Fillanci", L, "2.B"), ("Filatanci", L, "2.B"), ("Fula", L, "2.B")], [],
          "The Atlas describes it as scattered throughout Nigeria and spoken in other countries of West-Central Africa, and names three main dialects in Nigeria: Central (Kano–Katsina–Bauchi–Borno), East (Adamawa) and West (Sokoto). Its Adamawa entries name Fulfulde as a second language of the Ɓile, and as the language that has replaced Holma.",
          "3,000,000 (1952)",
          "Its speakers are the Fulɓe (singular Pullo), known in Nigeria as Fulani. It has an official orthography and a newspaper; the New Testament appeared in 1964 and 1968 and the complete Bible in 1983 (in Cameroon). Glottolog treats Fula as a family (fula1264), within which it lists Adamawa Fulfulde.", "fula1264"),
 "gaa": ("Gaa", "dakoid", "Benue–Congo (Northern Bantoid: Dakoid)", [("Tiba", L, "2.A"), ("Təbaya", L, "2.A")], ["ganye"],
         "It is spoken on the Tiba Plateau.", "fewer than 5,000 (Blench 1987)", "", "gaaa1245"),
 "ga-anda": ("Ga'anda", BM, "Chadic (Biu–Mandara A: Tera group)", [("Mokar", L, "2.B")], ["gombi"], "", "7,600 (1952); 10,000 (SIL 1973)",
         "It is a cluster of three members: Ga'anda (six villages), Kaɓən or Gabin (twelve villages) and Fərtata (five villages). The Atlas explains the name Mokar as that of the place where 'the rolling pot stopped'.", "gaan1243"),
 "gudu": ("Gudu", BM, "Chadic (Biu–Mandara A: Bata group)", [("Gutu", S, "1.A"), ("Gudo", S, "1.A")], ["song"],
          "It is spoken 120 km west of Song, in about five villages.", "1,200 (1971)", "", "gudu1250"),
 "gude": ("Guɗe", BM, "Chadic (Biu–Mandara A: Bata group)",
          [("Gude", S, "1.A"), ("Goudé", S, "1.A"), ("Mubi", L, "2.A"), ("Cheke", L, "2.B"), ("Tcheke", L, "2.B"), ("Mapuda", L, "2.B"), ("Shede", L, "2.B"), ("Mudaye", L, "2.B")], [],
          "It is spoken in Mubi LGA (since divided into Mubi North and Mubi South), in Askira–Uba LGA of Borno State, and in Cameroon.",
          "28,000 (1952), with an estimated 20,000 in Cameroon",
          "Three primers appeared in 1974 and folk tales in 1973, and a literacy programme is under way; Mark appeared in 1974, and Bible translation is in progress.", "gude1246"),
 "gvoko": ("Gvoko", BM, MANDARA, [("Gəvoko", S, "1.A"), ("Ngoshe Ndaghang", L, "2.A"), ("Ngweshe Ndhang", L, "2.A"), ("Nggweshe", L, "2.A")], ["michika"],
           "It is also spoken in Gwoza LGA of Borno State.", "2,500 (1963); 4,300 (SIL 1973); more than 20,000 (1990 estimate)", "", "gvok1239"),
 "hdi": ("Hdi", BM, MANDARA,
         [("Hidé", S, "1.A"), ("Hide", S, "1.A"), ("Xide", S, "1.A"), ("Xədi", B, "1.B"), ("Gra", L, "2.A"), ("Tur", L, "2.A"), ("Turu", L, "2.A"), ("Tourou", L, "2.A"), ("Ftour", L, "2.A")],
         ["michika"], "It is also spoken in Gwoza LGA of Borno State and in Cameroon.", "", "", "hdii1240"),
 "holma": ("Holma", BM, "Chadic (Biu–Mandara A: Bata group)", [("Da Holmaci", B, "1.B")], [],
           "It was spoken north of Sorau, on the Cameroon border; the Atlas gives no LGA.", "4 (Blench 1987)",
           "The language has almost vanished and been replaced by Fulfulde, and the Atlas records it as probably extinct (1987).", "holm1250"),
 "huba": ("Huba", BM, "Chadic (Biu–Mandara A: Bura–Higi major group, Bura group)", [("Həba", S, "1.A"), ("Chobba", L, "2.A"), ("Kilba", L, "2.A")],
          ["hong", "maiha", "gombi"], MUBI, "32,000 (1952); 100,000 (UBS 1980)",
          "Luwa is a dialect. A literacy programme and Bible translation are under way; Mark appeared in 1976.", "huba1236"),
 "hwana": ("Hwana", BM, "Chadic (Biu–Mandara A: Tera group)", [("Hona", S, "1.A"), ("Hwona", S, "1.A")], ["gombi"],
           "It is spoken at Guyuk and thirty other villages, which the Atlas places in Gombi LGA.", "6,604 (1952); 20,000 (SIL 1973); more than 20,000 (Blench 1987 estimate)", "", "hwan1240"),
 "kaan": ("Kaan", "adamawa", "Adamawa (Yungur group)", [("Libo", L, "2.A")], ["guyuk"], "", "", "", "kaan1247"),
 "kamwe": ("Kamwe", BM, "Chadic (Biu–Mandara A: Bura–Higi major group, Higi group)", [("Vəcəmwe", B, "1.B")], ["michika"],
           "It extends into Cameroon.", "64,000 (1952); 180,000 (SIL 1973), with an estimated 23,000 in Cameroon",
           "Its speakers are also called Higi, Hiji or Kapsiki. Its dialects are Nkafa, Dakwa (Bazza), Səna, Wula, Futu, Tili Pte and, in Cameroon, Kapsiki (Ptsəkɛ). Folk tales and a reading book appeared in 1970 and the New Testament in 1975.", "kamw1239"),
 "kirya-konzel": ("Kirya-Konzəl", BM, "Chadic (Biu–Mandara A: Bura–Higi major group, Higi group)", [], ["michika"], "",
           "7,000 for Kirya (13 villages) and 9,000 for Konzəl (15 villages) (2007 estimates)",
           "It has two members, Kirya and Konzəl, whose speakers are also called Fali (Fali of Kiriya and Fali of Mijilu). A dictionary (2007) and a grammar (2009) have been published. Glottolog lists it as Kirya-Konzel.", "kiry1234"),
 "kofa": ("Kofa", BM, "Chadic (Bura group)", [("Kota", L, "IX")], ["song"],
          "It is spoken north of the Belel road.", "",
          "The Atlas notes that its linguistic status is not certain, but that it is locally said to be a separate language.", "kofa1236"),
 "koma": ("Koma", "adamawa", "Adamawa (Vere group)", [("Kuma", S, "1.A")], ["ganye", "fufore"],
          "It is spoken in the Alantika Mountains, and also in Cameroon, where most of its speakers live.", "3,000 (SIL 1982)",
          "Koma is a Fulfulde cover term for a cluster of closely related languages, Gomme (also Damti or Koma Kampana), Gomnome (also Koma Kadam, Mbeya or Gimbe) and Ndera (also Vomni or Doome), which the Cameroon language atlas (ALCAM) treats as separate languages; the Atlas notes that the correspondence between the Nigerian and Cameroonian names is uncertain. Glottolog lists Koma Alantika as a family.", "koma1268"),
 "kpasam": ("Kpasam", "adamawa", YENDANG, [("Passam", S, "1.A"), ("Kpasham", S, "1.A"), ("Nyisam", L, "2.B")], ["numan"],
            "The Atlas places it in a single village 'south of Jalingo', in Numan LGA of Adamawa State. Jalingo is in Taraba State, and Glottolog's map point for Kpasam also lies in Taraba, so its present state needs confirmation.",
            "", "Glottolog groups it with Bali as Bali-Kpasam.", "kpas1242"),
 "kugama-gengle": ("Kugama-Gengle", "adamawa", YENDANG, [("Kugamma", S, "1.A"), ("Gengle", S, "1.A"), ("Wegam", L, "2.A"), ("Wegele", L, "2.B")], ["fufore"],
            "The Atlas describes the number of speakers as small.", "", "Glottolog lists it as Gengle-Kugama.", "kuga1239"),
 "kumba": ("Kumba", "adamawa", YENDANG, [("Sate", L, "2.A"), ("Yofo", L, "2.A")], ["mayo-belwa"], "", "", "", "kumb1238"),
 "lala": ("Lala", "adamawa", "Adamawa (Yungur group)", [("Lalla", L, "2.B (Yang)"), ("Gworam", L, "2.A (Roba)")], ["guyuk", "song", "gombi"], "",
          "30,000 (SIL); 44,300 together with Ɓəna (1963)",
          "It is a cluster of Yang, Roba (also called Gworam) and Ebode. The Atlas notes that 'Lala' is also used as a cover term for Ɓena, Roba and other groups in Guyuk, Gombi and Song LGAs, not all of which are clearly defined. Glottolog lists it as Lala-Roba.", "lala1261"),
 "lamang": ("Lamang", BM, MANDARA, [("Laamang", S, "1.A"), ("Waha", L, "2.A")], ["michika"],
            "It is spoken mainly in Gwoza LGA of Borno State; its Central (Ghumbagha) and South (Ghudavan) members extend into Michika LGA, and Lamang South into Cameroon.",
            "40,000 (1963); 15,000 (1970)",
            "It is a cluster of Zaladva (Lamang North), Ghumbagha (Lamang Central) and Ghudavan (Lamang South). Mark was drafted in 1991, and Bible translation is in progress.", "lama1288"),
 "longuda": ("Longuda", "adamawa", "Adamawa (Longuda group)",
            [("Languda", S, "1.A"), ("Nunguda", S, "1.A"), ("Nungura", S, "1.A"), ("Nunguraba", S, "1.A"), ("nyà núngúrá", B, "1.B (Guyuk)")], ["guyuk"],
            "It is also spoken in Balanga LGA of Gombe State.", "13,700 (1952, Numan Division); 32,000 (SIL 1973)",
            "Its dialects are Nya Guyuwa (Guyuk plains), Nya Ceriya (Banjiram), Nya Tariya (Kola), Nya Dele (Jessu) and Nya Gwanda (Nyuar). Mark appeared in 1954 and 1975, a primer and folk tales in 1975, and the New Testament in 1979.", "long1389"),
 "margi": ("Margi", BM, "Chadic (Biu–Mandara A: Bura–Higi major group, Bura group)", [("Marghi", S, "1.A"), ("Margyi", S, "1.A"), ("Màrgí", B, "1.B")],
           ["madagali", "michika"], MUBI + " It is also spoken in Askira–Uba and Damboa LGAs of Borno State.",
           "135,000 (1955) and 200,000 (UBS 1987), both for Margi, Margi South and Putai together",
           "Its Central dialects include Margi babal ('Margi of the Plain', around Lasa) and Margi Dzərŋu (around Gulak), with Gwàrà, Mə̀lgwí and Wúrgà. A primer appeared in 1941, Scripture portions from 1940 and the New Testament in 1984. Glottolog lists it as Marghi Central.", "marg1265"),
 "margi-south": ("Margi South", BM, "Chadic (Biu–Mandara A: Bura–Higi major group, Bura group)", [], ["michika"],
           MUBI + " It is also spoken in Askira–Uba LGA of Borno State.",
           "135,000 (1955), for Margi, Margi South and Putai together",
           "Its dialects are Wamdiu and Hildi. The Atlas counts it as a separate language, more closely related to Huba. Glottolog lists it as Marghi South.", "marg1266"),
 "mboi": ("Mboi", "adamawa", "Adamawa (Yungur group)", [("Mboire", S, "1.A"), ("Mboyi", S, "1.A")], ["song"], "", "3,200 (SIL 1973)",
          "It is a cluster of Gana (north-west of Song, at Livo village; 1,800 in 1971), Banga (west of Loko) and Haanda (west of Loko; 1,370 in 1971).", "mboi1246"),
 "mbula": ("Mbula", "jarawan", "Bantu (Jarawan)", [("Ɓwà Ɓwàzà", B, "1.B (Bwazza)"), ("Bare", L, "2.A (Bwazza; town name)"), ("Bere", L, "2.A (Bwazza; town name)")],
           ["numan", "shelleng", "song", "demsa"],
           "The Atlas places the cluster in Numan, Shelleng and Song LGAs, and its Bwazza member in Demsa, Numan, Shelleng and Song LGAs, in twenty-six villages.",
           "7,900 (1952); 25,000 (Barrett 1972); 23,447 (1977); the Atlas is not sure whether these cover Mbula alone or Mbula and Bwazza",
           "It is a cluster of Mbula, Tambo and Bwazza. There are radio broadcasts in Mbula and radio and television broadcasts in Tambo; Bwazza has a reading and writing book (2007), and the Gospel of Luke is ready for printing. Glottolog lists it as Mbula-Bwazza.", "mbul1260"),
 "mom-jango": ("Mom Jango", "adamawa", "Adamawa (Vere group)", [("Vere", L, "2.A"), ("Were", L, "2.A"), ("Verre", L, "2.A"), ("Kobo", L, "2.A (in Cameroon)")], ["fufore"],
           "", "20,000 in total, including Momi, with 4,000 in Cameroon (SIL 1982)", "The Atlas's name Vere covers both Mom Jango and Momi.", "momj1237"),
 "momi": ("Momi", "adamawa", "Adamawa (Vere group)", [("Ziri", B, "1.B"), ("Vere", L, "2.A"), ("Were", L, "2.A"), ("Verre", L, "2.A"), ("Kobo", L, "2.A (in Cameroon)")], ["fufore"],
          YOLA + " It is also spoken in Cameroon.", "20,000 in total, including Mom Jango, with 4,000 in Cameroon (SIL 1982)",
          "A dictionary was published in 2016. Glottolog lists it as Vere Kaadam, with Momi Yadim and Momi Bati among its dialects.", "vere1252"),
 "mukta": ("Mukta", BM, "Central Chadic (Kamwe cluster)", [], [],
           "The Atlas places it at Mukta village in Adamawa State, without naming an LGA.", "",
           "It forms a dialect cluster with Hya in Cameroon. No separate Glottolog entry was found.", None),
 "ngwaba": ("Ngwaba", BM, "Chadic (Biu–Mandara A: Bata group)", [], ["gombi"], "It is spoken at Fachi and Gudumiya.", "fewer than 1,000",
            "Its speakers are also called Gombi or Goba.", "ngwa1251"),
 "nyong": ("Nyong", "adamawa", "Adamawa (Leko group)", [("Nyɔŋ", S, "1.A"), ("Nyɔŋ Nyanga", B, "1.B"), ("Mumbake", L, "2.A"), ("Mubako", L, "2.A")], ["mayo-belwa"],
           "It is spoken west of Mayo Belwa town, at Bingkola and five other villages.", "10,000 (SIL)", "", "nyon1241"),
 "nzanyi": ("Nzanyi", BM, "Chadic (Biu–Mandara A: Bata group)",
            [("Njanyi", S, "1.A"), ("Njai", S, "1.A"), ("Njei", S, "1.A"), ("Zany", S, "1.A"), ("Nzangi", S, "1.A"), ("Zani", S, "1.A"), ("Wur Nzanyi", B, "1.B"),
             ("Jenge", L, "2.A"), ("Mzangyim", L, "2.A"), ("Kobochi", L, "2.A")], ["maiha"],
            "It is also spoken in Cameroon, west of Dourbeye near the Nigerian border (Mayo-Oulo subdivision).", "14,000 in Nigeria (1952); 9,000 in Cameroon",
            "Its dialects are Paka, Rogede, Nggwoli, Hoode, Maiha, Magara, Dede and Mutidi, and Lovi in Cameroon.", "nzan1240"),
 "pere": ("Pere", "adamawa", "Adamawa (Leko group)", [("Perema", B, "1.B"), ("Wom", L, "2.A (town name)")], ["fufore"],
          "It is spoken in ten villages around Yadim.", "fewer than 4,000",
          "The Atlas gives Kutin as the same language, formerly spoken in Ganye LGA and now only in Cameroon. Glottolog lists it as Peere.", "peer1241"),
 "sakun": ("Sakun", BM, "Chadic (Biu–Mandara A: Mandara/Mafa/Sukur major group, Sukur group)",
           [("Gemasakun", B, "1.B"), ("Sugur", L, "2.A"), ("Adikummu Sukur", L, "2.B")], ["madagali"],
           "It is spoken in seven villages. The Atlas writes 'Madgali LGA'.", "5,000 (1952); 10,000 (SIL 1973)", "Glottolog lists it as Sukur.", "suku1272"),
 "teme": ("Teme", "adamawa", YENDANG, [("Temme", S, "1.A")], ["mayo-belwa", "fufore"], "", "", "", "teme1252"),
 "tsobo": ("Tsobo", "adamawa", "Adamawa (Waja group)",
           [("Cibbo", S, "1.A"), ("Tsóbó", B, "1.B"), ("Lotsu–Piri", L, "2.A"), ("Pire", L, "2.A"), ("Fire", L, "2.A"), ("Kitta", L, "2.B")], ["numan"],
           "It is also spoken in Kaltungo LGA of Gombe State.", "2,000 (1952)", "Its dialects are Bərbou, Guzubo and Swabou. Glottolog lists it as Tso.", "tsoo1241"),
 "vemgo-mabas": ("Vemgo–Mabas", BM, MANDARA, [], ["michika"], "", "",
           "It has two members: Vemgo, also spoken in Gwoza LGA of Borno State and in Cameroon, and Mabas, a single village on the Nigeria–Cameroon frontier 10 km south-east of Madagali.", "vemg1240"),
 "voro": ("Voro", "adamawa", "Adamawa (Yungur group)", [("Vɔrɔ", S, "1.A"), ("Ebəna", B, "1.B"), ("Ebina", B, "1.B"), ("Woro", L, "2.A"), ("Yungur", L, "2.B")], ["song", "guyuk"],
          "It is spoken south of the Dumne road, at Waltande and associated hamlets.", "", "", "voro1240"),
 "waka": ("Waka", "adamawa", YENDANG, [], ["fufore", "mayo-belwa"], "", "", "", "waka1275"),
 "yoti": ("Yoti", "adamawa", YENDANG, [], ["numan"], "", "", "Glottolog lists it as Yotti.", "yott1234"),
 "zizilivakan": ("Zizilivəkan", BM, "Chadic (Biu–Mandara A: Bata group)", [("Zilivə", B, "1.B"), ("Fali of Jilbu", L, "2.A")], [],
           "It is spoken at Jilbu town in Mubi LGA (since divided into Mubi North and Mubi South), and in Cameroon.", "'a few hundred' in Cameroon", "", "zizi1238"),
}
REPORTED = {"kpasam"}  # location conflicts: state and LGA links at 'reported'
CAVEAT = "The Atlas may name the LGA as it was when its data were collected; Mubi and Yola have since been divided."
FIELD = {"1.A": "alternate spelling of the name", "1.B": "the speakers' own name for the language", "2.A": "name based on location", "2.B": "other name for the language",
         "GL": "Glottolog's name for the language", "IX": "cross-reference name in the Atlas index"}


def text(k):
    name, parent, cls, names, lgas, note, spk, extra, g = LANG[k]
    where = (f" in {', '.join(LGA[l] for l in lgas[:-1])}{' and ' if len(lgas) > 1 else ''}{LGA[lgas[-1]]} LGA{'s' if len(lgas) > 1 else ''} of Adamawa State") if lgas else " in Adamawa State"
    t = f"{name} is a language spoken{where}, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as {cls}."
    alt = [n for n, _, f in names if n != name and f != "GL"]
    if alt: t += f" Other names include {', '.join(alt[:6])}."
    if note: t += " " + note
    if spk: t += f" The Atlas cites speaker figures of {spk}."
    if extra: t += " " + extra
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for k, (name, g, desc) in BR.items():
    RECORDS.append(dict(key=f"br_{k}", table="languages", evidence="multiple_sources", level="well_documented",
                        fields=dict(lang_type="branch", name=name, slug=k, glottocode=g, summary=desc.split(". ")[0] + ".", description=desc),
                        srcs=[("ATLAS", f"{name}: classification"), ("GLIDX", f"{name} ({g})")]))
for k, (name, parent, cls, names, lgas, note, spk, extra, g) in LANG.items():
    f = dict(lang_type="language", name=name, slug=k, summary=text(k).split(". ")[0] + ".", description=text(k))
    if parent:
        f["parent_id"] = f"@key:{parent}" if parent.startswith("br_") else f"@languages:{parent}"
    if g:
        f["glottocode"] = g
    RECORDS.append(dict(key=k, table="languages", evidence="single_reliable_source", level="well_documented", fields=f,
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers")] + ([("GLIDX", f"Glottocode {g}")] if g else [])))
    lvl = "reported" if k in REPORTED else "well_documented"
    st_note = "Blench's Atlas."
    if k == "fulfulde":
        st_note = "Blench's Atlas: main Nigerian dialects include 'East: Adamawa'; its Adamawa entries name Fulfulde as a second language of the Ɓile (Numan LGA) and as the language that replaced Holma."
    if k in REPORTED:
        st_note = "Blench's Atlas: 'Adamawa State, Numan LGA, 1 village only, South of Jalingo'. Jalingo is in Taraba State and Glottolog's point lies there (reported)."
    RELATIONS.append(dict(frm=k, type="spoken_in", to="@admin_units:state:adamawa", source="ATLAS", evidence="single_reliable_source", level=lvl, notes=st_note))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=f"@admin_units:lga:adamawa/{l}", source="ATLAS", evidence="single_reliable_source", level=lvl,
                              notes=st_note if k in REPORTED else CAVEAT))
    for n, t, fld in names:
        if n == name:
            continue
        src = "GLIDX" if fld == "GL" else "ATLAS"
        usage = "Glottolog's name for the language." if fld == "GL" else f"Blench's Atlas (2020), field {fld}: {FIELD.get(fld[:3], FIELD.get(fld, 'head entry'))}."
        if fld == "IX":
            usage = "Blench's Atlas (2020): cross-reference in the Atlas index ('Kota = Kofa')."
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=usage, srcs=[src]))

# Existing records (Taraba batches) that get Adamawa links
TAR = "The Atlas writes 'Taraba State' for this LGA, which is in Adamawa State (the two were one state, Gongola, until 1991)."
EXISTING = [
    ("dadiya", ["lamurde"], "Atlas: 'Gombe State, Balanga LGA, Taraba State, Karim Lamido LGA and Adamawa State, Lamurde LGA'."),
    ("dong", ["mayo-belwa"], "Atlas: 'Taraba State, Zing and Mayo Belwa LGAs'. " + TAR),
    ("dza", ["numan"], "Atlas: 'Taraba State, Karim Lamido LGA and Adamawa State, Numan LGA. Along the Benue River.'"),
    ("joole", ["numan"], "Atlas: 'Taraba State, Karim Lamido LGA and Adamawa State, Numan LGA. Along the Benue River.'"),
    ("tha", ["numan"], "Atlas: 'Taraba State, Karim Lamido LGA and Adamawa State, Numan LGA'."),
    ("lamja-densa-tola", ["mayo-belwa"], "Atlas: 'Taraba State, Mayo Belwa LGAs'. " + TAR),
    ("mumuye", ["mayo-belwa"], "Atlas: 'Taraba State, Jalingo, Zing, Yorro and Mayo Belwa LGAs'. " + TAR),
    ("samba-daka", ["ganye", "mayo-belwa"], "Atlas: 'Taraba State, Ganye, Jalingo, Bali, Zing, and Mayo Belwa LGAs'. Ganye and Mayo Belwa are in Adamawa State (the two states were one, Gongola, until 1991)."),
    ("samba-leko", ["ganye", "fufore"], "Atlas: 'Taraba State, Ganye, Fufore, Wukari & Takum LGAs; mainly in Cameroon'. Ganye and Fufore are in Adamawa State (the two states were one, Gongola, until 1991)."),
    ("yendang", ["numan", "mayo-belwa"], "Atlas: 'Adamawa State, Numan, Mayo Belwa, and Karim Lamido LGAs'."),
    ("laka-lau", [], "Atlas: 'Taraba State, Karim Lamido LGA, at Lau; Yola LGA; and mainly in Cameroon'. Yola LGA has since been divided into Yola North and Yola South."),
]
for lang, lgas, note in EXISTING:
    RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to="@admin_units:state:adamawa", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
    for l in lgas:
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=f"@admin_units:lga:adamawa/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
RELATIONS.append(dict(frm="@ethnic_groups:fulani", type="speaks", to="fulfulde", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                      notes="Their language (Blench's Atlas: Fulfulde; the people Pullo, pl. Fulɓe, also called Fulani)."))

GAPS = [
    ("Adamawa: Mubi and Yola", "The Atlas names Mubi LGA (Bata, Daba, Fali of Mubi, Guɗe, Huba, Margi, Margi South, Zizilivəkan) and Yola LGA (Momi, Laka), since divided into Mubi North/South and Yola North/South. These are linked to the state only; the present LGAs need a current source."),
    ("Adamawa: LGAs with no language link", "Girei, Jada, Toungo, Mubi North, Mubi South, Yola North and Yola South have no Atlas language placed in them by name; Demsa only through the Bwazza member of Mbula. Chamba (Samba) and Fulfulde areas south of Yola need a current source."),
    ("Adamawa: Kpasam", "The Atlas places Kpasam in 'Adamawa State, Numan LGA, 1 village only, South of Jalingo'; Jalingo is in Taraba, and Glottolog's point for Kpasam also lies in Taraba. Linked at 'reported' until a source confirms the state."),
    ("Adamawa: Bali", "The Atlas writes 'Taraba State, Numan LGA, at Bali, a single village south of Jalingo'. Numan is in Adamawa but Bali is in Taraba; not linked to Adamawa."),
    ("Adamawa: Tera cluster", "The Atlas's head line says 'Gombe State, Gombi LGA, Kwami district'. Gombi is an Adamawa LGA, but Kwami is in Gombe State and no member is placed in Adamawa; not linked."),
    ("Adamawa: Thər", "An Atlas index line places Thər in Gombi LGA, north of Ga'anda, tentatively Chadic (Tera group), 'said not to be the same as Ga'anda'. It has no entry of its own; not recorded."),
    ("Adamawa: Jar cluster", "The Atlas's head line says 'Plateau, Bauchi and Adamawa States', but none of its members is placed in Adamawa; not linked."),
    ("Adamawa: languages now in Cameroon", "The Atlas records Kotopo and Kutin (Pere) as formerly spoken in Ganye LGA and now only in Cameroon (Kotopo since the creation of the Gashaka reserve in 1974). Not linked."),
    ("Adamawa: Holma and Mukta", "Holma (north of Sorau; probably extinct by 1987) and Mukta (Mukta village) are placed in Adamawa State without an LGA."),
    ("Adamawa: Hausa", "Named in the Atlas as a second language (Ɓile) but not yet a language record. Mukta has no Glottocode."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Adamawa languages (Blench Atlas, read in full): Biu–Mandara branch, {len(LANG)} languages, LGA links, other names; Adamawa links for {len(EXISTING)} existing languages; Fulani speak Fulfulde.")


def report():
    per_lga = {l: [] for l in LGA}
    for k, v in LANG.items():
        for l in v[4]:
            per_lga[l].append(v[0])
    for lang, lgas, _ in EXISTING:
        for l in lgas:
            per_lga[l].append(lang.replace("-", " ").title() + " (existing)")
    L = ["# Research batch 057 — Adamawa: the languages", "",
         f"Researched {ACCESSED}. Phase 3, the first Adamawa batch. Created in review; published only after your approval.", "",
         "## Summary", "",
         f"- **{len(LANG)} languages** and **1 new branch**, Biu–Mandara (Glottolog `bium1280`), all from Blench's Atlas.",
         "  - All 491 Atlas entries were searched for Adamawa State and its LGA names; 69 matched and every one was read by hand.",
         "  - Removed as false matches: Gwara, Labɨr, Numbu–Gbantu, Koenoem, Kororofa and Kuteb.",
         f"- **{sum(1 for v in LANG.values() if v[8])} Glottocodes.** Each was matched by name or by one of the Atlas's other names, and its map point checked. Only Mukta has none.",
         f"- **{len(RELATIONS)} links**, to the state and to LGAs, and **{len(NAMES)} other names**, typed by the Atlas's key.",
         f"- **{len(EXISTING)} existing languages get Adamawa links:** Dadiya, Dong, Dza, Joole, Tha, Lamja-Deŋsa-Tola, Mumuye, Samba Daka, Samba Leko, Yendang and Laka.",
         "- **The Fulani** people are now linked as speaking the new Fulfulde record.",
         "- **Boundaries:** Adamawa and Taraba were one state until 1991.",
         "  - Several entries write 'Taraba State' for Ganye, Mayo Belwa or Fufore, which are Adamawa LGAs. These are linked, quoting the Atlas.",
         "  - 'Mubi' and 'Yola' are now divided LGAs, so those entries are linked to the state only.",
         "- **One conflict:** Kpasam is placed in 'Numan LGA … south of Jalingo'. It is linked at *reported* level.", "",
         "## Languages per LGA (as the Atlas names them)", "", "| LGA | Count | Languages |", "|---|---|---|"]
    for l, ns in per_lga.items():
        L.append(f"| {LGA[l]} | {len(ns)} | {', '.join(ns) or '—'} |")
    L += ["", "## The languages", ""]
    for k in LANG:
        L += [f"**{LANG[k][0]}** ({LANG[k][8] or 'no Glottocode'}). {text(k)}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_057_adamawa_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_057_adamawa_languages_REVIEW.md", "w").write(report())
    print(f"languages={len(LANG)} branches={len(BR)} relations={len(RELATIONS)} names={len(NAMES)} glotto={sum(1 for v in LANG.values() if v[8])}")
