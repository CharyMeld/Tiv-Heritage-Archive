"""
Research batch 084 — Gombe (Phase 3): culture and heritage. Researched 2026-10-02. Pattern: batches 060, 066, 072, 078.

Places (6):
  * two proposed national monuments (NCMM 'Historic', page order: Kirfin Sama 63, Tula Prison Yard 64, Mbormi 65,
    Yakoko 66 — checks out). Gombe has no declared national monument.
      - Tula Prison Yard, Gombe State (no description found; state link only).
      - Mbormi Battle Ground, 'Bajoga LGA' (Bajoga is now the headquarters of Funakaye LGA), including the tombs of
        Sultan Attahiru and F.C. Marsh — the battle of Burmi, 27 July 1903 (Wikipedia, 'Burmi campaign';
        'Muhammadu Attahiru I'). Wikipedia calls the officer Major Charles Marsh; the NCMM writes F.C. Marsh.
  * National Museum Gombe (NCMM: Gombe Federal Secretariat Complex, rooms 289–294).
  * The Emir of Gombe's Palace (Wikipedia, 'Gombe Emirate': built by Shehu Usman Abubakar, 10th Emir, 1984–2014;
    administrative headquarters of the emirate; a key tourist attraction).
  * Dadin Kowa Dam, Yamaltu/Deba LGA (Wikipedia).
  * Muri Mountains (Wikipedia): along the boundaries of Bauchi, Gombe, Taraba and Adamawa states.
Cultural records (3): Pissi (Pishi) Tangale festival and the 'Bai' dog festival (Wikipedia, 'Tangale people' and
'Billiri'); Kamo Cultural Festival, Kaltungo (Wikipedia, 'Kaltungo', citing NICO). All 'reported'.
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_083_gombe_institutions as I83

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Gombe (Historic): No. 64 'Tula Prison Yard, Gombe State'; No. 65 'Mbormi Battle Ground, Bajoga LGA (including tombs of Sultan Attahiru and F.C. Marsh), Gombe State'."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. National Museum Gombe, Gombe Federal Secretariat Complex, Rm 289–294, 2nd floor, Gombe."),
    "WBUR": WS("Burmi campaign", "1903 British campaign against the Sokoto Caliphate; Burmi, a town in the Gombe Emirate; final battle on 27 July 1903; Major Charles Marsh killed by a poison arrow; Muhammadu Attahiru I killed; over 600 defenders killed and the town burnt."),
    "WATT": WS("Muhammadu Attahiru I", "Twelfth Sultan of the Sokoto Caliphate, October 1902 to March 1903; the last independent sultan; fled Sokoto on a hijra."),
    "WGE": I83.SOURCES["WGE"],
    "ATLAS": I83.SOURCES["ATLAS"],
    "WGOM": I83.SOURCES["WGOM"],
    "WFUN": I83.SOURCES["WFUN"],
    "WDKD": WS("Dadin Kowa Dam", "In Yamaltu/Deba LGA, about 37 km east of Gombe; completed 1984 by the federal government for irrigation and electricity; reservoir designed for 2.8 billion m³, about 300 km²; 26,000 people displaced; drinking water for Gombe."),
    "WMUR": WS("Muri Mountains", "Two near-parallel sandstone chains along the boundaries of Bauchi, Gombe, Taraba and Adamawa states; about twenty small ethnic groups (1992), including the Kushi, Pero, Tangale, Loo, Burak, Bangwinji, Dadiya, Cham, Tsobo, Waja and Longuda."),
    "WTAN": WS("Tangale people", "Festivals: Pissi Tangale festival; 'Bai' Carnival / Palam Tangle (Dog festival); Eku festival; Tangra; Wula; Pe Kodok; Pand Kungo."),
    "WBIL": I83.SOURCES["WBIL"],
    "WKAL": dict(I83.SOURCES["WKAL"], notes="Reused. Cultural festivals celebrated in the area: Pan-Mana, Kamo Cultural Festival (citing NICO), Tangale (Dog) Festival, Eku, Pid Tungo, Kaltungo Carnival, Wula, Pand Tungo."),
}
TEXT = {
 "tula": """The Tula Prison Yard is No. 64 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Gombe State has no declared national monument. The NCMM names only the state; Tula itself lies in Kaltungo LGA (Blench's Atlas). The prison yard's date, history and condition are not described in a readable source.""",
 "mbormi": """The Mbormi battle ground, including the tombs of Sultan Attahiru and F.C. Marsh, is No. 65 on the National Commission for Museums and Monuments' list of proposed national monuments, which places it in 'Bajoga LGA'; Bajoga is now the headquarters of Funakaye LGA (Wikipedia). It is the site of the battle of Burmi, a town of the Gombe Emirate, on 27 July 1903, the end of the British campaign against the Sokoto Caliphate (Wikipedia). Muhammadu Attahiru I, the twelfth Sultan of Sokoto, who had fled Sokoto after its capture, was killed there with many of his followers, and the British commander, Major Marsh, died of a poisoned arrow during the assault; according to British reports over 600 defenders were killed and the town was burnt (Wikipedia). Attahiru's son, Muhammad Bello (Mai Wurno), led survivors east to Sudan.""",
 "museum": """The National Museum Gombe is one of the national museums of the National Commission for Museums and Monuments (NCMM), which gives its address as rooms 289 to 294 on the second floor of the Gombe Federal Secretariat Complex. Its history and collections are not described in a readable source.""",
 "palace": """The Emir of Gombe's Palace, in Gombe, is the administrative headquarters of the Gombe Emirate. According to Wikipedia it was built by Shehu Usman Abubakar, the 10th Emir, who reigned from January 1984 until May 2014, and it has long been regarded as a key tourist attraction and as the state's most impressive building. Its date of construction and design are not described in more detail in a readable source.""",
 "dam": """The Dadin Kowa Dam is in Yamaltu/Deba LGA, about 37 km east of Gombe and 5 km from Dadin Kowa village (Wikipedia). The federal government completed it in 1984 to provide irrigation and electricity for a planned sugar plantation; its reservoir, Lake Dadin Kowa on the Gongola River, was designed to hold 2.8 billion cubic metres over about 300 km², and Wikipedia calls it the country's second largest dam. About 26,000 people were displaced by the reservoir with little help to resettle. A water supply project built by a Chinese company now provides drinking water for Gombe (Wikipedia).""",
 "muri": """The Muri Mountains are two nearly parallel sandstone chains running east to west along the boundaries of Bauchi, Gombe, Taraba and Adamawa states, merging with the Longuda plateau to the east and the Bauchi plateau to the west (Wikipedia). Their mountainous terrain and seasonal flooding have kept the area hard to reach and economically marginal. About twenty small ethnic groups lived in and around the mountains in 1992, among them the Kushi, Pero, Tangale, Loo, Burak, Bangwinji, Dadiya, Cham, Tsobo, Waja and Longuda, speaking Chadic, Adamawa and Benue–Congo languages (Wikipedia).""",
 "pissi": """The Pissi Tangale festival, also written Pishi Tangle, is a festival of the Tangale people (Wikipedia). Wikipedia's article on Billiri describes 'Pishi Tangle Day' as one of the major festivals of Tangale land, bringing together Tangale people in Gombe State and around the world to celebrate their culture and heritage and to promote peaceful co-existence. Its date and rites are not described in a readable source.""",
 "bai": """The 'Bai' carnival, also called Palam Tangle or the dog festival, is a festival of the Tangale people (Wikipedia, 'Tangale people'); Wikipedia's article on Billiri calls it the 'Bai dog meat festival', and its article on Kaltungo lists a Tangale (Dog) Festival among the area's festivals. Its date, rites and current practice are not described in a readable source.""",
 "kamo": """The Kamo Cultural Festival is held in Kaltungo LGA, according to Wikipedia's article on Kaltungo, which cites the National Institute for Cultural Orientation (NICO). The Kamo are a people of Kaltungo and Akko LGAs. The festival's date and course are not described in a readable source.""",
}
G = lambda l: f"@admin_units:lga:gombe/{l}"
ST = "@admin_units:state:gombe"
REC = [
    ("tula", "places", dict(place_type="historical_place", name="Tula Prison Yard", slug="tula-prison-yard", admin_unit_id=ST, status="existing"), ["NCMMP", "ATLAS"], "verified"),
    ("mbormi", "places", dict(place_type="historical_place", name="Mbormi Battle Ground", slug="mbormi-battle-ground", admin_unit_id=G("funakaye"), status="historical"), ["NCMMP", "WBUR", "WATT", "WFUN"], "well_documented"),
    ("museum", "places", dict(place_type="museum", name="National Museum Gombe", slug="national-museum-gombe", admin_unit_id=G("gombe"), status="existing"), ["NCMMM"], "verified"),
    ("palace", "places", dict(place_type="heritage_site", name="Emir of Gombe's Palace", slug="emir-of-gombes-palace", admin_unit_id=G("gombe"), status="existing"), ["WGE"], "reported"),
    ("dam", "places", dict(place_type="natural_feature", name="Dadin Kowa Dam", slug="dadin-kowa-dam", admin_unit_id=G("yamaltu-deba"), status="existing"), ["WDKD", "WGOM"], "reported"),
    ("muri", "places", dict(place_type="hill_or_mountain", name="Muri Mountains", slug="muri-mountains", admin_unit_id=ST, status="existing"), ["WMUR"], "reported"),
    ("pissi", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Pissi Tangale Festival",
                                       slug="pissi-tangale-festival", timing="Not stated", current_status="unknown", scope_level="ethnic_group"), ["WTAN", "WBIL"], "reported"),
    ("bai", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Bai Carnival (Tangale Dog Festival)",
                                     slug="bai-carnival-tangale", timing="Not stated", current_status="unknown", scope_level="ethnic_group"), ["WTAN", "WBIL", "WKAL"], "reported"),
    ("kamo", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Kamo Cultural Festival",
                                      slug="kamo-cultural-festival", timing="Not stated", current_status="unknown", scope_level="ethnic_group"), ["WKAL", "ATLAS"], "reported"),
]
RECORDS = []
for key, table, fields, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table=table, evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="mbormi", type="associated_with", to="@polities:gombe-emirate", role="Burmi, a town of the Gombe Emirate", source="WBUR", evidence="single_reliable_source", level="reported",
         notes="Wikipedia (Burmi campaign): 'the town of Burmi in the Gombe Emirate'."),
    dict(frm="palace", type="associated_with", to="@polities:gombe-emirate", role="administrative headquarters of the emirate", source="WGE", evidence="single_reliable_source", level="reported",
         notes="Wikipedia (Gombe Emirate): 'The Gombe Palace serves as Gombe Emirate's administrative headquarters'."),
    dict(frm="muri", type="located_in", to="@admin_units:state:bauchi", source="WMUR", evidence="single_reliable_source", level="reported", notes="Along the boundaries of Bauchi, Gombe, Taraba and Adamawa states (Wikipedia)."),
    dict(frm="muri", type="located_in", to="@admin_units:state:taraba", source="WMUR", evidence="single_reliable_source", level="reported", notes="Along the boundaries of Bauchi, Gombe, Taraba and Adamawa states (Wikipedia)."),
    dict(frm="muri", type="located_in", to="@admin_units:state:adamawa", source="WMUR", evidence="single_reliable_source", level="reported", notes="Along the boundaries of Bauchi, Gombe, Taraba and Adamawa states (Wikipedia)."),
    dict(frm="pissi", type="celebrated_by", to="@ethnic_groups:tangale", source="WTAN", evidence="multiple_sources", level="reported", notes="Wikipedia (Tangale people; Billiri)."),
    dict(frm="bai", type="celebrated_by", to="@ethnic_groups:tangale", source="WTAN", evidence="multiple_sources", level="reported", notes="Wikipedia (Tangale people; Billiri; Kaltungo)."),
    dict(frm="kamo", type="celebrated_by", to="@ethnic_groups:kamo", source="WKAL", evidence="single_reliable_source", level="reported", notes="Named after the Kamo; held in Kaltungo LGA (Wikipedia, citing NICO)."),
]
NAMES = [
    dict(record="mbormi", name="Burmi", name_type="alternative", usage_notes="The town's name in Wikipedia ('Burmi campaign'); the NCMM writes Mbormi.", srcs=["WBUR"]),
    dict(record="dam", name="Lake Dadin Kowa", name_type="alternative", usage_notes="The reservoir (Wikipedia, Gombe State and Dadin Kowa Dam).", srcs=["WDKD"]),
    dict(record="pissi", name="Pishi Tangle Day", name_type="spelling_variant", usage_notes="Wikipedia (Billiri).", srcs=["WBIL"]),
    dict(record="bai", name="Palam Tangle", name_type="alternative", usage_notes="Wikipedia (Tangale people).", srcs=["WTAN"]),
]
GAPS = [
    ("Gombe: Tula Prison Yard", "Named by the NCMM without description; its date, location within Kaltungo LGA and history need a source."),
    ("Gombe: Mbormi/Burmi", "The NCMM writes Mbormi and 'F.C. Marsh'; Wikipedia writes Burmi and 'Major Charles Marsh'. The tombs' condition is not described."),
    ("Gombe: festivals", "Wikipedia lists further Tangale festivals (Eku, Tangra, Wula, Pe Kodok, Pand Kungo) and Kaltungo festivals (Pan-Mana, Pid Tungo, Kaltungo Carnival) by name only; festivals of the Tera, Waja, Tula, Dadiya, Bolewa and others need sources."),
    ("Gombe: National Museum Gombe and the palace", "Collections, history and the palace's construction date are not described in a source read."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Gombe culture and heritage: Tula Prison Yard and Mbormi (NCMM proposed), National Museum Gombe, the Emir's Palace, Dadin Kowa Dam, Muri Mountains; Pissi Tangale, Bai and Kamo festivals.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 084 — Gombe: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **6 places:**",
         "  - **two proposed national monuments** (Gombe has no *declared* one):",
         "    - the **Tula Prison Yard** (No. 64): named only",
         "    - the **Mbormi (Burmi) battle ground** (No. 65), with the tombs of Sultan Attahiru I and Major Marsh. Here the British campaign against the Sokoto Caliphate ended on 27 July 1903.",
         "  - the **National Museum Gombe**",
         "  - the **Emir of Gombe's Palace**",
         "  - the **Dadin Kowa Dam** (Yamaltu/Deba)",
         "  - the **Muri Mountains**, which span Bauchi, Gombe, Taraba and Adamawa",
         "- **3 cultural records:** the **Pissi Tangale festival**, the Tangale **'Bai' dog festival** and the **Kamo Cultural Festival**. Each is *reported*: Wikipedia names them but gives no dates or rites.",
         "- **Links:** Mbormi and the palace to the Gombe Emirate; the festivals to the Tangale and Kamo; the Muri Mountains to the three neighbouring states.",
         "- **Page length:** check the word counts below. A page over 300 words would be indexable.", ""]
    for key, table, fields, srcs, lvl in REC:
        L += [f"## {fields['name']} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_084_gombe_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_084_gombe_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
