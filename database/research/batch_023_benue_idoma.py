"""
Research batch 023 — Benue (Phase 3, first Benue batch): the Idoma local government areas,
the Och'Idoma, the Idoma Area Traditional Council and the Igede paramountcy (researched
2026-09-25). Closes gap G-01 (which LGAs are Idoma) and G-06 (Idoma institutions) of the
Benue sample (NIGERIA_STEP6_SAMPLE_BENUE.md).

New national records (polities): the Och'Idoma title, the Idoma Area Traditional Council and
the Ny'Igede title. Filled EMPTY fields of the Idoma and Igede ethnic-group records (history,
origins). Changes to EXISTING links (evidence upgrades, settlement status, notes) are NOT made
here; they are in database/research/fix_023_benue_links.php, shown in the same review.

Evidence notes:
  * "Seven" or "nine" Idoma LGAs. I am Benue lists seven (Ado, Agatu, Apa, Ohimini, Ogbadibo,
    Okpokwu, Otukpo). Wikipedia lists nine, adding Obi and Oju, which the Benue State
    Government, Wikipedia (Igede people) and Igede voices describe as the Igede area. The nine
    together form Benue South (Zone C). Both counts are stated; the difference is explained,
    not resolved by us.
  * The Idoma Area Traditional Council, citing the 2016 gazette (Benue State Gazette No. 12,
    Vol. 41, 20 October 2016), counts Oju and Obi among its intermediate areas and in 2025
    declared void titles conferred by the Ny'Igede. The Benue State Government names a separate
    paramount ruler of the Igede, and the Ny'Igede was crowned a first-class chief in 2018.
    Igede voices (2017) reject being counted as Idoma. Recorded as a DISPUTE, both sides
    attributed, no side taken.
  * First Och'Idoma: 10 October 1947, formally installed 3 February 1949 (Idoma Voice timeline,
    compiled from colonial-era studies); Wikipedia says 1948 (unsourced there). Both given.
  * NOT used: Wikipedia's "1,307,647 Idoma" figure attributed to the 2006 census. Nigeria's
    census does not record ethnicity, so the figure cannot come from it.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "IAMID": dict(source_type="website", source_kind="community_organisation", source_tier=4,
                  title="Idoma People Of Benue State", organisation="I am Benue",
                  url="http://www.iambenue.com/benue-state/ethnic-composition/idoma-people-of-benue-state/",
                  verification_status="needs_corroboration",
                  notes="The Idoma are the second largest ethnic group in Benue State and occupy 7 LGAs in western Benue: Ado, Agatu, Apa, Ohimini, Ogbadibo, Okpokwu and Otukpo. Och'Idoma's residence: the Och'Idoma Palace in Otukpo; current ruler HRH John Elaigwu Odogbo, Och'Idoma V. Many Idoma groups trace their homeland to Apa; about 300 years ago the break-up of the Kwararafa kingdom led Idoma ancestors south; scholars argue the Idoma have no single origin."),
    "IAMB2": dict(source_type="website", title="Indigenous administrative structure and institutions", organisation="I am Benue",
                  url="http://www.iambenue.com/benue-state/benue-state/indigenous-administrative-structure-and-institutions/",
                  verification_status="needs_corroboration", notes="Reused (batches 006, 010)."),
    "COR": dict(source_type="website", title="Coronation Of Ten First Class Chiefs in Benue State; Tor Tiv and Och’Idoma Mere Spectators",
                organisation="I am Benue",
                url="http://www.iambenue.com/coronation-of-ten-first-class-chiefs-in-benue-state-tor-tiv-and-ochiidoma-mere-spectators/",
                verification_status="needs_corroboration", notes="Reused (batch 010)."),
    "BSG": dict(source_type="official_website", title="About Benue State", organisation="Benue State Government",
                url="https://benuestate.gov.ng/about/", verification_status="needs_corroboration", notes="Reused."),
    "BP25": dict(source_type="news", source_kind="news", source_tier=3,
                 title="Chieftaincy titles Conferred by Ny’Igede null and void, Idoma traditional council declares",
                 author="Grace Kanayo", organisation="Blueprint", publication_date="2025-09-07",
                 url="https://blueprint.ng/chieftaincy-titles-conferred-by-nyigede-null-and-void-idoma-traditional-council-declares/",
                 verification_status="needs_corroboration",
                 notes="Statement of the Idoma Area Traditional Council: its 2 September 2025 suspension of chieftaincy conferments covered the intermediate area councils of Apa and Agatu, Otukpo and Ohimini, Ado, Okpokwu and Ogbadibo, and Oju and Obi; titles conferred on 6 September 2025 by HRH CP Oga Ero (rtd), Adirahu Ny'Igede, declared void; cites Benue State Gazette No. 12, Vol. 41, 20 October 2016. No Igede response in the article."),
    "IV17": dict(source_type="news", source_kind="news", source_tier=3,
                 title="We are not Idoma; we have no historical or biological ties - Igede People", author="Treasure Orokpo",
                 organisation="Idoma Voice", publication_date="2017-04-06", url="https://idomavoice.com/we-are-not-idoma-we-have-no-historical/",
                 verification_status="needs_corroboration",
                 notes="Reports the Igede Youth Coalition (coordinator Andyson Iji Egbodo): Igede 'are not historically, biologically and culturally related' to the Idoma; trace their origin to present-day Edo State; occupy 'majorly two local governments in Benue state, Obi and Oju', with pockets in Konshisha and Gwer West; seek first-class status for the Adirahu Ny'Igede. Quotes the then Och'Idoma, HRM Agabaidu Elias Ikoyi Obekpa, calling the agitation a threat to unity."),
    "IV21": dict(source_type="news", source_kind="news", source_tier=3,
                 title="Timeline of important dates and events in Idoma colonial history", author="Sunny Green Itodo",
                 organisation="Idoma Voice", publication_date="2021-11-23",
                 url="https://idomavoice.com/timeline-of-important-dates-and-events-in-idoma-colonial-history/",
                 verification_status="needs_corroboration",
                 notes="Compiled timeline citing three works on Northern Nigeria, colonialism and Agaba'Idu Ogiri Okoh. 1923: Idoma Native Authority established; 1924: divisional headquarters moved from Okpoga to Otukpo; 1927: Governor approved the Idoma Central Native Council (Ojila); 1944: Idoma Loving Union (later Idoma Hope Rising Union); 10 October 1947: Ogiri Okoh became the first Och'Idoma; 3 February 1949: formally installed by the Acting Resident of Benue Province, D.F.H. McBride."),
    "TD22": dict(source_type="news", source_kind="news", source_tier=3,
                 title="Ortom Installs New Ochi’Idoma in Benue", author="George Okoh", organisation="ThisDay", publication_date="2022-07-01",
                 url="https://www.thisdaylive.com/index.php/2022/07/01/ortom-installs-new-ochiidoma-in-benue/",
                 verification_status="needs_corroboration",
                 notes="Governor Samuel Ortom crowned Agaba-Idu Elaigwu Odogbo Obagaji John at the Och'Idoma square, Otukpo; chosen by the selection committee as the 5th Och'Idoma."),
    "WIP": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Idoma people", organisation="Wikipedia",
                url="https://en.wikipedia.org/wiki/Idoma_people", verification_status="needs_corroboration",
                notes="Nine LGAs (Ado, Agatu, Apa, Obi, Ohimini, Ogbadibo, Oju, Okpokwu, Otukpo); Idomoid languages; tradition of Apa and Kwararafa; Och'Idoma created under British rule, first elected 1948 (marked citation needed); head of the Idoma Area Traditional Council; 5th Och'Idoma installed 30 June 2022. Its 2006-census figure for the Idoma is NOT used (the census did not record ethnicity)."),
}

OCH_DESCRIPTION = """The Och'Idoma is the paramount traditional ruler of the Idoma people of Benue State. The Benue State Government names the Och'Idoma, together with the Tor Tiv of the Tiv and the Ochi'Igede of the Igede, among the foremost traditional institutions of the state. The Och'Idoma's palace is at Otukpo, and he heads the Idoma Area Traditional Council.

