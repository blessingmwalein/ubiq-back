# UbiQ Entertainment Platform - Complete API Documentation

## Table of Contents
1. [Quick Start & Process Flows](#quick-start--process-flows)
2. [Authentication & Onboarding](#authentication--onboarding)
3. [Profile Management](#profile-management)
4. [Device Management](#device-management)
5. [Content Discovery](#content-discovery)
6. [Shows & Series](#shows--series)
7. [Streaming & Playback](#streaming--playback)
8. [Continue Watching](#continue-watching)
9. [User Interactions](#user-interactions)
10. [Subscription Management](#subscription-management)
11. [Response Types & Schemas](#response-types--schemas)
12. [Error Handling](#error-handling)

---

## Quick Start & Process Flows

### 🚀 Complete User Journey

```
┌─────────────────┐
│  User Opens App │
└────────┬────────┘
         │
         ▼
    ┌────────────┐        ┌──────────────┐
    │ Has Token? ├───NO──→│ Login/Register│
    └─────┬──────┘        └───────┬──────┘
          │ YES                   │
          ▼                       ▼
    ┌─────────────┐        ┌─────────────┐
    │Verify Device│        │Create Account│
    └──────┬──────┘        └──────┬──────┘
           │                      │
           └──────────┬───────────┘
                      ▼
              ┌──────────────┐
              │Select Profile│
              └───────┬──────┘
                      ▼
              ┌──────────────┐
              │Browse Content│
              └───────┬──────┘
                      ▼
              ┌──────────────┐
              │ Select Video │
              └───────┬──────┘
                      ▼
              ┌──────────────┐
              │Request Token │
              └───────┬──────┘
                      ▼
              ┌──────────────┐
              │ Stream Video │
              └───────┬──────┘
                      ▼
              ┌──────────────┐
              │Track Progress│
              └──────────────┘
```

### 📱 1. App Launch & Authentication Flow

**Step 1: Check Authentication Status**
```javascript
// On app launch
const token = await getStoredToken();

if (token) {
  // Verify device and get user data
  const userData = await fetch('/api/auth/me', {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  
  if (userData.ok) {
    // Navigate to profile selection
  } else {
    // Token expired, show login
  }
} else {
  // Show login/register screen
}
```

**Step 2: Login (Email/Password)**
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123",
  "device_name": "iPhone 14 Pro",
  "device_type": "mobile",
  "device_id": "unique-device-uuid"
}
```

**Response:**
```json
{
  "message": "Login successful",
  "data": {
    "user": { "id": 1, "name": "John Doe", "email": "user@example.com" },
    "account": { "id": "uuid", "subscription_status": "active" },
    "device": { "uuid": "device-uuid", "device_name": "iPhone 14 Pro" },
    "token": "1|abcdef123456..."
  }
}
```

**Step 3: Social Login (Google/Facebook)**
```http
POST /api/auth/login/social
Content-Type: application/json

{
  "provider": "google",
  "provider_id": "google-user-id",
  "email": "user@example.com",
  "name": "John Doe",
  "avatar_url": "https://lh3.googleusercontent.com/...",
  "device_name": "iPhone 14 Pro",
  "device_type": "mobile",
  "device_id": "unique-device-uuid"
}
```

**Response:** Same as login

---

### 👤 2. Profile Management Flow

**Step 1: Get All Profiles**
```http
GET /api/profiles
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": [
    {
      "id": "profile-uuid-1",
      "name": "John",
      "avatar_url": "https://...",
      "is_kids": false,
      "is_primary": true,
      "pin_enabled": false,
      "interests": ["action", "sci-fi"]
    },
    {
      "id": "profile-uuid-2",
      "name": "Kids",
      "avatar_url": "https://...",
      "is_kids": true,
      "is_primary": false,
      "pin_enabled": true,
      "interests": ["animation", "family"]
    }
  ]
}
```

**Step 2: Create New Profile**
```http
POST /api/profiles
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Sarah",
  "avatar_url": "https://...",
  "is_kids": false,
  "pin": "1234",
  "interests": ["drama", "romance", "comedy"]
}
```

**Step 3: Switch Profile**
```http
POST /api/profiles/{profile-uuid}/switch
Authorization: Bearer {token}
Content-Type: application/json

{
  "pin": "1234"  // Only required if profile has PIN
}
```

**Response:**
```json
{
  "message": "Profile switched successfully",
  "data": {
    "profile": { "id": "uuid", "name": "Sarah" },
    "token": "new-token-for-this-profile"
  }
}
```

---

### 🎬 3. Content Discovery Flow

**Home Screen - Load Multiple Sections in Parallel**

```javascript
// Make parallel requests
const [featured, trending, recommendations, continueWatching, newReleases] = await Promise.all([
  fetch('/api/content/featured'),
  fetch('/api/content/trending?limit=20'),
  fetch('/api/content/recommendations?profile_id=uuid'),
  fetch('/api/continue-watching?profile_id=uuid&limit=10'),
  fetch('/api/content/recently-added?limit=20')
]);
```

**Endpoint Details:**

**1. Featured Content (Hero Carousel)**
```http
GET /api/content/featured
```
Returns: 5 featured movies/shows for hero banner

**2. Trending Content**
```http
GET /api/content/trending?limit=20
```
Returns: Content with most views in last 7 days

**3. Personalized Recommendations**
```http
GET /api/content/recommendations?profile_id=uuid&limit=20
Authorization: Bearer {token}
```
Returns: Content based on watch history and interests

**4. Continue Watching**
```http
GET /api/continue-watching?profile_id=uuid&limit=10
Authorization: Bearer {token}
```
Returns: Content with watch progress

**5. Recently Added**
```http
GET /api/content/recently-added?limit=20
```
Returns: Newest content

**6. Browse by Genre**
```http
GET /api/content/genre/action?per_page=20
```
Returns: Content filtered by genre slug

**7. Search**
```http
GET /api/content/search?q=avengers&per_page=20
```
Returns: Content matching search query

**8. Get Content Details**
```http
GET /api/content/{content-id}
```

**Response:**
```json
{
  "data": {
    "id": "uuid",
    "title": "The Avengers",
    "type": "movie",
    "description": "Earth's mightiest heroes...",
    "poster_url": "https://cdn.../poster.jpg",
    "backdrop_url": "https://cdn.../backdrop.jpg",
    "trailer_url": "https://cdn.../trailer.mp4",
    "duration_minutes": 143,
    "release_year": 2012,
    "maturity_rating": "pg13",
    "views_count": 1500000,
    "category": { "id": "uuid", "name": "Action", "slug": "action" },
    "video_assets": [
      { "quality": "1080p", "type": "hls", "file_path": "content/..." }
    ]
  }
}
```

---

### 📺 4. Shows & Series Flow

**Step 1: Get Show Details**
```http
GET /api/shows/{show-uuid}
```

**Response:**
```json
{
  "data": {
    "id": "uuid",
    "uuid": "show-uuid",
    "total_seasons": 8,
    "total_episodes": 73,
    "status": "completed",
    "contentItem": {
      "id": "content-uuid",
      "title": "Game of Thrones",
      "description": "Nine noble families fight...",
      "poster_url": "https://...",
      "backdrop_url": "https://..."
    },
    "seasons": [
      {
        "id": "season-uuid-1",
        "season_number": 1,
        "episode_count": 10,
        "episodes": [
          {
            "id": "ep-uuid-1",
            "episode_number": 1,
            "title": "Winter Is Coming",
            "duration_minutes": 62,
            "air_date": "2011-04-17"
          }
        ]
      }
    ]
  }
}
```

**Step 2: Get Specific Season**
```http
GET /api/shows/{show-uuid}/seasons/1
```

**Step 3: Get Episode Details**
```http
GET /api/episodes/{episode-uuid}
```

**Response:**
```json
{
  "data": {
    "id": "uuid",
    "episode_number": 1,
    "title": "Winter Is Coming",
    "description": "Full episode description...",
    "thumbnail_url": "https://...",
    "duration_minutes": 62,
    "videoAssets": [
      { "quality": "1080p", "type": "hls", "file_path": "episodes/..." },
      { "quality": "720p", "type": "hls", "file_path": "episodes/..." }
    ],
    "watch_progress": {
      "progress_seconds": 1200,
      "progress_percentage": 32.26,
      "completed": false
    }
  }
}
```

**Step 4: Get Next Episode (Auto-Play)**
```http
GET /api/episodes/{current-episode-uuid}/next
```

**Response:**
```json
{
  "data": {
    "id": "next-ep-uuid",
    "episode_number": 2,
    "title": "The Kingsroad",
    "season": { "season_number": 1 }
  }
}
```
*Returns null if no next episode (series complete)*

---

### ▶️ 5. Video Streaming Flow (Complete Process)

#### Step 1: Request Playback Token
```http
POST /api/playback/token
Authorization: Bearer {token}
Content-Type: application/json

{
  "profile_id": "profile-uuid",
  "content_item_id": "content-uuid"
}
```

**Response:**
```json
{
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_at": "2025-11-16T16:00:00Z"
  }
}
```
*Token valid for 6 hours*

#### Step 2: Get HLS Manifest with Quality Options
```http
GET /api/playback/hls/{playback-token}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": {
    "content_id": "content-uuid",
    "title": "Movie Title",
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
    "duration": 8580,
    "token": "eyJhbGci...",
    "expires_at": "2025-11-16T16:00:00Z"
  }
}
```

#### Step 3: Get Subtitle & Audio Tracks
```http
GET /api/playback/tracks/{content-id}
Authorization: Bearer {token}
```

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
        "label": "Spanish (Latin America)",
        "url": "https://cdn.../subtitles-es.vtt?signature=...",
        "format": "vtt"
      }
    ],
    "audio": [
      {
        "id": 1,
        "language": "en",
        "label": "English (Original)",
        "codec": "AAC"
      }
    ]
  }
}
```

#### Step 4: Initialize Video Player

**Web (HLS.js):**
```javascript
import Hls from 'hls.js';

// Get playback data
const token = await requestPlaybackToken(profileId, contentId);
const playback = await getHlsManifest(token);

// Initialize player
const video = document.getElementById('video');
const hls = new Hls({
  startPosition: playback.resume_position // Resume from last position
});

// Load highest quality manifest (ABR will handle quality switching)
hls.loadSource(playback.variants[0].url);
hls.attachMedia(video);

// Add subtitles
playback.tracks.subtitles.forEach(subtitle => {
  const track = video.addTextTrack('subtitles', subtitle.label, subtitle.language);
  track.mode = subtitle.language === 'en' ? 'showing' : 'hidden';
});

// Handle quality switching
const qualityLevels = hls.levels.map((level, index) => ({
  label: `${level.height}p`,
  value: index
}));

function changeQuality(levelIndex) {
  hls.currentLevel = levelIndex;
}

// Error handling
hls.on(Hls.Events.ERROR, (event, data) => {
  reportPlaybackError(token, data.type, data.details);
});
```

**React Native (react-native-video):**
```javascript
import Video from 'react-native-video';

<Video
  source={{ uri: playbackData.variants[0].url }}
  style={styles.video}
  controls
  resizeMode="contain"
  onLoad={(data) => {
    // Seek to resume position
    videoRef.current.seek(playbackData.resume_position);
  }}
  onProgress={(data) => {
    // Update progress every 30 seconds
    if (Math.floor(data.currentTime) % 30 === 0) {
      updateWatchProgress(token, data.currentTime, data.seekableDuration);
    }
  }}
  onEnd={() => {
    markAsCompleted(profileId, contentId);
  }}
  onError={(error) => {
    reportPlaybackError(token, error.error.code, error.error.localizedDescription);
  }}
  textTracks={playbackData.tracks.subtitles.map(sub => ({
    title: sub.label,
    language: sub.language,
    type: 'text/vtt',
    uri: sub.url
  }))}
  selectedTextTrack={{ type: 'language', value: 'en' }}
/>
```

#### Step 5: Track Watch Progress (Every 30 seconds)
```http
POST /api/playback/progress
Authorization: Bearer {token}
Content-Type: application/json

{
  "token": "playback-token",
  "position": 1800,
  "duration": 8580
}
```

**Response:**
```json
{
  "message": "Progress updated successfully"
}
```

#### Step 6: Report Errors (If Any)
```http
POST /api/playback/error
Authorization: Bearer {token}
Content-Type: application/json

{
  "token": "playback-token",
  "error_code": "NETWORK_ERROR",
  "error_message": "Failed to load video segment at 1200s"
}
```

---

### 🔄 6. Continue Watching Flow

#### Get Continue Watching List
```http
GET /api/continue-watching?profile_id=profile-uuid&limit=20
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "profile_id": "profile-uuid",
      "content_item_id": "content-uuid",
      "progress_seconds": 1250,
      "duration_seconds": 7200,
      "progress_percentage": 17.36,
      "last_watched_at": "2025-11-16T12:30:00Z",
      "content_item": {
        "id": "content-uuid",
        "title": "The Dark Knight",
        "type": "movie",
        "thumbnail_url": "https://...",
        "duration_minutes": 152
      }
    },
    {
      "id": 2,
      "profile_id": "profile-uuid",
      "content_item_id": "show-content-uuid",
      "progress_seconds": 1800,
      "duration_seconds": 3600,
      "progress_percentage": 50.0,
      "last_watched_at": "2025-11-16T10:00:00Z",
      "content_item": {
        "id": "show-content-uuid",
        "title": "Breaking Bad - S1E3",
        "type": "series",
        "thumbnail_url": "https://..."
      },
      "episode": {
        "id": "episode-uuid",
        "title": "...And the Bag's in the River",
        "episode_number": 3,
        "season_number": 1
      }
    }
  ]
}
```

#### Mark as Completed (Remove from Continue Watching)
```http
POST /api/continue-watching/complete
Authorization: Bearer {token}
Content-Type: application/json

{
  "profile_id": "profile-uuid",
  "content_item_id": "content-uuid"
}
```

---

### 🎯 7. Auto-Play Next Episode Flow

```javascript
// When current episode is near end (progress > 90%)
videoPlayer.on('timeupdate', async () => {
  const progress = (videoPlayer.currentTime / videoPlayer.duration) * 100;
  
  if (progress > 90 && !nextEpisodeShown) {
    nextEpisodeShown = true;
    
    // Get next episode
    const nextEp = await fetch(`/api/episodes/${currentEpisodeUuid}/next`);
    
    if (nextEp.data) {
      // Show countdown overlay
      showNextEpisodeOverlay(nextEp.data, 10); // 10 second countdown
      
      // Auto-play after countdown
      setTimeout(() => {
        if (!userCancelled) {
          playEpisode(nextEp.data.id);
        }
      }, 10000);
    }
  }
});
```

---

## Authentication & Onboarding

### Base URL: `/api/auth`

### 1. Register User
**Endpoint:** `POST /api/auth/register`

**Request Body:**
```typescript
interface RegisterRequest {
  name: string;                    // Min 2 chars
  email: string;                   // Valid email
  password: string;                // Min 8 chars
  password_confirmation: string;   // Must match password
}
```

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "securepassword123",
  "password_confirmation": "securepassword123"
}
```

**Response:** `201 Created`
```typescript
interface RegisterResponse {
  message: string;
  data: {
    user: User;
    account: Account;
    token: string;
  }
}
```

```json
{
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "name": "John Doe",
      "email": "john@example.com",
      "email_verified_at": null,
      "avatar_url": null,
      "onboarding_completed": false,
      "created_at": "2025-11-16T10:00:00Z",
      "updated_at": "2025-11-16T10:00:00Z"
    },
    "account": {
      "id": "account-uuid",
      "user_id": 1,
      "package_id": "free-package-uuid",
      "subscription_status": "active",
      "package": {
        "id": "free-package-uuid",
        "key": "free",
        "title": "Free Plan",
        "price_monthly": 0,
        "max_profiles": 1,
        "max_devices": 1
      }
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz123456"
  }
}
```

**Features:**
- Auto-creates user account
- Auto-subscribes to free package (10-year trial)
- Auto-creates default profile
- Returns Sanctum auth token
- Token never expires unless revoked

---

### 2. Login
**Endpoint:** `POST /api/auth/login`

**Request Body:**
```typescript
interface LoginRequest {
  email: string;
  password: string;
  device_name?: string;    // e.g., "iPhone 14 Pro"
  device_type?: string;    // mobile, desktop, tablet, tv
  device_id?: string;      // Unique device identifier
}
```

```json
{
  "email": "john@example.com",
  "password": "securepassword123",
  "device_name": "iPhone 14 Pro",
  "device_type": "mobile",
  "device_id": "DEVICE-UUID-12345"
}
```

**Response:** `200 OK`
```typescript
interface LoginResponse {
  message: string;
  data: {
    user: User;
    account: Account;
    device?: Device;
    token: string;
  }
}
```

```json
{
  "message": "Login successful",
  "data": {
    "user": { 
      "id": 1,
      "uuid": "user-uuid",
      "name": "John Doe",
      "email": "john@example.com",
      "onboarding_completed": true
    },
    "account": {
      "id": "account-uuid",
      "subscription_status": "active",
      "package": {
        "key": "premium",
        "title": "Premium Plan",
        "max_profiles": 5,
        "max_devices": 4
      }
    },
    "device": {
      "uuid": "device-uuid",
      "device_name": "iPhone 14 Pro",
      "device_type": "mobile",
      "is_active": true,
      "last_used_at": "2025-11-16T10:00:00Z"
    },
    "token": "2|newtoken123456789"
  }
}
```

**Error Responses:**
```json
// Invalid credentials (401)
{
  "message": "Invalid email or password"
}

// Device limit reached (403)
{
  "message": "Device limit reached. Please remove a device to continue.",
  "data": {
    "current_devices": 4,
    "max_devices": 4
  }
}
```

---

### 3. Social Login (Google, Facebook, Apple)
**Endpoint:** `POST /api/auth/login/social`

**Request Body:**
```typescript
interface SocialLoginRequest {
  provider: 'google' | 'facebook' | 'apple';
  provider_id: string;              // Provider's user ID
  email: string;
  name: string;
  avatar_url?: string;
  device_name?: string;
  device_type?: 'mobile' | 'desktop' | 'tablet' | 'tv';
  device_id?: string;
  os_name?: string;                 // iOS, Android, Windows, macOS
  os_version?: string;              // 17.0, 14, etc.
  app_version?: string;             // 1.0.0
}
```

```json
{
  "provider": "google",
  "provider_id": "1234567890",
  "email": "john@gmail.com",
  "name": "John Doe",
  "avatar_url": "https://lh3.googleusercontent.com/a/...",
  "device_name": "iPhone 14 Pro",
  "device_type": "mobile",
  "device_id": "DEVICE-UUID-12345",
  "os_name": "iOS",
  "os_version": "17.0",
  "app_version": "1.0.0"
}
```

**Response:** `200 OK` - Same as login response

**Social Auth Integration Examples:**

**Google Sign-In (React Native):**
```javascript
import { GoogleSignin } from '@react-native-google-signin/google-signin';

GoogleSignin.configure({
  webClientId: 'YOUR_WEB_CLIENT_ID',
});

async function signInWithGoogle() {
  await GoogleSignin.hasPlayServices();
  const userInfo = await GoogleSignin.signIn();
  
  // Call your API
  const response = await fetch('/api/auth/login/social', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      provider: 'google',
      provider_id: userInfo.user.id,
      email: userInfo.user.email,
      name: userInfo.user.name,
      avatar_url: userInfo.user.photo,
      device_id: await getDeviceId(),
      device_name: await getDeviceName(),
      device_type: 'mobile'
    })
  });
  
  const data = await response.json();
  await AsyncStorage.setItem('token', data.data.token);
}
```

**Facebook Login (React Native):**
```javascript
import { LoginManager, AccessToken, Profile } from 'react-native-fbsdk-next';

