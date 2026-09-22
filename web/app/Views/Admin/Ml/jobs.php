<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Jobs - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); }
    .stat-card     { border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); border-top:3px solid var(--stat-color, var(--primary)); }
    .stat-icon     { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink:0; }
    .job-row.failed{ background-color: rgba(220,53,69,0.04); }
    .system-table td, .system-table th { font-size: 0.85rem; }
    .engine-local    { background:#e0f2fe; color:#0369a1; }
    .engine-external { background:#eef2ff; color:#4338ca; }
    .kv-section-label { font-size:.7rem; text-transform:uppercase; letter-spacing:.07em; font-weight:700; color:#94a3b8; margin-bottom:.5rem; }
    .meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:.25rem 1.5rem; }
    .meta-row  { display:flex; justify-content:space-between; align-items:baseline; padding:.25rem 0; border-bottom:1px solid #f1f5f9; font-size:.82rem; }
    .meta-row:last-child { border-bottom:none; }
    .meta-key  { color:#64748b; }
    .meta-val  { font-weight:600; color:#1e293b; text-align:right; max-width:60%; word-break:break-all; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-list-check me-2"></i> ML Jobs</h2>
        <p class="text-secondary mb-0">History and statistics of ML SMS-classification runs.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="card settings-card mb-4">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'jobs', 'status' => $status]) ?>
    </div>
</div>

<!-- Auto-jobs toggle card -->
<div class="card settings-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="stat-icon bg-<?= !empty($auto['auto_enabled']) ? 'success' : 'danger' ?>-subtle text-<?= !empty($auto['auto_enabled']) ? 'success' : 'danger' ?>">
                <i class="fa-solid fa-<?= !empty($auto['auto_enabled']) ? 'play' : 'pause' ?>"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold fs-6">Auto Processing Jobs</div>
                <div class="text-muted small">
                    <?php if ($auto['reachable'] === false): ?>
                        Backend offline — toggle unavailable.
                    <?php elseif (!empty($auto['auto_enabled'])): ?>
                        Running — the backend polls for new SMS and processes them automatically.
                    <?php else: ?>
                        <strong class="text-danger">Stopped</strong> — no ML jobs will run until re-enabled.
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($auto['reachable'] !== false): ?>
                <button type="button" id="autoToggleBtn"
                        class="btn btn-<?= !empty($auto['auto_enabled']) ? 'danger' : 'success' ?> rounded-pill px-4 fw-semibold">
                    <i class="fa-solid fa-<?= !empty($auto['auto_enabled']) ? 'stop' : 'play' ?> me-1"></i>
                    <?= !empty($auto['auto_enabled']) ? 'Stop Auto Jobs' : 'Start Auto Jobs' ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (empty($jobs)): ?>
    <div class="card settings-card">
        <div class="card-body p-5 text-center text-muted">
            <i class="fa-solid fa-clock-rotate-left fs-1 d-block mb-2"></i>
            No processing jobs recorded yet.
        </div>
    </div>
<?php else: ?>
    <!-- Summary stats Accordion -->
    <div class="accordion mb-4" id="statsAccordion">
        <div class="accordion-item glass-card border-0 shadow-sm" style="border-radius: 10px; overflow: hidden;">
            <h2 class="accordion-header" id="statsHeader">
                <button class="accordion-button collapsed fw-bold px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#statsCollapse" aria-expanded="false" aria-controls="statsCollapse" style="color: var(--primary); background: #f8fafc;">
                    <i class="fa-solid fa-chart-simple me-2"></i>View ML Classification Statistics
                </button>
            </h2>
            <div id="statsCollapse" class="accordion-collapse collapse" aria-labelledby="statsHeader" data-bs-parent="#statsAccordion">
                <div class="accordion-body p-4" style="background: #ffffff;">
                    <div class="row g-3">
                        <div class="col-6 col-lg-3">
                            <div class="card stat-card h-100" style="--stat-color:#0d6efd;">
                                <div class="card-body p-3 d-flex align-items-start gap-3">
                                    <div class="stat-icon bg-primary-subtle text-primary"><i class="fa-solid fa-list-check"></i></div>
                                    <div>
                                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['jobs']) ?></div>
                                        <div class="fw-semibold small">Total Jobs</div>
                                        <div class="text-muted small">Classification runs</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card stat-card h-100" style="--stat-color:#198754;">
                                <div class="card-body p-3 d-flex align-items-start gap-3">
                                    <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-envelope"></i></div>
                                    <div>
                                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['msgs']) ?></div>
                                        <div class="fw-semibold small">SMS Classified</div>
                                        <div class="text-muted small">Messages analyzed</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card stat-card h-100" style="--stat-color:#dc3545;">
                                <div class="card-body p-3 d-flex align-items-start gap-3">
                                    <div class="stat-icon bg-danger-subtle text-danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
                                    <div>
                                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['errors']) ?></div>
                                        <div class="fw-semibold small">Errors</div>
                                        <div class="text-muted small">Failed classifications</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card stat-card h-100" style="--stat-color:#fd7e14;">
                                <div class="card-body p-3 d-flex align-items-start gap-3">
                                    <div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-stopwatch"></i></div>
                                    <div>
                                        <div class="fw-bold fs-3 lh-1"><?= $totals['time'] > 0 ? gmdate('H:i:s', $totals['time']) : '0s' ?></div>
                                        <div class="fw-semibold small">Total Time</div>
                                        <div class="text-muted small">Cumulative processing</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card stat-card h-100" style="--stat-color:#6f42c1;">
                                <div class="card-body p-3 d-flex align-items-start gap-3">
                                    <div class="stat-icon bg-secondary-subtle text-secondary"><i class="fa-solid fa-address-book"></i></div>
                                    <div>
                                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['senders']) ?></div>
                                        <div class="fw-semibold small">Senders Seen</div>
                                        <div class="text-muted small"><?= number_format($totals['senders_finance']) ?> finance / <?= number_format($totals['senders_unwanted']) ?> unwanted</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card stat-card h-100" style="--stat-color:#198754;">
                                <div class="card-body p-3 d-flex align-items-start gap-3">
                                    <div class="stat-icon bg-success-subtle text-success"><i class="fa-solid fa-envelope-open-text"></i></div>
                                    <div>
                                        <div class="fw-bold fs-3 lh-1 text-success"><?= number_format($totals['sms_finance']) ?></div>
                                        <div class="fw-semibold small">Total Finance SMS</div>
                                        <div class="text-muted small">out of <?= number_format($totals['sms_total']) ?> raw SMS</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card stat-card h-100" style="--stat-color:#6c757d;">
                                <div class="card-body p-3 d-flex align-items-start gap-3">
                                    <div class="stat-icon bg-warning-subtle text-warning"><i class="fa-solid fa-ban"></i></div>
                                    <div>
                                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['sms_skipped']) ?></div>
                                        <div class="fw-semibold small">Skipped / Non-Finance</div>
                                        <div class="text-muted small">From unwanted senders</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="card stat-card h-100" style="--stat-color:#0dcaf0;">
                                <div class="card-body p-3 d-flex align-items-start gap-3">
                                    <div class="stat-icon bg-info-subtle text-info"><i class="fa-solid fa-money-bill-transfer"></i></div>
                                    <div>
                                        <div class="fw-bold fs-3 lh-1"><?= number_format($totals['transactional']) ?></div>
                                        <div class="fw-semibold small">Transactions Found</div>
                                        <div class="text-muted small">Inserted across all jobs</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job history table -->
    <div class="card settings-card">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="fw-bold mb-0" style="color: var(--primary);"><i class="fa-solid fa-clock-rotate-left me-2"></i> Job History</h5>
                <div class="d-flex gap-2 flex-wrap">
                    <?php foreach ($status_counts as $st => $cnt): ?>
                        <span class="badge bg-secondary"><?= esc($st) ?>: <?= $cnt ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-striped system-table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Engine</th>
                            <th>Model</th>
                            <th>Status</th>
                            <th>Duration</th>
                            <th>Finance / Raw SMS</th>
                            <th>Errors</th>
                            <th>Cost</th>
                            <th>Queued</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $j):
                            $md     = $j['metadata'] ?? [];
                            $engine = $md['llm_engine'] ?? 'local';
                            $isExt  = $engine === 'external';
                        ?>
                        <tr class="job-row <?= in_array($j['status'], ['failed','error']) ? 'failed' : '' ?>">
                            <td class="fw-semibold text-muted">#<?= $j['id'] ?></td>
                            <td>
                                <span class="badge rounded-pill engine-<?= $isExt ? 'external' : 'local' ?>" style="font-size:.72rem;">
                                    <i class="fa-solid fa-<?= $isExt ? 'cloud' : 'server' ?> me-1"></i><?= $isExt ? 'External' : 'Local' ?>
                                </span>
                            </td>
                            <td><small class="text-muted"><?= esc($md['model'] ?? '—') ?></small></td>
                            <td>
                                <?php $badge = match ($j['status']) {
                                    'completed', 'done' => 'success',
                                    'failed', 'error'   => 'danger',
                                    'processing', 'starting' => 'warning',
                                    'queued'   => 'info',
                                    'disabled' => 'secondary',
                                    default    => 'secondary',
                                }; ?>
                                <span class="badge bg-<?= $badge ?>"><?= esc($j['status']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($j['duration_seconds'])): ?>
                                    <?= gmdate('i:s', (int)$j['duration_seconds']) ?>
                                <?php elseif ($j['started_at'] && $j['completed_at']): ?>
                                    <?= gmdate('i:s', max(0, strtotime($j['completed_at']) - strtotime($j['started_at']))) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($md['sms_finance'])): ?>
                                    <span class="fw-bold text-success"><?= number_format((int)$md['sms_finance']) ?></span>
                                    <small class="text-muted">/ <?= number_format((int)($md['sms_total'] ?? $j['messages_processed'])) ?></small>
                                <?php else: ?>
                                    <?= number_format((int)($md['messages_processed'] ?? $j['messages_processed'])) ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-<?= $j['errors'] > 0 ? 'danger fw-semibold' : 'muted' ?>"><?= (int)($md['errors'] ?? $j['errors']) ?></td>
                            <td>
                                <?php if ($isExt && isset($j['cost']) && $j['cost'] > 0): ?>
                                    <span class="text-primary fw-bold">$<?= number_format($j['cost'], 4) ?></span>
                                    <div class="text-muted" style="font-size:0.65rem;">
                                        <i class="fa-solid fa-arrow-right-to-bracket me-1"></i><?= number_format($j['tokens']['prompt'] ?? 0) ?>
                                        <i class="fa-solid fa-arrow-right-from-bracket ms-1 me-1"></i><?= number_format($j['tokens']['reply'] ?? 0) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 0.8rem;">$0.0000</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?= esc($j['created_at'] ?? '—') ?></small></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#jobmeta-<?= (int)$j['id'] ?>">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </button>
                                    <?php if (in_array($j['status'], ['queued', 'processing', 'starting'])): ?>
                                        <button class="btn btn-sm btn-outline-danger btn-stop-job" data-job-id="<?= (int)$j['id'] ?>" title="Stop Job">
                                            <i class="fa-solid fa-stop"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>

                        <!-- Expanded metadata row -->
                        <tr class="collapse" id="jobmeta-<?= (int)$j['id'] ?>">
                            <td colspan="9" class="p-0">
                                <div class="px-4 py-3" style="background:#f8fafc; border-left:4px solid <?= $isExt ? '#6366f1' : '#0ea5e9' ?>;">

                                    <!-- Engine header -->
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <span class="badge engine-<?= $isExt ? 'external' : 'local' ?> rounded-pill px-3 py-1">
                                            <i class="fa-solid fa-<?= $isExt ? 'cloud' : 'server' ?> me-1"></i>
                                            <?= $isExt ? 'External Engine' : 'Local Engine' ?>
                                        </span>
                                        <span class="fw-semibold"><?= esc($md['model'] ?? '—') ?></span>
                                        <?php if (!empty($md['model_provider'])): ?>
                                            <span class="text-muted small">&middot; <?= esc($md['model_provider']) ?></span>
                                        <?php endif; ?>
                                        <span class="ms-auto text-muted small">
                                            <?= esc($j['started_at'] ?? '—') ?> → <?= esc($j['completed_at'] ?? '—') ?>
                                        </span>
                                    </div>

                                    <div class="row g-3">
                                        <!-- Senders & SMS -->
                                        <div class="col-md-4">
                                            <div class="kv-section-label"><i class="fa-solid fa-address-book me-1"></i>Senders & SMS</div>
                                            <div class="meta-row"><span class="meta-key">All senders</span><span class="meta-val"><?= number_format((int)($md['senders_total'] ?? 0)) ?></span></div>
                                            <div class="meta-row"><span class="meta-key text-success">Finance senders</span><span class="meta-val text-success"><?= number_format((int)($md['senders_finance'] ?? 0)) ?></span></div>
                                            <div class="meta-row"><span class="meta-key text-danger">Unwanted senders</span><span class="meta-val text-danger"><?= number_format((int)($md['senders_unwanted'] ?? 0)) ?></span></div>
                                            <div class="meta-row"><span class="meta-key">All SMS</span><span class="meta-val"><?= number_format((int)($md['sms_total'] ?? 0)) ?></span></div>
                                            <div class="meta-row"><span class="meta-key text-success">Finance SMS</span><span class="meta-val text-success"><?= number_format((int)($md['sms_finance'] ?? 0)) ?></span></div>
                                            <div class="meta-row"><span class="meta-key text-danger">Unwanted SMS</span><span class="meta-val text-danger"><?= number_format((int)($md['sms_unwanted'] ?? 0)) ?></span></div>
                                            <div class="meta-row"><span class="meta-key text-warning">Skipped SMS</span><span class="meta-val text-warning"><?= number_format((int)($md['sms_skipped'] ?? 0)) ?></span></div>
                                            <div class="meta-row"><span class="meta-key text-info">Transactions found</span><span class="meta-val text-info"><?= number_format((int)($md['transactional_inserted'] ?? 0)) ?></span></div>

                                            <?php $agg = $md['aggregation'] ?? null; if (!empty($agg)): ?>
                                                <div class="kv-section-label mt-2"><i class="fa-solid fa-chart-line me-1"></i>Financial Insights</div>
                                                <div class="meta-row"><span class="meta-key text-danger">Total Sent</span><span class="meta-val text-danger"><?= number_format((float)($agg['total_sent_money'] ?? 0), 2) ?></span></div>
                                                <div class="meta-row"><span class="meta-key text-success">Total Received</span><span class="meta-val text-success"><?= number_format((float)($agg['total_received_money'] ?? 0), 2) ?></span></div>
                                                <div class="meta-row"><span class="meta-key text-primary">Total Volume</span><span class="meta-val text-primary"><?= number_format((float)($agg['total_transaction_volume'] ?? 0), 2) ?></span></div>
                                                <div class="meta-row"><span class="meta-key text-info">Speed</span><span class="meta-val text-info"><?= esc($md['processing_rate_sms_per_sec'] ?? '—') ?> SMS/s</span></div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Engine config -->
                                        <div class="col-md-4">
                                            <div class="kv-section-label">
                                                <i class="fa-solid fa-<?= $isExt ? 'cloud' : 'server' ?> me-1"></i>
                                                <?= $isExt ? 'External' : 'Local' ?> Engine Config
                                            </div>
                                            <div class="meta-row"><span class="meta-key">Provider</span><span class="meta-val"><?= esc($md['model_provider'] ?? '—') ?></span></div>
                                            <div class="meta-row"><span class="meta-key">Model</span><span class="meta-val"><?= esc($md['model'] ?? '—') ?></span></div>
                                            <div class="meta-row"><span class="meta-key">Base URL</span><span class="meta-val" style="font-size:.75rem;"><?= esc($md['model_base_url'] ?? '—') ?></span></div>
                                            <div class="meta-row"><span class="meta-key">Max tokens</span><span class="meta-val"><?= esc($md['llm_max_tokens'] ?? '—') ?></span></div>
                                            <div class="meta-row"><span class="meta-key">Temperature</span><span class="meta-val"><?= esc($md['llm_temperature'] ?? '—') ?></span></div>
                                            <?php if (!$isExt): ?>
                                                <div class="meta-row"><span class="meta-key">Context size</span><span class="meta-val"><?= esc($md['llm_ctx_size'] ?? '—') ?></span></div>
                                                <div class="meta-row"><span class="meta-key">Prompt batch</span><span class="meta-val"><?= esc($md['llm_batch_size'] ?? '—') ?></span></div>
                                                <div class="meta-row"><span class="meta-key">GPU layers</span><span class="meta-val"><?= esc($md['n_gpu_layers'] ?? '—') ?></span></div>
                                            <?php endif; ?>
                                            <div class="meta-row"><span class="meta-key">SMS batch</span><span class="meta-val"><?= esc($md['sms_batch_size'] ?? '—') ?></span></div>
                                            <div class="meta-row"><span class="meta-key">Max retries</span><span class="meta-val"><?= esc($md['max_retries'] ?? '—') ?></span></div>
                                        </div>

                                        <!-- Extra: model path (local) or fallback (external) + errors -->
                                        <div class="col-md-4">
                                            <?php if (!$isExt && !empty($md['model_path'])): ?>
                                                <div class="kv-section-label"><i class="fa-solid fa-folder-open me-1"></i>Model File</div>
                                                <div class="meta-row"><span class="meta-key">Path</span><span class="meta-val" style="font-size:.72rem; word-break:break-all;"><?= esc($md['model_path']) ?></span></div>
                                            <?php endif; ?>
                                            <?php if ($isExt && !empty($md['fallback_enabled'])): ?>
                                                <div class="kv-section-label mt-2"><i class="fa-solid fa-shield-halved me-1"></i>Fallback</div>
                                                <div class="meta-row"><span class="meta-key">Provider</span><span class="meta-val"><?= esc($md['fallback_provider'] ?? '—') ?></span></div>
                                                <div class="meta-row"><span class="meta-key">Model</span><span class="meta-val"><?= esc($md['fallback_model'] ?? '—') ?></span></div>
                                            <?php endif; ?>
                                            <?php if (!empty($md['category_counts'])): ?>
                                                <div class="kv-section-label mt-2"><i class="fa-solid fa-tag me-1"></i>Category Breakdown</div>
                                                <?php foreach ($md['category_counts'] as $cat => $cnt): ?>
                                                    <div class="meta-row"><span class="meta-key"><?= esc($cat) ?></span><span class="meta-val"><?= number_format((int)$cnt) ?></span></div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <?php if (!empty($md['error'])): ?>
                                                <div class="alert alert-danger small mt-2 mb-0"><?= esc($md['error']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
document.getElementById('autoToggleBtn')?.addEventListener('click', function() {
    const currentlyEnabled = this.classList.contains('btn-danger');
    const enable = !currentlyEnabled;
    if (!confirm((enable ? 'Start' : 'Stop') + ' auto jobs?')) return;
    this.disabled = true;
    const data = new FormData();
    data.append('enabled', enable ? '1' : '0');
    fetch('<?= base_url('admin/ml/jobs/auto') ?>', { method: 'POST', body: data })
        .then(r => r.json()).then(res => {
            showAlert('Auto Jobs', res.message, res.status === 'ok' ? 'success' : 'danger');
            if (res.status === 'ok') setTimeout(() => window.location.reload(), 1200);
        })
        .catch(err => showAlert('Error', err.message, 'danger'))
        .finally(() => { this.disabled = false; });
});

document.querySelectorAll('.btn-stop-job').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const jobId = this.getAttribute('data-job-id');
        const self = this;
        
        Swal.fire({
            title: 'Stop ML Job?',
            text: 'Are you sure you want to stop/cancel Job #' + jobId + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Stop It!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                self.disabled = true;
                const formData = new FormData();
                formData.append('job_id', jobId);

                fetch('<?= base_url('admin/ml/jobs/stop') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Stopped!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        Swal.fire('Error', data.message, 'error');
                        self.disabled = false;
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Failed to send request: ' + err.message, 'error');
                    self.disabled = false;
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
