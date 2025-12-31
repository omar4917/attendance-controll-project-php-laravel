@extends('layouts.app')

@section('content')
<style>
    /* Page uses global theme variables from layout */

    .filter-form { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-bottom: 16px; background: var(--bg-card); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); color: var(--text-primary); }
    .filter-form label { font-weight: 600; font-size: 13px; color: var(--text-primary); margin-right: 4px; }
    .filter-form select { padding: 6px 10px; border: 1px solid var(--border-color); background: var(--input-bg); color: var(--text-primary); border-radius: 4px; font-size: 13px; }
    .filter-form button { padding: 6px 12px; background: var(--accent); color: var(--btn-primary-text); border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
    
    .admin-table { width: 100%; border-collapse: collapse; font-size: 14.5px; background: var(--bg-card); }
    .admin-table th { background: var(--bg-header); color: var(--text-primary); padding: 8px; text-align: center; border: 1px solid var(--border-color); position: sticky; top: 0; z-index: 5; }
    .admin-table td { padding: 6px; border: 1px solid var(--border-color); text-align: center; vertical-align: middle; color: var(--text-primary); }
    .admin-table tr:nth-child(even) { background: var(--bg-alternate); }
    .admin-table tr:nth-child(odd) { background: var(--bg-card); }
    .admin-table tr:hover { background: var(--bg-hover); }
    
    .employee-cell { text-align: left !important; min-width: 200px; }
    .day-cell { min-width: 30px; padding: 4px !important; }
    .status-badge { position: absolute; top: -6px; right: -4px; color: #ff3333; font-weight: 900; font-size: 16px; display: flex; align-items: center; justify-content: center; text-shadow: 0 0 2px rgba(0,0,0,0.5); }
    
    .dashboard-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; background: var(--bg-card); padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); color: var(--text-primary); }
    
    /* Import/Export Section Styles */
    .import-export-section { background: var(--bg-card); padding: 12px; border-radius: 6px; margin: 15px 0; border: 1px solid var(--border-color); color: var(--text-primary); }
    .import-export-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
    .section-label { color: var(--text-primary); font-weight: 600; font-size: 14px; }
    
    /* Modal Styles */
    #att-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 99999; display: none; align-items: center; justify-content: center; }
    #att-modal { width: 90%; max-width: 1100px; height: 90%; max-height: 900px; background: #fff; border-radius: 6px; overflow: hidden; display: flex; flex-direction: column; }
    
    .theme-toggle-btn { padding: 6px 12px; border: 1px solid var(--border-color); background: var(--bg-hover); color: var(--text-primary); border-radius: 4px; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 6px; }
</style>

<!-- Manual CRUD Form Removed (Moved to Attendance Records page) -->

<!-- Filters Row -->
<!-- Filters Row -->
<form method="get" action="{{ route('attendance.index') }}" style="background:var(--bg-quaternary); padding:20px; border-radius:8px; border:1px solid var(--border-color); margin-bottom:20px; display:flex; flex-wrap:wrap; gap:20px; align-items:flex-end;">
    
    <div style="width:200px;">
        <label style="font-size:14px; font-weight:700; color:var(--text-primary); margin-bottom:8px; display:block;">{{ __('messages.date') }}</label>
        <input type="month" name="date" class="form-control" value="{{ request('date', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)) }}" style="background:#fff; color:var(--text-primary); border:1px solid #ced4da; width: 100%; padding: 8px 12px; border-radius: 6px;">
    </div>

    <div style="width:200px;">
        <label style="font-size:14px; font-weight:700; color:var(--text-primary); margin-bottom:8px; display:block;">{{ __('messages.department') }}</label>
        <select name="department" class="form-select" style="background:#fff; color:var(--text-primary); border:1px solid #ced4da; width: 100%; padding: 8px 12px; border-radius: 6px;" onchange="this.form.submit()">
            <option value="">All</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
    </div>

    <div style="width:200px;">
        <label style="font-size:14px; font-weight:700; color:var(--text-primary); margin-bottom:8px; display:block;">{{ __('messages.designation') }}</label>
        <select name="designation" class="form-select" style="background:#fff; color:var(--text-primary); border:1px solid #ced4da; width: 100%; padding: 8px 12px; border-radius: 6px;" onchange="this.form.submit()">
            <option value="">All</option>
            @foreach($designations as $desig)
                <option value="{{ $desig }}" {{ request('designation') == $desig ? 'selected' : '' }}>{{ $desig }}</option>
            @endforeach
        </select>
    </div>

    <div style="display:flex; gap:10px;">
        <button type="submit" style="padding:8px 24px; background:#198754; color:#fff; border:none; border-radius:6px; font-weight:700; font-size:16px; cursor:pointer;">{{ __('messages.filter') }}</button>
        <a href="{{ route('attendance.index') }}" style="padding:8px 24px; background:#c3e6cb; color:#0f5132; text-decoration:none; border-radius:6px; font-weight:700; font-size:16px; border:1px solid #badbcc;">{{ __('messages.cancel') }}</a>
    </div>
