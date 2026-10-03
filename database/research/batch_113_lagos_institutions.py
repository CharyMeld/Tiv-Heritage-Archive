"""
Research batch 113 — Lagos (Phase 3): traditional institutions. Researched 2026-10-02. Pattern: batches 101, 107.

  * Kingdom of Lagos (Eko): Wikipedia 'Oba of Lagos' — the Oba, also Eleko of Eko, a ceremonial Yoruba sovereign; all
    Obas trace their lineage to Ashipa, an Awori war captain of the Oba of Benin; Ashipa (1600–1630, not crowned), Ado
    (1630–1669) first king; tribute to Benin ended about 1830; the British bombardment of 1851 (Kosoko defeated, Akitoye
    restored); earlier titles Ologun and Eleko; list of Obas to Rilwan Akiolu (2003–). 'Rilwan Akiolu': selected by the
    kingmakers 23 May 2003, confirmed by the state, crowned 9 August 2003, the 21st Oba; the Akinsemoyin Royal Family
    has challenged his coronation in court. 'Iga Idunganran': the palace on Lagos Island, built 1670 for Oba Gabaro.
    Still the Oba in July 2026 (The Gazette, 4 July 2026).
  * Badagry Kingdom: Wikipedia 'Akran of Badagry' — the Akran is the paramount ruler, from the Jegba ruling quarter;
    Oba Babatunde Akran (De Wheno Aholu Menu-Toyi I) reigned from 1977. NAN, 12 January 2026: he died on Monday
    (12 January) at 89, crowned 23 April 1977, Permanent Vice Chairman of the Lagos State Council of Obas and Chiefs.
    The Gazette, 15 January 2026: the governor cautioned the royal council against manipulating the succession; no
    successor named. A search on 2 October 2026 found no report of a new Akran. Wikipedia 'Badagry': city-state of
    eight wards, each with a traditional head, Jegba (Akran, the ruling house); origins mostly Ogu, Ewe and Oyo Yoruba
    (Robin Law); Ajido and Dale-Whedakoh kingdoms named (not recorded).
  * Olojo of Ojo: Wikipedia 'Ojo, Lagos' — founded by Esugbemi from Ile-Ife (oral tradition); an Oba of Oto-Awori
    ruled alongside the Olojo from the late 18th century; the Olojo festival, when the Olojo wears the crown.
Not recorded (gaps): the Ayangburen of Ikorodu (named only as a palace in a list), the Lagos State Council of Obas and
Chiefs, the Ajido and Dale-Whedakoh kingdoms, and the Oba of Benin (no record yet).
"""
import json, re, sys
import batch_111_lagos_languages_peoples as P111

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = lambda t, org, d, u, n, a=None: dict(source_type="news", source_kind="news", source_tier=3, title=t, organisation=org, publication_date=d, url=u,
                                            verification_status="needs_corroboration", notes=n, **({"author": a} if a else {}))
