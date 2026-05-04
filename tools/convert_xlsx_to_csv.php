<?php
/**
 * convert_xlsx_to_csv.php
 * --------------------------
 * استخدمه فقط إذا كنت تريد استبدال CSV الموجودة بملفات xlsx جديدة.
 *
 * الاستخدام:
 *   1. ضع ملفات xlsx في /data/source/
 *   2. ثبّت PhpSpreadsheet:  composer require phpoffice/phpspreadsheet
 *   3. شغّل:  php tools/convert_xlsx_to_csv.php
 *
 * هذا الملف اختياري تماماً - الموقع يعمل بدونه طالما CSV موجودة.
 */
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$map = [
    'users.xlsx'     => 'users.csv',
    'products.xlsx'  => 'products.csv',
    'ratings.xlsx'   => 'ratings.csv',
    'behavior.xlsx'  => 'behavior.csv',
];

$src = __DIR__ . '/../data/source/';
$dst = __DIR__ . '/../data/';

foreach ($map as $xlsx => $csv) {
    $in = $src . $xlsx;
    if (!file_exists($in)) { echo "skip $xlsx\n"; continue; }
    $sheet = IOFactory::load($in)->getActiveSheet();
    $writer = IOFactory::createWriter($sheet->getParent(), 'Csv');
    $writer->save($dst . $csv);
    echo "✓ $xlsx → $csv\n";
}
echo "تم. لا تنسَ مسح /cache/ من لوحة الأدمن.\n";
