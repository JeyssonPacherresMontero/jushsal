<?php
/**
 * Test de Verificación Automatizado para la Tarea T2 (Núcleo de Seguridad)
 */

define('JUSHSAL_INIT', true);

// Iniciar sesión antes de emitir cualquier salida por CLI
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_once __DIR__ . '/../includes/bitacora.php';

echo "============================================================\n";
echo " TEST DE VERIFICACIÓN · TAREA T2: SEGURIDAD JUSHSAL\n";
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

// 1. Pruebas de Helpers
$xss = '<script>alert("XSS")</script>';
afirmar(e($xss) === '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', 'Helper e() neutraliza XSS correctamente');

$slugPrueba = 'Convocatoria a Asamblea General Extraordinaria 2026!';
afirmar(slugify($slugPrueba) === 'convocatoria-a-asamblea-general-extraordinaria-2026', 'Helper slugify() genera URLs amigables limpias');

afirmar(formato_fecha('2026-09-29') === '29 sep 2026', 'Helper formato_fecha() formatea fechas en español');

flash('alerta', 'Mensaje de prueba flash');
afirmar(flash('alerta') === 'Mensaje de prueba flash', 'Helper flash() almacena y recupera mensaje');
afirmar(flash('alerta') === null, 'Helper flash() se elimina tras primer consumo');

// 2. Pruebas de CSRF
$token = csrf_token();
afirmar(!empty($token) && strlen($token) === 64, 'Token CSRF generado con 64 caracteres hexadecimales');
afirmar(csrf_verificar($token) === true, 'Token CSRF legítimo verificado con éxito');
afirmar(csrf_verificar('token_falso_invalido') === false, 'Token CSRF manipulado es rechazado');
afirmar(csrf_verificar(null) === false, 'Token CSRF ausente es rechazado');

// 3. Pruebas de Subida de Archivos y Detección de Malware
// 3.1. Archivo PHP camuflado como .jpg
$tempShell = tempnam(sys_get_temp_dir(), 'mal');
file_put_contents($tempShell, "<?php echo 'shell ejecutada'; ?>");
$archivoFalso = [
    'name'     => 'foto_inocente.jpg',
    'type'     => 'image/jpeg',
    'tmp_name' => $tempShell,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tempShell)
];
$resSubidaFalsa = subir_imagen($archivoFalso, 'noticias');
afirmar($resSubidaFalsa['exito'] === false, 'Subida rechazada: Archivo PHP con extensión jpg o código ejecutable');
@unlink($tempShell);

// 3.2. Archivo mayor a 5 MB
$tempGrande = tempnam(sys_get_temp_dir(), 'big');
$archivoGrande = [
    'name'     => 'pesada.jpg',
    'type'     => 'image/jpeg',
    'tmp_name' => $tempGrande,
    'error'    => UPLOAD_ERR_OK,
    'size'     => 6 * 1024 * 1024 // 6 MB
];
$resGrande = subir_imagen($archivoGrande, 'noticias');
afirmar($resGrande['exito'] === false, 'Subida rechazada: Archivo mayor al límite de 5 MB');
@unlink($tempGrande);

// 3.3. Imagen real válida tomada de los assets existentes
$origenImg = __DIR__ . '/../assets/images/about.jpg';
$tempImg = tempnam(sys_get_temp_dir(), 'okimg') . '.jpg';
copy($origenImg, $tempImg);

$archivoReal = [
    'name'     => 'prueba_valida.jpg',
    'type'     => 'image/jpeg',
    'tmp_name' => $tempImg,
    'error'    => UPLOAD_ERR_OK,
    'size'     => filesize($tempImg)
];
$resReal = subir_imagen($archivoReal, 'noticias');
afirmar($resReal['exito'] === true && !empty($resReal['ruta_relativa']), 'Subida permitida: Imagen JPEG legítima procesada');
if ($resReal['exito']) {
    afirmar(file_exists(PATH_ROOT . $resReal['ruta_relativa']), 'Archivo físico guardado en ruta segura aleatoria');
    eliminar_imagen($resReal['ruta_relativa']);
    afirmar(!file_exists(PATH_ROOT . $resReal['ruta_relativa']), 'Función eliminar_imagen() remueve archivo físico');
}
@unlink($tempImg);