async function signInWithFacebook() {
  const result = await LoginManager.logInWithPermissions(['public_profile', 'email']);
  
  if (!result.isCancelled) {
    const accessToken = await AccessToken.getCurrentAccessToken();
    const profile = await Profile.getCurrentProfile();
    
    // Get email from Graph API
    const response = await fetch(
      `https://graph.facebook.com/me?fields=email&access_token=${accessToken.accessToken}`
    );
    const userData = await response.json();
    
    // Call your API
    const apiResponse = await fetch('/api/auth/login/social', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        provider: 'facebook',
        provider_id: profile.userID,
        email: userData.email,
        name: profile.name,
        avatar_url: profile.imageURL,
        device_id: await getDeviceId(),
        device_name: await getDeviceName(),
        device_type: 'mobile'
      })
    });
    
    const data = await apiResponse.json();
    await AsyncStorage.setItem('token', data.data.token);
  }
}
```

---

### 4. Get Current User
**Endpoint:** `GET /api/auth/me`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```typescript
interface MeResponse {
  data: {
    user: User;
    account: Account;
    profiles: Profile[];
  }
}
```

```json
{
  "data": {
    "user": {
      "id": 1,
      "uuid": "user-uuid",
      "name": "John Doe",
      "email": "john@example.com",
      "avatar_url": "https://...",
      "onboarding_completed": true,
      "date_of_birth": "1990-01-15",
      "created_at": "2025-11-16T10:00:00Z"
    },
    "account": {
      "id": "account-uuid",
      "subscription_status": "active",
      "current_package": {
        "key": "premium",
        "title": "Premium Plan",
        "max_profiles": 5,
        "max_devices": 4
      }
    },
    "profiles": [
      {
        "id": "profile-uuid-1",
        "uuid": "profile-uuid-1",
        "name": "John",
        "avatar_url": null,
        "is_kids": false,
        "is_primary": true,
        "pin_enabled": false,
        "maturity_rating": "r",
        "interests": ["action", "sci-fi", "thriller"]
      },
      {
        "id": "profile-uuid-2",
        "uuid": "profile-uuid-2",
        "name": "Kids",
        "avatar_url": "https://.../kid-avatar.jpg",
        "is_kids": true,
        "is_primary": false,
        "pin_enabled": true,
        "maturity_rating": "pg",
        "interests": ["animation", "family", "adventure"]
      }
    ]
  }
}
```

---

### 5. Complete Onboarding
**Endpoint:** `POST /api/onboarding/complete`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```typescript
interface OnboardingRequest {
  avatar_url?: string;
  date_of_birth?: string;      // YYYY-MM-DD
  interests?: string[];         // Array of interest slugs
}
```

```json
{
  "avatar_url": "https://example.com/avatar.jpg",
  "date_of_birth": "1990-01-15",
  "interests": ["action", "comedy", "sci-fi", "thriller", "drama"]
}
```

**Available Interests:**
- action, adventure, animation, comedy, crime
- documentary, drama, family, fantasy, history
- horror, music, mystery, romance, sci-fi
- thriller, war, western

**Response:** `200 OK`
```json
{
  "message": "Onboarding completed successfully",
  "data": {
    "user": {
      "id": 1,
      "onboarding_completed": true,
      "avatar_url": "https://example.com/avatar.jpg",
      "date_of_birth": "1990-01-15"
    }
  }
}
```

---

### 6. Logout
**Endpoint:** `POST /api/auth/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "device_id": "DEVICE-UUID-12345"  // Optional: specific device
}
```

**Response:** `200 OK`
```json
{
  "message": "Logged out successfully"
}
```

**Note:** This revokes the current token. If `device_id` is provided, it also deactivates that device.

---

### 7. Forgot Password
**Endpoint:** `POST /api/auth/forgot-password`

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

**Response:** `200 OK`
```json
{
  "message": "Password reset link sent to your email"
}
```

---

### 8. Reset Password
**Endpoint:** `POST /api/auth/reset-password`

**Request Body:**
```json
{
  "email": "john@example.com",
  "token": "reset-token-from-email",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response:** `200 OK`
```json
{
  "message": "Password reset successfully"
}
```

---

## Profile Management

### Base URL
```
/api/profiles
```

### 1. List Profiles
**Endpoint:** `GET /api/profiles`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": "uuid",
      "name": "John",
      "avatar_url": "https://example.com/avatar1.jpg",
      "is_kids": false,
      "is_primary": true,
      "pin_enabled": false,
      "interests": ["action", "comedy"],
      "created_at": "2025-11-16T10:00:00Z"
    },
    {
      "id": "uuid",
      "name": "Kids",
      "avatar_url": "https://example.com/avatar2.jpg",
      "is_kids": true,
      "is_primary": false,
      "pin_enabled": true,
      "interests": ["animation", "family"],
      "created_at": "2025-11-16T10:00:00Z"
    }
  ]
}
```

---

### 2. Create Profile
**Endpoint:** `POST /api/profiles`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "Kids Profile",
  "avatar_url": "https://example.com/kids-avatar.jpg",
  "is_kids": true,
  "pin": "1234",
  "interests": ["animation", "family", "adventure"]
}
```

