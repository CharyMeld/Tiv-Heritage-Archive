"""
Research batch 057b — Adamawa (Phase 3): the peoples of Adamawa State and the LGAs where they live, as
far as the sources allow. Researched 2026-09-30. Pattern: Plateau batch 044b.

Sources:
  * Adamawa State Planning Commission (ADSPC), 'Adamawa State' page (adspc.ad.gov.ng/adamawa-state/),
    Internet Archive snapshot of 29 April 2023 (data/adspc_adamawa_state_2023-04-29.html): a table of
    43 'Popular Festivals' giving for each the locality, ETHNIC GROUP and LGA, and a table of tourist
    sites (Elephant House, 'of great significance to the Lunguda people'; Koma Hills, 'home to historic
    Koma people'; Sukur Kingdom). Official (Tier 1). The 2026 page keeps only festivals 1-10.
  * Wikipedia, 'Adamawa State': the ethnic groups named in its introduction, and a table of languages
    by LGA. The table cites the ADSPC page (accessed 2023-04-29), but the archived snapshots of that
    page (2022-2024) do not contain it, so it is attributed to Wikipedia only (gap).
  * Blench's Atlas (batch 057) for the languages and the peoples' own names (fields 1.C, 2.C).

Rules (as 044b):
  * A people record only for a group named by ADSPC or Wikipedia AND whose language the Atlas places
    in Adamawa (batch 057 records or existing ones). Names are mapped through the Atlas: Bwatiye/
    Bachama -> Bwatye; Higgi -> Kamwe (2.C Higi); Kilba -> Huba (2.A Kilba); Kanakuru -> Dera (2.A);
    Yungur -> Ɓena (2.B/2.C Yungur); Hona -> Hwana (1.A); Bare -> Bwazza (2.A Bare); Libbo -> Kaan
    (2.A Libo; name match only); Muchala -> the Fali town Muchella (Atlas 2.A of the Madzarin member);
    Verre -> Mom Jango and Momi (the Atlas's 'Vere' covers both); Kwa/Kwah [Bah] -> Baa (2.A Kwa).
  * LGA links are presence only (settlement status unknown).
    - Named by the ADSPC festival or tourist table: well documented (official), unless the match
      rests on a name only (Kaan/Libbo: reported).
    - Named only by Wikipedia's table: well documented where the Atlas also places the language in
      that LGA, reported otherwise.
    - ADSPC festivals whose LGA is 'Yola' (Fulani, Bachama) cannot be placed in Yola North or South:
      state link only.
  * Existing records get Adamawa links: Fulani, Chamba, Mumuye, Yandang (and Jibu at state level:
    Wikipedia's 'the Jibu in the far south').
"""
import json, sys
import batch_057_adamawa_languages as B57

ACCESSED = "2026-09-30"
SOURCES = {
    "ADSPC": dict(source_type="government_publication", source_kind="government_publication", source_tier=1, title="Adamawa State (state profile)",
                  organisation="Adamawa State Planning Commission", url="https://adspc.ad.gov.ng/adamawa-state/", verification_status="verified",
                  notes="Read via the Internet Archive snapshot of 2023-04-29 (web.archive.org/web/20230429233944/https://adspc.ad.gov.ng/adamawa-state/) and the live page on 2026-09-30. 'Popular Festivals' table (43 festivals: locality, ethnic group, month, duration, LGA, significance; the 2026 page keeps only 1-10); tourist sites table; minerals; market days. Copies in database/research/data/."),
    "WPAD": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Adamawa State", organisation="Wikipedia",
                 url="https://en.wikipedia.org/wiki/Adamawa_State", verification_status="needs_corroboration",
                 notes="Introduction: ethnic groups 'including the Verre, Tsobo, Bwatiye (Bachama), Bali, Bata (Gbwata), Gudu, Mbula-Bwazza, and Nungurab (Lunguda) in the central region; the Kamwe in the north and central region; the Jibu in the far south; the Kilba, Mafa, Marghi, and Waga in the north, and the Kwah [Bah], Mumuye in the south while the Fulani live throughout the state'. Languages section: table of languages by LGA (cites ADSPC, not found in its archived snapshots). Accessed 2026-09-30."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified", notes="Reused."),
}
LGA = B57.LGA
# Atlas LGA placements (batch 057 new + existing records)
ATLAS_LGA = {k: set(v[4]) for k, v in B57.LANG.items()}
for lang, lgas, _ in B57.EXISTING:
    ATLAS_LGA[lang] = set(lgas)
