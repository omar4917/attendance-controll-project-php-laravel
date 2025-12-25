@extends('layouts.app')

@section('title', 'Holidays')

@push('head')
<style>
    /* Light Green Theme */
    :root {
        --bg-body: #f8fdf9;
        --bg-card: #ffffff;
        --text-main: #052c18;
        --text-muted: #0f5132;
        --accent-color: #198754;
        --accent-hover: #157347;
        --border-color: #badbcc;
        --table-header-bg: #d1e7dd;
        --table-header-text: #052c18;
        --table-row-hover: #c3e6cb;
        --link-color: #198754;
    }

    body {
        background-color: var(--bg-body) !important;
        color: var(--text-main) !important;
    }

    .card {
        background-color: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
    }

    /* Header */
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
        background-color: #198754;
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
        background-color: #157347;
        color: #fff;
    }

    /* Top Action Bar */
    .action-bar {
        background-color: #f0fdf4;
        padding: 10px 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid var(--border-color);
    }

    .search-box {
        background: #ffffff;
        border: 1px solid #badbcc;
        color: #052c18;
        padding: 8px 12px;
        border-radius: 6px;
        width: 300px;
    }

    .btn-go {
        background-color: #198754;
        color: #fff;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
    }
    .btn-go:hover {
        background-color: #157347;
    }

    /* Table */
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.9rem;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
    }

    .admin-table thead tr {
        background-color: var(--table-header-bg);
        color: var(--table-header-text);
        text-transform: uppercase;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .admin-table th, .admin-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }

    .admin-table tbody tr:nth-child(even) {
        background-color: #f0fdf4;
    }

    .admin-table tbody tr:hover {
        background-color: var(--table-row-hover);
    }

    .admin-table a {
        color: var(--link-color);
        text-decoration: none;
        font-weight: 600;
    }

    .admin-table a:hover {
        text-decoration: underline;
    }

    .th-sortable {
        color: inherit;
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: color 0.2s;
    }
    .th-sortable:hover {
        color: var(--accent-color);
        text-decoration: none;
    }
    .th-sortable.active {
        color: var(--accent-color);
    }

    /* Status Icons */
    .icon-yes { color: #198754; font-weight: bold; }
    .icon-no { color: #dc3545; font-weight: bold; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Holidays</h1>
    <a href="{{ route('holidays.index', ['action' => 'add']) }}" class="btn-add">ADD HOLIDAY</a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background:#d1e7dd; color:#0f5132; border:1px solid #badbcc;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb;">{{ session('error') }}</div>
@endif

<div class="action-bar">
    <form action="{{ route('holidays.index') }}" method="GET" style="display:flex; gap:0;">
        <input type="text" name="search" class="search-box" placeholder="Search holidays..." value="{{ request('search') }}">
        <button type="submit" class="btn-go">Search</button>
    </form>
</div>

@php
    $currentSort = request('sort', 'start_date');
    $currentDir = request('dir', 'asc');
    $toggleDir = $currentDir === 'asc' ? 'desc' : 'asc';
    $sortParams = request()->except(['sort', 'dir']);
@endphp
<table class="admin-table">
    <thead>
        <tr>
            <th>OWNER</th>
            <th>
                <a href="{{ route('holidays.index', array_merge($sortParams, ['sort' => 'name', 'dir' => $currentSort === 'name' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'name' ? 'active' : '' }}">
                    NAME {!! $currentSort === 'name' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                </a>
            </th>
            <th>
                <a href="{{ route('holidays.index', array_merge($sortParams, ['sort' => 'start_date', 'dir' => $currentSort === 'start_date' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'start_date' ? 'active' : '' }}">
                    START DATE {!! $currentSort === 'start_date' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                </a>
            </th>
            <th>
                <a href="{{ route('holidays.index', array_merge($sortParams, ['sort' => 'end_date', 'dir' => $currentSort === 'end_date' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'end_date' ? 'active' : '' }}">
                    END DATE {!! $currentSort === 'end_date' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                </a>
            </th>
            <th>
                <a href="{{ route('holidays.index', array_merge($sortParams, ['sort' => 'is_active', 'dir' => $currentSort === 'is_active' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'is_active' ? 'active' : '' }}">
                    IS ACTIVE {!! $currentSort === 'is_active' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                </a>
            </th>
            <th>
                <a href="{{ route('holidays.index', array_merge($sortParams, ['sort' => 'is_government', 'dir' => $currentSort === 'is_government' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'is_government' ? 'active' : '' }}">
                    IS GOVERNMENT {!! $currentSort === 'is_government' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                </a>
            </th>
            <th>
                <a href="{{ route('holidays.index', array_merge($sortParams, ['sort' => 'created_at', 'dir' => $currentSort === 'created_at' ? $toggleDir : 'desc'])) }}" class="th-sortable {{ $currentSort === 'created_at' ? 'active' : '' }}">
                    CREATED AT {!! $currentSort === 'created_at' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                </a>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse($holidays as $h)
        <tr>
            <td>{{ $h['organization_name'] ?? 'Global' }}</td>
            <td><a href="{{ route('holidays.edit', $h['id'] ?? 0) }}">{{ $h['name'] ?? '-' }}</a></td>
            <td>{{ $h['start_date'] ?? '-' }}</td>
            <td>{{ $h['end_date'] ?? '-' }}</td>
            <td>
                @if(!empty($h['is_active']))
                    <span class="icon-yes">✔</span>
                @else
                    <span class="icon-no">✘</span>
                @endif
            </td>
            <td>
                @if(!empty($h['is_government']))
                    <span class="icon-yes">✔</span>
                @else
                    <span class="icon-no">✘</span>
                @endif
            </td>
            <td>{{ $h['created_at'] ?? now()->format('M. d, Y, h:i a') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center; padding: 30px; color: #666;">No holidays found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top: 15px; color: #666; font-size: 0.9rem;">
    {{ count($holidays) }} holidays
</div>
@endsection

@section('modal')
@if(request('action') == 'add')
<div class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Holiday</h3>
            <a href="{{ route('holidays.index') }}" class="close-btn">×</a>
        </div>
        <form action="{{ route('holidays.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="name" required placeholder="Holiday Name">
            </div>

            <div class="form-group">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" required>
            </div>

            <div class="form-group">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" required>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="new_is_active" name="is_active" value="1" checked>
                <label class="form-check-label" for="new_is_active">Is Active</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="new_is_government" name="is_government" value="1">
                <label class="form-check-label" for="new_is_government">Is Government Holiday</label>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn-save">Create Holiday</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('head')
<style>
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    .modal-box {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .modal-header h3 { margin: 0; font-size: 1.25rem; }
    .close-btn { font-size: 1.5rem; text-decoration: none; color: #666; cursor: pointer; }
    
    /* Reuse form styles from edit page if not global */
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; font-weight: 600; margin-bottom: 5px; }
    .form-control { width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    .form-check { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .btn-save { background: #198754; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: 600; width: 100%; }
    .btn-save:hover { background: #157347; }
</style>
@endpush
