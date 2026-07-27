<?php

function asegurarColumnaRecorridoIndex($pdo){

    $stmt =
        $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'usuarios'
            AND COLUMN_NAME = 'recorrido_index_visto'
        ");

    $stmt->execute();

    $existe =
        (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

    if(!$existe){

        $pdo->exec("
            ALTER TABLE imssight.usuarios
            ADD COLUMN recorrido_index_visto TINYINT DEFAULT 0
        ");

    }

}
