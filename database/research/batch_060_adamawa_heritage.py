"""
Research batch 060 — Adamawa (Phase 3): culture and heritage. Researched 2026-10-01. Pattern: batches 047 + 049 + 054.

Places (16): the Sukur Cultural Landscape (UNESCO World Heritage Site 1999; NCMM declared national monument
No. 5); the four NCMM museums in Adamawa (National Museum Yola, National Museum Fombina in the Lamido's
palace, National Museum Hong, the Sukur Interpretation Centre); the Elephant House, Guyuk (NCMM proposed
No. 18, 'Architectural') and the Three Sisters Rock, Song (proposed No. 87, 'Natural'); nine further sites
from the state's tourist table (Makam Walls, Hamayaji Old Palace, Koma Hills, Sassa, Yadim and Sella Negis
waterfalls, Gorobi rock formations, Ruwan Zafi and Tapichima springs).
Festivals (43): the Adamawa State Planning Commission's 'Popular Festivals' table, read in full.

Evidence notes:
  * UNESCO WHC page 938 read via the Internet Archive (2024; copy in data/unesco_938_sukur_wayback2024.html):
    inscribed 1999, criteria (iii)(v)(vi), 764.4 ha + buffer 1,178.1 ha, Madagali LGA; 'Brief synthesis'.
  * NCMM lists read as raw HTML on 2026-10-01 (declared, proposed, museums). Proposed-list numbers counted
    in page order across categories, checked against Yakoko (66) and Keana (90) as in batch 054.
  * ADSPC page = Wayback snapshot of 2023-04-29 (data/adspc_adamawa_state_2023-04-29.html), Tier 1. Its
    festival table has damaged rows; they are handled as follows:
      - No. 10 Klashe Shatin: the cells are shifted ('CHAMBA AND MUMUYE' in the locality column, 'JADA' in
        the significance column) — read as Chamba and Mumuye, Jada LGA; no significance recorded.
      - Festivals whose LGA is 'Yola' (Nos. 25–30) cannot be placed in Yola North or South: state link only
        (as batch 057b). No. 25 Shadi gives locality 'Gombi' but LGA 'Yola' — state link only, noted.
      - No. 23 Danbi: LGA 'Mubi' (now split) — state link only.
      - No. 16 Mba Mba: LGA 'HONG MADAGALI', people 'KILBA MARGHI' — both LGAs and both peoples.
      - No. 39 Dzava-Dafa: LGA blank; Sukur is in Madagali (UNESCO) — linked there as 'reported'.
      - Nos. 18, 30 (month blank) and 32, 39 (duration blank): left blank.
    People names are mapped to archive records exactly as in batch 057b (Higgi -> Kamwe, Bwatiye -> Bachama,
    Kanakuru -> Dera, Bare -> Bwazza, Hona -> Hwana, Bille -> Ɓile, Libbo -> Kaan (name match only),
    Muchala -> Fali (via the Fali town Muchella; 057b)). Bagale (No. 35): no archive record — no people link.
  * Not recorded: Kiri Dam (modern infrastructure); 'Gumti Park, Toungo' (very probably the Gumti sector of
    the existing Gashaka-Gumti National Park record, but no source read says so); Dzugumi, Pella (the table
    does not say what it is). Gaps.
"""
import json, re, sys

