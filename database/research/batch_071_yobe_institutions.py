"""
Research batch 071 — Yobe (Phase 3): traditional institutions. Researched 2026-10-01. Pattern: batches 059 and 065.

Records (12 polities): the emirates Wikipedia names for Yobe — the four with articles (Damaturu, Fika, Bade, Potiskum
(Pataskum)), Ngazargamu, Tikau and Gudi (dated news or a Wikipedia biography), and Gujba, Nguru, Yusufari, Fune and
Jajere (named only in Wikipedia's list of the emirates created on 6 January 2000 — 'reported').

Evidence notes:
  * Number of emirates: Wikipedia (Fika; Potiskum) — four when Yobe was created, thirteen after 6 January 2000
    (adding Ngazargamo, Gujba, Nguru, Tikau, Pataskum, Yusufari, Gudi, Fune and Jajere); Channels TV (2024) speaks of
    '14 prominent Emirs of the state'. Which four were the originals is not stated (Damaturu's first emir was
    appointed in 1993 — Daily Trust, 2024); Machina is not named in any source read (gap).
  * Ngazargamu: Emir Ahmad Tijjani Ibn Saleh died in Cairo on Tuesday 9 June 2026 (State House, 9 June 2026;
    Daily Trust, 13 June 2026: buried in Damaturu); Yerima Ibn Mahmud appointed to succeed him (Daily Trust).
    Wikipedia (Damaturu Emirate): 'the Ngazaragamo emirate, which is based in Gaidam'.
  * Tikau: Emir Muhammadu Abubakar Ibn Grema, appointed 25 May 2001, died 10 May 2024 (Channels TV). Successor not
    found (gap). Seat not stated.
  * Gudi: Ismaila Ahmed Dala Ibn Madugu Khaji II, 20th Emir from 2 August 2025; palace at Gadaka; 'head of the Ngamo
    people' (Wikipedia biography). LGA of Gadaka not stated in a source read — state link only.
  * Fika: Muhammadu Abali Ibn Muhammadu Idrissa received his staff of office on 12 May 2010 (Wikipedia, Fika Emirate);
    the two Wikipedia articles number him differently (43rd / 13th) — no ordinal recorded.
  * Damaturu, Bade, Potiskum: present rulers given by Wikipedia (2004, 2005, 2007–2011 mentions) — recorded with the
    date of the source, not as current.
"""
import json, re, sys

ACCESSED = "2026-10-01"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = lambda t, org, d, u, n, a=None: dict(source_type="news", source_kind="news", source_tier=3, title=t, organisation=org, publication_date=d, url=u,
                                            verification_status="needs_corroboration", notes=n, **({"author": a} if a else {}))
