<?php
/**
 * JUSHSAL - Bitácora de Auditoría del Panel (admin/bitacora.php)
 * Exclusivo para administradores.
 */

define('JUSHSAL_INIT', true);

$tituloPanel = 'Bitácora de Auditoría';
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/includes/bitacora.php';

// Verificación estricta de rol admin
requerir_rol(['admin']);

// Parámetros de filtrado
$filtroAccion = trim($_GET['accion'] ?? '');
$filtroEntidad = trim($_GET['entidad'] ?? '');
$pagina = max(1, (int)($_GET['p'] ?? 1));
$porPagina = 25;
$offset = ($pagina - 1) * $porPagina;

$filtros = [];
if ($filtroAccion !== '') $filtros['accion'] = $filtroAccion;
if ($filtroEntidad !== '') $filtros['entidad'] = $filtroEntidad;

$totalRegistros = bitacora_contar($filtros);
$totalPaginas = max(1, (int)ceil($totalRegistros / $porPagina));
$registros = bitacora_listar($filtros, $porPagina, $offset);

$accionesDisponibles = bitacora_acciones_distintas();
$entidadesDisponibles = bitacora_entidades_distintas();
?>

<div class="admin-title-row">
  <div>
    <h1>Bitácora de Auditoría del Sistema</h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Registro cronológico de operaciones administrativas, accesos y modificaciones en el sistema.
    </p>
  </div>
  <div>
    <span style="font-size: 13.5px; color: #64748b; background: #fff; padding: 6px 14px; border-radius: 20px; border: 1px solid #cbd5e1;">
      Total: <strong><?= $totalRegistros ?></strong> eventos
    </span>
  </div>
</div>

<!-- Filtros de búsqueda -->
<div class="admin-card" style="padding: 16px 20px; margin-bottom: 20px;">
  <form method="GET" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 180px;">
      <label for="f-accion" style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; text-transform: uppercase;">
        Acción
      </label>
      <select id="f-accion" name="accion" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; background: #fff;">
        <option value="">Todas las acciones</option>
        <?php foreach ($accionesDisponibles as $acc): ?>
          <option value="<?= e($acc) ?>" <?= $filtroAccion === $acc ? 'selected' : '' ?>>
            <?= e(ucfirst($acc)) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="flex: 1; min-width: 180px;">
      <label for="f-entidad" style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; text-transform: uppercase;">
        Módulo / Entidad
      </label>
      <select id="f-entidad" name="entidad" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; background: #fff;">
        <option value="">Todos los módulos</option>
        <?php foreach ($entidadesDisponibles as $ent): ?>
          <option value="<?= e($ent) ?>" <?= $filtroEntidad === $ent ? 'selected' : '' ?>>
            <?= e(ucfirst($ent)) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="display: flex; gap: 8px;">
      <button type="submit" class="btn btn-primary btn-sm" style="padding: 9px 18px;">
        Filtrar
      </button>
      <?php if ($filtroAccion !== '' || $filtroEntidad !== ''): ?>
        <a href="bitacora.php" class="btn btn-outline btn-sm" style="padding: 9px 14px;">
          Limpiar
        </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Tabla de eventos -->
<div class="admin-card">
  <?php if (empty($registros)): ?>
    <div style="text-align: center; padding: 40px 20px; color: #64748b;">
      <p style="font-size: 15px; margin: 0;">No se encontraron registros con los filtros seleccionados.</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 160px;">Fecha y Hora</th>
            <th>Usuario</th>
            <th>Acción</th>
            <th>Módulo</th>
            <th>Detalle de la Operación</th>
            <th style="width: 130px;">IP Origen</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($registros as $b): ?>
            <?php
              $badgeColor = match($b['accion']) {
                'crear'    => 'background:#dcfce7; color:#15803d;',
                'editar'   => 'background:#e0f2fe; color:#0369a1;',
                'eliminar' => 'background:#fee2e2; color:#991b1b;',
                'login'    => 'background:#fef3c7; color:#b45309;',
                default    => 'background:#f1f5f9; color:#475569;'
              };
            ?>
            <tr>
              <td style="font-size: 13px; color: #475569;">
                <?= e(date('d/m/Y H:i:s', strtotime($b['created_at']))) ?>
              </td>
              <td>
                <?php if (!empty($b['usuario_nombre'])): ?>
                  <strong style="color: #0b3d76; font-size: 13.5px;"><?= e($b['usuario_nombre']) ?></strong>
                  <div style="font-size: 11.5px; color: #64748b;"><?= e($b['usuario_email']) ?></div>
                <?php else: ?>
                  <span style="color: #94a3b8; font-style: italic;">Sistema / Anónimo</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge" style="<?= $badgeColor ?>">
                  <?= e(ucfirst($b['accion'])) ?>
                </span>
              </td>
              <td>
                <span style="font-size: 13px; font-weight: 600; color: #334155;">
                  <?= e(ucfirst($b['entidad'])) ?>
                </span>
                <?php if (!empty($b['entidad_id'])): ?>
                  <span style="font-size: 11px; color: #94a3b8;">#<?= (int)$b['entidad_id'] ?></span>
                <?php endif; ?>
              </td>
              <td style="font-size: 13px; color: #1e293b;">
                <?= e($b['detalle'] ?? '—') ?>
              </td>
              <td style="font-size: 12px; font-family: monospace; color: #64748b;">
                <?= e($b['ip'] ?? '—') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <?php if ($totalPaginas > 1): ?>
      <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 14px; border-top: 1px solid #e2e8f0;">
        <span style="font-size: 13px; color: #64748b;">
          Página <strong><?= $pagina ?></strong> de <strong><?= $totalPaginas ?></strong>
        </span>
        <div style="display: flex; gap: 8px;">
          <?php if ($pagina > 1): ?>
            <a href="?p=<?= $pagina - 1 ?>&accion=<?= urlencode($filtroAccion) ?>&entidad=<?= urlencode($filtroEntidad) ?>"
               class="btn btn-outline btn-sm">
              ← Anterior
            </a>
          <?php endif; ?>
          <?php if ($pagina < $totalPaginas): ?>
            <a href="?p=<?= $pagina + 1 ?>&accion=<?= urlencode($filtroAccion) ?>&entidad=<?= urlencode($filtroEntidad) ?>"
               class="btn btn-outline btn-sm">
              Siguiente →
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
