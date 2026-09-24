/**
 * VIDEO-UPLOAD-MANAGER.JS
 * 
 * Intelligent video upload system with:
 * - Universal chunking (chunks ALL videos >= 1MB into 512KB pieces, even small ones)
 * - Progress tracking with visual progress bar
 * - Auto-pause/resume on network loss
 * - Automatic retry with exponential backoff
 * - Network status monitoring
 * - Logs to the dashboard-wide debugger (window.dlog) defined in admin-dashboard.html
 */

const VideoUploadManager = {
  config: {
    chunkThreshold: 1 * 1024 * 1024,   // 1MB — chunk EVERYTHING >= 1MB
    chunkSize: 512 * 1024,              // 512KB chunks for efficiency
    maxRetries: 3,
    retryDelay: 1000,
    timeout: 60000,
  },

  uploads: {},
  networkOnline: navigator.onLine,

  init() {
    // window.dlog is provided globally by admin-dashboard.html's dashboard-wide debugger.
    // Fall back to a no-op if this script is loaded standalone without that host page.
    if (typeof window.dlog !== 'function') {
      window.dlog = () => {};
    }
    window.addEventListener('online', () => {
      VideoUploadManager.networkOnline = true;
      window.dlog('Video: network restored - resuming uploads', 'ok');
      VideoUploadManager.resumeAllUploads();
    });
    window.addEventListener('offline', () => {
      VideoUploadManager.networkOnline = false;
      window.dlog('Video: network lost - pausing uploads', 'warn');
      VideoUploadManager.pauseAllUploads();
    });
  },

  shouldChunk(fileSize) {
    return fileSize >= this.config.chunkThreshold;
  },

  getChunkSize(fileSize) {
    if (!this.shouldChunk(fileSize)) return fileSize;
    return this.config.chunkSize;
  },

  async startUpload(file, onProgress, onComplete, onError) {
    if (!file) return;
    const uploadId = this.generateUploadId();
    const chunkSize = this.getChunkSize(file.size);
    const totalChunks = Math.ceil(file.size / chunkSize);
    window.dlog(`Video: ${file.name} (${this.formatFileSize(file.size)}) - ${totalChunks} chunk${totalChunks === 1 ? '' : 's'}`, 'info');

    this.uploads[uploadId] = {
      uploadId, file, totalChunks, uploadedChunks: new Set(),
      status: 'uploading', retries: {}, onProgress, onComplete, onError,
      sessionId: Date.now() + Math.random(), chunkSize,
    };
    this.saveUploadState(uploadId);
    this.uploadChunk(uploadId);
  },

  async uploadChunk(uploadId) {
    const upload = this.uploads[uploadId];
    if (!upload) return;

    if (!this.networkOnline) {
      upload.status = 'paused';
      window.dlog('PAUSED - Waiting for network...', 'warn');
      return;
    }

    let chunkIndex = -1;
    for (let i = 0; i < upload.totalChunks; i++) {
      if (!upload.uploadedChunks.has(i)) { chunkIndex = i; break; }
    }

    if (chunkIndex === -1) { this.finalizeUpload(uploadId); return; }

    const file = upload.file;
    const start = chunkIndex * upload.chunkSize;
    const end = Math.min(start + upload.chunkSize, file.size);
    const chunk = file.slice(start, end);
    const chunkNum = chunkIndex + 1;

    try {
      const fd = new FormData();
      fd.append('sessionId', upload.sessionId);
      fd.append('uploadId', uploadId);
      fd.append('chunkIndex', chunkIndex);
      fd.append('totalChunks', upload.totalChunks);
      fd.append('fileName', file.name);
      fd.append('fileSize', file.size);
      fd.append('chunk', chunk);
      fd.append('token', window.adminToken || '');

      const xhr = new XMLHttpRequest();

      xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
          const chunkProgress = e.loaded / e.total;
          const totalProgress = (upload.uploadedChunks.size + chunkProgress) / upload.totalChunks;
          upload.onProgress?.(totalProgress * 100);
        }
      });

      const timeoutId = setTimeout(() => xhr.abort(), this.config.timeout);

      await new Promise((resolve, reject) => {
        xhr.onload = () => {
          clearTimeout(timeoutId);
          if (xhr.status >= 200 && xhr.status < 300) resolve(xhr.responseText);
          else reject(new Error(`HTTP ${xhr.status}`));
        };
        xhr.onerror = () => { clearTimeout(timeoutId); reject(new Error('Network')); };
        xhr.onabort = () => { clearTimeout(timeoutId); reject(new Error('Timeout')); };
        xhr.open('POST', './upload-video-chunk.php');
        xhr.send(fd);
      }).then(res => {
        const result = JSON.parse(res);
        if (!result.success) throw new Error(result.error);
        upload.uploadedChunks.add(chunkIndex);
        upload.retries[chunkIndex] = 0;
        window.dlog(`Chunk ${chunkNum}/${upload.totalChunks} OK`, 'ok');
        this.saveUploadState(uploadId);
        setTimeout(() => this.uploadChunk(uploadId), 100);
      });
    } catch (error) {
      const retries = upload.retries[chunkIndex] || 0;
      if (retries < this.config.maxRetries) {
        upload.retries[chunkIndex] = retries + 1;
        const delay = this.config.retryDelay * Math.pow(2, retries);
        window.dlog(`Chunk ${chunkNum} failed - retrying in ${delay}ms... (${retries + 1}/${this.config.maxRetries})`, 'warn');
        setTimeout(() => this.uploadChunk(uploadId), delay);
      } else {
        upload.status = 'error';
        window.dlog(`Chunk ${chunkNum} FAILED - max retries exceeded`, 'error');
        upload.onError?.(error);
      }
    }
  },

  async finalizeUpload(uploadId) {
    const upload = this.uploads[uploadId];
    if (!upload) return;
    window.dlog('Assembling final video...', 'info');

    try {
      const fd = new FormData();
      fd.append('action', 'finalize');
      fd.append('uploadId', uploadId);
      fd.append('sessionId', upload.sessionId);
      fd.append('totalChunks', upload.totalChunks);
      fd.append('fileName', upload.file.name);
      fd.append('token', window.adminToken || '');

      const res = await fetch('./upload-video-chunk.php', { method: 'POST', body: fd });
      const result = await res.json();

      if (result.success) {
        upload.status = 'completed';
        window.dlog(`SUCCESS: ${upload.file.name} ready!`, 'ok');
        upload.onComplete?.({ uploadId, videoToken: result.videoToken, fileName: upload.file.name });
        localStorage.removeItem(`vum_${uploadId}`);
      } else {
        throw new Error(result.error || 'Failed');
      }
    } catch (error) {
      upload.status = 'error';
      window.dlog(`Assembly failed: ${error.message}`, 'error');
      upload.onError?.(error);
    }
  },

  pauseAllUploads() {
    for (const uploadId in this.uploads) {
      if (this.uploads[uploadId].status === 'uploading') this.uploads[uploadId].status = 'paused';
    }
  },

  resumeAllUploads() {
    for (const uploadId in this.uploads) {
      if (this.uploads[uploadId].status === 'paused') {
        this.uploads[uploadId].status = 'uploading';
        this.uploadChunk(uploadId);
      }
    }
  },

  saveUploadState(uploadId) {
    const upload = this.uploads[uploadId];
    const state = {
      fileName: upload.file.name,
      fileSize: upload.file.size,
      totalChunks: upload.totalChunks,
      uploadedChunks: Array.from(upload.uploadedChunks),
      sessionId: upload.sessionId,
      retries: upload.retries,
      status: upload.status,
    };
    localStorage.setItem(`vum_${uploadId}`, JSON.stringify(state));
  },

  generateUploadId() {
    return `upload_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  },

  formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024, sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
  },
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => VideoUploadManager.init());
} else {
  VideoUploadManager.init();
}
