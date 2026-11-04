<?php
$lang             = $_SESSION['lang'] ?? 'es';
$nombreExplorador = Lang::get('planes.explorador_nombre');
$nombreAventura   = Lang::get('planes.aventura_nombre');
$nombrePremium    = Lang::get('planes.premium_nombre');
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Lang::get('pago_pendiente.titulo') ?> - Etreva Travel</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary:#667eea;--secondary:#764ba2;--text-primary:#f5f5f5;
            --bg-base:#1a1a2e;--bg-deep:#0f1629;--surf:#1e2140;--surf2:#252848;
            --border:rgba(255,255,255,0.07);--muted:rgba(255,255,255,0.4);
            --gold:#f59e0b;
            --sh-sm:0 2px 12px rgba(0,0,0,0.3);--sh-md:0 8px 32px rgba(0,0,0,0.4);
            --sh-lg:0 20px 60px rgba(0,0,0,0.5);--sh-xl:0 32px 80px rgba(0,0,0,0.6);
            --r:20px;--r-sm:12px;
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:var(--bg-base);color:var(--text-primary);font-family:'DM Sans',sans-serif;line-height:1.6;min-height:100vh;}
        body::before{content:'';position:fixed;inset:0;z-index:-1;background-image:radial-gradient(circle at 20% 30%,rgba(102,126,234,.08) 0%,transparent 40%),radial-gradient(circle at 80% 70%,rgba(118,75,162,.08) 0%,transparent 40%);pointer-events:none;}
        .container{max-width:1240px;margin:0 auto;padding:0 28px;}

        /* HERO */
        .hero-section{padding:90px 28px 80px;background:linear-gradient(135deg,var(--bg-deep) 0%,#1a1040 50%,var(--bg-base) 100%);text-align:center;position:relative;overflow:hidden;border-bottom:1px solid var(--border);}
        .hero-section::before{content:'';position:absolute;top:-80px;right:-80px;width:400px;height:400px;background:radial-gradient(circle,rgba(102,126,234,.18) 0%,transparent 70%);pointer-events:none;}
        .hero-section::after{content:'⛰';position:absolute;bottom:-40px;right:-20px;font-size:280px;opacity:.06;transform:rotate(-8deg);pointer-events:none;line-height:1;}
        .hero-inner{position:relative;z-index:1;}
        .hero-pill{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.08);border:1px solid var(--border);color:rgba(255,255,255,.55);font-size:.78rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;padding:6px 18px;border-radius:50px;margin-bottom:22px;backdrop-filter:blur(8px);}
        .hero-pill span{width:6px;height:6px;border-radius:50%;background:var(--primary);display:inline-block;}
        .hero-section h1{font-family:'Syne',sans-serif;font-size:clamp(2.2rem,5vw,3.8rem);font-weight:800;background:linear-gradient(135deg,#fff 0%,#b8c3f8 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:18px;line-height:1.15;}
        .hero-section h1 em{font-style:normal;background:linear-gradient(135deg,var(--primary),var(--secondary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
        .hero-section p{font-size:1.1rem;color:var(--muted);max-width:560px;margin:0 auto 32px;}
        .hero-section p strong{color:var(--primary);font-weight:700;}
        .btn-hero{display:inline-flex;align-items:center;gap:10px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;font-family:'DM Sans',sans-serif;font-weight:700;font-size:.95rem;padding:14px 32px;border-radius:50px;text-decoration:none;border:none;cursor:pointer;box-shadow:0 8px 24px rgba(102,126,234,.3);transition:all .3s ease;}
        .btn-hero:hover{transform:translateY(-3px);box-shadow:0 16px 40px rgba(102,126,234,.45);}
        .hero-stats{display:flex;justify-content:center;gap:40px;margin-top:48px;flex-wrap:wrap;}
        .hero-stat{text-align:center;border-left:1px solid var(--border);padding-left:40px;}
        .hero-stat:first-child{border-left:none;padding-left:0;}
        .hero-stat-num{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:var(--gold);line-height:1;}
        .hero-stat-lbl{font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-top:4px;}

        /* PLANES HEADER */
        .plans-header{text-align:center;padding:72px 0 36px;}
        .plans-header .section-tag{display:inline-block;background:rgba(102,126,234,.1);color:var(--primary);font-size:.72rem;font-weight:800;letter-spacing:2px;text-transform:uppercase;padding:5px 16px;border-radius:50px;margin-bottom:14px;border:1px solid rgba(102,126,234,.2);}
        .plans-header h2{font-family:'Syne',sans-serif;font-size:clamp(1.8rem,4vw,2.8rem);font-weight:800;color:white;margin-bottom:12px;}
        .plans-header p{color:var(--muted);max-width:520px;margin:0 auto;}

        /* BILLING */
        .billing-wrap{display:flex;justify-content:center;margin:28px 0 44px;}
        .billing-toggle{display:inline-flex;background:var(--surf);border:1px solid var(--border);border-radius:50px;padding:5px;gap:4px;}
        .billing-option{padding:10px 26px;border-radius:50px;cursor:pointer;font-weight:700;font-size:.85rem;transition:all .25s ease;color:var(--muted);user-select:none;white-space:nowrap;}
        .billing-option.active{background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;box-shadow:0 4px 12px rgba(102,126,234,.3);}
        .billing-option:not(.active):hover{background:rgba(255,255,255,.05);color:var(--text-primary);}

        /* CARDS */
        .plans-container{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;margin-bottom:80px;align-items:start;}
        .plan-card{background:var(--surf);border:1px solid var(--border);border-radius:var(--r);padding:36px 28px 28px;position:relative;transition:transform .3s ease,box-shadow .3s ease;display:flex;flex-direction:column;box-shadow:var(--sh-sm);overflow:hidden;}
        .plan-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--primary),var(--secondary));border-radius:var(--r) var(--r) 0 0;}
        .plan-card.featured::before{background:linear-gradient(90deg,var(--gold),var(--primary));}
        .plan-card:hover{transform:translateY(-8px);box-shadow:var(--sh-lg);border-color:rgba(102,126,234,.3);}
        .plan-card.featured{border-color:rgba(102,126,234,.4);box-shadow:var(--sh-md);}
        .plan-card.featured:hover{transform:translateY(-10px);box-shadow:var(--sh-xl);}
        .badge-popular{position:absolute;top:-1px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;font-size:.68rem;font-weight:800;letter-spacing:.8px;text-transform:uppercase;padding:5px 18px;border-radius:0 0 14px 14px;box-shadow:0 4px 12px rgba(102,126,234,.4);white-space:nowrap;}
        .plan-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:18px;background:rgba(102,126,234,.15);color:var(--primary);}
        .plan-card.featured .plan-icon{background:rgba(245,158,11,.15);color:var(--gold);}
        .plan-card h3{font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;color:white;margin-bottom:6px;}
        .plan-card.featured h3{color:var(--gold);}
        .plan-card .subtitle{color:var(--muted);font-size:.88rem;margin-bottom:20px;min-height:42px;}
        .price-box{margin:18px 0 6px;}
        .price{font-family:'Syne',sans-serif;font-size:2.6rem;font-weight:800;color:white;line-height:1;}
        .plan-card.featured .price{color:var(--gold);}
        .price small{font-family:'DM Sans',sans-serif;font-size:.85rem;font-weight:500;color:var(--muted);vertical-align:middle;}
        .btn-plan-elegir{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:13px 20px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;font-family:'DM Sans',sans-serif;font-weight:700;font-size:.9rem;border:none;border-radius:50px;cursor:pointer;transition:all .25s ease;box-shadow:0 4px 16px rgba(102,126,234,.3);margin:16px 0;text-decoration:none;}
        .btn-plan-elegir:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(102,126,234,.45);}
        .plan-card.featured .btn-plan-elegir{background:linear-gradient(135deg,var(--gold),#d97706);box-shadow:0 4px 16px rgba(245,158,11,.3);}
        .plan-card.featured .btn-plan-elegir:hover{box-shadow:0 8px 28px rgba(245,158,11,.45);}
        .plan-card ul{list-style:none;padding:0;margin:18px 0 0;flex-grow:1;}
        .plan-card ul li{display:flex;align-items:flex-start;gap:10px;padding:7px 0;border-bottom:1px dashed rgba(255,255,255,.04);font-size:.88rem;color:var(--muted);}
        .plan-card ul li:last-child{border-bottom:none;}
        .plan-card ul li .li-icon{width:18px;height:18px;flex-shrink:0;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(102,126,234,.2);color:var(--primary);font-size:.6rem;margin-top:1px;}
        .additional-features{margin-top:20px;padding-top:20px;border-top:1.5px dashed var(--border);}
        .additional-features h4{font-size:.72rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:12px;}
        .additional-features ul li .li-icon{background:rgba(245,158,11,.15);color:var(--gold);}

        /* SATISFACTION */
        .satisfaction-section{text-align:center;margin:0 0 80px;padding:60px 40px;background:linear-gradient(135deg,var(--bg-deep),var(--surf));border:1px solid var(--border);border-radius:var(--r);position:relative;overflow:hidden;box-shadow:var(--sh-lg);}
        .satisfaction-section::before{content:'🌿';position:absolute;bottom:-40px;right:-20px;font-size:220px;opacity:.06;pointer-events:none;}
        .satisfaction-section h3{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;color:white;margin-bottom:12px;position:relative;z-index:1;}
        .satisfaction-section>p{max-width:620px;margin:0 auto 40px;color:var(--muted);font-size:.95rem;position:relative;z-index:1;}
        .stats-container{display:flex;justify-content:center;gap:0;flex-wrap:wrap;position:relative;z-index:1;}
        .stat-item{text-align:center;padding:0 40px;border-right:1px solid var(--border);}
        .stat-item:last-child{border-right:none;}
        .stat-number{font-family:'Syne',sans-serif;font-size:2.6rem;font-weight:800;color:var(--gold);line-height:1;}
        .stat-label{font-size:.78rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-top:6px;}

        /* FOOTER */
        .footer{text-align:center;padding:48px 20px 32px;background:var(--bg-deep);color:var(--muted);border-top:3px solid var(--primary);font-size:.85rem;}
        .footer p+p{margin-top:8px;}
        .footer a{color:var(--primary);text-decoration:none;font-weight:700;transition:color .2s;}
        .footer a:hover{color:white;}

        /* MODAL */
        .modal-custom{display:none;position:fixed;inset:0;z-index:2000;background:rgba(15,22,41,.88);backdrop-filter:blur(6px);align-items:center;justify-content:center;}
        .modal-custom.open{display:flex;}
        .modal-box{background:var(--surf2);border:1px solid var(--border);border-radius:var(--r);padding:28px 28px 24px;width:90%;max-width:500px;position:relative;box-shadow:var(--sh-xl);animation:modalIn .3s ease;max-height:90vh;overflow-y:auto;}
        @keyframes modalIn{from{transform:translateY(24px);opacity:0}to{transform:none;opacity:1}}
        .modal-box::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,var(--primary),var(--secondary));border-radius:var(--r) var(--r) 0 0;}
        .modal-close{position:absolute;top:16px;right:18px;background:none;border:none;font-size:1.3rem;color:var(--muted);cursor:pointer;transition:color .2s;line-height:1;}
        .modal-close:hover{color:white;}
        .modal-step-tag{display:inline-block;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;font-size:.65rem;font-weight:800;letter-spacing:1.5px;text-transform:uppercase;padding:4px 14px;border-radius:50px;margin-bottom:10px;}
        .modal-box h2{font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;color:white;margin-bottom:4px;}
        .modal-price{font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:800;color:var(--gold);margin-bottom:16px;}
        .modal-divider{border:none;border-top:1.5px dashed var(--border);margin:14px 0;}

        /* QR box */
        .modal-qr-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 16px;
            padding: 20px 16px 16px;
            margin-bottom: 4px;
            text-align: center;
        }
        .modal-qr-box img {
            width: 200px;
            height: 200px;
            border-radius: 12px;
            background: #fff;
            padding: 8px;
            object-fit: contain;
            margin-bottom: 12px;
        }
        .modal-qr-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(102,126,234,.15);
            border: 1px solid rgba(102,126,234,.3);
            color: #b8c3f8;
            font-size: .65rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 50px;
            margin-bottom: 8px;
        }
        .modal-qr-info p {
            font-size: .82rem;
            color: rgba(255,255,255,.5);
            line-height: 1.5;
            margin: 0;
            max-width: 280px;
        }
        .modal-qr-info strong { color: rgba(255,255,255,.85); }

        .form-group{margin-bottom:14px;}
        .form-group label{display:block;margin-bottom:6px;font-weight:700;font-size:.78rem;color:var(--primary);text-transform:uppercase;letter-spacing:.5px;}
        .form-group input[type=file]{width:100%;padding:10px 14px;border-radius:var(--r-sm);border:1.5px dashed var(--border);background:var(--bg-base);font-family:'DM Sans',sans-serif;font-size:.85rem;color:var(--text-primary);cursor:pointer;transition:border-color .2s;}
        .form-group input[type=file]:hover{border-color:var(--primary);}
        .btn-confirmar{width:100%;padding:14px;background:linear-gradient(135deg,var(--primary),var(--secondary));color:white;font-family:'DM Sans',sans-serif;font-weight:700;font-size:.95rem;border:none;border-radius:50px;cursor:pointer;box-shadow:0 6px 20px rgba(102,126,234,.3);transition:all .25s;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:4px;}
        .btn-confirmar:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(102,126,234,.45);}

        @media(max-width:900px){
            .plans-container{grid-template-columns:1fr;max-width:480px;margin-left:auto;margin-right:auto;}
            .stats-container{gap:0;}
            .stat-item{padding:16px 24px;border-right:none;border-bottom:1px solid var(--border);}
            .stat-item:last-child{border-bottom:none;}
            .hero-stats{gap:0;flex-direction:column;}
            .hero-stat{border-left:none;padding-left:0;border-top:1px solid var(--border);padding-top:16px;}
            .hero-stat:first-child{border-top:none;padding-top:0;}
        }
     
    </style>
