"""
Research batch 011 — the Tiv people and the Tiv language linked to LGAs of Plateau and
Cross River states (researched 2026-09-25). Owner's option (e).

Starting point: Wikipedia's language-by-LGA tables for Plateau and Cross River, which name
Tiv in Langtang South, Qua'an Pan, Shendam and Wase (Plateau) and Obanliku, Obudu, Bekwarra
and Yala (Cross River). Both tables cite Ethnologue (22nd edition, 2019), so they are ONE
source. Wikipedia's "Tiv language" article repeats the same LGAs without a citation, and a
2022 Daily Trust letter repeats its Plateau sentence word for word — same lineage, not
independent.

Independent evidence found:
  * Yala: Blueprint (2023) reports Tiv families settled on Yache land in Yala LGA "for
    years" -> Tiv people in Yala: multiple sources.
  * Obanliku: Daily Trust (2013) reports that the Utanga people of Obanliku speak Tiv
    fluently, sing in Tiv and use the Tiv Bible -> Tiv language in Obanliku: multiple
    sources. The Utanga speak their own Tivoid language (Otank); some of them call
    themselves Tiv, others a related people, so Tiv *people* stays single-source.
Everything else stays 'single_reliable_source' (Ethnologue via Wikipedia), as batch 006
did for Bali and Lafia. Conflicts reported in these sources are not summarised.
"""
import json, sys

ACCESSED = "2026-09-25"
SOURCES = {
    "WPL": dict(source_type="encyclopedia", title="Plateau State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Plateau_State",
                verification_status="needs_corroboration", notes="Reused (batch 008)."),
    "WCR": dict(source_type="encyclopedia", title="Cross River State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Cross_River_State",
                verification_status="needs_corroboration", notes="Reused (batch 008)."),
    "BP": dict(source_type="news", title="Benue, Cross River communal conflict claims 7, houses razed", author="Joseph Obung", organisation="Blueprint",
               publication_date="2023-09-11", url="https://blueprint.ng/benue-cross-river-communal-conflict-claims-7-houses-razed/",
               verification_status="needs_corroboration",
               notes="Reports a land and boundary dispute between Tiv people and the Yache community of Yala LGA, Cross River State. A Yala elder is quoted saying that Tiv people 'had settled on that land for years' and that there is no clear boundary demarcation. Used only for Tiv presence in Yala LGA."),
    "DTU": dict(source_type="news", title="Utanga: The forgotten Tiv community of Cross River", author="Terkula Igidi", organisation="Daily Trust",
                publication_date="2013-12-15", url="https://dailytrust.com/utanga-the-forgotten-tiv-community-of-cross-river/",
                verification_status="needs_corroboration",
                notes="Feature on the Utanga of Obanliku LGA (headquarters Bessenge). The Utanga speak their own language but, according to the district head, an Utanga child 'grows to understand and speak Tiv fluently'; people sing in Tiv, and churches use the Tiv Bible and teach the catechism in Tiv. Some interviewees call the Utanga Tiv; the district head calls them brothers of the Tiv."),
}

PLATEAU = [("langtang-south", "Langtang South"), ("qua-an-pan", "Qua'an Pan"), ("shendam", "Shendam"), ("wase", "Wase")]
CROSS_RIVER = [("obanliku", "Obanliku"), ("obudu", "Obudu"), ("bekwarra", "Bekwarra"), ("yala", "Yala")]
ETH = "Wikipedia's language-by-LGA table for {state}, which cites Ethnologue (22nd edition, 2019), names Tiv in {name}."

RELATIONS = []
for slug, name in PLATEAU:
    n = (ETH.format(state="Plateau State", name=name) + " Wikipedia's 'Tiv language' article names the same four Plateau LGAs, without a citation. "
         "No independent source has been found yet.")
    RELATIONS.append(dict(frm="@ethnic_groups:tiv", type="present_in", to=f"@admin_units:lga:{slug}", source="WPL", evidence="single_reliable_source", notes=n))
    RELATIONS.append(dict(frm="@languages:tiv", type="spoken_in", to=f"@admin_units:lga:{slug}", source="WPL", evidence="single_reliable_source", notes=n))

