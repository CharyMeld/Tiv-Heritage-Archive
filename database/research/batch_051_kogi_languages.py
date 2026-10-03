"""
Research batch 051 — Kogi (Phase 3, first batch): the languages of Kogi State and the LGAs where Roger
Blench's Atlas of Nigerian Languages (2020) places them. Researched 2026-09-30.

Method: all 491 Atlas entries (column-split, atlas_extract.py) were searched for 'Kogi State', the 21
Kogi LGA names, and — because Kogi was created in 1991 from Kwara and Benue states — 'Kwara State' and
'Benue State'. Eleven entries concern Kogi (data/atlas_kogi_blocks.txt) and were read in full.

Boundaries: the Atlas often uses the pre-1991 states and LGAs: 'Kwara State, Kogi / Okene / Oyi LGA'
and 'Benue State, Ankpa / Dekina / Idah / Bassa LGAs'. Only today's Kogi LGAs whose names the Atlas uses
are linked, plus three 'reported' links where the Atlas names the town or district that a later LGA is
named after: Ogori and Magongo (Ọkọ and Ọsayẹn) -> Ogori/Magongo; the Ìbàjì dialect of Igala -> Ibaji;
Kabba District (Uwu) -> Kabba/Bunu. 'Oyi LGA' of the former Kwara State (Ọkpamheri) is linked to the
state only.

Existing records that get Kogi links: Ebira (Okene, Okehi, Kogi and — its Koto member — Bassa LGAs) and
Basa-Benue (Bassa and Ankpa). New branches: Yoruboid (Glottolog yoru1244; Igala, Yoruba) and Edoid
(edoi1239; Ọkpamheri). The Atlas's Nupe entry also places 'small but well established Nupe
communities' in Ibi (Taraba) and in Nasarawa State — linked, which answers the Taraba gap 'Nupe at Ibi'.
The existing Yoruba people record speaks the new Yoruba language record.
Excluded: the Arigidi cluster — its head line says 'Ondo State, Akoko North LGA; Kwara State, Kogi LGA',
but all ten members are in Akoko North, Ondo State (gap).
"""
import json, sys

