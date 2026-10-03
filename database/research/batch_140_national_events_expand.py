"""
Research batch 140 — National events: expansion past 300 words. Researched 2026-10-03 (owner: "let the event and people
pages pass 300 words"). Adds a 'Causes' (background) section and, where empty, 'Historical Significance' or
'Consequences' to each of the 29 national events of batches 136 and 138. Only EMPTY fields are filled (importer
'updates'); existing text is never changed, and a rollback empties the filled fields again.

Every sentence comes from the texts already saved in data/ (Library of Congress country study chapters; Wikipedia
articles of 2026-10-03). Target: each event's public word count (summary + prose) at least 300.
"""
import json, re, sys
import batch_135_national_periods as P

ACCESSED = "2026-10-03"
S = P.SOURCES
SOURCES = {k: S[k] for k in ["LEARLY", "LSAV", "LSOK", "LCOL", "LEXT", "LLUG", "LUNI", "LNAT", "LIND", "LCRI", "LWAR", "LGOW", "LMUR", "LOBA", "LSEC", "LMIL", "WHIST", "WFIRST", "WFOURTH", "WWAR"]}
SOURCES["LPOST"] = P.LOC(24, "Military Government in the Postwar Era", "The Northern Region divided into six states in 1967.")
SOURCES["LMURG"] = P.LOC(26, "The Regime of Murtala Muhammad", "Gowon deposed 29 July 1975; Murtala Muhammad chosen.")
for key, title, note in [
    ("WABA", "Women's War", "Causes (direct taxation of men in 1928; the 1929 census at Oloko); about 55 women killed; commissions of inquiry."),
    ("WECO", "Economic Community of West African States", "Treaty of Lagos 1975; revised treaty 1993; aims; peacekeeping; withdrawals."),
    ("WOPEC", "OPEC", "Founded in Baghdad on 14 September 1960; Nigeria joined 1971."),
    ("WFES", "FESTAC 77", "Background: the First World Festival of Black Arts, Dakar 1966; Présence Africaine."),
    ("WABJ", "Abuja", "Decree No. 6 of 4 February 1976; construction from 1979; capital from 12 December 1991."),
    ("WOGN", "Ogoni Nine", "Executions of 10 November 1995."),
    ("WKSW", "Ken Saro-Wiwa", "MOSOP and the Ogoni Bill of Rights."),
    ("WBAK", "Bakassi", "Population 150,000–300,000; fishing; oil interest; ICJ 2002."),
    ("WCHI", "Chibok schoolgirls kidnapping", "Shekau video; negotiations; 21 girls released October 2016."),
    ("WEND", "End SARS", "SARS founded late 1992; protests October 2020."),
    ("WABI", "Moshood Abiola", "SDP primaries in Jos, March 1993; running mate Kingibe; won in Kano and Abuja."),
    ("WBUH", "Muhammadu Buhari", "Candidacies 2003, 2007, 2011; APC candidate from December 2014."),
    ("WOVO", "Ovonramwen", "Treaty of 1892; Phillips's party attacked near Ugbine, January 1897."),
    ("WUSM", "Usman dan Fodio", "Scholar and author; critique of the Hausa ruling elites."),
    ("WIDIA", "Idia", "FESTAC 77 emblem."),
    ("WMUR", "Murtala Muhammed", "Led the mutiny of 29 July 1966 in Abeokuta."),
]:
    SOURCES[key] = P.WS(title, note)

