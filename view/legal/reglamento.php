<?php
/**
 * view/legal/reglamento.php
 * Página pública del Reglamento de uso, privacidad y riesgos (no requiere iniciar sesión).
 */
?><!doctype html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'es') ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= Lang::get('legal.titulo') ?> — Etreva Travel</title>
<style>
    :root { --fondo:#f1f5f9; --tarjeta:#fff; --texto:#1e293b; --suave:#64748b; --acento:#4f46e5; --borde:#e2e8f0; }
    @media (prefers-color-scheme: dark) { :root { --fondo:#0f172a; --tarjeta:#1e293b; --texto:#e2e8f0; --suave:#94a3b8; --acento:#818cf8; --borde:#334155; } }
    body { margin:0; background:var(--fondo); color:var(--texto); font-family:system-ui,-apple-system,"Segoe UI",sans-serif; line-height:1.6; }
    main { max-width:820px; margin:0 auto; padding:32px 16px 48px; }
    .tarjeta { background:var(--tarjeta); border:1px solid var(--borde); border-radius:18px; padding:28px clamp(18px,4vw,40px); }
    h1 { margin:0 0 4px; font-size:1.6rem; }
    .sub { color:var(--suave); margin:0 0 18px; }
    h4 { color:var(--acento); margin:22px 0 8px; font-size:1.02rem; }
    a { color:var(--acento); }
    .reg-meta { color:var(--suave); font-size:.85rem; }
    .volver { display:inline-block; margin-bottom:16px; text-decoration:none; font-weight:600; }
</style>
</head>
<body>
<main>
    <a class="volver" href="index.php">← Etreva Travel</a>
    <div class="tarjeta">
        <h1><?= Lang::get('legal.titulo') ?></h1>
        <p class="sub"><?= Lang::get('legal.rige_espanol') ?></p>
        <?php include __DIR__ . '/reglamento_texto.php'; ?>
    </div>
</main>
</body>
</html>
