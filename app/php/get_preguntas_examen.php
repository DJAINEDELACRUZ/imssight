<?php

require 'conn.php';

$idExamen = $_GET['id'];

$sql = "

SELECT

    ep.*,
    e.titulo AS titulo_examen

FROM examen_preguntas ep

LEFT JOIN examenes e
ON e.id = ep.id_examen

WHERE ep.id_examen = ?

ORDER BY ep.orden_pregunta ASC

";

$stmt = $pdo->prepare($sql);

$stmt->execute([$idExamen]);

echo json_encode(

    $stmt->fetchAll(PDO::FETCH_ASSOC)

);