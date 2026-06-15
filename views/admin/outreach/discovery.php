<div class="admin-wrapper">
<div class="admin-content">

    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
        <div>
            <h1 style="font-size:1.4rem;color:#2d1b0e;margin:0;">&#127758; Wikipedia Discovery</h1>
            <p style="margin:.3rem 0 0;color:#7a6a5a;font-size:.88rem;">Search Wikipedia for influential Tiv people. Review each result, then import to the database.</p>
        </div>
        <a href="<?= url('admin/outreach') ?>" class="btn btn-secondary" style="font-size:.85rem;padding:.45rem .9rem;">&larr; Outreach</a>
    </div>

    <!-- Search Bar -->
    <div class="admin-card" style="margin-bottom:1.2rem;">
        <div class="admin-card-body" style="padding:1rem 1.2rem;">
            <div style="display:flex;gap:.6rem;align-items:center;">
                <span style="font-size:1.2rem;color:#8a7a6a;">&#128269;</span>
                <input type="text" id="wiki-search" class="form-input" placeholder="Search Wikipedia — e.g. Tiv senator, Tiv professor, Tiv musician…"
                       style="flex:1;margin:0;" autocomplete="off" autofocus>
                <button id="wiki-search-btn" class="btn btn-primary" style="white-space:nowrap;">Search</button>
            </div>
        </div>
    </div>

    <!-- Quick Search Pills -->
    <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.5rem;">
        <span style="font-size:.8rem;color:#8a7a6a;align-self:center;margin-right:.2rem;">Quick:</span>
        <?php
        $pills = [
            'Tiv professor Nigeria',
            'Tiv senator Benue',
            'Tiv governor politician',
            'Tiv author writer',
            'Tiv musician singer',
            'Tiv bishop clergy',
            'Tiv diaspora',
            'Tiv researcher academic',
        ];
        foreach ($pills as $pill):
        ?>
        <button class="wiki-pill" data-q="<?= e($pill) ?>"
                style="padding:.3rem .8rem;border:1px solid #e5e0d5;border-radius:20px;background:#fff;font-size:.8rem;color:#5C3A21;cursor:pointer;transition:all .15s;"
                onmouseover="this.style.background='#5C3A21';this.style.color='#fff';this.style.borderColor='#5C3A21'"
                onmouseout="this.style.background='#fff';this.style.color='#5C3A21';this.style.borderColor='#e5e0d5'">
            <?= e($pill) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <!-- Status / Loading -->
    <div id="wiki-status" style="display:none;color:#7a6a5a;font-size:.88rem;margin-bottom:1rem;"></div>

    <!-- Results Grid -->
    <div id="wiki-results" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:1rem;"></div>

    <!-- Empty / Prompt State -->
    <div id="wiki-empty" style="background:#f7f4ee;border-radius:8px;padding:1.5rem;text-align:center;color:#8a7a6a;font-size:.9rem;">
        <div style="font-size:2rem;margin-bottom:.5rem;">&#128269;</div>
        Type a search term above or click a quick-search pill to find influential Tiv people on Wikipedia.
    </div>

    <!-- External search fallback -->
    <div id="wiki-external" style="display:none;border-top:1px solid #e5e0d5;margin-top:2rem;padding-top:1.5rem;">
        <p style="font-size:.85rem;color:#8a7a6a;margin:0 0 .8rem;">
            Not finding the right person on Wikipedia? Try external search:
        </p>
        <div id="wiki-external-links" style="display:flex;flex-wrap:wrap;gap:.6rem;"></div>
    </div>

</div>
</div>

<style>
.wiki-card {
    background: #fff;
    border: 1px solid #e5e0d5;
    border-radius: 10px;
    padding: 1rem 1.1rem;
    display: flex;
    flex-direction: column;
    gap: .6rem;
    transition: border-color .15s, box-shadow .15s;
}
.wiki-card:hover {
    border-color: #C8A951;
    box-shadow: 0 2px 8px rgba(92,58,33,.08);
}
.wiki-card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #2d1b0e;
    line-height: 1.3;
}
.wiki-card-extract {
    font-size: .82rem;
    color: #5a4a3a;
    line-height: 1.5;
    flex: 1;
}
.wiki-card-badge {
    display: inline-block;
    padding: .15rem .55rem;
    border-radius: 20px;
    font-size: .73rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
    background: #f0e8d8;
    color: #5C3A21;
}
.wiki-card-actions {
    display: flex;
    gap: .5rem;
    margin-top: .2rem;
}
.wiki-card-actions a, .wiki-card-actions button {
    font-size: .8rem;
    padding: .35rem .7rem;
}
</style>

