"""
Research batch 044 — Plateau (Phase 3, first batch): the languages of Plateau State and the LGAs
where Roger Blench's Atlas of Nigerian Languages (2020) places them. Researched 2026-09-30.
Peoples (ethnic groups) are a separate batch, as for Nasarawa and Taraba.

Method: the Atlas PDF (Internet Archive snapshot 2025-05-28) was split into columns with
atlas_extract.py, over ALL 491 head entries; every entry naming 'Plateau State' or a Plateau LGA was
kept (data/atlas_plateau_blocks.txt, 46 entries after removing false matches — 'Jos' as a Kainji
group name, bibliography titles, index cross-references) and read by hand. Only what each entry
states is used. Names are typed by the Atlas's key (1.A spelling, 1.B own name for the language,
2.A location name, 2.B other name) as in batches 036 and 038; own names of peoples (1.C, 2.C) are
kept for the peoples batch.

Boundaries: the Atlas's data predate the 1996 split of Nasarawa from Plateau, so several entries say
'Plateau State' for Lafia and Awe (now Nasarawa). Only CURRENT Plateau LGAs are linked. Old LGA
names that are now divided are not linked (state link + note): 'Jos' (now Jos North, Jos South and
Jos East) and 'Langtang' (now Langtang North and Langtang South). Pankshin, Shendam and Langtang
later lost areas to Kanke, Mikang and Qua'an Pan, which the Atlas mostly does not name.

Excluded after reading: Jili (its 'Plateau State, Lafia and Awe LGAs' is Nasarawa today; the record
exists), Jorto (the Atlas doubts it exists — gap), the Jukun cluster's Wase Tofa (location only —
gap), Kantana of the Jar cluster (no source separates it from Mama, which the Atlas also calls
Kantana — gap), and members placed only in other states (Zhar, Gwak, Bobar, Ibunu-Lɔrɔ, Panawa,
Ya, Bijim, Bwol, Gworam).
Existing records that get Plateau links: Goemai (Shendam; the Atlas writes 'Nasarawa State, Shendam,
Awe and Lafia' — Shendam is in Plateau), Hone (Wase), Wapan (Shendam; 'precise areas uncertain').
Also: the existing Kulere people record speaks the new Kulere language.

Glottocodes: matched by name (or the Atlas's other names) in Glottolog's CLDF language table, each map
point checked to lie in or near Plateau (7.5–11.5 N, 7.5–11.5 E) and the ISO code noted. Where
Glottolog's name differs the text says so (Eten, Amo, Mindat, Fyam, Pyapun, Pye, Sya, Montol, Izora,
Bada (Nigeria), Pan). No code: Shagawu, and the Vaghat cluster (a family in Glottolog).
"""
import json, sys

ACCESSED = "2026-09-30"
SOURCES = {
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf",
                  verification_status="verified", notes="Reused. Plateau entries read in full (data/atlas_plateau_blocks.txt)."),
    "GLIDX": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Glottolog language index (resourcemap)", organisation="Glottolog",
                  url="https://glottolog.org/resourcemap.json?rsc=language", verification_status="verified", notes="Reused."),
    "GTAR": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Tarokoid (taro1265)", organisation="Glottolog",
                 url="https://glottolog.org/resource/languoid/id/taro1265", verification_status="verified",
                 notes="Tarokoid (family), map point 9.40 N, 9.75 E. Includes Yangkam-Tarok-Pe (Tarok, Pye, Yangkam) and the Vaghat group."),
}
BR = {  # new branches
    "tarokoid": ("Tarokoid", "Tarokoid is a small group of Benue–Congo languages spoken in south-eastern Plateau State and neighbouring Bauchi State. Roger Blench's Atlas of Nigerian Languages classifies Tarok, Pe, Yangkam and the Vaghat–Ya–Bijim–Legeri cluster as Tarokoid, and Glottolog lists Tarokoid (taro1265) as a family.", "taro1265"),
}
LGA = {"barkin-ladi": "Barkin Ladi", "bassa": "Bassa", "bokkos": "Bokkos", "jos-east": "Jos East", "jos-north": "Jos North", "jos-south": "Jos South",
       "kanam": "Kanam", "kanke": "Kanke", "langtang-north": "Langtang North", "langtang-south": "Langtang South", "mangu": "Mangu", "mikang": "Mikang",
       "pankshin": "Pankshin", "qua-an-pan": "Qua'an Pan", "riyom": "Riyom", "shendam": "Shendam", "wase": "Wase"}
