"""
Research batch 033 — Nasarawa (Phase 3): traditional institutions, part 1. Researched 2026-09-26.

Records: the emirates of Lafia, Keffi and Nasarawa (the three oldest stools) and the Alago kingdoms
of Doma (the Andoma) and Keana (the Osana). Sources: Daily Trust (2012) for the 22 first-class stools;
Wikipedia (Lafia, Keffi, Doma, Keana) — attributed throughout; the Nasarawa State Government (history).
Founding dates of Doma (1232) and Keana (12th century) are traditions and are labelled so.
Britannica could not be used (automated access blocked). A 2023 report of new first-class stools
(Sahara Reporters) is internally inconsistent (says four, names six) — not used; gap.
"""
import json, re, sys

ACCESSED = "2026-09-26"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "DT12": dict(source_type="news", source_kind="news", source_tier=3, title="The Story Of Nasarawa State's 22 First Class Chiefs", author="Hir Joseph",
                 organisation="Daily Trust", publication_date="2012-08-18", url="https://dailytrust.com/the-story-of-nasarawa-states-22-first-class-chiefs/",
                 verification_status="needs_corroboration",
                 notes="Twenty-two first-class stools: the Emirs of Lafia, Keffi and Nasarawa (dating back to the early colonial era); the Andoma of Doma, Aren Eggon, Osana of Keana, Emir of Awe, Oriye Rindri, Ohimege Opanda, Esu Karu, Osu Ajiri, Emir of Karshi, She Migili, Odyong Nyankpa, Emir of Azara, Chun Mada; Abaga Toni, Gomo Babye, Osuko of Obi, Gom Mama, Sarkin Loko and Sangarin Kwandere."),
    "WLAF": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Lafia", organisation="Wikipedia", url=W("Lafia"), verification_status="needs_corroboration",
                 notes="Founded by Muhammadu Dunama in the late 18th century south of Shabu village; chiefdom; Mohamman Agwai (1881–1903), Lafia market, trade route to Loko; 1903 British recognised Chief Musa as first emir; Lafia Division of Benue Province; ruling houses Ari and Dallah Dunama (Kanuri, Bare-Bari); 17th Emir Sidi Bage Muhammad I succeeded Isa Mustapha Agwai I (1976–2019)."),
    "WKEF": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Keffi", organisation="Wikipedia", url=W("Keffi"), verification_status="needs_corroboration",
                 notes="Founded around 1802 by Abdu Zanga, a Fulani leader who took the title of emir; subject to Zaria emirate with annual tribute of slaves; 1902: the magaji (Zaria's representative) killed a British officer, pretext for Lugard's invasion of the northern caliphate."),
    "WDOM": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Doma, Nigeria", organisation="Wikipedia", url=W("Doma, Nigeria"), verification_status="needs_corroboration",
                 notes="Kingdom of Doma said to be founded in 1232 by Andoma (a 'popular tale'), part of the British protectorate from 1901; Alago; migration traditions from Apa/Kwararafa via Idah, Ogyogo and Obasidoma (Keana); list of rulers from John Stewart, African States and Rulers (2005), Andoma (1232) to Atta IV (1901–1930); present ruler the 43rd Andoma; Odu annual festival; Alago in the north and Bassa in the south of the LGA."),
    "WKEA": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Keana", organisation="Wikipedia", url=W("Keana"), verification_status="needs_corroboration",
                 notes="Keana said to be founded by Akyana Adi in the 12th century; 'home of salt'; with Doma, Obi, Agwatashi and Assakio a major centre of the Alago; 34th Osana; Osana, Andoma and Osuko of Obi among the most powerful Alago rulers."),
    "NSG": dict(source_type="official_website", title="About Nasarawa State", organisation="Nasarawa State Government", url="https://nasarawastate.gov.ng/about-nasarawa/",
                verification_status="needs_corroboration", notes="Reused (1902: the Emir of Nasarawa submitted to the British; Nasarawa Province headquarters moved to Nasarawa town)."),
}

LAFIA = """The Lafia Emirate, with its seat at Lafia, the capital of Nasarawa State, is one of the three oldest traditional stools of the state; Daily Trust (2012) dates the emirates of Lafia, Keffi and Nasarawa back to the early colonial era.

According to Wikipedia, Lafia was founded in the late eighteenth century by Muhammadu Dunama, south of Shabu village, and became the capital of an important chiefdom in the late nineteenth century. Under Mohamman Agwai (1881–1903) its market became one of the most important in the Benue valley, with a trade route to Loko, a river port on the Benue. In 1903 the British recognised Musa as Lafia's first emir, and the emirate formed most of the Lafia Division of Benue Province.

Wikipedia names two Kanuri (Bare-Bari) ruling houses, Ari and Dallah Dunama. It records Isa Mustapha Agwai I as the longest-reigning emir (1976–2019), succeeded by Sidi Bage Muhammad I, a retired Justice of the Supreme Court, as the seventeenth emir."""

