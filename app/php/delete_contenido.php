<?php

session_start();

header('Content-Type: application/json');

require 'conn.php';

function responder($payload): void
{
    echo json_encode($payload);
    exit;
}

function requireAdmin(): void
{
    if (!isset($_SESSION['usuario_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
        responder([
            'ok' => false,
            'mensaje' => 'No tienes permisos para eliminar contenido.'
        ]);
    }
}

function placeholders(array $values): string
{
    return implode(',', array_fill(0, count($values), '?'));
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
    ");
    $stmt->execute([$table]);

    return (int) $stmt->fetchColumn() > 0;
}

function fetchColumnList(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return array_values(array_filter(
        array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)),
        fn ($value) => $value > 0
    ));
}

function deleteWhereIn(PDO $pdo, string $table, string $column, array $ids): int
{
    if (!$ids || !tableExists($pdo, $table)) {
        return 0;
    }

    $ids = array_values(array_unique(array_map('intval', $ids)));
    $stmt = $pdo->prepare("
        DELETE FROM imssight.$table
        WHERE $column IN (" . placeholders($ids) . ")
    ");
    $stmt->execute($ids);

    return $stmt->rowCount();
}

function deleteSearchRows(PDO $pdo, array $especialidadIds, array $temaIds, array $casoIds, array $escenaIds): int
{
    if (!tableExists($pdo, 'search_index')) {
        return 0;
    }

    $conditions = [];
    $params = [];

    foreach ([
        'id_especialidad' => $especialidadIds,
        'id_tema' => $temaIds,
        'id_caso' => $casoIds,
        'id_escena' => $escenaIds
    ] as $column => $ids) {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (!$ids) {
            continue;
        }

        $conditions[] = "$column IN (" . placeholders($ids) . ")";
        $params = array_merge($params, $ids);
    }

    if (!$conditions) {
        return 0;
    }

    $stmt = $pdo->prepare("
        DELETE FROM imssight.search_index
        WHERE " . implode(' OR ', $conditions)
    );
    $stmt->execute($params);

    return $stmt->rowCount();
}

function deleteInfografiaFiles(array $rutas): void
{
    $base = realpath(__DIR__ . '/../img/infografias/casos');

    if (!$base) {
        return;
    }

    foreach ($rutas as $ruta) {
        $ruta = trim((string) $ruta);

        if ($ruta === '') {
            continue;
        }

        $local = realpath(__DIR__ . '/../' . ltrim($ruta, '/'));

        if ($local && strpos($local, $base) === 0 && is_file($local)) {
            unlink($local);
        }
    }
}

try {
    requireAdmin();

    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $tipo = $data['tipo'] ?? '';
    $id = (int) ($data['id'] ?? 0);

    if (!in_array($tipo, ['especialidad', 'tema', 'caso'], true) || $id <= 0) {
        throw new Exception('Solicitud de eliminación no válida.');
    }

    $especialidadIds = [];
    $temaIds = [];
    $casoIds = [];

    if ($tipo === 'especialidad') {
        $especialidadIds = [$id];
        $temaIds = fetchColumnList(
            $pdo,
            "SELECT id FROM imssight.temas WHERE id_especialidad = ?",
            [$id]
        );
    }

    if ($tipo === 'tema') {
        $temaIds = [$id];
        $especialidadIds = fetchColumnList(
            $pdo,
            "SELECT id_especialidad FROM imssight.temas WHERE id = ?",
            [$id]
        );
    }

    if ($tipo === 'caso') {
        $casoIds = [$id];
        $temaIds = fetchColumnList(
            $pdo,
            "SELECT id_tema FROM imssight.casos_clinicos WHERE id = ?",
            [$id]
        );

        if ($temaIds) {
            $especialidadIds = fetchColumnList(
                $pdo,
                "SELECT id_especialidad FROM imssight.temas WHERE id IN (" . placeholders($temaIds) . ")",
                $temaIds
            );
        }
    }

    if ($temaIds && $tipo !== 'caso') {
        $casoIds = fetchColumnList(
            $pdo,
            "SELECT id FROM imssight.casos_clinicos WHERE id_tema IN (" . placeholders($temaIds) . ")",
            $temaIds
        );
    }

    if ($tipo === 'especialidad' && !$temaIds && !$casoIds) {
        $stmt = $pdo->prepare("SELECT id FROM imssight.especialidades WHERE id = ?");
        $stmt->execute([$id]);

        if (!$stmt->fetchColumn()) {
            throw new Exception('La especialidad ya no existe.');
        }
    }

    if ($tipo === 'tema' && !$temaIds) {
        throw new Exception('El tema ya no existe.');
    }

    if ($tipo === 'caso' && !$casoIds) {
        throw new Exception('El caso ya no existe.');
    }

    $escenaIds = $casoIds
        ? fetchColumnList(
            $pdo,
            "SELECT id FROM imssight.escenas WHERE id_caso IN (" . placeholders($casoIds) . ")",
            $casoIds
        )
        : [];

    $examenIds = $casoIds
        ? fetchColumnList(
            $pdo,
            "SELECT id FROM imssight.examenes WHERE id_caso IN (" . placeholders($casoIds) . ")",
            $casoIds
        )
        : [];

    $infografiaRutas = [];

    if ($casoIds && tableExists($pdo, 'infografias_caso')) {
        $stmt = $pdo->prepare("
            SELECT ruta_imagen
            FROM imssight.infografias_caso
            WHERE id_caso IN (" . placeholders($casoIds) . ")
        ");
        $stmt->execute($casoIds);
        $infografiaRutas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    $pdo->beginTransaction();

    $searchEspecialidadIds = $tipo === 'especialidad' ? $especialidadIds : [];
    $searchTemaIds = in_array($tipo, ['especialidad', 'tema'], true) ? $temaIds : [];

    $deleted = [
        'search_index' => deleteSearchRows($pdo, $searchEspecialidadIds, $searchTemaIds, $casoIds, $escenaIds),
        'respuestas_usuario' => deleteWhereIn($pdo, 'respuestas_usuario', 'id_escena', $escenaIds),
        'examen_resultados' => deleteWhereIn($pdo, 'examen_resultados', 'id_examen', $examenIds),
        'examen_preguntas' => deleteWhereIn($pdo, 'examen_preguntas', 'id_examen', $examenIds),
        'examenes' => deleteWhereIn($pdo, 'examenes', 'id', $examenIds),
        'perlas_clinicas_caso' => deleteWhereIn($pdo, 'perlas_clinicas_caso', 'id_caso', $casoIds),
        'infografias_caso' => deleteWhereIn($pdo, 'infografias_caso', 'id_caso', $casoIds),
        'escalas_pronosticas_caso' => deleteWhereIn($pdo, 'escalas_pronosticas_caso', 'id_caso', $casoIds),
        'escenas' => deleteWhereIn($pdo, 'escenas', 'id', $escenaIds),
        'casos_clinicos' => deleteWhereIn($pdo, 'casos_clinicos', 'id', $casoIds),
        'temas' => deleteWhereIn($pdo, 'temas', 'id', $tipo === 'caso' ? [] : $temaIds),
        'especialidades' => deleteWhereIn($pdo, 'especialidades', 'id', $tipo === 'especialidad' ? $especialidadIds : [])
    ];

    $pdo->commit();

    deleteInfografiaFiles($infografiaRutas);

    responder([
        'ok' => true,
        'mensaje' => 'Contenido eliminado definitivamente.',
        'deleted' => $deleted
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    responder([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);
}
