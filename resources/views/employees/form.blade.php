@extends('layouts.app')

@section('content')
<style>
    :root {
        --bg-primary: #ffffff;
        --bg-secondary: #f8f9fa;
        --text-primary: #212529;
        --border-color: #dee2e6;
        --input-bg: #ffffff;
        --btn-primary: #198754;
        --btn-text: #fff;
    }
    
    body {
        background-color: var(--bg-primary);
        color: var(--text-primary);
        font-family: system-ui, -apple-system, sans-serif;
        padding: 20px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 14px;
    }
    
    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="date"],
    textarea,
    select {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        background: var(--input-bg);
        color: var(--text-primary);
        font-size: 14px;
        box-sizing: border-box;
    }
    
    .btn-submit {
        background: var(--btn-primary);
        color: var(--btn-text);
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
    }
    
    .error-msg {
        color: #dc3545;
        font-size: 12px;
        margin-top: 4px;
    }
    
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
</style>

<h2 style="margin-top:0; margin-bottom:20px;">{{ isset($employee) ? 'Edit Employee' : 'Add Employee' }}</h2>

@if(session('error'))
    <div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:4px; margin-bottom:15px;">
        {{ session('error') }}
    </div>
@endif

<form action="{{ isset($employee) ? route('employees.update', $employee['id']) : route('employees.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($employee))
        @method('PUT')
    @endif
    @if(request('popup'))
        <input type="hidden" name="popup" value="1">
    @endif

    <div class="grid-2">
        <div class="form-group">
            <label>Employee ID *</label>
            <input type="text" name="employee_id" value="{{ old('employee_id', $employee['employee_id'] ?? '') }}" required>
        </div>
        <div class="form-group">
            <label>Name *</label>
            <input type="text" name="name" value="{{ old('name', $employee['name'] ?? '') }}" required>
        </div>
    </div>

    <div class="grid-2">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $employee['email'] ?? '') }}">
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $employee['phone'] ?? '') }}">
        </div>
    </div>

    <div class="grid-2">
        <div class="form-group">
            <label>Department</label>
            <input type="text" name="department" value="{{ old('department', $employee['department'] ?? '') }}">
        </div>
        <div class="form-group">
            <label>Designation</label>
            <input type="text" name="designation" value="{{ old('designation', $employee['designation'] ?? '') }}">
        </div>
    </div>

    <div class="grid-2">
        <div class="form-group">
            <label>Bank Account</label>
            <input type="text" name="bank_account" value="{{ old('bank_account', $employee['bank_account'] ?? '') }}">
        </div>
        <div class="form-group">
            <label>Branch</label>
            <input type="text" name="branch" value="{{ old('branch', $employee['branch'] ?? '') }}">
        </div>
    </div>

    <div class="grid-2">
        <div class="form-group">
            <label>Monthly Salary</label>
            <input type="number" step="0.01" name="monthly_salary" value="{{ old('monthly_salary', $employee['monthly_salary'] ?? 0) }}">
        </div>
        <div class="form-group">
            <label>Hire Date</label>
            <input type="date" name="hire_date" value="{{ old('hire_date', $employee['hire_date'] ?? '') }}">
        </div>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $employee['is_active'] ?? true) ? 'checked' : '' }}>
            Is Active
        </label>
    </div>
    
    <div class="form-group">
        <label>Date Inactive</label>
        <input type="date" name="date_inactive" value="{{ old('date_inactive', $employee['date_inactive'] ?? '') }}">
    </div>

    <div class="form-group">
        <label>Employee Image</label>
        @if(!empty($employee['face_image']))
            <div style="margin-bottom:5px;">
                <img src="data:image/jpeg;base64,{{ $employee['face_image'] }}" style="height:50px; border-radius:4px;">
            </div>
        @endif
        <input type="file" name="employee_image">
    </div>

    <div class="form-group">
        <label>Facial Template (Read Only)</label>
        <textarea name="facial_template" rows="3" readonly style="background:#eee;">{{ $employee['facial_template'] ?? '' }}</textarea>
    </div>

    <button type="submit" class="btn-submit">SAVE</button>
</form>
@endsection
