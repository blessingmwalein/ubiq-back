# Admin APIs

Administrative endpoints for content management, user management, and analytics (Admin only).

---

## Table of Contents

1. [Admin Authentication](#admin-authentication)
2. [Content Management](#content-management)
   - [Get All Content](#get-all-content)
   - [Create Content](#create-content)
   - [Update Content](#update-content)
   - [Delete Content](#delete-content)
   - [Bulk Actions](#bulk-actions)
3. [Category Management](#category-management)
4. [Provider Management](#provider-management)
5. [User Management](#user-management)
6. [Video Asset Management](#video-asset-management)
7. [Analytics](#analytics)
8. [Settings](#settings)

---

## Admin Authentication

All admin endpoints require an admin user with proper permissions.

### Admin Login

```
POST /admin/login
```

**Request:**
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "token": "admin_token_here",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin"
  }
}
```

### Headers for All Admin Endpoints
```
Authorization: Bearer ADMIN_TOKEN
Content-Type: application/json
Accept: application/json
```

---

## Content Management

### Get All Content

Get all content items with filtering and pagination (admin view).

#### Endpoint
```
GET /admin/content
```

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number (default: 1) |
| per_page | integer | No | Items per page (default: 15, max: 100) |
| type | string | No | Filter: movie, show, episode |
| category_id | integer | No | Filter by category |
| provider_id | integer | No | Filter by provider |
| visibility | string | No | Filter: public, private, premium |
| status | string | No | Filter: draft, published, archived |
| search | string | No | Search in title, description |
| sort_by | string | No | Sort field: created_at, title, views_count, rating |
| sort_order | string | No | Sort order: asc, desc (default: desc) |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Action Movie",
      "slug": "action-movie",
      "type": "movie",
      "description": "Exciting action movie",
      "poster_url": "https://domain.com/storage/images/poster.jpg",
      "thumbnail_url": "https://domain.com/storage/images/thumb.jpg",
      "backdrop_url": "https://domain.com/storage/images/backdrop.jpg",
      "visibility": "public",
      "status": "published",
      "published_at": "2025-11-01T00:00:00.000000Z",
      "duration": 7200,
      "release_year": 2025,
      "rating": 4.5,
      "views_count": 1500,
      "category": {
        "id": 1,
        "name": "Action",
        "slug": "action"
      },
      "provider": {
        "id": 1,
        "name": "Studio XYZ",
        "slug": "studio-xyz"
      },
      "video_assets_count": 3,
      "created_at": "2025-10-15T00:00:00.000000Z",
      "updated_at": "2025-11-16T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

---

### Create Content

Create a new content item.

#### Endpoint
```
POST /admin/content
```

#### Request Body

```json
{
  "title": "New Action Movie",
  "type": "movie",
  "description": "An exciting new action film",
  "category_id": 1,
  "provider_id": 2,
  "poster_url": "https://domain.com/storage/images/poster.jpg",
  "thumbnail_url": "https://domain.com/storage/images/thumb.jpg",
  "backdrop_url": "https://domain.com/storage/images/backdrop.jpg",
  "trailer_url": "https://www.youtube.com/watch?v=xyz",
  "duration": 7200,
  "release_year": 2025,
  "rating": 0,
  "visibility": "public",
  "published_at": "2025-11-16T18:00:00.000000Z",
  "metadata": {
    "director": "John Director",
    "cast": ["Actor One", "Actor Two"],
    "genres": ["Action", "Thriller"],
    "languages": ["English", "Spanish"],
    "subtitles": ["English", "Spanish", "French"]
  }
}
```

#### Field Descriptions

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| title | string | Yes | Content title (max: 255) |
| type | string | Yes | Content type: movie, show, episode |
| description | text | No | Full description |
| category_id | integer | Yes | Category ID |
| provider_id | integer | Yes | Content provider ID |
| poster_url | string | No | Poster image URL |
| thumbnail_url | string | No | Thumbnail image URL |
| backdrop_url | string | No | Backdrop image URL |
| trailer_url | string | No | Trailer video URL (YouTube, Vimeo) |
| duration | integer | No | Duration in seconds |
| release_year | integer | No | Release year (1900-2100) |
| rating | decimal | No | Rating (0.0-10.0) |
| visibility | string | No | Visibility: public, private, premium (default: public) |
| published_at | datetime | No | Publish date (default: now) |
| metadata | json | No | Additional metadata |

#### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Content created successfully",
  "data": {
    "id": 151,
    "title": "New Action Movie",
    "slug": "new-action-movie",
    "type": "movie",
    "visibility": "public",
    "status": "published",
    "published_at": "2025-11-16T18:00:00.000000Z",
    "created_at": "2025-11-16T18:00:00.000000Z"
  }
}
```

#### Frontend Example (React)

```javascript
async function createContent(contentData) {
  const token = localStorage.getItem('admin_token');
  
  try {
    const response = await fetch('https://your-domain.com/admin/content', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(contentData)
    });
    
    const data = await response.json();
    
    if (data.success) {
      showSuccess('Content created successfully');
      return data.data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Failed to create content:', error);
    throw error;
  }
}

// React form component
function CreateContentForm() {
  const [formData, setFormData] = useState({
    title: '',
    type: 'movie',
    description: '',
    category_id: '',
    provider_id: '',
    poster_url: '',
    thumbnail_url: '',
    backdrop_url: '',
    trailer_url: '',
    duration: 0,
    release_year: new Date().getFullYear(),
    visibility: 'public',
    published_at: new Date().toISOString().slice(0, 16),
    metadata: {
      director: '',
      cast: [],
      genres: [],
      languages: ['English'],
      subtitles: []
    }
  });
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      const content = await createContent(formData);
      navigate(`/admin/content/${content.id}`);
    } catch (error) {
      showError(error.message);
    }
  };
  
  return (
    <form onSubmit={handleSubmit}>
      <input
        type="text"
        placeholder="Title"
        value={formData.title}
        onChange={(e) => setFormData({ ...formData, title: e.target.value })}
        required
      />
      
      <select
        value={formData.type}
        onChange={(e) => setFormData({ ...formData, type: e.target.value })}
      >
        <option value="movie">Movie</option>
        <option value="show">TV Show</option>
        <option value="episode">Episode</option>
      </select>
      
      <textarea
        placeholder="Description"
        value={formData.description}
        onChange={(e) => setFormData({ ...formData, description: e.target.value })}
      />
      
      {/* Category and Provider selects */}
      <CategorySelect
        value={formData.category_id}
        onChange={(id) => setFormData({ ...formData, category_id: id })}
      />
      
      <ProviderSelect
        value={formData.provider_id}
        onChange={(id) => setFormData({ ...formData, provider_id: id })}
      />
      
      {/* Image uploads */}
      <ImageUploader
        label="Poster"
        onUpload={(url) => setFormData({ ...formData, poster_url: url })}
      />
      
      {/* More fields... */}
      
      <button type="submit">Create Content</button>
    </form>
  );
}
```

---

### Update Content

Update an existing content item.

#### Endpoint
```
PUT /admin/content/{id}
```

#### Request Body

Same as Create Content, all fields optional.

```json
{
  "title": "Updated Title",
  "description": "Updated description",
  "visibility": "premium",
  "rating": 4.8
}
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Content updated successfully",
  "data": {
    "id": 151,
    "title": "Updated Title",
    "description": "Updated description",
    "visibility": "premium",
    "rating": 4.8,
    "updated_at": "2025-11-16T19:00:00.000000Z"
  }
}
```

---

### Delete Content

Delete a content item (soft delete).

#### Endpoint
```
DELETE /admin/content/{id}
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Content deleted successfully"
}
```

---

### Bulk Actions

Perform bulk operations on multiple content items.

#### Endpoint
```
POST /admin/content/bulk
```

#### Request Body

```json
{
  "action": "delete",
  "content_ids": [1, 2, 3, 4, 5]
}
```

**Available actions:**
- `delete` - Soft delete content
- `publish` - Set status to published
- `unpublish` - Set status to draft
- `set_visibility` - Change visibility (requires `visibility` field)
- `set_category` - Change category (requires `category_id` field)

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Bulk action completed",
  "data": {
    "affected_count": 5,
    "action": "delete"
  }
}
```

---

## Category Management

### Get All Categories

```
GET /admin/categories
```

### Create Category

```
POST /admin/categories
```

**Request:**
```json
{
  "name": "Documentary",
  "slug": "documentary",
  "description": "Documentary content",
  "parent_id": null,
  "icon": "film",
  "is_active": true
}
```

### Update Category

```
PUT /admin/categories/{id}
```

### Delete Category

```
DELETE /admin/categories/{id}
```

---

## Provider Management

### Get All Providers

```
GET /admin/providers
```

### Create Provider

```
POST /admin/providers
```

**Request:**
```json
{
  "name": "New Studio",
  "slug": "new-studio",
  "description": "A new content studio",
  "logo_url": "https://domain.com/storage/images/logo.png",
  "website_url": "https://newstudio.com",
  "is_active": true
}
```

### Update Provider

```
PUT /admin/providers/{id}
```

### Delete Provider

```
DELETE /admin/providers/{id}
```

---

## User Management

### Get All Users

```
GET /admin/users
```

**Query Parameters:**
- `page`, `per_page` - Pagination
- `search` - Search by name, email
- `status` - Filter: active, inactive, suspended
- `subscription_status` - Filter: active, cancelled, expired
- `sort_by` - created_at, name, email
- `sort_order` - asc, desc

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "status": "active",
      "email_verified_at": "2025-01-15T00:00:00.000000Z",
      "subscription": {
        "plan": "Standard",
        "status": "active",
        "expires_at": "2025-12-01T00:00:00.000000Z"
      },
      "profiles_count": 3,
      "created_at": "2025-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 1250
  }
}
```

### Get User Details

```
GET /admin/users/{id}
```

**Response includes:**
- Full user information
- Subscription history
- Watch history summary
- Profiles
- Payment methods

### Update User

```
PUT /admin/users/{id}
```

**Request:**
```json
{
  "name": "Updated Name",
  "email": "newemail@example.com",
  "status": "active"
}
```

### Suspend User

```
POST /admin/users/{id}/suspend
```

**Request:**
```json
{
  "reason": "Terms of service violation",
  "duration_days": 30
}
```

### Delete User

```
DELETE /admin/users/{id}
```

---

## Video Asset Management

### Get All Video Assets

```
GET /admin/video-assets
```

**Query Parameters:**
- `content_id` - Filter by content
- `rendition_key` - Filter by quality: 1080p, 720p, 480p, 360p
- `status` - Filter: ready, processing, failed
- `page`, `per_page` - Pagination

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "content_item_id": 123,
      "rendition_key": "1080p",
      "hls_manifest_key": "public/videos/movie-1080p.mp4",
      "file_size_mb": 2500.5,
      "resolution": "1920x1080",
      "bitrate": 5000,
      "status": "ready",
      "content_item": {
        "id": 123,
        "title": "Action Movie",
        "type": "movie"
      },
      "created_at": "2025-11-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 450
  }
}
```

### Get Video Asset Details

```
GET /admin/video-assets/{id}
```

### Update Video Asset

```
PUT /admin/video-assets/{id}
```

**Request:**
```json
{
  "rendition_key": "720p",
  "resolution": "1280x720",
  "bitrate": 3000,
  "status": "ready"
}
```

### Delete Video Asset

```
DELETE /admin/video-assets/{id}
```

---

## Analytics

### Dashboard Stats

Get overview statistics for admin dashboard.

#### Endpoint
```
GET /admin/analytics/dashboard
```

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| period | string | No | Time period: today, week, month, year (default: month) |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "period": "month",
    "users": {
      "total": 12500,
      "new": 450,
      "active": 8900,
      "growth_percentage": 3.6
    },
    "subscriptions": {
      "total": 8900,
      "active": 7200,
      "trialing": 450,
      "cancelled": 1250,
      "revenue": 107880.00,
      "revenue_growth_percentage": 5.2
    },
    "content": {
      "total": 450,
      "movies": 300,
      "shows": 100,
      "episodes": 50,
      "published_this_period": 15
    },
    "engagement": {
      "total_views": 125000,
      "total_watch_time_hours": 250000,
      "avg_watch_time_minutes": 45,
      "completion_rate": 0.68
    },
    "top_content": [
      {
        "id": 1,
        "title": "Popular Movie",
        "type": "movie",
        "views": 5000,
        "watch_time_hours": 10000
      }
    ]
  }
}
```

### Content Analytics

```
GET /admin/analytics/content/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "content": {
      "id": 1,
      "title": "Action Movie"
    },
    "views": {
      "total": 5000,
      "unique_users": 4200,
      "by_day": [
        { "date": "2025-11-16", "views": 250 }
      ]
    },
    "watch_time": {
      "total_hours": 10000,
      "average_minutes": 42,
      "completion_rate": 0.75
    },
    "demographics": {
      "by_country": [
        { "country": "US", "views": 2000 },
        { "country": "UK", "views": 800 }
      ],
      "by_device": [
        { "device": "desktop", "views": 2500 },
        { "device": "mobile", "views": 1800 },
        { "device": "tv", "views": 700 }
      ]
    }
  }
}
```

### Revenue Analytics

```
GET /admin/analytics/revenue
```

**Query Parameters:**
- `start_date` - Start date (YYYY-MM-DD)
- `end_date` - End date (YYYY-MM-DD)
- `group_by` - Group by: day, week, month

**Response:**
```json
{
  "success": true,
  "data": {
    "total_revenue": 107880.00,
    "by_period": [
      {
        "period": "2025-11",
        "revenue": 35960.00,
        "subscriptions": 2400,
        "new_subscriptions": 150,
        "churned_subscriptions": 50
      }
    ],
    "by_plan": [
      {
        "plan": "Standard",
        "revenue": 64740.00,
        "subscriptions": 4320
      },
      {
        "plan": "Premium",
        "revenue": 43140.00,
        "subscriptions": 2160
      }
    ]
  }
}
```

---

## Settings

### Get System Settings

```
GET /admin/settings
```

### Update Settings

```
PUT /admin/settings
```

**Request:**
```json
{
  "site_name": "My Streaming Platform",
  "site_logo": "https://domain.com/logo.png",
  "maintenance_mode": false,
  "allow_registration": true,
  "require_email_verification": true,
  "max_profiles_per_user": 5,
  "trial_days": 14,
  "video_quality_options": ["360p", "480p", "720p", "1080p"],
  "default_video_quality": "720p",
  "enable_downloads": true,
  "max_concurrent_streams": 2
}
```

---

## Frontend Admin Panel Example

```javascript
// Admin Dashboard Component
function AdminDashboard() {
  const [stats, setStats] = useState(null);
  const [period, setPeriod] = useState('month');
  
  useEffect(() => {
    loadDashboardStats(period);
  }, [period]);
  
  const loadDashboardStats = async (period) => {
    const token = localStorage.getItem('admin_token');
    
    try {
      const response = await fetch(
        `https://your-domain.com/admin/analytics/dashboard?period=${period}`,
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
          }
        }
      );
      
      const data = await response.json();
      setStats(data.data);
    } catch (error) {
      console.error('Failed to load stats:', error);
    }
  };
  
  if (!stats) return <LoadingSpinner />;
  
  return (
    <div className="admin-dashboard">
      <header>
        <h1>Dashboard</h1>
        <select value={period} onChange={(e) => setPeriod(e.target.value)}>
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="year">This Year</option>
        </select>
      </header>
      
      <div className="stats-grid">
        <StatCard
          title="Total Users"
          value={stats.users.total}
          change={`+${stats.users.new} new`}
          trend={stats.users.growth_percentage}
        />
        
        <StatCard
          title="Active Subscriptions"
          value={stats.subscriptions.active}
          change={`$${stats.subscriptions.revenue.toLocaleString()} revenue`}
          trend={stats.subscriptions.revenue_growth_percentage}
        />
        
        <StatCard
          title="Total Content"
          value={stats.content.total}
          change={`+${stats.content.published_this_period} this ${period}`}
        />
        
        <StatCard
          title="Total Views"
          value={stats.engagement.total_views.toLocaleString()}
          change={`${stats.engagement.avg_watch_time_minutes} min avg`}
        />
      </div>
      
      <div className="charts">
        <RevenueChart data={stats.subscriptions} />
        <TopContentList content={stats.top_content} />
      </div>
    </div>
  );
}
```

---

**Back to:** [API Overview ←](./API_OVERVIEW.md)
