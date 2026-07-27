<?php

header('Content-Type: application/json');

require 'conn.php';
require 'content_visibility.php';

asegurarColumnasVisibilidadContenido($pdo);

try {

    $id = $_GET['id'];

    $sql = "

        SELECT
            id,
            nombre,
            icono,
            color

        FROM imssight.especialidades e

        WHERE e.id = :id
          AND " . condicionContenidoInterno('e') . "

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
