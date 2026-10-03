"""
Research batch 034 — Nasarawa (Phase 3): culture and heritage, part 1. Researched 2026-09-26.

Records: Keana Salt Village (NCMM proposed national monument No. 90), the Oyarore salt festival of
Keana, the Odu festival of Doma, and the National Museum, Lafia.

Evidence notes:
  * NCMM (museum.ng): the declared-monuments list has NO site in Nasarawa. The proposed list has
    No. 90 "Keana Salt Village, Keana" but labels it "Plateau State" — Keana was in Plateau until
    Nasarawa State was created in 1996, so the label is out of date, not a rival claim.
  * Keana salt making and Oyarore: Betty Onuh, "Salt Women of Keana", Newswatch (Lagos), 17 Nov 2002
    (read in full via the Internet Archive copy of allAfrica). Its founding legend (salt found c. 1232
    by Akeana Adi) is oral tradition and labelled so. The 2025 festival: NAN report carried by
    Vanguard (21 Dec 2025). Its claim that the Federal Government has designated the site a national
    monument is the organiser's statement; the NCMM still lists the site as proposed — recorded as such.
  * Avre (2006), JORIND 4(2): abstract only (salt mining by rural women, poverty, environment).
  * Odu: Nasarawa Broadcasting Service (state broadcaster, 19 Mar 2025) and Wikipedia (Doma).
    The origin story (the sacrifice of Prince Oshobi) is found only on weak sites — gap. The site
    whereinnasarawa.com now serves gambling spam and is not used.
  * Academic papers on Oyarore (Adokwe 2019; "Ritual Dance ... Oyarore Salt Festival") and on Odu
    ("Theatrical Performance Aesthetics of Doma Odu Festival") are on academia.edu / ResearchGate,
    which block reading; search-engine snippets from them are NOT used.
"""
import json, re, sys

