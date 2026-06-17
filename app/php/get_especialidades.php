<?php

header('Content-Type: application/json');

require 'conn.php';

try {

    $sql = "
        SELECT 
            e.id,
            e.nombre,
            e.icono,
            e.color,
            COUNT(DISTINCT c.id) AS total_casos
        FROM imssight.especialidades e
        LEFT JOIN imssight.temas t
            ON t.id_especialidad = e.id
        LEFT JOIN imssight.casos_clinicos c
            ON c.id_tema = t.id
            AND c.activo = 1
        WHERE e.activo = 1
        GROUP BY e.id, e.nombre, e.icono, e.color
        ORDER BY e.nombre ASC
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
