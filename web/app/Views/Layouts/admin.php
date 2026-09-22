<?php
$versionData = [];
if (file_exists(APPPATH . 'Config/version.json')) {
    $versionData = json_decode(file_get_contents(APPPATH . 'Config/version.json'), true);
}
$systemVersion = $versionData['version'] ?? '3.2.0';
$systemChangelog = $versionData['changelog'] ?? [];
$systemGithub = $versionData['github_url'] ?? 'https://github.com/niccher/Mpesa_Analyzer_App';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $this->renderSection('title') ?? 'Mpesa Analyzer' ?></title>

    <!-- Primary Meta Tags -->
    <meta name="title" content="<?= $this->renderSection('title') ?? 'Financial Analyzer Ecosystem' ?>">
    <meta name="description" content="Universal Multi-Channel Financial Intelligence Suite — Autonomous transaction categorization, dynamic rules engine, and multi-institution financial analytics.">
    <meta name="keywords" content="financial analyzer, mpesa analyzer, transaction categorization, multi-channel finance, dynamic rules engine, personal finance manager">
    <meta name="author" content="Niccher / Financial Analyzer Ecosystem">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#438EB9">

    <!-- Mobile & PWA Web App Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="FinAnalyzer">
    <meta name="application-name" content="FinAnalyzer">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Open Graph / Social Meta Tags -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:site_name" content="Financial Analyzer Ecosystem">
    <meta property="og:title" content="<?= $this->renderSection('title') ?? 'Financial Analyzer Ecosystem' ?>">
    <meta property="og:description" content="Universal Multi-Channel Financial Intelligence Suite with AI categorization, dynamic rules engine, and multi-institution telemetry.">
    <meta property="og:image" content="<?= base_url('assets/img/logo.png') ?>">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $this->renderSection('title') ?? 'Financial Analyzer Ecosystem' ?>">
    <meta name="twitter:description" content="Universal Multi-Channel Financial Intelligence Suite with AI categorization & dynamic rules engine.">
    <meta name="twitter:image" content="<?= base_url('assets/img/logo.png') ?>">

    <!-- Favicon & PWA Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('favicon.png?v=' . $systemVersion) ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico?v=' . $systemVersion) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('apple-touch-icon.png?v=' . $systemVersion) ?>">
    <link rel="manifest" href="<?= base_url('site.webmanifest?v=' . $systemVersion) ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Ace Theme Engine CSS -->
    <link href="<?= base_url('assets/ace/css/ace-theme.css') ?>" rel="stylesheet" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Prevent Light Flash -->
    <script>
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }
    </script>

    <?= $this->renderSection('styles') ?>
</head>

