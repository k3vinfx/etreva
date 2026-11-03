<?php
$lang = $_SESSION['lang'] ?? 'es';
$t = function(string $key): string {
    return Lang::get('modal_intereses.' . $key);
};
?>
<style>
#modalPreferencias .modal-content {
    background: #1a1a2e;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 20px;
    color: #e2e8f0;
}
#modalPreferencias .modal-header {
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    border-radius: 18px 18px 0 0;
    border: none;
    padding: 20px 24px;
}
#modalPreferencias .modal-header .modal-title { font-weight: 800; font-size: 1.1rem; color: #fff; }
#modalPreferencias .modal-body  { padding: 28px 24px; background: #1a1a2e; }
#modalPreferencias .modal-footer {
    border-top: 1px solid rgba(255,255,255,0.08);
    padding: 16px 24px; gap: 10px;
    background: #1a1a2e;
    border-radius: 0 0 20px 20px;
}
#modalPreferencias .progress { background: rgba(255,255,255,0.1); border-radius: 10px; height: 6px !important; }
#modalPreferencias .progress-bar { background: #10b981; border-radius: 10px; }
#modalPreferencias .step h5 { color: #fff; font-weight: 700; margin-bottom: 20px; font-size: 1.2rem; }
#modalPreferencias .step h5 i { color: #6366f1; margin-right: 8px; }
#modalPreferencias .form-label {
    color: rgba(255,255,255,0.7); font-weight: 600; font-size: 0.85rem;
    text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px;
}
#modalPreferencias .form-control,
#modalPreferencias .form-select,
#modalPreferencias select.form-control {
    background: rgba(40,45,73,0.9) !important;
    color: #e5f0ff !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    padding: 10px 14px; font-size: 0.9rem;
    transition: border-color 0.2s, background 0.2s;
}
#modalPreferencias .form-control:focus,
#modalPreferencias .form-select:focus {
    outline: none;
    border-color: rgba(99,102,241,0.5);
    background: rgba(99,102,241,0.07);
    box-shadow: none;
}
#modalPreferencias .form-control::placeholder { color: rgba(255,255,255,0.3); }
#modalPreferencias .form-check-input {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 4px;
}
#modalPreferencias .form-check-input:checked { background-color: #6366f1; border-color: #6366f1; }
#modalPreferencias .form-check-label { color: #e2e8f0; font-size: 0.9rem; }
#modalPreferencias .btn {
    border-radius: 12px; padding: 10px 20px;
    font-weight: 600; font-size: 0.88rem; transition: all 0.2s; border: none;
}
#modalPreferencias .btn-primary { background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; }
#modalPreferencias .btn-primary:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,0.4); }
#modalPreferencias .btn-secondary { background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.8); border: 1px solid rgba(255,255,255,0.1); }
#modalPreferencias .btn-secondary:hover { background: rgba(255,255,255,0.12); }
#modalPreferencias .btn-success { background: linear-gradient(135deg, #10b981, #059669); color: #fff; }
#modalPreferencias .btn-success:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16,185,129,0.3); }
#modalPreferencias .btn:disabled { opacity: 0.5; cursor: not-allowed; }
#modalPreferencias .experience-card-multi {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px; cursor: pointer; transition: all 0.2s; height: 100%;
}
#modalPreferencias .experience-card-multi:hover { transform: translateY(-3px); border-color: rgba(99,102,241,0.4); background: rgba(99,102,241,0.07); }
#modalPreferencias .experience-card-multi.border-primary { border: 2px solid #6366f1 !important; background: rgba(99,102,241,0.1); }
#modalPreferencias .experience-card-multi .card-body { padding: 1rem; }
#modalPreferencias .experience-card-multi i { font-size: 2.2rem; color: #6366f1; }
#modalPreferencias .experience-card-multi h6 { color: #fff; font-weight: 700; margin: 10px 0 5px; font-size: 0.9rem; }
#modalPreferencias .experience-card-multi small { color: rgba(255,255,255,0.5); font-size: 0.75rem; }
#modalPreferencias #contador_experiencias { color: #6366f1; font-weight: 700; }
#modalPreferencias .motivo-btn {
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
    color: #e2e8f0; border-radius: 12px; padding: 12px;
    font-weight: 600; width: 100%; text-align: left; transition: all 0.2s;
}
#modalPreferencias .motivo-btn i { margin-right: 8px; color: #6366f1; }
#modalPreferencias .motivo-btn:hover { border-color: rgba(99,102,241,0.4); background: rgba(99,102,241,0.07); transform: translateY(-2px); }
#modalPreferencias .motivo-btn.btn-primary { background: rgba(99,102,241,0.2); border-color: #6366f1; color: #a5b4fc; }
#modalPreferencias .tag {
    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 50px; padding: 8px 18px; font-size: 0.85rem;
    color: rgba(255,255,255,0.7); cursor: pointer; transition: all 0.2s;
    display: inline-block; margin: 4px;
}
#modalPreferencias .tag i { margin-right: 6px; color: #6366f1; }
#modalPreferencias .tag:hover { border-color: rgba(99,102,241,0.4); color: #a5b4fc; background: rgba(99,102,241,0.07); }
#modalPreferencias .tag.btn-primary { background: rgba(99,102,241,0.2); border-color: #6366f1; color: #a5b4fc; font-weight: 700; }
#modalPreferencias .row .form-check { margin-bottom: 8px; }
#modalPreferencias .fw-bold { color: rgba(255,255,255,0.9); margin-top: 16px; margin-bottom: 8px; display: block; font-size: 0.9rem; }
#modalPreferencias #vn-intensidad,
#modalPreferencias #gastro-precio { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #e2e8f0; border-radius: 12px; padding: 10px; }
#modalPreferencias .text-danger { color: #f87171 !important; font-size: 0.75rem; margin-top: 4px; }
#modalPreferencias .step { animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.bg-light { --bs-bg-opacity: 1; background-color: rgb(62 76 89) !important; }
.tag, .experience-card { cursor: pointer; transition: 0.3s; }
.tag:hover, .experience-card:hover { transform: translateY(-2px); box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
.experience-card.border-primary { border-width: 2px; }
.step { animation: fadeIn 0.5s; }
</style>

<div class="modal show" id="modalPreferencias" tabindex="-1" role="dialog"
     style="display: block; background: rgba(0,0,0,0.3); overflow-y: auto;">
    <div class="modal-dialog modal-lg" role="document" style="z-index: 10001;">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalPreferenciasLabel">
                    <i class="fas fa-mountain"></i> <?= $t('titulo') ?>
                </h5>
            </div>

            <div class="modal-body">
                <form id="formPreferencias">
                    <div class="progress mb-4" style="height: 5px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 0%;" id="progressBar"></div>
                    </div>

                    <!-- ═══ PASO 1 ═══ -->
                    <div class="step" id="step1">
                        <h5 class="mb-3">
                            <i class="fas fa-user-check text-primary"></i>
                            <?= $t('paso1_titulo') ?>
                        </h5>
                        <div class="alert alert-info py-2">
                            <i class="fas fa-info-circle"></i> <?= $t('paso1_alerta') ?>
                        </div>

                        <div id="datosUsuarioContainer">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2"><?= $t('cargando') ?></p>
                            </div>
                        </div>

                        <div id="formularioDatosUsuario" style="display: none;">
                            <div class="row">
                                <!-- Columna izquierda -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold"><?= $t('label_nombre') ?></label>
                                        <input type="text" class="form-control" id="edit_nombre" name="edit_nombre">
                                        <div class="text-danger small mt-1 error-msg" id="err_nombre"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold"><?= $t('label_telefono') ?></label>
                                        <input type="tel" class="form-control" id="edit_telefono" name="edit_telefono" placeholder="Ej. 70658129">
                                        <div class="text-danger small mt-1 error-msg" id="err_telefono"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold"><?= $t('label_nacimiento') ?></label>
                                        <input type="date" class="form-control" id="edit_fecha_nacimiento" name="edit_fecha_nacimiento">
                                        <div class="text-danger small mt-1 error-msg" id="err_fecha"></div>
                                    </div>
                                </div>
                                <!-- Columna derecha -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold"><?= $t('label_apellido') ?></label>
                                        <input type="text" class="form-control" id="edit_apellido" name="edit_apellido">
                                        <div class="text-danger small mt-1 error-msg" id="err_apellido"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold"><?= $t('label_nacionalidad') ?></label>
                                        <select class="form-control" id="edit_nacionalidad" name="edit_nacionalidad">
                                            <option value=""><?= $t('sel_nacionalidad') ?></option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold"><?= $t('label_genero') ?></label>
                                        <select class="form-control" id="edit_genero" name="edit_genero">
                                            <option value=""><?= $t('sel_genero') ?></option>
                                            <option value="Masculino"><?= $t('genero_m') ?></option>
                                            <option value="Femenino"><?= $t('genero_f') ?></option>
                                            <option value="Otro"><?= $t('genero_o') ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="confirmar_datos" required>
                                <label class="form-check-label fw-bold" for="confirmar_datos">
                                    <?= $t('confirmar_datos') ?>
                                </label>
                            </div>
                        </div>

                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-primary next-step" data-next="2" id="btnSiguiente" disabled>
                                <?= $t('siguiente') ?> <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ═══ PASO 2 ═══ -->
                    <div class="step" id="step2" style="display: none;">
                        <h5 class="mb-3"><?= $t('paso2_titulo') ?></h5>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block motivo-btn" data-value="aventura">
                                    <i class="fas fa-hiking"></i> <?= $t('motivo_aventura') ?>
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block motivo-btn" data-value="cultura">
                                    <i class="fas fa-landmark"></i> <?= $t('motivo_cultura') ?>
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block motivo-btn" data-value="fiesta">
                                    <i class="fas fa-glass-cheers"></i> <?= $t('motivo_fiesta') ?>
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block motivo-btn" data-value="gastronomia">
                                    <i class="fas fa-utensils"></i> <?= $t('motivo_gastro') ?>
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block motivo-btn" data-value="relax">
                                    <i class="fas fa-spa"></i> <?= $t('motivo_relax') ?>
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block motivo-btn" data-value="negocios">
                                    <i class="fas fa-briefcase"></i> <?= $t('motivo_negocios') ?>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" id="motivo_viaje" name="motivo_viaje" required>
                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-secondary prev-step" data-prev="1">
                                <i class="fas fa-arrow-left"></i> <?= $t('anterior') ?>
                            </button>
                            <button type="button" class="btn btn-primary next-step" data-next="3">
                                <?= $t('siguiente') ?> <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ═══ PASO 3 ═══ -->
                    <div class="step" id="step3" style="display: none;">
                        <h5 class="mb-3"><?= $t('paso3_titulo') ?></h5>
                        <div class="tags-container mb-3">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <span class="tag btn btn-outline-secondary" data-tag="trekking">
                                        <i class="fas fa-hiking"></i> <?= $t('act_trekking') ?>
                                    </span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <span class="tag btn btn-outline-secondary" data-tag="montanismo">
                                        <i class="fas fa-mountain"></i> <?= $t('act_montanismo') ?>
                                    </span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <span class="tag btn btn-outline-secondary" data-tag="downhill">
                                        <i class="fas fa-bicycle"></i> <?= $t('act_downhill') ?>
                                    </span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <span class="tag btn btn-outline-secondary" data-tag="vida_nocturna">
                                        <i class="fas fa-glass-cheers"></i> <?= $t('act_nocturna') ?>
                                    </span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <span class="tag btn btn-outline-secondary" data-tag="cultura">
                                        <i class="fas fa-landmark"></i> <?= $t('act_cultura') ?>
                                    </span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <span class="tag btn btn-outline-secondary" data-tag="gastronomia">
                                        <i class="fas fa-utensils"></i> <?= $t('act_gastro') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="actividades_seleccionadas" name="actividades">
                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-secondary prev-step" data-prev="2">
                                <i class="fas fa-arrow-left"></i> <?= $t('anterior') ?>
                            </button>
                            <button type="button" class="btn btn-primary next-step" data-next="4">
                                <?= $t('siguiente') ?> <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ═══ PASO 4A – VIDA NOCTURNA ═══ -->
                    <div class="step" id="step4a" style="display:none;">
                        <h5 class="mb-3"><i class="fas fa-glass-cheers"></i> <?= $t('paso4a_titulo') ?></h5>

                        <label class="fw-bold"><?= $t('vn_ambiente') ?></label>
                        <div class="row mb-3">
                            <div class="col-md-4"><div class="form-check">
                                <input class="form-check-input vn-ambiente" type="checkbox" value="discoteca">
                                <label class="form-check-label"><?= $t('vn_discoteca') ?></label>
                            </div></div>
                            <div class="col-md-4"><div class="form-check">
                                <input class="form-check-input vn-ambiente" type="checkbox" value="cocteleria">
                                <label class="form-check-label"><?= $t('vn_cocteleria') ?></label>
                            </div></div>
                            <div class="col-md-4"><div class="form-check">
                                <input class="form-check-input vn-ambiente" type="checkbox" value="jazz_cultural">
                                <label class="form-check-label"><?= $t('vn_jazz') ?></label>
                            </div></div>
                            <div class="col-md-4"><div class="form-check">
                                <input class="form-check-input vn-ambiente" type="checkbox" value="karaoke">
                                <label class="form-check-label"><?= $t('vn_karaoke') ?></label>
                            </div></div>
                            <div class="col-md-4"><div class="form-check">
                                <input class="form-check-input vn-ambiente" type="checkbox" value="cerveceria">
                                <label class="form-check-label"><?= $t('vn_cerveceria') ?></label>
                            </div></div>
                        </div>

                        <label class="fw-bold"><?= $t('vn_generos_fav') ?></label>
                        <div class="row mb-2">
                            <?php foreach ([
                                'rock'      => 'Rock',
                                'metal'     => 'Metal',
                                'reggaeton' => 'Reggaetón',
                                'electronica'=> 'Electrónica',
                                'pop'       => 'Pop',
                                'latina'    => $lang !== 'es' ? 'Latin (cumbia, salsa, etc.)' : 'Latina (cumbia, salsa, etc.)',
                                'jazz'      => 'Jazz / Blues',
                                'folklore'  => $lang !== 'es' ? 'Folklore' : 'Folklore',
                            ] as $val => $label): ?>
                            <div class="col-md-3"><div class="form-check">
                                <input class="form-check-input vn-genero-fav" type="checkbox" value="<?= $val ?>">
                                <label class="form-check-label"><?= $label ?></label>
                            </div></div>
                            <?php endforeach; ?>
                        </div>

                        <label class="fw-bold"><?= $t('vn_generos_excl') ?></label>
                        <div class="row mb-3">
                            <?php foreach (['rock'=>'Rock','metal'=>'Metal','reggaeton'=>'Reggaetón','electronica'=>'Electrónica'] as $val=>$label): ?>
                            <div class="col-md-3"><div class="form-check">
                                <input class="form-check-input vn-genero-excluir" type="checkbox" value="<?= $val ?>">
                                <label class="form-check-label"><?= $label ?></label>
                            </div></div>
                            <?php endforeach; ?>
                        </div>

                        <label class="fw-bold"><?= $t('vn_intensidad') ?></label>
                        <select id="vn-intensidad" class="form-control">
                            <option value=""><?= $t('vn_int_todos') ?></option>
                            <option value="baja"><?= $t('vn_int_baja') ?></option>
                            <option value="media"><?= $t('vn_int_media') ?></option>
                            <option value="alta"><?= $t('vn_int_alta') ?></option>
                        </select>

                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-secondary prev-step" data-prev="3"><?= $t('anterior') ?></button>
                            <button type="button" class="btn btn-primary next-step" data-next="4b"><?= $t('siguiente') ?></button>
                        </div>
                    </div>

                    <!-- ═══ PASO 4B – GASTRONOMÍA ═══ -->
                    <div class="step" id="step4b" style="display:none;">
                        <h5 class="mb-3"><i class="fas fa-utensils"></i> <?= $t('paso4b_titulo') ?></h5>

                        <label class="fw-bold"><?= $t('gastro_fav') ?></label>
                        <div class="row mb-3">
                            <?php
                            $gastroItems = [
                                'criolla'    => $lang !== 'es' ? 'Creole / Traditional' : 'Criolla / Tradicional',
                                'parrilla'   => $lang !== 'es' ? 'Grill / Meats'        : 'Parrilla / Carnes',
                                'italiana'   => $lang !== 'es' ? 'Italian (pasta, pizza)': 'Italiana (pastas, pizzas)',
                                'china'      => $lang !== 'es' ? 'Chinese / Chifa'       : 'China / Chifa',
                                'japonesa'   => $lang !== 'es' ? 'Japanese / Sushi'      : 'Japonesa / Sushi',
                                'mexicana'   => $lang !== 'es' ? 'Mexican'               : 'Mexicana',
                                'vegetariana'=> $lang !== 'es' ? 'Vegetarian'            : 'Vegetariana',
                                'vegana'     => $lang !== 'es' ? 'Vegan'                 : 'Vegana',
                                'fastfood'   => $lang !== 'es' ? 'Fast food (burgers, chicken)' : 'Comida rápida (burgers, pollo)',
                                'mariscos'   => $lang !== 'es' ? 'Seafood / Fish'        : 'Mariscos / Pescados',
                                'cafe'       => $lang !== 'es' ? 'Cafés / Pastry'        : 'Cafés / Repostería',
                            ];
                            foreach ($gastroItems as $val => $label): ?>
                            <div class="col-md-4"><div class="form-check">
                                <input class="form-check-input gastro-fav" type="checkbox" value="<?= $val ?>">
                                <label class="form-check-label"><?= $label ?></label>
                            </div></div>
                            <?php endforeach; ?>
                        </div>

                        <label class="fw-bold"><?= $t('gastro_excl') ?></label>
                        <div class="row mb-3">
                            <?php foreach ([
                                'parrilla' => $lang !== 'es' ? 'Grill / Meats' : 'Parrilla / Carnes',
                                'fastfood' => $lang !== 'es' ? 'Fast food'     : 'Comida rápida',
                                'mariscos' => $lang !== 'es' ? 'Seafood'       : 'Mariscos',
                            ] as $val => $label): ?>
                            <div class="col-md-4"><div class="form-check">
                                <input class="form-check-input gastro-excluir" type="checkbox" value="<?= $val ?>">
                                <label class="form-check-label"><?= $label ?></label>
                            </div></div>
                            <?php endforeach; ?>
                        </div>

                        <label class="fw-bold"><?= $t('gastro_precio') ?></label>
                        <select id="gastro-precio" class="form-control">
                            <option value=""><?= $t('gastro_p_todos') ?></option>
                            <option value="economico"><?= $t('gastro_p_eco') ?></option>
                            <option value="medio"><?= $t('gastro_p_medio') ?></option>
                            <option value="alto"><?= $t('gastro_p_alto') ?></option>
                        </select>

                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-secondary prev-step" data-prev="4a"><?= $t('anterior') ?></button>
                            <button type="button" class="btn btn-primary next-step" data-next="4c"><?= $t('siguiente') ?></button>
                        </div>
                    </div>

                    <!-- ═══ PASO 4C – EXPERIENCIA Y PRESUPUESTO ═══ -->
                    <div class="step" id="step4c" style="display: none;">
                        <h5 class="mb-3"><?= $t('paso4c_titulo') ?></h5>

                        <div class="mb-4">
                            <label class="mb-2 fw-bold"><?= $t('exp_label') ?></label>
                            <div class="row">
                                <?php
                                $experiencias = [
                                    'aventura_naturaleza'  => ['fas fa-mountain text-danger',  $lang !== 'es' ? 'Adventure & Nature'      : 'Aventura y Naturaleza',    $lang !== 'es' ? 'Trekking, mountain, outdoor sports' : 'Trekking, montaña, deportes al aire libre'],
                                    'cultura_tradiciones'  => ['fas fa-landmark text-warning', $lang !== 'es' ? 'Culture & Traditions'     : 'Cultura y Tradiciones',    $lang !== 'es' ? 'Museums, historic sites, local customs' : 'Museos, sitios históricos, costumbres locales'],
                                    'vida_nocturna_ocio'   => ['fas fa-glass-cheers text-purple',$lang !== 'es' ? 'Nightlife & Entertainment': 'Vida Nocturna y Ocio',    $lang !== 'es' ? 'Bars, clubs, music, entertainment' : 'Bares, discotecas, música, entretenimiento'],
                                    'gastronomia_sabores'  => ['fas fa-utensils text-success',  $lang !== 'es' ? 'Gastronomy & Flavors'     : 'Gastronomía y Sabores',    $lang !== 'es' ? 'Local food, markets, culinary experiences' : 'Comida típica, mercados, experiencias culinarias'],
                                    'relax_bienestar'      => ['fas fa-spa text-info',          $lang !== 'es' ? 'Relax & Wellness'         : 'Relax y Bienestar',        $lang !== 'es' ? 'Rest, calm activities, easy walks' : 'Descanso, actividades tranquilas, paseos suaves'],
                                    'fotografia_paisajes'  => ['fas fa-camera text-secondary',  $lang !== 'es' ? 'Photography & Landscapes' : 'Fotografía y Paisajes',    $lang !== 'es' ? 'Viewpoints, sunsets, urban & natural views' : 'Miradores, atardeceres, vistas urbanas y naturales'],
                                ];
                                foreach ($experiencias as $exp => [$icon, $titulo, $desc]): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card experience-card-multi" data-exp="<?= $exp ?>">
                                        <div class="card-body text-center">
                                            <i class="fas <?= $icon ?> fa-3x mb-2"></i>
                                            <h6><?= $titulo ?></h6>
                                            <small><?= $desc ?></small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="experiencias_seleccionadas" name="experiencias_seleccionadas">
                            <small class="text-muted">
                                <?= $t('exp_count') ?> <span id="contador_experiencias">0</span>
                            </small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label><?= $t('presupuesto_label') ?></label>
                                <select class="form-control" id="presupuesto_max" name="presupuesto_max" required>
                                    <option value=""><?= $t('presupuesto_sel') ?></option>
                                    <option value="bajo"><?= $t('presupuesto_bajo') ?></option>
                                    <option value="medio"><?= $t('presupuesto_medio') ?></option>
                                    <option value="alto"><?= $t('presupuesto_alto') ?></option>
                                    <option value="premium"><?= $t('presupuesto_prem') ?></option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label><?= $t('duracion_label') ?></label>
                                <select class="form-control" id="duracion" name="duracion" required>
                                    <option value=""><?= $t('duracion_sel') ?></option>
                                    <option value="1-3"><?= $t('duracion_1') ?></option>
                                    <option value="4-7"><?= $t('duracion_2') ?></option>
                                    <option value="8-14"><?= $t('duracion_3') ?></option>
                                    <option value="15+"><?= $t('duracion_4') ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-secondary prev-step" data-prev="3">
                                <i class="fas fa-arrow-left"></i> <?= $t('anterior') ?>
                            </button>
                            <button type="button" class="btn btn-primary next-step" data-next="5">
                                <?= $t('siguiente') ?> <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ═══ PASO 5 ═══ -->
                    <div class="step" id="step5" style="display: none;">
                        <h5 class="mb-3"><?= $t('paso5_titulo') ?></h5>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label><?= $t('compania_label') ?></label>
                                <select class="form-control" id="compania" name="compania" required>
                                    <option value=""><?= $t('compania_sel') ?></option>
                                    <option value="solo"><?= $t('compania_solo') ?></option>
                                    <option value="pareja"><?= $t('compania_pareja') ?></option>
                                    <option value="familia"><?= $t('compania_familia') ?></option>
                                    <option value="amigos"><?= $t('compania_amigos') ?></option>
                                    <option value="grupo"><?= $t('compania_grupo') ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-secondary prev-step" data-prev="4c">
                                <i class="fas fa-arrow-left"></i> <?= $t('anterior') ?>
                            </button>
                            <button type="button" class="btn btn-success" onclick="guardarPreferencias()">
                                <i class="fas fa-save"></i> <?= $t('guardar') ?>
                            </button>
                        </div>
                    </div>

                </form>
            </div><!-- /modal-body -->
        </div>
    </div>
</div>

<script>
// ── Textos traducidos desde PHP ──────────────────────────────────────────────
const MODAL_T = <?= json_encode([
    'warn_completar'  => Lang::get('modal_intereses.warn_completar'),
    'warn_motivo'     => Lang::get('modal_intereses.warn_motivo'),
    'warn_actividad'  => Lang::get('modal_intereses.warn_actividad'),
    'warn_vn'         => Lang::get('modal_intereses.warn_vn'),
    'warn_gastro'     => Lang::get('modal_intereses.warn_gastro'),
    'warn_exp'        => Lang::get('modal_intereses.warn_exp'),
    'warn_presupuesto'=> Lang::get('modal_intereses.warn_presupuesto'),
    'warn_duracion'   => Lang::get('modal_intereses.warn_duracion'),
    'warn_compania'   => Lang::get('modal_intereses.warn_compania'),
    'exito'           => Lang::get('modal_intereses.exito'),
    'error_guardar'   => Lang::get('modal_intereses.error_guardar'),
    'error_conn'      => Lang::get('modal_intereses.error_conn'),
    'err_obligatorio' => Lang::get('modal_intereses.err_obligatorio'),
    'err_letras'      => Lang::get('modal_intereses.err_letras'),
    'err_telefono'    => Lang::get('modal_intereses.err_telefono'),
    'err_edad_min'    => Lang::get('modal_intereses.err_edad_min'),
    'err_edad_max'    => Lang::get('modal_intereses.err_edad_max'),
    'err_carga'       => Lang::get('modal_intereses.err_carga'),
    'err_conexion'    => Lang::get('modal_intereses.err_conexion'),
    'sel_nacionalidad'=> Lang::get('modal_intereses.sel_nacionalidad'),
]) ?>;

// ── Variables globales ───────────────────────────────────────────────────────
let reglasValidacion = {};
let currentStep      = 1;
let selectedTags     = [];
let selectedMotivo   = '';
let selectedExperiencias = [];
let vnAmbiente = [], vnGenerosFav = [], vnGenerosExcluir = [], vnIntensidad = '';
let gastroFav = [], gastroExcluir = [], gastroPrecio = '';
let flujoStep4 = [], indiceStep4 = 0;

// ── Cargar reglas de validación ──────────────────────────────────────────────
function cargarReglasValidacion() {
    return $.getJSON('assets/js/regex.json')
        .done(function(data) {
            reglasValidacion = {
                letras:   new RegExp(data.letras),
                email:    new RegExp(data.email),
                telefono: new RegExp(data.telefono)
            };
        })
        .fail(function() {
            reglasValidacion = {
                letras:   /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/,
                email:    /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                telefono: /^[0-9+\-\s]{7,15}$/
            };
        });
}

// ── Calcular rango de edad ───────────────────────────────────────────────────
function calcularRangoEdad(fechaNacimiento) {
    if (!fechaNacimiento) return '';
    const hoy       = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) edad--;
    if (edad >= 18 && edad <= 25) return "18-25";
    if (edad >= 26 && edad <= 35) return "26-35";
    if (edad >= 36 && edad <= 45) return "36-45";
    if (edad >= 46 && edad <= 60) return "46-60";
    if (edad > 60)  return "60+";
    if (edad < 18)  return "menor_18";
    return "";
}

