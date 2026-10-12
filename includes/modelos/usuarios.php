<?php
/**
 * JUSHSAL - Modelo de Datos: Usuarios del Panel Administrativo
 * Gestión de usuarios, roles, contraseñas y permisos del backoffice.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';

/**
 * Lista todos los usuarios registrados en el panel con su rol asignado.
 *
 * @return array
 */
function usuarios_listar(): array
{
    $db = obtener_db();
    $stmt = $db->query("
        SELECT u.id, u.rol_id, u.nombre, u.email, u.activo, u.ultimo_acceso, u.created_at,
               r.nombre AS rol_nombre, r.descripcion AS rol_descripcion
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id
        ORDER BY u.id ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Obtiene los datos de un usuario por su ID.
 *
 * @param int $id
 * @return array|null
 */
function usuarios_obtener_por_id(int $id): ?array
{
    $db = obtener_db();
    $stmt = $db->prepare("
        SELECT u.id, u.rol_id, u.nombre, u.email, u.activo, u.ultimo_acceso, u.created_at,
               r.nombre AS rol_nombre
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id
        WHERE u.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Obtiene un usuario por su dirección de correo electrónico.
 *
 * @param string $email
 * @return array|null
 */
function usuarios_obtener_por_email(string $email): ?array
{
    $db = obtener_db();
    $stmt = $db->prepare("
        SELECT id, email FROM usuarios WHERE email = :email LIMIT 1
    ");
    $stmt->execute([':email' => mb_strtolower(trim($email), 'UTF-8')]);
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Registra un nuevo usuario en el panel administrativo.
 *
 * @param array $datos
 * @return int ID del nuevo usuario
 */
function usuarios_crear(array $datos): int
{
    $db = obtener_db();
    $email = mb_strtolower(trim($datos['email']), 'UTF-8');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException("El formato del correo electrónico es inválido.");
    }

    if (usuarios_obtener_por_email($email)) {
        throw new InvalidArgumentException("Ya existe un usuario registrado con ese correo electrónico.");
    }

    $nombre = trim($datos['nombre']);
    if ($nombre === '') {
        throw new InvalidArgumentException("El nombre del usuario no puede estar vacío.");
    }

    $password = $datos['password'] ?? '';
    if (strlen($password) < 8) {
        throw new InvalidArgumentException("La contraseña debe tener al menos 8 caracteres.");
    }

    $rolId = (int)($datos['rol_id'] ?? 2);
    $activo = !empty($datos['activo']) ? 1 : 0;
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        INSERT INTO usuarios (rol_id, nombre, email, password_hash, activo, created_at)
        VALUES (:rol_id, :nombre, :email, :password_hash, :activo, NOW())
    ");
    $stmt->execute([
        ':rol_id'        => $rolId,
        ':nombre'        => $nombre,
        ':email'         => $email,
        ':password_hash' => $passwordHash,
        ':activo'        => $activo
    ]);

    return (int)$db->lastInsertId();
}

/**
 * Cambia el estado activo/inactivo de un usuario.
 *
 * @param int $id
 * @param int $activo
 * @return bool
 */
function usuarios_cambiar_estado(int $id, int $activo): bool
{
    $db = obtener_db();
    $stmt = $db->prepare("UPDATE usuarios SET activo = :activo, updated_at = NOW() WHERE id = :id");
    return $stmt->execute([
        ':id'     => $id,
        ':activo' => $activo ? 1 : 0
    ]);
}

/**
 * Actualiza la contraseña de un usuario mediante hash seguro.
 *
 * @param int    $id
 * @param string $nuevaPassword
 * @return bool
 */
function usuarios_cambiar_password(int $id, string $nuevaPassword): bool
{
    if (strlen($nuevaPassword) < 8) {
        throw new InvalidArgumentException("La nueva contraseña debe tener al menos 8 caracteres.");
    }

    $db = obtener_db();
    $passwordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE usuarios SET password_hash = :hash, intentos_fallidos = 0, bloqueado_hasta = NULL, updated_at = NOW() WHERE id = :id");
    return $stmt->execute([
        ':id'   => $id,
        ':hash' => $passwordHash
    ]);
}

/**
 * Lista los roles disponibles en el sistema.
 *
 * @return array
 */
function usuarios_listar_roles(): array
{
    $db = obtener_db();
    return $db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
}
