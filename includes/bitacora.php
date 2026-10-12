<?php
/**
 * JUSHSAL - Bitácora de Auditoría
 * Registro centralizado de acciones del sistema y accesos al panel administrativo.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

/**
 * Registra una acción en la tabla de bitácora.
 *
 * @param string $accion     login, login_fallido, crear, editar, eliminar, bloqueo
 * @param string $entidad    usuarios, noticias, convocatorias, gestiones, etc.
 * @param int|null $entidadId
 * @param string|null $detalle
 * @param int|null $usuarioId Si es null, se obtiene del usuario en sesión
 * @return bool
 */
function registrar_bitacora(
    string $accion,
    string $entidad,
    ?int $entidadId = null,
    ?string $detalle = null,
    ?int $usuarioId = null
): bool {
    try {
        if ($usuarioId === null) {
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            $usuarioId = !empty($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
        }

        $ip = obtener_ip();
        $db = obtener_db();

        $stmt = $db->prepare("
            INSERT INTO bitacora (usuario_id, accion, entidad, entidad_id, detalle, ip, created_at)
            VALUES (:usuario_id, :accion, :entidad, :entidad_id, :detalle, :ip, NOW())
        ");

        return $stmt->execute([
            ':usuario_id'  => $usuarioId,
            ':accion'      => mb_substr($accion, 0, 30, 'UTF-8'),
            ':entidad'     => mb_substr($entidad, 0, 40, 'UTF-8'),
            ':entidad_id'  => $entidadId,
            ':detalle'     => $detalle ? mb_substr($detalle, 0, 255, 'UTF-8') : null,
            ':ip'          => mb_substr($ip, 0, 45, 'UTF-8')
        ]);
    } catch (Throwable $e) {
        // La falla de bitácora no debe detener el flujo del sistema, pero se reporta al log
        error_log('Error al registrar en bitácora JUSHSAL: ' . $e->getMessage());
        return false;
    }
}

/**
 * Alias de compatibilidad para registrar_bitacora().
 */
function bitacora_registrar(
    ?int $usuarioId,
    string $accion,
    string $entidad,
    ?int $entidadId = null,
    ?string $detalle = null
): bool {
    return registrar_bitacora($accion, $entidad, $entidadId, $detalle, $usuarioId);
}

/**
 * Obtiene los registros de la bitácora con filtros y paginación.
 *
 * @param array $filtros ['accion' => string, 'entidad' => string, 'usuario_id' => int]
 * @param int   $limite
 * @param int   $offset
 * @return array
 */
function bitacora_listar(array $filtros = [], int $limite = 50, int $offset = 0): array
{
    $db = obtener_db();
    $sql = "
        SELECT b.*, u.nombre AS usuario_nombre, u.email AS usuario_email, r.nombre AS rol_nombre
        FROM bitacora b
        LEFT JOIN usuarios u ON b.usuario_id = u.id
        LEFT JOIN roles r ON u.rol_id = r.id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($filtros['accion'])) {
        $sql .= " AND b.accion = :accion";
        $params[':accion'] = $filtros['accion'];
    }

    if (!empty($filtros['entidad'])) {
        $sql .= " AND b.entidad = :entidad";
        $params[':entidad'] = $filtros['entidad'];
    }

    if (!empty($filtros['usuario_id'])) {
        $sql .= " AND b.usuario_id = :usuario_id";
        $params[':usuario_id'] = (int)$filtros['usuario_id'];
    }

    $sql .= " ORDER BY b.id DESC LIMIT " . (int)$limite . " OFFSET " . (int)$offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Cuenta el total de registros en la bitácora según los filtros.
 *
 * @param array $filtros
 * @return int
 */
function bitacora_contar(array $filtros = []): int
{
    $db = obtener_db();
    $sql = "SELECT COUNT(*) FROM bitacora WHERE 1=1";
    $params = [];

    if (!empty($filtros['accion'])) {
        $sql .= " AND accion = :accion";
        $params[':accion'] = $filtros['accion'];
    }

    if (!empty($filtros['entidad'])) {
        $sql .= " AND entidad = :entidad";
        $params[':entidad'] = $filtros['entidad'];
    }

    if (!empty($filtros['usuario_id'])) {
        $sql .= " AND usuario_id = :usuario_id";
        $params[':usuario_id'] = (int)$filtros['usuario_id'];
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

/**
 * Lista las acciones distintas registradas en la bitácora.
 *
 * @return array
 */
function bitacora_acciones_distintas(): array
{
    $db = obtener_db();
    return $db->query("SELECT DISTINCT accion FROM bitacora ORDER BY accion ASC")->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Lista las entidades distintas registradas en la bitácora.
 *
 * @return array
 */
function bitacora_entidades_distintas(): array
{
    $db = obtener_db();
    return $db->query("SELECT DISTINCT entidad FROM bitacora ORDER BY entidad ASC")->fetchAll(PDO::FETCH_COLUMN);
}
