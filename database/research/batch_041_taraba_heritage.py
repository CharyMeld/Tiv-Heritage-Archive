"""
Research batch 041 — Taraba (Phase 3): culture and heritage. Researched 2026-09-26.

Places: National Museum, Jalingo (NCMM); Yakoko Stone Burial Ground (NCMM proposed national
monument No. 66, 'Historic'); Gashaka-Gumti National Park; the Mambilla Plateau; Lake Nwonyo;
Puje, the Jukun sacred site. Festivals: Nwonyo Fishing Festival (Ibi); Purma (Chamba, Donga);
Kuchicheb (Kuteb, Takum).

Evidence notes:
  * NCMM lists read as raw HTML (numbering verified: Keana = 90 as in batch 034). No declared
    national monument in Taraba. Yakoko's LGA is not given by the NCMM; Blench's Atlas names Yakoko
    in the north-eastern (Zing) group of Mumuye — placed in Zing LGA as 'reported'.
  * Nwonyo: NICO and the Federal Ministry of Art, Culture and the Creative Economy (both Tier 1)
    agree on the lake 5 km north of Ibi, its discovery c. 1816 by Buba Wurbo, the first public
    festival under Agbumanu II (1903–1915), and the 2024 revival after a 14-year break. They give
    three meanings of the name — all recorded as traditions. Not used: 'largest lake in West Africa'.
  * Purma: M. S. Garbosa II (Gara Donga), History and Customs of the Chamba (1956), translated by
    Furniss and Fardon (Vestiges 7(2), 2021) — a first-hand account by the Gara himself.
  * Kuchicheb: Ambashak (2025, IJAMR) for the festival and the Kutumbu horn; Ahmed-Gamgum (2015)
    for its entanglement in the Takum chieftaincy conflict and the suspension of festivals —
    recorded neutrally, as that article's finding. The '25 March' date and the Ussa-hill fire
    procession appear only on weak sites — not used (gap).
  * Puje: not a festival (as some websites say) but the Jukun sacred site where a new Aku Uka
    performs his rites (Daily Trust, 2022). The same report gives the 25th Aku Uka, installed 28
    January 2022 — added to the Aku Uka record by fix_041.
  * Gashaka-Gumti: Nigeria National Park Service (Tier 1) gives 6,731 km²; Wikipedia 6,402 km² —
    both kept as statistics.
"""
import json, re, sys