ACCESSED = "2026-09-26"
SOURCES = {
    "NCMMP": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="Proposed National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/proposed-national-monuments/",
                  verification_status="verified",
                  notes="NCMM manages 65 national monuments with about 100 more awaiting declaration. Benue entries: No. 89 Makurdi Railway Bridge, Makurdi; No. 91 Traditional Iron Smelting Furnaces in Igede. No. 90: 'Keana Salt Village, Keana, Plateau State' (Keana has been in Nasarawa State since 1996). All three under Technology (Indigenous, Colonial and Early Post-Colonial Era)."),
    "NCMMD": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/national-monuments/",
                  verification_status="verified", notes="List of declared national monuments; no Benue or Nasarawa State monument appears in it (checked 2026-09-26)."),
    "NCMMM": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="National Museums",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/museums/national-museums/",
                  verification_status="verified",
                  notes="National Museum Makurdi: GP 4, Ahmadu Bello, opposite the Deputy Governor's Office, P.M.B. 102294, Makurdi, Benue State. National Museum Lafia: behind the Deputy Governor's Office, Shendam Road, P.M.B. 127, Lafia, Nasarawa State."),
    "NW02": dict(source_type="news", source_kind="news", source_tier=3, title="Salt Women of Keana", author="Betty Onuh",
                 organisation="Newswatch (Lagos), via allAfrica", publication_date="2002-11-17",
                 url="https://allafrica.com/stories/200211190755.html", verification_status="needs_corroboration",
                 notes="Read via the Internet Archive (snapshot 2003-01-01). Keana, 67 km from Lafia; salt mined by local technology 'for about 700 years'; process: salty soil in sets of three pots on a hollowed palm trunk, pond brine poured through, filtrate boiled in pots until it solidifies; only clay pots allowed into the pond; huts per lineage, inherited; mining exclusively women's work, men help drain the pond after heavy rain; peak in the dry season; Onyarore: a seven-day festival of thanksgiving for the salt harvest and the start of a new year, usually in November, dated by the Osana, 'chief custodian of the salt mine', who presides; climax 'wife lottery' (spear thrown by the Osana); festival 'no longer very popular' (2002); legend: salt found about 1232 AD by Akeana Adi while hunting; Alago-speaking people; 1995 UNDP/state trip of nine women to India; federal solar drying pit; salt also at Akiri (Awe LGA). Sources quoted: the state commissioner for tourism and culture, Hamza Elayo Mohammed, and two women miners."),
    "VG25": dict(source_type="news", source_kind="news", source_tier=3, title="Oyarore salt festival showcases Nasarawa's rich heritage, boosts creative economy – Minister",
                 organisation="Vanguard (News Agency of Nigeria report)", publication_date="2025-12-21",
                 url="https://www.vanguardngr.com/2025/12/oyarore-salt-festival-showcases-nasarawas-rich-heritage-boosts-creative-economy-minister/",
                 verification_status="needs_corroboration",
                 notes="2025 National Oyarore Salt Festival, Keana, began 10 Dec 2025; the Minister of Arts, Culture, Tourism and Creative Economy (represented) and the Governor (represented by the Deputy Governor) spoke; the Osana of Keana, Dr Abdullahi Agbo III: festival over 250 years old; organising committee chairman Dr Mike Omeri: 250th anniversary, began 1775; salt discovery central to Keana's founding; festival marks end of harvest and start of a new season; registered with the Corporate Affairs Commission; 'the Federal Government has designated the festival as a national event and the site as a national monument'; salt in the state logo; wrestling, performances, exhibitions; Alago nation."),
    "AVRE": dict(source_type="research", source_kind="journal_article", source_tier=2,
                 title="Traditional Mining, Poverty and Environment: The Case of Keana Salt Mining Sites in Nasarawa State", author="A. J. Avre",
                 organisation="Journal of Research in National Development", publication_date="2006",
                 publication_details="Vol. 4, No. 2, pp. 59–64. DOI 10.4314/jorind.v4i2.42333",
                 url="https://www.ajol.info/index.php/jorind/article/view/42333", verification_status="verified",
                 notes="Abstract only (read via the Internet Archive): evaluates traditional salt mining by rural women in Keana LGA; potential to reduce poverty held back by primitive methods, lack of finance, and 'mundane beliefs and restrictions'."),
    "NBS25": dict(source_type="news", source_kind="news", source_tier=3, title="Embracing Cultural Heritage: Andoma of Doma Urges Nigerians to Preserve Tradition",
                  author="Abdullahi Ibn-Umar", organisation="Nasarawa Broadcasting Service (state broadcaster)", publication_date="2025-03-19",
                  url="https://nbs.na.gov.ng/2025/03/19/embracing-cultural-heritage-andoma-of-doma-urges-nigerians-to-preserve-tradition/",
                  verification_status="needs_corroboration",
                  notes="2025 Odu Annual Festival held in Doma on a Tuesday (article of 19 March 2025); the Andoma of Doma, Dr Ahmadu Aliyu Oga Onawo, spoke through the Ogbole of Doma; Odu celebrated annually by the Alago to thank God for the previous year's harvest; the Idoma Woza Kengeh of Doma said it was established to foster unity in Alago land; performances included the acrobatic dance of the Odu figure 'Eku' and the royal father's dance."),
    "WDOM": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Doma, Nigeria", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Doma,_Nigeria",
                 verification_status="needs_corroboration",
                 notes="Reused. 'Odu is the annual festival in Doma local government'; Alago predominant in the north of the LGA, Bassa in the south."),
    "WKEA": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Keana", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Keana",
                 verification_status="needs_corroboration", notes="Reused. Keana 'home of salt'; founded by Akyana Adi in the 12th century (tradition)."),
}

SALT = """Keana Salt Village, in Keana Local Government Area of Nasarawa State, is where the people of Keana have made salt from salty soil and brine by hand for generations. The National Commission for Museums and Monuments (NCMM) lists it as No. 90 among the sites proposed for declaration as national monuments, in its category of indigenous, colonial and early post-colonial technology. The NCMM list still places it in Plateau State, to which Keana belonged until Nasarawa State was created in 1996. It is not among the declared national monuments.

A report by Betty Onuh in Newswatch (November 2002) describes the site as it was then: a row of small huts and clay pots on land where "the waters and soil underneath, all taste of salt". Salty sand is heaped into sets of three pots set on a hollowed palm trunk, and brine from a nearby pond is poured through them. The filtered brine is collected and boiled in pots until it solidifies into salt, and the drained sand is spread out again with brine to dry for the next round. Only clay pots may be taken into the pond, never metal buckets. Each hut belongs to a lineage and is inherited, and the work is done almost entirely by women, who also sell the salt; men help mostly to drain the pond when heavy rain dilutes it, and the peak season is the dry season. A study of Keana's salt mining by A. J. Avre (2006) likewise describes it as an industry of rural women, held back by traditional methods, lack of finance and customary restrictions.

According to local tradition reported by Newswatch, the salt was found in about 1232 AD by a hunter, Akeana Adi, and the deposit drew people to settle there. Wikipedia names Akyana Adi as the founder of Keana and calls the town the "home of salt". These dates come from oral tradition and are not established history."""

