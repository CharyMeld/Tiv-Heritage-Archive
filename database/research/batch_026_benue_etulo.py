"""
Research batch 026 — Benue culture (2): the Etulo (gap G-05, part 2). Researched 2026-09-26.

Main source: Chikelu I. Ezenwafor-Afuecheta, A Grammar of Etulo (Open Book Publishers, 2025,
CC BY-NC), chapter 1, which summarises the people's sociolinguistic situation, history and
culture from Tabe (2007), Gbor (1974), Hanior (1989), Agbedo and Kwambehar (2013) and fieldwork.
I am Benue corroborates location and livelihoods and gives the Ukpleka festival (August, Katsina-Ala,
titles conferred by the Etsu-Etulo) and the Akata fishing festival.

Evidence notes:
  * Origin: oral tradition via Tabe (2007) and Gbor (1974) — presented as tradition. Gbor's claim
    that the Tiv pushed other Kwararafa groups out of the Benue valley by conquest is attributed to
    him, not stated as fact.
  * "Opleka" (grammar) and "Ukpleka" (I am Benue) are treated as one festival, with the variant name
    and a note that the identity is not confirmed.
  * Speaker numbers: estimates range from 10,000 to 100,000 (as the grammar reports); not stored as
    statistics because none is a census of speakers.
  * Changes to the existing Etulo–Buruku and Etulo–Katsina-Ala links are in fix_026_etulo.php.
"""
import json, re, sys

ACCESSED = "2026-09-26"
SOURCES = {
    "GRAM": dict(source_type="book", source_kind="academic_book", source_tier=2, title="A Grammar of Etulo", author="Chikelu I. Ezenwafor-Afuecheta",
                 publisher="Open Book Publishers", publication_date="2025", publication_details="Chapter 1, General Introduction. DOI 10.11647/OBP.0467.01. CC BY-NC 4.0",
                 url="https://books.openbookpublishers.com/10.11647/obp.0467/ch1.xhtml", verification_status="verified",
                 notes="Name Etulo adopted 1976 (formerly called Turu by the Tiv and Utur by the Hausa); 14 clans (9 in Buruku, 5 in Katsina-Ala); Benue and Taraba (Wukari); both banks of the Katsina-Ala River; Adi market; Idomoid (Armstrong 1989; Williamson and Blench 2000); Tabe (2007) links it to Jukunoid; multilingual, Tiv dominant; critically endangered (Agbedo and Kwambehar 2013); 10,000–100,000 speakers by various estimates; Kwararafa origin with the Jukun (Tabe 2007), settled before the Tiv (Gbor 1974); progenitor Ibagye, Itsikpe, sons Okakwu, Ozi, Okwe; enthronement needs the Aku Uka's blessing; supreme being Mgbasho, minor gods Esekio and Emakpala; family shrine Ozoka; Christianity from about 1939; age grades; Opleka and Agashi festivals; fishing, farming (rice, millet, oranges), blacksmiths, carvers, herbalists."),
    "IAMET": dict(source_type="website", source_kind="community_organisation", source_tier=4, title="Etulo People of Benue State", organisation="I am Benue",
                  url="https://www.iambenue.com/benue-state/ethnic-composition/etulo-people/", verification_status="needs_corroboration",
                  notes="Etulo are found in Buruku LGA and part of Katsina-Ala LGA; known for fishing and farming."),
    "IAMFEST": dict(source_type="website", source_kind="community_organisation", source_tier=4, title="Benue Cultural Festivals", organisation="I am Benue",
                    url="http://www.iambenue.com/benue-state/culture/benue-cultural-festivals/", verification_status="needs_corroboration",
                    notes="Ukpleka festival of the Etulo of Katsina-Ala, every August: an annual gathering before their chief, the Etsu-Etulo, who confers titles; Akata fishing festival (Tiv, Etulo and Jukun fishermen), March–May, Katsina-Ala; Igede Agba in September; Idoma festivals (Ujor, Odumu, Ejegembi, Eje-Alekwu)."),
}