ACCESSED = "2026-09-26"
W = lambda t: f"https://en.wikipedia.org/wiki/{t.replace(' ', '_')}"
SOURCES = {
    "NCMMP": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="Proposed National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/proposed-national-monuments/",
                  verification_status="verified", notes="Reused. No. 66 (Historic): 'Yakoko Stone Burial Ground, Taraba State'."),
    "NCMMD": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="National Monuments",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/national-monuments-in-nigeria/national-monuments/",
                  verification_status="verified", notes="Reused. No Taraba monument among the declared national monuments (checked 2026-09-26)."),
    "NCMMM": dict(source_type="official_website", source_kind="heritage_body", source_tier=1, title="National Museums",
                  organisation="National Commission for Museums and Monuments (NCMM)", url="https://museum.ng/museums/national-museums/",
                  verification_status="verified", notes="Reused. National Museum Jalingo: beside the Taraba State Ministry of Culture and Tourism, opposite the Governor's office, Jalingo."),
    "ATLAS": dict(source_type="book", title="An Atlas of Nigerian Languages (2020 edition)", organisation="Roger Blench",
                  url="https://www.rogerblench.info/Language/Africa/Nigeria/Atlas%20of%20Nigerian%20Languages%202020.pdf", verification_status="verified",
                  notes="Reused. Mumuye cluster, north-eastern group (Zing, Yorro and Mayo Belwa LGAs): '... also Yakoko (according to Meek)'."),
    "NICO": dict(source_type="official_website", source_kind="heritage_body", source_tier=1,
                 title="Nwonyo Fishing Festival: Taraba's Spectacular Celebration of Culture and Aquatic Heritage",
                 organisation="National Institute for Cultural Orientation (NICO)",
                 url="https://nico.gov.ng/nwonyo-fishing-festival-tarabas-spectacular-celebration-of-culture-and-aquatic-heritage/",
                 verification_status="verified",
                 notes="Lake Nwonyo 5 km north of Ibi, over 15 km long, linking to the Benue; name said to mean 'hideout for huge and dangerous aquatic animals', or in Jukun 'under the locust bean tree', or 'abode of the snake'; lake discovered c. 1816 by Buba Wurbo, founder of Ibi; first public festival under Agbumanu II (1903–1915), Ibi people fishing and neighbours such as Wukari watching; 1943: the Chief of Ibi, Muhammadu Jikan Buba, appointed Muhammadu Sango Sarkin Ruwa (custodian of the lake) to protect it and announce the start; held in the dry season; fishing, swimming, dance, music, singing, masquerades."),
    "FMACCE": dict(source_type="official_website", source_kind="official_website", source_tier=1,
                   title="Nwonyo International Fishing and Cultural Festival — Ibi, Taraba State",
                   organisation="Federal Ministry of Art, Culture, Tourism and the Creative Economy",
                   url="https://www.fmacce.gov.ng/festivals-carnivals/nwonyo-international-fishing-and-cultural-festival-ibitaraba-state",
                   verification_status="verified",
                   notes="Held at Lake Nwonyo, 5 km north of Ibi; lake discovered by the Jukun Wurbo in 1816 (Buba Wurbo); first public festival under Agbumanu II (1903–1915); record catch of 318 kg in 2010; revived in 2024 after a 14-year break; name from the Jukun Wurbo language; Ibi on the south bank of the Benue; British administrative headquarters of western Muri from 1900."),
    "GARBOSA": dict(source_type="book", source_kind="academic_book", source_tier=2, title="History and Customs of the Chamba (translation)",
                    author="M. S. Garbosa II (Gara Donga); translated by Graham Furniss and Richard Fardon",
                    organisation="Vestiges: Traces of Record", publication_date="2021",
                    publication_details="Vol. 7 (2), pp. 62–159. English translation of Labarun Chambawa da Al'Amurransu (1956).",
                    url="https://www.vestiges-journal.info/2021/pdf/furniss_2021.pdf", verification_status="verified",
                    notes="'The Chamba festival of Purma' (also Pulma): held every year at the beginning of November, 'but now ... it is not possible to hold Purma every year'; neighbouring chiefs and distant relatives invited; two horse-riding days in the bush behind the town — the first led by the senior Gangum (war leader), deputy Gangum and Galim with their people, the second the Gara's own ride, followed by the Mbala queen (the Gara's aunt) and her women; dances Daya, Genna, Lera, Voma; drink and a bull slaughtered for guests; singing and dancing for seven nights. Under Garkiye II only one Purma was held."),
    "AMBASHAK": dict(source_type="journal_article", source_kind="journal_article", source_tier=2,
                     title="A Celebration of Kuteb Heritage: The Sacred Kutumbu Instrument", author="Jeremiah Musa Ambashak",
                     organisation="International Journal of Academic Multidisciplinary Research (IJAMR)", publication_date="2025-06",
                     publication_details="Vol. 9, Issue 6, pp. 345–352.",
                     url="http://ijeais.org/wp-content/uploads/2025/6/IJAMR250643.pdf", verification_status="verified",
                     notes="The Kutumbu, a sacred horn made from a calf's horn, blown only by selected male custodians; at the Kuchecheb Festival, an annual harvest celebration, it summons the twelve Kuteb clans to Mbarikam hill (Teekum, 'we assemble') and gives thanks for the harvest; chants such as 'Yakwein wuchi nyang' (last year's harvest was bountiful); masquerade dances."),
    "GAMGUM": dict(source_type="journal_article", source_kind="journal_article", source_tier=2,
                   title="Kuchicheb Festival: The Challenges of Cultural Genocide in Nigeria's Takum Chiefdom", author="Williams A. Ahmed-Gamgum",
                   organisation="Studies in Social Sciences and Humanities (Research Academy of Social Sciences)", publication_date="2015",
                   publication_details="Vol. 2, No. 4, pp. 182–213. RePEc:rss:jnljsh:v2i4p1.",
                   url="https://ideas.repec.org/a/rss/jnljsh/v2i4p1.html", verification_status="needs_corroboration",
                   notes="Abstract only: the Kuchicheb festival of the Kuteb of Takum Chiefdom became an occasion of conflict; the author argues that the state did not apply the national cultural policy and recommends lifting the suspension of cultural festivals in Takum. An argued position in the Takum dispute."),
    "DT22": dict(source_type="news", source_kind="news", source_tier=3, title="New vista as 25th Aku-Uka of Wukari mounts throne", author="Magaji Hunkuyi",
                 organisation="Daily Trust", publication_date="2022-11-20", url="https://dailytrust.com/new-vista-as-25th-aku-uka-of-wukari-mounts-throne/",
                 verification_status="needs_corroboration",
                 notes="Manu Ishaku Adda Ali installed as the 25th Aku-Uka of Wukari (first-class staff of office from Governor Darius Ishaku); kingmakers Abon-Achuwo, Abon-Ziken, Kinda-Achuwo, Kinda-Ziken and Kun-Vyi; crowned (Pankan) at Bye Vyi on 28 January 2022; of the Ba Ma ruling house; rites at 'the Jukuns spiritual site at Puje', Bye Vyi and Kuntsa, then at Akwana, Arufu and Wapan Nghaku; chosen from princes of the Ba Ma and Ba Gya ruling houses. The article dates the predecessor's death to January 2021 — contradicted by Channels TV, Vanguard and ThisDay (October 2021); not used."),
    "NPS": dict(source_type="official_website", source_kind="official_website", source_tier=1, title="Gashaka-Gumti National Park",
                organisation="Nigeria National Park Service", url="https://nigeriaparkservice.gov.ng/gashaka-gumti/", verification_status="verified",
                notes="In the mountainous north-east next to the Cameroon border, immediately north of the Mambilla Plateau; the largest of the seven national parks; 6°55'–8°05' N, 11°11'–12°13' E; 6,731 km²; in Adamawa and Taraba states; contiguous with Faro and 'Tchabal Mbado' (Tchabal Mbabo) parks in Cameroon; highland climate around Chappal Waddi; a pre-1918 German fort and garrison on Gashaka Hill with soldiers' graves, and an English fort near Gashaka village; Gashaka Primate Project (UCL, 2000); airstrip at Serti."),
    "WGGNP": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Gashaka Gumti National Park", organisation="Wikipedia",
                  url=W("Gashaka Gumti National Park"), verification_status="needs_corroboration",
                  notes="Gazetted from two game reserves in 1991; Nigeria's largest national park; about 6,402 km²; savannah in the north, mountains and montane forest in the south; altitude 457–2,419 m (Chappal Waddi); water catchment for the Benue; Fulani pastoral enclaves."),
    "WMAMB": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Mambilla Plateau", organisation="Wikipedia",
                  url=W("Mambilla Plateau"), verification_status="needs_corroboration",
                  notes="Average elevation about 1,600 m, the highest plateau in Nigeria; about 96 km long and 40 km wide, over 9,389 km²; bounded by an escarpment up to 900 m high; Chappal Waddi (2,419 m)."),
    "WGEMBU": dict(source_type="encyclopedia", source_kind="encyclopedia", source_tier=3, title="Gembu, Nigeria", organisation="Wikipedia",
                   url=W("Gembu, Nigeria"), verification_status="needs_corroboration",
                   notes="Gembu, on the Mambilla Plateau, is the headquarters of Sardauna LGA (formerly 'Mambilla' LGA), at about 1,348 m."),
}