</head>
<body>

<!-- ═══ HERO ═══ -->
<div class="hero-section">
    <div class="container">
        <div class="hero-inner">
            <div class="hero-pill">
                <span></span> <?= Lang::get('pago_pendiente.hero_pill') ?>
            </div>
            <h1>
                <?= Lang::get('pago_pendiente.hero_h1_1') ?><br>
                <em><?= Lang::get('pago_pendiente.hero_h1_2') ?></em>
            </h1>
            <p>
                <?= Lang::get('pago_pendiente.hola') ?>
                <strong><?= htmlspecialchars($_SESSION['session_usuario'] ?? Lang::get('pago_pendiente.viajero')) ?></strong>.
                <?= Lang::get('pago_pendiente.hero_desc') ?>
            </p>
            <a href="#seccion-planes" class="btn-hero">
                <i class="fas fa-map-marked-alt"></i> <?= Lang::get('pago_pendiente.comenzar') ?>
            </a>
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-num">98%</div>
                    <div class="hero-stat-lbl"><?= Lang::get('pago_pendiente.satisfaccion') ?></div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-num">+500</div>
                    <div class="hero-stat-lbl"><?= Lang::get('pago_pendiente.recomendaciones') ?></div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-num">24/7</div>
                    <div class="hero-stat-lbl"><?= Lang::get('pago_pendiente.ia_activa') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ PLANES ═══ -->
