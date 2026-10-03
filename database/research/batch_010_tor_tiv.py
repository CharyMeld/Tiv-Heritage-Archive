"""
Research batch 010 — the Tor Tiv, the Tiv Traditional Council (Ijirtamen) and the six Tiv
intermediate areas of Benue State (researched 2026-09-25). Owner's option (d).

New national records (polities): the Tor Tiv title, the Tiv Traditional Council and six
intermediate areas. Links: each of the 14 Tiv LGAs of Benue to its intermediate area, the
areas to the council, the five Tor Tivs of the Tiv collection to the title.

Evidence notes:
  * The six areas and their LGAs were given only by I am Benue until this batch. Daily Trust
    (2019) confirms the six names from the 2015 Benue chieftaincy law; the LGAs are confirmed
    by ThisDay (2021: Lobi = Makurdi + Guma) and by an opinion article on The Nigerian Voice
    (2016: Sankera, Kwande, Jechira, Jemgbagh, and "Minda" = Makurdi, Guma, Gwer East, Gwer West).
  * Five or six? Several writers (Vanguard 2016 interview, the 2016 opinion article) speak of
    five Tiv blocs in Benue, with Minda as one. The 2015 law created separate Lobi and Gwer
    councils, and ThisDay (2021) reports that the question is disputed in Benue politics. Both
    views are stated and attributed; neither is chosen.
  * The opinion article (which also makes unrelated origin claims) is used only for the LGA
    groupings, which it states plainly, never for history.
  * The Tor Tiv list and years: Wikipedia and I am Benue agree; Ayatse's accession is 2016
    (I am Benue) or 4 March 2017, the coronation (Wikipedia).
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "WTT": dict(source_type="encyclopedia", title="Tor Tiv", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Tor_Tiv",
                verification_status="needs_corroboration",
                notes="Title Begha u Tiv; stool established 1946 by the colonial administration after the Tiv Central Council; seat at Gboko; head of Ijirtamen (Tiv Traditional Council), which comprises all chiefs in Tivland and sits at least once a year; rotation between Ipusu and Ichongo under the Benue State council of chiefs law; chairman of the Benue State Council of Chiefs; origin (Tiv Division, Audu Dan Afoda 1927, post-war agitation by Makir Zakpe and Lawrence Igyuse Doki); colonial role; list of Tor Tivs with dates."),
    "WMZ": dict(source_type="encyclopedia", title="Makir Zakpe", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Makir_Zakpe",
                verification_status="needs_corroboration",
                notes="Soldier, police officer and first Tor Tiv; selected for the new office on 19 September 1946; Tor Tiv until his death in 1956."),
    "IAMB2": dict(source_type="website", title="Indigenous administrative structure and institutions", organisation="I am Benue",
                  url="http://www.iambenue.com/benue-state/benue-state/indigenous-administrative-structure-and-institutions/",
                  verification_status="needs_corroboration", notes="Reused (batch 006)."),
    "DT": dict(source_type="news", title="Abu King Shuluwa: From the soapbox to Tiv royalty", author="Hope Abah Emmanuel",
               organisation="Daily Trust", publication_date="2019-06-30", url="https://dailytrust.com/abu-king-shuluwa-from-the-soapbox-to-tiv-royalty/",
               verification_status="needs_corroboration",
               notes="On 7 April 2015 the Benue State House of Assembly passed a chieftaincy law allowing ten intermediate area traditional councils, including Jechira, Jemgbagh, Kwande, Sankera, Gwer and Lobi for the Tiv; the law was amended under Governor Ortom; first-class chiefs received their staffs of office on 14 May 2018. The new stools alternate between the Ipusu and Ichongo houses."),
    "TD": dict(source_type="news", title="Politics of Entitlement Takes Centerstage in Benue", organisation="ThisDay",
               publication_date="2021-02-09", url="https://thisdaylive.com/index.php/2021/02/09/politics-of-entitlement-takes-centerstage-in-benue",
               verification_status="needs_corroboration",
               notes="Lobi comprises Makurdi and Guma LGAs; the Tor Lobi chairs the Lobi Intermediate Area Traditional Council; some argue for five Tiv blocs (Kwande, Jechira, Jemgbagh, Sankera, Minda), others for six first-class chiefdoms, with Minda holding two, Lobi and Gwer."),
    "VG": dict(source_type="news", title="My plans for Tiv nation – Prof Ayatse", author="Peter Duru", organisation="Vanguard",
               publication_date="2016-12-17", url="https://www.vanguardngr.com/2016/12/plans-tiv-nation-prof-ayatse/",
               verification_status="needs_corroboration",
               notes="Interview before the selection of Tor Tiv V. Ayatse, from Shangev-ya in Kwande, names the Jechira, Jemgbagh, Minda, Sankera and Kwande intermediate areas and says Kwande is the only one that has not held the stool."),
    "NV": dict(source_type="news", title="On a new Tor Tiv", author="Donald Terfa Gaadi", organisation="The Nigerian Voice",
               publication_date="2016-10-14", url="https://www.thenigerianvoice.com/news/233073/on-a-new-tor-tiv.html",
               verification_status="needs_corroboration",
               notes="Opinion article (credited to pointblanknews.com). Used ONLY for its grouping of the Benue Tiv LGAs into five blocks: Sankera (Katsina-Ala, Ukum, Logo), Kwande (Kwande, Ushongo), Jechira (Vandeikya, Konshisha), Jemgbagh (Gboko, Buruku, Tarka) and Minda (Makurdi, Guma, Gwer East, Gwer West)."),
}

TT_DESCRIPTION = """The Tor Tiv is the paramount traditional ruler of the Tiv people. Wikipedia gives the title Begha u Tiv, "Lion of the Tiv", and describes the Tor Tiv as a symbol of Tiv unity. The seat of the Tor Tiv is at Gboko in Benue State.

