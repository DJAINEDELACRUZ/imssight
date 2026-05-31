<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

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

if(!isset($_SESSION['usuario_id'])){

    echo json_encode([
        'ok' => false,
        'mensaje' => 'No autenticado'
    ]);

    exit;

}

try{

    asegurarTablaNotificaciones($pdo);

    $idUsuario =
        $_SESSION['usuario_id'];

    if($_SERVER['REQUEST_METHOD'] === 'PATCH'){

        $data =
            json_decode(
                file_get_contents('php://input'),
                true
            );

        if(($data['accion'] ?? '') === 'leer_publicacion'){

            $idPublicacion =
                (int)($data['id_publicacion'] ?? 0);

            $stmt =
                $pdo->prepare("
                    UPDATE imssight.notificaciones
                    SET leida = 1
                    WHERE id_usuario_destino = ?
                    AND id_publicacion = ?
                ");

            $stmt->execute([
                $idUsuario,
                $idPublicacion
            ]);

            echo json_encode([
                'ok' => true
            ]);

            exit;

        }

        $id =
            (int)($data['id'] ?? 0);

        $stmt =
            $pdo->prepare("
                UPDATE imssight.notificaciones
                SET leida = 1
                WHERE id = ?
                AND id_usuario_destino = ?
            ");

        $stmt->execute([
            $id,
            $idUsuario
        ]);

        echo json_encode([
            'ok' => true
        ]);

        exit;

    }

    $notificaciones = [];

    $perfilStmt =
        $pdo->prepare("
            SELECT id
            FROM imssight.usuarios_perfil
            WHERE id_usuario = ?
            LIMIT 1
        ");

    $perfilStmt->execute([$idUsuario]);

    if(!$perfilStmt->fetch(PDO::FETCH_ASSOC)){

        $notificaciones[] = [
            'id' => 'perfil',
            'tipo' => 'perfil',
            'titulo' => 'Completa tu registro',
            'mensaje' => 'Agrega tu información académica y personal.',
            'url' => '../pages/user_profile.html',
            'leida' => 0,
            'fecha' => null,
            'icono' => 'warning'
        ];

    }

    $stmt =
        $pdo->prepare("
            SELECT
                id,
                tipo,
                titulo,
                mensaje,
                url,
                id_publicacion,
                id_comentario,
                leida,
                fecha
            FROM imssight.notificaciones
            WHERE id_usuario_destino = ?
            ORDER BY leida ASC, fecha DESC
            LIMIT 20
        ");

    $stmt->execute([$idUsuario]);

    foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $notificacion){

        $notificacion['icono'] =
            $notificacion['tipo'] === 'comentario_muro'
                ? 'forum'
                : 'notifications';

        $notificaciones[] =
            $notificacion;

    }

    $noLeidas = 0;

    foreach($notificaciones as $notificacion){
        if((int)$notificacion['leida'] === 0){
            $noLeidas++;
        }
    }

    echo json_encode([
        'ok' => true,
        'no_leidas' => $noLeidas,
        'notificaciones' => $notificaciones
    ]);

}
catch(Exception $e){

    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);

}
