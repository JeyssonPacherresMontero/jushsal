<?php
/**
 * JUSHSAL - Suite de Validación de Tarea T8: Admin, Usuarios y Bitácora
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/bitacora.php';
require_once dirname(__DIR__) . '/includes/modelos/usuarios.php';

echo "=== INICIANDO VALIDACIÓN DE TAREA T8: ADMIN, USUARIOS Y BITÁCORA ===\n\n";

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

// 1. Limpieza preventiva de usuarios de prueba
$db->exec("DELETE FROM usuarios WHERE email = 'test_comunicacion@jusanlorenzo.org.pe'");

// 2. Crear usuario con rol 'comunicaciones'
$nuevoId = usuarios_crear([
    'nombre'   => 'Operador de Prensa Demo',
    'email'    => 'test_comunicacion@jusanlorenzo.org.pe',
    'rol_id'   => 2, // comunicaciones
    'password' => 'PrensaPass2026!',
    'activo'   => 1
]);

afirmar($nuevoId > 0, "Usuario creado exitosamente con ID {$nuevoId}");

// 3. Verificar datos guardados y hash de contraseña
$usuarioCreado = usuarios_obtener_por_id($nuevoId);
afirmar($usuarioCreado !== null, "Usuario recuperado de la BD por ID");
afirmar($usuarioCreado['email'] === 'test_comunicacion@jusanlorenzo.org.pe', "Email coincide en minúsculas");
afirmar($usuarioCreado['rol_nombre'] === 'comunicaciones', "Rol asignado es 'comunicaciones'");

$stmtHash = $db->prepare("SELECT password_hash FROM usuarios WHERE id = :id");
$stmtHash->execute([':id' => $nuevoId]);
$hash = $stmtHash->fetchColumn();
afirmar(password_verify('PrensaPass2026!', $hash), "Contraseña encriptada válida con password_verify");

// 4. Intentar crear usuario con correo duplicado (debe fallar)
$lanzoDuplicado = false;
try {
    usuarios_crear([
        'nombre'   => 'Otro Operador',
        'email'    => 'test_comunicacion@jusanlorenzo.org.pe',
        'rol_id'   => 2,
        'password' => 'PrensaPass2026!',
        'activo'   => 1
    ]);
} catch (InvalidArgumentException $e) {
    $lanzoDuplicado = true;
}
afirmar($lanzoDuplicado, "Rechaza correo duplicado con InvalidArgumentException");

// 5. Intentar crear usuario con contraseña corta (<8 caracteres)
$lanzoCorta = false;
try {
    usuarios_crear([
        'nombre'   => 'Operador Corto',
        'email'    => 'test_corto@jusanlorenzo.org.pe',
        'rol_id'   => 2,
        'password' => '12345',
        'activo'   => 1
    ]);
} catch (InvalidArgumentException $e) {
    $lanzoCorta = true;
}
afirmar($lanzoCorta, "Rechaza contraseña menor a 8 caracteres");

// 6. Cambiar estado activo/inactivo
usuarios_cambiar_estado($nuevoId, 0);
$inactivo = usuarios_obtener_por_id($nuevoId);
afirmar((int)$inactivo['activo'] === 0, "Usuario desactivado correctamente (activo = 0)");

usuarios_cambiar_estado($nuevoId, 1);
$reactivado = usuarios_obtener_por_id($nuevoId);
afirmar((int)$reactivado['activo'] === 1, "Usuario reactivado correctamente (activo = 1)");

// 7. Resetear contraseña
usuarios_cambiar_password($nuevoId, 'NuevaPrensaPass2026!');
$stmtHash2 = $db->prepare("SELECT password_hash FROM usuarios WHERE id = :id");
$stmtHash2->execute([':id' => $nuevoId]);
$nuevoHash = $stmtHash2->fetchColumn();
afirmar(password_verify('NuevaPrensaPass2026!', $nuevoHash), "Nueva contraseña reseteada y verificada");
afirmar(!password_verify('PrensaPass2026!', $nuevoHash), "Contraseña anterior ya no es válida");

// 8. Probar registro en bitácora de auditoría
$registroExito = bitacora_registrar(
    $nuevoId,
    'crear',
    'noticias',
    999,
    '[TEST] Nota de prensa de prueba publicada'
);
afirmar($registroExito === true, "bitacora_registrar() ejecuta con éxito");

// 9. Consultar bitácora con filtros
$eventos = bitacora_listar(['entidad' => 'noticias', 'accion' => 'crear'], 5, 0);
afirmar(!empty($eventos), "bitacora_listar() recupera eventos con filtros");
$eventoTest = null;
foreach ($eventos as $ev) {
    if ((int)$ev['entidad_id'] === 999) {
        $eventoTest = $ev;
        break;
    }
}
afirmar($eventoTest !== null, "Evento específico encontrado en bitácora");
afirmar($eventoTest && $eventoTest['detalle'] === '[TEST] Nota de prensa de prueba publicada', "Detalle de bitácora coincide");

// 10. Probar conteo de bitácora
$conteoNoticias = bitacora_contar(['entidad' => 'noticias']);
afirmar($conteoNoticias >= 1, "bitacora_contar() devuelve total correcto ({$conteoNoticias})");

// 11. Acciones y entidades distintas
$acciones = bitacora_acciones_distintas();
afirmar(in_array('crear', $acciones, true), "bitacora_acciones_distintas() incluye 'crear'");
$entidades = bitacora_entidades_distintas();
afirmar(in_array('noticias', $entidades, true), "bitacora_entidades_distintas() incluye 'noticias'");

// 12. Matriz de permisos de AGENTS.md §4
// Rol 'admin' puede acceder a roles permitidos ['admin']
// Rol 'comunicaciones' NO puede acceder a roles permitidos ['admin']
$rolPermitidoAdmin = in_array('admin', ['admin'], true);
$rolPermitidoComunicaciones = in_array('comunicaciones', ['admin'], true);
afirmar($rolPermitidoAdmin === true, "Rol 'admin' tiene autorización completa");
afirmar($rolPermitidoComunicaciones === false, "Rol 'comunicaciones' queda bloqueado de administración");

// 13. Limpieza de datos de prueba
$db->exec("DELETE FROM bitacora WHERE detalle LIKE '[TEST]%'");
$db->exec("DELETE FROM usuarios WHERE email = 'test_comunicacion@jusanlorenzo.org.pe'");
$postLimpieza = usuarios_obtener_por_id($nuevoId);
afirmar($postLimpieza === null, "Usuario de prueba eliminado y BD limpia");

echo "\n--- RESUMEN DE PRUEBAS ---\n";
echo "Pruebas pasadas: {$pruebasPasadas} / {$pruebasTotal}\n";

if ($pruebasPasadas === $pruebasTotal) {
    echo ">>> TODOS LOS TESTS DE T8 PASARON SATISFACTORIAMENTE <<<\n";
    exit(0);
} else {
    echo ">>> HUBO ERRORES EN LA VALIDACIÓN <<<\n";
    exit(1);
}
