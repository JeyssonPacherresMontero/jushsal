<?php
/**
 * JUSHSAL - Formulario de Documento / Estado Financiero (admin/documentos/form.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/documentos.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$documento = null;

if ($id > 0) {
    $documento = documentos_obtener_por_id($id);
    if (!$documento) {
        flash('error', 'El documento solicitado no existe.');
        header('Location: listar.php');
        exit;
    }
}

$esEdicion = !empty($documento);
$tituloPanel = $esEdicion ? 'Editar Documento' : 'Nuevo Documento';

require_once dirname(__DIR__, 2) . '/admin/header.php';

$categorias = categorias_documento_listar();

$valTitulo       = $documento['titulo'] ?? '';
$valCategoriaId  = $documento['categoria_id'] ?? 1;
$valDescripcion  = $documento['descripcion'] ?? '';
$valAnio         = $documento['anio'] ?? (int)date('Y');
$valOrden        = $documento['orden'] ?? 0;
$valEsDestacado  = isset($documento['es_destacado']) ? (int)$documento['es_destacado'] === 1 : false;
$valActivo       = isset($documento['activo']) ? (int)$documento['activo'] === 1 : true;
?>

<div class="admin-title-row">
  <div>
    <h1><?= e($tituloPanel) ?></h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Carga y clasificación de documentos institucionales, leyes, tarifas y balances financieros en PDF.
    </p>
  </div>
  <div>
    <a href="listar.php" class="btn btn-outline" style="font-size: 14px; padding: 10px 18px;">
      ← Volver al listado
    </a>
  </div>
</div>

<div class="admin-card" style="max-width: 860px;">
  <form action="guardar.php" method="POST" enctype="multipart/form-data">
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= $id ?>"/>

    <div style="margin-bottom: 20px;">
      <label for="titulo" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
        Título Oficial del Documento *
      </label>
      <input type="text" id="titulo" name="titulo" required maxlength="220" value="<?= e($valTitulo) ?>"
             placeholder="Ej. Aprobación de Valores de Tarifa de Agua 2026 / RJ-239-2026-ANA Auditoría Financiera"
             style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px;"/>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
      <div>
        <label for="categoria_id" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Categoría Institucional *
        </label>
        <select id="categoria_id" name="categoria_id" required
                style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #fff;">
          <?php foreach ($categorias as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>" <?= (int)$valCategoriaId === (int)$cat['id'] ? 'selected' : '' ?>>
              <?= e($cat['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="anio" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Año Fiscal / Emisión
        </label>
        <input type="number" id="anio" name="anio" min="1990" max="2050" value="<?= (int)$valAnio ?>"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
      </div>
    </div>

    <div style="margin-bottom: 20px;">
      <label for="descripcion" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
        Descripción o Sumilla del Contenido
      </label>
      <textarea id="descripcion" name="descripcion" rows="3" maxlength="350"
                placeholder="Breve resumen o base legal que describe el propósito de este documento para los regantes..."
                style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"><?= e($valDescripcion) ?></textarea>
      <small style="color: #64748b; display: block; margin-top: 4px;">Máximo 350 caracteres.</small>
    </div>

    <!-- Carga de Archivo PDF -->
    <div style="margin-bottom: 24px; padding: 18px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
      <label for="archivo" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
        Archivo Documental (Formato PDF) <?= $esEdicion ? '(Opcional si conserva el actual)' : '*' ?>
      </label>
      <input type="file" id="archivo" name="archivo" accept="application/pdf,.pdf" <?= $esEdicion ? '' : 'required' ?>
             style="display: block; margin-bottom: 8px; font-size: 14px;"/>
      <small style="color: #64748b; display: block;">
        Formato permitido: Únicamente <strong>.PDF</strong>. Tamaño máximo: 15 MB.
      </small>

      <?php if ($esEdicion && !empty($documento['archivo_ruta'])): ?>
        <div style="margin-top: 14px; padding: 10px 14px; background: #fff; border-radius: 6px; border: 1px solid #cbd5e1; display: flex; align-items: center; justify-content: space-between;">
          <div style="font-size: 13.5px; color: #1e293b;">
            📄 <strong>Archivo actual:</strong> <?= e($documento['nombre_original'] ?? basename($documento['archivo_ruta'])) ?>
          </div>
          <a href="<?= $baseUrl . str_replace('%2F', '/', rawurlencode(ltrim($documento['archivo_ruta'], '/'))) ?>"
             target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-sm">
            Descargar / Ver ↗
          </a>
        </div>
      <?php endif; ?>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
      <div>
        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
          <input type="checkbox" name="es_destacado" value="1" <?= $valEsDestacado ? 'checked' : '' ?> style="width: 18px; height: 18px;"/>
          <span style="font-weight: 600; color: #1e293b; font-size: 14.5px;">Destacado en Portada</span>
        </label>
        <small style="color: #64748b; display: block; margin-left: 28px; margin-top: 4px;">
          Se mostrará entre los 4 recuadros principales de documentos institucionales en index.php.
        </small>
      </div>

      <div>
        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
          <input type="checkbox" name="activo" value="1" <?= $valActivo ? 'checked' : '' ?> style="width: 18px; height: 18px;"/>
          <span style="font-weight: 600; color: #1e293b; font-size: 14.5px;">Documento Activo</span>
        </label>
        <small style="color: #64748b; display: block; margin-left: 28px; margin-top: 4px;">
          Visible y disponible para descarga en el portal público.
        </small>
      </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #e2e8f0;">
      <a href="listar.php" class="btn btn-outline" style="padding: 10px 20px;">Cancelar</a>
      <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
        <?= $esEdicion ? 'Actualizar Documento' : 'Publicar Documento' ?>
      </button>
    </div>
  </form>
</div>

<?php require_once dirname(__DIR__, 2) . '/admin/footer.php'; ?>
