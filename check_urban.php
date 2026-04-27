<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check the Excel data for URBAN
$file = storage_path('app/roly/MaestroArticulos_ROL_fr-FR_41482.xlsx');
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
$reader->setReadDataOnly(true);
$spreadsheet = $reader->load($file);
$sheet = $spreadsheet->getActiveSheet();
$headers = [];
foreach ($sheet->getRowIterator(1, 1) as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $headers[$cell->getColumn()] = $cell->getValue();
    }
}

echo "=== ROL Headers ===\n";
print_r($headers);

// Find URBAN rows
echo "\n=== URBAN rows in ROL ===\n";
$found = 0;
foreach ($sheet->getRowIterator(2) as $row) {
    $data = [];
    foreach ($row->getCellIterator() as $cell) {
        $col = $cell->getColumn();
        if (isset($headers[$col])) {
            $data[$headers[$col]] = $cell->getValue();
        }
    }
    if (isset($data['MODELCODE']) && $data['MODELCODE'] === 'SU1067') {
        if ($found < 2) {
            echo "MODELCODE: {$data['MODELCODE']}, MODELNAME: {$data['MODELNAME']}\n";
            echo "  PRICE UNIT: " . ($data['PRICE UNIT'] ?? 'N/A') . "\n";
            echo "  PRICE PACK: " . ($data['PRICE PACK'] ?? 'N/A') . "\n";
            echo "  PRICE BOX: " . ($data['PRICE BOX'] ?? 'N/A') . "\n";
        }
        $found++;
    }
    if (isset($data['MODELCODE']) && $data['MODELCODE'] === 'SU1068') {
        if ($found < 4) {
            echo "MODELCODE: {$data['MODELCODE']}, MODELNAME: {$data['MODELNAME']}\n";
            echo "  PRICE UNIT: " . ($data['PRICE UNIT'] ?? 'N/A') . "\n";
            echo "  PRICE PACK: " . ($data['PRICE PACK'] ?? 'N/A') . "\n";
            echo "  PRICE BOX: " . ($data['PRICE BOX'] ?? 'N/A') . "\n";
        }
        $found++;
    }
}

// Also check STA file
echo "\n=== Checking STA file ===\n";
$file2 = storage_path('app/roly/MaestroArticulos_STA_fr-FR_41482.xlsx');
$spreadsheet2 = $reader->load($file2);
$sheet2 = $spreadsheet2->getActiveSheet();
$headers2 = [];
foreach ($sheet2->getRowIterator(1, 1) as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $headers2[$cell->getColumn()] = $cell->getValue();
    }
}
echo "STA Headers: " . implode(', ', array_filter($headers2, fn($h) => str_contains(strtoupper($h ?? ''), 'PRICE'))) . "\n";

foreach ($sheet2->getRowIterator(2) as $row) {
    $data = [];
    foreach ($row->getCellIterator() as $cell) {
        $col = $cell->getColumn();
        if (isset($headers2[$col])) {
            $data[$headers2[$col]] = $cell->getValue();
        }
    }
    if (isset($data['MODELCODE']) && in_array($data['MODELCODE'], ['SU1067','SU1068'])) {
        echo "MODELCODE: {$data['MODELCODE']}, MODELNAME: " . ($data['MODELNAME'] ?? '') . "\n";
        foreach ($data as $k => $v) {
            if (str_contains(strtoupper($k), 'PRICE')) {
                echo "  $k: $v\n";
            }
        }
        break;
    }
}
