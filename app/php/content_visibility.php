<?php

function columnExists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);

    return (int) $stmt->fetchColumn() > 0;
}

function asegurarColumnasVisibilidadContenido(PDO $pdo): void
{
    if (!columnExists($pdo, 'especialidades', 'visibilidad')) {
        $pdo->exec("
            ALTER TABLE imssight.especialidades
            ADD COLUMN visibilidad ENUM('interna', 'publica', 'ambas')
            NOT NULL DEFAULT 'interna'
            AFTER activo
        ");
    }

    if (!columnExists($pdo, 'especialidades', 'slug_publico')) {
        $pdo->exec("
            ALTER TABLE imssight.especialidades
            ADD COLUMN slug_publico VARCHAR(120) NULL
            AFTER visibilidad
        ");
    }

    $stmt = $pdo->prepare("
        UPDATE imssight.especialidades
        SET
            visibilidad = 'publica',
            slug_publico = 'pronam'
        WHERE UPPER(TRIM(nombre)) = 'PRONAM'
          AND (
            visibilidad = 'interna'
            OR slug_publico IS NULL
            OR slug_publico = ''
          )
    ");
    $stmt->execute();
}

function normalizarVisibilidadContenido($value): string
{
    $value = strtolower(trim((string) $value));

    return in_array($value, ['interna', 'publica', 'ambas'], true)
        ? $value
        : 'interna';
}

function normalizarSlugPublico($value): string
{
    $value = strtolower(trim((string) $value));
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);

    return trim($value, '-');
}

function condicionContenidoInterno(string $alias = 'e'): string
{
    return "$alias.activo = 1 AND $alias.visibilidad IN ('interna', 'ambas')";
}

function condicionContenidoPublico(string $alias = 'e'): string
{
    return "$alias.activo = 1 AND $alias.visibilidad IN ('publica', 'ambas')";
}
