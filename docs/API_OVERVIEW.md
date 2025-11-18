# UBIQ Entertainment Platform - API Overview

**Last Updated:** November 16, 2025  
**Base URL:** `https://your-domain.com/api`  
**Admin Base URL:** `https://your-domain.com/admin`

## Table of Contents

1. [Authentication APIs](./AUTH_APIS.md)
2. [Content APIs](./CONTENT_APIS.md)
3. [Streaming & Playback APIs](./PLAYBACK_APIS.md)
4. [User Profile & Account APIs](./PROFILE_APIS.md)
5. [Subscription & Payment APIs](./SUBSCRIPTION_APIS.md)
6. [Admin APIs](./ADMIN_APIS.md)
7. [Upload APIs](./UPLOAD_APIS.md)

---

## Quick Start Guide

### 1. Authentication Flow

```javascript
// Register new user
POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!"
}

// Login
POST /api/login
{
  "email": "john@example.com",
  "password": "SecurePass123!"
}

// Returns: { token: "...", user: {...} }
```

### 2. Browse Content

```javascript
// Get all content
GET /api/content?per_page=20&page=1

// Search content
GET /api/content/search?q=action&category_id=1

// Get content details
GET /api/content/{id}
```

### 3. Playback Flow

```javascript
// 1. Request playback token
POST /api/playback/token
{
  "content_id": 123,
  "profile_id": 1
}

// 2. Get streaming URL
GET /api/playback/stream/{token}

// 3. Track progress
POST /api/playback/progress
{
  "token": "...",
  "position": 120,
  "duration": 3600
}
```

---

## Authentication

All authenticated endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

### Token Types

1. **User Token** - From `/api/login` - Access user endpoints
2. **Playback Token** - From `/api/playback/token` - Access streaming URLs
3. **Admin Token** - From `/admin/login` - Access admin endpoints

---

## Response Format

### Success Response

```json
{
  "success": true,
  "data": {
    // Response data here
  },
  "message": "Operation successful"
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### Pagination Response

```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

---

## Common Query Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `per_page` | integer | Items per page | 15 |
| `page` | integer | Page number | 1 |
| `sort` | string | Sort field | - |
| `order` | string | Sort order (asc/desc) | desc |
| `category_id` | integer | Filter by category | - |
| `type` | string | Content type filter | - |

---

## Rate Limiting

- **Authenticated requests:** 60 requests per minute
- **Unauthenticated requests:** 30 requests per minute
- **Streaming requests:** No limit (uses playback tokens)

Rate limit headers included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1636896000
```

---

## Error Codes

| Code | Status | Description |
|------|--------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request parameters |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

---

## Content Types

Content is categorized by type:

- `movie` - Full-length movies
- `show` - TV series with seasons/episodes
- `skit` - Short-form comedy/entertainment
- `afrimation` - Affirmation/motivational content
- `real_estate` - Real estate listings/tours

---

## Content Visibility

- `public` - Available to all users
- `premium` - Requires active subscription
- `private` - Not publicly visible

---

## Video Quality Options

Videos are available in multiple quality levels:

- `1080p` - 1920x1080 resolution
- `720p` - 1280x720 resolution
- `480p` - 854x480 resolution
- `360p` - 640x360 resolution

---

## Storage & CDN

### Local Storage Configuration

Videos are stored locally in `storage/app/public/videos/`

**Public URL Format:**
```
https://your-domain.com/storage/videos/{filename}
```

**Setup Required:**
```bash
php artisan storage:link
```

This creates a symlink from `public/storage` → `storage/app/public`

---

## CORS Configuration

CORS is enabled for frontend applications. Allowed origins configured in `.env`:

```env
FRONTEND_URL=https://your-frontend-domain.com
```

---

## Testing APIs

### Using cURL

```bash
# Login
curl -X POST https://your-domain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Get content (authenticated)
curl -X GET https://your-domain.com/api/content \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Using Postman

Import the Postman collection: [Download Collection](./postman_collection.json)

---

## WebSocket Events (Future)

Real-time features planned:
- Watch party synchronization
- Live streaming
- Real-time notifications

---

## API Versioning

Currently using v1 (implicit). Future versions will use URL prefix:
- v1: `/api/...` (current)
- v2: `/api/v2/...` (future)

---

## Support

For API support:
- Email: api-support@ubiq-entertainment.com
- Documentation: https://docs.ubiq-entertainment.com
- Status Page: https://status.ubiq-entertainment.com

---

## Changelog

### 2025-11-16
- ✅ Fixed database column mismatches (views, quality, type, revoked)
- ✅ Updated to use local storage instead of S3
- ✅ Fixed relationship names (contentProvider → provider)
- ✅ Added auto-visibility and published_at for content
- ✅ Comprehensive API documentation created

---

**Next:** Read detailed endpoint documentation in the linked files above.
