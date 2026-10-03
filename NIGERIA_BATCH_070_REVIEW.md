# Research batch 070 — Yobe: INEC wards

Researched 2026-10-01. Created in review; published only after your approval. Directory PDF via web.archive.org/web/2020id_/https://inecnigeria.org/wp-content/uploads/2019/02/PU_Directory_Revised_January_2015_Yobe.pdf (saved as `database/research/data/inec_yobe.pdf`); parsed with `inec_parse_directory.py` (all 17 LGA ward counts and polling-unit totals match INEC's summary table) into `data/inec_yobe_ras.json`; built with `inec_wards_batch.py yobe Yobe`. **INEC spells one LGA 'Tarmuwa'** (as does the federal profile); the archive's record is 'Tarmua', so the wards are attached to it and the LGA name is unchanged.


**Source:** INEC, *Directory of Polling Units: Yobe State* (revised January 2015), Tier 1, via the Internet Archive. INEC has removed the PDF from its site.

- **178 wards** in 17 LGAs, each with INEC's code (LGA-ward) and its number of polling units in 2015. Every LGA's ward count and polling-unit total matches the directory's summary table.
- Wards are listed on their LGA's page, with INEC's disclaimer. They have no pages of their own and stay out of the sitemap, search index, AI lookups and home-page lists (as for Benue).

| LGA | Wards | Polling units |
|---|---|---|
| Bade | 10 | 80 |
| Bursari | 10 | 92 |
| Damaturu | 11 | 91 |
| Fika | 10 | 93 |
| Fune | 13 | 165 |
| Geidam | 11 | 112 |
| Gujba | 10 | 99 |
| Gulani | 12 | 99 |
| Jakusko | 10 | 103 |
| Karasuwa | 10 | 92 |
| Machina | 10 | 65 |
| Nangere | 11 | 138 |
| Nguru | 10 | 88 |
| Potiskum | 10 | 157 |
| Tarmua | 10 | 52 |
| Yunusari | 10 | 99 |
| Yusufari | 10 | 89 |

Ward names per LGA (as INEC writes them):

