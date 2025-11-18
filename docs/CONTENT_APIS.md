# Content APIs

Browse, search, and discover entertainment content.

---

## Table of Contents

1. [Get All Content](#get-all-content)
2. [Get Content Details](#get-content-details)
3. [Search Content](#search-content)
4. [Get Categories](#get-categories)
5. [Get Content by Category](#get-content-by-category)
6. [Get Trending Content](#get-trending-content)
7. [Get New Releases](#get-new-releases)
8. [Get Shows](#get-shows)
9. [Get Show Details](#get-show-details)
10. [Get Episodes](#get-episodes)

---

## Get All Content

Retrieve paginated list of all published content.

### Endpoint
```
GET /api/content
```

### Headers
```
Accept: application/json
```

### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| per_page | integer | No | 15 | Items per page (max: 100) |
| page | integer | No | 1 | Page number |
| type | string | No | - | Filter by type: movie, show, skit, afrimation, real_estate |
| category_id | integer | No | - | Filter by category ID |
| visibility | string | No | public | Filter by visibility: public, premium, private |
| sort | string | No | created_at | Sort field: created_at, release_year, title, views_count |
| order | string | No | desc | Sort order: asc, desc |

### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "title": "The Great Adventure",
      "description": "An epic journey through uncharted territories...",
      "type": "movie",
      "visibility": "public",
      "maturity_rating": "pg13",
      "release_year": 2024,
      "duration_seconds": 7200,
      "views_count": 15420,
      "poster_url": "/storage/posters/abc123.jpg",
      "backdrop_url": "/storage/backdrops/abc123.jpg",
      "thumbnail_url": "/storage/thumbnails/abc123.jpg",
      "trailer_url": "/storage/trailers/abc123.mp4",
      "published_at": "2025-11-01T00:00:00.000000Z",
      "category": {
        "id": 1,
        "title": "Action",
        "slug": "action",
        "description": "High-energy action content"
      },
      "provider": {
        "id": 1,
        "display_name": "UBIQ Originals",
        "logo_url": "/storage/providers/ubiq.png"
      },
      "created_at": "2025-11-01T10:00:00.000000Z",
      "updated_at": "2025-11-15T14:30:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  },
  "links": {
    "first": "https://your-domain.com/api/content?page=1",
    "last": "https://your-domain.com/api/content?page=10",
    "prev": null,
    "next": "https://your-domain.com/api/content?page=2"
  }
}
```

### Frontend Example

```javascript
async function getContent(filters = {}) {
  const params = new URLSearchParams({
    per_page: filters.perPage || 15,
    page: filters.page || 1,
    ...(filters.type && { type: filters.type }),
    ...(filters.categoryId && { category_id: filters.categoryId }),
    ...(filters.sort && { sort: filters.sort }),
    ...(filters.order && { order: filters.order })
  });
  
  try {
    const response = await fetch(`https://your-domain.com/api/content?${params}`, {
      headers: {
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Failed to fetch content:', error);
    throw error;
  }
}

// Usage examples
getContent({ type: 'movie', perPage: 20 });
getContent({ categoryId: 1, sort: 'views_count', order: 'desc' });
```

---

## Get Content Details

Retrieve detailed information about specific content.

### Endpoint
```
GET /api/content/{id}
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Content item ID |

### Headers
```
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "data": {
    "id": 1,
    "title": "The Great Adventure",
    "description": "An epic journey through uncharted territories. Follow our heroes as they discover ancient secrets and face incredible challenges...",
    "type": "movie",
    "visibility": "public",
    "maturity_rating": "pg13",
    "release_year": 2024,
    "duration_seconds": 7200,
    "views_count": 15420,
    "poster_url": "/storage/posters/abc123.jpg",
    "backdrop_url": "/storage/backdrops/abc123.jpg",
    "thumbnail_url": "/storage/thumbnails/abc123.jpg",
    "trailer_url": "/storage/trailers/abc123.mp4",
    "published_at": "2025-11-01T00:00:00.000000Z",
    "metadata": {
      "director": "John Smith",
      "cast": ["Actor A", "Actor B", "Actor C"],
      "genres": ["Action", "Adventure", "Drama"],
      "language": "English",
      "subtitles": ["English", "Spanish", "French"]
    },
    "category": {
      "id": 1,
      "title": "Action",
      "slug": "action",
      "description": "High-energy action content",
      "parent_id": null
    },
    "provider": {
      "id": 1,
      "display_name": "UBIQ Originals",
      "logo_url": "/storage/providers/ubiq.png",
      "description": "Original content from UBIQ Entertainment"
    },
    "video_assets": [
      {
        "id": 1,
        "rendition_key": "1080p",
        "resolution": "1920x1080",
        "file_size_mb": 4500.50,
        "bitrate": 5000,
        "status": "ready"
      },
      {
        "id": 2,
        "rendition_key": "720p",
        "resolution": "1280x720",
        "file_size_mb": 2100.25,
        "bitrate": 2500,
        "status": "ready"
      }
    ],
    "show": null,
    "created_at": "2025-11-01T10:00:00.000000Z",
    "updated_at": "2025-11-15T14:30:00.000000Z"
  }
}
```

### Error Response (404 Not Found)

```json
{
  "success": false,
  "message": "Content not found"
}
```

### Frontend Example

```javascript
async function getContentDetails(contentId) {
  try {
    const response = await fetch(`https://your-domain.com/api/content/${contentId}`, {
      headers: {
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) {
      throw new Error('Content not found');
    }
    
    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Failed to fetch content details:', error);
    throw error;
  }
}

// Usage
getContentDetails(1).then(content => {
  console.log(content.title);
  console.log(`Duration: ${content.duration_seconds / 60} minutes`);
});
```

---

## Search Content

Search content by title, description, or metadata.

### Endpoint
```
GET /api/content/search
```

### Headers
```
Accept: application/json
```

### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| q | string | Yes | - | Search query (min: 2 chars) |
| per_page | integer | No | 15 | Items per page |
| page | integer | No | 1 | Page number |
| type | string | No | - | Filter by content type |
| category_id | integer | No | - | Filter by category |
| maturity_rating | string | No | - | Filter by rating: all, pg, pg13, r, adult |

### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 5,
      "title": "Adventure Quest",
      "description": "An exciting adventure story...",
      "type": "movie",
      "visibility": "public",
      "poster_url": "/storage/posters/xyz789.jpg",
      "thumbnail_url": "/storage/thumbnails/xyz789.jpg",
      "duration_seconds": 5400,
      "release_year": 2024,
      "category": {
        "id": 1,
        "title": "Action"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 5,
    "per_page": 15
  },
  "query": "adventure"
}
```

### Frontend Example

```javascript
async function searchContent(searchQuery, filters = {}) {
  const params = new URLSearchParams({
    q: searchQuery,
    per_page: filters.perPage || 15,
    page: filters.page || 1,
    ...(filters.type && { type: filters.type }),
    ...(filters.categoryId && { category_id: filters.categoryId })
  });
  
  try {
    const response = await fetch(`https://your-domain.com/api/content/search?${params}`, {
      headers: {
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Search failed:', error);
    throw error;
  }
}

// Usage
searchContent('adventure', { type: 'movie' });
```

---

## Get Categories

Retrieve all content categories.

### Endpoint
```
GET /api/categories
```

### Headers
```
Accept: application/json
```

### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| with_content_count | boolean | No | false | Include content count per category |
| parent_only | boolean | No | false | Return only parent categories |

### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "title": "Action",
      "slug": "action",
      "description": "High-energy action content",
      "icon": "💥",
      "sort_order": 1,
      "parent_id": null,
      "content_count": 45,
      "children": [
        {
          "id": 10,
          "title": "Martial Arts",
          "slug": "martial-arts",
          "parent_id": 1,
          "content_count": 12
        }
      ],
      "created_at": "2025-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "title": "Comedy",
      "slug": "comedy",
      "description": "Laugh-out-loud comedies",
      "icon": "😂",
      "sort_order": 2,
      "parent_id": null,
      "content_count": 38,
      "children": [],
      "created_at": "2025-01-01T00:00:00.000000Z"
    }
  ]
}
```

### Frontend Example

```javascript
async function getCategories(options = {}) {
  const params = new URLSearchParams({
    ...(options.withContentCount && { with_content_count: true }),
    ...(options.parentOnly && { parent_only: true })
  });
  
  try {
    const response = await fetch(`https://your-domain.com/api/categories?${params}`, {
      headers: {
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Failed to fetch categories:', error);
    throw error;
  }
}

// Usage
getCategories({ withContentCount: true });
```

---

## Get Content by Category

Get all content in a specific category.

### Endpoint
```
GET /api/categories/{id}/content
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Category ID |

### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| per_page | integer | No | 15 | Items per page |
| page | integer | No | 1 | Page number |
| include_children | boolean | No | false | Include content from subcategories |

### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "title": "Action Movie Title",
      "type": "movie",
      "poster_url": "/storage/posters/abc.jpg",
      "category": {
        "id": 1,
        "title": "Action"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 45,
    "per_page": 15
  }
}
```

---

## Get Trending Content

Retrieve trending content based on recent views.

### Endpoint
```
GET /api/content/trending
```

### Headers
```
Accept: application/json
```

### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| limit | integer | No | 20 | Number of items to return (max: 50) |
| days | integer | No | 7 | Calculate trending from last X days |

### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 15,
      "title": "Hot New Movie",
      "type": "movie",
      "poster_url": "/storage/posters/trending.jpg",
      "thumbnail_url": "/storage/thumbnails/trending.jpg",
      "views_count": 8540,
      "recent_views": 2340,
      "trending_score": 95,
      "category": {
        "id": 1,
        "title": "Action"
      }
    }
  ]
}
```

### Frontend Example

```javascript
async function getTrendingContent(limit = 20) {
  try {
    const response = await fetch(`https://your-domain.com/api/content/trending?limit=${limit}`, {
      headers: {
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Failed to fetch trending content:', error);
    throw error;
  }
}
```

---

## Get New Releases

Retrieve recently published content.

### Endpoint
```
GET /api/content/new-releases
```

### Headers
```
Accept: application/json
```

### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| limit | integer | No | 20 | Number of items (max: 50) |
| days | integer | No | 30 | Published within last X days |
| type | string | No | - | Filter by content type |

### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 25,
      "title": "Brand New Content",
      "type": "movie",
      "poster_url": "/storage/posters/new.jpg",
      "published_at": "2025-11-15T00:00:00.000000Z",
      "release_year": 2025,
      "is_new": true
    }
  ]
}
```

---

## Get Shows

Retrieve all TV shows/series.

### Endpoint
```
GET /api/shows
```

### Headers
```
Accept: application/json
```

### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| per_page | integer | No | 15 | Items per page |
| page | integer | No | 1 | Page number |
| status | string | No | - | Filter: ongoing, completed, upcoming |

### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "title": "Amazing Series",
      "description": "A captivating TV series...",
      "status": "ongoing",
      "total_seasons": 3,
      "total_episodes": 36,
      "content_item": {
        "id": 10,
        "poster_url": "/storage/posters/series.jpg",
        "backdrop_url": "/storage/backdrops/series.jpg"
      },
      "seasons": [
        {
          "id": 1,
          "season_number": 1,
          "title": "Season 1",
          "episode_count": 12
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 25,
    "per_page": 15
  }
}
```

---

## Get Show Details

Get detailed information about a specific show including all seasons and episodes.

### Endpoint
```
GET /api/shows/{id}
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Show ID |

### Success Response (200 OK)

```json
{
  "data": {
    "id": 1,
    "title": "Amazing Series",
    "description": "A captivating TV series spanning multiple seasons...",
    "status": "ongoing",
    "total_seasons": 3,
    "total_episodes": 36,
    "content_item": {
      "id": 10,
      "poster_url": "/storage/posters/series.jpg",
      "backdrop_url": "/storage/backdrops/series.jpg",
      "trailer_url": "/storage/trailers/series.mp4",
      "category": {
        "id": 3,
        "title": "Drama"
      }
    },
    "seasons": [
      {
        "id": 1,
        "season_number": 1,
        "title": "Season 1",
        "description": "The beginning of the journey",
        "episode_count": 12,
        "episodes": [
          {
            "id": 1,
            "episode_number": 1,
            "title": "Pilot",
            "description": "The series premiere",
            "duration_seconds": 2400,
            "air_date": "2024-01-01",
            "content_item": {
              "id": 11,
              "thumbnail_url": "/storage/thumbnails/ep1.jpg"
            }
          }
        ]
      }
    ],
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2025-11-15T00:00:00.000000Z"
  }
}
```

---

## Get Episodes

Get episodes for a specific season.

### Endpoint
```
GET /api/shows/{showId}/seasons/{seasonId}/episodes
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| showId | integer | Yes | Show ID |
| seasonId | integer | Yes | Season ID |

### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "episode_number": 1,
      "title": "Pilot Episode",
      "description": "The beginning of an epic story...",
      "duration_seconds": 2400,
      "air_date": "2024-01-01",
      "content_item": {
        "id": 11,
        "thumbnail_url": "/storage/thumbnails/ep1.jpg",
        "visibility": "public"
      },
      "video_assets": [
        {
          "id": 10,
          "rendition_key": "1080p",
          "status": "ready"
        }
      ]
    }
  ]
}
```

---

**Next:** [Playback APIs →](./PLAYBACK_APIS.md)
