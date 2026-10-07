"""
Research batch 163 — Niger (Phase 3): culture and heritage. Researched 2026-10-07. Pattern: batches 145, 151, 157.

NCMM declared list: 48 'Tsoede's Tomb At Gwagwade, Niger State'; 49 'Site of Mai Jimina's House at Wushishi'; 50 'Ruins
and Site of colonial Government House at Zungeru'; 51 'The Katamba of the Palace of Etsu Nupe, Mohammed at Bida'.
NCMM proposed list (hand count, calibrated: Yakoko 66, Esie 70, Namoda 76, Dutse Bamle 81, Baturiya 94): Historic 67
'Zungeru Colonial Landscape, Zungeru'; 68 'Dabo Mosque, Gulu Lapai'; 69 'All Saints Anglican Church (First Church in
Northern Nigeria) Zungeru'; Natural 88 'Zuma Rock'. NCMM museums: National Museum Minna (Federal Secretariat Complex, Minna).
Wikipedia: 'Zungeru' (capital of Northern Nigeria 1902–1916; Lugard's market still in use); 'Zuma Rock' (Madalla, Suleja
LGA; about 725 m; on the 100-naira note; Gbagyi refuge; Koro settlements and the rock's priest); 'Tsoede' (first Etsu
Nupe; died 1591 on an expedition at an unknown place); 'Kainji National Park' (1978; about 5,341 km²; Niger and Kwara;
operations suspended 2021; co-management agreement 27 October 2023); 'Gurara Waterfalls' (about 30 m; Gurara LGA);
'Nupe Cultural Day' (26 June; the 1896 defeat of the British constabulary); 'Bida Emirate'; 'Lapai' (Nupe Day, Gwari Day).
Placement: Zungeru → Wushishi (INEC ward Zungeru 25-11); Gulu → Lapai (INEC ward Gulu/Anguwa Vatsa 12-05); Madalla →
Suleja (Wikipedia; INEC polling units in ward Hashimi 'B' 23-04); Bida and Wushishi → their LGAs; Gwagwade → state only
(a polling unit named Gwagwade is in Magama, ward Auna East Central 14-02, but no source ties it to the tomb).
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_162_niger_institutions as I162

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMML": dict(H66.SOURCES["NCMML"], notes="Reused. Niger: 48 Tsoede's Tomb at Gwagwade; 49 Site of Mai Jimina's House at Wushishi; 50 Ruins and Site of colonial Government House at Zungeru; 51 The Katamba of the Palace of Etsu Nupe, Mohammed at Bida."),
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Niger: Historic 67 Zungeru Colonial Landscape, Zungeru; 68 Dabo Mosque, Gulu Lapai; 69 All Saints Anglican Church (First Church in Northern Nigeria), Zungeru; Natural 88 Zuma Rock."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. Niger: National Museum Minna, Federal Secretariat Complex, Minna, P.M.B. 538."),
    "WZUN": WS("Zungeru", "Capital of the British protectorate of Northern Nigeria from 1902 until 1916; occupied by British forces in September 1902; chosen by Lugard over Jebba and Lokoja; capital moved to Kaduna in 1916; the market built by Lugard still in use; on the Kaduna River; birthplace of Nnamdi Azikiwe."),
    "WZUMA": WS("Zuma Rock", "Natural monolith (inselberg) of gabbro and granodiorite, about 725 m above sea level, at Madalla in Suleja LGA; on the 100-naira note; a defensive retreat of the Gbagyi; Koro settlements around it and a priest of the rock's deity; the Emir of Abuja sent annual sacrifices."),
    "WTSO": WS("Tsoede", "Tsoede (Tsuedigi, Edegi), c. 1496 – c. 1591, the first to unite the Nupe and considered the first Etsu Nupe; died on a military expedition in 1591 at an unknown location."),
    "WKNP": WS("Kainji National Park", "In Niger and Kwara states; established 1978; about 5,341 km²; three sectors (part of Kainji Lake, the Borgu Game Reserve, the Zugurma Game Reserve); operations suspended in 2021 because of insecurity; 31-year co-management agreement with the West African Conservation Network signed 27 October 2023."),
    "WGUR": WS("Gurara Waterfalls", "In Gurara LGA; about 30 metres high, on the Gurara River along the Suleja–Minna road; in oral history found by a Gwari hunter, Buba, in 1745, and once worshipped by the surrounding communities."),
    "WNCD": WS("Nupe Cultural Day", "Nupe Day, observed on 26 June, marks the defeat of the British constabulary by the Nupe on 26 June 1896 near the Bida camp at Ogidi; led by the Etsu Nupe; prayers, lectures and agricultural merit awards; the 9th edition held at the Etsu Nupe's palace in Bida, 24–26 November, after postponement for Ramadan."),
    "WBIE": dict(I162.SOURCES["WBIE"], notes="Reused. 'Till today, the emirate celebrates its cultural day known as Nupe Cultural Day'."),
    "WLAP": WS("Lapai", "Town and LGA adjoining the FCT, roughly coterminous with the Lapai Emirate; festivals such as Nupe Day and Gwari Day."),
    "INEC": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Directory of Polling Units: Niger State (Revised January 2015)",
                 organisation="Independent National Electoral Commission (INEC)", publication_date="2015-01",
                 url="https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_Niger.pdf", verification_status="verified", notes="Reused (batch 161)."),
}
D = "is No. {n} on the National Commission for Museums and Monuments' list of declared national monuments"
P = "is No. {n} on the National Commission for Museums and Monuments' list of proposed national monuments, in the '{c}' category"
TEXT = {
 "tsoede": f"""The tomb of Tsoede at Gwagwade {D.format(n=48)}. Tsoede, also called Edegi, is remembered as the first ruler to unite the Nupe and is considered the first Etsu Nupe; Wikipedia gives his dates as about 1496 to about 1591 and says he died on a military expedition at an unknown place. The NCMM list places his tomb at Gwagwade. The tomb is not described in a source read, and Gwagwade's LGA is not given.""",
 "jimina": f"""The site of Mai Jimina's house at Wushishi, in Wushishi LGA, {D.format(n=49)}. Who Mai Jimina was, and the history of the house, are not described in a source read.""",
 "govhouse": f"""The ruins and site of the colonial Government House at Zungeru, in Wushishi LGA, {D.format(n=50)}. According to Wikipedia, British forces occupied Zungeru in September 1902, and Frederick Lugard chose it as the capital of the Protectorate of Northern Nigeria over Jebba and Lokoja because of its central location; the capital moved to Kaduna in 1916. Zungeru is a ward of Wushishi LGA in INEC's directory. The building itself is not described in a source read.""",
 "katamba": f"""The katamba of the palace of the Etsu Nupe at Bida {D.format(n=51)}. The NCMM list names it after Etsu Nupe Mohammed. Bida is the seat of the Bida Emirate, whose ruler, the Etsu Nupe, is the leader of the Nupe people (Wikipedia). The structure and its date are not described in a source read.""",
 "zungeru": f"""The Zungeru colonial landscape {P.format(n=67, c='Historic')}. Zungeru, on the Kaduna River in Wushishi LGA, was the capital of the British Protectorate of Northern Nigeria from 1902 until 1916 (Wikipedia). The British cleared the area and built a market, barracks and a hospital, and the market built in Lugard's time is still in use. After 1916 Zungeru was administered from Minna as part of Niger Province. Two other listed sites are in the town: the ruins of the colonial Government House (declared) and All Saints Anglican Church (proposed).""",
 "dabo": f"""The Dabo Mosque at Gulu, in Lapai LGA, {P.format(n=68, c='Historic')}. Gulu is part of the ward Gulu/Anguwa Vatsa in INEC's directory. The mosque's history is not described in a source read.""",
 "allsaints": f"""All Saints Anglican Church at Zungeru, in Wushishi LGA, {P.format(n=69, c='Historic')}. The NCMM list describes it as the first church in Northern Nigeria. Its foundation date is not given in a source read.""",
 "zuma": f"""Zuma Rock, at Madalla in Suleja LGA, {P.format(n=88, c='Natural')}. According to Wikipedia, it is a large monolith of gabbro and granodiorite rising to about 725 metres above sea level, beside the main road from Abuja to Kaduna, and it is pictured on the 100-naira note. The Gbagyi used it as a defensive retreat during wars with their neighbours. Koro settlements grew up around it, and in tradition the rock had a priest, to whom the Emir of Abuja sent annual sacrifices. It was once thought to lie in the Federal Capital Territory.""",
 "museum_minna": """The National Museum Minna is one of the national museums of the National Commission for Museums and Monuments, in the Federal Secretariat Complex, Minna. Its history and collections are not described in a source read.""",
 "kainji": """Kainji National Park, established in 1978, covers about 5,341 km² in Niger and Kwara states (Wikipedia). It has three sectors: part of Kainji Lake, where fishing is restricted; the Borgu Game Reserve west of the lake, the only sector used for tourism; and the Zugurma Game Reserve to the south-east. Lion, leopard, elephant, hippopotamus and the African manatee are among the 65 mammal species recorded. The National Parks Service suspended operations there in 2021 because of insecurity, and in October 2023 the federal government signed a 31-year co-management agreement with the West African Conservation Network.""",
 "gurara": """Gurara Waterfalls, in Gurara LGA, falls about 30 metres on the Gurara River along the Suleja–Minna road (Wikipedia). In oral history it was found by a Gwari (Gbagyi) hunter named Buba in 1745 and was once worshipped by the communities around it; the falls and the river are said to be named after two deities, Gura and Rara.""",
 "nupeday": """Nupe Day, also called Nupe Cultural Day, is held each year on 26 June, led by the Etsu Nupe at Bida (Wikipedia). It marks the Nupe defeat of the British constabulary on 26 June 1896 near the Bida camp at Ogidi, in present Kogi State, when the Nupe cavalry seized the Union Jack. The celebration opens and closes with prayers in mosques and churches and includes lectures on Nupe history and culture and merit awards for agriculture. Its 9th edition was postponed from June to November because of Ramadan.""",
}
ST = "@admin_units:state:niger"
LG = lambda l: f"@admin_units:lga:niger/{l}"
REC = [
    ("tsoede", "places", dict(place_type="monument", name="Tsoede's Tomb, Gwagwade", slug="tsoede-tomb-gwagwade", admin_unit_id=ST, status="existing"), ["NCMML", "WTSO"], "verified"),
    ("jimina", "places", dict(place_type="historical_place", name="Site of Mai Jimina's House, Wushishi", slug="mai-jimina-house-wushishi", admin_unit_id=LG("wushishi"), status="historical"), ["NCMML"], "verified"),
    ("govhouse", "places", dict(place_type="historical_place", name="Ruins of the Colonial Government House, Zungeru", slug="government-house-ruins-zungeru", admin_unit_id=LG("wushishi"), status="historical"), ["NCMML", "WZUN", "INEC"], "verified"),
    ("katamba", "places", dict(place_type="monument", name="Katamba of the Etsu Nupe's Palace, Bida", slug="etsu-nupe-palace-katamba-bida", admin_unit_id=LG("bida"), status="existing"), ["NCMML", "WBIE"], "verified"),
    ("zungeru", "places", dict(place_type="heritage_site", name="Zungeru Colonial Landscape", slug="zungeru-colonial-landscape", admin_unit_id=LG("wushishi"), status="existing"), ["NCMMP", "WZUN", "INEC"], "verified"),
    ("dabo", "places", dict(place_type="sacred_site", name="Dabo Mosque, Gulu", slug="dabo-mosque-gulu", admin_unit_id=LG("lapai"), status="existing"), ["NCMMP", "INEC"], "verified"),
    ("allsaints", "places", dict(place_type="sacred_site", name="All Saints Anglican Church, Zungeru", slug="all-saints-church-zungeru", admin_unit_id=LG("wushishi"), status="existing"), ["NCMMP", "INEC"], "verified"),
    ("zuma", "places", dict(place_type="hill_or_mountain", name="Zuma Rock", slug="zuma-rock", admin_unit_id=LG("suleja"), status="existing"), ["NCMMP", "WZUMA", "INEC"], "verified"),
    ("museum_minna", "places", dict(place_type="museum", name="National Museum Minna", slug="national-museum-minna", admin_unit_id=ST, status="existing"), ["NCMMM"], "verified"),
    ("kainji", "places", dict(place_type="natural_feature", name="Kainji National Park", slug="kainji-national-park", admin_unit_id=ST, status="existing"), ["WKNP"], "reported"),
    ("gurara", "places", dict(place_type="natural_feature", name="Gurara Waterfalls", slug="gurara-waterfalls", admin_unit_id=LG("gurara"), status="existing"), ["WGUR"], "reported"),
    ("nupeday", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Nupe Day", slug="nupe-day",
                                         timing="26 June each year (sometimes postponed)", current_status="unknown", scope_level="community"), ["WNCD", "WBIE", "WLAP"], "reported"),
]
RECORDS = [dict(key=k, table=t, evidence="multiple_sources" if len(s) > 1 else "single_reliable_source", level=lvl,
                fields=dict(f, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k]), srcs=[(x, f["name"]) for x in s]) for k, t, f, s, lvl in REC]