The office grew out of British colonial administration. A timeline published by Idoma Voice in 2021, compiled from studies of the colonial period, gives these steps: an Idoma Native Authority was set up in 1923; in 1924 the divisional headquarters moved from Okpoga to Otukpo; and in 1927 the colonial government approved an Idoma Central Native Council. On 10 October 1947 Agaba'Idu Ogiri Oko became the first Och'Idoma, and on 3 February 1949 he was formally installed by the Acting Resident of Benue Province. Wikipedia, which also describes the office as a creation of British rule, dates the first election to 1948.

The present holder, Agaba-Idu Elaigwu Odogbo Obagaji John, is the fifth Och'Idoma. I am Benue, a Benue community website, dates his election to 30 December 2021. ThisDay reported in July 2022 that Governor Samuel Ortom had crowned him at the Och'Idoma square in Otukpo, after his choice by a selection committee. In 2017 Idoma Voice quoted Agabaidu Elias Ikoyi Obekpa as the Och'Idoma of the time. The names and dates of the other holders are not yet recorded here."""

OCH_GOVERNANCE = """According to I am Benue, the Och'Idoma is the first-class chief of the Idoma kingdom and heads twenty-two districts and one hundred and forty-four clans, run by district heads and clan heads under his supervision. The same site says that, unlike stools reserved for royal families, the office is open to any Idoma man with a first degree who is of sound mind.

Under Benue State's chieftaincy law of 2016, first-class chiefs also head intermediate areas. At the coronation of ten first-class chiefs on 15 May 2018, I am Benue lists four in the Idoma and Igede areas: the Ochi' Otukpo/Ohimini, the Ochi' Enone, the Ochi' Apa/Agatu and the Adirahu Ny'Igede. Whether the Igede ruler falls under the Och'Idoma is disputed; see the Idoma Area Traditional Council."""

