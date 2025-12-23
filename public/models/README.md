# Face-API.js Models Download

Untuk mengaktifkan face recognition, Anda perlu mendownload pre-trained models dari face-api.js.

## Cara Download Models:

### Option 1: Download Manual

1. Buka: https://github.com/justadudewhohacks/face-api.js/tree/master/weights

2. Download file-file berikut ke folder `public/models/`:

**Tiny Face Detector (Required):**
- `tiny_face_detector_model-weights_manifest.json`
- `tiny_face_detector_model-shard1`

**Face Landmark 68 (Required):**
- `face_landmark_68_model-weights_manifest.json`
- `face_landmark_68_model-shard1`
- `face_landmark_68_model-shard2`

**Face Recognition (Required):**
- `face_recognition_model-weights_manifest.json`
- `face_recognition_model-shard1`
- `face_recognition_model-shard2`

**Total size: ~7.2 MB**

### Option 2: Using Git Clone

```bash
cd public/models
git clone --depth 1 https://github.com/justadudewhohacks/face-api.js-models.git temp
cp temp/tiny_face_detector/* .
cp temp/face_landmark_68/* .
cp temp/face_recognition/* .
rm -rf temp
```

### Option 3: Using PowerShell Script

```powershell
# Run this from project root
powershell -ExecutionPolicy Bypass -File download-models.ps1
```

## Verifikasi:

Setelah download, struktur folder harus seperti ini:

```
public/models/
├── tiny_face_detector_model-weights_manifest.json
├── tiny_face_detector_model-shard1
├── face_landmark_68_model-weights_manifest.json
├── face_landmark_68_model-shard1
├── face_landmark_68_model-shard2
├── face_recognition_model-weights_manifest.json
├── face_recognition_model-shard1
└── face_recognition_model-shard2
```

## Testing:

Buka browser console di halaman scan RFID, jalankan:

```javascript
await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
console.log('Models loaded successfully!');
```

Jika tidak ada error, models sudah siap digunakan!
