<?php

header('Content-Type: application/json');

require 'conn.php';

$q = $_GET['q'] ?? '';

if(!$q){

    echo json_encode([]);

    exit;

}

$sql = "

SELECT *

FROM search_index

WHERE

    titulo LIKE ?

    OR contenido LIKE ?

    OR descripcion LIKE ?

LIMIT 50

";

$buscar = "%$q%";

$stmt = $pdo->prepare($sql);

$stmt->execute([

    $buscar,
    $buscar,
    $buscar

]);

$resultados = $stmt->fetchAll();

echo json_encode($resultados);