ACCESSED = "2026-10-01"
SOURCES = {
    "UNESCO": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="Sukur Cultural Landscape",
                   organisation="UNESCO World Heritage Centre", url="https://whc.unesco.org/en/list/938/", verification_status="verified",
                   notes="Read via the Internet Archive (web.archive.org/web/2024id_/https://whc.unesco.org/en/list/938); the live page is behind a bot check. Inscribed 1999, criteria (iii)(v)(vi); property 764.4 ha, buffer zone 1,178.1 ha; Madagali LGA; N10 44 26 E13 34 19. Copy in database/research/data/."),
    "NCMML": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="List of National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/list-of-national-monuments/",
                  verification_status="verified", notes="Reused. Adamawa: No. 5 'Sukur Cultural Landscape, Madagali, Adamawa State'."),
    "NCMMP": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="Proposed National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/proposed-national-monuments/",
                  verification_status="verified", notes="Reused. Adamawa: No. 18 'The Elephant House, Adamawa State' (Architectural); No. 87 'The Three Sisters Rock, Adamawa State' (Natural)."),
    "NCMMM": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="National Museums",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/museums/national-museums/",
                  verification_status="verified", notes="Reused. Adamawa: National Museum Hong (LG Secretariat Complex, Hong); Interpretation Centre, Sukur (Mubi–Maiduguri Road, Madagali LG); National Museum Yola (No. 2 Mohammed Tukur Road, Jimeta); National Museum Fombina, Yola (Lamido of Adamawa Palace, 'Jimeta, Yola')."),
    "ADSPC": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Adamawa State (state profile)",
                  organisation="Adamawa State Planning Commission", url="https://adspc.ad.gov.ng/adamawa-state/", verification_status="verified",
                  notes="Reused (batch 057b). 'Tourists Attraction' table (16 sites) and 'Popular Festivals' table (43 festivals), Wayback snapshot of 2023-04-29."),
}
A = lambda l: f"@admin_units:lga:adamawa/{l}"
ST = "@admin_units:state:adamawa"
LGA = {'demsa': 'Demsa', 'fufore': 'Fufore', 'ganye': 'Ganye', 'girei': 'Girei', 'gombi': 'Gombi', 'guyuk': 'Guyuk', 'hong': 'Hong', 'jada': 'Jada',
       'lamurde': 'Lamurde', 'madagali': 'Madagali', 'maiha': 'Maiha', 'mayo-belwa': 'Mayo Belwa', 'michika': 'Michika', 'mubi-north': 'Mubi North',
       'mubi-south': 'Mubi South', 'numan': 'Numan', 'shelleng': 'Shelleng', 'song': 'Song', 'toungo': 'Toungo', 'yola-north': 'Yola North', 'yola-south': 'Yola South'}
TOUR = "The Adamawa State Planning Commission lists {n} among the state's tourist sites"

