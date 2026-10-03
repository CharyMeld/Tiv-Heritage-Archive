"""
Research batch 003 — the Tiv as a national record: the Tiv people (ethnic group), the
Tiv language and its Tivoid group, and their sourced links to Nigerian states
(researched 2026-09-24).

Evidence notes:
  * The state list (Benue, Taraba, Nasarawa, Plateau, Cross River) comes from Ethnologue
    2025 as quoted by Glottolog; Wikipedia gives the same list, probably from the same
    origin — so the state links are recorded as 'single_reliable_source', not as
    independent corroboration.
  * Nasarawa is also affirmed by the archive owner (community knowledge, 2026-09-24),
    recorded as a separate community source — it does not raise the evidence level.
  * Migration/origin accounts (Swem, Luba-Katanga) are NOT recorded here: they need
    their own batch that separates oral tradition from scholarship.
Summaries use only facts with two or more sources, in our own words.
"""
import json, sys

ACCESSED = "2026-09-24"
SOURCES = {
    "GLOT": dict(source_type="dataset", title="Tiv (Glottocode tivv1240)", organisation="Glottolog (Max Planck Institute for Evolutionary Anthropology)",
                 url="https://glottolog.org/resource/languoid/id/tivv1240", verification_status="needs_corroboration",
                 notes="Language catalogue entry: ISO 639-3 tiv; classification Atlantic-Congo > Volta-Congo > Benue-Congo > Bantoid > Southern Bantoid > Tivoid; countries Nigeria and Cameroon; alternative name Munshi. Quotes Ethnologue (Eberhard, Simons & Fennig 2025): EGIDS 3 (Wider communication), 'de facto language of provincial identity in Benue, Nassarawa, Plateau, Taraba, and Cross River states, used in elementary education'."),
    "SIL": dict(source_type="official_website", title="ISO 639-3 code: tiv", organisation="SIL International (ISO 639-3 Registration Authority)",
                url="https://iso639-3.sil.org/code/tiv", verification_status="verified",
                notes="Registration authority record: Tiv, identifier tiv, individual, living language."),
    "BOH": dict(source_type="encyclopedia", title="Tiv", author="Paul Bohannan", organisation="Encyclopedia of World Cultures (Gale)",
                publication_details="Encyclopedia of World Cultures; accessed via Encyclopedia.com",
                url="https://www.encyclopedia.com/humanities/encyclopedias-almanacs-transcripts-and-maps/tiv", verification_status="needs_corroboration",
                notes="Scholarly ethnographic entry: Tiv (sing. Or-Tiv) live on both sides of the Benue River, 220 km from its confluence with the Niger; a single Tiv language with regional dialects, classified within Niger-Congo; called Munshi or Munchi in Hausa; population estimates 1933 (600,000), 1950 (about 800,000), 1990 (more than a million)."),
    "WTP": dict(source_type="encyclopedia", title="Tiv people", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Tiv_people",
                verification_status="needs_corroboration",
                notes="Used to corroborate: Tiv live predominantly in Nigeria with fewer in Cameroon; most Tiv speakers reside in Benue, Taraba, Nasarawa, Plateau and Cross River; 'Munchi' (Munshi) was a name used by the Fulani, not embraced by the Tiv."),
    "WTL": dict(source_type="encyclopedia", title="Tiv language", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Tiv_language",
                verification_status="needs_corroboration",
                notes="Used to corroborate the classification (Niger-Congo > Atlantic-Congo > Volta-Congo > Benue-Congo > Bantoid > Southern Bantoid > Tivoid), ISO 639-3 tiv and Glottocode tivv1240."),
    "OWNER": dict(source_type="community_submission", title="Statement by the Tiv Heritage Archive owner (24 September 2026)",
                  organisation="Tiv Heritage Archive", url=None, contributor_name="Tiv Heritage Archive owner",
                  notes="Community knowledge given during batch 003 research: 'Nasarawa have a large number of Tiv speakers.'",
                  verification_status="needs_corroboration"),
}

STATES = ["benue", "taraba", "nasarawa", "plateau", "cross-river"]
STATE_NAMES = {"benue": "Benue", "taraba": "Taraba", "nasarawa": "Nasarawa", "plateau": "Plateau", "cross-river": "Cross River"}

