<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

require 'conn.php';

function responderPodcast(bool $ok, array $payload = [], int $status = 200): void
{
    http_response_code($status);
    echo json_encode(
        array_merge(['ok' => $ok], $payload),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

function asegurarTablaPodcast(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS imssight.podcast_videos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(255) NOT NULL,
            descripcion TEXT,
            youtube_video_id VARCHAR(20) NOT NULL,
            embed_url VARCHAR(500) NOT NULL,
            serie VARCHAR(100) DEFAULT 'ResiTalks',
            ponente VARCHAR(180),
            duracion VARCHAR(30),
            es_principal TINYINT DEFAULT 0,
            activo TINYINT DEFAULT 1,
            creado_por INT NULL,
            actualizado_por INT NULL,
            fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_podcast_catalogo
                (activo, es_principal, fecha_publicacion, id),
            UNIQUE KEY uq_podcast_youtube_video
                (youtube_video_id),
            CONSTRAINT fk_podcast_creado_por
                FOREIGN KEY (creado_por)
                REFERENCES imssight.usuarios(id)
                ON DELETE SET NULL,
            CONSTRAINT fk_podcast_actualizado_por
                FOREIGN KEY (actualizado_por)
                REFERENCES imssight.usuarios(id)
                ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $total = (int) $pdo
        ->query("SELECT COUNT(*) FROM imssight.podcast_videos")
        ->fetchColumn();

    if ($total === 0) {
        $stmt = $pdo->prepare("
            INSERT INTO imssight.podcast_videos
            (
                titulo,
                descripcion,
                youtube_video_id,
                embed_url,
                serie,
                es_principal
            )
            VALUES (?, ?, ?, ?, ?, 1)
        ");

        $stmt->execute([
            'ResiTalks: episodio destacado',
            'Conversaciones y contenidos audiovisuales para la formación clínica.',
            'VMKEj1JLltg',
            'https://www.youtube-nocookie.com/embed/VMKEj1JLltg',
            'ResiTalks'
        ]);
    }
}

function requerirSesionPodcast(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        responderPodcast(false, ['mensaje' => 'No autenticado'], 401);
    }
}

function requerirAdminPodcast(): void
{
    requerirSesionPodcast();

    if (strtolower((string) ($_SESSION['rol'] ?? '')) !== 'admin') {
        responderPodcast(false, ['mensaje' => 'Solo administradores'], 403);
    }
}

function extraerVideoIdYoutube(string $entrada): ?string
{
    $entrada = html_entity_decode(trim($entrada), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    if (preg_match('/<iframe\b[^>]*\bsrc=["\']([^"\']+)["\']/i', $entrada, $match)) {
        $entrada = $match[1];
    }

    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $entrada)) {
        return $entrada;
    }

    if (!preg_match('/^https?:\/\//i', $entrada)) {
        return null;
    }

    $partes = parse_url($entrada);
    $host = strtolower((string) ($partes['host'] ?? ''));
    $host = preg_replace('/^www\./', '', $host);
    $path = trim((string) ($partes['path'] ?? ''), '/');

    if ($host === 'youtu.be') {
        $candidato = explode('/', $path)[0] ?? '';
    } elseif (in_array($host, ['youtube.com', 'm.youtube.com', 'youtube-nocookie.com'], true)) {
        parse_str((string) ($partes['query'] ?? ''), $query);

        if (str_starts_with($path, 'embed/')) {
            $candidato = explode('/', substr($path, 6))[0] ?? '';
        } elseif (str_starts_with($path, 'shorts/')) {
            $candidato = explode('/', substr($path, 7))[0] ?? '';
        } else {
            $candidato = (string) ($query['v'] ?? '');
        }
    } else {
        return null;
    }

    return preg_match('/^[A-Za-z0-9_-]{11}$/', $candidato)
        ? $candidato
        : null;
}

function datosPodcastEntrada(array $data): array
{
    $titulo = trim((string) ($data['titulo'] ?? ''));
    $descripcion = trim((string) ($data['descripcion'] ?? ''));
    $entradaYoutube = trim((string) ($data['iframe'] ?? $data['url'] ?? ''));
    $serie = trim((string) ($data['serie'] ?? 'ResiTalks'));
    $ponente = trim((string) ($data['ponente'] ?? ''));
    $duracion = trim((string) ($data['duracion'] ?? ''));
    $videoId = extraerVideoIdYoutube($entradaYoutube);

    if ($titulo === '' || !$videoId) {
        responderPodcast(false, [
            'mensaje' => 'Escribe un título y pega un iframe o enlace válido de YouTube.'
        ], 422);
    }

    if (mb_strlen($titulo) > 255 || mb_strlen($serie) > 100) {
        responderPodcast(false, ['mensaje' => 'El título o la serie son demasiado largos.'], 422);
    }

    if (mb_strlen($ponente) > 180 || mb_strlen($duracion) > 30) {
        responderPodcast(false, ['mensaje' => 'Ponente o duración no válidos.'], 422);
    }

    return [
        'titulo' => $titulo,
        'descripcion' => $descripcion,
        'youtube_video_id' => $videoId,
        'embed_url' => 'https://www.youtube-nocookie.com/embed/' . $videoId,
        'serie' => $serie !== '' ? $serie : 'ResiTalks',
        'ponente' => $ponente,
        'duracion' => $duracion
    ];
}

function obtenerCatalogoPodcast(PDO $pdo): array
{
    $stmt = $pdo->query("
        SELECT
            id,
            titulo,
            descripcion,
            youtube_video_id,
            embed_url,
            serie,
            ponente,
            duracion,
            es_principal,
            fecha_publicacion,
            fecha_actualizacion
        FROM imssight.podcast_videos
        WHERE activo = 1
        ORDER BY
            es_principal DESC,
            fecha_publicacion DESC,
            id DESC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    asegurarTablaPodcast($pdo);
    requerirSesionPodcast();

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        responderPodcast(true, [
            'es_admin' => strtolower((string) ($_SESSION['rol'] ?? '')) === 'admin',
            'videos' => obtenerCatalogoPodcast($pdo)
        ]);
    }

    requerirAdminPodcast();
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $usuarioId = (int) $_SESSION['usuario_id'];

    if ($method === 'POST') {
        $video = datosPodcastEntrada($data);

        $pdo->beginTransaction();
        $pdo->exec("
            UPDATE imssight.podcast_videos
            SET es_principal = 0
            WHERE es_principal = 1
        ");

        $stmt = $pdo->prepare("
            INSERT INTO imssight.podcast_videos
            (
                titulo,
                descripcion,
                youtube_video_id,
                embed_url,
                serie,
                ponente,
                duracion,
                es_principal,
                creado_por,
                actualizado_por
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?)
        ");

        $stmt->execute([
            $video['titulo'],
            $video['descripcion'],
            $video['youtube_video_id'],
            $video['embed_url'],
            $video['serie'],
            $video['ponente'],
            $video['duracion'],
            $usuarioId,
            $usuarioId
        ]);

        $pdo->commit();

        responderPodcast(true, [
            'mensaje' => 'Video publicado como principal.',
            'videos' => obtenerCatalogoPodcast($pdo)
        ], 201);
    }

    if ($method === 'PUT') {
        $id = (int) ($data['id'] ?? 0);
        $video = datosPodcastEntrada($data);
        $hacerPrincipal = !empty($data['es_principal']);

        if ($id <= 0) {
            responderPodcast(false, ['mensaje' => 'Video no válido.'], 422);
        }

        $pdo->beginTransaction();

        if ($hacerPrincipal) {
            $pdo->exec("
                UPDATE imssight.podcast_videos
                SET es_principal = 0
                WHERE es_principal = 1
            ");
        }

        $stmt = $pdo->prepare("
            UPDATE imssight.podcast_videos
            SET
                titulo = ?,
                descripcion = ?,
                youtube_video_id = ?,
                embed_url = ?,
                serie = ?,
                ponente = ?,
                duracion = ?,
                es_principal = CASE WHEN ? = 1 THEN 1 ELSE es_principal END,
                actualizado_por = ?
            WHERE id = ?
              AND activo = 1
        ");

        $stmt->execute([
            $video['titulo'],
            $video['descripcion'],
            $video['youtube_video_id'],
            $video['embed_url'],
            $video['serie'],
            $video['ponente'],
            $video['duracion'],
            $hacerPrincipal ? 1 : 0,
            $usuarioId,
            $id
        ]);

        $pdo->commit();

        responderPodcast(true, [
            'mensaje' => 'Video actualizado.',
            'videos' => obtenerCatalogoPodcast($pdo)
        ]);
    }

    if ($method === 'DELETE') {
        $id = (int) ($data['id'] ?? 0);

        if ($id <= 0) {
            responderPodcast(false, ['mensaje' => 'Video no válido.'], 422);
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            SELECT es_principal
            FROM imssight.podcast_videos
            WHERE id = ?
              AND activo = 1
            FOR UPDATE
        ");
        $stmt->execute([$id]);
        $actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$actual) {
            $pdo->rollBack();
            responderPodcast(false, ['mensaje' => 'Video no encontrado.'], 404);
        }

        $stmt = $pdo->prepare("
            UPDATE imssight.podcast_videos
            SET activo = 0, es_principal = 0, actualizado_por = ?
            WHERE id = ?
        ");
        $stmt->execute([$usuarioId, $id]);

        if ((int) $actual['es_principal'] === 1) {
            $pdo->exec("
                UPDATE imssight.podcast_videos
                SET es_principal = 1
                WHERE activo = 1
                ORDER BY fecha_publicacion DESC, id DESC
                LIMIT 1
            ");
        }

        $pdo->commit();

        responderPodcast(true, [
            'mensaje' => 'Video retirado del catálogo.',
            'videos' => obtenerCatalogoPodcast($pdo)
        ]);
    }

    responderPodcast(false, ['mensaje' => 'Método no permitido.'], 405);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $mensaje = $e->getCode() === '23000'
        ? 'Ese video de YouTube ya existe en el catálogo.'
        : 'No fue posible guardar el catálogo de videos.';

    responderPodcast(false, ['mensaje' => $mensaje], 500);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    responderPodcast(false, ['mensaje' => 'Ocurrió un error al procesar el video.'], 500);
}
