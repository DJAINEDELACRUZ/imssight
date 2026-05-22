<?php

header('Content-Type: application/json');

require 'conn.php';

/* =========================================
USUARIOS ACTIVOS
========================================= */

$usuarios =
$pdo->query("
    SELECT COUNT(*) total
    FROM imssight.usuarios
    WHERE activo = 1
")->fetch(PDO::FETCH_ASSOC);

/* =========================================
EXAMENES REALIZADOS
========================================= */

$examenes =
$pdo->query("
    SELECT COUNT(*) total
    FROM imssight.examen_resultados
")->fetch(PDO::FETCH_ASSOC);

/* =========================================
PROMEDIO GLOBAL
========================================= */

$promedio =
$pdo->query("
    SELECT ROUND(AVG(calificacion),2) promedio
    FROM imssight.examen_resultados
")->fetch(PDO::FETCH_ASSOC);

/* =========================================
ESPECIALIDADES POPULARES
========================================= */

$popular =
$pdo->query("
SELECT
    e.nombre AS especialidad,
    COUNT(r.id) AS interacciones

FROM imssight.examen_resultados r

INNER JOIN imssight.examenes ex
    ON r.id_examen = ex.id

INNER JOIN imssight.casos_clinicos c
    ON ex.id_caso = c.id

INNER JOIN imssight.temas t
    ON c.id_tema = t.id

INNER JOIN imssight.especialidades e
    ON t.id_especialidad = e.id

GROUP BY e.id

ORDER BY interacciones DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
USUARIOS MÁS ACTIVOS
========================================= */

$activos =
$pdo->query("
SELECT
    u.nombre,
    COUNT(r.id) AS actividad

FROM imssight.examen_resultados r

INNER JOIN imssight.usuarios u
    ON r.id_usuario = u.id

GROUP BY u.id

ORDER BY actividad DESC

LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
ESPECIALIDADES MÁS DIFÍCILES
========================================= */

$dificiles =
$pdo->query("
SELECT
    e.nombre,
    ROUND(AVG(r.calificacion),2) AS promedio

FROM imssight.examen_resultados r

INNER JOIN imssight.examenes ex
    ON r.id_examen = ex.id

INNER JOIN imssight.casos_clinicos c
    ON ex.id_caso = c.id

INNER JOIN imssight.temas t
    ON c.id_tema = t.id

INNER JOIN imssight.especialidades e
    ON t.id_especialidad = e.id

GROUP BY e.id

ORDER BY promedio ASC
")->fetchAll(PDO::FETCH_ASSOC);


/* =========================================
ESCENAS MÁS FALLADAS
========================================= */

$escenasFalladas =
$pdo->query("

SELECT

    esp.nombre AS especialidad,

    c.titulo AS caso_clinico,

    e.titulo AS escena,

    COUNT(*) AS fallos

FROM imssight.respuestas_usuario r

INNER JOIN imssight.escenas e
    ON r.id_escena = e.id

INNER JOIN imssight.casos_clinicos c
    ON e.id_caso = c.id

INNER JOIN imssight.temas t
    ON c.id_tema = t.id

INNER JOIN imssight.especialidades esp
    ON t.id_especialidad = esp.id

WHERE r.correcta = 0

GROUP BY e.id

ORDER BY fallos DESC

LIMIT 10

")->fetchAll(PDO::FETCH_ASSOC);


/* =========================================
RESPUESTA
========================================= */

echo json_encode([

    'usuarios'   => $usuarios['total'],
    'examenes'   => $examenes['total'],
    'promedio'   => $promedio['promedio'],

    'popular'    => $popular,
    'activos'    => $activos,
    'dificiles'  => $dificiles,

    'escenas_falladas' => $escenasFalladas

]);