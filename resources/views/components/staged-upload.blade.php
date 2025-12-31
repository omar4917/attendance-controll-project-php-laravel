{{-- Staged Upload Component --}}
<style>
    .staged-upload-zone {
        border: 2px dashed #6c757d;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: var(--bg-card, #fff);
    }
    .staged-upload-zone:hover, .staged-upload-zone.drag-over {
        border-color: #28a745;
        background: rgba(40, 167, 69, 0.05);
    }
    .staged-upload-zone .upload-icon {
        font-size: 48px;
        color: #6c757d;
        margin-bottom: 10px;
    }
    .staged-upload-zone.drag-over .upload-icon {
        color: #28a745;
    }
    
    .upload-progress-container {
        display: none;
        margin-top: 15px;
        padding: 15px;
        background: var(--bg-card, #f8f9fa);
        border-radius: 8px;
        border: 1px solid var(--border-color, #dee2e6);
    }
    .upload-progress-bar-wrapper {
        height: 24px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 8px;
    }
    .upload-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #28a745, #34ce57);
        transition: width 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 12px;
    }
    .upload-progress-text {
        font-size: 14px;
        color: var(--text-secondary, #6c757d);
    }
    
    .export-progress-container {
        display: none;
        padding: 10px 15px;
        background: #e7f5ff;
        border-radius: 6px;
        margin-top: 10px;
    }
    .export-progress-bar-wrapper {
        height: 8px;
        background: #c5dff8;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 5px;
    }
    .export-progress-bar {
        height: 100%;
        background: #0d6efd;
        transition: width 0.3s ease;
    }
    .export-progress-text {
        font-size: 12px;
        color: #0d6efd;
    }
    .btn-download-ready {
        display: none;
        background: #28a745;
        color: #fff;
        border: none;
        padding: 6px 16px;
        border-radius: 4px;
        margin-top: 8px;
        cursor: pointer;
    }
    .btn-download-ready:hover {
        background: #218838;
    }
</style>

{{-- Upload Zone --}}
<div class="staged-upload-zone" id="{{ $uploadZoneId ?? 'uploadZone' }}">
    <i class="bi bi-cloud-upload upload-icon"></i>
    <p style="margin:0;color:var(--text-secondary);">
        <strong>{{ $uploadLabel ?? 'Drag & drop file here' }}</strong><br>
        <small>or click to select</small>
    </p>
    <input type="file" id="{{ $fileInputId ?? 'fileInput' }}" style="display:none;" accept="{{ $acceptTypes ?? '.zip,.json' }}">
</div>

{{-- Progress Container --}}
<div class="upload-progress-container" id="{{ $progressContainerId ?? 'uploadProgressContainer' }}">
    <div class="upload-progress-bar-wrapper">
        <div class="upload-progress-bar" id="{{ $progressBarId ?? 'uploadProgressBar' }}" style="width:0%">0%</div>
    </div>
    <div class="upload-progress-text" id="{{ $progressTextId ?? 'uploadProgressText' }}">Preparing upload...</div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="{{ $previewModalId ?? 'uploadPreviewModal' }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:var(--header-bg);color:#fff;">
                <h5 class="modal-title"><i class="bi bi-file-earmark-check"></i> {{ $previewTitle ?? 'Upload Preview' }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                {{-- Preview content inserted by JS --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="{{ $cancelBtnId ?? 'uploadCancelBtn' }}" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="{{ $confirmBtnId ?? 'uploadConfirmBtn' }}">
                    <i class="bi bi-check-circle"></i> Confirm Import
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Export Progress --}}
@if($showExport ?? false)
<div class="export-progress-container" id="{{ $exportProgressContainerId ?? 'exportProgressContainer' }}">
    <div class="export-progress-bar-wrapper">
        <div class="export-progress-bar" id="{{ $exportProgressBarId ?? 'exportProgressBar' }}" style="width:0%"></div>
    </div>
    <div class="export-progress-text" id="{{ $exportProgressTextId ?? 'exportProgressText' }}">Preparing export...</div>
    <a href="#" class="btn-download-ready" id="{{ $downloadBtnId ?? 'exportDownloadBtn' }}">
        <i class="bi bi-download"></i> Download
    </a>
</div>
@endif