ACCESSED = "2026-09-30"
SOURCES = {
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf",
                  verification_status="verified", notes="Reused. Kogi entries read in full (data/atlas_kogi_blocks.txt)."),
    "GLIDX": dict(source_type="dataset", source_kind="linguistic_database", source_tier=2, title="Glottolog language index (resourcemap)", organisation="Glottolog",
                  url="https://glottolog.org/resourcemap.json?rsc=language", verification_status="verified", notes="Reused."),
}
BR = {
    "yoruboid": ("Yoruboid", "yoru1244", "Yoruboid is a group of languages of south-western and central Nigeria and neighbouring Benin and Togo. Roger Blench's Atlas of Nigerian Languages classifies both Yoruba and Igala as Yoruboid, and Glottolog lists Yoruboid (yoru1244) as a family."),
    "ayere-ahan": ("Ayere–Ahan", "ayer1244", "Ayere–Ahan is a small group of Benue–Congo languages. Roger Blench's Atlas of Nigerian Languages classifies Uwu (Ayere), spoken in the Kabba area of Kogi State, as 'Uwu–Ahan', and Glottolog lists the Ayere–Ahan family (ayer1244), which contains Ayere and Ahan."),
    "edoid": ("Edoid", "edoi1239", "Edoid is a group of languages of southern Nigeria, centred on Edo State. Roger Blench's Atlas of Nigerian Languages classifies Ọkpamheri, spoken in Akoko-Edo and in the Kabba area of Kogi State, as North-Western Edoid, and Glottolog lists Edoid (edoi1239) as a family."),
}
S, B, L = "spelling_variant", "endonym", "alternative"
K = lambda l: f"@admin_units:lga:kogi/{l}"
ST = lambda s: f"@admin_units:state:{s}"
KOGI_OLD = "The Atlas uses the pre-1991 state and LGA names; the LGA named here is today's Kogi LGA of that name."
# key: (name, parent, class text, [(name, type, field)], [(unit ref, note)], place text, speakers, extra, glottocode)
LANG = {
 "igala": ("Igala", "br_yoruboid", "Yoruboid (East Benue–Congo)", [],
           [(ST("kogi"), "Atlas: 'Benue State, Ankpa, Dekina, Idah and Bassa LGAs' — now Kogi State."),
            (K("ankpa"), KOGI_OLD), (K("dekina"), KOGI_OLD), (K("idah"), KOGI_OLD), (K("bassa"), KOGI_OLD),
            (K("ibaji"), "The Atlas names the Ìbàjì dialect 'in Idah and Anambra(?) LGAs'; Ibaji LGA was created later and bears its name (reported).")],
           "The Atlas places it in Ankpa, Dekina, Idah and Bassa LGAs (listing them under Benue State, before Kogi State was created), and also in Oshimili and Anambra areas to the south.",
           "295,000 (1952); 800,000 (UBS 1987)",
           "Its dialects are Ánkpa and Ògùgù (Ankpa LGA), Ìfè (Ankpa and Dekina), Ànyìgbá (Dekina), 'Idáh and Ìbàjì (Idah area) and Èbú (Oshimili). It has an official orthography; the New Testament appeared in 1935 and the Bible in 1970.", "igal1242"),
 "kakanda": ("Kakanda", "nupoid", "Nupoid (Nupe group)", [("Akanda", S, "1.A"), ("Hyabe", L, "2.B"), ("Adyaktye", L, "2.B")],
             [(ST("kogi"), "Atlas: 'Kwara State, Kogi LGA' — now Kogi State."), (K("kogi"), KOGI_OLD)],
             "The Atlas places it in Kogi LGA and in Agaie and Lapai LGAs of Niger State, in communities along the Niger centred on Budon.",
             "4,500 (1931); 20,000 (Blench 1989)", "It is a cluster of Kakanda–Budon and Kakanda–Gbanmi/Sokun.", "kaka1264"),
 "kupa": ("Kupa", "nupoid", "Nupoid (Nupe group)", [],
          [(ST("kogi"), "Atlas: 'Kwara State, Kogi LGA, around Abugi' — now Kogi State."), (K("kogi"), KOGI_OLD)],
          "It is spoken in 52 villages around Abugi, in Kogi LGA.", "", "", "kupa1238"),
 "nupe": ("Nupe", "nupoid", "Nupoid",
          [("Nife", S, "1.A (Nupe Central)"), ("Nyffe", S, "1.A (Nupe Central)"), ("Anupe", S, "1.A (Nupe Central)"), ("Tapa", L, "2.B (Nupe Central)"),
           ("Takpa", L, "2.B (Nupe Central)"), ("Nupenci", L, "2.B (Nupe Central)"), ("Ibara", L, "2.B (Nupe Tako)")],
          [(ST("kogi"), "Atlas: 'Kwara State, Edu and Kogi LGAs' and 'Kogi State, Bassa LGA' (Nupe Tako)."), (K("kogi"), KOGI_OLD),
           (K("bassa"), "Nupe Tako: 'Kogi State, Bassa LGA' (Atlas)."), (ST("niger"), "Atlas: Niger State, Lavun, Mariga, Gbako, Agaie and Lapai LGAs."),
           (ST("kwara"), "Atlas: Kwara State, Edu LGA."), ("@admin_units:lga:taraba/ibi", "Atlas: 'small but well established Nupe communities in Ibi (Taraba State)'."),
           (ST("nasarawa"), "Atlas: 'small but well established Nupe communities in ... Nasarawa State'.")],
          "The Atlas places Nupe mainly in Niger State (Lavun, Mariga, Gbako, Agaie and Lapai LGAs), in Edu and Kogi LGAs, in the Federal Capital Territory and, as Nupe Tako, in Bassa LGA of Kogi State; there are small but well established Nupe communities in Ibi (Taraba) and in Nasarawa State.",
          "360,000 (1952); 1,000,000 (UBS 1987), a figure that may include closely related languages",
          "It is a cluster of Nupe (Central), the accepted literary form, and Nupe Tako (Basa Nge). Scripture portions appeared from 1860 and the Bible in 1953. Nupe was still spoken in Brazil at the end of the nineteenth century, and was recorded in Cuba as Lucumu Tacua.", "nupe1254"),
 "oko-eni-osayen": ("Ọkọ–Eni–Ọsayẹn", None, "an isolated branch of Benue–Congo (the Ọkọ–Eni–Ọsayẹn cluster)",
          [("Oko", S, "1.A (Ọkọ)"), ("Uku", S, "1.A (Ọkọ)"), ("Ogori", L, "2.A (Ọkọ; town name)"), ("Osanyin", S, "1.A (Ọsayẹn)"), ("Magongo", L, "2.A (Ọsayẹn; town name)")],
          [(ST("kogi"), "Atlas: 'Kwara State, Okene LGA' — now Kogi State."),
           (K("ogori-magongo"), "The Atlas names Ogori and Magongo as the towns of Ọkọ and Ọsayẹn; Ogori/Magongo LGA was created later from the area (reported).")],
          "It is spoken at Ogori (Ọkọ) and Magongo (Ọsayẹn), which the Atlas places in Okene LGA, now Ogori/Magongo LGA.",
          "about 4,000 (Ọkọ), 3,000 (Eni) and 3,000 (Ọsayẹn) (1970, uncertain)",
          "It is a cluster of three members, Ọkọ, Eni and Ọsayẹn, forming their own branch of Benue–Congo.", "okoe1238"),
 "okpamheri": ("Ọkpamheri", "br_edoid", "Edoid (North-Western Edoid, Southern)", [("Opameri", S, "1.A")],
          [(ST("kogi"), "Atlas: 'Kwara State, Oyi LGA' — Oyi LGA of the former Kwara State, now in Kogi State; its present LGA is not stated."), (ST("edo"), "Atlas: 'Edo State, Akoko–Edo LGA'.")],
          "The Atlas places it in Akoko-Edo LGA of Edo State and in Oyi LGA of the former Kwara State, now in Kogi State.",
          "18,136 (1957); 30,000 (SIL 1973)",
          "Its name means 'we are one'; its dialects are Okulosho, Western Okpamheri and Emhalhe, each with many sub-dialects named after towns.", "okpa1238"),
 "uwu": ("Uwu", "br_ayere-ahan", "Benue–Congo (Uwu–Ahan)", [("Ayere", S, "1.A")],
         [(ST("kogi"), "Atlas: 'Kwara State, Oyi LGA, Kabba District' — now Kogi State."),
          (K("kabba-bunu"), "The Atlas names Kabba District; its present LGA (presumably Kabba/Bunu) is reported, not stated.")],
         "The Atlas places it in Kabba District, in Oyi LGA of the former Kwara State.", "",
         "Glottolog lists it as Ayere (ayer1245), related to Ahan in the Ayere–Ahan group.", "ayer1245"),
 "yoruba": ("Yoruba", "br_yoruboid", "Yoruboid (Volta–Niger)",
          [("Yorùbá", B, "1.B"), ("Yorouba", S, "1.A"), ("Yariba", S, "1.A"), ("Aku", L, "2.A"), ("Nago", L, "2.A")],
          [(ST("kogi"), "Atlas: 'western LGAs in Kogi State'."), (ST("kwara"), "Atlas: 'most of Kwara ... States'."), (ST("lagos"), "Atlas."), (ST("osun"), "Atlas."),
           (ST("oyo"), "Atlas."), (ST("ogun"), "Atlas."), (ST("ondo"), "Atlas.")],
          "The Atlas places Yoruba in most of Kwara, Lagos, Osun, Oyo, Ogun and Ondo states, in the western LGAs of Kogi State, and in Benin Republic and Togo; it is also a ritual language in Cuba and Brazil.",
          "5,100,000 (1952); 15,000,000 (UBS 1984)",
          "It has many dialects. The Atlas's preliminary grouping includes an Okun group (Yagba, Gbede and Ijumu), and names Owe and Bunu among the dialects. It has an official orthography and a written literature of more than a century; Scripture portions appeared from 1850.", "yoru1245"),
}
FIELD = {"1.A": "alternate spelling of the name", "1.B": "the speakers' own name for the language", "2.A": "name based on location", "2.B": "other name for the language"}