# slug -> (fields to fill, sources)
UP = {
 "abuja-becomes-capital-1991": (dict(
  causes="""The decision to move the capital went back to the 1970s. According to Wikipedia, plans had been made since independence to place the capital somewhere neutral to all the major ethnic groups and close to all the regions; the central site was chosen in the early 1970s as a sign of neutrality and national unity. Lagos's rapid growth had also made it overcrowded, and the government wanted to spread economic development into the interior; the planners compared the project with Brazil's new capital, Brasília. The Library of Congress country study adds that the move was seen as a way of spreading industry inland and of relieving the congestion that threatened to choke Lagos, and that Abuja was chosen partly because it was not identified with any one ethnic group.

The Federal Military Government issued Decree No. 6 on 4 February 1976 to begin moving the capital from Lagos to Abuja, and early planning was carried out under Murtala Muhammad and Olusegun Obasanjo (Wikipedia).""",
  consequences="""Construction began in 1979 under President Shehu Shagari, who made Abuja his first journey outside Lagos after his election and pressed for the work to be finished. The move was controversial: Obafemi Awolowo led the opposition to it. Because of economic and political instability, the first stages of the city were not completed until the late 1980s, and the relocation, which Shagari had rescheduled from 1986 to 1992, finally took place in December 1991 (Wikipedia). The land of the Federal Capital Territory was home to the Gbagyi, Gwandara, Basa, Koro and other peoples (Wikipedia)."""),
  ["WABJ", "LOBA"]),

 "founding-of-ecowas-1975": (dict(
  causes="""ECOWAS grew out of the problems faced by the newly independent states of West Africa, former French, British and Portuguese colonies together with Liberia. According to Wikipedia, many of these states struggled to achieve economic development on their own, and a regional approach was seen as necessary; the community was founded to provide regional economic co-operation. The Library of Congress country study records that Nigeria joined other West African countries in creating ECOWAS in 1975, with a mandate to reduce trade barriers in the region, and that Nigeria's regional ties through ECOWAS and the Organisation of African Unity were complemented by its active role in the Commonwealth.""",
  historical_significance="""ECOWAS's stated goal is "collective self-sufficiency" for its members through a single large trading bloc and, eventually, a full economic union; the treaty was revised and signed again at Cotonou on 24 July 1993 (Wikipedia). Over time the community took on political and military co-operation as well: it has sent peacekeeping forces at least seven times, with interventions in Côte d'Ivoire, Liberia, Guinea-Bissau, Mali and The Gambia, and has monitored elections across the region. Mauritania withdrew in 2000, and in 2024 the military governments of Niger, Burkina Faso and Mali announced their withdrawal, which took effect on 29 January 2025 (Wikipedia)."""),
  ["WECO", "LGOW"]),

 "nigeria-joins-opec-1971": (dict(
  causes="""OPEC had been founded in Baghdad on 14 September 1960 by Iran, Iraq, Kuwait, Saudi Arabia and Venezuela, as a counterweight to the "Seven Sisters", the group of multinational companies that then dominated the world oil market (Wikipedia). Nigeria's oil industry had grown quickly since the first commercial discovery at Oloibiri in 1956 and the first exports in 1958, and by the late 1960s oil had become the country's biggest earner of foreign exchange (Library of Congress country study).""",
  historical_significance="""Membership tied Nigeria's economy to world oil prices. The country study describes how revenue rose by 350 per cent between 1973–74 and 1979, financing massive but poorly planned spending concentrated in the cities, and how the older exports, groundnuts, cotton, cocoa and palm produce, declined until they ceased to be important at all, leaving Nigeria's exports dominated by oil. In 1972 the government issued the first indigenisation decree, reserving some businesses for Nigerians, and in 1975 it bought 60 per cent of the equity in the marketing operations of the major oil companies (country study). In 1975 production fell sharply with a sudden drop in world demand, until OPEC intervened late in the year to raise prices. Nigeria remains a member of OPEC (Wikipedia)."""),
  ["WOPEC", "LGOW", "LOBA", "LNAT"]),

 "nigeria-becomes-a-republic-1963": (dict(
  causes="""Nigeria became independent in 1960 as a Commonwealth realm, with Queen Elizabeth II as head of state, represented by a governor-general (Wikipedia). Becoming a republic within the Commonwealth replaced the Crown with a Nigerian head of state, but, as the Library of Congress country study notes, it called for no practical change in the constitutional system.

The change came in the middle of the crises of the First Republic. In 1962 the Action Group, the governing party of the Western Region, split between Obafemi Awolowo and Samuel Akintola; rioting followed, the federal government declared a state of emergency in the region, and Awolowo and others were later convicted of treason. In 1963 the creation of a Midwestern Region, carved out of the Western Region, was confirmed by plebiscite, and the census of 1962–63, which decided each region's share of seats, was bitterly disputed (country study).""",
  consequences="""The republican constitution kept the parliamentary system, so that real power remained with Prime Minister Abubakar Tafawa Balewa and the regional governments. The tensions of these years, over the census, the Western Region and the federal election of December 1964, led to the military coup of January 1966, which ended the First Republic (country study; Wikipedia)."""),
  ["LIND", "LCRI", "WFIRST"]),

 "return-to-civilian-rule-1979": (dict(
  causes="""The return to civilian rule was the result of a transition programme begun by Murtala Muhammad, who in 1975 committed the military government to hand power to an elected civilian government by October 1979, and completed by Olusegun Obasanjo after Murtala's assassination in 1976. According to the Library of Congress country study, a draft constitution was published in October 1976 and debated by a constituent assembly; proposals to declare Nigeria a "socialist" state were rejected, and parties seeking registration had to have national objectives and executive boards drawn from at least two-thirds of the states. The constitution adopted in 1979 was modelled on that of the United States, with a president, a Senate and a House of Representatives. The drafting committee discarded Murtala Muhammad's recommendation of a non-party system, and local elections were held before the national ones (country study).""",
  historical_significance="""This was the first time a Nigerian military government handed power voluntarily to an elected civilian government. Five parties were registered for the 1979 elections, several of them successors of the parties of the First Republic (country study). The Second Republic that followed lasted until the coup of 31 December 1983."""),
  ["LMUR", "LOBA", "LSEC"]),

 "oil-discovered-at-oloibiri-1956": (dict(
  causes="""Oil exploration in Nigeria had a long and uncertain history before the find at Oloibiri. According to the Library of Congress country study, the search for oil began in 1908 and was abandoned a few years later; it was revived in 1937 by Shell and British Petroleum and intensified after 1946. Wikipedia's history of Nigeria dates the discovery of the Oloibiri oilfield, the first in the country, to January 1956, in the south-south of present-day Nigeria.""",
  historical_significance="""The discovery came just before independence and changed the basis of the Nigerian economy. Oil was first exported in 1958 from facilities built at Port Harcourt. By the late 1960s it had replaced cocoa, groundnuts and palm produce as the main earner of foreign exchange, and after the price rises of 1974 it dominated Nigeria's exports. The country study notes that oil revenue paid for much of the industrial growth and spending of the 1970s, but that the boom's end in 1981 put severe strain on the Second Republic. Control of oil revenue, and the conditions of the oil-producing Niger Delta, have remained central issues of Nigerian politics (country study; Wikipedia)."""),
  ["LNAT", "LGOW", "LOBA", "LSEC", "WHIST"]),

 "presidential-election-2015": (dict(
  causes="""Muhammadu Buhari had stood for president three times before. According to his Wikipedia article, he was the candidate of the All Nigeria Peoples Party in 2003 and 2007, losing in 2007 to Umaru Yar'Adua, and of the Congress for Progressive Change, a party he helped found, in 2011, when he came second to Goodluck Jonathan with 32 per cent of the vote (Wikipedia's history of Nigeria). Before the 2015 election four opposition parties merged to form the All Progressives Congress, and in December 2014 Buhari became its presidential candidate.

Jonathan's government was criticised for its handling of the Boko Haram insurgency, including the kidnapping of the Chibok schoolgirls in 2014, and for corruption; Wikipedia's history of Nigeria describes the high level of corruption as a decisive factor in the election. The vote, originally set for mid-February, was postponed by six weeks because of Boko Haram violence in the north-east; observers described the election as fair, and Buhari, then 72, won on his fourth attempt (Wikipedia).""",
  consequences="""Buhari was sworn in on 29 May 2015, ending sixteen years of government by the People's Democratic Party. He was re-elected in February 2019 and served until 29 May 2023 (Wikipedia)."""),
  ["WBUH", "WHIST", "WFOURTH"]),

 "lagos-becomes-british-colony": (dict(
  causes="""Britain's interest in Lagos grew out of its campaign against the Atlantic slave trade, which it banned from 1807. According to Wikipedia's history of Nigeria, the West Africa Squadron concluded anti-slavery treaties with coastal rulers, but the Nigerian coast, with its many creeks and channels, was hard to control, and Badagry, Lagos, Bonny and Calabar remained busy slave-trading ports.

In 1841 Oba Akitoye of Lagos tried to end the trade; merchants opposed to the ban deposed him in favour of his nephew Kosoko. Britain intervened in this struggle and bombarded Lagos in 1851. Akitoye's successor, Dosunmu, was pressed in 1861 to accept a treaty of cession, which he signed under threat of bombardment (Wikipedia).""",
  historical_significance="""The Library of Congress country study describes colonial Lagos as a busy, cosmopolitan port with Victorian and Brazilian architecture and a black elite that included English-speakers from Sierra Leone and freed slaves who had returned from Brazil and Cuba; Africans were also represented on the largely appointed Lagos Legislative Council. Lagos became the first base of British power in what is now Nigeria and later the capital of the country, until Abuja replaced it in 1991."""),
  ["WHIST", "LEXT", "LCOL"]),

 "presidential-election-of-12-june-1993": (dict(
  causes="""The election was the climax of the long transition programme of General Ibrahim Babangida, who had promised to return Nigeria to civilian rule first by 1990 and then by January 1993. In 1989 the government created two parties, the Social Democratic Party (SDP) and the National Republican Convention (NRC), and allowed no others; earlier rounds of presidential primaries were cancelled and their candidates disqualified (Wikipedia's history of Nigeria).

According to Wikipedia's article on him, M. K. O. Abiola announced his candidacy in February 1993 and won the SDP primaries in Jos in March, narrowly defeating Baba Gana Kingibe, who became his running mate. His campaign slogans included "Farewell to poverty".""",
  historical_significance="""Abiola defeated Bashir Tofa of the NRC. According to Wikipedia, he won in more than two-thirds of the states, in the capital, Abuja, and even in Tofa's home state of Kano; as a Muslim from the south, he drew support across regional and religious lines. By the time of his death in 1998, Wikipedia notes, Abiola had become an unexpected symbol of democracy, and in 2018 Democracy Day was moved to 12 June in his honour."""),
  ["WHIST", "WABI"]),

 "return-to-democracy-1999": (dict(
  causes="""The transition followed the death of General Sani Abacha on 8 June 1998. His successor, General Abdulsalami Abubakar, commuted the sentences of those convicted of plotting coups under Abacha, released almost all known political detainees and, in August 1998, set up the Independent National Electoral Commission. Three parties qualified to contest the elections: the People's Democratic Party, the All People's Party and the Alliance for Democracy (Wikipedia's history of Nigeria).""",
  historical_significance="""In his first months in office Obasanjo retired some 200 military officers, including all 93 who held political office, making a coup by senior officers less likely, and placed the Ministry of Defence under more direct government control. Press freedom grew. His government also faced serious communal violence, in Kaduna, Jos and across Benue, Taraba and Nasarawa states, and in November 1999 the army destroyed the town of Odi in Bayelsa State after the killing of twelve policemen (Wikipedia). Obasanjo was re-elected in 2003 (Wikipedia). The civilian order begun in 1999 has continued since (Wikipedia)."""),
  ["WHIST", "WFOURTH"]),

 "nineteen-states-1976": (dict(
  causes="""The creation of more states had been part of the military government's programme since 1970, when Yakubu Gowon listed it among the tasks to be completed before a return to civilian rule (Library of Congress country study). Behind it lay the long agitation of minority peoples for their own states, which went back to the 1950s and early 1960s, when movements such as the United Middle Belt Congress sought to separate the middle belt from the Northern Region, and riots in Tivland in 1960 and 1964 were linked to this agitation (country study).""",
  consequences="""The new states broke up the large ethnic blocs of the old regions. Along with the new states, the government decided in 1976 to move the federal capital from Lagos to a new site at Abuja (Decree No. 6 of 4 February 1976, the day after the states were created; Wikipedia). The number of states continued to grow: to twenty-one in 1987, thirty in 1991 and thirty-six in 1996 (this archive's state records)."""),
  ["LGOW", "LCRI", "WABJ"]),

 "chibok-schoolgirls-kidnapping-2014": (dict(
  causes="""The kidnapping took place during the insurgency of Boko Haram in north-eastern Nigeria. According to Wikipedia, the school at Chibok had been closed for four weeks because of the deteriorating security situation, and the girls had returned only to sit their examinations. In the confusion that followed, the military at first wrongly claimed that most of the girls had escaped or been released. On 5 May 2014 Boko Haram's leader, Abubakar Shekau, claimed responsibility in a video, and said he would not free the girls until imprisoned members of the group were released.""",
  historical_significance="""The kidnapping drew worldwide attention to the insurgency and to the government's response. Wikipedia's history of Nigeria describes it as highlighting the weakness of the Nigerian state, and the criticism of President Goodluck Jonathan's handling of Boko Haram became an issue in the 2015 election. After his election, President Muhammadu Buhari said he was willing to negotiate with Boko Haram for the girls' release; 21 of the girls were later freed after negotiations brokered by the International Committee of the Red Cross and the Swiss government (Wikipedia)."""),
  ["WCHI", "WHIST"]),

 "nigeria-independence-1960": (dict(
  causes="""Independence was the result of a nationalist movement that grew from the 1920s and of a series of constitutional reforms after the Second World War. According to the Library of Congress country study, the 1922 constitution allowed a few elected members on the Legislative Council; Herbert Macaulay's Nigerian National Democratic Party dominated Lagos politics, and from 1944 the NCNC, led by Nnamdi Azikiwe, became the first party with nationwide appeal, followed by the Action Group of Obafemi Awolowo and the Northern People's Congress of Ahmadu Bello. Three constitutions between 1946 and 1954 moved the country towards self-government: the 1954 constitution established the federal principle, and in 1957 the Western and Eastern regions became self-governing.""",
  consequences="""Wikipedia records that Nigeria became independent with a federal constitution, three large regions and a relatively weak centre. The Northern People's Congress and the NCNC formed the government, with the Action Group in opposition, and Azikiwe became governor-general. In 1961 the Northern Cameroons voted to join Nigeria, while the Southern Cameroons chose Cameroon (Wikipedia)."""),
  ["LNAT", "WHIST"]),

 "declaration-of-biafra-1967": (dict(
  causes="""The declaration followed the crisis of 1966. After the coup of January 1966, in which most of the conspirators were Igbo, and the counter-coup of July, in which Johnson Aguiyi-Ironsi and other Igbo officers were killed, killings of Igbo in the north drove more than a million people back to the Eastern Region (Library of Congress country study). Some of Ojukwu's colleagues questioned whether the country could be reunited after these outrages. Ojukwu, fearing for his safety, refused to attend meetings in Lagos; the military leaders met at Aburi, Ghana, in January 1967 without settling the crisis, and the Eastern government then announced that it would keep all revenue collected in the region to pay for resettling refugees (country study).""",
  historical_significance="""The federal government treated the secession as a rebellion, and the fighting that began in July 1967 lasted until January 1970. France, Côte d'Ivoire and Gabon supported Biafra, while the United Kingdom and the Soviet Union were the main supporters of the federal government (Wikipedia). Wikipedia notes that the war's legacy includes the Igbo people's sense of political marginalisation and the rise, decades later, of new Biafran separatist movements."""),
  ["LCRI", "LWAR", "WWAR"]),

 "creation-of-states-1987-1996": (dict(
  causes="""The demand for new states had deep roots. According to the Library of Congress country study, minority movements after independence sought states of their own: the United Middle Belt Congress campaigned for the separation of the middle belt from the Northern Region, the Edo and western Igbo for a Midwestern Region, and Ijaw and Efik-Ibibio leaders for a region on the coast between the Niger Delta and Calabar. Successive governments answered with the twelve states of 1967 and the nineteen of 1976.

The reorganisations of 1987 and 1991 were made by the military government of General Ibrahim Babangida, who came to power in 1985, and that of 1996 by General Sani Abacha (Wikipedia's history of Nigeria).""",
  consequences="""Each state is divided into local government areas, of which Nigeria today has 774 (Wikipedia, States of Nigeria). Under the 1999 constitution the thirty-six states are semi-autonomous units that share power with the federal government, and each has an elected governor, a single-chamber House of Assembly and its own judiciary (Wikipedia)."""),
  ["LCRI", "WHIST", "P_STATES"]),

 "coup-of-july-1975": (dict(
  causes="""In October 1970 Gowon had announced that the military would stay in power until 1976, the target year for completing its programme and returning to elected civilian government. According to the Library of Congress country study, the oil boom that followed the price rises of 1974 brought rapid inflation and wide inequalities, which the public blamed on corruption and mismanagement under Gowon's government. The north also suffered a severe drought between 1972 and 1974. The country study records that the political atmosphere deteriorated to the point that Gowon was deposed. His government had ruled by decree through the Supreme Military Council, although the agreement of the state military governors was sought before decrees were issued.""",
  historical_significance="""The coup brought to power a government that set a firm timetable for the return to civilian rule, which was completed in 1979. Murtala Muhammad's policies won him broad popular support in a short time, and his assassination in February 1976 caused deep national mourning (country study)."""),
  ["LGOW", "LMURG", "LMUR"]),

 "british-conquest-of-kano-and-sokoto-1903": (dict(
  causes="""British claims to the Niger basin had been recognised at the Berlin Conference of 1885, on condition of effective occupation (Library of Congress country study). The Royal Niger Company held the Niger and Benue but had no real control of the north, and its charter was ended on 31 December 1899 because it was not considered sufficient for the conquest of the Sokoto Caliphate. From 1900 Frederick Lugard, high commissioner of the Protectorate of Northern Nigeria, set out to conquer the region and obtain recognition of British rule from the emirs (country study).""",
  historical_significance="""The fall of Kano and Sokoto completed the British conquest of northern Nigeria. The emirs kept their titles under indirect rule, and the country study notes that in the north the colonial government took careful account of Islam, kept out Christian missions and harmonised its limited educational efforts with Islamic institutions, a policy that left the north and south developing differently."""),
  ["LCOL", "LEXT", "LLUG", "LUNI"]),

 "coup-of-january-1966": (dict(
  causes="""The coup followed years of political crisis. According to the Library of Congress country study, the federal election campaign of December 1964 was contested by two alliances and boycotted in parts of the country; in the Western Region the election of November 1965 was followed by violence in which an estimated 2,000 people died in six months. The disputed censuses of 1962 and 1963, the split in the Action Group and the treason trial of Obafemi Awolowo had already shaken confidence in the political system, and the country study records widespread abuses: intimidation of opponents, manipulation of the courts, diversion of public funds, rigging of elections and corruption.""",
  historical_significance="""The coup began thirteen years of military rule and set off the chain of events, the July counter-coup, the killings in the north and the secession of Biafra, that led to the civil war (country study). Wikipedia notes that it is unclear whether President Azikiwe, who was abroad at the time, had been warned."""),
  ["LCRI", "WFIRST"]),

 "amalgamation-of-nigeria-1914": (dict(
  causes="""Before 1914 British rule in Nigeria had been built up piece by piece: the Colony of Lagos from 1861, the Oil Rivers and Niger Coast protectorates on the coast, and from 1900 the Protectorate of Northern Nigeria, conquered by Frederick Lugard (Library of Congress country study). Lugard, who had been high commissioner in the north from 1900 to 1906 and then governor of Hong Kong, returned to Nigeria in 1912 to carry out the merger of the northern and southern protectorates. Wikipedia's history of Nigeria notes that in the same year he had a new port built in the south-east, named Port Harcourt, which was linked by railway to the coal mines of Enugu. Lugard's method was indirect rule through traditional rulers, which he applied to the whole country after the merger; in 1916 he formed the Nigerian Council, a consultative body of six traditional rulers including the Sultan of Sokoto, the Emir of Kano and the king of Oyo (country study)."""),
  ["LLUG", "LUNI", "WHIST"]),

 "bakassi-judgment-2002": (dict(
  causes="""The Bakassi Peninsula, with a population generally put at between 150,000 and 300,000, lies where warm and cold ocean currents meet, making it a rich fishing ground on which most of its people depend (Wikipedia). It is often described as oil-rich, although no commercially viable oil has been found there; its waters have nevertheless attracted at least eight multinational oil companies. Disputes between Nigeria and Cameroon over the peninsula led Cameroon to take the case to the International Court of Justice in 1994 (Wikipedia). The fishing grounds form where the warm Guinea Current, called Aya Efiat in Efik, meets the cold Benguela Current, Aya Ubenekang, building up shoals rich in fish and shrimp. After the judgment the territory was handed over in two stages, the first in August 2006 and the rest two years later, on 14 August 2008 (Wikipedia)."""),
  ["WBAK"]),

 "endsars-protests-2020": (dict(
  causes="""The Special Anti-Robbery Squad was founded in late 1992 as one of fourteen units of the police Force Criminal Investigation and Intelligence Department, a masked unit for undercover operations against armed robbery, car snatching, kidnapping, cattle rustling and illegal firearms (Wikipedia). Over time it became notorious for abuses against citizens. Protests against it began in 2017, when activists, young people and celebrities took to the streets and to social media under the hashtag #EndSARS to demand its disbandment.""",
  historical_significance="""Wikipedia describes the movement as decentralised and led mainly by young people, and notes the role of the internet and social media in sustaining it; a network of more than 400 volunteer lawyers represented detained protesters. A memorial and fresh protests followed, including a protest in February 2021 against the reopening of the Lekki toll gate."""),
  ["WEND"]),

 "counter-coup-of-july-1966": (dict(
  causes="""After the January coup, Ironsi's government suspended the constitution and moved towards a unitary state, which Ironsi and his advisers believed would end the regionalism that had blocked political progress. According to the Library of Congress country study, Ironsi's decree showed a dangerous disregard for regional feeling after the bloody coup, and he was open to accusations of favouring the Igbo. Some northern leaders spoke seriously of secession, and many northerners feared that he meant to deprive them of power. The January coup itself had been carried out mainly by Igbo officers and had killed the premiers of the north and west, Ahmadu Bello and Samuel Akintola, together with senior officers of northern origin (country study). Wikipedia's article on Murtala Muhammed records that he led the mutiny of the night of 29 July 1966 in Abeokuta."""),
  ["LCRI", "WMUR"]),

 "twelve-states-1967": (dict(
  causes="""The division into states had been debated since independence. The country study describes how minority peoples sought states of their own, and how in 1966 and 1967 even northern leaders, who had been the first to threaten secession, came to favour a federation of many states. The twelve-state plan was announced as the Eastern Region moved towards secession (Library of Congress country study).""",
  consequences="""The country study records that the new structure made national unity more attractive to westerners, who now had a Yoruba-majority state, Kwara, in the north, and that it removed the north's former single power base. In the war that followed, federal forces secured the Delta, which became the Rivers and South-Eastern states, cutting Biafra off from the sea."""),
  ["LWAR", "LPOST"]),

 "execution-of-the-ogoni-nine-1995": (dict(
  causes="""From 1990 Ken Saro-Wiwa devoted most of his time to the Ogoni people of the Niger Delta, whose land had been the site of oil extraction since the 1950s and suffered severe environmental damage (Wikipedia). The Movement for the Survival of the Ogoni People (MOSOP) drew up the Ogoni Bill of Rights, demanding greater autonomy for the Ogoni, a fair share of the proceeds of oil, and repair of the damage to Ogoni land. In May 1994 four Ogoni chiefs known to oppose MOSOP were murdered, and the government blamed the movement (Wikipedia).""",
  historical_significance="""Saro-Wiwa had been vice-chairman of the General Assembly of the Unrepresented Nations and Peoples Organization from 1993 to 1995. According to Wikipedia's article on him, his execution triggered international outrage and led to Nigeria's suspension from the Commonwealth for more than three years."""),
  ["WKSW", "WOGN"]),

 "festac-77": (dict(
  causes="""FESTAC '77 was the second World Black and African Festival of Arts and Culture. The first had been held in Dakar, Senegal, in April 1966, under the leadership of Léopold Sédar Senghor and with outside support including UNESCO, and a pan-African festival followed in Algiers in 1969 (Wikipedia). These festivals grew from the movement of writers and thinkers of African descent who, from the 1940s, gathered around the Paris journal and publishing house Présence Africaine, founded by Aimé Césaire, Senghor and others; its forums brought together writers such as Cheikh Anta Diop, Richard Wright, James Baldwin and Frantz Fanon (Wikipedia).""",
  historical_significance="""The festival's emblem, the ivory mask identified with Queen Mother Idia of Benin, was afterwards reproduced widely as a symbol of Nigerian and African cultural heritage (Wikipedia, Idia). The festival also left Lagos the National Theatre at Iganmu and Festac Village, and led to the Nigerian National Council of Arts and Culture (Wikipedia)."""),
  ["WFES", "WIDIA"]),

 "womens-war-1929": (dict(
  causes="""The immediate cause was the extension of direct taxation. Direct taxation of men was introduced in the region in 1928 without major incidents (Wikipedia). In 1929 a new census of households, recording wives, children and livestock, led the women of Oloko to suspect that they too would be taxed. The world financial crash of 1929 had already hurt women's trade and production. On 18 November 1929 a dispute broke out between a woman named Nwanyeruwa and Mark Emereuwa, who was counting people for the warrant chief Okugo, and the protest spread from there (Wikipedia).""",
  historical_significance="""By the time order was restored, about 55 women had been killed by colonial troops, and more than thirty punitive inquiries had been carried out across the region. A commission of inquiry held public sittings for thirty-eight days in the Owerri and Calabar provinces and heard 485 witnesses, only about 103 of them women (Wikipedia)."""),
  ["WABA"]),

 "british-conquest-of-benin-1897": (dict(
  causes="""The background was a struggle over trade and authority. According to Wikipedia's article on Oba Ovonramwen, a treaty of 1892, negotiated by the vice-consul Henry Gallwey, was read by the British as limiting Benin's independence, while Ovonramwen continued to enforce Benin's trade restrictions and royal monopolies. As British officials and European merchants pressed for access to Benin's markets, senior protectorate officers were by 1896 openly considering removing him, and in November 1896 James Phillips sought approval to depose him. Benin, about 150 kilometres east of Lagos, was one of the largest kingdoms in what is now Nigeria (Wikipedia's history of Nigeria), and the Library of Congress country study notes that the consul's party was on its way to investigate reports of ritual human sacrifice in the city."""),
  ["WOVO", "WHIST", "LEXT"]),

 "jihad-of-usman-dan-fodio": (dict(
  causes="""The Library of Congress country study traces the background to the eighteenth century: rivalry and wars among the Hausa states, the weakness of Borno, and severe droughts and famines across the Sahel in the 1740s–1750s and again in the 1790s, which brought many Fulani into Hausaland. Muslim clerics began to voice the grievances of ordinary people, and attempts by rulers to control them only increased the tension. Usman dan Fodio himself, according to Wikipedia, criticised the ruling elites for their greed and their violations of Islamic law."""),
  ["LSAV", "LSOK", "WUSM"]),

 "nigerian-civil-war": (dict(
  causes="""The war grew out of the political crisis of the First Republic and the coups of 1966. According to Wikipedia, its immediate causes were the January coup, the July counter-coup and the killings of Igbo in the Northern Region, after which the Eastern leadership concluded that the federal government was unwilling or unable to protect its people. The Library of Congress country study records that talks at Aburi failed, that the federal government announced a twelve-state structure, and that Biafra was proclaimed on 30 May 1967."""),
  ["WWAR", "LWAR"]),
}

