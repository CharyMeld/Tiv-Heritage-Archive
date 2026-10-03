"""
Research batch 001 — Nigeria, the 36 states and the FCT: capitals, creation dates,
predecessor units and geopolitical zones (researched 2026-09-24).

Single source of truth for the batch: build_report() writes the review report and
build_json() the data that bin/import-research-batch.php inserts (review_status
'in_review'; nothing is published by the import).

Rules applied (NIGERIA_EXPANSION_STAGE_1_AUDIT.md §11, §19–21):
  * every fact carries the sources that state it;
  * a fact with two or more agreeing sources -> 'multiple_sources' (the 1999
    Constitution alone -> 'verified', being the primary legal text);
  * a fact with one source -> 'single_reliable_source';
  * contradictions and uncorroborated claims are NOT inserted — they become research gaps.
Summaries are written from the sourced facts only, in our own words.
"""
import json, sys

ACCESSED = "2026-09-24"

SOURCES = {
    "CON": dict(source_type="government_publication", title="Constitution of the Federal Republic of Nigeria 1999",
                author=None, organisation="Federal Republic of Nigeria",
                publication_details="Sections 2, 3, 297–299 and First Schedule (Parts I–II); text as published by the Federal Government",
                publication_date="1999", url="https://nigeriarights.gov.ng/files/constitution.pdf",
                notes="Primary legal text. Section 3: the 36 states and 768 LGAs; First Schedule Part I, third column: capital city of each state; section 298: the FCT, Abuja is the capital of the Federation.",
                verification_status="verified"),
    "STAT": dict(source_type="website", title="States of Nigeria", author="Gwillim Law", organisation="Statoids",
                 publication_details="Reference tables of administrative divisions, with change history",
                 url="https://www.statoids.com/ung.html",
                 notes="Capitals, and dated history of state creations (1967-05-27, 1976-02-03, 1987-09-23, 1991-08-27, 1996-10-01).",
                 verification_status="needs_corroboration"),
    "WLIST": dict(source_type="encyclopedia", title="List of Nigerian states by date of statehood", organisation="Wikipedia",
                  url="https://en.wikipedia.org/wiki/List_of_Nigerian_states_by_date_of_statehood",
                  notes="Used only to corroborate. Contains errors noted in batch 001 (e.g. gives Bendel State as the predecessor of Rivers State in 1967, although Bendel State was formed in 1976).",
                  verification_status="needs_corroboration"),
    "WSTATES": dict(source_type="encyclopedia", title="States of Nigeria", organisation="Wikipedia",
                    url="https://en.wikipedia.org/wiki/States_of_Nigeria",
                    notes="Used only to corroborate geopolitical zone membership.", verification_status="needs_corroboration"),
    "EUAA": dict(source_type="government_publication", title="Country Guidance: Nigeria — General remarks",
                 organisation="European Union Agency for Asylum (EUAA)", publication_date="February 2019",
                 url="https://www.euaa.europa.eu/country-guidance-nigeria/general-remarks-0",
                 notes="Lists the states of each of the six geopolitical zones.", verification_status="needs_corroboration"),
    "KANO": dict(source_type="official_website", title="History – Kano State Government", organisation="Kano State Government",
                 url="https://kanostate.gov.ng/history/",
                 notes="States that Kano State was created on 27 May 1967 out of the former Northern Region, and that Jigawa State was formed from part of it in 1991.",
                 verification_status="needs_corroboration"),
}
# Nigerian Investment Promotion Commission state pages (taken offline; cited from the Internet Archive).
NIPC = {
    "adamawa": "20240508", "akwa-ibom": "20240711", "bauchi": "20241204", "cross-river": "20250507", "ebonyi": "20240527",
    "enugu": "20250414", "gombe": "20250209", "kaduna": "20250426", "lagos": "20250426", "ogun": "20250429",
    "ondo": "20240615", "osun": "20250426", "oyo": "20250121", "plateau": "20240717", "taraba": "20240706",
}
for slug, ts in NIPC.items():
    name = slug.replace("-", " ").title()
    SOURCES["NIPC_" + slug] = dict(
        source_type="official_website", title=f"{name} State", organisation="Nigerian Investment Promotion Commission (NIPC)",
        url=f"https://www.nipc.gov.ng/nigeria-states/{slug}-state/",
        archive_reference=f"Internet Archive snapshot {ts}: http://web.archive.org/web/{ts}/https://www.nipc.gov.ng/nigeria-states/{slug}-state/",
        notes="Federal government agency page on the state (no longer online; consulted via the Internet Archive).",
        verification_status="needs_corroboration")

