"""
Research batch 005 — the peoples and languages of Benue State other than the Tiv
(researched 2026-09-24). The Tiv are already recorded (batch 003).

Which peoples: every group named by at least one of
  GOV   Benue State Government, "About Benue State" (official site): Tiv, Idoma, Igede
        (major); Etulo, Abakwa, Jukun, Nyifon, Akweya, Ufia (other communities);
  MOFEP Benue State Ministry of Finance and Economic Planning, "Explore Benue":
        Tiv, Idoma, Igede, Etulo, Abakpa, Jukun, Hausa, Akweya, Nyifon;
  WBS   Wikipedia, "Benue State": the above plus Igbo and "Ufia Orring".
So: Idoma, Igede, Etulo, Akweya, Nyifon, Ufia, Jukun, Hausa, Igbo, Abakpa (10 new).

Where: LGA-level links are recorded only with the LGA names in use today. Evidence rule:
  * a government source or Blench's Atlas (scholarly) plus another source -> 'multiple_sources';
  * the Atlas alone -> 'single_reliable_source';
  * Wikipedia alone (including the Ethnologue LGA table it quotes, which we could not
    check at origin) -> 'needs_corroboration', shown as such on the page;
  * the Atlas sometimes names LGAs as they were before later LGA creations (e.g.
    'Okpokwu', 'Gboko'); where no current-era source agrees, the LGA link is NOT
    recorded — it is listed as a research gap instead.
Summaries use facts with two or more sources, in our own words; single-source facts
are attributed in the text.
"""
import json, sys, re

ACCESSED = "2026-09-24"
WAYBACK_ATLAS = "https://web.archive.org/web/20250528003305/https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf"
SOURCES = {
    # Reused (already in the archive, same URL).
    "GOV": dict(source_type="official_website", title="About Benue State", organisation="Benue State Government",
                url="https://benuestate.gov.ng/about/", verification_status="needs_corroboration", notes="Reused from batch 004."),
    "MOFEP": dict(source_type="official_website", title="History Of Benue State", organisation="Benue State Ministry of Finance and Economic Planning",
                  url="https://www.mofep.be.gov.ng/explore_benue", verification_status="verified", notes="Reused (existing source)."),
    "WBS": dict(source_type="encyclopedia", title="Benue State", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Benue_State",
                verification_status="needs_corroboration", notes="Reused (existing source)."),
    # New.
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", author="Roger Blench",
                  organisation="Kay Williamson Educational Foundation / McDonald Institute for Archaeological Research, University of Cambridge",
                  publication_date="2020", publication_details="Version of 11 September 2020 (the running header reads 'Edition IV. 2019').",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf",
                  archive_reference=WAYBACK_ATLAS, verification_status="needs_corroboration",
                  notes="Scholarly reference listing each Nigerian language with its names, state and LGA locations, older speaker counts and classification. The author's site is no longer online; consulted through the Internet Archive copy. Some LGA names predate later LGA creations."),
    "GLOT": dict(source_type="dataset", title="Glottolog 5.3 — languoid catalogue", organisation="Glottolog (Max Planck Institute for Evolutionary Anthropology)",
                 url="https://glottolog.org/", verification_status="needs_corroboration",
                 notes="Classification and codes consulted: Idomoid idom1262; Idoma idom1241 (idu); Igede iged1239 (ige); Etulo etul1245 (utr); Akpa akpa1238 (akf); Oring orin1239 (org), with Ufia ufia1239 as a dialect; Jukunoid juku1257; Wapan wapa1235 (juk), with Nyifon nyif1234 as a dialect; Wannu wann1241 (jub); Iyive iyiv1238 (uiv); Otank otan1238 (uta)."),
    "IAMB": dict(source_type="website", title="Ethnic Composition", organisation="I am Benue", url="https://www.iambenue.com/benue-state/ethnic-composition/",
                 verification_status="needs_corroboration",
                 notes="Community website registered in Nigeria (Makurdi). Lists the ethnic groups of Benue: Tiv, Idoma, Igede, Etulo, Abakpa, Jukun, Hausa, Akweya and Nyifon."),
    "WIDO": dict(source_type="encyclopedia", title="Idoma people", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Idoma_people", verification_status="needs_corroboration",
                 notes="Idoma mainly in the lower western areas of Benue State, south of the Benue River; also in Taraba, Cross River, Enugu, Kogi and Nasarawa; Och'Idoma heads the Idoma Area Traditional Council. Its 2006 census figure for the Idoma is not recorded (the census did not publish ethnicity)."),
    "WIGE": dict(source_type="encyclopedia", title="Igede people", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Igede_people", verification_status="needs_corroboration",
                 notes="Igede native to Oju and Obi LGAs; language also spoken in Cross River State."),
    "WETU": dict(source_type="encyclopedia", title="Etulo language", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Etulo_language", verification_status="needs_corroboration",
                 notes="Spoken in Benue and Taraba states; most Etulo speakers also speak Tiv."),
    "WAKP": dict(source_type="encyclopedia", title="Akpa language", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Akpa_language", verification_status="needs_corroboration",
                 notes="Akpa (Akweya), an Idomoid language of Ohimini and Oturkpo LGAs."),
    "WNYI": dict(source_type="encyclopedia", title="Nyifon language", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Nyifon_language", verification_status="needs_corroboration",
                 notes="Nyifon (Iordaa), a poorly known Jukunoid language of Buruku LGA; perhaps 1,000 speakers in the 1990s; Glottolog lists it as a dialect of Wapan."),
    "WKOR": dict(source_type="encyclopedia", title="Korring", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Korring", verification_status="needs_corroboration",
                 notes="Orring (Korring), an Upper Cross River language of the Orring people of Benue, Cross River and Ebonyi states; the Ufia (Utonkon) of Benue speak K'ufia."),
    "WJUK": dict(source_type="encyclopedia", title="Jukun people (West Africa)", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Jukun_people_(West_Africa)", verification_status="needs_corroboration",
                 notes="Jukun traditionally in Taraba, Benue, Nasarawa, Plateau, Adamawa, Bauchi and Gombe states and north-western Cameroon; the Jukun of Wukari call themselves Wapa; the Jukun Wanu of Abinsi."),
    "WWAN": dict(source_type="encyclopedia", title="Wannu language", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Wannu_language", verification_status="needs_corroboration",
                 notes="Wannu, or Abinsi after the district where it is spoken, a Jukunoid language of Benue State."),
    "WOTA": dict(source_type="encyclopedia", title="Otank language", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Otank_language", verification_status="needs_corroboration",
                 notes="Otank (Utanga), a Tivoid language of the Utanga, described as a Tiv group, in Benue (Ushongo LGA), Cross River (Obanliku) and Cameroon."),
    "WIYI": dict(source_type="encyclopedia", title="Iyive language", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Iyive_language", verification_status="needs_corroboration",
                 notes="Iyive (Uive, Yiive, Ndir, Asumbo), a severely endangered Tivoid language of Nigeria and Cameroon."),
    "WHAU": dict(source_type="encyclopedia", title="Hausa people", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Hausa_people", verification_status="needs_corroboration",
                 notes="Hausa based mainly in southern Niger and northern Nigeria; speak the Hausa language."),
    "WIGB": dict(source_type="encyclopedia", title="Igbo people", organisation="Wikipedia", url="https://en.wikipedia.org/wiki/Igbo_people", verification_status="needs_corroboration",
                 notes="Igbo homeland in Abia, Anambra, Ebonyi, Enugu and Imo states."),
}

