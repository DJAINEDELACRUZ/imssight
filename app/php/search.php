<?php

header('Content-Type: application/json; charset=utf-8');

require 'conn.php';

function tieneIndiceBusqueda($pdo){

    $stmt =
        $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = 'imssight'
            AND TABLE_NAME = 'search_index'
            AND INDEX_NAME = 'idx_search_fulltext'
        ");

    $stmt->execute();

    return (int)$stmt->fetch()['total'] > 0;

}

function consultaBooleana($texto){

    $terminos =
        preg_split('/\s+/u', trim($texto));

    $limpios = [];

    foreach($terminos as $termino){

        $termino =
            preg_replace('/[^\p{L}\p{N}_]+/u', '', $termino);

        if(mb_strlen($termino, 'UTF-8') < 3){
            continue;
        }

        $limpios[] =
            '+' . $termino . '*';

    }

    return implode(' ', $limpios);

}

function normalizarTexto($texto){

    $texto =
        mb_strtolower($texto ?? '', 'UTF-8');

    $sinAcentos =
        iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);

    return $sinAcentos !== false ? $sinAcentos : $texto;

}

function agregarResultado(&$resultados, $tipo, $titulo, $descripcion, $url, $extra = []){

    $clave =
        $tipo . '|' . $url . '|' . $titulo;

    foreach($resultados as $resultado){

        $claveExistente =
            ($resultado['tipo'] ?? '') . '|' . ($resultado['url'] ?? '') . '|' . ($resultado['titulo'] ?? '');

        if($claveExistente === $clave){
            return;
        }

    }

    $resultados[] =
        array_merge([
            'tipo' => $tipo,
            'titulo' => $titulo,
            'contenido' => $descripcion,
            'descripcion' => $descripcion,
            'url' => $url
        ], $extra);

}

function transformarUrlCasoAntigua($pdo, $url){

    if(!$url || strpos($url, '/pages/caso.html') === false){
        return $url;
    }

    $partes =
        parse_url($url);

    parse_str($partes['query'] ?? '', $parametros);

    $idCaso =
        (int)($parametros['id'] ?? 0);

    if($idCaso <= 0){
        return $url;
    }

    static $casos = [];

    if(!array_key_exists($idCaso, $casos)){

        $stmt =
            $pdo->prepare("
                SELECT
                    c.id,
                    t.id AS id_tema,
                    t.id_especialidad
                FROM imssight.casos_clinicos c
                INNER JOIN imssight.temas t
                    ON t.id = c.id_tema
                WHERE c.id = ?
                LIMIT 1
            ");

        $stmt->execute([$idCaso]);

        $casos[$idCaso] =
            $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    }

    if(!$casos[$idCaso]){
        return $url;
    }

    $urlNueva =
        '/pages/especialidad.html?id=' . $casos[$idCaso]['id_especialidad'] .
        '&id_tema=' . $casos[$idCaso]['id_tema'] .
        '&id_caso=' . $idCaso;

    if(!empty($parametros['escena'])){
        $urlNueva .= '&escena=' . (int)$parametros['escena'];
    }

    return $urlNueva;

}

$q = $_GET['q'] ?? '';

if(!$q){

    echo json_encode([]);

    exit;

}

$buscar =
    "%$q%";

$booleanQuery =
    consultaBooleana($q);

if($booleanQuery !== '' && tieneIndiceBusqueda($pdo)){

    $sql = "

    SELECT *,
        MATCH(titulo, contenido, descripcion)
        AGAINST (? IN BOOLEAN MODE) AS relevancia

    FROM search_index

    WHERE
        MATCH(titulo, contenido, descripcion)
        AGAINST (? IN BOOLEAN MODE)
        OR titulo LIKE ?
        OR descripcion LIKE ?

    ORDER BY relevancia DESC, id DESC

    LIMIT 50

    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $booleanQuery,
        $booleanQuery,
        $buscar,
        $buscar
    ]);

}
else{

    $sql = "

SELECT *

FROM search_index

WHERE

    titulo LIKE ?

    OR contenido LIKE ?

    OR descripcion LIKE ?

LIMIT 50

";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([

        $buscar,
        $buscar,
        $buscar

    ]);

}

$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($resultados as &$resultado){

    $resultado['url'] =
        transformarUrlCasoAntigua($pdo, $resultado['url'] ?? '');

    if(
        ($resultado['tipo'] ?? '') === 'caso'
        && !empty($resultado['id_especialidad'])
        && !empty($resultado['id_tema'])
        && !empty($resultado['id_caso'])
    ){
        $resultado['url'] =
            '/pages/especialidad.html?id=' . $resultado['id_especialidad'] .
            '&id_tema=' . $resultado['id_tema'] .
            '&id_caso=' . $resultado['id_caso'];
    }

    if(
        ($resultado['tipo'] ?? '') === 'tema'
        && !empty($resultado['id_especialidad'])
        && !empty($resultado['id_tema'])
    ){
        $resultado['url'] =
            '/pages/especialidad.html?id=' . $resultado['id_especialidad'] .
            '&id_tema=' . $resultado['id_tema'];
    }

}

