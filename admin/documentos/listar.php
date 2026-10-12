<?php
/**
 * JUSHSAL - Listado Administrativo de Documentos y Estados Financieros (admin/documentos/listar.php)
 */

define('JUSHSAL_INIT', true);

$tituloPanel = 'Documentos y Balances';
require_once dirname(__DIR__, 2) . '/admin/header.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/documentos.php';

// Filtros
$catId = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$busqueda = trim($_GET['q'] ?? '');

$filtros = [];
if ($catId > 0) $filtros['categoria_id'] = $catId;
if ($busqueda !== '') $filtros['busqueda'] = $busqueda;

$documentos = documentos_listar_admin($filtros);
$categorias = categorias_documento_listar();
?>

<div class="admin-title-row">
  <div>
    <h1>Documentos Institucionales y Estados Financieros</h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Publicación de normatividad, resoluciones de la ANA, tarifas de agua y balances financieros auditados.
    </p>
  </div>
  <div>
    <a href="form.php" class="btn btn-primary" style="font-size: 14px; padding: 10px 18px;">
      + Nuevo Documento
    </a>
  </div>
</div>

<!-- Filtros y búsqueda -->
<div class="admin-card" style="padding: 16px 20px; margin-bottom: 20px;">
  <form method="GET" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 220px;">
      <label for="f-cat" style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; text-transform: uppercase;">
        Filtrar por Categoría
      </label>
      <select id="f-cat" name="cat" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; background: #fff;">
        <option value="0">Todas las categorías</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $catId === (int)$c['id'] ? 'selected' : '' ?>>
            <?= e($c['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div style="flex: 2; min-width: 240px;">
      <label for="f-q" style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; text-transform: uppercase;">
        Buscar por Título o Descripción
      </label>
      <input type="text" id="f-q" name="q" value="<?= e($busqueda) ?>" placeholder="Ej. Tarifa 2026, Auditoría, Ley 31801..."
             style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px;"/>
    </div>

    <div style="display: flex; gap: 8px;">
      <button type="submit" class="btn btn-primary btn-sm" style="padding: 9px 18px;">Filtrar</button>
      <?php if ($catId > 0 || $busqueda !== ''): ?>
        <a href="listar.php" class="btn btn-outline btn-sm" style="padding: 9px 14px;">Limpiar</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Tabla de Documentos -->
<div class="admin-card">
  <?php if (empty($documentos)): ?>
    <div style="text-align: center; padding: 40px 20px; color: #64748b;">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.3; margin-bottom: 12px;"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
      <p style="font-size: 15px; margin: 0 0 16px;">No se encontraron documentos registrados.</p>
      <a href="form.php" class="btn btn-primary btn-sm">Subir el primer documento</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 50px;">Tipo</th>
            <th>Título del Documento</th>
            <th>Categoría</th>
            <th>Año</th>
            <th>Portada</th>
            <th>Estado</th>
            <th>Descargas</th>
            <th style="text-align: right; width: 220px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($documentos as $d): ?>
            <?php
              $urlArchivo = $baseUrl . rawurlencode(ltrim($d['archivo_ruta'], '/'));
              $urlArchivo = str_replace('%2F', '/', $urlArchivo);
              $esDestacado = (int)$d['es_destacado'] === 1;
              $activo = (int)$d['activo'] === 1;
              $tamKb = !empty($d['tamanio_bytes']) ? round($d['tamanio_bytes'] / 1024, 1) : null;
            ?>
            <tr>
              <td>
                <span style="display: inline-block; padding: 4px 6px; border-radius: 4px; background: #fee2e2; color: #991b1b; font-weight: 700; font-size: 11px;">
                  PDF
                </span>
              </td>
              <td>
                <strong style="color: #0b3d76; font-size: 14.5px; display: block; margin-bottom: 3px;"><?= e($d['titulo']) ?></strong>
                <?php if (!empty($d['descripcion'])): ?>
                  <div style="font-size: 12px; color: #64748b; line-height: 1.3; max-width: 480px;">
                    <?= e(mb_substr($d['descripcion'], 0, 110)) ?>...
                  </div>
                <?php endif; ?>
                <?php if ($tamKb): ?>
                  <span style="font-size: 11px; color: #94a3b8; margin-top: 2px; display: inline-block;">
                    Tamaño: <?= $tamKb > 1024 ? round($tamKb / 1024, 2) . ' MB' : $tamKb . ' KB' ?>
                  </span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11.5px;">
                  <?= e($d['categoria_nombre']) ?>
                </span>
              </td>
              <td style="font-size: 13.5px; font-weight: 600; color: #334155;">
                <?= !empty($d['anio']) ? (int)$d['anio'] : '—' ?>
              </td>
              <td>
                <?php if ($esDestacado): ?>
                  <span class="badge badge-success" title="Visible en la sección documentos de portada">★ Destacado</span>
                <?php else: ?>
                  <span style="color: #94a3b8; font-size: 13px;">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($activo): ?>
                  <span class="badge badge-success">Activo</span>
                <?php else: ?>
                  <span class="badge badge-secondary">Inactivo</span>
                <?php endif; ?>
              </td>
              <td style="font-size: 13px; color: #64748b; text-align: center;">
                <?= (int)$d['descargas'] ?>
              </td>
              <td style="text-align: right;">
                <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                  <a href="<?= e($urlArchivo) ?>" target="_blank" rel="noopener noreferrer"
                     class="btn btn-outline btn-sm" style="color: #0b3d76; border-color: #cbd5e1;" title="Abrir documento PDF">
                    Ver ↗
                  </a>
                  <a href="form.php?id=<?= (int)$d['id'] ?>" class="btn btn-outline btn-sm" style="color: #0b3d76; border-color: #cbd5e1;">
                    Editar
                  </a>
                  <form action="eliminar.php" method="POST" style="display: inline;" onsubmit="return confirm('¿Está seguro de eliminar este documento? Esta acción eliminará el archivo del servidor.');">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id" value="<?= (int)$d['id'] ?>"/>
                    <button type="submit" class="btn btn-sm" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                      Eliminar
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once dirname(__DIR__, 2) . '/admin/footer.php'; ?>
