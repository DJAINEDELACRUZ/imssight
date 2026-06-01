<?php

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require 'conn.php';
require 'perfil_utils.php';

$pdo->exec("SET NAMES utf8mb4");

asegurarColumnasPerfilPublico($pdo);

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
        "/pages/especialidad.html?id=".$row['id']

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
        "/pages/especialidad.html?id=".$row['id_especialidad']

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
        "/pages/caso.html?id=".$row['id']

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
    trim(
        preg_replace(
            '/\s+/',
            ' ',
            strip_tags($row['contenido'])
        )
    );

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
        mb_substr($textoPlano, 0, 300, 'UTF-8'),
        $row['id_especialidad'],
        "/pages/caso.html?id=".$row['id_caso']."&escena=".$row['id']
    ]);

}

/*
|--------------------------------------------------------------------------
| USUARIOS
|--------------------------------------------------------------------------
*/

$sql = "

SELECT
    u.id,
    u.nombre,
    u.matricula,
    u.rol,
    p.universidad,
    p.especialidad,
    p.semestre,
    p.estado,
    p.biografia,
    p.hospital,
    p.cumpleanos_publico,
    p.puesto,
    p.etapa_profesional,
    p.intereses,
    p.frase_perfil,
    p.perfil_publico,
    p.mostrar_estado,
    p.mostrar_biografia

FROM imssight.usuarios u

LEFT JOIN imssight.usuarios_perfil p
ON p.id_usuario = u.id

WHERE u.activo = 1
AND COALESCE(p.perfil_publico, 1) = 1

";

$stmt = $pdo->query($sql);

while($row = $stmt->fetch()){

    $estado =
        ((int)($row['mostrar_estado'] ?? 1) === 1)
            ? ($row['estado'] ?? '')
            : '';

    $biografia =
        ((int)($row['mostrar_biografia'] ?? 1) === 1)
            ? ($row['biografia'] ?? '')
            : '';

    $descripcion =
        trim(
            implode(' · ', array_filter([
                $row['rol'] ?? '',
                $row['puesto'] ?? '',
                $row['hospital'] ?? '',
                $row['especialidad'] ?? '',
                $row['universidad'] ?? '',
                $estado
            ]))
        );

    $contenido =
        trim(
            implode(' ', array_filter([
                $row['nombre'] ?? '',
                $row['matricula'] ?? '',
                $row['rol'] ?? '',
                $row['universidad'] ?? '',
                $row['especialidad'] ?? '',
                $row['semestre'] ?? '',
                $row['hospital'] ?? '',
                $row['puesto'] ?? '',
                $row['etapa_profesional'] ?? '',
                $row['cumpleanos_publico'] ?? '',
                $row['intereses'] ?? '',
                $row['frase_perfil'] ?? '',
                $estado,
                $biografia
            ]))
        );

    $insert = $pdo->prepare("

        INSERT INTO search_index(

            tipo,
            titulo,
            contenido,
            descripcion,
            url

        )

        VALUES(

            'usuario',
            ?,
            ?,
            ?,
            ?

        )

    ");

    $insert->execute([

        $row['nombre'],
        $contenido,
        $descripcion ?: 'Perfil de usuario IMSSight',
        "/pages/user_profile.html?id=".$row['id']

    ]);

}

/*
|--------------------------------------------------------------------------
| ENTRADAS DE PERFIL
|--------------------------------------------------------------------------
*/

$pdo->exec("
    CREATE TABLE IF NOT EXISTS imssight.perfil_entradas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        tipo ENUM('frase','reflexion','historia','aprendizaje') DEFAULT 'reflexion',
        titulo VARCHAR(180),
        contenido TEXT NOT NULL,
        activo TINYINT DEFAULT 1,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (id_usuario)
        REFERENCES imssight.usuarios(id)
        ON DELETE CASCADE
    );
");

$sql = "

SELECT
    e.id,
    e.id_usuario,
    e.tipo,
    e.titulo,
    e.contenido,
    u.nombre

FROM imssight.perfil_entradas e

INNER JOIN imssight.usuarios u
ON u.id = e.id_usuario

LEFT JOIN imssight.usuarios_perfil p
ON p.id_usuario = u.id

WHERE e.activo = 1
AND u.activo = 1
AND COALESCE(p.perfil_publico, 1) = 1

";

$stmt = $pdo->query($sql);

while($row = $stmt->fetch()){

    $titulo =
        trim($row['titulo'] ?? '') !== ''
            ? $row['titulo']
            : ucfirst($row['tipo']) . ' de ' . $row['nombre'];

    $insert = $pdo->prepare("

        INSERT INTO search_index(

            tipo,
            titulo,
            contenido,
            descripcion,
            url

        )

        VALUES(

            'perfil_entrada',
            ?,
            ?,
            ?,
            ?

        )

    ");

    $insert->execute([

        $titulo,
        trim(($row['nombre'] ?? '') . ' ' . ($row['tipo'] ?? '') . ' ' . ($row['contenido'] ?? '')),
        mb_substr($row['contenido'] ?? '', 0, 300, 'UTF-8'),
        "/pages/user_profile.html?id=".$row['id_usuario']

    ]);

}

echo json_encode([

    "ok" => true,

    "mensaje" => "Índice reconstruido correctamente"

]);
