<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

if(!isset($_SESSION['usuario_id'])){

    echo json_encode([

        'success' => false,

        'message' => 'No autenticado'

    ]);

    exit;

}

$data = json_decode(

    file_get_contents("php://input"),

    true

);

$idUsuario =
    $_SESSION['usuario_id'];

$idEscena =
    $data['id_escena'];

$respuesta =
    $data['respuesta_usuario'];

$correcta =
    $data['correcta'];

try{

    $sql = "

        INSERT INTO respuestas_usuario (

            id_usuario,
            id_escena,
            respuesta_usuario,
            correcta

        ) VALUES (

            ?, ?, ?, ?

        )

    ";

    $stmt =
        $pdo->prepare($sql);

    $stmt->execute([

        $idUsuario,
        $idEscena,
        $respuesta,
        $correcta

    ]);

    echo json_encode([

        'success' => true

    ]);

} catch(Exception $e){

    echo json_encode([

        'success' => false,

        'message' => $e->getMessage()

    ]);

}