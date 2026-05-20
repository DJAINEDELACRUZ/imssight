<?php

header('Content-Type: application/json');

require 'conn.php';

$data = json_decode(
    file_get_contents("php://input"),
    true
);

try {

    $sql = "

        UPDATE imssight.especialidades

        SET

            nombre = :nombre,
            icono = :icono,
            color = :color,
            activo = :activo

        WHERE id = :id

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([

        ':id' => $data['id'],
        ':nombre' => $data['nombre'],
        ':icono' => $data['icono'],
        ':color' => $data['color'],
        ':activo' => $data['activo']

    ]);

    echo json_encode([

        'ok' => true,
        'mensaje' => 'Especialidad actualizada 😄'

    ]);

} catch(Exception $e) {

    echo json_encode([

        'ok' => false,
        'mensaje' => $e->getMessage()

    ]);

}