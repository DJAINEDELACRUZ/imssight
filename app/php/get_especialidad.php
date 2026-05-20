<?php

header('Content-Type: application/json');

require 'conn.php';

try {

    $id = $_GET['id'];

    $sql = "

        SELECT
            id,
            nombre,
            icono,
            color

        FROM imssight.especialidades

        WHERE id = :id

        LIMIT 1

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':id', $id);

    $stmt->execute();

    $especialidad = $stmt->fetch();

    echo json_encode($especialidad);

} catch (Exception $e) {

    echo json_encode([
        "error" => $e->getMessage()
    ]);
}