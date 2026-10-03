"""
Research batch 038 — Taraba (Phase 3, first batch): the languages of Taraba State and the LGAs
where Roger Blench's Atlas of Nigerian Languages (2020) places them. Researched 2026-09-26.
Peoples (ethnic groups) are a separate batch, as for Nasarawa (031 / 031b).

Method: the Atlas PDF (Internet Archive snapshot 2025-05-28) was split into columns with the new
reusable extractor `atlas_extract.py`; every entry naming Taraba (74) was read by hand
(data/atlas_taraba_blocks.txt). Only what each entry states is used. Names are typed by the
Atlas's key (1.A spelling, 1.B own name for the language, 2.A location name, 2.B other name) as in
batch 036; own names of peoples (1.C) are kept for the peoples batch.

Excluded after reading (they name Taraba only in passing or belong to other states): Koenoem, Longuda,
Tangale, Tinɔr-Myamya, Wuri-Gwamhyә–Mba, Zumbun, Nupe (a Nupe community at Ibi is mentioned — gap),
the Jar cluster except its Taraba member Ligri. Already in the archive: Tiv and Etulo (Taraba links
exist); Abinsi = the existing Wannu record (Glottolog's map point for Wannu is at Abinsi), which gets
its Taraba links here.

Caveats recorded on every LGA link:
  * The Atlas often names LGAs as they were when its data were gathered. Lau, Ussa, Donga, Kurmi,
    Ardo Kola, Yorro and others were created later, so 'Karim Lamido' may now be Lau, 'Takum' may
    now be Ussa or Donga, and so on.
  * Atlas errors not copied: 'Numan LGA' for Bali village (Numan is in Adamawa; the village is south
    of Jalingo — linked to the state only); Adamawa LGAs named under Taraba (Mayo Belwa, Ganye, Fufore,
    Yola) are mentioned in the text but not linked.
Glottocodes: matched by name in Glottolog's language index, with map points checked to lie in or near
Taraba; left blank where the names do not match cleanly (Buru, Dirim, Joole, Gbaya, Mambila,
Kulung (Chadic), the clusters).
"""
import json, re, sys

ACCESSED = "2026-09-26"
SOURCES = {
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf",
                  verification_status="verified", notes="Reused. Taraba entries read in full (data/atlas_taraba_blocks.txt)."),
    "GLIDX": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Glottolog language index (resourcemap)", organisation="Glottolog",
                  url="https://glottolog.org/resourcemap.json?rsc=language", verification_status="verified", notes="Reused."),
}
BR = {  # new branches
    "adamawa": ("Adamawa", "Adamawa is a large group of languages of north-eastern Nigeria, Cameroon and Chad, part of the Adamawa–Ubangi grouping of Niger-Congo. Roger Blench's Atlas of Nigerian Languages divides it into subgroups such as Mumuye–Yendang, Bikwin, Jen, Waja, Kam, Leko and Mbum, many of them spoken in Taraba State."),
    "mambiloid": ("Mambiloid", "Mambiloid is a group of Northern Bantoid languages spoken on and around the Mambila Plateau of Taraba State and in neighbouring Cameroon, according to Roger Blench's Atlas of Nigerian Languages. It includes Mambila, Ndoola, Vute, Mbɔŋnɔ and several small languages."),
    "dakoid": ("Dakoid", "Dakoid is a group of Northern Bantoid languages of Taraba and Adamawa states, according to Roger Blench's Atlas of Nigerian Languages. Its main member is Samba Daka (Chamba Daka); it also includes Dong, Dirim and Lamja-Deŋsa-Tola."),
    "beboid": ("Beboid", "Beboid is a group of South Bantoid languages spoken mainly in the Grassfields of Cameroon; Roger Blench's Atlas of Nigerian Languages lists a few small Beboid languages on the Nigerian side, near Takum in Taraba State."),
    "grassfields-bantu": ("Grassfields Bantu", "Grassfields (Grasslands) Bantu is a group of South Bantoid languages centred on the Grassfields of western Cameroon. Roger Blench's Atlas of Nigerian Languages records several of them on the Nigerian side of the border in Taraba State, among them Lamnso, Limbum and Yamba."),
    "gbaya": ("Gbaya", "Gbaya is a group of languages spoken mainly in Cameroon and the Central African Republic. Roger Blench's Atlas of Nigerian Languages records a small Gbaya community in Bali LGA of Taraba State, near the confluence of the Benue and Taraba rivers."),
}
LGA = {"ardo-kola": "Ardo Kola", "bali": "Bali", "donga": "Donga", "gashaka": "Gashaka", "gassol": "Gassol", "ibi": "Ibi", "jalingo": "Jalingo",
       "karim-lamido": "Karim Lamido", "kurmi": "Kurmi", "lau": "Lau", "sardauna": "Sardauna", "takum": "Takum", "ussa": "Ussa", "wukari": "Wukari",
       "yorro": "Yorro", "zing": "Zing"}
