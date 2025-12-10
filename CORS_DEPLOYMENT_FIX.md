# CORS Deployment Fix Guide

## Problem
CORS errors on production: `No 'Access-Control-Allow-Origin' header is present on the requested resource`

## Solution

### 1. Update Production Environment Variables

Add these to your **production** `.env` file on the server:

```bash
# CORS Configuration
CORS_ALLOWED_ORIGINS="https://ubiqent.com,https://www.ubiqent.com,https://backend.ubiqent.com"

# Session Configuration for Cross-Domain (fixes CSRF issues)
SESSION_DOMAIN=.ubiqent.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
```

### 2. Clear Configuration Cache on Production

After updating the `.env` file, run these commands on your production server:

```bash
php artisan config:clear
php artisan config:cache
php artisan cache:clear
```

### 3. Verify Apache/Nginx Configuration

#### If using Apache:
Make sure your `.htaccess` or Apache config doesn't override CORS headers. Check `public/.htaccess`.

#### If using Nginx:
Ensure your Nginx config doesn't have conflicting CORS headers. Remove any manual CORS headers if present.

### 4. Test the Fix

After deploying, test with:

```bash
curl -I -X OPTIONS https://backend.ubiqent.com/api/auth/login \
  -H "Origin: https://ubiqent.com" \
  -H "Access-Control-Request-Method: POST"
```

You should see these headers in the response:
- `Access-Control-Allow-Origin: https://ubiqent.com`
- `Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS`
- `Access-Control-Allow-Credentials: true`

### 5. Additional Domains

If you need to add more domains (staging, preview, etc.), update the `CORS_ALLOWED_ORIGINS` variable:

```bash
CORS_ALLOWED_ORIGINS="https://ubiqent.com,https://www.ubiqent.com,https://staging.ubiqent.com"
```

## What Changed

1. **config/cors.php**: Now reads from `CORS_ALLOWED_ORIGINS` environment variable
2. **.env**: Added `CORS_ALLOWED_ORIGINS` with all your domains
3. **.env.example**: Updated with the new variable for documentation

## Quick Deployment Checklist

- [ ] Update production `.env` with `CORS_ALLOWED_ORIGINS`
- [ ] Run `php artisan config:cache` on production
- [ ] Restart PHP-FPM/Apache if needed
- [ ] Test login from https://ubiqent.com
- [ ] Check browser console for CORS errors (should be gone)
