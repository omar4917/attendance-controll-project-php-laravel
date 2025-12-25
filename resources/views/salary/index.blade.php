@extends('layouts.app')

@section('content')
<style>
    .salary-container {
        background: var(--bg-primary);
        color: var(--text-primary);
        padding: 20px;
        border-radius: 8px;
    }

    .salary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }

    .salary-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .add-salary-btn {
        background: var(--btn-primary);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        text-decoration: none;
        display: inline-block;
    }

    .search-bar {
        background: var(--bg-quaternary);
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 15px;
        display: flex;
        gap: 10px;
        align-items: center;
        border: 1px solid var(--border-color);
    }

    .search-bar input {
        flex: 1;
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--input-text);
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 13px;
    }

    .search-bar button {
        background: var(--btn-primary);
        color: white;
        border: none;
        padding: 6px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
    }

    .salary-table-container {
        background: var(--bg-primary);
        border-radius: 4px;
        overflow-x: auto;
        border: 1px solid var(--border-color);
    }

    .salary-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        min-width: 1400px;
    }

    .salary-table thead {
        background: var(--bg-header);
        color: #1f2937; /* Dark gray for visibility */
        border-bottom: 2px solid var(--border-color);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .salary-table th {
        padding: 10px 8px;
        text-align: left;
        font-weight: 600;
        border-right: 1px solid rgba(255,255,255,0.1);
        white-space: nowrap;
        font-size: 11px;
        text-transform: uppercase;
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
        padding: 8px;
        border-right: 1px solid var(--border-color);
        color: var(--text-primary);
        white-space: nowrap;
    }

    .salary-table td:last-child {
        border-right: none;
    }

    .action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 4px;
        cursor: pointer;
        margin: 0 2px;
        font-size: 12px;
    }

    .action-icon.edit {
        background: var(--btn-primary);
        color: white;
    }

    .action-icon.delete {
        background: #dc3545;
        color: white;
    }

    .checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .employee-id {
        color: var(--btn-primary);
        font-weight: 600;
    }

    .amount {
        text-align: right;
        font-family: 'Courier New', monospace;
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

<div class="salary-container">
    <div class="salary-header">
        <h2>Select salary statistic to change</h2>
        <a href="#" class="add-salary-btn">ADD SALARY STATISTIC</a>
    </div>

    @if(!empty($error))
        <div class="alert-error">API error: {{ $error }}</div>
    @endif
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <form method="get" action="{{ route('salary.index') }}" class="search-bar" style="display:flex; gap:15px; align-items:flex-end; background:var(--bg-quaternary);">
        <!-- Month Filter -->
        <div style="flex:0 0 350px;">
            <label style="font-size:11px; font-weight:700; display:block; margin-bottom:4px; text-transform:uppercase; color:var(--text-primary);">Month & Year</label>
            @php
                $dt = \Carbon\Carbon::createFromDate($year, $month, 1);
                $prev = $dt->copy()->subMonth();
                $next = $dt->copy()->addMonth();
            @endphp
            <div style="display:flex; gap:5px; align-items:center;">
                <!-- Prev Arrow -->
                <a href="{{ route('salary.index', ['month' => $prev->month, 'year' => $prev->year]) }}" 
                   class="btn btn-sm btn-outline-secondary"
                   style="padding:6px 12px; background:var(--input-bg); border:1px solid var(--border-color); border-radius:4px; color:var(--text-primary); text-decoration:none; font-weight:bold;">
                    &larr;
                </a>

                <!-- Month Dropdown -->
                <select name="month" onchange="this.form.submit()" 
                        style="width:120px; padding:6px 10px; border:1px solid var(--border-color); border-radius:4px; font-size:13px; background:var(--input-bg); color:var(--text-primary);">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>

                <!-- Year Dropdown -->
                <select name="year" onchange="this.form.submit()" 
                        style="width:80px; padding:6px 10px; border:1px solid var(--border-color); border-radius:4px; font-size:13px; background:var(--input-bg); color:var(--text-primary);">
                    @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <!-- Next Arrow -->
                <a href="{{ route('salary.index', ['month' => $next->month, 'year' => $next->year]) }}" 
                   class="btn btn-sm btn-outline-secondary"
                   style="padding:6px 12px; background:var(--input-bg); border:1px solid var(--border-color); border-radius:4px; color:var(--text-primary); text-decoration:none; font-weight:bold;">
                    &rarr;
                </a>
            </div>
        </div>

        <!-- JS Search -->
        <div style="flex:1;">
            <label style="font-size:11px; font-weight:700; display:block; margin-bottom:4px; text-transform:uppercase; color:var(--text-primary);">Search Employee</label>
            <div style="display:flex; gap:5px;">
                <input type="text" placeholder="🔍 Search by name or ID..." id="searchInput" onkeyup="filterTable()" 
                       style="width:100%; padding:6px 10px; border:1px solid var(--border-color); border-radius:4px; font-size:13px;">
            </div>
        </div>
        
        <!-- Reset -->
        <div>
            <a href="{{ route('salary.index') }}" style="padding:7px 15px; background:var(--bg-secondary); color:var(--text-primary); text-decoration:none; border-radius:4px; font-size:13px; border:1px solid var(--border-color); display:inline-block; height: 32px; line-height: 16px;">Reset</a>
        </div>
    </form>

    <div class="salary-table-container">
        <table class="salary-table" id="salaryTable">
            <thead>
                <tr>
                    <th style="width: 30px;">
                        <input type="checkbox" class="checkbox" onclick="toggleAll(this)">
                    </th>
                    <th style="width: 30px;">#</th>
                    <th>EMPLOYEE</th>
                    <th>NAME</th>
                    <th>MONTH</th>
                    <th>YEAR</th>
                    <th>BASIC SALARY</th>
                    <th>HOUSE RENT</th>
                    <th>ATT. BONUS</th>
                    <th>LATE FINE</th>
                    <th>OTHER DED.</th>
                    <th>GROSS SALARY</th>
                    <th>PAYABLE</th>
                    <th style="text-align: center;">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stats as $index => $s)
                    <tr>
                        <td>
                            <input type="checkbox" class="checkbox">
                        </td>
                        <td>{{ $index + 1 }}</td>
                        <td class="employee-id">
                            <a href="{{ route('salary.edit', $s['id'] ?? 0) }}" style="color: var(--btn-primary); text-decoration: none;">
                                {{ $s['employee'] ?? $s['employee_id'] ?? '-' }}
                            </a>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:8px;">
                                @if(!empty($s['face_image']))
                                    <img src="data:image/jpeg;base64,{{ $s['face_image'] }}" style="width:30px; height:30px; border-radius:50%; object-fit:cover; border:1px solid var(--border-color);">
                                @else
                                    <div style="width:30px; height:30px; border-radius:50%; background:var(--bg-tertiary); color:var(--text-primary); display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:bold;">
                                        {{ strtoupper(substr($s['employee_name'] ?? 'N', 0, 1)) }}
                                    </div>
                                @endif
                                <a href="{{ route('salary.edit', $s['id'] ?? 0) }}" style="color: var(--text-primary); text-decoration: none;">
                                    {{ $s['employee_name'] ?? 'N/A' }}
                                </a>
                            </div>
                        </td>
                        <td>{{ $s['month'] ?? '-' }}</td>
                        <td>{{ $s['year'] ?? '-' }}</td>
                        <td class="amount">{{ number_format($s['basic_salary'] ?? $s['base_salary'] ?? 0, 2) }}</td>
                        <td class="amount">{{ number_format($s['house_rent'] ?? $s['mobile_salary'] ?? 0, 2) }}</td>
                        <td class="amount">{{ number_format($s['attendance_bonus'] ?? 0, 2) }}</td>
                        <td class="amount">{{ number_format($s['late_fine'] ?? 0, 2) }}</td>
                        <td class="amount">{{ number_format($s['other_deduction'] ?? $s['other_fine'] ?? 0, 2) }}</td>
                        <td class="amount">{{ number_format($s['gross_salary'] ?? 0, 2) }}</td>
                        <td class="amount">{{ number_format($s['payable'] ?? 0, 2) }}</td>
                        <td style="text-align: center;">
                            <a href="{{ route('salary.edit', $s['id'] ?? 0) }}" class="action-icon edit" title="Edit">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form action="{{ route('salary.destroy', $s['id'] ?? 0) }}" method="POST" onsubmit="return confirm('Are you sure?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-icon delete" title="Delete" style="border: none;">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" style="text-align: center; padding: 30px; color: var(--text-secondary);">
                            No salary statistics available.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 15px; font-size: 12px; color: var(--text-secondary);">
        {{ count($stats) }} of {{ count($stats) }} selected
    </div>
</div>

<script>
function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('.salary-table tbody .checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function filterTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('salaryTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    }
}

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        filterTable();
    }
});
</script>
@endsection
