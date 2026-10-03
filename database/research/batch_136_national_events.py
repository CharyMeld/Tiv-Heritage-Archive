"""
Research batch 136 — National: Events. Researched 2026-10-03 (owner: fill the empty People / Events / Periods sections of
/nigeria). Seventeen turning points of Nigerian history, 1804–2015, each linked to its period from batch 135 (import 135 first).

Same sources as batch 135 (texts saved in data/): the Library of Congress 'Nigeria: A Country Study' history chapters
(information as of December 1990) and Wikipedia ('History of Nigeria', 'First Nigerian Republic', 'Fourth Nigerian Republic',
'Nigerian Civil War'). Sources are reused by URL, so no new source rows are created except where noted.

National events are timeline_events rows with a national_slug, tagged to the 'nigeria' collection only (importer), so they
never appear in the Tiv timeline. Dates are given only to the precision a source read gives them.
"""
import json, re, sys
import batch_135_national_periods as P

ACCESSED = "2026-10-03"
S = P.SOURCES
SOURCES = {k: S[k] for k in ["LSOK", "LCOL", "LEXT", "LLUG", "LUNI", "LNAT", "LIND", "LCRI", "LWAR", "LGOW", "LMUR", "LSEC", "LMIL", "LOBA",
                             "WHIST", "WFIRST", "WFOURTH", "WWAR"]}
SOURCES["WOVO"] = P.WS("Ovonramwen", "Born Idugbowa; 1857 – 14 January 1914; 35th Oba of Benin; the 1892 treaty; Phillips's journey in January 1897 and the attack near Ugbine; exile to Calabar.")
SOURCES["LMURG"] = P.LOC(26, "The Regime of Murtala Muhammad", "Gowon deposed in a bloodless coup on 29 July 1975, the ninth anniversary of the July 1966 revolt; Murtala Muhammad chosen to succeed him.")