// ── Cargar países con preselección bilingüe ──────────────────────────────────
function cargarPaisesSelect(selectId, paisSeleccionado) {
    const backup = ["Bolivia","Argentina","Brasil","Chile","Colombia",
        "Ecuador","Paraguay","Perú","Uruguay","Venezuela",
        "Estados Unidos","España","Francia","Italia","Alemania"];

    $.getJSON('assets/js/paises.json')
        .done(function(data) { llenarSelectPaises(data, selectId, paisSeleccionado); })
        .fail(function()     { llenarSelectPaises(backup, selectId, paisSeleccionado); });
}
// Leer idioma de forma robusta al momento de ejecutar


function llenarSelectPaises(paises, selectId, paisSeleccionado) {
    const select = $(selectId);
    select.empty().append(`<option value="">${MODAL_T.sel_nacionalidad}</option>`);

    // ── PHP inyecta el idioma directamente, 100% confiable ──
    const esIngles = <?= $lang !== 'es' ? 'true' : 'false' ?>;

    paises.forEach(p => {
        // nombre    = inglés  (ej: "United States")
        // nombre_es = español (ej: "Estados Unidos")
        const nombre_es = typeof p === 'string' ? p : (p.nombre_es || p.nombre);
        const nombre_en = typeof p === 'string' ? p : (p.nombre    || p.nombre_es);

        // Label según idioma de sesión PHP
        const label = esIngles ? nombre_en : nombre_es;

        // Value siempre en español (coincide con lo guardado en BD)
        const value = nombre_es;

        // Preselección: buscar en ambas versiones
        const sel = (
            nombre_es === paisSeleccionado ||
            nombre_en === paisSeleccionado
        ) ? 'selected' : '';

        select.append(`<option value="${value}" ${sel}>${label}</option>`);
    });
}

