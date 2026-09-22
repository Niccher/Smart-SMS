<?= $this->extend('Layouts/superadmin') ?>
<?= $this->section('title') ?> Cron Jobs - Mpesa Analyzer <?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
    .settings-card { border: none; border-radius: 4px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    .schedule-preview { font-family: monospace; background: #f1f3f5; border-radius: 4px; padding: 0.35rem 0.6rem; font-size: 0.85rem; }
    .output-pre { background: #0f172a; color: #e2e8f0; border-radius: 4px; padding: 1rem; max-height: 380px; overflow: auto; font-size: 0.8rem; white-space: pre-wrap; word-break: break-word; }
    [data-bs-theme="dark"] .output-pre { background: #000; }
    .run-item { cursor: pointer; border: 1px solid #e0e0e0; border-radius: 4px; padding: 0.5rem 0.75rem; }
    .run-item:hover, .run-item.active { border-color: var(--primary); background: rgba(177,184,237,0.15); }
    .run-list { max-height: 420px; overflow: auto; }
</style>
<?= $this->endSection() ?>
<?= $this->section('page_header') ?>
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--primary);"><i class="fa-solid fa-clock me-2"></i> Cron Jobs</h2>
        <p class="text-secondary mb-0">Create and manage the background tasks that keep the platform running.</p>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="row g-4">
    <div class="col-lg-12">
        <div class="card settings-card">
            <div class="card-body p-4">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#cronJobsTab">Cron Jobs <span class="badge bg-secondary ms-1"><?= count($cron_jobs) ?></span></button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cronRunsTab">Cron Runs <span class="badge bg-secondary ms-1"><?= count($cron_runs) ?></span></button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="cronJobsTab">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <h5 class="fw-bold mb-0" style="color: var(--primary);"><i class="fa-solid fa-list-check me-2"></i> Scheduled Jobs</h5>
                            <div class="d-flex align-items-center gap-2">
                                <select class="form-select form-select-sm" id="jobTypeFilter" style="min-width: 180px;">
                                    <option value="all" data-badge="bg-secondary">All Types (<?= count($cron_jobs) ?>)</option>
                                    <?php foreach ($job_types as $type => $meta): ?>
                                        <option value="<?= esc($type) ?>"><?= esc($meta['label']) ?> (<?= $job_type_counts[$type] ?? 0 ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" data-bs-toggle="modal" data-bs-target="#cronModal" id="addCronBtn">
                                    <i class="fa-solid fa-plus me-1"></i> Add Cron Job
                                </button>
                            </div>
                        </div>
                        <?php if (empty($cron_jobs)): ?>
                            <div class="text-center text-muted p-5">
                                <i class="fa-solid fa-inbox fs-1 d-block mb-2"></i>
                                No cron jobs yet. Click <strong>Add Cron Job</strong> to create one.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Job</th>
                                            <th>Type</th>
                                            <th>Schedule</th>
                                            <th>Last Run</th>
                                            <th class="text-center" style="width: 80px;">Enabled</th>
                                            <th style="width: 200px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cron_jobs as $job): ?>
                                            <?php
                                                $jobType = $job['type'] ?? '';
                                                $typeMeta = $job_types[$jobType] ?? ['group' => 'Checks', 'label' => $jobType === '' ? 'Legacy / Unknown' : $jobType . ' (unmapped)'];
                                                $group = $typeMeta['group'] ?? 'Checks';
                                                $badgeClass = match ($group) {
                                                    'Spark Commands' => 'bg-primary',
                                                    'Database' => 'bg-success',
                                                    default => 'bg-info text-dark',
                                                };
                                            ?>
                                            <tr data-key="<?= esc($job['key']) ?>">
                                                <td>
                                                    <strong><?= esc($job['name'] ?? $job['key']) ?></strong>
                                                    <br><small class="text-muted"><?= esc($job['description'] ?? '') ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $badgeClass ?>"><?= esc($typeMeta['label']) ?></span>
                                                    <?php if ($jobType !== ''): ?>
                                                        <br><small class="text-muted"><code><?= esc($jobType) ?></code></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <code><?= esc($job['schedule'] ?? 'N/A') ?></code>
                                                    <br><small class="text-muted"><?= esc(\App\Libraries\CronRunner::describe($job['schedule'] ?? '')) ?></small>
                                                </td>
                                                <td>
                                                    <?php if (!empty($job['last_run'])): ?>
                                                        <small><?= esc($job['last_run']) ?></small>
                                                        <br>
                                                        <span class="badge <?= ($job['last_status'] ?? '') === 'success' ? 'bg-success' : 'bg-danger' ?>">
                                                            <?= ($job['last_status'] ?? '') === 'success' ? 'Success' : 'Error' ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <small class="text-muted">Never</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check form-switch d-inline-block">
                                                        <input class="form-check-input cron-toggle" type="checkbox" role="switch"
                                                            data-key="<?= esc($job['key']) ?>" <?= !empty($job['enabled']) ? 'checked' : '' ?>>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-success btn-run" data-key="<?= esc($job['key']) ?>" title="Run now">
                                                            <i class="fa-solid fa-play"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-history" data-key="<?= esc($job['key']) ?>" data-name="<?= esc($job['name'] ?? $job['key'], 'attr') ?>" title="View history">
                                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary btn-edit"
                                                            data-key="<?= esc($job['key']) ?>"
                                                            data-name="<?= esc($job['name'] ?? '', 'attr') ?>"
                                                            data-type="<?= esc($job['type'] ?? '', 'attr') ?>"
                                                            data-schedule="<?= esc($job['schedule'] ?? '', 'attr') ?>"
                                                            data-description="<?= esc($job['description'] ?? '', 'attr') ?>"
                                                            data-enabled="<?= !empty($job['enabled']) ? '1' : '0' ?>"
                                                            title="Edit">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-delete" data-key="<?= esc($job['key']) ?>" data-name="<?= esc($job['name'] ?? $job['key'], 'attr') ?>" title="Delete">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="cronRunsTab">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <h5 class="fw-bold mb-0" style="color: var(--primary);"><i class="fa-solid fa-clock-rotate-left me-2"></i> Run History</h5>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted small">Last <?= count($cron_runs) ?> runs</span>
                                <select class="form-select form-select-sm" id="runTypeFilter" style="min-width: 180px;">
                                    <option value="all" data-badge="bg-secondary">All Types</option>
                                    <?php
                                    // All known job types with their run counts.
                                    foreach ($job_types as $type => $meta):
                                        $cnt = $run_type_counts[$type] ?? 0;
                                    ?>
                                        <option value="<?= esc($type) ?>"><?= esc($meta['label']) ?> (<?= $cnt ?>)</option>
                                    <?php endforeach; ?>
                                    <?php
                                    // Any unmapped types that have runs.
                                    $mapped = array_keys($job_types);
                                    foreach ($run_type_counts as $type => $cnt):
                                        if (in_array($type, $mapped) || $type === '') continue;
                                    ?>
                                        <option value="<?= esc($type) ?>"><?= esc($type) ?> (<?= $cnt ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (empty($cron_runs)): ?>
                            <div class="text-center text-muted p-5">
                                <i class="fa-solid fa-inbox fs-1 d-block mb-2"></i>
                                No runs recorded yet. Runs appear here when a job is executed by the scheduler or manually.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Job</th>
                                            <th>Type</th>
                                            <th style="width: 140px;">Trigger</th>
                                            <th style="width: 170px;">Run At</th>
                                            <th style="width: 90px;">Status</th>
                                            <th>Output</th>
                                            <th style="width: 90px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cron_runs as $run): ?>
                                            <?php
                                                $runJobType = $run['job_type'] ?? '';
                                                $runTypeMeta = $job_types[$runJobType] ?? ['label' => $runJobType === '' ? 'Legacy' : $runJobType];
                                                $runLocal = '';
                                                if (!empty($run['ran_at'])) {
                                                    try {
                                                        $runLocal = (new \DateTimeImmutable($run['ran_at'], new \DateTimeZone('UTC')))
                                                            ->setTimezone(new \DateTimeZone('Africa/Nairobi'))
                                                            ->format('d M Y, g:i A');
                                                    } catch (\Throwable $e) {
                                                        $runLocal = $run['ran_at'];
                                                    }
                                                }
                                            ?>
                                            <tr data-job-type="<?= esc($run['job_type'] ?? '') ?>">
                                                <td>
                                                    <strong><?= esc($run['job_name'] ?: $run['job_key']) ?></strong>
                                                    <br><small class="text-muted"><code><?= esc($run['job_key']) ?></code></small>
                                                </td>
                                                <td><small><?= esc($runTypeMeta['label']) ?></small></td>
                                                <td>
                                                    <span class="badge <?= ($run['trigger'] ?? 'scheduler') === 'manual' ? 'bg-info text-dark' : 'bg-secondary' ?>">
                                                        <i class="fa-solid <?= ($run['trigger'] ?? 'scheduler') === 'manual' ? 'fa-user' : 'fa-clock' ?> me-1"></i><?= ($run['trigger'] ?? 'scheduler') === 'manual' ? 'Manual' : 'Scheduler' ?>
                                                    </span>
                                                </td>
                                                <td><small><?= esc($runLocal) ?></small></td>
                                                <td>
                                                    <span class="badge <?= ($run['status'] ?? '') === 'success' ? 'bg-success' : 'bg-danger' ?>">
                                                        <?= ($run['status'] ?? '') === 'success' ? 'Success' : 'Error' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted d-block text-truncate" style="max-width: 260px;"><?= esc(strtok((string) ($run['output'] ?? ''), "\n")) ?></small>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-history"
                                                        data-key="<?= esc($run['job_key']) ?>"
                                                        data-name="<?= esc($run['job_name'] ?: $run['job_key'], 'attr') ?>"
                                                        title="View history">
                                                        <i class="fa-solid fa-clock-rotate-left me-1"></i> History
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create / Edit Modal -->
<div class="modal fade" id="cronModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="cronForm">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="cronModalTitle" style="color: var(--primary);"><i class="fa-solid fa-plus me-2"></i>Add Cron Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="job_key" id="jobKey" value="">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Job Name</label>
                        <input type="text" class="form-control" name="name" id="jobName" placeholder="e.g. Nightly database backup" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Job Type</label>
                        <select class="form-select" name="type" id="jobType" required>
                            <option value="">-- Choose a job --</option>
                            <?php foreach ($job_sections as $section => $items): ?>
                                <optgroup label="<?= esc($section) ?>">
                                    <?php foreach ($items as $item): ?>
                                        <option value="<?= esc($item['type']) ?>" data-desc="<?= esc($item['desc'], 'attr') ?>"><?= esc($item['label']) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted d-block mt-1" id="jobTypeDesc"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Schedule <span class="text-muted fw-normal">(when the job runs)</span></label>
                        <select class="form-select mb-2" id="schedulePreset">
                            <option value="">-- Choose a preset --</option>
                            <?php foreach ($presets as $p): ?>
                                <option value="<?= esc($p['value']) ?>"><?= esc($p['label']) ?></option>
                            <?php endforeach; ?>
                            <option value="custom">Custom...</option>
                        </select>

                        <div class="row g-2 mb-2" id="scheduleBuilder">
                            <div class="col">
                                <label class="form-label small fw-semibold mb-1">Minute</label>
                                <select class="form-select form-select-sm sch-field" data-field="minute" id="schMinute">
                                    <option value="*">*</option>
                                    <option value="*/5">*/5</option>
                                    <option value="*/10">*/10</option>
                                    <option value="*/15">*/15</option>
                                    <option value="*/30">*/30</option>
                                    <?php foreach ([0,5,10,15,20,25,30,35,40,45,50,55] as $m): ?><option value="<?= $m ?>"><?= $m ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label small fw-semibold mb-1">Hour</label>
                                <select class="form-select form-select-sm sch-field" data-field="hour" id="schHour">
                                    <option value="*">*</option>
                                    <?php foreach (range(0, 23) as $h): ?><option value="<?= $h ?>"><?= $h ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label small fw-semibold mb-1">Day (month)</label>
                                <select class="form-select form-select-sm sch-field" data-field="day" id="schDay">
                                    <option value="*">*</option>
                                    <?php foreach (range(1, 31) as $d): ?><option value="<?= $d ?>"><?= $d ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label small fw-semibold mb-1">Month</label>
                                <select class="form-select form-select-sm sch-field" data-field="month" id="schMonth">
                                    <option value="*">*</option>
                                    <?php foreach (range(1, 12) as $mo): ?><option value="<?= $mo ?>"><?= $mo ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col">
                                <label class="form-label small fw-semibold mb-1">Day (week)</label>
                                <select class="form-select form-select-sm sch-field" data-field="dow" id="schDow">
                                    <option value="*">*</option>
                                    <?php
                                        $days = [0 => '0 (Sun)', 1 => '1 (Mon)', 2 => '2 (Tue)', 3 => '3 (Wed)', 4 => '4 (Thu)', 5 => '5 (Fri)', 6 => '6 (Sat)', 7 => '7 (Sun)'];
                                        foreach ($days as $dv => $dl): ?><option value="<?= $dv ?>"><?= $dl ?></option><?php endforeach;
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="schedule-preview flex-grow-1" id="schedulePreview">* * * * *</span>
                            <span class="badge bg-secondary" id="scheduleHuman">Every minute</span>
                        </div>
                        <input type="hidden" name="schedule" id="scheduleInput" value="* * * * *">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" name="description" id="jobDescription" rows="2" placeholder="Short description of what this job does"></textarea>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enabled" id="jobEnabled" role="switch" value="1" checked>
                        <label class="form-check-label fw-semibold" for="jobEnabled">Enable this job</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Save Job</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Output Modal -->
<div class="modal fade" id="outputModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="color: var(--primary);"><i class="fa-solid fa-terminal me-2"></i><span id="outputTitle">Job Output</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 align-items-center mb-3">
                    <span class="badge bg-secondary" id="outputRunAt">Never</span>
                    <span class="badge bg-success d-none" id="outputStatusOk"><i class="fa-solid fa-circle-check me-1"></i>Success</span>
                    <span class="badge bg-danger d-none" id="outputStatusErr"><i class="fa-solid fa-circle-xmark me-1"></i>Error</span>
                </div>
                <pre class="output-pre" id="outputBody">No output.</pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Run History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="color: var(--primary);"><i class="fa-solid fa-clock-rotate-left me-2"></i><span id="historyTitle">Run History</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="historyBody"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const CRON_PRESET_LABELS = {
    '* * * * *': 'Every minute',
    '*/5 * * * *': 'Every 5 minutes',
    '*/10 * * * *': 'Every 10 minutes',
    '*/15 * * * *': 'Every 15 minutes',
    '*/30 * * * *': 'Every 30 minutes',
    '0 * * * *': 'Every hour',
    '0 2 * * *': 'Daily at 2:00 AM',
    '0 6 * * *': 'Daily at 6:00 AM',
    '0 6 * * 1': 'Weekly on Monday at 6:00 AM',
    '0 3 * * 0': 'Weekly on Sunday at 3:00 AM'
};

function buildSchedule() {
    const parts = ['schMinute', 'schHour', 'schDay', 'schMonth', 'schDow'].map(id => document.getElementById(id).value);
    const expr = parts.join(' ');
    document.getElementById('scheduleInput').value = expr;
    document.getElementById('schedulePreview').textContent = expr;
    document.getElementById('scheduleHuman').textContent = CRON_PRESET_LABELS[expr] || 'Custom schedule';
}

function setScheduleFields(expr) {
    const p = (expr || '* * * * *').split(/\s+/);
    const ids = ['schMinute', 'schHour', 'schDay', 'schMonth', 'schDow'];
    ids.forEach((id, i) => {
        const sel = document.getElementById(id);
        if (sel.querySelector('option[value="' + p[i] + '"]')) sel.value = p[i];
    });
}

document.querySelectorAll('.sch-field').forEach(sel => sel.addEventListener('change', buildSchedule));

document.getElementById('schedulePreset').addEventListener('change', function() {
    if (this.value && this.value !== 'custom') {
        setScheduleFields(this.value);
        buildSchedule();
    }
});

// Type description helper
document.getElementById('jobType').addEventListener('change', function() {
    const opt = this.selectedOptions[0];
    document.getElementById('jobTypeDesc').textContent = opt && opt.dataset.desc ? opt.dataset.desc : '';
});

// Modal open/reset
const cronModal = document.getElementById('cronModal');
cronModal.addEventListener('hidden.bs.modal', function() {
    document.getElementById('cronForm').reset();
    document.getElementById('jobKey').value = '';
    document.getElementById('cronModalTitle').innerHTML = '<i class="fa-solid fa-plus me-2"></i>Add Cron Job';
    setScheduleFields('* * * * *');
    buildSchedule();
    document.getElementById('jobTypeDesc').textContent = '';
    document.getElementById('jobEnabled').checked = true;
});

// Edit
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('jobKey').value = this.dataset.key;
        document.getElementById('jobName').value = this.dataset.name;
        document.getElementById('jobDescription').value = this.dataset.description || '';
        document.getElementById('jobType').value = this.dataset.type;
        document.getElementById('jobType').dispatchEvent(new Event('change'));
        setScheduleFields(this.dataset.schedule);
        buildSchedule();
        document.getElementById('jobEnabled').checked = this.dataset.enabled === '1';
        document.getElementById('cronModalTitle').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Edit Cron Job';
        new bootstrap.Modal(cronModal).show();
    });
});

// Save
document.getElementById('cronForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const data = new FormData(this);
    if (!data.get('enabled')) data.append('enabled', '0');
    fetch('<?= base_url('admin/crons/save') ?>', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                showAlert('Cron Jobs', res.message, 'success');
                bootstrap.Modal.getInstance(cronModal).hide();
                setTimeout(() => location.reload(), 600);
            } else {
                showAlert('Cron Jobs', res.message || 'Failed to save job.', 'danger');
            }
        });
});