# key: (title, national_slug, event_date, year, precision, kind, location, period slug, sources, level,
#       {description, consequences, historical_significance})
EV = [
 ("jihad", "The Jihad of Usman dan Fodio Begins", "jihad-of-usman-dan-fodio", "1804", 1804, "year", "religious", "Gobir (Hausaland)", "sokoto-caliphate-period",
  ["LSOK"], "well_documented", dict(
  description="""In 1804 a jihad led by the Muslim scholar Usman dan Fodio began in Gobir, one of the Hausa states. According to the Library of Congress country study, many Muslim clerics had become dissatisfied with the insecurity of the Hausa states and Borno, and some, including Usman dan Fodio, a member of the Qadiriyya brotherhood, began to work for the overthrow of the existing rulers. He drew support from clerics and especially from the Fulani, whose clan leaders and cattle owners provided most of the troops. The study notes that the cleric whose actions actually started the jihad, Abd as-Salam, was Hausa.

Uprisings broke out at the same time across Hausaland and Borno, and by 1808 the Hausa states had been conquered. Their ruling dynasties withdrew to new walled towns such as Abuja, Argungu and Maradi. In Borno the old capital, Birni Gazargamu, was destroyed, but the cleric al-Kanemi organised resistance and the jihadists were driven back.""",
  consequences="""The jihad created the Sokoto Caliphate, with its capital at Sokoto from 1809: a confederation of emirates that at its height stretched about 1,500 kilometres across the savanna. The country study describes it as the largest empire in Africa since the fall of Songhai in 1591, and says that it inspired related holy wars and Islamic states far beyond Nigeria.""")),

 ("lagos", "Lagos Becomes a British Colony", "lagos-becomes-british-colony", "1861", 1861, "year", "colonial", "Lagos", "colonial-nigeria",
  ["LCOL", "LEXT", "WHIST"], "well_documented", dict(
  description="""In 1861 Lagos became a British colony. Wikipedia's history of Nigeria describes the background: Lagos, like Badagry, Bonny and Calabar, remained a busy slave-trading port despite British anti-slavery treaties. A struggle over the Lagos throne led Britain to bombard the town in 1851. On 30 July 1861 the acting British consul, William McCoskry, and Commander Bedingfield met King Dosunmu aboard HMS Prometheus; under threat of bombardment, Dosunmu signed the Lagos Cession Treaty.""",
  consequences="""According to the Library of Congress country study, British official opinion was still reluctant to take on tropical colonies: a parliamentary report of 1865 even urged withdrawal from West Africa. Colonial Lagos nevertheless became a busy, cosmopolitan port, and its governors worked in the following decades to impose peace settlements on the warring Yoruba states of the interior.""")),

 ("benin1897", "The British Conquest of Benin", "british-conquest-of-benin-1897", "1897", 1897, "year", "conflict", "Benin City", "colonial-nigeria",
  ["LEXT", "WHIST", "WOVO"], "well_documented", dict(
  description="""In 1897 a British force took Benin City and brought the kingdom of Benin under British rule. According to the Library of Congress country study, the expedition followed the killing of a British consul and his party who were travelling to the city. Wikipedia's article on Oba Ovonramwen gives more detail: relations had worsened after a treaty of 1892 that the British read as limiting Benin's independence, and in January 1897 the acting consul-general, James Phillips, set out for Benin although the oba had asked him to postpone the visit; a group of Benin chiefs, despite objections attributed to the oba, attacked the party near Ugbine and killed Phillips and most of its European members. (Wikipedia's general history of Nigeria dates Phillips's journey to 1896 and calls him vice-consul.) A punitive expedition under Admiral Rawson followed in February 1897.""",
  consequences="""The British destroyed the oba's palace, and the oba was sent into exile; Benin was then governed indirectly through a council of chiefs (country study). Wikipedia records that the palace was burnt and its bronze sculptures were taken and later auctioned in Europe to pay for the expedition. The country study notes that the conquest completed the British occupation of south-western Nigeria.""")),

 ("kano1903", "The British Conquest of Kano and Sokoto", "british-conquest-of-kano-and-sokoto-1903", "1903", 1903, "year", "conflict", "Kano and Sokoto", "sokoto-caliphate-period",
  ["LLUG"], "well_documented", dict(
  description="""In 1903 forces of the Royal West African Frontier Force under Frederick Lugard, high commissioner of the Protectorate of Northern Nigeria since 1900, attacked Kano and Sokoto, the wealthiest emirate and the capital of the Sokoto Caliphate. According to the Library of Congress country study, Lugard set out to conquer the whole region and obtain the emirs' recognition of the protectorate, using force where diplomacy failed; Borno gave in without a fight. Lugard considered clear military victories necessary because the surrender of these centres would weaken resistance elsewhere.""",
  consequences="""The conquest ended the independence of the Sokoto Caliphate. Under Lugard's policy of indirect rule, emirs who accepted British authority were kept in office and kept their titles, but answered to British officers, who could depose them; caliphate officials became salaried district heads responsible for peace and tax collection (country study).""")),

 ("amalgamation", "The Amalgamation of Northern and Southern Nigeria", "amalgamation-of-nigeria-1914", "1914", 1914, "year", "administrative", "Nigeria", "colonial-nigeria",
  ["LUNI", "WHIST"], "well_documented", dict(
  description="""In 1914 the British merged the northern and southern protectorates into a single Nigeria. Frederick Lugard, who returned to Nigeria in 1912 after six years as governor of Hong Kong, carried out the merger and became its first governor-general. According to Wikipedia, unification gave Nigeria common telegraphs, railways, customs, a common currency and a common civil service.""",
  consequences="""The Library of Congress country study stresses how loose the union remained: the regions kept separate administrations and continued to be run in very different ways, with Christian missions kept out of the Muslim north and English the only official language in the south, while Hausa was also official in the north. Wikipedia likewise notes that north and south remained in practice two separately administered countries, and that differences in access to modern education between them soon became pronounced.""",
  historical_significance="""The amalgamation created the territory that became independent as Nigeria in 1960. Wikipedia notes that the imbalance between north and south that it left in place was reflected in Nigeria's political life.""")),

 ("oil1956", "Oil Discovered at Oloibiri", "oil-discovered-at-oloibiri-1956", "1956", 1956, "year", "economic", "Oloibiri, Niger Delta", "colonial-nigeria",
  ["LNAT", "LGOW"], "well_documented", dict(
  description="""In 1956 oil was found in commercial quantity at Oloibiri in the Niger Delta. According to the Library of Congress country study, the search for oil had begun in 1908 and been abandoned a few years later; it was revived in 1937 by Shell and British Petroleum and intensified in 1946. Exports began in 1958 from facilities built at Port Harcourt.""",
  consequences="""By the late 1960s oil had replaced cocoa, groundnuts and palm produce as Nigeria's largest earner of foreign exchange. In 1971 Nigeria, by then the world's seventh-largest producer, joined the Organization of the Petroleum Exporting Countries (OPEC), and the rise in world oil prices in 1974 brought a sudden flood of revenue (country study).""")),

 ("independence", "Nigeria Becomes Independent", "nigeria-independence-1960", "1 October 1960", 1960, "exact", "political", "Lagos", "first-republic",
  ["LNAT", "WFIRST", "WHIST"], "well_documented", dict(
  description="""Nigeria became independent from the United Kingdom on 1 October 1960. According to the Library of Congress country study, the constitution of independent Nigeria was prepared at conferences at Lancaster House in London in 1957 and 1958, and elections for an enlarged House of Representatives were held in December 1959, with 174 of its 312 seats allocated to the Northern Region on the basis of its larger population. Wikipedia records that independence was granted by an act of the British Parliament and that Nigeria was admitted to the United Nations.

Nigeria became independent as a federation of three regions under a parliamentary government, with Abubakar Tafawa Balewa as prime minister. It remained a Commonwealth realm, with Queen Elizabeth II as head of state, until it became a republic in 1963.""",
  historical_significance="""Independence opened the First Republic, the country's first period of civilian self-government.""")),

 ("republic1963", "Nigeria Becomes a Republic", "nigeria-becomes-a-republic-1963", "1963", 1963, "year", "political", "Lagos", "first-republic",
  ["LIND", "WFIRST"], "well_documented", dict(
  description="""In 1963 Nigeria adopted a new constitution and became a republic within the Commonwealth, ending the British monarch's role as head of state. According to the Library of Congress country study, the change called for no practical alteration of the constitutional system: a president, elected for five years by a joint session of parliament, replaced the Crown as the symbol of national sovereignty. Nnamdi Azikiwe, who had been governor-general, became the first president. Wikipedia notes that the Westminster system was kept, so that the president's powers were largely ceremonial and Abubakar Tafawa Balewa remained prime minister.""")),

 ("coup1966", "The Coup of 15 January 1966", "coup-of-january-1966", "15 January 1966", 1966, "exact", "political", "Lagos, Ibadan and Kaduna", "first-republic",
  ["LCRI", "WFIRST"], "well_documented", dict(
  description="""On 15 January 1966 a group of army officers attempted to seize power in Nigeria's first military coup. According to the country study, the conspirators killed Prime Minister Balewa in Lagos, Samuel Akintola, premier of the Western Region, in Ibadan, and Ahmadu Bello, premier of the Northern Region, in Kaduna, as well as senior officers of northern origin. Wikipedia names Major Chukwuma Kaduna Nzeogwu among the leaders and records that the finance minister, Festus Okotie-Eboh, was also killed.

The army commander, Major General Johnson Aguiyi-Ironsi, intervened to restore discipline, suspended the constitution, dissolved the legislatures, banned political parties and formed a Federal Military Government.""",
  consequences="""The coup ended the First Republic and began thirteen years of military rule. The country study notes that most of the conspirators were Igbo, and that Ironsi, also Igbo, was vulnerable to accusations of favouring the Igbo; his move towards a unitary state alarmed many northerners and led to the counter-coup of July 1966.""")),

 ("countercoup1966", "The Counter-Coup of July 1966", "counter-coup-of-july-1966", "29 July 1966", 1966, "exact", "political", "Nigeria", "military-rule-and-civil-war",
  ["LCRI", "LMURG"], "well_documented", dict(
  description="""In July 1966 northern officers and army units staged a counter-coup against the government of Major General Johnson Aguiyi-Ironsi, in which Ironsi and a number of other Igbo officers were killed. According to the Library of Congress country study, many northerners feared that Ironsi intended to deprive them of power and to build an Igbo-dominated centralised state; his decree abolishing the federal structure had been seen as disregarding the strength of regional and ethnic feeling. The country study elsewhere describes the coup of 29 July 1975 as falling on the ninth anniversary of the revolt that had brought Yakubu Gowon to power.

Lieutenant Colonel Yakubu Gowon became head of state. His first act was to repeal Ironsi's decree and restore the federal system, and he released Obafemi Awolowo and Anthony Enahoro from prison.""",
  consequences="""Violence against Igbo in the north followed, and many fled to the Eastern Region. The crisis led to the Eastern Region's secession as Biafra in May 1967 and to civil war (country study).""")),

 ("biafra", "The Declaration of Biafra", "declaration-of-biafra-1967", "30 May 1967", 1967, "exact", "political", "Eastern Region", "military-rule-and-civil-war",
  ["LWAR", "WWAR"], "well_documented", dict(
  description="""On 30 May 1967 Lieutenant Colonel Chukwuemeka Odumegwu Ojukwu, military governor of the Eastern Region, proclaimed the independent Republic of Biafra, named after the Bight of Biafra. According to the Library of Congress country study, the talks held at Aburi, Ghana, in January 1967 had failed to settle the crisis; Gowon proclaimed a state of emergency and announced the division of the country into twelve states, and the Eastern Region Consultative Assembly voted on 26 May to secede. Ojukwu gave as the main reason the federal government's failure to protect the lives of easterners, presenting secession as a step taken reluctantly after all efforts to protect the Igbo in other regions had failed.""",
  consequences="""Fighting between Nigeria and Biafra began on 6 July 1967 (Wikipedia), starting the Nigerian Civil War.""")),

 ("civilwar", "The Nigerian Civil War", "nigerian-civil-war", "6 July 1967 – 15 January 1970", 1967, "range", "conflict", "South-eastern Nigeria", "military-rule-and-civil-war",
  ["WWAR", "LWAR"], "well_documented", dict(
  description="""The Nigerian Civil War, also called the Biafran War, was fought between Nigeria and the secessionist Republic of Biafra from 6 July 1967 to 15 January 1970 (Wikipedia). Yakubu Gowon led the federal government and Chukwuemeka Odumegwu Ojukwu led Biafra.

According to the Library of Congress country study, federal forces had regained the Midwestern Region and secured the Delta by the end of 1967, cutting Biafra off from the sea. Owerri was captured in September 1968; the federal army, grown to nearly 250,000 men, opened three fronts in 1969. When Owerri fell on 6 January 1970, Biafran resistance collapsed; Ojukwu left for Côte d'Ivoire, and his chief of staff, Philip Effiong, called for a ceasefire and surrendered to the federal government. Wikipedia records that a blockade of Biafra led to mass starvation, and that images of starving children made the war an international cause and led to a large humanitarian airlift.""",
  consequences="""Estimates of the dead differ widely. The country study gives between 1 million and 3 million deaths in the former Eastern Region from fighting, disease and starvation; Wikipedia gives about 100,000 military deaths and between 500,000 and 2 million civilian deaths from starvation. At the end of the war, more than 3 million Igbo refugees were crowded into a small enclave (country study).""")),

 ("gowonout", "The Coup that Removed Gowon", "coup-of-july-1975", "29 July 1975", 1975, "exact", "political", "Lagos", "military-rule-and-civil-war",
  ["LMURG", "LMUR", "LOBA"], "well_documented", dict(
  description="""On 29 July 1975 Yakubu Gowon was deposed in a bloodless coup while he was attending a summit of the Organisation of African Unity in Kampala, Uganda. According to the Library of Congress country study, the political atmosphere had deteriorated, and many of the officers involved had taken part in the July 1966 coup that brought Gowon to power; the coup fell on its ninth anniversary. The armed forces chose Brigadier Murtala Muhammad, a northerner trained at Sandhurst who had commanded federal forces in the civil war, to succeed him.""",
  consequences="""Murtala Muhammad committed his government to hand power to an elected civilian government by October 1979, and in 1976 Nigeria came to have nineteen states. Murtala was assassinated during an unsuccessful coup in February 1976; his deputy, Lieutenant General Olusegun Obasanjo, continued the programme (country study).""")),

 ("civilrule1979", "Return to Civilian Rule", "return-to-civilian-rule-1979", "1 October 1979", 1979, "exact", "political", "Lagos", "second-republic",
  ["LSEC", "LOBA"], "well_documented", dict(
  description="""On 1 October 1979 the military government handed power to an elected civilian government under President Shehu Shagari, beginning the Second Republic. According to the Library of Congress country study, the first elections under the 1979 constitution were held on schedule in July and August 1979. Five parties took part; Shagari of the National Party of Nigeria won the presidency, defeating Nnamdi Azikiwe in a close and controversial vote.""",
  consequences="""The new constitution replaced the parliamentary system of the First Republic with a presidential one modelled on that of the United States. The Second Republic lasted until the military coup of 31 December 1983 (country study).""")),

 ("june12", "The Presidential Election of 12 June 1993", "presidential-election-of-12-june-1993", "12 June 1993", 1993, "exact", "political", "Nigeria", "military-rule-1983-1999",
  ["WHIST"], "well_documented", dict(
  description="""On 12 June 1993 Nigeria held a presidential election as the final step of General Ibrahim Babangida's transition to civilian rule. Only the two parties created by the government in 1989, the Social Democratic Party and the National Republican Convention, were allowed to take part. According to Wikipedia, most observers considered it the fairest election in Nigeria's history, and early returns showed a decisive victory for M. K. O. Abiola.

On 23 June 1993 Babangida annulled the election, citing pending lawsuits. More than 100 people were killed in the riots that followed.""",
  consequences="""Babangida handed power in August 1993 to an interim government under Ernest Shonekan, which was overthrown later that year by General Sani Abacha. Abiola was later imprisoned and died in custody (Wikipedia).""")),

 ("democracy1999", "Return to Democracy and the Fourth Republic", "return-to-democracy-1999", "29 May 1999", 1999, "exact", "political", "Abuja", "fourth-republic",
  ["WFOURTH", "WHIST"], "well_documented", dict(
  description="""On 29 May 1999 Olusegun Obasanjo was sworn in as president under a new constitution, ending military rule and beginning the Fourth Republic. After the death of General Sani Abacha in 1998, his successor, General Abdulsalami Abubakar, lifted the ban on political activity, released political prisoners and set up the Independent National Electoral Commission, which held local, state and national elections between December 1998 and February 1999 (Wikipedia). Obasanjo, a former military head of state who had been freed from prison, won the presidency for the People's Democratic Party.""",
  consequences="""The 1999 constitution, based largely on the suspended 1979 constitution, kept a presidential system, with a National Assembly of a 109-member Senate and a 360-member House of Representatives. Civilian government has continued since (Wikipedia).""")),

 ("election2015", "The 2015 Presidential Election", "presidential-election-2015", "28 March 2015", 2015, "exact", "political", "Nigeria", "fourth-republic",
  ["WFOURTH", "WHIST"], "well_documented", dict(
  description="""On 28 March 2015 Muhammadu Buhari of the All Progressives Congress (APC) won Nigeria's presidential election, defeating the incumbent, Goodluck Jonathan of the People's Democratic Party. According to Wikipedia, the election ended sixteen years of PDP rule, and Buhari, sworn in on 29 May 2015, became the first opposition candidate to win a presidential election since independence in 1960. The election had been postponed by six weeks because of violence by Boko Haram in the north-east.""",
  historical_significance="""Wikipedia's history of Nigeria notes that Jonathan conceded defeat, and that this was the only time in the Fourth Republic that voters refused to re-elect an incumbent president.""")),
]