ZONES_SRC = ["WSTATES", "EUAA"]
ZONES = {
    "North Central": ["Benue", "Kogi", "Kwara", "Nasarawa", "Niger", "Plateau", "Federal Capital Territory"],
    "North East": ["Adamawa", "Bauchi", "Borno", "Gombe", "Taraba", "Yobe"],
    "North West": ["Jigawa", "Kaduna", "Kano", "Katsina", "Kebbi", "Sokoto", "Zamfara"],
    "South East": ["Abia", "Anambra", "Ebonyi", "Enugu", "Imo"],
    "South South": ["Akwa Ibom", "Bayelsa", "Cross River", "Delta", "Edo", "Rivers"],
    "South West": ["Ekiti", "Lagos", "Ogun", "Ondo", "Osun", "Oyo"],
}
ZONE_OF = {s: z for z, ss in ZONES.items() for s in ss}

# Historical units needed for the lineage. (name, type, created, ended, sources for dates)
HISTORICAL = {
    "Northern Region":      dict(unit_type="region", status="historical", ended="1967-05-27", ended_src=["STAT", "KANO"]),
    "Eastern Region":       dict(unit_type="region", status="historical", ended="1967-05-27", ended_src=["STAT", "NIPC_cross-river"]),
    "Western Region":       dict(unit_type="region", status="historical", ended="1967-05-27", ended_src=["STAT"]),
    "Mid-Western Region":   dict(unit_type="region", status="historical", ended="1967-05-27", ended_src=["STAT"]),
    "North-Western State":  dict(unit_type="state", status="abolished", created="1967-05-27", created_src=["STAT", "WLIST"], ended="1976-02-03", ended_src=["STAT", "WLIST"]),
    "North-Eastern State":  dict(unit_type="state", status="abolished", created="1967-05-27", created_src=["STAT", "WLIST"], ended="1976-02-03", ended_src=["STAT", "WLIST", "NIPC_bauchi"]),
    "Benue-Plateau State":  dict(unit_type="state", status="abolished", created="1967-05-27", created_src=["STAT", "WLIST"], ended="1976-02-03", ended_src=["STAT", "WLIST", "NIPC_plateau"]),
    "Western State":        dict(unit_type="state", status="abolished", created="1967-05-27", created_src=["STAT", "WLIST"], ended="1976-02-03", ended_src=["STAT", "WLIST", "NIPC_ondo", "NIPC_oyo"]),
    "East-Central State":   dict(unit_type="state", status="abolished", created="1967-05-27", created_src=["STAT", "WLIST"], ended="1976-02-03", ended_src=["STAT", "WLIST"]),
    "Bendel State":         dict(unit_type="state", status="abolished", created="1967-05-27", created_src=["STAT"], ended="1991-08-27", ended_src=["STAT", "WLIST"],
                                 old_names=[("Mid-Western State", 1967, 1976, ["STAT"])]),
    "Gongola State":        dict(unit_type="state", status="abolished", created="1976-02-03", created_src=["STAT"], ended="1991-08-27", ended_src=["STAT", "WLIST", "NIPC_adamawa", "NIPC_taraba"]),
}

