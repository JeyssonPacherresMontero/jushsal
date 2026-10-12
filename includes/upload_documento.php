<?php
/**
 * JUSHSAL - Subida Segura de Documentos PDF
 * Validación de tipo MIME real, verificación de cabecera binaria (%PDF-) y control de tamaño.
 */

if (!defined('JUSHSAL_INIT')) {
    define('JUSHSAL_INIT', true);
}

/**
 * Valida y guarda un documento PDF subido mediante formulario.
 *
 * @param array  $archivo    Elemento de $_FILES['nombre_campo']
 * @param string $subcarpeta Subcarpeta dentro de uploads/ (por defecto 'documentos')
 * @return array ['exito' => bool, 'ruta' => string|null, 'error' => string|null, 'nombre_original' => string, 'tamanio_bytes' => int, 'formato' => string]
 */
function subir_documento_pdf(array $archivo, string $subcarpeta = 'documentos'): array
{
    // 1. Validar que exista el archivo y no tenga error de subida
    if (!isset($archivo['error']) || is_array($archivo['error'])) {
        return ['exito' => false, 'error' => 'Parámetros de archivo inválidos.'];
    }

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $errores = [
            UPLOAD_ERR_INI_SIZE   => 'El archivo excede el tamaño máximo permitido por el servidor.',
            UPLOAD_ERR_FORM_SIZE  => 'El archivo excede el tamaño máximo permitido por el formulario.',
            UPLOAD_ERR_PARTIAL    => 'El archivo solo se subió parcialmente.',
            UPLOAD_ERR_NO_FILE    => 'No se seleccionó ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal en el servidor.',
            UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco.',
            UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida del archivo.'
        ];
        return ['exito' => false, 'error' => $errores[$archivo['error']] ?? 'Error desconocido al subir el archivo.'];
    }

    // 2. Validar tamaño (máximo 15 MB)
    $limiteBytes = 15 * 1024 * 1024; // 15 MB
    if ($archivo['size'] > $limiteBytes) {
        return ['exito' => false, 'error' => 'El archivo supera el tamaño máximo permitido de 15 MB.'];
    }

    // 3. Validar extensión inicial
    $nombreOriginal = basename($archivo['name']);
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    if ($extension !== 'pdf') {
        return ['exito' => false, 'error' => 'Solo se permiten archivos en formato PDF (.pdf).'];
    }

    // 4. Validar tipo MIME real mediante finfo (inspección de contenido binario)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        return ['exito' => false, 'error' => 'No se pudo inicializar la extensión fileinfo en el servidor.'];
    }

    $mimeReal = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);

    if ($mimeReal !== 'application/pdf') {
        return ['exito' => false, 'error' => 'El contenido del archivo no corresponde a un documento PDF legítimo (MIME detectado: ' . htmlspecialchars($mimeReal) . ').'];
    }

    // 5. Verificación de cabecera binaria (%PDF-)
    $handle = @fopen($archivo['tmp_name'], 'rb');
    if (!$handle) {
        return ['exito' => false, 'error' => 'No se pudo leer el archivo temporal para verificación.'];
    }
    $cabecera = fread($handle, 5);
    fclose($handle);

    if ($cabecera !== '%PDF-') {
        return ['exito' => false, 'error' => 'El archivo no cuenta con una firma válida de documento PDF.'];
    }

    // 6. Preparar directorio de destino seguro
    $directorioDestino = dirname(__DIR__) . '/uploads/' . trim($subcarpeta, '/');
    if (!is_dir($directorioDestino)) {
        if (!mkdir($directorioDestino, 0755, true) && !is_dir($directorioDestino)) {
            return ['exito' => false, 'error' => 'No se pudo crear el directorio de subidas.'];
        }
    }

    // 7. Generar nombre aleatorio criptográfico para evitar colisiones y ejecución maliciosa
    try {
        $nombreAleatorio = bin2hex(random_bytes(16)) . '.pdf';
    } catch (Exception $e) {
        $nombreAleatorio = md5(uniqid((string)mt_rand(), true)) . '.pdf';
    }

    $rutaCompleta = $directorioDestino . '/' . $nombreAleatorio;
    $rutaRelativa = 'uploads/' . trim($subcarpeta, '/') . '/' . $nombreAleatorio;

    // 8. Mover archivo a destino final (move_uploaded_file en web, copy en CLI de prueba)
    $movido = false;
    if (is_uploaded_file($archivo['tmp_name'])) {
        $movido = move_uploaded_file($archivo['tmp_name'], $rutaCompleta);
    } else {
        $movido = copy($archivo['tmp_name'], $rutaCompleta);
    }

    if (!$movido) {
        return ['exito' => false, 'error' => 'No se pudo almacenar el documento en el servidor.'];
    }

    return [
        'exito'           => true,
        'ruta'            => $rutaRelativa,
        'nombre_original' => $nombreOriginal,
        'tamanio_bytes'   => (int)$archivo['size'],
        'formato'         => 'PDF',
        'error'           => null
    ];
}

/**
 * Elimina de manera segura un documento físico subido.
 * Solo permite borrar archivos ubicados dentro de la carpeta uploads/.
 *
 * @param string $rutaRelativa
 * @return bool
 */
function eliminar_documento(string $rutaRelativa): bool
{
    // Prevención estricta de Path Traversal
    if (strpos($rutaRelativa, '..') !== false) {
        return false;
    }

    // Solo se permite eliminar archivos subidos en uploads/, nunca los de documentos/ institucionales
    if (!str_starts_with($rutaRelativa, 'uploads/')) {
        return false;
    }

    $rutaAbsoluta = dirname(__DIR__) . '/' . ltrim($rutaRelativa, '/');
    if (file_exists($rutaAbsoluta) && is_file($rutaAbsoluta)) {
        return unlink($rutaAbsoluta);
    }

    return false;
}