</form>

@php
    $base = rtrim($djangoBaseUrl, '/');
    $orgId = session('organization_id');
    $qs = http_build_query(['month' => $month, 'year' => $year, 'department' => request('department'), 'designation' => request('designation'), 'organization_id' => $orgId]);
    $pdfs = !empty($pdfUrls) ? $pdfUrls : ['pdf' => $base.'/attendance-dashboard/pdf/', 'bulk_pdf' => $base.'/attendance-dashboard/pdf/bulk/', 'combined_pdf' => $base.'/attendance-dashboard/pdf/combined/'];
@endphp

<!-- Unified Action Bar -->
<div class="unified-action-bar">
    <!-- PDF Downloads Group -->
    <div class="action-group">
        <span class="action-label"><i class="bi bi-file-pdf"></i> PDF:</span>
        <a href="{{ route('attendance.pdf', ['type' => 'pdf', 'month' => $month, 'year' => $year, 'department' => request('department'), 'designation' => request('designation'), 'organization_id' => $orgId]) }}" class="btn-action-primary" style="background:#198754;">{{ __('messages.download_pdf') }}</a>
        <a href="{{ route('attendance.pdf', ['type' => 'bulk', 'month' => $month, 'year' => $year, 'department' => request('department'), 'designation' => request('designation'), 'organization_id' => $orgId]) }}" class="btn-action-primary" style="background:#6f42c1;">{{ __('messages.individual_pdfs') }}</a>
        <a href="{{ route('attendance.pdf', ['type' => 'combined', 'month' => $month, 'year' => $year, 'department' => request('department'), 'designation' => request('designation'), 'organization_id' => $orgId]) }}" class="btn-action-primary" style="background:#fd7e14;">{{ __('messages.combined_pdf') }}</a>
    </div>


    @if(session('user_role') !== 'org_viewer')
    <div class="divider-vertical"></div>

    <!-- Export Group with Progress -->
    <div class="action-group">
        <span class="action-label"><i class="bi bi-box-arrow-up"></i> {{ __('messages.export') }}:</span>
        <button type="button" id="exportBtn" class="btn-action-primary" style="background:#0d6efd;">
            <i class="bi bi-download"></i> {{ __('messages.export') }} ZIP
        </button>
        <div id="exportProgressContainer" style="display:none;margin-left:10px;min-width:200px;">
            <div style="height:6px;background:#e9ecef;border-radius:3px;overflow:hidden;">
                <div id="exportProgressBar" style="height:100%;background:#0d6efd;width:0%;transition:width 0.3s;"></div>
            </div>
            <small id="exportProgressText" style="color:var(--text-secondary);">Preparing...</small>
        </div>
        <a href="#" id="exportDownloadBtn" class="btn-action-primary" style="display:none;background:#28a745;">
            <i class="bi bi-check-circle"></i> Download
        </a>
    </div>

    <div class="divider-vertical"></div>

    <!-- Import Group with Staged Upload -->
    <div class="action-group">
        <span class="action-label"><i class="bi bi-cloud-upload"></i> {{ __('messages.import') }}:</span>
        <button type="button" id="importBtn" class="btn-action-secondary" onclick="document.getElementById('stagedFileInput').click();">
            <i class="bi bi-folder2-open"></i> Select File
        </button>
        <input type="file" id="stagedFileInput" style="display:none;" accept=".zip,.json">
        <div id="uploadProgressContainer" style="display:none;margin-left:10px;min-width:200px;">
            <div style="height:6px;background:#e9ecef;border-radius:3px;overflow:hidden;">
                <div id="uploadProgressBar" style="height:100%;background:#28a745;width:0%;transition:width 0.3s;"></div>
            </div>
            <small id="uploadProgressText" style="color:var(--text-secondary);">Uploading...</small>
        </div>
    </div>
    @endif
