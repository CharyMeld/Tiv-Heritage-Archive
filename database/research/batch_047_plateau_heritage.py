"""
Research batch 047 — Plateau (Phase 3): culture and heritage. Researched 2026-09-30.
Pattern: Taraba batch 041.

Places: National Museum, Jos (NCMM; founded 1952 by Bernard Fagg); the Museum of Traditional
Nigerian Architecture (MOTNA), Jos; the Stone Causeways of Bokkos (declared national monuments Nos.
61–63: Batura, Forof, Tading); four proposed national monuments (Sha abandoned settlement and iron
smelting site, No. 72; Mai Idontoro Acheulian site, No. 74; Olingdan iron smelting site, No. 92;
Langalang foot bridge, No. 93); the Shere Hills. Festivals: Nzem Berom; Ilum Otarok (Tarok).

Evidence notes:
  * NCMM lists read as raw HTML (browser headers needed). The DECLARED list is
    /national-monuments-in-nigeria/list-of-national-monuments/ (65 monuments; Plateau = Nos. 61–63).
    The page /national-monuments/ used by earlier batches is a museum contacts directory; fix_047
    (run BEFORE this import) corrects source #559 to the real list — its conclusion (no declared
    monument in Benue or Taraba) stands.
  * Proposed-list numbers counted within the page's category order and checked against known entries
    (Yakoko = 66, Keana = 90). Keana Salt Village is listed as 'Plateau State' but is in Nasarawa today
    (batch 034 recorded it) — not repeated here.
  * Nzem Berom: Wikipedia (Berom people) for its creation (1980 or 1981) as an umbrella for Mandyeng,
    Nshok, Worom Chun and Vwana; Daily Trust (2019) for the week-long festival, its purpose as stated
    by the Gbong Gwom Jos, and the procession; ThisDay (2016) for its revival after about ten years.
  * Not used: blog pages on Pusdung (Ngas), 'Puus ka Berom', falls and rock formations (Assop, Kurra,
    Riyom) — no reliable source read; gaps.
"""
import json, re, sys