KEFFI = """The Keffi Emirate is one of the three oldest traditional stools of Nasarawa State (Daily Trust, 2012). According to Wikipedia, Keffi was founded around 1802 by Abdu Zanga, a Fulani leader who took the title of emir; his small state was subject to the Zaria emirate, to which it paid an annual tribute of slaves.

Wikipedia also records that in 1902 Keffi was the scene of an incident that led to the British invasion of northern Nigeria: the magaji, Zaria's representative at Keffi, killed a British officer, and his flight to Kano gave Frederick Lugard the pretext to invade the caliphate."""

NASARAWA = """The Nasarawa Emirate, with its seat at Nasarawa town, is one of the three oldest traditional stools of Nasarawa State, dating back to the early colonial era (Daily Trust, 2012). The Nasarawa State Government records that in 1902, after the Emir of Nasarawa submitted to the British, the colonial Lower Benue Province was renamed Nasarawa Province and its headquarters moved to Nasarawa town; the state takes its name from the emirate and town. Its earlier history is not yet recorded here."""

DOMA = """The Andoma of Doma is the traditional ruler of Doma, a kingdom of the Alago people and one of Nasarawa State's first-class stools (Daily Trust, 2012). Wikipedia describes the Alago as the main people of Doma LGA, with the Bassa numerous in its south.

By tradition — Wikipedia calls it a popular tale — the kingdom was founded in 1232 by Andoma, whose people had come from Apa, the seat of the old Kwararafa confederacy, by way of Idah, Ogyogo on the Benue and Obasidoma in present-day Keana. The kingdom became part of the British protectorate of Northern Nigeria in 1901. Wikipedia gives a list of rulers taken from John Stewart's African States and Rulers (2005), from Andoma to Atta IV (1901–1930); the present ruler is described as the 43rd Andoma. Doma's annual festival is named as Odu."""

KEANA = """The Osana of Keana is the traditional ruler of Keana, one of the main centres of the Alago people and one of Nasarawa State's first-class stools (Daily Trust, 2012). Wikipedia calls Keana the "home of salt" and, with Doma, Obi, Agwatashi and Assakio, one of the major towns of the Alago nation. By tradition Keana was founded in the twelfth century by Akyana Adi. The present ruler is described as the 34th Osana, and Wikipedia counts the Osana, the Andoma of Doma and the Osuko of Obi among the most powerful Alago rulers."""