**Response:** `201 Created`
```json
{
  "message": "Profile created successfully",
  "data": {
    "id": "uuid",
    "name": "Kids Profile",
    "avatar_url": "https://example.com/kids-avatar.jpg",
    "is_kids": true,
    "pin_enabled": true
  }
}
```

---

### 3. Update Profile
**Endpoint:** `PUT /api/profiles/{profileId}`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "name": "Updated Name",
  "avatar_url": "https://example.com/new-avatar.jpg",
  "interests": ["action", "thriller", "drama"]
}
```

**Response:** `200 OK`
```json
{
  "message": "Profile updated successfully",
  "data": { "..." }
}
```

---

### 4. Delete Profile
**Endpoint:** `DELETE /api/profiles/{profileId}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "message": "Profile deleted successfully"
}
```

---

### 5. Switch Profile
**Endpoint:** `POST /api/profiles/{profileId}/switch`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "pin": "1234"
}
```

**Response:** `200 OK`
```json
{
  "message": "Profile switched successfully",
  "data": {
    "profile": { "..." },
    "token": "new-token-for-this-profile"
  }
}
```

---

## Device Management

### Base URL
```
/api/devices
```

### 1. Register Device
**Endpoint:** `POST /api/devices/register`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "device_name": "iPhone 14 Pro",
  "device_type": "mobile",
  "device_id": "unique-device-identifier",
  "os_name": "iOS",
  "os_version": "17.0",
  "app_version": "1.0.0",
  "ip_address": "192.168.1.1"
}
```

**Response:** `201 Created`
```json
{
  "message": "Device registered successfully",
  "data": {
    "id": "uuid",
    "device_name": "iPhone 14 Pro",
    "device_type": "mobile",
    "is_active": true,
    "last_used_at": "2025-11-16T10:00:00Z",
    "registered_at": "2025-11-16T10:00:00Z"
  }
}
```

---

### 2. List Devices
**Endpoint:** `GET /api/devices`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": "uuid",
      "device_name": "iPhone 14 Pro",
      "device_type": "mobile",
      "os_name": "iOS",
      "is_active": true,
      "last_used_at": "2025-11-16T10:00:00Z",
      "registered_at": "2025-11-16T09:00:00Z"
    },
    {
      "id": "uuid",
      "device_name": "MacBook Pro",
      "device_type": "desktop",
      "os_name": "macOS",
      "is_active": false,
      "last_used_at": "2025-11-15T14:00:00Z",
      "registered_at": "2025-11-10T10:00:00Z"
    }
  ]
}
```

