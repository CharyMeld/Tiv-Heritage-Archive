"""
Research batch 024 — Benue (Phase 3): the 276 INEC wards (registration areas) of Benue State
(gap G-02) and the Abakpa/Abakwa (gap G-03); researched 2026-09-25/26.

Wards: from INEC's Directory of Polling Units, Benue State, revised January 2015 (the PDF was
removed from inecnigeria.org; the Internet Archive copy of 15 Aug 2026 is used and recorded).
Parsed to data/inec_benue_ras.json: 23 LGAs, 276 registration areas, 3,687 polling units; every
LGA's ward count and polling-unit total matches the directory's own summary table.
Each ward becomes an admin unit (type 'ward') under its LGA, with INEC's code (LGA-RA, e.g.
01-01) and its polling-unit count (2015) as a statistic. Wards have no pages of their own; they
are listed on their LGA's page (owner decision Q1: INEC wards, one state at a time).

Abakpa: only I am Benue describes them. Their language and origin are not classified; a travel
blog's claim that they speak an Idoma dialect is not used (Tier 5, uncorroborated). I am Benue's
claim that the Abakwa founded Abakwa and Katsina-Ala "before the Tiv" is attributed and marked
as uncorroborated.
Changes to EXISTING records are in fix_024_benue.php (separate approval).
"""
import json, re, sys, os

ACCESSED = "2026-09-25"
HERE = os.path.dirname(os.path.abspath(__file__))
INEC_URL = "https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_Benue.pdf"
SOURCES = {
    "INEC": dict(source_type="government_publication", source_kind="government_publication", source_tier=1,
                 title="Directory of Polling Units: Benue State (Revised January 2015)",
                 organisation="Independent National Electoral Commission (INEC)", publication_date="2015-01",
                 url=INEC_URL,
                 archive_reference="Internet Archive copy: https://web.archive.org/web/20260815145349/" + INEC_URL,
                 verification_status="verified",
                 notes="Lists Benue's 23 LGAs (codes 01–23), 276 registration areas (wards) with codes, and 3,687 polling units. INEC's disclaimer: the directory 'should not be referred to as a legal or administrative document for the purpose of administrative boundary or political claims'. The PDF is no longer on the INEC site; the Internet Archive copy was used."),
    "IAMAB": dict(source_type="website", source_kind="community_organisation", source_tier=4,
                  title="Abakwa People of Benue State", organisation="I am Benue",
                  url="https://www.iambenue.com/benue-state/abakwa-people-of-benue-state/",
                  verification_status="needs_corroboration",
                  notes="Abakwa are Benue indigenous people mainly from Abakwa, after Tyo-Wanye, Buruku LGA; king named Inusa; women mostly wear the hijab, men kaftans and agbada; tuwo of wheat or guinea corn; bow or genuflect in greeting; most celebrate Salah; marriage customs; states that the Abakwa founded Abakwa and Katsina-Ala towns more than 200 years ago before the Tiv came, and took part in establishing Gboko after 1948. No author or date."),
    "DT12": dict(source_type="news", source_kind="news", source_tier=3, title="The Hausa of Makurdi", author="Hope Abah",
                 organisation="Daily Trust", publication_date="2012-11-25", url="https://dailytrust.com/the-hausa-of-makurdi/",
                 verification_status="needs_corroboration",
                 notes="The first Hausa settlers came from parts of Kano and gained prominence during the construction of the railway bridge over the Benue; the community centres on Wadata and its riverside market; the Sarkin Hausawa; Alhaji Al-Hassan Maikeke chairman of Makurdi LGA in 1998; Tiv and Hausa co-existed peacefully after a Tiv chief was installed (after 1945), with intermarriage. Used by fix_024 for the Hausa–Makurdi link."),
}

lgas = json.load(open(os.path.join(HERE, "data", "inec_benue_ras.json")))
LGA_NAME = {l.split("\t")[0]: l.split("\t")[1].strip() for l in """ado	Ado
agatu	Agatu
apa	Apa
buruku	Buruku
gboko	Gboko
guma	Guma
gwer-east	Gwer East
gwer-west	Gwer West
katsina-ala	Katsina-Ala
konshisha	Konshisha
kwande	Kwande
logo	Logo
makurdi	Makurdi
obi	Obi
ogbadibo	Ogbadibo
ohimini	Ohimini
oju	Oju
okpokwu	Okpokwu
oturkpo	Oturkpo
tarka	Tarka
ukum	Ukum
ushongo	Ushongo
vandeikya	Vandeikya""".split("\n")}


def clean(n):
    n = n.replace("‐", "-")
    n = re.sub(r"\s*-\s*", "-", n)
    return re.sub(r"\s+", " ", n).strip()


def slug(n):
    return re.sub(r"[^a-z0-9]+", "-", n.lower()).strip("-")


UNITS, STATS = [], []
for code, v in sorted(lgas.items()):
    lslug = v["slug"]
    for r in v["ras"]:
        name = clean(r["name"])
        key = f"w{code}{r['code']}"
        oc = f"{code}-{r['code']}"
        UNITS.append(dict(key=key, unit_type="ward", parent=f"@admin_units:lga:benue/{lslug}", name=name, slug=slug(name),
                          official_code=oc, status="current", evidence="single_reliable_source", level="verified",
                          summary=f"{name} is a ward (INEC registration area {oc}) of {LGA_NAME[lslug]} Local Government Area, Benue State, "
                                  f"listed in INEC's Directory of Polling Units (revised January 2015) with {r['pus']} polling units.",
                          srcs=[("INEC", f"Registration area {oc} of {LGA_NAME[lslug]}; {r['pus']} polling units")]))
        STATS.append(dict(record=key, metric="other", value_low=r["pus"], reference_year=2015, method="other",
                          notes="Polling units (INEC Directory of Polling Units, revised January 2015)",
                          source="INEC", evidence="single_reliable_source", level="verified"))

