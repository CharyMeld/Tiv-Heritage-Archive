"""
Research batch 135 — National: Historical Periods. Researched 2026-10-03 (owner: fill the empty People / Events / Periods
sections of /nigeria; periods first, since events link to them).

Sources read (saved in data/): Library of Congress, Federal Research Division, 'Nigeria: A Country Study' (text reflects
information as of December 1990), history chapters, countrystudies.us/nigeria/3.htm–30.htm → data/loc_cs_nigeria_N.txt;
Wikipedia 'History of Nigeria' (rev 2026), 'Nok culture', 'Iho Eleru', 'First Nigerian Republic', 'Fourth Nigerian
Republic', 'Nigerian Civil War' → data/wiki_*_2026-10-03.txt.

Periods overlap where the sources' themes overlap (the early states run into the slave-trade era and the jihad); each
period's dates are the ones its own text explains. Where the two sources disagree (Nok dates, civil-war deaths), both are
given. No period is marked as more than 'well_documented'; prehistory and Nok, where the sources hedge, are 'reported'.
"""
import json, re, sys

ACCESSED = "2026-10-03"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}; text saved in database/research/data/.")
LOC = lambda n, t, note: dict(source_type="government_publication", source_kind="government_publication", source_tier=2,
                              title=f"Nigeria: A Country Study — {t}", organisation="Federal Research Division, Library of Congress",
                              url=f"http://countrystudies.us/nigeria/{n}.htm", lineage_group="loc-country-study-nigeria",
                              verification_status="verified",
                              notes=f"{note} The study's text reflects information as of December 1990 (preface). Mirror of the Library of Congress text. Accessed {ACCESSED}; saved as data/loc_cs_nigeria_{n}.txt.")
SOURCES = {
    "LEARLY": LOC(4, "Early History", "Iwo Eleru skeleton; Taruga furnaces; the Nok culture (4th century BC – 2nd century AD); the 'silent millennium'; trans-Saharan trade."),
    "LSTATES": LOC(5, "Early States Before 1500", "Yoruba kingdoms, Ife, Oyo, Benin; Igbo society and Nri; Kanem-Borno; the Hausa states; the Fulani."),
    "LSAV": LOC(6, "The Savanna States, 1500-1800", "Songhai and Borno; Idris Aloma; the Hausa states; the 18th-century droughts."),
    "LSLAVE": LOC(7, "The Slave Trade", "Portuguese arrival (1471, 1481); Benin; the transatlantic trade; Oyo and the Aro; more than 3.5 million slaves shipped from Nigeria."),
    "LSOK": LOC(9, "Usman dan Fodio and the Sokoto Caliphate", "Jihad from 1804; Sokoto founded 1809; thirty emirates; Al Kanemi in Borno; Ilorin; the fall of Oyo."),
    "LCOL": LOC(15, "Colonial Nigeria", "Lagos a colony in 1861; the 1865 report; the Berlin Conference of 1885."),
    "LEXT": LOC(16, "Extension of British Control", "Oil Rivers and Niger Coast protectorates; Benin 1897; end of the Royal Niger Company charter, 31 December 1899."),
    "LLUG": LOC(17, "Frederick Lugard", "Protectorate of Northern Nigeria from 1900; the 1903 assaults on Kano and Sokoto; indirect rule."),
    "LUNI": LOC(18, "Unification of Nigeria", "The 1914 merger; the Nigerian Council (1916); indirect rule; World War I; the Cameroons mandate (1920)."),
    "LNAT": LOC(20, "Emergence of Nigerian Nationalism", "Parties (NCNC 1944, Action Group 1951, NPC); constitutions 1946–1954; oil found 1956 at Oloibiri; independence 1 October 1960."),
    "LIND": LOC(21, "Independent Nigeria", "NPC–NCNC coalition; the Action Group crisis of 1962; republic in 1963 with Azikiwe as president."),
    "LCRI": LOC(22, "Politics in the Crisis Years", "Census disputes; the 1964–65 elections; the coups of January and July 1966."),
    "LWAR": LOC(23, "Civil War", "Aburi (January 1967); Biafra proclaimed 30 May 1967; the war's end in January 1970; death estimates."),
    "LGOW": LOC(25, "The Gowon Regime", "Supreme Military Council; OPEC membership (1971); ECOWAS (1975); the oil boom."),
    "LMUR": LOC(27, "Preparations for the Return to Civilian Rule", "Murtala Muhammad's transition programme; nineteen states in 1976."),
    "LOBA": LOC(28, "The Obasanjo Regime", "The 1979 constitution modelled on the United States constitution; the oil boom."),
    "LSEC": LOC(29, "The Second Republic, 1979-83", "Elections of 1979; Shagari president from 1 October 1979; the five parties; the end of the oil boom in 1981; expulsions of 1983."),
    "LMIL": LOC(30, "Return to Military Rule", "Coup of 31 December 1983; Buhari; War Against Indiscipline; Babangida's transition programme."),
    "WHIST": WS("History of Nigeria", "Overview from prehistory to the present: Dufuna canoe; Iwo/Iho Eleru; Nok; the Third Republic and the annulled election of 12 June 1993; Abacha; Abubakar's transition; the Fourth Republic presidencies."),
    "WNOK": WS("Nok culture", "Named after Nok village (southern Kaduna State); first terracotta found 1928; may have emerged c. 1500 BCE and lasted to c. 1 BCE; iron metallurgy; funerary sculpture."),
    "WIHO": WS("Iho Eleru", "Rock shelter at Isarun, Ondo State; Later Stone Age; found in 1961; the Iho Eleru skull about 13,000 years old."),
    "WFIRST": WS("First Nigerian Republic", "Republic 1963–1966; the period 1 October 1960 – 15 January 1966 also called the First Republic; the January 1966 coup."),
    "WFOURTH": WS("Fourth Nigerian Republic", "Constitution adopted 29 May 1999; Obasanjo, Yar'Adua, Jonathan, Buhari (2015), Tinubu (2023)."),
    "WWAR": WS("Nigerian Civil War", "6 July 1967 – 15 January 1970; estimates of about 100,000 military deaths and 500,000 to 2 million civilian deaths from starvation."),
}