// ── Validación de campos ─────────────────────────────────────────────────────
function mostrarError(input, div, msg) { input.addClass('is-invalid').removeClass('is-valid'); $(div).text(msg).show(); }
function limpiarError(input, div)      { input.removeClass('is-invalid').addClass('is-valid'); $(div).text('').hide(); }

function validarCampo(input, regex, errorDiv, mensaje) {
    const valor = input.val().trim();
    if (valor === '')        { mostrarError(input, errorDiv, MODAL_T.err_obligatorio); return false; }
    if (regex && !regex.test(valor)) { mostrarError(input, errorDiv, mensaje); return false; }
    limpiarError(input, errorDiv);
    return true;
}

function validarEdadForm() {
    const inputFecha     = $('#edit_fecha_nacimiento');
    const fechaNacimiento = inputFecha.val();
    const errorDiv       = '#err_fecha';
    if (!fechaNacimiento) { mostrarError(inputFecha, errorDiv, MODAL_T.err_obligatorio); return false; }

    const hoy       = new Date();
    const cumpleanos = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - cumpleanos.getFullYear();
    const mes = hoy.getMonth() - cumpleanos.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < cumpleanos.getDate())) edad--;

    if (edad < 10)  { mostrarError(inputFecha, errorDiv, MODAL_T.err_edad_min.replace('%d', edad)); return false; }
    if (edad > 100) { mostrarError(inputFecha, errorDiv, MODAL_T.err_edad_max); return false; }
    limpiarError(inputFecha, errorDiv);
    return true;
}

