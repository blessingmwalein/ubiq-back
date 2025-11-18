# Profile & Account Management APIs

User profiles, favorites, watchlists, and account settings.

---

## Table of Contents

1. [Get User Profiles](#get-user-profiles)
2. [Create Profile](#create-profile)
3. [Update Profile](#update-profile)
4. [Delete Profile](#delete-profile)
5. [Add to Watchlist](#add-to-watchlist)
6. [Remove from Watchlist](#remove-from-watchlist)
7. [Get Watchlist](#get-watchlist)
8. [Add to Favorites](#add-to-favorites)
9. [Get Favorites](#get-favorites)
10. [Update User Settings](#update-user-settings)

---

## Get User Profiles

Retrieve all profiles for the authenticated user.

### Endpoint
```
GET /api/profiles
```

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John's Profile",
      "avatar": "/storage/avatars/profile1.png",
      "is_kids": false,
      "is_default": true,
      "language": "en",
      "autoplay_next": true,
      "subtitle_preference": "en",
      "created_at": "2025-11-01T00:00:00.000000Z",
      "updated_at": "2025-11-15T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Kids",
      "avatar": "/storage/avatars/kids.png",
      "is_kids": true,
      "is_default": false,
      "language": "en",
      "autoplay_next": false,
      "subtitle_preference": null,
      "created_at": "2025-11-02T00:00:00.000000Z",
      "updated_at": "2025-11-02T00:00:00.000000Z"
    }
  ]
}
```

### Frontend Example

```javascript
async function getUserProfiles() {
  const token = localStorage.getItem('auth_token');
  
  try {
    const response = await fetch('https://your-domain.com/api/profiles', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      return data.data;
    }
  } catch (error) {
    console.error('Failed to fetch profiles:', error);
    throw error;
  }
}

// Usage - Profile selection screen
getUserProfiles().then(profiles => {
  displayProfileSelection(profiles);
});
```

---

## Create Profile

Create a new profile for the user account.

### Endpoint
```
POST /api/profiles
```

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
  "name": "Sarah's Profile",
  "avatar": "/storage/avatars/avatar3.png",
  "is_kids": false,
  "is_default": false,
  "language": "en",
  "autoplay_next": true,
  "subtitle_preference": "en"
}
```

### Request Parameters

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | Profile name (max: 50 chars) |
| avatar | string | No | Avatar image URL |
| is_kids | boolean | No | Kids profile (filters adult content) |
| is_default | boolean | No | Set as default profile |
| language | string | No | Preferred language (default: 'en') |
| autoplay_next | boolean | No | Autoplay next episode (default: true) |
| subtitle_preference | string | No | Preferred subtitle language |

### Success Response (201 Created)

```json
{
  "success": true,
  "data": {
    "id": 3,
    "name": "Sarah's Profile",
    "avatar": "/storage/avatars/avatar3.png",
    "is_kids": false,
    "is_default": false,
    "language": "en",
    "autoplay_next": true,
    "subtitle_preference": "en",
    "created_at": "2025-11-16T18:00:00.000000Z",
    "updated_at": "2025-11-16T18:00:00.000000Z"
  },
  "message": "Profile created successfully"
}
```

### Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

---

## Update Profile

Update an existing profile.

### Endpoint
```
PUT /api/profiles/{id}
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Profile ID |

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
  "name": "Updated Name",
  "avatar": "/storage/avatars/new-avatar.png",
  "autoplay_next": false
}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Updated Name",
    "avatar": "/storage/avatars/new-avatar.png",
    "is_kids": false,
    "is_default": true,
    "autoplay_next": false,
    "updated_at": "2025-11-16T18:05:00.000000Z"
  },
  "message": "Profile updated successfully"
}
```

---

## Delete Profile

Delete a profile (cannot delete default profile).

### Endpoint
```
DELETE /api/profiles/{id}
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Profile ID |

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Profile deleted successfully"
}
```

### Error Response (403 Forbidden)

```json
{
  "success": false,
  "message": "Cannot delete default profile"
}
```

---

## Add to Watchlist

Add content to a profile's watchlist.

### Endpoint
```
POST /api/profiles/{profileId}/watchlist
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| profileId | integer | Yes | Profile ID |

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
  "content_id": 123
}
```

### Success Response (201 Created)

```json
{
  "success": true,
  "data": {
    "id": 456,
    "profile_id": 1,
    "content_id": 123,
    "content": {
      "id": 123,
      "title": "The Great Adventure",
      "poster_url": "/storage/posters/abc123.jpg",
      "type": "movie"
    },
    "created_at": "2025-11-16T18:10:00.000000Z"
  },
  "message": "Added to watchlist"
}
```

### Frontend Example

```javascript
async function addToWatchlist(profileId, contentId) {
  const token = localStorage.getItem('auth_token');
  
  try {
    const response = await fetch(
      `https://your-domain.com/api/profiles/${profileId}/watchlist`,
      {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ content_id: contentId })
      }
    );
    
    const data = await response.json();
    
    if (data.success) {
      showNotification('Added to watchlist');
      return data.data;
    }
  } catch (error) {
    console.error('Failed to add to watchlist:', error);
  }
}

// Usage - Watchlist button click
addToWatchlistButton.addEventListener('click', () => {
  addToWatchlist(currentProfileId, contentId);
});
```

---

## Remove from Watchlist

Remove content from watchlist.

### Endpoint
```
DELETE /api/profiles/{profileId}/watchlist/{contentId}
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| profileId | integer | Yes | Profile ID |
| contentId | integer | Yes | Content ID |

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Removed from watchlist"
}
```

---

## Get Watchlist

Retrieve all content in a profile's watchlist.

### Endpoint
```
GET /api/profiles/{profileId}/watchlist
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| profileId | integer | Yes | Profile ID |

### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| per_page | integer | No | 20 | Items per page |
| page | integer | No | 1 | Page number |

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "data": [
    {
      "id": 456,
      "content": {
        "id": 123,
        "title": "The Great Adventure",
        "description": "An epic journey...",
        "type": "movie",
        "poster_url": "/storage/posters/abc123.jpg",
        "thumbnail_url": "/storage/thumbnails/abc123.jpg",
        "duration_seconds": 7200,
        "category": {
          "id": 1,
          "title": "Action"
        }
      },
      "added_at": "2025-11-16T18:10:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 15,
    "per_page": 20
  }
}
```

### Frontend Example

```javascript
async function getWatchlist(profileId, page = 1) {
  const token = localStorage.getItem('auth_token');
  
  try {
    const response = await fetch(
      `https://your-domain.com/api/profiles/${profileId}/watchlist?page=${page}`,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      }
    );
    
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Failed to fetch watchlist:', error);
    throw error;
  }
}

