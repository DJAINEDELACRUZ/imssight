<?php

session_start();

header('Content-Type: application/json');

if(

    isset($_SESSION['usuario_id'])

){

    require_once 'conn.php';
    require_once 'recorrido_utils.php';

    asegurarColumnaRecorridoIndex($pdo);

    $stmt =
        $pdo->prepare("
            SELECT recorrido_index_visto
            FROM imssight.usuarios
            WHERE id = ?
            LIMIT 1
        ");

    $stmt->execute([
        $_SESSION['usuario_id']
    ]);

    $preferencias =
        $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'recorrido_index_visto' => 0
        ];

    echo json_encode([

        'auth' => true,

        'usuario' => [

            'id' =>
                $_SESSION['usuario_id'],

            'nombre' =>
                $_SESSION['usuario_nombre'],

            'rol' =>
                $_SESSION['rol'],

            'recorrido_index_visto' =>
                (int)$preferencias['recorrido_index_visto']

        ]

    ]);

} else {

    echo json_encode([

        'auth' => false

    ]);

}