Tiv society had no single ruler before colonial rule. I am Benue, a Benue community website, describes a system in which the elders of each lineage settled disputes. It says the earliest Tiv chieftaincy title, Tor Agbande ("drum chief"), was borrowed from the Jukun and gave prestige rather than power.

The office itself was created by the British colonial administration in 1946. Wikipedia explains that the British found the large Tiv area hard to govern. They set up a Tiv Division and a Tiv Central Council, and in 1927 placed the council under Audu Dan Afoda, the chief of Makurdi, who was not Tiv. Educated Tiv and chiefs resented this. After the Second World War, returning soldiers such as Makir Zakpe and Lawrence Igyuse Doki joined Tiv officials in the colonial service in calling for a Tiv paramount ruler. Makir Zakpe, a retired army sergeant, was selected as the first Tor Tiv on 19 September 1946.

Five men have held the title. Wikipedia and I am Benue give the same years: Makir Zakpe (1946–1956), Gondo Aluor (1956–1978), James Akperan Orshi (1979–1990), Alfred Akawe Torkula (1991–2015) and James Ayatse. Ayatse was crowned at the J. S. Tarka Stadium in Gboko on 4 March 2017, according to Wikipedia; I am Benue dates his accession to 2016.

Wikipedia states that, under the Benue State law on the council of chiefs, the stool rotates between the descendants of Tiv's two sons, Ipusu and Ichongo, the two ruling houses. Some also look for the office to pass among the intermediate areas of Tivland. In a 2016 interview with Vanguard before the selection, Ayatse, who comes from Kwande, argued that Kwande was the only intermediate area that had not yet produced a Tor Tiv."""

TT_GOVERNANCE = """The Tor Tiv heads the Tiv Traditional Council, known in Tiv as Ijirtamen (see Connections). Wikipedia also names him chairman of the Benue State Council of Chiefs. In the colonial period, it says, the Tor Tiv worked under British officials, collected poll tax and presided over the Tiv Central Council as chief judge in customary and criminal cases.

Below the Tor Tiv, I am Benue describes six first-class chiefs, each titled Tor, who head the six intermediate areas. Below them are second-class chiefs, each titled Ter, who head one local government area each."""

