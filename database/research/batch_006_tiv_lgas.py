"""
Research batch 006 — the Tiv people and the Tiv language linked to the LGAs where
they are documented (researched 2026-09-24).

Benue State — the fourteen Tiv-speaking LGAs:
  MOFEP  Benue State Ministry of Finance and Economic Planning: the Tiv-speaking area has
         fourteen of the 23 LGAs; its traditional council has the Tor Tiv as chairman
         (the count, not the names);
  IAMB   I am Benue, "The Tiv People of Benue State": names the 14 LGAs by zone;
  IAMB2  I am Benue, "Indigenous administrative structure and institutions": the same 14
         LGAs grouped under the six Tiv intermediate areas (same publisher as IAMB, so
         not counted as independent);
  WBS    Wikipedia, "Benue State": Tiv in all seven LGAs of Zone A and of Zone B, whose
         LGAs are named in Wikipedia's senatorial-district articles (WNE, WNW); the
         Ethnologue table it quotes lists Tiv for 10 of the 14;
  ATLAS  Blench's Atlas: Makurdi, Gwer, Gboko, Kwande, Vandeikya and Katsina Ala LGAs.
  Each of the 14 is named by I am Benue and by Wikipedia, and the count of 14 matches the
  Ministry -> 'multiple_sources'.

Outside Benue (Blench's Atlas): Lafia LGA (Nasarawa); Wukari, Takum and Bali LGAs
(Taraba). Wikipedia's LGA articles also name Tiv for Wukari and Takum -> multiple;
Lafia and Bali from the Atlas only -> single_reliable_source.

Existing links are never changed; only new rows are added.
"""
import json, sys

ACCESSED = "2026-09-24"
SOURCES = {
    "MOFEP": dict(source_type="official_website", title="History Of Benue State", organisation="Benue State Ministry of Finance and Economic Planning",
                  url="https://www.mofep.be.gov.ng/explore_benue", verification_status="verified", notes="Reused (existing source)."),
    "WBS": dict(source_type="encyclopedia", title="Benue State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Benue_State",
                verification_status="needs_corroboration", notes="Reused (existing source)."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", author="Roger Blench", organisation="Kay Williamson Educational Foundation",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="needs_corroboration",
                  notes="Reused (batch 005)."),
    "IAMB": dict(source_type="website", title="The Tiv People of Benue State", organisation="I am Benue",
                 url="http://www.iambenue.com/benue-state/ethnic-composition/the-tiv-people-of-benue-state/", verification_status="needs_corroboration",
                 notes="Community website registered in Nigeria (Makurdi). The Tiv of Benue State live in fourteen LGAs. Zone A: Logo, Ukum, Katsina-Ala, Ushongo, Kwande, Vandeikya, Konshisha. Zone B: Gboko, Tarka, Buruku, Gwer-West, Gwer [East], Makurdi, Guma."),
    "IAMB2": dict(source_type="website", title="Indigenous administrative structure and institutions", organisation="I am Benue",
                  url="http://www.iambenue.com/benue-state/benue-state/indigenous-administrative-structure-and-institutions/", verification_status="needs_corroboration",
                  notes="Same publisher as 'The Tiv People of Benue State'. It lists the Tor Tiv's fourteen LGAs under six intermediate areas: Jemgbagh (Buruku, Gboko, Tarka), Jechira (Vandeikya, Konshisha), Kwande (Kwande, Ushongo), Sankera (Ukum, Logo, Katsina-Ala), Lobi (Makurdi, Guma) and Gwer (Gwer East, Gwer West)."),
    "WNE": dict(source_type="encyclopedia", title="Benue North-East senatorial district", organisation="Wikipedia",
                url="https://en.wikipedia.org/wiki/Benue_North-East_senatorial_district", verification_status="needs_corroboration",
                notes="Zone A: Katsina-Ala, Konshisha, Kwande, Logo, Ukum, Ushongo, Vandeikya."),
    "WNW": dict(source_type="encyclopedia", title="Benue North-West senatorial district", organisation="Wikipedia",
                url="https://en.wikipedia.org/wiki/Benue_North-West_senatorial_district", verification_status="needs_corroboration",
                notes="Zone B: Buruku, Gboko, Tarka, Guma, Makurdi, Gwer [East], Gwer West."),
    "WWUK": dict(source_type="encyclopedia", title="Wukari", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Wukari", verification_status="needs_corroboration",
                 notes="'The major Language spoken in Wukari LGA is Tiv and Wapan.'"),
    "WTAK": dict(source_type="encyclopedia", title="Takum", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Takum", verification_status="needs_corroboration",
                 notes="Tiv among the peoples of Takum LGA (the statement is marked 'citation needed')."),
}

