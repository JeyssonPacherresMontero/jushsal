<?php
/**
 * JUSHSAL - Eliminación de Documento (admin/documentos/eliminar.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/bitacora.php';
require_once dirname(__DIR__, 2) . '/includes/upload_documento.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/documentos.php';

requerir_rol(['admin', 'comunicaciones']);
csrf_requerir();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$doc = documentos_obtener_por_id($id);

if (!$doc) {
    flash('error', 'El documento no existe o ya fue eliminado.');
    header('Location: listar.php');
    exit;
}

$titulo = $doc['titulo'];
$usuario = usuario_actual();

if (documentos_eliminar($id)) {
    bitacora_registrar(
        (int)$usuario['id'],
        'eliminar',
        'documentos',
        $id,
        "Eliminó documento ID {$id}: '{$titulo}'"
    );
    flash('exito', 'Documento eliminado correctamente.');
} else {
    flash('error', 'No se pudo eliminar el documento.');
}

header('Location: listar.php');
exit;
