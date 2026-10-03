"""
Research batch 049 — Plateau (Phase 3): the annual cultural festivals listed by the state government.
Researched 2026-09-30.

Source: Plateau State Government, "Plateau State is open for business" (investment booklet, July 2022),
section 1.4 "Some annual cultural festivals in Plateau State" — Tier 1 (reused from batch 048). The table
gives festival, area, date and a remark; the booklet notes a recent tendency to merge several
traditional festivals into one 'mega festival' to save costs and attract participants.

Records (10 new): Afizere Culture Festival, Bit Goemai, Pan Cultural Festival, Pandam Fishing Festival,
Irigwe New Year Celebration, Puskaat, Pu'us Kang Mushere, Pusdung, Ron/Kulere Festival, Bogghom
Cultural Festival. Nzem Berom (existing, batch 047) gets the four LGAs the list names.
People links only where the festival's name or remark names the people (Pan -> the Kofyar record, as
the Atlas identifies the Pan-speaking people as Kofyar). Place links: the LGA named, or the LGA of the
town named (Miango is in Bassa LGA per Wikipedia; 'Kwall' beside Miango is the Bassa village of that
name, not Kwal, headquarters of Kanke — so only Bassa is linked).
Not recorded: Taroh Cultural Day (Langtang North/South, March/April) — very probably the Tarok festival
recorded as Ilum Otarok, but no source equates them; Resettlement Day (Langtang South), the Thaar
Cultural Community Festival (Wase) and the Zarachi Festival (Kwall and Miango) — no people or
description given. Gaps.
"""
import json, re, sys

ACCESSED = "2026-09-30"
SOURCES = {
    "OSS": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Plateau State is open for business (investment booklet)",
                organisation="Plateau State Government (One-Stop Shop)", publication_date="2022-07",
                url="https://plateaustate.gov.ng/uploads/Investing-in-Plateau-State-OSS-booklet.pdf", verification_status="verified", notes="Reused (batch 048). Section 1.4: annual cultural festivals."),
    "WPANK": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Pankshin", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Pankshin",
                  verification_status="needs_corroboration", notes="Reused (batch 048). Mentions Pusdung as the annual cultural festival of the Ngas."),
    "WBASSA": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Bassa, Plateau State", organisation="Wikipedia",
                   url="https://en.wikipedia.org/wiki/Bassa,_Plateau_State", verification_status="needs_corroboration", notes="Reused (batch 048). Miango and Kwal among the towns and villages of Bassa LGA."),
}
LEAD = "The Plateau State Government lists {n} among the state's annual cultural festivals (2022)"
MERGE = " The same list notes a recent tendency to bring several traditional festivals together into one larger festival, to save costs and attract more participants."
# key: (name, slug, where-text, timing, month_from, month_to, remark, people slugs, lga slugs, extra sources, extra sentence)
FEST = {
 "afizere": ("Afizere Culture Festival", "afizere-culture-festival", "in Jos North and Jos East LGAs", "1 January", 1, 1,
             "an annual, colourful celebration of the New Year by the Afizere people", ["afizere"], ["jos-north", "jos-east"], [], ""),
 "bitgoemai": ("Bit Goemai", "bit-goemai", "at Shendam town and in Qua'an Pan", "March or April", 3, 4,
               "the annual Goemai festival that ushers in the farming season", ["goemai"], ["shendam", "qua-an-pan"], [], ""),
 "pan": ("Pan Cultural Festival", "pan-cultural-festival", "at Doemak town (Ba'ap), in Qua'an Pan LGA", "March", 3, 3,
         "", ["kofyar"], ["qua-an-pan"], [], " The list gives no description; the Pan are the people Blench's Atlas calls Kofyar, speakers of the Pan cluster, whose ruler is the Long Pan."),
 "pandam": ("Pandam Fishing Festival", "pandam-fishing-festival", "at Pandam Wildlife Park and its lake", "December", 12, 12,
            "", [], [], [], " The list gives no description and does not name the LGA."),
 "irigwe": ("Irigwe New Year Celebration", "irigwe-new-year-celebration", "at Miango and Kwall, in Bassa LGA", "1 January", 1, 1,
            "an annual, colourful celebration of the New Year by the Irigwe people", ["irigwe"], ["bassa"], ["WBASSA"], ""),
 "puskaat": ("Puskaat", "puskaat", "at Mangu town", "April", 4, 4,
             "the annual festival of arts and culture of the Mwaghavul", ["mwaghavul"], ["mangu"], [], ""),
 "mushere": ("Pu'us Kang Mushere", "puus-kang-mushere", "at Ikgwakap (Bokkos town)", "April", 4, 4,
             "an annual celebration of the culture of the Mushere people", ["mushere"], ["bokkos"], [], ""),
 "pusdung": ("Pusdung", "pusdung", "at Pankshin town and in Kanke", "March or April", 3, 4,
             "the annual festival of arts and culture of the Ngas", ["ngas"], ["pankshin", "kanke"], ["WPANK"], " Wikipedia also mentions Pusdung as the annual cultural festival of the Ngas nation."),
 "ronkulere": ("Ron/Kulere Festival", "ron-kulere-festival", "at Bokkos town", "December or January", 12, 1,
               "", ["ron", "kulere"], ["bokkos"], [], " The list gives no description; the Ron and Kulere are the two peoples of Bokkos LGA whose languages Blench's Atlas places there."),
 "bogghom": ("Bogghom Cultural Festival", "bogghom-cultural-festival", "at Kanam town", "December or January", 12, 1,
             "", ["boghom"], ["kanam"], [], " The list gives no description."),
}