IATC_DESCRIPTION = """The Idoma Area Traditional Council is the council of Idoma traditional rulers in Benue State. It sits at Otukpo, and the Och'Idoma heads it. I am Benue describes the Och'Idoma as overseeing seven local government areas, twenty-two districts and one hundred and forty-four clans.

In a statement reported by Blueprint in September 2025, the council described its jurisdiction under Benue State Gazette No. 12, Volume 41, of 20 October 2016, the gazette of the state's 2016 chieftaincy law. It named five intermediate area councils: Apa and Agatu; Otukpo and Ohimini; Ado; Okpokwu and Ogbadibo; and Oju and Obi. On 2 September 2025 the council suspended all conferment of chieftaincy titles in these areas, and it later declared void titles conferred on 6 September 2025 by the Adirahu Ny'Igede, the paramount ruler of the Igede.

Whether Oju and Obi, the Igede area, fall under this council is disputed. The council counts them among its intermediate areas. The Benue State Government names the Igede as one of the state's three major peoples, with their own paramount ruler, and the Ny'Igede was crowned a first-class chief in 2018. In 2017 the Igede Youth Coalition, quoted by Idoma Voice, said that the Igede are not historically, biologically or culturally Idoma; the Och'Idoma of the time answered that the agitation threatened the unity of the kingdom. Both positions are recorded here; the archive takes no side."""

NY_DESCRIPTION = """The Ny'Igede, also styled Adirahu Ny'Igede, is the paramount traditional ruler of the Igede people of Oju and Obi local government areas in Benue State. The Benue State Government lists the paramount ruler of the Igede, whom it calls the Ochi'Igede, alongside the Tor Tiv and the Och'Idoma.

On 15 May 2018, I am Benue reports, CP Oga Ero (retired) was crowned Adirahu Ny'Igede as one of ten first-class chiefs of Benue State. In 2017 the Igede Youth Coalition had called for first-class status for the Igede ruler, separate from the Idoma. The relationship with the Idoma Area Traditional Council remains disputed: in September 2025 that council declared void chieftaincy titles conferred by the Ny'Igede and the Igede Traditional Council, citing the 2016 gazette. Another I am Benue page says instead that the Igede and Idoma share one first-class ruler, the Och'Idoma. The accounts differ; both are recorded."""

IDOMA_ORIGINS = """Idoma accounts of origin are traditions, and scholars read them in different ways. I am Benue and Wikipedia both report that many Idoma groups trace their ancestral homeland to Apa, and that the break-up of the Kwararafa (Okolofa) confederacy, about three hundred years ago, drove Idoma ancestors southwards. Wikipedia also records a tradition of Iduh as the father of the Idoma. I am Benue notes that scholars argue the Idoma have no single origin but formed from diverse groups, with Igala and Igbo elements among them."""

