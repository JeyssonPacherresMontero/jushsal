<?php
/**
 * JUSHSAL - Listado Administrativo de Galería Fotográfica (admin/galeria/listar.php)
 */

define('JUSHSAL_INIT', true);

$tituloPanel = 'Galería de Fotos';
require_once dirname(__DIR__, 2) . '/admin/header.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/galeria.php';

$imagenes = galeria_listar_admin();
?>

<div class="admin-title-row">
  <div>
    <h1>Galería Fotográfica Institucional</h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Gestión de fotografías del Valle de San Lorenzo, infraestructura de riego y actividades institucionales.
    </p>
  </div>
  <div>
    <a href="form.php" class="btn btn-primary" style="font-size: 14px; padding: 10px 18px;">
      + Nueva Fotografía
    </a>
  </div>
</div>

<div class="admin-card">
  <?php if (empty($imagenes)): ?>
    <div style="text-align: center; padding: 40px 20px; color: #64748b;">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.3; margin-bottom: 12px;"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
      <p style="font-size: 15px; margin: 0 0 16px;">No hay fotografías registradas en la galería.</p>
      <a href="form.php" class="btn btn-primary btn-sm">Subir la primera foto</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 80px;">Foto</th>
            <th>Título / Pie de Foto</th>
            <th style="text-align: center; width: 80px;">Orden</th>
            <th style="text-align: center; width: 120px;">Formato</th>
            <th style="text-align: center; width: 100px;">Estado</th>
            <th style="width: 140px;">Registrado por</th>
            <th style="text-align: right; width: 160px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($imagenes as $img): ?>
            <?php
              $urlImg = $baseUrl . rawurlencode(ltrim($img['imagen'], '/'));
              $urlImg = str_replace('%2F', '/', $urlImg);
              $esDestacada = (int)$img['es_destacada'] === 1;
              $activo = (int)$img['activo'] === 1;
            ?>
            <tr>
              <td>
                <a href="<?= e($urlImg) ?>" target="_blank" rel="noopener noreferrer">
                  <img src="<?= e($urlImg) ?>" alt="<?= e($img['titulo']) ?>"
                       style="width: 64px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; display: block;"
                       onerror="this.src='<?= $baseUrl ?>assets/images/logo.png'"/>
                </a>
              </td>
              <td>
                <strong style="color: #0b3d76; font-size: 14.5px; display: block; margin-bottom: 2px;">
                  <?= e($img['titulo']) ?>
                </strong>
                <?php if (!empty($img['descripcion'])): ?>
                  <div style="font-size: 12px; color: #64748b; line-height: 1.3; max-width: 480px;">
                    <?= e(mb_substr($img['descripcion'], 0, 110)) ?>...
                  </div>
                <?php endif; ?>
              </td>
              <td style="text-align: center; font-size: 13.5px; font-weight: 600; color: #334155;">
                <?= (int)$img['orden'] ?>
              </td>
              <td style="text-align: center;">
                <?php if ($esDestacada): ?>
                  <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;" title="Ocupa 2 columnas en la cuadrícula (clase .gallery-wide)">
                    ★ Destacada (Ancha)
                  </span>
                <?php else: ?>
                  <span class="badge badge-secondary">Estándar</span>
                <?php endif; ?>
              </td>
              <td style="text-align: center;">
                <?php if ($activo): ?>
                  <span class="badge badge-success">Activa</span>
                <?php else: ?>
                  <span class="badge badge-secondary">Oculta</span>
                <?php endif; ?>
              </td>
              <td style="font-size: 12.5px; color: #64748b;">
                <?= e($img['autor_nombre'] ?? 'Administración') ?><br/>
                <span style="font-size: 11px; opacity: 0.8;"><?= substr($img['created_at'], 0, 10) ?></span>
              </td>
              <td style="text-align: right;">
                <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                  <a href="form.php?id=<?= (int)$img['id'] ?>" class="btn btn-outline btn-sm" style="color: #0b3d76; border-color: #cbd5e1;">
                    Editar
                  </a>
                  <form action="eliminar.php" method="POST" style="display: inline;" onsubmit="return confirm('¿Está seguro de eliminar esta fotografía? Se borrará también el archivo del servidor.');">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int)$img['id'] ?>"/>
                    <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                      Eliminar
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once dirname(__DIR__, 2) . '/admin/footer.php'; ?>
