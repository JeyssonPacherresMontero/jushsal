<?php
/**
 * JUSHSAL - Modelo de Datos: Convocatorias a Asamblea (Popup)
 * Manejo de vigencias temporales en servidor, frecuencias y tiempos de despliegue.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';
require_once dirname(__DIR__) . '/upload.php';

/**
 * Obtiene la convocatoria actualmente activa y vigente en el servidor.
 * La vigencia se calcula estrictamente con NOW() según la zona horaria del Perú.
 *
 * @return array|null
 */
function convocatoria_obtener_activa(): ?array
{
    $db = obtener_db();
    $stmt = $db->query("
        SELECT * FROM convocatorias
        WHERE activo = 1 
          AND NOW() BETWEEN mostrar_desde AND mostrar_hasta
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Lista todas las convocatorias para el panel administrativo, calculando si están vigentes ahora.
 *
 * @return array
 */
function convocatorias_listar_admin(): array
{
    $db = obtener_db();
    $stmt = $db->query("
        SELECT c.*, u.nombre AS autor_nombre,
               (CASE WHEN c.activo = 1 AND NOW() BETWEEN c.mostrar_desde AND c.mostrar_hasta THEN 1 ELSE 0 END) AS vigente_ahora
        FROM convocatorias c
        LEFT JOIN usuarios u ON c.creado_por = u.id
        ORDER BY c.id DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Obtiene una convocatoria por su ID.
 *
 * @param int $id
 * @return array|null
 */
function convocatorias_obtener_por_id(int $id): ?array
{
    $db = obtener_db();
    $stmt = $db->prepare("SELECT * FROM convocatorias WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Registra una nueva convocatoria a asamblea en la base de datos.
 *
 * @param array $datos
 * @return int ID de la convocatoria creada
 */
function convocatorias_crear(array $datos): int
{
    $db = obtener_db();

    $desde = $datos['mostrar_desde'];
    $hasta = $datos['mostrar_hasta'];

    if (strtotime($hasta) <= strtotime($desde)) {
        throw new InvalidArgumentException("La fecha de fin de vigencia debe ser posterior a la fecha de inicio.");
    }

    $retraso = isset($datos['retraso_seg']) ? max(0, min(60, (int)$datos['retraso_seg'])) : 3;
    $duracion = isset($datos['duracion_seg']) ? max(0, min(300, (int)$datos['duracion_seg'])) : 0;
    $frecuencia = in_array($datos['frecuencia'] ?? '', ['cada_visita', 'una_vez_por_sesion', 'una_vez_por_dia'], true)
        ? $datos['frecuencia']
        : 'una_vez_por_sesion';

    $stmt = $db->prepare("
        INSERT INTO convocatorias (
            titulo, imagen, fecha_asamblea, mostrar_desde, mostrar_hasta,
            retraso_seg, duracion_seg, frecuencia, activo, creado_por, created_at
        ) VALUES (
            :titulo, :imagen, :fecha_asamblea, :mostrar_desde, :mostrar_hasta,
            :retraso_seg, :duracion_seg, :frecuencia, :activo, :creado_por, NOW()
        )
    ");

    $stmt->execute([
        ':titulo'         => trim($datos['titulo']),
        ':imagen'         => $datos['imagen'],
        ':fecha_asamblea' => !empty($datos['fecha_asamblea']) ? $datos['fecha_asamblea'] : null,
        ':mostrar_desde'  => $desde,
        ':mostrar_hasta'  => $hasta,
        ':retraso_seg'    => $retraso,
        ':duracion_seg'   => $duracion,
        ':frecuencia'     => $frecuencia,
        ':activo'         => !empty($datos['activo']) ? 1 : 0,
        ':creado_por'     => !empty($datos['creado_por']) ? (int)$datos['creado_por'] : null
    ]);

    return (int)$db->lastInsertId();
}

/**
 * Actualiza los datos de una convocatoria existente.
 *
 * @param int   $id
 * @param array $datos
 * @return bool
 */
function convocatorias_actualizar(int $id, array $datos): bool
{
    $db = obtener_db();

    $desde = $datos['mostrar_desde'];
    $hasta = $datos['mostrar_hasta'];

    if (strtotime($hasta) <= strtotime($desde)) {
        throw new InvalidArgumentException("La fecha de fin de vigencia debe ser posterior a la fecha de inicio.");
    }

    $retraso = isset($datos['retraso_seg']) ? max(0, min(60, (int)$datos['retraso_seg'])) : 3;
    $duracion = isset($datos['duracion_seg']) ? max(0, min(300, (int)$datos['duracion_seg'])) : 0;
    $frecuencia = in_array($datos['frecuencia'] ?? '', ['cada_visita', 'una_vez_por_sesion', 'una_vez_por_dia'], true)
        ? $datos['frecuencia']
        : 'una_vez_por_sesion';

    $sql = "
        UPDATE convocatorias SET
            titulo         = :titulo,
            fecha_asamblea = :fecha_asamblea,
            mostrar_desde  = :mostrar_desde,
            mostrar_hasta  = :mostrar_hasta,
            retraso_seg    = :retraso_seg,
            duracion_seg   = :duracion_seg,
            frecuencia     = :frecuencia,
            activo         = :activo,
            updated_at     = NOW()
    ";

    $params = [
        ':id'             => $id,
        ':titulo'         => trim($datos['titulo']),
        ':fecha_asamblea' => !empty($datos['fecha_asamblea']) ? $datos['fecha_asamblea'] : null,
        ':mostrar_desde'  => $desde,
        ':mostrar_hasta'  => $hasta,
        ':retraso_seg'    => $retraso,
        ':duracion_seg'   => $duracion,
        ':frecuencia'     => $frecuencia,
        ':activo'         => !empty($datos['activo']) ? 1 : 0
    ];

    if (!empty($datos['imagen'])) {
        $sql .= ", imagen = :imagen";
        $params[':imagen'] = $datos['imagen'];
    }

    $sql .= " WHERE id = :id";

    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Cambia el estado activo/inactivo de una convocatoria.
 *
 * @param int $id
 * @param int $activo
 * @return bool
 */
function convocatorias_cambiar_activo(int $id, int $activo): bool
{
    $db = obtener_db();
    $stmt = $db->prepare("UPDATE convocatorias SET activo = :activo, updated_at = NOW() WHERE id = :id");
    return $stmt->execute([':activo' => $activo ? 1 : 0, ':id' => $id]);
}

/**
 * Elimina una convocatoria y su imagen física.
 *
 * @param int $id
 * @return bool
 */
function convocatorias_eliminar(int $id): bool
{
    $conv = convocatorias_obtener_por_id($id);
    if (!$conv) {
        return false;
    }

    if (!empty($conv['imagen'])) {
        eliminar_imagen($conv['imagen']);
    }

    $db = obtener_db();
    $stmt = $db->prepare("DELETE FROM convocatorias WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}
