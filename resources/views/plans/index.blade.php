@extends('layouts.app')

@section('title', 'Subscription Plans')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col">
        <h2><i class="bi bi-award me-2"></i>Subscription Plans</h2>
        <p class="text-muted">Manage pricing and limits for organizations</p>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary" onclick="showPlanModal()">
            <i class="bi bi-plus-lg me-1"></i> Add Plan
        </button>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-4">
    @forelse($plans as $plan)
    <div class="col-md-4">
        <div class="card h-100 shadow-sm {{ $plan['is_active'] ? '' : 'border-warning bg-light' }}">
            <div class="card-header bg-transparent border-0 pt-4 pb-0 d-flex justify-content-between">
                @if($plan['is_default'])
                <span class="badge bg-success">Default</span>
                @else
                <span></span>
                @endif
                <div class="dropdown">
                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick='editPlan(@json($plan))'><i class="bi bi-pencil me-2"></i>Edit</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('plans.destroy', $plan['id']) }}" method="POST" onsubmit="return confirm('Delete this plan?');">
                                @csrf
                                @method('DELETE')
                                <button class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body text-center">
                <h3 class="card-title fw-bold mb-0 py-2">{{ $plan['name'] }}</h3>
                <div class="display-6 fw-bold text-primary my-3">
                    ${{ number_format($plan['price_monthly'], 0) }}
                    <span class="fs-6 text-muted fw-normal">/mo</span>
                </div>
                <p class="text-muted small mb-4">{{ $plan['description'] ?: 'No description' }}</p>
                
                <div class="row text-start g-2 border-top pt-3 mt-3">
                    <div class="col-6">
                        <small class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;">Employees</small>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-people-fill me-2 text-secondary"></i>
                            <span class="fw-semibold">{{ $plan['max_employees'] == 999999 ? 'Unlimited' : $plan['max_employees'] }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <small class="text-uppercase text-muted fw-bold" style="font-size:0.7rem;">Devices</small>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-tablet-fill me-2 text-secondary"></i>
                            <span class="fw-semibold">{{ $plan['max_devices'] == 999999 ? 'Unlimited' : $plan['max_devices'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pb-3">
                @if(!$plan['is_active'])
                <div class="text-center text-warning small"><i class="bi bi-slash-circle me-1"></i>Inactive</div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <i class="bi bi-award display-1 text-muted opacity-25"></i>
        <h4 class="mt-3 text-muted">No Plans Found</h4>
        <p class="mb-4">Create a subscription plan to get started.</p>
        <button class="btn btn-outline-primary" onclick="showPlanModal()">Create First Plan</button>
    </div>
    @endforelse
</div>

<!-- Plan Modal -->
<div class="modal fade" id="planModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('plans.store') }}" method="POST" id="planForm">
                @csrf
                <div id="method-spoof"></div>
                <input type="hidden" name="id" id="plan_id">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Subscription Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Plan Name *</label>
                        <input type="text" name="name" id="name" class="form-control" required placeholder="e.g. Basic, Pro, Enterprise">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Max Employees *</label>
                            <input type="number" name="max_employees" id="max_employees" class="form-control" required value="10">
                            <div class="form-text small">Enter 999999 for unlimited</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Max Devices *</label>
                            <input type="number" name="max_devices" id="max_devices" class="form-control" required value="2">
                            <div class="form-text small">Enter 999999 for unlimited</div>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Monthly Price ($)</label>
                            <input type="number" step="0.01" name="price_monthly" id="price_monthly" class="form-control" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Yearly Price ($)</label>
                            <input type="number" step="0.01" name="price_yearly" id="price_yearly" class="form-control" value="0">
                        </div>
                    </div>
                    
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active Plan</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1">
                        <label class="form-check-label" for="is_default">Set as Default Plan</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showPlanModal() {
        document.getElementById('planForm').reset();
        document.getElementById('planForm').action = "{{ route('plans.store') }}";
        document.getElementById('method-spoof').innerHTML = '';
        document.getElementById('modalTitle').innerText = 'Add Subscription Plan';
        document.getElementById('plan_id').value = '';
        document.getElementById('is_active').checked = true;
        
        var modal = new bootstrap.Modal(document.getElementById('planModal'));
        modal.show();
    }
    
    function editPlan(plan) {
        document.getElementById('planForm').action = "/plans/" + plan.id;
        document.getElementById('method-spoof').innerHTML = '@method("PUT")';
        document.getElementById('modalTitle').innerText = 'Edit Plan: ' + plan.name;
        
        document.getElementById('plan_id').value = plan.id;
        document.getElementById('name').value = plan.name;
        document.getElementById('description').value = plan.description || '';
        document.getElementById('max_employees').value = plan.max_employees;
        document.getElementById('max_devices').value = plan.max_devices;
        document.getElementById('price_monthly').value = plan.price_monthly;
        document.getElementById('price_yearly').value = plan.price_yearly;
        document.getElementById('is_active').checked = plan.is_active;
        document.getElementById('is_default').checked = plan.is_default;
        
        var modal = new bootstrap.Modal(document.getElementById('planModal'));
        modal.show();
    }
</script>
@endsection
