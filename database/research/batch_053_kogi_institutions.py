"""
Research batch 053 — Kogi (Phase 3): traditional institutions. Researched 2026-09-30. Pattern: batch 046.

Records: the Kogi State Council of Traditional Rulers; the Okun Area Traditional Council; the Attah Igala
(first class; council chairman); the Ohinoyi of Ebiraland (first class; council vice chairman); the Obaro
of Kabba (grade not found); the Ohimege-Igu of Koton Karfe and the Olu of Magongo (first class; their
holders deposed in January 2024); the Etsu Bassa-Nge (grade not found).

Sources:
  * ThisDay (31 Aug 2020): death of Attah Michael Idakwo Ameh Oboni II (Attah from 2013; died 27 Aug
    2020, aged 72), 'Chairman of the Kogi State Council of Traditional Rulers', 'first-class traditional ruler'.
  * Wikipedia, "Igala Kingdom": Idah the capital; the Àtá rotates among four branches of the royal clan
    (Aju Akogwu, Aju Amẹchọ, Aju Akwu, Aju Ocholi); nine traditional councils; first Àtá Ebulejonu.
  * Wikipedia, "Matthew Opaluwa": 28th Àtá, of the Aju Ameacho house; selected 28 Apr 2021, approved by the
    Kogi State Executive Council 18 Oct 2021, crowned at Idah 4 Mar 2022.
  * Wikipedia, "Ohinoyi of Ebiraland": elected by elders, rotating among the major Ebira clans; rulers from 1917.
  * The Will (29 Oct 2023): Ohinoyi Ado Ibrahim, 'first-class monarch', 'vice chairman of Kogi State
    Traditional Council', seat Okene, reigned from 2 Jun 1997.
  * Channels TV (8 Jan 2024) and Blueprint (9 Jan 2024): three first-class rulers deposed (Ohimege-Igu
    Koton-Karfe, Olu Magongo, Obobanyi of Emani); Ahmed Tijani Anaje, Ohi of Okengwe, appointed Ohinoyi.
  * Daily Trust (28 Oct 2018): 44th Obaro of Kabba, chairman of the Okun Area Traditional Council; the stool
    rotates among the Kabba, Odolu and Katu ruling houses.
  * Wikipedia (Okun people; Bassa Nge people) — reused.
Handling: grades stated only where a readable source gives them; present holders named only where the
report is about the stool. Not recorded: the Obobanyi of Emani (first class per Blueprint; the title was
reverted from 'Obobanyi of Ihima' amid litigation; seat not established) — gap.
"""
import json, re, sys