SOURCES = {
    "WOBA": WS("Oba of Lagos", "Oba of Lagos, also Eleko of Eko, a ceremonial Yoruba sovereign; lineage traced to Ashipa, an Awori war captain of the Oba of Benin; Ado (1630–1669) first king; tribute to Benin ended about 1830; 1851 bombardment; titles Ologun, Eleko, Oba of Lagos; list of Obas."),
    "WAKI": WS("Rilwan Akiolu", "Selected by the kingmakers on 23 May 2003, confirmed by the Lagos State government as the 21st Oba of Lagos, crowned 9 August 2003; the Akinsemoyin Royal Family has challenged the coronation in court."),
    "WIGA": WS("Iga Idunganran", "Official residence of the Oba of Lagos on Lagos Island; ancient palace built in 1670 for Oba Gabaro; venue of the Eyo festival."),
    "WLAG": dict(P111.SOURCES["WLAG"], notes="Reused. Eko, the island, was the initial name of Lagos; the first to settle in Eko were the Awori."),
    "GZ26": NEWS("Tinubu will win 2027 election, says Oba of Lagos", "The Gazette (Nigeria)", "2026-07-04", "https://gazettengr.com/tinubu-will-win-2027-election-says-oba-of-lagos/",
                 "'The Oba of Lagos, Rilwan Akiolu, on Saturday …' — confirms him in office in July 2026."),
    "WAKR": WS("Akran of Badagry", "The Akran is the paramount traditional ruler of Badagry Kingdom; the title is associated with the Jegba ruling quarter; Oba Babatunde Akran (De Wheno Aholu Menu-Toyi I) reigned from 1977 until his death in 2026; palace in the Jegba Quarter."),
    "WBAD": WS("Badagry", "City-state of eight wards each with a traditional head: Jegba (Akran, the ruling house), Asago, Ganho, Posuko, Boeko, Ahoviko, Ahwanjigo, Wharakoh; origins mostly Ogu, Ewe and Oyo Yoruba (Robin Law); Ajido and Dale-Whedakoh kingdoms."),
    "NAN26": NEWS("Akran of Badagry joins ancestors at 89", "News Agency of Nigeria", "2026-01-12", "https://nannews.ng/akran-of-badagry-joins-ancestors-at-89/",
                  "Oba Aholu Menu-Toyi Babatunde, Akran of Badagry, died on Monday after a brief illness, aged 89; born 18 September 1936; crowned 23 April 1977; Permanent Vice Chairman of the Lagos State Council of Obas and Chiefs.", "Raji Rasak"),
    "GZ26B": NEWS("Akran's death: Sanwo-Olu cautions Badagry chiefs on manipulating succession process", "The Gazette (Nigeria)", "2026-01-15",
                  "https://gazettengr.com/akrans-death-sanwo-olu-cautions-badagry-chiefs-on-manipulating-succession-process/",
                  "The governor urged the royal council not to manipulate the selection of the next Akran; no successor named."),
    "WOJO": WS("Ojo, Lagos", "Founded by Esugbemi from Ile-Ife (oral tradition); an Oba from Oto-Awori ruled alongside the Olojo of Ojo from the late 18th century; the Olojo festival, when the Olojo wears the crown."),
}
TEXT = {
 "lagos": """The Kingdom of Lagos, or Eko, is the traditional kingdom of Lagos Island, whose ruler is the Oba of Lagos, also called the Eleko of Eko. According to Wikipedia, every Oba traces his lineage to Ashipa, an Awori war captain of the Oba of Benin who governed Lagos on Benin's behalf from about 1600; Ado, who reigned from 1630, was the first king. Lagos stopped paying tribute to Benin around 1830, and after the British bombardment of 1851 Oba Akitoye was restored under British protection. Today the Oba is a ceremonial Yoruba sovereign without political power. His palace, Iga Idunganran on Lagos Island, was first built in 1670. Rilwan Akiolu was selected by the kingmakers in May 2003 and crowned that August as the 21st Oba; the Akinsemoyin royal family has challenged his coronation in court (Wikipedia). He was still the Oba in July 2026 (The Gazette).""",
 "badagry": """Badagry Kingdom is the traditional kingdom of Badagry, on the lagoon west of Lagos, whose paramount ruler is the Akran of Badagry. According to Wikipedia, the old city-state was divided into eight wards, each with a traditional head, and the Akran comes from the Jegba ruling house. Robin Law traces the town to a resettlement of displaced people, mostly Ogu, Ewe and Oyo Yoruba, and it grew as a coastal trading port from 1736. Oba Babatunde Akran, De Wheno Aholu Menu-Toyi I, was crowned on 23 April 1977 and died on 12 January 2026, aged 89; he was Permanent Vice Chairman of the Lagos State Council of Obas and Chiefs (News Agency of Nigeria). In January 2026 the state governor urged the royal council not to manipulate the choice of his successor (The Gazette); no new Akran is named in a source read.""",
 "ojo": """The Olojo of Ojo is the traditional ruler of Ojo, an Awori town west of Lagos. According to oral tradition reported by Wikipedia, Ojo was founded by Esugbemi, a hunter who migrated from Ile-Ife with his wife Erelu and the chief priest Osu, and was later joined by other Awori settlers. From the late 18th century an Oba from Oto-Awori ruled alongside the Olojo. Ojo is known for the Olojo festival, during which the Olojo wears the crown. The present holder of the title is not named in a source read.""",
}
REC = [
    ("lagos", "Kingdom of Lagos", "kingdom-of-lagos", ["WOBA", "WAKI", "WIGA", "GZ26"], "well_documented",
     dict(polity_type="kingdom", is_extant=1, founded_year=1600, founded_text="about 1600, under Ashipa; Ado, the first king, from 1630 (Wikipedia)", founded_precision="circa")),
    ("badagry", "Badagry Kingdom", "badagry-kingdom", ["WAKR", "WBAD", "NAN26", "GZ26B"], "well_documented", dict(polity_type="kingdom", is_extant=1)),
    ("ojo", "Olojo of Ojo", "olojo-of-ojo", ["WOJO"], "reported", dict(polity_type="traditional_title", is_extant=1)),
]
RECORDS = []
for key, name, slug, srcs, lvl, extra in REC:
    t = TEXT[key]
    f = dict(name=name, slug=slug, summary=t.split(". ")[0] + ".", description=t)
    f.update(extra)
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl, fields=f, srcs=[(s, name) for s in srcs]))
LG = lambda l: f"@admin_units:lga:lagos/{l}"
SEAT = "Seat (not a statement of full jurisdiction)."
RELATIONS = [
    dict(frm="lagos", type="located_in", to=LG("lagos-island"), source="WIGA", evidence="multiple_sources", level="well_documented", notes=f"Iga Idunganran, the palace, is on Lagos Island (Wikipedia). {SEAT}"),
    dict(frm="badagry", type="located_in", to=LG("badagry"), source="WAKR", evidence="multiple_sources", level="well_documented", notes=f"The Akran's palace is in the Jegba Quarter of Badagry (Wikipedia). {SEAT}"),
    dict(frm="ojo", type="located_in", to=LG("ojo"), source="WOJO", evidence="single_reliable_source", level="reported", notes=f"Ojo town. {SEAT}"),
    dict(frm="lagos", type="associated_with", to="@ethnic_groups:yoruba", role="Yoruba kingdom; its Obas trace their lineage to Ashipa, an Awori", source="WOBA", evidence="multiple_sources", level="well_documented",
         notes="Wikipedia (Oba of Lagos: 'a ceremonial Yoruba sovereign'; Lagos State: Eko first settled by the Awori)."),
    dict(frm="badagry", type="associated_with", to="@ethnic_groups:ogu", role="kingdom of a town settled mostly by Ogu, Ewe and Oyo Yoruba", source="WBAD", evidence="single_reliable_source", level="reported",
         notes="Wikipedia (Badagry), citing Robin Law; the Zangbeto masquerade of the Ogu (Egun) people of Badagry."),
    dict(frm="ojo", type="associated_with", to="@ethnic_groups:yoruba", role="Awori (Yoruba) town", source="WOJO", evidence="single_reliable_source", level="reported", notes="Wikipedia (Ojo, Lagos): Awori settlers from Ile-Ife."),
]
NAMES = [
    dict(record="lagos", name="Eko", name_type="endonym", usage_notes="Yoruba name of Lagos Island and of the kingdom; the ruler is the Eleko of Eko (Wikipedia).", srcs=["WOBA"]),
    dict(record="lagos", name="Oba of Lagos", name_type="alternative", usage_notes="Title of the ruler; earlier titles Ologun and Eleko (Wikipedia).", srcs=["WOBA"]),
    dict(record="badagry", name="Akran of Badagry", name_type="alternative", usage_notes="Title of the paramount ruler (Wikipedia; NAN).", srcs=["WAKR"]),
]
GAPS = [
    ("Lagos: the Akran of Badagry", "Oba Babatunde Akran died on 12 January 2026; the latest source read (The Gazette, 15 January 2026) names no successor, and a search on 2 October 2026 found none."),
    ("Lagos: Ikorodu, Epe and other obaships", "The Ayangburen of Ikorodu is named only as a palace in Wikipedia's list of Ikorodu landmarks; the obas of Epe, Ikeja, Ibeju and the Ikorodu Division towns, and the Ajido and Dale-Whedakoh kingdoms of Badagry, need sources."),
    ("Lagos: State Council of Obas and Chiefs", "Named by NAN (the Akran was its Permanent Vice Chairman); its membership and chairmanship are not given in a source read."),
    ("Lagos: Benin", "The Kingdom of Lagos's tie to the Oba of Benin is in its description; there is no Benin Kingdom record yet (Edo State)."),
    ("Lagos: Olojo of Ojo", "The present holder of the title is not named in a source read."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Lagos traditional institutions: the Kingdom of Lagos (Eko), Badagry Kingdom and the Olojo of Ojo.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 113 — Lagos: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **3 polities:**",
         "  - the **Kingdom of Lagos (Eko)**: the Oba of Lagos. Rilwan Akiolu has reigned since 2003 and was confirmed in office in July 2026.",
         "  - **Badagry Kingdom**: the Akran. Oba Babatunde Akran reigned from 1977 until his death on 12 January 2026.",
         "  - the **Olojo of Ojo**: a traditional title, *reported*",
         "- **Handled with care:**",
         "  - **The Akran's throne:** no successor is named. The last report read (January 2026) says the succession was still being decided. This is a gap.",
         "  - **The Akinsemoyin royal family's court challenge** to Oba Akiolu's coronation is stated neutrally, as Wikipedia gives it.",
         "  - **Not recorded:** the Ayangburen of Ikorodu and other obaships, the Council of Obas and Chiefs, and the Ajido and Dale-Whedakoh kingdoms. The sources are too thin; they are listed as gaps.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, name, slug, srcs, lvl, extra in REC:
        L += [f"## {name} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_113_lagos_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_113_lagos_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
