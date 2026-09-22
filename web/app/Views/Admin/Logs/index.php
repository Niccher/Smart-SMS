<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Log Viewer - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .log-output { font-family: 'Monospace', monospace; background: #0f172a; color: #e2e8f0; border-radius: 4px; padding: 1rem; max-height: 600px; overflow: auto; font-size: 0.8rem; white-space: pre-wrap; word-break: break-word; line-height: 1.5; }
    [data-bs-theme="dark"] .log-output { background: #000; }
    .log-line { border-bottom: 1px solid rgba(255,255,255,0.03); }
    .log-line:last-child { border-bottom: none; }
    .file-row { cursor: pointer; }
    .file-row:hover, .file-row.active { background: rgba(177,184,237,0.15); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-file-lines me-2"></i> Log Viewer</h2>
        <p class="text-secondary mb-0">Browse and search application log files.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="card settings-card mb-4">
    <div class="card-body p-4 pb-0">
        <?= view('Admin/System/_nav', ['active' => 'logs']) ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card settings-card h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3" style="color: var(--primary);"><i class="fa-solid fa-folder-open me-2"></i> Log Files</h5>
                <?php if (empty($log_files)): ?>
                    <div class="text-center text-muted p-4">
                        <i class="fa-solid fa-inbox fs-1 d-block mb-2"></i>
                        No log files found in <code>writable/logs/</code>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush" id="fileList">
                        <?php foreach ($log_files as $idx => $file): ?>
                            <button type="button" class="list-group-item list-group-item-action file-row d-flex justify-content-between align-items-center"
                                data-file="<?= esc($file['name']) ?>"
                                data-human-size="<?= esc($file['human_size']) ?>"
                                data-modified="<?= esc($file['modified']) ?>"
                                <?= $idx === 0 ? 'active' : '' ?>>
                                <div>
                                    <strong class="d-block text-truncate" style="max-width: 200px;"><?= esc($file['name']) ?></strong>
                                    <small class="text-muted"><?= esc($file['human_size']) ?> · <?= esc($file['modified']) ?></small>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card settings-card h-100 d-flex flex-column">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="fw-bold mb-0" style="color: var(--primary);"><i class="fa-solid fa-terminal me-2"></i> <span id="currentFileName">Select a log file</span></h5>
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="refreshBtn" title="Refresh"><i class="fa-solid fa-rotate"></i></button>
                        <button type="button" class="btn btn-outline-success btn-sm" id="copyBtn" title="Copy to Clipboard" disabled><i class="fa-solid fa-copy"></i></button>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="downloadBtn" title="Download" disabled><i class="fa-solid fa-download"></i></button>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="deleteFileBtn" title="Delete this file" disabled><i class="fa-solid fa-trash"></i></button>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="deleteAllBtn" title="Delete all log files"><i class="fa-solid fa-trash-can"></i></button>
                        <div class="form-check form-switch d-flex align-items-center mb-0">
                            <input class="form-check-input" type="checkbox" id="autoRefresh" value="1">
                            <label class="form-check-label small fw-semibold ms-1 mb-0" for="autoRefresh">Auto (5s)</label>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-3" id="controls" style="display:none;">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Lines</label>
                        <select class="form-select form-select-sm" id="linesSelect">
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="200" selected>200</option>
                            <option value="500">500</option>
                            <option value="1000">1000</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Level</label>
                        <select class="form-select form-select-sm" id="levelSelect">
                            <option value="">All Levels</option>
                            <option value="DEBUG">DEBUG</option>
                            <option value="INFO">INFO</option>
                            <option value="WARNING">WARNING</option>
                            <option value="ERROR">ERROR</option>
                            <option value="CRITICAL">CRITICAL</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Search</label>
                        <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Filter text...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary btn-sm w-100" id="applyFiltersBtn"><i class="fa-solid fa-filter me-1"></i> Apply</button>
                    </div>
                </div>

                <!-- Quick Filter Level Pills -->
                <div class="mb-3 align-items-center gap-1 flex-wrap" id="levelPillsContainer" style="display: none !important;">
                    <span class="text-secondary small fw-semibold me-1">Quick Levels:</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 active pill-level" data-level="">All</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 pill-level" data-level="DEBUG">Debug</button>
                    <button type="button" class="btn btn-sm btn-outline-info py-0 px-2 pill-level" data-level="INFO">Info</button>
                    <button type="button" class="btn btn-sm btn-outline-warning py-0 px-2 pill-level" data-level="WARNING">Warning</button>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 pill-level" data-level="ERROR">Error</button>
                    <button type="button" class="btn btn-sm btn-danger text-white py-0 px-2 pill-level" data-level="CRITICAL">Critical</button>
                </div>

                <div class="log-output" id="logOutput" tabindex="0">
                    <div class="text-muted text-center p-4" style="color: #64748b;">
                        <i class="fa-solid fa-file-lines fs-1 d-block mb-2"></i>
                        Select a log file from the list to view its contents.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

let currentFile = '';
let autoRefreshTimer = null;

const fileList = document.getElementById('fileList');
const logOutput = document.getElementById('logOutput');
const currentFileName = document.getElementById('currentFileName');
const downloadBtn = document.getElementById('downloadBtn');
const copyBtn = document.getElementById('copyBtn');
const deleteFileBtn = document.getElementById('deleteFileBtn');
const deleteAllBtn = document.getElementById('deleteAllBtn');
const refreshBtn = document.getElementById('refreshBtn');
const linesSelect = document.getElementById('linesSelect');
const levelSelect = document.getElementById('levelSelect');
const searchInput = document.getElementById('searchInput');
const applyFiltersBtn = document.getElementById('applyFiltersBtn');
const autoRefresh = document.getElementById('autoRefresh');
const controls = document.getElementById('controls');
const levelPillsContainer = document.getElementById('levelPillsContainer');

function loadLog(file) {
    currentFile = file;
    const params = new URLSearchParams({
        lines: linesSelect.value,
        level: levelSelect.value,
        search: searchInput.value
    });
    
    logOutput.innerHTML = '<div class="text-center text-muted p-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Loading...</div></div>';
    currentFileName.textContent = file;
    downloadBtn.disabled = false;
    copyBtn.disabled = false;
    deleteFileBtn.disabled = false;
    controls.style.display = 'flex';
    if (levelPillsContainer) {
        levelPillsContainer.style.setProperty('display', 'flex', 'important');
    }

    fetch('<?= base_url('admin/system/logs/view') ?>/' + encodeURIComponent(file) + '?' + params)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                logOutput.innerHTML = res.content || '<div class="text-muted text-center p-4">No matching lines.</div>';
                logOutput.scrollTop = logOutput.scrollHeight;
                
                // Sync quick level pills active state
                document.querySelectorAll('.pill-level').forEach(btn => {
                    if (btn.dataset.level === levelSelect.value) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            } else {
                logOutput.innerHTML = '<div class="text-danger p-4">Error: ' + res.message + '</div>';
            }
        })
        .catch(() => {
            logOutput.innerHTML = '<div class="text-danger p-4">Failed to load log.</div>';
        });
}

function startAutoRefresh() {
    if (autoRefresh.checked && currentFile) {
        autoRefreshTimer = setInterval(() => loadLog(currentFile), 5000);
    } else {
        clearInterval(autoRefreshTimer);
        autoRefreshTimer = null;
    }
}

fileList?.addEventListener('click', e => {
    const btn = e.target.closest('.file-row');
    if (!btn) return;
    fileList.querySelectorAll('.file-row').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');
    loadLog(btn.dataset.file);
});

// Quick level pills filtering
document.querySelectorAll('.pill-level').forEach(btn => {
    btn.addEventListener('click', function() {
        levelSelect.value = this.dataset.level;
        if (currentFile) loadLog(currentFile);
    });
});

// Copy to clipboard handler
copyBtn?.addEventListener('click', () => {
    if (!currentFile) return;
    const text = logOutput.innerText;
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            title: 'Copied!',
            text: 'Log traces copied to clipboard.',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
    });
});