---

### 3. Remove Device
**Endpoint:** `DELETE /api/devices/{deviceId}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "message": "Device removed successfully"
}
```

---

### 4. Verify Device Session
**Endpoint:** `POST /api/devices/verify`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "device_id": "unique-device-identifier"
}
```

**Response:** `200 OK`
```json
{
  "data": {
    "valid": true,
    "device": { "..." }
  }
}
```

---

## Content Discovery

### Base URL
```
/api/content
```

### 1. Browse Content
**Endpoint:** `GET /api/content`

**Query Parameters:**
- `type` - Filter by type: movie, show, skit, afrimation, real_estate
- `category_id` - Filter by category UUID
- `maturity_rating` - Filter by rating: all, pg, pg13, r, adult
- `visibility` - Filter by visibility: public, private
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 20)
- `sort` - Sort by: recent, popular, title, release_year

**Example:**
```
GET /api/content?type=movie&category_id=uuid&sort=popular&page=1&per_page=20
```

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": "uuid",
      "title": "Movie Title",
      "slug": "movie-title",
      "type": "movie",
      "description": "Description here...",
      "poster_url": "https://example.com/poster.jpg",
      "backdrop_url": "https://example.com/backdrop.jpg",
      "thumbnail_url": "https://example.com/thumb.jpg",
      "trailer_url": "https://example.com/trailer.mp4",
      "release_year": 2025,
      "duration_seconds": 7200,
      "maturity_rating": "pg13",
      "views_count": 15420,
      "category": {
        "id": "uuid",
        "title": "Action",
        "key": "action"
      },
      "provider": {
        "id": "uuid",
        "display_name": "Universal Studios"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8
  }
}
```

