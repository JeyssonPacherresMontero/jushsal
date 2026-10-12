<?php
/**
 * JUSHSAL - Suite de Validación de Tarea T3 (Fase 2)
 * Valida consultas públicas, filtros de documentos, renderizado en index.php y documentos.php.
 */

define('JUSHSAL_INIT', true);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/modelos/documentos.php';

echo "=== INICIANDO VALIDACIÓN DE TAREA T3 (FASE 2) ===\n\n";

$pruebasPasadas = 0;
$pruebasTotal = 0;

function afirmar($condicion, $mensaje) {
    global $pruebasPasadas, $pruebasTotal;
    $pruebasTotal++;
    if ($condicion) {
        $pruebasPasadas++;
        echo " [OK] " . $mensaje . "\n";
    } else {
        echo " [FALLO] " . $mensaje . "\n";
    }
}

// 1. Probar documentos_listar_publicos() sin filtros
$todos = documentos_listar_publicos();
afirmar(count($todos) >= 9, "documentos_listar_publicos() retorna al menos 9 documentos activos");

// 2. Probar filtro por categoría
$soloLeyes = documentos_listar_publicos(1); // Categoría 1: Leyes y Decretos
afirmar(!empty($soloLeyes), "Filtro por categoría retorna resultados");
$todosCat1 = true;
foreach ($soloLeyes as $d) {
    if ((int)$d['categoria_id'] !== 1) {
        $todosCat1 = false;
        break;
    }
}
afirmar($todosCat1, "Todos los documentos retornados pertenecen estrictamente a la categoría 1");

// 3. Probar filtro por año fiscal
$docs2026 = documentos_listar_publicos(null, 2026);
afirmar(!empty($docs2026), "Filtro por año fiscal 2026 retorna resultados");
$todos2026 = true;
foreach ($docs2026 as $d) {
    if ((int)$d['anio'] !== 2026) {
        $todos2026 = false;
        break;
    }
}
afirmar($todos2026, "Todos los documentos retornados corresponden al año 2026");

// 4. Probar búsqueda por palabra clave
$busqTarifa = documentos_listar_publicos(null, null, 'Tarifa');
afirmar(!empty($busqTarifa), "Búsqueda por palabra clave 'Tarifa' retorna resultados");
$coincide = false;
foreach ($busqTarifa as $d) {
    if (stripos($d['titulo'], 'Tarifa') !== false || stripos($d['descripcion'] ?? '', 'Tarifa') !== false) {
        $coincide = true;
        break;
    }
}
afirmar($coincide, "Los resultados contienen la palabra clave buscada");

// 5. Probar documentos_listar_destacados(6)
$destacados = documentos_listar_destacados(6);
afirmar(!empty($destacados) && count($destacados) <= 6, "documentos_listar_destacados(6) retorna hasta 6 documentos");
$todosDestacados = true;
foreach ($destacados as $d) {
    if ((int)$d['es_destacado'] !== 1) {
        $todosDestacados = false;
        break;
    }
}
afirmar($todosDestacados, "Todos los documentos retornados tienen flag es_destacado = 1");

// 6. Probar renderizado de portada index.php
ob_start();
include dirname(__DIR__) . '/index.php';
$htmlIndex = ob_get_clean();

afirmar(strpos($htmlIndex, 'documentos.php') !== false, "index.php contiene enlace hacia documentos.php");
afirmar(strpos($htmlIndex, 'Tarifa de Agua 2026') !== false || strpos($htmlIndex, 'Ley de Recursos Hídricos') !== false, "index.php renderiza documentos destacados desde BD");

// 7. Probar renderizado de documentos.php
ob_start();
include dirname(__DIR__) . '/documentos.php';
$htmlDocumentos = ob_get_clean();

afirmar(strpos($htmlDocumentos, 'Documentos Institucionales') !== false, "documentos.php renderiza cabecera institucional");
afirmar(strpos($htmlDocumentos, 'Todas las categorías') !== false, "documentos.php incluye selector de categorías");
afirmar(strpos($htmlDocumentos, 'name="q"') !== false, "documentos.php incluye formulario de búsqueda");
afirmar(strpos($htmlDocumentos, 'target="_blank"') !== false, "documentos.php incluye enlaces seguros con target='_blank'");

// 8. Privacidad estricta en HTML público
afirmar(strpos($htmlDocumentos, 'DNI') === false, "documentos.php no expone datos de DNI");

echo "\n--- RESUMEN DE PRUEBAS ---\n";
echo "Pruebas pasadas: {$pruebasPasadas} / {$pruebasTotal}\n";

if ($pruebasPasadas === $pruebasTotal) {
    echo ">>> TODOS LOS TESTS DE T3 (FASE 2) PASARON SATISFACTORIAMENTE <<<\n";
    exit(0);
} else {
    echo ">>> HUBO ERRORES EN LA VALIDACIÓN <<<\n";
    exit(1);
}