TEXT = {
 "prehistory": """Prehistoric Nigeria is the long span of human settlement before farming, ironworking and the first identifiable cultures. The Library of Congress country study notes that the region was settled for millennia before agriculture spread about 3,000 years ago, but that comparatively little archaeological work has been done, so this early history can only be outlined.

The best-known early human find is from Iho Eleru ("Cave of Ashes"), formerly written Iwo Eleru, a rock shelter at Isarun near Akure in Ondo State. It was found in 1961 during a survey of the hills around Akure. According to Wikipedia, the Iho Eleru skull is about 13,000 years old, and the shelter contains Later Stone Age tools from the transition between the Pleistocene and the Holocene. The country study, written in 1990, gives a lower estimate of about 10,000 years for the skeleton and adds that stone tools at the site are some 2,000 years older still.

Wikipedia's history of Nigeria describes, in cautious terms, the wider Stone Age of West Africa: earlier stone-tool users who may have lived in the region hundreds of thousands of years ago, and Middle and Later Stone Age hunter-gatherers whose way of life eventually gave way to farming communities. It also records the Dufuna canoe, a dugout found in Yobe State and dated to around 6300 BC, as the oldest known boat in Africa.

Farming developed gradually. The country study says that pastoralists in the savanna had microlithic and ceramic industries from at least the fourth millennium BC, continued by the grain farmers of settled communities, while further south hunting and gathering gave way to farming on the edge of the forest in the first millennium BC, and yams were later grown in forest clearings. Stone axe heads used to open up the forest were later venerated by the Yoruba as "thunderbolts" thrown down by the gods.

The end of this period is not a single date: it overlaps with the beginnings of ironworking and of the Nok culture, which Wikipedia dates from about 1500 BCE (see the next period).""",

 "nok": """The Nok culture is the earliest culture in Nigeria to be identified by its own distinctive artefacts. It is named after the village of Nok, in the Ham area of southern Kaduna State, where its terracotta sculptures were first found at a tin mine in 1928.

The two sources read date it differently. The Library of Congress country study places the Nok between the fourth century BC and the second century AD, in a large area above the confluence of the Niger and Benue rivers on the Jos Plateau. Wikipedia, drawing on more recent research, says the culture may have emerged around 1500 BCE and lasted until about 1 BCE, with the earliest terracotta sculptures perhaps made around 900 BCE. Both describe the Nok as ironworkers: the country study cites the furnaces at Taruga, dated to the fourth century BC, as the oldest evidence of metalworking in West Africa, and Wikipedia says iron metallurgy may have developed independently in the Nok culture between about 750 and 550 BCE.

The Nok are best known for their hollow, nearly life-size terracotta heads and figures, with detailed hairstyles, jewellery and stylised features. According to Wikipedia they are regarded as the earliest large figurative sculpture in Africa outside ancient Egypt; they were probably part of a complex funerary culture, and some show hunters with slings, bows and arrows, and people paddling a dugout canoe laden with goods. The country study praises the sculpture for both its artistic expression and its technical quality, and says the Nok reached a level of material development not repeated in the region for nearly a thousand years.

Much remains uncertain. Most sculptures survive only as fragments, many sites have been looted, and the origin of the Nok people is unknown; Wikipedia reports that researchers think a northern, Sahelian homeland most likely. It also notes suggestions that later West African art, including the sculpture of Ife and Benin, may continue the Nok terracotta tradition.

The country study calls the first millennium AD, after the Nok, a "silent millennium" about which little is known, apart from iron smelting on Dala Hill in Kano around 600–700 AD.""",

 "states": """From around the end of the first millennium AD, much of what is now Nigeria came to be organised into states and kingdoms, many of which modern peoples trace their history to. The Library of Congress country study names among the early states the Yoruba kingdoms, the Edo kingdom of Benin, the Hausa cities, Nupe and Kanem-Borno, and notes that other states probably existed whose age cannot be dated from oral tradition alone.

In the south-west, Yoruba village compounds began to merge into city-states from about the eleventh century, with Ife as their religious centre and source of royal legitimacy; Ife is famous for its sculpture. In the fifteenth century Oyo and Benin overtook Ife as political and economic powers. Oyo, in the savanna, drew its strength from cavalry; Benin, ruled by an oba advised by hereditary chiefs, built a centralised state, was in contact with Portugal by the late fifteenth century, and at its height in the sixteenth and seventeenth centuries reached into parts of Yorubaland and the western Igbo area.

In the south-east, most scholars long described Igbo society as "stateless", organised in self-governing village groups. The country study points out the limits of that view: the bronzes of Igbo-Ukwu show a rich material culture in the heart of Igboland about the eighth century AD (Wikipedia gives the ninth), and the Nri kingdom, remembered as the cradle of Igbo culture, appears to have flourished before the seventeenth century.

In the north, trade across the Sahara shaped state formation. Kanem, east of Lake Chad, had become an empire by the thirteenth century; its rulers accepted Islam in the eleventh century, and Borno, its western province, became independent in the late fourteenth century, where the Kanuri emerged as a people. By the eleventh century some Hausa states, such as Kano, Katsina and Gobir, were walled trading towns; Hausa tradition traces their rulers to a founding hero, Bayajidda (spelt Bayinjida in the country study), who became king of Daura. Islam spread along the caravan routes, and Fulani herders entered Hausaland from the thirteenth century.

In the sixteenth century much of the north paid tribute to Songhai or to Borno, which reached its height under Mai Idris Aloma (about 1569–1600) and dominated the region for two centuries until drought and rivalry weakened it in the eighteenth century.""",

 "slavetrade": """The era of the Atlantic slave trade began with the arrival of Portuguese ships on the coast in the late fifteenth century and lasted until the trade was suppressed in the nineteenth century. According to the Library of Congress country study, Portuguese ships reached the Niger Delta by 1471, and in 1481 envoys of the king of Portugal visited the court of the oba of Benin.

Benin traded pepper, ivory and, at first, slaves for coral beads, Indian cloth, European goods and manillas (brass bracelets used as currency), and used firearms bought from the Portuguese to strengthen its position. It later placed an embargo on the export of slaves, and the country study describes it as unique among Nigerian states in refusing to take part in the transatlantic trade, although it kept slaves in its own economy.

Elsewhere the trade grew enormously. The south-western coast and the neighbouring coast of the present Republic of Benin became known to Europeans as the "Slave Coast". After the Portuguese came Dutch, French, English and other traders, and Britain became the leading slaving power in the eighteenth century. The country study estimates that over the whole period more than 3.5 million enslaved people were shipped from Nigeria to the Americas, most of them Igbo and Yoruba, with many Hausa, Ibibio and others; in the nineteenth century perhaps 30 per cent of all those sent across the Atlantic came from Nigeria.

Two powers supplied most of the captives in the eighteenth century. Oyo, whose cavalry pushed south to the coast, grew with the trade and was shaken by struggles between kings and the Oyo Mesi council. In the south-east, the Aro, an Igbo clan whose oracle at Arochukwu was widely consulted, built a network of markets and alliances that fed the ports of Calabar, Bonny and Elem Kalabari, where Efik and Ijaw merchant communities grew into trading towns of 5,000 to 10,000 people.

Britain abolished its own slave trade in 1807 and then used treaties and naval force to suppress it on the coast. The trade nonetheless continued into the nineteenth century, fed by the Yoruba wars that followed Oyo's collapse in the 1820s; the Aro still exported captives through the 1830s.""",

 "sokoto": """The Sokoto Caliphate was the state created by the jihad of Usman dan Fodio, which began in Gobir in 1804 and transformed the whole of northern Nigeria. According to the Library of Congress country study, the background was a century of insecurity: rivalry among the Hausa states, the weakness of Borno, and severe droughts and famines in the eighteenth century, which brought many Fulani into Hausaland and increased tensions.

Usman dan Fodio, a Fulani scholar of the Qadiriyya brotherhood, attracted a following among clerics and among the Fulani. The study notes that the jihad's leaders were not all Fulani at first: the cleric whose actions started it, Abd as-Salam, was Hausa. By 1808 the Hausa states had been conquered, although their ruling families withdrew to new walled towns, among them Abuja (the Zaria dynasty), Argungu (Kebbi) and Maradi in present-day Niger (Katsina).

The new state took its name from its capital, Sokoto, founded in 1809. It was a loose confederation of emirates under the caliph, the Commander of the Faithful. After Usman dan Fodio died in 1817 his son Muhammad Bello succeeded him; a twin capital at Gwandu, under Bello's uncle Abdullahi, oversaw the western emirates. By the middle of the nineteenth century there were about thirty emirates, including Kano, the wealthiest; Adamawa, the largest, founded by Fulani from Borno with its capital at Yola; and Ilorin, which joined in the 1830s after Oyo's cavalry revolted against the Oyo king.

Borno did not fall. The cleric al-Kanemi organised its resistance, drove the jihadists back, and in the end his family replaced the old Sayfawa dynasty. In the south-west, Oyo collapsed in the 1820s and its leaders founded new towns such as Ibadan.

At its height the caliphate stretched some 1,500 kilometres from Dori in present-day Burkina Faso to southern Adamawa in Cameroon. The country study calls it the largest African empire since the fall of Songhai in 1591, and says the jihad inspired Islamic states far beyond Nigeria. The caliphate's independence ended with the British conquest: in 1903 Lugard's West African Frontier Force took Kano and Sokoto, and the emirs were kept in office under indirect rule.""",

 "colonial": """Colonial Nigeria is the period of British rule, from the annexation of Lagos in 1861 to independence on 1 October 1960. According to the Library of Congress country study, Britain moved towards control of the lower Niger cautiously: after abolishing the slave trade it made treaties with local rulers, and Lagos became a colony in 1861, although a parliamentary report of 1865 still urged withdrawal from West Africa. Competition from France and Germany, and the Berlin Conference of 1885, changed that.

British control spread in stages. The Oil Rivers Protectorate (later the Niger Coast Protectorate) covered the Delta and the coast; force was used against Ijebu, Oyo and, in 1897, Benin, whose oba was exiled. The Royal Niger Company held the Niger and Benue until its charter was ended on 31 December 1899. From 1900 Frederick Lugard, as high commissioner of the Protectorate of Northern Nigeria, conquered the north; in 1903 his forces took Kano and Sokoto. He governed through the defeated emirs, a system known as indirect rule.

In 1914 Lugard merged the northern and southern protectorates into one Nigeria. The country study stresses that unification remained loose: the north and south were run differently, Christian missions were kept out of the Muslim north, and in the east appointed "warrant chiefs" with no traditional standing were strongly resisted. Nigerian soldiers fought in the First World War, and after 1920 part of the former German Cameroons was administered with Nigeria.

The colonial economy was built on exports such as palm produce, cocoa and rubber, on tin from the Jos Plateau, and on railways and ports. Nationalist politics grew from the 1920s: the 1922 constitution allowed a few elected members on the Legislative Council, and later parties included the NCNC (1944), the Action Group (1951) and the Northern People's Congress. Three constitutions between 1946 and 1954 moved the country towards self-government; the 1954 constitution established the federal principle, and the Western and Eastern regions became self-governing in 1957. Oil was first found in commercial quantity at Oloibiri in the Niger Delta in 1956 and exported from 1958.

After conferences at Lancaster House in London in 1957 and 1958, and elections in December 1959, Nigeria became independent on 1 October 1960.""",

 "first": """The First Republic is the name generally given to Nigeria's first period of independent civilian government, from independence on 1 October 1960 to the military coup of 15 January 1966 (Wikipedia). Strictly, Nigeria was a dominion with the British monarch as head of state until 1963, when it became a republic within the Commonwealth; the parliamentary, Westminster-style system was kept, and Nnamdi Azikiwe, the governor-general, became the first president, a largely ceremonial office. Abubakar Tafawa Balewa was prime minister.

The federation was made up of large regions, each dominated by one party: the Northern People's Congress (NPC) in the north, the NCNC in the east and the Action Group in the west. According to the Library of Congress country study, the federal government was an NPC–NCNC coalition, although the two parties were very different, and the Action Group formed the opposition.

The country study traces how stability broke down. In 1962 the Action Group split between Obafemi Awolowo and Samuel Akintola, the premier of the Western Region; rioting followed, the federal government declared a state of emergency in the region, and Awolowo and others were later convicted of treason. A Midwestern Region was carved out of the Western Region, its creation confirmed by plebiscite in 1963. Censuses in 1962 and 1963 were bitterly disputed, because population decided each region's share of seats. Riots broke out in Tivland in 1960 and 1964. The federal election of December 1964 was boycotted in parts of the country, and in the six months after the Western Region election of November 1965 an estimated 2,000 people died in violence in that region.

On 15 January 1966 a group of army majors attempted to seize power. Wikipedia records that Prime Minister Balewa, Ahmadu Bello (premier of the north and Sardauna of Sokoto), Samuel Akintola (premier of the west) and the finance minister Festus Okotie-Eboh were killed. The army commander, Major General Johnson Aguiyi-Ironsi, took control and suspended the constitution, ending the First Republic.""",

 "military1": """The years from 1966 to 1979 were a period of military government that included the Nigerian Civil War. After the coup of January 1966, Major General Johnson Aguiyi-Ironsi formed a Federal Military Government, suspended the constitution and banned political parties. According to the Library of Congress country study, his decree moving towards a unitary state alarmed many northerners, and in July 1966 northern officers staged a counter-coup in which Ironsi and other Igbo officers were killed. Lieutenant Colonel Yakubu Gowon became head of state and restored federalism. Killings of Igbo in the north followed, and many fled to the Eastern Region.

Talks at Aburi, Ghana, in January 1967 failed to settle the crisis. As the crisis deepened, Gowon proclaimed a state of emergency and announced the division of the country into twelve states, and on 30 May the Eastern Region's military governor, Lieutenant Colonel Chukwuemeka Odumegwu Ojukwu, proclaimed the Republic of Biafra. War began on 6 July 1967 (Wikipedia). Federal forces gradually surrounded Biafra; Owerri fell on 6 January 1970, Ojukwu left the country, and Biafra's remaining leaders surrendered in January 1970 (Wikipedia gives 15 January as the end of the war).

Estimates of the dead differ. The country study gives between 1 million and 3 million deaths in the former Eastern Region from fighting, disease and starvation during the thirty-month war; Wikipedia gives about 100,000 military deaths and between 500,000 and 2 million civilian deaths from starvation, largely caused by the blockade of Biafra.

After the war, oil replaced cocoa, groundnuts and palm produce as the main export; Nigeria joined OPEC in 1971, and the rise in oil prices in 1974 brought a sudden flood of revenue. In 1975 Nigeria helped found the Economic Community of West African States (ECOWAS). On 29 July 1975, while Gowon was at an OAU summit in Kampala, he was deposed in a bloodless coup, and the armed forces chose Brigadier Murtala Muhammad to succeed him. Murtala set a timetable to return to civilian rule and in 1976 created nineteen states. Murtala was assassinated during an unsuccessful coup in February 1976; his deputy, Lieutenant General Olusegun Obasanjo, carried the programme through. A new constitution, modelled on that of the United States, was adopted, elections were held in 1979, and power was handed to an elected civilian government on 1 October 1979.""",

 "second": """The Second Republic was Nigeria's second period of civilian government, from 1 October 1979 to the military coup of 31 December 1983. According to the Library of Congress country study, it was born amid great expectations, with high oil prices and rising revenue, under a new presidential constitution modelled on that of the United States.

Five parties contested the 1979 elections, several of them continuing the parties of the First Republic. The National Party of Nigeria (NPN) inherited the mantle of the Northern People's Congress; the UPN, led by Obafemi Awolowo, succeeded the Action Group; the Nigerian People's Party (NPP), led by Nnamdi Azikiwe, succeeded the NCNC; the People's Redemption Party, led by Aminu Kano, succeeded the Northern Elements Progressive Union; and the Great Nigerian People's Party, led by Waziri Ibrahim of Borno, broke away from the NPP. Shehu Shagari of the NPN won the presidency in a close and controversial vote. Lacking a majority in either house of the National Assembly, the NPN governed at first in a shaky coalition with the NPP.

The country study describes how the republic was undermined. The oil boom ended in mid-1981, just as expectations were highest; state governments ran up large debts, and teachers went on strike because they had not been paid. Religious riots linked to the Maitatsine movement broke out in Kano in 1980 and in Kaduna and Maiduguri in 1982. In January and February 1983 the government expelled an estimated 2 million foreign workers, about half of them from Ghana. Large projects continued, including the Ajaokuta steel works and a steel plant at Aladja near Warri.

Shagari was re-elected in 1983 in an election widely regarded as fraudulent. On 31 December 1983 the military seized power again, led by Major General Muhammadu Buhari; the country study says the main reason was that there was virtually no confidence left in the civilian government, with the economy in chaos and corruption out of control.""",

 "military2": """The years from 1983 to 1999 were a second long period of military rule, interrupted by an aborted transition to a Third Republic. On 31 December 1983 the army overthrew the Second Republic and Major General Muhammadu Buhari became head of state. According to the Library of Congress country study, his government set up tribunals against corruption, cut spending and in 1984 launched a "War Against Indiscipline", while harassing journalists and critics; negotiations with the IMF over Nigeria's debt failed.

In 1985 Buhari was overthrown by General Ibrahim Babangida, who adopted an economic recovery programme, promised a return to civilian rule and set up a long transition. Wikipedia records that Babangida's government survived coup attempts, executed those convicted, and in 1989 created two parties, the National Republican Convention and the Social Democratic Party, the only ones allowed to register. In December 1991 Abuja became the capital in place of Lagos.

The presidential election of 12 June 1993, which most observers considered the fairest in Nigeria's history, was won according to early returns by M. K. O. Abiola. On 23 June Babangida annulled it, and more than 100 people were killed in the riots that followed. Babangida handed power in August 1993 to an interim government under Ernest Shonekan, which was overthrown later in 1993 by General Sani Abacha. Wikipedia notes that this was, as of 2026, the last military coup in Nigeria.

Abacha's rule was marked by repression. In 1995 the writer and activist Ken Saro-Wiwa and eight others, the "Ogoni Nine", were executed after a trial that caused international protest. Abiola was imprisoned and died in custody; Olusegun Obasanjo was jailed on a charge of plotting a coup. Large sums of public money were diverted, later known as the "Abacha loot".

Abacha died on 8 June 1998. His successor, General Abdulsalami Abubakar, released political prisoners and set up the Independent National Electoral Commission, which held elections between December 1998 and February 1999. Obasanjo won the presidency, and military rule ended with his inauguration on 29 May 1999.""",

 "fourth": """The Fourth Republic is Nigeria's present system of government, in place since 29 May 1999, when the constitution of that year came into force and Olusegun Obasanjo was sworn in as president (Wikipedia). It followed the transition organised by General Abdulsalami Abubakar after the death of General Sani Abacha in 1998. Like the Second Republic's constitution, the 1999 constitution sets up a presidential system closer to that of the United States than to the Westminster model, with a National Assembly of two houses: a 109-member Senate and a 360-member House of Representatives.

Wikipedia lists the presidents of the Fourth Republic. Obasanjo of the People's Democratic Party (PDP) served two terms, from 1999 to 2007. Umaru Musa Yar'Adua of the PDP won the election of April 2007, which international observers strongly criticised. Yar'Adua died on 5 May 2010, and the vice-president, Goodluck Jonathan, became president; Jonathan won the 2011 election, regarded as freer and fairer than earlier ones. In the election of 28 March 2015 Muhammadu Buhari of the All Progressives Congress (APC) defeated Jonathan, ending sixteen years of PDP rule; Buhari, sworn in on 29 May 2015, was the first opposition candidate to win a presidential election since independence. He was re-elected in 2019. Bola Tinubu of the APC won the presidential election of February 2023, which the opposition accused of fraud, and was sworn in on 29 May 2023.

Wikipedia's history of Nigeria records some of its major challenges: communal violence, conflict in the oil-producing Niger Delta, a dispute with Cameroon over the Bakassi Peninsula, and the insurgency of Boko Haram, which in 2014 kidnapped some 200 schoolgirls at Chibok. It also records large infrastructure projects, such as the Lagos–Ibadan standard-gauge railway (operating from 2021) and the Second Niger Bridge (completed in 2022).

This period is current: its account will be extended as events happen.""",
}

