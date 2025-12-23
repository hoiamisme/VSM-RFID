@extends('layouts.app')

@section('title', 'Registrasi Pengunjung Baru')
@section('page-title', 'Registrasi Pengunjung Baru')
@section('page-subtitle', 'Tambah data tamu atau pegawai baru')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('visitors.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- User Type Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Tipe Pengguna *</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="user_type" id="type-guest" value="guest" checked>
                                <label class="btn btn-outline-primary" for="type-guest">
                                    <i class="bi bi-person"></i> Tamu
                                </label>

                                <input type="radio" class="btn-check" name="user_type" id="type-employee" value="employee">
                                <label class="btn btn-outline-success" for="type-employee">
                                    <i class="bi bi-briefcase"></i> Pegawai
                                </label>

                                <input type="radio" class="btn-check" name="user_type" id="type-kadet" value="kadet">
                                <label class="btn btn-outline-info" for="type-kadet">
                                    <i class="bi bi-mortarboard"></i> Kadet
                                </label>
                            </div>
                        </div>

                        <hr>

                        <!-- Photo Upload -->
                        <div class="mb-3">
                            <label for="photo" class="form-label">Foto</label>
                            <input type="file" 
                                   class="form-control @error('photo') is-invalid @enderror" 
                                   id="photo" 
                                   name="photo" 
                                   accept="image/jpeg,image/jpg,image/png">
                            <small class="text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                            @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap *</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="text" 
                                   class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}">
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" 
                                      name="address" 
                                      rows="3">{{ old('address') }}</textarea>
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Institution (for guest) -->
                        <div class="mb-3" id="institution-field">
                            <label for="institution" class="form-label">Institusi/Instansi Asal</label>
                            <input type="text" 
                                   class="form-control @error('institution') is-invalid @enderror" 
                                   id="institution" 
                                   name="institution" 
                                   value="{{ old('institution') }}">
                            <small class="text-muted">Untuk tamu: nama institusi/perusahaan</small>
                            @error('institution')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Employee ID (for employee) -->
                        <div class="mb-3" id="employee-id-field" style="display: none;">
                            <label for="employee_id" class="form-label">NIP/NIK</label>
                            <input type="text" 
                                   class="form-control @error('employee_id') is-invalid @enderror" 
                                   id="employee_id" 
                                   name="employee_id" 
                                   value="{{ old('employee_id') }}">
                            <small class="text-muted">Nomor Induk Pegawai</small>
                            @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        <h5 class="mb-3"><i class="bi bi-key"></i> Hak Akses Lokasi</h5>
                        
                        <!-- Access Rights (for guest) -->
                        <div class="mb-3" id="access-rights-field" style="display: block;">
                            <label class="form-label">Pilih Gedung/Lokasi yang Boleh Diakses</label>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Penjaga: Centang lokasi yang boleh dikunjungi tamu
                            </div>
                            
                            @foreach(\App\Models\Location::where('is_active', true)->get() as $location)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="access_locations[]" 
                                       value="{{ $location->id }}" id="location_{{ $location->id }}"
                                       {{ in_array($location->id, old('access_locations', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="location_{{ $location->id }}">
                                    <strong>{{ $location->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $location->building }} - {{ $location->floor }}
                                        @if($location->requires_special_access)
                                            <span class="badge bg-warning text-dark">Akses Khusus</span>
                                        @endif
                                    </small>
                                </label>
                            </div>
                            @endforeach
                            
                            <small class="text-muted">
                                <i class="bi bi-lightbulb"></i> Pegawai otomatis punya akses ke semua lokasi
                            </small>
                        </div>

                        <hr>
                        <h5 class="mb-3"><i class="bi bi-credit-card-2-front"></i> Data Kartu RFID</h5>

                        <!-- RFID UID -->
                        <div class="mb-3">
                            <label for="rfid_uid" class="form-label">UID Kartu RFID</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control @error('rfid_uid') is-invalid @enderror" 
                                       id="rfid_uid" 
                                       name="rfid_uid" 
                                       value="{{ old('rfid_uid') }}"
                                       placeholder="Tap kartu pada reader...">
                                <button class="btn btn-outline-secondary" type="button" id="scan-rfid-btn">
                                    <i class="bi bi-credit-card-2-front"></i> Scan
                                </button>
                            </div>
                            <small class="text-muted">Opsional: Bisa didaftarkan nanti</small>
                            @error('rfid_uid')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- RFID Card Number -->
                        <div class="mb-3">
                            <label for="rfid_card_number" class="form-label">Nomor Kartu Fisik</label>
                            <input type="text" 
                                   class="form-control @error('rfid_card_number') is-invalid @enderror" 
                                   id="rfid_card_number" 
                                   name="rfid_card_number" 
                                   value="{{ old('rfid_card_number') }}"
                                   placeholder="Nomor yang tercetak di kartu">
                            @error('rfid_card_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        <h5 class="mb-3"><i class="bi bi-person-bounding-box"></i> Face Recognition (Opsional)</h5>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Daftarkan wajah untuk keamanan tambahan. User dengan face recognition akan diminta verifikasi wajah saat scan RFID.
                        </div>

                        <!-- Face Enrollment Section -->
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white">
                                            <i class="bi bi-camera-video"></i> Webcam
                                        </div>
                                        <div class="card-body text-center">
                                            <video id="enrollment-video" width="320" height="240" autoplay style="border: 2px solid #ddd; border-radius: 8px; background: #000;"></video>
                                            <canvas id="enrollment-canvas" style="display: none;"></canvas>
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-success" id="btn-start-camera">
                                                    <i class="bi bi-camera"></i> Aktifkan Kamera
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" id="btn-stop-camera" style="display: none;">
                                                    <i class="bi bi-stop-circle"></i> Matikan Kamera
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <i class="bi bi-check-circle"></i> Wajah Terdaftar
                                        </div>
                                        <div class="card-body text-center">
                                            <div id="face-preview" style="min-height: 240px; display: flex; align-items: center; justify-content: center; border: 2px dashed #ddd; border-radius: 8px; background: #f8f9fa;">
                                                <p class="text-muted mb-0">
                                                    <i class="bi bi-person-x" style="font-size: 48px;"></i><br>
                                                    Belum ada wajah terdaftar
                                                </p>
                                            </div>
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-primary" id="btn-capture-face" disabled>
                                                    <i class="bi bi-camera-fill"></i> Capture Wajah
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" id="btn-clear-face" style="display: none;">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Face Status -->
                            <div class="mt-3">
                                <div id="face-enrollment-status" class="alert alert-secondary" style="display: none;">
                                    <i class="bi bi-info-circle"></i> <span id="face-status-text">Memuat...</span>
                                </div>
                            </div>

                            <!-- Hidden field for face descriptor -->
                            <input type="hidden" id="face_descriptor" name="face_descriptor" value="">
                            
                            <!-- Require face verification checkbox -->
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="require_face_verification" name="require_face_verification" value="1">
                                <label class="form-check-label" for="require_face_verification">
                                    <strong>Wajibkan Verifikasi Wajah</strong>
                                    <br>
                                    <small class="text-muted">Jika dicentang, user harus verifikasi wajah setiap scan RFID</small>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('visitors.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Toggle fields based on user type
        $('input[name="user_type"]').change(function() {
            var userType = $(this).val();
            
            if (userType === 'guest') {
                $('#institution-field').show();
                $('#employee-id-field').hide();
                $('#access-rights-field').show();
                $('#employee_id').val('');
            } else if (userType === 'employee') {
                $('#institution-field').hide();
                $('#employee-id-field').show();
                $('#access-rights-field').hide();
                $('#institution').val('');
            } else if (userType === 'kadet') {
                $('#institution-field').show();
                $('#institution').val('Universitas Pertahanan');
                $('#employee-id-field').show();
                $('#access-rights-field').show();
            }
        });

        // Initialize visibility on page load
        $('input[name="user_type"]:checked').trigger('change');

        // Scan RFID button
        $('#scan-rfid-btn').click(function() {
            $('#rfid_uid').focus();
            alert('Tap kartu RFID pada reader. UID akan otomatis terisi.');
        });

        // Auto-focus RFID input on enter
        $('#rfid_uid').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                alert('UID RFID berhasil ditangkap: ' + $(this).val());
            }
        });

        // Face Recognition Enrollment
        let faceRecognition = null;
        let enrollmentStream = null;
        let capturedDescriptor = null;

        // Start camera
        $('#btn-start-camera').click(async function() {
            try {
                showFaceStatus('Memulai kamera...', 'info');
                
                // Check if FaceRecognition is available
                if (typeof FaceRecognition === 'undefined') {
                    throw new Error('FaceRecognition module belum dimuat. Silakan refresh halaman.');
                }
                
                // Initialize FaceRecognition
                if (!faceRecognition) {
                    faceRecognition = new FaceRecognition();
                }

                // Load models
                showFaceStatus('Memuat model AI...', 'info');
                await faceRecognition.loadModels();

                // Start webcam
                showFaceStatus('Mengaktifkan kamera...', 'info');
                enrollmentStream = await faceRecognition.startWebcam(
                    document.getElementById('enrollment-video')
                );

                showFaceStatus('Kamera aktif. Posisikan wajah Anda di depan kamera.', 'success');
                
                // Update UI
                $('#btn-start-camera').hide();
                $('#btn-stop-camera').show();
                $('#btn-capture-face').prop('disabled', false);

            } catch (error) {
                console.error('Error starting camera:', error);
                showFaceStatus('Gagal memulai kamera: ' + error.message, 'danger');
            }
        });

        // Stop camera
        $('#btn-stop-camera').click(function() {
            stopCamera();
            showFaceStatus('Kamera dimatikan', 'secondary');
        });

        // Capture face
        $('#btn-capture-face').click(async function() {
            try {
                showFaceStatus('Mendeteksi wajah...', 'info');
                
                const video = document.getElementById('enrollment-video');
                const descriptor = await faceRecognition.detectFace(video);

                if (!descriptor) {
                    showFaceStatus('Wajah tidak terdeteksi. Pastikan wajah terlihat jelas.', 'warning');
                    return;
                }

                // Capture image for preview
                const canvas = document.getElementById('enrollment-canvas');
                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0);
                
                const imageData = canvas.toDataURL('image/jpeg', 0.8);

                // Show preview
                $('#face-preview').html(
                    '<img src="' + imageData + '" style="max-width: 100%; max-height: 240px; border-radius: 8px;">'
                );

                // Store descriptor
                capturedDescriptor = Array.from(descriptor);
                $('#face_descriptor').val(JSON.stringify(capturedDescriptor));

                // Enable delete button and checkbox
                $('#btn-clear-face').show();
                $('#require_face_verification').prop('disabled', false);

                showFaceStatus('Wajah berhasil didaftarkan! ' + capturedDescriptor.length + ' fitur wajah terdeteksi.', 'success');

                // Stop camera
                stopCamera();

            } catch (error) {
                console.error('Error capturing face:', error);
                showFaceStatus('Gagal capture wajah: ' + error.message, 'danger');
            }
        });

        // Clear face
        $('#btn-clear-face').click(function() {
            $('#face-preview').html(
                '<p class="text-muted mb-0">' +
                '<i class="bi bi-person-x" style="font-size: 48px;"></i><br>' +
                'Belum ada wajah terdaftar' +
                '</p>'
            );
            
            $('#face_descriptor').val('');
            capturedDescriptor = null;
            $(this).hide();
            $('#require_face_verification').prop('checked', false).prop('disabled', true);
            
            showFaceStatus('Wajah dihapus', 'secondary');
        });

        function stopCamera() {
            const video = document.getElementById('enrollment-video');
            
            // Stop all tracks from video stream
            if (video && video.srcObject) {
                const stream = video.srcObject;
                if (stream && stream.getTracks) {
                    stream.getTracks().forEach(track => track.stop());
                }
                video.srcObject = null;
            }
            
            // Also stop enrollmentStream if it exists
            if (enrollmentStream && enrollmentStream.getTracks) {
                enrollmentStream.getTracks().forEach(track => track.stop());
            }
            enrollmentStream = null;

            $('#btn-start-camera').show();
            $('#btn-stop-camera').hide();
            $('#btn-capture-face').prop('disabled', true);
        }

        function showFaceStatus(message, type) {
            const $status = $('#face-enrollment-status');
            const $text = $('#face-status-text');
            
            $status.removeClass('alert-info alert-success alert-warning alert-danger alert-secondary');
            $status.addClass('alert-' + type);
            $text.text(message);
            $status.show();
        }

        // Disable face verification checkbox by default
        $('#require_face_verification').prop('disabled', true);

        // Form validation before submit
        $('form').on('submit', function(e) {
            if ($('#require_face_verification').is(':checked') && !capturedDescriptor) {
                e.preventDefault();
                alert('Anda mengaktifkan verifikasi wajah tapi belum mendaftarkan wajah. Silakan capture wajah terlebih dahulu atau uncheck opsi verifikasi wajah.');
                return false;
            }
        });
    });
</script>
@endsection
