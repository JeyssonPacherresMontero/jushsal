<?php
/**
 * JUSHSAL - Gestión de Logros por Año (admin/presidentes/logros.php)
 * Administración de obras e hitos realizados año a año por cada gestión.
 */

define('JUSHSAL_INIT', true);

require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/helpers.php';
require_once dirname(dirname(__DIR__)) . '/includes/modelos/gestiones.php';

// Permisos
requerir_rol(['admin', 'comunicaciones']);

$gestionId = !empty($_GET['gestion_id']) ? (int)$_GET['gestion_id'] : null;
if (!$gestionId) {
    flash('error', 'Debe seleccionar una gestión presidencial.');
    redirigir('listar.php');
}

$gestion = gestiones_obtener_por_id($gestionId);
if (!$gestion) {
    flash('error', 'La gestión solicitada no existe.');
    redirigir('listar.php');
}

$logroEditar = null;
if (!empty($_GET['editar_logro'])) {
    $logroEditar = logros_obtener_por_id((int)$_GET['editar_logro']);
}

$tituloPanel = 'Logros de Gestión · ' . $gestion['presidente_nombre'];
require_once dirname(__DIR__) . '/header.php';

$logros = logros_listar_por_gestion($gestionId);
$logrosPorAnio = logros_agrupar_por_anio($logros);

$anioMin = (int)$gestion['periodo_inicio'];
$anioMax = !empty($gestion['periodo_fin']) ? (int)$gestion['periodo_fin'] : (int)date('Y');
?>

