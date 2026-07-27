<?php

header('Content-Type: application/json');

require 'conn.php';
require 'content_visibility.php';

function asegurarTablaEscalas(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS imssight.escalas_pronosticas_caso (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_caso INT NOT NULL,
            titulo VARCHAR(255) NOT NULL,
            descripcion TEXT,
            url VARCHAR(600) NOT NULL,
            proveedor VARCHAR(120),
            orden INT DEFAULT 1,
            activo TINYINT DEFAULT 1,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_escalas_caso (id_caso, orden),
            CONSTRAINT fk_escalas_caso
                FOREIGN KEY (id_caso)
                REFERENCES imssight.casos_clinicos(id)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function responder($payload): void
{
    echo json_encode($payload);
    exit;
}

function normalizarUrlEscala(string $url): string
{
    $url = trim($url);

    if ($url !== '' && !preg_match('/^https?:\/\//i', $url)) {
        $url = 'https://' . $url;
    }

    return $url;
}

try {
    asegurarTablaEscalas($pdo);
    asegurarColumnasVisibilidadContenido($pdo);

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $idCaso = isset($_GET['id_caso']) ? (int) $_GET['id_caso'] : 0;

        if ($idCaso > 0) {
            $sql = "
                SELECT
                    s.*,
                    c.id_tema,
                    t.id_especialidad,
                    c.titulo AS caso,
                    c.descripcion AS caso_descripcion,
                    c.portada,
                    t.titulo AS tema,
                    e.nombre AS especialidad
                FROM imssight.escalas_pronosticas_caso s
                INNER JOIN imssight.casos_clinicos c ON c.id = s.id_caso
                INNER JOIN imssight.temas t ON t.id = c.id_tema
                INNER JOIN imssight.especialidades e ON e.id = t.id_especialidad
                WHERE s.id_caso = ?
                  AND s.activo = 1
                  AND c.activo = 1
                  AND " . condicionContenidoInterno('e') . "
                ORDER BY s.orden ASC, s.id ASC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idCaso]);
            responder($stmt->fetchAll(PDO::FETCH_ASSOC));
        }

        $sql = "
            SELECT
                s.*,
                c.id_tema,
                t.id_especialidad,
                c.titulo AS caso,
                c.descripcion AS caso_descripcion,
                c.portada,
                t.titulo AS tema,
                e.nombre AS especialidad
            FROM imssight.escalas_pronosticas_caso s
            INNER JOIN imssight.casos_clinicos c ON c.id = s.id_caso
            INNER JOIN imssight.temas t ON t.id = c.id_tema
            INNER JOIN imssight.especialidades e ON e.id = t.id_especialidad
            WHERE s.activo = 1
              AND c.activo = 1
              AND " . condicionContenidoInterno('e') . "
            ORDER BY e.nombre ASC, t.titulo ASC, c.titulo ASC, s.orden ASC, s.id ASC
        ";
        responder($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $idCaso = (int) ($data['id_caso'] ?? 0);
        $titulo = trim($data['titulo'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $url = normalizarUrlEscala($data['url'] ?? '');
        $proveedor = trim($data['proveedor'] ?? '');
        $orden = max(1, (int) ($data['orden'] ?? 1));

        if ($idCaso <= 0 || $titulo === '' || $url === '') {
            responder([
                'ok' => false,
                'mensaje' => 'Selecciona un caso, escribe título y pega el enlace.'
            ]);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            responder([
                'ok' => false,
                'mensaje' => 'El enlace de la escala no es válido.'
            ]);
        }

        $stmt = $pdo->prepare("
            INSERT INTO imssight.escalas_pronosticas_caso
            (
                id_caso,
                titulo,
                descripcion,
                url,
                proveedor,
                orden
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");

        $stmt->execute([
            $idCaso,
            $titulo,
            $descripcion,
            $url,
            $proveedor,
            $orden
        ]);

        responder([
            'ok' => true,
            'mensaje' => 'Escala pronóstica guardada correctamente.',
            'id' => $pdo->lastInsertId()
        ]);
    }

    if ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = (int) ($data['id'] ?? ($_GET['id'] ?? 0));

        if ($id <= 0) {
            responder([
                'ok' => false,
                'mensaje' => 'Escala no válida.'
            ]);
        }

        $delete = $pdo->prepare("
            DELETE FROM imssight.escalas_pronosticas_caso
            WHERE id = ?
        ");
        $delete->execute([$id]);

        responder([
            'ok' => true,
            'mensaje' => 'Escala eliminada.'
        ]);
    }

    responder([
        'ok' => false,
        'mensaje' => 'Método no soportado.'
    ]);
} catch (Exception $e) {
    responder([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);
}
