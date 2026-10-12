<?php
/**
 * JUSHSAL - Gestión de Presidentes: Formulario (admin/presidentes/form.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/helpers.php';
require_once dirname(dirname(__DIR__)) . '/includes/modelos/gestiones.php';

// Permisos
requerir_rol(['admin', 'comunicaciones']);

$id = !empty($_GET['id']) ? (int)$_GET['id'] : null;
$gestion = null;

if ($id) {
    $gestion = gestiones_obtener_por_id($id);
    if (!$gestion) {
        flash('error', 'La gestión presidencial solicitada no existe.');
        redirigir('listar.php');
    }
    $tituloPanel = 'Editar Gestión: ' . $gestion['presidente_nombre'];
} else {
    $tituloPanel = 'Nueva Gestión Presidencial';
}

require_once dirname(__DIR__) . '/header.php';
?>

<div class="admin-title-row">
  <div>
    <h1><?= $id ? 'Editar Gestión Presidencial' : 'Nueva Gestión Presidencial' ?></h1>
    <p style="margin:4px 0 0; color:#64748b; font-size:14px;">Registra al presidente institucional y el periodo de inicio y fin de su mandato.</p>
  </div>
  <div>
    <a href="listar.php" class="btn btn-outline">← Volver al listado</a>
  </div>
</div>

<div class="admin-card" style="max-width:800px; margin:0 auto;">
  <form method="POST" action="guardar.php" enctype="multipart/form-data">
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= $id ? (int)$id : '' ?>"/>

    <div style="margin-bottom:20px;">
      <label for="presidente_nombre" style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">
        Nombre completo del Presidente <span style="color:#ef4444;">*</span>
      </label>
      <input type="text" id="presidente_nombre" name="presidente_nombre" required maxlength="150"
             value="<?= e($gestion['presidente_nombre'] ?? '') ?>"
             placeholder="Ej.: Miguel Castillo Vera"
             style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:15px; box-sizing:border-box;"/>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
      <div>
        <label for="periodo_inicio" style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">
          Año de inicio <span style="color:#ef4444;">*</span>
        </label>
        <input type="number" id="periodo_inicio" name="periodo_inicio" required min="1950" max="2099"
               value="<?= e($gestion['periodo_inicio'] ?? date('Y')) ?>"
               placeholder="Ej.: 2026"
               style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:15px; box-sizing:border-box;"/>
      </div>
      <div>
        <label for="periodo_fin" style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">
          Año de fin (dejar vacío si está en curso)
        </label>
        <input type="number" id="periodo_fin" name="periodo_fin" min="1950" max="2099"
               value="<?= e($gestion['periodo_fin'] ?? '') ?>"
               placeholder="Ej.: 2029"
               style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:15px; box-sizing:border-box;"/>
        <small style="color:#64748b; font-size:12px;">Debe ser mayor o igual al año de inicio.</small>
      </div>
    </div>

    <div style="margin-bottom:20px;">
      <label for="semblanza" style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">
        Semblanza / Reseña biográfica
      </label>
      <textarea id="semblanza" name="semblanza" rows="5"
                placeholder="Breve reseña sobre su trayectoria y gestión al frente de la Junta de Usuarios..."
                style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; line-height:1.6; box-sizing:border-box;"><?= e($gestion['semblanza'] ?? '') ?></textarea>
    </div>

    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:18px; margin-bottom:24px;">
      <h3 style="margin:0 0 14px 0; font-size:14px; color:#0b3d76; font-family:'Montserrat',sans-serif;">Fotografía oficial</h3>

      <?php if (!empty($gestion['foto'])): ?>
        <div style="display:flex; align-items:center; gap:16px; margin-bottom:14px;">
          <img src="<?= $baseUrl . e($gestion['foto']) ?>" alt="Foto actual" style="width:70px; height:70px; object-fit:cover; border-radius:50%; border:2px solid #0b3d76;"/>
          <label style="font-size:13px; color:#ef4444; cursor:pointer;">
            <input type="checkbox" name="eliminar_foto" value="1"/>
            <span>Eliminar foto actual</span>
          </label>
        </div>
      <?php endif; ?>

      <div>
        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" style="font-size:13px;"/>
        <small style="color:#64748b; font-size:12px; display:block; margin-top:6px;">
          Formatos admitidos: JPG, PNG, WEBP (máx. 5 MB). Se recomienda una foto cuadrada o de retrato.
        </small>
      </div>
    </div>

    <div style="margin-bottom:24px;">
      <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer; background:#fffbeb; border:1px solid #fef3c7; padding:14px; border-radius:8px;">
        <input type="checkbox" name="es_vigente" value="1" <?= !empty($gestion['es_vigente']) ? 'checked' : '' ?> style="margin-top:3px;"/>
        <div>
          <strong style="color:#92400e; font-size:14px; display:block;">Marcar como Gestión Presidencial Vigente</strong>
          <span style="font-size:12px; color:#78350f;">
            Al marcar esta casilla, el sistema desmarcará automáticamente cualquier otra gestión vigente anterior mediante una transacción segura. Solo puede existir un presidente vigente a la vez.
          </span>
        </div>
      </label>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:12px; border-top:1px solid #e2e8f0; padding-top:20px;">
      <a href="listar.php" class="btn btn-outline">Cancelar</a>
      <button type="submit" class="btn btn-primary" style="padding:10px 24px;">
        <?= $id ? 'Guardar Cambios' : 'Registrar Gestión' ?>
      </button>
    </div>
  </form>
</div>

<?php
require_once dirname(__DIR__) . '/footer.php';
?>
