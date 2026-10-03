"""
Research batch 137 — National: People. Researched 2026-10-03 (owner: fill the empty People / Events / Periods sections of
/nigeria). Sixteen figures of Nigerian history, from the sixteenth century to the 1990s, chosen across regions, eras and
fields. Only people who are no longer living are included in this first batch.

Each person's facts come from their own Wikipedia article (saved in data/wiki_<name>_2026-10-03.txt) and, where it describes
them, the Library of Congress 'Nigeria: A Country Study' (data/loc_cs_nigeria_N.txt). Dates are as those sources give them;
where a tradition is uncertain (Amina) the text says so.

National people are historical_figures rows with a national_slug, tagged to the 'nigeria' collection only (importer), so they
never appear in the Tiv collection. The table's `state` column defaults to 'Benue' (a Tiv-collection default), so every
record here sets it explicitly (NULL where no single modern state applies).
"""
import json, re, sys
import batch_135_national_periods as P

ACCESSED = "2026-10-03"
WP = lambda t, n: P.WS(t, n)
SOURCES = {k: P.SOURCES[k] for k in ["LSOK", "LSAV", "LEXT", "LNAT", "LIND", "LCRI", "LWAR", "LMUR", "LSEC", "WHIST"]}
SOURCES["LMURG"] = P.LOC(26, "The Regime of Murtala Muhammad", "Gowon deposed 29 July 1975; Murtala Muhammad, a Hausa trained at Sandhurst, chosen to succeed him.")
for key, title, note in [
    ("WUSM", "Usman dan Fodio", "15 December 1754 – 20 April 1817; Fulani scholar, born in Gobir; founder and first caliph of the Sokoto Caliphate; wrote more than a hundred books."),
    ("WKAN", "Muhammad al-Amin al-Kanemi", "1776 – 8 June 1837; saved Kanem–Bornu during the Fula jihads; title shehu; seat at Kukawa."),
    ("WIDR", "Idris Alooma", "Mai of Kanem–Bornu c. 1570–1603; chronicled by Ahmad bin Fartuwa; legal reforms; expansion over Hausaland, Aïr and Bilma."),
    ("WAMI", "Amina of Zazzau", "Hausa figure of Zazzau (Zaria), born c. 1533, died 1610; traditions differ on whether she was queen or princess."),
    ("WOVO", "Ovonramwen", "Born Idugbowa; 1857 – 14 January 1914; 35th Oba of Benin; deposed and exiled to Calabar after 1897."),
    ("WMAC", "Herbert Macaulay", "14 November 1864 – 7 May 1946; born in Lagos; grandson of Bishop Samuel Ajayi Crowther; regarded by many as the founder of Nigerian nationalism."),
    ("WAZI", "Nnamdi Azikiwe", "16 November 1904 – 11 May 1996; born in Zungeru; governor-general 1960–63 and first president 1963–66."),
    ("WAWO", "Obafemi Awolowo", "6 March 1909 – 9 May 1987; born in Ikenne; premier of the Western Region 1952–1959; leader of the opposition 1959–1963."),
    ("WBEL", "Ahmadu Bello", "12 June 1910 – 15 January 1966; born in Rabah; premier of the Northern Region 1954–1966; leader of the NPC."),
    ("WBAL", "Abubakar Tafawa Balewa", "December 1912 – 15 January 1966; born in Tafawa Balewa, Bauchi; teacher; first and only prime minister of Nigeria."),
    ("WFRK", "Funmilayo Ransome-Kuti", "25 October 1900 – 13 April 1978; born in Abeokuta; educator and women's rights leader; Abeokuta Women's Union."),
    ("WMUR", "Murtala Muhammed", "8 November 1938 – 13 February 1976; born in Kano; head of state 29 July 1975 – 13 February 1976."),
    ("WOJU", "Chukwuemeka Odumegwu Ojukwu", "4 November 1933 – 26 November 2011; born in Zungeru; military governor of the Eastern Region; leader of Biafra 1967–1970."),
    ("WABI", "Moshood Abiola", "24 August 1937 – 7 July 1998; born in Abeokuta; businessman and publisher; won the annulled election of 12 June 1993; died in detention."),
    ("WKSW", "Ken Saro-Wiwa", "10 October 1941 – 10 November 1995; born in Bori; writer; president of MOSOP; executed in 1995."),
    ("WACH", "Chinua Achebe", "16 November 1930 – 21 March 2013; born in Ogidi; novelist; Things Fall Apart (1958)."),
]:
    SOURCES[key] = WP(title, note)

