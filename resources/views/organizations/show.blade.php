@extends('layouts.app')

@section('title', $organization['name'])

@push('head')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        margin-bottom: 20px;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-main);
    }

    .btn-back {
        background-color: #6c757d;
        color: #fff;
        border: none;
        padding: 8px 16px;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
        margin-right: 10px;
    }
    .btn-back:hover {
        background-color: #5a6268;
        color: #fff;
    }

    .btn-edit {
        background-color: var(--accent-color, #198754);
        color: #fff;
        border: none;
        padding: 8px 16px;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
    }
    .btn-edit:hover {
        background-color: var(--accent-hover, #157347);
        color: #fff;
    }

    /* Info Cards */
    .info-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-card {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #badbcc);
        border-radius: 8px;
        padding: 20px;
    }

    .info-card h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color, #badbcc);
    }

    .info-row {
        display: flex;
        margin-bottom: 10px;
    }

    .info-label {
        width: 140px;
        font-weight: 600;
        color: var(--text-muted, #666);
        font-size: 0.9rem;
    }

    .info-value {
        flex: 1;
        color: var(--text-main);
    }

    /* Stats */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #badbcc);
        border-radius: 8px;
        padding: 20px;
        text-align: center;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--accent-color, #198754);
    }

    .stat-label {
        font-size: 0.8rem;
        color: var(--text-muted, #666);
        text-transform: uppercase;
    }

    .stat-limit {
        font-size: 0.75rem;
        color: #999;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .status-active { background: #d1e7dd; color: #0f5132; }
    .status-inactive { background: #f8d7da; color: #721c24; }

    /* Devices Section */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .device-list {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #badbcc);
        border-radius: 8px;
    }

    .device-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color, #badbcc);
    }

    .device-item:last-child {
        border-bottom: none;
    }

    .device-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .device-icon {
        font-size: 1.5rem;
        color: var(--accent-color, #198754);
    }

    .device-name {
        font-weight: 600;
    }

    .device-id-text {
        font-family: monospace;
        font-size: 0.85rem;
        color: #666;
    }

    .device-status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .device-active { background: #d1e7dd; color: #0f5132; }
    .device-inactive { background: #f8d7da; color: #721c24; }

    .empty-devices {
        padding: 40px;
        text-align: center;
        color: #666;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-building me-2"></i>{{ $organization['name'] }}
        @if($organization['is_active'])
            <span class="status-badge status-active" style="margin-left:10px; font-size:0.8rem;">Active</span>
        @else
            <span class="status-badge status-inactive" style="margin-left:10px; font-size:0.8rem;">Inactive</span>
        @endif
    </h1>
    <div>
        <a href="{{ route('organizations.index') }}" class="btn-back">
            <i class="bi bi-arrow-left me-1"></i>Back to List
        </a>
        <a href="{{ route('organizations.edit', $organization['id']) }}" class="btn-edit">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['employees']['total'] ?? $organization['employee_count'] ?? 0 }}</div>
        <div class="stat-label">Employees</div>
        <div class="stat-limit">Limit: {{ $organization['max_employees'] ?? 100 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['devices']['total'] ?? $organization['device_count'] ?? 0 }}</div>
        <div class="stat-label">Devices</div>
        <div class="stat-limit">Limit: {{ $organization['max_devices'] ?? 5 }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['attendance']['today'] ?? 0 }}</div>
        <div class="stat-label">Today's Attendance</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['attendance']['this_month'] ?? 0 }}</div>
        <div class="stat-label">This Month</div>
    </div>
</div>

<!-- Info Cards -->
<div class="info-grid">
    <div class="info-card">
        <h3><i class="bi bi-info-circle me-2"></i>Organization Details</h3>
        
        <div class="info-row">
            <div class="info-label">Slug</div>
            <div class="info-value"><code>{{ $organization['slug'] }}</code></div>
        </div>

        <div class="info-row">
            <div class="info-label">Email</div>
            <div class="info-value">{{ $organization['email'] ?: '—' }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Phone</div>
            <div class="info-value">{{ $organization['phone'] ?: '—' }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Address</div>
            <div class="info-value">{{ $organization['address'] ?: '—' }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Created</div>
            <div class="info-value">{{ date('F d, Y', strtotime($organization['created_at'])) }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Last Updated</div>
            <div class="info-value">{{ date('F d, Y H:i', strtotime($organization['updated_at'])) }}</div>
        </div>
    </div>

    <div class="info-card">
        <h3><i class="bi bi-gear me-2"></i>Limits & Usage</h3>
        
        <div class="info-row">
            <div class="info-label">Employees</div>
            <div class="info-value">
                {{ $stats['employees']['active'] ?? 0 }} active / {{ $organization['max_employees'] ?? 100 }} max
            </div>
        </div>

        <div class="info-row">
            <div class="info-label">Devices</div>
            <div class="info-value">
                {{ $stats['devices']['active'] ?? 0 }} active / {{ $organization['max_devices'] ?? 5 }} max
            </div>
        </div>
    </div>
</div>

<!-- Devices Section -->
<div class="section-header">
    <h3 class="section-title"><i class="bi bi-phone me-2"></i>Registered Devices</h3>
    <a href="{{ route('organizations.devices', $organization['id']) }}" class="btn-edit" style="font-size:0.8rem;">
        Manage Devices
    </a>
</div>

<div class="device-list">
    @forelse($devices as $device)
    <div class="device-item">
        <div class="device-info">
            <div class="device-icon">
                <i class="bi bi-tablet"></i>
            </div>
            <div>
                <div class="device-name">{{ $device['device_name'] }}</div>
                <div class="device-id-text">{{ $device['device_id'] }}</div>
            </div>
        </div>
        <div>
            @if($device['location'])
                <span style="margin-right:15px; color:#666; font-size:0.85rem;">
                    <i class="bi bi-geo-alt"></i> {{ $device['location'] }}
                </span>
            @endif
            @if($device['is_active'])
                <span class="device-status device-active">Active</span>
            @else
                <span class="device-status device-inactive">Inactive</span>
            @endif
        </div>
    </div>
    @empty
    <div class="empty-devices">
        <i class="bi bi-phone" style="font-size:2rem; opacity:0.3;"></i>
        <p>No devices registered yet</p>
        <a href="{{ route('organizations.devices', $organization['id']) }}">Add devices</a>
    </div>
    @endforelse
</div>

<!-- Quick Actions -->
<div style="margin-top:30px; padding:20px; background:#f8f9fa; border-radius:8px; border:1px solid #e9ecef;">
    <h4 style="margin-bottom:15px; font-weight:600;">Quick Actions</h4>
    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a href="{{ route('organizations.devices', $organization['id']) }}" class="btn-edit">
            <i class="bi bi-phone me-1"></i>Manage Devices
        </a>
        <a href="{{ route('organizations.edit', $organization['id']) }}" class="btn-back">
            <i class="bi bi-pencil me-1"></i>Edit Company
        </a>
        <form action="{{ route('organizations.destroy', $organization['id']) }}" method="POST" style="display:inline;"
              onsubmit="return confirm('Are you sure you want to delete this organization? This will delete all associated employees, attendance records, and devices!');">
            @csrf
            @method('DELETE')
            <button type="submit" style="background:#dc3545; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-weight:600; cursor:pointer;">
                <i class="bi bi-trash me-1"></i>Delete
            </button>
        </form>
    </div>
</div>
@endsection
