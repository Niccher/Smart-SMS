<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Notification Settings - Mpesa Analyzer <?= $this->endSection() ?>

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
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-bell me-2"></i>Notification Preferences</h2>
        <p class="text-secondary mb-0">Choose which alerts you want to receive.</p>
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

<div class="row">
    <div class="col-lg-8">
        <div class="card settings-card">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4" style="color: var(--primary);">Alert Settings</h5>
                <form action="<?= base_url('dashboard/settings/notifications/save') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="setting-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-dark">Email Alerts</div>
                            <small class="text-muted">Receive email notifications for important alerts.</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="notify_email_alerts" value="1" role="switch"
                                <?= $settings['notify_email_alerts'] ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <div class="setting-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-dark">Budget Alerts</div>
                            <small class="text-muted">Get notified when you approach or exceed your budget limits.</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="notify_budget_alerts" value="1" role="switch"
                                <?= $settings['notify_budget_alerts'] ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <div class="setting-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-dark">Low Balance Warnings</div>
                            <small class="text-muted">Alert when your M-Pesa balance drops below a threshold.</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="notify_low_balance" value="1" role="switch"
                                <?= $settings['notify_low_balance'] ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <div class="setting-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-dark">Unusual Activity</div>
                            <small class="text-muted">Get alerts for unusual spending patterns or large transactions.</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="notify_unusual_activity" value="1" role="switch"
                                <?= $settings['notify_unusual_activity'] ? 'checked' : '' ?>>
                        </div>
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
</div>
<?= $this->endSection() ?>
