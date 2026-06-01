<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';
require 'perfil_utils.php';

asegurarColumnasPerfilPublico($pdo);

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

$hospital =
    $_POST['hospital'] ?? '';

function cumpleañosPublicoDesdeFecha($fecha){

    if(!$fecha){
        return '';
    }

    $meses = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre'
    ];

    $timestamp =
        strtotime($fecha);

    if(!$timestamp){
        return '';
    }

    return (int)date('j', $timestamp) . ' de ' . $meses[(int)date('n', $timestamp)];

}

$cumpleanos_publico =
    cumpleañosPublicoDesdeFecha($fecha_nacimiento);

$puesto =
    $_POST['puesto'] ?? '';

$etapa_profesional =
    $_POST['etapa_profesional'] ?? '';

$intereses =
    $_POST['intereses'] ?? '';

$frase_perfil =
    $_POST['frase_perfil'] ?? '';

$perfil_publico =
    !empty($_POST['perfil_publico']) ? 1 : 0;

$mostrar_correo =
    !empty($_POST['mostrar_correo']) ? 1 : 0;

$mostrar_telefono =
    !empty($_POST['mostrar_telefono']) ? 1 : 0;

$mostrar_estado =
    !empty($_POST['mostrar_estado']) ? 1 : 0;

$mostrar_biografia =
    !empty($_POST['mostrar_biografia']) ? 1 : 0;

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
            biografia = ?,
            perfil_publico = ?,
            mostrar_correo = ?,
            mostrar_telefono = ?,
            mostrar_estado = ?,
            mostrar_biografia = ?,
            hospital = ?,
            cumpleanos_publico = ?,
            puesto = ?,
            etapa_profesional = ?,
            intereses = ?,
            frase_perfil = ?

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
        $perfil_publico,
        $mostrar_correo,
        $mostrar_telefono,
        $mostrar_estado,
        $mostrar_biografia,
        $hospital,
        $cumpleanos_publico,
        $puesto,
        $etapa_profesional,
        $intereses,
        $frase_perfil,
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
            biografia,
            perfil_publico,
            mostrar_correo,
            mostrar_telefono,
            mostrar_estado,
            mostrar_biografia,
            hospital,
            cumpleanos_publico,
            puesto,
            etapa_profesional,
            intereses,
            frase_perfil

        )

        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)

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
        $biografia,
        $perfil_publico,
        $mostrar_correo,
        $mostrar_telefono,
        $mostrar_estado,
        $mostrar_biografia,
        $hospital,
        $cumpleanos_publico,
        $puesto,
        $etapa_profesional,
        $intereses,
        $frase_perfil

    ]);

}

echo json_encode([

    'success' => true

]);
