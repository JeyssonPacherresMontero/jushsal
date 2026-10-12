<?php
define('JUSHSAL_INIT', true);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';

$db = obtener_db();
$stmt = $db->query('SELECT c.*, cp.nombre_completo as presidente, cp.periodo_inicio, cp.periodo_fin, cp.celular 
                    FROM comisiones c 
                    LEFT JOIN comision_presidentes cp ON c.id = cp.comision_id 
                    ORDER BY c.numero ASC');
$rows = $stmt->fetchAll();
echo "Total comisiones: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo sprintf("[%02d] %-30s | Presidente: %s (Periodo: %s-%s)\n", 
        $r['numero'], 
        $r['nombre'], 
        $r['presidente'] ?? 'Sin asignar',
        $r['periodo_inicio'] ?? '-',
        $r['periodo_fin'] ?? '-'
    );
}