def text(k):
    name, parent, cls, names, links, place, spk, extra, g = LANG[k]
    t = f"{name} is a language of Kogi State, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as {cls}. {place}"
    alt = [n for n, _, _ in names if n != name]
    if alt: t += f" Other names include {', '.join(alt[:6])}."
    if spk: t += f" The Atlas cites speaker figures of {spk}."
    if extra: t += " " + extra
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for k, (name, g, desc) in BR.items():
    RECORDS.append(dict(key=f"br_{k}", table="languages", evidence="multiple_sources", level="well_documented",
                        fields=dict(lang_type="branch", name=name, slug=k, glottocode=g, summary=desc.split(". ")[0] + ".", description=desc),
                        srcs=[("ATLAS", f"{name}: classification"), ("GLIDX", f"{name} ({g})")]))
for k, (name, parent, cls, names, links, place, spk, extra, g) in LANG.items():
    f = dict(lang_type="language", name=name, slug=k, summary=text(k).split(". ")[0] + ".", description=text(k), glottocode=g)
    if parent:
        f["parent_id"] = f"@key:{parent}" if parent.startswith("br_") else f"@languages:{parent}"
    RECORDS.append(dict(key=k, table="languages", evidence="single_reliable_source", level="well_documented", fields=f,
                        srcs=[("ATLAS", f"{name}: location, classification, other names, speakers"), ("GLIDX", f"Glottocode {g}")]))
    for ref, note in links:
        rep = "reported" in note
        RELATIONS.append(dict(frm=k, type="spoken_in", to=ref, source="ATLAS", evidence="single_reliable_source", level="reported" if rep else "well_documented", notes=note))
    for n, t, fld in names:
        NAMES.append(dict(record=k, name=n, name_type=t, usage_notes=f"Blench's Atlas (2020), field {fld}: {FIELD.get(fld[:3], 'head entry')}.", srcs=["ATLAS"]))
