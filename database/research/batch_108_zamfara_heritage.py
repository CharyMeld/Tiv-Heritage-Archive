"""
Research batch 108 — Zamfara (Phase 3): culture and heritage. Researched 2026-10-02. Pattern: batches 090, 096, 102.

NCMM: Zamfara has no declared national monument and no NCMM museum. Proposed list (hand count, calibrated: Yarima 6,
Elephant House 18, Kusugu 25, Yakoko 66, Dutse Bamle 81): No. 5 'Giant tombs of Zamfara rulers in Zurmi LGA'
('Architectural'); No. 76 'Namoda tomb, Kaura Namoda LGA' ('Historic').
Wikipedia: 'Kaura Namoda' (founded 1807 by Muhammadu Namoda, a prince of the Alibawa ruling family of Zurmi, appointed
Sarkin Zamfara by the jihadists); 'Zamfara State' (Tourism: Jata, an ancient settlement around a hill with a large
cave where traditional practices were performed); 'Kingdom of Zamfara' (Jata among the early chiefdoms).
Mount Kwatarkwashi appears only as a picture caption on Wikipedia — not recorded.
"""
import json, re, sys
import batch_066_borno_heritage as H66
import batch_107_zamfara_institutions as I107

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "NCMMP": dict(H66.SOURCES["NCMMP"], notes="Reused. Zamfara: No. 5 'Giant tombs of Zamfara rulers in Zurmi LGA'; No. 76 'Namoda tomb, Kaura Namoda L.G.A'."),
    "WKNA": dict(I107.SOURCES["WKNA"], notes="Reused. Kaura Namoda founded in 1807 by Muhammadu Namoda, a prince of the Alibawa ruling family of Zurmi, appointed Sarkin Zamfara by the Sokoto jihadists."),
    "WZAM": dict(I107.SOURCES["WZAM"], notes="Reused. Tourism: 'Jata, an ancient settlement of Zamfara located around the hill with a large cave around where traditional practices were performed'."),
    "WKZ": I107.SOURCES["WKZ"],
}
TEXT = {
 "tombs": """The giant tombs of the Zamfara rulers, in Zurmi LGA, are No. 5 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Architectural' category. Zamfara has no declared national monument. The tombs are not described in a readable source; Zurmi was the home of the Alibawa ruling family, from which came Muhammadu Namoda, appointed Sarkin Zamfara during the jihad (Wikipedia).""",
 "namoda": """The tomb of Namoda, in Kaura Namoda LGA, is No. 76 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Muhammadu Namoda, a prince of the Alibawa ruling family of Zurmi and a military leader of the 19th century, founded Kaura Namoda in 1807; he fought with the Sokoto jihadists and was appointed Sarkin Zamfara, although, as Wikipedia notes, Zamfara never became an emirate. The tomb itself is not described in a readable source.""",
 "jata": """Jata is an ancient settlement of Zamfara, built around a hill with a large cave where traditional practices were performed, and is listed by Wikipedia among the state's tourist attractions. Wikipedia's article on the Kingdom of Zamfara names Jata, with Dutsi, Togai and Kiyawa, among the early chiefdoms from which the kingdom grew. Its exact location and present condition are not described in a readable source.""",
}
Z = lambda l: f"@admin_units:lga:zamfara/{l}"
REC = [
    ("tombs", dict(place_type="monument", name="Giant Tombs of the Zamfara Rulers, Zurmi", slug="giant-tombs-of-the-zamfara-rulers-zurmi", admin_unit_id=Z("zurmi"), status="existing"), ["NCMMP", "WKNA"], "verified"),
    ("namoda", dict(place_type="monument", name="Namoda Tomb, Kaura Namoda", slug="namoda-tomb-kaura-namoda", admin_unit_id=Z("kaura-namoda"), status="existing"), ["NCMMP", "WKNA"], "well_documented"),
    ("jata", dict(place_type="historical_place", name="Jata", slug="jata-zamfara", admin_unit_id="@admin_units:state:zamfara", status="historical"), ["WZAM", "WKZ"], "reported"),
]
RECORDS = []
for key, fields, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, fields["name"]) for s in srcs]))
RELATIONS = [
    dict(frm="tombs", type="associated_with", to="@polities:kingdom-of-zamfara", role="tombs of the Zamfara rulers (NCMM)", source="NCMMP", evidence="single_reliable_source", level="reported", notes="NCMM proposed list, No. 5."),
    dict(frm="namoda", type="associated_with", to="@polities:kingdom-of-zamfara", role="tomb of Muhammadu Namoda, appointed Sarkin Zamfara in the jihad", source="WKNA", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Kaura Namoda); NCMM No. 76."),
    dict(frm="jata", type="associated_with", to="@polities:kingdom-of-zamfara", role="one of the early chiefdoms of Zamfara", source="WKZ", evidence="single_reliable_source", level="reported", notes="Wikipedia (Kingdom of Zamfara)."),
]
GAPS = [
    ("Zamfara: tombs and Jata", "The NCMM names the Zurmi tombs and the Namoda tomb without descriptions; Jata's location is not given. Sources on each are needed."),
    ("Zamfara: festivals and other sites", "No Zamfara festival or museum was found in the sources read; Mount Kwatarkwashi appears only as a picture caption."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=[], gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Zamfara culture and heritage: the Zurmi tombs and the Namoda tomb (NCMM proposed) and Jata.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 108 — Zamfara: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **3 places.** Zamfara has **no declared national monument and no national museum**:",
         "  - the **giant tombs of the Zamfara rulers** at Zurmi (proposed No. 5)",
         "  - the **tomb of Namoda** at Kaura Namoda (proposed No. 76). Namoda founded the town in 1807 and was made Sarkin Zamfara in the jihad.",
         "  - **Jata**, an ancient settlement around a hill and cave, one of the early chiefdoms of Zamfara",
         "- **Links:** all three are tied to the Kingdom of Zamfara.",
         "- **Little is known:** none of the three is described in detail in a readable source. This is listed as a gap.", ""]
    for key, fields, srcs, lvl in REC:
        L += [f"## {fields['name']} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_108_zamfara_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_108_zamfara_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} sources={len(SOURCES)}")
