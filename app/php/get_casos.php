<?php

header('Content-Type: application/json');

require 'conn.php';

$idTema = $_GET['id'] ?? 0;

try {

    $sql = "

        SELECT

            id,
            titulo,
            descripcion

        FROM casos_clinicos

        WHERE id_tema = ?

        ORDER BY id

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$idTema]);

    $casos = $stmt->fetchAll();

    echo json_encode($casos);

} catch(Exception $e){

    echo json_encode([

        'error' => $e->getMessage()

    ]);

}