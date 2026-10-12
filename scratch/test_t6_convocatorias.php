<?php
/**
 * JUSHSAL - Suite de Validación de Tarea T6: Convocatorias y Popup
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/modelos/convocatorias.php';

echo "=== INICIANDO VALIDACIÓN DE TAREA T6: CONVOCATORIAS Y POPUP ===\n\n";

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

// 1. Verificar zona horaria del servidor MariaDB vs PHP
$tzPhp = date_default_timezone_get();
afirmar($tzPhp === 'America/Lima', "PHP configurado en America/Lima ({$tzPhp})");

$stmtTz = $db->query("SELECT @@session.time_zone AS tz, NOW() as ahora");
$rowTz = $stmtTz->fetch();
afirmar($rowTz['tz'] === '-05:00', "MariaDB sincronizado a UTC-5: {$rowTz['tz']}");

// 2. Limpieza preventiva de pruebas anteriores
$db->exec("DELETE FROM convocatorias WHERE titulo LIKE '[TEST]%'");

// 3. Crear convocatoria activa con fechas vigentes en tiempo real
$ahora = new DateTime('now', new DateTimeZone('America/Lima'));
$ayer = (clone $ahora)->modify('-1 day')->format('Y-m-d H:i:s');
$manana = (clone $ahora)->modify('+2 days')->format('Y-m-d H:i:s');

$idActiva = convocatorias_crear([
    'titulo'         => '[TEST] Convocatoria Asamblea Ordinaria 2026',
    'imagen'         => 'uploads/convocatorias/test_afiche.webp',
    'fecha_asamblea' => (clone $ahora)->modify('+1 day')->format('Y-m-d'),
    'mostrar_desde'  => $ayer,
    'mostrar_hasta'  => $manana,
    'retraso_seg'    => 2,
    'duracion_seg'   => 15,
    'frecuencia'     => 'una_vez_por_dia',
    'activo'         => 1,
    'creado_por'     => 1
]);

afirmar($idActiva > 0, "Convocatoria activa creada con ID {$idActiva}");

// 4. Probar convocatoria_obtener_activa()
$activa = convocatoria_obtener_activa();
afirmar($activa !== null, "convocatoria_obtener_activa() devuelve un registro");
afirmar($activa && (int)$activa['id'] === $idActiva, "La convocatoria activa coincide con la registrada");
afirmar($activa && (int)$activa['retraso_seg'] === 2, "Retraso configurado en 2s");
afirmar($activa && (int)$activa['duracion_seg'] === 15, "Duración configurada en 15s");
afirmar($activa && $activa['frecuencia'] === 'una_vez_por_dia', "Frecuencia configurada en una_vez_por_dia");

// 5. Crear convocatoria futura y verificar que NO se active prematuramente
$proximaSemana = (clone $ahora)->modify('+7 days')->format('Y-m-d H:i:s');
$enDosSemanas = (clone $ahora)->modify('+14 days')->format('Y-m-d H:i:s');

$idFutura = convocatorias_crear([
    'titulo'         => '[TEST] Convocatoria Futura Extraordinaria',
    'imagen'         => 'uploads/convocatorias/test_futura.webp',
    'fecha_asamblea' => (clone $ahora)->modify('+10 days')->format('Y-m-d'),
    'mostrar_desde'  => $proximaSemana,
    'mostrar_hasta'  => $enDosSemanas,
    'retraso_seg'    => 5,
    'duracion_seg'   => 0,
    'frecuencia'     => 'cada_visita',
    'activo'         => 1,
    'creado_por'     => 1
]);

$activa2 = convocatoria_obtener_activa();
afirmar($activa2 && (int)$activa2['id'] === $idActiva, "Convocatoria futura no se activa antes de tiempo");

// 6. Validación de fechas invertidas (mostrar_hasta <= mostrar_desde debe fallar)
$lanzoExcepcion = false;
try {
    convocatorias_crear([
        'titulo'        => '[TEST] Rango Invalido',
        'imagen'        => 'uploads/convocatorias/invalido.webp',
        'mostrar_desde' => $manana,
        'mostrar_hasta' => $ayer, // INVERTIDO
        'activo'        => 1
    ]);
} catch (InvalidArgumentException $e) {
    $lanzoExcepcion = true;
}
afirmar($lanzoExcepcion, "Rechaza fechas invertidas (hasta <= desde) con InvalidArgumentException");

// 7. Desactivar la convocatoria activa y verificar que no se muestre
convocatorias_cambiar_activo($idActiva, 0);
$activa3 = convocatoria_obtener_activa();
afirmar($activa3 === null, "Convocatoria con activo=0 no se devuelve en activa");

// 8. Reactivarla y verificar cálculo de vigente_ahora en listado admin
convocatorias_cambiar_activo($idActiva, 1);
$listaAdmin = convocatorias_listar_admin();
$encontradaActiva = null;
$encontradaFutura = null;

foreach ($listaAdmin as $item) {
    if ((int)$item['id'] === $idActiva) $encontradaActiva = $item;
    if ((int)$item['id'] === $idFutura) $encontradaFutura = $item;
}

afirmar($encontradaActiva !== null && (int)$encontradaActiva['vigente_ahora'] === 1, "Listado admin marca vigente_ahora=1 para la actual");
afirmar($encontradaFutura !== null && (int)$encontradaFutura['vigente_ahora'] === 0, "Listado admin marca vigente_ahora=0 para la futura");

// 9. Actualizar parámetros
$actualizado = convocatorias_actualizar($idActiva, [
    'titulo'        => '[TEST] Convocatoria Actualizada',
    'mostrar_desde' => $ayer,
    'mostrar_hasta' => $manana,
    'retraso_seg'   => 4,
    'duracion_seg'  => 20,
    'frecuencia'    => 'una_vez_por_sesion',
    'activo'        => 1
]);
afirmar($actualizado === true, "Actualización de convocatoria exitosa");
$recargada = convocatorias_obtener_por_id($idActiva);
afirmar($recargada['titulo'] === '[TEST] Convocatoria Actualizada', "Título actualizado correctamente");
afirmar((int)$recargada['retraso_seg'] === 4, "Retraso actualizado a 4s");

// 10. Limpieza final de registros de test
$db->exec("DELETE FROM convocatorias WHERE titulo LIKE '[TEST]%'");
$postLimpieza = convocatorias_obtener_por_id($idActiva);
afirmar($postLimpieza === null, "Registros de prueba eliminados correctamente");

echo "\n--- RESUMEN DE PRUEBAS ---\n";
echo "Pruebas pasadas: {$pruebasPasadas} / {$pruebasTotal}\n";

if ($pruebasPasadas === $pruebasTotal) {
    echo ">>> TODOS LOS TESTS DE T6 PASARON SATISFACTORIAMENTE <<<\n";
    exit(0);
} else {
    echo ">>> HUBO ERRORES EN LA VALIDACIÓN <<<\n";
    exit(1);
}
