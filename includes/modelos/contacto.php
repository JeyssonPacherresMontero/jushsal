<?php
/**
 * JUSHSAL - Modelo de Datos: Mensajes de Contacto Institucional
 * Manejo de consultas ciudadanas recibidas desde el portal web.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';

/**
 * Guarda un mensaje de contacto enviado desde el portal web.
 *
 * @param array  $datos
 * @param string $ip
 * @return int ID del mensaje insertado
 */
function contacto_guardar_mensaje(array $datos, string $ip): int
{
    $nombre    = trim($datos['nombre'] ?? '');
    $apellidos = trim($datos['apellidos'] ?? '');
    $email     = trim($datos['email'] ?? '');
    $telefono  = !empty($datos['celular']) ? trim($datos['celular']) : (!empty($datos['telefono']) ? trim($datos['telefono']) : null);
    $mensaje   = trim($datos['mensaje'] ?? '');

    if ($nombre === '' || $apellidos === '') {
        throw new InvalidArgumentException("Los nombres y apellidos son obligatorios.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException("Debe ingresar un correo electrónico válido.");
    }

    if (mb_strlen($mensaje) < 10) {
        throw new InvalidArgumentException("El mensaje de consulta debe tener al menos 10 caracteres.");
    }

    $db = obtener_db();
    $stmt = $db->prepare("
        INSERT INTO mensajes_contacto (
            nombre, apellidos, email, telefono, mensaje, ip, estado, created_at
        ) VALUES (
            :nombre, :apellidos, :email, :telefono, :mensaje, :ip, 'pendiente', NOW()
        )
    ");

    $stmt->execute([
        ':nombre'    => mb_substr($nombre, 0, 100),
        ':apellidos' => mb_substr($apellidos, 0, 100),
        ':email'     => mb_substr($email, 0, 150),
        ':telefono'  => $telefono ? mb_substr($telefono, 0, 25) : null,
        ':mensaje'   => $mensaje,
        ':ip'        => mb_substr($ip, 0, 45)
    ]);

    return (int)$db->lastInsertId();
}

/**
 * Lista los mensajes de contacto con filtros de estado y paginación.
 *
 * @param string|null $filtroEstado 'pendiente'|'atendido'|'archivado' o null para todos
 * @param int         $limite
 * @param int         $offset
 * @return array
 */
function contacto_listar_mensajes(?string $filtroEstado = null, int $limite = 50, int $offset = 0): array
{
    $db = obtener_db();
    $where = [];
    $params = [];

    if ($filtroEstado !== null && in_array($filtroEstado, ['pendiente', 'atendido', 'archivado'], true)) {
        $where[] = "m.estado = :estado";
        $params[':estado'] = $filtroEstado;
    }

    $sqlWhere = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "
        SELECT m.*, u.nombre AS atendido_por_nombre
        FROM mensajes_contacto m
        LEFT JOIN usuarios u ON m.atendido_por = u.id
        {$sqlWhere}
        ORDER BY m.id DESC
        LIMIT " . (int)$limite . " OFFSET " . (int)$offset;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Obtiene un mensaje por su ID.
 *
 * @param int $id
 * @return array|null
 */
function contacto_obtener_por_id(int $id): ?array
{
    $db = obtener_db();
    $stmt = $db->prepare("
        SELECT m.*, u.nombre AS atendido_por_nombre
        FROM mensajes_contacto m
        LEFT JOIN usuarios u ON m.atendido_por = u.id
        WHERE m.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Cambia el estado de un mensaje de contacto y opcionalmente añade notas internas.
 *
 * @param int         $id
 * @param string      $estado 'pendiente'|'atendido'|'archivado'
 * @param string|null $notasAtencion
 * @param int|null    $atendidoPor ID del usuario que atiende
 * @return bool
 */
function contacto_cambiar_estado(int $id, string $estado, ?string $notasAtencion = null, ?int $atendidoPor = null): bool
{
    if (!in_array($estado, ['pendiente', 'atendido', 'archivado'], true)) {
        throw new InvalidArgumentException("Estado no válido.");
    }

    $db = obtener_db();
    $sql = "
        UPDATE mensajes_contacto SET
            estado = :estado,
            updated_at = NOW()
    ";
    $params = [
        ':id'     => $id,
        ':estado' => $estado
    ];

    if ($notasAtencion !== null) {
        $sql .= ", notas_atencion = :notas";
        $params[':notas'] = trim($notasAtencion);
    }

    if ($atendidoPor !== null) {
        $sql .= ", atendido_por = :atendido_por, atendido_at = NOW()";
        $params[':atendido_por'] = $atendidoPor;
    }

    $sql .= " WHERE id = :id";

    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Cuenta la cantidad de mensajes pendientes de atención.
 *
 * @return int
 */
function contacto_contar_pendientes(): int
{
    $db = obtener_db();
    $stmt = $db->query("SELECT COUNT(*) FROM mensajes_contacto WHERE estado = 'pendiente'");
    return (int)$stmt->fetchColumn();
}

/**
 * Cuenta mensajes agrupados por estado.
 *
 * @return array ['total' => int, 'pendiente' => int, 'atendido' => int, 'archivado' => int]
 */
function contacto_conteo_estados(): array
{
    $db = obtener_db();
    $stmt = $db->query("
        SELECT estado, COUNT(*) AS cantidad
        FROM mensajes_contacto
        GROUP BY estado
    ");
    $filas = $stmt->fetchAll();

    $res = [
        'total'     => 0,
        'pendiente' => 0,
        'atendido'  => 0,
        'archivado' => 0
    ];

    foreach ($filas as $f) {
        $est = $f['estado'];
        $cant = (int)$f['cantidad'];
        if (isset($res[$est])) {
            $res[$est] = $cant;
        }
        $res['total'] += $cant;
    }

    return $res;
}

/**
 * Verifica si una IP ha superado el límite de envíos recientes (rate limiting).
 *
 * @param string $ip
 * @param int    $maxEnvios
 * @param int    $minutos
 * @return bool true si excede el límite (bloquear), false si está permitido
 */
function contacto_ip_excede_limite(string $ip, int $maxEnvios = 5, int $minutos = 10): bool
{
    $db = obtener_db();
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM mensajes_contacto
        WHERE ip = :ip
          AND created_at >= DATE_SUB(NOW(), INTERVAL :minutos MINUTE)
    ");
    $stmt->execute([
        ':ip'      => $ip,
        ':minutos' => $minutos
    ]);
    $conteo = (int)$stmt->fetchColumn();
    return $conteo >= $maxEnvios;
}
