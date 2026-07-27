<?php

session_start();

header('Content-Type: application/json');

if(!isset($_SESSION['usuario_id'])){

    http_response_code(401);

    echo json_encode([
        'ok' => false,
        'mensaje' => 'Sesión no válida'
    ]);

    exit;

}

require_once 'conn.php';
require_once 'recorrido_utils.php';

try{

    asegurarColumnaRecorridoIndex($pdo);

    $data =
        json_decode(
            file_get_contents('php://input'),
            true
        ) ?: [];

    $recorridoVisto =
        !empty($data['recorrido_index_visto'])
            ? 1
            : 0;

    $stmt =
        $pdo->prepare("
            UPDATE imssight.usuarios
            SET recorrido_index_visto = ?
            WHERE id = ?
        ");

    $stmt->execute([
        $recorridoVisto,
        $_SESSION['usuario_id']
    ]);

    echo json_encode([
        'ok' => true,
        'recorrido_index_visto' => $recorridoVisto
    ]);

}catch(PDOException $e){

    http_response_code(500);

    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);

}
