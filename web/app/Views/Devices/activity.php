<?= $this->extend('Layouts/admin') ?>
<?= $this->section('title') ?> API Activity - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .stat-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .metric {
        display: flex; align-items: center; gap: 0.85rem;
        padding: 0.9rem 1rem; height: 100%;
        background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 4px;
    }
    .metric-icon {
        width: 46px; height: 46px; border-radius: 4px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
        background: rgba(93, 95, 239, 0.12); color: var(--primary);
    }
    .metric-value { font-weight: 700; font-size: 1.35rem; line-height: 1.2; }
    .metric-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); font-weight: 600; }
    .metric-desc { font-size: 0.78rem; color: var(--text-muted); }
    .section-head { display: flex; align-items: center; gap: 0.7rem; margin-bottom: 1rem; }
    .section-head .head-icon {
        width: 38px; height: 38px; border-radius: 4px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(93, 95, 239, 0.12); color: var(--primary); font-size: 1rem;
    }
    .endpoint-row { display: flex; justify-content: space-between; align-items: center; padding: 0.45rem 0; border-bottom: 1px dashed var(--card-border); }
    .endpoint-row:last-child { border-bottom: none; }
    .endpoint-count { font-weight: 700; min-width: 44px; text-align: right; }
    .feed-table td, .feed-table th { font-size: 0.82rem; }
    .feed-meta { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 0.72rem; color: #6c757d; word-break: break-all; }
    [data-bs-theme="dark"] .stat-card { box-shadow: 0 4px 20px rgba(0,0,0,0.25); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-bolt me-2"></i> API Activity</h2>
        <p class="text-secondary mb-0">Audit trail of every endpoint hit from your Android devices.</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('dashboard/devices') ?>">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Devices
    </a>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?php
    $isOutgoing = function ($action) {
        return preg_match('#(^|/)get/|download|export#i', (string) $action) === 1;
    };
    $incoming = [];
    $outgoing = [];
    foreach ($activity['actions'] as $a) {
        if ($isOutgoing($a['action'])) {
            $outgoing[] = $a;
        } else {
            $incoming[] = $a;
        }
    }
    $inTotal = array_sum(array_column($incoming, 'cnt'));
    $outTotal = array_sum(array_column($outgoing, 'cnt'));
?>

<!-- Top 4 Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metric">
            <span class="metric-icon"><i class="fa-solid fa-bolt"></i></span>
            <div class="min-w-0">
                <div class="metric-value" style="color: var(--primary);"><?= number_format((int) ($activity['totals']->total_calls ?? 0)) ?></div>
                <div class="metric-label">Total Calls</div>
                <div class="metric-desc">All endpoint hits recorded</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric">
            <span class="metric-icon" style="color:#2ED573; background:rgba(46,213,115,0.12);"><i class="fa-solid fa-clock"></i></span>
            <div class="min-w-0">
                <div class="metric-value text-success"><?= number_format((int) ($activity['totals']->last_24h ?? 0)) ?></div>
                <div class="metric-label">Last 24 Hours</div>
                <div class="metric-desc">Calls in the past day</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric">
            <span class="metric-icon" style="color:#1e90ff; background:rgba(30,144,255,0.12);"><i class="fa-solid fa-calendar-week"></i></span>
            <div class="min-w-0">
                <div class="metric-value" style="color:#1e90ff;"><?= number_format((int) ($activity['totals']->last_7d ?? 0)) ?></div>
                <div class="metric-label">Last 7 Days</div>
                <div class="metric-desc">Calls in the past week</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="metric">
            <span class="metric-icon" style="color:#FFA502; background:rgba(255,165,2,0.12);"><i class="fa-solid fa-network-wired"></i></span>
            <div class="min-w-0">
                <div class="metric-value" style="color:#FFA502;"><?= number_format((int) ($activity['totals']->unique_ips ?? 0)) ?></div>
                <div class="metric-label">Unique IPs</div>
                <div class="metric-desc">Distinct device addresses</div>
            </div>
        </div>
    </div>
</div>

<!-- Endpoint Breakdown: Incoming + Outgoing -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card stat-card glass-card h-100">
            <div class="card-body p-4">
                <div class="section-head">
                    <div class="head-icon" style="color:#2ED573; background:rgba(46,213,115,0.12);"><i class="fa-solid fa-arrow-down-long"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">Incoming Traffic</h5>
                        <small class="text-secondary">Data sent from devices</small>
                    </div>
                    <span class="ms-auto fw-bold fs-5" style="color:#2ED573;"><?= number_format($inTotal) ?></span>
                </div>
                <?php if (empty($incoming)): ?>
                    <p class="text-muted small mb-0">No incoming traffic recorded.</p>
                <?php else: ?>
                    <?php foreach ($incoming as $a): ?>
                        <div class="endpoint-row">
                            <code class="small"><?= esc($a['action']) ?></code>
                            <span class="endpoint-count text-success"><?= number_format((int) $a['cnt']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card stat-card glass-card h-100">
            <div class="card-body p-4">
                <div class="section-head">
                    <div class="head-icon" style="color:#1e90ff; background:rgba(30,144,255,0.12);"><i class="fa-solid fa-arrow-up-long"></i></div>
                    <div>
                        <h5 class="fw-bold mb-0">Outgoing Traffic</h5>
                        <small class="text-secondary">Data fetched by devices</small>
                    </div>
                    <span class="ms-auto fw-bold fs-5" style="color:#1e90ff;"><?= number_format($outTotal) ?></span>
                </div>
                <?php if (empty($outgoing)): ?>
                    <p class="text-muted small mb-0">No outgoing traffic recorded.</p>
                <?php else: ?>
                    <?php foreach ($outgoing as $a): ?>
                        <div class="endpoint-row">
                            <code class="small"><?= esc($a['action']) ?></code>
                            <span class="endpoint-count" style="color:#1e90ff;"><?= number_format((int) $a['cnt']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Calls Table -->
<div class="card stat-card glass-card">
    <div class="card-body p-4">
        <div class="section-head">
            <div class="head-icon"><i class="fa-solid fa-list"></i></div>
            <div>
                <h5 class="fw-bold mb-0">Recent Calls</h5>
                <small class="text-secondary">Latest endpoint hits in detail</small>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped feed-table mb-0">
                <thead>
                    <tr><th style="width: 22%;">When</th><th style="width: 26%;">Action</th><th>Details</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($activity['feed'])): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No calls recorded.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($activity['feed'] as $f): ?>
                    <tr>
                        <td><small><?= format_date_display($f['created_at'] ?? '') ?></small></td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <code class="small"><?= esc($f['action']) ?></code>
                                <small class="text-primary" style="font-size:0.7rem; word-break: break-all;"><?= esc($f['ip'] ?? '-') ?></small>
                            </div>
                        </td>
                        <td>
                            <small><?= esc($f['description'] ?? '') ?></small>
                            <?php if (!empty($f['metadata'])): ?>
                                <div class="feed-meta"><?= esc(json_encode(json_decode($f['metadata'], true), JSON_UNESCAPED_SLASHES)) ?></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>