REC = [
    ("prehistory", dict(name="Prehistoric Nigeria (Stone Age)", slug="prehistoric-nigeria", scope="national", start_year=None, end_year=-1500, date_precision="circa"),
     ["LEARLY", "WIHO", "WHIST"], "reported"),
    ("nok", dict(name="The Nok Culture", slug="nok-culture", scope="national", start_year=-1500, end_year=200, date_precision="circa"),
     ["LEARLY", "WNOK", "WHIST"], "reported"),
    ("states", dict(name="Early States and Kingdoms (c. 900–1800)", slug="early-states-and-kingdoms", scope="national", start_year=900, end_year=1800, date_precision="circa"),
     ["LSTATES", "LSAV", "WHIST"], "well_documented"),
    ("slavetrade", dict(name="The Era of the Atlantic Slave Trade (c. 1470–1860)", slug="atlantic-slave-trade-era", scope="national", start_year=1471, end_year=1861, date_precision="circa"),
     ["LSLAVE", "LCOL", "WHIST"], "well_documented"),
    ("sokoto", dict(name="The Sokoto Caliphate (1804–1903)", slug="sokoto-caliphate-period", scope="national", start_year=1804, end_year=1903, date_precision="year"),
     ["LSOK", "LSAV", "LLUG"], "well_documented"),
    ("colonial", dict(name="Colonial Nigeria (1861–1960)", slug="colonial-nigeria", scope="national", start_year=1861, end_year=1960, date_precision="year"),
     ["LCOL", "LEXT", "LLUG", "LUNI", "LNAT"], "well_documented"),
    ("first", dict(name="The First Republic (1960–1966)", slug="first-republic", scope="national", start_year=1960, end_year=1966, date_precision="year"),
     ["WFIRST", "LIND", "LCRI"], "well_documented"),
    ("military1", dict(name="Military Rule and the Civil War (1966–1979)", slug="military-rule-and-civil-war", scope="national", start_year=1966, end_year=1979, date_precision="year"),
     ["LCRI", "LWAR", "LGOW", "LMUR", "LOBA", "WWAR"], "well_documented"),
    ("second", dict(name="The Second Republic (1979–1983)", slug="second-republic", scope="national", start_year=1979, end_year=1983, date_precision="year"),
     ["LSEC", "LMIL"], "well_documented"),
    ("military2", dict(name="Military Rule (1983–1999)", slug="military-rule-1983-1999", scope="national", start_year=1983, end_year=1999, date_precision="year"),
     ["LMIL", "WHIST"], "well_documented"),
    ("fourth", dict(name="The Fourth Republic (1999–present)", slug="fourth-republic", scope="national", start_year=1999, end_year=None, date_precision="year"),
     ["WFOURTH", "WHIST"], "well_documented"),
]

