<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Senders - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .cat-badge { font-size: 0.72rem; padding: 3px 8px; border-radius: 20px; font-weight: 600; }
    .cat-Mobile-Money  { background:#d1fae5; color:#065f46; }
    .cat-Bank          { background:#dbeafe; color:#1e40af; }
    .cat-Fintech       { background:#ede9fe; color:#5b21b6; }
    .cat-SACCO         { background:#fef9c3; color:#854d0e; }
    .cat-Insurance     { background:#fee2e2; color:#991b1b; }
    .cat-Payments-Govt { background:#e0f2fe; color:#0c4a6e; }
    .cat-Other-Finance { background:#f3f4f6; color:#374151; }
    .cat-uncategorized { background:#f3f4f6; color:#9ca3af; }
    #bulkActionBar { display: none; }
    .inline-edit-cat { display: none; }
    .inline-edit-cat.active { display: inline-block; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-star me-2"></i> Senders</h2>
        <p class="text-secondary mb-0">Finance senders recognised by the ML backend. Edit categories or add new ones.</p>
    </div>
    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addSenderModal">
        <i class="fa-solid fa-plus me-1"></i> Add Sender
    </button>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<!-- Add Sender Modal -->
<div class="modal fade" id="addSenderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Add Sender</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="allowedForm">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sender Name</label>
                        <input type="text" class="form-control text-uppercase" name="sender" placeholder="e.g. EQUITY" required>
                        <div class="form-text">Will be automatically uppercased.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <select class="form-select" name="category">
                            <option value="">-- Select Category --</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Bank">Bank</option>
                            <option value="Fintech">Fintech</option>
                            <option value="SACCO">SACCO</option>
                            <option value="Insurance">Insurance</option>
                            <option value="Payments/Govt">Payments/Govt</option>
                            <option value="Other Finance">Other Finance</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-star me-1"></i> Add to Senders</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card settings-card">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'allowed', 'status' => $status]) ?>

        <!-- Filter bar -->
        <div class="row g-2 mb-3 align-items-center">
            <div class="col-md-5">
                <input type="text" class="form-control" id="senderSearch" placeholder="Search sender...">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="Mobile Money">Mobile Money</option>
                    <option value="Bank">Bank</option>
                    <option value="Fintech">Fintech</option>
                    <option value="SACCO">SACCO</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Payments/Govt">Payments/Govt</option>
                    <option value="Other Finance">Other Finance</option>
                    <option value="">Uncategorized</option>
                </select>
            </div>
            <div class="col-md-4 text-end">
                <small class="text-muted"><span id="visibleCount"><?= count($allowed) ?></span> of <?= count($allowed) ?> senders</small>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div id="bulkActionBar" class="mb-3 p-3 rounded border border-primary bg-primary bg-opacity-10 d-flex align-items-center justify-content-between">
            <div class="fw-semibold text-primary small">
                <i class="fa-solid fa-check-square me-1"></i> <span id="selectedCount">0</span> selected
            </div>
            <div class="d-flex align-items-center gap-2">
                <select class="form-select form-select-sm" id="bulkCategory" style="width: auto; min-width: 160px;">
                    <option value="">-- Set Category --</option>
                    <option value="Mobile Money">Mobile Money</option>
                    <option value="Bank">Bank</option>
                    <option value="Fintech">Fintech</option>
                    <option value="SACCO">SACCO</option>
                    <option value="Insurance">Insurance</option>
                    <option value="Payments/Govt">Payments/Govt</option>
                    <option value="Other Finance">Other Finance</option>
                </select>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="applyBulkBtn">
                    <i class="fa-solid fa-check me-1"></i> Apply
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" id="bulkRemoveBtn">
                    <i class="fa-solid fa-trash me-1"></i> Remove
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0" id="allowedTable">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>Sender</th>
                        <th>Category</th>
                        <th>Added</th>
                        <th style="width: 80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allowed)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                No senders found. Click "Add Sender" to get started.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($allowed as $a): ?>
                            <?php
                                $cat = esc($a['category'] ?? '');
                                $catClass = $cat ? 'cat-' . str_replace(['/', ' '], ['-', '-'], $cat) : 'cat-uncategorized';
                            ?>
                            <tr data-sender="<?= esc($a['sender'], 'attr') ?>" data-category="<?= $cat ?>">
                                <td>
                                    <input type="checkbox" class="form-check-input row-checkbox" value="<?= esc($a['sender'], 'attr') ?>">
                                </td>
                                <td class="fw-semibold"><?= esc($a['sender']) ?></td>
                                <td>
                                    <!-- Display mode -->
                                    <span class="cat-display">
                                        <?php if ($cat): ?>
                                            <span class="cat-badge <?= $catClass ?>"><?= $cat ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                        <button class="btn btn-link btn-sm p-0 ms-1 text-muted edit-cat-btn" title="Edit category" style="font-size:0.7rem;">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    </span>
                                    <!-- Inline edit mode -->
                                    <span class="inline-edit-cat">
                                        <select class="form-select form-select-sm d-inline-block w-auto cat-select">
                                            <option value="">-- none --</option>
                                            <option value="Mobile Money" <?= $cat === 'Mobile Money' ? 'selected' : '' ?>>Mobile Money</option>
                                            <option value="Bank" <?= $cat === 'Bank' ? 'selected' : '' ?>>Bank</option>
                                            <option value="Fintech" <?= $cat === 'Fintech' ? 'selected' : '' ?>>Fintech</option>
                                            <option value="SACCO" <?= $cat === 'SACCO' ? 'selected' : '' ?>>SACCO</option>
                                            <option value="Insurance" <?= $cat === 'Insurance' ? 'selected' : '' ?>>Insurance</option>
                                            <option value="Payments/Govt" <?= $cat === 'Payments/Govt' ? 'selected' : '' ?>>Payments/Govt</option>
                                            <option value="Other Finance" <?= $cat === 'Other Finance' ? 'selected' : '' ?>>Other Finance</option>
                                        </select>
                                        <button class="btn btn-success btn-sm py-0 save-cat-btn ms-1"><i class="fa-solid fa-check"></i></button>
                                        <button class="btn btn-light btn-sm py-0 cancel-cat-btn"><i class="fa-solid fa-xmark"></i></button>
                                    </span>
                                </td>
                                <td><small class="text-muted"><?= esc($a['created_at'] ?? '') ?></small></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger remove-btn" data-sender="<?= esc($a['sender'], 'attr') ?>">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="alert alert-info mb-0 small py-2 flex-grow-1 me-3">
                <i class="fa-solid fa-circle-info me-1"></i>
                These senders skip LLM classification and are always treated as finance. The ML backend falls back to its hardcoded list only if this table is empty.
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" id="resetBtn">
                <i class="fa-solid fa-broom me-1"></i> Reset to Defaults
            </button>
        </div>
    </div>
</div>

<script>
const BULK_CATEGORIZE_URL  = '<?= base_url('admin/ml/allowed/bulk-categorize') ?>';
const ALLOWED_ADD_URL      = '<?= base_url('admin/ml/allowed/add') ?>';
const ALLOWED_REMOVE_URL   = '<?= base_url('admin/ml/allowed/remove') ?>';
const ALLOWED_RESET_URL    = '<?= base_url('admin/ml/allowed/reset') ?>';
const ALLOWED_SEED_URL     = '<?= base_url('admin/ml/allowed/seed') ?>';

// ---- Add Sender Form ----
document.getElementById('allowedForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = new FormData(this);
    const btn = this.querySelector('button[type=submit]');
    btn.disabled = true;
    fetch(ALLOWED_ADD_URL, { method: 'POST', body: data })
        .then(r => r.json()).then(res => {
            showAlert('Senders', res.message, res.status === 'success' ? 'success' : 'danger');
            if (res.status === 'success') setTimeout(() => window.location.reload(), 1200);
        })
        .catch(err => showAlert('Error', err.message, 'danger'))
        .finally(() => { btn.disabled = false; });
});

// ---- Remove single ----
document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const sender = this.dataset.sender;
        if (!confirm('Remove "' + sender + '" from the list?')) return;
        const data = new FormData();
        data.append('sender', sender);
        fetch(ALLOWED_REMOVE_URL, { method: 'POST', body: data })
            .then(r => r.json()).then(res => {
                showAlert('Senders', res.message, res.status === 'success' ? 'success' : 'danger');
                if (res.status === 'success') setTimeout(() => window.location.reload(), 1200);
            })
            .catch(err => showAlert('Error', err.message, 'danger'));
    });
});

// ---- Reset / Seed ----
document.getElementById('resetBtn').addEventListener('click', function() {
    if (!confirm('This will re-seed the list with all default finance senders. Existing custom entries will remain. Continue?')) return;
    fetch(ALLOWED_SEED_URL, { method: 'POST' })
        .then(r => r.json()).then(res => {
            showAlert('Senders', res.message, res.status === 'success' ? 'success' : 'danger');
            if (res.status === 'success') setTimeout(() => window.location.reload(), 1200);
        })
        .catch(err => showAlert('Error', err.message, 'danger'));
});

// ---- Bulk Selection ----
const selectAll      = document.getElementById('selectAll');
const bulkActionBar  = document.getElementById('bulkActionBar');
const selectedCount  = document.getElementById('selectedCount');
const applyBulkBtn   = document.getElementById('applyBulkBtn');
const bulkRemoveBtn  = document.getElementById('bulkRemoveBtn');
const bulkCategory   = document.getElementById('bulkCategory');

function getChecked() { return Array.from(document.querySelectorAll('.row-checkbox:checked')); }

function updateBulkBar() {
    const checked = getChecked();
    if (checked.length > 0) {
        bulkActionBar.style.display = 'flex';
        selectedCount.textContent = checked.length;
    } else {
        bulkActionBar.style.display = 'none';
        selectAll.checked = false;
    }
}

selectAll?.addEventListener('change', function() {
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        if (cb.closest('tr').style.display !== 'none') cb.checked = this.checked;
    });
    updateBulkBar();
});

