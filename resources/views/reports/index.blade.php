@extends('layouts.app')

@section('title', 'Laporan Tracking')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-bar-graph"></i> Laporan Tracking Tamu</h5>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ route('reports.index') }}" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Dari Tanggal</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $dateTo }}">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="location_code" class="form-label">Lokasi</label>
                            <select class="form-select" id="location_code" name="location_code">
                                <option value="">Semua Lokasi</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->code }}" {{ $locationCode == $location->code ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                        </div>
                    </form>

                    <!-- Statistics Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <h3 class="text-primary mb-0">{{ $stats['total_entries'] }}</h3>
                                    <small class="text-muted">Total Masuk</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card border-danger">
                                <div class="card-body text-center">
                                    <h3 class="text-danger mb-0">{{ $stats['total_exits'] }}</h3>
                                    <small class="text-muted">Total Keluar</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <h3 class="text-success mb-0">{{ $stats['currently_inside'] }}</h3>
                                    <small class="text-muted">Saat Ini Di Dalam</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <h3 class="text-info mb-0">{{ $stats['unique_visitors'] }}</h3>
                                    <small class="text-muted">Tamu Unik</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Buttons -->
                    @if($logs->count() > 0)
                    <div class="mb-3">
                        <div class="btn-group" role="group">
                            <form method="GET" action="{{ route('reports.export') }}" class="d-inline">
                                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                <input type="hidden" name="location_code" value="{{ $locationCode }}">
                                <input type="hidden" name="format" value="excel">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                                </button>
                            </form>
                            
                            <form method="GET" action="{{ route('reports.export') }}" class="d-inline">
                                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                <input type="hidden" name="location_code" value="{{ $locationCode }}">
                                <input type="hidden" name="format" value="pdf">
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                                </button>
                            </form>
                            
                            <form method="GET" action="{{ route('reports.export') }}" class="d-inline">
                                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                                <input type="hidden" name="date_to" value="{{ $dateTo }}">
                                <input type="hidden" name="location_code" value="{{ $locationCode }}">
                                <input type="hidden" name="format" value="csv">
                                <button type="submit" class="btn btn-info">
                                    <i class="bi bi-filetype-csv"></i> Export CSV
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Location Occupancy -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Okupansi Lokasi Saat Ini</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($locationOccupancy as $occupancy)
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $occupancy['location']->name }}</h6>
                                    <div class="progress mb-2" style="height: 25px;">
                                        <div class="progress-bar 
                                            @if($occupancy['percentage'] >= 90) bg-danger
                                            @elseif($occupancy['percentage'] >= 70) bg-warning
                                            @else bg-success
                                            @endif" 
                                            role="progressbar" 
                                            style="width: {{ $occupancy['percentage'] }}%"
                                            aria-valuenow="{{ $occupancy['percentage'] }}" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100">
                                            {{ $occupancy['percentage'] }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        {{ $occupancy['current_count'] }} / {{ $occupancy['capacity'] }} orang
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tracking Logs Table -->
            @if($logs->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Detail Log Tracking</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Nama Tamu</th>
                                    <th>Institusi</th>
                                    <th>Lokasi</th>
                                    <th>Aksi</th>
                                    <th>Status</th>
                                    <th>RFID UID</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $log->user->name }}</td>
                                    <td>{{ $log->user->institution }}</td>
                                    <td>{{ $log->location->name }}</td>
                                    <td>
                                        @if($log->action_type === 'entry')
                                            <span class="badge bg-success">Masuk</span>
                                        @else
                                            <span class="badge bg-danger">Keluar</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->status === 'accepted')
                                            <span class="badge bg-success">Diterima</span>
                                        @elseif($log->status === 'rejected')
                                            <span class="badge bg-danger">Ditolak</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($log->status) }}</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $log->rfid_uid }}</code></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Tidak ada data untuk periode yang dipilih. Silakan pilih rentang tanggal untuk melihat laporan.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