ACCESSED = "2026-09-30"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "NCMML": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="List of National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/list-of-national-monuments/",
                  verification_status="verified", notes="Reused (source corrected by fix_047). Plateau: No. 61 Stone Causeway at Batura, Bokkos; No. 62 Stone Causeway at Forof, Bokkos; No. 63 Stone Causeway at Tading, Bokkos."),
    "NCMMP": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="Proposed National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/proposed-national-monuments/",
                  verification_status="verified", notes="Reused. Plateau entries: No. 72 Sha abandoned settlement/iron ore smelting site and No. 74 Mai Idontoro Acheulian site (Historic); No. 92 Olingdan iron smelting site and No. 93 Langalang foot bridge (Technology)."),
    "NCMMM": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="National Museums",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/museums/national-museums/",
                  verification_status="verified", notes="Reused. National Museum Jos (P.M.B. 2013, Jos) with a zoological museum; Museum of Traditional Nigerian Architecture (MOTNA), Jos, opposite the High Court; Centre for Earth Construction Technology, opposite Jos Museum; Institute of Archaeological and Museum Studies, Jos."),
    "WJOSM": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Jos Museum", organisation="Wikipedia", url=W("Jos Museum"),
                  verification_status="needs_corroboration",
                  notes="Established in 1952 by Bernard Fagg; planned from 1944, built from October 1949 with a £6,500 grant; first public museum in West Africa; early displays of Nok figurines and archaeology from across Nigeria; Arabic manuscripts collected from 1958; zoo begun unofficially in 1955, formally in 1960; administers the Museum of Traditional Nigerian Architecture. Cites Fagg (1963), Museum International."),
    "WSHERE": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Shere Hills", organisation="Wikipedia", url=W("Shere Hills"),
                   verification_status="needs_corroboration",
                   notes="About 10 km east of Jos; highest peak about 1,759 m; the highest point of the Jos Plateau and the third highest point in Nigeria."),
    "WBEROM": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Berom people", organisation="Wikipedia", url=W("Berom people"),
                   verification_status="needs_corroboration",
                   notes="Nzem Berom: one-week festival in March/April, first celebrated in 1980 or 1981; Mandyeng, Nshok, Worom Chun and Vwana brought under this single umbrella; held in the first week of April, when Mandyeng, Nshok and Badu were held. Mandyeng ushers in the rainy season; Mandyeng/Nshok were the most vital festivals for farming, hunting and harvest."),
    "DT19": dict(source_type="news", source_kind="news", source_tier=3, title="When Plateau's Berom prayed for bountiful harvest", organisation="Daily Trust", author="Lami Sadiq",
                 publication_date="2019-06-01", url="https://dailytrust.com/when-plateaus-berom-prayed-for-bountiful-harvest/", verification_status="needs_corroboration",
                 notes="Annual week-long Nzem Berom; Muslim and Christian prayers during the week (10 and 12 May 2019); climax with a royal procession from the Gbong Gwom Jos palace to the Rwang Pam Township stadium with traditional dances and costumes; the Gbong Gwom Jos: the festival prays to God for abundant rain and harvest."),
    "TD16": dict(source_type="news", source_kind="news", source_tier=3, title="Gbong Gwon Jos: Nzem Berom Festival Must be Sustained", organisation="ThisDay",
                 publication_date="2016-05-08", url="https://thisdaylive.com/index.php/2016/05/08/gbong-gwon-jos-nzem-berom-festival-must-be-sustained", verification_status="needs_corroboration",
                 notes="Nzem Berom 'was last celebrated about ten years ago due to security conflicts on the Plateau'; the 2016 theme: 'A Culture Brand for Unity, Peace and Progress'."),
    "GOV25": dict(source_type="official_website", source_kind="official_website", source_tier=1,
                  title="Governor Mutfwang celebrates 2025 Ilum Otarok with the Tarok nation, promises to install new Ponzhi Tarok",
                  organisation="Plateau State Government (Director of Press and Public Affairs to the Governor)", author="Gyang Bere", publication_date="2025-05-06",
                  url="https://calebmutfwang.org/2025/05/governor-mutfwang-celebrates-2025-ilum-otarok-with-the-tarok-nation-promises-to-install-new-ponzhi-tarok/",
                  verification_status="verified", notes="Reused."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified",
                  notes="Reused. Run cluster: Run Daffo–Butura, field 2.A 'Batura' (Bokkos LGA)."),
}

MUSEUM = """The National Museum, Jos is one of the national museums of the National Commission for Museums and Monuments (NCMM), which lists it together with a zoological museum on the same site. According to Wikipedia, it was established in 1952 by the archaeologist Bernard Fagg, after planning began in 1944 and building started in October 1949 with a grant of £6,500, and it was the first public museum in West Africa. Its early displays featured the Nok figurines alongside archaeology from across Nigeria; a collection of Arabic manuscripts was begun in 1958, and a zoo, started informally in 1955, was formally established in 1960.

The museum administers the Museum of Traditional Nigerian Architecture, and the NCMM also lists a Centre for Earth Construction Technology opposite it and the Institute of Archaeological and Museum Studies in Jos."""

MOTNA = """The Museum of Traditional Nigerian Architecture (MOTNA) in Jos is a museum of the National Commission for Museums and Monuments, which gives its address as opposite the High Court, Jos. It is administered together with the National Museum, Jos (Wikipedia). A description of its buildings and collections was not found in a readable source."""

CAUSEWAYS = """Three stone causeways in Bokkos LGA are declared national monuments of Nigeria: the Stone Causeway at Batura (No. 61), the Stone Causeway at Forof (No. 62) and the Stone Causeway at Tading (No. 63) on the National Commission for Museums and Monuments' list of 65 national monuments. They are the only declared national monuments in Plateau State. Blench's Atlas places the Ron (Run) language in Bokkos LGA and records Batura as a location name of its Run Daffo–Butura variety. The age, builders and form of the causeways are not described in a readable source."""

