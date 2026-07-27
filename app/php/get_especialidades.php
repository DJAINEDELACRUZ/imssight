<?php

header('Content-Type: application/json');

require 'conn.php';
require 'content_visibility.php';

try {
    asegurarColumnasVisibilidadContenido($pdo);

    $incluirInactivas =
        isset($_GET['include_inactive'])
        && $_GET['include_inactive'] === '1';

    $where =
        $incluirInactivas
            ? ''
            : 'WHERE ' . condicionContenidoInterno('e');

    $sql = "
        SELECT 
            e.id,
            e.nombre,
            e.icono,
            e.color,
            e.activo,
            e.visibilidad,
            e.slug_publico,
            COUNT(DISTINCT t.id) AS total_temas,
            COUNT(DISTINCT c.id) AS total_casos
        FROM imssight.especialidades e
        LEFT JOIN imssight.temas t
            ON t.id_especialidad = e.id
        LEFT JOIN imssight.casos_clinicos c
            ON c.id_tema = t.id
            AND c.activo = 1
        $where
        GROUP BY e.id, e.nombre, e.icono, e.color, e.activo, e.visibilidad, e.slug_publico
        ORDER BY e.activo DESC, e.nombre ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $especialidades = $stmt->fetchAll();

    echo json_encode($especialidades);

} catch (Exception $e) {

    echo json_encode([
        "error" => $e->getMessage()
    ]);
}