ZONE_A = ["logo", "ukum", "katsina-ala", "ushongo", "kwande", "vandeikya", "konshisha"]
ZONE_B = ["gboko", "tarka", "buruku", "gwer-west", "gwer-east", "makurdi", "guma"]
ATLAS_BENUE = {"makurdi", "gboko", "kwande", "vandeikya", "katsina-ala"}          # Atlas names under the same LGA names today
ETHNOLOGUE = {"buruku", "gboko", "guma", "gwer-east", "gwer-west", "katsina-ala", "kwande", "makurdi", "ushongo", "vandeikya"}
AREA = {"buruku": "Jemgbagh", "gboko": "Jemgbagh", "tarka": "Jemgbagh", "vandeikya": "Jechira", "konshisha": "Jechira", "kwande": "Kwande", "ushongo": "Kwande",
        "ukum": "Sankera", "logo": "Sankera", "katsina-ala": "Sankera", "makurdi": "Lobi", "guma": "Lobi", "gwer-east": "Gwer", "gwer-west": "Gwer"}
NAME = lambda s: s.replace("-", " ").title().replace("Katsina Ala", "Katsina-Ala")

RELATIONS = []
for lga in ZONE_A + ZONE_B:
    zone = "A (Benue North-East)" if lga in ZONE_A else "B (Benue North-West)"
    also = []
    if lga in ETHNOLOGUE: also.append("the Ethnologue table quoted by Wikipedia")
    if lga in ATLAS_BENUE: also.append("Blench's Atlas")
    if lga == "gwer-east": also.append("Blench's Atlas under the older name 'Gwer'")
    notes = (f"One of the fourteen Tiv-speaking LGAs of Benue State, in Zone {zone}. It is named by I am Benue and by Wikipedia (Tiv in all seven LGAs of the zone), "
             f"and the Benue State Ministry of Finance gives the count of fourteen. I am Benue places it in the {AREA[lga]} intermediate area."
             + (f" Also: {'; '.join(also)}." if also else ""))
    src = "ATLAS" if lga in ATLAS_BENUE else "IAMB"
    RELATIONS.append(dict(frm="@ethnic_groups:tiv", type="present_in", to=f"@admin_units:lga:{lga}", source=src, evidence="multiple_sources", notes=notes))
    RELATIONS.append(dict(frm="@languages:tiv", type="spoken_in", to=f"@admin_units:lga:{lga}", source=src, evidence="multiple_sources", notes=notes))

OUTSIDE = [
    ("wukari", "Taraba", "multiple_sources", "ATLAS", "Blench's Atlas; Wikipedia (Wukari): Tiv and Wapan are the major languages of the LGA."),
    ("takum", "Taraba", "multiple_sources", "ATLAS", "Blench's Atlas; Wikipedia (Takum) names the Tiv among the LGA's peoples (marked 'citation needed' there)."),
    ("bali", "Taraba", "single_reliable_source", "ATLAS", "Blench's Atlas only."),
    ("lafia", "Nasarawa", "single_reliable_source", "ATLAS", "Blench's Atlas only. The archive owner also states that Nasarawa has many Tiv speakers (state level, batch 003)."),
]
for lga, st, ev, src, n in OUTSIDE:
    RELATIONS.append(dict(frm="@ethnic_groups:tiv", type="present_in", to=f"@admin_units:lga:{lga}", source=src, evidence=ev, notes=n))
    RELATIONS.append(dict(frm="@languages:tiv", type="spoken_in", to=f"@admin_units:lga:{lga}", source=src, evidence=ev, notes=n))

