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

function obtener_clave(): string
{
    $clave = trim((string) ($_GET['clave_presupuestal'] ?? ''));

    if ($clave === '') {
        responder_json(['error' => 'La clave presupuestal es obligatoria.'], 400);
    }

    return $clave;
}

try {
    $anio = obtener_anio();
    $clave = obtener_clave();

    $stmtPerfil = $pdo->prepare(
        'SELECT
            clave_presupuestal,
            unidad,
            nombre_unidad,
            ooad,
            nivel_atencion,
            denominacion_unidad,
            latitud,
            longitud,
            consultas_historicas,
            anios_con_datos,
            promedio_anual
        FROM imssight.v_perfil_unidad_resumen
        WHERE clave_presupuestal = :clave
        LIMIT 1'
    );
    $stmtPerfil->bindValue(':clave', $clave, PDO::PARAM_STR);
    $stmtPerfil->execute();
    $perfil = $stmtPerfil->fetch();

    if (!$perfil) {
        responder_json(['error' => 'No se encontró la unidad solicitada.'], 404);
    }

    $stmtAnual = $pdo->prepare(
        'SELECT
            anio,
            consultas
        FROM imssight.v_demanda_unidad_historica
        WHERE clave_presupuestal = :clave
        ORDER BY anio'
    );
    $stmtAnual->bindValue(':clave', $clave, PDO::PARAM_STR);
    $stmtAnual->execute();
    $historica = array_map(
        static fn(array $fila): array => [
            'anio' => (int) $fila['anio'],
            'consultas' => (int) $fila['consultas'],
        ],
        $stmtAnual->fetchAll()
    );

    $stmtMensual = $pdo->prepare(
        'SELECT
            mes,
            num_mes,
            consultas
        FROM imssight.v_demanda_unidad_mensual_total
        WHERE clave_presupuestal = :clave
          AND anio = :anio
        ORDER BY num_mes'
    );
    $stmtMensual->bindValue(':clave', $clave, PDO::PARAM_STR);
    $stmtMensual->bindValue(':anio', $anio, PDO::PARAM_INT);
    $stmtMensual->execute();
    $mensual = array_map(
        static fn(array $fila): array => [
            'mes' => $fila['mes'],
            'num_mes' => (int) $fila['num_mes'],
            'consultas' => (int) $fila['consultas'],
        ],
        $stmtMensual->fetchAll()
    );

    $stmtDesglose = $pdo->prepare(
        'SELECT
            tipo_consulta,
            consultas
        FROM imssight.v_demanda_unidad_anio_tipo
        WHERE clave_presupuestal = :clave
          AND anio = :anio
        ORDER BY tipo_consulta'
    );
    $stmtDesglose->bindValue(':clave', $clave, PDO::PARAM_STR);
    $stmtDesglose->bindValue(':anio', $anio, PDO::PARAM_INT);
    $stmtDesglose->execute();
    $desglose = array_map(
        static fn(array $fila): array => [
            'tipo_consulta' => $fila['tipo_consulta'],
            'consultas' => (int) $fila['consultas'],
        ],
        $stmtDesglose->fetchAll()
    );

    $stmtDetalle = $pdo->prepare(
        'SELECT
            tipo_consulta,
            mes,
            num_mes,
            consultas
        FROM imssight.demanda_unidad_mensual_geo
        WHERE clave_presupuestal = :clave
          AND anio = :anio
        ORDER BY tipo_consulta, num_mes'
    );
    $stmtDetalle->bindValue(':clave', $clave, PDO::PARAM_STR);
    $stmtDetalle->bindValue(':anio', $anio, PDO::PARAM_INT);
    $stmtDetalle->execute();
    $detalleTipoMensual = array_map(
        static fn(array $fila): array => [
            'tipo_consulta' => $fila['tipo_consulta'],
            'mes' => $fila['mes'],
            'num_mes' => (int) $fila['num_mes'],
            'consultas' => (int) $fila['consultas'],
        ],
        $stmtDetalle->fetchAll()
    );

    $consultasAnio = 0;
    foreach ($mensual as $fila) {
        $consultasAnio += $fila['consultas'];
    }

    $mesMayor = null;
    foreach ($mensual as $fila) {
        if ($mesMayor === null || $fila['consultas'] > $mesMayor['consultas']) {
            $mesMayor = $fila;
        }
    }

    $anioMayor = null;
    foreach ($historica as $fila) {
        if ($anioMayor === null || $fila['consultas'] > $anioMayor['consultas']) {
            $anioMayor = $fila;
        }
    }

    responder_json([
        'perfil' => [
            'clave_presupuestal' => $perfil['clave_presupuestal'],
            'unidad' => $perfil['unidad'],
            'nombre_unidad' => $perfil['nombre_unidad'],
            'ooad' => $perfil['ooad'],
            'nivel_atencion' => $perfil['nivel_atencion'],
            'denominacion_unidad' => $perfil['denominacion_unidad'],
            'latitud' => $perfil['latitud'] === null ? null : (float) $perfil['latitud'],
            'longitud' => $perfil['longitud'] === null ? null : (float) $perfil['longitud'],
            'consultas_historicas' => (int) $perfil['consultas_historicas'],
            'anios_con_datos' => (int) $perfil['anios_con_datos'],
            'promedio_anual' => (float) $perfil['promedio_anual'],
        ],
        'anio' => $anio,
        'consultas_anio' => $consultasAnio,
        'desglose_tipo' => $desglose,
        'mensual_total' => $mensual,
        'historica' => $historica,
        'detalle_tipo_mensual' => $detalleTipoMensual,
        'mes_mayor_demanda' => $mesMayor,
        'anio_mayor_demanda' => $anioMayor,
    ]);
} catch (Throwable $error) {
    responder_json(['error' => 'No fue posible consultar el perfil de la unidad.'], 500);
}
