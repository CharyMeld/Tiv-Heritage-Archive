# Research batch 087 — Kano: the languages

Researched 2026-10-02. Phase 3, the first Kano batch. Created in review; published only after your approval.

## Summary

- **Kano is almost wholly Hausa-speaking.** The Atlas places only two languages in Kano: Hausa, statewide, and Kurama, in Tudun Wada.
- **New: the Hausa language record** (`haus1257`). The archive had a Hausa *people* record but no language record, so every state's QC reported 'Hausa has no speaks link'. Hausa is now linked to the 8 states the Atlas names as first-language areas (Sokoto, Zamfara, Kaduna, Kano, Katsina, Jigawa, Gombe and Bauchi), and the Hausa people are linked to it.
- **New: Kurama** (`kura1249`), a Kainji language of Tudun Wada LGA and of southern Kaduna.
- **Fulfulde** gets a Kano link. Wikipedia names Hausa and Fulfulde as the state's official languages.
- **Not used:** Wikipedia's 'citation needed' claim that Moro, Kurama and Map are spoken in Doguwa.
- **13 links** and **8 other names**.

## The new languages

**Hausa** (haus1257). Hausa is spoken as a first language in large areas of Sokoto, Zamfara, Kaduna, Kano, Katsina, Jigawa, Gombe and Bauchi states and in the Republic of Niger, and as a regional language far beyond them, in the Middle Belt of Nigeria, northern Ghana and Benin Republic, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as Chadic (West A: Hausa group). The Atlas cites 5,700,000 speakers in 1952 and some 25 million first- and second-language speakers (SIL 1973), including about 3.5 million outside Nigeria. Its dialects fall into an Eastern group (Kano, Katagum, Hadejiya), a Western group (Sokoto, Gobirawa, Adarawa, Kebbawa, Zamfarawa) and a Northern group (Katsina, Arewa). It has an official orthography and a large literature, including scripture portions from 1853, a complete Bible from 1932 and Muslim literature in the Arabic-based Ajami script; an indigenous Hausa sign language is also recorded. Wikipedia names Hausa as the dominant language of Kano State and, with Fulfulde, its official language.

**Kurama** (kura1249). Kurama is a language spoken in Tudun Wada LGA of Kano State and in Saminaka and Ikara LGAs of Kaduna State, according to Roger Blench's Atlas of Nigerian Languages (2020), which classifies it as Benue–Congo (Kainji: Eastern Kainji, Northern Jos group, Kauru subgroup). Its speakers call it Tikurumi and themselves Akurumi; Bagwama, another name, is also used of the Ruma. The Atlas cites 11,300 speakers in 1949 and notes a scripture project in progress.

## Research gaps

- **Kano: languages by LGA.** The Atlas places only Kurama by LGA (Tudun Wada). Hausa and Fulfulde are statewide; Wikipedia's claim that Moro, Kurama and Map are spoken in Doguwa LGA is marked 'citation needed' and is not used.
- **Hausa: wider links.** The Hausa language is linked to the eight states the Atlas names as first-language areas; its regional use in the Middle Belt and elsewhere is not linked state by state.
## Test on a copy of the live database (2 Oct 2026, after Gombe was finished)

- **Kano baseline:** 44 LGAs; 0 peoples, 0 languages, 0 wards.
- **Import:** 3 sources reused, 2 languages, 5 source links, 8 names, 13 relations and 2 gaps.
- **Publish:** 2 languages.
- **Pages:** the Hausa and Kurama language pages, the Hausa people page, Fulfulde, the Kano state page and Tudun Wada LGA all return 200 with no PHP errors.
- **Sitemap:** 3,026, unchanged.
- **Kano QC:** 0 problems; 3 languages now in scope. The 44 "LGA has no people" checks are for batch 087b.
- **Bonus:** the national note "Hausa has no 'speaks' link" disappears from the Bauchi and Gombe QC, and will disappear from every state's QC.
- **Undo:** after unpublishing and rolling back, every table count matches the live copy apart from the activity-log and search-index rows, the known side effects.