unset($resultado);

$qNormalizado =
    normalizarTexto($q);

/*
|--------------------------------------------------------------------------
| ACCESOS DIRECTOS POR INTENCION
|--------------------------------------------------------------------------
*/

if(preg_match('/\b(caso|casos|clinico|clinicos)\b/u', $qNormalizado)){

    agregarResultado(
        $resultados,
        'caso',
        'Casos clínicos',
        'Explora especialidades, temas y casos clínicos interactivos.',
        '/pages/especialidad.html'
    );

}

if(preg_match('/\b(perla|perlas)\b/u', $qNormalizado)){

    agregarResultado(
        $resultados,
        'perla',
        'Perlas clínicas',
        'Consulta aprendizajes breves conectados a casos clínicos.',
        '/pages/perlas_clinicas.html'
    );

}

if(preg_match('/\b(info|infografia|infografias)\b/u', $qNormalizado)){

    agregarResultado(
        $resultados,
        'infografia',
        'Infografías',
        'Material visual y referencias para repaso rápido.',
        '/pages/infografias.html'
    );

}

if(preg_match('/\b(escala|escalas|pronostica|pronosticas|pronostico|pronosticos)\b/u', $qNormalizado)){

    agregarResultado(
        $resultados,
        'escala',
        'Escalas pronósticas',
        'Herramientas de riesgo y apoyo a decisiones clínicas.',
        '/pages/search.html?q=escala%20pronostica'
    );

}

/*
|--------------------------------------------------------------------------
| RESULTADOS VIVOS: CASOS Y PERLAS
|--------------------------------------------------------------------------
*/

$stmtCasos =
    $pdo->prepare("
        SELECT
            c.id,
            c.titulo,
            c.descripcion,
            t.id AS id_tema,
            t.titulo AS tema,
            t.id_especialidad,
            e.nombre AS especialidad
        FROM imssight.casos_clinicos c
        INNER JOIN imssight.temas t
            ON t.id = c.id_tema
        INNER JOIN imssight.especialidades e
            ON e.id = t.id_especialidad
        WHERE c.activo = 1
            AND (
                c.titulo LIKE ?
                OR c.descripcion LIKE ?
                OR t.titulo LIKE ?
                OR e.nombre LIKE ?
            )
        LIMIT 20
    ");

$stmtCasos->execute([
    $buscar,
    $buscar,
    $buscar,
    $buscar
]);

foreach($stmtCasos->fetchAll(PDO::FETCH_ASSOC) as $row){

    agregarResultado(
        $resultados,
        'caso',
        $row['titulo'],
        trim(($row['especialidad'] ?? '') . ' · ' . ($row['tema'] ?? '') . ' · ' . ($row['descripcion'] ?? '')),
        '/pages/especialidad.html?id=' . $row['id_especialidad'] .
            '&id_tema=' . $row['id_tema'] .
            '&id_caso=' . $row['id'],
        [
            'id_especialidad' => $row['id_especialidad'],
            'id_tema' => $row['id_tema'],
            'id_caso' => $row['id']
        ]
    );

}

$stmtPerlas =
    $pdo->prepare("
        SELECT
            p.id,
            p.seccion,
            p.contenido,
            c.id AS id_caso,
            c.titulo AS caso,
            t.titulo AS tema,
            e.nombre AS especialidad
        FROM imssight.perlas_clinicas_caso p
        INNER JOIN imssight.casos_clinicos c
            ON c.id = p.id_caso
        INNER JOIN imssight.temas t
            ON t.id = c.id_tema
        INNER JOIN imssight.especialidades e
            ON e.id = t.id_especialidad
        WHERE c.activo = 1
            AND (
                p.seccion LIKE ?
                OR p.contenido LIKE ?
                OR c.titulo LIKE ?
                OR t.titulo LIKE ?
                OR e.nombre LIKE ?
            )
        LIMIT 20
    ");

$stmtPerlas->execute([
    $buscar,
    $buscar,
    $buscar,
    $buscar,
    $buscar
]);

foreach($stmtPerlas->fetchAll(PDO::FETCH_ASSOC) as $row){

    $textoPlano =
        trim(preg_replace('/\s+/', ' ', strip_tags($row['contenido'] ?? '')));

    agregarResultado(
        $resultados,
        'perla',
        trim(($row['caso'] ?? 'Caso clínico') . ' · ' . ($row['seccion'] ?? 'Perla clínica')),
        mb_substr(trim(($row['especialidad'] ?? '') . ' · ' . ($row['tema'] ?? '') . ' · ' . $textoPlano), 0, 320, 'UTF-8'),
        '/pages/perlas_clinicas.html?id_caso=' . $row['id_caso'],
        [
            'id_caso' => $row['id_caso']
        ]
    );

}

echo json_encode($resultados);