ETH_NOTE = "Wikipedia's Benue State article lists languages by LGA from Ethnologue (22nd edition); not checked at origin."


def G(key, name, evidence, summary, srcs, **fields):
    return dict(key=key, table="ethnic_groups", evidence=evidence, fields=dict(name=name, slug=slugify(name), summary=summary, **fields), srcs=srcs)


def L(key, name, evidence, summary, srcs, lang_type="language", **fields):
    return dict(key=key, table="languages", evidence=evidence, fields=dict(lang_type=lang_type, name=name, slug=slugify(name), summary=summary, **fields), srcs=srcs)


def slugify(s):
    return re.sub(r"[^a-z0-9]+", "-", s.lower()).strip("-")


RECORDS = [
    # ── Language groups ────────────────────────────────────────────
    L("idomoid", "Idomoid", "multiple_sources",
      "Idomoid is a group of languages within the Benue–Congo branch of Niger–Congo. It includes Idoma, Igede, Etulo and Akpa, the main languages of southern Benue State.",
      [("GLOT", "Idomoid (idom1262) within Benue-Congo, containing Idoma, Igede, Etulo and Akpa"), ("ATLAS", "Idoma, Igede, Etulo and Akpa classified Benue-Congo: Idomoid")],
      lang_type="branch"),
    L("jukunoid", "Jukunoid", "multiple_sources",
      "Jukunoid is a group of languages within the Benue–Congo branch of Niger–Congo, named after the Jukun. In Benue State it includes Wannu, spoken at Abinsi, and probably Nyifon.",
      [("GLOT", "Jukunoid (juku1257) within Benue-Congo; Wannu and Nyifon (as a dialect of Wapan) within it"), ("ATLAS", "Jukun and Kororofa clusters: Benue-Congo: Jukunoid; Nyifon 'said to be Jukunoid'")],
      lang_type="branch"),
    # ── Languages ──────────────────────────────────────────────────
    L("idoma_l", "Idoma", "multiple_sources",
      "Idoma is the language of the Idoma people of southern Benue State, also spoken in Nasarawa State. It is an Idomoid language of the Benue–Congo family; its ISO 639-3 code is idu.",
      [("GLOT", "Idoma idom1241, ISO idu, Idomoid"), ("ATLAS", "Idoma cluster: Benue State, Otukpo and Okpokwu LGAs; Nasarawa State, Nassarawa and Awe LGAs"),
       ("WIDO", "Language of the Idoma, classified Idomoid")],
      parent_id="@key:idomoid", iso639_3="idu", glottocode="idom1241",
      description=("Roger Blench's Atlas of Nigerian Languages treats Idoma as a cluster of closely related varieties. It lists Agatu (also called Idoma North), "
                   "Idoma Central (Otukpo), Idoma West and Idoma South (Igumale) among them. The Atlas records a New Testament in Idoma Central from 1970 and a complete Bible from 2014.")),
    L("igede_l", "Igede", "multiple_sources",
      "Igede is the language of the Igede people, spoken in Benue State, mainly in Oju LGA, and in Cross River State. It is an Idomoid language; its ISO 639-3 code is ige.",
      [("GLOT", "Igede iged1239, ISO ige, Idomoid"), ("ATLAS", "Igede: Benue State, Oju, Otukpo and Okpokwu LGAs; Cross River State, Ogoja LGA"),
       ("WIGE", "Igede language also spoken in Cross River State")],
      parent_id="@key:idomoid", iso639_3="ige", glottocode="iged1239",
      description="Blench's Atlas of Nigerian Languages lists three dialects: Oju (central), Ito and Worku, plus Gabu in Ogoja LGA of Cross River State."),
    L("etulo_l", "Etulo", "multiple_sources",
      "Etulo is the language of the Etulo people of Benue and Taraba states. It is an Idomoid language, closely related to Idoma; its ISO 639-3 code is utr.",
      [("GLOT", "Etulo etul1245, ISO utr, in the Etulo-Idoma subgroup of Idomoid"), ("ATLAS", "Etulo: Benue State and Taraba State (Wukari LGA)"),
       ("WETU", "Spoken in Benue and Taraba states")],
      parent_id="@key:idomoid", iso639_3="utr", glottocode="etul1245",
      description="Wikipedia notes that most Etulo speakers also speak Tiv, the regional lingua franca."),
    L("akpa_l", "Akpa", "multiple_sources",
      "Akpa, also called Akweya, is the language of the Akweya people around Otukpo in Benue State. It is an Idomoid language; its ISO 639-3 code is akf.",
      [("GLOT", "Akpa akpa1238, ISO akf, Idomoid (Yatye-Akpa)"), ("ATLAS", "Akpa, also Akweya: Benue State, Otukpo LGA"), ("WAKP", "Akpa (Akweya), Idomoid, Ohimini and Oturkpo LGAs")],
      parent_id="@key:idomoid", iso639_3="akf", glottocode="akpa1238"),
    L("oring_l", "Oring", "multiple_sources",
      "Oring (Korring) is a cluster of Upper Cross River languages spoken in Benue and Ebonyi states. In Benue it is represented by Ufia, the speech of the Ufia (Utonkon) people. Its ISO 639-3 code is org.",
      [("GLOT", "Oring orin1239, ISO org, Upper Cross; Ufia listed as a dialect"), ("ATLAS", "Oring cluster (incl. Ufia, Ufiom and Okpoto): Benue State; Ufia = Utonkon"),
       ("WKOR", "Orring (Korring) spoken in Benue, Cross River and Ebonyi; the Ufia (Utonkon) of Benue speak K'ufia")],
      iso639_3="org", glottocode="orin1239"),
    L("nyifon_l", "Nyifon", "multiple_sources",
      "Nyifon, also called Iordaa, is the little-documented language of the Nyifon people of Buruku LGA, Benue State. It is thought to be Jukunoid.",
      [("ATLAS", "Nyifon (Iordaa): Buruku LGA, Benue State; said to be Jukunoid"), ("WNYI", "Poorly known Jukunoid language of Buruku LGA"),
       ("GLOT", "Nyifon nyif1234 listed as a dialect of Wapan (Jukunoid)")],
      parent_id="@key:jukunoid", glottocode="nyif1234",
      description=("Sources disagree on its status. Glottolog lists Nyifon as a dialect of Wapan, a Jukun language. Blench's Atlas gives no classification data beyond 'said to be Jukunoid', "
                   "and Wikipedia notes that Ethnologue does not list it.")),
    L("wannu_l", "Wannu", "multiple_sources",
      "Wannu, also called Abinsi after the place where it is spoken, is the Jukunoid language of the Jukun community at Abinsi in Benue State. Its ISO 639-3 code is jub.",
      [("GLOT", "Wannu wann1241, ISO jub, Jukunoid"), ("ATLAS", "Abinsi (Wapan, River Jukun): Benue State, at Abinsi"), ("WWAN", "Wannu or Abinsi, Jukunoid, Benue State")],
      parent_id="@key:jukunoid", iso639_3="jub", glottocode="wann1241",
      description=("Its place within Jukunoid is classified differently. Blench's Atlas and Wikipedia put it in the Jukun Wapan (Kororofa) cluster. "
                   "Glottolog puts it in a separate Wurbo–Wannu group.")),
    L("iyive_l", "Iyive", "multiple_sources",
      "Iyive (Uive, Yiive) is a small Tivoid language spoken near Turan in Kwande LGA of Benue State and in Cameroon. Its ISO 639-3 code is uiv.",
      [("ATLAS", "Iyive: Benue State, Kwande LGA, near Turan; and in Cameroon; Tivoid"), ("GLOT", "Iyive iyiv1238, ISO uiv, Tivoid"),
       ("WIYI", "Tivoid language of Nigeria and Cameroon")],
      parent_id="@languages:tivoid", iso639_3="uiv", glottocode="iyiv1238",
      description="Wikipedia describes it as severely endangered and notes that many of its Cameroonian speakers had moved to Nigeria because of a conflict."),
    L("otank_l", "Otank", "multiple_sources",
      "Otank (Utanga) is a Tivoid language closely related to Tiv, spoken on the Benue–Cross River border and in Cameroon. Its ISO 639-3 code is uta.",
      [("ATLAS", "Otank (Utanga): Cross River State, Obudu LGA; Benue State, Kwande LGA; Tivoid"), ("GLOT", "Otank otan1238, ISO uta, Tivoid (Tiv-Iyive-Otanga)"),
       ("WOTA", "Tivoid language of the Utanga in Nigeria and Cameroon")],
      parent_id="@languages:tivoid", iso639_3="uta", glottocode="otan1238",
      description=("Wikipedia describes its speakers, the Utanga, as a Tiv group living in Ushongo LGA of Benue State, Obanliku LGA of Cross River State and Cameroon. "
                   "Blench's Atlas places them in Kwande LGA of Benue State and Obudu LGA of Cross River State.")),
    L("basa_makurdi_l", "Basa-Makurdi", "single_reliable_source",
      "Basa-Makurdi is a little-documented language spoken in several villages on the north bank of the Benue River, north-west of Makurdi, according to Roger Blench's Atlas of Nigerian Languages.",
      [("ATLAS", "Basa-Makurdi: Benue State, Makurdi LGA, several villages on the north bank of the Benue, northwest of Makurdi; no data on classification")]),
    # ── Peoples ────────────────────────────────────────────────────
    G("idoma", "Idoma", "multiple_sources",
      "The Idoma are one of the three major peoples of Benue State, living mainly in the south of the state, south of the Benue River. They speak Idoma, an Idomoid language.",
      [("GOV", "Tiv, Idoma and Igede the three major ethnic groups; Idoma predominantly in the southern region"), ("MOFEP", "Idoma and Igede occupy the nine LGAs outside the Tiv area"),
       ("WIDO", "Idoma in the lower western areas of Benue State, south of the Benue River"), ("WBS", "Idoma among the main peoples of Benue State"),
       ("ATLAS", "Idoma cluster in Benue and Nasarawa states")],
      description=("The Benue State Government names the Tiv, Idoma and Igede as the state's three major peoples, and places the Idoma mainly in the south. "
                   "The state's Ministry of Finance and Economic Planning describes the nine LGAs outside the Tiv-speaking area as the Idoma–Igede area.\n\n"
                   "Roger Blench's Atlas of Nigerian Languages places the Idoma language in Otukpo and Okpokwu LGAs of Benue State and in Nassarawa and Awe LGAs of Nasarawa State. "
                   "Wikipedia also mentions Idoma communities in Taraba, Cross River, Enugu and Kogi states.\n\n"
                   "The LGAs are listed under Connections, each with its own evidence level."),
      traditional_governance=("The Och'Idoma is the paramount ruler of the Idoma. He chairs the Idoma Area Traditional Council, one of the two area traditional councils of Benue State "
                              "(Benue State Government; Benue State Ministry of Finance and Economic Planning). Wikipedia notes that the office was introduced under British colonial rule.")),
    G("igede", "Igede", "multiple_sources",
      "The Igede are one of the three major peoples of Benue State, with their homeland centred on Oju LGA. They speak Igede, an Idomoid language also spoken in Cross River State.",
      [("GOV", "Igede one of the three major ethnic groups; Ochi'Igede their paramount ruler"), ("MOFEP", "Igede listed; Idoma–Igede area of nine LGAs"),
       ("WIGE", "Igede native to Oju and Obi LGAs"), ("ATLAS", "Igede language in Oju LGA and in Cross River State")],
      description=("The Benue State Government counts the Igede among the state's three major peoples, with the Tiv and Idoma. Wikipedia describes them as native to Oju and Obi LGAs. "
                   "Blench's Atlas also lists Igede speakers in Ogoja LGA of Cross River State."),
      traditional_governance="The Benue State Government names the Ochi'Igede as the paramount ruler of the Igede."),
    G("etulo", "Etulo", "multiple_sources",
      "The Etulo are one of the smaller peoples of Benue State, with communities also in Taraba State. They speak Etulo, an Idomoid language related to Idoma.",
      [("GOV", "Etulo among the other ethnic communities of Benue State"), ("MOFEP", "Etulo listed"), ("WBS", "Etulo small communities in Katsina-Ala and Buruku"),
       ("ATLAS", "Etulo language in Benue State and in Wukari LGA, Taraba State"), ("WETU", "Etulo spoken in Benue and Taraba states")]),
    G("akweya", "Akweya", "multiple_sources",
      "The Akweya are one of the smaller peoples of Benue State, living around Otukpo in the Idoma area. Their language, Akpa (also called Akweya), is Idomoid.",
      [("GOV", "Akweya among the other ethnic communities"), ("MOFEP", "Akweya listed"), ("ATLAS", "Akpa (Akweya): Benue State, Otukpo LGA"),
       ("WBS", "Akweya have a few communities in the southern senatorial zone")]),
    G("nyifon", "Nyifon", "multiple_sources",
      "The Nyifon are a small people of Buruku LGA, Benue State. Their little-documented language, also called Iordaa, is thought to be Jukunoid.",
      [("GOV", "Nyifon among the other ethnic communities"), ("MOFEP", "Nyifon listed"), ("WBS", "Nyifon small community in Buruku"),
       ("ATLAS", "Nyifon (Iordaa): Buruku LGA")]),
    G("ufia", "Ufia", "multiple_sources",
      "The Ufia, also called Utonkon, are one of the smaller peoples of Benue State and part of the wider Orring people. They speak Ufia, a variety of the Oring (Korring) language cluster of the Upper Cross River group.",
      [("GOV", "Ufia among the other ethnic communities"), ("WBS", "'Ufia Orring' among the peoples of Benue; Orring communities in the southern zone"),
       ("ATLAS", "Ufia = Utonkon, member of the Oring cluster, Benue State"), ("WKOR", "The Ufia (Utonkon) of Benue speak K'ufia")]),
    G("jukun", "Jukun", "multiple_sources",
      "The Jukun are a people of the middle Benue valley, living mainly in Taraba State. In Benue State there is a Jukun community by the river at Abinsi, whose language is Wannu.",
      [("WJUK", "Jukun in Taraba, Benue, Nasarawa and other states; the Jukun Wanu of Abinsi"), ("GOV", "Jukun among the other ethnic communities"), ("MOFEP", "Jukun listed"),
       ("WBS", "Jukun community along the river bank at Abinsi"), ("ATLAS", "Jukun cluster in Taraba, Nasarawa and Benue; Abinsi (River Jukun) at Abinsi")],
      description=("Wikipedia names Taraba, Benue, Nasarawa, Plateau, Adamawa, Bauchi and Gombe states and north-western Cameroon as the Jukun's traditional areas, "
                   "and links them with the historical kingdom of Kwararafa. It notes that the Jukun of Wukari call themselves Wapa. "
                   "Blench's Atlas uses the name Jukun for two related language clusters, Jukun and Kororofa. It lists the speech of Abinsi (River Jukun) in the Kororofa cluster.")),
    G("hausa", "Hausa", "multiple_sources",
      "The Hausa are a people native to northern Nigeria and southern Niger who speak the Hausa language. In Benue State they are a minority, with communities in Makurdi and some other towns.",
      [("WHAU", "Hausa based mainly in northern Nigeria and southern Niger; speak Hausa"), ("MOFEP", "Hausa among the ethnic groups of Benue State"),
       ("WBS", "Hausa a minority group; communities in Makurdi city, Katsina-Ala and Zaki Biam"), ("IAMB", "Hausa listed among the ethnic groups")],
      description=("The Benue State Ministry of Finance and Economic Planning lists the Hausa among the state's peoples. The state government's own list does not include them. "
                   "According to Wikipedia, there are two large Hausa communities in Makurdi city, in the Wadata and North Bank neighbourhoods, and small ones at Katsina-Ala and Zaki Biam.")),
    G("igbo", "Igbo", "multiple_sources",
      "The Igbo are a people whose homeland is in south-eastern Nigeria. In Benue State there are Igbo communities in the southern LGAs near the Enugu and Ebonyi borders.",
      [("WIGB", "Igbo homeland in Abia, Anambra, Ebonyi, Enugu and Imo states"), ("WBS", "Igbo have a few communities in LGAs bordering Enugu State"),
       ("ATLAS", "Izi (Igboid) also in Benue State, Okpokwu LGA")],
      description=("Wikipedia's Benue State article lists the Igbo among the state's peoples. The two Benue State Government lists do not. "
                   "Blench's Atlas records Izi, an Igboid language, in Benue State as well as in Ebonyi.")),
    G("abakpa", "Abakpa", "needs_corroboration",
      "Abakpa (also spelt Abakwa) is named as one of the peoples of Benue State by the state government and other sources. The archive has not yet found a source that describes who they are or where they live.",
      [("MOFEP", "Abakpa among the ethnic groups of Benue State"), ("GOV", "'Abakwa' among the other ethnic communities"),
       ("IAMB", "Abakpa listed"), ("WBS", "Abakpa listed")]),
]

