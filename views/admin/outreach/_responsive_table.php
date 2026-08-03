<style>
/* overflow-y must stay visible — setting only overflow-x:auto makes the
   browser implicitly treat overflow-y as auto too, which clips the
   absolutely-positioned .op-actions-menu dropdown below the table. */
.op-table-wrap { overflow-x: auto; overflow-y: visible; }
.op-table { width:100%; border-collapse:collapse; font-size:.78rem; table-layout:fixed; }
.op-table thead tr { background:#f7f4ee; border-bottom:2px solid #e5e0d5; }
.op-table th { padding:.55rem .6rem; text-align:left; color:#5C3A21; font-weight:600; }
.op-table th.op-center-cell { text-align:center; }
.op-table td { padding:.55rem .6rem; border-bottom:1px solid #f0ede8; vertical-align:top; }
.op-table td.op-center-cell { text-align:center; }
.op-row--muted { opacity:.55; }
.op-truncate { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.op-badge { display:inline-block; padding:.12rem .35rem; border-radius:10px; font-size:.78em; font-weight:600; white-space:nowrap; }

.op-actions-cell { position:relative; }
.op-actions-toggle { font-size:.72rem; padding:.3rem .6rem; white-space:nowrap; }
.op-actions-menu { display:none; position:absolute; right:0; top:100%; margin-top:.3rem; background:#fff; border:1px solid #e5e0d5; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,.18); padding:.4rem; z-index:20; min-width:130px; flex-direction:column; gap:.3rem; }
.op-actions-menu.is-open { display:flex; }
.op-actions-menu .btn { font-size:.75rem; padding:.35rem .5rem; text-align:center; white-space:nowrap; }
.op-actions-menu form { margin:0; }

/* Mobile: stack each row as a labeled card instead of a horizontally-scrolling table */
@media (max-width: 720px) {
    .op-table-wrap { overflow: visible; }
    .op-table, .op-table tbody, .op-table tr, .op-table td { display:block; width:auto !important; }
    .op-table thead { display:none; }
    .op-table tr { border:1px solid #e5e0d5; border-radius:10px; margin:0 0 .75rem; padding:.6rem .7rem; background:#fff; }
    .op-table td { border-bottom:none; padding:.3rem 0; }
    .op-table td[data-label]::before {
        content: attr(data-label);
        display:block;
        font-size:.68rem;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.03em;
        color:#a89478;
        margin-bottom:.15rem;
    }
    .op-table td.op-truncate, .op-table .op-truncate { white-space:normal; overflow:visible; text-overflow:clip; }
    .op-table td.op-center-cell { text-align:left; }
    .op-table td[data-label="Actions"]::before { content:none; }
    .op-actions-menu { left:0; right:auto; width:100%; }
}
</style>

<script>
function opToggleActions(key) {
    var menu = document.getElementById('op-actions-' + key);
    if (!menu) return;
    var isOpen = menu.classList.contains('is-open');
    document.querySelectorAll('.op-actions-menu.is-open').forEach(function (el) {
        el.classList.remove('is-open');
    });
    if (!isOpen) {
        menu.classList.add('is-open');
    }
}
document.addEventListener('click', function (e) {
    if (!e.target.closest('.op-actions-cell')) {
        document.querySelectorAll('.op-actions-menu.is-open').forEach(function (el) {
            el.classList.remove('is-open');
        });
    }
});
</script>