// 4. Pruebas de Autenticación y Bloqueo de Fuerza Bruta
$emailPrueba = 'test_seguridad@jusanlorenzo.org.pe';
$claveCorrecta = 'ClaveSegura123!';
$db = obtener_db();

// Crear usuario de prueba
$db->prepare("DELETE FROM usuarios WHERE email = :email")->execute([':email' => $emailPrueba]);
$stmtTestUser = $db->prepare("
    INSERT INTO usuarios (rol_id, nombre, email, password_hash, activo, intentos_fallidos)
    VALUES (2, 'Usuario Prueba', :email, :hash, 1, 0)
");
$stmtTestUser->execute([
    ':email' => $emailPrueba,
    ':hash'  => password_hash($claveCorrecta, PASSWORD_DEFAULT)
]);
$idTest = (int)$db->lastInsertId();

// 4.1. Login fallido
$resLoginFallido = autenticar_usuario($emailPrueba, 'clave_incorrecta');
afirmar($resLoginFallido['exito'] === false, 'Login rechazado con contraseña incorrecta');

// 4.2. Acumular 4 intentos más para llegar a 5
for ($i = 2; $i <= 5; $i++) {
    $resLoginFallido = autenticar_usuario($emailPrueba, 'clave_incorrecta_' . $i);
}
afirmar(str_contains($resLoginFallido['mensaje'], 'bloqueada'), 'Cuenta bloqueada al 5.º intento fallido');

// 4.3. Verificar en BD que bloqueado_hasta está en el futuro
$stmtCheck = $db->prepare("SELECT intentos_fallidos, bloqueado_hasta FROM usuarios WHERE email = :email");
$stmtCheck->execute([':email' => $emailPrueba]);
$datosCheck = $stmtCheck->fetch();
afirmar((int)$datosCheck['intentos_fallidos'] >= 5, 'Contador de intentos_fallidos >= 5 en BD');
afirmar(!empty($datosCheck['bloqueado_hasta']) && strtotime($datosCheck['bloqueado_hasta']) > time(), 'bloqueado_hasta registrado en el futuro (+15 min)');

// 4.4. Intentar con clave correcta mientras está bloqueado
$resBloqueado = autenticar_usuario($emailPrueba, $claveCorrecta);
afirmar($resBloqueado['exito'] === false && str_contains($resBloqueado['mensaje'], 'bloqueada'), 'Intento bloqueado incluso con clave correcta mientras dura el castigo');

// 4.5. Restablecer bloqueo manualmente y probar login exitoso
$db->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE email = :email")->execute([':email' => $emailPrueba]);
$resLoginExito = autenticar_usuario($emailPrueba, $claveCorrecta);
afirmar($resLoginExito['exito'] === true && esta_autenticado(), 'Login exitoso tras desbloqueo y sesión iniciada');

// 5. Pruebas de Bitácora
$stmtBit = $db->prepare("SELECT COUNT(*) FROM bitacora WHERE entidad = 'usuarios' AND (entidad_id = :uid1 OR usuario_id = :uid2)");
$stmtBit->execute([':uid1' => $idTest, ':uid2' => $idTest]);
$totalLogs = $stmtBit->fetchColumn();
afirmar($totalLogs >= 5, 'Eventos de auditoría registrados en tabla bitacora (intentos fallidos, bloqueo y login)');

// Limpieza de usuario de prueba
$db->prepare("DELETE FROM usuarios WHERE email = :email")->execute([':email' => $emailPrueba]);

echo "\n============================================================\n";
echo " RESULTADO: {$pruebasPasadas} de {$pruebasTotales} pruebas superadas con éxito.\n";
echo "============================================================\n\n";

if ($pruebasPasadas === $pruebasTotales) {
    exit(0);
} else {
    exit(1);
}
