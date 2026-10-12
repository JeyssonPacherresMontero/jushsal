<?php
/**
 * JUSHSAL - Modelo de Datos: Noticias
 * Gestión de publicaciones institucionales, categorías y slugs únicos.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/helpers.php';
require_once dirname(__DIR__) . '/upload.php';

/**
 * Genera un monograma de 1 o 2 letras a partir del título de la noticia.
 *
 * @param string $titulo
 * @return string
 */
function monograma_noticia(string $titulo): string
{
    $limpio = preg_replace('/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]/u', '', trim($titulo));
    $palabras = preg_split('/\s+/u', $limpio, -1, PREG_SPLIT_NO_EMPTY);

    if (empty($palabras)) {
        return 'N';
    }

    if (count($palabras) === 1) {
        return mb_strtoupper(mb_substr($palabras[0], 0, 1, 'UTF-8'), 'UTF-8');
    }

    $primera = mb_strtoupper(mb_substr($palabras[0], 0, 1, 'UTF-8'), 'UTF-8');
    $ultima = mb_strtoupper(mb_substr(end($palabras), 0, 1, 'UTF-8'), 'UTF-8');

    return $primera . $ultima;
}

/**
 * Obtiene todas las categorías de noticias disponibles.
 *
 * @return array
 */
function categorias_noticia_listar(): array
{
    $db = obtener_db();
    $stmt = $db->query("SELECT id, nombre, slug FROM categorias_noticia ORDER BY nombre ASC");
    return $stmt->fetchAll();
}

/**
 * Genera un slug único para una noticia resolviendo colisiones.
 *
 * @param string   $titulo
 * @param int|null $excluirId ID de la noticia a ignorar (al editar)
 * @return string
 */
function noticias_generar_slug_unico(string $titulo, ?int $excluirId = null): string
{
    $db = obtener_db();
    $slugBase = slugify($titulo);
    $slug = $slugBase;
    $contador = 1;

    while (true) {
        if ($excluirId) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM noticias WHERE slug = :slug AND id != :id");
            $stmt->execute([':slug' => $slug, ':id' => $excluirId]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM noticias WHERE slug = :slug");
            $stmt->execute([':slug' => $slug]);
        }

        if ((int)$stmt->fetchColumn() === 0) {
            return $slug;
        }

        $contador++;
        $slug = "{$slugBase}-{$contador}";
    }
}

/**
 * Lista noticias en estado 'publicado' para la web pública.
 *
 * @param int      $limite
 * @param int      $offset
 * @param int|null $categoriaId
 * @return array
 */
function noticias_listar_publicadas(int $limite = 9, int $offset = 0, ?int $categoriaId = null): array
{
    $db = obtener_db();
    $sql = "
        SELECT n.id, n.categoria_id, n.titulo, n.slug, n.resumen, n.contenido,
               n.imagen_portada, n.fecha_publicacion, n.estado, n.destacada,
               c.nombre AS categoria_nombre, c.slug AS categoria_slug
        FROM noticias n
        LEFT JOIN categorias_noticia c ON n.categoria_id = c.id
        WHERE n.estado = 'publicado'
    ";

    $params = [];
    if ($categoriaId) {
        $sql .= " AND n.categoria_id = :cat_id";
        $params[':cat_id'] = $categoriaId;
    }

    $sql .= " ORDER BY n.destacada DESC, n.fecha_publicacion DESC, n.id DESC LIMIT :limite OFFSET :offset";

    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_INT);
    }
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Cuenta la cantidad de noticias publicadas para el cálculo de paginación.
 *
 * @param int|null $categoriaId
 * @return int
 */