---

### 2. Search Content
**Endpoint:** `GET /api/content/search`

**Query Parameters:**
- `q` - Search query (required)
- `type` - Filter by type
- `page` - Page number
- `per_page` - Items per page

**Example:**
```
GET /api/content/search?q=avengers&type=movie
```

**Response:** `200 OK` - Same structure as browse

---

### 3. Get Content Details
**Endpoint:** `GET /api/content/{id}`

**Response:** `200 OK`
```json
{
  "data": {
    "id": "uuid",
    "title": "Movie Title",
    "slug": "movie-title",
    "type": "movie",
    "description": "Full description...",
    "poster_url": "...",
    "backdrop_url": "...",
    "trailer_url": "...",
    "release_year": 2025,
    "duration_seconds": 7200,
    "maturity_rating": "pg13",
    "views_count": 15420,
    "category": { "..." },
    "provider": { "..." },
    "video_assets": [
      {
        "id": "uuid",
        "rendition_key": "1080p",
        "resolution": "1920x1080",
        "bitrate": 5000,
        "file_size_mb": 2500,
        "status": "ready"
      }
    ],
    "is_favorited": false,
    "watch_progress": null
  }
}
```

---

### 4. Most Viewed
**Endpoint:** `GET /api/content/most-viewed`

**Query Parameters:**
- `type` - Filter by type
- `period` - Time period: today, week, month, all (default: week)
- `limit` - Number of items (default: 10)

