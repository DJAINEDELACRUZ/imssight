<?php

header('Content-Type: application/json');

require 'conn.php';

try {

    $id_caso = $_GET['id_caso'] ?? 0;

    $sql = "

        SELECT
            id,
            id_caso,
            seccion,
            contenido
        FROM imssight.perlas_clinicas_caso
        WHERE id_caso = ?
        ORDER BY orden ASC

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $id_caso
    ]);

    echo json_encode(
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );

} catch(Exception $e){

    echo json_encode([]);

}