# Existing records
for lang, lgas, note in [
    ("ebira", ["okene", "okehi", "kogi", "bassa"], "Atlas: 'Kwara State, Okene, Okehi, and Kogi LGAs' (now Kogi State); its Koto member also in 'Kogi State, Bassa LGA'."),
    ("basa-benue", ["bassa", "ankpa"], "Atlas, entry 40.b: 'Kogi State, Bassa, and Ankpa LGAs'."),
]:
    RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=ST("kogi"), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
    for l in lgas:
        RELATIONS.append(dict(frm=f"@languages:{lang}", type="spoken_in", to=K(l), source="ATLAS", evidence="single_reliable_source", level="well_documented", notes=note))
RELATIONS.append(dict(frm="@ethnic_groups:yoruba", type="speaks", to="yoruba", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                      notes="Their language (Blench's Atlas: own name Yorùbá for the language and the people)."))

GAPS = [
    ("Kogi: Arigidi cluster", "The Atlas's head line says 'Ondo State, Akoko North LGA; Kwara State, Kogi LGA', but all ten members are placed in Akoko North, Ondo State. Not linked to Kogi."),
    ("Kogi: current LGAs", "The Atlas uses pre-1991 LGAs. Igala's presence in Olamaboro, Omala, Ofu and Igalamela-Odolu, Ebira's in Adavi and Ajaokuta, and Yoruba's in the western LGAs (Yagba East and West, Ijumu, Kabba/Bunu, Mopa-Muro, Lokoja) need a current source."),
    ("Kogi: Ọkpamheri and Uwu", "The Atlas names 'Oyi LGA' of the former Kwara State (Ọkpamheri) and its Kabba District (Uwu); their present LGAs are not stated."),
    ("Kogi: other languages", "Only the eleven Atlas entries that name Kogi or its LGAs (or their pre-1991 equivalents) were used; other languages spoken in Kogi, if any, need other sources."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope="Kogi languages (Blench Atlas, read in full): Yoruboid, Edoid and Ayere–Ahan branches, 8 languages, LGA links; Ebira and Basa-Benue Kogi links; Nupe at Ibi.")


def report():
    L = ["# Research batch 051 — Kogi: the languages", "",
         f"Researched {ACCESSED}. Phase 3, the first Kogi batch. Created in review; published only after your approval.", "",
         "## What it adds", "",
         f"- **{len(LANG)} languages** and **3 new branches**, Yoruboid, Edoid and Ayere–Ahan, all from Blench's Atlas. Each has a Glottolog code.",
         f"- **{len(RELATIONS)} links**, to states and LGAs, and **{len(NAMES)} other names**.",
         "- **Existing records get Kogi links:**",
         "  - **Ebira**, in Okene, Okehi, Kogi and Bassa.",
         "  - **Basa-Benue**, in Bassa and Ankpa.",
         "  - The **Yoruba** people now speak the new Yoruba record.",
         "  - **Nupe** is linked to Ibi (Taraba). This answers the Taraba gap 'Nupe at Ibi'.",
         "- **Boundaries:** the Atlas mostly uses the pre-1991 states ('Kwara State, Kogi LGA'; 'Benue State, Ankpa LGA'). Only today's Kogi LGAs of the same name are linked. There are three *reported* links where a later LGA is named after the town the Atlas gives: Ogori/Magongo, Ibaji and Kabba/Bunu.",
         "- **Kogi has fewer Atlas entries than Plateau or Taraba,** because it is dominated by a few large languages: Igala, Ebira, Yoruba (Okun) and Nupe.",
         "- **Excluded:** the Arigidi cluster, whose members are all in Ondo State.", "",
         "## The languages", ""]
    for k in LANG:
        L += [f"**{LANG[k][0]}** ({LANG[k][8]}). {text(k)}", ""]
    L += ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_051_kogi_languages.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_051_kogi_languages_REVIEW.md", "w").write(report())
    print(f"languages={len(LANG)} branches={len(BR)} relations={len(RELATIONS)} names={len(NAMES)}")
