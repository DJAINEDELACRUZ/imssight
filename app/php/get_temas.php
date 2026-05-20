<?php

header('Content-Type: application/json');

require 'conn.php';

$id = $_GET['id'] ?? 0;

try {

    $sql = "
        SELECT
            id,
            titulo,
            descripcion,
            imagen
        FROM temas
        WHERE id_especialidad = ?
        ORDER BY id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$id]);

    $temas = $stmt->fetchAll();

    echo json_encode($temas);

} catch (Exception $e) {

    echo json_encode([
        "error" => $e->getMessage()
    ]);
}