# Current states: capital (Constitution First Schedule, as spelled there -> display name),
# created date + its sources, predecessors [(unit, sources)], optional renames / notes.
STATES = {
    "Abia":        dict(cap="Umuahia", created="1991-08-27", created_src=["STAT", "WLIST"], from_=[("Imo", ["STAT", "WLIST"])]),
    "Adamawa":     dict(cap="Yola", created="1991-08-27", created_src=["STAT", "WLIST", "NIPC_adamawa"], from_=[("Gongola State", ["STAT", "WLIST", "NIPC_adamawa"])]),
    "Akwa Ibom":   dict(cap="Uyo", created="1987-09-23", created_src=["STAT", "WLIST", "NIPC_akwa-ibom"], from_=[("Cross River", ["STAT", "WLIST", "NIPC_akwa-ibom"])]),
    "Anambra":     dict(cap="Awka", created="1976-02-03", created_src=["STAT", "WLIST"], from_=[("East-Central State", ["STAT", "WLIST"])]),
    "Bauchi":      dict(cap="Bauchi", created="1976-02-03", created_src=["STAT", "WLIST", "NIPC_bauchi"], from_=[("North-Eastern State", ["STAT", "WLIST", "NIPC_bauchi"])]),
    "Bayelsa":     dict(cap="Yenagoa", created="1996-10-01", created_src=["STAT", "WLIST"], from_=[("Rivers", ["STAT", "WLIST"])]),
    "Benue":       dict(cap="Makurdi", created="1976-02-03", created_src=["STAT", "WLIST"], from_=[("Benue-Plateau State", ["STAT", "WLIST"])]),
    "Borno":       dict(cap="Maiduguri", created="1976-02-03", created_src=["STAT", "WLIST"], from_=[("North-Eastern State", ["STAT", "WLIST"])]),
    "Cross River": dict(cap="Calabar", created="1967-05-27", created_src=["STAT", "WLIST", "NIPC_cross-river"],
                        from_=[("Eastern Region", ["STAT", "NIPC_cross-river"])],
                        old_names=[("South-Eastern State", 1967, 1976, ["STAT", "NIPC_cross-river"])],
                        renamed=("1976-02-03", "South-Eastern State", "Cross River State", ["STAT", "NIPC_cross-river"])),
    "Delta":       dict(cap="Asaba", created="1991-08-27", created_src=["STAT", "WLIST"], from_=[("Bendel State", ["STAT", "WLIST"])]),
    "Ebonyi":      dict(cap="Abakaliki", created="1996-10-01", created_src=["STAT", "WLIST", "NIPC_ebonyi"],
                        from_=[("Abia", ["STAT", "WLIST", "NIPC_ebonyi"]), ("Enugu", ["STAT", "WLIST", "NIPC_ebonyi"])]),
    "Edo":         dict(cap="Benin City", created="1991-08-27", created_src=["STAT", "WLIST"], from_=[("Bendel State", ["STAT", "WLIST"])]),
    "Ekiti":       dict(cap="Ado Ekiti", created="1996-10-01", created_src=["STAT", "WLIST"], from_=[("Ondo", ["STAT", "WLIST", "NIPC_ondo"])]),
    "Enugu":       dict(cap="Enugu", created="1991-08-27", created_src=["STAT", "WLIST", "NIPC_enugu"], from_=[("Anambra", ["STAT", "WLIST"])]),
    "Gombe":       dict(cap="Gombe", created="1996-10-01", created_src=["STAT", "WLIST", "NIPC_gombe"], from_=[("Bauchi", ["STAT", "WLIST", "NIPC_gombe"])]),
    "Imo":         dict(cap="Owerri", created="1976-02-03", created_src=["STAT", "WLIST"], from_=[("East-Central State", ["STAT", "WLIST"])]),
    "Jigawa":      dict(cap="Dutse", created="1991-08-27", created_src=["STAT", "WLIST"], from_=[("Kano", ["STAT", "WLIST", "KANO"])]),
    "Kaduna":      dict(cap="Kaduna", created="1967-05-27", created_src=["STAT", "WLIST", "NIPC_kaduna"],
                        from_=[("Northern Region", ["STAT", "NIPC_kaduna"])],
                        old_names=[("North-Central State", 1967, 1976, ["STAT", "WLIST", "NIPC_kaduna"])],
                        renamed=("1976", "North-Central State", "Kaduna State", ["NIPC_kaduna"])),
    "Kano":        dict(cap="Kano", created="1967-05-27", created_src=["STAT", "WLIST", "KANO"], from_=[("Northern Region", ["STAT", "KANO"])]),
    "Katsina":     dict(cap="Katsina", created="1987-09-23", created_src=["STAT", "WLIST", "NIPC_kaduna"], from_=[("Kaduna", ["STAT", "WLIST", "NIPC_kaduna"])]),
    "Kebbi":       dict(cap="Birnin Kebbi", created="1991-08-27", created_src=["STAT", "WLIST"], from_=[("Sokoto", ["STAT", "WLIST"])]),
    "Kogi":        dict(cap="Lokoja", created="1991-08-27", created_src=["STAT", "WLIST"], from_=[("Benue", ["STAT", "WLIST"]), ("Kwara", ["STAT", "WLIST"])]),
    "Kwara":       dict(cap="Ilorin", created="1967-05-27", created_src=["STAT", "WLIST"], from_=[("Northern Region", ["STAT"])]),
    "Lagos":       dict(cap="Ikeja", created="1967-05-27", created_src=["STAT", "WLIST", "NIPC_lagos"], from_=[("Western Region", ["STAT"])],
                        capital_change=("1976-02-03", "Lagos", "Ikeja", ["STAT"])),
    "Nasarawa":    dict(cap="Lafia", created="1996-10-01", created_src=["STAT", "WLIST"], from_=[("Plateau", ["STAT", "WLIST", "NIPC_plateau"])],
                        alt_names=[("Nassarawa", "spelling_variant", ["STAT"])]),
    "Niger":       dict(cap="Minna", created="1976-02-03", created_src=["STAT", "WLIST"], from_=[("North-Western State", ["STAT", "WLIST"])]),
    "Ogun":        dict(cap="Abeokuta", created="1976-02-03", created_src=["STAT", "WLIST", "NIPC_ogun"], from_=[("Western State", ["STAT", "WLIST"])]),
    "Ondo":        dict(cap="Akure", created="1976-02-03", created_src=["STAT", "WLIST", "NIPC_ondo"], from_=[("Western State", ["STAT", "WLIST", "NIPC_ondo"])]),
    "Osun":        dict(cap="Oshogbo", created="1991-08-27", created_src=["STAT", "WLIST", "NIPC_osun"], from_=[("Oyo", ["STAT", "WLIST"])]),
    "Oyo":         dict(cap="Ibadan", created="1976-02-03", created_src=["STAT", "WLIST", "NIPC_oyo"], from_=[("Western State", ["STAT", "WLIST", "NIPC_oyo"])]),
    "Plateau":     dict(cap="Jos", created="1976-02-03", created_src=["STAT", "WLIST", "NIPC_plateau"], from_=[("Benue-Plateau State", ["STAT", "WLIST", "NIPC_plateau"])]),
    "Rivers":      dict(cap="Port Harcourt", created="1967-05-27", created_src=["STAT", "WLIST"], from_=[("Eastern Region", ["STAT"])]),
    "Sokoto":      dict(cap="Sokoto", created="1976-02-03", created_src=["STAT", "WLIST"], from_=[("North-Western State", ["STAT", "WLIST"])],
                        created_note="Statoids treats the 1976 change as Niger State splitting from Sokoto (North-Western State continuing as Sokoto); the Wikipedia list dates Sokoto State from 3 February 1976. The link to North-Western State is supported by both."),
    "Taraba":      dict(cap="Jalingo", created="1991-08-27", created_src=["STAT", "NIPC_taraba"], from_=[("Gongola State", ["STAT", "WLIST", "NIPC_taraba"])]),
    "Yobe":        dict(cap="Damaturu", created="1991-08-27", created_src=["STAT", "WLIST"], from_=[("Borno", ["STAT", "WLIST"])]),
    "Zamfara":     dict(cap="Gusau", created="1996-10-01", created_src=["STAT", "WLIST"], from_=[("Sokoto", ["STAT", "WLIST"])]),
}
# Spellings used by the Constitution / Statoids where the display name differs.
CAPITAL_ALT = {
    "Oshogbo": [("Osogbo", "spelling_variant", ["CON"], "Spelling used for the Osogbo Local Government Area in the Constitution's First Schedule.")],
    "Port Harcourt": [("Port-Harcourt", "spelling_variant", ["CON"], "Spelling used in the Constitution's First Schedule.")],
    "Ado Ekiti": [("Ado-Ekiti", "spelling_variant", ["STAT"], None)],
}