// ── Cargar datos del usuario ─────────────────────────────────────────────────
function cargarDatosUsuario() {
    $('#datosUsuarioContainer .text-center').show();
    $('#formularioDatosUsuario').hide();

    $.ajax({
        url: 'index.php?c=login&a=VerPreferencias',
        type: 'GET', dataType: 'json',
        success: function(response) {
            if (response.success) {
                const usuario = response.usuario;
                $('#datosUsuarioContainer .text-center').hide();
                $('#formularioDatosUsuario').show();

                $('#edit_nombre').val(usuario.nombre   || '');
                $('#edit_apellido').val(usuario.apellido || '');
                $('#edit_telefono').val(usuario.telefono || '');

                if (usuario.fecha_nacimiento) {
                    $('#edit_fecha_nacimiento').val(usuario.fecha_nacimiento);
                }

                const hoy      = new Date();
                const maxFecha = new Date(hoy.getFullYear()-10,  hoy.getMonth(), hoy.getDate()).toISOString().split('T')[0];
                const minFecha = new Date(hoy.getFullYear()-100, hoy.getMonth(), hoy.getDate()).toISOString().split('T')[0];
                $('#edit_fecha_nacimiento').attr('max', maxFecha).attr('min', minFecha);

                // ── Preselección de nacionalidad (bilingüe) ──────────────────
                cargarPaisesSelect('#edit_nacionalidad', usuario.nacionalidad || '');

                if (usuario.genero) $('#edit_genero').val(usuario.genero);

                inicializarValidaciones();
            } else {
                $('#datosUsuarioContainer').html(`<div class="alert alert-danger">${MODAL_T.err_carga}</div>`);
            }
        },
        error: function() {
            $('#datosUsuarioContainer').html(`<div class="alert alert-danger">${MODAL_T.err_conexion}</div>`);
        }
    });
}

