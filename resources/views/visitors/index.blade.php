@extends('layouts.app')

@section('title', 'Daftar Pengunjung')
@section('page-title', 'Daftar Pengunjung')
@section('page-subtitle', 'Manage data tamu dan pegawai')

@section('content')
<div class="container-fluid">
    <!-- Filter & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('visitors.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Cari nama, email, atau telepon..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="guest" {{ request('type') == 'guest' ? 'selected' : '' }}>Tamu</option>
                        <option value="employee" {{ request('type') == 'employee' ? 'selected' : '' }}>Pegawai</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="is_active" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-3">
        <a href="{{ route('visitors.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Pengunjung Baru
        </a>
        <a href="#" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </a>
        <a href="#" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
    </div>

    <!-- Visitors Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Email / Telepon</th>
                        <th>Tipe</th>
                        <th>Kartu RFID</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visitors as $visitor)
                    <tr>
                        <td>{{ $loop->iteration + $visitors->firstItem() - 1 }}</td>
                        <td>
                            <img src="{{ $visitor->photo_url }}" 
                                 alt="{{ $visitor->name }}" 
                                 class="rounded-circle" 
                                 style="width: 40px; height: 40px; object-fit: cover;">
                        </td>
                        <td>
                            <strong>{{ $visitor->name }}</strong>
                            @if($visitor->employee_id)
                            <br><small class="text-muted">NIP: {{ $visitor->employee_id }}</small>
                            @endif
                        </td>
                        <td>
                            <i class="bi bi-envelope"></i> {{ $visitor->email }}<br>
                            @if($visitor->phone)
                            <i class="bi bi-telephone"></i> {{ $visitor->phone }}
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $visitor->user_type == 'guest' ? 'info' : 'success' }}">
                                {{ $visitor->user_type_name }}
                            </span>
                        </td>
                        <td>
                            @if($visitor->rfidCards->count() > 0)
                                @foreach($visitor->rfidCards as $card)
                                <span class="badge bg-{{ $card->status_color }} d-block mb-1">
                                    {{ $card->uid }}
                                </span>
                                @endforeach
                            @else
                                <span class="text-muted">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            @if($visitor->is_active)
                            <span class="badge bg-success">Aktif</span>
                            @else
                            <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('visitors.show', $visitor->id) }}" 
                                   class="btn btn-info" 
                                   title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('visitors.edit', $visitor->id) }}" 
                                   class="btn btn-warning" 
                                   title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-danger" 
                                        title="Hapus"
                                        onclick="confirmDelete({{ $visitor->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-2">Tidak ada data pengunjung</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Menampilkan {{ $visitors->firstItem() ?? 0 }} - {{ $visitors->lastItem() ?? 0 }} 
                dari {{ $visitors->total() }} data
            </div>
            <div>
                {{ $visitors->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            const form = document.getElementById('delete-form');
            form.action = '/visitors/' + id;
            form.submit();
        }
    }
</script>
@endsection
