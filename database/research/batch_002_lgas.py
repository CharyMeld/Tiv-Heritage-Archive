"""
Research batch 002 — the Local Government Areas of the 36 states and the six area
councils of the FCT (researched 2026-09-24).

Inputs (prepared by the comparison scripts in the research scratch folder):
  lga_decided.json — each LGA grouped across three lists:
    CON  : 1999 Constitution, First Schedule Part I (government-published text; this
           transcription has known faults — missing commas, misspellings, 5 omissions)
    STAT : Statoids LGA table (derived from the Constitution's list, so not independent
           for names; used to recover names the transcription garbles or omits)
    WIKI : Wikipedia "Local government areas of Nigeria" (independent spelling check)

Rules:
  * an LGA is inserted only if at least two lists have it; the reconciled total is
    768 LGAs — the Constitution's own figure (s. 3(6)) — plus 6 area councils;
  * evidence: in the Constitution text -> 'verified' (primary legal text);
    otherwise (STAT + WIKI only) -> 'multiple_sources';
  * name: the spelling shared by at least two lists (Wikipedia's form preferred when it
    is one of them); where all three differ, Wikipedia's, with the others as variants;
  * Wikipedia's different spelling is kept as a searchable spelling variant;
  * names found in only one list are not inserted — they are reported as gaps.
"""
import json, re, sys, collections

SRC = sys.argv[1] if len(sys.argv) > 1 else "."
ACCESSED = "2026-09-24"
d = json.load(open(f"{SRC}/lga_decided.json"))
raw = json.load(open(f"{SRC}/lga_raw.json"))

SOURCES = {
    "CON": dict(source_type="government_publication", title="Constitution of the Federal Republic of Nigeria 1999",
                organisation="Federal Republic of Nigeria", url="https://nigeriarights.gov.ng/files/constitution.pdf",
                publication_date="1999", verification_status="verified",
                notes="Primary legal text (reused from batch 001)."),
    "STAT": dict(source_type="website", title="Local Government Areas of Nigeria", author="Gwillim Law", organisation="Statoids",
                 url="https://www.statoids.com/yng.html", verification_status="needs_corroboration",
                 notes="Table of the 774 LGAs/area councils named in the 1999 Constitution (plus a 'Disputed Areas' row, not an LGA), with 2006 census population, area and headquarters. Its names follow the Constitution's list, including some of its misprints."),
    "WIKI": dict(source_type="encyclopedia", title="Local government areas of Nigeria", organisation="Wikipedia",
                 url="https://en.wikipedia.org/wiki/Local_government_areas_of_Nigeria", verification_status="needs_corroboration",
                 notes="Used as an independent spelling check. Its list includes later renamings and duplicates (e.g. Bodinga twice) and misses some LGAs (e.g. Kebbe, Shagari, Yabo)."),
}

OWNER_NAMES = {("Oyo", "Ogbmosho South"): "Ogbomosho South", ("Bayelsa", "Yenegoa"): "Yenagoa"}
STATE_SLUG = lambda st: st.lower().replace(" ", "-")


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower().replace("/", " ")).strip("-")


def clean(s):
    """Capitalise words the transcription left in lower case ('kaduna South' -> 'Kaduna South')."""
    return re.sub(r"(^|[\s\-/(])([a-z])", lambda m: m.group(1) + m.group(2).upper(), s.strip())


def letters(s):
    return re.sub(r"[^a-z]", "", s.lower())


