<?php
/**
 * Styles for the full-text collection pages (views/collections/*). Kept inline so the
 * minified site stylesheet doesn't need regenerating; scoped to .col-* classes.
 */
?>
<style>
.col-wrap { padding: 1.5rem 0 3rem; }
.col-container { max-width: 820px; }
.col-intro { font-size: 1.05rem; line-height: 1.7; color: var(--color-text); margin: 0 0 1.5rem; }
.col-nav, .col-pages { display: flex; flex-wrap: wrap; gap: .4rem; margin: 0 0 1.25rem; }
.col-nav-link { display: inline-block; min-width: 2.2rem; text-align: center; padding: .35rem .7rem; border: 1px solid var(--color-border-light);
  border-radius: var(--radius); background: var(--color-surface); color: var(--color-primary); font-family: var(--font-ui); font-size: .9rem; text-decoration: none; }
.col-nav-link:hover, .col-nav-link:focus-visible { border-color: var(--color-accent); }
.col-nav-link.is-active { background: var(--color-primary); border-color: var(--color-primary); color: #fff; }
.col-entries { display: grid; gap: 0; border-top: 1px solid var(--color-border-light); }
.col-entry { padding: 1.1rem 0; border-bottom: 1px solid var(--color-border-light); scroll-margin-top: 90px; }
.col-entry-title { font-family: var(--font-heading); font-size: 1.25rem; margin: 0 0 .15rem; line-height: 1.3; }
.col-entry-title.is-italic { font-style: italic; }
.col-entry-title a { color: var(--color-primary); text-decoration: none; }
.col-entry-title a:hover, .col-entry-title a:focus-visible { text-decoration: underline; }
.col-entry-sub { margin: 0 0 .4rem; font-size: 1rem; color: var(--color-text); }
.col-entry-chips { display: flex; flex-wrap: wrap; gap: .35rem; margin: 0 0 .5rem; }
.col-entry-chips span { font-family: var(--font-ui); font-size: .75rem; padding: .1rem .5rem; border-radius: 999px; background: var(--color-surface-alt); color: var(--color-text-muted); }
.col-entry-fields { display: grid; grid-template-columns: minmax(110px, 160px) 1fr; gap: .3rem 1rem; margin: .4rem 0 0; }
.col-entry-fields dt { font-family: var(--font-ui); font-size: .8rem; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: .04em; padding-top: .15rem; }
.col-entry-fields dd { margin: 0; line-height: 1.6; min-width: 0; overflow-wrap: anywhere; }
@media (max-width: 600px) {
  .col-entry-fields { grid-template-columns: 1fr; }
  .col-entry-fields dd { margin-bottom: .4rem; }
}
.col-prevnext { display: flex; justify-content: space-between; gap: 1rem; margin-top: 1.75rem; }
.col-back { margin-top: 1.5rem; }
.col-hub { display: grid; gap: 1.5rem; }
.col-hub-section h2 { font-family: var(--font-heading); color: var(--color-primary); font-size: 1.35rem; margin: 0 0 .35rem; }
.col-hub-section p { margin: 0 0 .7rem; line-height: 1.6; }
</style>
