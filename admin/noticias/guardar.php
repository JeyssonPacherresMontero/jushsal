<?php
/**
 * JUSHSAL - Gestión de Noticias: Controlador Guardar (admin/noticias/guardar.php)
 * Procesa la creación y edición de noticias con subida de imagen, slugs y bitácora.
 */

define('JUSHSAL_INIT', true);

require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/csrf.php';
require_once dirname(dirname(__DIR__)) . '/includes/helpers.php';
require_once dirname(dirname(__DIR__)) . '/includes/upload.php';
require_once dirname(dirname(__DIR__)) . '/includes/bitacora.php';
require_once dirname(dirname(__DIR__)) . '/includes/modelos/noticias.php';

// Verificar permisos
requerir_rol(['admin', 'comunicaciones']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir(BASE_URL . 'admin/noticias/listar.php');
}

csrf_requerir();

$usuario = usuario_actual();
$id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

$titulo = trim($_POST['titulo'] ?? '');
$resumen = trim($_POST['resumen'] ?? '');
$contenido = trim($_POST['contenido'] ?? '');
$categoriaId = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
$fechaPublicacion = !empty($_POST['fecha_publicacion']) ? $_POST['fecha_publicacion'] : date('Y-m-d');
$estado = in_array($_POST['estado'] ?? '', ['borrador', 'publicado', 'archivado'], true) ? $_POST['estado'] : 'borrador';
$destacada = !empty($_POST['destacada']) ? 1 : 0;
$eliminarImagen = !empty($_POST['eliminar_imagen']);

// Validaciones obligatorias
if (empty($titulo)) {
    flash('error', 'El título de la noticia es obligatorio.');
    redirigir($id ? "form.php?id={$id}" : 'form.php');
}

if (empty($resumen)) {
    flash('error', 'El resumen de la noticia es obligatorio.');
    redirigir($id ? "form.php?id={$id}" : 'form.php');
}

// Obtener noticia previa si es edición
$noticiaPrevia = $id ? noticias_obtener_por_id($id) : null;
if ($id && !$noticiaPrevia) {
    flash('error', 'La noticia que intenta editar no existe.');
    redirigir('listar.php');
}

$rutaImagen = $noticiaPrevia['imagen_portada'] ?? null;

// Manejo de eliminación explícita de imagen
if ($eliminarImagen && $rutaImagen) {
    eliminar_imagen($rutaImagen);
    $rutaImagen = null;
}

// Manejo de subida de nueva imagen
if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
    $resSubida = subir_imagen($_FILES['imagen'], 'noticias');
    if (!$resSubida['exito']) {
        flash('error', 'Error en la imagen: ' . $resSubida['error']);
        redirigir($id ? "form.php?id={$id}" : 'form.php');
    }

    // Si había una imagen previa y se subió una nueva, borrar la anterior
    if ($rutaImagen && $rutaImagen !== $resSubida['ruta_relativa']) {
        eliminar_imagen($rutaImagen);
    }

    $rutaImagen = $resSubida['ruta_relativa'];
}

// Generación o actualización de slug único
$slug = noticias_generar_slug_unico($titulo, $id);

$datos = [
    'categoria_id'      => $categoriaId,
    'titulo'            => $titulo,
    'slug'              => $slug,
    'resumen'           => $resumen,
    'contenido'         => $contenido ?: null,
    'imagen_portada'    => $rutaImagen,
    'fecha_publicacion' => $fechaPublicacion,
    'estado'            => $estado,
    'destacada'         => $destacada,
    'autor_id'          => $usuario['id']
];

if ($id) {
    $exito = noticias_actualizar($id, $datos);
    if ($exito) {
        registrar_bitacora('editar', 'noticias', $id, "Noticia editada: {$titulo} (estado: {$estado})");
        flash('exito', 'Noticia actualizada exitosamente.');
    } else {
        flash('error', 'Ocurrió un error al actualizar la noticia.');
    }
} else {
    $nuevoId = noticias_crear($datos);
    if ($nuevoId > 0) {
        registrar_bitacora('crear', 'noticias', $nuevoId, "Noticia creada: {$titulo} (estado: {$estado})");
        flash('exito', 'Noticia creada exitosamente.');
    } else {
        flash('error', 'Ocurrió un error al registrar la noticia.');
    }
}

redirigir('listar.php');