**Response:** `200 OK` - Array of content items

---

### 5. Recently Added
**Endpoint:** `GET /api/content/recently-added`

**Query Parameters:**
- `type` - Filter by type
- `limit` - Number of items (default: 20)

**Response:** `200 OK` - Array of content items

---

### 6. Recommendations
**Endpoint:** `GET /api/content/recommendations`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `profile_id` - Profile UUID (optional, uses current profile)
- `limit` - Number of items (default: 20)

**Response:** `200 OK` - Array of content items based on user interests and watch history

---

## Shows & Episodes

### Base URL
```
/api/shows
```

### 1. Get Show Details
**Endpoint:** `GET /api/shows/{showId}`

**Response:** `200 OK`
```json
{
  "data": {
    "id": "uuid",
    "content_item": {
      "id": "uuid",
      "title": "Show Title",
      "description": "...",
      "poster_url": "...",
      "backdrop_url": "..."
    },
    "total_seasons": 3,
    "total_episodes": 30,
    "status": "ongoing",
    "seasons": [
      {
        "id": "uuid",
        "season_number": 1,
        "title": "Season 1",
        "description": "...",
        "poster_url": "...",
        "episode_count": 10,
        "released_at": "2025-01-01"
      }
    ]
  }
}
```

---

### 2. Get Season Details
**Endpoint:** `GET /api/shows/{showId}/seasons/{seasonNumber}`

