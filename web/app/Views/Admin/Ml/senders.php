<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> ML Senders - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-address-book me-2"></i> ML Senders</h2>
        <p class="text-secondary mb-0">Override the finance flag for specific senders (e.g. mark a sender as non-finance).</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?= view('Admin/Ml/_status', ['status' => $status]) ?>

<div class="card settings-card">
    <div class="card-body p-4">
        <?= view('Admin/Ml/_nav', ['active' => 'senders']) ?>

        <?php if (empty($senders)): ?>
            <div class="alert alert-info mb-0">No sender profiles found yet. Senders appear after ML analysis has run.</div>
        <?php else: ?>
            <div class="mb-3">
                <input type="text" class="form-control" id="senderSearch" placeholder="Search sender number or name...">
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle" id="sendersTable">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Profiles</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($senders as $s): ?>
                        <tr>
                            <td class="fw-semibold sender-number"><?= esc($s['number']) ?></td>
                            <td><?= esc($s['name']) ?: '<span class="text-muted">—</span>' ?></td>
                            <td><?= esc($s['category']) ?: '<span class="text-muted">—</span>' ?></td>
                            <td><?= $s['total'] ?></td>
                            <td>
                                <?php if ($s['is_finance']): ?>
                                    <span class="badge bg-success">Finance</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Non-finance</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($s['is_finance']): ?>
                                    <button class="btn btn-sm btn-outline-secondary set-finance" data-sender="<?= esc($s['number']) ?>" data-is-finance="0">
                                        <i class="fa-solid fa-ban me-1"></i> Set Non-finance
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-success set-finance" data-sender="<?= esc($s['number']) ?>" data-is-finance="1">
                                        <i class="fa-solid fa-check me-1"></i> Set Finance
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-3 mb-0 small">
                <i class="fa-solid fa-circle-info me-1"></i>
                Changing a sender to non-finance hides its transactions from financial views. The flag is applied to all sender profiles and existing SMS classifications for that sender.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('senderSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#sendersTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

function showToast(title, icon = 'success') {
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        Toast.fire({ icon, title });
    } else {
        showAlert('Sender', title, icon);
    }
}

function attachSetFinanceHandler(btn) {
    btn.addEventListener('click', function() {
        const sender = this.dataset.sender;
        const isFinance = this.dataset.isFinance === '1';
        const action = isFinance ? 'finance' : 'non-finance';
        const row = this.closest('tr');
        const self = this;

        const doToggle = () => {
            const originalHtml = self.innerHTML;
            self.disabled = true;
            self.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';

            const data = new FormData();
            data.append('sender', sender);
            data.append('is_finance', isFinance ? '1' : '0');
            fetch('<?= base_url('admin/ml/senders/set-finance') ?>', { method: 'POST', body: data })
                .then(r => r.json()).then(res => {
                    self.disabled = false;
                    if (res.status === 'success') {
                        showToast(res.message, 'success');
                        if (row) {
                            const statusCell = row.querySelector('td:nth-child(5)');
                            if (statusCell) {
                                statusCell.innerHTML = isFinance
                                    ? '<span class="badge bg-success">Finance</span>'
                                    : '<span class="badge bg-secondary">Non-finance</span>';
                            }
                            if (isFinance) {
                                self.className = 'btn btn-sm btn-outline-secondary set-finance';
                                self.dataset.isFinance = '0';
                                self.innerHTML = '<i class="fa-solid fa-ban me-1"></i> Set Non-finance';
                            } else {
                                self.className = 'btn btn-sm btn-outline-success set-finance';
                                self.dataset.isFinance = '1';
                                self.innerHTML = '<i class="fa-solid fa-check me-1"></i> Set Finance';
                            }
                            row.classList.add('table-success');
                            setTimeout(() => row.classList.remove('table-success'), 1500);
                        }
                    } else {
                        self.innerHTML = originalHtml;
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', res.message, 'error');
                        } else {
                            showAlert('Error', res.message, 'danger');
                        }
                    }
                })
                .catch(err => {
                    self.disabled = false;
                    self.innerHTML = originalHtml;
                    showToast(err.message || 'Network error', 'error');
                });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: isFinance ? 'Mark as Finance?' : 'Mark as Non-Finance?',
                text: `Mark sender "${sender}" as ${action}? This updates all profiles and SMS classifications for this sender.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: isFinance ? '#198754' : '#6c757d',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, Set ${isFinance ? 'Finance' : 'Non-Finance'}`,
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) doToggle();
            });
        } else {
            if (confirm('Mark sender "' + sender + '" as ' + action + '? This updates all profiles and SMS classifications for this sender.')) {
                doToggle();
            }
        }
    });
}

document.querySelectorAll('.set-finance').forEach(attachSetFinanceHandler);
</script>
<?= $this->endSection() ?>