E, X = "endonym", "alternative"
AD, WT = "ADSPC", "WPAD"
# slug: (name, [language slugs], [(other name, type, source note)], note, [(lga, source, detail)], named in Wikipedia's list, name-match only)
PEOPLE = {
 "bachama": ("Bachama", ["bwatye"], [("Bwatiye", X, "Wikipedia; ADSPC festival table"), ("Ɓwaare", E, "Atlas, Bwatye entry, 1.C"), ("Bacama", X, "Wikipedia's table")],
          "Blench's Atlas calls their language Bwatye, a Chadic language of the Bata cluster, spoken in Numan and Guyuk LGAs; Bachama fishermen migrate long distances down the Benue. The state's festival table also lists three Bwatiye festivals in 'Yola' (Njuwa at Rugange, and the Ngpalakiyie/Sharo circumcision festival at Njoboli, shared with the Fulani).",
          [("demsa", AD, "the Vunon festival at Farai (Bwatiye and Mbula), marking the start of the rains, with prayers to the deity Nzeanzo"), ("fufore", AD, "the Mbele fishing festival at Ribadu"),
           ("numan", AD, "the Kadyaga festival at Numan"), ("numan", WT, "'Bachama'"), ("lamurde", AD, "the Wurokakai and Kwatte festivals at Lamurde"), ("lamurde", WT, "'Bacama'")], True, False),
 "bata": ("Bata", ["bata"], [("Gbwata", X, "Wikipedia; also Atlas 1.A")],
          "Blench's Atlas places their language, the Bata member of the Bata cluster (Chadic), in Numan, Song, Fufore and Mubi LGAs and in Cameroon.",
          [("demsa", WT, "'Bata'"), ("fufore", WT, "'Bata'"), ("girei", WT, "'Bata'")], True, False),
 "mbula": ("Mbula", ["mbula"], [],
          "Blench's Atlas places the Mbula cluster (Jarawan Bantu) in Numan, Shelleng and Song LGAs; its Tambo member has radio and television broadcasts. Wikipedia names the Mbula-Bwazza among the peoples of central Adamawa.",
          [("demsa", AD, "the Mba-Pur warrior festival at Demsa and the Vunon festival at Farai (with the Bwatiye)"), ("demsa", WT, "'Mbula-Bwazza'"),
           ("girei", AD, "the Mba Beng harvest festival at Tambo"), ("girei", WT, "'Tambo'")], True, False),
 "bwazza": ("Bwazza", ["mbula"], [("Bare", X, "ADSPC festival table; Atlas, Bwazza 2.A (a town name)"), ("Ɓwázà", E, "Atlas, Bwazza entry, 1.C")],
          "Blench's Atlas lists Bwazza as a member of the Mbula cluster (Jarawan Bantu), spoken in twenty-six villages of Demsa, Numan, Shelleng and Song LGAs. The state's festival table calls the people of Bwazza 'Bare'.",
          [("demsa", AD, "the Mba-Tambal and Mba-Zonhwo festivals at Bwazza, for the protection of the land and a good harvest"), ("demsa", WT, "'Mbula-Bwazza'")], True, False),
 "lunguda": ("Lunguda", ["longuda"], [("Nungurab", X, "Wikipedia"), ("Longuda", X, "Atlas head name of their language; Wikipedia's table"), ("Núngúráyábá", E, "Atlas, Longuda entry, 1.C (Guyuk)"),
            ("Nùngùrábà", E, "Atlas, Longuda entry, 1.C (Jessu)"), ("Lóngúrábá", E, "Atlas, Longuda entry, 1.C (Kola)")],
          "Blench's Atlas places their language, Longuda (Adamawa family), in Guyuk LGA and in Balanga LGA of Gombe State. The state lists the Elephant House in Guyuk LGA as 'ancient architecture of great significance to the Lunguda people'.",
          [("guyuk", AD, "the Simalama festival at the end of the rains, and the Elephant House"), ("guyuk", WT, "'Longuda'")], True, False),
 "kamwe": ("Kamwe", ["kamwe"], [("Higi", X, "Atlas, Kamwe entry, 2.C"), ("Higgi", X, "ADSPC festival table"), ("Hiji", X, "Atlas, Kamwe entry, 2.C"), ("Kapsiki", X, "Atlas, Kamwe entry, 2.C")],
          "Blench's Atlas places their language, a Chadic language of the Higi group, in Michika LGA and across the border in Cameroon. Wikipedia places the Kamwe in the north and centre of the state.",
          [("michika", AD, "the Zhita initiation festival at Bazza and the Yawle harvest festival at Kwabbapale"), ("michika", WT, "'Kamwe'")], True, False),
 "kilba": ("Kilba", ["huba"], [("Huba", E, "Atlas, Huba entry, 1.C")],
          "Blench's Atlas calls their language Huba (other name Kilba), a Chadic language spoken in Hong, Maiha, Mubi and Gombi LGAs.",
          [("hong", AD, "the Tiwa funeral festival at Hong, and the Mba Mba initiation festival (Kilba and Marghi)"), ("hong", WT, "'Kilba'")], True, False),
 "marghi": ("Marghi", ["margi", "margi-south"], [("Margi", X, "Atlas head name of their language; ADSPC"), ("Màrgí", E, "Atlas, Margi entry, 1.C")],
          "Blench's Atlas places Margi in Madagali, Michika and (old) Mubi LGAs and in Borno State, and Margi South in Michika and Mubi LGAs. The state lists the old palace at Hamayaji, Madagali LGA, as housing the history of a Marghi king.",
          [("madagali", AD, "the Dukwa and Yawal festivals at Gulak, Shuwa, Palam and Mildu, the Yinagu fishing festival at Gulak, and the Mba Mba festival (Kilba and Marghi)"), ("madagali", WT, "'Marghi'"),
           ("hong", AD, "the Mba Mba initiation festival, listed for Hong and Madagali (Kilba and Marghi)"), ("hong", WT, "'Marghi'")], True, False),
 "verre": ("Verre", ["mom-jango", "momi"], [("Vere", X, "Atlas: other name covering Mom Jango and Momi; Wikipedia's table")],
          "Blench's Atlas uses the name Vere for two related languages of the Vere group: Mom Jango (Fufore LGA) and Momi (Fufore and old Yola LGAs, and Cameroon).",
          [("fufore", AD, "the Poodeh circumcision festival, for the 'Verre in Nassarawo'"), ("yola-south", WT, "'Vere'")], True, False),
 "tsobo": ("Tsobo", ["tsobo"], [("nyi Tsó", E, "Atlas, Tsobo entry, 1.C"), ("Cibbo", X, "Atlas, Tsobo entry, 1.A")],
          "Blench's Atlas places their language, of the Waja group (Adamawa family), in Numan LGA and in Kaltungo LGA of Gombe State.",
          [("lamurde", WT, "'Tsobo'")], True, False),
 "gudu": ("Gudu", ["gudu"], [],
          "Blench's Atlas places their language, a Chadic language of the Bata group, in Song LGA, in about five villages 120 km west of Song.",
          [], True, False),
 "baa": ("Baa", ["baa"], [("Kwa", X, "Atlas, Baa entry, 2.A; Wikipedia's table"), ("Kwah", X, "Wikipedia ('Kwah [Bah]')"), ("Báà", E, "Atlas, Baa entry, 1.C (plural; singular raBáà)")],
          "Blench's Atlas places their language, of the Kwa group (Adamawa family), at Gyakan and Kwa towns in Numan LGA. Wikipedia names the 'Kwah [Bah]' among the peoples of the south.",
          [("lamurde", WT, "'Kwa'")], True, False),
 "yungur": ("Yungur", ["bena"], [("Ɓəna", E, "Atlas, Ɓena entry, 1.C"), ("Yungirba", X, "Atlas, Ɓena entry, 2.C"), ("Ɓena", X, "Atlas head name of their language")],
          "Blench's Atlas calls their language Ɓena (other names Yungur, Yangur), of the Yungur group (Adamawa family), in Song and Guyuk LGAs; the Ɓena are divided into seventeen clans.",
          [("song", AD, "the Hono initiation festival, listed for Song and Girei"), ("song", WT, "'Yungur'"), ("girei", AD, "the Hono initiation festival, listed for Song and Girei")], False, False),
 "ga-anda": ("Ga'anda", ["ga-anda"], [],
          "Blench's Atlas places their language, a Chadic cluster of Ga'anda, Kaɓən (Gabin) and Fərtata, in Gombi LGA.",
          [("gombi", AD, "the Kwayfa harvest festival and the Janda/Sumanta festival at Ga'anda"), ("gombi", WT, "'Ga'anda'")], False, False),
 "hwana": ("Hwana", ["hwana"], [("Hona", X, "ADSPC festival table; Atlas, Hwana entry, 1.A")],
          "Blench's Atlas places their language, a Chadic language of the Tera group, in Gombi LGA, at Guyuk and thirty other villages.",
          [("gombi", AD, "the Khombalta festival at Ga'anda/Hadi, listed for the 'Hona Fulani'"), ("gombi", WT, "'Hwana'")], False, False),
 "gude": ("Gude", ["gude"], [("Guɗe", X, "Atlas head name of their language")],
          "Blench's Atlas places their language, a Chadic language of the Bata group, in (old) Mubi LGA, in Askira–Uba LGA of Borno State and in Cameroon.",
          [("mubi-south", AD, "the Wangirwa harvest festival at Mubi and the Vulma festival at Gella"), ("mubi-south", WT, "'Gude'")], False, False),
 "fali": ("Fali", ["fali-mubi"], [("Muchala", X, "ADSPC festival table; Muchella is a Fali town (Atlas, Madzarin member, 2.A)"), ("Vimtim", X, "Atlas, Fali cluster, 2.C"), ("Yimtim", X, "Atlas, Fali cluster, 2.C")],
          "Blench's Atlas places the Fali of Mubi cluster in (old) Mubi LGA, in four principal towns: Vimtim, Bahuli, Muchella and Bagira. The Atlas also calls the speakers of Kirya-Konzəl (Michika LGA) Fali; they are not linked here. The state's festival table lists the Danbi festival at Mijulu for the Fali of 'Mubi'.",
          [("mubi-north", AD, "the Imandirza Zinna initiation festival at Gyalla, for the 'Muchala'"), ("mubi-north", WT, "'Fali'")], False, False),
 "nzanyi": ("Nzanyi", ["nzanyi"], [("Nzangɨ", E, "Atlas, Nzanyi entry, 1.C (singular)")],
          "Blench's Atlas places their language, a Chadic language of the Bata group, in Maiha LGA and in Cameroon.",
          [("maiha", AD, "the Alalile and Wagurwa initiation festivals"), ("maiha", WT, "'Nzanyi'")], False, False),
 "dera": ("Dera", ["dera"], [("Kanakuru", X, "ADSPC festival table; Wikipedia's table; Atlas, Dera entry, 2.A")],
          "Blench's Atlas places their language, a West Chadic language of the Bole group, in Shelleng LGA and in Shani LGA of Borno State.",
          [("shelleng", AD, "the Menjauli festival at the end of the rains"), ("shelleng", WT, "'Kanakuru'")], False, False),
 "kaan": ("Kaan", ["kaan"], [("Libo", X, "Atlas, Kaan entry, 2.A"), ("Libbo", X, "ADSPC festival table (name match only)")],
          "Blench's Atlas places their language, of the Yungur group (Adamawa family), in Guyuk LGA. The state's festival table lists the Lamushi initiation festival of the Libbo at Libbo, Shelleng LGA, and Wikipedia's table lists Kaan in Numan LGA. The three sources name three different LGAs, and the Libbo are identified with the Kaan by name only.",
          [("shelleng", AD, "the Lamushi initiation festival at Libbo, for the 'Libbo'"), ("numan", WT, "'Kaan'")], False, True),
 "sukur": ("Sukur", ["sakun"], [("Gə̀mà Sákún", E, "Atlas, Sakun entry, 1.C")],
          "Blench's Atlas calls their language Sakun (other name Sugur), a Chadic language spoken in seven villages of Madagali LGA. The state lists the Sukur Kingdom, at Sukur village, as a UNESCO heritage site.",
          [("madagali", AD, "the Zoku harvest festival at Sukur, and the Sukur Kingdom"), ("madagali", WT, "'Sukur'")], False, False),
 "bile": ("Ɓile", ["bile"], [("Bille", X, "ADSPC festival table; Wikipedia's table"), ("ɓa Ɓíilé", E, "Atlas, Ɓile entry, 1.C")],
          "Blench's Atlas places their language (Jarawan Bantu) 25 km south of Numan, east of the Wukari road, in 36 villages that are wholly Ɓile-speaking and 16 more where some Ɓile is spoken, in Numan LGA.",
          [("demsa", AD, "the Jabba circumcision festival at Demsa"), ("demsa", WT, "'Bille'")], False, False),
 "laka": ("Laka", ["laka-lau"], [("Lakka", X, "Wikipedia's table")],
          "Blench's Atlas places their language, of the Mbum group (Adamawa family), at Lau in Karim Lamido LGA (Taraba), in Yola LGA, and mainly in Cameroon.",
          [("yola-north", WT, "'Lakka'")], False, False),
 "koma": ("Koma", ["koma"], [],
          "Blench's Atlas places the Koma cluster (Adamawa family) in the Alantika Mountains, in Ganye and Fufore LGAs, and in Cameroon. The state lists the Koma Hills, at Koma village in Jada LGA, as 'home to historic Koma people'.",
          [("jada", AD, "the Koma Hills at Koma village"), ("jada", WT, "'Koma'")], False, False),
 "pere": ("Pere", ["pere"], [("Peere", X, "Wikipedia's table"), ("Pereba", E, "Atlas, Pere entry, 1.C (plural; singular Pena)")],
          "Blench's Atlas places their language, of the Leko group (Adamawa family), in ten villages around Yadim in Fufore LGA, and notes that Kutin, the same language, was formerly spoken in Ganye LGA.",
          [("ganye", WT, "'Peere'")], False, False),
 "mboi": ("Mboi", ["mboi"], [],
          "Blench's Atlas places their language, a cluster of Gana, Banga and Haanda (Yungur group), in Song LGA.",
          [("song", WT, "'Mboi'")], False, False),
 "lala": ("Lala", ["lala"], [("Lala-Roba", X, "Wikipedia's table; Glottolog")],
          "Blench's Atlas places the Lala cluster (Yang, Roba and Ebode; Yungur group) in Guyuk, Song and Gombi LGAs, and notes that 'Lala' is also used as a cover term for several groups there.",
          [("gombi", WT, "'Lala-Roba'")], False, False),
 "ngwaba": ("Ngwaba", ["ngwaba"], [("Goba", X, "Atlas, Ngwaba entry, 2.C"), ("Gombi", X, "Atlas, Ngwaba entry, 2.C")],
          "Blench's Atlas places their language, a Chadic language of the Bata group, at Fachi and Gudumiya in Gombi LGA.",
          [("gombi", WT, "'Ngwaba'")], False, False),
 "waka": ("Waka", ["waka"], [("Wakka", X, "Wikipedia's table")],
          "Blench's Atlas places their language, of the Yendang subgroup (Adamawa family), in Fufore and Mayo Belwa LGAs.",
          [("demsa", WT, "'Wakka'"), ("mayo-belwa", WT, "'Wakka'")], False, False),
}
# Existing people records: (language slugs for Atlas agreement, state note, [(lga, source, detail)])
EXISTING = {
 "fulani": (["fulfulde"], "Wikipedia: 'the Fulani live throughout the state, often as nomadic herders'; the state's festival table lists five Fulani festivals in 'Yola' (Shadi, Sorro, Remnol, Kilisa, and Ngpalakiyie/Sharo with the Bwatiye).",
            [("fufore", WT, "'Fulfulde'"), ("ganye", WT, "'Fulfulde'"), ("girei", WT, "'Fulfulde'"), ("jada", WT, "'Fulfulde'"), ("mayo-belwa", WT, "'Fulfulde'"), ("yola-south", WT, "'Fulfulde'")]),
 "chamba": (["samba-daka", "samba-leko"], "The state's festival table lists the Klashe Shatin festival of the Chamba and Mumuye in Jada LGA; the Atlas places Samba Daka and Samba Leko in Ganye LGA.",
            [("jada", AD, "the Klashe Shatin festival (Chamba and Mumuye)"), ("jada", WT, "'Chamba'"), ("ganye", WT, "'Chamba Daka'"), ("toungo", WT, "'Chamba'")]),
 "mumuye": (["mumuye"], "Wikipedia names the Mumuye among the peoples of the south; the state's festival table lists the Klashe Shatin festival of the Chamba and Mumuye in Jada LGA.",
            [("jada", AD, "the Klashe Shatin festival (Chamba and Mumuye)"), ("jada", WT, "'Mumuye'"), ("fufore", WT, "'Mumuye'"), ("ganye", WT, "'Mumuye'"),
             ("mayo-belwa", WT, "'Mumuye' (linked there to the Yendang languages)"), ("toungo", WT, "'Mumuye'"), ("yola-north", WT, "'Mumuye'"), ("yola-south", WT, "'Mumuye'")]),
 "yandang": (["yendang"], "The state's festival table lists two Yandang festivals in Mayo Belwa LGA; the Atlas places Yendang in Numan and Mayo Belwa LGAs.",
            [("mayo-belwa", AD, "the Phuki harvest festival at Kudaku and Gorobi and the Here-Yawetti festival at Gorobi")]),
}


