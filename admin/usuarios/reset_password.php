<?php
/**
 * JUSHSAL - Resetear Contraseña de Usuario (admin/usuarios/reset_password.php)
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
$nuevaPassword = $_POST['nueva_password'] ?? '';
$confirmarPassword = $_POST['confirmar_password'] ?? '';
$usuarioAdmin = usuario_actual();

$u = usuarios_obtener_por_id($id);
if (!$u) {
    flash('error', 'El usuario no existe.');
    header('Location: listar.php');
    exit;
}

if ($nuevaPassword !== $confirmarPassword) {
    flash('error', 'Las contraseñas no coinciden.');
    header('Location: listar.php');
    exit;
}

if (strlen($nuevaPassword) < 8) {
    flash('error', 'La nueva contraseña debe tener al menos 8 caracteres.');
    header('Location: listar.php');
    exit;
}

try {
    usuarios_cambiar_password($id, $nuevaPassword);

    bitacora_registrar(
        (int)$usuarioAdmin['id'],
        'editar',
        'usuarios',
        $id,
        "El administrador reseteó la contraseña del usuario '{$u['email']}'"
    );

    flash('exito', "Contraseña actualizada exitosamente para '{$u['email']}'.");
    header('Location: listar.php');
    exit;
} catch (Exception $e) {
    flash('error', 'Error al actualizar la contraseña: ' . $e->getMessage());
    header('Location: listar.php');
    exit;
}
