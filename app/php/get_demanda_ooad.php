<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conn.php';

function responder_json($payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function obtener_anio(): int
{
    $anio = $_GET['anio'] ?? 2024;

    if (!filter_var($anio, FILTER_VALIDATE_INT)) {
        responder_json(['error' => 'El año solicitado no es válido.'], 400);
    }

    $anio = (int) $anio;

    if ($anio < 2012 || $anio > 2024) {
        responder_json(['error' => 'El año debe estar entre 2012 y 2024.'], 400);
    }

    return $anio;
}

function obtener_mes(): ?int
{
    $mes = $_GET['mes'] ?? '';

    if ($mes === '' || $mes === '0' || $mes === 0 || strtolower((string) $mes) === 'todos') {
        return null;
    }

    if (!filter_var($mes, FILTER_VALIDATE_INT)) {
        responder_json(['error' => 'El mes solicitado no es válido.'], 400);
    }

    $mes = (int) $mes;

    if ($mes < 1 || $mes > 12) {
        responder_json(['error' => 'El mes debe estar entre 1 y 12.'], 400);
    }

    return $mes;
}

function obtener_tipo(): ?string
{
    $tipo = trim((string) ($_GET['tipo'] ?? 'Total'));
    $tiposValidos = [
        'Total' => null,
        'Medicina Familiar' => 'Medicina Familiar',
        'Especialidades' => 'Especialidades',
    ];

    if (!array_key_exists($tipo, $tiposValidos)) {
        responder_json(['error' => 'El tipo de consulta no es válido.'], 400);
    }

    return $tiposValidos[$tipo];
}

try {
    $anio = obtener_anio();
    $mes = obtener_mes();
    $tipo = obtener_tipo();

    if ($mes !== null) {
        $sql = 'SELECT
                    ooad,
                    SUM(consultas) AS consultas
                FROM imssight.demanda_unidad_mensual_geo
                WHERE anio = :anio
                  AND num_mes = :mes';

        if ($tipo !== null) {
            $sql .= ' AND tipo_consulta = :tipo';
        }

        $sql .= ' GROUP BY ooad ORDER BY ooad';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
        $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);

        if ($tipo !== null) {
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        }
    } elseif ($tipo === null) {
        $stmt = $pdo->prepare(
            'SELECT
                ooad,
                consultas
            FROM imssight.v_demanda_ooad_anio
            WHERE anio = :anio
            ORDER BY ooad'
        );

        $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
    } else {
        $stmt = $pdo->prepare(
            'SELECT
                ooad,
                consultas
            FROM imssight.v_demanda_ooad_anio_tipo
            WHERE anio = :anio
              AND tipo_consulta = :tipo
            ORDER BY ooad'
        );

        $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
    }

    $stmt->execute();

    $datos = array_map(
        static fn(array $fila): array => [
            'ooad' => $fila['ooad'],
            'consultas' => (int) $fila['consultas'],
        ],
        $stmt->fetchAll()
    );

    responder_json($datos);
} catch (Throwable $error) {
    responder_json(['error' => 'No fue posible consultar la demanda médica por OOAD.'], 500);
}
