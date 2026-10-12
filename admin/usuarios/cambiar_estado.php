<?php
/**
 * JUSHSAL - Cambiar Estado de Usuario (admin/usuarios/cambiar_estado.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/bitacora.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/usuarios.php';

requerir_rol(['admin']);
csrf_requerir();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 0;
$usuarioAdmin = usuario_actual();

if ($id === (int)$usuarioAdmin['id']) {
    flash('error', 'No puede cambiar el estado de su propia cuenta de administrador.');
    header('Location: listar.php');
    exit;
}

$u = usuarios_obtener_por_id($id);
if (!$u) {
    flash('error', 'El usuario no existe.');
    header('Location: listar.php');
    exit;
}

usuarios_cambiar_estado($id, $activo);

$accionTexto = $activo ? 'activó' : 'desactivó';
bitacora_registrar(
    (int)$usuarioAdmin['id'],
    'editar',
    'usuarios',
    $id,
    "El administrador {$accionTexto} la cuenta del usuario '{$u['email']}'"
);

flash('exito', "Usuario '{$u['email']}' {$accionTexto} exitosamente.");
header('Location: listar.php');
exit;
