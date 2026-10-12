<?php
/**
 * JUSHSAL - Gestión de Logros: Controlador Guardar (admin/presidentes/logro_guardar.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/csrf.php';
require_once dirname(dirname(__DIR__)) . '/includes/helpers.php';
require_once dirname(dirname(__DIR__)) . '/includes/upload.php';
require_once dirname(dirname(__DIR__)) . '/includes/bitacora.php';
require_once dirname(dirname(__DIR__)) . '/includes/modelos/gestiones.php';

// Permisos
requerir_rol(['admin', 'comunicaciones']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('listar.php');
}

csrf_requerir();

$gestionId = !empty($_POST['gestion_id']) ? (int)$_POST['gestion_id'] : null;
$logroId = !empty($_POST['logro_id']) ? (int)$_POST['logro_id'] : null;

if (!$gestionId) {
    flash('error', 'Gestión no especificada.');
    redirigir('listar.php');
}

$anio = !empty($_POST['anio']) ? (int)$_POST['anio'] : null;
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$orden = !empty($_POST['orden']) ? (int)$_POST['orden'] : 0;

if (empty($titulo)) {
    flash('error', 'El título de la obra o logro es obligatorio.');
    redirigir("logros.php?gestion_id={$gestionId}");
}

$logroPrevio = $logroId ? logros_obtener_por_id($logroId) : null;
$rutaImagen = $logroPrevio['imagen'] ?? null;

if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
    $resSubida = subir_imagen($_FILES['imagen'], 'logros');
    if (!$resSubida['exito']) {
        flash('error', 'Error al subir la imagen del logro: ' . $resSubida['error']);
        redirigir("logros.php?gestion_id={$gestionId}");
    }

    if ($rutaImagen && $rutaImagen !== $resSubida['ruta_relativa']) {
        eliminar_imagen($rutaImagen);
    }

    $rutaImagen = $resSubida['ruta_relativa'];
}

$datos = [
    'gestion_id'  => $gestionId,
    'anio'        => $anio,
    'titulo'      => $titulo,
    'descripcion' => $descripcion ?: null,
    'imagen'      => $rutaImagen,
    'orden'       => $orden
];

try {
    if ($logroId) {
        logros_actualizar($logroId, $datos);
        registrar_bitacora('editar', 'logros_gestion', $logroId, "Logro editado ({$anio}): {$titulo}");
        flash('exito', 'Logro de gestión actualizado exitosamente.');
    } else {
        $nuevoId = logros_crear($datos);
        registrar_bitacora('crear', 'logros_gestion', $nuevoId, "Logro registrado ({$anio}): {$titulo}");
        flash('exito', 'Logro registrado exitosamente en el año ' . $anio . '.');
    }
} catch (RangeException|InvalidArgumentException $e) {
    flash('error', $e->getMessage());
} catch (Throwable $e) {
    flash('error', 'Error inesperado al guardar el logro: ' . $e->getMessage());
}

redirigir("logros.php?gestion_id={$gestionId}");