# ---------------------------------------------------------------- places
TEXT = {
 "sukur": """The Sukur Cultural Landscape, in Madagali LGA on the border with Cameroon, was inscribed on UNESCO's World Heritage List in 1999 under criteria (iii), (v) and (vi), and is No. 5 on the National Commission for Museums and Monuments' list of declared national monuments. UNESCO describes a hilltop settlement at about 1,045 metres, some 290 km from Yola, with a property of 764.4 hectares and a buffer zone of 1,178.1 hectares. The landscape is dominated by the dry-stone palace of the Hidi, the political and spiritual head of the community, with shrines in and around it. Below lie villages with dry-stone walls, sunken cattle pens, granaries, threshing floors and wells topped by conical stone structures, and terraced fields with ritual features such as sacred trees, joined by stone-paved tracks. The remains of many disused shaft furnaces, blown with bellows, record a former iron industry; UNESCO dates the settlement's recorded history of iron smelting, trade and political institutions to the 16th century. According to UNESCO the terraces and their rituals are still in use, festivals and ceremonies are regularly observed, and the thatched roofs need frequent upkeep. The site is protected as a national monument under Decree No. 77 of 1979 (now the NCMM Act) and by Adamawa State Gazette No. 47 of 20 November 1997, with the written consent of the Hidi-in-Council. The NCMM runs an interpretation centre at Sukur, and the Adamawa State Planning Commission lists the Sukur Kingdom among the state's tourist sites, together with two Sukur festivals, Zoku and Dzava-Dafa.""",
 "sukuric": """The Interpretation Centre at Sukur, on the Mubi–Maiduguri road in Madagali LGA, is one of the museums of the National Commission for Museums and Monuments (NCMM). It serves the Sukur Cultural Landscape, a UNESCO World Heritage Site. Its collections are not described in a readable source.""",
 "yola": """The National Museum Yola is one of the national museums of the National Commission for Museums and Monuments (NCMM), which gives its address as No. 2 Mohammed Tukur Road, off Ahmadu Bello Way, Jimeta-Yola. Its history and collections are not described in a readable source.""",
 "fombina": """The National Museum Fombina is a museum of the National Commission for Museums and Monuments (NCMM) in the palace of the Lamido of Adamawa. The Adamawa State Planning Commission lists the 'Fombina Palace Museum', at the Lamido's palace in Yola South LGA, among the state's tourist sites, as housing artefacts of the old Fombina kingdom; the NCMM gives the address as 'Jimeta, Yola'. Fombina is the old name of the Adamawa Emirate. The museum's collections are not described in more detail in a readable source.""",
 "hong": """The National Museum Hong is one of the national museums of the National Commission for Museums and Monuments (NCMM), housed in the Local Government Secretariat complex at Hong. Its history and collections are not described in a readable source.""",
 "elephant": """The Elephant House in Guyuk LGA is No. 18 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Architectural' category. The Adamawa State Planning Commission lists it among the state's tourist sites as ancient architecture of great significance to the Lunguda people. Its age, form and use are not described in a readable source.""",
 "sisters": """The Three Sisters Rock in Song LGA is No. 87 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Natural' category. The Adamawa State Planning Commission lists it among the state's tourist sites as a natural rock formation. It is not described in more detail in a readable source.""",
 "makam": TOUR.format(n="the Makam Walls") + """, at Makam village in Toungo LGA, describing them as ancient architecture hundreds of years old. Who built the walls, and when, is not described in a readable source.""",
 "hamayaji": TOUR.format(n="the old palace at Hamayaji") + """, in Madagali LGA, saying that it houses the history of a king of the Marghi people. The palace's date and the king's name are not given in a readable source.""",
 "koma": TOUR.format(n="the Koma Hills") + """, at Koma village in Jada LGA, as the home of the historic Koma people. Blench's Atlas and the archive's Koma record place the Koma in Jada LGA.""",
 "sassa": TOUR.format(n="the Sassa Waterfalls") + """, at Sassa village in Toungo LGA. They are not described in more detail in a readable source.""",
 "yadim": TOUR.format(n="the Yadim Waterfalls") + """, at Yadim village in Fufore LGA. The same list places the Sella Negis Waterfalls at Yadim village. They are not described in more detail in a readable source.""",
 "sella": TOUR.format(n="the Sella Negis Waterfalls") + """, at Yadim village in Fufore LGA, noting their nearness to the state capital. The same list places the Yadim Waterfalls at the same village. They are not described in more detail in a readable source.""",
 "gorobi": TOUR.format(n="the Gorobi rock formations") + """, at Gorobi village in Mayo Belwa LGA. The same commission's festival table names Gorobi as a home of the Phuki and Here-Yawetti festivals of the Yandang.""",
 "ruwanzafi": TOUR.format(n="Ruwan Zafi Spring") + """, a natural hot spring in Lamurde LGA. 'Ruwan zafi' is Hausa for 'hot water'. The spring is not described in more detail in a readable source.""",
 "tapichima": TOUR.format(n="the Tapichima cold-water spring") + """, a natural spring at Pella town in Hong LGA. It is not described in more detail in a readable source.""",
}
REC_P = [
    ("sukur", dict(place_type="heritage_site", name="Sukur Cultural Landscape", slug="sukur-cultural-landscape", admin_unit_id=A("madagali"), status="existing"), ["UNESCO", "NCMML", "NCMMM", "ADSPC"], "verified"),
    ("sukuric", dict(place_type="museum", name="Sukur Interpretation Centre", slug="sukur-interpretation-centre", admin_unit_id=A("madagali"), status="existing"), ["NCMMM"], "verified"),
    ("yola", dict(place_type="museum", name="National Museum Yola", slug="national-museum-yola", admin_unit_id=A("yola-north"), status="existing"), ["NCMMM"], "verified"),
    ("fombina", dict(place_type="museum", name="National Museum Fombina", slug="national-museum-fombina", admin_unit_id=A("yola-south"), status="existing"), ["NCMMM", "ADSPC"], "well_documented"),
    ("hong", dict(place_type="museum", name="National Museum Hong", slug="national-museum-hong", admin_unit_id=A("hong"), status="existing"), ["NCMMM"], "verified"),
    ("elephant", dict(place_type="historical_place", name="Elephant House, Guyuk", slug="elephant-house-guyuk", admin_unit_id=A("guyuk"), status="existing"), ["NCMMP", "ADSPC"], "well_documented"),
    ("sisters", dict(place_type="natural_feature", name="Three Sisters Rock", slug="three-sisters-rock", admin_unit_id=A("song"), status="existing"), ["NCMMP", "ADSPC"], "well_documented"),
    ("makam", dict(place_type="historical_place", name="Makam Walls", slug="makam-walls", admin_unit_id=A("toungo"), status="existing"), ["ADSPC"], "reported"),
    ("hamayaji", dict(place_type="historical_place", name="Hamayaji Old Palace", slug="hamayaji-old-palace", admin_unit_id=A("madagali"), status="existing"), ["ADSPC"], "reported"),
    ("koma", dict(place_type="hill_or_mountain", name="Koma Hills", slug="koma-hills", admin_unit_id=A("jada"), status="existing"), ["ADSPC"], "reported"),
    ("sassa", dict(place_type="natural_feature", name="Sassa Waterfalls", slug="sassa-waterfalls", admin_unit_id=A("toungo"), status="existing"), ["ADSPC"], "reported"),
    ("yadim", dict(place_type="natural_feature", name="Yadim Waterfalls", slug="yadim-waterfalls", admin_unit_id=A("fufore"), status="existing"), ["ADSPC"], "reported"),
    ("sella", dict(place_type="natural_feature", name="Sella Negis Waterfalls", slug="sella-negis-waterfalls", admin_unit_id=A("fufore"), status="existing"), ["ADSPC"], "reported"),
    ("gorobi", dict(place_type="natural_feature", name="Gorobi Rock Formations", slug="gorobi-rock-formations", admin_unit_id=A("mayo-belwa"), status="existing"), ["ADSPC"], "reported"),
    ("ruwanzafi", dict(place_type="natural_feature", name="Ruwan Zafi Spring", slug="ruwan-zafi-spring", admin_unit_id=A("lamurde"), status="existing"), ["ADSPC"], "reported"),
    ("tapichima", dict(place_type="natural_feature", name="Tapichima Spring", slug="tapichima-spring", admin_unit_id=A("hong"), status="existing"), ["ADSPC"], "reported"),
]

