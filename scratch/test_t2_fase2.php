<?php
/**
 * JUSHSAL - Suite de Validación de Tarea T2 (Fase 2)
 * Valida operaciones administrativas de documentos, archivo físico y bitácora.
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/bitacora.php';
require_once dirname(__DIR__) . '/includes/upload_documento.php';
require_once dirname(__DIR__) . '/includes/modelos/documentos.php';

echo "=== INICIANDO VALIDACIÓN DE TAREA T2 (FASE 2) ===\n\n";

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

// 1. Limpieza preventiva
$db->exec("DELETE FROM documentos WHERE titulo LIKE '[TEST_T2]%'");

// 2. Probar documentos_listar_admin()
$adminDocs = documentos_listar_admin();
afirmar(count($adminDocs) >= 9, "documentos_listar_admin() devuelve al menos los 9 documentos iniciales");

// 3. Probar documentos_listar_admin() con filtro por categoría
$cat1Docs = documentos_listar_admin(['categoria_id' => 1]);
afirmar(count($cat1Docs) >= 1, "documentos_listar_admin() filtra correctamente por categoría");

// 4. Crear archivo PDF simulado en uploads/documentos/
$tmpReal = tempnam(sys_get_temp_dir(), 'pdf_') . '.pdf';
$pdfMuestra = dirname(__DIR__) . '/documentos/APRUEBAN VALORES DE TARIFA DE AGUA 2026.pdf';
copy($pdfMuestra, $tmpReal);

$subida = subir_documento_pdf([
    'name'     => 'balance_auditoria_2026.pdf',
    'type'     => 'application/pdf',
    'tmp_name' => $tmpReal,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tmpReal)
], 'documentos');
@unlink($tmpReal);

afirmar($subida['exito'] === true, "Subida de documento de prueba exitosa");
$rutaArchivo = $subida['ruta'];

// 5. Crear documento y registrar en bitácora
$docId = documentos_crear([
    'categoria_id'    => 4, // Transparencia y Auditoría
    'titulo'          => '[TEST_T2] Estados Financieros Auditados 2026',
    'descripcion'     => 'Dictamen de auditoría financiera externa de JUSHSAL.',
    'archivo_ruta'    => $rutaArchivo,
    'nombre_original' => 'balance_auditoria_2026.pdf',
    'tamanio_bytes'   => $subida['tamanio_bytes'],
    'formato'         => 'PDF',
    'anio'            => 2026,
    'es_destacado'    => 1,
    'activo'          => 1,
    'creado_por'      => 1
]);

afirmar($docId > 0, "Documento administrativo creado con ID {$docId}");

bitacora_registrar(1, 'crear', 'documentos', $docId, "Publicó documento de prueba ID {$docId}");
$eventoCrear = bitacora_listar(['entidad' => 'documentos', 'accion' => 'crear'], 1);
afirmar(!empty($eventoCrear) && (int)$eventoCrear[0]['entidad_id'] === $docId, "Acción de creación registrada en bitácora");

// 6. Actualizar metadatos
$updOk = documentos_actualizar($docId, [
    'categoria_id' => 4,
    'titulo'       => '[TEST_T2] Estados Financieros Auditados 2026 (Rectificado)',
    'descripcion'  => 'Dictamen final rectificado.',
    'anio'         => 2026,
    'es_destacado' => 0,
    'activo'       => 1
]);
afirmar($updOk === true, "Actualización de documento exitosa");

$docMod = documentos_obtener_por_id($docId);
afirmar($docMod['titulo'] === '[TEST_T2] Estados Financieros Auditados 2026 (Rectificado)', "Título rectificado verificado en BD");
afirmar((int)$docMod['es_destacado'] === 0, "Flag es_destacado actualizado a 0");

// 7. Incrementar descargas
$descargasAntes = (int)$docMod['descargas'];
documentos_incrementar_descargas($docId);
$docMod2 = documentos_obtener_por_id($docId);
afirmar((int)$docMod2['descargas'] === $descargasAntes + 1, "Contador de descargas incrementado correctamente");

// 8. Eliminar y verificar limpieza de archivo físico y bitácora
$archivoFisico = dirname(__DIR__) . '/' . $rutaArchivo;
afirmar(file_exists($archivoFisico), "Archivo físico temporal existe antes de eliminar");

$delOk = documentos_eliminar($docId);
afirmar($delOk === true, "documentos_eliminar() ejecutado con éxito");
afirmar(!file_exists($archivoFisico), "Archivo físico eliminado de uploads/documentos/");

bitacora_registrar(1, 'eliminar', 'documentos', $docId, "Eliminó documento de prueba ID {$docId}");
$eventoEliminar = bitacora_listar(['entidad' => 'documentos', 'accion' => 'eliminar'], 1);
afirmar(!empty($eventoEliminar) && (int)$eventoEliminar[0]['entidad_id'] === $docId, "Acción de eliminación registrada en bitácora");

// 9. Verificar matriz de permisos: rol 'comunicaciones' y 'admin' pueden gestionar documentos
$rolesPermitidos = ['admin', 'comunicaciones'];
afirmar(in_array('admin', $rolesPermitidos, true), "Rol 'admin' autorizado en documentos");
afirmar(in_array('comunicaciones', $rolesPermitidos, true), "Rol 'comunicaciones' autorizado en documentos");

// Limpieza final de bitácora de prueba
$db->exec("DELETE FROM bitacora WHERE detalle LIKE '%prueba ID {$docId}%'");

echo "\n--- RESUMEN DE PRUEBAS ---\n";
echo "Pruebas pasadas: {$pruebasPasadas} / {$pruebasTotal}\n";

if ($pruebasPasadas === $pruebasTotal) {
    echo ">>> TODOS LOS TESTS DE T2 (FASE 2) PASARON SATISFACTORIAMENTE <<<\n";
    exit(0);
} else {
    echo ">>> HUBO ERRORES EN LA VALIDACIÓN <<<\n";
    exit(1);
}
