<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Email Notifications - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-envelope me-2"></i> Email Notifications</h2>
        <p class="text-secondary mb-0">Configure the SMTP server and choose which events email your users.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
// Group triggers by their group for the admin UI.
$grouped = [];
foreach ($trigger_meta as $m) {
    $grouped[$m['group']][] = $m;
}
?>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="card settings-card">
            <div class="card-body p-4">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#setupTab"><i class="fa-solid fa-server me-1"></i> Setup &amp; Configuration</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#triggersTab"><i class="fa-solid fa-bell me-1"></i> Triggers <span class="badge bg-secondary ms-1"><?= count($trigger_meta) ?></span></button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#emailsSentTab"><i class="fa-solid fa-paper-plane me-1"></i> Emails Sent <span class="badge bg-secondary ms-1"><?= esc($email_log_count ?: 0) ?></span></button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="setupTab">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="card settings-card mb-4">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-server me-2"></i> SMTP Configuration</h5>
                                        <div class="alert alert-info py-2 small mb-3">
                                            <i class="fa-solid fa-circle-info me-1"></i>
                                            <strong>Setup Tip:</strong> Use <strong>Port 465 with SSL</strong> (or <strong>Port 587 with TLS</strong>). Ensure your <strong>From Email</strong> uses your domain (e.g. <code>info@chegecache.co.ke</code>), not <code>example.com</code>, so your mail server permits relaying.
                                        </div>
                                        <form id="smtpForm">
                                            <?= csrf_field() ?>
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label class="form-label fw-semibold">SMTP Host</label>
                                                    <input type="text" class="form-control" name="smtp_host" value="<?= esc($config['smtp_host']) ?>" placeholder="smtp.yourhost.com">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Port</label>
                                                    <input type="number" class="form-control" name="smtp_port" value="<?= esc($config['smtp_port']) ?>" placeholder="587">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Username</label>
                                                    <input type="text" class="form-control" name="smtp_user" value="<?= esc($config['smtp_user']) ?>" autocomplete="off" placeholder="SMTP username">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Password</label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" name="smtp_pass" value="<?= esc($config['smtp_pass']) ?>" autocomplete="new-password" placeholder="SMTP password">
                                                        <button class="btn btn-outline-secondary" type="button" data-toggle-pass="smtp_pass"><i class="fa-solid fa-eye"></i></button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Encryption</label>
                                                    <select class="form-select" name="smtp_crypto">
                                                        <option value="tls" <?= ($config['smtp_crypto'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                                        <option value="ssl" <?= ($config['smtp_crypto'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                                        <option value="none" <?= ($config['smtp_crypto'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <label class="form-label fw-semibold mb-0">From Email</label>
                                                        <button type="button" class="btn btn-link p-0 text-decoration-none small" id="btnMatchUsername" style="font-size: 0.78rem;">
                                                            <i class="fa-solid fa-arrows-rotate me-1"></i> Match Username
                                                        </button>
                                                    </div>
                                                    <input type="email" class="form-control" name="from_email" id="fromEmailInput" value="<?= esc($config['from_email']) ?>" placeholder="noreply@example.com">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">From Name</label>
                                                    <input type="text" class="form-control" name="from_name" value="<?= esc($config['from_name']) ?>" placeholder="Mpesa Analyzer">
                                                </div>
                                                <div class="col-md-6 d-flex align-items-end">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="enabled" value="1" role="switch" id="emailEnabled" <?= !empty($config['enabled']) ? 'checked' : '' ?>>
                                                        <label class="form-check-label fw-semibold" for="emailEnabled">Enable email notifications</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2 mt-4">
                                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Save SMTP Settings</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="card settings-card">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-paper-plane me-2"></i> Send Test Email</h5>
                                        <p class="text-muted small mb-3">Verify the SMTP configuration by sending a test email.</p>
                                        <form id="testEmailForm" class="d-flex gap-2 flex-wrap">
                                            <?= csrf_field() ?>
                                            <input type="email" class="form-control flex-grow-1" name="test_email" placeholder="recipient@example.com" required>
                                            <button type="submit" class="btn btn-outline-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-paper-plane me-1"></i> Send Test Email</button>
                                        </form>
                                        <div id="testResult" class="mt-3"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card settings-card">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold mb-3" style="color: var(--primary);">SMTP Status</h5>
                                        <?php if (empty($config['enabled'])): ?>
                                            <div class="alert alert-warning py-2 small mb-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> Email notifications are currently <strong>disabled</strong>.</div>
                                        <?php elseif ($config['smtp_host'] === ''): ?>
                                            <div class="alert alert-warning py-2 small mb-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> Enabled but no SMTP host configured.</div>
                                        <?php else: ?>
                                            <div class="alert alert-success py-2 small mb-3"><i class="fa-solid fa-circle-check me-1"></i> SMTP configured and enabled.</div>
                                        <?php endif; ?>
                                        <ul class="small text-muted mb-0">
                                            <li class="mb-1"><strong>Host:</strong> <?= esc($config['smtp_host'] ?: '—') ?></li>
                                            <li class="mb-1"><strong>Port:</strong> <?= esc($config['smtp_port']) ?></li>
                                            <li class="mb-1"><strong>Encryption:</strong> <?= esc(($config['smtp_crypto'] ?? 'tls') === 'none' ? 'none' : strtoupper($config['smtp_crypto'] ?? 'tls')) ?></li>
                                            <li class="mb-1"><strong>From:</strong> <?= esc($config['from_email'] ?: '—') ?></li>
                                        </ul>
                                        <hr>
                                        <p class="small text-muted mb-0">
                                            Shield-sent emails (password reset / magic links) automatically use the same SMTP configuration.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="triggersTab">
                        <div class="card settings-card mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-bell me-2"></i> Email Triggers</h5>
                                <p class="text-muted small mb-3">Choose the events that should email your users. Turning a trigger off stops those emails from being sent.</p>
                                <form id="triggersForm">
                                    <?= csrf_field() ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr><th>Event</th><th>Description</th><th class="text-center" style="width: 110px;">Enabled</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($grouped as $group => $items): ?>
                                                    <?php foreach ($items as $meta): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?= esc($meta['label']) ?></strong>
                                                            <br><small class="text-muted text-uppercase" style="font-size: 10px;"><?= esc($group) ?></small>
                                                            <br><code><?= esc($meta['key']) ?></code>
                                                        </td>
                                                        <td class="text-muted small"><?= esc($meta['description']) ?></td>
                                                        <td class="text-center">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input class="form-check-input" type="checkbox" name="triggers[<?= esc($meta['key']) ?>]" value="1" role="switch" <?= !empty($triggers[$meta['key']]) ? 'checked' : '' ?>>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <?php if ($group !== array_key_last($grouped)): ?>
                                                    <tr><td colspan="3" class="border-0"></td></tr>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Save Triggers</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="emailsSentTab">
                        <div class="card settings-card mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-paper-plane me-2"></i> Sent Emails</h5>
                                <p class="text-muted small mb-3">Log of all emails sent through the notification system (last 50).</p>
                                <?php if (empty($email_log)): ?>
                                    <div class="text-center text-muted p-4">
                                        <i class="fa-solid fa-inbox fs-1 d-block mb-2"></i>
                                        No emails have been sent yet. The log records every send attempt (success or failure).
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Date &amp; Time</th>
                                                    <th>Trigger</th>
                                                    <th>Recipient</th>
                                                    <th>Subject</th>
                                                    <th style="width: 100px;">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($email_log as $entry): ?>
                                                    <?php
                                                        $statusClass = ($entry['status'] ?? '') === 'success' ? 'bg-success' : 'bg-danger';
                                                        $statusLabel = ($entry['status'] ?? '') === 'success' ? 'Sent' : 'Failed';
                                                        $localTime = '';
                                                        if (!empty($entry['sent_at'])) {
                                                            try {
                                                                $dt = new \DateTimeImmutable($entry['sent_at'], new \DateTimeZone('UTC'));
                                                                $localTime = $dt->setTimezone(new \DateTimeZone('Africa/Nairobi'))->format('d M Y, g:i A');
                                                            } catch (\Throwable $e) {
                                                                $localTime = $entry['sent_at'];
                                                            }
                                                        }
                                                    ?>
                                                    <tr>
                                                        <td><small class="text-muted"><?= esc($localTime ?: '—') ?></small></td>
                                                        <td><code><?= esc($entry['trigger'] ?: '—') ?></code></td>
                                                        <td><?= esc($entry['to_email'] ?: '—') ?></td>
                                                        <td><?= esc($entry['subject'] ?: '—') ?></td>
                                                        <td><span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
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
            </div>
        </div>
    </div>
</div>

<script>
async function safeFetchJson(response) {
    const text = await response.text();
    if (!text || !text.trim()) {
        throw new Error(`Server returned empty response (HTTP ${response.status}).`);
    }
    try {
        return JSON.parse(text);
    } catch (e) {
        const clean = text.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim();
        throw new Error(clean.substring(0, 250) || `Server error (HTTP ${response.status}).`);
    }
}

function showAlert(title, message, type) {
    const icon = type === 'success' ? 'success' : (type === 'danger' || type === 'error' ? 'error' : 'info');
    Swal.fire(title, message, icon);
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-match From Email with Username
    const smtpUserInput = document.querySelector('input[name="smtp_user"]');
    const fromEmailInput = document.getElementById('fromEmailInput');
    const btnMatchUsername = document.getElementById('btnMatchUsername');

    if (smtpUserInput && fromEmailInput) {
        smtpUserInput.addEventListener('input', function() {
            const userVal = this.value.trim();
            if (userVal.includes('@') && (fromEmailInput.value.trim() === '' || fromEmailInput.value.includes('example.com') || fromEmailInput.dataset.autoMatched === 'true')) {
                fromEmailInput.value = userVal;
                fromEmailInput.dataset.autoMatched = 'true';
            }
        });

        fromEmailInput.addEventListener('input', function() {
            delete this.dataset.autoMatched;
        });
    }

    if (btnMatchUsername && smtpUserInput && fromEmailInput) {
        btnMatchUsername.addEventListener('click', function() {
            const userVal = smtpUserInput.value.trim();
            if (!userVal) {
                Swal.fire('Match Username', 'Please enter a Username first.', 'info');
                return;
            }
            fromEmailInput.value = userVal;
            fromEmailInput.dataset.autoMatched = 'true';
            Swal.fire({
                title: 'From Email Updated',
                text: `From Email set to "${userVal}" to match SMTP Username.`,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        });
    }

    const smtpForm = document.getElementById('smtpForm');
    if (smtpForm) {
        smtpForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
            }
            const body = new FormData(this);
            fetch('<?= base_url('admin/notifications/save-config') ?>', { method: 'POST', body })
                .then(r => safeFetchJson(r))
                .then(res => {
                    if (res.status === 'success') {
                        showAlert('Email Notifications', res.message, 'success');
                    } else {
                        showAlert('Email Notifications', res.message, 'danger');
                    }
                })
                .catch(err => showAlert('Error', err.message, 'danger'))
                .finally(() => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                });
        });
    }

    const testEmailForm = document.getElementById('testEmailForm');
    if (testEmailForm) {
        testEmailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const body = new FormData(this);
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
            }
            fetch('<?= base_url('admin/notifications/send-test-email') ?>', { method: 'POST', body })
                .then(r => safeFetchJson(r))
                .then(res => {
                    const testResult = document.getElementById('testResult');
                    if (testResult) {
                        testResult.innerHTML =
                            '<div class="alert alert-' + (res.status === 'success' ? 'success' : 'danger') + ' py-2 small mb-0">' +
                            '<i class="fa-solid fa-' + (res.status === 'success' ? 'circle-check' : 'circle-xmark') + ' me-1"></i> ' + res.message + '</div>';
                    }
                })
                .catch(err => {
                    const testResult = document.getElementById('testResult');
                    if (testResult) {
                        testResult.innerHTML = '<div class="alert alert-danger py-2 small mb-0"><i class="fa-solid fa-circle-xmark me-1"></i> ' + err.message + '</div>';
                    }
                })
                .finally(() => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Send Test Email';
                    }
                });
        });
    }

    const triggersForm = document.getElementById('triggersForm');
    if (triggersForm) {
        triggersForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
            }
            const body = new FormData(this);
            fetch('<?= base_url('admin/notifications/save-triggers') ?>', { method: 'POST', body })
                .then(r => safeFetchJson(r))
                .then(res => {
                    if (res.status === 'success') {
                        showAlert('Email Notifications', res.message, 'success');
                    } else {
                        showAlert('Email Notifications', res.message, 'danger');
                    }
                })
                .catch(err => showAlert('Error', err.message, 'danger'))
                .finally(() => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                });
        });
    }

    document.querySelectorAll('[data-toggle-pass]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.querySelector('input[name="' + btn.dataset.togglePass + '"]');
            if (input) {
                input.type = input.type === 'password' ? 'text' : 'password';
                btn.innerHTML = input.type === 'password' ? '<i class="fa-solid fa-eye"></i>' : '<i class="fa-solid fa-eye-slash"></i>';
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
