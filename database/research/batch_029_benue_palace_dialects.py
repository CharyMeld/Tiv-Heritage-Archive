"""
Research batch 029 — Benue (Phase 3): the Tor Tiv Palace at Gboko (G-04, part 2) and the dialects
of Tiv and Idoma (G-07). Researched 2026-09-26.

  * Tor Tiv Palace: Vanguard (Peter Duru, 20 May 2021) and Wikipedia (Gboko). Its original building
    date is found only in a social-media post, so it is a gap.
  * Dialects: Glottolog (Tier 2). Tiv: two dialect groups, Icongo (Ichongo) and Ipusu, with nine
    dialects bearing the names of the Tiv clan-families. Idoma: Central, South, West and Okpogu.
    Nomishan (2020), citing Tiv scholars, describes Tiv as having no dialects that hinder mutual
    understanding — both views are stated. Record names follow Glottolog; the usual Tiv spellings
    (Ichongo, Turan, Ikyurav, Shitire) are added as other names from Nomishan (2020).
  * Not done: the Och'Idoma's palace (only one line in I am Benue) and gap G-08 (no new independent
    source on the Hausa of Katsina-Ala and Ukum).
"""
import json, re, sys

ACCESSED = "2026-09-26"
SOURCES = {
    "VG21": dict(source_type="news", source_kind="news", source_tier=3, title="How Tor Tiv palace was turned into tourist destination",
                 author="Peter Duru", organisation="Vanguard", publication_date="2021-05-20",
                 url="https://www.vanguardngr.com/2021/05/how-tor-tiv-palace-was-turned-into-tourist-destination/", verification_status="needs_corroboration",
                 notes="The former palace was badly dilapidated beyond renovation (the Tor Tiv); rebuilt by Governor Samuel Ortom's administration, begun about five years earlier and completed in phases (phases two and three under Commissioner Ekpa Ogbu), funded by the state government and the 14 Tiv-speaking LGAs; Tiv colours black and white and Tiv cultural symbols built into the design; artifacts; visitors; Prof. James Ayatse the first occupant."),
    "WGB": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Gboko", organisation="Wikipedia",
                url="https://en.wikipedia.org/wiki/Gboko", verification_status="needs_corroboration",
                notes="The palace of the Tor Tiv, the supreme traditional leader, is in the heart of the town."),
    "GLTIV": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Tiv (tivv1240)", organisation="Glottolog",
                  url="https://glottolog.org/resource/languoid/id/tivv1240", verification_status="verified",
                  notes="Dialects: Icongo (Masev-Hyarev, Nongov, Tulan, Ugondo) and Ipusu (Kparev, Kyurav, Shitile, Tongov, Ukum)."),
    "GLIDO": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Idoma (idom1241)", organisation="Glottolog",
                  url="https://glottolog.org/resource/languoid/id/idom1241", verification_status="verified",
                  notes="Dialects: Idoma Central, Idoma South, Idoma West, Okpogu. Classification: Idomoid > Akweya > Etulo-Idoma > Nuclear Idoma > Idoma-Agatu-Okpogu."),
    "NOM20": dict(source_type="research", source_kind="journal_article", source_tier=2,
                  title="Perspectives on the Origin, Genealogical Narration, Early Migrations and Settlement Morphology of the Tiv of Central Nigeria",
                  author="Terngu S. Nomishan", organisation="International Journal of Academic Pedagogical Research", publication_date="2020",
                  publication_details="Vol. 4, Issue 8, pp. 26–32", url="http://ijeais.org/wp-content/uploads/2020/8/IJAPR200808.pdf", verification_status="needs_corroboration",
                  notes="Tiv, his wife Ayaaya and two sons Ipusu and Ichongo; descendants called Ipusu-akem and Ichongo-akem; the Tiv language is homogeneous, 'without dialect, spoken and understood by all' (citing Gbor 1974, Makar 1975/1994 and others); migration traditions (three groups from the Nwange hills: Kparev and Ukum; Tongov, Ikyurav, Nongov and Turan; Masev, Ihyarev, Ugondo and Shitire)."),
}

PALACE = """The Tor Tiv Palace in Gboko is the palace of the Tor Tiv, the paramount ruler of the Tiv people; Wikipedia places it in the heart of the town.

Vanguard reported in May 2021 that the old palace had become, in the Tor Tiv's words, too dilapidated to be renovated, and that Governor Samuel Ortom's administration had rebuilt it in phases over about five years. The work was paid for jointly by the Benue State Government and the fourteen Tiv-speaking local government areas. The new building uses the Tiv colours, black and white, and other Tiv cultural symbols in its design and finishing, and it was furnished with artifacts meant to show Tiv heritage; Vanguard described it as a place that now draws visitors. The present Tor Tiv, James Ayatse, was its first occupant. When the first palace was built is not recorded here."""

