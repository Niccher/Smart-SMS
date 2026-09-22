<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Forbidden - M-Pesa Analyzer</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary: #438EB9; --bg-gradient: linear-gradient(135deg, #E8F2F8 0%, #438EB9 100%); }
        body { font-family: 'Outfit', sans-serif; background: var(--bg-gradient); height: 100vh; margin: 0; display: flex; align-items: center; justify-content: center; }
        .error-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 4px; padding: 3rem; max-width: 480px; width: 90%; text-align: center; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12); animation: fadeIn 0.6s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .error-code { font-size: 7rem; font-weight: 800; line-height: 1; margin-bottom: 0.25rem; background: linear-gradient(135deg, #FF7F50 0%, #FF6347 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .error-title { font-size: 1.75rem; font-weight: 700; margin-bottom: 0.75rem; color: #2d3436; }
        .error-message { font-size: 1rem; color: #636e72; margin-bottom: 2rem; line-height: 1.6; }
        .btn-primary { background-color: var(--primary); border: none; padding: 12px 32px; border-radius: 4px; font-weight: 600; transition: all 0.3s; }
        .btn-primary:hover { background-color: #4A4CD4; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(93, 95, 239, 0.35); }
        .btn-outline-dark { border-radius: 4px; font-weight: 600; padding: 12px 24px; }
        .illustration { font-size: 4rem; color: #FF7F50; margin-bottom: 0.75rem; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="illustration"><i class="fa-solid fa-user-shield"></i></div>
        <div class="error-code">403</div>
        <h1 class="error-title">Admin on User Console</h1>
        <p class="error-message"><?= esc($message ?? 'You don\'t have permission to access this resource.') ?></p>
        <div class="d-grid gap-2">
            <a href="/admin" class="btn btn-primary btn-lg"><i class="fa-solid fa-gauge-high me-2"></i>Go to Admin Panel</a>
            <a href="/auth/logout" class="btn btn-outline-dark"><i class="fa-solid fa-right-from-bracket me-2"></i>Log out &amp; log in as a user</a>
        </div>
    </div>
</body>
</html>