TTC_DESCRIPTION = """The Tiv Traditional Council, called Ijirtamen or Ijir Tamen in Tiv, is headed by the Tor Tiv. Wikipedia describes it as the highest policy-making body of the Tiv people. It says the council includes all the chiefs in Tivland and sits at least once a year. Wikipedia uses the same name, Ijir Tamen, for the Tiv Central Council of the colonial period, over which the Tor Tiv presided from 1946.

I am Benue describes a three-tier system of traditional councils in Benue State. Local government area traditional councils are made up of district heads. Above them are area traditional councils, of which the Tiv Traditional Council is one. The Tiv Traditional Council covers the fourteen Tiv local government areas of Benue State, grouped into six intermediate areas (see Connections)."""

AREA_COMMON = ("Each intermediate area is headed by a first-class chief with the title Tor. According to Daily Trust, a Benue State chieftaincy law passed on 7 April 2015 "
               "provided for intermediate area traditional councils for Jechira, Jemgbagh, Kwande, Sankera, Gwer and Lobi. The first-class chiefs received their staffs of office on 14 May 2018. "
               "I am Benue credits the introduction of this tier to Tor Tiv IV, Alfred Akawe Torkula.")
MINDA = ("Makurdi, Guma, Gwer East and Gwer West are also often spoken of together as Minda. A 2016 Vanguard interview and a 2016 opinion article on The Nigerian Voice both count five "
         "Tiv blocs in Benue, with Minda as one of them. ThisDay reported in 2021 that whether Minda counts as one area or as two, Lobi and Gwer, was disputed in Benue politics.")

AREAS = [
    # key, name, LGAs, the sources confirming the LGAs besides I am Benue, extra sentence
    ("jemgbagh", "Jemgbagh", ["gboko", "buruku", "tarka"], ["NV"],
     "I am Benue gives Buruku as the home of Tor Tiv III, James Akperan Orshi."),
    ("jechira", "Jechira", ["vandeikya", "konshisha"], ["NV"],
     "I am Benue gives Vandeikya as the home of the first Tor Tiv, Makir Zakpe."),
    ("kwande", "Kwande", ["kwande", "ushongo"], ["NV"],
     "Tor Tiv V, James Ayatse, comes from Shangev-ya in Kwande, according to his 2016 Vanguard interview. In that interview he argued that Kwande was the only intermediate area that had not yet produced a Tor Tiv."),
    ("sankera", "Sankera", ["katsina-ala", "ukum", "logo"], ["NV"],
     "I am Benue gives Logo as the home of Tor Tiv II, Gondo Aluor. Daily Trust reported in 2019 that the stool of the Tor Sankera, like the other new stools, alternates between the Ipusu and Ichongo houses."),
    ("lobi", "Lobi", ["makurdi", "guma"], ["TD"],
     "I am Benue gives Guma as the home of Tor Tiv IV, Alfred Akawe Torkula. ThisDay describes the Tor Lobi as chairman of the Lobi Intermediate Area Traditional Council. " + MINDA),
    ("gwer", "Gwer", ["gwer-east", "gwer-west"], ["NV"], MINDA),
]
LGA_NAME = {"gboko": "Gboko", "buruku": "Buruku", "tarka": "Tarka", "vandeikya": "Vandeikya", "konshisha": "Konshisha", "kwande": "Kwande",
            "ushongo": "Ushongo", "katsina-ala": "Katsina-Ala", "ukum": "Ukum", "logo": "Logo", "makurdi": "Makurdi", "guma": "Guma",
            "gwer-east": "Gwer East", "gwer-west": "Gwer West"}


def join(xs):
    return xs[0] if len(xs) == 1 else ", ".join(xs[:-1]) + " and " + xs[-1]


def area_text(name, lgas, extra):
    return (f"{name} is one of the six intermediate areas into which the Tiv Traditional Council groups the Tiv local government areas of Benue State. "
            f"It covers {join([LGA_NAME[l] for l in lgas])} local government areas, and its first-class chief is the Tor {name}.\n\n{AREA_COMMON}\n\n{extra}")


