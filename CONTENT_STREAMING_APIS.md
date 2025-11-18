# Content & Streaming APIs Documentation

## Overview
This document provides comprehensive documentation for the content discovery, streaming, and playback APIs.

---

## Content Discovery APIs

### 1. Get Content Catalog
**Endpoint:** `GET /api/content`  
**Authentication:** Not required  
**Query Parameters:**
- `per_page` (optional): Items per page (default: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "title": "Content Title",
      "description": "Content description",
      "type": "movie|series",
      "maturity_rating": "pg13",
      "duration_minutes": 120,
      "published_at": "2024-01-01T00:00:00Z",
      "category": {
        "id": 1,
        "name": "Action",
        "slug": "action"
      }
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

### 2. Search Content
**Endpoint:** `GET /api/content/search?q=query`  
**Authentication:** Not required  
**Query Parameters:**
- `q` (required): Search query
- `per_page` (optional): Items per page

**Example:** `GET /api/content/search?q=avengers&per_page=20`

---

### 3. Get Trending Content
**Endpoint:** `GET /api/content/trending`  
**Authentication:** Not required  
**Query Parameters:**
- `limit` (optional): Number of items (default: 20)

**Response:**
Returns content based on views from the last 7 days, ordered by popularity.

---

### 4. Get Featured Content
**Endpoint:** `GET /api/content/featured`  
**Authentication:** Not required  

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Featured Movie",
      "featured": true,
      "featured_order": 1,
      "thumbnail_url": "https://...",
      "backdrop_url": "https://..."
    }
  ]
}
```

---

### 5. Get Content by Type
**Endpoint:** `GET /api/content/type/{type}`  
**Authentication:** Not required  
**Path Parameters:**
- `type`: `movie` or `series`

**Query Parameters:**
- `per_page` (optional): Items per page

**Example:** `GET /api/content/type/movie?per_page=20`

---

### 6. Get Content by Genre
**Endpoint:** `GET /api/content/genre/{slug}`  
**Authentication:** Not required  
**Path Parameters:**
- `slug`: Category slug (e.g., `action`, `comedy`, `drama`)

**Example:** `GET /api/content/genre/action`

---

### 7. Get Content by Category
**Endpoint:** `GET /api/content/category/{categoryId}`  
**Authentication:** Not required  

---

### 8. Get Most Viewed Content
**Endpoint:** `GET /api/content/most-viewed`  
**Authentication:** Not required  
**Query Parameters:**
- `limit` (optional): Number of items (default: 10)

---

### 9. Get Recently Added Content
**Endpoint:** `GET /api/content/recently-added`  
**Authentication:** Not required  
**Query Parameters:**
- `limit` (optional): Number of items (default: 20)

---

### 10. Get Content Details
**Endpoint:** `GET /api/content/{id}`  
**Authentication:** Not required  
**Path Parameters:**
- `id`: Content item ID

**Response:**
```json
{
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "title": "Movie Title",
    "description": "Full description",
    "type": "movie",
    "maturity_rating": "pg13",
    "duration_minutes": 120,
    "release_date": "2024-01-01",
    "category": {...},
    "video_assets": [
      {
        "id": 1,
        "quality": "1080p",
        "type": "hls",
        "file_path": "content/movie/manifest.m3u8"
      }
    ]
  }
}
```

---

### 11. Get Similar Content
**Endpoint:** `GET /api/content/{id}/similar`  
**Authentication:** Not required  
**Query Parameters:**
- `limit` (optional): Number of items (default: 10)

**Response:** Returns similar content based on category and type.

---

### 12. Get Personalized Recommendations
**Endpoint:** `GET /api/content/recommendations`  
**Authentication:** Required (Bearer token)  
**Query Parameters:**
- `limit` (optional): Number of items (default: 20)

**Response:** Returns personalized recommendations based on user's watch history and preferences.

---

### 13. Get All Categories
**Endpoint:** `GET /api/categories`  
**Authentication:** Not required  

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Action",
      "slug": "action",
      "description": "Action movies and series"
    }
  ]
}
```

---

## Shows & Series APIs

