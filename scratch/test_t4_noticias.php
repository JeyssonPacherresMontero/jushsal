<?php
/**
 * Test de Verificación Automatizado para la Tarea T4 (Módulo de Noticias)
 */

define('JUSHSAL_INIT', true);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/modelos/noticias.php';

echo "============================================================\n";
echo " TEST DE VERIFICACIÓN · TAREA T4: NOTICIAS JUSHSAL\n";
echo "============================================================\n\n";

$pruebasPasadas = 0;
$pruebasTotales = 0;

function afirmar(bool $condicion, string $descripcion) {
    global $pruebasPasadas, $pruebasTotales;
    $pruebasTotales++;
    if ($condicion) {
        $pruebasPasadas++;
        echo " [✔] {$descripcion}\n";
    } else {
        echo " [✘] FALLÓ: {$descripcion}\n";
    }
}

$db = obtener_db();

// 1. Monograma
afirmar(monograma_noticia('Inscripción de nuevos usuarios') === 'IU', 'Monograma de dos palabras generado: IU');
afirmar(monograma_noticia('Comunicado') === 'C', 'Monograma de una palabra generado: C');

// 2. Slugs únicos
$slug1 = noticias_generar_slug_unico('Mantenimiento preventivo de canales');
afirmar($slug1 === 'mantenimiento-preventivo-de-canales', 'Primer slug generado limpiamente');

// Insertar noticia borrador de prueba
$db->prepare("DELETE FROM noticias WHERE titulo LIKE 'Test T4%'")->execute();

$idBorrador = noticias_crear([
    'categoria_id'      => 1,
    'titulo'            => 'Test T4 Noticia Borrador',
    'slug'              => noticias_generar_slug_unico('Test T4 Noticia Borrador'),
    'resumen'           => 'Este es un resumen de prueba de una noticia en borrador.',
    'contenido'         => 'Contenido detallado de la noticia borrador.',
    'estado'            => 'borrador',
    'fecha_publicacion' => date('Y-m-d'),
    'destacada'         => 0,
    'autor_id'          => 1
]);
afirmar($idBorrador > 0, 'Noticia borrador creada con ID ' . $idBorrador);

// 3. Comprobar que el borrador NO aparece en listado público
$publicadas = noticias_listar_publicadas(50);
$encontradaEnPublicadas = false;
foreach ($publicadas as $p) {
    if ((int)$p['id'] === $idBorrador) {
        $encontradaEnPublicadas = true;
        break;
    }
}
afirmar($encontradaEnPublicadas === false, 'Noticia en estado BORRADOR no aparece en la web pública');

// Comprobar que noticias_obtener_por_slug(..., soloPublicadas: true) no la encuentra
$slugBorrador = 'test-t4-noticia-borrador';
$detalleBorrador = noticias_obtener_por_slug($slugBorrador, true);
afirmar($detalleBorrador === null, 'Detalle público rechaza noticia en borrador (retorna null)');

// 4. Cambiar estado a PUBLICADO
noticias_cambiar_estado($idBorrador, 'publicado');
$publicadasDespues = noticias_listar_publicadas(50);
$encontradaPublicada = false;
foreach ($publicadasDespues as $p) {
    if ((int)$p['id'] === $idBorrador) {
        $encontradaPublicada = true;
        break;
    }
}
afirmar($encontradaPublicada === true, 'Noticia cambiada a PUBLICADO aparece de inmediato en la web pública');

$detallePublicada = noticias_obtener_por_slug($slugBorrador, true);
afirmar($detallePublicada !== null && (int)$detallePublicada['id'] === $idBorrador, 'Detalle público encuentra la noticia publicada por su slug');

// 5. Cambiar estado a ARCHIVADO (borrado lógico)
noticias_eliminar($idBorrador, true); // true = borrado lógico
$publicadasArchivadas = noticias_listar_publicadas(50);
$encontradaArchivada = false;
foreach ($publicadasArchivadas as $p) {
    if ((int)$p['id'] === $idBorrador) {
        $encontradaArchivada = true;
        break;
    }
}
afirmar($encontradaArchivada === false, 'Noticia ARCHIVADA desaparece del listado público');

// 6. Prueba de unicidad de Slug con duplicados
$idNoticiaA = noticias_crear([
    'categoria_id'      => 1,
    'titulo'            => 'Test T4 Convocatoria Urgente',
    'slug'              => noticias_generar_slug_unico('Test T4 Convocatoria Urgente'),
    'resumen'           => 'Resumen primera noticia',
    'estado'            => 'publicado',
    'fecha_publicacion' => date('Y-m-d')
]);
$slugDuplicado = noticias_generar_slug_unico('Test T4 Convocatoria Urgente');
afirmar($slugDuplicado === 'test-t4-convocatoria-urgente-2', 'Resolución de colisión de slug: asigna sufijo -2');

$idNoticiaB = noticias_crear([
    'categoria_id'      => 1,
    'titulo'            => 'Test T4 Convocatoria Urgente',
    'slug'              => $slugDuplicado,
    'resumen'           => 'Resumen segunda noticia',
    'estado'            => 'publicado',
    'fecha_publicacion' => date('Y-m-d')
]);
$slugTriplicado = noticias_generar_slug_unico('Test T4 Convocatoria Urgente');
afirmar($slugTriplicado === 'test-t4-convocatoria-urgente-3', 'Resolución de colisión de slug: asigna sufijo -3');

// 7. Prueba XSS
$tituloXSS = 'Test T4 <script>alert("XSS")</script>';
$contenidoXSS = "Línea 1\n<img src=x onerror=alert(1)>\nLínea 2";
$idXSS = noticias_crear([
    'categoria_id'      => 2,
    'titulo'            => $tituloXSS,
    'slug'              => noticias_generar_slug_unico($tituloXSS),
    'resumen'           => 'Resumen con comillas " y <script>',
    'contenido'         => $contenidoXSS,
    'estado'            => 'publicado',
    'fecha_publicacion' => date('Y-m-d')
]);
$noticiaXSS = noticias_obtener_por_id($idXSS);
$htmlTituloEscapado = e($noticiaXSS['titulo']);
$htmlContenidoEscapado = nl2br(e($noticiaXSS['contenido']));

afirmar(!str_contains($htmlTituloEscapado, '<script>'), 'Título con <script> es neutralizado con entidades HTML');
afirmar(str_contains($htmlTituloEscapado, '&lt;script&gt;'), 'Título contiene entidades escapadas &lt;script&gt;');
afirmar(!str_contains($htmlContenidoEscapado, '<img'), 'Cuerpo con tag <img onerror> es neutralizado');
afirmar(str_contains($htmlContenidoEscapado, '<br />') || str_contains($htmlContenidoEscapado, '<br>'), 'Saltos de línea en texto plano convertidos a <br>');

// Limpieza de datos de prueba
$db->prepare("DELETE FROM noticias WHERE titulo LIKE 'Test T4%'")->execute();

echo "\n============================================================\n";
echo " RESULTADO: {$pruebasPasadas} de {$pruebasTotales} pruebas superadas con éxito.\n";
echo "============================================================\n\n";

if ($pruebasPasadas === $pruebasTotales) {
    exit(0);
} else {
    exit(1);
}
