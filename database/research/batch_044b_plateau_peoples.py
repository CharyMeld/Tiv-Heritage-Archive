"""
Research batch 044b — Plateau (Phase 3): the peoples of Plateau State and the LGAs where they live,
as far as the sources allow. Researched 2026-09-30. Pattern: Taraba batch 038b.

Sources:
  * Wikipedia, "Plateau State": 'some of the indigenous ethnic groups' (31 names), the settler groups
    named there, and a table of languages by CURRENT LGA citing Ethnologue, 22nd edition.
  * Blench's Atlas (batch 044) for the languages and the peoples' own names (fields 1.C, 2.C).
  * No official list found: the state government's 'People' page (plateaustate.gov.ng/people) has no
    list of ethnic groups; the Internet Archive was offline, so archived state and NIPC pages could
    not be checked (gap). People records therefore rest on Wikipedia plus the Atlas, as for Taraba.

Rules (as 038b):
  * A people record only for a group Wikipedia names AND whose language the Atlas places in Plateau
    (batch 044 records). Mapped by the Atlas's own names: Afizere -> Izere (1.C Afizere), Amo -> Map
    (Glottolog 'Amo'), Anaguta -> Iguta (1.C), Bache -> Che (1.C), Buji -> Boze of the Jere cluster
    (2.A Buji), Montol -> Tel (2.A), Kofyar -> Pan (1.C), Jipal -> Pan (member), Mupun -> Mwaghavul
    (member), Mushere -> Cakfem-Mushere, Bijim and Kadung -> the Vaghat cluster (Wikipedia's links for
    both go to 'Kwanka language'; the Atlas gives Kadun and Kwanka as names of its Kwang member),
    Youm -> Ywom (Wikipedia links 'Ywam language'; name match only, stated in the text).
  * Not created: Talet (Wikipedia's link leads nowhere; not assumed to be Tal), Jarawa (the Atlas
    uses the name for Izere and for the Jar cluster), Atyap (a Kaduna people; no Plateau entry in the
    Atlas) — gaps. 'Ron-Kulere' is split, as the Atlas treats Run (Ron) and Kulere as two languages;
    Kulere already has a people record (Nasarawa batch).
  * A people is linked to an LGA where Ethnologue (via Wikipedia) lists its language there: presence
    only (settlement status unknown). 'Well documented' where the Atlas also places the language in
    that LGA, 'reported' otherwise. Wikipedia's row 'Langtang' (beside 'Langtang South') is read as
    Langtang North.
  * Ethnologue names not mapped (ambiguous or no record): Ibaas, Panawa (Bauchi in the Atlas), Duhwa,
    Manguna (Shagau), Jhar and Jar, Saya, Kadung/Bijim as languages are mapped to the Vaghat cluster
    only through the people, Dass, Jorto, Takad (Tyap), Ganang, Jukun (language unspecified).
  * Settlers named by Wikipedia with existing records (Hausa, Idoma, Igbo, Yoruba): linked to the
    state as migrant communities.
"""
import json, sys

