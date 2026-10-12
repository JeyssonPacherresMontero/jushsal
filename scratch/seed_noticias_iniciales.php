<?php
/**
 * Migra las 4 noticias iniciales de la maqueta a MySQL si la tabla está vacía.
 */

define('JUSHSAL_INIT', true);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/modelos/noticias.php';

$db = obtener_db();

$noticiasIniciales = [
    [
        'titulo' => 'Inscripción de nuevos usuarios para el presente año agrícola',
        'cat'    => 'comunicado',
        'fecha'  => '2026-09-10',
        'resumen'=> 'La Junta de Usuarios San Lorenzo comunica el inicio del proceso de inscripción y actualización de datos de los usuarios del sector hidráulico para el nuevo año agrícola.',
        'contenido' => "La Junta de Usuarios del Sector Hidráulico Menor San Lorenzo comunica a todos los agricultores y regantes que ha iniciado el proceso oficial de inscripción y actualización de datos para el presente año agrícola.\n\nLos interesados deberán apersonarse a la Sede Administrativa en La Cruceta o coordinar con su respectiva comisión de usuarios portando los documentos acreditativos correspondientes.",
        'img'    => 'assets/images/repartidor.jpg',
        'dest'   => 1
    ],
    [
        'titulo' => 'Mantenimiento preventivo de la red de canales de riego',
        'cat'    => 'gestion',
        'fecha'  => '2026-08-28',
        'resumen'=> 'Se realizarán trabajos de limpieza y mantenimiento de los canales principales, programados por comisión de usuarios. Coordina con tu comité el cronograma correspondiente.',
        'contenido' => "En el marco del plan de operación y mantenimiento, cuadrillas técnicas de la Junta vienen realizando labores de descolmatación y reforzamiento en los canales principales del sector hidráulico.\n\nSe exhorta a los usuarios a respetar los turnos de corte y distribución programados.",
        'img'    => 'assets/images/valvula.jpg',
        'dest'   => 0
    ],
    [
        'titulo' => 'Asamblea general ordinaria de usuarios convocada',
        'cat'    => 'actividad',
        'fecha'  => '2026-08-05',
        'resumen'=> 'Convocamos a todos los usuarios a la asamblea general ordinaria donde se informarán los acuerdos de gestión y el plan de inversiones del sector.',
        'contenido' => "El Consejo Directivo de la Junta de Usuarios San Lorenzo convoca a todos los delegados y miembros a la Asamblea General Ordinaria en la sede central.\n\nEn la asamblea se expondrá el balance semestral y los avances en la infraestructura hídrica.",
        'img'    => 'assets/images/asamblea.jpg',
        'dest'   => 0
    ],
    [
        'titulo' => 'Campaña de concientización sobre uso eficiente del agua',
        'cat'    => 'campana',
        'fecha'  => '2026-07-19',
        'resumen'=> 'Iniciamos una campaña de sensibilización para promover el ahorro y la distribución equitativa del recurso hídrico entre los agricultores de la zona.',
        'contenido' => "Bajo el lema 'Cuidemos cada gota', técnicos de la Junta realizan talleres informativos en diversos subsectores para capacitar a los agricultores en técnicas de riego tecnificado y uso eficiente del recurso hídrico.",
        'img'    => 'assets/images/reservorio2.jpg',
        'dest'   => 0
    ]
];

foreach ($noticiasIniciales as $n) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM noticias WHERE titulo = :t');
    $stmt->execute([':t' => $n['titulo']]);
    if ((int)$stmt->fetchColumn() === 0) {
        $stmtCat = $db->prepare('SELECT id FROM categorias_noticia WHERE slug = :s LIMIT 1');
        $stmtCat->execute([':s' => $n['cat']]);
        $catId = $stmtCat->fetchColumn() ?: 1;

        noticias_crear([
            'categoria_id'      => $catId,
            'titulo'            => $n['titulo'],
            'slug'              => noticias_generar_slug_unico($n['titulo']),
            'resumen'           => $n['resumen'],
            'contenido'         => $n['contenido'],
            'imagen_portada'    => $n['img'],
            'fecha_publicacion' => $n['fecha'],
            'estado'            => 'publicado',
            'destacada'         => $n['dest'],
            'autor_id'          => 1
        ]);
        echo "Noticia sembrada: {$n['titulo']}\n";
    } else {
        echo "Ya existe: {$n['titulo']}\n";
    }
}
