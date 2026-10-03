"""
Research batch 061 — Adamawa LGA profiles (headquarters, 2006 population and area, short sourced descriptions
for the 21 LGAs). Researched 2026-10-01. Pattern: batch 055 (Kogi).

Sources:
  * Statoids (reused): headquarters, 2006 census population, area (rows NG.AD.*).
  * INEC, "Adamawa State" LGA office addresses (wp1.inecnigeria.org/wp-content/uploads/2024/04/ADAMAWA-STATE.pdf,
    read via the Internet Archive, 2025 snapshot; copy in data/inec_adamawa_lga_offices.pdf). Tier 1. Only Maiha's
    office is placed by the LGA secretariat in a named town ('Behind Local Govt. Secretariat ... Maiha'); Girei's
    and Mubi North's are by the secretariat but name no town; Jada's and Shelleng's are at the OLD secretariat.
  * Wikipedia article of each LGA. These are thin: most of their text is a 2022/23 survey of internally displaced
    persons' languages (not used — not a population count) and climate data (not used).
  * Reused from batches 057b, 059 and 060 for the peoples, traditional states, heritage sites and festivals of
    each LGA (ADSPC, Wikipedia 'Adamawa State', Blench's Atlas, Adamawa State Government, Daily Trust, UNESCO, NCMM).
No official Adamawa State table of LGA headquarters was found.
Headquarters grading: two sources that explicitly name the headquarters = well documented; one = reported.
  * Wikipedia names the headquarters explicitly only for Demsa, Hong and Michika; INEC fixes Maiha.
  * Girei: Statoids spells it 'Girie'; INEC and the LGA name 'Girei' -> Girei, with Girie as a variant.
  * Yola South: headquarters Yola = the existing place record (the state capital).
Not used from Wikipedia: Lamurde '511.252' (2006) and Shelleng '590,671' (implausible); Mayo-Belwa '204,200 as of
2016' and Song '260,900' (estimates, no census); Yola North 'Mandara Mountains' and Yola South 'Shebshi and
Dimlang Mountains' (these ranges lie elsewhere); Shelleng 'Mendamo festival' (the state's table has Menjauli —
gap); Madagali name etymology (folk etymology, unsourced). Lamurde area: Wikipedia 2,098 km² vs Statoids 1,171
km² — Statoids recorded, noted.
"""
import json, re, sys
import batch_057b_adamawa_peoples as B57B
import batch_059_adamawa_institutions as B59
import batch_060_adamawa_heritage as B60

ACCESSED = "2026-10-01"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "STAT": dict(source_type="dataset", title="Local Government Areas of Nigeria", organisation="Statoids (Gwillim Law)", url="https://www.statoids.com/yng.html",
                 verification_status="needs_corroboration", notes="Reused. Adamawa rows (NG.AD.*): 2006 census, area, headquarters."),
    "INECA": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Adamawa State: INEC state and LGA office addresses",
                  organisation="Independent National Electoral Commission (INEC)", url="https://wp1.inecnigeria.org/wp-content/uploads/2024/04/ADAMAWA-STATE.pdf",
                  verification_status="verified", archive_reference="Read via the Internet Archive (web.archive.org/web/2025id_/...); copy in database/research/data/inec_adamawa_lga_offices.pdf.",
                  notes="State office: No 33 Galadima Aminu Way/Bank Road, Jimeta Yola. LGA offices: Demsa — Demsa Town; Fufore — Gurin Road; Ganye; Girei — opposite the LG secretariat; Gombi — Sangere Gombi; Guyuk — Guyuk Town; Hong — Shangui Ward; Jada — Old Secretariat Jada; Lamurde; Madagali — Palace Road, Gulak; Maiha — behind the LG secretariat, Mayonguli Ward, Maiha; Mayo Belwa; Michika — Zaibadari Ward; Mubi North — inside the LG secretariat; Mubi South — opposite the District Head's palace, Gela; Numan; Shelleng — Old Local Govt. Secretariat Street, Shelleng Town; Song; Toungo; Yola North — Demsawo Ward; Yola South — Bako Ward, Yola Town."),
}
for k in ("ADSPC", "WPAD", "ATLAS"):
    SOURCES[k] = B57B.SOURCES[k]
for k in ("ADGOV", "DT24", "WAE"):
    SOURCES[k] = B59.SOURCES[k]
for k in ("UNESCO", "NCMML", "NCMMP", "NCMMM"):
    SOURCES[k] = B60.SOURCES[k]