MUSEUM = """The National Museum, Jalingo is the national museum of the National Commission for Museums and Monuments (NCMM) in Taraba State. The NCMM gives its address as beside the Taraba State Ministry of Culture and Tourism, opposite the Governor's office, in Jalingo, the state capital. When it was founded and what its galleries hold are not yet documented here from a readable source."""

YAKOKO = """The Yakoko Stone Burial Ground in Taraba State is on the National Commission for Museums and Monuments' list of sites proposed for declaration as national monuments, as No. 66 in its historic category. Taraba has no declared national monument.

The NCMM does not describe the site or give its LGA. Yakoko is a Mumuye community: Roger Blench's Atlas of Nigerian Languages, citing C. K. Meek, lists Yakoko among the north-eastern (Zing) group of Mumuye, spoken in Zing, Yorro and Mayo Belwa LGAs, so the site is placed here in Zing LGA as reported. Its age, the form of its stone graves and who is buried there are not yet documented."""

GGNP = """Gashaka-Gumti National Park lies in the mountains of north-eastern Nigeria on the border with Cameroon, in Taraba and Adamawa states, immediately north of the Mambilla Plateau. The Nigeria National Park Service calls it the largest of the country's seven national parks and gives its area as 6,731 km²; Wikipedia, which gives about 6,402 km², says it was formed from two game reserves in 1991. It borders the Faro and Tchabal Mbabo national parks in Cameroon.

The north of the park is savannah and the south rugged mountain country with montane forest, rising to Chappal Waddi (2,419 m), the highest point in Nigeria, and it is an important catchment for the Benue. The park service also points to historic sites: a German fort and garrison on Gashaka Hill from before 1918, with the graves of German soldiers, and a British fort near Gashaka village built by the British force that drove out the Germans to take the Mambilla pass into Cameroon. Since 2000 the Gashaka Primate Project of University College London has worked in the park. Fulani pastoral enclaves lie within its boundary."""