SHA = """The Sha abandoned settlement and iron ore smelting site is No. 72 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category, placed in Plateau State. Its exact location, age and history are not described in a readable source."""
MAIID = """The Mai Idontoro Acheulian site is No. 74 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category, placed in Plateau State. 'Acheulian' names an Early Stone Age tool tradition. The site's exact location and finds are not described in a readable source."""
OLING = """The Olingdan iron smelting site is No. 92 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Technology' category, placed in Plateau State. Its exact location and history are not described in a readable source."""
LANGA = """The Langalang foot bridge is No. 93 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Technology' category, placed in Plateau State. Its exact location, date and builders are not described in a readable source."""

SHERE = """The Shere Hills are a range of hills and rock formations about 10 km east of Jos. According to Wikipedia, their highest peak, at about 1,759 metres, is the highest point of the Jos Plateau and the third highest point in Nigeria."""

NZEM = """Nzem Berom is the week-long festival of the Berom. According to Wikipedia, it was first celebrated in 1980 or 1981, bringing together under one umbrella older Berom ceremonies such as Mandyeng, Nshok, Worom Chun and Vwana, so that they would not be lost; it is held in the first week of April, the season of Mandyeng, Nshok and Badu. Mandyeng was the festival that ushered in the rainy season, and with Nshok was held to ensure good farming, hunting and harvest.

In 2016 the Gbong Gwom Jos revived the festival, which had not been held for about ten years because of insecurity on the Plateau (ThisDay, 2016). In 2019 the week included Muslim and Christian prayers and ended with a royal procession from the Gbong Gwom Jos's palace to the Rwang Pam Township Stadium, with traditional dances and costumes; the Gbong Gwom Jos described the festival as a prayer for abundant rain and harvest (Daily Trust, 2019)."""

ILUM = """Ilum Otarok is the annual cultural festival of the Tarok. In May 2025 it was held at the Langtang North Mini Stadium, where Governor Caleb Mutfwang joined the Tarok in the celebrations and spoke about the Ponzhi Tarok stool (Plateau State Government, 2025). The festival's history and rites are not described in a readable source."""

