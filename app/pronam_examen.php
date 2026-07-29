<?php

function h($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$idExamen = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$idCaso = isset($_GET['caso']) ? (int) $_GET['caso'] : 0;
$programa = trim((string) ($_GET['programa'] ?? 'pronam')) ?: 'pronam';
$initialView = ($_GET['view'] ?? '') === 'constancia' ? 'certificate' : 'exam';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Evaluación PRONAM</title>
    <link rel="apple-touch-icon" sizes="76x76" href="assets/img/favicon-pronam-ssa.svg">
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon-pronam-ssa.svg">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0">
    <style>
        :root {
            --imss-green: #235B4E;
            --imss-green-dark: #103D33;
            --imss-gold: #BC955C;
            --imss-gray: #6F7271;
            --imss-border: #D6D1C8;
            --imss-soft: #F6F3EE;
            --surface: #FFFFFF;
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
            background: var(--imss-soft);
            color: #2E3130;
            font-family: "Noto Sans", Arial, sans-serif;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: #12372F;
            box-shadow: 0 12px 28px rgba(13, 42, 36, .16);
        }

        .topbar-inner,
        .exam-shell,
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
            width: auto;
        }

        .brand-government-logo {
            height: 42px;
        }

        .brand-divider {
            width: 1px;
            height: 42px;
            background: rgba(255, 255, 255, .24);
        }

        .imss-logo-chip {
            height: 46px;
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
            margin-top: 3px;
        }

        .back-link,
        .primary-btn,
        .secondary-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 8px;
            font: inherit;
            font-size: 14px;
            font-weight: 800;
            padding: 11px 16px;
            text-decoration: none;
            cursor: pointer;
        }

        .back-link {
            border: 1px solid rgba(255, 255, 255, .38);
            background: rgba(255, 255, 255, .08);
            color: #FFFFFF;
        }

        .primary-btn {
            border: 1px solid var(--imss-green);
            background: var(--imss-green);
            color: #FFFFFF;
        }

        .secondary-btn {
            border: 1px solid var(--imss-border);
            background: #FFFFFF;
            color: var(--imss-green);
        }

        .primary-btn:disabled,
        .secondary-btn:disabled {
            cursor: not-allowed;
            opacity: .55;
        }

        .exam-shell {
            padding: 34px 0 46px;
        }

        .exam-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 24px;
            align-items: start;
            border: 1px solid var(--imss-border);
            border-top: 5px solid var(--imss-gold);
            border-radius: 8px;
            background: #FFFFFF;
            padding: 26px;
            box-shadow: 0 14px 32px rgba(45, 46, 45, .08);
        }

        .exam-kicker {
            color: var(--imss-green);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .exam-hero h1 {
            margin: 8px 0 10px;
            color: var(--imss-green-dark);
            font-size: clamp(28px, 4vw, 44px);
            font-weight: 900;
            line-height: 1.08;
        }

        .exam-hero p {
            max-width: 760px;
            margin: 0;
            color: var(--imss-gray);
            line-height: 1.7;
        }

        .exam-status {
            min-width: 240px;
            border: 1px solid rgba(35, 91, 78, .18);
            border-radius: 8px;
            background: #F7FAF8;
            padding: 14px;
        }

        .status-line {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            color: var(--imss-green-dark);
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .progress-track {
            height: 8px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(35, 91, 78, .12);
        }

        .progress-track span {
            display: block;
            width: 0;
            height: 100%;
            border-radius: inherit;
            background: var(--imss-green);
            transition: width .18s ease;
        }

        .exam-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 18px 0;
        }

        .tab-btn {
            border: 1px solid rgba(35, 91, 78, .2);
            border-radius: 8px;
            background: #FFFFFF;
            color: var(--imss-green-dark);
            cursor: pointer;
            font: inherit;
            font-weight: 900;
            min-height: 44px;
            padding: 10px 14px;
        }

        .tab-btn.is-active {
            background: var(--imss-green);
            color: #FFFFFF;
        }

        .tab-btn.is-locked {
            color: var(--imss-gray);
        }

        .exam-panel {
            border: 1px solid var(--imss-border);
            border-radius: 8px;
            background: #FFFFFF;
            overflow: hidden;
            box-shadow: 0 14px 32px rgba(45, 46, 45, .08);
        }

        .panel-head {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            border-bottom: 1px solid var(--imss-border);
            background: #FAF8F4;
            padding: 16px 18px;
        }

        .panel-head h2 {
            margin: 0;
            color: var(--imss-green-dark);
            font-size: 20px;
            font-weight: 900;
        }

        .question-list {
            display: grid;
            gap: 14px;
            padding: 18px;
        }

        .question-card {
            border: 1px solid rgba(35, 91, 78, .16);
            border-radius: 8px;
            background: #F7FAF9;
            padding: 16px;
        }

        .question-card h3 {
            margin: 0 0 14px;
            color: #263B36;
            font-size: 17px;
            font-weight: 800;
            line-height: 1.45;
        }

        .answer-list {
            display: grid;
            gap: 8px;
        }

        .answer-option {
            display: grid;
            grid-template-columns: 20px 34px minmax(0, 1fr);
            gap: 12px;
            align-items: center;
            border: 1px solid transparent;
            border-radius: 8px;
            background: #FFFFFF;
            cursor: pointer;
            min-height: 52px;
            padding: 12px 14px;
        }

        .answer-option:hover,
        .answer-option.is-selected {
            border-color: rgba(35, 91, 78, .32);
            background: rgba(35, 91, 78, .06);
        }

        .answer-option input {
            width: 18px;
            height: 18px;
            margin: 0;
            justify-self: center;
        }

        .answer-letter {
            color: var(--imss-green);
            font-weight: 900;
            line-height: 1.25;
            text-align: left;
        }

        .answer-text {
            min-width: 0;
            color: #2E3130;
            font-weight: 600;
            line-height: 1.42;
        }

        .panel-actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            border-top: 1px solid var(--imss-border);
            padding: 16px 18px;
        }

        .result-box,
        .certificate-box {
            display: grid;
            gap: 14px;
            padding: 18px;
        }

        .result-card,
        .certificate-form,
        .certificate-card,
        .locked-card {
            border: 1px solid rgba(35, 91, 78, .16);
            border-radius: 8px;
            background: #FFFFFF;
            padding: 18px;
        }

        .score {
            color: var(--imss-green-dark);
            font-size: clamp(42px, 8vw, 72px);
            font-weight: 900;
            line-height: 1;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .field {
            display: grid;
            gap: 6px;
        }

        .field label {
            color: var(--imss-green-dark);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .field input {
            min-height: 44px;
            border: 1px solid var(--imss-border);
            border-radius: 8px;
            font: inherit;
            padding: 10px 12px;
        }

        .certificate-card {
            position: relative;
            width: min(100%, 920px);
            aspect-ratio: 1531 / 1978;
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, .12);
            border-radius: 0;
            background: #FFFFFF;
            box-shadow: 0 18px 42px rgba(0, 0, 0, .12);
            color: #787878;
            font-family: Arial, Helvetica, sans-serif;
            text-align: center;
        }

        .certificate-template-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            user-select: none;
        }

        .certificate-person,
        .certificate-line,
        .certificate-course,
        .certificate-duration,
        .certificate-issued-copy,
        .certificate-issued-date {
            position: absolute;
            left: 7%;
            width: 82%;
            z-index: 1;
        }

        .certificate-person {
            top: 42.8%;
            color: #777;
            font-size: clamp(30px, 5vw, 56px);
            font-weight: 400;
            line-height: 1.12;
        }

        .certificate-line {
            top: 53.7%;
            color: #6f6f6f;
            font-size: clamp(13px, 2vw, 24px);
            font-weight: 800;
            white-space: nowrap;
        }

        .certificate-course {
            top: 58.8%;
            color: #777;
            font-size: clamp(24px, 4vw, 47px);
            font-weight: 900;
            line-height: 1.13;
        }

        .certificate-duration {
            top: 68.4%;
            color: #777;
            font-size: clamp(16px, 2.7vw, 31px);
            font-weight: 900;
        }

        .certificate-issued-copy {
            top: 72.4%;
            color: #000;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: clamp(17px, 3vw, 35px);
            font-weight: 400;
        }

        .certificate-issued-date {
            top: 76.2%;
            color: #777;
            font-size: clamp(17px, 2.65vw, 32px);
            font-weight: 600;
        }

        .certificate-meta {
            width: min(100%, 920px);
            margin: 12px auto 0;
            color: var(--imss-gray);
            font-size: 13px;
            font-weight: 800;
            text-align: center;
        }

        .message {
            color: var(--imss-gray);
            font-weight: 800;
        }

        .site-footer {
            background: #12372F;
            color: rgba(255, 255, 255, .82);
        }

        .footer-inner {
            display: grid;
            grid-template-columns: 1.15fr 1fr;
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
            height: 42px;
            width: auto;
        }

        .footer-brand strong {
            display: block;
            color: #FFFFFF;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, .12);
            padding: 16px 0;
            color: rgba(255, 255, 255, .6);
            font-size: 13px;
        }

        @media print {
            @page {
                size: 215.9mm 279.4mm;
                margin: 0;
            }

            body * {
                visibility: hidden;
            }

            .certificate-card,
            .certificate-card * {
                visibility: visible;
            }

            .certificate-card {
                position: fixed;
                inset: 0;
                width: 100vw;
                height: 100vh;
                max-width: none;
                aspect-ratio: auto;
                border: 0;
                box-shadow: none;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .certificate-meta,
            .panel-actions {
                display: none !important;
            }
        }

        @media (max-width: 760px) {
            .topbar-inner,
            .exam-hero,
            .panel-head,
            .panel-actions {
                grid-template-columns: 1fr;
            }

            .topbar-inner,
            .panel-actions {
                align-items: stretch;
            }

            .exam-hero,
            .footer-inner,
            .field-grid {
                grid-template-columns: 1fr;
            }

            .brand {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="index.php?programa=<?= h($programa) ?>&caso=<?= h($idCaso) ?>#examen-caso" aria-label="Volver a PRONAM">
                <img class="brand-government-logo" src="assets/img/logo_gob_mx.png" alt="Gobierno de México">
                <span class="brand-divider" aria-hidden="true"></span>
                <img class="imss-logo-chip" src="assets/img/logo_imss_blanco.png" alt="IMSS">
                <span>
                    <span class="brand-title">PRONAM</span>
                    <span class="brand-copy">Evaluación clínica pública</span>
                </span>
            </a>
            <a class="back-link" href="index.php?programa=<?= h($programa) ?>&caso=<?= h($idCaso) ?>#examen-caso">
                <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                Volver al curso
            </a>
        </div>
    </header>

    <main class="exam-shell">
        <section class="exam-hero">
            <div>
                <div class="exam-kicker">PRONAM · Evaluación</div>
                <h1 id="examTitle">Cuestionario final</h1>
                <p id="examDescription">Las preguntas se presentan en bloques de 10. Al aprobar, se desbloquea la constancia para registro de datos.</p>
            </div>
            <aside class="exam-status">
                <div class="status-line">
                    <span id="progressLabel">0 de 0 respondidas</span>
                    <span id="batchLabel">Bloque 1</span>
                </div>
                <div class="progress-track">
                    <span id="progressBar"></span>
                </div>
            </aside>
        </section>

        <nav class="exam-tabs" aria-label="Secciones de evaluación">
            <button class="tab-btn is-active" type="button" data-view="exam">
                Examen
            </button>
            <button class="tab-btn is-locked" type="button" data-view="certificate" id="certificateTab">
                <span class="material-symbols-rounded" aria-hidden="true">lock</span>
                Constancia
            </button>
        </nav>

        <section class="exam-panel" id="examView">
            <div class="panel-head">
                <h2 id="panelTitle">Preparando preguntas...</h2>
                <span class="message" id="panelMessage"></span>
            </div>
            <div class="question-list" id="questionList"></div>
            <div class="panel-actions">
                <button class="secondary-btn" type="button" id="prevBtn">
                    <span class="material-symbols-rounded" aria-hidden="true">chevron_left</span>
                    Anterior
                </button>
                <button class="primary-btn" type="button" id="nextBtn">
                    Siguiente
                    <span class="material-symbols-rounded" aria-hidden="true">chevron_right</span>
                </button>
            </div>
        </section>

        <section class="exam-panel" id="resultView" hidden>
            <div class="panel-head">
                <h2>Resultado</h2>
            </div>
            <div class="result-box" id="resultBox"></div>
        </section>

        <section class="exam-panel" id="certificateView" hidden>
            <div class="panel-head">
                <h2>Constancia</h2>
            </div>
            <div class="certificate-box" id="certificateBox"></div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <section>
                <div class="footer-brand">
                    <img src="assets/img/logo_gob_mx.png" alt="Gobierno de México">
                    <span>
                        <strong>PRONAM</strong>
                        <span>Unidad de Educación e Investigación</span>
                    </span>
                </div>
                <p>Evaluación pública para reforzar contenidos de formación clínica.</p>
            </section>
            <section>
                <h2>Contacto institucional</h2>
                <p>Dirección de Prestaciones Médicas, IMSS.</p>
            </section>
        </div>
        <div class="footer-bottom">
            <div class="footer-inner">
                <span>Gobierno de México · Instituto Mexicano del Seguro Social</span>
                <span>2026 · PRONAM</span>
            </div>
        </div>
    </footer>

    <script>
        const idExamen = <?= json_encode($idExamen) ?>;
        const idCaso = <?= json_encode($idCaso) ?>;
        const initialView = <?= json_encode($initialView) ?>;
        const initialToken = new URLSearchParams(window.location.search).get('token');
        const batchSize = 10;
        const storageKey = `pronam-exam-${idExamen}-result`;
        let exam = null;
        let questions = [];
        let answers = {};
        let batchIndex = 0;
        let latestResult = null;
        let latestCertificate = null;

        const $ = (selector) => document.querySelector(selector);

        function escapeHtml(value = '') {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function setView(view) {
            $('#examView').hidden = view !== 'exam';
            $('#resultView').hidden = view !== 'result';
            $('#certificateView').hidden = view !== 'certificate';

            document.querySelectorAll('[data-view]').forEach((button) => {
                button.classList.toggle('is-active', button.dataset.view === view);
            });

            if (view === 'certificate') {
                renderCertificate();
            }
        }

        function updateProgress() {
            const answered = Object.keys(answers).length;
            const total = questions.length;
            const percent = total ? Math.round((answered / total) * 100) : 0;
            const totalBatches = Math.max(1, Math.ceil(total / batchSize));

            $('#progressLabel').textContent = `${answered} de ${total} respondidas`;
            $('#batchLabel').textContent = `Bloque ${Math.min(batchIndex + 1, totalBatches)} de ${totalBatches}`;
            $('#progressBar').style.width = `${percent}%`;
        }

        function renderBatch() {
            const start = batchIndex * batchSize;
            const currentQuestions = questions.slice(start, start + batchSize);
            const totalBatches = Math.max(1, Math.ceil(questions.length / batchSize));

            $('#panelTitle').textContent = `Preguntas ${start + 1}-${Math.min(start + batchSize, questions.length)}`;
            $('#panelMessage').textContent = 'Selecciona una respuesta por reactivo.';
            $('#questionList').innerHTML = currentQuestions.map((question, index) => {
                const number = start + index + 1;
                const options = [
                    ['A', question.opcion_a],
                    ['B', question.opcion_b],
                    ['C', question.opcion_c],
                    ['D', question.opcion_d]
                ];

                return `
                    <article class="question-card">
                        <h3>${number}. ${escapeHtml(question.pregunta)}</h3>
                        <div class="answer-list">
                            ${options.map(([letter, text]) => `
                                <label class="answer-option ${answers[question.id] === letter ? 'is-selected' : ''}">
                                    <input
                                        type="radio"
                                        name="question-${question.id}"
                                        value="${letter}"
                                        ${answers[question.id] === letter ? 'checked' : ''}
                                        data-question="${question.id}">
                                    <span class="answer-letter">${letter}.</span>
                                    <span class="answer-text">${escapeHtml(text)}</span>
                                </label>
                            `).join('')}
                        </div>
                    </article>
                `;
            }).join('');

            $('#prevBtn').disabled = batchIndex === 0;
            $('#nextBtn').innerHTML = batchIndex >= totalBatches - 1
                ? 'Enviar examen <span class="material-symbols-rounded" aria-hidden="true">send</span>'
                : 'Siguiente <span class="material-symbols-rounded" aria-hidden="true">chevron_right</span>';

            updateProgress();
        }

        async function loadExam() {
            const response = await fetch(`php/pronam_examen_publico.php?action=exam&id=${encodeURIComponent(idExamen)}`);
            const payload = await response.json();

            if (!payload.success) {
                $('#questionList').innerHTML = `<div class="locked-card">${escapeHtml(payload.mensaje || 'No fue posible cargar el examen.')}</div>`;
                return;
            }

            exam = payload.examen;
            questions = payload.preguntas || [];
            $('#examTitle').textContent = exam.titulo || 'Cuestionario final';
            $('#examDescription').textContent = exam.descripcion || 'Evaluación pública PRONAM.';

            renderBatch();
            await loadStoredResult();
            setView(initialView);
        }

        async function loadStoredResult() {
            if (initialToken) {
                localStorage.setItem(storageKey, initialToken);
            }

            const token = localStorage.getItem(storageKey);

            if (!token) {
                renderCertificate();
                return;
            }

            try {
                const response = await fetch(`php/pronam_examen_publico.php?action=status&token=${encodeURIComponent(token)}`);
                const payload = await response.json();

                if (payload.success && payload.resultado) {
                    latestResult = payload.resultado;
                    latestCertificate = payload.constancia;
                    updateCertificateTab();
                }
            } catch (error) {
                console.error(error);
            }
        }

        async function submitExam() {
            if (Object.keys(answers).length < questions.length) {
                $('#panelMessage').textContent = 'Faltan preguntas por responder en el examen.';
                return;
            }

            $('#nextBtn').disabled = true;
            $('#panelMessage').textContent = 'Enviando respuestas...';

            try {
                const response = await fetch('php/pronam_examen_publico.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'submit',
                        id_examen: idExamen,
                        respuestas: answers
                    })
                });
                const payload = await response.json();

                if (!payload.success) {
                    $('#panelMessage').textContent = payload.mensaje || 'No fue posible enviar el examen.';
                    $('#nextBtn').disabled = false;
                    return;
                }

                localStorage.setItem(storageKey, payload.token);
                latestResult = {
                    intento_token: payload.token,
                    calificacion: payload.calificacion,
                    respuestas_correctas: payload.correctas,
                    total_preguntas: payload.total
                };
                latestCertificate = null;
                renderResult(payload);
                updateCertificateTab();
                setView('result');
            } catch (error) {
                console.error(error);
                $('#panelMessage').textContent = 'No fue posible enviar el examen.';
                $('#nextBtn').disabled = false;
            }
        }

        function renderResult(result) {
            const approved = Number(result.calificacion) >= 8;
            $('#resultBox').innerHTML = `
                <section class="result-card">
                    <div class="score">${escapeHtml(result.calificacion)}</div>
                    <p class="message">${escapeHtml(result.correctas)} de ${escapeHtml(result.total)} respuestas correctas.</p>
                    <p>${approved ? 'Calificación aprobatoria. La constancia ya está desbloqueada para registrar datos.' : 'La constancia se desbloquea con calificación mínima de 8.0.'}</p>
                    <div class="panel-actions">
                        <button class="secondary-btn" type="button" data-view="exam">Volver al examen</button>
                        <button class="primary-btn" type="button" data-view="certificate" ${approved ? '' : 'disabled'}>Ir a constancia</button>
                    </div>
                </section>
            `;
        }

        function updateCertificateTab() {
            const tab = $('#certificateTab');
            const approved = latestResult && Number(latestResult.calificacion) >= 8;
            tab.classList.toggle('is-locked', !approved);
            tab.innerHTML = approved
                ? '<span class="material-symbols-rounded" aria-hidden="true">workspace_premium</span> Constancia'
                : '<span class="material-symbols-rounded" aria-hidden="true">lock</span> Constancia';
        }

        function renderCertificate() {
            updateCertificateTab();

            if (!latestResult) {
                $('#certificateBox').innerHTML = `
                    <section class="locked-card">
                        <h3>Constancia bloqueada</h3>
                        <p>Primero completa el examen PRONAM.</p>
                    </section>
                `;
                return;
            }

            if (Number(latestResult.calificacion) < 8) {
                $('#certificateBox').innerHTML = `
                    <section class="locked-card">
                        <h3>Constancia bloqueada</h3>
                        <p>La constancia se desbloquea con calificación mínima de 8.0. Tu calificación fue ${escapeHtml(latestResult.calificacion)}.</p>
                    </section>
                `;
                return;
            }

            if (latestCertificate) {
                $('#certificateBox').innerHTML = certificateMarkup(latestCertificate);
                return;
            }

            $('#certificateBox').innerHTML = `
                <form class="certificate-form" id="certificateForm">
                    <h3>Datos para constancia</h3>
                    <p class="message">Completa la información exactamente como debe aparecer en la constancia.</p>
                    <div class="field-grid">
                        <div class="field">
                            <label for="certName">Nombre</label>
                            <input id="certName" name="nombre" type="text" required>
                        </div>
                        <div class="field">
                            <label for="certMatricula">Matrícula</label>
                            <input id="certMatricula" name="matricula" type="text" required>
                        </div>
                        <div class="field">
                            <label for="certCategoria">Categoría</label>
                            <input id="certCategoria" name="categoria" type="text" required>
                        </div>
                    </div>
                    <div class="panel-actions">
                        <span class="message" id="certificateMessage"></span>
                        <button class="primary-btn" type="submit">Generar constancia</button>
                    </div>
                </form>
            `;
        }

        function certificateMarkup(certificate) {
            const course = certificateCourseTitle();
            const issuedDate = formatCertificateDate(certificate.fecha);
            return `
                <section class="certificate-card">
                    <img class="certificate-template-img" src="assets/img/pronam_constancia_template.png" alt="">
                    <div class="certificate-person">${escapeHtml(certificate.nombre)}</div>
                    <div class="certificate-line">Por haber concluido satisfactoriamente el Curso a Distancia</div>
                    <div class="certificate-course">${course}</div>
                    <div class="certificate-duration">con una duración de 2 horas 30 minutos</div>
                    <div class="certificate-issued-copy">Se extiende la presente constancia el:</div>
                    <div class="certificate-issued-date">${escapeHtml(issuedDate)}</div>
                </section>
                <div class="certificate-meta">
                    Folio ${escapeHtml(certificate.folio)} · Matrícula ${escapeHtml(certificate.matricula)} · Categoría ${escapeHtml(certificate.categoria)} · Calificación ${escapeHtml(certificate.calificacion)}
                </div>
                <div class="panel-actions">
                    <button class="secondary-btn" type="button" onclick="window.print()">Imprimir constancia</button>
                </div>
            `;
        }

        function certificateCourseTitle() {
            const title = exam?.caso || 'PRONAM de diabetes tipo 2 y el síndrome metabólico';
            return escapeHtml(title).replace(' y el ', '<br>y el ');
        }

        function formatCertificateDate(rawDate) {
            if (!rawDate) {
                return '';
            }

            const [datePart] = String(rawDate).split(' ');
            const [year, month, day] = datePart.split('-').map(Number);
            const months = [
                'enero',
                'febrero',
                'marzo',
                'abril',
                'mayo',
                'junio',
                'julio',
                'agosto',
                'septiembre',
                'octubre',
                'noviembre',
                'diciembre'
            ];

            if (!year || !month || !day || !months[month - 1]) {
                return rawDate;
            }

            return `${day} de ${months[month - 1]} de ${year}`;
        }

        async function submitCertificate(form) {
            const message = $('#certificateMessage');
            const token = localStorage.getItem(storageKey);
            const data = Object.fromEntries(new FormData(form).entries());
            data.action = 'certificate';
            data.token = token;

            message.textContent = 'Generando constancia...';

            try {
                const response = await fetch('php/pronam_examen_publico.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                const payload = await response.json();

                if (!payload.success) {
                    message.textContent = payload.mensaje || 'No fue posible generar la constancia.';
                    return;
                }

                latestCertificate = payload.constancia;
                renderCertificate();
            } catch (error) {
                console.error(error);
                message.textContent = 'No fue posible generar la constancia.';
            }
        }

        document.addEventListener('change', (event) => {
            const input = event.target.closest('[data-question]');
            if (!input) return;

            answers[input.dataset.question] = input.value;
            document.querySelectorAll(`input[name="${input.name}"]`).forEach((radio) => {
                const option = radio.closest('.answer-option');
                if (option) {
                    option.classList.toggle('is-selected', radio.checked);
                }
            });
            updateProgress();
        });

        document.addEventListener('click', (event) => {
            const viewButton = event.target.closest('[data-view]');
            if (viewButton && !viewButton.disabled) {
                setView(viewButton.dataset.view);
                return;
            }

            if (event.target.closest('#prevBtn')) {
                batchIndex = Math.max(0, batchIndex - 1);
                renderBatch();
                window.scrollTo({ top: 0, behavior: 'smooth' });
                return;
            }

            if (event.target.closest('#nextBtn')) {
                const totalBatches = Math.max(1, Math.ceil(questions.length / batchSize));

                if (batchIndex >= totalBatches - 1) {
                    submitExam();
                    return;
                }

                batchIndex += 1;
                renderBatch();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        document.addEventListener('submit', (event) => {
            if (event.target.id === 'certificateForm') {
                event.preventDefault();
                submitCertificate(event.target);
            }
        });

        loadExam();
    </script>
</body>
</html>
