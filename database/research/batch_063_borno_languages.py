"""
Research batch 063 — Borno (Phase 3, first batch): the languages of Borno State and the LGAs where Roger
Blench's Atlas of Nigerian Languages (2020) places them. Researched 2026-10-01. Pattern: batch 057.
Peoples (ethnic groups) are a separate batch, as for the other states.

Method: the Atlas PDF (Internet Archive copy) was re-extracted column by column into its 502 numbered entries
(the extractor was checked against the 69 Adamawa entries saved earlier — all found). Entries naming 'Borno'
or any of the 27 Borno LGAs were kept (33) and read by hand; 26 remain (data/atlas_borno_blocks.txt).

Boundaries: the Atlas uses Borno's pre-1991 borders, when it included today's Yobe State. Entries placed
only in LGAs now in Yobe (Bade, Fika, Damaturu, Geidam, Nguru, Gujba, Fune, Yunusari) are NOT linked to
Borno: Bade, Ɗuwai, Bole, Ngamo, Ngizim (left for a Yobe batch). Kanuri's Yobe LGAs are left out likewise.
Removed as false matches after reading: Waka (an index line mentions Borno), Huba, Kamwe and the Fali cluster
(Adamawa only). Hausa and Fulfulde: the Atlas does not place them in Borno by name; not linked here.
Other states in the Atlas wording: Jara and Tera are also placed in 'Bauchi State, Ako LGA' / Gombe LGAs —
not linked (Gombe and Bauchi are not in scope).
Names are typed by the Atlas's key (1.A spelling, 1.B own name for the language, 2.A location name,
2.B other name); own names of peoples (1.C, 2.C: e.g. Buduma, Beriberi, Kibaku's Cíbɔ̀k) are kept for the
peoples batch.

Glottocodes: matched by name in Glottolog's language index, map points checked to lie in or near Borno;
cluster records take Glottolog's group code (Kanuri–Kanembu kanu1279, Guduf-Gava gudu1252, Wandala-Malgwa
wand1281). Glottolog names that differ are stated: Chadian Arabic (Shuwa), Cineni, Guduf-Gava, Buduma
(Yedina), Tedaga (Teda; Kecherda is a Tedaga dialect).
"""
import json, sys

ACCESSED = "2026-10-01"
G = "https://glottolog.org/resource/languoid/id/"
SOURCES = {
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf",
                  verification_status="verified", notes="Reused. Borno entries read in full (data/atlas_borno_blocks.txt)."),
    "GLIDX": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Glottolog language index (resourcemap)", organisation="Glottolog",
                  url="https://glottolog.org/resourcemap.json?rsc=language", verification_status="verified", notes="Reused."),
    "GSAH": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Saharan (saha1256)", organisation="Glottolog", url=G + "saha1256",
                 verification_status="verified", notes="Saharan family; Kanuri-Kanembu (kanu1279) and Tebu (tebu1238, incl. Tedaga teda1241, of which Kecherda kech1245 is a dialect) are in Western Saharan. Consulted 2026-10-01."),
    "GSEM": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Semitic (semi1276)", organisation="Glottolog", url=G + "semi1276",
                 verification_status="verified", notes="Semitic family (Afro-Asiatic); Chadian Arabic (chad1249) is in Arabic > Sudanese-Chadian Arabic. Consulted 2026-10-01."),
}
BR = {  # new branches: key -> (name, glottocode, source key, description)
    "saharan": ("Saharan", "saha1256", "GSAH",
                "Saharan is a group of languages spoken around Lake Chad and in the central Sahara, in Nigeria, Niger, Chad and Libya. Roger Blench's Atlas of Nigerian Languages classes it as Nilo-Saharan and places two of its languages in Borno State: Kanuri (with Kanembu), the main language of Borno, and Teda. Glottolog treats Saharan as a family of its own (saha1256), with Kanuri-Kanembu and Tebu in its Western branch."),
    "semitic": ("Semitic", "semi1276", "GSEM",
                "Semitic is the branch of the Afro-Asiatic languages that includes Arabic, Hebrew and Amharic. In Nigeria it is represented by Arabic: Roger Blench's Atlas of Nigerian Languages records an Arabic cluster in Borno and Yobe states, chiefly Shuwa Arabic, and Glottolog lists Semitic as a family (semi1276) within Afro-Asiatic."),
}
LGA = {"abadam": "Abadam", "askira-uba": "Askira/Uba", "bama": "Bama", "bayo": "Bayo", "biu": "Biu", "chibok": "Chibok", "damboa": "Damboa", "dikwa": "Dikwa",
       "gubio": "Gubio", "guzamala": "Guzamala", "gwoza": "Gwoza", "hawul": "Hawul", "jere": "Jere", "kaga": "Kaga", "kala-balge": "Kala/Balge", "konduga": "Konduga",
       "kukawa": "Kukawa", "kwaya-kusar": "Kwaya Kusar", "mafa": "Mafa", "magumeri": "Magumeri", "maiduguri": "Maiduguri", "marte": "Marte", "mobbar": "Mobbar",
       "monguno": "Monguno", "ngala": "Ngala", "nganzai": "Nganzai", "shani": "Shani"}
