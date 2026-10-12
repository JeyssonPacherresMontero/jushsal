<?php
/**
 * JUSHSAL - Formulario de Edición de Comisión (admin/comisiones/form.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/comisiones.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$comision = null;

if ($id > 0) {
    $comision = comisiones_obtener_por_id($id);
}

if (!$comision) {
    flash('error', 'La comisión solicitada no existe.');
    header('Location: listar.php');
    exit;
}

$tituloPanel = 'Editar Comisión N.° ' . sprintf('%02d', (int)$comision['numero']);
require_once dirname(__DIR__, 2) . '/admin/header.php';
?>

<div class="admin-title-row">
  <div>
    <h1><?= e($comision['nombre']) ?></h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Sub Sector Hidráulico N.° <?= (int)$comision['numero'] ?> de la Junta de Usuarios San Lorenzo.
    </p>
  </div>
  <div>
    <a href="listar.php" class="btn btn-outline" style="font-size: 14px; padding: 10px 18px;">
      ← Volver al listado
    </a>
  </div>
</div>

<div class="admin-card" style="max-width: 800px;">
  <form action="guardar.php" method="POST">
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= (int)$comision['id'] ?>"/>

    <h2 style="font-size: 17px; color: #0b3d76; margin: 0 0 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
      1. Información de la Comisión
    </h2>

    <div style="display: grid; grid-template-columns: 100px 1fr; gap: 16px; margin-bottom: 20px;">
      <div>
        <label for="numero" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Número
        </label>
        <input type="text" id="numero" value="<?= sprintf('%02d', (int)$comision['numero']) ?>" disabled
               style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; background: #f8fafc; border-radius: 6px; font-weight: 700; text-align: center; font-size: 15px; color: #0b3d76;"/>
      </div>
      <div>
        <label for="nombre" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Nombre Oficial de la Comisión *
        </label>
        <input type="text" id="nombre" name="nombre" required maxlength="100" value="<?= e($comision['nombre']) ?>"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px;"/>
      </div>
    </div>

    <div style="margin-bottom: 28px;">
      <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
        <input type="checkbox" name="activo" value="1" <?= (int)$comision['activo'] === 1 ? 'checked' : '' ?> style="width: 18px; height: 18px;"/>
        <span style="font-weight: 600; color: #1e293b; font-size: 14.5px;">Comisión activa en el portal público</span>
      </label>
    </div>

    <h2 style="font-size: 17px; color: #0b3d76; margin: 0 0 16px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
      2. Presidente / Representante de la Comisión
    </h2>

    <div style="margin-bottom: 20px;">
      <label for="nombre_completo" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
        Nombres y Apellidos del Presidente / Delegado
      </label>
      <input type="text" id="nombre_completo" name="nombre_completo" maxlength="150" 
             value="<?= e($comision['presidente_nombre'] ?? '') ?>"
             placeholder="Ej. Juan Manuel Ramos Morales (dejar vacío si aún no está asignado)"
             style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px;"/>
      <small style="color: #64748b; display: block; margin-top: 4px;">
        Este nombre se mostrará en el portal web institucional.
      </small>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
      <div>
        <label for="periodo_inicio" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Año de Inicio del Periodo
        </label>
        <input type="number" id="periodo_inicio" name="periodo_inicio" min="1960" max="2050"
               value="<?= e($comision['periodo_inicio'] ?? '2026') ?>"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
      </div>
      <div>
        <label for="periodo_fin" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Año de Fin del Periodo
        </label>
        <input type="number" id="periodo_fin" name="periodo_fin" min="1960" max="2050"
               value="<?= e($comision['periodo_fin'] ?? '2029') ?>"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
      </div>
    </div>

    <div style="margin-bottom: 24px; padding: 16px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px;">
      <label for="celular" style="display: block; font-weight: 600; margin-bottom: 6px; color: #92400e;">
        Teléfono / Celular de Contacto (Privado - Solo Panel Interno)
      </label>
      <input type="text" id="celular" name="celular" maxlength="20"
             value="<?= e($comision['celular'] ?? '') ?>"
             placeholder="Ej. +51 987 654 321"
             style="width: 100%; padding: 10px 14px; border: 1px solid #fcd34d; border-radius: 6px; font-size: 14px; background: #fff;"/>
      <small style="color: #b45309; display: block; margin-top: 6px;">
        🔒 <strong>Privacidad de Datos:</strong> Este número de celular se almacena únicamente para coordinaciones institucionales del panel de JUSHSAL. Nunca se publicará ni se filtrará en la página web pública.
      </small>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #e2e8f0;">
      <a href="listar.php" class="btn btn-outline" style="padding: 10px 20px;">Cancelar</a>
      <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
        Guardar Cambios
      </button>
    </div>
  </form>
</div>

<?php require_once dirname(__DIR__, 2) . '/admin/footer.php'; ?>
