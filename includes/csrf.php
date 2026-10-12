<?php
/**
 * JUSHSAL - Protección Anti-CSRF (Cross-Site Request Forgery)
 * Generación, verificación e inyección de tokens de seguridad.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once __DIR__ . '/helpers.php';

/**
 * Asegura que la sesión esté iniciada con configuración adecuada.
 */
function asegurar_sesion_csrf(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Configurar opciones de cookie de sesión si los headers no han sido enviados
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
 * Obtiene el token CSRF activo o genera uno nuevo si no existe.
 *
 * @return string
 */
function csrf_token(): string
{
    asegurar_sesion_csrf();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Genera el campo oculto HTML para formularios POST.
 *
 * @return string
 */
function csrf_campo(): string
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Verifica la validez del token CSRF recibido frente al almacenado en sesión.
 *
 * @param string|null $token
 * @return bool
 */
function csrf_verificar(?string $token = null): bool
{
    asegurar_sesion_csrf();

    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    }

    if (!is_string($token) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Exige validación CSRF obligatoria para peticiones que modifican estado.
 * Responde con código HTTP 419 en caso de falla.
 *
 * @return void
 */
function csrf_requerir(): void
{
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array(strtoupper($metodo), ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
        if (!csrf_verificar()) {
            http_response_code(419);
            die('Error 419: Sesión expirada o token CSRF inválido. Por favor, recargue el formulario.');
        }
    }
}
