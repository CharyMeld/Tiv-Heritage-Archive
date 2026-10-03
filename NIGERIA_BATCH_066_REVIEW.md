# Research batch 066 — Borno: culture and heritage

Researched 2026-10-01. Created in review; published only after your approval.

## What it adds

- **7 places:**
  - **Rabeh's Fort, Dikwa**: Borno's only *declared* national monument (No. 13), and Rabih az-Zubayr's fortified capital from 1893 to 1900
  - **two proposed national monuments:** the El-Kanemi Prayer House at Ngala (No. 60) and the Tomb of the First Four Shehus (No. 61). The NCMM places the tomb only in 'Borno State'.
  - the **National Museum Maiduguri**
  - **Kukawa**: the al-Kanemi capital from 1846 to 1893
  - **Chad Basin National Park** (Borno sector) and **Sambisa Forest**
- **1 cultural record:** the **Kanem-Borno Cultural Summit** in Maiduguri, reported on 2 January 2026, at *reported* level.
- **Links:** Kukawa and the tomb to the Borno Emirate; Sambisa to the park; the summit to the Kanuri and Maiduguri.
- **Not recorded:**
  - the UNESCO-listed Eid durbar, which concerns Kano
  - festivals of Borno's peoples: no reliable source was found
- All texts are under 300 words, so the sitemap is unchanged.

## Rabeh's Fort, Dikwa (100 words; verified)

> Rabeh's House or Fort at Dikwa is No. 13 on the National Commission for Museums and Monuments' list of declared national monuments, and the only declared national monument in Borno State. Dikwa was the capital of the Sudanese warlord Rabih az-Zubayr, who conquered the Kanem–Bornu Empire in 1893, destroyed its capital at Kukawa and settled at Dikwa because of its better communications and water supply; according to Wikipedia the town was heavily fortified and remained his capital until he was killed at the battle of Kousséri in 1900. The fort's present condition is not described in a readable source.

## El-Kanemi Prayer House, Ngala (51 words; verified)

> The El-Kanemi Prayer House at Ngala is No. 60 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category. Its age and its link to Muhammad al-Amin al-Kanemi, founder of the dynasty of the shehus of Borno, are not described in a readable source.

## Tomb of the First Four Shehus (69 words; verified)

> The Tomb of the First Four Shehus is No. 61 on the National Commission for Museums and Monuments' list of proposed national monuments, in the 'Historic' category; the list places it only in 'Borno State'. The shehus are the rulers of the al-Kanemi dynasty, whose line began with Muhammad al-Amin al-Kanemi in the early 19th century. Which shehus are buried there, and where, is not stated in the list.

## National Museum Maiduguri (43 words; verified)

> The National Museum Maiduguri is one of the national museums of the National Commission for Museums and Monuments (NCMM), which gives its address as the Custom Area, Maiduguri, P.M.B. 1029. Its history and collections are not described in a readable source.

## Kukawa (100 words; reported)

> Kukawa, near Lake Chad, was founded as Kuka in 1814 by Muhammad al-Amin al-Kanemi, the scholar and military leader whose descendants became the shehus of Borno, and it served as the capital of the Kanem–Bornu Empire from 1846 to 1893 (Wikipedia). Umar Kura rebuilt it as two towns, each with a wall of white clay, enclosed with villages, farms and a cemetery by a mud wall some 6 metres high. Rabih az-Zubayr captured and destroyed Kukawa in May 1893. Shehu Abubakar Garbai was invested among its ruins in 1904, but moved his capital to Yerwa, now Maiduguri, in 1907.

## Chad Basin National Park (62 words; reported)

> Chad Basin National Park is a national park of about 2,258 km² in the Chad Basin of north-eastern Nigeria, established in 1991 (Wikipedia). It is fragmented into three sectors: the Chingurmi-Duguma sector, in the Sudanian savanna of Borno State, and the Bade-Nguru Wetlands and Bulatura sectors, in the Sahel of Yobe State. Wikipedia places the Sambisa Forest in its south-western part.

## Sambisa Forest (68 words; reported)

> The Sambisa Forest lies about 60 km south-east of Maiduguri, at the edge of the West Sudanian and Sahel savannas, and has an area of about 518 km² (Wikipedia). The colonial Sambisa Game Reserve was incorporated into Chad Basin National Park in 1991. After Boko Haram insurgents took over the forest in February 2013, its management was abandoned and most of its large animals disappeared, according to Wikipedia.

## Kanem-Borno Cultural Summit (80 words; reported)

> The Kanem-Borno Cultural Summit, hosted in Maiduguri by Governor Babagana Zulum and reported by Voice of Nigeria on 2 January 2026, brought together 161 emirs and delegations of Kanuri people from at least ten African countries, among them Chad, Niger, Cameroon, Sudan, Libya and Ghana. It aimed to strengthen cross-border kinship among Kanuri communities and preserve the legacy of the Kanem–Bornu civilisation, with cultural performances, traditional dances and heritage displays. Whether it will be held regularly is not stated.

## Research gaps

- **Borno: monuments.** The NCMM names Rabeh's Fort, the El-Kanemi Prayer House and the Tomb of the First Four Shehus without dates, descriptions or (for the tomb) a town. Their condition after the insurgency is not known.
- **Borno: festivals.** No reliable source was read for the festivals of Borno's peoples (Kanuri, Shuwa, Bura, Marghi and others). The Eid durbar inscribed by UNESCO in December 2024 concerns Kano and is left for a Kano or national batch.
- **Borno: Chad Basin National Park and Sambisa.** The park's Borno sector (Chingurmi-Duguma) is not tied to an LGA in a source read; Wikipedia gives two areas for the Sambisa game reserve (518 and 2,258 km²).
## Test (1 Oct 2026, on a fresh copy of live, with 065 in it)

- **Import:** dry run, then apply. Result: 7 places and 1 cultural record, 11 source links, 2 names, 5 relations and 3 gaps. 5 sources are new; 4 are reused (the three NCMM lists and Wikipedia's list of shehus).
- **Publish:** 7 places and 1 cultural record.
- **Pages:** Rabeh's Fort, Kukawa, Chad Basin National Park, Sambisa Forest, the National Museum Maiduguri, the summit, the Borno Emirate and Borno State all return 200 with no PHP errors. All the new pages are noindex.
- **Sitemap:** 3,026, unchanged.
- **Borno QC:** 0 problems. There are now 11 places and 21 cultural records in scope.
- **Undo:** unpublish, then roll back. Every table count matches the pre-import snapshot except two, both known side effects of the test:
  - activity_log: +8
  - archive_search_index: +8