NAMES = [
    dict(record="idoma", name="Akpoto", name_type="alternative", srcs=["ATLAS"], usage_notes="Listed by Blench's Atlas as another name for Idoma (Idoma Central)."),
    dict(record="igede", name="Egede", name_type="spelling_variant", srcs=["ATLAS"], usage_notes="Also Igedde, Egedde (Blench's Atlas)."),
    dict(record="igede_l", name="Igedde", name_type="spelling_variant", srcs=["ATLAS"], usage_notes="Also Egede, Egedde (Blench's Atlas)."),
    dict(record="etulo", name="Utur", name_type="alternative", srcs=["ATLAS"], usage_notes="Also Eturo (Blench's Atlas)."),
    dict(record="etulo", name="Turumawa", name_type="exonym", srcs=["ATLAS"], usage_notes="Name used by others, listed by Blench's Atlas."),
    dict(record="akpa_l", name="Akweya", name_type="alternative", srcs=["ATLAS"], usage_notes="Name of the people, also used for the language (Blench's Atlas; Wikipedia)."),
    dict(record="nyifon", name="Iordaa", name_type="alternative", srcs=["ATLAS"], usage_notes="Given by Blench's Atlas and Wikipedia as another name for Nyifon."),
    dict(record="nyifon_l", name="Iordaa", name_type="alternative", srcs=["ATLAS"], usage_notes="Given by Blench's Atlas and Wikipedia."),
    dict(record="ufia", name="Utonkon", name_type="alternative", srcs=["ATLAS"], usage_notes="Blench's Atlas: Ufia = Utonkon; also in Wikipedia (Korring)."),
    dict(record="ufia", name="Ufia Orring", name_type="other", srcs=["WBS"], usage_notes="Form used in Wikipedia's list of the peoples of Benue State."),
    dict(record="oring_l", name="Korring", name_type="alternative", srcs=["WKOR"], usage_notes="Also Koring, Orri (Blench's Atlas)."),
    dict(record="jukun", name="Njuku", name_type="alternative", srcs=["ATLAS"], usage_notes="Given by Blench's Atlas."),
    dict(record="jukun", name="Wapa", name_type="endonym", srcs=["WJUK"], usage_notes="Self-name of the Jukun of Wukari, Ibi, Dampar and Wase (Wikipedia)."),
    dict(record="wannu_l", name="Abinsi", name_type="alternative", srcs=["WWAN"], usage_notes="After the place where it is spoken (Wikipedia; Blench's Atlas)."),
    dict(record="wannu_l", name="River Jukun", name_type="alternative", srcs=["ATLAS"], usage_notes="Blench's Atlas."),
    dict(record="iyive_l", name="Uive", name_type="alternative", srcs=["ATLAS"], usage_notes="Also Yiive, Ndir; Asumbo in Cameroon (Blench's Atlas; Wikipedia)."),
    dict(record="otank_l", name="Utanga", name_type="alternative", srcs=["ATLAS"], usage_notes="Also Otanga (Blench's Atlas; Wikipedia)."),
    dict(record="abakpa", name="Abakwa", name_type="spelling_variant", srcs=["GOV"], usage_notes="Spelling on the Benue State Government website; its Ministry of Finance and Economic Planning writes Abakpa."),
    dict(record="igbo", name="Ibo", name_type="spelling_variant", srcs=["WIGB"], usage_notes="Older spelling (Wikipedia)."),
]

