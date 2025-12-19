@extends('layouts.app')

@section('title', 'Kelola Hak Akses')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-shield-check"></i> Kelola Hak Akses</h2>
                <a href="{{ route('access-rights.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Berikan Hak Akses Baru
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Filter -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('access-rights.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Filter Tamu</label>
                            <select name="user_id" class="form-select">
                                <option value="">Semua Tamu</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Filter Lokasi</label>
                            <select name="location_id" class="form-select">
                                <option value="">Semua Lokasi</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tipe Akses</label>
                            <select name="access_type" class="form-select">
                                <option value="">Semua Tipe</option>
                                <option value="permanent" {{ request('access_type') == 'permanent' ? 'selected' : '' }}>Permanen</option>
                                <option value="temporary" {{ request('access_type') == 'temporary' ? 'selected' : '' }}>Sementara</option>
                                <option value="scheduled" {{ request('access_type') == 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tamu</th>
                                    <th>Lokasi</th>
                                    <th>Tipe Akses</th>
                                    <th>Berlaku</th>
                                    <th>Status</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accessRights as $access)
                                <tr>
                                    <td>
                                        <strong>{{ $access->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $access->user->institution }}</small>
                                    </td>
                                    <td>{{ $access->location->name }}</td>
                                    <td>
                                        @if($access->access_type === 'permanent')
                                            <span class="badge bg-success">Permanen</span>
                                        @elseif($access->access_type === 'temporary')
                                            <span class="badge bg-warning text-dark">Sementara</span>
                                        @else
                                            <span class="badge bg-info">Terjadwal</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            {{ $access->valid_from ? $access->valid_from->format('d/m/Y') : '-' }}
                                            @if($access->valid_until)
                                                <br>s/d {{ $access->valid_until->format('d/m/Y') }}
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        @if($access->can_access && $access->isValidAt(now()))
                                            <span class="badge bg-success">Aktif</span>
                                        @elseif($access->isExpired())
                                            <span class="badge bg-danger">Kadaluarsa</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('access-rights.destroy', $access) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Yakin ingin mencabut hak akses ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Cabut Akses">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p>Belum ada data hak akses</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $accessRights->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