DESCRIPTION = """The Etulo are a small people of the Benue valley. According to Chikelu Ezenwafor-Afuecheta's Grammar of Etulo (2025), the name Etulo refers at once to the people, their language and their land. Their neighbours long called them by other names, Turu among the Tiv and Utur among the Hausa, and the name Etulo was officially adopted in 1976 with the support of the Benue State military government.

In Benue State the Etulo live on both banks of the Katsina-Ala River, in Buruku and Katsina-Ala local government areas; others live in Wukari Local Government Area of Taraba State. The grammar counts fourteen clans: nine in Buruku (Agbatala, Oglazi, Agbo, Ugie, Agia, Ogbulube, Oshafu, Okpashila and Ingwaje) and five in Katsina-Ala (Otsazi, Otanga, Okadinya, Shewe and Ashitanakwu). The Etulo land in Buruku is home to the well-known Adi market, whose name, the grammar notes, is gradually replacing that of the area. It also records a view held by some Etulo that their division between two local government areas reduced their weight as a minority.

The Etulo language is classified as Idomoid. The Etulo are multilingual: most also speak Tiv, the dominant language of the area, and Etulo is used mostly at home, in the market and in church. A 2013 study cited by the grammar describes the language as critically endangered. Estimates of the number of speakers range from about 10,000 to 100,000."""

ORIGINS = """Etulo history is known mostly from oral tradition. As summarised in the Grammar of Etulo, Tabe (2007) traces the Etulo to one of the Jukun-related groups of the old Kwararafa kingdom, which later separated into peoples including the Etulo, Idoma and Jukun. Gbor (1974) holds that the Etulo were settled on the banks of the Benue before the Tiv arrived, and that the Tiv later displaced other Kwararafa groups from the valley; these are the historians' interpretations, reported here as such. The Etulo trace their descent to a progenitor named Ibagye, whose son Itsikpe led the people; Itsikpe's sons Okakwu, Ozi and Okwe founded the royal family."""

GOVERNANCE = """The Etulo keep a link with the Kwararafa heritage: according to the Grammar of Etulo, the enthronement of a new Etulo king still requires the blessing of the Aku Uka, the ruler at Wukari in Taraba State. I am Benue calls the Etulo chief the Etsu-Etulo and describes an annual gathering, the Ukpleka festival, at which he confers titles on prominent members of the community."""

SOCIAL = """Age grades remain central to Etulo society: the grammar describes them as its pillars, keeping law and order, settling disputes and providing defence. Before Christianity reached the Etulo, around 1939, people followed a traditional religion centred on one supreme being, Mgbasho, who is revealed through lesser gods such as Esekio, god of the river, and Emakpala, god of thunder. Families kept a sacred enclosure, the ozoka, for family gods and rites, and some of these practices continue. Besides Opleka, the grammar names Agashi, a rite involving the return of ancestral spirits, performed especially when a man faces hardship."""

ECONOMY = """The Etulo are mainly fishermen and farmers. The grammar lists rice, millet and oranges among their crops, and notes Etulo blacksmiths, wood carvers and herbal practitioners; I am Benue also describes them as a fishing and farming people. I am Benue reports that Tiv, Etulo and Jukun fishermen compete in the annual Akata fishing festival at Katsina-Ala, held between March and May."""

LANG_DOC = """Documented in Chikelu I. Ezenwafor-Afuecheta, A Grammar of Etulo (Open Book Publishers, 2025, open access), based on fieldwork in 2014–2015 on the variety of Buruku and Katsina-Ala. The grammar classifies Etulo as Idomoid (after Armstrong 1989 and Williamson and Blench 2000) and notes Tabe's (2007) view that it is close to the Jukunoid languages."""

