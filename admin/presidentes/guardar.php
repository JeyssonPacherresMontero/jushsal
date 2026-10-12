<?php
/**
 * JUSHSAL - Gestión de Presidentes: Controlador Guardar (admin/presidentes/guardar.php)
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

$accion = $_POST['accion'] ?? 'guardar';
$id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

// Acción rápida: Marcar como vigente
if ($accion === 'hacer_vigente' && $id) {
    $gestion = gestiones_obtener_por_id($id);
    if ($gestion) {
        gestiones_marcar_vigente($id);
        registrar_bitacora('editar', 'gestiones_presidenciales', $id, "Gestión marcada como vigente: {$gestion['presidente_nombre']}");
        flash('exito', "La gestión de {$gestion['presidente_nombre']} ahora es la gestión vigente.");
    }
    redirigir('listar.php');
}

// Guardar o actualizar datos de formulario
$nombre = trim($_POST['presidente_nombre'] ?? '');
$inicio = !empty($_POST['periodo_inicio']) ? (int)$_POST['periodo_inicio'] : null;
$fin = !empty($_POST['periodo_fin']) ? (int)$_POST['periodo_fin'] : null;
$semblanza = trim($_POST['semblanza'] ?? '');
$esVigente = !empty($_POST['es_vigente']) ? 1 : 0;
$eliminarFoto = !empty($_POST['eliminar_foto']);

if (empty($nombre)) {
    flash('error', 'El nombre del presidente es obligatorio.');
    redirigir($id ? "form.php?id={$id}" : 'form.php');
}

if (!$inicio || $inicio < 1950) {
    flash('error', 'El año de inicio de la gestión no es válido.');
    redirigir($id ? "form.php?id={$id}" : 'form.php');
}

if ($fin !== null && $fin < $inicio) {
    flash('error', "El año de fin ({$fin}) debe ser mayor o igual al año de inicio ({$inicio}).");
    redirigir($id ? "form.php?id={$id}" : 'form.php');
}

$gestionPrevia = $id ? gestiones_obtener_por_id($id) : null;
$rutaFoto = $gestionPrevia['foto'] ?? null;

if ($eliminarFoto && $rutaFoto) {
    eliminar_imagen($rutaFoto);
    $rutaFoto = null;
}

if (!empty($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    $resSubida = subir_imagen($_FILES['foto'], 'presidentes');
    if (!$resSubida['exito']) {
        flash('error', 'Error en la fotografía: ' . $resSubida['error']);
        redirigir($id ? "form.php?id={$id}" : 'form.php');
    }

    if ($rutaFoto && $rutaFoto !== $resSubida['ruta_relativa']) {
        eliminar_imagen($rutaFoto);
    }

    $rutaFoto = $resSubida['ruta_relativa'];
}

$datos = [
    'presidente_nombre' => $nombre,
    'periodo_inicio'    => $inicio,
    'periodo_fin'       => $fin,
    'foto'              => $rutaFoto,
    'semblanza'         => $semblanza ?: null,
    'es_vigente'        => $esVigente
];

try {
    if ($id) {
        gestiones_actualizar($id, $datos);
        registrar_bitacora('editar', 'gestiones_presidenciales', $id, "Gestión editada: {$nombre}");
        flash('exito', 'Gestión presidencial actualizada exitosamente.');
    } else {
        $nuevoId = gestiones_crear($datos);
        registrar_bitacora('crear', 'gestiones_presidenciales', $nuevoId, "Gestión creada: {$nombre} ({$inicio})");
        flash('exito', 'Gestión presidencial registrada exitosamente.');
    }
} catch (Throwable $e) {
    flash('error', 'Error al procesar la gestión: ' . $e->getMessage());
    redirigir($id ? "form.php?id={$id}" : 'form.php');
}

redirigir('listar.php');
