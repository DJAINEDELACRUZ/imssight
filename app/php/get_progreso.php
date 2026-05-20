<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

if(!isset($_SESSION['usuario_id'])){

    echo json_encode([
        'porcentaje' => 0
    ]);

    exit;

}

$idUsuario =
    $_SESSION['usuario_id'];

$idCaso =
    $_GET['id_caso'] ?? 0;

# =====================================
# TOTAL DE PREGUNTAS DEL CASO
# =====================================

$sqlTotal = "

    SELECT COUNT(*) AS total

    FROM escenas

    WHERE

        id_caso = ?
        AND tipo = 'pregunta'

";

$stmtTotal =
    $pdo->prepare($sqlTotal);

$stmtTotal->execute([$idCaso]);

$total =
    $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

# =====================================
# RESPUESTAS CORRECTAS
# =====================================

$sqlCorrectas = "

    SELECT COUNT(*) AS correctas

    FROM respuestas_usuario ru

    INNER JOIN escenas e
        ON ru.id_escena = e.id

    WHERE

        e.id_caso = ?
        AND ru.id_usuario = ?
        AND ru.correcta = 1

";

$stmtCorrectas =
    $pdo->prepare($sqlCorrectas);

$stmtCorrectas->execute([

    $idCaso,
    $idUsuario

]);

$correctas =
    $stmtCorrectas->fetch(PDO::FETCH_ASSOC)['correctas'];

# =====================================
# CALCULAR PORCENTAJE
# =====================================

$porcentaje = 0;

if($total > 0){

    $porcentaje =
        round(
            ($correctas / $total) * 100
        );

}

echo json_encode([

    'total' => $total,
    'correctas' => $correctas,
    'porcentaje' => $porcentaje

]);