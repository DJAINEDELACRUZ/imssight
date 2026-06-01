<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

function asegurarTablaPerfilEntradas($pdo){

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

}

if(!isset($_SESSION['usuario_id'])){

    echo json_encode([
        'ok' => false,
        'mensaje' => 'No autenticado'
    ]);

    exit;

}

asegurarTablaPerfilEntradas($pdo);

$idSesion =
    (int)$_SESSION['usuario_id'];

if($_SERVER['REQUEST_METHOD'] === 'GET'){

    $idUsuario =
        (int)($_GET['id_usuario'] ?? $idSesion);

    $stmt =
        $pdo->prepare("
            SELECT
                id,
                id_usuario,
                tipo,
                titulo,
                contenido,
                fecha
            FROM imssight.perfil_entradas
            WHERE id_usuario = ?
            AND activo = 1
            ORDER BY fecha DESC
            LIMIT 20
        ");

    $stmt->execute([$idUsuario]);

    echo json_encode([
        'ok' => true,
        'puede_editar' => $idUsuario === $idSesion,
        'entradas' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);

    exit;

}

$data =
    json_decode(
        file_get_contents('php://input'),
        true
    ) ?: [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $tipo =
        $data['tipo'] ?? 'reflexion';

    $tiposPermitidos =
        ['frase','reflexion','historia','aprendizaje'];

    if(!in_array($tipo, $tiposPermitidos, true)){
        $tipo = 'reflexion';
    }

    $titulo =
        trim($data['titulo'] ?? '');

    $contenido =
        trim($data['contenido'] ?? '');

    if($contenido === ''){

        echo json_encode([
            'ok' => false,
            'mensaje' => 'Escribe algo para publicar.'
        ]);

        exit;

    }

    if(strlen($titulo) > 180){
        $titulo = substr($titulo, 0, 180);
    }

    if(strlen($contenido) > 2200){

        echo json_encode([
            'ok' => false,
            'mensaje' => 'La entrada es demasiado larga.'
        ]);

        exit;

    }

    $stmt =
        $pdo->prepare("
            INSERT INTO imssight.perfil_entradas
            (
                id_usuario,
                tipo,
                titulo,
                contenido
            )
            VALUES
            (
                ?, ?, ?, ?
            )
        ");

    $stmt->execute([
        $idSesion,
        $tipo,
        $titulo !== '' ? $titulo : null,
        $contenido
    ]);

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Entrada publicada.'
    ]);

    exit;

}

if($_SERVER['REQUEST_METHOD'] === 'DELETE'){

    $id =
        (int)($data['id'] ?? 0);

    $stmt =
        $pdo->prepare("
            UPDATE imssight.perfil_entradas
            SET activo = 0
            WHERE id = ?
            AND id_usuario = ?
        ");

    $stmt->execute([
        $id,
        $idSesion
    ]);

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Entrada eliminada.'
    ]);

    exit;

}

echo json_encode([
    'ok' => false,
    'mensaje' => 'Método no soportado.'
]);