MAMBILLA = """The Mambilla Plateau in Taraba State is the highest plateau in Nigeria, with an average elevation of about 1,600 m. According to Wikipedia it is about 96 km long and 40 km wide, covers more than 9,389 km² and is bounded by an escarpment up to 900 m high; Chappal Waddi (2,419 m), the highest mountain in Nigeria, rises from the plateau's surroundings. Gembu, on the plateau, is the headquarters of Sardauna LGA, which was once called Mambilla LGA. The Mambila are its original and largest people, and Gashaka-Gumti National Park lies immediately to its north."""

LAKE = """Lake Nwonyo lies about five kilometres north of Ibi in Taraba State and runs for more than 15 km to the River Benue, according to the National Institute for Cultural Orientation (NICO). Tradition dates its discovery to about 1816, by Buba Wurbo, the founder of Ibi. The lake gives its name to the Nwonyo Fishing Festival. In 1943 the Chief of Ibi appointed a Sarkin Ruwa, a custodian of the lake, to protect it from illegal fishing and to announce the start of each festival."""

PUJE = """Puje is the sacred site of the Jukun of Wukari, where a newly chosen Aku Uka performs the traditional rites that make him king. Daily Trust (2022) reports that after his crowning at Bye Vyi in January 2022, the 25th Aku Uka went through rites and rituals at 'the Jukuns spiritual site at Puje', at Bye Vyi and at Kuntsa before returning to Wukari, and then performed further rites at Akwana, Arufu and Wapan Nghaku. Some websites describe Puje as a festival; the source used here describes it as a place of ritual. Its exact location is not recorded."""

NWONYO = """The Nwonyo Fishing Festival is held every year at Lake Nwonyo, about five kilometres north of Ibi in Taraba State. The Federal Ministry of Art, Culture, Tourism and the Creative Economy describes it as one of the oldest festivals of its kind in Africa. By tradition the lake was discovered in about 1816 by Buba Wurbo, the founder of Ibi; the first public festival was held under Agbumanu II (1903–1915), with the people of Ibi fishing and neighbours such as Wukari watching. In 1943 the Chief of Ibi appointed a Sarkin Ruwa to guard the lake and announce the start of the fishing.

The festival is held in the dry season, when the water is low. According to the National Institute for Cultural Orientation (NICO), its heart is a fishing competition from canoes, with swimming contests, canoe races, dance, music, singing and masquerades. The ministry records a winning catch of 318 kg in 2010 and says the festival was revived in 2024, after a break of fourteen years. The meaning of the name is given in three ways: in the Jukun Wurbo language 'hideout for huge and dangerous aquatic animals', and, in other traditions, 'under the locust bean tree' or 'abode of the snake'."""

