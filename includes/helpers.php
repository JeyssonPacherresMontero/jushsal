<?php
/**
 * JUSHSAL - Funciones de Ayuda (Helpers Globales)
 * Seguridad, formateo y utilidades comunes.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

/**
 * Escapa cadenas para prevenir ataques de Cross-Site Scripting (XSS).
 *
 * @param string|null $valor
 * @return string
 */
function e(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Genera un slug limpio y amigable para URLs a partir de una cadena.
 *
 * @param string $texto
 * @return string
 */
function slugify(string $texto): string
{
    // Reemplazos de caracteres especiales y acentuados en español
    $caracteres = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
        'ñ' => 'n', 'Ñ' => 'n', 'ü' => 'u', 'Ü' => 'u'
    ];
    $texto = strtr($texto, $caracteres);

    // Convertir a minúsculas
    $texto = mb_strtolower(trim($texto), 'UTF-8');

    // Reemplazar todo lo que no sea letra o número por guiones
    $texto = preg_replace('/[^a-z0-9]+/u', '-', $texto);

    // Eliminar guiones duplicados y de los extremos
    $texto = trim(preg_replace('/-+/', '-', $texto), '-');

    return $texto ?: 'item-' . bin2hex(random_bytes(4));
}

/**
 * Formatea una fecha en formato legible en español (ej. "12 oct 2026").
 *
 * @param string|null $fecha
 * @param bool $conHora
 * @return string
 */
function formato_fecha(?string $fecha, bool $conHora = false): string
{
    if (!$fecha) {
        return '';
    }

    $timestamp = strtotime($fecha);
    if ($timestamp === false) {
        return $fecha;
    }

    $meses = [
        1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr',
        5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'ago',
        9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic'
    ];

    $dia = date('d', $timestamp);
    $mesNumero = (int)date('n', $timestamp);
    $mes = $meses[$mesNumero] ?? '';
    $anio = date('Y', $timestamp);

    $resultado = "{$dia} {$mes} {$anio}";

    if ($conHora) {
        $hora = date('H:i', $timestamp);
        $resultado .= " {$hora}";
    }

    return $resultado;
}

/**
 * Redirige a una URL específica y detiene la ejecución del script.
 *
 * @param string $url
 * @return void
 */
function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Obtiene o asigna mensajes flash en la sesión.
 *
 * @param string $clave
 * @param string|null $mensaje Si se especifica, asigna el mensaje. Si es null, lo obtiene y elimina.
 * @return string|null
 */
function flash(string $clave, ?string $mensaje = null): ?string
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    if ($mensaje !== null) {
        $_SESSION['flash'][$clave] = $mensaje;
        return null;
    }

    if (isset($_SESSION['flash'][$clave])) {
        $msg = $_SESSION['flash'][$clave];
        unset($_SESSION['flash'][$clave]);
        return $msg;
    }

    return null;
}

/**
 * Obtiene la dirección IP real del visitante o usuario.
 *
 * @return string
 */
function obtener_ip(): string
{
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '127.0.0.1';
}

/**
 * Formatea un tamaño en bytes a representación legible (KB, MB).
 *
 * @param int $bytes
 * @return string
 */
function formato_bytes(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' B';
}
