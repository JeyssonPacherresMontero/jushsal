<?php
/**
 * JUSHSAL - Listado Público de Noticias (noticias.php)
 * Muestra noticias publicadas con paginación (9/página) y filtro por categoría.
 */

define('JUSHSAL_INIT', true);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/modelos/noticias.php';

$categoriaId = !empty($_GET['cat']) ? (int)$_GET['cat'] : null;
$pagina = max(1, (int)($_GET['p'] ?? 1));
$porPagina = 9;
$offset = ($pagina - 1) * $porPagina;

$totalNoticias = noticias_contar_publicadas($categoriaId);
$noticias = noticias_listar_publicadas($porPagina, $offset, $categoriaId);
$totalPaginas = max(1, (int)ceil($totalNoticias / $porPagina));
$categorias = categorias_noticia_listar();

$tituloPagina = 'Noticias y Comunicados Institucionales | Junta de Usuarios San Lorenzo';
$descripcionPagina = 'Infórmate sobre los últimos comunicados, actividades y gestiones de la Junta de Usuarios del Sector Hidráulico Menor San Lorenzo.';
$baseUrl = BASE_URL;

include __DIR__ . '/includes/vistas/header.php';
?>

<section class="section section-alt" style="padding-top:40px;">
  <div class="container">
    <div class="section-head center">
      <p class="eyebrow">Comunicaciones JUSHSAL</p>
      <h1 class="section-title">Noticias y Comunicados</h1>
      <p class="section-desc">Mantente informado sobre las actividades, acuerdos y novedades del Sector Hidráulico Menor San Lorenzo.</p>
    </div>

    <!-- Filtros de categoría -->
    <div style="display:flex; justify-content:center; gap:10px; flex-wrap:wrap; margin-bottom:36px;">
      <a href="noticias.php" 
         class="btn <?= !$categoriaId ? 'btn-primary' : 'btn-outline' ?>" 
         style="padding:8px 16px; font-size:13px;">
        Todas
      </a>
      <?php foreach ($categorias as $cat): ?>
        <a href="noticias.php?cat=<?= $cat['id'] ?>" 
           class="btn <?= ($categoriaId === (int)$cat['id']) ? 'btn-primary' : 'btn-outline' ?>" 
           style="padding:8px 16px; font-size:13px;">
          <?= e($cat['nombre']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Cuadrícula de noticias -->
    <?php if (empty($noticias)): ?>
      <div style="text-align:center; padding:60px 20px; background:#fff; border-radius:12px; border:1px solid #e2e8f0; max-width:600px; margin:0 auto;">
        <svg style="width:48px; height:48px; color:#94a3b8; margin-bottom:12px;" viewBox="0 0 24 24" fill="currentColor">
          <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
        </svg>
        <h3 style="color:#0b3d76; margin:0 0 8px 0; font-size:18px;">No hay noticias publicadas en esta sección</h3>
        <p style="color:#64748b; font-size:14px; margin:0;">Las nuevas comunicaciones institucionales aparecerán aquí cuando sean publicadas.</p>
        <?php if ($categoriaId): ?>
          <a href="noticias.php" class="btn btn-outline" style="margin-top:16px;">Ver todas las noticias</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="news-grid">
        <?php foreach ($noticias as $i => $n): ?>
          <?php $tintClass = 'tint-' . (($i % 4) + 1); ?>
          <article class="news-card">
            <div class="news-media <?= $tintClass ?>">
              <?php if (!empty($n['imagen_portada'])): ?>
                <img class="news-img" src="<?= $baseUrl . e($n['imagen_portada']) ?>" alt="<?= e($n['titulo']) ?>" loading="lazy"/>
              <?php else: ?>
                <span class="news-monogram"><?= e(monograma_noticia($n['titulo'])) ?></span>
              <?php endif; ?>
              <span class="news-badge"><?= e($n['categoria_nombre'] ?? 'Noticia') ?></span>
            </div>
            <div class="news-body">
              <span class="news-date">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V9h14v11z"/></svg>
                <?= e(formato_fecha($n['fecha_publicacion'])) ?>
              </span>
              <h3><?= e($n['titulo']) ?></h3>
              <p><?= e($n['resumen']) ?></p>
              <a class="link-more" href="noticia.php?slug=<?= urlencode($n['slug']) ?>">Leer más <span>→</span></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <!-- Paginación -->
      <?php if ($totalPaginas > 1): ?>
        <div style="display:flex; justify-content:center; gap:8px; margin-top:40px;">
          <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
            <a href="noticias.php?p=<?= $p ?><?= $categoriaId ? '&cat=' . $categoriaId : '' ?>" 
               style="padding:8px 14px; border-radius:6px; font-weight:700; text-decoration:none; font-size:14px; <?= $p === $pagina ? 'background:#0b3d76; color:#fff;' : 'background:#fff; color:#0b3d76; border:1px solid #cbd5e1;' ?>">
              <?= $p ?>
            </a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php
include __DIR__ . '/includes/vistas/footer.php';
?>
