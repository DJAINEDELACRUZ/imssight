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
            c.titulo
        FROM imssight.casos_clinicos c
        INNER JOIN imssight.temas t
            ON t.id = c.id_tema
        INNER JOIN imssight.especialidades e
            ON e.id = t.id_especialidad
        WHERE c.id_tema = ?
          AND c.activo = 1
          AND " . condicionContenidoInterno('e') . "
        LIMIT 1

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$idTema]);

    $caso = $stmt->fetch();

    echo json_encode($caso);

} catch(Exception $e){

    echo json_encode([
        'error' => $e->getMessage()
    ]);

}
