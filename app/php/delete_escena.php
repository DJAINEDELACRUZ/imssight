<?php

header('Content-Type: application/json');

require 'conn.php';

try {

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    $id = $data['id'] ?? null;

    if (!$id) {
        throw new Exception('Falta el identificador de la escena.');
    }

    $pdo->beginTransaction();

    $deleteRespuestas = $pdo->prepare("
        DELETE FROM respuestas_usuario
        WHERE id_escena = ?
    ");

    $deleteRespuestas->execute([
        $id
    ]);

    $stmt = $pdo->prepare("
        DELETE FROM escenas
        WHERE id = ?
    ");

    $stmt->execute([
        $id
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('La escena ya no existe o no se encontró.');
    }

    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Escena eliminada definitivamente.'
    ]);

} catch(Exception $e) {

    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);

}
