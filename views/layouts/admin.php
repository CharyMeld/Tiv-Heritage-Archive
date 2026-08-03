<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="site-url" content="<?= SITE_URL ?>">
    <title><?= e($title ?? 'Admin') ?> | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= asset('css/styles.css') ?>">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <!-- Top Navigation Bar -->
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button class="admin-menu-toggle" id="adminMenuToggle" aria-label="Toggle menu">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" x2="21" y1="6" y2="6"/>
                        <line x1="3" x2="21" y1="12" y2="12"/>
                        <line x1="3" x2="21" y1="18" y2="18"/>
                    </svg>
                </button>
                <a href="<?= url('admin') ?>" class="admin-topbar-brand">
                    <span class="admin-topbar-brand-name">TIV ARCHIVE</span>
                    <span class="admin-topbar-brand-sub">Admin Panel</span>
                </a>
            </div>
            <nav class="admin-topnav" id="adminTopNav">
                <a href="<?= url('admin') ?>" class="admin-topnav-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="<?= url('admin/pending') ?>" class="admin-topnav-link <?= ($currentPage ?? '') === 'pending' ? 'active' : '' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span>Pending</span>
                </a>
                <div class="admin-topnav-dropdown">
                    <button class="admin-topnav-link admin-dropdown-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        <span>Content</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dropdown-arrow"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="admin-dropdown-menu">
                        <a href="<?= url('admin/content/names') ?>" class="<?= ($currentPage ?? '') === 'names' ? 'active' : '' ?>">Names</a>
                        <a href="<?= url('admin/content/proverbs') ?>" class="<?= ($currentPage ?? '') === 'proverbs' ? 'active' : '' ?>">Proverbs</a>
                        <a href="<?= url('admin/content/plants') ?>" class="<?= ($currentPage ?? '') === 'plants' ? 'active' : '' ?>">Plants</a>
                        <a href="<?= url('admin/content/festivals') ?>" class="<?= ($currentPage ?? '') === 'festivals' ? 'active' : '' ?>">Festivals</a>
                        <a href="<?= url('admin/content/foods') ?>" class="<?= ($currentPage ?? '') === 'foods' ? 'active' : '' ?>">Foods</a>
                        <a href="<?= url('admin/content/animals') ?>" class="<?= ($currentPage ?? '') === 'animals' ? 'active' : '' ?>">Animals</a>
                        <a href="<?= url('admin/content/words') ?>" class="<?= ($currentPage ?? '') === 'words' ? 'active' : '' ?>">Words</a>
                        <a href="<?= url('admin/content/videos') ?>" class="<?= ($currentPage ?? '') === 'videos' ? 'active' : '' ?>">Videos</a>
                        <hr style="border:none;border-top:1px solid rgba(255,255,255,.1);margin:.3rem 0;">
                        <a href="<?= url('admin/bible') ?>" class="<?= str_starts_with($currentPage ?? '', 'bible') ? 'active' : '' ?>">&#128214; Bible (Tiv Entry)</a>
                    </div>
                </div>
                <div class="admin-topnav-dropdown">
                    <button class="admin-topnav-link admin-dropdown-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3M4.22 4.22l2.12 2.12m11.32 11.32 2.12 2.12M2 12h3m14 0h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"/></svg>
                        <span>Knowledge</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dropdown-arrow"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="admin-dropdown-menu">
                        <a href="<?= url('admin/sources') ?>" class="<?= ($currentPage ?? '') === 'sources' ? 'active' : '' ?>">&#128218; Sources</a>
                        <a href="<?= url('admin/links') ?>" class="<?= ($currentPage ?? '') === 'links' ? 'active' : '' ?>">&#127760; Graph Links</a>
                        <a href="<?= url('references') ?>" target="_blank">&#128279; View Public Page</a>
                    </div>
                </div>
                <div class="admin-topnav-dropdown">
                    <button class="admin-topnav-link admin-dropdown-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="m22 22-5-10-5 10"/><path d="M14 18h6"/></svg>
                        <span>Translate</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dropdown-arrow"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="admin-dropdown-menu">
                        <a href="<?= url('admin/translation') ?>"            class="<?= ($currentPage ?? '') === 'translation' ? 'active' : '' ?>">&#128202; Logs &amp; Stats</a>
                        <a href="<?= url('admin/translation/phrases') ?>"    class="<?= ($currentPage ?? '') === 'translation_phrases' ? 'active' : '' ?>">&#128172; Phrases</a>
                        <a href="<?= url('admin/translation/rules') ?>"      class="<?= ($currentPage ?? '') === 'translation_rules' ? 'active' : '' ?>">&#9881; Rules</a>
                        <a href="<?= url('admin/translation/feedback') ?>"   class="<?= ($currentPage ?? '') === 'translation_feedback' ? 'active' : '' ?>">&#128172; Feedback</a>
                        <a href="<?= url('translate') ?>" target="_blank">&#127760; Open Translator</a>
                    </div>
                </div>
                <div class="admin-topnav-dropdown">
                    <button class="admin-topnav-link admin-dropdown-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        <span>Sections</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dropdown-arrow"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="admin-dropdown-menu">
                        <a href="<?= url('admin/alphabet') ?>" class="<?= ($currentPage ?? '') === 'alphabet' ? 'active' : '' ?>">&#127279; Alphabet Manager</a>
                        <a href="<?= url('admin/content-items/literature/folktales') ?>">&#127919; Folktales</a>
                        <a href="<?= url('admin/content-items/literature/stories') ?>">&#128196; Stories</a>
                        <a href="<?= url('admin/content-items/literature/poems') ?>">&#128145; Poems</a>
                        <a href="<?= url('admin/content-items/culture/traditions') ?>">&#127981; Traditions</a>
                        <a href="<?= url('admin/content-items/culture/attire') ?>">&#128255; Attire</a>
                        <a href="<?= url('admin/content-items/culture/marriage-customs') ?>">&#128149; Marriage Customs</a>
                        <a href="<?= url('admin/content-items/history/origins') ?>">&#127758; Origins</a>
                        <a href="<?= url('admin/content-items/history/migration') ?>">&#128667; Migration</a>
                        <a href="<?= url('admin/historical-figures') ?>">&#129332; Historical Figures</a>
                        <a href="<?= url('admin/content-items/history/timeline') ?>">&#128337; Timeline</a>
                        <a href="<?= url('admin/content-items/archive/documents') ?>">&#128196; Documents</a>
                        <a href="<?= url('admin/content-items/archive/audio') ?>">&#127911; Audio Recordings</a>
                        <a href="<?= url('admin/content-items/archive/publications') ?>">&#128214; Publications</a>
                    </div>
                </div>
                <div class="admin-topnav-dropdown">
                    <button class="admin-topnav-link admin-dropdown-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>Community</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dropdown-arrow"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="admin-dropdown-menu">
                        <a href="<?= url('admin/community/applications') ?>" class="<?= str_starts_with($currentPage ?? '', 'community_applications') ? 'active' : '' ?>">&#128196; Applications</a>
                        <a href="<?= url('admin/community/members') ?>" class="<?= str_starts_with($currentPage ?? '', 'community_members') ? 'active' : '' ?>">&#128101; Members Directory</a>
                        <a href="<?= url('admin/contributors') ?>" class="<?= str_starts_with($currentPage ?? '', 'contributors') ? 'active' : '' ?>">&#128176; Contributors &amp; Payments</a>
                        <a href="<?= url('community') ?>" target="_blank">&#127760; View Public Page</a>
                    </div>
                </div>
                <div class="admin-topnav-dropdown">
                    <button class="admin-topnav-link admin-dropdown-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <span>Outreach</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dropdown-arrow"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="admin-dropdown-menu">
                        <a href="<?= url('admin/outreach') ?>" class="<?= ($currentPage ?? '') === 'outreach' ? 'active' : '' ?>">&#127757; Dashboard</a>
                        <a href="<?= url('admin/outreach/people') ?>">&#128101; Influential People</a>
                        <a href="<?= url('admin/outreach/nominations') ?>">&#128203; Nominations</a>
                        <a href="<?= url('admin/outreach/templates') ?>">&#128196; Email Templates</a>
                        <a href="<?= url('admin/outreach/campaigns') ?>">&#128231; Campaigns</a>
                        <a href="<?= url('admin/outreach/discovery') ?>">&#128269; Discovery</a>
                        <hr style="border:none;border-top:1px solid rgba(255,255,255,.1);margin:.3rem 0;">
                        <a href="<?= url('nominate-influential') ?>" target="_blank">&#128279; Public Nominate Page</a>
                    </div>
                </div>
                <div class="admin-topnav-dropdown">
                    <button class="admin-topnav-link admin-dropdown-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        <span>Marketing</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="dropdown-arrow"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="admin-dropdown-menu">
                        <a href="<?= url('admin/marketing') ?>" class="<?= ($currentPage ?? '') === 'marketing_dashboard' ? 'active' : '' ?>">&#128202; Dashboard</a>
                        <a href="<?= url('admin/marketing/activity') ?>" class="<?= ($currentPage ?? '') === 'marketing_activity' ? 'active' : '' ?>">&#128225; Activity Log</a>
                        <a href="<?= url('admin/marketing/generator') ?>" class="<?= ($currentPage ?? '') === 'marketing_generator' ? 'active' : '' ?>">&#129302; Content Generator</a>
                        <a href="<?= url('admin/marketing/templates') ?>" class="<?= ($currentPage ?? '') === 'marketing_templates' ? 'active' : '' ?>">&#128196; Prompt Templates</a>
                        <a href="<?= url('admin/marketing/social') ?>" class="<?= ($currentPage ?? '') === 'marketing_social' ? 'active' : '' ?>">&#128241; Social Media</a>
                        <a href="<?= url('admin/marketing/images') ?>" class="<?= ($currentPage ?? '') === 'marketing_images' ? 'active' : '' ?>">&#128444; Image Generator</a>
                        <a href="<?= url('admin/marketing/calendar') ?>" class="<?= ($currentPage ?? '') === 'marketing_scheduled' ? 'active' : '' ?>">&#128197; Scheduled Posts</a>
                        <a href="<?= url('admin/marketing/facebook/queue') ?>" class="<?= ($currentPage ?? '') === 'marketing_queue' ? 'active' : '' ?>">&#128257; Publishing Queue</a>
                        <a href="<?= url('admin/marketing/campaigns') ?>" class="<?= ($currentPage ?? '') === 'marketing_campaigns' ? 'active' : '' ?>">&#128231; Email Campaigns</a>
                        <a href="<?= url('admin/marketing/newsletter') ?>" class="<?= ($currentPage ?? '') === 'marketing_newsletter' ? 'active' : '' ?>">&#128240; Newsletter</a>
                        <a href="<?= url('admin/marketing/analytics') ?>" class="<?= ($currentPage ?? '') === 'marketing_analytics' ? 'active' : '' ?>">&#128200; Analytics</a>
                        <a href="<?= url('admin/marketing/traffic') ?>" class="<?= ($currentPage ?? '') === 'marketing_traffic' ? 'active' : '' ?>">&#128279; Traffic Reports</a>
                        <a href="<?= url('admin/marketing/facebook/settings') ?>" class="<?= ($currentPage ?? '') === 'marketing_settings' ? 'active' : '' ?>">&#9881; Settings</a>
                    </div>
                </div>
                <a href="<?= url('admin/suggestions') ?>" class="admin-topnav-link <?= str_starts_with($currentPage ?? '', 'suggestions') ? 'active' : '' ?>" title="Suggestions">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Suggestions</span>
                </a>
                <a href="<?= url('admin/api-keys') ?>" class="admin-topnav-link <?= ($currentPage ?? '') === 'api_keys' ? 'active' : '' ?>" title="API Keys">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3L22 7l-3-3"/></svg>
                    <span>API Keys</span>
                </a>
            </nav>
            <form class="admin-topbar-search" method="GET" action="<?= url('admin/search') ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input type="text" name="q" placeholder="Search all content..."
                       value="<?= e($_GET['q'] ?? '') ?>">
            </form>
            <div class="admin-topbar-right">
                <a href="<?= url('/') ?>" class="admin-topbar-btn" title="View Site">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                </a>
                <a href="<?= url('logout') ?>" class="admin-topbar-btn" title="Logout">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                </a>
            </div>
        </header>

        <!-- Mobile / Tablet Accordion Drawer (hidden ≥ 1200px) -->
        <div class="adnav-overlay" id="adnavOverlay"></div>
        <nav class="adnav-drawer" id="adnavDrawer" aria-label="Admin navigation" aria-hidden="true">
            <div class="adnav-hdr">
                <p class="adnav-hdr-title">&#9776; Admin Menu</p>
                <button class="adnav-x" id="adnavClose" aria-label="Close">&times;</button>
            </div>
            <ul class="adnav-list">

                <li><a href="<?= url('admin') ?>" class="adnav-plain <?= ($currentPage??'')==='dashboard'?'adnav-active':'' ?>">&#9671; Dashboard</a></li>
                <li><a href="<?= url('admin/pending') ?>" class="adnav-plain <?= ($currentPage??'')==='pending'?'adnav-active':'' ?>">&#9203; Pending</a></li>
                <li><a href="<?= url('admin/search') ?>" class="adnav-plain">&#128269; Search Content</a></li>
                <li><div class="adnav-sep"></div></li>

                <!-- Content -->
                <li>
                    <button class="adnav-btn" aria-expanded="false" aria-controls="adn-content">
                        <span>&#128218; Content</span><span class="adnav-arr">&#9660;</span>
                    </button>
                    <ul class="adnav-sub" id="adn-content">
                        <li><a href="<?= url('admin/content/names') ?>">Names</a></li>
                        <li><a href="<?= url('admin/content/proverbs') ?>">Proverbs</a></li>
                        <li><a href="<?= url('admin/content/plants') ?>">Plants</a></li>
                        <li><a href="<?= url('admin/content/festivals') ?>">Festivals</a></li>
                        <li><a href="<?= url('admin/content/foods') ?>">Foods</a></li>
                        <li><a href="<?= url('admin/content/animals') ?>">Animals</a></li>
                        <li><a href="<?= url('admin/content/words') ?>">Words</a></li>
                        <li><a href="<?= url('admin/content/videos') ?>">Videos</a></li>
                        <li><a href="<?= url('admin/bible') ?>">&#128214; Bible (Tiv Entry)</a></li>
                    </ul>
                </li>

                <!-- Sections -->
                <li>
                    <button class="adnav-btn" aria-expanded="false" aria-controls="adn-sections">
                        <span>&#128196; Sections</span><span class="adnav-arr">&#9660;</span>
                    </button>
                    <ul class="adnav-sub" id="adn-sections">
                        <li><a href="<?= url('admin/alphabet') ?>">&#127279; Alphabet Manager</a></li>
                        <li><a href="<?= url('admin/content-items/literature/folktales') ?>">&#127919; Folktales</a></li>
                        <li><a href="<?= url('admin/content-items/literature/stories') ?>">&#128196; Stories</a></li>
                        <li><a href="<?= url('admin/content-items/literature/poems') ?>">&#128145; Poems</a></li>
                        <li><a href="<?= url('admin/content-items/culture/traditions') ?>">&#127981; Traditions</a></li>
                        <li><a href="<?= url('admin/content-items/culture/attire') ?>">&#128255; Attire</a></li>
                        <li><a href="<?= url('admin/content-items/culture/marriage-customs') ?>">&#128149; Marriage Customs</a></li>
                        <li><a href="<?= url('admin/content-items/history/origins') ?>">&#127758; Origins</a></li>
                        <li><a href="<?= url('admin/content-items/history/migration') ?>">&#128667; Migration</a></li>
                        <li><a href="<?= url('admin/historical-figures') ?>">&#129332; Historical Figures</a></li>
                        <li><a href="<?= url('admin/content-items/history/timeline') ?>">&#128337; Timeline</a></li>
                        <li><a href="<?= url('admin/content-items/archive/documents') ?>">&#128196; Documents</a></li>
                        <li><a href="<?= url('admin/content-items/archive/audio') ?>">&#127911; Audio Recordings</a></li>
                        <li><a href="<?= url('admin/content-items/archive/publications') ?>">&#128214; Publications</a></li>
                    </ul>
                </li>

                <!-- Knowledge -->
                <li>
                    <button class="adnav-btn" aria-expanded="false" aria-controls="adn-knowledge">
                        <span>&#128300; Knowledge</span><span class="adnav-arr">&#9660;</span>
                    </button>
                    <ul class="adnav-sub" id="adn-knowledge">
                        <li><a href="<?= url('admin/sources') ?>">&#128218; Sources</a></li>
                        <li><a href="<?= url('admin/links') ?>">&#127760; Graph Links</a></li>
                    </ul>
                </li>

                <!-- Translate -->
                <li>
                    <button class="adnav-btn" aria-expanded="false" aria-controls="adn-translate">
                        <span>&#127760; Translate</span><span class="adnav-arr">&#9660;</span>
                    </button>
                    <ul class="adnav-sub" id="adn-translate">
                        <li><a href="<?= url('admin/translation') ?>">&#128202; Logs &amp; Stats</a></li>
                        <li><a href="<?= url('admin/translation/phrases') ?>">&#128172; Phrases</a></li>
                        <li><a href="<?= url('admin/translation/rules') ?>">&#9881; Rules</a></li>
                        <li><a href="<?= url('admin/translation/feedback') ?>">&#128172; Feedback</a></li>
                    </ul>
                </li>

                <!-- Community -->
                <li>
                    <button class="adnav-btn" aria-expanded="false" aria-controls="adn-community">
                        <span>&#128101; Community</span><span class="adnav-arr">&#9660;</span>
                    </button>
                    <ul class="adnav-sub" id="adn-community">
                        <li><a href="<?= url('admin/community/applications') ?>">&#128196; Applications</a></li>
                        <li><a href="<?= url('admin/community/members') ?>">&#128101; Members Directory</a></li>
                        <li><a href="<?= url('admin/contributors') ?>">&#128176; Contributors &amp; Payments</a></li>
                    </ul>
                </li>

                <!-- Outreach -->
                <li>
                    <button class="adnav-btn" aria-expanded="false" aria-controls="adn-outreach">
                        <span>&#128231; Outreach</span><span class="adnav-arr">&#9660;</span>
                    </button>
                    <ul class="adnav-sub" id="adn-outreach">
                        <li><a href="<?= url('admin/outreach') ?>">&#127757; Dashboard</a></li>
                        <li><a href="<?= url('admin/outreach/people') ?>">&#128101; Influential People</a></li>
                        <li><a href="<?= url('admin/outreach/nominations') ?>">&#128203; Nominations</a></li>
                        <li><a href="<?= url('admin/outreach/templates') ?>">&#128196; Email Templates</a></li>
                        <li><a href="<?= url('admin/outreach/campaigns') ?>">&#128231; Campaigns</a></li>
                        <li><a href="<?= url('admin/outreach/discovery') ?>">&#128269; Discovery</a></li>
                    </ul>
                </li>

                <!-- Marketing -->
                <li>
                    <button class="adnav-btn" aria-expanded="false" aria-controls="adn-marketing">
                        <span>&#129302; Marketing</span><span class="adnav-arr">&#9660;</span>
                    </button>
                    <ul class="adnav-sub" id="adn-marketing">
                        <li><a href="<?= url('admin/marketing') ?>">&#128202; Dashboard</a></li>
                        <li><a href="<?= url('admin/marketing/activity') ?>">&#128225; Activity Log</a></li>
                        <li><a href="<?= url('admin/marketing/generator') ?>">&#129302; Content Generator</a></li>
                        <li><a href="<?= url('admin/marketing/templates') ?>">&#128196; Prompt Templates</a></li>
                        <li><a href="<?= url('admin/marketing/social') ?>">&#128241; Social Media</a></li>
                        <li><a href="<?= url('admin/marketing/images') ?>">&#128444; Image Generator</a></li>
                        <li><a href="<?= url('admin/marketing/calendar') ?>">&#128197; Scheduled Posts</a></li>
                        <li><a href="<?= url('admin/marketing/facebook/queue') ?>">&#128257; Publishing Queue</a></li>
                        <li><a href="<?= url('admin/marketing/campaigns') ?>">&#128231; Email Campaigns</a></li>
                        <li><a href="<?= url('admin/marketing/newsletter') ?>">&#128240; Newsletter</a></li>
                        <li><a href="<?= url('admin/marketing/analytics') ?>">&#128200; Analytics</a></li>
                        <li><a href="<?= url('admin/marketing/traffic') ?>">&#128279; Traffic Reports</a></li>
                        <li><a href="<?= url('admin/marketing/facebook/settings') ?>">&#9881; Settings</a></li>
                    </ul>
                </li>

                <li><div class="adnav-sep"></div></li>
                <li><a href="<?= url('admin/suggestions') ?>" class="adnav-plain">&#128172; Suggestions &amp; Feedback</a></li>
                <li><a href="<?= url('admin/api-keys') ?>" class="adnav-plain">&#128273; API Keys</a></li>
                <li><div class="adnav-sep"></div></li>
                <li><a href="<?= url('/') ?>" class="adnav-plain">&#127760; View Site</a></li>
                <li><a href="<?= url('logout') ?>" class="adnav-plain">&#10006; Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="admin-main">
            <?php if ($flash = flash('message')): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <span><?= e($flash['message']) ?></span>
                    <button class="alert-close">&times;</button>
                </div>
            <?php endif; ?>

            <?= $this->getContent() ?>
        </main>
    </div>

    <script src="<?= asset('js/script.js') ?>?v=<?= time() ?>"></script>
    <script>
    (function () {
        var DESK_BP = 1200;
        function isDesk() { return window.innerWidth >= DESK_BP; }

        /* ── Accordion Drawer ── */
        var overlay  = document.getElementById('adnavOverlay');
        var drawer   = document.getElementById('adnavDrawer');
        var closeBtn = document.getElementById('adnavClose');
        var toggle   = document.getElementById('adminMenuToggle');

        function openDrawer() {
            if (!drawer) return;
            overlay.classList.add('adnav--open');
            drawer.classList.add('adnav--open');
            drawer.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            if (toggle) toggle.classList.add('active');
        }
        function closeDrawer() {
            if (!drawer) return;
            overlay.classList.remove('adnav--open');
            drawer.classList.remove('adnav--open');
            drawer.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            if (toggle) toggle.classList.remove('active');
        }

        if (toggle) toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            if (isDesk()) return;
            drawer && drawer.classList.contains('adnav--open') ? closeDrawer() : openDrawer();
        });
        if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
        if (overlay)  overlay.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeDrawer(); });
        window.addEventListener('resize', function () { if (isDesk()) closeDrawer(); });
        if (drawer) drawer.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', closeDrawer); });

        /* ── Accordion ── */
        document.querySelectorAll('.adnav-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var sub    = document.getElementById(btn.getAttribute('aria-controls'));
                var isOpen = btn.getAttribute('aria-expanded') === 'true';
                document.querySelectorAll('.adnav-btn[aria-expanded="true"]').forEach(function (b) {
                    if (b !== btn) {
                        b.setAttribute('aria-expanded', 'false');
                        var o = document.getElementById(b.getAttribute('aria-controls'));
                        if (o) o.classList.remove('adnav-sub--open');
                    }
                });
                btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                if (sub) sub.classList.toggle('adnav-sub--open', !isOpen);
            });
        });

        /* ── Desktop dropdown toggles ── */
        document.querySelectorAll('.admin-dropdown-toggle').forEach(function (t) {
            t.addEventListener('click', function (e) {
                e.stopPropagation();
                this.parentElement.classList.toggle('open');
            });
        });
        document.addEventListener('click', function () {
            document.querySelectorAll('.admin-topnav-dropdown.open').forEach(function (d) {
                d.classList.remove('open');
            });
        });

        /* ── Confirm delete ── */
        document.querySelectorAll('[data-confirm]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                if (!confirm(this.dataset.confirm || 'Are you sure?')) e.preventDefault();
            });
        });
    })();
    </script>
</body>
</html>
