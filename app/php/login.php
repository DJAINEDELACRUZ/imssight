<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

$data =
    json_decode(
        file_get_contents(
            "php://input"
        ),
        true
    );

$data =
    is_array($data)
        ? $data
        : [];

$matricula =
    trim((string)($data['matricula'] ?? ''));

$password =
    (string)($data['password'] ?? '');

function contieneIntentoSqlLogin(string $valor): bool
{
    $patrones = [
        "/(?:'|\"|`)\\s*(?:or|and)\\s*(?:'|\"|`)?[a-z0-9_]+(?:'|\"|`)?\\s*=\\s*(?:'|\"|`)?[a-z0-9_]+/i",
        "/\\b(?:or|and)\\b\\s+\\d+\\s*=\\s*\\d+/i",
        "/\\b(?:or|and)\\b\\s+(?:true|false|null)\\b/i",
        "/(?:'|\"|`)\\s*=\\s*(?:'|\"|`)/",
        "/\\bunion\\b\\s+(?:all\\s+)?\\bselect\\b/i",
        "/;\\s*(?:select|insert|update|delete|drop|alter|create|truncate)\\b/i",
        "/(?:--|#|\\/\\*)/",
        "/\\b(?:sleep|benchmark|load_file|information_schema)\\b/i"
    ];

    foreach($patrones as $patron){
        if(preg_match($patron, $valor)){
            return true;
        }
    }

    return false;
}

if(
    contieneIntentoSqlLogin($matricula) ||
    contieneIntentoSqlLogin($password)
){
    echo json_encode([
        'success' => false,
        'security_alert' => true,
        'message' => 'Buen intento jajajajajaa... no nací ayer.'
    ]);

    exit;
}

$sql = "

    SELECT *

    FROM usuarios

    WHERE matricula = ?

    LIMIT 1

";

$stmt =
    $pdo->prepare($sql);

$stmt->execute([
    $matricula
]);

$usuario =
    $stmt->fetch();

if(

    $usuario &&

    password_verify(
        $password,
        $usuario['password']
    )

){

    $_SESSION['usuario_id'] =
        $usuario['id'];

    $_SESSION['usuario_nombre'] =
        $usuario['nombre'];

    $_SESSION['rol'] =
        $usuario['rol'];

    echo json_encode([

        'success' => true

    ]);

} else {

    echo json_encode([

        'success' => false,

        'message' =>
            'Credenciales incorrectas'

    ]);

}