BENUE = "@admin_units:state:benue"
LGA = lambda s: f"@admin_units:lga:{s}"
ST = lambda s: f"@admin_units:state:{s}"
RELATIONS = []


def rel(frm, typ, to, source, evidence, notes):
    RELATIONS.append(dict(frm=frm, type=typ, to=to, source=source, evidence=evidence, notes=notes))


# People speak their language.
for g, l, n in [("idoma", "idoma_l", "Blench's Atlas; Wikipedia (Idoma people)."), ("igede", "igede_l", "Blench's Atlas; Wikipedia (Igede people)."),
                ("etulo", "etulo_l", "Blench's Atlas; Wikipedia (Etulo language)."), ("akweya", "akpa_l", "Blench's Atlas (Akpa, also called Akweya); Wikipedia (Akpa language)."),
                ("ufia", "oring_l", "The Ufia speak K'ufia, an Oring-cluster variety (Blench's Atlas; Wikipedia, Korring)."),
                ("nyifon", "nyifon_l", "Blench's Atlas; Wikipedia (Nyifon language)."),
                ("jukun", "wannu_l", "Only the Jukun of Abinsi: their speech is Wannu/Abinsi (Blench's Atlas; Wikipedia, Wannu language). Other Jukun speak other Jukunoid languages.")]:
    rel(g, "speaks", l, "ATLAS", "multiple_sources", n)

