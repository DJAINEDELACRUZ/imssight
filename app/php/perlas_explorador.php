<?php

header('Content-Type: application/json; charset=utf-8');

require 'conn.php';
require 'content_visibility.php';

asegurarColumnasVisibilidadContenido($pdo);

function responder($data)
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function id_parametro($nombre)
{
    $valor = filter_input(INPUT_GET, $nombre, FILTER_VALIDATE_INT);
    return $valor && $valor > 0 ? $valor : 0;
}

try {

    $accion = $_GET['accion'] ?? 'especialidades';

    if ($accion === 'especialidades') {

        $sql = "
            SELECT
                e.id,
                e.nombre,
                e.icono,
                e.color,
                COUNT(DISTINCT c.id) AS total_casos,
                COUNT(DISTINCT c.id) AS total_perlas,
                COUNT(p.id) AS total_secciones
            FROM imssight.especialidades e
            INNER JOIN imssight.temas t
                ON t.id_especialidad = e.id
            INNER JOIN imssight.casos_clinicos c
                ON c.id_tema = t.id
                AND c.activo = 1
            INNER JOIN imssight.perlas_clinicas_caso p
                ON p.id_caso = c.id
            WHERE " . condicionContenidoInterno('e') . "
            GROUP BY e.id, e.nombre, e.icono, e.color
            ORDER BY e.nombre
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        responder([
            'ok' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

    if ($accion === 'temas') {

        $idEspecialidad = id_parametro('id_especialidad');

        if (!$idEspecialidad) {
            responder([
                'ok' => false,
                'mensaje' => 'Selecciona una especialidad.'
            ]);
        }

        $sql = "
            SELECT
                t.id,
                t.titulo,
                t.descripcion,
                t.imagen,
                COUNT(DISTINCT c.id) AS total_casos,
                COUNT(DISTINCT c.id) AS total_perlas,
                COUNT(p.id) AS total_secciones
            FROM imssight.temas t
            INNER JOIN imssight.especialidades e
                ON e.id = t.id_especialidad
            INNER JOIN imssight.casos_clinicos c
                ON c.id_tema = t.id
                AND c.activo = 1
            INNER JOIN imssight.perlas_clinicas_caso p
                ON p.id_caso = c.id
            WHERE t.id_especialidad = :id_especialidad
                AND " . condicionContenidoInterno('e') . "
            GROUP BY t.id, t.titulo, t.descripcion, t.imagen
            ORDER BY t.titulo
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_especialidad' => $idEspecialidad
        ]);

        responder([
            'ok' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

    if ($accion === 'casos') {

        $idTema = id_parametro('id_tema');

        if (!$idTema) {
            responder([
                'ok' => false,
                'mensaje' => 'Selecciona un tema.'
            ]);
        }

        $sql = "
            SELECT
                c.id,
                COALESCE(c.id_especialidad, t.id_especialidad) AS id_especialidad,
                c.id_tema,
                c.titulo,
                c.descripcion,
                c.portada,
                c.dificultad,
                1 AS total_perlas,
                COUNT(p.id) AS total_secciones
            FROM imssight.casos_clinicos c
            INNER JOIN imssight.temas t
                ON t.id = c.id_tema
            INNER JOIN imssight.especialidades e
                ON e.id = t.id_especialidad
            INNER JOIN imssight.perlas_clinicas_caso p
                ON p.id_caso = c.id
            WHERE c.id_tema = :id_tema
                AND c.activo = 1
                AND " . condicionContenidoInterno('e') . "
            GROUP BY c.id, c.id_especialidad, t.id_especialidad, c.id_tema, c.titulo, c.descripcion, c.portada, c.dificultad
            ORDER BY c.titulo
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_tema' => $idTema
        ]);

        responder([
            'ok' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

    if ($accion === 'perlas') {

        $idCaso = id_parametro('id_caso');

        if (!$idCaso) {
            responder([
                'ok' => false,
                'mensaje' => 'Selecciona un caso clinico.'
            ]);
        }

        $sql = "
            SELECT
                c.id AS id_caso,
                c.titulo AS caso,
                c.descripcion AS caso_descripcion,
                c.portada AS portada,
                t.id AS id_tema,
                t.titulo AS tema,
                e.id AS id_especialidad,
                e.nombre AS especialidad,
                p.id,
                p.seccion,
                p.contenido,
                p.orden
            FROM imssight.perlas_clinicas_caso p
            INNER JOIN imssight.casos_clinicos c
                ON c.id = p.id_caso
            INNER JOIN imssight.temas t
                ON t.id = c.id_tema
            INNER JOIN imssight.especialidades e
                ON e.id = t.id_especialidad
            WHERE p.id_caso = :id_caso
                AND c.activo = 1
                AND " . condicionContenidoInterno('e') . "
            ORDER BY p.orden, p.id
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_caso' => $idCaso
        ]);

        $perlas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        responder([
            'ok' => true,
            'data' => $perlas,
            'contexto' => $perlas[0] ?? null
        ]);
    }

    responder([
        'ok' => false,
        'mensaje' => 'Accion no disponible.'
    ]);

} catch (Exception $e) {

    responder([
        'ok' => false,
        'mensaje' => 'No se pudo cargar la informacion solicitada.'
    ]);
}
