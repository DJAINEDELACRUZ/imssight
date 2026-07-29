<?php

header('Content-Type: application/json');

require 'conn.php';

function responder($payload): void
{
    echo json_encode($payload);
    exit;
}

function asegurarTablasPronamPublico(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS imssight.pronam_examen_resultados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_examen INT NOT NULL,
            intento_token VARCHAR(96) NOT NULL UNIQUE,
            nombre VARCHAR(180) NULL,
            matricula VARCHAR(60) NULL,
            categoria VARCHAR(150) NULL,
            calificacion DECIMAL(5,2) NOT NULL,
            respuestas_correctas INT NOT NULL,
            total_preguntas INT NOT NULL,
            respuestas_json JSON NULL,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_pronam_resultados_examen (id_examen, fecha),
            CONSTRAINT fk_pronam_resultados_examen
                FOREIGN KEY (id_examen)
                REFERENCES imssight.examenes(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    asegurarColumna($pdo, 'pronam_examen_resultados', 'nombre', 'nombre VARCHAR(180) NULL AFTER intento_token');
    asegurarColumna($pdo, 'pronam_examen_resultados', 'matricula', 'matricula VARCHAR(60) NULL AFTER nombre');
    asegurarColumna($pdo, 'pronam_examen_resultados', 'categoria', 'categoria VARCHAR(150) NULL AFTER matricula');

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS imssight.pronam_examen_respuestas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_resultado INT NOT NULL,
            id_examen INT NOT NULL,
            id_pregunta INT NOT NULL,
            orden_pregunta INT NOT NULL,
            pregunta TEXT NOT NULL,
            respuesta_usuario CHAR(1) NULL,
            texto_respuesta_usuario TEXT NULL,
            respuesta_correcta CHAR(1) NOT NULL,
            texto_respuesta_correcta TEXT NULL,
            es_correcta TINYINT NOT NULL DEFAULT 0,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_pronam_respuestas_resultado (id_resultado),
            INDEX idx_pronam_respuestas_examen (id_examen, es_correcta),
            CONSTRAINT fk_pronam_respuestas_resultado
                FOREIGN KEY (id_resultado)
                REFERENCES imssight.pronam_examen_resultados(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_pronam_respuestas_examen
                FOREIGN KEY (id_examen)
                REFERENCES imssight.examenes(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_pronam_respuestas_pregunta
                FOREIGN KEY (id_pregunta)
                REFERENCES imssight.examen_preguntas(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS imssight.pronam_constancias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_resultado INT NOT NULL,
            id_examen INT NOT NULL,
            folio VARCHAR(96) NOT NULL UNIQUE,
            nombre VARCHAR(180) NOT NULL,
            matricula VARCHAR(60) NOT NULL,
            categoria VARCHAR(150) NOT NULL,
            calificacion DECIMAL(5,2) NOT NULL,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_pronam_constancias_examen (id_examen, fecha),
            CONSTRAINT fk_pronam_constancias_resultado
                FOREIGN KEY (id_resultado)
                REFERENCES imssight.pronam_examen_resultados(id)
                ON DELETE CASCADE,
            CONSTRAINT fk_pronam_constancias_examen
                FOREIGN KEY (id_examen)
                REFERENCES imssight.examenes(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function asegurarColumna(PDO $pdo, string $tabla, string $columna, string $ddl): void
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'imssight'
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    $stmt->execute([$tabla, $columna]);

    if ((int) $stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE imssight.$tabla ADD COLUMN $ddl");
    }
}

function textoOpcion(array $pregunta, string $letra): ?string
{
    $mapa = [
        'A' => 'opcion_a',
        'B' => 'opcion_b',
        'C' => 'opcion_c',
        'D' => 'opcion_d'
    ];

    return isset($mapa[$letra]) ? (string) ($pregunta[$mapa[$letra]] ?? '') : null;
}

function obtenerResultadoPorToken(PDO $pdo, string $token): ?array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM imssight.pronam_examen_resultados
        WHERE intento_token = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    return $resultado ?: null;
}

function obtenerConstanciaPorResultado(PDO $pdo, int $idResultado): ?array
{
    $stmt = $pdo->prepare("
        SELECT *
        FROM imssight.pronam_constancias
        WHERE id_resultado = ?
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->execute([$idResultado]);
    $constancia = $stmt->fetch(PDO::FETCH_ASSOC);

    return $constancia ?: null;
}

try {
    asegurarTablasPronamPublico($pdo);

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? $_POST['action'] ?? 'exam';

    if ($method === 'GET' && $action === 'exam') {
        $idExamen = (int) ($_GET['id'] ?? 0);

        if ($idExamen <= 0) {
            responder([
                'success' => false,
                'mensaje' => 'Examen no válido.'
            ]);
        }

        $stmtExamen = $pdo->prepare("
            SELECT
                e.id,
                e.titulo,
                e.descripcion,
                c.titulo AS caso
            FROM imssight.examenes e
            INNER JOIN imssight.casos_clinicos c ON c.id = e.id_caso
            WHERE e.id = ?
              AND e.activo = 1
            LIMIT 1
        ");
        $stmtExamen->execute([$idExamen]);
        $examen = $stmtExamen->fetch(PDO::FETCH_ASSOC);

        if (!$examen) {
            responder([
                'success' => false,
                'mensaje' => 'Examen no disponible.'
            ]);
        }

        $stmtPreguntas = $pdo->prepare("
            SELECT
                id,
                pregunta,
                opcion_a,
                opcion_b,
                opcion_c,
                opcion_d,
                orden_pregunta
            FROM imssight.examen_preguntas
            WHERE id_examen = ?
            ORDER BY orden_pregunta ASC, id ASC
        ");
        $stmtPreguntas->execute([$idExamen]);

        responder([
            'success' => true,
            'examen' => $examen,
            'preguntas' => $stmtPreguntas->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

    if ($method === 'GET' && $action === 'status') {
        $token = trim($_GET['token'] ?? '');

        if ($token === '') {
            responder([
                'success' => true,
                'resultado' => null,
                'constancia' => null
            ]);
        }

        $resultado = obtenerResultadoPorToken($pdo, $token);

        responder([
            'success' => true,
            'resultado' => $resultado,
            'constancia' => $resultado ? obtenerConstanciaPorResultado($pdo, (int) $resultado['id']) : null
        ]);
    }

    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $data['action'] ?? $action;

    if ($method === 'POST' && $action === 'submit') {
        $idExamen = (int) ($data['id_examen'] ?? 0);
        $respuestas = is_array($data['respuestas'] ?? null) ? $data['respuestas'] : [];

        if ($idExamen <= 0 || !$respuestas) {
            responder([
                'success' => false,
                'mensaje' => 'Responde el examen antes de enviarlo.'
            ]);
        }

        $stmt = $pdo->prepare("
            SELECT
                id,
                pregunta,
                opcion_a,
                opcion_b,
                opcion_c,
                opcion_d,
                respuesta_correcta,
                orden_pregunta
            FROM imssight.examen_preguntas
            WHERE id_examen = ?
            ORDER BY orden_pregunta ASC, id ASC
        ");
        $stmt->execute([$idExamen]);
        $preguntas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = count($preguntas);
        $correctas = 0;
        $respuestasNormalizadas = [];
        $detalleRespuestas = [];

        foreach ($preguntas as $pregunta) {
            $idPregunta = (string) $pregunta['id'];
            $respuestaUsuario = strtoupper(trim((string) ($respuestas[$idPregunta] ?? '')));
            $respuestaCorrecta = strtoupper((string) $pregunta['respuesta_correcta']);
            $esCorrecta = $respuestaUsuario !== '' && $respuestaUsuario === $respuestaCorrecta;
            $respuestasNormalizadas[$idPregunta] = $respuestaUsuario;

            if ($esCorrecta) {
                $correctas += 1;
            }

            $detalleRespuestas[] = [
                'id_pregunta' => (int) $pregunta['id'],
                'orden_pregunta' => (int) $pregunta['orden_pregunta'],
                'pregunta' => (string) $pregunta['pregunta'],
                'respuesta_usuario' => $respuestaUsuario !== '' ? $respuestaUsuario : null,
                'texto_respuesta_usuario' => $respuestaUsuario !== '' ? textoOpcion($pregunta, $respuestaUsuario) : null,
                'respuesta_correcta' => $respuestaCorrecta,
                'texto_respuesta_correcta' => textoOpcion($pregunta, $respuestaCorrecta),
                'es_correcta' => $esCorrecta ? 1 : 0
            ];
        }

        $calificacion = $total ? round(($correctas / $total) * 10, 2) : 0;
        $token = bin2hex(random_bytes(24));

        $pdo->beginTransaction();

        try {
            $insert = $pdo->prepare("
                INSERT INTO imssight.pronam_examen_resultados
                (
                    id_examen,
                    intento_token,
                    calificacion,
                    respuestas_correctas,
                    total_preguntas,
                    respuestas_json
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");
            $insert->execute([
                $idExamen,
                $token,
                $calificacion,
                $correctas,
                $total,
                json_encode($respuestasNormalizadas, JSON_UNESCAPED_UNICODE)
            ]);

            $idResultado = (int) $pdo->lastInsertId();
            $insertDetalle = $pdo->prepare("
                INSERT INTO imssight.pronam_examen_respuestas
                (
                    id_resultado,
                    id_examen,
                    id_pregunta,
                    orden_pregunta,
                    pregunta,
                    respuesta_usuario,
                    texto_respuesta_usuario,
                    respuesta_correcta,
                    texto_respuesta_correcta,
                    es_correcta
                )
                VALUES
                (
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

            foreach ($detalleRespuestas as $detalle) {
                $insertDetalle->execute([
                    $idResultado,
                    $idExamen,
                    $detalle['id_pregunta'],
                    $detalle['orden_pregunta'],
                    $detalle['pregunta'],
                    $detalle['respuesta_usuario'],
                    $detalle['texto_respuesta_usuario'],
                    $detalle['respuesta_correcta'],
                    $detalle['texto_respuesta_correcta'],
                    $detalle['es_correcta']
                ]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }

        responder([
            'success' => true,
            'token' => $token,
            'aprobado' => $calificacion >= 8,
            'calificacion' => $calificacion,
            'correctas' => $correctas,
            'total' => $total
        ]);
    }

    if ($method === 'POST' && $action === 'certificate') {
        $token = trim($data['token'] ?? '');
        $nombre = trim($data['nombre'] ?? '');
        $matricula = trim($data['matricula'] ?? '');
        $categoria = trim($data['categoria'] ?? '');

        if ($token === '' || $nombre === '' || $matricula === '' || $categoria === '') {
            responder([
                'success' => false,
                'mensaje' => 'Completa nombre, matrícula y categoría.'
            ]);
        }

        $resultado = obtenerResultadoPorToken($pdo, $token);

        if (!$resultado || (float) $resultado['calificacion'] < 8) {
            responder([
                'success' => false,
                'mensaje' => 'La constancia se desbloquea al aprobar el examen.'
            ]);
        }

        $constanciaExistente = obtenerConstanciaPorResultado($pdo, (int) $resultado['id']);

        if ($constanciaExistente) {
            $updateResultado = $pdo->prepare("
                UPDATE imssight.pronam_examen_resultados
                SET nombre = ?, matricula = ?, categoria = ?
                WHERE id = ?
                  AND (nombre IS NULL OR matricula IS NULL OR categoria IS NULL)
            ");
            $updateResultado->execute([
                $constanciaExistente['nombre'],
                $constanciaExistente['matricula'],
                $constanciaExistente['categoria'],
                (int) $resultado['id']
            ]);

            responder([
                'success' => true,
                'constancia' => $constanciaExistente
            ]);
        }

        $folio = 'PRONAM-' . date('Ymd') . '-' . strtoupper(substr($token, 0, 10));
        $insert = $pdo->prepare("
            INSERT INTO imssight.pronam_constancias
            (
                id_resultado,
                id_examen,
                folio,
                nombre,
                matricula,
                categoria,
                calificacion
            )
            VALUES
            (
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
            (int) $resultado['id'],
            (int) $resultado['id_examen'],
            $folio,
            $nombre,
            $matricula,
            $categoria,
            (float) $resultado['calificacion']
        ]);

        $updateResultado = $pdo->prepare("
            UPDATE imssight.pronam_examen_resultados
            SET nombre = ?, matricula = ?, categoria = ?
            WHERE id = ?
        ");
        $updateResultado->execute([
            $nombre,
            $matricula,
            $categoria,
            (int) $resultado['id']
        ]);

        responder([
            'success' => true,
            'constancia' => obtenerConstanciaPorResultado($pdo, (int) $resultado['id'])
        ]);
    }

    responder([
        'success' => false,
        'mensaje' => 'Solicitud no válida.'
    ]);
} catch (Throwable $e) {
    responder([
        'success' => false,
        'mensaje' => 'No fue posible procesar la solicitud PRONAM.'
    ]);
}
