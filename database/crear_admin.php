<?php
/**
 * JUSHSAL - Script CLI para crear usuario Administrador
 * Uso interactivo: php database/crear_admin.php
 * Uso con argumentos: php database/crear_admin.php --email=admin@jusanlorenzo.org.pe --nombre="Administrador" --password="clave"
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Acceso denegado: Este script solo puede ejecutarse desde la línea de comandos (CLI).\n");
}

require_once dirname(__DIR__) . '/includes/db.php';

echo "============================================================\n";
echo " JUSHSAL · Creación de Usuario Administrador (CLI)\n";
echo "============================================================\n\n";

// Parsear argumentos opcionales si se pasan por CLI
$opciones_cli = getopt('', ['email:', 'nombre:', 'password:']);

$nombre = $opciones_cli['nombre'] ?? null;
$email = $opciones_cli['email'] ?? null;
$password = $opciones_cli['password'] ?? null;

// Función auxiliar para leer entrada en consola de forma compatible con Windows
function solicitar_entrada(string $prompt): string {
    if (function_exists('readline')) {
        $val = readline($prompt);
        if ($val !== false) {
            readline_add_history($val);
            return trim($val);
        }
    }
    echo $prompt;
    $handle = fopen('php://stdin', 'r');
    $linea = fgets($handle);
    fclose($handle);
    return trim($linea ?: '');
}

// 1. Solicitar Nombre
while (empty($nombre)) {
    $nombre = solicitar_entrada("Nombre completo del administrador: ");
    if (empty($nombre)) {
        echo " [!] El nombre no puede estar vacío.\n";
    }
}

// 2. Solicitar Email
while (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $email = solicitar_entrada("Correo electrónico: ");
    if (empty($email)) {
        echo " [!] El correo electrónico no puede estar vacío.\n";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo " [!] Formato de correo electrónico inválido. Ejemplo: admin@jusanlorenzo.org.pe\n";
        $email = null;
    }
}

$db = obtener_db();

// Verificar si el correo ya existe
$stmt = $db->prepare("SELECT id, nombre, rol_id FROM usuarios WHERE email = :email");
$stmt->execute([':email' => $email]);
$usuarioExistente = $stmt->fetch();

if ($usuarioExistente) {
    echo "\n [!] El correo '{$email}' ya se encuentra registrado con ID #{$usuarioExistente['id']} ({$usuarioExistente['nombre']}).\n";
    $confirmar = strtolower(solicitar_entrada("¿Desea actualizar su contraseña a un nuevo valor? (s/n): "));
    if ($confirmar !== 's' && $confirmar !== 'si') {
        echo "Operación cancelada. No se realizaron cambios.\n";
        exit(0);
    }
}

// 3. Solicitar Contraseña
while (empty($password)) {
    $p1 = solicitar_entrada("Contraseña (mínimo 8 caracteres): ");
    if (strlen($p1) < 8) {
        echo " [!] La contraseña debe tener al menos 8 caracteres por seguridad.\n";
        continue;
    }
    $p2 = solicitar_entrada("Confirme la contraseña: ");
    if ($p1 !== $p2) {
        echo " [!] Las contraseñas no coinciden. Intente de nuevo.\n";
        continue;
    }
    $password = $p1;
}

// Obtener ID del rol admin
$stmtRol = $db->prepare("SELECT id FROM roles WHERE nombre = 'admin' LIMIT 1");
$stmtRol->execute();
$rolAdmin = $stmtRol->fetch();

if (!$rolAdmin) {
    // Si no existe, crear o asignar por defecto 1
    $rolId = 1;
} else {
    $rolId = (int)$rolAdmin['id'];
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    if ($usuarioExistente) {
        $stmtUpdate = $db->prepare("
            UPDATE usuarios 
            SET nombre = :nombre, 
                password_hash = :hash, 
                rol_id = :rol_id, 
                activo = 1,
                intentos_fallidos = 0,
                bloqueado_hasta = NULL,
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmtUpdate->execute([
            ':nombre' => $nombre,
            ':hash'   => $passwordHash,
            ':rol_id' => $rolId,
            ':id'     => $usuarioExistente['id']
        ]);

        echo "\n============================================================\n";
        echo " [OK] Usuario administrador actualizado exitosamente.\n";
        echo " ID: {$usuarioExistente['id']}\n";
        echo " Nombre: {$nombre}\n";
        echo " Email: {$email}\n";
        echo " Rol: admin (ID {$rolId})\n";
        echo "============================================================\n\n";
    } else {
        $stmtInsert = $db->prepare("
            INSERT INTO usuarios (rol_id, nombre, email, password_hash, activo, intentos_fallidos)
            VALUES (:rol_id, :nombre, :email, :hash, 1, 0)
        ");
        $stmtInsert->execute([
            ':rol_id' => $rolId,
            ':nombre' => $nombre,
            ':email'  => $email,
            ':hash'   => $passwordHash
        ]);

        $nuevoId = $db->lastInsertId();

        echo "\n============================================================\n";
        echo " [OK] Usuario administrador creado exitosamente.\n";
        echo " ID: {$nuevoId}\n";
        echo " Nombre: {$nombre}\n";
        echo " Email: {$email}\n";
        echo " Rol: admin (ID {$rolId})\n";
        echo "============================================================\n\n";
    }
} catch (PDOException $e) {
    echo "\n [ERROR] Falló la operación en base de datos: " . $e->getMessage() . "\n";
    exit(1);
}
