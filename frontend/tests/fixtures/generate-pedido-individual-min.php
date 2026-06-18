<?php

declare(strict_types=1);

require __DIR__.'/../../../backend/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Hoja1');

$headers = ['codigo cliente', 'codigo de articulo', 'cantidad'];
foreach ($headers as $index => $header) {
    $sheet->setCellValue([$index + 1, 1], $header);
}

$sheet->setCellValue([1, 2], 'CLI001');
$sheet->setCellValue([2, 2], 'ART-001');
$sheet->setCellValue([3, 2], 2);

$target = __DIR__.'/pedido-individual-min.xlsx';
(new Xlsx($spreadsheet))->save($target);

echo "Created {$target}\n";
