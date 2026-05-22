<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

/* =========================================
VALIDAR SESION
========================================= */

if(!isset($_SESSION['usuario'])){

    echo json_encode([

        'success' => false,
        'auth' => false

    ]);

    exit;

}

/* =========================================
USUARIO
========================================= */

$id_usuario =
    $_SESSION['usuario']['id'];

/* =========================================
BUSCAR PERFIL
========================================= */

$sql = "

    SELECT *

    FROM imssight.usuarios_perfil

    WHERE id_usuario = ?

    LIMIT 1

";

$stmt = $pdo->prepare($sql);

$stmt->execute([$id_usuario]);

$perfil =
    $stmt->fetch(PDO::FETCH_ASSOC);

/* =========================================
SIN PERFIL
========================================= */

if(!$perfil){

    echo json_encode([

        'success' => true,
        'perfil' => null

    ]);

    exit;

}

/* =========================================
RESPUESTA
========================================= */

echo json_encode([

    'success' => true,
    'perfil' => [

        'telefono' =>
            $perfil['telefono'],

        'correo_personal' =>
            $perfil['correo_personal'],

        'sexo' =>
            $perfil['sexo'],

        'fecha_nacimiento' =>
            $perfil['fecha_nacimiento'],

        'estado' =>
            $perfil['estado'],

        'universidad' =>
            $perfil['universidad'],

        'especialidad' =>
            $perfil['especialidad'],

        'semestre' =>
            $perfil['semestre'],

        'biografia' =>
            $perfil['biografia']

    ]

]);