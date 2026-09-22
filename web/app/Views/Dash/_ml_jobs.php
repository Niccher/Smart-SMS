<?php $cs = currency_symbol(); ?>
<?php $jobs = $jobs ?? []; ?>

<?php if (empty($jobs)): ?>
<div class="card glass-card text-center p-5 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-center mx-auto mb-4"
             style="width: 80px; height: 80px; border-radius: 4px; background: var(--bg-color);">
            <i class="fa-solid fa-microchip fs-1 text-muted"></i>
        </div>
        <h5 class="fw-bold">No ML jobs yet</h5>
        <p class="text-secondary">Your data hasn't been run through the ML backend yet. Use the Analyze / Rescan buttons on your dashboard to trigger a job.</p>
    </div>
</div>
<?php else: ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
    <div>
        <h5 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-microchip me-2"></i>ML Job Runs</h5>
        <p class="text-muted small mb-0">Each row is one processing run. Click <i class="fa-solid fa-eye"></i> to open the full summary of that run.</p>
    </div>
</div>

<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="mlJobsTable">
                <thead class="table-light">
                    <tr>
                        <th>Job</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Finance SMS</th>
                        <th>Non-Finance SMS</th>
                        <th>Raw Total</th>
                        <th>Senders</th>
                        <th>Time</th>
                        <th>Model</th>
                        <th>Cost</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $j):
                        $md = $j['metadata'] ?? [];
                        $smsTotal = (int) ($md['sms_total'] ?? 0);
                        $smsFinance = (int) ($md['sms_finance'] ?? 0);
                        $smsUnwanted = (int) ($md['sms_unwanted'] ?? 0);
                        $sendersTotal = (int) ($md['senders_total'] ?? 0);
                        $dur = (int) ($md['duration_seconds'] ?? $j['duration_seconds'] ?? 0);
                        $isExt = ($md['llm_engine'] ?? '') === 'external';
                        $badge = match ($j['status']) {
                            'completed', 'done' => 'success',
                            'failed', 'error' => 'danger',
                            'processing', 'starting' => 'warning',
                            'queued' => 'info',
                            'disabled' => 'secondary',
                            default => 'secondary',
                        };
                    ?>
                    <tr style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#jobModal<?= (int) $j['id'] ?>">
                        <td class="fw-semibold">#<?= (int) $j['id'] ?></td>
                        <td><small><?= esc($j['created_at'] ?? '—') ?></small></td>
                        <td><span class="badge bg-<?= $badge ?>"><?= esc($j['status']) ?></span></td>
                        <td class="fw-bold text-success" style="font-size:1.02rem;"><?= number_format($smsFinance) ?></td>
                        <td class="fw-semibold text-danger"><?= number_format($smsUnwanted) ?></td>
                        <td class="text-muted"><small><?= number_format($smsTotal) ?></small></td>
                        <td><?= number_format($sendersTotal) ?></td>
                        <td><small><?= $dur > 0 ? gmdate('i:s', $dur) : '—' ?></small></td>
                        <td><small class="text-muted"><?= esc($md['model'] ?? '—') ?></small></td>
                        <td>
                            <?php if ($isExt && isset($j['cost']) && $j['cost'] > 0): ?>
                                <span class="text-primary fw-bold">$<?= number_format($j['cost'], 4) ?></span>
                                <div class="text-muted" style="font-size:0.62rem;">
                                    In: <?= number_format($j['tokens']['prompt'] ?? 0) ?><br>
                                    Out: <?= number_format($j['tokens']['reply'] ?? 0) ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:0.8rem;">$0.0000</span>
                            <?php endif; ?>
                        </td>
                        <td><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#jobModal<?= (int) $j['id'] ?>"><i class="fa-solid fa-eye"></i></button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Job Summary Modals -->
<?php foreach ($jobs as $j):
    $md = $j['metadata'] ?? [];
    $smsTotal = (int) ($md['sms_total'] ?? 0);
    $smsFinance = (int) ($md['sms_finance'] ?? 0);
    $smsUnwanted = (int) ($md['sms_unwanted'] ?? 0);
    $smsSkipped = (int) ($md['sms_skipped'] ?? 0);
    $sendersTotal = (int) ($md['senders_total'] ?? 0);
    $sendersFinance = (int) ($md['senders_finance'] ?? 0);
    $sendersUnwanted = (int) ($md['senders_unwanted'] ?? 0);
    $msgsProcessed = (int) ($md['messages_processed'] ?? 0);
    $transInserted = (int) ($md['transactional_inserted'] ?? 0);
    $errors = (int) ($md['errors'] ?? 0);
    $dur = (int) ($md['duration_seconds'] ?? $j['duration_seconds'] ?? 0);
