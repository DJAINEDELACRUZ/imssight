<?php

header('Content-Type: application/json');

require 'conn.php';

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$sql = "

    UPDATE imssight.temas

    SET

        titulo = :titulo,
        descripcion = :descripcion,
        imagen = :imagen

    WHERE id = :id

";

$stmt = $pdo->prepare($sql);

$stmt->execute([

    ':id' =>
        $data['id'],

    ':titulo' =>
        $data['titulo'],

    ':descripcion' =>
        $data['descripcion'],

    ':imagen' =>
        $data['imagen']

]);

echo json_encode([

    'ok' => true,
    'mensaje' => 'Tema actualizado 😄'

]);