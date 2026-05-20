<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

require 'conn.php';

/*
|--------------------------------------------------------------------------
| LIMPIAR ÍNDICE
|--------------------------------------------------------------------------
*/

$pdo->exec("TRUNCATE TABLE search_index");

/*
|--------------------------------------------------------------------------
| ESPECIALIDADES
|--------------------------------------------------------------------------
*/

$sql = "

SELECT
    id,
    nombre
FROM especialidades

";

$stmt = $pdo->query($sql);

while($row = $stmt->fetch()){

    $insert = $pdo->prepare("

        INSERT INTO search_index(

            tipo,
            titulo,
            contenido,
            descripcion,
            id_especialidad,
            url

        )

        VALUES(

            'especialidad',
            ?,
            ?,
            ?,
            ?,
            ?

        )

    ");

    $insert->execute([

        $row['nombre'],
        $row['nombre'],
        $row['nombre'],
        $row['id'],
        "../pages/especialidad.html?id=".$row['id']

    ]);

}

/*
|--------------------------------------------------------------------------
| TEMAS
|--------------------------------------------------------------------------
*/

$sql = "

SELECT
    id,
    titulo,
    descripcion,
    id_especialidad
FROM temas

";

$stmt = $pdo->query($sql);

while($row = $stmt->fetch()){

    $insert = $pdo->prepare("

        INSERT INTO search_index(

            tipo,
            titulo,
            contenido,
            descripcion,
            id_especialidad,
            id_tema,
            url

        )

        VALUES(

            'tema',
            ?,
            ?,
            ?,
            ?,
            ?,
            ?

        )

    ");

    $insert->execute([

        $row['titulo'],
        $row['titulo'].' '.$row['descripcion'],
        $row['descripcion'],
        $row['id_especialidad'],
        $row['id'],
        "../pages/especialidad.html?id=".$row['id_especialidad']

    ]);

}


/*
|--------------------------------------------------------------------------
| CASOS
|--------------------------------------------------------------------------
*/

$sql = "

SELECT
    c.id,
    c.titulo,
    c.descripcion,
    t.id_especialidad,
    t.id AS id_tema

FROM casos_clinicos c

LEFT JOIN temas t
ON t.id = c.id_tema

";

$stmt = $pdo->query($sql);

while($row = $stmt->fetch()){

    $insert = $pdo->prepare("

        INSERT INTO search_index(

            tipo,
            titulo,
            contenido,
            descripcion,
            id_especialidad,
            id_tema,
            id_caso,
            url

        )

        VALUES(

            'caso',
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?

        )

    ");

    $insert->execute([

        $row['titulo'],
        $row['titulo'].' '.$row['descripcion'],
        $row['descripcion'],
        $row['id_especialidad'],
        $row['id_tema'],
        $row['id'],
        "../pages/caso.html?id=".$row['id']

    ]);

}

/*
|--------------------------------------------------------------------------
| ESCENAS
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    e.id,
    e.titulo,
    e.contenido,
    c.id AS id_caso,
    t.id_especialidad

FROM escenas e

LEFT JOIN casos_clinicos c
ON c.id = e.id_caso

LEFT JOIN temas t
ON t.id = c.id_tema

";

$stmt = $pdo->query($sql);

while($row = $stmt->fetch()){

    $textoPlano =
        strip_tags($row['contenido']);

    $insert = $pdo->prepare("

        INSERT INTO search_index(

            tipo,
            titulo,
            contenido,
            descripcion,
            id_especialidad,
            url

        )

        VALUES(

            'escena',
            ?,
            ?,
            ?,
            ?,
            ?

        )

    ");

    $insert->execute([

        $row['titulo'],
        $textoPlano,
        substr($textoPlano,0,300),
        $row['id_especialidad'],
        "../pages/caso.html?id=".$row['id_caso']."&escena=".$row['id'].urlencode($q ?? '')
    ]);

}