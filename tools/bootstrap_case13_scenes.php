<?php

declare(strict_types=1);

$dryRun = in_array('--dry-run', $argv, true);
$caseId = 13;
$timestamp = date('Ymd_His');
$backupDir = __DIR__ . '/backups/case13_bootstrap_before_' . $timestamp;

if (!$dryRun && !is_dir($backupDir) && !mkdir($backupDir, 0775, true)) {
    fwrite(STDERR, "No se pudo crear el directorio de respaldo.\n");
    exit(1);
}

$pdo = new PDO('mysql:host=imssight-db;dbname=imssight;charset=utf8mb4', 'imssight_user', 'MiPasswordSegura123!', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$stmt = $pdo->prepare('
    SELECT id, orden, titulo, contenido
    FROM escenas
    WHERE id_caso = ?
    ORDER BY orden ASC
');
$stmt->execute([$caseId]);
$scenes = $stmt->fetchAll();

if (!$scenes) {
    fwrite(STDERR, "No se encontraron escenas para el caso {$caseId}.\n");
    exit(1);
}

$style = <<<'CSS'
<style>
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
</style>
CSS;

function hasClass(DOMElement $element, string $class): bool
{
    return in_array($class, preg_split('/\s+/', trim($element->getAttribute('class'))), true);
}

function addClasses(DOMElement $element, string $classes): void
{
    $current = preg_split('/\s+/', trim($element->getAttribute('class')));
    $current = array_values(array_filter($current ?: []));

    foreach (preg_split('/\s+/', trim($classes)) as $class) {
        if ($class !== '' && !in_array($class, $current, true)) {
            $current[] = $class;
        }
    }

    $element->setAttribute('class', implode(' ', $current));
}

function removeInlineStyles(DOMXPath $xpath): void
{
    foreach ($xpath->query('//*[@style]') as $element) {
        if ($element instanceof DOMElement) {
            $element->removeAttribute('style');
        }
    }
}

function replaceStyleBlocks(DOMDocument $dom, DOMXPath $xpath, string $style): void
{
    foreach ($xpath->query('//style') as $styleNode) {
        $styleNode->parentNode?->removeChild($styleNode);
    }

    $fragment = $dom->createDocumentFragment();
    $fragment->appendXML($style);

    $root = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " pronam-case13-course ")]')->item(0);
    if ($root instanceof DOMElement) {
        $root->insertBefore($fragment, $root->firstChild);
    }
}

function renameElement(DOMDocument $dom, DOMElement $old, string $newTag): DOMElement
{
    $new = $dom->createElement($newTag);

    foreach ($old->attributes as $attribute) {
        $new->setAttribute($attribute->nodeName, $attribute->nodeValue);
    }

    while ($old->firstChild) {
        $new->appendChild($old->firstChild);
    }

    $old->parentNode?->replaceChild($new, $old);

    return $new;
}

function transformHeadings(DOMDocument $dom, DOMXPath $xpath): void
{
    foreach (iterator_to_array($xpath->query('//h2')) as $heading) {
        if ($heading instanceof DOMElement) {
            $new = renameElement($dom, $heading, 'h1');
            addClasses($new, 'display-6 fw-bold text-dark lh-sm mb-3');
        }
    }

    foreach (iterator_to_array($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " pc13-case-title ")]')) as $title) {
        if ($title instanceof DOMElement && strtolower($title->tagName) !== 'h1') {
            $new = renameElement($dom, $title, 'h1');
            addClasses($new, 'display-6 fw-bold text-dark lh-sm mb-3');
        }
    }

    foreach ($xpath->query('//h1') as $heading) {
        if ($heading instanceof DOMElement) {
            addClasses($heading, 'display-6 fw-bold text-dark lh-sm mb-3');
        }
    }

    foreach ($xpath->query('//h3') as $heading) {
        if ($heading instanceof DOMElement) {
            addClasses($heading, 'h3 fw-semibold text-success-emphasis lh-sm mb-3');
        }
    }
}

function transformLists(DOMXPath $xpath): void
{
    foreach ($xpath->query('//ul[contains(concat(" ", normalize-space(@class), " "), " pc13-list ")]') as $list) {
        if (!$list instanceof DOMElement) {
            continue;
        }

        addClasses($list, 'list-group list-group-flush mb-3');
        foreach ($list->getElementsByTagName('li') as $item) {
            addClasses($item, 'list-group-item px-0');
        }
    }

    foreach ($xpath->query('//ol[contains(concat(" ", normalize-space(@class), " "), " pc13-route-list ")]') as $list) {
        if (!$list instanceof DOMElement) {
            continue;
        }

        addClasses($list, 'list-group list-group-numbered mb-3');
        foreach ($list->getElementsByTagName('li') as $item) {
            addClasses($item, 'list-group-item');
        }
    }
}

