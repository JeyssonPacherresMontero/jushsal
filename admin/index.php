<?php
/**
 * JUSHSAL - Tablero Principal del Panel Administrativo (admin/index.php)
 */

define('JUSHSAL_INIT', true);

$tituloPanel = 'Tablero Principal';
require_once __DIR__ . '/header.php';
require_once dirname(__DIR__) . '/includes/modelos/noticias.php';

$db = obtener_db();

// Métricas del sistema
$totalPublicadas = noticias_contar_publicadas();
$totalBorradores = (int)$db->query("SELECT COUNT(*) FROM noticias WHERE estado = 'borrador'")->fetchColumn();
$totalComisiones = (int)$db->query("SELECT COUNT(*) FROM comisiones")->fetchColumn();
$totalDocumentos = (int)$db->query("SELECT COUNT(*) FROM documentos WHERE activo = 1")->fetchColumn();
$totalContactosPendientes = contacto_contar_pendientes();
$totalUsuarios = (int)$db->query("SELECT COUNT(*) FROM usuarios WHERE activo = 1")->fetchColumn();
$totalBitacora = (int)$db->query("SELECT COUNT(*) FROM bitacora")->fetchColumn();

// Convocatoria activa
$stmtConv = $db->query("
    SELECT titulo, fecha_asamblea 
    FROM convocatorias 
    WHERE activo = 1 AND NOW() BETWEEN mostrar_desde AND mostrar_hasta 
    ORDER BY id DESC LIMIT 1
");
$convocatoriaActiva = $stmtConv->fetch();

// Gestión vigente
$stmtGestion = $db->query("
    SELECT presidente_nombre, periodo_inicio, periodo_fin 
    FROM gestiones_presidenciales 
    WHERE es_vigente = 1 LIMIT 1
");
$gestionVigente = $stmtGestion->fetch();

// Últimas 5 noticias en el panel
$ultimasNoticias = noticias_listar_admin([], 5);
?>

<div class="admin-title-row">
  <div>
    <h1>Bienvenido(a), <?= e($usuario['nombre']) ?></h1>
    <p style="margin:4px 0 0; color:#64748b; font-size:14px;">Panel de administración institucional de JUSHSAL.</p>
  </div>
  <div>
    <a href="<?= $adminUrl ?>noticias/form.php" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px;">
      <span>+ Nueva Noticia</span>
    </a>
  </div>
</div>

<!-- Tarjetas de métricas -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:28px;">
  <div class="admin-card" style="margin:0; border-top: 4px solid #0b3d76;">
    <span style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Noticias Publicadas</span>
    <div style="font-size:32px; font-weight:700; color:#0b3d76; margin:8px 0;"><?= $totalPublicadas ?></div>
    <span style="font-size:13px; color:#64748b;"><?= $totalBorradores ?> en borrador</span>
  </div>

  <div class="admin-card" style="margin:0; border-top: 4px solid #14919b;">
    <span style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Popup Convocatoria</span>
    <div style="font-size:18px; font-weight:700; color:#0b3d76; margin:12px 0 8px;">
      <?= $convocatoriaActiva ? 'Activa' : 'Sin convocatoria vigente' ?>
    </div>
    <span style="font-size:12px; color:#64748b;">
      <?= $convocatoriaActiva ? e(mb_substr($convocatoriaActiva['titulo'], 0, 30)) . '...' : 'Configurable desde panel' ?>
    </span>
  </div>

  <div class="admin-card" style="margin:0; border-top: 4px solid #ffab00;">
    <span style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Gestión Presidencial</span>
    <div style="font-size:17px; font-weight:700; color:#0b3d76; margin:12px 0 8px;">
      <?= $gestionVigente ? e($gestionVigente['presidente_nombre']) : 'No asignado' ?>
    </div>
    <span style="font-size:12px; color:#64748b;">
      Periodo <?= $gestionVigente ? e($gestionVigente['periodo_inicio']) . '–' . e($gestionVigente['periodo_fin'] ?? 'Actual') : '' ?>
    </span>
  </div>

  <div class="admin-card" style="margin:0; border-top: 4px solid #3b82f6;">
    <span style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Comisiones Registradas</span>
    <div style="font-size:32px; font-weight:700; color:#0b3d76; margin:8px 0;"><?= $totalComisiones ?></div>
    <span style="font-size:13px; color:#64748b;">Sub sectores de riego</span>
  </div>

  <div class="admin-card" style="margin:0; border-top: 4px solid <?= $totalContactosPendientes > 0 ? '#ef4444' : '#10b981' ?>;">
    <span style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Consultas de Contacto</span>
    <div style="font-size:32px; font-weight:700; color:<?= $totalContactosPendientes > 0 ? '#dc2626' : '#0b3d76' ?>; margin:8px 0;">
      <?= $totalContactosPendientes ?>
    </div>
    <a href="<?= $adminUrl ?>contactos/listar.php?estado=pendiente" style="font-size:13px; color:#0b3d76; text-decoration:none; font-weight:600;">
      <?= $totalContactosPendientes > 0 ? 'Pendientes de responder →' : 'Sin pendientes · Ver todos →' ?>
    </a>
  </div>

  <div class="admin-card" style="margin:0; border-top: 4px solid #8b5cf6;">
    <span style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Documentos y Balances</span>
    <div style="font-size:32px; font-weight:700; color:#0b3d76; margin:8px 0;"><?= $totalDocumentos ?></div>
    <a href="<?= $adminUrl ?>documentos/listar.php" style="font-size:13px; color:#0b3d76; text-decoration:none; font-weight:600;">
      Leyes, tarifas y balances →
    </a>
  </div>
</div>

<!-- Listado rápido de noticias -->
<div class="admin-card">
  <div class="admin-title-row" style="margin-bottom:16px;">
    <h2 style="font-size:18px; margin:0; color:#0b3d76;">Noticias Recientes</h2>
    <a href="<?= $adminUrl ?>noticias/listar.php" style="font-size:13px; color:#0b3d76; font-weight:600; text-decoration:none;">Ver todas las noticias →</a>
  </div>

  <?php if (empty($ultimasNoticias)): ?>
    <p style="color:#64748b; text-align:center; padding:30px 0; margin:0;">Aún no hay noticias registradas. Utiliza el botón <strong>+ Nueva Noticia</strong> para crear la primera.</p>
  <?php else: ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Título</th>
            <th>Categoría</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ultimasNoticias as $n): ?>
            <tr>
              <td>
                <strong><?= e($n['titulo']) ?></strong>
                <div style="font-size:12px; color:#64748b;"><?= e(mb_substr($n['resumen'], 0, 80)) ?>...</div>
              </td>
              <td><?= e($n['categoria_nombre'] ?? 'Sin categoría') ?></td>
              <td><?= e(formato_fecha($n['fecha_publicacion'])) ?></td>
              <td>
                <?php if ($n['estado'] === 'publicado'): ?>
                  <span class="badge badge-success">Publicado</span>
                <?php elseif ($n['estado'] === 'borrador'): ?>
                  <span class="badge badge-warning">Borrador</span>
                <?php else: ?>
                  <span class="badge badge-secondary">Archivado</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?= $adminUrl ?>noticias/form.php?id=<?= $n['id'] ?>" class="btn btn-outline btn-sm">Editar</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php if (($usuario['rol_nombre'] ?? '') === 'admin'): ?>
  <?php
    $ultimosEventos = $db->query("
      SELECT b.*, u.nombre AS usuario_nombre, u.email AS usuario_email
      FROM bitacora b
      LEFT JOIN usuarios u ON b.usuario_id = u.id
      ORDER BY b.id DESC LIMIT 5
    ")->fetchAll();
  ?>
  <div class="admin-card" style="margin-top: 24px;">
    <div class="admin-title-row" style="margin-bottom: 16px;">
      <div>
        <h2 style="font-size: 18px; margin: 0; color: #0b3d76;">Últimos Eventos de Auditoría</h2>
        <span style="font-size: 12px; color: #64748b;">Acciones recientes registradas en el sistema (Visible solo para administradores)</span>
      </div>
      <a href="<?= $adminUrl ?>bitacora.php" style="font-size: 13px; color: #0b3d76; font-weight: 600; text-decoration: none;">Ver bitácora completa →</a>
    </div>

    <?php if (empty($ultimosEventos)): ?>
      <p style="color: #64748b; text-align: center; padding: 20px 0; margin: 0;">Sin eventos registrados aún.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Fecha y Hora</th>
              <th>Usuario</th>
              <th>Acción</th>
              <th>Módulo</th>
              <th>Detalle</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ultimosEventos as $ev): ?>
              <tr>
                <td style="font-size: 12.5px; color: #64748b;"><?= e(date('d/m/Y H:i', strtotime($ev['created_at']))) ?></td>
                <td>
                  <strong><?= e($ev['usuario_nombre'] ?? 'Sistema') ?></strong>
                </td>
                <td>
                  <span class="badge" style="background:#f1f5f9; color:#334155;">
                    <?= e(ucfirst($ev['accion'])) ?>
                  </span>
                </td>
                <td><?= e(ucfirst($ev['entidad'])) ?></td>
                <td style="font-size: 13px;"><?= e($ev['detalle'] ?? '—') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/footer.php';
?>
