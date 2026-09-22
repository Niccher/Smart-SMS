<?php if (!isset($status)) return; ?>
<?php
$online  = $status['reachable'] ?? false;
$app     = $status['app'] ?? [];
$engine  = $app['llm_engine'] ?? 'local';
$isExt   = $engine === 'external';

// Pick the right model/provider/url to show depending on active engine
if ($isExt) {
    $activeModel    = $app['llm_external_model']    ?? '—';
    $activeProvider = $app['llm_external_provider'] ?? '—';
    $activeUrl      = $app['llm_external_base_url'] ?? '—';
    $engineLabel    = 'External';
    $engineIcon     = 'fa-cloud';
    $engineColor    = '#6366f1';
    $engineBg       = '#eef2ff';
} else {
    $activeModel    = $app['llm_model']    ?? '—';
    $activeProvider = $app['llm_provider'] ?? '—';
    $activeUrl      = $app['llm_base_url'] ?? '—';
    $engineLabel    = 'Local';
    $engineIcon     = 'fa-server';
    $engineColor    = '#0ea5e9';
    $engineBg       = '#e0f2fe';
}

$latency     = $status['latency_ms'] ?? null;
$llamaOk     = ($status['llama']          ?? '') === 'ok';
$dbOk        = (bool)($status['db_configured'] ?? false);
$autoJobs    = (bool)($status['auto_jobs_enabled'] ?? false);
$uptimeSec   = $status['uptime'] ?? null;

// Format uptime
$uptimeStr = '—';
if ($uptimeSec !== null) {
    $h = floor($uptimeSec / 3600);
    $m = floor(($uptimeSec % 3600) / 60);
    $uptimeStr = $h > 0 ? "{$h}h {$m}m" : "{$m}m";
}
?>

<div class="card border-0 mb-4 overflow-hidden" style="border-radius:14px; box-shadow:0 2px 16px rgba(0,0,0,0.06);">
    <div class="card-body p-0">
        <?php if ($online): ?>
        <div class="d-flex align-items-stretch flex-wrap">

            <!-- Engine badge strip -->
            <div class="d-flex flex-column align-items-center justify-content-center px-4 py-3 text-white"
                 style="background: linear-gradient(135deg, <?= $engineColor ?>, <?= $isExt ? '#818cf8' : '#38bdf8' ?>); min-width:100px;">
                <i class="fa-solid <?= $engineIcon ?> fa-2x mb-1"></i>
                <div class="fw-bold small"><?= $engineLabel ?></div>
                <div style="font-size:0.65rem; opacity:.85;">Engine</div>
            </div>

            <!-- Main info -->
            <div class="flex-grow-1 px-4 py-3 d-flex flex-column justify-content-center">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-success">ONLINE</span>
                    <span class="fw-bold fs-6">ML Backend</span>
                    <?php if ($autoJobs): ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.68rem;">
                            <i class="fa-solid fa-bolt me-1"></i>Auto Jobs
                        </span>
                    <?php endif; ?>
                </div>
                <div class="text-muted small d-flex flex-wrap gap-3 mt-1">
                    <span title="Active model"><i class="fa-solid fa-microchip me-1 text-primary"></i><?= esc($activeModel) ?></span>
                    <span title="Provider"><i class="fa-solid fa-plug me-1 text-primary"></i><?= esc($activeProvider) ?></span>
                    <?php if ($latency !== null): ?>
                        <span title="Latency"><i class="fa-solid fa-gauge-high me-1 text-<?= $latency < 200 ? 'success' : ($latency < 600 ? 'warning' : 'danger') ?>"></i><?= $latency ?>ms</span>
                    <?php endif; ?>
                    <span title="Uptime"><i class="fa-solid fa-clock me-1 text-primary"></i><?= $uptimeStr ?></span>
                </div>
            </div>

            <!-- Status pills -->
            <div class="d-flex align-items-center gap-3 px-4 py-3 border-start">
                <div class="text-center">
                    <div class="fw-bold small text-<?= $llamaOk ? 'success' : 'danger' ?>">
                        <i class="fa-solid fa-<?= $llamaOk ? 'check-circle' : 'circle-xmark' ?> me-1"></i><?= $llamaOk ? 'OK' : 'ERR' ?>
                    </div>
                    <div style="font-size:.65rem;" class="text-muted">Llama</div>
                </div>
                <div class="text-center">
                    <div class="fw-bold small text-<?= $dbOk ? 'success' : 'danger' ?>">
                        <i class="fa-solid fa-<?= $dbOk ? 'check-circle' : 'circle-xmark' ?> me-1"></i><?= $dbOk ? 'OK' : 'ERR' ?>
                    </div>
                    <div style="font-size:.65rem;" class="text-muted">DB Container</div>
                </div>
            </div>

            <!-- Refresh button -->
            <div class="d-flex align-items-center px-3 py-3 border-start">
                <a href="<?= current_url() ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                   title="Refresh status">
                    <i class="fa-solid fa-rotate me-1"></i> Refresh
                </a>
            </div>
        </div>

        <?php if (!$dbOk): ?>
            <div class="alert alert-warning mb-0 border-0 rounded-0 border-top small px-4 py-2 text-dark">
                <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>
                <strong>DB Container Access Warning:</strong> The ML microservice is online, but it cannot connect to the MySQL database container (<code>db_configured: false</code>). Check database credentials in the ML container environment variables.
            </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="d-flex align-items-center gap-3 p-4">
            <div class="rounded d-flex align-items-center justify-content-center"
                 style="width:48px;height:48px;background:#fdecec;color:#c0392b;font-size:1.3rem;flex-shrink:0;">
                <i class="fa-solid fa-microchip"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold text-danger">ML Backend Offline</div>
                <small class="text-muted"><?= esc($status['error'] ?? 'Unreachable') ?></small>
            </div>
            <a href="<?= current_url() ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                <i class="fa-solid fa-rotate me-1"></i> Retry
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
