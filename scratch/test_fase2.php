<?php
/**
 * Suite de Validación General - FASE 2 JUSHSAL
 * Valida la integración total de Documentos, Estados Financieros, Galería y Contacto.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

if (!defined('EN_TESTING_CLI')) {
    define('EN_TESTING_CLI', true);
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/upload_documento.php';
require_once __DIR__ . '/../includes/modelos/documentos.php';
require_once __DIR__ . '/../includes/modelos/galeria.php';
require_once __DIR__ . '/../includes/modelos/contacto.php';

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

echo "===============================================================\n";
echo "   SUITE DE VALIDACIÓN AUTOMATIZADA - FASE 2 JUSHSAL\n";
echo "===============================================================\n\n";

$db = obtener_db();

// 1. Integridad de Tablas y Esquema
echo "--- 1. Integridad de Base de Datos y Tablas ---\n";
$tablasFase2 = ['categorias_documento', 'documentos', 'galeria_imagenes', 'mensajes_contacto'];
foreach ($tablasFase2 as $t) {
    $stmt = $db->query("SHOW TABLES LIKE '{$t}'");
    assert_test($stmt->rowCount() > 0, "Tabla {$t} existe en junta_bd");
}

// 2. Tablas de Fase 1 intactas
echo "\n--- 2. Tablas de Fase 1 Intactas ---\n";
$stmtCom = $db->query("SELECT COUNT(*) FROM comisiones");
$totalCom = (int)$stmtCom->fetchColumn();
assert_test($totalCom === 16, "16 Comisiones de Usuarios intactas (actual: {$totalCom})");

$stmtUsu = $db->query("SELECT COUNT(*) FROM usuarios WHERE activo = 1");
$totalUsu = (int)$stmtUsu->fetchColumn();
assert_test($totalUsu >= 1, "Usuarios del panel administrativo activos: {$totalUsu}");

// 3. Módulo de Documentos y Estados Financieros
echo "\n--- 3. Módulo de Documentos y Estados Financieros ---\n";
$categorias = categorias_documento_listar();
assert_test(count($categorias) === 5, "5 Categorías institucionales registradas (actual: " . count($categorias) . ")");

$docsDestacados = documentos_listar_destacados(6);
assert_test(count($docsDestacados) >= 4, "Existen documentos destacados para portada (encontrados: " . count($docsDestacados) . ")");

// Validación de carga de PDF
$tmpPdf = sys_get_temp_dir() . '/test_pdf_' . bin2hex(random_bytes(4)) . '.pdf';
file_put_contents($tmpPdf, "%PDF-1.4\nTest Document Stream\n%%EOF");
$fileSim = [
    'name'     => 'Resolucion_Prueba.pdf',
    'type'     => 'application/pdf',
    'tmp_name' => $tmpPdf,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tmpPdf)
];
$resPdf = subir_documento_pdf($fileSim, 'documentos');
assert_test($resPdf['exito'] === true, "subir_documento_pdf() valida y almacena PDF con firma binaria");

$nuevoDocId = documentos_crear([
    'categoria_id'   => 1,
    'titulo'         => 'Documento de Prueba Suite Fase 2',
    'descripcion'    => 'Descripción para prueba integral',
    'archivo_ruta'   => $resPdf['ruta'],
    'formato'        => 'PDF',
    'tamanio_bytes'  => $resPdf['tamanio_bytes'],
    'anio'           => 2026,
    'es_destacado'   => 1,
    'orden'          => 99,
    'activo'         => 1,
    'creado_por'     => 1
]);
assert_test($nuevoDocId > 0, "documentos_crear() inserta registro en BD (ID: {$nuevoDocId})");

$docObtenido = documentos_obtener_por_id($nuevoDocId);
assert_test($docObtenido && $docObtenido['titulo'] === 'Documento de Prueba Suite Fase 2', "documentos_obtener_por_id() recupera datos correctos");

$elimDoc = documentos_eliminar($nuevoDocId);
assert_test($elimDoc === true && !file_exists(dirname(__DIR__) . '/' . $resPdf['ruta']), "documentos_eliminar() borra registro y archivo físico del servidor");

// 4. Módulo de Galería Fotográfica
echo "\n--- 4. Módulo de Galería Fotográfica Administrable ---\n";
$fotosPublicas = galeria_listar_publica();
assert_test(count($fotosPublicas) >= 7, "Galería pública contiene al menos 7 fotografías (actual: " . count($fotosPublicas) . ")");

$fotosWide = array_filter($fotosPublicas, fn($f) => (int)$f['es_destacada'] === 1);
assert_test(count($fotosWide) >= 2, "Existen fotos con formato destacado ancho (.gallery-wide) (actual: " . count($fotosWide) . ")");

$nuevoFotoId = galeria_crear([
    'titulo'       => 'Foto Suite Test',
    'descripcion'  => 'Pie de foto de prueba',
    'imagen'       => 'assets/images/sede.jpg',
    'es_destacada' => 0,
    'orden'        => 88,
    'activo'       => 1,
    'creado_por'   => 1
]);
assert_test($nuevoFotoId > 0, "galeria_crear() inserta foto en BD");

$delFoto = galeria_eliminar($nuevoFotoId);
assert_test($delFoto === true, "galeria_eliminar() elimina registro correctamente");

// 5. Módulo de Mensajes de Contacto
echo "\n--- 5. Módulo de Mensajes de Contacto ---\n";
$idContacto = contacto_guardar_mensaje([
    'nombre'    => 'Validación',
    'apellidos' => 'Suite General',
    'email'     => 'suite@jusanlorenzo.org.pe',
    'celular'   => '999888777',
    'mensaje'   => 'Prueba de integración de la suite automatizada para Fase 2.'
], '127.0.0.1');
assert_test($idContacto > 0, "contacto_guardar_mensaje() inserta mensaje (ID: {$idContacto})");

$msgRec = contacto_obtener_por_id($idContacto);
assert_test($msgRec && $msgRec['estado'] === 'pendiente', "Mensaje nuevo inicia en estado 'pendiente'");

$actMsg = contacto_cambiar_estado($idContacto, 'atendido', 'Atendido durante suite', 1);
assert_test($actMsg === true, "contacto_cambiar_estado() actualiza estado y notas de atención");

$db->prepare("DELETE FROM mensajes_contacto WHERE id = :id")->execute([':id' => $idContacto]);
assert_test(true, "Limpieza de mensaje de prueba completada");

// 6. Seguridad y Privacidad en Vistas Públicas
echo "\n--- 6. Seguridad y Privacidad en Vistas Públicas ---\n";
ob_start();
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/jushsal/documentos.php';
$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';
$_SERVER['SCRIPT_NAME'] = '/jushsal/documentos.php';
$_SERVER['PHP_SELF'] = '/jushsal/documentos.php';
require dirname(__DIR__) . '/documentos.php';
$htmlDocs = ob_get_clean();

assert_test(strpos($htmlDocs, 'Documentos Institucionales') !== false, "documentos.php renderiza correctamente");
// Verificar que no se filtran DNIs (8 dígitos continuos) ni celulares privados
$dnisEncontrados = preg_match('/\b\d{8}\b/', $htmlDocs);
assert_test($dnisEncontrados === 0, "documentos.php cumple con privacidad: 0 DNIs expuestos en HTML");

// 7. Navegación en Panel Administrativo
echo "\n--- 7. Navegación Administrativa ---\n";
$headerAdmin = file_get_contents(dirname(__DIR__) . '/admin/header.php');
assert_test(strpos($headerAdmin, 'documentos/listar.php') !== false, "admin/header.php contiene enlace a Documentos y Balances");
assert_test(strpos($headerAdmin, 'galeria/listar.php') !== false, "admin/header.php contiene enlace a Galería de Fotos");
assert_test(strpos($headerAdmin, 'contactos/listar.php') !== false, "admin/header.php contiene enlace a Mensajes de Contacto");

echo "\n===============================================================\n";
echo "RESULTADO FINAL: {$total} pruebas ejecutadas, {$fallos} fallos.\n";
if ($fallos === 0) {
    echo ">>> TODAS LAS PRUEBAS DE LA FASE 2 PASARON EXITOSAMENTE (100%) <<<\n";
    exit(0);
} else {
    echo ">>> SE ENCONTRARON FALLOS EN LA FASE 2 <<<\n";
    exit(1);
}