function transformClasses(DOMXPath $xpath): void
{
    $map = [
        'pronam-case13-course' => 'pc13-bootstrap',
        'pc13-stage' => 'card rounded-3 bg-white shadow-sm',
        'pc13-slide' => 'p-4 p-lg-5',
        'pc13-line' => 'rounded-pill my-3',
        'pc13-body' => 'container-fluid',
        'pc13-copy' => 'col-12 col-lg-8',
        'pc13-doctor' => 'col-12 col-lg-4 img-fluid mx-auto d-block',
        'pc13-kicker' => 'badge text-bg-light text-danger border border-danger-subtle text-uppercase mb-3',
        'pc13-card-grid' => 'row row-cols-1 row-cols-md-3 g-3 mt-3',
        'pc13-card' => 'col btn btn-light text-start border rounded-3 shadow-sm p-3 h-100',
        'pc13-flip-zone' => 'row row-cols-1 row-cols-md-2 g-3 mt-3',
        'pc13-flip-unit' => 'col',
        'pc13-flip-card' => 'card h-100 text-decoration-none text-reset border-0',
        'pc13-flip-inner' => 'h-100',
        'pc13-flip-face' => 'rounded-3 shadow-sm border p-4',
        'pc13-flip-front' => 'bg-secondary text-white text-center align-items-center',
        'pc13-flip-back' => 'bg-white text-dark border-top border-4 border-dark justify-content-start',
        'pc13-band' => 'alert alert-light border-start border-4 my-3',
        'pc13-row-grid' => 'row row-cols-1 row-cols-md-2 g-2 mt-3',
        'pc13-row' => 'col d-flex gap-3 align-items-center p-3 rounded-3 border bg-light',
        'pc13-actions' => 'd-flex align-items-center justify-content-center gap-3',
        'pc13-btn' => 'btn btn-success fw-semibold px-4 shadow-sm',
        'pc13-btn-secondary' => 'btn-outline-success',
        'pc13-count' => 'badge text-bg-light text-secondary',
        'pc13-options-grid' => 'row row-cols-1 row-cols-md-2 g-2 mt-3',
        'pc13-option-btn' => 'col btn btn-success text-start p-3 rounded-3 shadow-sm d-flex gap-2 align-items-start',
        'pc13-pearl-box' => 'alert alert-success border-start border-4 mt-3',
        'pc13-static-pearl' => 'alert alert-success border-start border-4 mt-3',
        'pc13-modal-card' => 'card border-0 shadow-lg p-4',
    ];

    foreach ($map as $sourceClass => $bootstrapClasses) {
        $query = '//*[contains(concat(" ", normalize-space(@class), " "), " ' . $sourceClass . ' ")]';
        foreach ($xpath->query($query) as $element) {
            if ($element instanceof DOMElement) {
                addClasses($element, $bootstrapClasses);
            }
        }
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " pc13-body ")]') as $body) {
        if ($body instanceof DOMElement) {
            addClasses($body, 'row align-items-center g-4');
        }
    }

    foreach ($xpath->query('//p') as $paragraph) {
        if ($paragraph instanceof DOMElement) {
            addClasses($paragraph, 'mb-3');
        }
    }

    foreach ($xpath->query('//strong') as $strong) {
        if ($strong instanceof DOMElement) {
            addClasses($strong, 'fw-semibold');
        }
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " pc13-card ")]/strong') as $strong) {
        if ($strong instanceof DOMElement) {
            addClasses($strong, 'd-block h3 text-success-emphasis mb-2');
        }
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " pc13-card ")]/span') as $span) {
        if ($span instanceof DOMElement) {
            addClasses($span, 'd-block');
        }
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " pc13-option-btn ")]/span') as $span) {
        if ($span instanceof DOMElement) {
            addClasses($span, 'badge text-bg-light text-success me-2');
        }
    }

    foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " pc13-row ")]/span[1]') as $span) {
        if ($span instanceof DOMElement) {
            addClasses($span, 'badge text-bg-success rounded-pill');
        }
    }
}

function transformHtml(string $html, string $style): string
{
    $dom = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $dom->loadHTML(
        '<?xml encoding="UTF-8"><div id="pc13-fragment-root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    removeInlineStyles($xpath);
    replaceStyleBlocks($dom, $xpath, $style);
    transformHeadings($dom, $xpath);
    transformLists($xpath);
    transformClasses($xpath);

    $wrapper = $dom->getElementById('pc13-fragment-root');
    $output = '';

    if ($wrapper) {
        foreach ($wrapper->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }
    }

    return trim(str_replace('<?xml encoding="UTF-8">', '', $output));
}

$update = $pdo->prepare('UPDATE escenas SET contenido = ? WHERE id = ? AND id_caso = ?');

foreach ($scenes as $scene) {
    $original = (string) $scene['contenido'];
    $updated = transformHtml($original, $style);

    $remainingHeavyCss = preg_match('/font-size\s*:|clamp\s*\(|<style>[\s\S]{2500,}<\/style>/i', $updated) === 1;

    if (!$dryRun) {
        $backupFile = sprintf('%s/scene_%03d_order_%02d.html', $backupDir, (int) $scene['id'], (int) $scene['orden']);
        file_put_contents($backupFile, $original);
        $update->execute([$updated, (int) $scene['id'], $caseId]);
    }

    printf(
        "%s escena %d orden %d: %d -> %d bytes%s\n",
        $dryRun ? 'DRY-RUN' : 'OK',
        (int) $scene['id'],
        (int) $scene['orden'],
        strlen($original),
        strlen($updated),
        $remainingHeavyCss ? ' (revisar CSS remanente)' : ''
    );
}

if (!$dryRun) {
    echo "Respaldos: {$backupDir}\n";
}
