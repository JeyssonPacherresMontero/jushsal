<?php
/**
 * JUSHSAL - Gestión de Noticias: Listado Administrativo (admin/noticias/listar.php)
 */

define('JUSHSAL_INIT', true);

$tituloPanel = 'Administración de Noticias';
require_once dirname(__DIR__) . '/header.php';
require_once dirname(dirname(__DIR__)) . '/includes/modelos/noticias.php';

// Verificar permisos de rol
requerir_rol(['admin', 'comunicaciones']);

$estadoFiltro = $_GET['estado'] ?? '';
$categoriaFiltro = !empty($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : null;
$buscarFiltro = trim($_GET['buscar'] ?? '');
$pagina = max(1, (int)($_GET['p'] ?? 1));
$porPagina = 15;
$offset = ($pagina - 1) * $porPagina;

$filtros = [
    'estado'       => $estadoFiltro,
    'categoria_id' => $categoriaFiltro,
    'buscar'       => $buscarFiltro
];

$totalNoticias = noticias_contar_admin($filtros);
$noticias = noticias_listar_admin($filtros, $porPagina, $offset);
$totalPaginas = max(1, (int)ceil($totalNoticias / $porPagina));
$categorias = categorias_noticia_listar();
?>

<div class="admin-title-row">
  <div>
    <h1>Noticias Institucionales</h1>
    <p style="margin:4px 0 0; color:#64748b; font-size:14px;">Administra comunicados, actividades y notas de prensa de JUSHSAL.</p>
  </div>
  <div>
    <a href="form.php" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px;">
      <span>+ Nueva Noticia</span>
    </a>
  </div>
</div>

<!-- Filtros de búsqueda -->
<div class="admin-card" style="padding:16px 20px; margin-bottom:20px;">
  <form method="GET" action="listar.php" style="display:flex; gap:14px; flex-wrap:wrap; align-items:center;">
    <div style="flex:1; min-width:200px;">
      <input type="text" name="buscar" value="<?= e($buscarFiltro) ?>" placeholder="Buscar por título o resumen..." style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; box-sizing:border-box;"/>
    </div>

    <div>
      <select name="estado" style="padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; background:#fff;">
        <option value="">Todos los estados</option>
        <option value="publicado" <?= $estadoFiltro === 'publicado' ? 'selected' : '' ?>>Publicados</option>
        <option value="borrador" <?= $estadoFiltro === 'borrador' ? 'selected' : '' ?>>Borradores</option>
        <option value="archivado" <?= $estadoFiltro === 'archivado' ? 'selected' : '' ?>>Archivados</option>
      </select>
    </div>

    <div>
      <select name="categoria_id" style="padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; background:#fff;">
        <option value="">Todas las categorías</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $categoriaFiltro === (int)$c['id'] ? 'selected' : '' ?>>
            <?= e($c['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="btn btn-outline btn-sm" style="padding:9px 16px;">Filtrar</button>
    <?php if ($estadoFiltro || $categoriaFiltro || $buscarFiltro): ?>
      <a href="listar.php" style="font-size:13px; color:#64748b; text-decoration:none;">Limpiar</a>
    <?php endif; ?>
  </form>
</div>

<!-- Tabla de resultados -->
<div class="admin-card">
  <?php if (empty($noticias)): ?>
    <div style="text-align:center; padding:40px 20px; color:#64748b;">
      <p style="font-size:16px; margin:0 0 10px 0;">No se encontraron noticias con los criterios seleccionados.</p>
      <a href="form.php" class="btn btn-primary btn-sm">+ Crear una noticia ahora</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:70px;">Portada</th>
            <th>Título y Resumen</th>
            <th>Categoría</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th style="text-align:right;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($noticias as $n): ?>
            <tr>
              <td>
                <?php if (!empty($n['imagen_portada'])): ?>
                  <img src="<?= $baseUrl . e($n['imagen_portada']) ?>" alt="Portada" style="width:54px; height:40px; object-fit:cover; border-radius:4px; display:block;"/>
                <?php else: ?>
                  <div style="width:54px; height:40px; background:#e2e8f0; border-radius:4px; display:flex; align-items:center; justify-content:center; font-weight:700; color:#0b3d76; font-size:13px;">
                    <?= e(monograma_noticia($n['titulo'])) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <div style="display:flex; align-items:center; gap:6px;">
                  <strong style="color:#0b3d76; font-size:14px;"><?= e($n['titulo']) ?></strong>
                  <?php if (!empty($n['destacada'])): ?>
                    <span style="color:#ffab00; font-size:16px;" title="Noticia destacada">★</span>
                  <?php endif; ?>
                </div>
                <div style="font-size:12px; color:#64748b; margin-top:2px; max-width:550px;">
                  <?= e(mb_substr($n['resumen'], 0, 100)) ?>...
                </div>
                <div style="font-size:11px; color:#94a3b8; margin-top:2px;">
                  slug: <code><?= e($n['slug']) ?></code>
                </div>
              </td>
              <td>
                <span class="badge" style="background:#f1f5f9; color:#334155;">
                  <?= e($n['categoria_nombre'] ?? 'Sin categoría') ?>
                </span>
              </td>
              <td style="white-space:nowrap; font-size:13px;">
                <?= e(formato_fecha($n['fecha_publicacion'])) ?>
              </td>
              <td>
                <?php if ($n['estado'] === 'publicado'): ?>
                  <span class="badge badge-success">Publicado</span>
                <?php elseif ($n['estado'] === 'borrador'): ?>
                  <span class="badge badge-warning">Borrador</span>
                <?php else: ?>
                  <span class="badge badge-secondary">Archivado</span>
                <?php endif; ?>
              </td>
              <td style="text-align:right; white-space:nowrap;">
                <a href="form.php?id=<?= $n['id'] ?>" class="btn btn-outline btn-sm" style="margin-right:4px;">Editar</a>
                
                <?php if ($n['estado'] === 'publicado'): ?>
                  <a href="<?= $baseUrl ?>noticia.php?slug=<?= urlencode($n['slug']) ?>" target="_blank" class="btn btn-outline btn-sm" style="margin-right:4px;" title="Ver en la web pública">Ver</a>
                <?php endif; ?>

                <form method="POST" action="eliminar.php" style="display:inline;" onsubmit="return confirm('¿Está seguro de archivar esta noticia?');">
                  <?= csrf_campo() ?>
                  <input type="hidden" name="id" value="<?= $n['id'] ?>"/>
                  <input type="hidden" name="accion" value="archivar"/>
                  <button type="submit" class="btn btn-outline btn-sm" style="color:#ef4444; border-color:#fca5a5;">Archivar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <?php if ($totalPaginas > 1): ?>
      <div style="display:flex; justify-content:center; gap:8px; margin-top:24px;">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
          <a href="listar.php?p=<?= $i ?>&estado=<?= urlencode($estadoFiltro) ?>&categoria_id=<?= urlencode((string)$categoriaFiltro) ?>&buscar=<?= urlencode($buscarFiltro) ?>" 
             style="padding:6px 12px; border-radius:6px; border:1px solid #cbd5e1; text-decoration:none; font-size:13px; font-weight:600; <?= $i === $pagina ? 'background:#0b3d76; color:#fff; border-color:#0b3d76;' : 'background:#fff; color:#475569;' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php
require_once dirname(__DIR__) . '/footer.php';
?>
