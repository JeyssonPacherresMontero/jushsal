<?php
/**
 * JUSHSAL - Procesador de Guardado de Convocatorias (admin/convocatorias/guardar.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/bitacora.php';
require_once dirname(__DIR__, 2) . '/includes/upload.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/convocatorias.php';

requerir_login();
csrf_requerir();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$titulo        = trim($_POST['titulo'] ?? '');
$fechaAsamblea = !empty($_POST['fecha_asamblea']) ? trim($_POST['fecha_asamblea']) : null;
$mostrarDesde  = trim($_POST['mostrar_desde'] ?? '');
$mostrarHasta  = trim($_POST['mostrar_hasta'] ?? '');
$retrasoSeg    = max(0, min(60, (int)($_POST['retraso_seg'] ?? 3)));
$duracionSeg   = max(0, min(300, (int)($_POST['duracion_seg'] ?? 0)));
$frecuencia    = in_array($_POST['frecuencia'] ?? '', ['cada_visita', 'una_vez_por_sesion', 'una_vez_por_dia'], true)
    ? $_POST['frecuencia']
    : 'una_vez_por_sesion';
$activo        = isset($_POST['activo']) ? 1 : 0;
$usuario       = usuario_actual();

// Validaciones
if ($titulo === '') {
    flash('error', 'El título de la convocatoria es obligatorio.');
    header('Location: form.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}

if ($mostrarDesde === '' || $mostrarHasta === '') {
    flash('error', 'Debe especificar el rango completo de fechas de vigencia.');
    header('Location: form.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}

// Convertir fechas a formato MySQL Y-m-d H:i:s
$tsDesde = strtotime($mostrarDesde);
$tsHasta = strtotime($mostrarHasta);

if (!$tsDesde || !$tsHasta || $tsHasta <= $tsDesde) {
    flash('error', 'La fecha de fin de vigencia debe ser estrictamente posterior a la fecha de inicio.');
    header('Location: form.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}

$mysqlDesde = date('Y-m-d H:i:s', $tsDesde);
$mysqlHasta = date('Y-m-d H:i:s', $tsHasta);

$convocatoriaActual = null;
if ($id > 0) {
    $convocatoriaActual = convocatorias_obtener_por_id($id);
    if (!$convocatoriaActual) {
        flash('error', 'La convocatoria a actualizar no existe.');
        header('Location: listar.php');
        exit;
    }
}

// Procesar imagen
$rutaImagen = null;
$tieneArchivo = isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE;

if ($tieneArchivo) {
    $resultadoSubida = subir_imagen($_FILES['imagen'], 'convocatorias');
    if (!$resultadoSubida['exito']) {
        flash('error', 'Error en el archivo de imagen: ' . $resultadoSubida['error']);
        header('Location: form.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }
    $rutaImagen = $resultadoSubida['ruta'];
} elseif (!$convocatoriaActual) {
    flash('error', 'Debe subir un afiche o imagen para la nueva convocatoria.');
    header('Location: form.php');
    exit;
}

try {
    if ($id > 0) {
        // Actualizar
        $datosUpdate = [
            'titulo'         => $titulo,
            'fecha_asamblea' => $fechaAsamblea,
            'mostrar_desde'  => $mysqlDesde,
            'mostrar_hasta'  => $mysqlHasta,
            'retraso_seg'    => $retrasoSeg,
            'duracion_seg'   => $duracionSeg,
            'frecuencia'     => $frecuencia,
            'activo'         => $activo
        ];

        if ($rutaImagen !== null) {
            $datosUpdate['imagen'] = $rutaImagen;
            // Eliminar imagen anterior
            if (!empty($convocatoriaActual['imagen'])) {
                eliminar_imagen($convocatoriaActual['imagen']);
            }
        }

        convocatorias_actualizar($id, $datosUpdate);

        bitacora_registrar(
            (int)$usuario['id'],
            'editar',
            'convocatorias',
            $id,
            "Actualizó convocatoria ID {$id}: '{$titulo}'"
        );

        flash('exito', 'Convocatoria actualizada exitosamente.');
    } else {
        // Crear
        $nuevoId = convocatorias_crear([
            'titulo'         => $titulo,
            'imagen'         => $rutaImagen,
            'fecha_asamblea' => $fechaAsamblea,
            'mostrar_desde'  => $mysqlDesde,
            'mostrar_hasta'  => $mysqlHasta,
            'retraso_seg'    => $retrasoSeg,
            'duracion_seg'   => $duracionSeg,
            'frecuencia'     => $frecuencia,
            'activo'         => $activo,
            'creado_por'     => (int)$usuario['id']
        ]);

        bitacora_registrar(
            (int)$usuario['id'],
            'crear',
            'convocatorias',
            $nuevoId,
            "Creó convocatoria ID {$nuevoId}: '{$titulo}'"
        );

        flash('exito', 'Convocatoria creada y programada exitosamente.');
    }

    header('Location: listar.php');
    exit;
} catch (Exception $e) {
    if ($rutaImagen !== null) {
        eliminar_imagen($rutaImagen);
    }
    flash('error', 'Error al guardar la convocatoria: ' . $e->getMessage());
    header('Location: form.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}