# ---------------------------------------------------------------- festivals (ADSPC table, in its order)
MON = {1: "January", 2: "February", 3: "March", 4: "April", 5: "May", 6: "June", 7: "July", 8: "August", 9: "September", 10: "October", 11: "November", 12: "December"}
# (No., name, slug, other names, locality, people as written, [people slugs], m1, m2, duration, [lga] or [] = state, significance or "", extra sentence, lga note)
FEST = [
 (1, "Zhita", "zhita", [], "Bazza", "Higgi", ["kamwe"], 8, 9, "three days", ["michika"], "an initiation festival", "", ""),
 (2, "Yawle", "yawle", [], "Kwabbapale", "Higgi", ["kamwe"], 8, 8, "three days", ["michika"], "a festival marking the harvest season", "", ""),
 (3, "Dukwa", "dukwa", [], "Gulak, Shuwa, Palam and Mildu", "Marghi", ["marghi"], 7, 8, "three days", ["madagali"], "the initiation of the young into adulthood among the Medugu people", "", ""),
 (4, "Yawal", "yawal", [], "Gulak, Shuwa, Palam and Mildu", "Marghi", ["marghi"], 6, 7, "three days", ["madagali"], "a festival of sacrifice to Mambul, the spirits of the dead, and the period of circumcision", "", ""),
 (5, "Vunon", "vunon", [], "Farai", "Bwatiye and Mbula", ["bachama", "mbula"], 5, 5, "three days", ["demsa"],
  "a festival marking the beginning of the rainy season, with prayers to the deity Nzeanzo for good health and a bumper harvest, and marked by wrestling", "", ""),
 (6, "Mba-Pur", "mba-pur", [], "Demsa", "Mbula", ["mbula"], 4, 4, "three days", ["demsa"],
  "a warrior festival commemorating the death of their heroes and assuring their warlord of their readiness against any external attack", "", ""),
 (7, "Hono", "hono", [], "Song and Girei", "Yungur", ["yungur"], 11, 12, "one day", ["song", "girei"], "an initiation festival into adulthood", "", ""),
 (8, "Kwayfa", "kwayfa", [], "Ga'anda", "Ga'anda", ["ga-anda"], 8, 9, "two days", ["gombi"], "a harvest festival", "", ""),
 (9, "Khombalta", "khombalta", [], "Ga'anda and Hadi", "Hona and Fulani", ["hwana", "fulani"], 3, 4, "one day", ["gombi"], "a thanksgiving and initiation festival",
  " The table writes the people as 'Hona Fulani', read here as the Hona (Hwana) and the Fulani, as for the Bwatiye–Fulani festival Ngpalakiyie.", ""),
 (10, "Klashe Shatin", "klashe-shatin", [], None, "Chamba and Mumuye", ["chamba", "mumuye"], 8, 9, "seven days", ["jada"], "",
  " The row in the table is damaged: the peoples appear in the locality column and the LGA is repeated in place of the festival's significance, so its purpose is not recorded.", ""),
 (11, "Poodeh", "poodeh", [], "Nassarawo", "Verre (in Nassarawo)", ["verre"], 11, 12, "two days", ["fufore"], "circumcision rites", "", ""),
 (12, "Mbele", "mbele", ["Rivadu"], "Ribadu", "Bwatiye", ["bachama"], 11, 12, "one day", ["fufore"], "a fishing festival", " The table names it 'Mbele/Rivadu'.", ""),
 (13, "Simalama", "simalama", [], "Guyuk", "Lunguda", ["lunguda"], 10, 10, "three days", ["guyuk"], "a celebration marking the end of the rainy season", "", ""),
 (14, "Imandirza Zinna", "imandirza-zinna", [], "Gyalla", "Muchala", ["fali"], 11, 12, "three days", ["mubi-north"], "an initiation festival",
  " The table gives the people as 'Muchala', read here, as in the archive's peoples batch, as the Fali of Muchella.", ""),
 (15, "Wangirwa", "wangirwa", [], "Mubi", "Gude", ["gude"], 10, 11, "one day", ["mubi-south"], "a festival commemorating a bumper harvest", "", ""),
 (16, "Mba Mba", "mba-mba", [], "the old village of Madagali", "Kilba and Marghi", ["kilba", "marghi"], 3, 4, "seven days", ["hong", "madagali"], "an initiation festival into adulthood",
  " The table gives the people as 'Kilba Marghi' and the LGA as 'Hong Madagali'.", ""),
 (17, "Tiwa", "tiwa", [], "Hong", "Kilba", ["kilba"], 3, 4, "120 days", ["hong"],
  "a funeral festival with libation, incantation and sacrifice to the ancestors, and drumming, singing and dancing", "", ""),
 (18, "Alalile", "alalile", [], "Nzanyi", "Nzanyi", ["nzanyi"], None, None, "one day", ["maiha"], "an initiation of children into adulthood", " The table gives no month.", ""),
 (19, "Phuki", "phuki", [], "Kudaku and Gorobi", "Yandang", ["yandang"], 9, 9, "one day", ["mayo-belwa"], "a harvest and thanksgiving festival", "", ""),
 (20, "Menjauli", "menjauli", [], "Shelleng", "Kanakuru", ["dera"], 10, 10, "one day", ["shelleng"], "a festival marking the end of the rainy season",
  " The Kanakuru are the people Blench's Atlas calls Dera.", ""),
 (21, "Lamushi", "lamushi", [], "Libbo", "Libbo", ["kaan"], 7, 7, "one day", ["shelleng"], "an initiation for males and females",
  " The Libbo are linked to the archive's Kaan record on the strength of the name only (the Atlas gives 'Libo' as a name of Kaan).", ""),
 (22, "Vulma", "vulma", [], "Gella", "Gude", ["gude"], 8, 8, "two days", ["mubi-south"], "a display of manhood by the different age groups", "", ""),
 (23, "Danbi", "danbi", [], "Mijulu", "Fali", ["fali"], 8, 8, "one day", [], "a festival marking the first initiation into manhood",
  "", "The table gives the LGA as 'Mubi', which was divided into Mubi North and Mubi South, so only the state is linked."),
 (24, "Janda", "janda", ["Sumanta"], "Ga'anda", "Ga'anda", ["ga-anda"], 6, 6, "two days", ["gombi"], "a festival of preparation for farming", " The table names it 'Janda/Sumanta'.", ""),
 (25, "Shadi", "shadi", [], "Gombi", "Fulani", ["fulani"], 6, 6, "seven days", [], "a festival of engagement, or of showing manhood",
  "", "The table gives the locality as Gombi but the LGA as 'Yola', which is now divided into Yola North and Yola South, so only the state is linked."),
 (26, "Njuwa", "njuwa", [], "Rugange", "Bwatiye", ["bachama"], 3, 3, "two days", [], "an annual fishing festival",
  "", "The table gives the LGA as 'Yola', which is now divided into Yola North and Yola South, so only the state is linked."),
 (27, "Ngpalakiyie", "ngpalakiyie", ["Sharo"], "Njoboli", "Bwatiye and Fulani", ["bachama", "fulani"], 5, 6, "fourteen days", [], "a circumcision festival and initiation into adulthood",
  " The table names it 'Ngpalakiyie/Sharo'.", "The table gives the LGA as 'Yola', which is now divided into Yola North and Yola South, so only the state is linked."),
 (28, "Sorro", "sorro", [], "Yola", "Fulani", ["fulani"], 2, 2, "one day", [], "a festival marking manhood",
  "", "The table gives the LGA as 'Yola', which is now divided into Yola North and Yola South, so only the state is linked."),
 (29, "Remnol", "remnol", [], "Njoboli", "Fulani", ["fulani"], 12, 12, "one day", [], "a circumcision festival",
  "", "The table gives the LGA as 'Yola', which is now divided into Yola North and Yola South, so only the state is linked."),
 (30, "Kilisa", "kilisa", [], "Yola", "Fulani", ["fulani"], None, None, "one day", [], "a festival for entertaining important dignitaries visiting the emirate council",
  " The table gives no month.", "The table gives the LGA as 'Yola', which is now divided into Yola North and Yola South, so only the state is linked."),
 (31, "Yinagu", "yinagu", [], "Gulak", "Margi", ["marghi"], 4, 4, "two days", ["madagali"], "a fishing festival", "", ""),
 (32, "Mba Beng", "mba-beng", [], "Tambo", "Mbula", ["mbula"], 10, 12, "", ["girei"], "a harvest and thanksgiving festival", " The table gives no duration.", ""),
 (33, "Zoku", "zoku", [], "Sukur", "Sukur", ["sukur"], 12, 12, "two days", ["madagali"], "a festival celebrating a bumper harvest", "", ""),
 (34, "Jabba", "jabba", [], "Demsa", "Bille", ["bile"], 12, 12, "three days", ["demsa"], "a circumcision festival", "", ""),
 (35, "Ngrah Lakiye", "ngrah-lakiye", [], "Fufore", "Bagale", [], 6, 6, "three days", ["fufore"], "a circumcision festival",
  " The archive has no record for the Bagale, so no people is linked.", ""),
 (36, "Mba-Tambal", "mba-tambal", [], "Bwazza", "Bare", ["bwazza"], 4, 4, "seven days", ["demsa"], "a festival for the protection of the land and its people",
  " The Bare are the people of Bwazza (Blench's Atlas gives 'Bare' as a name of Bwazza).", ""),
 (37, "Mba-Zonhwo", "mba-zonhwo", [], "Bwazza", "Bare", ["bwazza"], 10, 10, "seven days", ["demsa"], "a festival for a good harvest and the protection of the people", "", ""),
 (38, "Here-Yawetti", "here-yawetti", [], "Gorobi", "Yandang", ["yandang"], 5, 5, "one day", ["mayo-belwa"], "a festival rewarding the spirits of the ancestors", " The table gives its date as the first Saturday (of May).", ""),
 (39, "Dzava-Dafa", "dzava-dafa", [], "Sukur and Hildi", "Sukur", ["sukur"], 4, 5, "", ["madagali"], "a festival of thanks for a bumper harvest",
  " The table gives no duration.", "The table gives no LGA; Sukur is in Madagali LGA (UNESCO)."),
 (40, "Wagurwa", "wagurwa", [], "Nzanyi", "Nzanyi", ["nzanyi"], 2, 3, "three days", ["maiha"], "an initiation rite into adulthood", "", ""),
 (41, "Kadyaga", "kadyaga", [], "Numan", "Bachama", ["bachama"], 4, 4, "one day", ["numan"], "a festival of manhood", "", ""),
 (42, "Wurokakai", "wurokakai", [], "Lamurde", "Bachama", ["bachama"], 3, 3, "one day", ["lamurde"], "a festival of manhood", "", ""),
 (43, "Kwatte", "kwatte", [], "Lamurde", "Bachama", ["bachama"], 4, 5, "three days", ["lamurde"], "a festival marking the preparation of the land", "", ""),
]
PNAME = {"kamwe": "Kamwe", "marghi": "Marghi", "bachama": "Bachama", "mbula": "Mbula", "yungur": "Yungur", "ga-anda": "Ga'anda", "hwana": "Hwana", "fulani": "Fulani",
         "chamba": "Chamba", "mumuye": "Mumuye", "verre": "Verre", "lunguda": "Lunguda", "fali": "Fali", "gude": "Gude", "kilba": "Kilba", "nzanyi": "Nzanyi",
         "yandang": "Yandang", "dera": "Dera", "kaan": "Kaan", "sukur": "Sukur", "bile": "Ɓile", "bwazza": "Bwazza"}
