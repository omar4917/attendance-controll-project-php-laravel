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
        <label style="font-size:14px; font-weight:700; color:var(--text-primary); margin-bottom:8px; display:block;">Date</label>
        <input type="month" name="date" class="form-control" value="{{ request('date', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)) }}" style="background:#fff; color:var(--text-primary); border:1px solid #ced4da; width: 100%; padding: 8px 12px; border-radius: 6px;">
    </div>

    <div style="width:200px;">
        <label style="font-size:14px; font-weight:700; color:var(--text-primary); margin-bottom:8px; display:block;">Department</label>
        <select name="department" class="form-select" style="background:#fff; color:var(--text-primary); border:1px solid #ced4da; width: 100%; padding: 8px 12px; border-radius: 6px;" onchange="this.form.submit()">
            <option value="">All</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
            @endforeach
        </select>
    </div>

    <div style="width:200px;">
        <label style="font-size:14px; font-weight:700; color:var(--text-primary); margin-bottom:8px; display:block;">Designation</label>
        <select name="designation" class="form-select" style="background:#fff; color:var(--text-primary); border:1px solid #ced4da; width: 100%; padding: 8px 12px; border-radius: 6px;" onchange="this.form.submit()">
            <option value="">All</option>
            @foreach($designations as $desig)
                <option value="{{ $desig }}" {{ request('designation') == $desig ? 'selected' : '' }}>{{ $desig }}</option>
            @endforeach
        </select>
    </div>

    <div style="display:flex; gap:10px;">
        <button type="submit" style="padding:8px 24px; background:#198754; color:#fff; border:none; border-radius:6px; font-weight:700; font-size:16px; cursor:pointer;">Filter</button>
        <a href="{{ route('attendance.index') }}" style="padding:8px 24px; background:#c3e6cb; color:#0f5132; text-decoration:none; border-radius:6px; font-weight:700; font-size:16px; border:1px solid #badbcc;">Reset</a>
    </div>
</form>

@php
    $base = rtrim($djangoBaseUrl, '/');
    $qs = http_build_query(['month' => $month, 'year' => $year, 'department' => request('department'), 'designation' => request('designation')]);
    $pdfs = !empty($pdfUrls) ? $pdfUrls : ['pdf' => $base.'/attendance-dashboard/pdf/', 'bulk_pdf' => $base.'/attendance-dashboard/pdf/bulk/', 'combined_pdf' => $base.'/attendance-dashboard/pdf/combined/'];
@endphp

<!-- Actions Row (Nav + Downloads) -->
<div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-bottom:16px;">
    <!-- Navigation -->
    <div style="display:flex;gap:4px;">
        <a href="?{{ $prev_qs }}" style="padding:8px 14px;border:1px solid var(--border-color);background:var(--bg-secondary);border-radius:4px;color:var(--text-primary);text-decoration:none;font-size:13px;font-weight:600;">&larr; Previous</a>
        <a href="?{{ $next_qs }}" style="padding:8px 14px;border:1px solid var(--border-color);background:var(--bg-secondary);border-radius:4px;color:var(--text-primary);text-decoration:none;font-size:13px;font-weight:600;">Next &rarr;</a>
    </div>
    
    <!-- Downloads -->
    <div style="display:flex;gap:6px;">
        <a href="{{ $pdfs['pdf'] ?? ($base.'/attendance-dashboard/pdf/') }}?{{ $qs }}" target="_blank" style="padding:8px 14px;background:#198754;color:#fff;border-radius:4px;text-decoration:none;font-size:13px;font-weight:600;border:none;">Download PDF</a>
        <a href="{{ $pdfs['bulk_pdf'] ?? ($base.'/attendance-dashboard/pdf/bulk/') }}?{{ $qs }}" target="_blank" style="padding:8px 14px;background:#6f42c1;color:#fff;border-radius:4px;text-decoration:none;font-size:13px;font-weight:600;border:none;">Download Individual PDFs (ZIP)</a>
        <a href="{{ $pdfs['combined_pdf'] ?? ($base.'/attendance-dashboard/pdf/combined/') }}?{{ $qs }}" target="_blank" style="padding:8px 14px;background:#fd7e14;color:#fff;border-radius:4px;text-decoration:none;font-size:13px;font-weight:600;border:none;">Download Combined Detailed PDF</a>
    </div>
</div>

