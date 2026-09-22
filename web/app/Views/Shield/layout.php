<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Primary Meta Tags -->
    <meta name="title" content="<?= $this->renderSection('title') ?> — Financial Analyzer Ecosystem">
    <meta name="description" content="Secure Authentication — Financial Analyzer Ecosystem.">
    <meta name="theme-color" content="#438EB9">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="FinAnalyzer Auth">
    <meta name="application-name" content="FinAnalyzer Auth">

    <!-- Favicon & PWA Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('favicon.png?v=3.2.0') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico?v=3.2.0') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('apple-touch-icon.png?v=3.2.0') ?>">
    <link rel="manifest" href="<?= base_url('site.webmanifest?v=3.2.0') ?>">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('ace_theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/ace/css/ace-theme.css') ?>" rel="stylesheet" />
    <style>
        :root {
            --primary: #438EB9;
            --primary-dark: #222A2D;
            --secondary: #E8F2F8;
            --dark: #1A1A2E;
            --light: #F8F9FA;
            --radius: 4px;
            --card-bg: rgba(255, 255, 255, 0.9);
            --card-border: rgba(255, 255, 255, 0.4);
            --text-main: #2d3436;
            --text-muted: #636e72;
            --input-bg: #F3F4F6;
        }
        [data-bs-theme="dark"] {
            --card-bg: rgba(30, 41, 59, 0.9);
            --card-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --input-bg: #1e293b;
        }
        * { box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        body { font-family: 'Outfit', sans-serif; background: var(--light); color: var(--dark); margin: 0; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { padding: 1.25rem 0; background: transparent; transition: all 0.3s ease; position: fixed; width: 100%; top: 0; z-index: 1030; }
        .navbar.scrolled { background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); padding: 0.75rem 0; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        [data-bs-theme="dark"] .navbar.scrolled { background: rgba(15, 23, 42, 0.92); }
        .navbar-brand { font-weight: 800; font-size: 1.5rem; letter-spacing: -0.5px; }
        .nav-link { font-weight: 500; transition: color 0.2s; position: relative; }
        .nav-link:hover { color: var(--primary) !important; }
        .btn-primary { background-color: var(--primary); border-color: var(--primary); padding: 0.7rem 1.8rem; font-weight: 600; border-radius: var(--radius); transition: all 0.3s; }
        .btn-primary:hover { background-color: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(93,95,239,0.35); }
        .btn-outline-primary { color: var(--primary); border-color: var(--primary); padding: 0.7rem 1.8rem; font-weight: 600; border-radius: var(--radius); }
        .btn-outline-primary:hover { background-color: var(--primary); color: #fff; transform: translateY(-2px); }
        .auth-section { flex: 1; display: flex; align-items: center; padding: 120px 0 60px; background: linear-gradient(135deg, #fff 0%, var(--secondary) 100%); min-height: 100vh; }
        [data-bs-theme="dark"] .auth-section { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
        .auth-card { background: var(--card-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid var(--card-border); border-radius: var(--radius); padding: 40px 36px; box-shadow: 0 20px 60px rgba(0,0,0,0.1); animation: slideUp 0.6s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        .auth-header { text-align: center; margin-bottom: 28px; }
        .auth-header .brand-icon { width: 52px; height: 52px; background: var(--primary); color: #fff; border-radius: var(--radius); display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: 14px; }
        .auth-header h1 { font-size: 1.65rem; font-weight: 700; color: var(--text-main); margin: 0 0 4px; }
        .auth-header p { color: var(--text-muted); margin: 0; font-size: 0.92rem; }
        .form-group { margin-bottom: 18px; }
        .form-label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; }
        .form-control { width: 100%; padding: 13px 16px; border-radius: var(--radius); border: 2px solid transparent; background: var(--input-bg); font-size: 0.93rem; transition: all 0.3s; color: var(--text-main); }
        .form-control:focus { outline: none; border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(93,95,239,0.12); }
        [data-bs-theme="dark"] .form-control:focus { background: #0f172a; }
        .form-control::placeholder { color: #adb5bd; }
        .btn-primary { width: 100%; padding: 13px; border-radius: var(--radius); border: none; background: var(--primary); color: white; font-size: 1rem; font-weight: 700; cursor: pointer; transition: transform 0.2s, background 0.3s, box-shadow 0.3s; margin-top: 6px; }
        .btn-primary:hover { background: #4A4CD4; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(93,95,239,0.35); }
        .btn-primary:active { transform: translateY(0); }
        .auth-footer { text-align: center; margin-top: 22px; font-size: 0.88rem; color: var(--text-muted); }
        .auth-footer a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }
        .alert { padding: 11px 15px; border-radius: var(--radius); margin-bottom: 18px; font-size: 0.86rem; border: none; }
        .alert-error { background: #FEE2E2; color: #B91C1C; border-left: 4px solid #B91C1C; }
        .alert-success { background: #D1FAE5; color: #065F46; border-left: 4px solid #065F46; }
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
        .benefit-item { display: flex; gap: 14px; align-items: flex-start; margin-bottom: 20px; }
        .benefit-item .icon { width: 40px; height: 40px; background: rgba(93,95,239,0.1); color: var(--primary); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1rem; }
        .footer { background: var(--dark); color: #fff; padding: 3rem 0 1.5rem; flex-shrink: 0; }
        .footer a { color: rgba(255,255,255,0.7); text-decoration: none; transition: color 0.2s; }
        .footer a:hover { color: #fff; }
        @media (max-width: 991.98px) { .auth-section { padding: 100px 0 40px; } }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand text-primary" href="<?= base_url() ?>">
                <i class="fa-solid fa-wallet me-2"></i>Mpesa Analyzer
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#authNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="authNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('android-app') ?>">Android App</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('ml-backend') ?>">ML Backend</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('setup') ?>">Setup</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('faq') ?>">FAQ</a></li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm px-2.5 py-1" id="themeToggleBtn" type="button" title="Toggle Light/Dark Theme">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                    <?php $uri = uri_string(); ?>
                    <?php if ($uri === 'register' || strpos($uri, 'magic-link') !== false): ?>
                        <a href="<?= url_to('login') ?>" class="btn btn-outline-primary btn-sm">Sign In</a>
                    <?php else: ?>
                        <a href="<?= url_to('register') ?>" class="btn btn-primary btn-sm">Get Started</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <section class="auth-section">
        <div class="container">
            <div class="row align-items-center g-5 justify-content-center">
                <div class="col-lg-5 d-none d-lg-block">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 mb-3 fw-semibold">
                        <i class="fa-solid fa-wallet me-1"></i> Mpesa Analyzer
                    </span>
                    <h2 class="fw-bold mb-3" style="font-size: 2rem; letter-spacing: -0.3px;">Take Control of Your <span class="text-primary">Mobile Money</span></h2>
                    <p class="text-muted mb-4" style="line-height: 1.7;">
                        Automatically sync your SMS, classify transactions with AI, and visualize your spending in real time.
                    </p>
                    <div class="benefit-item">
                        <div class="icon"><i class="fa-solid fa-microchip"></i></div>
                        <div><h6 class="fw-bold mb-0">ML-Powered</h6><small class="text-muted">Fine-tuned LLM classifies every transaction</small></div>
                    </div>
                    <div class="benefit-item">
                        <div class="icon"><i class="fa-solid fa-mobile-screen"></i></div>
                        <div><h6 class="fw-bold mb-0">Auto Sync</h6><small class="text-muted">Android app forwards SMS in real time</small></div>
                    </div>
                    <div class="benefit-item">
                        <div class="icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <div><h6 class="fw-bold mb-0">Privacy First</h6><small class="text-muted">Non-financial SMS never stored</small></div>
                    </div>
                </div>
                <div class="col-lg-5 col-md-8 col-12">
                    <div class="auth-card">
                        <?= $this->renderSection('main') ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-5">
                    <h5 class="text-white fw-bold mb-3"><i class="fa-solid fa-wallet me-2"></i>Mpesa Analyzer</h5>
                    <p class="opacity-75 small mb-0">AI-powered financial intelligence platform.</p>
                </div>
                <div class="col-md-2">
                    <h6 class="text-white fw-bold mb-3">Platform</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= base_url('android-app') ?>">Android App</a></li>
                        <li class="mb-2"><a href="<?= base_url('ml-backend') ?>">ML Backend</a></li>
                        <li class="mb-2"><a href="<?= base_url('setup') ?>">Setup Guide</a></li>
                        <li class="mb-2"><a href="<?= base_url('faq') ?>">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h6 class="text-white fw-bold mb-3">Account</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?= url_to('login') ?>">Sign In</a></li>
                        <li class="mb-2"><a href="<?= url_to('register') ?>">Register</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="text-white fw-bold mb-3">Tech</h6>
                    <div class="d-flex flex-wrap gap-1 small opacity-75">
                        <span class="badge bg-light text-dark">CodeIgniter 4</span>
                        <span class="badge bg-light text-dark">FastAPI</span>
                        <span class="badge bg-light text-dark">LLM</span>
                        <span class="badge bg-light text-dark">MySQL</span>
                        <span class="badge bg-light text-dark">Docker</span>
                    </div>
                </div>
            </div>
            <hr class="mt-3 mb-3 opacity-25">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small opacity-75">
                <p class="mb-0">&copy; <?= date('Y') ?> Mpesa Analyzer. All rights reserved.</p>
                <p class="mb-0 mt-2 mt-md-0">Built with <i class="fa-solid fa-heart text-danger"></i> for financial freedom</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('scroll', function() { document.querySelector('.navbar').classList.toggle('scrolled', window.scrollY > 50); });

        document.addEventListener('DOMContentLoaded', function() {
            const themeBtn = document.getElementById('themeToggleBtn');
            const html = document.documentElement;

            function updateIcon(theme) {
                if (themeBtn) {
                    const icon = themeBtn.querySelector('i');
                    if (icon) {
                        icon.className = theme === 'dark' ? 'fa-solid fa-sun text-warning' : 'fa-solid fa-moon';
                    }
                }
            }

            const currentTheme = localStorage.getItem('ace_theme') || 'light';
            updateIcon(currentTheme);

            if (themeBtn) {
                themeBtn.addEventListener('click', function() {
                    const newTheme = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                    html.setAttribute('data-bs-theme', newTheme);
                    localStorage.setItem('ace_theme', newTheme);
                    updateIcon(newTheme);
                });
            }
        });
    </script>
</body>
</html>