RECORDS = [
    dict(key="lafia", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="emirate", name="Lafia Emirate", slug="lafia-emirate", is_extant=1, founded_text="late 18th century (town founded, per Wikipedia); first emir recognised 1903",
                     founded_precision="century", summary="The Lafia Emirate, seated at Lafia, is one of the three oldest traditional stools of Nasarawa State; its ruling houses are Kanuri.", description=LAFIA),
         srcs=[("DT12", "One of the three oldest stools"), ("WLAF", "Founding, Mohamman Agwai, 1903, ruling houses, emirs")]),
    dict(key="keffi", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="emirate", name="Keffi Emirate", slug="keffi-emirate", is_extant=1, founded_year=1802, founded_text="around 1802 (Wikipedia)", founded_precision="circa",
                     summary="The Keffi Emirate, founded around 1802 by Abdu Zanga, is one of the three oldest traditional stools of Nasarawa State.", description=KEFFI),
         srcs=[("DT12", "One of the three oldest stools"), ("WKEF", "Founding c. 1802; Zaria; 1902 incident")]),
    dict(key="nasarawa", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="emirate", name="Nasarawa Emirate", slug="nasarawa-emirate", is_extant=1,
                     summary="The Nasarawa Emirate, seated at Nasarawa town, is one of the three oldest traditional stools of Nasarawa State, which is named after it.", description=NASARAWA),
         srcs=[("DT12", "One of the three oldest stools"), ("NSG", "1902 submission; Nasarawa Province")]),
    dict(key="doma", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="kingdom", name="Andoma of Doma", slug="andoma-of-doma", is_extant=1, founded_year=1232, founded_text="1232 (tradition)", founded_precision="year",
                     summary="The Andoma of Doma rules Doma, an Alago kingdom of Nasarawa State that by tradition was founded in 1232; a first-class stool.", description=DOMA),
         srcs=[("DT12", "First-class stool"), ("WDOM", "Tradition of 1232; migration; rulers list (Stewart 2005); Odu")]),
    dict(key="keana", table="polities", evidence="multiple_sources", level="reported",
         fields=dict(polity_type="kingdom", name="Osana of Keana", slug="osana-of-keana", is_extant=1, founded_text="12th century (tradition)", founded_precision="century",
                     summary="The Osana of Keana rules Keana, an Alago centre known for its salt; a first-class stool of Nasarawa State.", description=KEANA),
         srcs=[("DT12", "First-class stool"), ("WKEA", "Home of salt; Alago centres; tradition of founding; 34th Osana")]),
]
RELATIONS = [
    dict(frm="lafia", type="located_in", to="@admin_units:lga:nasarawa/lafia", source="WLAF", evidence="multiple_sources", level="reported", notes="Seat at Lafia."),
    dict(frm="lafia", type="associated_with", to="@ethnic_groups:kanuri", role="ruling houses", source="WLAF", evidence="single_reliable_source", level="reported",
         notes="Wikipedia: the ruling houses Ari and Dallah Dunama are Kanuri (Bare-Bari)."),
    dict(frm="keffi", type="located_in", to="@admin_units:lga:nasarawa/keffi", source="WKEF", evidence="multiple_sources", level="reported", notes="Seat at Keffi."),
    dict(frm="nasarawa", type="located_in", to="@admin_units:lga:nasarawa/nasarawa", source="NSG", evidence="multiple_sources", level="reported", notes="Seat at Nasarawa town."),
    dict(frm="doma", type="located_in", to="@admin_units:lga:nasarawa/doma", source="WDOM", evidence="multiple_sources", level="reported", notes="Seat at Doma."),
    dict(frm="doma", type="associated_with", to="@ethnic_groups:alago", role="Alago kingdom", source="WDOM", evidence="single_reliable_source", level="reported", notes="Wikipedia (Doma, Nigeria)."),
    dict(frm="keana", type="located_in", to="@admin_units:lga:nasarawa/keana", source="WKEA", evidence="multiple_sources", level="reported", notes="Seat at Keana."),
    dict(frm="keana", type="associated_with", to="@ethnic_groups:alago", role="Alago kingdom", source="WKEA", evidence="single_reliable_source", level="reported", notes="Wikipedia (Keana)."),
    dict(frm="doma", type="historically_related", to="@ethnic_groups:jukun", source="WDOM", evidence="oral_tradition", level="reported",
         notes="Tradition that the Alago of Doma came from Apa, seat of the Kwararafa confederacy (Wikipedia)."),
]
GAPS = [
    ("The other first-class stools", "Daily Trust (2012) lists 22 first-class stools; only five are recorded here. The others (Aren Eggon, Emir of Awe, Oriye Rindri, Ohimege Opanda, Esu Karu, Osu Ajiri, Emir of Karshi, She Migili, Odyong Nyankpa, Emir of Azara, Chun Mada, Abaga Toni, Gomo Babye, Osuko of Obi, Gom Mama, Sarkin Loko, Sangarin Kwandere) need sources."),
    ("Stools created after 2012", "A 2023 report (Sahara Reporters) of new first-class chiefdoms is internally inconsistent; needs the state gazette or the council of chiefs."),
    ("Nasarawa State Council of Chiefs and chieftaincy law", "Not yet sourced: its chairman, composition and the governing law."),
    ("Early history of the Nasarawa and Keffi emirates", "Only outlined from Wikipedia; needs scholarly histories of the Sokoto-era emirates of the Benue valley."),
    ("Doma rulers list", "Taken by Wikipedia from John Stewart, African States and Rulers (2005); the book itself not consulted."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=[], gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Nasarawa traditional institutions (1): Lafia, Keffi and Nasarawa emirates; the Andoma of Doma; the Osana of Keana.")


def report():
    L = ["# Research batch 033 — Nasarawa: traditional institutions (1)", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         f"- Five records: Lafia Emirate ({words(LAFIA)} words), Keffi Emirate ({words(KEFFI)}), Nasarawa Emirate ({words(NASARAWA)}), Andoma of Doma ({words(DOMA)}), Osana of Keana ({words(KEANA)}).",
         f"- {len(RELATIONS)} links: each to its LGA; Lafia to the Kanuri (ruling houses); Doma and Keana to the Alago; Doma's tradition of a Kwararafa origin (as tradition).",
         "- All under 300 words, so they stay noindex (sitemap unchanged). Founding dates for Doma (1232) and Keana (12th century) are marked as **tradition**.",
         "- **The 22 first-class stools** (Daily Trust, 2012):",
         "  - the Emirs of Lafia, Keffi and Nasarawa",
         "  - the Andoma of Doma, Aren Eggon, Osana of Keana, Emir of Awe, Oriye Rindri, Ohimege Opanda, Esu Karu, Osu Ajiri, Emir of Karshi, She Migili, Odyong Nyankpa, Emir of Azara, Chun Mada",
         "  - Abaga Toni, Gomo Babye, Osuko of Obi, Gom Mama, Sarkin Loko, Sangarin Kwandere",
         "  - Only five are recorded now; the rest are a research gap.", ""]
    for n, t in (("Lafia Emirate", LAFIA), ("Keffi Emirate", KEFFI), ("Nasarawa Emirate", NASARAWA), ("Andoma of Doma", DOMA), ("Osana of Keana", KEANA)):
        L += [f"## {n}", ""] + [f"> {p}" if p else ">" for p in t.split("\n")] + [""]
    L += ["## Not used", "", "- Britannica (automated access blocked).",
          "- Sahara Reporters (2023) on new first-class stools: it says four, then names six, and cites no law.", "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_033_nasarawa_rulers.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_033_nasarawa_rulers_REVIEW.md", "w").write(report())
    print("ok", [words(t) for t in (LAFIA, KEFFI, NASARAWA, DOMA, KEANA)])