ACCESSED = "2026-09-30"
SOURCES = {
    "WPLA": dict(source_type="encyclopedia", title="Plateau State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Plateau_State",
                 verification_status="needs_corroboration",
                 notes="Demographics: 'over forty ethno-linguistic groups'; 'some of the indigenous ethnic groups': Afizere, Amo, Anaguta, Aten, Atyap, Bache, Berom, Bijim, Bogghom, Buji, Fier, Goemai, Irigwe, Jarawa, Jipal, Jukun, Kadung, Kofyar (Doemak, Kwalla, Mernyang), Miship, Montol, Mupun, Mushere, Mwaghavul, Ngas, Piapung, Pyem, Ron-Kulere, Talet, Tarok, Tiv, Youm. Settlers: Hausa, Idoma, Igbo, Yoruba, Ibibio, Annang, Efik, Ijaw, Bini. Languages section: table of languages by LGA citing Ethnologue (22nd ed.). Accessed 2026-09-30."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified", notes="Reused."),
}
LGA = {"barkin-ladi": "Barkin Ladi", "bassa": "Bassa", "bokkos": "Bokkos", "jos-east": "Jos East", "jos-north": "Jos North", "jos-south": "Jos South",
       "kanam": "Kanam", "kanke": "Kanke", "langtang-north": "Langtang North", "langtang-south": "Langtang South", "mangu": "Mangu", "mikang": "Mikang",
       "pankshin": "Pankshin", "qua-an-pan": "Qua'an Pan", "riyom": "Riyom", "shendam": "Shendam", "wase": "Wase"}
# Ethnologue (via Wikipedia) languages by LGA, as printed (unmappable entries kept, and skipped by LANGMAP).
ETH = {
 "barkin-ladi": ["Berom", "Ron", "Ibaas"],
 "bassa": ["Amo", "Buji", "Cara", "Anaguta", "Izora", "Janji", "Jere", "Kuce", "Panawa", "Rigwe", "Sanga", "Chokobo", "Gus", "Bache (Rukuba)", "Tarya", "Lemoro"],
 "bokkos": ["Bo-Rukul", "Duhwa", "Hasha", "Horom", "Kulere", "Mushere", "Mundat", "Nungu", "Ron", "Manguna (Shagau)"],
 "jos-east": ["Afizere", "Duguza"],
 "jos-north": ["Afizere", "Anaguta", "Berom"],
 "jos-south": ["Berom"],
 "kanam": ["Jhar", "Boghom", "Duguri", "Ngas", "Tarok", "Yangkam", "Saya"],
 "langtang-north": ["Tarok"],
 "langtang-south": ["Tarok", "Tiv"],
 "mangu": ["Mwaghavul", "Jipal", "Pyem", "Chakfem", "Bijim", "Kadung"],
 "mikang": ["Montol", "Tarok", "Youm"],
 "kanke": ["Ngas", "Jar", "Mupun", "Tarok"],
 "pankshin": ["Fyer", "Mhiship", "Ngas", "Jipal", "Mupun", "Pai", "Sur", "Tal", "Tambas", "Kadung", "Bijim"],
 "qua-an-pan": ["Kofyar", "Doemak", "Kwagalak", "Mernyang", "Teng", "Ngas", "Tiv"],
 "riyom": ["Berom", "Iten", "Takad (Tyap)"],
 "shendam": ["Boghom", "Dass", "Tiv", "Goemai", "Jorto", "Koenoem", "Miship", "Tarok", "Montol"],
 "wase": ["Jukun", "Boghom (Burmawa)", "Tarok", "Tiv"],
}
# Ethnologue name -> language record slug (batch 044 or existing)
LANGMAP = {"Berom": "berom", "Ron": "run", "Amo": "map", "Buji": "jere", "Cara": "cara", "Tarya": "cara", "Anaguta": "iguta", "Izora": "zora", "Chokobo": "zora",
           "Janji": "janji", "Jere": "jere", "Sanga": "jere", "Gus": "jere", "Kuce": "che", "Bache (Rukuba)": "che", "Rigwe": "rigwe", "Lemoro": "lemoro",
           "Bo-Rukul": "bo-rukul", "Hasha": "hasha", "Horom": "horom", "Kulere": "kulere", "Mushere": "cakfem-mushere", "Chakfem": "cakfem-mushere",
           "Mundat": "mundat", "Nungu": "rindre", "Afizere": "izere", "Duguza": "tunzu", "Boghom": "boghom", "Boghom (Burmawa)": "boghom", "Duguri": "doori",
           "Ngas": "ngas", "Tarok": "tarok", "Yangkam": "yangkam", "Tiv": "tiv", "Mwaghavul": "mwaghavul", "Sur": "mwaghavul", "Mupun": "mwaghavul",
           "Jipal": "pan", "Kofyar": "pan", "Doemak": "pan", "Kwagalak": "pan", "Mernyang": "pan", "Teng": "pan", "Pyem": "pyam", "Montol": "tel",
           "Youm": "ywom", "Fyer": "fyer", "Mhiship": "miship", "Miship": "miship", "Pai": "pe", "Tal": "tal", "Tambas": "tambas", "Iten": "aten",
           "Goemai": "goemai", "Koenoem": "koenoem", "Bijim": "vaghat-ya-bijim-legeri", "Kadung": "vaghat-ya-bijim-legeri"}
# Atlas LGA placements (batch 044 + existing Plateau links), to grade agreement.
ATLAS_LGA = {"berom": {"barkin-ladi"}, "aten": {"barkin-ladi"}, "bo-rukul": {"mangu"}, "boghom": {"kanam"}, "cakfem-mushere": {"mangu"}, "cara": {"bassa"},
             "che": {"bassa"}, "firan": {"barkin-ladi"}, "fyer": {"mangu"}, "horom": {"mangu"}, "iguta": {"bassa"}, "izere": {"jos-south", "barkin-ladi"},
             "janji": {"bassa"}, "doori": {"kanam"}, "mbat": {"kanam"}, "jere": {"bassa"}, "koenoem": {"shendam"}, "kulere": {"bokkos"}, "lemoro": {"bassa"},
             "map": {"bassa"}, "miship": {"mangu", "shendam"}, "mundat": {"mangu"}, "mwaghavul": {"barkin-ladi", "mangu"}, "ngas": {"pankshin", "kanam"},
             "pan": {"shendam", "mangu", "qua-an-pan"}, "pe": {"pankshin"}, "pyam": {"barkin-ladi", "mangu"}, "pyapung": {"shendam"}, "rigwe": {"bassa"},
             "run": {"bokkos", "mangu"}, "sha": {"mangu"}, "shagawu": {"mangu"}, "tal": {"pankshin"}, "tambas": {"mangu"}, "tarok": {"wase"},
             "tel": {"shendam"}, "tunzu": {"jos-east"}, "vaghat-ya-bijim-legeri": {"mangu"}, "yangkam": {"wase"}, "ywom": {"shendam"}, "zora": {"bassa"},
             "goemai": {"shendam"}, "hone": {"wase"}, "wapan": {"shendam"}, "tiv": {"langtang-south", "qua-an-pan", "shendam", "wase"}}
# slug: (name, language slugs, Ethnologue names that indicate them, [(other name, type, source note)], Atlas-based note)
E, X = "endonym", "alternative"
PEOPLE = {
 "afizere": ("Afizere", ["izere"], ["Afizere"],
          [("Fizere", E, "Atlas, Izere entry, 1.C"), ("Afusare", E, "Atlas, Izere entry, 1.C"), ("Afizarek", E, "Atlas, Izere entry, 1.C"), ("Jarawan Dutse", X, "Atlas, Izere entry, 2.C: other name for the people")],
          "Blench's Atlas calls their language Izere, a Plateau language spoken in Jos South and Barkin Ladi LGAs and in Toro LGA of Bauchi State."),
 "amo": ("Amo", ["map"], ["Amo"], [("Amap", E, "Atlas, Map entry, 1.C (plural; singular Kumap)")],
          "Blench's Atlas calls their language Map (Glottolog: Amo), a Kainji language of Bassa LGA and Saminaka LGA, Kaduna State."),
 "anaguta": ("Anaguta", ["iguta"], ["Anaguta"], [],
          "Blench's Atlas calls their language Iguta (also Naraguta), a Kainji language of Bassa LGA."),
 "aten": ("Aten", ["aten"], ["Iten"], [("Nìtèn", E, "Atlas, Aten entry, 1.C (plural; singular Àtên)")],
          "Blench's Atlas places their language, a Beromic language, in Barkin Ladi LGA and in Jema'a LGA of Kaduna State; Ethnologue's name for it is Iten."),
 "bache": ("Bache", ["che"], ["Kuce", "Bache (Rukuba)"], [("Rukuba", X, "Wikipedia; also the Atlas's location name for their language (2.A)")],
          "Blench's Atlas calls their language Che (also Kuche, Rukuba), a Plateau language of Bassa LGA, and gives Bache as the people's own name."),
 "berom": ("Berom", ["berom"], ["Berom"], [("Birom", E, "Atlas, Berom entry, 1.C"), ("Wòrom", E, "Atlas, Berom entry, 1.C (singular)")],
          "Blench's Atlas places their language, the largest of the Beromic group, in Barkin Ladi and (old) Jos LGAs and in Jema'a LGA of Kaduna State."),
 "boghom": ("Boghom", ["boghom"], ["Boghom", "Boghom (Burmawa)"], [("Burumawa", X, "Atlas, Boghom entry, 2.C: other name for the people"), ("Bogghom", X, "Wikipedia's spelling")],
          "Blench's Atlas places their language, a West Chadic language, in Kanam LGA."),
 "buji": ("Buji", ["jere"], ["Buji"], [("Boze", X, "Atlas: head name of the Boze member of the Jere cluster"), ("anaBoze", E, "Atlas, Boze entry, 1.C (plural)")],
          "Blench's Atlas calls them Boze, speakers of a member of the Jere cluster (Kainji) on both sides of the Jos–Zaria road north of Jos, in Bassa LGA; Glottolog treats Buji as a dialect of Jere."),
 "fyer": ("Fyer", ["fyer"], ["Fyer"], [("Fier", X, "Wikipedia's spelling; also Atlas 1.A")],
          "Blench's Atlas places their language, a West Chadic language of the Ron group, in Mangu LGA."),
 "goemai": ("Goemai", ["goemai"], ["Goemai"], [],
          "Blench's Atlas places their language, a West Chadic language, in Shendam LGA (and in Awe and Lafia LGAs, Nasarawa State)."),
 "irigwe": ("Irigwe", ["rigwe"], ["Rigwe"], [("yíɾìgʷȅ", E, "Atlas, Rigwe entry, 1.C (plural)")],
          "Blench's Atlas calls their language Rigwe (also Irigwe, Miango), a Plateau language of Bassa LGA and Kauru LGA, Kaduna State."),
 "jipal": ("Jipal", ["pan"], ["Jipal"], [],
          "Blench's Atlas lists Jipal, in Mangu LGA, as a member of the Pan cluster (the Kofyar language); Glottolog treats it as a dialect of Pan."),
 "kofyar": ("Kofyar", ["pan"], ["Kofyar", "Doemak", "Kwagalak", "Mernyang", "Teng"], [],
          "Wikipedia describes the Kofyar as comprising the Doemak, Kwalla and Mernyang. Blench's Atlas gives Kofyar as the people's own name for the speakers of the Pan cluster, spoken in Shendam, Mangu and Qua'an Pan LGAs."),
 "miship": ("Miship", ["miship"], ["Mhiship", "Miship"], [],
          "Blench's Atlas places their language, a West Chadic language, in Mangu and Shendam LGAs."),
 "montol": ("Montol", ["tel"], ["Montol"], [],
          "Blench's Atlas calls their language Tel (other names Montol, Baltap), a West Chadic language of Shendam LGA."),
 "mupun": ("Mupun", ["mwaghavul"], ["Mupun"], [],
          "Blench's Atlas lists Mupun as a member of the Mwaghavul cluster; Glottolog treats it as a dialect of Mwaghavul."),
 "mushere": ("Mushere", ["cakfem-mushere"], ["Mushere"], [],
          "Blench's Atlas places their language, a member of the Cakfem–Mushere pair (West Chadic), in about thirteen villages of Mangu LGA."),
 "mwaghavul": ("Mwaghavul", ["mwaghavul"], ["Mwaghavul", "Sur"], [("Sura", X, "Atlas, Mwaghavul entry, 2.C: other name for the people")],
          "Blench's Atlas places their language, a West Chadic language, in Barkin Ladi and Mangu LGAs."),
 "ngas": ("Ngas", ["ngas"], ["Ngas"], [("Kerang", E, "Atlas, Ngas entry, 1.C"), ("Angas", X, "Wikipedia")],
          "Blench's Atlas places their language, a West Chadic language with Hill and Plain dialects, in Pankshin, Kanam and (old) Langtang LGAs."),
 "piapung": ("Piapung", ["pyapung"], [], [("Pyapung", X, "Atlas head name of their language")],
          "Blench's Atlas calls their language Pyapung, a West Chadic language of Shendam LGA."),
 "pyem": ("Pyem", ["pyam"], ["Pyem"], [("Fyem", X, "Atlas, Pyam entry, 1.A")],
          "Blench's Atlas calls their language Pyam, a Plateau language of (old) Jos, Barkin Ladi and Mangu LGAs, and describes it as endangered."),
 "ron": ("Ron", ["run"], ["Ron"], [("Challa", X, "Atlas, Run entry, 2.C: other name for the people")],
          "Wikipedia lists them together with the Kulere as 'Ron-Kulere'; Blench's Atlas treats Run (Ron) and Kulere as separate West Chadic languages, and places Run in Bokkos and Mangu LGAs."),
 "tarok": ("Tarok", ["tarok"], ["Tarok"], [],
          "Blench's Atlas places their language, a Tarokoid language, in (old) Langtang and Wase LGAs."),
 "youm": ("Youm", ["ywom"], ["Youm"], [("Gerkawa", X, "Atlas, Ywom entry, 2.C: other name for the people")],
          "Wikipedia's Youm (linked there to 'Ywam language') are taken to be the speakers of the Atlas's Ywom (also Yiwom), a West Chadic language of Shendam and (old) Langtang LGAs; the match rests on the name."),
 "bijim": ("Bijim", ["vaghat-ya-bijim-legeri"], ["Bijim"], [],
          "Wikipedia links their language to 'Kwanka language'. Blench's Atlas places Bijim in Tafawa Balewa LGA of Bauchi State, as a member of the Vaghat–Ya–Bijim–Legeri cluster (Tarokoid), whose Plateau members are in Mangu LGA."),
 "kadung": ("Kadung", ["vaghat-ya-bijim-legeri"], ["Kadung"], [],
          "Wikipedia links their language to 'Kwanka language'. Blench's Atlas gives Kadun and Kwanka as other names of Kwang (Vaghat), a member of the Vaghat–Ya–Bijim–Legeri cluster (Tarokoid) in Mangu LGA."),
}
EXISTING = {"kulere": ["Kulere"], "jukun": ["Jukun"], "tiv": ["Tiv"]}
HAVE = {("tiv", "langtang-south"), ("tiv", "qua-an-pan"), ("tiv", "shendam"), ("tiv", "wase")}
SETTLERS = ["hausa", "idoma", "igbo", "yoruba"]


def lgas_of(eth):
    return [l for l, names in ETH.items() if any(n in names for n in eth)]


def text(slug):
    name, langs, eth, alt, note = PEOPLE[slug]
    t = f"The {name} are one of the indigenous peoples of Plateau State named by Wikipedia."
    names = [n for n, _, _ in alt]
    if names: t += f" Other names include {', '.join(names)}."
    if note: t += " " + note
    ls = lgas_of(eth)
    if ls: t += f" Ethnologue lists their language in {', '.join(LGA[l] for l in ls[:-1])}{' and ' if len(ls) > 1 else ''}{LGA[ls[-1]]} LGA{'s' if len(ls) > 1 else ''}."
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for slug, (name, langs, eth, alt, note) in PEOPLE.items():
    RECORDS.append(dict(key=slug, table="ethnic_groups", evidence="multiple_sources", level="well_documented",
                        fields=dict(name=name, slug=slug, summary=text(slug).split(". ")[0] + ".", description=text(slug)),
                        srcs=[("WPLA", f"{name} named among the indigenous peoples of Plateau; Ethnologue language table"), ("ATLAS", f"{name}: their language in Plateau")]))
    RELATIONS.append(dict(frm=slug, type="present_in", to="@admin_units:state:plateau", source="WPLA", evidence="multiple_sources", level="well_documented",
                          notes="Named among the indigenous peoples of the state by Wikipedia; their language is placed in Plateau by Blench's Atlas."))
    for lg in langs:
        RELATIONS.append(dict(frm=slug, type="speaks", to=f"@languages:{lg}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                              notes="Their language, per Blench's Atlas."))
    for l in lgas_of(eth):
        both = any(l in ATLAS_LGA.get(lg, ()) for lg in langs)
        RELATIONS.append(dict(frm=slug, type="present_in", to=f"@admin_units:lga:plateau/{l}", source="WPLA",
                              evidence="multiple_sources" if both else "single_reliable_source", level="well_documented" if both else "reported",
                              settlement_status="unknown",
                              notes="Their language is listed in this LGA by Ethnologue (via Wikipedia)" + ("; Blench's Atlas agrees." if both else ".") + " Presence only; the nature of their presence is not established."))
    for n, t, src in alt:
        NAMES.append(dict(record=slug, name=n, name_type=t, usage_notes=src + ".", srcs=["WPLA"] if src.startswith("Wikipedia") else ["ATLAS"]))

# Existing people records.
RELATIONS.append(dict(frm="@ethnic_groups:kulere", type="present_in", to="@admin_units:state:plateau", source="ATLAS", evidence="multiple_sources", level="well_documented",
                      notes="Wikipedia names them (as 'Ron-Kulere') among the indigenous peoples of Plateau; Blench's Atlas places the Kulere language in Bokkos LGA."))
RELATIONS.append(dict(frm="@ethnic_groups:jukun", type="present_in", to="@admin_units:state:plateau", source="WPLA", evidence="multiple_sources", level="well_documented",
                      notes="Named among the indigenous peoples of Plateau by Wikipedia; the Atlas places Jukunoid languages (Hone, Wapan) in Wase and Shendam LGAs."))
for slug, names in EXISTING.items():
    for l, lst in ETH.items():
        if any(n in lst for n in names) and (slug, l) not in HAVE:
            lang = LANGMAP.get(names[0])
            both = (slug == "kulere" and l in ATLAS_LGA["kulere"]) or (slug == "jukun" and l in ATLAS_LGA["hone"]) or (bool(lang) and l in ATLAS_LGA.get(lang, ()))
            RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to=f"@admin_units:lga:plateau/{l}", source="WPLA",
                                  evidence="multiple_sources" if both else "single_reliable_source", level="well_documented" if both else "reported", settlement_status="unknown",
                                  notes=f"Their language ({names[0]}) is listed in this LGA by Ethnologue (via Wikipedia)" + ("; Blench's Atlas agrees." if both else ".") + " Presence only."))
for g in SETTLERS:
    RELATIONS.append(dict(frm=f"@ethnic_groups:{g}", type="present_in", to="@admin_units:state:plateau", source="WPLA", evidence="single_reliable_source", level="reported",
                          settlement_status="migrant_community",
                          notes="Wikipedia names them among the people from other parts of the country who have settled in the state."))
# New language–LGA links from Ethnologue for current LGAs where the Atlas (batch 044) has none.
for l, names in ETH.items():
    for n in names:
        s = LANGMAP.get(n)
        if not s or l in ATLAS_LGA.get(s, ()):
            continue
        RELATIONS.append(dict(frm=f"@languages:{s}", type="spoken_in", to=f"@admin_units:lga:plateau/{l}", source="WPLA", evidence="single_reliable_source", level="reported",
                              notes=f"Listed in {LGA[l]} LGA by Ethnologue (22nd ed., via Wikipedia) as '{n}'."))
seen, RELS = set(), []
for r in RELATIONS:
    k = (r["frm"], r["type"], r["to"])
    if k not in seen:
        seen.add(k); RELS.append(r)
RELATIONS = RELS

GAPS = [
    ("No official list of Plateau's peoples", "The state government's People page names none, and the Internet Archive was offline, so archived state and NIPC pages could not be checked. Wikipedia says the state has 'over forty ethno-linguistic groups'."),
    ("Talet, Jarawa, Atyap", "Named by Wikipedia. Talet's link leads nowhere (not assumed to be Tal); 'Jarawa' is used in the Atlas both for the Izere and for the Jar cluster; the Atyap are a Kaduna people with no Plateau entry in the Atlas. Not recorded."),
    ("The many smaller Plateau peoples", "About 20 language communities recorded in batch 044 (Cara, Che's neighbours, Janji, Lemoro, Zora, Tunzu, Horom, Bo-Rukul, Tal, Tambas, Pe and others) have no people record because Wikipedia does not name them."),
    ("Who is indigenous where", "All LGA links are presence only (settlement status unknown)."),
    ("Wikipedia's Ethnologue table", "Unmapped names: Ibaas, Panawa, Duhwa, Manguna (Shagau), Jhar and Jar, Saya, Dass, Jorto, Takad (Tyap), Ganang, and 'Jukun' as a language."),
    ("Youm and Ywom", "The Youm people record rests on a name match between Wikipedia's 'Youm' and the Atlas's 'Ywom'; a source that equates them is wanted."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Plateau (Phase 3): {len(PEOPLE)} peoples (Wikipedia's indigenous groups, checked against Blench's Atlas), LGA links from Ethnologue, language links; Kulere, Jukun, Tiv and settler links.")


def report():
    lga_people = {l: [] for l in LGA}
    for s, p in PEOPLE.items():
        for l in lgas_of(p[2]):
            lga_people[l].append(p[0])
    for s, names in EXISTING.items():
        for l, lst in ETH.items():
            if any(n in lst for n in names):
                lga_people[l].append(s.title())
    L = ["# Research batch 044b — Plateau: the peoples", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## Sources", "",
         "**As for Taraba, no official list of Plateau's peoples was found.**",
         "- The state government's People page names no ethnic groups.",
         "- The Internet Archive was offline, so archived state and NIPC pages could not be checked.",
         "- The records therefore rest on **Wikipedia's list of the state's indigenous groups, checked against Blench's Atlas**. They are graded *well documented* at most.", "",
         "## What it adds", "",
         f"- **{len(RECORDS)} new people records:** " + ", ".join(p[0] for p in PEOPLE.values()) + ". Each is linked to Plateau and to its language.",
         f"- **{sum(1 for r in RELATIONS if r['type'] == 'present_in' and 'lga:' in r['to'])} people–LGA links**, from Ethnologue's table via Wikipedia, which covers **all 17 current LGAs**.",
         "  - *Well documented* where the Atlas agrees, *reported* otherwise.",
         "  - All are presence only.",
         f"- **{sum(1 for r in RELATIONS if r['type'] == 'spoken_in')} new language–LGA links.** These are mostly for current LGAs the Atlas does not name (Jos North and East, Kanke, Mikang, Riyom and the two Langtangs).",
         "- **Existing records:**",
         "  - **Kulere**, from the Nasarawa batch, gets Plateau links for Bokkos.",
         "  - **Jukun** gets Plateau links for Wase.",
         "  - The settler groups Wikipedia names that already have records (**Hausa, Idoma, Igbo, Yoruba**) are linked to Plateau as *migrant communities*.",
         "- **Not recorded, listed as gaps:**",
         "  - **Talet:** Wikipedia's link leads nowhere.",
         "  - **Jarawa:** the name is ambiguous.",
         "  - **Atyap:** a Kaduna people.",
         "- All the new records are short, so they are noindex and the sitemap is unchanged.", "",
         "## People by LGA", "", "| LGA | Peoples linked |", "|---|---|"]
    for l, ps in lga_people.items():
        L.append(f"| {LGA[l]} | {', '.join(ps) or '—'} |")
    L += ["", "## The people records", ""] + [f"**{PEOPLE[s][0]}.** {text(s)}" + "\n" for s in PEOPLE] + \
         ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_044b_plateau_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_044b_plateau_peoples_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} (present_in LGA={sum(1 for r in RELATIONS if r['type'] == 'present_in' and 'lga:' in r['to'])}, spoken_in={sum(1 for r in RELATIONS if r['type'] == 'spoken_in')}) names={len(NAMES)}")
