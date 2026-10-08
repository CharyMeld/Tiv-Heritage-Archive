<section class="translate-page">
    <div class="container">

        <div class="translate-hero">
            <h1 class="translate-title">Tiv Translation Engine <span class="badge-v1">v3</span></h1>
            <p class="translate-subtitle">Self-contained translation powered entirely by the Tiv Heritage Archive — dictionary, grammar rules, proverbs, and bilingual Bible.</p>

            <?php if (!empty($isAdmin)): ?>
                <div class="translate-limit-notice">
                    <span class="remaining-badge remaining-paid">Admin — unlimited translations</span>
                </div>
            <?php elseif (!$isPaid): ?>
                <div class="translate-limit-notice">
                    <?php if ($remaining > 0): ?>
                        <span class="remaining-badge remaining-ok"><?= (int)$remaining ?> free translation<?= $remaining !== 1 ? 's' : '' ?> left today</span>
                    <?php else: ?>
                        <span class="remaining-badge remaining-empty">Daily limit reached</span>
                        <?php if (!$user): ?>
                            <a href="<?= url('register') ?>" class="btn btn-sm btn-primary">Register for more</a>
                        <?php else: ?>
                            <a href="<?= url('translate/plans') ?>" class="btn btn-sm btn-primary">Upgrade for unlimited</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="translate-limit-notice">
                    <span class="remaining-badge remaining-paid">Paid access — unlimited translations</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Translation Form -->
        <div class="translate-card">
            <form method="POST" action="<?= url('translate') ?>" id="translateForm">
                <?= csrf_field() ?>

                <!-- Language Selector -->
                <div class="translate-lang-bar">
                    <div class="lang-select-group">
                        <label for="source_language" class="sr-only">Translate from</label>
                        <select name="source_language" id="source_language" class="lang-select">
                            <option value="tiv"     <?= ($lastSource ?? 'tiv') === 'tiv'     ? 'selected' : '' ?>>Tiv</option>
                            <option value="english" <?= ($lastSource ?? 'tiv') === 'english' ? 'selected' : '' ?>>English</option>
                        </select>
                    </div>

                    <button type="button" class="lang-swap-btn" id="swapLangs" title="Swap languages">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 3 4 7l4 4"/><path d="M4 7h16"/>
                            <path d="m16 21 4-4-4-4"/><path d="M20 17H4"/>
                        </svg>
                    </button>

                    <div class="lang-select-group">
                        <label for="target_language" class="sr-only">Translate to</label>
                        <select name="target_language" id="target_language" class="lang-select">
                            <option value="english" <?= ($lastTarget ?? 'english') === 'english' ? 'selected' : '' ?>>English</option>
                            <option value="tiv"     <?= ($lastTarget ?? 'english') === 'tiv'     ? 'selected' : '' ?>>Tiv</option>
                        </select>
                    </div>
                </div>

                <!-- Input / Output panels -->
                <div class="translate-panels">
                    <div class="translate-panel translate-panel--input">
                        <textarea
                            name="input_text"
                            id="input_text"
                            class="translate-textarea"
                            placeholder="Enter text to translate…"
                            <?= empty($isAdmin) ? 'maxlength="1000"' : '' ?>
                            rows="5"
                            autofocus
                        ><?= e($lastInput ?? '') ?></textarea>
                        <div class="translate-panel-footer">
                            <span class="char-count" id="charCount">0 / <?= empty($isAdmin) ? '1000' : '∞' ?></span>
                            <button type="button" class="translate-clear-btn" id="clearInput" title="Clear">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <?php if ($result): ?>
                    <div class="translate-panel translate-panel--output" id="resultPanel">
                        <?php if (($result['match_type'] ?? 'none') === 'none' || empty($result['translated_text'])): ?>
                        <div class="translate-not-found">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                                <path d="M11 8v4"/><path d="M11 16h.01"/>
                            </svg>
                            <p><strong>Not found in dictionary</strong></p>
                            <p class="not-found-hint">
                                "<?= e($result['input_text'] ?? $lastInput) ?>" is not in our Tiv dictionary yet.
                                <?php if ($user): ?>
                                    Do you know this word? <a href="<?= url('contribute') ?>">Contribute it</a> to help grow the dictionary.
                                <?php else: ?>
                                    <a href="<?= url('register') ?>">Register</a> to contribute missing words and help others.
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if (!empty($isAdmin)): ?>
                        <div class="translate-panel-footer">
                            <span class="confidence-badge conf-low">Not found</span>
                            <span class="match-type-badge">None</span>
                        </div>
                        <?php endif; ?>
                        <?php else: ?>


                        <?php
                            // Render translated text safely with tone-mark superscripts.
                            // 1. HTML-escape the raw text first (security).
                            // 2. Then wrap trailing tone digits (1–4) after a letter in <sup>.
                            // 3. Finally restore newlines as <br> (white-space:pre-wrap handles
                            //    actual \n, but we emit <br> so copy-paste also works cleanly).
                            $safeText = e($result['translated_text']);
                            // Superscript tone numbers: letter immediately followed by 1–4
                            $safeText = preg_replace('/(\p{L})([1-4])(?=\s|[,\.;:!\?\'"\)\]>\n]|$)/u', '$1<sup>$2</sup>', $safeText);
                        ?>
                        <div class="translate-result-text" id="resultText"><?= $safeText ?></div>

                        <?php if (!empty($isAdmin)): ?>
                        <!-- Admin-only: confidence and match type info -->
                        <?php
                            $conf = (int) ($result['confidence_score'] ?? 0);
                            $mt   = $result['match_type'] ?? 'none';
                            $confClass = $conf >= 80 ? 'conf-high' : ($conf >= 40 ? 'conf-medium' : 'conf-low');
                            $confLabel = $conf >= 80 ? 'High' : ($conf >= 40 ? 'Medium' : 'Low');
                            $mtLabels = [
                                'proverb'      => 'Curated proverb',
                                'phrase'       => 'Curated phrase',
                                'word'         => 'Dictionary word',
                                'word_by_word' => 'Word-by-word',
                                'bible_refined'=> 'Bible-refined',
                                'learned'      => 'Learned from history',
                                'category'     => 'Category match',
                                'bible'        => 'Bible match',
                                'none'         => 'Not found',
                            ];
                            $mtLabel = $mtLabels[$mt] ?? ucfirst(str_replace('_', ' ', $mt));
                        ?>
                        <div class="translate-panel-footer">
                            <span class="confidence-badge <?= $confClass ?>">
                                <?= $confLabel ?> confidence <?= $conf ?>%
                            </span>
                            <span class="match-type-badge">
                                <?= e($mtLabel) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="translate-panel translate-panel--output translate-panel--empty" id="resultPanel">
                        <p class="translate-placeholder">Translation will appear here</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Translate button -->
                <?php if (!empty($isAdmin) || $remaining > 0 || $isPaid): ?>
                    <div class="translate-actions">
                        <button type="submit" class="btn btn-primary btn-translate" id="translateBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/>
                                <path d="M7 2h1"/><path d="m22 22-5-10-5 10"/><path d="M14 18h6"/>
                            </svg>
                            Translate
                        </button>
                    </div>
                <?php else: ?>
                    <div class="translate-actions">
                        <button type="button" class="btn btn-disabled" disabled>Daily limit reached</button>
                        <?php if (!$user): ?>
                            <a href="<?= url('register') ?>" class="btn btn-secondary">Register for more</a>
                        <?php else: ?>
                            <a href="<?= url('translate/plans') ?>" class="btn btn-secondary">View plans</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Result details (shown after translation) -->
        <?php if ($result && !empty($result['translated_text'])): ?>
        <div class="translate-details">

            <?php if (!empty($result['why_explanation'])): ?>
            <div class="translate-detail-card">
                <h3 class="detail-heading">Why this translation?</h3>
                <p><?= e($result['why_explanation']) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($result['cultural_meaning']) || !empty($result['explanation'])): ?>
            <div class="translate-detail-card">
                <h3 class="detail-heading">Explanation</h3>
                <?php if (!empty($result['cultural_meaning'])): ?>
                    <p><strong>Meaning:</strong> <?= e($result['cultural_meaning']) ?></p>
                <?php endif; ?>
                <?php if (!empty($result['explanation']) && $result['explanation'] !== $result['cultural_meaning']): ?>
                    <p><?= e($result['explanation']) ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($result['alternatives'])): ?>
            <div class="translate-detail-card">
                <h3 class="detail-heading">Alternative Expressions</h3>
                <ul style="margin:.4rem 0 0;padding-left:1.2rem;list-style:disc;">
                    <?php foreach ($result['alternatives'] as $alt): ?>
                    <li>
                        <strong><?= e($alt['text']) ?></strong>
                        <?php if (!empty($alt['note'])): ?>
                            <span style="color:#7a6a5a;font-size:.85rem;"> — <?= e($alt['note']) ?></span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($result['pronunciation'])): ?>
            <div class="translate-detail-card">
                <h3 class="detail-heading">Pronunciation</h3>
                <p class="pronunciation"><?= e($result['pronunciation']) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($result['usage_context'])): ?>
            <div class="translate-detail-card">
                <h3 class="detail-heading">Context</h3>
                <p><?= e($result['usage_context']) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($result['citations'])): ?>
            <div class="translate-detail-card">
                <h3 class="detail-heading">Sources</h3>
                <p style="color:#7a6a5a;font-size:.82rem;margin:0 0 .5rem;">Linguistic records consulted to produce this translation.</p>
                <ul style="margin:0;padding-left:1.2rem;list-style:disc;">
                    <?php foreach ($result['citations'] as $cite): ?>
                    <li>
                        <span style="display:inline-block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:#5C3A21;background:#fdf8f0;border:1px solid #e5d5b8;border-radius:10px;padding:.1rem .5rem;margin-right:.4rem;"><?= e($cite['type'] ?? 'source') ?></span>
                        <?= e($cite['label'] ?? $cite['table'] ?? 'Archive record') ?>
                        <?php if (!empty($cite['id'])): ?>
                            <span style="color:#9a8a7a;font-size:.8rem;"> (#<?= e($cite['id']) ?>)</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>


            <?php
            $missingTokens  = $result['missing_tokens']   ?? [];
            $missingWordIds = $result['missing_word_ids'] ?? [];
            // Only include tokens that have a DB id (so we can save them)
            $suggestableTokens = array_filter($missingTokens, fn($t) => isset($missingWordIds[$t]));
            ?>
            <?php if (!empty($missingTokens)): ?>
            <div class="translate-detail-card missing-words-card">
                <h3 class="detail-heading">Words Not in Dictionary</h3>

                <?php
                // Determine what kind of translation is needed based on direction
                $srcLang        = $lastSource ?? 'tiv';
                $tgtLang        = $lastTarget ?? 'english';
                $targetLabel    = ucfirst($tgtLang); // "English" or "Tiv"
                $placeholder    = $targetLabel . ' translation (optional)';
                $submitLabel    = 'Submit ' . $targetLabel . ' Translations';
                ?>
                <?php if ($user && !empty($suggestableTokens)): ?>
                <p class="missing-words-intro">
                    <?= count($missingTokens) > 1 ? 'These words were' : 'This word was' ?> not found.
                    If you know the <?= $targetLabel ?> translation, fill it in — leave the rest blank — and click <strong><?= $submitLabel ?></strong>.
                    <?php if (!empty($isAdmin)): ?>
                        <em>(Admin: translations are approved instantly.)</em>
                    <?php endif; ?>
                </p>

                <form method="POST" action="<?= url('translate/suggest-multi') ?>" class="multi-suggest-form">
                    <?= csrf_field() ?>
                    <div class="multi-suggest-grid">
                        <?php foreach ($suggestableTokens as $token):
                            $mwId = $missingWordIds[$token];
                        ?>
                        <div class="multi-suggest-row">
                            <label class="multi-suggest-label" for="meaning_<?= (int)$mwId ?>">
                                <span class="missing-word-text"><?= e($token) ?></span>
                            </label>
                            <input
                                type="text"
                                id="meaning_<?= (int)$mwId ?>"
                                name="meanings[<?= (int)$mwId ?>]"
                                class="multi-suggest-input"
                                placeholder="<?= htmlspecialchars($placeholder) ?>"
                                autocomplete="off"
                            >
                        </div>
                        <?php endforeach; ?>
                        <?php
                        // Show tokens with no DB id as read-only pills (can't save, no id)
                        $unsaveable = array_diff($missingTokens, array_keys(array_flip($suggestableTokens)));
                        foreach ($unsaveable as $token): ?>
                        <div class="multi-suggest-row">
                            <span class="missing-word-text"><?= e($token) ?></span>
                            <span class="multi-suggest-na">—</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="multi-suggest-actions">
                        <button type="submit" class="btn btn-primary btn-sm"><?= $submitLabel ?></button>
                        <span class="multi-suggest-hint">You can leave words blank if you don't know them.</span>
                    </div>
                </form>

                <?php elseif ($user): ?>
                <p class="missing-words-intro">
                    <?= count($missingTokens) > 1 ? 'These words were' : 'This word was' ?> not found in the dictionary.
                </p>
                <div class="missing-words-list">
                    <?php foreach ($missingTokens as $token): ?>
                    <div class="missing-word-item">
                        <span class="missing-word-text"><?= e($token) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php else: ?>
                <p class="missing-words-intro">
                    <?= count($missingTokens) > 1 ? 'These words were' : 'This word was' ?> not found.
                    <a href="<?= url('register') ?>">Register</a> to suggest meanings and help grow the dictionary.
                </p>
                <div class="missing-words-list">
                    <?php foreach ($missingTokens as $token): ?>
                    <div class="missing-word-item">
                        <span class="missing-word-text"><?= e($token) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Word-by-word breakdown (only for word_by_word match type) -->
            <?php
            $wordResults = $result['word_results'] ?? [];
            $showBreakdown = !empty($wordResults) && count($wordResults) > 1
                          && in_array($result['match_type'] ?? '', ['word_by_word','bible_refined','learned']);
            ?>
            <?php if ($showBreakdown): ?>
            <div class="translate-detail-card">
                <h3 class="detail-heading">Word-by-Word Breakdown</h3>
                <table class="word-breakdown-table">
                    <thead>
                        <tr>
                            <th><?= ($result['source_language'] ?? 'tiv') === 'tiv' ? 'Tiv' : 'English' ?> token</th>
                            <th>Part of speech</th>
                            <th>Translation</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wordResults as $wr): ?>
                        <tr style="<?= !$wr['found'] ? 'color:#b45309;' : '' ?>">
                            <td><code><?= e($wr['token']) ?></code></td>
                            <td style="color:#6b7280;font-size:.82rem;"><?= e($wr['pos'] ?? '') ?></td>
                            <td><?= $wr['found'] ? e($wr['result'] ?? '') : '<em style="color:#b45309;">not found</em>' ?></td>
                            <td style="color:#6b7280;font-size:.78rem;"><?= e($wr['source'] ?? '') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p style="font-size:.78rem;color:#9ca3af;margin:.5rem 0 0;">
                    Tiv word order and grammar rules are applied after token lookup.
                    Words marked in orange were not found in the archive.
                </p>
            </div>
            <?php endif; ?>

            <!-- Feedback form -->
            <?php if ($user && !empty($result['log_id'])): ?>
            <div class="translate-detail-card feedback-card">
                <h3 class="detail-heading">Was this translation helpful?</h3>
                <form method="POST" action="<?= url('translate/feedback') ?>" class="feedback-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="log_id" value="<?= (int)$result['log_id'] ?>">
                    <div class="rating-stars">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>">
                        <label for="star<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">&#9733;</label>
                        <?php endfor; ?>
                    </div>
                    <textarea name="suggested_correction" class="feedback-textarea"
                              placeholder="Suggest a better translation (optional)…" rows="2"></textarea>
                    <button type="submit" class="btn btn-sm btn-secondary">Submit Feedback</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Quick Links -->
        <div class="translate-quick-links">
            <?php if ($user): ?>
                <a href="<?= url('translate/history') ?>" class="quick-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                        <path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>
                    </svg>
                    Translation History
                </a>
            <?php endif; ?>
            <a href="<?= url(section_path('words')) ?>" class="quick-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                </svg>
                Browse Tiv Dictionary
            </a>
            <a href="<?= url(section_path('proverbs')) ?>" class="quick-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/>
                    <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>
                </svg>
                Browse Proverbs
            </a>
        </div>

    </div>