SOURCES = {
    "WDAM": WS("Damaturu Emirate", "First-class emirate based at Damaturu; formerly part of the Ngazaragamo emirate based in Gaidam; Baba Shehu Hashimi II Ibn Umar El-Kanemi appointed Shehu (Emir) on 15 May 2004."),
    "WFIK": WS("Fika Emirate", "Headquarters Potiskum (moved from Fika town in 1924); the Emir (Moi) heads the Bole people; tradition dates it to the 15th century; Muhammadu Abali Ibn Muhammadu Idrissa received staff of office 12 May 2010; emirates increased from four to thirteen on 6 January 2000."),
    "WBAD": WS("Bade Emirate", "Headquarters Gashua; Abubakar Umar Suleiman, 11th Emir (Mai Bade), turbaned 12 November 2005; Bade settled by c. 1300; Pan-Bade confederation."),
    "WPOT": WS("Potiskum Emirate", "Also Pataskum; title 'Mai'; founded 1809 by the Ngizim (Mai Bauya); merged into Fika in 1913; independent again from 6 January 2000, with Ngazargamo, Gujba, Nguru, Tikau, Pataskum, Yusufari, Gudi, Fune and Jajere; Emir Umaru Bubaram Ibn Wuriwa Bauya (2007–2011 mentions)."),
    "WGAD": WS("Ismaila Ahmed Gadaka", "20th Emir of Gudi from 2 August 2025; palace at Gadaka; 'head of the Ngamo people'; succeeded Isa Bunuwo Madugu Ibn Khaji."),
    "SH26": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="President Tinubu mourns Emir of Ngazargamu, Alhaji Ahmad Tijjani Ibn Saleh",
                 organisation="State House, Abuja (Special Adviser on Information and Strategy)", publication_date="2026-06-09",
                 url="https://statehouse.gov.ng/president-tinubu-mourns-emir-of-ngazargamu-alhaji-ahmad-tijjani-ibn-saleh/", verification_status="verified",
                 notes="The Ngazargamu Emirate Council announced the Emir's death on Tuesday in Cairo, Egypt, after a prolonged illness."),
    "DT26": NEWS("Buni Appoints Mahmud as New Emir of Ngazargamu", "Daily Trust", "2026-06-13", "https://dailytrust.com/buni-appoints-mahmud-as-new-emir-of-ngazargamu",
                 "Yerima Ibn Mahmud appointed to succeed Tijjani Ibn Saleh Geidam, who died on Tuesday in Egypt and was buried on Friday in Damaturu; formerly Commissioner for Livestock and Turakin Ngazargamu for 16 years.", "Habibu Gimba"),
    "CH24": NEWS("Tikau Emirate In Yobe Announces Emir's Passing", "Channels Television", "2024-05-11", "https://www.channelstv.com/2024/05/11/tikau-emirate-in-yobe-announces-emirs-passing/",
                 "Emir Muhammadu Abubakar Ibn Grema died on Friday 10 May 2024, aged 73; appointed 25 May 2001 'as one of the 14 prominent Emirs of the state'."),
    "DT24": NEWS("Damaturu's Former Emir Dies in Saudi Arabia", "Daily Trust", "2024-04-21", "https://dailytrust.com.ng/damaturus-former-emir-dies-in-saudi-arabia",
                 "First Emir of Damaturu, Mai Aliyu Muhammad Biriri, appointed August 1993, removed by the military regime in January 1995; died aged 82.", "Habibu Gimba"),
    "NCMMP": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="Proposed National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/proposed-national-monuments/",
                  verification_status="verified", notes="Reused. Yobe: 'Ruins of Old Ngazargamu, ancient city of Kanem-Borno Empire in Geidam LGA, Yobe State'."),
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Yobe rows: Bade ('Barde') LGA headquarters Gashua."),
}
TEXT = {
 "damaturu": """The Damaturu Emirate is a first-class traditional state based at Damaturu, the capital of Yobe State, according to Wikipedia, which says it was once part of the Ngazargamu emirate based at Geidam. Its first emir, Mai Aliyu Muhammad Biriri, was appointed in August 1993 and removed by the military government in January 1995; he died in Saudi Arabia in April 2024 (Daily Trust). Baba Shehu Hashimi II Ibn Umar El-Kanemi was appointed Shehu (Emir) of Damaturu on 15 May 2004, succeeding his brother (Wikipedia); whether he still reigns is not confirmed by a recent source.""",
 "fika": """The Fika Emirate is the traditional state of the Bole (Bolewa), whose ruler, the Moi, is their head; tradition dates it to the 15th century, and its headquarters moved from Fika town to Potiskum in 1924 (Wikipedia). Its emir, Muhammadu Abali Ibn Muhammadu Idrissa, received his staff of office from Governor Ibrahim Gaidam on 12 May 2010. When the state government increased Yobe's emirates from four to thirteen on 6 January 2000, the Emir of Fika protested and went to court before accepting the change, and in 2009–2010 a dispute between the Fika and Potiskum emirate councils was resolved by the Sultan of Sokoto (Wikipedia).""",
 "bade": """The Bade Emirate is the traditional state of the Bade, with its headquarters at Gashua (Wikipedia), the headquarters of Bade LGA (Statoids). According to Wikipedia the Bade had settled in their present territory by about 1300 and, under pressure from Kanuri and Fulani attacks, their clans united in a Pan-Bade confederation under one leader. Abubakar Umar Suleiman was turbaned as the 11th Emir, or Mai Bade, on 12 November 2005. Wikipedia mentions a Bade fishing festival held in the emirate.""",
 "potiskum": """The Potiskum (or Pataskum) Emirate is the traditional state of the Ngizim, whose ruler holds the title Mai. According to Wikipedia it was founded in 1809 by the Ngizim chief Mai Bauya, merged by the British into the Fika Emirate in 1913, and made independent again on 6 January 2000; both emirates now have their headquarters at Potiskum. Wikipedia names Umaru Bubaram Ibn Wuriwa Bauya as Emir between 2007 and 2011.""",
 "ngazargamu": """The Ngazargamu Emirate is one of the emirates the Yobe State government created or restored on 6 January 2000 (Wikipedia), with its seat at Geidam. The ruins of old Ngazargamu, an ancient city of the Kanem–Bornu Empire in Geidam LGA, are on the National Commission for Museums and Monuments' list of proposed national monuments. Its Emir, Ahmad Tijjani Ibn Saleh, died in Cairo on 9 June 2026 and was buried in Damaturu (State House; Daily Trust), and Governor Mai Mala Buni appointed Yerima Ibn Mahmud, a former state commissioner and Turakin Ngazargamu, to succeed him (Daily Trust, 13 June 2026).""",
 "tikau": """The Tikau Emirate is one of the emirates created in Yobe State on 6 January 2000 (Wikipedia). Its Emir, Muhammadu Abubakar Ibn Grema, appointed on 25 May 2001, died on 10 May 2024 at the age of 73 (Channels Television). His successor and the emirate's seat are not given in a readable source.""",
 "gudi": """The Gudi Emirate is one of the emirates created in Yobe State on 6 January 2000 (Wikipedia). Its ruler, the Mai, is head of the Ngamo people, and his palace is at Gadaka; Ismaila Ahmed Dala Ibn Madugu Khaji II became the 20th Emir on 2 August 2025, succeeding Isa Bunuwo Madugu Ibn Khaji (Wikipedia).""",
 "gujba": """The Gujba Emirate is named by Wikipedia among the emirates created in Yobe State on 6 January 2000. Its seat, ruler and grade are not given in a readable source.""",
 "nguru": """The Nguru Emirate is named by Wikipedia among the emirates created in Yobe State on 6 January 2000. Its seat, ruler and grade are not given in a readable source.""",
 "yusufari": """The Yusufari Emirate is named by Wikipedia among the emirates created in Yobe State on 6 January 2000. Its seat, ruler and grade are not given in a readable source.""",
 "fune": """The Fune Emirate is named by Wikipedia among the emirates created in Yobe State on 6 January 2000. Its seat, ruler and grade are not given in a readable source.""",
 "jajere": """The Jajere Emirate is named by Wikipedia among the emirates created in Yobe State on 6 January 2000. Its seat, ruler and grade are not given in a readable source.""",
}
REC = [
    ("damaturu", "Damaturu Emirate", "damaturu-emirate", ["WDAM", "DT24"], "well_documented"),
    ("fika", "Fika Emirate", "fika-emirate", ["WFIK", "WPOT"], "well_documented"),
    ("bade", "Bade Emirate", "bade-emirate", ["WBAD", "STAT"], "well_documented"),
    ("potiskum", "Potiskum Emirate", "potiskum-emirate", ["WPOT", "WFIK"], "well_documented"),
    ("ngazargamu", "Ngazargamu Emirate", "ngazargamu-emirate", ["SH26", "DT26", "WDAM", "WPOT", "NCMMP"], "well_documented"),
    ("tikau", "Tikau Emirate", "tikau-emirate", ["CH24", "WPOT"], "well_documented"),
    ("gudi", "Gudi Emirate", "gudi-emirate", ["WGAD", "WPOT"], "reported"),
    ("gujba", "Gujba Emirate", "gujba-emirate", ["WPOT"], "reported"),
    ("nguru", "Nguru Emirate", "nguru-emirate", ["WPOT"], "reported"),
    ("yusufari", "Yusufari Emirate", "yusufari-emirate", ["WPOT"], "reported"),
    ("fune", "Fune Emirate", "fune-emirate", ["WPOT"], "reported"),
    ("jajere", "Jajere Emirate", "jajere-emirate", ["WPOT"], "reported"),
]
RECORDS = []
for key, name, slug, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(polity_type="emirate", name=name, slug=slug, is_extant=1, summary=t.split(". ")[0] + ".", description=t), srcs=[(s, name) for s in srcs]))
Y = lambda l: f"@admin_units:lga:yobe/{l}"
SEAT = "Seat of the traditional state (not a statement of its full jurisdiction)."
ST = lambda k, src, n: dict(frm=k, type="located_in", to="@admin_units:state:yobe", source=src, evidence="single_reliable_source", level="reported", notes=n)
RELATIONS = [
    dict(frm="damaturu", type="located_in", to=Y("damaturu"), source="WDAM", evidence="multiple_sources", level="well_documented", notes="Based at Damaturu (Wikipedia; Daily Trust). " + SEAT),
    dict(frm="fika", type="located_in", to=Y("potiskum"), source="WFIK", evidence="multiple_sources", level="well_documented", notes="Headquarters at Potiskum since 1924 (Wikipedia). " + SEAT),
    dict(frm="bade", type="located_in", to=Y("bade"), source="WBAD", evidence="multiple_sources", level="well_documented", notes="Headquarters Gashua (Wikipedia), the headquarters of Bade LGA (Statoids). " + SEAT),
    dict(frm="potiskum", type="located_in", to=Y("potiskum"), source="WPOT", evidence="single_reliable_source", level="well_documented", notes="Headquarters at Potiskum (Wikipedia). " + SEAT),
    dict(frm="ngazargamu", type="located_in", to=Y("geidam"), source="WDAM", evidence="multiple_sources", level="reported", notes="'the Ngazaragamo emirate, which is based in Gaidam' (Wikipedia); the late Emir was known as Tijjani Ibn Saleh Geidam (Daily Trust). " + SEAT),
    ST("tikau", "CH24", "Seat not stated (Channels TV; Wikipedia)."),
    ST("gudi", "WGAD", "Palace at Gadaka (Wikipedia); the LGA of Gadaka is not stated in a source read."),
] + [ST(k, "WPOT", "Named in Wikipedia's list of emirates created on 6 January 2000; seat not stated.") for k in ("gujba", "nguru", "yusufari", "fune", "jajere")] + [
    dict(frm="potiskum", type="part_of", to="fika", source="WPOT", evidence="single_reliable_source", level="reported", valid_from_year=1913, valid_to_year=2000,
         notes="Historical: merged into the Fika Emirate by the British in 1913; independent again from 6 January 2000 (Wikipedia)."),
    dict(frm="fika", type="associated_with", to="@ethnic_groups:bolewa", role="the Moi is head of the Bole people", source="WFIK", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Fika Emirate)."),
    dict(frm="potiskum", type="associated_with", to="@ethnic_groups:ngizim", role="emirate of the Ngizim", source="WPOT", evidence="single_reliable_source", level="well_documented", notes="'founded in 1809 by the Ngizim or Ngizimawa people' (Wikipedia)."),
    dict(frm="bade", type="associated_with", to="@ethnic_groups:bade", role="emirate of the Bade", source="WBAD", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Bade Emirate): Bade history and the Pan-Bade confederation."),
    dict(frm="gudi", type="associated_with", to="@ethnic_groups:ngamo", role="the Mai is head of the Ngamo people", source="WGAD", evidence="single_reliable_source", level="reported", notes="Wikipedia (Ismaila Ahmed Gadaka)."),
]
NAMES = [
    dict(record="potiskum", name="Pataskum Emirate", name_type="alternative", usage_notes="Wikipedia.", srcs=["WPOT"]),
    dict(record="bade", name="Mai Bade", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WBAD"]),
    dict(record="fika", name="Moi Fika", name_type="alternative", usage_notes="Title of the ruler, 'Moi' in the local language (Wikipedia).", srcs=["WFIK"]),
    dict(record="ngazargamu", name="Gazargamu Emirate", name_type="spelling_variant", usage_notes="State House statement (2026).", srcs=["SH26"]),
]
GAPS = [
    ("Yobe: the emirates", "Wikipedia gives four emirates at the state's creation and thirteen after 6 January 2000; Channels TV (2024) speaks of fourteen emirs. Which four were the originals, and whether Machina is an emirate, were not found in a source read."),
    ("Yobe: present rulers", "Damaturu (2004), Bade (2005), Fika (2010) and Potiskum (2007–2011) rulers are dated to their sources; Tikau's emir since May 2024 and the rulers of Gujba, Nguru, Yusufari, Fune and Jajere were not found. No official list of grades or of the state council of emirs was found."),
    ("Yobe: Fika's numbering", "Wikipedia's Fika Emirate article calls Muhammadu Abali the 43rd Emir; its biography of him, the 13th. No ordinal is recorded."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Yobe traditional institutions: Damaturu, Fika, Bade, Potiskum, Ngazargamu, Tikau, Gudi, and Gujba, Nguru, Yusufari, Fune, Jajere emirates.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 071 — Yobe: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **{len(RECORDS)} emirates:**",
         "  - **Damaturu**: first class; its first emir served 1993–95",
         "  - **Fika**: the Bolewa, seated at Potiskum since 1924",
         "  - **Bade**: seated at Gashua",
         "  - **Potiskum (Pataskum)**: the Ngizim, founded 1809",
         "  - **Ngazargamu**: seated at Geidam. Its emir died on 9 June 2026 and Yerima Ibn Mahmud was appointed to succeed him (State House; Daily Trust).",
         "  - **Tikau**: its emir died on 10 May 2024; his successor was not found",
         "  - **Gudi**: the Ngamo; its 20th emir took office on 2 August 2025",
         "  - **Gujba, Nguru, Yusufari, Fune and Jajere**: named only in Wikipedia's list of emirates created on 6 January 2000, so they are *reported*",
         "- **Links:** each emirate to its seat LGA, or to the state where no seat is given; Fika to the Bolewa, Potiskum to the Ngizim, Bade to the Bade, Gudi to the Ngamo; Potiskum's history inside Fika (1913–2000).",
         "- **Handled with care:**",
         "  - **Rulers:** those known only from older Wikipedia text are dated to their source, not presented as current.",
         "  - **Fika's numbering** differs between two Wikipedia articles (43rd or 13th), so no number is recorded.",
         "  - **The count of emirates** differs: thirteen in Wikipedia, fourteen in Channels TV.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, name, slug, srcs, lvl in REC:
        t = TEXT[key]
        L += [f"## {name} ({words(t)} words; {lvl})", "", f"> {t}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_071_yobe_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_071_yobe_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
