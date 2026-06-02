<?php

declare(strict_types=1);

$jsonPath = __DIR__ . '/PQ_PARAMETROS_GRAL.PedidosWeb.seed.json';
$outputPath = __DIR__ . '/Update_PQ_PARAMETROS_GRAL_PedidosWeb_CAPTION_TOOLTIP.sql';

/** @var list<array{programa:string,clave:string,caption:string,tooltip:string}> $rows */
$rows = json_decode((string) file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);

function sqlNv(string $value): string
{
    return "N'" . str_replace("'", "''", $value) . "'";
}

$lines = [
    '/*',
    '  Actualiza CAPTION y TOOLTIP — Programa PedidosWeb',
    '  Fuente: PQ_PARAMETROS_GRAL.PedidosWeb.seed.json',
    '  NO modifica tipo_valor ni columnas Valor_* (verificados en BD).',
    '',
    '  Antes de ejecutar:',
    '    USE [Ankas_del_sur];  -- o la Company DB correspondiente',
    '*/',
    '',
    'SET NOCOUNT ON;',
    'BEGIN TRANSACTION;',
    '',
];

foreach ($rows as $row) {
    $programa = sqlNv($row['programa']);
    $clave = sqlNv($row['clave']);
    $caption = sqlNv($row['caption']);
    $tooltip = sqlNv($row['tooltip']);

    $lines[] = 'UPDATE dbo.PQ_PARAMETROS_GRAL';
    $lines[] = "   SET [CAPTION] = {$caption},";
    $lines[] = "       [TOOLTIP] = {$tooltip}";
    $lines[] = " WHERE [Programa] = {$programa}";
    $lines[] = "   AND [Clave] = {$clave};";
    $lines[] = "IF @@ROWCOUNT = 0 PRINT N'AVISO: sin fila para ' + {$clave};";
    $lines[] = '';
}

$lines = array_merge($lines, [
    '-- Verificación rápida',
    'SELECT [Clave], [tipo_valor], [CAPTION], LEFT(CAST([TOOLTIP] AS NVARCHAR(120)), 120) AS [TOOLTIP_preview]',
    '  FROM dbo.PQ_PARAMETROS_GRAL',
    " WHERE [Programa] = N'PedidosWeb'",
    ' ORDER BY [Clave];',
    '',
    'COMMIT TRANSACTION;',
    "PRINT N'Update CAPTION/TOOLTIP PedidosWeb OK';",
    '',
]);

file_put_contents($outputPath, implode(PHP_EOL, $lines) . PHP_EOL);
fwrite(STDOUT, 'Generado: ' . $outputPath . ' (' . count($rows) . " UPDATEs)\n");