- **Bade**: Dagona (01-01); Gwio-Kura (01-02); Katuzu (01-03); Lawan Audu/Lawan Al-Wali (01-04); Lawan Fannami (01-05); Lawan Musa (01-06); Sarkin Hausawa (01-07); Tagali/Sugum (01-08); Usur/Dawayo (01-09); Zangon Musa/Zango Umaru (01-10)
- **Bursari**: Bayamari (02-01); Damnawa/Juluri (02-02); Danani (02-03); Dapchi (02-04); Garun Dole / Garin Alkali (02-05); Guba (02-06); Guji / Metalari (02-07); Kaliyari (02-08); Kurnawa (02-09); Masaba (02-10)
- **Damaturu**: Bindigari/Fawari (03-01); Damakasu (03-02); Damaturu Central (03-03); Gabir/Maduri (03-04); Kalallawa/Gabai (03-05); Kukareta/Warsala (03-06); Maisandari/Waziri Ibrahim Estate (03-07); Murfa Kalam (03-08); Nayinawa (03-09); Njiwaji/Gwange (03-10); Sasawa/Kabaru (03-11)
- **Fika**: Daya/Chana (04-01); Fika/Anze (04-02); Gadaka/Shembire (04-03); Gudi / Dozi / Godo Woli (04-04); Janga / Boza / Fa. Sawa / T. Nanai (04-05); Mubi / Fusami / Garin Wayo (04-06); Ngalda/Dumbulwa (04-07); Shoye/Garin Aba (04-08); Turmi / Maluri (04-09); Zangaya/Mazawaun (04-10)
- **Fune**: Abakire / Ngenlshengele / Shamka (05-01); Alagarno (05-02); Borno Kiji/Ngarho/Bebbende (05-03); Damagum Town (05-04); Daura/Bulanyiwa/Dubbul/Bauwa (05-05); Dogon Kuka/Gishiwari/Gununu (05-06); Fune/Ngelzarma/Milbiyar/Lawan Kalam (05-07); Gaba Tasha/Aigada/Dumbulwa (05-08); Gudugurka/Marmar I (05-09); Jajere/Banellewa/Babbare (05-10); Kayeri (05-11); Kollere/Kafaje (05-12); Mashio (05-13)
- **Geidam**: Asheikri (06-01); Balle/Gallaba/Meleri (06-02); Damakarwa/Kusur (06-03); Dejina/Fukurti (06-04); Futchimiram (06-05); Gumsa (06-06); Hausari (06-07); Kawuri (06-08); Ma'Anna/Dagambi (06-09); Shame Kura / Dilawa (06-10); Zurgu Ngilewa / Borko (06-11)
- **Gujba**: Bunigari/Lawanti (07-01); Buniyadi North / Buniyadi South (07-02); Dadingel (07-03); Goniri (07-04); Gotala/Gotumba (07-05); Gujba (07-06); Mallam Dunari (07-07); Mutai (07-08); Ngurbuwa (07-09); Wagir (07-10)
- **Gulani**: Bara (08-01); Borno Kiji/Tetteba (08-02); Bularafa (08-03); Bumsa (08-04); Dokshi (08-05); Gabai (08-06); Gagure (08-07); Garin Tuwo (08-08); Gulani (08-09); Kushimaga (08-10); Njibulwa (08-11); Ruhu (08-12)
- **Jakusko**: Buduwa / Saminaka (09-01); Dumbari (09-02); Gidgid / Bayam (09-03); Gorgoram (09-04); Jaba (09-05); Jakusko (09-06); Jawur/Katamma (09-07); Lafiya Loi-Loi (09-08); Muguram (09-09); Zabudum / Dachia (09-10)
- **Karasuwa**: Bukarti (10-01); Fajiganari (10-02); Garin Gawo (10-03); Gasma (10-04); Jaji Maji (10-05); Karasuwa Galu (10-06); Karasuwa Garu Guna (10-07); Wachakal (10-08); Waro (10-09); Yajiri (10-10)
- **Machina**: Bogo (11-01); Damai (11-02); Dole (11-03); Falimaram (11-04); Kom-Komma (11-05); Kuka-Yasku (11-06); Lamisu (11-07); Machina-Kwari (11-08); Maskandare (11-09); Taganama (11-10)
- **Nangere**: Chilariye (12-01); Dadiso / Chukuriwa (12-02); Dawasa/G.Baba (12-03); Dazigau (12-04); Degubi (12-05); Kukuri/Chiromari (12-06); Langawa / Darin (12-07); Nangere (12-08); Pakarau Kare-Kare/ Pakarau Fulani (12-09); Tikau (12-10); Watinani (12-11)
- **Nguru**: Bulabulin (13-01); Bulanguwa (13-02); Dabule (13-03); Dumsai/Dogon-Kuka (13-04); Garbi/Bambori (13-05); Hausari (13-06); Kanuri (13-07); Maja-Kura (13-08); Mirba-Kabir/Mirba Sagir (13-09); Nglaiwa (13-10)
- **Potiskum**: Bare-Bare/Bauya/Lalai Dumbulwa (14-01); Bolewa 'A' (14-02); Bolewa 'B' (14-03); Danchuwa/Bula (14-04); Dogo Nini (14-05); Dogo Tebo (14-06); Hausawa (14-07); Mamudo (14-08); Ngojin/Alaraba (14-09); Yerimaram/Garin Daye/Badejo/Nahuta (14-10)
- **Tarmua**: Babangida (15-01); Barkami / Bulturi (15-02); Biriri/Churokusko (15-03); Jumbam (15-04); Koka/Sungul (15-05); Koriyel (15-06); Lantaiwa (15-07); Mafa (15-08); Mandadawa (15-09); Shekau (15-10)
- **Yunusari**: Bultuwa/Mar/Yaro (16-01); Daratoshia (16-02); Degaltura/Ngamzai (16-03); Dekwa (16-04); Dilala/Kalgi (16-05); Mairari (16-06); Mozogun/Kujari (16-07); Ngirabo (16-08); Wadi/Kafiya (16-09); Zajibiri / Dumbal (16-10)
- **Yusufari**: Alanjirori (17-01); Gumshi (17-02); Guya (17-03); Jebuwa (17-04); Kajimaram/Sumbar (17-05); Kaska/Tulotulowa (17-06); Kumagannam (17-07); Mai-Malari (17-08); Mayori (17-09); Yusufari (17-10)
## Test (1 Oct 2026, on a fresh copy of live, with 069b in it)

- **Import:** dry run, then apply. Result: 178 ward units, 178 polling-unit statistics, 178 source links, 1 new source (INEC) and 2 gaps.
- **Publish:** 178 admin units.
- **Pages:** Damaturu lists its 11 wards (codes 03-01 to 03-11) and Tarmua its 10 (15-01 to 15-10). Both pages and Yobe State return 200 with no PHP errors.
- **Sitemap:** 3,026, unchanged.
- **Yobe QC:** 0 problems. The scope now includes 178 wards.
- **Undo:** unpublish, then roll back. Every table count matches the pre-import snapshot except activity_log, which has +178 publish entries.
