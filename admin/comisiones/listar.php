<?php
/**
 * JUSHSAL - Listado de Comisiones de Usuarios (admin/comisiones/listar.php)
 */

define('JUSHSAL_INIT', true);

$tituloPanel = 'Comisiones de Usuarios';
require_once dirname(__DIR__, 2) . '/admin/header.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/comisiones.php';

$comisiones = comisiones_listar_admin();
?>

<div class="admin-title-row">
  <div>
    <h1>Comisiones de Usuarios (16 Sub Sectores)</h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Administración de las 16 comisiones de riego de JUSHSAL y sus presidentes para el periodo institucional.
    </p>
  </div>
</div>

<div class="admin-card">
  <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 13.5px; color: #1e40af;">
    <strong>ℹ Política de Privacidad de Datos:</strong> Los números celulares de contacto de los presidentes se gestionan exclusivamente en este panel para fines de coordinación interna. <strong>Nunca se publican ni se exponen en la web pública.</strong>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 60px;">N.°</th>
          <th>Comisión de Usuarios</th>
          <th>Presidente Actual / Delegado</th>
          <th>Periodo</th>
          <th>Contacto Interno</th>
          <th>Estado</th>
          <th style="text-align: right; width: 140px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($comisiones as $c): ?>
          <tr>
            <td>
              <span style="display: inline-block; width: 32px; height: 32px; border-radius: 50%; background: #0b3d76; color: #fff; text-align: center; line-height: 32px; font-weight: 700; font-size: 13px;">
                <?= sprintf('%02d', (int)$c['numero']) ?>
              </span>
            </td>
            <td>
              <strong style="color: #0b3d76; font-size: 15px;"><?= e($c['nombre']) ?></strong>
              <div style="font-size: 12px; color: #64748b;">Sub Sector Hidráulico N.° <?= (int)$c['numero'] ?></div>
            </td>
            <td>
              <?php if (!empty($c['presidente_nombre'])): ?>
                <strong style="color: #1e293b;"><?= e($c['presidente_nombre']) ?></strong>
              <?php else: ?>
                <span style="color: #94a3b8; font-style: italic;">Sin presidente asignado</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($c['periodo_inicio'])): ?>
                <span class="badge" style="background: #e0f2fe; color: #0369a1;">
                  <?= e($c['periodo_inicio']) ?> – <?= e($c['periodo_fin'] ?? '2029') ?>
                </span>
              <?php else: ?>
                <span style="color: #94a3b8;">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($c['celular'])): ?>
                <span style="font-family: monospace; font-size: 13px; color: #0f766e; background: #ccfbf1; padding: 2px 6px; border-radius: 4px;">
                  🔒 <?= e($c['celular']) ?>
                </span>
              <?php else: ?>
                <span style="color: #94a3b8; font-size: 13px;">Sin teléfono</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ((int)$c['activo'] === 1): ?>
                <span class="badge badge-success">Activo</span>
              <?php else: ?>
                <span class="badge badge-secondary">Inactivo</span>
              <?php endif; ?>
            </td>
            <td style="text-align: right;">
              <a href="form.php?id=<?= (int)$c['id'] ?>" class="btn btn-outline btn-sm" style="color: #0b3d76; border-color: #cbd5e1;">
                Editar
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/admin/footer.php'; ?>
