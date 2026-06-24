<?php

header('Content-Type: application/json');

require 'conn.php';

/*
|--------------------------------------------------------------------------
| GET
|--------------------------------------------------------------------------
*/

if($_SERVER['REQUEST_METHOD'] == 'GET'){

    $idExamen =
        $_GET['id'] ?? null;

    $sql = "

        SELECT *

        FROM examen_preguntas

        WHERE id_examen = ?

        ORDER BY id ASC

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$idExamen]);

    echo json_encode(

        $stmt->fetchAll(PDO::FETCH_ASSOC)

    );

    exit;

}

/*
|--------------------------------------------------------------------------
| POST
|--------------------------------------------------------------------------
*/

$data = json_decode(

    file_get_contents("php://input"),
    true

);

/*
|--------------------------------------------------------------------------
| INSERTAR
|--------------------------------------------------------------------------
*/

if(!isset($data['id'])){

    $stmtOrden = $pdo->prepare("

        SELECT
            COALESCE(MAX(orden_pregunta),0) + 1
            AS siguiente

        FROM examen_preguntas

        WHERE id_examen = ?

    ");

    $stmtOrden->execute([

        $data['id_examen']

    ]);

    $orden =
        $stmtOrden->fetch()['siguiente'];

    $stmt = $pdo->prepare("

        INSERT INTO examen_preguntas(

            id_examen,
            pregunta,
            opcion_a,
            opcion_b,
            opcion_c,
            opcion_d,
            respuesta_correcta,
            explicacion,
            dificultad,
            orden_pregunta

        )

        VALUES(

            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?

        )

    ");

    $ok = $stmt->execute([

        $data['id_examen'],
        $data['pregunta'],
        $data['opcion_a'],
        $data['opcion_b'],
        $data['opcion_c'],
        $data['opcion_d'],
        $data['respuesta_correcta'],
        $data['explicacion'],
        $data['dificultad'],
        $orden

    ]);

    echo json_encode([

        'success' => $ok,
        'mensaje' => 'Pregunta creada'

    ]);

    exit;

}

/*
|--------------------------------------------------------------------------
| ACTUALIZAR
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("

    UPDATE examen_preguntas

    SET

        pregunta = ?,
        opcion_a = ?,
        opcion_b = ?,
        opcion_c = ?,
        opcion_d = ?,
        respuesta_correcta = ?,
        explicacion = ?,
        dificultad = ?

    WHERE id = ?

");

$ok = $stmt->execute([

    $data['pregunta'],
    $data['opcion_a'],
    $data['opcion_b'],
    $data['opcion_c'],
    $data['opcion_d'],
    $data['respuesta_correcta'],
    $data['explicacion'],
    $data['dificultad'],
    $data['id']

]);

echo json_encode([

    'success' => $ok,
    'mensaje' => 'Pregunta actualizada'

]);
