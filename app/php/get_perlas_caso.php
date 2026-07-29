<?php

header('Content-Type: application/json');

require 'conn.php';

function htmlTieneContenido($html)
{
    $texto = html_entity_decode(strip_tags((string) ($html ?? '')), ENT_QUOTES, 'UTF-8');
    $texto = preg_replace('/\x{00a0}/u', ' ', $texto);

    return trim($texto) !== '';
}

try {

    $id_caso = $_GET['id_caso'] ?? 0;

    $sql = "

        SELECT
            id,
            id_caso,
            seccion,
            contenido
        FROM imssight.perlas_clinicas_caso
        WHERE id_caso = ?
        ORDER BY orden ASC

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $id_caso
    ]);

    $perlas = array_values(array_filter(
        $stmt->fetchAll(PDO::FETCH_ASSOC),
        static function ($perla) {
            return htmlTieneContenido($perla['contenido'] ?? '');
        }
    ));

    echo json_encode($perlas);

} catch(Exception $e){

    echo json_encode([]);

}