PEO = "The peoples recorded for the LGA in this archive include the {p} (state festival and tourist tables, Wikipedia and Blench's Atlas)."
# slug: (name, Wikipedia title, Statoids HQ, 2006 pop, area km2, HQ used, explicit HQ sources, text)
LGAS = {
 "demsa": ("Demsa", "Demsa", "Demsa", 180251, 1825, "Demsa", "SW",
           "Demsa lies on the Benue (Wikipedia). " + PEO.format(p="Bachama (Bwatiye), Bata, Bwazza, Ɓile, Mbula and Waka")
           + " The state's festival table places Vunon (with prayers to the deity Nzeanzo), Mba-Pur, Jabba, Mba-Tambal and Mba-Zonhwo here."),
 "fufore": ("Fufore", "Fufore", "Fufore", 207287, 4972, "Fufore", "S",
            "It had the largest population of the state's LGAs at the 2006 census. Fufore is the seat of the Fufore Emirate, a second-class traditional state created in December 2024 (Adamawa State Government; Daily Trust). "
            + PEO.format(p="Bachama, Bata, Fulani, Mumuye and Verre") + " The state lists the Yadim and Sella Negis waterfalls, at Yadim village, among its tourist sites."),
 "ganye": ("Ganye", "Ganye", "Ganye", 164087, 1888, "Ganye", "S",
           "According to Wikipedia, Jada and Toungo LGAs were carved out of Ganye, which it calls the headquarters of the Sama (Chamba) people. Ganye is the seat of the Gangwari Ganye, the first-class ruler of the Ganye Chiefdom. "
           + PEO.format(p="Chamba, Fulani, Mumuye and Pere")),
 "girei": ("Girei", "Girei", "Girie", 129995, 1848, "Girei", "S",
           "Statoids spells it Girie. Girei lies on the Benue; according to Wikipedia the Fulani (Fulɓe) are the main people, with Bwatiye villages such as Greng and Labondo. "
           + PEO.format(p="Bata, Fulani, Mbula and Yungur") + " INEC's office for the LGA is opposite the LGA secretariat."),
 "gombi": ("Gombi", "Gombi", "Gombi", 146429, 1101, "Gombi", "S",
           "Gombi is the seat of the Gombi Chiefdom, a third-class traditional state created in December 2024 (Adamawa State Government; Daily Trust). "
           + PEO.format(p="Ga'anda, Hwana, Lala and Ngwaba") + " The state's festival table places the Kwayfa, Janda and Khombalta festivals at Ga'anda."),
 "guyuk": ("Guyuk", "Guyuk", "Guyuk", 177785, 757, "Guyuk", "S",
           "Guyuk lies on the Numan–Biu road (Wikipedia), and the state lists limestone and gypsum among the LGA's minerals. It is the home of the Lunguda, whose Simalama festival marks the end of the rainy season; the Elephant House, of great significance to the Lunguda, is No. 18 on the National Commission for Museums and Monuments' list of proposed national monuments."),
 "hong": ("Hong", "Hong, Nigeria", "Hong", 169126, 2626, "Hong", "SW",
          "Wikipedia calls Hong the capital of the Kilba (Holba), with Marghi, Kamwe and Fulani also present, and lists 12 wards. Hong is the seat of the Huba Chiefdom, a second-class traditional state created in December 2024 for the Huba (Kilba) people (Daily Trust; Adamawa State Government). The National Museum Hong is in the LGA secretariat complex (NCMM). The state's tables place the Kilba funeral festival Tiwa at Hong, and the Tapichima cold-water spring at Pella."),
 "jada": ("Jada", "Jada, Nigeria", "Jada", 168473, 2794, "Jada", "S",
          "According to Wikipedia it was created from the old Ganye LGA. " + PEO.format(p="Chamba, Fulani, Koma and Mumuye")
          + " The state lists the Koma Hills, at Koma village, as the home of the historic Koma people, and the Klashe Shatin festival of the Chamba and Mumuye."),
 "lamurde": ("Lamurde", "Lamurde", "Lamurde", 112803, 1171, "Lamurde", "S",
             "According to Wikipedia it was created from Numan LGA on 14 December 1990 and is inhabited mainly by the Bwatiye (Bachama) and Tsobo; Wikipedia's article on Numan calls Lamurde the spiritual or ancestral home of the Bwatiye, where the Hama Bachama has a second palace. Wikipedia gives an area of 2,098 km². The state's tables place the Wurokakai and Kwatte festivals of the Bachama here, and the Ruwan Zafi hot spring."),
 "madagali": ("Madagali", "Madagali", "Gulak", 134827, 821, "Gulak", "S",
              "INEC's office for the LGA is also at Gulak. According to Wikipedia the LGA was created in 1991 and borders Cameroon. Gulak is the seat of the Madagali Chiefdom, a second-class traditional state created in December 2024 (Daily Trust; Adamawa State Government). The LGA holds the Sukur Cultural Landscape, a UNESCO World Heritage Site and Adamawa's only declared national monument (No. 5), with the NCMM's interpretation centre at Sukur. "
              + PEO.format(p="Marghi and Sukur")),
 "maiha": ("Maiha", "Maiha", "Maiha", 111215, 1273, "Maiha", "SI",
           "Maiha borders Cameroon (Wikipedia) and is the seat of the Maiha Emirate, a third-class traditional state created in December 2024 (Daily Trust; Adamawa State Government). It is the home of the Nzanyi, whose initiation festivals Alalile and Wagurwa the state lists."),
 "mayo-belwa": ("Mayo Belwa", "Mayo-Belwa", "Mayo-Belwa", 153129, 1768, "Mayo Belwa", "S",
                "According to a tradition recorded by Wikipedia, the town was founded by the Bata of the Mayo Ine valley as Gabalwa and took its present name after the forces of Lamido Lawal of Adamawa took it. A meteorite of about 5 kg fell in the town on 3 August 1974 (Wikipedia). "
                + PEO.format(p="Fulani, Mumuye, Waka and Yandang") + " The state lists the Gorobi rock formations and the Yandang festivals Phuki and Here-Yawetti."),
 "michika": ("Michika", "Michika", "Michika", 155302, 967, "Michika", "SW",
             "Wikipedia gives the Kamwe form Mwe-cika, names the Kamwe as the main people and says the LGA was created in 1976; it borders Cameroon. Michika is the seat of the Michika Chiefdom, a second-class traditional state created in December 2024 (Daily Trust; Adamawa State Government). The state lists the Zhita and Yawle festivals of the Higgi (Kamwe)."),
 "mubi-north": ("Mubi North", "Mubi North", "Mubi", 151072, 903, "Mubi", "S",
                "INEC's office for the LGA is inside the LGA secretariat. Wikipedia places Adamawa State University and the Federal Polytechnic, Mubi, in the town. Mubi is the seat of the Mubi Emirate, which Wikipedia lists as a first-class emirate covering Mubi North and Mubi South. "
                + PEO.format(p="Fali")),
 "mubi-south": ("Mubi South", "Mubi South", "Gella", 128937, 414, "Gella", "S",
                "INEC's office for the LGA is at Gela, opposite the district head's palace. It is part of the Mubi Emirate (Wikipedia). It is the home of the Gude, whose festivals Wangirwa and Vulma (at Gella) the state lists."),
 "numan": ("Numan", "Numan, Nigeria", "Numan", 90723, 905, "Numan", "S",
           "Numan lies at the confluence of the Benue and Gongola rivers (Wikipedia). It is the seat of the Hama Bachama, the first-class ruler of the Bachama (Bwatiye), whose palace is at Numan; Lamurde LGA was created from Numan in 1990 (Wikipedia). "
           + PEO.format(p="Bachama and Kaan") + " The state lists the Bachama festival Kadyaga here."),
 "shelleng": ("Shelleng", "Shelleng", "Shelleng", 149069, 1359, "Shelleng", "S",
              "INEC's office for the LGA is on Old Local Government Secretariat Street in Shelleng town. The state lists Kiri Dam, at Kiri, among its tourist sites, and the Menjauli festival of the Kanakuru (Dera) and the Lamushi initiation of the Libbo. "
              + PEO.format(p="Dera and Kaan")),
 "song": ("Song", "Song, Nigeria", "Song", 192697, 4256, "Song", "S",
          "Wikipedia lists among its districts Song, Dumne, Kilange, Gudu and Mboi. The Yungur Chiefdom, a third-class traditional state created in December 2024, has its headquarters at Dumne in the LGA (Adamawa State Government; Daily Trust). The Three Sisters Rock is No. 87 on the NCMM's list of proposed national monuments. "
          + PEO.format(p="Mboi and Yungur")),
 "toungo": ("Toungo", "Toungo, Nigeria", "Toungo", 52040, 5478, "Toungo", "S",
            "It is the largest LGA of the state by area and had the smallest population at the 2006 census (Statoids). Wikipedia's article on Ganye says Toungo was carved out of Ganye. The state lists the Makam Walls and the Sassa Waterfalls among its tourist sites. "
            + PEO.format(p="Chamba and Mumuye")),
 "yola-north": ("Yola North", "Yola North", "Jimeta", 198247, 718, "Jimeta", "S",
                "Yola North includes Jimeta, part of the Yola metropolitan area (Wikipedia). The National Museum Yola and INEC's state office are in Jimeta. " + PEO.format(p="Laka and Mumuye")),
 "yola-south": ("Yola South", "Yola South", "Yola", 194607, 113, "Yola", "S",
                "Yola is the capital of Adamawa State and the seat of the Lamido of Adamawa, ruler of the Adamawa Emirate; the National Museum Fombina is in the Lamido's palace (NCMM; Adamawa State Planning Commission). It is the smallest LGA of the state by area (Statoids). "
                + PEO.format(p="Fulani, Mumuye and Verre")),
}
for slug, (name, wt, *_r) in LGAS.items():
    SOURCES[f"W_{slug}"] = dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=wt, organisation="Wikipedia", url=W(wt),
                                verification_status="needs_corroboration", notes=f"Wikipedia article on {name} LGA, consulted {ACCESSED}.")
