<?php

header('Content-Type: application/json');

require 'conn.php';

/*
|--------------------------------------------------------------------------
| GET
|--------------------------------------------------------------------------
*/

if($_SERVER['REQUEST_METHOD'] == 'GET'){

    $idCaso =
        $_GET['id'] ?? null;

    $sql = "

        SELECT

            e.*,
            c.titulo AS caso_titulo

        FROM examenes e

        LEFT JOIN casos_clinicos c
        ON c.id = e.id_caso

        WHERE e.id_caso = ?

        ORDER BY e.id DESC

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$idCaso]);

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

    $stmt = $pdo->prepare("

        INSERT INTO examenes(

            id_caso,
            titulo,
            descripcion,
            activo

        )

        VALUES(

            ?,
            ?,
            ?,
            1

        )

    ");

    $ok = $stmt->execute([

        $data['id_caso'],
        $data['titulo'],
        $data['descripcion']

    ]);

    echo json_encode([

        'success' => $ok,
        'mensaje' => 'Examen creado'

    ]);

    exit;

}

/*
|--------------------------------------------------------------------------
| ACTUALIZAR
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("

    UPDATE examenes

    SET

        titulo = ?,
        descripcion = ?,
        activo = ?

    WHERE id = ?

");

$ok = $stmt->execute([

    $data['titulo'],
    $data['descripcion'],
    $data['activo'],
    $data['id']

]);

echo json_encode([

    'success' => $ok,
    'mensaje' => 'Examen actualizado'

]);