for slug, name in CROSS_RIVER:
    base = ETH.format(state="Cross River State", name=name)
    people = dict(frm="@ethnic_groups:tiv", type="present_in", to=f"@admin_units:lga:{slug}", source="WCR", evidence="single_reliable_source",
                  notes=base + " No independent source has been found yet.")
    lang = dict(frm="@languages:tiv", type="spoken_in", to=f"@admin_units:lga:{slug}", source="WCR", evidence="single_reliable_source",
                notes=base + " No independent source has been found yet.")
    if slug == "yala":
        people.update(source="BP", evidence="multiple_sources",
                      notes=base + " Blueprint (2023) independently reports Tiv families settled on Yache land in Yala LGA for years, in a report on a boundary dispute.")
        lang["notes"] = base + " Blueprint (2023) confirms Tiv people in the LGA but does not mention language."
    if slug == "obanliku":
        lang.update(source="DTU", evidence="multiple_sources",
                    notes=base + " Daily Trust (2013) independently reports that the Utanga people of Obanliku speak Tiv fluently, sing in Tiv and use the Tiv Bible.")
        people["notes"] = (base + " The Utanga of Obanliku speak their own Tivoid language (Otank). In Daily Trust (2013) some of them call themselves Tiv "
                           "and others a people related to the Tiv, so this link stays single-source.")
    RELATIONS += [people, lang]

GAPS = [
    ("An independent source for Tiv in Plateau's LGAs", "Langtang South, Qua'an Pan, Shendam and Wase rest on Ethnologue (via Wikipedia). A 2022 Daily Trust letter repeats Wikipedia's sentence word for word, so it is not independent. The Plateau State Government's investor guide does not mention the Tiv. Needs census, academic or official confirmation."),
    ("Obudu: the Cross River Ministry of Information report", "A search result shows a Cross River State Ministry of Information report in which the Obudu LGA chairman addresses 'members of the Tiv community' among Obudu residents after a killing. The site (moi.cr.gov.ng) could not be reached on 2026-09-25, and it has no Internet Archive copy. Retry; if readable, Obudu becomes multiple-source."),
    ("Bekwarra", "Vanguard (30 June 2026) reports 'ten Tiv indigenes' rescued by the Bekwarra police division at Imaje during unrest. Whether Imaje is in Bekwarra or Yala LGA is not clear, so it is not used."),
    ("The Utanga (Otank) and the Tiv", "The Utanga of Obanliku speak Otank, a Tivoid language, and use Tiv in church and song. Whether they are a Tiv group or a separate Tivoid people differs by speaker. Otank is not yet a record."),
    ("Conflicts on the Benue–Cross River border", "Several sources report land disputes between Tiv communities and their Cross River neighbours (Yala, Obudu, Obanliku). They are not summarised; they need a separate, careful, neutral treatment."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="The Tiv people and the Tiv language linked to four LGAs of Plateau State and four of Cross River State.")


def report():
    L = ["# Research batch 011 — Tiv in the LGAs of Plateau and Cross River", "",
         f"Researched {ACCESSED}. Owner's option (e). {len(RELATIONS)} new links, and no new pages. The links show on the Tiv people and Tiv language pages and on the eight LGA pages. The sitemap is unchanged.", "",
         "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}{', ' + s['author'] if s.get('author') else ''}{', ' + s['publication_date'] if s.get('publication_date') else ''}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    L += ["", "## Result per LGA", "", "| LGA | State | Tiv people | Tiv language | Why |", "|---|---|---|---|---|"]
    ev = {}
    for r in RELATIONS:
        ev.setdefault(r["to"].split(":")[-1], {})[r["type"]] = r["evidence"].replace("_", " ")
    why = {"yala": "Ethnologue + Blueprint 2023 (people)", "obanliku": "Ethnologue + Daily Trust 2013 (language)"}
    for slug, name in PLATEAU + CROSS_RIVER:
        st = "Plateau" if (slug, name) in PLATEAU else "Cross River"
        L.append(f"| {name} | {st} | {ev[slug]['present_in']} | {ev[slug]['spoken_in']} | {why.get(slug, 'Ethnologue (via Wikipedia) only')} |")
    L += ["", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense", "", "- No new pages and no new prose, so the sitemap is unchanged. The two new source pages stay noindex.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_011_tiv_plateau_cross_river.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_011_tiv_plateau_cross_river_REVIEW.md", "w").write(report())
    print(f"relations={len(RELATIONS)} multiple={sum(r['evidence'] == 'multiple_sources' for r in RELATIONS)}")
