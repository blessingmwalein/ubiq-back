# Upload APIs

File upload endpoints for images and videos (Admin only).

---

## Table of Contents

1. [Upload Image](#upload-image)
2. [Upload Video](#upload-video)
3. [Upload Video Chunk](#upload-video-chunk)
4. [Delete File](#delete-file)
5. [Delete Video Asset](#delete-video-asset)
6. [Get Upload Progress](#get-upload-progress)

---

## Overview

The upload system supports:
- **Images:** Posters, thumbnails, backdrops
- **Videos:** Full video files with quality detection
- **Chunked uploads:** For large video files
- **Local storage:** Files stored in `storage/app/public/`

**Important:** All uploads require admin authentication.

---

## Upload Image

Upload an image file (poster, thumbnail, backdrop).

### Endpoint
```
POST /admin/upload/image
```

### Headers
```
Authorization: Bearer ADMIN_TOKEN
Content-Type: multipart/form-data
Accept: application/json
```

### Form Data

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| file | file | Yes | Image file (JPG, PNG, WebP, max: 10MB) |
| folder | string | No | Subfolder name (default: 'images') |
| content_id | integer | No | Link to content item |
| type | string | No | Image type: poster, thumbnail, backdrop |

### Success Response (200 OK)

```json
{
  "success": true,
  "url": "https://your-domain.com/storage/images/abc123def456.jpg",
  "path": "/storage/images/abc123def456.jpg",
  "filename": "abc123def456.jpg",
  "size": 524288,
  "dimensions": {
    "width": 1920,
    "height": 1080
  }
}
```

### Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "file": ["The file must be an image.", "The file may not be greater than 10240 kilobytes."]
  }
}
```

### Frontend Example (React)

```javascript
async function uploadImage(file, options = {}) {
  const token = localStorage.getItem('admin_token');
  const formData = new FormData();
  
  formData.append('file', file);
  if (options.folder) formData.append('folder', options.folder);
  if (options.contentId) formData.append('content_id', options.contentId);
  if (options.type) formData.append('type', options.type);
  
  try {
    const response = await fetch('https://your-domain.com/admin/upload/image', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      return data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Image upload failed:', error);
    throw error;
  }
}

// Usage with file input
const handleFileSelect = async (event) => {
  const file = event.target.files[0];
  
  if (file) {
    try {
      const result = await uploadImage(file, {
        folder: 'posters',
        contentId: 123,
        type: 'poster'
      });
      
      console.log('Uploaded:', result.url);
      updateContentPoster(result.url);
    } catch (error) {
      showError('Failed to upload image');
    }
  }
};

// React component
function ImageUploader({ onUpload, contentId }) {
  const [uploading, setUploading] = useState(false);
  const [progress, setProgress] = useState(0);
  
  const handleUpload = async (file) => {
    setUploading(true);
    
    try {
      const result = await uploadImage(file, { 
        contentId,
        folder: 'posters',
        type: 'poster'
      });
      
      onUpload(result.url);
      showSuccess('Image uploaded successfully');
    } catch (error) {
      showError(error.message);
    } finally {
      setUploading(false);
    }
  };
  
  return (
    <div className="uploader">
      <input 
        type="file" 
        accept="image/*"
        onChange={(e) => handleUpload(e.target.files[0])}
        disabled={uploading}
      />
      {uploading && <progress value={progress} max="100" />}
    </div>
  );
}
```

---

## Upload Video

Upload a video file and automatically create video asset record.

### Endpoint
```
POST /admin/upload/video
```

### Headers
```
Authorization: Bearer ADMIN_TOKEN
Content-Type: multipart/form-data
Accept: application/json
```

### Form Data

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| file | file | Yes | Video file (MP4, MOV, WebM, AVI, max: 500MB) |
| folder | string | No | Subfolder name (default: 'videos') |
| content_id | integer | Yes | Content item ID to link video |

### Success Response (200 OK)

```json
{
  "success": true,
  "url": "https://your-domain.com/storage/videos/xyz789abc123.mp4",
  "path": "/storage/videos/xyz789abc123.mp4",
  "filename": "xyz789abc123.mp4",
  "size": 104857600,
  "metadata": {
    "duration": 7200,
    "resolution": "1920x1080",
    "bitrate": 5000,
    "codec": "h264",
    "fps": 30
  },
  "video_asset_id": 15,
  "video_asset": {
    "id": 15,
    "content_item_id": 123,
    "rendition_key": "1080p",
    "hls_manifest_key": "public/videos/xyz789abc123.mp4",
    "file_size_mb": 100.0,
    "resolution": "1920x1080",
    "bitrate": 5000,
    "status": "ready"
  }
}
```

### Frontend Example with Progress

```javascript
async function uploadVideo(file, contentId, onProgress) {
  const token = localStorage.getItem('admin_token');
  const formData = new FormData();
  
  formData.append('file', file);
  formData.append('content_id', contentId);
  formData.append('folder', 'videos');
  
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    
    // Track upload progress
    xhr.upload.addEventListener('progress', (e) => {
      if (e.lengthComputable) {
        const percentComplete = (e.loaded / e.total) * 100;
        onProgress(percentComplete);
      }
    });
    
    xhr.addEventListener('load', () => {
      if (xhr.status === 200) {
        const data = JSON.parse(xhr.responseText);
        resolve(data);
      } else {
        reject(new Error('Upload failed'));
      }
    });
    
    xhr.addEventListener('error', () => {
      reject(new Error('Upload failed'));
    });
    
    xhr.open('POST', 'https://your-domain.com/admin/upload/video');
    xhr.setRequestHeader('Authorization', `Bearer ${token}`);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.send(formData);
  });
}

// React component with progress bar
function VideoUploader({ contentId, onUploadComplete }) {
  const [uploading, setUploading] = useState(false);
  const [progress, setProgress] = useState(0);
  const [uploadedSize, setUploadedSize] = useState(0);
  const [totalSize, setTotalSize] = useState(0);
  
  const handleUpload = async (file) => {
    setUploading(true);
    setTotalSize(file.size);
    
    try {
      const result = await uploadVideo(file, contentId, (percent) => {
        setProgress(percent);
        setUploadedSize((file.size * percent) / 100);
      });
      
      showSuccess('Video uploaded successfully');
      onUploadComplete(result);
    } catch (error) {
      showError('Upload failed: ' + error.message);
    } finally {
      setUploading(false);
      setProgress(0);
    }
  };
  
  const formatBytes = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
  };
  
  return (
    <div className="video-uploader">
      <input 
        type="file" 
        accept="video/*"
        onChange={(e) => handleUpload(e.target.files[0])}
        disabled={uploading}
      />
      
      {uploading && (
        <div className="upload-progress">
          <div className="progress-bar">
            <div className="progress-fill" style={{ width: `${progress}%` }}></div>
          </div>
          <div className="progress-text">
            {Math.round(progress)}% - {formatBytes(uploadedSize)} / {formatBytes(totalSize)}
          </div>
        </div>
      )}
    </div>
  );
}
```

---

## Upload Video Chunk

Upload large videos in chunks for better reliability.

### Endpoint
```
POST /admin/upload/chunk
```

### Headers
```
Authorization: Bearer ADMIN_TOKEN
Content-Type: multipart/form-data
Accept: application/json
```

### Form Data

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| chunk | file | Yes | Video chunk file |
| chunkIndex | integer | Yes | Current chunk index (0-based) |
| totalChunks | integer | Yes | Total number of chunks |
| uploadId | string | Yes | Unique upload session ID |
| filename | string | Yes | Original filename |
| content_id | integer | Yes | Content item ID |

### Success Response (200 OK)

**Chunk uploaded (not final):**
```json
{
  "success": true,
  "message": "Chunk uploaded",
  "chunkIndex": 0,
  "totalChunks": 10,
  "uploadedChunks": 1,
  "complete": false
}
```

**Final chunk (upload complete):**
```json
{
  "success": true,
  "message": "Upload complete",
  "complete": true,
  "url": "https://your-domain.com/storage/videos/final-video.mp4",
  "video_asset_id": 20,
  "metadata": {
    "duration": 7200,
    "resolution": "1920x1080",
    "size": 524288000
  }
}
```

### Frontend Example (Chunked Upload)

```javascript
class ChunkedVideoUploader {
  constructor(file, contentId, options = {}) {
    this.file = file;
    this.contentId = contentId;
    this.chunkSize = options.chunkSize || 5 * 1024 * 1024; // 5MB chunks
    this.uploadId = this.generateUploadId();
    this.totalChunks = Math.ceil(file.size / this.chunkSize);
    this.uploadedChunks = 0;
    this.onProgress = options.onProgress || (() => {});
    this.token = localStorage.getItem('admin_token');
  }
  
  generateUploadId() {
    return `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
  }
  
  async uploadChunk(chunkIndex) {
    const start = chunkIndex * this.chunkSize;
    const end = Math.min(start + this.chunkSize, this.file.size);
    const chunk = this.file.slice(start, end);
    
    const formData = new FormData();
    formData.append('chunk', chunk);
    formData.append('chunkIndex', chunkIndex);
    formData.append('totalChunks', this.totalChunks);
    formData.append('uploadId', this.uploadId);
    formData.append('filename', this.file.name);
    formData.append('content_id', this.contentId);
    
    const response = await fetch('https://your-domain.com/admin/upload/chunk', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json'
      },
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      this.uploadedChunks++;
      const progress = (this.uploadedChunks / this.totalChunks) * 100;
      this.onProgress(progress, this.uploadedChunks, this.totalChunks);
      
      return data;
    } else {
      throw new Error(data.message || 'Chunk upload failed');
    }
  }
  
  async upload() {
    try {
      for (let i = 0; i < this.totalChunks; i++) {
        const result = await this.uploadChunk(i);
        
        if (result.complete) {
          return result;
        }
      }
    } catch (error) {
      console.error('Upload failed:', error);
      throw error;
    }
  }
}

// Usage
async function uploadLargeVideo(file, contentId) {
  const uploader = new ChunkedVideoUploader(file, contentId, {
    chunkSize: 10 * 1024 * 1024, // 10MB chunks
    onProgress: (percent, uploaded, total) => {
      console.log(`Progress: ${percent.toFixed(2)}% (${uploaded}/${total} chunks)`);
      updateProgressBar(percent);
    }
  });
  
  try {
    const result = await uploader.upload();
    console.log('Upload complete:', result.url);
    return result;
  } catch (error) {
    console.error('Upload failed:', error);
    throw error;
  }
}
```

---

## Delete File

Delete an uploaded file from storage.

### Endpoint
```
DELETE /admin/upload/file
```

### Headers
```
Authorization: Bearer ADMIN_TOKEN
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
  "path": "/storage/videos/abc123.mp4"
}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "File deleted successfully"
}
```

### Error Response (404 Not Found)

```json
{
  "success": false,
  "message": "File not found"
}
```

---

## Delete Video Asset

Delete a video asset record and its associated file.

### Endpoint
```
DELETE /admin/video-assets/{id}
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Video asset ID |

### Headers
```
Authorization: Bearer ADMIN_TOKEN
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Video asset deleted successfully"
}
```

---

## Get Upload Progress

Check the status of a chunked upload.

### Endpoint
```
GET /admin/upload/progress/{uploadId}
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| uploadId | string | Yes | Upload session ID |

### Headers
```
Authorization: Bearer ADMIN_TOKEN
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "upload_id": "1636896000-abc123",
    "filename": "large-video.mp4",
    "total_chunks": 20,
    "uploaded_chunks": 15,
    "progress_percentage": 75,
    "complete": false,
    "started_at": "2025-11-16T18:00:00.000000Z"
  }
}
```

---

## File Upload Best Practices

### File Size Limits
- **Images:** 10MB maximum
- **Videos (single upload):** 500MB maximum
- **Videos (chunked upload):** No limit (recommended for files > 100MB)

### Recommended Chunk Sizes
- **Fast connection:** 10MB chunks
- **Average connection:** 5MB chunks
- **Slow connection:** 2MB chunks

### Video Formats
Supported formats:
- MP4 (H.264 codec recommended)
- WebM
- MOV
- AVI

### Image Formats
Supported formats:
- JPEG
- PNG
- WebP
- GIF (for thumbnails)

### Error Handling

```javascript
async function uploadWithRetry(file, contentId, maxRetries = 3) {
  let lastError;
  
  for (let attempt = 1; attempt <= maxRetries; attempt++) {
    try {
      return await uploadVideo(file, contentId, (progress) => {
        console.log(`Attempt ${attempt}: ${progress}%`);
      });
    } catch (error) {
      lastError = error;
      console.error(`Upload attempt ${attempt} failed:`, error);
      
      if (attempt < maxRetries) {
        // Wait before retrying (exponential backoff)
        await new Promise(resolve => setTimeout(resolve, 1000 * Math.pow(2, attempt)));
      }
    }
  }
  
  throw new Error(`Upload failed after ${maxRetries} attempts: ${lastError.message}`);
}
```

---

## Video Processing Queue

After upload, videos may be queued for processing:

```json
{
  "success": true,
  "video_asset": {
    "id": 25,
    "status": "processing",
    "message": "Video is being processed. This may take several minutes."
  }
}
```

Poll for completion:

```javascript
async function waitForProcessing(videoAssetId) {
  const maxWait = 600000; // 10 minutes
  const pollInterval = 5000; // 5 seconds
  const startTime = Date.now();
  
  while (Date.now() - startTime < maxWait) {
    const response = await fetch(
      `https://your-domain.com/admin/video-assets/${videoAssetId}`,
      {
        headers: {
          'Authorization': `Bearer ${adminToken}`,
          'Accept': 'application/json'
        }
      }
    );
    
    const data = await response.json();
    
    if (data.status === 'ready') {
      return data;
    } else if (data.status === 'failed') {
      throw new Error('Video processing failed');
    }
    
    await new Promise(resolve => setTimeout(resolve, pollInterval));
  }
  
  throw new Error('Processing timeout');
}
```

---

**Back to:** [API Overview ←](./API_OVERVIEW.md)