GAPS = [
    ("Tiv in other LGAs of Nasarawa, Taraba, Plateau and Cross River", "Blench's Atlas names only Lafia (Nasarawa) and Wukari, Takum and Bali (Taraba), and several LGAs have been created since its sources were compiled. Tiv communities in other LGAs of these states (and in Plateau and Cross River, where the Tiv are documented at state level) need sources."),
    ("Tiv communities in the nine Idoma–Igede LGAs", "Not recorded. The sources consulted describe those LGAs as Idoma and Igede areas."),
    ("The six Tiv intermediate areas", "Jemgbagh, Jechira, Kwande, Sankera, Lobi and Gwer are given by one publisher (I am Benue). They are named only in the notes on these links, not as records, until a second source confirms them."),
    ("Evidence level of the earlier state-level Tiv links", "The Benue State links from batch 003 stay 'single reliable source' (existing rows are never changed). The Benue State Government and its Finance Ministry now corroborate Tiv presence in Benue, and an editor may raise the level in the admin screen."),
    ("'Gwer' and 'Gwer East'", "Some sources write 'Gwer' for the LGA now called Gwer East. It is treated as the same LGA here."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=[], names=[], gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="The Tiv people and the Tiv language linked to the fourteen Tiv-speaking LGAs of Benue State and to LGAs in Taraba and Nasarawa.")


def report():
    L = ["# Research batch 006 — Tiv people and Tiv language: LGA links", "",
         f"Researched {ACCESSED}. No new pages. This batch only adds links (\"has documented presence in\" and \"is spoken in\") from the existing Tiv records to LGAs.", "",
         "## Sources", ""] + [f"- **{k}** — {s['title']} ({s['organisation']}). {s['url']}. {s['notes']}" for k, s in SOURCES.items()]
    L += ["", "## The fourteen Tiv-speaking LGAs of Benue State", "",
          "| LGA | Zone | Intermediate area (I am Benue) | I am Benue | Wikipedia zone | Ethnologue table | Blench's Atlas | Evidence |", "|---|---|---|---|---|---|---|---|"]
    for lga in ZONE_A + ZONE_B:
        L.append(f"| {NAME(lga)} | {'A' if lga in ZONE_A else 'B'} | {AREA[lga]} | yes | yes | {'yes' if lga in ETHNOLOGUE else '—'} | "
                 f"{'yes' if lga in ATLAS_BENUE else ('as Gwer' if lga == 'gwer-east' else '—')} | multiple sources |")
    L += ["", "The Benue State Ministry of Finance and Economic Planning confirms that there are **fourteen** Tiv-speaking LGAs, but does not name them.", "",
          "## Outside Benue State", "", "| LGA | State | Evidence | Notes |", "|---|---|---|---|"]
    L += [f"| {NAME(l)} | {st} | {ev.replace('_', ' ')} | {n} |" for l, st, ev, _, n in OUTSIDE]
    L += ["", f"Total: **{len(RELATIONS)} links** ({len(RELATIONS)//2} for the Tiv people and {len(RELATIONS)//2} for the Tiv language).", "",
          "## Not recorded — research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## What changes for visitors, the AI and Google", "",
          "- The Tiv people and Tiv language pages list the 18 LGAs, and each of those LGA pages lists the Tiv.",
          "- The AI can answer \"What languages are spoken in Gboko?\" and \"Where do the Tiv live?\" down to LGA level.",
          "- No new pages and no text changes, so the sitemap (2,977) and indexing do not change. New sources stay noindex.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_006_tiv_lgas.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_006_tiv_lgas_REVIEW.md", "w").write(report())
    print(f"relations={len(RELATIONS)} sources={len(SOURCES)} gaps={len(GAPS)}")