# Presence in Benue State.
PRES = {
    "idoma": ("GOV", "multiple_sources", "Benue State Government; Ministry of Finance and Economic Planning; Wikipedia; Blench's Atlas."),
    "igede": ("GOV", "multiple_sources", "Benue State Government; Ministry of Finance and Economic Planning; Wikipedia; Blench's Atlas."),
    "etulo": ("GOV", "multiple_sources", "Benue State Government; Ministry of Finance and Economic Planning; Wikipedia; Blench's Atlas."),
    "akweya": ("GOV", "multiple_sources", "Benue State Government; Ministry of Finance and Economic Planning; Wikipedia; Blench's Atlas."),
    "nyifon": ("GOV", "multiple_sources", "Benue State Government; Ministry of Finance and Economic Planning; Wikipedia; Blench's Atlas."),
    "ufia": ("GOV", "multiple_sources", "Benue State Government; Wikipedia ('Ufia Orring'); Blench's Atlas."),
    "jukun": ("GOV", "multiple_sources", "Benue State Government; Ministry of Finance and Economic Planning; Wikipedia; Blench's Atlas."),
    "hausa": ("MOFEP", "multiple_sources", "Benue State Ministry of Finance and Economic Planning; Wikipedia; I am Benue. Not in the state government's own list."),
    "igbo": ("WBS", "multiple_sources", "Wikipedia (Benue State); Blench's Atlas (Izi, an Igboid language, in Benue State). Not in the Benue State Government lists."),
    "abakpa": ("MOFEP", "multiple_sources", "Named by the Ministry of Finance and Economic Planning (Abakpa), the state government (Abakwa), Wikipedia and I am Benue. Where they live is not documented."),
}
for g, (s, e, n) in PRES.items():
    rel(g, "present_in", BENUE, s, e, n)