[linesSelect, levelSelect, searchInput].forEach(el => {
    el.addEventListener('change', () => {
        if (currentFile) loadLog(currentFile);
    });
});

applyFiltersBtn?.addEventListener('click', () => {
    if (currentFile) loadLog(currentFile);
});

refreshBtn?.addEventListener('click', () => {
    if (currentFile) loadLog(currentFile);
});

downloadBtn?.addEventListener('click', () => {
    if (currentFile) {
        window.location.href = '<?= base_url('admin/system/logs/download') ?>/' + encodeURIComponent(currentFile);
    }
});

deleteFileBtn?.addEventListener('click', async () => {
    if (!currentFile) return;
    const result = await confirmAction(
        'Delete Log File?',
        `This will permanently delete:\n${currentFile}\n\nThis action CANNOT be undone.`,
        'warning',
        'Yes, delete'
    );
    if (!result.isConfirmed) return;

    try {
        const res = await fetch('<?= base_url('admin/system/logs/delete') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'file=' + encodeURIComponent(currentFile)
        });
        const data = await res.json();
        if (data.status === 'success') {
            Swal.fire('Deleted!', data.message, 'success');
            // Remove from file list
            const btn = fileList.querySelector(`[data-file="${currentFile}"]`);
            if (btn) btn.remove();
            // Clear current view
            currentFile = '';
            currentFileName.textContent = 'Select a log file';
            downloadBtn.disabled = true;
            deleteFileBtn.disabled = true;
            controls.style.display = 'none';
            logOutput.innerHTML = '<div class="text-muted text-center p-4"><i class="fa-solid fa-file-lines fs-1 d-block mb-2"></i>Select a log file from the list to view its contents.</div>';
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Failed to delete: ' + e.message, 'error');
    }
});

deleteAllBtn?.addEventListener('click', async () => {
    const result = await confirmAction(
        'Delete ALL Log Files?',
        'This will permanently delete ALL log files in writable/logs/.\n\nThis action CANNOT be undone.',
        'error',
        'Yes, delete ALL'
    );
    if (!result.isConfirmed) return;

    try {
        const res = await fetch('<?= base_url('admin/system/logs/delete-all') ?>', { method: 'POST' });
        const data = await res.json();
        if (data.status === 'success') {
            Swal.fire('Deleted!', data.message, 'success');
            // Clear file list
            fileList.innerHTML = '<div class="text-center text-muted p-4"><i class="fa-solid fa-inbox fs-1 d-block mb-2"></i>No log files found in <code>writable/logs/</code></div>';
            currentFile = '';
            currentFileName.textContent = 'Select a log file';
            downloadBtn.disabled = true;
            deleteFileBtn.disabled = true;
            controls.style.display = 'none';
            logOutput.innerHTML = '<div class="text-muted text-center p-4"><i class="fa-solid fa-file-lines fs-1 d-block mb-2"></i>Select a log file from the list to view its contents.</div>';
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Failed to delete: ' + e.message, 'error');
    }
});

autoRefresh?.addEventListener('change', startAutoRefresh);

// Cleanup on page unload
window.addEventListener('beforeunload', () => clearInterval(autoRefreshTimer));
</script>
<?= $this->endSection() ?>