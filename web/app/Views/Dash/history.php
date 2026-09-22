<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Upload History - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .cat-badge {
        font-size: 0.65rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 4px;
        white-space: nowrap;
        display: inline-block;
    }
    .classify-bar {
        height: 6px;
        border-radius: 3px;
        background: #e9ecef;
        overflow: hidden;
        min-width: 80px;
    }
    .classify-bar-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.5s ease;
    }
    .summary-stat {
        padding: 16px 20px;
        border-radius: 4px;
        background: var(--card-bg);
        border: 1px solid var(--card-border);
    }
    .history-table th {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        font-weight: 600;
        border-bottom: 2px solid var(--card-border);
        padding: 12px 8px;
        white-space: nowrap;
    }
    .history-table td {
        padding: 14px 8px;
        vertical-align: middle;
        border-bottom: 1px solid var(--card-border);
        font-size: 0.9rem;
    }
    .history-table tr:hover td {
        background: rgba(93, 95, 239, 0.02);
    }
    .batch-uuid {
        font-family: 'JetBrains Mono', 'Courier New', monospace;
        font-size: 0.65rem;
        color: var(--text-muted);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-timeline me-2"></i>Data &amp; ML History</h2>
        <p class="text-secondary mb-0">Your upload history and ML job runs.</p>
    </div>
    <div>
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2">
            <i class="fa-solid fa-layer-group me-1"></i>
            <?= count($batches) ?> Batches
        </span>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $cs = currency_symbol(); ?>

<!-- Top nav (shared across History sub-pages, like the Blocklist page) -->
<div class="card glass-card border-0 shadow-sm mb-4">
    <div class="card-body py-2 px-4">
        <ul class="nav nav-tabs border-0" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?= ($active_tab ?? 'uploads') === 'uploads' ? 'active' : '' ?>" href="<?= base_url('dashboard/history') ?>">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload History
                    <span class="badge bg-secondary ms-1"><?= count($batches) ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($active_tab ?? 'uploads') === 'jobs' ? 'active' : '' ?>" href="<?= base_url('dashboard/history/jobs') ?>">
                    <i class="fa-solid fa-microchip me-1"></i> ML Jobs
                    <span class="badge bg-info ms-1"><?= count($jobs ?? []) ?></span>
                </a>
            </li>
        </ul>
    </div>
</div>

<?php if (($active_tab ?? 'uploads') === 'jobs'): ?>
    <?php include __DIR__ . '/_ml_jobs.php'; ?>
<?php else: ?>

<?php if (!empty($batches)) : ?>

<!-- ML Summary Row Accordion -->
<div class="accordion mb-4" id="mlSummaryAccordion">
    <div class="accordion-item border-0 shadow-sm glass-card">
        <h2 class="accordion-header" id="headingSummary">
            <button class="accordion-button collapsed fw-bold px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSummary" aria-expanded="false" aria-controls="collapseSummary" style="background: transparent; color: var(--primary); outline: none; box-shadow: none;">
                <div class="d-flex justify-content-between align-items-center w-100 pe-3 flex-wrap gap-2">
                    <div>
                        <i class="fa-solid fa-microchip me-2"></i>ML Analysis Summary
                        <span class="text-secondary fw-normal small ms-2 d-none d-sm-inline">Stats across all your uploads, produced by the ML job.</span>
                    </div>
                    <?php if (!empty($stats['newest']) || !empty($stats['oldest'])): ?>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 font-monospace" style="font-size:0.75rem;">
                            <i class="fa-solid fa-calendar me-1"></i>
                            <?= !empty($stats['oldest']) ? esc(format_date_display($stats['oldest'])) : '—' ?> &rarr; <?= !empty($stats['newest']) ? esc(format_date_display($stats['newest'])) : '—' ?>
                        </span>
                    <?php endif; ?>
                </div>
            </button>
        </h2>
        <div id="collapseSummary" class="accordion-collapse collapse" aria-labelledby="headingSummary" data-bs-parent="#mlSummaryAccordion">
            <div class="accordion-body p-4 border-top" style="border-color: rgba(0,0,0,0.05) !important;">
                <div class="row g-3">
                    <div class="col-6 col-lg-2">
                        <div class="summary-stat text-center">
                            <i class="fa-solid fa-message text-muted mb-2 fs-5"></i>
                            <div class="fw-bold fs-4"><?= number_format($stats['all_sms']) ?></div>
                            <div class="text-muted small text-uppercase" style="font-weight:600; letter-spacing:0.5px;">All SMS</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <div class="summary-stat text-center">
                            <i class="fa-solid fa-circle-check text-success mb-2 fs-5"></i>
                            <div class="fw-bold fs-4 text-success"><?= number_format($stats['finance_sms']) ?></div>
                            <div class="text-muted small text-uppercase" style="font-weight:600; letter-spacing:0.5px;">Good SMS</div>
                            <div class="text-muted small">finance-related</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <div class="summary-stat text-center">
                            <i class="fa-solid fa-circle-xmark text-danger mb-2 fs-5"></i>
                            <div class="fw-bold fs-4 text-danger"><?= number_format($stats['non_finance_sms']) ?></div>
                            <div class="text-muted small text-uppercase" style="font-weight:600; letter-spacing:0.5px;">Bad SMS</div>
                            <div class="text-muted small">non-finance</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <div class="summary-stat text-center">
                            <i class="fa-solid fa-circle-question text-warning mb-2 fs-5"></i>
                            <div class="fw-bold fs-4 text-warning"><?= number_format($stats['unclassified']) ?></div>
                            <div class="text-muted small text-uppercase" style="font-weight:600; letter-spacing:0.5px;">Unclassified</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <div class="summary-stat text-center">
                            <i class="fa-solid fa-money-bill-transfer text-info mb-2 fs-5"></i>
                            <div class="fw-bold fs-4 text-info"><?= number_format($stats['transactions']) ?></div>
                            <div class="text-muted small text-uppercase" style="font-weight:600; letter-spacing:0.5px;">Transactions</div>
                            <div class="text-muted small"><?= $cs ?><?= number_format($stats['total_value'], 0) ?></div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-2">
                        <div class="summary-stat text-center">
                            <i class="fa-solid fa-building-columns mb-2 fs-5" style="color:#6f42c1;"></i>
                            <div class="fw-bold fs-4" style="color:#6f42c1;"><?= number_format($stats['banks']) ?></div>
                            <div class="text-muted small text-uppercase" style="font-weight:600; letter-spacing:0.5px;">Banks</div>
                            <div class="text-muted small">distinct</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <div class="summary-stat">
                            <div class="text-muted small text-uppercase mb-2" style="font-weight:600; letter-spacing:0.5px;"><i class="fa-solid fa-users me-2"></i>Senders</div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span><i class="fa-solid fa-address-book text-muted me-1 small"></i>All senders</span><span class="fw-bold"><?= number_format($stats['all_senders']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-success"><i class="fa-solid fa-circle-check text-success me-1 small"></i>Finance senders</span><span class="fw-bold text-success"><?= number_format($stats['finance_senders']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-danger"><i class="fa-solid fa-circle-xmark text-danger me-1 small"></i>Non-finance senders</span><span class="fw-bold text-danger"><?= number_format($stats['non_finance_senders']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-stat">
                            <div class="text-muted small text-uppercase mb-2" style="font-weight:600; letter-spacing:0.5px;"><i class="fa-solid fa-right-left me-2"></i>Direction</div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span><i class="fa-solid fa-arrow-up-from-bracket text-muted me-1 small"></i>Outgoing (sent)</span><span class="fw-bold"><?= number_format($stats['sent']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span><i class="fa-solid fa-arrow-down-to-bracket text-muted me-1 small"></i>Incoming (received)</span><span class="fw-bold"><?= number_format($stats['received']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span><i class="fa-solid fa-question text-muted me-1 small"></i>Undetermined</span><span class="fw-bold"><?= number_format($stats['none']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-stat">
                            <div class="text-muted small text-uppercase mb-2" style="font-weight:600; letter-spacing:0.5px;"><i class="fa-solid fa-chart-pie me-2"></i>Totals</div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span><i class="fa-solid fa-wallet text-muted me-1 small"></i>Total SMS value</span><span class="fw-bold"><?= $cs ?><?= number_format($stats['total_value'], 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span><i class="fa-solid fa-upload text-muted me-1 small"></i>Batches uploaded</span><span class="fw-bold"><?= count($batches) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span><i class="fa-solid fa-building-columns text-muted me-1 small"></i>Banks</span><span class="fw-bold"><?= number_format($stats['banks']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="summary-stat text-center">
            <i class="fa-solid fa-message text-muted mb-2"></i>
            <div class="fw-bold fs-4"><?= number_format($total_sms_all) ?></div>
            <div class="text-muted small text-uppercase" style="font-weight: 600; letter-spacing: 0.5px;">Total SMS</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-stat text-center">
            <i class="fa-solid fa-brain text-success mb-2"></i>
            <div class="fw-bold fs-4 text-success"><?= number_format($total_classified_all) ?></div>
            <div class="text-muted small text-uppercase" style="font-weight: 600; letter-spacing: 0.5px;">Classified</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-stat text-center">
            <i class="fa-solid fa-wallet text-primary mb-2"></i>
            <div class="fw-bold fs-4 text-primary"><?= $cs ?> <?= number_format($total_amount_all, 0) ?></div>
            <div class="text-muted small text-uppercase" style="font-weight: 600; letter-spacing: 0.5px;">Total Value</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="summary-stat text-center">
            <i class="fa-solid fa-chart-line mb-2" style="color:#6f42c1;"></i>
            <div class="fw-bold fs-4" style="color:#6f42c1;"><?= $total_sms_all > 0 ? round(($total_classified_all / $total_sms_all) * 100) : 0 ?>%</div>
            <div class="text-muted small text-uppercase" style="font-weight: 600; letter-spacing: 0.5px;">LLM Coverage</div>
        </div>
    </div>
</div>

<!-- Batches Table -->
<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table history-table mb-0">
                <thead>
                    <tr>
                        <th><i class="fa-solid fa-calendar me-1"></i>Date</th>
                        <th style="min-width: 100px;"><i class="fa-solid fa-message me-1"></i>SMS</th>
                        <th style="min-width: 120px;"><i class="fa-solid fa-brain me-1"></i>Classification</th>
                        <th><i class="fa-solid fa-tags me-1"></i>Categories</th>
                        <th><i class="fa-solid fa-coins me-1"></i>Amount</th>
                        <th><i class="fa-solid fa-handshake me-1"></i>Parties</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $catPalette = [
                        'Unclassified'    => ['bg' => '#e9ecef', 'text' => '#6c757d', 'icon' => 'fa-question'],
                        'Mobile Money'    => ['bg' => '#d1e7dd', 'text' => '#0f5132', 'icon' => 'fa-mobile-screen-button'],
                        'Payments/Govt'   => ['bg' => '#cfe2ff', 'text' => '#084298', 'icon' => 'fa-landmark'],
                        'Bank Transfer'   => ['bg' => '#cff4fc', 'text' => '#055160', 'icon' => 'fa-building-columns'],
                        'Fintech'         => ['bg' => '#fff3cd', 'text' => '#664d03', 'icon' => 'fa-bolt'],
                        'Airtime'         => ['bg' => '#f8d7da', 'text' => '#842029', 'icon' => 'fa-phone'],
                        'Notification'    => ['bg' => '#e2e3e5', 'text' => '#41464b', 'icon' => 'fa-bell'],
                        'Salary'          => ['bg' => '#d1e7dd', 'text' => '#0f5132', 'icon' => 'fa-money-bill-wave'],
                        'Shopping'        => ['bg' => '#f8d7da', 'text' => '#842029', 'icon' => 'fa-cart-shopping'],
                        'Food & Drink'    => ['bg' => '#fff3cd', 'text' => '#664d03', 'icon' => 'fa-utensils'],
                        'Transport'       => ['bg' => '#cff4fc', 'text' => '#055160', 'icon' => 'fa-car'],
                        'Utilities'       => ['bg' => '#e2e3e5', 'text' => '#41464b', 'icon' => 'fa-plug'],
                        'Entertainment'   => ['bg' => '#f0d6ff', 'text' => '#5a189a', 'icon' => 'fa-film'],
                    ];
                    ?>
                    <?php foreach ($batches as $b) : 
                        $ts = strtotime($b['created_at']);
                        $dateStr = $ts ? date('M j, Y', $ts) : 'N/A';
                        $timeStr = $ts ? date('h:i A', $ts) : '';
                        $classifyPct = $b['total'] > 0 ? round(($b['classified'] / $b['total']) * 100) : 0;
                        $barColor = $classifyPct >= 100 ? '#198754' : ($classifyPct > 0 ? '#ffc107' : '#e9ecef');
                    ?>
                    <tr>
                        <td class="text-nowrap">
                            <div class="fw-semibold"><?= $dateStr ?></div>
                            <div class="text-muted small"><?= $timeStr ?></div>
                        </td>
                        <td>
                            <div class="fw-semibold"><?= number_format($b['total']) ?></div>
                            <div class="batch-uuid"><?= htmlspecialchars(substr($b['uuid'], 0, 12)) ?>…</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="classify-bar flex-grow-1">
                                    <div class="classify-bar-fill" style="width: <?= $classifyPct ?>%; background: <?= $barColor ?>;"></div>
                                </div>
                                <span class="small fw-semibold text-nowrap" style="font-size: 0.75rem;">
                                    <?= $b['classified'] ?>/<?= $b['total'] ?>
                                </span>
                            </div>
                            <?php if ($classifyPct > 0 && $classifyPct < 100) : ?>
                                <div class="small text-warning mt-1" style="font-size: 0.65rem;">
                                    <i class="fa-solid fa-microchip me-1"></i>LLM running…
                                </div>
                            <?php elseif ($classifyPct >= 100) : ?>
                                <div class="small text-success mt-1" style="font-size: 0.65rem;">
                                    <i class="fa-solid fa-check-circle me-1"></i>Complete
                                </div>
                            <?php else : ?>
                                <div class="small text-muted mt-1" style="font-size: 0.65rem;">
                                    <i class="fa-solid fa-clock me-1"></i>Pending
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $cats = $b['categories'];
                            arsort($cats);
                            $shown = 0;
                            foreach ($cats as $name => $count) : 
                                if ($shown >= 3) break;
                                $pal = $catPalette[$name] ?? ['bg' => '#f8f9fa', 'text' => '#212529', 'icon' => 'fa-tag'];
                                $shown++;
                            ?>
                                <span class="cat-badge me-1 mb-1" style="background: <?= $pal['bg'] ?>; color: <?= $pal['text'] ?>;">
                                    <i class="fa-solid <?= $pal['icon'] ?> me-1"></i><?= $name ?> <?= $count ?>
                                </span>
                            <?php endforeach; ?>
                            <?php if (count($cats) > 3) : ?>
                                <span class="cat-badge" style="background: #e9ecef; color: #6c757d;">+<?= count($cats) - 3 ?> more</span>
                            <?php endif; ?>
                        </td>
                        <td class="fw-semibold text-nowrap">
                            <?= $cs ?> <?= number_format($b['amount'], 2) ?>
                        </td>
                        <td class="text-nowrap">
                            <?= number_format($b['counterparties'] ?? 0) ?>
                        </td>
                        <td>
                            <a href="<?= base_url('dashboard/transactions') ?>"
                               class="btn btn-sm btn-outline-primary rounded-pill px-3"
                               title="View all transactions">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else : ?>
<div class="card glass-card text-center p-5 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-center mx-auto mb-4"
             style="width: 80px; height: 80px; border-radius: 4px; background: var(--bg-color);">
            <i class="fa-solid fa-timeline fs-1 text-muted"></i>
        </div>
        <h5 class="fw-bold">No upload history yet</h5>
        <p class="text-secondary">Your device hasn't uploaded any data. Make sure your Android app is configured and linked.</p>
        <a href="<?= url_to('Info::index') ?>" class="btn btn-primary rounded-pill px-4 mt-2">
            <i class="fa-solid fa-gear me-2"></i> Configure App
        </a>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?= $this->endSection() ?>