# key, english_name, title, national_slug, category, period, gender, born, place, died, occupation, state, sources, level, prose
PEOPLE = [
 ("amina", "Amina of Zazzau", "Queen (in palace tradition)", "amina-of-zazzau", "Women of Influence", "Pre-Colonial Era", "female",
  "c. 1533", "Zazzau (Zaria)", "1610", "Ruler and military leader of Zazzau (in tradition)", "Kaduna", ["WAMI"], "reported", dict(
  biography="""Amina, also called Aminatu, is a Hausa historical figure of the city-state of Zazzau, today Zaria in Kaduna State. According to Wikipedia, she was born around 1533 and died in 1610, the daughter of Nikatau, the 22nd ruler of Zazzau, and Queen Bakwa Turunku.

Traditions about her differ. Palace tradition holds that she reigned as queen (sarauniya), while other traditions say she was a princess (gimbiya); her name does not appear on the regnal lists in the Chronicle of Abuja or in the two lists published by E. J. Arnett. Some traditions say she became queen after the death of her brother Karama in 1576 and ruled the territories she conquered.""",
  legacy="""She is still celebrated in Hausa praise songs as "Amina daughter of Nikatau, a woman as capable as a man", remembered as a leader of men in war (Wikipedia).""")),

 ("idris", "Idris Alooma", "Mai of Kanem–Bornu", "idris-alooma", "Traditional Leadership", "Pre-Colonial Era", "male",
  None, None, None, "Mai (ruler) of the Kanem–Bornu Empire", None, ["WIDR", "LSAV"], "well_documented", dict(
  biography="""Idris Alooma, also called Idris Amsami, was mai (ruler) of the Kanem–Bornu Empire in the late sixteenth and early seventeenth centuries. Wikipedia dates his reign to approximately 1570–1603; the Library of Congress country study gives about 1569–1600. His reign is known mainly from the chronicle of his chief imam, Ahmad bin Fartuwa.

According to Wikipedia, Idris ended Bornu's long conflict with the Bilala of Kanem, introduced legal reforms based on Islamic law with qadi courts independent of the executive, and extended Bornu's influence over most of Hausaland, the Tuareg of Aïr and the Tebu of Bilma, giving it control of central Saharan trade routes. The country study says that Kanem was reconquered during his reign and that Kano and Katsina became tributaries.""",
  historical_significance="""Both sources regard his reign as the high point of Bornu: the country study says that after the fall of Songhai in 1591 Bornu dominated the political history of northern Nigeria for two centuries.""")),

 ("usman", "Usman dan Fodio", "Shehu; Commander of the Faithful", "usman-dan-fodio", "Religious Leaders", "Pre-Colonial Era", "male",
  "15 December 1754", "Gobir", "20 April 1817", "Islamic scholar, teacher, writer and leader of the Sokoto jihad", None, ["WUSM", "LSOK"], "well_documented", dict(
  biography="""Shehu Usman dan Fodio (15 December 1754 – 20 April 1817) was a Fulani Islamic scholar, teacher and writer who founded the Sokoto Caliphate and was its first caliph. According to Wikipedia he was born in Gobir, studied the Quran at Degel, and was first taught by his mother, Hauwa; he wrote more than a hundred books on religion, government, culture and society, and criticised the ruling Muslim elites of the Hausa states for what he saw as their greed and their violations of Islamic law.

The Library of Congress country study describes how, as a member of the Qadiriyya brotherhood, he drew a following among clerics and among the Fulani, and how the jihad he led began in Gobir in 1804. By 1808 the Hausa states had been conquered. Sokoto, founded in 1809, became the capital of the new caliphate. Wikipedia notes that he passed actual leadership to his son, Muhammad Bello, who succeeded him on his death in 1817 (country study).""",
  legacy="""The country study calls the Sokoto Caliphate the largest empire in Africa since the fall of Songhai, and says the jihad inspired Islamic states far beyond Nigeria. Wikipedia notes that he encouraged literacy and scholarship for women as well as men.""")),

 ("kanemi", "Muhammad al-Amin al-Kanemi", "Shehu of Borno", "muhammad-al-amin-al-kanemi", "Religious Leaders", "Pre-Colonial Era", "male",
  "1776", None, "8 June 1837", "Islamic scholar, military and political leader of Borno", "Borno", ["WKAN", "LSOK"], "well_documented", dict(
  biography="""Muhammad al-Amin al-Kanemi (1776 – 8 June 1837), also known as Laminu, was an Islamic scholar and teacher and a religious, military and political leader who saved the Kanem–Bornu Empire during the jihads of the early nineteenth century (Wikipedia). According to the Library of Congress country study, when the Sokoto jihad reached Borno its mai was overthrown and the capital, Birni Gazargamu, destroyed, but al-Kanemi organised a strong resistance that forced the jihadists to retreat west and south.

Wikipedia records that the mai, Dunama IX Lefiami, rewarded him with unprecedented power, which allowed al-Kanemi to become the effective ruler of the empire; he took the title shehu ("sheikh") and made Kukawa his seat.""",
  legacy="""His family replaced the centuries-old Sayfawa dynasty (country study); under his son Umar the mais were deposed and the al-Kanemi line became Borno's monarchs in name as well as in fact (Wikipedia).""")),

 ("ovonramwen", "Ovonramwen", "Oba of Benin", "ovonramwen", "Traditional Leadership", "Pre-Colonial Era", "male",
  "1857", None, "14 January 1914", "Oba (king) of Benin", "Edo", ["WOVO", "LEXT"], "well_documented", dict(
  biography="""Ovonramwen (born Idugbowa; 1857 – 14 January 1914), recorded as "Overami" by the British, was the thirty-fifth Oba of Benin and the last ruler of the kingdom before it lost its independence (Wikipedia). He succeeded his father, Adolo, in 1888 or 1889, taking the name Ovonramwen n'Ogbaisi, "the rising sun that spreads over all". His reign began with rivalry among palace chiefs and efforts to keep Benin's authority as British influence spread across the western Niger Delta.

In 1892 the British vice-consul Henry Gallwey negotiated a treaty which, as the British read it, limited Benin's independence; Ovonramwen did not personally touch the pen, and continued to enforce Benin's trade restrictions and royal monopolies. In January 1897 the acting consul-general, James Phillips, set out for Benin although the oba had asked him to postpone his visit; a group of Benin chiefs, despite objections attributed to Ovonramwen, attacked the party and killed most of its European members (Wikipedia).""",
  career="""The British expedition that followed took Benin City in 1897 and destroyed the oba's palace (country study). Ovonramwen was deposed, sentenced to exile and sent to Calabar, where he remained until his death in January 1914; his eldest son then became oba as Eweka II (Wikipedia).""")),

 ("macaulay", "Herbert Macaulay", None, "herbert-macaulay", "Political Leaders", "Colonial Era", "male",
  "14 November 1864", "Lagos", "7 May 1946", "Nationalist politician, surveyor, engineer and journalist", "Lagos", ["WMAC", "LNAT"], "well_documented", dict(
  biography="""Herbert Macaulay (14 November 1864 – 7 May 1946) was a Nigerian nationalist, politician, surveyor, engineer, journalist and musician, considered by many to be the founder of Nigerian nationalism (Wikipedia). Born on Broad Street, Lagos, he was a grandson of Samuel Ajayi Crowther, the first African bishop of the Niger territory. From 1891 to 1894 he studied civil engineering in Plymouth, England.

According to the Library of Congress country study, he was the principal figure in the political activity that followed the 1922 constitution, which allowed a few elected members on the Legislative Council: he led the Nigerian National Democratic Party, which dominated elections in Lagos from its founding in 1922 until 1938, and used his newspaper, the Lagos Daily News, to arouse political awareness. Wikipedia records that in his political work he also relied on the Lagos Daily News, on the Lagos Market Women Association led by his ally Alimotu Pelewura, and on the House of Docemo. In 1944, when the NCNC was founded as Nigeria's first party with nationwide appeal, "the aged Macaulay" was elected its president, with Nnamdi Azikiwe as secretary-general (country study).""")),

 ("frk", "Funmilayo Ransome-Kuti", "Chief", "funmilayo-ransome-kuti", "Women of Influence", "Colonial Era", "female",
  "25 October 1900", "Abeokuta", "13 April 1978", "Educator, political organiser and women's rights campaigner", "Ogun", ["WFRK"], "well_documented", dict(
  biography="""Funmilayo Ransome-Kuti (25 October 1900 – 13 April 1978) was a Nigerian educator, political organiser and women's rights campaigner (Wikipedia). Born in Abeokuta, she was the first female student at the Abeokuta Grammar School. As a teacher she organised some of the earliest pre-school classes in the country and literacy classes for poorer women.

In the 1940s she founded the Abeokuta Women's Union, which demanded better representation of women in local government and an end to unfair taxes on market women; marches and protests of up to 10,000 women led by her forced the Alake of Egbaland to abdicate temporarily in 1949. She took part in the independence movement, joined delegations on Nigeria's constitution, led the creation of the Nigerian Women's Union and the Federation of Nigerian Women's Societies, and campaigned for women's right to vote.""",
  legacy="""She received the Lenin Peace Prize and membership of the Order of the Niger. Her children included the musician Fela Kuti, the doctor and activist Beko Ransome-Kuti and the health minister Olikoye Ransome-Kuti. She died at 77 after being wounded in a military raid on family property (Wikipedia).""")),

 ("azikiwe", "Nnamdi Azikiwe", "Zik of Africa", "nnamdi-azikiwe", "Political Leaders", "First Republic", "male",
  "16 November 1904", "Zungeru", "11 May 1996", "Journalist and politician; first president of Nigeria", "Anambra", ["WAZI", "LNAT", "LIND", "LSEC"], "well_documented", dict(
  biography="""Nnamdi Azikiwe (16 November 1904 – 11 May 1996), known as "Zik of Africa", was the first Nigerian governor-general of Nigeria, from 1960 to 1963, and the first president of the country, from 1963 to 1966 (Wikipedia). Born in Zungeru, in present-day Niger State, to Igbo parents from Onitsha, he grew up speaking Hausa, Igbo and Yoruba. He studied in the United States at Storer College, Columbia University, the University of Pennsylvania and Howard University, and returned to Africa in 1934 to work as a journalist in the Gold Coast.

The Library of Congress country study describes his central role in the nationalist movement: he encouraged the conference that founded the NCNC in 1944, became its secretary-general under Herbert Macaulay, and led the party, in part through the string of newspapers he ran. When Nigeria became a republic in 1963, Azikiwe, who had been governor-general, became its first president (country study).""",
  legacy="""Wikipedia records that he is widely regarded as a father of Nigerian nationalism and one of the founding fathers of the country. In the Second Republic he led the Nigerian People's Party and stood for the presidency in 1979 (country study).""")),

 ("awolowo", "Obafemi Awolowo", "Chief", "obafemi-awolowo", "Political Leaders", "First Republic", "male",
  "6 March 1909", "Ikenne", "9 May 1987", "Lawyer, journalist and politician; premier of the Western Region", "Ogun", ["WAWO", "LNAT", "LIND"], "well_documented", dict(
  biography="""Obafemi Awolowo (6 March 1909 – 9 May 1987) was a Nigerian politician who was the first premier of the Western Region (Wikipedia). Born in the Remo town of Ikenne, in present-day Ogun State, he worked as a journalist, studied commerce in Nigeria and law at the University of London, and founded the Nigerian Tribune.

According to the Library of Congress country study, he led the Nigerian Produce Traders' Association and played a leading role in the Yoruba cultural movement Egbe Omo Oduduwa, and the Action Group, founded in 1951, was largely his creation. He was premier of the Western Region from 1952 to 1959 and leader of the opposition in the federal parliament from 1959 to 1963 (Wikipedia).

In 1962 the Action Group split between Awolowo and Samuel Akintola; Awolowo was later tried and convicted of treason and sentenced to ten years in prison (country study). He was released in 1966, after the July coup, and served as federal commissioner for finance and vice-chairman of the Federal Executive Council during the civil war (Wikipedia; country study). He was three times a leading candidate for the presidency.""")),

 ("bello", "Ahmadu Bello", "Sardauna of Sokoto", "ahmadu-bello", "Political Leaders", "First Republic", "male",
  "12 June 1910", "Rabah", "15 January 1966", "Politician; premier of the Northern Region", "Sokoto", ["WBEL", "LNAT", "LCRI"], "well_documented", dict(
  biography="""Sir Ahmadu Bello (12 June 1910 – 15 January 1966), known by his title Sardauna of Sokoto, was the first and only premier of the Northern Region, from 1954 until his death, and leader of the Northern People's Congress (Wikipedia). He was born in Rabah, where his father was district head, and was a member of the Sokoto Caliphate dynasty; he sought to become Sultan of Sokoto before entering politics.

The Library of Congress country study calls him the most powerful figure in the NPC, a controversial figure described by opponents as a "feudal" conservative, with a consuming interest in protecting northern social and political institutions from southern influence. Through the NPC's control of the north he dominated national affairs for more than a decade (Wikipedia).""",
  career="""He was killed in Kaduna in the coup of 15 January 1966, together with Prime Minister Balewa and the Western premier, Samuel Akintola (country study).""")),

 ("balewa", "Abubakar Tafawa Balewa", "Sir", "abubakar-tafawa-balewa", "Political Leaders", "First Republic", "male",
  "December 1912", "Tafawa Balewa, Bauchi", "15 January 1966", "Teacher and politician; prime minister of Nigeria", "Bauchi", ["WBAL", "LIND", "LCRI"], "well_documented", dict(
  biography="""Sir Abubakar Tafawa Balewa (December 1912 – 15 January 1966) was the first and only prime minister of Nigeria (Wikipedia). He was born in the village of Tafawa Balewa in the Lere district of Bauchi province, to a Gerawa father and a Fulani mother. He studied at a madrasa in Bauchi, at Bauchi Government Provincial School and at Katsina Higher College (now Barewa College) from 1928 to 1932, became a secondary-school teacher, and in 1944 headmaster of Bauchi middle school.

As prime minister from independence in 1960 he led a coalition of the Northern People's Congress and the NCNC (country study). He governed through the crises of the First Republic, including the Action Group split and the state of emergency in the Western Region in 1962. He was killed in Lagos in the coup of 15 January 1966 (country study).""")),

 ("murtala", "Murtala Muhammed", "General", "murtala-muhammed", "Warriors & Military Leaders", "Military Era", "male",
  "8 November 1938", "Kano", "13 February 1976", "Army officer; head of state of Nigeria", "Kano", ["WMUR", "LMURG", "LMUR"], "well_documented", dict(
  biography="""Murtala Muhammed (8 November 1938 – 13 February 1976) was a Nigerian army officer and the fourth head of state of Nigeria, from 29 July 1975 until his assassination on 13 February 1976 (Wikipedia). Born in Kano, he trained at the Royal Military Academy Sandhurst and served in the Congo. He played a leading part in the counter-coup of July 1966 and during the civil war commanded the federal army's Second Infantry Division, which, Wikipedia records, committed the Asaba massacre of civilians (Wikipedia; country study).

After Yakubu Gowon was deposed on 29 July 1975, the armed forces chose Murtala to succeed him. According to the Library of Congress country study, he committed the government to hand over power to an elected civilian government by October 1979, began the drafting of a new constitution, and reviewed the plan that gave Nigeria nineteen states in 1976. His policies quickly won broad popular support.""",
  legacy="""He was killed in an unsuccessful coup in February 1976, and the country went into deep mourning (country study). His deputy, Olusegun Obasanjo, completed the transition to civilian rule in 1979.""")),

 ("ojukwu", "Chukwuemeka Odumegwu Ojukwu", "Ikemba", "chukwuemeka-odumegwu-ojukwu", "Warriors & Military Leaders", "Military Era", "male",
  "4 November 1933", "Zungeru", "26 November 2011", "Army officer and politician; leader of Biafra", "Anambra", ["WOJU", "LWAR"], "well_documented", dict(
  biography="""Chukwuemeka Odumegwu Ojukwu (4 November 1933 – 26 November 2011), known as Ikemba, was a Nigerian army officer and politician who led the Republic of Biafra from 1967 to 1970 (Wikipedia). Born in Zungeru, the son of the Igbo businessman Louis Odumegwu Ojukwu, he was educated at King's College, Lagos, Epsom College in England and Lincoln College, Oxford, where he studied history. He served as an administrative officer before joining the army.

After the coup of January 1966, Johnson Aguiyi-Ironsi appointed him military governor of the Eastern Region. Following the killings of Igbo in other parts of Nigeria, and after the talks at Aburi failed, he proclaimed the independent Republic of Biafra on 30 May 1967, saying that the federal government could not protect the lives of easterners (country study; Wikipedia).""",
  career="""He led Biafra through the civil war. When Biafran resistance collapsed in January 1970 he left for Côte d'Ivoire, where he was given political asylum (country study; Wikipedia). He died in London in 2011 (Wikipedia).""")),

 ("abiola", "Moshood Abiola", "Chief", "moshood-abiola", "Business Leaders & Entrepreneurs", "Military Era", "male",
  "24 August 1937", "Abeokuta", "7 July 1998", "Businessman, publisher and politician", "Ogun", ["WABI", "WHIST"], "well_documented", dict(
  biography="""Moshood Kashimawo Olawale Abiola (24 August 1937 – 7 July 1998), known as M. K. O. Abiola, was a Nigerian businessman, publisher and politician (Wikipedia). He was born and educated in Abeokuta. He stood for president in the election of 12 June 1993, which most observers considered the fairest in Nigeria's history, and early returns showed him winning decisively; his support crossed regional and religious lines. The military president, Ibrahim Babangida, annulled the election on 23 June 1993 (Wikipedia).

In June 1994 Abiola declared himself the lawful president of Nigeria at Epetedo, Lagos, and was imprisoned. He died in detention on 7 July 1998, shortly after the death of General Sani Abacha, on the day he was due to be released (Wikipedia).""",
  legacy="""In 2018 he was posthumously awarded Nigeria's highest honour, Grand Commander of the Order of the Federal Republic, and Democracy Day was moved from 29 May to 12 June in his honour (Wikipedia).""")),

 ("saro", "Ken Saro-Wiwa", None, "ken-saro-wiwa", "Activists & Reformers", "Military Era", "male",
  "10 October 1941", "Bori", "10 November 1995", "Writer, television producer and environmental activist", "Rivers", ["WKSW", "WHIST"], "well_documented", dict(
  biography="""Kenule Beeson Saro-Wiwa (10 October 1941 – 10 November 1995) was a Nigerian writer, teacher, television producer and activist, a member of the Ogoni people of the Niger Delta (Wikipedia). Born in Bori, near Port Harcourt, he studied at Government College Umuahia and taught at the University of Nigeria, Nsukka. His novel Sozaboy (1985) tells of a village boy recruited into the army in the civil war, and his satirical television series Basi & Company drew an estimated audience of 30 million.

As spokesman and then president of the Movement for the Survival of the Ogoni People (MOSOP), he led a non-violent campaign against the environmental damage done to Ogoniland by oil companies, especially Royal Dutch Shell, and criticised the government for failing to enforce environmental regulations (Wikipedia).""",
  legacy="""He was tried by a special military tribunal on charges of masterminding the murder of Ogoni chiefs and was hanged in 1995 under the government of General Sani Abacha, together with eight others. His execution caused international outrage and, according to Wikipedia's article on him, led to Nigeria's suspension from the Commonwealth for more than three years.""")),

 ("achebe", "Chinua Achebe", None, "chinua-achebe", "Writers & Authors", "Post-Colonial Era", "male",
  "16 November 1930", "Ogidi", "21 March 2013", "Novelist, poet and critic", "Anambra", ["WACH"], "well_documented", dict(
  biography="""Chinua Achebe (16 November 1930 – 21 March 2013) was a Nigerian novelist, poet and critic regarded as a central figure of modern African literature (Wikipedia). Born in Ogidi and raised between Igbo tradition and colonial Christianity, he studied at what is now the University of Ibadan and worked for the Nigerian Broadcasting Service in Lagos.

His first novel, Things Fall Apart (1958), is, according to Wikipedia, the most widely studied, translated and read African novel; with No Longer at Ease (1960) and Arrow of God (1964) it forms his "African Trilogy". Later novels include A Man of the People (1966) and Anthills of the Savannah (1987). With the publisher Heinemann he began the African Writers Series, which launched the careers of writers such as Ngũgĩ wa Thiong'o and Flora Nwapa.""",
  career="""When Biafra broke away in 1967 he supported its independence and acted as an ambassador for its cause. He won the Man Booker International Prize in 2007, and from 2009 until his death was professor of African studies at Brown University in the United States (Wikipedia).""")),
]