**Response:** `200 OK`
```json
{
  "data": {
    "id": "uuid",
    "season_number": 1,
    "title": "Season 1",
    "description": "...",
    "poster_url": "...",
    "episode_count": 10,
    "episodes": [
      {
        "id": "uuid",
        "episode_number": 1,
        "title": "Pilot",
        "description": "...",
        "thumbnail_url": "...",
        "duration_seconds": 3600,
        "released_at": "2025-01-01",
        "has_video": true,
        "watch_progress": {
          "progress_seconds": 1200,
          "progress_percentage": 33.33,
          "last_watched_at": "2025-11-16T10:00:00Z"
        }
      }
    ]
  }
}
```

---

### 3. Get Episode Details
**Endpoint:** `GET /api/episodes/{episodeId}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "data": {
    "id": "uuid",
    "episode_number": 1,
    "title": "Pilot",
    "description": "Full episode description...",
    "thumbnail_url": "...",
    "duration_seconds": 3600,
    "released_at": "2025-01-01",
    "views_count": 5420,
    "season": {
      "id": "uuid",
      "season_number": 1,
      "title": "Season 1"
    },
    "show": {
      "id": "uuid",
      "title": "Show Title"
    },
    "video_assets": [
      {
        "id": "uuid",
        "rendition_key": "1080p",
        "resolution": "1920x1080",
        "bitrate": 5000,
        "status": "ready"
      },
      {
        "id": "uuid",
        "rendition_key": "720p",
        "resolution": "1280x720",
        "bitrate": 3000,
        "status": "ready"
      }
    ],
    "watch_progress": {
      "progress_seconds": 1200,
      "progress_percentage": 33.33,
      "completed": false
    },
    "next_episode": {
      "id": "uuid",
      "episode_number": 2,
      "title": "Next Episode"
    },
    "previous_episode": null
  }
}
```

---

## Video Streaming

### Base URL
```
/api/playback
```

