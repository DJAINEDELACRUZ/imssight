<?php

header('Content-Type: application/json');

require 'conn.php';

$id = $_GET['id'];

$sql = "

    SELECT
        t.*,
        COUNT(DISTINCT c.id) AS total_casos

    FROM imssight.temas t

    LEFT JOIN imssight.casos_clinicos c
        ON c.id_tema = t.id
        AND c.activo = 1

    WHERE t.id_especialidad = :id

    GROUP BY t.id, t.id_especialidad, t.titulo, t.descripcion, t.imagen

    ORDER BY t.id

";

$stmt = $pdo->prepare($sql);

$stmt->execute([

    ':id' => $id

]);

echo json_encode(
    $stmt->fetchAll()
);
