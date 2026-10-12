<?php
/**
 * JUSHSAL - Modelo de Datos: Gestiones Presidenciales y Logros
 * Gestión de periodos de gobierno institucional y registro de hitos por año.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';
require_once dirname(__DIR__) . '/upload.php';

/**
 * Obtiene la gestión presidencial actualmente marcada como vigente.
 *
 * @return array|null
 */
function gestiones_obtener_vigente(): ?array
{
    $db = obtener_db();
    $stmt = $db->query("SELECT * FROM gestiones_presidenciales WHERE es_vigente = 1 LIMIT 1");
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Lista las gestiones anteriores (no vigentes) ordenadas cronológicamente hacia atrás.
 *
 * @return array
 */
function gestiones_listar_anteriores(): array
{
    $db = obtener_db();
    $stmt = $db->query("
        SELECT * FROM gestiones_presidenciales 
        WHERE es_vigente = 0 
        ORDER BY periodo_inicio DESC, id DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Lista todas las gestiones para el panel administrativo, incluyendo el total de logros registrados.
 *
 * @return array
 */
function gestiones_listar_todas(): array
{
    $db = obtener_db();
    $stmt = $db->query("
        SELECT g.*, 
               (SELECT COUNT(*) FROM logros_gestion l WHERE l.gestion_id = g.id) AS total_logros
        FROM gestiones_presidenciales g
        ORDER BY g.es_vigente DESC, g.periodo_inicio DESC, g.id DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Obtiene una gestión presidencial por su ID.
 *
 * @param int $id
 * @return array|null
 */
function gestiones_obtener_por_id(int $id): ?array
{
    $db = obtener_db();
    $stmt = $db->prepare("SELECT * FROM gestiones_presidenciales WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Inserta una nueva gestión presidencial.
 * Si se marca como vigente, asegura atómicamente que las demás pasen a no vigentes.
 *
 * @param array $datos
 * @return int ID de la gestión creada
 */
function gestiones_crear(array $datos): int
{
    $db = obtener_db();
    $esVigente = !empty($datos['es_vigente']) ? 1 : 0;

    $inicio = (int)$datos['periodo_inicio'];
    $fin = !empty($datos['periodo_fin']) ? (int)$datos['periodo_fin'] : null;

    if ($fin !== null && $fin < $inicio) {
        throw new InvalidArgumentException("El año de fin ({$fin}) no puede ser menor que el año de inicio ({$inicio}).");
    }

    try {
        $db->beginTransaction();

        if ($esVigente === 1) {
            $db->exec("UPDATE gestiones_presidenciales SET es_vigente = 0");
        }

        $stmt = $db->prepare("
            INSERT INTO gestiones_presidenciales (
                presidente_nombre, periodo_inicio, periodo_fin,
                foto, semblanza, es_vigente, created_at
            ) VALUES (
                :nombre, :inicio, :fin,
                :foto, :semblanza, :es_vigente, NOW()
            )
        ");

        $stmt->execute([
            ':nombre'     => trim($datos['presidente_nombre']),
            ':inicio'     => $inicio,
            ':fin'        => $fin,
            ':foto'       => $datos['foto'] ?? null,
            ':semblanza'  => !empty($datos['semblanza']) ? trim($datos['semblanza']) : null,
            ':es_vigente' => $esVigente
        ]);

        $nuevoId = (int)$db->lastInsertId();
        $db->commit();

        return $nuevoId;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

/**
 * Actualiza los datos de una gestión presidencial.
 *
 * @param int   $id
 * @param array $datos
 * @return bool
 */
function gestiones_actualizar(int $id, array $datos): bool
{
    $db = obtener_db();
    $esVigente = !empty($datos['es_vigente']) ? 1 : 0;

    $inicio = (int)$datos['periodo_inicio'];
    $fin = !empty($datos['periodo_fin']) ? (int)$datos['periodo_fin'] : null;

    if ($fin !== null && $fin < $inicio) {
        throw new InvalidArgumentException("El año de fin ({$fin}) no puede ser menor que el año de inicio ({$inicio}).");
    }

    try {
        $db->beginTransaction();

        if ($esVigente === 1) {
            $stmtReset = $db->prepare("UPDATE gestiones_presidenciales SET es_vigente = 0 WHERE id != :id");
            $stmtReset->execute([':id' => $id]);
        }

        $sql = "
            UPDATE gestiones_presidenciales SET
                presidente_nombre = :nombre,
                periodo_inicio    = :inicio,
                periodo_fin       = :fin,
                semblanza         = :semblanza,
                es_vigente        = :es_vigente,
                updated_at        = NOW()
        ";

        $params = [
            ':id'         => $id,
            ':nombre'     => trim($datos['presidente_nombre']),
            ':inicio'     => $inicio,
            ':fin'        => $fin,
            ':semblanza'  => !empty($datos['semblanza']) ? trim($datos['semblanza']) : null,
            ':es_vigente' => $esVigente
        ];

        if (array_key_exists('foto', $datos)) {
            $sql .= ", foto = :foto";
            $params[':foto'] = $datos['foto'];
        }

        $sql .= " WHERE id = :id";

        $stmt = $db->prepare($sql);
        $res = $stmt->execute($params);

        $db->commit();
        return $res;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

/**
 * Marca una gestión específica como vigente y desmarca atómicamente todas las demás.
 *
 * @param int $id
 * @return bool
 */
function gestiones_marcar_vigente(int $id): bool
{
    $db = obtener_db();
    try {
        $db->beginTransaction();
        $db->exec("UPDATE gestiones_presidenciales SET es_vigente = 0");
        $stmt = $db->prepare("UPDATE gestiones_presidenciales SET es_vigente = 1, updated_at = NOW() WHERE id = :id");
        $res = $stmt->execute([':id' => $id]);
        $db->commit();
        return $res;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

/**
 * Elimina una gestión presidencial y sus fotos asociadas.
 *
 * @param int $id
 * @return bool
 */
function gestiones_eliminar(int $id): bool
{
    $gestion = gestiones_obtener_por_id($id);
    if (!$gestion) {
        return false;
    }

    // 1. Limpiar foto de la gestión si existe
    if (!empty($gestion['foto'])) {
        eliminar_imagen($gestion['foto']);
    }

    // 2. Limpiar fotos de los logros hijos
    $logros = logros_listar_por_gestion($id);
    foreach ($logros as $l) {
        if (!empty($l['imagen'])) {
            eliminar_imagen($l['imagen']);
        }
    }

    // 3. Eliminar registro en base de datos (ON DELETE CASCADE limpia los logros)
    $db = obtener_db();
    $stmt = $db->prepare("DELETE FROM gestiones_presidenciales WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Lista todos los logros de una gestión específica ordenados cronológicamente por año.
 *
 * @param int $gestionId
 * @return array
 */
function logros_listar_por_gestion(int $gestionId): array
{
    $db = obtener_db();
    $stmt = $db->prepare("
        SELECT * FROM logros_gestion 
        WHERE gestion_id = :gid 
        ORDER BY anio DESC, orden ASC, id ASC
    ");
    $stmt->execute([':gid' => $gestionId]);
    return $stmt->fetchAll();
}

/**
 * Agrupa una lista de logros en un arreglo asociativo por año: [2026 => [...], 2027 => [...]].
 *
 * @param array $logros
 * @return array
 */
function logros_agrupar_por_anio(array $logros): array
{
    $agrupados = [];
    foreach ($logros as $l) {
        $anio = (int)$l['anio'];
        if (!isset($agrupados[$anio])) {
            $agrupados[$anio] = [];
        }
        $agrupados[$anio][] = $l;
    }
    krsort($agrupados); // De año más reciente a más antiguo
    return $agrupados;
}

/**
 * Obtiene un logro específico por su ID.
 *
 * @param int $id
 * @return array|null
 */
function logros_obtener_por_id(int $id): ?array
{
    $db = obtener_db();
    $stmt = $db->prepare("SELECT * FROM logros_gestion WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Inserta un nuevo logro dentro de una gestión.
 *
 * @param array $datos
 * @return int ID del logro creado
 */
function logros_crear(array $datos): int
{
    $gestion = gestiones_obtener_por_id((int)$datos['gestion_id']);
    if (!$gestion) {
        throw new InvalidArgumentException("La gestión institucional asociada no existe.");
    }

    $anio = (int)$datos['anio'];
    $inicio = (int)$gestion['periodo_inicio'];
    $fin = !empty($gestion['periodo_fin']) ? (int)$gestion['periodo_fin'] : (int)date('Y');

    if ($anio < $inicio || $anio > $fin) {
        throw new RangeException("El año del logro ({$anio}) debe estar dentro del periodo de la gestión ({$inicio} - {$fin}).");
    }

    $db = obtener_db();
    $stmt = $db->prepare("
        INSERT INTO logros_gestion (
            gestion_id, anio, titulo, descripcion, imagen, orden, created_at
        ) VALUES (
            :gestion_id, :anio, :titulo, :descripcion, :imagen, :orden, NOW()
        )
    ");

    $stmt->execute([
        ':gestion_id'   => (int)$datos['gestion_id'],
        ':anio'         => $anio,
        ':titulo'       => trim($datos['titulo']),
        ':descripcion'  => !empty($datos['descripcion']) ? trim($datos['descripcion']) : null,
        ':imagen'       => $datos['imagen'] ?? null,
        ':orden'        => !empty($datos['orden']) ? (int)$datos['orden'] : 0
    ]);

    return (int)$db->lastInsertId();
}

/**
 * Actualiza un logro existente.
 *
 * @param int   $id
 * @param array $datos
 * @return bool
 */
function logros_actualizar(int $id, array $datos): bool
{
    $logro = logros_obtener_por_id($id);
    if (!$logro) {
        return false;
    }

    $gestion = gestiones_obtener_por_id((int)$logro['gestion_id']);
    $anio = (int)$datos['anio'];
    $inicio = (int)$gestion['periodo_inicio'];
    $fin = !empty($gestion['periodo_fin']) ? (int)$gestion['periodo_fin'] : (int)date('Y');

    if ($anio < $inicio || $anio > $fin) {
        throw new RangeException("El año del logro ({$anio}) debe estar dentro del periodo de la gestión ({$inicio} - {$fin}).");
    }

    $db = obtener_db();
    $sql = "
        UPDATE logros_gestion SET
            anio        = :anio,
            titulo      = :titulo,
            descripcion = :descripcion,
            orden       = :orden,
            updated_at  = NOW()
    ";

    $params = [
        ':id'          => $id,
        ':anio'        => $anio,
        ':titulo'      => trim($datos['titulo']),
        ':descripcion' => !empty($datos['descripcion']) ? trim($datos['descripcion']) : null,
        ':orden'       => !empty($datos['orden']) ? (int)$datos['orden'] : 0
    ];

    if (array_key_exists('imagen', $datos)) {
        $sql .= ", imagen = :imagen";
        $params[':imagen'] = $datos['imagen'];
    }

    $sql .= " WHERE id = :id";

    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Elimina un logro y su imagen física.
 *
 * @param int $id
 * @return bool
 */
function logros_eliminar(int $id): bool
{
    $logro = logros_obtener_por_id($id);
    if (!$logro) {
        return false;
    }

    if (!empty($logro['imagen'])) {
        eliminar_imagen($logro['imagen']);
    }

    $db = obtener_db();
    $stmt = $db->prepare("DELETE FROM logros_gestion WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}
