"""
Research batch 119 — Ogun (Phase 3): traditional institutions. Researched 2026-10-02. Pattern: batch 113 (Lagos).

  * Egbaland (the Alake): Wikipedia 'Egba Ake' — the Alake of Egbaland is the traditional ruler of the Egba in Abeokuta;
    Egbaland has four sections (Ake, Oke-Ona, Gbagura, Owu); the Alake comes from Egba Ake, whose Omo-Iya-Marun are his
    kingmakers; Orile Egba founded after leaving the Oyo empire about the 13th century (Samuel Johnson); Abeokuta founded
    about 1830. 'Adedotun Aremu Gbadebo III': Alake since 2 August 2005 (Laarun ruling house). The Gazette, 19 July
    2026: 'Oba Adedotun Gbadebo, the Alake and Paramount Ruler of Egbaland'. 'Ogun State': Abeokuta South (Ake).
  * Ijebu Kingdom (the Awujale): Wikipedia 'Ijebu Kingdom' — a Yoruba kingdom formed around the 15th century, its
    dynasty founded by Obanta of Ile-Ife; Sungbo's Eredo; British war of 1892. 'Awujale': four ruling houses (Gbelegbuwa,
    Anikinaiya, Fusengbuwa, Fidipote), declaration of 1959. 'Sikiru Kayode Adetona': Awujale from 2 April 1960 to his
    death on 13 July 2025. Arise News, 19 June 2026: the Ilamuren kingmakers sent five princes to the governor (letter of
    14 April 2026), but the selection remained suspended by the state government.
  * Remo (the Akarigbo): Wikipedia 'Akarigbo of Remo' — paramount ruler of the 33 towns of the Remo kingdom; capital
    Sagamu, formed by 13 towns in 1872; the palace at Offin; the line dates from Prince Akarigbo in the early 16th century;
    Oba Babatunde Adewale Ajayi, Torungbuwa II, the 19th Akarigbo since 7 December 2017. The Gazette, 11 June 2024: the
    immediate-past chairman of the Ogun State Council of Traditional Rulers.
  * Olu of Ilaro: Wikipedia 'Ilaro' — the Olu of Ilaro is also the Paramount Ruler of Yewaland; Oba Kehinde Gbadewole
    Olugbenle since 14 April 2012. The Gazette, 11 June 2024: inaugurated as chairman of the state Council of Traditional
    Rulers.
  * Olota of Ota: Wikipedia 'Olota of Ota' — traditional ruler of Ota (Awori); origins in oral and Ifa traditions; Oba
    Adeyemi Abdulkabir Obalanlege from 2018.
Not recorded: the Ogun State Council of Traditional Rulers (only its chairmanship is sourced) and the many other obaships
(gap).
"""
import json, re, sys
import batch_117_ogun_languages_peoples as P117

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = lambda t, org, d, u, n: dict(source_type="news", source_kind="news", source_tier=3, title=t, organisation=org, publication_date=d, url=u,
                                    verification_status="needs_corroboration", notes=n)