</div>

<!-- Upload Preview Modal -->
<div class="modal fade" id="uploadPreviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--header-bg, #2c3e50);color:#fff;">
                <h5 class="modal-title"><i class="bi bi-file-earmark-check"></i> Upload Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview content inserted by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="uploadCancelBtn" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="uploadConfirmBtn">
                    <i class="bi bi-check-circle"></i> Confirm Import
                </button>
            </div>
        </div>
    </div>
</div>



<!-- Dashboard Title & Search -->
<div style="margin-bottom:15px;">
    <div style="display:flex;align-items:center;gap:20px;margin-bottom:10px;">
        <div>
            @if($isSuperAdmin ?? false)
            <h3 style="margin:0;color:var(--text-primary);display:flex;align-items:center;gap:8px;">
                <i class="bi bi-graph-up-arrow text-primary"></i>
                System Attendance Dashboard – {{ $month }}/{{ $year }}
            </h3>
            <small class="text-muted">Cross-organization attendance overview</small>
            @else
@inject('djangoApi', 'App\Services\DjangoApi')
            <h3 style="margin:0;color:var(--text-primary);display:flex;align-items:center;gap:8px;">
                @if(session('organization_logo'))
                    @php 
                        $dashLogoPath = session('organization_logo');
                        if (!str_starts_with($dashLogoPath, 'http')) {
                            $dashLogoPath = $djangoApi->getBaseUrl() . (str_starts_with($dashLogoPath, '/') ? '' : '/') . $dashLogoPath;
                        }
                    @endphp
                    <img src="{{ $dashLogoPath }}" alt="Logo" style="height:24px;width:24px;border-radius:4px;object-fit:cover;" onerror="this.style.display='none'" />
                @endif
                {{ __('messages.attendance_dashboard') }} – {{ $month }}/{{ $year }}
            </h3>
            <small class="text-muted">Your organization's attendance</small>
            @endif
        </div>
        
        <!-- Navigation Arrows (inline with title) -->
        <div style="display:flex;gap:4px;">
            <a href="?{{ $prev_qs }}" class="btn-action-secondary" title="{{ __('messages.previous') }}">&larr; {{ __('messages.previous') }}</a>
            <a href="?{{ $next_qs }}" class="btn-action-secondary" title="{{ __('messages.next') }}">{{ __('messages.next') }} &rarr;</a>
        </div>
    </div>
    
    <div style="display:flex;align-items:center;gap:8px;">
        <label for="att-search" style="font-weight:600;font-size:13px;color:var(--text-primary);">{{ __('messages.quick_search') }}:</label>
        <input id="att-search" type="text" placeholder="{{ __('messages.search_placeholder') }}" style="padding:6px 10px;border-radius:4px;border:1px solid var(--border-color);min-width:280px;font-size:13px;background:var(--input-bg);color:var(--text-primary);">
        <span style="font-size:12px;color:var(--text-secondary);">{{ __('messages.drag_to_select') }}</span>
    </div>
</div>

<!-- Legend -->
<div style="margin-bottom:10px; padding:8px; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:4px; display:flex; flex-wrap:wrap; gap:15px; font-size:12px; color:var(--text-primary);">
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/present.png') }}" width="16" height="16"> <span>{{ __('messages.present') }}</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><span style="color:#ff3333;font-weight:900;font-size:14px;">!</span> <span>{{ __('messages.late') }}</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/absent.png') }}" width="16" height="16"> <span>{{ __('messages.absent') }}</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/on_leave.png') }}" width="16" height="16"> <span>{{ __('messages.on_leave') }}</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/holidays.png') }}" width="16" height="16"> <span>{{ __('messages.holiday') }}</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/half_day.png') }}" width="16" height="16"> <span>{{ __('messages.half_day') }}</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/early_leave.png') }}" width="16" height="16"> <span>{{ __('messages.early_leave') }}</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/off_day.png') }}" width="16" height="16"> <span>Off Day</span></div>
</div>

