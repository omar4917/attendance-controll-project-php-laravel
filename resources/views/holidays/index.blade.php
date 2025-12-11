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

<table class="admin-table">
    <thead>
        <tr>
            <th>NAME</th>
            <th>START DATE</th>
            <th>END DATE</th>
            <th>IS ACTIVE</th>
            <th>IS GOVERNMENT</th>
            <th>CREATED AT</th>
        </tr>
    </thead>
    <tbody>
        @forelse($holidays as $h)
        <tr>
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
            <td colspan="6" style="text-align:center; padding: 30px; color: #666;">No holidays found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div style="margin-top: 15px; color: #666; font-size: 0.9rem;">
    {{ count($holidays) }} holidays
</div>
@endsection
