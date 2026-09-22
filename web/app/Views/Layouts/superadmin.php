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
    <title><?= $this->renderSection('title') ?? 'Mpesa Analyzer - SuperAdmin' ?></title>

    <!-- Primary Meta Tags -->
    <meta name="title" content="<?= $this->renderSection('title') ?? 'Financial Analyzer Ecosystem - SuperAdmin' ?>">
    <meta name="description" content="Universal Multi-Channel Financial Intelligence Suite — SuperAdmin Management System.">
    <meta name="keywords" content="financial analyzer, mpesa analyzer, admin dashboard, system administration">
    <meta name="author" content="Niccher / Financial Analyzer Ecosystem">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#438EB9">

    <!-- Mobile & PWA Web App Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="FinAnalyzer Admin">
    <meta name="application-name" content="FinAnalyzer Admin">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Open Graph / Social Meta Tags -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:site_name" content="Financial Analyzer Ecosystem">
    <meta property="og:title" content="<?= $this->renderSection('title') ?? 'Financial Analyzer Ecosystem' ?>">
    <meta property="og:description" content="Universal Multi-Channel Financial Intelligence Suite SuperAdmin.">
    <meta property="og:image" content="<?= base_url('assets/img/logo.png') ?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= $this->renderSection('title') ?? 'Financial Analyzer Ecosystem' ?>">
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
    <header class="ace-navbar" style="background-color: var(--ace-dark);">
        <div class="d-flex align-items-center gap-3">
            <button class="btn text-white p-0 border-0 d-lg-none" id="mobileSidebarToggle" type="button">
                <i class="fa-solid fa-bars fs-5"></i>
            </button>

            <a href="<?= base_url('admin') ?>" class="ace-brand">
                <i class="fa-solid fa-user-shield"></i>
                <span>Admin Panel</span>
            </a>
        </div>

        <div class="ace-nav-actions">
            <a href="<?= url_to('DashboardController::index') ?>" class="ace-nav-btn bg-primary">
                <i class="fa-solid fa-house"></i> User Dashboard
            </a>

            <!-- Theme Light/Dark Mode Switcher -->
            <button class="ace-nav-btn bg-transparent" id="themeToggleBtn">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
                <span id="themeText" class="d-none d-sm-inline">Dark Mode</span>
            </button>

            <!-- Admin Profile Dropdown -->
            <div class="dropdown ace-user-menu">
                <a href="#" class="ace-user-link dropdown-toggle" data-bs-toggle="dropdown">
                    <?php $username = auth()->user()->username ?? 'SuperAdmin'; ?>
                    <div class="ace-avatar bg-warning text-dark"><?= strtoupper(substr($username, 0, 1)) ?></div>
                    <span class="d-none d-sm-inline"><?= esc($username) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2">
                    <li><a class="dropdown-item py-2" href="<?= base_url('admin') ?>"><i class="fa-solid fa-gauge-high me-2 text-primary"></i> Overview</a></li>
                    <li><a class="dropdown-item py-2" href="<?= base_url('admin/system') ?>"><i class="fa-solid fa-gears me-2 text-primary"></i> System Utilities</a></li>
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
            <div class="ace-shortcuts">
                <a href="<?= base_url('admin') ?>" class="ace-shortcut-btn bg-ace-green" title="Overview"><i class="fa-solid fa-gauge-high"></i></a>
                <a href="<?= base_url('admin/users') ?>" class="ace-shortcut-btn bg-ace-blue" title="Users"><i class="fa-solid fa-users"></i></a>
                <a href="<?= base_url('admin/ml') ?>" class="ace-shortcut-btn bg-ace-amber" title="ML Backend"><i class="fa-solid fa-microchip"></i></a>
                <a href="<?= base_url('admin/system') ?>" class="ace-shortcut-btn bg-ace-red" title="System"><i class="fa-solid fa-wrench"></i></a>
            </div>

            <ul class="ace-nav-list">
                <?php $currentURL = uri_string(); ?>

                <li class="px-3 py-2 text-uppercase fw-bold text-muted" style="font-size: 11px; letter-spacing: 0.5px;">Administration</li>

                <li class="ace-nav-item <?= ($currentURL == 'admin' || $currentURL == 'admin/') ? 'active' : '' ?>">
                    <a href="<?= base_url('admin') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span class="ace-nav-text">Overview</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= strpos($currentURL, 'admin/users') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/users') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-users"></i>
                        <span class="ace-nav-text">Users Management</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= strpos($currentURL, 'admin/devices') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/devices') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-mobile-screen"></i>
                        <span class="ace-nav-text">Connected Devices</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= strpos($currentURL, 'admin/ml') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/ml') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-microchip"></i>
                        <span class="ace-nav-text">ML Classifier</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= strpos($currentURL, 'admin/crons') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/crons') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-clock"></i>
                        <span class="ace-nav-text">Cron Schedules</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= strpos($currentURL, 'admin/notifications') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/notifications') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-envelope"></i>
                        <span class="ace-nav-text">Email Notifications</span>
                    </a>
                </li>

                <li class="px-3 py-2 text-uppercase fw-bold text-muted mt-2" style="font-size: 11px; letter-spacing: 0.5px;">System Operations</li>

                <li class="ace-nav-item <?= strpos($currentURL, 'admin/system') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/system') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-wrench"></i>
                        <span class="ace-nav-text">System Utilities</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= strpos($currentURL, 'admin/audit') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/audit') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span class="ace-nav-text">Audit Trail</span>
                    </a>
                </li>

                <li class="ace-nav-item <?= strpos($currentURL, 'admin/telemetry') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin/telemetry') ?>" class="ace-nav-link">
                        <i class="fa-solid fa-chart-line"></i>
                        <span class="ace-nav-text">Container Telemetry</span>
                    </a>
                </li>
            </ul>

            <div class="p-3 border-top mt-auto">
                <a href="<?= url_to('logout') ?>" class="btn btn-outline-danger btn-sm w-100 fw-semibold">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                </a>
            </div>
        </aside>

        <!-- Main Content Section -->
        <main class="ace-main">
            <div class="ace-breadcrumbs">
                <ul class="ace-breadcrumb-trail">
                    <li><a href="<?= base_url('admin') ?>"><i class="fa-solid fa-shield-halved me-1"></i>Admin Home</a></li>
                    <li class="text-muted">/</li>
                    <li class="active"><?= ucfirst(explode('/', uri_string())[1] ?? 'Overview') ?></li>
                </ul>
            </div>

            <div class="ace-content">
                <div class="mb-3">
                    <?= $this->renderSection('page_header') ?>
                </div>

                <div>
                    <?= $this->renderSection('content') ?>
                </div>
            </div>

            <footer class="ace-footer">
                <div>&copy; <?= date('Y') ?> <strong class="text-primary">Mpesa Analyzer Admin</strong></div>
                <div><span class="badge bg-primary rounded-pill">v<?= esc($systemVersion) ?> SuperAdmin</span></div>
            </footer>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    <?= $this->renderSection('scripts') ?>
</body>
</html>