PURMA = """Purma (also Pulma) is the great festival of the Chamba of Donga. The fullest account is by M. S. Garbosa II, the seventh Gara Donga, in his History and Customs of the Chamba (1956), translated by Graham Furniss and Richard Fardon (2021). He writes that the Chamba held Purma every year at the beginning of November, but that in his day it could no longer be held every year because of the amount of work people had to do; neighbouring chiefs and distant relatives had to be invited.

Purma centres on two days of horse-riding in the bush behind the town. On the first day the senior Gangum, the Chamba war leader, rides out with the deputy Gangum, the Galim and their people; they return in companies to salute the Gara at his gateway. The second day is the Gara's own ride, followed by the Mbala queen, the Gara's aunt, with her column of women. The dances include the Daya and the Genna, and in the evenings the Lera and the Voma at the palace door. The Gara provides drink and a bull for the guests, and the singing and dancing last seven nights. Garbosa II records that under one earlier Gara, Garkiye II, Purma was held only once."""

KUCHICHEB = """Kuchicheb (also written Kuchecheb) is the annual harvest festival of the Kuteb of Takum. In a study of the Kuteb's sacred Kutumbu horn, J. M. Ambashak (2025) describes how the horn, made from a calf's horn and blown only by selected male custodians, is sounded at the festival to summon the twelve Kuteb clans to Mbarikam hill, an assembly called Teekum ('we assemble'), to give thanks for the harvest and ask blessings for the coming year, with chants such as 'Yakwein wuchi nyang' ('last year's harvest was bountiful') and masquerade dances.

The festival has also been caught up in the dispute over the Takum chieftaincy. W. A. Ahmed-Gamgum (2015) argues that the celebration of Kuchicheb became an occasion of conflict and that the suspension of cultural festivals in Takum should be lifted; the archive records this as his published argument, alongside the open dispute over the Ukwe Takum stool."""