// ── Validaciones en tiempo real ──────────────────────────────────────────────
function inicializarValidaciones() {
    cargarReglasValidacion().done(function() {
        $('#edit_nombre').off('input blur').on('input blur', function() {
            validarCampo($(this), reglasValidacion.letras, '#err_nombre', MODAL_T.err_letras);
            verificarConfirmacion();
        });
        $('#edit_apellido').off('input blur').on('input blur', function() {
            validarCampo($(this), reglasValidacion.letras, '#err_apellido', MODAL_T.err_letras);
            verificarConfirmacion();
        });
        $('#edit_telefono').off('input blur').on('input blur', function() {
            validarCampo($(this), reglasValidacion.telefono, '#err_telefono', MODAL_T.err_telefono);
            verificarConfirmacion();
        });
        $('#edit_fecha_nacimiento').off('input blur').on('input blur', function() {
            validarEdadForm(); verificarConfirmacion();
        });
        $('#edit_nacionalidad, #edit_genero').off('change').on('change', function() {
            verificarConfirmacion();
        });
    });
}

function verificarConfirmacion() {
    const nombreValido    = validarCampo($('#edit_nombre'),    reglasValidacion.letras,   '#err_nombre',   '');
    const apellidoValido  = validarCampo($('#edit_apellido'),  reglasValidacion.letras,   '#err_apellido', '');
    const telefonoValido  = validarCampo($('#edit_telefono'),  reglasValidacion.telefono, '#err_telefono', '');
    const fechaValida     = validarEdadForm();
    const nacionalidadOk  = $('#edit_nacionalidad').val() !== '';
    const generoOk        = $('#edit_genero').val() !== '';
    const checkboxMarcado = $('#confirmar_datos').is(':checked');

    const todosValidos = nombreValido && apellidoValido && telefonoValido &&
                         fechaValida  && nacionalidadOk && generoOk;

    $('#btnSiguiente').prop('disabled', !(todosValidos && checkboxMarcado));
}

