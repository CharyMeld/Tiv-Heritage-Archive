"""
Research batch 101 — Katsina (Phase 3): traditional institutions. Researched 2026-10-02. Pattern: batches 089 and 095.

Polities (3):
  * Kingdom of Katsina (Wikipedia 'Kingdom of Katsina'): Hausa kingdom from the early second millennium to its conquest
    in 1805/6; Kumayo's dynasty at Durbi ta Kusheyi near Mani; Muhammad Korau (c. 1445–95) founded the Wangarawa dynasty;
    al-Maghili's visit 1493; Gobirau mosque; exiles founded Maradi.
  * Katsina Emirate (same article; 'Abdulmumini Kabir Usman'): Fulani dynasty under Sokoto from 1806; the present emir,
    Abdulmumini Kabir Usman (born 1949, Sullubawa clan, 50th Emir), presided at the palace on 2 May 2026 (Katsina State
    Government press release).
  * Daura Emirate (Wikipedia 'Daura Emirate', 'Faruk Umar Faruk'): Bayajidda and the Kusugu well; one of the Hausa
    Bakwai; Fulani emirate under Malam Ishaku from 1805; Muhammad Bashar 1966–2007; Faruk Umar Faruk (born 1928),
    60th Emir from 28 February 2007, reported in office in June 2025 (NAN via The Gazette, 29 June 2025). A search
    summary dated a Daura report to 2026, but the article itself is from December 2023 — not used for 2026.
"""
import json, re, sys
import batch_099_katsina_languages_peoples as P99

ACCESSED = "2026-10-02"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
WS = lambda t, n: dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title=t, organisation="Wikipedia", url=W(t),
                       verification_status="needs_corroboration", notes=n + f" Accessed {ACCESSED}.")
