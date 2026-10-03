"""
Research batch 139 — National: People, part 2. Researched 2026-10-03 (owner: "extend people and events"). Fourteen more
figures of Nigerian history, all deceased (Muhammadu Buhari died 13 July 2025, per his Wikipedia article read today).
Same method as batch 137: each person's facts from their own Wikipedia article (saved in data/) and, where relevant, the
Library of Congress country study. Contested matters (Abacha's rule, Nana's trial) are attributed, not asserted.
"""
import json, re, sys
import batch_135_national_periods as P

ACCESSED = "2026-10-03"
SOURCES = {k: P.SOURCES[k] for k in ["LSEC", "LMIL", "LCRI", "LGOW", "WHIST", "WFOURTH"]}
for key, title, note in [
    ("WIDIA", "Idia", "Iyoba (Queen Mother) of Benin, late 15th – early 16th century; mother of Oba Esigie; Igala–Benin War 1515–1516; FESTAC 77 emblem."),
    ("WJAJA", "Jaja of Opobo", "c. 1821 – 1891; founder and first amanyanabo of Opobo (1869); palm-oil trade; exiled by the British; died 1891 on the way home."),
    ("WNANA", "Nana Olomu", "c. 1852 – 3 July 1916; Itsekiri merchant and Governor of the Benin River (1884); Ebrohimi bombarded 1894; deported; returned 1906."),
    ("WATT", "Muhammadu Attahiru I", "Twelfth Sultan of Sokoto, October 1902 – 15 March 1903; last independent sultan; killed at Burmi (Mbormi) 29 July 1903."),
    ("WIRO", "Johnson Aguiyi-Ironsi", "3 March 1924 – 29 July 1966; born in Ibeku, Umuahia; first military head of state, 16 January – 29 July 1966."),
    ("WAMK", "Aminu Kano", "9 August 1920 – 17 April 1983; born in Kano; founder of NEPU and leader of the PRP; Northern Teachers' Association (1948)."),
    ("WEKPO", "Margaret Ekpo", "27 July 1914 – 21 September 2006; born in Creek Town; women's rights leader in Aba; Eastern House of Assembly 1961."),
    ("WSAW", "Gambo Sawaba", "15 February 1933 – October 2001; women's rights activist; NEPU women's wing; imprisoned 16 times."),
    ("WSHA", "Shehu Shagari", "25 February 1925 – 28 December 2018; born in Shagari; president 1979–1983."),
    ("WABA2", "Sani Abacha", "20 September 1943 – 8 June 1998; born in Kano; military head of state 1993–1998."),
    ("WYAR", "Umaru Musa Yar'Adua", "16 August 1951 – 5 May 2010; born in Katsina; governor of Katsina 1999–2007; president 2007–2010."),
    ("WFELA", "Fela Kuti", "15 October 1938 – 2 August 1997; born in Abeokuta; musician, pioneer of Afrobeat; Kalakuta Republic."),
    ("WAKU", "Dora Akunyili", "14 July 1954 – 7 June 2014; born in Makurdi; Director-General of NAFDAC 2001–2008; Minister of Information 2008–2010."),
    ("WBUH", "Muhammadu Buhari", "17 December 1942 – 13 July 2025; born in Daura; military head of state 1983–1985; president 2015–2023."),
]:
    SOURCES[key] = P.WS(title, note)

