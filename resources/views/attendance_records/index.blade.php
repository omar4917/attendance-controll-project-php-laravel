@extends('layouts.app')

@section('content')
<style>
    /* Use global variables from layout */
    .admin-table-container {
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 4px;
        overflow-x: auto;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .admin-table th {
        background: var(--bg-header);
        color: var(--text-primary);
        padding: 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid var(--border-header);
        white-space: nowrap;
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
    .admin-table td {
        padding: 12px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
        color: var(--text-primary);
    }
    .admin-table tr:hover {
        background: var(--bg-secondary);
    }
    .admin-table tr:nth-child(even) {
        background: var(--bg-quaternary);
    }
    
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    /* Status colors can remain specific or use variables if we define them globally */
    .status-present { background: #d1e7dd; color: #0f5132; }
    .status-absent { background: #f8d7da; color: #842029; }
    .status-late { background: #fff3cd; color: #664d03; }
    .status-leave { background: #cff4fc; color: #055160; }
    
    .thumb-img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid var(--border-color);
        cursor: pointer;
        transition: transform 0.2s;
        background: #fff;
    }
    .thumb-img:hover {
        transform: scale(2.5);
        z-index: 10;
        position: relative;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        border-color: var(--btn-primary);
    }
    
    .action-link {
        color: var(--btn-primary);
        text-decoration: none;
        margin-right: 8px;
        font-weight: 600;
        cursor: pointer;
    }
    .action-link:hover { text-decoration: underline; }
    
    .delete-btn {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-weight: 600;
        padding: 0;
    }
    .delete-btn:hover { text-decoration: underline; }
    
    .custom-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: var(--btn-primary);
    }
    
    /* Page Header */
    .page-header {
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 20px;
        background: var(--bg-primary);
        padding: 15px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    /* Sidebar Layout */
    .layout-container {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }
    .sidebar-filter {
        width: 250px;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 4px;
        padding: 0;
        flex-shrink: 0;
    }
    .sidebar-header {
        background: #5b80b2; /* Adjust to match theme or user preference */
        color: #fff;
        padding: 10px 15px;
        font-weight: 600;
        border-radius: 4px 4px 0 0;
    }
    .filter-group {
        padding: 15px;
        border-bottom: 1px solid var(--border-color);
    }
    .filter-group h4 {
        margin: 0 0 10px 0;
        font-size: 14px;
        color: var(--text-primary);
        font-weight: 600;
    }
    .filter-link {
        display: block;
        padding: 5px 0;
        color: var(--btn-primary); /* Greenish */
        text-decoration: none;
        font-size: 13px;
    }
    .filter-link:hover {
        text-decoration: underline;
    }
    .filter-link.active {
        font-weight: bold;
        color: var(--text-primary);
        border-left: 3px solid var(--btn-primary);
        padding-left: 8px;
    }

    /* Search Bar Area */
    .search-bar-container {
        background: var(--bg-quaternary);
        padding: 15px;
        border-radius: 4px;
        border: 1px solid var(--border-color);
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .search-input {
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 8px;
        border-radius: 4px;
        flex: 1;
    }
    .search-btn {
        background: var(--bg-primary);
        border: 1px solid var(--btn-primary);
        color: var(--btn-primary);
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
    }
    .search-btn:hover {
        background: var(--btn-primary);
        color: #fff;
    }

    /* Modal Styles */
    #att-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; display: none; align-items: center; justify-content: center; }
    #att-modal { width: 90%; max-width: 1100px; height: 90%; max-height: 900px; background: #fff; border-radius: 6px; overflow: hidden; display: flex; flex-direction: column; }
</style>

<div style="padding: 20px;">
    <div class="page-header">
        <div>
            @if($isSuperAdmin ?? false)
                @if($selectedOrgId ?? false)
                <h2 style="margin:0; font-size: 20px; font-weight: 600; color: var(--text-primary);">
                    <i class="bi bi-building text-primary me-2"></i>{{ $selectedOrgName ?? 'Organization' }} - Attendance Records
                </h2>
                <small class="text-muted">
                    <a href="{{ route('attendance-records.index') }}" style="color:var(--btn-primary);">← Back to all organizations</a>
                </small>
                @else
                <h2 style="margin:0; font-size: 20px; font-weight: 600; color: var(--text-primary);">
                    <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>{{ __('messages.organization_overview') }} - {{ __('messages.attendance_report') }}
                </h2>
                <small class="text-muted">{{ __('messages.select_salary_statistic') }}</small>
                @endif
            @else
            <h2 style="margin:0; font-size: 20px; font-weight: 600; color: var(--text-primary);">
                Select attendance record to change
            </h2>
            <small class="text-muted">Your organization's records</small>
            @endif
        </div>
        @if(!($showOrgOverview ?? false))
        <a href="#" onclick="return openPopup('{{ route('attendance-records.create', ['popup' => 1]) }}');" style="padding:8px 16px; background:var(--btn-primary); color:var(--btn-text); text-decoration:none; border-radius:6px; font-size:14px; font-weight:600;">+ Add New Record</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background:#d1e7dd; color:#0f5132; padding:12px; border-radius:6px; margin-bottom:20px; border: 1px solid #badbcc;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="background:#f8d7da; color:#842029; padding:12px; border-radius:6px; margin-bottom:20px; border: 1px solid #f5c2c7;">{{ session('error') }}</div>
    @endif

    {{-- ============================================================== --}}
    {{-- SUPER ADMIN: Organization Cards Overview --}}
    {{-- ============================================================== --}}
    @if($showOrgOverview ?? false)
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">
                <i class="bi bi-building me-2 text-primary"></i>
                Select Organization
            </h5>
            <span class="badge bg-primary rounded-pill">{{ count($organizations ?? []) }} Organizations</span>
        </div>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
            @foreach($organizations ?? [] as $org)
            <div class="col">
                <a href="{{ route('attendance-records.index', ['organization_id' => $org['id']]) }}" style="text-decoration:none;">
                    <div class="card h-100 border-0 shadow-sm" style="transition: transform 0.2s, box-shadow 0.2s;">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                @if(!empty($org['logo']))
                                {{-- Show org logo if available --}}
@inject('djangoApi', 'App\Services\DjangoApi')
                                <div style="width:50px;height:50px;border-radius:12px;overflow:hidden;background:#f8f9fa;display:flex;align-items:center;justify-content:center;">
                                    <img src="{{ $djangoApi->getBaseUrl() . $org['logo'] }}" 
                                         alt="{{ $org['name'] }}" 
                                         style="width:100%;height:100%;object-fit:cover;"
                                         onerror="this.style.display='none';this.parentElement.innerHTML='<i class=\'bi bi-building text-secondary fs-4\'></i>';">
                                </div>
                                @else
                                {{-- Fallback: gradient icon --}}
                                <div style="width:50px;height:50px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-building text-white fs-4"></i>
                                </div>
                                @endif
                                <div>
                                    <h6 class="card-title mb-1 fw-bold text-dark">{{ $org['name'] }}</h6>
                                    <small class="text-muted">
                                        <i class="bi bi-people me-1"></i>{{ $org['employee_count'] ?? '?' }} employees
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-primary bg-opacity-10 border-0 text-center">
                            <small class="text-primary fw-semibold">
                                <i class="bi bi-arrow-right-circle me-1"></i>View Attendance Records
                            </small>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    
    @else
    {{-- ============================================================== --}}
    {{-- Normal View: Filter Bar and Records Table --}}
    {{-- ============================================================== --}}

    <!-- Filter Bar -->
    <form method="GET" action="{{ route('attendance-records.index') }}" style="background:var(--bg-card); padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid var(--border-color); display:flex; flex-wrap:wrap; gap:15px; align-items:flex-end;">
        
        @if($isSuperAdmin ?? false)
        <input type="hidden" name="organization_id" value="{{ $selectedOrgId }}">
        @endif
        
        <div style="flex:1; min-width:250px;">
            <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">{{ __('messages.search') }}</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or ID..." class="form-control" style="background:var(--input-bg); color:var(--input-text); border:1px solid var(--input-border);">
        </div>

        <div style="width:180px;">
            <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">{{ __('messages.date') }}</label>
            <input type="date" name="date" class="form-control" value="{{ request('date') }}" 
                   style="background:var(--input-bg); color:var(--input-text); border:1px solid var(--input-border); cursor:pointer;"
                   onchange="this.form.submit()">
        </div>
        
        <div style="width:100px;">
            <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">{{ __('messages.year') }}</label>
            <select name="year" class="form-select" style="background:var(--input-bg); color:var(--input-text); border:1px solid var(--input-border);" onchange="this.form.submit()">
                <option value="All" {{ request('year') == 'All' ? 'selected' : '' }}>All</option>
                @for($y = 2020; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        <div style="width:120px;">
            <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">{{ __('messages.month') }}</label>
            <select name="month" class="form-select" style="background:var(--input-bg); color:var(--input-text); border:1px solid var(--input-border);" onchange="this.form.submit()">
                <option value="All" {{ request('month') == 'All' ? 'selected' : '' }}>All</option>
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                @endforeach
            </select>
        </div>

        <div style="width:140px;">
            <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">{{ __('messages.status') }}</label>
            <select name="status" class="form-select" style="background:var(--input-bg); color:var(--input-text); border:1px solid var(--input-border);" onchange="this.form.submit()">
                <option value="All" {{ request('status') == 'All' ? 'selected' : '' }}>All</option>
                @foreach(['Present', 'Late', 'Late Check-in', 'Early Leave', 'Absent', 'Half Day', 'On Leave', 'Holiday', 'Pending', 'Off Day'] as $st)
                    <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>

        <div style="width:150px;">
            <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">Department</label>
            <select name="department" class="form-select" style="background:var(--input-bg); color:var(--input-text); border:1px solid var(--input-border);" onchange="this.form.submit()">
                <option value="All" {{ request('department') == 'All' ? 'selected' : '' }}>All</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </div>

        <div style="width:150px;">
            <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">Designation</label>
            <select name="designation" class="form-select" style="background:var(--input-bg); color:var(--input-text); border:1px solid var(--input-border);" onchange="this.form.submit()">
                <option value="All" {{ request('designation') == 'All' ? 'selected' : '' }}>All</option>
                @foreach($designations as $desig)
                    <option value="{{ $desig }}" {{ request('designation') == $desig ? 'selected' : '' }}>{{ $desig }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-primary" style="background:var(--btn-primary); border:none; padding:8px 20px;">{{ __('messages.apply_filters') }}</button>
            <a href="{{ route('attendance-records.index', ['organization_id' => $selectedOrgId ?? '']) }}" class="btn btn-secondary" style="background:#6c757d; border:none; padding:8px 20px; color:#fff; text-decoration:none; display:inline-block; line-height:1.5;">{{ __('messages.reset') }}</a>
        </div>
    </form>

    <div class="layout-container">
        <!-- Main Content (Full Width now) -->
        <div style="flex:1; min-width:0;">

            <!-- Bulk Action Controls -->
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:15px; background:var(--bg-quaternary); padding:10px; border-radius:6px; border:1px solid var(--border-color);">
                <label style="font-weight:600; font-size:13px; color:var(--text-primary);">Action:</label>
                <select id="bulk-action-select" class="form-select" style="width:auto; display:inline-block; padding:4px 8px; font-size:13px;">
                    <option value="">---------</option>
                    <option value="delete">Delete selected attendance records</option>
                    <option value="remove_checkout">Remove checkout time and image</option>
                </select>
                <button type="button" onclick="submitBulkAction()" class="btn btn-primary" style="padding:4px 12px; font-size:13px; background:var(--btn-primary); border:none; color:#fff;">Go</button>
                <span id="selected-count" style="font-size:13px; color:var(--text-secondary); margin-left:10px;">0 of {{ count($records) }} selected</span>
            </div>

            <!-- Top Pagination -->
            <div style="margin-bottom: 15px; display: flex; align-items: center; justify-content: flex-end; gap: 15px; color: var(--text-secondary); font-size: 13px; font-weight: 600;">
                <div>
                    Showing {{ (($page ?? 1) - 1) * ($limit ?? 50) + 1 }} - {{ min(($page ?? 1) * ($limit ?? 50), $totalCount ?? count($records)) }} of {{ $totalCount ?? count($records) }} records
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <select onchange="window.location.href='{{ request()->fullUrlWithQuery(['limit' => '']) }}'.replace('limit=', 'limit=' + this.value).replace('&page={{ $page ?? 1 }}', '&page=1')" 
                            class="form-select form-select-sm" 
                            style="width: auto; height: 28px; font-size: 12px; padding: 2px 8px;">
                        @foreach([50, 75, 100, 200] as $l)
                            <option value="{{ $l }}" {{ ($limit ?? 50) == $l ? 'selected' : '' }}>{{ $l }}</option>
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

            <div class="admin-table-container">
                @php
                    $currentSort = request('sort', 'date');
                    $currentDir = request('dir', 'desc');
                    $toggleDir = $currentDir === 'asc' ? 'desc' : 'asc';
                    $sortParams = request()->except(['sort', 'dir']);
                @endphp
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" class="custom-checkbox" id="select-all"></th>
                            <th>#</th>
                            <th>
                                <a href="{{ route('attendance-records.index', array_merge($sortParams, ['sort' => 'employee_name', 'dir' => $currentSort === 'employee_name' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'employee_name' ? 'active' : '' }}">
                                    {{ __('messages.employee') }} {!! $currentSort === 'employee_name' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('attendance-records.index', array_merge($sortParams, ['sort' => 'organization_name', 'dir' => $currentSort === 'organization_name' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'organization_name' ? 'active' : '' }}">
                                    {{ __('messages.organization') }} {!! $currentSort === 'organization_name' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('attendance-records.index', array_merge($sortParams, ['sort' => 'date', 'dir' => $currentSort === 'date' ? $toggleDir : 'desc'])) }}" class="th-sortable {{ $currentSort === 'date' ? 'active' : '' }}">
                                    {{ __('messages.date') }} {!! $currentSort === 'date' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('attendance-records.index', array_merge($sortParams, ['sort' => 'checkin_time', 'dir' => $currentSort === 'checkin_time' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'checkin_time' ? 'active' : '' }}">
                                    {{ __('messages.clock_in') }} {!! $currentSort === 'checkin_time' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('attendance-records.index', array_merge($sortParams, ['sort' => 'checkout_time', 'dir' => $currentSort === 'checkout_time' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'checkout_time' ? 'active' : '' }}">
                                    {{ __('messages.clock_out') }} {!! $currentSort === 'checkout_time' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('attendance-records.index', array_merge($sortParams, ['sort' => 'status', 'dir' => $currentSort === 'status' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'status' ? 'active' : '' }}">
                                    {{ __('messages.status') }} {!! $currentSort === 'status' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('attendance-records.index', array_merge($sortParams, ['sort' => 'late_duration', 'dir' => $currentSort === 'late_duration' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'late_duration' ? 'active' : '' }}">
                                    {{ __('messages.late') }} (M:S) {!! $currentSort === 'late_duration' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('attendance-records.index', array_merge($sortParams, ['sort' => 'device_id', 'dir' => $currentSort === 'device_id' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'device_id' ? 'active' : '' }}">
                                    {{ __('messages.device') }} ID {!! $currentSort === 'device_id' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                                </a>
                            </th>
                            <th>Check-in Image</th>
                            <th>Check-out Image</th>
                            <th style="text-align:right;">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $index => $record)
                            <tr>
                                <td><input type="checkbox" name="selected_ids[]" value="{{ $record['id'] }}" class="custom-checkbox record-checkbox"></td>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        @if(!empty($record['face_image']))
                                            <img src="data:image/jpeg;base64,{{ $record['face_image'] }}" style="width:35px; height:35px; border-radius:50%; object-fit:cover; border:1px solid var(--border-color);" alt="img">
                                        @else
                                            <div style="width:35px; height:35px; border-radius:50%; background:var(--accent); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:12px;">
                                                {{ strtoupper(substr($record['employee_name'] ?? 'N', 0, 1)) }}
                                            </div>
                                        @endif
                                        <a href="#" onclick="return openPopup('{{ route('attendance-records.edit', ['id' => $record['id'], 'popup' => 1]) }}');" style="text-decoration:none;">
                                            <div style="font-weight:600; color: var(--text-primary);">{{ $record['employee_name'] ?? 'Unknown' }}</div>
                                            <div style="font-size:12px; color:var(--text-secondary);">{{ $record['employee_id'] }}</div>
                                        </a>
                                    </div>
                                </td>
                                <td style="font-size:12px; color:var(--text-secondary);">
                                    {{ $record['organization_name'] ?? '-' }}
                                </td>
                            <td>
                                @if($record['date'])
                                    {{ \Carbon\Carbon::parse($record['date'])->format('M j, Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                {{ $record['checkin_time'] ? \Carbon\Carbon::parse($record['checkin_time'])->timezone('Asia/Dhaka')->format('h:i A') : '-' }}
                            </td>
                            <td>
                                {{ $record['checkout_time'] ? \Carbon\Carbon::parse($record['checkout_time'])->timezone('Asia/Dhaka')->format('h:i A') : '-' }}
                            </td>
                            <td>
                                @php
                                    $statusClass = match($record['status']) {
                                        'Present' => 'status-present',
                                        'Absent' => 'status-absent',
                                        'Late' => 'status-late',
                                        'On Leave' => 'status-leave',
                                        default => 'status-present'
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $record['status'] }}
                                    @if(!empty($record['is_status_override']))
                                        <span style="margin-left:4px; color:#856404;" title="Manually overridden">⚠ Manual</span>
                                    @endif
                                </span>
                            </td>
                            <td>{{ $record['late_duration'] !== '-' ? $record['late_duration'] : '—' }}</td>
                            <td>{{ $record['device_id'] }}</td>
                            <td>
                                @if(!empty($record['checkin_image']))
                                    <a href="{{ $record['checkin_image'] }}" target="_blank">
                                        <img src="{{ $record['checkin_image'] }}" class="thumb-img" alt="In">
                                    </a>
                                @else
                                    <span style="color:#ccc;">—</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($record['checkout_image']))
                                    <a href="{{ $record['checkout_image'] }}" target="_blank">
                                        <img src="{{ $record['checkout_image'] }}" class="thumb-img" alt="Out">
                                    </a>
                                @else
                                    <span style="color:#ccc;">—</span>
                                @endif
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a href="#" onclick="return openPopup('{{ route('attendance-records.edit', ['id' => $record['id'], 'popup' => 1]) }}');" class="action-link" style="margin-right:10px;">Edit</a>
                                <form id="delete-form-{{ $record['id'] }}" action="{{ route('attendance-records.destroy', $record['id']) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="delete-btn" onclick="if(confirm('Are you sure you want to delete this record?')) { document.getElementById('delete-form-{{ $record['id'] }}').submit(); }">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" style="padding:30px; text-align:center; color:var(--text-secondary);">No attendance records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 15px; display: flex; align-items: center; justify-content: space-between; color: var(--text-secondary); font-size: 13px; font-weight: 600;">
            <div>
                 Showing {{ (($page ?? 1) - 1) * 50 + 1 }} - {{ min(($page ?? 1) * 50, $totalCount ?? count($records)) }} of {{ $totalCount ?? count($records) }} records
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
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
</div>

@endif {{-- End of showOrgOverview else block --}}

<form id="bulk-action-form" action="{{ route('attendance-records.bulk_action') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="action" id="bulk-action-input">
    <div id="bulk-ids-container"></div>
</form>

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
    function submitBulkAction() {
        const action = document.getElementById('bulk-action-select').value;
        if (!action) {
            alert('Please select an action.');
            return;
        }
        
        const selected = document.querySelectorAll('.record-checkbox:checked');
        if (selected.length === 0) {
            alert('Please select at least one record.');
            return;
        }
        
        if (confirm('Are you sure you want to proceed with this bulk action?')) {
            document.getElementById('bulk-action-input').value = action;
            const container = document.getElementById('bulk-ids-container');
            container.innerHTML = '';
            selected.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });
            document.getElementById('bulk-action-form').submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.record-checkbox');
        const countSpan = document.getElementById('selected-count');
        const totalRecords = {{ count($records) }};

        function updateCount() {
            const checkedCount = document.querySelectorAll('.record-checkbox:checked').length;
            countSpan.textContent = checkedCount + ' of ' + totalRecords + ' selected';
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateCount();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateCount);
        });
    });

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
