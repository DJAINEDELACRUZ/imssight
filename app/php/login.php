<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

$data =
    json_decode(
        file_get_contents(
            "php://input"
        ),
        true
    );

$matricula =
    $data['matricula'];

$password =
    $data['password'];

$sql = "

    SELECT *

    FROM usuarios

    WHERE matricula = ?

    LIMIT 1

";

$stmt =
    $pdo->prepare($sql);

$stmt->execute([
    $matricula
]);

$usuario =
    $stmt->fetch();

if(

    $usuario &&

    password_verify(
        $password,
        $usuario['password']
    )

){

    $_SESSION['usuario_id'] =
        $usuario['id'];

    $_SESSION['usuario_nombre'] =
        $usuario['nombre'];

    $_SESSION['rol'] =
        $usuario['rol'];

    echo json_encode([

        'success' => true

    ]);

} else {

    echo json_encode([

        'success' => false,

        'message' =>
            'Credenciales incorrectas'

    ]);

}