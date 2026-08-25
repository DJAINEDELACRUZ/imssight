<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  </head>
  <body>







 <section class="pronam-case13-course pc13-bootstrap pc13-scene213" data-pc13-root><style>
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

  .pc13-lab-intro-slide .pc13-lab-intro-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    align-items: center;
    min-height: 38rem;
  }

  .pc13-lab-doctor-zone {
    position: relative;
    display: grid;
    grid-template-columns: minmax(16rem, 24rem) minmax(18rem, 34rem);
    align-items: center;
    justify-content: center;
    gap: clamp(1rem, 3vw, 2.5rem);
    transition: transform .55s ease;
  }

  .pc13-lab-doctor {
    width: min(23rem, 82vw);
    max-height: 32rem;
    transform: translateX(0) scale(1);
    transform-origin: center bottom;
    transition: transform .55s ease, width .55s ease, max-height .55s ease;
  }

  .pc13-doctor-bubble {
    position: relative;
    padding: 1.25rem 1.4rem;
    border: 2px solid rgba(35, 91, 78, .18);
    border-radius: 1.1rem;
    background: rgba(255, 255, 255, .96);
    box-shadow: 0 1rem 2rem rgba(16, 49, 43, .12);
    color: var(--pc13-ink);
    transition: transform .45s ease, opacity .35s ease;
  }

  .pc13-doctor-bubble::before {
    content: "";
    position: absolute;
    left: -0.85rem;
    top: 32%;
    width: 1.35rem;
    height: 1.35rem;
    border-left: 2px solid rgba(35, 91, 78, .18);
    border-bottom: 2px solid rgba(35, 91, 78, .18);
    background: rgba(255, 255, 255, .96);
    transform: rotate(45deg);
  }

  .pc13-doctor-bubble h1 {
    font-size: clamp(1.65rem, 3vw, 2.45rem);
  }

  .pc13-question-panel {
    width: min(58rem, 100%);
    margin: 0 auto;
    opacity: 0;
    max-height: 0;
    overflow: hidden;
    pointer-events: none;
    transform: translateX(2rem);
    transition: opacity .48s ease, transform .55s ease, max-height .55s ease, margin-top .55s ease;
  }

  .pc13-question-bubble {
    position: relative;
    margin-bottom: 1rem;
    padding: 1rem 1.15rem;
    border: 2px solid rgba(35, 91, 78, .18);
    border-radius: 1rem;
    background: rgba(255, 255, 255, .98);
    box-shadow: 0 .9rem 1.8rem rgba(16, 49, 43, .12);
  }

  .pc13-question-bubble::before {
    content: "";
    position: absolute;
    left: -0.72rem;
    top: 1.5rem;
    width: 1.15rem;
    height: 1.15rem;
    border-left: 2px solid rgba(35, 91, 78, .18);
    border-bottom: 2px solid rgba(35, 91, 78, .18);
    background: rgba(255, 255, 255, .98);
    transform: rotate(45deg);
  }

  .pc13-question-bubble h1 {
    margin: 0;
    font-size: clamp(1.35rem, 2vw, 2rem);
  }

  .pc13-lab-intro-slide.is-question-visible .pc13-lab-intro-layout {
    grid-template-columns: minmax(16rem, .45fr) minmax(0, .95fr);
    align-items: center;
  }

  .pc13-lab-intro-slide.is-question-visible .pc13-lab-doctor-zone {
    grid-template-columns: 1fr;
    gap: .75rem;
    justify-items: center;
    transform: translateX(-.5rem);
  }

  .pc13-lab-intro-slide.is-question-visible .pc13-lab-doctor {
    width: min(20rem, 86vw);
    max-height: 29rem;
    transform: translateY(0) scale(1);
  }

  .pc13-lab-intro-slide.is-question-visible .pc13-doctor-bubble {
    opacity: 0;
    max-height: 0;
    padding-top: 0;
    padding-bottom: 0;
    border-width: 0;
    overflow: hidden;
    transform: translateY(-.5rem);
  }

  .pc13-lab-intro-slide.is-question-visible [data-pc13-intro-next] {
    display: none;
  }

  .pc13-lab-intro-slide.is-question-visible .pc13-question-panel {
    opacity: 1;
    max-height: 72rem;
    pointer-events: auto;
    transform: translateX(0);
  }

  @media (max-width: 991.98px) {
    .pc13-lab-doctor-zone,
    .pc13-lab-intro-slide.is-question-visible .pc13-lab-intro-layout {
      grid-template-columns: 1fr;
    }

    .pc13-doctor-bubble::before {
      left: 50%;
      top: -0.75rem;
      transform: translateX(-50%) rotate(135deg);
    }

    .pc13-lab-intro-slide.is-question-visible .pc13-question-panel {
      margin-top: 1rem;
    }
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


  /* pc13-scene213: modales homologados con escena 194 */
  .pc13-scene213 .pc13-modal-card::before,
  .pc13-scene213 .pc13-modal-card::after,
  .pc13-scene213 .pc13-modal-x::before,
  .pc13-scene213 .pc13-modal-x::after {
    content: none !important;
    display: none !important;
  }

  .pc13-scene213 .pc13-modal-x {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    text-align: center !important;
    font-size: 2rem !important;
    font-weight: 900 !important;
    line-height: 1 !important;
    background: #a57f2c !important;
    box-shadow: 0 .85rem 1.75rem rgba(165, 127, 44, .28) !important;
  }

  .pc13-scene213 .pc13-modal-x:hover,
  .pc13-scene213 .pc13-modal-x:focus {
    background: #8f6e26 !important;
    box-shadow: 0 0 0 .25rem rgba(165, 127, 44, .2) !important;
  }

  .pc13-scene213 .pc13-feedback-icon {
    display: none;
    place-items: center;
    width: 4rem;
    height: 4rem;
    margin: 0 0 1rem;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 .85rem 1.6rem rgba(16, 49, 43, .12);
    font-size: 3rem;
    font-weight: 900;
    line-height: 1;
  }

  .pc13-scene213 .pc13-modal-card.is-correct .pc13-feedback-icon,
  .pc13-scene213 .pc13-modal-card.is-incorrect .pc13-feedback-icon {
    display: grid;
  }

  .pc13-scene213 .pc13-modal-card.is-correct .pc13-feedback-icon {
    color: #168A45;
  }

  .pc13-scene213 .pc13-modal-card.is-incorrect .pc13-feedback-icon {
    color: var(--pc13-wine);
  }

  .pc13-scene213 .pc13-modal-card.is-correct {
    background: #fff !important;
    color: #212529 !important;
  }

  .pc13-scene213 .pc13-modal-card.is-incorrect {
    background: var(--pc13-wine) !important;
    color: #fff !important;
  }

  .pc13-scene213 .pc13-modal-card.is-incorrect [data-pc13-modal-title],
  .pc13-scene213 .pc13-modal-card.is-incorrect [data-pc13-modal-body],
  .pc13-scene213 .pc13-modal-card.is-incorrect .pc13-modal-body {
    color: #fff !important;
  }



  /* pc13-scene213: fuerza visible cierre dorado */
  .pc13-scene213 .pc13-modal-card .pc13-modal-x[data-pc13-modal-close] {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 0 !important;
    color: #fff !important;
    font-size: 2rem !important;
    font-weight: 900 !important;
    line-height: 1 !important;
    text-indent: 0 !important;
    background: #a57f2c !important;
  }



  /* pc13-scene213: geometria copiada de escena 194 */
  .pc13-scene213 .pc13-modal {
    align-items: center !important;
    justify-items: center !important;
    overflow: hidden !important;
  }

  .pc13-scene213 .pc13-modal-card[data-pc13-modal-card] {
    position: relative !important;
    width: min(820px, calc(100% - 2rem)) !important;
    max-height: calc(100% - 2rem) !important;
    overflow: visible !important;
    border-radius: 1rem !important;
    padding: 2rem !important;
  }

  .pc13-scene213 .pc13-modal-card[data-pc13-modal-card].is-correct {
    background: #fff !important;
    color: #212529 !important;
  }

  .pc13-scene213 .pc13-modal-card[data-pc13-modal-card].is-incorrect {
    background: var(--pc13-wine) !important;
    color: #fff !important;
  }

  .pc13-scene213 .pc13-modal-card[data-pc13-modal-card].is-incorrect [data-pc13-modal-title],
  .pc13-scene213 .pc13-modal-card[data-pc13-modal-card].is-incorrect [data-pc13-modal-body],
  .pc13-scene213 .pc13-modal-card[data-pc13-modal-card].is-incorrect .pc13-modal-body {
    color: #fff !important;
  }

  .pc13-scene213 .pc13-feedback-icon[data-pc13-feedback-icon] {
    display: none;
    place-items: center;
    width: 4rem !important;
    height: 4rem !important;
    margin: 0 0 1rem !important;
    border-radius: 50% !important;
    background: #fff !important;
    box-shadow: 0 .85rem 1.6rem rgba(16, 49, 43, .12) !important;
    font-size: 3rem !important;
    font-weight: 900 !important;
    line-height: 1 !important;
  }

  .pc13-scene213 .pc13-modal-card.is-correct .pc13-feedback-icon[data-pc13-feedback-icon],
  .pc13-scene213 .pc13-modal-card.is-incorrect .pc13-feedback-icon[data-pc13-feedback-icon] {
    display: grid !important;
  }

  .pc13-scene213 .pc13-modal-card.is-correct .pc13-feedback-icon[data-pc13-feedback-icon] {
    color: #168A45 !important;
  }

  .pc13-scene213 .pc13-modal-card.is-incorrect .pc13-feedback-icon[data-pc13-feedback-icon] {
    color: var(--pc13-wine) !important;
  }

  .pc13-scene213 .pc13-modal-card .pc13-modal-x[data-pc13-modal-close] {
    position: absolute !important;
    top: -1.15rem !important;
    right: -1.15rem !important;
    z-index: 2 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 3rem !important;
    height: 3rem !important;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 50% !important;
    background: #a57f2c !important;
    color: #fff !important;
    font-size: 2rem !important;
    font-weight: 900 !important;
    line-height: 1 !important;
    text-indent: 0 !important;
    box-shadow: 0 .85rem 1.75rem rgba(165, 127, 44, .28) !important;
  }

  </style>

    <div class="pc13-stage card rounded-3 bg-white shadow-sm" aria-label="Capítulo 3 Estudios de laboratorio, integración diagnóstica y estratificación inicial del riesgo">
      <article class="pc13-slide pc13-lab-intro-slide is-active p-4 p-lg-5" data-pc13-slide="0" data-pc13-gated="intro-question">
        <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
        <div class="pc13-body pc13-lab-intro-layout container-fluid g-4">
          <div class="pc13-lab-doctor-zone">
            <img class="pc13-doctor pc13-lab-doctor img-fluid mx-auto d-block" src="img/doctora_caso.png" alt="Doctora guia PRONAM">
            <div class="pc13-doctor-bubble" role="group" aria-label="Evolución del caso">
              <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Evolución del caso</h1>
            <p class="mb-3">Con base en la historia clínica y la exploración física, el médico considera altamente probable la presencia de síndrome metabólico con diabetes mellitus tipo 2 no diagnosticada. Antes de iniciar tratamiento, decide solicitar estudios dirigidos para:</p>
            <ul class="">
              <li class="">Confirmar el diagnóstico.</li>
              <li class="">Identificar alteraciones metabólicas asociadas.</li>
              <li class="">Evaluar daño a órgano blanco.</li>
              <li class="">Estratificar el riesgo cardiovascular y renal.</li>
              <li class="">Detectar posibles causas secundarias de hiperglucemia.</li>
            </ul>
              <button class="pc13-btn btn btn-success fw-semibold px-4 shadow-sm mt-3" type="button" data-pc13-intro-next>OK, continuar</button>
            </div>
          </div>
          <div class="pc13-question-panel" data-pc13-intro-question aria-hidden="true">
            <div class="pc13-question-bubble" aria-label="Pregunta de la doctora">
              <h1 class="fw-bold text-dark lh-sm">¿Cuáles de los siguientes estudios solicitaría en la primera valoración? (Selección múltiple)</h1>
            </div>
            <div class="pc13-question">
    <div class="pc13-options-grid row row-cols-1 row-cols-md-2 g-2 mt-3">
      <button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="La evaluación inicial de un paciente con sospecha de diabetes mellitus tipo 2 debe ser integral. Además de confirmar la hiperglucemia, es indispensable valorar función renal, perfil lipídico, daño renal temprano (albuminuria), enfermedad hepática metabólica, alteraciones hematológicas y riesgo cardiovascular basal. La TSH puede ser útil en pacientes con obesidad, dislipidemia o síntomas compatibles con enfermedad tiroidea." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">1</span>Glucosa plasmática en ayuno.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="La evaluación inicial de un paciente con sospecha de diabetes mellitus tipo 2 debe ser integral. Además de confirmar la hiperglucemia, es indispensable valorar función renal, perfil lipídico, daño renal temprano (albuminuria), enfermedad hepática metabólica, alteraciones hematológicas y riesgo cardiovascular basal. La TSH puede ser útil en pacientes con obesidad, dislipidemia o síntomas compatibles con enfermedad tiroidea." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">2</span>Hemoglobina glucosilada (HbA1c).</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="La evaluación inicial de un paciente con sospecha de diabetes mellitus tipo 2 debe ser integral. Además de confirmar la hiperglucemia, es indispensable valorar función renal, perfil lipídico, daño renal temprano (albuminuria), enfermedad hepática metabólica, alteraciones hematológicas y riesgo cardiovascular basal. La TSH puede ser útil en pacientes con obesidad, dislipidemia o síntomas compatibles con enfermedad tiroidea." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">3</span>Química sanguínea (glucosa, urea, creatinina y electrolitos).</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="La evaluación inicial de un paciente con sospecha de diabetes mellitus tipo 2 debe ser integral. Además de confirmar la hiperglucemia, es indispensable valorar función renal, perfil lipídico, daño renal temprano (albuminuria), enfermedad hepática metabólica, alteraciones hematológicas y riesgo cardiovascular basal. La TSH puede ser útil en pacientes con obesidad, dislipidemia o síntomas compatibles con enfermedad tiroidea." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">4</span>Perfil de lípidos en ayuno.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="La evaluación inicial de un paciente con sospecha de diabetes mellitus tipo 2 debe ser integral. Además de confirmar la hiperglucemia, es indispensable valorar función renal, perfil lipídico, daño renal temprano (albuminuria), enfermedad hepática metabólica, alteraciones hematológicas y riesgo cardiovascular basal. La TSH puede ser útil en pacientes con obesidad, dislipidemia o síntomas compatibles con enfermedad tiroidea." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">5</span>Examen general de orina y relación albúmina/creatinina urinaria.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="La evaluación inicial de un paciente con sospecha de diabetes mellitus tipo 2 debe ser integral. Además de confirmar la hiperglucemia, es indispensable valorar función renal, perfil lipídico, daño renal temprano (albuminuria), enfermedad hepática metabólica, alteraciones hematológicas y riesgo cardiovascular basal. La TSH puede ser útil en pacientes con obesidad, dislipidemia o síntomas compatibles con enfermedad tiroidea." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">6</span>Pruebas de función hepática.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="La evaluación inicial de un paciente con sospecha de diabetes mellitus tipo 2 debe ser integral. Además de confirmar la hiperglucemia, es indispensable valorar función renal, perfil lipídico, daño renal temprano (albuminuria), enfermedad hepática metabólica, alteraciones hematológicas y riesgo cardiovascular basal. La TSH puede ser útil en pacientes con obesidad, dislipidemia o síntomas compatibles con enfermedad tiroidea." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">7</span>Biometría hemática.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="La evaluación inicial de un paciente con sospecha de diabetes mellitus tipo 2 debe ser integral. Además de confirmar la hiperglucemia, es indispensable valorar función renal, perfil lipídico, daño renal temprano (albuminuria), enfermedad hepática metabólica, alteraciones hematológicas y riesgo cardiovascular basal. La TSH puede ser útil en pacientes con obesidad, dislipidemia o síntomas compatibles con enfermedad tiroidea." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">8</span>TSH.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="La evaluación inicial de un paciente con sospecha de diabetes mellitus tipo 2 debe ser integral. Además de confirmar la hiperglucemia, es indispensable valorar función renal, perfil lipídico, daño renal temprano (albuminuria), enfermedad hepática metabólica, alteraciones hematológicas y riesgo cardiovascular basal. La TSH puede ser útil en pacientes con obesidad, dislipidemia o síntomas compatibles con enfermedad tiroidea." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">9</span>Electrocardiograma de 12 derivaciones.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="1" data-pc13-feedback="La evaluación inicial de un paciente con sospecha de diabetes mellitus tipo 2 debe ser integral. Además de confirmar la hiperglucemia, es indispensable valorar función renal, perfil lipídico, daño renal temprano (albuminuria), enfermedad hepática metabólica, alteraciones hematológicas y riesgo cardiovascular basal. La TSH puede ser útil en pacientes con obesidad, dislipidemia o síntomas compatibles con enfermedad tiroidea." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">10</span>Todas las anteriores.</button>
    </div>
    <div class="pc13-pearl-box alert alert-success border-start border-4 mt-3" data-pc13-pearl-box hidden></div>
  </div>
          </div>
        </div>
        <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
      </article>


  <article class="pc13-slide p-4 p-lg-5" data-pc13-slide="2">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Estudios de laboratorio solicitados</h1>
        <p class="mb-3">Biometría hemática</p>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Estudio</span><span>Resultado</span><span>Valores de referencia</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Hemoglobina</span><span>15.6 g/dL</span><span>13.5–17.5</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Hematocrito</span><span>46 %</span><span>41–53</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Leucocitos</span><span>7,900/µL</span><span>4,500–11,000</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Plaquetas</span><span>262,000/µL</span><span>150,000–450,000</span></div>
  <p class="mb-3">Interpretación: Sin alteraciones hematológicas relevantes.</p>
  <p class="mb-3">Química sanguínea</p>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Parámetro</span><span>Resultado</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Glucosa en ayuno</span><span>178 mg/dL</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Urea</span><span>34 mg/dL</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Creatinina</span><span>0.93 mg/dL</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">eTFG (CKD-EPI)</span><span>101 mL/min/1.73 m²</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Sodio</span><span>139 mEq/L</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Potasio</span><span>4.2 mEq/L</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Cloro</span><span>102 mEq/L</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">Bicarbonato</span><span>24 mEq/L</span></div>
  <p class="mb-3">Interpretación</p>
  <p class="mb-3">Existe hiperglucemia en rango diagnóstico para diabetes mellitus. La función renal es normal y no hay alteraciones hidroelectrolíticas que sugieran una descompensación metabólica aguda.</p>
  <p class="mb-3">Hemoglobina glucosilada (HbA1c)</p>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">8.</span><span>4 %</span></div>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="3">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">¿Qué representa la HbA1c en la práctica clínica?</h1>

  <div class="pc13-question">
    <div class="pc13-options-grid row row-cols-1 row-cols-md-2 g-2 mt-3">
      <button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="La HbA1c refleja la exposición promedio de los eritrocitos a la glucosa durante su vida media (aproximadamente 120 días), con mayor influencia de las últimas 8–12 semanas. Es útil para el diagnóstico y seguimiento, aunque puede ser menos fiable en condiciones que alteran el recambio eritrocitario (anemias hemolíticas, transfusiones, hemoglobinopatías, enfermedad renal avanzada, entre otras). Perfil de lípidos Parámetro	Resultado	Meta general en DM2* Colesterol total	236 mg/dL	Individualizar LDL-C	154 mg/dL	&lt;70 mg/dL si alto riesgo CV HDL-C	37 mg/dL	&gt;40 mg/dL (hombre) Triglicéridos	248 mg/dL	&lt;150 mg/dL *Las metas dependen del riesgo cardiovascular global. Interpretación Dislipidemia aterogénica característica del síndrome metabólico: hipertrigliceridemia, HDL bajo y LDL elevado. Función hepática Estudio	Resultado AST	34 U/L ALT	58 U/L FA	88 U/L GGT	67 U/L Bilirrubinas	Normales Albúmina	4.4 g/dL" data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">A</span>La glucemia del día de la consulta.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="1" data-pc13-feedback="La HbA1c refleja la exposición promedio de los eritrocitos a la glucosa durante su vida media (aproximadamente 120 días), con mayor influencia de las últimas 8–12 semanas. Es útil para el diagnóstico y seguimiento, aunque puede ser menos fiable en condiciones que alteran el recambio eritrocitario (anemias hemolíticas, transfusiones, hemoglobinopatías, enfermedad renal avanzada, entre otras). Perfil de lípidos Parámetro	Resultado	Meta general en DM2* Colesterol total	236 mg/dL	Individualizar LDL-C	154 mg/dL	&lt;70 mg/dL si alto riesgo CV HDL-C	37 mg/dL	&gt;40 mg/dL (hombre) Triglicéridos	248 mg/dL	&lt;150 mg/dL *Las metas dependen del riesgo cardiovascular global. Interpretación Dislipidemia aterogénica característica del síndrome metabólico: hipertrigliceridemia, HDL bajo y LDL elevado. Función hepática Estudio	Resultado AST	34 U/L ALT	58 U/L FA	88 U/L GGT	67 U/L Bilirrubinas	Normales Albúmina	4.4 g/dL" data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">B</span>El promedio aproximado de glucosa de los últimos 2–3 meses.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="La HbA1c refleja la exposición promedio de los eritrocitos a la glucosa durante su vida media (aproximadamente 120 días), con mayor influencia de las últimas 8–12 semanas. Es útil para el diagnóstico y seguimiento, aunque puede ser menos fiable en condiciones que alteran el recambio eritrocitario (anemias hemolíticas, transfusiones, hemoglobinopatías, enfermedad renal avanzada, entre otras). Perfil de lípidos Parámetro	Resultado	Meta general en DM2* Colesterol total	236 mg/dL	Individualizar LDL-C	154 mg/dL	&lt;70 mg/dL si alto riesgo CV HDL-C	37 mg/dL	&gt;40 mg/dL (hombre) Triglicéridos	248 mg/dL	&lt;150 mg/dL *Las metas dependen del riesgo cardiovascular global. Interpretación Dislipidemia aterogénica característica del síndrome metabólico: hipertrigliceridemia, HDL bajo y LDL elevado. Función hepática Estudio	Resultado AST	34 U/L ALT	58 U/L FA	88 U/L GGT	67 U/L Bilirrubinas	Normales Albúmina	4.4 g/dL" data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">C</span>La reserva pancreática.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="La HbA1c refleja la exposición promedio de los eritrocitos a la glucosa durante su vida media (aproximadamente 120 días), con mayor influencia de las últimas 8–12 semanas. Es útil para el diagnóstico y seguimiento, aunque puede ser menos fiable en condiciones que alteran el recambio eritrocitario (anemias hemolíticas, transfusiones, hemoglobinopatías, enfermedad renal avanzada, entre otras). Perfil de lípidos Parámetro	Resultado	Meta general en DM2* Colesterol total	236 mg/dL	Individualizar LDL-C	154 mg/dL	&lt;70 mg/dL si alto riesgo CV HDL-C	37 mg/dL	&gt;40 mg/dL (hombre) Triglicéridos	248 mg/dL	&lt;150 mg/dL *Las metas dependen del riesgo cardiovascular global. Interpretación Dislipidemia aterogénica característica del síndrome metabólico: hipertrigliceridemia, HDL bajo y LDL elevado. Función hepática Estudio	Resultado AST	34 U/L ALT	58 U/L FA	88 U/L GGT	67 U/L Bilirrubinas	Normales Albúmina	4.4 g/dL" data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">D</span>La resistencia a la insulina.</button>
    </div>
    <div class="pc13-pearl-box alert alert-success border-start border-4 mt-3" data-pc13-pearl-box hidden></div>
  </div>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso2.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="4">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Integración</h1>
        <p class="mb-3">La elevación leve de ALT y GGT, en el contexto de obesidad y resistencia a la insulina, orienta a enfermedad hepática esteatósica asociada a disfunción metabólica (MASLD). Debe confirmarse con evaluación dirigida (por ejemplo, ultrasonido hepático y cálculo de índices no invasivos de fibrosis cuando estén indicados).</p>
  <p class="mb-3">Examen general de orina</p>
  <ul class="pc13-list list-group list-group-flush mb-3"><li class="list-group-item px-0">Glucosuria: ++</li><li class="list-group-item px-0">Cetonas: negativas</li><li class="list-group-item px-0">Proteínas: negativas</li><li class="list-group-item px-0">Leucocitos: negativos</li><li class="list-group-item px-0">Nitritos: negativos</li></ul>
  <p class="mb-3">Relación albúmina/creatinina urinaria</p>
  <p class="mb-3">18 mg/g</p>
  <p class="mb-3">Interpretación</p>
  <p class="mb-3">No existe albuminuria en este momento (&lt;30 mg/g), lo que sugiere ausencia de nefropatía diabética detectable al diagnóstico. Este resultado servirá como línea basal para el seguimiento.</p>
  <p class="mb-3">Electrocardiograma</p>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="5">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Laboratorio y diagnóstico</h1>
        <ul class="pc13-list list-group list-group-flush mb-3"><li class="list-group-item px-0">Ritmo sinusal.</li><li class="list-group-item px-0">Frecuencia: 82 lpm.</li><li class="list-group-item px-0">Eje normal.</li><li class="list-group-item px-0">Sin datos de isquemia aguda.</li><li class="list-group-item px-0">Sin criterios de hipertrofia ventricular izquierda.</li></ul>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso2.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="6">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Integración de resultados</h1>
        <p class="mb-3">Criterios diagnósticos de diabetes mellitus</p>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="7">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">¿Cuáles de los siguientes criterios cumple este paciente?</h1>

  <div class="pc13-question">
    <div class="pc13-options-grid row row-cols-1 row-cols-md-2 g-2 mt-3">
      <button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="1" data-pc13-feedback="Revisa el razonamiento clínico y continúa con el caso." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">1</span>Glucosa plasmática en ayuno ≥126 mg/dL.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="Revisa el razonamiento clínico y continúa con el caso." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">2</span>HbA1c ≥6.5%.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="Revisa el razonamiento clínico y continúa con el caso." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">3</span>Glucosa al azar ≥200 mg/dL con síntomas clásicos documentada en esta consulta.</button><button class="pc13-option-btn col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start" type="button" data-pc13-answer="0" data-pc13-feedback="Revisa el razonamiento clínico y continúa con el caso." data-pc13-pearl-title="" data-pc13-pearl=""><span class="badge text-bg-light text-success me-2">4</span>Prueba oral de tolerancia a la glucosa con valor a las 2 horas ≥200 mg/dL (no realizada).</button>
    </div>
    <div class="pc13-pearl-box alert alert-success border-start border-4 mt-3" data-pc13-pearl-box hidden></div>
  </div>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso2.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="8">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Integración diagnóstica final</h1>
        </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="9">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Diagnóstico principal</h1>

  <ul class="pc13-list list-group list-group-flush mb-3"><li class="list-group-item px-0">Diabetes mellitus tipo 2 de nuevo diagnóstico.</li></ul>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso2.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="10">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Diagnósticos asociados</h1>

  <ul class="pc13-list list-group list-group-flush mb-3"><li class="list-group-item px-0">Síndrome metabólico.</li><li class="list-group-item px-0">Obesidad grado I.</li><li class="list-group-item px-0">Obesidad abdominal.</li><li class="list-group-item px-0">Hipertensión arterial sistémica no controlada.</li><li class="list-group-item px-0">Dislipidemia mixta.</li><li class="list-group-item px-0">Alta probabilidad de MASLD.</li><li class="list-group-item px-0">Alto riesgo cardiovascular.</li></ul>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="11">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Perla clínica 4</h1>
        <aside class="pc13-static-pearl alert alert-success border-start border-4 mt-3"><strong class="fw-semibold">Perla clínica 4</strong><p class="mb-3">El diagnóstico de diabetes mellitus tipo 2 no debe marcar el final de la evaluación, sino el inicio de una estratificación integral. En la primera consulta deben definirse el riesgo cardiovascular, la función renal, la presencia de albuminuria, el estado hepático, las comorbilidades y las complicaciones ya existentes. Estos elementos determinarán la elección del tratamiento y las metas terapéuticas.</p></aside>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso2.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="12">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Algoritmo de razonamiento clínico</h1>
        <p class="mb-3">Paciente con sospecha de DM2</p>
  <p class="mb-3">│</p>
  <p class="mb-3">▼</p>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="13">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Laboratorio inicial completo</h1>

  <p class="mb-3">│</p>
  <p class="mb-3">├── Glucosa en ayuno: 178 mg/dL</p>
  <p class="mb-3">├── HbA1c: 8.4 %</p>
  <p class="mb-3">├── Perfil lipídico aterogénico</p>
  <p class="mb-3">├── Función renal conservada</p>
  <p class="mb-3">├── Albuminuria ausente</p>
  <p class="mb-3">├── ALT/GGT elevadas</p>
  <p class="mb-3">▼</p>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso2.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="14">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Diagnóstico de DM2 + Síndrome metabólico</h1>

  <p class="mb-3">│</p>
  <p class="mb-3">▼</p>
  <p class="mb-3">Estratificación cardiovascular, renal y hepática</p>
  <p class="mb-3">│</p>
  <p class="mb-3">▼</p>
  <p class="mb-3">Selección del tratamiento individualizado</p>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="15">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Objetivos alcanzados</h1>

  <p class="mb-3">Al finalizar este capítulo, el participante será capaz de:</p>
  <ul class="pc13-list list-group list-group-flush mb-3"><li class="list-group-item px-0">Solicitar el laboratorio inicial recomendado en un paciente con sospecha de diabetes mellitus tipo 2.</li><li class="list-group-item px-0">Interpretar correctamente los criterios diagnósticos de diabetes.</li><li class="list-group-item px-0">Reconocer la importancia de evaluar función renal, albuminuria, perfil lipídico y función hepática desde el diagnóstico.</li><li class="list-group-item px-0">Integrar un diagnóstico sindromático y establecer el perfil cardiometabólico basal.</li></ul>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso2.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article><article class="pc13-slide p-4 p-lg-5" data-pc13-slide="16">
    <div class="pc13-line pc13-line-top rounded-pill my-3" aria-hidden="true"></div>
    <div class="pc13-body container-fluid row align-items-center g-4">
      <div class="pc13-copy col-12 col-lg-8">
        <h1 class="display-6 fw-bold text-dark lh-sm mb-3">Bibliografía (formato Vancouver)</h1>

  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">1.</span><span>American Diabetes Association Professional Practice Committee. Standards of Care in Diabetes—2026. Diabetes Care. 2026.</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">2.</span><span>Secretaría de Salud. CENETEC. Guía de Práctica Clínica: Diagnóstico y tratamiento de la diabetes mellitus tipo 2 en el primer nivel de atención.</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">3.</span><span>Asociación Latinoamericana de Diabetes (ALAD). Guías ALAD para el diagnóstico y tratamiento de la diabetes mellitus tipo 2. Última edición.</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">4.</span><span>Grundy SM, et al. Diagnosis and Management of the Metabolic Syndrome. Circulation. 2005;112:e285-e290.</span></div>
  <div class="pc13-row col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light"><span class="badge text-bg-success rounded-pill">5.</span><span>Alberti KGMM, Eckel RH, Grundy SM, et al. Harmonizing the Metabolic Syndrome. Circulation. 2009;120:1640-1645.</span></div>
      </div>
      <img class="pc13-doctor col-12 col-lg-4 img-fluid mx-auto d-block" src="img/doctora_caso.png" alt="Doctora guia PRONAM">
    </div>
    <div class="pc13-line pc13-line-bottom rounded-pill my-3" aria-hidden="true"></div>
  </article>
      <div class="pc13-actions d-flex align-items-center justify-content-center gap-3">
        <button class="pc13-btn btn btn-success fw-semibold px-4 shadow-sm" type="button" data-pc13-prev disabled>Anterior</button>
        <button class="pc13-btn btn btn-success fw-semibold px-4 shadow-sm" type="button" data-pc13-next>Continuar</button>
      </div>
      <div class="pc13-count badge text-bg-light text-secondary" data-pc13-count>1 / 16</div>
      <div class="pc13-modal" data-pc13-modal>
        <div class="pc13-modal-card card border-0 shadow-lg p-4" data-pc13-modal-card>
          <button class="pc13-modal-x" type="button" data-pc13-modal-close aria-label="Cerrar">&times;</button>
          <div class="pc13-feedback-icon" data-pc13-feedback-icon aria-hidden="true"></div>
          <h3 data-pc13-modal-title class="h3 fw-semibold text-success-emphasis lh-sm mb-3">Retroalimentación</h3>
          <p data-pc13-modal-body class="mb-3"></p>
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
        var modalIcon = root.querySelector('[data-pc13-feedback-icon]');
        var current = 0;
        function currentSlideNeedsIntroAction() {
          return slides[current]
            && slides[current].getAttribute('data-pc13-gated') === 'intro-question'
            && !slides[current].classList.contains('is-question-visible');
        }
        function updateNavState() {
          if (prev) prev.disabled = current === 0;
          if (next) {
            next.disabled = currentSlideNeedsIntroAction();
            next.textContent = current === slides.length - 1 ? 'Finalizar' : 'Continuar';
          }
          if (count) count.textContent = (current + 1) + ' / ' + slides.length;
        }
        function show(index) {
          current = Math.max(0, Math.min(index, slides.length - 1));
          slides.forEach(function(slide, slideIndex) { slide.classList.toggle('is-active', slideIndex === current); });
          updateNavState();
        }
        function finishScene() {
          var scene = root.closest('[data-course-section]');
          var nextScene = scene ? scene.nextElementSibling : null;
          while (nextScene && !nextScene.matches('[data-course-section]')) nextScene = nextScene.nextElementSibling;
          if (nextScene) nextScene.scrollIntoView({ behavior:'smooth', block:'start' });
        }
        root.addEventListener('click', function(event) {
          var introNext = event.target.closest('[data-pc13-intro-next]');
          if (introNext && root.contains(introNext)) {
            var introSlide = introNext.closest('[data-pc13-slide]');
            var questionPanel = introSlide ? introSlide.querySelector('[data-pc13-intro-question]') : null;
            if (introSlide) introSlide.classList.add('is-question-visible');
            if (questionPanel) questionPanel.setAttribute('aria-hidden', 'false');
            updateNavState();
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
          if (modal && modalCard && modalTitle && modalBody) {
            modal.classList.add('is-open');
            modalCard.classList.toggle('is-correct', correct);
            modalCard.classList.toggle('is-incorrect', !correct);
            if (modalIcon) modalIcon.textContent = correct ? '✓' : '×';
            modalTitle.textContent = correct ? 'Retroalimentación' : 'Retroalimentación';
            modalBody.textContent = answer.getAttribute('data-pc13-feedback') || '';
          }
        });
        if (modalClose) modalClose.addEventListener('click', function() { modal.classList.remove('is-open'); });
        if (prev) prev.addEventListener('click', function() { show(current - 1); });
        if (next) next.addEventListener('click', function() { current === slides.length - 1 ? finishScene() : show(current + 1); });
        show(0);
      })();
    </script>
</section>












    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  </body>
</html>