RECORDS = []
for key, title, slug, date, year, prec, kind, loc, period, srcs, lvl, prose in EV:
    summary = prose["description"].split(". ")[0].rstrip(".") + "."
    if len(summary) > 480:
        summary = summary[:477].rsplit(" ", 1)[0] + "…"
    fields = dict(title=title, national_slug=slug, event_date=date, year=year, date_precision=prec, temporal_status="historical",
                  event_kind=kind, event_nature="documented_history", location=loc, period_id=f"@historical_periods:{period}",
                  short_summary=summary, country_note=None)
    fields.pop("country_note")
    fields.update(prose)
    RECORDS.append(dict(key=key, table="timeline_events", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=fields, srcs=[(s, title) for s in srcs]))

GAPS = [
    ("National events: coverage", "Seventeen events only. Missing so far: the Aba Women's War (1929), the 1946–1954 constitutions, the creation of twelve (1967), nineteen (1976), twenty-one (1987), thirty (1991) and thirty-six (1996) states, the move of the capital to Abuja (1991), ECOWAS (1975), the Ogoni Nine (1995), the Niger Delta crisis, Boko Haram and Chibok (2014), the #EndSARS protests (2020). Each needs its own sources."),
    ("National events: the 1966 crisis", "The killings of Igbo in the north in 1966 and their scale are referred to only in general terms; a dedicated scholarly source is needed before they are described in detail."),
    ("National events: second sources after 1990", "The 1993 and 2015 elections rest on Wikipedia only; INEC or academic sources should be added."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=[], gaps=GAPS, updates=[], relations=[], statistics=[],
                scope="National events: seventeen turning points of Nigerian history from the jihad of 1804 to the 2015 election, each linked to its historical period.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 136 — National: Events", "",
         f"Researched {ACCESSED}. Created as drafts; published only after your approval. Needs batch 135 (periods) first.", "",
         "- **17 national events**, 1804–2015, each linked to its historical period and tagged to the Nigeria collection only (never shown in the Tiv timeline).", ""]
    for key, title, slug, date, year, prec, kind, loc, period, srcs, lvl, prose in EV:
        L += [f"## {title} — {date} ({sum(words(v) for v in prose.values())} words; {lvl}; {', '.join(srcs)})", ""]
        for k, v in prose.items():
            L += [f"**{k}:**", ""] + [f"> {p}" if p else ">" for p in v.split("\n")] + [""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_136_national_events.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_136_national_events_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} sources={len(SOURCES)}")
    for key, title, slug, date, year, prec, kind, loc, period, srcs, lvl, prose in EV:
        print(f"  {slug}: {sum(words(v) for v in prose.values())} words")
