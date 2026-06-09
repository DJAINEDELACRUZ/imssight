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

try {
    $stmt = $pdo->prepare(
        'SELECT
            base.ooad,
            COALESCE(y2012.consultas, 0) AS consultas_2012,
            COALESCE(y2024.consultas, 0) AS consultas_2024,
            COALESCE(y2024.consultas, 0) - COALESCE(y2012.consultas, 0) AS diferencia,
            CASE
                WHEN COALESCE(y2012.consultas, 0) = 0 THEN NULL
                ELSE ROUND(
                    ((COALESCE(y2024.consultas, 0) - COALESCE(y2012.consultas, 0)) / y2012.consultas) * 100,
                    2
                )
            END AS crecimiento_pct
        FROM (
            SELECT DISTINCT ooad
            FROM imssight.v_demanda_ooad_anio
        ) base
        LEFT JOIN imssight.v_demanda_ooad_anio y2012
          ON y2012.ooad = base.ooad
         AND y2012.anio = 2012
        LEFT JOIN imssight.v_demanda_ooad_anio y2024
          ON y2024.ooad = base.ooad
         AND y2024.anio = 2024
        ORDER BY crecimiento_pct DESC'
    );

    $stmt->execute();

    $datos = array_map(
        static fn(array $fila): array => [
            'ooad' => $fila['ooad'],
            'consultas_2012' => (int) $fila['consultas_2012'],
            'consultas_2024' => (int) $fila['consultas_2024'],
            'diferencia' => (int) $fila['diferencia'],
            'crecimiento_pct' => $fila['crecimiento_pct'] === null ? null : (float) $fila['crecimiento_pct'],
        ],
        $stmt->fetchAll()
    );

    responder_json($datos);
} catch (Throwable $error) {
    responder_json(['error' => 'No fue posible consultar el crecimiento de demanda médica.'], 500);
}
