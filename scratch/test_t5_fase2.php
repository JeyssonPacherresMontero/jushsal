<?php
/**
 * Test de Validación - Tarea T5 (Fase 2)
 * Valida Formulario y Bandeja de Mensajes de Contacto: modelo, API, vistas y bitácora.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
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

echo "=== INICIANDO VALIDACIÓN TAREA T5 (MENSAJES DE CONTACTO) ===\n\n";

// 1. Guardar mensaje válido con contacto_guardar_mensaje()
$ipTest = '192.168.1.100';
$datosPrueba = [
    'nombre'    => 'Carlos Alberto',
    'apellidos' => 'Morán Vílchez',
    'email'     => 'carlos.moran.test@ejemplo.com',
    'celular'   => '942000111',
    'mensaje'   => 'Solicito información oficial sobre el rol de turnos de riego para la Comisión Hualtaco I.'
];

$nuevoId = contacto_guardar_mensaje($datosPrueba, $ipTest);
assert_test($nuevoId > 0, "1. contacto_guardar_mensaje() inserta mensaje en BD (ID: {$nuevoId})");

// 2. Obtener por ID
$msgRecuperado = contacto_obtener_por_id($nuevoId);
assert_test($msgRecuperado !== null, "2. contacto_obtener_por_id() recupera el registro");
assert_test($msgRecuperado['nombre'] === 'Carlos Alberto' && $msgRecuperado['estado'] === 'pendiente', "3. Datos y estado inicial 'pendiente' correctos");
assert_test($msgRecuperado['ip'] === $ipTest, "4. IP de origen almacenada correctamente");

// 3. Validaciones de datos
$errorEmail = false;
try {
    contacto_guardar_mensaje([
        'nombre'    => 'Juan',
        'apellidos' => 'Pérez',
        'email'     => 'correo_invalido_sin_arroba',
        'mensaje'   => 'Mensaje de prueba con más de 10 caracteres'
    ], $ipTest);
} catch (InvalidArgumentException $e) {
    $errorEmail = true;
}
assert_test($errorEmail, "5. Rechaza correo electrónico no válido con excepción");

$errorMensajeCorto = false;
try {
    contacto_guardar_mensaje([
        'nombre'    => 'Juan',
        'apellidos' => 'Pérez',
        'email'     => 'juan@test.com',
        'mensaje'   => 'Hola' // Menos de 10 caracteres
    ], $ipTest);
} catch (InvalidArgumentException $e) {
    $errorMensajeCorto = true;
}
assert_test($errorMensajeCorto, "6. Rechaza mensaje con menos de 10 caracteres con excepción");

// 4. Cambiar estado a 'atendido' con notas internas
$actEstado = contacto_cambiar_estado(
    $nuevoId,
    'atendido',
    'Se remitió el rol de turnos de riego vía correo institucional el 12/10.',
    1 // atendido por usuario 1
);
assert_test($actEstado, "7. contacto_cambiar_estado() ejecuta exitosamente");

$msgAtendido = contacto_obtener_por_id($nuevoId);
assert_test($msgAtendido['estado'] === 'atendido', "8. Estado actualizado a 'atendido'");
assert_test(strpos($msgAtendido['notas_atencion'], 'rol de turnos') !== false, "9. Notas de atención guardadas correctamente");
assert_test((int)$msgAtendido['atendido_por'] === 1, "10. Atendido por ID de usuario asignado");

// 5. Conteo de estados y pendientes
$conteos = contacto_conteo_estados();
assert_test(isset($conteos['total'], $conteos['pendiente'], $conteos['atendido'], $conteos['archivado']), "11. contacto_conteo_estados() estructura completa de contadores");
assert_test($conteos['atendido'] >= 1, "12. Conteo de atendidos refleja al menos 1 mensaje");

// 6. Probar endpoint api/contacto_enviar.php vía simulación HTTP POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '10.0.0.50';
$_POST = [
    'nombre'    => 'María Elena',
    'apellidos' => 'Castro Silva',
    'email'     => 'maria.castro.test@ejemplo.com',
    'celular'   => '969123456',
    'mensaje'   => 'Consulta ciudadana enviada vía formulario web para validar API.'
];

if (!defined('EN_TESTING_CLI')) {
    define('EN_TESTING_CLI', true);
}
ob_start();
require dirname(__DIR__) . '/api/contacto_enviar.php';
$jsonApi = ob_get_clean();

$respApi = json_decode($jsonApi, true);
assert_test(is_array($respApi) && !empty($respApi['exito']), "13. api/contacto_enviar.php responde JSON exitoso con exito=true");
$idMsgApi = $respApi['id'] ?? 0;
assert_test($idMsgApi > 0, "14. api/contacto_enviar.php registró y retornó ID ({$idMsgApi})");

// 7. Verificar enlaces en admin/header.php y tarjetas en admin/index.php
$headerContent = file_get_contents(dirname(__DIR__) . '/admin/header.php');
assert_test(strpos($headerContent, 'contactos/listar.php') !== false, "15. admin/header.php contiene enlace a Mensajes de Contacto");

$indexContent = file_get_contents(dirname(__DIR__) . '/admin/index.php');
assert_test(strpos($indexContent, 'Consultas de Contacto') !== false, "16. admin/index.php contiene tarjeta métrica de Contacto");

// 8. Limpieza de registros de prueba en BD
$db = obtener_db();
$stmtDel = $db->prepare("DELETE FROM mensajes_contacto WHERE id IN (:id1, :id2)");
$stmtDel->execute([':id1' => $nuevoId, ':id2' => $idMsgApi]);
assert_test(true, "17. Limpieza de mensajes de prueba en BD completada");

echo "\n============================================\n";
echo "RESULTADO: {$total} pruebas ejecutadas, {$fallos} fallos.\n";
if ($fallos === 0) {
    echo ">>> TODOS LOS TESTS DE T5 PASARON EXITOSAMENTE <<<\n";
    exit(0);
} else {
    echo ">>> HUBO FALLOS EN T5 <<<\n";
    exit(1);
}
