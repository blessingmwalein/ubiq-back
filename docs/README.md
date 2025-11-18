# API Documentation - Quick Reference

## 📚 Complete API Documentation Suite

All API documentation has been created and organized into separate files for easy navigation and frontend integration.

### Documentation Files Status: ✅ COMPLETE

1. **[API_OVERVIEW.md](./API_OVERVIEW.md)** - Main overview and quick start guide
2. **[AUTH_APIS.md](./AUTH_APIS.md)** - Authentication & authorization endpoints
3. **[CONTENT_APIS.md](./CONTENT_APIS.md)** - Content browsing and discovery
4. **[PLAYBACK_APIS.md](./PLAYBACK_APIS.md)** - Video streaming and playback
5. **[PROFILE_APIS.md](./PROFILE_APIS.md)** - User profiles and preferences
6. **[SUBSCRIPTION_APIS.md](./SUBSCRIPTION_APIS.md)** - Billing and subscriptions
7. **[ADMIN_APIS.md](./ADMIN_APIS.md)** - Admin panel management
8. **[UPLOAD_APIS.md](./UPLOAD_APIS.md)** - File upload system

---

## 🚀 Getting Started

### For Frontend Developers

1. Start with **[API_OVERVIEW.md](./API_OVERVIEW.md)** for:
   - Base URLs and authentication
   - Response formats and error handling
   - Rate limiting information
   - Storage configuration

2. Read **[AUTH_APIS.md](./AUTH_APIS.md)** to implement:
   - User registration and login
   - Token-based authentication
   - Password reset flow
   - Two-factor authentication

3. Use **[CONTENT_APIS.md](./CONTENT_APIS.md)** for:
   - Displaying content catalogs
   - Search functionality
   - Categories and filters
   - Trending and recommendations

4. Integrate **[PLAYBACK_APIS.md](./PLAYBACK_APIS.md)** for:
   - Video player implementation
   - Progress tracking
   - Watch history
   - Quality selection

### For Admin Developers

1. Review **[ADMIN_APIS.md](./ADMIN_APIS.md)** for:
   - Content management CRUD
   - User administration
   - Analytics dashboard
   - System settings

2. Check **[UPLOAD_APIS.md](./UPLOAD_APIS.md)** for:
   - Image uploads (posters, thumbnails)
   - Video uploads with progress
   - Chunked upload for large files

---

## 📊 API Categories

### Public APIs (No Auth Required)
- Get subscription plans
- Browse public content
- Search content
- Get categories

### User APIs (Auth Required)
- User registration/login
- Profile management
- Watchlist and favorites
- Playback and streaming
- Subscription management

### Admin APIs (Admin Auth Required)
- Content CRUD operations
- User management
- Provider/category management
- Analytics and reporting
- File uploads

---

## 🔑 Authentication

### User Authentication
```bash
# Login to get token
POST /api/login

# Use token in headers
Authorization: Bearer YOUR_TOKEN_HERE
```

### Admin Authentication
```bash
# Admin login
POST /admin/login

# Use admin token
Authorization: Bearer ADMIN_TOKEN_HERE
```

---

## 📦 Storage Configuration

### Current Setup: Local Storage

- **Storage Path:** `storage/app/public/`
- **Public URL:** `https://your-domain.com/storage/`
- **Video Path:** `storage/app/public/videos/`
- **Image Path:** `storage/app/public/images/`

### Setup Command
```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

---

## 🎯 Common Use Cases

### 1. User Registration & Login Flow

```javascript
// Step 1: Register
const registerResponse = await fetch('/api/register', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: 'John Doe',
    email: 'john@example.com',
    password: 'password123',
    password_confirmation: 'password123'
  })
});

// Step 2: Login
const loginResponse = await fetch('/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'john@example.com',
    password: 'password123'
  })
});

const { token } = await loginResponse.json();
localStorage.setItem('token', token);
```

### 2. Browse and Watch Content Flow

```javascript
// Step 1: Get content
const contentResponse = await fetch('/api/content?type=movie&per_page=20');
const { data: movies } = await contentResponse.json();

// Step 2: Request playback token
const tokenResponse = await fetch('/api/playback/token', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    content_id: movies[0].id,
    profile_id: userProfileId,
    device_id: deviceId
  })
});

const { playback_token } = await tokenResponse.json();

// Step 3: Get streaming URL
const streamResponse = await fetch(`/api/playback/stream/${playback_token}`);
const { manifest_url } = await streamResponse.json();

// Step 4: Initialize video player
initializePlayer(manifest_url);

// Step 5: Track progress
setInterval(() => {
  fetch('/api/playback/progress', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      playback_token,
      position: player.currentTime,
      duration: player.duration
    })
  });
}, 10000); // Every 10 seconds
```

### 3. Admin Content Creation Flow

```javascript
// Step 1: Upload poster image
const formData = new FormData();
formData.append('file', posterFile);
formData.append('folder', 'posters');