RECORDS = []
for key, name, title, slug, cat, period, gender, born, place, died, occ, state, srcs, lvl, prose in PEOPLE:
    first = prose["biography"].split(". ")[0].rstrip(".") + "."
    summary = first if len(first) <= 480 else first[:477].rsplit(" ", 1)[0] + "…"
    fields = dict(english_name=name, title=title, national_slug=slug, category=cat, historical_period=period, gender=gender,
                  date_of_birth=born, place_of_birth=place, date_of_death=died, occupation=occ, state=state, country="Nigeria",
                  short_summary=summary)
    fields.update(prose)
    RECORDS.append(dict(key=key, table="historical_figures", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=fields, srcs=[(s, name) for s in srcs]))

GAPS = [
    ("National people: coverage", "Sixteen people only, all deceased. Not yet covered: Jaja of Opobo, Nana Olomu, Sultan Attahiru I, Aminu Kano, Margaret Ekpo, Sarduana-era and First Republic ministers, the military heads of state (Ironsi, Gowon, Obasanjo, Buhari, Babangida, Abacha, Abubakar), Shehu Shagari, Fela Kuti, Wole Soyinka and other living or recent figures. Living people need extra care and should be added only with strong sources."),
    ("National people: single-source records", "Achebe and Amina rest on one source each; Amina's dates and title are matters of tradition, recorded as such."),
    ("National people: images", "No portraits yet; add free-licence portraits through the image-refill process (media_assets) where they exist."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=[], gaps=GAPS, updates=[], relations=[], statistics=[],
                scope="National people: sixteen figures of Nigerian history from Amina of Zazzau to Chinua Achebe (deceased people only in this batch).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 137 — National: People", "",
         f"Researched {ACCESSED}. Created as drafts; published only after your approval.", "",
         "- **16 national figures**, tagged to the Nigeria collection only (never shown in the Tiv collection). Deceased people only in this first batch.", ""]
    for key, name, title, slug, cat, period, gender, born, place, died, occ, state, srcs, lvl, prose in PEOPLE:
        L += [f"## {name} ({born or '?'} – {died or '?'}; {cat}; {sum(words(v) for v in prose.values())} words; {', '.join(srcs)})", ""]
        for k, v in prose.items():
            L += [f"**{k}:**", ""] + [f"> {p}" if p else ">" for p in v.split("\n")] + [""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_137_national_people.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_137_national_people_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} sources={len(SOURCES)}")
    for key, name, *_rest, prose in PEOPLE:
        print(f"  {name}: {sum(words(v) for v in prose.values())} words")
