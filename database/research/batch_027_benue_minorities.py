"""
Research batch 027 — Benue culture (3): the Akweya, Ufia and Nyifon (gap G-05, part 3).
Researched 2026-09-26. Fills EMPTY description fields only; no new records, no fix script.

Sources are thin for these small peoples, so the texts are short and attributed:
  * Akweya: akweya.com (community site) for self-name, official name and district; Glottolog
    (Akpa: Idomoid, Yatye–Akpa). Blog accounts of a 16th-century migration are NOT used.
  * Ufia: Pulse (2025) for location and customs; a 2021 opinion article (National Record) for the
    identity debate and a migration account, attributed as one writer's view.
  * Nyifon: Glottolog (a dialect of Wapan, Jukunoid) and Wikipedia (about 1,000 speakers in the
    1990s); Pulse (2025) for location and endangerment. The archive lists Nyifon as a language;
    Glottolog as a dialect — recorded as a classification gap, the record type is not changed.
  * Jukun (Benue): nothing added. The only new source (Pulse) places Benue's Jukun "in border areas
    like Wukari", but Wukari is in Taraba, so it is not used.
"""
import json, re, sys

ACCESSED = "2026-09-26"
SOURCES = {
    "AKW": dict(source_type="website", source_kind="community_organisation", source_tier=4, title="About Akweya People",
                organisation="akweya.com (AkweyaTV)", url="https://www.akweya.com/p/about-akweya-people.html", verification_status="needs_corroboration",
                notes="Self-name Akweya ('sons of Akwu'); outsiders say Akpa, 'a distortion of the word meaning people from Apa'; Akpa used officially by government since 1950; Akpa District in Otukpo LGA, bordered by Otukpo district (north), Oglewu in Ohimini (north-west), Edumoga in Okpokwu (west), Obi-Ito-Igede (east) and Ufia-Utonkon in Ado (south); Ohmenyi river (R. Okpokwu) bisects the district."),
    "GLAKPA": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Akpa (akpa1238)", organisation="Glottolog",
                   url="https://glottolog.org/resource/languoid/id/akpa1238", verification_status="verified",
                   notes="Akpa language: Atlantic-Congo > Volta-Congo > Benue-Congo > Idomoid > Yatye-Akpa. ISO 639-3 akf."),
    "GLNYI": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Nyifon (nyif1234)", organisation="Glottolog",
                  url="https://glottolog.org/resource/languoid/id/nyif1234", verification_status="verified",
                  notes="Nyifon: level 'dialect'; Benue-Congo > Jukunoid > Central Jukunoid > Jukun-Mbembe-Wurbo > Jukun > Kororofa > Wapan."),
    "WNYI": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Nyifon language", organisation="Wikipedia",
                 url="https://en.wikipedia.org/wiki/Nyifon_language", verification_status="needs_corroboration",
                 notes="Nyifon (Iordaa), a poorly known Jukunoid language of Buruku LGA; perhaps about 1,000 speakers in the 1990s; Glottolog lists it as a dialect of Wapan."),
    "WAKPA": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Akpa language", organisation="Wikipedia",
                  url="https://en.wikipedia.org/wiki/Akpa_language", verification_status="needs_corroboration",
                  notes="Akpa (Akweya) is an Idomoid language spoken in Ohimini and Oturkpo LGAs, Benue State."),
    "PULSE": dict(source_type="news", source_kind="news", source_tier=3, title="Benue State Tribes: Meet The Indigenous Groups", author="Anna Ajayi",
                  organisation="Pulse Nigeria", publication_date="2025-06-17", url="https://www.pulse.ng/articles/lifestyle/tribes-in-benue-state-2025061717333829037",
                  verification_status="needs_corroboration",
                  notes="Nyifon live in a small part of Buruku LGA, mostly near the Benue River; their language, spoken by a few thousand, is endangered. Utonkon (Ufia) are a minority in parts of Ado LGA with a distinct dialect and their own rites of passage, marriage customs and music styles. (Its placing of Benue's Jukun 'in border areas like Wukari' is not used: Wukari is in Taraba.)"),
    "NR": dict(source_type="news", source_kind="news", source_tier=4, title="DEBATE: Ufia People are NOT Idoma", author="Adakole Ijogi",
               organisation="National Record", publication_date="2021-03-06", url="https://www.nationalrecord.com.ng/debate-ufia-people-are-not-idoma/",
               verification_status="needs_corroboration",
               notes="Opinion article (tier lowered to 4 as opinion). If Idoma is a language the Ufia are not Idoma; if Idoma is a people, they are. The Ufia occupy Utonkon in Ado LGA; their language is related to Ekoi, Ukelle and other Cross River languages; migration from northern Cross River through Ebonyi State in the early 16th century."),
}

AKWEYA = """The Akweya are a people of the Idoma area of Benue State. According to akweya.com, a community website, they call themselves Akweya, "sons of Akwu", while outsiders call them Akpa, a name the site explains as a distortion of a word meaning "people from Apa" and which government has used officially since 1950.

The same site places them in Akpa District of Otukpo Local Government Area. The district borders Otukpo to the north, Oglewu in Ohimini to the north-west, Edumoga in Okpokwu to the west, Obi and the Igede to the east, and the Ufia of Utonkon in Ado to the south, and the Ohmenyi (Okpokwu) river runs through it.

Their language, Akpa, is classified by Glottolog as an Idomoid language of the Yatye–Akpa group, and Wikipedia places it in Ohimini and Otukpo local government areas. Accounts of Akweya origins found online differ and are not yet supported by reliable sources."""

