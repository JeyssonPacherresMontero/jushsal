<?php
/**
 * JUSHSAL - Procesar Cambio de Estado de Mensaje (admin/contactos/cambiar_estado.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/bitacora.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/contacto.php';

requerir_rol(['admin', 'comunicaciones']);
csrf_requerir();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id            = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nuevoEstado   = trim($_POST['estado'] ?? '');
$notasAtencion = trim($_POST['notas_atencion'] ?? ($_POST['notas_internas'] ?? ''));

$mensaje = contacto_obtener_por_id($id);
if (!$mensaje) {
    flash('error', 'El mensaje de contacto no existe.');
    header('Location: listar.php');
    exit;
}

if (!in_array($nuevoEstado, ['pendiente', 'atendido', 'archivado'], true)) {
    flash('error', 'El estado seleccionado no es válido.');
    header('Location: detalle.php?id=' . $id);
    exit;
}

$usuario = usuario_actual();
$atendidoPorId = ($nuevoEstado === 'atendido') ? (int)$usuario['id'] : null;

try {
    contacto_cambiar_estado($id, $nuevoEstado, $notasAtencion, $atendidoPorId);

    bitacora_registrar(
        (int)$usuario['id'],
        'actualizar',
        'mensajes_contacto',
        $id,
        "Cambió estado del mensaje #{$id} ({$mensaje['nombre']}) a '{$nuevoEstado}'"
    );

    flash('exito', "El mensaje #{$id} ha sido actualizado correctamente.");
    header('Location: detalle.php?id=' . $id);
    exit;

} catch (Throwable $e) {
    flash('error', 'Error al actualizar el mensaje: ' . $e->getMessage());
    header('Location: detalle.php?id=' . $id);
    exit;
}
