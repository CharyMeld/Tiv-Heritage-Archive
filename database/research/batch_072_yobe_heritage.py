"""
Research batch 072 — Yobe (Phase 3): culture and heritage. Researched 2026-10-01. Pattern: batches 054, 060, 066.

Places (6): the ruins of old Ngazargamu (NCMM proposed No. 58), the Dufuna archaeological site and its 8,000-year-old
canoe (No. 59), Goya Gorge and the Ngeji Escarpment (No. 86), the Dagona Bird Sanctuary (No. 99), the National Museum
Damaturu (NCMM), and the Hadejia-Nguru wetlands (Ramsar). Chad Basin National Park (batch 066) gets a Yobe link.
Cultural record (1): the Bade fishing festival (Wikipedia, Bade Emirate — one line, 'reported').

Evidence notes:
  * NCMM proposed list counted in page order (checked against Yakoko 66 and Keana 90): Ngazargamu 58 and Dufuna 59
    ('Historic'), Goya Gorge 86 ('Natural'), Dagona 99 ('Wild life / forest reserve'). Yobe has no declared monument.
  * Dufuna: Wikipedia (Dufuna canoe) — found 4 May 1987 near Dufuna, Fune LGA; 8.4 m; dated 8,500–8,000 years;
    excavated 1994 by a German–Nigerian team; 'currently located in Damaturu' (the museum is not named — not linked).
  * Ngazargamu: Wikipedia — capital of Kanem–Bornu from the 15th century to its destruction in the Fula jihads in the
    early 19th century; near Geidam; c. 1,500 ha; rampart and red bricks visible.
  * Hadejia-Nguru wetlands: Wikipedia — Nguru Lake and the Marma Channel complex (58,100 ha) a Ramsar Site; LGA not stated.
"""
import json, re, sys
import batch_066_borno_heritage as H66