S, B, L, A = "spelling_variant", "endonym", "alternative", "alternative"
# key: (name, parent, class text, [(name, type, field)], [lgas], place/other-state note, speakers, extra, glottocode)
LANG = {
 "abon": ("Abon", "tivoid", "Southern Bantoid (Tivoid)", [("Abong", S, "1.A"), ("Abõ", B, "1.B")], ["sardauna"],
          "It is spoken only in Abong town, east of Baissa.", "", "", "abon1238"),
 "akum": ("Akum", "jukunoid", "Jukunoid", [], [], "The Atlas locates it at about 6°50′N, 9°50′E, in three villages in Nigeria, and in Cameroon.", "600 in Cameroon (1976)", "", "akum1238"),
 "ambo": ("Ambo", "tivoid", "possibly Tivoid (Southern Bantoid; no data)", [], ["sardauna"], "It is spoken in a single village east of Baissa.", "", "The Atlas marks its classification as uncertain.", "ambo1249"),
 "bali-taraba": ("Bali", "adamawa", "Adamawa (Mumuye–Yendang group; Yendang subgroup)", [("Ị̀báalí", B, "1.B")], [],
          "It is spoken in a single village, Bali, south of Jalingo; the Atlas names 'Numan LGA', which is in Adamawa State, so no LGA is linked.", "1,000 (SIL)", "", "bali1245"),
 "batu": ("Batu", "tivoid", "Southern Bantoid (Tivoid)", [], ["sardauna"],
          "It is spoken in several villages east of Baissa, below the Mambila escarpment.", "25,000 (SIL)", "It is a cluster whose members include Amanda–Afi, Angwe and Kamino, each named after its Batu village.", "batu1255"),
 "bete": ("Bete", "jukunoid", "Jukunoid (no data)", [], ["wukari"], "It is spoken in Bete town.", "", "The Atlas describes the language as dying out.", "bete1261"),
 "bitare": ("Bitare", "tivoid", "Southern Bantoid (Tivoid)", [("Njwande", L, "2.B"), ("Yukutare", L, "2.B")], ["sardauna"],
          "It is spoken near Baissa and in Cameroon.", "3,000 in Nigeria (SIL 1973); 3,700 in Cameroon (SIL 1987)", "", "bita1251"),
 "bukwen": ("Bukwen", "beboid", "South Bantoid (Beboid)", [], ["takum"], "It is spoken near Takum.", "", "", "bukw1238"),
 "buru": ("Buru", None, "South Bantoid (unclassified)", [], ["sardauna"], "It is spoken in a village near Batu, east of Baissa.", "", "The Atlas leaves its classification open within South Bantoid.", None),
 "como-karim": ("Como Karim", "jukunoid", "Jukunoid (Central Jukunoid; Wurbo cluster)",
          [("Shomoh", S, "1.A"), ("Shomong", S, "1.A"), ("Chomo", S, "1.A"), ("Shomo", S, "1.A"), ("Karim", L, "2.A"), ("Kirim", L, "2.A"), ("Kiyu", L, "2.B"), ("Nuadhu", L, "2.B")],
          ["karim-lamido", "jalingo"], "", "", "", "como1258"),
 "dadiya": ("Dadiya", "adamawa", "Adamawa (Waja group)", [("Nda Dia", S, "1.A"), ("Dadia", S, "1.A"), ("Bwe Daddiya", B, "1.B")], ["karim-lamido"],
          "It is also spoken in Balanga LGA of Gombe State and Lamurde LGA of Adamawa State, between Dadiya and Bambam.", "3,986 (1961); 20,000 (1992 estimate)", "", "dadi1249"),
 "dirim": ("Dirim", "dakoid", "Northern Bantoid (Dakoid)", [], ["bali"], "It is spoken in the Garba Chede area.", "9,000 (CAPRO 1992)",
          "The Atlas doubts whether it is really separate from Samba Daka.", None),
 "dong": ("Dong", "dakoid", "Dakoid", [], ["zing"], "It is spoken in at least six villages; the Atlas also names Mayo Belwa LGA (Adamawa State).", "about 20,000", "", "dong1293"),
 "dza": ("Dza", "adamawa", "Adamawa (Jen group)", [("Ja", S, "1.A"), ("nnwa' Dzâ", B, "1.B"), ("Jenjo", L, "2.A"), ("Janjo", L, "2.A"), ("Jen", L, "2.A")], ["karim-lamido"],
          "It is spoken along the Benue River, and also in Numan LGA of Adamawa State.", "6,100 (1952), a figure that may include other Jen groups such as Joole and Tha", "", "dzaa1238"),
 "dzodinka": ("Dzodinka", "grassfields-bantu", "Southern Bantoid (Grasslands Bantu: East)", [("Adiri", L, "2.A"), ("Adere", L, "2.A")], ["sardauna"],
          "It is spoken in a single village on the border, and in Cameroon.", "", "Mark (1923) and John (1932) were published in the language.", "dzod1238"),
 "etkywan": ("Etkywan", "jukunoid", "Jukunoid (Central Jukunoid; Kpan–Icen group)",
          [("Icen", S, "1.A"), ("Ichen", S, "1.A"), ("Itchen", S, "1.A"), ("Kentu", B, "1.B"), ("Kyanton", B, "1.B"), ("Nyidu", B, "1.B")], ["takum", "sardauna"],
          "", "6,330 in Donga district (1952); more than 7,000 (SIL 1973)", "", "etky1238"),
 "fam": ("Fam", "mambiloid", "Northern Bantoid (Mambiloid)", [], ["bali"], "It is spoken 17 km east of Kungana.", "under 1,000 (1984); under 500 (2016)", "", "famm1241"),
 "gbaya-taraba": ("Gbaya", "gbaya", "Gbaya (Niger-Congo)", [("Baya", S, "1.A")], ["bali"],
          "It is spoken near the confluence of the Benue and Taraba rivers, but mainly in Cameroon and the Central African Republic.", "200 in Nigeria (1965)", "", None),
 "jiru": ("Jiru", "jukunoid", "Jukunoid (Central Jukunoid; Wurbo cluster)", [("Zhiru", S, "1.A"), ("Atak", L, "2.B"), ("Wiyap", L, "2.B"), ("Kir", L, "2.B")], ["karim-lamido"], "", "", "", "jiru1238"),
 "joole": ("Joole", "adamawa", "Adamawa (Jen group)", [("èèʒìì", B, "1.B")], ["karim-lamido"], "It is spoken along the Benue River, and also in Numan LGA of Adamawa State.", "", "", None),
 "jibu": ("Jibu", "jukunoid", "Jukunoid (Central Jukunoid; Jukun cluster)", [], ["gashaka"], "", "25,000 (SIL 1987)",
          "It belongs to the Jukun cluster; its dialects include Gayam and Garbabi. Parts of the Bible have been translated.", "jibu1239"),
 "jukun-takum": ("Jukun Takum", "jukunoid", "Jukunoid (Central Jukunoid; Jukun cluster)", [("Takum-Donga", A, "Atlas head")], ["takum", "sardauna", "bali"],
          "", "40,000 second-language speakers only (UBS 1979)", "It belongs to the Jukun cluster; its dialects are Takum and Donga. The Atlas also gives Jibu as another name for it, although Jibu is a separate member of the cluster (Gashaka LGA). A Donga primer appeared in 1915.", "juku1254"),
 "kam": ("Kam", "adamawa", "Adamawa (Kam group)", [], ["bali"], "It is spoken only in Mayo Kam and Kamijim villages.", "583 (1922); more than 1,000 (1987 estimate)", "", "kamm1249"),
 "kapya": ("Kapya", "jukunoid", "Jukunoid (Yukuben–Kutep)", [], ["takum"], "It is spoken at Kapya.", "", "", "kapy1238"),
 "kholok": ("Kholok", "west-chadic", "West Chadic (Bole–Ngas group; Bole group)",
          [("Kode", L, "2.A"), ("Koode", L, "2.A"), ("Kwoode", L, "2.A"), ("Widala", L, "2.A"), ("Pitiko", L, "2.A")], ["karim-lamido"], "It is spoken near Didango.", "2,500 (1977)", "", "khol1240"),
 "hone": ("Hone", "jukunoid", "Jukunoid (Kororofa cluster)", [("Kona", L, "2.A")], ["karim-lamido"],
          "It is spoken in villages north and west of Jalingo, and also in Wase LGA of Plateau State.", "2,000 (1977)", "Mark was published in 1927.", "hone1235"),
 "wapan": ("Wapan", "jukunoid", "Jukunoid (Kororofa cluster)", [("Wapan", B, "1.B"), ("Wukari", L, "2.A")], ["wukari"],
          "It is also spoken in Awe, Lafia, Shendam and Langtang LGAs (Nasarawa and Plateau states; precise areas uncertain).", "60,000 (SIL 1973)",
          "It is the Jukun of Wukari. A primer appeared in 1915 and Scripture portions since 1914; a Bible translation is in progress.", "wapa1235"),
 "dampar": ("Dampar", "jukunoid", "Jukunoid (Kororofa cluster)", [], ["wukari"], "It is spoken at Dampar.", "", "", None),
 "kpan": ("Kpan", "jukunoid", "Jukunoid (Central Jukunoid; Kpan–Icen group)",
          [("Kpanten", S, "1.A"), ("Ikpan", S, "1.A"), ("Akpanzhi", S, "1.A"), ("Kpanzon", S, "1.A"), ("Abakan", S, "1.A"), ("Kpwate", L, "2.B"), ("Hwaye", L, "2.B"), ("Hwaso", L, "2.B"), ("Nyatso", L, "2.B"), ("Yorda", L, "2.B")],
          ["wukari", "takum", "sardauna"], "", "", "Its dialects fall into a western group (Kumbo, Takum, Donga and the extinct Bissaula) and an eastern group.", "kpan1246"),
 "kulung": ("Kulung", "jarawan", "Bantu (Jarawan)", [("Kúkùlúŋ", B, "1.B"), ("Bambur", L, "2.A"), ("Wurkum", L, "2.A")], ["karim-lamido", "wukari"],
          "It is spoken at Balasa, Bambur and Kirim (Karim Lamido LGA) and at Gada Mayo (Wukari LGA).", "15,000 (SIL)",
          "It is still passed on to children and learned by neighbours. Hausa is the main second language. Scripture portions appeared between 1926 and 1950.", "kulu1255"),
 "kulung-chadic": ("Kulung (Chadic)", "west-chadic", "West Chadic (Bole–Ngas group; Bole group)", [("Wurkum", L, "2.A")], ["karim-lamido"], "", "perhaps 2,000",
          "Its speakers consider themselves Kulung (whose language is Jarawan Bantu), although their own language is Chadic and related to Piya.", None),
 "kuteb": ("Kuteb", "jukunoid", "Jukunoid (Yukuben–Kutep)", [("Kutev", S, "1.A"), ("Kutep", S, "1.A"), ("Ati", L, "2.A"), ("Mbarike", L, "2.B"), ("Zumper", L, "2.B")], ["takum"],
          "It is also spoken in Furu-Awa subdivision, Cameroon.", "15,592 (1952); 30,000 (UBS 1986); 1,400 in Cameroon (1976)",
          "Its dialects are Lissam, Fikyu, Jenuwa, Kunabe and Kentin. The New Testament appeared in 1990.", "kute1248"),
 "kyak": ("Kyak", "adamawa", "Adamawa (Bikwin group)", [("Kyãk", B, "1.B"), ("Bambuka", L, "2.A")], ["karim-lamido"], "It is spoken at Bambuka.", "10,000 (SIL)", "", "kyak1243"),
 "laka-lau": ("Laka", "adamawa", "Adamawa (Mbum group)", [("Lau", L, "2.A"), ("Lao Habe", L, "2.A")], ["karim-lamido"],
          "It is spoken at Lau (now in Lau LGA), in Yola LGA of Adamawa State, and mainly in Cameroon.", "460 (1952); 500 (SIL 1973)", "", "laka1252"),
 "lamja-densa-tola": ("Lamja-Deŋsa-Tola", "dakoid", "Northern Bantoid (Dakoid)", [], [],
          "The Atlas names Mayo Belwa LGA (Adamawa State); the Lamja and Deŋsa live in 13 villages, with Ganglamja the central Lamja town.", "",
          "Its dialects are mutually intelligible and may not be distinct enough from Samba Daka to count as a separate language.", "lamj1245"),
 "lamnso": ("Lamnso", "grassfields-bantu", "Southern Bantoid (Grasslands Bantu)", [("Lam-Nsaw", S, "1.A"), ("Lam-Nsọ", S, "1.A"), ("Lam-Nsọ'", B, "1.B")], ["sardauna", "takum"],
          "It is spoken at Gembu and nearby towns (Sardauna LGA) and at Manya (Takum LGA), but mainly in Cameroon.", "125,000 in Cameroon (SIL 1987)", "The New Testament appeared in 1989.", "lamn1239"),
 "leelau": ("Leelạu", "adamawa", "Adamawa (Bikwin group)", [("Lelo", S, "1.A"), ("Munga", L, "2.A")], ["karim-lamido"],
          "It is spoken in one village and a hamlet 15 km east of Karim Lamido town.", "", "", "leel1242"),
 "limbum": ("Limbum", "grassfields-bantu", "Southern Bantoid (Grasslands Bantu)", [], ["sardauna"], "It is spoken on the Mambila uplands, but mainly in Cameroon.",
          "few in Nigeria; 73,000 in Cameroon (SIL 1982)", "", "limb1268"),
 "loo": ("Loo", "adamawa", "Adamawa (Bikwin group)", [("Shú̹ŋó̹", B, "1.B")], ["karim-lamido"],
          "It is spoken at Lo village and its hamlets, 30 km north of Karim Lamido town, and in Kaltungo LGA of Gombe State.", "8,000 (1992 estimate)", "", "looo1238"),
 "maghdi": ("Maghdi", "adamawa", "Adamawa (Bikwin group)", [("Mághdì", B, "1.B"), ("Widala", L, "2.B")], ["karim-lamido"],
          "Its speakers are a section of the Widala (a name that also applies to Kholok).", "under 2,000 (1992)", "", "magh1238"),
 "mak": ("Mak", "adamawa", "Adamawa (Bikwin group)", [("Panya", L, "2.A"), ("Panyam", L, "2.A"), ("Zoo", L, "2.A")], ["karim-lamido"],
          "It is spoken 15 km north of Karim Lamido town.", "", "The name Panya comes from Poonya, a founding hero. Its dialects are Panya and Zo.", "makn1235"),
 "mambila": ("Mambila", "mambiloid", "Northern Bantoid (Mambiloid)", [("Ju Nɔri", B, "1.B"), ("Mambilla", L, "2.A"), ("Mambere", L, "2.A")], ["sardauna"],
          "It is spoken on the Mambila Plateau, and in Cameroon.", "18,000 (1952); 60,000 (SIL 1973); 10,000 in Cameroon",
          "Almost every village has its own dialect, forming a chain, with centres at Bang, Dorofi, Gembu, Hainari, Kabri, Mayo Ndaga, Mbamnga, Tamien and Warwar. The New Testament appeared in the Gembu dialect in 1975.", None),
 "mashi": ("Mashi", "beboid", "South Bantoid (Beboid)", [], ["takum"], "It is spoken in one village near Takum.", "", "", "mash1269"),
 "mbembe-tigong": ("Mbembe Tigong", "jukunoid", "Jukunoid (Central Jukunoid; Jukun–Mbembe–Wurbo group)",
          [("Tigong", L, "2.A"), ("Tigun", L, "2.A"), ("Tugun", L, "2.A"), ("Tukun", L, "2.A"), ("Tigum", L, "2.A"), ("Akonto", L, "2.B"), ("Nzare", L, "2.B")], ["sardauna"],
          "It is spoken mainly in Cameroon.", "2,900 in Nigeria (SIL 1973)", "It is a cluster including Ashuku and Nama.", "tigo1236"),
 "mbongno": ("Mbɔŋnɔ", "mambiloid", "Northern Bantoid (Mambiloid)", [("Bungnu", S, "1.A"), ("Mbọngnọ", B, "1.B"), ("Kamkam", L, "2.A"), ("Kakaba", L, "2.B"), ("Bunu", L, "2.B")], ["sardauna"],
          "It is spoken at Kakara town.", "800 (1952); about 3,000 (Blench and Connell 1999)", "", "mbon1253"),
 "mingang-doso": ("Mingang Doso", "adamawa", "Adamawa (Jen group)", [("Munga", S, "1.A"), ("ŋwai Mәngàn", B, "1.B"), ("Dosọ", L, "2.A")], ["karim-lamido"],
          "It is spoken in one village and its hamlets 15 km east of Karim Lamido town.", "", "", "ming1254"),
 "moo": ("Mɔɔ", "adamawa", "Adamawa (Bikwin group)", [("ŋwaa Mɔ́ɔ̀", B, "1.B"), ("Gwomo", L, "2.A"), ("Gwom", L, "2.A"), ("Gwomu", L, "2.A"), ("Gomu", L, "2.A")], ["karim-lamido"], "", "", "", "mooo1239"),
 "mumuye": ("Mumuye", "adamawa", "Adamawa (Mumuye–Yendang group; Mumuye subgroup)", [], ["jalingo", "zing", "yorro"],
          "The Atlas also names Mayo Belwa LGA (Adamawa State).", "103,000 (1952); 400,000 (UBS 1980)",
          "It is a cluster with a north-eastern group (the Zing group: Bajama, Jeng, Zing, Mang, Kwaji, Meeka, Yaa) in Zing, Yorro and Mayo Belwa, and a south-western group (Monkin and Kpugbong groups, including Lankoviri and Jalingo) in Jalingo LGA. Mark appeared in Zinna in 1938.", "nucl1240"),
 "mvanip": ("Mvanɨp", "mambiloid", "Northern Bantoid (Mambiloid)", [("Magu", L, "2.A")], ["sardauna"],
          "It is spoken in a single quarter of Zongo Ajiya town in the north-west of the Mambila Plateau.", "100 (Blench 1999)", "", "mvan1238"),
 "naki": ("Naki", "beboid", "South Bantoid (Beboid)", [], [], "The Atlas places one village (Belogo, also called Tosso 2) in Nigeria at about 6°57′N, 10°13′E; it is spoken mainly in Cameroon.",
          "3,000 in Cameroon (1976)", "", "naki1238"),
 "ndoola": ("Ndoola", "mambiloid", "Northern Bantoid (Mambiloid)", [("Ndoro", S, "1.A"), ("Njoyamɛ", L, "2.A")], ["sardauna", "gashaka"],
          "It is also spoken in one village in Cameroon.", "1,169 (1952); more than 15,000 (1999 estimate)", "It has at least two dialects.", "ndoo1241"),
 "ndunda": ("Ndunda", "mambiloid", "Northern Bantoid (Mambiloid)", [], ["sardauna"], "It is spoken in the north-west of the Mambila Plateau.", "400 (Blench 1999)", "", "ndun1251"),
 "nyam": ("Nyam", "west-chadic", "West Chadic (Bole–Ngas group; Bole–Tangale group)", [], ["karim-lamido"], "It is spoken in a single village, Andami.", "", "", "nyam1285"),
 "pangseng": ("Pangseng", "adamawa", "Adamawa (Mumuye–Yendang group; Mumuye subgroup)", [], ["karim-lamido"], "", "", "Its varieties are Pangseng, Komo and Jega.", "pang1286"),
 "piya-kwonci": ("Piya–Kwonci", "west-chadic", "West Chadic (Bole–Ngas group; Bole group)", [("Pia", S, "1.A"), ("Wurkum", L, "2.A"), ("Pitiko", L, "2.A")], ["karim-lamido"],
          "It is spoken near Didango.", "2,500 (1977); Kwonci more than 4,000 (1990)", "It is a cluster of Piya and Kwonci.", "piya1245"),
 "rang": ("Rang", "adamawa", "Adamawa (Mumuye–Yendang group; Mumuye subgroup)", [], ["zing"], "", "", "", "rang1269"),
 "samba-daka": ("Samba Daka", "dakoid", "Northern Bantoid (Dakoid)",
          [("Chamba Daka", S, "1.A"), ("Chamba", S, "1.A"), ("Tchamba", S, "1.A"), ("Tsamba", S, "1.A"), ("Jama", S, "1.A"), ("Daka", S, "1.A"), ("Sama Mum", B, "1.B")], ["jalingo", "bali", "zing"],
          "The Atlas also names Ganye and Mayo Belwa LGAs (Adamawa State).", "66,000 (1952); more than 100,000 (1990)",
          "Its dialects include Samba Daka, Samba Jangani, Samba Nnakenyare and Samba of Mapeo, and may form a cluster with Lamja and Taram. Mark was published in 1933.", "samb1311"),
 "samba-leko": ("Samba Leko", "adamawa", "Adamawa (Leko group)", [("Chamba Leko", S, "1.A"), ("Samba Leeko", S, "1.A"), ("Sama", B, "1.B"), ("Leko", L, "2.B"), ("Suntai", L, "2.B")], ["wukari", "takum"],
          "The Atlas also names Ganye and Fufore LGAs (Adamawa State); it is spoken mainly in Cameroon.", "42,000 in all (SIL 1972); 50,000 (1971)", "", "samb1305"),
 "shoo-minda-nye": ("Shoo–Minda–Nye", "jukunoid", "Jukunoid (Central Jukunoid; Wurbo cluster)", [("Jinleri", L, "2.A (Minda)")], ["karim-lamido"], "", "10,000 (SIL)",
          "It is a cluster of Shoo, Minda and Nye, and may be related to Jessi, spoken between Lau and Lankoviri.", "shoo1247"),
 "somyev": ("Somyɛv", "mambiloid", "Northern Bantoid (Mambiloid)", [("Kila", L, "2.A"), ("Zuzun", L, "2.A")], ["sardauna"],
          "It is the blacksmiths' dialect of Kila Yang village, 10 km west of Mayo Ndaga, and was formerly spoken in Cameroon.", "4 speakers (2006)", "It is on the point of extinction.", "somy1238"),
 "tep": ("Tep", "mambiloid", "Northern Bantoid (Mambiloid)", [], ["sardauna"], "It is spoken in a single village and its hamlets on the Mambila Plateau.", "under 4,000", "", "tepp1235"),
 "tha": ("Tha", "adamawa", "Adamawa (Bikwin–Jen group)", [], ["karim-lamido"], "It is spoken at Joole Manga Dìdí village, and in Numan LGA of Adamawa State.", "", "", "thaa1239"),
 "ugare": ("Ugarә", "tivoid", "Southern Bantoid (Tivoid)", [("Binangeli", L, "2.B"), ("Messaka", L, "2.B")], [],
          "Quoting Cassetta and Cassetta (1994), the Atlas says that most speakers live in Cameroon and that those in Nigeria live mainly in Benue and Taraba states.", "5,000 (1994 estimate)", "", "mesa1245"),
 "vute": ("Vute", "mambiloid", "Northern Bantoid (Mambiloid)", [("Bute", S, "1.A"), ("Mbute", S, "1.A"), ("Wute", S, "1.A"), ("Voute", S, "1.A")], ["sardauna"],
          "It is spoken on the north-east of the Mambila Plateau, but mainly in Cameroon.", "1,000 or fewer in Nigeria; 30,000 in Cameroon (1985)", "It has at least six dialects.", "vute1244"),
 "wiyaa": ("Wiyaa", "adamawa", "Adamawa (Waja group)", [("Wagga", S, "1.A"), ("Nyan Wịyáù", B, "1.B"), ("Waja", L, "2.A")], ["bali"],
          "It is spoken mainly in Balanga and Kaltungo LGAs of Gombe State (Waja district).", "19,700 (1952); 50,000 (1992 estimate)", "Its dialects are Plain and Hills.", "waja1259"),
 "yamba": ("Yamba", "grassfields-bantu", "Southern Bantoid (Grassfields; Nkambe cluster)", [("Mbem", L, "2.B")], ["sardauna", "gashaka"],
          "It is spoken in Antere and other border villages, but mainly in Cameroon.", "few in Nigeria; 25,000 in Cameroon (SIL 1982)", "", "yamb1251"),
 "yukuben": ("Yukuben", "jukunoid", "Jukunoid (Yukuben–Kutep)", [("Nyikuben", S, "1.A"), ("Nyikobe", S, "1.A"), ("Ayikiben", S, "1.A"), ("Yikuben", S, "1.A"), ("Boritsu", L, "2.B"), ("Balaabe", L, "2.B")], ["takum"],
          "It is also spoken in Furu-Awa subdivision, Cameroon.", "10,000 (1971); 1,000 in Cameroon (1976)", "", "yuku1243"),
 "ligri": ("Ligri", "jarawan", "Bantu (Jarawan; Jar cluster)", [], ["karim-lamido"], "", "800 (Ayuba estimate, 2008)", "It is the Taraba member of the Jar cluster, which is otherwise spoken in Plateau and Bauchi states.", "ligr1238"),
}
CAVEAT = "The Atlas may name the LGA as it was when its data were collected; several Taraba LGAs (Lau, Ussa, Donga, Kurmi, Ardo Kola, Yorro and others) were created later from older ones."
FIELD = {"1.A": "alternate spelling of the name", "1.B": "the speakers' own name for the language", "2.A": "name based on location", "2.B": "other name for the language"}


