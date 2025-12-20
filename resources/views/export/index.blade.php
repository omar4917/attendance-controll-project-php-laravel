@extends('layouts.app')

@section('title', 'Export / Import')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-arrow-down-up me-2"></i>Data Export / Import</h2>
            <p class="text-muted">
                @if($isSuperAdmin ?? false)
                    Super Admin: Export/import data for any or all organizations
                @else
                    Export your organization's data or import from a backup
                @endif
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Export Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-download me-2"></i>Export Data</h5>
                </div>
                <div class="card-body">
                    <p>Download data as JSON files for backup or migration.</p>
                    
                    <form method="GET" action="{{ route('export.download') }}" id="exportForm">
                        
                        @if($isSuperAdmin ?? false)
                        <!-- Super Admin: Export Mode Selection -->
                        <div class="mb-3 p-3 bg-light rounded border">
                            <label class="form-label fw-bold text-primary"><i class="bi bi-shield-check me-1"></i>Super Admin Export Mode:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_mode" value="single" id="mode_single" checked>
                                <label class="form-check-label" for="mode_single">
                                    <strong>Single Organization</strong> - Export one organization
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_mode" value="all" id="mode_all">
                                <label class="form-check-label" for="mode_all">
                                    <strong>All Organizations</strong> - Export all as ZIP (batch backup)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_mode" value="selected" id="mode_selected">
                                <label class="form-check-label" for="mode_selected">
                                    <strong>Selected Organizations</strong> - Choose specific ones
                                </label>
                            </div>
                        </div>
                        
                        <!-- Organization selector for single mode -->
                        <div class="mb-3" id="singleOrgSelector">
                            <label class="form-label fw-bold">Select Organization:</label>
                            <select name="organization_id" class="form-select">
                                <option value="">-- Current Organization --</option>
                                @foreach($organizations ?? [] as $org)
                                    <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Multi-select for selected mode -->
                        <div class="mb-3" id="multiOrgSelector" style="display:none;">
                            <label class="form-label fw-bold">Select Organizations:</label>
                            <div style="max-height:200px;overflow-y:auto;border:1px solid #ddd;padding:10px;border-radius:4px;">
                                @foreach($organizations ?? [] as $org)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="selected_orgs[]" value="{{ $org['id'] }}" id="org_{{ $org['id'] }}">
                                    <label class="form-check-label" for="org_{{ $org['id'] }}">{{ $org['name'] }}</label>
                                </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Hold Ctrl to select multiple</small>
                        </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">What to Export:</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include[]" value="employees" id="exp_employees" checked>
                                <label class="form-check-label" for="exp_employees">Employees</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include[]" value="attendance" id="exp_attendance" checked>
                                <label class="form-check-label" for="exp_attendance">Attendance Records</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include[]" value="shifts" id="exp_shifts" checked>
                                <label class="form-check-label" for="exp_shifts">Shifts</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include[]" value="holidays" id="exp_holidays" checked>
                                <label class="form-check-label" for="exp_holidays">Holidays</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include[]" value="salary" id="exp_salary">
                                <label class="form-check-label" for="exp_salary">Salary Reports</label>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Month (for attendance)</label>
                                <select name="month" class="form-select">
                                    <option value="">All (last 3 months)</option>
                                    @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Year</label>
                                <select name="year" class="form-select">
                                    @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-download me-2"></i>Download Export
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Import Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-upload me-2"></i>Import Data</h5>
                </div>
                <div class="card-body">
                    <p>Upload a previously exported JSON file to restore or migrate data.</p>
                    
                    <form method="POST" action="{{ route('export.import') }}" enctype="multipart/form-data">
                        @csrf
                        
                        @if($isSuperAdmin ?? false)
                        <!-- Super Admin: Target organization selector -->
                        <div class="mb-3 p-3 bg-light rounded border">
                            <label class="form-label fw-bold text-primary"><i class="bi bi-shield-check me-1"></i>Import to Organization:</label>
                            <select name="target_org_id" class="form-select">
                                <option value="">-- Current Organization --</option>
                                @foreach($organizations ?? [] as $org)
                                    <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select which organization to import data into</small>
                        </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select JSON File:</label>
                            <input type="file" name="import_file" class="form-control" accept=".json" required>
                            <small class="text-muted">Only .json files exported from this system</small>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> Existing records with matching IDs will be updated. 
                            Employee limits based on subscription plan will be enforced.
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-2"></i>Import Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Help Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-question-circle me-2"></i>Help</h5>
        </div>
        <div class="card-body">
            <h6>Export Tips:</h6>
            <ul>
                <li>Export creates a complete backup of organization data</li>
                <li>Use monthly exports for archiving attendance records</li>
                <li>Store exports securely - they contain employee information</li>
                @if($isSuperAdmin ?? false)
                <li><strong>Super Admin:</strong> "All Organizations" creates a ZIP with separate JSON files for each org</li>
                @endif
            </ul>
            
            <h6>Import Tips:</h6>
            <ul>
                <li>Import will create new records or update existing ones</li>
                <li>Employee IDs are used to match existing records</li>
                <li>Subscription plan limits will be enforced during import</li>
            </ul>
        </div>
    </div>
</div>

@if($isSuperAdmin ?? false)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modeRadios = document.querySelectorAll('input[name="export_mode"]');
    const singleSelector = document.getElementById('singleOrgSelector');
    const multiSelector = document.getElementById('multiOrgSelector');
    
    function updateVisibility() {
        const mode = document.querySelector('input[name="export_mode"]:checked').value;
        
        if (mode === 'single') {
            singleSelector.style.display = 'block';
            multiSelector.style.display = 'none';
        } else if (mode === 'selected') {
            singleSelector.style.display = 'none';
            multiSelector.style.display = 'block';
        } else {
            // 'all' mode - hide both
            singleSelector.style.display = 'none';
            multiSelector.style.display = 'none';
        }
    }
    
    modeRadios.forEach(radio => radio.addEventListener('change', updateVisibility));
    updateVisibility();
});
</script>
@endif
@endsection