</section>

<style>
/* ── Translate Page Styles ── */
.translate-page { padding: 2rem 0 4rem; }
.translate-hero { text-align: center; margin-bottom: 2rem; }
.translate-title { font-size: 2rem; font-weight: 700; color: var(--color-primary, #5C3A21); margin-bottom: .5rem; }
.badge-v1 { background: var(--color-primary, #5C3A21); color: #fff; font-size: .65rem; padding: .2em .5em; border-radius: 4px; vertical-align: middle; font-weight: 600; letter-spacing: .05em; }
.translate-subtitle { color: var(--color-text-muted, #666); margin-bottom: 1rem; }
.translate-limit-notice { display: flex; align-items: center; gap: .75rem; justify-content: center; margin-top: .75rem; }
.remaining-badge { padding: .3em .75em; border-radius: 20px; font-size: .8rem; font-weight: 600; }
.remaining-ok { background: #e8f5e9; color: #2e7d32; }
.remaining-empty { background: #fff3e0; color: #e65100; }
.remaining-paid { background: #e3f2fd; color: #1565c0; }
.translate-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(0,0,0,.08); overflow: hidden; margin-bottom: 1.5rem; }
.translate-lang-bar { display: flex; align-items: center; justify-content: center; gap: 1rem; padding: 1rem 1.5rem; background: var(--color-bg-light, #faf7f4); border-bottom: 1px solid #eee; }
.lang-select { border: 1px solid #ddd; border-radius: 6px; padding: .5em 1em; font-size: 1rem; font-weight: 600; background: #fff; cursor: pointer; color: var(--color-primary, #5C3A21); }
.lang-swap-btn { background: none; border: 1px solid #ddd; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #666; transition: all .2s; }
.lang-swap-btn:hover { background: var(--color-primary, #5C3A21); color: #fff; border-color: var(--color-primary, #5C3A21); }
.translate-panels { display: grid; grid-template-columns: 1fr 1fr; min-height: 180px; }
@media (max-width: 640px) { .translate-panels { grid-template-columns: 1fr; } }
.translate-panel { padding: 1.25rem; position: relative; }
.translate-panel--input { border-right: 1px solid #eee; }
@media (max-width: 640px) { .translate-panel--input { border-right: none; border-bottom: 1px solid #eee; } }
.translate-textarea { width: 100%; border: none; outline: none; resize: none; font-size: 1.1rem; font-family: inherit; background: transparent; color: #222; line-height: 1.6; word-break: break-word; }
.translate-panel-footer { display: flex; align-items: center; justify-content: space-between; margin-top: .75rem; padding-top: .5rem; border-top: 1px solid #f0f0f0; }
.char-count { font-size: .75rem; color: #999; }
.translate-clear-btn { background: none; border: none; cursor: pointer; color: #999; padding: 2px; line-height: 1; }
.translate-clear-btn:hover { color: #333; }
.translate-result-text { font-size: 1.1rem; color: #222; line-height: 1.6; min-height: 80px; white-space: pre-wrap; word-break: break-word; }
.translate-result-text sup { font-size: .65em; vertical-align: super; color: #888; line-height: 0; }
.translate-placeholder { color: #bbb; font-size: 1rem; padding-top: .5rem; }
.translate-not-found { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; gap: .5rem; padding: 1rem .5rem; color: #888; min-height: 80px; }
.translate-not-found svg { color: #ccc; }
.translate-not-found strong { color: #555; }
.not-found-hint { font-size: .85rem; color: #aaa; }
.not-found-hint a { color: var(--color-primary, #5C3A21); }
.translate-panel--empty { display: flex; align-items: center; justify-content: center; background: #fafafa; }
.confidence-badge { font-size: .72rem; padding: .25em .6em; border-radius: 12px; font-weight: 600; }
.conf-high { background: #e8f5e9; color: #2e7d32; }
.conf-medium { background: #fff8e1; color: #f57f17; }
.conf-low { background: #fce4ec; color: #c62828; }
.match-type-badge { font-size: .72rem; background: #f0f0f0; color: #555; padding: .25em .6em; border-radius: 12px; }
.match-type-badge.match-ai { background: #ede7f6; color: #4527a0; }
.translate-actions { padding: 1rem 1.5rem; display: flex; gap: 1rem; align-items: center; justify-content: center; border-top: 1px solid #eee; }
.btn-translate { padding: .65em 2em; font-size: 1rem; display: flex; align-items: center; gap: .5rem; }
.btn-disabled { opacity: .5; cursor: not-allowed; }

/* Details */
.translate-details { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.translate-detail-card { background: #fff; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 8px rgba(0,0,0,.06); }
.detail-heading { font-size: .85rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--color-primary, #5C3A21); margin-bottom: .75rem; }
.pronunciation { font-size: 1.1rem; font-style: italic; color: #555; }
.word-breakdown-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
.word-breakdown-table th, .word-breakdown-table td { padding: .4em .6em; text-align: left; border-bottom: 1px solid #f0f0f0; }
.word-breakdown-table th { font-weight: 600; color: #555; }
.not-found { color: #bbb; }
.feedback-card { grid-column: 1 / -1; }
.feedback-form { display: flex; flex-direction: column; gap: .75rem; }
.rating-stars { display: flex; flex-direction: row-reverse; gap: .2rem; }
.rating-stars input { display: none; }
.rating-stars label { font-size: 1.5rem; color: #ddd; cursor: pointer; }
.rating-stars input:checked ~ label, .rating-stars label:hover, .rating-stars label:hover ~ label { color: #f59e0b; }
.feedback-textarea { border: 1px solid #ddd; border-radius: 6px; padding: .6em .8em; font-size: .9rem; font-family: inherit; resize: vertical; }

/* Missing words */
.row-missing td { color: #c0392b; }
.missing-words-card { grid-column: 1 / -1; }
.missing-words-intro { font-size: .9rem; color: #666; margin-bottom: .75rem; }
.missing-words-list { display: flex; flex-wrap: wrap; gap: .5rem; }
.missing-word-item { display: flex; align-items: center; gap: .5rem; background: #fff8f8; border: 1px solid #f5c6c6; border-radius: 6px; padding: .35em .75em; }
.missing-word-text { font-weight: 600; color: #c0392b; }
.btn-xs { padding: .2em .6em; font-size: .75rem; border-radius: 4px; }
.btn-outline-primary { border: 1px solid var(--color-primary,#5C3A21); color: var(--color-primary,#5C3A21); background: transparent; text-decoration: none; cursor: pointer; }
.btn-outline-primary:hover { background: var(--color-primary,#5C3A21); color: #fff; }

/* Multi-suggest inline form */
.multi-suggest-form { margin-top: .5rem; }
.multi-suggest-grid { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 1rem; }
.multi-suggest-row { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
.multi-suggest-label { min-width: 140px; }
.multi-suggest-input { flex: 1; min-width: 180px; border: 1px solid #ddd; border-radius: 6px; padding: .45em .75em; font-size: .9rem; font-family: inherit; outline: none; transition: border-color .15s; }
.multi-suggest-input:focus { border-color: var(--color-primary,#5C3A21); box-shadow: 0 0 0 2px rgba(92,58,33,.12); }
.multi-suggest-na { color: #bbb; font-size: .85rem; }
.multi-suggest-actions { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
.multi-suggest-hint { font-size: .8rem; color: #999; }

/* Quick links */
.translate-quick-links { display: flex; flex-wrap: wrap; gap: .75rem; justify-content: center; margin-top: 1rem; }
.quick-link { display: flex; align-items: center; gap: .4rem; color: var(--color-primary, #5C3A21); text-decoration: none; font-size: .9rem; padding: .4em .8em; border: 1px solid #ddd; border-radius: 20px; background: #fff; transition: all .2s; }
.quick-link:hover { background: var(--color-primary, #5C3A21); color: #fff; border-color: var(--color-primary, #5C3A21); }
</style>

<script>
(function() {
    const textarea  = document.getElementById('input_text');
    const charCount = document.getElementById('charCount');
    const clearBtn  = document.getElementById('clearInput');
    const swapBtn   = document.getElementById('swapLangs');
    const srcSel    = document.getElementById('source_language');
    const tgtSel    = document.getElementById('target_language');
    const form      = document.getElementById('translateForm');

    const charLimit = <?= empty($isAdmin) ? '1000' : 'null' ?>;
    function updateCount() {
        if (textarea && charCount) {
            charCount.textContent = textarea.value.length + ' / ' + (charLimit !== null ? charLimit : '∞');
        }
    }
    if (textarea) {
        textarea.addEventListener('input', updateCount);
        updateCount();
    }

    if (clearBtn && textarea) {
        clearBtn.addEventListener('click', function() {
            textarea.value = '';
            updateCount();
            textarea.focus();
        });
    }

    if (swapBtn && srcSel && tgtSel) {
        swapBtn.addEventListener('click', function() {
            const src = srcSel.value;
            srcSel.value = tgtSel.value;
            tgtSel.value = src;
        });
    }

    // Keep source != target
    function syncLangs(changed, other) {
        if (changed.value === other.value) {
            other.value = changed.value === 'tiv' ? 'english' : 'tiv';
        }
    }
    if (srcSel && tgtSel) {
        srcSel.addEventListener('change', () => syncLangs(srcSel, tgtSel));
        tgtSel.addEventListener('change', () => syncLangs(tgtSel, srcSel));
    }

    // Loading state on submit
    if (form) {
        form.addEventListener('submit', function() {
            const btn = document.getElementById('translateBtn');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Translating…';
            }
        });
    }
})();
</script>
