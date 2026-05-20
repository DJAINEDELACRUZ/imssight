<?php

header('Content-Type: application/json');

require 'conn.php';

$idCaso = $_GET['id'] ?? 0;

try {

    // Forzar entero
    $idCaso = (int)$idCaso;

    $sql = "
        SELECT
            id,
            id_caso,
            orden,
            tipo,
            titulo,
            contenido,
            multimedia
        FROM escenas
        WHERE id_caso = ?
        ORDER BY orden ASC
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$idCaso]);

    $escenas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($escenas);

} catch(Exception $e) {

    echo json_encode([

        'success' => false,

        'error' => $e->getMessage(),

        'idCasoRecibido' => $idCaso

    ]);

}
?>