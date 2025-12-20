@extends('layouts.app')

@section('title', 'Devices - ' . $organization['name'])

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

    .page-subtitle {
        font-size: 0.9rem;
        color: var(--text-muted, #666);
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

    .btn-add {
        background-color: var(--accent-color, #198754);
        color: #fff;
        border: none;
        padding: 8px 16px;
        text-transform: uppercase;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
    }
    .btn-add:hover {
        background-color: var(--accent-hover, #157347);
    }

    /* Cards */
    .device-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .device-card {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #badbcc);
        border-radius: 8px;
        padding: 20px;
        position: relative;
    }

    .device-card.inactive {
        opacity: 0.6;
    }

    .device-status {
        position: absolute;
        top: 15px;
        right: 15px;
    }

    .device-icon {
        font-size: 2rem;
        color: var(--accent-color, #198754);
        margin-bottom: 10px;
    }

    .device-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 5px;
    }

    .device-id {
        font-family: monospace;
        background: #f5f5f5;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        margin-bottom: 10px;
        display: inline-block;
    }

    .device-location {
        font-size: 0.9rem;
        color: var(--text-muted, #666);
        margin-bottom: 10px;
    }

    .device-last-seen {
        font-size: 0.8rem;
        color: var(--text-muted, #999);
    }

    .device-actions {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid var(--border-color, #badbcc);
        display: flex;
        gap: 10px;
    }

    .badge-success { background: #d1e7dd; color: #0f5132; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; }
    .badge-danger { background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; }

    /* Add Device Form */
    .add-device-form {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #badbcc);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        display: none;
    }

    .add-device-form.show {
        display: block;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        font-size: 0.85rem;
    }

    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--border-color, #badbcc);
        border-radius: 6px;
        font-size: 0.9rem;
    }

    .empty-state {
        text-align: center;
        padding: 60px;
        color: #666;
    }
    .empty-state i {
        font-size: 3rem;
        opacity: 0.3;
        margin-bottom: 15px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-phone me-2"></i>Devices
        </h1>
        <p class="page-subtitle">{{ $organization['name'] }}</p>
    </div>
    <div>
        <a href="{{ route('organizations.show', $organization['id']) }}" class="btn-back">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        <button type="button" class="btn-add" onclick="document.getElementById('addDeviceForm').classList.toggle('show')">
            <i class="bi bi-plus-circle me-1"></i>ADD DEVICE
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; padding:10px; border-radius:6px; margin-bottom:15px;">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:10px; border-radius:6px; margin-bottom:15px;">
        {{ session('error') }}
    </div>
@endif

<!-- Add Device Form -->
<div id="addDeviceForm" class="add-device-form">
    <h4 style="margin-bottom:15px; font-weight:600;">Add New Device</h4>
    <form action="{{ route('organizations.devices.store', $organization['id']) }}" method="POST">
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="device_id">Device ID *</label>
                <input type="text" class="form-control" id="device_id" name="device_id" required
                       placeholder="e.g., main_gate_tablet">
            </div>
            <div class="form-group">
                <label class="form-label" for="device_name">Device Name *</label>
                <input type="text" class="form-control" id="device_name" name="device_name" required
                       placeholder="e.g., Main Gate Tablet">
            </div>
            <div class="form-group">
                <label class="form-label" for="location">Location</label>
                <input type="text" class="form-control" id="location" name="location"
                       placeholder="e.g., Main Entrance">
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:15px;">
            <label style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" name="is_active" value="1" checked>
                <span>Active</span>
            </label>
            <button type="submit" class="btn-add">
                <i class="bi bi-plus me-1"></i>Add Device
            </button>
        </div>
    </form>
</div>

<!-- Device Stats -->
<div style="background:var(--bg-card, #fff); padding:15px 20px; border-radius:8px; border:1px solid var(--border-color, #badbcc); margin-bottom:20px;">
    <strong>{{ count($devices) }}</strong> devices registered 
    <span style="color:#999;">|</span> 
    <strong>{{ collect($devices)->where('is_active', true)->count() }}</strong> active
    <span style="color:#999;">|</span>
    Limit: <strong>{{ $organization['max_devices'] ?? 5 }}</strong>
</div>

<!-- Devices Grid -->
@if(count($devices) > 0)
<div class="device-grid">
    @foreach($devices as $device)
    <div class="device-card {{ $device['is_active'] ? '' : 'inactive' }}">
        <div class="device-status">
            @if($device['is_active'])
                <span class="badge-success">Active</span>
            @else
                <span class="badge-danger">Inactive</span>
            @endif
        </div>
        
        <div class="device-icon">
            <i class="bi bi-tablet"></i>
        </div>
        
        <div class="device-name">{{ $device['device_name'] }}</div>
        <div class="device-id">{{ $device['device_id'] }}</div>
        
        @if($device['location'])
            <div class="device-location">
                <i class="bi bi-geo-alt me-1"></i>{{ $device['location'] }}
            </div>
        @endif
        
        <div class="device-last-seen">
            @if($device['last_seen'])
                Last seen: {{ date('M d, Y H:i', strtotime($device['last_seen'])) }}
            @else
                Never connected
            @endif
        </div>

        <div class="device-actions">
            <form action="{{ route('organizations.devices.update', [$organization['id'], $device['id']]) }}" method="POST" style="display:inline;">
                @csrf
                @method('PUT')
                <input type="hidden" name="device_name" value="{{ $device['device_name'] }}">
                <input type="hidden" name="location" value="{{ $device['location'] }}">
                @if($device['is_active'])
                    <button type="submit" class="btn-add" style="background:#ffc107; font-size:0.75rem; padding:5px 10px;">
                        Deactivate
                    </button>
                @else
                    <input type="hidden" name="is_active" value="1">
                    <button type="submit" class="btn-add" style="font-size:0.75rem; padding:5px 10px;">
                        Activate
                    </button>
                @endif
            </form>
            
            <form action="{{ route('organizations.devices.destroy', [$organization['id'], $device['id']]) }}" method="POST" 
                  style="display:inline;" onsubmit="return confirm('Delete this device?');">
                @csrf
                @method('DELETE')
                <button type="submit" style="background:#dc3545; color:#fff; border:none; padding:5px 10px; border-radius:4px; font-size:0.75rem; cursor:pointer;">
                    Delete
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="empty-state">
    <i class="bi bi-phone"></i>
    <h3>No Devices</h3>
    <p>This organization has no registered devices yet.</p>
    <button type="button" class="btn-add" onclick="document.getElementById('addDeviceForm').classList.add('show')">
        Add your first device
    </button>
</div>
@endif

<!-- Instructions -->
<div style="background:#f8f9fa; padding:20px; border-radius:8px; margin-top:30px; border:1px solid #e9ecef;">
    <h4 style="margin-bottom:10px; font-weight:600;">
        <i class="bi bi-info-circle me-1"></i>How Device Registration Works
    </h4>
    <ol style="margin:0; padding-left:20px; color:#666;">
        <li>Create a device here with a unique <strong>Device ID</strong> (e.g., "main_gate")</li>
        <li>In the Android APK settings, set the <strong>Device Name</strong> to match this Device ID</li>
        <li>When the APK sends attendance data, it will automatically be linked to this organization</li>
        <li>The "Last Seen" timestamp updates each time the device communicates with the server</li>
    </ol>
</div>
@endsection