function noticias_contar_publicadas(?int $categoriaId = null): int
{
    $db = obtener_db();
    $sql = "SELECT COUNT(*) FROM noticias WHERE estado = 'publicado'";
    $params = [];

    if ($categoriaId) {
        $sql .= " AND categoria_id = :cat_id";
        $params[':cat_id'] = $categoriaId;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    return (int)$stmt->fetchColumn();
}

/**
 * Obtiene el detalle de una noticia por su slug amigable.
 *
 * @param string $slug
 * @param bool   $soloPublicadas
 * @return array|null
 */
function noticias_obtener_por_slug(string $slug, bool $soloPublicadas = true): ?array
{
    $db = obtener_db();
    $sql = "
        SELECT n.*, c.nombre AS categoria_nombre, c.slug AS categoria_slug,
               u.nombre AS autor_nombre
        FROM noticias n
        LEFT JOIN categorias_noticia c ON n.categoria_id = c.id
        LEFT JOIN usuarios u ON n.autor_id = u.id
        WHERE n.slug = :slug
    ";

    if ($soloPublicadas) {
        $sql .= " AND n.estado = 'publicado'";
    }
    $sql .= " LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute([':slug' => $slug]);
    $res = $stmt->fetch();

    return $res ?: null;
}

/**
 * Obtiene una noticia por su identificador primario.
 *
 * @param int $id
 * @return array|null
 */
function noticias_obtener_por_id(int $id): ?array
{
    $db = obtener_db();
    $stmt = $db->prepare("
        SELECT n.*, c.nombre AS categoria_nombre
        FROM noticias n
        LEFT JOIN categorias_noticia c ON n.categoria_id = c.id
        WHERE n.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();

    return $res ?: null;
}

/**
 * Lista noticias en el panel administrativo con filtros de búsqueda y estado.
 *
 * @param array $filtros ['estado' => '', 'buscar' => '', 'categoria_id' => 0]
 * @param int   $limite
 * @param int   $offset
 * @return array
 */
function noticias_listar_admin(array $filtros = [], int $limite = 20, int $offset = 0): array
{
    $db = obtener_db();
    $sql = "
        SELECT n.id, n.categoria_id, n.titulo, n.slug, n.resumen,
               n.imagen_portada, n.fecha_publicacion, n.estado, n.destacada,
               n.created_at, c.nombre AS categoria_nombre, u.nombre AS autor_nombre
        FROM noticias n
        LEFT JOIN categorias_noticia c ON n.categoria_id = c.id
        LEFT JOIN usuarios u ON n.autor_id = u.id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($filtros['estado']) && in_array($filtros['estado'], ['borrador', 'publicado', 'archivado'], true)) {
        $sql .= " AND n.estado = :estado";
        $params[':estado'] = $filtros['estado'];
    }

    if (!empty($filtros['categoria_id'])) {
        $sql .= " AND n.categoria_id = :cat_id";
        $params[':cat_id'] = (int)$filtros['categoria_id'];
    }

    if (!empty($filtros['buscar'])) {
        $sql .= " AND (n.titulo LIKE :buscar OR n.resumen LIKE :buscar)";
        $params[':buscar'] = '%' . trim($filtros['buscar']) . '%';
    }

    $sql .= " ORDER BY n.fecha_publicacion DESC, n.id DESC LIMIT :limite OFFSET :offset";

    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Cuenta las noticias filtradas en el panel administrativo.
 *
 * @param array $filtros
 * @return int
 */
function noticias_contar_admin(array $filtros = []): int
{
    $db = obtener_db();
    $sql = "SELECT COUNT(*) FROM noticias n WHERE 1=1";
    $params = [];

    if (!empty($filtros['estado']) && in_array($filtros['estado'], ['borrador', 'publicado', 'archivado'], true)) {
        $sql .= " AND n.estado = :estado";
        $params[':estado'] = $filtros['estado'];
    }

    if (!empty($filtros['categoria_id'])) {
        $sql .= " AND n.categoria_id = :cat_id";
        $params[':cat_id'] = (int)$filtros['categoria_id'];
    }

    if (!empty($filtros['buscar'])) {
        $sql .= " AND (n.titulo LIKE :buscar OR n.resumen LIKE :buscar)";
        $params[':buscar'] = '%' . trim($filtros['buscar']) . '%';
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    return (int)$stmt->fetchColumn();
}

/**
 * Inserta una nueva noticia en la base de datos.
 *
 * @param array $datos
 * @return int ID de la noticia creada
 */
function noticias_crear(array $datos): int
{
    $db = obtener_db();

    $stmt = $db->prepare("
        INSERT INTO noticias (
            categoria_id, titulo, slug, resumen, contenido,
            imagen_portada, fecha_publicacion, estado, destacada, autor_id, created_at
        ) VALUES (
            :categoria_id, :titulo, :slug, :resumen, :contenido,
            :imagen_portada, :fecha_publicacion, :estado, :destacada, :autor_id, NOW()
        )
    ");

    $stmt->execute([
        ':categoria_id'      => !empty($datos['categoria_id']) ? (int)$datos['categoria_id'] : null,
        ':titulo'            => trim($datos['titulo']),
        ':slug'              => $datos['slug'],
        ':resumen'           => trim($datos['resumen']),
        ':contenido'         => !empty($datos['contenido']) ? trim($datos['contenido']) : null,
        ':imagen_portada'    => $datos['imagen_portada'] ?? null,
        ':fecha_publicacion' => $datos['fecha_publicacion'] ?? date('Y-m-d'),
        ':estado'            => in_array($datos['estado'] ?? '', ['borrador', 'publicado', 'archivado'], true) ? $datos['estado'] : 'borrador',
        ':destacada'         => !empty($datos['destacada']) ? 1 : 0,
        ':autor_id'          => !empty($datos['autor_id']) ? (int)$datos['autor_id'] : null
    ]);

    return (int)$db->lastInsertId();
}

/**
 * Actualiza los datos de una noticia existente.
 *
 * @param int   $id
 * @param array $datos
 * @return bool
 */
function noticias_actualizar(int $id, array $datos): bool
{
    $db = obtener_db();

    $sql = "
        UPDATE noticias SET
            categoria_id      = :categoria_id,
            titulo            = :titulo,
            slug              = :slug,
            resumen           = :resumen,
            contenido         = :contenido,
            fecha_publicacion = :fecha_publicacion,
            estado            = :estado,
            destacada         = :destacada,
            updated_at        = NOW()
    ";

    $params = [
        ':id'                => $id,
        ':categoria_id'      => !empty($datos['categoria_id']) ? (int)$datos['categoria_id'] : null,
        ':titulo'            => trim($datos['titulo']),
        ':slug'              => $datos['slug'],
        ':resumen'           => trim($datos['resumen']),
        ':contenido'         => !empty($datos['contenido']) ? trim($datos['contenido']) : null,
        ':fecha_publicacion' => $datos['fecha_publicacion'] ?? date('Y-m-d'),
        ':estado'            => in_array($datos['estado'] ?? '', ['borrador', 'publicado', 'archivado'], true) ? $datos['estado'] : 'borrador',
        ':destacada'         => !empty($datos['destacada']) ? 1 : 0
    ];

    if (array_key_exists('imagen_portada', $datos)) {
        $sql .= ", imagen_portada = :imagen_portada";
        $params[':imagen_portada'] = $datos['imagen_portada'];
    }

    $sql .= " WHERE id = :id";

    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Cambia el estado de una noticia ('borrador', 'publicado', 'archivado').
 *
 * @param int    $id
 * @param string $nuevoEstado
 * @return bool
 */
function noticias_cambiar_estado(int $id, string $nuevoEstado): bool
{
    if (!in_array($nuevoEstado, ['borrador', 'publicado', 'archivado'], true)) {
        return false;
    }

    $db = obtener_db();
    $stmt = $db->prepare("UPDATE noticias SET estado = :estado, updated_at = NOW() WHERE id = :id");
    return $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);
}

/**
 * Elimina una noticia. Por defecto realiza borrado lógico (estado = 'archivado').
 * Si se solicita borrado físico, elimina la imagen asociada en disco.
 *
 * @param int  $id
 * @param bool $logico
 * @return bool
 */
function noticias_eliminar(int $id, bool $logico = true): bool
{
    $noticia = noticias_obtener_por_id($id);
    if (!$noticia) {
        return false;
    }

    if ($logico) {
        return noticias_cambiar_estado($id, 'archivado');
    }

    // Borrado físico: limpiar imagen física si existe
    if (!empty($noticia['imagen_portada'])) {
        eliminar_imagen($noticia['imagen_portada']);
    }

    $db = obtener_db();
    $stmt = $db->prepare("DELETE FROM noticias WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}
