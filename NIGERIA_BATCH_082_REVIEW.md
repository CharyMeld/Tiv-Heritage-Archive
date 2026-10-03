# INEC wards — Gombe State

**Source:** INEC, *Directory of Polling Units: Gombe State* (revised January 2015), Tier 1, via the Internet Archive. INEC has removed the PDF from its site.

- **114 wards** in 11 LGAs, each with INEC's code (LGA-ward) and its number of polling units in 2015. Every LGA's ward count and polling-unit total matches the directory's summary table.
- Wards are listed on their LGA's page, with INEC's disclaimer. They have no pages of their own and stay out of the sitemap, search index, AI lookups and home-page lists (as for Benue).

| LGA | Wards | Polling units |
|---|---|---|
| Akko | 11 | 338 |
| Balanga | 10 | 185 |
| Billiri | 10 | 132 |
| Dukku | 11 | 231 |
| Funakaye | 10 | 206 |
| Gombe | 11 | 189 |
| Kaltungo | 10 | 193 |
| Kwami | 10 | 207 |
| Nafada | 10 | 108 |
| Shomgom | 10 | 93 |
| Yamaltu/Deba | 11 | 336 |

Ward names per LGA (as INEC writes them):

- **Akko**: Akko (01-01); Garko (01-02); Kalshingi (01-03); Kashere (01-04); Kumo Central (01-05); Kumo East (01-06); Kumo North (01-07); Kumo West (01-08); Pindiga (01-09); Tumu (01-10); Tukulma (01-11)
- **Balanga**: Bambam (02-01); Bangu (02-02); Dadiya (02-03); Gelengu / Balanga (02-04); Kindiyo (02-05); Kulani / Degre /Sikkam (02-06); Mwona (02-07); Nyuwar / Jessu (02-08); Swa / Ref / W. Waja (02-09); Talasse / Dong / Reme (02-10)
- **Billiri**: Baganje North (03-01); Baganje South (03-02); Bare (03-03); Billiri North (03-04); Billiri South (03-05); Kalmai (03-06); Tal (03-07); Tanglang (03-08); Todi (03-09); Tudu Kwaya (03-10)
- **Dukku**: Gombe Abba (04-01); Hashidu (04-02); Jamari (04-03); Kunde (04-04); Lafiya (04-05); Malala (04-06); Waziri North (04-07); Waziri South / Central (04-08); Wuro Tale (04-09); Zange (04-10); Zaune (04-11)
- **Funakaye**: Ashaka / Magaba (05-01); Bage (05-02); Bajoga West (05-03); Bajoga East (05-04); Bodor / Tilde (05-05); Jillahi (05-06); Kupto (05-07); Ribadu (05-08); Tongo (05-09); Wawa / Wakkulutu (05-10)
- **Gombe**: Ajiya (06-01); Bajoga (06-02); Bolari East (06-03); Bolari West (06-04); Dawaki (06-05); Herwagana (06-06); Jeka Dafari (06-07); Kumbiya-Kumbiya (06-08); Nasarawa (06-09); Pantami (06-10); Shamaki (06-11)
- **Kaltungo**: Awak (07-01); Bule / Kaltin (07-02); Kaltungo West (07-03); Kaltungo East (07-04); Kamo (07-05); Tula Baule (07-06); Tula Wange (07-07); Tula-Yiri (07-08); Tungo (07-09); Ture (07-10)
- **Kwami**: Bojude (08-01); Daban Fulani (08-02); Doho (08-03); Dukul (08-04); Gadam (08-05); Jurara (08-06); Komfulata (08-07); Kwami (08-08); Malam Sidi (08-09); Malleri (08-10)
- **Nafada**: Barwo / Nasarawo (09-01); Barwo Winde (09-02); Birin Bolewa (09-03); Birin Fulani East (09-04); Birin Fulani West (09-05); Gudukku (09-06); Jigawa (09-07); Nafada Central (09-08); Nafada East (09-09); Nafada West (09-10)
- **Shomgom**: Bangunji (10-01); Boh (10-02); Burak (10-03); Filiya (10-04); Gundale (10-05); Gwandum (10-06); Kulishin (10-07); Kushi (10-08); Lalaipido (10-09); Lapan (10-10)
- **Yamaltu/Deba**: Deba (11-01); Difa / Lubo / Kinafa (11-02); Gwani / Shinga / Wade (11-03); Hinna (11-04); Jagali North (11-05); Jagali South (11-06); Kanawa / Wajari (11-07); Kuri /Lano / Lambam (11-08); Kwadon / Liji / Kurba (11-09); Nono / Kunwal / W. Birdeka (11-10); Zambul / Kwali (11-11)
## Test on a copy of the live database (2 Oct 2026, after 081b went live)

- **Import:** the dry run and the real import agree: 1 new source, 114 wards, 114 source links, 114 polling-unit statistics and 2 gaps.
- **Publish:** 114 admin units.
- **Pages:** the Akko, Shongom (INEC: Shomgom), Yamaltu/Deba and Gombe LGA pages return 200 with no PHP errors. Akko lists "Wards (INEC registration areas) (11)": Kumo Central, Pindiga and the rest.
- **Sitemap:** 3,026, unchanged (wards have no pages).
- **Gombe QC:** 0 problems; 114 wards now in scope.
- **Undo:** after unpublishing and rolling back, every table count matches the live copy. The only difference is +114 activity-log rows (the publish log), the known side effect.
- The PDF is saved as `data/inec_gombe.pdf` (Wayback 2020id_ of INEC's 2019/02 upload), and the parse is in `data/inec_gombe_ras.json`.
