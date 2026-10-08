<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="header-left">
                <a href="<?= url('/') ?>" class="logo">
                    <img src="<?= asset('images/logo.png') ?>" alt="<?= SITE_NAME ?>" class="logo-img">
                    <div class="logo-text">
                        <span class="logo-title">TIV HERITAGE ARCHIVE</span>
                        <span class="logo-subtitle">"Wisdom of the Tiv People"</span>
                    </div>
                </a>
            </div>
            <div class="header-right">
                <a href="<?= url('/archive') ?>" class="header-icon" aria-label="Search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                </a>
                <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation" aria-expanded="false">
                    <span class="menu-toggle-bar"></span>
                    <span class="menu-toggle-bar"></span>
                    <span class="menu-toggle-bar"></span>
                </button>
            </div>
        </div>
    </div>
</header>
