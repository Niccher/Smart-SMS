<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Settings - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .settings-card {
        border: none;
        border-radius: 4px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: transform 0.2s;
    }
    .settings-card:hover {
        transform: translateY(-2px);
    }
    .settings-card .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">Settings</h2>
        <p class="text-secondary mb-0">Manage your profile, preferences, and data.</p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?= $this->include('Layouts/_control_center_nav', ['activeTab' => 'preferences']) ?>

<?php if (session()->has('message')) : ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= session('message') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->has('error')) : ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= session('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4 col-md-6">
        <a href="<?= base_url('dashboard/settings/profile') ?>" class="text-decoration-none">
            <div class="card settings-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="card-icon" style="background: #e8f5e9; color: #2e7d32;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Profile</h6>
                            <small class="text-muted">Username, email, password</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Update your display name, email address, or change your password.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a href="<?= base_url('dashboard/settings/notifications') ?>" class="text-decoration-none">
            <div class="card settings-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="card-icon" style="background: #e3f2fd; color: #1565c0;">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Notifications</h6>
                            <small class="text-muted">Alert preferences</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Configure which alerts you receive for budgets, low balance, and unusual activity.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a href="<?= base_url('dashboard/settings/preferences') ?>" class="text-decoration-none">
            <div class="card settings-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="card-icon" style="background: #f3e5f5; color: #7b1fa2;">
                            <i class="fa-solid fa-sliders"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Preferences</h6>
                            <small class="text-muted">Currency, dates, widgets</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Set your currency, date format, budget defaults, and choose which dashboard widgets to show.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a href="<?= base_url('dashboard/settings/security') ?>" class="text-decoration-none">
            <div class="card settings-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="card-icon" style="background: #fce4ec; color: #c62828;">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Security</h6>
                            <small class="text-muted">API tokens, devices</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Manage your API access tokens and linked Android devices.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a href="<?= base_url('dashboard/settings/data') ?>" class="text-decoration-none">
            <div class="card settings-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="card-icon" style="background: #e0f2f1; color: #00695c;">
                            <i class="fa-solid fa-database"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Data Management</h6>
                            <small class="text-muted">Export, purge, uploads</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Export your transactions, purge old data, or delete individual upload batches.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a href="<?= base_url('dashboard/settings/tags') ?>" class="text-decoration-none">
            <div class="card settings-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="card-icon" style="background: #fff3e0; color: #e65100;">
                            <i class="fa-solid fa-tags"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Tags</h6>
                            <small class="text-muted">Custom labels</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Create custom tags to label and filter your transactions.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a href="<?= base_url('dashboard/settings/goals') ?>" class="text-decoration-none">
            <div class="card settings-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="card-icon" style="background: #e8f5e9; color: #1b5e20;">
                            <i class="fa-solid fa-bullseye"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Spending Goals</h6>
                            <small class="text-muted">Targets & progress</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Set monthly or weekly spending targets and track your progress.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a href="<?= base_url('dashboard/settings/recurring') ?>" class="text-decoration-none">
            <div class="card settings-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="card-icon" style="background: #fce4ec; color: #880e4f;">
                            <i class="fa-solid fa-rotate"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Recurring</h6>
                            <small class="text-muted">Bills & subscriptions</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Track recurring payments and detect new recurring patterns.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a href="<?= base_url('dashboard/settings/report-schedule') ?>" class="text-decoration-none">
            <div class="card settings-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="card-icon" style="background: #e3f2fd; color: #0d47a1;">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Report Schedule</h6>
                            <small class="text-muted">Auto-email reports</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">Schedule periodic PDF/CSV reports to be emailed to you.</p>
                </div>
            </div>
        </a>
    </div>
</div>
<?= $this->endSection() ?>
