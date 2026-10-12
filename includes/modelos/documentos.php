<?php
/**
 * JUSHSAL - Modelo de Datos: Documentos Institucionales y Estados Financieros
 * Gestión de normatividad, resoluciones ANA, tarifas y balances auditados.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';
require_once dirname(__DIR__) . '/upload_documento.php';

/**
 * Lista todas las categorías activas de documentos.
 *
 * @return array
 */
function categorias_documento_listar(): array
{
    $db = obtener_db();
    $stmt = $db->query("
        SELECT * FROM categorias_documento
        WHERE activo = 1
        ORDER BY orden ASC, id ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Obtiene una categoría de documento por su ID.
 *
 * @param int $id
 * @return array|null
 */
function categorias_documento_obtener_por_id(int $id): ?array
{
    $db = obtener_db();
    $stmt = $db->prepare("SELECT * FROM categorias_documento WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Lista los documentos públicos activos con filtros opcionales.
 *
 * @param int|null    $categoriaId
 * @param int|null    $anio
 * @param string|null $busqueda
 * @return array
 */
function documentos_listar_publicos(?int $categoriaId = null, ?int $anio = null, ?string $busqueda = null): array
{
    $db = obtener_db();
    $sql = "
        SELECT d.*, c.nombre AS categoria_nombre, c.slug AS categoria_slug
        FROM documentos d
        INNER JOIN categorias_documento c ON d.categoria_id = c.id
        WHERE d.activo = 1 AND c.activo = 1
    ";
    $params = [];

    if (!empty($categoriaId)) {
        $sql .= " AND d.categoria_id = :categoria_id";
        $params[':categoria_id'] = $categoriaId;
    }

    if (!empty($anio)) {
        $sql .= " AND d.anio = :anio";
        $params[':anio'] = $anio;
    }

    if (!empty($busqueda)) {
        $sql .= " AND (d.titulo LIKE :busq1 OR d.descripcion LIKE :busq2)";
        $params[':busq1'] = '%' . trim($busqueda) . '%';
        $params[':busq2'] = '%' . trim($busqueda) . '%';
    }

    $sql .= " ORDER BY d.es_destacado DESC, d.orden ASC, d.id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Lista los documentos destacados para la portada institucional (index.php).
 *
 * @param int $limite
 * @return array
 */
function documentos_listar_destacados(int $limite = 4): array
{
    $db = obtener_db();
    $stmt = $db->prepare("
        SELECT d.*, c.nombre AS categoria_nombre
        FROM documentos d
        INNER JOIN categorias_documento c ON d.categoria_id = c.id
        WHERE d.activo = 1 AND d.es_destacado = 1 AND c.activo = 1
        ORDER BY d.orden ASC, d.id DESC
        LIMIT :limite
    ");
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Lista todos los documentos para el panel administrativo con filtros.
 *
 * @param array $filtros ['categoria_id' => int, 'busqueda' => string]
 * @return array
 */
function documentos_listar_admin(array $filtros = []): array
{
    $db = obtener_db();
    $sql = "
        SELECT d.*, c.nombre AS categoria_nombre, u.nombre AS autor_nombre
        FROM documentos d
        INNER JOIN categorias_documento c ON d.categoria_id = c.id
        LEFT JOIN usuarios u ON d.creado_por = u.id
        WHERE 1=1
    ";
    $params = [];

    if (!empty($filtros['categoria_id'])) {
        $sql .= " AND d.categoria_id = :categoria_id";
        $params[':categoria_id'] = (int)$filtros['categoria_id'];
    }

    if (!empty($filtros['busqueda'])) {
        $sql .= " AND (d.titulo LIKE :busq1 OR d.descripcion LIKE :busq2)";
        $params[':busq1'] = '%' . trim($filtros['busqueda']) . '%';
        $params[':busq2'] = '%' . trim($filtros['busqueda']) . '%';
    }

    $sql .= " ORDER BY d.id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Obtiene un documento por su ID.
 *
 * @param int $id
 * @return array|null
 */
function documentos_obtener_por_id(int $id): ?array
{
    $db = obtener_db();
    $stmt = $db->prepare("
        SELECT d.*, c.nombre AS categoria_nombre
        FROM documentos d
        INNER JOIN categorias_documento c ON d.categoria_id = c.id
        WHERE d.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Registra un nuevo documento en la base de datos.
 *
 * @param array $datos
 * @return int ID del documento creado
 */
function documentos_crear(array $datos): int
{
    $titulo = trim($datos['titulo'] ?? '');
    if ($titulo === '') {
        throw new InvalidArgumentException("El título del documento es obligatorio.");
    }

    if (empty($datos['categoria_id'])) {
        throw new InvalidArgumentException("Debe seleccionar una categoría para el documento.");
    }

    if (empty($datos['archivo_ruta'])) {
        throw new InvalidArgumentException("Debe adjuntar un archivo PDF válido.");
    }

    $db = obtener_db();
    $stmt = $db->prepare("
        INSERT INTO documentos (
            categoria_id, titulo, descripcion, archivo_ruta, nombre_original,
            formato, tamanio_bytes, anio, es_destacado, orden, activo, creado_por, created_at
        ) VALUES (
            :categoria_id, :titulo, :descripcion, :archivo_ruta, :nombre_original,
            :formato, :tamanio_bytes, :anio, :es_destacado, :orden, :activo, :creado_por, NOW()
        )
    ");

    $stmt->execute([
        ':categoria_id'    => (int)$datos['categoria_id'],
        ':titulo'          => $titulo,
        ':descripcion'     => !empty($datos['descripcion']) ? trim($datos['descripcion']) : null,
        ':archivo_ruta'    => $datos['archivo_ruta'],
        ':nombre_original' => !empty($datos['nombre_original']) ? $datos['nombre_original'] : basename($datos['archivo_ruta']),
        ':formato'         => !empty($datos['formato']) ? $datos['formato'] : 'PDF',
        ':tamanio_bytes'   => !empty($datos['tamanio_bytes']) ? (int)$datos['tamanio_bytes'] : null,
        ':anio'            => !empty($datos['anio']) ? (int)$datos['anio'] : (int)date('Y'),
        ':es_destacado'    => !empty($datos['es_destacado']) ? 1 : 0,
        ':orden'           => isset($datos['orden']) ? (int)$datos['orden'] : 0,
        ':activo'          => isset($datos['activo']) ? ($datos['activo'] ? 1 : 0) : 1,
        ':creado_por'      => !empty($datos['creado_por']) ? (int)$datos['creado_por'] : null
    ]);

    return (int)$db->lastInsertId();
}

/**
 * Actualiza los datos de un documento existente.
 *
 * @param int   $id
 * @param array $datos
 * @return bool
 */
function documentos_actualizar(int $id, array $datos): bool
{
    $titulo = trim($datos['titulo'] ?? '');
    if ($titulo === '') {
        throw new InvalidArgumentException("El título del documento es obligatorio.");
    }

    $db = obtener_db();
    $sql = "
        UPDATE documentos SET
            categoria_id = :categoria_id,
            titulo       = :titulo,
            descripcion  = :descripcion,
            anio         = :anio,
            es_destacado = :es_destacado,
            orden        = :orden,
            activo       = :activo,
            updated_at   = NOW()
    ";

    $params = [
        ':id'           => $id,
        ':categoria_id' => (int)$datos['categoria_id'],
        ':titulo'       => $titulo,
        ':descripcion'  => !empty($datos['descripcion']) ? trim($datos['descripcion']) : null,
        ':anio'         => !empty($datos['anio']) ? (int)$datos['anio'] : (int)date('Y'),
        ':es_destacado' => !empty($datos['es_destacado']) ? 1 : 0,
        ':orden'        => isset($datos['orden']) ? (int)$datos['orden'] : 0,
        ':activo'       => isset($datos['activo']) ? ($datos['activo'] ? 1 : 0) : 1
    ];

    if (!empty($datos['archivo_ruta'])) {
        $sql .= ", archivo_ruta = :archivo_ruta, nombre_original = :nombre_original, tamanio_bytes = :tamanio_bytes";
        $params[':archivo_ruta']    = $datos['archivo_ruta'];
        $params[':nombre_original'] = !empty($datos['nombre_original']) ? $datos['nombre_original'] : basename($datos['archivo_ruta']);
        $params[':tamanio_bytes']   = !empty($datos['tamanio_bytes']) ? (int)$datos['tamanio_bytes'] : null;
    }

    $sql .= " WHERE id = :id";

    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Elimina un documento y su archivo físico (si está en uploads/).
 *
 * @param int $id
 * @return bool
 */
function documentos_eliminar(int $id): bool
{
    $doc = documentos_obtener_por_id($id);
    if (!$doc) {
        return false;
    }

    // Borrado físico si reside en uploads/
    if (!empty($doc['archivo_ruta']) && str_starts_with($doc['archivo_ruta'], 'uploads/')) {
        eliminar_documento($doc['archivo_ruta']);
    }

    $db = obtener_db();
    $stmt = $db->prepare("DELETE FROM documentos WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}

/**
 * Incrementa el contador de descargas de un documento.
 *
 * @param int $id
 * @return void
 */
function documentos_incrementar_descargas(int $id): void
{
    $db = obtener_db();
    $stmt = $db->prepare("UPDATE documentos SET descargas = descargas + 1 WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

/**
 * Lista los años fiscales distintos disponibles en la base de datos.
 *
 * @return array
 */
function documentos_anios_disponibles(): array
{
    $db = obtener_db();
    return $db->query("
        SELECT DISTINCT anio FROM documentos
        WHERE activo = 1 AND anio IS NOT NULL
        ORDER BY anio DESC
    ")->fetchAll(PDO::FETCH_COLUMN);
}