IDOMA_HISTORY = """Under British rule, according to a 2021 Idoma Voice timeline, an Idoma Native Authority was set up in 1923, its headquarters moved from Okpoga to Otukpo in 1924, and an Idoma Central Native Council was approved in 1927. A single paramount ruler, the Och'Idoma, followed: Ogiri Oko became the first in 1947 and was formally installed in 1949. The Och'Idoma now heads the Idoma Area Traditional Council at Otukpo.

Sources differ on how many local government areas are Idoma. I am Benue lists seven: Ado, Agatu, Apa, Ohimini, Ogbadibo, Okpokwu and Otukpo. Wikipedia lists nine, adding Obi and Oju, which the Benue State Government and Igede voices describe as the Igede area; the nine together make up Benue South. Whether the Igede come under the Idoma traditional council is disputed."""

IGEDE_ORIGINS = """In 2017 the Igede Youth Coalition, quoted by Idoma Voice, said that the Igede trace their origin to present-day Edo State, whereas the Idoma are traced to the Kwararafa dynasty. This is the coalition's statement; no scholarly account of Igede origins is recorded here yet."""

IGEDE_HISTORY = """The Igede have their own paramount ruler, the Ny'Igede (Adirahu Ny'Igede), whom the Benue State Government lists with the Tor Tiv and the Och'Idoma. The Igede ruler was crowned a first-class chief on 15 May 2018. The Igede Youth Coalition said in 2017 that the Igede are not Idoma and occupy mainly Obi and Oju, with pockets in Konshisha and Gwer West. The Idoma Area Traditional Council counts Oju and Obi among its intermediate areas under the 2016 gazette and in 2025 declared void titles conferred by the Ny'Igede. The question is disputed; both positions are recorded."""

RECORDS = [
    dict(key="och", table="polities", evidence="multiple_sources", level="well_documented",
         fields=dict(polity_type="traditional_title", name="Och'Idoma", slug="och-idoma", is_extant=1,
                     founded_year=1947, founded_text="10 October 1947 (first Och'Idoma; formally installed 3 February 1949)", founded_precision="exact",
                     summary="The Och'Idoma is the paramount traditional ruler of the Idoma people of Benue State, with his palace at Otukpo. The office dates from British rule; the first Och'Idoma took office in 1947.",
                     description=OCH_DESCRIPTION, governance=OCH_GOVERNANCE),
         srcs=[("BSG", "The Och'Idoma among the state's foremost traditional institutions"),
               ("IAMID", "Palace at Otukpo; Och'Idoma V, John Elaigwu Odogbo"),
               ("IAMB2", "First-class chief; 22 districts, 144 clans; 7 LGAs; election 30 Dec 2021; eligibility"),
               ("IV21", "Native Authority 1923; Otukpo 1924; central council 1927; first Och'Idoma 1947, installed 1949"),
               ("WIP", "Created under British rule; first elected 1948 (per Wikipedia); head of the council"),
               ("TD22", "Crowned by Governor Ortom at Otukpo; 5th Och'Idoma; selection committee"),
               ("IV17", "Elias Ikoyi Obekpa as Och'Idoma in 2017"),
               ("COR", "First-class chiefs in Idoma and Igede areas crowned 15 May 2018")]),
    dict(key="iatc", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="traditional_council", name="Idoma Area Traditional Council", slug="idoma-area-traditional-council", is_extant=1,
                     summary="The Idoma Area Traditional Council is the council of Idoma traditional rulers in Benue State, sitting at Otukpo under the Och'Idoma. Its authority over the Igede area (Oju and Obi) is disputed.",
                     description=IATC_DESCRIPTION),
         srcs=[("IAMB2", "Och'Idoma oversees 7 LGAs, 22 districts, 144 clans"), ("IAMID", "Och'Idoma palace at Otukpo"),
               ("BP25", "Council's statement: five intermediate area councils; 2016 gazette; 2025 suspension; titles by the Ny'Igede declared void"),
               ("WIP", "The Och'Idoma heads the Idoma Area Traditional Council"),
               ("BSG", "Igede as a major people with their own paramount ruler"),
               ("IV17", "Igede Youth Coalition's position; Och'Idoma's reply (2017)"),
               ("COR", "Adirahu Ny'Igede crowned first-class chief, 15 May 2018")]),
    dict(key="ny", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="traditional_title", name="Ny'Igede", slug="ny-igede", is_extant=1,
                     summary="The Ny'Igede (Adirahu Ny'Igede) is the paramount traditional ruler of the Igede people of Oju and Obi, Benue State, crowned a first-class chief in 2018. Its relation to the Idoma council is disputed.",
                     description=NY_DESCRIPTION),
         srcs=[("BSG", "Ochi'Igede, paramount ruler of the Igede"),
               ("COR", "CP Oga Ero (rtd) crowned Adirahu Ny'Igede, a first-class chief, 15 May 2018"),
               ("IV17", "2017 call for first-class status separate from the Idoma"),
               ("BP25", "2025: Idoma council declared titles conferred by the Ny'Igede void"),
               ("IAMB2", "One first-class ruler (the Och'Idoma) for Idoma and Igede — differing account")]),
]