OPLEKA = """Opleka is a festival of the Etulo people of Benue State. The Grammar of Etulo (2025) describes it as a festival of prayers, traditional dances, sacrifices and rites of passage to the Etulo ancestors and gods.

I am Benue describes what appears to be the same festival under the name Ukpleka: celebrated every August among the Etulo of Katsina-Ala Local Government Area, it brings the sons and daughters of Etulo together before their chief, the Etsu-Etulo, who confers titles on the most prominent of them. That the two names refer to one festival is likely but not confirmed by either source."""

RECORDS = [
    dict(key="opleka", table="cultural_records", evidence="multiple_sources", level="well_documented",
         fields=dict(record_type="festival", cultural_category="festivals_ceremonies", nature="documented_practice",
                     name="Opleka", local_name="Opleka", slug="opleka", language_id="@languages:etulo",
                     timing="Annually in August (as Ukpleka, per I am Benue)", month_from=8, month_to=8, current_status="active", scope_level="ethnic_group",
                     summary="Opleka (Ukpleka) is an Etulo festival of prayers, dances and rites to the ancestors, held in August in Katsina-Ala, when the Etsu-Etulo confers titles.",
                     description=OPLEKA),
         srcs=[("GRAM", "Opleka: prayers, dances, sacrifices, rites to ancestors and gods"),
               ("IAMFEST", "Ukpleka: August, Katsina-Ala, gathering before the Etsu-Etulo, titles")]),
]
UPDATES = [
    dict(ref="@ethnic_groups:etulo", fields=dict(description=DESCRIPTION, origins_and_migration=ORIGINS, traditional_governance=GOVERNANCE,
                                                 social_organisation=SOCIAL, economy_and_occupations=ECONOMY),
         srcs=[("GRAM", "Name, location, 14 clans, Adi, language, multilingualism, endangerment, origins, royal line, Aku Uka, religion, age grades, festivals, livelihoods"),
               ("IAMET", "Buruku and part of Katsina-Ala; fishing and farming"),
               ("IAMFEST", "Etsu-Etulo; Ukpleka; Akata fishing festival")]),
    dict(ref="@languages:etulo", fields=dict(documentation_notes=LANG_DOC, vitality="Critically endangered (Agbedo and Kwambehar 2013, cited in A Grammar of Etulo, 2025)",
                                             vitality_status="endangered", vitality_source_id="@src:GRAM"),
         srcs=[("GRAM", "Grammar; classification; vitality; where spoken")]),
]
NAMES = [
    dict(record="@ethnic_groups:etulo", name="Turu", name_type="exonym",
         usage_notes="Name used by the Tiv, described by the Grammar of Etulo as wrong; the name Etulo was officially adopted in 1976.", srcs=["GRAM"]),
    dict(record="opleka", name="Ukpleka", name_type="spelling_variant", usage_notes="Form used by I am Benue; likely the same festival (not confirmed).", srcs=["IAMFEST"]),
]
RELATIONS = [
    dict(frm="@ethnic_groups:etulo", type="present_in", to="@admin_units:lga:taraba/wukari", source="GRAM", evidence="single_reliable_source", level="well_documented",
         settlement_status="unknown", notes="Etulo are found in Wukari LGA, Taraba State (Grammar of Etulo, 2025)."),
    dict(frm="@languages:etulo", type="spoken_in", to="@admin_units:lga:benue/buruku", source="GRAM", evidence="multiple_sources", level="well_documented",
         speaker_role="first_language", notes="Studied in Buruku and Katsina-Ala (Grammar of Etulo, 2025); I am Benue places the Etulo there."),
    dict(frm="@languages:etulo", type="spoken_in", to="@admin_units:lga:benue/katsina-ala", source="GRAM", evidence="multiple_sources", level="well_documented",
         speaker_role="first_language", notes="Studied in Buruku and Katsina-Ala (Grammar of Etulo, 2025); I am Benue places the Etulo there."),
    dict(frm="@languages:etulo", type="spoken_in", to="@admin_units:lga:taraba/wukari", source="GRAM", evidence="single_reliable_source", level="well_documented",
         speaker_role="first_language", notes="Also spoken in Wukari LGA, Taraba (Grammar of Etulo, 2025)."),
    dict(frm="@ethnic_groups:etulo", type="shared_ancestry_tradition", to="@ethnic_groups:jukun", source="GRAM", evidence="oral_tradition", level="reported",
         notes="Tradition of a common Kwararafa origin with the Jukun (Tabe 2007, via the Grammar of Etulo); the Aku Uka's blessing is still needed to enthrone an Etulo king."),
    dict(frm="opleka", type="celebrated_by", to="@ethnic_groups:etulo", source="GRAM", evidence="multiple_sources", level="well_documented", notes="Grammar of Etulo; I am Benue."),
    dict(frm="opleka", type="celebrated_in", to="@admin_units:lga:benue/katsina-ala", source="IAMFEST", evidence="single_reliable_source", level="reported",
         notes="I am Benue places the Ukpleka festival among the Etulo of Katsina-Ala."),
]
GAPS = [
    ("Etsu-Etulo", "The title, seat and present holder of the Etulo chief are given only by I am Benue (title). Needs official or scholarly confirmation, and his place under the 2016 chieftaincy law."),
    ("Opleka and Ukpleka", "Whether the grammar's Opleka and I am Benue's Ukpleka are one festival; its date in the Buruku clans."),
    ("Akata fishing festival", "Given only by I am Benue (Tiv, Etulo and Jukun fishermen, March–May, Katsina-Ala). Not recorded as a record yet."),
    ("Etulo population", "Speaker estimates range from 10,000 to 100,000 (as reported by the grammar); no reliable count."),
    ("Etulo clan spellings", "The clan names are given here in simplified spelling; the grammar writes them with Etulo letters (e.g. Agbɔ, Oʃafu, Ingwaʤɛ)."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Benue (Phase 3) culture, part 2: the Etulo people, language and the Opleka festival.")


def quote(t):
    return [f"> {p}" if p else ">" for p in t.split("\n")]


def report():
    total = sum(words(x) for x in (DESCRIPTION, ORIGINS, GOVERNANCE, SOCIAL, ECONOMY))
    L = ["# Research batch 026 — Benue culture (2): the Etulo", "",
         f"Researched {ACCESSED}. Phase 3, gap G-05, part 2. Created in review; published only after your approval.", "",
         f"- **Etulo people** (existing record, empty fields filled): about {total} words in five sections, so the page becomes indexable (sitemap +1).",
         "- **Etulo language** (existing record): documentation note, vitality *critically endangered* (with its source) and controlled status *endangered*.",
         f"- **Opleka (Ukpleka)**, a new festival record: {words(OPLEKA)} words (noindex).",
         f"- {len(RELATIONS)} new links; 2 names (Turu, Ukpleka).", "",
         "## Etulo — Overview", ""] + quote(DESCRIPTION) + ["", "## Origins & migration", ""] + quote(ORIGINS) + \
        ["", "## Traditional governance", ""] + quote(GOVERNANCE) + ["", "## Social organisation", ""] + quote(SOCIAL) + \
        ["", "## Economy & occupations", ""] + quote(ECONOMY) + ["", "## Etulo language — documentation note", ""] + quote(LANG_DOC) + \
        ["", "## Opleka", ""] + quote(OPLEKA)
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s.get('author', s.get('organisation', ''))}{', ' + s['publication_date'] if s.get('publication_date') else ''}). {s['url']}. Tier {s['source_tier']}." for k, s in SOURCES.items()]
    L += ["", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_026_benue_etulo.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_026_benue_etulo_REVIEW.md", "w").write(report())
    print(f"etulo={sum(words(x) for x in (DESCRIPTION, ORIGINS, GOVERNANCE, SOCIAL, ECONOMY))} opleka={words(OPLEKA)} relations={len(RELATIONS)}")