def dialect_text(name, group, members=None):
    if members:
        return (f"{name} is one of the two dialect groups of the Tiv language in Glottolog, the language catalogue of the Max Planck Institute. It takes its name from "
                f"{'Ichongo' if name == 'Icongo' else 'Ipusu'}, one of the two sons of Tiv in Tiv genealogy, and Glottolog places these dialects in it: {members}. "
                "The names are those of the Tiv clan-families that trace their descent from him. Tiv scholars cited by Nomishan (2020) describe the language as "
                "homogeneous and understood by all Tiv, without dialects that divide it.")
    return (f"{name} is listed by Glottolog as a dialect of Tiv in the {group} group. Its name is that of a Tiv clan-family.")

TIV_GROUPS = [("icongo", "Icongo", "icon1234", "Masev-Hyarev, Nongov, Tulan and Ugondo", ["Ichongo"]),
              ("ipusu", "Ipusu", "ipus1234", "Kparev, Kyurav, Shitile, Tongov and Ukum", [])]
TIV_DIALECTS = [("masev-hyarev", "Masev-Hyarev", "mase1251", "icongo", []), ("nongov", "Nongov", "nong1248", "icongo", []),
                ("tulan", "Tulan", "tula1255", "icongo", ["Turan"]), ("ugondo", "Ugondo", "ugon1240", "icongo", []),
                ("kparev", "Kparev", "kpar1234", "ipusu", []), ("kyurav", "Kyurav", "kyur1234", "ipusu", ["Ikyurav"]),
                ("shitile", "Shitile", "shit1243", "ipusu", ["Shitire"]), ("tongov", "Tongov", "tong1331", "ipusu", []),
                ("ukum-dialect", "Ukum", "ukum1234", "ipusu", [])]
IDOMA_DIALECTS = [("idoma-central", "Idoma Central", "idom1261"), ("idoma-south", "Idoma South", "idom1259"),
                  ("idoma-west", "Idoma West", "idom1260"), ("okpogu", "Okpogu", "okpo1242")]

RECORDS = [dict(key="palace", table="places", evidence="multiple_sources", level="well_documented",
                fields=dict(place_type="heritage_site", name="Tor Tiv Palace", slug="tor-tiv-palace", admin_unit_id="@admin_units:lga:benue/gboko", status="existing",
                            summary="The Tor Tiv Palace in Gboko is the palace of the paramount ruler of the Tiv, rebuilt by 2021 in Tiv black and white with Tiv cultural symbols.",
                            description=PALACE),
                srcs=[("WGB", "In the heart of Gboko"), ("VG21", "Rebuilding, funding, design, first occupant")])]
NAMES = []
for key, name, code, members, alts in TIV_GROUPS:
    RECORDS.append(dict(key=key, table="languages", evidence="single_reliable_source", level="well_documented",
                        fields=dict(lang_type="dialect", name=name, slug=key, parent_id="@languages:tiv", glottocode=code,
                                    summary=f"{name} is one of the two dialect groups of Tiv in Glottolog, named after {'Ichongo' if name == 'Icongo' else 'Ipusu'}, son of Tiv.",
                                    description=dialect_text(name, None, members)),
                        srcs=[("GLTIV", f"{name} dialect group and its dialects"), ("NOM20", "Ipusu and Ichongo, sons of Tiv; Tiv 'without dialect' view")]))
    for a in alts:
        NAMES.append(dict(record=key, name=a, name_type="spelling_variant", usage_notes="Usual Tiv spelling (Nomishan 2020); Glottolog writes Icongo.", srcs=["NOM20"]))
for key, name, code, grp, alts in TIV_DIALECTS:
    gname = dict((k, n) for k, n, *_ in TIV_GROUPS)[grp]
    RECORDS.append(dict(key=key, table="languages", evidence="single_reliable_source", level="well_documented",
                        fields=dict(lang_type="dialect", name=name, slug=key, parent_id=f"@key:{grp}", glottocode=code,
                                    summary=f"{name} is a dialect of Tiv in the {gname} group (Glottolog).", description=dialect_text(name, gname)),
                        srcs=[("GLTIV", f"{name}: dialect of Tiv, {gname} group")]))
    for a in alts:
        NAMES.append(dict(record=key, name=a, name_type="spelling_variant", usage_notes=f"Usual spelling of the clan name (Nomishan 2020); Glottolog writes {name}.", srcs=["NOM20"]))
