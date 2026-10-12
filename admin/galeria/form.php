<?php
/**
 * JUSHSAL - Formulario de Fotografía para Galería (admin/galeria/form.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/galeria.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$foto = null;

if ($id > 0) {
    $foto = galeria_obtener_por_id($id);
    if (!$foto) {
        flash('error', 'La fotografía solicitada no existe.');
        header('Location: listar.php');
        exit;
    }
}

$esEdicion = !empty($foto);
$tituloPanel = $esEdicion ? 'Editar Fotografía' : 'Nueva Fotografía';

require_once dirname(__DIR__, 2) . '/admin/header.php';

$valTitulo       = $foto['titulo'] ?? '';
$valDescripcion  = $foto['descripcion'] ?? '';
$valOrden        = $foto['orden'] ?? 0;
$valEsDestacada  = isset($foto['es_destacada']) ? (int)$foto['es_destacada'] === 1 : false;
$valActivo       = isset($foto['activo']) ? (int)$foto['activo'] === 1 : true;
$valImagenActual = $foto['imagen'] ?? '';
?>

<div class="admin-title-row">
  <div>
    <h1><?= e($tituloPanel) ?></h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Suba y configure fotografías para el mosaico institucional de la portada web.
    </p>
  </div>
  <div>
    <a href="listar.php" class="btn btn-outline" style="font-size: 14px; padding: 10px 18px;">
      ← Volver a la Galería
    </a>
  </div>
</div>

<div class="admin-card" style="max-width: 820px;">
  <form action="guardar.php" method="POST" enctype="multipart/form-data">
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= $id ?>"/>

    <div style="margin-bottom: 20px;">
      <label for="titulo" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
        Título o Pie de Foto *
      </label>
      <input type="text" id="titulo" name="titulo" required maxlength="180"
             value="<?= e($valTitulo) ?>"
             placeholder="Ej. Canal Principal Quiroz - Sector Tambogrande"
             style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
      <span style="font-size: 12px; color: #64748b;">Texto descriptivo visible para el público al pasar el cursor o hacer clic.</span>
    </div>

    <div style="margin-bottom: 20px;">
      <label for="descripcion" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
        Descripción Detallada (Opcional)
      </label>
      <textarea id="descripcion" name="descripcion" rows="3"
                placeholder="Breve reseña sobre la infraestructura, obra o actividad mostrada..."
                style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; font-family: inherit;"><?= e($valDescripcion) ?></textarea>
    </div>

    <!-- Archivo de Imagen -->
    <div style="margin-bottom: 24px; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
      <label for="imagen" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
        Archivo de Imagen <?= $esEdicion ? '(Opcional para reemplazar)' : '*' ?>
      </label>

      <?php if ($esEdicion && !empty($valImagenActual)): ?>
        <?php
          $urlFotoActual = $baseUrl . rawurlencode(ltrim($valImagenActual, '/'));
          $urlFotoActual = str_replace('%2F', '/', $urlFotoActual);
        ?>
        <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 12px; background: #fff; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;">
          <img src="<?= e($urlFotoActual) ?>" alt="Actual"
               style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #cbd5e1;"/>
          <div>
            <span style="font-size: 13px; font-weight: 600; color: #0b3d76; display: block;">Imagen actual asignada</span>
            <span style="font-size: 11.5px; color: #64748b;"><?= e($valImagenActual) ?></span>
          </div>
        </div>
      <?php endif; ?>

      <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp"
             <?= $esEdicion ? '' : 'required' ?>
             style="display: block; width: 100%; font-size: 13.5px;"/>
      <span style="font-size: 12px; color: #64748b; display: block; margin-top: 6px;">
        Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo recomendado: 5 MB.
      </span>
    </div>

    <!-- Opciones de Presentación -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
      <div>
        <label for="orden" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Orden de Visualización
        </label>
        <input type="number" id="orden" name="orden" value="<?= (int)$valOrden ?>" min="0" max="999"
               style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
        <span style="font-size: 11.5px; color: #64748b;">Número menor se muestra primero.</span>
      </div>

      <div style="display: flex; flex-direction: column; justify-content: center;">
        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; cursor: pointer;">
          <input type="checkbox" name="es_destacada" value="1" <?= $valEsDestacada ? 'checked' : '' ?> style="width: 18px; height: 18px;"/>
          Destacada / Formato Ancho
        </label>
        <span style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
          Ocupará 2 columnas en la cuadrícula de portada (clase <code>.gallery-wide</code>).
        </span>
      </div>

      <div style="display: flex; flex-direction: column; justify-content: center;">
        <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; cursor: pointer;">
          <input type="checkbox" name="activo" value="1" <?= $valActivo ? 'checked' : '' ?> style="width: 18px; height: 18px;"/>
          Activa (Visible en la web)
        </label>
        <span style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
          Desmarcar para ocultar la foto temporalmente.
        </span>
      </div>
    </div>

    <!-- Botones de Acción -->
    <div style="display: flex; gap: 12px; align-items: center; padding-top: 10px; border-top: 1px solid #e2e8f0;">
      <button type="submit" class="btn btn-primary" style="padding: 11px 24px; font-size: 14.5px;">
        <?= $esEdicion ? 'Actualizar Fotografía' : 'Guardar Fotografía' ?>
      </button>
      <a href="listar.php" class="btn btn-outline" style="padding: 11px 20px; font-size: 14.5px;">
        Cancelar
      </a>
    </div>
  </form>
</div>

<?php require_once dirname(__DIR__, 2) . '/admin/footer.php'; ?>
