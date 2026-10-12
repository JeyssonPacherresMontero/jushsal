<?php
/**
 * JUSHSAL - Suite de Validación de Tarea T1 (Fase 2)
 * Valida tablas de BD, subida segura de PDFs y modelo de documentos.
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/upload_documento.php';
require_once dirname(__DIR__) . '/includes/modelos/documentos.php';

echo "=== INICIANDO VALIDACIÓN DE TAREA T1 (FASE 2) ===\n\n";

$pruebasPasadas = 0;
$pruebasTotal = 0;

function afirmar($condicion, $mensaje) {
    global $pruebasPasadas, $pruebasTotal;
    $pruebasTotal++;
    if ($condicion) {
        $pruebasPasadas++;
        echo " [OK] " . $mensaje . "\n";
    } else {
        echo " [FALLO] " . $mensaje . "\n";
    }
}

$db = obtener_db();

// 1. Verificar existencia de las tablas nuevas de Fase 2
$tablasFase2 = ['categorias_documento', 'documentos', 'galeria_imagenes', 'mensajes_contacto'];
foreach ($tablasFase2 as $t) {
    $existe = (int)$db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'junta_bd' AND table_name = '{$t}'")->fetchColumn();
    afirmar($existe === 1, "Tabla '{$t}' existe en junta_bd");
}

// 2. Verificar que las tablas de Fase 1 permanecen intactas
$totalComisiones = (int)$db->query("SELECT COUNT(*) FROM comisiones")->fetchColumn();
afirmar($totalComisiones === 16, "Tabla comisiones de Fase 1 permanece intacta con 16 registros");

// 3. Probar categorias_documento_listar()
$categorias = categorias_documento_listar();
afirmar(count($categorias) === 5, "Existen 5 categorías iniciales de documentos sembradas");

// 4. Probar documentos_listar_destacados(4)
$destacados = documentos_listar_destacados(4);
afirmar(count($destacados) === 4, "documentos_listar_destacados(4) devuelve exactamente 4 documentos para portada");

// 5. Test de seguridad: Archivo malicioso camuflado como PDF
// Crear archivo temporal con código PHP y extensión .pdf
$tmpFalso = tempnam(sys_get_temp_dir(), 'mal_') . '.pdf';
file_put_contents($tmpFalso, "<?php echo 'malicioso'; ?>");

$arrayFalso = [
    'name'     => 'ataque.pdf',
    'type'     => 'application/pdf',
    'tmp_name' => $tmpFalso,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tmpFalso)
];

$resFalso = subir_documento_pdf($arrayFalso);
@unlink($tmpFalso);

afirmar($resFalso['exito'] === false, "Rechaza archivo PHP disfrazado de PDF");

// 6. Test de subida legítima de PDF
$tmpReal = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
$pdfMuestra = dirname(__DIR__) . '/documentos/APRUEBAN VALORES DE TARIFA DE AGUA 2026.pdf';
copy($pdfMuestra, $tmpReal);

$arrayReal = [
    'name'     => 'informe_prueba.pdf',
    'type'     => 'application/pdf',
    'tmp_name' => $tmpReal,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tmpReal)
];

$resReal = subir_documento_pdf($arrayReal, 'documentos');
@unlink($tmpReal);

afirmar($resReal['exito'] === true, "Acepta documento PDF legítimo con cabecera %PDF-");
afirmar(!empty($resReal['ruta']) && str_starts_with($resReal['ruta'], 'uploads/documentos/'), "Ruta guardada en uploads/documentos/ con hash seguro");

$rutaSubida = $resReal['ruta'];

// 7. Probar documentos_crear() con el archivo subido
$docId = documentos_crear([
    'categoria_id'  => 1,
    'titulo'        => '[TEST] Documento de Prueba Automatizado',
    'descripcion'   => 'Descripción de validación unitaria de Fase 2',
    'archivo_ruta'  => $rutaSubida,
    'anio'          => 2026,
    'es_destacado'  => 0,
    'orden'         => 100,
    'activo'        => 1
]);

afirmar($docId > 0, "Documento de prueba creado en BD con ID {$docId}");

// 8. Recuperar y actualizar documento
$docObtenido = documentos_obtener_por_id($docId);
afirmar($docObtenido !== null && $docObtenido['titulo'] === '[TEST] Documento de Prueba Automatizado', "Documento recuperado correctamente");

$actualizado = documentos_actualizar($docId, [
    'categoria_id' => 2,
    'titulo'       => '[TEST] Documento de Prueba Modificado',
    'anio'         => 2026,
    'es_destacado' => 1
]);
afirmar($actualizado === true, "Documento actualizado correctamente");

// 9. Eliminar documento y verificar remoción física del archivo
$archivoFisico = dirname(__DIR__) . '/' . $rutaSubida;
afirmar(file_exists($archivoFisico), "Archivo físico temporal existe antes de eliminar");

$eliminado = documentos_eliminar($docId);
afirmar($eliminado === true, "documentos_eliminar() ejecutado con éxito");
afirmar(!file_exists($archivoFisico), "Archivo físico en uploads/ eliminado tras borrar documento");
afirmar(documentos_obtener_por_id($docId) === null, "Registro en BD removido por completo");

echo "\n--- RESUMEN DE PRUEBAS ---\n";
echo "Pruebas pasadas: {$pruebasPasadas} / {$pruebasTotal}\n";

if ($pruebasPasadas === $pruebasTotal) {
    echo ">>> TODOS LOS TESTS DE T1 (FASE 2) PASARON SATISFACTORIAMENTE <<<\n";
    exit(0);
} else {
    echo ">>> HUBO ERRORES EN LA VALIDACIÓN <<<\n";
    exit(1);
}
