<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Cache & Sessions - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); background: var(--card-bg, #fff); }
    .metric-pill {
        background: var(--body-bg, rgba(0,0,0,0.02));
        border: 1px solid var(--card-border, #e2e8f0);
        border-radius: 6px;
        padding: 0.85rem 1rem;
        height: 100%;
    }
    .metric-pill .label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 600; }
    .metric-pill .val { font-size: 1.15rem; font-weight: 700; color: var(--text-main); line-height: 1.3; }
    [data-bs-theme="dark"] .settings-card { box-shadow: 0 4px 20px rgba(0,0,0,0.25); }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-broom me-2"></i> Cache &amp; Sessions</h2>
        <p class="text-secondary mb-0">Manage compiled application cache, active user sessions, and automated data retention.</p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Sub Navigation -->
<div class="card settings-card mb-4">
    <div class="card-body p-3 pb-0">
        <?= view('Admin/System/_nav', ['active' => 'maintenance']) ?>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Application Cache Card -->
    <div class="col-lg-6">
        <div class="card settings-card h-100 border p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 bg-primary-subtle text-primary rounded"><i class="fa-solid fa-memory fs-5"></i></span>
                    <div>
                        <h5 class="fw-bold mb-0">Application Cache</h5>
                        <small class="text-muted">Compiled views, config &amp; data cache</small>
                    </div>
                </div>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1"><?= esc($cache_info['driver']) ?></span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="metric-pill">
                        <div class="label">Cached Files</div>
                        <div class="val"><?= number_format($cache_info['file_count']) ?></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="metric-pill">
                        <div class="label">Storage Used</div>
                        <div class="val"><?= esc($cache_info['size_human']) ?></div>
                    </div>
                </div>
            </div>

            <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold" id="clearCacheBtn">
                    <i class="fa-solid fa-trash-can me-1"></i> Flush All Cache
                </button>
                <span class="text-secondary small" id="cacheStatus"></span>
            </div>
        </div>
    </div>

    <!-- Active Sessions Card -->
    <div class="col-lg-6">
        <div class="card settings-card h-100 border p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 bg-info-subtle text-info rounded"><i class="fa-solid fa-user-clock fs-5"></i></span>
                    <div>
                        <h5 class="fw-bold mb-0">User Sessions</h5>
                        <small class="text-muted">Active client sessions &amp; storage</small>
                    </div>
                </div>
                <span class="badge bg-info-subtle text-info rounded-pill px-3 py-1"><?= esc($session_info['handler']) ?></span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div class="metric-pill">
                        <div class="label">Active Sessions</div>
                        <div class="val"><?= number_format($session_info['file_count']) ?></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="metric-pill">
                        <div class="label">Expired (2h+)</div>
                        <div class="val <?= $session_info['expired_sessions'] > 0 ? 'text-warning' : 'text-success' ?>">
                            <?= number_format($session_info['expired_sessions']) ?>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="metric-pill">
                        <div class="label">Storage</div>
                        <div class="val"><?= esc($session_info['size_human']) ?></div>
                    </div>
                </div>
            </div>

            <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-semibold" id="cleanExpiredBtn">
                        <i class="fa-solid fa-broom me-1"></i> Clean Stale (2h+)
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold" id="clearSessionsBtn">
                        <i class="fa-solid fa-user-slash me-1"></i> Flush All
                    </button>
                </div>
                <span class="text-secondary small" id="sessionStatus"></span>
            </div>
        </div>
    </div>
</div>

<!-- Automated Data Retention -->
<div class="card settings-card border mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="p-2 bg-warning-subtle text-warning rounded"><i class="fa-solid fa-hourglass-half fs-5"></i></span>
                <div>
                    <h5 class="fw-bold mb-0">Automated Data Retention</h5>
                    <small class="text-muted">Purge old SMS uploads and unreferenced records automatically</small>
                </div>
            </div>
            <a href="<?= base_url('admin/crons') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="fa-solid fa-clock me-1"></i> View Cron Schedules
            </a>
        </div>

        <form id="retentionForm" class="row g-3 align-items-center">
            <?= csrf_field() ?>
            <div class="col-md-5 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary"><i class="fa-solid fa-calendar-days text-primary"></i></span>
                    <input type="number" class="form-control" name="retention_days" id="retentionDays" min="0" max="3650" value="<?= esc($retention_days) ?>" placeholder="Days (0 = keep forever)" required>
                    <span class="input-group-text bg-body-tertiary text-muted">days</span>
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Window
                </button>
            </div>
            <div class="col-12">
                <span class="text-secondary small" id="retentionStatus">
                    <?= $retention_days > 0 
                        ? 'Active: Purging uploads older than ' . esc($retention_days) . ' days.' 
                        : 'Inactive: All uploaded dataset files are retained indefinitely.' ?>
                </span>
            </div>
        </form>
    </div>
</div>

<script>
function showStatus(el, message, type = 'info') {
    el.textContent = message;
    el.className = 'text-' + (type === 'success' ? 'success' : type === 'error' ? 'danger' : 'secondary') + ' small';
}

function setLoading(btn, loading) {
    btn.disabled = loading;
    if (loading) {
        btn.dataset.originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Working...';
    } else {
        btn.innerHTML = btn.dataset.originalHtml || btn.innerHTML;
    }
}

function confirmAction(title, text, icon = 'warning', confirmText = 'Yes, proceed') {
    return Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        reverseButtons: true,
    });
}