OYARORE = """Oyarore (also written Onyarore) is the salt festival of Keana, in Nasarawa State, held by the Alago people of the town to give thanks for the year's salt.

In 2002, Newswatch quoted the state commissioner for tourism and culture, a native of Keana, as describing Onyarore as a seven-day festival of thanksgiving to the gods for the previous year's salt harvest, which also opens a new harvest year. It was then usually held in November, on a date set by the Osana of Keana, the traditional ruler and "chief custodian of the salt mine", who presides over its sacrifices, incantations and feasting. On the last day, the report said, the Osana threw a spear over a woven fence to the young men of the town, and the one who caught it was given a wife. Newswatch added that, under the influence of Christianity, the festival was "no longer very popular" at that time.

The festival has since been revived on a larger scale. The News Agency of Nigeria, reported by Vanguard, describes the 2025 National Oyarore Salt Festival, which opened on 10 December 2025 with cultural displays, wrestling, traditional performances and exhibitions, attended by representatives of the federal Minister of Arts, Culture, Tourism and Creative Economy and of the state governor. The Osana, Dr Abdullahi Agbo III, said the festival was more than 250 years old, and the organising committee presented the 2025 edition as the 250th anniversary of a festival begun in 1775. The organisers also said the Federal Government had designated the festival a national event and the salt site a national monument; the NCMM, however, still lists Keana Salt Village as a proposed monument."""

ODU = """Odu is the annual festival of Doma, the Alago kingdom in Doma Local Government Area of Nasarawa State. Wikipedia describes it as the annual festival of Doma local government.

The Nasarawa Broadcasting Service, the state broadcaster, reported on the 2025 festival, held in Doma on a Tuesday in March. It describes Odu as a festival the Alago celebrate every year to thank God for the previous year's harvest. The Andoma of Doma, Dr Ahmadu Aliyu Oga Onawo, speaking through the Ogbole of Doma, presented it as an occasion that brings people of different backgrounds together, and the Idoma Woza Kengeh of Doma said the festival was established to foster unity in Alago land. The performances included the acrobatic dance of Eku, the legendary figure of Odu, and a dance by the Andoma himself.

The origin of the festival and the meaning of Eku are not yet documented here from a reliable source."""

MUSEUM = """The National Museum, Lafia is one of the national museums run by the National Commission for Museums and Monuments (NCMM), the federal agency responsible for Nigeria's museums and monuments. The NCMM gives its address as behind the Deputy Governor's Office, Shendam Road, Lafia, the capital of Nasarawa State. It is the only NCMM national museum listed in the state. When it was founded and what its galleries hold are not yet documented here from a readable source."""

