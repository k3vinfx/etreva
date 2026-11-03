<?php
/*
 * herramientas/generar_datos_prueba.php  (solo línea de comandos)
 * Crea usuarios de prueba con actividad realista: 2 administradores, 2 de finanzas y
 * 110 turistas de distintos países, edades, idiomas y estados de suscripción, con
 * itinerarios, visitas calificadas, reseñas y favoritos.
 *
 * Todos quedan marcados con configuracion_json.datos_prueba = true (así se reconocen y se borran).
 * Los correos parecen reales pero son inventados: no envíes correos a estas cuentas.
 * Volver a correrlo borra los de prueba anteriores y los recrea (las cuentas reales no se tocan).
 *
 *   php herramientas/generar_datos_prueba.php           → genera y escribe herramientas/credenciales_prueba.csv
 *   php herramientas/generar_datos_prueba.php --borrar  → solo borra los datos de prueba
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
chdir(__DIR__ . '/..');
require 'model/database.php';

mt_srand(20261102); // mismos datos en cada corrida
$pdo = Database::Conectar();
$tz  = new DateTimeZone('America/La_Paz');
$hoy = new DateTimeImmutable('now', $tz);

function azar(array $a) { return $a[mt_rand(0, count($a) - 1)]; }
function prob(float $p): bool { return mt_rand() / mt_getrandmax() < $p; }
function fecha(DateTimeImmutable $d): string { return $d->format('Y-m-d H:i:s'); }
function quitarTildes(string $s): string {
    return strtolower(preg_replace('/[^a-z]/i', '', strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u','ç'=>'c','ã'=>'a','ô'=>'o','ê'=>'e','à'=>'a','è'=>'e','ö'=>'o','ä'=>'a','ß'=>'ss','Á'=>'a','É'=>'e','Ó'=>'o','Ú'=>'u','Í'=>'i'])));
}

// ── Borrar datos de prueba anteriores (itinerarios, reseñas, soporte caen por CASCADE) ──
$borrados = $pdo->exec("DELETE FROM usuarios.usuarios WHERE (configuracion_json->>'datos_prueba') = 'true'");
echo "Borrados $borrados usuarios de prueba anteriores\n";
if (in_array('--borrar', $argv, true)) exit;

// ── Catálogo de atracciones con su puntuación base (sesga las calificaciones) ──
$atr = [];
foreach ($pdo->query("SELECT configuracion_json->'alternativas' alt, id modelo, nombre, version FROM recomendaciones.modelos_mr") as $m) {
    foreach (json_decode($m['alt'], true) ?: [] as $a) {
        $atr[(int)$a['id']] = ['id' => (int)$a['id'], 'nombre' => $a['nombre'], 'cat' => intdiv((int)$a['id'], 1000),
            'puntuacion' => (float)($a['puntuacion'] ?? 4.2), 'modelo' => (int)$m['modelo'],
            'modelo_nombre' => $m['nombre'], 'modelo_version' => $m['version'] ?: '1.0.0'];
    }
}
$existentes = $pdo->query("SELECT id FROM catalogo.atracciones")->fetchAll(PDO::FETCH_COLUMN);
$atr = array_intersect_key($atr, array_flip(array_map('intval', $existentes)));
$porCat = [];
foreach ($atr as $a) $porCat[$a['cat']][] = $a;
// Algunas atracciones "de moda": concentran más visitas para que haya rankings claros
$populares = array_rand($atr, 40);

// ── Países: nacionalidad, idioma, nombres, prefijo telefónico ──
$paises = [
    'Bolivia'        => ['es', 30, '+591 7', ['Marcos','Lucía','Jorge','Valeria','Diego','Camila','Rodrigo','Daniela','Iván','Paola','Álvaro','Gabriela','Sergio','Fernanda','Wilson','Ximena'], ['Mamani','Quispe','Choque','Flores','Rojas','Gutiérrez','Vargas','Condori','Morales','Arce','Paz','Salinas']],
    'Argentina'      => ['es', 8, '+54 9 11 ', ['Martín','Sofía','Facundo','Agustina','Nicolás','Julieta','Tomás','Florencia'], ['González','Fernández','López','Romero','Sosa','Benítez','Acosta']],
    'Chile'          => ['es', 6, '+56 9 ', ['Matías','Antonia','Benjamín','Javiera','Cristóbal','Constanza'], ['Muñoz','Soto','Contreras','Silva','Pizarro','Espinoza']],
    'Perú'           => ['es', 7, '+51 9', ['Luis','Rosa','Carlos','Milagros','Renzo','Andrea','Piero'], ['Huamán','Ramírez','Torres','Castillo','Mendoza','Chávez']],
    'México'         => ['es', 6, '+52 55 ', ['Alejandro','Ximena','Emiliano','Regina','Santiago','Valentina'], ['Hernández','García','Martínez','Ruiz','Reyes','Cruz']],
    'Colombia'       => ['es', 5, '+57 3', ['Juan Pablo','Mariana','Andrés','Natalia','Felipe'], ['Restrepo','Gómez','Ospina','Cárdenas','Valencia']],
    'España'         => ['es', 6, '+34 6', ['Pablo','Lucía','Javier','Marta','Álvaro','Carmen'], ['García','Navarro','Ortega','Serrano','Molina','Delgado']],
    'Estados Unidos' => ['en', 9, '+1 415 ', ['Michael','Emily','Jacob','Olivia','Ethan','Madison','Ryan','Hannah'], ['Smith','Johnson','Miller','Davis','Wilson','Anderson','Taylor']],
    'Reino Unido'    => ['en', 4, '+44 7', ['Oliver','Amelia','Harry','Isla'], ['Brown','Clarke','Walker','Wright','Hughes']],
    'Canadá'         => ['en', 3, '+1 604 ', ['Liam','Chloe','Noah'], ['Tremblay','Campbell','Martin','Roy']],
    'Australia'      => ['en', 3, '+61 4', ['Jack','Mia','Lachlan'], ['Kelly','Murphy','Mitchell','Cooper']],
    'Brasil'         => ['pt', 8, '+55 11 9', ['João','Ana Clara','Gabriel','Beatriz','Lucas','Larissa','Rafael','Juliana'], ['Silva','Santos','Oliveira','Souza','Pereira','Costa','Almeida']],
    'Portugal'       => ['pt', 2, '+351 9', ['Tiago','Inês'], ['Ferreira','Rodrigues','Carvalho']],
    'Francia'        => ['fr', 5, '+33 6 ', ['Louis','Camille','Hugo','Léa','Arthur'], ['Martin','Bernard','Dubois','Lefèvre','Moreau']],
    'Bélgica'        => ['fr', 1, '+32 4', ['Mathis'], ['Peeters','Janssens']],
    'Alemania'       => ['de', 5, '+49 15', ['Lukas','Hannah','Felix','Lena','Jonas'], ['Müller','Schmidt','Schneider','Fischer','Weber']],
    'Suiza'          => ['de', 1, '+41 7', ['Noah'], ['Keller','Meier']],
    'Austria'        => ['de', 1, '+43 6', ['Elias'], ['Gruber','Huber']],
];
$femeninos = '/^(Lucía|Valeria|Camila|Daniela|Paola|Gabriela|Fernanda|Ximena|Sofía|Agustina|Julieta|Florencia|Antonia|Javiera|Constanza|Rosa|Milagros|Andrea|Regina|Valentina|Mariana|Natalia|Marta|Carmen|Emily|Olivia|Madison|Hannah|Amelia|Isla|Chloe|Mia|Ana Clara|Beatriz|Larissa|Juliana|Inês|Camille|Léa|Lena)$/u';

// ── Textos de reseña por categoría, idioma y sentimiento ──
$resenas = [
 'es' => [
  'pos' => [
   1 => ['Caminata preciosa, paisajes increíbles y bien señalizada.', 'Ideal para aclimatarse a la altura, volvería sin dudar.', 'Las formaciones son impresionantes, lleven agua y protector solar.'],
   2 => ['Experiencia de otro nivel, el guía fue muy profesional.', 'Llegar a la cumbre fue lo mejor del viaje, vale cada esfuerzo.', 'Equipo en buen estado y una organización impecable.'],
   3 => ['Muy bien conservado, se aprende muchísimo de la historia paceña.', 'Imperdible si te interesa la cultura andina.', 'La guía explicó todo con mucha pasión, súper recomendado.'],
   4 => ['Adrenalina pura, la bajada es espectacular.', 'Bicis en buen estado y los guías muy atentos a la seguridad.', 'Una de las mejores rutas que he hecho en mi vida.'],
   5 => ['Excelente ambiente y buena música, volvería el fin de semana.', 'Los tragos muy buenos y el personal amable.', 'Gran lugar para salir con amigos, se llena pero vale la pena.'],
   6 => ['La comida deliciosa y las porciones generosas.', 'Probé platos típicos buenísimos, la atención fue rápida.', 'Relación calidad precio excelente, volveré.'],
   7 => ['Un lugar con mucho encanto, ideal para fotos.', 'Me encantó recorrerlo con calma, tiene mucha vida.', 'De lo más bonito de La Paz, no se lo pierdan.'],
  ],
  'neu' => ['Estuvo bien, aunque esperaba un poco más.', 'Buena experiencia en general, pero algo cara.', 'Correcto, aunque había demasiada gente.', 'Vale la pena una vez, no sé si volvería.'],
  'neg' => ['No cumplió mis expectativas, la atención fue lenta.', 'Muy caro para lo que ofrece.', 'Estaba cerrado en el horario indicado, una pena.', 'Mal organizado, tuvimos que esperar mucho.'],
 ],
 'en' => [
  'pos' => [
   1 => ['Beautiful hike with stunning views, well marked trail.', 'Great way to acclimatize to the altitude.', 'The rock formations are amazing, bring water and sunscreen.'],
   2 => ['Once in a lifetime experience, our guide was very professional.', 'Reaching the summit was the highlight of my trip.', 'Gear was in great condition and the organization flawless.'],
   3 => ['Very well preserved, you learn a lot about local history.', 'A must if you are into Andean culture.', 'Our guide was passionate and knowledgeable, highly recommended.'],
   4 => ['Pure adrenaline, the descent is spectacular.', 'Bikes in great shape and the guides really care about safety.', 'One of the best rides I have ever done.'],
   5 => ['Great vibe and good music, would come back on the weekend.', 'Drinks were excellent and the staff friendly.', 'Fun place to go out with friends, gets busy but worth it.'],
   6 => ['Delicious food and generous portions.', 'Tried some amazing local dishes, quick service.', 'Excellent value for money, will be back.'],
   7 => ['Charming spot, perfect for photos.', 'Loved walking around slowly, full of life.', 'One of the prettiest places in La Paz, do not miss it.'],
  ],
  'neu' => ['It was fine, but I expected a bit more.', 'Nice overall, but a little pricey.', 'Decent, though it was too crowded.', 'Worth it once, not sure I would go back.'],
  'neg' => ['Did not meet my expectations, slow service.', 'Too expensive for what it offers.', 'It was closed during the listed hours, disappointing.', 'Poorly organized, we waited a long time.'],
 ],
 'pt' => [
  'pos' => [1 => ['Trilha linda, paisagens incríveis.', 'Ótima para se aclimatar à altitude.'], 2 => ['Experiência inesquecível, guia muito profissional.', 'Chegar ao cume foi o melhor da viagem.'],
   3 => ['Muito bem conservado, aprende-se muito da história.', 'Imperdível para quem gosta de cultura andina.'], 4 => ['Adrenalina pura, a descida é espetacular.', 'Bicicletas em ótimo estado e guias atentos.'],
   5 => ['Ambiente ótimo e boa música.', 'Drinks muito bons e equipe simpática.'], 6 => ['Comida deliciosa e porções generosas.', 'Pratos típicos maravilhosos, atendimento rápido.'],
   7 => ['Lugar cheio de charme, ótimo para fotos.', 'Um dos lugares mais bonitos de La Paz.']],
  'neu' => ['Foi bom, mas esperava um pouco mais.', 'Boa experiência, porém um pouco cara.'],
  'neg' => ['Não atendeu às expectativas, atendimento lento.', 'Caro demais para o que oferece.'],
 ],
 'fr' => [
  'pos' => [1 => ['Randonnée magnifique, paysages incroyables.', 'Idéal pour s\'acclimater à l\'altitude.'], 2 => ['Expérience inoubliable, guide très professionnel.', 'Atteindre le sommet a été le meilleur moment du voyage.'],
   3 => ['Très bien conservé, on apprend beaucoup sur l\'histoire.', 'Incontournable pour la culture andine.'], 4 => ['Adrénaline pure, la descente est spectaculaire.', 'Vélos en bon état et guides attentifs.'],
   5 => ['Super ambiance et bonne musique.', 'Cocktails excellents et personnel sympathique.'], 6 => ['Cuisine délicieuse et portions généreuses.', 'Plats typiques excellents, service rapide.'],
   7 => ['Endroit plein de charme, parfait pour les photos.', 'Un des plus beaux endroits de La Paz.']],
  'neu' => ['C\'était bien, mais je m\'attendais à mieux.', 'Bonne expérience, un peu chère.'],
  'neg' => ['Décevant, service très lent.', 'Trop cher pour ce que c\'est.'],
 ],
 'de' => [
  'pos' => [1 => ['Wunderschöne Wanderung mit tollen Ausblicken.', 'Perfekt zum Akklimatisieren an die Höhe.'], 2 => ['Unvergessliches Erlebnis, sehr professioneller Guide.', 'Der Gipfel war das Highlight der Reise.'],
   3 => ['Sehr gut erhalten, man lernt viel über die Geschichte.', 'Ein Muss für Fans der Andenkultur.'], 4 => ['Pures Adrenalin, die Abfahrt ist spektakulär.', 'Räder in gutem Zustand, Guides achten auf Sicherheit.'],
   5 => ['Tolle Stimmung und gute Musik.', 'Sehr gute Drinks und freundliches Personal.'], 6 => ['Leckeres Essen und große Portionen.', 'Typische Gerichte, schneller Service.'],
   7 => ['Charmanter Ort, ideal für Fotos.', 'Einer der schönsten Orte in La Paz.']],
  'neu' => ['War okay, hatte aber etwas mehr erwartet.', 'Gute Erfahrung, aber etwas teuer.'],
  'neg' => ['Enttäuschend, sehr langsamer Service.', 'Zu teuer für das Angebot.'],
 ],
];
$tagsPos = ['buen_ambiente','buena_atencion','buena_comida','buen_precio','vistas_increibles','seguro','recomendable'];
$tagsNeg = ['caro','lento','lleno','mala_atencion','cerrado'];

// ── Planes ──
$planes = [
    ['Explorador Urbano', 'explorador_urbano', ['weekly' => 8,  'monthly' => 10, 'yearly' => 40]],
    ['Aventura Andina',   'aventura_andina',   ['weekly' => 10, 'monthly' => 20, 'yearly' => 60]],
    ['Premium Paceño',    'premium_paceno',    ['weekly' => 15, 'monthly' => 30, 'yearly' => 90]],
];
$diasPeriodo = ['weekly' => 7, 'monthly' => 30, 'yearly' => 365];

// Reparto de casos de suscripción entre los 110 turistas
$casos = array_merge(
    array_fill(0, 52, 'activo'),          // plan vigente
    array_fill(0, 6,  'por_vencer'),      // vence en 1-2 días
    array_fill(0, 7,  'gracia'),          // venció hace 1-3 días
    array_fill(0, 9,  'vencido'),         // bloqueado, debe renovar
    array_fill(0, 14, 'pendiente_pago'),  // subió comprobante, espera aprobación (< 72 h)
    array_fill(0, 4,  'pendiente_viejo'), // pendiente hace más de 72 h
    array_fill(0, 5,  'rechazado'),
    array_fill(0, 8,  'pendiente_nuevo'), // se registró y nunca pagó
    array_fill(0, 5,  'sin_plan')
);
shuffle($casos);

// Comprobante de ejemplo para los pagos (imagen generada)
$comprobante = 'uploads/comprobantes/comprobante_prueba.png';
if (!is_file($comprobante) && function_exists('imagecreatetruecolor')) {
    $im = imagecreatetruecolor(600, 800);
    imagefill($im, 0, 0, imagecolorallocate($im, 250, 250, 250));
    $n = imagecolorallocate($im, 30, 30, 30);
    foreach (['COMPROBANTE DE PAGO', 'Transferencia QR - Banco de prueba', 'Etreva Travel', 'DATO DE PRUEBA'] as $i => $t)
        imagestring($im, 5, 60, 120 + $i * 60, $t, $n);
    imagepng($im, $comprobante);
    imagedestroy($im);
}

$insUsuario = $pdo->prepare("INSERT INTO usuarios.usuarios
    (email, password_hash, nombre, apellido, fecha_nacimiento, genero, nacionalidad, telefono, rol, estado,
     configuracion_json, historial_json, fecha_registro, created_at, updated_at)
    VALUES (?,?,?,?,?,?,?,?,?,?,?::jsonb,?::jsonb,?,?,?) RETURNING id");
$insPref = $pdo->prepare("INSERT INTO usuarios.preferencias (usuario_id, motivo_viaje, presupuesto_max, duracion_dias, intereses_json, created_at, updated_at) VALUES (?,?,?,?,?::jsonb,?,?)");
$insItin = $pdo->prepare("INSERT INTO itinerarios.itinerarios (usuario_id, nombre, descripcion, atracciones_id, fecha_visita, estado, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?) RETURNING id");
$insBt   = $pdo->prepare("INSERT INTO itinerarios.back_testing_mr (itinerario_id, atraccion_id, calificacion_usuario, comentario_usuario, modelo_mr_fk, nueva_metricas_json_reajuste, estado, created_at, updated_at) VALUES (?,?,?,?,?::jsonb,?::jsonb,?,?,?)");
$insSop  = $pdo->prepare("INSERT INTO usuarios.solicitudes_soporte (usuario_id, tipo, mensaje, estado, respuesta, atendida_por, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?)");

$credenciales = [];
$correosUsados = [];
$J = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

function correo(string $nombre, string $apellido, array &$usados, string $pais = '', bool $interno = false): string {
    $n = quitarTildes(explode(' ', $nombre)[0]); $a = quitarTildes($apellido);
    if ($interno) {
        $local = $n[0] . $a; $dominio = 'etrevatravel.com';
    } else {
        // Formatos y proveedores habituales de cada país, con números para que parezcan reales
        $formatos = ["$n.$a", "$n$a", "{$n}_$a", $n[0] . $a, "$a.$n", "$n.$a" . mt_rand(80, 99), $n . mt_rand(10, 99), "$n.{$a}" . mt_rand(1, 9) . mt_rand(0, 9)];
        $dominios = [
            'Brasil' => ['gmail.com', 'hotmail.com', 'uol.com.br', 'outlook.com', 'yahoo.com.br'],
            'Portugal' => ['gmail.com', 'sapo.pt'], 'Francia' => ['gmail.com', 'orange.fr', 'free.fr', 'hotmail.fr'],
            'Bélgica' => ['gmail.com', 'skynet.be'], 'Alemania' => ['gmail.com', 'gmx.de', 'web.de', 't-online.de'],
            'Suiza' => ['gmail.com', 'bluewin.ch'], 'Austria' => ['gmail.com', 'gmx.at'],
            'Estados Unidos' => ['gmail.com', 'yahoo.com', 'outlook.com', 'icloud.com'], 'Reino Unido' => ['gmail.com', 'outlook.com', 'btinternet.com'],
            'Canadá' => ['gmail.com', 'outlook.com'], 'Australia' => ['gmail.com', 'bigpond.com'],
            'España' => ['gmail.com', 'hotmail.es', 'yahoo.es'], 'México' => ['gmail.com', 'hotmail.com', 'outlook.com'],
            'Argentina' => ['gmail.com', 'hotmail.com', 'yahoo.com.ar'],
        ];
        $local = azar($formatos);
        $dominio = azar($dominios[$pais] ?? ['gmail.com', 'gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com']);
    }
    $c = "$local@$dominio"; $i = 2;
    while (isset($usados[$c])) $c = $local . $i++ . "@$dominio";
    $usados[$c] = true;
    return $c;
}
function clave(string $nombre): string {
    return ucfirst(substr(quitarTildes($nombre), 0, 5)) . '#' . mt_rand(1000, 9999);
}

$pdo->beginTransaction();
try {
    // ── Personal interno: 2 administradores (rol 2) y 2 de finanzas (rol 3) ──
    $internos = [
        [2, 'Andrea',  'Villarroel', 'F', 'Administración', 'Coordinadora de operaciones y contenido'],
        [2, 'Ricardo', 'Pinto',      'M', 'Administración', 'Administrador de usuarios y soporte'],
        [3, 'Carla',   'Ibáñez',     'F', 'Finanzas',       'Responsable de validación de pagos'],
        [3, 'Hernán',  'Saavedra',   'M', 'Finanzas',       'Analista financiero y reportes'],
    ];
    $idsAdmin = [];
    foreach ($internos as $k => [$rol, $nom, $ape, $g, $depto, $desc]) {
        $email = correo($nom, $ape, $correosUsados, '', true);
        $pass  = clave($nom);
        $alta  = $hoy->modify('-' . (240 - $k * 10) . ' days')->setTime(9, 0);
        $cfg = ['tema' => $k % 2 ? 'claro' : 'oscuro', 'dark_mode' => !($k % 2), 'idioma' => 'es', 'datos_prueba' => true,
            'perfil' => ['avatar' => 'preset_' . (($k * 3) % 12), 'descripcion' => $desc, 'departamento' => $depto],
            'ultimo_acceso' => fecha($hoy->modify('-' . mt_rand(0, 48) . ' hours')), 'permisos_extra' => [],
            'restauracion_cuenta' => null,
            'token_verificacion_correo' => ['token' => null, 'estado' => 'verificado', 'fecha_creacion' => fecha($alta), 'fecha_verificacion' => fecha($alta)]];
        $hist = [['fecha' => fecha($alta), 'evento' => 'creacion', 'descripcion' => "Cuenta de $depto creada (datos de prueba)", 'por_admin_id' => null]];
        $insUsuario->execute([$email, password_hash($pass, PASSWORD_DEFAULT), $nom, $ape,
            sprintf('%d-%02d-%02d', 1980 + $k * 4, mt_rand(1, 12), mt_rand(1, 28)), $g === 'F' ? 'Femenino' : 'Masculino',
            'Bolivia', '+591 7' . mt_rand(1000000, 9999999), $rol, 'activo', json_encode($cfg, $J), json_encode($hist, $J),
            fecha($alta), fecha($alta), fecha($alta)]);
        $id = (int)$insUsuario->fetchColumn();
        if ($rol === 2) $idsAdmin[] = $id;
        $idFinanzas[] = $id;
        $credenciales[] = [$rol === 2 ? 'Administrador' : 'Finanzas', "$nom $ape", $email, $pass, 'Bolivia', '', '', 'Personal interno'];
    }
    $aprobadores = array_slice($idFinanzas, 2) ?: $idsAdmin;

    // ── 110 turistas ──
    $lista = [];
    foreach ($paises as $pais => [$lang, $cant]) for ($i = 0; $i < $cant; $i++) $lista[] = $pais;
    $lista = array_slice($lista, 0, 110);
    shuffle($lista);

    $motivos = ['aventura','cultura','gastronomia','vida_nocturna','descanso','negocios','fiesta'];
    $companias = ['solo','pareja','familia','amigos','grupo'];
    $experiencias = ['aventura_naturaleza','cultura_tradiciones','gastronomia_local','vida_nocturna_ocio','fotografia_paisajes'];
    $actividades = ['trekking','montanismo','downhill','museos','gastronomia','vida_nocturna','fotografia'];
    $presupuestos = ['bajo','medio','medio','alto','premium'];
    // Afinidad motivo → categorías que más visita
    $afinidad = ['aventura' => [1,2,4,1,7], 'cultura' => [3,3,7,6], 'gastronomia' => [6,6,6,7,5], 'vida_nocturna' => [5,5,6,7],
                 'descanso' => [7,3,6,1], 'negocios' => [6,7,3,5], 'fiesta' => [5,5,6,7]];

    $tot = ['itinerarios' => 0, 'resenas' => 0, 'favoritos' => 0, 'pendientes' => 0, 'soporte' => 0];
    foreach ($lista as $n => $pais) {
        [$lang, , $pref, $nombres, $apellidos] = $paises[$pais];
        $nom = azar($nombres); $ape = azar($apellidos);
        $genero = preg_match($femeninos, $nom) ? 'Femenino' : 'Masculino';
        if (prob(0.03)) $genero = 'Otro';
        $email = correo($nom, $ape, $correosUsados, $pais);
        $pass  = clave($nom);

        // Edad: mayoría 20-40, pero hay jóvenes de 18 y mayores de 70
        $edad = azar([18, 19, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 38, 40, 42, 45, 48, 52, 55, 58, 61, 64, 68, 72]);
        $nac  = $hoy->modify("-$edad years")->modify('-' . mt_rand(0, 360) . ' days');
        $rango = $edad <= 25 ? '18_25' : ($edad <= 35 ? '26_35' : ($edad <= 50 ? '36_50' : 'mayor_50'));

        $caso  = $casos[$n];
        $antig = in_array($caso, ['pendiente_nuevo', 'sin_plan', 'pendiente_pago'], true) ? mt_rand(1, 40) : mt_rand(20, 240);
        $alta  = $hoy->modify("-$antig days")->setTime(mt_rand(7, 22), mt_rand(0, 59));
        $motivo = azar($motivos);
        $compania = $edad >= 55 ? azar(['pareja','familia','grupo']) : azar($companias);

        $intereses = ['compania' => $compania, 'rango_edad' => $rango, 'edad_exacta' => $edad,
            'actividades' => array_values(array_unique([azar($actividades), azar($actividades)])),
            'experiencias' => array_values(array_unique([azar($experiencias), azar($experiencias), azar($experiencias)])),
            'motivo_viaje' => $motivo,
            'preferencias_gastronomicas' => ['nivel_precio' => azar(['', 'bajo', 'medio', 'alto']), 'tipos_excluidos' => [], 'tipos_favoritos' => prob(.5) ? [azar(['tipica','vegetariana','internacional','cafeteria'])] : []],
            'preferencias_vida_nocturna' => ['ambiente' => [], 'intensidad' => azar(['', 'tranquila', 'moderada', 'intensa']), 'generos_excluidos' => [], 'generos_favoritos' => []]];
        $duracion = azar([2, 3, 3, 4, 5, 7, 10, 14]);
        $presu = azar($presupuestos);

        // ── Suscripción (registro_pago) e historial de pagos anteriores ──
        $hist = [['fecha' => fecha($alta), 'evento' => 'creacion', 'descripcion' => 'Registro de cuenta (datos de prueba)', 'por_admin_id' => null]];
        $rp = null;
        if (!in_array($caso, ['sin_plan', 'pendiente_nuevo'], true)) {
            [$planNom, $planSlug, $precios] = azar($planes);
            $periodo = azar(['weekly', 'monthly', 'monthly', 'monthly', 'yearly']);
            $dias = $diasPeriodo[$periodo];
            // Inicio del plan actual según el caso
            switch ($caso) {
                case 'activo':          $inicio = $hoy->modify('-' . mt_rand(0, max(0, $dias - 3)) . ' days'); break;
                case 'por_vencer':      $inicio = $hoy->modify('-' . ($dias - mt_rand(1, 2)) . ' days'); break;
                case 'gracia':          $inicio = $hoy->modify('-' . ($dias + mt_rand(1, 3)) . ' days')->modify('+2 hours'); break;
                case 'vencido':         $inicio = $hoy->modify('-' . ($dias + mt_rand(6, 40)) . ' days'); break;
                case 'pendiente_viejo': $inicio = $hoy->modify('-' . mt_rand(4, 9) . ' days'); break;
                default:                $inicio = $hoy->modify('-' . mt_rand(1, 60) . ' hours');
            }
            if ($inicio < $alta) $alta = $inicio->modify('-' . mt_rand(0, 3) . ' days');

            // Renovaciones previas archivadas (alimentan los ingresos históricos de Finanzas)
            $prev = $inicio; $renov = 0;
            while ($renov < 5 && prob(0.45)) {
                $prev = $prev->modify("-$dias days");
                if ($prev < $alta) break;
                $hist[] = ['estado' => 'activo_pagado', 'monto' => $precios[$periodo], 'tipo_plan' => $planNom, 'periodo' => $periodo,
                    'verificado' => true, 'fecha_pago' => fecha($prev), 'fecha_aprobacion' => fecha($prev->modify('+' . mt_rand(1, 20) . ' hours')),
                    'fecha_vencimiento' => fecha($prev->modify("+$dias days")), 'aprobado_por' => azar($aprobadores),
                    'identificador_plan' => "{$planSlug}_{$precios[$periodo]}_{$periodo}", '_archivado_en' => fecha($prev->modify("+$dias days"))];
                $renov++;
            }

            $estadoPago = ['activo' => 'activo_pagado', 'por_vencer' => 'activo_pagado', 'gracia' => 'activo_pagado', 'vencido' => 'activo_pagado',
                           'pendiente_pago' => 'pendiente_pago', 'pendiente_viejo' => 'pendiente_pago', 'rechazado' => 'rechazado'][$caso];
            $rp = ['monto' => $precios[$periodo], 'estado' => $estadoPago, 'periodo' => $periodo, 'dias_plan' => $dias, 'tipo_plan' => $planNom,
                'fecha_pago' => fecha($inicio), 'motivo_pago' => $renov ? 'renovacion' : 'nuevo', 'dias_totales' => $dias, 'dias_carryover' => 0,
                'ruta_comprobante' => $comprobante, 'fecha_vencimiento' => fecha($inicio->modify("+$dias days")),
                'identificador_plan' => "{$planSlug}_{$precios[$periodo]}_{$periodo}", 'ultima_actualizacion' => fecha($inicio),
                'verificado' => $estadoPago === 'activo_pagado'];
            if ($estadoPago === 'activo_pagado') {
                $aprob = $inicio->modify('+' . mt_rand(1, 30) . ' hours');
                $rp += ['fecha_aprobacion' => fecha($aprob), 'aprobado_por' => azar($aprobadores)];
                $hist[] = ['fecha' => fecha($aprob), 'evento' => 'pago_aprobado', 'descripcion' => "Pago aprobado: $planNom ($periodo)", 'por_admin_id' => $rp['aprobado_por']];
            } elseif ($estadoPago === 'rechazado') {
                $rp += ['motivo_rechazo' => azar(['Comprobante ilegible', 'El monto no coincide con el plan', 'Transferencia no encontrada']),
                        'fecha_rechazo' => fecha($inicio->modify('+5 hours')), 'rechazado_por' => azar($aprobadores)];
            }
        } elseif ($caso === 'pendiente_nuevo') {
            $rp = ['estado' => 'pendiente_nuevo', 'ultima_actualizacion' => fecha($alta)];
        }

        $origen = prob(0.3) ? 'google' : 'normal';
        $cfg = ['tema' => prob(.6) ? 'oscuro' : 'claro', 'idioma' => $lang, 'animaciones' => true, 'notificaciones' => prob(.8),
            'formato_fecha' => $lang === 'en' ? 'mm/dd/yyyy' : 'dd/mm/yyyy', 'menu_compacto' => prob(.15),
            'avatar' => prob(.85) ? 'preset_' . mt_rand(0, 11) : null, 'contacto_publico' => prob(.55),
            'origen_registro' => $origen, 'datos_prueba' => true,
            'ultimo_acceso' => fecha($hoy->modify('-' . mt_rand(0, 24 * 20) . ' hours')),
            'preferencias' => ['interese' => true, 'intereses' => $intereses, 'duracion_dia' => null, 'motivo_viaje' => $motivo,
                'duracion_dias' => $duracion, 'zona_preferida' => azar([null, 'Centro', 'Sopocachi', 'Zona Sur', 'Miraflores']),
                'presupuesto_max' => $presu, 'idioma_preferido' => $lang],
            'token_verificacion_correo' => ['token' => null, 'estado' => 'verificado', 'fecha_creacion' => fecha($alta), 'fecha_verificacion' => fecha($alta)]];
        $cfg['dark_mode'] = $cfg['tema'] === 'oscuro';
        if ($rp) $cfg['registro_pago'] = $rp;

        // Unos pocos con correo sin verificar o cuenta bloqueada por un administrador
        $estadoCuenta = 'activo';
        if ($caso === 'sin_plan' && prob(.4)) {
            $estadoCuenta = 'pendiente_correo';
            $cfg['token_verificacion_correo'] = ['token' => bin2hex(random_bytes(16)), 'estado' => 'pendiente', 'fecha_creacion' => fecha($alta), 'fecha_verificacion' => null];
        }
        if ($caso === 'vencido' && prob(.25)) {
            $estadoCuenta = 'bloqueado';
            $hist[] = ['fecha' => fecha($hoy->modify('-2 days')), 'evento' => 'bloqueo', 'descripcion' => 'Bloqueo de prueba por plan vencido', 'por_admin_id' => $idsAdmin[0]];
        }

        $insUsuario->execute([$email, password_hash($pass, PASSWORD_DEFAULT), $nom, $ape, $nac->format('Y-m-d'), $genero, $pais,
            $pref . mt_rand(1000000, 9999999), 1, $estadoCuenta, json_encode($cfg, $J), json_encode($hist, $J),
            fecha($alta), fecha($alta), fecha($alta)]);
        $uid = (int)$insUsuario->fetchColumn();
        $insPref->execute([$uid, $motivo, $presu, $duracion, json_encode($intereses, $J), fecha($alta), fecha($alta)]);

        // ── Actividad: solo quien llegó a usar la plataforma (pagó alguna vez) ──
        $activo = in_array($caso, ['activo', 'por_vencer', 'gracia', 'vencido', 'pendiente_viejo', 'pendiente_pago', 'rechazado'], true);
        $nVisitas = $activo ? azar([0, 1, 2, 3, 3, 4, 5, 6, 7, 8, 10, 12, 15, 18]) : 0;
        if (in_array($caso, ['pendiente_pago', 'rechazado'], true)) $nVisitas = min($nVisitas, 2);
        $nPend = $activo ? mt_rand(0, 4) : 0;
        $nFav  = $activo ? mt_rand(0, 5) : 0;

        $vistos = [];
        $elegir = function () use ($afinidad, $motivo, $porCat, $populares, $atr, &$vistos) {
            for ($t = 0; $t < 20; $t++) {
                $a = prob(.3) ? $atr[azar($populares)] : azar($porCat[azar($afinidad[$motivo])]);
                if (!isset($vistos[$a['id']])) { $vistos[$a['id']] = true; return $a; }
            }
            return null;
        };
        $desdeAct = max($alta, $hoy->modify('-200 days'));
        $spanDias = max(1, (int)$desdeAct->diff($hoy)->days);

        for ($v = 0; $v < $nVisitas + $nPend + $nFav; $v++) {
            $a = $elegir();
            if (!$a) break;
            if ($v < $nVisitas) {
                $estado = 'visitado';
                $fv = $desdeAct->modify('+' . mt_rand(0, $spanDias) . ' days')->setTime(mt_rand(8, 22), azar([0, 15, 30, 45]));
                if ($fv > $hoy) $fv = $hoy->modify('-3 hours');
            } elseif ($v < $nVisitas + $nPend) {
                $estado = 'pendiente';
                $fv = $hoy->modify('+' . mt_rand(1, 25) . ' days')->setTime(mt_rand(8, 21), azar([0, 30]));
            } else {
                $estado = 'favorito';
                $fv = $desdeAct->modify('+' . mt_rand(0, $spanDias) . ' days')->setTime(mt_rand(9, 20), 0);
                if ($fv > $hoy) $fv = $hoy->modify('-1 day');
            }
            $creado = $estado === 'pendiente' ? $hoy->modify('-' . mt_rand(0, 6) . ' days') : $fv->modify('-' . mt_rand(1, 10) . ' days');
            if ($creado < $alta) $creado = $alta;
            $desc = prob(.35) ? azar(['Con amigos', 'Plan de fin de semana', 'Recomendado por el hotel', 'Visita en familia', 'Para el último día', 'Ir temprano']) : '';
            $insItin->execute([$uid, $a['nombre'], $desc, $a['id'], $fv->format('Y-m-d H:i:sP'), $estado, fecha($creado), fecha($estado === 'visitado' ? $fv : $creado)]);
            $iid = (int)$insItin->fetchColumn();
            $tot['itinerarios']++;
            if ($estado === 'pendiente') { $tot['pendientes']++; continue; }
            if ($estado === 'favorito') $tot['favoritos']++;

            // Calificación: la mayoría de visitas y favoritos se califican
            if ($estado === 'visitado' && prob(.12)) continue;
            $base = $a['puntuacion'] + ($estado === 'favorito' ? 0.4 : 0) + (mt_rand(-150, 100) / 100);
            if ($estado === 'visitado' && prob(.13)) $base -= mt_rand(150, 330) / 100; // mala experiencia puntual
            $nota = max(1, min(5, (int)round($base)));
            $sent = $nota >= 4 ? 'pos' : ($nota === 3 ? 'neu' : 'neg');
            $txt  = $resenas[$lang];
            $comentario = $sent === 'pos' ? azar($txt['pos'][$a['cat']] ?? $txt['pos'][7]) : azar($txt[$sent]);
            if (prob(.12)) $comentario = null; // calificó sin escribir
            $tags = $sent === 'neg' ? [azar($tagsNeg)] : array_values(array_unique([azar($tagsPos), azar($tagsPos)]));
            $cuando = $fv->modify('+' . mt_rand(1, 30) . ' hours');
            if ($cuando > $hoy) $cuando = $hoy->modify('-10 minutes');
            $metricas = ['tags' => $tags, 'atraccion_id' => $a['id'], 'calificacion' => $nota,
                'feedback_tipo' => $nota >= 4 ? 'positivo' : ($nota === 3 ? 'neutral' : 'negativo'),
                'modelo_origen' => ['id' => $a['modelo'], 'nombre' => $a['modelo_nombre'], 'version' => $a['modelo_version']],
                'fecha_reajuste' => fecha($cuando),
                'preferencias_usadas' => ['compania' => $compania, 'rango_edad' => $rango, 'actividades' => $intereses['actividades'],
                    'experiencias' => $intereses['experiencias'], 'motivo_viaje' => $motivo, 'duracion_dias' => $duracion, 'presupuesto_max' => $presu]];
            $insBt->execute([$iid, $a['id'], $nota, $comentario, json_encode(['modelo_id' => $a['modelo']]), json_encode($metricas, $J),
                'completado', fecha($cuando), fecha($cuando)]);
            $tot['resenas']++;
        }

        // ── Solicitudes de soporte de quienes tienen el pago trabado ──
        if (in_array($caso, ['pendiente_viejo', 'rechazado'], true) || ($caso === 'pendiente_pago' && prob(.3))) {
            $resuelta = $caso === 'rechazado' && prob(.5);
            $creada = $hoy->modify('-' . mt_rand(2, 70) . ' hours');
            $insSop->execute([$uid, $caso === 'rechazado' ? 'pago_rechazado' : 'pago_no_confirmado',
                azar(['Ya pagué hace días y mi plan sigue sin activarse.', 'Subí el comprobante pero no me confirman el pago.', 'Me rechazaron el pago pero la transferencia salió bien, adjunto de nuevo.', 'Hola, ¿cuánto tarda la aprobación del pago?']),
                $resuelta ? 'resuelta' : azar(['pendiente', 'pendiente', 'en_revision']),
                $resuelta ? 'Revisamos tu transferencia, por favor vuelve a subir el comprobante con el monto completo.' : null,
                $resuelta ? $idsAdmin[1] : null, fecha($creada), fecha($creada)]);
            $tot['soporte']++;
        }

        $planTxt = $rp && isset($rp['tipo_plan']) ? "{$rp['tipo_plan']} ({$rp['periodo']})" : '';
        $credenciales[] = ['Turista', "$nom $ape", $email, $pass, $pais, $edad, $planTxt, $caso . ($estadoCuenta !== 'activo' ? " / cuenta $estadoCuenta" : '')];
    }

    $pdo->commit();
    require __DIR__ . "/recalcular_reajustes.php"; // vectores de reajuste del modelo, igual que al calificar en la app
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "ERROR, no se guardó nada: " . $e->getMessage() . "\n");
    exit(1);
}

// ── Credenciales (CSV con BOM para que Excel muestre bien los acentos) ──
$csv = fopen(__DIR__ . '/credenciales_prueba.csv', 'w');
fwrite($csv, "\xEF\xBB\xBF");
fputcsv($csv, ['tipo', 'nombre', 'correo', 'contraseña', 'país', 'edad', 'plan', 'caso de suscripción']);
foreach ($credenciales as $c) fputcsv($csv, $c);
fclose($csv);

echo json_encode(['usuarios' => count($credenciales)] + $tot, $J), "\n";
echo "Credenciales: herramientas/credenciales_prueba.csv\n";
