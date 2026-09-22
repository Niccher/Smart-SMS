<?= $this->extend('Layouts/admin') ?>
<?= $this->section('title') ?> Blocklist Status - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .status-card { border: none; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; border-left: 4px solid var(--card-color, #6c757d); }
    .status-card:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
    .status-icon { width: 48px; height: 48px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
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

        <div class="mb-4">
            <h5 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-chart-pie me-2"></i>Blocklist Overview</h5>
            <p class="text-muted small mb-0">Overview of SMS records, senders, and classification categories. Click a card to learn more.</p>
        </div>

        <div class="row g-3 mb-5">
            <!-- All SMS -->
            <div class="col-md-6 col-lg-3">
                <div class="card status-card h-100" style="--card-color: #0d6efd;" 
                     data-bs-toggle="modal" data-bs-target="#statusExplanationModal"
                     data-title="All SMS"
                     data-desc="The total number of SMS messages uploaded to your account. This includes financial transactions, promo text alerts, spam, network warnings, and personal messages.">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="status-icon bg-primary-subtle text-primary"><i class="fa-solid fa-message"></i></div>
                        <div>
                            <div class="fw-bold fs-4 lh-1"><?= number_format($stats['all_sms']) ?></div>
                            <div class="fw-semibold small mt-1 text-secondary">All SMS</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Good SMS -->
            <div class="col-md-6 col-lg-3">
                <div class="card status-card h-100" style="--card-color: #198754;"
                     data-bs-toggle="modal" data-bs-target="#statusExplanationModal"
                     data-title="Good SMS (Finance)"
                     data-desc="The number of SMS messages classified as financial transactions (e.g. deposits, transfers, airtime, banking services). These are parsed to build your financial intelligence ledger.">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="status-icon bg-success-subtle text-success"><i class="fa-solid fa-circle-check"></i></div>
                        <div>
                            <div class="fw-bold fs-4 lh-1"><?= number_format($stats['good_sms']) ?></div>
                            <div class="fw-semibold small mt-1 text-secondary">Good SMS</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bad SMS -->
            <div class="col-md-6 col-lg-3">
                <div class="card status-card h-100" style="--card-color: #fd7e14;"
                     data-bs-toggle="modal" data-bs-target="#statusExplanationModal"
                     data-title="Bad SMS (Non-Finance)"
                     data-desc="SMS messages that are classified as non-financial. This includes advertisement alerts, spam, subscriptions, and personal messages. They are skipped during transaction details extraction.">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="status-icon bg-warning-subtle text-warning"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div>
                            <div class="fw-bold fs-4 lh-1"><?= number_format($stats['bad_sms']) ?></div>
                            <div class="fw-semibold small mt-1 text-secondary">Bad SMS</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Senders -->
            <div class="col-md-6 col-lg-3">
                <div class="card status-card h-100" style="--card-color: #6f42c1;"
                     data-bs-toggle="modal" data-bs-target="#statusExplanationModal"
                     data-title="All Senders"
                     data-desc="The total number of unique phone numbers or alphanumeric sender names (like MPESA, SAFARICOM, NCBA_BANK) found across all uploaded messages in your database.">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="status-icon bg-purple-subtle text-purple" style="background-color:rgba(111, 66, 193, 0.1); color:#6f42c1;"><i class="fa-solid fa-address-book"></i></div>
                        <div>
                            <div class="fw-bold fs-4 lh-1"><?= number_format($stats['all_senders']) ?></div>
                            <div class="fw-semibold small mt-1 text-secondary">All Senders</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Finance Senders -->
            <div class="col-md-6 col-lg-4">
                <div class="card status-card h-100" style="--card-color: #198754;"
                     data-bs-toggle="modal" data-bs-target="#statusExplanationModal"
                     data-title="Finance Senders"
                     data-desc="Known financial senders in the database. SMS from these senders are marked for transactional parsing. You can review and block them on the 'Allowed' tab.">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="status-icon bg-success-subtle text-success"><i class="fa-solid fa-star"></i></div>
                        <div>
                            <div class="fw-bold fs-4 lh-1"><?= number_format($stats['finance_senders']) ?></div>
                            <div class="fw-semibold small mt-1 text-secondary">Finance Senders</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unverified Senders -->
            <div class="col-md-6 col-lg-4">
                <div class="card status-card h-100" style="--card-color: #6c757d;"
                     data-bs-toggle="modal" data-bs-target="#statusExplanationModal"
                     data-title="Unverified Senders"
                     data-desc="Senders whose financial or transaction status has not yet been defined. They are automatically queued for LLM classification. You can block or allow them on the 'Unknown' tab.">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="status-icon bg-secondary-subtle text-secondary"><i class="fa-solid fa-question-circle"></i></div>
                        <div>
                            <div class="fw-bold fs-4 lh-1"><?= number_format($stats['unverified_senders']) ?></div>
                            <div class="fw-semibold small mt-1 text-secondary">Unverified Senders</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blocked Senders -->
            <div class="col-md-6 col-lg-4">
                <div class="card status-card h-100" style="--card-color: #dc3545;"
                     data-bs-toggle="modal" data-bs-target="#statusExplanationModal"
                     data-title="Blocked Senders"
                     data-desc="Senders you have manually excluded. All SMS messages from blocked senders are skipped, excluded from processing, and bypass the LLM pipeline. You can review them on the 'Blocked' tab.">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="status-icon bg-danger-subtle text-danger"><i class="fa-solid fa-ban"></i></div>
                        <div>
                            <div class="fw-bold fs-4 lh-1"><?= number_format($stats['blocked_senders']) ?></div>
                            <div class="fw-semibold small mt-1 text-secondary">Blocked Senders</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-top pt-4">
            <h5 class="fw-bold text-danger mb-2"><i class="fa-solid fa-trash-can me-2"></i>Cleanup Options</h5>
            <p class="text-secondary small mb-3">Permanently delete messages in your database associated with unwanted senders.</p>
            
            <div class="p-3 bg-light rounded d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="fw-bold small text-dark">Delete SMS from Unwanted (Blocked) Senders</div>
                    <div class="text-muted small">This will search all your uploaded SMS and delete messages sent by any address currently in your Blocked tab.</div>
                </div>
                <button type="button" id="btnDeleteUnwanted" class="btn btn-danger btn-sm rounded px-3 fw-semibold"
                        data-blocked-sms="<?= (int)($stats['blocked_sms'] ?? 0) ?>"
                        data-blocked-senders="<?= (int)($stats['blocked_senders'] ?? 0) ?>"
                        data-all-sms="<?= (int)($stats['all_sms'] ?? 0) ?>"
                        data-good-sms="<?= (int)($stats['good_sms'] ?? 0) ?>"
                        data-bad-sms="<?= (int)($stats['bad_sms'] ?? 0) ?>">
                    <i class="fa-solid fa-trash me-1"></i>Delete Messages
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Explanation Modal -->
<div class="modal fade" id="statusExplanationModal" tabindex="-1" aria-labelledby="statusExplanationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark" id="statusExplanationModalLabel">Card Info</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p id="modalExplanationText" class="text-secondary mb-0" style="line-height: 1.6;"></p>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary rounded px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const statusExplanationModal = document.getElementById('statusExplanationModal');
if (statusExplanationModal) {
    statusExplanationModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const title = button.getAttribute('data-title');
        const desc = button.getAttribute('data-desc');
        
        const modalTitle = statusExplanationModal.querySelector('.modal-title');
        const modalBodyText = statusExplanationModal.querySelector('#modalExplanationText');
        
        modalTitle.textContent = title;
        modalBodyText.textContent = desc;
    });
}

document.getElementById('btnDeleteUnwanted')?.addEventListener('click', function() {
    const blockedSms = parseInt(this.getAttribute('data-blocked-sms')) || 0;
    const blockedSenders = parseInt(this.getAttribute('data-blocked-senders')) || 0;
    const allSms = parseInt(this.getAttribute('data-all-sms')) || 0;
    const goodSms = parseInt(this.getAttribute('data-good-sms')) || 0;
    const badSms = parseInt(this.getAttribute('data-bad-sms')) || 0;

    if (blockedSms === 0) {
        Swal.fire({
            title: 'No Messages to Delete',
            text: 'There are currently 0 messages in your database from senders on your Blocked list.',
            icon: 'info',
            confirmButtonColor: '#6c757d'
        });
        return;
    }

    const formatNum = (num) => new Intl.NumberFormat().format(num);

    Swal.fire({
        title: 'Delete Unwanted SMS?',
        html: `
            <div class="text-start">
                <p class="mb-3 text-secondary small">This will permanently delete all SMS from senders currently in your Blocked list. This action cannot be undone.</p>
                
                <div class="alert alert-danger py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                    <div>
                        <strong>To be deleted:</strong> <span class="badge bg-danger">${formatNum(blockedSms)} SMS</span> from <strong>${formatNum(blockedSenders)} blocked sender(s)</strong>.
                    </div>
                </div>
                
                <h6 class="fw-bold mb-2 text-dark small" style="letter-spacing:0.05em; text-transform:uppercase;">Database Impact Statistics</h6>
                <table class="table table-sm table-bordered text-center small mb-0 align-middle">
                    <thead>
                        <tr class="table-light">
                            <th>Metric</th>
                            <th>Before</th>
                            <th>After Deletion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start fw-semibold">Total SMS</td>
                            <td>${formatNum(allSms)}</td>
                            <td class="text-danger fw-bold">${formatNum(allSms - blockedSms)}</td>
                        </tr>
                        <tr>
                            <td class="text-start fw-semibold">Finance SMS</td>
                            <td class="text-success">${formatNum(goodSms)}</td>
                            <td class="text-success fw-semibold">${formatNum(goodSms)}</td>
                        </tr>
                        <tr>
                            <td class="text-start fw-semibold">Non-Finance SMS</td>
                            <td class="text-muted">${formatNum(badSms)}</td>
                            <td class="text-danger fw-bold">${formatNum(badSms - blockedSms)}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete Them!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            this.disabled = true;
            fetch('<?= base_url('dashboard/blocklist/delete-unwanted-sms') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                    this.disabled = false;
                }
            })
            .catch(err => {
                Swal.fire('Error', 'Failed to complete request: ' + err.message, 'error');
                this.disabled = false;
            });
        }
    });
});
</script>

<?= $this->endSection() ?>