### 14. Get All Shows
**Endpoint:** `GET /api/shows`  
**Authentication:** Not required  
**Query Parameters:**
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "content_item_id": 10,
      "total_seasons": 5,
      "total_episodes": 60,
      "status": "ongoing",
      "contentItem": {
        "id": 10,
        "title": "Series Title",
        "description": "Series description",
        "category": {...}
      }
    }
  ],
  "meta": {...}
}
```

---

### 15. Get Show Details
**Endpoint:** `GET /api/shows/{uuid}`  
**Authentication:** Not required  
**Path Parameters:**
- `uuid`: Show UUID

**Response:** Returns show details with all seasons and episodes nested.
```json
{
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "total_seasons": 3,
    "total_episodes": 30,
    "status": "ongoing",
    "seasons": [
      {
        "id": 1,
        "season_number": 1,
        "episode_count": 10,
        "episodes": [
          {
            "id": 1,
            "episode_number": 1,
            "title": "Pilot",
            "duration_minutes": 45
          }
        ]
      }
    ]
  }
}
```

---

### 16. Get Show Seasons
**Endpoint:** `GET /api/shows/{uuid}/seasons`  
**Authentication:** Not required  

**Response:** Returns all seasons with episodes for a show.

---

### 17. Get Specific Season
**Endpoint:** `GET /api/shows/{showUuid}/seasons/{seasonNumber}`  
**Authentication:** Not required  
**Path Parameters:**
- `showUuid`: Show UUID
- `seasonNumber`: Season number (1, 2, 3, etc.)

**Example:** `GET /api/shows/550e8400.../seasons/2`

---

### 18. Get Episode Details
**Endpoint:** `GET /api/episodes/{uuid}`  
**Authentication:** Not required  
**Path Parameters:**
- `uuid`: Episode UUID

**Response:**
```json
{
  "data": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "episode_number": 1,
    "title": "Episode Title",
    "description": "Episode description",
    "duration_minutes": 45,
    "air_date": "2024-01-01",
    "season": {...},
    "contentItem": {...},
    "videoAssets": [
      {
        "id": 1,
        "quality": "1080p",
        "type": "hls",
        "file_path": "episodes/..."
      }
    ]
  }
}
```

---

### 19. Get Next Episode
**Endpoint:** `GET /api/episodes/{uuid}/next`  
**Authentication:** Not required  
**Path Parameters:**
- `uuid`: Current episode UUID

**Response:** Returns the next episode in the same season, or the first episode of the next season if current season is complete. Returns null if series is complete.

**Example Response:**
```json
{
  "data": {
    "id": 2,
    "episode_number": 2,
    "title": "Next Episode",
    "season": {...}
  }
}
```

---

### 20. Search Shows
**Endpoint:** `GET /api/shows/search?q=query`  
**Authentication:** Not required  
**Query Parameters:**
- `q` (required): Search query
- `per_page` (optional): Items per page

**Example:** `GET /api/shows/search?q=breaking+bad`

---

## Playback & Streaming APIs

### 21. Request Playback Token
**Endpoint:** `POST /api/playback/token`  
**Authentication:** Required (Bearer token)  
**Request Body:**
```json
{
  "profile_id": 1,
  "content_item_id": 10
}
```

**Response:**
```json
{
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_at": "2024-01-01T06:00:00Z"
  }
}
```

**Note:** Token is valid for 6 hours for streaming access.

---

### 22. Get HLS Manifest with Quality Options
**Endpoint:** `GET /api/playback/hls/{token}`  
**Authentication:** Required (Bearer token)  
**Path Parameters:**
- `token`: Playback token from step 21

**Response:**
```json
{
  "data": {
    "content_id": 10,
    "title": "Content Title",
    "variants": [
      {
        "quality": "1080p",
        "bandwidth": 5000000,
        "resolution": "1920x1080",
        "url": "https://cdn.../manifest-1080p.m3u8?signature=...",
        "codec": "H.264"
      },
      {
        "quality": "720p",
        "bandwidth": 3000000,
        "resolution": "1280x720",
        "url": "https://cdn.../manifest-720p.m3u8?signature=...",
        "codec": "H.264"
      },
      {
        "quality": "480p",
        "bandwidth": 1500000,
        "resolution": "854x480",
        "url": "https://cdn.../manifest-480p.m3u8?signature=...",
        "codec": "H.264"
      },
      {
        "quality": "360p",
        "bandwidth": 800000,
        "resolution": "640x360",
        "url": "https://cdn.../manifest-360p.m3u8?signature=...",
        "codec": "H.264"
      }
    ],
    "resume_position": 1250,
    "duration": 7200,
    "token": "eyJhbGci...",
    "expires_at": "2024-01-01T06:00:00Z"
  }
}
```

**Usage:**
1. Request playback token
2. Call this endpoint with the token
3. Use HLS player (e.g., Video.js, HLS.js) to play the manifest URL
4. Player will automatically handle quality switching based on bandwidth

---

### 23. Get Quality Options
**Endpoint:** `GET /api/playback/qualities/{contentId}`  
**Authentication:** Required (Bearer token)  
**Path Parameters:**
- `contentId`: Content item ID

**Response:**
```json
{
  "data": [
    {
      "quality": "1080p",
      "bandwidth": 5000000,
      "resolution": "1920x1080",
      "file_size": 2147483648,
      "codec": "H.264"
    },
    {
      "quality": "720p",
      "bandwidth": 3000000,
      "resolution": "1280x720",
      "file_size": 1073741824,
      "codec": "H.264"
    }
  ]
}
```

---

### 24. Get Subtitle & Audio Tracks
**Endpoint:** `GET /api/playback/tracks/{contentId}`  
**Authentication:** Required (Bearer token)  
**Path Parameters:**
- `contentId`: Content item ID

**Response:**
```json
{
  "data": {
    "subtitles": [
      {
        "id": 1,
        "language": "en",
        "label": "English",
        "url": "https://cdn.../subtitles-en.vtt?signature=...",
        "format": "vtt"
      },
      {
        "id": 2,
        "language": "es",
        "label": "Spanish",
        "url": "https://cdn.../subtitles-es.vtt?signature=...",
        "format": "vtt"
      }
    ],
    "audio": [
      {
        "id": 1,
        "language": "en",
        "label": "English",
        "codec": "AAC"
      }
    ]
  }
}
```

---

### 25. Update Watch Progress
**Endpoint:** `POST /api/playback/progress`  
**Authentication:** Required (Bearer token)  
**Request Body:**
```json
{
  "token": "playback_token_here",
  "position": 1250,
  "duration": 7200
}
```

**Response:**
```json
{
  "message": "Progress updated successfully"
}
```

**Note:** Call this endpoint periodically (e.g., every 30 seconds) during playback to track watch progress.

---

### 26. Get Resume Position
**Endpoint:** `GET /api/playback/resume/{contentId}/{profileId}`  
**Authentication:** Required (Bearer token)  
**Path Parameters:**
- `contentId`: Content item ID
- `profileId`: Profile ID

**Response:**
```json
{
  "data": {
    "position": 1250
  }
}
```

**Note:** Position in seconds where user left off. Use this to resume playback.

---

### 27. Validate Playback Token
**Endpoint:** `GET /api/playback/validate/{token}`  
**Authentication:** Required (Bearer token)  
**Path Parameters:**
- `token`: Playback token

**Response:**
```json
{
  "data": {
    "valid": true,
    "expires_at": "2024-01-01T06:00:00Z"
  }
}
```

---

### 28. Report Playback Error
**Endpoint:** `POST /api/playback/error`  
**Authentication:** Required (Bearer token)  
**Request Body:**
```json
{
  "token": "playback_token_here",
  "error_code": "NETWORK_ERROR",
  "error_message": "Failed to load video segment"
}
```

**Response:**
```json
{
  "message": "Error reported successfully"
}
```

**Common Error Codes:**
- `NETWORK_ERROR`: Network connectivity issues
- `DECODE_ERROR`: Video decoding failure
- `MANIFEST_LOAD_ERROR`: Failed to load HLS manifest
- `SEGMENT_LOAD_ERROR`: Failed to load video segment
- `DRM_ERROR`: DRM/encryption issues

---

## Continue Watching APIs

### 29. Get Continue Watching List
**Endpoint:** `GET /api/continue-watching?profile_id=1`  
**Authentication:** Required (Bearer token)  
**Query Parameters:**
- `profile_id` (required): Profile ID
- `limit` (optional): Number of items (default: 20)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "profile_id": 1,
      "content_item_id": 10,
      "progress_seconds": 1250,
      "duration_seconds": 7200,
      "progress_percentage": 17.36,
      "last_watched_at": "2024-01-01T12:00:00Z",
      "content_item": {
        "id": 10,
        "title": "Movie Title",
        "thumbnail_url": "https://...",
        "type": "movie"
      }
    }
  ]
}
```

