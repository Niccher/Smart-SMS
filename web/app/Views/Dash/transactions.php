<?= $this->extend('Layouts/admin') ?>

<?= $this->section('title') ?> Unified Ledger - Mpesa Analyzer <?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .trx-card {
        background: var(--card-bg);
        border-radius: 4px;
        border: 1px solid var(--card-border);
    }
    .badge { font-weight: 600; padding: 0.4em 0.8em; }
    .badge-dir { font-size: 0.65rem; padding: 0.2em 0.5em; }
    .page-item.active .page-link {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    .page-link { color: var(--primary); border-radius: 4px !important; margin: 0 2px; }
    .filter-btn.active { box-shadow: 0 4px 12px rgba(93, 95, 239, 0.25); }
    .pagination { margin: 0; }
    .table tbody tr:hover { background: rgba(93,95,239,0.03); }
    .form-control, .form-select {
        border-radius: 4px;
        padding: 0.5rem 0.8rem;
        border: 1px solid #e0e0e0;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);">Transaction Ledger</h2>
        <p class="text-secondary mb-0">
            Showing <strong><?= number_format(($page - 1) * $perPage + 1) ?>–<?= number_format(min($page * $perPage, $total)) ?></strong>
            of <strong><?= number_format($total) ?></strong> records
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2 mb-1">
        <a href="<?= base_url('dashboard/transactions?category=finances') ?>" class="btn btn-outline-dark filter-btn btn-sm rounded-pill px-3 <?= $category === 'finances' ? 'active' : '' ?>"><i class="fa-solid fa-coins me-1"></i> All Finances</a>
        <a href="<?= base_url('dashboard/transactions?category=money_in') ?>" class="btn btn-outline-primary filter-btn btn-sm rounded-pill px-3 <?= $category === 'money_in' ? 'active' : '' ?>"><i class="fa-solid fa-arrow-down me-1"></i> Money In</a>
        <a href="<?= base_url('dashboard/transactions?category=money_out') ?>" class="btn btn-outline-danger filter-btn btn-sm rounded-pill px-3 <?= $category === 'money_out' ? 'active' : '' ?>"><i class="fa-solid fa-arrow-up me-1"></i> Money Out</a>
        <a href="<?= base_url('dashboard/transactions?category=notifications') ?>" class="btn btn-outline-secondary filter-btn btn-sm rounded-pill px-3 <?= $category === 'notifications' ? 'active' : '' ?>"><i class="fa-solid fa-bell me-1"></i> Notifications</a>
        
        <div class="vr mx-1 d-none d-md-block"></div>
        
        <button onclick="exportTableToPDF('transactionsTable', 'transactions_ledger', this)" class="btn btn-dark filter-btn btn-sm rounded-pill px-3 shadow-sm">
            <i class="fa-solid fa-file-pdf me-1"></i> PDF
        </button>
        <button onclick="submitExport()" class="btn btn-dark filter-btn btn-sm rounded-pill px-3 shadow-sm">
            <i class="fa-solid fa-file-csv me-1"></i> CSV
        </button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $cs = currency_symbol(); ?>

<!-- Advanced Search Filters Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form method="GET" action="<?= base_url('dashboard/transactions') ?>" id="filterForm" class="row g-3">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase text-secondary">Search Keyword</label>
                <input type="text" name="search" class="form-control" placeholder="Search message details..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-secondary">Sender</label>
                <select name="sender" class="form-select">
                    <option value="">All Senders</option>
                    <?php foreach ($senders as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>" <?= ($filters['sender'] ?? '') === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase text-secondary">Date Range</label>
                <input type="text" id="reportrange" class="form-control" placeholder="Select dates...">
                <input type="hidden" name="date_from" id="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                <input type="hidden" name="date_to" id="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-secondary">Min/Max Amount</label>
                <div class="input-group">
                    <input type="number" name="min_amount" class="form-control" placeholder="Min" value="<?= htmlspecialchars($filters['min_amount'] ?? '') ?>">
                    <input type="number" name="max_amount" class="form-control" placeholder="Max" value="<?= htmlspecialchars($filters['max_amount'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-secondary">Type</label>
                <select name="transaction_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="transfer" <?= ($filters['transaction_type'] ?? '') === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                    <option value="payment" <?= ($filters['transaction_type'] ?? '') === 'payment' ? 'selected' : '' ?>>Payment</option>
                    <option value="withdrawal" <?= ($filters['transaction_type'] ?? '') === 'withdrawal' ? 'selected' : '' ?>>Withdrawal</option>
                    <option value="deposit" <?= ($filters['transaction_type'] ?? '') === 'deposit' ? 'selected' : '' ?>>Deposit</option>
                    <option value="loan" <?= ($filters['transaction_type'] ?? '') === 'loan' ? 'selected' : '' ?>>Loan</option>
                    <option value="repayment" <?= ($filters['transaction_type'] ?? '') === 'repayment' ? 'selected' : '' ?>>Repayment</option>
                </select>
            </div>
            <div class="col-12 text-end mt-3">
                <a href="<?= base_url('dashboard/transactions') ?>" class="btn btn-light me-2 px-3 btn-sm">Reset</a>
                <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                    <i class="fa-solid fa-magnifying-glass me-2"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ledger Table -->
<div class="card trx-card mb-4">
    <div class="card-body p-3">
        <div class="table-responsive">
            <table id="transactionsTable" class="table table-bordered table-hover table-striped w-100 align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Date &amp; Time</th>
                        <th>Category / Sender</th>
                        <th>Amount &amp; Fee</th>
                        <th>Indicators</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $tx): ?>
                            <?php
                                $dir = strtolower($tx->sms_direction ?? '');
                                $cat = $tx->sms_category ?? 'Unclassified';
                                $amt = (float)($tx->sms_amount ?? 0);
                                $fee = (float)($tx->sms_fee ?? 0);

                                if (in_array($dir, ['incoming', 'received', 'money_in', 'in'])) {
                                    $badgeClass = 'bg-success';
                                    $dirLabel = '↓ Money In';
                                } elseif (in_array($dir, ['outgoing', 'sent', 'money_out', 'out'])) {
                                    $badgeClass = 'bg-danger';
                                    $dirLabel = '↑ Money Out';
                                } else {
                                    $badgeClass = 'bg-secondary';
                                    $dirLabel = '— Notification';
                                }

                                $body = base64_decode($tx->sms_body);
                                $bodyStr = mb_check_encoding($body, 'UTF-8') ? $body : "Unreadable content";

                                $ts = is_numeric($tx->sms_time) && $tx->sms_time > 1000000000000
                                    ? (int)($tx->sms_time / 1000)
                                    : (is_numeric($tx->sms_time) ? (int)$tx->sms_time : strtotime($tx->sms_time));
                                $datePart = date('D, M d, Y', $ts);
                                $timePart = date('h:i A', $ts);
                            ?>
                            <tr>
                                <td class="ps-4" style="min-width:120px;">
                                    <div class="fw-semibold small lh-1"><?= $datePart ?></div>
                                    <div class="text-muted" style="font-size:0.7rem; line-height:1;"><?= $timePart ?></div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <span class="badge rounded-pill <?= $badgeClass ?> badge-dir w-auto"><?= $dirLabel ?></span>
                                        <span class="small text-secondary fw-semibold" style="font-size:0.75rem;"><?= htmlspecialchars($cat) ?></span>
                                        <span class="font-monospace small text-primary" style="font-size:0.72rem;"><?= htmlspecialchars($tx->sms_number) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-bold small <?= $dir === 'incoming' || $dir === 'received' ? 'text-success' : 'text-danger' ?>">
                                            <?= ($dir === 'incoming' || $dir === 'received') ? '+' : '-' ?><?= $cs ?> <?= number_format($amt, 2) ?>
                                        </span>
                                    </div>
                                    <?php if ($fee > 0): ?>
                                        <div class="text-muted" style="font-size:0.72rem;">
                                            Fee: <?= $cs ?><?= number_format($fee, 2) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <?php if (!empty($tx->sms_is_reversal)): ?>
                                            <span class="badge bg-warning text-dark badge-dir w-auto"><i class="fa-solid fa-rotate-left"></i> Reversal</span>
                                        <?php endif; ?>
                                        <?php if (!empty($tx->sms_is_loan)): ?>
                                            <span class="badge bg-info text-white badge-dir w-auto"><i class="fa-solid fa-credit-card"></i> Loan</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-muted small" style="word-break: break-word;">
                                            <?= htmlspecialchars($bodyStr) ?>
                                        </span>
                                        <?php if (!empty($tx->sms_counterparty) && $tx->sms_counterparty !== 'Unknown'): ?>
                                        <button class="btn btn-sm btn-outline-primary rounded-pill shadow-sm flex-shrink-0 py-0 px-2 fw-semibold recategorize-btn"
                                                data-trans-id="<?= $tx->id ?>"
                                                data-counterparty="<?= htmlspecialchars($tx->sms_counterparty) ?>"
                                                data-category="<?= htmlspecialchars($tx->sms_category ?? '') ?>"
                                                title="Smart Auto-Fix Rule"
                                                style="font-size:0.7rem;">
                                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Fix
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-light rounded-circle shadow-sm view-sms-btn flex-shrink-0"
                                                data-time="<?= $datePart ?> <?= $timePart ?>"
                                                data-body="<?= htmlspecialchars($bodyStr) ?>"
                                                title="View details">
                                            <i class="fa-solid fa-eye text-primary"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-2x mb-3 d-block opacity-25"></i>
                                No transactions found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top bg-light rounded-bottom">
            <div class="text-muted small">
                Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong>
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0 gap-1">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link rounded-3 shadow-none" href="<?= base_url("dashboard/transactions?page=".($page-1)."&".http_build_query(array_filter($filters))) ?>">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php
                    $start = max(1, $page - 3);
                    $end   = min($totalPages, $page + 3);
                    for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link rounded-3 shadow-none" href="<?= base_url("dashboard/transactions?page=$i&".http_build_query(array_filter($filters))) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link rounded-3 shadow-none" href="<?= base_url("dashboard/transactions?page=".($page+1)."&".http_build_query(array_filter($filters))) ?>">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="smsDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-envelope me-2"></i>SMS Raw Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 rounded-4 mb-3 font-monospace small" id="modal-sms-body" style="background: var(--bg-color); color: var(--text-main); word-break: break-all;"></div>
                <div class="text-end text-secondary small" id="modal-sms-time"></div>
            </div>
        </div>
    </div>
</div>

<!-- Fix Rule Modal -->
<div class="modal fade" id="fixRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form id="fixRuleForm" action="<?= base_url('dashboard/analyse/rule') ?>" method="post">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-success"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Fix Classification Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">Create an auto-replace rule for this counterparty to override the LLM category. Future scans will map it automatically.</p>
                    <div class="mb-3">
                        <label class="small text-secondary fw-bold text-uppercase">Counterparty</label>
                        <input type="text" id="rule_counterparty" class="form-control bg-light" name="keyword" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="small text-secondary fw-bold text-uppercase">Override Category</label>
                        <select name="category" class="form-select" required>
                            <?php foreach ($llm_categories as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="small text-secondary fw-bold text-uppercase">Match Type</label>
                        <select name="match_type" class="form-select">
                            <option value="exact" selected>Exact — only match this exact name</option>
                            <option value="contains">Contains — match any name that includes this keyword</option>
                        </select>
                        <div class="form-text text-muted">Use <strong>Contains</strong> to catch name variations (e.g., "Naivas Westlands", "Naivas Gateway" with keyword "naivas").</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Save Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function() {
        const detailModal = new bootstrap.Modal(document.getElementById('smsDetailModal'));
        const modalTime = document.getElementById('modal-sms-time');
        const modalBody = document.getElementById('modal-sms-body');

        $(document).on('click', '.view-sms-btn', function() {
            modalTime.textContent = $(this).attr('data-time');
            modalBody.innerHTML = $(this).attr('data-body').replace(/\n/g, '<br>');
            detailModal.show();
        });

        const fixModal = new bootstrap.Modal(document.getElementById('fixRuleModal'));
        $(document).on('click', '.recategorize-btn', function() {
            $('#rule_counterparty').val($(this).attr('data-counterparty'));
            $('#fixRuleModal select[name="category"]').val($(this).attr('data-category'));
            fixModal.show();
        });

        // Date Range Picker setup
        var start = <?= !empty($filters['date_from']) ? "moment('".$filters['date_from']."')" : "moment().subtract(29, 'days')" ?>;
        var end = <?= !empty($filters['date_to']) ? "moment('".$filters['date_to']."')" : "moment()" ?>;

        function cb(start, end) {
            $('#reportrange').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            $('#date_from').val(start.format('YYYY-MM-DD'));
            $('#date_to').val(end.format('YYYY-MM-DD'));
        }

        $('#reportrange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
               'Today': [moment(), moment()],
               'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               'Last 7 Days': [moment().subtract(6, 'days'), moment()],
               'Last 30 Days': [moment().subtract(29, 'days'), moment()],
               'This Month': [moment().startOf('month'), moment().endOf('month')],
               'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        cb(start, end);

        // Persistent categories filter tab buttons
        $('.filter-btn').on('click', function(e) {
            e.preventDefault();
            const url = new URL(this.href);
            const cat = url.searchParams.get('category') || '';
            $('input[name="category"]').val(cat);
            $('#filterForm').submit();
        });
    });

    window.submitExport = function() {
        const queryParams = $('#filterForm').serialize();
        window.location.href = "<?= base_url('dashboard/transactions/export?') ?>" + queryParams;
    };

    window.exportTableToPDF = function(tableId, filename, btnEl) {
        const btn = btnEl;
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Exporting...';

        const table = document.getElementById(tableId);
        if (!table) { btn.disabled = false; btn.innerHTML = orig; return; }

        const wrapper = document.createElement('div');
        wrapper.style.padding = '20px';
        wrapper.style.background = '#fff';
        wrapper.style.width = table.offsetWidth + 'px';
        const clone = table.cloneNode(true);
        wrapper.appendChild(clone);
        document.body.appendChild(wrapper);

        html2canvas(wrapper, {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            document.body.removeChild(wrapper);
            const imgData = canvas.toDataURL('image/png');
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('l', 'mm', 'a4');
            const pdfW = pdf.internal.pageSize.getWidth();
            const pdfH = (canvas.height * pdfW) / canvas.width;
            pdf.addImage(imgData, 'PNG', 0, 0, pdfW, pdfH);
            pdf.save(filename + '.pdf');
            btn.disabled = false;
            btn.innerHTML = orig;
        }).catch(err => {
            document.body.removeChild(wrapper);
            console.error('PDF export error:', err);
            btn.disabled = false;
            btn.innerHTML = orig;
        });
    }
</script>
<?= $this->endSection() ?>