<div class="container" id="seccion-planes">
    <div class="plans-header">
        <div class="section-tag"><?= Lang::get('pago_pendiente.elegir_exp') ?></div>
        <h2><?= Lang::get('pago_pendiente.planes_titulo') ?></h2>
        <p><?= Lang::get('pago_pendiente.planes_subtitulo') ?></p>
    </div>

    <div class="billing-wrap">
        <div class="billing-toggle">
            <div class="billing-option active" data-period="weekly"><?= Lang::get('planes.semanal') ?></div>
            <div class="billing-option"        data-period="monthly"><?= Lang::get('planes.mensual') ?></div>
            <div class="billing-option"        data-period="yearly"><?= Lang::get('planes.anual') ?></div>
        </div>
    </div>

    <div class="plans-container">

        <!-- ── Plan 1: Explorador ── -->
        <div class="plan-card" data-plan="<?= htmlspecialchars($nombreExplorador) ?>">
            <div class="plan-icon">🗺️</div>
            <h3><?= $nombreExplorador ?></h3>
            <p class="subtitle"><?= Lang::get('planes.explorador_desc') ?></p>
            <div class="price-box"><div class="price"></div></div>
            <button class="btn-plan-elegir btn-elegir-plan" data-plan="<?= htmlspecialchars($nombreExplorador) ?>">
                <i class="fas fa-arrow-right"></i> <?= Lang::get('planes.elegir_btn') ?>
            </button>
            <ul>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_5rec') ?></li>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_base_datos') ?></li>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_perfil_basico') ?></li>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_soporte_email') ?></li>
            </ul>
            <div class="additional-features">
                <h4>🌿 <?= Lang::get('pago_pendiente.incluye') ?></h4>
                <ul>
                    <li><span class="li-icon"><i class="fas fa-star"></i></span><?= Lang::get('pago_pendiente.inc_miradores') ?></li>
                    <li><span class="li-icon"><i class="fas fa-star"></i></span><?= Lang::get('pago_pendiente.inc_mapa') ?></li>
                </ul>
            </div>
        </div>

        <!-- ── Plan 2: Aventura (featured) ── -->
        <div class="plan-card featured" data-plan="<?= htmlspecialchars($nombreAventura) ?>">
            <div class="badge-popular"><?= Lang::get('planes.tag_popular') ?></div>
            <div class="plan-icon">⛰️</div>
            <h3><?= $nombreAventura ?></h3>
            <p class="subtitle"><?= Lang::get('planes.aventura_desc') ?></p>
            <div class="price-box"><div class="price"></div></div>
            <button class="btn-plan-elegir btn-elegir-plan" data-plan="<?= htmlspecialchars($nombreAventura) ?>">
                <i class="fas fa-rocket"></i> <?= Lang::get('planes.elegir_btn') ?>
            </button>
            <ul>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_dinamicas') ?></li>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_historial') ?></li>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_ajuste_auto') ?></li>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_analisis') ?></li>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_soporte_prio') ?></li>
            </ul>
            <div class="additional-features">
                <h4>🌿 <?= Lang::get('pago_pendiente.incluye') ?></h4>
                <ul>
                    <li><span class="li-icon"><i class="fas fa-star"></i></span><?= Lang::get('pago_pendiente.inc_miradores') ?></li>
                    <li><span class="li-icon"><i class="fas fa-star"></i></span><?= Lang::get('pago_pendiente.inc_mapa') ?></li>
                    <li><span class="li-icon"><i class="fas fa-star"></i></span><?= Lang::get('pago_pendiente.inc_rutas') ?></li>
                </ul>
            </div>
        </div>

        <!-- ── Plan 3: Premium ── -->
        <div class="plan-card" data-plan="<?= htmlspecialchars($nombrePremium) ?>">
            <div class="plan-icon">👑</div>
            <h3><?= $nombrePremium ?></h3>
            <p class="subtitle"><?= Lang::get('planes.premium_desc') ?></p>
            <div class="price-box"><div class="price"></div></div>
            <button class="btn-plan-elegir btn-elegir-plan" data-plan="<?= htmlspecialchars($nombrePremium) ?>">
                <i class="fas fa-crown"></i> <?= Lang::get('planes.elegir_btn') ?>
            </button>
            <ul>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_evolutivo') ?></li>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_predictivo') ?></li>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_tiempo_real') ?></li>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_asistente') ?></li>
                <li><span class="li-icon"><i class="fas fa-check"></i></span><?= Lang::get('planes.f_vip') ?></li>
            </ul>
            <div class="additional-features">
                <h4>🌿 <?= Lang::get('pago_pendiente.incluye') ?></h4>
                <ul>
                    <li><span class="li-icon"><i class="fas fa-star"></i></span><?= Lang::get('pago_pendiente.inc_vip') ?></li>
                    <li><span class="li-icon"><i class="fas fa-star"></i></span><?= Lang::get('pago_pendiente.inc_traslados') ?></li>
                    <li><span class="li-icon"><i class="fas fa-star"></i></span><?= Lang::get('pago_pendiente.inc_miradores_ex') ?></li>
                    <li><span class="li-icon"><i class="fas fa-star"></i></span><?= Lang::get('pago_pendiente.inc_reportes') ?></li>
                </ul>
            </div>
        </div>

    </div>

    <!-- ═══ SATISFACCIÓN ═══ -->
    <div class="satisfaction-section">
        <h3><?= Lang::get('pago_pendiente.analisis_titulo') ?></h3>
        <p><?= Lang::get('pago_pendiente.analisis_desc') ?></p>
        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-number">98%</div>
                <div class="stat-label"><?= Lang::get('pago_pendiente.satisfaccion') ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number">+500</div>
                <div class="stat-label"><?= Lang::get('pago_pendiente.recomendaciones') ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number">24/7</div>
                <div class="stat-label"><?= Lang::get('pago_pendiente.ia_activa') ?></div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ FOOTER ═══ -->
