<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Dashboard - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .summary-card {
        border: none;
        border-radius: 4px;
        color: white;
        transition: transform 0.3s ease;
        overflow: hidden;
    }
    
    .summary-card:hover {  transform: translateY(-5px); }

    .card-purple { background: linear-gradient(135deg, #6C5CE7 0%, #4834D4 100%); }
    .card-blue { background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%); }
    .card-dark { background: linear-gradient(135deg, #34495E 0%, #2C3E50 100%); }

    .metric-box {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(5px);
        border-radius: 4px;
        padding: 1rem 0.5rem;
    }
    
    .metric-icon { font-size: 2rem; }
    .recent-activity-badge { background: rgba(0,0,0,0.1); border-radius: 4px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">Dashboard Overview</h2>
        <p class="text-secondary mb-0">High-level summary of your financial data (Last 30 Days).</p>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <?php if (!empty($linked_devices)): ?>
        <form method="get" action="<?= base_url('dashboard') ?>" class="me-2 mb-0">
            <div class="input-group input-group-sm rounded-pill shadow-sm overflow-hidden border border-primary-subtle" style="background: var(--card-bg);">
                <span class="input-group-text bg-transparent border-0 pe-1"><i class="fa-solid fa-mobile-screen text-primary"></i></span>
                <select name="device" class="form-select border-0 shadow-none pe-4 font-monospace small" style="background-color: transparent; cursor: pointer; color: var(--text-main);" onchange="this.form.submit()">
                    <option value="" style="background: var(--card-bg); color: var(--text-main);">All Devices</option>
                    <?php foreach ($linked_devices as $ld): ?>
                        <option value="<?= htmlspecialchars($ld->device_token) ?>" <?= (($device_filter ?? '') === $ld->device_token) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ld->device_name ?: substr($ld->device_token, 0, 8)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        <?php endif; ?>

        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm focus-ring" data-bs-toggle="modal" data-bs-target="#linkDeviceModal">
            <i class="fa-solid fa-link"></i> Link Device
        </button>

        <button class="btn btn-success btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#analyzeConfirmModal">
            <i class="fa-solid fa-microchip me-1"></i> Analyze
        </button>
        <a href="<?= base_url('dashboard/search') ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
            <i class="fa-solid fa-magnifying-glass me-1"></i> Search
        </a>
    </div>
</div>

<!-- Link Device Modal -->
<div class="modal fade" id="linkDeviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-mobile-screen-button text-primary me-2"></i>Link a Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('dashboard/device/link') ?>" method="post">
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4">Link an Android device running the M-Pesa SMS sync app using its unique Device Token (found in API settings).</p>
                    
                    <div class="mb-3">
                        <label class="text-secondary small fw-bold text-uppercase">Device Token / SMS Owner</label>
                        <input type="text" class="form-control form-control-lg border-0 font-monospace" style="background: var(--bg-color); color: var(--text-main);" name="device_token" placeholder="Paste device token here" required>
                    </div>

                    <div class="mb-2">
                        <label class="text-secondary small fw-bold text-uppercase">Device Name (Optional)</label>
                        <input type="text" class="form-control border-0" style="background: var(--bg-color); color: var(--text-main);" name="device_name" placeholder="e.g. Galaxy S23">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 rounded-bottom-4" style="background: var(--card-bg);">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Save Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Analyze Confirm Modal -->
<div class="modal fade" id="analyzeConfirmModal" tabindex="-1" aria-labelledby="analyzeConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="analyzeConfirmModalLabel"><i class="fa-solid fa-microchip text-success me-2"></i>Run Rules Analysis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small">This action executes your custom user-defined category rules and regex patterns against all new/unprocessed transactions instantly.</p>
                <p class="text-muted small mb-0">It helps group your spending accurately before triggering the deep LLM classifier.</p>
            </div>
            <div class="modal-footer border-0 pt-0 rounded-bottom-4" style="background: var(--card-bg);">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="runAnalysis" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Proceed Anyway</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $cs = currency_symbol(); ?>

<?php
// Sort alerts so Danger is top
$alerts = $smart_alerts ?? [];
usort($alerts, fn($a, $b) => $a['level'] === 'danger' ? -1 : 1);
?>

<?php if (!empty($alerts)): ?>
<div class="row mb-4">
    <div class="col-12">
        <h6 class="fw-bold mb-3 text-secondary text-uppercase small"><i class="fa-solid fa-robot me-2"></i>Intelligent Alerts</h6>
        <?php foreach ($alerts as $al): ?>
            <div class="alert alert-<?= $al['level'] ?> shadow-sm border-0 rounded-4 d-flex align-items-center gap-3 py-3 mb-2">
                <?php if ($al['type'] === 'low_balance'): ?>
                    <i class="fa-solid fa-wallet fa-2x"></i>
                <?php elseif ($al['type'] === 'unusual_activity'): ?>
                    <i class="fa-solid fa-shield-halved fa-2x"></i>
                <?php elseif ($al['type'] === 'fuliza_index'): ?>
                    <i class="fa-solid fa-percent fa-2x"></i>
                <?php endif; ?>
                <div>
                    <h6 class="fw-bold mb-1 text-dark"><?= $al['title'] ?></h6>
                    <div class="small text-dark"><?= $al['message'] ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($budget_alerts)): 
    $overLimit = array_filter($budget_alerts, fn($b) => $b['over_limit']);
    $nearLimit = array_filter($budget_alerts, fn($b) => !$b['over_limit'] && $b['percentage'] >= 80);
?>
    <?php if (!empty($overLimit)): ?>
    <div class="alert alert-danger shadow-sm border-0 rounded-4 d-flex align-items-center gap-3 mb-4">
        <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
        <div>
            <h6 class="fw-bold mb-1">Budget Exceeded!</h6>
            <div class="small">
                <?php foreach ($overLimit as $b): ?>
                    <span class="me-3"><strong><?= htmlspecialchars($b['label'] ?: $b['category']) ?>:</strong> <?= number_format($b['percentage'], 0) ?>% used</span>
                <?php endforeach; ?>
            </div>
        </div>
        <a href="<?= base_url('dashboard/budget') ?>" class="btn btn-sm btn-danger ms-auto rounded-pill px-3 fw-bold">View Budget</a>
    </div>
    <?php endif; ?>

    <?php if (!empty($nearLimit) && empty($overLimit)): ?>
    <div class="alert alert-warning shadow-sm border-0 rounded-4 d-flex align-items-center gap-3 mb-4">
        <i class="fa-solid fa-bell fa-2x text-warning"></i>
        <div>
            <h6 class="fw-bold mb-1 text-dark">Approaching Budget Limit</h6>
            <div class="small text-dark">
                <?php foreach ($nearLimit as $b): ?>
                    <span class="me-3"><strong><?= htmlspecialchars($b['label'] ?: $b['category']) ?>:</strong> <?= number_format($b['percentage'], 0) ?>% used</span>
                <?php endforeach; ?>
            </div>
        </div>
        <a href="<?= base_url('dashboard/budget') ?>" class="btn btn-sm btn-warning text-dark ms-auto rounded-pill px-3 fw-bold">Manage Budget</a>
    </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row g-4 mb-4" id="dashboardGrid">

    <!-- General Summary Card -->
    <div class="col-md-3" data-id="finance_overview">
        <div class="card summary-card card-purple h-100 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0 opacity-75">Finance Overview</h5>
                    <i class="fa-solid fa-wallet metric-icon opacity-50"></i>
                </div>
                <div class="metric-box text-center mb-0">
                    <h2 class="fw-bold mb-1"><?= $cs ?> <?= number_format($metrics['current_balance'], 2) ?></h2>
                    <p class="small mb-0 opacity-75">Current Balance</p>
                </div>
                <div class="mt-3 small">
                    <div class="d-flex justify-content-between opacity-75 mb-1">
                        <span>Fuliza Limit:</span>
                        <span class="fw-bold"><?= $cs ?> <?= number_format($metrics['fuliza_balance'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Spending Summary Card -->
    <div class="col-md-3" data-id="spending_summary">
        <div class="card summary-card card-blue h-100 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0 opacity-75">Spending (30 Days)</h5>
                    <i class="fa-solid fa-paper-plane metric-icon opacity-50"></i>
                </div>
                <div class="metric-box text-center mb-0">
                    <h2 class="fw-bold mb-1"><?= $cs ?> <?= number_format($metrics['total_sent_30'], 2) ?></h2>
                    <p class="small mb-0 opacity-75">Total Outflow</p>
                </div>
                <div class="mt-3 small">
                    <div class="d-flex justify-content-between opacity-75 mb-1">
                        <span>Daily Avg:</span>
                        <span class="fw-bold text-white"><?= $cs ?> <?= number_format($metrics['daily_avg_spend'] ?? 0, 0) ?></span>
                    </div>
                    <div class="d-flex justify-content-between opacity-75">
                        <span>Max Trans:</span>
                        <span class="fw-bold text-white"><?= $cs ?> <?= number_format($metrics['max_transaction'] ?? 0, 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Income Summary Card -->
    <div class="col-md-3" data-id="income_summary">
        <div class="card summary-card card-dark h-100 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0 opacity-75">Income (30 Days)</h5>
                    <i class="fa-solid fa-hand-holding-dollar metric-icon opacity-50"></i>
                </div>
                <div class="metric-box text-center mb-0">
                    <h2 class="fw-bold mb-1"><?= $cs ?> <?= number_format($metrics['total_received_30'], 2) ?></h2>
                    <p class="small mb-0 opacity-75">Total Inflow</p>
                </div>
                <div class="mt-3 small">
                    <div class="d-flex justify-content-between opacity-75 mb-1">
                        <span>Banks/Other:</span>
                        <span class="fw-bold"><?= $cs ?> <?= number_format($received_summary['banks'], 0) ?></span>
                    </div>
                    <div class="d-flex justify-content-between opacity-75">
                        <span>M-Shwari/KCB:</span>
                        <span class="fw-bold"><?= $cs ?> <?= number_format($received_summary['mshwari_kcb'], 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Financial Health Score Card -->
    <div class="col-md-3" data-id="health_score">
        <div class="card summary-card border border-2 opacity-100 h-100 shadow-sm" style="border-color: <?= $health_score['color'] ?>50 !important; background: linear-gradient(135deg, <?= $health_score['color'] ?>20 0%, transparent 100%);">
            <div class="card-body p-4 text-dark">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0 fw-bold" style="color: <?= $health_score['color'] ?>;">Health Score</h5>
                    <i class="fa-solid fa-heart-pulse metric-icon" style="color: <?= $health_score['color'] ?>80;"></i>
                </div>
                
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3 position-relative" style="width: 70px; height: 70px;">
                        <svg viewBox="0 0 36 36" class="circular-chart" style="width: 100%; height: 100%;">
                            <path class="circle-bg" stroke="#e6e6e6" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="circle" stroke="<?= $health_score['color'] ?>" stroke-width="3" fill="none" stroke-dasharray="<?= $health_score['score'] ?>, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <text x="18" y="20.35" fill="currentColor" text-anchor="middle" font-size="10" font-weight="bold" font-family="Outfit"><?= $health_score['score'] ?></text>
                        </svg>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark"><?= $health_score['score'] >= 80 ? 'Excellent' : ($health_score['score'] >= 50 ? 'Fair' : 'Needs Action') ?></h4>
                        <p class="small text-muted mb-0">Based on last 60 days</p>
                    </div>
                </div>
                
                <div class="mt-2 small opacity-75">
                    <?php if (!empty($health_score['tips'])): ?>
                    <ul class="list-unstyled mb-0">
                        <li><i class="fa-solid fa-check text-success me-1"></i> <?= htmlspecialchars($health_score['tips'][0]) ?></li>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php if (!empty($top_counterparties)): ?>
    <div class="col-md-12" data-id="top_counterparties">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-0 py-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-users me-2 text-primary"></i>Top Spending &amp; Receiving Entities</h4>
                    <p class="text-secondary small mb-0">Your most frequent transaction partners and their total volumes.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                    <i class="fa-solid fa-bolt me-1"></i> Interactive Insights
                </span>
            </div>
            <div class="card-body px-4 pb-4 pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">
                                <th>Entity Partner</th>
                                <th>Direction Flow</th>
                                <th class="text-center">Transactions</th>
                                <th class="text-end">Total Volume</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $colors = ['#438EB9', '#2ED573', '#FFA502', '#FF4757', '#1E90FF'];
                            foreach ($top_counterparties as $index => $entity): 
                                $color = $colors[$index % count($colors)];
                                $isIncoming = in_array(strtolower($entity->sms_direction ?? ''), ['incoming', 'received', 'money_in', 'in']);
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px; background-color: <?= $color ?>15; border: 1px solid <?= $color ?>30;">
                                            <i class="fa-solid <?= strpos(strtolower($entity->counterparty), 'bank') !== false ? 'fa-building-columns' : 'fa-user' ?>" style="color: <?= $color ?>;"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark small"><?= htmlspecialchars($entity->counterparty) ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($isIncoming): ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size:0.7rem;">
                                            <i class="fa-solid fa-arrow-down-long me-1"></i> Money In
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1" style="font-size:0.7rem;">
                                            <i class="fa-solid fa-arrow-up-long me-1"></i> Money Out
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center small fw-semibold text-secondary">
                                    <?= $entity->trans_count ?> transactions
                                </td>
                                <td class="text-end fw-bold">
                                    <span class="<?= $isIncoming ? 'text-success' : 'text-danger' ?>">
                                        <?= $isIncoming ? '+' : '-' ?><?= $cs ?> <?= number_format($entity->total_amount, 2) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('dashboard/transactions?search=' . urlencode($entity->counterparty)) ?>" 
                                       class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-magnifying-glass me-1"></i> Trace
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>

            </div>
        </div>
    </div>

<!-- SMS Detail Modal -->
<div class="modal fade" id="smsDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Transaction Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="text-secondary small fw-bold text-uppercase">Time</label>
                    <p id="modal-sms-time" class="mb-0 fw-semibold"></p>
                </div>
                <div>
                    <label class="text-secondary small fw-bold text-uppercase">Message Content</label>
                    <div class="p-3 bg-light rounded-3 mt-1" id="modal-sms-body" style="white-space: pre-wrap; font-size: 0.9rem;"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal logic (Native JS)
        const smsModalEl = document.getElementById('smsDetailModal');
        const smsModal = new bootstrap.Modal(smsModalEl);
        const modalTime = document.getElementById('modal-sms-time');
        const modalBody = document.getElementById('modal-sms-body');

        // Dynamic binding for table buttons
        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.view-sms-btn');
            if (btn) {
                modalTime.textContent = btn.getAttribute('data-time');
                modalBody.innerHTML = btn.getAttribute('data-body').replace(/\n/g, '<br>');
                smsModal.show();
            }
        });
        // Analysis button logic
        const analysisBtn = document.getElementById('runAnalysis');
        if (analysisBtn) {
            analysisBtn.addEventListener('click', function() {
                // Dismiss the confirmation modal
                const modalEl = document.getElementById('analyzeConfirmModal');
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                const originalText = analysisBtn.innerHTML;
                analysisBtn.disabled = true;
                analysisBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Analysing...';
                
                fetch('<?= base_url('dashboard/analyse') ?>')
                    .then(response => {
                        const contentType = response.headers.get('content-type');
                        if (!response.ok || !contentType || !contentType.includes('application/json')) {
                            return response.text().then(text => {
                                throw new Error(`Server returned ${response.status}: ${text.substring(0, 100)}...`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            showAlert('Analysis Complete', data.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert('Analysis Error', data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Analysis Error:', error);
                        showAlert('Analysis Failed', error.message, 'danger');
                    })
                    .finally(() => {
                        analysisBtn.disabled = false;
                        analysisBtn.innerHTML = originalText;
                    });
            });
        }
        
        // SortableJS Logic for Dashboard Widgets
        const grid = document.getElementById('dashboardGrid');
        if (grid && typeof Sortable !== 'undefined') {
            const sortable = Sortable.create(grid, {
                animation: 150,
                handle: '.card', // Drag handles are the cards themselves
                ghostClass: 'opacity-50',
                onEnd: function() {
                    const order = sortable.toArray();
                    localStorage.setItem('dashboard_order', JSON.stringify(order));
                }
            });

            // Apply saved layout
            const savedOrder = JSON.parse(localStorage.getItem('dashboard_order'));
            if (savedOrder && savedOrder.length > 0) {
                sortable.sort(savedOrder);
            }
        }
    });
</script>
<?= $this->endSection() ?>
