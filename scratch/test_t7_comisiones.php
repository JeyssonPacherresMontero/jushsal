<?php
/**
 * JUSHSAL - Suite de Validación de Tarea T7: Comisiones de Usuarios (16)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/modelos/comisiones.php';

echo "=== INICIANDO VALIDACIÓN DE TAREA T7: COMISIONES (16) ===\n\n";

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

// 1. Verificar conteo exacto de 16 comisiones
$totalComisiones = (int)$db->query("SELECT COUNT(*) FROM comisiones")->fetchColumn();
afirmar($totalComisiones === 16, "Existen exactamente 16 comisiones registradas (Encontradas: {$totalComisiones})");

// 2. Verificar que los números sean secuenciales 1 al 16
$numeros = $db->query("SELECT numero FROM comisiones ORDER BY numero ASC")->fetchAll(PDO::FETCH_COLUMN);
$esperados = range(1, 16);
afirmar($numeros === $esperados, "Los números de sub sector son continuos del 1 al 16");

// 3. Probar comisiones_listar_publicas()
$publicas = comisiones_listar_publicas();
afirmar(count($publicas) === 16, "comisiones_listar_publicas() retorna las 16 comisiones");

// 4. Regla estricta de privacidad: la consulta pública NO DEBE contener la clave 'celular'
$tieneCelular = false;
foreach ($publicas as $c) {
    if (array_key_exists('celular', $c)) {
        $tieneCelular = true;
        break;
    }
}
afirmar(!$tieneCelular, "La función pública comisiones_listar_publicas() NO expone el campo 'celular'");

// 5. Asignar un presidente de prueba con celular confidencial
$comisionTest = $publicas[0];
$testId = (int)$comisionTest['id'];
$celularSecreto = '+51987654321';
$presidenteTest = '[DEMO] Representante de Prueba';

comision_presidente_guardar($testId, [
    'nombre_completo' => $presidenteTest,
    'periodo_inicio'  => 2026,
    'periodo_fin'     => 2029,
    'celular'         => $celularSecreto
]);

// 6. Verificar en admin que el presidente y celular SÍ están disponibles para coordinación interna
$adminList = comisiones_listar_admin();
$encontradaAdmin = null;
foreach ($adminList as $c) {
    if ((int)$c['id'] === $testId) {
        $encontradaAdmin = $c;
        break;
    }
}
afirmar($encontradaAdmin !== null, "Comisión de prueba encontrada en admin");
afirmar($encontradaAdmin && $encontradaAdmin['presidente_nombre'] === $presidenteTest, "Nombre de presidente visible en admin");
afirmar($encontradaAdmin && $encontradaAdmin['celular'] === $celularSecreto, "Celular visible en admin para coordinación interna");

// 7. Verificar en función pública que el presidente SÍ se muestra pero el celular NO
$publicasRecheck = comisiones_listar_publicas();
$encontradaPublica = null;
foreach ($publicasRecheck as $c) {
    if ((int)$c['id'] === $testId) {
        $encontradaPublica = $c;
        break;
    }
}
afirmar($encontradaPublica && $encontradaPublica['presidente_nombre'] === $presidenteTest, "Nombre de presidente visible en lista pública");
afirmar(!isset($encontradaPublica['celular']), "Campo celular no existe en registro público");

// 8. Verificar que ni index.php ni comisiones.php filtren el celular en su salida HTML
ob_start();
include dirname(__DIR__) . '/index.php';
$htmlIndex = ob_get_clean();

afirmar(strpos($htmlIndex, $celularSecreto) === false, "HTML de index.php NO contiene el número celular del presidente");
afirmar(strpos($htmlIndex, 'Sub sector N.° 16 de JUSHSAL') !== false || strpos($htmlIndex, 'Algarrobo Valle Hermoso') !== false, "HTML de index.php contiene la comisión 16");

ob_start();
include dirname(__DIR__) . '/comisiones.php';
$htmlComisiones = ob_get_clean();

afirmar(strpos($htmlComisiones, $celularSecreto) === false, "HTML de comisiones.php NO contiene el número celular del presidente");
afirmar(strpos($htmlComisiones, 'Sub Sector N.° 16 · JUSHSAL') !== false, "HTML de comisiones.php contiene las 16 comisiones");

// 9. Validación de periodos inválidos (periodo_fin < periodo_inicio debe lanzar excepción)
$lanzoExcepcion = false;
try {
    comision_presidente_guardar($testId, [
        'nombre_completo' => 'Test Invalido',
        'periodo_inicio'  => 2029,
        'periodo_fin'     => 2026 // Invertido
    ]);
} catch (InvalidArgumentException $e) {
    $lanzoExcepcion = true;
}
afirmar($lanzoExcepcion, "Rechaza periodo_fin < periodo_inicio con InvalidArgumentException");

// 10. Limpieza: desasignar presidente de prueba
comision_presidente_guardar($testId, [
    'nombre_completo' => '' // Vacío desasigna
]);
$comisionLimpia = comisiones_obtener_por_id($testId);
afirmar(empty($comisionLimpia['presidente_nombre']), "Presidente de prueba desasignado y base limpia");

echo "\n--- RESUMEN DE PRUEBAS ---\n";
echo "Pruebas pasadas: {$pruebasPasadas} / {$pruebasTotal}\n";

if ($pruebasPasadas === $pruebasTotal) {
    echo ">>> TODOS LOS TESTS DE T7 PASARON SATISFACTORIAMENTE <<<\n";
    exit(0);
} else {
    echo ">>> HUBO ERRORES EN LA VALIDACIÓN <<<\n";
    exit(1);
}
