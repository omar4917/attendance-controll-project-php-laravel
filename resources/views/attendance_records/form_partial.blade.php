<style>
    body {
        background-color: var(--bg-primary);
        color: var(--text-primary);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .form-container {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    .page-title {
        font-size: 20px;
        font-weight: 400;
        color: var(--text-primary);
        margin-bottom: 20px;
    }
    .section-header {
        background-color: var(--bg-header); /* Use header color */
        color: var(--text-secondary); /* Use text secondary or primary */
        padding: 10px 15px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        margin-top: 20px;
        margin-bottom: 15px;
        border-radius: 4px;
        border: 1px solid var(--border-header);
    }
    .form-row {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 15px;
    }
    .form-row:last-child {
        border-bottom: none;
    }
    .form-label {
        width: 150px;
        font-weight: 600;
        font-size: 13px;
        color: var(--text-primary);
    }
    .form-input-container {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-control, .form-select {
        background-color: var(--input-bg);
        border: 1px solid var(--input-border);
        color: var(--input-text);
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 14px;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.25); /* Green shadow */
    }
    .btn-icon {
        color: var(--primary);
        cursor: pointer;
        font-size: 16px;
    }
    .btn-icon:hover {
        color: var(--text-primary);
    }
    .btn-delete {
        color: #dc3545;
        cursor: pointer;
        font-size: 16px;
    }
    .btn-toolbar {
        background-color: var(--bg-quaternary);
        padding: 10px 15px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 30px;
        position: sticky;
        bottom: 0;
    }
    .btn-primary {
        background-color: var(--btn-primary);
        border: none;
        padding: 8px 16px;
        color: var(--btn-text);
        font-weight: 600;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-primary:hover {
        opacity: 0.9;
    }
    .btn-secondary {
        background-color: #6c757d;
        border: none;
        padding: 8px 16px;
        color: #fff;
        font-weight: 600;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-danger {
        background-color: #dc3545;
        border: none;
        padding: 8px 16px;
        color: #fff;
        font-weight: 600;
        border-radius: 4px;
        cursor: pointer;
    }
    .help-text {
        font-size: 11px;
        color: var(--text-secondary);
        margin-top: 4px;
    }
    a { color: var(--primary); text-decoration: none; }
    a:hover { color: var(--text-primary); }
</style>

<div class="form-container">
    <h2 class="page-title">
        {{ isset($record) ? 'Change attendance record' : 'Add attendance record' }}
    </h2>
    
    @if(isset($record))
        <div style="margin-bottom: 20px; font-size: 14px; font-weight: 600;">
            {{ $record['employee_id'] }} | IN: {{ $record['checkin_time'] ? \Carbon\Carbon::parse($record['checkin_time'])->format('Y-m-d h:i A') : '-' }} | OUT: {{ $record['checkout_time'] ? \Carbon\Carbon::parse($record['checkout_time'])->format('Y-m-d h:i A') : '-' }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success" style="background:#d1e7dd; color:#0f5132; border:1px solid #badbcc; padding:10px; margin-bottom:15px; border-radius:4px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="background:#f8d7da; color:#842029; border:1px solid #f5c2c7; padding:10px; margin-bottom:15px; border-radius:4px;">{{ session('error') }}</div>
    @endif

    <form action="{{ isset($record) ? route('attendance-records.update', $record['id']) : route('attendance-records.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($record))
            @method('PUT')
        @endif
        
        @if(request('popup'))
            <input type="hidden" name="popup" value="1">
        @endif

        <!-- General Info -->
        <div class="form-row">
            <label class="form-label">Employee:</label>
            <div class="form-input-container">
                <select name="employee_id" class="form-select" style="width: 300px;" required>
                    <option value="">---------</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp['employee_id'] }}" {{ (old('employee_id', $record['employee_id'] ?? '') == $emp['employee_id']) ? 'selected' : '' }}>
                            {{ $emp['employee_id'] }} - {{ $emp['name'] }}
                        </option>
                    @endforeach
                </select>
                <span class="btn-icon">✎</span>
                <span class="btn-icon">+</span>
                <span class="btn-icon">👁</span>
            </div>
        </div>

        <div class="form-row">
            <label class="form-label">Date:</label>
            <div class="form-input-container">
                <input type="date" name="date" class="form-control" style="width: 150px;" value="{{ old('date', $record['date'] ?? date('Y-m-d')) }}" required>
                <span style="font-size:12px; cursor:pointer; color:var(--primary);" onclick="document.querySelector('input[name=date]').value = new Date().toISOString().split('T')[0]">Today</span>
            </div>
        </div>

        <div class="form-row">
            <label class="form-label">Shift:</label>
            <div class="form-input-container">
                <select name="shift_id" class="form-select" style="width: 300px;">
                    <option value="">---------</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift['id'] }}" {{ (old('shift_id', $record['shift_id'] ?? '') == $shift['id']) ? 'selected' : '' }}>
                            {{ $shift['name'] }} ({{ $shift['start'] }} - {{ $shift['end'] }})
                        </option>
                    @endforeach
                </select>
                <span class="btn-icon">✎</span>
                <span class="btn-icon">+</span>
                <span class="btn-icon">✕</span>
                <span class="btn-icon">👁</span>
            </div>
        </div>
        <div style="margin-left: 150px; font-size: 11px; color: var(--text-secondary); margin-top: -10px; margin-bottom: 15px;">
            Shift used for this attendance day. If empty, falls back to the active shift.
        </div>

        <!-- Time & Images Section -->
        <div class="section-header">Time & Images</div>

        <div class="form-row">
            <label class="form-label">Checkin time:</label>
            <div class="form-input-container">
                <input type="time" name="checkin_time" class="form-control" style="width: 150px;" value="{{ old('checkin_time', !empty($record['checkin_time']) ? \Carbon\Carbon::parse($record['checkin_time'])->format('H:i') : '') }}">
                <label class="form-label" style="width: auto; margin-left: 20px;">Checkin image:</label>
                <input type="file" name="checkin_image" class="form-control" style="width: 250px;">
                @if(!empty($record['checkin_image']))
                    <a href="{{ $record['checkin_image'] }}" target="_blank" style="font-size:12px;">View Current</a>
                @else
                    <span style="font-size:12px; color:var(--text-secondary);">No file selected.</span>
                @endif
            </div>
        </div>

        <div class="form-row">
            <label class="form-label">Checkout time:</label>
            <div class="form-input-container">
                <input type="time" name="checkout_time" class="form-control" style="width: 150px;" value="{{ old('checkout_time', !empty($record['checkout_time']) ? \Carbon\Carbon::parse($record['checkout_time'])->format('H:i') : '') }}">
                <label class="form-label" style="width: auto; margin-left: 20px;">Checkout image:</label>
                <input type="file" name="checkout_image" class="form-control" style="width: 250px;">
                @if(!empty($record['checkout_image']))
                    <a href="{{ $record['checkout_image'] }}" target="_blank" style="font-size:12px;">View Current</a>
                @else
                    <span style="font-size:12px; color:var(--text-secondary);">No file selected.</span>
                @endif
            </div>
        </div>

        <div class="form-row">
            <label class="form-label">Device Id:</label>
            <div class="form-input-container">
                <input type="text" name="device_id" class="form-control" style="width: 250px;" value="{{ old('device_id', $record['device_id'] ?? '') }}">
            </div>
        </div>
        <div style="margin-left: 150px; font-size: 11px; color: var(--text-secondary); margin-top: -10px; margin-bottom: 15px;">
            Source device identifier (optional)
        </div>

        <!-- Status & Override Section -->
        <div class="section-header">Status & Override</div>

        <div class="form-row">
            <label class="form-label">Status:</label>
            <div class="form-input-container">
                <select name="status" id="status_select" class="form-select" style="width: 200px;">
                    @foreach(['Present', 'Late', 'Early Leave', 'Absent', 'Half Day', 'On Leave', 'Holiday', 'Pending', 'Off Day'] as $st)
                        <option value="{{ $st }}" {{ (old('status', $record['status'] ?? 'Present') == $st) ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <label class="form-label">Late duration:</label>
            <div class="form-input-container">
                <span style="color: var(--text-primary);">{{ $record['late_duration'] ?? '0:00:00' }}</span>
            </div>
        </div>
        <div style="margin-left: 150px; font-size: 11px; color: var(--text-secondary); margin-top: -10px; margin-bottom: 15px;">
            Duration employee was late beyond allowed minutes
        </div>

        <div class="form-row">
            <label class="form-label">Is status override:</label>
            <div class="form-input-container">
                <!-- Single hidden field that JS will update -->
                <input type="hidden" name="is_status_override" value="{{ !empty($record['is_status_override']) ? '1' : '0' }}" id="is_status_override_value">
                <input type="checkbox" id="is_status_override_checkbox" 
                       {{ !empty($record['is_status_override']) ? 'checked' : '' }}
                       style="width:18px; height:18px; cursor:pointer;"
                       onchange="document.getElementById('is_status_override_value').value = this.checked ? '1' : '0';">
                @if(!empty($record['is_status_override']))
                    <span id="override_label" style="color: #f0ad4e; font-weight: bold; margin-left: 5px;">⚠ Manual</span>
                @else  
                    <span id="override_label" style="color: #dc3545; font-weight: bold; margin-left: 5px;">✕</span>
                @endif
            </div>
        </div>
        <div style="margin-left: 150px; font-size: 11px; color: var(--text-secondary); margin-top: -10px; margin-bottom: 15px;">
            If True, status is manually set and will not be auto-calculated.
        </div>

        <!-- Footer Buttons -->
        <div class="btn-toolbar">
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn-primary">SAVE</button>
                <button type="submit" name="save_and_add_another" value="1" class="btn-secondary">Save and add another</button>
                <button type="submit" name="save_and_continue" value="1" class="btn-secondary">Save and continue editing</button>
            </div>
            
            @if(isset($record))
                <button type="button" class="btn-danger" onclick="if(confirm('Are you sure?')) document.getElementById('delete-form').submit();">Delete</button>
            @endif
        </div>
    </form>

<script>
// Ensure hidden field syncs with checkbox on page load
document.addEventListener('DOMContentLoaded', function() {
    var checkbox = document.getElementById('is_status_override_checkbox');
    var hidden = document.getElementById('is_status_override_value');
    var label = document.getElementById('override_label');
    var statusSelect = document.getElementById('status_select');
    var originalStatus = statusSelect ? statusSelect.value : null;
    
    // Function to update override UI
    function setOverrideEnabled(enabled) {
        if (checkbox) checkbox.checked = enabled;
        if (hidden) hidden.value = enabled ? '1' : '0';
        if (label) {
            label.innerHTML = enabled ? '⚠ Manual' : '✕';
            label.style.color = enabled ? '#f0ad4e' : '#dc3545';
        }
    }
    
    if (checkbox && hidden) {
        // Sync on checkbox change
        checkbox.addEventListener('change', function() {
            setOverrideEnabled(this.checked);
        });
        
        // Ensure sync before form submit
        var form = checkbox.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                hidden.value = checkbox.checked ? '1' : '0';
            });
        }
    }
    
    // Auto-enable override when status is manually changed
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            if (this.value !== originalStatus) {
                setOverrideEnabled(true);
            }
        });
    }
});
</script>
    
    @if(isset($record))
        <form id="delete-form" action="{{ route('attendance-records.destroy', $record['id']) }}" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>
