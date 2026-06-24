<?php

header('Content-Type: application/json');

require 'conn.php';

try {
    $incluirInactivas =
        isset($_GET['include_inactive'])
        && $_GET['include_inactive'] === '1';

    $where =
        $incluirInactivas
            ? ''
            : 'WHERE e.activo = 1';

    $sql = "
        SELECT 
            e.id,
            e.nombre,
            e.icono,
            e.color,
            e.activo,
            COUNT(DISTINCT t.id) AS total_temas,
            COUNT(DISTINCT c.id) AS total_casos
        FROM imssight.especialidades e
        LEFT JOIN imssight.temas t
            ON t.id_especialidad = e.id
        LEFT JOIN imssight.casos_clinicos c
            ON c.id_tema = t.id
            AND c.activo = 1
        $where
        GROUP BY e.id, e.nombre, e.icono, e.color, e.activo
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
