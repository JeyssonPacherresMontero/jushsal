<?php
/**
 * JUSHSAL - API Pública: Convocatoria Activa para Popup
 * Responde JSON con la convocatoria vigente evaluada por el servidor en tiempo real.
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/modelos/convocatorias.php';

// Cabeceras estrictas contra caché y respuesta JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$convocatoria = convocatoria_obtener_activa();

if (!$convocatoria) {
    echo json_encode([
        'activa' => false
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$imagenUrl = BASE_URL . ltrim($convocatoria['imagen'], '/');

echo json_encode([
    'activa'       => true,
    'id'           => (int)$convocatoria['id'],
    'titulo'       => $convocatoria['titulo'],
    'imagen_url'   => $imagenUrl,
    'retraso_seg'  => (int)$convocatoria['retraso_seg'],
    'duracion_seg' => (int)$convocatoria['duracion_seg'],
    'frecuencia'   => $convocatoria['frecuencia'],
    'version'      => $convocatoria['updated_at'] ?? $convocatoria['created_at']
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
