<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

/* =========================================
VALIDAR SESIÓN
========================================= */

if(!isset($_SESSION['usuario_id'])){

    echo json_encode([
        'auth' => false,
        'perfil' => null
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

    SELECT
        id,
        id_usuario,
        telefono,
        correo_personal,
        sexo,
        DATE_FORMAT(fecha_nacimiento, '%Y-%m-%d') AS fecha_nacimiento,
        estado,
        universidad,
        especialidad,
        semestre,
        foto,
        biografia,
        fecha_registro

    FROM imssight.usuarios_perfil

    WHERE id_usuario = ?

    LIMIT 1

";

$stmt =
    $pdo->prepare($sql);

$stmt->execute([
    $id_usuario
]);

$perfil =
    $stmt->fetch(PDO::FETCH_ASSOC);

/* =========================================
RESPUESTA
========================================= */

echo json_encode([
    'auth' => true,
    'perfil' => $perfil ?: null
]);