<?php

header('Content-Type: application/json');

require 'conn.php';

$data = json_decode(
    file_get_contents("php://input"),
    true
);

try {

    $sql = "

        INSERT INTO imssight.especialidades
        (
            nombre,
            icono,
            color
        )

        VALUES
        (
            :nombre,
            :icono,
            :color
        )

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([

        ':nombre' => $data['nombre'],
        ':icono' => $data['icono'],
        ':color' => $data['color']

    ]);

    echo json_encode([

        'ok' => true,
        'mensaje' => 'Especialidad guardada 😄'

    ]);

} catch(Exception $e) {

    echo json_encode([

        'ok' => false,
        'mensaje' => $e->getMessage()

    ]);

}