document.getElementById('clearCacheBtn').addEventListener('click', async function() {
    const result = await confirmAction(
        'Clear All Application Cache?',
        'This will:\n• Remove ALL cached views, config, and data\n• Force fresh compilation on next request\n• May cause temporary slowdown on first page loads\n\nThis action CANNOT be undone.',
        'warning',
        'Yes, clear cache'
    );
    if (!result.isConfirmed) return;

    const btn = this;
    const status = document.getElementById('cacheStatus');
    setLoading(btn, true);
    showStatus(status, 'Clearing cache...', 'info');

    try {
        const res = await fetch('<?= base_url('admin/system/maintenance/clear-cache') ?>', { method: 'POST' });
        const data = await res.json();
        showStatus(status, data.message, data.status === 'success' ? 'success' : 'error');
        if (data.status === 'success') {
            Swal.fire('Cleared!', data.message, 'success');
        }
    } catch (e) {
        showStatus(status, 'Request failed: ' + e.message, 'error');
        Swal.fire('Error', 'Request failed: ' + e.message, 'error');
    }
    setLoading(btn, false);
});

document.getElementById('cleanExpiredBtn').addEventListener('click', async function() {
    const result = await confirmAction(
        'Clean Expired Sessions?',
        'This will:\n• Remove ONLY sessions older than 2 hours\n• Keep active user sessions intact\n• Free up storage space\n\nThis action CANNOT be undone.',
        'info',
        'Yes, clean expired'
    );
    if (!result.isConfirmed) return;

    const btn = this;
    const status = document.getElementById('sessionStatus');
    setLoading(btn, true);
    showStatus(status, 'Cleaning expired sessions...', 'info');

    try {
        const res = await fetch('<?= base_url('admin/system/maintenance/clean-expired-sessions') ?>', { method: 'POST' });
        const data = await res.json();
        showStatus(status, data.message, data.status === 'success' ? 'success' : 'error');
        if (data.status === 'success') {
            Swal.fire('Cleaned!', data.message, 'success');
        }
    } catch (e) {
        showStatus(status, 'Request failed: ' + e.message, 'error');
        Swal.fire('Error', 'Request failed: ' + e.message, 'error');
    }
    setLoading(btn, false);
});

document.getElementById('clearSessionsBtn').addEventListener('click', async function() {
    const result = await confirmAction(
        'Clear ALL Sessions?',
        '⚠️ THIS WILL LOG OUT EVERYONE INCLUDING YOU\n\nThis will:\n• Immediately terminate ALL active user sessions\n• Log out ALL users (including yourself)\n• Clear both file and database sessions\n• Users will need to log in again\n\nThis action CANNOT be undone.',
        'error',
        'Yes, clear ALL sessions'
    );
    if (!result.isConfirmed) return;

    const btn = this;
    const status = document.getElementById('sessionStatus');
    setLoading(btn, true);
    showStatus(status, 'Clearing all sessions...', 'info');

    try {
        const res = await fetch('<?= base_url('admin/system/maintenance/clear-sessions') ?>', { method: 'POST' });
        const data = await res.json();
        showStatus(status, data.message, data.status === 'success' ? 'success' : 'error');
        if (data.status === 'success') {
            Swal.fire('Cleared!', data.message + ' Reloading...', 'success');
            setTimeout(() => location.reload(), 1500);
        }
    } catch (e) {
        showStatus(status, 'Request failed: ' + e.message, 'error');
        Swal.fire('Error', 'Request failed: ' + e.message, 'error');
    }
    setLoading(btn, false);
});

document.getElementById('retentionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const status = document.getElementById('retentionStatus');
    const data = new FormData(this);
    const btn = this.querySelector('button[type=submit]');
    setLoading(btn, true);
    showStatus(status, 'Saving retention period...', 'info');
    try {
        const res = await fetch('<?= base_url('admin/system/maintenance/save-retention') ?>', { method: 'POST', body: data });
        const r = await res.json();
        showStatus(status, r.message, r.status === 'success' ? 'success' : 'error');
        Swal.fire(r.status === 'success' ? 'Saved!' : 'Error', r.message, r.status === 'success' ? 'success' : 'error');
    } catch (err) {
        showStatus(status, 'Request failed: ' + err.message, 'error');
    }
    setLoading(btn, false);
});
</script>
<?= $this->endSection() ?>