S, B, L = "spelling_variant", "endonym", "alternative"
JOS = "The Atlas also names Jos LGA, which has since been divided into Jos North, Jos South and Jos East."
LANGTANG = "The Atlas also names Langtang LGA, since divided into Langtang North and Langtang South."
# key: (name, parent, class text, [(name, type, field)], [lgas], place/other-state note, speakers, extra, glottocode)
LANG = {
 "akpondu": ("Akpondu", "plateau", "Plateau (Alumic)", [], [],
          "The Atlas gives no LGA and names Ninzo as the second language.", "1 (2005)",
          "In 2005 the last speaker was a 'rememberer' who could recall only fragments of vocabulary; the Atlas lists it as moribund or extinct.", "akpo1243"),
 "aten": ("Aten", "plateau", "Plateau (Beromic)", [("Ten", B, "1.B"), ("Etien", B, "1.B"), ("Ganawuri", L, "2.B"), ("Jal", L, "2.B")], ["barkin-ladi"],
          "It is also spoken in Jema'a LGA of Kaduna State.", "6,710 (1963 census); about 40,000 (Kjenstad 1988; Blench 2003)",
          "A literacy programme is in progress; Mark appeared in 1940, and the New Testament has been published. Glottolog lists it as Eten.", "eten1239"),
 "berom": ("Berom", "plateau", "Plateau (Beromic)",
          [("Birom", S, "1.A"), ("Berum", S, "1.A"), ("Cèn Bèrom", B, "1.B"), ("Afango", L, "2.B"), ("Akuut", L, "2.B"), ("Baho", L, "2.B"), ("Gbang", L, "2.B"),
           ("Kibbo", L, "2.B"), ("Kibo", L, "2.B"), ("Kibbun", L, "2.B"), ("Kibyen", L, "2.B"), ("Sine", L, "2.B")], ["barkin-ladi"],
          JOS + " It is also spoken in Jema'a LGA of Kaduna State.", "54,500 (HDG); 200,000 (SIL 1985)",
          "Its dialect groups are Gyel–Kuru–Vwang, Fan–Foron–Heikpang, Bachit–Gashish, Du–Ropp–Rim–Riyom and perhaps Hoss; Nincut is treated as a separate language. An official orthography was published in 1985; Scripture portions appeared from 1916, the New Testament in 1984 and the complete Bible in 2009.", "bero1242"),
 "bo-rukul": ("Bo-Rukul", "plateau", "Plateau (Southeastern group)", [("Mabo–Barkul", S, "1.A"), ("Mabol", L, "2.A"), ("Barukul", L, "2.A")], ["mangu"],
          "It is spoken in Richa district.", "",
          "The Atlas also gives 'Kulere' as another name for it, although Kulere is a separate, Chadic language of Bokkos LGA.", "boru1244"),
 "boghom": ("Boghom", "west-chadic", "West Chadic (branch B: Zaar group, Boghom subgroup)",
          [(n, S, "1.A") for n in ("Burom", "Burrum", "Burma", "Borrom", "Boghorom", "Bogghom", "Bohom", "Bokiyim")], ["kanam"],
          "", "9,500 (1952); 50,000 (SIL 1973)", "Scripture portions have appeared since 1955, and a reading and writing book in 2018.", "bogh1241"),
 "cakfem-mushere": ("Cakfem–Mushere", "west-chadic", "West Chadic (branch A3)", [("Chakfem", S, "1.A (Cakfem)"), ("Chokfem", S, "1.A (Cakfem)")], ["mangu"],
          "Mushere is spoken in about thirteen villages.", "Cakfem 5,000 (SIL)",
          "It has two members: Cakfem, with the dialect Jajura, and Mushere, which is sharply divided into two dialects, plus Kadim, spoken in a single village. Some literacy work is under way.", "cakf1236"),
 "cara": ("Cara", "plateau", "Plateau (Beromic)",
          [(n, S, "1.A") for n in ("Chara", "Nfachara", "Fakara", "Pakara", "Fachara", "Terea", "Teria", "Terri", "Tariya")], ["bassa"],
          "It is spoken in nine villages.", "735 (1936); 5,000 (Blench estimate, 2012)", "", "cara1270"),
 "che": ("Che", "plateau", "Plateau (Ninzic)", [("Ce", S, "1.A"), ("Kuche", B, "1.B"), ("Rukuba", L, "2.A"), ("Sale", L, "2.B"), ("Inchazi", L, "2.B")], ["bassa"],
          "", "15,600 (1936); 50,000 (SIL 1973)", "Mark appeared in 1924 and John in 1931.", "chee1238"),
 "firan": ("Fɨran", "plateau", "Plateau (Central group, South–Central subgroup: Izeric)", [("Faran", S, "1.A"), ("Forom", S, "1.A"), ("Kwakwi", L, "2.A")], ["barkin-ladi"],
          "It is spoken at Kwakwi station, south of Jos.", "under 1,500 (1991)", "", "fira1238"),
 "fyer": ("Fyer", "west-chadic", "West Chadic (branch A: Ron group)", [("Fier", S, "1.A")], ["mangu"], "", "1,500 (1970); 10,000 (Blench 1999)", "", "fyer1241"),
 "horom": ("Horom", "plateau", "Plateau (Southeastern group)", [("Barom", B, "1.B")], ["mangu"],
          "It is spoken in one village and one hamlet.", "500 (SIL 1973); 1,000 (Blench 1998)", "", "horo1245"),
 "iguta": ("Iguta", "kainji", "Kainji (Eastern Kainji: Northern Jos group)", [("Naraguta", L, "2.A")], ["bassa"], "", "2,580 (HDG); 3,000 (SIL 1973)", "", "igut1238"),
 "izere": ("Izere", "plateau", "Plateau (Central)", [("Izarek", S, "1.A"), ("Zarek", S, "1.A"), ("Jarawa", L, "2.B")], ["jos-south", "barkin-ladi"],
          "It is also spoken in Toro LGA of Bauchi State, and by migrants in Jema'a LGA of Kaduna State. The Atlas places the Fobur and Northeastern dialects in 'Jos LGA', since divided.",
          "22,000 (1971); 30,000 (1977)",
          "It is a cluster whose members include Fobur (with Shere and Jos Zarazon), Northeastern (Federe, Zendi, Fursum, Jarawan Kogi), Southern (at Forom and Gashish in Barkin Ladi LGA), Ichèn, Faishang and Ganang. Mark appeared in 1940, and a New Testament translation is under way.", "izer1241"),
 "janji": ("Janji", "kainji", "Kainji (Eastern Kainji: Northern Jos group)", [("Jenji", S, "1.A"), ("Tìjánjí", B, "1.B")], ["bassa"], "", "360 (1950)", "", "janj1240"),
 "doori": ("Doori", "jarawan", "Bantu (Jarawan; Jar cluster)", [("Dõõri", B, "1.B"), ("Duguranci", L, "2.B")], ["kanam"],
          "It is spoken mainly in Alkaleri and Tafawa Balewa LGAs of Bauchi State.", "",
          "Earlier sources divided it into regional dialects, but the Atlas finds that all Doori speak mutually intelligible varieties. It is gradually yielding to Hausa: middle-aged people still use it, but young people no longer actively do. Glottolog lists it as Duguri.", "dugu1249"),
 "mbat": ("Mbat", "jarawan", "Bantu (Jarawan; Jar cluster)",
          [("Mbada", S, "1.A"), ("Bat", S, "1.A"), ("Bada", S, "1.A"), ("Baɗa", S, "1.A"), ("Kanna", L, "2.A"), ("Jar", L, "2.B"), ("Jarawan Kogi", L, "2.B"), ("Garaka", L, "2.B")], ["kanam"],
          "It is spoken in the north-central part of Kanam LGA, centred at Gagdi-Gum; the related Mbat-Galamkya is spoken in north-western Kanam, including Gyangyang 2 and Gidgid.",
          "10,000 (SIL), and 10,000 for Mbat-Galamkya",
          "Hausa and Fulfulde are used as second languages. Glottolog lists it as Bada (Nigeria), with Mbat and Galamkya as dialects.", "bada1258"),
 "jere": ("Jere", "kainji", "Kainji (Eastern Kainji: Northern Jos group)", [("Jera", S, "1.A"), ("Jeere", S, "1.A"), ("Buji", L, "2.A (Boze)"), ("Jengre", L, "2.A (Jere)")], ["bassa"],
          "It is also spoken in Toro LGA of Bauchi State and Saminaka LGA of Kaduna State.", "23,000 (SIL 1972)",
          "It is a cluster. Its Plateau members are Boze (Buji), spoken on both sides of the Jos–Zaria road directly north of Jos in three dialects (εGorong, εKɔkɔŋ and εFiru), Gusu (Sanga) and Jere proper (Ezelle); Ibunu-Lɔrɔ and Panawa are spoken in Bauchi State. Glottolog treats Boze (Buji) and Gusu as dialects of Jere.", "jere1244"),
 "koenoem": ("Koenoem", "west-chadic", "West Chadic (branch A3)", [("Kanam", S, "1.A")], ["shendam"], "", "1,898 (1934); 3,000 (SIL)",
          "The Atlas groups it with Tal and Pyapung in the Talic cluster.", "koen1239"),
 "kulere": ("Kulere", "west-chadic", "West Chadic (branch A: Ron group)",
          [("Akande", B, "1.B"), ("Tof", L, "2.A"), ("Richa", L, "2.A"), ("Kamwai", L, "2.A"), ("Korom Ɓoye", L, "2.B")], ["bokkos"],
          "", "6,500 (1925); 4,933 (1943); 8,000 (SIL 1973)", "Its dialects are Tof, Richa and Kamwai (which includes Marahai).", "kule1247"),
 "lemoro": ("Lemoro", "kainji", "Kainji (Eastern Kainji: Northern Jos group, North–central cluster)", [("Limorro", S, "1.A"), ("Emoro", B, "1.B"), ("Anowuru", L, "2.A")], ["bassa"],
          "It is also spoken in Toro LGA of Bauchi State.", "2,950 (1936)", "", "lemo1242"),
 "map": ("Map", "kainji", "Kainji (Eastern Kainji: Amic)", [("Amon", S, "1.A"), ("Among", S, "1.A"), ("Timap", B, "1.B"), ("Ba", L, "2.B")], ["bassa"],
          "It is also spoken in Saminaka LGA of Kaduna State.", "3,550 (1950)",
          "Three reading and writing books exist, and the Atlas describes it as vigorous (Jirgi 2016). Glottolog lists it as Amo.", "amoo1242"),
 "miship": ("Miship", "west-chadic", "West Chadic (branch A3)", [("Ship", S, "1.A"), ("Chip", S, "1.A"), ("Cip", S, "1.A")], ["mangu", "shendam"],
          "", "10,127 (1934); 6,000 (SIL)", "Its dialects include Longmaar and Jiɓaam.", "mish1244"),
 "mundat": ("Mundat", "west-chadic", "West Chadic (branch A: Ron group)", [], ["mangu"], "", "", "Glottolog lists it as Mindat.", "mund1334"),
 "mwaghavul": ("Mwaghavul", "west-chadic", "West Chadic (branch A3)",
          [("Mwahavul", S, "1.A"), ("Sura", L, "2.B"), ("Mapan", S, "1.A (Mupun)"), ("Toos", S, "1.A (Takas)")], ["barkin-ladi", "mangu"],
          "", "20,000 (1952); 40,000 (SIL 1973); current informal estimates suggest around 200,000",
          "It is a cluster of Mwaghavul, Mupun and Takas. Primers appeared from 1912, Scripture portions from 1915 and the New Testament in 1992; the Old Testament is in progress. A grammar and a dictionary were published in 2019.", "mwag1236"),
 "ngas": ("Ngas", "west-chadic", "West Chadic (branch A3)", [("Nngas Ngas", S, "1.A")], ["pankshin", "kanam"],
          LANGTANG, "55,250 (1952)",
          "Its dialects are Hill and Plain. Scripture portions appeared from 1916 and the New Testament in 1976; Hausa is the second language.", "ngas1240"),
 "pan": ("Pan", "west-chadic", "West Chadic (branch A3)", [], ["shendam", "mangu", "qua-an-pan"],
          "Its members are Mernyang, Doemak and Kwagallak (Shendam LGA), Tèŋ and Shindai (Qua'an Pan LGA; Shindai in Namu District) and Jipal (Mangu LGA); two more, Bwol and Gworam, are in Lafia LGA, now in Nasarawa State.",
          "72,946 (1963), of whom Mernyang 16,739 and Kwagallak 25,403",
          "The people call themselves Kofyar. Glottolog lists the language as Pan (ISO kwl), with Mernyang, Doemak, Kwagallak and Jipal as dialects.", "kofy1242"),
 "pe": ("Pe", "br_tarokoid", "Benue–Congo (Tarokoid)", [("Pai", S, "1.A"), ("Dalong", L, "2.B")], ["pankshin"],
          "It is spoken in seven villages.", "2,511 (1934); 2,000 (SIL 1973); 5,000 (1996)", "Glottolog lists it as Pye.", "peee1238"),
 "pyam": ("Pyam", "plateau", "Plateau (Southeastern)", [(n, S, "1.A") for n in ("Fyem", "Pyem", "Paiem", "Fem", "Pem")], ["barkin-ladi", "mangu"],
          JOS, "7,700 (1952); 14,000 (SIL 1973)",
          "The Atlas describes it as endangered. A reading and writing book and an Android dictionary appeared in 2018. Glottolog lists it as Fyam.", "fyam1238"),
 "pyapung": ("Pyapung", "west-chadic", "West Chadic (branch A3: Talic)", [("Piapun", S, "1.A"), ("Pyapun", S, "1.A")], ["shendam"],
          "", "5,167, including a few hundred Tal speakers (1934); 10,000 (Blench estimate, 2016)", "", "pyap1239"),
 "rigwe": ("Rigwe", "plateau", "Plateau (Central, South–central subgroup)",
          [("Aregwe", S, "1.A"), ("Irigwe", S, "1.A"), ("ɾȉgʷȅ", B, "1.B"), ("Miango", L, "2.A"), ("Nyango", L, "2.A"), ("Kwal", L, "2.A"), ("Kwoll", L, "2.A"), ("Kwan", L, "2.A")], ["bassa"],
          "It is also spoken in Kauru LGA of Kaduna State.", "13,500 (HDG); 40,000 (UBS 1985)",
          "Its dialects are Northern (Kwan) and Southern (Miango). The New Testament translation is complete and the Old Testament is in progress; there are some radio broadcasts in Plateau State, and the orthography is used for texting and on Facebook. The Atlas records it as not currently endangered. Glottolog lists it as Irigwe.", "irig1241"),
 "run": ("Run", "west-chadic", "West Chadic (branch A: Ron group)",
          [("Ron", S, "1.A"), ("Lis ma Run", B, "1.B (Run Bokkos)"), ("Bokos", L, "2.A (Run Bokkos)"), ("Batura", L, "2.A (Run Daffo–Butura)")], ["bokkos", "mangu"],
          "", "13,120 (1934); 60,000 (UBS 1985)",
          "It is a cluster: Run Bokkos (with Bokkos and Baron) and Run Daffo–Butura in Bokkos LGA, and Manguna and Mangar in Mangu LGA. Bokkos and Daffo–Butura are more closely related to each other than to Sha. A Bible translation is in progress. Glottolog lists it as Ron.", "ronn1241"),
 "sha": ("Sha", "west-chadic", "West Chadic (branch A: Ron group)", [], ["mangu"], "", "500 (SIL); about 1,000 (1970)",
          "The Atlas lists it with the Run (Ron) cluster, but less closely related than its other members. Glottolog lists it as Sya.", "shaa1247"),
 "shagawu": ("Shagawu", "west-chadic", "West Chadic (branch A: Ron group)", [("Shagau", S, "1.A"), ("Nafunfia", L, "2.B"), ("Maleni", L, "2.B")], ["mangu"],
          "", "20,000 (SIL)", "", None),
 "tal": ("Tal", "west-chadic", "West Chadic (branch A3: Talic cluster, with Pyapung and Koenoem)", [("Amtul", B, "1.B"), ("Kwabzak", L, "2.A")], ["pankshin"],
          "It is spoken in 52 settlements.", "9,210 (1934); 10,000 (SIL 1973); 26,000 (2014 estimate)",
          "It has six mutually intelligible dialects. A dictionary appeared in 2018, and the language is used on social media.", "tall1250"),
 "tambas": ("Tambas", "west-chadic", "West Chadic (branch A: Ron group)", [("Tembis", S, "1.A")], ["mangu"], "", "3,000 (SIL)", "", "tamb1267"),
 "tarok": ("Tarok", "br_tarokoid", "Benue–Congo (Tarokoid)", [("iTarok", B, "1.B"), ("Appa", L, "2.B"), ("Yergam", L, "2.B"), ("Yergum", L, "2.B")], ["wase"],
          LANGTANG, "68,000 (1971); 140,000 (UBS 1985)",
          "Its dialects are iTarok (Plain Tarok), iZini (Hill Tarok), Sәlyәr, iTarok Oga aSa and iGyang. A primer appeared in 1915, Scripture portions from 1917 and the New Testament in 1988.", "taro1263"),
 "tel": ("Tel", "west-chadic", "West Chadic (branch A3)", [("Teel", S, "1.A"), ("Tehl", S, "1.A"), ("Baltap", L, "2.A"), ("Montoil", L, "2.A"), ("Montol", L, "2.A")], ["shendam"],
          "", "13,386 (1934); 20,000 (SIL 1973)", "Glottolog lists it as Montol.", "mont1280"),
 "tunzu": ("Tunzu", "kainji", "Kainji (Eastern Kainji: Northern Jos group)", [("Dugusa", L, "2.A"), ("Duguza", L, "2.A")], ["jos-east"],
          "It is spoken in five villages of Jos East LGA and in two villages of Toro LGA, Bauchi State.",
          "2,500 (Blench estimate, 2003), with perhaps another 2,000 ethnic Tunzu who do not speak it",
          "Its speakers also use Izere, Ibunu and Hausa, and the Atlas describes it as threatened by a switch to Hausa.", "tunz1235"),
 "vaghat-ya-bijim-legeri": ("Vaghat–Ya–Bijim–Legeri", "br_tarokoid", "Benue–Congo (Tarokoid)", [("Kwanka", L, "2.A (Kwang)"), ("Kadun", L, "2.A (Kwang)")], ["mangu"],
          "Its Plateau members are Kwang (Vaghat) and Legeri, in Mangu LGA; Ya and Bijim are spoken in Tafawa Balewa LGA of Bauchi State.", "", "", None),
 "yangkam": ("Yangkam", "br_tarokoid", "Benue–Congo (Tarokoid)", [("Bashiri", L, "2.A")], ["wase"],
          "It is spoken at Bashar town. " + LANGTANG,
          "fewer than 400 in 1996, all over 40 years old (published figures such as 20,000 in 1977 refer to the ethnic population)",
          "The Atlas notes that the community has largely shifted to Hausa.", "yang1290"),
 "ywom": ("Ywom", "west-chadic", "West Chadic (branch A3)", [("Yiwom", S, "1.A"), ("Gerkanci", L, "2.B"), ("Gurka", L, "2.B")], ["shendam"],
          LANGTANG, "2,520 (1934); 8,000 (SIL 1973)", "Reading and writing books appeared in 2011 and 2018. Glottolog lists it as Yiwom.", "yiwo1237"),
 "zari": ("Zari", "west-chadic", "West Chadic (branch B: Zaar group)", [("Kopti", L, "2.A (Zari)"), ("Kwapm", L, "2.A (Zari)")], [],
          "It is spoken mainly in Toro and Tafawa Balewa LGAs of Bauchi State; the Atlas also names Jos LGA of Plateau State, since divided.",
          "Zakshi 2,950 and Boto 1,000 (1950)", "It is a cluster of Zakshi, Boto and Zari.", "zari1242"),
 "zora": ("Zora", "kainji", "Kainji (Eastern Kainji: Northern Jos group, North–central cluster)", [("iZora", B, "1.B")], ["bassa"],
          "It is spoken in ten settlements.", "425 (1936); 19 speakers (March 2016), in an ethnic population of about 3,000–4,000",
          "Hausa is the second language of the whole community and the first language of most of it; the Atlas describes Zora as highly endangered and not actively spoken by the younger generation. Glottolog lists it as Izora.", "izor1238"),
}
CAVEAT = "The Atlas may name the LGA as it was when its data were collected; Kanke, Mikang and Qua'an Pan, and the divisions of Jos and Langtang, came later."
FIELD = {"1.A": "alternate spelling of the name", "1.B": "the speakers' own name for the language", "2.A": "name based on location", "2.B": "other name for the language"}


