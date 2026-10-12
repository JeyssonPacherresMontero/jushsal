<?php
/**
 * JUSHSAL - Conexión a la Base de Datos (PDO)
 * Utiliza sentencias preparadas, emulación desactivada y sincronización horaria UTC-5.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

// Cargar configuración si aún no está presente
if (!defined('DB_NAME')) {
    $configPath = __DIR__ . '/config.php';
    if (!file_exists($configPath)) {
        die('Error: No se encontró el archivo de configuración includes/config.php. Verifique includes/config.example.php.');
    }
    require_once $configPath;
}

/**
 * Obtiene la instancia única (singleton) de la conexión PDO.
 *
 * @return PDO
 * @throws PDOException
 */
function obtener_db(): PDO
{
    static $db = null;

    if ($db === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '-05:00'"
        ];

        try {
            $db = new PDO($dsn, DB_USER, DB_PASS, $opciones);
            // Asegurar sincronización de zona horaria Perú (-05:00)
            $db->exec("SET time_zone = '-05:00'");
        } catch (PDOException $e) {
            error_log('Error de conexión PDO JUSHSAL: ' . $e->getMessage());
            die('Error al conectar con la base de datos institucional. Por favor, contacte al administrador.');
        }
    }

    return $db;
}

// Variable de conexión para compatibilidad directa
$conexion = obtener_db();
