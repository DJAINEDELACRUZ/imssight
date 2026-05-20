<?php

header('Content-Type: application/json');

require 'conn.php';

try {

    $sql = "
        SELECT 
            id,
            nombre,
            icono,
            color
        FROM imssight.especialidades
        WHERE activo = 1
        ORDER BY id
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