RECORDS = [
    dict(key="tivoid", table="languages", evidence="multiple_sources",
         fields=dict(lang_type="branch", name="Tivoid", slug="tivoid",
                     summary="Tivoid is a group of languages within the Southern Bantoid branch of Benue–Congo (Niger–Congo). Tiv is its best-known member."),
         srcs=[("GLOT", "Tivoid within Southern Bantoid, Benue-Congo"), ("WTL", "Tiv classified as Tivoid")]),
    dict(key="tiv_lang", table="languages", evidence="multiple_sources",
         fields=dict(lang_type="language", name="Tiv", slug="tiv", parent_id="@key:tivoid", iso639_3="tiv", glottocode="tivv1240",
                     summary="Tiv is the language of the Tiv people, spoken mainly in central Nigeria and also in Cameroon. It belongs to the Tivoid group of the Benue–Congo branch of Niger–Congo; its ISO 639-3 code is tiv.",
                     description=("Paul Bohannan, in the Encyclopedia of World Cultures, describes a single Tiv language understood by all Tiv, "
                                  "with regional dialects that let listeners tell which area a speaker comes from.\n\n"
                                  "Ethnologue (2025), as quoted by Glottolog, rates Tiv as a language of wider communication — level 3 on its EGIDS scale — "
                                  "and describes it as the de facto language of provincial identity in Benue, Nasarawa, Plateau, Taraba and Cross River states, "
                                  "used in elementary education."),
                     vitality="EGIDS 3 (Wider communication), per Ethnologue 2025 as quoted by Glottolog", vitality_source_id="@src:GLOT",
                     educational_use="Used in elementary education (Ethnologue 2025, as quoted by Glottolog)."),
         srcs=[("SIL", "ISO 639-3 code tiv; individual living language"), ("GLOT", "Glottocode tivv1240; classification; spoken in Nigeria and Cameroon"),
               ("WTL", "Classification; ISO and Glottolog codes"), ("BOH", "Single Tiv language with regional dialects; Niger-Congo")]),
    dict(key="tiv_people", table="ethnic_groups", evidence="multiple_sources",
         fields=dict(name="Tiv", slug="tiv", endonym="Tiv",
                     summary="The Tiv are a people of central Nigeria who live on both sides of the Benue River, with a smaller population in Cameroon. They speak the Tiv language.",
                     description=("In the Encyclopedia of World Cultures, Paul Bohannan places the Tiv on both sides of the Benue River, about 220 kilometres from "
                                  "its confluence with the Niger. He notes that the south-east of their land borders the foothills of the Cameroons, "
                                  "\"from whence the Tiv say they originally came\" — an account the Tiv themselves give, recorded here as such.\n\n"
                                  "The singular form is Or-Tiv (\"a Tiv person\"). Outsiders have also used the name Munshi or Munchi, which the Tiv have not adopted; "
                                  "see Other names.\n\n"
                                  "Population estimates are given below with their sources and years; they are not directly comparable with one another.")),
         srcs=[("BOH", "Tiv live on both sides of the Benue River; singular Or-Tiv"), ("WTP", "Tiv live predominantly in Nigeria, with fewer in Cameroon"),
               ("GLOT", "Tiv language spoken in Nigeria and Cameroon"),
               ("OWNER", "Large number of Tiv speakers in Nasarawa State (community knowledge)")]),
]

NAMES = [
    dict(record="tiv_people", name="Munshi", name_type="exonym", srcs=["BOH"],
         usage_notes="Name used by outsiders — in Hausa according to Bohannan; by the Fulani according to Wikipedia — and in older records. Not accepted by the Tiv themselves."),
    dict(record="tiv_people", name="Munchi", name_type="spelling_variant", srcs=["BOH"], usage_notes="Variant spelling of Munshi (Bohannan; Wikipedia)."),
    dict(record="tiv_people", name="Or-Tiv", name_type="other", srcs=["BOH"], usage_notes="Singular: a Tiv person (Bohannan)."),
    dict(record="tiv_lang", name="Munshi", name_type="exonym", srcs=["GLOT"], usage_notes="Older name for the language listed by Glottolog (via Multitree); not used by the Tiv."),
]

STATE_NOTE = "Ethnologue (2025, quoted by Glottolog) names these states; Wikipedia (Tiv people) lists the same states, probably from the same origin."
RELATIONS = [dict(frm="tiv_people", type="speaks", to="tiv_lang", source="BOH", evidence="multiple_sources",
                  notes="Bohannan: a single Tiv language intelligible to all Tiv; Wikipedia (Tiv language): the language of the Tiv people.")]
for s in STATES:
    extra = " Also affirmed by the archive owner (community knowledge, 24 September 2026)." if s == "nasarawa" else ""
    RELATIONS.append(dict(frm="tiv_lang", type="spoken_in", to=f"@admin_units:state:{s}", source="GLOT", evidence="single_reliable_source",
                          notes=STATE_NOTE + extra))
    RELATIONS.append(dict(frm="tiv_people", type="present_in", to=f"@admin_units:state:{s}", source="GLOT", evidence="single_reliable_source",
                          notes="Ethnologue describes Tiv as the de facto language of provincial identity in this state; Wikipedia (Tiv people) says most Tiv speakers live in these states." + extra))