WEAK = {"kaan", "fali"}  # people links resting on a name reading (057b)


def months(m1, m2):
    if not m1:
        return ""
    return MON[m1] if m1 == m2 else f"{MON[m1]}–{MON[m2]}"


def lga_text(lgas):
    if not lgas:
        return ""
    names = [LGA[l] for l in lgas]
    return " and ".join(names) + (" LGAs" if len(names) > 1 else " LGA")


def ftext(f):
    no, name, slug, alts, loc, ppl_txt, ppl, m1, m2, dur, lgas, sig, extra, lnote = f
    where = (f"at {loc}" if loc else "") + (f", in {lga_text(lgas)}" if loc else f"in {lga_text(lgas)}") if lgas and not lnote.startswith("The table gives no LGA") else f"at {loc}"
    when = (f" in {MON[m1]}" if m1 == m2 else f" between {MON[m1]} and {MON[m2]}") if m1 else ""
    t = f"The Adamawa State Planning Commission lists {name} among the state's popular festivals: a festival of the {ppl_txt}, held {where}{when}" + (f" and lasting {dur}" if dur else "") + "."
    if sig:
        t += f" The table describes it as {sig}."
    t += extra
    if lnote:
        t += " " + lnote
    return t


def fsummary(f):
    no, name, slug, alts, loc, ppl_txt, ppl, m1, m2, dur, lgas, sig, extra, lnote = f
    return f"{name} is a festival of the {ppl_txt}, held " + (f"at {loc}" if loc else f"in {lga_text(lgas)}") + (f" in {months(m1, m2)}" if m1 else "") + (f": {sig}." if sig else ".")