def grade(langs, lga, evs, name_only):
    """-> (evidence, level, source) for one people-LGA link."""
    srcs = {s for s, _ in evs}
    atlas = any(lga in ATLAS_LGA.get(l, ()) for l in langs)
    if name_only:
        return "single_reliable_source", "reported", AD if AD in srcs else WT
    if AD in srcs:
        return ("multiple_sources" if (WT in srcs or atlas) else "single_reliable_source"), "well_documented", AD
    return ("multiple_sources", "well_documented", WT) if atlas else ("single_reliable_source", "reported", WT)


def by_lga(evs):
    out = {}
    for l, s, d in evs:
        out.setdefault(l, []).append((s, d))
    return out


def ev_note(evs, atlas):
    parts = []
    for s, d in evs:
        parts.append(f"Adamawa State Planning Commission: {d}." if s == AD else f"Wikipedia's language table lists {d} in this LGA.")
    if atlas:
        parts.append("Blench's Atlas places their language here.")
    return " ".join(parts) + " Presence only; the nature of their presence is not established."


def text(slug):
    name, langs, alt, note, evs, wl, _ = PEOPLE[slug]
    t = f"The {name} are a people of Adamawa State" + (", named by Wikipedia among the state's ethnic groups." if wl else ".")
    names = [n for n, _, _ in alt]
    if names: t += f" Other names include {', '.join(names)}."
    t += " " + note
    ls = list(by_lga(evs))
    if ls:
        t += f" The sources place them in {', '.join(LGA[l] for l in ls[:-1])}{' and ' if len(ls) > 1 else ''}{LGA[ls[-1]]} LGA{'s' if len(ls) > 1 else ''}."
    return t