// Display watchlist
getWatchlist(profileId).then(watchlist => {
  displayWatchlist(watchlist.data);
});
```

---

## Add to Favorites

Mark content as favorite.

### Endpoint
```
POST /api/profiles/{profileId}/favorites
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| profileId | integer | Yes | Profile ID |

### Request Body

```json
{
  "content_id": 123
}
```

### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Added to favorites"
}
```

---

## Get Favorites

Get all favorite content for a profile.

### Endpoint
```
GET /api/profiles/{profileId}/favorites
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| profileId | integer | Yes | Profile ID |

### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| per_page | integer | No | 20 | Items per page |
| page | integer | No | 1 | Page number |

### Success Response (200 OK)

```json
{
  "data": [
    {
      "content": {
        "id": 123,
        "title": "Favorite Movie",
        "poster_url": "/storage/posters/fav.jpg",
        "type": "movie"
      },
      "favorited_at": "2025-11-10T12:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 8,
    "per_page": 20
  }
}
```

---

## Update User Settings

Update account settings.

### Endpoint
```
PUT /api/user/settings
```

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
  "email_notifications": true,
  "push_notifications": false,
  "autoplay_previews": true,
  "data_saver_mode": false,
  "language": "en",
  "subtitle_size": "medium",
  "playback_quality": "auto"
}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "email_notifications": true,
    "push_notifications": false,
    "autoplay_previews": true,
    "data_saver_mode": false,
    "language": "en",
    "subtitle_size": "medium",
    "playback_quality": "auto",
    "updated_at": "2025-11-16T18:20:00.000000Z"
  },
  "message": "Settings updated successfully"
}
```

---

## Get User Account Info

Get complete account information.

### Endpoint
```
GET /api/user/account
```

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "email_verified_at": "2025-11-01T10:00:00.000000Z",
      "created_at": "2025-11-01T00:00:00.000000Z"
    },
    "subscription": {
      "status": "active",
      "plan": "premium",
      "start_date": "2025-11-01T00:00:00.000000Z",
      "end_date": "2025-12-01T00:00:00.000000Z",
      "auto_renew": true
    },
    "profiles_count": 3,
    "devices_count": 5,
    "watch_time_minutes": 1240
  }
}
```

---

**Next:** [Subscription APIs →](./SUBSCRIPTION_APIS.md)
