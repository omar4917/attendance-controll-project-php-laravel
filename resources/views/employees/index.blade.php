@extends('layouts.app')

@section('title', 'Select employee to change')

@section('content')
<style>
    /* Page-specific styles using global theme variables */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .page-title {
        font-size: 1.4rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .controls-bar {
        background: var(--bg-header);
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .filter-form {
        background: var(--bg-alternate);
        border: 1px solid var(--border-color);
        padding: 15px;
        border-radius: 8px;
        display: flex;
        gap: 20px;
        align-items: flex-end;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-group {
        min-width: 150px;
    }

    .filter-group.search {
        flex: 1;
        min-width: 200px;
    }

    .filter-label {
        display: block;
        font-weight: 600;
        font-size: 12px;
        color: var(--text-secondary);
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .filter-input, .filter-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: var(--input-bg);
        color: var(--text-primary);
        font-size: 14px;
    }

    .filter-input:focus, .filter-select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 2px var(--shadow);
    }

    .btn-action {
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
    }

    .btn-primary {
        background: #198754 !important;
        color: #ffffff !important;
    }

    .btn-primary:hover {
        background: #157347 !important;
    }

    .btn-secondary {
        background: #f8f9fa !important;
        color: #333333 !important;
        border: 1px solid #dee2e6;
    }

    .btn-secondary:hover {
        background: #e9ecef !important;
    }

    .btn-dark {
        background: #212529;
        color: #fff;
    }

    .btn-warning {
        background: #f57f17;
        color: #000;
    }

    /* Table */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        background: var(--bg-card);
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .data-table th {
        background: var(--bg-header);
        color: var(--text-primary);
        padding: 12px 10px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        border-bottom: 2px solid var(--border-color);
    }

    .th-sortable {
        color: var(--text-primary);
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: color 0.2s;
    }

    .th-sortable:hover {
        color: var(--accent);
    }

    .th-sortable.active {
        color: var(--accent);
    }

    .data-table td {
        padding: 10px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
        vertical-align: middle;
    }

    .data-table tbody tr:nth-child(even) {
        background: var(--bg-alternate);
    }

    .data-table tbody tr:hover {
        background: var(--bg-hover);
    }

    .employee-img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--border-color);
    }

    .employee-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--accent);
        color: var(--btn-primary-text);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
    }

    .status-icon-yes { color: #22c55e; font-weight: bold; }
    .status-icon-no { color: #ef4444; font-weight: bold; }
    .text-synced { color: #22c55e; font-size: 0.85rem; font-weight: 500; }

    .link-primary {
        color: var(--accent);
        text-decoration: none;
        font-weight: 600;
    }

    .link-primary:hover {
        text-decoration: underline;
    }

    .link-filter {
        color: var(--text-primary);
        text-decoration: none;
        cursor: pointer;
        transition: color 0.2s;
    }

    .link-filter:hover {
        color: var(--accent);
        text-decoration: underline;
    }

    .link-danger {
        color: #ef4444;
        background: none;
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
    }

    .results-count {
        margin-top: 15px;
        color: var(--text-muted);
        font-size: 0.9rem;
    }

    /* Modal */
    #att-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
    }

    #att-modal {
        width: 90%;
        max-width: 1100px;
        height: 90%;
        max-height: 900px;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border-bottom: 1px solid #eee;
        background: #f8f9fa;
    }
</style>

<div class="page-header">
    <h2 class="page-title">{{ __('messages.employee_name') }}</h2>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background:var(--bg-alternate); color:var(--text-secondary); border:1px solid var(--border-color); padding:12px; border-radius:6px; margin-bottom:15px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca; padding:12px; border-radius:6px; margin-bottom:15px;">{{ session('error') }}</div>
@endif

<!-- Unified Action Bar -->
<div class="unified-action-bar">
    <!-- Export Group -->
    <form action="{{ route('attendance.export') }}" method="POST" class="action-group" style="margin:0;">
        @csrf
        <input type="hidden" name="year" value="{{ date('Y') }}">
        <input type="hidden" name="month" value="{{ date('m') }}">
        <input type="hidden" name="export_data" value="1">
        <input type="hidden" name="type" value="employees">
        
        <span class="action-label"><i class="bi bi-file-earmark-arrow-down"></i> {{ __('messages.export') }}:</span>
        <button type="submit" class="btn-action-primary" style="background:#0d6efd;">{{ __('messages.export') }} ZIP</button>
    </form>

    <div class="divider-vertical"></div>

    <!-- Import Group -->
    <form action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data" class="action-group" style="margin:0;">
        @csrf
        <input type="hidden" name="type" value="employees">
        <span class="action-label"><i class="bi bi-cloud-upload"></i> {{ __('messages.import') }}:</span>
        <input type="file" name="import_file" class="action-input-file" style="max-width:200px;">
        <button type="submit" class="btn-action-secondary">{{ __('messages.upload') }}</button>
    </form>

    <div class="divider-vertical"></div>

    <!-- Add Employee -->
    <div class="action-group">
        <a href="#" onclick="return openPopup('{{ route('employees.create', ['popup' => 1]) }}');" class="btn-action-primary" style="background:#212529;">
            <i class="bi bi-person-plus"></i> {{ __('messages.add_employee') }}
        </a>
    </div>
</div>

<!-- Filter Form -->
<form method="GET" class="filter-form">
    <div class="filter-group search">
        <label class="filter-label">{{ __('messages.search') }}</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_placeholder') }}" class="filter-input">
    </div>
    
    <div class="filter-group">
        <label class="filter-label">{{ __('messages.date') }}</label>
        <input type="date" name="date" value="{{ request('date') }}" class="filter-input">
    </div>

    <div class="filter-group">
        <label class="filter-label">{{ __('messages.status') }}</label>
        <select name="active" class="filter-select">
            <option value="">All</option>
            <option value="yes" {{ request('active') == 'yes' ? 'selected' : '' }}>Active</option>
            <option value="no" {{ request('active') == 'no' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>

    <div class="filter-group">
        <label class="filter-label">{{ __('messages.department') }}</label>
        <select name="department" class="filter-select">
            <option value="">All</option>
            @foreach($departments ?? [] as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
    </div>

    <div class="filter-group">
        <label class="filter-label">{{ __('messages.designation') }}</label>
        <select name="designation" class="filter-select">
            <option value="">All</option>
            @foreach($designations ?? [] as $desig)
                <option value="{{ $desig }}" {{ request('designation') == $desig ? 'selected' : '' }}>{{ $desig }}</option>
            @endforeach
        </select>
    </div>

    <div style="display:flex; gap:8px; align-items:flex-end;">
        <button type="submit" class="btn-action btn-primary">{{ __('messages.filter') }}</button>
        <a href="{{ route('employees.index') }}" class="btn-action btn-secondary">{{ __('messages.cancel') }}</a>
    </div>
</form>

<!-- Table -->
@php
    $currentSort = request('sort', 'name');
    $currentDir = request('dir', 'asc');
    $toggleDir = $currentDir === 'asc' ? 'desc' : 'asc';
    
    // Build base URL with existing filters
    $sortParams = request()->except(['sort', 'dir']);
@endphp
    <!-- Top Pagination -->
    <div style="margin-bottom: 10px; display: flex; align-items: center; justify-content: flex-end; gap: 15px; color: var(--text-secondary); font-size: 13px; font-weight: 600;">
        <div>
            Showing {{ (($page ?? 1) - 1) * ($perPage ?? 50) + 1 }} - {{ min(($page ?? 1) * ($perPage ?? 50), $totalCount ?? count($employees)) }} of {{ $totalCount ?? count($employees) }} records
        </div>
        
        <div style="display: flex; align-items: center; gap: 8px;">
            <select onchange="window.location.href='{{ request()->fullUrlWithQuery(['limit' => '']) }}'.replace('limit=', 'limit=' + this.value).replace('&page={{ $page ?? 1 }}', '&page=1')" 
                    class="form-select form-select-sm" 
                    style="width: auto; height: 28px; font-size: 12px; padding: 2px 8px;">
                @foreach([50, 75, 100, 200] as $l)
                    <option value="{{ $l }}" {{ ($perPage ?? 50) == $l ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>

            {{-- Prev Arrow --}}
            @if(($page ?? 1) > 1)
                <a href="{{ request()->fullUrlWithQuery(['page' => ($page ?? 1) - 1]) }}" 
                   class="btn btn-sm btn-light border" 
                   style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; padding: 0;">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @else
                <button class="btn btn-sm btn-light border" disabled style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; padding: 0; opacity: 0.5;">
                    <i class="bi bi-chevron-left"></i>
                </button>
            @endif

            {{-- Next Arrow --}}
            @if(($page ?? 1) < ($lastPage ?? 1))
                <a href="{{ request()->fullUrlWithQuery(['page' => ($page ?? 1) + 1]) }}" 
                   class="btn btn-sm btn-light border" 
                   style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; padding: 0;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <button class="btn btn-sm btn-light border" disabled style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; padding: 0; opacity: 0.5;">
                    <i class="bi bi-chevron-right"></i>
                </button>
            @endif
        </div>
    </div>
<div style="overflow-x:auto;">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40px;">#</th>
                <th>
                    <a href="{{ route('employees.index', array_merge($sortParams, ['sort' => 'employee_id', 'dir' => $currentSort === 'employee_id' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'employee_id' ? 'active' : '' }}">
                        {{ __('messages.employee_id') }} {!! $currentSort === 'employee_id' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th>
                    <a href="{{ route('employees.index', array_merge($sortParams, ['sort' => 'name', 'dir' => $currentSort === 'name' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'name' ? 'active' : '' }}">
                        {{ __('messages.employee_name') }} {!! $currentSort === 'name' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th>
                    <a href="{{ route('employees.index', array_merge($sortParams, ['sort' => 'organization_name', 'dir' => $currentSort === 'organization_name' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'organization_name' ? 'active' : '' }}">
                        {{ __('messages.organization') }} {!! $currentSort === 'organization_name' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th>
                    <a href="{{ route('employees.index', array_merge($sortParams, ['sort' => 'department', 'dir' => $currentSort === 'department' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'department' ? 'active' : '' }}">
                        {{ __('messages.department') }} {!! $currentSort === 'department' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th>
                    <a href="{{ route('employees.index', array_merge($sortParams, ['sort' => 'designation', 'dir' => $currentSort === 'designation' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'designation' ? 'active' : '' }}">
                        {{ __('messages.designation') }} {!! $currentSort === 'designation' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th>
                    <a href="{{ route('employees.index', array_merge($sortParams, ['sort' => 'monthly_salary', 'dir' => $currentSort === 'monthly_salary' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'monthly_salary' ? 'active' : '' }}">
                        {{ __('messages.salary') }} {!! $currentSort === 'monthly_salary' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th>
                    <a href="{{ route('employees.index', array_merge($sortParams, ['sort' => 'is_active', 'dir' => $currentSort === 'is_active' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'is_active' ? 'active' : '' }}">
                        {{ __('messages.status') }} {!! $currentSort === 'is_active' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th>Template Synced</th>
                <th style="text-align:right;">{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $index => $emp)
            <tr>
                <td style="text-align:center;">{{ (($page ?? 1) - 1) * 50 + $loop->iteration }}</td>
                <td>
                    @php $img = $emp['face_image'] ?? null; @endphp
                    @if($img)
                        <img src="data:image/jpeg;base64,{{ $img }}" class="employee-img" alt="face">
                    @else
                        <div class="employee-avatar">{{ strtoupper(substr($emp['name'] ?? 'N',0,1)) }}</div>
                    @endif
                    <a href="#" onclick="return openPopup('{{ route('employees.edit', ['id' => $emp['id'], 'popup' => 1]) }}');" class="link-primary" style="margin-left:8px;">
                        {{ $emp['employee_id'] ?? '-' }}
                    </a>
                </td>
                <td>
                    <a href="#" onclick="return openPopup('{{ route('employees.edit', ['id' => $emp['id'], 'popup' => 1]) }}');" class="link-primary">
                        {{ $emp['name'] ?? '-' }}
                    </a>
                </td>
                <td style="font-size:12px;">
                    @if($emp['organization_name'] ?? null)
                        <a href="{{ route('employees.index', ['organization_id' => $emp['organization_id'] ?? '']) }}" 
                           class="link-filter" style="color:var(--accent);"
                           title="Filter by this organization">{{ $emp['organization_name'] }}</a>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($emp['department'] ?? null)
                        <a href="{{ route('employees.index', ['department' => $emp['department']]) }}" 
                           class="link-filter"
                           title="Filter by this department">{{ $emp['department'] }}</a>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($emp['designation'] ?? null)
                        <a href="{{ route('employees.index', ['designation' => $emp['designation']]) }}" 
                           class="link-filter"
                           title="Filter by this designation">{{ $emp['designation'] }}</a>
                    @else
                        -
                    @endif
                </td>
                <td>{{ number_format($emp['monthly_salary'] ?? 0, 2) }}</td>
                <td style="text-align:center;">
                    @if(!empty($emp['is_active']))
                        <span class="status-icon-yes">✔</span>
                    @else
                        <span class="status-icon-no">✘</span>
                    @endif
                </td>
                <td>
                    <span class="text-synced">Synced</span>
                </td>
                <td style="text-align:right;">
                    <a href="#" onclick="return openPopup('{{ route('employees.edit', ['id' => $emp['id'], 'popup' => 1]) }}');" class="link-primary" style="margin-right:10px;">Edit</a>
                    <form action="{{ route('employees.destroy', $emp['id']) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="link-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align:center; padding: 40px; color: var(--text-muted);">No employees found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    <div style="margin-top: 15px; display: flex; align-items: center; justify-content: space-between; color: var(--text-secondary); font-size: 13px; font-weight: 600;">
        <div style="margin-top: 15px; display: flex; align-items: center; justify-content: space-between; color: var(--text-secondary); font-size: 13px; font-weight: 600;">
            <div>
                Showing {{ (($page ?? 1) - 1) * 50 + 1 }} - {{ min(($page ?? 1) * 50, $totalCount ?? count($employees)) }} of {{ $totalCount ?? count($employees) }} employees
            </div>
            
            <div style="display: flex; gap: 5px;">
                {{-- Prev Arrow --}}
                @if(($page ?? 1) > 1)
                    <a href="{{ request()->fullUrlWithQuery(['page' => ($page ?? 1) - 1]) }}" 
                       class="btn btn-sm btn-light border" 
                       style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; padding: 0;">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                @else
                    <button class="btn btn-sm btn-light border" disabled style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; padding: 0; opacity: 0.5;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                @endif

                {{-- Next Arrow --}}
                @if(($page ?? 1) < ($lastPage ?? 1))
                    <a href="{{ request()->fullUrlWithQuery(['page' => ($page ?? 1) + 1]) }}" 
                       class="btn btn-sm btn-light border" 
                       style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; padding: 0;">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                @else
                    <button class="btn btn-sm btn-light border" disabled style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; padding: 0; opacity: 0.5;">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Overlay -->
<div id="att-modal-overlay">
  <div id="att-modal">
    <div class="modal-header">
      <strong style="font-size:14px;color:#333;">Record</strong>
      <div style="display:flex;gap:8px;">
        <button id="att-modal-refresh" class="btn-action btn-secondary">Refresh</button>
        <button id="att-modal-close" class="btn-action btn-secondary">Close</button>
      </div>
    </div>
    <iframe id="att-modal-iframe" src="" style="border:0;width:100%;flex:1;background:#fff;"></iframe>
  </div>
</div>

<script>
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
