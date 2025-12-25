@extends('layouts.app')

@section('content')
<style>
    .salary-edit-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--border-color);
    }

    .page-header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .employee-selector {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 20px;
        background: var(--bg-quaternary);
        padding: 15px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .employee-selector label {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .employee-selector select,
    .employee-selector input {
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--input-text);
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
        flex: 1;
        max-width: 400px;
    }

    .icon-btn {
        background: var(--btn-primary);
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .icon-btn.success {
        background: var(--btn-primary);
    }

    .section-header {
        background: var(--btn-primary);
        color: #1f2937; /* Dark gray for visibility */
        padding: 8px 15px;
        margin: 20px 0 10px 0;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .form-row {
        display: grid;
        grid-template-columns: 200px 1fr;
        gap: 15px;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .form-row label {
        font-size: 13px;
        color: var(--text-primary);
        font-weight: 500;
    }

    .form-row input {
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--input-text);
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 13px;
        max-width: 250px;
    }

    .form-row input:focus {
        outline: none;
        border-color: var(--btn-primary);
    }

    .form-row input[readonly] {
        background: var(--bg-quaternary);
        color: var(--text-secondary);
        cursor: not-allowed;
    }

    .help-text {
        font-size: 11px;
        color: var(--text-secondary);
        font-style: italic;
        margin-top: 5px;
    }

    .button-group {
        display: flex;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid var(--border-color);
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-primary {
        background: var(--btn-primary);
        color: white;
    }

    .btn-secondary {
        background: #5a9bd4;
        color: white;
    }

    .timestamp {
        font-size: 12px;
        color: var(--text-secondary);
        padding: 8px 0;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 15px;
        border: 1px solid #f5c6cb;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 15px;
        border: 1px solid #c3e6cb;
    }
</style>

<div class="salary-edit-container">
    <div class="page-header">
        <h2>Change salary statistic</h2>
    </div>

    @if(!empty($error))
        <div class="alert-error">API error: {{ $error }}</div>
    @endif

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('salary.update', $salary['id']) }}">
        @csrf
        @method('PUT')

        <!-- Employee Selector -->
        <div class="employee-selector">
            <label>Employee:</label>
            <input type="text" value="{{ $salary['employee'] ?? $salary['employee_id'] ?? '' }} - {{ $salary['employee_name'] ?? 'Unknown' }}" readonly>
            <button type="button" class="icon-btn">🔍</button>
            <button type="button" class="icon-btn success">+</button>
            <button type="button" class="icon-btn">🗑️</button>
        </div>

        <!-- Month & Year -->
        <div class="form-row">
            <label>Month:</label>
            <input type="number" name="month" min="1" max="12" value="{{ $salary['month'] ?? '' }}" required>
        </div>

        <div class="form-row">
            <label>Year:</label>
            <input type="number" name="year" min="2000" max="2100" value="{{ $salary['year'] ?? '' }}" required>
        </div>

        <!-- Salary Components -->
        <div class="section-header">Salary Components</div>

        <div class="form-row">
            <label>Base salary:</label>
            <input type="number" step="0.01" name="basic_salary" value="{{ $salary['basic_salary'] ?? $salary['base_salary'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>House rent:</label>
            <input type="number" step="0.01" name="house_rent" value="{{ $salary['house_rent'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>Medical allowance:</label>
            <input type="number" step="0.01" name="medical_allowance" value="{{ $salary['medical_allowance'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>Conveyance allowance:</label>
            <input type="number" step="0.01" name="conveyance_allowance" value="{{ $salary['conveyance_allowance'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>Food allowance:</label>
            <input type="number" step="0.01" name="food_allowance" value="{{ $salary['food_allowance'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>Other allowance:</label>
            <input type="number" step="0.01" name="other_allowance" value="{{ $salary['other_allowance'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>Gross salary:</label>
            <input type="number" step="0.01" name="gross_salary" value="{{ $salary['gross_salary'] ?? '' }}" readonly>
        </div>

        <!-- Attendance -->
        <div class="section-header">Attendance</div>

        <div class="form-row">
            <label>Working days:</label>
            <input type="number" name="working_days" value="{{ $salary['working_days'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>Weekends:</label>
            <input type="number" name="weekends" value="{{ $salary['weekends'] ?? $salary['working_days_including_weekends'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>Leave days:</label>
            <input type="number" name="leave_days" value="{{ $salary['leave_days'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>Holidays:</label>
            <input type="number" name="holidays" value="{{ $salary['holidays'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>Attended days:</label>
            <input type="number" name="attendance_days" value="{{ $salary['attendance_days'] ?? $salary['attended_days'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>On leave:</label>
            <input type="number" name="on_leave" value="{{ $salary['on_leave'] ?? '' }}">
        </div>





        <!-- Adjustments -->
        <div class="section-header">Adjustments</div>

        <div class="form-row">
            <label>Attendance Bonus:</label>
            <input type="number" step="0.01" name="attendance_bonus" value="{{ $salary['attendance_bonus'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>Required attendance percent:</label>
            <input type="number" step="0.01" name="required_attendance_percent" value="{{ $salary['required_attendance_percent'] ?? '' }}">
            <div class="help-text">Minimum attendance percentage required for bonus</div>
        </div>



        <div class="form-row">
            <label>Late fine:</label>
            <input type="number" step="0.01" name="late_fine" value="{{ $salary['late_fine'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>Late needed:</label>
            <input type="number" name="late_needed" value="{{ $salary['late_needed'] ?? '' }}">
            <div class="help-text">Number of late days to trigger one fine unit</div>
        </div>



        <div class="form-row">
            <label>Other deduction:</label>
            <input type="number" step="0.01" name="other_deduction" value="{{ $salary['other_deduction'] ?? '' }}">
        </div>

        <!-- Calculation -->
        <div class="section-header">Calculation</div>

        <div class="form-row">
            <label>Tax payment:</label>
            <input type="number" step="0.01" name="tds_percent" value="{{ $salary['tds_percent'] ?? $salary['tax_payment'] ?? '' }}">
        </div>

        <div class="form-row">
            <label>Payable:</label>
            <input type="number" step="0.01" name="payable" value="{{ $salary['payable'] ?? '' }}" readonly>
        </div>

        <!-- Metadata -->
        <div class="section-header">Metadata</div>

        <div class="timestamp">
            <strong>Created at:</strong> {{ $salary['created_at'] ?? 'N/A' }}
        </div>

        <div class="timestamp">
            <strong>Updated at:</strong> {{ $salary['updated_at'] ?? 'N/A' }}
        </div>

        <!-- Action Buttons -->
        <div class="button-group">
            <button type="submit" class="btn btn-primary">SAVE</button>
            <button type="button" class="btn btn-secondary" onclick="continueEditing()">Save and continue editing</button>
            <a href="{{ route('salary.index') }}" class="btn" style="background: #6c757d; color: white;">Cancel</a>
        </div>
    </form>
</div>

<script>
function continueEditing() {
    const form = document.querySelector('form');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'continue_editing';
    input.value = '1';
    form.appendChild(input);
    form.submit();
}
</script>
@endsection
