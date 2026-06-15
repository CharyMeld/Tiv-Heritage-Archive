<?php
// Only render widget if Charymeld is enabled
if (!defined('CHARYMELD_ENABLED') || !CHARYMELD_ENABLED) return;
$cymCsrf = Security::generateCSRFToken();
?>

<!-- ════════════════════════════════════════════════════════
     CHARYMELD AI ASSISTANT WIDGET
════════════════════════════════════════════════════════ -->

<!-- Floating trigger button -->
<button class="cym-fab" id="cymFab" aria-label="Open Tiv AI assistant" title="Ask Tiv AI">
    <span class="cym-fab-icon">
        <!-- Chat bubble icon -->
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            <path d="M8 9h8M8 13h5"/>
        </svg>
    </span>
    <span class="cym-fab-label">Tiv AI</span>
    <span class="cym-fab-badge" id="cymBadge" style="display:none">1</span>
</button>

<!-- Chat panel -->
<div class="cym-panel" id="cymPanel" aria-hidden="true" role="dialog" aria-label="Tiv AI assistant">

    <!-- Header -->
    <div class="cym-header">
        <div class="cym-header-left">
            <div class="cym-avatar">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L9.5 9.5 2 12l7.5 2.5L12 22l2.5-7.5L22 12l-7.5-2.5z"/>
                </svg>
            </div>
            <div class="cym-header-info">
                <span class="cym-name">Tiv AI</span>
                <span class="cym-status"><span class="cym-status-dot"></span>Tiv Heritage Assistant</span>
            </div>
        </div>
        <div class="cym-header-right">
            <button class="cym-clear-btn" id="cymClear" title="Clear conversation" aria-label="Clear chat">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                </svg>
            </button>
            <button class="cym-close-btn" id="cymClose" aria-label="Close Tiv AI">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Messages area -->
    <div class="cym-messages" id="cymMessages">
        <!-- Welcome message -->
        <div class="cym-msg cym-msg--ai cym-msg--welcome">
            <div class="cym-msg-avatar">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L9.5 9.5 2 12l7.5 2.5L12 22l2.5-7.5L22 12l-7.5-2.5z"/>
                </svg>
            </div>
            <div class="cym-msg-bubble">
                <p><strong>Msugh u za van! I'm Tiv AI</strong> 👋</p>
                <p>I'm your AI assistant for the Tiv Heritage Archive. I can help you with:</p>
                <ul>
                    <li>🔤 Tiv words &amp; their English meanings</li>
                    <li>🏷️ Tiv names &amp; their significance</li>
                    <li>📜 Tiv proverbs &amp; wisdom</li>
                    <li>🎉 Festivals, foods, plants &amp; more</li>
                </ul>
                <p class="cym-hint">Try: <em>"What does 'Aôndo' mean?"</em> or <em>"Tell me about the Kwagh-hir festival"</em></p>
            </div>
        </div>
    </div>

    <!-- Input area -->
    <div class="cym-input-area">
        <div class="cym-input-wrap" id="cymInputWrap">
            <button class="cym-mic-btn" id="cymMic" title="Voice input" aria-label="Start voice input">
                <svg id="cymMicIcon" xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3z"/>
                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                    <line x1="12" x2="12" y1="19" y2="22"/>
                </svg>
            </button>
            <textarea id="cymInput" class="cym-textarea"
                      placeholder="Ask anything about Tiv culture…"
                      rows="1" maxlength="800" aria-label="Message Tiv AI"></textarea>
            <button class="cym-send-btn" id="cymSend" aria-label="Send message" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m22 2-7 20-4-9-9-4z"/>
                    <path d="M22 2 11 13"/>
                </svg>
            </button>
        </div>
        <p class="cym-footer-note">Powered by Claude AI &bull; Searches the live Tiv archive</p>
    </div>

</div>

