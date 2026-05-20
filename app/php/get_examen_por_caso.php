<?php

require 'conn.php';

$idCaso = $_GET['id_caso'];

$sql = "

SELECT *

FROM examenes

WHERE id_caso = ?

LIMIT 1

";

$stmt = $pdo->prepare($sql);

$stmt->execute([$idCaso]);

echo json_encode(

    $stmt->fetch(PDO::FETCH_ASSOC)

);