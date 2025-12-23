# PowerShell script to download face-api.js models
# Run this from project root

Write-Host "Downloading face-api.js models..." -ForegroundColor Cyan

$modelsPath = "public/models"
$baseUrl = "https://raw.githubusercontent.com/justadudewhohacks/face-api.js-models/master"

$files = @(
    "tiny_face_detector/tiny_face_detector_model-weights_manifest.json",
    "tiny_face_detector/tiny_face_detector_model-shard1",
    "face_landmark_68/face_landmark_68_model-weights_manifest.json",
    "face_landmark_68/face_landmark_68_model-shard1",
    "face_landmark_68/face_landmark_68_model-shard2",
    "face_recognition/face_recognition_model-weights_manifest.json",
    "face_recognition/face_recognition_model-shard1",
    "face_recognition/face_recognition_model-shard2"
)

if (!(Test-Path $modelsPath)) {
    New-Item -ItemType Directory -Path $modelsPath | Out-Null
}

$downloaded = 0
foreach ($file in $files) {
    $fileName = Split-Path $file -Leaf
    $url = "$baseUrl/$file"
    $destination = Join-Path $modelsPath $fileName
    
    try {
        Write-Host "Downloading: $fileName" -NoNewline
        Invoke-WebRequest -Uri $url -OutFile $destination -UseBasicParsing
        Write-Host " - OK" -ForegroundColor Green
        $downloaded++
    }
    catch {
        Write-Host " - FAILED" -ForegroundColor Red
    }
}

Write-Host "Downloaded $downloaded files" -ForegroundColor Green