# Lineage of the historical units themselves (all from Statoids' dated change history).
HISTORICAL_LINKS = [
    ("North-Western State", "Northern Region", "1967-05-27"), ("North-Eastern State", "Northern Region", "1967-05-27"),
    ("Benue-Plateau State", "Northern Region", "1967-05-27"), ("Western State", "Western Region", "1967-05-27"),
    ("East-Central State", "Eastern Region", "1967-05-27"), ("Bendel State", "Mid-Western Region", "1967-05-27"),
    ("Gongola State", "North-Eastern State", "1976-02-03"),
]

FCT = dict(created="1976-02-03", created_src=["STAT", "WLIST"])

GAPS = [
    ("Territory from which the FCT was formed (1976)",
     "Sources disagree: Statoids says 'formed from parts of Niger and Plateau states'; the Wikipedia list gives 'Benue-Plateau, North-Central, and North-Western States'. Not recorded; needs the 1976 decree text or an official FCT source."),
    ("Reported 1987 renaming of the FCT to 'Abuja Capital Territory'",
     "Stated only by Statoids; no second source found. Not recorded."),
    ("Kwara State's original 1967 name ('West Central State')",
     "Given as the preceding entity by the Wikipedia list only; Statoids names it Kwara from 1967. Not recorded."),
    ("Predecessor of Rivers State (1967)",
     "Eastern Region per Statoids (recorded as single source). The Wikipedia list gives 'Bendel State', which cannot be right (Bendel State dates from 1976). The Rivers State Government site could not be consulted (access refused)."),
    ("Predecessors of Lagos State (1967)",
     "Statoids: Western Region; Wikipedia list: Federal Territory of Lagos. Recorded only the Statoids claim as single source; the Federal Territory of Lagos needs a primary source (the 1967 states-creation decree)."),
    ("Taraba State creation date",
     "Recorded as 27 August 1991 (Statoids; NIPC). The Wikipedia list gives 1 August 1991; treated as an error in that list."),
    ("Legal basis of the six geopolitical zones",
     "The zones are not defined in the 1999 Constitution (its text was searched). Membership is recorded from two agreeing sources; when and how the zones were adopted is not yet documented."),
    ("ISO 3166-2 codes for the states", "Not recorded: only one source consulted."),
    ("Creation dates of the Northern, Eastern, Western and Mid-Western Regions", "Outside batch 001; only their 1967 end is recorded."),
    ("Legal instruments of each state-creation exercise (1967, 1976, 1987, 1991, 1996)", "Decree numbers not recorded; primary texts not yet consulted."),
    ("Kaduna State renaming date", "'By 1976' per NIPC only; recorded with year precision as single source."),
    ("LGAs of each state and the six FCT area councils", "Listed in the Constitution's First Schedule; to be recorded in batch 002."),
]


