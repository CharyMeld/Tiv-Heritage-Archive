"""
Research batch 144 — Ondo (Phase 3): traditional institutions. Researched 2026-10-07. Pattern: batch 131 (Osun).

  * Akure (the Deji): Wikipedia 'Akure Kingdom' — successor of an ancient Yoruba city-state; founded in tradition by
    Omoremilekun (Asodeboyede), a descendant of Oduduwa; the palace dates to 1150 AD (Wikipedia); one of the 16 or so
    Ekiti kingdoms, influenced by Benin; the Ado-Akure community of partial Bini descent; the title Deji from Oba Ogunja
    (r. 1533–1554); six high chiefs (iwarefa). 'Aladetoyinbo Ogunlade Aladelusi': Odundun II, the 47th Deji, selected 17
    June 2015 from the Osupa family. Peoples Gazette (28 Aug and 14 Sep 2025): still Deji (Amole festival, Ulefunta
    leave). THEWILL (15 Jan 2023): his dispute over hierarchy with the Iralepo of Isinkan; courts gave the Deji authority
    over lesser chiefs, which the Iralepo has not accepted.
  * Owo (the Olowo): Wikipedia 'Olowo of Owo' — first Olowo Ojugbelu Arere, by tradition a descendant of Oduduwa
    (1019–1070 in Wikipedia's list); kingmakers and the Iloro chiefs; Ajibade Gbadegesin Ogunoye III from July 2019.
    'Ajibade Gbadegesin Ogunoye III': crowned and given the staff of office on 14 December 2019. ThisDay (19 Jul 2026):
    the 32nd paramount ruler, 60 on 6 July 2026, chairs the Ondo State Council of Obas. 'Igogo festival': held each
    September in honour of Queen Oronsen; the Olowo and high chiefs dress as women.
  * Ondo (the Osemawe): Wikipedia 'Ondo Kingdom' — founded by Pupupu (traditions differ on her parentage); her son Airo
    (Aiho) built the political structure; the Osemawe is chosen from the royal houses descended from Airo's sons; six
    high chiefs (Lisa, Jomo, Odunwo, Sasere, Adaja, Odofin); 500th anniversary in 2010; wars of 1845–1872; treaty with
    Lagos Colony 20 Feb 1889. 'Adesimbo Victor Kiladejo': Jilo III, the 44th Osemawe, appointed 1 Dec 2006, crowned 29
    Dec 2008, of the Okuta ruling house. ThisDay (7 Dec 2025): to preside over Ekimogun Day, 27 Dec 2025.
  * Ugbo (the Olugbo): Wikipedia 'Ugbo Kingdom' — Ilaje kingdom of Ilaje LGA, capital Ode Ugbo, sixteen quarters,
    fishing and salt-making. 'Fredrick Obateru Akinruntan': Olugbo since 2009 'after a succession dispute and lawsuit';
    some of his statements on Yoruba history are disputed. ThisDay (16 Mar 2025, date from the URL): returned from the UK
    after speculation about his health.
  * Idoani (the Alani): Wikipedia 'Idoani Confederacy' — formed 1880 by six communities during the Yoruba wars; merged
    1921; a succession dispute from the 1970s; Oba Aderemi Atewogboye recognised in 1999, died 2010; regency 2010–2016;
    Oba Olufemi Olutoye crowned after it. FUTA news (2018): Oba (Maj. Gen., rtd) Olufemi Olutoye, Alani of Ido-Ani.
Placement: the seat's LGA — Akure → Akure South, Owo → Owo, Ondo → Ondo West, Ode Ugbo → Ilaje, Idoani → Ose.
"""
import json, re, sys
import batch_142_ondo_languages_peoples as P142

ACCESSED = "2026-10-07"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
NEWS = lambda t, org, d, u, n: dict(source_type="news", source_kind="news", source_tier=3, title=t, organisation=org, publication_date=d, url=u,
                                    verification_status="needs_corroboration", notes=n)
