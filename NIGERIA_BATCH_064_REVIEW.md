# Research batch 064 — Borno: INEC wards

Researched 2026-10-01. Created in review; published only after your approval. Directory PDF via web.archive.org/web/2020id_/https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_Borno.pdf (saved as `database/research/data/inec_borno.pdf`); parsed with `inec_parse_directory.py` (all 27 LGA ward counts and polling-unit totals match INEC's summary table) into `data/inec_borno_ras.json`; built with `inec_wards_batch.py borno Borno`.


**Source:** INEC, *Directory of Polling Units: Borno State* (revised January 2015), Tier 1, via the Internet Archive. INEC has removed the PDF from its site.

- **312 wards** in 27 LGAs, each with INEC's code (LGA-ward) and its number of polling units in 2015. Every LGA's ward count and polling-unit total matches the directory's summary table.
- Wards are listed on their LGA's page, with INEC's disclaimer. They have no pages of their own and stay out of the sitemap, search index, AI lookups and home-page lists (as for Benue).

| LGA | Wards | Polling units |
|---|---|---|
| Abadam | 10 | 64 |
| Askira/Uba | 13 | 238 |
| Bama | 14 | 285 |
| Bayo | 10 | 59 |
| Biu | 11 | 197 |
| Chibok | 11 | 95 |
| Damboa | 10 | 100 |
| Dikwa | 10 | 89 |
| Gubio | 10 | 108 |
| Guzamala | 10 | 72 |
| Gwoza | 13 | 260 |
| Hawul | 12 | 162 |
| Jere | 12 | 239 |
| Kaga | 15 | 93 |
| Kala/Balge | 10 | 93 |
| Konduga | 11 | 189 |
| Kukawa | 10 | 76 |
| Kwaya Kusar | 10 | 75 |
| Mafa | 12 | 83 |
| Magumeri | 13 | 71 |
| Maiduguri | 15 | 723 |
| Marte | 13 | 109 |
| Mobbar | 10 | 71 |
| Monguno | 12 | 73 |
| Ngala | 12 | 105 |
| Nganzai | 12 | 85 |
| Shani | 11 | 119 |

Ward names per LGA (as INEC writes them):

- **Abadam**: Arege (01-01); Banowa (01-02); Fuguwa (01-03); Jabullam (01-04); Kudokurgu (01-05); Mallamfatori Kessa (01-06); Malam Kaunari (01-07); Yau (01-08); Yawa Kura (01-09); Yituwa (01-10)
- **Askira/Uba**: Askira East (02-01); Chul / Rumirgo (02-02); Dille / Huyum (02-03); Husara / Tampul (02-04); Kopa / Multhafu (02-05); Lassa (02-06); Mussa (02-07); Ngohi (02-08); Ngulde (02-09); Uba (02-10); Uda / Uvu (02-11); Wamdeo / Giwi (02-12); Zadawa / Hausari (02-13)
- **Bama**: Andara / Ajiri /Wulba (03-01); Buduwa / Bula Chirabe (03-02); Dipchari / Jere / Dar-Jamal / Kotembe (03-03); Gulumba / Jukkuri / Batra (03-04); Kasugula (03-05); Kumshe /Nduguno (03-06); Lawanti / Malam / Mastari / Abbaram (03-07); Marka / Malge / Amchaka (03-08); Mbuliya / Goniri / Siraja (03-09); Sabsabwa / Soye/ Bulongu (03-10); Shehuri / Hausari / Mairi (03-11); Wulbari/Ndine/Chachile (03-12); Yabiri Kura/Yabiri Gana/Chongolo (03-13); Zangeri/Kash Kash (03-14)
- **Bayo**: Balbaya (04-01); Briyel (04-02); Fikayel (04-03); Gamadadi (04-04); Jara Gol (04-05); Jara Dali (04-06); Limanti (04-07); Teli (04-08); Wuyo (04-09); Zara (04-10)
- **Biu**: Buratai (05-01); Dadin Kowa (05-02); Dugja (05-03); Garubula (05-04); Gur (05-05); Kenken (05-06); Mandara Girau (05-07); Miringa (05-08); Sulumthla (05-09); Yawi (05-10); Zarawuyaku (05-11)
- **Chibok**: Chibok Garu (06-01); Chibok Likama (06-02); Chibok Wuntaku (06-03); Gatamarwa (06-04); Kautikari (06-05); Korongilim (06-06); Kuburmbula (06-07); Mbalala (06-08); Mboa Kura (06-09); Shikarkir (06-10); Pemi (06-11)
- **Damboa**: Ajign (A) (07-01); Ajign (B) (07-02); Azur/Multe/Forfor (07-03); Bego/Yerwa/Ngurna (07-04); Damboa (07-05); Gumsuri/Misakurbudu (07-06); Kafa / Mafi (07-07); Mulgwai / Kopchi (07-08); Nguda / Wuyaram (07-09); Wawa / Korede (07-10)
- **Dikwa**: Boboshe (08-01); Dikwa (08-02); Gajibo (08-03); Ufaye / Gujile (08-04); Muliye / Jemuri (08-05); Mudu / Kaza (08-06); Mallam Maja (08-07); Ngudoram (08-08); Magarta / Sheffri (08-09); Sogoma / Afuye (08-10)
- **Gubio**: Ardimini (09-01); Dabira (09-02); Felo (09-03); Gamowo (09-04); Gazabure (09-05); Gubio Town I (09-06); Gubio Town II (09-07); Kingowa (09-08); Ngetra (09-09); Zowo (09-10)
- **Guzamala**: Aduwa (10-01); Gudumbali East (10-02); Gudumbali West (10-03); Guworam (10-04); Guzamala East (10-05); Guzamala West (10-06); Kingarwa (10-07); Mairari (10-08); Moduri (10-09); Wamiri (10-10)
- **Gwoza**: Ashigashiya (11-01); Bita / Izge (11-02); Dure / Wala / Warabe (11-03); Gavva / Agapalwa (11-04); Guduf Nagadiyo (11-05); Gwoza Town Gadamayo (11-06); Gwoza Wakane / Bulabulin (11-07); Hambagda/ Liman Kara/ New Settlement (11-08); Johode/Chikide/Kughum (11-09); Kirawa/Jimini (11-10); Kurana Bassa/Ngoshe-Sama'a (11-11); Ngoshe (11-12); Pulka/Bokko (11-13)
- **Hawul**: Bilingwi (12-01); Dzar/ VInadum/ Birni/ Dlandi (12-02); Gwanzang Pusda (12-03); Hizhi (12-04); Kida (12-05); Kwajaffa/Hang (12-06); Kwaya-Bur/Tanga Rumta (12-07); Marama/Kidang (12-08); Pama/Whitambaya (12-09); Puba/VIdau/Lokoja (12-10); Sakwa/Hema (12-11); Shaffa (12-12)
- **Jere**: Alau (13-01); Bale Galtimari (13-02); Dala Lawanti (13-03); Dusuman (13-04); Gongulong (13-05); Maimusari (13-06); Mashamari (13-07); Ngudaa/Addamari (13-08); Old Maiduguri (13-09); Tuba (13-10); Mairi (13-11); Gomari (13-12)
- **Kaga**: Afa/Dig/Maudori (14-01); Benisheikh (14-02); Borgozo (14-03); Dogoma / Jalori (14-04); Dongo (14-05); Galangi (14-06); Guwo (14-07); Karagawaru (14-08); Mainok (14-09); Marguba (14-10); Ngamdu (14-11); Shettimari (14-12); Tobolo (14-13); Wajiro / Burgumma (14-14); Wassaram (14-15)
- **Kala/Balge**: Moholo (15-01); Jilbe "A" (15-02); Jilbe "B"/Koma Kaudi (15-03); Jarawa/Sangaya (15-04); Kala (15-05); Kumaga (15-06); Rann "A" (15-07); Rann "B''/Daima (15-08); Mada (15-09); Sigal/Karche (15-10)
- **Konduga**: Auno / Chabbol (16-01); Dalori / Wanori (16-02); Dawa East / Malari / Kangamari (16-03); Jewu / Lamboa (16-04); Kawuri (16-05); Kelumiri / Ngalbi Amari / Yale (16-06); Konduga (16-07); Mairamri / Yeleri / Bazamri (16-08); Masba / Dalwa West (16-09); Nyaleri/Sandia/Yejiwa (16-10); Sojiri/ Nguro-Nguro (16-11)
- **Kukawa**: Alagarno (17-01); Baga (17-02); Bundur (17-03); Dogoshi (17-04); Doro / Duguri (17-05); Kauwa (17-06); Kekeno (17-07); Kukawa (17-08); Moduari / Barwari (17-09); Yoyo (17-10)
- **Kwaya Kusar**: Gondi (18-01); Gusi / Billa (18-02); Guwal (18-03); Kubuku (18-04); Kurba (18-05); Kwaya Kusar (18-06); Peta (18-07); Wada (18-08); Wawa (18-09); Yimirthalang (18-10)
- **Mafa**: Abbari (19-01); Anadua (19-02); Gawa (19-03); Koshebe (19-04); Laje (19-05); Limanti (19-06); Loskuri (19-07); Ma'afa (19-08); Mafa (19-09); Masu (19-10); Mujigine (19-11); Tamsu Ngamdua (19-12)
- **Magumeri**: Ardo Ram (20-01); Ayi / Yasku (20-02); Borno Yesu (20-03); Furram (20-04); Gaji Ganna I (20-05); Gaji Ganna II (20-06); Hoyo / Chin Gowa (20-07); Kalizoram / Banoram (20-08); Kareram (20-09); Kubti (20-10); Magumeri (20-11); Ngamma (20-12); Ngubala (20-13)
- **Maiduguri**: Bolori I (21-01); Bolori II (21-02); Bulablin (21-03); Fezzan (21-04); Gamboru Liberty (21-05); Gwange I (21-06); Gwange II (21-07); Gwange III (21-08); Hausari/Zango (21-09); Lamisula/Jabba Mari (21-10); Limanti (21-11); Mafoni (21-12); Maisandari (21-13); Shehuri North (21-14); Shehuri South (21-15)
- **Marte**: Ala (22-01); Alla Lawanti (22-02); Borsori (22-03); Gumna (22-04); Kabulawa (22-05); Kirenowa (22-06); Kulli (22-07); Marte (22-08); Mawulli (22-09); Musune (22-10); Ngeleiwa (22-11); Njine (22-12); Zaga (22-13)
- **Mobbar**: Asaga (23-01); Bogum (23-02); Chamba (23-03); Damasak (23-04); Duji (23-05); Gashagar (23-06); Kareto (23-07); Layi (23-08); Zanna Umorti (23-09); Zari (23-10)
- **Monguno**: Damakuli (24-01); Kaguram (24-02); Kumalia (24-03); Mofio (24-04); Mandala (24-05); Mintar (24-06); Monguno (24-07); Ngurno (24-08); Sure (24-09); Wulo (24-10); Yele (24-11); Zulum (24-12)
- **Ngala**: Sagir (25-01); Fuye (25-02); Gamboru 'B' (25-03); Gamboru 'C' (25-04); Logumane (25-05); Ndufu (25-06); Ngala Ward (25-07); Old Gamboru 'A' (25-08); Tunokalia (25-09); Warshele (25-10); Wulgo (25-11); Wurge (25-12)
- **Nganzai**: Alarge (26-01); Badu (26-02); Damaram (26-03); Gajiram (26-04); Gadai (26-05); Jigalta (26-06); Kuda (26-07); Kurnawa (26-08); Maiwa (26-09); Miye (26-10); Sabsabuwa (26-11); Sugundure (26-12)
- **Shani**: Bargu / Burashika (27-01); Buma (27-02); Gasi / Salifawa (27-03); Gora (27-04); Gwalasho (27-05); Gwaskara (27-06); Kombo (27-07); Kubo (27-08); Kwaba (27-09); Shani (27-10); Walama (27-11)
## Test (1 Oct 2026, on a fresh copy of live, with 063b in it)

- **Import:** dry run, then apply. Result: 312 ward units, 312 polling-unit statistics, 312 source links, 1 new source (INEC) and 2 gaps.
- **Publish:** 312 admin units.
- **Pages:** the Maiduguri, Gwoza and Kwaya Kusar LGA pages and Borno State return 200 with no PHP errors. Maiduguri lists its wards with INEC codes 21-01 to 21-15.
- **Sitemap:** 3,026, unchanged. Wards have no pages of their own.
- **Borno QC:** 0 problems. The scope now includes 312 wards.
- **Undo:** unpublish, then roll back. Every table count matches the pre-import snapshot except activity_log, which has +312 publish entries. Wards are not search-indexed, so the search index is unchanged.
