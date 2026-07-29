<?php

$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir) && !mkdir($backupDir, 0775, true)) {
    fwrite(STDERR, "No se pudo crear el directorio de respaldo.\n");
    exit(1);
}

$pdo = new PDO('mysql:host=imssight-db;dbname=imssight;charset=utf8mb4', 'imssight_user', 'MiPasswordSegura123!', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$stmt = $pdo->prepare('SELECT contenido FROM escenas WHERE id = 194 AND id_caso = 13 AND orden = 1');
$stmt->execute();
$html = $stmt->fetchColumn();

if ($html === false) {
    fwrite(STDERR, "No se encontró la escena 194 del caso 13.\n");
    exit(1);
}

$backupFile = $backupDir . '/case13_scene194_before_colors_' . date('Ymd_His') . '.html';
file_put_contents($backupFile, $html);

$replacements = [
    '.pc13-flip-front { padding:30px; background:linear-gradient(135deg,#235B4E,#10312B); color:#fff; transform:rotateY(0deg) translateZ(1px); }'
        => '.pc13-flip-front { align-items:center; padding:30px; background:linear-gradient(135deg,#6F7271,#555857); color:#fff; text-align:center; transform:rotateY(0deg) translateZ(1px); }',
    '.pc13-flip-front strong { display:block; max-width:360px; font-size:clamp(25px,2.4vw,34px); line-height:1.02; font-weight:900; }'
        => '.pc13-flip-front strong { display:block; max-width:360px; margin:0 auto; font-size:clamp(25px,2.4vw,34px); line-height:1.02; font-weight:900; }',
    '.pc13-flip-back { transform:rotateY(180deg) translateZ(1px); padding:20px 24px; background:#fff; color:var(--pc13-ink); border-top:8px solid var(--pc13-wine); justify-content:flex-start; }'
        => '.pc13-flip-back { transform:rotateY(180deg) translateZ(1px); padding:20px 24px; background:#fff; color:var(--pc13-ink); border-top:8px solid #111; justify-content:flex-start; }',
    '.pc13-slide:first-child .pc13-case-title { display:block; margin:34px 0 18px; color:var(--pc13-wine);'
        => '.pc13-slide:first-child .pc13-case-title { display:block; margin:34px 0 18px; color:#111;',
];

$updated = strtr($html, $replacements);

if ($updated === $html) {
    fwrite(STDERR, "No se encontraron reglas para actualizar; no se modificó la escena.\n");
    exit(1);
}

$update = $pdo->prepare('UPDATE escenas SET contenido = ? WHERE id = 194 AND id_caso = 13 AND orden = 1');
$update->execute([$updated]);

echo "Respaldo: {$backupFile}\n";
echo "Escena 194 actualizada solo en colores/alineación. Longitud: " . mb_strlen($updated, 'UTF-8') . "\n";
