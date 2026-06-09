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

function escenario_actual(): string
{
    $escenario = trim((string) ($_GET['escenario'] ?? 'Conservador'));
    return in_array($escenario, ESCENARIOS_VALIDOS, true) ? $escenario : 'Conservador';
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

function riesgo_ooad_porcentaje(?float $crecimiento): string
{
    if ($crecimiento === null) {
        return 'Sin base real';
    }

    if ($crecimiento >= 15) {
        return 'Crítico';
    }

    if ($crecimiento >= 10) {
        return 'Alto';
    }

    if ($crecimiento >= 5) {
        return 'Vigilancia';
    }

    return 'Normal';
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

function fila_maxima(array $filas, string $campo): ?array
{
    $mejor = null;

    foreach ($filas as $fila) {
        if ($mejor === null || numero($fila[$campo] ?? 0) > numero($mejor[$campo] ?? 0)) {
            $mejor = $fila;
        }
    }

    return $mejor;
}

function fila_minima(array $filas, string $campo): ?array
{
    $mejor = null;

    foreach ($filas as $fila) {
        if ($mejor === null || numero($fila[$campo] ?? 0) < numero($mejor[$campo] ?? 0)) {
            $mejor = $fila;
        }
    }

    return $mejor;
}

function ordenar_por_campo(array $filas, string $campo, bool $desc = true): array
{
    usort(
        $filas,
        static fn(array $a, array $b): int => $desc
            ? numero($b[$campo] ?? 0) <=> numero($a[$campo] ?? 0)
            : numero($a[$campo] ?? 0) <=> numero($b[$campo] ?? 0)
    );

    return $filas;
}

function mape_texto($value): string
{
    if ($value === null || round(numero($value), 2) >= 999.99) {
        return 'MAPE no disponible';
    }

    return round(numero($value), 2) . '%';
}

function normalizar_prediccion_anual(array $fila): array
{
    return [
        'anio' => (int) $fila['anio'],
        'consultas_estimadas' => entero($fila['consultas_estimadas']),
        'limite_inferior' => entero($fila['limite_inferior']),
        'limite_superior' => entero($fila['limite_superior']),
        'modelo' => $fila['modelo'],
        'error_mape' => $fila['error_mape'] === null ? null : round(numero($fila['error_mape']), 2),
        'error_mape_texto' => mape_texto($fila['error_mape']),
    ];
}

function indexar_por(array $filas, string $campo): array
{
    $index = [];

    foreach ($filas as $fila) {
        $index[(string) $fila[$campo]] = $fila;
    }

    return $index;
}

function normalizar_ooad(array $fila): array
{
    $real2024 = numero($fila['consultas_2024']);
    $estimado2026 = numero($fila['estimado_2026']);
    $diferencia = $estimado2026 - $real2024;
    $crecimiento = $real2024 > 0 ? (($estimado2026 - $real2024) / $real2024) * 100 : null;

    return [
        'ooad' => $fila['ooad'],
        'consultas_2024' => entero($real2024),
        'estimado_2025' => entero($fila['estimado_2025']),
        'estimado_2026' => entero($estimado2026),
        'limite_inferior_2026' => entero($fila['limite_inferior_2026']),
        'limite_superior_2026' => entero($fila['limite_superior_2026']),
        'diferencia_esperada' => entero($diferencia),
        'crecimiento_esperado_pct' => $crecimiento === null ? null : round($crecimiento, 2),
        'riesgo_preliminar' => riesgo_ooad_porcentaje($crecimiento),
        'modelo' => $fila['modelo'],
        'error_mape' => $fila['error_mape'] === null ? null : round(numero($fila['error_mape']), 2),
        'error_mape_texto' => mape_texto($fila['error_mape']),
    ];
}

function normalizar_unidad(array $fila): array
{
    $real2024 = numero($fila['consultas_2024']);
    $estimado2026 = numero($fila['estimado_2026']);
    $diferencia = $estimado2026 - $real2024;
    $crecimiento = $real2024 > 0 ? (($estimado2026 - $real2024) / $real2024) * 100 : null;

    return [
        'clave_presupuestal' => $fila['clave_presupuestal'],
        'unidad' => $fila['unidad'],
        'ooad' => $fila['ooad'],
        'consultas_2024' => entero($real2024),
        'estimado_2025' => entero($fila['estimado_2025']),
        'estimado_2026' => entero($estimado2026),
        'diferencia_esperada' => entero($diferencia),
        'crecimiento_esperado_pct' => $crecimiento === null ? null : round($crecimiento, 2),
        'riesgo_preliminar' => riesgo_unidad_preliminar($crecimiento, $diferencia),
        'modelo' => $fila['modelo'],
        'error_mape' => $fila['error_mape'] === null ? null : round(numero($fila['error_mape']), 2),
        'error_mape_texto' => mape_texto($fila['error_mape']),
    ];
}

function normalizar_mes(array $fila): array
{
    return [
        'num_mes' => (int) $fila['num_mes'],
        'mes' => $fila['mes'],
        'promedio_historico' => entero($fila['promedio_historico']),
        'minimo_historico' => entero($fila['minimo_historico'] ?? 0),
        'maximo_historico' => entero($fila['maximo_historico'] ?? 0),
    ];
}

try {
    $escenario = escenario_actual();

    $anualReal = fetch_all(
        $pdo,
        "SELECT anio, SUM(consultas) AS consultas
         FROM imssight.demanda_unidad_mensual_geo
         GROUP BY anio
         ORDER BY anio"
    );

    $limites = fetch_all(
        $pdo,
        "SELECT MIN(anio) AS anio_inicial, MAX(anio) AS anio_final
         FROM imssight.demanda_unidad_mensual_geo"
    );

    $anioInicial = (int) ($limites[0]['anio_inicial'] ?? 2012);
    $anioFinal = (int) ($limites[0]['anio_final'] ?? 2024);
    $anioMayor = fila_maxima($anualReal, 'consultas');

    $totalInicial = 0;
    $totalFinal = 0;

    foreach ($anualReal as $fila) {
        if ((int) $fila['anio'] === $anioInicial) {
            $totalInicial = numero($fila['consultas']);
        }
        if ((int) $fila['anio'] === $anioFinal) {
            $totalFinal = numero($fila['consultas']);
        }
    }

    $crecimientoHistorico = $totalInicial > 0 ? (($totalFinal - $totalInicial) / $totalInicial) * 100 : null;

    $estacionalidad = fetch_all(
        $pdo,
        "SELECT num_mes, mes, promedio_historico, minimo_historico, maximo_historico
         FROM imssight.v_estacionalidad_mensual
         ORDER BY num_mes"
    );

    $mesMayor = fila_maxima($estacionalidad, 'promedio_historico');
    $mesMenor = fila_minima($estacionalidad, 'promedio_historico');
    $mesesAltos = array_slice(ordenar_por_campo($estacionalidad, 'promedio_historico', true), 0, 3);
    $mesesBajos = array_slice(ordenar_por_campo($estacionalidad, 'promedio_historico', false), 0, 3);

    $mesesCalientes = fetch_all(
        $pdo,
        "SELECT anio, num_mes, mes, consultas, promedio_historico, indice_presion_mensual, categoria
         FROM imssight.v_meses_calientes
         WHERE anio = :anio
         ORDER BY indice_presion_mensual DESC, consultas DESC",
        [':anio' => $anioFinal]
    );

    $proyeccionNacional = fetch_all(
        $pdo,
        "SELECT anio,
                SUM(consultas_estimadas) AS consultas_estimadas,
                SUM(limite_inferior) AS limite_inferior,
                SUM(limite_superior) AS limite_superior,
                MAX(modelo) AS modelo,
                MAX(error_mape) AS error_mape
         FROM imssight.prediccion_demanda
         WHERE escenario = :escenario
           AND nivel = 'nacional'
           AND tipo_consulta = 'Total'
           AND anio IN (2025, 2026)
         GROUP BY anio
         ORDER BY anio",
        [':escenario' => $escenario]
    );

    $proyeccionNacionalIndex = indexar_por($proyeccionNacional, 'anio');
    $pred2025 = $proyeccionNacionalIndex['2025'] ?? null;
    $pred2026 = $proyeccionNacionalIndex['2026'] ?? null;

    $serieNacional = array_map(
        static fn(array $fila): array => [
            'anio' => (int) $fila['anio'],
            'consultas' => entero($fila['consultas']),
            'tipo' => 'real',
        ],
        $anualReal
    );

    foreach ($proyeccionNacional as $fila) {
        $serieNacional[] = [
            'anio' => (int) $fila['anio'],
            'consultas' => entero($fila['consultas_estimadas']),
            'limite_inferior' => entero($fila['limite_inferior']),
            'limite_superior' => entero($fila['limite_superior']),
            'tipo' => 'proyeccion',
        ];
    }

    $proyeccionOoadRaw = fetch_all(
        $pdo,
        "SELECT
            COALESCE(hist.ooad, pred.clave) AS ooad,
            COALESCE(hist.consultas_2024, 0) AS consultas_2024,
            SUM(CASE WHEN pred.anio = 2025 THEN pred.consultas_estimadas ELSE 0 END) AS estimado_2025,
            SUM(CASE WHEN pred.anio = 2026 THEN pred.consultas_estimadas ELSE 0 END) AS estimado_2026,
            SUM(CASE WHEN pred.anio = 2026 THEN pred.limite_inferior ELSE 0 END) AS limite_inferior_2026,
            SUM(CASE WHEN pred.anio = 2026 THEN pred.limite_superior ELSE 0 END) AS limite_superior_2026,
            MAX(pred.modelo) AS modelo,
            MAX(pred.error_mape) AS error_mape
         FROM imssight.prediccion_demanda pred
         LEFT JOIN (
            SELECT ooad, SUM(consultas) AS consultas_2024
            FROM imssight.demanda_unidad_mensual_geo
            WHERE anio = :anio_real
            GROUP BY ooad
         ) hist ON hist.ooad = pred.clave
         WHERE pred.escenario = :escenario
           AND pred.nivel = 'ooad'
           AND pred.tipo_consulta = 'Total'
           AND pred.anio IN (2025, 2026)
         GROUP BY COALESCE(hist.ooad, pred.clave), hist.consultas_2024
         ORDER BY estimado_2026 DESC",
        [
            ':anio_real' => $anioFinal,
            ':escenario' => $escenario,
        ]
    );

    $proyeccionOoad = array_map('normalizar_ooad', $proyeccionOoadRaw);
    $riesgoOoad = $proyeccionOoad;
    usort(
        $riesgoOoad,
        static fn(array $a, array $b): int => numero($b['crecimiento_esperado_pct'] ?? -999) <=> numero($a['crecimiento_esperado_pct'] ?? -999)
    );

    $proyeccionUnidadRaw = fetch_all(
        $pdo,
        "SELECT
            pred.clave AS clave_presupuestal,
            MAX(pred.nombre) AS unidad,
            MAX(hist.ooad) AS ooad,
            COALESCE(MAX(hist.consultas_2024), 0) AS consultas_2024,
            SUM(CASE WHEN pred.anio = 2025 THEN pred.consultas_estimadas ELSE 0 END) AS estimado_2025,
            SUM(CASE WHEN pred.anio = 2026 THEN pred.consultas_estimadas ELSE 0 END) AS estimado_2026,
            MAX(pred.modelo) AS modelo,
            MAX(pred.error_mape) AS error_mape
         FROM imssight.prediccion_demanda pred
         LEFT JOIN (
            SELECT clave_presupuestal, MAX(ooad) AS ooad, SUM(consultas) AS consultas_2024
            FROM imssight.demanda_unidad_mensual_geo
            WHERE anio = :anio_real
            GROUP BY clave_presupuestal
         ) hist ON hist.clave_presupuestal = pred.clave
         WHERE pred.escenario = :escenario
           AND pred.nivel = 'unidad'
           AND pred.tipo_consulta = 'Total'
           AND pred.anio IN (2025, 2026)
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
         LIMIT 30",
        [
            ':anio_real' => $anioFinal,
            ':escenario' => $escenario,
        ]
    );

    $proyeccionUnidad = array_map('normalizar_unidad', $proyeccionUnidadRaw);

    $topOoadHistorico = fetch_all(
        $pdo,
        "SELECT ooad, SUM(consultas) AS consultas
         FROM imssight.demanda_unidad_mensual_geo
         GROUP BY ooad
         ORDER BY consultas DESC
         LIMIT 5"
    );

    $totalHistorico = fetch_all(
        $pdo,
        "SELECT SUM(consultas) AS consultas
         FROM imssight.demanda_unidad_mensual_geo"
    );

    $totalHistoricoValor = numero($totalHistorico[0]['consultas'] ?? 0);
    $concentracionTop5 = array_map(
        static fn(array $fila): array => [
            'ooad' => $fila['ooad'],
            'consultas' => entero($fila['consultas']),
            'porcentaje_nacional' => $totalHistoricoValor > 0
                ? round((numero($fila['consultas']) / $totalHistoricoValor) * 100, 2)
                : 0,
        ],
        $topOoadHistorico
    );

    $puntosCalientes = fetch_all(
        $pdo,
        "SELECT clave_presupuestal, unidad, ooad, nivel_atencion, anio, num_mes, mes,
                consultas, promedio_historico, z_score, indice_presion, categoria
         FROM imssight.v_puntos_calientes_unidad
         WHERE anio = :anio
         ORDER BY z_score DESC, consultas DESC
         LIMIT 12",
        [':anio' => $anioFinal]
    );

    $modelo = $pred2025['modelo'] ?? $pred2026['modelo'] ?? null;
    $errorMape = $pred2025['error_mape'] ?? $pred2026['error_mape'] ?? null;
    $ooadMasConcentracion = $concentracionTop5[0] ?? null;
    $ooadMasRiesgo = $riesgoOoad[0] ?? null;

    responder_json([
        'generado_en' => date('c'),
        'fuente' => 'IMSSight / demanda_unidad_mensual_geo / prediccion_demanda',
        'escenario' => $escenario,
        'escenarios_disponibles' => ESCENARIOS_VALIDOS,
        'notas_metodologicas' => [
            'La demanda total suma Medicina Familiar + Especialidades.',
            'Las predicciones se filtran por escenario y nunca se suman escenarios distintos.',
            'El análisis presenta tres escenarios predictivos: Conservador, Ajustado sin COVID y Tendencia reciente. Estos escenarios no deben sumarse entre sí; representan supuestos alternativos para planeación.',
            'El riesgo preliminar se calcula con crecimiento esperado 2024-2026.',
            'Este riesgo es preliminar porque aún no cruza demanda contra plantilla médica. La versión final se integrará al IRCA.',
        ],
        'resumen_nacional' => [
            'anio_inicial' => $anioInicial,
            'anio_final_real' => $anioFinal,
            'anio_historico_mayor_demanda' => $anioMayor ? (int) $anioMayor['anio'] : null,
            'consultas_anio_mayor_demanda' => $anioMayor ? entero($anioMayor['consultas']) : 0,
            'total_ultimo_anio_real' => entero($totalFinal),
            'crecimiento_historico_pct' => $crecimientoHistorico === null ? null : round($crecimientoHistorico, 2),
            'mes_historico_mas_demandado' => $mesMayor ? $mesMayor['mes'] : null,
            'mes_historico_menos_demandado' => $mesMenor ? $mesMenor['mes'] : null,
            'escenario' => $escenario,
            'modelo' => $modelo,
            'error_mape' => $errorMape === null ? null : round(numero($errorMape), 2),
            'error_mape_texto' => mape_texto($errorMape),
            'ooad_mayor_concentracion' => $ooadMasConcentracion,
            'ooad_mayor_riesgo_preliminar' => $ooadMasRiesgo,
        ],
        'hallazgos' => [
            'principal' => "La demanda real nacional alcanza su punto histórico más alto en " . ($anioMayor['anio'] ?? 'N/D') . " y el último año real disponible suma " . number_format($totalFinal, 0, '.', ',') . " consultas.",
            'proyeccion' => "Bajo el escenario {$escenario}, la proyección nacional estima " . number_format(numero($pred2025['consultas_estimadas'] ?? 0), 0, '.', ',') . " consultas en 2025 y " . number_format(numero($pred2026['consultas_estimadas'] ?? 0), 0, '.', ',') . " en 2026.",
            'estacionalidad' => 'Históricamente suben más ' . implode(', ', array_column($mesesAltos, 'mes')) . ' y bajan ' . implode(', ', array_column($mesesBajos, 'mes')) . '.',
            'riesgo' => $ooadMasRiesgo ? "{$ooadMasRiesgo['ooad']} encabeza el crecimiento esperado 2024-2026 con riesgo preliminar {$ooadMasRiesgo['riesgo_preliminar']}." : 'No hay riesgo territorial calculable.',
        ],
        'serie_nacional' => $serieNacional,
        'estacionalidad' => [
            'mes_promedio_mas_alto' => $mesMayor ? normalizar_mes($mesMayor) : null,
            'mes_promedio_mas_bajo' => $mesMenor ? normalizar_mes($mesMenor) : null,
            'meses_altos' => array_map('normalizar_mes', $mesesAltos),
            'meses_bajos' => array_map('normalizar_mes', $mesesBajos),
            'serie_mensual' => array_map('normalizar_mes', $estacionalidad),
            'meses_calientes_ultimo_anio_real' => array_map(
                static fn(array $fila): array => [
                    'anio' => (int) $fila['anio'],
                    'num_mes' => (int) $fila['num_mes'],
                    'mes' => $fila['mes'],
                    'consultas' => entero($fila['consultas']),
                    'promedio_historico' => entero($fila['promedio_historico']),
                    'indice_presion_mensual' => round(numero($fila['indice_presion_mensual']), 2),
                    'categoria' => $fila['categoria'],
                ],
                $mesesCalientes
            ),
        ],
        'proyeccion_nacional' => [
            'escenario' => $escenario,
            'tipo_consulta' => 'Total',
            'por_anio' => array_map('normalizar_prediccion_anual', $proyeccionNacional),
            'estimado_2025' => $pred2025 ? normalizar_prediccion_anual($pred2025) : null,
            'estimado_2026' => $pred2026 ? normalizar_prediccion_anual($pred2026) : null,
        ],
        'proyeccion_ooad' => $proyeccionOoad,
        'riesgo_ooad' => $riesgoOoad,
        'proyeccion_unidad' => $proyeccionUnidad,
        'riesgo_unidad' => $proyeccionUnidad,
        'concentracion_territorial' => [
            'top_5_historico' => $concentracionTop5,
            'porcentaje_acumulado_top_5' => round(array_sum(array_column($concentracionTop5, 'porcentaje_nacional')), 2),
        ],
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
        'anexos' => [
            'proyeccion_ooad' => $proyeccionOoad,
            'proyeccion_unidad' => $proyeccionUnidad,
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
        ],
    ]);
} catch (Throwable $error) {
    error_log('get_analisis_demanda.php: ' . $error->getMessage());
    responder_json(['error' => 'No fue posible generar el análisis ejecutivo de demanda médica.'], 500);
}
