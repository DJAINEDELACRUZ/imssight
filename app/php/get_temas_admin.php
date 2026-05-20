<?php

header('Content-Type: application/json');

require 'conn.php';

$id = $_GET['id'];

$sql = "

    SELECT *

    FROM imssight.temas

    WHERE id_especialidad = :id

    ORDER BY id

";

$stmt = $pdo->prepare($sql);

$stmt->execute([

    ':id' => $id

]);

echo json_encode(
    $stmt->fetchAll()
);