RECORDS = [
    dict(key="museum", table="places", evidence="single_reliable_source", level="well_documented",
         fields=dict(place_type="museum", name="National Museum, Jalingo", slug="national-museum-jalingo", admin_unit_id="@admin_units:lga:taraba/jalingo",
                     status="existing", summary="The National Museum, Jalingo is the national museum of the National Commission for Museums and Monuments in Taraba State.",
                     description=MUSEUM), srcs=[("NCMMM", "NCMM national museum; address")]),
    dict(key="yakoko", table="places", evidence="multiple_sources", level="reported",
         fields=dict(place_type="heritage_site", name="Yakoko Stone Burial Ground", slug="yakoko-stone-burial-ground", admin_unit_id="@admin_units:lga:taraba/zing",
                     status="unknown", summary="The Yakoko Stone Burial Ground, a Mumuye site in Taraba State, is No. 66 on the NCMM list of proposed national monuments.",
                     description=YAKOKO),
         srcs=[("NCMMP", "Proposed national monument No. 66 (Historic)"), ("NCMMD", "No declared monument in Taraba"), ("ATLAS", "Yakoko in the Zing group of Mumuye")]),
    dict(key="ggnp", table="places", evidence="multiple_sources", level="well_documented",
         fields=dict(place_type="natural_feature", name="Gashaka-Gumti National Park", slug="gashaka-gumti-national-park", admin_unit_id="@admin_units:state:taraba",
                     status="existing", summary="Gashaka-Gumti National Park, on the Cameroon border in Taraba and Adamawa states, is Nigeria's largest national park.",
                     description=GGNP),
         srcs=[("NPS", "Location, area 6,731 km², neighbours, forts, primate project"), ("WGGNP", "Gazetted 1991; 6,402 km²; landscape; Chappal Waddi")]),
    dict(key="mambilla", table="places", evidence="multiple_sources", level="well_documented",
         fields=dict(place_type="natural_feature", name="Mambilla Plateau", slug="mambilla-plateau", admin_unit_id="@admin_units:lga:taraba/sardauna",
                     status="existing", summary="The Mambilla Plateau in Sardauna LGA, Taraba State, is the highest plateau in Nigeria (about 1,600 m).",
                     description=MAMBILLA),
         srcs=[("WMAMB", "Elevation, size, escarpment"), ("WGEMBU", "Gembu, HQ of Sardauna LGA, on the plateau"), ("NPS", "Park immediately north of the plateau")]),
    dict(key="lake", table="places", evidence="multiple_sources", level="well_documented",
         fields=dict(place_type="natural_feature", name="Lake Nwonyo", slug="lake-nwonyo", admin_unit_id="@admin_units:lga:taraba/ibi",
                     status="existing", summary="Lake Nwonyo, five kilometres north of Ibi in Taraba State, is the site of the Nwonyo Fishing Festival.",
                     description=LAKE),
         srcs=[("NICO", "Location, length, discovery tradition, Sarkin Ruwa"), ("FMACCE", "Location; discovery by the Jukun Wurbo")]),
    dict(key="puje", table="places", evidence="single_reliable_source", level="reported",
         fields=dict(place_type="sacred_site", name="Puje", slug="puje", admin_unit_id="@admin_units:state:taraba", status="existing",
                     summary="Puje is the Jukun sacred site where a new Aku Uka of Wukari performs the rites of kingship.", description=PUJE),
         srcs=[("DT22", "Rites of the 25th Aku Uka at Puje, Bye Vyi and Kuntsa (2022)")]),
    dict(key="nwonyo", table="cultural_records", evidence="multiple_sources", level="well_documented",
         fields=dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Nwonyo Fishing Festival",
                     local_name="Nwonyo", slug="nwonyo-fishing-festival", timing="Annually in the dry season, when the lake is low",
                     season="Dry season", current_status="revived", scope_level="community",
                     summary="The Nwonyo Fishing Festival at Lake Nwonyo near Ibi, Taraba State, dates from the early 20th century and was revived in 2024.",
                     description=NWONYO),
         srcs=[("NICO", "Festival, history, custodian, activities"), ("FMACCE", "History, 2010 record catch, 2024 revival, name")]),
    dict(key="purma", table="cultural_records", evidence="single_reliable_source", level="well_documented",
         fields=dict(record_type="festival", cultural_category="festivals_ceremonies", nature="documented_practice", name="Purma", local_name="Purma",
                     slug="purma", timing="Beginning of November (Garbosa II, 1956); not held every year by then", month_from=11, month_to=11,
                     current_status="unknown", scope_level="community",
                     summary="Purma is the seven-day festival of the Chamba of Donga, with the war leaders' and the Gara's horse-riding days.",
                     description=PURMA),
         srcs=[("GARBOSA", "Garbosa II's account of Purma")]),
    dict(key="kuchicheb", table="cultural_records", evidence="multiple_sources", level="well_documented",
         fields=dict(record_type="festival", cultural_category="festivals_ceremonies", nature="contemporary_practice", name="Kuchicheb",
                     local_name="Kuchicheb", slug="kuchicheb", timing="Annually, at harvest", season="Harvest", current_status="unknown", scope_level="ethnic_group",
                     summary="Kuchicheb is the annual harvest festival of the Kuteb of Takum, when the Kutumbu horn calls the twelve clans to Mbarikam hill.",
                     description=KUCHICHEB),
         srcs=[("AMBASHAK", "Festival, Kutumbu horn, Mbarikam hill, chants"), ("GAMGUM", "Festival and the Takum conflict; suspension")]),
]
NAMES = [
    dict(record="purma", name="Pulma", name_type="spelling_variant", usage_notes="Garbosa II: 'some people call Pulma'.", srcs=["GARBOSA"]),
    dict(record="kuchicheb", name="Kuchecheb", name_type="spelling_variant", usage_notes="Spelling used by Ambashak (2025).", srcs=["AMBASHAK"]),
    dict(record="nwonyo", name="Nwonyo International Fishing and Cultural Festival", name_type="official", usage_notes="Federal ministry's title.", srcs=["FMACCE"]),
    dict(record="ggnp", name="GGNP", name_type="abbreviation", usage_notes="Common abbreviation.", srcs=["WGGNP"]),
]
RELATIONS = [
    dict(frm="yakoko", type="associated_with", to="@ethnic_groups:mumuye", role="Mumuye community site", source="ATLAS", evidence="single_reliable_source", level="reported",
         notes="Yakoko is a Mumuye community (Blench's Atlas, citing Meek)."),
    dict(frm="mambilla", type="associated_with", to="@ethnic_groups:mambila", role="homeland", source="WMAMB", evidence="multiple_sources", level="well_documented",
         notes="The Mambila of the Mambilla Plateau (Wikipedia: the original and predominant group)."),
    dict(frm="ggnp", type="located_in", to="@admin_units:lga:taraba/gashaka", source="NPS", evidence="multiple_sources", level="reported",
         notes="The Gashaka sector of the park, reached via Serti (headquarters of Gashaka LGA)."),
    dict(frm="puje", type="associated_with", to="@ethnic_groups:jukun", role="sacred site", source="DT22", evidence="single_reliable_source", level="reported",
         notes="'The Jukuns spiritual site at Puje' (Daily Trust, 2022)."),
    dict(frm="@polities:aku-uka-of-wukari", type="custodian_of", to="puje", source="DT22", evidence="single_reliable_source", level="reported",
         notes="A new Aku Uka performs his rites at Puje."),
    dict(frm="nwonyo", type="celebrated_in", to="@admin_units:lga:taraba/ibi", source="NICO", evidence="multiple_sources", level="verified",
         notes="Lake Nwonyo, 5 km north of Ibi (NICO; federal ministry)."),
    dict(frm="nwonyo", type="associated_with", to="lake", role="the lake it is held on", source="NICO", evidence="multiple_sources", level="verified", notes="Held at Lake Nwonyo."),
    dict(frm="nwonyo", type="associated_with", to="@ethnic_groups:jukun", role="Jukun Wurbo tradition of the lake", source="FMACCE", evidence="single_reliable_source",
         level="reported", notes="The ministry names the Jukun Wurbo as the lake's discoverers and the source of its name; the ministry also describes Ibi's people as largely Hausa and Fulani."),
    dict(frm="purma", type="celebrated_by", to="@ethnic_groups:chamba", source="GARBOSA", evidence="single_reliable_source", level="well_documented",
         notes="The Chamba of Donga (Garbosa II)."),
    dict(frm="purma", type="celebrated_in", to="@admin_units:lga:taraba/donga", source="GARBOSA", evidence="single_reliable_source", level="well_documented",
         notes="Donga town (Garbosa II)."),
    dict(frm="@polities:gara-donga", type="custodian_of", to="purma", source="GARBOSA", evidence="single_reliable_source", level="well_documented",
         notes="The Gara leads the second day and hosts the guests."),
    dict(frm="kuchicheb", type="celebrated_by", to="@ethnic_groups:kuteb", source="AMBASHAK", evidence="multiple_sources", level="well_documented",
         notes="The twelve Kuteb clans (Ambashak 2025; Ahmed-Gamgum 2015)."),
    dict(frm="kuchicheb", type="celebrated_in", to="@admin_units:lga:taraba/takum", source="GAMGUM", evidence="multiple_sources", level="reported",
         notes="Takum Chiefdom (Ahmed-Gamgum 2015). Mbarikam hill's LGA (Takum or Ussa) is not stated."),
]
STATS = [
    dict(record="ggnp", metric="area_km2", value_low=6731, method="other", notes="Nigeria National Park Service", source="NPS", evidence="single_reliable_source", level="well_documented"),
    dict(record="ggnp", metric="area_km2", value_low=6402, method="other", notes="Wikipedia (about 6,402 km²)", source="WGGNP", evidence="single_reliable_source", level="reported"),
]
GAPS = [
    ("Yakoko Stone Burial Ground", "The NCMM names the site only; its exact location, age, the form of the stone graves and their history need an archaeological or NCMM source."),
    ("National Museum, Jalingo", "Founding date and collections not found in a readable source."),
    ("Kuchicheb date and procession", "Websites give 25 March and a procession bringing fire from Ussa hill to Takum; not found in a reliable source."),
    ("Purma today", "Whether and when Purma is still held in Donga is not documented after Garbosa II's account (1956)."),
    ("Other Taraba festivals", "Kati (Mambila), Tagba (Acha, Takum), the Jukun Puje rites calendar, Mumuye festivals and the Wukari Kwararafa sites are not yet researched."),
    ("Gashaka-Gumti area", "The park service gives 6,731 km², Wikipedia about 6,402 km²; both are kept."),
]