<div class="footer">
    <div class="container">
        <p><?= Lang::get('pago_pendiente.footer_copy') ?></p>
        <p style="margin-top:6px;"><?= Lang::get('pago_pendiente.footer_sub') ?></p>
        <p style="margin-top:10px;">
            <a href="#"><?= Lang::get('pago_pendiente.footer_terminos') ?></a> &nbsp;·&nbsp;
            <a href="#"><?= Lang::get('pago_pendiente.footer_privacidad') ?></a> &nbsp;·&nbsp;
            <a href="#"><?= Lang::get('pago_pendiente.footer_contacto') ?></a>
        </p>
    </div>
</div>

<!-- ═══ MODAL PAGO ═══ -->
<div id="modalPlan" class="modal-custom">
    <div class="modal-box">
        <button class="modal-close" onclick="cerrarModal()" aria-label="Cerrar">&times;</button>
        <div class="modal-step-tag"><?= Lang::get('planes.modal_paso') ?></div>
        <h2 id="display-nombre-plan">Plan</h2>
        <div class="modal-price" id="display-precio-plan"></div>

        <hr class="modal-divider">

        <form id="form-datos-pago" enctype="multipart/form-data">
            <!-- ── Método de pago: Yape · QR (Bolivia) o USDT/USDC BEP-20 ── -->
            <?php include __DIR__ . '/partials/metodos_pago.php'; ?>
            <div class="form-group">
                <label><?= Lang::get('pago_pendiente.modal_label') ?></label>
                <input type="file" id="comprobante-file" name="comprobante" accept="image/*">
                <input type="hidden" id="hidden-plan"    name="plan">
                <input type="hidden" id="hidden-precio"  name="monto">
                <input type="hidden" id="hidden-periodo" name="periodo">
                <input type="hidden" name="id_usuario"   value="<?= $_SESSION['user_id'] ?? '' ?>">
            </div>
            <button type="submit" class="btn-confirmar">
                <i class="fas fa-rocket"></i> <?= Lang::get('planes.modal_activar') ?>
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal          = document.getElementById('modalPlan');
    const billingOptions = document.querySelectorAll('.billing-option');
    const planCards      = document.querySelectorAll('.plan-card');

    const nombreExplorador = <?= json_encode($nombreExplorador) ?>;
    const nombreAventura   = <?= json_encode($nombreAventura) ?>;
    const nombrePremium    = <?= json_encode($nombrePremium) ?>;

    const precios = {};
    precios[nombreExplorador] = { weekly: 8,  monthly: 10, yearly: 40 };
    precios[nombreAventura]   = { weekly: 10, monthly: 20, yearly: 60 };
    precios[nombrePremium]    = { weekly: 15, monthly: 30, yearly: 90 };

    const periodoTextos = {
        weekly:  <?= json_encode(Lang::get('pago_pendiente.periodo_semana')) ?>,
        monthly: <?= json_encode(Lang::get('pago_pendiente.periodo_mes')) ?>,
        yearly:  <?= json_encode(Lang::get('pago_pendiente.periodo_anio')) ?>
    };
    const periodoUnid = {
        weekly:  '/' + periodoTextos.weekly,
        monthly: '/' + periodoTextos.monthly,
        yearly:  '/' + periodoTextos.yearly
    };

    let periodoActivo = 'weekly';

    function renderPrecios() {
        planCards.forEach(card => {
            const nombre   = card.dataset.plan;
            const priceDiv = card.querySelector('.price');
            if (!nombre || !precios[nombre] || !priceDiv) return;
            const valor = precios[nombre][periodoActivo];
            priceDiv.innerHTML = `$${valor}<small> ${periodoUnid[periodoActivo]}</small>`;
        });
    }

    billingOptions.forEach(opt => {
        opt.addEventListener('click', function () {
            billingOptions.forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            periodoActivo = this.dataset.period;
            renderPrecios();
        });
    });

    document.querySelectorAll('.btn-elegir-plan').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const nombre = this.dataset.plan;
            const precio = precios[nombre]?.[periodoActivo] ?? 0;
            const ptexto = periodoTextos[periodoActivo];
            document.getElementById('display-nombre-plan').textContent = nombre;
            document.getElementById('display-precio-plan').textContent = `$${precio} / ${ptexto}`;
            document.getElementById('hidden-plan').value    = nombre;
            document.getElementById('hidden-precio').value  = precio;
            document.getElementById('hidden-periodo').value = periodoActivo;
            EtrevaPago.setMonto(precio);
            modal.classList.add('open');
        });
    });

    window.cerrarModal = function () { modal.classList.remove('open'); };
    modal.addEventListener('click', e => { if (e.target === modal) cerrarModal(); });

    document.getElementById('form-datos-pago').onsubmit = async function (e) {
        e.preventDefault();
        // Yape y Binance Pay: captura del comprobante. Cripto: captura o hash de la transacción.
        if (!EtrevaPago.valido(document.getElementById('comprobante-file').files.length > 0)) return;
        const formData = new FormData(this);
        cerrarModal();

        Swal.fire({
            title: <?= json_encode(Lang::get('pago_pendiente.procesando')) ?>,
            text:  <?= json_encode(Lang::get('pago_pendiente.validando')) ?>,
            allowOutsideClick: false,
            background: '#1a1a2e', color: '#f5f5f5',
            didOpen: () => Swal.showLoading(),
        });

        try {
            const response = await fetch('index.php?c=pago&a=ProcesarPago', { method: 'POST', body: formData });
            const result   = await response.json();

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: <?= json_encode(Lang::get('pago_pendiente.exito_titulo')) ?>,
                    text:  <?= json_encode(Lang::get('pago_pendiente.exito_texto')) ?>,
                    confirmButtonColor: '#667eea',
                    background: '#1a1a2e', color: '#f5f5f5',
                }).then(() => { window.location.href = 'index.php?c=login&a=Logout'; });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: <?= json_encode(Lang::get('pago_pendiente.error_proceso')) ?>,
                    text:  result.message || <?= json_encode(Lang::get('pago_pendiente.error_pago')) ?>,
                    confirmButtonColor: '#764ba2',
                    background: '#1a1a2e', color: '#f5f5f5',
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: <?= json_encode(Lang::get('pago_pendiente.error_conn')) ?>,
                text:  <?= json_encode(Lang::get('pago_pendiente.no_conectar')) ?>,
                confirmButtonColor: '#764ba2',
                background: '#1a1a2e', color: '#f5f5f5',
            });
        }
    };

    renderPrecios();
});
</script>
</body>
</html>