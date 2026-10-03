"""
Research batch 114 — Lagos (Phase 3): culture and heritage. Researched 2026-10-02. Pattern: batches 090, 102, 108.

NCMM declared list: No. 44 Ilojo Bar, Lagos Island; No. 45 Iga Idunganran (Oba's Old Palace), Lagos Island; No. 46
Water House, Lagos Island; No. 47 Old Secretariat, Marina.
NCMM proposed list (hand count, calibrated: Elephant House 18, Kusugu 25, Yakoko 66, Namoda 76, Dutse Bamle 81 — 'Imo
state' in lower case must not merge two entries): Architectural — 12 Christ Church Cathedral (CMS) Marina; 13
Independence Building, Lagos Island; 14 Shitta Bey Mosque, 'Surulere'; 19 National Arts Theatre, Iganmu. Historic —
39 King's College, Lagos Island; 41 First Storey Building in Nigeria, Badagry; 42 Brazilian Baracoon Museum/Point of
No Return, Badagry; 43 CMS Grammar School, Bariga; 44 Iddo Railway Terminus Building; 45 Tafawa Balewa Square; 46 the
site of Gen. Murtala Mohammed's assassination, 'Lagos Island'.
NCMM museums: National Museum Lagos, King George V Road, Onikan.
Wikipedia articles (one per site, below) give the descriptions. Placement: an LGA only where a source names the place
(Lagos Island, Surulere/Iganmu, Badagry); otherwise the state. Conflicts kept side by side: Shitta-Bey Mosque
(Wikipedia: Martins Ereko Street, Lagos Island, 'designated as National monument … in 2013'; NCMM: proposed, 'Surulere');
the Murtala site (NCMM: 'Lagos Island'; Wikipedia: 'near the Federal Secretariat at Ikoyi').
Also: the Vlekete slave market (Wikipedia), the Eyo festival and Zangbeto (Wikipedia; Badagry).
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_113_lagos_institutions as I113

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMML": dict(H66.SOURCES["NCMML"], notes="Reused. Lagos: No. 44 Ilojo Bar, Lagos Island; No. 45 Iga Idunganran (Oba's Old Palace), Lagos Island; No. 46 Water House, Lagos Island; No. 47 Old Secretariat, Marina."),
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Lagos: Architectural 12 Christ Church Cathedral (CMS) Marina, 13 Independence Building, 14 Shitta Bey Mosque ('Surulere'), 19 National Arts Theatre Iganmu; Historic 39 King's College, 41 First Storey Building Badagry, 42 Brazilian Baracoon Museum/Point of No Return Badagry, 43 CMS Grammar School Bariga, 44 Iddo Railway Terminus Building, 45 Tafawa Balewa Square, 46 site of Gen. Murtala Mohammed's assassination."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. National Museum Lagos, King George V Road, Onikan, P.M.B. 12556, Lagos State."),
    "WIGA": I113.SOURCES["WIGA"], "WOBA": I113.SOURCES["WOBA"],
    "WBAD": dict(I113.SOURCES["WBAD"], notes="Reused. Slave barracoons built by Brazilian merchants under Chief Sunbu Mobee (about 40); the Badagry Heritage Museum; the first two-storey structure built at Marina, Badagry, in 1845; Freeman's pre-fabricated house considered by some the first storey building; the Ajido Zangbeto festival; Zangbeto among the Ogu (Egun)."),
    "WILO": WS("Ilojo Bar", "Also Olaiya House or Casa da Fernandez, near Tinubu Square; said to date from 1855, but two halves built 1903–1914 (van Zeijl); sold to A. O. Olaiya in 1933; declared a national monument in 1956; demolished 11 September 2016."),
    "WWAT": WS("Water House", "Brazilian-style residential building on Kakawa Street, Lagos Island, built in the 19th century; owned and inhabited by Candido Da Rocha."),
    "WCCC": WS("Cathedral Church of Christ, Lagos", "Anglican cathedral on Lagos Island; established 1869; present building begun 1924 and completed 1946; Ajayi Crowther's relics translated there in 1976; the oldest Anglican cathedral of the Church of Nigeria."),
    "WIND": WS("Independence House", "25-storey office building west of Tafawa Balewa Square, Onikan; commissioned by the British government for Nigeria's independence in 1960; Defence House under Babangida; fire in 1993."),
    "WSHB": WS("Shitta-Bey Mosque", "Martins Ereko Street, Lagos Island; built 1891–94, financed by Mohammed Shitta, Brazilian architect João Baptista da Costa; opened 4 July 1894; 'designated as National monument … in 2013'."),
    "WNT": WS("National Theatre, Nigeria", "Iganmu, Surulere; completed 1976 for FESTAC '77; renamed the Wole Soyinka Centre for Culture and Creative Arts in July 2024."),
    "WKC": WS("King's College, Lagos", "Boys' secondary school founded 20 September 1909 on Lagos Island, adjacent to Tafawa Balewa Square; land given by Oba Eshugbayi Eleko."),
    "WFSB": WS("First Storey Building in Nigeria", "Built by the Church Missionary Society between 1842 and 1845; a tourist site in Badagry."),
    "WCMS": WS("CMS Grammar School, Lagos", "In Bariga; the oldest secondary school in Nigeria, founded 6 June 1859 by the Church Missionary Society."),
    "WLTR": WS("Lagos Terminus railway station", "Also Lagos Iddo; the main railway station of Lagos until 2021, on Iddo Island near Carter Bridge; Nigeria's first railway line started in Lagos in 1898."),
    "WTBS": WS("Tafawa Balewa Square", "14.5-hectare ceremonial ground on Lagos Island, formerly the Race Course; land given by Oba Dosunmu in 1859; independence celebrations of 1960; square built in 1972."),
    "WMUR": WS("Murtala Muhammed", "Head of state 1975–76; assassinated on 13 February 1976 in his car in traffic 'near the Federal Secretariat at Ikoyi in Lagos' during an abortive coup."),
    "WNM": WS("Nigerian National Museum", "At Onikan, Lagos Island; founded in 1957 by Kenneth Murray; administered by the NCMM; reopened in April 2026 after an overhaul."),
    "WVLE": WS("Velekete Slave Market", "Vlekete slave market in Badagry, said to have been established in 1502 and named after the Vlekete deity; a market where African middlemen sold captives to European traders."),
    "WEYO": WS("Eyo festival", "Also the Adamu Orisha Play; a Yoruba festival unique to Lagos, with roots in Iperu-Remo (Ogun State); performed on Lagos Island; held to escort the soul of a departed king or chief and to usher in a new king; procession from Idumota to Iga Idunganran."),
    "WZAN": WS("Zangbeto", "Vodún guardians of the night among the Ogu (Egun) of Benin, Togo and Nigeria; a traditional police and security institution; the name means 'men of the night' in Gun."),
}
NCMM_D = "is No. {n} on the National Commission for Museums and Monuments' list of declared national monuments"
NCMM_P = "is No. {n} on the National Commission for Museums and Monuments' list of proposed national monuments, in the '{c}' category"
TEXT = {
 "ilojo": f"""Ilojo Bar, also called Olaiya House or Casa da Fernandez, was a Brazilian-style building near Tinubu Square on Lagos Island, and {NCMM_D.format(n=44)}. It was long said to have been built in 1855 by the Fernandez family with craftsmen who had returned from Brazil and Cuba, but research by Femke van Zeijl found two halves built between 1903 and 1914 (Wikipedia). Alfred Omolana Olaiya bought it in 1933 and named it after his hometown, Ilojo; Wikipedia dates its declaration as a monument to 1956. It was pulled down on 11 September 2016 in circumstances Wikipedia calls suspicious, and the land is now controlled by the Lagos State Government.""",
 "iga": f"""Iga Idunganran, the palace of the Oba of Lagos on Lagos Island, {NCMM_D.format(n=45)} (as 'Iga Idunganran (Oba's Old Palace)'). According to Wikipedia, the old palace was built in 1670 for Oba Gabaro on land that Chief Aromire had farmed for pepper, and its name means 'the palace built on a pepper farm'; it was later refurbished with materials, especially tiles, brought from Portugal. The modern part of the complex was commissioned on 1 October 1960 by the Prime Minister, Sir Abubakar Tafawa Balewa, and modernised again in 2007 and 2008. It is the venue of the Eyo festival.""",
 "water": f"""The Water House, on Kakawa Street, Lagos Island, {NCMM_D.format(n=46)}. Built in the 19th century, during the time of the Lagos Colony, it is one of the few remaining houses in the Brazilian style brought to Lagos by returnees, and was owned and lived in by Candido Da Rocha (Wikipedia). Its present condition is not described in a source read.""",
 "secretariat": f"""The Old Secretariat at Marina, Lagos, {NCMM_D.format(n=47)}. It is not described in a source read.""",
 "cathedral": f"""The Cathedral Church of Christ, Marina, the Anglican cathedral on Lagos Island, {NCMM_P.format(n=12, c='Architectural')} (as 'Christ Church Cathedral (CMS) Marina'). According to Wikipedia, the cathedral was established in 1869; the present building was begun in 1924, its foundation stone laid by the Prince of Wales in 1925, and completed in 1946. The relics of Samuel Ajayi Crowther, the first African Anglican bishop, were moved there in 1976. It is the oldest Anglican cathedral of the Church of Nigeria and the seat of the Bishop of Lagos.""",
 "independence": f"""Independence House, the tower west of Tafawa Balewa Square at Onikan, Lagos Island, {NCMM_P.format(n=13, c='Architectural')} (as 'Independence Building'). According to Wikipedia, it was commissioned by the British government as a gesture of goodwill for Nigeria's independence in 1960, and is a reinforced-concrete office building of about 25 storeys. Under the Babangida government it housed the Defence headquarters and was known as Defence House; part of it burned in 1993.""",
 "shitta": f"""The Shitta-Bey Mosque, on Martins Ereko Street, Lagos Island, {NCMM_P.format(n=14, c='Architectural')}; the NCMM list places it in Surulere, and Wikipedia says it was designated a national monument in 2013. Built from 1891 under the Brazilian architect João Baptista da Costa and financed by Mohammed Shitta, it opened on 4 July 1894 in the presence of the Governor and Oba Oyekan I, when the Ottoman Sultan's envoy conferred on Shitta the title Bey (Wikipedia). It is one of the oldest mosques in southern Nigeria.""",
 "theatre": f"""The National Theatre at Iganmu, Surulere, {NCMM_P.format(n=19, c='Architectural')} (as 'National Arts Theatre'). According to Wikipedia, it is Nigeria's main centre for the performing arts, completed in 1976 for the Festival of Arts and Culture (FESTAC) of 1977, and was renamed the Wole Soyinka Centre for Culture and Creative Arts in July 2024. Its design follows the Palace of Culture and Sports in Varna, Bulgaria.""",
 "kings": f"""King's College, Lagos, {NCMM_P.format(n=39, c='Historic')}. A boys' secondary school, it was founded on 20 September 1909 with ten students on Lagos Island, next to Tafawa Balewa Square, on land given by Oba Eshugbayi Eleko; Henry Carr's proposals to Governor Walter Egerton were the basis for it (Wikipedia).""",
 "storey": f"""The First Storey Building in Nigeria, at Badagry, {NCMM_P.format(n=41, c='Historic')}. According to Wikipedia, it was built by missionaries of the Church Missionary Society between 1842 and 1845 and is one of the tourist sites of Badagry; Wikipedia's Badagry article dates the first two-storey structure at Marina, Badagry, to 1845, and notes that some consider a pre-fabricated house built by Thomas Birch Freeman the first storey building.""",
 "baracoon": f"""The Brazilian Barracoon Museum and the Point of No Return, at Badagry, are {NCMM_P.format(n=42, c='Historic')[3:]}. According to Wikipedia, Badagry's barracoons, where enslaved people were held before shipment, were built by Brazilian slave merchants on the order of Chief Sunbu Mobee; about 40 once stood at the site, each holding up to 40 people, and a few remain. Badagry was a slave port from 1736, at its peak until 1789. The 'Point of No Return' itself is not described in a source read.""",
 "cms": f"""The CMS Grammar School at Bariga, Lagos, {NCMM_P.format(n=43, c='Historic')}. Founded on 6 June 1859 by the Church Missionary Society, with seed money from James Pinson Labulo Davies, it is the oldest secondary school in Nigeria and was for decades the main source of African clergy and administrators in the Lagos Colony (Wikipedia).""",
 "iddo": f"""The Iddo railway terminus building, Lagos, {NCMM_P.format(n=44, c='Historic')}. Lagos Terminus, also called Lagos Iddo, stands on Iddo Island in front of Carter Bridge and was the main railway station of Lagos until 2021; Nigeria's first railway line started in Lagos in 1898 (Wikipedia).""",
 "tbs": f"""Tafawa Balewa Square, Lagos Island, {NCMM_P.format(n=45, c='Historic')}, as the memorial site of Nigeria's independence. According to Wikipedia, the 14.5-hectare ceremonial ground was the colonial Race Course, on land given by Oba Dosunmu in 1859; it was redeveloped in 1960 for the independence celebrations, and the square was built over the old track in 1972.""",
 "murtala": f"""The site of General Murtala Muhammed's assassination, in Lagos, {NCMM_P.format(n=46, c='Historic')}; the NCMM list places it on Lagos Island. Murtala Muhammed, head of state from July 1975, was killed on 13 February 1976 when soldiers of an abortive coup ambushed his car in traffic near the Federal Secretariat at Ikoyi (Wikipedia). The site itself is not described in a source read.""",
 "museum": """The National Museum Lagos, also called the Nigerian National Museum, is at King George V Road, Onikan, on Lagos Island, and is run by the National Commission for Museums and Monuments. It was founded in 1957 by the archaeologist Kenneth Murray and holds collections of Nigerian art, statuary and carvings, and archaeological and ethnographic exhibits; Wikipedia reports that it reopened in April 2026 after a major overhaul.""",
 "vlekete": """The Vlekete slave market, in Badagry, is a former market of the Atlantic slave trade, named after the Vlekete deity of the ocean and wind; Wikipedia says it was established in 1502. There African middlemen sold captives to European traders, and Wikipedia ties its growth to the trading post of the Dutch trader Hendrik Hertog, known locally as Huntokonu. A shrine of Vlekete stood in the Posuko quarter of Badagry (Wikipedia).""",
 "eyo": """The Eyo festival, also called the Adamu Orisha Play, is a Yoruba festival unique to Lagos and traditionally performed on Lagos Island, with historical roots in Iperu-Remo in Ogun State (Wikipedia). It was held to escort the soul of a departed king or chief of Lagos and to usher in a new king; on Eyo Day the main road from Carter Bridge to Tinubu Square is closed for a procession from Idumota to the Iga Idunganran palace. The white-clad Eyo masquerades represent spirits of the dead. Today it is also presented as a tourist event.""",
 "zangbeto": """Zangbeto are the night guardians of the Ogu (Egun) people of Badagry and of Benin and Togo: a Vodún masquerade and traditional police institution that patrols at night to watch over people and property and to track down thieves (Wikipedia). The name means 'men of the night' in the Gun language. The masquerade, covered in raffia or hay, dances by spinning, shrinking and growing in height; in Badagry, the Ajido Zangbeto festival is supported by the state (Wikipedia, Badagry).""",
}
LG = lambda l: f"@admin_units:lga:lagos/{l}"
ST = "@admin_units:state:lagos"
REC = [
    ("ilojo", "places", dict(place_type="historical_place", name="Ilojo Bar", slug="ilojo-bar", admin_unit_id=LG("lagos-island"), status="destroyed"), ["NCMML", "WILO"], "well_documented"),
    ("iga", "places", dict(place_type="heritage_site", name="Iga Idunganran", slug="iga-idunganran", admin_unit_id=LG("lagos-island"), status="existing"), ["NCMML", "WIGA"], "well_documented"),
    ("water", "places", dict(place_type="historical_place", name="Water House, Lagos", slug="water-house-lagos", admin_unit_id=LG("lagos-island"), status="existing"), ["NCMML", "WWAT"], "well_documented"),
    ("secretariat", "places", dict(place_type="historical_place", name="Old Secretariat, Marina", slug="old-secretariat-marina", admin_unit_id=ST, status="unknown"), ["NCMML"], "verified"),
    ("cathedral", "places", dict(place_type="sacred_site", name="Cathedral Church of Christ, Marina", slug="cathedral-church-of-christ-marina", admin_unit_id=LG("lagos-island"), status="existing"), ["NCMMP", "WCCC"], "well_documented"),
    ("independence", "places", dict(place_type="historical_place", name="Independence House, Lagos", slug="independence-house-lagos", admin_unit_id=LG("lagos-island"), status="existing"), ["NCMMP", "WIND"], "well_documented"),
    ("shitta", "places", dict(place_type="sacred_site", name="Shitta-Bey Mosque", slug="shitta-bey-mosque", admin_unit_id=LG("lagos-island"), status="existing"), ["NCMMP", "WSHB"], "well_documented"),
    ("theatre", "places", dict(place_type="institution", name="National Theatre, Iganmu", slug="national-theatre-iganmu", admin_unit_id=LG("surulere"), status="existing"), ["NCMMP", "WNT"], "well_documented"),
    ("kings", "places", dict(place_type="institution", name="King's College, Lagos", slug="kings-college-lagos", admin_unit_id=LG("lagos-island"), status="existing"), ["NCMMP", "WKC"], "well_documented"),
    ("storey", "places", dict(place_type="historical_place", name="First Storey Building in Nigeria, Badagry", slug="first-storey-building-badagry", admin_unit_id=LG("badagry"), status="existing"), ["NCMMP", "WFSB", "WBAD"], "well_documented"),
    ("baracoon", "places", dict(place_type="heritage_site", name="Brazilian Barracoon and Point of No Return, Badagry", slug="brazilian-barracoon-point-of-no-return-badagry", admin_unit_id=LG("badagry"), status="existing"), ["NCMMP", "WBAD"], "well_documented"),
    ("cms", "places", dict(place_type="institution", name="CMS Grammar School, Bariga", slug="cms-grammar-school-bariga", admin_unit_id=ST, status="existing"), ["NCMMP", "WCMS"], "well_documented"),
    ("iddo", "places", dict(place_type="historical_place", name="Iddo Railway Terminus", slug="iddo-railway-terminus", admin_unit_id=ST, status="existing"), ["NCMMP", "WLTR"], "well_documented"),
    ("tbs", "places", dict(place_type="monument", name="Tafawa Balewa Square", slug="tafawa-balewa-square", admin_unit_id=LG("lagos-island"), status="existing"), ["NCMMP", "WTBS"], "well_documented"),
    ("murtala", "places", dict(place_type="historical_place", name="Murtala Muhammed Assassination Site", slug="murtala-muhammed-assassination-site", admin_unit_id=ST, status="existing"), ["NCMMP", "WMUR"], "well_documented"),
    ("museum", "places", dict(place_type="museum", name="National Museum Lagos", slug="national-museum-lagos", admin_unit_id=LG("lagos-island"), status="existing"), ["NCMMM", "WNM"], "well_documented"),
    ("vlekete", "places", dict(place_type="historical_place", name="Vlekete Slave Market", slug="vlekete-slave-market", admin_unit_id=LG("badagry"), status="historical"), ["WVLE", "WBAD"], "reported"),
    ("eyo", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Eyo Festival", slug="eyo-festival",
                                     timing="Held on special occasions, traditionally for the passing of a king or chief and a new king's accession", current_status="active", scope_level="community"), ["WEYO", "WIGA"], "well_documented"),
    ("zangbeto", "cultural_records", dict(record_type="belief_ritual", cultural_category="knowledge_belief", nature="contemporary_practice", name="Zangbeto", slug="zangbeto",
                                          current_status="active", scope_level="ethnic_group"), ["WZAN", "WBAD"], "well_documented"),
]
RECORDS = []
for key, table, fields, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table=table, evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="iga", type="associated_with", to="@polities:kingdom-of-lagos", role="palace of the Oba of Lagos", source="WIGA", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Iga Idunganran; Oba of Lagos); NCMM No. 45."),
    dict(frm="eyo", type="celebrated_by", to="@ethnic_groups:yoruba", source="WEYO", evidence="single_reliable_source", level="well_documented", notes="Wikipedia: 'a Yoruba festival unique to Lagos'."),
    dict(frm="eyo", type="celebrated_in", to=LG("lagos-island"), source="WEYO", evidence="single_reliable_source", level="well_documented", notes="Wikipedia: 'traditionally performed on Lagos Island'."),
    dict(frm="eyo", type="associated_with", to="iga", source="WEYO", evidence="multiple_sources", level="well_documented", notes="Wikipedia: the procession ends at the Iga Idunganran palace, the festival's venue."),
    dict(frm="@polities:kingdom-of-lagos", type="associated_with", to="eyo", role="the festival escorts a departed king and ushers in a new one", source="WEYO", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Eyo festival)."),
    dict(frm="zangbeto", type="practised_by", to="@ethnic_groups:ogu", source="WZAN", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Zangbeto; Badagry): the night guardians of the Ogu (Egun)."),
    dict(frm="zangbeto", type="celebrated_in", to=LG("badagry"), source="WBAD", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Badagry): the Ajido Zangbeto festival; Zangbeto among the Ogu (Egun) of Badagry."),
    dict(frm="baracoon", type="associated_with", to="@polities:badagry-kingdom", role="slave barracoons of the Badagry port", source="WBAD", evidence="single_reliable_source", level="reported", notes="Wikipedia (Badagry)."),
    dict(frm="vlekete", type="associated_with", to="@polities:badagry-kingdom", role="slave market of the Badagry port", source="WVLE", evidence="single_reliable_source", level="reported", notes="Wikipedia (Velekete Slave Market; Badagry)."),
]
NAMES = [
    dict(record="ilojo", name="Olaiya House", name_type="alternative", usage_notes="Wikipedia.", srcs=["WILO"]),
    dict(record="ilojo", name="Casa da Fernandez", name_type="historical", usage_notes="Wikipedia.", srcs=["WILO"]),
    dict(record="cathedral", name="Christ Church Cathedral (CMS) Marina", name_type="alternative", usage_notes="The NCMM's name (proposed list, No. 12).", srcs=["NCMMP"]),
    dict(record="independence", name="Independence Building", name_type="alternative", usage_notes="The NCMM's name (proposed list, No. 13).", srcs=["NCMMP"]),
    dict(record="theatre", name="Wole Soyinka Centre for Culture and Creative Arts", name_type="official", usage_notes="Name since July 2024 (Wikipedia).", srcs=["WNT"]),
    dict(record="theatre", name="National Arts Theatre", name_type="alternative", usage_notes="The NCMM's name (proposed list, No. 19).", srcs=["NCMMP"]),
    dict(record="museum", name="Nigerian National Museum", name_type="alternative", usage_notes="Wikipedia's name.", srcs=["WNM"]),
    dict(record="iddo", name="Lagos Terminus", name_type="alternative", usage_notes="Also 'Lagos Iddo' (Wikipedia).", srcs=["WLTR"]),
    dict(record="vlekete", name="Velekete", name_type="spelling_variant", usage_notes="Wikipedia's article title ('Velekete Slave Market').", srcs=["WVLE"]),
    dict(record="eyo", name="Adamu Orisha Play", name_type="alternative", usage_notes="Wikipedia.", srcs=["WEYO"]),
]
GAPS = [
    ("Lagos: Shitta-Bey Mosque", "Wikipedia says it was designated a national monument in 2013 and places it on Lagos Island; the NCMM's list has it as proposed and in Surulere. Both are kept; NCMM confirmation is needed."),
    ("Lagos: undescribed sites", "The Old Secretariat, Marina, and the 'Point of No Return' at Badagry are not described in a source read; the LGAs of the Old Secretariat, CMS Grammar School (Bariga), the Iddo terminus and the Murtala site (NCMM 'Lagos Island'; Wikipedia 'near the Federal Secretariat at Ikoyi') are not stated, so they are placed at state level."),
    ("Lagos: Ilojo Bar", "Demolished on 11 September 2016; Wikipedia says the matter is still being investigated. Its legal status as a monument after demolition is not stated."),
    ("Lagos: other festivals and sites", "Wikipedia names the Oro, Igunnu, Egungun and Olojo festivals and the Badagry heritage museums (Seriki Faremi Williams Abass Museum, Badagry Heritage Museum) without enough detail for records."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Lagos culture and heritage: 4 declared and 11 proposed NCMM monuments, the National Museum Lagos, the Vlekete slave market, the Eyo festival and Zangbeto.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 114 — Lagos: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **{sum(1 for r in REC if r[1] == 'places')} places:**",
         "  - **4 declared national monuments:** Ilojo Bar (demolished in 2016, recorded as *destroyed*), Iga Idunganran, the Water House and the Old Secretariat",
         "  - **11 proposed monuments:** Christ Church Cathedral, Independence House, the Shitta-Bey Mosque, the National Theatre, King's College, the First Storey Building and the Barracoon/Point of No Return at Badagry, CMS Grammar School, the Iddo terminus, Tafawa Balewa Square and the Murtala Muhammed assassination site",
         "  - the **National Museum Lagos** and the **Vlekete slave market** (*reported*)",
         "- **2 cultural records:** the **Eyo festival** (Lagos Island, Yoruba, tied to the Kingdom of Lagos and Iga Idunganran) and **Zangbeto**, the Ogu night guardians of Badagry",
         "- **Iga Idunganran as the Kingdom of Lagos's seat:** the importer cannot update polities, so this will be set in the Lagos QC fix.",
         "- **Handled with care:**",
         "  - **Conflicting sources are kept side by side.** The Shitta-Bey Mosque: Wikipedia says it was declared in 2013 and is on Lagos Island; NCMM lists it as proposed and in Surulere. The Murtala site: NCMM says Lagos Island; Wikipedia says Ikoyi.",
         "  - **Placement:** a place goes in an LGA only where a source names it. Otherwise it is placed at state level.",
         "  - **Proposed-list numbers are counted by hand** and checked against five earlier numbers. A lower-case 'Imo state' had merged two entries.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, table, fields, srcs, lvl in REC:
        L += [f"## {fields['name']} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_114_lagos_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_114_lagos_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