UPDATES = [
    dict(ref="@ethnic_groups:idoma", fields=dict(origins_and_migration=IDOMA_ORIGINS, history=IDOMA_HISTORY),
         srcs=[("IAMID", "Apa homeland; Kwararafa; no single origin; seven LGAs"), ("WIP", "Apa; Kwararafa (Okolofa); Iduh; nine LGAs"),
               ("IV21", "Native Authority 1923; Otukpo 1924; council 1927; first Och'Idoma 1947/1949"),
               ("BSG", "Igede area; three major peoples"), ("BP25", "Council's intermediate areas incl. Oju and Obi")]),
    dict(ref="@ethnic_groups:igede", fields=dict(origins_and_migration=IGEDE_ORIGINS, history=IGEDE_HISTORY),
         srcs=[("IV17", "Igede Youth Coalition: origin in Edo; not Idoma; Obi and Oju"), ("BSG", "Paramount ruler of the Igede"),
               ("COR", "Adirahu Ny'Igede crowned first-class chief, 15 May 2018"), ("BP25", "Council's 2025 statement")]),
]

NAMES = [
    dict(record="ny", name="Adirahu Ny'Igede", name_type="official", usage_notes="Full title as used in the 2018 coronation list and 2025 press reports.", srcs=["COR"]),
    dict(record="ny", name="Ochi'Igede", name_type="alternative", usage_notes="Form used by the Benue State Government.", srcs=["BSG"]),
    dict(record="och", name="Ochi'Idoma", name_type="spelling_variant", usage_notes="Spelling used by some newspapers (e.g. ThisDay 2022).", srcs=["TD22"]),
]

RELATIONS = [
    dict(frm="och", type="associated_with", to="iatc", role="head of the council", source="WIP", evidence="multiple_sources", level="reported",
         notes="The Och'Idoma heads the Idoma Area Traditional Council (Wikipedia; I am Benue; Blueprint 2025)."),
    dict(frm="och", type="associated_with", to="@ethnic_groups:idoma", role="paramount ruler", source="BSG", evidence="multiple_sources", level="well_documented",
         notes="Paramount ruler of the Idoma (Benue State Government; I am Benue; Wikipedia)."),
    dict(frm="ny", type="associated_with", to="@ethnic_groups:igede", role="paramount ruler", source="BSG", evidence="multiple_sources", level="well_documented",
         notes="Paramount ruler of the Igede (Benue State Government, as 'Ochi'Igede'; I am Benue 2018 coronation list)."),
    dict(frm="ny", type="disputed_relationship", to="iatc", source="BP25", evidence="disputed", level="disputed",
         notes="The Idoma Area Traditional Council claims authority over Oju and Obi and voided the Ny'Igede's 2025 conferments (Blueprint 2025); the Ny'Igede is a first-class chief since 2018 (I am Benue) and Igede voices reject being counted as Idoma (Idoma Voice 2017)."),
    dict(frm="iatc", type="located_in", to="@admin_units:lga:benue/oturkpo", source="IAMID", evidence="multiple_sources", level="reported",
         notes="Seat at Otukpo, where the Och'Idoma's palace is (I am Benue; Blueprint 2025)."),
]
# The seven LGAs I am Benue lists as Idoma, each also named in the council's own list of intermediate areas.
IDOMA_LGAS = ["ado", "agatu", "apa", "ohimini", "ogbadibo", "okpokwu", "oturkpo"]
for l in IDOMA_LGAS:
    RELATIONS.append(dict(frm=f"@admin_units:lga:benue/{l}", type="part_of", to="iatc", source="BP25", evidence="multiple_sources", level="reported",
                          notes="Within the council's intermediate areas as it listed them in 2025 (Blueprint); one of the seven Idoma LGAs listed by I am Benue."))
