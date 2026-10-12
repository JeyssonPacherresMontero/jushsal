<?php
/**
 * JUSHSAL - Formulario de Nuevo Usuario (admin/usuarios/form.php)
 */

define('JUSHSAL_INIT', true);

$tituloPanel = 'Nuevo Usuario';
require_once dirname(__DIR__, 2) . '/admin/header.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/usuarios.php';

requerir_rol(['admin']);

$roles = usuarios_listar_roles();
?>

<div class="admin-title-row">
  <div>
    <h1>Nuevo Usuario del Panel</h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Registra un nuevo miembro del equipo institucional con rol de administración o comunicaciones.
    </p>
  </div>
  <div>
    <a href="listar.php" class="btn btn-outline" style="font-size: 14px; padding: 10px 18px;">
      ← Volver al listado
    </a>
  </div>
</div>

<div class="admin-card" style="max-width: 700px;">
  <form action="guardar.php" method="POST">
    <?= csrf_campo() ?>

    <div style="margin-bottom: 20px;">
      <label for="nombre" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
        Nombres y Apellidos *
      </label>
      <input type="text" id="nombre" name="nombre" required maxlength="120"
             placeholder="Ej. Ana Torres Morales"
             style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px;"/>
    </div>

    <div style="margin-bottom: 20px;">
      <label for="email" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
        Correo Electrónico Institucional *
      </label>
      <input type="email" id="email" name="email" required maxlength="150"
             placeholder="ejemplo@jusanlorenzo.org.pe"
             style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px;"/>
    </div>

    <div style="margin-bottom: 20px;">
      <label for="rol_id" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
        Rol y Nivel de Acceso *
      </label>
      <select id="rol_id" name="rol_id" required
              style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #fff;">
        <?php foreach ($roles as $r): ?>
          <option value="<?= (int)$r['id'] ?>" <?= $r['nombre'] === 'comunicaciones' ? 'selected' : '' ?>>
            <?= e(ucfirst($r['nombre'])) ?> — <?= e($r['descripcion']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
      <div>
        <label for="password" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Contraseña Temporal (mínimo 8 car.) *
        </label>
        <input type="password" id="password" name="password" required minlength="8"
               placeholder="••••••••"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
      </div>
      <div>
        <label for="password_confirm" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Confirmar Contraseña *
        </label>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="8"
               placeholder="••••••••"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
      </div>
    </div>

    <div style="margin-bottom: 24px;">
      <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
        <input type="checkbox" name="activo" value="1" checked style="width: 18px; height: 18px;"/>
        <span style="font-weight: 600; color: #1e293b; font-size: 14.5px;">Usuario activo (puede iniciar sesión)</span>
      </label>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #e2e8f0;">
      <a href="listar.php" class="btn btn-outline" style="padding: 10px 20px;">Cancelar</a>
      <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">Crear Usuario</button>
    </div>
  </form>
</div>

<?php require_once dirname(__DIR__, 2) . '/admin/footer.php'; ?>