def evidence(srcs):
    if srcs == ["CON"]:
        return "verified"
    return "multiple_sources" if len(set(srcs)) >= 2 else "single_reliable_source"


def state_summary(name, d):
    zone = ZONE_OF[name]
    created = {"1967-05-27": "27 May 1967", "1976-02-03": "3 February 1976", "1987-09-23": "23 September 1987",
               "1991-08-27": "27 August 1991", "1996-10-01": "1 October 1996"}[d["created"]]
    preds = [p for p, s in d["from_"] if len(set(s)) >= 2]
    def pname(p):
        return p if p.endswith(("State", "Region")) else p + " State"
    pred_txt = (" from " + " and ".join(pname(p) for p in preds)) if preds else ""
    first = f"{name} State is one of the 36 states of Nigeria; its capital is {d['cap']}."
    if "renamed" in d:
        old = d["renamed"][1]
        second = f" It was created on {created} as {old} and later took its present name."
    else:
        second = f" It was created on {created}{pred_txt}."
    return first + second + f" It is grouped in the {zone} geopolitical zone."


def build():
    units, places, changes, names, links = [], [], [], [], []

    units.append(dict(key="Nigeria", unit_type="country", name="Nigeria", official_name="Federal Republic of Nigeria",
                      slug="nigeria", status="current", parent=None, evidence="verified",
                      summary="The Federal Republic of Nigeria is a federation of 36 states and the Federal Capital Territory, Abuja, which is the capital of the Federation.",
                      srcs=[("CON", "Federation of 36 states and the FCT (ss. 2–3); FCT is the capital of the Federation (s. 298)")]))

    for hname, h in HISTORICAL.items():
        srcs = [(s, f"Created {h['created']}") for s in h.get("created_src", [])] + [(s, f"Ended {h['ended']}") for s in h["ended_src"]]
        units.append(dict(key=hname, unit_type=h["unit_type"], name=hname, slug=hname.lower().replace(" ", "-"),
                          status=h["status"], parent="Nigeria", created_on=h.get("created"),
                          created_precision="exact" if h.get("created") else "unknown",
                          ended_on=h["ended"], ended_precision="exact",
                          evidence=evidence([s for s, _ in srcs]),
                          summary=None, srcs=srcs))
        for old, y1, y2, s in h.get("old_names", []):
            names.append(dict(unit=hname, name=old, name_type="historical", valid_from_year=y1, valid_to_year=y2, srcs=s))

    for name, d in STATES.items():
        key = name
        slug = name.lower().replace(" ", "-")
        cap_key = d["cap"] + " (capital)"
        srcs = [("CON", "Listed among the 36 states (s. 3); capital city in First Schedule Part I")]
        srcs += [(s, f"Capital {d['cap']}") for s in ["STAT"]]
        srcs += [(s, "Creation date") for s in d["created_src"]]
        srcs += [(s, "Geopolitical zone") for s in ZONES_SRC]
        units.append(dict(key=key, unit_type="state", name=f"{name} State", slug=slug, status="current", parent="Nigeria",
                          geopolitical_zone=ZONE_OF[name], capital=cap_key, created_on=d["created"], created_precision="exact",
                          evidence="multiple_sources", summary=state_summary(name, d), srcs=srcs, notes=d.get("created_note")))
        places.append(dict(key=cap_key, name=d["cap"], slug=d["cap"].lower().replace(" ", "-"),
                           unit=key, evidence="multiple_sources",
                           summary=f"{d['cap']} is the capital of {name} State, as listed in the First Schedule to the 1999 Constitution.",
                           srcs=[("CON", f"Capital city of {name} State (First Schedule Part I)"), ("STAT", f"Capital of {name}")]))
        for alt, t, s, note in CAPITAL_ALT.get(d["cap"], []):
            names.append(dict(place=cap_key, name=alt, name_type=t, srcs=s, usage_notes=note))
        for pred, s in d["from_"]:
            changes.append(dict(change_type="created_from", from_unit=pred, to_unit=key, effective_date=d["created"] if len(d["created"]) == 10 else None,
                                precision="exact", srcs=s, evidence=evidence(s), notes=d.get("created_note")))
        if "renamed" in d:
            dt, old, new, s = d["renamed"]
            changes.append(dict(change_type="renamed", from_unit=None, to_unit=key, old_value=old, new_value=new,
                                effective_date=dt if len(dt) == 10 else None, effective_text=None if len(dt) == 10 else dt,
                                precision="exact" if len(dt) == 10 else "year", srcs=s, evidence=evidence(s)))
        for old, y1, y2, s in d.get("old_names", []):
            names.append(dict(unit=key, name=old, name_type="historical", valid_from_year=y1, valid_to_year=y2, srcs=s))
        for alt, t, s in d.get("alt_names", []):
            names.append(dict(unit=key, name=alt, name_type=t, srcs=s))
        if "capital_change" in d:
            dt, old, new, s = d["capital_change"]
            changes.append(dict(change_type="capital_change", from_unit=None, to_unit=key, old_value=old, new_value=new,
                                effective_date=dt, precision="exact", srcs=s, evidence=evidence(s)))

    units.append(dict(key="Federal Capital Territory", unit_type="federal_capital_territory", name="Federal Capital Territory",
                      official_name="Federal Capital Territory, Abuja", slug="federal-capital-territory", status="current", parent="Nigeria",
                      geopolitical_zone="North Central", created_on=FCT["created"], created_precision="exact", evidence="multiple_sources",
                      summary="The Federal Capital Territory, Abuja is the capital of the Federation and the seat of the Government of the Federation. It was created on 3 February 1976 and is grouped in the North Central geopolitical zone.",
                      srcs=[("CON", "Capital of the Federation and seat of government (s. 298); boundaries in First Schedule Part II")]
                           + [(s, "Created 3 February 1976") for s in FCT["created_src"]] + [(s, "Geopolitical zone") for s in ZONES_SRC]))
    names.append(dict(unit="Federal Capital Territory", name="FCT", name_type="abbreviation", srcs=["CON"]))

    for unit, pred, dt in HISTORICAL_LINKS:
        changes.append(dict(change_type="created_from", from_unit=pred, to_unit=unit, effective_date=dt, precision="exact",
                            srcs=["STAT"], evidence="single_reliable_source"))
    changes.append(dict(change_type="renamed", from_unit=None, to_unit="Bendel State", old_value="Mid-Western State", new_value="Bendel State",
                        effective_date="1976-02-03", precision="exact", srcs=["STAT"], evidence="single_reliable_source"))
    return dict(sources=SOURCES, units=units, places=places, changes=changes, names=names, gaps=GAPS)


