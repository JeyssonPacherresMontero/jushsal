<?php
/**
 * JUSHSAL - Guardar Documento Institucional (admin/documentos/guardar.php)
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

$id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$titulo      = trim($_POST['titulo'] ?? '');
$categoriaId = (int)($_POST['categoria_id'] ?? 0);
$descripcion = trim($_POST['descripcion'] ?? '');
$anio        = !empty($_POST['anio']) ? (int)$_POST['anio'] : (int)date('Y');
$esDestacado = isset($_POST['es_destacado']) ? 1 : 0;
$activo      = isset($_POST['activo']) ? 1 : 0;
$usuario     = usuario_actual();

if ($titulo === '') {
    flash('error', 'El título del documento es obligatorio.');
    header('Location: form.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}

if ($categoriaId <= 0) {
    flash('error', 'Debe seleccionar una categoría institucional.');
    header('Location: form.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}

$docActual = null;
if ($id > 0) {
    $docActual = documentos_obtener_por_id($id);
    if (!$docActual) {
        flash('error', 'El documento a actualizar no existe.');
        header('Location: listar.php');
        exit;
    }
}

// Procesar archivo PDF
$resultadoArchivo = null;
$tieneArchivo = isset($_FILES['archivo']) && $_FILES['archivo']['error'] !== UPLOAD_ERR_NO_FILE;

if ($tieneArchivo) {
    $resSubida = subir_documento_pdf($_FILES['archivo'], 'documentos');
    if (!$resSubida['exito']) {
        flash('error', 'Error al procesar el archivo PDF: ' . $resSubida['error']);
        header('Location: form.php' . ($id > 0 ? '?id=' . $id : ''));
        exit;
    }
    $resultadoArchivo = $resSubida;
} elseif (!$docActual) {
    flash('error', 'Debe adjuntar un archivo PDF para el nuevo documento.');
    header('Location: form.php');
    exit;
}

try {
    if ($id > 0) {
        // Actualizar
        $datosUpdate = [
            'categoria_id' => $categoriaId,
            'titulo'       => $titulo,
            'descripcion'  => $descripcion,
            'anio'         => $anio,
            'es_destacado' => $esDestacado,
            'activo'       => $activo
        ];

        if ($resultadoArchivo !== null) {
            $datosUpdate['archivo_ruta']    = $resultadoArchivo['ruta'];
            $datosUpdate['nombre_original'] = $resultadoArchivo['nombre_original'];
            $datosUpdate['tamanio_bytes']   = $resultadoArchivo['tamanio_bytes'];

            // Eliminar archivo anterior si estaba en uploads/
            if (!empty($docActual['archivo_ruta'])) {
                eliminar_documento($docActual['archivo_ruta']);
            }
        }

        documentos_actualizar($id, $datosUpdate);

        bitacora_registrar(
            (int)$usuario['id'],
            'editar',
            'documentos',
            $id,
            "Actualizó documento ID {$id}: '{$titulo}'"
        );

        flash('exito', 'Documento actualizado correctamente.');
    } else {
        // Crear
        $nuevoId = documentos_crear([
            'categoria_id'    => $categoriaId,
            'titulo'          => $titulo,
            'descripcion'     => $descripcion,
            'archivo_ruta'    => $resultadoArchivo['ruta'],
            'nombre_original' => $resultadoArchivo['nombre_original'],
            'tamanio_bytes'   => $resultadoArchivo['tamanio_bytes'],
            'formato'         => 'PDF',
            'anio'            => $anio,
            'es_destacado'    => $esDestacado,
            'activo'          => $activo,
            'creado_por'      => (int)$usuario['id']
        ]);

        bitacora_registrar(
            (int)$usuario['id'],
            'crear',
            'documentos',
            $nuevoId,
            "Publicó nuevo documento ID {$nuevoId}: '{$titulo}'"
        );

        flash('exito', 'Documento publicado exitosamente.');
    }

    header('Location: listar.php');
    exit;
} catch (Exception $e) {
    if ($resultadoArchivo !== null && !empty($resultadoArchivo['ruta'])) {
        eliminar_documento($resultadoArchivo['ruta']);
    }
    flash('error', 'Error al guardar el documento: ' . $e->getMessage());
    header('Location: form.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}
