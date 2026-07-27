<?php

header('Content-Type: application/json');

require 'conn.php';
require 'content_visibility.php';

asegurarColumnasVisibilidadContenido($pdo);

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$visibilidad = normalizarVisibilidadContenido($data['visibilidad'] ?? 'interna');
$slugPublico = normalizarSlugPublico($data['slug_publico'] ?? '');

try {

    $sql = "

        INSERT INTO imssight.especialidades
        (
            nombre,
            icono,
            color,
            visibilidad,
            slug_publico
        )

        VALUES
        (
            :nombre,
            :icono,
            :color,
            :visibilidad,
            :slug_publico
        )

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([

        ':nombre' => $data['nombre'],
        ':icono' => $data['icono'],
        ':color' => $data['color'],
        ':visibilidad' => $visibilidad,
        ':slug_publico' => $slugPublico ?: null

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
