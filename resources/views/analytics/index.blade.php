@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            @if($isSuperAdmin)
            <h2><i class="bi bi-graph-up-arrow me-2"></i>{{ __('messages.system_analytics') }}</h2>
            <p class="text-muted">{{ __('messages.cross_org_metrics') }}</p>
            @else
            <h2><i class="bi bi-graph-up me-2"></i>{{ __('messages.organization_analytics') }}</h2>
            <p class="text-muted">{{ __('messages.your_org_metrics') }}</p>
            @endif
        </div>
    </div>

    @if($error)
    <div class="alert alert-danger">{{ $error }}</div>
    @endif

    <!-- Summary Cards - Both Roles -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill fs-1 text-primary"></i>
                    <h3 class="mt-2 mb-0">{{ $summary['active_employees'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('messages.active_employees') }}</p>
                    <small class="text-secondary">of {{ $summary['total_employees'] ?? 0 }} total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-phone-fill fs-1 text-success"></i>
                    <h3 class="mt-2 mb-0">{{ $summary['active_devices'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">{{ __('messages.active_devices') }}</p>
                    <small class="text-secondary">of {{ $summary['total_devices'] ?? 0 }} total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-percent fs-1 text-info"></i>
                    <h3 class="mt-2 mb-0">{{ $attendance['attendance_rate'] ?? 0 }}%</h3>
                    <p class="text-muted mb-0">{{ __('messages.attendance_rate') }}</p>
                    <small class="text-secondary">This month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-camera-video-fill fs-1 text-warning"></i>
                    <h3 class="mt-2 mb-0">{{ $livefeedToday }}</h3>
                    <p class="text-muted mb-0">{{ __('messages.live_feed_today') }}</p>
                    <small class="text-secondary">{{ __('messages.captures') }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Attendance This Month - Both Roles -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>{{ __('messages.attendance_this_month') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="p-2 rounded bg-success bg-opacity-10">
                                <h4 class="text-success mb-0">{{ $attendance['present'] ?? 0 }}</h4>
                                <small>Present</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded bg-warning bg-opacity-10">
                                <h4 class="text-warning mb-0">{{ $attendance['late'] ?? 0 }}</h4>
                                <small>Late</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded bg-danger bg-opacity-10">
                                <h4 class="text-danger mb-0">{{ $attendance['absent'] ?? 0 }}</h4>
                                <small>Absent</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-2 rounded bg-info bg-opacity-10">
                                <h4 class="text-info mb-0">{{ $attendance['on_leave'] ?? 0 }}</h4>
                                <small>On Leave</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress bar -->
                    <div class="mt-4">
                        <label class="form-label small">{{ __('messages.attendance_rate') }}</label>
                        @php $rate = $attendance['attendance_rate'] ?? 0; @endphp
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: {{ $rate }}%">
                                {{ $rate }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Last 7 Days - Both Roles -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-activity me-2"></i>{{ __('messages.activity_last_7_days') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <i class="bi bi-box-arrow-in-right fs-2 text-primary"></i>
                                <h4 class="mb-0">{{ $activity['logins'] ?? 0 }}</h4>
                                <small class="text-muted">Logins</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <i class="bi bi-lightning-fill fs-2 text-warning"></i>
                                <h4 class="mb-0">{{ $activity['actions'] ?? 0 }}</h4>
                                <small class="text-muted">Actions</small>
                            </div>
                        </div>
                    </div>
                    
                    @if(!empty($activity['top_users']))
                    <h6 class="mb-2">{{ __('messages.top_active_users') }}</h6>
                    <ul class="list-group list-group-flush">
                        @foreach($activity['top_users'] as $user)
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span><i class="bi bi-person me-2"></i>{{ $user['user_email'] }}</span>
                            <span class="badge bg-primary rounded-pill">{{ $user['count'] }}</span>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- SUPER ADMIN ONLY: Plan Usage Cards (All Organizations) --}}
    {{-- ============================================================== --}}
    @if($isSuperAdmin && !empty($planUsage))
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">
                <i class="bi bi-grid-3x3-gap-fill me-2 text-primary"></i>
                {{ __('messages.organizations_overview') }}
            </h5>
            <span class="badge bg-primary rounded-pill">{{ count($planUsage) }} Organizations</span>
        </div>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
            @foreach($planUsage as $usage)
            @php 
                $emp = $usage['employees'] ?? ['used' => 0, 'max' => 0, 'percent' => 0];
                $dev = $usage['devices'] ?? ['used' => 0, 'max' => 0, 'percent' => 0];
                $planName = $usage['plan_name'] ?? null;
                $empWarning = $emp['percent'] >= 90;
                $devWarning = $dev['percent'] >= 90;
            @endphp
            <div class="col">
                <div class="card h-100 border-0 shadow-sm {{ ($empWarning || $devWarning) ? 'border-start border-warning border-3' : '' }}">
                    <div class="card-body">
                        {{-- Organization Header --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="card-title mb-1 fw-bold">
                                    <i class="bi bi-building me-1 text-primary"></i>
                                    {{ $usage['organization_name'] }}
                                </h6>
                                @if($planName && $planName !== 'No Plan')
                                <span class="badge bg-success bg-opacity-75">
                                    <i class="bi bi-award me-1"></i>{{ $planName }}
                                </span>
                                @else
                                <span class="badge bg-secondary bg-opacity-50">
                                    <i class="bi bi-dash-circle me-1"></i>{{ __('messages.no_plan') }}
                                </span>
                                @endif
                            </div>
                            @if($empWarning || $devWarning)
                            <span class="badge bg-warning text-dark" title="Approaching limit">
                                <i class="bi bi-exclamation-triangle"></i>
                            </span>
                            @endif
                        </div>
                        
                        {{-- Employees Usage --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">
                                    <i class="bi bi-people-fill me-1"></i>Employees
                                </small>
                                <small class="fw-semibold {{ $empWarning ? 'text-danger' : '' }}">
                                    {{ $emp['used'] }} / {{ $emp['max'] == 999999 ? '∞' : $emp['max'] }}
                                </small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $emp['percent'] >= 90 ? 'bg-danger' : ($emp['percent'] >= 70 ? 'bg-warning' : 'bg-primary') }}" 
                                     style="width: {{ $emp['max'] == 999999 ? 5 : min($emp['percent'], 100) }}%"
                                     role="progressbar">
                                </div>
                            </div>
                        </div>
                        
                        {{-- Devices Usage --}}
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">
                                    <i class="bi bi-phone-fill me-1"></i>Devices
                                </small>
                                <small class="fw-semibold {{ $devWarning ? 'text-danger' : '' }}">
                                    {{ $dev['used'] }} / {{ $dev['max'] == 999999 ? '∞' : $dev['max'] }}
                                </small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $dev['percent'] >= 90 ? 'bg-danger' : ($dev['percent'] >= 70 ? 'bg-warning' : 'bg-info') }}" 
                                     style="width: {{ $dev['max'] == 999999 ? 5 : min($dev['percent'], 100) }}%"
                                     role="progressbar">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Card Footer with Quick Stats --}}
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <div class="d-flex justify-content-between text-muted small">
                            <span title="Total Employees">
                                <i class="bi bi-person-check"></i> {{ $emp['used'] }} active
                            </span>
                            <span title="Total Devices">
                                <i class="bi bi-phone"></i> {{ $dev['used'] }} connected
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ============================================================== --}}
    {{-- ORG ADMIN ONLY: Organization Usage Card (Their Org Only) --}}
    {{-- ============================================================== --}}
    @if(!$isSuperAdmin && $orgPlanUsage)
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>{{ __('messages.your_plan_usage') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="text-muted mb-2">Employees</h6>
                    @php $emp = $orgPlanUsage['employees'] ?? ['used' => 0, 'max' => 0, 'percent' => 0]; @endphp
                    <div class="d-flex align-items-center gap-3">
                        <div class="progress flex-grow-1" style="height: 30px;">
                            <div class="progress-bar {{ $emp['percent'] >= 90 ? 'bg-danger' : ($emp['percent'] >= 70 ? 'bg-warning' : 'bg-success') }}" 
                                 style="width: {{ min($emp['percent'], 100) }}%">
                                {{ $emp['percent'] }}%
                            </div>
                        </div>
                        <span class="fs-5 fw-bold">{{ $emp['used'] }}/{{ $emp['max'] == 999999 ? '∞' : $emp['max'] }}</span>
                    </div>
                    @if($emp['percent'] >= 90)
                    <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Approaching limit!</small>
                    @endif
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="text-muted mb-2">Devices</h6>
                    @php $dev = $orgPlanUsage['devices'] ?? ['used' => 0, 'max' => 0, 'percent' => 0]; @endphp
                    <div class="d-flex align-items-center gap-3">
                        <div class="progress flex-grow-1" style="height: 30px;">
                            <div class="progress-bar {{ $dev['percent'] >= 90 ? 'bg-danger' : ($dev['percent'] >= 70 ? 'bg-warning' : 'bg-success') }}" 
                                 style="width: {{ min($dev['percent'], 100) }}%">
                                {{ $dev['percent'] }}%
                            </div>
                        </div>
                        <span class="fs-5 fw-bold">{{ $dev['used'] }}/{{ $dev['max'] == 999999 ? '∞' : $dev['max'] }}</span>
                    </div>
                    @if($dev['percent'] >= 90)
                    <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Approaching limit!</small>
                    @endif
                </div>
            </div>
            @if(!empty($orgPlanUsage['plan_name']))
            <div class="text-center mt-3">
                <span class="badge bg-secondary fs-6">{{ $orgPlanUsage['plan_name'] }} Plan</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Period Info - Both Roles -->
    <div class="text-muted small text-end">
        <i class="bi bi-clock me-1"></i>
        Data as of {{ $period['today'] ?? date('Y-m-d') }} | 
        Month: {{ $period['month_start'] ?? '' }} to {{ $period['today'] ?? '' }}
    </div>
</div>
@endsection
