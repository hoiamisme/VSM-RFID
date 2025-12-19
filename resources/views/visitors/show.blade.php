@extends('layouts.app')

@section('title', 'Detail Pengunjung')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Detail Pengunjung</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('visitors.index') }}">Pengunjung</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('visitors.edit', $visitor->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('visitors.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    @if($visitor->photo)
                        <img src="{{ Storage::url($visitor->photo) }}" alt="{{ $visitor->name }}" 
                             class="rounded-circle mb-3" width="150" height="150" style="object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 150px; height: 150px; font-size: 3rem;">
                            {{ strtoupper(substr($visitor->name, 0, 1)) }}
                        </div>
                    @endif
                    
                    <h4 class="mb-1">{{ $visitor->name }}</h4>
                    <span class="badge bg-{{ $visitor->user_type == 'employee' ? 'primary' : 'info' }} mb-3">
                        {{ $visitor->user_type == 'employee' ? 'Pegawai' : 'Tamu' }}
                    </span>
                    
                    @if($visitor->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-danger">Nonaktif</span>
                    @endif

                    <hr class="my-3">

                    <div class="text-start">
                        <p class="mb-2">
                            <i class="bi bi-envelope text-muted"></i>
                            <span class="ms-2">{{ $visitor->email }}</span>
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-telephone text-muted"></i>
                            <span class="ms-2">{{ $visitor->phone ?? '-' }}</span>
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-geo-alt text-muted"></i>
                            <span class="ms-2">{{ $visitor->address ?? '-' }}</span>
                        </p>
                        @if($visitor->user_type == 'employee')
                            <p class="mb-2">
                                <i class="bi bi-badge-tm text-muted"></i>
                                <span class="ms-2">{{ $visitor->employee_id ?? '-' }}</span>
                            </p>
                        @else
                            <p class="mb-2">
                                <i class="bi bi-building text-muted"></i>
                                <span class="ms-2">{{ $visitor->institution ?? '-' }}</span>
                            </p>
                        @endif
                    </div>

                    <hr class="my-3">

                    <div class="text-muted small">
                        <p class="mb-1">Terdaftar: {{ $visitor->created_at->format('d M Y H:i') }}</p>
                        <p class="mb-0">Update: {{ $visitor->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details & Stats -->
        <div class="col-md-8">
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small">Total Scan</div>
                                    <h3 class="mb-0">{{ $stats['total_scans'] }}</h3>
                                </div>
                                <i class="bi bi-upc-scan fs-2 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small">Diterima</div>
                                    <h3 class="mb-0">{{ $stats['accepted_scans'] }}</h3>
                                </div>
                                <i class="bi bi-check-circle fs-2 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small">Ditolak</div>
                                    <h3 class="mb-0">{{ $stats['denied_scans'] }}</h3>
                                </div>
                                <i class="bi bi-x-circle fs-2 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small">Lokasi</div>
                                    <h3 class="mb-0">{{ $stats['unique_locations'] }}</h3>
                                </div>
                                <i class="bi bi-geo-alt fs-2 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Status -->
            @if($stats['current_location'])
            <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-person-check-fill fs-4 me-3"></i>
                <div>
                    <strong>Sedang Berada:</strong> {{ $stats['current_location']->name }}
                    <br>
                    <small class="text-muted">Masuk {{ $stats['last_entry']->scanned_at->diffForHumans() }}</small>
                </div>
            </div>
            @else
            <div class="alert alert-secondary d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-door-open fs-4 me-3"></i>
                <div>
                    <strong>Status:</strong> Tidak ada di lokasi manapun
                </div>
            </div>
            @endif

            <!-- RFID Cards -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card-2-front"></i> Kartu RFID
                    </h5>
                </div>
                <div class="card-body">
                    @if($visitor->rfidCards->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>UID</th>
                                        <th>No. Kartu</th>
                                        <th>Status</th>
                                        <th>Terdaftar</th>
                                        <th>Kadaluarsa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($visitor->rfidCards as $card)
                                    <tr>
                                        <td><code>{{ $card->uid }}</code></td>
                                        <td>{{ $card->card_number }}</td>
                                        <td>
                                            @if($card->status == 'active')
                                                <span class="badge bg-success">Aktif</span>
                                            @elseif($card->status == 'inactive')
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            @elseif($card->status == 'blocked')
                                                <span class="badge bg-danger">Diblokir</span>
                                            @else
                                                <span class="badge bg-warning">Hilang</span>
                                            @endif
                                        </td>
                                        <td>{{ $card->registered_at->format('d M Y') }}</td>
                                        <td>{{ $card->expired_at ? $card->expired_at->format('d M Y') : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Belum ada kartu RFID terdaftar.</p>
                    @endif
                </div>
            </div>

            <!-- Access Rights -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-key"></i> Hak Akses Lokasi
                    </h5>
                </div>
                <div class="card-body">
                    @if($visitor->accessRights->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Lokasi</th>
                                        <th>Akses</th>
                                        <th>Tipe</th>
                                        <th>Berlaku</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($visitor->accessRights as $access)
                                    <tr>
                                        <td>{{ $access->location->name }}</td>
                                        <td>
                                            @if($access->can_access)
                                                <span class="badge bg-success">Boleh</span>
                                            @else
                                                <span class="badge bg-danger">Tidak</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $access->access_type == 'permanent' ? 'primary' : 'warning' }}">
                                                {{ ucfirst($access->access_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($access->valid_from && $access->valid_until)
                                                {{ $access->valid_from->format('d M Y') }} - {{ $access->valid_until->format('d M Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Belum ada hak akses diberikan.</p>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Aktivitas Terakhir
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentActivity->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Lokasi</th>
                                        <th>Aksi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentActivity as $log)
                                    <tr>
                                        <td>{{ $log->scanned_at->format('d M Y H:i:s') }}</td>
                                        <td>{{ $log->location->name }}</td>
                                        <td>
                                            @if($log->action_type == 'entry')
                                                <span class="badge bg-success">Masuk</span>
                                            @elseif($log->action_type == 'exit')
                                                <span class="badge bg-warning">Keluar</span>
                                            @elseif($log->action_type == 'move')
                                                <span class="badge bg-info">Pindah</span>
                                            @else
                                                <span class="badge bg-danger">Ditolak</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->status == 'accepted')
                                                <i class="bi bi-check-circle text-success"></i> Diterima
                                            @else
                                                <i class="bi bi-x-circle text-danger"></i> Ditolak
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('visitors.index') }}" class="btn btn-sm btn-outline-primary">
                                Lihat Semua Aktivitas
                            </a>
                        </div>
                    @else
                        <p class="text-muted mb-0">Belum ada aktivitas tercatat.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
