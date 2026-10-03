# Research batch 033 — Nasarawa: traditional institutions (1)

Researched 2026-09-26. Created in review; published only after your approval.

- Five records: Lafia Emirate (163 words), Keffi Emirate (101), Nasarawa Emirate (81), Andoma of Doma (137), Osana of Keana (93).
- 9 links: each to its LGA; Lafia to the Kanuri (ruling houses); Doma and Keana to the Alago; Doma's tradition of a Kwararafa origin (as tradition).
- All under 300 words, so they stay noindex (sitemap unchanged). Founding dates for Doma (1232) and Keana (12th century) are marked as **tradition**.
- **The 22 first-class stools** (Daily Trust, 2012):
  - the Emirs of Lafia, Keffi and Nasarawa
  - the Andoma of Doma, Aren Eggon, Osana of Keana, Emir of Awe, Oriye Rindri, Ohimege Opanda, Esu Karu, Osu Ajiri, Emir of Karshi, She Migili, Odyong Nyankpa, Emir of Azara, Chun Mada
  - Abaga Toni, Gomo Babye, Osuko of Obi, Gom Mama, Sarkin Loko, Sangarin Kwandere
  - Only five are recorded now; the rest are a research gap.

## Lafia Emirate

> The Lafia Emirate, with its seat at Lafia, the capital of Nasarawa State, is one of the three oldest traditional stools of the state; Daily Trust (2012) dates the emirates of Lafia, Keffi and Nasarawa back to the early colonial era.
>
> According to Wikipedia, Lafia was founded in the late eighteenth century by Muhammadu Dunama, south of Shabu village, and became the capital of an important chiefdom in the late nineteenth century. Under Mohamman Agwai (1881–1903) its market became one of the most important in the Benue valley, with a trade route to Loko, a river port on the Benue. In 1903 the British recognised Musa as Lafia's first emir, and the emirate formed most of the Lafia Division of Benue Province.
>
> Wikipedia names two Kanuri (Bare-Bari) ruling houses, Ari and Dallah Dunama. It records Isa Mustapha Agwai I as the longest-reigning emir (1976–2019), succeeded by Sidi Bage Muhammad I, a retired Justice of the Supreme Court, as the seventeenth emir.

## Keffi Emirate

> The Keffi Emirate is one of the three oldest traditional stools of Nasarawa State (Daily Trust, 2012). According to Wikipedia, Keffi was founded around 1802 by Abdu Zanga, a Fulani leader who took the title of emir; his small state was subject to the Zaria emirate, to which it paid an annual tribute of slaves.
>
> Wikipedia also records that in 1902 Keffi was the scene of an incident that led to the British invasion of northern Nigeria: the magaji, Zaria's representative at Keffi, killed a British officer, and his flight to Kano gave Frederick Lugard the pretext to invade the caliphate.

## Nasarawa Emirate

> The Nasarawa Emirate, with its seat at Nasarawa town, is one of the three oldest traditional stools of Nasarawa State, dating back to the early colonial era (Daily Trust, 2012). The Nasarawa State Government records that in 1902, after the Emir of Nasarawa submitted to the British, the colonial Lower Benue Province was renamed Nasarawa Province and its headquarters moved to Nasarawa town; the state takes its name from the emirate and town. Its earlier history is not yet recorded here.

## Andoma of Doma

> The Andoma of Doma is the traditional ruler of Doma, a kingdom of the Alago people and one of Nasarawa State's first-class stools (Daily Trust, 2012). Wikipedia describes the Alago as the main people of Doma LGA, with the Bassa numerous in its south.
>
> By tradition — Wikipedia calls it a popular tale — the kingdom was founded in 1232 by Andoma, whose people had come from Apa, the seat of the old Kwararafa confederacy, by way of Idah, Ogyogo on the Benue and Obasidoma in present-day Keana. The kingdom became part of the British protectorate of Northern Nigeria in 1901. Wikipedia gives a list of rulers taken from John Stewart's African States and Rulers (2005), from Andoma to Atta IV (1901–1930); the present ruler is described as the 43rd Andoma. Doma's annual festival is named as Odu.

## Osana of Keana

> The Osana of Keana is the traditional ruler of Keana, one of the main centres of the Alago people and one of Nasarawa State's first-class stools (Daily Trust, 2012). Wikipedia calls Keana the "home of salt" and, with Doma, Obi, Agwatashi and Assakio, one of the major towns of the Alago nation. By tradition Keana was founded in the twelfth century by Akyana Adi. The present ruler is described as the 34th Osana, and Wikipedia counts the Osana, the Andoma of Doma and the Osuko of Obi among the most powerful Alago rulers.

## Not used

- Britannica (automated access blocked).
- Sahara Reporters (2023) on new first-class stools: it says four, then names six, and cites no law.

## Research gaps

- **The other first-class stools.** Daily Trust (2012) lists 22 first-class stools; only five are recorded here. The others (Aren Eggon, Emir of Awe, Oriye Rindri, Ohimege Opanda, Esu Karu, Osu Ajiri, Emir of Karshi, She Migili, Odyong Nyankpa, Emir of Azara, Chun Mada, Abaga Toni, Gomo Babye, Osuko of Obi, Gom Mama, Sarkin Loko, Sangarin Kwandere) need sources.
- **Stools created after 2012.** A 2023 report (Sahara Reporters) of new first-class chiefdoms is internally inconsistent; needs the state gazette or the council of chiefs.
- **Nasarawa State Council of Chiefs and chieftaincy law.** Not yet sourced: its chairman, composition and the governing law.
- **Early history of the Nasarawa and Keffi emirates.** Only outlined from Wikipedia; needs scholarly histories of the Sokoto-era emirates of the Benue valley.
- **Doma rulers list.** Taken by Wikipedia from John Stewart, African States and Rulers (2005); the book itself not consulted.
## Tests (copy of the live database, MySQL 8.0.46)

- **Import:** the dry run is clean (5 new sources, 1 reused, 5 records, 9 links, 5 gaps).
- **Pages after publishing on the copy:**
  - The Kingdoms list, all five record pages and the Alago page load.
  - The Andoma of Doma page shows "Founded: 1232 (tradition)".
  - The sitemap is **unchanged at 3,023**, and there are 0 PHP errors.
- **Full undo:** the rollback gives a database identical to a fresh copy of the live one.
- **No code changes; no changes to existing records.**

## Deploy (on your approval)

1. Back up the database.
2. Import (dry run, then apply).
3. Publish.
4. Clear the cache.
5. Check the pages.
