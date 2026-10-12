<?php
/**
 * JUSHSAL - Configuración General del Sistema (Plantilla de Ejemplo)
 * Copiar este archivo a 'config.php' y ajustar las credenciales según el entorno.
 */

// Evitar acceso directo no deseado
if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

// Configuración de Base de Datos (MySQL / MariaDB en XAMPP)
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'junta_bd');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// URL Base del proyecto (soporta dinámicamente puerto estándar y alternativo como 8080)
$esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
define('BASE_URL', $esquema . '://' . $host . '/jushsal/');

// Rutas del sistema de archivos
define('PATH_ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('PATH_INCLUDES', PATH_ROOT . 'includes' . DIRECTORY_SEPARATOR);
define('PATH_UPLOADS', PATH_ROOT . 'uploads' . DIRECTORY_SEPARATOR);
define('URL_UPLOADS', BASE_URL . 'uploads/');

// Zona horaria oficial del Perú (America/Lima)
define('APP_TIMEZONE', 'America/Lima');
date_default_timezone_set(APP_TIMEZONE);

// Codificación interna
mb_internal_encoding('UTF-8');