// ── Validar paso actual ──────────────────────────────────────────────────────
function validateStep(step) {
    switch (step) {
        case 1:
            if ($('#btnSiguiente').is(':disabled')) {
                toastr.warning(MODAL_T.warn_completar); return false;
            }
            return true;
        case 2:
            if (!selectedMotivo) {
                toastr.warning(MODAL_T.warn_motivo); return false;
            }
            return true;
        case 3:
            if (selectedTags.length === 0) {
                toastr.warning(MODAL_T.warn_actividad); return false;
            }
            return true;
        case '4a':
            if (vnAmbiente.length === 0 && vnGenerosFav.length === 0) {
                toastr.warning(MODAL_T.warn_vn); return false;
            }
            return true;
        case '4b':
            if (gastroFav.length === 0) {
                toastr.warning(MODAL_T.warn_gastro); return false;
            }
            return true;
        case '4c':
            if (selectedExperiencias.length === 0) { toastr.warning(MODAL_T.warn_exp);         return false; }
            if (!$('#presupuesto_max').val())       { toastr.warning(MODAL_T.warn_presupuesto); return false; }
            if (!$('#duracion').val())              { toastr.warning(MODAL_T.warn_duracion);    return false; }
            return true;
        default:
            return true;
    }
}

// ── Progreso ─────────────────────────────────────────────────────────────────
function updateProgress() {
    let ps = currentStep;
    if (currentStep === '4a' || currentStep === '4b' || currentStep === '4c') ps = 4;
    $('#progressBar').css('width', (ps / 5 * 100) + '%');
}

