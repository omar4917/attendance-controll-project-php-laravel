@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-clock-history me-2"></i>{{ __('messages.audit_logs') }}</h2>
            <p class="text-muted">{{ __('messages.audit_description') }}</p>
        </div>
    </div>

    @if($error)
    <div class="alert alert-danger">{{ $error }}</div>
    @endif

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-funnel me-2 text-primary"></i>{{ __('messages.filter') }}</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('audit.index') }}">
                <div class="row g-3">
                    @if($role === 'super_admin' && count($organizations) > 0)
                    <div class="col-md-3">
                        <label class="form-label">{{ __('messages.organization') }}</label>
                        <select name="organization_id" class="form-select">
                            <option value="">All Organizations</option>
                            @foreach($organizations as $org)
                            <option value="{{ $org['id'] }}" {{ ($filters['organization_id'] ?? '') == $org['id'] ? 'selected' : '' }}>
                                {{ $org['name'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    
                    <div class="{{ $role === 'super_admin' ? 'col-md-3' : 'col-md-4' }}">
                        <label class="form-label">{{ __('messages.user_email') }}</label>
                        <input type="text" name="user_email" class="form-control" 
                               value="{{ $filters['user_email'] ?? '' }}" placeholder="Search by email...">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">{{ __('messages.action') }}</label>
                        <select name="action" class="form-select">
                            <option value="">{{ __('messages.all_actions') }}</option>
                            @foreach($actionTypes as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['action'] ?? '') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">{{ __('messages.resource') }}</label>
                        <select name="resource_type" class="form-select">
                            <option value="">{{ __('messages.all_resources') }}</option>
                            @foreach($resourceTypes as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['resource_type'] ?? '') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">{{ __('messages.start_date') }}</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ $filters['start_date'] ?? '' }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">{{ __('messages.end_date') }}</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="{{ $filters['end_date'] ?? '' }}">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> {{ __('messages.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>{{ __('messages.activity_log') }}
                <span class="badge bg-secondary ms-2">{{ $total }} {{ __('messages.entries') }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">{{ __('messages.timestamp') }}</th>
                            <th>{{ __('messages.user') }}</th>
                            <th>{{ __('messages.actions') }}</th>
                            <th>{{ __('messages.resource') }}</th>
                            @if($role === 'super_admin')
                            <th>{{ __('messages.organization') }}</th>
                            @endif
                            <th>IP Address</th>
                            <th class="text-center" style="width: 100px;">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td class="text-nowrap">
                                <small>{{ \Carbon\Carbon::parse($log['timestamp'])->setTimezone('Asia/Dhaka')->format('M d, Y h:i:s A') }}</small>
                            </td>
                            <td>
                                <strong>{{ $log['user_name'] ?: $log['user_email'] }}</strong>
                                @if($log['user_name'])
                                <br><small class="text-muted">{{ $log['user_email'] }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $actionColors = [
                                        'login' => 'success',
                                        'logout' => 'secondary',
                                        'create' => 'primary',
                                        'update' => 'info',
                                        'delete' => 'danger',
                                        'view' => 'light',
                                        'export' => 'warning',
                                        'import' => 'warning',
                                        'unauthorized_attempt' => 'danger',
                                    ];
                                    $color = $actionColors[$log['action']] ?? 'secondary';
                                    
                                    // Get intended action for unauthorized attempts
                                    $intendedAction = null;
                                    if ($log['action'] === 'unauthorized_attempt') {
                                        $intendedAction = $log['details']['intended_action'] ?? null;
                                        if (!$intendedAction && !empty($log['details']['method'])) {
                                            $intendedAction = $log['details']['method'];
                                        }
                                    }
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ str_replace('_', ' ', ucfirst($log['action'])) }}</span>
                                @if($intendedAction)
                                <br><small class="text-danger"><strong>Tried: {{ ucfirst(str_replace('_', ' ', $intendedAction)) }}</strong></small>
                                @endif
                            </td>
                            <td>
                                <span class="text-capitalize">{{ str_replace('_', ' ', $log['resource_type']) }}</span>
                                @if($log['action'] === 'export' && !empty($log['details']['filename']))
                                    <br><small class="text-muted"><i class="bi bi-file-text me-1"></i>{{ $log['details']['filename'] }}</small>
                                @elseif($log['resource_name'])
                                    <br><small class="text-muted">{{ $log['resource_name'] }}</small>
                                @elseif($log['resource_id'])
                                    <br><small class="text-muted">ID: {{ $log['resource_id'] }}</small>
                                @endif
                            </td>
                            @if($role === 'super_admin')
                            <td>
                                <small>{{ $log['organization_name'] ?? 'System' }}</small>
                            </td>
                            @endif
                            <td>
                                <small class="text-muted">{{ $log['ip_address'] ?? '-' }}</small>
                            </td>
                             <td class="text-center">
                                @if(!empty($log['details']))
                                    <button class="btn btn-sm bg-white border shadow-sm px-2 py-1" 
                                            data-bs-toggle="modal" data-bs-target="#detailsModal{{ $log['id'] }}"
                                            style="border-color: #dee2e6 !important; border-radius: 6px; width: 36px; height: 36px;"
                                            title="View Details">
                                        <i class="bi bi-eye text-primary" style="font-size: 1.1rem;"></i>
                                    </button>
                                    
                                    <!-- Details Modal -->
                                    <div class="modal fade text-start" id="detailsModal{{ $log['id'] }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered {{ isset($log['details']['changes']) ? 'modal-lg' : '' }}">
                                            <div class="modal-content border-0 shadow-lg">
                                                <div class="modal-header bg-light border-bottom-0">
                                                    <h6 class="modal-title fw-bold"><i class="bi bi-info-circle me-2"></i>{{ __('messages.action_details') }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-0">
                                                    {{-- Comparison Table for Updates --}}
                                                    @php
                                                        $changes = $log['details']['changes'] ?? null;
                                                        if (is_string($changes)) $changes = json_decode($changes, true);
                                                    @endphp

                                                    @if(is_array($changes) && count($changes) > 0)
                                                        <div class="p-4 bg-white border-bottom shadow-sm">
                                                            <div class="d-flex align-items-center mb-3">
                                                                <div class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-circle me-3">
                                                                    <i class="bi bi-arrow-left-right" style="font-size: 1.2rem;"></i>
                                                                </div>
                                                                <div>
                                                                    <h6 class="fw-bold mb-0 text-dark">Data Comparison</h6>
                                                                    <small class="text-muted">Review internal changes to this record</small>
                                                                </div>
                                                            </div>
                                                            <div class="table-responsive rounded border shadow-sm">
                                                                <table class="table table-hover mb-0" style="font-size: 0.9rem;">
                                                                    <thead class="bg-light">
                                                                        <tr class="small text-uppercase text-muted">
                                                                            <th class="ps-3 py-3" style="width: 30%;">Field</th>
                                                                            <th class="py-3">Previous State</th>
                                                                            <th class="py-3">Current State</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($changes as $field => $diff)
                                                                            <tr class="align-middle">
                                                                                <td class="ps-3 text-capitalize fw-bold text-dark">{{ str_replace('_', ' ', $field) }}</td>
                                                                                <td class="text-secondary">
                                                                                    <div class="d-inline-flex align-items-center">
                                                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 px-2 py-1">
                                                                                            @php
                                                                                                $oldVal = $diff['old'];
                                                                                                if (is_string($oldVal) && preg_match('/^\d{4}-\d{2}-\d{2}/', $oldVal)) {
                                                                                                    try { $oldVal = \Carbon\Carbon::parse($oldVal, 'UTC')->timezone('Asia/Dhaka')->format('M j, Y h:i A'); } catch(\Exception $e){}
                                                                                                }
                                                                                            @endphp
                                                                                            {{ is_bool($diff['old']) ? ($diff['old'] ? 'True' : 'False') : ($oldVal ?? 'Empty') }}
                                                                                        </span>
                                                                                    </div>
                                                                                </td>
                                                                                <td class="text-dark fw-bold">
                                                                                    <div class="d-inline-flex align-items-center">
                                                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 px-2 py-1">
                                                                                            @php
                                                                                                $newVal = $diff['new'];
                                                                                                if (is_string($newVal) && preg_match('/^\d{4}-\d{2}-\d{2}/', $newVal)) {
                                                                                                    try { $newVal = \Carbon\Carbon::parse($newVal, 'UTC')->timezone('Asia/Dhaka')->format('M j, Y h:i A'); } catch(\Exception $e){}
                                                                                                }
                                                                                            @endphp
                                                                                            {{ is_bool($diff['new']) ? ($diff['new'] ? 'True' : 'False') : ($newVal ?? 'Empty') }}
                                                                                        </span>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    @elseif($log['action'] === 'update')
                                                        <div class="px-4 py-3 bg-light border-bottom text-muted small">
                                                            <i class="bi bi-info-circle me-1"></i>No functional data changes were detected in this update.
                                                        </div>
                                                    @endif

                                                    {{-- Standard Details View --}}
                                                    <div class="p-4">
                                                        <h6 class="fw-bold mb-3 small text-uppercase text-muted">Technical Overview</h6>
                                                        <div class="card border shadow-none bg-light bg-opacity-10">
                                                            <table class="table table-hover mb-0">
                                                                <tbody>
                                                                    @foreach($log['details'] as $key => $value)
                                                                        @if($key === 'changes') @continue @endif
                                                                        <tr class="align-middle">
                                                                            <th class="bg-light ps-4 py-3" style="width: 35%; border-left: 3px solid #0d6efd;">
                                                                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">{{ str_replace('_', ' ', $key) }}</small>
                                                                            </th>
                                                                            <td class="ps-4 py-3">
                                                                                @if(is_array($value))
                                                                                    <pre class="mb-0 bg-dark text-white p-3 rounded shadow-sm" style="font-size: 0.8rem; overflow-x: auto;"><code>{{ json_encode($value, JSON_PRETTY_PRINT) }}</code></pre>
                                                                                @elseif(is_bool($value))
                                                                                    <span class="fw-bold text-{{ $value ? 'success' : 'danger' }}">{{ $value ? 'True' : 'False' }}</span>
                                                                                @else
                                                                                    @php
                                                                                        $displayVal = $value;
                                                                                        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                                                                                            try { $displayVal = \Carbon\Carbon::parse($value, 'UTC')->timezone('Asia/Dhaka')->format('M j, Y h:i A'); } catch(\Exception $e){}
                                                                                        }
                                                                                    @endphp
                                                                                    <span class="fw-medium text-dark">{{ $displayVal }}</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top-0 bg-light py-3">
                                                    <button type="button" class="btn btn-primary px-4 shadow-sm" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                                </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $role === 'super_admin' ? '7' : '6' }}" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">{{ __('messages.no_logs_found') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($totalPages > 1)
        <div class="card-footer">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    @if($page > 1)
                    <li class="page-item">
                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}">Previous</a>
                    </li>
                    @endif
                    
                    @for($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++)
                    <li class="page-item {{ $i == $page ? 'active' : '' }}">
                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                    </li>
                    @endfor
                    
                    @if($page < $totalPages)
                    <li class="page-item">
                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}">Next</a>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>
@endsection
