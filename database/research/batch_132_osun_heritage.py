"""
Research batch 132 — Osun (Phase 3): culture and heritage. Researched 2026-10-02. Pattern: batches 114, 126.

NCMM declared list: 56 'River-Side Shrine and Sacred Grove of Osun, Osogbo'; 57 'Shrine of Osun in the King's Market at
Osogbo'; 58 'Shrine of Osun at Afin Ata-Oja (Palace), Osogbo'; 59 'Ita Yemoo at Ile-Ife'; 60 'Carved Stone Figure at
Igbajo'. NCMM proposed list (calibrated count; Kusugu 25, Dutse Bamle 81): Architectural 15 'Ifa Temple, Ile-Ife';
Historic 47 'Oranmiyan Staff (granite stele obelisk)'; Natural 83 'Erin Ijesa (Oluminrin) Water Falls, Erin Ijesa'. NCMM
museums: National Museum Ile-Ife (Enuwa Square); National Museum Osogbo (Ataoja's Palace).
Wikipedia: 'Osun-Osogbo' (sacred grove outside Osogbo; UNESCO World Heritage Site 2005; Susanne Wenger and the New Sacred
Art movement; the festival in August; the Olutimehin tradition); 'Erin-Ijesha Waterfalls' (Olumirin; Erin-odo, Oriade
LGA; seven levels; Akinla tradition); 'Ọranyan' (the Opa Oranmiyan, a 5.5 m obelisk erected, in tradition, where he
died); 'Igbajo' (Boluwaduro LGA; ten stone markers erected, in tradition, by ten monarchs; the treaty of 23 September 1886).
Placement: the falls in Oriade and the Igbajo figure in Boluwaduro (both named by Wikipedia); the Osogbo and Ile-Ife
sites at state level (each city spans two LGAs and the sites' LGAs are not given).
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_131_osun_institutions as I131

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMML": dict(H66.SOURCES["NCMML"], notes="Reused. Osun: 56 River-Side Shrine and Sacred Grove of Osun, Osogbo; 57 Shrine of Osun in the King's Market at Osogbo; 58 Shrine of Osun at Afin Ata-Oja (Palace), Osogbo; 59 Ita Yemoo at Ile-Ife; 60 Carved Stone Figure at Igbajo."),
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Osun: Architectural 15 Ifa Temple, Ile-Ife; Historic 47 Oranmiyan Staff (granite stele obelisk); Natural 83 Erin Ijesa (Oluminrin) Water Falls."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. Osun: National Museum Ile-Ife (Enuwa Square, Ile-Ife); National Museum Osogbo (Ataoja's Palace, Osogbo)."),
    "WOSG": WS("Osun-Osogbo", "Sacred grove on the Osun river outside Osogbo; several centuries old; UNESCO World Heritage Site 2005; desecration in the 1950s; Susanne Wenger (1915–2009) and the New Sacred Art movement, with the Ataoja's support; the Osun-Osogbo festival in August; the Olutimehin founding tradition."),
    "WERI": WS("Erin-Ijesha Waterfalls", "Olumirin waterfalls at Erin-odo, Oriade LGA; seven levels; discovered, in tradition, by Akinla, founder of Erin-Ijesa; a sacred site where festivals and sacrifices were formerly held."),
    "WORA": WS("Ọranyan", "The Staff of Oranmiyan (Opa Oranmiyan), an obelisk 5.5 m tall erected, in tradition, where Oranmiyan died; damaged in a storm in 1884."),
    "WIGJ": WS("Igbajo", "Town in Boluwaduro LGA, Osun State, founded in the 12th century; ten stone markers said to commemorate a meeting of ten monarchs; the treaty that ended the Yoruba wars signed there on 23 September 1886."),
    "WOSO": I131.SOURCES["WOSO"],
}
D = "is No. {n} on the National Commission for Museums and Monuments' list of declared national monuments"
P = "is No. {n} on the National Commission for Museums and Monuments' list of proposed national monuments, in the '{c}' category"
TEXT = {
 "grove": f"""The Osun-Osogbo Sacred Grove, on the banks of the Osun River just outside Osogbo, {D.format(n=56)} (as the 'River-Side Shrine and Sacred Grove of Osun') and was inscribed as a UNESCO World Heritage Site in 2005 (Wikipedia). Several centuries old, it is among the last of the sacred forests that once adjoined most Yoruba towns. After shrines were neglected and the forest damaged in the 1950s, the Austrian artist Susanne Wenger, with the Ataoja's support, led the New Sacred Art movement that restored its protection; she was honoured as Adunni Olorisha. It is the setting of the annual Osun-Osogbo festival.""",
 "market": f"""The shrine of Osun in the King's Market at Osogbo {D.format(n=57)}. It is one of three Osogbo sites on the list devoted to Osun, the river goddess of the town's founding tradition. It is not described in more detail in a source read.""",
 "palace_shrine": f"""The shrine of Osun at the Afin Ata-Oja, the Ataoja's palace in Osogbo, {D.format(n=58)}. The palace also houses the National Museum Osogbo. The shrine is not described in more detail in a source read.""",
 "itayemoo": f"""Ita Yemoo, at Ile-Ife, {D.format(n=59)}. It is not described in a source read.""",
 "igbajo": f"""The carved stone figure at Igbajo, in Boluwaduro LGA, {D.format(n=60)}. Wikipedia records that, according to tradition, Igbajo was the meeting place of ten monarchs on their way to found their kingdoms, who erected ten stone markers that still stand, and that the treaty ending the Yoruba wars was signed there on 23 September 1886. Whether the monument is one of those markers is not stated in a source read.""",
 "ifa": f"""The Ifa temple at Ile-Ife {P.format(n=15, c='Architectural')}. It is not described in a source read.""",
 "opa": f"""The Staff of Oranmiyan, Opa Oranmiyan, at Ile-Ife, {P.format(n=47, c='Historic')} (as the 'Oranmiyan Staff (granite stele obelisk)'). According to Wikipedia, it is an obelisk about 5.5 metres tall, said in tradition to have been raised by Oranmiyan's family where he died; Oranmiyan is remembered as a prince of Ife and the founder of Oyo. Part of it was broken in a storm in 1884.""",
 "falls": f"""The Erin-Ijesa waterfalls, also called Olumirin, at Erin-odo in Oriade LGA, {P.format(n=83, c='Natural')}. According to Wikipedia, the falls descend in seven levels; local tradition says they were found by Akinla, a granddaughter of Oduduwa and founder of Erin-Ijesa, who named them Olumirin, 'another god'. The people regard the falls as sacred, and festivals and sacrifices were once held there; they are now a popular destination for visitors.""",
 "museum_ife": """The National Museum Ile-Ife is one of the national museums of the National Commission for Museums and Monuments, at Enuwa Square, Ile-Ife. Its history and collections are not described in a source read.""",
 "museum_osogbo": """The National Museum Osogbo is one of the national museums of the National Commission for Museums and Monuments, housed at the Ataoja's palace in Osogbo. Its history and collections are not described in a source read.""",
 "festival": """The Osun-Osogbo festival is held every August in the Osun-Osogbo Sacred Grove, honouring Osun, the river goddess, and draws thousands of worshippers, spectators and visitors from around the world (Wikipedia). Tradition holds that it is more than 700 years old: migrants led by the hunter Olutimehin settled by the Osun River, where the goddess appeared and promised to protect them in return for an annual sacrifice, and the people then founded Osogbo.""",
}
ST = "@admin_units:state:osun"
LG = lambda l: f"@admin_units:lga:osun/{l}"
REC = [
    ("grove", "places", dict(place_type="sacred_site", name="Osun-Osogbo Sacred Grove", slug="osun-osogbo-sacred-grove", admin_unit_id=ST, status="existing"), ["NCMML", "WOSG"], "verified"),
    ("market", "places", dict(place_type="sacred_site", name="Shrine of Osun, King's Market, Osogbo", slug="osun-shrine-kings-market-osogbo", admin_unit_id=ST, status="existing"), ["NCMML"], "verified"),
    ("palace_shrine", "places", dict(place_type="sacred_site", name="Shrine of Osun, Ataoja's Palace, Osogbo", slug="osun-shrine-ataoja-palace-osogbo", admin_unit_id=ST, status="existing"), ["NCMML", "NCMMM"], "verified"),
    ("itayemoo", "places", dict(place_type="archaeological_site", name="Ita Yemoo, Ile-Ife", slug="ita-yemoo-ile-ife", admin_unit_id=ST, status="existing"), ["NCMML"], "verified"),
    ("igbajo", "places", dict(place_type="monument", name="Carved Stone Figure, Igbajo", slug="carved-stone-figure-igbajo", admin_unit_id=LG("boluwaduro"), status="existing"), ["NCMML", "WIGJ"], "verified"),
    ("ifa", "places", dict(place_type="sacred_site", name="Ifa Temple, Ile-Ife", slug="ifa-temple-ile-ife", admin_unit_id=ST, status="existing"), ["NCMMP"], "verified"),
    ("opa", "places", dict(place_type="monument", name="Opa Oranmiyan (Staff of Oranmiyan)", slug="opa-oranmiyan", admin_unit_id=ST, status="existing"), ["NCMMP", "WORA"], "well_documented"),
    ("falls", "places", dict(place_type="natural_feature", name="Erin-Ijesa Waterfalls", slug="erin-ijesa-waterfalls", admin_unit_id=LG("oriade"), status="existing"), ["NCMMP", "WERI"], "well_documented"),
    ("museum_ife", "places", dict(place_type="museum", name="National Museum Ile-Ife", slug="national-museum-ile-ife", admin_unit_id=ST, status="existing"), ["NCMMM"], "verified"),
    ("museum_osogbo", "places", dict(place_type="museum", name="National Museum Osogbo", slug="national-museum-osogbo", admin_unit_id=ST, status="existing"), ["NCMMM"], "verified"),
    ("festival", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Osun-Osogbo Festival", slug="osun-osogbo-festival",
                                          timing="Annually in August", current_status="active", scope_level="community"), ["WOSG", "WOSO"], "well_documented"),
]
RECORDS = []
for key, table, fields, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table=table, evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="grove", type="associated_with", to="@polities:ataoja-of-osogbo", role="the sacred grove of Osogbo's founding tradition", source="WOSG", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Osun-Osogbo; Osogbo)."),
    dict(frm="palace_shrine", type="associated_with", to="@polities:ataoja-of-osogbo", role="shrine of Osun at the Ataoja's palace", source="NCMML", evidence="single_reliable_source", level="well_documented", notes="NCMM No. 58."),
    dict(frm="museum_osogbo", type="associated_with", to="@polities:ataoja-of-osogbo", role="national museum housed at the Ataoja's palace", source="NCMMM", evidence="single_reliable_source", level="well_documented", notes="NCMM."),
    dict(frm="opa", type="associated_with", to="@polities:ife-kingdom", role="memorial of Oranmiyan, prince of Ife", source="WORA", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Ọranyan); NCMM proposed No. 47."),
    dict(frm="opa", type="associated_with", to="@polities:oyo-empire", role="memorial of Oranmiyan, founder of Oyo in tradition", source="WORA", evidence="single_reliable_source", level="reported", notes="Wikipedia (Ọranyan; Oyo Empire)."),
    dict(frm="museum_ife", type="associated_with", to="@polities:ife-kingdom", role="national museum at Enuwa Square, Ile-Ife", source="NCMMM", evidence="single_reliable_source", level="reported", notes="NCMM: Enuwa Square, Ile-Ife."),
    dict(frm="festival", type="celebrated_by", to="@ethnic_groups:yoruba", source="WOSG", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Osun-Osogbo)."),
    dict(frm="festival", type="celebrated_in", to=LG("osogbo"), source="WOSG", evidence="multiple_sources", level="well_documented", notes="Osogbo and its sacred grove (Wikipedia)."),
    dict(frm="festival", type="associated_with", to="grove", source="WOSG", evidence="single_reliable_source", level="well_documented", notes="Held in the grove every August (Wikipedia)."),
    dict(frm="@polities:ataoja-of-osogbo", type="associated_with", to="festival", role="the festival of the town whose ruler is the Ataoja", source="WOSO", evidence="multiple_sources", level="reported", notes="Wikipedia (Osogbo: the town is the venue of the festival; the Ataoja is its ruler)."),
]
NAMES = [
    dict(record="grove", name="River-Side Shrine and Sacred Grove of Osun", name_type="official", usage_notes="The NCMM's name (declared list, No. 56).", srcs=["NCMML"]),
    dict(record="falls", name="Olumirin Waterfalls", name_type="alternative", usage_notes="Wikipedia; the NCMM writes 'Oluminrin'.", srcs=["WERI"]),
]
GAPS = [
    ("Osun: undescribed monuments", "Ita Yemoo, the Ifa temple, the two Osun shrines in Osogbo and the Igbajo figure are not described in a source read; the LGAs of the Osogbo and Ile-Ife sites are not given."),
    ("Osun: other festivals and sites", "The Olojo festival of Ile-Ife, the Ife bronze and terracotta heads, the Natural History Museum at Ife and the Igbajo treaty site need sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Osun culture and heritage: 5 declared and 3 proposed NCMM monuments (including the UNESCO Osun-Osogbo Sacred Grove), 2 national museums and the Osun-Osogbo festival.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 132 — Osun: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **10 places:**",
         "  - **5 declared national monuments:** the **Osun-Osogbo Sacred Grove** (UNESCO World Heritage Site, 2005), the Osun shrines in the King's Market and at the Ataoja's palace, Ita Yemoo at Ile-Ife, and the carved stone figure at Igbajo",
         "  - **3 proposed:** the Ifa temple at Ile-Ife, the **Opa Oranmiyan** (Oranmiyan's staff) and the **Erin-Ijesa (Olumirin) waterfalls**",
         "  - the **national museums** at Ile-Ife and Osogbo",
         "- **1 festival:** the **Osun-Osogbo festival** (August), linked to the grove and the Ataoja",
         "- **Links:** the grove, palace shrine and museum go to the Ataoja; the Opa Oranmiyan and the Ife museum to the Ooni. The Opa Oranmiyan is also linked to the Oyo Empire.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, table, fields, srcs, lvl in REC:
        L += [f"## {fields['name']} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_132_osun_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_132_osun_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
