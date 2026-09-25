<?php
$versionData = [];
if (file_exists(APPPATH . 'Config/version.json')) {
    $versionData = json_decode(file_get_contents(APPPATH . 'Config/version.json'), true);
}
$systemVersion = $versionData['version'] ?? '3.5.0';
$systemReleaseDate = $versionData['release_date'] ?? date('Y-m-d');
$systemGithub = $versionData['github_url'] ?? 'https://github.com/Niccher/Smart-Finance-Platform';
$systemAndroidGithub = $versionData['android_github_url'] ?? 'https://github.com/Niccher/Smart-Finance-Android';
$systemReleases = $versionData['releases'] ?? [];
$systemChangelog = $versionData['changelog'] ?? [];
?>

<!-- Interactive Multi-Release Changelog Modal -->
<div class="modal fade" id="changelogModal" tabindex="-1" aria-labelledby="changelogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header border-bottom bg-light bg-opacity-50 py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="changelogModalLabel">Platform Release History</h5>
                        <p class="text-muted small mb-0">Unified versioning across WebApp, ML Microservice & Android Companion App</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4" style="max-height: 70vh; overflow-y: auto;">
                <?php if (!empty($systemReleases)): ?>
                    <div class="timeline-container">
                        <?php foreach ($systemReleases as $idx => $rel): ?>
                            <div class="card mb-3 border <?= $idx === 0 ? 'border-primary shadow-xs' : 'border-light-subtle' ?> rounded-3">
                                <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-2.5 px-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge <?= $idx === 0 ? 'bg-primary' : 'bg-secondary' ?> rounded-pill px-2.5 py-1.5 font-monospace">
                                            v<?= esc($rel['version']) ?>
                                        </span>
                                        <strong class="text-dark"><?= esc($rel['title'] ?? 'Release Update') ?></strong>
                                        <?php if ($idx === 0): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle small font-monospace">LATEST</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-muted small font-monospace mt-1 mt-sm-0">
                                        <i class="fa-regular fa-calendar me-1"></i><?= esc($rel['date'] ?? '') ?>
                                    </span>
                                </div>
                                <div class="card-body py-2.5 px-3">
                                    <ul class="list-unstyled mb-0 small">
                                        <?php foreach ($rel['highlights'] ?? [] as $item): ?>
                                            <li class="py-1.5 d-flex align-items-start text-secondary">
                                                <i class="fa-solid fa-check-circle text-primary me-2 mt-1 flex-shrink-0"></i>
                                                <span class="text-dark"><?= esc($item) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <span class="badge bg-primary rounded-pill px-3 py-2 fw-semibold">Version: v<?= esc($systemVersion) ?></span>
                    </div>
                    <ul class="list-group list-group-flush small">
                        <?php foreach ($systemChangelog as $change): ?>
                            <li class="list-group-item px-0 py-2 border-light d-flex align-items-start">
                                <i class="fa-solid fa-check text-success me-2 mt-1"></i>
                                <div><?= esc($change) ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="modal-footer bg-light bg-opacity-50 border-top py-2 px-3 d-flex justify-content-between align-items-center">
                <span class="small text-muted font-monospace">Active Release: <strong>v<?= esc($systemVersion) ?></strong> (<?= esc($systemReleaseDate) ?>)</span>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