SC = "@admin_units:state:plateau"
RECORDS = [
    dict(key="museum", table="places", evidence="multiple_sources", level="well_documented",
         fields=dict(place_type="museum", name="National Museum, Jos", slug="national-museum-jos", admin_unit_id=SC, status="existing",
                     summary="The National Museum, Jos, founded by Bernard Fagg in 1952, was the first public museum in West Africa.", description=MUSEUM),
         srcs=[("NCMMM", "NCMM national museum; zoological museum; related institutions"), ("WJOSM", "Founding 1952, Fagg, collections, zoo, MOTNA")]),
    dict(key="motna", table="places", evidence="multiple_sources", level="reported",
         fields=dict(place_type="museum", name="Museum of Traditional Nigerian Architecture", slug="museum-of-traditional-nigerian-architecture", admin_unit_id=SC, status="existing",
                     summary="The Museum of Traditional Nigerian Architecture (MOTNA) is a national museum in Jos, administered with the National Museum, Jos.", description=MOTNA),
         srcs=[("NCMMM", "MOTNA, opposite the High Court, Jos"), ("WJOSM", "Administered by the Jos Museum")]),
    dict(key="causeways", table="places", evidence="single_reliable_source", level="verified",
         fields=dict(place_type="monument", name="Stone Causeways of Bokkos", slug="stone-causeways-of-bokkos", admin_unit_id="@admin_units:lga:plateau/bokkos", status="existing",
                     summary="Three stone causeways in Bokkos LGA, at Batura, Forof and Tading, are declared national monuments of Nigeria (Nos. 61–63).", description=CAUSEWAYS),
         srcs=[("NCMML", "Declared national monuments Nos. 61–63"), ("ATLAS", "Batura as a Run (Ron) location name")]),
    dict(key="sha", table="places", evidence="single_reliable_source", level="verified",
         fields=dict(place_type="archaeological_site", name="Sha Abandoned Settlement and Iron Smelting Site", slug="sha-abandoned-settlement", admin_unit_id=SC, status="unknown",
                     summary="The Sha abandoned settlement and iron ore smelting site in Plateau State is No. 72 on the NCMM list of proposed national monuments.", description=SHA),
         srcs=[("NCMMP", "Proposed national monument No. 72 (Historic)")]),
    dict(key="maiid", table="places", evidence="single_reliable_source", level="verified",
         fields=dict(place_type="archaeological_site", name="Mai Idontoro Acheulian Site", slug="mai-idontoro-acheulian-site", admin_unit_id=SC, status="unknown",
                     summary="The Mai Idontoro Acheulian site in Plateau State is No. 74 on the NCMM list of proposed national monuments.", description=MAIID),
         srcs=[("NCMMP", "Proposed national monument No. 74 (Historic)")]),
    dict(key="oling", table="places", evidence="single_reliable_source", level="verified",
         fields=dict(place_type="archaeological_site", name="Olingdan Iron Smelting Site", slug="olingdan-iron-smelting-site", admin_unit_id=SC, status="unknown",
                     summary="The Olingdan iron smelting site in Plateau State is No. 92 on the NCMM list of proposed national monuments.", description=OLING),
         srcs=[("NCMMP", "Proposed national monument No. 92 (Technology)")]),
    dict(key="langa", table="places", evidence="single_reliable_source", level="verified",
         fields=dict(place_type="heritage_site", name="Langalang Foot Bridge", slug="langalang-foot-bridge", admin_unit_id=SC, status="unknown",
                     summary="The Langalang foot bridge in Plateau State is No. 93 on the NCMM list of proposed national monuments.", description=LANGA),
         srcs=[("NCMMP", "Proposed national monument No. 93 (Technology)")]),
    dict(key="shere", table="places", evidence="single_reliable_source", level="reported",
         fields=dict(place_type="hill_or_mountain", name="Shere Hills", slug="shere-hills", admin_unit_id=SC, status="existing",
                     summary="The Shere Hills east of Jos rise to about 1,759 m, the highest point of the Jos Plateau.", description=SHERE),
         srcs=[("WSHERE", "Location, elevation")]),
    dict(key="nzem", table="cultural_records", evidence="multiple_sources", level="well_documented",
         fields=dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Nzem Berom", local_name="Nzem Berom",
                     slug="nzem-berom", timing="Annually, a week in March, April or May (first week of April per Wikipedia; May in 2019)", season="Start of the rainy season",
                     current_status="revived", scope_level="ethnic_group",
                     summary="Nzem Berom, the week-long festival of the Berom created about 1980 from older rites such as Mandyeng, was revived in 2016.", description=NZEM),
         srcs=[("WBEROM", "Origin 1980/81; umbrella of Mandyeng, Nshok, Worom Chun, Vwana; timing"), ("DT19", "2019 festival, procession, purpose"), ("TD16", "2016 revival after about ten years")]),
    dict(key="ilum", table="cultural_records", evidence="single_reliable_source", level="reported",
         fields=dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Ilum Otarok", local_name="Ilum Otarok",
                     slug="ilum-otarok", timing="Annually (May in 2025)", current_status="active", scope_level="ethnic_group",
                     summary="Ilum Otarok is the annual cultural festival of the Tarok, held in 2025 at Langtang North.", description=ILUM),
         srcs=[("GOV25", "2025 Ilum Otarok Annual Cultural Festival, Langtang North")]),
]
NAMES = [
    dict(record="motna", name="MOTNA", name_type="abbreviation", usage_notes="NCMM.", srcs=["NCMMM"]),
    dict(record="museum", name="Jos Museum", name_type="alternative", usage_notes="Common name (Wikipedia; NCMM addresses 'opposite Jos Museum').", srcs=["WJOSM"]),
]
RELATIONS = [
    dict(frm="motna", type="part_of", to="museum", source="WJOSM", evidence="single_reliable_source", level="reported", notes="The Jos Museum administers MOTNA (Wikipedia)."),
    dict(frm="nzem", type="celebrated_by", to="@ethnic_groups:berom", source="WBEROM", evidence="multiple_sources", level="well_documented", notes="The Berom festival (Wikipedia; Daily Trust; ThisDay)."),
    dict(frm="nzem", type="celebrated_in", to=SC, source="DT19", evidence="single_reliable_source", level="reported",
         notes="Procession from the Gbong Gwom Jos palace to the Rwang Pam Township Stadium, Jos (Daily Trust, 2019); the LGA is not stated."),
    dict(frm="@polities:gbong-gwom-jos", type="custodian_of", to="nzem", source="DT19", evidence="multiple_sources", level="reported",
         notes="The Gbong Gwom Jos leads the royal procession and revived the festival in 2016 (Daily Trust; ThisDay)."),
    dict(frm="ilum", type="celebrated_by", to="@ethnic_groups:tarok", source="GOV25", evidence="single_reliable_source", level="reported", notes="'the Tarok Nation' (Plateau State Government, 2025)."),
    dict(frm="ilum", type="celebrated_in", to="@admin_units:lga:plateau/langtang-north", source="GOV25", evidence="single_reliable_source", level="reported",
         notes="Langtang North Mini Stadium (2025)."),
]
GAPS = [
    ("Plateau: locations of the proposed monuments", "The NCMM gives only 'Plateau State' for Sha, Mai Idontoro, Olingdan and Langalang; their LGAs, ages and histories need an NCMM or archaeological source."),
    ("Plateau: the stone causeways of Bokkos", "Declared national monuments, but their age, builders and form are not described in a readable source."),
    ("Plateau: MOTNA and the Jos Museum", "MOTNA's buildings and collections, and the museum's present galleries, are not described in a readable source; Nok material in Jos is not itemised."),
    ("Plateau: other festivals", "Pusdung (Ngas), Puus ka Berom, Mandyeng as a separate rite, Kofyar, Goemai, Mwaghavul and Irigwe festivals: only weak web pages found."),
    ("Plateau: natural sites", "Assop Falls, Kurra Falls, the Riyom rock formations and the Jos Wildlife Park: no reliable source read."),
    ("Plateau: Nzem Berom date", "Wikipedia gives the first week of April; the 2019 festival was held in May; the actual rule is not stated."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Plateau culture and heritage: Jos museums, declared and proposed national monuments, Shere Hills, Nzem Berom, Ilum Otarok.")


def report():
    L_ = ["# Research batch 047 — Plateau: culture and heritage", "",
          f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
          "## What it adds", "",
          "- **8 places:**",
          "  - **National Museum, Jos**, founded by Bernard Fagg in 1952, the first public museum in West Africa",
          "  - the **Museum of Traditional Nigerian Architecture**",
          "  - the **Stone Causeways of Bokkos**: Plateau's only *declared* national monuments, Nos. 61–63",
          "  - **four proposed national monuments**: Sha (No. 72), Mai Idontoro (No. 74), Olingdan (No. 92) and Langalang (No. 93)",
          "  - the **Shere Hills**",
          "- **2 festivals:**",
          "  - **Nzem Berom**, created about 1980 from older rites and revived in 2016",
          "  - **Ilum Otarok**, the Tarok festival",
          "- **Links:** the festivals to the Berom and the Tarok; the Gbong Gwom Jos as custodian of Nzem Berom; MOTNA as part of the Jos Museum.",
          "- **A correction, in fix_047, run before the import:**",
          "  - Earlier batches cited the NCMM page `/national-monuments/`. That page is actually a *museum contacts directory*; the declared list is `/list-of-national-monuments/`.",
          "  - fix_047 re-points source #559 to the real list.",
          "  - The earlier conclusion still holds: no Benue or Taraba monument is on the list.",
          "- **Not used:** blog pages on other festivals and on the falls and rock sites. They are listed as gaps.",
          "- All records are short, so they are noindex and the sitemap is unchanged.", ""]
    for r in RECORDS:
        t = r["fields"]["description"]
        L_ += [f"## {r['fields']['name']} ({words(t)} words)", ""] + [f"> {p}" if p else ">" for p in t.split("\n")] + [""]
    L_ += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_047_plateau_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_047_plateau_heritage_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
