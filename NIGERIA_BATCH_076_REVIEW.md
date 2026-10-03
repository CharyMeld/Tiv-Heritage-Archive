# Research batch 076 — Bauchi: INEC wards

Researched 2026-10-01. Directory PDF via web.archive.org/web/2020id_/https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_Bauchi.pdf (saved as `database/research/data/inec_bauchi.pdf`); parsed with `inec_parse_directory.py` (all 20 LGA ward counts and polling-unit totals match INEC's summary table) into `data/inec_bauchi_ras.json`; built with `inec_wards_batch.py bauchi Bauchi`. All 20 INEC LGA names match the archive's records (INEC writes 'DAMBAN', as the archive does).


**Source:** INEC, *Directory of Polling Units: Bauchi State* (revised January 2015), Tier 1, via the Internet Archive. INEC has removed the PDF from its site.

- **212 wards** in 20 LGAs, each with INEC's code (LGA-ward) and its number of polling units in 2015. Every LGA's ward count and polling-unit total matches the directory's summary table.
- Wards are listed on their LGA's page, with INEC's disclaimer. They have no pages of their own and stay out of the sitemap, search index, AI lookups and home-page lists (as for Benue).

| LGA | Wards | Polling units |
|---|---|---|
| Alkaleri | 11 | 237 |
| Bauchi | 12 | 493 |
| Bogoro | 10 | 70 |
| Damban | 10 | 121 |
| Darazo | 11 | 233 |
| Dass | 10 | 79 |
| Gamawa | 11 | 256 |
| Ganjuwa | 11 | 208 |
| Giade | 10 | 121 |
| Itas/Gadau | 10 | 193 |
| Jama'are | 10 | 105 |
| Katagum | 11 | 274 |
| Kirfi | 10 | 132 |
| Misau | 10 | 197 |
| Ningi | 11 | 282 |
| Shira | 11 | 238 |
| Tafawa Balewa | 11 | 196 |
| Toro | 11 | 292 |
| Warji | 10 | 116 |
| Zaki | 11 | 231 |

Ward names per LGA (as INEC writes them):

- **Alkaleri**: Alkaleri (01-01); Pali (01-02); Gar (01-03); Gwaram (01-04); Maimadi (01-05); Dan Kungibar (01-06); Birin/ Gigara/ Yankari (01-07); Yuli/ Lim (01-08); Futuk (01-09); Yalo (01-10); Gwana / Mansur (01-11)
- **Bauchi**: Majidadi 'A' (02-01); Majidadi 'B' (02-02); Makama/Sarki Baki (02-03); Zungur/Liman Katagum (02-04); Mun/Munsal (02-05); Dandango/Yamrat (02-06); Birshi/Miri (02-07); Kundum/Durum (02-08); Kangyare/Turwun (02-09); Galambi/Gwaskwaram (02-10); Dan'Iya Hardo (02-11); Dawaki (02-12)
- **Bogoro**: Bogoro "A" (03-01); Bogoro "B" (03-02); Bogoro "C" (03-03); Bogoro "D" (03-04); B O I "A" (03-05); B O I "B" (03-06); B O I "C" (03-07); Lusa "A" (03-08); Lusa "B" (03-09); Lusa "C" (03-10)
- **Damban**: Garuza (04-01); Gurbana (04-02); Dambam (04-03); Yanda (04-04); Yame (04-05); Dagauda (04-06); Gargawa (04-07); Zaura (04-08); Jalam Central (04-09); Jalam East (04-10)
- **Darazo**: Darazo (05-01); Tauya (05-02); Gabarin (05-03); Konkiyal (05-04); Lago (05-05); Sade (05-06); Lanzai (05-07); Yautare (05-08); Gabciyari (05-09); Wahu (05-10); Papa (05-11)
- **Dass**: Bagel/Bajar (06-01); Bundot (06-02); Bununu Central (06-03); Bununu South (06-04); Baraza (06-05); Dott (06-06); Durr (06-07); Polchi (06-08); Wandi (06-09); Zumbul/Lukshi (06-10)
- **Gamawa**: Gamawa (07-01); Kafin Romi (07-02); Gololo (07-03); Kubdiya (07-04); Alagarno/Jadori (07-05); Tumbi (07-06); Udubo (07-07); Tarmasuwa (07-08); Raga (07-09); Gadiya (07-10); Zindi (07-11)
- **Ganjuwa**: Ganjuwa (08-01); Gungura (08-02); Kafin Madaki (08-03); Kariya (08-04); Kubi East (08-05); Kubi West (08-06); Miya East (08-07); Miya West (08-08); Nasarawa North (08-09); Nasarawa South (08-10); Yali (08-11)
- **Giade**: Chinkani (09-01); Sabon Sara (09-02); Doguwa Central (09-03); Doguwa South (09-04); Giade (09-05); Isawa (09-06); Uzum "A" (09-07); Uzum "B" (09-08); Zabi (09-09); Zirrami (09-10)
- **Itas/Gadau**: Itas (10-01); Mashema (10-02); Gwarai (10-03); Abdallawa/Magarya (10-04); Buzawa (10-05); Bilkicheri (10-06); Bambal (10-07); Gadau (10-08); Kashuri (10-09); Zubuki (10-10)
- **Jama'are**: Jama'are 'A' (11-01); Jama'are 'B' (11-02); Jama'are 'C' (11-03); Jama'are 'D' (11-04); Dogon Jeji "A" (11-05); Dogon Jeji "B" (11-06); Dogon Jeji "C" (11-07); Hanafari (11-08); Galdimari (11-09); Jurara (11-10)
- **Katagum**: Tsakuwa Kofar Gabas/ Kofar Kuka (12-01); Nasarawa Bakin Kasuwa (12-02); Madangala (12-03); Madara (12-04); Buskuri (12-05); Ragwam/Magonshi (12-06); Gambaki/Bidir (12-07); Chinade (12-08); Bulkachuwa/Dagaro (12-09); Yayu (12-10); Madachi/Gangai (12-11)
- **Kirfi**: Badara (13-01); Bara (13-02); Beni "A" (13-03); Beni "B" (13-04); Dewu Central (13-05); Dewu East (13-06); Kirfi (13-07); Shango (13-08); Tubule (13-09); Wanka (13-10)
- **Misau**: Zadawa (14-01); Beti (14-02); Jarkasa (14-03); Kukadi/Gundari (14-04); Ajilin/Gugulin (14-05); Tofu (14-06); Hardawa (14-07); Sarma/Akuyam (14-08); Sirko (14-09); Gwaram (14-10)
- **Ningi**: Ningi (15-01); Dingis (15-02); Nasaru (15-03); Jangu (15-04); Balma (15-05); Kudu / Yamma (15-06); Tiffi / Guda (15-07); Burra / Kyata (15-08); Sama (15-09); Bashe (15-10); Kurmi (15-11)
- **Shira**: Andubun (16-01); Sambuwal (16-02); Bukul/Bangire (16-03); Disina (16-04); Faggo (16-05); Beli/Gagidaba (16-06); Kilbori (16-07); Shira (16-08); Tsafi (16-09); Tumfafi (16-10); Zubo (16-11)
- **Tafawa Balewa**: Kardam "A" (17-01); Kardam "B" (17-02); Lere North (17-03); Lere South (17-04); Tapshin (17-05); Wai (17-06); Ball (17-07); Bula (17-08); Dajin (17-09); Dull (17-10); Bununu (17-11)
- **Toro**: Toro / Tulai (18-01); Tilden Fulani (18-02); Ribina (18-03); Mara / Palama (18-04); Rauta / Geji (18-05); Jama'a / Zaranda (18-06); Lame (18-07); Wonu (18-08); Zalau / Rishi (18-09); Tama (18-10); Rahama (18-11)
- **Warji**: Baima North / West (19-01); Baima South/East (19-02); Dagu East (19-03); Dagu West (19-04); Gabanga (19-05); Katanga (19-06); Ranga (19-07); Tiyin (19-08); Tudun Wada East (19-09); Tudun Wada West (19-10)
- **Zaki**: Bursali (20-01); Katagum (20-02); Tashena / Gadai (20-03); Makawa (20-04); Sakwa (20-05); Gumai (20-06); Murmur North (20-07); Murmur South (20-08); Alangawari / Kafin / Larabawa (20-09); Maiwa (20-10); Mainako (20-11)
## Test (1 Oct 2026, on a fresh copy of live, with 075b in it)

- **Import:** dry run, then apply. Result: 212 ward units, 212 polling-unit statistics, 212 source links, 1 new source (INEC) and 2 gaps.
- **Publish:** 212 admin units.
- **Pages:** Bauchi LGA lists its 12 wards and Jama'are its 10. Both pages and Bauchi State return 200 with no PHP errors.
- **Sitemap:** 3,026, unchanged.
- **Bauchi QC:** 0 problems. The scope now includes 212 wards.
- **Undo:** unpublish, then roll back. Every table count matches the pre-import snapshot except activity_log, which has +212 publish entries.
