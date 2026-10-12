<?php
/**
 * JUSHSAL - Modelo de Datos: Comisiones de Usuarios (16 Sub Sectores)
 * Gestión de comisiones y presidentes/delegados de sub sector.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';

/**
 * Lista las comisiones activas para la web pública.
 * Por política de privacidad y seguridad, NUNCA expone números de celular ni datos privados.
 *
 * @return array
 */
function comisiones_listar_publicas(): array
{
    $db = obtener_db();
    $stmt = $db->query("
        SELECT c.id, c.numero, c.nombre,
               cp.nombre_completo AS presidente_nombre,
               cp.periodo_inicio,
               cp.periodo_fin
        FROM comisiones c
        LEFT JOIN (
            SELECT cp1.*
            FROM comision_presidentes cp1
            INNER JOIN (
                SELECT comision_id, MAX(id) AS max_id
                FROM comision_presidentes
                GROUP BY comision_id
            ) cp2 ON cp1.id = cp2.max_id
        ) cp ON c.id = cp.comision_id
        WHERE c.activo = 1
        ORDER BY c.numero ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Lista todas las comisiones para el panel administrativo, incluyendo datos de contacto.
 *
 * @return array
 */
function comisiones_listar_admin(): array
{
    $db = obtener_db();
    $stmt = $db->query("
        SELECT c.id, c.numero, c.nombre, c.activo,
               cp.id AS presidente_id,
               cp.nombre_completo AS presidente_nombre,
               cp.periodo_inicio,
               cp.periodo_fin,
               cp.celular
        FROM comisiones c
        LEFT JOIN (
            SELECT cp1.*
            FROM comision_presidentes cp1
            INNER JOIN (
                SELECT comision_id, MAX(id) AS max_id
                FROM comision_presidentes
                GROUP BY comision_id
            ) cp2 ON cp1.id = cp2.max_id
        ) cp ON c.id = cp.comision_id
        ORDER BY c.numero ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Obtiene los detalles de una comisión por su ID.
 *
 * @param int $id
 * @return array|null
 */
function comisiones_obtener_por_id(int $id): ?array
{
    $db = obtener_db();
    $stmt = $db->prepare("
        SELECT c.id, c.numero, c.nombre, c.activo,
               cp.id AS presidente_id,
               cp.nombre_completo AS presidente_nombre,
               cp.periodo_inicio,
               cp.periodo_fin,
               cp.celular
        FROM comisiones c
        LEFT JOIN (
            SELECT cp1.*
            FROM comision_presidentes cp1
            INNER JOIN (
                SELECT comision_id, MAX(id) AS max_id
                FROM comision_presidentes
                GROUP BY comision_id
            ) cp2 ON cp1.id = cp2.max_id
        ) cp ON c.id = cp.comision_id
        WHERE c.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Actualiza la información básica de una comisión (nombre y estado activo).
 *
 * @param int   $id
 * @param array $datos
 * @return bool
 */
function comisiones_actualizar(int $id, array $datos): bool
{
    $db = obtener_db();
    $stmt = $db->prepare("
        UPDATE comisiones
        SET nombre = :nombre,
            activo = :activo
        WHERE id = :id
    ");
    return $stmt->execute([
        ':id'     => $id,
        ':nombre' => trim($datos['nombre']),
        ':activo' => !empty($datos['activo']) ? 1 : 0
    ]);
}

/**
 * Guarda o actualiza los datos del presidente de una comisión.
 *
 * @param int   $comisionId
 * @param array $datos
 * @return bool
 */
function comision_presidente_guardar(int $comisionId, array $datos): bool
{
    $db = obtener_db();
    $nombreCompleto = trim($datos['nombre_completo'] ?? '');

    // Si se envía nombre vacío y se desea limpiar el presidente
    if ($nombreCompleto === '') {
        $stmtDel = $db->prepare("DELETE FROM comision_presidentes WHERE comision_id = :comision_id");
        return $stmtDel->execute([':comision_id' => $comisionId]);
    }

    $periodoInicio = !empty($datos['periodo_inicio']) ? (int)$datos['periodo_inicio'] : 2026;
    $periodoFin = !empty($datos['periodo_fin']) ? (int)$datos['periodo_fin'] : 2029;
    $celular = !empty($datos['celular']) ? trim($datos['celular']) : null;

    if ($periodoFin < $periodoInicio) {
        throw new InvalidArgumentException("El año de fin del periodo debe ser mayor o igual al año de inicio.");
    }

    // Verificar si ya existe registro de presidente para esta comisión
    $stmtCheck = $db->prepare("SELECT id FROM comision_presidentes WHERE comision_id = :comision_id LIMIT 1");
    $stmtCheck->execute([':comision_id' => $comisionId]);
    $existente = $stmtCheck->fetch();

    if ($existente) {
        $stmtUpd = $db->prepare("
            UPDATE comision_presidentes
            SET nombre_completo = :nombre_completo,
                periodo_inicio  = :periodo_inicio,
                periodo_fin     = :periodo_fin,
                celular         = :celular
            WHERE id = :id
        ");
        return $stmtUpd->execute([
            ':id'              => (int)$existente['id'],
            ':nombre_completo' => $nombreCompleto,
            ':periodo_inicio'  => $periodoInicio,
            ':periodo_fin'     => $periodoFin,
            ':celular'         => $celular
        ]);
    } else {
        $stmtIns = $db->prepare("
            INSERT INTO comision_presidentes (
                comision_id, nombre_completo, periodo_inicio, periodo_fin, celular, created_at
            ) VALUES (
                :comision_id, :nombre_completo, :periodo_inicio, :periodo_fin, :celular, NOW()
            )
        ");
        return $stmtIns->execute([
            ':comision_id'     => $comisionId,
            ':nombre_completo' => $nombreCompleto,
            ':periodo_inicio'  => $periodoInicio,
            ':periodo_fin'     => $periodoFin,
            ':celular'         => $celular
        ]);
    }
}