### 1. Request Playback Token
**Endpoint:** `POST /api/playback/token`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "content_item_id": "uuid",
  "profile_id": "uuid",
  "quality": "1080p"
}
```

**Response:** `200 OK`
```json
{
  "data": {
    "token": "secure-playback-token",
    "expires_at": "2025-11-16T14:00:00Z",
    "video_asset": {
      "id": "uuid",
      "rendition_key": "1080p",
      "resolution": "1920x1080"
    }
  }
}
```

---

### 2. Get Stream URL
**Endpoint:** `GET /api/playback/stream/{token}`

**Response:** `200 OK`
```json
{
  "data": {
    "stream_url": "https://cdn.example.com/stream/video.m3u8",
    "content_type": "application/vnd.apple.mpegurl",
    "expires_at": "2025-11-16T14:00:00Z",
    "subtitles": [
      {
        "language": "en",
        "label": "English",
        "url": "https://cdn.example.com/subs/en.vtt"
      }
    ]
  }
}
```

---

### 3. Validate Playback Token
**Endpoint:** `GET /api/playback/validate/{token}`

**Response:** `200 OK`
```json
{
  "data": {
    "valid": true,
    "expires_at": "2025-11-16T14:00:00Z",
    "content_item_id": "uuid"
  }
}
```

---

### 4. Update Watch Progress
**Endpoint:** `POST /api/watch-history/progress`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "content_item_id": "uuid",
  "episode_id": "uuid",
  "profile_id": "uuid",
  "progress_seconds": 1800,
  "duration_seconds": 3600,
  "completed": false
}
```

**Response:** `200 OK`
```json
{
  "message": "Progress updated successfully",
  "data": {
    "progress_percentage": 50.0,
    "completed": false
  }
}
```

---

## User Interactions

### 1. Toggle Favorite
**Endpoint:** `POST /api/favorites/{contentId}/toggle`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "profile_id": "uuid"
}
```

**Response:** `200 OK`
```json
{
  "message": "Added to favorites",
  "data": {
    "is_favorited": true
  }
}
```

---

### 2. List Favorites
**Endpoint:** `GET /api/favorites`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `profile_id` - Profile UUID
- `type` - Filter by content type

**Response:** `200 OK` - Array of content items

---

### 3. Continue Watching
**Endpoint:** `GET /api/watch-history/continue`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `profile_id` - Profile UUID
- `limit` - Number of items (default: 10)

**Response:** `200 OK`
```json
{
  "data": [
    {
      "content_item": { "..." },
      "episode": { "..." },
      "progress_seconds": 1200,
      "progress_percentage": 33.33,
      "last_watched_at": "2025-11-16T10:00:00Z"
    }
  ]
}
```

---

## Subscription Management

### 1. Get Available Packages
**Endpoint:** `GET /api/packages`

**Response:** `200 OK`
```json
{
  "data": [
    {
      "id": "uuid",
      "key": "free",
      "title": "Free Plan",
      "description": "Basic streaming with ads",
      "price_monthly": 0,
      "price_yearly": 0,
      "trial_days": 0,
      "max_profiles": 1,
      "features": [
        "Limited content",
        "SD quality",
        "With ads",
        "1 device"
      ],
      "is_active": true
    },
    {
      "id": "uuid",
      "key": "premium",
      "title": "Premium Plan",
      "description": "Full HD streaming without ads",
      "price_monthly": 9.99,
      "price_yearly": 99.99,
      "trial_days": 7,
      "max_profiles": 4,
      "features": [
        "Full content library",
        "HD & Full HD quality",
        "No ads",
        "4 devices simultaneously",
        "Download for offline"
      ],
      "is_active": true
    }
  ]
}
```

---

### 2. Get Current Subscription
**Endpoint:** `GET /api/subscriptions`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** `200 OK`
```json
{
  "data": {
    "id": "uuid",
    "account_id": "uuid",
    "package": {
      "id": "uuid",
      "title": "Free Plan",
      "price_monthly": 0
    },
    "status": "active",
    "current_period_start": "2025-11-01T00:00:00Z",
    "current_period_end": "2025-12-01T00:00:00Z",
    "auto_renew": true,
    "created_at": "2025-11-01T00:00:00Z"
  }
}
```

---

## Error Responses

All endpoints follow this error format:

```json
{
  "message": "Error message here",
  "errors": {
    "field_name": [
      "Validation error message"
    ]
  }
}
```

### Common Status Codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Server Error

---

## Rate Limiting

All API endpoints are rate-limited:
- **Public endpoints:** 60 requests per minute
- **Authenticated endpoints:** 120 requests per minute
- **Streaming endpoints:** 30 requests per minute

Rate limit headers:
```
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 115
X-RateLimit-Reset: 1700140800
```

---

## Authentication

All protected endpoints require Bearer token:

```
Authorization: Bearer {your-token-here}
```

Tokens expire after 7 days. Refresh tokens before expiration.

---

## Pagination

Paginated endpoints return:

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "last_page": 8
  },
  "links": {
    "first": "https://api.example.com/endpoint?page=1",
    "last": "https://api.example.com/endpoint?page=8",
    "prev": null,
    "next": "https://api.example.com/endpoint?page=2"
  }
}
```

---

## Implementation Status

✅ **Completed:**
- Basic authentication (register, login, social login)
- Content discovery (browse, search)
- Watch history
- Favorites
- Basic playback

🚧 **In Progress:**
- Onboarding flow
- Device management
- Profile management
- Show/episode APIs
- Enhanced streaming

📋 **Planned:**
- Payment integration
- Subscription upgrades
- Offline downloads
- Live streaming
