<?php
/**
 * JUSHSAL - Eliminar Fotografía de Galería (admin/galeria/eliminar.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/bitacora.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/galeria.php';

requerir_rol(['admin', 'comunicaciones']);
csrf_requerir();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$foto = galeria_obtener_por_id($id);

if (!$foto) {
    flash('error', 'La fotografía a eliminar no existe.');
    header('Location: listar.php');
    exit;
}

$usuario = usuario_actual();
$titulo = $foto['titulo'];

if (galeria_eliminar($id)) {
    bitacora_registrar(
        (int)$usuario['id'],
        'eliminar',
        'galeria_imagenes',
        $id,
        "Eliminó fotografía #{$id}: {$titulo}"
    );
    flash('exito', 'Fotografía eliminada correctamente.');
} else {
    flash('error', 'No se pudo eliminar la fotografía.');
}

header('Location: listar.php');
exit;