# Presence in other states (two sources each).
rel("idoma", "present_in", ST("nasarawa"), "ATLAS", "multiple_sources", "Blench's Atlas: Nassarawa and Awe LGAs; Wikipedia (Idoma people) also names Nasarawa.")
rel("igede", "present_in", ST("cross-river"), "ATLAS", "multiple_sources", "Blench's Atlas: Ogoja LGA; Wikipedia (Igede people): the language is also spoken in Cross River State.")
rel("etulo", "present_in", ST("taraba"), "ATLAS", "multiple_sources", "Blench's Atlas: Wukari LGA; Wikipedia (Etulo language): Taraba State.")
rel("jukun", "present_in", ST("taraba"), "ATLAS", "multiple_sources", "Blench's Atlas: Wukari, Takum, Bali and Sardauna LGAs; Wikipedia (Jukun people).")
rel("jukun", "present_in", ST("nasarawa"), "ATLAS", "multiple_sources", "Blench's Atlas: Awe and Lafia LGAs; Wikipedia (Jukun people).")

# Presence in Benue LGAs.
BOTH = "Blench's Atlas and the Ethnologue LGA list quoted by Wikipedia (Benue State) agree."
for l in ["oturkpo", "okpokwu"]:
    rel("idoma", "present_in", LGA(l), "ATLAS", "multiple_sources", BOTH)
for l in ["agatu", "apa", "ado", "ogbadibo", "ohimini", "obi"]:
    rel("idoma", "present_in", LGA(l), "WBS", "needs_corroboration", ETH_NOTE)
rel("igede", "present_in", LGA("oju"), "ATLAS", "multiple_sources", "Blench's Atlas; Wikipedia (Igede people); Ethnologue LGA list quoted by Wikipedia.")
rel("igede", "present_in", LGA("obi"), "WIGE", "needs_corroboration", "Wikipedia (Igede people) and the Ethnologue LGA list quoted by Wikipedia (Benue State).")
rel("akweya", "present_in", LGA("oturkpo"), "ATLAS", "multiple_sources", BOTH)
rel("akweya", "present_in", LGA("ohimini"), "WAKP", "needs_corroboration", "Wikipedia (Akpa language) only.")
rel("etulo", "present_in", LGA("buruku"), "WBS", "needs_corroboration", "Wikipedia (Benue State) only. Blench's Atlas gives Gboko LGA.")
rel("etulo", "present_in", LGA("katsina-ala"), "WBS", "needs_corroboration", "Wikipedia (Benue State) only.")
rel("nyifon", "present_in", LGA("buruku"), "ATLAS", "multiple_sources", "Blench's Atlas; Wikipedia (Benue State; Nyifon language).")
rel("ufia", "present_in", LGA("ado"), "WBS", "needs_corroboration", ETH_NOTE + " Blench's Atlas gives Okpokwu LGA.")
for l in ["makurdi", "katsina-ala", "ukum"]:
    rel("hausa", "present_in", LGA(l), "WBS", "needs_corroboration", "Wikipedia (Benue State) only" + (" (Zaki Biam)." if l == "ukum" else "."))
for l in ["ado", "obi", "oju"]:
    rel("igbo", "present_in", LGA(l), "WBS", "needs_corroboration", ETH_NOTE)