SOURCES = {
    "WKK": WS("Kingdom of Katsina", "Hausa kingdom from the early second millennium to its conquest in 1805/6; continues as the Katsina Emirate; Kumayo's dynasty at Durbi ta Kusheyi near Mani (Durbawa); Muhammad Korau c. 1445–95 (Wangarawa dynasty); al-Maghili visited 1493; Gobirau mosque; Sarkin Katsina Halidu's death ended the Korau dynasty; exiles founded Maradi; jihad leaders Umaru Dumyawa (Sullubawa) and Muhamman Dikko (Yandakawa); insignia Gajere and the bronze pot of Korau."),
    "WAKU": WS("Abdulmumini Kabir Usman", "Born 9 January 1949; Emir of Katsina; Fulani of the Sullubawa clan; 50th emir and 4th of the Sullubawa dynasty, succeeding his father Muhammadu Kabir Usman."),
    "KSG26": dict(source_type="official_website", source_kind="government_publication", source_tier=1, title="Gov. Radda, Gov. Yusuf, Others Grace Turbaning of Three Illustrious District Heads in Katsina",
                  organisation="Katsina State Government", publication_date="2026-05-02",
                  url="https://katsinastate.gov.ng/2026/05/02/gov-radda-gov-yusuf-others-grace-turbaning-of-three-illustrious-district-heads-in-katsina/",
                  verification_status="verified", notes="The ceremony at the Emir's Palace, Katsina, was presided over by the Emir of Katsina, Alhaji (Dr.) Abdulmumini Kabir Usman."),
    "WDE": WS("Daura Emirate", "Bayajidda killed the snake Sarki at the Kusugu well and married Queen Daurama; one of the Hausa Bakwai; Fulani emirate under Malam Ishaku from 1805; the British made Malam Musa, of a rival Hausa line, emir; Muhammad Bashar 1966–2007; Umar Faruk Umar from 28 February 2007."),
    "WFUF": WS("Faruk Umar Faruk", "Born 1928; 60th Emir of Daura from 28 February 2007, succeeding Muhammadu Bashar; Daura durbar at Id-el-Kabir."),
    "GAZ25": dict(source_type="news", source_kind="news", source_tier=3, title="Gov. Radda hails Emir of Daura's commitment to peace in Katsina", organisation="News Agency of Nigeria (via The Gazette)",
                  publication_date="2025-06-29", url="https://gazettengr.com/gov-radda-hails-emir-of-dauras-commitment-to-peace-in-katsina/", verification_status="needs_corroboration",
                  notes="Governor Dikko Radda commended the Emir of Daura, Umar Farouk, at a turbaning ceremony at the emir's palace."),
    "W_daura": P99.SOURCES["W_daura"],
    "WKAT": P99.SOURCES["WKAT"],
}
TEXT = {
 "kingdom": """The Kingdom of Katsina was a Hausa kingdom centred on the city of Katsina, founded early in the second millennium and conquered in the jihad of Usman dan Fodio in 1805–6 (Wikipedia). Tradition names Kumayo, a grandson of Bayajidda, as founder of its first dynasty, at Durbi ta Kusheyi near Mani, among the Durbawa. About 1445 Muhammad Korau seized the throne and founded the Wangarawa dynasty; the scholar al-Maghili visited in 1493, and the Gobirau mosque became a centre of learning. At its height Katsina drew scholars from across the western Sudan and its influence reached Maradi and Zamfara. After the Fulani conquest the exiled Hausa rulers founded Maradi, now in the Republic of Niger (Wikipedia).""",
 "katsina": """The Katsina Emirate is the Fulani emirate that succeeded the Hausa Kingdom of Katsina after the jihad of 1805–6, under the Sokoto Caliphate; jihad leaders there included Umaru Dumyawa of the Sullubawa and Muhamman Dikko of the Yandakawa (Wikipedia). Its royal insignia include Gajere, the short sword with which Korau took the throne, and the bronze pot of Korau. The present Emir, Abdulmumini Kabir Usman, born in 1949, is a Fulani of the Sullubawa clan and the 50th Emir, succeeding his father, Muhammadu Kabir Usman (Wikipedia); in May 2026 he presided over the turbaning of three district heads at the palace in Katsina (Katsina State Government).""",
 "daura": """The Daura Emirate is centred on Daura, in northern Katsina State. Tradition holds that Bayajidda killed the snake that kept the people of Daura from the Kusugu well and married its queen, Daurama, and Daura is counted among the seven Hausa states, the Hausa Bakwai; Wikipedia calls it the spiritual home of the Hausa people. In 1805 the Fulani leader Malam Ishaku made it an emirate; the deposed Hausa rulers set up rival states nearby, and the British later made Malam Musa, of the Hausa line, emir (Wikipedia). Muhammad Bashar reigned from 1966 to 2007, and Faruk Umar Faruk, born in 1928, became the 60th Emir on 28 February 2007 (Wikipedia); he was reported in office in June 2025 (NAN).""",
}
REC = [  # key, name, slug, srcs, level, extra
    ("kingdom", "Kingdom of Katsina", "kingdom-of-katsina", ["WKK"], "well_documented",
     dict(is_extant=0, founded_text="early second millennium (Wikipedia)", founded_precision="unknown", ended_year=1806, ended_text="1805–6, Fulani jihad", ended_precision="circa")),
    ("katsina", "Katsina Emirate", "katsina-emirate", ["WKK", "WAKU", "KSG26"], "well_documented", dict(is_extant=1, founded_year=1806, founded_text="1805–6, after the Fulani jihad (Wikipedia)", founded_precision="circa")),
    ("daura", "Daura Emirate", "daura-emirate", ["WDE", "WFUF", "GAZ25", "W_daura"], "well_documented", dict(is_extant=1, founded_year=1805, founded_text="1805, as a Fulani emirate (Wikipedia); the Hausa state of Daura is far older", founded_precision="year")),
]
RECORDS = []
for key, name, slug, srcs, lvl, extra in REC:
    t = TEXT[key]
    f = dict(polity_type="kingdom" if key == "kingdom" else "emirate", name=name, slug=slug, summary=t.split(". ")[0] + ".", description=t)
    f.update(extra)
    RECORDS.append(dict(key=key, table="polities", evidence="multiple_sources" if len(srcs) > 1 else "single_reliable_source", level=lvl, fields=f, srcs=[(s, name) for s in srcs]))