RECORDS = []
for key, fields, srcs, lvl in REC:
    t = TEXT[key]
    first_para = t.split("\n\n")[0]
    summary = first_para.split(". ")[0].rstrip(".") + "."
    if len(summary) > 480:
        summary = summary[:477].rsplit(" ", 1)[0] + "…"
    RECORDS.append(dict(key=key, table="historical_periods", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(fields, summary=summary, description=t), srcs=[(s, fields["name"]) for s in srcs]))

GAPS = [
    ("National periods: older periodisations", "Nigerian historians use other schemes (for example pre-colonial / colonial / post-colonial, or periods named after particular kingdoms). These eleven periods follow the two sources read; a Nigerian university or NCMM periodisation should be checked."),
    ("National periods: prehistory and Nok dates", "Dates for the Iho Eleru skull (c. 10,000 vs c. 13,000 years) and for the Nok culture (4th century BC – 2nd century AD vs c. 1500 – 1 BCE) differ between the 1990 country study and current Wikipedia; a recent archaeological source (e.g. the Frankfurt Nok project's publications) should settle them."),
    ("National periods: civil-war deaths", "Death estimates differ widely (1–3 million in the country study; about 100,000 military and 0.5–2 million civilian in Wikipedia). Both are given; neither is preferred."),
    ("National periods: 1990 onwards", "After 1990 the account rests on Wikipedia only; a second independent source is needed for the Third Republic, the Abacha years and the Fourth Republic."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=[], gaps=GAPS, updates=[], relations=[], statistics=[],
                scope="National historical periods: eleven periods from prehistory to the Fourth Republic, from the Library of Congress country study and Wikipedia.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 135 — National: Historical Periods", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **11 historical periods** for the empty *Historical Periods* section of /nigeria, from prehistory to the Fourth Republic.",
         "- Sources: the Library of Congress *Nigeria: A Country Study* (text as of December 1990) for everything up to 1990, and Wikipedia; texts saved in `database/research/data/`.",
         "- Where the two sources disagree (Iho Eleru and Nok dates, civil-war deaths) both figures are given.", ""]
    for key, fields, srcs, lvl in REC:
        L += [f"## {fields['name']} ({words(TEXT[key])} words; {lvl}; sources: {', '.join(srcs)})", ""] + [f"> {p}" if p else ">" for p in TEXT[key].split("\n")] + [""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_135_national_periods.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_135_national_periods_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} sources={len(SOURCES)}")
    for key, fields, srcs, lvl in REC:
        print(f"  {fields['slug']}: {words(TEXT[key])} words")