def build_report(data):
    L = ["# Research batch 001 — Nigeria, the 36 states and the FCT", "",
         f"Researched {ACCESSED}. **Nothing below is inserted until approved;** after approval it is imported as *in review* (not published).", "",
         "## Sources consulted", ""]
    for k, s in data["sources"].items():
        L.append(f"- **{k}** — {s['title']} ({s.get('organisation') or s.get('author')}). {s.get('url')}"
                 + (f" — {s['archive_reference']}" if s.get('archive_reference') else "") + f". Accessed {ACCESSED}.")
    L += ["", "## States and the FCT", "", "| State | Capital | Created | Created from | Zone | Evidence |", "|---|---|---|---|---|---|"]
    for name, d in STATES.items():
        preds = "; ".join(f"{p} [{', '.join(s)}]" for p, s in d["from_"])
        extra = ""
        if "renamed" in d: extra = f" (as {d['renamed'][1]}; renamed {d['renamed'][0]} [{', '.join(d['renamed'][3])}])"
        L.append(f"| {name} | {d['cap']} [CON, STAT] | {d['created']} [{', '.join(d['created_src'])}]{extra} | {preds} | {ZONE_OF[name]} [WSTATES, EUAA] | "
                 + ("single source for a predecessor" if any(len(set(s)) < 2 for _, s in d["from_"]) else "multiple sources") + " |")
    L.append(f"| Federal Capital Territory | — (Abuja is the capital of the Federation, s. 298) [CON] | {FCT['created']} [STAT, WLIST] | *gap: sources disagree* | North Central [WSTATES, EUAA] | multiple sources |")
    L += ["", "## Historical units (for lineage)", "", "| Unit | Type | Created | Ended |", "|---|---|---|---|"]
    for n, h in HISTORICAL.items():
        L.append(f"| {n} | {h['unit_type']} | {h.get('created', 'not researched')} [{', '.join(h.get('created_src', []))}] | {h['ended']} [{', '.join(h['ended_src'])}] |")
    L += ["", "## Other records", "", "- **Nigeria** (country): federation of 36 states and the FCT; FCT is the capital of the Federation [CON].",
          "- **Capitals**: 36 place records, each citing the Constitution and Statoids; spelling variants kept as alternative names (Oshogbo/Osogbo, Port Harcourt/Port-Harcourt, Ado Ekiti/Ado-Ekiti).",
          "- **Other names**: South-Eastern State (Cross River, 1967–76), North-Central State (Kaduna, 1967–76), Mid-Western State (Bendel, 1967–76), Nassarawa (spelling variant), FCT (abbreviation).",
          "- **Capital change**: Lagos State capital from Lagos to Ikeja, 3 February 1976 [STAT only — single source].",
          "- **Lineage of historical units** [STAT only — single source]: " + "; ".join(f"{u} from {p} ({d})" for u, p, d in HISTORICAL_LINKS) + "; Mid-Western State renamed Bendel State (1976-02-03).",
          "", "## Not inserted — research gaps", ""]
    for t, dsc in data["gaps"]:
        L.append(f"- **{t}.** {dsc}")
    L += ["", "## Sample summary (every state gets one, written only from multi-sourced facts)", "", "> " + state_summary("Benue", STATES["Benue"]), "",
          "## Google / AdSense note", "",
          "These records are short (well under 300 words each), so every state page stays **noindex** and out of the sitemap until later batches add sourced history. Publishing them only makes the Nigeria menu appear.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    data = build()
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(data, open(f"{out}/batch_001_states.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_001_states_REVIEW.md", "w").write(build_report(data))
    print(f"units={len(data['units'])} places={len(data['places'])} changes={len(data['changes'])} names={len(data['names'])} "
          f"sources={len(data['sources'])} gaps={len(data['gaps'])}")
