<?= $this->extend('Layouts/admin') ?>
<?= $this->section('title') ?> Blocklist - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .bulk-bar { position: sticky; top: 0; z-index: 5; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-ban me-2"></i> Blocklist</h2>
        <p class="text-secondary mb-0">Control which senders are excluded from your finance intelligence.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?php
    $bulkEndpoint = in_array($active_tab, ['allowed', 'unknown'], true)
        ? base_url('dashboard/blocklist/bulk-block')
        : base_url('dashboard/blocklist/bulk-unblock');
    $bulkLabel = in_array($active_tab, ['allowed', 'unknown'], true) ? 'Block Selected Senders' : 'Unblock Selected Senders';
    $bulkBtnClass = in_array($active_tab, ['allowed', 'unknown'], true) ? 'btn-danger' : 'btn-success';
?>

<div class="card settings-card">
    <div class="card-body p-4">
        <!-- Top nav (shared across Blocklist sub-pages) -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?= $active_tab === 'status' ? 'active' : '' ?>" href="<?= base_url('dashboard/blocklist/status') ?>">
                    <i class="fa-solid fa-chart-line me-1"></i> Status
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $active_tab === 'allowed' ? 'active' : '' ?>" href="<?= base_url('dashboard/blocklist/allowed') ?>">
                    <i class="fa-solid fa-star me-1"></i> Allowed <span class="badge bg-success ms-1"><?= $counts['allowed'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $active_tab === 'unknown' ? 'active' : '' ?>" href="<?= base_url('dashboard/blocklist/unknown') ?>">
                    <i class="fa-solid fa-question-circle me-1"></i> Unknown <span class="badge bg-secondary ms-1"><?= $counts['unknown'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $active_tab === 'blocked' ? 'active' : '' ?>" href="<?= base_url('dashboard/blocklist/blocked') ?>">
                    <i class="fa-solid fa-ban me-1"></i> Blocked <span class="badge bg-danger ms-1"><?= $counts['blocked'] ?></span>
                </a>
            </li>
        </ul>

        <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid <?= $meta['icon'] ?> me-2"></i><?= $meta['title'] ?></h5>
                <p class="text-muted small mb-0"><?= $meta['desc'] ?></p>
            </div>
            <input type="text" class="form-control" id="senderSearch" placeholder="Search sender..." style="max-width: 260px;">
        </div>

        <?php if (empty($senders)): ?>
            <div class="text-center text-muted p-5">
                <i class="fa-solid fa-inbox fs-1 d-block mb-2"></i>
                <?php if ($active_tab === 'blocked'): ?>
                    No blocked senders. Block senders from the Allowed or Unknown tabs.
                <?php elseif ($active_tab === 'allowed'): ?>
                    No allowed senders. Uploaded and analysed senders appear here once classified as finance.
                <?php else: ?>
                    No unknown senders. All your senders have been classified.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="bulk-bar d-flex align-items-center gap-2 bg-white border rounded p-2 mb-3" id="bulkBar">
                <?php if ($active_tab === 'unknown'): ?>
                    <button type="button" class="btn btn-sm btn-danger" id="bulkBlockBtn" disabled>
                        <i class="fa-solid fa-ban me-1"></i>Block Selected <span class="selectedCount"></span>
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="bulkAllowBtn" disabled>
                        <i class="fa-solid fa-star me-1"></i>Add Selected to Allowed List <span class="selectedCount"></span>
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-sm <?= $bulkBtnClass ?>" id="bulkActionBtn" disabled>
                        <i class="fa-solid fa-<?= $active_tab === 'blocked' ? 'check' : 'ban' ?> me-1"></i><?= $bulkLabel ?> <span id="selectedCount"></span>
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAllBtn">Select all</button>
                <span class="text-muted small" id="selInfo"></span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="sendersTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;"><input class="form-check-input" type="checkbox" id="checkAll"></th>
                            <th>Sender</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th style="width: 140px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($senders as $s): ?>
                            <tr data-sender="<?= esc($s['sender'], 'attr') ?>">
                                <td><input class="form-check-input row-check" type="checkbox" value="<?= esc($s['sender'], 'attr') ?>"></td>
                                <td class="fw-semibold"><?= esc($s['sender']) ?></td>
                                <td><?= esc($s['name']) ?: '<span class="text-muted">—</span>' ?></td>
                                <td><?= esc($s['category']) ?: '<span class="text-muted">—</span>' ?></td>
                                <td>
                                    <?php if ($s['blocked']): ?>
                                        <span class="badge bg-danger">Blocked</span>
                                    <?php elseif ($s['allowed']): ?>
                                        <span class="badge bg-success">Allowed</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Unknown</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($s['blocked']): ?>
                                        <button class="btn btn-sm btn-outline-success row-action" data-sender="<?= esc($s['sender'], 'attr') ?>" data-action="unblock">
                                            <i class="fa-solid fa-check me-1"></i> Unblock
                                        </button>
                                    <?php else: ?>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-outline-danger row-action" data-sender="<?= esc($s['sender'], 'attr') ?>" data-action="block">
                                                <i class="fa-solid fa-ban me-1"></i> Block
                                            </button>
                                            <?php if (!$s['allowed']): ?>
                                                <button class="btn btn-sm btn-outline-success row-action" data-sender="<?= esc($s['sender'], 'attr') ?>" data-action="allow">
                                                    <i class="fa-solid fa-star me-1"></i> Allow
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="alert alert-info mt-4 mb-0 small">
            <i class="fa-solid fa-circle-info me-1"></i>
            <strong>Blocked</strong> senders have their SMS marked non-finance (excluded from finance reports). <strong>Allowed</strong> senders are treated as finance by default. <strong>Unknown</strong> senders are run through classification each time and may be flagged finance or non-finance.
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const checkAll = document.getElementById('checkAll');
const bulkBtn = document.getElementById('bulkActionBtn');
const bulkBlockBtn = document.getElementById('bulkBlockBtn');
const bulkAllowBtn = document.getElementById('bulkAllowBtn');
const selectedCounts = document.querySelectorAll('.selectedCount');
const selectedCount = document.getElementById('selectedCount');
const bulkEndpoint = '<?= $bulkEndpoint ?>';
const isBulkBlock = bulkEndpoint.includes('bulk-block');

function selected() {
    return Array.from(document.querySelectorAll('.row-check:checked')).map(c => c.value);
}

function refresh() {
    const sel = selected();
    checkAll.checked = sel.length > 0 && sel.length === document.querySelectorAll('.row-check').length;
    if (bulkBtn) bulkBtn.disabled = sel.length === 0;
    if (bulkBlockBtn) bulkBlockBtn.disabled = sel.length === 0;
    if (bulkAllowBtn) bulkAllowBtn.disabled = sel.length === 0;
    if (selectedCount) selectedCount.textContent = sel.length ? `(${sel.length})` : '';
    selectedCounts.forEach(el => el.textContent = sel.length ? `(${sel.length})` : '');
    document.getElementById('selInfo').textContent = sel.length ? `${sel.length} selected` : '';
}

document.querySelectorAll('.row-check').forEach(c => c.addEventListener('change', refresh));
checkAll.addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
    refresh();
});

document.getElementById('selectAllBtn').addEventListener('click', function() {
    const all = document.querySelectorAll('.row-check');
    const shouldCheck = selected().length !== all.length;
    all.forEach(c => c.checked = shouldCheck);
    refresh();
});

document.getElementById('senderSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#sendersTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

function postSenders(url, senders, msg) {
    const data = new FormData();
    senders.forEach(s => data.append('senders[]', s));
    return fetch(url, { method: 'POST', body: data })
        .then(r => r.json()).then(res => {
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: msg.title,
                    html: msg.html + '<br><span class="small text-muted">' + res.message + '</span>',
                    timer: 2000,
                    showConfirmButton: false,
                }).then(() => window.location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Something went wrong.' });
            }
        })
        .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
}

