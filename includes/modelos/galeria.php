<?php
/**
 * JUSHSAL - Modelo de Datos: Galería Fotográfica Institucional
 * Gestión de imágenes, títulos y formato visual destacado (.gallery-wide).
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';
require_once dirname(__DIR__) . '/upload.php';

/**
 * Lista las fotografías activas para la portada pública.
 *
 * @return array
 */
function galeria_listar_publica(): array
{
    $db = obtener_db();
    $stmt = $db->query("
        SELECT id, titulo, descripcion, imagen, es_destacada, orden
        FROM galeria_imagenes
        WHERE activo = 1
        ORDER BY orden ASC, id ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Lista todas las fotografías para el panel administrativo.
 *
 * @return array
 */
function galeria_listar_admin(): array
{
    $db = obtener_db();
    $stmt = $db->query("
        SELECT g.*, u.nombre AS autor_nombre
        FROM galeria_imagenes g
        LEFT JOIN usuarios u ON g.creado_por = u.id
        ORDER BY g.orden ASC, g.id DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Obtiene una fotografía por su ID.
 *
 * @param int $id
 * @return array|null
 */
function galeria_obtener_por_id(int $id): ?array
{
    $db = obtener_db();
    $stmt = $db->prepare("SELECT * FROM galeria_imagenes WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Registra una nueva fotografía en la galería.
 *
 * @param array $datos
 * @return int ID de la imagen creada
 */
function galeria_crear(array $datos): int
{
    $titulo = trim($datos['titulo'] ?? '');
    if ($titulo === '') {
        throw new InvalidArgumentException("El título o pie de foto es obligatorio.");
    }

    if (empty($datos['imagen'])) {
        throw new InvalidArgumentException("Debe seleccionar una imagen para la galería.");
    }

    $db = obtener_db();
    $stmt = $db->prepare("
        INSERT INTO galeria_imagenes (
            titulo, descripcion, imagen, es_destacada, orden, activo, creado_por, created_at
        ) VALUES (
            :titulo, :descripcion, :imagen, :es_destacada, :orden, :activo, :creado_por, NOW()
        )
    ");

    $stmt->execute([
        ':titulo'       => $titulo,
        ':descripcion'  => !empty($datos['descripcion']) ? trim($datos['descripcion']) : null,
        ':imagen'       => $datos['imagen'],
        ':es_destacada' => !empty($datos['es_destacada']) ? 1 : 0,
        ':orden'        => isset($datos['orden']) ? (int)$datos['orden'] : 0,
        ':activo'       => isset($datos['activo']) ? ($datos['activo'] ? 1 : 0) : 1,
        ':creado_por'   => !empty($datos['creado_por']) ? (int)$datos['creado_por'] : null
    ]);

    return (int)$db->lastInsertId();
}

/**
 * Actualiza los datos de una imagen en la galería.
 *
 * @param int   $id
 * @param array $datos
 * @return bool
 */
function galeria_actualizar(int $id, array $datos): bool
{
    $titulo = trim($datos['titulo'] ?? '');
    if ($titulo === '') {
        throw new InvalidArgumentException("El título o pie de foto es obligatorio.");
    }

    $db = obtener_db();
    $sql = "
        UPDATE galeria_imagenes SET
            titulo       = :titulo,
            descripcion  = :descripcion,
            es_destacada = :es_destacada,
            orden        = :orden,
            activo       = :activo,
            updated_at   = NOW()
    ";

    $params = [
        ':id'           => $id,
        ':titulo'       => $titulo,
        ':descripcion'  => !empty($datos['descripcion']) ? trim($datos['descripcion']) : null,
        ':es_destacada' => !empty($datos['es_destacada']) ? 1 : 0,
        ':orden'        => isset($datos['orden']) ? (int)$datos['orden'] : 0,
        ':activo'       => isset($datos['activo']) ? ($datos['activo'] ? 1 : 0) : 1
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
 * Elimina una imagen de la galería y su archivo físico (si está en uploads/).
 *
 * @param int $id
 * @return bool
 */
function galeria_eliminar(int $id): bool
{
    $foto = galeria_obtener_por_id($id);
    if (!$foto) {
        return false;
    }

    // Borrado físico si reside en uploads/
    if (!empty($foto['imagen']) && str_starts_with($foto['imagen'], 'uploads/')) {
        eliminar_imagen($foto['imagen']);
    }

    $db = obtener_db();
    $stmt = $db->prepare("DELETE FROM galeria_imagenes WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Cambia el estado activo/inactivo de una imagen de la galería.
 *
 * @param int $id
 * @param int $activo
 * @return bool
 */
function galeria_cambiar_activo(int $id, int $activo): bool
{
    $db = obtener_db();
    $stmt = $db->prepare("UPDATE galeria_imagenes SET activo = :activo, updated_at = NOW() WHERE id = :id");
    return $stmt->execute([':activo' => $activo ? 1 : 0, ':id' => $id]);
}
