<?php

header('Content-Type: application/json');

require 'conn.php';

try {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    $id_caso = $data['id_caso'];
    $orden = $data['orden'];
    $tipo = $data['tipo'];
    $titulo = $data['titulo'];
    $contenido = $data['contenido'];
    $multimedia = $data['multimedia'];

    /*
    |--------------------------------------------------------------------------
    | NUEVOS CAMPOS
    |--------------------------------------------------------------------------
    */

    $pregunta_json =
        $data['pregunta_json'] ?? null;

    $respuesta_correcta =
        $data['respuesta_correcta'] ?? null;

        $sql = "

            INSERT INTO escenas
            (

                id_caso,
                orden,
                tipo,
                titulo,
                contenido,
                multimedia,

                respuesta_correcta,
                evaluable

            )

            VALUES
            (

                ?,
                ?,
                ?,
                ?,
                ?,
                ?,

                ?,
                ?

            )

        ";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([

        $id_caso,
        $orden,
        $tipo,
        $titulo,
        $contenido,
        $multimedia,

        $data['respuesta_correcta'],
        $data['evaluable']

    ]);

    echo json_encode([

        'ok' => true,

        'mensaje' => 'Escena guardada correctamente 😄'

    ]);

} catch(Exception $e){

    echo json_encode([

        'ok' => false,

        'mensaje' => $e->getMessage()

    ]);

}