SOURCES = {
    "WEAK": WS("Egba Ake", "The Alake of Egbaland rules the Egba in Abeokuta; four sections of Egbaland (Ake, Oke-Ona, Gbagura, Owu); the Alake from Egba Ake, kingmakers the Omo-Iya-Marun; Orile Egba about the 13th century (Samuel Johnson); Abeokuta founded about 1830."),
    "WGBA": WS("Adedotun Aremu Gbadebo III", "Alake since 2 August 2005, of the Laarun ruling house; elected by the Egba kingmakers after Oba Oyebade Lipede died on 3 February 2005."),
    "GZALK": NEWS("Alake of Egbaland endorses PDP candidate Adebutu for 2027 Ogun governorship race", "The Gazette (Nigeria)", "2026-07-19",
                  "https://gazettengr.com/alake-of-egbaland-endorses-pdp-candidate-adebutu-for-2027-ogun-governorship-race/",
                  "'Oba Adedotun Gbadebo, the Alake and Paramount Ruler of Egbaland'; the Egba Traditional Council."),
    "WIJK": WS("Ijebu Kingdom", "Yoruba kingdom formed around the 15th century; dynasty founded by Obanta (Ogborogan of Ile-Ife); capital Ijebu-Ode; Sungbo's Eredo; British war of 1892."),
    "WAWJ": WS("Awujale", "Royal title of the Ijebu Kingdom; four ruling houses (Gbelegbuwa, Anikinaiya, Fusengbuwa, Fidipote) under a 1959 declaration; the most recent Awujale, Sikiru Kayode Adetona, reigned 1960–2025."),
    "WADT": WS("Sikiru Kayode Adetona", "Awujale from 2 April 1960 until his death on 13 July 2025, aged 91; House of Anikinaiya."),
    "ARAWJ": NEWS("Awujale succession race advances as kingmakers submit five princes to Abiodun", "Arise News", "2026-06-19",
                  "https://www.arise.tv/awujale-succession-race-advances-as-kingmakers-submit-five-princes-to-abiodun/",
                  "The Ilamuren kingmakers, led by the Olisa, sent five princes to the governor in a letter of 14 April 2026; the state government had not lifted its suspension of the selection process."),
    "WAKR": WS("Akarigbo of Remo", "Paramount ruler of the 33 towns of Remo; capital Sagamu, formed by 13 towns in 1872; palace at Offin; line from Prince Akarigbo, early 16th century; Oba Babatunde Adewale Ajayi, Torungbuwa II, 19th Akarigbo since 7 December 2017."),
    "WILA": WS("Ilaro", "The Olu of Ilaro is also the Paramount Ruler of Yewaland; Oba Kehinde Gbadewole Olugbenle since 14 April 2012; Ilaro the headquarters of Yewa South."),
    "GZ24": NEWS("Governor Abiodun inaugurates Olu of Ilaro as chairman of Ogun traditional rulers council", "The Gazette (Nigeria)", "2024-06-11",
                 "https://gazettengr.com/governor-abiodun-inaugurates-olu-of-ilaro-as-chairman-of-ogun-traditional-rulers-council/",
                 "Oba Kehinde Olugbenle, the Olu of Ilaro and head of traditional rulers in Yewaland, inaugurated as chairman of the state Council of Traditional Rulers; the Akarigbo, Oba Babatunde Ajayi, the immediate-past chairman."),
    "WOLO": WS("Olota of Ota", "Traditional ruler of Ota; origins traced to oral and Ifa traditions; Oba Adeyemi Abdulkabir Obalanlege from 2018."),
    "WOGS": dict(P117.SOURCES["WOGS"], notes="Reused. Senatorial districts with LGA headquarters: Abeokuta South (Ake), Ijebu Ode, Sagamu, Yewa South (Ilaro), Ado-Odo/Ota (Otta)."),
}
TEXT = {
 "egba": """Egbaland is the traditional kingdom of the Egba, a Yoruba people, centred on Abeokuta; its paramount ruler is the Alake of Egbaland. According to Wikipedia, it has four sections — Ake, Oke-Ona, Gbagura and Owu — and the Alake must come from Egba Ake, whose senior chiefs, the Omo-Iya-Marun, are his kingmakers. Samuel Johnson's history places the founding of Orile Egba, in the Egba forest, around the 13th century, after the Egba left the Oyo empire; Abeokuta was founded about 1830, after the collapse of Oyo. Oba Adedotun Aremu Gbadebo III, of the Laarun ruling house, was elected Alake by the Egba kingmakers and has reigned since 2 August 2005 (Wikipedia). He was still the Alake in July 2026 (The Gazette).""",
 "ijebu": """The Ijebu Kingdom is the traditional Yoruba kingdom of the Ijebu, centred on Ijebu-Ode, whose ruler bears the title Awujale. According to Wikipedia, it was formed around the 15th century, its ruling dynasty founded by Obanta of Ile-Ife, and the great earthworks of Sungbo's Eredo surround its heartland; the British went to war with it in 1892. Under a declaration of 1959, the Awujale is chosen from four ruling houses: Gbelegbuwa, Anikinaiya, Fusengbuwa and Fidipote. Oba Sikiru Kayode Adetona reigned from 2 April 1960 until his death on 13 July 2025 (Wikipedia). In June 2026 the throne was still vacant: the kingmakers had sent five princes to the governor, but the state government had suspended the selection (Arise News).""",
 "remo": """Remo is the traditional kingdom of the Remo, a Yoruba people of Ogun State, whose paramount ruler is the Akarigbo of Remoland. According to Wikipedia, it comprises 33 towns; its capital, Sagamu, was formed in 1872, when 13 towns came together for security, and the Akarigbo's palace is at Offin. The line of Akarigbos dates from a prince of that name in the early 16th century, in the time of the Ijebu Kingdom's founding. Oba Babatunde Adewale Ajayi, Torungbuwa II, became the 19th Akarigbo on 7 December 2017 (Wikipedia), and chaired the Ogun State Council of Traditional Rulers until June 2024 (The Gazette).""",
 "ilaro": """The Olu of Ilaro is the traditional ruler of Ilaro, the headquarters of Yewa South, and is also the Paramount Ruler of Yewaland, the land of the Yewa (formerly Egbado) people (Wikipedia). Oba Kehinde Gbadewole Olugbenle has held the title since 14 April 2012 (Wikipedia), and in June 2024 he was inaugurated as chairman of the Ogun State Council of Traditional Rulers (The Gazette). The history of the title is not described in a source read.""",
 "ota": """The Olota of Ota is the traditional ruler of Ota, the Awori town that is the headquarters of Ado-Odo/Ota LGA. Wikipedia traces the title's origins to oral and Ifa traditions, beginning with a female ruler, Iyarigimoko, and gives Oba Adeyemi Abdulkabir Obalanlege as the Olota since 2018. Its history is not otherwise described in an independent source read.""",
}
REC = [
    ("egba", "Egbaland", "egbaland", ["WEAK", "WGBA", "GZALK"], "well_documented", dict(polity_type="kingdom", is_extant=1, founded_year=1830, founded_text="Orile Egba about the 13th century; Abeokuta about 1830 (Wikipedia)", founded_precision="circa")),
    ("ijebu", "Ijebu Kingdom", "ijebu-kingdom", ["WIJK", "WAWJ", "WADT", "ARAWJ"], "well_documented", dict(polity_type="kingdom", is_extant=1, founded_text="around the 15th century (Wikipedia)", founded_precision="century")),
    ("remo", "Remo Kingdom", "remo-kingdom", ["WAKR", "GZ24"], "well_documented", dict(polity_type="kingdom", is_extant=1, founded_text="the Akarigbo line from the early 16th century; Sagamu formed in 1872 (Wikipedia)", founded_precision="century")),
    ("ilaro", "Olu of Ilaro", "olu-of-ilaro", ["WILA", "GZ24"], "well_documented", dict(polity_type="traditional_title", is_extant=1)),
    ("ota", "Olota of Ota", "olota-of-ota", ["WOLO"], "reported", dict(polity_type="traditional_title", is_extant=1)),
]
RECORDS = []
for key, name, slug, srcs, lvl, extra in REC:
    t = TEXT[key]
    f = dict(name=name, slug=slug, summary=t.split(". ")[0] + ".", description=t)
    f.update(extra)
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl, fields=f, srcs=[(s, name) for s in srcs]))
LG = lambda l: f"@admin_units:lga:ogun/{l}"
SEAT = "Seat (not a statement of full jurisdiction)."
RELATIONS = [
    dict(frm="egba", type="located_in", to=LG("abeokuta-south"), source="WOGS", evidence="multiple_sources", level="well_documented", notes=f"Abeokuta; Wikipedia gives Ake as the headquarters of Abeokuta South. {SEAT}"),
    dict(frm="ijebu", type="located_in", to=LG("ijebu-ode"), source="WIJK", evidence="multiple_sources", level="well_documented", notes=f"Ijebu-Ode, the capital (Wikipedia). {SEAT}"),
    dict(frm="remo", type="located_in", to=LG("shagamu"), source="WAKR", evidence="single_reliable_source", level="well_documented", notes=f"Sagamu, the capital; the palace at Offin (Wikipedia). {SEAT}"),
    dict(frm="ilaro", type="located_in", to=LG("egbado-south"), source="WILA", evidence="single_reliable_source", level="well_documented", notes=f"Ilaro, headquarters of Yewa South (the archive's Egbado South) (Wikipedia). {SEAT}"),
    dict(frm="ota", type="located_in", to=LG("ado-odo-ota"), source="WOLO", evidence="single_reliable_source", level="reported", notes=f"Ota (Wikipedia). {SEAT}"),
    dict(frm="egba", type="associated_with", to="@ethnic_groups:yoruba", role="kingdom of the Egba, a Yoruba sub-group", source="WEAK", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Egba Ake; Egba people)."),
    dict(frm="ijebu", type="associated_with", to="@ethnic_groups:yoruba", role="kingdom of the Ijebu, a Yoruba sub-group", source="WIJK", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Ijebu Kingdom: 'a Yoruba kingdom')."),
    dict(frm="remo", type="associated_with", to="@ethnic_groups:yoruba", role="kingdom of the Remo, a Yoruba sub-group", source="WAKR", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Akarigbo of Remo); the federal profile names the Remo among Ogun's peoples."),
    dict(frm="ilaro", type="associated_with", to="@ethnic_groups:yoruba", role="paramount ruler of the Yewa (formerly Egbado), a Yoruba sub-group", source="WILA", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Ilaro; Yewa)."),
    dict(frm="ota", type="associated_with", to="@ethnic_groups:yoruba", role="ruler of an Awori (Yoruba) town", source="WOLO", evidence="single_reliable_source", level="reported", notes="Wikipedia (Olota of Ota; Yewa: Ado-Odo/Ota the Awori part of Ogun West)."),
    dict(frm="remo", type="associated_with", to="ijebu", role="the Akarigbo line arose with the founding of the Ijebu Kingdom", source="WAKR", evidence="single_reliable_source", level="reported", notes="Wikipedia (Akarigbo of Remo)."),
]
NAMES = [
    dict(record="egba", name="Alake of Egbaland", name_type="alternative", usage_notes="Title of the paramount ruler; also 'Alake of Abeokuta' (Wikipedia).", srcs=["WEAK"]),
    dict(record="ijebu", name="Awujale of Ijebuland", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WAWJ"]),
    dict(record="ijebu", name="Jebu", name_type="alternative", usage_notes="Wikipedia: 'also known as Jebu, Geebu, or Xabu'.", srcs=["WIJK"]),
    dict(record="remo", name="Akarigbo of Remoland", name_type="alternative", usage_notes="Title of the paramount ruler (Wikipedia).", srcs=["WAKR"]),
    dict(record="ilaro", name="Paramount Ruler of Yewaland", name_type="alternative", usage_notes="The Olu of Ilaro's second title (Wikipedia).", srcs=["WILA"]),
]
GAPS = [
    ("Ogun: the Awujale", "The throne has been vacant since Oba Sikiru Adetona died on 13 July 2025; in June 2026 the selection was suspended by the state government after the kingmakers had named five princes (Arise News). A later report is needed."),
    ("Ogun: other obaships", "The Egba section rulers (Osile of Oke-Ona, Agura of Gbagura, Olowu of Owu), the obas of the Remo and Ijebu towns, the Yewa obas under the Olu of Ilaro, and the Ogun State Council of Traditional Rulers need sources."),
    ("Ogun: the Olu of Ilaro and the Olota", "The Olu of Ilaro is confirmed to June 2024 and the Olota only by Wikipedia (from 2018); 2025–26 confirmation is needed."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Ogun traditional institutions: Egbaland (Alake), the Ijebu Kingdom (Awujale), Remo (Akarigbo), the Olu of Ilaro and the Olota of Ota.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 119 — Ogun: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **5 polities:**",
         "  - **Egbaland**, ruled by the Alake: Oba Adedotun Gbadebo III, Alake since 2005, still on the throne in July 2026",
         "  - the **Ijebu Kingdom**, ruled by the Awujale: **vacant** since Oba Sikiru Adetona died on 13 July 2025. In June 2026 the selection was still suspended.",
         "  - **Remo**, ruled by the Akarigbo: Oba Babatunde Ajayi, Akarigbo since 2017",
         "  - the **Olu of Ilaro**, Paramount Ruler of Yewaland: Oba Kehinde Olugbenle since 2012, and chairman of the state council of traditional rulers since June 2024",
         "  - the **Olota of Ota** (Awori): *reported*, from Wikipedia only",
         "- **Handled with care:**",
         "  - **The Awujale succession** is stated neutrally: five princes were nominated, but the process was suspended. No new Awujale is named.",
         "  - **Each current ruler is dated to the latest source read.** Gaps list where 2025–26 confirmation is still needed.",
         "  - **The Olota's early history** is attributed to oral and Ifa tradition, as Wikipedia gives it.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, name, slug, srcs, lvl, extra in REC:
        L += [f"## {name} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_119_ogun_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_119_ogun_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
