<?php

header('Content-Type: application/json');

require 'conn.php';

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$sql = "

    INSERT INTO imssight.temas
    (
        id_especialidad,
        titulo,
        descripcion,
        imagen
    )

    VALUES
    (
        :id_especialidad,
        :titulo,
        :descripcion,
        :imagen
    )

";

$stmt = $pdo->prepare($sql);

$stmt->execute([

    ':id_especialidad' =>
        $data['id_especialidad'],

    ':titulo' =>
        $data['titulo'],

    ':descripcion' =>
        $data['descripcion'],

    ':imagen' =>
        $data['imagen']

]);

echo json_encode([

    'ok' => true,
    'mensaje' => 'Tema guardado 😄'

]);