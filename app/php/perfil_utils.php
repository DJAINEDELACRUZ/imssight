<?php

function asegurarColumnasPerfilPublico($pdo){

    $columnas = [
        'perfil_publico' => "TINYINT DEFAULT 1",
        'mostrar_correo' => "TINYINT DEFAULT 0",
        'mostrar_telefono' => "TINYINT DEFAULT 0",
        'mostrar_estado' => "TINYINT DEFAULT 1",
        'mostrar_biografia' => "TINYINT DEFAULT 1",
        'hospital' => "VARCHAR(255) NULL",
        'cumpleanos_publico' => "VARCHAR(40) NULL",
        'puesto' => "VARCHAR(150) NULL",
        'etapa_profesional' => "VARCHAR(100) NULL",
        'intereses' => "TEXT NULL",
        'frase_perfil' => "VARCHAR(255) NULL"
    ];

    $stmt =
        $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'usuarios_perfil'
            AND COLUMN_NAME = ?
        ");

    foreach($columnas as $nombre => $definicion){

        $stmt->execute([$nombre]);

        $existe =
            (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

        if(!$existe){
            $pdo->exec("
                ALTER TABLE imssight.usuarios_perfil
                ADD COLUMN $nombre $definicion
            ");
        }

    }

}