// ── Guardar preferencias ─────────────────────────────────────────────────────
function guardarPreferencias() {
    if (!$('#compania').val()) { toastr.warning(MODAL_T.warn_compania); return; }

    function calcularEdadYRango(fechaNacimiento) {
        if (!fechaNacimiento) return { edad_exacta: null, rango_edad: '' };
        const hoy = new Date();
        const nac = new Date(fechaNacimiento);
        let edad = hoy.getFullYear() - nac.getFullYear();
        const mes = hoy.getMonth() - nac.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < nac.getDate())) edad--;
        let rango = '';
        if (edad >= 18 && edad <= 25) rango = "18-25";
        else if (edad >= 26 && edad <= 35) rango = "26-35";
        else if (edad >= 36 && edad <= 45) rango = "36-45";
        else if (edad >= 46 && edad <= 60) rango = "46-60";
        else if (edad > 60)  rango = "60+";
        else if (edad < 18)  rango = "menor_18";
        return { edad_exacta: edad, rango_edad: rango };
    }

    const { edad_exacta, rango_edad } = calcularEdadYRango($('#edit_fecha_nacimiento').val());

    let actividadesArray = [];
    if ($('#actividades_seleccionadas').val()) {
        actividadesArray = $('#actividades_seleccionadas').val().split(',');
    }

    let duracion = 0;
    switch ($('#duracion').val()) {
        case '1-3': duracion = 3;  break;
        case '4-7': duracion = 7;  break;
        case '8-14':duracion = 14; break;
        case '15+': duracion = 15; break;
    }

    const datosCompletos = {
        usuario: {
            nombre:           $('#edit_nombre').val(),
            apellido:         $('#edit_apellido').val(),
            telefono:         $('#edit_telefono').val(),
            nacionalidad:     $('#edit_nacionalidad').val(),
            fecha_nacimiento: $('#edit_fecha_nacimiento').val(),
            genero:           $('#edit_genero').val()
        },
        preferencias: {
            motivo_viaje:    $('#motivo_viaje').val(),
            presupuesto_max: $('#presupuesto_max').val(),
            duracion_dias:   duracion,
            idioma_preferido: null,
            intereses: {
                motivo_viaje: $('#motivo_viaje').val(),
                actividades:  actividadesArray,
                experiencias: selectedExperiencias,
                edad_exacta:  edad_exacta,
                rango_edad:   rango_edad,
                compania:     $('#compania').val(),
                preferencias_vida_nocturna: {
                    ambiente:          vnAmbiente,
                    generos_favoritos: vnGenerosFav,
                    generos_excluidos: vnGenerosExcluir,
                    intensidad:        vnIntensidad
                },
                preferencias_gastronomicas: {
                    tipos_favoritos: gastroFav,
                    tipos_excluidos: gastroExcluir,
                    nivel_precio:    gastroPrecio
                }
            }
        }
    };

    $.ajax({
        url: 'index.php?c=login&a=GuardarPreferencias',
        type: 'POST',
        data: JSON.stringify(datosCompletos),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#modalPreferencias').modal('hide');
                toastr.success(MODAL_T.exito);
                setTimeout(() => location.reload(), 1500);
            } else {
                toastr.error(MODAL_T.error_guardar + (response.message || ''));
            }
        },
        error: function() { toastr.error(MODAL_T.error_conn); }
    });
}

