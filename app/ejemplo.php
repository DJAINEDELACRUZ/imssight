<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  </head>
  <body>
    




  
<section class="pronam-case13-course pc13-updated pc13-bootstrap" data-pc13-root aria-label="Presentación interactiva del caso clínico"><style>
  .pronam-case13-course {
    --pc13-ink: #10312B;
    --pc13-green: #235B4E;
    --pc13-wine: #9F2241;
    --pc13-cream: #F7F2E8;
    color: var(--pc13-ink);
  }

  .pronam-case13-course * {
    box-sizing: border-box;
    letter-spacing: 0;
  }

  .pc13-stage {
    position: relative;
    overflow: hidden;
    scroll-margin-top: calc(var(--topbar-height, 86px) + 12px);
  }

  .pc13-slide {
    display: none;
    position: relative;
    min-height: 500px;
    padding-bottom: 5rem;
    isolation: isolate;
  }

  .pc13-slide.is-active {
    display: block;
  }

  .pc13-line {
    height: 4px;
    background: var(--pc13-wine);
  }

  .pc13-doctor {
    max-height: 430px;
    object-fit: contain;
    filter: drop-shadow(0 1.25rem 1.875rem rgba(16, 49, 43, .18));
    pointer-events: none;
  }

  .pc13-flip-toggle {
    position: absolute;
    inline-size: 1px;
    block-size: 1px;
    opacity: 0;
    pointer-events: none;
  }

  .pc13-flip-card {
    display: block;
    min-height: 20rem;
    cursor: pointer;
    perspective: 1200px;
  }

  .pc13-flip-inner {
    position: relative;
    min-height: 20rem;
    transform-style: preserve-3d;
    transition: transform .62s cubic-bezier(.2, .8, .2, 1);
  }

  .pc13-flip-toggle:checked + .pc13-flip-card .pc13-flip-inner {
    transform: rotateY(180deg);
  }

  .pc13-flip-face {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    overflow: auto;
    transform: translateZ(0);
  }

  .pc13-flip-back {
    transform: rotateY(180deg) translateZ(1px);
  }

  .pc13-actions {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 1rem;
    z-index: 6;
    pointer-events: none;
  }

  .pc13-actions .pc13-btn {
    pointer-events: auto;
  }

  .pc13-count {
    position: absolute;
    right: 1.5rem;
    bottom: 1.5rem;
    z-index: 6;
  }

  .pc13-modal {
    position: absolute;
    inset: 0;
    z-index: 10;
    display: none;
    place-items: center;
    padding: 1.5rem;
    background: rgba(16, 49, 43, .48);
  }

  .pc13-modal.is-open {
    display: grid;
  }

  .pc13-option-btn.is-correct {
    background: var(--pc13-green);
    border-color: var(--pc13-green);
    color: #fff;
  }

  .pc13-option-btn.is-incorrect,
  .pc13-modal-card.is-incorrect {
    background: var(--pc13-wine);
    border-color: var(--pc13-wine);
    color: #fff;
  }

  @media (max-width: 991px) {
    .pc13-slide {
      min-height: 760px;
    }

    .pc13-doctor {
      max-height: 220px;
      opacity: .18;
    }
  }
  /* pc13-polish: capa visual, no cambia contenido ni estructura */
  .pronam-case13-course .pc13-stage {
    border-color: rgba(214, 209, 200, .85);
    box-shadow: 0 .75rem 2rem rgba(16, 49, 43, .08) !important;
  }

  .pronam-case13-course .pc13-slide h1 {
    max-width: 58rem;
    margin-bottom: 1.35rem !important;
    color: var(--pc13-ink) !important;
  }

  .pronam-case13-course .pc13-slide p {
    line-height: 1.65;
    color: #2E3130;
  }

  .pronam-case13-course .pc13-slide ul {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .65rem 1.25rem;
    margin: 1rem 0 0;
    padding-left: 1.25rem;
  }

  .pronam-case13-course .pc13-slide li {
    line-height: 1.45;
    padding-right: .5rem;
  }

  .pronam-case13-course .pc13-flip-back ul,
  .pronam-case13-course .modal-body ul {
    grid-template-columns: 1fr;
  }

  .pronam-case13-course .pc13-flip-front {
    background: linear-gradient(135deg, #6F7271, #555857) !important;
  }

  .pronam-case13-course .pc13-card {
    border-radius: .75rem !important;
    line-height: 1.35;
  }

  .pronam-case13-course [data-pc13-slide="7"] .d-grid {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .85rem 1rem !important;
    max-width: 56rem;
    margin-inline: auto;
  }

  .pronam-case13-course [data-pc13-slide="7"] .d-grid .btn {
    min-height: 3.25rem;
    padding: .75rem 1rem;
    border-radius: .65rem;
    font-weight: 700;
    line-height: 1.2;
    white-space: normal;
  }

  .pronam-case13-course .pc13-options-grid {
    display: grid !important;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem !important;
    max-width: 56rem;
    margin-left: 0 !important;
    margin-right: 0 !important;
  }

  .pronam-case13-course .pc13-option-btn {
    width: 100%;
    min-height: 0;
    margin: 0 !important;
    padding: .9rem 1rem !important;
    border-radius: .75rem !important;
    font-weight: 700;
    line-height: 1.35;
    white-space: normal;
  }

  .pronam-case13-course .pc13-option-btn .badge {
    flex: 0 0 auto;
  }

  .pronam-case13-course .pc13-options-grid .pc13-option-btn:last-child:nth-child(odd) {
    grid-column: 1 / -1;
    max-width: calc(50% - .5rem);
  }

  .pronam-case13-course .pc13-option-btn.is-correct {
    background: var(--pc13-green);
    border-color: var(--pc13-green);
    color: #fff;
  }

  .pronam-case13-course .pc13-option-btn.is-incorrect,
  .pronam-case13-course .pc13-modal-card.is-incorrect {
    background: var(--pc13-wine);
    border-color: var(--pc13-wine);
    color: #fff;
  }

  .pronam-case13-course [data-pc13-modal-close] {
    border-radius: .65rem;
    font-weight: 700;
  }

  .pronam-case13-course .modal-content,
  .scene-content .modal-content,
  .pronam-case13-course .pc13-modal-card {
    border: 0;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 1.5rem 4rem rgba(16, 32, 28, .22);
  }

  .scene-content .modal-dialog {
    max-width: min(860px, calc(100vw - 2rem));
  }

  .pronam-case13-course .modal-header,
  .scene-content .modal-header {
    align-items: flex-start;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-bottom-color: rgba(214, 209, 200, .85);
  }

  .pronam-case13-course .modal-title,
  .scene-content .modal-title {
    color: var(--pc13-ink, #10312B);
    line-height: 1.1;
  }

  .scene-content .modal-body {
    padding: 1.35rem 1.5rem;
  }

  .scene-content .modal-body h3 {
    margin: 0 0 1rem;
    font-size: 1.15rem;
    font-weight: 600;
    line-height: 1.5;
  }

  .scene-content .modal-body br {
    display: none;
  }

  .scene-content .modal-footer {
    padding: 1rem 1.5rem;
  }

  .pronam-case13-course .btn-close,
  .scene-content .btn-close {
    width: 2.25rem;
    height: 2.25rem;
    flex: 0 0 2.25rem;
    margin: 0;
    border-radius: 50%;
    background: #9F2241 url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293A1 1 0 0 1 .293 14.293L6.586 8 .293 1.707A1 1 0 0 1 .293.293z'/%3e%3c/svg%3e") center / .75rem auto no-repeat !important;
    opacity: 1;
    box-shadow: 0 .75rem 1.5rem rgba(159, 34, 65, .24);
  }

  .pronam-case13-course .btn-close:hover,
  .scene-content .btn-close:hover,
  .pronam-case13-course .btn-close:focus,
  .scene-content .btn-close:focus {
    background-color: #691C32 !important;
    opacity: 1;
    box-shadow: 0 0 0 .25rem rgba(159, 34, 65, .18);
  }

  @media (max-width: 991px) {
    .pronam-case13-course .pc13-slide ul,
    .pronam-case13-course [data-pc13-slide="7"] .d-grid,
    .pronam-case13-course .pc13-options-grid {
      grid-template-columns: 1fr;
    }

    .pronam-case13-course .pc13-options-grid .pc13-option-btn:last-child:nth-child(odd) {
      grid-column: auto;
      max-width: none;
    }
  }
  /* pc13-inline-modal: modales internos de la escena */
  .pronam-case13-course .pc13-modal {
    align-items: center;
    justify-items: center;
    overflow: hidden;
  }

  .pronam-case13-course .pc13-modal-card {
    position: relative;
    width: min(820px, calc(100% - 2rem));
    max-height: calc(100% - 2rem);
    overflow: visible;
    background: #fff;
    color: #212529;
    border-radius: 1rem;
    padding: 2rem !important;
  }

  .pronam-case13-course .pc13-modal-card.is-info {
    border-top: .45rem solid var(--pc13-green);
  }

  .pronam-case13-course .pc13-modal-card.is-incorrect {
    background: var(--pc13-wine);
    color: #fff;
  }

  .pronam-case13-course .pc13-modal-card.is-incorrect [data-pc13-modal-title],
  .pronam-case13-course .pc13-modal-card.is-incorrect .pc13-modal-body {
    color: #fff !important;
  }

  .pronam-case13-course .pc13-modal-body {
    max-height: min(52vh, 520px);
    overflow: auto;
    padding-right: .25rem;
  }

  .pronam-case13-course .pc13-modal-body h3 {
    margin: 0 0 1rem;
    font-size: 1.15rem;
    font-weight: 600;
    line-height: 1.5;
    color: #2E3130;
  }

  .pronam-case13-course .pc13-modal-body ul {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .55rem 1rem;
    margin: .75rem 0 1rem;
  }

  .pronam-case13-course .pc13-modal-x {
    position: absolute;
    top: -1.15rem;
    right: -1.15rem;
    z-index: 2;
    display: grid;
    place-items: center;
    width: 3rem;
    height: 3rem;
    border: 0;
    border-radius: 50%;
    background: var(--pc13-wine);
    color: #fff;
    font-size: 1.75rem;
    line-height: 1;
    box-shadow: 0 .85rem 1.75rem rgba(159, 34, 65, .28);
  }

  .pronam-case13-course .pc13-modal-x:hover,
  .pronam-case13-course .pc13-modal-x:focus {
    background: #691C32;
    outline: 0;
    box-shadow: 0 0 0 .25rem rgba(159, 34, 65, .2);
  }

  .pronam-case13-course .pc13-modal-card.is-info [data-pc13-modal-action] {
    display: none;
  }

  @media (max-width: 991px) {
    .pronam-case13-course .pc13-modal-card {
      width: min(100%, calc(100% - 1rem));
      padding: 1.5rem !important;
    }

    .pronam-case13-course .pc13-modal-body ul {
      grid-template-columns: 1fr;
    }
  }

  .pc13-interrogatorio-card {
    overflow: hidden;
    cursor: pointer;
    border: 1px solid rgba(35, 91, 78, 0.18);
    border-radius: 1rem;
    background-color: #ffffff;
    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        border-color 0.2s ease;
  }

  .pc13-interrogatorio-card:hover,
  .pc13-interrogatorio-card:focus-visible {
      transform: translateY(-4px);
      border-color: var(--pc13-green, #235B4E);
      box-shadow: 0 1rem 2rem rgba(16, 49, 43, 0.16) !important;
  }

  .pc13-interrogatorio-card:focus-visible {
      outline: 3px solid rgba(35, 91, 78, 0.25);
      outline-offset: 3px;
  }

  .pc13-interrogatorio-card .card-img-top {
      width: 100%;
      height: 190px;
      padding: 0.75rem;
      object-fit: contain;
      background-color: #f5f8f7;
  }

  .pc13-interrogatorio-card .card-body {
      min-height: 82px;
      padding: 1rem;
      color: #ffffff;
      background-color: var(--pc13-green, #235B4E);
  }

  .pc13-interrogatorio-card .card-title {
      font-weight: 700;
      line-height: 1.2;
  }

  @media (max-width: 767.98px) {
      .pc13-interrogatorio-card .card-img-top {
          height: 220px;
      }
  }
</style>
  
  <div class="pc13-stage card rounded-3 bg-white shadow-sm" aria-label="Capítulo 1 Presentación del caso">
    <article class="pc13-slide is-active p-4 p-lg-5" data-pc13-slide="0">
      <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
      <div class="pc13-body container-fluid row align-items-center g-4">
        <div class="pc13-copy col-12 col-lg-8">
          <h1 class="pc13-case-title display-6 fw-bold text-dark lh-sm mb-3">PRESENTACIÓN DEL CASO</h1>
          <div class="pc13-flip-zone row row-cols-1 row-cols-md-2 g-3 mt-3" aria-label="Tarjetas de inicio del caso">
            <div class="pc13-flip-unit col">
              <input class="pc13-flip-toggle" type="checkbox" id="pc13-flip-competencias">
              <label class="pc13-flip-card card h-100 text-decoration-none text-reset border-0" for="pc13-flip-competencias">
                <div class="pc13-flip-inner h-100">
                  <div class="pc13-flip-face pc13-flip-front text-center rounded-3 shadow-sm border p-4 bg-secondary text-white align-items-center">
                    <h3 class="fw-semibold">Competencias a desarrollar</h3>
                    <small>Presione para girar la tarjeta</small>
                  </div>
                  <div class="pc13-flip-face pc13-flip-back rounded-3 shadow-sm border p-4 bg-white text-dark border-top border-4 border-dark justify-content-start">
                    <h3 class="h3 fw-semibold text-success-emphasis lh-sm mb-3">Al finalizar este caso clínico usted será capaz de:</h3>
                    <ul>
                        <li>Identificar oportunamente a un paciente con síndrome metabólico</li>
                        <li>Reconocer factores de riesgo para diabetes mellitus tipo 2</li>
                        <li>Aplicar los criterios diagnósticos actuales</li>
                        <li>Solicitar e interpretar estudios de laboratorio</li>
                        <li>Estratificar el riesgo cardiovascular y renal</li>
                        <li>Seleccionar el tratamiento farmacológico de primera línea</li>
                        <li>Individualizar las metas glucémicas</li>
                        <li>Prevenir complicaciones micro y macrovasculares</li>
                        <li>Realizar seguimiento longitudinal del paciente</li>
                    </ul>
                  </div>
                </div>
              </label>
            </div>
            <div class="pc13-flip-unit col">
              <input class="pc13-flip-toggle" type="checkbox" id="pc13-flip-ruta">
              <label class="pc13-flip-card card h-100 text-decoration-none text-reset border-0" for="pc13-flip-ruta">
                <div class="pc13-flip-inner h-100">
                  <div class="pc13-flip-face pc13-flip-front text-center rounded-3 shadow-sm border p-4 bg-secondary text-white align-items-center">
                    <h3 class="fw-semibold">Objetivos específicos</h3>
                    <small>Presione para girar la tarjeta</small>
                  </div>
                  <div class="pc13-flip-face pc13-flip-back rounded-3 shadow-sm border p-4 bg-white text-dark border-top border-4 border-dark justify-content-start">
                    <h3 class="h3 fw-semibold text-success-emphasis lh-sm mb-3">Al concluir este módulo usted podrá:</h3>
                    <ul>
                      <li>Diagnosticar síndrome metabólico mediante ATP III, IDF y ALAD</li>
                      <li>Diagnosticar diabetes utilizando criterios ADA y CENETEC</li>
                      <li>Calcular riesgo cardiovascular</li>
                      <li>Identificar enfermedad renal diabética temprana</li>
                      <li>Seleccionar tratamiento basado en comorbilidades</li>
                      <li>Ajustar tratamiento conforme evoluciona el paciente</li>
                      <li>Prevenir complicaciones</li>
                    </ul>
                  </div>
                </div>
              </label>
            </div>
          </div>
        </div>
        <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso.png" alt="Doctora guia PRONAM">
      </div>
      <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
    </article>


    <article class="pc13-slide p-4 p-lg-5" data-pc13-slide="2">

        <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>

        <div class="pc13-body container-fluid">

            <h1 class="display-6 fw-bold text-dark lh-sm mb-4">
                Escenario clínico
            </h1>

            <div class="row justify-content-center">

                <div class="col-12">

                    <img
                        src="img/comic.png"
                        class="img-fluid rounded shadow-lg mx-auto d-block"
                        alt="Escenario clínico">

                </div>

            </div>

        </div>

        <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>

    </article>


    <article class="pc13-slide p-4 p-lg-5" data-pc13-slide="5">
      <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
      <div class="pc13-body container-fluid row align-items-center g-4">
        <div class="pc13-copy col-12 col-lg-8">
          <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Con la información disponible, ¿cuál es el siguiente paso más apropiado?</h1>
          <div class="pc13-question">
            <div class="pc13-options-grid row row-cols-1 row-cols-md-2 g-2 mt-3">
              <button class="pc13-option-btn btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="Aunque la sintomatología es altamente sugestiva de hiperglucemia, el diagnóstico de diabetes mellitus debe confirmarse mediante los criterios diagnósticos establecidos (glucosa plasmática en ayuno, HbA1c, PTOG o glucosa plasmática al azar en presencia de síntomas clásicos y valores ≥200 mg/dL). Antes de iniciar tratamiento es indispensable caracterizar el estado metabólico, identificar comorbilidades y establecer una línea basal para el seguimiento." data-pc13-pearl-title="PERLA CLÍNICA No. 1" data-pc13-pearl="Hasta el 50% de los pacientes con diabetes mellitus tipo 2 presentan complicaciones microvasculares o macrovasculares al momento del diagnóstico debido al largo periodo de hiperglucemia asintomática."><span class="badge text-bg-light text-success me-2">A</span>Iniciar metformina de manera inmediata.</button>
              <button class="pc13-option-btn btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="1" data-pc13-feedback="Aunque la sintomatología es altamente sugestiva de hiperglucemia, el diagnóstico de diabetes mellitus debe confirmarse mediante los criterios diagnósticos establecidos (glucosa plasmática en ayuno, HbA1c, PTOG o glucosa plasmática al azar en presencia de síntomas clásicos y valores ≥200 mg/dL). Antes de iniciar tratamiento es indispensable caracterizar el estado metabólico, identificar comorbilidades y establecer una línea basal para el seguimiento." data-pc13-pearl-title="PERLA CLÍNICA No. 1" data-pc13-pearl="Hasta el 50% de los pacientes con diabetes mellitus tipo 2 presentan complicaciones microvasculares o macrovasculares al momento del diagnóstico debido al largo periodo de hiperglucemia asintomática."><span class="badge text-bg-light text-success me-2">B</span>Solicitar laboratorio completo para confirmar el diagnóstico.</button>
              <button class="pc13-option-btn btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="Aunque la sintomatología es altamente sugestiva de hiperglucemia, el diagnóstico de diabetes mellitus debe confirmarse mediante los criterios diagnósticos establecidos (glucosa plasmática en ayuno, HbA1c, PTOG o glucosa plasmática al azar en presencia de síntomas clásicos y valores ≥200 mg/dL). Antes de iniciar tratamiento es indispensable caracterizar el estado metabólico, identificar comorbilidades y establecer una línea basal para el seguimiento." data-pc13-pearl-title="PERLA CLÍNICA No. 1" data-pc13-pearl="Hasta el 50% de los pacientes con diabetes mellitus tipo 2 presentan complicaciones microvasculares o macrovasculares al momento del diagnóstico debido al largo periodo de hiperglucemia asintomática."><span class="badge text-bg-light text-success me-2">C</span>Solicitar tomografía abdominal.</button>
              <button class="pc13-option-btn btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="Aunque la sintomatología es altamente sugestiva de hiperglucemia, el diagnóstico de diabetes mellitus debe confirmarse mediante los criterios diagnósticos establecidos (glucosa plasmática en ayuno, HbA1c, PTOG o glucosa plasmática al azar en presencia de síntomas clásicos y valores ≥200 mg/dL). Antes de iniciar tratamiento es indispensable caracterizar el estado metabólico, identificar comorbilidades y establecer una línea basal para el seguimiento." data-pc13-pearl-title="PERLA CLÍNICA No. 1" data-pc13-pearl="Hasta el 50% de los pacientes con diabetes mellitus tipo 2 presentan complicaciones microvasculares o macrovasculares al momento del diagnóstico debido al largo periodo de hiperglucemia asintomática."><span class="badge text-bg-light text-success me-2">D</span>Prescribir antibióticos por probable infección urinaria.</button>
            </div>
            <div class="pc13-pearl-box alert alert-success border-start border-4 mt-3" data-pc13-pearl-box hidden></div>
          </div>
        </div>
        <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso2.png" alt="Doctora guia PRONAM">
      </div>
      <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
    </article>
    
    <article class="pc13-slide p-4 p-lg-5" data-pc13-slide="7">
        <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
        <div class="pc13-body container-fluid">
            <h1 class="display-6 fw-bold text-dark lh-sm mb-4">
                El médico decide ampliar el interrogatorio
            </h1>
            <div class="row align-items-center g-4">

                <!-- Doctora -->
                <div class="col-12 col-lg-4 text-center">

                    <img
                        class="pc13-doctor img-fluid mx-auto d-block"
                        src="img/doctora_caso.png"
                        alt="Doctora guía PRONAM">

                </div>

                <!-- Tarjetas -->
                <div class="pc13-copy col-12 col-lg-8">

                    <div class="row row-cols-1 row-cols-md-2 g-4">

                        <!-- Antecedentes heredofamiliares -->
                        <div class="col">

                            <div
                                class="card h-100 shadow-sm pc13-interrogatorio-card"
                                role="button"
                                tabindex="0"
                                data-pc13-card-title="Riesgo familiar"
                                data-pc13-card-template="pc13-modal-info-1">

                                <img
                                    src="img/antecedentesHeredofamiliares.png"
                                    class="card-img-top"
                                    alt="Antecedentes heredofamiliares">

                                <div class="card-body d-flex align-items-center justify-content-center">

                                    <h2 class="card-title h5 text-center mb-0">
                                        Antecedentes heredofamiliares
                                    </h2>

                                </div>

                            </div>

                        </div>

                        <!-- Antecedentes personales patológicos -->
                        <div class="col">

                            <div
                                class="card h-100 shadow-sm pc13-interrogatorio-card"
                                role="button"
                                tabindex="0"
                                data-pc13-card-title="Comorbilidades conocidas"
                                data-pc13-card-template="pc13-modal-info-2">

                                <img
                                    src="img/antecedentesPersonalesPatologicos.png"
                                    class="card-img-top"
                                    alt="Antecedentes personales patológicos">

                                <div class="card-body d-flex align-items-center justify-content-center">

                                    <h2 class="card-title h5 text-center mb-0">
                                        Antecedentes personales patológicos
                                    </h2>

                                </div>

                            </div>

                        </div>

                        <!-- Antecedentes personales no patológicos -->
                        <div class="col">

                            <div
                                class="card h-100 shadow-sm pc13-interrogatorio-card"
                                role="button"
                                tabindex="0"
                                data-pc13-card-title="Estilo de vida y contexto"
                                data-pc13-card-template="pc13-modal-info-3">

                                <img
                                    src="img/antecedentesPersonalesNoPatologicos.png"
                                    class="card-img-top"
                                    alt="Antecedentes personales no patológicos">

                                <div class="card-body d-flex align-items-center justify-content-center">

                                    <h2 class="card-title h5 text-center mb-0">
                                        Antecedentes personales no patológicos
                                    </h2>

                                </div>

                            </div>

                        </div>

                        <!-- Revisión por aparatos y sistemas -->
                        <div class="col">

                            <div
                                class="card h-100 shadow-sm pc13-interrogatorio-card"
                                role="button"
                                tabindex="0"
                                data-pc13-card-title="Síntomas actuales"
                                data-pc13-card-template="pc13-modal-info-4">

                                <img
                                    src="img/revisionAparatosSistemas.png"
                                    class="card-img-top"
                                    alt="Revisión por aparatos y sistemas">

                                <div class="card-body d-flex align-items-center justify-content-center">

                                    <h2 class="card-title h5 text-center mb-0">
                                        Revisión por aparatos y sistemas
                                    </h2>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>
        <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
    </article>
   

    <article class="pc13-slide p-4 p-lg-5" data-pc13-slide="11">
      <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
      <div class="pc13-body container-fluid row align-items-center g-4">
        <div class="pc13-copy col-12 col-lg-8">
          <h1 class="display-6 fw-bold text-dark lh-sm mb-3">¿Qué factores de riesgo para diabetes mellitus tipo 2 identifica en este paciente? (Selección múltiple)</h1>
          <div class="pc13-question">
            <div class="pc13-options-grid row row-cols-1 row-cols-md-2 g-2 mt-3">
              <button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="El paciente concentra múltiples factores de riesgo mayores para diabetes mellitus tipo 2 y enfermedad cardiovascular. Su perfil clínico justifica no solo la confirmación diagnóstica, sino una evaluación integral que incluya riesgo renal, hepático y cardiovascular desde el primer contacto."><span class="badge text-bg-light text-success me-2">A</span>Edad mayor de 45 años.</button>
              <button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="El paciente concentra múltiples factores de riesgo mayores para diabetes mellitus tipo 2 y enfermedad cardiovascular. Su perfil clínico justifica no solo la confirmación diagnóstica, sino una evaluación integral que incluya riesgo renal, hepático y cardiovascular desde el primer contacto."><span class="badge text-bg-light text-success me-2">B</span>Obesidad y hábitos alimentarios inadecuados.</button>
              <button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="El paciente concentra múltiples factores de riesgo mayores para diabetes mellitus tipo 2 y enfermedad cardiovascular. Su perfil clínico justifica no solo la confirmación diagnóstica, sino una evaluación integral que incluya riesgo renal, hepático y cardiovascular desde el primer contacto."><span class="badge text-bg-light text-success me-2">C</span>Sedentarismo.</button>
              <button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="El paciente concentra múltiples factores de riesgo mayores para diabetes mellitus tipo 2 y enfermedad cardiovascular. Su perfil clínico justifica no solo la confirmación diagnóstica, sino una evaluación integral que incluya riesgo renal, hepático y cardiovascular desde el primer contacto."><span class="badge text-bg-light text-success me-2">D</span>Antecedentes familiares de primer grado.</button>
              <button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="El paciente concentra múltiples factores de riesgo mayores para diabetes mellitus tipo 2 y enfermedad cardiovascular. Su perfil clínico justifica no solo la confirmación diagnóstica, sino una evaluación integral que incluya riesgo renal, hepático y cardiovascular desde el primer contacto."><span class="badge text-bg-light text-success me-2">E</span>Hipertensión arterial.</button>
              <button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="El paciente concentra múltiples factores de riesgo mayores para diabetes mellitus tipo 2 y enfermedad cardiovascular. Su perfil clínico justifica no solo la confirmación diagnóstica, sino una evaluación integral que incluya riesgo renal, hepático y cardiovascular desde el primer contacto."><span class="badge text-bg-light text-success me-2">F</span>Dislipidemia.</button>
              <button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="1" data-pc13-feedback="El paciente concentra múltiples factores de riesgo mayores para diabetes mellitus tipo 2 y enfermedad cardiovascular. Su perfil clínico justifica no solo la confirmación diagnóstica, sino una evaluación integral que incluya riesgo renal, hepático y cardiovascular desde el primer contacto."><span class="badge text-bg-light text-success me-2">G</span>Todos los anteriores.</button>
            </div>
            <div class="pc13-pearl-box alert alert-success border-start border-4 mt-3" data-pc13-pearl-box hidden></div>
          </div>
        </div>
        <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso2.png" alt="Doctora guia PRONAM">
      </div>
      <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
    </article>
    <div class="pc13-actions d-flex align-items-center justify-content-center gap-3">
      <button class="pc13-btn btn btn-success fw-semibold px-4 shadow-sm" type="button" data-pc13-prev disabled>Anterior</button>
      <button class="pc13-btn btn btn-success fw-semibold px-4 shadow-sm" type="button" data-pc13-next>Continuar</button>
    </div>
    <div class="pc13-count badge text-bg-light text-secondary" data-pc13-count>1 / 11</div>
    <template id="pc13-modal-info-1">
      <h3>Padre con diabetes mellitus tipo 2 diagnosticada a los 52 años; falleció por infarto agudo de miocardio a los 61 años.</h3>
      <h3>Madre con hipertensión arterial sistémica y enfermedad renal crónica.</h3>
      <h3>Hermano mayor con obesidad y dislipidemia. Abuelo paterno con amputación supracondílea secundaria a pie diabético.</h3>
    </template>
    <template id="pc13-modal-info-2">
      <h3>Hipertensión arterial diagnosticada hace cinco años, con apego irregular al tratamiento.</h3>
      <h3>Dislipidemia conocida desde hace tres años, sin tratamiento actual.</h3>
      <h3>Niega enfermedades autoinmunes, pancreatitis o uso de glucocorticoides.</h3>
      <h3>Sin antecedentes de hospitalizaciones por hiperglucemia.</h3>
    </template>
    <template id="pc13-modal-info-3">
      <h3>Sedentarismo; no realiza actividad física programada.</h3>
      <h3>Alimentación rica en carbohidratos refinados y bebidas azucaradas.</h3>
      <h3>Consumo frecuente de comida rápida (4-5 veces por semana).</h3>
      <h3>Exfumador (15 paquetes-año), suspendido hace cinco años.</h3>
      <h3>Consumo ocasional de alcohol.</h3>
      <h3>Sueño de 5-6 horas por noche debido a largas jornadas laborales.</h3>
    </template>
    <template id="pc13-modal-info-4">
      <h3>Refiere además:</h3>
      <ul>
        <li><h3>Poliuria.</h3></li>
        <li><h3>Polidipsia.</h3></li>
        <li><h3>Polifagia.</h3></li>
        <li><h3>Visión borrosa intermitente.</h3></li>
        <li><h3>Fatiga.</h3></li>
        <li><h3>Calambres nocturnos.</h3></li>
      </ul>
      <h3>Niega dolor torácico, disnea, edema o síntomas neurológicos focales.</h3>
    </template>    <div class="pc13-modal" data-pc13-modal aria-hidden="true">
      <div class="pc13-modal-card card border-0 shadow-lg p-4" data-pc13-modal-card role="dialog" aria-modal="true" aria-label="Detalle interactivo">
        <button class="pc13-modal-x" type="button" data-pc13-modal-close aria-label="Cerrar">&times;</button>
        <h3 data-pc13-modal-title class="h3 fw-semibold text-success-emphasis lh-sm mb-3">Detalle</h3>
        <div data-pc13-modal-body class="pc13-modal-body mb-3"></div>
        <button class="pc13-btn btn btn-success fw-semibold px-4 shadow-sm" type="button" data-pc13-modal-close data-pc13-modal-action>Continuar</button>
      </div>
    </div>
  </div>
  <script>
    (function() {
      var root = document.currentScript.closest('[data-pc13-root]');
      if (!root) return;
      var slides = Array.prototype.slice.call(root.querySelectorAll('[data-pc13-slide]'));
      var prev = root.querySelector('[data-pc13-prev]');
      var next = root.querySelector('[data-pc13-next]');
      var count = root.querySelector('[data-pc13-count]');
      var modal = root.querySelector('[data-pc13-modal]');
      var modalCard = root.querySelector('[data-pc13-modal-card]');
      var modalTitle = root.querySelector('[data-pc13-modal-title]');
      var modalBody = root.querySelector('[data-pc13-modal-body]');
      var modalClose = root.querySelector('[data-pc13-modal-close]');
      var current = 0;
      function openModal(title, body, incorrect, options) {
        options = options || {};
        if (!modal || !modalCard || !modalTitle || !modalBody) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        modalCard.classList.toggle('is-incorrect', !!incorrect);
        modalCard.classList.toggle('is-info', !!options.info);
        modalTitle.textContent = title || 'Detalle';
        if (options.html) {
          modalBody.innerHTML = body || '';
        } else {
          modalBody.textContent = body || '';
        }
        root.querySelectorAll('[data-pc13-modal-action]').forEach(function(action) {
          action.hidden = !!options.hideAction;
        });
      }
      function closeModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
      }
      function show(index) {
        current = Math.max(0, Math.min(index, slides.length - 1));
        slides.forEach(function(slide, slideIndex) { slide.classList.toggle('is-active', slideIndex === current); });
        if (prev) prev.disabled = current === 0;
        if (next) next.textContent = current === slides.length - 1 ? 'Finalizar' : 'Continuar';
        if (count) count.textContent = (current + 1) + ' / ' + slides.length;
      }
      function finishScene() {
        var scene = root.closest('[data-course-section]');
        var nextScene = scene ? scene.nextElementSibling : null;
        while (nextScene && !nextScene.matches('[data-course-section]')) nextScene = nextScene.nextElementSibling;
        if (nextScene) nextScene.scrollIntoView({ behavior:'smooth', block:'start' });
      }
      root.addEventListener('click', function(event) {
        var infoCard = event.target.closest('[data-pc13-card-title]');
        if (infoCard && root.contains(infoCard)) {
          var templateId = infoCard.getAttribute('data-pc13-card-template');
          var template = templateId ? root.querySelector('#' + templateId) : null;
          openModal(
            infoCard.getAttribute('data-pc13-card-title'),
            template ? template.innerHTML : infoCard.getAttribute('data-pc13-card-copy'),
            false,
            { html: !!template, info: true, hideAction: true }
          );
          return;
        }
        var answer = event.target.closest('[data-pc13-answer]');
        if (!answer || !root.contains(answer)) return;
        var correct = answer.getAttribute('data-pc13-answer') === '1';
        var slide = answer.closest('[data-pc13-slide]');
        slide.querySelectorAll('[data-pc13-answer]').forEach(function(button) {
          button.classList.toggle('is-correct', button.getAttribute('data-pc13-answer') === '1');
          button.classList.toggle('is-incorrect', button === answer && !correct);
        });
        var pearl = answer.getAttribute('data-pc13-pearl') || '';
        var pearlTitle = answer.getAttribute('data-pc13-pearl-title') || 'Perla clínica';
        var pearlBox = slide.querySelector('[data-pc13-pearl-box]');
        if (pearl && pearlBox) {
          pearlBox.hidden = false;
          pearlBox.innerHTML = '<strong>' + pearlTitle + '' + pearl;
        }
        openModal('Retroalimentación', answer.getAttribute('data-pc13-feedback') || '', !correct, { hideAction: false });
      });
      root.querySelectorAll('[data-pc13-modal-close]').forEach(function(button) { button.addEventListener('click', closeModal); });
      if (modal) modal.addEventListener('click', function(event) { if (event.target === modal) closeModal(); });
      root.addEventListener('keydown', function(event) { if (event.key === 'Escape') closeModal(); });
      if (prev) prev.addEventListener('click', function() { show(current - 1); });
      if (next) next.addEventListener('click', function() { current === slides.length - 1 ? finishScene() : show(current + 1); });
      show(0);
    })();
  </script>
</section>





    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>
</html>