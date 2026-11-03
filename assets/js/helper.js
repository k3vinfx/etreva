function setLang(lang) {
  if (!i18n[lang]) return;
  const t = i18n[lang];

  // ── Botones de bandera ──
  document.querySelectorAll('.lang-btn').forEach(b =>
    b.classList.toggle('active', b.dataset.lang === lang)
  );

  
  // ── Login UI ──
  const el = (sel) => document.querySelector(sel);

  if (el('.auth-title'))                    el('.auth-title').textContent                      = t.loginTitle;
  if (el('input[name="CorreoElectronico"]'))el('input[name="CorreoElectronico"]').placeholder  = t.emailPH;
  if (el('input[name="Contrasena"]'))       el('input[name="Contrasena"]').placeholder         = t.passwordPH;
  if (el('.auth-btn[type="submit"]'))       el('.auth-btn[type="submit"]').textContent          = t.loginBtn;
  if (el('.text-center.my-3 span'))         el('.text-center.my-3 span').textContent            = t.orWith;

  const links = document.querySelectorAll('.auth-link');
  if (links[0]) links[0].textContent = t.registerLink;
  if (links[1]) links[1].textContent = t.forgotLink;

  // ── Modal de registro ──
  const modal = document.getElementById('registerModal');
  if (modal) {
    const mTitle = modal.querySelector('.modal-title');
    if (mTitle) mTitle.innerHTML = `<i class="fas fa-user-plus me-2"></i>${t.modalTitle}`;

    // Labels
    const lbls = modal.querySelectorAll('label.form-label');
    ['lblNombre','lblApellido','lblEmail','lblTelefono',
     'lblNacionalidad','lblFecha','lblGenero','lblPassword','lblConfirm']
    .forEach((k, i) => { if (lbls[i]) lbls[i].textContent = t[k]; });

    // Placeholders
    const phs = {
      '#reg_nombre':       'phNombre',
      '#reg_apellido':     'phApellido',
      '#reg_email':        'phEmail',
      '#reg_telefono':     'phTelefono',
      '#confirm_password': 'phConfirm',
    };
    Object.entries(phs).forEach(([sel, key]) => {
      const input = modal.querySelector(sel);
      if (input) input.placeholder = t[key];
    });

    // Selects
    const selNac = modal.querySelector('#select-nacionalidad option[disabled]');
    if (selNac) selNac.textContent = t.selNacionalidad;

    const selGen = modal.querySelector('#reg_genero');
    if (selGen) {
      if (selGen.options[0]) selGen.options[0].textContent = t.selGenero;
      if (selGen.options[1]) selGen.options[1].textContent = t.optMasculino;
      if (selGen.options[2]) selGen.options[2].textContent = t.optFemenino;
      if (selGen.options[3]) selGen.options[3].textContent = t.optOtro;
    }

    const regBtn = modal.querySelector('[onclick="registrarUsuario()"]');
    if (regBtn) regBtn.textContent = t.registerBtn;
  }

  // ── Carrusel: actualizar array global Y slide actual ──  <-- DENTRO de setLang
  if (t.carousel) {
    window.texts = t.carousel;

    const textContainer = document.querySelector('.carousel-text');
    if (textContainer && typeof window.currentCarouselIndex !== 'undefined') {
      const slide = window.texts[window.currentCarouselIndex];
      if (slide) {
        textContainer.querySelector('h2').textContent = slide.title;
        textContainer.querySelector('p').textContent  = slide.desc;
      }
    }
  }

  // Recargar select de nacionalidad en el idioma correcto
if (typeof llenarSelect === 'function' && window._paisesData) {
    llenarSelect(window._paisesData, lang);
} else if (typeof cargarPaises === 'function') {
    cargarPaises();
}
  // ── Campo oculto del form de registro ──
  const hiddenLang = document.getElementById('reg_idioma');
  if (hiddenLang) hiddenLang.value = lang;

  // ── Guardar en sesión PHP ──
  if (typeof $ !== 'undefined') {
    $.post('index.php?c=login&a=SetLang', { lang: lang });
  }

  // ── Backup en sessionStorage ──
  sessionStorage.setItem('etreva_lang', lang);
}

// Auto-inicializar al cargar el DOM
document.addEventListener('DOMContentLoaded', function () {
  const lang = sessionStorage.getItem('etreva_lang') || 'es';
  setLang(lang);
});