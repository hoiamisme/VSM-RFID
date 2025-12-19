@extends('layouts.app')

@section('title', 'Check-in Tamu di Pos Utama')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Header -->
            <div class="mb-4 text-center">
                <h2><i class="bi bi-door-open"></i> Pos Utama - Check-in & Check-out</h2>
                <p class="text-muted">Kelola kedatangan dan kepulangan tamu</p>
            </div>

            <!-- Tab Switcher -->
            <ul class="nav nav-pills nav-fill mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="checkin-tab" data-bs-toggle="pill" 
                            data-bs-target="#checkin-panel" type="button" role="tab">
                        <i class="bi bi-box-arrow-in-right"></i> Check-in (Masuk)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="checkout-tab" data-bs-toggle="pill" 
                            data-bs-target="#checkout-panel" type="button" role="tab">
                        <i class="bi bi-box-arrow-left"></i> Check-out (Keluar)
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
            <!-- CHECK-IN PANEL -->
            <div class="tab-pane fade show active" id="checkin-panel" role="tabpanel">
            <div class="row">
                <!-- Left: Scan RFID -->
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-credit-card-2-front"></i> 1. Scan RFID Tamu</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Instruksi: Minta tamu untuk tap kartu RFID
                            </div>

                            <div class="mb-3">
                                <label for="rfid_uid_scan" class="form-label fw-bold">UID Kartu RFID</label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="rfid_uid_scan" 
                                       placeholder="Tap kartu RFID di sini..." 
                                       autofocus>
                            </div>

                            <div id="scan-result"></div>
                        </div>
                    </div>
                </div>

                <!-- Right: Info Tamu & Akses -->
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4" id="visitor-info-card" style="display: none;">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-person-check"></i> 2. Data Tamu</h5>
                        </div>
                        <div class="card-body">
                            <div id="visitor-details"></div>

                            <hr>

                            <h6 class="mb-3"><i class="bi bi-building"></i> 3. Pilih Gedung Tujuan</h6>
                            <form id="access-form">
                                <input type="hidden" id="visitor_id" name="visitor_id">
                                
                                @foreach(\App\Models\Location::where('is_active', true)->where('code', '!=', 'MAIN')->get() as $location)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="access_locations[]" 
                                           value="{{ $location->id }}" id="check_location_{{ $location->id }}">
                                    <label class="form-check-label" for="check_location_{{ $location->id }}">
                                        <strong>{{ $location->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $location->building }} - {{ $location->floor }}</small>
                                    </label>
                                </div>
                                @endforeach

                                <button type="submit" class="btn btn-success w-100 mt-3">
                                    <i class="bi bi-check-circle"></i> Berikan Akses & Check-in
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Check-in Success -->
                    <div class="card shadow-sm border-success" id="checkin-success" style="display: none;">
                        <div class="card-body text-center">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                            <h4 class="text-success mt-3">Check-in Berhasil!</h4>
                            <p class="text-muted">Tamu sudah terdaftar dan boleh masuk ke gedung yang dipilih</p>
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="bi bi-arrow-repeat"></i> Scan Tamu Berikutnya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <!-- END CHECK-IN PANEL -->

            <!-- CHECK-OUT PANEL -->
            <div class="tab-pane fade" id="checkout-panel" role="tabpanel">
            <div class="row">
                <!-- Left: Scan RFID untuk Check-out -->
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-credit-card-2-front"></i> 1. Scan RFID Tamu</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="bi bi-info-circle"></i> Instruksi: Minta tamu untuk tap kartu RFID saat keluar
                            </div>

                            <div class="mb-3">
                                <label for="rfid_uid_checkout" class="form-label fw-bold">UID Kartu RFID</label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="rfid_uid_checkout" 
                                       placeholder="Tap kartu RFID di sini...">
                            </div>

                            <div id="checkout-scan-result"></div>
                        </div>
                    </div>
                </div>

                <!-- Right: Info Tamu untuk Check-out -->
                <div class="col-md-6">
                    <div class="card shadow-sm mb-4" id="visitor-checkout-card" style="display: none;">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-person-x"></i> 2. Konfirmasi Check-out</h5>
                        </div>
                        <div class="card-body">
                            <div id="visitor-checkout-details"></div>

                            <form id="checkout-form">
                                <input type="hidden" id="checkout_visitor_id" name="visitor_id">
                                
                                <button type="submit" class="btn btn-danger w-100 mt-3">
                                    <i class="bi bi-box-arrow-left"></i> Proses Check-out
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Check-out Success -->
                    <div class="card shadow-sm border-danger" id="checkout-success" style="display: none;">
                        <div class="card-body text-center">
                            <i class="bi bi-check-circle-fill text-danger" style="font-size: 4rem;"></i>
                            <h4 class="text-danger mt-3">Check-out Berhasil!</h4>
                            <p class="text-muted">Tamu sudah tercatat keluar dari UNHAN</p>
                            <button class="btn btn-outline-danger" onclick="resetCheckoutForm()">
                                <i class="bi bi-arrow-repeat"></i> Check-out Tamu Lain
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <!-- END CHECK-OUT PANEL -->
            </div>

            <!-- Recent Check-ins -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Check-in Hari Ini</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Nama</th>
                                    <th>Institusi</th>
                                    <th>Gedung Tujuan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="checkin-list">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Loading...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    console.log('Check-in page loaded, jQuery version:', $.fn.jquery);
    let currentVisitorId = null;

    // Autofocus RFID input
    $('#rfid_uid_scan').focus();

    // Scan RFID
    $('#rfid_uid_scan').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            const uid = $(this).val().trim();
            
            if (uid) {
                scanRFID(uid);
            }
        }
    });

    function scanRFID(uid) {
        console.log('Scanning UID:', uid);
        $('#scan-result').html('<div class="alert alert-info">Memproses scan...</div>');
        
        $.ajax({
            url: '/api/checkin/check-visitor',
            method: 'POST',
            data: JSON.stringify({ uid: uid }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    currentVisitorId = response.data.user.id;
                    showVisitorInfo(response.data);
                    $('#rfid_uid_scan').val('');
                } else {
                    $('#scan-result').html(
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-x-circle"></i> ' + response.message +
                        '<br><small>UID: ' + uid + '</small>' +
                        '</div>'
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                let errorMsg = 'Kartu RFID tidak terdaftar. Silakan daftarkan tamu terlebih dahulu.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $('#scan-result').html(
                    '<div class="alert alert-danger">' + errorMsg + '</div>'
                );
            }
        });
    }

    function showVisitorInfo(data) {
        const user = data.user;
        const html = `
            <div class="text-center mb-3">
                <img src="${user.photo || '/images/default-avatar.png'}" 
                     class="rounded-circle" width="100" height="100" style="object-fit: cover;">
            </div>
            <table class="table table-sm">
                <tr>
                    <th width="40%">Nama:</th>
                    <td><strong>${user.name}</strong></td>
                </tr>
                <tr>
                    <th>Tipe:</th>
                    <td><span class="badge bg-info">${user.type}</span></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>${user.email}</td>
                </tr>
                <tr>
                    <th>Institusi:</th>
                    <td>${user.institution || '-'}</td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>${data.is_inside ? '<span class="badge bg-warning">Sudah di dalam</span>' : '<span class="badge bg-success">Belum check-in</span>'}</td>
                </tr>
            </table>
        `;
        
        $('#visitor-details').html(html);
        $('#visitor_id').val(user.id);
        $('#visitor-info-card').show();
        $('#scan-result').html('');
        
        // Pre-check existing access
        if (data.existing_access && data.existing_access.length > 0) {
            data.existing_access.forEach(function(locId) {
                $('#check_location_' + locId).prop('checked', true);
            });
        }
    }

    // Submit access form
    $('#access-form').on('submit', function(e) {
        e.preventDefault();
        
        const selectedLocations = [];
        $('input[name="access_locations[]"]:checked').each(function() {
            selectedLocations.push($(this).val());
        });
        
        if (selectedLocations.length === 0) {
            alert('Pilih minimal 1 gedung tujuan!');
            return;
        }
        
        $.ajax({
            url: '/api/checkin/grant-access',
            method: 'POST',
            data: {
                visitor_id: currentVisitorId,
                locations: selectedLocations
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#visitor-info-card').hide();
                    $('#checkin-success').show();
                    loadRecentCheckins();
                    
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                }
            },
            error: function() {
                alert('Gagal memberikan akses. Silakan coba lagi.');
            }
        });
    });

    // Load recent check-ins
    function loadRecentCheckins() {
        $.ajax({
            url: '/api/checkin/today',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(item) {
                        html += `
                            <tr>
                                <td>${item.time}</td>
                                <td>${item.name}</td>
                                <td>${item.institution}</td>
                                <td><small>${item.locations}</small></td>
                                <td><span class="badge bg-${item.is_inside ? 'success' : 'secondary'}">${item.status}</span></td>
                            </tr>
                        `;
                    });
                    $('#checkin-list').html(html);
                } else {
                    $('#checkin-list').html('<tr><td colspan="5" class="text-center text-muted">Belum ada check-in hari ini</td></tr>');
                }
            }
        });
    }

    // Load on page load
    loadRecentCheckins();
    
    // Refresh every 30 seconds
    setInterval(loadRecentCheckins, 30000);

    // ===== CHECK-OUT FUNCTIONALITY =====
    let currentCheckoutVisitorId = null;

    // Auto-focus when switching to checkout tab
    $('#checkout-tab').on('shown.bs.tab', function() {
        $('#rfid_uid_checkout').focus();
    });

    // Re-focus checkin tab
    $('#checkin-tab').on('shown.bs.tab', function() {
        $('#rfid_uid_scan').focus();
    });

    // Scan RFID for checkout
    $('#rfid_uid_checkout').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            const uid = $(this).val().trim();
            
            if (uid) {
                scanCheckoutRFID(uid);
            }
        }
    });

    function scanCheckoutRFID(uid) {
        console.log('Scanning checkout UID:', uid);
        $('#checkout-scan-result').html('<div class="alert alert-info">Memproses scan...</div>');
        
        $.ajax({
            url: '/api/checkin/check-visitor',
            method: 'POST',
            data: JSON.stringify({ uid: uid }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Checkout Response:', response);
                if (response.success) {
                    // Check if visitor is inside
                    if (!response.data.is_inside) {
                        $('#checkout-scan-result').html(
                            '<div class="alert alert-warning">' +
                            '<i class="bi bi-exclamation-triangle"></i> Tamu ini sudah check-out atau belum check-in.' +
                            '</div>'
                        );
                        $('#rfid_uid_checkout').val('');
                        return;
                    }
                    
                    currentCheckoutVisitorId = response.data.user.id;
                    showCheckoutInfo(response.data);
                    $('#rfid_uid_checkout').val('');
                } else {
                    $('#checkout-scan-result').html(
                        '<div class="alert alert-danger">' +
                        '<i class="bi bi-x-circle"></i> ' + response.message +
                        '</div>'
                    );
                }
            },
            error: function(xhr) {
                let errorMsg = 'Kartu RFID tidak terdaftar.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $('#checkout-scan-result').html(
                    '<div class="alert alert-danger">' + errorMsg + '</div>'
                );
            }
        });
    }

    function showCheckoutInfo(data) {
        const user = data.user;
        const html = `
            <div class="text-center mb-3">
                <img src="${user.photo || '/images/default-avatar.png'}" 
                     class="rounded-circle" width="100" height="100" style="object-fit: cover;">
            </div>
            <table class="table table-sm">
                <tr>
                    <th width="40%">Nama:</th>
                    <td><strong>${user.name}</strong></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>${user.email || '-'}</td>
                </tr>
                <tr>
                    <th>Institusi:</th>
                    <td>${user.institution || '-'}</td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td><span class="badge bg-success">Sedang di dalam</span></td>
                </tr>
                <tr>
                    <th>Lokasi Terakhir:</th>
                    <td>${data.last_location || 'Pos Utama'}</td>
                </tr>
            </table>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Pastikan tamu sudah mengembalikan kartu tamu dan keluar dari area UNHAN.
            </div>
        `;
        
        $('#visitor-checkout-details').html(html);
        $('#checkout_visitor_id').val(user.id);
        
        $('#visitor-checkout-card').fadeIn();
        $('#checkout-scan-result').empty();
    }

    // Submit checkout form
    $('#checkout-form').on('submit', function(e) {
        e.preventDefault();
        
        const userId = $('#checkout_visitor_id').val();
        
        if (!userId) {
            alert('User ID tidak valid');
            return;
        }

        $.ajax({
            url: '/api/checkin/checkout',
            method: 'POST',
            data: JSON.stringify({ 
                user_id: userId 
            }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Checkout success:', response);
                if (response.success) {
                    $('#visitor-checkout-card').hide();
                    $('#checkout-success').fadeIn();
                    loadRecentCheckins(); // Refresh table
                } else {
                    alert('Gagal: ' + response.message);
                }
            },
            error: function(xhr) {
                console.error('Checkout error:', xhr);
                let errorMsg = 'Gagal melakukan check-out. Silakan coba lagi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
            }
        });
    });
});

// Global function for reset checkout
function resetCheckoutForm() {
    $('#visitor-checkout-card').hide();
    $('#checkout-success').hide();
    $('#rfid_uid_checkout').val('').focus();
}
</script>
@endsection
