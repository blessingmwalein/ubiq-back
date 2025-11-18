# Profile Management API Implementation

## Overview
Complete profile management system with PIN protection, kids profiles, and full CRUD operations.

## Database Schema

### Profiles Table (Updated)
```sql
- id (primary key)
- uuid (unique identifier)
- account_id (foreign key)
- name
- avatar_url
- maturity_rating (enum: all, pg, pg13, r, adult)
- is_primary (boolean)
- is_kids (boolean) -- NEW
- pin_enabled (boolean) -- NEW
- pin (hashed) -- NEW
- timestamps
```

## Model Enhancements

### Profile Model
**Location:** `app/Models/Profile.php`

**New Methods:**
- `verifyPin(?string $pin): bool` - Verifies PIN using hash comparison
- `setPin(?string $pin): void` - Sets or clears PIN (hashed with SHA-256)
- `canView(string $contentRating): bool` - Existing maturity rating check

**Security:**
- PIN is hashed using SHA-256
- PIN field is hidden in JSON responses
- Uses `hash_equals()` for timing-safe comparison

## Service Layer

### ProfileService
**Location:** `app/Services/ProfileService.php`

**Methods:**

1. **getUserProfiles(User $user): Collection**
   - Returns all profiles for user's account
   - No PIN required (just listing)

2. **createProfile(User $user, array $data): Profile**
   - Creates new profile with optional PIN
   - Validates profile limit from subscription package
   - Syncs interests if provided
   - **Validation:**
     - Checks max_profiles from active subscription
     - Prevents exceeding profile limit

3. **updateProfile(User $user, string $profileUuid, array $data): Profile**
   - Updates profile fields
   - Can update PIN
   - Can update interests
   - **Security:**
     - Verifies profile belongs to user
     - Prevents modifying primary profile flag

4. **deleteProfile(User $user, string $profileUuid): bool**
   - Deletes profile
   - **Security:**
     - Prevents deletion of primary profile
     - Verifies profile belongs to user

5. **switchProfile(User $user, string $profileUuid, ?string $pin): Profile**
   - Switches to a profile with PIN validation
   - Returns profile with interests loaded
   - **Security:**
     - Requires PIN if profile.pin_enabled = true
     - Throws exception on invalid PIN

6. **getProfile(User $user, string $profileUuid): Profile**
   - Gets single profile details
   - Verifies ownership

7. **getProfileStatistics(User $user, string $profileUuid): array**
   - Returns:
     - total_watch_time
     - total_watched
     - favorites_count
     - continue_watching_count
     - interests_count

## API Endpoints

### ProfileController
**Location:** `app/Http/Controllers/Api/ProfileController.php`

All routes require `auth:api` middleware.

#### 1. List All Profiles
```http
GET /api/profiles
Authorization: Bearer {token}

Response 200:
{
  "data": [
    {
      "uuid": "...",
      "name": "John",
      "avatar_url": "...",
      "maturity_rating": "pg13",
      "is_primary": true,
      "is_kids": false,
      "pin_enabled": false,
      "interests": [...]
    }
  ]
}
```

#### 2. Create Profile
```http
POST /api/profiles
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Kids Profile",
  "avatar_url": "https://...",
  "maturity_rating": "pg",
  "is_kids": true,
  "pin": "1234",
  "interests": [1, 3, 5]
}

Response 201:
{
  "message": "Profile created successfully",
  "data": {
    "uuid": "...",
    "name": "Kids Profile",
    ...
  }
}

Error 400:
{
  "message": "Failed to create profile",
  "error": "Profile limit reached. Maximum 5 profiles allowed."
}
```

**Validation:**
- name: required, string, max 255
- avatar_url: nullable, url
- maturity_rating: nullable, in:all,pg,pg13,r,adult
- is_kids: nullable, boolean
- pin: nullable, string, digits:4
- interests: nullable, array of interest IDs

#### 3. Get Profile Details
```http
GET /api/profiles/{uuid}
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "uuid": "...",
    "name": "John",
    "interests": [...]
  }
}

Error 404:
{
  "message": "Failed to retrieve profile",
  "error": "Profile not found"
}
```

#### 4. Update Profile
```http
PUT /api/profiles/{uuid}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Name",
  "maturity_rating": "r",
  "pin": "5678",
  "interests": [2, 4, 6]
}

Response 200:
{
  "message": "Profile updated successfully",
  "data": {...}
}

Error 400:
{
  "message": "Failed to update profile",
  "error": "Cannot delete primary profile"
}
```

**Validation:**
- name: sometimes, string, max 255
- avatar_url: nullable, url
- maturity_rating: sometimes, in:all,pg,pg13,r,adult
- is_kids: sometimes, boolean
- pin: nullable, string, digits:4 (empty string to remove PIN)
- interests: nullable, array

#### 5. Delete Profile
```http
DELETE /api/profiles/{uuid}
Authorization: Bearer {token}

Response 200:
{
  "message": "Profile deleted successfully"
}

Error 400:
{
  "message": "Failed to delete profile",
  "error": "Cannot delete primary profile"
}
```

