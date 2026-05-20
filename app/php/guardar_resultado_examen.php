<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

/*
|--------------------------------------------------------------------------
| VALIDAR SESIÓN
|--------------------------------------------------------------------------
*/

if(!isset($_SESSION['usuario_id'])){

    echo json_encode([
        'success' => false,
        'mensaje' => 'No autenticado'
    ]);

    exit;

}

/*
|--------------------------------------------------------------------------
| RECIBIR DATOS
|--------------------------------------------------------------------------
*/

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$idExamen =
    $data['id_examen'] ?? null;

$correctas =
    $data['correctas'] ?? 0;

$total =
    $data['total'] ?? 0;

$calificacion =
    $data['calificacion'] ?? 0;

/*
|--------------------------------------------------------------------------
| USUARIO
|--------------------------------------------------------------------------
*/

$idUsuario =
    $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| CALCULAR INTENTO
|--------------------------------------------------------------------------
*/

$stmtIntento = $pdo->prepare("

    SELECT COUNT(*) + 1 AS intento

    FROM examen_resultados

    WHERE id_usuario = ?
    AND id_examen = ?

");

$stmtIntento->execute([

    $idUsuario,
    $idExamen

]);

$intento =
    $stmtIntento->fetch()['intento'];

/*
|--------------------------------------------------------------------------
| INSERTAR RESULTADO
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("

    INSERT INTO examen_resultados(

        id_examen,
        id_usuario,
        calificacion,
        respuestas_correctas,
        total_preguntas,
        intento

    )

    VALUES(

        ?,
        ?,
        ?,
        ?,
        ?,
        ?

    )

");

$ok = $stmt->execute([

    $idExamen,
    $idUsuario,
    $calificacion,
    $correctas,
    $total,
    $intento

]);

echo json_encode([

    'success' => $ok

]);