// Run now
document.querySelectorAll('.btn-run').forEach(btn => {
    btn.addEventListener('click', function() {
        const key = this.dataset.key;
        const original = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        const data = new FormData();
        data.append('job_key', key);
        fetch('<?= base_url('admin/crons/run') ?>', { method: 'POST', body: data })
            .then(r => r.json())
            .then(res => {
                this.disabled = false;
                this.innerHTML = original;
                showOutputModal(res.name || key, res.last_run, res.status, res.output);
                setTimeout(() => location.reload(), 800);
            })
            .catch(() => {
                this.disabled = false;
                this.innerHTML = original;
                showAlert('Cron Jobs', 'Failed to run job.', 'danger');
            });
    });
});

// View run history
document.querySelectorAll('.btn-history').forEach(btn => {
    btn.addEventListener('click', function() {
        const data = new FormData();
        data.append('job_key', this.dataset.key);
        fetch('<?= base_url('admin/crons/history') ?>', { method: 'POST', body: data })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    showHistoryModal(res.name, res.runs || []);
                } else {
                    showAlert('Cron Jobs', res.message || 'Failed to load history.', 'danger');
                }
            });
    });
});

function showHistoryModal(name, runs) {
    document.getElementById('historyTitle').textContent = name + ' — Run History';
    const body = document.getElementById('historyBody');

    if (!runs.length) {
        body.innerHTML = '<div class="col-12 text-center text-muted p-4">' +
            '<i class="fa-solid fa-inbox fs-1 d-block mb-2"></i>No runs recorded for this job yet.</div>';
    } else {
        body.innerHTML = '' +
            '<div class="col-md-5"><div class="run-list d-flex flex-column gap-2" id="historyRunList"></div></div>' +
            '<div class="col-md-7"><pre class="output-pre" id="historyOutput" style="max-height: 460px;">Loading...</pre></div>';
        const list = document.getElementById('historyRunList');
        runs.forEach((run, idx) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'run-item btn text-start w-100 d-flex justify-content-between align-items-center gap-2' + (idx === 0 ? ' active' : '');
            const statusBadge = run.status === 'success'
                ? '<span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i>Success</span>'
                : '<span class="badge bg-danger"><i class="fa-solid fa-circle-xmark me-1"></i>Error</span>';
            const triggerBadge = run.trigger === 'manual'
                ? '<span class="badge bg-info text-dark"><i class="fa-solid fa-user me-1"></i>Manual</span>'
                : '<span class="badge bg-secondary"><i class="fa-solid fa-clock me-1"></i>Scheduler</span>';
            item.innerHTML = '<span class="d-flex flex-column gap-1">' +
                '<small><strong>' + (run.ran_at_local || run.ran_at || 'Unknown time') + '</strong></small>' +
                '<span class="d-flex gap-1">' + statusBadge + triggerBadge + '</span></span>' +
                '<i class="fa-solid fa-chevron-right text-muted"></i>';
            item.addEventListener('click', () => {
                list.querySelectorAll('.run-item').forEach(el => el.classList.remove('active'));
                item.classList.add('active');
                document.getElementById('historyOutput').textContent = run.output || 'No output.';
            });
            list.appendChild(item);
        });
        document.getElementById('historyOutput').textContent = runs[0].output || 'No output.';
    }

    new bootstrap.Modal(document.getElementById('historyModal')).show();
}

