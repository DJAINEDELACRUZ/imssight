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

    if($_SERVER['REQUEST_METHOD'] === 'GET'){

        $id_caso = $_GET['id_caso'] ?? 0;

        $sql = "
            SELECT
                id,
                id_caso,
                seccion,
                contenido,
                orden
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

        exit;
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        $data = json_decode(
            file_get_contents('php://input'),
            true
        );

        $id_caso = $data['id_caso'];
        $perlas = $data['perlas'];

        $pdo->beginTransaction();

        $delete = $pdo->prepare("
            DELETE FROM imssight.perlas_clinicas_caso
            WHERE id_caso = ?
        ");

        $delete->execute([
            $id_caso
        ]);

        $insert = $pdo->prepare("
            INSERT INTO imssight.perlas_clinicas_caso
            (
                id_caso,
                seccion,
                contenido,
                orden
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?
            )
        ");

        foreach($perlas as $perla){

            if(!htmlTieneContenido($perla['contenido'] ?? '')){
                continue;
            }

            $insert->execute([
                $id_caso,
                $perla['seccion'],
                $perla['contenido'],
                $perla['orden']
            ]);

        }

        $pdo->commit();

        echo json_encode([
            'ok' => true,
            'mensaje' => 'Perlas clínicas guardadas correctamente 😄'
        ]);

        exit;
    }

} catch(Exception $e){

    if($pdo->inTransaction()){
        $pdo->rollBack();
    }

    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);

}
