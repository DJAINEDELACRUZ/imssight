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

        UPDATE imssight.especialidades

        SET

            nombre = :nombre,
            icono = :icono,
            color = :color,
            activo = :activo,
            visibilidad = :visibilidad,
            slug_publico = :slug_publico

        WHERE id = :id

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([

        ':id' => $data['id'],
        ':nombre' => $data['nombre'],
        ':icono' => $data['icono'],
        ':color' => $data['color'],
        ':activo' => $data['activo'],
        ':visibilidad' => $visibilidad,
        ':slug_publico' => $slugPublico ?: null

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
