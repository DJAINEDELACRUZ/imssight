<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

function asegurarTablaChat($pdo){

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS imssight.chat_mensajes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_remitente INT NOT NULL,
            id_destinatario INT NOT NULL,
            contenido TEXT NOT NULL,
            leido TINYINT DEFAULT 0,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

            FOREIGN KEY (id_remitente)
            REFERENCES imssight.usuarios(id)
            ON DELETE CASCADE,

            FOREIGN KEY (id_destinatario)
            REFERENCES imssight.usuarios(id)
            ON DELETE CASCADE
        );
    ");

    asegurarIndiceChat(
        $pdo,
        'chat_mensajes',
        'idx_chat_conversacion_fecha',
        'id_remitente, id_destinatario, fecha'
    );

    asegurarIndiceChat(
        $pdo,
        'chat_mensajes',
        'idx_chat_destinatario_leido',
        'id_destinatario, leido, fecha'
    );

}

function asegurarIndiceChat($pdo, $tabla, $indice, $columnas){

    $stmt =
        $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = 'imssight'
            AND TABLE_NAME = ?
            AND INDEX_NAME = ?
        ");

    $stmt->execute([
        $tabla,
        $indice
    ]);

    if((int)$stmt->fetch()['total'] > 0){
        return;
    }

    $pdo->exec("
        CREATE INDEX $indice
        ON imssight.$tabla ($columnas)
    ");

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

    asegurarIndiceChat(
        $pdo,
        'notificaciones',
        'idx_notificaciones_usuario_estado',
        'id_usuario_destino, leida, fecha'
    );

}

if(!isset($_SESSION['usuario_id'])){

    echo json_encode([
        'ok' => false,
        'mensaje' => 'No autenticado'
    ]);

    exit;

}

try{

    asegurarTablaChat($pdo);
    asegurarTablaNotificaciones($pdo);

    $idUsuario =
        $_SESSION['usuario_id'];

    if($_SERVER['REQUEST_METHOD'] === 'GET'){

        if(($_GET['accion'] ?? '') === 'conversaciones'){

            $stmt =
                $pdo->prepare("
                    SELECT
                        u.id,
                        u.nombre,
                        u.matricula,
                        u.rol,
                        MAX(cm.fecha) AS ultima_fecha,
                        SUBSTRING_INDEX(
                            GROUP_CONCAT(cm.contenido ORDER BY cm.fecha DESC SEPARATOR '||'),
                            '||',
                            1
                        ) AS ultimo_mensaje,
                        SUM(
                            CASE
                                WHEN cm.id_destinatario = ?
                                AND cm.leido = 0
                                THEN 1
                                ELSE 0
                            END
                        ) AS sin_leer
                    FROM imssight.chat_mensajes cm
                    INNER JOIN imssight.usuarios u
                        ON u.id = CASE
                            WHEN cm.id_remitente = ?
                            THEN cm.id_destinatario
                            ELSE cm.id_remitente
                        END
                    WHERE
                        (
                            cm.id_remitente = ?
                            OR cm.id_destinatario = ?
                        )
                        AND u.activo = 1
                        AND u.id <> ?
                    GROUP BY
                        u.id,
                        u.nombre,
                        u.matricula,
                        u.rol
                    ORDER BY ultima_fecha DESC
                    LIMIT 30
                ");

            $stmt->execute([
                $idUsuario,
                $idUsuario,
                $idUsuario,
                $idUsuario,
                $idUsuario
            ]);

            echo json_encode([
                'ok' => true,
                'usuarios' =>
                    $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);

            exit;

        }

        if(($_GET['accion'] ?? '') === 'usuarios'){

            $q =
                trim($_GET['q'] ?? '');

            $stmt =
                $pdo->prepare("
                    SELECT id, nombre, matricula, rol
                    FROM imssight.usuarios
                    WHERE activo = 1
                    AND id <> ?
                    AND (
                        nombre LIKE ?
                        OR matricula LIKE ?
                        OR rol LIKE ?
                    )
                    ORDER BY nombre ASC
                    LIMIT 30
                ");

            $busqueda =
                '%' . $q . '%';

            $stmt->execute([
                $idUsuario,
                $busqueda,
                $busqueda,
                $busqueda
            ]);

            echo json_encode([
                'ok' => true,
                'usuarios' =>
                    $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);

            exit;

        }

        if(($_GET['accion'] ?? '') === 'usuario'){

            $idBuscado =
                (int)($_GET['id'] ?? 0);

            $stmt =
                $pdo->prepare("
                    SELECT id, nombre, matricula, rol
                    FROM imssight.usuarios
                    WHERE activo = 1
                    AND id <> ?
                    AND id = ?
                    LIMIT 1
                ");

            $stmt->execute([
                $idUsuario,
                $idBuscado
            ]);

            echo json_encode([
                'ok' => true,
                'usuario' =>
                    $stmt->fetch(PDO::FETCH_ASSOC) ?: null
            ]);

            exit;

        }

        $idConversacion =
            (int)($_GET['usuario_id'] ?? 0);

        if($idConversacion <= 0){

            echo json_encode([
                'ok' => true,
                'mensajes' => []
            ]);

            exit;

        }

        $stmt =
            $pdo->prepare("
                UPDATE imssight.chat_mensajes
                SET leido = 1
                WHERE id_remitente = ?
                AND id_destinatario = ?
            ");

        $stmt->execute([
            $idConversacion,
            $idUsuario
        ]);

        $stmt =
            $pdo->prepare("
                UPDATE imssight.notificaciones
                SET leida = 1
                WHERE id_usuario_destino = ?
                AND id_usuario_actor = ?
                AND tipo = 'mensaje_privado'
            ");

        $stmt->execute([
            $idUsuario,
            $idConversacion
        ]);

        $stmt =
            $pdo->prepare("
                SELECT
                    cm.id,
                    cm.id_remitente,
                    cm.id_destinatario,
                    cm.contenido,
                    cm.leido,
                    cm.fecha,
                    u.nombre AS remitente_nombre,
                    u.rol AS remitente_rol
                FROM imssight.chat_mensajes cm
                INNER JOIN imssight.usuarios u
                    ON u.id = cm.id_remitente
                WHERE
                    (
                        cm.id_remitente = ?
                        AND cm.id_destinatario = ?
                    )
                    OR
                    (
                        cm.id_remitente = ?
                        AND cm.id_destinatario = ?
                    )
                ORDER BY cm.fecha ASC
                LIMIT 200
            ");

        $stmt->execute([
            $idUsuario,
            $idConversacion,
            $idConversacion,
            $idUsuario
        ]);

        echo json_encode([
            'ok' => true,
            'mensajes' =>
                $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);

        exit;

    }

    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        $data =
            json_decode(
                file_get_contents('php://input'),
                true
            );

        $idDestinatario =
            (int)($data['id_destinatario'] ?? 0);

        $contenido =
            trim($data['contenido'] ?? '');

        if($idDestinatario <= 0 || $contenido === ''){

            echo json_encode([
                'ok' => false,
                'mensaje' => 'Mensaje inválido.'
            ]);

            exit;

        }

        if(strlen($contenido) > 2000){

            echo json_encode([
                'ok' => false,
                'mensaje' => 'El mensaje es demasiado largo.'
            ]);

            exit;

        }

        $stmt =
            $pdo->prepare("
                INSERT INTO imssight.chat_mensajes
                (
                    id_remitente,
                    id_destinatario,
                    contenido
                )
                VALUES
                (
                    ?, ?, ?
                )
            ");

        $stmt->execute([
            $idUsuario,
            $idDestinatario,
            $contenido
        ]);

        $stmt =
            $pdo->prepare("
                INSERT INTO imssight.notificaciones
                (
                    id_usuario_destino,
                    id_usuario_actor,
                    actor_nombre,
                    tipo,
                    titulo,
                    mensaje,
                    url
                )
                VALUES
                (
                    ?, ?, ?, 'mensaje_privado', ?, ?, ?
                )
            ");

        $stmt->execute([
            $idDestinatario,
            $idUsuario,
            $_SESSION['usuario_nombre'],
            'Nuevo mensaje privado',
            $_SESSION['usuario_nombre'] . ' te envió un mensaje.',
            '../pages/chat.html?usuario_id=' . $idUsuario
        ]);

        echo json_encode([
            'ok' => true,
            'mensaje' => 'Mensaje enviado.'
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
