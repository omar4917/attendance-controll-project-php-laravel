@extends('layouts.app')

@section('title', isset($organization) ? 'Edit Organization' : 'Create Organization')

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

    .form-card {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #badbcc);
        border-radius: 8px;
        padding: 25px;
        max-width: 700px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        color: var(--text-main);
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border-color, #badbcc);
        border-radius: 6px;
        font-size: 0.95rem;
        background: #fff;
        color: var(--text-main);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--accent-color, #198754);
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-check input {
        width: 18px;
        height: 18px;
    }

    .form-help {
        font-size: 0.8rem;
        color: var(--text-muted, #666);
        margin-top: 4px;
    }

    .btn-submit {
        background-color: var(--accent-color, #198754);
        color: #fff;
        border: none;
        padding: 12px 30px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-submit:hover {
        background-color: var(--accent-hover, #157347);
    }

    .btn-cancel {
        background-color: #6c757d;
        color: #fff;
        border: none;
        padding: 12px 30px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none;
        margin-left: 10px;
    }
    .btn-cancel:hover {
        background-color: #5a6268;
        color: #fff;
    }

    .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 25px 0 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border-color, #badbcc);
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-building me-2"></i>
        {{ isset($organization) ? 'Edit Organization' : 'Create Organization' }}
    </h1>
</div>

@if(session('error'))
    <div class="alert alert-danger" style="background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; padding:10px; border-radius:6px; margin-bottom:15px; max-width:700px;">
        {{ session('error') }}
    </div>
@endif

<div class="form-card">
    <form action="{{ isset($organization) ? route('organizations.update', $organization['id']) : route('organizations.store') }}" method="POST">
        @csrf
        @if(isset($organization))
            @method('PUT')
        @endif

        <h3 class="section-title">Basic Information</h3>
        
        <div class="form-group">
            <label class="form-label" for="name">Organization Name *</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="{{ old('name', $organization['name'] ?? '') }}" required>
        </div>

        @if(!isset($organization))
        <div class="form-group">
            <label class="form-label" for="slug">Slug (URL identifier)</label>
            <input type="text" class="form-control" id="slug" name="slug" 
                   value="{{ old('slug', $organization['slug'] ?? '') }}" 
                   placeholder="e.g., company-abc (auto-generated if empty)">
            <p class="form-help">Leave empty to auto-generate from name</p>
        </div>
        @endif

        <h3 class="section-title">Contact Information</h3>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="{{ old('email', $organization['email'] ?? '') }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" 
                       value="{{ old('phone', $organization['phone'] ?? '') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="address">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $organization['address'] ?? '') }}</textarea>
        </div>

        @if(!isset($organization))
        <h3 class="section-title">Main Organization Admin</h3>
        <p class="form-help" style="margin-bottom:15px;">Create the primary administrator account for this organization.</p>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="admin_username">Admin Username</label>
                <input type="text" class="form-control" id="admin_username" name="admin_username" 
                       value="{{ old('admin_username') }}" placeholder="e.g. admin_company">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="admin_email">Admin Email</label>
                <input type="email" class="form-control" id="admin_email" name="admin_email" 
                       value="{{ old('admin_email') }}" placeholder="admin@company.com">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="admin_password">Admin Password</label>
                <input type="password" class="form-control" id="admin_password" name="admin_password" placeholder="Min. 8 characters">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="admin_password_confirmation">Confirm Password</label>
                <input type="password" class="form-control" id="admin_password_confirmation" name="admin_password_confirmation">
            </div>
        </div>
        @endif

        <h3 class="section-title">Subscription Plan</h3>

        <div class="form-group">
            <label class="form-label" for="plan_id">Plan</label>
            <select class="form-control" id="plan_id" name="plan_id" onchange="toggleCustomLimits()" style="max-width:400px;">
                @if(isset($plans) && count($plans) > 0)
                @foreach($plans as $plan)
                <option value="{{ $plan['id'] }}" 
                        data-employees="{{ $plan['max_employees'] }}"
                        data-devices="{{ $plan['max_devices'] }}"
                        {{ (old('plan_id', $organization['plan_id'] ?? '') == $plan['id']) ? 'selected' : '' }}>
                    {{ $plan['name'] }} ({{ $plan['max_employees'] }} employees, {{ $plan['max_devices'] }} devices)
                </option>
                @endforeach
                @endif
                <option value="custom" 
                        {{ (old('plan_id', $organization['plan_id'] ?? '') == '' || old('plan_id', $organization['plan_id'] ?? '') === null) ? 'selected' : '' }}>
                    ⚙️ Custom Plan (Set limits manually)
                </option>
            </select>
        </div>

        <div id="custom-limits" style="display:none;">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="max_employees">Max Employees</label>
                    <input type="number" class="form-control" id="max_employees" name="max_employees" 
                           value="{{ old('max_employees', $organization['max_employees'] ?? 100) }}" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label" for="max_devices">Max Devices</label>
                    <input type="number" class="form-control" id="max_devices" name="max_devices" 
                           value="{{ old('max_devices', $organization['max_devices'] ?? 5) }}" min="1">
                </div>
            </div>
        </div>

        <script>
        function toggleCustomLimits() {
            const select = document.getElementById('plan_id');
            const customDiv = document.getElementById('custom-limits');
            const option = select.options[select.selectedIndex];
            
            if (select.value === 'custom') {
                customDiv.style.display = 'block';
            } else {
                customDiv.style.display = 'none';
                // Auto-fill hidden fields with plan limits
                const employees = option.getAttribute('data-employees');
                const devices = option.getAttribute('data-devices');
                if (employees && devices) {
                    document.getElementById('max_employees').value = employees;
                    document.getElementById('max_devices').value = devices;
                }
            }
        }
        // Run on page load
        document.addEventListener('DOMContentLoaded', toggleCustomLimits);
        </script>

        <div class="form-group">
            <label class="form-check">
                <input type="checkbox" name="is_active" value="1" 
                       {{ old('is_active', $organization['is_active'] ?? true) ? 'checked' : '' }}>
                <span>Active</span>
            </label>
            <p class="form-help">Inactive organizations cannot receive attendance data</p>
        </div>

        <div style="margin-top:30px; padding-top:20px; border-top:1px solid var(--border-color, #badbcc);">
            <button type="submit" class="btn-submit">
                <i class="bi bi-check-circle me-1"></i>
                {{ isset($organization) ? 'Update Organization' : 'Create Organization' }}
            </button>
            <a href="{{ route('organizations.index') }}" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>
@endsection
