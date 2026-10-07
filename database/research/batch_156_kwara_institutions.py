"""
Research batch 156 — Kwara (Phase 3): traditional institutions. Researched 2026-10-07. Pattern: batches 144, 150.

  * Ilorin (the Emir): Wikipedia 'Ilorin Emirate' — first settled by Yoruba from Oyo; about 1810 the Oyo warlord Afonja
    and the scholar Shehu Alimi (Salih Janta) allied against Oyo, with help from Usman dan Fodio; Ilorin's forces burned
    Oyo-Ile; Abdulsalam, Alimi's son, became ruler and Afonja was killed; an emirate of the Sokoto Caliphate under Gwandu;
    defeated by Ibadan at Osogbo in 1838. University of Ilorin (27 Apr 2026): Alhaji Ibrahim Sulu-Gambari, 86 on
    22 April 2026, the 11th Emir since August 1995, chairman of the Kwara State Council of Chiefs. Peoples Gazette
    (9 Mar 2026): chairman of the Kwara Traditional Rulers Council.
  * Offa (the Olofa): Wikipedia 'Offa, Nigeria' — founded in the late 14th century by Olofagangan; 24 Olofas since; five
    high chiefs (Essa, Ojomu, Sawo, Asalofa, Balogun); Offa the cultural headquarters of the Ibolo; the Jalumi War with
    Ilorin. ThisDay (1 Jul 2016): the Supreme Court affirmed Oba Mufutau Gbadamosi as Olofa and held Anilelerin the only
    ruling house. allschool.ng (25 Mar 2026): Oba Mufutau Muhammad Gbadamosi Oloyede, Okikiola Esuwoye II, named pioneer
    chancellor of the University of Offa.
  * Pategi (the Etsu): Wikipedia 'Pategi Emirate' — founded 1898 by Idrisu Gana, who led the Kede, a Nupe sub-group,
    from Bida; list of Etsus; Umar Bologi II from 2019; the Pategi Regatta (first held 1952). Voice of Nigeria (16 May
    2023): Etsu Patigi Umar Bologi II named Amirul-Hajj.
  * Lafiagi (the Emir): New Telegraph (2 Jul 2023): Emir Alhaji Mohammed Kudu Kawu at the Sallah Nko festival of
    Lafiagi Emirate, Edu LGA. Wikipedia 'Edu, Nigeria': Lafiagi the LGA headquarters; a Nupe-speaking area.
Placement: Ilorin → Kwara State (the city spans three LGAs; the palace's LGA is not given); Offa → Offa; Pategi →
Pategi; Lafiagi → Edu.
"""
import json, re, sys
import batch_144_ondo_institutions as I144
import batch_154_kwara_languages_peoples as P154

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = I144.NEWS
SOURCES = {
    "WILE": WS("Ilorin Emirate", "First settled by Yoruba from Oyo; the alliance of Afonja and Shehu Alimi about 1810; Oyo-Ile burned; Abdulsalam, Alimi's son, ruler; an emirate of the Sokoto Caliphate under Gwandu; defeated by Ibadan at Osogbo, 1838; a major slave market (Stone)."),
    "UNI26": NEWS("Egbewole salutes Emir of Ilorin on 86th birthday", "University of Ilorin", "2026-04-27", "https://www.unilorin.edu.ng/egbewole-salutes-emir-of-ilorin-on-86th-birthday/",
                  "Alhaji (Dr) Ibrahim Sulu-Gambari, CFR, 86 on 22 April 2026; a former Justice of the Court of Appeal; 'his historic emergence as the 11th Emir of Ilorin in August, 1995'; chairman of the Kwara State Council of Chiefs."),
    "PG26I": NEWS("Umrah: Emir of Ilorin urges pilgrims to pray for peace", "Peoples Gazette", "2026-03-09", "https://gazettengr.com/umrah-emir-of-ilorin-urges-pilgrims-to-pray-for-peace/",
                  "The Emir of Ilorin, Ibrahim Sulu-Gambari, 'chairman of the Kwara Traditional Rulers Council'."),
    "WOFFA": dict(P154.SOURCES["WOFFA"], notes="Reused. The Olofa, assisted by five high chiefs (Essa, Ojomu, Sawo, Asalofa, Balogun); 24 Olofas since the town's founding in the late 14th century by Olofagangan; Offa the cultural headquarters of the Ibolo; the Jalumi War with Ilorin."),
    "TD16": NEWS("S'Court Says Only One Ruling House Can Produce an Oba for Offa", "ThisDay", "2016-07-01", "https://thisdaylive.com/index.php/2016/07/01/scourt-says-only-one-ruling-house-can-produce-an-oba-for-offa",
                 "The Supreme Court affirmed Oba Mufutau Gbadamosi as Olofa and held the Anilelerin ruling house the only one entitled to the throne, rejecting the Olugbense family's claim. Date from the URL."),
    "AS26": NEWS("University of Offa Announces Olofa of Offa as pioneer Chancellor", "allschool.ng (University of Offa announcement)", "2026-03-25", "https://allschool.ng/university-of-offa-announces-olofa-of-offa-as-pioneer-chancellor/",
                 "Oba Mufutau Muhammad Gbadamosi Oloyede, Okikiola Esuwoye II, Olofa of Offa, named pioneer chancellor on 24 March 2026."),
    "WPATE": WS("Pategi Emirate", "Founded 1898 by Idrisu Gana, leading the Kede, a Nupe sub-group, from Bida; inhabited by Nupe and Yoruba; the Pategi Regatta (founded 1949, first held 1952); list of Etsus to Umar Bologi II (2019–)."),
    "VON23": NEWS("Kwara State Governor Appoints Etsu Patigi As Amirul-Hajj 2023", "Voice of Nigeria", "2023-05-16", "https://von.gov.ng/kwara-state-governor-appoints-etsu-patigi-as-amirul-hajj-2023/",
                  "The Etsu Patigi, Alhaji Umar Bologi II, appointed Amirul-Hajj for 2023."),
    "NT23": NEWS("Kwara Gov Restates Support For Tourism As Lafiagi Holds Sallah Nko Festival", "New Telegraph", "2023-07-02", "https://newtelegraphng.com/kwara-gov-restates-support-for-tourism-as-lafiagi-holds-sallah-nko-festival/",
                 "The Sallah Nko festival of Lafiagi Emirate, Edu LGA, at which village heads pay homage to the Emir; Emir Alhaji Mohammed Kudu Kawu."),
    "WEDU": P154.SOURCES["WEDU"],
    "WPAT": P154.SOURCES["WPAT"],
}
TEXT = {
 "ilorin": """The Ilorin Emirate is the traditional state of Ilorin, the capital of Kwara State, and its ruler is the Emir of Ilorin. According to Wikipedia, Ilorin was first settled by Yoruba from Oyo; about 1810 the Oyo warlord Afonja and the Muslim scholar Shehu Alimi allied against the Alaafin, with help sent by Usman dan Fodio, and Ilorin's forces went on to burn Oyo-Ile, the capital of the Oyo Empire. Alimi's son Abdulsalam became ruler, Afonja was killed, and Ilorin became an emirate of the Sokoto Caliphate under Gwandu; its southward advance ended when Ibadan defeated it at Osogbo in 1838. Its people are mainly Yoruba, though its ruling house has Fulani heritage. The present Emir, Alhaji Ibrahim Sulu-Gambari, a former Justice of the Court of Appeal, became the 11th Emir in August 1995 and turned 86 in April 2026; he chairs the Kwara State council of traditional rulers (University of Ilorin; Peoples Gazette).""",
 "offa": """Offa is a Yoruba kingdom of Kwara State, the cultural headquarters of the Ibolo, and its ruler is the Olofa. According to Wikipedia, the town was founded in the late fourteenth century by Olofagangan, 'the warrior with a sharp arrow', whose name it bears, and twenty-four Olofas have reigned since; the Olofa rules with five high chiefs, the Essa, Ojomu, Sawo, Asalofa and Balogun. In the nineteenth century Offa fought the Jalumi War against Ilorin. In 2016 the Supreme Court affirmed Oba Mufutau Gbadamosi as Olofa and held the Anilelerin family to be the only ruling house (ThisDay); as Okikiola Esuwoye II he was named pioneer chancellor of the University of Offa in March 2026.""",
 "pategi": """The Pategi Emirate is a Nupe traditional state on the Niger in Kwara State, and its ruler is the Etsu Pategi. According to Wikipedia, it was founded in 1898 by Idrisu Gana, who led the Kede, a Nupe sub-group, away from Bida, and its people are mainly Nupe, with Yoruba; they are farmers, traders and fishermen. The emirate's traditional council founded the Pategi Regatta, a canoe festival on the Niger, first held in 1952. Wikipedia lists the Etsus from Idrisu Gana to Umar Bologi II, appointed in 2019, who was named Amirul-Hajj by the state governor in 2023 (Voice of Nigeria).""",
 "lafiagi": """The Lafiagi Emirate is the traditional state of Lafiagi, the headquarters of Edu LGA, a Nupe-speaking area of Kwara State (Wikipedia). Its ruler is the Emir of Lafiagi. In July 2023 Emir Alhaji Mohammed Kudu Kawu hosted the emirate's Sallah Nko festival, a yearly gathering at which the village heads and the people of the emirate pay homage to the Emir during the Eid celebrations (New Telegraph). The emirate's history is not described in a source read.""",
}
REC = [
    ("ilorin", "Ilorin Emirate", "ilorin-emirate", "emirate", ["WILE", "UNI26", "PG26I"], "well_documented"),
    ("offa", "Offa Kingdom", "offa-kingdom", "kingdom", ["WOFFA", "TD16", "AS26"], "well_documented"),
    ("pategi", "Pategi Emirate", "pategi-emirate", "emirate", ["WPATE", "VON23", "WPAT"], "reported"),
    ("lafiagi", "Lafiagi Emirate", "lafiagi-emirate", "emirate", ["NT23", "WEDU"], "reported"),
]
RECORDS = [dict(key=k, table="polities", evidence="multiple_sources", level=lvl,
                fields=dict(name=n, slug=s, polity_type=t, is_extant=1, summary=TEXT[k].split(". ")[0] + ".", description=TEXT[k]),
                srcs=[(x, n) for x in srcs]) for k, n, s, t, srcs, lvl in REC]
