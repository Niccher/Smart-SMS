<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Database Info - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .section-head { display: flex; align-items: center; gap: 0.85rem; margin-bottom: 1.25rem; }
    .section-head .head-icon {
        width: 42px; height: 42px; border-radius: 4px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(93, 95, 239, 0.12); color: var(--primary); font-size: 1.1rem;
    }
    .metric {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.9rem 1rem; height: 100%;
        background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 4px;
    }
    .metric-icon {
        width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(93, 95, 239, 0.12); font-size: 0.95rem;
    }
    .metric-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); }
    .metric-value { font-weight: 700; color: var(--text-main); }
    .metric-value.trunc { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .stat-tile .card-body { padding: 1.25rem 1.25rem; }
    .stat-tile .tile-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); font-weight: 600; }
    [data-bs-theme="dark"] .settings-card { box-shadow: 0 4px 20px rgba(0,0,0,0.25); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-database me-2"></i> Database Information</h2>
        <p class="text-secondary mb-0">All details about the application database</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="card settings-card mb-4">
    <div class="card-body p-4 pb-0">
        <?= view('Admin/System/_nav', ['active' => 'db-info']) ?>
    </div>
</div>

<!-- Overview stat tiles -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card settings-card glass-card stat-tile">
            <div class="card-body">
                <div class="metric">
                    <span class="metric-icon text-primary"><i class="fa-solid fa-server"></i></span>
                    <div class="min-w-0">
                        <div class="metric-label">Database</div>
                        <div class="metric-value trunc" title="<?= esc($server['database']) ?>"><?= esc($server['database']) ?></div>
                        <small class="text-secondary"><?= esc($server['driver']) ?> driver</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card settings-card glass-card stat-tile">
            <div class="card-body">
                <div class="metric">
                    <span class="metric-icon text-primary"><i class="fa-solid fa-hard-drive"></i></span>
                    <div class="min-w-0">
                        <div class="metric-label">Total Size</div>
                        <div class="metric-value"><?= esc($stats['size_mb'] ?? '0') ?> MB</div>
                        <small class="text-secondary"><?= esc($total_size_human) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card settings-card glass-card stat-tile">
            <div class="card-body">
                <div class="metric">
                    <span class="metric-icon text-primary"><i class="fa-solid fa-table-list"></i></span>
                    <div class="min-w-0">
                        <div class="metric-label">Tables</div>
                        <div class="metric-value"><?= (int)($stats['table_count'] ?? 0) ?></div>
                        <small class="text-secondary">Data: <?= esc($stats['data_mb'] ?? '0') ?> MB · Index: <?= esc($stats['index_mb'] ?? '0') ?> MB</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card settings-card glass-card stat-tile">
            <div class="card-body">
                <div class="metric">
                    <span class="metric-icon text-primary"><i class="fa-solid fa-layer-group"></i></span>
                    <div class="min-w-0">
                        <div class="metric-label">Approx Rows</div>
                        <div class="metric-value"><?= esc($total_rows) ?></div>
                        <small class="text-secondary">Estimated (InnoDB)</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Diagnostic Health Bar & System Specs Toggle -->
<div class="card settings-card glass-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="fs-4 text-success"><i class="fa-solid fa-heart-pulse"></i></span>
                <div>
                    <h6 class="fw-bold mb-0">Database Health Status</h6>
                    <small class="text-secondary">Basic status metrics</small>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <span class="badge bg-success py-2 px-3 fs-7"><i class="fa-solid fa-circle-check me-1"></i> Connection: Active</span>
                <span class="badge bg-primary py-2 px-3 fs-7"><i class="fa-solid fa-database me-1"></i> Version: <?= esc($server['version']) ?></span>
                <span class="badge bg-info text-dark py-2 px-3 fs-7"><i class="fa-solid fa-table me-1"></i> Engine: InnoDB Default</span>
                <button class="btn btn-outline-primary btn-sm px-3" type="button" data-bs-toggle="collapse" data-bs-target="#systemSpecsCollapse" aria-expanded="false">
                    <i class="fa-solid fa-sliders me-1"></i> View System Specs
                </button>
            </div>
        </div>
        
        <!-- Collapsible System Specs -->
        <div class="collapse mt-3" id="systemSpecsCollapse">
            <div class="border-top pt-3 mt-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-network-wired"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Host</div>
                                <div class="metric-value"><?= esc($server['host']) ?>:<?= esc($server['port']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-gears"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Driver</div>
                                <div class="metric-value"><?= esc($server['driver']) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric">
                            <span class="metric-icon text-primary"><i class="fa-solid fa-font"></i></span>
                            <div class="min-w-0">
                                <div class="metric-label">Charset</div>
                                <div class="metric-value"><?= esc($server['charset'] ?: '—') ?> (<?= esc($server['collation'] ?: '—') ?>)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Storage Engine & Table footprint -->
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card settings-card glass-card h-100">
            <div class="card-body p-4">
                <div class="section-head">
                    <div class="head-icon"><i class="fa-solid fa-chart-pie"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">Table Storage Footprint</h5>
                        <small class="text-secondary">Proportional table sizes relative to total database size</small>
                    </div>
                </div>
                
                <?php
                // Sort tables by size to get top ones
                $topTables = array_slice($tables, 0, 5);
                $totalDbSize = 0;
                foreach ($tables as $t) {
                    $totalDbSize += $t['size_bytes'];
                }
                ?>
                
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($topTables as $t): 
                        $percentage = $totalDbSize > 0 ? round(($t['size_bytes'] / $totalDbSize) * 100, 1) : 0;
                    ?>
                        <div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold text-dark small"><code><?= esc($t['name']) ?></code></span>
                                <span class="text-secondary small fw-semibold"><?= esc($t['size_human']) ?> (<?= $percentage ?>%)</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $percentage ?>%" aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card settings-card glass-card h-100">
            <div class="card-body p-4">
                <div class="section-head">
                    <div class="head-icon"><i class="fa-solid fa-microchip"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">Storage Engines</h5>
                        <small class="text-secondary">Engine breakdown by usage</small>
                    </div>
                </div>
                <?php if (empty($engines)): ?>
                    <div class="text-secondary">No engine data.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Engine</th>
                                    <th class="text-end">Tables</th>
                                    <th class="text-end">Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($engines as $e): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fa-solid fa-database me-1"></i><?= esc($e['engine_name']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end"><i class="fa-solid fa-table-list me-1 text-secondary"></i><?= (int) $e['table_count'] ?></td>
                                        <td class="text-end"><i class="fa-solid fa-hard-drive me-1 text-secondary"></i><?= esc($e['size_mb']) ?> MB</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tables -->
<div class="card settings-card glass-card mt-4">
    <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="section-head mb-0">
                <div class="head-icon"><i class="fa-solid fa-table-list"></i></div>
                <div>
                    <h5 class="fw-bold mb-0">Tables <span class="text-secondary fs-6 fw-normal">(<?= count($tables) ?>)</span></h5>
                </div>
            </div>
            <div class="input-group input-group-sm" style="max-width: 260px;">
                <span class="input-group-text"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                <input type="text" id="tableSearch" class="form-control" placeholder="Filter tables...">
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" id="tablesTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 25%;">Table</th>
                        <th style="width: 50%;">Fields &amp; Rows</th>
                        <th style="width: 25%;">Size</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tables as $t): ?>
                        <tr>
                            <td>
                                <strong class="d-block"><i class="fa-solid fa-table me-1 text-secondary"></i><?= esc($t['name']) ?></strong>
                                <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                    <span class="badge bg-success"><i class="fa-solid fa-database me-1"></i><?= esc($t['engine_name']) ?></span>
                                    <span class="badge bg-secondary"><i class="fa-solid fa-globe me-1"></i><?= esc($t['collation']) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <strong><i class="fa-solid fa-table-columns me-1 text-secondary"></i><?= $t['field_count'] ?> fields</strong>
                                    <span class="badge bg-info text-dark"><i class="fa-solid fa-list me-1"></i><?= number_format((int) $t['row_count']) ?> rows</span>
                                </div>
                                <em class="text-secondary d-block mt-1" style="font-size: 0.8rem; white-space: normal; word-break: break-word;"><?= htmlspecialchars($t['field_list']) ?></em>
                            </td>
                            <td>
                                <div class="d-flex align-items-baseline">
                                    <small class="text-secondary fw-semibold text-uppercase" style="font-size: 0.7rem; width: 44px; flex-shrink: 0;">Total</small>
                                    <strong class="ms-1"><i class="fa-solid fa-database me-1 text-secondary"></i><?= esc($t['size_human']) ?></strong>
                                </div>
                                <div class="d-flex align-items-baseline mt-1">
                                    <small class="text-secondary" style="font-size: 0.75rem; width: 44px; flex-shrink: 0;">Data</small>
                                    <small class="ms-1"><?= esc($t['data_human']) ?></small>
                                </div>
                                <div class="d-flex align-items-baseline mt-1">
                                    <small class="text-secondary" style="font-size: 0.75rem; width: 44px; flex-shrink: 0;">Index</small>
                                    <small class="ms-1"><?= esc($t['index_human']) ?></small>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('tableSearch');
    const rows = document.querySelectorAll('#tablesTable tbody tr');
    input.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
});
</script>
<?= $this->endSection() ?>