RECORDS, RELATIONS, NAMES = [], [], []
for key, fields, srcs, lvl in REC_P:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
for f in FEST:
    no, name, slug, alts, loc, ppl_txt, ppl, m1, m2, dur, lgas, sig, extra, lnote = f
    fd = dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name=name, local_name=name, slug=slug,
              timing=("Annually, " + months(m1, m2)) if m1 else "Annually (month not given)", current_status="unknown",
              scope_level="ethnic_group" if ppl else "community", summary=fsummary(f), description=ftext(f))
    if m1:
        fd.update(month_from=m1, month_to=m2)
    RECORDS.append(dict(key=f"f{no}", table="cultural_records", evidence="single_reliable_source", level="reported", fields=fd,
                        srcs=[("ADSPC", f"Popular Festivals No. {no}: {name}")]))
    for p in ppl:
        RELATIONS.append(dict(frm=f"f{no}", type="celebrated_by", to=f"@ethnic_groups:{p}", source="ADSPC", evidence="single_reliable_source", level="reported",
                              notes=f"Ethnic group in the state's table (No. {no}): '{ppl_txt}'" + (" — read as this record by name only (batch 057b)." if p in WEAK else ".")))
    targets = lgas if lgas else [None]
    for l in targets:
        RELATIONS.append(dict(frm=f"f{no}", type="celebrated_in", to=A(l) if l else ST, source="ADSPC", evidence="single_reliable_source", level="reported",
                              notes=(lnote or f"Locality {loc or '(not given)'}; LGA as given in the state's table (No. {no}).")))
    for a in alts:
        NAMES.append(dict(record=f"f{no}", name=a, name_type="alternative", usage_notes=f"The state's table writes '{name}/{a}'.", srcs=["ADSPC"]))

