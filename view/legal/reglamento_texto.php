<?php
/**
 * view/legal/reglamento_texto.php
 * Texto del Reglamento de uso, privacidad y riesgos. Lo usan el modal de consentimiento
 * (view/cliente/modal/modal_consentimiento.php) y la página pública (index.php?c=legal&a=Reglamento).
 * Si se modifica el contenido, subir Consentimiento::VERSION para que todos lo acepten de nuevo.
 */
require_once __DIR__ . '/../../model/consentimiento.php';
?>
<div class="reglamento-texto">
<p class="reg-meta">Versión <?= Consentimiento::VERSION ?> · Etreva Travel · La Paz, Bolivia</p>

<h4>1. Quiénes somos</h4>
<p>Etreva Travel ("Etreva", "nosotros") es un sistema web de recomendaciones turísticas de La Paz, Bolivia. Es responsable del tratamiento de los datos personales que registras. Contacto: <a href="mailto:k3vinfx@gmail.com">k3vinfx@gmail.com</a> o el botón de Soporte del menú.</p>

<h4>2. Qué datos recopilamos</h4>
<ul>
    <li><strong>Registro y perfil:</strong> nombre, apellido, correo, teléfono, fecha de nacimiento, género, nacionalidad e idioma.</li>
    <li><strong>Preferencias de viaje:</strong> motivo, presupuesto, duración, compañía, rango de edad, actividades e intereses.</li>
    <li><strong>Uso del sistema:</strong> atracciones vistas, clics, búsquedas, favoritos, itinerarios, lugares visitados, calificaciones y reseñas.</li>
    <li><strong>Datos técnicos:</strong> dirección IP, navegador y dispositivo, para seguridad y para registrar este consentimiento.</li>
    <li><strong>Pago del plan:</strong> datos del comprobante que subes. No guardamos números de tarjeta.</li>
</ul>

<h4>3. Para qué los usamos</h4>
<ul>
    <li>Generar recomendaciones personalizadas con nuestra red neuronal y armar tus itinerarios.</li>
    <li>Mejorar el modelo de recomendación con tus calificaciones (de forma agregada).</li>
    <li>Gestionar tu cuenta, tu plan, el soporte y la seguridad del sistema.</li>
    <li>Proteger a los menores de edad (ver punto 7).</li>
</ul>

<h4>4. Base legal y tu consentimiento</h4>
<p>Tratamos tus datos con tu <strong>consentimiento libre, previo, expreso e informado</strong>, conforme al derecho a la privacidad e intimidad de la Constitución Política del Estado Plurinacional de Bolivia (art. 21) y a la acción de protección de privacidad (art. 130), a la Ley N.º 164 de Bolivia, General de Telecomunicaciones y Tecnologías de Información y Comunicación, y a su reglamento, el Decreto Supremo N.º 1793 de Bolivia. Si resides en la Unión Europea, también te informamos conforme al Reglamento General de Protección de Datos (RGPD): la base del tratamiento es tu consentimiento (art. 6.1.a), que puedes retirar en cualquier momento.</p>

<h4>5. Con quién compartimos tu información</h4>
<ul>
    <li><strong>Etreva Travel:</strong> solo el personal autorizado (administración y soporte), para los fines del punto 3.</li>
    <li><strong>Otros usuarios:</strong> solo si lo autorizas, ven tu nombre junto a tus reseñas y calificaciones. Nunca ven tu correo, teléfono, fecha de nacimiento ni tu historial.</li>
    <li><strong>Servicios técnicos:</strong> envío de correos, mapas e inicio de sesión con Google, solo con los datos necesarios para funcionar.</li>
    <li><strong>Autoridades:</strong> solo por orden judicial o requerimiento legal.</li>
</ul>
<p><strong>No vendemos ni alquilamos tus datos personales.</strong></p>

<h4>6. Tus derechos</h4>
<p>Puedes <strong>acceder</strong> a tus datos, <strong>rectificarlos</strong> desde "Mi Perfil", pedir su <strong>eliminación</strong>, <strong>oponerte</strong> a un tratamiento o <strong>retirar tu consentimiento</strong> escribiendo a Soporte. Respondemos en un plazo máximo de 15 días hábiles. Retirar el consentimiento implica que no podremos seguir personalizando recomendaciones. Conservamos tus datos mientras tu cuenta esté activa; al eliminarla se borran o se anonimizan, salvo lo que la ley obligue a conservar.</p>

<h4>7. Menores de edad</h4>
<p>Conforme a la Ley N.º 548 de Bolivia (Código Niña, Niño y Adolescente), si tienes <strong>menos de 18 años</strong> necesitas la <strong>autorización de tu madre, padre o tutor</strong> para usar Etreva Travel y para que tratemos tus datos.</p>
<p>Por la Ley N.º 259 de Bolivia, de Control al Expendio y Consumo de Bebidas Alcohólicas, y por razones de madurez y seguridad, <strong>a los menores de 18 años no se les muestran bares, discotecas ni locales cuyo fin principal es el consumo de alcohol</strong>: no aparecen en búsquedas, recomendaciones, favoritos ni itinerarios, y no se pueden guardar. Estos lugares se habilitan automáticamente al cumplir <strong>18 años</strong>, según la fecha de nacimiento registrada. Declarar una fecha falsa es responsabilidad del usuario y de su tutor.</p>

<h4>8. Actividades de aventura y riesgo</h4>
<p>Algunas atracciones implican <strong>riesgos para la salud y la integridad física</strong>: montañismo y ascensos de alta montaña (Huayna Potosí, Illimani, Chacaltaya, Condoriri), descenso en bicicleta o <em>downhill</em> (Camino de la Muerte y otras rutas) y trekking en altura. Los riesgos incluyen mal de altura, hipotermia, caídas, accidentes de tránsito y cambios bruscos de clima.</p>
<ul>
    <li><strong>Etreva Travel informa y recomienda; no es operador turístico</strong> ni presta, vende o supervisa estas actividades.</li>
    <li>Contrata solo <strong>operadores y guías registrados ante el Viceministerio de Turismo de Bolivia</strong> (Ley N.º 292 de Bolivia, General de Turismo) y exige equipo de seguridad y un seguro que cubra la actividad.</li>
    <li>Evalúa tu condición física, aclimátate a la altura y consulta a un médico si tienes dudas.</li>
    <li>Conforme a la Ley N.º 453 de Bolivia, de los Derechos de las Usuarias y los Usuarios y de las Consumidoras y los Consumidores, te damos esta información para que decidas de forma informada. La decisión de practicar la actividad, y su contratación, son tuyas y del operador que elijas.</li>
</ul>

<h4>9. Reseñas y comentarios</h4>
<p>Puedes publicar <strong>una reseña por atracción</strong>, que puedes editar. No se publican reseñas repetidas o copiadas de otras, ni contenido ofensivo, publicitario o falso. Etreva puede ocultar las que no cumplan estas reglas.</p>

<h4>10. Cambios en este reglamento</h4>
<p>Si cambiamos este reglamento publicaremos una nueva versión y te pediremos aceptarla de nuevo antes de seguir usando el sistema.</p>
</div>