STATISTICS = [
    dict(record="tiv_people", metric="population", value_low=600000, reference_year=1933, method="estimate", source="BOH", evidence="single_reliable_source",
         notes="\"The earliest estimate of the Tiv population, in 1933, was 600,000\" (Bohannan)."),
    dict(record="tiv_people", metric="population", value_low=800000, reference_year=1950, method="other", source="BOH", evidence="single_reliable_source",
         notes="\"In 1950 the count was about 800,000\" (Bohannan)."),
    dict(record="tiv_people", metric="population", value_low=1000000, reference_year=1990, method="estimate", source="BOH", evidence="single_reliable_source",
         notes="\"By 1990, the figure had climbed to more than a million\" (Bohannan) — a lower bound, not an exact figure."),
]

GAPS = [
    ("An independent source for the states with Tiv communities", "Ethnologue (via Glottolog) and Wikipedia list Benue, Taraba, Nasarawa, Plateau and Cross River, probably from one origin; the links are recorded as single-source. Needs census, official or academic confirmation (Nasarawa is also affirmed by the archive owner)."),
    ("Current population and speaker numbers", "Wikipedia gives about 8 million people (citing Minority Rights Group) and 4.56 million speakers (2020) — conflicting and not verified at source; not recorded. Nigeria's census does not publish ethnicity."),
    ("Tiv in Cameroon", "Sources place a smaller Tiv population in Cameroon; Cameroonian administrative units are not yet in the archive."),
    ("Origins and migration", "Accounts of origin at Swem and of migration from the Luba-Katanga region (Ethnologue) are not recorded; they need a batch separating oral tradition from scholarship."),
    ("Tiv dialects", "Bohannan notes regional dialects; they are not yet enumerated from a source."),
    ("Who used the name 'Munshi'", "Bohannan says Hausa; Wikipedia says Fulani. Both recorded in the name note."),
    ("Links to Tiv Heritage collection records", "Festivals, foods, proverbs and history articles of the Tiv collection are not yet linked to this national record with sources."),
    ("Tiv orthography and writing", "Not yet recorded from a source."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="The Tiv people and the Tiv language as national records, linked to the states where they are documented.")


def report(d):
    L = ["# Research batch 003 — The Tiv people and the Tiv language", "", f"Researched {ACCESSED}. Imported as *in review* only after approval.", "", "## Sources", ""]
    for k, s in SOURCES.items():
        L.append(f"- **{k}** — {s['title']}{' — ' + s['author'] if s.get('author') else ''} ({s['organisation']}). {s['url']}. Accessed {ACCESSED}.")
    L += ["", "## Records", ""]
    for r in RECORDS:
        L.append(f"### {r['fields']['name']} ({r['table'].replace('_', ' ')}) — evidence: {r['evidence'].replace('_', ' ')}")
        L.append(""); L.append(f"> {r['fields']['summary']}"); L.append("")
        if r['fields'].get('description'): L += [r['fields']['description'], ""]
        L.append("Sources: " + "; ".join(f"{k} ({c})" for k, c in r['srcs'])); L.append("")
    L += ["## Other names", ""] + [f"- **{n['name']}** ({n['name_type']}, for {n['record']}) — {n['usage_notes']} [{', '.join(n['srcs'])}]" for n in NAMES]
    L += ["", "## Relationships", "", "| From | Relationship | To | Evidence | Source | Notes |", "|---|---|---|---|---|---|"]
    for r in RELATIONS:
        to = STATE_NAMES.get(r['to'].split(':')[-1], r['to']) + (" State" if r['to'].startswith('@') else "")
        L.append(f"| {r['frm']} | {r['type']} | {to} | {r['evidence'].replace('_', ' ')} | {r['source']} | {r['notes']} |")
    L += ["", "## Population figures (kept side by side, each with its source)", ""]
    L += [f"- {s['reference_year']}: {s['value_low']:,}{'+' if s['reference_year'] == 1990 else ''} — {s['notes']}" for s in STATISTICS]
    L += ["", "## Not recorded — research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## What changes for visitors and the AI", "",
          "- New pages: /nigeria/ethnic-groups/tiv and /nigeria/languages/tiv (plus Tivoid). Short records, so **noindex** (no sitemap change).",
          "- Benue, Taraba, Nasarawa, Plateau and Cross River state pages gain a 'Connections' entry for the Tiv.",
          "- The AI can answer \"Which states have documented Tiv communities?\" and \"Where is Tiv spoken?\" with sources and evidence levels.",
          "- \"Tell me about Tiv\" keeps the Tiv Heritage Archive's own answer.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    d = build()
    json.dump(d, open(f"{out}/batch_003_tiv.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_003_tiv_REVIEW.md", "w").write(report(d))
    print(f"records={len(RECORDS)} names={len(NAMES)} relations={len(RELATIONS)} statistics={len(STATISTICS)} sources={len(SOURCES)} gaps={len(GAPS)}")
