<?php
/**
 * JUSHSAL - Gestión de Noticias: Controlador Eliminar / Archivar (admin/noticias/eliminar.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/csrf.php';
require_once dirname(dirname(__DIR__)) . '/includes/helpers.php';
require_once dirname(dirname(__DIR__)) . '/includes/bitacora.php';
require_once dirname(dirname(__DIR__)) . '/includes/modelos/noticias.php';

// Permisos
requerir_rol(['admin', 'comunicaciones']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('listar.php');
}

csrf_requerir();

$usuario = usuario_actual();
$id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
$accion = $_POST['accion'] ?? 'archivar';

if (!$id) {
    flash('error', 'Identificador de noticia no proporcionado.');
    redirigir('listar.php');
}

$noticia = noticias_obtener_por_id($id);
if (!$noticia) {
    flash('error', 'La noticia indicada no existe.');
    redirigir('listar.php');
}

if ($accion === 'eliminar_permanente') {
    // Solo el rol admin puede realizar borrado físico permanente
    if ($usuario['rol_nombre'] !== 'admin') {
        flash('error', 'Solo el administrador general puede eliminar definitivamente una noticia.');
        redirigir('listar.php');
    }

    $exito = noticias_eliminar($id, false);
    if ($exito) {
        registrar_bitacora('eliminar', 'noticias', $id, "Noticia eliminada físicamente: {$noticia['titulo']}");
        flash('exito', 'Noticia eliminada definitivamente de la base de datos.');
    } else {
        flash('error', 'No se pudo eliminar la noticia.');
    }
} else {
    // Borrado lógico (archivar)
    $exito = noticias_eliminar($id, true);
    if ($exito) {
        registrar_bitacora('editar', 'noticias', $id, "Noticia archivada: {$noticia['titulo']}");
        flash('exito', 'La noticia ha sido archivada y ya no es visible públicamente.');
    } else {
        flash('error', 'No se pudo archivar la noticia.');
    }
}

redirigir('listar.php');
