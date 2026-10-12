<?php
/**
 * JUSHSAL - Detalle y Gestión de Consulta Ciudadana (admin/contactos/detalle.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/contacto.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mensaje = contacto_obtener_por_id($id);

if (!$mensaje) {
    flash('error', 'El mensaje de contacto solicitado no existe.');
    header('Location: listar.php');
    exit;
}

$tituloPanel = 'Detalle de Consulta #' . $id;
require_once dirname(__DIR__, 2) . '/admin/header.php';

$estado = $mensaje['estado'];
$badgeColor = '#475569';
$badgeBg = '#f1f5f9';
$badgeTexto = 'Archivado';

if ($estado === 'pendiente') {
    $badgeColor = '#b45309';
    $badgeBg = '#fef3c7';
    $badgeTexto = 'Pendiente de Atención';
} elseif ($estado === 'atendido') {
    $badgeColor = '#065f46';
    $badgeBg = '#dcfce7';
    $badgeTexto = 'Atendido';
}
?>

<div class="admin-title-row">
  <div>
    <h1>Consulta de <?= e($mensaje['nombre'] . ' ' . $mensaje['apellidos']) ?></h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Recibido el <?= date('d/m/Y \a \l\a\s H:i', strtotime($mensaje['created_at'])) ?>
    </p>
  </div>
  <div>
    <a href="listar.php" class="btn btn-outline" style="font-size: 14px; padding: 10px 18px;">
      ← Volver a la Bandeja
    </a>
  </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
  <!-- Columna izquierda: Información del mensaje -->
  <div>
    <div class="admin-card" style="margin-bottom: 20px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
        <span class="badge" style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; font-size: 13px; padding: 5px 12px;">
          Estado actual: <?= $badgeTexto ?>
        </span>
        <span style="font-size: 12px; color: #94a3b8;">
          Dirección IP: <?= e($mensaje['ip'] ?? '—') ?>
        </span>
      </div>

      <h3 style="font-size: 14px; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; margin: 0 0 10px;">
        Contenido de la Consulta
      </h3>
      <div style="background: #f8fafc; border-radius: 8px; padding: 18px; border: 1px solid #e2e8f0; font-size: 15px; color: #1e293b; line-height: 1.6; white-space: pre-wrap; margin-bottom: 20px;">
<?= e($mensaje['mensaje']) ?>
      </div>

      <?php if (!empty($mensaje['atendido_por_nombre'])): ?>
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 12px 16px; font-size: 13px; color: #065f46;">
          <strong>Atendido por:</strong> <?= e($mensaje['atendido_por_nombre']) ?>
          el <?= !empty($mensaje['atendido_at']) ? date('d/m/Y H:i', strtotime($mensaje['atendido_at'])) : '—' ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Formulario de cambio de estado y notas internas -->
    <div class="admin-card">
      <h3 style="font-size: 16px; color: #0b3d76; margin: 0 0 16px;">
        Actualizar Estado y Gestión Interna
      </h3>
      <form action="cambiar_estado.php" method="POST">
        <?= csrf_campo() ?>
        <input type="hidden" name="id" value="<?= (int)$mensaje['id'] ?>"/>

        <div style="margin-bottom: 16px;">
          <label for="estado" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
            Cambiar Estado *
          </label>
          <select id="estado" name="estado" required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #fff;">
            <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>Pendiente (Aún sin responder)</option>
            <option value="atendido" <?= $estado === 'atendido' ? 'selected' : '' ?>>Atendido (Consulta resuelta)</option>
            <option value="archivado" <?= $estado === 'archivado' ? 'selected' : '' ?>>Archivado (Cerrado)</option>
          </select>
        </div>

        <div style="margin-bottom: 20px;">
          <label for="notas_atencion" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
            Notas Internas de Seguimiento
          </label>
          <textarea id="notas_atencion" name="notas_atencion" rows="4"
                    placeholder="Registra detalles de la respuesta, fecha de contacto, canal utilizado, etc..."
                    style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; font-family: inherit;"><?= e($mensaje['notas_atencion'] ?? '') ?></textarea>
          <span style="font-size: 12px; color: #64748b;">Estas notas son privadas y solo visibles para el equipo administrativo.</span>
        </div>

        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-size: 14px;">
          Guardar Cambios
        </button>
      </form>
    </div>
  </div>

  <!-- Columna derecha: Datos del remitente y acciones rápidas -->
  <div>
    <div class="admin-card">
      <h3 style="font-size: 15px; color: #0b3d76; margin: 0 0 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
        Datos del Remitente
      </h3>

      <div style="margin-bottom: 14px;">
        <span style="font-size: 11.5px; text-transform: uppercase; color: #64748b; font-weight: 600; display: block;">Nombre Completo</span>
        <strong style="font-size: 15px; color: #1e293b;"><?= e($mensaje['nombre'] . ' ' . $mensaje['apellidos']) ?></strong>
      </div>

      <div style="margin-bottom: 14px;">
        <span style="font-size: 11.5px; text-transform: uppercase; color: #64748b; font-weight: 600; display: block;">Correo Electrónico</span>
        <a href="mailto:<?= e($mensaje['email']) ?>?subject=<?= rawurlencode('Respuesta a tu consulta - Junta de Usuarios San Lorenzo') ?>"
           style="font-size: 14px; color: #0284c7; text-decoration: none; word-break: break-all;">
          <?= e($mensaje['email']) ?> ↗
        </a>
      </div>

      <?php if (!empty($mensaje['telefono'])): ?>
        <div style="margin-bottom: 16px;">
          <span style="font-size: 11.5px; text-transform: uppercase; color: #64748b; font-weight: 600; display: block;">Teléfono / Celular</span>
          <span style="font-size: 14.5px; color: #1e293b; font-weight: 600;"><?= e($mensaje['telefono']) ?></span>
        </div>

        <?php
          $numLimpio = preg_replace('/\D/', '', $mensaje['telefono']);
          if (!str_starts_with($numLimpio, '51') && strlen($numLimpio) === 9) {
              $numLimpio = '51' . $numLimpio;
          }
        ?>
        <a href="https://wa.me/<?= e($numLimpio) ?>" target="_blank" rel="noopener noreferrer"
           class="btn btn-sm" style="background: #25d366; color: #fff; width: 100%; text-align: center; margin-bottom: 8px; display: block;">
          Abrir Chat de WhatsApp ↗
        </a>
      <?php endif; ?>

      <a href="mailto:<?= e($mensaje['email']) ?>?subject=<?= rawurlencode('Respuesta institucional - JUSHSAL') ?>"
         class="btn btn-outline btn-sm" style="width: 100%; text-align: center; display: block; color: #0b3d76; border-color: #cbd5e1;">
        Responder por Correo ✉
      </a>
    </div>
  </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/admin/footer.php'; ?>
