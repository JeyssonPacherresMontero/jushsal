<?php
/**
 * Test de Validación - Tarea T4 (Fase 2)
 * Valida Galería Fotográfica: modelo, vistas admin, renderizado en portada y borrado de archivos.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/modelos/galeria.php';

$fallos = 0;
$total = 0;

function assert_test(bool $cond, string $desc) {
    global $fallos, $total;
    $total++;
    if ($cond) {
        echo " [OK] {$desc}\n";
    } else {
        echo " [FAIL] {$desc}\n";
        $fallos++;
    }
}

echo "=== INICIANDO VALIDACIÓN TAREA T4 (GALERÍA FOTOGRÁFICA) ===\n\n";

// 1. Directorio uploads/galeria
$dirGaleria = dirname(__DIR__) . '/uploads/galeria';
assert_test(is_dir($dirGaleria), "1. Directorio uploads/galeria existe: {$dirGaleria}");

// 2. Comprobar semillas iniciales
$db = obtener_db();
$stmt = $db->query("SELECT COUNT(*) FROM galeria_imagenes");
$conteoInicial = (int)$stmt->fetchColumn();
assert_test($conteoInicial >= 7, "2. Hay al menos 7 fotografías iniciales registradas (actual: {$conteoInicial})");

// 3. Probar galeria_listar_publica()
$publicas = galeria_listar_publica();
assert_test(count($publicas) >= 7, "3. galeria_listar_publica() retorna las fotos activas (" . count($publicas) . " encontradas)");

$destacadas = array_filter($publicas, fn($f) => (int)$f['es_destacada'] === 1);
assert_test(count($destacadas) >= 2, "4. Existen fotos con es_destacada = 1 para el formato ancho (.gallery-wide) (" . count($destacadas) . " encontradas)");

// 4. Crear una fotografía de prueba
$imgPruebaPath = dirname(__DIR__) . '/uploads/galeria/test_dummy.jpg';
file_put_contents($imgPruebaPath, 'DUMMY_IMAGE_BYTES');

$nuevoId = galeria_crear([
    'titulo'       => 'Foto de Prueba Automatizada T4',
    'descripcion'  => 'Descripción de prueba para verificar creación de fotografía',
    'imagen'       => 'uploads/galeria/test_dummy.jpg',
    'es_destacada' => 1,
    'orden'        => 99,
    'activo'       => 1,
    'creado_por'   => 1
]);
assert_test($nuevoId > 0, "5. galeria_crear() inserta registro exitosamente (ID: {$nuevoId})");

// 5. Obtener por ID
$fotoCreada = galeria_obtener_por_id($nuevoId);
assert_test($fotoCreada && $fotoCreada['titulo'] === 'Foto de Prueba Automatizada T4', "6. galeria_obtener_por_id() recupera los datos correctos");
assert_test((int)$fotoCreada['es_destacada'] === 1, "7. Foto creada tiene es_destacada = 1");

// 6. Actualizar fotografía
$actOk = galeria_actualizar($nuevoId, [
    'titulo'       => 'Foto T4 Editada',
    'descripcion'  => 'Nueva descripción editada',
    'es_destacada' => 0,
    'orden'        => 10,
    'activo'       => 1
]);
assert_test($actOk, "8. galeria_actualizar() ejecuta correctamente");

$fotoActualizada = galeria_obtener_por_id($nuevoId);
assert_test($fotoActualizada['titulo'] === 'Foto T4 Editada' && (int)$fotoActualizada['es_destacada'] === 0, "9. Datos actualizados reflejados en BD");

// 7. Eliminar fotografía y archivo físico
assert_test(file_exists($imgPruebaPath), "10. Archivo de imagen de prueba existe en disco antes de borrar");
$elOk = galeria_eliminar($nuevoId);
assert_test($elOk, "11. galeria_eliminar() retorna true");
assert_test(!file_exists($imgPruebaPath), "12. Archivo físico en uploads/galeria/ fue eliminado del disco");
$fotoPostEliminar = galeria_obtener_por_id($nuevoId);
assert_test($fotoPostEliminar === null, "13. Registro eliminado de la base de datos");

// 8. Verificar que index.php renderiza la galería dinámica
ob_start();
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/jushsal/';
$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';
$_SERVER['SCRIPT_NAME'] = '/jushsal/index.php';
$_SERVER['PHP_SELF'] = '/jushsal/index.php';
require dirname(__DIR__) . '/index.php';
$htmlIndex = ob_get_clean();

assert_test(strpos($htmlIndex, 'gallery-grid') !== false, "14. index.php contiene la cuadrícula .gallery-grid");
assert_test(strpos($htmlIndex, 'gallery-wide') !== false, "15. index.php contiene elementos con clase .gallery-wide");
assert_test(strpos($htmlIndex, 'Instalaciones') !== false, "16. index.php contiene pie de foto de la BD ('Instalaciones')");

// 9. Comprobar enlace en admin/header.php
$headerAdminContent = file_get_contents(dirname(__DIR__) . '/admin/header.php');
assert_test(strpos($headerAdminContent, 'galeria/listar.php') !== false, "17. admin/header.php incluye enlace a Galería de Fotos");

echo "\n============================================\n";
echo "RESULTADO: {$total} pruebas ejecutadas, {$fallos} fallos.\n";
if ($fallos === 0) {
    echo ">>> TODOS LOS TESTS DE T4 PASARON EXITOSAMENTE <<<\n";
    exit(0);
} else {
    echo ">>> HUBO FALLOS EN T4 <<<\n";
    exit(1);
}
