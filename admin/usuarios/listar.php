<?php
/**
 * JUSHSAL - Gestión de Usuarios del Panel (admin/usuarios/listar.php)
 * Exclusivo para administradores institucionales.
 */

define('JUSHSAL_INIT', true);

$tituloPanel = 'Usuarios del Panel';
require_once dirname(__DIR__, 2) . '/admin/header.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/usuarios.php';

// Verificación estricta de rol admin
requerir_rol(['admin']);

$usuarios = usuarios_listar();
$usuarioSesion = usuario_actual();
?>

<div class="admin-title-row">
  <div>
    <h1>Usuarios del Panel Administrativo</h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Administración de cuentas con acceso al backoffice institucional y asignación de roles.
    </p>
  </div>
  <div>
    <a href="form.php" class="btn btn-primary" style="font-size: 14px; padding: 10px 18px;">
      + Nuevo Usuario
    </a>
  </div>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Usuario</th>
          <th>Correo Electrónico</th>
          <th>Rol Asignado</th>
          <th>Estado</th>
          <th>Último Acceso</th>
          <th style="text-align: right; width: 260px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
          <?php
            $esPropio = (int)$u['id'] === (int)$usuarioSesion['id'];
            $activo = (int)$u['activo'] === 1;
            $rolClass = $u['rol_nombre'] === 'admin' ? 'background:#e0f2fe; color:#0369a1;' : 'background:#fef3c7; color:#b45309;';
          ?>
          <tr>
            <td>
              <strong style="color: #0b3d76; font-size: 15px;"><?= e($u['nombre']) ?></strong>
              <?php if ($esPropio): ?>
                <span style="font-size: 11px; background: #e2e8f0; color: #475569; padding: 2px 6px; border-radius: 4px; font-weight: 600; margin-left: 6px;">
                  Tu cuenta
                </span>
              <?php endif; ?>
            </td>
            <td style="color: #334155;">
              <?= e($u['email']) ?>
            </td>
            <td>
              <span class="badge" style="<?= $rolClass ?>">
                <?= e(ucfirst($u['rol_nombre'])) ?>
              </span>
            </td>
            <td>
              <?php if ($activo): ?>
                <span class="badge badge-success">Activo</span>
              <?php else: ?>
                <span class="badge badge-secondary">Inactivo</span>
              <?php endif; ?>
            </td>
            <td style="font-size: 13px; color: #64748b;">
              <?= !empty($u['ultimo_acceso']) ? e(date('d/m/Y H:i', strtotime($u['ultimo_acceso']))) : 'Nunca' ?>
            </td>
            <td style="text-align: right;">
              <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                
                <!-- Resetear contraseña -->
                <button type="button" class="btn btn-outline btn-sm btn-abrir-reset"
                        data-id="<?= (int)$u['id'] ?>"
                        data-nombre="<?= e($u['nombre']) ?>"
                        style="color: #0b3d76; border-color: #cbd5e1;">
                  Contraseña
                </button>

                <!-- Activar / Desactivar -->
                <?php if (!$esPropio): ?>
                  <form action="cambiar_estado.php" method="POST" style="display: inline;" onsubmit="return confirm('¿Está seguro de <?= $activo ? 'desactivar' : 'activar' ?> el acceso a este usuario?');">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>"/>
                    <input type="hidden" name="activo" value="<?= $activo ? 0 : 1 ?>"/>
                    <?php if ($activo): ?>
                      <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                        Desactivar
                      </button>
                    <?php else: ?>
                      <button type="submit" class="btn btn-sm" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                        Activar
                      </button>
                    <?php endif; ?>
                  </form>
                <?php else: ?>
                  <span style="font-size: 12px; color: #94a3b8; font-style: italic; padding: 0 4px;">Activo</span>
                <?php endif; ?>

              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal para Resetear Contraseña -->
<div class="modal" id="modalResetPassword" role="dialog" aria-modal="true" aria-labelledby="modalResetTitle">
  <div class="modal-backdrop" id="modalResetBackdrop"></div>
  <div class="modal-card" style="width: min(480px, 92vw); padding: 24px;">
    <button type="button" class="modal-close" id="modalResetClose" aria-label="Cerrar modal">&times;</button>
    <h3 id="modalResetTitle" style="color: #0b3d76; margin: 0 0 6px;">Resetear Contraseña</h3>
    <p id="modalResetUsuario" style="font-size: 13.5px; color: #64748b; margin: 0 0 18px;"></p>

    <form action="reset_password.php" method="POST">
      <?= csrf_campo() ?>
      <input type="hidden" name="id" id="resetUserId" value="0"/>

      <div style="margin-bottom: 16px;">
        <label for="nueva_password" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13.5px;">
          Nueva Contraseña (mínimo 8 caracteres) *
        </label>
        <input type="password" id="nueva_password" name="nueva_password" required minlength="8"
               placeholder="••••••••"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
      </div>

      <div style="margin-bottom: 20px;">
        <label for="confirmar_password" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 13.5px;">
          Confirmar Nueva Contraseña *
        </label>
        <input type="password" id="confirmar_password" name="confirmar_password" required minlength="8"
               placeholder="••••••••"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
      </div>

      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" id="btnCancelReset" class="btn btn-outline" style="padding: 8px 16px;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="padding: 8px 18px;">Actualizar Contraseña</button>
      </div>
    </form>
  </div>
</div>

<script>
(function() {
  var modal = document.getElementById('modalResetPassword');
  var closeBtn = document.getElementById('modalResetClose');
  var cancelBtn = document.getElementById('btnCancelReset');
  var backdrop = document.getElementById('modalResetBackdrop');
  var userIdInput = document.getElementById('resetUserId');
  var userDesc = document.getElementById('modalResetUsuario');

  function abrirModal(id, nombre) {
    userIdInput.value = id;
    userDesc.textContent = 'Actualizando credenciales para el usuario: ' + nombre;
    modal.classList.add('open');
  }

  function cerrarModal() {
    modal.classList.remove('open');
  }

  document.querySelectorAll('.btn-abrir-reset').forEach(function(btn) {
    btn.addEventListener('click', function() {
      abrirModal(this.dataset.id, this.dataset.nombre);
    });
  });

  if (closeBtn) closeBtn.addEventListener('click', cerrarModal);
  if (cancelBtn) cancelBtn.addEventListener('click', cerrarModal);
  if (backdrop) backdrop.addEventListener('click', cerrarModal);
})();
</script>

<?php require_once dirname(__DIR__, 2) . '/admin/footer.php'; ?>