RELATIONS = [
    dict(frm="katamba", type="associated_with", to="@polities:bida-emirate", role="part of the Etsu Nupe's palace", source="NCMML", evidence="multiple_sources", level="well_documented", notes="NCMM; Wikipedia (Bida Emirate)."),
    dict(frm="tsoede", type="associated_with", to="@polities:bida-emirate", role="tomb of Tsoede, held to be the first Etsu Nupe of the old Nupe Kingdom", source="WTSO", evidence="multiple_sources", level="reported", notes="NCMM; Wikipedia (Tsoede; Bida Emirate)."),
    dict(frm="tsoede", type="associated_with", to="@ethnic_groups:nupe", role="tomb of the first ruler to unite the Nupe", source="WTSO", evidence="single_reliable_source", level="reported", notes="Wikipedia (Tsoede)."),
    dict(frm="govhouse", type="associated_with", to="zungeru", role="part of the colonial capital", source="WZUN", evidence="multiple_sources", level="well_documented", notes="NCMM; Wikipedia (Zungeru)."),
    dict(frm="allsaints", type="associated_with", to="zungeru", role="church in the colonial town", source="NCMMP", evidence="single_reliable_source", level="well_documented", notes="NCMM."),
    dict(frm="zuma", type="associated_with", to="@ethnic_groups:gbagyi", role="defensive retreat in wars with neighbours", source="WZUMA", evidence="single_reliable_source", level="reported", notes="Wikipedia (Zuma Rock)."),
    dict(frm="zuma", type="associated_with", to="@ethnic_groups:koro", role="Koro settlements and the rock's priest", source="WZUMA", evidence="single_reliable_source", level="reported", notes="Wikipedia (Zuma Rock)."),
    dict(frm="zuma", type="associated_with", to="@polities:suleja-emirate", role="the Emir of Abuja (Suleja) sent annual sacrifices to the rock's guardians", source="WZUMA", evidence="single_reliable_source", level="reported", notes="Wikipedia (Zuma Rock); the Abuja Emirate is now the Suleja Emirate."),
    dict(frm="gurara", type="associated_with", to="@ethnic_groups:gbagyi", role="in oral history found by a Gwari hunter", source="WGUR", evidence="single_reliable_source", level="reported", notes="Wikipedia (Gurara Waterfalls)."),
    dict(frm="nupeday", type="associated_with", to="@polities:bida-emirate", role="led by the Etsu Nupe", source="WNCD", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Nupe Cultural Day; Bida Emirate)."),
    dict(frm="nupeday", type="celebrated_in", to=LG("bida"), source="WNCD", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Nupe Cultural Day): held at the Etsu Nupe's palace in Bida."),
    dict(frm="nupeday", type="celebrated_in", to=LG("lapai"), source="WLAP", evidence="single_reliable_source", level="reported", notes="Wikipedia (Lapai): Nupe Day among Lapai's festivals."),
    dict(frm="nupeday", type="celebrated_by", to="@ethnic_groups:nupe", source="WNCD", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Nupe Cultural Day; Bida Emirate)."),
]
NAMES = [
    dict(record="nupeday", name="Nupe Cultural Day", name_type="alternative", usage_notes="Wikipedia's article title and the name in 'Bida Emirate'.", srcs=["WNCD", "WBIE"]),
    dict(record="zuma", name="Gateway to Abuja", name_type="alternative", usage_notes="A popular description, 'Gateway to Abuja from Suleja' (Wikipedia).", srcs=["WZUMA"]),
    dict(record="kainji", name="Kainji Lake National Park", name_type="alternative", usage_notes="Name used by the West African Conservation Network project (Wikipedia).", srcs=["WKNP"]),
]
GAPS = [
    ("Niger: undescribed monuments", "Tsoede's tomb, Mai Jimina's house, the Government House ruins, the Etsu Nupe's katamba, the Dabo Mosque, All Saints Church and the Minna museum are not described in a source read; who Mai Jimina was is not known from a source read."),
    ("Niger: Gwagwade", "The NCMM places Tsoede's tomb at Gwagwade; INEC lists a polling unit named Gwagwade in Magama LGA (ward Auna East Central), but no source read ties it to the tomb, so the record is placed at state level. Wikipedia says Tsoede died at an unknown place."),
    ("Niger: Kainji National Park", "The park lies in Niger and Kwara states; the record is placed in Niger only. Whether operations have resumed since the 2021 suspension is not stated in a source read."),
    ("Niger: festivals", "Nupe Day is described from Wikipedia only; a dated news report of a recent edition was not read. Gwari Day (Lapai) and the Nupe Sallah durbars need sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Niger culture and heritage: 4 declared and 4 proposed NCMM monuments (Zungeru, Zuma Rock, Tsoede's tomb, the Etsu Nupe's katamba), the National Museum Minna, Kainji National Park, Gurara Waterfalls and Nupe Day.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 163 — Niger: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **11 places:**",
         "  - **4 declared national monuments:** **Tsoede's tomb** at Gwagwade; the site of **Mai Jimina's house** at Wushishi; the ruins of the **colonial Government House** at Zungeru; the **katamba** of the Etsu Nupe's palace at Bida",
         "  - **4 proposed:** the **Zungeru colonial landscape**; the **Dabo Mosque** at Gulu (Lapai); **All Saints Anglican Church**, Zungeru; **Zuma Rock** (Suleja)",
         "  - the **National Museum Minna**, **Kainji National Park** and **Gurara Waterfalls**. The last two are *reported*, from Wikipedia only.",
         "- **1 festival:** **Nupe Day** (26 June, Bida), *reported*.",
         "- **NCMM numbers:** declared 48–51 are printed on the list. The proposed numbers 67, 68, 69 and 88 were hand-counted and checked against known points (Yakoko 66, Esie 70, Namoda 76, Dutse Bamle 81, Baturiya 94).",
         "- **Not included:** the Kainji, Jebba, Shiroro and Zungeru dams (infrastructure), and Gwari Day (no source read).", ""]
    for k, t, f, s, lvl in REC:
        L += [f"## {f['name']} ({words(TEXT[k])} words; {lvl})", "", f"> {TEXT[k]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_163_niger_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_163_niger_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} sources={len(SOURCES)}")
