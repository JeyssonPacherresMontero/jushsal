<?php
/**
 * JUSHSAL - Gestión de Presidentes: Controlador Eliminar (admin/presidentes/eliminar.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/csrf.php';
require_once dirname(dirname(__DIR__)) . '/includes/helpers.php';
require_once dirname(dirname(__DIR__)) . '/includes/bitacora.php';
require_once dirname(dirname(__DIR__)) . '/includes/modelos/gestiones.php';

// Permisos
requerir_rol(['admin', 'comunicaciones']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('listar.php');
}

csrf_requerir();

$id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
if (!$id) {
    flash('error', 'Identificador de gestión no proporcionado.');
    redirigir('listar.php');
}

$gestion = gestiones_obtener_por_id($id);
if (!$gestion) {
    flash('error', 'La gestión indicada no existe.');
    redirigir('listar.php');
}

// Proteger contra eliminación accidental de la gestión actualmente vigente
if (!empty($gestion['es_vigente'])) {
    flash('error', 'No se puede eliminar la gestión vigente actual. Marque otra gestión como vigente antes de proceder.');
    redirigir('listar.php');
}

$exito = gestiones_eliminar($id);
if ($exito) {
    registrar_bitacora('eliminar', 'gestiones_presidenciales', $id, "Gestión eliminada: {$gestion['presidente_nombre']}");
    flash('exito', 'Gestión presidencial y sus logros asociados han sido eliminados.');
} else {
    flash('error', 'Ocurrió un error al intentar eliminar la gestión.');
}

redirigir('listar.php');
