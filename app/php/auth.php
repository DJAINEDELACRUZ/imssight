<?php

session_start();

header('Content-Type: application/json');

if(

    isset($_SESSION['usuario_id'])

){

    echo json_encode([

        'auth' => true,

        'usuario' => [

            'id' =>
                $_SESSION['usuario_id'],

            'nombre' =>
                $_SESSION['usuario_nombre'],

            'rol' =>
                $_SESSION['rol']

        ]

    ]);

} else {

    echo json_encode([

        'auth' => false

    ]);

}