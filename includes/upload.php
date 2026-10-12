<?php
/**
 * JUSHSAL - Procesamiento Seguro de Subida de Imágenes
 * Validación estricta con finfo, getimagesize, límite de 5 MB y nombres aleatorios.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

/**
 * Valida y procesa la subida de una imagen institucional.
 *
 * @param array  $archivo      Elemento de $_FILES (ej. $_FILES['imagen'])
 * @param string $subcarpeta   Carpeta destino ('noticias', 'convocatorias', 'presidentes', 'logros')
 * @param int    $maxBytes     Tamaño máximo permitido en bytes (por defecto 5 MB)
 * @return array ['exito' => bool, 'ruta_relativa' => string|null, 'nombre' => string|null, 'error' => string|null]
 */
function subir_imagen(array $archivo, string $subcarpeta = 'noticias', int $maxBytes = 5242880): array
{
    // 1. Validar errores nativos de subida en PHP
    if (!isset($archivo['error']) || is_array($archivo['error'])) {
        return ['exito' => false, 'ruta_relativa' => null, 'nombre' => null, 'error' => 'Parámetros de subida inválidos.'];
    }

    switch ($archivo['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['exito' => false, 'ruta_relativa' => null, 'nombre' => null, 'error' => 'No se seleccionó ningún archivo para subir.'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['exito' => false, 'ruta_relativa' => null, 'nombre' => null, 'error' => 'El archivo supera el tamaño máximo permitido por el servidor.'];
        default:
            return ['exito' => false, 'ruta_relativa' => null, 'nombre' => null, 'error' => 'Error durante la recepción del archivo en el servidor.'];
    }

    // 2. Validar tamaño límite (5 MB por defecto)
    if (!isset($archivo['size']) || $archivo['size'] > $maxBytes) {
        $limiteStr = formato_bytes($maxBytes);
        return ['exito' => false, 'ruta_relativa' => null, 'nombre' => null, 'error' => "La imagen no debe superar los {$limiteStr}."];
    }

    $tmpPath = $archivo['tmp_name'] ?? '';
    if (!file_exists($tmpPath)) {
        return ['exito' => false, 'ruta_relativa' => null, 'nombre' => null, 'error' => 'El archivo temporal no existe.'];
    }

    // 3. Inspección rigurosa de tipo MIME con finfo_file (ignora extensión del cliente y $_FILES['type'])
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeReal = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    $mimesPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!array_key_exists($mimeReal, $mimesPermitidos)) {
        return [
            'exito'         => false,
            'ruta_relativa' => null,
            'nombre'        => null,
            'error'         => 'Formato no permitido. Solo se aceptan imágenes JPG, PNG o WEBP válidas.'
        ];
    }

    $extension = $mimesPermitidos[$mimeReal];

    // 4. Doble verificación de integridad gráfica mediante getimagesize()
    $infoGrafica = @getimagesize($tmpPath);
    if ($infoGrafica === false || empty($infoGrafica[0]) || empty($infoGrafica[1])) {
        return [
            'exito'         => false,
            'ruta_relativa' => null,
            'nombre'        => null,
            'error'         => 'El archivo no contiene una estructura de imagen válida o está dañado.'
        ];
    }

    // 5. Análisis de contenido: descartar código PHP incrustado en metadatos o cabeceras
    $contenido = @file_get_contents($tmpPath);
    if ($contenido !== false) {
        if (preg_match('/<\?php|<\?=|style\s*=\s*["\'][^"\']*expression/i', $contenido)) {
            return [
                'exito'         => false,
                'ruta_relativa' => null,
                'nombre'        => null,
                'error'         => 'El archivo contiene secuencias no seguras y fue rechazado.'
            ];
        }
    }

    // 6. Sanitizar subcarpeta y preparar destino físico
    $subcarpetaLimpia = preg_replace('/[^a-z0-9_-]/', '', strtolower($subcarpeta)) ?: 'noticias';
    $dirDestino = PATH_UPLOADS . $subcarpetaLimpia . DIRECTORY_SEPARATOR;

    if (!is_dir($dirDestino)) {
        if (!mkdir($dirDestino, 0755, true) && !is_dir($dirDestino)) {
            return ['exito' => false, 'ruta_relativa' => null, 'nombre' => null, 'error' => 'No se pudo crear el directorio de subidas.'];
        }
    }

    // 7. Nombre aleatorio criptográfico para evitar sobreescritura y ejecución
    $nombreAleatorio = bin2hex(random_bytes(12)) . '.' . $extension;
    $rutaFisica = $dirDestino . $nombreAleatorio;

    // En entorno web se usa move_uploaded_file; si se prueba en CLI o entorno simulado, copy/rename
    $movido = false;
    if (is_uploaded_file($tmpPath)) {
        $movido = move_uploaded_file($tmpPath, $rutaFisica);
    } else {
        $movido = copy($tmpPath, $rutaFisica);
    }

    if (!$movido) {
        return ['exito' => false, 'ruta_relativa' => null, 'nombre' => null, 'error' => 'Error al mover el archivo a su ubicación final.'];
    }

    // 8. Retornar ruta relativa normalizada para base de datos
    $rutaRelativa = 'uploads/' . $subcarpetaLimpia . '/' . $nombreAleatorio;

    return [
        'exito'         => true,
        'ruta_relativa' => $rutaRelativa,
        'nombre'        => $nombreAleatorio,
        'error'         => null
    ];
}

/**
 * Elimina una imagen física previamente subida protegiendo contra Directory Traversal.
 *
 * @param string|null $rutaRelativa (ej. "uploads/noticias/abc123.jpg")
 * @return bool
 */
function eliminar_imagen(?string $rutaRelativa): bool
{
    if (empty($rutaRelativa)) {
        return false;
    }

    // Normalizar separadores
    $rutaLimpia = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $rutaRelativa);
    $rutaAbsoluta = PATH_ROOT . ltrim($rutaLimpia, DIRECTORY_SEPARATOR);

    $realPath = realpath($rutaAbsoluta);
    $uploadsReal = realpath(PATH_UPLOADS);

    // Verificar que el archivo esté estrictamente dentro de PATH_UPLOADS
    if ($realPath && $uploadsReal && str_starts_with($realPath, $uploadsReal) && is_file($realPath)) {
        return @unlink($realPath);
    }

    return false;
}
