<?php
/**
 * JUSHSAL - Autenticación y Control de Acceso por Roles
 * Manejo de sesiones seguras, bloqueo por intentos fallidos y permisos del panel.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/bitacora.php';

/**
 * Inicia la sesión de forma segura con cookies HttpOnly y SameSite=Lax.
 */
function iniciar_sesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        if (!headers_sent()) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        @session_start();
    }
}

/**
 * Autentica credenciales de usuario con protección contra ataques de fuerza bruta.
 *
 * @param string $email
 * @param string $password
 * @return array ['exito' => bool, 'mensaje' => string, 'usuario' => array|null]
 */
function autenticar_usuario(string $email, string $password): array
{
    iniciar_sesion();
    $db = obtener_db();

    $stmt = $db->prepare("
        SELECT u.id, u.rol_id, u.nombre, u.email, u.password_hash, u.activo,
               u.intentos_fallidos, u.bloqueado_hasta, r.nombre AS rol_nombre
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id
        WHERE u.email = :email
        LIMIT 1
    ");
    $stmt->execute([':email' => trim($email)]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        // Mensaje genérico para no filtrar existencia de correos
        return ['exito' => false, 'mensaje' => 'Credenciales inválidas.', 'usuario' => null];
    }

    // 1. Verificar si la cuenta está bloqueada temporalmente
    if (!empty($usuario['bloqueado_hasta'])) {
        $tiempoBloqueo = strtotime($usuario['bloqueado_hasta']);
        $ahora = time();
        if ($tiempoBloqueo > $ahora) {
            $minutosRestantes = (int)ceil(($tiempoBloqueo - $ahora) / 60);
            return [
                'exito'   => false,
                'mensaje' => "Cuenta bloqueada temporalmente por seguridad. Intente de nuevo en {$minutosRestantes} minuto(s).",
                'usuario' => null
            ];
        } else {
            // El bloqueo ya expiró: restablecer intentos
            $stmtReset = $db->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = :id");
            $stmtReset->execute([':id' => $usuario['id']]);
            $usuario['intentos_fallidos'] = 0;
            $usuario['bloqueado_hasta'] = null;
        }
    }

    // 2. Verificar si la cuenta está activa
    if ((int)$usuario['activo'] !== 1) {
        return [
            'exito'   => false,
            'mensaje' => 'Su cuenta se encuentra inactiva. Contacte al administrador.',
            'usuario' => null
        ];
    }

    // 3. Verificar contraseña
    if (!password_verify($password, $usuario['password_hash'])) {
        $nuevosIntentos = (int)$usuario['intentos_fallidos'] + 1;

        if ($nuevosIntentos >= 5) {
            // Bloquear cuenta por 15 minutos
            $stmtBloqueo = $db->prepare("
                UPDATE usuarios 
                SET intentos_fallidos = :intentos, 
                    bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                WHERE id = :id
            ");
            $stmtBloqueo->execute([
                ':intentos' => $nuevosIntentos,
                ':id'       => $usuario['id']
            ]);

            registrar_bitacora('bloqueo', 'usuarios', $usuario['id'], 'Bloqueo temporal tras 5 intentos fallidos', $usuario['id']);

            return [
                'exito'   => false,
                'mensaje' => 'Ha superado el límite de intentos permitidos. Su cuenta ha sido bloqueada por 15 minutos.',
                'usuario' => null
            ];
        } else {
            $stmtInc = $db->prepare("UPDATE usuarios SET intentos_fallidos = :intentos WHERE id = :id");
            $stmtInc->execute([
                ':intentos' => $nuevosIntentos,
                ':id'       => $usuario['id']
            ]);

            $restantes = 5 - $nuevosIntentos;
            registrar_bitacora('login_fallido', 'usuarios', $usuario['id'], "Contraseña incorrecta (intento {$nuevosIntentos}/5)", $usuario['id']);

            return [
                'exito'   => false,
                'mensaje' => "Credenciales inválidas. Intentos restantes antes del bloqueo: {$restantes}.",
                'usuario' => null
            ];
        }
    }

    // 4. Autenticación exitosa: reiniciar contador y actualizar último acceso
    $stmtExito = $db->prepare("
        UPDATE usuarios 
        SET intentos_fallidos = 0, 
            bloqueado_hasta = NULL, 
            ultimo_acceso = NOW()
        WHERE id = :id
    ");
    $stmtExito->execute([':id' => $usuario['id']]);

    // Regenerar ID de sesión para prevenir Session Fixation
    if (!headers_sent()) {
        @session_regenerate_id(true);
    }

    $_SESSION['usuario_id']     = (int)$usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email']  = $usuario['email'];
    $_SESSION['rol_id']         = (int)$usuario['rol_id'];
    $_SESSION['rol_nombre']     = $usuario['rol_nombre'];

    registrar_bitacora('login', 'usuarios', $usuario['id'], 'Inicio de sesión exitoso');

    return [
        'exito'   => true,
        'mensaje' => 'Inicio de sesión exitoso.',
        'usuario' => [
            'id'         => (int)$usuario['id'],
            'nombre'     => $usuario['nombre'],
            'email'      => $usuario['email'],
            'rol_id'     => (int)$usuario['rol_id'],
            'rol_nombre' => $usuario['rol_nombre']
        ]
    ];
}

/**
 * Obtiene los datos del usuario en sesión activa.
 *
 * @return array|null
 */
function usuario_actual(): ?array
{
    iniciar_sesion();

    if (empty($_SESSION['usuario_id'])) {
        return null;
    }

    return [
        'id'         => (int)$_SESSION['usuario_id'],
        'nombre'     => $_SESSION['usuario_nombre'] ?? '',
        'email'      => $_SESSION['usuario_email'] ?? '',
        'rol_id'     => (int)($_SESSION['rol_id'] ?? 0),
        'rol_nombre' => $_SESSION['rol_nombre'] ?? ''
    ];
}

/**
 * Determina si hay una sesión de usuario válida activa.
 *
 * @return bool
 */
function esta_autenticado(): bool
{
    return usuario_actual() !== null;
}

/**
 * Exige que exista un usuario autenticado; de lo contrario redirige al login.
 *
 * @return void
 */
function requerir_login(): void
{
    if (!esta_autenticado()) {
        $loginUrl = defined('BASE_URL') ? BASE_URL . 'admin/login.php' : '/jushsal/admin/login.php';
        redirigir($loginUrl);
    }
}

/**
 * Exige que el usuario autenticado tenga uno de los roles permitidos.
 * Si no cumple con el rol, deniega el acceso con HTTP 403 Forbidden.
 *
 * @param array|string $rolesPermitidos Lista de roles permitidos (ej. ['admin', 'comunicaciones'])
 * @return void
 */
function requerir_rol(array|string $rolesPermitidos): void
{
    requerir_login();

    $usuario = usuario_actual();
    $roles = is_array($rolesPermitidos) ? $rolesPermitidos : [$rolesPermitidos];

    if (!$usuario || !in_array($usuario['rol_nombre'], $roles, true)) {
        http_response_code(403);
        die("
            <!DOCTYPE html>
            <html lang='es'>
            <head><meta charset='UTF-8'><title>403 - Acceso Denegado</title></head>
            <body style='font-family:sans-serif;text-align:center;padding:50px;'>
                <h2>403 · Acceso Denegado</h2>
                <p>Su perfil actual (<strong>" . e($usuario['rol_nombre'] ?? 'Invitado') . "</strong>) no tiene autorización para realizar esta acción.</p>
                <p><a href='" . (defined('BASE_URL') ? BASE_URL . 'admin/' : '/jushsal/admin/') . "'>Volver al panel</a></p>
            </body>
            </html>
        ");
    }
}

/**
 * Cierra la sesión activa de forma segura.
 *
 * @return void
 */
function cerrar_sesion(): void
{
    iniciar_sesion();

    if (!empty($_SESSION['usuario_id'])) {
        registrar_bitacora('logout', 'usuarios', (int)$_SESSION['usuario_id'], 'Cierre de sesión');
    }

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}
