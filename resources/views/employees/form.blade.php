@extends('layouts.app')

@section('content')
<style>
    :root {
        --bg-primary: #ffffff;
        --bg-secondary: #f8f9fa;
        --text-primary: #212529;
        --border-color: #dee2e6;
        --input-bg: #ffffff;
        --btn-primary: #198754;
        --btn-text: #fff;
    }
    
    body {
        background-color: var(--bg-primary);
        color: var(--text-primary);
        font-family: system-ui, -apple-system, sans-serif;
        padding: 20px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        font-size: 14px;
    }
    
    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="date"],
    textarea,
    select {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        background: var(--input-bg);
        color: var(--text-primary);
        font-size: 14px;
        box-sizing: border-box;
    }
    
    .btn-submit {
        background: var(--btn-primary);
        color: var(--btn-text);
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
    }
    
    .error-msg {
        color: #dc3545;
        font-size: 12px;
        margin-top: 4px;
    }
    
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
</style>

<h2 style="margin-top:0; margin-bottom:20px;">{{ isset($employee) ? 'Edit Employee' : 'Add Employee' }}</h2>

@if(session('error'))
    <div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:4px; margin-bottom:15px;">
        {{ session('error') }}
    </div>
@endif

<form action="{{ isset($employee) ? route('employees.update', $employee['id']) : route('employees.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($employee))
        @method('PUT')
    @endif
    @if(request('popup'))
        <input type="hidden" name="popup" value="1">
    @endif

    {{-- Organization Field --}}
    <div class="form-group" style="margin-bottom:20px; padding-bottom:15px; border-bottom:1px solid var(--border-color);">
        <label>Organization *</label>
        @if(($isSuperAdmin ?? false) && count($organizations ?? []) > 0)
            {{-- Super Admin: Show searchable dropdown --}}
            <select name="organization_id" id="emp-org-selector" required style="width:100%;">
                <option value="">Select Organization...</option>
                @foreach($organizations as $org)
                    <option value="{{ $org['id'] }}" 
                        {{ old('organization_id', $employee['organization_id'] ?? $orgId ?? '') == $org['id'] ? 'selected' : '' }}>
                        {{ $org['name'] }}
                    </option>
                @endforeach
            </select>
        @else
            {{-- Org Admin: Show read-only field --}}
            <input type="hidden" name="organization_id" value="{{ $orgId ?? '' }}">
            <input type="text" value="{{ $organizationName ?? 'Current Organization' }}" readonly 
                   style="background:#eee; cursor:not-allowed;">
        @endif
    </div>

    <div class="grid-2">
        <div class="form-group">
            <label>Employee ID *</label>
            <input type="text" name="employee_id" value="{{ old('employee_id', $employee['employee_id'] ?? '') }}" required>
        </div>
        <div class="form-group">
            <label>Name *</label>
            <input type="text" name="name" value="{{ old('name', $employee['name'] ?? '') }}" required>
        </div>
    </div>

    <div class="grid-2">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $employee['email'] ?? '') }}">
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $employee['phone'] ?? '') }}">
        </div>
    </div>

    <div class="grid-2">
        <div class="form-group">
            <label>Department</label>
            <div class="dropdown-container" style="position: relative;">
                <input type="text" name="department" id="departmentInput" value="{{ old('department', $employee['department'] ?? '') }}" autocomplete="off" style="padding-right: 30px;" onclick="toggleDropdown('departmentDropdown')">
                <span class="dropdown-toggle-btn" onclick="event.stopPropagation(); toggleDropdown('departmentDropdown')" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;">▼</span>
                <div id="departmentDropdown" class="custom-dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ccc; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                    @foreach($departments ?? [] as $dept)
                        <div class="dropdown-item" onclick="selectItem('departmentInput', '{{ $dept }}', 'departmentDropdown')" style="padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; font-size: 13px; transition: background 0.2s;">{{ $dept }}</div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Designation</label>
            <div class="dropdown-container" style="position: relative;">
                <input type="text" name="designation" id="designationInput" value="{{ old('designation', $employee['designation'] ?? '') }}" autocomplete="off" style="padding-right: 30px;" onclick="toggleDropdown('designationDropdown')">
                <span class="dropdown-toggle-btn" onclick="event.stopPropagation(); toggleDropdown('designationDropdown')" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;">▼</span>
                <div id="designationDropdown" class="custom-dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ccc; border-radius: 4px; max-height: 200px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                    @foreach($designations ?? [] as $desig)
                        <div class="dropdown-item" onclick="selectItem('designationInput', '{{ $desig }}', 'designationDropdown')" style="padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f0f0f0; font-size: 13px; transition: background 0.2s;">{{ $desig }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div class="form-group">
            <label>Bank Account</label>
            <input type="text" name="bank_account" value="{{ old('bank_account', $employee['bank_account'] ?? '') }}">
        </div>
        <div class="form-group">
            <label>Branch</label>
            <input type="text" name="branch" value="{{ old('branch', $employee['branch'] ?? '') }}">
        </div>
    </div>

    <div class="grid-2">
        <div class="form-group">
            <label>Monthly Salary</label>
            <input type="number" step="0.01" name="monthly_salary" value="{{ old('monthly_salary', $employee['monthly_salary'] ?? 0) }}">
        </div>
        <div class="form-group">
            <label>Hire Date</label>
            <input type="date" name="hire_date" value="{{ old('hire_date', $employee['hire_date'] ?? '') }}">
        </div>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $employee['is_active'] ?? true) ? 'checked' : '' }}>
            Is Active
        </label>
    </div>
    
    <div class="form-group">
        <label>Date Inactive</label>
        <input type="date" name="date_inactive" value="{{ old('date_inactive', $employee['date_inactive'] ?? '') }}">
    </div>

    <div class="form-group">
        <label>Employee Image (Click image to adjust)</label>
        <div style="margin-bottom:5px; cursor:pointer;" id="previewContainer" title="Click to calculate position">
            @if(!empty($employee['face_image']))
                <img id="previewImage" src="data:image/jpeg;base64,{{ $employee['face_image'] }}" style="height:100px; width:100px; object-fit:cover; border-radius:4px; border:1px solid #ccc;">
            @else
                 <img id="previewImage" src="https://via.placeholder.com/100?text=No+Image" style="height:100px; width:100px; object-fit:cover; border-radius:4px; border:1px solid #ccc; display:none;">
            @endif
        </div>
        <input type="file" name="employee_image" id="inputImage" accept="image/*">
    </div>

    <div class="form-group">
        <label>Facial Template (Read Only)</label>
        <textarea name="facial_template" rows="3" readonly style="background:#eee;">{{ $employee['facial_template'] ?? '' }}</textarea>
    </div>

    <button type="submit" class="btn-submit">SAVE</button>
