<?php
/**
 * JUSHSAL - Gestión de Presidentes: Listado Administrativo (admin/presidentes/listar.php)
 */

define('JUSHSAL_INIT', true);

$tituloPanel = 'Historial de Presidentes y Gestiones';
require_once dirname(__DIR__) . '/header.php';
require_once dirname(dirname(__DIR__)) . '/includes/modelos/gestiones.php';

// Verificar permisos
requerir_rol(['admin', 'comunicaciones']);

$gestiones = gestiones_listar_todas();
?>

<div class="admin-title-row">
  <div>
    <h1>Gestiones Presidenciales</h1>
    <p style="margin:4px 0 0; color:#64748b; font-size:14px;">Administra los periodos de gobierno de la Junta y las obras/logros realizados año a año.</p>
  </div>
  <div>
    <a href="form.php" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px;">
      <span>+ Nueva Gestión</span>
    </a>
  </div>
</div>

<div class="admin-card">
  <?php if (empty($gestiones)): ?>
    <div style="text-align:center; padding:40px 20px; color:#64748b;">
      <p style="font-size:16px; margin:0 0 10px 0;">No hay gestiones presidenciales registradas.</p>
      <a href="form.php" class="btn btn-primary btn-sm">+ Registrar gestión actual</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:70px;">Foto</th>
            <th>Presidente</th>
            <th>Periodo</th>
            <th>Semblanza</th>
            <th>Logros Registrados</th>
            <th>Estado</th>
            <th style="text-align:right;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($gestiones as $g): ?>
            <tr>
              <td>
                <?php if (!empty($g['foto'])): ?>
                  <img src="<?= $baseUrl . e($g['foto']) ?>" alt="Foto" style="width:48px; height:48px; object-fit:cover; border-radius:50%; display:block;"/>
                <?php else: ?>
                  <div style="width:48px; height:48px; background:#e8f1f8; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#0b3d76; font-size:14px;">
                    <?= mb_strtoupper(mb_substr($g['presidente_nombre'], 0, 2, 'UTF-8'), 'UTF-8') ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <strong style="color:#0b3d76; font-size:15px;"><?= e($g['presidente_nombre']) ?></strong>
              </td>
              <td style="white-space:nowrap; font-weight:600; color:#334155;">
                <?= e($g['periodo_inicio']) ?> – <?= !empty($g['periodo_fin']) ? e($g['periodo_fin']) : 'En curso' ?>
              </td>
              <td>
                <div style="font-size:13px; color:#64748b; max-width:320px;">
                  <?= !empty($g['semblanza']) ? e(mb_substr($g['semblanza'], 0, 90)) . '...' : '<em style="color:#94a3b8;">Sin semblanza</em>' ?>
                </div>
              </td>
              <td>
                <a href="logros.php?gestion_id=<?= $g['id'] ?>" class="badge" style="background:#e0f2fe; color:#0369a1; text-decoration:none; font-weight:700; padding:6px 12px; display:inline-flex; align-items:center; gap:4px;">
                  <span><?= (int)$g['total_logros'] ?> logro(s)</span>
                  <span style="font-size:11px;">→ Gestionar</span>
                </a>
              </td>
              <td>
                <?php if (!empty($g['es_vigente'])): ?>
                  <span class="badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a;">★ Vigente</span>
                <?php else: ?>
                  <span class="badge badge-secondary">Histórica</span>
                <?php endif; ?>
              </td>
              <td style="text-align:right; white-space:nowrap;">
                <a href="logros.php?gestion_id=<?= $g['id'] ?>" class="btn btn-outline btn-sm" style="color:#0b3d76; margin-right:4px;" title="Ver y registrar obras por año">Logros</a>
                <a href="form.php?id=<?= $g['id'] ?>" class="btn btn-outline btn-sm" style="margin-right:4px;">Editar</a>

                <?php if (empty($g['es_vigente'])): ?>
                  <form method="POST" action="guardar.php" style="display:inline;" onsubmit="return confirm('¿Desea marcar a esta gestión como la VIGENTE actual? Desmarcará a la anterior automáticamente.');">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="accion" value="hacer_vigente"/>
                    <input type="hidden" name="id" value="<?= $g['id'] ?>"/>
                    <button type="submit" class="btn btn-outline btn-sm" style="color:#15803d; border-color:#86efac; margin-right:4px;">Hacer Vigente</button>
                  </form>
                <?php endif; ?>

                <form method="POST" action="eliminar.php" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar esta gestión? Se eliminarán también todos los logros asociados.');">
                  <?= csrf_campo() ?>
                  <input type="hidden" name="id" value="<?= $g['id'] ?>"/>
                  <button type="submit" class="btn btn-outline btn-sm" style="color:#ef4444; border-color:#fca5a5;">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php
require_once dirname(__DIR__) . '/footer.php';
?>