function showOutputModal(name, lastRun, status, output) {
    document.getElementById('outputTitle').textContent = name + ' — Output';
    document.getElementById('outputRunAt').textContent = lastRun ? ('Run at ' + lastRun) : 'Never';
    document.getElementById('outputStatusOk').classList.add('d-none');
    document.getElementById('outputStatusErr').classList.add('d-none');
    if (status === 'success') document.getElementById('outputStatusOk').classList.remove('d-none');
    else if (status === 'error') document.getElementById('outputStatusErr').classList.remove('d-none');
    document.getElementById('outputBody').textContent = output || 'No output.';
    new bootstrap.Modal(document.getElementById('outputModal')).show();
}

// Toggle enabled
document.querySelectorAll('.cron-toggle').forEach(chk => {
    chk.addEventListener('change', function() {
        const data = new FormData();
        data.append('job_key', this.dataset.key);
        data.append('enabled', this.checked ? '1' : '0');
        fetch('<?= base_url('admin/crons/toggle') ?>', { method: 'POST', body: data })
            .then(r => r.json())
            .then(res => showAlert('Cron Jobs', res.message, res.status === 'success' ? 'success' : 'danger'));
    });
});

// Delete cron job
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const name = this.dataset.name;
        if (!confirm('Delete cron job "' + name + '"? This cannot be undone.')) return;
        const data = new FormData();
        data.append('job_key', this.dataset.key);
        fetch('<?= base_url('admin/crons/delete') ?>', { method: 'POST', body: data })
            .then(r => r.json())
            .then(res => {
                showAlert('Cron Jobs', res.message, res.status === 'success' ? 'success' : 'danger');
                if (res.status === 'success') setTimeout(() => location.reload(), 600);
            });
    });
});