PEOPLE = [
 ("idia", "Idia", "Iyoba (Queen Mother) of Benin", "idia-iyoba-of-benin", "Women of Influence", "Pre-Colonial Era", "female",
  None, "Ugieghudu, Benin kingdom (in tradition)", None, "Queen Mother (Iyoba) of the Kingdom of Benin", "Edo", ["WIDIA"], "well_documented", dict(
  biography="""Idia was a royal woman of the Kingdom of Benin and the first to hold the title of Iyoba, the Edo title of the mother of the reigning oba (Wikipedia). A wife of Oba Ozolua and the mother of Oba Esigie, she held ritual, military and political authority during the disputes over her son's succession and the early decades of his reign, in the first half of the sixteenth century. Her birth and death cannot be dated precisely; tradition says she came from Ugieghudu in the Isi district.

Edo accounts credit Esigie's victory over his half-brother Aruanran partly to Idia's counsel, forces, ritual protection and knowledge of medicine. An Edo proverb calls her the only woman who went to war, though traditions differ on whether she led troops herself. Her contingent took part in the Igala–Benin War of 1515–1516. Esigie created the office of Iyoba for her, with her own palace at Lower Uselu.""",
  legacy="""After her death Idia became a subject of Benin court art, on cast-metal memorial heads, carved ivory pendants and other royal objects. An ivory pendant identified with her became the emblem of FESTAC '77 in 1977 and has since been reproduced as a symbol of Nigerian and African cultural heritage (Wikipedia).""")),

 ("jaja", "Jaja of Opobo", "King (Amanyanabo) of Opobo", "jaja-of-opobo", "Traditional Leadership", "Pre-Colonial Era", "male",
  "c. 1821", None, "1891", "Merchant and king of Opobo", "Rivers", ["WJAJA"], "well_documented", dict(
  biography="""Jaja of Opobo (Jubo Jubogha; c. 1821–1891) was the founder and first king (amanyanabo) of the Opobo Kingdom, in present-day Rivers and Akwa Ibom states (Wikipedia). Of Igbo origin, he was brought to Bonny and ritually adopted into the Ijaw community. After his master's death he took charge of the trade of the Anna Pepple House, one of Bonny's merchant houses, and led it until a war with the Manilla Pepple House, led by Oko Jumbo, led him to break away and found Opobo, east of Bonny, in 1869.

Opobo became a leading centre of the palm-oil trade. Jaja barred European and African middlemen from the interior markets, and by 1870 was selling eight thousand tons of palm oil directly to the British.""",
  career="""He was later abducted aboard a British vessel, tried in Accra and exiled, first to London and then to Saint Vincent and Barbados in the West Indies. In 1891 he was allowed to return to Opobo but died on the way (Wikipedia).""")),

 ("nana", "Nana Olomu", "Governor of the Benin River", "nana-olomu", "Freedom Fighters & Resistance Leaders", "Pre-Colonial Era", "male",
  "c. 1852", None, "3 July 1916", "Itsekiri palm-oil merchant and political leader", "Delta", ["WNANA"], "well_documented", dict(
  biography="""Nana Olomu (born Eriomala; c. 1852 – 3 July 1916) was an Itsekiri palm-oil merchant and political leader in the western Niger Delta (Wikipedia). He inherited the large trading organisation of his father, Olomu, and in 1884 was chosen by Itsekiri elders as Governor of the Benin River, an office that combined commercial and political authority while the Warri kingship was vacant. By the early 1890s his organisation controlled much of the trade between European firms on the lower Benin River and the Urhobo palm-oil producers inland, through commercial alliances and credit as well as armed canoes, agents and enslaved labour.""",
  career="""British officials at first endorsed his authority but, after setting up a more direct administration in 1891, treated his office as obsolete and sought to open the hinterland to direct trade. The dispute ended with the British blockade and bombardment of his fortified town of Ebrohimi in 1894. Nana escaped, surrendered in Lagos, and was convicted on four charges in a consular court without legal representation. He was deported, first within the Niger Coast Protectorate and then to the Gold Coast, and returned home in 1906 (Wikipedia).""")),

 ("attahiru", "Muhammadu Attahiru I", "Sultan of Sokoto", "muhammadu-attahiru-i", "Freedom Fighters & Resistance Leaders", "Pre-Colonial Era", "male",
  None, None, "29 July 1903", "Sultan of the Sokoto Caliphate", "Sokoto", ["WATT"], "well_documented", dict(
  biography="""Muhammadu Attahiru I was the twelfth Sultan of the Sokoto Caliphate, from October 1902 until 15 March 1903, and the last independent sultan before the British conquest (Wikipedia). When British forces advanced on Sokoto in 1903, he and many followers left the city on what he described as a hijra, to prepare for the coming of the Mahdi. The British entered the largely deserted city and on 21 March 1903 installed Muhammadu Attahiru II as the new sultan.""",
  legacy="""British forces attacked Attahiru's followers at Burmi (written Mbormi in the source), near present-day Gombe, on 29 July 1903, and he was among those killed (Wikipedia).""")),

 ("ironsi", "Johnson Aguiyi-Ironsi", "Major General", "johnson-aguiyi-ironsi", "Warriors & Military Leaders", "Military Era", "male",
  "3 March 1924", "Ibeku, Umuahia", "29 July 1966", "Army officer; first military head of state of Nigeria", "Abia", ["WIRO", "LCRI", "LGOW"], "well_documented", dict(
  biography="""Johnson Thomas Umunnakwe Aguiyi-Ironsi (3 March 1924 – 29 July 1966) was a Nigerian army officer and the country's first military head of state (Wikipedia). Born in Ibeku, Umuahia, he was educated in Umuahia and Kano. In November 1960 he led the 5th Battalion to the Congo as part of the United Nations operation there, and by 1964 Nigerian units under his command formed the backbone of the UN force (Library of Congress country study, chapter on the Gowon regime; Wikipedia).

As army commander he took control after the coup of 15 January 1966, suspended the constitution, banned political parties and formed a Federal Military Government; he ruled from 16 January 1966. According to the country study, his decree moving towards a unitary state alarmed many northerners.""",
  legacy="""He was killed on 29 July 1966 in the counter-coup led by northern officers (Wikipedia; country study).""")),

 ("aminu", "Aminu Kano", "Mallam", "aminu-kano", "Political Leaders", "First Republic", "male",
  "9 August 1920", "Sudawa ward, Kano", "17 April 1983", "Teacher, poet, trade unionist and politician", "Kano", ["WAMK", "LSEC"], "well_documented", dict(
  biography="""Aminu Kano (9 August 1920 – 17 April 1983) was a Nigerian politician, teacher, poet, playwright and trade unionist, one of the leading figures of the independence movement (Wikipedia). Born in the Sudawa ward of Kano, he began as a teacher and became an early critic of colonial rule and of the northern aristocracy, denouncing indirect rule as oppressive to the talakawa (commoners).

In 1948 he founded the Northern Teachers' Association, the first labour union in Northern Nigeria, and was a founding member of the Northern People's Congress, which he left because of its conservatism. As leader of the Northern Elements Progressive Union (NEPU) from 1953 he championed democratic socialism, women's rights and the empowerment of the talakawa, seeking to align Islamic principles with social justice. He sat in the Federal House of Representatives from 1959 and served as a federal commissioner under Yakubu Gowon.""",
  career="""In the Second Republic he led the People's Redemption Party, successor to NEPU, and was its presidential candidate; in 1979 the party won Kano State and the governorship of Kaduna (country study; Wikipedia).""")),

 ("ekpo", "Margaret Ekpo", "Chief", "margaret-ekpo", "Women of Influence", "First Republic", "female",
  "27 July 1914", "Creek Town", "21 September 2006", "Women's rights activist and politician", "Cross River", ["WEKPO"], "well_documented", dict(
  biography="""Margaret Ekpo (27 July 1914 – 21 September 2006) was a Nigerian women's rights activist and a pioneering woman politician of the First Republic (Wikipedia). Born in Creek Town, in present-day Cross River State, she grew up in Aba, where after training in domestic science she set up a Domestic Science and Sewing Institute.

She organised a Market Women Association to unionise the market women of Aba, and in 1954 founded the Aba Township Women's Association. She joined the NCNC, which in 1959 nominated her to the regional House of Chiefs, and in 1961 she won a seat in the Eastern Regional House of Assembly, where she continued to fight for women's interests. Wikipedia describes her as one of a class of women activists who rallied women beyond ethnic loyalties in a male-dominated independence movement.""")),

 ("sawaba", "Gambo Sawaba", "Hajia", "gambo-sawaba", "Women of Influence", "First Republic", "female",
  "15 February 1933", None, "October 2001", "Women's rights activist and politician", "Kaduna", ["WSAW"], "reported", dict(
  biography="""Hajia Gambo Sawaba (15 February 1933 – October 2001) was a Nigerian women's rights activist, politician and philanthropist (Wikipedia). Her father, Isa Amartey Amarteifio, was an immigrant from Ghana who converted to Islam after settling in Zaria; her mother, Fatima, was a Nupe woman from Niger State. She was named Gambo, the Hausa name for a child born after twins.

She was elected leader of the national women's wing of the Northern Elements Progressive Union (NEPU) and later served as deputy chairman of the Great Nigerian People's Party. Wikipedia records that she was imprisoned sixteen times for openly campaigning against child marriage, forced and unpaid labour and unfair taxes, and for jobs for women, education for girls and full voting rights.""")),

 ("shagari", "Shehu Shagari", "Alhaji", "shehu-shagari", "Political Leaders", "Post-Colonial Era", "male",
  "25 February 1925", "Shagari", "28 December 2018", "Teacher and politician; president of Nigeria", "Sokoto", ["WSHA", "LSEC", "LMIL"], "well_documented", dict(
  biography="""Shehu Usman Aliyu Shagari (25 February 1925 – 28 December 2018) was the first democratically elected president of Nigeria, from 1979 to 1983 (Wikipedia). Born in Shagari to a Fulani family, he worked briefly as a teacher, entered politics in 1951 and was elected to the House of Representatives in 1954. Between 1958 and 1975 he held federal ministerial posts; as federal commissioner for finance he oversaw the launch of the naira.

He won the presidency in 1979 as candidate of the National Party of Nigeria, defeating Nnamdi Azikiwe in a close and controversial vote (country study). As president he prioritised industrial development, including the Ajaokuta steel works. His government faced the end of the oil boom in 1981, rising debts and political unrest (country study).""",
  career="""He was re-elected in 1983 in an election widely regarded as fraudulent, and was overthrown in the military coup of 31 December 1983 (country study; Wikipedia).""")),

 ("abacha", "Sani Abacha", "General", "sani-abacha", "Warriors & Military Leaders", "Military Era", "male",
  "20 September 1943", "Kano", "8 June 1998", "Army officer; military head of state of Nigeria", "Kano", ["WABA2", "WHIST"], "well_documented", dict(
  biography="""Sani Abacha (20 September 1943 – 8 June 1998) was a Nigerian army officer who ruled as military head of state from 1993 until his death (Wikipedia). Born and raised in Kano to a Kanuri family from present-day Borno State, he played a prominent part in the coup of 1983 that brought Muhammadu Buhari to power and in the coup of 1985 that replaced Buhari with Ibrahim Babangida, under whom he became Chief of Army Staff. In 1993 he seized power from the interim government of Ernest Shonekan; Wikipedia records this as the last successful coup in Nigeria.""",
  career="""His rule was marked by repression. Wikipedia records political killings and executions of opponents, including the hanging of Ken Saro-Wiwa and eight other Ogoni leaders in 1995, which made Nigeria an international pariah, and the imprisonment of M. K. O. Abiola. He is alleged to have embezzled between US$2 billion and US$5 billion, much of it hidden abroad; some of these funds have since been seized and returned to Nigeria (Wikipedia).""",
  legacy="""His death on 8 June 1998 opened the way to the transition led by General Abdulsalami Abubakar and the Fourth Republic in 1999 (Wikipedia).""")),

 ("fela", "Fela Kuti", None, "fela-kuti", "Musicians & Performing Artists", "Military Era", "male",
  "15 October 1938", "Abeokuta", "2 August 1997", "Musician and political activist", "Ogun", ["WFELA", "LMIL"], "well_documented", dict(
  biography="""Fela Aníkúlápó Kútì (born Olufela Olusegun Oludotun Ransome-Kuti; 15 October 1938 – 2 August 1997) was a Nigerian musician and political activist, regarded as the principal innovator of Afrobeat, which combines West African music with American funk and jazz (Wikipedia). Born in Abeokuta, he was the son of the women's rights leader Funmilayo Ransome-Kuti. With his band Africa '70, featuring the drummer Tony Allen, he became a star in Nigeria in the 1970s, performing at his own club, the Afrika Shrine.

He was an outspoken critic of Nigeria's military governments and their target. In 1970 he founded the Kalakuta Republic, a commune that declared itself independent of military rule; it was destroyed in a 1977 army raid in which he was injured and his mother fatally hurt. He was jailed by the Buhari government in 1984 and released after twenty months; the Library of Congress country study describes his arrest as the symbol of that government's crackdown on critics.""",
  legacy="""He continued to record and perform through the 1980s and 1990s. Since his death his music has been widely reissued, and AllMusic has described him as "a musical and sociopolitical voice" of international significance (Wikipedia).""")),

 ("yaradua", "Umaru Musa Yar'Adua", None, "umaru-musa-yaradua", "Political Leaders", "Fourth Republic", "male",
  "16 August 1951", "Katsina", "5 May 2010", "Teacher and politician; president of Nigeria", "Katsina", ["WYAR", "WFOURTH"], "well_documented", dict(
  biography="""Umaru Musa Yar'Adua (16 August 1951 – 5 May 2010) was president of Nigeria from 2007 until his death (Wikipedia). Born in Katsina, he was the son of Musa Yar'Adua, a federal minister in the First Republic, and inherited his father's title of Matawalle of the Katsina Emirate. He taught at the Katsina College of Arts, Science and Technology and at Katsina Polytechnic before entering business and politics, and was governor of Katsina State from 1999 to 2007 for the People's Democratic Party.

He won the presidential election of 21 April 2007, which international observers strongly criticised, and was sworn in on 29 May 2007 (Wikipedia).""",
  legacy="""In 2009 he travelled to Saudi Arabia for treatment for pericarditis; he returned on 24 February 2010 and died on 5 May 2010. His vice-president, Goodluck Jonathan, succeeded him (Wikipedia).""")),

 ("akunyili", "Dora Akunyili", None, "dora-akunyili", "Public Servants & Administrators", "Fourth Republic", "female",
  "14 July 1954", "Makurdi", "7 June 2014", "Pharmacist; head of NAFDAC and Minister of Information", "Anambra", ["WAKU"], "well_documented", dict(
  biography="""Dora Nkem Akunyili (14 July 1954 – 7 June 2014) was Director-General of the National Agency for Food and Drug Administration and Control (NAFDAC) from 2001 to 2008 (Wikipedia). She was born in Makurdi, Benue State, to a father from Nanka, Anambra State.

She had a personal reason for fighting counterfeit medicines: in 1988 her 21-year-old sister died after being given fake insulin. At NAFDAC she built a team of mostly women pharmacists and inspectors and led a campaign against counterfeit drugs that closed many open-air medicine markets, publicised lists of fake products, and by June 2006 had secured convictions of 45 counterfeiters (Wikipedia).""",
  career="""In 2008 she became Minister of Information and Communications, and resigned on 16 December 2010 to run for the Senate seat of Anambra Central (Wikipedia).""")),

 ("buhari", "Muhammadu Buhari", "General", "muhammadu-buhari", "Political Leaders", "Fourth Republic", "male",
  "17 December 1942", "Daura", "13 July 2025", "Army officer and politician; head of state and president of Nigeria", "Katsina", ["WBUH", "LMIL", "WFOURTH"], "well_documented", dict(
  biography="""Muhammadu Buhari (17 December 1942 – 13 July 2025) was a Nigerian army officer and politician who ruled as military head of state from 1983 to 1985 and was elected president from 2015 to 2023 (Wikipedia). Born in Daura, now in Katsina State, he fought in the civil war and rose through later military governments.

He became head of state after the coup of 31 December 1983 that ended the Second Republic. According to the Library of Congress country study, his government set up tribunals against corruption, cut spending and in 1984 launched the "War Against Indiscipline", while critics and journalists were harassed. He was overthrown on 27 August 1985 in a palace coup led by Ibrahim Babangida (Wikipedia).""",
  career="""In the Fourth Republic he stood for president several times and won on 28 March 2015 as candidate of the All Progressives Congress, defeating the incumbent, Goodluck Jonathan: the first opposition candidate to win a presidential election since independence. He was re-elected in February 2019 and served until 29 May 2023 (Wikipedia).""")),
]

