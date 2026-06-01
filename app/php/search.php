<?php

header('Content-Type: application/json');

require 'conn.php';

function tieneIndiceBusqueda($pdo){

    $stmt =
        $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = 'imssight'
            AND TABLE_NAME = 'search_index'
            AND INDEX_NAME = 'idx_search_fulltext'
        ");

    $stmt->execute();

    return (int)$stmt->fetch()['total'] > 0;

}

function consultaBooleana($texto){

    $terminos =
        preg_split('/\s+/u', trim($texto));

    $limpios = [];

    foreach($terminos as $termino){

        $termino =
            preg_replace('/[^\p{L}\p{N}_]+/u', '', $termino);

        if(mb_strlen($termino, 'UTF-8') < 3){
            continue;
        }

        $limpios[] =
            '+' . $termino . '*';

    }

    return implode(' ', $limpios);

}

$q = $_GET['q'] ?? '';

if(!$q){

    echo json_encode([]);

    exit;

}

$buscar =
    "%$q%";

$booleanQuery =
    consultaBooleana($q);

if($booleanQuery !== '' && tieneIndiceBusqueda($pdo)){

    $sql = "

    SELECT *,
        MATCH(titulo, contenido, descripcion)
        AGAINST (? IN BOOLEAN MODE) AS relevancia

    FROM search_index

    WHERE
        MATCH(titulo, contenido, descripcion)
        AGAINST (? IN BOOLEAN MODE)
        OR titulo LIKE ?
        OR descripcion LIKE ?

    ORDER BY relevancia DESC, id DESC

    LIMIT 50

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $booleanQuery,
        $booleanQuery,
        $buscar,
        $buscar
    ]);

}
else{

    $sql = "

SELECT *

FROM search_index

WHERE

    titulo LIKE ?

    OR contenido LIKE ?

    OR descripcion LIKE ?

LIMIT 50

";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([

        $buscar,
        $buscar,
        $buscar

    ]);

}

$resultados = $stmt->fetchAll();

echo json_encode($resultados);