if (bulkBtn) {
    bulkBtn.addEventListener('click', function() {
        const sel = selected();
        if (!sel.length) return;
        const blocking = isBulkBlock;
        Swal.fire({
            title: (blocking ? 'Block' : 'Unblock') + ' selected senders?',
            html: 'This will <strong>' + (blocking ? 'exclude' : 'include') + '</strong> '
                + sel.length + ' sender(s) '
                + (blocking ? 'from your finance intelligence.' : 'back into finance classification.'),
            icon: blocking ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: blocking ? '#dc3545' : '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: blocking ? 'Yes, block them' : 'Yes, unblock them',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) {
                postSenders(bulkEndpoint, sel, {
                    title: blocking ? 'Senders blocked' : 'Senders unblocked',
                    html: sel.length + ' sender(s) ' + (blocking ? 'excluded from' : 'restored to') + ' finance intelligence.',
                });
            }
        });
    });
}

if (bulkBlockBtn) {
    bulkBlockBtn.addEventListener('click', function() {
        const sel = selected();
        if (!sel.length) return;
        Swal.fire({
            title: 'Block selected senders?',
            html: 'This will <strong>exclude</strong> ' + sel.length + ' sender(s) from your finance intelligence.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, block them',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) {
                postSenders('<?= base_url('dashboard/blocklist/bulk-block') ?>', sel, {
                    title: 'Senders blocked',
                    html: sel.length + ' sender(s) excluded from finance intelligence.',
                });
            }
        });
    });
}

