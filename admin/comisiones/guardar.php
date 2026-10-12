<?php
/**
 * JUSHSAL - Guardar Cambios de Comisión (admin/comisiones/guardar.php)
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/bitacora.php';
require_once dirname(__DIR__, 2) . '/includes/modelos/comisiones.php';

requerir_login();
csrf_requerir();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$comision = comisiones_obtener_por_id($id);

if (!$comision) {
    flash('error', 'La comisión no existe.');
    header('Location: listar.php');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$activo = isset($_POST['activo']) ? 1 : 0;
$nombreCompleto = trim($_POST['nombre_completo'] ?? '');
$periodoInicio = !empty($_POST['periodo_inicio']) ? (int)$_POST['periodo_inicio'] : 2026;
$periodoFin = !empty($_POST['periodo_fin']) ? (int)$_POST['periodo_fin'] : 2029;
$celular = !empty($_POST['celular']) ? trim($_POST['celular']) : null;
$usuario = usuario_actual();

if ($nombre === '') {
    flash('error', 'El nombre de la comisión no puede estar vacío.');
    header('Location: form.php?id=' . $id);
    exit;
}

if ($periodoFin < $periodoInicio) {
    flash('error', 'El año de fin del periodo no puede ser menor al año de inicio.');
    header('Location: form.php?id=' . $id);
    exit;
}

try {
    // 1. Actualizar datos de la comisión
    comisiones_actualizar($id, [
        'nombre' => $nombre,
        'activo' => $activo
    ]);

    // 2. Actualizar o registrar presidente
    comision_presidente_guardar($id, [
        'nombre_completo' => $nombreCompleto,
        'periodo_inicio'  => $periodoInicio,
        'periodo_fin'     => $periodoFin,
        'celular'         => $celular
    ]);

    bitacora_registrar(
        (int)$usuario['id'],
        'editar',
        'comisiones',
        $id,
        "Actualizó comisión N.° {$comision['numero']} '{$nombre}' y datos de representación"
    );

    flash('exito', 'Comisión N.° ' . sprintf('%02d', (int)$comision['numero']) . ' actualizada exitosamente.');
    header('Location: listar.php');
    exit;
} catch (Exception $e) {
    flash('error', 'Error al guardar los cambios: ' . $e->getMessage());
    header('Location: form.php?id=' . $id);
    exit;
}
