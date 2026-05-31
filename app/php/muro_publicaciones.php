<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

function asegurarTablaMuro($pdo){

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS imssight.muro_publicaciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NULL,
            autor_nombre VARCHAR(150) NOT NULL,
            autor_rol VARCHAR(50),
            tipo ENUM('institucional','experto','noticia','usuario') DEFAULT 'usuario',
            titulo VARCHAR(180),
            contenido TEXT NOT NULL,
            fuente VARCHAR(255),
            fijado TINYINT DEFAULT 0,
            fecha_fijado TIMESTAMP NULL,
            activo TINYINT DEFAULT 1,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (id_usuario)
            REFERENCES imssight.usuarios(id)
            ON DELETE SET NULL
        );
    ");

    $stmt =
        $pdo->query("
            SELECT COUNT(*) AS total
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = 'imssight'
            AND TABLE_NAME = 'muro_publicaciones'
            AND COLUMN_NAME = 'fecha_fijado'
        ");

    $tieneFechaFijado =
        (int)$stmt->fetch()['total'] > 0;

    if(!$tieneFechaFijado){

        $pdo->exec("
            ALTER TABLE imssight.muro_publicaciones
            ADD COLUMN fecha_fijado TIMESTAMP NULL
            AFTER fijado
        ");

    }

    $pdo->exec("
        UPDATE imssight.muro_publicaciones
        SET fecha_fijado = fecha
        WHERE fijado = 1
        AND fecha_fijado IS NULL
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS imssight.muro_comentarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_publicacion INT NOT NULL,
            id_usuario INT NOT NULL,
            autor_nombre VARCHAR(150) NOT NULL,
            autor_rol VARCHAR(50),
            contenido TEXT NOT NULL,
            activo TINYINT DEFAULT 1,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (id_publicacion)
            REFERENCES imssight.muro_publicaciones(id)
            ON DELETE CASCADE,

            FOREIGN KEY (id_usuario)
            REFERENCES imssight.usuarios(id)
            ON DELETE CASCADE
        );
    ");

    asegurarTablaNotificaciones($pdo);

    $stmt =
        $pdo->query("
            SELECT COUNT(*) AS total
            FROM imssight.muro_publicaciones
            WHERE fijado = 1
        ");

    $total =
        (int)$stmt->fetch()['total'];

    if($total > 0){
        return;
    }

    $insert =
        $pdo->prepare("
            INSERT INTO imssight.muro_publicaciones
            (
                id_usuario,
                autor_nombre,
                autor_rol,
                tipo,
                titulo,
                contenido,
                fuente,
                fijado,
                fecha_fijado
            )
            VALUES
            (
                NULL,
                ?, ?, ?, ?, ?, ?, 1, NOW()
            )
        ");

    $publicaciones = [
        [
            'IMSSight / UEI',
            'institucional',
            'institucional',
            'Bienvenido al muro académico',
            'Este espacio concentra experiencias clínicas, hallazgos útiles, avisos académicos y recomendaciones para fortalecer el aprendizaje entre residentes, profesores y equipos de salud.',
            'Unidad de Educación e Investigación'
        ],
        [
            'Profesores IMSS',
            'experto',
            'experto',
            'Perla docente',
            'Cuando compartas un hallazgo, intenta incluir contexto clínico, razonamiento y una fuente o referencia si aplica. El valor del muro está en convertir la experiencia en aprendizaje reutilizable.',
            'Recomendación docente'
        ],
        [
            'IMSSight Noticias',
            'institucional',
            'noticia',
            'Convocatoria permanente',
            'Puedes publicar dudas, aprendizajes breves, casos comentados, artículos relevantes o experiencias de rotación. Mantén siempre confidencialidad del paciente y evita datos identificables.',
            'Buenas prácticas de publicación'
        ]
    ];

    foreach($publicaciones as $publicacion){
        $insert->execute($publicacion);
    }

}

function asegurarTablaNotificaciones($pdo){

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS imssight.notificaciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario_destino INT NOT NULL,
            id_usuario_actor INT NULL,
            actor_nombre VARCHAR(150),
            tipo VARCHAR(50) NOT NULL,
            titulo VARCHAR(180) NOT NULL,
            mensaje TEXT NOT NULL,
            url VARCHAR(255),
            id_publicacion INT NULL,
            id_comentario INT NULL,
            leida TINYINT DEFAULT 0,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (id_usuario_destino)
            REFERENCES imssight.usuarios(id)
            ON DELETE CASCADE,

            FOREIGN KEY (id_usuario_actor)
            REFERENCES imssight.usuarios(id)
            ON DELETE SET NULL
        );
    ");

}

function crearNotificacionesComentario($pdo, $idPublicacion, $idComentario, $idActor, $actorNombre){

    $destinatarios = [];

    $stmt =
        $pdo->prepare("
            SELECT id_usuario
            FROM imssight.muro_publicaciones
            WHERE id = ?
            AND activo = 1
            LIMIT 1
        ");

    $stmt->execute([$idPublicacion]);

    $publicacion =
        $stmt->fetch(PDO::FETCH_ASSOC);

    if(
        $publicacion
        && !empty($publicacion['id_usuario'])
        && (int)$publicacion['id_usuario'] !== (int)$idActor
    ){
        $destinatarios[(int)$publicacion['id_usuario']] = true;
    }

    $stmt =
        $pdo->prepare("
            SELECT DISTINCT id_usuario
            FROM imssight.muro_comentarios
            WHERE id_publicacion = ?
            AND activo = 1
            AND id_usuario <> ?
        ");

    $stmt->execute([
        $idPublicacion,
        $idActor
    ]);

    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row){
        $destinatarios[(int)$row['id_usuario']] = true;
    }

    if(count($destinatarios) === 0){
        return;
    }

    $insert =
        $pdo->prepare("
            INSERT INTO imssight.notificaciones
            (
                id_usuario_destino,
                id_usuario_actor,
                actor_nombre,
                tipo,
                titulo,
                mensaje,
                url,
                id_publicacion,
                id_comentario
            )
            VALUES
            (
                ?, ?, ?, 'comentario_muro', ?, ?, ?, ?, ?
            )
        ");

    foreach(array_keys($destinatarios) as $idDestino){

        $insert->execute([
            $idDestino,
            $idActor,
            $actorNombre,
            'Nuevo comentario en el muro',
            $actorNombre . ' comentó una publicación en la que participas.',
            '../pages/muro_publicacion.html?id=' . $idPublicacion,
            $idPublicacion,
            $idComentario
        ]);

    }

}

if(!isset($_SESSION['usuario_id'])){

    echo json_encode([
        'ok' => false,
        'mensaje' => 'No autenticado'
    ]);

    exit;

}

try{

    asegurarTablaMuro($pdo);

    $rolSesion =
        $_SESSION['rol'] ?? 'usuario';

    $puedeFijar =
        in_array(
            $rolSesion,
            ['admin','docente'],
            true
        );

    if($_SERVER['REQUEST_METHOD'] === 'GET'){

        $idPublicacionFiltro =
            (int)($_GET['id'] ?? 0);

        $sql = "
                SELECT
                    id,
                    id_usuario,
                    autor_nombre,
                    autor_rol,
                    tipo,
                    titulo,
                    contenido,
                    fuente,
                    fijado,
                    fecha_fijado,
                    fecha
                FROM imssight.muro_publicaciones
                WHERE activo = 1
        ";

        $params = [];

        if($idPublicacionFiltro > 0){
            $sql .= " AND id = ? ";
            $params[] = $idPublicacionFiltro;
        }

        $sql .= "
                ORDER BY
                    fijado DESC,
                    fecha_fijado DESC,
                    fecha DESC
                LIMIT 80
        ";

        $stmt =
            $pdo->prepare($sql);

        $stmt->execute($params);

        $publicaciones =
            $stmt->fetchAll(PDO::FETCH_ASSOC);

        $comentariosPorPublicacion = [];

        if(count($publicaciones) > 0){

            $ids =
                array_column(
                    $publicaciones,
                    'id'
                );

            $placeholders =
                implode(
                    ',',
                    array_fill(0, count($ids), '?')
                );

            $comentariosStmt =
                $pdo->prepare("
                    SELECT
                        id,
                        id_publicacion,
                        id_usuario,
                        autor_nombre,
                        autor_rol,
                        contenido,
                        fecha
                    FROM imssight.muro_comentarios
                    WHERE activo = 1
                    AND id_publicacion IN ($placeholders)
                    ORDER BY fecha ASC
                ");

            $comentariosStmt->execute($ids);

            foreach(
                $comentariosStmt->fetchAll(PDO::FETCH_ASSOC)
                as $comentario
            ){

                $idPublicacion =
                    $comentario['id_publicacion'];

                $comentario['puede_eliminar'] =
                    $puedeFijar
                    || (int)$comentario['id_usuario'] === (int)$_SESSION['usuario_id'];

                if(!isset($comentariosPorPublicacion[$idPublicacion])){
                    $comentariosPorPublicacion[$idPublicacion] = [];
                }

                $comentariosPorPublicacion[$idPublicacion][] =
                    $comentario;

            }

        }

        foreach($publicaciones as &$publicacion){

            $publicacion['puede_editar'] =
                $puedeFijar
                || (
                    !empty($publicacion['id_usuario'])
                    && (int)$publicacion['id_usuario'] === (int)$_SESSION['usuario_id']
                );

            $publicacion['puede_eliminar'] =
                $publicacion['puede_editar'];

            $publicacion['comentarios'] =
                $comentariosPorPublicacion[$publicacion['id']]
                ?? [];

        }

        echo json_encode([
            'ok' => true,
            'permisos' => [
                'usuario_id' => $_SESSION['usuario_id'],
                'rol' => $rolSesion,
                'puede_fijar' => $puedeFijar
            ],
            'publicaciones' =>
                $publicaciones
        ]);

        exit;

    }

    if($_SERVER['REQUEST_METHOD'] === 'DELETE'){

        $data =
            json_decode(
                file_get_contents('php://input'),
                true
            );

        $idComentario =
            (int)($data['id_comentario'] ?? 0);

        $idPublicacionEliminar =
            (int)($data['id_publicacion'] ?? 0);

        if($idPublicacionEliminar > 0){

            $stmt =
                $pdo->prepare("
                    SELECT id_usuario
                    FROM imssight.muro_publicaciones
                    WHERE id = ?
                    AND activo = 1
                    LIMIT 1
                ");

            $stmt->execute([$idPublicacionEliminar]);

            $publicacion =
                $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$publicacion){

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'No se encontró la publicación.'
                ]);

                exit;

            }

            $puedeEliminarPublicacion =
                $puedeFijar
                || (
                    !empty($publicacion['id_usuario'])
                    && (int)$publicacion['id_usuario'] === (int)$_SESSION['usuario_id']
                );

            if(!$puedeEliminarPublicacion){

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'No tienes permisos para borrar esta publicación.'
                ]);

                exit;

            }

            $stmt =
                $pdo->prepare("
                    UPDATE imssight.muro_publicaciones
                    SET activo = 0
                    WHERE id = ?
                ");

            $stmt->execute([$idPublicacionEliminar]);

            $stmt =
                $pdo->prepare("
                    UPDATE imssight.muro_comentarios
                    SET activo = 0
                    WHERE id_publicacion = ?
                ");

            $stmt->execute([$idPublicacionEliminar]);

            $stmt =
                $pdo->prepare("
                    UPDATE imssight.notificaciones
                    SET leida = 1
                    WHERE id_publicacion = ?
                ");

            $stmt->execute([$idPublicacionEliminar]);

            echo json_encode([
                'ok' => true,
                'mensaje' => 'Publicación eliminada.'
            ]);

            exit;

        }

        if($idComentario <= 0){

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Comentario inválido.'
            ]);

            exit;

        }

        $stmt =
            $pdo->prepare("
                SELECT id_usuario
                FROM imssight.muro_comentarios
                WHERE id = ?
                AND activo = 1
                LIMIT 1
            ");

        $stmt->execute([$idComentario]);

        $comentario =
            $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$comentario){

            echo json_encode([
                'ok' => false,
                'mensaje' => 'No se encontró el comentario.'
            ]);

            exit;

        }

        $puedeEliminar =
            $puedeFijar
            || (int)$comentario['id_usuario'] === (int)$_SESSION['usuario_id'];

        if(!$puedeEliminar){

            echo json_encode([
                'ok' => false,
                'mensaje' => 'No tienes permisos para borrar este comentario.'
            ]);

            exit;

        }

        $stmt =
            $pdo->prepare("
                UPDATE imssight.muro_comentarios
                SET activo = 0
                WHERE id = ?
            ");

        $stmt->execute([$idComentario]);

        $stmt =
            $pdo->prepare("
                UPDATE imssight.notificaciones
                SET leida = 1
                WHERE id_comentario = ?
            ");

        $stmt->execute([$idComentario]);

        echo json_encode([
            'ok' => true,
            'mensaje' => 'Comentario eliminado.'
        ]);

        exit;

    }

    if($_SERVER['REQUEST_METHOD'] === 'PATCH'){

        $data =
            json_decode(
                file_get_contents('php://input'),
                true
            );

        if(($data['accion'] ?? '') === 'editar_publicacion'){

            $idPublicacion =
                (int)($data['id_publicacion'] ?? 0);

            $titulo =
                trim($data['titulo'] ?? '');

            $contenido =
                trim($data['contenido'] ?? '');

            if($idPublicacion <= 0 || $contenido === ''){

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Publicación inválida.'
                ]);

                exit;

            }

            if(strlen($contenido) > 1800){

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'La publicación es demasiado larga.'
                ]);

                exit;

            }

            if(strlen($titulo) > 180){
                $titulo = substr($titulo, 0, 180);
            }

            $stmt =
                $pdo->prepare("
                    SELECT id_usuario
                    FROM imssight.muro_publicaciones
                    WHERE id = ?
                    AND activo = 1
                    LIMIT 1
                ");

            $stmt->execute([$idPublicacion]);

            $publicacion =
                $stmt->fetch(PDO::FETCH_ASSOC);

            $puedeEditar =
                $publicacion
                && (
                    $puedeFijar
                    || (
                        !empty($publicacion['id_usuario'])
                        && (int)$publicacion['id_usuario'] === (int)$_SESSION['usuario_id']
                    )
                );

            if(!$puedeEditar){

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'No tienes permisos para editar esta publicación.'
                ]);

                exit;

            }

            $stmt =
                $pdo->prepare("
                    UPDATE imssight.muro_publicaciones
                    SET titulo = ?, contenido = ?
                    WHERE id = ?
                    AND activo = 1
                ");

            $stmt->execute([
                $titulo !== '' ? $titulo : null,
                $contenido,
                $idPublicacion
            ]);

            echo json_encode([
                'ok' => true,
                'mensaje' => 'Publicación actualizada.'
            ]);

            exit;

        }

        if(!$puedeFijar){

            echo json_encode([
                'ok' => false,
                'mensaje' => 'No tienes permisos para fijar publicaciones.'
            ]);

            exit;

        }

        $id =
            (int)($data['id'] ?? 0);

        $fijado =
            !empty($data['fijado']) ? 1 : 0;

        if($id <= 0){

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Publicación inválida.'
            ]);

            exit;

        }

        $stmt =
            $pdo->prepare("
                UPDATE imssight.muro_publicaciones
                SET
                    fijado = ?,
                    fecha_fijado = CASE
                        WHEN ? = 1 THEN NOW()
                        ELSE NULL
                    END
                WHERE id = ?
                AND activo = 1
            ");

        $stmt->execute([
            $fijado,
            $fijado,
            $id
        ]);

        echo json_encode([
            'ok' => true,
            'mensaje' =>
                $fijado
                    ? 'Publicación fijada.'
                    : 'Publicación desfijada.'
        ]);

        exit;

    }

    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        $data =
            json_decode(
                file_get_contents('php://input'),
                true
            );

        $titulo =
            trim($data['titulo'] ?? '');

        $contenido =
            trim($data['contenido'] ?? '');

        if(($data['accion'] ?? '') === 'comentar'){

            $idPublicacion =
                (int)($data['id_publicacion'] ?? 0);

            if($idPublicacion <= 0){

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Publicación inválida.'
                ]);

                exit;

            }

            if($contenido === ''){

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'Escribe un comentario.'
                ]);

                exit;

            }

            if(strlen($contenido) > 800){

                echo json_encode([
                    'ok' => false,
                    'mensaje' => 'El comentario es demasiado largo.'
                ]);

                exit;

            }

            $stmt =
                $pdo->prepare("
                    INSERT INTO imssight.muro_comentarios
                    (
                        id_publicacion,
                        id_usuario,
                        autor_nombre,
                        autor_rol,
                        contenido
                    )
                    VALUES
                    (
                        ?, ?, ?, ?, ?
                    )
                ");

            $stmt->execute([
                $idPublicacion,
                $_SESSION['usuario_id'],
                $_SESSION['usuario_nombre'],
                $rolSesion,
                $contenido
            ]);

            crearNotificacionesComentario(
                $pdo,
                $idPublicacion,
                $pdo->lastInsertId(),
                $_SESSION['usuario_id'],
                $_SESSION['usuario_nombre']
            );

            echo json_encode([
                'ok' => true,
                'mensaje' => 'Comentario publicado.'
            ]);

            exit;

        }

        if($contenido === ''){

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Escribe algo para publicar.'
            ]);

            exit;

        }

        if(strlen($contenido) > 1800){

            echo json_encode([
                'ok' => false,
                'mensaje' => 'La publicación es demasiado larga.'
            ]);

            exit;

        }

        if(strlen($titulo) > 180){
            $titulo = substr($titulo, 0, 180);
        }

        $tiposPermitidos =
            ['institucional','experto','noticia','usuario'];

        $tipo =
            $puedeFijar
            && in_array(
                $data['tipo'] ?? '',
                $tiposPermitidos,
                true
            )
                ? $data['tipo']
                : 'usuario';

        $fijado =
            $puedeFijar
            && !empty($data['fijado'])
                ? 1
                : 0;

        $stmt =
            $pdo->prepare("
                INSERT INTO imssight.muro_publicaciones
                (
                    id_usuario,
                    autor_nombre,
                    autor_rol,
                    tipo,
                    titulo,
                    contenido,
                    fuente,
                    fijado,
                    fecha_fijado
                )
                VALUES
                (
                    ?, ?, ?, ?, ?, ?, NULL, ?,
                    CASE
                        WHEN ? = 1 THEN NOW()
                        ELSE NULL
                    END
                )
            ");

        $stmt->execute([
            $_SESSION['usuario_id'],
            $_SESSION['usuario_nombre'],
            $rolSesion,
            $tipo,
            $titulo !== '' ? $titulo : null,
            $contenido,
            $fijado,
            $fijado
        ]);

        echo json_encode([
            'ok' => true,
            'mensaje' => 'Publicación compartida correctamente.'
        ]);

        exit;

    }

    echo json_encode([
        'ok' => false,
        'mensaje' => 'Método no permitido.'
    ]);

}
catch(Exception $e){

    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);

}
