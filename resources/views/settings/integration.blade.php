@extends('layouts.app')

@section('title', 'Integration Settings')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Integration Settings</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if($error)
                    <div class="alert alert-warning">API Error: {{ $error }}</div>
                @endif

                <form action="{{ route('settings.integration.save') }}" method="POST">
                    @csrf

                    <!-- API Server Configuration -->
                    <div class="mb-4 p-3" style="background: var(--bg-quaternary); border-radius: 8px; border: 1px solid var(--border-color);">
                        <h5 style="color: var(--text-primary); margin-bottom: 15px;">🔗 API Server Configuration</h5>
                        <div class="mb-3">
                            <label for="api_server" class="form-label">Django Backend URL</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="api_server" name="api_server" 
                                       value="{{ session('django_base_url', env('DJANGO_BASE_URL', 'http://127.0.0.1:8000')) }}"
                                       placeholder="http://127.0.0.1:8000">
                                <button type="submit" class="btn btn-primary btn-sm">Set</button>
                            </div>
                            <div class="form-text">URL of the Django API backend (e.g., http://127.0.0.1:8000)</div>
                        </div>
                    </div>

                    <!-- Firebase Configuration -->
                    <h5 style="color: var(--text-primary); margin-bottom: 15px;">🔥 Firebase Configuration</h5>
                    
                    <div class="mb-3">
                        <label for="fcm_server_key" class="form-label">FCM Server Key</label>
                        <textarea class="form-control" id="fcm_server_key" name="fcm_server_key" rows="3" placeholder="Paste your Firebase Cloud Messaging server key here">{{ $data['fcm_server_key'] ?? '' }}</textarea>
                        <div class="form-text">Legacy server key for Firebase Cloud Messaging.</div>
                    </div>

                    <div class="mb-3">
                        <label for="fcm_service_account_json" class="form-label">FCM Service Account JSON</label>
                        <textarea class="form-control font-monospace" id="fcm_service_account_json" name="fcm_service_account_json" rows="8" placeholder="{ ... }">{{ $data['fcm_service_account_json'] ?? '' }}</textarea>
                        <div class="form-text">Service account JSON for Firebase HTTP v1 API.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