def text(k):
    name, parent, cls, names, lgas, note, spk, extra, g = LANG[k]
    where = (f" in {', '.join(LGA[l] for l in lgas[:-1])}{' and ' if len(lgas) > 1 else ''}{LGA[lgas[-1]]} LGA{'s' if len(lgas) > 1 else ''} of Plateau State") if lgas else " in Plateau State"
    t = f"{name} is a language spoken{where}, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as {cls}."
    alt = [n for n, _, _ in names if n != name]
    if alt: t += f" Other names include {', '.join(alt[:6])}."
    if note: t += " " + note
    if spk: t += f" The Atlas cites speaker figures of {spk}."
    if extra: t += " " + extra
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for k, (name, desc, g) in BR.items():
    RECORDS.append(dict(key=f"br_{k}", table="languages", evidence="multiple_sources", level="well_documented",
                        fields=dict(lang_type="branch", name=name, slug=k, glottocode=g, summary=desc.split(". ")[0] + ".", description=desc),
                        srcs=[("ATLAS", f"{name}: classification"), ("GTAR", "Tarokoid family")]))
for k, (name, parent, cls, names, lgas, note, spk, extra, g) in LANG.items():
    f = dict(lang_type="language", name=name, slug=k, summary=text(k).split(". ")[0] + ".", description=text(k))
    f["parent_id"] = f"@key:{parent}" if parent.startswith("br_") else f"@languages:{parent}"
    if g:
        f["glottocode"] = g
    RECORDS.append(dict(key=k, table="languages", evidence="single_reliable_source", level="well_documented", fields=f,
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers")] + ([("GLIDX", f"Glottocode {g}")] if g else [])))
    RELATIONS.append(dict(frm=k, type="spoken_in", to="@admin_units:state:plateau", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                          notes="Blench's Atlas."))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=f"@admin_units:lga:plateau/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                              notes=CAVEAT))
    for n, t, fld in names:
        if n == name:
            continue
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=f"Blench's Atlas (2020), field {fld}: {FIELD.get(fld[:3], 'head entry')}.", srcs=["ATLAS"]))

