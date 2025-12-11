@extends('layouts.app')

@section('content')
@extends('layouts.app')

@section('content')
<style>
    /* Dashboard Theme Styles */
    .salary-report-container {
        font-family: sans-serif;
        color: var(--text-primary);
        background-color: var(--bg-primary);
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 10px;
    }

    .page-title {
        font-size: 24px;
        font-weight: bold;
        color: var(--text-primary);
        margin: 0;
    }

    .filter-bar {
        display: flex;
        gap: 10px;
        align-items: center;
        background: var(--bg-secondary);
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 20px;
        border: 1px solid var(--border-color);
    }

    .filter-label {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 600;
    }

    .filter-select {
        background: var(--bg-primary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 13px;
    }

    .btn-filter {
        background: var(--bg-tertiary);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        padding: 5px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
    }
    .btn-filter:hover { background: var(--bg-quaternary); }

    .btn-download {
        background: #198754; /* Bootstrap success green */
        color: #fff;
        border: 1px solid #157347;
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 13px;
        display: inline-block;
        margin-bottom: 20px;
    }
    .btn-download:hover { background: #157347; }

    .rules-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .rule-box {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 4px;
        padding: 15px;
    }

    .rule-title {
        color: #d63384; /* Pinkish/Red for headers, or use var(--text-primary) */
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .rule-list {
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 12px;
        color: var(--text-secondary);
    }
    .rule-list li { margin-bottom: 4px; }

    .salary-table-container {
        overflow-x: auto;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 4px;
    }

    .salary-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        white-space: nowrap;
    }

    .salary-table th {
        background: var(--bg-header);
        color: var(--text-primary);
        padding: 8px 6px;
        text-align: left;
        border-bottom: 1px solid var(--border-header);
        font-weight: 600;
    }

    .salary-table td {
        padding: 6px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
    }

    .salary-table tr:hover td {
        background: var(--bg-secondary);
    }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    
    .col-highlight { background: #e8f0fe; color: #000; } /* Light blue highlight for payable */
    
    .alert-error {
        background: #f8d7da;
        color: #842029;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 20px;
        border: 1px solid #f5c2c7;
    }
</style>

<div class="salary-report-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Salary Report - {{ $monthName }}</h1>
            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 5px;">Data generated instantly from database</div>
        </div>
        <a href="{{ route('reports.index') }}" style="color: #0d6efd; text-decoration: none; font-size: 13px;">← Back to Admin</a>
    </div>

    @if(!empty($error))
        <div class="alert-error">API Error: {{ $error }}</div>
    @endif

    <form method="GET" class="filter-bar">
        <span class="filter-label">Month:</span>
        <select name="month" class="filter-select">
            @foreach($months as $m)
                <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
            @endforeach
        </select>

        <span class="filter-label">Year:</span>
        <select name="year" class="filter-select">
            @foreach($years as $y)
                <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>

        <span class="filter-label">Department:</span>
        <select name="department" class="filter-select">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>

        <button type="submit" class="btn-filter">Filter</button>
    </form>

    <a href="#" class="btn-download">Download PDF</a>

    <div class="rules-container">
        <div class="rule-box">
            <div class="rule-title">⚠️ Bonus Rules</div>
            <ul class="rule-list">
                <li>• Perfect Attendance: {{ $defaults['attendance_bonus'] ?? '0' }} BDT (0 late days)</li>
                <li>• Manual Bonus: Added by admin</li>
            </ul>
        </div>
        <div class="rule-box">
            <div class="rule-title">⚠️ Fine Rules</div>
            <ul class="rule-list">
                <li>• Late Fine: 1 day salary per {{ $defaults['late_needed'] ?? '3' }} late days (Monthly Salary ÷ 30)</li>
                <li>• Manual Fines: Added by admin</li>
            </ul>
        </div>
    </div>

    <div class="salary-table-container">
        <table class="salary-table">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Employee</th>
                    <th>Join Date</th>
                    <th>Bank</th>
                    <th class="text-right">Basic</th>
                    <th class="text-right">House Rent</th>
                    <th class="text-right">Medical</th>
                    <th class="text-right">Conv</th>
                    <th class="text-right">Food</th>
                    <th class="text-right">Other Allow</th>
                    <th class="text-right">Gross</th>
                    <th class="text-center">WD</th>
                    <th class="text-center">WKN</th>
                    <th class="text-center">Leave</th>
                    <th class="text-center">Holiday</th>
                    <th class="text-center">Att Day</th>
                    <th class="text-center">Late</th>
                    <th class="text-right">OT Hrs</th>
                    <th class="text-right">OT Rate</th>
                    <th class="text-right">OT Amt</th>
                    <th class="text-right">HD Allow</th>
                    <th class="text-right">Att Bonus</th>
                    <th class="text-right">Other Deduct</th>
                    <th class="text-right">Fine</th>
                    <th class="text-right">TDS</th>
                    <th class="text-right col-highlight">Payable</th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaryData as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div style="font-weight:bold;">{{ $row['employee_name'] }}</div>
                            <div style="font-size:10px; color:#aaa;">{{ $row['employee_id'] }}</div>
                        </td>
                        <td>{{ $row['joining_date'] ?? '-' }}</td>
                        <td>{{ $row['bank_info'] ?? '-' }}</td>
                        <td class="text-right">{{ number_format((float)$row['basic_salary'], 2) }}</td>
                        <td class="text-right">{{ number_format((float)$row['house_rent'], 2) }}</td>
                        <td class="text-right">{{ number_format((float)$row['medical_allowance'], 2) }}</td>
                        <td class="text-right">{{ number_format((float)$row['conveyance_allowance'], 2) }}</td>
                        <td class="text-right">{{ number_format((float)$row['food_allowance'], 2) }}</td>
                        <td class="text-right">{{ number_format((float)$row['other_allowance'], 2) }}</td>
                        <td class="text-right" style="font-weight:bold;">{{ number_format((float)$row['gross_salary'], 2) }}</td>
                        <td class="text-center">{{ $row['working_days'] }}</td>
                        <td class="text-center">{{ $row['working_days_including_weekends'] - $row['working_days'] }}</td>
                        <td class="text-center">{{ $row['leave_days'] }}</td>
                        <td class="text-center">{{ $row['holidays'] }}</td>
                        <td class="text-center">{{ $row['attendance_days'] }}</td>
                        <td class="text-center" style="color: #dc3545;">{{ $row['late_days'] }}</td>
                        <td class="text-right">{{ $row['ot_hours'] ?? '0:00' }}</td>
                        <td class="text-right">{{ number_format((float)$row['ot_rate'], 2) }}</td>
                        <td class="text-right">{{ number_format((float)$row['ot_amount'], 2) }}</td>
                        <td class="text-right">{{ number_format((float)$row['hd_allowance'], 2) }}</td>
                        <td class="text-right" style="color: #198754;">{{ number_format((float)$row['attendance_bonus'], 2) }}</td>
                        <td class="text-right" style="color: #dc3545;">{{ number_format((float)$row['other_deduction'], 2) }}</td>
                        <td class="text-right" style="color: #dc3545;">{{ number_format((float)$row['late_fine'], 2) }}</td>
                        <td class="text-right" style="color: #dc3545;">{{ number_format((float)$row['tds_amount'], 2) }}</td>
                        <td class="text-right col-highlight" style="font-weight:bold;">{{ number_format((float)$row['final_salary'], 2) }}</td>
                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="27" class="text-center" style="padding: 20px; color: #aaa;">No salary data found for this month.</td>
                    </tr>
                @endforelse
                
                <!-- Totals Row -->
                @if(count($salaryData) > 0)
                    <tr style="background: #333; font-weight: bold;">
                        <td colspan="4" class="text-right">TOTALS</td>
                        <td class="text-right">{{ number_format((float)($totals['basic_salary'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float)($totals['house_rent'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float)($totals['medical_allowance'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float)($totals['conveyance_allowance'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float)($totals['food_allowance'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float)($totals['other_allowance'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float)($totals['gross_salary'] ?? 0), 2) }}</td>
                        <td colspan="4"></td>
                        <td class="text-center">{{ $totals['attendance_days'] ?? 0 }}</td>
                        <td class="text-center">{{ $totals['late_days'] ?? 0 }}</td>
                        <td></td>
                        <td></td>
                        <td class="text-right">{{ number_format((float)($totals['ot_amount'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float)($totals['hd_allowance'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float)($totals['attendance_bonus'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float)($totals['other_deduction'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float)($totals['late_fine'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float)($totals['tds_amount'] ?? 0), 2) }}</td>
                        <td class="text-right col-highlight">{{ number_format((float)($totals['final_salary'] ?? 0), 2) }}</td>
                        <td></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
