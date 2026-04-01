<?php
echo "<h2>Checking pdftotext</h2>";

// Check if pdftotext exists
$command = 'pdftotext -v 2>&1';
$output = shell_exec($command);

if (strpos($output, 'pdftotext') !== false) {
    echo "✅ pdftotext is installed!<br>";
    echo "<pre>$output</pre>";
} else {
    echo "❌ pdftotext not found. Please copy pdftotext.exe to C:/xampp/php/<br>";
    echo "Download from: https://github.com/oschwartz10612/poppler-windows/releases/<br>";
}
?>