# Where the languages are spoken.
LANG_BENUE = {
    "idoma_l": ("ATLAS", "multiple_sources", "Blench's Atlas; Wikipedia; Glottolog."),
    "igede_l": ("ATLAS", "multiple_sources", "Blench's Atlas; Wikipedia."),
    "etulo_l": ("ATLAS", "multiple_sources", "Blench's Atlas; Wikipedia."),
    "akpa_l": ("ATLAS", "multiple_sources", "Blench's Atlas; Wikipedia."),
    "oring_l": ("ATLAS", "multiple_sources", "Blench's Atlas; Wikipedia (Korring)."),
    "nyifon_l": ("ATLAS", "multiple_sources", "Blench's Atlas; Wikipedia."),
    "wannu_l": ("ATLAS", "multiple_sources", "Blench's Atlas; Wikipedia."),
    "iyive_l": ("ATLAS", "single_reliable_source", "Blench's Atlas (Kwande LGA, near Turan). Wikipedia says Nigeria without naming the state."),
    "otank_l": ("ATLAS", "multiple_sources", "Blench's Atlas (Kwande LGA); Wikipedia (Ushongo LGA)."),
    "basa_makurdi_l": ("ATLAS", "single_reliable_source", "Blench's Atlas only."),
}
for l, (s, e, n) in LANG_BENUE.items():
    rel(l, "spoken_in", BENUE, s, e, n)
rel("idoma_l", "spoken_in", ST("nasarawa"), "ATLAS", "multiple_sources", "Blench's Atlas: Nassarawa and Awe LGAs; Wikipedia (Idoma people).")
rel("igede_l", "spoken_in", ST("cross-river"), "ATLAS", "multiple_sources", "Blench's Atlas: Ogoja LGA; Wikipedia (Igede people; Igede language).")
rel("etulo_l", "spoken_in", ST("taraba"), "ATLAS", "multiple_sources", "Blench's Atlas: Wukari LGA; Wikipedia (Etulo language).")
rel("oring_l", "spoken_in", ST("ebonyi"), "WKOR", "multiple_sources", "Wikipedia (Korring); Blench's Atlas gives Ishielu LGA under Anambra State, an area now in Ebonyi State.")
for l in ["oturkpo", "okpokwu"]:
    rel("idoma_l", "spoken_in", LGA(l), "ATLAS", "multiple_sources", BOTH)
rel("igede_l", "spoken_in", LGA("oju"), "ATLAS", "multiple_sources", "Blench's Atlas; Ethnologue LGA list quoted by Wikipedia.")
rel("akpa_l", "spoken_in", LGA("oturkpo"), "ATLAS", "multiple_sources", BOTH)
rel("nyifon_l", "spoken_in", LGA("buruku"), "ATLAS", "multiple_sources", "Blench's Atlas; Wikipedia.")
rel("iyive_l", "spoken_in", LGA("kwande"), "ATLAS", "single_reliable_source", "Blench's Atlas: near Turan.")
rel("basa_makurdi_l", "spoken_in", LGA("makurdi"), "ATLAS", "single_reliable_source", "Blench's Atlas: several villages on the north bank of the Benue, north-west of Makurdi.")

# Older speaker counts, as given by Blench's Atlas (each with the year and origin it gives).
STATISTICS = [
    dict(record="igede_l", metric="speakers", value_low=70000, reference_year=1952, method="other", source="ATLAS", evidence="single_reliable_source", notes="Blench's Atlas: 70,000 (1952, 'RGA')."),
    dict(record="igede_l", metric="speakers", value_low=120000, reference_year=1982, method="estimate", source="ATLAS", evidence="single_reliable_source", notes="Blench's Atlas: 120,000 (1982, UBS)."),
    dict(record="akpa_l", metric="speakers", value_low=5500, reference_year=1952, method="other", source="ATLAS", evidence="single_reliable_source", notes="Blench's Atlas: 5,500 (1952, 'RGA')."),
    dict(record="ufia", metric="speakers", value_low=12300, reference_year=1952, method="other", source="ATLAS", evidence="single_reliable_source", notes="Blench's Atlas, for Ufia (Utonkon): 12,300 (1952, 'RGA')."),
    dict(record="otank_l", metric="speakers", value_low=2000, reference_year=1953, method="estimate", source="ATLAS", evidence="single_reliable_source", notes="Blench's Atlas: 2,000 (1953, Bohannan); also 2,500 (SIL, undated)."),
    dict(record="nyifon_l", metric="speakers", value_low=1000, method="estimate", source="ATLAS", evidence="single_reliable_source", notes="Blench's Atlas: 1,000 (CAPRO, undated, 'probably 1990s'); Wikipedia gives the same figure."),
]

GAPS = [
    ("Who the Abakpa (Abakwa) of Benue are", "Named by the Benue State Government, its Finance Ministry, Wikipedia and I am Benue, but no source found describes them or where they live. Blench's Atlas uses 'Abakpa' for Ekin, an Ejagham variety of Cross River State; that is not assumed to be the same group."),
    ("Current population of each people", "Nigeria's census does not publish ethnicity. Wikipedia gives '1,307,647 Idoma' from the 2006 census; this could not be verified and is not recorded. Only the older speaker counts listed in Blench's Atlas are recorded, with their years."),
    ("LGAs named by Blench's Atlas under older boundaries", "The Atlas places Igede in Otukpo and Okpokwu LGAs, Ufia in Okpokwu, Etulo in Gboko, Izi (Igbo) in Okpokwu, and Otank in Kwande. Several LGAs have been created since then, so these links are not recorded until a current source confirms them."),
    ("Current LGA of the Jukun community at Abinsi", "Blench's Atlas and Wikipedia say Makurdi LGA. Abinsi's LGA under today's boundaries has not been confirmed from a source, so no LGA link is recorded."),
    ("Ethnologue's LGA table", "The LGA-level links marked 'needs corroboration' come from Wikipedia, including the Ethnologue (22nd edition) table it quotes. They need checking against Ethnologue or a Benue State source."),
    ("Hausa and Igbo language records", "The Hausa and Igbo languages are not yet national records. So these peoples are not yet linked to their languages."),
    ("Other states for these peoples", "Only presence confirmed by two sources is recorded. Wikipedia's further states (e.g. Idoma in Taraba, Cross River, Enugu and Kogi; Jukun in Plateau, Adamawa, Bauchi and Gombe) need checking."),
    ("Origins and history of each people", "Origin accounts (e.g. the Igede tradition of a migration from Ora, and the Idoma link with Kwararafa) are not recorded. They need a batch that separates oral tradition from scholarship."),
    ("Other small languages of Benue", "Wikipedia also names Utugwang–Irungene–Afrike, and Blench lists Ufiom (Effium) in Benue. These are not recorded here. Their Benue locations need confirming."),
]


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS,
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATISTICS,
                scope="The peoples of Benue State other than the Tiv, and their languages, linked to the states and LGAs where they are documented.")


