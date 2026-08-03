<?php if (empty($groups)): ?>
<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">&#9898; Duplicate Entries</h1>
        <p class="admin-page-sub">No duplicates found — your archive is clean!</p>
    </div>
    <a href="<?= url('admin') ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;">&#8592; Dashboard</a>
</div>
<div class="empty-state">
    <h3>&#10003; No duplicates found</h3>
    <p>All entries in your archive are unique.</p>
</div>
<?php else: ?>

<style>
.dup-toolbar {
    position: sticky; top: 0; z-index: 100;
    background: #fff; border-bottom: 2px solid #e5e7eb;
    padding: .85rem 1.5rem;
    display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
}
.dup-toolbar-title { flex: 1; min-width: 180px; }
.dup-toolbar-title h1 { font-size: 1.05rem; font-weight: 700; color: #111; margin: 0; }
.dup-toolbar-sub { font-size: .8rem; color: #6b7280; margin-top: .1rem; }
.dup-sel-wrap { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
.dup-sel-label { font-size: .88rem; color: #374151; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; gap: .4rem; white-space: nowrap; user-select: none; }
.dup-sel-label input[type=checkbox] { width: 1.1rem; height: 1.1rem; accent-color: #ef4444; cursor: pointer; }
.dup-sel-count { font-size: .82rem; color: #b45309; background: #fef3c7;
    padding: .2rem .75rem; border-radius: 50px; white-space: nowrap; }
.btn-bulk-delete {
    background: #ef4444; color: #fff; border: none;
    padding: .5rem 1.3rem; border-radius: 8px;
    font-size: .88rem; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; gap: .4rem; transition: background .15s;
    white-space: nowrap;
}
.btn-bulk-delete:hover:not(:disabled) { background: #dc2626; }
.btn-bulk-delete:disabled { background: #fca5a5; cursor: not-allowed; }
.btn-bulk-delete.loading { background: #f87171; cursor: wait; }

.dup-group { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 2rem; overflow: hidden; }
.dup-group-header {
    background: #fef3c7; padding: .8rem 1.5rem; border-bottom: 1px solid #fde68a;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem;
}
.dup-group-title { font-weight: 700; font-size: .95rem; color: #92400e; }
.dup-group-right { display: flex; align-items: center; gap: .75rem; }
.dup-group-selall { font-size: .8rem; color: #92400e; font-weight: 600; cursor: pointer;
    display: flex; align-items: center; gap: .35rem; user-select: none; }
.dup-group-selall input[type=checkbox] { width: 1rem; height: 1rem; accent-color: #ef4444; cursor: pointer; }
.dup-group-meta { font-size: .78rem; color: #b45309; background: #fde68a; padding: .15rem .6rem; border-radius: 50px; }

.dup-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 1rem; padding: 1.5rem; }
.dup-card {
    border: 2px solid #e5e7eb; border-radius: 10px; padding: 1rem;
    position: relative; transition: border-color .12s, box-shadow .12s, opacity .3s, transform .3s;
    cursor: pointer;
}
.dup-card:hover { border-color: #d1d5db; box-shadow: 0 2px 6px rgba(0,0,0,.06); }
.dup-card.oldest { border-color: #3b82f6; }
.dup-card.newest { border-color: #10b981; }
.dup-card.selected { border-color: #ef4444 !important; box-shadow: 0 0 0 3px #fecaca; background: #fff5f5; }
.dup-card.removing { opacity: 0; transform: scale(.95); pointer-events: none; }
.dup-badge {
    position: absolute; top: -1px; right: -1px;
    font-size: .68rem; font-weight: 700; padding: .2rem .55rem;
    border-radius: 0 10px 0 8px; color: #fff; pointer-events: none;
}
.dup-badge.oldest { background: #3b82f6; }
.dup-badge.newest { background: #10b981; }
.dup-card-check {
    position: absolute; top: .7rem; left: .7rem;
    width: 1.15rem; height: 1.15rem; cursor: pointer; accent-color: #ef4444;
    z-index: 2;
}
.dup-body { padding-left: 1.8rem; }
.dup-card-id { font-size: .75rem; color: #9ca3af; margin-bottom: .3rem; }
.dup-field { font-size: .8rem; color: #6b7280; margin-bottom: .15rem; }
.dup-field strong { color: #111; }
.dup-field-date { font-size: .73rem; color: #9ca3af; margin-top: .4rem; }
.dup-group-empty { padding: 1rem 1.5rem; font-size: .85rem; color: #6b7280; font-style: italic; }

/* Toast */
.dup-toast {
    position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
    background: #111; color: #fff; padding: .8rem 1.3rem; border-radius: 10px;
    font-size: .9rem; font-weight: 600; box-shadow: 0 4px 16px rgba(0,0,0,.2);
    display: flex; align-items: center; gap: .5rem;
    transform: translateY(100px); opacity: 0; transition: all .3s ease;
    pointer-events: none;
}
.dup-toast.show { transform: translateY(0); opacity: 1; }
.dup-toast.success { background: #065f46; }
.dup-toast.error { background: #991b1b; }
</style>

<!-- Sticky toolbar — no <form> needed -->
<div class="dup-toolbar">
    <div class="dup-toolbar-title">
        <h1>&#9898; Duplicate Entries</h1>
        <div class="dup-toolbar-sub"><?= $totalGroups ?> group<?= $totalGroups !== 1 ? 's' : '' ?> &mdash; tick any entries to remove, keep the best copy</div>
    </div>
    <div class="dup-sel-wrap">
        <label class="dup-sel-label">
            <input type="checkbox" id="select-all-global">
            Select all
        </label>
        <span class="dup-sel-count" id="global-count">0 selected</span>
        <button type="button" class="btn-bulk-delete" id="bulk-delete-btn" disabled>
            &#128465; Delete Selected
        </button>
    </div>
    <a href="<?= url('admin') ?>" class="btn btn-secondary btn-sm" style="border-radius:50px;white-space:nowrap;">&#8592; Dashboard</a>
</div>

<!-- Groups -->
<?php foreach ($groups as $gi => $group): ?>
<div class="dup-group" id="group-<?= $gi ?>" data-group="<?= $gi ?>">
    <div class="dup-group-header">
        <span class="dup-group-title">&ldquo;<?= e($group['term']) ?>&rdquo; &mdash; <?= e($group['label']) ?></span>
        <div class="dup-group-right">
            <label class="dup-group-selall">
                <input type="checkbox" class="group-selall" data-group="<?= $gi ?>">
                Select group
            </label>
            <span class="dup-group-meta" id="group-meta-<?= $gi ?>"><?= $group['count'] ?> copies</span>
        </div>
    </div>

    <div class="dup-cards" id="cards-<?= $gi ?>">
        <?php $last = count($group['rows']) - 1; ?>
        <?php foreach ($group['rows'] as $i => $row): ?>
        <?php
            $isOldest  = ($i === 0);
            $isNewest  = ($i === $last);
            $cardClass = $isOldest ? 'oldest' : ($isNewest ? 'newest' : '');
            $itemVal   = $group['category'] . ':' . $row['id'];
        ?>
        <div class="dup-card <?= $cardClass ?>"
             id="item-<?= e($itemVal) ?>"
             data-item="<?= e($itemVal) ?>"
             data-group="<?= $gi ?>">

            <?php if ($cardClass): ?>
            <span class="dup-badge <?= $cardClass ?>"><?= strtoupper($cardClass) ?></span>
            <?php endif; ?>

            <input type="checkbox" class="dup-card-check entry-checkbox"
                   data-item="<?= e($itemVal) ?>" data-group="<?= $gi ?>"
                   value="<?= e($itemVal) ?>">

            <div class="dup-body">
                <div class="dup-card-id">ID #<?= e($row['id']) ?></div>

                <?php if ($group['category'] === 'words'): ?>
                    <div class="dup-field"><strong><?= e($row['tiv_word']) ?></strong></div>
                    <div class="dup-field">Meaning: <strong><?= e($row['english_meaning'] ?? '—') ?></strong></div>
                    <div class="dup-field">POS: <strong><?= e($row['part_of_speech'] ?? '—') ?></strong></div>
                    <div class="dup-field">Example: <strong><?= e(mb_strimwidth($row['example_tiv'] ?? '—', 0, 60, '…')) ?></strong></div>

                <?php elseif ($group['category'] === 'names'): ?>
                    <div class="dup-field"><strong><?= e($row['tiv_name']) ?></strong></div>
                    <div class="dup-field">Meaning: <strong><?= e($row['english_meaning'] ?? '—') ?></strong></div>
                    <div class="dup-field">Gender: <strong><?= e($row['gender'] ?? '—') ?></strong></div>
                    <div class="dup-field"><?= e(mb_strimwidth($row['description'] ?? '—', 0, 70, '…')) ?></div>

                <?php elseif ($group['category'] === 'proverbs'): ?>
                    <div class="dup-field"><strong><?= e(mb_strimwidth($row['tiv_text'], 0, 80, '…')) ?></strong></div>
                    <div class="dup-field"><?= e(mb_strimwidth($row['english_translation'] ?? '—', 0, 80, '…')) ?></div>

                <?php elseif ($group['category'] === 'plants'): ?>
                    <div class="dup-field"><strong><?= e($row['tiv_name']) ?></strong></div>
                    <div class="dup-field">English: <strong><?= e($row['english_name'] ?? '—') ?></strong></div>
                    <div class="dup-field"><?= e(mb_strimwidth($row['medicinal_uses'] ?? '—', 0, 60, '…')) ?></div>

                <?php elseif ($group['category'] === 'festivals'): ?>
                    <div class="dup-field"><strong><?= e($row['tiv_name']) ?></strong></div>
                    <div class="dup-field">English: <strong><?= e($row['english_name'] ?? '—') ?></strong></div>
                    <div class="dup-field">Timing: <strong><?= e($row['timing'] ?? '—') ?></strong></div>

                <?php elseif ($group['category'] === 'foods'): ?>
                    <div class="dup-field"><strong><?= e($row['tiv_name']) ?></strong></div>
                    <div class="dup-field">English: <strong><?= e($row['english_name'] ?? '—') ?></strong></div>
                    <div class="dup-field"><?= e(mb_strimwidth($row['ingredients'] ?? '—', 0, 60, '…')) ?></div>

                <?php elseif ($group['category'] === 'animals'): ?>
                    <div class="dup-field"><strong><?= e($row['tiv_name']) ?></strong></div>
                    <div class="dup-field">English: <strong><?= e($row['english_name'] ?? '—') ?></strong></div>
                <?php endif; ?>

                <div class="dup-field-date">Added: <?= e($row['created_at'] ?? '—') ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<!-- Toast notification -->
<div class="dup-toast" id="dup-toast"></div>

<script>
(function () {
    var csrfToken    = <?= json_encode(csrf_token()) ?>;
    var bulkUrl      = <?= json_encode(url('admin/duplicates/delete-bulk')) ?>;
    var globalSelAll = document.getElementById('select-all-global');
    var globalCount  = document.getElementById('global-count');
    var deleteBtn    = document.getElementById('bulk-delete-btn');
    var toast        = document.getElementById('dup-toast');
    var toastTimer;

    /* ── Toast ── */
    function showToast(msg, type) {
        clearTimeout(toastTimer);
        toast.textContent = msg;
        toast.className   = 'dup-toast ' + (type || '');
        // force reflow so transition fires even if already shown
        void toast.offsetWidth;
        toast.classList.add('show');
        toastTimer = setTimeout(function () { toast.classList.remove('show'); }, 3500);
    }

    /* ── Selection state ── */
    function getAllCheckboxes() {
        return Array.from(document.querySelectorAll('.entry-checkbox'));
    }
    function getChecked() {
        return getAllCheckboxes().filter(function (cb) { return cb.checked; });
    }

    function updateUI() {
        var checked = getChecked();
        var all     = getAllCheckboxes();
        var n       = checked.length;

        globalCount.textContent    = n + ' selected';
        deleteBtn.disabled         = n === 0;
        globalSelAll.checked       = all.length > 0 && n === all.length;
        globalSelAll.indeterminate = n > 0 && n < all.length;

        // Card highlight
        all.forEach(function (cb) {
            cb.closest('.dup-card').classList.toggle('selected', cb.checked);
        });

        // Per-group select-all sync
        document.querySelectorAll('.group-selall').forEach(function (sa) {
            var gi  = sa.dataset.group;
            var gAll = Array.from(document.querySelectorAll('.entry-checkbox[data-group="' + gi + '"]'));
            var gChk = gAll.filter(function (c) { return c.checked; });
            sa.checked       = gAll.length > 0 && gChk.length === gAll.length;
            sa.indeterminate = gChk.length > 0 && gChk.length < gAll.length;
        });
    }

    /* ── Checkbox listeners — delegated so removed cards don't ghost ── */
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('entry-checkbox')) {
            updateUI();
            return;
        }
        if (e.target.classList.contains('group-selall')) {
            var gi  = e.target.dataset.group;
            document.querySelectorAll('.entry-checkbox[data-group="' + gi + '"]').forEach(function (cb) {
                cb.checked = e.target.checked;
            });
            updateUI();
            return;
        }
        if (e.target.id === 'select-all-global') {
            getAllCheckboxes().forEach(function (cb) { cb.checked = e.target.checked; });
            updateUI();
        }
    });

    /* ── Clicking a card (not the checkbox itself) toggles selection ── */
    document.addEventListener('click', function (e) {
        var card = e.target.closest('.dup-card');
        if (!card || e.target.classList.contains('dup-card-check')) return;
        var cb = card.querySelector('.entry-checkbox');
        if (cb) { cb.checked = !cb.checked; updateUI(); }
    });

    /* ── Bulk delete ── */
    deleteBtn.addEventListener('click', function () {
        var checked = getChecked();
        if (checked.length === 0) return;

        var n = checked.length;
        if (!confirm('Permanently delete ' + n + ' selected ' + (n === 1 ? 'entry' : 'entries') + '? This cannot be undone.')) return;

        var items = checked.map(function (cb) { return cb.value; });

        deleteBtn.disabled  = true;
        deleteBtn.classList.add('loading');
        deleteBtn.textContent = 'Deleting…';

        fetch(bulkUrl, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body:    JSON.stringify({ items: items, csrf_token: csrfToken })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) {
                showToast(data.error || 'Delete failed.', 'error');
                return;
            }

            // Update CSRF token for next request
            csrfToken = data.token;

            // Animate and remove deleted cards
            data.items.forEach(function (item) {
                var card = document.getElementById('item-' + item);
                if (!card) return;
                card.classList.add('removing');
                setTimeout(function () {
                    var group     = card.closest('.dup-group');
                    var gi        = card.dataset.group;
                    card.remove();

                    // Update group copy count or remove empty group
                    var remaining = group.querySelectorAll('.dup-card');
                    if (remaining.length === 0) {
                        group.style.transition = 'opacity .3s';
                        group.style.opacity    = '0';
                        setTimeout(function () { group.remove(); updateGlobalSub(); }, 300);
                    } else {
                        var metaEl = document.getElementById('group-meta-' + gi);
                        if (metaEl) metaEl.textContent = remaining.length + ' cop' + (remaining.length === 1 ? 'y' : 'ies');
                        // Refresh oldest/newest badges
                        refreshBadges(group);
                    }
                }, 300);
            });

            showToast('&#10003; ' + data.deleted + ' ' + (data.deleted === 1 ? 'entry' : 'entries') + ' deleted.', 'success');
            setTimeout(updateUI, 350);
        })
        .catch(function () {
            showToast('Network error — please try again.', 'error');
        })
        .finally(function () {
            deleteBtn.classList.remove('loading');
            deleteBtn.textContent = '🗑 Delete Selected';
            updateUI();
        });
    });

    function refreshBadges(group) {
        var cards = group.querySelectorAll('.dup-card');
        cards.forEach(function (c) {
            c.classList.remove('oldest', 'newest');
            var b = c.querySelector('.dup-badge');
            if (b) b.remove();
        });
        if (cards.length >= 2) {
            cards[0].classList.add('oldest');
            addBadge(cards[0], 'oldest', 'OLDEST');
            cards[cards.length - 1].classList.add('newest');
            addBadge(cards[cards.length - 1], 'newest', 'NEWEST');
        }
    }
    function addBadge(card, cls, label) {
        var b = document.createElement('span');
        b.className   = 'dup-badge ' + cls;
        b.textContent = label;
        card.insertBefore(b, card.firstChild);
    }
    function updateGlobalSub() {
        var groups = document.querySelectorAll('.dup-group').length;
        var sub    = document.querySelector('.dup-toolbar-sub');
        if (sub) sub.textContent = groups + ' group' + (groups !== 1 ? 's' : '') + ' — tick entries to remove, keep the best copy';
        if (groups === 0) {
            document.querySelector('.dup-sel-wrap').style.display = 'none';
            document.getElementById('bulk-delete-btn').style.display = 'none';
        }
    }

    updateUI();
}());
</script>

<?php endif; ?>
