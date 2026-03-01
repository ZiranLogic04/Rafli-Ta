<?php
$docxPath = 'storage/app/private/letters/2026/02/1771784376_1_Surat_Keterangan.docx';
$zip = new ZipArchive();
$zip->open($docxPath);
$xml = $zip->getFromName('word/document.xml');
$zip->close();

// Extract just the anchor block cleanly
preg_match('/(wp:anchor[^>]*>)/s', $xml, $anchorOpen);
echo "=== Anchor opening tag ===\n";
echo ($anchorOpen[1] ?? 'not found') . "\n\n";

// Extract positionH and positionV
preg_match('/(<wp:positionH.*?<\/wp:positionH>)/s', $xml, $posH);
preg_match('/(<wp:positionV.*?<\/wp:positionV>)/s', $xml, $posV);
preg_match('/(wrapSquare[^\/]*\/>|wrapTight[^\/]*\/>|wrapNone[^\/]*\/>|wrapTopAndBottom[^\/]*\/>)/s', $xml, $wrap);

echo "=== Horizontal Position ===\n";
echo ($posH[1] ?? 'not found') . "\n\n";

echo "=== Vertical Position ===\n";
echo ($posV[1] ?? 'not found') . "\n\n";

echo "=== Wrap Style ===\n";
echo ($wrap[1] ?? 'not found') . "\n\n";

// Convert offsets to cm
preg_match_all('/posOffset>(-?\d+)</', $xml, $offsets);
echo "=== All posOffset values ===\n";
foreach ($offsets[1] as $i => $v) {
    echo "  #$i: $v EMU = " . round((int)$v / 360000, 2) . " cm\n";
}

// Get the distT, distB, distL, distR from anchor  
preg_match('/distT="(\d+)"/', $xml, $dT);
preg_match('/distB="(\d+)"/', $xml, $dB);
preg_match('/distL="(\d+)"/', $xml, $dL);
preg_match('/distR="(\d+)"/', $xml, $dR);

echo "\n=== Distances (margins around image) ===\n";
echo "Top: " . round(($dT[1] ?? 0) / 360000, 2) . " cm\n";
echo "Bottom: " . round(($dB[1] ?? 0) / 360000, 2) . " cm\n";
echo "Left: " . round(($dL[1] ?? 0) / 360000, 2) . " cm\n";
echo "Right: " . round(($dR[1] ?? 0) / 360000, 2) . " cm\n";

echo "\n=== Image Size ===\n";
preg_match('/extent cx="(\d+)" cy="(\d+)"/', $xml, $ext);
echo round(($ext[1] ?? 0) / 360000, 2) . " cm x " . round(($ext[2] ?? 0) / 360000, 2) . " cm\n";
echo round(($ext[1] ?? 0) / 914400 * 96) . " px x " . round(($ext[2] ?? 0) / 914400 * 96) . " px\n";