ABAKPA_DESCRIPTION = """Little has been published about the Abakpa, a name also spelt Abakwa. The Benue State Government names the Abakwa among the peoples of the state, and its Ministry of Finance and Economic Planning writes the name Abakpa.

I am Benue, a Benue community website, describes the Abakwa as a Benue people living mainly at Abakwa, near Tyo-Wanye in Buruku Local Government Area. It says that most of them are Muslims who celebrate Salah; that the women mostly wear the hijab and the men kaftans and agbada; that a favourite food is tuwo made from wheat or guinea corn; and that people bow or genuflect in greeting. It names their king as Inusa.

The same page states that the Abakwa founded Abakwa and Katsina-Ala towns more than two hundred years ago, before the Tiv came to the Benue valley. No other source consulted confirms this, so it is recorded only as I am Benue's statement. The language and origin of the Abakwa are not yet established from reliable sources."""

UPDATES = [dict(ref="@ethnic_groups:abakpa", fields=dict(description=ABAKPA_DESCRIPTION),
                srcs=[("IAMAB", "Abakwa town near Tyo-Wanye, Buruku; religion, dress, food, greeting; king Inusa; founding claim (attributed)")])]
RELATIONS = [dict(frm="@ethnic_groups:abakpa", type="present_in", to="@admin_units:lga:benue/buruku", source="IAMAB",
                  evidence="needs_corroboration", level="reported", settlement_status="unknown",
                  notes="I am Benue places the Abakwa mainly at Abakwa, near Tyo-Wanye in Buruku LGA, and calls them indigenous; single community source, so the nature of their presence is left as unknown.")]

GAPS = [
    ("INEC wards after 2015", "The ward list is from INEC's 2015 directory. INEC added polling units in 2021; whether any Benue registration area was renamed or split since 2015 needs INEC's current list."),
    ("Ward boundaries and headquarters", "INEC lists wards and polling units only; ward boundaries (GIS) and ward headquarters are not recorded."),
    ("Abakpa language and origin", "Not established. A travel blog calls them an Idoma-speaking group of Kwararafa origin; I am Benue's description (Muslim practice) suggests otherwise. Needs ethnographic or official sources."),
    ("Abakwa founding claim", "I am Benue says the Abakwa founded Abakwa and Katsina-Ala towns over 200 years ago, before the Tiv; uncorroborated. Needs local histories of Katsina-Ala and Buruku."),
    ("Abakwa and Agwan-Abakwa", "Search results link Abakwa to Agwan-Abakwa (Etulo headquarters, Gboko LGA); not confirmed from a readable source."),
]


def build():
    return dict(sources=SOURCES, units=UNITS, places=[], changes=[], records=[], names=[], gaps=GAPS, updates=UPDATES,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATS,
                scope="Benue (Phase 3): the 276 INEC wards (registration areas) with codes and polling-unit counts; the Abakpa/Abakwa (description, Buruku).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 024 — Benue: the 276 INEC wards and the Abakpa", "",
         f"Researched {ACCESSED}. Phase 3, second Benue batch; closes gaps G-02 (wards) and G-03 (who the Abakpa are, as far as sources allow).",
         "New records are created **in review** and published only after your approval.", "",
         "## Wards", "",
         "- **Source:** INEC, *Directory of Polling Units: Benue State* (revised January 2015). INEC has removed the PDF from its site; the Internet Archive copy (15 Aug 2026) is used and recorded.",
         f"- **{len(UNITS)} wards** in the 23 LGAs, each with INEC's code (LGA-ward, e.g. 01-01) and its number of polling units in 2015. Totals: 3,687 polling units; every LGA's count matches INEC's own summary table.",
         "- Each ward gets a permanent ID, e.g. `NG-WARD-BENUE-ADO-01-01`.",
         "- **No ward pages.** Each LGA page gets a list, *Wards (INEC registration areas)*, with codes and polling-unit counts, and INEC's note that the directory is not a legal document for boundary claims. Wards stay out of the sitemap, search index, AI lookups and home-page counts.",
         "- Evidence: *verified* (an official source stating it directly), dated 2015.", "",
         "| LGA | Wards | Polling units |", "|---|---|---|"]
    for code, v in sorted(lgas.items()):
        L.append(f"| {LGA_NAME[v['slug']]} | {v['nra']} | {v['npu']} |")
    L += ["", "Ward names per LGA:", ""]
    for code, v in sorted(lgas.items()):
        L.append(f"- **{LGA_NAME[v['slug']]}**: " + "; ".join(f"{clean(r['name'])} ({code}-{r['code']})" for r in v["ras"]))
    L += ["", "## Abakpa (Abakwa)", "",
          f"Fills the empty *description* of the existing Abakpa record ({words(ABAKPA_DESCRIPTION)} words, so the page stays noindex) and links the Abakpa to Buruku LGA (level *reported*, settlement *unknown*).", ""]
    L += [f"> {p}" if p else ">" for p in ABAKPA_DESCRIPTION.split("\n")]
    L += ["", "Not used: a travel blog's claim that the Abakpa speak an Idoma dialect (Tier 5, uncorroborated, and at odds with I am Benue).", "",
          "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    L += ["", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_024_benue_wards.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_024_benue_wards_REVIEW.md", "w").write(report())
    print(f"wards={len(UNITS)} stats={len(STATS)} abakpa_words={words(ABAKPA_DESCRIPTION)}")