<script>
(function () {
    /* ── Config ─────────────────────────────────── */
    var CSRF      = <?= json_encode($cymCsrf) ?>;
    var ENDPOINT  = '<?= url('charymeld/chat') ?>';
    var history   = [];   // [{role:'user',content:''},{role:'assistant',content:''}]

    /* ── Elements ───────────────────────────────── */
    var fab      = document.getElementById('cymFab');
    var panel    = document.getElementById('cymPanel');
    var closeBtn = document.getElementById('cymClose');
    var clearBtn = document.getElementById('cymClear');
    var input    = document.getElementById('cymInput');
    var sendBtn  = document.getElementById('cymSend');
    var micBtn   = document.getElementById('cymMic');
    var msgs     = document.getElementById('cymMessages');
    var badge    = document.getElementById('cymBadge');
    var wrap     = document.getElementById('cymInputWrap');

    var isOpen   = false;
    var isBusy   = false;
    var hasNewMsg = false;

    /* ── Open / Close ───────────────────────────── */
    function open() {
        isOpen = true;
        panel.classList.add('cym-panel--open');
        panel.setAttribute('aria-hidden', 'false');
        fab.classList.add('cym-fab--open');
        hideBadge();
        setTimeout(function () { input.focus(); }, 300);
    }
    function close() {
        isOpen = false;
        panel.classList.remove('cym-panel--open');
        panel.setAttribute('aria-hidden', 'true');
        fab.classList.remove('cym-fab--open');
    }
    function showBadge() {
        if (!isOpen) { badge.style.display = 'flex'; }
    }
    function hideBadge() { badge.style.display = 'none'; }

    fab.addEventListener('click', function () { isOpen ? close() : open(); });
    closeBtn.addEventListener('click', close);

    clearBtn.addEventListener('click', function () {
        history = [];
        msgs.querySelectorAll('.cym-msg:not(.cym-msg--welcome)').forEach(function (el) { el.remove(); });
    });

    /* ── Auto-resize textarea ───────────────────── */
    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        sendBtn.disabled = this.value.trim() === '';
    });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!sendBtn.disabled && !isBusy) sendMessage();
        }
    });
    sendBtn.addEventListener('click', function () {
        if (!isBusy) sendMessage();
    });

    /* ── Send message ───────────────────────────── */
    function sendMessage() {
        var text = input.value.trim();
        if (!text || isBusy) return;

        appendMsg('user', text);
        history.push({ role: 'user', content: text });

        input.value = '';
        input.style.height = 'auto';
        sendBtn.disabled = true;
        isBusy = true;

        var typingEl = appendTyping();

        var body = new FormData();
        body.append('csrf_token', CSRF);
        body.append('message',    text);
        body.append('history',    JSON.stringify(history.slice(-6)));

        fetch(ENDPOINT, { method: 'POST', body: body })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                typingEl.remove();
                if (data.error) {
                    appendMsg('ai', '⚠️ ' + data.error);
                } else {
                    appendMsg('ai', data.reply);
                    history.push({ role: 'assistant', content: data.reply });
                    if (!isOpen) showBadge();
                }
            })
            .catch(function () {
                typingEl.remove();
                appendMsg('ai', '⚠️ Network error. Please check your connection and try again.');
            })
            .finally(function () { isBusy = false; });
    }

    /* ── Append a chat bubble ───────────────────── */
    function appendMsg(role, text) {
        var div = document.createElement('div');
        div.className = 'cym-msg cym-msg--' + (role === 'user' ? 'user' : 'ai');

        var html = '';
        if (role === 'ai') {
            html += '<div class="cym-msg-avatar">'
                  + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L9.5 9.5 2 12l7.5 2.5L12 22l2.5-7.5L22 12l-7.5-2.5z"/></svg>'
                  + '</div>';
        }
        html += '<div class="cym-msg-bubble">' + formatText(text) + '</div>';
        div.innerHTML = html;
        msgs.appendChild(div);
        msgs.scrollTop = msgs.scrollHeight;
        return div;
    }

    /* ── Typing indicator ───────────────────────── */
    function appendTyping() {
        var div = document.createElement('div');
        div.className = 'cym-msg cym-msg--ai cym-msg--typing';
        div.innerHTML = '<div class="cym-msg-avatar">'
            + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L9.5 9.5 2 12l7.5 2.5L12 22l2.5-7.5L22 12l-7.5-2.5z"/></svg>'
            + '</div>'
            + '<div class="cym-msg-bubble cym-typing-bubble">'
            + '<span class="cym-dot"></span><span class="cym-dot"></span><span class="cym-dot"></span>'
            + '</div>';
        msgs.appendChild(div);
        msgs.scrollTop = msgs.scrollHeight;
        return div;
    }

    /* ── Basic markdown-like formatting ─────────── */
    function formatText(text) {
        // Escape HTML first
        text = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        // Bold **text**
        text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        // Italic *text*
        text = text.replace(/\*(.+?)\*/g, '<em>$1</em>');
        // Bullet lists (lines starting with •, -, *)
        text = text.replace(/^[•\-]\s+(.+)$/gm, '<li>$1</li>');
        text = text.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
        // Newlines
        text = text.replace(/\n{2,}/g, '</p><p>').replace(/\n/g, '<br>');
        return '<p>' + text + '</p>';
    }

    /* ── Voice Input (Web Speech API) ───────────── */
    var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        micBtn.style.display = 'none';
    } else {
        var recognition = new SpeechRecognition();
        recognition.lang = 'en-NG';        // Nigerian English; falls back to en
        recognition.continuous = false;
        recognition.interimResults = true;

        var isListening = false;

        micBtn.addEventListener('click', function () {
            if (isListening) {
                recognition.stop();
            } else {
                try {
                    recognition.start();
                } catch(e) { /* already started */ }
            }
        });

        recognition.onstart = function () {
            isListening = true;
            micBtn.classList.add('cym-mic--active');
            input.placeholder = '🎙️ Listening…';
        };

        recognition.onresult = function (e) {
            var transcript = '';
            for (var i = e.resultIndex; i < e.results.length; i++) {
                transcript += e.results[i][0].transcript;
            }
            input.value = transcript;
            input.dispatchEvent(new Event('input'));
        };

        recognition.onend = function () {
            isListening = false;
            micBtn.classList.remove('cym-mic--active');
            input.placeholder = 'Ask anything about Tiv culture…';
            // Auto-send if we got text
            if (input.value.trim() && !isBusy) {
                setTimeout(sendMessage, 300);
            }
        };

        recognition.onerror = function (e) {
            isListening = false;
            micBtn.classList.remove('cym-mic--active');
            input.placeholder = 'Ask anything about Tiv culture…';
            if (e.error !== 'aborted' && e.error !== 'no-speech') {
                appendMsg('ai', '⚠️ Microphone error: ' + e.error + '. Please type your question instead.');
            }
        };
    }

    /* ── Close on outside click ─────────────────── */
    document.addEventListener('click', function (e) {
        if (isOpen && !panel.contains(e.target) && e.target !== fab && !fab.contains(e.target)) {
            close();
        }
    });

    /* ── Escape key ─────────────────────────────── */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) close();
    });

})();
</script>