if (bulkAllowBtn) {
    bulkAllowBtn.addEventListener('click', function() {
        const sel = selected();
        if (!sel.length) return;
        Swal.fire({
            title: 'Allow selected senders?',
            html: 'This will <strong>allow</strong> ' + sel.length + ' sender(s) and classify them as finance.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, allow them',
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) {
                postSenders('<?= base_url('dashboard/blocklist/bulk-allow') ?>', sel, {
                    title: 'Senders allowed',
                    html: sel.length + ' sender(s) added to allowed list.',
                });
            }
        });
    });
}

document.querySelectorAll('.row-action').forEach(btn => {
    btn.addEventListener('click', function() {
        const sender = this.dataset.sender;
        const action = this.dataset.action;
        let url = '<?= base_url('dashboard/blocklist/block') ?>';
        if (action === 'unblock') {
            url = '<?= base_url('dashboard/blocklist/unblock') ?>';
        } else if (action === 'allow') {
            url = '<?= base_url('dashboard/blocklist/allow') ?>';
        }
        
        const blocking = action === 'block';
        const allowing = action === 'allow';
        const unblocking = action === 'unblock';
        
        let title = 'Block sender?';
        let html = 'Sender <strong>' + sender + '</strong> will be excluded from your finance intelligence.';
        let icon = 'warning';
        let confirmColor = '#dc3545';
        let confirmText = 'Yes, block';
        
        if (allowing) {
            title = 'Allow sender?';
            html = 'Sender <strong>' + sender + '</strong> will be added to the allowed list and classified as finance.';
            icon = 'question';
            confirmColor = '#28a745';
            confirmText = 'Yes, allow';
        } else if (unblocking) {
            title = 'Unblock sender?';
            html = 'Sender <strong>' + sender + '</strong> will be restored for finance classification.';
            icon = 'question';
            confirmColor = '#28a745';
            confirmText = 'Yes, unblock';
        }

        Swal.fire({
            title: title,
            html: html,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel',
            allowOutsideClick: false,
            focusCancel: true,
        }).then(result => {
            if (result.isConfirmed) {
                postSenders(url, [sender], {
                    title: allowing ? 'Sender allowed' : (blocking ? 'Sender blocked' : 'Sender unblocked'),
                    html: '<strong>' + sender + '</strong> ' + (allowing ? 'added to allowed list' : (blocking ? 'excluded from' : 'restored to')) + ' finance intelligence.',
                });
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
