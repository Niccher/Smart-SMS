<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Test Prompt - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .result-block { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 4px; padding: 1rem; }
    .result-block pre { margin: 0; white-space: pre-wrap; word-break: break-word; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-flask me-2"></i> Test Prompt</h2>
        <p class="text-secondary mb-0">Send a sample SMS through the LLM to verify classification & extraction.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="card settings-card">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'test', 'status' => $status]) ?>

        <form id="testForm" class="row g-3 mb-4">
            <?= csrf_field() ?>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Sender</label>
                <input type="text" class="form-control" name="sender" value="MPESA" placeholder="e.g. MPESA">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Test SMS Message</label>
                <textarea class="form-control" name="message" rows="3" placeholder="Ksh 1,200.00 sent to John for XR4K9L2. New balance: Ksh 25,300.50. M-PESA."></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"><i class="fa-solid fa-play me-1"></i> Run Test</button>
            </div>
        </form>

        <div id="testResult" class="d-none">
            <h6 class="fw-bold text-uppercase text-secondary mb-3" style="font-size:0.75rem; letter-spacing:1px;">LLM Response</h6>
            <div class="row g-3">
                <div class="col-lg-5">
                    <div class="result-block">
                        <div class="fw-bold mb-2"><i class="fa-solid fa-tags me-1"></i> Classification</div>
                        <pre id="classificationOutput">Loading...</pre>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="result-block">
                        <div class="fw-bold mb-2"><i class="fa-solid fa-file-lines me-1"></i> Extraction</div>
                        <pre id="extractionOutput">Loading...</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('testForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const data = new FormData(this);
    const btn = this.querySelector('button[type=submit]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Analyzing...';
    fetch('<?= base_url('admin/ml/test/run') ?>', { method: 'POST', body: data })
        .then(r => r.json()).then(res => {
            if (res.status === 'ok' && res.result) {
                const result = res.result;
                document.getElementById('testResult').classList.remove('d-none');
                document.getElementById('classificationOutput').textContent = JSON.stringify(result.classification || {}, null, 2);
                document.getElementById('extractionOutput').textContent = JSON.stringify((result.extractions || []).map(e => e || {}), null, 2);
                showAlert('Test Prompt', res.message, 'success');
            } else {
                document.getElementById('testResult').classList.add('d-none');
                showAlert('Test Prompt', res.message || 'Failed', 'danger');
            }
        })
        .catch(err => showAlert('Error', err.message, 'danger'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-play me-1"></i> Run Test';
        });
});
</script>
<?= $this->endSection() ?>
