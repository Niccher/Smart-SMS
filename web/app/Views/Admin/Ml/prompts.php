<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Prompts - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); }
    .key-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0; }
    .prompt-pre { background:#0f1220; color:#d6dbe8; border-radius:8px; padding:12px 14px; font-size:.78rem; white-space:pre-wrap; word-break:break-word; max-height:240px; overflow:auto; }
    .badge-soft { font-size:.7rem; font-weight:600; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-file-lines me-2"></i> ML Prompts</h2>
        <p class="text-secondary mb-0">View and manage the prompts used for SMS classification and extraction. Editing creates a new version.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="card settings-card">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'prompts', 'status' => $status]) ?>

        <?php if (!empty($prompts['error'])): ?>
            <div class="alert alert-warning mb-0">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                Could not load prompts from the ML backend: <?= esc($prompts['error']) ?>
            </div>
        <?php else: ?>
            <?php
            $resolved = $prompts['resolved'] ?? [];
            $all = $prompts['prompts'] ?? [];
            $keys = $prompts['keys'] ?? [];
            $keyMeta = [
                'classify_sender' => ['Classify Sender', 'fa-microchip', 'Decides whether an SMS sender is finance-related.', 'primary'],
                'extract_batch'   => ['Extract Batch', 'fa-arrows-down-to-line', 'Parses a batch of SMS into structured financial data.', 'success'],
            ];
            ?>

            <div class="d-flex flex-wrap gap-3 mb-4">
                <div class="card stat-card h-100 border" style="--stat-color:#0d6efd; min-width:170px;">
                    <div class="card-body p-3 d-flex align-items-start gap-3">
                        <div class="key-icon bg-primary-subtle text-primary"><i class="fa-solid fa-list"></i></div>
                        <div>
                            <div class="fw-bold fs-3 lh-1"><?= count($keys) ?></div>
                            <div class="fw-semibold small">Prompt Keys</div>
                            <div class="text-muted small">Prompt types available</div>
                        </div>
                    </div>
                </div>
                <div class="card stat-card h-100 border" style="--stat-color:#198754; min-width:170px;">
                    <div class="card-body p-3 d-flex align-items-start gap-3">
                        <div class="key-icon bg-success-subtle text-success"><i class="fa-solid fa-layer-group"></i></div>
                        <div>
                            <div class="fw-bold fs-3 lh-1"><?= count($all) ?></div>
                            <div class="fw-semibold small">DB Versions</div>
                            <div class="text-muted small">Saved prompt versions</div>
                        </div>
                    </div>
                </div>
                <div class="card stat-card h-100 border" style="--stat-color:#fd7e14; min-width:170px;">
                    <div class="card-body p-3 d-flex align-items-start gap-3">
                        <div class="key-icon bg-warning-subtle text-warning"><i class="fa-solid fa-book-open"></i></div>
                        <div>
                            <div class="fw-bold fs-3 lh-1"><?= count(array_filter($resolved, fn($r) => !$r['using_db'] ?? false)) ?></div>
                            <div class="fw-semibold small">Using Default</div>
                            <div class="text-muted small">Hardcoded fallback active</div>
                        </div>
                    </div>
                </div>
            </div>

            <?php foreach ($keys as $key): ?>
                <?php
                $meta = $keyMeta[$key] ?? [$key, 'fa-gear', '', 'secondary'];
                [$label, $icon, $desc, $color] = $meta;
                $r = $resolved[$key] ?? ['using_db' => false, 'active' => null, 'default' => ''];
                $versions = array_filter($all, fn($p) => $p['prompt_key'] === $key);
                ?>
                <div class="card settings-card mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start gap-3 flex-wrap">
                            <div class="key-icon bg-<?= $color ?>-subtle text-<?= $color ?>"><i class="fa-solid <?= esc($icon) ?>"></i></div>
                            <div class="flex-grow-1">
                                <div class="fw-bold fs-6"><?= esc($label) ?></div>
                                <div class="text-muted small"><?= esc($desc) ?></div>
                                <div class="d-flex flex-wrap gap-2 mt-2">
                                    <span class="badge bg-secondary badge-soft">key: <?= esc($key) ?></span>
                                    <?php if (!empty($r['using_db'])): ?>
                                        <span class="badge bg-success badge-soft"><i class="fa-solid fa-database me-1"></i>Using DB prompt</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark badge-soft"><i class="fa-solid fa-book-open me-1"></i>Hardcoded default</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-primary rounded-pill px-3 open-editor"
                                    data-key="<?= esc($key) ?>"
                                    data-title="<?= esc($label) ?>"
                                    data-body="<?= esc($r['using_db'] ? ($r['active'] ?? '') : ($r['default'] ?? ''), 'attr') ?>">
                                <i class="fa-solid fa-plus me-1"></i> New Version
                            </button>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-lg-6">
                                <div class="small fw-semibold text-secondary mb-1">Current Prompt</div>
                                <pre class="prompt-pre mb-0"><?= esc($r['using_db'] ? ($r['active'] ?? '') : ($r['default'] ?? '')) ?></pre>
                            </div>
                            <div class="col-lg-6">
                                <div class="small fw-semibold text-secondary mb-1">Hardcoded Default (fallback)</div>
                                <pre class="prompt-pre mb-0"><?= esc($r['default'] ?? '') ?></pre>
                            </div>
                        </div>

                        <?php if ($versions): ?>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-striped align-middle system-table">
                                    <thead class="table-light">
                                        <tr><th>Version</th><th>Title</th><th>Status</th><th>Created</th><th>Actions</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($versions as $p): ?>
                                        <tr>
                                            <td class="fw-semibold">v<?= (int) $p['version'] ?></td>
                                            <td><small><?= esc($p['title'] ?: '—') ?></small></td>
                                            <td>
                                                <?php if (!empty($p['is_active'])): ?>
                                                    <span class="badge bg-success badge-soft">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary badge-soft">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><small><?= esc($p['created_at'] ?? '—') ?></small></td>
                                            <td class="text-nowrap">
                                                <button class="btn btn-sm btn-outline-secondary open-editor"
                                                        data-key="<?= esc($key) ?>"
                                                        data-title="<?= esc($p['title'] ?? $label) ?>"
                                                        data-body="<?= esc($p['body'] ?? '', 'attr') ?>">
                                                    <i class="fa-solid fa-pen"></i> Edit
                                                </button>
                                                <?php if (empty($p['is_active'])): ?>
                                                    <button class="btn btn-sm btn-outline-primary activate-prompt" data-id="<?= (int) $p['id'] ?>">
                                                        <i class="fa-solid fa-play me-1"></i> Activate
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-prompt" data-id="<?= (int) $p['id'] ?>">
                                                        <i class="fa-solid fa-trash me-1"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info small mt-3 mb-0">No DB versions yet. Click "New Version" to save one (falls back to the hardcoded default until then).</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Edit / New version modal -->
