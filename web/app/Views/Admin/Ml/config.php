<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Config - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); }
    .cfg-label { font-weight:600; font-size:.82rem; color:#33415c; margin-bottom:.3rem; }
    .cfg-desc { font-size:.76rem; color:#6c757d; }
    .cfg-default { font-size:.74rem; color:#dc3545; }
    .cfg-default.unset { font-weight:600; }
    .cfg-default i { margin-right:.25rem; }
    .input-group-text.cfg-ico { background:#f4f6fb; color:var(--primary); border:1px solid #dfe4ef; font-size:.85rem; }
    .form-control { border:1px solid #dfe4ef; }

</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-sliders me-2"></i> ML Config</h2>
        <p class="text-secondary mb-0">Tune how the ML backend classifies and extracts SMS financial data.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="card settings-card">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'config', 'status' => $status]) ?>

        <!-- ML Backend Connection Configuration (Always Accessible) -->
        <div class="card bg-light border-0 mb-4 p-3 rounded-3">
            <h6 class="fw-bold mb-2 text-primary"><i class="fa-solid fa-network-wired me-2"></i>ML Microservice Endpoint URL</h6>
            <form id="urlConfigForm">
                <?= csrf_field() ?>
                <div class="row g-2 align-items-center">
                    <div class="col-md-7 col-lg-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-primary"><i class="fa-solid fa-link"></i></span>
                            <input type="text" class="form-control border-start-0" id="ml_backend_url" name="ml_backend_url" value="<?= esc($ml_backend_url ?? config('MlBackend')->baseUrl) ?>" placeholder="http://ml-mpesa-analyzer:9050">
                        </div>
                    </div>
                    <div class="col-md-5 col-lg-4 d-flex gap-2">
                        <button class="btn btn-outline-primary fw-semibold flex-fill" type="button" id="btnTestBackendUrl">
                            <i class="fa-solid fa-plug me-1"></i> Test Endpoint
                        </button>
                        <button class="btn btn-primary fw-semibold flex-fill" type="submit" id="btnSaveUrlOnly">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save URL
                        </button>
                    </div>
                </div>
                <div class="cfg-desc mt-2 small text-muted">
                    The internal or public HTTP address where WebApp connects to the Python ML container (e.g. <code>http://ml-mpesa-analyzer:9050</code> or Railway private network URL).
                </div>
            </form>
        </div>

        <?php if (!$status['reachable']): ?>
            <div class="alert alert-warning mb-0 p-3">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                The ML microservice is currently unreachable at <code><?= esc($ml_backend_url ?? config('MlBackend')->baseUrl) ?></code>. Update and save the URL above, or check container deployment on Railway to load LLM parameters.
            </div>
        <?php else: ?>
            <?php $cfg = $status['app'] ?? []; ?>

            <div class="alert alert-light border mb-4 small">
                <i class="fa-solid fa-envelope me-1"></i>
                The LLM processes short SMS messages, so the defaults below are tuned for fast, low-cost,
                deterministic extraction. Values in <span class="text-danger">red</span> show what the backend
                falls back to when a field is left empty — a llama.cpp restart applies context, batch and GPU changes.
            </div>

            <form id="configForm">
                <?= csrf_field() ?>

                <input type="hidden" name="llm_gemini_api_key" id="key_gemini" value="<?= esc($cfg['llm_gemini_api_key'] ?? '') ?>">
                <input type="hidden" name="llm_deepseek_api_key" id="key_deepseek" value="<?= esc($cfg['llm_deepseek_api_key'] ?? '') ?>">
                <input type="hidden" name="llm_openai_api_key" id="key_openai" value="<?= esc($cfg['llm_openai_api_key'] ?? '') ?>">
                <input type="hidden" name="llm_groq_api_key" id="key_groq" value="<?= esc($cfg['llm_groq_api_key'] ?? '') ?>">
                <input type="hidden" name="llm_mistral_api_key" id="key_mistral" value="<?= esc($cfg['llm_mistral_api_key'] ?? '') ?>">
                <input type="hidden" name="llm_openrouter_api_key" id="key_openrouter" value="<?= esc($cfg['llm_openrouter_api_key'] ?? '') ?>">
                <input type="hidden" name="llm_cohere_api_key" id="key_cohere" value="<?= esc($cfg['llm_cohere_api_key'] ?? '') ?>">
                <input type="hidden" name="llm_kimi_api_key" id="key_kimi" value="<?= esc($cfg['llm_kimi_api_key'] ?? '') ?>">
                <input type="hidden" name="llm_nemotron_api_key" id="key_nemotron" value="<?= esc($cfg['llm_nemotron_api_key'] ?? '') ?>">
                <input type="hidden" name="llm_xai_api_key" id="key_xai" value="<?= esc($cfg['llm_xai_api_key'] ?? '') ?>">

                <?php
                $engine = $cfg['llm_engine'] ?? 'local';
                $localFields = [
                    'llm_provider'    => ['LLM Provider', 'fa-server', 'Model provider type for local execution.', 'openai-compatible', 'text', null, null, null],
                    'llm_base_url'    => ['Base URL', 'fa-link', 'Local API endpoint base URL.', 'http://localhost:8080/v1', 'text', null, null, null],
                    'llm_api_key'     => ['API Key', 'fa-key', 'Authorization key for local LLM (usually not needed).', 'not-needed', 'text', null, null, null],
                    'llm_model'       => ['LLM Model', 'fa-microchip', 'Model name sent to the local provider.', 'qwen2.5-1.5b-instruct', 'text', null, null, null],
                    'llm_ctx_size'    => ['Context Size', 'fa-arrows-left-right', 'llama.cpp context window size.', 8192, 'number', 256, 131072, null],
                    'llm_batch_size'  => ['Prompt Batch', 'fa-layer-group', 'llama.cpp prompt processing batch size.', 512, 'number', 1, 8192, null],
                    'n_gpu_layers'    => ['GPU Layers', 'fa-memory', 'Layers offloaded to GPU (0 = CPU only).', 0, 'number', 0, 512, null],
                    'llm_max_tokens'  => ['Max Tokens', 'fa-note-sticky', 'Maximum output length per LLM call.', 512, 'number', 256, 8192, null],
                    'llm_temperature' => ['Temperature', 'fa-thermometer-half', 'Sampling randomness.', 0.1, 'number', 0, 2, 0.05],
                    'batch_size'      => ['SMS Batch Size', 'fa-bolt', 'How many unprocessed SMS are pulled per background poll.', 50, 'number', 1, 500, null],
                    'max_retries'     => ['Max Retries', 'fa-rotate', 'Retries for failed LLM calls before marking SMS as error.', 3, 'number', 0, 10, null],
                    'poll_interval'   => ['Poll Interval (s)', 'fa-clock', 'Seconds between background polls.', 30, 'number', 5, 3600, null],
                ];

                $externalFields = [
                    'llm_external_provider' => ['External Provider', 'fa-server', 'Cloud provider type (e.g. openai-compatible).', 'openai-compatible', 'text', null, null, null],
                    'llm_external_base_url' => ['External Base URL', 'fa-link', 'External API endpoint base URL.', '', 'text', null, null, null],
                    'llm_external_api_key'  => ['External API Key', 'fa-key', 'Secret authorization key for the external API.', '', 'text', null, null, null],
                    'llm_external_model'    => ['External Model', 'fa-microchip', 'Model name (e.g. gemini-1.5-flash).', '', 'text', null, null, null],
                    'llm_external_max_tokens' => ['Max Tokens', 'fa-note-sticky', 'Maximum output length per LLM call.', 2048, 'number', 256, 8192, null],
                    'llm_external_temperature'=> ['Temperature', 'fa-thermometer-half', 'Sampling randomness.', 0.2, 'number', 0, 2, 0.05],
                    'external_batch_size'    => ['SMS Batch Size', 'fa-bolt', 'How many unprocessed SMS are pulled per background poll.', 20, 'number', 1, 500, null],
                    'external_max_retries'   => ['Max Retries', 'fa-rotate', 'Retries for failed LLM calls before marking SMS as error.', 3, 'number', 0, 10, null],
                    'external_poll_interval' => ['Poll Interval (s)', 'fa-clock', 'Seconds between background polls.', 30, 'number', 5, 3600, null],
                ];
                ?>

                <!-- Engine Selector -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="cfg-label d-block text-uppercase text-secondary small fw-bold mb-2">Active LLM Engine</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="llm_engine" id="engineLocal" value="local" <?= $engine === 'local' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary py-2 fw-semibold" for="engineLocal">
                                <i class="fa-solid fa-desktop me-2"></i>Local Engine (llama.cpp)
                            </label>
                            
                            <input type="radio" class="btn-check" name="llm_engine" id="engineExternal" value="external" <?= $engine === 'external' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary py-2 fw-semibold" for="engineExternal">
                                <i class="fa-solid fa-cloud me-2"></i>External API Engine (Gemini, DeepSeek, etc.)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Local Engine Section -->
                <div id="localEngineSection" class="mb-4">
                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="fa-solid fa-desktop me-2"></i>Local Engine Configuration</h5>
                    
                    <?php
                    $activeModel = null;
                    foreach (($status['models'] ?? []) as $m) { if (!empty($m['active'])) { $activeModel = $m; break; } }
                    $activeMd = $activeModel['metadata'] ?? [];
                    ?>
                    <div class="card bg-light border-0 mb-4 p-3 rounded-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-3 bg-white rounded-circle shadow-sm text-primary">
                                    <i class="fa-solid fa-microchip fa-xl"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-dark"><?= esc($activeModel['filename'] ?? ($cfg['llm_model'] ?? 'Local Model')) ?></span>
                                        <?php if ($activeModel): ?>
                                            <span class="badge bg-success small"><i class="fa-solid fa-circle-check me-1"></i>Active GGUF</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary small">No GGUF Active</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 mt-1">
                                        <small class="text-muted"><i class="fa-solid fa-cube me-1"></i><?= esc($activeMd['n_params_label'] ?? '—') ?></small>
                                        <small class="text-muted"><i class="fa-solid fa-sliders me-1"></i><?= esc($activeMd['quantization'] ?? '—') ?></small>
                                        <small class="text-muted"><i class="fa-solid fa-arrows-left-right me-1"></i>ctx <?= esc($cfg['llm_ctx_size'] ?? ($activeMd['context_length'] ?? '8192')) ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" id="btnTestLocalEngine" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fa-solid fa-plug me-1"></i> Test Local Engine
                                </button>
                                <a href="<?= base_url('admin/ml/models') ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                                    <i class="fa-solid fa-box-open me-1"></i> Manage Models
                                </a>
                            </div>
                        </div>
                        <div id="localTestFeedback" class="mt-2 d-none"></div>
                    </div>

                    <div class="row g-4">
                        <?php foreach ($localFields as $name => [$label, $icon, $desc, $dflt, $type, $min, $max, $step]): ?>
                            <?php
                            $val = $cfg[$name] ?? null;
                            $isSet = $val !== null && $val !== '';
                            ?>
                            <div class="col-md-6 col-xl-4">
                                <label class="cfg-label" for="<?= esc($name) ?>"><?= esc($label) ?></label>
                                <div class="input-group">
                                    <span class="input-group-text cfg-ico"><i class="fa-solid <?= esc($icon) ?>"></i></span>
                                    <input type="<?= esc($type) ?>" class="form-control" id="<?= esc($name) ?>" name="<?= esc($name) ?>" value="<?= esc($val ?? '') ?>" <?= $min !== null ? 'min="' . $min . '"' : '' ?> <?= $max !== null ? 'max="' . $max . '"' : '' ?> <?= $step !== null ? 'step="' . $step . '"' : '' ?>>
                                </div>
                                <div class="cfg-desc mt-1"><?= esc($desc) ?></div>
                                <div class="cfg-default <?= $isSet ? '' : 'unset' ?>">
                                    <i class="fa-solid fa-circle-info"></i> <?= $isSet ? 'Default if unset: <code>' . esc($dflt) . '</code>' : 'No value set — using default <code>' . esc($dflt) . '</code>' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- External Engine Section -->
                <div id="externalEngineSection" class="mb-4">
                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2"><i class="fa-solid fa-cloud me-2"></i>External Engine Configuration</h5>
                    <div class="row g-4">
                        <?php foreach ($externalFields as $name => [$label, $icon, $desc, $dflt, $type, $min, $max, $step]): ?>
                            <?php
                            $val = $cfg[$name] ?? null;
                            $isSet = $val !== null && $val !== '';
                            ?>
                            <div class="col-md-6 col-xl-4">
                                <label class="cfg-label" for="<?= esc($name) ?>"><?= esc($label) ?></label>
                                <div class="input-group">
                                    <span class="input-group-text cfg-ico"><i class="fa-solid <?= esc($icon) ?>"></i></span>
                                    <?php if ($name === 'llm_external_provider'): ?>
                                        <select class="form-select" id="llm_external_provider" name="llm_external_provider">
                                            <option value="gemini" <?= ($val ?? '') === 'gemini' ? 'selected' : '' ?>>Google Gemini</option>
                                            <option value="deepseek" <?= ($val ?? '') === 'deepseek' ? 'selected' : '' ?>>DeepSeek</option>
                                            <option value="openai" <?= ($val ?? '') === 'openai' ? 'selected' : '' ?>>OpenAI</option>
                                            <option value="groq" <?= ($val ?? '') === 'groq' ? 'selected' : '' ?>>Groq</option>
                                            <option value="mistral" <?= ($val ?? '') === 'mistral' ? 'selected' : '' ?>>Mistral AI</option>
                                            <option value="openrouter" <?= ($val ?? '') === 'openrouter' ? 'selected' : '' ?>>OpenRouter</option>
                                            <option value="cohere" <?= ($val ?? '') === 'cohere' ? 'selected' : '' ?>>Cohere</option>
                                            <option value="kimi" <?= ($val ?? '') === 'kimi' ? 'selected' : '' ?>>Kimi (Moonshot)</option>
                                            <option value="nemotron" <?= ($val ?? '') === 'nemotron' ? 'selected' : '' ?>>Nemotron (NVIDIA)</option>
                                            <option value="xai" <?= ($val ?? '') === 'xai' ? 'selected' : '' ?>>x.ai (Grok)</option>
                                            <option value="openai-compatible" <?= ($val ?? '') === 'openai-compatible' ? 'selected' : '' ?>>Custom (OpenAI Compatible)</option>
                                        </select>
                                    <?php elseif ($name === 'llm_external_model'): ?>
                                        <div class="w-100">
                                            <select class="form-select mb-2" id="llm_external_model_select">
                                                <option value="gemini-3.5-flash" <?= ($val ?? '') === 'gemini-3.5-flash' ? 'selected' : '' ?>>Gemini 3.5 Flash (Recommended)</option>
                                                <option value="gemini-3.5-pro" <?= ($val ?? '') === 'gemini-3.5-pro' ? 'selected' : '' ?>>Gemini 3.5 Pro</option>
                                                <option value="gemini-2.0-flash" <?= ($val ?? '') === 'gemini-2.0-flash' ? 'selected' : '' ?>>Gemini 2.0 Flash</option>
                                                <option value="gemini-1.5-flash" <?= ($val ?? '') === 'gemini-1.5-flash' ? 'selected' : '' ?>>Gemini 1.5 Flash</option>
                                                <option value="gemini-1.5-pro" <?= ($val ?? '') === 'gemini-1.5-pro' ? 'selected' : '' ?>>Gemini 1.5 Pro</option>
                                                <option value="deepseek-chat" <?= ($val ?? '') === 'deepseek-chat' ? 'selected' : '' ?>>DeepSeek Chat (V3)</option>
                                                <option value="deepseek-reasoner" <?= ($val ?? '') === 'deepseek-reasoner' ? 'selected' : '' ?>>DeepSeek Reasoner (R1)</option>
                                                <option value="gpt-4o-mini" <?= ($val ?? '') === 'gpt-4o-mini' ? 'selected' : '' ?>>GPT-4o Mini</option>
                                                <option value="gpt-4o" <?= ($val ?? '') === 'gpt-4o' ? 'selected' : '' ?>>GPT-4o</option>
                                                <option value="llama-3.3-70b-versatile" <?= ($val ?? '') === 'llama-3.3-70b-versatile' ? 'selected' : '' ?>>Llama 3.3 70B (Groq)</option>
                                                <option value="llama-3.1-8b-instant" <?= ($val ?? '') === 'llama-3.1-8b-instant' ? 'selected' : '' ?>>Llama 3.1 8B Instant (Groq)</option>
                                                <option value="mistral-large-latest" <?= ($val ?? '') === 'mistral-large-latest' ? 'selected' : '' ?>>Mistral Large</option>
                                                <option value="mistral-small-latest" <?= ($val ?? '') === 'mistral-small-latest' ? 'selected' : '' ?>>Mistral Small</option>
                                                <option value="command-r-plus" <?= ($val ?? '') === 'command-r-plus' ? 'selected' : '' ?>>Cohere Command R+</option>
                                                <option value="moonshot-v1-8k" <?= ($val ?? '') === 'moonshot-v1-8k' ? 'selected' : '' ?>>Moonshot V1 8k (Kimi)</option>
                                                <option value="nvidia/llama-3.1-nemotron-70b-instruct" <?= ($val ?? '') === 'nvidia/llama-3.1-nemotron-70b-instruct' ? 'selected' : '' ?>>Llama 3.1 Nemotron 70B (NVIDIA)</option>
                                                <option value="grok-3" <?= ($val ?? '') === 'grok-3' ? 'selected' : '' ?>>Grok 3 (x.ai)</option>
                                                <option value="grok-3-mini" <?= ($val ?? '') === 'grok-3-mini' ? 'selected' : '' ?>>Grok 3 Mini (x.ai)</option>
                                                <?php
                                                $knownModels = ['gemini-3.5-flash','gemini-3.5-pro','gemini-2.0-flash','gemini-1.5-flash','gemini-1.5-pro','deepseek-chat','deepseek-reasoner','gpt-4o-mini','gpt-4o','llama-3.3-70b-versatile','llama-3.1-8b-instant','mistral-large-latest','mistral-small-latest','command-r-plus','moonshot-v1-8k','nvidia/llama-3.1-nemotron-70b-instruct','grok-3','grok-3-mini'];
                                                ?>
                                                <option value="custom" <?= !in_array($val, $knownModels, true) && $isSet ? 'selected' : '' ?>>Custom Model Name</option>
                                            </select>
                                            <input type="text" class="form-control" id="llm_external_model" name="llm_external_model" value="<?= esc($val ?? '') ?>" placeholder="Enter custom model name">
                                        </div>
                                    <?php elseif ($name === 'llm_external_api_key'): ?>
                                        <input type="text" class="form-control" id="llm_external_api_key" name="llm_external_api_key" value="<?= esc($val ?? '') ?>" placeholder="API Key">
                                    <?php else: ?>
                                        <input type="<?= esc($type) ?>" class="form-control" id="<?= esc($name) ?>" name="<?= esc($name) ?>" value="<?= esc($val ?? '') ?>" <?= $min !== null ? 'min="' . $min . '"' : '' ?> <?= $max !== null ? 'max="' . $max . '"' : '' ?> <?= $step !== null ? 'step="' . $step . '"' : '' ?>>
                                    <?php endif; ?>
                                </div>
                                <div class="cfg-desc mt-1"><?= esc($desc) ?></div>
                                <div class="cfg-default <?= $isSet ? '' : 'unset' ?>">
                                    <i class="fa-solid fa-circle-info"></i> <?= $isSet ? 'Default if unset: <code>' . esc($dflt) . '</code>' : 'No value set — using default <code>' . esc($dflt) . '</code>' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="col-12">
                            <div class="card border bg-light p-3 rounded-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <span class="fw-bold"><i class="fa-solid fa-vial-circle-check me-1 text-primary"></i> Test External API Connectivity</span>
                                        <small class="text-muted d-block">Verifies authorization, model compatibility, and latency against the provider before saving.</small>
                                    </div>
                                    <button class="btn btn-outline-primary rounded-pill px-4 fw-semibold" type="button" id="btnTestConnection">
                                        <i class="fa-solid fa-plug me-1"></i> Test Connection
                                    </button>
                                </div>
                                <div id="externalTestFeedback" class="mt-2 d-none"></div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Config
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
async function safeFetchJson(response) {
    const text = await response.text();
    if (!text || !text.trim()) {
        throw new Error(`Server returned an empty response (HTTP ${response.status}).`);
    }
    try {
        return JSON.parse(text);
    } catch (e) {
        const clean = text.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim();
        throw new Error(clean.substring(0, 250) || `Server error (HTTP ${response.status}).`);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const localSection = document.getElementById('localEngineSection');
    const externalSection = document.getElementById('externalEngineSection');

    function toggleEngineSections() {
        if (!localSection || !externalSection) return;
        const isExternal = document.getElementById('engineExternal')?.checked;
        if (isExternal) {
            localSection.style.display = 'none';
            externalSection.style.display = 'block';
        } else {
            localSection.style.display = 'block';
            externalSection.style.display = 'none';
        }
    }

    document.querySelectorAll('input[name="llm_engine"]').forEach(radio => {
        radio.addEventListener('change', toggleEngineSections);
    });

    const providerSelect = document.getElementById('llm_external_provider');
    const extBaseUrlInput = document.getElementById('llm_external_base_url');
    const extModelInput = document.getElementById('llm_external_model');
    const extModelSelect = document.getElementById('llm_external_model_select');
    const extApiKeyInput = document.getElementById('llm_external_api_key');

    const providerDefaults = {
        'gemini': {
            url: 'https://generativelanguage.googleapis.com/v1beta/openai/',
            model: 'gemini-3.5-flash',
            keyField: 'key_gemini'
        },
        'deepseek': {
            url: 'https://api.deepseek.com/v1',
            model: 'deepseek-chat',
            keyField: 'key_deepseek'
        },
        'openai': {
            url: 'https://api.openai.com/v1',
            model: 'gpt-4o-mini',
            keyField: 'key_openai'
        },
        'groq': {
            url: 'https://api.groq.com/openai/v1',
            model: 'llama-3.3-70b-versatile',
            keyField: 'key_groq'
        },
        'mistral': {
            url: 'https://api.mistral.ai/v1',
            model: 'mistral-large-latest',
            keyField: 'key_mistral'
        },
        'openrouter': {
            url: 'https://openrouter.ai/api/v1',
            model: 'google/gemini-2.5-flash',
            keyField: 'key_openrouter'
        },
        'cohere': {
            url: 'https://api.cohere.com/v1',
            model: 'command-r-plus',
            keyField: 'key_cohere'
        },
        'kimi': {
            url: 'https://api.moonshot.cn/v1',
            model: 'moonshot-v1-8k',
            keyField: 'key_kimi'
        },
        'nemotron': {
            url: 'https://integrate.api.nvidia.com/v1',
            model: 'nvidia/llama-3.1-nemotron-70b-instruct',
            keyField: 'key_nemotron'
        },
        'xai': {
            url: 'https://api.x.ai/v1',
            model: 'grok-3',
            keyField: 'key_xai'
        }
    };

    function toggleModelInput() {
        if (!extModelSelect || !extModelInput) return;
        if (extModelSelect.value === 'custom') {
            extModelInput.style.display = 'block';
        } else {
            extModelInput.style.display = 'none';
            extModelInput.value = extModelSelect.value;
        }
    }
    extModelSelect?.addEventListener('change', toggleModelInput);
    if (extModelSelect) {
        toggleModelInput();
    }

    providerSelect?.addEventListener('change', function() {
        const val = this.value;
        if (providerDefaults[val]) {
            if (extBaseUrlInput) extBaseUrlInput.value = providerDefaults[val].url;
            const defModel = providerDefaults[val].model;
            if (extModelSelect) {
                const opt = Array.from(extModelSelect.options).find(o => o.value === defModel);
                if (opt) {
                    extModelSelect.value = defModel;
                } else {
                    extModelSelect.value = 'custom';
                }
                toggleModelInput();
            }
            if (extModelInput) extModelInput.value = defModel;
            const hiddenKeyInput = document.getElementById(providerDefaults[val].keyField);
            if (extApiKeyInput && hiddenKeyInput) {
                extApiKeyInput.value = hiddenKeyInput.value;
            }
        } else {
            if (extApiKeyInput) extApiKeyInput.value = '';
        }
    });

    if (providerSelect) {
        if (!providerSelect.value || (providerSelect.value === 'openai-compatible' && (!extBaseUrlInput || !extBaseUrlInput.value))) {
            // Default to Gemini on initial page load if unset
            providerSelect.value = 'gemini';
            providerSelect.dispatchEvent(new Event('change'));
        } else {
            const val = providerSelect.value;
            if (providerDefaults[val]) {
                const hiddenKeyInput = document.getElementById(providerDefaults[val].keyField);
                if (extApiKeyInput && hiddenKeyInput) {
                    extApiKeyInput.value = hiddenKeyInput.value;
                }
            }
        }
    }

    extApiKeyInput?.addEventListener('input', function() {
        if (!providerSelect) return;
        const val = providerSelect.value;
        if (providerDefaults[val]) {
            const hiddenKeyInput = document.getElementById(providerDefaults[val].keyField);
            if (hiddenKeyInput) hiddenKeyInput.value = this.value;
        }
    });

    document.getElementById('btnTestLocalEngine')?.addEventListener('click', function() {
        const btn = this;
        const feedback = document.getElementById('localTestFeedback');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing...';
        if (feedback) {
            feedback.classList.remove('d-none');
            feedback.innerHTML = '<div class="alert alert-info py-2 px-3 small mb-0"><i class="fa-solid fa-spinner fa-spin me-2"></i>Pinging local engine and checking active model...</div>';
        }

        const data = new FormData();
        const mlUrlInput = document.getElementById('ml_backend_url');
        if (mlUrlInput && mlUrlInput.value.trim()) {
            data.append('ml_backend_url', mlUrlInput.value.trim());
        }
        data.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('<?= base_url('admin/ml/config/test-local') ?>', { method: 'POST', body: data })
            .then(r => safeFetchJson(r))
            .then(res => {
                if (res.status === 'ok') {
                    if (feedback) {
                        feedback.innerHTML = `<div class="alert alert-success py-2 px-3 small mb-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div><i class="fa-solid fa-circle-check me-2"></i><strong>Local Engine Online:</strong> Active model <code>${res.active_model || 'Loaded'}</code> (latency: <strong>${res.latency_ms} ms</strong>)</div>
                            <span class="badge bg-success">llama.cpp: ${res.llama_status || 'healthy'}</span>
                        </div>`;
                    }
                } else {
                    if (feedback) {
                        feedback.innerHTML = `<div class="alert alert-danger py-2 px-3 small mb-0">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Local Test Failed:</strong> ${res.message || 'Could not connect.'}
                        </div>`;
                    }
                }
            })
            .catch(err => {
                if (feedback) {
                    feedback.innerHTML = `<div class="alert alert-danger py-2 px-3 small mb-0"><i class="fa-solid fa-circle-xmark me-2"></i>${err.message}</div>`;
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-plug me-1"></i> Test Local Engine';
            });
    });

    document.getElementById('btnTestConnection')?.addEventListener('click', function() {
        const btn = this;
        const feedback = document.getElementById('externalTestFeedback');
        if (!providerSelect || !extBaseUrlInput || !extApiKeyInput || !extModelInput) return;

        const provider = providerSelect.value;
        const apiKey = extApiKeyInput.value.trim();
        const model = extModelInput.value.trim();
        const baseUrl = extBaseUrlInput.value.trim();

        if (!apiKey) {
            Swal.fire('API Key Required', `Please enter an API Key for ${providerSelect.options[providerSelect.selectedIndex]?.text || provider}.`, 'warning');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing API...';
        if (feedback) {
            feedback.classList.remove('d-none');
            feedback.innerHTML = `<div class="alert alert-info py-2 px-3 small mb-0"><i class="fa-solid fa-spinner fa-spin me-2"></i>Contacting <strong>${providerSelect.options[providerSelect.selectedIndex]?.text}</strong> with model <code>${model}</code>...</div>`;
        }

        const data = new FormData();
        const mlUrlInput = document.getElementById('ml_backend_url');
        if (mlUrlInput && mlUrlInput.value.trim()) {
            data.append('ml_backend_url', mlUrlInput.value.trim());
        }
        data.append('provider', provider);
        data.append('base_url', baseUrl);
        data.append('api_key', apiKey);
        data.append('model', model);
        data.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('<?= base_url('admin/ml/config/test') ?>', { method: 'POST', body: data })
            .then(r => safeFetchJson(r))
            .then(res => {
                if (res.status === 'success') {
                    if (feedback) {
                        feedback.innerHTML = `<div class="alert alert-success py-2 px-3 small mb-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div><i class="fa-solid fa-circle-check me-2"></i><strong>Connected Successfully!</strong> Provider <strong>${provider}</strong> responded with model <code>${model}</code></div>
                            <span class="badge bg-success">Status: OK</span>
                        </div>`;
                    }
                    Swal.fire({
                        title: 'Connection Successful!',
                        html: `<div class="text-start p-2">
                            <p class="mb-1 text-success"><i class="fa-solid fa-circle-check me-2"></i><strong>Provider:</strong> ${providerSelect.options[providerSelect.selectedIndex]?.text || provider}</p>
                            <p class="mb-1 text-success"><i class="fa-solid fa-microchip me-2"></i><strong>Model:</strong> ${model}</p>
                            <small class="text-muted d-block mt-2">API key is verified and authorized. You can save your configuration.</small>
                        </div>`,
                        icon: 'success',
                        confirmButtonText: 'Great'
                    });
                } else {
                    if (feedback) {
                        feedback.innerHTML = `<div class="alert alert-danger py-2 px-3 small mb-0">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Connection Error:</strong> ${res.message || 'API connection failed.'}
                        </div>`;
                    }
                    Swal.fire({
                        title: 'Connection Failed',
                        text: res.message || 'The external API provider rejected the connection or key.',
                        icon: 'error',
                        confirmButtonText: 'Close'
                    });
                }
            })
            .catch(err => {
                if (feedback) {
                    feedback.innerHTML = `<div class="alert alert-danger py-2 px-3 small mb-0"><i class="fa-solid fa-circle-xmark me-2"></i>${err.message}</div>`;
                }
                Swal.fire({ title: 'Error', text: err.message, icon: 'error', confirmButtonText: 'Close' });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-plug me-1"></i> Test Connection';
            });
    });

    document.getElementById('btnTestBackendUrl')?.addEventListener('click', function() {
        const btn = this;
        const urlInput = document.getElementById('ml_backend_url');
        const targetUrl = urlInput ? urlInput.value.trim() : '';

        if (!targetUrl) {
            Swal.fire({ title: 'Input Required', text: 'Please enter an ML Backend URL to test.', icon: 'warning' });
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing...';

        const data = new FormData();
        data.append('url', targetUrl);
        data.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('<?= base_url('admin/ml/config/test-url') ?>', { method: 'POST', body: data })
            .then(r => safeFetchJson(r))
            .then(res => {
                if (res.status === 'success') {
                    if (res.db_configured) {
                        Swal.fire({
                            title: 'ML Backend Reachable!',
                            html: `<div class="text-start p-2">
                                <p class="mb-2 text-success"><i class="fa-solid fa-circle-check me-2"></i><strong>ML Service:</strong> Online (${res.latency_ms} ms)</p>
                                <p class="mb-0 text-success"><i class="fa-solid fa-database me-2"></i><strong>Database Access:</strong> Healthy & Connected</p>
                            </div>`,
                            icon: 'success',
                            confirmButtonText: 'Great'
                        });
                    } else {
                        Swal.fire({
                            title: 'DB Connection Failed',
                            html: `<div class="text-start p-2">
                                <p class="mb-2 text-success"><i class="fa-solid fa-circle-check me-2"></i><strong>ML Service:</strong> Online (${res.latency_ms} ms)</p>
                                <p class="mb-1 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i><strong>Database Access:</strong> FAILED (db_configured: false)</p>
                                <small class="text-muted d-block mt-2">The ML microservice is up, but cannot access the MySQL database container. Check database environment variables (MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD) in your ML container configuration.</small>
                            </div>`,
                            icon: 'warning',
                            confirmButtonText: 'Understood'
                        });
                    }
                } else {
                    Swal.fire({
                        title: 'ML Backend Unreachable',
                        text: res.message || 'Could not connect to the specified URL.',
                        icon: 'error',
                        confirmButtonText: 'Close'
                    });
                }
            })
            .catch(err => {
                Swal.fire({ title: 'Error', text: err.message, icon: 'error', confirmButtonText: 'Close' });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-plug me-1"></i> Test Endpoint';
            });
    });

    document.getElementById('configForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const data = new FormData(form);
        const btn = form.querySelector('button[type=submit]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        fetch('<?= base_url('admin/ml/config/save') ?>', { method: 'POST', body: data })
            .then(r => safeFetchJson(r)).then(res => {
                showAlert('ML Config', res.message, res.status === 'success' ? 'success' : 'danger');
            })
            .catch(err => showAlert('Error', err.message, 'danger'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Config';
            });
    });

    document.getElementById('urlConfigForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const data = new FormData(form);
        const btn = document.getElementById('btnSaveUrlOnly');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        fetch('<?= base_url('admin/ml/config/save') ?>', { method: 'POST', body: data })
            .then(r => safeFetchJson(r)).then(res => {
                Swal.fire({
                    title: res.status === 'success' ? 'Endpoint Saved' : 'Save Failed',
                    text: res.message,
                    icon: res.status === 'success' ? 'success' : 'error',
                    confirmButtonText: 'OK'
                }).then(() => {
                    if (res.status === 'success') location.reload();
                });
            })
            .catch(err => Swal.fire({ title: 'Error', text: err.message, icon: 'error' }))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save URL';
            });
    });

    toggleEngineSections();
});
</script>
<?= $this->endSection() ?>