@extends('layouts.app')

@section('title', 'Scan RFID')
@section('page-title', 'Scan Kartu RFID')
@section('page-subtitle', 'Tap kartu RFID pada reader untuk scan')

@section('styles')
<style>
    .scan-container {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .scan-card {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .scan-icon {
        font-size: 120px;
        color: #0d6efd;
        animation: scan-pulse 2s infinite;
    }
    
    @keyframes scan-pulse {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.5;
            transform: scale(0.95);
        }
    }
    
    #rfid-input {
        font-size: 1.5rem;
        text-align: center;
        border: 3px solid #0d6efd;
        border-radius: 10px;
        padding: 20px;
    }
    
    #rfid-input:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .result-card {
        border-radius: 15px;
        padding: 30px;
        margin-top: 30px;
        display: none;
        animation: slideDown 0.5s;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .result-success {
        background: linear-gradient(135deg, #198754 0%, #0f5132 100%);
        color: white;
    }
    
    .result-error {
        background: linear-gradient(135deg, #dc3545 0%, #a02834 100%);
        color: white;
    }
    
    .user-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    
    .location-badge {
        display: inline-block;
        padding: 10px 20px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        font-size: 1.1rem;
    }
</style>
@endsection

@section('content')
<div class="scan-container">
    <!-- Location Selection (Mode B - Dropdown) -->
    <div class="card mb-4">
        <div class="card-body">
            <label for="location-select" class="form-label fw-bold">
                <i class="bi bi-geo-alt-fill"></i> Pilih Lokasi RFID Reader:
            </label>
            <select class="form-select form-select-lg" id="location-select">
                @if($selectedLocation)
                    <option value="{{ $selectedLocation->code }}" selected>
                        {{ $selectedLocation->name }}
                    </option>
                @else
                    <option value="">-- Pilih Lokasi --</option>
                @endif
                
                @foreach($locations as $location)
                    <option value="{{ $location->code }}" 
                        {{ $selectedLocation && $selectedLocation->id == $location->id ? 'selected' : '' }}>
                        {{ $location->name }} - {{ $location->building }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">
                <i class="bi bi-info-circle"></i>
                Simulasi: Pilih lokasi dimana RFID reader "berada" secara virtual
            </small>
        </div>
    </div>

    <!-- Scan Card -->
    <div class="scan-card">
        <div class="scan-icon">
            <i class="bi bi-credit-card-2-front-fill"></i>
        </div>
        
        <h3 class="mt-4 mb-3">Siap untuk Scan</h3>
        <p class="text-muted mb-4">
            Tap kartu RFID Anda pada reader
        </p>

        <!-- Hidden RFID Input (autofocus) -->
        <input 
            type="text" 
            id="rfid-input" 
            class="form-control" 
            placeholder="Menunggu input RFID..." 
            autocomplete="off"
            autofocus>

        <div class="mt-3">
            <button class="btn btn-primary btn-lg" id="manual-scan-btn">
                <i class="bi bi-arrow-clockwise"></i> Scan Manual
            </button>
            <button class="btn btn-outline-secondary btn-lg ms-2" id="clear-btn">
                <i class="bi bi-x-circle"></i> Clear
            </button>
        </div>

        <!-- Loading Spinner -->
        <div id="loading-spinner" class="mt-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memproses scan...</p>
        </div>
    </div>

    <!-- Result Card (Success) -->
    <div id="result-success" class="result-card result-success">
        <div class="text-center">
            <img id="result-avatar" src="" alt="User Avatar" class="user-avatar mb-3">
            
            <h2 class="mb-2" id="result-name"></h2>
            <p class="mb-3" id="result-type"></p>
            
            <div class="location-badge mb-3">
                <i class="bi bi-geo-alt-fill"></i>
                <span id="result-location"></span>
            </div>
            
            <h4 id="result-message" class="mt-4 mb-3"></h4>
            
            <div class="row mt-4">
                <div class="col-6">
                    <i class="bi bi-clock-fill"></i>
                    <div class="mt-1" id="result-time"></div>
                </div>
                <div class="col-6">
                    <i class="bi bi-arrow-right-circle-fill"></i>
                    <div class="mt-1" id="result-action"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Result Card (Error) -->
    <div id="result-error" class="result-card result-error">
        <div class="text-center">
            <i class="bi bi-x-circle-fill" style="font-size: 80px;"></i>
            <h2 class="mt-3 mb-3">Akses Ditolak</h2>
            <h5 id="error-message"></h5>
            
            <div class="mt-4">
                <button class="btn btn-light btn-lg" onclick="resetScan()">
                    <i class="bi bi-arrow-clockwise"></i> Scan Lagi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // RFID Scan Handler
    let scanTimeout;
    let isSelectingLocation = false;
    
    $(document).ready(function() {
        // Auto-focus pada input RFID dengan delay untuk memberikan waktu user melihat halaman
        setTimeout(function() {
            $('#rfid-input').focus();
        }, 500);

        // Listener untuk input RFID (RFID reader mengirim data + Enter)
        $('#rfid-input').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                processScan();
            }
        });

        // Manual scan button
        $('#manual-scan-btn').click(function() {
            processScan();
        });

        // Clear button
        $('#clear-btn').click(function() {
            resetScan();
        });

        // Location select - track when user is interacting with dropdown
        $('#location-select').on('mousedown focus', function() {
            isSelectingLocation = true;
        });
        
        $('#location-select').on('blur change', function() {
            // Delay reset flag agar dropdown punya waktu untuk menutup dengan sempurna
            setTimeout(function() {
                isSelectingLocation = false;
            }, 300);
            
            // Log lokasi yang dipilih
            const selectedLocation = $('#location-select').find('option:selected').text();
            if (selectedLocation) {
                console.log('Lokasi dipilih: ' + selectedLocation);
            }
        });

        // Auto-focus kembali ke input setelah beberapa detik (hanya jika tidak sedang memilih lokasi)
        setInterval(function() {
            // Jangan auto-focus jika:
            // 1. Input sudah focus
            // 2. User sedang memilih lokasi
            // 3. Loading sedang tampil
            if (!$('#rfid-input').is(':focus') && 
                !isSelectingLocation && 
                !$('#location-select').is(':focus') && 
                $('#loading-spinner').is(':hidden') &&
                $('#result-success').is(':hidden') &&
                $('#result-error').is(':hidden')) {
                $('#rfid-input').focus();
            }
        }, 5000); // Perpanjang interval menjadi 5 detik agar lebih nyaman
    });

    function processScan() {
        const uid = $('#rfid-input').val().trim();
        const location = $('#location-select').val();

        // Validasi
        if (!uid) {
            alert('UID RFID tidak boleh kosong!');
            return;
        }

        if (!location) {
            alert('Pilih lokasi terlebih dahulu!');
            $('#location-select').focus();
            return;
        }

        // Show loading
        $('#loading-spinner').show();
        $('#result-success, #result-error').hide();

        // AJAX request
        $.ajax({
            url: '{{ route("rfid.scan.process") }}',
            method: 'POST',
            data: {
                uid: uid,
                location: location,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Scan berhasil:', response);
                showSuccessResult(response);
            },
            error: function(xhr) {
                console.error('Scan gagal:', xhr);
                showErrorResult(xhr);
            },
            complete: function() {
                $('#loading-spinner').hide();
                $('#rfid-input').val('');
                
                // Auto-reset setelah 5 detik
                setTimeout(function() {
                    resetScan();
                }, 5000);
            }
        });
    }

    function showSuccessResult(response) {
        const data = response.data;
        
        // Update result card
        $('#result-avatar').attr('src', data.user.photo);
        $('#result-name').text(data.user.name);
        $('#result-type').text(data.user.type);
        $('#result-location').text(data.location.full_name);
        $('#result-message').text(response.message);
        $('#result-time').text(data.tracking.scanned_at);
        $('#result-action').text(data.tracking.action_name);
        
        // Show success card
        $('#result-success').fadeIn();
        
        // Play success sound (optional)
        playSound('success');
    }

    function showErrorResult(xhr) {
        let message = 'Terjadi kesalahan';
        
        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }
        
        $('#error-message').text(message);
        $('#result-error').fadeIn();
        
        // Play error sound (optional)
        playSound('error');
    }

    function resetScan() {
        $('#result-success, #result-error').fadeOut();
        $('#rfid-input').val('').focus();
    }

    function playSound(type) {
        // Optional: Add audio feedback
        // const audio = new Audio(type === 'success' ? '/sounds/success.mp3' : '/sounds/error.mp3');
        // audio.play();
    }

    // Keyboard shortcut: Esc untuk reset
    $(document).keyup(function(e) {
        if (e.key === "Escape") {
            resetScan();
        }
    });
</script>
@endsection