<!-- Import/Export Section -->
<div style="background:var(--bg-secondary);padding:12px;border-radius:8px;border:1px solid var(--border-color);margin-bottom:20px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:15px;">
    <!-- Export -->
    <div style="display:flex;align-items:center;gap:10px;">
        <span style="font-weight:600;font-size:14px;color:var(--text-primary);">📦 Export:</span>
        <form action="{{ route('attendance.export') }}" method="POST" target="_blank" style="margin:0;">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="submit" name="export_data" value="1" style="padding:6px 12px;background:#0d6efd;color:#fff;border:none;border-radius:4px;font-size:13px;font-weight:600;cursor:pointer;">Export ZIP (with Images)</button>
        </form>
    </div>
    
    <!-- Import -->
    <div style="display:flex;align-items:center;gap:10px;">
        <span style="font-weight:600;font-size:14px;color:var(--text-primary);">📥 Import:</span>
        <form action="{{ route('attendance.import') }}" method="POST" enctype="multipart/form-data" style="display:flex;align-items:center;gap:6px;margin:0;">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="file" name="import_file" style="font-size:13px;padding:4px;border:1px solid var(--border-color);border-radius:4px;background:var(--input-bg);color:var(--text-primary);">
            <button type="submit" style="padding:6px 12px;background:#6c757d;color:#fff;border:none;border-radius:4px;font-size:13px;font-weight:600;cursor:pointer;">Upload</button>
        </form>
    </div>
</div>

<!-- Dashboard Title & Search -->
<div style="margin-bottom:15px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
        <h3 style="margin:0;color:var(--text-primary);display:flex;align-items:center;gap:8px;">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height:24px;" onerror="this.style.display='none'" />
            Attendance Dashboard – {{ $month }}/{{ $year }}
        </h3>
        <button id="theme-toggle" class="theme-toggle-btn" title="Toggle Light/Dark Theme">
            <span>&#9728;&#65039;</span> Theme
        </button>
    </div>
    
    <div style="display:flex;align-items:center;gap:8px;">
        <label for="att-search" style="font-weight:600;font-size:13px;color:var(--text-primary);">Quick search:</label>
        <input id="att-search" type="text" placeholder="Search by name, ID, designation" style="padding:6px 10px;border-radius:4px;border:1px solid var(--border-color);min-width:280px;font-size:13px;background:var(--input-bg);color:var(--text-primary);">
        <span style="font-size:12px;color:var(--text-secondary);">Tip: drag to select text for copy.</span>
    </div>
</div>

<!-- Legend -->
<div style="margin-bottom:10px; padding:8px; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:4px; display:flex; flex-wrap:wrap; gap:15px; font-size:12px; color:var(--text-primary);">
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/present.png') }}" width="16" height="16"> <span>Present</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><span style="color:#ff3333;font-weight:900;font-size:14px;">!</span> <span>Late</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/absent.png') }}" width="16" height="16"> <span>Absent</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/on_leave.png') }}" width="16" height="16"> <span>On Leave</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/holidays.png') }}" width="16" height="16"> <span>Holiday</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/half_day.png') }}" width="16" height="16"> <span>Half Day</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/early_leave.png') }}" width="16" height="16"> <span>Early Leave</span></div>
    <div style="display:flex;align-items:center;gap:5px;"><img src="{{ asset('icons/off_day.png') }}" width="16" height="16"> <span>Off Day</span></div>
</div>

<!-- Table -->
<div class="table-container" role="region" aria-label="Attendance table" style="user-select:text; overflow-x:auto;">
  <table class="admin-table" role="table" aria-describedby="attendance-desc">
    <thead>
      <tr style="background-color:#393737">
        <th scope="col" style="width:35px;">#</th>
        <th style="z-index: 30 !important;" scope="col">Employee</th>
        <th scope="col">Designation</th>
        @foreach($daysForGrid as $day)
          <th scope="col" title="{{ $day['full'] }}">
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;line-height:1.4;padding:4px 0;">
              <div style="font-weight:600;font-size:13px;">{{ $day['num'] }}</div>
              <div style="font-size:11px;color:#aaa;">{{ $day['short'] }}</div>
            </div>
          </th>
        @endforeach
        <th scope="col" class="attend-data">Total Present</th>
        <th scope="col" class="attend-data">Late</th>
        <th scope="col" class="attend-data">On Leave</th>
        <th scope="col" class="attend-data">Holiday</th>
        <th scope="col" class="attend-data">Absent</th>
        <th scope="col" class="attend-data">Half Day</th>
        <th scope="col" class="attend-data">Early Leave</th>
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
                            'checkin_time' => !empty($st['checkin_time']) ? \Carbon\Carbon::parse($st['checkin_time'])->format('H:i') : '',
                            'checkout_time' => !empty($st['checkout_time']) ? \Carbon\Carbon::parse($st['checkout_time'])->format('H:i') : '',
                            'popup' => 1,
                        ];
                    @endphp
                    <a href="#" onclick="return openPopup('{{ route('attendance-records.edit', array_merge(['id' => $st['id'] ?? 0], $editParams)) }}');" aria-label="Edit record">
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
                    </a>
                @else
                    <a href="#" onclick="return openPopup('{{ route('attendance-records.create', ['employee_id' => $emp['employee_id'], 'date' => $day['full'], 'popup' => 1]) }}');" style="color:#ccc; text-decoration:none;">&mdash;</a>
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
@endsection
