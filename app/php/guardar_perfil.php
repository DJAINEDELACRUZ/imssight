<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

/* =========================================
VALIDAR SESION
========================================= */

if(!isset($_SESSION['usuario_id'])){

    echo json_encode([
        'success' => false,
        'message' => 'Sesión no válida'
    ]);

    exit;

}

$id_usuario =
    $_SESSION['usuario_id'];

/* =========================================
RECIBIR DATOS
========================================= */

$telefono =
    $_POST['telefono'] ?? '';

$correo_personal =
    $_POST['correo_personal'] ?? '';

$sexo =
    $_POST['sexo'] ?? '';

$fecha_nacimiento =
    $_POST['fecha_nacimiento'] ?? null;

$estado =
    $_POST['estado'] ?? '';

$universidad =
    $_POST['universidad'] ?? '';

$especialidad =
    $_POST['especialidad'] ?? '';

$semestre =
    $_POST['semestre'] ?? '';

$biografia =
    $_POST['biografia'] ?? '';

/* =========================================
VERIFICAR EXISTENCIA
========================================= */

$sql = "

    SELECT id

    FROM imssight.usuarios_perfil

    WHERE id_usuario = ?

";

$stmt = $pdo->prepare($sql);

$stmt->execute([$id_usuario]);

$existe =
    $stmt->fetch();

/* =========================================
UPDATE
========================================= */

if($existe){

    $sql = "

        UPDATE imssight.usuarios_perfil

        SET

            telefono = ?,
            correo_personal = ?,
            sexo = ?,
            fecha_nacimiento = ?,
            estado = ?,
            universidad = ?,
            especialidad = ?,
            semestre = ?,
            biografia = ?

        WHERE id_usuario = ?

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([

        $telefono,
        $correo_personal,
        $sexo,
        $fecha_nacimiento,
        $estado,
        $universidad,
        $especialidad,
        $semestre,
        $biografia,
        $id_usuario

    ]);

}else{

/* =========================================
INSERT
========================================= */

    $sql = "

        INSERT INTO imssight.usuarios_perfil(

            id_usuario,
            telefono,
            correo_personal,
            sexo,
            fecha_nacimiento,
            estado,
            universidad,
            especialidad,
            semestre,
            biografia

        )

        VALUES(?,?,?,?,?,?,?,?,?,?)

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([

        $id_usuario,
        $telefono,
        $correo_personal,
        $sexo,
        $fecha_nacimiento,
        $estado,
        $universidad,
        $especialidad,
        $semestre,
        $biografia

    ]);

}

echo json_encode([

    'success' => true

]);