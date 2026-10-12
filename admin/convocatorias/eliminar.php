<?php
/**
 * JUSHSAL - Eliminación de Convocatoria (admin/convocatorias/eliminar.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/bitacora.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/convocatorias.php';

requerir_login();
csrf_requerir();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$conv = convocatorias_obtener_por_id($id);

if (!$conv) {
    flash('error', 'La convocatoria no existe o ya fue eliminada.');
    header('Location: listar.php');
    exit;
}

$titulo = $conv['titulo'];
$usuario = usuario_actual();

if (convocatorias_eliminar($id)) {
    bitacora_registrar(
        (int)$usuario['id'],
        'eliminar',
        'convocatorias',
        $id,
        "Eliminó convocatoria ID {$id}: '{$titulo}'"
    );
    flash('exito', 'Convocatoria eliminada correctamente.');
} else {
    flash('error', 'No se pudo eliminar la convocatoria.');
}

header('Location: listar.php');
exit;
