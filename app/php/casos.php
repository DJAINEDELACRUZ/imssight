<?php

header('Content-Type: application/json');

require 'conn.php';

$method = $_SERVER['REQUEST_METHOD'];

//
// ======================================
// GET
// ======================================
//

if($method === 'GET') {

    $idTema = $_GET['id'] ?? 0;

    try {

        $sql = "

            SELECT *

            FROM casos_clinicos

            WHERE id_tema = ?

            ORDER BY id DESC

        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([$idTema]);

        $casos = $stmt->fetchAll();

        echo json_encode($casos);

    } catch(Exception $e){

        echo json_encode([

            'error' => $e->getMessage()

        ]);

    }

    exit;

}

//
// ======================================
// POST
// ======================================
//

if($method === 'POST') {

    $data = json_decode(

        file_get_contents("php://input"),

        true

    );

    //
    // UPDATE
    //

    if(isset($data['id'])) {

        try {

            $sql = "

                UPDATE casos_clinicos

                SET

                    titulo = ?,
                    descripcion = ?,
                    dificultad = ?,
                    portada = ?,
                    activo = ?

                WHERE id = ?

            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([

                $data['titulo'],
                $data['descripcion'],
                $data['dificultad'],
                $data['portada'],
                $data['activo'],
                $data['id']

            ]);

            echo json_encode([

                'mensaje' => 'Caso actualizado'

            ]);

        } catch(Exception $e){

            echo json_encode([

                'error' => $e->getMessage()

            ]);

        }

        exit;

    }

    //
    // INSERT
    //

    try {

        $sql = "

            INSERT INTO casos_clinicos (

                id_tema,
                titulo,
                descripcion,
                dificultad,
                portada

            )

            VALUES (?, ?, ?, ?, ?)

        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([

            $data['id_tema'],
            $data['titulo'],
            $data['descripcion'],
            $data['dificultad'],
            $data['portada']

        ]);

        echo json_encode([

            'mensaje' => 'Caso guardado correctamente'

        ]);

    } catch(Exception $e){

        echo json_encode([

            'error' => $e->getMessage()

        ]);

    }

}