K = lambda l: f"@admin_units:lga:katsina/{l}"
RELATIONS = [
    dict(frm="katsina", type="located_in", to=K("katsina"), source="KSG26", evidence="multiple_sources", level="well_documented", notes="The Emir's Palace, Katsina (Katsina State Government, 2026). Seat (not a statement of full jurisdiction)."),
    dict(frm="daura", type="located_in", to=K("daura"), source="WDE", evidence="multiple_sources", level="well_documented", notes="Daura town (Wikipedia). Seat (not a statement of full jurisdiction)."),
    dict(frm="kingdom", type="located_in", to="@admin_units:state:katsina", source="WKK", evidence="single_reliable_source", level="well_documented", notes="Centred on the city of Katsina (Wikipedia)."),
    dict(frm="kingdom", type="located_in", to=K("mani"), source="WKK", evidence="single_reliable_source", level="reported", notes="Kumayo's early seat, Durbi ta Kusheyi, was near Mani (Wikipedia)."),
    dict(frm="katsina", type="associated_with", to="kingdom", role="succeeded the Hausa kingdom after the 1805–6 jihad", source="WKK", evidence="single_reliable_source", level="well_documented", notes="Wikipedia (Kingdom of Katsina)."),
    dict(frm="kingdom", type="associated_with", to="@ethnic_groups:hausa", role="Hausa kingdom (Hausa Bakwai)", source="WKK", evidence="single_reliable_source", level="well_documented", notes="Wikipedia."),
    dict(frm="katsina", type="associated_with", to="@ethnic_groups:fulani", role="Fulani dynasty since 1806 (the Sullubawa)", source="WAKU", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Kingdom of Katsina; Abdulmumini Kabir Usman)."),
    dict(frm="daura", type="associated_with", to="@ethnic_groups:hausa", role="'the spiritual home of the Hausa people'; Hausa Bakwai", source="W_daura", evidence="multiple_sources", level="well_documented", notes="Wikipedia (Daura; Daura Emirate)."),
]
NAMES = [
    dict(record="kingdom", name="Sarkin Katsina", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WKK"]),
    dict(record="daura", name="Sarkin Daura", name_type="alternative", usage_notes="Title of the ruler (Wikipedia).", srcs=["WFUF"]),
]
GAPS = [
    ("Katsina: the Emir of Daura in 2026", "The latest source read placing Faruk Umar Faruk (born 1928) in office is June 2025. A 2026 confirmation is needed."),
    ("Katsina: Katsina emir's accession", "The year Abdulmumini Kabir Usman succeeded his father is not given in the sources read."),
    ("Katsina: emirate jurisdictions", "The LGAs of each emirate are not listed in a source read."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Katsina traditional institutions: the Kingdom of Katsina, the Katsina Emirate and the Daura Emirate.")


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def report():
    L = ["# Research batch 101 — Katsina: traditional institutions", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## What it adds", "",
         "- **3 polities:**",
         "  - the **Kingdom of Katsina** (Hausa, to 1805–6): Kumayo, Korau, the Gobirau mosque, and Maradi founded by its exiles",
         "  - the **Katsina Emirate** (Fulani, from 1806): Emir Abdulmumini Kabir Usman, confirmed presiding in May 2026 by the state government",
         "  - the **Daura Emirate**: Bayajidda and the Kusugu well, the 'spiritual home of the Hausa'. Emir Faruk Umar Faruk (born 1928) has reigned since 2007.",
         "- **Handled with care:** the latest source placing the Emir of Daura in office is **June 2025**. A search result claiming a 2026 Daura report was checked, and the article was from December 2023, so it was not used. A 2026 confirmation is listed as a gap.",
         "- **Links:** seats (Katsina, Daura; Kumayo's early seat near Mani), peoples (Hausa, Fulani) and the kingdom-to-emirate succession.",
         "- All pages are under 300 words, so the sitemap is unchanged.", ""]
    for key, name, slug, srcs, lvl, extra in REC:
        L += [f"## {name} ({words(TEXT[key])} words; {lvl})", "", f"> {TEXT[key]}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_101_katsina_institutions.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_101_katsina_institutions_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} names={len(NAMES)} sources={len(SOURCES)}")