RECORDS = [
    dict(key="salt", table="places", evidence="multiple_sources", level="well_documented",
         fields=dict(place_type="heritage_site", name="Keana Salt Village", slug="keana-salt-village", admin_unit_id="@admin_units:lga:nasarawa/keana",
                     status="existing",
                     summary="Keana Salt Village, Nasarawa State, is a traditional salt-making site worked by Alago women and a proposed national monument of the NCMM.",
                     description=SALT),
         srcs=[("NCMMP", "Proposed national monument No. 90 (technology); listed under Plateau State"),
               ("NW02", "Site, process, clay pots, lineage huts, women's work, founding legend"),
               ("AVRE", "Salt mining by rural women in Keana LGA; constraints"),
               ("WKEA", "Keana 'home of salt'; founder Akyana Adi (tradition)")]),
    dict(key="oyarore", table="cultural_records", evidence="multiple_sources", level="well_documented",
         fields=dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice",
                     name="Oyarore Salt Festival", local_name="Oyarore", slug="oyarore-salt-festival", language_id="@languages:alago",
                     timing="Annually; usually November, on a date set by the Osana (Newswatch 2002); the 2025 edition opened on 10 December",
                     season="After the salt harvest", month_from=11, month_to=12, current_status="active", scope_level="community",
                     summary="Oyarore is the salt festival of Keana, Nasarawa State: a thanksgiving for the year's salt, presided over by the Osana of Keana and now held as a national festival.",
                     description=OYARORE),
         srcs=[("NW02", "Seven days; thanksgiving for salt; November; Osana as custodian; spear rite; declining in 2002"),
               ("VG25", "2025 National Oyarore Salt Festival; 250 years claim (1775); activities; officials")]),
    dict(key="odu", table="cultural_records", evidence="multiple_sources", level="reported",
         fields=dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice",
                     name="Odu", local_name="Odu", slug="odu-festival-doma", language_id="@languages:alago",
                     timing="Annually in March (2025 edition, per the Nasarawa Broadcasting Service)", season="After the harvest",
                     month_from=3, month_to=3, current_status="active", scope_level="community",
                     summary="Odu is the annual festival of Doma, Nasarawa State, where the Alago give thanks for the harvest in the presence of the Andoma of Doma.",
                     description=ODU),
         srcs=[("NBS25", "2025 festival; thanksgiving for harvest; unity; Eku dance; the Andoma"),
               ("WDOM", "Odu the annual festival of Doma")]),
    dict(key="museum", table="places", evidence="single_reliable_source", level="well_documented",
         fields=dict(place_type="museum", name="National Museum, Lafia", slug="national-museum-lafia", admin_unit_id="@admin_units:lga:nasarawa/lafia",
                     status="existing", summary="The National Museum, Lafia is a national museum of the National Commission for Museums and Monuments in Lafia, Nasarawa State.",
                     description=MUSEUM),
         srcs=[("NCMMM", "National museum of the NCMM; address")]),
]
NAMES = [
    dict(record="oyarore", name="Onyarore", name_type="spelling_variant", usage_notes="Spelling used by Newswatch (2002).", srcs=["NW02"]),
    dict(record="oyarore", name="National Oyarore Salt Festival", name_type="alternative", usage_notes="Name of the 2025 edition (NAN/Vanguard).", srcs=["VG25"]),
]
RELATIONS = [
    dict(frm="salt", type="associated_with", to="@ethnic_groups:alago", role="salt-making community", source="NW02", evidence="multiple_sources", level="well_documented",
         notes="Mining 'among the Alago-speaking people' (Newswatch 2002); Keana an Alago town (Wikipedia)."),
    dict(frm="oyarore", type="celebrated_by", to="@ethnic_groups:alago", source="VG25", evidence="multiple_sources", level="well_documented",
         notes="Keana and 'the Alago nation' (NAN/Vanguard 2025); Newswatch 2002."),
    dict(frm="oyarore", type="celebrated_in", to="@admin_units:lga:nasarawa/keana", source="NW02", evidence="multiple_sources", level="verified",
         notes="Keana town (Newswatch 2002; Vanguard 2025)."),
    dict(frm="oyarore", type="associated_with", to="salt", role="the salt site it celebrates", source="NW02", evidence="multiple_sources", level="well_documented",
         notes="Thanksgiving for the salt harvest."),
    dict(frm="@polities:osana-of-keana", type="custodian_of", to="salt", source="NW02", evidence="single_reliable_source", level="reported",
         notes="Newswatch (2002): the Osana is 'the chief custodian of the salt mine'."),
    dict(frm="@polities:osana-of-keana", type="custodian_of", to="oyarore", source="NW02", evidence="multiple_sources", level="well_documented",
         notes="Sets the date and presides (Newswatch 2002); host ruler in 2025 (Vanguard)."),
    dict(frm="odu", type="celebrated_by", to="@ethnic_groups:alago", source="NBS25", evidence="single_reliable_source", level="reported",
         notes="Nasarawa Broadcasting Service (2025)."),
    dict(frm="odu", type="celebrated_in", to="@admin_units:lga:nasarawa/doma", source="NBS25", evidence="multiple_sources", level="well_documented",
         notes="Doma (NBS 2025; Wikipedia)."),
    dict(frm="@polities:andoma-of-doma", type="custodian_of", to="odu", source="NBS25", evidence="single_reliable_source", level="reported",
         notes="The Andoma leads the festival and dances at it (NBS 2025)."),
]
GAPS = [
    ("Keana salt: NCMM state label", "The NCMM proposed-monuments list (No. 90) still places Keana Salt Village in Plateau State; it has been in Nasarawa since 1996. Worth reporting to the NCMM."),
    ("Keana salt: national monument status", "The Oyarore organisers said in December 2025 that the Federal Government has designated the site a national monument; the NCMM list (checked 26 Sep 2026) still shows it as proposed. Needs a gazette or NCMM notice."),
    ("Oyarore: age and dates", "The 1775 start date (organisers, 2025) and the 1232 salt-discovery legend (Newswatch 2002) are unverified traditions; the move from November to December is not explained."),
    ("Oyarore and Odu: academic studies", "Papers on Oyarore (H. Adokwe 2019, 'Continuity and Change in Ogiri and Oyarore Festivals'; 'Ritual Dance ... Oyarore Salt Festival') and on Odu ('Theatrical Performance Aesthetics of Doma Odu Festival of Alago') could not be read (academia.edu / ResearchGate block access)."),
    ("Odu: origin and Eku", "Websites tell of an Andoma who sacrificed his son, Prince Oshobi, whose spirit Eku represents; not found in a reliable source."),
    ("Osana's name", "Wikipedia names the 34th Osana Abdullahi Amegwa III; NAN (2025) names the Osana Dr Abdullahi Agbo III. Probably the same person; not settled."),
    ("National Museum, Lafia: history and collections", "Founding date and galleries not found in a readable source."),
    ("Other Nasarawa culture", "Eggon (Anzhili festival), Mada, Migili, Gwandara, Koro, Basa and Tiv festivals and heritage sites are not yet researched; Ogiri of Agwatashi too."),
    ("Declared national monuments in Nasarawa", "None appears in the NCMM list as checked on 26 Sep 2026."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Nasarawa (Phase 3) culture and heritage, part 1: Keana Salt Village, the Oyarore salt festival, the Odu festival of Doma, the National Museum, Lafia.")


def quote(t):
    return [f"> {p}" if p else ">" for p in t.split("\n")]


def report():
    L = ["# Research batch 034 — Nasarawa culture and heritage (1)", "",
         f"Researched {ACCESSED}. Phase 3, Nasarawa. Created in review; published only after your approval.", "",
         "**Findings:**",
         "- The NCMM's list of declared national monuments has **no site in Nasarawa**. **Keana Salt Village** is No. 90 on its list of *proposed* monuments, but the NCMM still labels it **Plateau State** (Keana was in Plateau until 1996).",
         "- The Oyarore organisers said in December 2025 that the site is now a national monument; the NCMM list does not show this. Both are recorded, and the gap stays open.",
         "- The NCMM runs one national museum in the state, at **Lafia**.", "",
         f"- Keana Salt Village ({words(SALT)} words) · Oyarore Salt Festival ({words(OYARORE)}) · Odu ({words(ODU)}) · National Museum, Lafia ({words(MUSEUM)}).",
         f"- {len(RELATIONS)} links, including the Osana of Keana as custodian of the salt site and of Oyarore, and the Andoma of Doma as custodian of Odu.", "",
         "## Keana Salt Village", ""] + quote(SALT) + ["", "## Oyarore Salt Festival", ""] + quote(OYARORE) + \
        ["", "## Odu (Doma)", ""] + quote(ODU) + ["", "## National Museum, Lafia", ""] + quote(MUSEUM)
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s.get('author', s['organisation'])}{', ' + s['publication_date'] if s.get('publication_date') else ''}). {s['url']}. Tier {s['source_tier']}." for k, s in SOURCES.items()]
    L += ["", "## Not used", "",
          "- whereinnasarawa.com (Odu origin story): the domain now serves gambling spam.",
          "- Search-engine snippets of the academia.edu / ResearchGate papers on Oyarore and Odu (could not be read).",
          "- Nasarawa Broadcasting Service, 'Unveiling the Mysteries of Nasarawa Salt Lake' (2025): generic, unsourced claims (no inflow, constant level).",
          "- Facebook, TikTok and YouTube posts.", "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_034_nasarawa_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_034_nasarawa_heritage_REVIEW.md", "w").write(report())
    print(f"salt={words(SALT)} oyarore={words(OYARORE)} odu={words(ODU)} museum={words(MUSEUM)} relations={len(RELATIONS)}")