// Run type filter
const runTypeFilter = document.getElementById('runTypeFilter');
if (runTypeFilter) {
    runTypeFilter.addEventListener('change', function() {
        const type = this.value;
        const rows = document.querySelectorAll('#cronRunsTab tbody tr');
        rows.forEach(row => {
            if (type === 'all') {
                row.style.display = '';
            } else {
                row.style.display = (row.dataset.jobType === type) ? '' : 'none';
            }
        });
    });
}

// Job type filter (for scheduled jobs list)
const jobTypeFilter = document.getElementById('jobTypeFilter');
if (jobTypeFilter) {
    jobTypeFilter.addEventListener('change', function() {
        const type = this.value;
        const rows = document.querySelectorAll('#cronJobsTab tbody tr[data-key]');
        const allOption = this.querySelector('option[value="all"]');
        const totalAll = parseInt(allOption.dataset-badge?.match(/\d+/)?.[0] || '0');
        
        rows.forEach(row => {
            const jobType = row.querySelector('td:nth-child(2)')?.textContent || '';
            const typeMatch = jobType.toLowerCase().includes(type.toLowerCase()) || 
                              (type === 'all') ||
                              (row.dataset.key && jobTypeFilter.options[jobTypeFilter.selectedIndex]?.value === 'all');
            
            if (type === 'all') {
                row.style.display = '';
            } else {
                const jobTypeSpan = row.querySelector('td:nth-child(2)');
                const typeText = jobTypeSpan?.textContent || '';
                const badgeCode = jobTypeSpan?.querySelector('code')?.textContent || '';
                if (badgeCode === type || typeText.toLowerCase().includes(type.toLowerCase())) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
        
        const visibleCount = Array.from(rows).filter(r => r.style.display !== 'none').length;
        if (allOption) {
            allOption.textContent = 'All Types (' + visibleCount + ')';
        }
    });
}
</script>
<?= $this->endSection() ?>