W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "TD20": dict(source_type="news", source_kind="news", source_tier=3, title="Atiku Mourns as Kogi Announces Death of Attah Igala", organisation="ThisDay",
                 author="Chuks Okocha; Ibrahim Oyewale", publication_date="2020-08-31",
                 url="https://thisdaylive.com/index.php/2020/08/31/atiku-mourns-as-kogi-announces-death-of-attah-igala", verification_status="needs_corroboration",
                 notes="Michael Idakwo Ameh Oboni II, Attah Igala from 2013, died 27 August 2020 aged 72; 'Chairman of the Kogi State Council of Traditional Rulers'; 'first-class traditional ruler'."),
    "WIGK": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Igala Kingdom", organisation="Wikipedia", url=W("Igala Kingdom"), verification_status="needs_corroboration",
                 notes="Capital Idah; the Àtá as king, national father and spiritual head; the throne rotates among four branches of the royal clan (Aju Akogwu, Aju Amẹchọ, Aju Akwu, Aju Ocholi); nine traditional ruling councils including Idah; first Àtá Ebulejonu, a woman."),
    "WOPAL": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Matthew Opaluwa", organisation="Wikipedia", url=W("Matthew Opaluwa"), verification_status="needs_corroboration",
                  notes="28th Àtá Ígálá, of the Aju Ameacho ruling house; selected by the Igala Traditional Council 28 April 2021; approved by the Kogi State Executive Council 18 October 2021; crowned at Idah 4 March 2022."),
    "WOHIN": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Ohinoyi of Ebiraland", organisation="Wikipedia", url=W("Ohinoyi of Ebiraland"), verification_status="needs_corroboration",
                  notes="Traditional ruler of the Ebira; elected by elders, rotating among the major clans; rulers: Ibrahim Onoruoiza (1917–1954), Muhamman Sani Omolori (1957–1997), Abdul Rahman Ado Ibrahim (1997–2023), Ahmed Tijani Anaje (from 8 January 2024)."),
    "WILL23": dict(source_type="news", source_kind="news", source_tier=3, title="Ohinoyi Of Ebiraland Ado Ibrahim Is Dead", organisation="The Will", publication_date="2023-10-29",
                   url="https://thewillnews.com/ohinoyi-of-ebiraland-ado-ibrahim-is-dead/", verification_status="needs_corroboration",
                   notes="'The Ohinoyi and paramount ruler of Ebiraland and vice chairman of Kogi State Traditional Council'; 'a first-class monarch'; traditional state headquartered in Okene; reigned from 2 June 1997; died 29 October 2023 aged 94."),
    "CH24": dict(source_type="news", source_kind="news", source_tier=3, title="Governor Bello Deposes Three Traditional Rulers In Kogi", organisation="Channels Television", publication_date="2024-01-08",
                 url="https://www.channelstv.com/2024/01/08/governor-bello-deposes-three-traditional-rulers-in-kogi/", verification_status="needs_corroboration",
                 notes="Deposed: Ohimege-Igu Koton-Karfe (Lokoja/Kogi LGA), Olu Magongo of Magongo, Obobanyi of Emani (title reverted from Obobanyi of Ihima amid litigation); Onu-Ife (Omala) suspended; Ahmed Tijani Anaje appointed Ohinoyi of Ebiraland."),
    "BP24": dict(source_type="news", source_kind="news", source_tier=3, title="Yahaya Bello deposes 3 first class Kogi monarchs, annoints new Ohinoyi of Ibiraland", organisation="Blueprint",
                 author="Oyibo Salihu", publication_date="2024-01-09",
                 url="https://blueprint.ng/yahaya-bello-deposes-3-first-class-kogi-monarchs-annoints-new-ohinoyi-of-ibiraland/", verification_status="needs_corroboration",
                 notes="The Ohimege-Igu Koton-Karfe, the Olu Magongo and the Obobanyi of Emani, all first class, deposed; Ahmed Tijani Anaje, the Ohi of Okengwe, appointed Ohinoyi of Ebiraland."),
    "DT18": dict(source_type="news", source_kind="news", source_tier=3, title="Oba Owoniyi enthroned as 44th Obaro of Kabba", organisation="Daily Trust", publication_date="2018-10-28",
                 url="https://dailytrust.com/oba-owoniyi-enthroned-as-44th-obaro-of-kabba", verification_status="needs_corroboration",
                 notes="Oba Solomon Oladele Owoniyi, Obaro Otitoleke Oweyomade I, presented with the staff of office as the 44th Obaro of Kabba; chairman, Okun Area Traditional Council; appointment approved by the governor in July 2018; rotation among the Kabba, Odolu and Katu ruling houses."),
    "WOKUN": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Okun people", organisation="Wikipedia", url=W("Okun people"), verification_status="needs_corroboration", notes="Reused."),
    "WBNGE": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Bassa Nge people", organisation="Wikipedia", url=W("Bassa Nge people"), verification_status="needs_corroboration", notes="Reused."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified", notes="Reused. Ebira cluster, *Koto: 2.C 'Igu (Egu ...)'."),
}
TEXT = {
 "council": """The Kogi State Council of Traditional Rulers brings together the state's leading traditional rulers. It has been chaired by the Attah Igala: ThisDay described Attah Michael Idakwo Ameh Oboni II, who died in 2020, as its chairman, and The Will described Ohinoyi Ado Ibrahim of Ebiraland, who died in 2023, as its vice chairman. Below it are area councils such as the Okun Area Traditional Council, chaired by the Obaro of Kabba, and the Lokoja/Kogi LGA Traditional Council. The law constituting the council and a complete list of its first-class members were not found.""",
 "okun": """The Okun Area Traditional Council brings together the traditional rulers of the Okun, the Yoruba-speaking peoples of western Kogi State. Its chairman is the Obaro of Kabba (Daily Trust, 2018; Wikipedia).""",
 "attah": """The Attah (Àtá) Igala is the king, national father and spiritual head of the Igala, and rules the Igala Kingdom from its capital at Idah. According to Wikipedia, the first Àtá was a woman, Ebulejonu; the throne rotates among four branches of the royal clan, Aju Akogwu, Aju Amẹchọ, Aju Akwu and Aju Ocholi; and the kingdom has nine traditional councils, headed by the Àtá at Idah.

The Attah is a first-class traditional ruler and chairs the Kogi State Council of Traditional Rulers (ThisDay, 2020). Michael Idakwo Ameh Oboni II reigned from 2013 until his death on 27 August 2020. His successor, Matthew Opaluwa, of the Aju Ameacho house and the 28th Àtá, was selected by the Igala Traditional Council in April 2021, approved by the Kogi State Executive Council in October 2021, and crowned at Idah on 4 March 2022 (Wikipedia).""",
 "ohinoyi": """The Ohinoyi is the paramount ruler of the Ebira, with the seat of the traditional state at Okene, and a first-class monarch; Ohinoyi Ado Ibrahim, who reigned from 1997 until his death in October 2023, was also vice chairman of the Kogi State Traditional Council (The Will, 2023). According to Wikipedia the Ohinoyi is chosen by a group of elders and the office has rotated among the major Ebira clans; the rulers listed there are Ibrahim Onoruoiza (1917–1954), Muhamman Sani Omolori (1957–1997), Abdul Rahman Ado Ibrahim (1997–2023) and Ahmed Tijani Anaje, previously the Ohi of Okengwe, appointed in January 2024 (Channels TV; Blueprint).""",
 "obaro": """The Obaro of Kabba is the traditional ruler of Kabba, the largest Okun town, and chairs the Okun Area Traditional Council. In 2018 Oba Solomon Oladele Owoniyi was installed as the 44th Obaro, after the governor approved his appointment in July; the stool rotates among the Kabba, Odolu and Katu ruling houses (Daily Trust, 2018). The stool's grade is not stated in the sources read.""",
 "ohimege": """The Ohimege-Igu of Koton Karfe is a first-class stool of Kogi State, in Kogi LGA (Blueprint, 2024). In January 2024 Governor Yahaya Bello deposed its holder, together with the Olu of Magongo and the Obobanyi of Emani (Channels TV; Blueprint). 'Igu' is one of the names Blench's Atlas gives for the Koto people, the Ebira Koto of the Koton Karfe area.""",
 "magongo": """The Olu of Magongo is a first-class stool of Kogi State, the ruler of Magongo in Ogori/Magongo LGA (Blueprint, 2024). In January 2024 Governor Yahaya Bello deposed its holder, together with the Ohimege-Igu of Koton Karfe and the Obobanyi of Emani (Channels TV; Blueprint).""",
 "etsu": """The Etsu Bassa-Nge is the traditional ruler of the Bassa-Nge, with his seat at Gboloko, according to Wikipedia. The title Etsu is the one also used by the ruler of the Nupe. The stool's grade and history are not described in a readable source.""",
}
REC = [
    ("council", "traditional_council", "Kogi State Council of Traditional Rulers", "kogi-state-council-of-traditional-rulers", ["TD20", "WILL23", "DT18", "BP24"], "reported"),
    ("okun", "traditional_council", "Okun Area Traditional Council", "okun-area-traditional-council", ["DT18", "WOKUN"], "reported"),
    ("attah", "kingdom", "Attah Igala", "attah-igala", ["WIGK", "TD20", "WOPAL"], "reported"),
    ("ohinoyi", "traditional_title", "Ohinoyi of Ebiraland", "ohinoyi-of-ebiraland", ["WOHIN", "WILL23", "CH24", "BP24"], "reported"),
    ("obaro", "traditional_title", "Obaro of Kabba", "obaro-of-kabba", ["DT18", "WOKUN"], "reported"),
    ("ohimege", "traditional_title", "Ohimege-Igu of Koton Karfe", "ohimege-igu-of-koton-karfe", ["BP24", "CH24", "ATLAS"], "reported"),
    ("magongo", "traditional_title", "Olu of Magongo", "olu-of-magongo", ["BP24", "CH24"], "reported"),
    ("etsu", "traditional_title", "Etsu Bassa-Nge", "etsu-bassa-nge", ["WBNGE"], "reported"),
]
RECORDS = []
for key, ptype, name, slug, srcs, lvl in REC:
    t = TEXT[key]
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl,
                        fields=dict(polity_type=ptype, name=name, slug=slug, is_extant=1, summary=t.split(". ")[0] + ".", description=t),
                        srcs=[(s, name) for s in srcs]))
