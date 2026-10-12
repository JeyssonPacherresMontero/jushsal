<?php
/**
 * JUSHSAL - Guardar Fotografía de Galería (admin/galeria/guardar.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/bitacora.php';
require_once dirname(__DIR__, 2) . '/includes/upload.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/galeria.php';

requerir_rol(['admin', 'comunicaciones']);
csrf_requerir();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$titulo      = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$orden       = isset($_POST['orden']) ? (int)$_POST['orden'] : 0;
$esDestacada = isset($_POST['es_destacada']) ? 1 : 0;
$activo      = isset($_POST['activo']) ? 1 : 0;
$usuario     = usuario_actual();

if ($titulo === '') {
    flash('error', 'El título o pie de foto es obligatorio.');
    header('Location: form.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}

$fotoActual = null;
if ($id > 0) {
    $fotoActual = galeria_obtener_por_id($id);
    if (!$fotoActual) {
        flash('error', 'La fotografía a actualizar no existe.');
        header('Location: listar.php');
        exit;
    }
}

// Procesar archivo de imagen
$rutaImagenNueva = null;
$tieneArchivo = isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE;

if ($tieneArchivo) {
    $resSubida = subir_imagen($_FILES['imagen'], 'galeria');
    if (!$resSubida['exito']) {
        flash('error', 'Error al procesar la imagen: ' . $resSubida['error']);
        header('Location: form.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }
    $rutaImagenNueva = $resSubida['ruta'];
} elseif (!$fotoActual) {
    flash('error', 'Debe seleccionar un archivo de imagen para la nueva fotografía.');
    header('Location: form.php');
    exit;
}

try {
    if ($id > 0) {
        // Actualización
        $datosUpdate = [
            'titulo'       => $titulo,
            'descripcion'  => $descripcion,
            'es_destacada' => $esDestacada,
            'orden'        => $orden,
            'activo'       => $activo
        ];

        if ($rutaImagenNueva) {
            $datosUpdate['imagen'] = $rutaImagenNueva;
            // Eliminar imagen anterior si residía en uploads/
            if (!empty($fotoActual['imagen']) && str_starts_with($fotoActual['imagen'], 'uploads/')) {
                eliminar_imagen($fotoActual['imagen']);
            }
        }

        galeria_actualizar($id, $datosUpdate);

        bitacora_registrar(
            (int)$usuario['id'],
            'actualizar',
            'galeria_imagenes',
            $id,
            "Actualizó fotografía #{$id}: {$titulo}"
        );

        flash('exito', 'Fotografía actualizada correctamente.');
    } else {
        // Creación
        $nuevoId = galeria_crear([
            'titulo'       => $titulo,
            'descripcion'  => $descripcion,
            'imagen'       => $rutaImagenNueva,
            'es_destacada' => $esDestacada,
            'orden'        => $orden,
            'activo'       => $activo,
            'creado_por'   => (int)$usuario['id']
        ]);

        bitacora_registrar(
            (int)$usuario['id'],
            'crear',
            'galeria_imagenes',
            $nuevoId,
            "Publicó nueva fotografía en galería #{$nuevoId}: {$titulo}"
        );

        flash('exito', 'Fotografía agregada a la galería con éxito.');
    }

    header('Location: listar.php');
    exit;

} catch (Throwable $e) {
    if ($rutaImagenNueva) {
        eliminar_imagen($rutaImagenNueva);
    }
    flash('error', 'Error en el servidor al guardar la fotografía: ' . $e->getMessage());
    header('Location: form.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}