SOURCES = {
    "WAKK": WS("Akure Kingdom", "Successor of an ancient Yoruba city-state; founder Omoremilekun (Asodeboyede); palace dated to 1150 AD; one of the 16 or so Ekiti kingdoms; Benin influence and the Ado-Akure; Deji title from Oba Ogunja (r. 1533–1554); six high chiefs; succession dispute 1999–2005."),
    "WDEJI": WS("Aladetoyinbo Ogunlade Aladelusi", "Odundun II, the 47th Deji of Akure; selected 17 June 2015 from the Osupa royal family; staff of office 17 July 2015."),
    "PG25A": NEWS("Amole Festival: Deji of Akure orders closure of markets, shops", "Peoples Gazette", "2025-08-28", "https://gazettengr.com/amole-festival-deji-of-akure-orders-closure-of-markets-shops/",
                  "Names Oba Aladetoyinbo Aladelusi as Deji of Akure (August 2025)."),
    "PG25B": NEWS("Deji of Akure begins seven-day 'Ulefunta' leave", "Peoples Gazette", "2025-09-14", "https://gazettengr.com/deji-of-akure-begins-seven-day-ulefunta-leave/",
                  "The Deji's annual Ulefunta leave; drumming prohibited in Akure during it (September 2025)."),
    "TW23": NEWS("Supremacy Battle Between Obas Aladetoyinbo, Ajimokunola Deepens", "THEWILL", "2023-01-15", "https://thewillnews.com/supremacy-battle-between-obas-aladetoyinbo-ajimokunola-deepens",
                 "The dispute over traditional hierarchy between the Deji of Akure and the Iralepo of Isinkan, a former quarter chief upgraded to a traditional ruler; 'The Supreme Court and Appeal court gave Deji of Akure the sole authority over lesser chiefs and Oba's but Ajimokunola has refused to submit to his authority.'"),
    "WOLOWO": WS("Olowo of Owo", "First Olowo Ojugbelu Arere (1019–1070 in Wikipedia's list), by tradition a descendant of Oduduwa; kingmakers (Omolowo) and the Iloro chiefs; list of Olowos to Ajibade Gbadegesin Ogunoye III (July 2019–)."),
    "WOGUN": WS("Ajibade Gbadegesin Ogunoye III", "Crowned and given the staff of office on 14 December 2019; born 6 July 1966; son of Oba Adekola Ogunoye II."),
    "TD26": NEWS("A Diamond Reign: Oba Ajibade Ogunoye at 60", "ThisDay", "2026-07-19", "https://www.thisdaylive.com/2026/07/19/a-diamond-reign-oba-ajibade-ogunoye-at-60/",
                 "'the 32nd paramount ruler of the Owo Kingdom'; 60 on 6 July 2026; 'he chairs the Ondo State Council of Obas'."),
    "WIGOGO": WS("Igogo festival", "Held annually in September in Owo to honour Queen Oronsen; the Olowo and high chiefs dress as women; origins over 600 years ago under Olowo Rerengejen."),
    "WONDK": WS("Ondo Kingdom", "Founded by Pupupu (traditions differ on her parentage); Airo (Aiho) built the political structure; royal houses descended from his sons; six high chiefs; 500th anniversary in 2010; conflict 1845–1872; agreement with the Lagos Colony, 20 February 1889."),
    "WKILA": WS("Adesimbo Victor Kiladejo", "Jilo III, the 44th Osemawe; appointed 1 December 2006, crowned 29 December 2008; of the Okuta ruling house."),
    "TD25": NEWS("Ekimogun Day 2025: Ondo Kingdom Set to Host Royal Fathers, Top Entertainers", "ThisDay", "2025-12-07", "https://www.thisdaylive.com/2025/12/07/ekimogun-day-2025-ondo-kingdom-set-to-host-royal-fathers-top-entertainers/",
                 "'The Osemawe and Paramount Ruler of Ondo Kingdom, Oba Dr. Victor Adesimbo Kiladejo, Jilo III' to preside over the finale on 27 December 2025."),
    "WUGBO": WS("Ugbo Kingdom", "A Yoruba (Ilaje) kingdom of Ilaje LGA, capital Ode Ugbo; sixteen quarters under chiefs; the Olugbo; fishing and salt-making."),
    "WAKIN": WS("Fredrick Obateru Akinruntan", "Olugbo of Ugbo since 2009 'after a succession dispute and lawsuit'; 'He has made statements about Yoruba history that have been disputed by others.'"),
    "TD25B": NEWS("Oba Obateru Akinruntan Shames Mongers as He Arrives Nigeria", "ThisDay", "2025-03-16", "https://thisdaylive.com/index.php/2025/03/16/oba-obateru-akinruntan-shames-mongers-as-he-arrives-nigeria",
                  "The Olugbo of Ugbo returned from the UK after weeks of speculation about his health. Date taken from the URL."),
    "WIDCF": WS("Idoani Confederacy", "Formed in 1880 by six communities (Ido, Amusigbo, Isure, Iyayu, Isewa, Ako) during the Yoruba wars; merged 1921; disputed succession from the early 1970s; Oba Aderemi Atewogboye recognised 1999, died 2010; regency 2010–2016; Oba Olufemi Olutoye crowned after it."),
    "FUTA18": NEWS("Ido-Ani monarch commends FUTA's contributions to educational development", "Federal University of Technology, Akure", "2018", "https://futa.edu.ng/home/newsd/346",
                   "Names Oba (Maj. Gen., rtd) Dr Olufemi Olutoye as the traditional ruler of Ido-Ani (2018)."),
    "WIDOANI": dict(P142.SOURCES["WIDOANI"], notes="Reused. Idoani, in Ose LGA; the Idoani Confederacy; six historic quarters."),
}
TEXT = {
 "akure": """The Akure Kingdom is the traditional state of Akure, the capital of Ondo State, and its ruler is the Deji of Akure. According to Wikipedia, it succeeds an ancient Yoruba city-state, which tradition says was united under a new dynasty by Omoremilekun, called Asodeboyede, a descendant of Oduduwa, whose palace dates to 1150 AD; it came to be counted among the sixteen or so Ekiti kingdoms. Akure was a trading link between Benin and Ife and at times came under Benin, and the Ado-Akure community traces partial descent from Benin warriors and traders. The title Deji dates from Oba Ogunja in the sixteenth century; the Deji is assisted by six high chiefs. Oba Aladetoyinbo Ogunlade Aladelusi, Odundun II, of the Osupa royal family, was selected as the 47th Deji on 17 June 2015 (Wikipedia) and was still reigning in September 2025 (Peoples Gazette). His authority over some other rulers in the area has been disputed: in January 2023 THEWILL reported a long dispute with the Iralepo of Isinkan, who refused to accept court rulings giving the Deji authority over lesser chiefs.""",
 "owo": """Owo is a Yoruba kingdom of Ondo State, and its ruler is the Olowo of Owo. According to Wikipedia, the first Olowo, Ojugbelu Arere, was by tradition a descendant of Oduduwa, and between 1400 and 1600 Owo was the capital of a Yoruba city-state that kept virtual independence from Benin while exchanging courtly arts with it. The Olowo is chosen by kingmakers, and the Iloro chiefs play a central part in his installation. Oba Ajibade Gbadegesin Ogunoye III was crowned on 14 December 2019 (Wikipedia); in July 2026 ThisDay described him as the 32nd paramount ruler of the kingdom and the chairman of the Ondo State Council of Obas. Each September Owo holds the Igogo festival in honour of Queen Oronsen, when the Olowo and his high chiefs dress as women (Wikipedia).""",
 "ondo": """The Ondo Kingdom is a Yoruba traditional state with its capital at Ondo, and its ruler is the Osemawe. According to Wikipedia, its tradition names Pupupu, mother of twins, as its founder, although accounts differ on whose wife she was, and her son Airo built its political structure; the Osemawe is chosen from royal houses descended from his sons and rules with six high chiefs, the Lisa, Jomo, Odunwo, Sasere, Adaja and Odofin. Between 1845 and 1872 the kingdom went through a period of wars and changes of capital, and on 20 February 1889 the Osemawe made an agreement on free trade with the British Lagos Colony. Oba Adesimbo Victor Kiladejo, Jilo III, of the Okuta ruling house, was appointed the 44th Osemawe on 1 December 2006 and crowned on 29 December 2008; the kingdom marked its 500th anniversary in 2010 (Wikipedia). He was still Osemawe in December 2025 (ThisDay).""",
 "ugbo": """The Ugbo Kingdom is a Yoruba kingdom of the Ilaje people on the Atlantic coast of Ondo State, in Ilaje LGA, with its capital at Ode Ugbo; its ruler is the Olugbo of Ugbo. According to Wikipedia, it is made up of sixteen quarters headed by chiefs, and its people are mainly fishermen and salt-makers, with trade along the creeks and lagoons as far as Lagos and Warri. Oba Fredrick Obateru Akinruntan became Olugbo in 2009 after a succession dispute and lawsuit, and some of his statements on Yoruba history have been disputed by others (Wikipedia). In March 2025 he returned from the United Kingdom after weeks of speculation about his health (ThisDay).""",
 "idoani": """The Idoani Confederacy is the traditional state of Idoani, in Ose LGA of Ondo State, and its ruler is the Alani. According to Wikipedia, it was formed in 1880, during the Yoruba wars, by six communities near the Ose river, Ido, Amusigbo, Isure, Iyayu, Isewa and Ako, for protection against raiding warlords; they were merged into one town in 1921. A dispute over the succession began in the early 1970s and left a regent in office for fourteen years and, for a time, two rival claimants. Oba Aderemi Atewogboye was recognised in 1999 and died in 2010; after a regency Oba Olufemi Olutoye was crowned (Wikipedia), and a university news item names him as Alani in 2018 (FUTA).""",
}
REC = [
    ("akure", "Akure Kingdom", "akure-kingdom", ["WAKK", "WDEJI", "PG25A", "PG25B", "TW23"], "well_documented", dict(polity_type="kingdom", is_extant=1)),
    ("owo", "Owo Kingdom", "owo-kingdom", ["WOLOWO", "WOGUN", "TD26", "WIGOGO"], "well_documented", dict(polity_type="kingdom", is_extant=1)),
    ("ondo", "Ondo Kingdom", "ondo-kingdom", ["WONDK", "WKILA", "TD25"], "well_documented", dict(polity_type="kingdom", is_extant=1, founded_year=1510, founded_text="500th anniversary of the Osemawe dynasty celebrated in 2010 (Wikipedia)", founded_precision="circa")),
    ("ugbo", "Ugbo Kingdom", "ugbo-kingdom", ["WUGBO", "WAKIN", "TD25B"], "well_documented", dict(polity_type="kingdom", is_extant=1)),
    ("idoani", "Idoani Confederacy", "idoani-confederacy", ["WIDCF", "FUTA18", "WIDOANI"], "reported", dict(polity_type="confederacy", is_extant=1, founded_year=1880, founded_text="Formed in 1880 (Wikipedia)", founded_precision="year")),
]
RECORDS = []
for key, name, slug, srcs, lvl, extra in REC:
    t = TEXT[key]
    f = dict(name=name, slug=slug, summary=t.split(". ")[0] + ".", description=t)
    f.update(extra)
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources", level=lvl, fields=f, srcs=[(s, name) for s in srcs]))
LG = lambda l: f"@admin_units:lga:ondo/{l}"
SEAT = "Seat (not a statement of full jurisdiction)."
RELATIONS = [
    dict(frm="akure", type="located_in", to=LG("akure-south"), source="WAKK", evidence="multiple_sources", level="well_documented", notes=f"Akure, headquarters of Akure South (Wikipedia, Akure South). {SEAT}"),
    dict(frm="owo", type="located_in", to=LG("owo"), source="WOLOWO", evidence="multiple_sources", level="well_documented", notes=f"Owo, a city and LGA (Wikipedia, Owo). {SEAT}"),
    dict(frm="ondo", type="located_in", to=LG("ondo-west"), source="WONDK", evidence="multiple_sources", level="well_documented", notes=f"Ondo town, headquarters of Ondo West (Wikipedia, Ondo West). {SEAT}"),
    dict(frm="ugbo", type="located_in", to=LG("ilaje"), source="WUGBO", evidence="multiple_sources", level="well_documented", notes=f"Wikipedia: 'in the Ilaje local government area', capital Ode Ugbo. {SEAT}"),
    dict(frm="idoani", type="located_in", to=LG("ose"), source="WIDCF", evidence="multiple_sources", level="well_documented", notes=f"Wikipedia: 'based in the town of Idoani in the Ose Local Government Area'. {SEAT}"),
    dict(frm="akure", type="associated_with", to="@ethnic_groups:yoruba", role="Yoruba kingdom, counted among the Ekiti kingdoms", source="WAKK", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Akure Kingdom)."),
    dict(frm="owo", type="associated_with", to="@ethnic_groups:yoruba", role="Yoruba kingdom of the Owo", source="WOLOWO", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Olowo of Owo; Owo)."),
    dict(frm="ondo", type="associated_with", to="@ethnic_groups:yoruba", role="Yoruba kingdom of the Ondo", source="WONDK", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Ondo Kingdom)."),
    dict(frm="ugbo", type="associated_with", to="@ethnic_groups:yoruba", role="kingdom of the Ilaje, a Yoruba sub-group", source="WUGBO", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Ugbo Kingdom; Ilaje)."),
    dict(frm="idoani", type="associated_with", to="@ethnic_groups:yoruba", role="confederacy of eastern Yoruba communities", source="WIDCF", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Idoani Confederacy): 'six eastern Yoruba communes'."),
    dict(frm="idoani", type="associated_with", to="@languages:iyayu", role="Iyayu, one of the six founding communities, speaks the Iyayu language", source="WIDOANI", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Idoani; Idoani Confederacy); Blench's Atlas (Iyayu: one quarter of Idoani)."),
    dict(frm="akure", type="associated_with", to="@polities:ife-kingdom", role="founding dynasty traced to Oduduwa of Ile-Ife; trading link between Benin and Ife", source="WAKK", evidence="single_reliable_source", level="reported", notes="Wikipedia (Akure Kingdom)."),
    dict(frm="akure", type="associated_with", to="@polities:ijesaland", role="the title Deji traced to a gift of the Owa Atakunmosa of Ijeshaland to his grandson", source="WAKK", evidence="single_reliable_source", level="reported", notes="Wikipedia (Akure Kingdom)."),
]
NAMES = [
    dict(record="akure", name="Deji of Akure", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WAKK"]),
    dict(record="owo", name="Olowo of Owo", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WOLOWO"]),
    dict(record="ondo", name="Osemawe of Ondo", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WONDK"]),
    dict(record="ugbo", name="Olugbo of Ugbo", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WUGBO"]),
    dict(record="idoani", name="Alani of Idoani", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WIDCF"]),
    dict(record="idoani", name="Ido-ani Confederation", name_type="alternative", usage_notes="Wikipedia: 'The Idoani Confederacy or Ido-ani confederation'.", srcs=["WIDCF"]),
]
GAPS = [
    ("Ondo: the Deji's paramountcy", "THEWILL (January 2023) reports court rulings giving the Deji authority over lesser chiefs, not accepted by the Iralepo of Isinkan; the rulings themselves and the present state of the dispute were not read."),
    ("Ondo: the Alani of Idoani", "Only a 2018 source names Oba Olufemi Olutoye; a 2025–26 confirmation is needed (record kept as reported)."),
    ("Ondo: Benin links", "Wikipedia describes Benin's influence on Akure and its exchanges with Owo; the archive has no Benin Kingdom record yet, so no link is made."),
    ("Ondo: other obaships", "The Owa-Ale of Idanre, the Jegun of Ile-Oluji, the Abodi of Ikale, the Amapetu of Mahin, the Olukare of Ikare, the Olubaka of Oka, the Zaki of Arigidi and the rulers of Ese Odo (Apoi, Arogbo) need sources."),
    ("Ondo: Ugbo's antiquity", "Claims about the age of the Olugbo's throne relative to Ife are disputed (Wikipedia notes his statements on Yoruba history are disputed); not recorded."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Ondo traditional institutions: Akure (Deji), Owo (Olowo), Ondo (Osemawe), Ugbo (Olugbo) and the Idoani Confederacy (Alani).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 144 — Ondo: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **5 polities**, each with its present ruler confirmed by a dated source:",
         "  - the **Akure Kingdom** (the Deji): Oba Aladetoyinbo Aladelusi, Odundun II, since 2015, confirmed September 2025. His dispute with the Iralepo of Isinkan over authority is stated neutrally, with its date.",
         "  - the **Owo Kingdom** (the Olowo): Oba Ajibade Gbadegesin Ogunoye III, crowned December 2019, confirmed July 2026. He chairs the Ondo State Council of Obas.",
         "  - the **Ondo Kingdom** (the Osemawe): Oba Victor Kiladejo, Jilo III, since 2006, confirmed December 2025",
         "  - the **Ugbo Kingdom** (the Olugbo, Ilaje): Oba Fredrick Obateru Akinruntan, since 2009, confirmed March 2025 (date from the article's URL)",
         "  - the **Idoani Confederacy** (the Alani): *reported*. The latest source naming Oba Olufemi Olutoye is from 2018.",
         "- **Links:** each polity is linked to its seat LGA and to the Yoruba. Akure is linked to Ife and Ijesaland through its traditions, and Idoani to the Iyayu language (from batch 142).",
         "", "## The records", ""]
    for key, name, slug, srcs, lvl, extra in REC:
        L += [f"### {name} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_144_ondo_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_144_ondo_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)} words={[words(TEXT[k]) for k in TEXT]}")
