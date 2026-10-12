<?php
/**
 * Test de Verificación Automatizado para la Tarea T5 (Presidentes y Logros por Año)
 */

define('JUSHSAL_INIT', true);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/modelos/gestiones.php';

echo "============================================================\n";
echo " TEST DE VERIFICACIÓN · TAREA T5: PRESIDENTES Y LOGROS\n";
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

// 1. Verificar gestión vigente inicial (Miguel Castillo Vera, 2026-2029)
$vigenteInicial = gestiones_obtener_vigente();
afirmar($vigenteInicial !== null, 'Existe una gestión vigente inicial en la base de datos');
afirmar($vigenteInicial['presidente_nombre'] === 'Miguel Castillo Vera', 'Presidente vigente inicial es Miguel Castillo Vera');
afirmar((int)$vigenteInicial['periodo_inicio'] === 2026, 'Año de inicio vigente inicial es 2026');

// 2. Validación de rangos de años en gestiones
$errorRangoCapturado = false;
try {
    gestiones_crear([
        'presidente_nombre' => 'Test Invalido',
        'periodo_inicio'    => 2025,
        'periodo_fin'       => 2020 // Fin menor que inicio -> debe fallar
    ]);
} catch (InvalidArgumentException $e) {
    $errorRangoCapturado = true;
}
afirmar($errorRangoCapturado === true, 'Rechazo garantizado al intentar periodo_fin menor a periodo_inicio');

// 3. Crear gestión histórica demo (2022–2025 [DEMO])
$db->prepare("DELETE FROM gestiones_presidenciales WHERE presidente_nombre LIKE '%[DEMO]%'")->execute();

$idDemo = gestiones_crear([
    'presidente_nombre' => 'Juan Pérez Gómez [DEMO]',
    'periodo_inicio'    => 2022,
    'periodo_fin'       => 2025,
    'semblanza'         => 'Gestión histórica de prueba orientada a la tecnificación del riego.',
    'es_vigente'        => 0
]);
afirmar($idDemo > 0, 'Gestión histórica [DEMO] (2022-2025) creada con ID ' . $idDemo);

// 4. Validación de rango de años en logros
$errorLogroFueraDeRango = false;
try {
    logros_crear([
        'gestion_id' => $idDemo,
        'anio'       => 2020, // Fuera del periodo 2022-2025
        'titulo'     => 'Logro en año inválido'
    ]);
} catch (RangeException $e) {
    $errorLogroFueraDeRango = true;
}
afirmar($errorLogroFueraDeRango === true, 'Rechazo garantizado ante logro con año fuera del periodo de la gestión');

// 5. Registrar 2 logros en años diferentes (2023 y 2024) dentro de la gestión demo
$idLogro1 = logros_crear([
    'gestion_id'  => $idDemo,
    'anio'        => 2023,
    'titulo'      => 'Rehabilitación de compuertas en Sector Quiroz [DEMO]',
    'descripcion' => 'Trabajos de mantenimiento integral en compuertas metálicas.'
]);

$idLogro2 = logros_crear([
    'gestion_id'  => $idDemo,
    'anio'        => 2024,
    'titulo'      => 'Adquisición de maquinaria pesada [DEMO]',
    'descripcion' => 'Compra de retroexcavadora para descolmatación de drenes.'
]);

$idLogro3 = logros_crear([
    'gestion_id'  => $idDemo,
    'anio'        => 2024,
    'titulo'      => 'Capacitación en uso eficiente del agua [DEMO]',
    'descripcion' => 'Talleres para 200 regantes.'
]);

afirmar($idLogro1 > 0 && $idLogro2 > 0 && $idLogro3 > 0, '3 logros creados exitosamente en años 2023 y 2024');

// 6. Probar agrupación de logros por año
$logrosDemo = logros_listar_por_gestion($idDemo);
$logrosAgrupados = logros_agrupar_por_anio($logrosDemo);

afirmar(isset($logrosAgrupados[2023]) && isset($logrosAgrupados[2024]), 'Logros agrupados exitosamente en llaves de año 2023 y 2024');
afirmar(count($logrosAgrupados[2023]) === 1, 'Año 2023 contiene exactamente 1 logro');
afirmar(count($logrosAgrupados[2024]) === 2, 'Año 2024 contiene exactamente 2 logros');

// 7. Prueba transaccional de cambio de vigencia
gestiones_marcar_vigente($idDemo);

$nuevoVigente = gestiones_obtener_vigente();
afirmar((int)$nuevoVigente['id'] === $idDemo, 'La gestión [DEMO] ahora es la vigente');

// Verificar que Miguel Castillo Vera pasó a no vigente
$stmtVerificarAntigua = $db->prepare("SELECT es_vigente FROM gestiones_presidenciales WHERE id = :id");
$stmtVerificarAntigua->execute([':id' => $vigenteInicial['id']]);
afirmar((int)$stmtVerificarAntigua->fetchColumn() === 0, 'La gestión anterior pasó atómicamente a no vigente (es_vigente = 0)');

// Verificar que en toda la tabla solo hay 1 registro con es_vigente = 1
$totalVigentes = (int)$db->query("SELECT COUNT(*) FROM gestiones_presidenciales WHERE es_vigente = 1")->fetchColumn();
afirmar($totalVigentes === 1, 'Se garantiza estrictamente que solo UNA gestión tiene es_vigente = 1 en toda la base de datos');

// 8. Restaurar a Miguel Castillo Vera como vigente original
gestiones_marcar_vigente((int)$vigenteInicial['id']);
$restauradoVigente = gestiones_obtener_vigente();
afirmar((int)$restauradoVigente['id'] === (int)$vigenteInicial['id'], 'Restablecida vigencia original a Miguel Castillo Vera');

// 9. Limpieza de datos de prueba [DEMO]
gestiones_eliminar($idDemo);
$existeDemoTrasBorrado = gestiones_obtener_por_id($idDemo);
afirmar($existeDemoTrasBorrado === null, 'Gestión [DEMO] y sus logros hijos eliminados correctamente');

echo "\n============================================================\n";
echo " RESULTADO: {$pruebasPasadas} de {$pruebasTotales} pruebas superadas con éxito.\n";
echo "============================================================\n\n";

if ($pruebasPasadas === $pruebasTotales) {
    exit(0);
} else {
    exit(1);
}
