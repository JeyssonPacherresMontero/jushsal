<?php
/**
 * JUSHSAL - Formulario de Convocatoria (admin/convocatorias/form.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/convocatorias.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$convocatoria = null;

if ($id > 0) {
    $convocatoria = convocatorias_obtener_por_id($id);
    if (!$convocatoria) {
        flash('error', 'La convocatoria solicitada no existe.');
        header('Location: listar.php');
        exit;
    }
}

$esEdicion = !empty($convocatoria);
$tituloPanel = $esEdicion ? 'Editar Convocatoria' : 'Nueva Convocatoria';

require_once dirname(__DIR__, 2) . '/admin/header.php';

// Valores por defecto para nueva convocatoria
$ahora = new DateTime('now', new DateTimeZone('America/Lima'));
$enUnaSemana = (clone $ahora)->modify('+7 days');

$valTitulo = $convocatoria['titulo'] ?? '';
$valFechaAsamblea = $convocatoria['fecha_asamblea'] ?? '';
$valMostrarDesde = !empty($convocatoria['mostrar_desde']) 
    ? date('Y-m-d\TH:i', strtotime($convocatoria['mostrar_desde'])) 
    : $ahora->format('Y-m-d\TH:i');
$valMostrarHasta = !empty($convocatoria['mostrar_hasta']) 
    ? date('Y-m-d\TH:i', strtotime($convocatoria['mostrar_hasta'])) 
    : $enUnaSemana->format('Y-m-d\TH:i');
$valRetraso = isset($convocatoria['retraso_seg']) ? (int)$convocatoria['retraso_seg'] : 3;
$valDuracion = isset($convocatoria['duracion_seg']) ? (int)$convocatoria['duracion_seg'] : 0;
$valFrecuencia = $convocatoria['frecuencia'] ?? 'una_vez_por_sesion';
$valActivo = isset($convocatoria['activo']) ? (int)$convocatoria['activo'] === 1 : true;
?>

<div class="admin-title-row">
  <div>
    <h1><?= e($tituloPanel) ?></h1>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 14px;">
      Configuración del afiche y reglas de despliegue para la ventana emergente en el portal público.
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
        Título de la Convocatoria *
      </label>
      <input type="text" id="titulo" name="titulo" required maxlength="255" value="<?= e($valTitulo) ?>"
             placeholder="Ej. Convocatoria a Asamblea General Ordinaria 2026"
             style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px;"/>
      <small style="color: #64748b; display: block; margin-top: 4px;">
        Este título se muestra en la cabecera del popup emergente.
      </small>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
      <div>
        <label for="fecha_asamblea" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Fecha del Evento / Asamblea (Opcional)
        </label>
        <input type="date" id="fecha_asamblea" name="fecha_asamblea" value="<?= e($valFechaAsamblea) ?>"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
      </div>
      <div>
        <label for="frecuencia" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Frecuencia de Aparición *
        </label>
        <select id="frecuencia" name="frecuencia" required
                style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; background: #fff;">
          <option value="una_vez_por_sesion" <?= $valFrecuencia === 'una_vez_por_sesion' ? 'selected' : '' ?>>
            1 vez por sesión de navegador (Recomendado)
          </option>
          <option value="una_vez_por_dia" <?= $valFrecuencia === 'una_vez_por_dia' ? 'selected' : '' ?>>
            1 vez por día (Guarda en localStorage)
          </option>
          <option value="cada_visita" <?= $valFrecuencia === 'cada_visita' ? 'selected' : '' ?>>
            En cada recarga / visita (Para avisos urgentes)
          </option>
        </select>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
      <div>
        <label for="mostrar_desde" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Mostrar Desde (Fecha y Hora) *
        </label>
        <input type="datetime-local" id="mostrar_desde" name="mostrar_desde" required value="<?= e($valMostrarDesde) ?>"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
        <small style="color: #64748b; display: block; margin-top: 4px;">Hora oficial de Lima (UTC-5)</small>
      </div>
      <div>
        <label for="mostrar_hasta" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Mostrar Hasta (Fecha y Hora) *
        </label>
        <input type="datetime-local" id="mostrar_hasta" name="mostrar_hasta" required value="<?= e($valMostrarHasta) ?>"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
        <small style="color: #64748b; display: block; margin-top: 4px;">Debe ser posterior a la fecha de inicio</small>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
      <div>
        <label for="retraso_seg" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Retraso antes de aparecer (segundos)
        </label>
        <input type="number" id="retraso_seg" name="retraso_seg" min="0" max="60" value="<?= $valRetraso ?>"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
        <small style="color: #64748b; display: block; margin-top: 4px;">
          Tiempo de espera tras cargar la página (0 a 60 seg, por defecto 3s).
        </small>
      </div>
      <div>
        <label for="duracion_seg" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
          Cierre automático tras (segundos)
        </label>
        <input type="number" id="duracion_seg" name="duracion_seg" min="0" max="300" value="<?= $valDuracion ?>"
               style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px;"/>
        <small style="color: #64748b; display: block; margin-top: 4px;">
          0 = No se cierra solo (el usuario debe pulsar cerrar o fuera).
        </small>
      </div>
    </div>

    <div style="margin-bottom: 24px; padding: 18px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
      <label for="imagen" style="display: block; font-weight: 600; margin-bottom: 6px; color: #1e293b;">
        Afiche / Imagen de la Convocatoria <?= $esEdicion ? '(Opcional si conserva la actual)' : '*' ?>
      </label>
      <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png,image/webp" <?= $esEdicion ? '' : 'required' ?>
             style="display: block; margin-bottom: 8px; font-size: 14px;"/>
      <small style="color: #64748b; display: block;">
        Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 5 MB. Se recomienda orientación vertical (afiche) con texto legible.
      </small>

      <?php if ($esEdicion && !empty($convocatoria['imagen'])): ?>
        <div style="margin-top: 14px; display: flex; align-items: center; gap: 14px;">
          <img src="<?= $baseUrl . ltrim($convocatoria['imagen'], '/') ?>" alt="Afiche actual"
               style="max-height: 120px; border-radius: 6px; border: 1px solid #cbd5e1; box-shadow: 0 1px 4px rgba(0,0,0,0.06);"/>
          <span style="font-size: 13px; color: #475569;">
            Afiche actual cargado en el sistema.<br/>
            Suba un nuevo archivo si desea reemplazarlo.
          </span>
        </div>
      <?php endif; ?>
    </div>

    <div style="margin-bottom: 24px;">
      <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
        <input type="checkbox" name="activo" value="1" <?= $valActivo ? 'checked' : '' ?> style="width: 18px; height: 18px;"/>
        <span style="font-weight: 600; color: #1e293b; font-size: 15px;">Convocatoria Activa</span>
      </label>
      <small style="color: #64748b; display: block; margin-left: 28px; margin-top: 2px;">
        Si se desmarca, el popup no aparecerá incluso si está dentro del rango de fechas.
      </small>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #e2e8f0;">
      <a href="listar.php" class="btn btn-outline" style="padding: 10px 20px;">Cancelar</a>
      <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
        <?= $esEdicion ? 'Actualizar Convocatoria' : 'Guardar y Activar' ?>
      </button>
    </div>
  </form>
</div>

<?php require_once dirname(__DIR__, 2) . '/admin/footer.php'; ?>