def words(t):
    return len(re.findall(r"[\w’'-]+", t))


def build():
    return dict(sources=SOURCES, units=[], places=[], changes=[], records=RECORDS, names=NAMES, gaps=GAPS, updates=[],
                relations=[dict(r, **{"from": r["frm"]}) for r in RELATIONS], statistics=STATS,
                scope="Taraba culture and heritage: 6 places (NCMM museum and proposed monument, national park, plateau, lake, Puje) and 3 festivals (Nwonyo, Purma, Kuchicheb).")


def report():
    texts = (("National Museum, Jalingo", MUSEUM), ("Yakoko Stone Burial Ground", YAKOKO), ("Gashaka-Gumti National Park", GGNP),
             ("Mambilla Plateau", MAMBILLA), ("Lake Nwonyo", LAKE), ("Puje", PUJE), ("Nwonyo Fishing Festival", NWONYO), ("Purma", PURMA), ("Kuchicheb", KUCHICHEB))
    L = ["# Research batch 041 — Taraba: culture and heritage", "",
         f"Researched {ACCESSED}. Created in review; published only after your approval.", "",
         "**Findings:**",
         "- The NCMM has **no declared national monument in Taraba**. The **Yakoko Stone Burial Ground** is No. 66 (Historic) on its *proposed* list, and the NCMM runs a **National Museum in Jalingo**.",
         "- **Puje** is the Jukun **sacred site** where a new Aku Uka performs his rites (Daily Trust, 2022), not a festival as some websites say.",
         "- **Purma** comes from a first-hand account by the seventh Gara Donga himself (1956, translated 2021).",
         "- **Kuchicheb** is sourced from two journal articles. One (Ahmed-Gamgum 2015) argues a side in the Takum dispute, so it is recorded as his argument.", "",
         f"- **9 records:** 6 places and 3 festivals ({sum(words(t) for _, t in texts)} words). **{len(RELATIONS)} links**, 4 other names, 2 area figures for the national park (6,731 and 6,402 km², kept side by side).",
         "- **fix_041:** adds the 2022 succession to the Aku Uka record: Manu Ishaku Adda Ali, the 25th Aku Uka, crowned 28 January 2022, with rites at Puje.", ""]
    for n, t in texts:
        L += [f"## {n} ({words(t)} words)", ""] + [f"> {p}" if p else ">" for p in t.split("\n")] + [""]
    L += ["## Sources", ""] + [f"- **{k}** — {s['title']} ({s.get('author', s['organisation'])}{', ' + s['publication_date'] if s.get('publication_date') else ''}). {s['url']}. Tier {s.get('source_tier', '-')}." for k, s in SOURCES.items()] + [""]
    L += ["## Not used", "",
          "- 'Largest natural lake in West Africa' (NICO; unverified).",
          "- The 25 March date and the Ussa-hill fire procession for Kuchicheb (websites only).",
          "- Blogs describing Puje as a festival (nigeria234, ibifoundry).", "",
          "## Research gaps", ""] + [f"- **{t}.** {x}" for t, x in GAPS]
    return "\n".join(L)


if __name__ == "__main__":
    out = sys.argv[1] if len(sys.argv) > 1 else "."
    json.dump(build(), open(f"{out}/batch_041_taraba_heritage.json", "w"), indent=1, ensure_ascii=False)
    open(f"{out}/batch_041_taraba_heritage_REVIEW.md", "w").write(report())
    print("records", len(RECORDS), "relations", len(RELATIONS), [words(t) for t in (MUSEUM, YAKOKO, GGNP, MAMBILLA, LAKE, PUJE, NWONYO, PURMA, KUCHICHEB)])
