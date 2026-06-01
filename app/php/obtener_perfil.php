<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';
require 'perfil_utils.php';

asegurarColumnasPerfilPublico($pdo);

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
    (int)$_SESSION['usuario_id'];

$id_perfil_solicitado =
    (int)($_GET['id'] ?? 0);

$es_perfil_publico =
    $id_perfil_solicitado > 0
    && $id_perfil_solicitado !== $id_usuario;

$id_consulta =
    $es_perfil_publico
        ? $id_perfil_solicitado
        : $id_usuario;

$usuarioStmt =
    $pdo->prepare("
        SELECT id, nombre, matricula, rol
        FROM imssight.usuarios
        WHERE id = ?
        AND activo = 1
        LIMIT 1
    ");

$usuarioStmt->execute([$id_consulta]);

$usuario =
    $usuarioStmt->fetch(PDO::FETCH_ASSOC);

if(!$usuario){

    echo json_encode([
        'auth' => true,
        'encontrado' => false,
        'perfil' => null
    ]);

    exit;

}

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
        frase_perfil,
        fecha_registro

    FROM imssight.usuarios_perfil

    WHERE id_usuario = ?

    LIMIT 1

";

$stmt =
    $pdo->prepare($sql);

$stmt->execute([
    $id_consulta
]);

$perfil =
    $stmt->fetch(PDO::FETCH_ASSOC);

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

    $dia =
        (int)date('j', $timestamp);

    $mes =
        (int)date('n', $timestamp);

    return $dia . ' de ' . ($meses[$mes] ?? '');

}

if($perfil && empty($perfil['cumpleanos_publico'])){
    $perfil['cumpleanos_publico'] =
        cumpleañosPublicoDesdeFecha($perfil['fecha_nacimiento'] ?? '');
}

if($es_perfil_publico){

    if($perfil && (int)($perfil['perfil_publico'] ?? 1) !== 1){

        echo json_encode([
            'auth' => true,
            'encontrado' => true,
            'modo' => 'publico',
            'usuario' => $usuario,
            'perfil_publico' => false,
            'perfil' => null
        ]);

        exit;

    }

    if(!$perfil){
        $perfil = [];
    }

    if((int)($perfil['mostrar_correo'] ?? 0) !== 1){
        $perfil['correo_personal'] = null;
    }

    if((int)($perfil['mostrar_telefono'] ?? 0) !== 1){
        $perfil['telefono'] = null;
    }

    if((int)($perfil['mostrar_estado'] ?? 1) !== 1){
        $perfil['estado'] = null;
    }

    if((int)($perfil['mostrar_biografia'] ?? 1) !== 1){
        $perfil['biografia'] = null;
    }

    $perfil['sexo'] = null;
    $perfil['fecha_nacimiento'] = null;

}

/* =========================================
RESPUESTA
========================================= */

echo json_encode([
    'auth' => true,
    'encontrado' => true,
    'modo' => $es_perfil_publico ? 'publico' : 'propio',
    'usuario' => $usuario,
    'perfil_publico' => !$es_perfil_publico || (bool)($perfil['perfil_publico'] ?? true),
    'perfil' => $perfil ?: null
]);