def text(k):
    name, parent, cls, names, lgas, note, spk, extra, g = LANG[k]
    where = (f" in {', '.join(LGA[l] for l in lgas[:-1])}{' and ' if len(lgas) > 1 else ''}{LGA[lgas[-1]]} LGA{'s' if len(lgas) > 1 else ''} of Taraba State") if lgas else " in Taraba State"
    t = f"{name} is a language spoken{where}, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as {cls}."
    alt = [n for n, _, _ in names if n != name]
    if alt: t += f" Other names include {', '.join(alt[:6])}."
    if note: t += " " + note
    if spk: t += f" The Atlas cites speaker figures of {spk}."
    if extra: t += " " + extra
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for k, (name, desc) in BR.items():
    RECORDS.append(dict(key=f"br_{k}", table="languages", evidence="single_reliable_source", level="well_documented",
                        fields=dict(lang_type="branch", name=name, slug=k, summary=desc.split(". ")[0] + ".", description=desc),
                        srcs=[("ATLAS", f"{name}: classification")]))
for k, (name, parent, cls, names, lgas, note, spk, extra, g) in LANG.items():
    f = dict(lang_type="language", name=name, slug=k, summary=text(k).split(". ")[0] + ".", description=text(k))
    if parent:
        f["parent_id"] = f"@key:br_{parent}" if parent in BR else f"@languages:{parent}"
    if g:
        f["glottocode"] = g
    RECORDS.append(dict(key=k, table="languages", evidence="single_reliable_source", level="well_documented", fields=f,
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers")] + ([("GLIDX", f"Glottocode {g}")] if g else [])))
    RELATIONS.append(dict(frm=k, type="spoken_in", to="@admin_units:state:taraba", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                          notes="Blench's Atlas."))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=f"@admin_units:lga:taraba/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                              notes=CAVEAT))
    for n, t, fld in names:
        if n == name:
            continue
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=f"Blench's Atlas (2020), field {fld}: {FIELD.get(fld[:3], 'head entry')}.", srcs=["ATLAS"]))
# Abinsi = the existing Wannu record
RELATIONS += [
    dict(frm="@languages:wannu", type="spoken_in", to="@admin_units:state:taraba", source="ATLAS", evidence="single_reliable_source", level="well_documented",
         notes="Atlas, Kororofa cluster, *Abinsi: Taraba State, Wukari LGA, at Sufa and Kwantan Sufa; Benue State, Makurdi LGA, at Abinsi."),
    dict(frm="@languages:wannu", type="spoken_in", to="@admin_units:lga:taraba/wukari", source="ATLAS", evidence="single_reliable_source", level="well_documented",
         notes="At Sufa and Kwantan Sufa (Atlas). " + CAVEAT),
]
NAMES += [
    dict(record="@languages:wannu", name="Abinsi", name_type="alternative", usage_notes="Blench's Atlas (2020): head of this member of the Kororofa cluster.", srcs=["ATLAS"]),
    dict(record="@languages:wannu", name="River Jukun", name_type="alternative", usage_notes="Blench's Atlas (2020), field 2.A.", srcs=["ATLAS"]),
]
GAPS = [
    ("Nupe at Ibi", "The Atlas mentions 'small but well established Nupe communities in Ibi (Taraba State)'. Nupe is not yet a language record."),
    ("Glottocodes not matched", "Buru (Glottolog 'Buru-Angwe' merges it with Batu Angwe), Dirim ('Jirim'?), Joole, Gbaya, Mambila (a family in Glottolog), Kulung (Chadic), Dampar."),
    ("Current LGAs", "The Atlas names pre-1996 LGAs. Which present LGA (Lau, Ussa, Donga, Kurmi, Ardo Kola, Yorro, Gassol, Ibi) each language is in needs checking."),
    ("Adamawa LGAs named under Taraba", "Mayo Belwa, Ganye, Fufore, Yola and Numan are Adamawa LGAs; the Atlas lists them for Dong, Mumuye, Samba Daka, Samba Leko, Lamja and others."),
    ("Bali village", "The Atlas places the Bali language at 'Bali, a single village south of Jalingo' in 'Numan LGA'. The present LGA is not known."),
    ("Wannu and Wapan", "The archive's Wannu record (Glottolog wann1241, map point at Abinsi) is the Atlas's Abinsi; Wapan (Jukun of Wukari) is a separate record. The Atlas gives 'Wapan' as the Abinsi people's own name too."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Taraba languages (Blench Atlas, read in full): 6 branches, 68 languages, LGA links, other names; Wannu (Abinsi) Taraba links.")


def report():
    langs = [r for r in RECORDS if r["fields"]["lang_type"] == "language"]
    per_lga = {l: 0 for l in LGA}
    for k, v in LANG.items():
        for l in v[4]:
            per_lga[l] += 1
    L = ["# Research batch 038 — Taraba: the languages", "",
         f"Researched {ACCESSED}. Phase 3, the first Taraba batch. Created in review; published only after your approval.", "",
         f"- **{len(langs)} languages** and **{len(BR)} new branches** (Adamawa, Mambiloid, Dakoid, Beboid, Grassfields Bantu, Gbaya), all from Blench's Atlas. All 74 Taraba entries were read by hand; 9 were excluded after reading (see the top of the script).",
         f"- **{sum(1 for v in LANG.values() if v[8])} Glottocodes**, matched by name with map points checked in or near Taraba.",
         f"- **{len(RELATIONS)} links** (state and LGA), and **{len(NAMES)} other names**, typed by the Atlas's key as in batch 036.",
         "- The existing **Wannu** record is the Atlas's *Abinsi* and gets its Taraba links (Wukari). Tiv and Etulo already have theirs.",
         "- New tool: `database/research/atlas_extract.py` (column-split Atlas extraction for any state).", "",
         "## Languages per LGA (as the Atlas names them)", "", "| LGA | Languages |", "|---|---|"]
    for l, n in per_lga.items():
        L.append(f"| {LGA[l]} | {n} |")
    L += ["", "Lau, Ussa, Donga, Kurmi, Ardo Kola, Gassol and Ibi get few or no links because the Atlas uses the older LGA names.", "",
          "## The languages", ""]
    for k in LANG:
        L += [f"**{LANG[k][0]}** ({LANG[k][8] or 'no Glottocode'}). {text(k)}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_038_taraba_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_038_taraba_languages_REVIEW.md", "w").write(report())
    print(f"languages={len(LANG)} branches={len(BR)} relations={len(RELATIONS)} names={len(NAMES)} glotto={sum(1 for v in LANG.values() if v[8])}")