#### 6. Switch Profile (with PIN)
```http
POST /api/profiles/{uuid}/switch
Authorization: Bearer {token}
Content-Type: application/json

{
  "pin": "1234"  // Optional, required only if pin_enabled=true
}

Response 200:
{
  "message": "Profile switched successfully",
  "data": {
    "uuid": "...",
    "name": "Kids Profile",
    "interests": [...]
  }
}

Error 403:
{
  "message": "Failed to switch profile",
  "error": "Invalid PIN"
}
```

**Use Case:**
- Frontend calls this when user clicks on a profile
- If PIN is enabled, prompt for PIN
- Backend validates PIN and returns profile data
- Frontend stores profile UUID in session/state for subsequent API calls

#### 7. Profile Statistics
```http
GET /api/profiles/{uuid}/statistics
Authorization: Bearer {token}

Response 200:
{
  "data": {
    "total_watch_time": 18000,  // seconds
    "total_watched": 45,
    "favorites_count": 12,
    "continue_watching_count": 3,
    "interests_count": 5
  }
}
```

## Security Features

### PIN Protection
1. **PIN Hashing:**
   - PINs stored as SHA-256 hash
   - Never stored or transmitted in plain text
   - Uses `hash_equals()` for timing-safe comparison

2. **PIN Validation:**
   - Required only if `pin_enabled = true`
   - Validated in `switchProfile()` method
   - Returns 403 Forbidden on invalid PIN

3. **PIN Management:**
   - Set PIN during profile creation: `pin: "1234"`
   - Update PIN: `PUT /api/profiles/{uuid}` with `pin: "5678"`
   - Remove PIN: `PUT /api/profiles/{uuid}` with `pin: null`

### Authorization
1. **Profile Ownership:**
   - All operations verify profile belongs to user's account
   - Uses account_id comparison
   - Prevents cross-account access

2. **Primary Profile Protection:**
   - Cannot delete primary profile
   - Cannot modify `is_primary` flag
   - Enforced in service layer

3. **Profile Limits:**
   - Enforced from subscription package
   - Checked during profile creation
   - Default: 5 profiles

## Kids Profile Features

### Maturity Rating Filter
```php
// In Profile model
public function canView(string $contentRating): bool
{
    $ratings = ['all', 'pg', 'pg13', 'r', 'adult'];
    $profileLevel = array_search($this->maturity_rating, $ratings);
    $contentLevel = array_search($contentRating, $ratings);
    
    return $profileLevel >= $contentLevel;
}
```

**Usage:**
- Kids profiles should have `maturity_rating = 'all'` or `'pg'`
- Content API should filter based on current profile's rating
- Example: Kids profile (rating: pg) can only see content rated 'all' or 'pg'

### Recommended Flow
1. User creates kids profile:
   ```json
   {
     "name": "Kids",
     "is_kids": true,
     "maturity_rating": "pg",
     "pin": "1234"
   }
   ```

2. When switching to kids profile, frontend should:
   - Filter content by maturity rating
   - Show age-appropriate UI
   - Hide certain features (e.g., payment settings)

## Integration with Other APIs

### Content Discovery
When fetching content, filter by profile's maturity rating:

```php
// In ContentController
$profile = $request->profile; // Middleware sets current profile
$contentItems = ContentItem::where('status', 'published')
    ->where(function($query) use ($profile) {
        $query->where('maturity_rating', '<=', $profile->maturity_rating);
    })
    ->get();
```

### Watch History
Associate watch history with current profile:

```php
// In WatchHistoryController
$watchHistory = WatchHistory::create([
    'profile_id' => $request->profile_id,
    'content_item_id' => $request->content_id,
    // ...
]);
```

### Favorites
Associate favorites with current profile:

```php
// In FavoriteController
$favorite = Favorite::create([
    'profile_id' => $request->profile_id,
    'content_item_id' => $request->content_id,
]);
```

## Migration Commands

```bash
# Run migrations
php artisan migrate

# Rollback if needed
php artisan migrate:rollback --step=1
```

## Testing Checklist

- [ ] Create profile with PIN
- [ ] Create profile without PIN
- [ ] Switch to profile with correct PIN
- [ ] Switch to profile with wrong PIN (should fail)
- [ ] Switch to profile without PIN
- [ ] Update profile PIN
- [ ] Remove profile PIN
- [ ] Exceed profile limit (should fail)
- [ ] Delete non-primary profile
- [ ] Delete primary profile (should fail)
- [ ] Access another user's profile (should fail)
- [ ] Kids profile content filtering
- [ ] Profile statistics calculation

## Next Steps

After implementing Profile Management, the following APIs are recommended:

1. **Enhanced Content Discovery APIs**
   - Recommendations based on profile interests
   - Content filtering by profile maturity rating
   - Trending content

2. **Show/Episode APIs**
   - List shows with seasons and episodes
   - Episode details with video assets
   - Next episode suggestions

3. **Enhanced Playback APIs**
   - Secure video token generation
   - HLS streaming URLs
   - Multi-quality support
   - Subtitle/audio track selection

4. **Profile Context Middleware**
   - Create middleware to set current profile
   - Validate profile UUID from request header
   - Auto-filter content by profile rating
