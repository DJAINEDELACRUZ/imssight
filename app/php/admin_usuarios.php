<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require 'conn.php';

function responder($ok, $payload = [], $status = 200){
    http_response_code($status);
    echo json_encode(
        array_merge(['ok' => $ok], $payload),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

if(!isset($_SESSION['usuario_id'])){
    responder(false, ['mensaje' => 'No autenticado'], 401);
}

if(($_SESSION['rol'] ?? '') !== 'admin'){
    responder(false, ['mensaje' => 'Solo administradores'], 403);
}

function normalizarRol($rol){
    $rol = strtolower(trim((string)$rol));
    return in_array($rol, ['admin','docente','usuario'], true)
        ? $rol
        : null;
}

try{
    if($_SERVER['REQUEST_METHOD'] === 'GET'){
        $q =
            trim($_GET['q'] ?? '');

        $params = [];

        $sql = "
            SELECT
                u.id,
                u.nombre,
                u.matricula,
                u.rol,
                u.activo,
                p.hospital,
                p.puesto,
                p.especialidad,
                p.estado
            FROM imssight.usuarios u
            LEFT JOIN imssight.usuarios_perfil p
                ON p.id_usuario = u.id
        ";

        if($q !== ''){
            $sql .= "
                WHERE
                    u.nombre LIKE ?
                    OR u.matricula LIKE ?
                    OR u.rol LIKE ?
                    OR p.puesto LIKE ?
                    OR p.especialidad LIKE ?
            ";

            $busqueda = '%' . $q . '%';
            $params = [
                $busqueda,
                $busqueda,
                $busqueda,
                $busqueda,
                $busqueda
            ];
        }

        $sql .= "
            ORDER BY
                FIELD(u.rol, 'admin', 'docente', 'usuario'),
                u.nombre ASC
            LIMIT 300
        ";

        $stmt =
            $pdo->prepare($sql);

        $stmt->execute($params);

        responder(true, [
            'usuarios' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $data =
            json_decode(
                file_get_contents('php://input'),
                true
            ) ?: [];

        $id =
            (int)($data['id'] ?? 0);

        $rol =
            normalizarRol($data['rol'] ?? '');

        $activo =
            isset($data['activo'])
                ? (int)((bool)$data['activo'])
                : null;

        if($id <= 0 || !$rol || $activo === null){
            responder(false, ['mensaje' => 'Datos inválidos'], 422);
        }

        $stmt =
            $pdo->prepare("
                SELECT id, rol, activo
                FROM imssight.usuarios
                WHERE id = ?
                LIMIT 1
            ");

        $stmt->execute([$id]);

        $usuario =
            $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$usuario){
            responder(false, ['mensaje' => 'Usuario no encontrado'], 404);
        }

        $stmt =
            $pdo->query("
                SELECT COUNT(*) AS total
                FROM imssight.usuarios
                WHERE rol = 'admin'
                AND activo = 1
            ");

        $adminsActivos =
            (int)$stmt->fetch()['total'];

        $quitariaUltimoAdmin =
            (int)$usuario['activo'] === 1
            && $usuario['rol'] === 'admin'
            && (
                $rol !== 'admin'
                || $activo !== 1
            )
            && $adminsActivos <= 1;

        if($quitariaUltimoAdmin){
            responder(false, [
                'mensaje' => 'No puedes quitar el último administrador activo.'
            ], 409);
        }

        $stmt =
            $pdo->prepare("
                UPDATE imssight.usuarios
                SET rol = ?, activo = ?
                WHERE id = ?
            ");

        $stmt->execute([
            $rol,
            $activo,
            $id
        ]);

        responder(true, [
            'mensaje' => 'Permisos actualizados.'
        ]);
    }

    responder(false, ['mensaje' => 'Método no permitido'], 405);
}
catch(Exception $e){
    responder(false, ['mensaje' => $e->getMessage()], 500);
}