<body>

    <!-- Ace Fixed Navbar Header -->
    <header class="ace-navbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn text-white p-0 border-0 d-lg-none" id="mobileSidebarToggle" type="button" aria-label="Toggle Navigation">
                <i class="fa-solid fa-bars fs-5"></i>
            </button>

            <a href="<?= url_to('DashboardController::index') ?>" class="ace-brand">
                <i class="fa-solid fa-wallet"></i>
                <span>Mpesa Analyzer</span>
            </a>
        </div>

        <div class="ace-nav-actions">
            <!-- Rescan Buttons -->
            <div class="d-none d-md-flex gap-2">
                <button class="ace-nav-btn bg-primary" id="rescanBtn" title="Process only new/unprocessed SMS">
                    <i class="fa-solid fa-rotate"></i> Rescan
                </button>
                <button class="ace-nav-btn bg-warning text-dark" id="rescanAllBtn" title="Reprocess ALL SMS from scratch">
                    <i class="fa-solid fa-rotate-right"></i> Full Reset
                </button>
            </div>

            <!-- Scan Progress Status Badge -->
            <button id="scanStatusBadge" class="ace-nav-btn d-none" title="Click for scan details" data-bs-toggle="modal" data-bs-target="#scanProgressModal">
                <span id="scanStatusIcon" class="spinner-border spinner-border-sm text-warning" role="status"></span>
                <span id="scanStatusText">Scanning...</span>
            </button>

            <!-- Theme Light/Dark Mode Switcher -->
            <button class="ace-nav-btn bg-transparent" id="themeToggleBtn" title="Toggle Light/Dark Theme">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
                <span id="themeText" class="d-none d-sm-inline">Dark Mode</span>
            </button>

            <!-- User Dropdown -->
            <div class="dropdown ace-user-menu">
                <a href="#" class="ace-user-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php $username = auth()->user()->username ?? 'User'; ?>
                    <div class="ace-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
                    <span class="d-none d-sm-inline"><?= esc($username) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                    <li><a class="dropdown-item py-2" href="<?= url_to('Info::index') ?>"><i class="fa-solid fa-user me-2 text-primary"></i> Profile</a></li>
                    <li><a class="dropdown-item py-2" href="<?= base_url('dashboard/settings/profile') ?>"><i class="fa-solid fa-gear me-2 text-primary"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger" href="<?= url_to('logout') ?>"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Ace Main Layout Container -->
    <div class="ace-layout">

        <!-- Mobile Drawer Backdrop -->
        <div class="ace-backdrop" id="sidebarBackdrop"></div>

        <!-- Sidebar Navigation -->
        <aside class="ace-sidebar" id="aceSidebar">
            <!-- Sidebar Quick Shortcuts Bar -->
            <div class="ace-shortcuts">
                <a href="<?= url_to('DashboardController::index') ?>" class="ace-shortcut-btn bg-ace-green" title="Dashboard">
                    <i class="fa-solid fa-chart-line"></i>
                </a>
                <a href="<?= url_to('Graph::index') ?>" class="ace-shortcut-btn bg-ace-blue" title="Analytics">
                    <i class="fa-solid fa-chart-pie"></i>
                </a>
                <a href="<?= url_to('Transactions::index') ?>" class="ace-shortcut-btn bg-ace-amber" title="Transactions">
                    <i class="fa-solid fa-list-check"></i>
                </a>
                <a href="<?= base_url('dashboard/settings/profile') ?>" class="ace-shortcut-btn bg-ace-red" title="Control Center">
                    <i class="fa-solid fa-sliders"></i>
                </a>
            </div>

            <!-- Sidebar Navigation Links -->
            <ul class="ace-nav-list">
                <?php $currentURL = uri_string(); ?>

                <li class="ace-nav-item <?= ($currentURL == 'dashboard' || $currentURL == '') ? 'active' : '' ?>">
                    <a href="<?= url_to('DashboardController::index') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-house"></i>
                        <span class="ace-nav-text">Dashboard Overview</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= ($currentURL == 'dashboard/graph') ? 'active' : '' ?>">
                    <a href="<?= url_to('Graph::index') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span class="ace-nav-text">Analytics</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= strpos($currentURL, 'dashboard/transactions') !== false ? 'active' : '' ?>">
                    <a href="<?= url_to('Transactions::index') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-list-check"></i>
                        <span class="ace-nav-text">Transaction Ledger</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= strpos($currentURL, 'dashboard/reports') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('dashboard/reports') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span class="ace-nav-text">Reports & Insights</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= strpos($currentURL, 'dashboard/budget') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('dashboard/budget') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-bullseye"></i>
                        <span class="ace-nav-text">Budget Tracker</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= (strpos($currentURL, 'dashboard/settings') !== false || strpos($currentURL, 'dashboard/devices') !== false || $currentURL == 'dashboard/info') ? 'active' : '' ?>">
                    <a href="<?= base_url('dashboard/settings/profile') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-sliders"></i>
                        <span class="ace-nav-text">Control Center</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= strpos($currentURL, 'dashboard/history') !== false ? 'active' : '' ?>">
                    <a href="<?= url_to('HistoryController::index') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span class="ace-nav-text">History</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= strpos($currentURL, 'dashboard/blocklist') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('dashboard/blocklist') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-ban"></i>
                        <span class="ace-nav-text">Blocklist</span>
                    </a>
                </li>
            </ul>

            <!-- Sidebar Footer Logout -->
            <div class="p-3 border-top mt-auto">
                <a href="<?= url_to('logout') ?>" class="btn btn-outline-danger btn-sm w-100 fw-semibold">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Section -->
        <main class="ace-main">
            
            <!-- Breadcrumbs Header Bar -->
            <div class="ace-breadcrumbs">
                <ul class="ace-breadcrumb-trail">
                    <li>
                        <a href="<?= url_to('DashboardController::index') ?>"><i class="fa-solid fa-house me-1"></i>Home</a>
                    </li>
                    <li class="text-muted">/</li>
                    <li class="active"><?= ucfirst(explode('/', uri_string())[0] ?? 'Dashboard') ?></li>
                </ul>

                <div class="ace-search-pill">
                    <form action="<?= url_to('Transactions::index') ?>" method="get" class="m-0">
                        <i class="fa-solid fa-magnifying-glass ace-search-icon"></i>
                        <input type="text" name="search" placeholder="Search transactions..." class="ace-search-input" autocomplete="off">
                    </form>
                </div>
            </div>

            <!-- Dynamic View Content Container -->
            <div class="ace-content">
                <div class="mb-3">
                    <?= $this->renderSection('page_header') ?>
                </div>

                <div>
                    <?= $this->renderSection('content') ?>
                </div>
            </div>

            <!-- Ace Footer Bar -->
            <footer class="ace-footer">
                <div>
                    &copy; <?= date('Y') ?> <strong class="text-primary">Mpesa Analyzer</strong>. All rights reserved.
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#changelogModal" class="text-decoration-none text-muted">
                        <i class="fa-solid fa-code-branch me-1 text-primary"></i> v<?= esc($systemVersion) ?>
                    </a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#docsModal" class="text-decoration-none text-muted">
                        <i class="fa-solid fa-book-open me-1 text-info"></i> Docs
                    </a>
                    <a href="<?= esc($systemGithub) ?>" target="_blank" class="text-decoration-none text-muted">
                        <i class="fa-brands fa-github me-1"></i> GitHub
                    </a>
                </div>
            </footer>
        </main>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <script>
    window.showAlert = function(title, message, type) {
        const icon = type === 'success' ? 'success' : (type === 'danger' || type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'info'));
        if (typeof Swal !== 'undefined') {
            Swal.fire(title, message, icon);
        } else {
            alert(title + ': ' + message);
        }
    };
    </script>

    <?php $baseUrl = base_url(); ?>

    <!-- Mobile Drawer & Theme Toggle Logic -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const mobileToggle = document.getElementById('mobileSidebarToggle');
        const aceSidebar = document.getElementById('aceSidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');

        function toggleSidebar() {
            aceSidebar.classList.toggle('open');
            sidebarBackdrop.classList.toggle('show');
        }

        if (mobileToggle) mobileToggle.addEventListener('click', toggleSidebar);
        if (sidebarBackdrop) sidebarBackdrop.addEventListener('click', toggleSidebar);

        // Dark / Light Theme Handler
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');

        function updateThemeUI(theme) {
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                if (themeIcon) themeIcon.className = 'fa-solid fa-sun text-warning';
                if (themeText) themeText.textContent = 'Light Mode';
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
                if (themeIcon) themeIcon.className = 'fa-solid fa-moon';
                if (themeText) themeText.textContent = 'Dark Mode';
            }
        }

        const currentTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        updateThemeUI(currentTheme);

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const cur = document.documentElement.getAttribute('data-bs-theme') || 'light';
                const next = cur === 'dark' ? 'light' : 'dark';
                localStorage.setItem('theme', next);
                updateThemeUI(next);
            });
        }
    });
    </script>

    <!-- Scan Progress Polling Logic -->
    <script>
    let pollInterval = null;
    const scanBadge = document.getElementById('scanStatusBadge');
    const scanIcon = document.getElementById('scanStatusIcon');
    const scanText = document.getElementById('scanStatusText');

    function startPolling() {
        stopPolling();
        if (scanBadge) scanBadge.classList.remove('d-none');
        pollInterval = setInterval(pollProgress, 2000);
        pollProgress();
    }

    function stopPolling() {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }

    function setBadgeState(state, label) {
        if (!scanBadge) return;
        scanBadge.classList.remove('d-none');
        if (state === 'scanning') {
            scanIcon.className = 'spinner-border spinner-border-sm text-warning';
            scanText.textContent = label || 'Scanning...';
            scanBadge.className = 'ace-nav-btn bg-warning text-dark';
        } else if (state === 'idle') {
            scanIcon.className = 'fa-solid fa-circle text-secondary';
            scanText.textContent = label || 'No scan running';
            scanBadge.className = 'ace-nav-btn bg-transparent';
        } else if (state === 'complete') {
            scanIcon.className = 'fa-solid fa-circle-check text-success';
            scanText.textContent = label || 'Scan complete';
            scanBadge.className = 'ace-nav-btn bg-success';
        } else if (state === 'failed') {
            scanIcon.className = 'fa-solid fa-triangle-exclamation text-danger';
            scanText.textContent = label || 'Scan failed';
            scanBadge.className = 'ace-nav-btn bg-danger';
        }
    }

    function pollProgress() {
        fetch('<?= $baseUrl ?>dashboard/rescan/progress')
            .then(r => r.json())
            .then(data => {
                const total = data.total || 0;
                const processed = data.processed || 0;
                const isTerminal = data.job && ['done', 'error', 'failed', 'cancelled', 'disabled', 'completed'].includes(data.job.status);
                const pct = total > 0 ? Math.min(100, Math.round((processed / total) * 100)) : 0;

                // Update modal progress elements if present
                const pBar = document.getElementById('modalScanProgressBar');
                const pPct = document.getElementById('modalScanPercent');
                const pTotal = document.getElementById('modalScanTotal');
                const pProcessed = document.getElementById('modalScanProcessed');
                const pClassified = document.getElementById('modalScanClassified');
                const pStatus = document.getElementById('modalScanStatusLabel');
                if (pBar) pBar.style.width = pct + '%';
                if (pPct) pPct.textContent = pct + '%';
                if (pTotal) pTotal.textContent = total.toLocaleString();
                if (pProcessed) pProcessed.textContent = processed.toLocaleString();
                if (pClassified) pClassified.textContent = (data.llm_classified || 0).toLocaleString();
                if (pStatus) pStatus.textContent = 'Status: ' + (data.job?.status || data.status || 'Running');

                if (isTerminal) {
                    stopPolling();
                    setBadgeState(data.job.status === 'completed' || data.job.status === 'done' ? 'complete' : 'failed', processed + ' processed');
                } else if (data.running) {
                    setBadgeState('scanning', pct + '% - ' + processed + '/' + total);
                } else {
                    setBadgeState('idle', 'No scan running');
                }
            })
            .catch(() => {});
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetch('<?= $baseUrl ?>dashboard/rescan/progress')
            .then(r => r.json())
            .then(data => {
                if (data.running) startPolling();
            })
            .catch(() => {});
    });

    document.getElementById('rescanBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        const btn = this;
        Swal.fire({
            title: 'Start Rescan?',
            text: 'Analyze all new/unprocessed SMS messages using the LLM engine.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#438EB9',
            confirmButtonText: 'Yes, start!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Scanning...';
                fetch('<?= $baseUrl ?>dashboard/rescan', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                    }
                })
                    .then(async r => {
                        const data = await r.json().catch(() => ({}));
                        if (!r.ok) {
                            throw new Error(data.message || ('Server error: ' + r.status));
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.status === 'started') {
                            startPolling();
                            Swal.fire({
                                title: 'Rescan Started',
                                text: data.message || 'Processing will run in the background.',
                                icon: 'info',
                                timer: 2500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Scan Notice', data.message || 'Scan could not be started.', 'warning');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Scan Error', err.message || 'Failed to trigger rescan.', 'error');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-rotate"></i> Rescan';
                    });
            }
        });
    });

    document.getElementById('rescanAllBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        const btn = this;
        Swal.fire({
            title: 'Reset & Reprocess All?',
            text: 'WARNING: Re-analyze all SMS from scratch.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, reset!'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Resetting...';
                fetch('<?= $baseUrl ?>dashboard/rescan/all', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                    }
                })
                    .then(async r => {
                        const data = await r.json().catch(() => ({}));
                        if (!r.ok) {
                            throw new Error(data.message || ('Server error: ' + r.status));
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.status === 'started') {
                            startPolling();
                            Swal.fire({
                                title: 'Full Reset Started',
                                text: data.message || 'Reprocessing from scratch in the background.',
                                icon: 'info',
                                timer: 2500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Reset Notice', data.message || 'Reset could not be started.', 'warning');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Reset Error', err.message || 'Failed to trigger full reset.', 'error');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Full Reset';
                    });
            }
        });
    });
    </script>

    <!-- Changelog & Docs Modals -->
    <div class="modal fade" id="changelogModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i> System Changelog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
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
                </div>
            </div>
        </div>
    </div>

    <!-- Scan Progress Modal -->
    <div class="modal fade" id="scanProgressModal" tabindex="-1" aria-labelledby="scanProgressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary" id="scanProgressModalLabel">
                        <i class="fa-solid fa-microchip me-2"></i> SMS Analysis Progress
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted fw-semibold" id="modalScanStatusLabel">Status: Idle</span>
                        <span class="badge bg-primary rounded-pill px-3 py-1 fw-bold" id="modalScanPercent">0%</span>
                    </div>
                    <div class="progress mb-3" style="height: 10px; border-radius: 5px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="modalScanProgressBar" style="width: 0%;"></div>
                    </div>
                    <div class="row g-2 text-center small mb-2">
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted" style="font-size: 0.75rem;">Processed</div>
                                <div class="fw-bold fs-6" id="modalScanProcessed">0</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted" style="font-size: 0.75rem;">Total SMS</div>
                                <div class="fw-bold fs-6" id="modalScanTotal">0</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted" style="font-size: 0.75rem;">Classified</div>
                                <div class="fw-bold fs-6 text-success" id="modalScanClassified">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                    <a href="<?= $baseUrl ?>dashboard/history/jobs" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="fa-solid fa-list-check me-1"></i> Full Job History
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Docs Modal -->
    <div class="modal fade" id="docsModal" tabindex="-1" aria-labelledby="docsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary" id="docsModalLabel">
                        <i class="fa-solid fa-book-open me-2"></i> System Documentation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100 border p-3">
                                <h6 class="fw-bold text-primary"><i class="fa-solid fa-circle-question me-2"></i>FAQ &amp; Knowledge Base</h6>
                                <p class="text-muted small mb-2">Answers to common questions about SMS parsing, privacy, and sync features.</p>
                                <a href="<?= base_url('faq') ?>" class="btn btn-sm btn-outline-primary rounded-pill align-self-start mt-auto">Open FAQ</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border p-3">
                                <h6 class="fw-bold text-primary"><i class="fa-solid fa-microchip me-2"></i>ML Classification Engine</h6>
                                <p class="text-muted small mb-2">Details on local GGUF models, classification pipelines, and token usage.</p>
                                <a href="<?= base_url('dashboard/history/jobs') ?>" class="btn btn-sm btn-outline-primary rounded-pill align-self-start mt-auto">View ML Jobs</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