K = lambda l: f"@admin_units:lga:kogi/{l}"
SEAT = "Seat of the stool (not a statement of its jurisdiction)."
RELATIONS = [
    dict(frm="council", type="located_in", to="@admin_units:state:kogi", source="TD20", evidence="multiple_sources", level="reported", notes="State-level council."),
    dict(frm="okun", type="located_in", to="@admin_units:state:kogi", source="DT18", evidence="single_reliable_source", level="reported", notes="Area council of the Okun (western Kogi)."),
    dict(frm="okun", type="part_of", to="council", source="DT18", evidence="single_reliable_source", level="reported", notes="An area traditional council of Kogi State."),
    dict(frm="attah", type="member_of", to="council", role="chairman", source="TD20", evidence="single_reliable_source", level="reported", notes="ThisDay (2020): the Attah as chairman."),
    dict(frm="ohinoyi", type="member_of", to="council", role="vice chairman", source="WILL23", evidence="single_reliable_source", level="reported", notes="The Will (2023): the Ohinoyi as vice chairman."),
    dict(frm="obaro", type="member_of", to="okun", role="chairman", source="DT18", evidence="multiple_sources", level="reported", notes="Daily Trust (2018); Wikipedia (Okun people)."),
    dict(frm="attah", type="located_in", to=K("idah"), source="WIGK", evidence="multiple_sources", level="reported", notes="Idah, capital of the Igala Kingdom. " + SEAT),
    dict(frm="ohinoyi", type="located_in", to=K("okene"), source="WILL23", evidence="single_reliable_source", level="reported", notes="Traditional state headquartered in Okene (The Will). " + SEAT),
    dict(frm="obaro", type="located_in", to=K("kabba-bunu"), source="DT18", evidence="single_reliable_source", level="reported", notes="Kabba town. " + SEAT),
    dict(frm="ohimege", type="located_in", to=K("kogi"), source="CH24", evidence="single_reliable_source", level="reported", notes="Koton Karfe, Kogi LGA (Channels TV: 'Lokoja/Kogi LGA'). " + SEAT),
    dict(frm="magongo", type="located_in", to=K("ogori-magongo"), source="CH24", evidence="single_reliable_source", level="reported", notes="Magongo. " + SEAT),
    dict(frm="etsu", type="located_in", to="@admin_units:state:kogi", source="WBNGE", evidence="single_reliable_source", level="reported", notes="Seat at Gboloko (Wikipedia); the LGA is not stated."),
    dict(frm="attah", type="associated_with", to="@ethnic_groups:igala", role="king of the Igala", source="WIGK", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Igala Kingdom; Igala people)."),
    dict(frm="ohinoyi", type="associated_with", to="@ethnic_groups:ebira", role="paramount ruler of the Ebira", source="WOHIN", evidence="multiple_sources", level="well_documented", notes="Wikipedia; The Will."),
    dict(frm="obaro", type="associated_with", to="@ethnic_groups:okun", role="Okun ruler; council chairman", source="WOKUN", evidence="multiple_sources", level="reported", notes="Wikipedia (Okun people); Daily Trust."),
    dict(frm="okun", type="associated_with", to="@ethnic_groups:okun", role="area council of the Okun", source="DT18", evidence="single_reliable_source", level="reported", notes="Daily Trust (2018)."),
    dict(frm="ohimege", type="associated_with", to="@ethnic_groups:ebira", role="ruler of the Igu (Ebira Koto) of Koton Karfe", source="ATLAS", evidence="single_reliable_source", level="reported",
         notes="Blench's Atlas gives Igu as a name of the Koto (Ebira Koto) people; Wikipedia places the Ebira Koto in Kogi (Koton Karfe) LGA."),
    dict(frm="magongo", type="associated_with", to="@ethnic_groups:ogori-magongo", role="ruler of Magongo", source="BP24", evidence="single_reliable_source", level="reported", notes="Olu Magongo of Magongo."),
    dict(frm="etsu", type="associated_with", to="@ethnic_groups:bassa-nge", role="traditional ruler", source="WBNGE", evidence="single_reliable_source", level="reported", notes="Wikipedia (Bassa Nge people)."),
]
NAMES = [
    dict(record="attah", name="Àtá Ígálá", name_type="endonym", usage_notes="Wikipedia (Igala Kingdom; Matthew Opaluwa).", srcs=["WIGK"]),
    dict(record="attah", name="Ata of Igala", name_type="spelling_variant", usage_notes="Wikipedia (Igala Kingdom).", srcs=["WIGK"]),
    dict(record="council", name="Kogi State Traditional Council", name_type="alternative", usage_notes="The Will (2023).", srcs=["WILL23"]),
]
GAPS = [
    ("Kogi: list of first-class stools", "No official list found. First class is recorded for the Attah Igala, the Ohinoyi of Ebiraland, the Ohimege-Igu of Koton Karfe and the Olu of Magongo (news reports); the Obaro of Kabba's and the Etsu Bassa-Nge's grades are not stated in the sources read."),
    ("Kogi: Obobanyi of Emani", "Described as first class (Blueprint, 2024); the title was reverted from 'Obobanyi of Ihima' amid litigation. Seat and people not established; not recorded."),
    ("Kogi: the council's law and composition", "The law constituting the Kogi State Council of Traditional Rulers and its full membership were not found."),
    ("Kogi: other stools", "The nine Igala councils (Ankpa, Ajaka, Ugwolawo, Egume, Dekina, Omala, Olamaboro and others), the other Okun stools (Olubunu, Olujumu, Agbana of Isanlu, Olu of Oworo), the Ogori stool and the Kakanda and Kupa rulers are not yet researched."),
    ("Kogi: Ohimege-Igu and Olu Magongo after 2024", "Whether the two stools have been filled since the January 2024 depositions is not documented."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kogi traditional institutions: state council, Okun council, Attah Igala, Ohinoyi of Ebiraland, Obaro of Kabba, Ohimege-Igu, Olu Magongo, Etsu Bassa-Nge.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 053 — Kogi: traditional institutions", "",
         "Researched 2026-09-30. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **8 records:**",
         "  - the **Kogi State Council of Traditional Rulers**, chaired by the Attah Igala",
         "  - the **Okun Area Traditional Council**, chaired by the Obaro of Kabba",
         "  - **four first-class stools**, each confirmed by a news report: the Attah Igala, the Ohinoyi of Ebiraland, the Ohimege-Igu of Koton Karfe and the Olu of Magongo",
         "  - the **Obaro of Kabba** and the **Etsu Bassa-Nge**, whose grades were not found",
         "- **Links:** each stool to its seat LGA and to its people, and the chairmen to their councils.",
         "- **The January 2024 depositions** (Ohimege-Igu and Olu Magongo) are recorded. The stools remain, but their present holders are not named.",
         "- **Not recorded:** the Obobanyi of Emani, whose title is in litigation and whose seat is not established. Listed as a gap.",
         "- All records are short, so they are noindex and the sitemap is unchanged.", ""]
    for key, ptype, name, slug, srcs, lvl in REC:
        t = TEXT[key]
        L += [f"## {name} ({words(t)} words)", ""] + [f"> {p}" if p else ">" for p in t.split("\n")] + [""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_053_kogi_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_053_kogi_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
