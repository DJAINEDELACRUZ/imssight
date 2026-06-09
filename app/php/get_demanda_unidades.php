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

function obtener_ooad(): string
{
    $ooad = trim((string) ($_GET['ooad'] ?? ''));

    if ($ooad === '') {
        responder_json(['error' => 'La OOAD es obligatoria.'], 400);
    }

    return $ooad;
}

try {
    $anio = obtener_anio();
    $mes = obtener_mes();
    $tipo = obtener_tipo();
    $ooad = obtener_ooad();

    if ($mes !== null) {
        $sql = 'SELECT
                    clave_presupuestal,
                    unidad,
                    nombre_unidad,
                    nivel_atencion,
                    denominacion_unidad,
                    latitud,
                    longitud,
                    SUM(consultas) AS consultas
                FROM imssight.demanda_unidad_mensual_geo
                WHERE anio = :anio
                  AND ooad = :ooad
                  AND num_mes = :mes
                  AND latitud IS NOT NULL
                  AND longitud IS NOT NULL';

        if ($tipo !== null) {
            $sql .= ' AND tipo_consulta = :tipo';
        }

        $sql .= ' GROUP BY
                    clave_presupuestal,
                    unidad,
                    nombre_unidad,
                    nivel_atencion,
                    denominacion_unidad,
                    latitud,
                    longitud
                  ORDER BY consultas DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
        $stmt->bindValue(':ooad', $ooad, PDO::PARAM_STR);
        $stmt->bindValue(':mes', $mes, PDO::PARAM_INT);

        if ($tipo !== null) {
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        }
    } elseif ($tipo === null) {
        $stmt = $pdo->prepare(
            'SELECT
                clave_presupuestal,
                unidad,
                nombre_unidad,
                nivel_atencion,
                denominacion_unidad,
                consultas,
                latitud,
                longitud
            FROM imssight.v_demanda_unidad_anio
            WHERE anio = :anio
              AND ooad = :ooad
              AND latitud IS NOT NULL
              AND longitud IS NOT NULL
            ORDER BY consultas DESC'
        );

        $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
        $stmt->bindValue(':ooad', $ooad, PDO::PARAM_STR);
    } else {
        $stmt = $pdo->prepare(
            'SELECT
                clave_presupuestal,
                unidad,
                nombre_unidad,
                nivel_atencion,
                denominacion_unidad,
                consultas,
                latitud,
                longitud
            FROM imssight.v_demanda_unidad_anio_tipo
            WHERE anio = :anio
              AND ooad = :ooad
              AND tipo_consulta = :tipo
              AND latitud IS NOT NULL
              AND longitud IS NOT NULL
            ORDER BY consultas DESC'
        );

        $stmt->bindValue(':anio', $anio, PDO::PARAM_INT);
        $stmt->bindValue(':ooad', $ooad, PDO::PARAM_STR);
        $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
    }

    $stmt->execute();

    $datos = array_map(
        static fn(array $fila): array => [
            'clave_presupuestal' => $fila['clave_presupuestal'],
            'unidad' => $fila['unidad'],
            'nombre_unidad' => $fila['nombre_unidad'],
            'nivel_atencion' => $fila['nivel_atencion'],
            'denominacion_unidad' => $fila['denominacion_unidad'],
            'consultas' => (int) $fila['consultas'],
            'latitud' => (float) $fila['latitud'],
            'longitud' => (float) $fila['longitud'],
        ],
        $stmt->fetchAll()
    );

    responder_json($datos);
} catch (Throwable $error) {
    responder_json(['error' => 'No fue posible consultar la demanda médica por unidad.'], 500);
}