document.querySelectorAll('.row-checkbox').forEach(cb => {
    cb.addEventListener('change', () => updateBulkBar());
});

applyBulkBtn?.addEventListener('click', function() {
    const senders = getChecked().map(cb => cb.value);
    if (senders.length === 0) return;
    applyBulkBtn.disabled = true;
    applyBulkBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
    const data = new FormData();
    senders.forEach(s => data.append('senders[]', s));
    data.append('category', bulkCategory.value);
    fetch(BULK_CATEGORIZE_URL, { method: 'POST', body: data })
        .then(r => r.json()).then(res => {
            showAlert('Bulk Update', res.message, res.status === 'success' ? 'success' : 'danger');
            if (res.status === 'success') setTimeout(() => window.location.reload(), 1200);
        })
        .catch(err => showAlert('Error', err.message, 'danger'))
        .finally(() => { applyBulkBtn.disabled = false; applyBulkBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Apply'; });
});

bulkRemoveBtn?.addEventListener('click', function() {
    const senders = getChecked().map(cb => cb.value);
    if (senders.length === 0) return;
    if (!confirm('Remove ' + senders.length + ' senders?')) return;
    bulkRemoveBtn.disabled = true;
    const data = new FormData();
    senders.forEach(s => data.append('senders[]', s));
    fetch('<?= base_url('admin/ml/allowed/bulk-remove') ?>', { method: 'POST', body: data })
        .then(r => r.json()).then(res => {
            showAlert('Bulk Remove', res.message, res.status === 'success' ? 'success' : 'danger');
            if (res.status === 'success') setTimeout(() => window.location.reload(), 1200);
        })
        .catch(err => showAlert('Error', err.message, 'danger'))
        .finally(() => { bulkRemoveBtn.disabled = false; });
});

// ---- Inline Category Edit ----
document.querySelectorAll('.edit-cat-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const td = this.closest('td');
        td.querySelector('.cat-display').style.display = 'none';
        td.querySelector('.inline-edit-cat').classList.add('active');
    });
});

