<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

/* =========================================
VALIDAR SESION
========================================= */

if(!isset($_SESSION['usuario_id'])){

    echo json_encode([

        'auth' => false

    ]);

    exit;

}

/* =========================================
USUARIO
========================================= */

$id_usuario =
    $_SESSION['usuario_id'];

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
RESPUESTA
========================================= */

if($perfil){

    echo json_encode([

        'auth' => true,

        'perfil_completo' => true

    ]);

}else{

    echo json_encode([

        'auth' => true,

        'perfil_completo' => false

    ]);

}