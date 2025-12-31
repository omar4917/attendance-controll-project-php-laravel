/**
 * Staged Upload System - Google Drive Style
 * Supports progress tracking, chunked uploads, and confirmation flow
 */

class StagedUpload {
    constructor(options) {
        this.dropZone = document.getElementById(options.dropZoneId);
        this.fileInput = document.getElementById(options.fileInputId);
        this.progressContainer = document.getElementById(options.progressContainerId);
        this.progressBar = document.getElementById(options.progressBarId);
        this.progressText = document.getElementById(options.progressTextId);
        this.previewModal = document.getElementById(options.previewModalId);
        this.confirmBtn = document.getElementById(options.confirmBtnId);
        this.cancelBtn = document.getElementById(options.cancelBtnId);
        
        this.uploadUrl = options.uploadUrl;
        this.confirmUrl = options.confirmUrl;
        this.csrfToken = options.csrfToken;
        this.onSuccess = options.onSuccess || (() => {});
        this.onError = options.onError || ((err) => alert(err));
        
        this.currentFile = null;
        this.sessionId = null;
        this.xhr = null;
        
        this.init();
    }
    
    init() {
        // File input change
        if (this.fileInput) {
            this.fileInput.addEventListener('change', (e) => this.handleFileSelect(e.target.files[0]));
        }
        
        // Drag & drop
        if (this.dropZone) {
            this.dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                this.dropZone.classList.add('drag-over');
            });
            this.dropZone.addEventListener('dragleave', () => {
                this.dropZone.classList.remove('drag-over');
            });
            this.dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                this.dropZone.classList.remove('drag-over');
                if (e.dataTransfer.files.length) {
                    this.handleFileSelect(e.dataTransfer.files[0]);
                }
            });
        }
        
        // Confirm/Cancel buttons
        if (this.confirmBtn) {
            this.confirmBtn.addEventListener('click', () => this.confirmImport());
        }
        if (this.cancelBtn) {
            this.cancelBtn.addEventListener('click', () => this.cancelUpload());
        }
    }
    
    handleFileSelect(file) {
        if (!file) return;
        
        this.currentFile = file;
        this.showProgress();
        this.uploadFile(file);
    }
    
    showProgress() {
        if (this.progressContainer) {
            this.progressContainer.style.display = 'block';
        }
        this.updateProgress(0, 'Starting upload...');
    }
    
    hideProgress() {
        if (this.progressContainer) {
            this.progressContainer.style.display = 'none';
        }
    }
    
    updateProgress(percent, text) {
        if (this.progressBar) {
            this.progressBar.style.width = percent + '%';
            this.progressBar.textContent = percent + '%';
        }
        if (this.progressText) {
            this.progressText.textContent = text || `Uploading: ${percent}%`;
        }
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    uploadFile(file) {
        this.xhr = new XMLHttpRequest();
        const formData = new FormData();
        formData.append('file', file);
        formData.append('filename', file.name);
        formData.append('filesize', file.size);
        
        // Progress tracking
        this.xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                const loaded = this.formatFileSize(e.loaded);
                const total = this.formatFileSize(e.total);
                this.updateProgress(percent, `Uploading: ${loaded} / ${total} (${percent}%)`);
            }
        });
        
        // Complete
        this.xhr.addEventListener('load', () => {
            if (this.xhr.status === 200) {
                try {
                    const response = JSON.parse(this.xhr.responseText);
                    this.sessionId = response.session_id;
                    this.updateProgress(100, 'Upload complete! Processing...');
                    this.showPreview(response);
                } catch (e) {
                    this.onError('Failed to parse response');
                }
            } else {
                this.onError(`Upload failed: ${this.xhr.statusText}`);
                this.hideProgress();
            }
        });
        
        // Error
        this.xhr.addEventListener('error', () => {
            this.onError('Upload failed - network error');
            this.hideProgress();
        });
        
        // Abort
        this.xhr.addEventListener('abort', () => {
            this.updateProgress(0, 'Upload cancelled');
            this.hideProgress();
        });
        
        this.xhr.open('POST', this.uploadUrl, true);
        this.xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
        this.xhr.send(formData);
    }
    
    showPreview(response) {
        // Update preview modal content
        const previewContent = document.getElementById('previewContent');
        if (previewContent) {
            previewContent.innerHTML = `
                <div class="preview-info">
                    <p><strong>File:</strong> ${response.filename}</p>
                    <p><strong>Size:</strong> ${this.formatFileSize(response.filesize)}</p>
                    <p><strong>Records found:</strong> ${response.record_count || 'Processing...'}</p>
                    ${response.preview_html || ''}
                </div>
            `;
        }
        
        // Show modal
        if (this.previewModal) {
            const modal = new bootstrap.Modal(this.previewModal);
            modal.show();
        }
    }
    
    confirmImport() {
        if (!this.sessionId) {
            this.onError('No upload session found');
            return;
        }
        
        this.updateProgress(100, 'Importing data...');
        
        fetch(this.confirmUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({ session_id: this.sessionId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.updateProgress(100, 'Import complete!');
                this.onSuccess(data);
                // Close modal
                if (this.previewModal) {
                    bootstrap.Modal.getInstance(this.previewModal)?.hide();
                }
                // Reload page after short delay
                setTimeout(() => location.reload(), 1500);
            } else {
                this.onError(data.error || 'Import failed');
            }
        })
        .catch(err => {
            this.onError('Import failed: ' + err.message);
        });
    }
    
    cancelUpload() {
        if (this.xhr) {
            this.xhr.abort();
        }
        this.sessionId = null;
        this.currentFile = null;
        this.hideProgress();
        
        // Close modal if open
        if (this.previewModal) {
            bootstrap.Modal.getInstance(this.previewModal)?.hide();
        }
    }
}

