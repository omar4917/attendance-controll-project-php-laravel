@extends('layouts.app')

@section('title', 'Edit Holiday')

@push('head')
<style>
    /* Light Green Theme */
    :root {
        --bg-body: #f8fdf9;
        --bg-card: #ffffff;
        --text-main: #052c18;
        --text-muted: #417a5c;
        --accent-color: #198754;
        --border-color: #badbcc;
        --input-bg: #ffffff;
        --input-border: #badbcc;
    }

    body {
        background-color: var(--bg-body) !important;
        color: var(--text-main) !important;
    }

    .card {
        background-color: var(--bg-card) !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
        padding: 20px !important;
        border-radius: 8px;
        border: 1px solid var(--border-color);
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

    .btn-back {
        background-color: #6c757d;
        color: #fff;
        border: none;
        padding: 8px 16px;
        text-transform: uppercase;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
    }
    .btn-back:hover {
        background-color: #545b62;
        color: #fff;
    }

    /* Form */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-main);
    }

    .form-control {
        width: 100%;
        max-width: 400px;
        padding: 10px 12px;
        border: 1px solid var(--input-border);
        border-radius: 6px;
        background: var(--input-bg);
        color: var(--text-main);
    }

    .form-control:focus {
        border-color: var(--accent-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(25,135,84,0.15);
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        accent-color: var(--accent-color);
    }

    .form-check-label {
        font-weight: 500;
        color: var(--text-main);
    }

    /* Buttons */
    .btn-group {
        display: flex;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
    }

    .btn-save {
        background-color: #198754;
        color: #fff;
        border: none;
        padding: 10px 20px;
        text-transform: uppercase;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
    }
    .btn-save:hover {
        background-color: #157347;
    }

    .btn-delete {
        background-color: #dc3545;
        color: #fff;
        border: none;
        padding: 10px 20px;
        text-transform: uppercase;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        margin-left: auto;
    }
    .btn-delete:hover {
        background-color: #c82333;
    }

    .info-text {
        color: var(--text-muted);
        font-size: 0.85rem;
        margin-top: 5px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Holiday: {{ $holiday['name'] ?? 'Holiday' }}</h1>
    <a href="{{ route('holidays.index') }}" class="btn-back">← Back to List</a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; padding:12px; border-radius:6px; margin-bottom:20px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:12px; border-radius:6px; margin-bottom:20px;">{{ session('error') }}</div>
@endif

<div class="card">
    <form action="{{ route('holidays.update', $holiday['id']) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" name="name" value="{{ $holiday['name'] ?? '' }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control" name="start_date" value="{{ $holiday['start_date'] ?? '' }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control" name="end_date" value="{{ $holiday['end_date'] ?? '' }}" required>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                {{ !empty($holiday['is_active']) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Is Active</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="is_government" name="is_government" value="1"
                {{ !empty($holiday['is_government']) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_government">Is Government Holiday</label>
        </div>

        <p class="info-text">Created: {{ $holiday['created_at'] ?? 'N/A' }}</p>

        <div class="btn-group">
            <button type="submit" class="btn-save">Save Changes</button>
            <button type="button" class="btn-delete" onclick="if(confirm('Delete this holiday?')) document.getElementById('delete-form').submit();">Delete</button>
        </div>
    </form>
    
    <form id="delete-form" action="{{ route('holidays.destroy', $holiday['id']) }}" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
