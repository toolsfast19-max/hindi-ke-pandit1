<?php
session_start();
require_once '../config/database.php';

// Fetch all tests
$tests_query = "SELECT * FROM tests ORDER BY created_at DESC";
$tests_result = mysqli_query($conn, $tests_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Test System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f7fafc; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header h1 { color: #667eea; }
        .btn-logout { padding: 10px 20px; background: #e53e3e; color: white; text-decoration: none; border-radius: 5px; }
        .section { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section h2 { margin-bottom: 20px; color: #333; }
        .btn-add { display: inline-block; padding: 12px 24px; background: #48bb78; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-bottom: 20px; }
        .btn-add:hover { background: #38a169; }
        
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 10px; width: 90%; max-width: 550px; max-height: 80vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .close { font-size: 24px; cursor: pointer; color: #999; }
        .close:hover { color: #333; }
        .upload-area { border: 2px dashed #ccc; padding: 40px; text-align: center; margin: 20px 0; cursor: pointer; border-radius: 10px; transition: all 0.3s; }
        .upload-area.drag-over { background: #e3f2fd; border-color: #2196F3; }
        .selected-file { margin: 10px 0; color: #2196F3; font-weight: bold; }
        .btn-upload { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .btn-upload:hover { background: #5a67d8; }
        .result { margin-top: 15px; padding: 10px; border-radius: 5px; display: none; }
        .result.success { background: #d4edda; color: #155724; display: block; }
        .result.error { background: #f8d7da; color: #721c24; display: block; }
        .result.loading { background: #e3f2fd; color: #0c5460; display: block; }
        .format-example { background: #f5f5f5; padding: 10px; border-radius: 5px; font-size: 12px; margin-top: 10px; }
        .format-example pre { margin: 5px 0; font-family: monospace; white-space: pre-wrap; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f7fafc; font-weight: 600; }
        .btn-edit, .btn-delete { padding: 5px 10px; border-radius: 3px; text-decoration: none; font-size: 12px; margin-right: 5px; display: inline-block; }
        .btn-edit { background: #4299e1; color: white; }
        .btn-delete { background: #e53e3e; color: white; }
        .no-tests { text-align: center; padding: 40px; color: #718096; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div>
            <h1>📚 Admin Dashboard</h1>
            <p>Manage Tests and Questions</p>
        </div>
        <a href="../logout.php" class="btn-logout">🚪 Logout</a>
    </div>
    
    <div class="section">
        <h2>📋 Available Tests</h2>
        <button onclick="openModal()" class="btn-add">+ Add New Test (Upload PDF/TXT)</button>
        
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Test Name</th>
                        <th>Description</th>
                        <th>Duration</th>
                        <th>Questions</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($tests_result) > 0): ?>
                        <?php while ($test = mysqli_fetch_assoc($tests_result)): ?>
                        <tr>
                            <td><?php echo $test['id']; ?></td>
                            <td><?php echo htmlspecialchars($test['title']); ?></td>
                            <td><?php echo htmlspecialchars($test['description']); ?></td>
                            <td><?php echo $test['duration']; ?> min</td>
                            <td><?php echo $test['total_questions']; ?></td>
                            <td><?php echo date('d M Y', strtotime($test['created_at'])); ?></td>
                            <td>
                                <a href="questions.php?test_id=<?php echo $test['id']; ?>" class="btn-edit">Questions</a>
                                <a href="delete_test.php?id=<?php echo $test['id']; ?>" class="btn-delete" onclick="return confirm('Delete this test?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-tests">No tests available. Click "Add New Test" to create one.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>📄 Upload File to Create Test</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        
        <p>Upload a PDF or TXT file with MCQs in this format:</p>
        <div class="format-example">
            <pre>
1. What is the capital of India?
A. Mumbai
B. New Delhi
C. Kolkata
D. Chennai
Answer: B

2. What is 2 + 2?
A. 3
B. 4
C. 5
D. 6
Answer: B
            </pre>
        </div>
        
        <div class="upload-area" id="dropZone">
            <p>📁 Drag & Drop file here or click to select</p>
            <input type="file" id="fileInput" accept=".txt,.pdf" style="display: none;">
        </div>
        
        <div id="selectedFile" class="selected-file"></div>
        
        <button onclick="uploadFile()" class="btn-upload">🚀 Upload & Create Test</button>
        
        <div id="uploadResult" class="result"></div>
    </div>
</div>

<script>
    const modal = document.getElementById('uploadModal');
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const selectedFileDiv = document.getElementById('selectedFile');
    const resultDiv = document.getElementById('uploadResult');
    
    function openModal() {
        modal.style.display = 'flex';
        resetForm();
    }
    
    function closeModal() {
        modal.style.display = 'none';
        resetForm();
    }
    
    function resetForm() {
        fileInput.value = '';
        selectedFileDiv.innerHTML = '';
        resultDiv.innerHTML = '';
        resultDiv.className = 'result';
    }
    
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
        
        resultDiv.className = 'result loading';
        resultDiv.innerHTML = '⏳ Processing file, please wait...';
        
        const formData = new FormData();
        formData.append('file', file);
        
        try {
            const response = await fetch('process_upload.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                resultDiv.className = 'result success';
                resultDiv.innerHTML = `
                    ✅ Test Created Successfully!<br>
                    📝 Test Name: ${data.test_name}<br>
                    ❓ Questions: ${data.questions_count}<br>
                    🆔 Test ID: ${data.test_id}<br>
                    <br>
                    <button onclick="closeModal(); location.reload();" style="background:#48bb78; color:white; padding:8px 15px; border:none; border-radius:5px; cursor:pointer; margin-top:10px;">Refresh Page</button>
                `;
                selectedFileDiv.innerHTML = '';
                fileInput.value = '';
            } else {
                resultDiv.className = 'result error';
                resultDiv.innerHTML = '❌ ' + data.message;
            }
        } catch (error) {
            resultDiv.className = 'result error';
            resultDiv.innerHTML = '❌ Error: ' + error.message;
        }
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
</body>
</html>