/**
 * Background Export System
 * Prepares export in background, shows progress, enables instant download
 */
class BackgroundExport {
    constructor(options) {
        this.exportBtn = document.getElementById(options.exportBtnId);
        this.progressContainer = document.getElementById(options.progressContainerId);
        this.progressBar = document.getElementById(options.progressBarId);
        this.progressText = document.getElementById(options.progressTextId);
        this.downloadBtn = document.getElementById(options.downloadBtnId);
        
        this.initiateUrl = options.initiateUrl;
        this.statusUrl = options.statusUrl;
        this.downloadUrl = options.downloadUrl;
        this.csrfToken = options.csrfToken;
        
        this.jobId = null;
        this.pollInterval = null;
        
        this.init();
    }
    
    init() {
        if (this.exportBtn) {
            this.exportBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.startExport();
            });
        }
        
        if (this.downloadBtn) {
            this.downloadBtn.addEventListener('click', () => this.downloadFile());
        }
    }
    
    startExport() {
        this.showProgress();
        this.updateProgress(0, 'Preparing export...');
        
        // Get form data if exists
        const form = this.exportBtn.closest('form');
        const formData = form ? new FormData(form) : new FormData();
        
        fetch(this.initiateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.job_id) {
                this.jobId = data.job_id;
                this.pollProgress();
            } else if (data.download_url) {
                // Instant download (small file)
                this.updateProgress(100, 'Ready!');
                window.location.href = data.download_url;
                setTimeout(() => this.hideProgress(), 2000);
            } else {
                this.updateProgress(0, 'Export failed');
            }
        })
        .catch(err => {
            this.updateProgress(0, 'Export failed: ' + err.message);
        });
    }
    
    pollProgress() {
        this.pollInterval = setInterval(() => {
            fetch(`${this.statusUrl}?job_id=${this.jobId}`)
            .then(res => res.json())
            .then(data => {
                this.updateProgress(data.progress || 0, data.status || 'Processing...');
                
                if (data.complete) {
                    clearInterval(this.pollInterval);
                    this.updateProgress(100, 'Export ready! Click to download.');
                    if (this.downloadBtn) {
                        this.downloadBtn.style.display = 'inline-block';
                        this.downloadBtn.href = data.download_url || `${this.downloadUrl}?job_id=${this.jobId}`;
                    }
                }
            })
            .catch(() => {
                clearInterval(this.pollInterval);
            });
        }, 1000);
    }
    
    downloadFile() {
        if (this.jobId) {
            window.location.href = `${this.downloadUrl}?job_id=${this.jobId}`;
        }
    }
    
    showProgress() {
        if (this.progressContainer) {
            this.progressContainer.style.display = 'block';
        }
        if (this.downloadBtn) {
            this.downloadBtn.style.display = 'none';
        }
    }
    
    hideProgress() {
        if (this.progressContainer) {
            this.progressContainer.style.display = 'none';
        }
    }
    
    updateProgress(percent, text) {
        if (this.progressBar) {
            this.progressBar.style.width = percent + '%';
        }
        if (this.progressText) {
            this.progressText.textContent = text;
        }
    }
}

// Export for use
window.StagedUpload = StagedUpload;
window.BackgroundExport = BackgroundExport;
