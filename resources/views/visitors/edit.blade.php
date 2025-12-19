@extends('layouts.app')

@section('title', 'Edit Pengunjung')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Edit Pengunjung</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('visitors.index') }}">Pengunjung</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('visitors.show', $visitor->id) }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <!-- Form Card -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('visitors.update', $visitor->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Tipe User -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Tipe Pengguna <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="user_type" id="type_guest" value="guest" 
                                       {{ old('user_type', $visitor->user_type) == 'guest' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="type_guest">
                                    <i class="bi bi-person"></i> Tamu
                                </label>

                                <input type="radio" class="btn-check" name="user_type" id="type_employee" value="employee"
                                       {{ old('user_type', $visitor->user_type) == 'employee' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success" for="type_employee">
                                    <i class="bi bi-briefcase"></i> Pegawai
                                </label>

                                <input type="radio" class="btn-check" name="user_type" id="type_kadet" value="kadet"
                                       {{ old('user_type', $visitor->user_type) == 'kadet' ? 'checked' : '' }}>
                                <label class="btn btn-outline-info" for="type_kadet">
                                    <i class="bi bi-mortarboard"></i> Kadet
                                </label>
                            </div>
                            @error('user_type')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Data Diri -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $visitor->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $visitor->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">No. Telepon</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $visitor->phone) }}" 
                                       placeholder="08xxxxxxxxxx">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Alamat</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       id="address" name="address" value="{{ old('address', $visitor->address) }}">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Field untuk Tamu -->
                            <div class="col-md-12 mb-3 guest-field" style="display: {{ old('user_type', $visitor->user_type) == 'guest' ? 'block' : 'none' }}">
                                <label for="institution" class="form-label">Asal Institusi</label>
                                <input type="text" class="form-control @error('institution') is-invalid @enderror" 
                                       id="institution" name="institution" value="{{ old('institution', $visitor->institution) }}" 
                                       placeholder="Contoh: Universitas Indonesia">
                                @error('institution')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Field untuk Pegawai -->
                            <div class="col-md-6 mb-3 employee-field" style="display: {{ old('user_type', $visitor->user_type) == 'employee' ? 'block' : 'none' }}">
                                <label for="employee_id" class="form-label">ID Pegawai <span class="text-danger employee-required">*</span></label>
                                <input type="text" class="form-control @error('employee_id') is-invalid @enderror" 
                                       id="employee_id" name="employee_id" value="{{ old('employee_id', $visitor->employee_id) }}" 
                                       placeholder="Contoh: EMP001">
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="photo" class="form-label">Foto</label>
                                @if($visitor->photo)
                                    <div class="mb-2">
                                        <img src="{{ Storage::url($visitor->photo) }}" alt="Current Photo" 
                                             class="rounded" width="100">
                                        <small class="text-muted d-block">Foto saat ini</small>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                       id="photo" name="photo" accept="image/*">
                                <small class="text-muted">Upload foto baru jika ingin mengubah. Format: JPG, PNG (Max: 2MB)</small>
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                           value="1" {{ old('is_active', $visitor->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Status Aktif
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('visitors.show', $visitor->id) }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle fields based on user type
    function toggleFields() {
        const userType = $('input[name="user_type"]:checked').val();
        
        if (userType === 'guest') {
            $('.guest-field').show();
            $('.employee-field').hide();
            $('#institution').prop('required', false);
            $('#employee_id').prop('required', false);
        } else if (userType === 'employee') {
            $('.guest-field').hide();
            $('.employee-field').show();
            $('#institution').prop('required', false);
            $('#employee_id').prop('required', true);
        } else if (userType === 'kadet') {
            $('.guest-field').show();
            $('.employee-field').show();
            $('#institution').val('Universitas Pertahanan');
            $('#institution').prop('required', false);
            $('#employee_id').prop('required', true);
        }
    }

    // Initialize
    toggleFields();

    // Listen to changes
    $('input[name="user_type"]').on('change', toggleFields);

    // Preview photo
    $('#photo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = `<div class="mt-2"><img src="${e.target.result}" class="rounded" width="100"><small class="text-muted d-block">Preview foto baru</small></div>`;
                $('#photo').after(preview);
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush
@endsection
