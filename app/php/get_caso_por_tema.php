<?php

header('Content-Type: application/json');

require 'conn.php';

$idTema = $_GET['id'] ?? 0;

try {

    $sql = "

        SELECT
            id,
            titulo
        FROM imssight.casos_clinicos
        WHERE id_tema = ?
        LIMIT 1

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$idTema]);

    $caso = $stmt->fetch();

    echo json_encode($caso);

} catch(Exception $e){

    echo json_encode([
        'error' => $e->getMessage()
    ]);

}