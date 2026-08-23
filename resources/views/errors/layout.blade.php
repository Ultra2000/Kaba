<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · KABA</title>
    <link rel="icon" type="image/png" href="/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --brand:#7C3AED; --brand-dark:#6D28D9; --dark:#1A1523; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Poppins',system-ui,sans-serif;
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            background:radial-gradient(1200px 600px at 50% -10%, #F3EEFF 0%, #ffffff 55%);
            color:var(--dark); padding:24px;
        }
        .card { text-align:center; max-width:560px; width:100%; }
        .logo { display:inline-flex; align-items:center; gap:10px; margin-bottom:40px; text-decoration:none; }
        .logo img { height:36px; }
        .logo span { font-weight:900; font-size:26px; letter-spacing:-1px; color:var(--dark); }
        .icon {
            width:96px; height:96px; border-radius:28px; margin:0 auto 28px;
            display:flex; align-items:center; justify-content:center; font-size:42px; color:#fff;
            background:linear-gradient(135deg, var(--brand), var(--brand-dark));
            box-shadow:0 20px 40px -12px rgba(124,58,237,.45);
        }
        .code { font-size:15px; font-weight:800; letter-spacing:3px; text-transform:uppercase; color:var(--brand); margin-bottom:12px; }
        h1 { font-size:30px; font-weight:800; line-height:1.15; margin-bottom:14px; letter-spacing:-.5px; }
        p { font-size:16px; color:#6B7280; line-height:1.6; margin-bottom:32px; }
        .actions { display:flex; gap:12px; justify-content:center; flex-wrap:wrap; }
        .btn {
            display:inline-flex; align-items:center; gap:8px; height:52px; padding:0 26px;
            border-radius:999px; font-weight:700; font-size:15px; text-decoration:none; transition:.15s; cursor:pointer; border:none;
        }
        .btn-primary { background:var(--brand); color:#fff; box-shadow:0 12px 24px -8px rgba(124,58,237,.5); }
        .btn-primary:hover { background:var(--brand-dark); transform:translateY(-1px); }
        .btn-ghost { background:#fff; color:var(--dark); border:2px solid #E5E7EB; }
        .btn-ghost:hover { border-color:var(--dark); }
        @media (max-width:480px){ h1{font-size:24px;} .icon{width:80px;height:80px;font-size:34px;border-radius:24px;} }
    </style>
</head>
<body>
    <div class="card">
        <a href="/" class="logo">
            <img src="/images/logo-trans.png" alt="KABA">
            <span>KABA</span>
        </a>
        <div class="icon"><i class="fa-solid @yield('icon')"></i></div>
        <div class="code">Erreur @yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            <a href="/" class="btn btn-primary"><i class="fa-solid fa-house"></i> Retour à l'accueil</a>
            <a href="/explorer" class="btn btn-ghost"><i class="fa-solid fa-compass"></i> Explorer les livres</a>
        </div>
    </div>
</body>
</html>