document.querySelectorAll('.cancel-cat-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const td = this.closest('td');
        td.querySelector('.cat-display').style.display = '';
        td.querySelector('.inline-edit-cat').classList.remove('active');
    });
});

document.querySelectorAll('.save-cat-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const row    = this.closest('tr');
        const sender = row.dataset.sender;
        const cat    = row.querySelector('.cat-select').value;
        btn.disabled = true;
        const data   = new FormData();
        data.append('senders[]', sender);
        data.append('category', cat);
        fetch(BULK_CATEGORIZE_URL, { method: 'POST', body: data })
            .then(r => r.json()).then(res => {
                showAlert('Category', res.message, res.status === 'success' ? 'success' : 'danger');
                if (res.status === 'success') setTimeout(() => window.location.reload(), 800);
            })
            .catch(err => showAlert('Error', err.message, 'danger'))
            .finally(() => { btn.disabled = false; });
    });
});

// ---- Search + Category Filter ----
const searchInput    = document.getElementById('senderSearch');
const categoryFilter = document.getElementById('categoryFilter');
const visibleCount   = document.getElementById('visibleCount');
const rows           = document.querySelectorAll('#allowedTable tbody tr[data-sender]');

function filterTable() {
    const q   = searchInput.value.toLowerCase();
    const cat = categoryFilter.value;
    let visible = 0;
    rows.forEach(row => {
        const senderMatch = row.dataset.sender.toLowerCase().includes(q);
        const catMatch    = !cat || row.dataset.category === cat;
        const show = senderMatch && catMatch;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    visibleCount.textContent = visible;
}

searchInput?.addEventListener('input', filterTable);
categoryFilter?.addEventListener('change', filterTable);
</script>
<?= $this->endSection() ?>
