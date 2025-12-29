@extends('layouts.app')

@section('title', __('messages.export_import'))

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-arrow-down-up me-2"></i>{{ __('messages.export_import') }}</h2>
            <p class="text-muted">
                @if($isSuperAdmin ?? false)
                    Super Admin: Export/import data for any or all organizations
                @else
                    {{ __('messages.export_import_desc') }}
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
                    <h5 class="mb-0"><i class="bi bi-download me-2"></i>{{ __('messages.export_data') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ __('messages.export_data_desc') }}</p>
                    
                    <form method="GET" action="{{ route('export.download') }}" id="exportForm">
                        
                        @if($isSuperAdmin ?? false)
                        <!-- Super Admin: Export Mode Selection -->
                        <div class="mb-3 p-3 bg-light rounded border">
                            <label class="form-label fw-bold text-primary"><i class="bi bi-shield-check me-1"></i>{{ __('messages.super_admin_export_mode') }}</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_mode" value="single" id="mode_single" checked>
                                <label class="form-check-label" for="mode_single">
                                    <strong>{{ __('messages.mode_single') }}</strong> - {{ __('messages.mode_single_desc') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_mode" value="all" id="mode_all">
                                <label class="form-check-label" for="mode_all">
                                    <strong>{{ __('messages.mode_all') }}</strong> - {{ __('messages.mode_all_desc') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_mode" value="selected" id="mode_selected">
                                <label class="form-check-label" for="mode_selected">
                                    <strong>{{ __('messages.mode_selected') }}</strong> - {{ __('messages.mode_selected_desc') }}
                                </label>
                            </div>
                        </div>
                        
                        <!-- Organization selector for single mode -->
                        <div class="mb-3" id="singleOrgSelector">
                            <label class="form-label fw-bold">{{ __('messages.select_org') }}</label>
                            <select name="organization_id" class="form-select">
                                <option value="">{{ __('messages.current_org') }}</option>
                                @foreach($organizations ?? [] as $org)
                                    <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Multi-select for selected mode -->
                        <div class="mb-3" id="multiOrgSelector" style="display:none;">
                            <label class="form-label fw-bold">{{ __('messages.select_orgs') }}</label>
                            <div style="max-height:200px;overflow-y:auto;border:1px solid #ddd;padding:10px;border-radius:4px;">
                                @foreach($organizations ?? [] as $org)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="selected_orgs[]" value="{{ $org['id'] }}" id="org_{{ $org['id'] }}">
                                    <label class="form-check-label" for="org_{{ $org['id'] }}">{{ $org['name'] }}</label>
                                </div>
                                @endforeach
                            </div>
                            <small class="text-muted">{{ __('messages.hold_ctrl') }}</small>
                        </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('messages.what_to_export') }}</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include[]" value="employees" id="exp_employees" checked>
                                <label class="form-check-label" for="exp_employees">{{ __('messages.exp_employees') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include[]" value="attendance" id="exp_attendance" checked>
                                <label class="form-check-label" for="exp_attendance">{{ __('messages.exp_attendance') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include[]" value="shifts" id="exp_shifts" checked>
                                <label class="form-check-label" for="exp_shifts">{{ __('messages.exp_shifts') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include[]" value="holidays" id="exp_holidays" checked>
                                <label class="form-check-label" for="exp_holidays">{{ __('messages.exp_holidays') }}</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include[]" value="salary" id="exp_salary">
                                <label class="form-check-label" for="exp_salary">{{ __('messages.exp_salary') }}</label>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">{{ __('messages.month_attendance') }}</label>
                                <select name="month" class="form-select">
                                    <option value="">{{ __('messages.all_last_3_months') }}</option>
                                    @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">{{ __('messages.year') }}</label>
                                <select name="year" class="form-select">
                                    @for($y = date('Y'); $y >= date('Y') - 2; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-download me-2"></i>{{ __('messages.download_export') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Import Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-upload me-2"></i>{{ __('messages.import_data') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ __('messages.import_data_desc') }}</p>
                    
                    <form method="POST" action="{{ route('export.import') }}" enctype="multipart/form-data">
                        @csrf
                        
                        @if($isSuperAdmin ?? false)
                        <!-- Super Admin: Target organization selector -->
                        <div class="mb-3 p-3 bg-light rounded border">
                            <label class="form-label fw-bold text-primary"><i class="bi bi-shield-check me-1"></i>{{ __('messages.import_to_org') }}</label>
                            <select name="target_org_id" class="form-select">
                                <option value="">{{ __('messages.current_org') }}</option>
                                @foreach($organizations ?? [] as $org)
                                    <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ __('messages.select_import_org_desc') }}</small>
                        </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('messages.select_json') }}</label>
                            <input type="file" name="import_file" class="form-control" accept=".json" required>
                            <small class="text-muted">{{ __('messages.only_json_files') }}</small>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>{{ __('messages.import_note') }}</strong>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload me-2"></i>{{ __('messages.import_data') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Help Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-question-circle me-2"></i>{{ __('messages.help') }}</h5>
        </div>
        <div class="card-body">
            <h6>{{ __('messages.export_tips') }}</h6>
            <ul>
                <li>{{ __('messages.export_tip_1') }}</li>
                <li>{{ __('messages.export_tip_2') }}</li>
                <li>{{ __('messages.export_tip_3') }}</li>
                @if($isSuperAdmin ?? false)
                <li><strong>Super Admin:</strong> "All Organizations" creates a ZIP with separate JSON files for each org</li>
                @endif
            </ul>
            
            <h6>{{ __('messages.import_tips') }}</h6>
            <ul>
                <li>{{ __('messages.import_tip_1') }}</li>
                <li>{{ __('messages.import_tip_2') }}</li>
                <li>{{ __('messages.import_tip_3') }}</li>
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
