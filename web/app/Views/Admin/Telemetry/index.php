<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Container Telemetry - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .telemetry-card {
        border: none;
        border-radius: 6px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        background: var(--card-bg, #ffffff);
        border: 1px solid var(--card-border, #e2e8f0);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .live-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        background-color: #28a745;
        border-radius: 50%;
        box-shadow: 0 0 0 rgba(40, 167, 69, 0.4);
        animation: pulse 1.8s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.6); }
        70% { box-shadow: 0 0 0 8px rgba(40, 167, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
    }
    .metric-badge {
        font-size: 0.72rem;
        padding: 0.25rem 0.6rem;
        border-radius: 50rem;
        font-weight: 600;
    }
    .progress-bar {
        transition: width 0.4s ease;
    }
    .stat-pill {
        background: rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 4px;
        padding: 0.5rem 0.75rem;
    }
    [data-bs-theme="dark"] .stat-pill {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
    }
    .telemetry-sparkline {
        height: 28px;
        width: 100%;
        overflow: visible;
        display: block;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">
            <i class="fa-solid fa-chart-line me-2"></i> Container Telemetry
        </h2>
        <p class="text-secondary mb-0">Live resource health, memory headroom, and container diagnostics across WebApp, MySQL, and ML Inference.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 bg-body-tertiary px-3 py-1 rounded border small">
            <span class="live-dot" id="livePulse"></span>
            <span class="fw-semibold" id="liveStatusText">Live Monitoring</span>
            <span class="text-muted" style="font-size: 0.75rem;" id="lastUpdatedText">Just now</span>
        </div>
        <select class="form-select form-select-sm w-auto" id="pollIntervalSelect" title="Auto-refresh rate">
            <option value="5000" selected>Refresh: 5s (Optimal)</option>
            <option value="10000">Refresh: 10s</option>
            <option value="30000">Refresh: 30s</option>
            <option value="0">Paused</option>
        </select>
        <button class="btn btn-sm btn-primary rounded-pill px-3" id="manualRefreshBtn">
            <i class="fa-solid fa-rotate me-1" id="refreshIcon"></i> Refresh Now
        </button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$web = $metrics['web'] ?? [];
$mysql = $metrics['mysql'] ?? [];
$ml = $metrics['ml'] ?? [];
?>

<!-- Quick Overview KPIs with Live Sparklines -->
<div class="row g-3 mb-4">
    <!-- WebApp Container -->
    <div class="col-md-4">
        <div class="card telemetry-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase"><i class="fa-brands fa-php me-1 text-primary"></i> WebApp Container</span>
                <div class="d-flex gap-1 align-items-center">
                    <span class="badge bg-danger metric-badge <?= !empty($web['memory']['container_oom_warning']) ? '' : 'd-none' ?>" id="kpiWebOomBadge" title="Approaching Railway memory limit">
                        <i class="fa-solid fa-triangle-exclamation"></i> 80%+ OOM Risk
                    </span>
                    <span class="badge bg-success metric-badge" id="kpiWebStatus"><?= esc($web['status'] ?? 'online') ?></span>
                </div>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-2">
                <h3 class="fw-bold mb-0" id="kpiWebCpu"><?= esc($web['cpu']['load_pct'] ?? 0) ?>%</h3>
                <span class="text-muted small">CPU Load (<span id="kpiWebCpuIdle" class="text-success fw-semibold"><?= esc($web['cpu']['idle_pct'] ?? 100) ?>%</span> Free)</span>
            </div>
            <div class="small text-muted d-flex justify-content-between mb-2">
                <span>RAM: <strong id="kpiWebRam"><?= number_format($web['memory']['container_used_mb'] ?? 0) ?> MB</strong> / <span id="kpiWebRamLimit"><?= $web['memory']['container_limit_mb'] ? number_format($web['memory']['container_limit_mb']) . ' MB' : number_format($web['memory']['host_total_mb'] ?? 0) . ' MB' ?></span></span>
                <span>Uptime: <strong id="kpiWebUptime"><?= esc($web['uptime_formatted'] ?? '0s') ?></strong></span>
            </div>
            <!-- Live CPU Sparkline -->
            <div class="pt-2 border-top">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted" style="font-size: 0.7rem;">CPU Load Trend</span>
                    <span class="text-muted font-monospace" style="font-size: 0.7rem;" id="sparkWebCpuVal"><?= esc($web['cpu']['load_pct'] ?? 0) ?>%</span>
                </div>
                <svg id="sparkWebCpu" class="telemetry-sparkline"></svg>
            </div>
        </div>
    </div>

    <!-- MySQL Container -->
    <div class="col-md-4">
        <div class="card telemetry-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase"><i class="fa-solid fa-database me-1 text-info"></i> MySQL Container</span>
                <span class="badge bg-success metric-badge" id="kpiMysqlStatus"><?= esc($mysql['status'] ?? 'online') ?></span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-2">
                <h3 class="fw-bold mb-0" id="kpiMysqlConn"><?= esc($mysql['connections']['connected'] ?? 0) ?></h3>
                <span class="text-muted small">Conns (<span id="kpiMysqlConnPct"><?= esc($mysql['connections']['used_pct'] ?? 0) ?>%</span> &bull; <span id="kpiMysqlConnFree" class="text-success fw-semibold"><?= esc($mysql['connections']['free'] ?? 0) ?></span> free)</span>
            </div>
            <div class="small text-muted d-flex justify-content-between mb-2">
                <span>QPS: <strong id="kpiMysqlQps"><?= esc($mysql['throughput']['qps'] ?? 0) ?></strong></span>
                <span>Slow: <strong id="kpiMysqlSlowQ" class="<?= !empty($mysql['throughput']['slow_queries']) ? 'text-danger fw-bold' : 'text-success' ?>"><?= esc($mysql['throughput']['slow_queries'] ?? 0) ?></strong></span>
            </div>
            <!-- Live QPS Sparkline -->
            <div class="pt-2 border-top">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted" style="font-size: 0.7rem;">QPS Throughput Trend</span>
                    <span class="text-muted font-monospace" style="font-size: 0.7rem;" id="sparkMysqlQpsVal"><?= esc($mysql['throughput']['qps'] ?? 0) ?> QPS</span>
                </div>
                <svg id="sparkMysqlQps" class="telemetry-sparkline"></svg>
            </div>
        </div>
    </div>

    <!-- ML Engine Container -->
    <div class="col-md-4">
        <div class="card telemetry-card p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase"><i class="fa-solid fa-microchip me-1 text-warning"></i> ML Engine Container</span>
                <span class="badge bg-<?= ($ml['status'] ?? '') === 'online' ? 'success' : 'danger' ?> metric-badge" id="kpiMlStatus"><?= esc($ml['status'] ?? 'offline') ?></span>
            </div>
            <div class="d-flex align-items-baseline gap-2 mb-2">
                <h3 class="fw-bold mb-0" id="kpiMlMode">
                    <?= !empty($ml['gpu']['has_gpu']) ? 'GPU' : 'CPU' ?>
                </h3>
                <span class="text-muted small" id="kpiMlLatency">(<?= esc($ml['latency_ms'] ?? 0) ?> ms API latency)</span>
            </div>
            <div class="small text-muted d-flex justify-content-between mb-2">
                <span>Active: <strong id="kpiMlModel" class="text-truncate" style="max-width: 140px;"><?= esc($ml['inference']['active_model'] ?? 'None') ?></strong></span>
                <span>Queue: <strong id="kpiMlQueue"><?= esc($ml['queue']['active_jobs'] ?? 0) ?> active</strong></span>
            </div>
            <!-- Live Latency Sparkline -->
            <div class="pt-2 border-top">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted" style="font-size: 0.7rem;">Inference Latency Trend</span>
                    <span class="text-muted font-monospace" style="font-size: 0.7rem;" id="sparkMlLatencyVal"><?= esc($ml['latency_ms'] ?? 0) ?> ms</span>
                </div>
                <svg id="sparkMlLatency" class="telemetry-sparkline"></svg>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Metrics Sections (Equal 2-Column Grid) -->
<div class="row g-4 mb-4">
    <!-- 1. WebApp Container Deep Diagnostics -->
    <div class="col-lg-6">
        <div class="card telemetry-card h-100">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-primary">
                    <i class="fa-brands fa-php me-2"></i> WebApp &amp; Host Diagnostics
                </h5>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">PHP <?= esc($web['runtime']['php_version'] ?? PHP_VERSION) ?></span>
            </div>
            <div class="card-body">
                <!-- CPU Utilization -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">CPU Utilization (<span id="webCpuCores"><?= esc($web['cpu']['cores'] ?? 1) ?></span> Core<?= ($web['cpu']['cores'] ?? 1) > 1 ? 's' : '' ?>)</span>
                        <span class="small fw-bold">
                            <span id="webCpuPctText"><?= esc($web['cpu']['load_pct'] ?? 0) ?>%</span> Used &bull; <span id="webCpuIdleText" class="text-success"><?= esc($web['cpu']['idle_pct'] ?? 100) ?>%</span> Free
                        </span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-primary" id="webCpuBar" style="width: <?= esc($web['cpu']['load_pct'] ?? 0) ?>%;"></div>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="stat-pill small text-muted flex-fill text-center">1m: <strong id="webLoad1m"><?= esc($web['cpu']['load_1m'] ?? 0) ?></strong></span>
                        <span class="stat-pill small text-muted flex-fill text-center">5m: <strong id="webLoad5m"><?= esc($web['cpu']['load_5m'] ?? 0) ?></strong></span>
                        <span class="stat-pill small text-muted flex-fill text-center">15m: <strong id="webLoad15m"><?= esc($web['cpu']['load_15m'] ?? 0) ?></strong></span>
                    </div>
                </div>

                <!-- Memory (RAM) Allocation & Headroom -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Container RAM Allocation</span>
                        <span class="small fw-bold" id="webRamText">
                            <?= number_format($web['memory']['container_used_mb'] ?? 0) ?> MB / <?= $web['memory']['container_limit_mb'] ? number_format($web['memory']['container_limit_mb']) . ' MB' : number_format($web['memory']['host_total_mb'] ?? 0) . ' MB (Host)' ?> (<?= esc($web['memory']['container_used_pct'] ?? 0) ?>%)
                        </span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <?php
                        $memPct = $web['memory']['container_used_pct'] ?? 0;
                        $memBarClass = $memPct > 85 ? 'bg-danger' : ($memPct > 70 ? 'bg-warning' : 'bg-info');
                        ?>
                        <div class="progress-bar <?= $memBarClass ?>" id="webRamBar" style="width: <?= $memPct ?>%;"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small mb-2">
                        <span>Free Headroom: <strong class="text-success" id="webRamFree"><?= number_format($web['memory']['container_free_mb'] ?? 0) ?> MB</strong></span>
                        <span>Host Total: <strong id="webHostTotal"><?= number_format($web['memory']['host_total_mb'] ?? 0) ?> MB</strong></span>
                    </div>
                    <div class="row g-2 text-center small text-muted">
                        <div class="col-4 stat-pill">
                            <div>PHP Current Heap</div>
                            <strong class="text-dark" id="webPhpAlloc"><?= number_format($web['memory']['php_allocated_mb'] ?? 0) ?> MB</strong>
                        </div>
                        <div class="col-4 stat-pill">
                            <div>PHP Peak Heap</div>
                            <strong class="text-dark" id="webPhpPeak"><?= number_format($web['memory']['php_peak_mb'] ?? 0) ?> MB</strong>
                        </div>
                        <div class="col-4 stat-pill">
                            <div>Memory Limit</div>
                            <strong class="text-dark" id="webPhpLimit"><?= esc($web['memory']['php_limit'] ?? 'N/A') ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Local Disk Storage -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Local Disk Storage (Root Volume)</span>
                        <span class="small fw-bold" id="webDiskText">
                            <?= number_format(($web['disk']['used_mb'] ?? 0) / 1024, 2) ?> GB / <?= number_format(($web['disk']['total_mb'] ?? 0) / 1024, 2) ?> GB (<?= esc($web['disk']['used_pct'] ?? 0) ?>%)
                        </span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-warning" id="webDiskBar" style="width: <?= esc($web['disk']['used_pct'] ?? 0) ?>%;"></div>
                    </div>
                    <div class="small text-muted d-flex justify-content-between">
                        <span>Free Storage: <strong class="text-success" id="webDiskFree"><?= number_format(($web['disk']['free_mb'] ?? 0) / 1024, 2) ?> GB</strong> (<span id="webDiskFreePct"><?= esc($web['disk']['free_pct'] ?? 0) ?>%</span> free)</span>
                        <span>Usage: <strong id="webDiskPct"><?= esc($web['disk']['used_pct'] ?? 0) ?>%</strong></span>
                    </div>
                </div>

                <hr class="my-3 opacity-25">
                <div class="row g-2 text-muted small">
                    <div class="col-6">Server: <strong class="text-dark" id="webServer"><?= esc($web['runtime']['server'] ?? 'Nginx Container') ?></strong></div>
                    <div class="col-6 text-end">Active Sessions: <strong class="text-dark" id="webSessions"><?= esc($web['runtime']['sessions'] ?? 0) ?></strong></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. MySQL Container Diagnostics -->
    <div class="col-lg-6">
        <div class="card telemetry-card h-100">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-info">
                    <i class="fa-solid fa-database me-2"></i> MySQL Container Diagnostics
                </h5>
                <span class="badge bg-info-subtle text-info rounded-pill px-3 py-1" id="mysqlVersion">
                    <?= esc($mysql['version'] ?? 'MySQL') ?>
                </span>
            </div>
            <div class="card-body">
                <!-- Client Connection Pool -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Client Connection Pool</span>
                        <span class="small fw-bold" id="mysqlConnText">
                            <?= esc($mysql['connections']['connected'] ?? 0) ?> / <?= esc($mysql['connections']['max'] ?? 151) ?> max (<?= esc($mysql['connections']['used_pct'] ?? 0) ?>%)
                        </span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-info" id="mysqlConnBar" style="width: <?= esc($mysql['connections']['used_pct'] ?? 0) ?>%;"></div>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="stat-pill small text-muted flex-fill text-center">Active Running: <strong id="mysqlThreadsRunning"><?= esc($mysql['connections']['running'] ?? 0) ?></strong></span>
                        <span class="stat-pill small text-muted flex-fill text-center">Free Slots: <strong id="mysqlFreeConn" class="text-success"><?= esc($mysql['connections']['free'] ?? 0) ?></strong></span>
                        <span class="stat-pill small text-muted flex-fill text-center">Latency: <strong id="mysqlLatency"><?= esc($mysql['latency_ms'] ?? 0) ?> ms</strong></span>
                    </div>
                </div>

                <!-- Performance & Query Health -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Throughput &amp; Query Health</span>
                        <span class="small text-muted">Latency: <strong class="text-dark"><?= esc($mysql['latency_ms'] ?? 0) ?> ms</strong></span>
                    </div>
                    <div class="row g-2 text-center small text-muted">
                        <div class="col-4 stat-pill">
                            <div>Queries / Sec</div>
                            <strong class="text-primary fs-6" id="mysqlQps"><?= esc($mysql['throughput']['qps'] ?? 0) ?></strong>
                        </div>
                        <div class="col-4 stat-pill">
                            <div>Slow Queries</div>
                            <strong class="<?= !empty($mysql['throughput']['slow_queries']) ? 'text-danger fw-bold' : 'text-success' ?>" id="mysqlSlowQ"><?= number_format($mysql['throughput']['slow_queries'] ?? 0) ?></strong>
                        </div>
                        <div class="col-4 stat-pill">
                            <div>Aborted Connects</div>
                            <strong class="<?= !empty($mysql['connections']['aborted']) ? 'text-warning' : 'text-dark' ?>" id="mysqlAbortedConnects"><?= number_format($mysql['connections']['aborted'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>

                <!-- InnoDB Buffer Pool Memory -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">InnoDB Buffer Pool Memory (RAM Cache)</span>
                        <span class="small fw-bold" id="mysqlBpText">
                            <?= number_format($mysql['buffer_pool']['data_mb'] ?? 0, 1) ?> MB / <?= number_format($mysql['buffer_pool']['size_mb'] ?? 0, 1) ?> MB (<?= esc($mysql['buffer_pool']['used_pct'] ?? 0) ?>%)
                        </span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-success" id="mysqlBpBar" style="width: <?= esc($mysql['buffer_pool']['used_pct'] ?? 0) ?>%;"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Free Buffer RAM: <strong class="text-success" id="mysqlBpFree"><?= number_format($mysql['buffer_pool']['free_mb'] ?? 0, 1) ?> MB</strong></span>
                        <span>Peak Connections: <strong id="mysqlMaxUsed"><?= esc($mysql['connections']['max_used'] ?? 0) ?></strong></span>
                    </div>
                </div>

                <!-- Database Storage Footprint -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Database Schema Storage</span>
                        <span class="small fw-bold" id="mysqlDbSizeText"><?= number_format($mysql['database_size_mb'] ?? 0, 2) ?> MB</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Tables Count: <strong id="mysqlTables"><?= esc($mysql['tables_count'] ?? 0) ?> tables</strong></span>
                        <span>Approx Total Rows: <strong id="mysqlRows"><?= number_format($mysql['approx_rows'] ?? 0) ?></strong></span>
                    </div>
                </div>

                <hr class="my-3 opacity-25">
                <div class="row g-2 text-muted small">
                    <div class="col-6">Schema: <strong class="text-dark"><?= esc($mysql['database'] ?? 'mpesa_analyzer') ?></strong></div>
                    <div class="col-6 text-end">Uptime: <strong class="text-dark" id="mysqlUptime"><?= esc($mysql['uptime_formatted'] ?? '0s') ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. ML Classifier Engine Container (Full-Width Row) -->
<div class="row g-4">
    <div class="col-12">
        <div class="card telemetry-card">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="fw-bold mb-0 text-warning">
                    <i class="fa-solid fa-microchip me-2"></i> ML Classifier Engine &amp; Hardware Diagnostics
                </h5>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1" id="mlLlamaStatus">
                        Llama Server: <?= !empty($ml['inference']['llama_running']) ? 'Running (PID ' . esc($ml['inference']['llama_pid']) . ')' : 'Standby / Stopped' ?>
                    </span>
                    <span class="badge bg-secondary rounded-pill px-3 py-1" id="mlLatencyBadge">
                        API: <?= esc($ml['latency_ms'] ?? 0) ?> ms
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- Left: Hardware & RAM -->
                    <div class="col-lg-6">
                        <h6 class="fw-bold mb-3 small text-muted text-uppercase">Hardware &amp; Execution Environment</h6>

                        <!-- GPU / Hardware Acceleration Badge -->
                        <div class="p-3 border rounded mb-3" id="gpuCardWrapper">
                            <?php if (!empty($ml['gpu']['has_gpu'])): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-success"><i class="fa-solid fa-bolt me-1"></i> <?= esc($ml['gpu']['gpus'][0]['name'] ?? 'NVIDIA GPU') ?></span>
                                    <span class="badge bg-success">GPU Accelerated</span>
                                </div>
                                <div class="small text-muted mb-2">
                                    Driver: <?= esc($ml['gpu']['gpus'][0]['driver_version'] ?? 'N/A') ?> | Temp: <?= esc($ml['gpu']['gpus'][0]['temperature_c'] ?? 0) ?>°C
                                </div>
                                <div class="d-flex justify-content-between small fw-semibold mb-1">
                                    <span>VRAM Memory</span>
                                    <span><?= number_format($ml['gpu']['gpus'][0]['memory_used_mb'] ?? 0) ?> MB / <?= number_format($ml['gpu']['gpus'][0]['memory_total_mb'] ?? 0) ?> MB</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <?php
                                    $vTot = $ml['gpu']['gpus'][0]['memory_total_mb'] ?? 1;
                                    $vUsed = $ml['gpu']['gpus'][0]['memory_used_mb'] ?? 0;
                                    $vPct = min(100, round(($vUsed / $vTot) * 100));
                                    ?>
                                    <div class="progress-bar bg-success" style="width: <?= $vPct ?>%;"></div>
                                </div>
                            <?php else: ?>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-primary"><i class="fa-solid fa-microchip me-1"></i> CPU Vectorized Inference (AVX2 / OpenMP)</div>
                                        <div class="small text-muted">Running in optimized CPU container mode. No discrete NVIDIA GPU detected.</div>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">CPU Mode</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- ML RAM Breakdown -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1 small fw-semibold">
                                <span>Container RAM Usage</span>
                                <span id="mlRamText"><?= number_format($ml['memory']['container_used_mb'] ?? 0) ?> MB (Python + Model Server)</span>
                            </div>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-warning" id="mlRamBar" style="width: <?= esc($ml['memory']['host_used_pct'] ?? 20) ?>%;"></div>
                            </div>
                            <div class="row g-2 text-center small text-muted">
                                <div class="col-6 stat-pill">
                                    <div>Python FastAPI Worker</div>
                                    <strong class="text-dark" id="mlPythonRss"><?= number_format($ml['memory']['python_rss_mb'] ?? 0) ?> MB</strong>
                                </div>
                                <div class="col-6 stat-pill">
                                    <div>Llama C++ Server</div>
                                    <strong class="text-dark" id="mlLlamaRss"><?= number_format($ml['memory']['llama_rss_mb'] ?? 0) ?> MB</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Model in Memory & Job Queues -->
                    <div class="col-lg-6">
                        <h6 class="fw-bold mb-3 small text-muted text-uppercase">Active Model &amp; Processing Queue</h6>

                        <div class="p-3 border rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark"><i class="fa-solid fa-cube me-1 text-primary"></i> Active GGUF Model</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0" id="mlModelEngine">
                                    <?= esc($ml['inference']['engine'] ?? 'local_gguf') ?>
                                </span>
                            </div>
                            <div class="fw-semibold text-primary mb-1 text-break" id="mlModelName">
                                <?= esc($ml['inference']['active_model'] ?? 'None') ?>
                            </div>
                            <div class="row g-2 text-muted small mt-2">
                                <div class="col-4">Size on Disk: <strong class="text-dark" id="mlModelSize"><?= number_format($ml['inference']['model_size_mb'] ?? 0) ?> MB</strong></div>
                                <div class="col-4">Context: <strong class="text-dark" id="mlCtxSize"><?= number_format($ml['inference']['ctx_size'] ?? 16384) ?> tok</strong></div>
                                <div class="col-4 text-end">Batch: <strong class="text-dark" id="mlBatchSize"><?= esc($ml['inference']['batch_size'] ?? 512) ?></strong></div>
                            </div>
                        </div>

                        <!-- Job Queues Stats -->
                        <div class="row g-2 text-center small">
                            <div class="col-4">
                                <div class="p-2 border rounded bg-body-tertiary">
                                    <div class="text-muted" style="font-size: 0.72rem;">Active Processing</div>
                                    <div class="fw-bold fs-5 text-warning" id="mlQueueActive"><?= esc($ml['queue']['active_jobs'] ?? 0) ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-body-tertiary">
                                    <div class="text-muted" style="font-size: 0.72rem;">Queued in DB</div>
                                    <div class="fw-bold fs-5 text-info" id="mlQueueQueued"><?= esc($ml['queue']['queued_jobs'] ?? 0) ?></div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-body-tertiary">
                                    <div class="text-muted" style="font-size: 0.72rem;">Completed Today</div>
                                    <div class="fw-bold fs-5 text-success" id="mlQueueCompleted"><?= esc($ml['queue']['completed_today'] ?? 0) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Route guard: strictly run only when on /admin/telemetry
    if (!window.location.pathname.includes('/admin/telemetry')) {
        return;
    }

    let pollTimer = null;
    let pollInterval = 5000; // Optimal 5s polling rate (low container overhead)

    // Rolling history buffers for real-time client-side sparklines (max 20 points)
    const MAX_HISTORY = 20;
    const historyWebCpu = [];
    const historyMysqlQps = [];
    const historyMlLatency = [];

    const intervalSelect = document.getElementById('pollIntervalSelect');
    const manualRefreshBtn = document.getElementById('manualRefreshBtn');
    const refreshIcon = document.getElementById('refreshIcon');
    const liveStatusText = document.getElementById('liveStatusText');
    const livePulse = document.getElementById('livePulse');
    const lastUpdatedText = document.getElementById('lastUpdatedText');

    // Helper: Draw Pure-SVG Sparkline (zero external dependencies)
    function drawSparkline(svgId, dataPoints, minVal = 0, maxVal = null, strokeColor = '#0d6efd', fillColor = 'rgba(13, 110, 253, 0.12)') {
        const svg = document.getElementById(svgId);
        if (!svg || dataPoints.length === 0) return;

        const width = svg.clientWidth || 240;
        const height = svg.clientHeight || 28;
        const padding = 2;

        let effectiveMax = maxVal;
        if (effectiveMax === null) {
            effectiveMax = Math.max(...dataPoints);
            if (effectiveMax === 0) effectiveMax = 1;
        }
        let effectiveMin = minVal !== null ? minVal : Math.min(...dataPoints);
        const range = (effectiveMax - effectiveMin) || 1;

        const points = dataPoints.map((val, idx) => {
            const x = (idx / Math.max(1, dataPoints.length - 1)) * (width - padding * 2) + padding;
            const normalized = (val - effectiveMin) / range;
            const y = height - padding - (normalized * (height - padding * 2));
            return `${x.toFixed(1)},${y.toFixed(1)}`;
        });

        const lineD = 'M ' + points.join(' L ');
        const fillD = `${lineD} L ${width - padding},${height} L ${padding},${height} Z`;

        svg.innerHTML = `
            <path d="${fillD}" fill="${fillColor}" />
            <path d="${lineD}" fill="none" stroke="${strokeColor}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            <circle cx="${points[points.length - 1].split(',')[0]}" cy="${points[points.length - 1].split(',')[1]}" r="2.5" fill="${strokeColor}" />
        `;
    }

    // Main AJAX update routine
    async function fetchTelemetry(showSpinner = false) {
        if (showSpinner && refreshIcon) {
            refreshIcon.classList.add('fa-spin');
        }

        try {
            const res = await fetch('<?= base_url('admin/telemetry/live') ?>', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store'
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            updateDashboard(data);

            if (lastUpdatedText) {
                const now = new Date();
                lastUpdatedText.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
        } catch (err) {
            console.warn('[Telemetry] Poll failed:', err);
            if (liveStatusText) liveStatusText.textContent = 'Connection Issue';
            if (livePulse) livePulse.style.backgroundColor = '#dc3545';
        } finally {
            if (showSpinner && refreshIcon) {
                refreshIcon.classList.remove('fa-spin');
            }
        }
    }

    function updateDashboard(data) {
        if (!data || !data.web || !data.mysql || !data.ml) return;

        const web = data.web;
        const mysql = data.mysql;
        const ml = data.ml;

        if (liveStatusText) liveStatusText.textContent = 'Live Monitoring';
        if (livePulse) livePulse.style.backgroundColor = '#28a745';

        // 1. WebApp Container
        const webCpuPct = web.cpu?.load_pct || 0;
        const webCpuIdle = web.cpu?.idle_pct ?? Math.max(0, 100 - webCpuPct);
        const webRamUsed = web.memory?.container_used_mb || 0;
        const webRamLimit = web.memory?.container_limit_mb || web.memory?.host_total_mb || 0;
        const webRamFree = web.memory?.container_free_mb ?? Math.max(0, webRamLimit - webRamUsed);
        const webRamUsedPct = web.memory?.container_used_pct || 0;

        document.getElementById('kpiWebCpu').textContent = webCpuPct + '%';
        const kpiWebCpuIdle = document.getElementById('kpiWebCpuIdle');
        if (kpiWebCpuIdle) kpiWebCpuIdle.textContent = webCpuIdle + '%';

        document.getElementById('kpiWebRam').textContent = Math.round(webRamUsed).toLocaleString() + ' MB';
        const kpiWebRamLimit = document.getElementById('kpiWebRamLimit');
        if (kpiWebRamLimit) kpiWebRamLimit.textContent = Math.round(webRamLimit).toLocaleString() + ' MB';

        document.getElementById('kpiWebUptime').textContent = web.uptime_formatted || '0s';

        // OOM Risk warning badge
        const kpiWebOomBadge = document.getElementById('kpiWebOomBadge');
        if (kpiWebOomBadge) {
            if (web.memory?.container_oom_warning) {
                kpiWebOomBadge.classList.remove('d-none');
            } else {
                kpiWebOomBadge.classList.add('d-none');
            }
        }

        // WebApp CPU Sparkline
        historyWebCpu.push(webCpuPct);
        if (historyWebCpu.length > MAX_HISTORY) historyWebCpu.shift();
        const sparkCpuColor = webCpuPct > 85 ? '#dc3545' : (webCpuPct > 65 ? '#ffc107' : '#0d6efd');
        const sparkCpuFill = webCpuPct > 85 ? 'rgba(220, 53, 69, 0.15)' : 'rgba(13, 110, 253, 0.12)';
        drawSparkline('sparkWebCpu', historyWebCpu, 0, 100, sparkCpuColor, sparkCpuFill);
        const sparkWebCpuVal = document.getElementById('sparkWebCpuVal');
        if (sparkWebCpuVal) sparkWebCpuVal.textContent = webCpuPct + '%';

        document.getElementById('webCpuPctText').textContent = webCpuPct + '%';
        const webCpuIdleText = document.getElementById('webCpuIdleText');
        if (webCpuIdleText) webCpuIdleText.textContent = webCpuIdle + '%';

        const webCpuBar = document.getElementById('webCpuBar');
        if (webCpuBar) {
            webCpuBar.style.width = webCpuPct + '%';
            webCpuBar.className = 'progress-bar ' + (webCpuPct > 85 ? 'bg-danger' : (webCpuPct > 65 ? 'bg-warning' : 'bg-primary'));
        }

        document.getElementById('webLoad1m').textContent = web.cpu?.load_1m || 0;
        document.getElementById('webLoad5m').textContent = web.cpu?.load_5m || 0;
        document.getElementById('webLoad15m').textContent = web.cpu?.load_15m || 0;

        const limitLabel = web.memory?.container_limit_mb ? (Math.round(web.memory.container_limit_mb).toLocaleString() + ' MB') : (Math.round(web.memory?.host_total_mb || 0).toLocaleString() + ' MB (Host)');
        document.getElementById('webRamText').textContent = Math.round(webRamUsed).toLocaleString() + ' MB / ' + limitLabel + ' (' + webRamUsedPct + '%)';
        const webRamBar = document.getElementById('webRamBar');
        if (webRamBar) {
            webRamBar.style.width = webRamUsedPct + '%';
            webRamBar.className = 'progress-bar ' + (webRamUsedPct > 85 ? 'bg-danger' : (webRamUsedPct > 70 ? 'bg-warning' : 'bg-info'));
        }

        const webRamFreeEl = document.getElementById('webRamFree');
        if (webRamFreeEl) webRamFreeEl.textContent = Math.round(webRamFree).toLocaleString() + ' MB';

        document.getElementById('webPhpAlloc').textContent = (web.memory?.php_allocated_mb || 0).toFixed(1) + ' MB';
        document.getElementById('webPhpPeak').textContent = (web.memory?.php_peak_mb || 0).toFixed(1) + ' MB';
        document.getElementById('webPhpLimit').textContent = web.memory?.php_limit || 'N/A';

        const usedGb = ((web.disk?.used_mb || 0) / 1024).toFixed(2);
        const totalGb = ((web.disk?.total_mb || 0) / 1024).toFixed(2);
        const freeGb = ((web.disk?.free_mb || 0) / 1024).toFixed(2);
        const freePct = web.disk?.free_pct ?? Math.max(0, 100 - (web.disk?.used_pct || 0));

        document.getElementById('webDiskText').textContent = usedGb + ' GB / ' + totalGb + ' GB (' + (web.disk?.used_pct || 0) + '%)';
        const webDiskBar = document.getElementById('webDiskBar');
        if (webDiskBar) webDiskBar.style.width = (web.disk?.used_pct || 0) + '%';
        document.getElementById('webDiskFree').textContent = freeGb + ' GB';
        const webDiskFreePct = document.getElementById('webDiskFreePct');
        if (webDiskFreePct) webDiskFreePct.textContent = freePct + '%';
        document.getElementById('webDiskPct').textContent = (web.disk?.used_pct || 0) + '%';
        document.getElementById('webSessions').textContent = web.runtime?.sessions || 0;

        // 2. MySQL Container
        const mysqlConn = mysql.connections?.connected || 0;
        const mysqlMaxConn = mysql.connections?.max || 151;
        const mysqlConnPct = mysql.connections?.used_pct || 0;
        const mysqlFreeConn = mysql.connections?.free ?? Math.max(0, mysqlMaxConn - mysqlConn);
        const mysqlQps = mysql.throughput?.qps || 0;

        document.getElementById('kpiMysqlConn').textContent = mysqlConn;
        const kpiMysqlConnPct = document.getElementById('kpiMysqlConnPct');
        if (kpiMysqlConnPct) kpiMysqlConnPct.textContent = mysqlConnPct + '%';
        const kpiMysqlConnFree = document.getElementById('kpiMysqlConnFree');
        if (kpiMysqlConnFree) kpiMysqlConnFree.textContent = mysqlFreeConn;

        document.getElementById('kpiMysqlQps').textContent = mysqlQps;

        // MySQL QPS Sparkline
        historyMysqlQps.push(mysqlQps);
        if (historyMysqlQps.length > MAX_HISTORY) historyMysqlQps.shift();
        drawSparkline('sparkMysqlQps', historyMysqlQps, 0, null, '#0dcaf0', 'rgba(13, 202, 240, 0.12)');
        const sparkMysqlQpsVal = document.getElementById('sparkMysqlQpsVal');
        if (sparkMysqlQpsVal) sparkMysqlQpsVal.textContent = mysqlQps + ' QPS';

        // Slow Queries & Aborted Connects
        const slowQ = mysql.throughput?.slow_queries || 0;
        const abortedConn = mysql.connections?.aborted || 0;
        const kpiMysqlSlowQ = document.getElementById('kpiMysqlSlowQ');
        if (kpiMysqlSlowQ) {
            kpiMysqlSlowQ.textContent = slowQ;
            kpiMysqlSlowQ.className = slowQ > 0 ? 'text-danger fw-bold' : 'text-success';
        }
        const mysqlSlowQ = document.getElementById('mysqlSlowQ');
        if (mysqlSlowQ) {
            mysqlSlowQ.textContent = slowQ.toLocaleString();
            mysqlSlowQ.className = slowQ > 0 ? 'text-danger fw-bold' : 'text-success';
        }
        const mysqlAbortedConnects = document.getElementById('mysqlAbortedConnects');
        if (mysqlAbortedConnects) {
            mysqlAbortedConnects.textContent = abortedConn.toLocaleString();
            mysqlAbortedConnects.className = abortedConn > 0 ? 'text-warning fw-semibold' : 'text-dark';
        }

        document.getElementById('mysqlConnText').textContent = mysqlConn + ' / ' + mysqlMaxConn + ' max (' + mysqlConnPct + '%)';
        const mysqlConnBar = document.getElementById('mysqlConnBar');
        if (mysqlConnBar) mysqlConnBar.style.width = mysqlConnPct + '%';
        document.getElementById('mysqlThreadsRunning').textContent = mysql.connections?.running || 0;
        const mysqlFreeConnEl = document.getElementById('mysqlFreeConn');
        if (mysqlFreeConnEl) mysqlFreeConnEl.textContent = mysqlFreeConn;
        document.getElementById('mysqlMaxUsed').textContent = mysql.connections?.max_used || 0;
        document.getElementById('mysqlLatency').textContent = (mysql.latency_ms || 0) + ' ms';
        document.getElementById('mysqlQps').textContent = mysqlQps;

        const bpDataMb = mysql.buffer_pool?.data_mb || 0;
        const bpSizeMb = mysql.buffer_pool?.size_mb || 0;
        const bpFreeMb = mysql.buffer_pool?.free_mb ?? Math.max(0, bpSizeMb - bpDataMb);
        const bpUsedPct = mysql.buffer_pool?.used_pct || 0;

        document.getElementById('mysqlBpText').textContent = bpDataMb.toFixed(1) + ' MB / ' + bpSizeMb.toFixed(1) + ' MB (' + bpUsedPct + '%)';
        const mysqlBpBar = document.getElementById('mysqlBpBar');
        if (mysqlBpBar) mysqlBpBar.style.width = bpUsedPct + '%';
        const mysqlBpFree = document.getElementById('mysqlBpFree');
        if (mysqlBpFree) mysqlBpFree.textContent = bpFreeMb.toFixed(1) + ' MB';

        document.getElementById('mysqlDbSizeText').textContent = (mysql.database_size_mb || 0).toFixed(2) + ' MB';
        document.getElementById('mysqlTables').textContent = (mysql.tables_count || 0) + ' tables';
        document.getElementById('mysqlRows').textContent = (mysql.approx_rows || 0).toLocaleString();
        document.getElementById('mysqlUptime').textContent = mysql.uptime_formatted || '0s';

        // 3. ML Engine Container
        const mlOnline = ml.status === 'online';
        const mlLatency = ml.latency_ms || 0;
        const kpiMlStatus = document.getElementById('kpiMlStatus');
        if (kpiMlStatus) {
            kpiMlStatus.textContent = mlOnline ? 'online' : 'offline';
            kpiMlStatus.className = 'badge metric-badge ' + (mlOnline ? 'bg-success' : 'bg-danger');
        }

        const llamaRunning = ml.inference?.llama_running;
        const mlLlamaStatus = document.getElementById('mlLlamaStatus');
        if (mlLlamaStatus) {
            mlLlamaStatus.textContent = llamaRunning ? 'Llama Server: Running (PID ' + (ml.inference?.llama_pid || '') + ')' : 'Llama Server: Standby / Stopped';
            mlLlamaStatus.className = 'badge rounded-pill px-3 py-1 ' + (llamaRunning ? 'bg-warning-subtle text-warning-emphasis' : 'bg-secondary-subtle text-secondary');
        }

        document.getElementById('kpiMlMode').textContent = ml.gpu?.has_gpu ? 'GPU' : 'CPU';
        document.getElementById('kpiMlLatency').textContent = '(' + mlLatency + ' ms API latency)';
        document.getElementById('mlLatencyBadge').textContent = 'API: ' + mlLatency + ' ms';

        // ML Latency Sparkline
        historyMlLatency.push(mlLatency);
        if (historyMlLatency.length > MAX_HISTORY) historyMlLatency.shift();
        drawSparkline('sparkMlLatency', historyMlLatency, 0, null, '#ffc107', 'rgba(255, 193, 7, 0.15)');
        const sparkMlLatencyVal = document.getElementById('sparkMlLatencyVal');
        if (sparkMlLatencyVal) sparkMlLatencyVal.textContent = mlLatency + ' ms';

        document.getElementById('kpiMlModel').textContent = ml.inference?.active_model || 'None';
        document.getElementById('kpiMlQueue').textContent = (ml.queue?.active_jobs || 0) + ' active';

        // GPU / Hardware Card Update
        const gpuWrapper = document.getElementById('gpuCardWrapper');
        if (gpuWrapper) {
            if (ml.gpu?.has_gpu && ml.gpu.gpus && ml.gpu.gpus.length > 0) {
                const g = ml.gpu.gpus[0];
                const vTot = g.memory_total_mb || 1;
                const vUsed = g.memory_used_mb || 0;
                const vPct = Math.min(100, Math.round((vUsed / vTot) * 100));
                gpuWrapper.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-success"><i class="fa-solid fa-bolt me-1"></i> ${g.name || 'NVIDIA GPU'}</span>
                        <span class="badge bg-success">GPU Accelerated</span>
                    </div>
                    <div class="small text-muted mb-2">Driver: ${g.driver_version || 'N/A'} | Temp: ${g.temperature_c || 0}°C</div>
                    <div class="d-flex justify-content-between small fw-semibold mb-1">
                        <span>VRAM Memory</span>
                        <span>${Math.round(vUsed).toLocaleString()} MB / ${Math.round(vTot).toLocaleString()} MB</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: ${vPct}%;"></div>
                    </div>
                `;
            } else {
                gpuWrapper.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-primary"><i class="fa-solid fa-microchip me-1"></i> CPU Vectorized Inference (AVX2 / OpenMP)</div>
                            <div class="small text-muted">Running in optimized CPU container mode. No discrete NVIDIA GPU detected.</div>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">CPU Mode</span>
                    </div>
                `;
            }
        }

        const mlRamUsedMb = ml.memory?.container_used_mb || 0;
        document.getElementById('mlRamText').textContent = Math.round(mlRamUsedMb).toLocaleString() + ' MB (Python + Model Server)';
        const mlRamBar = document.getElementById('mlRamBar');
        if (mlRamBar) mlRamBar.style.width = (ml.memory?.host_used_pct || 20) + '%';
        document.getElementById('mlPythonRss').textContent = (ml.memory?.python_rss_mb || 0).toFixed(1) + ' MB';
        document.getElementById('mlLlamaRss').textContent = (ml.memory?.llama_rss_mb || 0).toFixed(1) + ' MB';

        document.getElementById('mlModelName').textContent = ml.inference?.active_model || 'None';
        document.getElementById('mlModelSize').textContent = (ml.inference?.model_size_mb || 0).toFixed(1) + ' MB';
        document.getElementById('mlCtxSize').textContent = (ml.inference?.ctx_size || 16384).toLocaleString() + ' tok';
        document.getElementById('mlBatchSize').textContent = ml.inference?.batch_size || 512;

        document.getElementById('mlQueueActive').textContent = ml.queue?.active_jobs || 0;
        document.getElementById('mlQueueQueued').textContent = ml.queue?.queued_jobs || 0;
        document.getElementById('mlQueueCompleted').textContent = ml.queue?.completed_today || 0;
    }

    // Interval Timer & Page Visibility handling
    function resetTimer() {
        if (pollTimer) clearInterval(pollTimer);
        if (pollInterval > 0) {
            pollTimer = setInterval(() => fetchTelemetry(false), pollInterval);
        }
    }

    if (intervalSelect) {
        intervalSelect.addEventListener('change', (e) => {
            pollInterval = parseInt(e.target.value, 10);
            resetTimer();
            if (pollInterval === 0) {
                if (liveStatusText) liveStatusText.textContent = 'Monitoring Paused';
                if (livePulse) livePulse.style.backgroundColor = '#6c757d';
            } else {
                fetchTelemetry(true);
            }
        });
    }

    if (manualRefreshBtn) {
        manualRefreshBtn.addEventListener('click', () => {
            fetchTelemetry(true);
        });
    }

    // Suspend polling when tab is inactive to save container cycles
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (pollTimer) clearInterval(pollTimer);
        } else {
            if (pollInterval > 0) {
                fetchTelemetry(false);
                resetTimer();
            }
        }
    });

    // Initial render of first sparkline point from preloaded PHP data
    const initialWebCpu = <?= json_encode($web['cpu']['load_pct'] ?? 0) ?>;
    const initialMysqlQps = <?= json_encode($mysql['throughput']['qps'] ?? 0) ?>;
    const initialMlLat = <?= json_encode($ml['latency_ms'] ?? 0) ?>;
    historyWebCpu.push(initialWebCpu);
    historyMysqlQps.push(initialMysqlQps);
    historyMlLatency.push(initialMlLat);
    drawSparkline('sparkWebCpu', historyWebCpu, 0, 100, '#0d6efd', 'rgba(13, 110, 253, 0.12)');
    drawSparkline('sparkMysqlQps', historyMysqlQps, 0, null, '#0dcaf0', 'rgba(13, 202, 240, 0.12)');
    drawSparkline('sparkMlLatency', historyMlLatency, 0, null, '#ffc107', 'rgba(255, 193, 7, 0.15)');

    // Start polling on load
    resetTimer();
});
</script>
<?= $this->endSection() ?>
