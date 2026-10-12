<?php
/**
 * JUSHSAL - Bandeja de Mensajes de Contacto (admin/contactos/listar.php)
 */

define('JUSHSAL_INIT', true);

$tituloPanel = 'Mensajes de Contacto';
require_once dirname(__DIR__, 2) . '/admin/header.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/contacto.php';

$filtroEstado = isset($_GET['estado']) && in_array($_GET['estado'], ['pendiente', 'atendido', 'archivado'], true)
    ? $_GET['estado']
    : null;

$mensajes = contacto_listar_mensajes($filtroEstado, 100);
$conteos = contacto_conteo_estados();
?>

<div class="admin-title-row">
  <div>
    <h1>Bandeja de Consultas Ciudadanas</h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Consultas y solicitudes de información recibidas a través del portal institucional de JUSHSAL.
    </p>
  </div>
</div>

<!-- Filtros por estado (Pestañas) -->
<div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
  <a href="listar.php" class="btn btn-sm"
     style="<?= $filtroEstado === null ? 'background: #0b3d76; color: #fff;' : 'background: #fff; color: #475569; border: 1px solid #cbd5e1;' ?>">
    Todos (<?= (int)$conteos['total'] ?>)
  </a>
  <a href="listar.php?estado=pendiente" class="btn btn-sm"
     style="<?= $filtroEstado === 'pendiente' ? 'background: #f59e0b; color: #fff;' : 'background: #fff; color: #b45309; border: 1px solid #fde68a;' ?>">
    Pendientes (<?= (int)$conteos['pendiente'] ?>)
  </a>
  <a href="listar.php?estado=atendido" class="btn btn-sm"
     style="<?= $filtroEstado === 'atendido' ? 'background: #10b981; color: #fff;' : 'background: #fff; color: #047857; border: 1px solid #a7f3d0;' ?>">
    Atendidos (<?= (int)$conteos['atendido'] ?>)
  </a>
  <a href="listar.php?estado=archivado" class="btn btn-sm"
     style="<?= $filtroEstado === 'archivado' ? 'background: #64748b; color: #fff;' : 'background: #fff; color: #475569; border: 1px solid #cbd5e1;' ?>">
    Archivados (<?= (int)$conteos['archivado'] ?>)
  </a>
</div>

<div class="admin-card">
  <?php if (empty($mensajes)): ?>
    <div style="text-align: center; padding: 40px 20px; color: #64748b;">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.3; margin-bottom: 12px;"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
      <p style="font-size: 15px; margin: 0;">No se encontraron mensajes en esta bandeja.</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 100px;">Estado</th>
            <th style="width: 140px;">Fecha y Hora</th>
            <th style="width: 180px;">Remitente</th>
            <th style="width: 200px;">Datos de Contacto</th>
            <th>Consulta / Mensaje</th>
            <th style="text-align: right; width: 120px;">Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($mensajes as $m): ?>
            <?php
              $estado = $m['estado'];
              $badgeColor = '#475569';
              $badgeBg = '#f1f5f9';
              $badgeTexto = 'Archivado';

              if ($estado === 'pendiente') {
                  $badgeColor = '#b45309';
                  $badgeBg = '#fef3c7';
                  $badgeTexto = 'Pendiente';
              } elseif ($estado === 'atendido') {
                  $badgeColor = '#065f46';
                  $badgeBg = '#dcfce7';
                  $badgeTexto = 'Atendido';
              }
            ?>
            <tr>
              <td>
                <span class="badge" style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>;">
                  <?= $badgeTexto ?>
                </span>
              </td>
              <td style="font-size: 12.5px; color: #64748b; white-space: nowrap;">
                <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
              </td>
              <td>
                <strong style="color: #0b3d76; font-size: 14px; display: block;">
                  <?= e($m['nombre'] . ' ' . $m['apellidos']) ?>
                </strong>
                <span style="font-size: 11px; color: #94a3b8;">IP: <?= e($m['ip'] ?? '—') ?></span>
              </td>
              <td style="font-size: 13px;">
                <a href="mailto:<?= e($m['email']) ?>" style="color: #0369a1; text-decoration: none; display: block;">
                  <?= e($m['email']) ?>
                </a>
                <?php if (!empty($m['telefono'])): ?>
                  <span style="font-size: 12px; color: #475569; display: block; margin-top: 2px;">
                    Tel: <?= e($m['telefono']) ?>
                  </span>
                <?php endif; ?>
              </td>
              <td>
                <div style="font-size: 13.5px; color: #1e293b; line-height: 1.4; max-width: 520px;">
                  <?= e(mb_substr($m['mensaje'], 0, 140)) ?><?= mb_strlen($m['mensaje']) > 140 ? '...' : '' ?>
                </div>
                <?php if (!empty($m['notas_atencion'])): ?>
                  <div style="margin-top: 4px; font-size: 11.5px; color: #047857; background: #f0fdf4; padding: 2px 6px; border-radius: 4px; display: inline-block;">
                    <strong>Nota:</strong> <?= e(mb_substr($m['notas_atencion'], 0, 70)) ?>...
                  </div>
                <?php endif; ?>
              </td>
              <td style="text-align: right;">
                <a href="detalle.php?id=<?= (int)$m['id'] ?>" class="btn btn-outline btn-sm"
                   style="color: #0b3d76; border-color: #cbd5e1; white-space: nowrap;">
                  Gestionar →
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once dirname(__DIR__, 2) . '/admin/footer.php'; ?>