RECORDS = [
    dict(key="tortiv", table="polities", evidence="multiple_sources",
         fields=dict(polity_type="traditional_title", name="Tor Tiv", slug="tor-tiv", is_extant=1,
                     founded_year=1946, founded_text="19 September 1946 (first Tor Tiv selected)", founded_precision="exact",
                     summary="The Tor Tiv is the paramount traditional ruler of the Tiv people, with his seat at Gboko in Benue State. The office was created under British rule in 1946.",
                     description=TT_DESCRIPTION, governance=TT_GOVERNANCE),
         srcs=[("WTT", "Title, seat, 1946 origin, colonial role, list and dates, Ipusu–Ichongo rotation, council"),
               ("WMZ", "Makir Zakpe selected first Tor Tiv on 19 September 1946"),
               ("IAMB2", "Pre-colonial system; Tor Agbande; Makir Zakpe; list and years; home LGAs; six first-class chiefs; Ter"),
               ("VG", "Ayatse's 2016 interview: Kwande had not produced a Tor Tiv")]),
    dict(key="ttc", table="polities", evidence="multiple_sources",
         fields=dict(polity_type="traditional_council", name="Tiv Traditional Council", slug="tiv-traditional-council", is_extant=1,
                     summary="The Tiv Traditional Council (Ijirtamen) is the council of Tiv chiefs headed by the Tor Tiv. It covers the fourteen Tiv local government areas of Benue State.",
                     description=TTC_DESCRIPTION),
         srcs=[("WTT", "Ijirtamen: highest policy-making body; all chiefs; sits at least yearly; Tiv Central Council"),
               ("IAMB2", "Three-tier council system; Tiv Traditional Council; fourteen LGAs in six intermediate areas")]),
]
for key, name, lgas, second, extra in AREAS:
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources",
                        fields=dict(polity_type="traditional_council", name=f"{name} Intermediate Area", slug=f"{key}-intermediate-area", is_extant=1,
                                    founded_year=2015, founded_text="7 April 2015 (Benue State chieftaincy law providing for its traditional council)", founded_precision="exact",
                                    summary=f"{name} is one of the six Tiv intermediate areas of Benue State, covering {join([LGA_NAME[l] for l in lgas])} local government areas and headed by the Tor {name}.",
                                    description=area_text(name, lgas, extra)),
                        srcs=[("IAMB2", f"{name}: its LGAs and its first-class chief, the Tor {name}"),
                              ("DT", "2015 law providing for the intermediate area traditional councils; staffs of office 14 May 2018")]
                             + [(s, f"{name}'s LGAs") for s in second]
                             + ([("TD", "Minda: one area or two (Lobi and Gwer)"), ("VG", "Five intermediate areas including Minda")] if key in ("lobi", "gwer") else [])
                             + ([("VG", "Ayatse from Kwande; Kwande had not produced a Tor Tiv")] if key == "kwande" else [])))

NAMES = [
    dict(record="tortiv", name="Begha u Tiv", name_type="alternative", usage_notes='Title meaning "Lion of the Tiv" (Wikipedia).', srcs=["WTT"]),
    dict(record="ttc", name="Ijirtamen", name_type="endonym", usage_notes="Tiv name of the council; also written Ijir Tamen.", srcs=["WTT"]),
]
# "Minda" names Lobi and Gwer together, not one record, so it is kept in the prose only.

RELATIONS = [
    dict(frm="tortiv", type="associated_with", to="ttc", role="head of the council", source="WTT", evidence="multiple_sources",
         notes="The Tor Tiv heads the Tiv Traditional Council (Wikipedia; I am Benue)."),
    dict(frm="tortiv", type="associated_with", to="@ethnic_groups:tiv", role="paramount ruler", source="WTT", evidence="multiple_sources",
         notes="Paramount ruler of the Tiv people (Wikipedia; I am Benue)."),
]
for key, name, lgas, second, extra in AREAS:
    RELATIONS.append(dict(frm=key, type="part_of", to="ttc", source="IAMB2", evidence="multiple_sources",
                          notes="One of the six intermediate areas under the Tiv Traditional Council (I am Benue; the council named in the 2015 law, Daily Trust)."))
    others = {"NV": "an opinion article on The Nigerian Voice (2016)", "TD": "ThisDay (2021)"}
    for l in lgas:
        RELATIONS.append(dict(frm=f"@admin_units:lga:{l}", type="part_of", to=key, source="IAMB2", evidence="multiple_sources",
                              notes=f"Given by I am Benue and by {join([others[s] for s in second])}"
                                    + (" (which counts it under Minda)" if key == "gwer" else "") + "."))
