<?php

header('Content-Type: application/json');

require 'conn.php';
require 'content_visibility.php';

asegurarColumnasVisibilidadContenido($pdo);

$id = $_GET['id'] ?? 0;

try {

    $sql = "
        SELECT
            t.id,
            t.titulo,
            t.descripcion,
            t.imagen
        FROM imssight.temas t
        INNER JOIN imssight.especialidades e
            ON e.id = t.id_especialidad
        WHERE t.id_especialidad = ?
          AND " . condicionContenidoInterno('e') . "
        ORDER BY t.id
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
