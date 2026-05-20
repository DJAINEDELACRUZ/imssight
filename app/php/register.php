<?php

header('Content-Type: application/json');

require 'conn.php';

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$nombre = $data['nombre'];
$matricula = $data['matricula'];
$password = $data['password'];

if(
    empty($nombre) ||
    empty($matricula) ||
    empty($password)
){

    echo json_encode([

        'success' => false,
        'message' => 'Completa todos los campos'

    ]);

    exit;

}

try{

    // Verificar si ya existe

    $stmt = $pdo->prepare("

        SELECT id
        FROM imssight.usuarios
        WHERE matricula = ?

    ");

    $stmt->execute([$matricula]);

    if($stmt->fetch()){

        echo json_encode([

            'success' => false,
            'message' => 'La matrícula ya existe'

        ]);

        exit;

    }

    // Encriptar contraseña

    $passwordHash = password_hash(

        $password,
        PASSWORD_DEFAULT

    );

    // Insertar usuario

    $stmt = $pdo->prepare("

        INSERT INTO imssight.usuarios
        (

            nombre,
            matricula,
            password,
            rol,
            activo

        )

        VALUES
        (

            ?,
            ?,
            ?,
            'usuario',
            1

        )

    ");

    $stmt->execute([

        $nombre,
        $matricula,
        $passwordHash

    ]);

    echo json_encode([

        'success' => true

    ]);

}catch(PDOException $e){

    echo json_encode([

        'success' => false,
        'message' => $e->getMessage()

    ]);

}