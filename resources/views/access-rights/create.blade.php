@extends('layouts.app')

@section('title', 'Berikan Hak Akses')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="mb-4">
                <a href="{{ route('access-rights.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Berikan Hak Akses Baru</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('access-rights.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="user_id" class="form-label">Pilih Tamu <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" 
                                    id="user_id" 
                                    name="user_id" 
                                    required>
                                <option value="">-- Pilih Tamu --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} - {{ $user->institution }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pilih Lokasi yang Diizinkan <span class="text-danger">*</span></label>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                @foreach($locations as $location)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="location_ids[]" 
                                           value="{{ $location->id }}" 
                                           id="location_{{ $location->id }}"
                                           {{ in_array($location->id, old('location_ids', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="location_{{ $location->id }}">
                                        <strong>{{ $location->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $location->building }} - {{ $location->floor }}</small>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @error('location_ids')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="access_type" class="form-label">Tipe Akses <span class="text-danger">*</span></label>
                            <select class="form-select @error('access_type') is-invalid @enderror" 
                                    id="access_type" 
                                    name="access_type" 
                                    required>
                                <option value="permanent" {{ old('access_type') == 'permanent' ? 'selected' : '' }}>
                                    Permanen (Tidak ada batas waktu)
                                </option>
                                <option value="temporary" {{ old('access_type', 'temporary') == 'temporary' ? 'selected' : '' }}>
                                    Sementara (Ada batas waktu)
                                </option>
                                <option value="scheduled" {{ old('access_type') == 'scheduled' ? 'selected' : '' }}>
                                    Terjadwal (Sesuai jadwal tertentu)
                                </option>
                            </select>
                            @error('access_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row" id="date-range">
                            <div class="col-md-6 mb-3">
                                <label for="valid_from" class="form-label">Berlaku Dari</label>
                                <input type="date" 
                                       class="form-control @error('valid_from') is-invalid @enderror" 
                                       id="valid_from" 
                                       name="valid_from" 
                                       value="{{ old('valid_from', date('Y-m-d')) }}">
                                @error('valid_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="valid_until" class="form-label">Berlaku Sampai</label>
                                <input type="date" 
                                       class="form-control @error('valid_until') is-invalid @enderror" 
                                       id="valid_until" 
                                       name="valid_until" 
                                       value="{{ old('valid_until') }}">
                                @error('valid_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Kosongkan untuk akses permanen</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Alasan Pemberian Akses</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                      id="reason" 
                                      name="reason" 
                                      rows="3"
                                      placeholder="Jelaskan alasan pemberian hak akses ini">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('access-rights.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Berikan Hak Akses
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('access_type').addEventListener('change', function() {
    const dateRange = document.getElementById('date-range');
    if (this.value === 'permanent') {
        dateRange.style.display = 'none';
    } else {
        dateRange.style.display = 'flex';
    }
});

// Initial check
document.addEventListener('DOMContentLoaded', function() {
    const accessType = document.getElementById('access_type');
    if (accessType.value === 'permanent') {
        document.getElementById('date-range').style.display = 'none';
    }
});
</script>
@endsection