for l in ["oju", "obi"]:
    RELATIONS.append(dict(frm=f"@admin_units:lga:benue/{l}", type="disputed_relationship", to="iatc", source="BP25", evidence="disputed", level="disputed",
                          notes="Claimed by the Idoma Area Traditional Council as the Oju and Obi intermediate area (Blueprint 2025); the Igede area, with its own paramount ruler (Benue State Government) — disputed."))

GAPS = [
    ("Text of the 2016 gazette (Idoma and Igede councils)", "The Idoma council cites Benue State Gazette No. 12, Vol. 41 of 20 October 2016. Which councils it creates, and whether the Igede council is under the Idoma council, need the gazetted text (also gap G-09)."),
    ("Idoma intermediate areas and their first-class chiefs", "The council named five intermediate areas in 2025; the 2018 coronation list names the Ochi' Otukpo/Ohimini, Ochi' Enone and Ochi' Apa/Agatu. Which area 'Enone' covers, and the chief of Okpokwu/Ogbadibo, are not established. Not recorded as records."),
    ("List of Och'Idomas", "Only the first (Ogiri Oko, 1947) and fifth (Elaigwu Odogbo, 2021/2022) holders, and Elias Ikoyi Obekpa as holder in 2017, are sourced. The full list with reigns needs a reliable source."),
    ("First Och'Idoma: 1947, 1948 or 1949", "Idoma Voice gives 10 October 1947 (took office) and 3 February 1949 (installation); Wikipedia 1948 without a source. Needs a colonial record or a scholarly work."),
    ("Idoma Native Authority date", "Idoma Voice gives 1923; other summaries give 1927 (the year the central council was approved). Needs the colonial sources."),
    ("Igede history from scholarly sources", "Igede origins are recorded only from a 2017 youth-coalition statement. Needs ethnographic or historical works."),
    ("Idoma population", "No reliable Idoma population figure: Wikipedia's '2006 census' figure is not used because the census did not record ethnicity."),
    ("Twenty-two districts and 144 clans", "Given only by I am Benue. The names of the districts (e.g. Adoka, Otukpa, Agatu) are not recorded."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Benue (Phase 3): the Idoma LGAs, the Och'Idoma, the Idoma Area Traditional Council and the Igede paramountcy; Idoma and Igede origins and history (empty fields only).")


def quote(t):
    return [f"> {p}" if p else ">" for p in t.split("\n")]


def report():
    L = ["# Research batch 023 — Benue: the Idoma LGAs, the Och'Idoma and the Idoma Area Traditional Council", "",
         f"Researched {ACCESSED}. Phase 3 (state-by-state research), first Benue batch; closes gaps G-01 and G-06 of the Benue sample.",
         "New records are created **in review** and are published only after your approval.", "",
         f"- Och'Idoma: **{words(OCH_DESCRIPTION) + words(OCH_GOVERNANCE)} words** (passes the 300-word rule, so it will be indexable once published).",
         f"- Idoma Area Traditional Council: {words(IATC_DESCRIPTION)} words (under 300, so noindex until more is researched).",
         f"- Ny'Igede: {words(NY_DESCRIPTION)} words (short, so noindex).",
         f"- Idoma record: fills its empty *Origins & migration* ({words(IDOMA_ORIGINS)} words) and *History* ({words(IDOMA_HISTORY)} words). Igede record: fills *Origins* ({words(IGEDE_ORIGINS)}) and *History* ({words(IGEDE_HISTORY)}). Nothing already written is changed.",
         f"- {len(RELATIONS)} new links: the Och'Idoma → the council and the Idoma people; the Ny'Igede → the Igede people and (disputed) → the council; the council → Otukpo; the seven Idoma LGAs → the council; Oju and Obi → the council marked **disputed**.",
         "- Changes to existing links are in a separate fix script (below), so they can be approved separately.", "",
         "## The main finding: seven or nine Idoma LGAs", "",
         "| Source | Count | LGAs |", "|---|---|---|",
         "| I am Benue (Idoma People of Benue State) | 7 | Ado, Agatu, Apa, Ohimini, Ogbadibo, Okpokwu, Otukpo |",
         "| Idoma Area Traditional Council (Blueprint, 2025) | 5 intermediate areas = 9 LGAs | Apa & Agatu; Otukpo & Ohimini; Ado; Okpokwu & Ogbadibo; **Oju & Obi** |",
         "| Wikipedia (Idoma people) | 9 | the seven + **Obi, Oju** |",
         "| Benue State Government | — | Idoma mainly in the south; Igede in the east, with their own paramount ruler |",
         "| Igede Youth Coalition (Idoma Voice, 2017) | — | Igede occupy mainly **Obi and Oju**; 'not Idoma' |", "",
         "So the seven are Idoma by every source. Oju and Obi are the Igede area. The Idoma council claims them under the 2016 gazette, while the state government and Igede voices treat the Igede as a separate people with their own paramount ruler. The archive records this as a **dispute**, with both sides attributed.", "",
         "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}{', ' + s['author'] if s.get('author') else ''}{', ' + s['publication_date'] if s.get('publication_date') else ''}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    L += ["", "## Och'Idoma — Overview", ""] + quote(OCH_DESCRIPTION) + ["", "## Och'Idoma — Governance", ""] + quote(OCH_GOVERNANCE)
    L += ["", "## Idoma Area Traditional Council — Overview", ""] + quote(IATC_DESCRIPTION)
    L += ["", "## Ny'Igede — Overview", ""] + quote(NY_DESCRIPTION)
    L += ["", "## Idoma record — Origins & migration (fills an empty field)", ""] + quote(IDOMA_ORIGINS)
    L += ["", "## Idoma record — History (fills an empty field)", ""] + quote(IDOMA_HISTORY)
    L += ["", "## Igede record — Origins & migration (fills an empty field)", ""] + quote(IGEDE_ORIGINS)
    L += ["", "## Igede record — History (fills an empty field)", ""] + quote(IGEDE_HISTORY)
    L += ["", "## How each statement is supported", "", "| Statement | Sources | Handling |", "|---|---|---|",
          "| Och'Idoma is the Idoma paramount ruler; palace at Otukpo | Benue State Government; I am Benue; Wikipedia | stated |",
          "| Native Authority 1923; Otukpo 1924; central council 1927 | Idoma Voice timeline (2021) | attributed |",
          "| First Och'Idoma 10 Oct 1947, installed 3 Feb 1949 | Idoma Voice timeline; Wikipedia says 1948 | both attributed |",
          "| Elaigwu Odogbo 5th Och'Idoma; crowned by Governor Ortom at Otukpo | ThisDay (2022); I am Benue; Wikipedia | stated |",
          "| Elected 30 December 2021 | I am Benue | attributed |",
          "| 22 districts, 144 clans; eligibility (first degree) | I am Benue | attributed |",
          "| Council's five intermediate areas; 2016 gazette; 2025 suspension and voided titles | Council statement reported by Blueprint (2025) | attributed |",
          "| Ny'Igede first-class chief, 15 May 2018 | I am Benue (coronation list) | attributed |",
          "| Igede not Idoma; origin in Edo; mainly Obi and Oju | Igede Youth Coalition via Idoma Voice (2017) | attributed as their statement |",
          "| One first-class ruler for Idoma and Igede | I am Benue (another page) | attributed; contradicted by the 2018 list; both kept |",
          "| Apa homeland, Kwararafa, ~300 years ago; no single origin | I am Benue; Wikipedia | stated as tradition; scholarly view attributed |",
          "| Idoma '2006 census' population | Wikipedia | **not used** (census has no ethnicity) |",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- After publication the Och'Idoma page passes the 300-word rule and becomes indexable (sitemap +1). The council and Ny'Igede pages are short, so they stay noindex. The Idoma page grows from about 176 to about 390 words and becomes indexable. The Kingdoms & Traditional Institutions listing then links to three indexable pages and becomes indexable under the existing rule. **Sitemap +3 (3,018 → 3,021)**, confirmed on a copy. The Igede page grows to about 230 words and stays noindex.",
          "- The dispute is written neutrally: both positions are attributed to who holds them, and no side is taken.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_023_benue_idoma.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_023_benue_idoma_REVIEW.md", "w").write(report())
    print(f"och={words(OCH_DESCRIPTION) + words(OCH_GOVERNANCE)} iatc={words(IATC_DESCRIPTION)} ny={words(NY_DESCRIPTION)} "
          f"idoma+={words(IDOMA_ORIGINS) + words(IDOMA_HISTORY)} igede+={words(IGEDE_ORIGINS) + words(IGEDE_HISTORY)} records={len(RECORDS)} relations={len(RELATIONS)}")
