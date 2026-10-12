<?php
/**
 * JUSHSAL - Listado de Convocatorias a Asamblea (admin/convocatorias/listar.php)
 */

define('JUSHSAL_INIT', true);

$tituloPanel = 'Convocatorias a Asamblea';
require_once dirname(__DIR__, 2) . '/admin/header.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/convocatorias.php';

$convocatorias = convocatorias_listar_admin();
?>

<div class="admin-title-row">
  <div>
    <h1>Convocatorias a Asamblea (Popup)</h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Gestión de afiches y convocatorias oficiales que se muestran en ventana emergente (popup) a los visitantes.
    </p>
  </div>
  <div>
    <a href="form.php" class="btn btn-primary" style="font-size: 14px; padding: 10px 18px;">
      + Nueva Convocatoria
    </a>
  </div>
</div>

<div class="admin-card">
  <?php if (empty($convocatorias)): ?>
    <div style="text-align: center; padding: 40px 20px; color: #64748b;">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.3; margin-bottom: 12px;"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-7 12h-2v-2h2v2zm0-4h-2V6h2v4z"/></svg>
      <p style="font-size: 15px; margin: 0 0 16px;">No hay convocatorias registradas.</p>
      <a href="form.php" class="btn btn-primary btn-sm">Crear la primera convocatoria</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 80px;">Afiche</th>
            <th>Título de la Convocatoria</th>
            <th>Vigencia (Desde – Hasta)</th>
            <th>Parámetros</th>
            <th>Frecuencia</th>
            <th>Estado</th>
            <th style="text-align: right; width: 220px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($convocatorias as $c): ?>
            <?php
              $imagenUrl = !empty($c['imagen']) ? $baseUrl . ltrim($c['imagen'], '/') : $baseUrl . 'assets/images/placeholder.jpg';
              $esVigente = (int)$c['vigente_ahora'] === 1;
              $activo = (int)$c['activo'] === 1;
              $frecuenciaTexto = [
                'cada_visita' => 'Cada visita',
                'una_vez_por_sesion' => '1 vez / sesión',
                'una_vez_por_dia' => '1 vez / día'
              ][$c['frecuencia']] ?? $c['frecuencia'];
            ?>
            <tr>
              <td>
                <a href="<?= e($imagenUrl) ?>" target="_blank" title="Ver afiche completo">
                  <img src="<?= e($imagenUrl) ?>" alt="Afiche" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; display: block;"/>
                </a>
              </td>
              <td>
                <strong style="color: #0b3d76; font-size: 15px; display: block; margin-bottom: 4px;"><?= e($c['titulo']) ?></strong>
                <?php if (!empty($c['fecha_asamblea'])): ?>
                  <span style="font-size: 12px; color: #475569;">
                    📅 Asamblea: <strong><?= e(date('d/m/Y', strtotime($c['fecha_asamblea']))) ?></strong>
                  </span>
                <?php endif; ?>
              </td>
              <td>
                <div style="font-size: 13px; color: #334155; line-height: 1.4;">
                  <div><span style="color:#64748b;">Desde:</span> <?= e(date('d/m/Y H:i', strtotime($c['mostrar_desde']))) ?></div>
                  <div><span style="color:#64748b;">Hasta:</span> <?= e(date('d/m/Y H:i', strtotime($c['mostrar_hasta']))) ?></div>
                </div>
                <div style="margin-top: 6px;">
                  <?php if ($esVigente): ?>
                    <span class="badge badge-success">● Vigente Ahora</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">Fuera de rango</span>
                  <?php endif; ?>
                </div>
              </td>
              <td style="font-size: 12.5px; color: #475569;">
                <div>Retraso: <strong><?= (int)$c['retraso_seg'] ?>s</strong></div>
                <div>Duración: <strong><?= (int)$c['duracion_seg'] > 0 ? (int)$c['duracion_seg'] . 's' : 'Fijo' ?></strong></div>
              </td>
              <td style="font-size: 13px;">
                <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;">
                  <?= e($frecuenciaTexto) ?>
                </span>
              </td>
              <td>
                <?php if ($activo): ?>
                  <span class="badge badge-success">Activo</span>
                <?php else: ?>
                  <span class="badge badge-warning">Inactivo</span>
                <?php endif; ?>
              </td>
              <td style="text-align: right;">
                <div style="display: flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap;">
                  <button type="button" class="btn btn-outline btn-sm btn-probar-popup"
                          data-id="<?= (int)$c['id'] ?>"
                          data-titulo="<?= e($c['titulo']) ?>"
                          data-img="<?= e($imagenUrl) ?>"
                          data-duracion="<?= (int)$c['duracion_seg'] ?>"
                          title="Previsualizar cómo lo verá el usuario">
                    👁 Probar
                  </button>
                  <a href="form.php?id=<?= (int)$c['id'] ?>" class="btn btn-outline btn-sm" style="color: #0b3d76; border-color: #cbd5e1;">
                    Editar
                  </a>
                  <form action="eliminar.php" method="POST" style="display: inline;" onsubmit="return confirm('¿Está seguro de eliminar esta convocatoria? Esta acción borrará la imagen y no se puede deshacer.');">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>"/>
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

<script src="<?= $baseUrl ?>js/popup.js"></script>
<script>
document.querySelectorAll('.btn-probar-popup').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var data = {
      id: this.dataset.id,
      titulo: this.dataset.titulo,
      imagen_url: this.dataset.img,
      retraso_seg: 0,
      duracion_seg: parseInt(this.dataset.duracion, 10) || 0,
      frecuencia: 'cada_visita',
      version: 'test_' + Date.now()
    };
    if (typeof window.JUSHSAL_PROBAR_POPUP === 'function') {
      window.JUSHSAL_PROBAR_POPUP(data);
    } else {
      alert('Error cargando módulo popup');
    }
  });
});
</script>

<?php require_once dirname(__DIR__, 2) . '/admin/footer.php'; ?>
