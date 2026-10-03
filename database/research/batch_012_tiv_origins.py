"""
Research batch 012 — Tiv origins and migration (researched 2026-09-25). Owner's option (a):
keep oral tradition and scholarship apart.

Fills the EMPTY origins_and_migration field of the national Tiv record (ethnic_groups:tiv);
never overwrites. Nothing in the Tiv Heritage collection is changed.

Sources read directly:
  * T. S. Nomishan, "Swem: The Tangible and Intangible Cultural Heritage of the Tiv of Central
    Nigeria", Tourism & Heritage Journal 3 (2021), 56-67 (peer reviewed; field interviews 2019
    plus a literature review). Used for Swem's two meanings, the five scholarly placements of
    Swem, the traditional reasons for leaving it, the dating evidence and the oath's decline.
  * Paul Bohannan, "Tiv", Encyclopedia of World Cultures. Used for "coming down" from the
    southeast, the Fulani joking relationship, the genealogy depth and the 1852/1879 records.
  * Glottolog (Tivoid within Southern Bantoid).
  * Wikipedia, "Tiv people" — ONLY to report the popular Congo-route account, attributed.
Works cited through Nomishan (Akiga 1939, Bohannan & Bohannan 1954, Makar 1975, Gbor 1978,
Orkar 1979, Dzurgba 2007, the archaeological dating studies) were not read directly and are
named as Nomishan's summary.
"""
import json, re, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "NOM": dict(source_type="journal_article", title="Swem: The Tangible and Intangible Cultural Heritage of the Tiv of Central Nigeria", author="Nomishan, T. S.",
                url="https://revistes.ub.edu/index.php/tourismheritage/article/view/37430", verification_status="needs_corroboration", notes="Reused (existing source)."),
    "BOH": dict(source_type="encyclopedia", title="Tiv", author="Paul Bohannan", organisation="Encyclopedia of World Cultures (Gale)",
                url="https://www.encyclopedia.com/humanities/encyclopedias-almanacs-transcripts-and-maps/tiv", verification_status="needs_corroboration", notes="Reused (batch 003)."),
    "GLOT": dict(source_type="dataset", title="Tiv (Glottocode tivv1240)", organisation="Glottolog (Max Planck Institute for Evolutionary Anthropology)",
                 url="https://glottolog.org/resource/languoid/id/tivv1240", verification_status="needs_corroboration", notes="Reused (batch 003)."),
    "WTP": dict(source_type="encyclopedia", title="Tiv people", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Tiv_people",
                verification_status="needs_corroboration", notes="Reused (existing source)."),
}

ORIGINS = """Tiv accounts of where they came from and the findings of researchers are set out separately here, because they answer different questions and do not always agree. The Tiv Heritage collection tells the story at greater length (see the links below).

What Tiv tradition says. All Tiv trace their descent from one ancestor, Tiv, and through his two sons, Ichongo and Ipusu, to the two great divisions of the people. Paul Bohannan, in the Encyclopedia of World Cultures, records that the Tiv speak of having "come down" into their present land from the southeast, and that on the way they met the Fulani, with whom they still keep a joking relationship. Tradition names Swem as the place from which the Tiv spread out. T. S. Nomishan, in a 2021 peer-reviewed study, explains that Swem has two meanings for the Tiv: the ancestral home of the whole people, and a powerful oath of justice. The tradition he recorded gives three reasons for leaving Swem: attacks by neighbouring peoples such as the Bafum, a growing population, and the need for more farmland.

As an oath, Swem was sworn to settle disputes and accusations of witchcraft. Nomishan reports that its use declined sharply after Pentecostal campaigns against it from the 1990s, although some communities still swore it, for example in Ushongo in 2019 and Katsina-Ala in 2020.

Where Swem is. Researchers disagree. Nomishan sets out five placements: a hill in the Iyon area of south-eastern Tivland, which Akiga said he visited in 1934; Ngol-Kedju hill in the Bamenda highlands of Cameroon (Paul and Laura Bohannan, 1954); a hill south-west of Nyiev-Ya in today's Kwande LGA (Makar, 1975); a hill on the Nigeria–Cameroon border south-east of Tivland (Gbor, 1978; Orkar, 1979); and a hill at the source of the Katsina-Ala River in the Akwaya area of Cameroon (Dzurgba, 2007). Peoples of the Akwaya area, among them the Iyon, Ugbe and Utange, also call that hill Swem and claim kinship with the Tiv. Nomishan concludes that the site has not yet been scientifically identified.

What can be dated. Nomishan summarises archaeological studies whose dates for Tiv presence in the Middle Benue Valley cluster around the 15th and 16th centuries. Bohannan notes that genealogies collected in the early 1950s ran fourteen to eighteen generations from Tiv to living elders, and warns that they cannot be read as a simple count of years. He also records that Europeans first reported Tiv on the banks of the Benue in 1852.

Language. Glottolog classifies Tiv in the Tivoid group of Southern Bantoid, within Benue–Congo. This relates Tiv to the Bantu languages and fits a south-eastern origin, but it does not by itself show a route or a date.

A popular account, repeated on Wikipedia, has the Tiv wandering through southern and central Africa and returning by way of the Congo before settling at Swem around 1600. The scholarly sources used here do not describe such a route, and the dates Nomishan cites place the Tiv in the Benue Valley before 1600."""


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