<!-- Table -->
<div class="table-container" role="region" aria-label="Attendance table" style="user-select:text; overflow-x:auto;">
  <table class="admin-table" role="table" aria-describedby="attendance-desc">
    <thead>
      <tr style="background-color:#393737">
        <th scope="col" style="width:35px;">#</th>
        <th style="z-index: 30 !important;" scope="col">{{ __('messages.employee') }}</th>
        <th scope="col">{{ __('messages.designation') }}</th>
        @foreach($daysForGrid as $day)
          <th scope="col" title="{{ $day['full'] }}">
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;line-height:1.4;padding:4px 0;">
              <div style="font-weight:600;font-size:13px;">{{ $day['num'] }}</div>
              <div style="font-size:11px;color:#aaa;">{{ $day['short'] }}</div>
            </div>
          </th>
        @endforeach
        <th scope="col" class="attend-data">{!! preg_replace('/\s+/', '<br>', __('messages.total_present')) !!}</th>
        <th scope="col" class="attend-data">{{ __('messages.late') }}</th>
        <th scope="col" class="attend-data">{!! preg_replace('/\s+/', '<br>', __('messages.on_leave')) !!}</th>
        <th scope="col" class="attend-data">{{ __('messages.holiday') }}</th>
        <th scope="col" class="attend-data">{{ __('messages.absent') }}</th>
        <th scope="col" class="attend-data">{!! preg_replace('/\s+/', '<br>', __('messages.half_day')) !!}</th>
        <th scope="col" class="attend-data">{!! preg_replace('/\s+/', '<br>', __('messages.early_leave')) !!}</th>
      </tr>
    </thead>
    <tbody>
      @foreach($employeesForGrid as $index => $emp)
      <tr class="employee-row" data-search="{{ strtolower($emp['name']) }} {{ strtolower($emp['employee_id']) }} {{ strtolower($emp['designation'] ?? '') }}">
        <td style="text-align:center;font-weight:600;">{{ $index + 1 }}</td>
        <td class="employee-cell">
          <div style="display:flex;align-items:center;gap:10px;">
            @php
                $imgUrl = $emp['emp_image_url'] ?? ($emp['image_url'] ?? null);
                $empPk = $emp['emp_pk'] ?? '';
                // Use Laravel route for editing employee, add popup param
                $editUrl = $empPk ? route('employees.edit', $empPk) . '?popup=1' : '#';
            @endphp
            @if($empPk)
                <a href="#" onclick="return openPopup('{{ $editUrl }}');" title="Edit {{ $emp['name'] }}">
            @else
                <span title="No ID available">
            @endif
                @if(!empty($imgUrl))
                  <img src="{{ $imgUrl }}" style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:1px solid #ddd" alt="{{ $emp['name'] }}" />
                @else
                  <img src="{{ asset('icons/default-avatar.png') }}" style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:1px solid #ddd" alt="avatar" />
                @endif
            @if($empPk)
                </a>
            @else
                </span>
            @endif
            <div>
              @if($empPk)
                  <a href="#" onclick="return openPopup('{{ $editUrl }}');" style="text-decoration:none;color:inherit;" title="Edit {{ $emp['name'] }}">
              @else
                  <span style="color:inherit;">
              @endif
                <div class="employee-names" style="font-weight:600;font-size:14.5px;color:var(--text-primary);cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;">{{ $emp['name'] }}</div>
                <div class="employee-id" style="font-size:12.5px;color:var(--text-secondary)">{{ $emp['employee_id'] }}</div>
              @if($empPk)
                  </a>
              @else
                  </span>
              @endif
              <div style="margin-top:2px;display:flex;gap:6px;align-items:center;font-size:11px;">
                @if(!empty($emp['pdf_url']))
                    <a href="{{ $emp['pdf_url'] }}" target="_blank" style="color:#5b80b2;text-decoration:none;font-weight:600;">PDF</a>
                @else
                    <a href="{{ $base }}/attendance-dashboard/pdf/?month={{ $month }}&year={{ $year }}&employee_id={{ $emp['emp_pk'] }}" target="_blank" style="color:#5b80b2;text-decoration:none;font-weight:600;">PDF</a>
                @endif
              </div>
            </div>
          </div>
        </td>

        <td class="designation-cell">{{ $emp['designation'] }}</td>

        @foreach($emp['statuses'] as $st)
            <td class="day-cell" title="{{ $st['status'] ?? '' }}">
                @if(!empty($st['status']))
                    @php
                        // Construct edit URL with query params to populate the form
                        $editParams = [
                            'employee_id' => $emp['employee_id'],
                            'date' => $day['full'], // Use the column date
                            'status' => $st['status'],
                            'checkin_time' => !empty($st['checkin_time']) ? \Carbon\Carbon::parse($st['checkin_time'])->timezone('Asia/Dhaka')->format('H:i') : '',
                            'checkout_time' => !empty($st['checkout_time']) ? \Carbon\Carbon::parse($st['checkout_time'])->timezone('Asia/Dhaka')->format('H:i') : '',
                            'popup' => 1,
                        ];
                    @endphp
                    @if(session('user_role') !== 'org_viewer')
                    <a href="#" onclick="return openPopup('{{ route('attendance-records.edit', array_merge(['id' => $st['id'] ?? 0], $editParams)) }}');" aria-label="Edit record">
                    @endif
                        <span style="position:relative;display:inline-block;width:22px;height:22px;">
                            @if(!empty($st['icon']))
                                <img src="{{ asset($st['icon']) }}" style="width:22px;height:22px;object-fit:contain" alt="{{ $st['status'] }}" />
                            @else
                                {{ substr($st['status'], 0, 1) }}
                            @endif
                            
                            @if(!empty($st['is_late']))
                                <span class="status-badge" title="Late">!</span>
                            @endif
                            
                            @if(!empty($st['is_status_override']))
                                <span style="position:absolute; bottom:-8px; left:50%; transform:translateX(-50%); font-size:8px; color:#f0ad4e; font-weight:bold;" title="Manual Override">⚠</span>
                            @endif
                        </span>
                    @if(session('user_role') !== 'org_viewer')
                    </a>
                    @endif
                @else
                    @if(session('user_role') !== 'org_viewer')
                    <a href="#" onclick="return openPopup('{{ route('attendance-records.create', ['employee_id' => $emp['employee_id'], 'date' => $day['full'], 'popup' => 1]) }}');" style="color:#ccc; text-decoration:none;">&mdash;</a>
                    @else
                    <span style="color:#ccc;">&mdash;</span>
                    @endif
                @endif
            </td>
        @endforeach

        <td style="font-weight:bold">{{ $emp['totals']['Present'] ?? 0 }}</td>
        <td>{{ $emp['totals']['Late'] ?? 0 }}</td>
        <td>{{ $emp['totals']['On_Leave'] ?? 0 }}</td>
        <td>{{ $emp['totals']['Holiday'] ?? 0 }}</td>
        <td>{{ $emp['totals']['Absent'] ?? 0 }}</td>
        <td>{{ $emp['totals']['Half_Day'] ?? 0 }}</td>
        <td>{{ $emp['totals']['Early_Leave'] ?? 0 }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
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

// Quick search filter (name/ID/designation)
(function(){
  var input = document.getElementById('att-search');
  if (!input) return;
  var rows = document.querySelectorAll('.employee-row');

  input.addEventListener('input', function(){
    var q = (input.value || '').toLowerCase().trim();
    rows.forEach(function(row){
      var hay = row.getAttribute('data-search') || '';
      row.style.display = hay.indexOf(q) !== -1 ? '' : 'none';
    });
  });
})();
</script>

<!-- Staged Upload JS -->
<script src="{{ asset('js/staged-upload.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '{{ csrf_token() }}';
    const djangoBaseUrl = '{{ session("django_base_url", env("DJANGO_BASE_URL", "http://127.0.0.1:8000")) }}';
    
    // Initialize Staged Upload for Import
    const fileInput = document.getElementById('stagedFileInput');
    const progressContainer = document.getElementById('uploadProgressContainer');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    const previewModal = document.getElementById('uploadPreviewModal');
    const confirmBtn = document.getElementById('uploadConfirmBtn');
    const cancelBtn = document.getElementById('uploadCancelBtn');
    
    let currentSessionId = null;
    let currentXhr = null;
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Show progress
            progressContainer.style.display = 'block';
            progressBar.style.width = '0%';
            progressText.textContent = 'Uploading...';
            
            // Create XHR for progress tracking
            currentXhr = new XMLHttpRequest();
            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', 'attendance');
            
            currentXhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    const loaded = (e.loaded / 1024 / 1024).toFixed(2);
                    const total = (e.total / 1024 / 1024).toFixed(2);
                    progressText.textContent = `Uploading: ${loaded}MB / ${total}MB (${percent}%)`;
                }
            });
            
            currentXhr.addEventListener('load', function() {
                if (currentXhr.status === 200) {
                    try {
                        const response = JSON.parse(currentXhr.responseText);
                        currentSessionId = response.session_id;
                        progressText.textContent = 'Upload complete! Review and confirm.';
                        
                        // Show preview modal
                        document.getElementById('previewContent').innerHTML = `
                            <div class="alert alert-info">
                                <strong>File:</strong> ${response.filename}<br>
                                <strong>Size:</strong> ${(response.filesize / 1024 / 1024).toFixed(2)} MB<br>
                                <strong>Records found:</strong> ${response.record_count}
                            </div>
                            ${response.preview_html || ''}
                            <p class="text-muted">Click "Confirm Import" to proceed with the import.</p>
                        `;
                        const modal = new bootstrap.Modal(previewModal);
                        modal.show();
                    } catch (err) {
                        progressText.textContent = 'Error: Invalid response';
                    }
                } else {
                    progressText.textContent = 'Upload failed: ' + currentXhr.statusText;
                }
            });
            
            currentXhr.addEventListener('error', function() {
                progressText.textContent = 'Upload failed - network error';
            });
            
            currentXhr.open('POST', djangoBaseUrl + '/api/staged-upload/', true);
            currentXhr.setRequestHeader('Authorization', 'Key {{ env("DJANGO_API_KEY", "Key123") }}');
            currentXhr.send(formData);
        });
    }
    
    // Confirm button
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (!currentSessionId) return;
            
            progressText.textContent = 'Importing data...';
            
            fetch(djangoBaseUrl + '/api/staged-upload/confirm/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Key {{ env("DJANGO_API_KEY", "Key123") }}'
                },
                body: JSON.stringify({ session_id: currentSessionId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    progressText.textContent = 'Import complete! Reloading...';
                    bootstrap.Modal.getInstance(previewModal)?.hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    progressText.textContent = 'Error: ' + (data.error || 'Import failed');
                }
            })
            .catch(err => {
                progressText.textContent = 'Error: ' + err.message;
            });
        });
    }
    
    // Cancel button
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            if (currentXhr) currentXhr.abort();
            currentSessionId = null;
            progressContainer.style.display = 'none';
            fileInput.value = '';
        });
    }
    
    // Background Export
    const exportBtn = document.getElementById('exportBtn');
    const exportProgressContainer = document.getElementById('exportProgressContainer');
    const exportProgressBar = document.getElementById('exportProgressBar');
    const exportProgressText = document.getElementById('exportProgressText');
    const exportDownloadBtn = document.getElementById('exportDownloadBtn');
    
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            exportProgressContainer.style.display = 'block';
            exportDownloadBtn.style.display = 'none';
            exportProgressBar.style.width = '0%';
            exportProgressText.textContent = 'Starting export...';
            
            fetch(djangoBaseUrl + '/api/export-job/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Key {{ env("DJANGO_API_KEY", "Key123") }}'
                },
                body: JSON.stringify({
                    type: 'attendance',
                    month: {{ $month }},
                    year: {{ $year }}
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.job_id) {
                    pollExportStatus(data.job_id);
                } else {
                    exportProgressText.textContent = 'Failed to start export';
                }
            })
            .catch(err => {
                exportProgressText.textContent = 'Error: ' + err.message;
            });
        });
    }
    
    function pollExportStatus(jobId) {
        const poll = setInterval(() => {
            fetch(djangoBaseUrl + '/api/export-job/status/?job_id=' + jobId, {
                headers: { 'Authorization': 'Key {{ env("DJANGO_API_KEY", "Key123") }}' }
            })
            .then(res => res.json())
            .then(data => {
                exportProgressBar.style.width = data.progress + '%';
                exportProgressText.textContent = data.status;
                
                if (data.complete) {
                    clearInterval(poll);
                    exportDownloadBtn.href = djangoBaseUrl + data.download_url;
                    exportDownloadBtn.style.display = 'inline-block';
                    exportProgressText.textContent = 'Ready! Click to download.';
                }
            })
            .catch(() => clearInterval(poll));
        }, 1000);
    }
});
</script>
@endsection

