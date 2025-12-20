@extends('layouts.app')

@section('title', 'Change context setting')

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
        align-items: center;
    }

    .admin-form-label {
        width: 180px;
        font-weight: 600;
        color: var(--text-main);
        font-size: 0.9rem;
    }

    .admin-form-field {
        flex: 1;
        max-width: 600px;
    }

    /* Checkbox styling */
    .form-check-input {
        background-color: var(--input-bg);
        border-color: var(--input-border);
        width: 1.2em;
        height: 1.2em;
        margin-top: 0;
    }
    .form-check-input:checked {
        background-color: var(--accent-color);
        border-color: var(--accent-color);
    }

    .readonly-text {
        padding: 8px 0;
        color: var(--text-main);
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
        <div class="page-title">Change context setting</div>
        <div class="page-subtitle">Organization Configuration</div>
    </div>
    <a href="#" class="btn-history">HISTORY</a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background:#1b5e20; color:#e8f5e9; border:none;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="background:#b71c1c; color:#ffebee; border:none;">{{ session('error') }}</div>
@endif

<form action="{{ route('settings.context.save') }}" method="POST">
    @csrf
    
    <!-- General Settings -->
    <div style="margin: 20px 0 10px; font-weight:600; color:var(--text-muted); border-bottom:1px solid #d1e7dd; padding-bottom:5px;">General Settings</div>
    
    <div class="admin-form-row">
        <label class="admin-form-label" for="timezone">Timezone</label>
        <div class="admin-form-field">
            <input class="form-control" type="text" id="timezone" name="timezone" value="{{ $data['timezone'] ?? 'Asia/Dhaka' }}" style="width:100%; padding:8px; border:1px solid #badbcc; border-radius:4px;">
        </div>
    </div>

    <div class="admin-form-row">
        <label class="admin-form-label" for="work_week_start">Work Week Start</label>
        <div class="admin-form-field">
            <select class="form-control" id="work_week_start" name="work_week_start" style="width:100%; padding:8px; border:1px solid #badbcc; border-radius:4px;">
                @foreach([0=>'Sunday', 1=>'Monday', 2=>'Tuesday', 3=>'Wednesday', 4=>'Thursday', 5=>'Friday', 6=>'Saturday'] as $val => $label)
                    <option value="{{ $val }}" {{ ($data['work_week_start'] ?? 0) == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Biometrics -->
    <div style="margin: 20px 0 10px; font-weight:600; color:var(--text-muted); border-bottom:1px solid #d1e7dd; padding-bottom:5px;">Biometrics</div>
    
    <div class="admin-form-row">
        <label class="admin-form-label" for="liveness_threshold">Liveness Threshold</label>
        <div class="admin-form-field">
            <input class="form-control" type="number" step="0.01" min="0" max="1" id="liveness_threshold" name="liveness_threshold" value="{{ $data['liveness_threshold'] ?? 0.7 }}" style="width:100px; padding:8px; border:1px solid #badbcc; border-radius:4px;">
             <span style="font-size:0.8em; color:#666; margin-left:10px;">(0.0 - 1.0)</span>
        </div>
    </div>
    
    <div class="admin-form-row">
        <label class="admin-form-label" for="match_threshold">Match Threshold</label>
        <div class="admin-form-field">
            <input class="form-control" type="number" step="0.01" min="0" max="1" id="match_threshold" name="match_threshold" value="{{ $data['match_threshold'] ?? 0.8 }}" style="width:100px; padding:8px; border:1px solid #badbcc; border-radius:4px;">
            <span style="font-size:0.8em; color:#666; margin-left:10px;">(0.0 - 1.0)</span>
        </div>
    </div>

    <!-- Voice & Notifications -->
    <div style="margin: 20px 0 10px; font-weight:600; color:var(--text-muted); border-bottom:1px solid #d1e7dd; padding-bottom:5px;">Voice & Notifications</div>

    <div class="admin-form-row">
        <label class="admin-form-label" for="default_voice_language">Voice Language</label>
        <div class="admin-form-field">
            <input class="form-control" type="text" id="default_voice_language" name="default_voice_language" value="{{ $data['default_voice_language'] ?? 'en' }}" style="width:100px; padding:8px; border:1px solid #badbcc; border-radius:4px;">
        </div>
    </div>

    <div class="admin-form-row">
         <div class="admin-form-field" style="display:flex; gap:20px; flex-wrap:wrap;">
            <div style="display:flex; align-items:center;">
                <input class="form-check-input" type="checkbox" id="voice_enabled" name="voice_enabled" {{ !empty($data['voice_enabled']) ? 'checked' : '' }}>
                <label for="voice_enabled" style="margin-left:5px; font-weight:600; color:var(--text-main);">Voice Enabled (Org)</label>
            </div>
            <div style="display:flex; align-items:center;">
                <input class="form-check-input" type="checkbox" id="email_on_late" name="email_on_late" {{ !empty($data['email_on_late']) ? 'checked' : '' }}>
                <label for="email_on_late" style="margin-left:5px; font-weight:600; color:var(--text-main);">Email on Late</label>
            </div>
            <div style="display:flex; align-items:center;">
                <input class="form-check-input" type="checkbox" id="email_on_absent" name="email_on_absent" {{ !empty($data['email_on_absent']) ? 'checked' : '' }}>
                <label for="email_on_absent" style="margin-left:5px; font-weight:600; color:var(--text-main);">Email on Absent</label>
            </div>
         </div>
    </div>

    <!-- System Legacy -->
    <div style="margin: 20px 0 10px; font-weight:600; color:var(--text-muted); border-bottom:1px solid #d1e7dd; padding-bottom:5px;">System Defaults (Legacy)</div>
    
    <div class="admin-form-row">
        <div class="admin-form-field" style="display:flex; align-items:center;">
            <input class="form-check-input" type="checkbox" id="text_message_display" name="text_message_display" 
                {{ !empty($data['text_message_display']) ? 'checked' : '' }}>
            <label class="admin-form-label" for="text_message_display" style="margin-left:10px; width:auto;">Text message display</label>
        </div>
    </div>

    <div class="admin-form-row">
        <div class="admin-form-field" style="display:flex; align-items:center;">
            <input class="form-check-input" type="checkbox" id="voice_message_active" name="voice_message_active" 
                {{ !empty($data['voice_message_active']) ? 'checked' : '' }}>
            <label class="admin-form-label" for="voice_message_active" style="margin-left:10px; width:auto;">Voice message active</label>
        </div>
    </div>

    <div class="admin-form-row">
        <div class="admin-form-label">Updated at:</div>
        <div class="admin-form-field">
            <div class="readonly-text">{{ $data['updated_at'] ?? now()->format('M. d, Y, h:i a') }}</div>
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
