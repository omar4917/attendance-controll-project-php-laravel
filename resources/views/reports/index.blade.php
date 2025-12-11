@extends('layouts.app')

@section('content')
<style>
    .salary-report-container {
        background: var(--bg-primary);
        color: var(--text-primary);
        padding: 0;
    }

    .report-header {
        background: var(--bg-header);
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
        border-radius: 8px 8px 0 0;
    }

    .report-header h1 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .report-header h1::before {
        content: "💰";
    }

    .report-subtitle {
        font-size: 12px;
        color: var(--text-secondary);
        margin-top: 5px;
    }

    .header-actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .header-actions a, .header-actions button {
        padding: 6px 15px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: none;
        cursor: pointer;
    }

    .btn-back {
        background: var(--btn-primary);
        color: white;
    }

    .btn-check {
        background: var(--btn-primary);
        color: white;
    }

    .btn-delete {
        background: transparent;
        color: #dc3545;
        border: 1px solid #dc3545 !important;
    }

    .filter-bar {
        background: var(--bg-quaternary);
        padding: 15px 20px;
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--border-color);
    }

    .filter-bar label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-primary);
        margin-right: 5px;
    }

    .filter-bar select {
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--input-text);
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        min-width: 120px;
    }

    .filter-bar .btn-filter {
        background: var(--btn-primary);
        color: white;
        padding: 6px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }

    .download-btn {
        background: #fd7e14;
        color: white;
        padding: 8px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        margin: 15px 20px;
        display: inline-block;
    }

    .rules-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        padding: 15px 20px;
    }

    .rules-box {
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .rules-box .header {
        padding: 8px 15px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .rules-box.bonus .header {
        background: var(--btn-primary);
        color: white;
    }

    .rules-box.fine .header {
        background: #dc3545;
        color: white;
    }

    .rules-box .content {
        background: var(--bg-quaternary);
        padding: 12px 15px;
        font-size: 12px;
        color: var(--text-primary);
    }

    .rules-box .content ul {
        margin: 0;
        padding-left: 20px;
    }

    .rules-box .content li {
        margin-bottom: 5px;
    }

    .table-container {
        overflow-x: auto;
        margin: 0 20px 20px 20px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }

    .salary-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        min-width: 2000px;
    }

    .salary-table thead {
        background: var(--btn-primary);
        color: white;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .salary-table th {
        padding: 10px 6px;
        text-align: center;
        font-weight: 600;
        border-right: 1px solid rgba(255,255,255,0.2);
        white-space: nowrap;
        font-size: 10px;
    }

    .salary-table th:last-child {
        border-right: none;
    }

    .salary-table tbody tr {
        border-bottom: 1px solid var(--border-color);
    }

    .salary-table tbody tr:nth-child(even) {
        background: var(--bg-quaternary);
    }

    .salary-table tbody tr:nth-child(odd) {
        background: var(--bg-primary);
    }

    .salary-table tbody tr:hover {
        background: var(--bg-secondary);
    }

    .salary-table td {
        padding: 8px 6px;
        border-right: 1px solid var(--border-color);
        color: var(--text-primary);
        text-align: center;
        white-space: nowrap;
    }

    .salary-table td:last-child {
        border-right: none;
    }

    .employee-id {
        color: var(--btn-primary);
        font-weight: 600;
    }

    .employee-name {
        color: var(--text-primary);
        text-align: left !important;
    }

    .amount {
        text-align: right !important;
        font-family: 'Courier New', monospace;
    }

    .payable {
        background: var(--btn-primary) !important;
        color: white !important;
        font-weight: 600;
    }

    .signature-box {
        width: 60px;
        height: 25px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 3px;
        display: inline-block;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        padding: 10px;
        margin: 0 20px;
        border-radius: 4px;
        border: 1px solid #f5c6cb;
    }

    @media (max-width: 768px) {
        .rules-section {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="salary-report-container">
    <!-- Header -->
    <div class="report-header">
        <h1>Salary Report - {{ $monthName ?? 'December' }} {{ $year ?? date('Y') }}</h1>
        <div class="report-subtitle">📋 Data cutdate formatted from database</div>
        <div class="header-actions">
            <a href="{{ route('attendance.index') }}" class="btn-back">← Back to Admin</a>
            <button type="button" class="btn-check">✓</button>
            <button type="button" class="btn-delete">Delete salary from database</button>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="filter-bar">
        <div>
            <label>Month:</label>
            <select name="month">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ ($month ?? date('m')) == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0,0,0,$m,1)) }}
                    </option>
                @endfor
            </select>
        </div>
        <div>
            <label>Year:</label>
            <select name="year">
                @for($y = date('Y')-2; $y <= date('Y')+1; $y++)
                    <option value="{{ $y }}" {{ ($year ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label>Department:</label>
            <select name="department">
                <option value="">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-filter">Filter</button>
    </form>

    <!-- Download Button -->
    @php
        $djangoUrl = rtrim(Session::get('django_base_url', config('django.base_url', env('DJANGO_BASE_URL', 'http://localhost:8001'))), '/');
        $pdfUrl = $djangoUrl . '/salary-report/pdf/?month=' . ($month ?? date('n')) . '&year=' . ($year ?? date('Y'));
        if (!empty(request('department'))) {
            $pdfUrl .= '&department=' . urlencode(request('department'));
        }
    @endphp
    <a href="{{ $pdfUrl }}" target="_blank" class="download-btn" style="text-decoration:none; display:inline-block;">📥 Download PDF</a>

    <!-- Rules Section -->
    <div class="rules-section">
        <div class="rules-box bonus">
            <div class="header">📋 Bonus Rules</div>
            <div class="content">
                <ul>
                    <li>Perfect Attendance: 1,000 BDT (0 late days)</li>
                    <li>Manual Bonuses: Added by admin</li>
                </ul>
            </div>
        </div>
        <div class="rules-box fine">
            <div class="header">⚠️ Fine Rules</div>
            <div class="content">
                <ul>
                    <li>Late Fine: 1 day salary per 3 late days (Monthly Salary ÷ 30)</li>
                    <li>Manual Fines: Added by admin</li>
                </ul>
            </div>
        </div>
    </div>

    @if(!empty($error))
        <div class="alert-error">API error: {{ $error }}</div>
    @endif

    <!-- Salary Table -->
    <div class="table-container">
        <table class="salary-table">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Employee</th>
                    <th>Join Date</th>
                    <th>Bank</th>
                    <th>Basic</th>
                    <th>House Rent</th>
                    <th>Medical</th>
                    <th>Conv</th>
                    <th>Food</th>
                    <th>Other Allow</th>
                    <th>Gross</th>
                    <th>WD</th>
                    <th>WKN</th>
                    <th>Leave</th>
                    <th>Holiday</th>
                    <th>Att Day</th>
                    <th>Late</th>
                    <th>OT Hrs</th>
                    <th>OT Rate</th>
                    <th>OT Amt</th>
                    <th>HD Allow</th>
                    <th>Att Bonus</th>
                    <th>Other Deduct</th>
                    <th>Fine</th>
                    <th>TDS</th>
                    <th>Payable</th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $index => $r)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="employee-name">
                        {{ $r['employee_name'] ?? $r['employee'] ?? $r['employee_id'] ?? '-' }}<br>
                        <small class="employee-id">{{ $r['employee_id'] ?? '' }}</small>
                    </td>
                    <td>{{ $r['joining_date'] ?? $r['join_date'] ?? '-' }}</td>
                    <td>{{ $r['bank_info'] ?? $r['bank'] ?? '-' }}</td>
                    <td class="amount">{{ number_format($r['basic_salary'] ?? $r['base_salary'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['house_rent'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['medical_allowance'] ?? $r['medical'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['conveyance_allowance'] ?? $r['conveyance'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['food_allowance'] ?? $r['food'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['other_allowance'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['gross_salary'] ?? 0, 2) }}</td>
                    <td>{{ $r['working_days'] ?? 31 }}</td>
                    <td>{{ $r['working_days_including_weekends'] ?? $r['weekends'] ?? 4 }}</td>
                    <td>{{ $r['leave_days'] ?? 0 }}</td>
                    <td>{{ $r['holidays'] ?? 0 }}</td>
                    <td>{{ $r['attendance_days'] ?? $r['attended_days'] ?? 0 }}</td>
                    <td>{{ $r['late_days'] ?? $r['late_count'] ?? 0 }}</td>
                    <td class="amount">{{ number_format($r['ot_hours'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['ot_rate'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['ot_amount'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['hd_allowance'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['attendance_bonus'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['other_deduction'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['late_fine'] ?? $r['fine'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($r['tds_amount'] ?? $r['tds'] ?? $r['tax_payment'] ?? 0, 2) }}</td>
                    <td class="payable">{{ number_format($r['final_salary'] ?? $r['payable'] ?? 0, 2) }}</td>
                    <td><div class="signature-box"></div></td>
                </tr>
                @empty
                <tr>
                    <td colspan="27" style="text-align: center; padding: 30px; color: var(--text-secondary);">
                        No salary reports available for this period.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