---

### 30. Get Recently Watched
**Endpoint:** `GET /api/recently-watched?profile_id=1`  
**Authentication:** Required (Bearer token)  
**Query Parameters:**
- `profile_id` (required): Profile ID
- `limit` (optional): Number of items (default: 20)

**Response:** Similar to continue watching, but includes completed content.

---

### 31. Mark Content as Completed
**Endpoint:** `POST /api/continue-watching/complete`  
**Authentication:** Required (Bearer token)  
**Request Body:**
```json
{
  "profile_id": 1,
  "content_item_id": 10
}
```

**Response:**
```json
{
  "message": "Content marked as completed"
}
```

---

### 32. Remove from Continue Watching
**Endpoint:** `DELETE /api/continue-watching`  
**Authentication:** Required (Bearer token)  
**Request Body:**
```json
{
  "profile_id": 1,
  "content_item_id": 10
}
```

**Response:**
```json
{
  "message": "Content removed from continue watching"
}
```

---

## Complete Streaming Flow

### Example: Playing a Movie

```javascript
// 1. Request playback token
const tokenResponse = await fetch('/api/playback/token', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${userToken}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    profile_id: 1,
    content_item_id: 10
  })
});
const { data: { token } } = await tokenResponse.json();

// 2. Get HLS manifest with all quality variants
const manifestResponse = await fetch(`/api/playback/hls/${token}`, {
  headers: {
    'Authorization': `Bearer ${userToken}`
  }
});
const { data: playbackData } = await manifestResponse.json();

// 3. Get subtitles if needed
const tracksResponse = await fetch('/api/playback/tracks/10', {
  headers: {
    'Authorization': `Bearer ${userToken}`
  }
});
const { data: tracks } = await tracksResponse.json();

// 4. Initialize HLS player (using hls.js)
const video = document.getElementById('video');
const hls = new Hls({
  startPosition: playbackData.resume_position // Resume from last position
});

// Use the first (highest quality) variant URL
hls.loadSource(playbackData.variants[0].url);
hls.attachMedia(video);

// 5. Add subtitles
tracks.subtitles.forEach(subtitle => {
  const track = video.addTextTrack('subtitles', subtitle.label, subtitle.language);
  track.mode = subtitle.language === 'en' ? 'showing' : 'hidden';
  // Load VTT file from subtitle.url
});

// 6. Track progress every 30 seconds
setInterval(async () => {
  await fetch('/api/playback/progress', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${userToken}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      token: token,
      position: Math.floor(video.currentTime),
      duration: Math.floor(video.duration)
    })
  });
}, 30000);

// 7. Handle errors
hls.on(Hls.Events.ERROR, async (event, data) => {
  await fetch('/api/playback/error', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${userToken}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      token: token,
      error_code: data.type,
      error_message: data.details
    })
  });
});
```

