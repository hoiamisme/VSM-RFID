@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Monitoring')
@section('page-subtitle', 'Realtime Visitor Management System')

@section('styles')
<style>
    .refresh-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
    }
    
    .visitor-card {
        transition: all 0.3s;
    }
    
    .visitor-card:hover {
        transform: translateX(5px);
        background-color: #f8f9fa;
    }
    
    .occupancy-bar {
        height: 30px;
        border-radius: 15px;
        overflow: hidden;
        background-color: #e9ecef;
    }
    
    .activity-item {
        border-left: 3px solid #0d6efd;
        padding-left: 15px;
        margin-bottom: 15px;
    }
    
    .activity-time {
        font-size: 0.85rem;
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card card h-100 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50 mb-1">Pengunjung Aktif</h6>
                            <h2 class="mb-0">{{ $stats['currently_inside'] }}</h2>
                            <small>Sedang berada di lokasi</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-people-fill pulse"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card card h-100 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Scan Hari Ini</h6>
                            <h2 class="mb-0">{{ $stats['total_scans'] }}</h2>
                            <small>{{ $stats['acceptance_rate'] }}% diterima</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-clipboard-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card card h-100 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-white-50 mb-1">Masuk Hari Ini</h6>
                            <h2 class="mb-0">{{ $stats['today_entries'] }}</h2>
                            <small>{{ $stats['unique_visitors'] }} pengunjung unik</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card card h-100 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-dark-50 mb-1">Keluar Hari Ini</h6>
                            <h2 class="mb-0">{{ $stats['today_exits'] }}</h2>
                            <small>Scan keluar</small>
                        </div>
                        <div class="stat-icon">
                            <i class="bi bi-box-arrow-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Active Visitors -->
        <div class="col-lg-6 mb-4">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="bi bi-people-fill text-primary"></i>
                        Pengunjung Aktif
                    </h5>
                    <span class="badge bg-primary">{{ $activeVisitors->count() }} orang</span>
                </div>

                <div class="list-group" id="active-visitors-list">
                    @forelse($activeVisitors as $visitor)
                    <div class="list-group-item visitor-card">
                        <div class="d-flex align-items-center">
                            <img src="{{ $visitor->photo_url }}" 
                                 alt="{{ $visitor->name }}" 
                                 class="rounded-circle me-3" 
                                 style="width: 50px; height: 50px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $visitor->name }}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    {{ $visitor->current_location ? $visitor->current_location->name : 'Unknown' }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-clock-fill"></i>
                                    Masuk: {{ $visitor->entry_time ? $visitor->entry_time->format('H:i') : '-' }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $visitor->user_type == 'guest' ? 'info' : 'success' }}">
                                {{ $visitor->user_type_name }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">Tidak ada pengunjung aktif</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6 mb-4">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history text-primary"></i>
                        Aktivitas Terkini
                    </h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshActivity()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>

                <div id="recent-activity-list" style="max-height: 500px; overflow-y: auto;">
                    @foreach($recentActivity as $log)
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <strong>{{ $log->user->name }}</strong>
                                <span class="badge badge-status bg-{{ $log->action_color }} ms-2">
                                    {{ $log->action_type_name }}
                                </span>
                                <p class="mb-1 text-muted">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    {{ $log->location->name }}
                                </p>
                                <div class="activity-time">
                                    <i class="bi bi-clock"></i>
                                    {{ $log->scanned_at->format('H:i:s') }} - {{ $log->time_ago }}
                                </div>
                            </div>
                            <span class="badge bg-{{ $log->status_color }}">
                                {{ $log->status_name }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Location Occupancy -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="table-card">
                <h5 class="mb-4">
                    <i class="bi bi-building text-primary"></i>
                    Okupansi Lokasi
                </h5>

                <div class="row">
                    @foreach($locationOccupancy as $loc)
                    <div class="col-lg-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">{{ $loc['name'] }}</h6>
                                    <span class="badge bg-{{ $loc['status'] }}">
                                        {{ $loc['current_count'] }} / {{ $loc['capacity'] ?? '∞' }}
                                    </span>
                                </div>

                                @if($loc['capacity'])
                                <div class="occupancy-bar">
                                    <div class="progress-bar bg-{{ $loc['status'] }}" 
                                         role="progressbar" 
                                         style="width: {{ min($loc['percentage'], 100) }}%"
                                         aria-valuenow="{{ $loc['percentage'] }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ round($loc['percentage']) }}%
                                    </div>
                                </div>
                                @else
                                <small class="text-muted">Tanpa batas kapasitas</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row">
        <div class="col-12">
            <div class="table-card">
                <h5 class="mb-4">
                    <i class="bi bi-bar-chart text-primary"></i>
                    Statistik Sistem
                </h5>

                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-primary mb-0">{{ $stats['total_users'] }}</h3>
                            <small class="text-muted">Total Pengguna</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-success mb-0">{{ $stats['total_guests'] }}</h3>
                            <small class="text-muted">Tamu Terdaftar</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-info mb-0">{{ $stats['total_employees'] }}</h3>
                            <small class="text-muted">Pegawai</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 bg-light rounded">
                            <h3 class="text-warning mb-0">{{ $stats['total_locations'] }}</h3>
                            <small class="text-muted">Lokasi Aktif</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-refresh dashboard setiap 30 detik
    setInterval(function() {
        refreshDashboard();
    }, 30000);

    function refreshDashboard() {
        $.ajax({
            url: '/api/dashboard/realtime',
            method: 'GET',
            success: function(data) {
                console.log('Dashboard refreshed:', data);
                // Update UI dengan data baru
                updateActiveVisitors(data.currently_inside);
                updateRecentActivity(data.recent_activity);
            },
            error: function(error) {
                console.error('Failed to refresh dashboard:', error);
            }
        });
    }

    function refreshActivity() {
        location.reload();
    }

    function updateActiveVisitors(count) {
        // Update badge count
        $('.stat-card.bg-primary h2').text(count);
    }

    function updateRecentActivity(activities) {
        // You can implement dynamic update here
        console.log('Recent activities:', activities);
    }
</script>
@endsection