?>
<div class="modal fade" id="jobModal<?= (int) $j['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-microchip me-2" style="color: var(--primary);"></i>Job #<?= (int) $j['id'] ?> Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-<?= match($j['status']) {'completed','done'=>'success','failed','error'=>'danger','processing','starting'=>'warning','queued'=>'info','disabled'=>'secondary',default=>'secondary'} ?>"><?= esc($j['status']) ?></span>
                    <span class="badge bg-secondary">User #<?= esc($j['user_id'] ?? '—') ?></span>
                    <span class="badge bg-secondary"><?= esc($j['created_at'] ?? '—') ?></span>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 text-center bg-success-subtle border-success">
                            <div class="fw-bold fs-4 text-success"><?= number_format($smsFinance) ?></div>
                            <div class="text-success small fw-semibold">Total Finance SMS</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 text-center">
                            <div class="fw-bold fs-4 text-danger"><?= number_format($smsUnwanted) ?></div>
                            <div class="text-muted small">Non-Finance SMS</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 text-center">
                            <div class="fw-bold fs-4 text-muted"><?= number_format($smsTotal) ?></div>
                            <div class="text-muted small">Raw Total SMS</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-3 text-center">
                            <div class="fw-bold fs-4 text-info"><?= esc($md['processing_rate_sms_per_sec'] ?? '—') ?></div>
                            <div class="text-muted small">Speed (SMS/s)</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="fw-semibold small text-secondary mb-1">Senders</div>
                        <table class="table table-sm mb-0">
                            <tr><td>All senders</td><td class="fw-semibold"><?= number_format($sendersTotal) ?></td></tr>
                            <tr><td>Good (finance) senders</td><td class="fw-semibold text-success"><?= number_format($sendersFinance) ?></td></tr>
                            <tr><td>Bad (unwanted) senders</td><td class="fw-semibold text-danger"><?= number_format($sendersUnwanted) ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <div class="fw-semibold small text-secondary mb-1">Results</div>
                        <table class="table table-sm mb-0">
                            <tr><td>Messages processed</td><td class="fw-semibold"><?= number_format($msgsProcessed) ?></td></tr>
                            <tr><td>Transactions found</td><td class="fw-semibold text-info"><?= number_format($transInserted) ?></td></tr>
                            <tr><td>Errors</td><td class="fw-semibold text-danger"><?= number_format($errors) ?></td></tr>
                            <tr><td>Time taken</td><td class="fw-semibold"><?= $dur > 0 ? gmdate('H:i:s', $dur) : '—' ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <div class="fw-semibold small text-secondary mb-1">Direction</div>
                        <table class="table table-sm mb-0">
                            <?php $dc = $md['direction_counts'] ?? []; ?>
                            <tr><td>Incoming (received)</td><td class="fw-semibold"><?= number_format($dc['incoming'] ?? 0) ?></td></tr>
                            <tr><td>Outgoing (sent)</td><td class="fw-semibold"><?= number_format($dc['outgoing'] ?? 0) ?></td></tr>
                            <tr><td>Undetermined</td><td class="fw-semibold"><?= number_format($dc['none'] ?? 0) ?></td></tr>
                        </table>
                    </div>
                </div>

                <?php $agg = $md['aggregation'] ?? null; if (!empty($agg)): ?>
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body p-3">
                        <div class="fw-semibold small text-primary mb-2"><i class="fa-solid fa-chart-line me-1"></i>Financial Insights Summary</div>
                        <div class="row g-2 text-center">
                            <div class="col-6 col-md-3">
                                <div class="bg-white rounded p-2 border">
                                    <div class="fw-bold text-danger"><?= $cs ?> <?= number_format((float)($agg['total_sent_money'] ?? 0), 2) ?></div>
                                    <div class="text-muted small">Total Sent</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-white rounded p-2 border">
                                    <div class="fw-bold text-success"><?= $cs ?> <?= number_format((float)($agg['total_received_money'] ?? 0), 2) ?></div>
                                    <div class="text-muted small">Total Received</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-white rounded p-2 border">
                                    <div class="fw-bold text-primary"><?= $cs ?> <?= number_format((float)($agg['total_transaction_volume'] ?? 0), 2) ?></div>
                                    <div class="text-muted small">Total Volume</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-white rounded p-2 border">
                                    <div class="fw-bold text-info"><?= esc($md['processing_rate_sms_per_sec'] ?? '—') ?> SMS/s</div>
                                    <div class="text-muted small">Throughput Speed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php $cc = $md['category_counts'] ?? []; if (!empty($cc)): ?>
                <div class="fw-semibold small text-secondary mb-1">SMS by Category</div>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php foreach ($cc as $cat => $cnt): ?>
                        <span class="badge bg-secondary-subtle text-dark border" style="font-size:0.78rem;">
                            <?= esc($cat) ?>: <span class="fw-bold"><?= number_format($cnt) ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php $sd = $md['senders_detail'] ?? []; if (!empty($sd)): ?>
                <div class="fw-semibold small text-secondary mb-1">Senders Detail</div>
                <div class="table-responsive mb-3" style="max-height:260px; overflow-y:auto;">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sender</th>
                                <th>Category</th>
                                <th>Type</th>
                                <th>Confidence</th>
                                <th>SMS</th>
                                <th>Parsed</th>
                                <th>Direction (In/Out/—)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sd as $snd):
                                $dirs = $snd['directions'] ?? [];
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= esc($snd['sender'] ?? '—') ?></td>
                                <td><?= esc($snd['category'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($snd['is_finance'])): ?>
                                        <span class="badge bg-success">Good</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Bad</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format((float)($snd['confidence'] ?? 0), 2) ?></td>
                                <td><?= number_format((int)($snd['sms_count'] ?? 0)) ?></td>
                                <td><?= number_format((int)($snd['parsed_count'] ?? 0)) ?></td>
                                <td><small><?= (int)($dirs['incoming'] ?? 0) ?> / <?= (int)($dirs['outgoing'] ?? 0) ?> / <?= (int)($dirs['none'] ?? 0) ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <div class="fw-semibold small text-secondary mb-1">Model &amp; Backend</div>
                <table class="table table-sm mb-0">
                    <tr><td>Model</td><td class="fw-semibold"><?= esc($md['model'] ?? '—') ?></td></tr>
                    <tr><td>Provider</td><td><?= esc($md['model_provider'] ?? '—') ?></td></tr>
                    <tr><td>Model path</td><td><small><?= esc($md['model_path'] ?? '—') ?></small></td></tr>
                    <tr><td>Max tokens / Temp</td><td><?= esc($md['llm_max_tokens'] ?? '—') ?> / <?= esc($md['llm_temperature'] ?? '—') ?></td></tr>
                    <tr><td>Context / Prompt batch</td><td><?= esc($md['llm_ctx_size'] ?? '—') ?> / <?= esc($md['llm_batch_size'] ?? '—') ?></td></tr>
                    <tr><td>GPU layers</td><td><?= esc($md['n_gpu_layers'] ?? '—') ?></td></tr>
                    <tr><td>SMS batch / Retries / Poll</td><td><?= esc($md['sms_batch_size'] ?? '—') ?> / <?= esc($md['max_retries'] ?? '—') ?> / <?= esc($md['poll_interval'] ?? '—') ?></td></tr>
                    <tr><td>Started</td><td><small><?= esc($j['started_at'] ?? '—') ?></small></td></tr>
                    <tr><td>Completed</td><td><small><?= esc($j['completed_at'] ?? '—') ?></small></td></tr>
                </table>

                <?php if (!empty($md['error'])): ?>
                    <div class="alert alert-danger small mt-3 mb-0"><?= esc($md['error']) ?></div>
                <?php endif; ?>
            </div>
            <div class="modal-footer border-0 pt-0">
                <?php if (in_array($j['status'], ['queued', 'processing', 'starting'])): ?>
                    <button type="button" class="btn btn-danger rounded-pill px-4 me-auto btn-stop-job" data-job-id="<?= (int)$j['id'] ?>">
                        <i class="fa-solid fa-stop me-1"></i> Stop Job
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<div class="alert alert-info mt-4 mb-0 small">
    <i class="fa-solid fa-circle-info me-1"></i>
    <strong>Good SMS</strong> = from finance senders. <strong>Bad SMS</strong> = from non-finance/unwanted senders.
    <strong>Skipped SMS</strong> = all SMS minus unwanted senders' SMS. Click any row for the full run summary.
</div>

<script>
document.querySelectorAll('.btn-stop-job').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const jobId = this.getAttribute('data-job-id');
        if (!confirm('Are you sure you want to stop/cancel this ML job?')) {
            return;
        }
        
        this.disabled = true;
        const formData = new FormData();
        formData.append('job_id', jobId);
        
        fetch('<?= base_url('dashboard/history/jobs/stop') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
                this.disabled = false;
            }
        })
        .catch(err => {
            alert('Failed to send request: ' + err.message);
            this.disabled = false;
        });
    });
});
</script>
<?php endif; ?>