HOLDERS = [("makir-zakpe-1", 1946, 1956, "Tor Tiv I"), ("gondo-aluor-2", 1956, 1978, "Tor Tiv II"), ("james-akperan-orshi-3", 1979, 1990, "Tor Tiv III"),
           ("alfred-akawe-torkula-4", 1991, 2015, "Tor Tiv IV"), ("james-ayatse-5", 2017, None, "Tor Tiv V")]
for slug, a, b, role in HOLDERS:
    note = "Years as given by Wikipedia and I am Benue."
    if role == "Tor Tiv V":
        note = "Crowned 4 March 2017 (Wikipedia); I am Benue dates his accession to 2016."
    RELATIONS.append(dict(frm=f"@historical_figures:{slug}", type="held_title", to="tortiv", role=role, valid_from_year=a, valid_to_year=b,
                          **({} if b else {"valid_to_text": "present"}),
                          precision="year", source="WTT", evidence="multiple_sources", notes=note))

GAPS = [
    ("Five blocs or six intermediate areas", "Vanguard (2016) and an opinion article (2016) speak of five Tiv blocs in Benue, with Minda (Makurdi, Guma, Gwer East, Gwer West) as one. The 2015 law and I am Benue have six, with Lobi and Gwer separate. ThisDay (2021) reports this as disputed. Both are recorded, attributed; needs the text of the law."),
    ("The Benue chieftaincy law", "The law of 7 April 2015, its later amendment and the section on Ipusu–Ichongo rotation (cited by Wikipedia as section 16(1), schedule 3) have not been seen directly. Needs the gazetted text."),
    ("Tiv blocs outside Benue", "The 2016 opinion article describes Central and Southern Tiv blocs in Taraba and an 'Ichongo' bloc in Nasarawa. Single opinion source; not recorded."),
    ("Lineages in each intermediate area", "Which clans (Kparev, Ugondo, Iharev, Nongov, Masev, Shitile, Ukum, Turan …) belong to each area is given only by the 2016 opinion article. Not recorded."),
    ("Current first- and second-class chiefs", "I am Benue names the current Tor of each area and the Ter of each LGA. They are not recorded as people; they change, and each needs its own sources."),
    ("Ayatse's accession: 2016 or 2017", "I am Benue says 2016 (after Torkula's death); Wikipedia gives the coronation, 4 March 2017. The link uses 2017 and notes both."),
    ("Orshi's installation", "Wikipedia gives 10 March 1979; the Tiv collection's record of James Akperan Orshi says April 1979. Needs a primary source."),
    ("Tor Agbande and Jukun influence", "I am Benue says the earliest Tiv chieftaincy title, Tor Agbande, came from the Jukun (Aku Uka of Wukari). Single source; attributed."),
    ("Pre-colonial Tiv political organisation", "Only summarised from I am Benue. Scholarly accounts (e.g. Bohannan; Dorward) should be used in the planned origins-and-history batch."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="The Tor Tiv, the Tiv Traditional Council (Ijirtamen) and the six Tiv intermediate areas of Benue State, linked to the 14 Tiv LGAs and to the five Tor Tivs of the Tiv collection.")


def report():
    L = ["# Research batch 010 — The Tor Tiv, the Tiv Traditional Council and the six intermediate areas", "",
         f"Researched {ACCESSED}. Owner's option (d). The new records are created **in review** and are published only after approval.", "",
         f"- Tor Tiv: **{words(TT_DESCRIPTION) + words(TT_GOVERNANCE)} words**. It passes the 300-word rule, so it will be indexable once published.",
         f"- Tiv Traditional Council: {words(TTC_DESCRIPTION)} words, so the page stays noindex.",
         f"- Six intermediate areas: about {words(area_text('Lobi', ['makurdi', 'guma'], AREAS[4][4]))} words each at most, so they stay noindex.",
         f"- {len(RELATIONS)} links: 14 LGAs → their area, 6 areas → the council, the Tor Tiv → the council and the Tiv people, and the 5 Tor Tivs of the Tiv collection → the title.", "",
         "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}{', ' + s['author'] if s.get('author') else ''}{', ' + s['publication_date'] if s.get('publication_date') else ''}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    L += ["", "## Tor Tiv — Overview (description)", ""] + [f"> {p}" if p else ">" for p in TT_DESCRIPTION.split("\n")]
    L += ["", "## Tor Tiv — Governance", ""] + [f"> {p}" if p else ">" for p in TT_GOVERNANCE.split("\n")]
    L += ["", "## Tiv Traditional Council — Overview", ""] + [f"> {p}" if p else ">" for p in TTC_DESCRIPTION.split("\n")]
    for key, name, lgas, second, extra in AREAS:
        L += ["", f"## {name} Intermediate Area", ""] + [f"> {p}" if p else ">" for p in area_text(name, lgas, extra).split("\n")]
    L += ["", "## The six areas and their LGAs", "", "| Area | LGAs | I am Benue | Second source |", "|---|---|---|---|"]
    for key, name, lgas, second, extra in AREAS:
        L.append(f"| {name} | {', '.join(LGA_NAME[l] for l in lgas)} | yes | {'; '.join({'NV': 'Nigerian Voice opinion article (2016)' + (' — as Minda' if key == 'gwer' else ''), 'TD': 'ThisDay (2021)'}[s] for s in second)} |")
    L += ["", "Area names confirmed by Daily Trust (2019), which cites the 2015 law. Vanguard (2016) names Jechira, Jemgbagh, Kwande, Sankera and Minda.",
          "", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Paramount ruler of the Tiv; seat at Gboko | Wikipedia; I am Benue | stated |",
          "| Title Begha u Tiv, 'Lion of the Tiv' | Wikipedia | attributed |",
          "| Pre-colonial rule by elders; Tor Agbande from the Jukun | I am Benue only | attributed |",
          "| Created 1946 by the colonial administration; background (1927, Audu Dan Afoda; war veterans) | Wikipedia; I am Benue (veterans, Makir Zakpe) | 1946 stated; details attributed |",
          "| Makir Zakpe selected 19 September 1946 | Wikipedia (Tor Tiv; Makir Zakpe) | stated |",
          "| The five Tor Tivs and their years | Wikipedia; I am Benue | stated; Ayatse 2016/2017 both given |",
          "| Rotation between Ipusu and Ichongo | Wikipedia (citing the state law); Daily Trust (for the new stools) | attributed |",
          "| Ayatse's Kwande argument | Vanguard interview | attributed |",
          "| Ijirtamen; council functions; chairman of the Benue council of chiefs | Wikipedia | attributed |",
          "| Six first-class chiefs (Tor), second-class chiefs (Ter) | I am Benue; Daily Trust (first class) | attributed |",
          "| 2015 law, staffs of office 14 May 2018 | Daily Trust | attributed |",
          "| Five blocs with Minda vs six areas | Vanguard; Nigerian Voice; ThisDay; I am Benue; Daily Trust | both views attributed; gap |",
          "", "## Links to the Tiv collection", "",
          "The five Tor Tiv pages in the Tiv collection (Makir Zakpe … James Ayatse) are linked to the new Tor Tiv record as 'held the title of', with years. Nothing in those Tiv records is changed.",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- After publication the Tor Tiv page becomes indexable, with original, sourced prose, and the sitemap should grow by 1 (2,986 → 2,987). The council and the six area pages are short, so they stay noindex and out of the sitemap. So do the new source pages.",
          "- The Tiv collection already has pages on each Tor Tiv. The new page is about the office itself, and it links to those pages instead of repeating them.",
          "- Political disputes (Minda, rotation) are reported neutrally and attributed. No side is taken.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_010_tor_tiv.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_010_tor_tiv_REVIEW.md", "w").write(report())
    print(f"Tor Tiv words={words(TT_DESCRIPTION) + words(TT_GOVERNANCE)} council={words(TTC_DESCRIPTION)} records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)}")
