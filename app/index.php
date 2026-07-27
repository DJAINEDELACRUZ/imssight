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

$temas = [];
$casos = [];
$escenas = [];
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
            font-size: clamp(30px, 4vw, 46px);
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
            font-size: clamp(24px, 3vw, 34px);
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
            font-size: 22px;
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
            font-size: 25px;
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
            font-size: clamp(28px, 4vw, 46px);
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

        .scene-head {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px 16px;
            border-bottom: 1px solid var(--imss-border);
            background: #FAF8F4;
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
            font-size: 18px;
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

        .scene-content img,
        .scene-content video {
            display: block;
            max-width: 100%;
            max-height: 520px;
            margin: 16px auto 0;
            border-radius: 4px;
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
                                            <div class="case-meta"><?= h($caso['tema']) ?> · <?= h($caso['especialidad']) ?></div>
                                            <h3><?= h($caso['titulo']) ?></h3>
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
                            <?php if (!$escenas): ?>
                                <div class="empty-state">
                                    Este caso todavia no tiene escenas publicadas.
                                </div>
                            <?php else: ?>
                                <div class="course-layout">
                                    <aside class="course-nav" aria-label="Contenido del curso">
                                        <div class="course-nav-title">Contenido</div>
                                        <ol class="course-nav-list">
                                            <?php foreach ($escenas as $index => $escena): ?>
                                                <li>
                                                    <a class="course-nav-link<?= $index === 0 ? ' is-active' : '' ?>" href="#escena-<?= h($escena['id']) ?>" data-scene-link="escena-<?= h($escena['id']) ?>">
                                                        <span class="course-nav-number"><?= $index + 1 ?></span>
                                                        <span class="course-nav-text"><?= h($escena['titulo'] ?: 'Escena clinica') ?></span>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </aside>

                                    <div class="course-content">
                                        <?php foreach ($escenas as $index => $escena): ?>
                                            <section class="scene" id="escena-<?= h($escena['id']) ?>" data-scene-section="escena-<?= h($escena['id']) ?>">
                                                <div class="scene-head">
                                                    <span class="scene-number"><?= $index + 1 ?></span>
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
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
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
            var sceneLinks = document.querySelectorAll('[data-scene-link]');
            var sceneSections = document.querySelectorAll('[data-scene-section]');

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

            function setActiveScene(sceneId) {
                for (var i = 0; i < sceneLinks.length; i += 1) {
                    sceneLinks[i].classList.toggle('is-active', sceneLinks[i].getAttribute('data-scene-link') === sceneId);
                }
            }

            for (var k = 0; k < sceneLinks.length; k += 1) {
                sceneLinks[k].addEventListener('click', function (event) {
                    var targetId = this.getAttribute('data-scene-link');
                    var target = document.getElementById(targetId);

                    if (!target) {
                        return;
                    }

                    event.preventDefault();
                    setActiveScene(targetId);
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                    if (history.replaceState) {
                        history.replaceState(null, '', '#' + targetId);
                    }
                });
            }

            if ('IntersectionObserver' in window && sceneSections.length) {
                var observer = new IntersectionObserver(function (entries) {
                    var visible = entries
                        .filter(function (entry) {
                            return entry.isIntersecting;
                        })
                        .sort(function (first, second) {
                            return second.intersectionRatio - first.intersectionRatio;
                        });

                    if (visible[0]) {
                        setActiveScene(visible[0].target.getAttribute('data-scene-section'));
                    }
                }, {
                    rootMargin: '-22% 0px -58% 0px',
                    threshold: [0.08, 0.18, 0.32]
                });

                for (var l = 0; l < sceneSections.length; l += 1) {
                    observer.observe(sceneSections[l]);
                }
            }

            if (!page.classList.contains('is-case-selected') && window.location.hash.indexOf('#tema-') === 0) {
                showTopic(window.location.hash.replace('#tema-', ''), false);
            }
        }());
    </script>
</body>
</html>
