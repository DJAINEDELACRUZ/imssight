<?php

header('Content-Type: application/json');

require 'conn.php';

$rolesAcademicos = "LOWER(COALESCE(u.rol, '')) IN ('usuario', 'docente')";

/* =========================================
USUARIOS ACTIVOS
========================================= */

$usuarios =
$pdo->query("
    SELECT COUNT(*) total
    FROM imssight.usuarios
    WHERE activo = 1
    AND LOWER(COALESCE(rol, '')) IN ('usuario', 'docente')
")->fetch(PDO::FETCH_ASSOC);

/* =========================================
EXAMENES REALIZADOS
========================================= */

$examenes =
$pdo->query("
    SELECT COUNT(r.id) total
    FROM imssight.examen_resultados r
    INNER JOIN imssight.usuarios u
        ON r.id_usuario = u.id
    WHERE $rolesAcademicos
")->fetch(PDO::FETCH_ASSOC);

/* =========================================
PROMEDIO GLOBAL
========================================= */

$promedio =
$pdo->query("
    SELECT ROUND(AVG(r.calificacion),2) promedio
    FROM imssight.examen_resultados r
    INNER JOIN imssight.usuarios u
        ON r.id_usuario = u.id
    WHERE $rolesAcademicos
")->fetch(PDO::FETCH_ASSOC);

/* =========================================
RESPUESTAS EN ESCENAS
========================================= */

$escenasResumen =
$pdo->query("
    SELECT
        COUNT(r.id) AS total,
        COALESCE(SUM(CASE WHEN r.correcta = 1 THEN 1 ELSE 0 END), 0) AS correctas,
        COALESCE(SUM(CASE WHEN r.correcta = 0 THEN 1 ELSE 0 END), 0) AS incorrectas,
        ROUND(AVG(CASE WHEN r.correcta = 1 THEN 100 ELSE 0 END), 2) AS promedio
    FROM imssight.respuestas_usuario r
    INNER JOIN imssight.usuarios u
        ON r.id_usuario = u.id
    WHERE $rolesAcademicos
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

INNER JOIN imssight.usuarios u
    ON r.id_usuario = u.id

INNER JOIN imssight.examenes ex
    ON r.id_examen = ex.id

INNER JOIN imssight.casos_clinicos c
    ON ex.id_caso = c.id

INNER JOIN imssight.temas t
    ON c.id_tema = t.id

INNER JOIN imssight.especialidades e
    ON t.id_especialidad = e.id

WHERE $rolesAcademicos

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
    COUNT(a.id_usuario) AS actividad

FROM (
    SELECT id_usuario
    FROM imssight.examen_resultados

    UNION ALL

    SELECT id_usuario
    FROM imssight.respuestas_usuario
) a

INNER JOIN imssight.usuarios u
    ON a.id_usuario = u.id

WHERE $rolesAcademicos

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

INNER JOIN imssight.usuarios u
    ON r.id_usuario = u.id

INNER JOIN imssight.examenes ex
    ON r.id_examen = ex.id

INNER JOIN imssight.casos_clinicos c
    ON ex.id_caso = c.id

INNER JOIN imssight.temas t
    ON c.id_tema = t.id

INNER JOIN imssight.especialidades e
    ON t.id_especialidad = e.id

WHERE $rolesAcademicos

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

INNER JOIN imssight.usuarios u
    ON r.id_usuario = u.id

INNER JOIN imssight.escenas e
    ON r.id_escena = e.id

INNER JOIN imssight.casos_clinicos c
    ON e.id_caso = c.id

INNER JOIN imssight.temas t
    ON c.id_tema = t.id

INNER JOIN imssight.especialidades esp
    ON t.id_especialidad = esp.id

WHERE r.correcta = 0
AND $rolesAcademicos

GROUP BY e.id

ORDER BY fallos DESC

LIMIT 10

")->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
ESCENAS POR ESPECIALIDAD
========================================= */

$escenasPorEspecialidad =
$pdo->query("
SELECT
    esp.nombre AS especialidad,
    COUNT(r.id) AS respuestas,
    COALESCE(SUM(CASE WHEN r.correcta = 1 THEN 1 ELSE 0 END), 0) AS correctas,
    COALESCE(SUM(CASE WHEN r.correcta = 0 THEN 1 ELSE 0 END), 0) AS incorrectas,
    ROUND(AVG(CASE WHEN r.correcta = 1 THEN 100 ELSE 0 END), 2) AS acierto

FROM imssight.respuestas_usuario r

INNER JOIN imssight.usuarios u
    ON r.id_usuario = u.id

INNER JOIN imssight.escenas es
    ON r.id_escena = es.id

INNER JOIN imssight.casos_clinicos c
    ON es.id_caso = c.id

INNER JOIN imssight.temas t
    ON c.id_tema = t.id

INNER JOIN imssight.especialidades esp
    ON t.id_especialidad = esp.id

WHERE $rolesAcademicos

GROUP BY esp.id

ORDER BY respuestas DESC
")->fetchAll(PDO::FETCH_ASSOC);

$escenasPorCaso =
$pdo->query("
SELECT
    c.titulo AS caso,
    COUNT(r.id) AS respuestas

FROM imssight.respuestas_usuario r

INNER JOIN imssight.usuarios u
    ON r.id_usuario = u.id

INNER JOIN imssight.escenas es
    ON r.id_escena = es.id

INNER JOIN imssight.casos_clinicos c
    ON es.id_caso = c.id

WHERE $rolesAcademicos

GROUP BY c.id

ORDER BY respuestas DESC

LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
SALUD EDITORIAL
========================================= */

$saludEditorial =
$pdo->query("
SELECT 'casos_sin_examen' metrica, COUNT(*) valor
FROM imssight.casos_clinicos c
LEFT JOIN imssight.examenes e
    ON e.id_caso = c.id
WHERE e.id IS NULL

UNION ALL

SELECT 'casos_sin_escenas', COUNT(*)
FROM imssight.casos_clinicos c
LEFT JOIN imssight.escenas es
    ON es.id_caso = c.id
WHERE es.id IS NULL

UNION ALL

SELECT 'casos_sin_perlas', COUNT(*)
FROM imssight.casos_clinicos c
LEFT JOIN imssight.perlas_clinicas_caso p
    ON p.id_caso = c.id
WHERE p.id IS NULL

UNION ALL

SELECT 'examenes_sin_preguntas', COUNT(*)
FROM imssight.examenes e
LEFT JOIN imssight.examen_preguntas p
    ON p.id_examen = e.id
WHERE p.id IS NULL

UNION ALL

SELECT 'examenes_sin_intentos', COUNT(*)
FROM imssight.examenes e
WHERE NOT EXISTS (
    SELECT 1
    FROM imssight.examen_resultados r
    INNER JOIN imssight.usuarios u
        ON r.id_usuario = u.id
    WHERE r.id_examen = e.id
    AND $rolesAcademicos
)
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
CASOS INCOMPLETOS
========================================= */

$casosIncompletos =
$pdo->query("
SELECT
    c.id,
    c.titulo AS caso,
    COALESCE(esp.nombre, 'Sin especialidad') AS especialidad,
    COALESCE(es.escenas, 0) AS escenas,
    COALESCE(ex.examenes, 0) AS examenes,
    COALESCE(p.preguntas, 0) AS preguntas,
    COALESCE(pc.perlas, 0) AS perlas,
    COALESCE(er.intentos, 0) AS intentos

FROM imssight.casos_clinicos c

LEFT JOIN imssight.temas t
    ON t.id = c.id_tema

LEFT JOIN imssight.especialidades esp
    ON esp.id = t.id_especialidad

LEFT JOIN (
    SELECT id_caso, COUNT(*) AS escenas
    FROM imssight.escenas
    GROUP BY id_caso
) es
    ON es.id_caso = c.id

LEFT JOIN (
    SELECT id_caso, COUNT(*) AS examenes
    FROM imssight.examenes
    GROUP BY id_caso
) ex
    ON ex.id_caso = c.id

LEFT JOIN (
    SELECT ex.id_caso, COUNT(p.id) AS preguntas
    FROM imssight.examenes ex
    LEFT JOIN imssight.examen_preguntas p
        ON p.id_examen = ex.id
    GROUP BY ex.id_caso
) p
    ON p.id_caso = c.id

LEFT JOIN (
    SELECT id_caso, COUNT(*) AS perlas
    FROM imssight.perlas_clinicas_caso
    GROUP BY id_caso
) pc
    ON pc.id_caso = c.id

LEFT JOIN (
    SELECT ex.id_caso, COUNT(u.id) AS intentos
    FROM imssight.examenes ex
    LEFT JOIN imssight.examen_resultados r
        ON r.id_examen = ex.id
    LEFT JOIN imssight.usuarios u
        ON r.id_usuario = u.id
        AND $rolesAcademicos
    GROUP BY ex.id_caso
) er
    ON er.id_caso = c.id

WHERE
    COALESCE(es.escenas, 0) = 0
    OR COALESCE(ex.examenes, 0) = 0
    OR COALESCE(p.preguntas, 0) = 0
    OR COALESCE(pc.perlas, 0) = 0

ORDER BY c.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================================
BALANCE DEL BANCO DE PREGUNTAS
========================================= */

$balanceRespuestas =
$pdo->query("
SELECT
    respuesta_correcta AS opcion,
    COUNT(*) AS total
FROM imssight.examen_preguntas
GROUP BY respuesta_correcta
ORDER BY respuesta_correcta
")->fetchAll(PDO::FETCH_ASSOC);

$dificultadPreguntas =
$pdo->query("
SELECT
    dificultad,
    COUNT(*) AS total
FROM imssight.examen_preguntas
GROUP BY dificultad
ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);


/* =========================================
RESPUESTA
========================================= */

echo json_encode([

    'usuarios'   => $usuarios['total'],
    'examenes'   => $examenes['total'],
    'promedio'   => $promedio['promedio'],
    'escenas_resumen' => $escenasResumen,

    'popular'    => $popular,
    'activos'    => $activos,
    'dificiles'  => $dificiles,

    'escenas_falladas' => $escenasFalladas,
    'escenas_por_especialidad' => $escenasPorEspecialidad,
    'escenas_por_caso' => $escenasPorCaso,
    'salud_editorial' => $saludEditorial,
    'casos_incompletos' => $casosIncompletos,
    'balance_respuestas' => $balanceRespuestas,
    'dificultad_preguntas' => $dificultadPreguntas

]);
