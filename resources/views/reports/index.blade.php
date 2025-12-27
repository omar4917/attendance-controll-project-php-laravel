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
        background: #ffffff;
        border: 1px solid #ced4da;
        color: #333333;
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 12px;
        min-width: 120px;
    }

    .filter-bar select option {
        color: #333333;
        background: #ffffff;
    }

    .filter-bar .btn-filter {
        background: #198754 !important;
        color: #ffffff !important;
        padding: 6px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
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
        color: #1f2937; /* Dark gray for visibility */
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
        background: var(--bg-header);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .salary-table th {
        padding: 12px 6px;
        text-align: center;
        font-weight: 600;
        color: #1f2937; /* Dark gray for visibility */
        border-bottom: 2px solid var(--border-color);
        white-space: nowrap;
        font-size: 10px;
        text-transform: uppercase;
    }

    .salary-table th a.th-sortable {
        color: #1f2937; /* Dark gray */
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        transition: color 0.2s;
    }

    .salary-table th a.th-sortable:hover {
        color: var(--accent);
    }

    .salary-table th a.th-sortable.active {
        color: var(--accent);
    }

    .salary-table th:last-child {
        border-right: none;
    }

    .salary-table tbody tr {
        border-bottom: 1px solid var(--border-color);
    }

    .salary-table tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    .salary-table tbody tr:nth-child(odd) {
        background: #ffffff;
    }

    .salary-table tbody tr:hover {
        background: #e9ecef;
    }

    .salary-table td {
        padding: 8px 6px;
        border-right: 1px solid #dee2e6;
        color: #333333 !important;
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
        @if($isSuperAdmin ?? false)
        <h1><i class="bi bi-graph-up-arrow" style="margin-right:8px;"></i>System Salary Report - {{ $monthName ?? 'December' }} {{ $year ?? date('Y') }}</h1>
        <div class="report-subtitle">📊 Cross-organization salary overview</div>
        @else
        <h1>💰 Salary Report - {{ $monthName ?? 'December' }} {{ $year ?? date('Y') }}</h1>
        <div class="report-subtitle">📋 Your organization's salary data</div>
        @endif
        <div class="header-actions">
            <a href="{{ route('attendance.index') }}" class="btn-back">← Back to Admin</a>
            <button type="button" class="btn-check">✓</button>
            @if($isSuperAdmin ?? false)
            <button type="button" class="btn-delete">Delete salary from database</button>
            @endif
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

    <!-- Unified Action Bar -->
    @php
        // Build Laravel route for salary PDF download
        $pdfParams = [
            'month' => $month ?? date('n'),
            'year' => $year ?? date('Y'),
        ];
        
        if (!empty(request('department'))) {
            $pdfParams['department'] = request('department');
        }
        
        $orgIdForPdf = $orgId ?? session('organization_id');
        if ($orgIdForPdf) {
            $pdfParams['organization_id'] = $orgIdForPdf;
        }
        
        $pdfUrl = route('reports.salary.pdf', $pdfParams);
    @endphp
    <div class="unified-action-bar" style="margin-bottom:15px;">
        <div class="action-group">
            <span class="action-label"><i class="bi bi-file-pdf"></i> Download:</span>
            <a href="{{ $pdfUrl }}" target="_blank" class="btn-action-primary" style="background:#198754;"><i class="bi bi-download"></i> Salary Report PDF</a>
        </div>
    </div>

    @if(!empty($error))
        <div class="alert-error">API error: {{ $error }}</div>
    @endif

    <!-- Salary Table -->
    @php
        $currentSort = request('sort', 'employee_name');
        $currentDir = request('dir', 'asc');
    @endphp
    <div class="table-container">
        <table class="salary-table">
            <thead>
                <tr>
                    <th>SN</th>
                    <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'employee_name', 'dir' => $currentSort == 'employee_name' && $currentDir == 'asc' ? 'desc' : 'asc']) }}" class="th-sortable {{ $currentSort == 'employee_name' ? 'active' : '' }}">Employee {!! $currentSort == 'employee_name' ? ($currentDir == 'asc' ? '▲' : '▼') : '' !!}</a></th>
                    <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'joining_date', 'dir' => $currentSort == 'joining_date' && $currentDir == 'asc' ? 'desc' : 'asc']) }}" class="th-sortable {{ $currentSort == 'joining_date' ? 'active' : '' }}">Join Date {!! $currentSort == 'joining_date' ? ($currentDir == 'asc' ? '▲' : '▼') : '' !!}</a></th>
                    <th>Bank</th>
                    <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'basic_salary', 'dir' => $currentSort == 'basic_salary' && $currentDir == 'asc' ? 'desc' : 'asc']) }}" class="th-sortable {{ $currentSort == 'basic_salary' ? 'active' : '' }}">Basic {!! $currentSort == 'basic_salary' ? ($currentDir == 'asc' ? '▲' : '▼') : '' !!}</a></th>
                    <th>House Rent</th>
                    <th>Medical</th>
                    <th>Conv</th>
                    <th>Food</th>
                    <th>Other Allow</th>
                    <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'gross_salary', 'dir' => $currentSort == 'gross_salary' && $currentDir == 'asc' ? 'desc' : 'asc']) }}" class="th-sortable {{ $currentSort == 'gross_salary' ? 'active' : '' }}">Gross {!! $currentSort == 'gross_salary' ? ($currentDir == 'asc' ? '▲' : '▼') : '' !!}</a></th>
                    <th>WD</th>
                    <th>WKN</th>
                    <th>Leave</th>
                    <th>Holiday</th>
                    <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'attendance_days', 'dir' => $currentSort == 'attendance_days' && $currentDir == 'asc' ? 'desc' : 'asc']) }}" class="th-sortable {{ $currentSort == 'attendance_days' ? 'active' : '' }}">Att Day {!! $currentSort == 'attendance_days' ? ($currentDir == 'asc' ? '▲' : '▼') : '' !!}</a></th>
                    <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'late_days', 'dir' => $currentSort == 'late_days' && $currentDir == 'asc' ? 'desc' : 'asc']) }}" class="th-sortable {{ $currentSort == 'late_days' ? 'active' : '' }}">Late {!! $currentSort == 'late_days' ? ($currentDir == 'asc' ? '▲' : '▼') : '' !!}</a></th>
                    <th>OT Hrs</th>
                    <th>OT Rate</th>
                    <th>OT Amt</th>
                    <th>HD Allow</th>
                    <th>Att Bonus</th>
                    <th>Other Deduct</th>
                    <th>Fine</th>
                    <th>TDS</th>
                    <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'final_salary', 'dir' => $currentSort == 'final_salary' && $currentDir == 'asc' ? 'desc' : 'asc']) }}" class="th-sortable {{ $currentSort == 'final_salary' ? 'active' : '' }}">Payable {!! $currentSort == 'final_salary' ? ($currentDir == 'asc' ? '▲' : '▼') : '' !!}</a></th>
                    <th>Signature</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $index => $r)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="employee-name">
                        <div style="display:flex; align-items:center; gap:8px;">
                            @if(!empty($r['face_image']))
                                <img src="data:image/jpeg;base64,{{ $r['face_image'] }}" style="width:30px; height:30px; border-radius:50%; object-fit:cover; border:1px solid #ccc;">
                            @else
                                <div style="width:30px; height:30px; border-radius:50%; background:var(--bg-tertiary); color:var(--text-primary); display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:bold;">
                                    {{ strtoupper(substr($r['employee_name'] ?? 'N', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                {{ $r['employee_name'] ?? $r['employee'] ?? $r['employee_id'] ?? '-' }}<br>
                                <small class="employee-id">{{ $r['employee_id'] ?? '' }}</small>
                            </div>
                        </div>
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
