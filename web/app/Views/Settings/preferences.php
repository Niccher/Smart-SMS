<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Preferences - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .setting-item { padding: 1rem 0; border-bottom: 1px solid #f0f0f0; }
    .setting-item:last-child { border-bottom: none; }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-sliders me-2"></i>Preferences</h2>
        <p class="text-secondary mb-0">Customize your dashboard experience.</p>
    </div>
    <a href="<?= base_url('dashboard/settings') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Settings
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (session()->has('message')) : ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session('message') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);">Display & Regional</h5>
                <form action="<?= base_url('dashboard/settings/preferences/save') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="setting-item">
                        <label class="fw-bold text-dark mb-1">Currency</label>
                        <select class="form-select" name="currency">
                            <option value="KES" <?= $settings['currency'] === 'KES' ? 'selected' : '' ?>>KES - Kenyan Shilling</option>
                            <option value="USD" <?= $settings['currency'] === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                            <option value="EUR" <?= $settings['currency'] === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                            <option value="GBP" <?= $settings['currency'] === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
                            <option value="TZS" <?= $settings['currency'] === 'TZS' ? 'selected' : '' ?>>TZS - Tanzanian Shilling</option>
                            <option value="UGX" <?= $settings['currency'] === 'UGX' ? 'selected' : '' ?>>UGX - Ugandan Shilling</option>
                            <option value="RWF" <?= $settings['currency'] === 'RWF' ? 'selected' : '' ?>>RWF - Rwandan Franc</option>
                        </select>
                        <small class="text-muted">This affects how amounts are displayed throughout the dashboard.</small>
                    </div>

                    <div class="setting-item">
                        <label class="fw-bold text-dark mb-1">Date Format</label>
                        <select class="form-select" name="date_format">
                            <option value="Y-m-d" <?= $settings['date_format'] === 'Y-m-d' ? 'selected' : '' ?>>2026-03-15 (Y-m-d)</option>
                            <option value="d/m/Y" <?= $settings['date_format'] === 'd/m/Y' ? 'selected' : '' ?>>15/03/2026 (d/m/Y)</option>
                            <option value="m/d/Y" <?= $settings['date_format'] === 'm/d/Y' ? 'selected' : '' ?>>03/15/2026 (m/d/Y)</option>
                            <option value="d M Y" <?= $settings['date_format'] === 'd M Y' ? 'selected' : '' ?>>15 Mar 2026</option>
                            <option value="M d, Y" <?= $settings['date_format'] === 'M d, Y' ? 'selected' : '' ?>>Mar 15, 2026</option>
                        </select>
                        <small class="text-muted">How dates appear in transactions, reports, and charts.</small>
                    </div>

                    <div class="setting-item">
                        <label class="fw-bold text-dark mb-1">Time Format</label>
                        <select class="form-select" name="time_format">
                            <option value="H:i" <?= $settings['time_format'] === 'H:i' ? 'selected' : '' ?>>14:30 (24-hour)</option>
                            <option value="h:i A" <?= $settings['time_format'] === 'h:i A' ? 'selected' : '' ?>>02:30 PM (12-hour)</option>
                        </select>
                    </div>

                    <hr class="my-4">
                    <h5 class="fw-bold mb-4" style="color: var(--primary);">Budget Defaults</h5>

                    <div class="setting-item">
                        <label class="fw-bold text-dark mb-1">Default Budget Period</label>
                        <select class="form-select" name="default_budget_period">
                            <option value="monthly" <?= $settings['default_budget_period'] === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="weekly" <?= $settings['default_budget_period'] === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                        </select>
                    </div>

                    <div class="setting-item">
                        <label class="fw-bold text-dark mb-1">Budget Alert Threshold (%)</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="range" class="form-range w-50" name="budget_alert_threshold"
                                   min="50" max="100" step="5"
                                   value="<?= $settings['budget_alert_threshold'] ?? 80 ?>"
                                   oninput="document.getElementById('threshold_val').textContent = this.value + '%'">
                            <span id="threshold_val" class="fw-bold" style="color: var(--primary);"><?= $settings['budget_alert_threshold'] ?? 80 ?>%</span>
                        </div>
                        <small class="text-muted">Alert when spending reaches this percentage of the budget limit.</small>
                    </div>

                    <hr class="my-4">
                    <h5 class="fw-bold mb-4" style="color: var(--primary);">Export Defaults</h5>

                    <div class="setting-item">
                        <label class="fw-bold text-dark mb-1">Default Export Format</label>
                        <select class="form-select" name="export_default_format">
                            <option value="csv" <?= $settings['export_default_format'] === 'csv' ? 'selected' : '' ?>>CSV</option>
                            <option value="json" <?= $settings['export_default_format'] === 'json' ? 'selected' : '' ?>>JSON</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);"><i class="fa-solid fa-grip me-2"></i>Dashboard Widgets</h5>
                <p class="text-muted small mb-3">Choose which widgets appear on your home dashboard.</p>
                <form action="<?= base_url('dashboard/settings/preferences/save') ?>" method="POST">
                    <?= csrf_field() ?>
                    <?php $widgets = json_decode($settings['dashboard_widgets'] ?? '[]', true) ?: []; ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="dashboard_widgets[]" value="metrics" id="w_metrics"
                                    <?= in_array('metrics', $widgets) || empty($widgets) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="w_metrics">Metrics Cards</label>
                                <small class="d-block text-muted">Total sent, received, transaction count</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="dashboard_widgets[]" value="recent" id="w_recent"
                                    <?= in_array('recent', $widgets) || empty($widgets) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="w_recent">Recent Transactions</label>
                                <small class="d-block text-muted">Last 10 transactions</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="dashboard_widgets[]" value="counterparties" id="w_counterparties"
                                    <?= in_array('counterparties', $widgets) || empty($widgets) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="w_counterparties">Top Counterparties</label>
                                <small class="d-block text-muted">Frequent senders and recipients</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="dashboard_widgets[]" value="budget_alerts" id="w_budget"
                                    <?= in_array('budget_alerts', $widgets) || empty($widgets) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="w_budget">Budget Alerts</label>
                                <small class="d-block text-muted">Budget progress and warnings</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="dashboard_widgets[]" value="smart_alerts" id="w_smart"
                                    <?= in_array('smart_alerts', $widgets) || empty($widgets) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="w_smart">Smart Alerts</label>
                                <small class="d-block text-muted">Low balance, unusual activity, Fuliza alerts</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="dashboard_widgets[]" value="trends" id="w_trends"
                                    <?= in_array('trends', $widgets) || empty($widgets) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="w_trends">Spending Trends</label>
                                <small class="d-block text-muted">Month-over-month spending comparison</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="dashboard_widgets[]" value="health_score" id="w_health"
                                    <?= in_array('health_score', $widgets) || empty($widgets) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="w_health">Financial Health Score</label>
                                <small class="d-block text-muted">Your 0-100 financial health rating</small>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold mt-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Widget Preferences
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
