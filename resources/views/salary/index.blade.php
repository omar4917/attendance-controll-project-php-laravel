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
        background: var(--btn-primary);
        color: white;
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

    <div class="search-bar">
        <input type="text" placeholder="🔍 Search" id="searchInput">
        <button onclick="filterTable()">Search</button>
    </div>

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
                    <th>STAMP</th>
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
                            <a href="{{ route('salary.edit', $s['id'] ?? 0) }}" style="color: var(--text-primary); text-decoration: none;">
                                {{ $s['employee_name'] ?? 'N/A' }}
                            </a>
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
                        <td class="amount">{{ number_format($s['stamp'] ?? $s['tax_payable'] ?? 0, 2) }}</td>
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
