<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PDF Text Extractor Debug</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
    $uploadDir = '../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['pdf_file']['name']);
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $filePath)) {
        echo "<h3>PDF Uploaded: " . $_FILES['pdf_file']['name'] . "</h3>";
        
        // Read file content
        $content = file_get_contents($filePath);
        
        // Extract text from parentheses (common in PDFs)
        preg_match_all('/\((.*?)\)/s', $content, $matches);
        $text = implode("\n", $matches[1]);
        
        // Clean up
        $text = preg_replace('/[^\x20-\x7E\n]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        echo "<h3>Extracted Text:</h3>";
        echo "<pre style='background:#f0f0f0; padding:15px; border:1px solid #ccc; overflow:auto; max-height:400px;'>";
        echo htmlspecialchars($text);
        echo "</pre>";
        
        // Show raw content for debugging
        echo "<h3>Raw Content (first 1000 chars):</h3>";
        echo "<pre style='background:#f0f0f0; padding:15px; border:1px solid #ccc; overflow:auto; max-height:200px;'>";
        echo htmlspecialchars(substr($content, 0, 1000));
        echo "</pre>";
        
        unlink($filePath);
    } else {
        echo "<p style='color:red'>Failed to upload file</p>";
    }
}
?>

<h2>Upload PDF to Debug</h2>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="pdf_file" accept=".pdf" required>
    <button type="submit">Upload & Debug</button>
</form>

<p><strong>Note:</strong> Upload the same PDF you're trying to use.</p>