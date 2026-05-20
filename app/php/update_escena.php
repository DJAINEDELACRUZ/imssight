<?php

header('Content-Type: application/json');

require 'conn.php';

try {

    $data = json_decode(

        file_get_contents("php://input"),

        true

    );

    $id = $data['id'];

    $orden = $data['orden'];

    $tipo = $data['tipo'];

    $titulo = $data['titulo'];

    $contenido = $data['contenido'];

    $multimedia = $data['multimedia'];

    $sql = "

        UPDATE escenas

        SET

            orden = ?,
            tipo = ?,
            titulo = ?,
            contenido = ?,
            multimedia = ?

        WHERE id = ?

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([

        $orden,
        $tipo,
        $titulo,
        $contenido,
        $multimedia,
        $id

    ]);

    echo json_encode([

        'ok' => true,

        'mensaje' => 'Escena actualizada 😄'

    ]);

} catch(Exception $e){

    echo json_encode([

        'ok' => false,

        'mensaje' => $e->getMessage()

    ]);

}