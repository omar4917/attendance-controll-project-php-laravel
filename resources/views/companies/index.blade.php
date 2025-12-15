@extends('layouts.app')

@section('title', 'Companies')

@push('head')
<style>
    /* Inherit theme variables */
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

    .btn-add {
        background-color: var(--accent-color, #198754);
        color: #fff;
        border: none;
        padding: 8px 16px;
        text-transform: uppercase;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-add:hover {
        background-color: var(--accent-hover, #157347);
        color: #fff;
    }

    /* Search/filter bar */
    .action-bar {
        background-color: var(--bg-card, #f0fdf4);
        padding: 10px 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid var(--border-color, #badbcc);
    }

    .search-box {
        background: #ffffff;
        border: 1px solid var(--border-color, #badbcc);
        color: var(--text-main);
        padding: 8px 12px;
        border-radius: 6px;
        width: 300px;
    }

    .btn-go {
        background-color: var(--accent-color, #198754);
        color: #fff;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
    }
    .btn-go:hover {
        background-color: var(--accent-hover, #157347);
    }

    /* Table */
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #badbcc);
        border-radius: 8px;
        overflow: hidden;
    }

    .admin-table thead tr {
        background-color: var(--table-header-bg, #d1e7dd);
        color: var(--table-header-text, #052c18);
        text-transform: uppercase;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .admin-table th, .admin-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid var(--border-color, #badbcc);
    }

    .admin-table tbody tr:nth-child(even) {
        background-color: var(--table-row-even, #f0fdf4);
    }

    .admin-table tbody tr:hover {
        background-color: var(--table-row-hover, #c3e6cb);
    }

    .admin-table a {
        color: var(--link-color, #198754);
        text-decoration: none;
        font-weight: 600;
    }

    .admin-table a:hover {
        text-decoration: underline;
    }

    /* Status badges */
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-success { background: #d1e7dd; color: #0f5132; }
    .badge-danger { background: #f8d7da; color: #721c24; }
    .badge-info { background: #cff4fc; color: #055160; }

    /* Icon styles */
    .icon-yes { color: #198754; font-weight: bold; }
    .icon-no { color: #dc3545; font-weight: bold; }

    /* Stats */
    .stats-row {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #badbcc);
        border-radius: 8px;
        padding: 15px 20px;
        flex: 1;
        text-align: center;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--accent-color, #198754);
    }
    .stat-label {
        font-size: 0.8rem;
        color: var(--text-muted, #666);
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-building me-2"></i>Companies (Organizations)
    </h1>
    <a href="{{ route('companies.create') }}" class="btn-add">
        <i class="bi bi-plus-circle me-1"></i>ADD COMPANY
    </a>
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
@if($error)
    <div class="alert alert-warning" style="background:#fff3cd; color:#856404; border:1px solid #ffeeba; padding:10px; border-radius:6px; margin-bottom:15px;">
        API Error: {{ $error }}
    </div>
@endif

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-value">{{ count($organizations) }}</div>
        <div class="stat-label">Total Companies</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ collect($organizations)->where('is_active', true)->count() }}</div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ collect($organizations)->sum('employee_count') }}</div>
        <div class="stat-label">Total Employees</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ collect($organizations)->sum('device_count') }}</div>
        <div class="stat-label">Total Devices</div>
    </div>
</div>

<!-- Search Bar -->
<div class="action-bar">
    <form action="{{ route('companies.index') }}" method="GET" style="display:flex; gap:10px; align-items:center;">
        <input type="text" name="search" class="search-box" placeholder="Search companies..." value="{{ request('search') }}">
        <select name="active" class="search-box" style="width:150px;">
            <option value="">All Status</option>
            <option value="yes" {{ request('active') === 'yes' ? 'selected' : '' }}>Active Only</option>
            <option value="no" {{ request('active') === 'no' ? 'selected' : '' }}>Inactive Only</option>
        </select>
        <button type="submit" class="btn-go">Filter</button>
        @if(request('search') || request('active'))
            <a href="{{ route('companies.index') }}" class="btn-go" style="background:#6c757d;">Clear</a>
        @endif
    </form>
</div>

<!-- Companies Table -->
<table class="admin-table">
    <thead>
        <tr>
            <th>NAME</th>
            <th>SLUG</th>
            <th>EMPLOYEES</th>
            <th>DEVICES</th>
            <th>CONTACT</th>
            <th>STATUS</th>
            <th>CREATED</th>
            <th>ACTIONS</th>
        </tr>
    </thead>
    <tbody>
        @forelse($organizations as $org)
        <tr>
            <td>
                <a href="{{ route('companies.show', $org['id']) }}">
                    <strong>{{ $org['name'] }}</strong>
                </a>
            </td>
            <td><code>{{ $org['slug'] }}</code></td>
            <td>
                <span class="badge badge-info">
                    {{ $org['employee_count'] ?? 0 }} / {{ $org['max_employees'] ?? 100 }}
                </span>
            </td>
            <td>
                <span class="badge badge-info">
                    {{ $org['device_count'] ?? 0 }} / {{ $org['max_devices'] ?? 5 }}
                </span>
            </td>
            <td>
                @if($org['email'])
                    <small>{{ $org['email'] }}</small>
                @else
                    <span style="color:#999;">—</span>
                @endif
            </td>
            <td>
                @if($org['is_active'])
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </td>
            <td>
                <small>{{ date('M d, Y', strtotime($org['created_at'])) }}</small>
            </td>
            <td>
                <a href="{{ route('companies.show', $org['id']) }}" title="View" style="margin-right:8px;">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('companies.devices', $org['id']) }}" title="Devices" style="margin-right:8px;">
                    <i class="bi bi-phone"></i>
                </a>
                <a href="{{ route('companies.edit', $org['id']) }}" title="Edit" style="margin-right:8px;">
                    <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('companies.destroy', $org['id']) }}" method="POST" style="display:inline;" 
                      onsubmit="return confirm('Delete this organization? This will also delete all associated data!');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background:none; border:none; color:#dc3545; cursor:pointer;" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" style="text-align:center; padding: 40px; color: #666;">
                <i class="bi bi-building" style="font-size:2rem; opacity:0.3;"></i><br>
                No companies found. <a href="{{ route('companies.create') }}">Create your first company</a>.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top: 15px; color: #666; font-size: 0.9rem;">
    {{ count($organizations) }} companies
</div>
@endsection