# extra sources keyed by phrases in the text
KEYS = {"ADSPC": ["state's festival table", "state lists", "state's tables", "Planning Commission"], "WPAD": ["peoples recorded"], "ATLAS": ["Blench's Atlas"],
        "ADGOV": ["December 2024"], "DT24": ["December 2024"], "WAE": ["Lamido"],
        "UNESCO": ["UNESCO"], "NCMML": ["declared national monument"], "NCMMP": ["proposed national monuments"], "NCMMM": ["National Museum", "interpretation centre"]}
XREF = {"lamurde": ["W_numan"], "toungo": ["W_ganye"], "mubi-south": ["W_mubi-north"]}
NAMES_OF = {"S": "Statoids", "W": "Wikipedia", "I": "INEC"}
REUSE_PLACE = {"yola-south": "@places:yola"}


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


def by(agree):
    n = [NAMES_OF[c] for c in "SWI" if c in agree]
    return n[0] if len(n) == 1 else ", ".join(n[:-1]) + " and " + n[-1]


def text(slug):
    name, wt, s_hq, pop, area, hq, agree, extra = LGAS[slug]
    t = f"{name} is a local government area of Adamawa State with its headquarters at {hq} (according to {by(agree)})."
    t += f" It had a population of {pop:,} at the 2006 census and an area of about {area:,} km² (Statoids)."
    return (t + (" " + extra if extra else "")).strip()


