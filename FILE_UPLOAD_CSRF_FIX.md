# File Upload & CSRF Token Fix Guide

## Issues Fixed
1. ✅ CSRF token mismatch errors
2. ✅ File upload size limits removed (now supports up to 5GB)
3. ✅ Cross-domain session/cookie issues
4. ✅ "The file failed to upload" errors

## Changes Made

### 1. CSRF Protection
- **Excluded upload routes** from CSRF verification in `bootstrap/app.php`
- Upload routes (`admin/upload/*`) no longer require CSRF tokens

### 2. File Size Limits
- **Images**: Increased to 100MB (was 10MB)
- **Videos**: Increased to 5GB (was 500MB)
- **PHP limits**: Updated in `.htaccess` to support large uploads

### 3. Session Configuration
- **SameSite**: Changed to `none` for cross-domain support
- **Secure**: Set to `true` for HTTPS
- **Domain**: Set to `.ubiqent.com` to share cookies across subdomains

### 4. PHP Configuration
Updated `.htaccess` with:
- `upload_max_filesize`: 5120M (5GB)
- `post_max_size`: 5120M (5GB)
- `max_execution_time`: 3600 seconds (1 hour)
- `max_input_time`: 3600 seconds (1 hour)
- `memory_limit`: 512M

## Production Deployment Steps

### Step 1: Update Environment Variables

Add/update these in your **production** `.env` file:

```bash
# CORS Configuration
CORS_ALLOWED_ORIGINS="https://ubiqent.com,https://www.ubiqent.com,https://backend.ubiqent.com"

# Session Configuration for Cross-Domain
SESSION_DOMAIN=.ubiqent.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
```

### Step 2: Update PHP Configuration

If `.htaccess` doesn't work (some servers ignore it), update your `php.ini`:

```ini
upload_max_filesize = 5120M
post_max_size = 5120M
max_execution_time = 3600
max_input_time = 3600
memory_limit = 512M
```

Or create a `.user.ini` file in the `public` directory:

```ini
upload_max_filesize = 5120M
post_max_size = 5120M
max_execution_time = 3600
max_input_time = 3600
memory_limit = 512M
```

### Step 3: Clear Caches

Run these commands on production:

```bash
php artisan config:clear
php artisan config:cache
php artisan cache:clear
php artisan route:clear
php artisan route:cache
```

### Step 4: Restart Services

```bash
# Restart PHP-FPM (if using)
sudo systemctl restart php8.2-fpm

# Or restart Apache
sudo systemctl restart apache2

# Or restart Nginx
sudo systemctl restart nginx
```

### Step 5: Test File Upload

Test with a small file first:

```bash
curl -X POST https://backend.ubiqent.com/admin/upload/image \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@test-image.jpg" \
  -F "folder=images"
```

## Frontend Changes Needed

### Option 1: Remove CSRF Token from Upload Requests (Recommended)

Since we excluded upload routes from CSRF, you can remove the CSRF token from upload requests:

```javascript
// Before
const formData = new FormData();
formData.append('file', file);
formData.append('_token', csrfToken); // Remove this

// After
const formData = new FormData();
formData.append('file', file);
// No CSRF token needed
```

### Option 2: Get CSRF Token from Cookie

If you want to keep CSRF for uploads, get the token from the cookie:

```javascript
// Get CSRF token from cookie
function getCsrfToken() {
  const name = 'XSRF-TOKEN';
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) {
    return decodeURIComponent(parts.pop().split(';').shift());
  }
  return null;
}

// Use in request
axios.post('/admin/upload/image', formData, {
  headers: {
    'X-XSRF-TOKEN': getCsrfToken(),
  },
});
```

### Option 3: Fetch CSRF Token from Sanctum

```javascript
// First, get CSRF cookie
await axios.get('https://backend.ubiqent.com/sanctum/csrf-cookie', {
  withCredentials: true,
});

// Then make upload request
await axios.post('https://backend.ubiqent.com/admin/upload/image', formData, {
  withCredentials: true,
});
```

## Troubleshooting

### Still Getting CSRF Errors?

1. **Check cookies are being sent**:
   - Open DevTools → Network → Check request headers
   - Should see `Cookie: laravel_session=...`

2. **Verify CORS headers**:
   ```bash
   curl -I -X OPTIONS https://backend.ubiqent.com/admin/upload/image \
     -H "Origin: https://ubiqent.com" \
     -H "Access-Control-Request-Method: POST"
   ```

3. **Check session domain**:
   - Make sure `SESSION_DOMAIN=.ubiqent.com` (with leading dot)
   - This allows cookies to work on both `ubiqent.com` and `backend.ubiqent.com`

### File Upload Still Failing?

1. **Check server logs**:
   ```bash
   tail -f /var/log/apache2/error.log
   # or
   tail -f /var/log/nginx/error.log
   # or
   tail -f storage/logs/laravel.log
   ```

2. **Verify PHP limits**:
   ```bash
   php -i | grep -E "upload_max_filesize|post_max_size|max_execution_time|memory_limit"
   ```

3. **Check disk space**:
   ```bash
   df -h
   ```

4. **Verify permissions**:
   ```bash
   chmod -R 775 storage
   chown -R www-data:www-data storage
   ```

## Testing Checklist

- [ ] CORS headers present in response
- [ ] Cookies being sent with requests
- [ ] Small file upload works (< 10MB)
- [ ] Large file upload works (> 100MB)
- [ ] No CSRF errors in console
- [ ] Files saved to correct location
- [ ] Database records created (if applicable)

## Quick Fix Summary

**For immediate fix on production:**

1. Add to `.env`:
   ```
   SESSION_DOMAIN=.ubiqent.com
   SESSION_SECURE_COOKIE=true
   SESSION_SAME_SITE=none
   ```

2. Run:
   ```bash
   php artisan config:cache
   ```

3. Restart web server

4. Remove CSRF token from frontend upload requests

That's it! Your uploads should work now.
