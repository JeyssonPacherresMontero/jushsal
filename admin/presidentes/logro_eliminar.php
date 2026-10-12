<?php
/**
 * JUSHSAL - Gestión de Logros: Controlador Eliminar (admin/presidentes/logro_eliminar.php)
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

$gestionId = !empty($_POST['gestion_id']) ? (int)$_POST['gestion_id'] : null;
$logroId = !empty($_POST['logro_id']) ? (int)$_POST['logro_id'] : null;

if (!$logroId || !$gestionId) {
    flash('error', 'Parámetros incompletos.');
    redirigir('listar.php');
}

$logro = logros_obtener_por_id($logroId);
if ($logro) {
    logros_eliminar($logroId);
    registrar_bitacora('eliminar', 'logros_gestion', $logroId, "Logro eliminado: {$logro['titulo']}");
    flash('exito', 'Logro eliminado correctamente.');
}

redirigir("logros.php?gestion_id={$gestionId}");
