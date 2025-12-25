@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-clock-history me-2"></i>Audit Logs</h2>
            <p class="text-muted">Track user activities across the system</p>
        </div>
    </div>

    @if($error)
    <div class="alert alert-danger">{{ $error }}</div>
    @endif

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('audit.index') }}">
                <div class="row g-3">
                    @if($role === 'super_admin' && count($organizations) > 0)
                    <div class="col-md-3">
                        <label class="form-label">Organization</label>
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
                        <label class="form-label">User Email</label>
                        <input type="text" name="user_email" class="form-control" 
                               value="{{ $filters['user_email'] ?? '' }}" placeholder="Search by email...">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Action</label>
                        <select name="action" class="form-select">
                            <option value="">All Actions</option>
                            @foreach($actionTypes as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['action'] ?? '') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Resource</label>
                        <select name="resource_type" class="form-select">
                            <option value="">All Resources</option>
                            @foreach($resourceTypes as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['resource_type'] ?? '') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ $filters['start_date'] ?? '' }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="{{ $filters['end_date'] ?? '' }}">
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Filter
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
                <i class="bi bi-list-ul me-2"></i>Activity Log
                <span class="badge bg-secondary ms-2">{{ $total }} entries</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Resource</th>
                            @if($role === 'super_admin')
                            <th>Organization</th>
                            @endif
                            <th>IP Address</th>
                            <th>Details</th>
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
                            <td>
                                @if(!empty($log['details']))
                                <button class="btn btn-sm btn-outline-secondary" 
                                        data-bs-toggle="modal" data-bs-target="#detailsModal{{ $log['id'] }}">
                                    <i class="bi bi-eye"></i>
                                </button>
                                
                                <!-- Details Modal -->
                                <div class="modal fade" id="detailsModal{{ $log['id'] }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Action Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <pre class="bg-light p-3 rounded">{{ json_encode($log['details'], JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $role === 'super_admin' ? '7' : '6' }}" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p class="mb-0">No audit logs found</p>
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
