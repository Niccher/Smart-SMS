<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Server Error - M-Pesa Analyzer</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/ace-theme.css">
    <script>
        (function() {
            const theme = localStorage.getItem('ace_theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    <style>
        :root { --primary: #438EB9; --bg-gradient: linear-gradient(135deg, #E8F2F8 0%, #438EB9 100%); }
        body { font-family: 'Outfit', sans-serif; background: var(--bg-gradient); min-height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; }
        .error-card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 6px; padding: 3rem; max-width: 480px; width: 90%; text-align: center; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12); animation: fadeIn 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .error-code { font-size: 7rem; font-weight: 800; line-height: 1; margin-bottom: 0.25rem; background: linear-gradient(135deg, #2ED573 0%, #10AC84 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .error-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.75rem; color: #2d3436; }
        .error-message { font-size: 1rem; color: #636e72; margin-bottom: 2rem; line-height: 1.6; }
        .btn-primary { background-color: var(--primary); border: none; padding: 12px 32px; border-radius: 4px; font-weight: 600; transition: all 0.3s; }
        .btn-primary:hover { background-color: #337ab7; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(67, 142, 185, 0.35); }
        .illustration { font-size: 4rem; color: var(--primary); margin-bottom: 0.75rem; opacity: 0.85; }

        /* Dark mode support */
        .dark-mode body { background: linear-gradient(135deg, #1C1D21 0%, #0F1015 100%) !important; color: #E4E6EB; }
        .dark-mode .error-card { background: rgba(30, 32, 40, 0.85); border-color: rgba(255, 255, 255, 0.08); box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5); }
        .dark-mode .error-title { color: #F0F2F5; }
        .dark-mode .error-message { color: #B0B3B8; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="illustration"><i class="fa-solid fa-server"></i></div>
        <div class="error-code">500</div>
        <h1 class="error-title">Internal Server Error</h1>
        <p class="error-message">Our servers encountered an unexpected issue. We've been notified and are working on a fix.</p>
        <div class="d-grid gap-2">
            <a href="/" class="btn btn-primary btn-lg"><i class="fa-solid fa-house me-2"></i>Return Home</a>
            <button onclick="location.reload()" class="btn btn-link text-decoration-none text-secondary"><i class="fa-solid fa-rotate me-1"></i>Try Again</button>
        </div>
    </div>
</body>
</html>
