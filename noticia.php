<?php
/**
 * JUSHSAL - Detalle de Noticia Institucional (noticia.php)
 * Visualización individual por parámetro ?slug=
 */

define('JUSHSAL_INIT', true);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/modelos/noticias.php';

$slug = trim($_GET['slug'] ?? '');
$noticia = null;

if (!empty($slug)) {
    $noticia = noticias_obtener_por_slug($slug, true);
}

if (!$noticia) {
    http_response_code(404);
    $tituloPagina = 'Noticia no encontrada | Junta de Usuarios San Lorenzo';
    $descripcionPagina = 'La noticia solicitada no existe o no se encuentra disponible actualmente.';
    $baseUrl = BASE_URL;
    include __DIR__ . '/includes/vistas/header.php';
    ?>
    <section class="section" style="padding:80px 20px; text-align:center;">
      <div class="container" style="max-width:600px;">
        <h1 style="font-size:32px; color:#0b3d76; margin-bottom:12px;">Noticia no encontrada</h1>
        <p style="color:#64748b; font-size:16px; margin-bottom:24px;">La publicación que buscas no existe, ha sido despublicada o el enlace ingresado es incorrecto.</p>
        <a href="<?= $baseUrl ?>noticias.php" class="btn btn-primary">← Ver todas las noticias</a>
      </div>
    </section>
    <?php
    include __DIR__ . '/includes/vistas/footer.php';
    exit;
}

$tituloPagina = $noticia['titulo'] . ' | JUSHSAL';
$descripcionPagina = mb_substr($noticia['resumen'], 0, 155);
$baseUrl = BASE_URL;

// Otras 3 noticias recientes para recomendaciones al pie
$otrasNoticias = noticias_listar_publicadas(3, 0);

include __DIR__ . '/includes/vistas/header.php';
?>

<article class="section" style="padding-top:40px;">
  <div class="container" style="max-width:880px;">
    
    <!-- Migas de pan -->
    <div style="font-size:13px; color:#64748b; margin-bottom:16px; display:flex; gap:8px; align-items:center;">
      <a href="<?= $baseUrl ?>index.php" style="color:#0b3d76; text-decoration:none;">Inicio</a>
      <span>/</span>
      <a href="<?= $baseUrl ?>noticias.php" style="color:#0b3d76; text-decoration:none;">Noticias</a>
      <span>/</span>
      <span style="color:#94a3b8;"><?= e(mb_substr($noticia['titulo'], 0, 35)) ?>...</span>
    </div>

    <!-- Encabezado de la noticia -->
    <header style="margin-bottom:28px;">
      <span class="news-badge" style="position:static; display:inline-block; margin-bottom:12px;">
        <?= e($noticia['categoria_nombre'] ?? 'Comunicado') ?>
      </span>
      <h1 style="font-size:32px; color:#0b3d76; line-height:1.25; margin:0 0 16px 0; font-family:'Montserrat',sans-serif;">
        <?= e($noticia['titulo']) ?>
      </h1>
      <div style="display:flex; align-items:center; gap:16px; color:#64748b; font-size:14px; border-bottom:1px solid #e2e8f0; padding-bottom:16px;">
        <span style="display:flex; align-items:center; gap:6px;">
          <svg style="width:16px; height:16px;" viewBox="0 0 24 24" fill="currentColor"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V9h14v11z"/></svg>
          Publicado: <?= e(formato_fecha($noticia['fecha_publicacion'])) ?>
        </span>
        <?php if (!empty($noticia['autor_nombre'])): ?>
          <span>· Por: <?= e($noticia['autor_nombre']) ?></span>
        <?php endif; ?>
      </div>
    </header>

    <!-- Imagen destacada si existe -->
    <?php if (!empty($noticia['imagen_portada'])): ?>
      <div style="margin-bottom:32px; border-radius:12px; overflow:hidden; box-shadow:0 6px 20px rgba(0,0,0,0.08);">
        <img src="<?= $baseUrl . e($noticia['imagen_portada']) ?>" alt="<?= e($noticia['titulo']) ?>" style="width:100%; max-height:480px; object-fit:cover; display:block;"/>
      </div>
    <?php endif; ?>

    <!-- Bajada / Resumen -->
    <div style="font-size:18px; line-height:1.6; color:#1e293b; font-weight:600; margin-bottom:28px; padding-left:18px; border-left:4px solid #14919b; background:#f8fafc; padding:18px; border-radius:0 8px 8px 0;">
      <?= e($noticia['resumen']) ?>
    </div>

    <!-- Cuerpo principal formateado con nl2br(e()) -->
    <?php if (!empty($noticia['contenido'])): ?>
      <div style="font-size:16px; line-height:1.8; color:#334155; margin-bottom:48px; white-space:pre-line;">
        <?= nl2br(e($noticia['contenido'])) ?>
      </div>
    <?php endif; ?>

    <div style="padding-top:24px; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
      <a href="<?= $baseUrl ?>noticias.php" class="btn btn-outline">← Volver al listado de noticias</a>
      <a href="<?= $baseUrl ?>index.php#contacto" class="btn btn-primary">Contáctanos</a>
    </div>

  </div>
</article>

<!-- Otras noticias recomendadas -->
<?php if (!empty($otrasNoticias)): ?>
  <section class="section section-alt" style="padding-top:40px;">
    <div class="container">
      <h2 style="font-size:22px; color:#0b3d76; margin-bottom:24px;">Otras noticias de interés</h2>
      <div class="news-grid">
        <?php foreach ($otrasNoticias as $i => $otra): ?>
          <?php if ($otra['id'] === $noticia['id']) continue; ?>
          <article class="news-card">
            <div class="news-media tint-<?= ($i % 4) + 1 ?>">
              <?php if (!empty($otra['imagen_portada'])): ?>
                <img class="news-img" src="<?= $baseUrl . e($otra['imagen_portada']) ?>" alt="<?= e($otra['titulo']) ?>" loading="lazy"/>
              <?php else: ?>
                <span class="news-monogram"><?= e(monograma_noticia($otra['titulo'])) ?></span>
              <?php endif; ?>
              <span class="news-badge"><?= e($otra['categoria_nombre'] ?? 'Noticia') ?></span>
            </div>
            <div class="news-body">
              <span class="news-date"><?= e(formato_fecha($otra['fecha_publicacion'])) ?></span>
              <h3><?= e($otra['titulo']) ?></h3>
              <p><?= e($otra['resumen']) ?></p>
              <a class="link-more" href="noticia.php?slug=<?= urlencode($otra['slug']) ?>">Leer más <span>→</span></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php
include __DIR__ . '/includes/vistas/footer.php';
?>