<div class="modal fade" id="promptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-file-lines me-2" style="color: var(--primary);"></i><span id="promptModalTitle">Edit Prompt</span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="promptForm">
                <?= csrf_field() ?>
                <input type="hidden" name="prompt_key" id="f_key">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" class="form-control" name="title" id="f_title" placeholder="e.g. Sender classifier v3">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Prompt Body</label>
                        <textarea class="form-control font-monospace" name="body" id="f_body" rows="18" style="font-size:.8rem;"></textarea>
                    </div>
                    <small class="text-muted">
                        Use placeholders <code>{sender}</code> &amp; <code>{sms_messages}</code> (classify) or <code>{messages_list}</code> (extract) where dynamic content goes.
                        Saving creates a <strong>new version</strong> and makes it active; the previous version is preserved.
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-floppy-disk me-1"></i> Save New Version</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const promptModalEl = document.getElementById('promptModal');
const promptModal = new bootstrap.Modal(promptModalEl);

document.querySelectorAll('.open-editor').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('promptModalTitle').textContent = this.dataset.title + ' — New Version';
        document.getElementById('f_key').value = this.dataset.key;
        document.getElementById('f_title').value = this.dataset.title;
        document.getElementById('f_body').value = this.dataset.body;
        promptModal.show();
    });
});

document.getElementById('promptForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    fetch('<?= base_url('admin/ml/prompts/save') ?>', { method: 'POST', body: new FormData(this) })
        .then(r => r.json()).then(res => {
            showAlert('Prompt', res.message, res.status === 'ok' ? 'success' : 'danger');
            if (res.status === 'ok') { promptModal.hide(); setTimeout(() => window.location.reload(), 1200); }
        })
        .catch(err => showAlert('Error', err.message, 'danger'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save New Version';
        });
});

function postAction(url, id, confirmMsg, doneMsg) {
    if (!confirm(confirmMsg)) return;
    const data = new FormData();
    data.append('id', id);
    fetch(url, { method: 'POST', body: data })
        .then(r => r.json()).then(res => {
            showAlert('Prompt', res.message, res.status === 'ok' ? 'success' : 'danger');
            if (res.status === 'ok') setTimeout(() => window.location.reload(), 1200);
        })
        .catch(err => showAlert('Error', err.message, 'danger'));
}

document.querySelectorAll('.activate-prompt').forEach(btn => {
    btn.addEventListener('click', () => postAction('<?= base_url('admin/ml/prompts/activate') ?>', btn.dataset.id, 'Activate this prompt version?'));
});
document.querySelectorAll('.delete-prompt').forEach(btn => {
    btn.addEventListener('click', () => postAction('<?= base_url('admin/ml/prompts/delete') ?>', btn.dataset.id, 'Delete this prompt version?'));
});
</script>
<?= $this->endSection() ?>