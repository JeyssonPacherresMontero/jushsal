<?php
/**
 * JUSHSAL - Gestión de Noticias: Formulario de Creación / Edición (admin/noticias/form.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/helpers.php';
require_once dirname(dirname(__DIR__)) . '/includes/modelos/noticias.php';

// Verificar permisos
requerir_rol(['admin', 'comunicaciones']);

$id = !empty($_GET['id']) ? (int)$_GET['id'] : null;
$noticia = null;

if ($id) {
    $noticia = noticias_obtener_por_id($id);
    if (!$noticia) {
        flash('error', 'La noticia solicitada no existe.');
        redirigir(BASE_URL . 'admin/noticias/listar.php');
    }
    $tituloPanel = 'Editar Noticia: ' . mb_substr($noticia['titulo'], 0, 40) . '...';
} else {
    $tituloPanel = 'Nueva Noticia Institucional';
}

require_once dirname(__DIR__) . '/header.php';
$categorias = categorias_noticia_listar();
?>

<div class="admin-title-row">
  <div>
    <h1><?= $id ? 'Editar Noticia' : 'Nueva Noticia' ?></h1>
    <p style="margin:4px 0 0; color:#64748b; font-size:14px;">
      <?= $id ? 'Actualiza los datos y estado de la publicación.' : 'Completa los campos para crear un borrador o publicar una noticia.' ?>
    </p>
  </div>
  <div>
    <a href="listar.php" class="btn btn-outline">← Volver al listado</a>
  </div>
</div>

<div class="admin-card" style="max-width:900px; margin:0 auto;">
  <form method="POST" action="guardar.php" enctype="multipart/form-data">
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= $id ? (int)$id : '' ?>"/>

    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
      <!-- Columna principal -->
      <div>
        <div style="margin-bottom:20px;">
          <label for="titulo" style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">
            Título de la noticia <span style="color:#ef4444;">*</span>
          </label>
          <input type="text" id="titulo" name="titulo" required maxlength="160"
                 value="<?= e($noticia['titulo'] ?? '') ?>"
                 placeholder="Ej.: Inscripción de nuevos usuarios para el presente año agrícola"
                 style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:15px; box-sizing:border-box;"/>
          <small style="color:#64748b; font-size:12px;">Máximo 160 caracteres.</small>
        </div>

        <div style="margin-bottom:20px;">
          <label for="resumen" style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">
            Resumen o bajada <span style="color:#ef4444;">*</span>
          </label>
          <textarea id="resumen" name="resumen" required maxlength="400" rows="3"
                    placeholder="Breve resumen que se mostrará en las tarjetas de noticias y portada..."
                    style="width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; box-sizing:border-box;"><?= e($noticia['resumen'] ?? '') ?></textarea>
          <small style="color:#64748b; font-size:12px;">Máximo 400 caracteres. Visible en el listado y tarjetas.</small>
        </div>

        <div style="margin-bottom:20px;">
          <label for="contenido" style="display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:6px;">
            Contenido completo
          </label>
          <textarea id="contenido" name="contenido" rows="12"
                    placeholder="Desarrollo completo de la noticia. Puedes escribir múltiples párrafos..."
                    style="width:100%; padding:12px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; line-height:1.6; box-sizing:border-box;"><?= e($noticia['contenido'] ?? '') ?></textarea>
          <small style="color:#64748b; font-size:12px;">Por seguridad, se renderiza como texto plano formateado con párrafos automáticos.</small>
        </div>
      </div>

      <!-- Barra lateral de opciones -->
      <div>
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:18px; margin-bottom:20px;">
          <h3 style="margin:0 0 16px 0; font-size:15px; color:#0b3d76; font-family:'Montserrat',sans-serif;">Publicación</h3>

          <div style="margin-bottom:16px;">
            <label for="estado" style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px;">
              Estado <span style="color:#ef4444;">*</span>
            </label>
            <select id="estado" name="estado" required style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; background:#fff;">
              <option value="borrador" <?= ($noticia['estado'] ?? '') === 'borrador' ? 'selected' : '' ?>>Borrador (No visible en web)</option>
              <option value="publicado" <?= ($noticia['estado'] ?? 'publicado') === 'publicado' ? 'selected' : '' ?>>Publicado (Visible en web)</option>
              <option value="archivado" <?= ($noticia['estado'] ?? '') === 'archivado' ? 'selected' : '' ?>>Archivado</option>
            </select>
          </div>

          <div style="margin-bottom:16px;">
            <label for="categoria_id" style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px;">
              Categoría
            </label>
            <select id="categoria_id" name="categoria_id" style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; background:#fff;">
              <option value="">-- Sin categoría --</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ((int)($noticia['categoria_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                  <?= e($cat['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="margin-bottom:16px;">
            <label for="fecha_publicacion" style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px;">
              Fecha de publicación <span style="color:#ef4444;">*</span>
            </label>
            <input type="date" id="fecha_publicacion" name="fecha_publicacion" required
                   value="<?= e($noticia['fecha_publicacion'] ?? date('Y-m-d')) ?>"
                   style="width:100%; padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; background:#fff; box-sizing:border-box;"/>
          </div>

          <div style="margin-bottom:8px;">
            <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#334155; cursor:pointer;">
              <input type="checkbox" name="destacada" value="1" <?= !empty($noticia['destacada']) ? 'checked' : '' ?>/>
              <span>Destacar en portada</span>
            </label>
          </div>
        </div>

        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:18px;">
          <h3 style="margin:0 0 14px 0; font-size:15px; color:#0b3d76; font-family:'Montserrat',sans-serif;">Imagen de portada</h3>

          <?php if (!empty($noticia['imagen_portada'])): ?>
            <div style="margin-bottom:14px;">
              <img src="<?= $baseUrl . e($noticia['imagen_portada']) ?>" alt="Portada actual" style="width:100%; height:130px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1;"/>
              <label style="display:flex; align-items:center; gap:6px; font-size:12px; color:#ef4444; margin-top:6px; cursor:pointer;">
                <input type="checkbox" name="eliminar_imagen" value="1"/>
                <span>Quitar imagen actual</span>
              </label>
            </div>
          <?php endif; ?>

          <div>
            <label for="imagen" style="display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px;">
              <?= !empty($noticia['imagen_portada']) ? 'Reemplazar imagen:' : 'Subir imagen:' ?>
            </label>
            <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp"
                   style="font-size:12px; width:100%;"/>
            <small style="color:#64748b; font-size:11px; display:block; margin-top:6px;">
              Formatos: JPG, PNG, WEBP. Máx: 5 MB. Si no se sube imagen, se generará un monograma institucional.
            </small>
          </div>
        </div>
      </div>
    </div>

    <div style="margin-top:28px; padding-top:20px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:12px;">
      <a href="listar.php" class="btn btn-outline">Cancelar</a>
      <button type="submit" class="btn btn-primary" style="padding:10px 24px;">
        <?= $id ? 'Guardar Cambios' : 'Publicar Noticia' ?>
      </button>
    </div>
  </form>
</div>

<?php
require_once dirname(__DIR__) . '/footer.php';
?>