S, B, L = "spelling_variant", "endonym", "alternative"
BM = "@languages:biu-mandara"
MANDARA = "Chadic (Biu–Mandara A: Mandara/Mafa/Sukur major group, Mandara group)"
BURA = "Chadic (Biu–Mandara A: Bura–Higi major group, Bura group)"
# key: (name, parent, class text, [(name, type, field)], [lgas], note, speakers, extra, glottocode)
LANG = {
 "afade": ("Afaɗə", BM, "Chadic (Biu–Mandara B: Mandage group)", [("Afade", S, "1.A"), ("Affade", S, "1.A"), ("Afaɗə", B, "1.B"), ("Kotoko", L, "2.A"), ("Mogari", L, "2.A")],
           ["ngala"], "It is also spoken in Cameroon.", "fewer than 20,000 in twelve villages in Nigeria (1990)", "Glottolog lists it as Afade.", "afad1236"),
 "shuwa-arabic": ("Shuwa Arabic", "br_semitic", "Afroasiatic (Semitic), the Shuwa member of its Arabic cluster",
                  [("Shuwa", S, "1.A"), ("Shua", S, "1.A"), ("Choa", S, "1.A"), ("Arabiyye", B, "1.B"), ("Chadian Arabic", L, "GL")],
                  ["dikwa", "konduga", "ngala", "bama"],
                  "The Atlas regards Dikwa, Konduga, Ngala and Bama as residential areas, but notes that the Shuwa range widely across Borno and Yobe states on transhumance, and that the name Shuwa is regarded as pejorative, at least in Chad. It is also spoken in Cameroon, Chad and Niger, where it is a lingua franca.",
                  "about 100,000 in Nigeria (SIL 1973), and over 1.7 million in total", "The Atlas adds that the Boko Haram insurgency caused many Shuwa Arabs to leave Nigeria and devastated their villages. The New Testament appeared in 1967. Glottolog lists the language as Chadian Arabic.", "chad1249"),
 "bura-pabir": ("Bura–Pabir", BM, BURA, [("Bourrah", S, "1.A"), ("Burra", S, "1.A"), ("Babur", S, "1.A"), ("Mya Bura", B, "1.B"), ("Kwojeffa", L, "2.A")],
                ["biu", "askira-uba"], "The Atlas describes it as one language of two peoples, the Bura and the Pabir, with two dialects: Bura Pela (Hill Bura) and Bura Hyil Hawul (Plains Bura).",
                "72,200 (1952) and 250,000 (UBS 1987)", "The New Testament appeared in 1950 and the complete Bible in 2014, and there is extensive literacy material and a Bura sign language.", "bura1292"),
 "cibak": ("Cibak", BM, BURA, [("Chibak", S, "1.A"), ("Chibuk", S, "1.A"), ("Kyibaku", S, "1.A"), ("Kibaku", S, "1.A")], ["damboa"],
           "The Atlas places it south of Damboa town.", "20,000 (SIL 1973)", "", "ciba1236"),
 "cinene": ("Cinene", BM, MANDARA, [], ["gwoza"], "It is spoken in five villages in the mountains east of Gwoza town.", "3,200 (Kim 2001)", "Glottolog lists it as Cineni.", "cine1238"),
 "dghwede": ("Dghweɗe", BM, "Chadic (Biu–Mandara A: Mandara group)", [("Dghwede", S, "1.A"), ("Hude", S, "1.A"), ("Johode", S, "1.A"), ("Dghwéɗè", B, "1.B"), ("Azaghvana", L, "2.B"), ("Zaghvana", L, "2.B")],
             ["gwoza"], "", "19,000 (1963), 7,900 (1970) and 30,000 (UBS 1980)", "The New Testament appeared in 1980.", "dghw1239"),
 "glavda": ("Glavda", BM, MANDARA, [("Galavda", S, "1.A"), ("Glanda", S, "1.A"), ("Gelebda", S, "1.A")], ["gwoza"],
            "It is also spoken in Cameroon; Ngoshe (Ngweshe) is a dialect.", "20,000 (1963) in Nigeria and 2,800 in Cameroon (SIL 1982)", "", "glav1244"),
 "guduf-cikide": ("Guduf–Cikide", BM, MANDARA, [("Guduf", L, "member"), ("Gava", L, "member"), ("Cikide", L, "member")], ["gwoza"],
                  "It is a cluster of Guduf, Gava and Cikide, spoken in six main villages in the mountains east of Gwoza town.", "21,300 (1963)", "Glottolog lists the group as Guduf-Gava.", "gudu1252"),
 "jara": ("Jara", BM, "Chadic (Biu–Mandara A: Tera group)", [("Jera", S, "1.A")], ["biu"], "The Atlas also places it in Ako LGA, which it gives under Bauchi State.", "4,000 (SIL)", "", "jara1274"),
 "jilbe": ("Jilbe", BM, "Chadic (Biu–Mandara B: Mandage group)", [], [],
           "The Atlas places it in a single village on the Nigeria–Cameroon border, south of Dikwa, without naming the LGA.", "perhaps 100 (Tourneux 1999)", "", "jilb1238"),
 "kanuri": ("Kanuri", "br_saharan", "Nilo-Saharan (West Saharan), as part of its Kanuri–Kanembu cluster",
            [("Kanouri", S, "1.A"), ("Kànùrí", B, "1.B"), ("Borno", L, "2.A"), ("Bornu", L, "2.A"), ("Kanuri-Kanembu", L, "GL")],
            ["kukawa", "kaga", "konduga", "maiduguri", "monguno", "ngala", "bama", "gwoza"],
            "The Atlas lists it under the old Borno LGAs, some of which are now in Yobe State and are not linked here; Kanembu, spoken by a separate people, is placed in the LGAs on the edge of Lake Chad. Kanuri is also spoken in Niger, Cameroon and Chad, with diaspora communities in Sudan and Eritrea. Its dialects include Yerwa, Badawai, Koyam, Lere, Mober and Jetko.",
            "3,000,000 in Nigeria, and 3,500,000 for the cluster (UBS 1987)",
            "It has an official orthography; scripture portions appeared in 1853 and the Gospel of John in 1949 and 1965, including in Ajami script. Glottolog groups Kanuri and Kanembu as Kanuri-Kanembu (kanu1279).", "kanu1279"),
 "mafa": ("Mafa", BM, "Chadic (Biu–Mandara A: Mandara/Mafa/Sukur major group, Mafa group)", [("Mofa", S, "1.A")], ["gwoza"],
          "It is spoken mainly in Cameroon. Despite the shared name, the Atlas does not place it in Mafa LGA.", "2,000 (1963) in Nigeria and 136,000 in Cameroon (SIL 1982)", "The complete Bible appeared in 1989 (Cameroon).", "mafa1239"),
 "nggwahyi": ("Nggwahyi", BM, BURA, [("Ngwaxi", S, "1.A"), ("Ngwohi", S, "1.A")], ["askira-uba"], "It is spoken in one village.", "", "", "nggw1242"),
 "putai": ("Putai", BM, BURA, [("Margi West", L, "2.B")], ["damboa"], "The Atlas notes that the language is dying out, though the ethnic population is large.", "", "", "puta1243"),
 "teda": ("Teda", "br_saharan", "Nilo-Saharan (Saharan)", [("Tubu", L, "1.A"), ("Kecherda", L, "1.A"), ("Daza", L, "1.A"), ("Tedaga", L, "GL")], [],
          "The Atlas places it in a few villages in the north-eastern LGAs of Borno State, without naming them; it is spoken mostly in Niger and Chad, and the dialect spoken in Nigeria is Kecherda.",
          "fewer than 2,000 in Nigeria", "Glottolog lists Kecherda as a dialect of Tedaga (teda1241).", "teda1241"),
 "tera": ("Tera", BM, "Chadic (Biu–Mandara A: Tera group)", [("Nyimatli", L, "member"), ("Pidlimdi", L, "member")], ["biu", "bayo"],
          "It is a cluster whose members include Nyimatli (also in Gombe State and in Ɓayo LGA), Pidlimdi (Hina) and Bura Kokura, the last two in Biu LGA.", "46,000 (SIL) to 50,000 (Newman 1970)", "", "tera1251"),
 "wandala": ("Wandala", BM, MANDARA, [("Mandara", L, "1.A"), ("Ndara", S, "1.A"), ("Malgwa", L, "member")],
             ["bama", "gwoza", "damboa", "konduga"],
             "It is a cluster of Wandala (Mandara), a vehicular language in this part of Nigeria and Cameroon, Malgwa (Gamergu), spoken in Damboa, Gwoza and Konduga LGAs, and Mura, which may not be spoken in Nigeria.",
             "19,300 in Nigeria (1970) and 23,500 in Cameroon (SIL 1982); Malgwa 10,000 (1970)", "Glottolog groups Wandala and Malgwa as Wandala-Malgwa (wand1281).", "wand1281"),
 "yedina": ("Yedina", BM, "Chadic (Biu–Mandara B: Yedina group)", [("Yídə́nà", S, "1.A"), ("Buduma", L, "GL")], [],
            "The Atlas places it on the islands of Lake Chad, mostly in Chad, without naming an LGA; its speakers are known as Buduma.", "20,000 in Chad and 25,000 in all (SIL 1987)",
            "Glottolog lists it as Buduma.", "budu1265"),
}
REPORTED = set()
CAVEAT = "The Atlas may name the LGA as it was when its data were collected; several Borno LGAs have since been created from older ones."
FIELD = {"1.A": "alternate spelling of the name", "1.B": "the speakers' own name for the language", "2.A": "name based on location", "2.B": "other name for the language",
         "2.C": "other name, also used of the people", "GL": "Glottolog's name for the language", "member": "member of the cluster, as the Atlas lists it"}