<script>
(function () {
    var searchInput  = document.getElementById('wiki-search');
    var searchBtn    = document.getElementById('wiki-search-btn');
    var resultsEl    = document.getElementById('wiki-results');
    var statusEl     = document.getElementById('wiki-status');
    var emptyEl      = document.getElementById('wiki-empty');
    var externalEl   = document.getElementById('wiki-external');
    var externalLinks= document.getElementById('wiki-external-links');
    var debounce;
    var lastQuery    = '';

    var categoryLabels = <?= json_encode(InfluentialPerson::CATEGORIES) ?>;
    var createUrl      = '<?= url('admin/outreach/people/create') ?>';
    var searchApiUrl   = '<?= url('admin/outreach/discovery/search') ?>';

    function status(msg) {
        statusEl.textContent = msg;
        statusEl.style.display = msg ? 'block' : 'none';
    }

    function run(q) {
        q = q.trim();
        if (q.length < 2) return;
        if (q === lastQuery) return;
        lastQuery = q;
        searchInput.value = q;

        resultsEl.innerHTML = '';
        emptyEl.style.display = 'none';
        externalEl.style.display = 'none';
        status('Searching Wikipedia…');

        fetch(searchApiUrl + '?q=' + encodeURIComponent(q))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                status('');
                if (!data.results || data.results.length === 0) {
                    emptyEl.innerHTML = '<div style="font-size:2rem;margin-bottom:.5rem;">&#128203;</div>' +
                        'No Wikipedia results for <strong>' + escHtml(q) + '</strong>.<br>' +
                        '<span style="font-size:.8rem;">Try different keywords or use the external search links below.</span>';
                    emptyEl.style.display = 'block';
                } else {
                    renderCards(data.results, q);
                }
                renderExternalLinks(q);
                externalEl.style.display = 'block';
            })
            .catch(function () {
                status('Could not reach Wikipedia. Check your internet connection and try again.');
                emptyEl.style.display = 'none';
            });
    }

    function renderCards(results, q) {
        resultsEl.innerHTML = '';
        results.forEach(function (p) {
            var catLabel = categoryLabels[p.category] || 'Other';
            var importUrl = createUrl + '?from=wiki'
                + '&name='         + encodeURIComponent(p.title)
                + '&category='     + encodeURIComponent(p.category)
                + '&source_url='   + encodeURIComponent(p.url)
                + '&source_notes=' + encodeURIComponent('Wikipedia article: ' + p.title + ' — ' + p.url)
                + '&notes='        + encodeURIComponent(p.extract ? p.extract.substring(0, 200) : '');

            var card = document.createElement('div');
            card.className = 'wiki-card';
            card.innerHTML =
                '<div class="wiki-card-title">' + escHtml(p.title) + '</div>' +
                (p.extract ? '<div class="wiki-card-extract">' + escHtml(p.extract) + '</div>' : '') +
                '<div><span class="wiki-card-badge">' + escHtml(catLabel) + '</span></div>' +
                '<div class="wiki-card-actions">' +
                  '<a href="' + escHtml(importUrl) + '" class="btn btn-primary" title="Prefill the Add Person form with this Wikipedia entry">+ Import to Form</a>' +
                  '<a href="' + escHtml(p.url) + '" target="_blank" rel="noopener" class="btn btn-secondary">Wikipedia &rarr;</a>' +
                '</div>';
            resultsEl.appendChild(card);
        });
    }

    function renderExternalLinks(q) {
        var enc = encodeURIComponent('Tiv ' + q);
        var sources = [
            { label: 'Google', url: 'https://www.google.com/search?q=' + enc },
            { label: 'Google Scholar', url: 'https://scholar.google.com/scholar?q=' + enc },
            { label: 'LinkedIn', url: 'https://www.linkedin.com/search/results/people/?keywords=' + enc },
            { label: 'ResearchGate', url: 'https://www.researchgate.net/search?q=' + enc },
        ];
        externalLinks.innerHTML = sources.map(function (s) {
            return '<a href="' + escHtml(s.url) + '" target="_blank" rel="noopener" ' +
                'style="padding:.35rem .8rem;border:1px solid #e5e0d5;border-radius:6px;font-size:.8rem;color:#2d1b0e;text-decoration:none;background:#fff;">' +
                escHtml(s.label) + ' &rarr;</a>';
        }).join('');
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Search on button click
    searchBtn.addEventListener('click', function () { run(searchInput.value); });

    // Search on Enter
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { clearTimeout(debounce); run(searchInput.value); }
    });

    // Debounced search while typing
    searchInput.addEventListener('input', function () {
        clearTimeout(debounce);
        debounce = setTimeout(function () { run(searchInput.value); }, 500);
    });

    // Quick pills
    document.querySelectorAll('.wiki-pill').forEach(function (btn) {
        btn.addEventListener('click', function () { run(btn.dataset.q); });
    });
}());
</script>
