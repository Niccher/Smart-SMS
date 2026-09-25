<?= $this->extend('Layouts/admin') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-3 py-2">
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">
                <i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i>AI Financial Assistant
            </h1>
            <p class="text-muted small mb-0">Ask questions in English or Sheng about your spending habits, cash flow, counterparties, and budgets.</p>
        </div>
        <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
            <!-- Active Model Badge -->
            <div id="aiModelBadge" class="badge bg-light text-dark border px-3 py-2 d-flex align-items-center gap-2 shadow-xs">
                <span id="aiModelStatusDot" class="spinner-grow spinner-grow-sm text-primary" style="width: 0.6rem; height: 0.6rem;"></span>
                <span class="text-muted small">Model:</span>
                <strong id="aiModelName" class="font-monospace text-primary">Checking...</strong>
                <span id="aiProviderBadge" class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1 d-none font-monospace small"></span>
            </div>
            <button id="clearChatBtn" class="btn btn-outline-secondary btn-sm" title="Clear conversation history">
                <i class="fa-solid fa-trash-can me-1"></i> Clear
            </button>
        </div>
    </div>

    <!-- Chat Card Container -->
    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
        <!-- Quick Starter Pills -->
        <div class="card-header bg-light bg-opacity-75 border-bottom py-2 px-3">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="small text-muted fw-semibold me-1"><i class="fa-regular fa-lightbulb text-warning me-1"></i>Suggested Prompts:</span>
                <button class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 prompt-chip" data-prompt="How much did I spend this month compared to last month?">
                    💸 Monthly spending trend
                </button>
                <button class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 prompt-chip" data-prompt="Who are my top 5 recipients by total money sent?">
                    👥 Top 5 recipients
                </button>
                <button class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 prompt-chip" data-prompt="How much have I spent on Fuliza or M-Shwari fees?">
                    ⚡ Fuliza / Loan fees
                </button>
                <button class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 prompt-chip" data-prompt="Can I afford a KES 15,000 expense this weekend?">
                    🎯 Can I afford KES 15,000?
                </button>
            </div>
        </div>

        <!-- Chat Stream Body -->
        <div class="card-body p-3 p-md-4" id="chatStream" style="height: 520px; overflow-y: auto; background-color: var(--bs-body-bg, #f8f9fa);">
            <?php if (empty($chat_history)): ?>
            <!-- Welcome Assistant Bubble -->
            <div class="d-flex mb-3 ai-message-row" id="welcomeMsgRow">
                <div class="flex-shrink-0 me-2">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-xs" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                </div>
                <div class="flex-grow-1" style="max-width: 82%;">
                    <div class="p-3 rounded-3 shadow-xs bg-white text-dark border">
                        <div class="fw-semibold text-primary mb-1 small d-flex align-items-center gap-2">
                            <span>M-Pesa Smart Advisor</span>
                            <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.65rem;">System</span>
                        </div>
                        <p class="mb-2">
                            Sasa! I am your personal M-Pesa AI financial advisor. I have access to your analyzed transaction metrics, inflow/outflow, and spending patterns.
                        </p>
                        <p class="mb-0 text-muted small">
                            Feel free to ask me anything in English or Sheng about your balances, biggest expenses, or whether you can afford an upcoming purchase.
                        </p>
                    </div>
                    <div class="small text-muted mt-1 ms-1" style="font-size: 0.72rem;">Just now</div>
                </div>
            </div>
            <?php else: ?>
                <?php foreach ($chat_history as $msg): ?>
                    <?php if ($msg['role'] === 'user'): ?>
                        <div class="d-flex justify-content-end mb-3">
                            <div style="max-width: 80%;">
                                <div class="p-3 shadow-xs chat-bubble-user">
                                    <p class="mb-0 text-white"><?= nl2br(esc($msg['message'])) ?></p>
                                </div>
                                <div class="small text-muted text-end mt-1 me-1 d-flex align-items-center justify-content-end gap-1" style="font-size: 0.72rem;">
                                    <?php if (($msg['platform'] ?? 'webapp') === 'mobile'): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle" title="Sent from Android Mobile"><i class="fa-solid fa-mobile-screen me-1"></i>Mobile</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" title="Sent from WebApp"><i class="fa-solid fa-laptop me-1"></i>Web</span>
                                    <?php endif; ?>
                                    <span><?= !empty($msg['created_at']) ? date('M j, H:i', strtotime($msg['created_at'])) : '' ?></span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="d-flex mb-3 ai-message-row">
                            <div class="flex-shrink-0 me-2">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-xs" style="width: 38px; height: 38px;">
                                    <i class="fa-solid fa-robot"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1" style="max-width: 82%;">
                                <div class="p-3 rounded-3 shadow-xs chat-bubble-ai">
                                    <div class="fw-semibold text-primary mb-1 small d-flex align-items-center">
                                        <span>M-Pesa Smart Advisor</span>
                                        <?php if (!empty($msg['model'])): ?>
                                            <span class="badge bg-secondary-subtle text-secondary font-monospace ms-2" style="font-size: 0.65rem;"><?= esc($msg['model']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($msg['latency_ms'])): ?>
                                            <span class="badge bg-light text-muted border font-monospace ms-2" style="font-size: 0.65rem;"><?= (int)$msg['latency_ms'] ?>ms</span>
                                        <?php endif; ?>
                                        <?php if (($msg['platform'] ?? 'webapp') === 'mobile'): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle ms-2" title="Replied to Mobile app request" style="font-size: 0.65rem;"><i class="fa-solid fa-mobile-screen me-1"></i>Mobile</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="chat-text mb-0"><?= nl2br(esc($msg['message'])) ?></div>
                                </div>
                                <div class="small text-muted mt-1 ms-1" style="font-size: 0.72rem;"><?= !empty($msg['created_at']) ? date('M j, H:i', strtotime($msg['created_at'])) : '' ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Typing Indicator (Hidden by default) -->
        <div id="typingIndicator" class="px-4 py-2 border-top bg-light d-none">
            <div class="d-flex align-items-center gap-2 text-muted small">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span>Analyzing your financial data and generating response...</span>
            </div>
        </div>

        <!-- Chat Input Form -->
        <div class="card-footer bg-white border-top p-3">
            <form id="chatForm" class="d-flex align-items-center gap-2">
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="chatCsrfToken">
                <div class="flex-grow-1 position-relative">
                    <textarea 
                        id="chatInput" 
                        class="form-control py-2 ps-3 pe-5" 
                        placeholder="Ask anything about your M-Pesa transactions... (Enter to send, Shift+Enter for newline)" 
                        rows="1" 
                        style="resize: none; max-height: 120px; font-size: 0.95rem;"></textarea>
                </div>
                <button type="submit" id="sendBtn" class="btn btn-primary px-3 py-2 fw-semibold d-flex align-items-center gap-1 shadow-sm">
                    <span class="d-none d-sm-inline">Send</span>
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.chat-bubble-user {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
    color: #ffffff;
    border-radius: 14px 14px 2px 14px;
}
.chat-bubble-ai {
    background: #ffffff;
    color: #212529;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 14px 14px 14px 2px;
}
[data-bs-theme="dark"] .chat-bubble-ai {
    background: #2b3035;
    color: #f8f9fa;
    border-color: rgba(255, 255, 255, 0.12);
}
.prompt-chip {
    transition: all 0.15s ease-in-out;
    font-size: 0.82rem;
}
.prompt-chip:hover {
    transform: translateY(-1px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chatStream = document.getElementById('chatStream');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const typingIndicator = document.getElementById('typingIndicator');
    const aiModelName = document.getElementById('aiModelName');
    const aiProviderBadge = document.getElementById('aiProviderBadge');
    const aiModelStatusDot = document.getElementById('aiModelStatusDot');
    const clearChatBtn = document.getElementById('clearChatBtn');
    const csrfInput = document.getElementById('chatCsrfToken');

    let conversationHistory = <?= json_encode(array_map(function($m) {
        return [
            'role' => $m['role'],
            'content' => $m['message']
        ];
    }, $chat_history ?? [])) ?>;

    // Auto-expand textarea height
    chatInput.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Enter to submit, Shift+Enter for new line
    chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });

    // Fetch active model info
    async function fetchModelInfo() {
        try {
            const resp = await fetch('<?= base_url('dashboard/chat/info') ?>');
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            const data = await resp.json();
            if (data.model) {
                aiModelName.textContent = data.model;
                if (data.provider) {
                    aiProviderBadge.textContent = data.provider.toUpperCase();
                    aiProviderBadge.classList.remove('d-none');
                }
                aiModelStatusDot.className = 'spinner-grow spinner-grow-sm text-success';
            } else {
                aiModelName.textContent = 'Active LLM';
                aiModelStatusDot.className = 'spinner-grow spinner-grow-sm text-warning';
            }
        } catch (err) {
            aiModelName.textContent = 'Offline';
            aiModelStatusDot.className = 'spinner-grow spinner-grow-sm text-danger';
        }
    }
    fetchModelInfo();

    // Scroll chat to bottom
    function scrollToBottom() {
        chatStream.scrollTop = chatStream.scrollHeight;
    }

    // Escape HTML & basic markdown parser (bold, list, code)
    function formatMessageText(text) {
        if (!text) return '';
        let escaped = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Bold **text**
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Italic *text*
        escaped = escaped.replace(/\*(.*?)\*/g, '<em>$1</em>');
        // Backtick `code`
        escaped = escaped.replace(/`([^`]+)`/g, '<code class="bg-light px-1 py-0.5 rounded text-danger">$1</code>');
        // Line breaks
        escaped = escaped.replace(/\n/g, '<br>');

        return escaped;
    }

    // Append user message
    function appendUserMessage(text) {
        const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const row = document.createElement('div');
        row.className = 'd-flex justify-content-end mb-3';
        row.innerHTML = `
            <div style="max-width: 80%;">
                <div class="p-3 shadow-xs chat-bubble-user">
                    <p class="mb-0 text-white">${formatMessageText(text)}</p>
                </div>
                <div class="small text-muted text-end mt-1 me-1 d-flex align-items-center justify-content-end gap-1" style="font-size: 0.72rem;">
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" title="Sent from WebApp"><i class="fa-solid fa-laptop me-1"></i>Web</span>
                    <span>${timeStr}</span>
                </div>
            </div>
        `;
        chatStream.appendChild(row);
        scrollToBottom();
    }

    // Append AI response
    function appendAiMessage(reply, modelName, latencyMs) {
        const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const latencyBadge = latencyMs ? `<span class="badge bg-light text-muted border font-monospace ms-2" style="font-size: 0.65rem;">${latencyMs}ms</span>` : '';
        const modelBadge = modelName ? `<span class="badge bg-secondary-subtle text-secondary font-monospace ms-2" style="font-size: 0.65rem;">${modelName}</span>` : '';

        const row = document.createElement('div');
        row.className = 'd-flex mb-3 ai-message-row';
        row.innerHTML = `
            <div class="flex-shrink-0 me-2">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-xs" style="width: 38px; height: 38px;">
                    <i class="fa-solid fa-robot"></i>
                </div>
            </div>
            <div class="flex-grow-1" style="max-width: 82%;">
                <div class="p-3 rounded-3 shadow-xs chat-bubble-ai">
                    <div class="fw-semibold text-primary mb-1 small d-flex align-items-center">
                        <span>M-Pesa Smart Advisor</span>
                        ${modelBadge}
                        ${latencyBadge}
                    </div>
                    <div class="chat-text mb-0">${formatMessageText(reply)}</div>
                </div>
                <div class="small text-muted mt-1 ms-1" style="font-size: 0.72rem;">${timeStr}</div>
            </div>
        `;
        chatStream.appendChild(row);
        scrollToBottom();
    }

    // Send user message
    async function sendMessage(userText) {
        if (!userText || !userText.trim()) return;
        userText = userText.trim();

        appendUserMessage(userText);
        chatInput.value = '';
        chatInput.style.height = 'auto';

        // Show typing indicator
        typingIndicator.classList.remove('d-none');
        sendBtn.disabled = true;
        scrollToBottom();

        try {
            const csrfName = csrfInput ? csrfInput.name : 'mpesa_analyzer_csrf_token';
            const csrfVal = csrfInput ? csrfInput.value : '';

            const payload = {
                message: userText,
                history: conversationHistory.slice(-8)
            };
            if (csrfName && csrfVal) {
                payload[csrfName] = csrfVal;
            }

            const reqHeaders = {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (csrfVal) {
                reqHeaders['X-CSRF-TOKEN'] = csrfVal;
                if (csrfName) {
                    reqHeaders[csrfName] = csrfVal;
                }
            }

            const response = await fetch('<?= base_url('dashboard/chat/send') ?>', {
                method: 'POST',
                headers: reqHeaders,
                body: JSON.stringify(payload)
            });

            // If new CSRF token is provided in response headers, update it
            const newCsrf = response.headers.get('X-CSRF-TOKEN') || response.headers.get(csrfName);
            if (newCsrf && csrfInput) {
                csrfInput.value = newCsrf;
            }

            const data = await response.json();

            if (!response.ok) {
                const errMsg = data.error || ('Server error ' + response.status);
                appendAiMessage('⚠️ ' + errMsg, null, null);
            } else {
                appendAiMessage(data.reply, data.model, data.latency_ms);
                // Keep conversation history
                conversationHistory.push({ role: 'user', content: userText });
                conversationHistory.push({ role: 'assistant', content: data.reply });
                if (data.model) {
                    aiModelName.textContent = data.model;
                }
            }
        } catch (err) {
            appendAiMessage('⚠️ Connection error: ' + err.message + '. Please ensure the ML microservice is running.', null, null);
        } finally {
            typingIndicator.classList.add('d-none');
            sendBtn.disabled = false;
            chatInput.focus();
            scrollToBottom();
        }
    }

    // Form submit
    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        sendMessage(chatInput.value);
    });

    // Prompt chips click
    document.querySelectorAll('.prompt-chip').forEach(btn => {
        btn.addEventListener('click', function () {
            const prompt = this.getAttribute('data-prompt');
            if (prompt) {
                sendMessage(prompt);
            }
        });
    });

    // Clear conversation
    clearChatBtn.addEventListener('click', async function () {
        if (!confirm('Clear your chat conversation history?')) return;
        try {
            clearChatBtn.disabled = true;
            await fetch('<?= base_url('dashboard/chat/clear') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            conversationHistory = [];
            chatStream.innerHTML = `
                <div class="d-flex mb-3 ai-message-row">
                    <div class="flex-shrink-0 me-2">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-xs" style="width: 38px; height: 38px;">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1" style="max-width: 82%;">
                        <div class="p-3 rounded-3 shadow-xs chat-bubble-ai">
                            <div class="fw-semibold text-primary mb-1 small d-flex align-items-center gap-2">
                                <span>M-Pesa Smart Advisor</span>
                                <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.65rem;">System</span>
                            </div>
                            <p class="mb-0 text-muted small">
                                Conversation history cleared. Feel free to ask a new question about your M-Pesa finances!
                            </p>
                        </div>
                        <div class="small text-muted mt-1 ms-1" style="font-size: 0.72rem;">Just now</div>
                    </div>
                </div>
            `;
        } catch (err) {
            alert('Failed to clear conversation history: ' + err.message);
        } finally {
            clearChatBtn.disabled = false;
        }
    });

    // Auto-scroll to latest message on page load
    scrollToBottom();
});
</script>
<?= $this->endSection() ?>
