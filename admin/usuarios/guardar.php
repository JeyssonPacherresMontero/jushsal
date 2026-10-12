<?php
/**
 * JUSHSAL - Guardar Nuevo Usuario (admin/usuarios/guardar.php)
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

$nombre          = trim($_POST['nombre'] ?? '');
$email           = mb_strtolower(trim($_POST['email'] ?? ''), 'UTF-8');
$rolId           = (int)($_POST['rol_id'] ?? 2);
$password        = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';
$activo          = isset($_POST['activo']) ? 1 : 0;
$usuarioAdmin    = usuario_actual();

if ($password !== $passwordConfirm) {
    flash('error', 'Las contraseñas ingresadas no coinciden.');
    header('Location: form.php');
    exit;
}

try {
    $nuevoId = usuarios_crear([
        'nombre'   => $nombre,
        'email'    => $email,
        'rol_id'   => $rolId,
        'password' => $password,
        'activo'   => $activo
    ]);

    bitacora_registrar(
        (int)$usuarioAdmin['id'],
        'crear',
        'usuarios',
        $nuevoId,
        "Creó usuario ID {$nuevoId} '{$email}' con rol ID {$rolId}"
    );

    flash('exito', 'Usuario creado exitosamente.');
    header('Location: listar.php');
    exit;
} catch (Exception $e) {
    flash('error', 'Error al crear el usuario: ' . $e->getMessage());
    header('Location: form.php');
    exit;
}