def text(k):
    name, parent, cls, names, lgas, note, spk, extra, g = LANG[k]
    where = (f" in {', '.join(LGA[l] for l in lgas[:-1])}{' and ' if len(lgas) > 1 else ''}{LGA[lgas[-1]]} LGA{'s' if len(lgas) > 1 else ''} of Borno State") if lgas else " in Borno State"
    t = f"{name} is a language spoken{where}, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as {cls}."
    alt = [n for n, _, f in names if n != name and f not in ("GL", "member")]
    if alt: t += f" Other names include {', '.join(alt[:6])}."
    if note: t += " " + note
    if spk: t += f" The Atlas cites speaker figures of {spk}."
    if extra: t += " " + extra
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for k, (name, g, sk, desc) in BR.items():
    RECORDS.append(dict(key=f"br_{k}", table="languages", evidence="multiple_sources", level="well_documented",
                        fields=dict(lang_type="branch", name=name, slug=k, glottocode=g, summary=desc.split(". ")[0] + ".", description=desc),
                        srcs=[("ATLAS", f"{name}: classification"), (sk, f"{name} ({g})")]))
for k, (name, parent, cls, names, lgas, note, spk, extra, g) in LANG.items():
    f = dict(lang_type="language", name=name, slug=k, summary=text(k).split(". ")[0] + ".", description=text(k))
    if parent:
        f["parent_id"] = f"@key:{parent}" if parent.startswith("br_") else parent
    if g:
        f["glottocode"] = g
    RECORDS.append(dict(key=k, table="languages", evidence="single_reliable_source", level="well_documented", fields=f,
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers")] + ([("GLIDX", f"Glottocode {g}")] if g else [])))
    RELATIONS.append(dict(frm=k, type="spoken_in", to="@admin_units:state:borno", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes="Blench's Atlas."))
    for l in lgas:
        RELATIONS.append(dict(frm=k, type="spoken_in", to=f"@admin_units:lga:borno/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=CAVEAT))
    for n, t, fld in names:
        if n == name:
            continue
        src = "GLIDX" if fld == "GL" else "ATLAS"
        usage = "Glottolog's name." if fld == "GL" else f"Blench's Atlas (2020), {('field ' + fld + ': ') if fld[0].isdigit() else ''}{FIELD[fld]}."
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=usage, srcs=[src]))

# Existing records (Adamawa batch 057) that get Borno links
EXISTING = [
    ("dera", ["shani"], "Atlas: 'Adamawa State, Shellen LGA; Borno State, Shani LGA'."),
    ("gude", ["askira-uba"], "Atlas: 'Adamawa State, Mubi LGA; Borno State, Askira–Uba LGA; and in Cameroon'."),
    ("gvoko", ["gwoza"], "Atlas: 'Borno State, Gwoza LGA; Adamawa State, Michika LGA'."),
    ("hdi", ["gwoza"], "Atlas: 'Borno State, Gwoza LGA; Adamawa State, Michika LGA; and in Cameroon'."),
    ("lamang", ["gwoza"], "Atlas: all three members (Zaladva, Ghumbagha, Ghudavan) in 'Borno State, Gwoza LGA'."),
    ("margi", ["askira-uba", "damboa"], "Atlas: 'Borno State, Askira–Uba and Damboa LGAs; Adamawa State, Madagali, Mubi and Michika LGAs'."),
    ("margi-south", ["askira-uba"], "Atlas: 'Borno State, Askira–Uba LGA; Adamawa State, Mubi and Michika LGAs'."),
    ("vemgo-mabas", ["gwoza"], "Atlas: Vemgo in 'Borno State, Gwoza LGA; Adamawa State, Michika LGA; and in Cameroon'."),
]
for lang, lgas, note in EXISTING:
    RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to="@admin_units:state:borno", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
    for l in lgas:
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=f"@admin_units:lga:borno/{l}", source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
RELATIONS.append(dict(frm="@ethnic_groups:kanuri", type="speaks", to="kanuri", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                      notes="Their language (Blench's Atlas: Kanuri, own name Kànùrí; also spoken by the Kanembu)."))

GAPS = [
    ("Borno: languages of the old Borno now in Yobe", "The Atlas places Bade, Ɗuwai, Bole, Ngamo and Ngizim, and part of Kanuri, in LGAs of pre-1991 Borno that are now in Yobe State (Bade, Fika, Damaturu, Geidam, Nguru, Gujba, Fune, Yunusari). Not linked to Borno; for a Yobe batch."),
    ("Borno: Arabic varieties other than Shuwa", "The Atlas's Arabic cluster also records Uled Suliman (Libyan) Arabic in 'Geidam, Mober, Yunusari LGAs' and Baggara (Sudanese) Arabic in Yobe. Only Shuwa Arabic is recorded; Mobbar is the one Borno LGA named for Uled Suliman."),
    ("Borno: LGAs with no language placed by name", "Abadam, Chibok, Gubio, Guzamala, Hawul, Jere, Kala/Balge, Kwaya Kusar, Mafa, Magumeri, Marte, Mobbar and Nganzai have no Atlas language placed in them by name, though Kanuri and Shuwa Arabic are described as widespread. Several were created after the Atlas's sources (e.g. Hawul, named in the Bura Hyil Hawul dialect). A current source is needed."),
    ("Borno: Teda, Yedina and Jilbe", "Placed only as 'north-eastern LGAs', 'islands of Lake Chad' and 'south of Dikwa'; linked to the state only."),
    ("Borno: Hausa and Fulfulde", "Widely spoken in Borno but not placed there by name in the Atlas; Hausa is not yet a language record."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Borno languages (Blench Atlas, read in full): Saharan and Semitic branches, {len(LANG)} languages, LGA links, other names; Borno links for {len(EXISTING)} existing languages; Kanuri speak Kanuri.")


def report():
    per_lga = {l: [] for l in LGA}
    for k, v in LANG.items():
        for l in v[4]:
            per_lga[l].append(v[0])
    for lang, lgas, _ in EXISTING:
        for l in lgas:
            per_lga[l].append(lang.replace("-", " ").title() + " (existing)")
    L = ["# Research batch 063 — Borno: the languages", "",
         f"Researched {ACCESSED}. Phase 3, the first Borno batch. Created in review; published only after your approval.", "",
         "## Summary", "",
         f"- **{len(LANG)} languages** and **2 new branches**, Saharan (Glottolog `saha1256`) and Semitic (`semi1276`), all from Blench's Atlas.",
         "  - All 502 Atlas entries were searched for Borno and its 27 LGA names; 33 matched and every one was read by hand.",
         "  - 26 entries were kept. Removed as false matches: Waka, Huba, Kamwe and Fali.",
         "  - Removed because the Atlas uses Borno's pre-1991 borders: Bade, Ɗuwai, Bole, Ngamo and Ngizim, whose LGAs are now in Yobe.",
         f"- **{sum(1 for v in LANG.values() if v[8])} Glottocodes**, each matched by name with its map point checked. Cluster records take Glottolog's group code.",
         f"- **{len(RELATIONS)} links**, to the state and to LGAs, and **{len(NAMES)} other names**, typed by the Atlas's key. The peoples' own names (such as Buduma) are kept for the peoples batch.",
         f"- **{len(EXISTING)} existing languages get Borno links:** Dera, Guɗe, Gvoko, Hdi, Lamang, Margi, Margi South and Vemgo–Mabas.",
         "- **The Kanuri** people (an existing record) are now linked as speaking the new Kanuri language record.",
         "- **13 LGAs have no language placed in them by name** in the Atlas. Several were created after its sources. This is listed as a gap.", "",
         "## Languages per LGA (as the Atlas names them)", "", "| LGA | Count | Languages |", "|---|---|---|"]
    for l, ns in per_lga.items():
        L.append(f"| {LGA[l]} | {len(ns)} | {', '.join(ns) or '—'} |")
    L += ["", "## The languages", ""]
    for k in LANG:
        L += [f"**{LANG[k][0]}** ({LANG[k][8] or 'no Glottocode'}). {text(k)}", ""]
    L += ["## New branches", ""] + [f"**{v[0]}** (`{v[1]}`). {v[3]}\n" for v in BR.values()]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_063_borno_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_063_borno_languages_REVIEW.md", "w").write(report())
    print(f"languages={len(LANG)} branches={len(BR)} relations={len(RELATIONS)} names={len(NAMES)} glotto={sum(1 for v in LANG.values() if v[8])}")