RECORDS = []
for key, name, title, slug, cat, period, gender, born, place, died, occ, state, srcs, lvl, prose in PEOPLE:
    first = prose["biography"].split(". ")[0].rstrip(".") + "."
    summary = first if len(first) <= 480 else first[:477].rsplit(" ", 1)[0] + "…"
    fields = dict(english_name=name, title=title, national_slug=slug, category=cat, historical_period=period, gender=gender,
                  date_of_birth=born, place_of_birth=place, date_of_death=died, occupation=occ, state=state, country="Nigeria", short_summary=summary)
    fields.update(prose)
    RECORDS.append(dict(key=key, table="historical_figures", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=fields, srcs=[(s, name) for s in srcs]))

GAPS = [
    ("National people part 2: still missing", "Not yet covered: Queen Moremi, Oduduwa and other figures of tradition; Usman dan Fodio's family (Nana Asma'u, Muhammad Bello); Samuel Ajayi Crowther; Ernest Ikoli; Anthony Enahoro; Ahmadu Rufai Cerkoru; living former heads of state (Gowon, Obasanjo, Babangida, Abubakar, Jonathan) and living cultural figures (Wole Soyinka) — living people need extra care."),
    ("National people part 2: single sources", "Most entries rest on the person's own Wikipedia article only; biographical dictionaries or academic sources should be added, above all for Gambo Sawaba (single source)."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=[], gaps=GAPS, updates=[], relations=[], statistics=[],
                scope="National people, part 2: fourteen more figures, from Iyoba Idia to Muhammadu Buhari (deceased people only).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 139 — National: People, part 2", "", f"Researched {ACCESSED}. Drafts; published only after your approval.", ""]
    for key, name, title, slug, cat, period, gender, born, place, died, occ, state, srcs, lvl, prose in PEOPLE:
        L += [f"## {name} ({born or '?'} – {died or '?'}; {cat}; {sum(words(v) for v in prose.values())} words; {', '.join(srcs)})", ""]
        for k, v in prose.items():
            L += [f"**{k}:**", ""] + [f"> {p}" if p else ">" for p in v.split("\n")] + [""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_139_national_people_2.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_139_national_people_2_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} sources={len(SOURCES)}")
    for key, name, *_r, prose in PEOPLE:
        print(f"  {name}: {sum(words(v) for v in prose.values())} words")
