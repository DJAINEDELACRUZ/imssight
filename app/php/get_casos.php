<?php

header('Content-Type: application/json');

require 'conn.php';
require 'content_visibility.php';

asegurarColumnasVisibilidadContenido($pdo);

$idTema = $_GET['id'] ?? 0;

try {

    $sql = "

        SELECT

            c.id,
            c.titulo,
            c.descripcion,
            c.portada

        FROM imssight.casos_clinicos c
        INNER JOIN imssight.temas t
            ON t.id = c.id_tema
        INNER JOIN imssight.especialidades e
            ON e.id = t.id_especialidad

        WHERE c.id_tema = ?
          AND c.activo = 1
          AND " . condicionContenidoInterno('e') . "

        ORDER BY c.id

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