### Example: React Native with Video.js or react-native-video

```typescript
import Video from 'react-native-video';

const VideoPlayer = () => {
  const [playbackData, setPlaybackData] = useState(null);
  
  useEffect(() => {
    // Get playback token and manifest
    async function setupPlayback() {
      const token = await requestPlaybackToken(profileId, contentId);
      const manifest = await getHlsManifest(token);
      setPlaybackData(manifest);
    }
    setupPlayback();
  }, []);
  
  return (
    <Video
      source={{ uri: playbackData?.variants[0].url }}
      style={styles.video}
      controls
      resizeMode="contain"
      onProgress={(data) => {
        // Update progress every 30 seconds
        if (data.currentTime % 30 === 0) {
          updateWatchProgress(token, data.currentTime, data.seekableDuration);
        }
      }}
      onError={(error) => {
        reportPlaybackError(token, error.error.code, error.error.localizedDescription);
      }}
      textTracks={playbackData?.tracks.subtitles.map(sub => ({
        title: sub.label,
        language: sub.language,
        type: 'text/vtt',
        uri: sub.url
      }))}
    />
  );
};
```

---

## Quality Selection & Adaptive Streaming

The HLS manifests include multiple quality variants. Modern HLS players (HLS.js, Video.js, ExoPlayer, AVPlayer) automatically handle:

1. **Adaptive Bitrate Streaming (ABR):** Automatically switches between qualities based on network bandwidth
2. **Manual Quality Selection:** Allow users to manually select quality
3. **Bandwidth Estimation:** Continuously monitors network conditions

### Manual Quality Selection Example

```javascript
// Get available qualities
const qualities = playbackData.variants.map(v => v.quality);

// Switch to specific quality
function setQuality(quality) {
  const variant = playbackData.variants.find(v => v.quality === quality);
  if (variant) {
    hls.currentLevel = hls.levels.findIndex(level => 
      level.height === parseInt(variant.resolution.split('x')[1])
    );
  }
}

// UI for quality selection
<select onChange={(e) => setQuality(e.target.value)}>
  <option value="auto">Auto</option>
  {qualities.map(q => (
    <option key={q} value={q}>{q}</option>
  ))}
</select>
```

---

## Security Notes

1. **Playback Tokens:** Valid for 6 hours, tied to specific profile and content
2. **Signed URLs:** All streaming URLs are signed with expiration timestamps
3. **Token Validation:** Tokens are validated on every streaming request
4. **IP Tracking:** Playback tokens track IP address for fraud detection
5. **Concurrent Streams:** Track via active playback tokens per profile

---

## Testing with Scramble API Docs

Access interactive API documentation at: `/docs/api`

All endpoints require Bearer token authentication (except public content discovery endpoints).

**Example Authorization Header:**
```
Authorization: Bearer your_sanctum_token_here
```

Get your token from the registration or login endpoints:
- POST `/api/auth/register`
- POST `/api/auth/login`
- POST `/api/auth/login/social`