units, names, gaps_one = [], [], []
per_state = collections.Counter()
for st, rows in d["result"].items():
    is_fct = st == "FCT"
    parent = "@admin_units:federal_capital_territory:federal-capital-territory" if is_fct else f"@admin_units:state:{STATE_SLUG(st)}"
    seen = set()
    for r in rows:
        sp = r["spellings"]
        if len(r["sources"]) < 2:
            gaps_one.append((st, next(iter(sp.values())), next(iter(sp))))
            continue
        name = clean(r["name"] or sp.get("WIKI") or sp.get("STAT") or sp["CON"])
        # Owner correction (2026-09-24): correct forms displayed; printed spellings kept as variants
        # (applied to the live data by database/research/fix_002_lga_names.php).
        name = OWNER_NAMES.get((st, name), name)
        slug = slugify(name)
        assert (slug not in seen), (st, slug)
        seen.add(slug)
        key = f"{st}|{slug}"
        in_con = "CON" in sp
        srcs = []
        if in_con: srcs.append(("CON", f"Listed in the First Schedule, Part {'II' if is_fct else 'I'} (printed as '{sp['CON']}')"))
        if "STAT" in sp: srcs.append(("STAT", f"Listed as '{sp['STAT']}'"))
        if "WIKI" in sp: srcs.append(("WIKI", f"Listed as '{sp['WIKI']}'"))
        state_name = "the Federal Capital Territory" if is_fct else f"{st} State"
        if is_fct:
            hq_con = {"Abaji": "Abaji", "Abuja Municipal": "Garki", "Bwari": "Bwari", "Gwagwalada": "Gwagwalada", "Kuje": "Kuje", "Kwali": "Kwali"}
            con_name = sp.get("CON")
            hq = hq_con.get(con_name)
            stat_hq = next((x["hq"] for x in raw["stat"]["FCT"] if letters(x["name"]).startswith(letters(con_name or name)[:6])), None)
            summary = f"{name} is one of the six area councils of the Federal Capital Territory, listed in Part II of the First Schedule to the 1999 Constitution"
            summary += f"; its headquarters is {hq}." if hq and stat_hq and letters(hq) == letters(stat_hq) else "."
            if hq and stat_hq and letters(hq) == letters(stat_hq): srcs.append(("STAT", f"Headquarters {stat_hq}"))
            units.append(dict(key=key, unit_type="other", name=name, official_name=name if name.endswith("Area Council") else f"{name} Area Council", slug=slug, status="current",
                              parent=parent, evidence="verified" if in_con else "multiple_sources", summary=summary, srcs=srcs))
        else:
            summary = (f"{name} is a local government area of {state_name}, listed in the First Schedule to the 1999 Constitution."
                       if in_con else f"{name} is a local government area of {state_name}.")
            units.append(dict(key=key, unit_type="lga", name=name, slug=slug, status="current", parent=parent,
                              evidence="verified" if in_con else "multiple_sources", summary=summary, srcs=srcs))
        per_state[st] += 1
        # Wikipedia's (independent) spelling as a searchable variant when it differs by letters.
        for s_key in ("WIKI", "CON"):
            v = sp.get(s_key)
            if v and letters(v) != letters(name) and (s_key == "WIKI" or r["name"] is None):
                names.append(dict(unit=key, name=clean(v), name_type="spelling_variant", srcs=[s_key],
                                  usage_notes="Spelling in " + ("Wikipedia's list" if s_key == "WIKI" else "the transcription of the Constitution consulted")))

GAPS = [
    ("Spelling of Girei LGA (Adamawa)", "The three lists differ: Constitution transcription 'Gireri', Statoids 'Girie', Wikipedia 'Girei'. Recorded as 'Girei' with the others as variants; needs an official Adamawa or INEC source."),
    ("Five LGAs missing from the Constitution transcription consulted", "Gamawa (Bauchi), Ilorin South (Kwara), Kaura (Kaduna), Ibadan North-East (Oyo) and Ilejemeje (Ekiti) are absent from, or garbled in, the government-published transcription; they are recorded from Statoids and Wikipedia and bring the total to the Constitution's 768. Needs a certified copy of the First Schedule."),
    ("'Ibadan Central' in the Constitution transcription", "Listed for Oyo in the transcription but in neither other list (which have Ibadan North-East). Not recorded; not assumed to be the same LGA."),
    ("'Karawa' (Yobe) and 'Ilemeji' (Ekiti) in the Constitution transcription", "Not in the other lists ('Ilemeji' is probably the transcription's form of Ilejemeje). Not recorded as separate LGAs."),
    ("LGAs created or renamed after 1999", "Wikipedia lists names not in the Constitution (e.g. Yewa North/South, Gbonyin, Ghari, Isara, Ilishan-Remo, Edda, Afikpo, Okobo, Ile-Oluji). State-created LGAs/LCDAs and renamings need official state sources."),
    ("LGA headquarters", "Given by Statoids only (single source); not recorded, except the FCT area councils, whose headquarters are in Part II of the First Schedule."),
    ("LGA populations and areas", "Statoids gives 2006 census figures (single, secondary source); not recorded. Needs the National Population Commission's published figures."),
]