<div class="admin-title-row">
  <div>
    <div style="font-size:13px; color:#64748b; margin-bottom:4px;">
      <a href="listar.php" style="color:#0b3d76; text-decoration:none;">Gestiones</a> / Obras y Logros
    </div>
    <h1>Logros de <?= e($gestion['presidente_nombre']) ?></h1>
    <p style="margin:4px 0 0; color:#64748b; font-size:14px;">
      Periodo institucional: <strong><?= e($gestion['periodo_inicio']) ?> – <?= !empty($gestion['periodo_fin']) ? e($gestion['periodo_fin']) : 'En curso' ?></strong>
      <?php if (!empty($gestion['es_vigente'])): ?>
        <span class="badge" style="background:#fef3c7; color:#92400e; margin-left:8px;">★ Gestión Vigente</span>
      <?php endif; ?>
    </p>
  </div>
  <div>
    <a href="listar.php" class="btn btn-outline">← Volver a Gestiones</a>
  </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1.4fr; gap:24px; align-items:start;">

  <!-- Formulario de agregar / editar logro -->
  <div class="admin-card" style="margin:0;">
    <h2 style="font-size:17px; color:#0b3d76; margin:0 0 16px 0; font-family:'Montserrat',sans-serif;">
      <?= $logroEditar ? 'Editar Logro / Hito' : '+ Registrar Nuevo Logro' ?>
    </h2>

    <form method="POST" action="logro_guardar.php" enctype="multipart/form-data">
      <?= csrf_campo() ?>
      <input type="hidden" name="gestion_id" value="<?= $gestionId ?>"/>
      <input type="hidden" name="logro_id" value="<?= $logroEditar ? (int)$logroEditar['id'] : '' ?>"/>

      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
        <div>
          <label for="anio" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">
            Año del logro <span style="color:#ef4444;">*</span>
          </label>
          <input type="number" id="anio" name="anio" required
                 min="<?= $anioMin ?>" max="<?= $anioMax ?>"
                 value="<?= e($logroEditar['anio'] ?? $anioMin) ?>"
                 style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; box-sizing:border-box;"/>
          <small style="color:#64748b; font-size:11px;">Rango: <?= $anioMin ?> al <?= $anioMax ?></small>
        </div>

        <div>
          <label for="orden" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">
            Orden
          </label>
          <input type="number" id="orden" name="orden" value="<?= e($logroEditar['orden'] ?? 0) ?>"
                 style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; box-sizing:border-box;"/>
          <small style="color:#64748b; font-size:11px;">0 = normal, menor primero</small>
        </div>
      </div>

      <div style="margin-bottom:14px;">
        <label for="titulo" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">
          Título de la obra o logro <span style="color:#ef4444;">*</span>
        </label>
        <input type="text" id="titulo" name="titulo" required maxlength="160"
               value="<?= e($logroEditar['titulo'] ?? '') ?>"
               placeholder="Ej.: Descolmatación integral del Canal Principal"
               style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; box-sizing:border-box;"/>
      </div>

      <div style="margin-bottom:14px;">
        <label for="descripcion" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">
          Descripción detallada
        </label>
        <textarea id="descripcion" name="descripcion" rows="4"
                  placeholder="Detalles sobre lo realizado en este año..."
                  style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; box-sizing:border-box;"><?= e($logroEditar['descripcion'] ?? '') ?></textarea>
      </div>

      <div style="margin-bottom:20px; background:#f8fafc; padding:12px; border-radius:6px; border:1px solid #e2e8f0;">
        <label for="imagen" style="display:block; font-size:12px; font-weight:600; color:#334155; margin-bottom:4px;">
          Fotografía de la obra (opcional)
        </label>
        <?php if (!empty($logroEditar['imagen'])): ?>
          <div style="margin-bottom:8px;">
            <img src="<?= $baseUrl . e($logroEditar['imagen']) ?>" alt="Foto" style="width:80px; height:60px; object-fit:cover; border-radius:4px;"/>
          </div>
        <?php endif; ?>
        <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp" style="font-size:12px; width:100%;"/>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:8px;">
        <?php if ($logroEditar): ?>
          <a href="logros.php?gestion_id=<?= $gestionId ?>" class="btn btn-outline btn-sm">Cancelar edición</a>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary" style="padding:8px 18px;">
          <?= $logroEditar ? 'Actualizar Logro' : 'Guardar Logro' ?>
        </button>
      </div>
    </form>
  </div>

  <!-- Listado de logros agrupados por año -->
  <div class="admin-card" style="margin:0;">
    <h2 style="font-size:17px; color:#0b3d76; margin:0 0 16px 0; font-family:'Montserrat',sans-serif;">
      Hitos y Obras Registradas (<?= count($logros) ?>)
    </h2>

    <?php if (empty($logros)): ?>
      <div style="text-align:center; padding:40px 20px; color:#64748b;">
        <p style="margin:0; font-size:14px;">Aún no hay logros registrados para esta gestión.</p>
        <small style="color:#94a3b8;">Utiliza el formulario de la izquierda para agregar las obras realizadas año a año.</small>
      </div>
    <?php else: ?>
      <?php foreach ($logrosPorAnio as $anio => $itemsAnio): ?>
        <div style="margin-bottom:24px;">
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
            <span style="background:#0b3d76; color:#fff; font-size:13px; font-weight:700; padding:2px 10px; border-radius:12px;">
              Año <?= e($anio) ?>
            </span>
            <span style="font-size:12px; color:#64748b;"><?= count($itemsAnio) ?> logro(s)</span>
          </div>

          <div style="display:flex; flex-direction:column; gap:10px;">
            <?php foreach ($itemsAnio as $item): ?>
              <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px; display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
                <?php if (!empty($item['imagen'])): ?>
                  <img src="<?= $baseUrl . e($item['imagen']) ?>" alt="Foto" style="width:60px; height:45px; object-fit:cover; border-radius:4px; flex-shrink:0;"/>
                <?php endif; ?>
                <div style="flex:1;">
                  <strong style="font-size:14px; color:#0b3d76; display:block;"><?= e($item['titulo']) ?></strong>
                  <?php if (!empty($item['descripcion'])): ?>
                    <p style="margin:4px 0 0 0; font-size:13px; color:#475569;"><?= e($item['descripcion']) ?></p>
                  <?php endif; ?>
                </div>
                <div style="white-space:nowrap; display:flex; gap:6px;">
                  <a href="logros.php?gestion_id=<?= $gestionId ?>&editar_logro=<?= $item['id'] ?>" class="btn btn-outline btn-sm" style="padding:4px 8px; font-size:12px;">Editar</a>
                  
                  <form method="POST" action="logro_eliminar.php" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este logro?');">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="gestion_id" value="<?= $gestionId ?>"/>
                    <input type="hidden" name="logro_id" value="<?= $item['id'] ?>"/>
                    <button type="submit" class="btn btn-outline btn-sm" style="padding:4px 8px; font-size:12px; color:#ef4444; border-color:#fca5a5;">×</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<?php
require_once dirname(__DIR__) . '/footer.php';
?>