# Existing records
for lang, lga, note in [
    ("goemai", "shendam", "Atlas: 'Nasarawa State, Shendam, Awe and Lafia LGAs'; Shendam is in Plateau State. 13,507 speakers in Shendam (Ames 1934)."),
    ("hone", "wase", "Atlas, Kororofa cluster, *Hone: 'Taraba State, Karim Lamido LGA; Plateau State, Wase LGA'."),
    ("wapan", "shendam", "Atlas, Kororofa cluster, *Wapan: 'Nasarawa State, Awe, Shendam, Lafia and Langtang LGAs (precise areas uncertain)'; Shendam and Langtang are in Plateau State."),
]:
    RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to="@admin_units:state:plateau", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
    RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=f"@admin_units:lga:plateau/{lga}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                          notes=note + " " + CAVEAT))
RELATIONS.append(dict(frm="@ethnic_groups:kulere", type="speaks", to="kulere", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                      notes="The Atlas's Kulere entry (Bokkos LGA; own name Akande)."))

GAPS = [
    ("Plateau: Jorto", "The Atlas lists Jorto (Shendam LGA, at Dokan Kasuwa; 4,876 in 1934; West Chadic A3) but doubts it really exists: no data have ever circulated. Not recorded."),
    ("Plateau: Jukun of Wase (Wase Tofa)", "The Atlas's Jukun cluster has a member 'Wase Tofa' in Shendam and Langtang LGAs, with no other data. Not recorded until a source describes it."),
    ("Plateau: Kantana", "The Atlas lists Kantana (Jar cluster, Kanam LGA; yielding to Hausa) but also gives 'Kantana' as another name for Mama (Nasarawa). No source found separates the two; no Glottolog entry."),
    ("Plateau: current LGAs", "The Atlas names pre-1996 LGAs. Languages placed in 'Jos' (Berom, Pyam, Izere dialects, Zari) and 'Langtang' (Ngas, Tarok, Yangkam, Ywom, Wapan) are linked to the state only; which of Jos North/South/East and Langtang North/South needs a current source. Kanke, Mikang and Riyom have no language links yet for the same reason."),
    ("Plateau: Glottocodes not matched", "Shagawu (no Glottolog entry found), Vaghat–Ya–Bijim–Legeri (a family in Glottolog)."),
    ("Plateau: Hausa and Fulfulde", "Named in the Atlas as second languages (Ngas, Mbat, Tunzu, Zora) but not yet language records."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Plateau languages (Blench Atlas, read in full): Tarokoid branch, 43 languages, LGA links, other names; Goemai, Hone, Wapan Plateau links; Kulere speaks Kulere.")


def report():
    langs = [r for r in RECORDS if r["fields"]["lang_type"] == "language"]
    per_lga = {l: [] for l in LGA}
    for k, v in LANG.items():
        for l in v[4]:
            per_lga[l].append(v[0])
    for lang, lga in (("Goemai", "shendam"), ("Hone", "wase"), ("Wapan", "shendam")):
        per_lga[lga].append(lang + " (existing)")
    L = ["# Research batch 044 — Plateau: the languages", "",
         f"Researched {ACCESSED}. Phase 3, the first Plateau batch. Created in review; published only after your approval.", "",
         "## Summary", "",
         f"- **{len(langs)} languages** and **1 new branch**, Tarokoid (Glottolog `taro1265`), all from Blench's Atlas.",
         "  - All 491 Atlas entries were searched, not only those containing 'Plateau State'.",
         "  - 46 entries name Plateau State or a Plateau LGA. Every one was read by hand.",
         f"- **{sum(1 for v in LANG.values() if v[8])} Glottocodes.** Each was matched by name or by one of the Atlas's other names, and its map point checked to lie in Plateau.",
         f"- **{len(RELATIONS)} links**, to the state and to LGAs.",
         f"- **{len(NAMES)} other names**, typed by the Atlas's key.",
         "- **Existing records get Plateau links:**",
         "  - **Goemai**, in Shendam. The Atlas writes 'Nasarawa State' here, but Shendam is in Plateau.",
         "  - **Hone**, in Wase.",
         "  - **Wapan**, in Shendam.",
         "  - The Nasarawa **Kulere** people are now linked as speaking the new Kulere language.",
         "- **Boundaries:** the Atlas's data predate 1996, so 'Plateau State, Lafia/Awe' is Nasarawa today and is not linked. 'Jos' and 'Langtang' are now divided LGAs, so those entries are linked to the state only.", "",
         "## Languages per LGA (as the Atlas names them)", "", "| LGA | Count | Languages |", "|---|---|---|"]
    for l, ns in per_lga.items():
        L.append(f"| {LGA[l]} | {len(ns)} | {', '.join(ns)} |")
    L += ["", "## The languages", ""]
    for k in LANG:
        L += [f"**{LANG[k][0]}** ({LANG[k][8] or 'no Glottocode'}). {text(k)}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_044_plateau_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_044_plateau_languages_REVIEW.md", "w").write(report())
    print(f"languages={len(LANG)} branches={len(BR)} relations={len(RELATIONS)} names={len(NAMES)} glotto={sum(1 for v in LANG.values() if v[8])}")
