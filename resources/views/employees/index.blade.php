@extends('layouts.app')

@section('title', 'Select employee to change')

@section('content')
<style>
    :root {
        /* Default Dark Theme */
        --bg-primary: #2b2b2b;
        --bg-secondary: #3a3a3a;
        --bg-tertiary: #2f2f2f;
        --bg-quaternary: #252525;
        --bg-header: #393737;
        --text-primary: #f8f8f2;
        --text-secondary: #dac3c3;
        --border-color: #484848;
        --border-header: #555;
        --input-bg: #3a3a3a;
        --btn-primary: #0b7d5c;
        --btn-text: #fff;
    }

    .light-theme {
        /* Light Theme (Fresh Light Green) */
        --bg-primary: #ffffff;
        --bg-secondary: #c3e6cb; /* Hover green */
        --bg-tertiary: #ffffff;
        --bg-quaternary: #f0fdf4; /* Very pale green for alternating rows */
        --bg-header: #d1e7dd; /* Fresh light green header */
        --text-primary: #052c18; /* Darker green/black for better visibility */
        --text-secondary: #0f5132;
        --border-color: #badbcc; /* Soft green border */
        --border-header: #a3cfbb;
        --input-bg: #ffffff;
        --btn-primary: #198754;
        --btn-text: #fff;
    }

    body {
        background-color: var(--bg-primary) !important;
        color: var(--text-primary) !important;
    }

    .filter-form { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 16px; background: var(--bg-primary); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); color: var(--text-primary); }
    .filter-form label { font-weight: 600; font-size: 13px; color: var(--text-primary); margin-right: 4px; }
    .filter-form select { padding: 6px 10px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-primary); border-radius: 4px; font-size: 13px; }
    .filter-form button { padding: 6px 12px; background: var(--btn-primary); color: var(--btn-text); border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
    
    .admin-table { width: 100%; border-collapse: collapse; font-size: 14.5px; }
    .admin-table th { background: var(--bg-header); color: var(--text-primary); padding: 8px; text-align: left; border: 1px solid var(--border-header); position: sticky; top: 0; z-index: 5; }
    .admin-table td { padding: 6px; border: 1px solid var(--border-color); text-align: left; vertical-align: middle; color: var(--text-primary); }
    .admin-table tr:nth-child(even) { background: var(--bg-tertiary); }
    .admin-table tr:nth-child(odd) { background: var(--bg-quaternary); }
    .admin-table tr:hover { background: var(--bg-secondary); }
    
    .employee-img { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 8px; vertical-align: middle; border: 1px solid #555; }
    
    .dashboard-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; background: var(--bg-primary); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); color: var(--text-primary); }
    
    /* Modal Styles */
    #att-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; display: none; align-items: center; justify-content: center; }
    #att-modal { width: 90%; max-width: 1100px; height: 90%; max-height: 900px; background: #fff; border-radius: 6px; overflow: hidden; display: flex; flex-direction: column; }
    
    .theme-toggle-btn { padding: 6px 12px; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-primary); border-radius: 4px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 6px; }
    
    .icon-yes { color: #4caf50; font-weight: bold; }
    .icon-no { color: #f44336; font-weight: bold; }
    .text-synced { color: #4caf50; font-size: 0.85rem; }
</style>

<div class="dashboard-header">
    <h3 style="margin:0;color:var(--text-primary);display:flex;align-items:center;gap:8px;">
        Select employee to change
    </h3>
    <button id="theme-toggle" class="theme-toggle-btn" title="Toggle Light/Dark Theme">
        <span>&#9728;&#65039;</span> Theme
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background:#1b5e20; color:#e8f5e9; border:none; padding:10px; border-radius:4px; margin-bottom:15px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="background:#b71c1c; color:#ffebee; border:none; padding:10px; border-radius:4px; margin-bottom:15px;">{{ session('error') }}</div>
@endif

<!-- Top Controls -->
<div style="background:var(--bg-secondary); padding:15px; border-radius:4px; margin-bottom:20px; border:1px solid var(--border-color);">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <!-- Export -->
        <form action="{{ route('attendance.export') }}" method="POST" style="display:inline;">
            @csrf
            <input type="hidden" name="year" value="{{ date('Y') }}">
            <input type="hidden" name="month" value="{{ date('m') }}">
            <input type="hidden" name="export_data" value="1">
            <span style="color:var(--text-primary); font-weight:600; margin-right:10px;">Export:</span>
            <button type="submit" style="background:#5b6b79; color:#fff; border:none; padding:6px 15px; border-radius:4px; font-weight:600; cursor:pointer;">Export ZIP (with images)</button>
        </form>

        <!-- Import & Add -->
        <div style="display:flex; align-items:center; gap:15px;">
            <form action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data" style="display:flex; align-items:center;">
                @csrf
                <span style="color:var(--text-primary); font-weight:600; margin-right:10px;">Import:</span>
                <input type="file" name="import_file" style="background:var(--input-bg); border:1px solid var(--border-color); color:var(--text-primary); padding:3px; border-radius:4px 0 0 4px;">
                <button type="submit" style="background:#5b6b79; color:#fff; border:none; padding:6px 15px; border-radius:0 4px 4px 0; font-weight:600; cursor:pointer;">Import</button>
            </form>
            
            <a href="#" class="btn-update-db" style="background:#f57f17; color:#000; padding:8px 15px; border-radius:4px; font-weight:700; text-decoration:none;">Update Database</a>
            <a href="#" onclick="return openPopup('{{ route('employees.create', ['popup' => 1]) }}');" style="background:#212121; color:#fff; border:1px solid #444; padding:6px 15px; border-radius:4px; font-weight:600; text-decoration:none; margin-left:10px;">ADD EMPLOYEE</a>
        </div>
    </div>
</div>

<style>
<style>
    .layout-container {
        display: block;
    }
</style>

<!-- Filter Form -->
<form method="GET" style="background:#f0fdf4; border:1px solid #badbcc; padding:15px; border-radius:8px; display:flex; gap:20px; align-items:flex-end; margin-bottom:20px; flex-wrap:wrap;">
    <div style="flex:1; min-width:200px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#0f5132; margin-bottom:5px;">Search</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or ID..." class="form-control" style="width:100%; padding:8px 12px; border:1px solid #badbcc; border-radius:4px;">
    </div>
    
    <div style="width:150px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#0f5132; margin-bottom:5px;">Date</label>
        <input type="date" name="date" value="{{ request('date') }}" class="form-control" style="width:100%; padding:8px 12px; border:1px solid #badbcc; border-radius:4px;">
    </div>

    <div style="width:150px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#0f5132; margin-bottom:5px;">Status</label>
        <select name="active" class="form-select" style="width:100%; padding:8px 12px; border:1px solid #badbcc; border-radius:4px;">
            <option value="">All</option>
            <option value="yes" {{ request('active') == 'yes' ? 'selected' : '' }}>Active</option>
            <option value="no" {{ request('active') == 'no' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>

    <div style="width:150px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#0f5132; margin-bottom:5px;">Department</label>
        <select name="department" class="form-select" style="width:100%; padding:8px 12px; border:1px solid #badbcc; border-radius:4px;">
            <option value="">All</option>
            @foreach($departments ?? [] as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
    </div>

    <div style="width:150px;">
        <label style="display:block; font-weight:600; font-size:13px; color:#0f5132; margin-bottom:5px;">Designation</label>
        <select name="designation" class="form-select" style="width:100%; padding:8px 12px; border:1px solid #badbcc; border-radius:4px;">
            <option value="">All</option>
            @foreach($designations ?? [] as $desig)
                <option value="{{ $desig }}" {{ request('designation') == $desig ? 'selected' : '' }}>{{ $desig }}</option>
            @endforeach
        </select>
    </div>

    <div style="display:flex; gap:10px;">
        <button type="submit" style="background:#198754; color:#fff; border:none; padding:8px 20px; border-radius:4px; font-weight:600; cursor:pointer;">Filter</button>
        <a href="{{ route('employees.index') }}" style="background:#6c757d; color:#fff; text-decoration:none; padding:8px 20px; border-radius:4px; font-weight:600; display:inline-block;">Reset</a>
    </div>
</form>


<!-- Table -->
<div class="table-container" style="overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>EMPLOYEE ID</th>
                <th>NAME</th>
                <th>DEPARTMENT</th>
                <th>DESIGNATION</th>
                <th>MONTHLY SALARY</th>
                <th>IS ACTIVE</th>
                <th>TEMPLATE SYNCED</th>
                <th style="text-align:right;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $index => $emp)
            <tr>
                <td style="text-align:center;">{{ $loop->iteration }}</td>
                <td>
                    @php $img = $emp['face_image'] ?? null; @endphp
                    @if($img)
                        <img src="data:image/jpeg;base64,{{ $img }}" class="employee-img" alt="face">
                    @else
                        <div class="employee-img" style="display:inline-flex; align-items:center; justify-content:center; background:#333; color:#fff; font-size:0.7rem;">{{ strtoupper(substr($emp['name'] ?? 'N',0,1)) }}</div>
                    @endif
                    <a href="#" onclick="return openPopup('{{ route('employees.edit', ['id' => $emp['id'], 'popup' => 1]) }}');" style="color:var(--text-primary); text-decoration:none; font-weight:600;">
                        {{ $emp['employee_id'] ?? '-' }}
                    </a>
                </td>
                <td>
                    <a href="#" onclick="return openPopup('{{ route('employees.edit', ['id' => $emp['id'], 'popup' => 1]) }}');" style="color:var(--text-primary); text-decoration:none; font-weight:600;">
                        {{ $emp['name'] ?? '-' }}
                    </a>
                </td>
                <td>{{ $emp['department'] ?? '-' }}</td>
                <td>{{ $emp['designation'] ?? '-' }}</td>
                <td>{{ number_format($emp['monthly_salary'] ?? 0, 2) }}</td>
                <td style="text-align:center;">
                    @if(!empty($emp['is_active']))
                        <span class="icon-yes">✔</span>
                    @else
                        <span class="icon-no">✘</span>
                    @endif
                </td>
                <td>
                    <span class="text-synced">Synced</span>
                </td>
                <td style="text-align:right;">
                    <a href="#" onclick="return openPopup('{{ route('employees.edit', ['id' => $emp['id'], 'popup' => 1]) }}');" style="color:var(--btn-primary); text-decoration:none; margin-right:8px; font-weight:600;">Edit</a>
                    <form action="{{ route('employees.destroy', $emp['id']) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:none; border:none; color:#dc3545; cursor:pointer; font-weight:600; padding:0;">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align:center; padding: 30px; color: var(--text-secondary);">No employees found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div style="margin-top: 15px; color: var(--text-secondary); font-size: 0.9rem;">
        {{ count($employees) }} employees
    </div>
</div>
    </div>
</div>

<!-- Modal Overlay -->
<div id="att-modal-overlay">
  <div id="att-modal">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border-bottom:1px solid #eee;background:#fafafa;">
      <strong style="font-size:14px;color:#333;">Record</strong>
      <div style="display:flex;gap:8px;align-items:center;">
        <button id="att-modal-refresh" style="padding:6px 10px;border:1px solid #ddd;background:#fff;border-radius:4px;cursor:pointer;color:#333;">Refresh</button>
        <button id="att-modal-close" style="padding:6px 10px;border:1px solid #ddd;background:#fff;border-radius:4px;cursor:pointer;color:#333;">Close</button>
      </div>
    </div>
    <iframe id="att-modal-iframe" src="" style="border:0;width:100%;flex:1;background:#fff;"></iframe>
  </div>
</div>

<script>
// Theme Toggle Logic
(function(){
    var toggleBtn = document.getElementById('theme-toggle');
    var body = document.body;
    var themeKey = 'attendance_theme';
    
    // Load preference
    var savedTheme = localStorage.getItem(themeKey);
    if(savedTheme === 'light') {
        body.classList.add('light-theme');
    }
    
    toggleBtn.addEventListener('click', function(){
        body.classList.toggle('light-theme');
        var isLight = body.classList.contains('light-theme');
        localStorage.setItem(themeKey, isLight ? 'light' : 'dark');
    });
})();

// Modal Logic
function openPopup(url){
    var overlay = document.getElementById('att-modal-overlay');
    var iframe = document.getElementById('att-modal-iframe');
    if(overlay && iframe) {
        iframe.src = url;
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    return false;
}

document.getElementById('att-modal-close').addEventListener('click', function(){
    document.getElementById('att-modal-overlay').style.display = 'none';
    document.body.style.overflow = '';
});

document.getElementById('att-modal-refresh').addEventListener('click', function(){
    var f = document.getElementById('att-modal-iframe');
    f.src = f.src;
});

document.getElementById('att-modal-overlay').addEventListener('click', function(e){
    if (e.target === this) {
        this.style.display = 'none';
        document.body.style.overflow = '';
    }
});
</script>
@endsection