RECORDS, UPDATES, NAMES, STATS = [], [], [], []
for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
    upd = dict(description=text(slug))
    srcs = [("STAT", f"{name}: headquarters, 2006 population, area"), ("INECA", f"{name}: INEC LGA office address"), (f"W_{slug}", f"{name}: Wikipedia article")]
    srcs += [(x, f"{name}: {SOURCES[x]['title']}") for x in XREF.get(slug, [])]
    for k, kws in KEYS.items():
        if any(w in extra for w in kws):
            srcs.append((k, f"{name}: {SOURCES[k]['title']}"))
    if slug in REUSE_PLACE:
        upd["headquarters_place_id"] = REUSE_PLACE[slug]
    else:
        key = f"hq_{slug}"
        hsrc = ([("STAT", f"Headquarters of {name}")] if "S" in agree else []) + ([(f"W_{slug}", f"Headquarters of {name}")] if "W" in agree else []) \
               + ([("INECA", f"LGA office behind the LGA secretariat at {hq}")] if "I" in agree else [])
        two = len(agree) >= 2
        RECORDS.append(dict(key=key, table="places", evidence="multiple_sources" if two else "single_reliable_source", level="well_documented" if two else "reported",
                            fields=dict(place_type="town", name=hq, slug=slugify(hq), admin_unit_id=f"@admin_units:lga:adamawa/{slug}", status="existing",
                                        summary=f"{hq} is the headquarters of {name} Local Government Area, Adamawa State."), srcs=hsrc))
        upd["headquarters_place_id"] = f"@key:{key}"
    UPDATES.append(dict(ref=f"@admin_units:lga:adamawa/{slug}", fields=upd, srcs=srcs))
    STATS.append(dict(record=f"@admin_units:lga:adamawa/{slug}", metric="population", value_low=pop, reference_year=2006, method="census",
                      notes="2006 census, as given by Statoids", source="STAT", evidence="single_reliable_source", level="reported"))
    STATS.append(dict(record=f"@admin_units:lga:adamawa/{slug}", metric="area_km2", value_low=area, method="other",
                      notes="Area as given by Statoids" + ("; Wikipedia gives 2,098 km²" if slug == "lamurde" else ""), source="STAT", evidence="single_reliable_source", level="reported"))
