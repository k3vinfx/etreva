<?php
$lang = $_SESSION['lang'] ?? 'es';
$p = function(string $key): string { return Lang::get('perfil.' . $key); };
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $p('titulo_pagina') ?> - Etreva Travel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/menu.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        :root{--bg-primary:#232a3b;--bg-card:#161c2d;--bg-input:#0f172a;--border:rgba(255,255,255,0.08);--border-focus:#6366f1;--text:#e2e8f0;--muted:#94a3b8;--accent:#6366f1;--accent2:#818cf8;--green:#22c55e;--red:#ef4444;--gold:#f59e0b;}
        html,body{background:var(--bg-primary)!important;color:var(--text)!important;min-height:100vh;font-family:'Segoe UI',system-ui,sans-serif;}
        main{margin-left:280px;min-height:100vh;background:var(--bg-primary)!important;transition:margin-left 0.3s;}
        main.min-main{margin-left:85px;}
        .perfil-hero{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);padding:40px 48px 0;position:relative;overflow:hidden;}
        .perfil-hero::before{content:'';position:absolute;top:-100px;right:-100px;width:400px;height:400px;background:radial-gradient(circle,rgba(99,102,241,0.1) 0%,transparent 70%);pointer-events:none;}
        .hero-inner{display:flex;align-items:flex-end;gap:32px;padding-bottom:0;flex-wrap:wrap;}
        .avatar-wrap{position:relative;flex-shrink:0;}
        .avatar-circle{width:110px;height:110px;border-radius:50%;border:4px solid var(--accent);display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:800;color:#fff;background:linear-gradient(135deg,var(--accent),#a78bfa);cursor:pointer;transition:all 0.2s;overflow:hidden;}
        .avatar-circle:hover{box-shadow:0 0 0 6px rgba(99,102,241,0.25);}
        .avatar-circle img{width:100%;height:100%;object-fit:cover;}
        .avatar-edit-btn{position:absolute;bottom:4px;right:4px;width:30px;height:30px;background:var(--accent);border:2px solid var(--bg-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.7rem;cursor:pointer;transition:all 0.2s;}
        .avatar-edit-btn:hover{background:#4f46e5;transform:scale(1.1);}
        #avatarFileInput{display:none;}
        .hero-user-info{flex:1;padding-bottom:28px;}
        .hero-user-info h1{font-size:2rem;font-weight:800;color:#fff;margin:0 0 4px;}
        .hero-user-info p{color:var(--muted);font-size:0.9rem;margin:0 0 16px;}
        .hero-badges{display:flex;gap:10px;flex-wrap:wrap;}
        .hero-badge{background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:50px;padding:5px 14px;font-size:0.78rem;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:6px;}
        .hero-badge i{color:var(--accent);}
        .perfil-tabs{display:flex;gap:0;padding:0 48px;border-bottom:1px solid var(--border);background:linear-gradient(135deg,#1a1a2e,#16213e);}
        .perfil-tab{padding:14px 22px;font-size:0.88rem;font-weight:600;color:var(--muted);cursor:pointer;border-bottom:3px solid transparent;transition:all 0.2s;display:flex;align-items:center;gap:8px;background:none;border-top:none;border-left:none;border-right:none;}
        .perfil-tab:hover{color:var(--text);}
        .perfil-tab.activo{color:var(--accent2);border-bottom-color:var(--accent);}
        .perfil-body{padding:36px 48px 60px;max-width:1000px;}
        .tab-panel{display:none;}
        .tab-panel.activo{display:block;}
        .pf-section{background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:28px 32px;margin-bottom:24px;}
        .pf-section-title{font-size:0.78rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--accent2);margin-bottom:24px;display:flex;align-items:center;gap:10px;}
        .pf-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;}
        .pf-row.full{grid-template-columns:1fr;}
        .pf-row.three{grid-template-columns:1fr 1fr 1fr;}
        @media(max-width:700px){.pf-row,.pf-row.three{grid-template-columns:1fr;}}
        .pf-field{display:flex;flex-direction:column;gap:8px;}
        .pf-label{font-size:0.78rem;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:var(--muted);}
        .pf-input{background:var(--bg-input);border:1.5px solid var(--border);border-radius:12px;padding:11px 16px;font-size:0.9rem;color:var(--text);transition:all 0.2s;width:100%;outline:none;}
        .pf-input:focus{border-color:var(--border-focus);box-shadow:0 0 0 3px rgba(99,102,241,0.15);}
        .pf-input::placeholder{color:rgba(148,163,184,0.5);}
        select.pf-input option{background:#1e293b;color:var(--text);}
        .pf-toggle-row{display:flex;align-items:center;justify-content:space-between;padding:14px 0;border-bottom:1px solid var(--border);}
        .pf-toggle-row:last-child{border-bottom:none;}
        .pf-toggle-info h5{font-size:0.9rem;font-weight:600;color:var(--text);margin:0 0 3px;}
        .pf-toggle-info p{font-size:0.78rem;color:var(--muted);margin:0;}
        .pf-switch{position:relative;width:48px;height:26px;flex-shrink:0;}
        .pf-switch input{display:none;}
        .pf-switch-slider{position:absolute;inset:0;background:rgba(255,255,255,0.1);border-radius:50px;cursor:pointer;transition:all 0.25s;}
        .pf-switch-slider::before{content:'';position:absolute;width:20px;height:20px;background:#fff;border-radius:50%;top:3px;left:3px;transition:all 0.25s;box-shadow:0 2px 4px rgba(0,0,0,0.3);}
        .pf-switch input:checked+.pf-switch-slider{background:var(--accent);}
        .pf-switch input:checked+.pf-switch-slider::before{transform:translateX(22px);}
        .chips-wrap{display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;}
        .pref-chip{background:rgba(255,255,255,0.05);border:1.5px solid var(--border);border-radius:50px;padding:7px 16px;font-size:0.82rem;font-weight:600;color:var(--muted);cursor:pointer;transition:all 0.2s;user-select:none;}
        .pref-chip:hover{border-color:var(--accent);color:var(--text);}
        .pref-chip.activo{background:rgba(99,102,241,0.15);border-color:var(--accent);color:var(--accent2);}
        .pref-chip.excluir.activo{background:rgba(239,68,68,0.12);border-color:var(--red);color:#fca5a5;}
        .pf-btn-primary{background:var(--accent);color:#fff;border:none;border-radius:12px;padding:12px 28px;font-size:0.88rem;font-weight:700;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:8px;}
        .pf-btn-primary:hover{background:#4f46e5;transform:translateY(-2px);}
        .pf-btn-primary:disabled{opacity:0.5;cursor:not-allowed;transform:none;}
        .pf-btn-secondary{background:rgba(255,255,255,0.06);color:var(--text);border:1px solid var(--border);border-radius:12px;padding:12px 24px;font-size:0.88rem;font-weight:600;cursor:pointer;transition:all 0.2s;}
        .pf-btn-secondary:hover{background:rgba(255,255,255,0.1);}
        .pf-btn-danger{background:rgba(239,68,68,0.1);color:#fca5a5;border:1px solid rgba(239,68,68,0.25);border-radius:12px;padding:12px 24px;font-size:0.88rem;font-weight:600;cursor:pointer;transition:all 0.2s;}
        .pf-btn-danger:hover{background:rgba(239,68,68,0.2);}
        .btn-row{display:flex;gap:12px;justify-content:flex-end;margin-top:8px;}
        .avatar-previews{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-top:16px;}
        .avatar-option{width:60px;height:60px;border-radius:50%;cursor:pointer;border:3px solid transparent;transition:all 0.2s;display:flex;align-items:center;justify-content:center;font-size:1.6rem;}
        .avatar-option:hover{transform:scale(1.1);border-color:var(--accent);}
        .avatar-option.activo{border-color:var(--accent);box-shadow:0 0 0 3px rgba(99,102,241,0.25);}
        .pwd-strength{margin-top:8px;}
        .pwd-bar{height:4px;background:rgba(255,255,255,0.08);border-radius:50px;overflow:hidden;margin-bottom:4px;}
        .pwd-bar-fill{height:100%;border-radius:50px;transition:all 0.3s;width:0;}
        .pwd-label{font-size:0.72rem;color:var(--muted);}
        @media(max-width:768px){main,main.min-main{margin-left:0;}.perfil-hero{padding:24px 20px 0;}.perfil-tabs{padding:0 20px;overflow-x:auto;}.perfil-body{padding:24px 20px 40px;}.pf-section{padding:20px;}}
    </style>
</head>

<?php include 'view/menu.php'; ?>

<body>
<main>

    <!-- HERO -->
    <div class="perfil-hero">
        <div class="hero-inner">
            <div class="avatar-wrap" onclick="document.getElementById('avatarFileInput').click()">
                <div class="avatar-circle" id="avatarPreview"><span id="avatarIniciales">?</span></div>
                <div class="avatar-edit-btn"><i class="fas fa-camera"></i></div>
                <input type="file" id="avatarFileInput" accept="image/*">
            </div>
            <div class="hero-user-info">
                <h1 id="heroNombre"><?= $p('cargando') ?></h1>
                <p id="heroEmail"></p>
                <div class="hero-badges" id="heroBadges">
                    <div class="hero-badge"><i class="fas fa-spinner fa-spin"></i> <?= $p('cargando_perfil') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABS -->
    <div class="perfil-tabs">
        <button class="perfil-tab activo" data-tab="datos"><i class="fas fa-user"></i> <?= $p('tab_datos') ?></button>
        <button class="perfil-tab" data-tab="preferencias"><i class="fas fa-sliders-h"></i> <?= $p('tab_preferencias') ?></button>
        <button class="perfil-tab" data-tab="apariencia"><i class="fas fa-palette"></i> <?= $p('tab_apariencia') ?></button>
        <button class="perfil-tab" data-tab="seguridad"><i class="fas fa-shield-alt"></i> <?= $p('tab_seguridad') ?></button>
    </div>

    <div class="perfil-body">

        <!-- TAB DATOS -->
        <div class="tab-panel activo" id="panel-datos">
            <div class="pf-section">
                <div class="pf-section-title"><i class="fas fa-id-card"></i> <?= $p('sec_info_basica') ?></div>
                <div class="pf-row">
                    <div class="pf-field"><label class="pf-label"><?= $p('label_nombre') ?></label><input type="text" class="pf-input" id="inputNombre" placeholder="<?= $p('ph_nombre') ?>"></div>
                    <div class="pf-field"><label class="pf-label"><?= $p('label_apellido') ?></label><input type="text" class="pf-input" id="inputApellido" placeholder="<?= $p('ph_apellido') ?>"></div>
                </div>
                <div class="pf-row">
                    <div class="pf-field"><label class="pf-label"><?= $p('label_email') ?></label><input type="email" class="pf-input" id="inputEmail"></div>
                    <div class="pf-field"><label class="pf-label"><?= $p('label_telefono') ?></label><input type="tel" class="pf-input" id="inputTelefono" placeholder="+591 7xxxxxxx"></div>
                </div>
                <div class="pf-row">
                    <div class="pf-field">
                        <label class="pf-label"><?= $p('label_pais') ?></label>
                        <select class="pf-input" id="inputPais"><option value=""><?= $p('sel_pais') ?></option></select>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label"><?= $p('label_idioma') ?></label>
                        <select class="pf-input" id="inputIdioma">
                            <option value="es">🇧🇴 Español</option>
                            <option value="en">🇺🇸 English</option>
                            <option value="pt">🇧🇷 Português</option>
                            <option value="fr">🇫🇷 Français</option>
                            <option value="de">🇩🇪 Deutsch</option>
                        </select>
                    </div>
                </div>
                <div class="pf-row full">
                    <div class="pf-field">
                        <label class="pf-label"><?= $p('label_nacimiento') ?></label>
                        <input type="date" class="pf-input" id="inputFechaNacimiento">
                        <div class="text-danger small mt-1" id="errFechaNacimiento"></div>
                        <small class="text-muted"><?= $p('hint_nacimiento') ?></small>
                    </div>
                </div>
                <div class="btn-row">
                    <button class="pf-btn-secondary" onclick="cargarDatosUsuario()"><i class="fas fa-undo"></i> <?= $p('btn_restablecer') ?></button>
                    <button class="pf-btn-primary" id="btnGuardarDatos"><i class="fas fa-save"></i> <?= $p('btn_guardar') ?></button>
                </div>
            </div>

            <div class="pf-section">
                <div class="pf-section-title"><i class="fas fa-image"></i> <?= $p('sec_foto') ?></div>
                <p style="color:var(--muted);font-size:0.85rem;margin-bottom:16px;"><?= $p('foto_desc') ?></p>
                <div class="avatar-previews" id="avatarOpciones"></div>
                <div class="btn-row" style="margin-top:20px;">
                    <button class="pf-btn-primary" id="btnGuardarAvatar"><i class="fas fa-check"></i> <?= $p('btn_guardar_avatar') ?></button>
                </div>
            </div>
        </div>

        <!-- TAB PREFERENCIAS -->
        <div class="tab-panel" id="panel-preferencias">
            <div class="pf-section">
                <div class="pf-section-title"><i class="fas fa-suitcase"></i> <?= $p('sec_prefs_generales') ?></div>
                <div class="pf-row three">
                    <div class="pf-field">
                        <label class="pf-label"><?= $p('label_motivo') ?></label>
                        <select class="pf-input" id="prefMotivo">
                            <option value=""><?= $p('sel_opcion') ?></option>
                            <option value="aventura"><?= $p('motivo_aventura') ?></option>
                            <option value="cultura"><?= $p('motivo_cultura') ?></option>
                            <option value="fiesta"><?= $p('motivo_fiesta') ?></option>
                            <option value="gastronomia"><?= $p('motivo_gastro') ?></option>
                            <option value="relax"><?= $p('motivo_relax') ?></option>
                            <option value="negocios"><?= $p('motivo_negocios') ?></option>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label"><?= $p('label_compania') ?></label>
                        <select class="pf-input" id="prefCompania">
                            <option value=""><?= $p('sel_opcion') ?></option>
                            <option value="solo"><?= $p('comp_solo') ?></option>
                            <option value="pareja"><?= $p('comp_pareja') ?></option>
                            <option value="familia"><?= $p('comp_familia') ?></option>
                            <option value="amigos"><?= $p('comp_amigos') ?></option>
                            <option value="grupo"><?= $p('comp_grupo') ?></option>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label"><?= $p('label_presupuesto') ?></label>
                        <select class="pf-input" id="prefPresupuesto">
                            <option value=""><?= $p('sel_opcion') ?></option>
                            <option value="bajo"><?= $p('pres_bajo') ?></option>
                            <option value="medio"><?= $p('pres_medio') ?></option>
                            <option value="alto"><?= $p('pres_alto') ?></option>
                            <option value="premium"><?= $p('pres_premium') ?></option>
                        </select>
                    </div>
                </div>
                <div class="pf-row">
                    <div class="pf-field">
                        <label class="pf-label"><?= $p('label_rango_edad') ?></label>
                        <div class="pf-input" style="background:rgba(255,255,255,0.03);display:flex;align-items:center;height:46px;"><span id="rangoEdadValor">—</span></div>
                        <small class="text-muted"><?= $p('hint_rango_edad') ?></small>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label"><?= $p('label_duracion') ?></label>
                        <select class="pf-input" id="prefDuracion">
                            <option value=""><?= $p('sel_opcion') ?></option>
                            <option value="1-3"><?= $p('dur_1') ?></option>
                            <option value="4-7"><?= $p('dur_2') ?></option>
                            <option value="8-14"><?= $p('dur_3') ?></option>
                            <option value="15+"><?= $p('dur_4') ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pf-section">
                <div class="pf-section-title"><i class="fas fa-hiking"></i> <?= $p('sec_actividades') ?> <span style="font-size:0.7rem;color:var(--muted);text-transform:none;letter-spacing:0;"><?= $p('sec_actividades_hint') ?></span></div>
                <div class="chips-wrap" id="chipsActividades"></div>
            </div>

            <div class="pf-section">
                <div class="pf-section-title"><i class="fas fa-star"></i> <?= $p('sec_experiencias') ?></div>
                <div class="chips-wrap" id="chipsExperiencias"></div>
            </div>

            <div class="pf-section" id="seccionVN">
                <div class="pf-section-title"><i class="fas fa-glass-cheers"></i> <?= $p('sec_vn') ?></div>
                <div class="pf-field" style="margin-bottom:16px;">
                    <label class="pf-label"><?= $p('vn_ambiente') ?></label>
                    <div class="chips-wrap" id="chipsAmbiente"></div>
                </div>
                <div class="pf-field" style="margin-bottom:16px;">
                    <label class="pf-label"><?= $p('vn_generos_fav') ?></label>
                    <div class="chips-wrap" id="chipsGenerosFav"></div>
                </div>
                <div class="pf-field">
                    <label class="pf-label" style="color:#fca5a5;"><?= $p('vn_generos_excl') ?></label>
                    <div class="chips-wrap" id="chipsGenerosExcluir"></div>
                </div>
            </div>

            <div class="pf-section" id="seccionGastro">
                <div class="pf-section-title"><i class="fas fa-utensils"></i> <?= $p('sec_gastro') ?></div>
                <div class="pf-field" style="margin-bottom:16px;">
                    <label class="pf-label"><?= $p('gastro_fav') ?></label>
                    <div class="chips-wrap" id="chipsGastroFav"></div>
                </div>
                <div class="pf-field">
                    <label class="pf-label" style="color:#fca5a5;"><?= $p('gastro_excl') ?></label>
                    <div class="chips-wrap" id="chipsGastroExcluir"></div>
                </div>
            </div>

            <div class="btn-row">
                <button class="pf-btn-secondary" onclick="cargarPreferencias()"><i class="fas fa-undo"></i> <?= $p('btn_restablecer') ?></button>
                <button class="pf-btn-primary" id="btnGuardarPrefs"><i class="fas fa-save"></i> <?= $p('btn_guardar_prefs') ?></button>
            </div>
        </div>

        <!-- TAB APARIENCIA -->
        <div class="tab-panel" id="panel-apariencia">
            <div class="pf-section">
                <div class="pf-section-title"><i class="fas fa-moon"></i> <?= $p('sec_tema') ?></div>
                <div class="pf-toggle-row"><div class="pf-toggle-info"><h5><?= $p('toggle_dark') ?></h5><p><?= $p('toggle_dark_desc') ?></p></div><label class="pf-switch"><input type="checkbox" id="toggleDarkMode"><span class="pf-switch-slider"></span></label></div>
                <div class="pf-toggle-row"><div class="pf-toggle-info"><h5><?= $p('toggle_notifs') ?></h5><p><?= $p('toggle_notifs_desc') ?></p></div><label class="pf-switch"><input type="checkbox" id="toggleNotifs" checked><span class="pf-switch-slider"></span></label></div>
                <div class="pf-toggle-row"><div class="pf-toggle-info"><h5><?= $p('toggle_anim') ?></h5><p><?= $p('toggle_anim_desc') ?></p></div><label class="pf-switch"><input type="checkbox" id="toggleAnimaciones" checked><span class="pf-switch-slider"></span></label></div>
                <div class="pf-toggle-row"><div class="pf-toggle-info"><h5><?= $p('toggle_menu') ?></h5><p><?= $p('toggle_menu_desc') ?></p></div><label class="pf-switch"><input type="checkbox" id="toggleMenuCompacto"><span class="pf-switch-slider"></span></label></div>
                <div class="pf-toggle-row"><div class="pf-toggle-info"><h5><?= $p('toggle_contacto') ?></h5><p><?= $p('toggle_contacto_desc') ?></p></div><label class="pf-switch"><input type="checkbox" id="toggleContacto"><span class="pf-switch-slider"></span></label></div>
            </div>
            <div class="pf-section">
                <div class="pf-section-title"><i class="fas fa-language"></i> <?= $p('sec_idioma') ?></div>
                <div class="pf-row">
                    <div class="pf-field">
                        <label class="pf-label"><?= $p('label_idioma') ?></label>
                        <select class="pf-input" id="aparienciaIdioma">
                            <option value="es">🇧🇴 Español</option>
                            <option value="en">🇺🇸 English</option>
                            <option value="pt">🇧🇷 Português</option>
                            <option value="fr">🇫🇷 Français</option>
                            <option value="de">🇩🇪 Deutsch</option>
                        </select>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label"><?= $p('label_fecha_fmt') ?></label>
                        <select class="pf-input" id="aparienciaFecha">
                            <option value="dd/mm/yyyy">DD/MM/YYYY</option>
                            <option value="mm/dd/yyyy">MM/DD/YYYY</option>
                            <option value="yyyy-mm-dd">YYYY-MM-DD</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="btn-row">
                <button class="pf-btn-primary" id="btnGuardarApariencia"><i class="fas fa-save"></i> <?= $p('btn_guardar_config') ?></button>
            </div>
        </div>

        <!-- TAB SEGURIDAD -->
        <div class="tab-panel" id="panel-seguridad">
            <div class="pf-section">
                <div class="pf-section-title"><i class="fas fa-lock"></i> <?= $p('sec_password') ?></div>
                <div class="pf-row full"><div class="pf-field"><label class="pf-label"><?= $p('label_pwd_actual') ?></label><input type="password" class="pf-input" id="pwdActual" placeholder="••••••••"></div></div>
                <div class="pf-row">
                    <div class="pf-field">
                        <label class="pf-label"><?= $p('label_pwd_nueva') ?></label>
                        <input type="password" class="pf-input" id="pwdNueva" placeholder="<?= $p('ph_pwd_nueva') ?>">
                        <div class="pwd-strength"><div class="pwd-bar"><div class="pwd-bar-fill" id="pwdBarFill"></div></div><span class="pwd-label" id="pwdLabel"><?= $p('pwd_ingresa') ?></span></div>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label"><?= $p('label_pwd_confirm') ?></label>
                        <input type="password" class="pf-input" id="pwdConfirm" placeholder="<?= $p('ph_pwd_confirm') ?>">
                        <span id="pwdMatchMsg" style="font-size:0.72rem;margin-top:4px;"></span>
                    </div>
                </div>
                <div class="btn-row"><button class="pf-btn-primary" id="btnCambiarPwd"><i class="fas fa-key"></i> <?= $p('btn_cambiar_pwd') ?></button></div>
            </div>

            <div class="pf-section">
                <div class="pf-section-title"><i class="fas fa-info-circle"></i> <?= $p('sec_cuenta_info') ?></div>
                <div class="pf-toggle-row">
                    <div class="pf-toggle-info"><h5><?= $p('info_estado') ?></h5><p id="infoEstado">—</p></div>
                    <span style="background:rgba(34,197,94,0.15);color:#86efac;padding:4px 14px;border-radius:50px;font-size:0.78rem;font-weight:700;" id="badgeEstado"><?= $p('badge_activo') ?></span>
                </div>
                <div class="pf-toggle-row">
                    <div class="pf-toggle-info"><h5><?= $p('info_miembro') ?></h5><p id="infoFechaRegistro">—</p></div>
                </div>
                <div class="pf-toggle-row" style="border-bottom:none;">
                    <div class="pf-toggle-info"><h5 style="color:#fca5a5;"><?= $p('info_eliminar') ?></h5><p><?= $p('info_eliminar_desc') ?></p></div>
                    <button class="pf-btn-danger" onclick="confirmarEliminarCuenta()"><i class="fas fa-trash-alt"></i> <?= $p('btn_eliminar') ?></button>
                </div>
            </div>
        </div>

    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="assets/js/etreva-auth.js"></script>
<script>
const PERFIL_T = <?= json_encode([
    'cargando'           => Lang::get('perfil.cargando'),
    'error_perfil'       => Lang::get('perfil.error_perfil'),
    'error_prefs'        => Lang::get('perfil.error_prefs'),
    'guardado_ok'        => Lang::get('perfil.guardado_ok'),
    'guardado_prefs'     => Lang::get('perfil.guardado_prefs'),
    'guardado_avatar'    => Lang::get('perfil.guardado_avatar'),
    'guardado_apariencia'=> Lang::get('perfil.guardado_apariencia'),
    'pwd_cambiada'       => Lang::get('perfil.pwd_cambiada'),
    'cuenta_eliminada'   => Lang::get('perfil.cuenta_eliminada'),
    'err_nombre_email'   => Lang::get('perfil.err_nombre_email'),
    'err_fecha'          => Lang::get('perfil.err_fecha'),
    'err_img_grande'     => Lang::get('perfil.err_img_grande'),
    'err_avatar_sel'     => Lang::get('perfil.err_avatar_sel'),
    'err_pwd_campos'     => Lang::get('perfil.err_pwd_campos'),
    'err_pwd_no_match'   => Lang::get('perfil.err_pwd_no_match'),
    'err_pwd_corta'      => Lang::get('perfil.err_pwd_corta'),
    'err_conexion'       => Lang::get('perfil.err_conexion'),
    'confirm_eliminar1'  => Lang::get('perfil.confirm_eliminar1'),
    'confirm_eliminar2'  => Lang::get('perfil.confirm_eliminar2'),
    'pwd_muy_debil'      => Lang::get('perfil.pwd_muy_debil'),
    'pwd_debil'          => Lang::get('perfil.pwd_debil'),
    'pwd_regular'        => Lang::get('perfil.pwd_regular'),
    'pwd_fuerte'         => Lang::get('perfil.pwd_fuerte'),
    'pwd_coinciden'      => Lang::get('perfil.pwd_coinciden'),
    'pwd_no_coinciden'   => Lang::get('perfil.pwd_no_coinciden'),
    'pwd_ingresa'        => Lang::get('perfil.pwd_ingresa'),
    'guardando'          => Lang::get('perfil.guardando'),
    'cambiando'          => Lang::get('perfil.cambiando'),
    'datos_prefs_ok'     => Lang::get('perfil.datos_prefs_ok'),
    'datos_ok_prefs_warn'=> Lang::get('perfil.datos_ok_prefs_warn'),
    'sel_pais'           => Lang::get('perfil.sel_pais'),
    'desde'              => Lang::get('perfil.desde'),
    'cuenta_verificada'  => Lang::get('perfil.cuenta_verificada'),
    'err_fecha_min'      => Lang::get('perfil.err_fecha_min'),
    'err_fecha_max'      => Lang::get('perfil.err_fecha_max'),
    'edad_lbl'           => Lang::get('perfil.edad_lbl'),
    'fecha_invalida'     => Lang::get('perfil.fecha_invalida'),
    'no_especificada'    => Lang::get('perfil.no_especificada'),
    'rango_menor18'      => Lang::get('perfil.rango_menor18'),
    'rango_18_25'        => Lang::get('perfil.rango_18_25'),
    'rango_26_35'        => Lang::get('perfil.rango_26_35'),
    'rango_36_45'        => Lang::get('perfil.rango_36_45'),
    'rango_46_60'        => Lang::get('perfil.rango_46_60'),
    'rango_60mas'        => Lang::get('perfil.rango_60mas'),
    'btn_guardar'        => Lang::get('perfil.btn_guardar'),
    'btn_guardar_avatar' => Lang::get('perfil.btn_guardar_avatar'),
    'btn_guardar_prefs'  => Lang::get('perfil.btn_guardar_prefs'),
    'btn_guardar_config' => Lang::get('perfil.btn_guardar_config'),
    'btn_cambiar_pwd'    => Lang::get('perfil.btn_cambiar_pwd'),
]) ?>;

const _PERFIL_LANG = '<?= $lang ?>';

const ACTIVIDADES_CAT=[
    {val:'trekking',     label:'🥾 <?= $p('act_trekking') ?>'},
    {val:'montanismo',   label:'🏔️ <?= $p('act_montanismo') ?>'},
    {val:'downhill',     label:'🚵 <?= $p('act_downhill') ?>'},
    {val:'vida_nocturna',label:'🌙 <?= $p('act_nocturna') ?>'},
    {val:'cultura',      label:'🏛️ <?= $p('act_cultura') ?>'},
    {val:'gastronomia',  label:'🍽️ <?= $p('act_gastro') ?>'},
    {val:'miradores',    label:'🔭 <?= $p('act_miradores') ?>'},
    {val:'fotografia',   label:'📷 <?= $p('act_foto') ?>'},
    {val:'relax',        label:'🧘 <?= $p('act_relax') ?>'},
];
const EXPERIENCIAS_CAT=[
    {val:'aventura_naturaleza', label:'🌿 <?= $p('exp_aventura') ?>'},
    {val:'cultura_tradiciones', label:'🎭 <?= $p('exp_cultura') ?>'},
    {val:'vida_nocturna_ocio',  label:'🥂 <?= $p('exp_nocturna') ?>'},
    {val:'gastronomia_sabores', label:'🍜 <?= $p('exp_gastro') ?>'},
    {val:'relax_bienestar',     label:'💆 <?= $p('exp_relax') ?>'},
    {val:'fotografia_paisajes', label:'🌄 <?= $p('exp_foto') ?>'},
];
const VN_AMBIENTES=[
    {val:'discoteca',    label:'🪩 <?= $p('vn_discoteca') ?>'},
    {val:'cocteleria',   label:'🍸 <?= $p('vn_cocteleria') ?>'},
    {val:'cerveceria',   label:'🍺 <?= $p('vn_cerveceria') ?>'},
    {val:'karaoke',      label:'🎤 <?= $p('vn_karaoke') ?>'},
    {val:'jazz_cultural',label:'🎷 <?= $p('vn_jazz') ?>'},
    {val:'bar_rock',     label:'🎸 <?= $p('vn_bar_rock') ?>'},
];
const VN_GENEROS=[
    {val:'electronica',label:'🎛️ <?= $p('gen_electronica') ?>'},
    {val:'reggaeton',  label:'🎵 <?= $p('gen_reggaeton') ?>'},
    {val:'salsa',      label:'💃 <?= $p('gen_salsa') ?>'},
    {val:'rock',       label:'🎸 <?= $p('gen_rock') ?>'},
    {val:'pop',        label:'🎤 <?= $p('gen_pop') ?>'},
    {val:'jazz',       label:'🎷 <?= $p('gen_jazz') ?>'},
    {val:'cumbia',     label:'🪗 <?= $p('gen_cumbia') ?>'},
    {val:'metal',      label:'🤘 <?= $p('gen_metal') ?>'},
];
const GASTRO_TIPOS=[
    {val:'criolla',    label:'🍲 <?= $p('gastro_criolla') ?>'},
    {val:'italiana',   label:'🍕 <?= $p('gastro_italiana') ?>'},
    {val:'japonesa',   label:'🍱 <?= $p('gastro_japonesa') ?>'},
    {val:'vegetariana',label:'🥗 <?= $p('gastro_vegetariana') ?>'},
    {val:'vegana',     label:'🌱 <?= $p('gastro_vegana') ?>'},
    {val:'parrilla',   label:'🥩 <?= $p('gastro_parrilla') ?>'},
    {val:'mariscos',   label:'🦞 <?= $p('gastro_mariscos') ?>'},
    {val:'cafe',       label:'☕ <?= $p('gastro_cafe') ?>'},
    {val:'china',      label:'🥡 <?= $p('gastro_china') ?>'},
    {val:'mexicana',   label:'🌮 <?= $p('gastro_mexicana') ?>'},
    {val:'fastfood',   label:'🍔 <?= $p('gastro_fastfood') ?>'},
];
const AVATARS_DEFAULT=[
    {bg:'linear-gradient(135deg,#6366f1,#818cf8)',emoji:'🧭'},
    {bg:'linear-gradient(135deg,#16a34a,#22c55e)',emoji:'🏔️'},
    {bg:'linear-gradient(135deg,#ea580c,#f97316)',emoji:'🌋'},
    {bg:'linear-gradient(135deg,#b91c7b,#ec4899)',emoji:'🌸'},
    {bg:'linear-gradient(135deg,#0e7490,#06b6d4)',emoji:'🌊'},
    {bg:'linear-gradient(135deg,#d97706,#f59e0b)',emoji:'🌟'},
    {bg:'linear-gradient(135deg,#7c3aed,#a78bfa)',emoji:'🎭'},
    {bg:'linear-gradient(135deg,#0369a1,#38bdf8)',emoji:'🗺️'},
    {bg:'linear-gradient(135deg,#166534,#4ade80)',emoji:'🌿'},
    {bg:'linear-gradient(135deg,#9333ea,#c084fc)',emoji:'🦋'},
    {bg:'linear-gradient(135deg,#dc2626,#f87171)',emoji:'🏕️'},
    {bg:'linear-gradient(135deg,#0891b2,#67e8f9)',emoji:'✈️'},
];

let estadoPerfil={usuario:{},configuracion:{},preferencias:{},intereses:{},avatarSeleccionado:null,avatarFile:null};

function calcularEdadYRango(f){
    if(!f)return{edad:null,rango:''};
    const hoy=new Date(),nac=new Date(f);
    let edad=hoy.getFullYear()-nac.getFullYear();
    const mes=hoy.getMonth()-nac.getMonth();
    if(mes<0||(mes===0&&hoy.getDate()<nac.getDate()))edad--;
    let rango='';
    if(edad<18)rango='menor_18';
    else if(edad<=25)rango='18_25';
    else if(edad<=35)rango='26_35';
    else if(edad<=45)rango='36_45';
    else if(edad<=60)rango='46_60';
    else rango='60+';
    return{edad,rango};
}
function formatearRango(r){
    const m={'menor_18':PERFIL_T.rango_menor18,'18_25':PERFIL_T.rango_18_25,'26_35':PERFIL_T.rango_26_35,'36_45':PERFIL_T.rango_36_45,'46_60':PERFIL_T.rango_46_60,'60+':PERFIL_T.rango_60mas};
    return m[r]||r;
}
function convertirDuracionAId(v){switch(v){case'1-3':return 3;case'4-7':return 7;case'8-14':return 14;case'15+':return 15;default:return 0;}}
function duracionIdToSelect(v){if(v<=3)return'1-3';if(v<=7)return'4-7';if(v<=14)return'8-14';if(v>=15)return'15+';return'';}

document.querySelectorAll('.perfil-tab').forEach(btn=>{
    btn.addEventListener('click',()=>{
        document.querySelectorAll('.perfil-tab').forEach(b=>b.classList.remove('activo'));
        document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('activo'));
        btn.classList.add('activo');
        document.getElementById('panel-'+btn.dataset.tab).classList.add('activo');
    });
});

function renderChips(cid,catalog,sel,excluir=false){
    const w=document.getElementById(cid);if(!w)return;
    w.innerHTML=catalog.map(item=>`<div class="pref-chip ${sel.includes(item.val)?'activo':''} ${excluir?'excluir':''}" data-val="${item.val}" onclick="toggleChip(this,${excluir})">${item.label}</div>`).join('');
}
function toggleChip(el,ex){el.classList.toggle('activo');if(ex)el.classList.toggle('excluir',true);}
function getSelectedChips(cid){return[...document.querySelectorAll(`#${cid} .pref-chip.activo`)].map(c=>c.dataset.val);}

function renderAvatarOpciones(cur){
    const w=document.getElementById('avatarOpciones');if(!w)return;
    w.innerHTML=AVATARS_DEFAULT.map((av,i)=>`<div class="avatar-option ${cur==='preset_'+i?'activo':''}" style="background:${av.bg};" onclick="seleccionarAvatarPreset(${i},this)">${av.emoji}</div>`).join('');
}
function seleccionarAvatarPreset(idx,el){
    document.querySelectorAll('.avatar-option').forEach(a=>a.classList.remove('activo'));
    el.classList.add('activo');
    estadoPerfil.avatarSeleccionado='preset_'+idx;
    estadoPerfil.avatarFile=null;
    actualizarAvatarHero(AVATARS_DEFAULT[idx],null);
    guardarAvatar();   // se guarda al instante: no hace falta otro botón
}

// Guarda el avatar elegido (foto subida o predefinido) y lo refleja en el menú lateral
function guardarAvatar(){
    const ok=r=>{
        if(!r.success){toastr.error(r.message||PERFIL_T.err_conexion);return;}
        toastr.success(PERFIL_T.guardado_avatar);
        estadoPerfil.configuracion.avatar=r.avatar||estadoPerfil.avatarSeleccionado;
        estadoPerfil.avatarFile=null;
        refrescarAvatarMenu();
    };
    if(estadoPerfil.avatarFile){
        const fd=new FormData();fd.append('avatar',estadoPerfil.avatarFile);
        return $.ajax({url:'index.php?c=perfil&a=SubirAvatar',type:'POST',data:fd,processData:false,contentType:false,dataType:'json',
            success:ok,error:()=>toastr.error(PERFIL_T.err_conexion)});
    }
    if(estadoPerfil.avatarSeleccionado){
        return $.ajax({url:'index.php?c=perfil&a=ActualizarAvatar',type:'POST',contentType:'application/json',
            data:JSON.stringify({avatar:estadoPerfil.avatarSeleccionado}),dataType:'json',
            success:r=>{if(r.success)r.avatar=estadoPerfil.avatarSeleccionado;ok(r);},error:()=>toastr.error(PERFIL_T.err_conexion)});
    }
    toastr.info(PERFIL_T.err_avatar_sel);
    return $.Deferred().resolve();
}

// Copia el avatar del encabezado del perfil al menú lateral
function refrescarAvatarMenu(){
    const menu=document.querySelector('.usuario-avatar');
    const hero=document.getElementById('avatarPreview');
    if(!menu||!hero)return;
    const span=document.createElement('span');
    span.className='etv-avatar';
    span.style.cssText='width:45px;height:45px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;overflow:hidden;color:#fff;font-size:22px;background:'+(hero.style.background||'transparent');
    span.innerHTML=hero.innerHTML.replace(/font-size:[^;"]*;?/g,'');
    menu.innerHTML='';menu.appendChild(span);
}
function actualizarAvatarHero(p,src){
    const av=document.getElementById('avatarPreview');
    if(src){av.innerHTML=`<img src="${src}" alt="avatar">`;av.style.background='';}
    else if(p){av.style.background=p.bg;av.innerHTML=`<span style="font-size:2.8rem;">${p.emoji}</span>`;}
}

document.getElementById('avatarFileInput').addEventListener('change',function(){
    const f=this.files[0];if(!f)return;
    if(f.size>3*1024*1024){toastr.warning(PERFIL_T.err_img_grande);return;}
    estadoPerfil.avatarFile=f;estadoPerfil.avatarSeleccionado=null;
    const r=new FileReader();
    r.onload=e=>{actualizarAvatarHero(null,e.target.result);document.querySelectorAll('.avatar-option').forEach(a=>a.classList.remove('activo'));guardarAvatar();};
    r.readAsDataURL(f);
    this.value='';   // permite volver a elegir el mismo archivo
});

function cargarDatosUsuario(){
    // Primero la configuración (avatar, idioma, apariencia); después los datos que la usan
    $.ajax({url:'index.php?c=perfil&a=ObtenerPerfil',type:'GET',dataType:'json',
        success:r=>{if(r.success&&r.configuracion)estadoPerfil.configuracion=r.configuracion;},
        complete:cargarDatosBasicos
    });
}
function cargarDatosBasicos(){
    $.ajax({url:'index.php?c=login&a=VerPreferencias',type:'GET',dataType:'json',
        success:function(resp){
            if(!resp.success){toastr.error(PERFIL_T.error_perfil);return;}
            const u=resp.usuario||{},pref=resp.preferencias||{},cfg=estadoPerfil.configuracion||{};
            estadoPerfil.usuario=u;
            const nombre=(u.nombre||'')+' '+(u.apellido||'');
            const iniciales=((u.nombre?.[0]||'')+(u.apellido?.[0]||'')).toUpperCase()||'?';
            document.getElementById('heroNombre').textContent=nombre.trim()||'—';
            document.getElementById('heroEmail').textContent=u.email||'';
            document.getElementById('avatarIniciales').textContent=iniciales;
            const badges=[];
            if(u.nacionalidad)badges.push(`<div class="hero-badge"><i class="fas fa-flag"></i>${u.nacionalidad}</div>`);
            const ic=cfg.idioma||pref.idioma_preferido||null;
            if(ic)badges.push(`<div class="hero-badge"><i class="fas fa-language"></i>${ic.toUpperCase()}</div>`);
            if(u.fecha_registro){
                const f=new Date(u.fecha_registro).toLocaleDateString(_PERFIL_LANG,{year:'numeric',month:'long'});
                badges.push(`<div class="hero-badge"><i class="fas fa-calendar"></i>${PERFIL_T.desde} ${f}</div>`);
            }
            document.getElementById('heroBadges').innerHTML=badges.join('')||'';
            $('#inputNombre').val(u.nombre||'');$('#inputApellido').val(u.apellido||'');
            $('#inputEmail').val(u.email||'');$('#inputTelefono').val(u.telefono||'');
            cargarPaisesSelectPerfil(u.nacionalidad||cfg.pais||'Bolivia');
            $('#inputIdioma').val(cfg.idioma||pref.idioma_preferido||'es');
            if(u.fecha_nacimiento){$('#inputFechaNacimiento').val(u.fecha_nacimiento);actualizarRangoEdadDesdeFecha(u.fecha_nacimiento);}
            else{$('#inputFechaNacimiento').val('');$('#rangoEdadValor').text(PERFIL_T.no_especificada);}
            const hoy=new Date();
            $('#inputFechaNacimiento').attr('max',new Date(hoy.getFullYear()-10,hoy.getMonth(),hoy.getDate()).toISOString().split('T')[0])
                                     .attr('min',new Date(hoy.getFullYear()-100,hoy.getMonth(),hoy.getDate()).toISOString().split('T')[0]);
            const av=cfg.avatar||'';
            if(av.startsWith('preset_')){const i=parseInt(av.replace('preset_',''));if(AVATARS_DEFAULT[i])actualizarAvatarHero(AVATARS_DEFAULT[i],null);}
            else if(av&&(av.startsWith('http')||av.startsWith('assets/'))){actualizarAvatarHero(null,av);}
            else{const ae=document.getElementById('avatarPreview');ae.style.background='linear-gradient(135deg,#6366f1,#a78bfa)';ae.innerHTML=`<span style="font-size:2rem;font-weight:800;">${iniciales}</span>`;}
            renderAvatarOpciones(av);estadoPerfil.avatarSeleccionado=av;
            $('#infoEstado').text(PERFIL_T.cuenta_verificada);
            if(u.fecha_registro)$('#infoFechaRegistro').text(new Date(u.fecha_registro).toLocaleDateString(_PERFIL_LANG,{day:'2-digit',month:'long',year:'numeric'}));
            $('#toggleDarkMode').prop('checked',!document.documentElement.classList.contains('tema-claro'));
            $('#toggleNotifs').prop('checked',cfg.notificaciones!==false);
            $('#toggleAnimaciones').prop('checked',cfg.animaciones!==false);
            $('#toggleMenuCompacto').prop('checked',cfg.menu_compacto===true);
            $('#toggleContacto').prop('checked',cfg.contacto_publico===true);
            $('#aparienciaIdioma').val(cfg.idioma||pref.idioma_preferido||'es');
            $('#aparienciaFecha').val(cfg.formato_fecha||'dd/mm/yyyy');
        },
        error:()=>toastr.error(PERFIL_T.error_perfil)
    });
}

function cargarPreferencias(){
    $.ajax({url:'index.php?c=login&a=VerPreferencias',type:'GET',dataType:'json',
        success:function(resp){
            if(!resp.success)return;
            const tb=resp.preferencias_registro_tb||{};
            const int=(tb.intereses&&typeof tb.intereses==='object')?tb.intereses:{};
            const pj=resp.preferencias||{};
            estadoPerfil.preferencias=tb;estadoPerfil.intereses=int;
            $('#prefMotivo').val(tb.motivo_viaje||int.motivo_viaje||pj.motivo_viaje||'');
            $('#prefCompania').val(int.compania||'');
            $('#prefPresupuesto').val(tb.presupuesto_max||pj.presupuesto_max||'');
            $('#prefDuracion').val(duracionIdToSelect(tb.duracion_dias||pj.duracion_dias||0));
            renderChips('chipsActividades', ACTIVIDADES_CAT, int.actividades||[]);
            renderChips('chipsExperiencias',EXPERIENCIAS_CAT,int.experiencias||[]);
            const vn=int.preferencias_vida_nocturna||{};
            renderChips('chipsAmbiente',       VN_AMBIENTES,vn.ambiente||[]);
            renderChips('chipsGenerosFav',     VN_GENEROS,  vn.generos_favoritos||[]);
            renderChips('chipsGenerosExcluir', VN_GENEROS,  vn.generos_excluidos||[],true);
            const gs=int.preferencias_gastronomicas||{};
            renderChips('chipsGastroFav',     GASTRO_TIPOS,gs.tipos_favoritos||[]);
            renderChips('chipsGastroExcluir', GASTRO_TIPOS,gs.tipos_excluidos||[],true);
        },
        error:()=>toastr.error(PERFIL_T.error_prefs)
    });
}

$('#btnGuardarDatos').on('click',function(){
    const btn=$(this).prop('disabled',true).html(`<i class="fas fa-spinner fa-spin"></i> ${PERFIL_T.guardando}`);
    const fecha=$('#inputFechaNacimiento').val();
    const id=$('#inputIdioma').val();
    sessionStorage.setItem('etreva_lang',id);
    const datos={nombre:$('#inputNombre').val().trim(),apellido:$('#inputApellido').val().trim(),email:$('#inputEmail').val().trim(),telefono:$('#inputTelefono').val().trim(),pais:$('#inputPais').val().trim(),idioma_preferido:id,fecha_nacimiento:fecha};
    if(!datos.nombre||!datos.email){toastr.warning(PERFIL_T.err_nombre_email);btn.prop('disabled',false).html(`<i class="fas fa-save"></i> ${PERFIL_T.btn_guardar}`);return;}
    if(!validarFechaNacimiento()){toastr.warning(PERFIL_T.err_fecha);btn.prop('disabled',false).html(`<i class="fas fa-save"></i> ${PERFIL_T.btn_guardar}`);return;}
    $.ajax({url:'index.php?c=perfil&a=ActualizarDatos',type:'POST',contentType:'application/json',data:JSON.stringify(datos),dataType:'json',
        success:function(resp){
            if(!resp.success){toastr.error(resp.message||PERFIL_T.error_perfil);return;}
            const{rango}=calcularEdadYRango(fecha);
            $.ajax({url:'index.php?c=perfil&a=ActualizarPreferencias',type:'POST',contentType:'application/json',
                data:JSON.stringify({actualizar_solo_rango:true,rango_edad:rango}),dataType:'json',
                success:r=>toastr.success(r.success?PERFIL_T.datos_prefs_ok:PERFIL_T.datos_ok_prefs_warn),
                error:()=>toastr.warning(PERFIL_T.datos_ok_prefs_warn)
            });
            cargarDatosUsuario();
        },
        error:()=>toastr.error(PERFIL_T.err_conexion),
        complete:()=>btn.prop('disabled',false).html(`<i class="fas fa-save"></i> ${PERFIL_T.btn_guardar}`)
    });
});

$('#btnGuardarAvatar').on('click',function(){
    const btn=$(this).prop('disabled',true).html(`<i class="fas fa-spinner fa-spin"></i> ${PERFIL_T.guardando}`);
    $.when(guardarAvatar()).always(()=>btn.prop('disabled',false).html(`<i class="fas fa-check"></i> ${PERFIL_T.btn_guardar_avatar}`));
});

$('#btnGuardarPrefs').on('click',function(){
    const btn=$(this).prop('disabled',true).html(`<i class="fas fa-spinner fa-spin"></i> ${PERFIL_T.guardando}`);
    const dv=$('#prefDuracion').val();
    $.ajax({url:'index.php?c=perfil&a=ActualizarPreferencias',type:'POST',contentType:'application/json',
        data:JSON.stringify({motivo_viaje:$('#prefMotivo').val(),compania:$('#prefCompania').val(),presupuesto_max:$('#prefPresupuesto').val(),duracion_dias:dv?convertirDuracionAId(dv):null,
            intereses:{compania:$('#prefCompania').val(),rango_edad:$('#inputFechaNacimiento').val()?calcularEdadYRango($('#inputFechaNacimiento').val()).rango:'',
                actividades:getSelectedChips('chipsActividades'),experiencias:getSelectedChips('chipsExperiencias'),
                preferencias_vida_nocturna:{ambiente:getSelectedChips('chipsAmbiente'),generos_favoritos:getSelectedChips('chipsGenerosFav'),generos_excluidos:getSelectedChips('chipsGenerosExcluir')},
                preferencias_gastronomicas:{tipos_favoritos:getSelectedChips('chipsGastroFav'),tipos_excluidos:getSelectedChips('chipsGastroExcluir')}}}),
        dataType:'json',
        success:r=>r.success?toastr.success(PERFIL_T.guardado_prefs):toastr.error(r.message||PERFIL_T.err_conexion),
        error:()=>toastr.error(PERFIL_T.err_conexion),
        complete:()=>btn.prop('disabled',false).html(`<i class="fas fa-save"></i> ${PERFIL_T.btn_guardar_prefs}`)
    });
});

$('#btnGuardarApariencia').on('click',function(){
    const btn=$(this).prop('disabled',true).html(`<i class="fas fa-spinner fa-spin"></i> ${PERFIL_T.guardando}`);
    const dm=$('#toggleDarkMode').is(':checked');
    document.body.classList.toggle('dark-mode',dm);
    $.ajax({url:'index.php?c=perfil&a=ActualizarApariencia',type:'POST',contentType:'application/json',
        data:JSON.stringify({dark_mode:dm,notificaciones:$('#toggleNotifs').is(':checked'),animaciones:$('#toggleAnimaciones').is(':checked'),menu_compacto:$('#toggleMenuCompacto').is(':checked'),contacto_publico:$('#toggleContacto').is(':checked'),idioma:$('#aparienciaIdioma').val(),formato_fecha:$('#aparienciaFecha').val()}),
        dataType:'json',
        success:r=>r.success?toastr.success(PERFIL_T.guardado_apariencia):toastr.error(r.message||PERFIL_T.err_conexion),
        error:()=>toastr.error(PERFIL_T.err_conexion),
        complete:()=>btn.prop('disabled',false).html(`<i class="fas fa-save"></i> ${PERFIL_T.btn_guardar_config}`)
    });
});

$('#toggleDarkMode').on('change',function(){if(typeof cambiarTema==='function')cambiarTema(this.checked?'oscuro':'claro');});

$('#pwdNueva').on('input',function(){
    const v=$(this).val();let s=0;
    if(v.length>=8)s+=25;if(/[A-Z]/.test(v))s+=25;if(/[0-9]/.test(v))s+=25;if(/[^A-Za-z0-9]/.test(v))s+=25;
    let l='',c='';
    if(s<=25){l=PERFIL_T.pwd_muy_debil;c='#ef4444';}else if(s<=50){l=PERFIL_T.pwd_debil;c='#f97316';}else if(s<=75){l=PERFIL_T.pwd_regular;c='#f59e0b';}else{l=PERFIL_T.pwd_fuerte;c='#22c55e';}
    $('#pwdBarFill').css({width:s+'%',background:c});$('#pwdLabel').css('color',c).text(l);
});

$('#pwdConfirm').on('input',function(){
    const m=$(this).val()===$('#pwdNueva').val();
    $('#pwdMatchMsg').text(m?'✅ '+PERFIL_T.pwd_coinciden:'❌ '+PERFIL_T.pwd_no_coinciden).css('color',m?'#86efac':'#fca5a5');
});

$('#btnCambiarPwd').on('click',function(){
    const a=$('#pwdActual').val(),n=$('#pwdNueva').val(),c=$('#pwdConfirm').val();
    if(!a||!n||!c){toastr.warning(PERFIL_T.err_pwd_campos);return;}
    if(n!==c){toastr.warning(PERFIL_T.err_pwd_no_match);return;}
    if(n.length<8){toastr.warning(PERFIL_T.err_pwd_corta);return;}
    const btn=$(this).prop('disabled',true).html(`<i class="fas fa-spinner fa-spin"></i> ${PERFIL_T.cambiando}`);
    $.ajax({url:'index.php?c=perfil&a=CambiarPassword',type:'POST',contentType:'application/json',
        data:JSON.stringify({password_actual:a,password_nueva:n}),dataType:'json',
        success:function(r){
            if(r.success){toastr.success(PERFIL_T.pwd_cambiada);$('#pwdActual,#pwdNueva,#pwdConfirm').val('');$('#pwdBarFill').css('width','0');$('#pwdLabel').text(PERFIL_T.pwd_ingresa).css('color','');$('#pwdMatchMsg').text('');}
            else toastr.error(r.message||PERFIL_T.err_conexion);
        },
        error:()=>toastr.error(PERFIL_T.err_conexion),
        complete:()=>btn.prop('disabled',false).html(`<i class="fas fa-key"></i> ${PERFIL_T.btn_cambiar_pwd}`)
    });
});

function confirmarEliminarCuenta(){
    if(!confirm(PERFIL_T.confirm_eliminar1))return;
    if(!confirm(PERFIL_T.confirm_eliminar2))return;
    $.ajax({url:'index.php?c=perfil&a=EliminarCuenta',type:'POST',dataType:'json',
        success:r=>{if(r.success){toastr.success(PERFIL_T.cuenta_eliminada);setTimeout(()=>{window.location.href='index.php?c=login&a=Index';},2000);}else toastr.error(r.message||PERFIL_T.err_conexion);}
    });
}

function validarFechaNacimiento(){
    const input=$('#inputFechaNacimiento'),fecha=input.val(),err=$('#errFechaNacimiento');
    if(!fecha){err.text(PERFIL_T.err_fecha);input.addClass('is-invalid').removeClass('is-valid');return false;}
    const hoy=new Date(),nac=new Date(fecha);
    let edad=hoy.getFullYear()-nac.getFullYear();
    const mes=hoy.getMonth()-nac.getMonth();
    if(mes<0||(mes===0&&hoy.getDate()<nac.getDate()))edad--;
    if(edad<10){err.text(PERFIL_T.err_fecha_min.replace('%d',edad));input.addClass('is-invalid').removeClass('is-valid');return false;}
    if(edad>100){err.text(PERFIL_T.err_fecha_max);input.addClass('is-invalid').removeClass('is-valid');return false;}
    err.text('');input.removeClass('is-invalid').addClass('is-valid');return true;
}

function actualizarRangoEdadDesdeFecha(f){
    if(!f){$('#rangoEdadValor').text('—');return;}
    const{edad,rango}=calcularEdadYRango(f);
    $('#rangoEdadValor').text(`${edad} ${PERFIL_T.edad_lbl} (${formatearRango(rango)})`);
}

function cargarPaisesSelectPerfil(ps){
    if(window._paisesData){llenarSelectPerfil(window._paisesData,ps);return;}
    $.getJSON('assets/js/paises.json')
        .done(d=>{window._paisesData=d;llenarSelectPerfil(d,ps);})
        .fail(()=>{
            const bk=[{nombre:'Bolivia',nombre_es:'Bolivia'},{nombre:'Argentina',nombre_es:'Argentina'},{nombre:'Brazil',nombre_es:'Brasil'},{nombre:'Chile',nombre_es:'Chile'},{nombre:'Colombia',nombre_es:'Colombia'},{nombre:'Peru',nombre_es:'Perú'},{nombre:'Mexico',nombre_es:'México'},{nombre:'United States',nombre_es:'Estados Unidos'},{nombre:'Spain',nombre_es:'España'}];
            llenarSelectPerfil(bk,ps);
        });
}

function llenarSelectPerfil(data,ps){
    // es/en desde el JSON; pt/fr/de con los nombres de país del navegador (Intl)
    let regiones=null;
    try{if(!['es','en'].includes(_PERFIL_LANG))regiones=new Intl.DisplayNames([_PERFIL_LANG],{type:'region'});}catch(e){}
    const nombrePais=p=>{
        if(_PERFIL_LANG==='es')return p.nombre_es||p.nombre;
        if(regiones&&p.codigo){try{return regiones.of(p.codigo)||p.nombre;}catch(e){}}
        return p.nombre;
    };
    const $s=$('#inputPais');
    $s.empty().append(`<option value="">${PERFIL_T.sel_pais}</option>`);
    const sorted=[...data].sort((a,b)=>nombrePais(a).localeCompare(nombrePais(b),_PERFIL_LANG));
    sorted.forEach(p=>{
        const label=nombrePais(p);
        const value=p.nombre_es||p.nombre;
        const sel=(value===ps||p.nombre===ps||p.nombre_es===ps)?'selected':'';
        $s.append(`<option value="${value}" ${sel}>${label}</option>`);
    });
    if(ps&&$s.val()!==ps)$s.append(`<option value="${ps}" selected>${ps}</option>`);
}

$('#inputFechaNacimiento').on('input change',function(){
    validarFechaNacimiento()?actualizarRangoEdadDesdeFecha($(this).val()):$('#rangoEdadValor').text(PERFIL_T.fecha_invalida);
});

$('#aparienciaIdioma,#inputIdioma').on('change',function(){
    sessionStorage.setItem('etreva_lang',$(this).val());
    if(window._paisesData)llenarSelectPerfil(window._paisesData,$('#inputPais').val());
});

$(document).ready(function(){
    toastr.options={closeButton:true,progressBar:true,positionClass:'toast-top-right',timeOut:'3500'};
    EtrevaAuth.verificarSesionInicial(function(a){
        if(a){cargarDatosUsuario();cargarPreferencias();}
    });
    renderChips('chipsActividades',  ACTIVIDADES_CAT,[]);
    renderChips('chipsExperiencias', EXPERIENCIAS_CAT,[]);
    renderChips('chipsAmbiente',     VN_AMBIENTES,[]);
    renderChips('chipsGenerosFav',   VN_GENEROS,[]);
    renderChips('chipsGenerosExcluir',VN_GENEROS,[],true);
    renderChips('chipsGastroFav',    GASTRO_TIPOS,[]);
    renderChips('chipsGastroExcluir',GASTRO_TIPOS,[],true);
    renderAvatarOpciones('');
});
</script>
</body>
</html>
