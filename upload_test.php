<!DOCTYPE html>
<html>
<head>
    <title>Admin - Upload Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7fafc;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .upload-area {
            border: 2px dashed #ccc;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .upload-area.drag-over {
            background: #e3f2fd;
            border-color: #2196F3;
        }
        button {
            padding: 12px 24px;
            background: #48bb78;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }
        button:hover {
            background: #38a169;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .loading {
            background: #e3f2fd;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>📄 Upload PDF/TXT to Create Test</h1>
    <p>Upload a file with MCQs in format:</p>
    <pre style="background:#f5f5f5; padding:10px; border-radius:5px;">
1. What is the capital of India?
A. Mumbai
B. New Delhi
C. Kolkata
D. Chennai
Answer: B
    </pre>
    
    <div class="upload-area" id="dropZone">
        <p>📁 Drag & Drop file here or click to select</p>
        <input type="file" id="fileInput" accept=".txt,.pdf" style="display: none;">
    </div>
    
    <div id="selectedFile" style="margin: 10px 0; color: #2196F3;"></div>
    
    <button onclick="uploadFile()">🚀 Upload & Create Test</button>
    
    <div id="result" style="margin-top: 20px;"></div>
    
    <a href="view_tests.php" class="back-link">← View All Tests</a>
</div>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const selectedFileDiv = document.getElementById('selectedFile');
    const resultDiv = document.getElementById('result');
    
    dropZone.onclick = () => fileInput.click();
    
    dropZone.ondragover = (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    };
    
    dropZone.ondragleave = () => {
        dropZone.classList.remove('drag-over');
    };
    
    dropZone.ondrop = (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file && (file.name.endsWith('.txt') || file.name.endsWith('.pdf'))) {
            fileInput.files = e.dataTransfer.files;
            selectedFileDiv.innerHTML = '✅ Selected: ' + file.name;
            resultDiv.innerHTML = '';
        } else {
            alert('Please upload a .txt or .pdf file');
        }
    };
    
    fileInput.onchange = () => {
        if (fileInput.files[0]) {
            selectedFileDiv.innerHTML = '✅ Selected: ' + fileInput.files[0].name;
            resultDiv.innerHTML = '';
        }
    };
    
    async function uploadFile() {
        const file = fileInput.files[0];
        if (!file) {
            alert('Please select a file first');
            return;
        }
        
        resultDiv.innerHTML = '<div class="result loading">⏳ Processing file, please wait...</div>';
        
        const formData = new FormData();
        formData.append('file', file);
        
        try {
            const response = await fetch('process_pdf.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="result success">
                        ✅ Test Created Successfully!<br>
                        📝 Test Name: ${data.test_name}<br>
                        ❓ Questions: ${data.questions_count}<br>
                        🆔 Test ID: ${data.test_id}<br>
                        <a href="../dashboard.php" style="color: #155724;">👉 Go to Student Dashboard</a>
                    </div>
                `;
                selectedFileDiv.innerHTML = '';
                fileInput.value = '';
            } else {
                resultDiv.innerHTML = `<div class="result error">❌ ${data.message}</div>`;
            }
        } catch (error) {
            resultDiv.innerHTML = `<div class="result error">❌ Error: ${error.message}</div>`;
        }
    }
</script>
</body>
</html>