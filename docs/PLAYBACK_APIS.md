# Playback & Streaming APIs

Video playback, streaming tokens, and watch progress tracking.

---

## Table of Contents

1. [Request Playback Token](#request-playback-token)
2. [Get Streaming URL](#get-streaming-url)
3. [Get HLS Manifest](#get-hls-manifest)
4. [Track Watch Progress](#track-watch-progress)
5. [Update Progress](#update-progress)
6. [Get Watch History](#get-watch-history)
7. [Resume Playback](#resume-playback)
8. [Get Quality Options](#get-quality-options)
9. [Episode Playback Example](#episode-playback-example)

---

## Overview

The playback system uses secure tokens to provide access to video streams. The flow is:

1. **Request Token** → Get a temporary playback token
2. **Get Stream URL** → Use token to get video URL
3. **Track Progress** → Report viewing progress
4. **Resume** → Continue from last watched position

All videos are stored locally and served from `/storage/videos/`

---

## Request Playback Token

Generate a secure playback token for content access (works for movies, shows, and episodes).

### Endpoint
```
POST /api/playback/token
```

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json
Accept: application/json
```

### Request Body

**For Movies:**
```json
{
  "content_id": 123,
  "profile_id": 1,
  "device_id": "web-chrome-uuid-12345",
  "client_info": {
    "browser": "Chrome",
    "os": "Windows 10",
    "resolution": "1920x1080"
  }
}
```

**For TV Show Episodes:**
```json
{
  "content_id": 456,
  "profile_id": 1,
  "device_id": "web-chrome-uuid-12345",
  "episode_info": {
    "show_id": 10,
    "season_number": 2,
    "episode_number": 5
  }
}
```

### Request Parameters

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| content_id | integer | Yes | Content item ID (movie, show, or episode) |
| profile_id | integer | Yes | User profile ID |
| device_id | string | No | Unique device identifier |
| client_info | object | No | Client/device information |
| episode_info | object | No | Episode metadata (for tracking) |

### Success Response (201 Created)

**For Movies:**
```json
{
  "success": true,
  "data": {
    "token": "abc123def456ghi789jkl012mno345pqr678stu901vwx234yz",
    "content": {
      "id": 123,
      "title": "The Great Adventure",
      "type": "movie",
      "duration_seconds": 7200,
      "thumbnail_url": "/storage/thumbnails/abc123.jpg"
    },
    "expires_at": "2025-11-16T22:30:00.000000Z",
    "created_at": "2025-11-16T16:30:00.000000Z"
  },
  "message": "Playback token generated successfully"
}
```

**For Episodes:**
```json
{
  "success": true,
  "data": {
    "token": "xyz789abc123def456ghi789jkl012mno345pqr678stu901vwx",
    "content": {
      "id": 456,
      "title": "The Heist",
      "type": "episode",
      "duration_seconds": 2700,
      "thumbnail_url": "/storage/thumbnails/episode-456.jpg",
      "show": {
        "id": 10,
        "title": "Crime Series"
      },
      "season_number": 2,
      "episode_number": 5
    },
    "expires_at": "2025-11-16T22:30:00.000000Z",
    "created_at": "2025-11-16T16:30:00.000000Z"
  },
  "message": "Playback token generated successfully"
}
```

### Error Responses

**403 Forbidden** (Subscription Required)
```json
{
  "success": false,
  "message": "This content requires an active subscription",
  "required_subscription": "premium"
}
```

**404 Not Found** (Content Not Found)
```json
{
  "success": false,
  "message": "Content not found or not available"
}
```

### Frontend Example

```javascript
async function requestPlaybackToken(contentId, profileId) {
  const token = localStorage.getItem('auth_token');
  
  try {
    const response = await fetch('https://your-domain.com/api/playback/token', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        content_id: contentId,
        profile_id: profileId,
        device_id: getDeviceId(), // Your device ID function
        client_info: {
          browser: navigator.userAgent,
          resolution: `${window.screen.width}x${window.screen.height}`
        }
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Store playback token
      sessionStorage.setItem('playback_token', data.data.token);
      return data.data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Failed to get playback token:', error);
    throw error;
  }
}

// Usage
requestPlaybackToken(123, 1).then(data => {
  console.log('Token expires:', data.expires_at);
  // Proceed to get streaming URL
});
```

---

## Get Streaming URL

Retrieve the video streaming URL using a playback token.

### Endpoint
```
GET /api/playback/stream/{token}
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| token | string | Yes | Playback token from previous request |

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
    "manifest_url": "https://your-domain.com/storage/videos/content-123/master.m3u8",
    "type": "application/x-mpegURL",
    "token": "abc123def456...",
    "expires_at": "2025-11-16T22:30:00.000000Z",
    "quality_options": [
      {
        "quality": "1080p",
        "resolution": "1920x1080",
        "bitrate": 5000
      },
      {
        "quality": "720p",
        "resolution": "1280x720",
        "bitrate": 2500
      },
      {
        "quality": "480p",
        "resolution": "854x480",
        "bitrate": 1000
      }
    ]
  }
}
```

### Error Responses

**401 Unauthorized** (Invalid Token)
```json
{
  "success": false,
  "message": "Invalid or expired playback token"
}
```

**410 Gone** (Token Already Used)
```json
{
  "success": false,
  "message": "This playback token has already been used"
}
```

### Frontend Example (Video.js Integration)

```javascript
async function getStreamingUrl(playbackToken) {
  const authToken = localStorage.getItem('auth_token');
  
  try {
    const response = await fetch(`https://your-domain.com/api/playback/stream/${playbackToken}`, {
      headers: {
        'Authorization': `Bearer ${authToken}`,
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      return data.data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Failed to get streaming URL:', error);
    throw error;
  }
}

// Initialize video player
async function initializePlayer(contentId, profileId) {
  // Get playback token
  const tokenData = await requestPlaybackToken(contentId, profileId);
  
  // Get streaming URL
  const streamData = await getStreamingUrl(tokenData.token);
  
  // Initialize Video.js player
  const player = videojs('my-video', {
    controls: true,
    autoplay: false,
    preload: 'auto',
    fluid: true,
    sources: [{
      src: streamData.manifest_url,
      type: streamData.type
    }]
  });
  
  // Track progress every 10 seconds
  setInterval(() => {
    trackProgress(tokenData.token, player.currentTime(), player.duration());
  }, 10000);
  
  return player;
}

// Usage
initializePlayer(123, 1);
```

---

## Get HLS Manifest

Get HLS manifest with multiple quality variants.

### Endpoint
```
GET /api/playback/manifest/{token}
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| token | string | Yes | Playback token |

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
    "content_id": 123,
    "title": "The Great Adventure",
    "variants": [
      {
        "quality": "1080p",
        "bandwidth": 5000000,
        "resolution": "1920x1080",
        "url": "https://your-domain.com/storage/videos/content-123/1080p.m3u8"
      },
      {
        "quality": "720p",
        "bandwidth": 2500000,
        "resolution": "1280x720",
        "url": "https://your-domain.com/storage/videos/content-123/720p.m3u8"
      },
      {
        "quality": "480p",
        "bandwidth": 1000000,
        "resolution": "854x480",
        "url": "https://your-domain.com/storage/videos/content-123/480p.m3u8"
      }
    ],
    "resume_position": 0,
    "duration": 7200,
    "token": "abc123def456...",
    "expires_at": "2025-11-16T22:30:00.000000Z"
  }
}
```

---

## Track Watch Progress

Report viewing progress during playback.

### Endpoint
```
POST /api/playback/progress
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
  "token": "abc123def456...",
  "position": 1200,
  "duration": 7200,
  "quality": "1080p",
  "bandwidth_used": 5000
}
```

### Request Parameters

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| token | string | Yes | Playback token |
| position | integer | Yes | Current playback position (seconds) |
| duration | integer | Yes | Total content duration (seconds) |
| quality | string | No | Current quality level |
| bandwidth_used | integer | No | Current bandwidth (kbps) |

### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "progress_percentage": 16.67,
    "is_completed": false,
    "last_position": 1200,
    "watch_session_id": 456
  },
  "message": "Progress updated successfully"
}
```

### Frontend Example

```javascript
function createProgressTracker(playbackToken, videoPlayer) {
  let progressInterval = null;
  
  const trackProgress = async () => {
    const authToken = localStorage.getItem('auth_token');
    const currentTime = Math.floor(videoPlayer.currentTime());
    const duration = Math.floor(videoPlayer.duration());
    
    // Only track if user is actively watching
    if (videoPlayer.paused() || currentTime === 0) {
      return;
    }
    
    try {
      await fetch('https://your-domain.com/api/playback/progress', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          token: playbackToken,
          position: currentTime,
          duration: duration,
          quality: videoPlayer.currentResolution()?.label || 'auto'
        })
      });
    } catch (error) {
      console.error('Failed to track progress:', error);
    }
  };
  
  // Track every 10 seconds
  progressInterval = setInterval(trackProgress, 10000);
  
  // Track when video ends
  videoPlayer.on('ended', () => {
    clearInterval(progressInterval);
    trackProgress(); // Final update
  });
  
  // Cleanup on player dispose
  videoPlayer.on('dispose', () => {
    if (progressInterval) {
      clearInterval(progressInterval);
    }
  });
  
  return {
    stop: () => clearInterval(progressInterval)
  };
}

// Usage
const tracker = createProgressTracker(playbackToken, player);
```

---

## Update Progress

Manually update watch progress (for custom players).

### Endpoint
```
PUT /api/watch-history/{contentId}/progress
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| contentId | integer | Yes | Content item ID |

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Content-Type: application/json
```

### Request Body

```json
{
  "profile_id": 1,
  "progress_seconds": 1200,
  "duration_seconds": 7200,
  "completed": false
}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 789,
    "content_id": 123,
    "profile_id": 1,
    "progress_seconds": 1200,
    "progress_percentage": 16.67,
    "completed": false,
    "updated_at": "2025-11-16T17:00:00.000000Z"
  }
}
```

---

## Get Watch History

Retrieve user's watch history and progress.

### Endpoint
```
GET /api/profiles/{profileId}/watch-history
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
| completed | boolean | No | - | Filter by completion status |

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
      "id": 789,
      "content": {
        "id": 123,
        "title": "The Great Adventure",
        "type": "movie",
        "poster_url": "/storage/posters/abc123.jpg",
        "thumbnail_url": "/storage/thumbnails/abc123.jpg",
        "duration_seconds": 7200
      },
      "progress_seconds": 1200,
      "progress_percentage": 16.67,
      "completed": false,
      "last_watched_at": "2025-11-16T17:00:00.000000Z",
      "created_at": "2025-11-16T16:30:00.000000Z"
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
async function getWatchHistory(profileId, page = 1) {
  const token = localStorage.getItem('auth_token');
  
  try {
    const response = await fetch(
      `https://your-domain.com/api/profiles/${profileId}/watch-history?page=${page}`,
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
    console.error('Failed to fetch watch history:', error);
    throw error;
  }
}

// Display continue watching section
getWatchHistory(1).then(history => {
  const continueWatching = history.data
    .filter(item => !item.completed && item.progress_percentage > 5)
    .slice(0, 10);
    
  displayContinueWatching(continueWatching);
});
```

---

## Resume Playback

Get resume position for content.

### Endpoint
```
GET /api/playback/resume/{contentId}
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| contentId | integer | Yes | Content item ID |

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| profile_id | integer | Yes | Profile ID |

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
    "content_id": 123,
    "resume_position": 1200,
    "progress_percentage": 16.67,
    "has_progress": true,
    "last_watched_at": "2025-11-16T17:00:00.000000Z"
  }
}
```

### Frontend Example

```javascript
async function getResumePosition(contentId, profileId) {
  const token = localStorage.getItem('auth_token');
  
  try {
    const response = await fetch(
      `https://your-domain.com/api/playback/resume/${contentId}?profile_id=${profileId}`,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      }
    );
    
    const data = await response.json();
    
    if (data.success && data.data.has_progress) {
      // Ask user if they want to resume
      const shouldResume = confirm(
        `Resume from ${formatTime(data.data.resume_position)}?`
      );
      
      if (shouldResume) {
        videoPlayer.currentTime(data.data.resume_position);
      }
    }
    
    return data.data;
  } catch (error) {
    console.error('Failed to get resume position:', error);
  }
}

function formatTime(seconds) {
  const h = Math.floor(seconds / 3600);
  const m = Math.floor((seconds % 3600) / 60);
  const s = seconds % 60;
  return `${h}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
}
```

---

## Get Quality Options

Retrieve available video quality options.

### Endpoint
```
GET /api/content/{contentId}/quality-options
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| contentId | integer | Yes | Content item ID |

### Headers
```
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "quality": "1080p",
      "bandwidth": 5000000,
      "resolution": "1920x1080",
      "file_size": 4500.5
    },
    {
      "quality": "720p",
      "bandwidth": 2500000,
      "resolution": "1280x720",
      "file_size": 2100.25
    },
    {
      "quality": "480p",
      "bandwidth": 1000000,
      "resolution": "854x480",
      "file_size": 900.75
    }
  ]
}
```

---

## Episode Playback Example

### Complete flow for playing TV show episodes

```javascript
// Step 1: Get show details with seasons and episodes
async function loadShowWithEpisodes(showId) {
  const token = localStorage.getItem('token');
  
  const response = await fetch(`https://your-domain.com/api/shows/${showId}`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  const data = await response.json();
  return data.data; // Returns show with seasons and episodes
}

// Step 2: Play specific episode
async function playEpisode(episodeId, showId, seasonNum, episodeNum) {
  const token = localStorage.getItem('token');
  const profileId = getCurrentProfileId();
  const deviceId = getDeviceId();
  
  try {
    // Request playback token for episode
    const tokenResponse = await fetch('https://your-domain.com/api/playback/token', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        content_id: episodeId,
        profile_id: profileId,
        device_id: deviceId,
        episode_info: {
          show_id: showId,
          season_number: seasonNum,
          episode_number: episodeNum
        }
      })
    });
    
    const tokenData = await tokenResponse.json();
    
    if (!tokenData.success) {
      throw new Error(tokenData.message);
    }
    
    const playbackToken = tokenData.data.token;
    
    // Get streaming URL
    const streamResponse = await fetch(
      `https://your-domain.com/api/playback/stream/${playbackToken}`,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      }
    );
    
    const streamData = await streamResponse.json();
    
    if (!streamData.success) {
      throw new Error(streamData.message);
    }
    
    // Initialize video player
    initializeEpisodePlayer({
      manifestUrl: streamData.data.manifest_url,
      episodeId: episodeId,
      playbackToken: playbackToken,
      showId: showId,
      seasonNumber: seasonNum,
      episodeNumber: episodeNum
    });
    
    return streamData.data;
  } catch (error) {
    console.error('Episode playback failed:', error);
    throw error;
  }
}