RECORDS, RELATIONS, NAMES = [], [], []
for slug, (name, langs, alt, note, evs, wl, name_only) in PEOPLE.items():
    srcs = []
    if any(s == AD for _, s, _ in evs): srcs.append(("ADSPC", f"{name}: festivals or sites by LGA"))
    if wl or any(s == WT for _, s, _ in evs): srcs.append(("WPAD", f"{name}: named among the state's peoples or in its language table"))
    srcs.append(("ATLAS", f"{name}: their language in Adamawa"))
    RECORDS.append(dict(key=slug, table="ethnic_groups", evidence="multiple_sources", level="well_documented",
                        fields=dict(name=name, slug=slug, summary=text(slug).split(". ")[0] + ".", description=text(slug)), srcs=srcs))
    RELATIONS.append(dict(frm=slug, type="present_in", to="@admin_units:state:adamawa", source=srcs[0][0], evidence="multiple_sources", level="well_documented",
                          notes="Named by " + ("the Adamawa State Planning Commission" if srcs[0][0] == "ADSPC" else "Wikipedia") + "; their language is placed in Adamawa by Blench's Atlas."))
    for lg in langs:
        RELATIONS.append(dict(frm=slug, type="speaks", to=f"@languages:{lg}", source="ATLAS", evidence="single_reliable_source", level="well_documented",
                              notes="Their language, per Blench's Atlas."))
    for l, e in by_lga(evs).items():
        ev, lvl, src = grade(langs, l, e, name_only)
        atlas = any(l in ATLAS_LGA.get(x, ()) for x in langs)
        RELATIONS.append(dict(frm=slug, type="present_in", to=f"@admin_units:lga:adamawa/{l}", source=src, evidence=ev, level=lvl, settlement_status="unknown",
                              notes=ev_note(e, atlas) + (" The identification rests on a name match." if name_only else "")))
    for n, t, src in alt:
        NAMES.append(dict(record=slug, name=n, name_type=t, usage_notes=src + ".",
                          srcs=["ADSPC"] if src.startswith("ADSPC") else ["WPAD"] if src.startswith("Wikipedia") else ["ATLAS"]))

