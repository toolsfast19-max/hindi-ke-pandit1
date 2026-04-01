<?php
echo "<h2>Checking PDF Parser Files</h2>";

$basePath = '../pdfparser/src/Smalot/PdfParser/';

$files = [
    'Parser.php',
    'Config.php',
    'Document.php',
    'Page.php',
    'Element.php',
    'Font.php',
    'Header.php',
    'Pages.php',
    'PDFObject.php'
];

$allFound = true;
foreach ($files as $file) {
    $fullPath = $basePath . $file;
    echo "Checking: $file ... ";
    if (file_exists($fullPath)) {
        echo "✅ FOUND<br>";
    } else {
        echo "❌ MISSING<br>";
        $allFound = false;
    }
}

echo "<hr>";
if ($allFound) {
    echo "<h3 style='color:green'>✅ All files found! Ready to use.</h3>";
} else {
    echo "<h3 style='color:red'>❌ Some files are missing. Please download complete library.</h3>";
}

echo "PDF Parser folder path: " . realpath('../pdfparser') . "<br>";
echo "Current directory: " . __DIR__ . "<br>";
?>