RELATIONS += [
    dict(frm="sukur", type="associated_with", to="@ethnic_groups:sukur", role="homeland of the Sukur", source="UNESCO", evidence="multiple_sources", level="well_documented",
         notes="UNESCO (the Sukur community, the Hidi-in-Council); ADSPC ('Sukur Kingdom')."),
    dict(frm="sukuric", type="part_of", to="sukur", source="NCMMM", evidence="single_reliable_source", level="reported", notes="NCMM 'Interpretation Centre, Sukur'."),
    dict(frm="fombina", type="associated_with", to="@polities:adamawa-emirate", role="housed in the Lamido's palace", source="NCMMM", evidence="multiple_sources", level="well_documented",
         notes="NCMM ('Lamido of Adamawa Palace'); ADSPC ('Lamido's Palace, Yola South LGA'). NCMM gives the address as 'Jimeta, Yola'; ADSPC places it in Yola South."),
    dict(frm="elephant", type="associated_with", to="@ethnic_groups:lunguda", role="of great significance to the Lunguda", source="ADSPC", evidence="single_reliable_source", level="reported",
         notes="ADSPC tourist table."),
    dict(frm="koma", type="associated_with", to="@ethnic_groups:koma", role="home of the Koma", source="ADSPC", evidence="single_reliable_source", level="reported",
         notes="ADSPC: 'Home to historic Koma People'."),
    dict(frm="hamayaji", type="associated_with", to="@ethnic_groups:marghi", role="history of a Marghi king", source="ADSPC", evidence="single_reliable_source", level="reported",
         notes="ADSPC: 'Houses history of the ... King of the Marghi people'."),
    dict(frm="f30", type="associated_with", to="@polities:adamawa-emirate", role="entertainment of the emirate council's guests", source="ADSPC", evidence="single_reliable_source", level="reported",
         notes="'visiting the emirate council' at Yola (No. 30); the Adamawa Emirate is the emirate seated at Yola."),
]
NAMES += [
    dict(record="fombina", name="Fombina Palace Museum", name_type="alternative", usage_notes="Adamawa State Planning Commission, tourist table.", srcs=["ADSPC"]),
    dict(record="sukur", name="Sukur Kingdom", name_type="alternative", usage_notes="Adamawa State Planning Commission, tourist table.", srcs=["ADSPC"]),
]
GAPS = [
    ("Adamawa: 'Gumti Park', Toungo", "The state's tourist table lists 'Gumti Park' in Toungo LGA. It is very probably the Gumti sector of Gashaka-Gumti National Park (existing record), but no source read says so; not linked."),
    ("Adamawa: Kiri Dam and Dzugumi", "Kiri Dam (Shelleng) is modern infrastructure and not recorded; the table does not say what Dzugumi (Pella, Hong) is."),
    ("Adamawa: museums and monuments", "The NCMM lists the four museums and the two proposed monuments without dates or descriptions; the Elephant House's age and form, and the history of the Makam Walls and Hamayaji palace, need a source. National Museum Fombina: NCMM says 'Jimeta, Yola', the state says Yola South (recorded in Yola South)."),
    ("Adamawa: festival rites, dates and places", "The state's festival table gives one line per festival and has damaged rows (No. 10); six festivals are placed only in 'Yola' or 'Mubi'. Rites, history and current practice need ethnographic or local sources. Whether each festival is still held is unknown (current status left 'unknown')."),
    ("Adamawa: Bagale people", "Ngrah Lakiye (No. 35) is a festival of the Bagale of Fufore; the archive has no Bagale record."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Adamawa culture and heritage: Sukur (UNESCO; declared No. 5), four NCMM museums, two proposed monuments, nine state tourist sites, 43 festivals from the state's table.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 060 — Adamawa: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **{len(REC_P)} places:**",
         "  - the **Sukur Cultural Landscape**: a UNESCO World Heritage Site (1999) and Adamawa's only *declared* national monument (No. 5). Its text is drawn from UNESCO's own description.",
         "  - the **four NCMM museums**: National Museum Yola, National Museum Fombina (in the Lamido's palace), National Museum Hong, and the Sukur Interpretation Centre",
         "  - **two proposed national monuments:** the Elephant House in Guyuk (No. 18) and the Three Sisters Rock in Song (No. 87)",
         "  - **nine sites from the state's tourist table:** the Makam Walls, Hamayaji Old Palace, the Koma Hills, the Sassa, Yadim and Sella Negis waterfalls, the Gorobi rock formations, and the Ruwan Zafi and Tapichima springs",
         f"- **{len(FEST)} festivals:** the whole of the state's 'Popular Festivals' table. Each is linked to its people and LGA, and each text says only what the table says.",
         "  - Six festivals are given only 'Yola' or 'Mubi', both now divided, so they are linked to the state only.",
         "  - The damaged row No. 10 (Klashe Shatin) is recorded without a purpose.",
         "  - Ngrah Lakiye (Bagale) has no people link, because the archive has no Bagale record.",
         "- **Links:**",
         "  - Sukur to the Sukur people",
         "  - the Fombina museum and the Kilisa festival to the Adamawa Emirate",
         "  - the Elephant House to the Lunguda, the Koma Hills to the Koma, and the Hamayaji palace to the Marghi",
         "- **Not recorded:** Kiri Dam, 'Gumti Park' (probably part of the existing Gashaka-Gumti record, but unproven) and Dzugumi. These are listed as gaps.",
         "- Only Sukur's text may reach 300 words, so all the others are noindex.", "",
         "## Places", ""]
    for key, fields, srcs, lvl in REC_P:
        t = TEXT[key]
        L += [f"### {fields['name']} — {LGA[fields['admin_unit_id'].split('/')[-1]]} ({words(t)} words; {lvl})", "", f"> {t}", ""]
    L += ["## Festivals", "", "| No. | Festival | People (table → archive) | Where | When | LGA link |", "|---|---|---|---|---|---|"]
    for f in FEST:
        no, name, slug, alts, loc, ppl_txt, ppl, m1, m2, dur, lgas, sig, extra, lnote = f
        L.append(f"| {no} | {name} | {ppl_txt} → {', '.join(PNAME[p] for p in ppl) or '—'} | {loc or '—'} | {months(m1, m2) or '—'} | {lga_text(lgas) or 'state only'} |")
    L += ["", "### Festival texts", ""] + [f"**{f[1]}.** {ftext(f)}\n" for f in FEST]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_060_adamawa_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_060_adamawa_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