def words(s):
    return len(re.findall(r"\w+", s or ""))


def report():
    peoples = [r for r in RECORDS if r["table"] == "ethnic_groups"]
    langs = [r for r in RECORDS if r["table"] == "languages"]
    ev = lambda e: e.replace("_", " ")
    L = ["# Research batch 005 — The peoples and languages of Benue State", "",
         f"Researched {ACCESSED}. Imported as *in review* only after approval. The Tiv are already recorded (batch 003).", "",
         "## Which peoples, and from which lists", "",
         "| People | Benue State Government | Finance Ministry | Wikipedia | Blench's Atlas (language) |", "|---|---|---|---|---|"]
    lists = {"Idoma": "✓✓✓✓", "Igede": "✓✓✓✓", "Etulo": "✓✓✓✓", "Akweya": "✓✓✓✓", "Nyifon": "✓✓✓✓", "Ufia": "✓–✓✓", "Jukun": "✓✓✓✓",
             "Hausa": "–✓✓–", "Igbo": "––✓✓", "Abakpa": "✓✓✓–"}
    for p, marks in lists.items():
        L.append(f"| {p} | " + " | ".join("yes" if m == "✓" else "—" for m in marks) + " |")
    L += ["", "The two government lists name nine or ten groups. Hausa appears only in the Finance Ministry list. Igbo appears only in Wikipedia and, as a language, in Blench's Atlas.", "",
          "## Sources", ""]
    for k, s in SOURCES.items():
        L.append(f"- **{k}** — {s['title']}{' — ' + s['author'] if s.get('author') else ''} ({s['organisation']}). {s['url']}. {s['notes']}")
    L += ["", "## Peoples", ""]
    for r in peoples:
        f = r["fields"]
        L += [f"### {f['name']} — evidence: {ev(r['evidence'])}", "", f"> {f['summary']}", ""]
        for fld in ("description", "traditional_governance"):
            if f.get(fld): L += [f"*{fld.replace('_', ' ').capitalize()}:* " + f[fld].replace("\n\n", " "), ""]
        L += ["Sources: " + "; ".join(f"{k} ({c})" for k, c in r["srcs"]), ""]
    L += ["## Languages", ""]
    for r in langs:
        f = r["fields"]
        par = f.get("parent_id", "")
        L += [f"### {f['name']} ({f['lang_type']}{', ISO ' + f['iso639_3'] if f.get('iso639_3') else ''}{', under ' + par.split(':')[-1] if par else ''}) — evidence: {ev(r['evidence'])}", "",
              f"> {f['summary']}", ""]
        if f.get("description"): L += [f["description"], ""]
        L += ["Sources: " + "; ".join(f"{k} ({c})" for k, c in r["srcs"]), ""]
    L += ["## Other names", ""] + [f"- **{n['name']}** ({n['name_type']}, for {n['record']}) — {n['usage_notes']}" for n in NAMES]
    L += ["", "## Relationships", "", "| From | Relationship | To | Evidence | Notes |", "|---|---|---|---|---|"]
    for r in RELATIONS:
        to = r["to"].split(":")[-1].replace("-", " ").title() + (" State" if ":state:" in r["to"] else " LGA" if ":lga:" in r["to"] else "")
        L.append(f"| {r['frm']} | {r['type']} | {to} | {ev(r['evidence'])} | {r['notes']} |")
    L += ["", "## Older speaker counts (as given by Blench's Atlas)", ""] + [f"- {s['record']}: {s['value_low']:,}{' (' + str(s['reference_year']) + ')' if s.get('reference_year') else ''} — {s['notes']}" for s in STATISTICS]
    L += ["", "## Not recorded — research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    total = sum(words(r["fields"].get("summary")) + words(r["fields"].get("description")) + words(r["fields"].get("traditional_governance")) for r in RECORDS)
    L += ["", "## What changes for visitors, the AI and Google", "",
          f"- {len(peoples)} new people pages and {len(langs)} new language pages under /nigeria/. All are short ({total} words of prose in total), so every one stays **noindex** and out of the sitemap, and the sitemap does not change.",
          "- The Benue State page (already indexed) gains these peoples and languages under Connections. Benue LGA pages gain them too.",
          "- The AI can answer \"What ethnic groups are in Benue State?\", \"Which languages are spoken in Oju?\" and \"Where do the Igede live?\". Each answer gives evidence levels and sources.",
          "- The sources created by this batch stay noindex, as before.", ""]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_005_benue_peoples.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_005_benue_peoples_REVIEW.md", "w").write(report())
    print(f"records={len(RECORDS)} (peoples={sum(r['table']=='ethnic_groups' for r in RECORDS)}, languages={sum(r['table']=='languages' for r in RECORDS)}) "
          f"names={len(NAMES)} relations={len(RELATIONS)} statistics={len(STATISTICS)} sources={len(SOURCES)} gaps={len(GAPS)}")
