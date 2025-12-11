@extends('layouts.app')

@section('title', 'Change Company Information')

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
        font-size: 1.2rem;
        color: var(--text-muted);
        margin-bottom: 5px;
    }

    .page-subtitle {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-main);
    }

    .btn-history {
        background-color: #d1e7dd;
        color: #198754;
        border: 1px solid #badbcc;
        padding: 5px 15px;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 20px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-history:hover {
        background-color: #198754;
        color: #fff;
    }

    /* Form */
    .admin-form-row {
        display: flex;
        padding: 15px 0;
        border-bottom: 1px solid #d1e7dd;
        align-items: flex-start;
    }

    .admin-form-label {
        width: 180px;
        font-weight: 600;
        color: var(--text-main);
        padding-top: 7px;
        font-size: 0.9rem;
    }

    .admin-form-field {
        flex: 1;
        max-width: 600px;
    }

    .form-control {
        background-color: var(--input-bg) !important;
        border: 1px solid var(--input-border) !important;
        color: var(--text-main) !important;
        border-radius: 4px;
        padding: 8px 12px;
    }

    .form-control:focus {
        border-color: var(--accent-color) !important;
        box-shadow: 0 0 0 2px rgba(25,135,84,0.15) !important;
    }

    .help-text {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 5px;
    }

    .current-file {
        font-size: 0.9rem;
        margin-bottom: 8px;
    }
    .current-file a {
        color: var(--accent-color);
        text-decoration: none;
    }

    /* Bottom Bar */
    .bottom-bar {
        background-color: #d1e7dd;
        padding: 10px 15px;
        margin-top: 30px;
        border-radius: 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid #badbcc;
    }

    .btn-save {
        background-color: #198754;
        color: #fff;
        border: none;
        padding: 8px 15px;
        text-transform: uppercase;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-save:hover {
        background-color: #157347;
    }

    .btn-delete {
        background-color: #dc3545;
        color: #fff;
        border: none;
        padding: 8px 15px;
        text-transform: uppercase;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-delete:hover {
        background-color: #c82333;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Change Company Information</div>
        <div class="page-subtitle">{{ $data['name'] ?? 'Company Name' }}</div>
    </div>
    <a href="#" class="btn-history">HISTORY</a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background:#1b5e20; color:#e8f5e9; border:none;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="background:#b71c1c; color:#ffebee; border:none;">{{ session('error') }}</div>
@endif

<form action="{{ route('settings.company.save') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="admin-form-row">
        <div class="admin-form-label">Name:</div>
        <div class="admin-form-field">
            <input type="text" class="form-control" name="name" value="{{ $data['name'] ?? '' }}" required>
        </div>
    </div>

    <div class="admin-form-row">
        <div class="admin-form-label">Logo:</div>
        <div class="admin-form-field">
            @if(!empty($data['logo_url']))
                <div class="current-file">
                    Currently: <a href="{{ $data['logo_url'] }}" target="_blank">{{ basename($data['logo_url']) }}</a>
                    <input type="checkbox" name="clear_logo" id="clear_logo"> <label for="clear_logo">Clear</label>
                </div>
            @endif
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-weight:600; font-size:0.9rem;">Change:</span>
                <input type="file" class="form-control" name="logo" accept="image/*" style="padding: 4px;">
            </div>
        </div>
    </div>

    <div class="admin-form-row">
        <div class="admin-form-label">Address:</div>
        <div class="admin-form-field">
            <textarea class="form-control" name="address" rows="4">{{ $data['address'] ?? '' }}</textarea>
        </div>
    </div>

    <div class="admin-form-row">
        <div class="admin-form-label">Email:</div>
        <div class="admin-form-field">
            <input type="email" class="form-control" name="email" value="{{ $data['email'] ?? '' }}">
        </div>
    </div>

    <div class="admin-form-row">
        <div class="admin-form-label">Phone:</div>
        <div class="admin-form-field">
            <input type="text" class="form-control" name="phone" value="{{ $data['phone'] ?? '' }}">
        </div>
    </div>

    <div class="admin-form-row">
        <div class="admin-form-label">Website:</div>
        <div class="admin-form-field">
            @if(!empty($data['website']))
                <div class="current-file">
                    Currently: <a href="{{ $data['website'] }}" target="_blank">{{ $data['website'] }}</a>
                </div>
            @endif
            <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-weight:600; font-size:0.9rem;">Change:</span>
                <input type="url" class="form-control" name="website" value="{{ $data['website'] ?? '' }}">
            </div>
        </div>
    </div>

    <div class="admin-form-row">
        <div class="admin-form-label">TIN:</div>
        <div class="admin-form-field">
            <input type="text" class="form-control" name="tin" value="{{ $data['tin'] ?? '' }}">
        </div>
    </div>

    <div class="admin-form-row">
        <div class="admin-form-label">BIN / BFN:</div>
        <div class="admin-form-field">
            <input type="text" class="form-control" name="bin" value="{{ $data['bin'] ?? '' }}">
        </div>
    </div>

    <div class="admin-form-row">
        <div class="admin-form-label">Founder:</div>
        <div class="admin-form-field">
            <input type="text" class="form-control" name="founder" value="{{ $data['founder'] ?? '' }}">
        </div>
    </div>

    <div class="bottom-bar">
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn-save">SAVE</button>
            <button type="submit" name="continue" value="1" class="btn-save">Save and continue editing</button>
        </div>
        <button type="button" class="btn-delete">Delete</button>
    </div>
</form>
@endsection
