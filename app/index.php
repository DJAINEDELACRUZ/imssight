<?php

function h($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function mediaUrl($path)
{
    $path = trim((string) $path);

    if ($path === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    $path = preg_replace('/^(\.\.\/)+/', '', $path);

    return ltrim($path, '/') === $path
        ? $path
        : substr($path, 1);
}

function htmlTieneContenido($html)
{
    $texto = html_entity_decode(strip_tags((string) ($html ?? '')), ENT_QUOTES, 'UTF-8');
    $texto = preg_replace('/\x{00a0}/u', ' ', $texto);

    return trim($texto) !== '';
}

$temas = [];
$casos = [];
$escenas = [];
$perlasCaso = [];
$infografiasCaso = [];
$escalasCaso = [];
$examenesCaso = [];
$casoSeleccionado = null;
$errorCarga = null;

try {
    require_once __DIR__ . '/php/conn.php';
    require_once __DIR__ . '/php/content_visibility.php';

    asegurarColumnasVisibilidadContenido($pdo);

    $programaSlug = normalizarSlugPublico($_GET['programa'] ?? 'pronam') ?: 'pronam';

    $sqlTemas = "
        SELECT
            t.id,
            t.titulo,
            t.descripcion,
            t.imagen,
            t.id_especialidad,
            e.nombre AS especialidad,
            e.slug_publico
        FROM imssight.temas t
        INNER JOIN imssight.especialidades e
            ON e.id = t.id_especialidad
        WHERE
            " . condicionContenidoPublico('e') . "
            AND e.slug_publico = :programa
        ORDER BY t.id ASC
    ";

    $stmtTemas = $pdo->prepare($sqlTemas);
    $stmtTemas->execute([
        ':programa' => $programaSlug
    ]);
    $temas = $stmtTemas->fetchAll(PDO::FETCH_ASSOC);

    $sqlCasos = "
        SELECT
            c.id,
            c.titulo,
            c.descripcion,
            c.portada,
            c.id_tema,
            t.titulo AS tema,
            t.id_especialidad,
            e.nombre AS especialidad
        FROM imssight.casos_clinicos c
        INNER JOIN imssight.temas t
            ON t.id = c.id_tema
        INNER JOIN imssight.especialidades e
            ON e.id = t.id_especialidad
        WHERE
            " . condicionContenidoPublico('e') . "
            AND e.slug_publico = :programa
            AND c.activo = 1
        ORDER BY e.nombre, t.titulo, c.titulo
    ";

    $stmtCasos = $pdo->prepare($sqlCasos);
    $stmtCasos->execute([
        ':programa' => $programaSlug
    ]);
    $casos = $stmtCasos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($temas as &$tema) {
        $tema['casos'] = [];

        foreach ($casos as $caso) {
            if ((int) $caso['id_tema'] === (int) $tema['id']) {
                $tema['casos'][] = $caso;
            }
        }
    }

    unset($tema);

    $idsDisponibles = [];

    foreach ($casos as $casoDisponible) {
        $idsDisponibles[] = (int) $casoDisponible['id'];
    }

    $idSolicitado = isset($_GET['caso']) ? (int) $_GET['caso'] : 0;
    $idSeleccionado = in_array($idSolicitado, $idsDisponibles, true)
        ? $idSolicitado
        : 0;

    if ($idSeleccionado > 0) {
        foreach ($casos as $caso) {
            if ((int) $caso['id'] === $idSeleccionado) {
                $casoSeleccionado = $caso;
                break;
            }
        }
    }

    if ($casoSeleccionado) {
        $stmtEscenas = $pdo->prepare("
            SELECT
                id,
                orden,
                tipo,
                titulo,
                contenido,
                multimedia
            FROM imssight.escenas
            WHERE id_caso = ?
            ORDER BY orden ASC
        ");

        $stmtEscenas->execute([(int) $casoSeleccionado['id']]);
        $escenas = $stmtEscenas->fetchAll(PDO::FETCH_ASSOC);

        $idCasoSeleccionado = (int) $casoSeleccionado['id'];

        $stmtPerlas = $pdo->prepare("
            SELECT
                id,
                seccion,
                contenido,
                orden
            FROM imssight.perlas_clinicas_caso
            WHERE id_caso = ?
            ORDER BY orden ASC, id ASC
        ");
        $stmtPerlas->execute([$idCasoSeleccionado]);
        $perlasCaso = array_values(array_filter(
            $stmtPerlas->fetchAll(PDO::FETCH_ASSOC),
            static function ($perla) {
                return htmlTieneContenido($perla['contenido'] ?? '');
            }
        ));

        $stmtInfografias = $pdo->prepare("
            SELECT
                id,
                titulo,
                descripcion,
                ruta_imagen,
                alt_text,
                orden
            FROM imssight.infografias_caso
            WHERE id_caso = ?
              AND activo = 1
            ORDER BY orden ASC, id ASC
        ");
        $stmtInfografias->execute([$idCasoSeleccionado]);
        $infografiasCaso = $stmtInfografias->fetchAll(PDO::FETCH_ASSOC);

        $stmtEscalas = $pdo->prepare("
            SELECT
                id,
                titulo,
                descripcion,
                url,
                proveedor,
                orden
            FROM imssight.escalas_pronosticas_caso
            WHERE id_caso = ?
              AND activo = 1
            ORDER BY orden ASC, id ASC
        ");
        $stmtEscalas->execute([$idCasoSeleccionado]);
        $escalasCaso = $stmtEscalas->fetchAll(PDO::FETCH_ASSOC);

        $stmtExamenes = $pdo->prepare("
            SELECT
                id,
                titulo,
                descripcion,
                (
                    SELECT COUNT(*)
                    FROM imssight.examen_preguntas p
                    WHERE p.id_examen = imssight.examenes.id
                ) AS total_preguntas
            FROM imssight.examenes
            WHERE id_caso = ?
              AND activo = 1
              AND EXISTS (
                  SELECT 1
                  FROM imssight.examen_preguntas p
                  WHERE p.id_examen = imssight.examenes.id
              )
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmtExamenes->execute([$idCasoSeleccionado]);
        $examenesCaso = $stmtExamenes->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $errorCarga = 'No fue posible cargar los casos PRONAM en este momento.';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PRONAM por IMSSight</title>
    <link rel="apple-touch-icon" sizes="76x76" href="assets/img/favicon-pronam-ssa.svg">
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon-pronam-ssa.svg">
    <link rel="preload" as="image" href="img/inicio_sesion.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        :root {
            --imss-green: #235B4E;
            --imss-green-dark: #103D33;
            --imss-green-light: #2E7D67;
            --imss-gold: #BC955C;
            --imss-gray: #6F7271;
            --imss-border: #D6D1C8;
            --imss-soft: #F6F3EE;
            --imss-surface: #FFFFFF;
            --topbar-height: 86px;
            --option-card-title-size: clamp(30px, 4vw, 46px);
            --topic-title-size: clamp(24px, 3vw, 34px);
            --case-card-title-size: 22px;
            --case-card-title-size-featured: 25px;
            --case-viewer-title-size: clamp(28px, 4vw, 46px);
            --scene-title-size: 18px;
            --resource-card-title-size: 17px;
        }

        .pronam-page .btn-success {
            --bs-btn-bg: var(--imss-green);
            --bs-btn-border-color: var(--imss-green);
            --bs-btn-hover-bg: var(--imss-green-dark);
            --bs-btn-hover-border-color: var(--imss-green-dark);
            --bs-btn-active-bg: var(--imss-green-dark);
            --bs-btn-active-border-color: var(--imss-green-dark);
            --bs-btn-disabled-bg: var(--imss-green);
            --bs-btn-disabled-border-color: var(--imss-green);
            background-color: var(--imss-green) !important;
            border-color: var(--imss-green) !important;
        }

        .pronam-page .btn-success:hover,
        .pronam-page .btn-success:focus,
        .pronam-page .btn-success:active,
        .pronam-page .btn-success.show {
            background-color: var(--imss-green-dark) !important;
            border-color: var(--imss-green-dark) !important;
        }

        .pronam-page .btn-outline-success {
            --bs-btn-color: var(--imss-green);
            --bs-btn-border-color: var(--imss-green);
            --bs-btn-hover-bg: var(--imss-green);
            --bs-btn-hover-border-color: var(--imss-green);
            --bs-btn-active-bg: var(--imss-green-dark);
            --bs-btn-active-border-color: var(--imss-green-dark);
            --bs-btn-disabled-color: var(--imss-green);
            --bs-btn-disabled-border-color: var(--imss-green);
            background-color: transparent !important;
            border-color: var(--imss-green) !important;
            color: var(--imss-green) !important;
        }

        .pronam-page .btn-outline-success:hover,
        .pronam-page .btn-outline-success:focus,
        .pronam-page .btn-outline-success:active,
        .pronam-page .btn-outline-success.show {
            background-color: var(--imss-green) !important;
            border-color: var(--imss-green) !important;
            color: #FFFFFF !important;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            padding-top: var(--topbar-height);
            background: #F6F3EE;
            color: #2E3130;
            font-family: "Noto Sans", Arial, sans-serif;
        }

        body.has-case-selected {
            padding-top: 0;
        }

        a {
            color: inherit;
        }

        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 20;
            border-bottom: 1px solid rgba(255, 255, 255, .12);
            background: #12372F;
            box-shadow: 0 12px 28px rgba(13, 42, 36, .16);
        }

        body.has-case-selected .topbar {
            position: sticky;
        }

        .topbar-inner,
        .shell,
        .hero-inner,
        .footer-inner {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar-inner {
            min-height: var(--topbar-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }

        .brand img {
            display: block;
            height: 48px;
            width: auto;
        }

        .brand-government-logo {
            height: 42px;
            opacity: .95;
        }

        .brand-divider {
            width: 1px;
            height: 42px;
            background: rgba(255, 255, 255, .24);
        }

        .imss-logo-chip {
            height: 46px;
            opacity: .95;
        }

        .brand-title {
            display: block;
            color: #FFFFFF;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.1;
        }

        .brand-copy {
            display: block;
            color: rgba(255, 255, 255, .72);
            font-size: 13px;
            font-weight: 400;
            margin-top: 3px;
        }

        .login-btn,
        .hero-login-btn,
        .case-open-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 42px;
            border: 1px solid rgba(255, 255, 255, .38);
            border-radius: 8px;
            background: rgba(255, 255, 255, .08);
            color: #FFFFFF;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
            padding: 12px 18px;
            text-decoration: none;
            box-shadow: none;
        }

        .login-btn:hover,
        .hero-login-btn:hover,
        .case-open-btn:hover {
            border-color: rgba(255, 255, 255, .62);
            background: rgba(255, 255, 255, .16);
            color: #FFFFFF;
        }

        .login-btn .material-symbols-rounded,
        .hero-login-btn .material-symbols-rounded,
        .case-open-btn .material-symbols-rounded {
            font-size: 18px;
        }

        .hero-login-btn,
        .case-open-btn {
            border-color: var(--imss-green);
            background: var(--imss-green);
            color: #FFFFFF;
            box-shadow: 0 10px 22px rgba(0, 0, 0, .16);
        }

        .hero-login-btn:hover,
        .case-open-btn:hover {
            border-color: var(--imss-green-dark);
            background: var(--imss-green-dark);
            color: #FFFFFF;
        }

        .pronam-page {
            min-height: 100vh;
        }

        .pronam-hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 46px;
            background:
                radial-gradient(circle at 18% 44%, rgba(8, 35, 30, .88), rgba(8, 35, 30, .5) 30%, transparent 58%),
                linear-gradient(90deg, rgba(8, 35, 30, .7) 0%, rgba(16, 49, 43, .34) 44%, rgba(16, 49, 43, .08) 100%),
                url("img/inicio_sesion.png") 34% center/cover;
            color: #FFFFFF;
        }

        .pronam-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 76% 46%, rgba(255, 255, 255, .06), transparent 24%),
                linear-gradient(180deg, rgba(18, 55, 47, .02), rgba(18, 55, 47, .12));
        }

        .hero-inner {
            position: relative;
            z-index: 1;
            min-height: 660px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding-top: 142px;
            padding-bottom: 66px;
        }

        .hero-copy-block {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .pronam-title-wrap {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
            width: fit-content;
            max-width: 100%;
            padding-left: 10px;
        }

        .pronam-hero h1 {
            max-width: 760px;
            margin: 0;
            color: #FFFFFF;
            font-family: "Noto Sans", Arial, sans-serif;
            font-size: clamp(76px, 10vw, 132px);
            font-weight: 900;
            line-height: .92;
            letter-spacing: 0;
        }

        .pronam-byline {
            color: rgba(255, 255, 255, .82);
            font-size: clamp(20px, 2.4vw, 34px);
            font-weight: 400;
            line-height: 1.2;
            margin-right: 4px;
            letter-spacing: .02em;
        }

        .pronam-panel {
            width: min(520px, 100%);
            margin-top: 34px;
            border-left: 5px solid var(--imss-gold);
            background: rgba(18, 55, 47, .58);
            color: #FFFFFF;
            padding: 26px 30px;
            backdrop-filter: blur(4px);
            box-shadow: 0 20px 46px rgba(7, 31, 26, .24);
        }

        .pronam-panel strong {
            display: block;
            margin-bottom: 12px;
            color: #FFFFFF;
            font-size: 22px;
            font-weight: 800;
            line-height: 1.25;
        }

        .pronam-panel span {
            display: block;
            color: rgba(255, 255, 255, .84);
            font-size: 17px;
            font-weight: 400;
            line-height: 1.7;
        }

        .pronam-actions {
            position: absolute;
            right: 0;
            bottom: 46px;
            z-index: 2;
        }

        .btn-pronam-secondary,
        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            border-radius: 8px;
            font-weight: 800;
            padding: 12px 18px;
            text-decoration: none !important;
        }

        .btn-pronam-secondary {
            border: 1px solid rgba(255, 255, 255, .72);
            background: rgba(255, 255, 255, .08);
            color: #FFFFFF !important;
        }

        .btn-pronam-secondary:hover {
            background: rgba(255, 255, 255, .16);
            color: #FFFFFF;
        }

        .back-link {
            border: 1px solid var(--imss-border);
            background: #FFFFFF;
            color: var(--imss-green);
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
        }

        .back-link:hover {
            border-color: rgba(35, 91, 78, .38);
            color: var(--imss-green-dark);
        }

        .pronam-section {
            margin-bottom: 40px;
            scroll-margin-top: calc(var(--topbar-height) + 28px);
        }

        .shell {
            padding-bottom: 46px;
        }

        .section-title {
            margin: 0 0 6px;
            color: var(--imss-green-dark);
            font-size: 34px;
            font-weight: 700;
            line-height: 1.2;
        }

        .section-copy {
            max-width: 760px;
            margin: 0 0 28px;
            color: var(--imss-gray);
            font-size: 17px;
            line-height: 1.75;
        }

        .case-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
        }

        .option-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
        }

        .option-card {
            min-height: 230px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            border: 1px solid var(--imss-border);
            border-top: 5px solid var(--imss-gold);
            border-radius: 8px;
            background:
                linear-gradient(135deg, rgba(35, 91, 78, .09), rgba(188, 149, 92, .07)),
                #FFFFFF;
            color: #2E3130;
            cursor: pointer;
            padding: 28px;
            text-align: left;
            box-shadow: 0 12px 26px rgba(45, 46, 45, .08);
            transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }

        .option-card:hover,
        .option-card:focus {
            border-color: rgba(35, 91, 78, .52);
            outline: none;
            transform: translateY(-2px);
            box-shadow: 0 18px 34px rgba(35, 91, 78, .14);
        }

        .option-card h3 {
            margin: 8px 0 14px;
            color: var(--imss-green-dark);
            font-size: var(--option-card-title-size);
            font-weight: 800;
            line-height: 1.05;
        }

        .option-card p {
            max-width: 560px;
            margin: 0 0 24px;
            color: var(--imss-gray);
            font-size: 16px;
            line-height: 1.65;
        }

        .option-card .topic-count {
            margin-top: auto;
        }

        .topic-stack {
            display: grid;
            gap: 26px;
        }

        .topic-card {
            border: 1px solid var(--imss-border);
            border-radius: 8px;
            overflow: hidden;
            background: #FFFFFF;
            box-shadow: 0 12px 26px rgba(45, 46, 45, .08);
        }

        .topic-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: start;
            padding: 24px;
            background:
                linear-gradient(90deg, rgba(35, 91, 78, .08), rgba(188, 149, 92, .08)),
                #FFFFFF;
            border-top: 5px solid var(--imss-gold);
            border-bottom: 1px solid var(--imss-border);
        }

        .topic-eyebrow {
            margin-bottom: 8px;
            color: var(--imss-green);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .topic-head h3 {
            margin: 0;
            color: var(--imss-green-dark);
            font-size: var(--topic-title-size);
            font-weight: 800;
            line-height: 1.14;
        }

        .topic-head p {
            max-width: 760px;
            margin: 12px 0 0;
            color: var(--imss-gray);
            font-size: 16px;
            line-height: 1.65;
        }

        .topic-count {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            border-radius: 999px;
            background: rgba(35, 91, 78, .11);
            color: var(--imss-green-dark);
            font-size: 13px;
            font-weight: 800;
            padding: 8px 12px;
            white-space: nowrap;
        }

        .topic-body {
            padding: 24px;
        }

        .topic-empty {
            border: 1px dashed rgba(35, 91, 78, .32);
            border-radius: 8px;
            background: #FAF8F4;
            color: var(--imss-gray);
            padding: 18px;
            line-height: 1.6;
        }

        .topic-detail {
            display: none;
            padding-top: 44px;
        }

        .topic-detail.is-active {
            display: block;
        }

        .detail-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .detail-head p {
            max-width: 760px;
            margin: 8px 0 0;
            color: var(--imss-gray);
            font-size: 16px;
            line-height: 1.65;
        }

        .pronam-page.is-topic-selected .pronam-hero,
        .pronam-page.is-topic-selected .option-section,
        .pronam-page.is-case-selected .pronam-hero,
        .pronam-page.is-case-selected .option-section {
            display: none;
        }

        .pronam-page.is-case-selected .shell {
            width: 100%;
            max-width: none;
            padding-top: 0;
            padding-bottom: 0;
        }

        .case-card {
            position: relative;
            display: flex;
            min-height: 100%;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid var(--imss-border);
            border-radius: 8px;
            background: #FFFFFF;
            color: #2E3130;
            text-decoration: none !important;
            box-shadow: 0 12px 26px rgba(45, 46, 45, .08);
            transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }

        .case-card:hover,
        .case-card:focus {
            border-color: rgba(35, 91, 78, .52);
            color: #2E3130;
            transform: translateY(-2px);
            box-shadow: 0 18px 34px rgba(35, 91, 78, .14);
        }

        .case-card-media {
            min-height: 150px;
            background:
                linear-gradient(135deg, rgba(16, 61, 51, .88), rgba(188, 149, 92, .5)),
                var(--case-image, url("img/pronam_dm2_banner.png")) center/cover;
        }

        .case-card-body {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 24px;
        }

        .case-meta {
            margin-bottom: 10px;
            color: var(--imss-green);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.45;
        }

        .case-card h3 {
            margin: 0;
            color: var(--imss-green-dark);
            font-size: var(--case-card-title-size);
            font-weight: 700;
            line-height: 1.32;
        }

        .case-card p {
            margin: 16px 0 24px;
            color: var(--imss-gray);
            line-height: 1.72;
        }

        .case-card-cta {
            margin-top: auto;
            color: var(--imss-green);
            font-weight: 700;
        }

        .topic-detail .case-grid {
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 460px), 560px));
            gap: 32px;
        }

        .topic-detail .case-card {
            border-color: rgba(35, 91, 78, .18);
            box-shadow: 0 22px 46px rgba(35, 91, 78, .14);
        }

        .topic-detail .case-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1;
            height: 5px;
            background: linear-gradient(90deg, var(--imss-gold), rgba(35, 91, 78, .72));
        }

        .topic-detail .case-card-media {
            min-height: 150px;
            background:
                linear-gradient(135deg, rgba(35, 91, 78, .1), rgba(188, 149, 92, .08)),
                #0F332B;
            background-image:
                linear-gradient(135deg, rgba(35, 91, 78, .1), rgba(188, 149, 92, .08)),
                var(--case-image, url("img/pronam_dm2_banner.png"));
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover, contain;
        }

        .topic-detail .case-card-body {
            padding: 30px;
        }

        .topic-detail .case-card h3 {
            font-size: var(--case-card-title-size-featured);
            line-height: 1.24;
        }

        .topic-detail .case-card-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            border-radius: 8px;
            background: rgba(35, 91, 78, .1);
            color: var(--imss-green-dark);
            padding: 10px 14px;
        }

        .case-viewer {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            align-items: start;
            border: 0;
            background: transparent;
            border-radius: 0;
            overflow: visible;
            box-shadow: none;
        }

        .case-viewer-hero {
            grid-column: 2;
            min-height: 330px;
            background:
                var(--case-hero-image, linear-gradient(135deg, #235B4E, #BC955C));
            background-position: center;
            background-size: cover;
        }

        .case-viewer-summary {
            grid-column: 2;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 22px;
            align-items: start;
            padding: 30px 34px;
            border-bottom: 1px solid var(--imss-border);
            background: #FFFFFF;
        }

        .case-viewer-summary h2 {
            margin: 0;
            color: var(--imss-green-dark);
            font-size: var(--case-viewer-title-size);
            font-weight: 800;
            line-height: 1.08;
        }

        .case-viewer-summary p {
            max-width: 760px;
            margin: 12px 0 0;
            color: var(--imss-gray);
            line-height: 1.65;
        }

        .case-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .case-chip {
            border-radius: 999px;
            background: rgba(35, 91, 78, .1);
            color: var(--imss-green-dark);
            font-size: 12px;
            font-weight: 700;
            padding: 6px 10px;
        }

        .case-viewer-body {
            display: contents;
            padding: 0;
        }

        .course-layout {
            display: contents;
        }

        .course-nav {
            grid-column: 1;
            grid-row: 1 / span 3;
            position: sticky;
            top: var(--topbar-height);
            max-height: calc(100vh - var(--topbar-height));
            overflow: auto;
            min-height: calc(100vh - var(--topbar-height));
            border: 0;
            border-right: 1px solid var(--imss-border);
            border-radius: 0;
            background: #F4F6F5;
            padding: 18px 16px;
        }

        .course-content {
            grid-column: 2;
            min-width: 0;
            padding: 28px 34px 46px;
        }

        .course-nav-title {
            margin: 4px 8px 12px;
            color: var(--imss-green-dark);
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .course-nav-list {
            display: grid;
            gap: 6px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .course-nav-link {
            display: grid;
            grid-template-columns: 28px minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            border-radius: 7px;
            color: #575D5A;
            padding: 10px;
            text-decoration: none;
        }

        .course-nav-link:hover,
        .course-nav-link.is-active {
            background: var(--imss-green);
            color: #FFFFFF;
        }

        .course-nav-number {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: rgba(35, 91, 78, .12);
            color: var(--imss-green-dark);
            font-weight: 800;
        }

        .course-nav-link.is-active .course-nav-number,
        .course-nav-link:hover .course-nav-number {
            background: rgba(255, 255, 255, .18);
            color: #FFFFFF;
        }

        .course-nav-text {
            min-width: 0;
            overflow: hidden;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.28;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .scene {
            border: 1px solid var(--imss-border);
            border-radius: 8px;
            overflow: hidden;
            background: #FFFFFF;
            scroll-margin-top: calc(var(--topbar-height) + 24px);
        }

        .scene + .scene {
            margin-top: 14px;
        }

        .course-resource {
            margin-top: 14px;
        }

        .scene-head {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            position: relative;
            padding: 14px 16px;
            border-bottom: 1px solid var(--imss-border);
            background: #FAF8F4;
            cursor: pointer;
            user-select: none;
        }

        .scene-head::after {
            content: "expand_more";
            align-self: center;
            margin-left: auto;
            color: var(--imss-green-dark);
            font-family: "Material Symbols Rounded";
            font-size: 24px;
            line-height: 1;
            transition: transform .2s ease;
        }

        .scene-head:focus-visible {
            outline: 3px solid rgba(188, 149, 92, .45);
            outline-offset: -4px;
        }

        .scene.is-open .scene-head::after {
            transform: rotate(180deg);
        }

        .scene.is-collapsed .scene-head {
            border-bottom: 0;
        }

        .scene-number {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            background: var(--imss-green);
            color: #FFFFFF;
            font-weight: 800;
        }

        .scene-title {
            margin: 0;
            color: var(--imss-green-dark);
            font-size: var(--scene-title-size);
            font-weight: 700;
            line-height: 1.25;
        }

        .scene-type {
            margin-top: 3px;
            color: var(--imss-gray);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .scene-content {
            padding: 18px;
            line-height: 1.65;
        }

        .scene.is-collapsed .scene-content {
            display: none;
        }

        .scene-content img,
        .scene-content video {
            display: block;
            max-width: 100%;
            max-height: 520px;
            margin: 16px auto 0;
            border-radius: 4px;
        }

        .resource-list {
            display: grid;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .resource-card {
            border: 1px solid rgba(35, 91, 78, .18);
            border-radius: 8px;
            background: #FFFFFF;
            padding: 16px;
        }

        .resource-card h4 {
            margin: 0 0 8px;
            color: var(--imss-green-dark);
            font-size: var(--resource-card-title-size);
            font-weight: 800;
            line-height: 1.25;
        }

        .resource-card p {
            margin: 0 0 12px;
            color: var(--imss-gray);
            line-height: 1.6;
        }

        .resource-card p:last-child {
            margin-bottom: 0;
        }

        .resource-card img {
            display: block;
            width: 100%;
            max-height: 420px;
            object-fit: contain;
            border: 1px solid var(--imss-border);
            border-radius: 4px;
            background: #F8F8F7;
        }

        .floating-resource-dock {
            position: fixed;
            right: 24px;
            top: 50%;
            z-index: 30;
            display: grid;
            gap: 10px;
            transform: translateY(-50%);
        }

        .floating-resource-label {
            writing-mode: vertical-rl;
            justify-self: center;
            color: #9F2241;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .floating-resource-btn {
            position: relative;
            width: 54px;
            height: 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(159, 34, 65, .34);
            border-radius: 50%;
            background: #9F2241;
            color: #FFFFFF;
            box-shadow: 0 12px 26px rgba(159, 34, 65, .28);
            cursor: pointer;
            transition: transform .18s ease, background .18s ease, color .18s ease;
        }

        .floating-resource-btn:hover,
        .floating-resource-btn:focus-visible {
            background: #7D1933;
            color: #FFFFFF;
            transform: translateX(-3px);
            outline: none;
        }

        .floating-resource-btn .material-symbols-rounded {
            font-size: 26px;
        }

        .floating-resource-btn.is-pearl-resource {
            width: 54px;
            height: 54px;
            display: grid;
            place-items: center;
            border: 0;
            background: transparent;
            padding: 0;
            box-shadow: none;
            overflow: visible;
        }

        .floating-resource-btn.is-pearl-resource:hover,
        .floating-resource-btn.is-pearl-resource:focus-visible {
            background: transparent;
            transform: translateX(-3px) scale(1.04);
        }

        .floating-resource-btn.is-pearl-resource img {
            width: 110px;
            height: auto;
            max-width: none;
            display: block;
            object-fit: contain;
            border-radius: 0;
            transform: translateX(-1px);
            filter: drop-shadow(0 12px 18px rgba(165, 127, 44, .28));
        }

        .floating-resource-btn::after {
            content: attr(data-tooltip);
            position: absolute;
            right: calc(100% + 12px);
            top: 50%;
            min-width: 112px;
            padding: 7px 10px;
            border-radius: 6px;
            background: #172F2A;
            color: #FFFFFF;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            opacity: 0;
            pointer-events: none;
            transform: translate(6px, -50%);
            transition: opacity .16s ease, transform .16s ease;
            white-space: nowrap;
        }

        .floating-resource-btn:hover::after,
        .floating-resource-btn:focus-visible::after {
            opacity: 1;
            transform: translate(0, -50%);
        }

        .resource-modal {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: grid;
            place-items: center;
            padding: 24px;
            background: rgba(16, 32, 28, .48);
        }

        .resource-modal[hidden] {
            display: none;
        }

        .resource-modal-card {
            width: min(1180px, calc(100vw - 36px));
            max-height: min(900px, calc(100vh - 36px));
            overflow: auto;
            border-radius: 8px;
            background: #FFFFFF;
            box-shadow: 0 28px 60px rgba(16, 32, 28, .32);
        }

        .resource-modal-head {
            position: sticky;
            top: 0;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            padding: 18px 20px;
            border-bottom: 1px solid var(--imss-border);
            background: #FFFFFF;
        }

        .resource-modal-title {
            margin: 0;
            color: var(--imss-green-dark);
            font-size: 22px;
            font-weight: 800;
        }

        .resource-modal-body {
            padding: 20px;
        }

        .resource-modal-body .resource-list {
            gap: 18px;
        }

        .resource-modal-body .pearl-resource-list {
            display: grid;
            gap: 16px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .pearl-resource-card {
            padding: 18px 20px;
            border: 1px solid rgba(35, 91, 78, .16);
            border-radius: 8px;
            background: #FFFFFF;
            color: var(--imss-text);
            box-shadow: 0 12px 28px rgba(16, 32, 28, .06);
        }

        .pearl-resource-card > :first-child {
            margin-top: 0;
        }

        .pearl-resource-card > :last-child {
            margin-bottom: 0;
        }

        .infografia-gallery {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .infografia-thumb {
            width: 100%;
            min-height: 178px;
            display: grid;
            grid-template-rows: 112px auto;
            gap: 10px;
            border: 1px solid rgba(35, 91, 78, .16);
            border-radius: 8px;
            background: #FFFFFF;
            padding: 10px;
            color: var(--imss-green-dark);
            text-align: left;
            cursor: pointer;
        }

        .infografia-thumb:hover,
        .infografia-thumb:focus-visible,
        .infografia-thumb.is-selected {
            border-color: #9F2241;
            box-shadow: 0 12px 24px rgba(159, 34, 65, .16);
            outline: none;
        }

        .infografia-thumb img {
            width: 100%;
            height: 112px;
            object-fit: cover;
            border-radius: 5px;
            background: #F8F8F7;
        }

        .infografia-thumb-title {
            display: block;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.25;
        }

        .infografia-preview {
            position: relative;
            margin-top: 20px;
            border: 1px solid var(--imss-border);
            border-radius: 8px;
            background: #F8F8F7;
            padding: 16px;
        }

        .infografia-preview.is-zoomed {
            background:
                linear-gradient(90deg, rgba(16, 61, 51, .04) 1px, transparent 1px),
                #ECEAE4;
            background-size: 18px 18px;
        }

        .infografia-preview[hidden] {
            display: none;
        }

        .infografia-preview h4 {
            margin: 0 0 12px;
            color: var(--imss-green-dark);
            font-size: 20px;
            font-weight: 800;
        }

        .infografia-preview p {
            margin: -4px 0 14px;
            color: var(--imss-gray);
            line-height: 1.6;
        }

        .infografia-preview-scroll {
            max-height: 74vh;
            overflow: auto;
            border-radius: 6px;
            background: #FFFFFF;
        }

        .infografia-preview-scroll img {
            display: block;
            width: 100%;
            max-width: 100%;
            object-fit: contain;
            border-radius: 6px;
            background: #FFFFFF;
        }

        .infografia-preview.is-zoomed .infografia-preview-scroll {
            max-height: 76vh;
            padding: 26px;
        }

        .infografia-preview.is-zoomed .infografia-preview-scroll img {
            width: auto;
            max-width: none;
            min-width: min(794px, 100%);
            height: auto;
            min-height: 1123px;
            margin: 0 auto;
            border: 1px solid rgba(16, 61, 51, .12);
            box-shadow: 0 18px 34px rgba(16, 32, 28, .18);
        }

        .infografia-zoom-btn {
            position: absolute;
            right: 26px;
            top: 26px;
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 50%;
            background: #9F2241;
            color: #FFFFFF;
            box-shadow: 0 12px 24px rgba(16, 32, 28, .22);
            cursor: pointer;
        }

        .resource-modal-close {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--imss-border);
            border-radius: 50%;
            background: #FFFFFF;
            color: var(--imss-green-dark);
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .floating-resource-dock {
                right: 14px;
                bottom: 18px;
                top: auto;
                grid-auto-flow: column;
                align-items: center;
                transform: none;
            }

            .floating-resource-label {
                writing-mode: initial;
            }

            .floating-resource-btn::after {
                right: 50%;
                top: auto;
                bottom: calc(100% + 10px);
                transform: translate(50%, 6px);
            }

            .floating-resource-btn:hover::after,
            .floating-resource-btn:focus-visible::after {
                transform: translate(50%, 0);
            }

            .infografia-gallery {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 560px) {
            .infografia-gallery {
                grid-template-columns: 1fr;
            }
        }

        .empty-state,
        .error-state {
            border: 1px dashed var(--imss-border);
            background: #FFFFFF;
            color: var(--imss-gray);
            padding: 20px;
            line-height: 1.5;
        }

        .site-footer {
            margin-top: 48px;
            background: #12372F;
            color: rgba(255, 255, 255, .82);
        }

        .footer-inner {
            display: grid;
            grid-template-columns: 1.15fr 1fr 1fr;
            gap: 32px;
            padding: 34px 0;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 14px;
        }

        .footer-brand img {
            display: block;
            height: 42px;
            width: auto;
        }

        .footer-brand strong {
            display: block;
            color: #FFFFFF;
            font-size: 18px;
        }

        .footer-brand span {
            display: block;
            color: rgba(255, 255, 255, .68);
            font-size: 13px;
        }

        .footer-title {
            margin: 0 0 12px;
            color: #FFFFFF;
            font-size: 15px;
            font-weight: 800;
        }

        .footer-list {
            display: grid;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .footer-list a {
            color: rgba(255, 255, 255, .78);
            text-decoration: none;
        }

        .footer-list a:hover {
            color: #FFFFFF;
            text-decoration: underline;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, .14);
            background: #0D2A24;
            color: rgba(255, 255, 255, .62);
            font-size: 13px;
        }

        .footer-bottom .footer-inner {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 0;
        }

        @media (max-width: 991px) {
            .case-grid {
                grid-template-columns: 1fr;
            }

            .option-grid {
                grid-template-columns: 1fr;
            }

            .topic-head {
                grid-template-columns: 1fr;
            }

            .detail-head {
                flex-direction: column;
            }

            .case-viewer {
                display: block;
            }

            .case-viewer-hero {
                min-height: 240px;
            }

            .case-viewer-summary {
                grid-template-columns: 1fr;
                padding: 24px;
            }

            .case-viewer-body {
                display: block;
                padding: 0;
            }

            .course-layout {
                display: block;
            }

            .course-nav {
                position: static;
                min-height: 0;
                max-height: none;
                border-right: 0;
                border-bottom: 1px solid var(--imss-border);
            }

            .course-content {
                padding: 22px;
            }

            .pronam-hero {
                background:
                    radial-gradient(circle at 22% 34%, rgba(8, 35, 30, .9), rgba(8, 35, 30, .5) 34%, transparent 66%),
                    linear-gradient(180deg, rgba(8, 35, 30, .74) 0%, rgba(16, 49, 43, .34) 100%),
                    url("img/inicio_sesion.png") 38% center/cover;
            }

            .hero-inner {
                min-height: 600px;
                gap: 36px;
                align-content: center;
            }

            .pronam-actions {
                position: static;
                margin-top: 30px;
                align-self: flex-start;
            }

            .footer-inner {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            :root {
                --topbar-height: 78px;
            }

            .topbar-inner,
            .shell,
            .hero-inner,
            .footer-inner {
                width: min(100% - 22px, 1180px);
            }

            .brand img {
                height: 30px;
            }

            .imss-logo-chip {
                height: 32px;
            }

            .brand-copy {
                display: none;
            }

            .footer-bottom .footer-inner {
                flex-direction: column;
            }

            .hero-inner {
                min-height: 540px;
                padding-top: 92px;
                padding-bottom: 44px;
            }

            .pronam-hero h1 {
                font-size: clamp(68px, 20vw, 108px);
            }

            .pronam-title-wrap {
                align-items: flex-start;
                padding-left: 0;
            }

            .pronam-panel {
                margin-top: 26px;
                padding: 22px;
            }

            .pronam-panel strong {
                font-size: 20px;
            }

            .pronam-panel span {
                font-size: 16px;
                line-height: 1.6;
            }

            .pronam-tagline {
                text-align: left;
            }
        }
    </style>
</head>
<body class="<?= $casoSeleccionado ? 'has-case-selected' : '' ?>">
    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand" aria-label="IMSSight">
                <img class="brand-government-logo" src="assets/img/logo_gob_mx.png" alt="Gobierno de Mexico">
                <span class="brand-divider" aria-hidden="true"></span>
                <img class="imss-logo-chip" src="assets/img/logo_imss_blanco.png" alt="IMSS">
                <span>
                    <span class="brand-title">IMSSight</span>
                    <span class="brand-copy">Formacion clinica interactiva</span>
                </span>
            </div>

            <a class="login-btn" href="pages/sign-in.html">
                <span class="material-symbols-rounded" aria-hidden="true">login</span>
                Inicio de sesion
            </a>
        </div>
    </header>

    <main class="page pronam-page<?= $casoSeleccionado ? ' is-case-selected' : '' ?>">
        <section class="pronam-hero">
            <div class="hero-inner">
                <div class="hero-copy-block">
                    <div class="pronam-title-wrap">
                        <h1>PRONAM</h1>
                        <span class="pronam-byline">por IMSSight</span>
                    </div>

                    <aside class="pronam-panel">
                        <span>Elige una opcion para abrir recursos informativos o casos clinicos guiados.</span>
                    </aside>
                </div>

                <div class="pronam-actions">
                    <a class="btn-pronam-secondary" href="#casos-pronam">Ver opciones disponibles</a>
                </div>
            </div>
        </section>

        <div class="shell">
            <?php if ($errorCarga): ?>
                <div class="error-state">
                    <?= h($errorCarga) ?>
                </div>
            <?php elseif (!$temas): ?>
                <div class="empty-state">
                    Todavia no hay temas publicados para este programa. Cuando se agreguen en el administrador, apareceran en esta pagina.
                </div>
            <?php else: ?>
                <section class="pronam-section option-section" id="casos-pronam">
                    <h2 class="section-title">Selecciona una opcion</h2>
                    <p class="section-copy">Ambiente virtual de aprendizaje.</p>

                    <div class="option-grid">
                        <?php foreach ($temas as $tema): ?>
                            <?php
                                $casosTema = $tema['casos'] ?? [];
                                $totalCasosTema = count($casosTema);
                            ?>
                            <button class="option-card" type="button" data-topic-option="<?= h($tema['id']) ?>">
                                <span class="topic-eyebrow"><?= h($tema['especialidad']) ?></span>
                                <h3><?= h($tema['titulo']) ?></h3>
                                <span class="topic-count">
                                    <?= $totalCasosTema === 1 ? '1 recurso' : $totalCasosTema . ' recursos' ?>
                                </span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </section>

                <?php foreach ($temas as $tema): ?>
                    <?php
                        $casosTema = $tema['casos'] ?? [];
                        $totalCasosTema = count($casosTema);
                    ?>
                    <section class="pronam-section topic-detail" id="tema-<?= h($tema['id']) ?>" data-topic-detail="<?= h($tema['id']) ?>">
                        <div class="detail-head">
                            <div>
                                <div class="topic-eyebrow"><?= h($tema['especialidad']) ?></div>
                                <h2 class="section-title"><?= h($tema['titulo']) ?></h2>
                            </div>
                            <button class="back-link" type="button" data-back-options>Volver a opciones</button>
                        </div>

                        <?php if (!$casosTema): ?>
                            <div class="topic-empty">
                                Este tema esta listo para alojar contenido informativo o casos guiados. Cuando agregues un caso dentro de este tema, aparecera aqui como tarjeta seleccionable.
                            </div>
                        <?php else: ?>
                            <div class="case-grid">
                                <?php foreach ($casosTema as $caso): ?>
                                    <?php
                                        $portadaCard = mediaUrl($caso['portada'] ?? '');
                                        $cardStyle = $portadaCard
                                            ? "--case-image: url('" . h($portadaCard) . "');"
                                            : '';
                                    ?>
                                    <a class="case-card" href="?programa=<?= h($programaSlug) ?>&caso=<?= h($caso['id']) ?>#caso-seleccionado">
                                        <div class="case-card-media" style="<?= $cardStyle ?>"></div>
                                        <div class="case-card-body">
                                            <?php if (!empty($caso['descripcion'])): ?>
                                                <p><?= nl2br(h($caso['descripcion'])) ?></p>
                                            <?php endif; ?>
                                            <span class="case-card-cta">Abrir recurso</span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>

                <?php if ($casoSeleccionado): ?>
                    <?php
                        $portada = mediaUrl($casoSeleccionado['portada'] ?? '');
                        $heroStyle = $portada
                            ? "--case-hero-image: url('" . h($portada) . "');"
                            : '';
                        $mostrarExamenesCaso = $examenesCaso;
                        $hayRecursosCaso = $perlasCaso || $infografiasCaso || $escalasCaso || $mostrarExamenesCaso;
                        $hayContenidoCurso = $escenas || $hayRecursosCaso;
                    ?>
                    <section class="case-viewer" id="caso-seleccionado">
                        <div class="case-viewer-hero" style="<?= $heroStyle ?>" aria-label="<?= h($casoSeleccionado['titulo']) ?>"></div>

                        <div class="case-viewer-summary">
                            <div>
                                <div class="case-chip-row">
                                    <span class="case-chip"><?= h($casoSeleccionado['especialidad']) ?></span>
                                    <span class="case-chip"><?= h($casoSeleccionado['tema']) ?></span>
                                </div>
                                <h2><?= h($casoSeleccionado['titulo']) ?></h2>
                                <?php if (!empty($casoSeleccionado['descripcion'])): ?>
                                    <p><?= nl2br(h($casoSeleccionado['descripcion'])) ?></p>
                                <?php endif; ?>
                            </div>
                            <a class="back-link" href="index.php?programa=<?= h($programaSlug) ?>#casos-pronam">Volver a opciones</a>
                        </div>

                        <div class="case-viewer-body">
                            <?php if (!$hayContenidoCurso): ?>
                                <div class="empty-state">
                                    Este caso todavia no tiene contenido publicado.
                                </div>
                            <?php else: ?>
                                <div class="course-layout">
                                    <aside class="course-nav" aria-label="Contenido del curso">
                                        <div class="course-nav-title">Contenido</div>
                                        <ol class="course-nav-list">
                                            <?php $courseIndex = 0; ?>
                                            <?php foreach ($escenas as $index => $escena): ?>
                                                <?php $courseIndex += 1; ?>
                                                <li>
                                                    <a class="course-nav-link<?= $courseIndex === 1 ? ' is-active' : '' ?>" href="#escena-<?= h($escena['id']) ?>" data-course-link="escena-<?= h($escena['id']) ?>">
                                                        <span class="course-nav-number"><?= $courseIndex ?></span>
                                                        <span class="course-nav-text"><?= h($escena['titulo'] ?: 'Escena clinica') ?></span>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                            <?php if ($mostrarExamenesCaso): ?>
                                                <?php $courseIndex += 1; ?>
                                                <li>
                                                    <a class="course-nav-link<?= $courseIndex === 1 ? ' is-active' : '' ?>" href="#examen-caso" data-course-link="examen-caso">
                                                        <span class="course-nav-number"><?= $courseIndex ?></span>
                                                        <span class="course-nav-text">Examen</span>
                                                    </a>
                                                </li>
                                                <?php $courseIndex += 1; ?>
                                                <li>
                                                    <a class="course-nav-link<?= $courseIndex === 1 ? ' is-active' : '' ?>" href="#constancia-caso" data-course-link="constancia-caso">
                                                        <span class="course-nav-number"><?= $courseIndex ?></span>
                                                        <span class="course-nav-text">Constancia</span>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ol>
                                    </aside>

                                    <div class="course-content">
                                        <?php $contentIndex = 0; ?>
                                        <?php foreach ($escenas as $index => $escena): ?>
                                            <?php $contentIndex += 1; ?>
                                            <section class="scene" id="escena-<?= h($escena['id']) ?>" data-course-section="escena-<?= h($escena['id']) ?>">
                                                <div class="scene-head">
                                                    <span class="scene-number"><?= $contentIndex ?></span>
                                                    <div>
                                                        <h3 class="scene-title"><?= h($escena['titulo'] ?: 'Escena clinica') ?></h3>
                                                        <div class="scene-type"><?= h($escena['tipo'] ?: 'contenido') ?></div>
                                                    </div>
                                                </div>
                                                <div class="scene-content">
                                                    <?= $escena['contenido'] ?: '' ?>

                                                    <?php
                                                        $media = mediaUrl($escena['multimedia'] ?? '');
                                                        $extension = strtolower(pathinfo(parse_url($media, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
                                                    ?>

                                                    <?php if ($media && in_array($extension, ['mp4', 'webm', 'ogg'], true)): ?>
                                                        <video controls>
                                                            <source src="<?= h($media) ?>">
                                                        </video>
                                                    <?php elseif ($media && in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)): ?>
                                                        <img src="<?= h($media) ?>" alt="<?= h($escena['titulo'] ?: 'Recurso del caso') ?>">
                                                    <?php elseif ($media): ?>
                                                        <p>
                                                            <a class="case-open-btn" href="<?= h($media) ?>" target="_blank" rel="noopener">
                                                                <span class="material-symbols-rounded" aria-hidden="true">open_in_new</span>
                                                                Abrir recurso
                                                            </a>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </section>
                                        <?php endforeach; ?>

                                        <?php if ($mostrarExamenesCaso): ?>
                                            <?php $contentIndex += 1; ?>
                                            <section class="scene course-resource" id="examen-caso" data-course-section="examen-caso">
                                                <div class="scene-head">
                                                    <span class="scene-number"><?= $contentIndex ?></span>
                                                    <div>
                                                        <h3 class="scene-title">Examen</h3>
                                                        <div class="scene-type">Evaluacion y preguntas del caso</div>
                                                    </div>
                                                </div>
                                                <div class="scene-content">
                                                    <ul class="resource-list">
                                                        <?php foreach ($mostrarExamenesCaso as $examen): ?>
                                                            <li class="resource-card">
                                                                <h4><?= h($examen['titulo'] ?: 'Examen del caso') ?></h4>
                                                                <?php if (!empty($examen['descripcion'])): ?>
                                                                    <p><?= nl2br(h($examen['descripcion'])) ?></p>
                                                                <?php endif; ?>
                                                                <a class="case-open-btn" href="pronam_examen.php?id=<?= h($examen['id']) ?>&caso=<?= h($casoSeleccionado['id']) ?>&programa=<?= h($programaSlug) ?>">
                                                                    <span class="material-symbols-rounded" aria-hidden="true">assignment</span>
                                                                    Abrir examen
                                                                </a>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </section>

                                            <?php $contentIndex += 1; ?>
                                            <section class="scene course-resource" id="constancia-caso" data-course-section="constancia-caso">
                                                <div class="scene-head">
                                                    <span class="scene-number"><?= $contentIndex ?></span>
                                                    <div>
                                                        <h3 class="scene-title">Constancia</h3>
                                                        <div class="scene-type">Desbloqueo posterior a evaluación aprobatoria</div>
                                                    </div>
                                                </div>
                                                <div class="scene-content">
                                                    <ul class="resource-list">
                                                        <?php foreach ($mostrarExamenesCaso as $examen): ?>
                                                            <li class="resource-card">
                                                                <h4>Constancia PRONAM</h4>
                                                                <p>La constancia se desbloquea al aprobar el cuestionario final. Al abrirla se solicitarán nombre, matrícula y categoría.</p>
                                                                <a class="case-open-btn" href="pronam_examen.php?id=<?= h($examen['id']) ?>&caso=<?= h($casoSeleccionado['id']) ?>&programa=<?= h($programaSlug) ?>&view=constancia">
                                                                    <span class="material-symbols-rounded" aria-hidden="true">lock</span>
                                                                    Abrir constancia
                                                                </a>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </section>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($infografiasCaso || $escalasCaso || $perlasCaso): ?>
                            <div class="floating-resource-dock" aria-label="Recursos complementarios">
                                <span class="floating-resource-label">Recursos</span>
                                <?php if ($infografiasCaso): ?>
                                    <button class="floating-resource-btn" type="button" data-resource-open="infografias" data-tooltip="Infografías" aria-label="Abrir infografías">
                                        <span class="material-symbols-rounded" aria-hidden="true">image</span>
                                    </button>
                                <?php endif; ?>
                                <?php if ($escalasCaso): ?>
                                    <button class="floating-resource-btn" type="button" data-resource-open="escalas" data-tooltip="Escalas" aria-label="Abrir escalas">
                                        <span class="material-symbols-rounded" aria-hidden="true">calculate</span>
                                    </button>
                                <?php endif; ?>
                                <?php if ($perlasCaso): ?>
                                    <button class="floating-resource-btn is-pearl-resource" type="button" data-resource-open="perlas" data-tooltip="Perlas" aria-label="Abrir perlas">
                                        <img src="img/perla.png" alt="">
                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php if ($infografiasCaso): ?>
                                <div class="resource-modal" id="resourceModalInfografias" data-resource-modal="infografias" hidden>
                                    <div class="resource-modal-card" role="dialog" aria-modal="true" aria-labelledby="resourceInfografiasTitle">
                                        <header class="resource-modal-head">
                                            <h3 class="resource-modal-title" id="resourceInfografiasTitle">Infografías</h3>
                                            <button class="resource-modal-close" type="button" data-resource-close aria-label="Cerrar infografías">
                                                <span class="material-symbols-rounded" aria-hidden="true">close</span>
                                            </button>
                                        </header>
                                        <div class="resource-modal-body">
                                            <ul class="infografia-gallery">
                                                <?php foreach ($infografiasCaso as $infografia): ?>
                                                    <?php $rutaInfografia = mediaUrl($infografia['ruta_imagen'] ?? ''); ?>
                                                    <li>
                                                        <button
                                                            class="infografia-thumb"
                                                            type="button"
                                                            data-infografia-select
                                                            data-infografia-src="<?= h($rutaInfografia) ?>"
                                                            data-infografia-title="<?= h($infografia['titulo'] ?: 'Infografía') ?>"
                                                            data-infografia-description="<?= h($infografia['descripcion'] ?? '') ?>"
                                                            data-infografia-alt="<?= h($infografia['alt_text'] ?: ($infografia['titulo'] ?: 'Infografía del caso')) ?>">
                                                            <?php if ($rutaInfografia): ?>
                                                                <img src="<?= h($rutaInfografia) ?>" alt="">
                                                            <?php endif; ?>
                                                            <span class="infografia-thumb-title"><?= h($infografia['titulo'] ?: 'Infografía') ?></span>
                                                        </button>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <section class="infografia-preview" data-infografia-preview hidden>
                                                <button class="infografia-zoom-btn" type="button" data-infografia-zoom aria-label="Ampliar infografía" aria-pressed="false">
                                                    <span class="material-symbols-rounded" aria-hidden="true">zoom_in</span>
                                                </button>
                                                <h4 data-infografia-preview-title></h4>
                                                <p data-infografia-preview-description hidden></p>
                                                <div class="infografia-preview-scroll">
                                                    <img data-infografia-preview-image src="" alt="">
                                                </div>
                                            </section>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($escalasCaso): ?>
                                <div class="resource-modal" id="resourceModalEscalas" data-resource-modal="escalas" hidden>
                                    <div class="resource-modal-card" role="dialog" aria-modal="true" aria-labelledby="resourceEscalasTitle">
                                        <header class="resource-modal-head">
                                            <h3 class="resource-modal-title" id="resourceEscalasTitle">Escalas</h3>
                                            <button class="resource-modal-close" type="button" data-resource-close aria-label="Cerrar escalas">
                                                <span class="material-symbols-rounded" aria-hidden="true">close</span>
                                            </button>
                                        </header>
                                        <div class="resource-modal-body">
                                            <ul class="resource-list">
                                                <?php foreach ($escalasCaso as $escala): ?>
                                                    <li class="resource-card">
                                                        <h4><?= h($escala['titulo'] ?: 'Escala') ?></h4>
                                                        <?php if (!empty($escala['descripcion'])): ?>
                                                            <p><?= nl2br(h($escala['descripcion'])) ?></p>
                                                        <?php endif; ?>
                                                        <a class="case-open-btn" href="<?= h($escala['url']) ?>" target="_blank" rel="noopener">
                                                            <span class="material-symbols-rounded" aria-hidden="true">open_in_new</span>
                                                            Abrir escala<?= !empty($escala['proveedor']) ? ' - ' . h($escala['proveedor']) : '' ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($perlasCaso): ?>
                                <div class="resource-modal" id="resourceModalPerlas" data-resource-modal="perlas" hidden>
                                    <div class="resource-modal-card" role="dialog" aria-modal="true" aria-labelledby="resourcePerlasTitle">
                                        <header class="resource-modal-head">
                                            <h3 class="resource-modal-title" id="resourcePerlasTitle">Perlas</h3>
                                            <button class="resource-modal-close" type="button" data-resource-close aria-label="Cerrar perlas">
                                                <span class="material-symbols-rounded" aria-hidden="true">close</span>
                                            </button>
                                        </header>
                                        <div class="resource-modal-body">
                                            <ul class="pearl-resource-list">
                                                <?php foreach ($perlasCaso as $perla): ?>
                                                    <li class="pearl-resource-card">
                                                        <?= $perla['contenido'] ?: '' ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <section>
                <div class="footer-brand">
                    <img src="assets/img/logo_gob_mx.png" alt="Gobierno de Mexico">
                    <span>
                        <strong>IMSSight</strong>
                        <span>Unidad de Educacion e Investigacion</span>
                    </span>
                </div>
                <p>
                    Plataforma de formacion clinica interactiva para fortalecer el aprendizaje con casos, escenas y recursos educativos.
                </p>
            </section>

            <section>
                <h2 class="footer-title">Enlaces</h2>
                <ul class="footer-list">
                    <li><a href="pages/sign-in.html">Inicio de sesion</a></li>
                    <li><a href="#casos-pronam">PRONAM</a></li>
                    <li><a href="pages/sign-up.html">Crear cuenta</a></li>
                </ul>
            </section>

            <section>
                <h2 class="footer-title">Contacto institucional</h2>
                <p>
                    Direccion de Prestaciones Medicas, IMSS.<br>
                    Contenido educativo para fines de formacion clinica.
                </p>
            </section>
        </div>

        <div class="footer-bottom">
            <div class="footer-inner">
                <span>Gobierno de Mexico · Instituto Mexicano del Seguro Social</span>
                <span><?= date('Y') ?> · IMSSight</span>
            </div>
        </div>
    </footer>
    <script>
        (function () {
            var page = document.querySelector('.pronam-page');
            var optionSection = document.querySelector('.option-section');
            var optionButtons = document.querySelectorAll('[data-topic-option]');
            var detailSections = document.querySelectorAll('[data-topic-detail]');
            var backButtons = document.querySelectorAll('[data-back-options]');
            var courseLinks = document.querySelectorAll('[data-course-link]');
            var courseSections = document.querySelectorAll('[data-course-section]');
            var resourceButtons = document.querySelectorAll('[data-resource-open]');
            var resourceModals = document.querySelectorAll('[data-resource-modal]');

            if (!page || !optionSection) {
                return;
            }

            function getDetailById(topicId) {
                for (var i = 0; i < detailSections.length; i += 1) {
                    if (detailSections[i].getAttribute('data-topic-detail') === topicId) {
                        return detailSections[i];
                    }
                }

                return null;
            }

            function showTopic(topicId, shouldScroll) {
                var activeDetail = getDetailById(topicId);

                if (!activeDetail) {
                    return;
                }

                for (var i = 0; i < detailSections.length; i += 1) {
                    detailSections[i].classList.toggle('is-active', detailSections[i] === activeDetail);
                }

                page.classList.add('is-topic-selected');
                page.classList.remove('is-case-selected');

                if (history.replaceState) {
                    history.replaceState(null, '', '#tema-' + topicId);
                }

                if (shouldScroll) {
                    activeDetail.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }

            function showOptions() {
                for (var i = 0; i < detailSections.length; i += 1) {
                    detailSections[i].classList.remove('is-active');
                }

                page.classList.remove('is-topic-selected');
                page.classList.remove('is-case-selected');

                if (history.replaceState) {
                    history.replaceState(null, '', window.location.pathname + window.location.search + '#casos-pronam');
                }

                optionSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            for (var i = 0; i < optionButtons.length; i += 1) {
                optionButtons[i].addEventListener('click', function () {
                    showTopic(this.getAttribute('data-topic-option'), true);
                });
            }

            for (var j = 0; j < backButtons.length; j += 1) {
                backButtons[j].addEventListener('click', showOptions);
            }

            function setActiveCourseItem(itemId) {
                for (var i = 0; i < courseLinks.length; i += 1) {
                    courseLinks[i].classList.toggle('is-active', courseLinks[i].getAttribute('data-course-link') === itemId);
                }
            }

            function closeResourceModals() {
                for (var i = 0; i < resourceModals.length; i += 1) {
                    resourceModals[i].hidden = true;
                }
            }

            function openResourceModal(resourceKey) {
                closeResourceModals();

                for (var i = 0; i < resourceModals.length; i += 1) {
                    if (resourceModals[i].getAttribute('data-resource-modal') === resourceKey) {
                        resourceModals[i].hidden = false;
                        return;
                    }
                }
            }

            function selectInfografia(button) {
                if (!button) {
                    return;
                }

                var modal = button.closest('[data-resource-modal]');
                var preview = modal ? modal.querySelector('[data-infografia-preview]') : null;
                var image = preview ? preview.querySelector('[data-infografia-preview-image]') : null;
                var title = preview ? preview.querySelector('[data-infografia-preview-title]') : null;
                var description = preview ? preview.querySelector('[data-infografia-preview-description]') : null;
                var src = button.getAttribute('data-infografia-src') || '';
                var text = button.getAttribute('data-infografia-title') || 'Infografía';
                var descriptionText = button.getAttribute('data-infografia-description') || '';

                if (!preview || !image || !src) {
                    return;
                }

                modal.querySelectorAll('[data-infografia-select]').forEach(function (item) {
                    item.classList.toggle('is-selected', item === button);
                });

                if (title) {
                    title.textContent = text;
                }

                if (description) {
                    description.textContent = descriptionText;
                    description.hidden = descriptionText.trim() === '';
                }

                image.src = src;
                image.alt = button.getAttribute('data-infografia-alt') || text;
                preview.classList.remove('is-zoomed');

                var zoomButton = preview.querySelector('[data-infografia-zoom]');

                if (zoomButton) {
                    zoomButton.setAttribute('aria-pressed', 'false');
                    zoomButton.querySelector('.material-symbols-rounded').textContent = 'zoom_in';
                }

                preview.hidden = false;
                preview.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }

            function setCourseSectionOpen(section, shouldOpen) {
                if (!section) {
                    return;
                }

                var head = section.querySelector('.scene-head');

                section.classList.toggle('is-open', shouldOpen);
                section.classList.toggle('is-collapsed', !shouldOpen);
                section.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');

                if (head) {
                    head.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
                }
            }

            function closeOtherCourseSections(section) {
                for (var i = 0; i < courseSections.length; i += 1) {
                    if (courseSections[i] !== section) {
                        setCourseSectionOpen(courseSections[i], false);
                    }
                }
            }

            function openCourseSection(section, behavior, shouldUpdateHash) {
                if (!section) {
                    return;
                }

                var targetId = section.getAttribute('data-course-section') || section.id;

                closeOtherCourseSections(section);
                setCourseSectionOpen(section, true);
                setActiveCourseItem(targetId);

                if (shouldUpdateHash && history.replaceState) {
                    history.replaceState(null, '', '#' + targetId);
                }

                scrollToCourseSection(section, behavior || 'smooth');
            }

            function toggleCourseSection(section) {
                if (!section) {
                    return;
                }

                if (section.classList.contains('is-open')) {
                    setCourseSectionOpen(section, false);
                    setActiveCourseItem('');
                    return;
                }

                openCourseSection(section, 'smooth', true);
            }

            function getCourseScrollTarget(section) {
                if (!section) {
                    return null;
                }

                return section.querySelector('.pc13-stage') || section;
            }

            function scrollToCourseSection(section, behavior) {
                var scrollTarget = getCourseScrollTarget(section);

                if (!scrollTarget) {
                    return;
                }

                var topbar = document.querySelector('.topbar');
                var topbarHeight = topbar ? topbar.getBoundingClientRect().height : 0;
                var offset = Math.max(0, topbarHeight - 12);
                var scrollTop = scrollTarget.getBoundingClientRect().top + window.pageYOffset - offset;

                window.scrollTo({
                    top: Math.max(0, scrollTop),
                    behavior: behavior || 'auto'
                });
            }

            for (var k = 0; k < courseLinks.length; k += 1) {
                courseLinks[k].addEventListener('click', function (event) {
                    var targetId = this.getAttribute('data-course-link');
                    var target = document.getElementById(targetId);

                    if (!target) {
                        return;
                    }

                    event.preventDefault();
                    openCourseSection(target, 'smooth', true);
                });
            }

            for (var resourceIndex = 0; resourceIndex < resourceButtons.length; resourceIndex += 1) {
                resourceButtons[resourceIndex].addEventListener('click', function () {
                    openResourceModal(this.getAttribute('data-resource-open'));
                });
            }

            for (var modalIndex = 0; modalIndex < resourceModals.length; modalIndex += 1) {
                resourceModals[modalIndex].addEventListener('click', function (event) {
                    var infografiaButton = event.target.closest('[data-infografia-select]');

                    if (infografiaButton && this.contains(infografiaButton)) {
                        selectInfografia(infografiaButton);
                        return;
                    }

                    if (event.target.closest('[data-infografia-zoom]')) {
                        var preview = this.querySelector('[data-infografia-preview]');
                        var zoomButton = event.target.closest('[data-infografia-zoom]');

                        if (preview && !preview.hidden) {
                            var isZoomed = preview.classList.toggle('is-zoomed');
                            zoomButton.setAttribute('aria-pressed', isZoomed ? 'true' : 'false');
                            zoomButton.querySelector('.material-symbols-rounded').textContent = isZoomed ? 'zoom_out' : 'zoom_in';
                        }

                        return;
                    }

                    if (event.target === this || event.target.closest('[data-resource-close]')) {
                        closeResourceModals();
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeResourceModals();
                }
            });

            for (var sectionIndex = 0; sectionIndex < courseSections.length; sectionIndex += 1) {
                (function (section) {
                    var head = section.querySelector('.scene-head');

                    setCourseSectionOpen(section, false);

                    if (!head) {
                        return;
                    }

                    head.setAttribute('role', 'button');
                    head.setAttribute('tabindex', '0');
                    head.addEventListener('click', function () {
                        toggleCourseSection(section);
                    });
                    head.addEventListener('keydown', function (event) {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            toggleCourseSection(section);
                        }
                    });
                }(courseSections[sectionIndex]));
            }

            setActiveCourseItem('');

            document.addEventListener('click', function (event) {
                var nextButton = event.target.closest('[data-pc13-next]');

                if (!nextButton) {
                    return;
                }

                var section = nextButton.closest('[data-course-section]');
                var root = nextButton.closest('[data-pc13-root]');
                var slides = root ? root.querySelectorAll('[data-pc13-slide]') : [];
                var activeSlide = root ? root.querySelector('[data-pc13-slide].is-active') : null;
                var isLastSlide = slides.length > 0 && activeSlide === slides[slides.length - 1];

                if (!section || !isLastSlide) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                window.setTimeout(function () {
                    var nextSection = section.nextElementSibling;

                    while (nextSection && !nextSection.matches('[data-course-section]')) {
                        nextSection = nextSection.nextElementSibling;
                    }

                    if (nextSection) {
                        openCourseSection(nextSection, 'smooth', true);
                    }
                }, 80);
            }, true);

            if ('IntersectionObserver' in window && courseSections.length) {
                var observer = new IntersectionObserver(function (entries) {
                    var visible = entries
                        .filter(function (entry) {
                            return entry.isIntersecting;
                        })
                        .sort(function (first, second) {
                            return second.intersectionRatio - first.intersectionRatio;
                        });

                    if (visible[0]) {
                        if (visible[0].target.classList.contains('is-open')) {
                            setActiveCourseItem(visible[0].target.getAttribute('data-course-section'));
                        }
                    }
                }, {
                    rootMargin: '-22% 0px -58% 0px',
                    threshold: [0.08, 0.18, 0.32]
                });

                for (var l = 0; l < courseSections.length; l += 1) {
                    observer.observe(courseSections[l]);
                }
            }

            function scrollToInitialCourseHash() {
                if (!page.classList.contains('is-case-selected')) {
                    return;
                }

                var targetId = window.location.hash.slice(1);

                if (targetId === 'perlas-clinicas-caso') {
                    if (courseSections[0]) {
                        window.setTimeout(function () {
                            openCourseSection(courseSections[0], 'auto', false);
                            openResourceModal('perlas');
                        }, 80);
                    } else {
                        openResourceModal('perlas');
                    }
                    return;
                }

                var target = document.getElementById(targetId);

                if (!target || !target.hasAttribute('data-course-section')) {
                    if (courseSections[0]) {
                        window.setTimeout(function () {
                            openCourseSection(courseSections[0], 'auto', false);
                        }, 80);
                    }
                    return;
                }

                setActiveCourseItem(targetId);
                window.setTimeout(function () {
                    openCourseSection(target, 'auto', false);
                }, 80);
            }

            scrollToInitialCourseHash();

            if (!page.classList.contains('is-case-selected') && window.location.hash.indexOf('#tema-') === 0) {
                showTopic(window.location.hash.replace('#tema-', ''), false);
            }
        }());
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
