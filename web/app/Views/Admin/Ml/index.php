<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Backend - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .ml-card     { border: none; border-radius: 14px; box-shadow: 0 2px 16px rgba(0,0,0,0.05); }
    .stat-pill   { border-radius: 10px; padding: 1rem 1.25rem; }
    .kv-label    { font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; font-weight: 600; }
    .kv-value    { font-size: .93rem; font-weight: 600; color: #1e293b; word-break: break-all; }
    .section-title { font-size:.72rem; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; font-weight:700; margin-bottom:.75rem; }
    .engine-tab  { cursor:pointer; border-radius:8px; padding:.5rem 1rem; font-size:.85rem; font-weight:600; transition:all .15s; }
    .engine-tab.active { background:var(--bs-primary); color:#fff; }
    .engine-tab:not(.active):hover { background:#f1f5f9; }
    .config-row  { display:flex; align-items:flex-start; gap:.75rem; padding:.55rem 0; border-bottom:1px solid #f1f5f9; }
    .config-row:last-child { border-bottom: none; }
    .config-icon { width:28px; height:28px; border-radius:7px; display:flex; align-items:center; justify-content:center;
                   font-size:.72rem; background:#f4f6fb; color:var(--bs-primary); flex-shrink:0; margin-top:2px; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-microchip me-2"></i> ML Backend</h2>
        <p class="text-secondary mb-0">Real-time health, engine status and active configuration.</p>
    </div>
    <a href="<?= base_url('admin/ml/config') ?>" class="btn btn-primary rounded-pill px-4">
        <i class="fa-solid fa-sliders me-1"></i> Configure
    </a>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$app     = $status['app'] ?? [];
$engine  = $app['llm_engine'] ?? 'local';
$isExt   = $engine === 'external';
$online  = $status['reachable'] ?? false;
$uptime  = $status['uptime']    ?? null;

$uptimeStr = '—';
if ($uptime !== null) {
    $h = floor($uptime / 3600); $m = floor(($uptime % 3600) / 60);
    $uptimeStr = $h > 0 ? "{$h}h {$m}m" : "{$m}m";
}

// External provider pretty names
$providerNames = [
    'gemini'           => 'Google Gemini',
    'deepseek'         => 'DeepSeek',
    'openai'           => 'OpenAI',
    'groq'             => 'Groq',
    'mistral'          => 'Mistral AI',
    'openrouter'       => 'OpenRouter',
    'cohere'           => 'Cohere',
    'kimi'             => 'Kimi (Moonshot)',
    'nemotron'         => 'Nemotron (NVIDIA)',
    'xai'              => 'x.ai (Grok)',
    'openai-compatible'=> 'Custom API',
];
?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="card ml-card">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'status', 'status' => $status]) ?>

        <?php if ($online): ?>

        <!-- ── Engine & quick stats ───────────────────────────────── -->
        <div class="row g-3 mb-4">

            <!-- Active Engine -->
            <div class="col-md-3 col-sm-6">
                <div class="stat-pill h-100 d-flex flex-column gap-1"
                     style="background:<?= $isExt ? '#eef2ff' : '#e0f2fe' ?>; border-left:4px solid <?= $isExt ? '#6366f1' : '#0ea5e9' ?>;">
                    <div class="kv-label">Active Engine</div>
                    <div class="kv-value d-flex align-items-center gap-2">
                        <i class="fa-solid <?= $isExt ? 'fa-cloud' : 'fa-server' ?>" style="color:<?= $isExt ? '#6366f1' : '#0ea5e9' ?>"></i>
                        <?= $isExt ? 'External' : 'Local' ?>
                    </div>
                    <?php if ($isExt): ?>
                    <small class="text-muted"><?= esc($providerNames[$app['llm_external_provider'] ?? ''] ?? ($app['llm_external_provider'] ?? '—')) ?></small>
                    <?php else: ?>
                    <small class="text-muted"><?= esc($app['llm_provider'] ?? '—') ?></small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Active Model -->
            <div class="col-md-3 col-sm-6">
                <div class="stat-pill h-100 d-flex flex-column gap-1" style="background:#f0fdf4; border-left:4px solid #22c55e;">
                    <div class="kv-label">Active Model</div>
                    <div class="kv-value">
                        <?= $isExt ? esc($app['llm_external_model'] ?? '—') : esc($app['llm_model'] ?? '—') ?>
                    </div>
                    <small class="text-muted"><?= $isExt
                        ? 'Max ' . ($app['llm_external_max_tokens'] ?? '—') . ' tokens'
                        : 'Max ' . ($app['llm_max_tokens'] ?? '—') . ' tokens' ?>
                    </small>
                </div>
            </div>

            <!-- Latency -->
            <?php $lat = $status['latency_ms'] ?? null; ?>
            <div class="col-md-3 col-sm-6">
                <div class="stat-pill h-100 d-flex flex-column gap-1"
                     style="background:<?= $lat < 200 ? '#f0fdf4' : ($lat < 600 ? '#fefce8' : '#fef2f2') ?>;
                            border-left:4px solid <?= $lat < 200 ? '#22c55e' : ($lat < 600 ? '#eab308' : '#ef4444') ?>;">
                    <div class="kv-label">API Latency</div>
                    <div class="kv-value"><?= $lat !== null ? $lat . 'ms' : '—' ?></div>
                    <small class="text-muted"><?= $lat < 200 ? 'Fast' : ($lat < 600 ? 'Moderate' : 'Slow') ?></small>
                </div>
            </div>

            <!-- Uptime -->
            <div class="col-md-3 col-sm-6">
                <div class="stat-pill h-100 d-flex flex-column gap-1" style="background:#faf5ff; border-left:4px solid #a855f7;">
                    <div class="kv-label">Uptime</div>
                    <div class="kv-value"><?= $uptimeStr ?></div>
                    <small class="text-muted">Auto-jobs: <strong class="text-<?= ($status['auto_jobs_enabled'] ?? false) ? 'success' : 'secondary' ?>">
                        <?= ($status['auto_jobs_enabled'] ?? false) ? 'ON' : 'OFF' ?>
                    </strong></small>
                </div>
            </div>
        </div>

        <!-- ── Config panels ─────────────────────────────────────── -->
        <div class="row g-4">

            <!-- Active engine config -->
            <div class="col-lg-6">
                <div class="section-title">
                    <i class="fa-solid fa-<?= $isExt ? 'cloud' : 'server' ?> me-1"></i>
                    <?= $isExt ? 'External Engine Config' : 'Local Engine Config' ?>
                </div>
                <div class="card ml-card">
                    <div class="card-body p-3">
                        <?php if ($isExt):
                            $extRows = [
                                ['fa-plug',           'Provider',   $providerNames[$app['llm_external_provider'] ?? ''] ?? ($app['llm_external_provider'] ?? '—')],
                                ['fa-microchip',      'Model',      $app['llm_external_model'] ?? '—'],
                                ['fa-link',           'Base URL',   $app['llm_external_base_url'] ?? '—'],
                                ['fa-note-sticky',    'Max Tokens', $app['llm_external_max_tokens'] ?? '—'],
                                ['fa-thermometer-half','Temp',      $app['llm_external_temperature'] ?? '—'],
                                ['fa-bolt',           'Batch Size', $app['external_batch_size'] ?? '—'],
                                ['fa-rotate',         'Max Retries',$app['external_max_retries'] ?? '—'],
                                ['fa-clock',          'Poll Interval', ($app['external_poll_interval'] ?? '—') . 's'],
                            ];
                        else:
                            $extRows = [
                                ['fa-plug',           'Provider',   $app['llm_provider'] ?? '—'],
                                ['fa-microchip',      'Model',      $app['llm_model'] ?? '—'],
                                ['fa-link',           'Base URL',   $app['llm_base_url'] ?? '—'],
                                ['fa-note-sticky',    'Max Tokens', $app['llm_max_tokens'] ?? '—'],
                                ['fa-thermometer-half','Temp',      $app['llm_temperature'] ?? '—'],
                                ['fa-arrows-left-right','Context',  $app['llm_ctx_size'] ?? '—'],
                                ['fa-memory',         'GPU Layers', $app['n_gpu_layers'] ?? '—'],
                                ['fa-bolt',           'Batch Size', $app['batch_size'] ?? '—'],
                                ['fa-rotate',         'Max Retries',$app['max_retries'] ?? '—'],
                                ['fa-clock',          'Poll Interval', ($app['poll_interval'] ?? '—') . 's'],
                                ['fa-folder-open',    'Model Path', $app['model_path'] ?? '—'],
                            ];
                        endif;
                        foreach ($extRows as [$icon, $label, $val]): ?>
                        <div class="config-row">
                            <div class="config-icon"><i class="fa-solid <?= $icon ?>"></i></div>
                            <div class="flex-grow-1">
                                <div class="kv-label mb-0" style="letter-spacing:0;"><?= $label ?></div>
                                <div class="kv-value" style="font-size:.85rem;"><?= esc((string)$val) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Right panel: Models list (local) or Fallback (external) + System -->
            <div class="col-lg-6">

                <?php if ($isExt && ($app['llm_fallback_enabled'] ?? false)): ?>
                <div class="section-title"><i class="fa-solid fa-shield-halved me-1"></i> Fallback Config</div>
                <div class="card ml-card mb-4">
                    <div class="card-body p-3">
                        <?php foreach ([
                            ['fa-plug',      'Provider', $app['llm_fallback_provider'] ?? '—'],
                            ['fa-microchip', 'Model',    $app['llm_fallback_model'] ?? '—'],
                            ['fa-link',      'Base URL', $app['llm_fallback_base_url'] ?? '—'],
                        ] as [$icon, $label, $val]): ?>
                        <div class="config-row">
                            <div class="config-icon"><i class="fa-solid <?= $icon ?>"></i></div>
                            <div>
                                <div class="kv-label mb-0" style="letter-spacing:0;"><?= $label ?></div>
                                <div class="kv-value" style="font-size:.85rem;"><?= esc($val) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Local models (always show if engine=local; show as reference if external) -->
                <?php if (!$isExt || !empty($status['models'])): ?>
                <div class="section-title"><i class="fa-solid fa-database me-1"></i> Local Models</div>
                <div class="card ml-card mb-4">
                    <div class="card-body p-3">
                        <?php if (empty($status['models'])): ?>
                            <p class="text-muted small mb-0">No local models loaded.</p>
                        <?php else: ?>
                            <?php foreach ($status['models'] as $m): ?>
                            <div class="config-row">
                                <div class="config-icon" style="background:<?= $m['active'] ? '#f0fdf4' : '#f4f6fb' ?>;color:<?= $m['active'] ? '#16a34a' : 'var(--bs-primary)' ?>;">
                                    <i class="fa-solid fa-file-code"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="kv-value" style="font-size:.85rem;"><?= esc($m['filename']) ?></div>
                                    <small class="text-muted"><?= $m['size_mb'] ?> MB</small>
                                </div>
                                <?php if ($m['active']): ?>
                                    <span class="badge bg-success align-self-center">ACTIVE</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary align-self-center">Idle</span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- System Health -->
                <div class="section-title"><i class="fa-solid fa-heart-pulse me-1"></i> System Health</div>
                <div class="card ml-card">
                    <div class="card-body p-3 d-flex gap-4 flex-wrap">
                        <?php
                        $checks = [
                            ['Llama',    ($status['llama'] ?? '') === 'ok',       'fa-brain'],
                            ['Database', (bool)($status['db_configured'] ?? false), 'fa-database'],
                            ['Auto Jobs',(bool)($status['auto_jobs_enabled'] ?? false),'fa-bolt'],
                        ];
                        foreach ($checks as [$name, $ok, $icon]):
                        ?>
                        <div class="text-center flex-fill">
                            <div class="mb-1" style="font-size:1.5rem; color:<?= $ok ? '#22c55e' : '#ef4444' ?>;">
                                <i class="fa-solid <?= $icon ?>"></i>
                            </div>
                            <div class="kv-label mb-0"><?= $name ?></div>
                            <div class="fw-bold small text-<?= $ok ? 'success' : 'danger' ?>"><?= $ok ? 'OK' : 'FAIL' ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div><!-- /col -->
        </div><!-- /row -->

        <?php else: ?>
        <div class="alert alert-warning d-flex align-items-center gap-3 mb-0">
            <i class="fa-solid fa-triangle-exclamation fa-xl"></i>
            <div>
                <strong>ML Backend not reachable.</strong><br>
                <small>Check that the <code>ml-mpesa-analyzer</code> container is running and healthy.</small>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?= $this->endSection() ?>
