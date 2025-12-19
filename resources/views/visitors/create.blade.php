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
    });
</script>
@endsection