for key, name, code in IDOMA_DIALECTS:
    RECORDS.append(dict(key=key, table="languages", evidence="single_reliable_source", level="well_documented",
                        fields=dict(lang_type="dialect", name=name, slug=key, parent_id="@languages:idoma", glottocode=code,
                                    summary=f"{name} is a dialect of Idoma listed by Glottolog.",
                                    description=f"{name} is one of the four dialects of the Idoma language listed by Glottolog, with Idoma Central, Idoma South, Idoma West and Okpogu. Where each is spoken is not yet recorded here."),
                        srcs=[("GLIDO", f"{name}: dialect of Idoma")]))

RELATIONS = [dict(frm="palace", type="associated_with", to="@polities:tor-tiv", role="seat of the Tor Tiv", source="WGB", evidence="multiple_sources", level="well_documented",
                  notes="Palace of the Tor Tiv (Wikipedia; Vanguard 2021).")]
GAPS = [
    ("Tor Tiv Palace: first building", "When the first palace was built is not documented in a reliable source (a social-media post gives 1946–2021)."),
    ("Where each Tiv and Idoma dialect is spoken", "Glottolog lists the dialects but not their areas; mapping them to LGAs needs linguistic or ethnographic sources (links with gap G-10)."),
    ("Ikyurav: Ipusu or Ichongo", "Glottolog places Kyurav (Ikyurav) under Ipusu; a genealogical account seen only in a search summary lists Ikurav among the sons of Ichongo. Needs Tiv genealogy sources."),
    ("Tiv 'without dialect'", "Tiv scholars (via Nomishan 2020) describe Tiv as dialect-free in practice; Glottolog lists nine dialects. The two views are compatible (clan speech varieties, mutually intelligible) but this is not stated by a source."),
    ("Och'Idoma palace", "Only I am Benue mentions it (Otukpo). Not recorded as a place yet."),
    ("Hausa of Katsina-Ala and Ukum (G-08)", "No independent source beyond Wikipedia found; still open."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Benue: the Tor Tiv Palace (G-04 part 2); Tiv and Idoma dialects from Glottolog (G-07).")


def report():
    L = ["# Research batch 029 — Benue: the Tor Tiv Palace and the Tiv and Idoma dialects", "",
         f"Researched {ACCESSED}. Phase 3, gaps G-04 (part 2) and G-07. Created in review; published only after your approval.", "",
         f"- **Tor Tiv Palace**, Gboko ({words(PALACE)} words), linked to the Tor Tiv record.",
         "- **Tiv dialects** (Glottolog): 2 dialect groups, **Icongo (Ichongo)** and **Ipusu**, with 9 dialects under them: Masev-Hyarev, Nongov, Tulan (Turan), Ugondo | Kparev, Kyurav (Ikyurav), Shitile (Shitire), Tongov, Ukum. These are the names of the Tiv clan-families.",
         "- **Idoma dialects** (Glottolog): Idoma Central, Idoma South, Idoma West, Okpogu.",
         f"- {len(RECORDS) - 1} dialect records in all, each linked to its language through 'belongs to', so they appear on the Tiv and Idoma language pages under *Dialects & varieties*. {len(NAMES)} usual Tiv spellings are added as other names.",
         "- All the new pages are short, so they stay noindex (sitemap unchanged).", "",
         "## Tor Tiv Palace", ""] + [f"> {p}" if p else ">" for p in PALACE.split("\n")]
    L += ["", "## Example dialect texts", "", "> " + dialect_text("Icongo", None, "Masev-Hyarev, Nongov, Tulan and Ugondo"), "",
          "> " + dialect_text("Kparev", "Ipusu"), "",
          "## Two views, both stated", "",
          "Glottolog lists nine Tiv dialects. Nomishan (2020), citing Tiv scholars (Gbor, Makar and others), calls Tiv a homogeneous language 'without dialect', understood by all Tiv. The dialect-group pages state both.", "",
          "## Sources", ""] + [f"- **{k}** — {s['title']} ({s.get('author', s['organisation'])}{', ' + s['publication_date'] if s.get('publication_date') else ''}). {s['url']}. Tier {s['source_tier']}." for k, s in SOURCES.items()]
    L += ["", "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_029_benue_palace_dialects.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_029_benue_palace_dialects_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} names={len(NAMES)} palace={words(PALACE)}")