def build_report():
    L = ["# Research batch 002 — Local Government Areas and FCT area councils", "",
         f"Researched {ACCESSED}. Imported as *in review* only after approval.", "",
         "## Result", "", f"- **{sum(v for k, v in per_state.items() if k != 'FCT')} LGAs** (the Constitution's figure is 768) and **{per_state['FCT']} FCT area councils**.",
         f"- In the Constitution text (evidence *verified*): {sum(1 for u in units if u['evidence'] == 'verified')}; from Statoids + Wikipedia only (*multiple sources*): {sum(1 for u in units if u['evidence'] == 'multiple_sources')}.",
         f"- Spelling variants kept for search: {len(names)}.", "",
         "## Sources", ""]
    for k, s in SOURCES.items():
        L.append(f"- **{k}** — {s['title']} ({s.get('organisation')}). {s['url']}. Accessed {ACCESSED}. {s['notes']}")
    L += ["", "## How the lists were reconciled", "",
          "- The government-published transcription of the First Schedule has faults: missing commas that merge two LGAs (e.g. 'Kaugama Kazaure', 'Lagelu Ogbomosho North', 'Nguru Potiskum', 'Takum. Ussa', 'Talata Mafara. Tsafe'), a wrong split ('Kaura, Namoda' = Kaura Namoda), garbled entries ('Akoko South Akure East' = Akoko South East) and misprints ('Tqngaza', 'Cassol', 'Takali'). These were repaired for matching only and are listed in the variants/notes.",
          "- Statoids' extra row 'Disputed Areas' (Taraba) is not an LGA and was dropped.",
          "- No two names were assumed to be the same LGA unless their spellings match closely; unmatched names are reported below.", "",
          "## LGAs per state", "", "| State | LGAs | Not in the Constitution text |", "|---|---|---|"]
    for st in sorted(per_state):
        miss = [u["name"] for u in units if u["key"].startswith(st + "|") and u["evidence"] != "verified"]
        L.append(f"| {st} | {per_state[st]} | {', '.join(miss) or '—'} |")
    L += ["", "## Spelling differences kept as variants", ""]
    for n in names:
        L.append(f"- {n['unit'].split('|')[0]}: **{next(u['name'] for u in units if u['key'] == n['unit'])}** — also '{n['name']}' ({n['usage_notes']})")
    L += ["", "## Names in only one list (not inserted)", ""]
    for st, nm, src in gaps_one:
        L.append(f"- {st}: '{nm}' ({src})")
    L += ["", "## Research gaps recorded", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    L += ["", "## Google / AdSense note", "", "LGA pages carry one sentence each, so they stay **noindex** and out of the sitemap (like the state pages) until later batches add sourced content. They mainly make the state pages and the AI more complete (\"What are the LGAs of Benue State?\").", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[2] if len(sys.argv) > 2 else SRC
    data = dict(sources=SOURCES, units=units, places=[], changes=[], names=names, gaps=GAPS,
                scope="The 768 LGAs of the 36 states and the six FCT area councils (1999 Constitution, First Schedule), names reconciled across three lists.")
    json.dump(data, open(f"{out}/batch_002_lgas.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_002_lgas_REVIEW.md", "w").write(build_report())
    print(f"units={len(units)} (LGAs={sum(1 for u in units if u['unit_type']=='lga')}, area councils={sum(1 for u in units if u['unit_type']=='other')}) "
          f"names={len(names)} one-list names={len(gaps_one)} gaps={len(GAPS)}")