UFIA = """The Ufia, also called Utonkon, live at Utonkon in Ado Local Government Area of Benue State. Pulse (2025) describes them as a small minority with their own dialect, rites of passage, marriage customs and music. Their language belongs to the Oring (Korring) cluster of the Upper Cross River languages, not to the Idomoid group to which Idoma belongs.

Whether the Ufia are Idoma is debated. In a 2021 opinion article in National Record, Adakole Ijogi argued that the answer depends on what "Idoma" means: by language the Ufia are not Idoma, but as members of the wider Idoma people they are. He also wrote that the Ufia came from northern Cross River State through Ebonyi State in the early sixteenth century. This is one writer's account and has not been confirmed."""

NYIFON = """The Nyifon live in a small part of Buruku Local Government Area, Benue State; Pulse (2025) places them mostly near the Benue River. Their language, also called Iordaa, is little documented. Glottolog classifies Nyifon as a dialect of Wapan, the Jukun language of Wukari, within the Jukunoid group, whereas other listings, and this archive, treat it as a language of its own. Wikipedia records an estimate of about 1,000 speakers in the 1990s, and Pulse describes the language as endangered, spoken by a few thousand people. No reliable account of Nyifon history or customs has been found yet."""

AKPA_LANG = """Akpa, also called Akweya, is the language of the Akweya people of Benue State. Glottolog classifies it as an Idomoid language of the Yatye–Akpa group, and Wikipedia places it in Ohimini and Otukpo local government areas."""

UPDATES = [
    dict(ref="@ethnic_groups:akweya", fields=dict(description=AKWEYA),
         srcs=[("AKW", "Self-name, exonym, official name since 1950, Akpa District and its borders"), ("GLAKPA", "Akpa: Idomoid, Yatye–Akpa"),
               ("WAKPA", "Akpa spoken in Ohimini and Otukpo")]),
    dict(ref="@ethnic_groups:ufia", fields=dict(description=UFIA),
         srcs=[("PULSE", "Utonkon, Ado LGA; dialect and customs"), ("NR", "Identity debate; migration account (opinion)")]),
    dict(ref="@ethnic_groups:nyifon", fields=dict(description=NYIFON),
         srcs=[("PULSE", "Buruku, near the Benue River; endangered"), ("GLNYI", "Dialect of Wapan (Jukunoid)"), ("WNYI", "Iordaa; about 1,000 speakers in the 1990s")]),
    dict(ref="@languages:akpa", fields=dict(description=AKPA_LANG),
         srcs=[("GLAKPA", "Classification"), ("WAKPA", "Ohimini and Otukpo")]),
]
GAPS = [
    ("Nyifon: language or dialect", "Glottolog classes Nyifon as a dialect of Wapan (Jukun of Wukari); the archive and other listings treat it as a language. Needs a linguistic study (e.g. Blench's Jukunoid work)."),
    ("Akweya and Ufia origins", "Online accounts (migration from Cross River in the 16th century; links to Kwararafa, Igala, Igbo) are unsourced or one writer's view."),
    ("Akweya traditional institutions", "The Akweya ruler and his place under the Idoma Area Traditional Council are not documented."),
    ("Jukun in Benue", "Beyond the Abinsi (Wannu) community already recorded, no reliable source was found. Pulse's 'Wukari' is in Taraba."),
    ("Culture of the Akweya, Ufia and Nyifon", "Festivals, food, dress and crafts: no reliable source found yet."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS, updates=UPDATES,
                relations=[], statistics=[], scope="Benue (Phase 3) culture, part 3: the Akweya, Ufia and Nyifon (descriptions).")


def quote(t):
    return [f"> {p}" if p else ">" for p in t.split("\n")]


def report():
    L = ["# Research batch 027 — Benue culture (3): the Akweya, Ufia and Nyifon", "",
         f"Researched {ACCESSED}. Phase 3, gap G-05, part 3. Fills **empty** description fields only; nothing already written changes. No new records and no fix script.", "",
         f"- Akweya: {words(AKWEYA)} words · Ufia: {words(UFIA)} · Nyifon: {words(NYIFON)} · Akpa language: {words(AKPA_LANG)}. All stay under 300 words (noindex); sitemap unchanged.",
         "- The sources for these small peoples are thin (community site, a news feature, an opinion article, Glottolog, Wikipedia), so every statement is attributed, and origins are left as gaps.", "",
         "## Akweya", ""] + quote(AKWEYA) + ["", "## Ufia (Utonkon)", ""] + quote(UFIA) + ["", "## Nyifon", ""] + quote(NYIFON) + \
        ["", "## Akpa language", ""] + quote(AKPA_LANG)
    L += ["", "## Sources", ""] + [f"- **{k}** — {s['title']} ({s.get('author', s.get('organisation', ''))}{', ' + s['publication_date'] if s.get('publication_date') else ''}). {s['url']}. Tier {s['source_tier']}." for k, s in SOURCES.items()]
    L += ["", "## Not used", "", "- Blog accounts of Akweya and Ufia migrations (unsourced).",
          "- Pulse's placing of Benue's Jukun 'in border areas like Wukari' (Wukari is in Taraba).", "", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_027_benue_minorities.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_027_benue_minorities_REVIEW.md", "w").write(report())
    print(f"akweya={words(AKWEYA)} ufia={words(UFIA)} nyifon={words(NYIFON)} akpa={words(AKPA_LANG)}")
