<?php

header('Content-Type: application/json');

require 'conn.php';
require 'content_visibility.php';

function asegurarTablaInfografias(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS imssight.infografias_caso (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_caso INT NOT NULL,
            titulo VARCHAR(255) NOT NULL,
            descripcion TEXT,
            objetivo TEXT,
            identificador VARCHAR(120) NOT NULL,
            ruta_imagen VARCHAR(255) NOT NULL,
            mime_type VARCHAR(80),
            tamano_bytes INT DEFAULT 0,
            alt_text VARCHAR(255),
            color_sugerido VARCHAR(20) DEFAULT '#1f5f4f',
            orden INT DEFAULT 1,
            activo TINYINT DEFAULT 1,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_infografias_caso (id_caso, orden),
            UNIQUE KEY uq_infografias_identificador (identificador),
            CONSTRAINT fk_infografias_caso
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

function slugInfografia(string $texto): string
{
    $texto = trim(mb_strtolower($texto, 'UTF-8'));
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    $texto = trim($texto, '-');

    return $texto ?: 'infografia';
}

try {
    asegurarTablaInfografias($pdo);
    asegurarColumnasVisibilidadContenido($pdo);

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $idCaso = isset($_GET['id_caso']) ? (int) $_GET['id_caso'] : 0;
        $esAdmin = (isset($_GET['contexto']) && $_GET['contexto'] === 'admin')
            || (isset($_GET['admin']) && $_GET['admin'] === '1');
        $condicionVisibilidad = $esAdmin
            ? 'e.activo = 1'
            : condicionContenidoInterno('e');

        if ($idCaso > 0) {
            $sql = "
                SELECT
                    i.*,
                    c.id_tema,
                    t.id_especialidad,
                    c.titulo AS patologia,
                    c.descripcion AS caso_descripcion,
                    c.portada,
                    t.titulo AS subtema,
                    e.nombre AS tema
                FROM imssight.infografias_caso i
                INNER JOIN imssight.casos_clinicos c ON c.id = i.id_caso
                INNER JOIN imssight.temas t ON t.id = c.id_tema
                INNER JOIN imssight.especialidades e ON e.id = t.id_especialidad
                WHERE i.id_caso = ?
                  AND i.activo = 1
                  AND c.activo = 1
                  AND " . $condicionVisibilidad . "
                ORDER BY i.orden ASC, i.id ASC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idCaso]);
            responder($stmt->fetchAll(PDO::FETCH_ASSOC));
        }

        $sql = "
            SELECT
                i.*,
                c.id_tema,
                t.id_especialidad,
                c.titulo AS patologia,
                c.descripcion AS caso_descripcion,
                c.portada,
                t.titulo AS subtema,
                e.nombre AS tema
            FROM imssight.infografias_caso i
            INNER JOIN imssight.casos_clinicos c ON c.id = i.id_caso
            INNER JOIN imssight.temas t ON t.id = c.id_tema
            INNER JOIN imssight.especialidades e ON e.id = t.id_especialidad
            WHERE i.activo = 1
              AND c.activo = 1
              AND " . $condicionVisibilidad . "
            ORDER BY e.nombre ASC, t.titulo ASC, c.titulo ASC, i.orden ASC, i.id ASC
        ";
        responder($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($method === 'POST') {
        $idCaso = (int) ($_POST['id_caso'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $objetivo = trim($_POST['objetivo'] ?? '');
        $altText = trim($_POST['alt_text'] ?? '');
        $color = trim($_POST['color_sugerido'] ?? '#1f5f4f');
        $orden = max(1, (int) ($_POST['orden'] ?? 1));

        if ($idCaso <= 0 || $titulo === '') {
            responder([
                'ok' => false,
                'mensaje' => 'Selecciona un caso y escribe un título.'
            ]);
        }

        if (!isset($_FILES['imagen'])) {
            responder([
                'ok' => false,
                'mensaje' => 'Carga una imagen válida para la infografía.'
            ]);
        }

        $archivo = $_FILES['imagen'];

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $mensajeUpload = 'Carga una imagen válida para la infografía.';

            if ($archivo['error'] === UPLOAD_ERR_INI_SIZE || $archivo['error'] === UPLOAD_ERR_FORM_SIZE) {
                $mensajeUpload = 'La imagen supera el tamaño permitido por PHP. El contenedor queda configurado para aceptar hasta 8 MB al reconstruirse.';
            }

            responder([
                'ok' => false,
                'mensaje' => $mensajeUpload
            ]);
        }

        if ($archivo['size'] > 8 * 1024 * 1024) {
            responder([
                'ok' => false,
                'mensaje' => 'La imagen no debe superar 8 MB.'
            ]);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($archivo['tmp_name']);
        $extensiones = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif'
        ];

        if (!isset($extensiones[$mime])) {
            responder([
                'ok' => false,
                'mensaje' => 'Formato no permitido. Usa JPG, PNG, WEBP o GIF.'
            ]);
        }

        $directorio = __DIR__ . '/../img/infografias/casos';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0775, true);
        }

        $identificador = slugInfografia($titulo) . '-' . $idCaso . '-' . date('YmdHis');
        $nombreArchivo = $identificador . '.' . $extensiones[$mime];
        $destino = $directorio . '/' . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
            responder([
                'ok' => false,
                'mensaje' => 'No fue posible guardar el archivo de imagen.'
            ]);
        }

        $ruta = 'img/infografias/casos/' . $nombreArchivo;

        $stmt = $pdo->prepare("
            INSERT INTO imssight.infografias_caso
            (
                id_caso,
                titulo,
                descripcion,
                objetivo,
                identificador,
                ruta_imagen,
                mime_type,
                tamano_bytes,
                alt_text,
                color_sugerido,
                orden
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
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
            $objetivo,
            $identificador,
            $ruta,
            $mime,
            (int) $archivo['size'],
            $altText,
            $color,
            $orden
        ]);

        responder([
            'ok' => true,
            'mensaje' => 'Infografía cargada correctamente.',
            'id' => $pdo->lastInsertId()
        ]);
    }

    if ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $id = (int) ($data['id'] ?? ($_GET['id'] ?? 0));

        if ($id <= 0) {
            responder([
                'ok' => false,
                'mensaje' => 'Infografía no válida.'
            ]);
        }

        $stmt = $pdo->prepare("
            SELECT ruta_imagen
            FROM imssight.infografias_caso
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $infografia = $stmt->fetch(PDO::FETCH_ASSOC);

        $delete = $pdo->prepare("
            DELETE FROM imssight.infografias_caso
            WHERE id = ?
        ");
        $delete->execute([$id]);

        if ($infografia && !empty($infografia['ruta_imagen'])) {
            $rutaLocal = realpath(__DIR__ . '/../' . $infografia['ruta_imagen']);
            $base = realpath(__DIR__ . '/../img/infografias/casos');

            if ($rutaLocal && $base && strpos($rutaLocal, $base) === 0 && is_file($rutaLocal)) {
                unlink($rutaLocal);
            }
        }

        responder([
            'ok' => true,
            'mensaje' => 'Infografía eliminada.'
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