// ── DOCUMENT READY ───────────────────────────────────────────────────────────
$(document).ready(function () {
    cargarDatosUsuario();

    toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: '3000' };

    // Experiencias
    $('.experience-card-multi').off('click').on('click', function () {
        const exp = $(this).data('exp');
        if ($(this).hasClass('border-primary bg-light')) {
            $(this).removeClass('border-primary bg-light');
            selectedExperiencias = selectedExperiencias.filter(e => e !== exp);
        } else {
            $(this).addClass('border-primary bg-light');
            selectedExperiencias.push(exp);
        }
        $('#experiencias_seleccionadas').val(selectedExperiencias.join(','));
        $('#contador_experiencias').text(selectedExperiencias.length)
            .css({ color: selectedExperiencias.length > 0 ? '#28a745' : '#6c757d', fontWeight: 'bold' });
    });

    // Vida nocturna
    $('.vn-ambiente').off('change').on('change', function () {
        vnAmbiente = $('.vn-ambiente:checked').map(function () { return $(this).val(); }).get();
    });

    function sincronizarGeneros(origen, destino, valor) {
        const $dest = $(`.${destino}[value="${valor}"]`);
        if ($dest.is(':checked')) $dest.prop('checked', false).trigger('change');
        $dest.prop('disabled', true);
    }

    $('.vn-genero-fav').on('change', function () {
        const valor = $(this).val();
        if ($(this).is(':checked')) {
            sincronizarGeneros('vn-genero-fav', 'vn-genero-excluir', valor);
        } else {
            if (!$(`.vn-genero-excluir[value="${valor}"]`).is(':checked'))
                $(`.vn-genero-excluir[value="${valor}"]`).prop('disabled', false);
        }
        vnGenerosFav = $('.vn-genero-fav:checked').map(function () { return $(this).val(); }).get();
    });

    $('.vn-genero-excluir').on('change', function () {
        const valor = $(this).val();
        if ($(this).is(':checked')) {
            sincronizarGeneros('vn-genero-excluir', 'vn-genero-fav', valor);
        } else {
            if (!$(`.vn-genero-fav[value="${valor}"]`).is(':checked'))
                $(`.vn-genero-fav[value="${valor}"]`).prop('disabled', false);
        }
        vnGenerosExcluir = $('.vn-genero-excluir:checked').map(function () { return $(this).val(); }).get();
    });

    $('#vn-intensidad').off('change').on('change', function () { vnIntensidad = $(this).val(); });

    // Gastronomía
    function syncGastro(origen, destino, valor, checked) {
        if (checked) {
            const $dest = $(`.${destino}[value="${valor}"]`);
            if ($dest.is(':checked')) $dest.prop('checked', false).trigger('change');
            $dest.prop('disabled', true);
        } else {
            if (!$(`.${destino}[value="${valor}"]`).is(':checked'))
                $(`.${destino}[value="${valor}"]`).prop('disabled', false);
        }
    }

    $('.gastro-fav').off('change').on('change', function () {
        gastroFav = $('.gastro-fav:checked').map(function () { return $(this).val(); }).get();
        syncGastro('gastro-fav', 'gastro-excluir', $(this).val(), $(this).is(':checked'));
    });

    $('.gastro-excluir').off('change').on('change', function () {
        gastroExcluir = $('.gastro-excluir:checked').map(function () { return $(this).val(); }).get();
        syncGastro('gastro-excluir', 'gastro-fav', $(this).val(), $(this).is(':checked'));
    });

    $('#gastro-precio').off('change').on('change', function () { gastroPrecio = $(this).val(); });

    // Checkbox confirmación
    $('#confirmar_datos').off('change').on('change', function () { verificarConfirmacion(); });

    // Navegación → siguiente
    $('.next-step').off('click').on('click', function () {
        if (!validateStep(currentStep)) return;
        $('#step' + currentStep).hide();

        if (currentStep === 3) {
            definirFlujoStep4();
            currentStep = flujoStep4[indiceStep4];
            $('#step' + currentStep).show();
            updateProgress();
            return;
        }
        if (currentStep === '4a' || currentStep === '4b') {
            indiceStep4++;
            currentStep = flujoStep4[indiceStep4];
            $('#step' + currentStep).show();
            updateProgress();
            return;
        }
        currentStep = $(this).data('next');
        $('#step' + currentStep).show();
        updateProgress();
    });

    function definirFlujoStep4() {
        flujoStep4  = [];
        indiceStep4 = 0;
        if (selectedTags.includes('vida_nocturna')) flujoStep4.push('4a');
        if (selectedTags.includes('gastronomia'))   flujoStep4.push('4b');
        flujoStep4.push('4c');
    }

    // Navegación → anterior
    $('.prev-step').off('click').on('click', function () {
        $('#step' + currentStep).hide();
        if (currentStep === '4a' || currentStep === '4b' || currentStep === '4c') {
            if (indiceStep4 === 0) { currentStep = 3; $('#step3').show(); }
            else { indiceStep4--; currentStep = flujoStep4[indiceStep4]; $('#step' + currentStep).show(); }
            updateProgress(); return;
        }
        currentStep = $(this).data('prev');
        $('#step' + currentStep).show();
        updateProgress();
    });

    // Motivo
    $('.motivo-btn').off('click').on('click', function () {
        $('.motivo-btn').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
        selectedMotivo = $(this).data('value');
        $('#motivo_viaje').val(selectedMotivo);
    });

    // Tags actividades
    $('.tag').off('click').on('click', function () {
        const tag = $(this).data('tag');
        if ($(this).hasClass('btn-primary')) {
            $(this).removeClass('btn-primary').addClass('btn-outline-secondary');
            selectedTags = selectedTags.filter(t => t !== tag);
        } else {
            $(this).removeClass('btn-outline-secondary').addClass('btn-primary');
            selectedTags.push(tag);
        }
        $('#actividades_seleccionadas').val(selectedTags.join(','));
    });
});
</script>