<?php
/**
 * JUSHSAL - API Endpoint: Enviar Mensaje de Contacto
 * Guarda la consulta ciudadana en la base de datos y retorna respuesta JSON.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

if (!headers_sent()) {
    header('Content-Type: application/json; charset=UTF-8');
}

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/modelos/contacto.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'exito' => false,
        'error' => 'Método no permitido. Se requiere petición POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener IP del cliente
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Protección contra flood / spam: máx 5 envíos en 10 minutos por IP
if (contacto_ip_excede_limite($ip, 5, 10)) {
    http_response_code(429);
    echo json_encode([
        'exito' => false,
        'error' => 'Has alcanzado el límite de consultas permitidas. Por favor, espera unos minutos antes de intentar nuevamente.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener datos (pueden venir vía POST estándar o JSON body)
$nombre    = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$email     = trim($_POST['email'] ?? '');
$celular   = trim($_POST['celular'] ?? '');
$mensaje   = trim($_POST['mensaje'] ?? '');

// Si vienen vía php://input en formato JSON
if ($nombre === '' && $apellidos === '' && $email === '') {
    $cuerpoJson = file_get_contents('php://input');
    if ($cuerpoJson) {
        $datosJson = json_decode($cuerpoJson, true);
        if (is_array($datosJson)) {
            $nombre    = trim($datosJson['nombre'] ?? '');
            $apellidos = trim($datosJson['apellidos'] ?? '');
            $email     = trim($datosJson['email'] ?? '');
            $celular   = trim($datosJson['celular'] ?? '');
            $mensaje   = trim($datosJson['mensaje'] ?? '');
        }
    }
}

// Validaciones
if ($nombre === '' || $apellidos === '') {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'error' => 'Por favor, ingresa tus nombres y apellidos completos.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'error' => 'Por favor, ingresa una dirección de correo electrónico válida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (mb_strlen($mensaje) < 10) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'error' => 'El mensaje de consulta debe contener al menos 10 caracteres explicativos.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $mensajeId = contacto_guardar_mensaje([
        'nombre'    => $nombre,
        'apellidos' => $apellidos,
        'email'     => $email,
        'celular'   => $celular,
        'mensaje'   => $mensaje
    ], $ip);

    echo json_encode([
        'exito'   => true,
        'mensaje' => '¡Tu mensaje ha sido registrado exitosamente! Nos comunicaremos contigo a la brevedad posible.',
        'id'      => $mensajeId
    ], JSON_UNESCAPED_UNICODE);
    if (!defined('EN_TESTING_CLI')) {
        exit;
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'error' => 'Ocurrió un error al registrar tu consulta. Inténtalo nuevamente más tarde.'
    ], JSON_UNESCAPED_UNICODE);
    if (!defined('EN_TESTING_CLI')) {
        exit;
    }
}
