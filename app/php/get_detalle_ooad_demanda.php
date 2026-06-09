<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conn.php';

const ESCENARIOS_VALIDOS = ['Conservador', 'Ajustado sin COVID', 'Tendencia reciente'];

function responder_json($payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function fetch_all(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll();
}

function numero($value): float
{
    return $value === null ? 0.0 : (float) $value;
}

function entero($value): int
{
    return (int) round(numero($value));
}

function riesgo_unidad_preliminar(?float $crecimiento, float $diferencia): string
{
    if ($crecimiento === null) {
        return 'Sin base real';
    }

    if ($crecimiento >= 15 && $diferencia >= 10000) {
        return 'Crítico';
    }

    if ($crecimiento >= 10 && $diferencia >= 5000) {
        return 'Alto';
    }

    if ($crecimiento >= 5) {
        return 'Vigilancia';
    }

    return 'Normal';
}

function mape_texto($value): string
{
    if ($value === null || round(numero($value), 2) >= 999.99) {
        return 'MAPE no disponible';
    }

    return round(numero($value), 2) . '%';
}

function normalizar_serie_real(array $fila): array
{
    return [
        'anio' => (int) $fila['anio'],
        'consultas' => entero($fila['consultas']),
        'tipo' => 'real',
    ];
}

function normalizar_serie_predicha(array $fila): array
{
    return [
        'anio' => (int) $fila['anio'],
        'consultas' => entero($fila['consultas_estimadas']),
        'limite_inferior' => entero($fila['limite_inferior']),
        'limite_superior' => entero($fila['limite_superior']),
        'modelo' => $fila['modelo'],
        'error_mape' => $fila['error_mape'] === null ? null : round(numero($fila['error_mape']), 2),
        'error_mape_texto' => mape_texto($fila['error_mape']),
        'tipo' => 'proyeccion',
    ];
}

function normalizar_unidad_crecimiento(array $fila): array
{
    $real2024 = numero($fila['consultas_2024']);
    $estimado2026 = numero($fila['estimado_2026']);
    $diferencia = $estimado2026 - $real2024;
    $crecimiento = $real2024 > 0 ? (($estimado2026 - $real2024) / $real2024) * 100 : null;

    return [
        'clave_presupuestal' => $fila['clave_presupuestal'],
        'unidad' => $fila['unidad'],
        'nivel_atencion' => $fila['nivel_atencion'] ?? null,
        'consultas_2024' => entero($real2024),
        'estimado_2025' => entero($fila['estimado_2025']),
        'estimado_2026' => entero($estimado2026),
        'diferencia_esperada' => entero($diferencia),
        'crecimiento_esperado_pct' => $crecimiento === null ? null : round($crecimiento, 2),
        'riesgo_preliminar' => riesgo_unidad_preliminar($crecimiento, $diferencia),
    ];
}

try {
    $ooad = trim((string) ($_GET['ooad'] ?? ''));
    $escenario = trim((string) ($_GET['escenario'] ?? 'Conservador'));

    if ($ooad === '') {
        responder_json(['error' => 'El parámetro ooad es obligatorio.'], 400);
    }

    if (!in_array($escenario, ESCENARIOS_VALIDOS, true)) {
        $escenario = 'Conservador';
    }

    $historico = fetch_all(
        $pdo,
        "SELECT anio, SUM(consultas) AS consultas
         FROM imssight.demanda_unidad_mensual_geo
         WHERE ooad = :ooad
         GROUP BY anio
         ORDER BY anio",
        [':ooad' => $ooad]
    );

    $prediccion = fetch_all(
        $pdo,
        "SELECT anio,
                SUM(consultas_estimadas) AS consultas_estimadas,
                SUM(limite_inferior) AS limite_inferior,
                SUM(limite_superior) AS limite_superior,
                MAX(modelo) AS modelo,
                MAX(error_mape) AS error_mape
         FROM imssight.prediccion_demanda
         WHERE escenario = :escenario
           AND nivel = 'ooad'
           AND tipo_consulta = 'Total'
           AND clave = :ooad
           AND anio IN (2025, 2026)
         GROUP BY anio
         ORDER BY anio",
        [
            ':escenario' => $escenario,
            ':ooad' => $ooad,
        ]
    );

    $topUnidades2024 = fetch_all(
        $pdo,
        "SELECT clave_presupuestal, unidad, nivel_atencion, SUM(consultas) AS consultas
         FROM imssight.demanda_unidad_mensual_geo
         WHERE ooad = :ooad
           AND anio = 2024
         GROUP BY clave_presupuestal, unidad, nivel_atencion
         ORDER BY consultas DESC
         LIMIT 10",
        [':ooad' => $ooad]
    );

    $topUnidades2026 = fetch_all(
        $pdo,
        "SELECT pred.clave AS clave_presupuestal,
                MAX(pred.nombre) AS unidad,
                MAX(hist.nivel_atencion) AS nivel_atencion,
                SUM(pred.consultas_estimadas) AS estimado_2026
         FROM imssight.prediccion_demanda pred
         LEFT JOIN (
            SELECT clave_presupuestal, MAX(nivel_atencion) AS nivel_atencion
            FROM imssight.demanda_unidad_mensual_geo
            WHERE ooad = :ooad_real
            GROUP BY clave_presupuestal
         ) hist ON hist.clave_presupuestal = pred.clave
         WHERE pred.escenario = :escenario
           AND pred.nivel = 'unidad'
           AND pred.tipo_consulta = 'Total'
           AND pred.anio = 2026
           AND pred.clave IN (
              SELECT DISTINCT clave_presupuestal
              FROM imssight.demanda_unidad_mensual_geo
              WHERE ooad = :ooad_sub
           )
         GROUP BY pred.clave
         ORDER BY estimado_2026 DESC
         LIMIT 10",
        [
            ':ooad_real' => $ooad,
            ':escenario' => $escenario,
            ':ooad_sub' => $ooad,
        ]
    );

    $unidadesCrecimiento = fetch_all(
        $pdo,
        "SELECT
            pred.clave AS clave_presupuestal,
            MAX(pred.nombre) AS unidad,
            MAX(hist.nivel_atencion) AS nivel_atencion,
            COALESCE(MAX(hist.consultas_2024), 0) AS consultas_2024,
            SUM(CASE WHEN pred.anio = 2025 THEN pred.consultas_estimadas ELSE 0 END) AS estimado_2025,
            SUM(CASE WHEN pred.anio = 2026 THEN pred.consultas_estimadas ELSE 0 END) AS estimado_2026
         FROM imssight.prediccion_demanda pred
         LEFT JOIN (
            SELECT clave_presupuestal, MAX(nivel_atencion) AS nivel_atencion, SUM(consultas) AS consultas_2024
            FROM imssight.demanda_unidad_mensual_geo
            WHERE ooad = :ooad_real
              AND anio = 2024
            GROUP BY clave_presupuestal
         ) hist ON hist.clave_presupuestal = pred.clave
         WHERE pred.escenario = :escenario
           AND pred.nivel = 'unidad'
           AND pred.tipo_consulta = 'Total'
           AND pred.anio IN (2025, 2026)
           AND pred.clave IN (
              SELECT DISTINCT clave_presupuestal
              FROM imssight.demanda_unidad_mensual_geo
              WHERE ooad = :ooad_sub
           )
         GROUP BY pred.clave
         HAVING COALESCE(MAX(hist.consultas_2024), 0) > 0
            AND SUM(CASE WHEN pred.anio = 2026 THEN pred.consultas_estimadas ELSE 0 END) > 0
         ORDER BY (
            (
              SUM(CASE WHEN pred.anio = 2026 THEN pred.consultas_estimadas ELSE 0 END)
              - COALESCE(MAX(hist.consultas_2024), 0)
            ) / COALESCE(MAX(hist.consultas_2024), 1)
         ) DESC,
         SUM(CASE WHEN pred.anio = 2026 THEN pred.consultas_estimadas ELSE 0 END) DESC
         LIMIT 10",
        [
            ':ooad_real' => $ooad,
            ':escenario' => $escenario,
            ':ooad_sub' => $ooad,
        ]
    );

    $puntosCalientes = fetch_all(
        $pdo,
        "SELECT clave_presupuestal, unidad, ooad, nivel_atencion, anio, mes,
                consultas, promedio_historico, z_score, categoria
         FROM imssight.v_puntos_calientes_unidad
         WHERE ooad = :ooad
         ORDER BY z_score DESC, consultas DESC
         LIMIT 10",
        [':ooad' => $ooad]
    );

    responder_json([
        'generado_en' => date('c'),
        'ooad' => $ooad,
        'escenario' => $escenario,
        'historico' => array_map('normalizar_serie_real', $historico),
        'prediccion' => array_map('normalizar_serie_predicha', $prediccion),
        'serie' => array_merge(
            array_map('normalizar_serie_real', $historico),
            array_map('normalizar_serie_predicha', $prediccion)
        ),
        'top_unidades_2024' => array_map(
            static fn(array $fila): array => [
                'clave_presupuestal' => $fila['clave_presupuestal'],
                'unidad' => $fila['unidad'],
                'nivel_atencion' => $fila['nivel_atencion'],
                'consultas' => entero($fila['consultas']),
            ],
            $topUnidades2024
        ),
        'top_unidades_proyectadas_2026' => array_map(
            static fn(array $fila): array => [
                'clave_presupuestal' => $fila['clave_presupuestal'],
                'unidad' => $fila['unidad'],
                'nivel_atencion' => $fila['nivel_atencion'],
                'estimado_2026' => entero($fila['estimado_2026']),
            ],
            $topUnidades2026
        ),
        'unidades_mayor_crecimiento_esperado' => array_map('normalizar_unidad_crecimiento', $unidadesCrecimiento),
        'puntos_calientes' => array_map(
            static fn(array $fila): array => [
                'clave_presupuestal' => $fila['clave_presupuestal'],
                'unidad' => $fila['unidad'],
                'ooad' => $fila['ooad'],
                'nivel_atencion' => $fila['nivel_atencion'],
                'anio' => (int) $fila['anio'],
                'mes' => $fila['mes'],
                'consultas' => entero($fila['consultas']),
                'promedio_historico' => entero($fila['promedio_historico']),
                'z_score' => round(numero($fila['z_score']), 2),
                'categoria' => $fila['categoria'],
            ],
            $puntosCalientes
        ),
    ]);
} catch (Throwable $error) {
    error_log('get_detalle_ooad_demanda.php: ' . $error->getMessage());
    responder_json(['error' => 'No fue posible generar el detalle de la OOAD.'], 500);
}
