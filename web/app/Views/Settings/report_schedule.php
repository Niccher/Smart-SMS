<?= $this->extend('Layouts/admin') ?>
<?= $this->section('title') ?> Report Schedule - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?><style>.settings-card{border:none;border-radius:4px;box-shadow:0 4px 15px rgba(0,0,0,0.03)}</style><?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div><h2 class="fw-bold mb-1" style="color:var(--primary);"><i class="fa-solid fa-calendar-check me-2"></i>Report Schedule</h2><p class="text-secondary mb-0">Auto-generate and email periodic financial reports.</p></div>
    <a href="<?= base_url('dashboard/settings') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php if (session()->has('message')): ?><div class="alert alert-success alert-dismissible fade show"><?= session('message') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
<div class="row">
    <div class="col-lg-8">
        <div class="card settings-card"><div class="card-body p-4">
            <h5 class="fw-bold mb-4" style="color:var(--primary);">Schedule Configuration</h5>
            <form action="<?= base_url('dashboard/settings/report-schedule/save') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enabled" value="1" role="switch" id="rs_enabled"
                            <?= ($settings['report_schedule_enabled'] ?? false) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="rs_enabled">Enable Scheduled Reports</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?= $settings['report_schedule_email'] ?? auth()->user()->email ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Frequency</label>
                    <select name="frequency" class="form-select">
                        <option value="weekly" <?= ($settings['report_schedule_frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                        <option value="monthly" <?= ($settings['report_schedule_frequency'] ?? 'monthly') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Day of Period</label>
                    <input type="number" name="day" class="form-control" min="1" max="28" value="<?= $settings['report_schedule_day'] ?? 1 ?>">
                    <small class="text-muted">Day of month (1-28) or day of week (1=Monday) for weekly reports.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Format</label>
                    <select name="format" class="form-select">
                        <option value="pdf" <?= ($settings['report_schedule_format'] ?? 'pdf') === 'pdf' ? 'selected' : '' ?>>PDF</option>
                        <option value="csv" <?= ($settings['report_schedule_format'] ?? '') === 'csv' ? 'selected' : '' ?>>CSV</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Save Schedule</button>
            </form>
        </div></div>
    </div>
</div>
<?= $this->endSection() ?>