def text(k):
    name, slug, where, timing, m1, m2, remark, ppl, lgas, xs, extra = FEST[k]
    t = LEAD.format(n=("the " if name.endswith(("Festival", "Celebration")) else "") + name) + f", held {where} in {timing}" + (f": {remark}." if remark else ".") + extra + MERGE
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for k, (name, slug, where, timing, m1, m2, remark, ppl, lgas, xs, extra) in FEST.items():
    RECORDS.append(dict(key=k, table="cultural_records", evidence="multiple_sources" if xs else "single_reliable_source", level="reported",
                        fields=dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name=name, local_name=name,
                                    slug=slug, timing=f"Annually, {timing}", month_from=m1, month_to=m2, current_status="active", scope_level="ethnic_group" if ppl else "community",
                                    summary=(f"{name}, held {where} in {timing}, is " + (remark if remark else "one of Plateau State's annual cultural festivals") + ".").replace("is an annual", "is an annual"),
                                    description=text(k)),
                        srcs=[("OSS", f"{name}: area, date, remark")] + [(x, f"{name}") for x in xs]))
    for p in ppl:
        RELATIONS.append(dict(frm=k, type="celebrated_by", to=f"@ethnic_groups:{p}", source="OSS", evidence="single_reliable_source", level="reported",
                              notes="Named in the festival's name or remark (state government, 2022)." if p not in ("kofyar",) else "The Pan (Kofyar) of Qua'an Pan (state government, 2022; Blench's Atlas for the Pan–Kofyar identity)."))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="celebrated_in", to=f"@admin_units:lga:plateau/{l}", source="OSS", evidence="single_reliable_source", level="reported",
                              notes=f"Area given by the state government (2022): {where}."))
    if not lgas:
        RELATIONS.append(dict(frm=k, type="celebrated_in", to="@admin_units:state:plateau", source="OSS", evidence="single_reliable_source", level="reported",
                              notes=f"State government (2022): {where}; LGA not stated."))
# Nzem Berom (existing): LGAs from the official list.
for l in ("jos-north", "jos-south", "barkin-ladi", "riyom"):
    RELATIONS.append(dict(frm="@cultural_records:nzem-berom", type="celebrated_in", to=f"@admin_units:lga:plateau/{l}", source="OSS", evidence="single_reliable_source", level="reported",
                          notes="State government (2022): Nzem Berom, Jos North/Jos South/Barkin Ladi/Riyom, April — annual celebration of the start of the rainy season."))
RELATIONS.append(dict(frm="@polities:long-pan", type="associated_with", to="pan", role="ruler of the Pan Chiefdom", source="OSS", evidence="single_reliable_source", level="reported",
                      notes="The Pan Cultural Festival is held at Doemak in the Pan Chiefdom's LGA; the Long Pan's role in it is not described."))
GAPS = [
    ("Plateau: Taroh Cultural Day and Ilum Otarok", "The state government lists 'Taroh Cultural Day' (Langtang North/South, March/April); the governor's office called the Tarok's annual cultural festival 'Ilum Otarok' (May 2025). They are very probably the same event, but no source says so; only Ilum Otarok is recorded."),
    ("Plateau: Resettlement Day, Thaar and Zarachi festivals", "Listed by the state government (Langtang South; Wase town; Kwall and Miango) without description or people; not recorded."),
    ("Plateau: festival rites and histories", "The official list gives only place, month and a one-line remark; the rites, history and current practice of each festival need ethnographic or local sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Plateau annual cultural festivals from the state government's list (2022): 10 festivals; Nzem Berom LGAs.")


def report():
    L_ = ["# Research batch 049 — Plateau: annual cultural festivals (state government list)", "",
          f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
          "**Source:** Plateau State Government, *Plateau State is open for business* (2022), section 1.4. It is Tier 1, and the same booklet was used for the LGA headquarters.", "",
          "## What it adds", "",
          f"- **{len(RECORDS)} festivals:** " + ", ".join(v[0] for v in FEST.values()) + ".",
          "  - Each is linked to its people where the list names them, and to its LGA.",
          "  - Each text says only what the list says: the place, the month and the one-line remark.",
          "- **Nzem Berom** (existing) is linked to the four LGAs the list gives: Jos North, Jos South, Barkin Ladi and Riyom.",
          "- **Not recorded:**",
          "  - **Taroh Cultural Day:** very probably the recorded Ilum Otarok, but not proven.",
          "  - **Resettlement Day, Thaar and Zarachi:** no people or description given.",
          "  These are listed as gaps.",
          "- All records are short, so they are noindex and the sitemap is unchanged.", "",
          "| Festival | Where | When | People |", "|---|---|---|---|"]
    for v in FEST.values():
        L_.append(f"| {v[0]} | {v[2]} | {v[3]} | {', '.join(v[7]) or '—'} |")
    L_ += ["", "## Texts", ""] + [f"**{FEST[k][0]}.** {text(k)}\n" for k in FEST] + ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L_)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_049_plateau_festivals.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_049_plateau_festivals_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} sources={len(SOURCES)}")
