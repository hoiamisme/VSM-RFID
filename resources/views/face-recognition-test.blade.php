<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Recognition Test - VMS UNHAN</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background: #f8f9fa;
        }
        .test-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🧪 Face Recognition Test Suite</h1>
        
        <!-- Backend Tests -->
        <div class="test-section">
            <h3>🔧 Backend Tests</h3>
            
            <div class="mb-3">
                <h5>1. Database Schema Check</h5>
                <button class="btn btn-primary btn-sm" onclick="testDatabaseSchema()">Run Test</button>
                <div id="db-result" class="mt-2"></div>
            </div>
            
            <div class="mb-3">
                <h5>2. API Routes Check</h5>
                <button class="btn btn-primary btn-sm" onclick="testApiRoutes()">Run Test</button>
                <div id="api-result" class="mt-2"></div>
            </div>
            
            <div class="mb-3">
                <h5>3. Controller Methods Check</h5>
                <button class="btn btn-primary btn-sm" onclick="testController()">Run Test</button>
                <div id="controller-result" class="mt-2"></div>
            </div>
        </div>
        
        <!-- Frontend Tests -->
        <div class="test-section">
            <h3>🎨 Frontend Tests</h3>
            
            <div class="mb-3">
                <h5>4. Models Loading Check</h5>
                <button class="btn btn-primary btn-sm" onclick="testModelsLoading()">Run Test</button>
                <div id="models-result" class="mt-2"></div>
            </div>
            
            <div class="mb-3">
                <h5>5. FaceRecognition Class Check</h5>
                <button class="btn btn-primary btn-sm" onclick="testFaceRecognitionClass()">Run Test</button>
                <div id="class-result" class="mt-2"></div>
            </div>
            
            <div class="mb-3">
                <h5>6. Webcam Support Check</h5>
                <button class="btn btn-primary btn-sm" onclick="testWebcamSupport()">Run Test</button>
                <div id="webcam-result" class="mt-2"></div>
            </div>
        </div>
        
        <!-- Run All Tests -->
        <div class="test-section">
            <h3>🚀 Run All Tests</h3>
            <button class="btn btn-success btn-lg" onclick="runAllTests()">Run All Tests</button>
            <div id="summary-result" class="mt-3"></div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        let testResults = {
            passed: 0,
            failed: 0,
            total: 0
        };
        
        function showResult(elementId, success, message, details = null) {
            const statusClass = success ? 'status-success' : 'status-error';
            const icon = success ? '✅' : '❌';
            let html = `<span class="status-badge ${statusClass}">${icon} ${message}</span>`;
            if (details) {
                html += `<pre class="mt-2">${JSON.stringify(details, null, 2)}</pre>`;
            }
            document.getElementById(elementId).innerHTML = html;
            
            testResults.total++;
            if (success) testResults.passed++;
            else testResults.failed++;
        }
        
        async function testDatabaseSchema() {
            try {
                const response = await fetch('/api/face/stats');
                const data = await response.json();
                showResult('db-result', true, 'Database schema OK', data);
            } catch (error) {
                showResult('db-result', false, 'Database schema ERROR', error.message);
            }
        }
        
        async function testApiRoutes() {
            const routes = [
                { method: 'POST', path: '/api/face/verify', name: 'verify' },
                { method: 'POST', path: '/api/face/enroll', name: 'enroll' },
                { method: 'GET', path: '/api/face/stats', name: 'stats' }
            ];
            
            let allOk = true;
            let results = [];
            
            for (const route of routes) {
                try {
                    const response = await fetch(route.path, {
                        method: route.method === 'GET' ? 'GET' : 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    
                    // Status 200 atau 422 (validation error) berarti route exists
                    const routeExists = response.status === 200 || response.status === 422;
                    results.push({
                        route: route.path,
                        status: response.status,
                        exists: routeExists
                    });
                    
                    if (!routeExists) allOk = false;
                } catch (error) {
                    allOk = false;
                    results.push({
                        route: route.path,
                        error: error.message
                    });
                }
            }
            
            showResult('api-result', allOk, allOk ? 'All API routes OK' : 'Some routes FAILED', results);
        }
        
        async function testController() {
            try {
                const response = await fetch('/api/face/stats');
                const data = await response.json();
                const hasExpectedFields = data.hasOwnProperty('total_users') && 
                                         data.hasOwnProperty('users_with_face');
                showResult('controller-result', hasExpectedFields, 
                          hasExpectedFields ? 'Controller OK' : 'Controller response incomplete', 
                          data);
            } catch (error) {
                showResult('controller-result', false, 'Controller ERROR', error.message);
            }
        }
        
        async function testModelsLoading() {
            const modelFiles = [
                '/models/tiny_face_detector_model-weights_manifest.json',
                '/models/face_landmark_68_model-weights_manifest.json',
                '/models/face_recognition_model-weights_manifest.json'
            ];
            
            let allOk = true;
            let results = [];
            
            for (const file of modelFiles) {
                try {
                    const response = await fetch(file);
                    const exists = response.ok;
                    results.push({
                        file: file,
                        status: response.status,
                        exists: exists
                    });
                    if (!exists) allOk = false;
                } catch (error) {
                    allOk = false;
                    results.push({
                        file: file,
                        error: error.message
                    });
                }
            }
            
            showResult('models-result', allOk, allOk ? 'All models accessible' : 'Some models MISSING', results);
        }
        
        function testFaceRecognitionClass() {
            try {
                const classExists = typeof FaceRecognition !== 'undefined';
                if (classExists) {
                    const instance = new FaceRecognition();
                    const methods = ['loadModels', 'startWebcam', 'detectFace', 'verifyFace', 'getDescriptor'];
                    const allMethodsExist = methods.every(m => typeof instance[m] === 'function');
                    
                    showResult('class-result', allMethodsExist, 
                              allMethodsExist ? 'FaceRecognition class OK' : 'Some methods missing',
                              { methods: methods, available: methods.filter(m => typeof instance[m] === 'function') });
                } else {
                    showResult('class-result', false, 'FaceRecognition class NOT FOUND', 
                              'Make sure to run: npm run build');
                }
            } catch (error) {
                showResult('class-result', false, 'Class test ERROR', error.message);
            }
        }
        
        function testWebcamSupport() {
            const supported = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
            showResult('webcam-result', supported, 
                      supported ? 'Webcam API supported' : 'Webcam API NOT supported',
                      { 
                          mediaDevices: !!navigator.mediaDevices,
                          getUserMedia: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia)
                      });
        }
        
        async function runAllTests() {
            testResults = { passed: 0, failed: 0, total: 0 };
            
            document.getElementById('summary-result').innerHTML = 
                '<div class="status-badge status-pending">⏳ Running tests...</div>';
            
            await testDatabaseSchema();
            await testApiRoutes();
            await testController();
            await testModelsLoading();
            testFaceRecognitionClass();
            testWebcamSupport();
            
            setTimeout(() => {
                const allPassed = testResults.failed === 0;
                const statusClass = allPassed ? 'status-success' : 'status-error';
                const icon = allPassed ? '🎉' : '⚠️';
                
                document.getElementById('summary-result').innerHTML = `
                    <div class="status-badge ${statusClass}">
                        ${icon} Tests Complete: ${testResults.passed}/${testResults.total} passed
                        ${testResults.failed > 0 ? `| ${testResults.failed} failed` : ''}
                    </div>
                    <div class="mt-3">
                        <h5>Summary:</h5>
                        <ul>
                            <li>Total Tests: ${testResults.total}</li>
                            <li>Passed: ${testResults.passed}</li>
                            <li>Failed: ${testResults.failed}</li>
                            <li>Success Rate: ${Math.round((testResults.passed/testResults.total)*100)}%</li>
                        </ul>
                    </div>
                `;
            }, 2000);
        }
    </script>
</body>
</html>