const posterResponse = await fetch('/admin/upload/image', {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${adminToken}` },
  body: formData
});

const { url: posterUrl } = await posterResponse.json();

// Step 2: Create content
const contentResponse = await fetch('/admin/content', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${adminToken}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'New Movie',
    type: 'movie',
    description: 'Exciting new film',
    category_id: 1,
    provider_id: 2,
    poster_url: posterUrl,
    visibility: 'public',
    published_at: new Date().toISOString()
  })
});

const { data: newContent } = await contentResponse.json();

// Step 3: Upload video
const videoFormData = new FormData();
videoFormData.append('file', videoFile);
videoFormData.append('content_id', newContent.id);

const videoResponse = await fetch('/admin/upload/video', {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${adminToken}` },
  body: videoFormData
});
```

---

## 🔍 Error Handling

### Standard Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message 1", "Error message 2"]
  }
}
```

### Common HTTP Status Codes

- **200 OK** - Request successful
- **201 Created** - Resource created successfully
- **400 Bad Request** - Invalid request data
- **401 Unauthorized** - Missing or invalid token
- **403 Forbidden** - Insufficient permissions
- **404 Not Found** - Resource not found
- **422 Unprocessable Entity** - Validation failed
- **429 Too Many Requests** - Rate limit exceeded
- **500 Internal Server Error** - Server error

---

## 📈 Rate Limiting

- **Authenticated requests:** 60 requests per minute
- **Unauthenticated requests:** 30 requests per minute
- **Admin requests:** 120 requests per minute

Rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
X-RateLimit-Reset: 1636896000
```

---

## 🛠️ Testing the APIs

### Using cURL

```bash
# Login
curl -X POST https://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Get content with token
curl https://your-domain.com/api/content \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

### Using Postman

1. Import the API documentation as a Postman collection
2. Set base URL variable: `{{base_url}}` = `https://your-domain.com/api`
3. Set auth token variable: `{{token}}` = `your_token_here`
4. Add to request headers: `Authorization: Bearer {{token}}`

---

## 📝 Change Log

### November 16, 2025

**Database Column Fixes:**
- Fixed `views` → `views_count` in ContentService
- Fixed `quality` → `rendition_key` in video assets
- Fixed `file_path` → `hls_manifest_key` in video assets
- Fixed `file_size` → `file_size_mb` in video assets
- Removed `type` column filters (all assets are HLS)
- Fixed `revoked` → `used_at` logic in playback tokens
- Fixed `ip_address` → `client_ip` in token creation

**Relationship Fixes:**
- Fixed `contentProvider()` → `provider()` relationship

**Storage Configuration:**
- Changed from S3 to local storage
- Updated all file URLs to use public disk
- Removed S3 temporary URL generation
- Using permanent public URLs: `Storage::disk('public')->url()`

**Features Added:**
- Auto-set visibility to 'public' on content creation
- Auto-set published_at to now() on content creation
- Comprehensive API documentation suite (8 files)

---

## 🎓 Best Practices

### 1. Always Use HTTPS
```javascript
const BASE_URL = 'https://your-domain.com/api'; // ✅
const BASE_URL = 'http://your-domain.com/api';  // ❌
```

### 2. Store Tokens Securely
```javascript
// ✅ Use secure storage
localStorage.setItem('token', token);

// ✅ Clear on logout
localStorage.removeItem('token');

// ❌ Don't expose in URLs
window.location.href = `/dashboard?token=${token}`;
```

### 3. Handle Errors Gracefully
```javascript
try {
  const response = await fetch(url);
  const data = await response.json();
  
  if (!response.ok) {
    throw new Error(data.message || 'Request failed');
  }
  
  return data;
} catch (error) {
  console.error('API Error:', error);
  showUserFriendlyError(error.message);
}
```

### 4. Implement Retry Logic
```javascript
async function fetchWithRetry(url, options, maxRetries = 3) {
  for (let i = 0; i < maxRetries; i++) {
    try {
      const response = await fetch(url, options);
      if (response.ok) return response;
      
      if (response.status >= 500 && i < maxRetries - 1) {
        await new Promise(resolve => setTimeout(resolve, 1000 * (i + 1)));
        continue;
      }
      
      return response;
    } catch (error) {
      if (i === maxRetries - 1) throw error;
      await new Promise(resolve => setTimeout(resolve, 1000 * (i + 1)));
    }
  }
}
```

### 5. Paginate Large Lists
```javascript
// ✅ Load data in pages
const response = await fetch('/api/content?page=1&per_page=20');

// ❌ Don't load everything at once
const response = await fetch('/api/content?per_page=10000');
```

---

## 🤝 Support

For questions or issues:
- Review the specific API documentation file for your use case
- Check the error response format and status codes
- Verify authentication tokens are valid
- Ensure request format matches documentation examples

---

**Start with:** [API Overview →](./API_OVERVIEW.md)