RECORDS[2]["fields"].update(founded_year=1898, founded_text="Founded 1898 by Idrisu Gana (Wikipedia)", founded_precision="year")
LG = lambda l: f"@admin_units:lga:kwara/{l}"
SEAT = "Seat (not a statement of full jurisdiction)."
RELATIONS = [
    dict(frm="ilorin", type="located_in", to="@admin_units:state:kwara", source="WILE", evidence="multiple_sources", level="well_documented", notes=f"Ilorin, the state capital; the city spans Ilorin East, South and West, and the palace's LGA is not given in a source read. {SEAT}"),
    dict(frm="offa", type="located_in", to=LG("offa"), source="WOFFA", evidence="multiple_sources", level="well_documented", notes=f"Offa, a city and LGA (Wikipedia). {SEAT}"),
    dict(frm="pategi", type="located_in", to=LG("pategi"), source="WPATE", evidence="multiple_sources", level="well_documented", notes=f"Pategi, 'the headquarters of Pategi Emirate' (Wikipedia). {SEAT}"),
    dict(frm="lafiagi", type="located_in", to=LG("edu"), source="NT23", evidence="multiple_sources", level="well_documented", notes=f"Lafiagi, headquarters of Edu LGA (Wikipedia; New Telegraph). {SEAT}"),
    dict(frm="ilorin", type="associated_with", to="@ethnic_groups:yoruba", role="emirate of a mainly Yoruba city", source="WILE", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Ilorin Emirate; Ilorin)."),
    dict(frm="ilorin", type="associated_with", to="@ethnic_groups:fulani", role="ruling house of Fulani heritage (descendants of Shehu Alimi)", source="WILE", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Ilorin): 'its traditional ruler has a Fulani heritage'."),
    dict(frm="ilorin", type="associated_with", to="@polities:oyo-empire", role="Ilorin broke from Oyo about 1810 and burned Oyo-Ile", source="WILE", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Ilorin Emirate)."),
    dict(frm="offa", type="associated_with", to="@ethnic_groups:yoruba", role="kingdom of the Ibolo, a Yoruba sub-group", source="WOFFA", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Offa): 'the cultural headquarters of the Ibolo people'."),
    dict(frm="offa", type="associated_with", to="ilorin", role="the Jalumi War between Offa and Ilorin", source="WOFFA", evidence="single_reliable_source", level="reported", notes="Wikipedia (Offa)."),
    dict(frm="pategi", type="associated_with", to="@ethnic_groups:nupe", role="Nupe emirate founded by the Kede", source="WPATE", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Pategi Emirate; Pategi)."),
    dict(frm="lafiagi", type="associated_with", to="@ethnic_groups:nupe", role="emirate of a Nupe-speaking area", source="WEDU", evidence="single_reliable_source", level="reported", notes="Wikipedia (Edu): 'A Nupe speaking area'."),
]
NAMES = [
    dict(record="ilorin", name="Emir of Ilorin", name_type="alternative", usage_notes="Title of the ruler.", srcs=["UNI26"]),
    dict(record="offa", name="Olofa of Offa", name_type="alternative", usage_notes="Title of the ruler (Wikipedia; ThisDay writes Oloffa).", srcs=["WOFFA"]),
    dict(record="pategi", name="Etsu Pategi", name_type="alternative", usage_notes="Title of the ruler; also written Etsu Patigi (Voice of Nigeria).", srcs=["WPATE"]),
    dict(record="pategi", name="Patigi Emirate", name_type="spelling_variant", usage_notes="Wikipedia's article opens 'Patigi Emirate'.", srcs=["WPATE"]),
    dict(record="lafiagi", name="Emir of Lafiagi", name_type="alternative", usage_notes="Title of the ruler.", srcs=["NT23"]),
]
GAPS = [
    ("Kwara: Sokoto Caliphate and Gwandu", "Ilorin became an emirate of the Sokoto Caliphate under Gwandu (Wikipedia); the archive has no Sokoto Caliphate or Gwandu record yet, so no link is made."),
    ("Kwara: Pategi and Lafiagi rulers", "The latest sources read naming the Etsu Pategi and the Emir of Lafiagi are from 2023; both records are kept as reported until a 2025–26 source is found."),
    ("Kwara: other thrones", "The Emir of Kaiama, the Emir of Shonga, the Olomu of Omu-Aran, the Elerin of Erin-Ile, the Olupo of Ajase-Ipo and the Orangun of Oke-Ode, among others, need sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kwara traditional institutions: the Ilorin Emirate, Offa (Olofa), the Pategi Emirate and the Lafiagi Emirate.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 156 — Kwara: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **4 polities:**",
         "  - the **Ilorin Emirate**: Emir Ibrahim Sulu-Gambari, the 11th Emir since 1995, confirmed April 2026",
         "  - **Offa** (the Olofa): Oba Mufutau Gbadamosi, Esuwoye II, confirmed March 2026. The Supreme Court upheld his appointment in 2016.",
         "  - the **Pategi Emirate** (the Etsu, Nupe): Umar Bologi II, since 2019. *Reported*: the latest source is from 2023.",
         "  - the **Lafiagi Emirate**: Emir Mohammed Kudu Kawu. *Reported*: the latest source is from 2023.",
         "- **Links:** Ilorin to the Yoruba, the Fulani and the Oyo Empire; Offa to Ilorin through the Jalumi War; Pategi and Lafiagi to the Nupe.",
         "- **Ilorin's seat** is linked to the state, not an LGA, because the city spans three LGAs and no source gives the palace's LGA.", "",
         "## The records", ""]
    for k, n, s, t, srcs, lvl in REC:
        L += [f"### {n} ({words(TEXT[k])} words; {lvl})", "", f"> {TEXT[k]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_156_kwara_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_156_kwara_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
