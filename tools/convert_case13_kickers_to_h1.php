<?php

declare(strict_types=1);

$caseId = 13;
$timestamp = date('Ymd_His');
$backupDir = __DIR__ . '/backups/case13_kickers_before_' . $timestamp;

if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true)) {
    fwrite(STDERR, "No se pudo crear el directorio de respaldo.\n");
    exit(1);
}

$pdo = new PDO('mysql:host=imssight-db;dbname=imssight;charset=utf8mb4', 'imssight_user', 'MiPasswordSegura123!', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$stmt = $pdo->prepare('
    SELECT id, orden, contenido
    FROM escenas
    WHERE id_caso = ?
    ORDER BY orden ASC
');
$stmt->execute([$caseId]);
$scenes = $stmt->fetchAll();

$update = $pdo->prepare('UPDATE escenas SET contenido = ? WHERE id = ? AND id_caso = ?');
$totalChanges = 0;

foreach ($scenes as $scene) {
    $original = (string) $scene['contenido'];

    $updated = preg_replace_callback(
        '/<span\s+class="pc13-kicker[^"]*"([^>]*)>(.*?)<\/span>/isu',
        static function (array $match): string {
            $attributes = trim($match[1]);
            $title = trim($match[2]);
            $extraAttributes = $attributes !== '' ? ' ' . $attributes : '';

            return '<h1 class="display-6 fw-bold text-dark lh-sm mb-3"' . $extraAttributes . '>' . $title . '</h1>';
        },
        $original,
        -1,
        $changes
    );

    if ($changes === 0 || $updated === null) {
        printf("Sin cambios escena %d orden %d\n", (int) $scene['id'], (int) $scene['orden']);
        continue;
    }

    $backupFile = sprintf('%s/scene_%03d_order_%02d.html', $backupDir, (int) $scene['id'], (int) $scene['orden']);
    file_put_contents($backupFile, $original);
    $update->execute([$updated, (int) $scene['id'], $caseId]);
    $totalChanges += $changes;

    printf("OK escena %d orden %d: %d kicker(s) convertidos\n", (int) $scene['id'], (int) $scene['orden'], $changes);
}

echo "Total convertidos: {$totalChanges}\n";
echo "Respaldos: {$backupDir}\n";
