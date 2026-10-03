# Research batch 032 — Nasarawa: the 147 INEC wards

Researched 26 September 2026. Phase 3, Nasarawa. Created in review; published only after your approval.

**Source:** INEC, *Directory of Polling Units: Nasarawa State* (revised January 2015), Tier 1, via the Internet Archive. INEC has removed the PDF from its site.

- **147 wards** in 13 LGAs, each with INEC's code (LGA-ward) and its number of polling units in 2015. Every LGA's ward count and polling-unit total matches the directory's summary table.
- Wards are listed on their LGA's page, with INEC's disclaimer. They have no pages of their own and stay out of the sitemap, search index, AI lookups and home-page lists (as for Benue).

| LGA | Wards | Polling units |
|---|---|---|
| Akwanga | 11 | 70 |
| Awe | 10 | 94 |
| Doma | 10 | 126 |
| Karu | 11 | 121 |
| Keana | 10 | 81 |
| Keffi | 10 | 68 |
| Kokona | 11 | 107 |
| Lafia | 13 | 256 |
| Nasarawa | 15 | 133 |
| Nassarawa Egon | 14 | 125 |
| Obi | 10 | 132 |
| Toto | 12 | 123 |
| Wamba | 10 | 59 |

Ward names per LGA (as INEC writes them):

- **Akwanga**: Anchobaba (01-01); Agyaga (01-02); Gwanje.Gwanje (01-03); Ancho Nighaan (01-04); Andaha (01-05); Nunku (01-06); Gudi (01-07); Moroa (01-08); Akwanga West (01-09); Akwanga East (01-10); Ningo / Bohar (01-11)
- **Awe**: Tunga (02-01); Makwangiji (02-02); Madaki (02-03); Galadima (02-04); Jangaru (02-05); Kanje/Abuni (02-06); Ribi (02-07); Azara (02-08); Wuse (02-09); Akiri (02-10)
- **Doma**: Alagye (03-01); Rukubi (03-02); Agbashi (03-03); Doka (03-04); Akpanaja (03-05); Madaki (03-06); Ungwan Sarki Dawaki (03-07); Madauchi (03-08); Ungwan Dan Galadima (03-09); Sabon Gari (03-10)
- **Karu**: Aso / Kodape (04-01); Agada/Bagaji (04-02); Karshi I (04-03); Karshi II (04-04); Kafin Shanu/Betti (04-05); Tattara/Kondoro (04-06); Gitata (04-07); Gurku/Kabusu (04-08); Uke (04-09); Panda / Kare (04-10); Karu (04-11)
- **Keana**: Iwagu (05-01); Amiri (05-02); Obene (05-03); Oki (05-04); Kadarko (05-05); Kwara (05-06); Aloshi (05-07); Agaza (05-08); Madaki (05-09); Giza Galadima (05-10)
- **Keffi**: Angwan Iya I (06-01); Angwan Iya II (06-02); Tudun Kofa T.V (06-03); Gangare Tudu (06-04); Keffi Town East / Kofar Goriya (06-05); Yara (06-06); Ang. Rimi (06-07); Sabon Gari (06-08); Jigwada (06-09); Liman Abaji (06-10)
- **Kokona**: Agwada (07-01); Koya / Kana (07-02); Bassa (07-03); Kokona (07-04); Kofar Gwari (07-05); Ninkoro (07-06); Hadari (07-07); Dari (07-08); Amba (07-09); Garaku (07-10); Yelwa (07-11)
- **Lafia**: Adogi (08-01); Agyaragun Tofa (08-02); Bakin Rijiya/Akurba/Sarkin Pada (08-03); Arikya (08-04); Assakio (08-05); Ashigie (08-06); Chiroma (08-07); Gayam (08-08); Keffin/Wambai (08-09); Makama (08-10); Shabu/Kwandere (08-11); Wakwa (08-12); Zanwa (08-13)
- **Nasarawa**: Udenin Gida (09-01); Akum (09-02); Udenin (09-03); Loko (09-04); Tunga/Bakono (09-05); Guto/Aisa (09-06); Nasarawa North (09-07); Nasarawa East (09-08); Nasarawa Central (09-09); Nasarawa Main Town (09-10); Ara I (09-11); Ara II (09-12); Laminga (09-13); Kanah/Ondo/Apawu (09-14); Odu (09-15)
- **Nassarawa Egon**: Nasarawa Eggon (10-01); Ubbe (10-02); Igga/Burumburum (10-03); Umme (10-04); Mada Station (10-05); Lizzin Keffi/Ezzen (10-06); Lambaga/Arikpa (10-07); Kagbu Wana (10-08); Ikka Wangibi (10-09); Ende (10-10); Wakama (10-11); Aloce/Ginda (10-12); Alogani (10-13); Agunji (10-14)
- **Obi**: Agwatashi (11-01); Deddere/Riri (11-02); Obi (11-03); Tudun Adabu (11-04); Duduguru (11-05); Gwadenye (11-06); Kyakale (11-07); Gidan Ausa I (11-08); Gidan Ausa II (11-09); Adudu (11-10)
- **Toto**: Gwargwada (12-01); Gadagwa (12-02); Bugakarmo (12-03); Umaisha (12-04); Dausu (12-05); Kanyehu (12-06); Ugya (12-07); Toto (12-08); Shafan Abakpa (12-09); Shafan Kwato (12-10); Shege (12-11); Katakpa (12-12)
- **Wamba**: Arum (13-01); Mangar (13-02); Gitta (13-03); Nakere (13-04); Konvah (13-05); Wayo (13-06); Wamba West (13-07); Wamba East (13-08); Kwara (13-09); Jimiya (13-10)
## Tool change

- **`database/research/inec_wards_batch.py`:** a reusable builder for ward batches. Each state's wards are now built the same way, from its parsed INEC directory (`data/inec_<state>_ras.json`). The parsing and summary-table checks are the same as for Benue.

## Tests (copy of the live database, MySQL 8.0.46)

- **Import:** the dry run is clean (1 source, 147 wards, 147 figures, 2 gaps).
- **IDs:** 147 unique, from `NG-WARD-NASARAWA-AKWANGA-01-01` to `NG-WARD-NASARAWA-WAMBA-13-10`.
- **Pages after publishing on the copy:**
  - Lafia's page lists its 13 wards (e.g. "Adogi · code 08-01 · 14 polling units"), followed by INEC's disclaimer.
  - A ward address returns 404, as intended.
  - The sitemap is **unchanged at 3,023**, and there are 0 PHP errors.
- **Quality check:** no duplicates, including ward names within an LGA.
- **Full undo:** the rollback gives a database identical to a fresh copy of the live one.

## Deploy (on your approval)

1. Back up the database.
2. Copy the builder and data.
3. Import (dry run, then apply).
4. Publish.
5. Clear the cache.
6. Check the pages and the sitemap.
