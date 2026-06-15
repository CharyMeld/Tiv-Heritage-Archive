<nav class="bottom-nav">
    <ul class="bottom-nav-list">
        <li class="bottom-nav-item">
            <a href="<?= url('contribute') ?>" class="bottom-nav-link <?= ($currentPage ?? '') === 'contribute' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                </svg>
                <span>Contribute</span>
            </a>
        </li>
        <li class="bottom-nav-item">
            <a href="<?= url('about') ?>" class="bottom-nav-link <?= ($currentPage ?? '') === 'about' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>About</span>
            </a>
        </li>
        <li class="bottom-nav-item">
            <a href="<?= url('contact') ?>" class="bottom-nav-link <?= ($currentPage ?? '') === 'contact' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <span>Contact</span>
            </a>
        </li>
        <li class="bottom-nav-item">
            <a href="<?= is_logged_in() ? url('profile') : url('login') ?>" class="bottom-nav-link <?= ($currentPage ?? '') === 'profile' ? 'active' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="5"/>
                    <path d="M20 21a8 8 0 0 0-16 0"/>
                </svg>
                <span><?= is_logged_in() ? 'Profile' : 'Sign In' ?></span>
            </a>
        </li>
    </ul>
</nav>
