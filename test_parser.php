<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing PDF Parser</h2>";

// सही पाथ
$parserPath = '../pdfparser/src/Smalot/PdfParser/Parser.php';

echo "Checking path: " . $parserPath . "<br>";

if (file_exists($parserPath)) {
    echo "✅ Parser.php found!<br>";
    
    require_once $parserPath;
    
    if (class_exists('Smalot\PdfParser\Parser')) {
        echo "✅ PDF Parser class loaded successfully!<br>";
        echo "<h3 style='color:green'>Everything is working correctly!</h3>";
    } else {
        echo "❌ PDF Parser class not found<br>";
    }
} else {
    echo "❌ Parser.php NOT found at: " . $parserPath . "<br>";
    
    // बताएँ कि क्या फाइलें हैं
    echo "<br>Checking in parent directory:<br>";
    $parentDir = dirname(__DIR__);
    $pdfparserDir = $parentDir . '/pdfparser/src/Smalot/PdfParser/';
    
    if (file_exists($pdfparserDir)) {
        echo "Files in $pdfparserDir:<br>";
        $files = scandir($pdfparserDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {
                echo "- $file<br>";
            }
        }
    }
}
?>