for slug, (langs, st_note, evs) in EXISTING.items():
    RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to="@admin_units:state:adamawa", source="WPAD" if slug == "fulani" else "ADSPC",
                          evidence="multiple_sources", level="well_documented", notes=st_note))
    for l, e in by_lga(evs).items():
        ev, lvl, src = grade(langs, l, e, False)
        atlas = any(l in ATLAS_LGA.get(x, ()) for x in langs)
        RELATIONS.append(dict(frm=f"@ethnic_groups:{slug}", type="present_in", to=f"@admin_units:lga:adamawa/{l}", source=src, evidence=ev, level=lvl,
                              settlement_status="unknown", notes=ev_note(e, atlas)))
RELATIONS.append(dict(frm="@ethnic_groups:jibu", type="present_in", to="@admin_units:state:adamawa", source="WPAD", evidence="single_reliable_source", level="reported",
                      notes="Wikipedia: 'the Jibu in the far south'. No LGA is named, and the Atlas places the Jibu language in Taraba State."))

GAPS = [
    ("Adamawa: Wikipedia's language table", "Wikipedia's table of languages by LGA cites the Adamawa State Planning Commission's state page, but the archived snapshots of that page (2022-2024) do not contain it. It is attributed to Wikipedia only."),
    ("Adamawa: Mafa, Waga, Bura, Waja", "Named by Wikipedia (Mafa in Madagali and Mubi South; Waga in the north; Bura-Pabir in Gombi; Waja in Numan), but the Atlas places none of these languages in Adamawa (Waga is a dialect name within Lamang Central). Not recorded."),
    ("Adamawa: Bali", "Wikipedia names the Bali among the peoples of central Adamawa and lists Bali in Demsa; the Atlas places the Bali language at Bali, south of Jalingo (Taraba). Not linked."),
    ("Adamawa: Bagale", "The state's festival table lists the Ngrah Lakiye festival of the 'Bagale' in Fufore LGA; no source identifies this group. Not recorded."),
    ("Adamawa: Yola festivals", "Five Fulani festivals and three Bwatiye (Bachama) festivals are listed for 'Yola', which may mean Yola North or Yola South; they support state links only."),
    ("Adamawa: Kaan and the Libbo", "The Atlas (Guyuk), the state's festival table (Libbo, Shelleng) and Wikipedia (Numan) place the Kaan in three different LGAs; the Libbo are identified with them by name only. All links 'reported'."),
    ("Adamawa: smaller language communities", "About twenty Atlas languages in Adamawa have no people record because no source names the people: Dadiya, Dijim–Bwilim, Kumba, Teme, Kugama-Gengle, Yoti, Kpasam, Nyong, Gaa, Kofa, Holma, Mukta, Daba, Zizilivəkan, Boga, Tha, Joole and Dza, and the Michika languages Lamang, Hdi, Gvoko, Vemgo–Mabas and Kirya-Konzəl."),
    ("Adamawa: who is indigenous where", "All LGA links are presence only (settlement status unknown)."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=[],
                scope=f"Adamawa (Phase 3): {len(PEOPLE)} peoples (state festival and tourist tables, Wikipedia, checked against Blench's Atlas), LGA links, language links; Fulani, Chamba, Mumuye, Yandang and Jibu links.")


def report():
    lga_people = {l: [] for l in LGA}
    for s, p in PEOPLE.items():
        for l in by_lga(p[4]):
            lga_people[l].append(p[0])
    for s, (_, _, evs) in EXISTING.items():
        for l in by_lga(evs):
            lga_people[l].append(s.title() + " (existing)")
    lg = [r for r in RELATIONS if r["type"] == "present_in" and "lga:" in r["to"]]
    L = ["# Research batch 057b — Adamawa: the peoples", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "## Sources", "",
         "- **Adamawa State Planning Commission (official).** Its state page, as archived on 29 April 2023, has two useful tables:",
         "  - **43 'popular festivals'**, each with its ethnic group and LGA. Today's page keeps only the first 10.",
         "  - **Tourist sites**: the Elephant House of the Lunguda (Guyuk), the Koma Hills (Jada) and the Sukur Kingdom (Madagali).",
         "- **Wikipedia, 'Adamawa State'**: the peoples it names, and a table of languages by LGA.",
         "  - The table cites the Planning Commission, but I could not find it in any archived copy of that page, so it counts as Wikipedia only.",
         "- **Blench's Atlas** (batch 057) for the languages and the peoples' own names.", "",
         "## What it adds", "",
         f"- **{len(RECORDS)} new people records:** " + ", ".join(p[0] for p in PEOPLE.values()) + ". Each is linked to Adamawa and to its language.",
         f"- **{len(lg)} people–LGA links, covering all 21 LGAs:** {sum(1 for r in lg if r['level'] == 'well_documented')} *well documented* and {sum(1 for r in lg if r['level'] == 'reported')} *reported*.",
         "  - A link is *well documented* when it is named in the state's table, or when Wikipedia names it and the Atlas agrees.",
         "  - All links are presence only.",
         "- **Existing records get Adamawa links:** Fulani, Chamba, Mumuye and Yandang (to state and LGAs), and Jibu (state only, *reported*).",
         "- **Not recorded, listed as gaps:** Mafa, Waga, Bura and Waja, whose languages the Atlas does not place in Adamawa; Bali, which is in Taraba; and the 'Bagale', whom no source identifies.",
         "- All the new records are short, so they are noindex and the sitemap is unchanged.", "",
         "## People by LGA", "", "| LGA | Peoples linked |", "|---|---|"]
    for l, ps in lga_people.items():
        L.append(f"| {LGA[l]} | {', '.join(ps) or '—'} |")
    L += ["", "## The people records", ""] + [f"**{PEOPLE[s][0]}.** {text(s)}" + "\n" for s in PEOPLE] + \
         ["## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_057b_adamawa_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_057b_adamawa_peoples_REVIEW.md", "w").write(report())
    lg = [r for r in RELATIONS if r["type"] == "present_in" and "lga:" in r["to"]]
    print(f"records={len(RECORDS)} relations={len(RELATIONS)} (people-LGA={len(lg)}, well={sum(1 for r in lg if r['level'] == 'well_documented')}) names={len(NAMES)}")
