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
        color: var(--btn-primary);
    }
    .th-sortable.active {
        color: var(--btn-primary);
    }
    
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    .badge.success { background: #d1e7dd; color: #0f5132; }
    .badge.inactive { background: #f8d7da; color: #842029; }

    .btn-edit {
        padding: 4px 8px;
        border: 1px solid var(--border-color);
        background: var(--bg-primary);
        border-radius: 4px;
        cursor: pointer;
        color: var(--btn-primary);
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
    }
    .btn-delete {
        padding: 4px 8px;
        border: 1px solid var(--border-color);
        background: var(--bg-primary);
        border-radius: 4px;
        cursor: pointer;
        color: #dc3545;
        font-weight: 600;
        margin-left: 5px;
    }
</style>

<div style="padding: 20px;">
    <div class="page-header" style="margin-bottom: 20px;">
        <h2 style="margin:0; font-size: 20px; font-weight: 600; color: var(--text-primary);">Shifts</h2>
        <p style="margin:0; color:var(--text-secondary); font-size:14px;">Manage work shifts and timings</p>
    </div>

    @if(!empty($error))
        <div class="alert alert-danger" style="background:#f8d7da; color:#842029; padding:12px; border-radius:6px; margin-bottom:20px; border: 1px solid #f5c2c7;">API error: {{ $error }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success" style="background:#d1e7dd; color:#0f5132; padding:12px; border-radius:6px; margin-bottom:20px; border: 1px solid #badbcc;">{{ session('success') }}</div>
    @endif

    <div style="background:var(--card); padding:20px; border-radius:8px; border:1px solid var(--border-color); margin-bottom:25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <form id="shiftForm" method="POST" action="{{ route('shifts.store') }}">
            @csrf
            <div id="methodField"></div>
            <h3 style="margin:0 0 15px 0; font-size:16px; color:var(--text-primary); border-bottom:1px solid var(--border-color); padding-bottom:10px;">Add / Update Shift</h3>
            
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; align-items:end;">
                <input type="hidden" name="id" id="shift_id">
                
                <div>
                    <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">Name</label>
                    <input name="name" id="shift_name" required class="form-control" style="width:100%; padding:8px; border:1px solid var(--input-border); border-radius:4px; background:var(--input-bg); color:var(--input-text);">
                </div>
                
                <div>
                    <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">Start Time</label>
                    <input type="time" name="start" id="shift_start" required class="form-control" style="width:100%; padding:8px; border:1px solid var(--input-border); border-radius:4px; background:var(--input-bg); color:var(--input-text);">
                </div>
                
                <div>
                    <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">End Time</label>
                    <input type="time" name="end" id="shift_end" required class="form-control" style="width:100%; padding:8px; border:1px solid var(--input-border); border-radius:4px; background:var(--input-bg); color:var(--input-text);">
                </div>
                
                <div>
                    <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">Half Day (hrs)</label>
                    <input type="number" step="0.1" name="half_day_hours" id="shift_half" value="4.0" class="form-control" style="width:100%; padding:8px; border:1px solid var(--input-border); border-radius:4px; background:var(--input-bg); color:var(--input-text);">
                </div>
                
                <div>
                    <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">Present (hrs)</label>
                    <input type="number" step="0.1" name="present_hours" id="shift_present" value="8.0" class="form-control" style="width:100%; padding:8px; border:1px solid var(--input-border); border-radius:4px; background:var(--input-bg); color:var(--input-text);">
                </div>
                
                <div>
                    <label style="font-size:13px; font-weight:600; color:var(--text-primary); margin-bottom:5px; display:block;">Allowed Late (min)</label>
                    <input type="number" name="allowed_late_minutes" id="shift_late" value="0" class="form-control" style="width:100%; padding:8px; border:1px solid var(--input-border); border-radius:4px; background:var(--input-bg); color:var(--input-text);">
                </div>
                
                <div style="padding-bottom:10px;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:14px; color:var(--text-primary);">
                        <input type="checkbox" name="is_active" id="shift_active" value="1" style="width:16px; height:16px; accent-color:var(--btn-primary);"> 
                        Active Shift
                    </label>
                </div>
                
                <div style="display:flex; gap:10px;">
                    <button type="submit" id="saveBtn" style="padding:10px 20px; border:none; border-radius:4px; background:var(--btn-primary); color:var(--btn-text); font-weight:600; cursor:pointer;">Save Shift</button>
                    <button type="button" id="cancelBtn" onclick="resetForm()" style="padding:10px 20px; border:1px solid var(--border-color); border-radius:4px; background:#6c757d; color:#fff; font-weight:600; cursor:pointer; display:none;">Cancel</button>
                </div>
            </div>
        </form>
    </div>

    <div class="admin-table-container">
        @php
            $currentSort = request('sort', 'name');
            $currentDir = request('dir', 'asc');
            $toggleDir = $currentDir === 'asc' ? 'desc' : 'asc';
            $sortParams = request()->except(['sort', 'dir']);
        @endphp
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Owner</th>
                    <th>
                        <a href="{{ route('shifts.index', array_merge($sortParams, ['sort' => 'name', 'dir' => $currentSort === 'name' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'name' ? 'active' : '' }}">
                            Name {!! $currentSort === 'name' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('shifts.index', array_merge($sortParams, ['sort' => 'start', 'dir' => $currentSort === 'start' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'start' ? 'active' : '' }}">
                            Time {!! $currentSort === 'start' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                        </a>
                    </th>
                    <th>Hours (H/P)</th>
                    <th>
                        <a href="{{ route('shifts.index', array_merge($sortParams, ['sort' => 'allowed_late_minutes', 'dir' => $currentSort === 'allowed_late_minutes' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'allowed_late_minutes' ? 'active' : '' }}">
                            Late (min) {!! $currentSort === 'allowed_late_minutes' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('shifts.index', array_merge($sortParams, ['sort' => 'is_active', 'dir' => $currentSort === 'is_active' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'is_active' ? 'active' : '' }}">
                            Active {!! $currentSort === 'is_active' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                        </a>
                    </th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($shifts as $s)
                <tr>
                    <td>{{ $s['organization_name'] ?? 'Global' }}</td>
                    <td style="font-weight:600;">{{ $s['name'] }}</td>
                    <td>{{ $s['start'] }} - {{ $s['end'] }}</td>
                    <td>{{ $s['half_day_hours'] }} / {{ $s['present_hours'] }}</td>
                    <td>{{ $s['allowed_late_minutes'] }}</td>
                    <td>
                        @if($s['is_active'])
                            <span class="badge success">Active</span>
                        @else
                            <span class="badge inactive">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex; gap:5px;">
                            <button onclick='editShift(@json($s))' class="btn-edit">Edit</button>
                            <form action="{{ route('shifts.destroy', $s['id'] ?? 0) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this shift?');" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center; padding:20px; color:var(--text-secondary);">No shifts found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function editShift(s) {
        document.getElementById('shift_id').value = s.id;
        document.getElementById('shift_name').value = s.name;
        document.getElementById('shift_start').value = s.start;
        document.getElementById('shift_end').value = s.end;
        document.getElementById('shift_half').value = s.half_day_hours;
        document.getElementById('shift_present').value = s.present_hours;
        document.getElementById('shift_late').value = s.allowed_late_minutes;
        document.getElementById('shift_active').checked = s.is_active;
        
        // Update form action and method
        var form = document.getElementById('shiftForm');
        form.action = "{{ url('/shifts') }}/" + s.id;
        document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('saveBtn').innerText = 'Update Shift';
        document.getElementById('cancelBtn').style.display = 'inline-block';
        
        window.scrollTo({top:0, behavior:'smooth'});
    }

    function resetForm() {
        document.getElementById('shiftForm').reset();
        document.getElementById('shiftForm').action = "{{ route('shifts.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('saveBtn').innerText = 'Save Shift';
        document.getElementById('cancelBtn').style.display = 'none';
        document.getElementById('shift_id').value = '';
    }
</script>
@endsection