UPDATES = []
for slug, (fields, srcs) in UP.items():
    srcs = [s for s in srcs if s != "P_STATES"] + (["STAT"] if "P_STATES" in srcs else [])
    UPDATES.append(dict(ref=f"@timeline_events:{slug}", fields=fields, srcs=[(s, f"Expansion of '{slug}'") for s in srcs]))
SOURCES["STAT"] = dict(source_type="website", source_kind="reference_database", source_tier=2, title="States of Nigeria", organisation="Statoids",
                       url="https://www.statoids.com/ung.html", verification_status="verified", notes="Reused (archive source #180).")
SOURCES["WSTATES"] = P.WS("States of Nigeria", "36 states and the FCT; 774 local government areas; state legislatures, governors and judiciaries.")
for u in UPDATES:
    if u["ref"].endswith("creation-of-states-1987-1996"):
        u["srcs"].append(("WSTATES", "Expansion of 'creation-of-states-1987-1996'"))

GAPS = [("National events expansion", "Expanded from the sources already read; the single-source events noted in batches 136 and 138 still need independent sources.")]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS, updates=UPDATES, relations=[], statistics=[],
                scope="Expansion of the 29 national events past 300 words: background ('Causes') and significance or consequences, filling empty fields only.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_140_national_events_expand.json", "w"), indent=1, ensure_ascii=False)
    L = ["# Research batch 140 — National events: expansion", "", f"Researched {ACCESSED}. Fills empty sections only.", ""]
    for slug, (fields, srcs) in UP.items():
        L += [f"## {slug} (+{sum(words(v) for v in fields.values())} words)", ""]
        for k, v in fields.items():
            L += [f"**{k}:**", ""] + [f"> {p}" if p else ">" for p in v.split("\n")] + [""]
    open(f"{out}/batch_140_national_events_expand_REVIEW.md", "w").write("\n".join(L))
    print(f"updates={len(UPDATES)} sources={len(SOURCES)}")
    for slug, (fields, srcs) in UP.items():
        print(f"  {slug}: +{sum(words(v) for v in fields.values())}")