NAMES += [
    dict(record="hq_girei", name="Girie", name_type="spelling_variant", usage_notes="Statoids.", srcs=["STAT"]),
    dict(record="hq_mubi-south", name="Gela", name_type="spelling_variant", usage_notes="INEC's spelling.", srcs=["INECA"]),
    dict(record="hq_michika", name="Mwe-cika", name_type="endonym", usage_notes="Kamwe form (Wikipedia).", srcs=["W_michika"]),
    dict(record="hq_mayo-belwa", name="Gabalwa", name_type="historical", usage_notes="Earlier name of the settlement, according to a tradition recorded by Wikipedia.", srcs=["W_mayo-belwa"]),
]
RELATIONS = []
GAPS = [
    ("Adamawa: no official table of LGA headquarters", "Seventeen headquarters rest on Statoids alone (Wikipedia says only 'a town and LGA'); INEC names the secretariat town only for Maiha. An official Adamawa State list would settle them."),
    ("Adamawa: Lamurde area", "Statoids gives 1,171 km² and Wikipedia 2,098 km²; Statoids' figure is recorded."),
    ("Adamawa: LGA creation dates", "Only Michika (1976, Wikipedia), Lamurde (14 December 1990, from Numan) and Madagali (1991) are dated, and Jada and Toungo are said to come from Ganye without a date."),
    ("Adamawa: Shelleng festival name", "Wikipedia names a 'Mendamo festival' in Shelleng; the state's table has Menjauli (Kanakuru, Shelleng). Whether they are the same is not shown; only Menjauli is recorded."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=UPDATES,
                relations=RELATIONS, statistics=STATS,
                scope="Adamawa LGA profiles: headquarters, 2006 population and area, short sourced descriptions (21 LGAs).")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 061 — Adamawa LGA profiles", "",
         f"Researched {ACCESSED}. Completes the LGA layer of Phase 3 for Adamawa. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **21 LGA descriptions**, filling fields that were empty.",
         f"- **{len(RECORDS)} headquarters towns**, created as places. Yola South reuses the existing Yola place.",
         "- **Figures:** the 2006 population and area of each LGA, from Statoids.",
         "- **New official source:** INEC's list of its Adamawa LGA offices, saved in the repo. It fixes Maiha's headquarters, and it supports Gulak (Madagali) and Gela (Mubi South).",
         "- **Headquarters strength:** four are well documented (Demsa, Hong, Michika and Maiha). Sixteen rest on Statoids alone, because no official Adamawa table was found (listed as a gap).",
         "- **Each description draws on records already in the archive** from batches 057b, 059 and 060: the LGA's peoples, its traditional state, and its heritage sites and festivals.",
         "- **Not used from Wikipedia:**",
         "  - implausible 2006 figures for Lamurde and Shelleng",
         "  - '2016' population estimates",
         "  - the mountain ranges placed in Yola North and Yola South",
         "  - the language survey of displaced persons, which is not a census",
         "- The pages stay under 300 words, so they are noindex and the sitemap is unchanged.", "",
         "## Headquarters", "", "| LGA | Headquarters | Statoids | Sources | Level |", "|---|---|---|---|---|"]
    for slug, (name, wt, s_hq, pop, area, hq, agree, extra) in LGAS.items():
        L.append(f"| {name} | {hq} | {s_hq} | {by(agree)} | {'well documented' if len(agree) >= 2 else 'reported'} |")
    L += ["", "## The 21 descriptions", ""] + [f"**{LGAS[s][0]}** ({words(text(s))} words). {text(s)}\n" for s in LGAS]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_061_adamawa_lga_profiles.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_061_adamawa_lga_profiles_REVIEW.md", "w").write(report())
    print(f"places={len(RECORDS)} updates={len(UPDATES)} stats={len(STATS)} sources={len(SOURCES)} names={len(NAMES)}")
