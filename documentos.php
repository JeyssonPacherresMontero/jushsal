<?php
/**
 * JUSHSAL - Portal Público: Documentos Institucionales y Estados Financieros (documentos.php)
 * Repositorio de normatividad, resoluciones ANA, tarifas y balances auditados.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/modelos/documentos.php';

$catId = isset($_GET['cat']) ? (int)$_GET['cat'] : null;
$anio = !empty($_GET['anio']) ? (int)$_GET['anio'] : null;
$busqueda = !empty($_GET['q']) ? trim($_GET['q']) : null;

$documentos = documentos_listar_publicos($catId, $anio, $busqueda);
$categorias = categorias_documento_listar();
$aniosDisponibles = documentos_anios_disponibles();

$tituloPagina = 'Documentos Institucionales y Resoluciones | Junta de Usuarios San Lorenzo';
$descripcionPagina = 'Accede a la normativa, tarifas de agua, resoluciones de la ANA y estados financieros auditados de JUSHSAL.';
$baseUrl = BASE_URL;

include __DIR__ . '/includes/vistas/header.php';
?>

<section class="section section-alt" style="padding-top:40px;">
  <div class="container">

    <div class="section-head center">
      <p class="eyebrow">Transparencia y Normatividad</p>
      <h1 class="section-title">Documentos Institucionales</h1>
      <p class="section-desc">
        Consulta y descarga libre de leyes, resoluciones de la Autoridad Nacional del Agua (ANA), reglamentos, tarifas de agua aprobadas y estados financieros auditados.
      </p>
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <div style="background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:20px 24px; margin-bottom:36px; box-shadow:0 2px 6px rgba(0,0,0,0.02);">
      <form method="GET" action="documentos.php" style="display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap;">
        
        <div style="flex:2; min-width:220px;">
          <label for="f-q" style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">
            Buscar por palabra clave
          </label>
          <input type="text" id="f-q" name="q" value="<?= e($busqueda ?? '') ?>"
                 placeholder="Ej. Tarifa, Auditoría, Ley 31801, Operadores..."
                 style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;"/>
        </div>

        <div style="flex:2; min-width:200px;">
          <label for="f-cat" style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">
            Categoría
          </label>
          <select id="f-cat" name="cat" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; background:#fff;">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= $catId === (int)$c['id'] ? 'selected' : '' ?>>
                <?= e($c['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="flex:1; min-width:130px;">
          <label for="f-anio" style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px;">
            Año
          </label>
          <select id="f-anio" name="anio" style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; background:#fff;">
            <option value="">Todos</option>
            <?php foreach ($aniosDisponibles as $a): ?>
              <option value="<?= (int)$a ?>" <?= $anio === (int)$a ? 'selected' : '' ?>>
                <?= (int)$a ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="display:flex; gap:8px;">
          <button type="submit" class="btn btn-primary" style="padding:10px 20px; font-size:14px;">
            Buscar
          </button>
          <?php if (!empty($catId) || !empty($anio) || !empty($busqueda)): ?>
            <a href="documentos.php" class="btn btn-outline" style="padding:10px 16px; font-size:14px;">
              Limpiar
            </a>
          <?php endif; ?>
        </div>

      </form>
    </div>

    <!-- Cuadrícula de Documentos -->
    <?php if (empty($documentos)): ?>
      <div style="text-align:center; padding:60px 20px; background:#fff; border-radius:12px; border:1px solid #e2e8f0; color:#64748b;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor" style="opacity:.3; margin-bottom:12px;"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
        <p style="font-size:16px; margin:0 0 12px; font-weight:600;">No se encontraron documentos con los criterios de búsqueda.</p>
        <a href="documentos.php" class="btn btn-outline btn-sm">Ver todos los documentos</a>
      </div>
    <?php else: ?>
      <div class="docs-grid">
        <?php foreach ($documentos as $d): ?>
          <?php
            $urlDoc = $baseUrl . str_replace('%2F', '/', rawurlencode(ltrim($d['archivo_ruta'], '/')));
          ?>
          <div class="doc-card" style="display:flex; flex-direction:column; justify-content:space-between;">
            <div>
              <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                <span class="doc-icon">
                  <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 2c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6H6zm7 7V3.5L18.5 9H13z"/></svg>
                </span>
                <?php if (!empty($d['anio'])): ?>
                  <span style="font-size:12px; background:#f1f5f9; color:#475569; padding:3px 8px; border-radius:12px; font-weight:600;">
                    <?= (int)$d['anio'] ?>
                  </span>
                <?php endif; ?>
              </div>

              <div style="margin-bottom:6px;">
                <span style="font-size:11.5px; color:#0369a1; font-weight:700; text-transform:uppercase; letter-spacing:.5px;">
                  <?= e($d['categoria_nombre']) ?>
                </span>
              </div>

              <h3 title="<?= e($d['titulo']) ?>" style="font-size:16.5px; line-height:1.35; margin-bottom:8px; color:var(--primary-darker);">
                <?= e($d['titulo']) ?>
              </h3>

              <?php if (!empty($d['descripcion'])): ?>
                <p style="font-size:13.5px; color:var(--muted); line-height:1.45; margin-bottom:14px;">
                  <?= e($d['descripcion']) ?>
                </p>
              <?php endif; ?>
            </div>

            <div style="margin-top:16px; padding-top:12px; border-top:1px solid #f1f5f9;">
              <a class="link-more" href="<?= e($urlDoc) ?>" target="_blank" rel="noopener noreferrer" style="font-weight:600; font-size:14px;">
                Consultar Documento PDF <span>→</span>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/includes/vistas/footer.php'; ?>