// Step 3: Initialize episode player with auto-next episode
function initializeEpisodePlayer(config) {
  const {
    manifestUrl,
    episodeId,
    playbackToken,
    showId,
    seasonNumber,
    episodeNumber
  } = config;
  
  const player = videojs('episode-player', {
    controls: true,
    autoplay: true,
    preload: 'auto',
    fluid: true,
    sources: [{
      src: manifestUrl,
      type: 'application/x-mpegURL'
    }]
  });
  
  // Track progress every 10 seconds
  let progressInterval = setInterval(() => {
    if (player.paused()) return;
    
    const position = Math.floor(player.currentTime());
    const duration = Math.floor(player.duration());
    
    trackEpisodeProgress(playbackToken, position, duration);
  }, 10000);
  
  // Handle episode completion - auto-play next episode
  player.on('ended', async () => {
    clearInterval(progressInterval);
    
    // Mark episode as completed
    await trackEpisodeProgress(playbackToken, duration, duration);
    
    // Try to load next episode
    const nextEpisode = await getNextEpisode(showId, seasonNumber, episodeNumber);
    
    if (nextEpisode && userSettings.autoplay_next) {
      // Show "Next episode in 5 seconds" overlay
      showNextEpisodeOverlay(nextEpisode, () => {
        playEpisode(
          nextEpisode.id,
          showId,
          nextEpisode.season_number,
          nextEpisode.episode_number
        );
      });
    } else {
      showEpisodeEndScreen(showId, seasonNumber);
    }
  });
  
  // Clean up on player dispose
  player.on('dispose', () => {
    clearInterval(progressInterval);
  });
  
  return player;
}