ACCESSED = "2026-10-01"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Yobe: No. 58 Ruins of Old Ngazargamu (Geidam LGA); No. 59 Dufuna Archaeological Site; No. 86 Goya Gorge and Ngeji Escarpment, Fika; No. 99 Dagona Bird Sanctuary, Dagona village, Bade LGA."),
    "NCMMM": dict(H66.SOURCES["NCMMM"], notes="Reused. National Museum Damaturu, behind the Cultural Centre, Maiduguri Road; opposite Ben Kadio Housing Estate."),
    "WNGZ": WS("Ngazargamu", "Capital of the Kanem–Bornu Empire from the 15th century to its destruction in the Fula jihads (early 19th century); near Geidam, 150 km west of Lake Chad; c. 1,500 ha; rampart and red bricks visible."),
    "WDUF": WS("Dufuna canoe", "Found 4 May 1987 near Dufuna village, Fune LGA; 8.4 m long; radiocarbon dated 8,500–8,000 years; excavated in 1994; now in Damaturu."),
    "WHNW": WS("Hadejia-Nguru wetlands", "Yobe-Komadugu sub-basin of the Chad Basin; Nguru Lake and the Marma Channel complex (58,100 ha) a Ramsar Site; 200,000–325,000 waterbirds."),
    "WBAD": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Bade Emirate", organisation="Wikipedia", url=W("Bade Emirate"),
                 verification_status="needs_corroboration", notes="Reused. 'Bade Fishing Festival is observed at Bade Emirate.'"),
    "WCBNP": H66.SOURCES["WCBNP"],
}
TEXT = {
 "ngazargamu": """The ruins of old Ngazargamu, in Geidam LGA, are No. 58 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Birni Ngazargamu was the capital of the Kanem–Bornu Empire from its foundation in the 15th century until it was destroyed and abandoned during the Fula jihads of the early 19th century (Wikipedia). It stood in the fork of the Komadugu Gana and Komadugu Yobe rivers, near present-day Geidam, about 150 km west of Lake Chad, on the trans-Saharan trade routes; at its height it covered about 1,500 hectares. The great rampart is still visible, with sparse remains of buildings, and the site is noted for its red bricks, rare in pre-modern Africa (Wikipedia).""",
 "dufuna": """The Dufuna archaeological site, near Dufuna village in Fune LGA, is No. 59 on the National Commission for Museums and Monuments' list of proposed national monuments. It is the find-spot of the Dufuna canoe, a dugout discovered on 4 May 1987 by a Fulani herdsman digging a well near the Komadugu Gana River (Wikipedia). Radiocarbon dating places the canoe at about 8,500 to 8,000 years old, and Wikipedia calls it the world's second-oldest known boat; it is 8.4 metres long and was excavated in 1994 by a German and Nigerian team from the universities of Frankfurt and Maiduguri. The canoe is now kept in Damaturu.""",
 "goya": """Goya Gorge and the Ngeji Escarpment, in Fika, are No. 86 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Natural' category. They are not described in more detail in a readable source.""",
 "dagona": """The Dagona Bird Sanctuary, at Dagona village in Bade LGA, is No. 99 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Wild life / forest reserve' category. It is not described in more detail in a readable source.""",
 "museum": """The National Museum Damaturu is one of the national museums of the National Commission for Museums and Monuments (NCMM), located behind the Cultural Centre on Maiduguri Road, opposite the Ben Kadio Housing Estate. Its history and collections are not described in a readable source.""",
 "wetlands": """The Hadejia-Nguru wetlands lie in the Yobe-Komadugu sub-basin of the Chad Basin, where the Hadejia and Jama'are rivers meet lines of ancient dunes and break into many channels, drained by the Yobe River towards Lake Chad (Wikipedia). Nguru Lake and the Marma Channel complex, about 58,100 hectares, are a Ramsar Site, a wetland of international importance, holding an estimated 200,000 to 325,000 waterbirds. Wikipedia notes that the wetlands are threatened by lower rainfall, a growing population and upstream dams.""",
 "fishing": """The Bade fishing festival is observed in the Bade Emirate, according to Wikipedia. Its date, rites and current practice are not described in a readable source.""",
}
Y = lambda l: f"@admin_units:lga:yobe/{l}"
REC = [
    ("ngazargamu", "places", dict(place_type="archaeological_site", name="Ruins of Old Ngazargamu", slug="ruins-of-old-ngazargamu", admin_unit_id=Y("geidam"), status="historical"), ["NCMMP", "WNGZ"], "well_documented"),
    ("dufuna", "places", dict(place_type="archaeological_site", name="Dufuna Archaeological Site", slug="dufuna-archaeological-site", admin_unit_id=Y("fune"), status="existing"), ["NCMMP", "WDUF"], "well_documented"),
    ("goya", "places", dict(place_type="natural_feature", name="Goya Gorge and Ngeji Escarpment", slug="goya-gorge-and-ngeji-escarpment", admin_unit_id=Y("fika"), status="existing"), ["NCMMP"], "verified"),
    ("dagona", "places", dict(place_type="natural_feature", name="Dagona Bird Sanctuary", slug="dagona-bird-sanctuary", admin_unit_id=Y("bade"), status="existing"), ["NCMMP"], "verified"),
    ("museum", "places", dict(place_type="museum", name="National Museum Damaturu", slug="national-museum-damaturu", admin_unit_id=Y("damaturu"), status="existing"), ["NCMMM"], "verified"),
    ("wetlands", "places", dict(place_type="natural_feature", name="Hadejia-Nguru Wetlands", slug="hadejia-nguru-wetlands", admin_unit_id="@admin_units:state:yobe", status="existing"), ["WHNW"], "reported"),
    ("fishing", "cultural_records", dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Bade Fishing Festival",
                                         slug="bade-fishing-festival", timing="Not stated", current_status="unknown", scope_level="ethnic_group"), ["WBAD"], "reported"),
]
RECORDS = []
for key, table, fields, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table=table, evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="@places:chad-basin-national-park", type="located_in", to="@admin_units:state:yobe", source="WCBNP", evidence="single_reliable_source", level="reported",
         notes="Wikipedia: its Bade-Nguru Wetlands and Bulatura sectors are in Yobe State."),
    dict(frm="ngazargamu", type="associated_with", to="@polities:ngazargamu-emirate", role="the emirate bearing the old capital's name, seated at Geidam", source="WNGZ", evidence="single_reliable_source", level="reported",
         notes="Name and place (Geidam) shared; the emirate's history in relation to the old capital is not described in a source read."),
    dict(frm="fishing", type="celebrated_by", to="@ethnic_groups:bade", source="WBAD", evidence="single_reliable_source", level="reported", notes="Observed in the Bade Emirate (Wikipedia)."),
    dict(frm="@polities:bade-emirate", type="associated_with", to="fishing", role="festival observed in the emirate", source="WBAD", evidence="single_reliable_source", level="reported", notes="Wikipedia (Bade Emirate)."),
]
NAMES = [
    dict(record="ngazargamu", name="Birni Ngazargamu", name_type="alternative", usage_notes="Wikipedia; also Gazargamu, Gazargamo, Ngazargamo; 'Birni Gazargamo' in Kanuri pronunciation.", srcs=["WNGZ"]),
    dict(record="ngazargamu", name="Birni Gazargamo", name_type="endonym", usage_notes="Kanuri form (Wikipedia).", srcs=["WNGZ"]),
    dict(record="dufuna", name="Dufuna canoe", name_type="alternative", usage_notes="The find for which the site is known (Wikipedia).", srcs=["WDUF"]),
]
GAPS = [
    ("Yobe: Goya Gorge, Dagona and the museum", "The NCMM names them without descriptions; their history, size and condition need a source."),
    ("Yobe: the Dufuna canoe's home", "Wikipedia says the canoe is 'currently located in Damaturu' without naming the institution; not linked to the National Museum Damaturu."),
    ("Yobe: festivals", "Only a one-line mention of the Bade fishing festival was found; festivals of the Kanuri, Karekare, Bolewa, Ngizim and others need sources."),
    ("Yobe: the Hadejia-Nguru wetlands", "No LGA is named; the Ramsar Site's boundaries were not read from the Ramsar record."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Yobe culture and heritage: Ngazargamu, Dufuna, Goya Gorge, Dagona (NCMM proposed), National Museum Damaturu, Hadejia-Nguru wetlands, Bade fishing festival; Chad Basin NP linked.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 072 — Yobe: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **6 places:**",
         "  - **four proposed national monuments** (Yobe has no *declared* monument):",
         "    - the **Ruins of Old Ngazargamu** (No. 58): the Kanem–Bornu capital from the 15th century to the early 19th, near Geidam",
         "    - the **Dufuna Archaeological Site** (No. 59): the find-spot of an 8,000-year-old dugout canoe",
         "    - **Goya Gorge and the Ngeji Escarpment** (No. 86)",
         "    - the **Dagona Bird Sanctuary** (No. 99)",
         "  - the **National Museum Damaturu**",
         "  - the **Hadejia-Nguru Wetlands** (a Ramsar Site)",
         "- **Existing record:** Chad Basin National Park gets a Yobe link, for its two Yobe sectors.",
         "- **1 cultural record:** the **Bade Fishing Festival**. Only a one-line mention was found, so it is *reported*.",
         "- **Links:** the ruins to the Ngazargamu Emirate (shared name and place); the festival to the Bade and the Bade Emirate.",
         "- **Page length:** check the word counts below. A page over 300 words would be indexable.", ""]
    for key, table, fields, srcs, lvl in REC:
        t = TEXT[key]
        L += [f"## {fields['name']} ({words(t)} words; {lvl})", "", f"> {t}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_072_yobe_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_072_yobe_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