</form>

<!-- Cropper Modal -->
<div id="cropperModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:20px; border-radius:8px; width:90%; max-width:500px; max-height:90%; overflow:hidden; display:flex; flex-direction:column;">
        <h3 style="margin-top:0;">Adjust Image</h3>
        <div style="max-height:400px; overflow:hidden; margin-bottom:15px;">
            <img id="imageToCrop" style="max-width:100%;">
        </div>
        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" id="btnCancelCrop" style="padding:8px 16px; border:1px solid #ccc; background:#fff; border-radius:4px; cursor:pointer;">Cancel</button>
            <button type="button" id="btnCrop" style="padding:8px 16px; border:none; background:var(--btn-primary); color:#fff; border-radius:4px; cursor:pointer;">Set Image</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

@push('scripts')
<script>
    let cropper;
    const inputImage = document.getElementById('inputImage');
    const modal = document.getElementById('cropperModal');
    const image = document.getElementById('imageToCrop');
    const btnCrop = document.getElementById('btnCrop');
    const btnCancel = document.getElementById('btnCancelCrop');
    const previewImage = document.getElementById('previewImage');
    const previewContainer = document.getElementById('previewContainer');

    // Handle File Input Change
    inputImage.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const file = files[0];
            // Check if this is a scripted event (our own set files) to avoid double modal?
            // Actually, setting files via DataTransfer DOES NOT trigger 'change' event in most browsers.
            // So this handles Manual Selection only.
            
            const reader = new FileReader();
            reader.onload = function(evt) {
                openCropper(evt.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Handle Click on Preview
    previewContainer.addEventListener('click', function() {
        if (previewImage && previewImage.src && (previewImage.src.startsWith('data') || previewImage.src.startsWith('blob'))) {
             openCropper(previewImage.src);
        }
    });

    function openCropper(src) {
        image.src = src;
        modal.style.display = 'flex';
        if (cropper) {
            cropper.destroy();
        }
        cropper = new Cropper(image, {
            aspectRatio: 1, 
            viewMode: 1,
            minCropBoxWidth: 100,
            minCropBoxHeight: 100,
        });
    }

    btnCancel.addEventListener('click', function() {
        modal.style.display = 'none';
        // Do not clear. Just cancel editing.
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    });

    btnCrop.addEventListener('click', function() {
        if (cropper) {
            cropper.getCroppedCanvas({
                width: 300,
                height: 300,
            }).toBlob(function(blob) {
                // Update Preview
                const url = URL.createObjectURL(blob);
                previewImage.src = url;
                previewImage.style.display = 'block';

                // Assign to input
                const newFile = new File([blob], "profile_cropped.jpg", { type: "image/jpeg" });
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(newFile);
                inputImage.files = dataTransfer.files;
                
                modal.style.display = 'none';
            }, 'image/jpeg');
        }
    });
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Organization selector (for super admin)
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2 && $('#emp-org-selector').length) {
        $('#emp-org-selector').select2({
            placeholder: 'Search organization...',
            allowClear: false,
            width: '100%'
        });
    }
});

// Custom dropdown functions for Department and Designation
function toggleDropdown(dropdownId) {
    var dropdown = document.getElementById(dropdownId);
    var isHidden = dropdown.style.display === 'none';
    
    // Close all other dropdowns first
    document.querySelectorAll('.custom-dropdown').forEach(function(d) {
        d.style.display = 'none';
    });
    
    // Toggle this dropdown
    dropdown.style.display = isHidden ? 'block' : 'none';
}

function selectItem(inputId, value, dropdownId) {
    document.getElementById(inputId).value = value;
    document.getElementById(dropdownId).style.display = 'none';
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    // If click is outside the entire relative container, close dropdowns
    if (!e.target.closest('.dropdown-container')) {
        document.querySelectorAll('.custom-dropdown').forEach(function(d) {
            d.style.display = 'none';
        });
    }
});

// Add hover styles dynamically
document.addEventListener('mouseover', function(e) {
    if (e.target.classList.contains('dropdown-item')) {
        e.target.style.background = '#f5f5f5';
    }
});
document.addEventListener('mouseout', function(e) {
    if (e.target.classList.contains('dropdown-item')) {
        e.target.style.background = 'transparent';
    }
});
</script>
@endpush
@endsection