// Step 4: Get next episode in sequence
async function getNextEpisode(showId, currentSeason, currentEpisode) {
  const token = localStorage.getItem('token');
  
  try {
    const response = await fetch(
      `https://your-domain.com/api/shows/${showId}`,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        }
      }
    );
    
    const data = await response.json();
    const show = data.data;
    
    // Find current season
    const season = show.seasons.find(s => s.season_number === currentSeason);
    if (!season) return null;
    
    // Try to get next episode in same season
    const nextEpisodeInSeason = season.episodes.find(
      e => e.episode_number === currentEpisode + 1
    );
    
    if (nextEpisodeInSeason) {
      return nextEpisodeInSeason;
    }
    
    // Try to get first episode of next season
    const nextSeason = show.seasons.find(
      s => s.season_number === currentSeason + 1
    );
    
    if (nextSeason && nextSeason.episodes.length > 0) {
      return nextSeason.episodes[0];
    }
    
    return null; // No more episodes
  } catch (error) {
    console.error('Failed to get next episode:', error);
    return null;
  }
}

// React component for episode player
function EpisodePlayer({ show, season, episode }) {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const playerRef = useRef(null);
  
  useEffect(() => {
    playEpisode(
      episode.id,
      show.id,
      season.season_number,
      episode.episode_number
    )
      .then(() => setLoading(false))
      .catch(err => {
        setError(err.message);
        setLoading(false);
      });
    
    return () => {
      if (playerRef.current) {
        playerRef.current.dispose();
      }
    };
  }, [episode.id]);
  
  if (loading) return <LoadingSpinner />;
  if (error) return <ErrorMessage message={error} />;
  
  return (
    <div className="episode-player-container">
      <div className="episode-info">
        <h2>{show.title}</h2>
        <p>Season {season.season_number}, Episode {episode.episode_number}</p>
        <h3>{episode.title}</h3>
      </div>
      
      <video
        id="episode-player"
        className="video-js vjs-default-skin"
        ref={playerRef}
      />
      
      <div className="episode-description">
        <p>{episode.description}</p>
      </div>
    </div>
  );
}
```

---

## Playback Events

Track these events for analytics:

```javascript
videoPlayer.on('play', () => {
  // Track play event
  analytics.track('video_play', { content_id: contentId });
});

videoPlayer.on('pause', () => {
  // Track pause event
  analytics.track('video_pause', { 
    content_id: contentId,
    position: videoPlayer.currentTime()
  });
});

videoPlayer.on('ended', () => {
  // Track completion
  analytics.track('video_completed', { content_id: contentId });
});

videoPlayer.on('error', (error) => {
  // Track errors
  analytics.track('video_error', { 
    content_id: contentId,
    error: error.message
  });
});
```

---

**Next:** [Profile APIs →](./PROFILE_APIS.md)