UPDATES = [
    dict(ref="@ethnic_groups:tiv", fields=dict(origins_and_migration=ORIGINS),
         srcs=[("NOM", "Swem: ancestral home and oath; reasons for leaving; five placements of Swem; Akwaya peoples; dates cluster 15th–16th c.; oath's decline"),
               ("BOH", "'Coming down' from the southeast; Fulani joking relationship; genealogies 14–18 generations; Tiv on the Benue in 1852"),
               ("GLOT", "Tiv within Tivoid, Southern Bantoid, Benue–Congo"),
               ("WTP", "The popular Congo-route account (reported, not adopted)")]),
]

GAPS = [
    ("Primary works on Tiv origins not read directly", "Bohannan's 'The Migration and Expansion of the Tiv' (Africa 24, 1954), Akiga's Story (1939), Makar (1975), Gbor (1978), Orkar (1979) and Dzurgba (2007) are known here only through Nomishan's summary. Reading them would allow direct citation."),
    ("The archaeological dating studies", "The 15th–16th-century dates come from Nomishan's summary of Tubosun, Andah, Ogundele and Orijemie. The site names, the methods and the exact dates should be taken from the original reports."),
    ("The Tiv collection's origins article and the dates", "The Tiv collection article 'The Origins of the Tiv People' says archaeology has not confirmed the Swem/Congo homeland; Nomishan cites archaeological dates for Tiv presence in the Benue Valley. The two statements answer different questions and do not conflict, but the owner may want the Tiv article to mention the dates."),
    ("Swem as a place record", "Not created: its location is unknown (five placements)."),
    ("The 18th-century expansion", "The Tiv expansion across the Benue Valley (the Tiv collection's timeline, from Chia and others) belongs in the national record's History field, left for a later batch."),
    ("Wikipedia's history section", "Its Congo-route account, its 1600 settlement date and its account of early European contact are not used. Its account of first contact in the 18th century conflicts with Bohannan (1852)."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], updates=UPDATES, gaps=GAPS,
                relations=[], statistics=[], scope="Tiv origins and migration: oral tradition and scholarship kept apart (fills the empty field of the national Tiv record).")


def report():
    L = ["# Research batch 012 — Tiv origins and migration", "",
         f"Researched {ACCESSED}. Owner's option (a). The text fills the empty 'Origins & Migration' section of the national Tiv page (/nigeria/ethnic-groups/tiv). That page is already published, so **the text goes live the moment it is imported**.", "",
         f"- New prose: **{words(ORIGINS)} words**. Together with the existing overview, the Tiv page passes the 300-word rule and becomes indexable, so the sitemap grows by 1.",
         "- Nothing in the Tiv Heritage collection is changed. The page's 'From the Tiv Heritage collection' block links to the fuller articles.", "",
         "## Sources", ""] + [f"- **{k}** — {s['title']} ({s.get('organisation') or s.get('author')}). {s['url']}." for k, s in SOURCES.items()]
    L += ["", "## Origins & Migration (new text)", ""] + [f"> {p}" if p else ">" for p in ORIGINS.split("\n")]
    L += ["", "## How each statement is supported", "", "| Statement | Source | Kind |", "|---|---|---|",
          "| Descent from Tiv through Ichongo and Ipusu | Bohannan (single patrilineage, two sons) | tradition, reported by a scholar |",
          "| 'Came down' from the southeast; met the Fulani; joking relationship | Bohannan | tradition, reported by a scholar |",
          "| Swem: ancestral home and oath of justice | Nomishan (fieldwork 2019 and literature) | tradition, reported by a scholar |",
          "| Reasons for leaving Swem | Nomishan | tradition |",
          "| Oath's decline since the 1990s; Ushongo 2019, Katsina-Ala 2020 | Nomishan | scholarship (fieldwork) |",
          "| Five placements of Swem; site not identified | Nomishan, summarising Akiga, the Bohannans, Makar, Gbor, Orkar and Dzurgba | scholarship |",
          "| Tiv presence in the Benue Valley dated to the 15th–16th centuries | Nomishan, summarising archaeological studies | scholarship |",
          "| Genealogies of 14–18 generations are not a year count; first European report in 1852 | Bohannan | scholarship |",
          "| Tiv in Tivoid, Southern Bantoid | Glottolog | linguistics |",
          "| Congo-route account, settlement around 1600 | Wikipedia | popular account, reported and not adopted |",
          "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "",
          "- The Tiv page becomes indexable, so the sitemap grows by 1 (2,988 → 2,989). The text is original and differs in structure and content from the Tiv collection's articles: it is an evidence map, not a narrative, and it links to them.",
          "- Swem as a living oath (witchcraft accusations) is described neutrally, as Nomishan reports it.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_012_tiv_origins.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_012_tiv_origins_REVIEW.md", "w").write(report())
    print(f"origins words={words(ORIGINS)}")
