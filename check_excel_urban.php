<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$file = storage_path('app/roly/MaestroArticulos_ROL_fr-FR_41482.xlsx');
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

$headers = $data[1];
echo "Price columns:\n";
foreach ($headers as $col => $h) {
    if ($h && stripos($h, 'PRICE') !== false) echo "  $col: $h\n";
}

$modelCodeCol = array_search('MODELCODE', $headers);
echo "\nMODELCODE col: $modelCodeCol\n";

$count = 0;
foreach ($data as $rowNum => $row) {
    if ($rowNum <= 1) continue;
    if (in_array($row[$modelCodeCol] ?? '', ['SU1067', 'SU1068'])) {
        echo "\nRow $rowNum:\n";
        foreach ($headers as $col => $h) {
            if ($row[$col] !== null && $row[$col] !== '') {
                echo "  $h: {$row[$col]}\n";
            }
        }
        $count++;
        if ($count >= 4) break;
    }
}
