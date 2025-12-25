@extends('layouts.app')

@section('title', 'Manage Users')

@push('head')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 0;
        margin-bottom: 20px;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--text-main);
    }

    .users-table {
        background: var(--bg-card, #fff);
        border: 1px solid var(--border-color, #badbcc);
        border-radius: 8px;
        overflow: hidden;
    }

    .users-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .users-table th {
        background: var(--accent-color, #198754);
        color: #fff;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
    }
    .th-sortable {
        color: #fff;
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: opacity 0.2s;
    }
    .th-sortable:hover {
        opacity: 0.8;
    }
    .th-sortable.active {
        font-weight: 700;
    }

    .users-table td {
        padding: 12px 15px;
        border-bottom: 1px solid var(--border-color, #badbcc);
        color: var(--text-main);
    }

    .users-table tr:hover {
        background: rgba(25, 135, 84, 0.05);
    }

    .role-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .role-main-admin { background: #198754; color: #fff; }
    .role-admin { background: #0d6efd; color: #fff; }
    .role-viewer { background: #6c757d; color: #fff; }

    .btn-add {
        background: var(--accent-color, #198754);
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
    }

    .btn-add:hover { background: var(--accent-hover, #157347); }

    .btn-delete {
        background: #dc3545;
        color: #fff;
        border: none;
        padding: 5px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.8rem;
    }
    .btn-delete:hover { background: #bb2d3b; }

    .btn-edit {
        background: #0d6efd;
        color: #fff;
        border: none;
        padding: 5px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.8rem;
        margin-right: 5px;
    }
    .btn-edit:hover { background: #0b5ed7; }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active { display: flex; }

    .modal-box {
        background: var(--bg-card, #fff);
        padding: 25px;
        border-radius: 8px;
        width: 100%;
        max-width: 450px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    .modal-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 20px;
        color: var(--text-main);
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--text-main);
    }

    .form-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border-color, #badbcc);
        border-radius: 6px;
        font-size: 0.95rem;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--accent-color, #198754);
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn-cancel {
        background: #6c757d;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .super-admin-badge {
        background: #6c757d;
        color: #fff;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        margin-left: 5px;
    }

    .main-admin-badge {
        background: #ffc107;
        color: #000;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        margin-left: 5px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <i class="bi bi-people me-2"></i>
        Manage Users
    </h1>
    @if($canManage)
    <button class="btn-add" onclick="openModal()">
        <i class="bi bi-plus-circle me-1"></i> Add User
    </button>
    @endif
</div>

@if(session('success'))
<div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert-error">{{ session('error') }}</div>
@endif

<div class="users-table">
    @php
        $currentSort = request('sort', 'username');
        $currentDir = request('dir', 'asc');
        $toggleDir = $currentDir === 'asc' ? 'desc' : 'asc';
        $sortParams = request()->except(['sort', 'dir']);
    @endphp
    <table>
        <thead>
            <tr>
                <th>
                    <a href="{{ route('org-users.index', array_merge($sortParams, ['sort' => 'username', 'dir' => $currentSort === 'username' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'username' ? 'active' : '' }}">
                        Username {!! $currentSort === 'username' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th>
                    <a href="{{ route('org-users.index', array_merge($sortParams, ['sort' => 'email', 'dir' => $currentSort === 'email' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'email' ? 'active' : '' }}">
                        Email {!! $currentSort === 'email' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th>
                    <a href="{{ route('org-users.index', array_merge($sortParams, ['sort' => 'organization_name', 'dir' => $currentSort === 'organization_name' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'organization_name' ? 'active' : '' }}">
                        Organization {!! $currentSort === 'organization_name' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th>
                    <a href="{{ route('org-users.index', array_merge($sortParams, ['sort' => 'first_name', 'dir' => $currentSort === 'first_name' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'first_name' ? 'active' : '' }}">
                        Name {!! $currentSort === 'first_name' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th>
                    <a href="{{ route('org-users.index', array_merge($sortParams, ['sort' => 'role', 'dir' => $currentSort === 'role' ? $toggleDir : 'asc'])) }}" class="th-sortable {{ $currentSort === 'role' ? 'active' : '' }}">
                        Role {!! $currentSort === 'role' ? ($currentDir === 'asc' ? '▲' : '▼') : '' !!}
                    </a>
                </th>
                <th>Created By</th>
                @if($canManage)<th>Actions</th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($orgUsers as $user)
            @php
                $currentUserId = session('admin_id');
                $currentUserRole = session('user_role');
                $isSuperUser = session('is_superuser', false);
                
                // CRITICAL: Compare with Django User ID (user_id) not the record ID
                $isSelf = ($currentUserId == $user['user_id']);
                
                // Determine if deletion is allowed
                $canDelete = false;
                if (!$isSelf && $canManage) {
                    if ($isSuperUser || $currentUserRole === 'super_admin') {
                        // Super Admin can delete anyone except themselves
                        $canDelete = true;
                    } elseif ($currentUserRole === 'org_main_admin') {
                        // Main Admin can delete Org Admins/Viewers, but NOT Super Admins or other Main Admins
                        $canDelete = !in_array($user['role'], ['super_admin', 'org_main_admin']);
                    } elseif ($currentUserRole === 'org_admin') {
                        // Org Admin cannot delete superiors
                        $canDelete = !in_array($user['role'], ['super_admin', 'org_main_admin', 'org_admin']);
                    }
                }
            @endphp

            <tr>
                <td>
                    {{ $user['username'] }}
                    @if($user['role'] === 'super_admin')
                    <span class="super-admin-badge">SUPER</span>
                    @elseif($user['role'] === 'org_main_admin')
                    <span class="main-admin-badge">MAIN</span>
                    @endif
                </td>
                <td>{{ $user['email'] ?: '-' }}</td>
                <td>
                    <span style="font-size: 0.85rem; font-weight: 500; color: #198754;">
                        {{ $user['organization_name'] }}
                    </span>
                </td>
                <td>{{ trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: '-' }}</td>
                <td>
                    @php
                        $roleClass = match($user['role']) {
                            'super_admin' => 'role-viewer',
                            'org_main_admin' => 'role-main-admin',
                            'org_admin' => 'role-admin',
                            default => 'role-viewer'
                        };
                    @endphp
                    <span class="role-badge {{ $roleClass }}">{{ $user['role_display'] }}</span>
                </td>
                <td>{{ $user['created_by'] ?? '-' }}</td>
                @if($canManage)
                <td>
                    <button class="btn-edit" onclick='openEditModal(@json($user))' title="Edit">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    @if($canDelete)
                    <form method="POST" action="{{ route('org-users.destroy', $user['id']) }}" style="display:inline;" onsubmit="return confirm('Delete this user?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-delete">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                    @else
                    <span style="color:#6c757d; font-size:0.8rem;"><i class="bi bi-shield-check"></i> Protected</span>
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ $canManage ? 6 : 5 }}" style="text-align:center; padding:30px; color:#666;">
                    No users found in this organization.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal-box">
        <h3 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New User</h3>
        <form method="POST" action="{{ route('org-users.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Username *</label>
                <input type="text" name="username" class="form-input" required placeholder="e.g., john.doe">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" placeholder="e.g., john@example.com">
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-input" required minlength="6">
            </div>
            <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-input">
            </div>

            @if(session('user_role') === 'super_admin' && !$orgId)
            <div class="form-group">
                <label class="form-label">Organization *</label>
                <select name="organization_id" class="form-input" required>
                    <option value="">Select Organization</option>
                    @foreach($organizations as $org)
                    <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="form-group">
                <label class="form-label">Role *</label>
                <select name="role" class="form-input">
                    <option value="org_admin">Organization Admin (Full Access)</option>
                    <option value="org_viewer">Organization Viewer (Read Only)</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn-add">Create User</button>
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal-box">
        <h3 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit User</h3>
        <form id="editUserForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Username *</label>
                <input type="text" name="username" id="edit_username" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="edit_email" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-input" minlength="6">
            </div>
            <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" id="edit_first_name" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" id="edit_last_name" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Role *</label>
                <select name="role" id="edit_role" class="form-input">
                    @if(session('user_role') === 'super_admin')
                    <option value="super_admin">Super Admin</option>
                    @endif
                    <option value="org_main_admin">Organization Main Admin</option>
                    <option value="org_admin">Organization Admin</option>
                    <option value="org_viewer">Organization Viewer</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn-add">Update User</button>
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('addUserModal').classList.add('active');
}
function closeModal() {
    document.getElementById('addUserModal').classList.remove('active');
}

function openEditModal(user) {
    const form = document.getElementById('editUserForm');
    form.action = `/org-users/${user.id}`;
    
    document.getElementById('edit_username').value = user.username || '';
    document.getElementById('edit_email').value = user.email || '';
    document.getElementById('edit_first_name').value = user.first_name || '';
    document.getElementById('edit_last_name').value = user.last_name || '';
    document.getElementById('edit_role').value = user.role || 'org_viewer';
    
    document.getElementById('editUserModal').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editUserModal').classList.remove('active');
}

// Close on outside click
window.addEventListener('click', function(e) {
    if (e.target.id === 'addUserModal') closeModal();
    if (e.target.id === 'editUserModal') closeEditModal();
});
</script>
@endsection
