<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Manual Autoloader
spl_autoload_register(function ($class) {
    $prefixMap = [
        'PhpOffice\\PhpWord\\' => 'vendor/phpoffice/phpword/src/PhpWord/',
        'Dompdf\\' => 'vendor/dompdf/dompdf/src/',
        'FontLib\\' => 'vendor/phenx/php-font-lib/src/FontLib/',
        'Svg\\' => 'vendor/phenx/php-svg-lib/src/Svg/',
        'Sabberworm\\CSS\\' => 'vendor/sabberworm/php-css-parser/src/',
        'Masterminds\\' => 'vendor/masterminds/html5/src/',
    ];
    foreach ($prefixMap as $prefix => $path) {
        if (strpos($class, $prefix) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = __DIR__ . '/' . $path . str_replace('\\', '/', $relativeClass) . '.php';
            if ($class === 'Dompdf\Cpdf') { $file = __DIR__ . '/vendor/dompdf/dompdf/lib/Cpdf.php'; }
            if (file_exists($file)) { return require $file; }
            if (strpos($class, 'Dompdf\\') === 0) {
                $libFile = __DIR__ . '/vendor/dompdf/dompdf/lib/' . str_replace('\\', '/', $relativeClass) . '.php';
                if (file_exists($libFile)) { return require $libFile; }
            }
        }
    }
    return false;
});

use PhpOffice\PhpWord\IOFactory;

$file = 'storage/app/private/letters/2026/02/1771784376_1_Surat_Keterangan.docx';
if (!file_exists($file)) die("ERROR: File not found: $file\n");

echo "Loading DOCX...\n";
$phpWord = IOFactory::load($file);

echo "Generating HTML (Current Logic)...\n";
$htmlWriter = IOFactory::createWriter($phpWord, 'HTML');
ob_start();
$htmlWriter->save('php://output');
$html = ob_get_clean();

// CURRENT LOGIC FROM CONTROLLER
$customCss = "
    <style>
        @page { margin: 2.5cm; }
        body { 
            font-family: 'Times New Roman', Times, serif !important; 
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
        }
        /* Reset PHPWord intrusive table borders */
        table, td { border: none !important; border-collapse: collapse !important; }
        
        .premium-border {
            border-top: 1px solid #000;
            border-bottom: 3.5px solid #000;
            height: 2px;
            margin: 5px 0 15px 0;
            width: 100%;
            clear: both;
        }
        table { width: 100% !important; }
        img { 
            max-width: 100%; 
            height: auto; 
            vertical-align: middle;
        }
        p { margin: 0; padding: 1px 0; }
    </style>
";
$html = str_replace('</head>', $customCss . '</head>', $html);
$html = preg_replace('/<p>&nbsp;<\/p>/', '<div class="premium-border"></div>', $html, 1);

file_put_contents('debug_refinement_current.html', $html);
echo "HTML saved to debug_refinement_current.html. Please inspect it.\n";
