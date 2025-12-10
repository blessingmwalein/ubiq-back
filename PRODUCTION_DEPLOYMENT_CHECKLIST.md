# Production Deployment - Quick Checklist

## 🚀 Immediate Actions Required

### 1. Update Production `.env`

```bash
# CORS - Allow frontend to access backend
CORS_ALLOWED_ORIGINS="https://ubiqent.com,https://www.ubiqent.com,https://backend.ubiqent.com"

# Session - Fix CSRF and cross-domain issues
SESSION_DOMAIN=.ubiqent.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none

# App Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://backend.ubiqent.com
```

### 2. Clear All Caches

```bash
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan cache:clear
php artisan view:clear
```

### 3. Restart Web Server

```bash
# For Apache
sudo systemctl restart apache2

# For Nginx + PHP-FPM
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### 4. Verify Upload Limits

Visit: `https://backend.ubiqent.com/check-upload-limits.php`

Should show:
- ✅ upload_max_filesize: 5120M
- ✅ post_max_size: 5120M
- ✅ max_execution_time: 3600
- ✅ memory_limit: 512M

**DELETE the check file after verification!**

```bash
rm public/check-upload-limits.php
```

### 5. Test CORS

```bash
curl -I -X OPTIONS https://backend.ubiqent.com/api/auth/login \
  -H "Origin: https://ubiqent.com" \
  -H "Access-Control-Request-Method: POST"
```

Should see:
```
Access-Control-Allow-Origin: https://ubiqent.com
Access-Control-Allow-Credentials: true
```

### 6. Test File Upload

```bash
# Create a test image
curl -X POST https://backend.ubiqent.com/admin/upload/image \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@test.jpg" \
  -F "folder=test"
```

## 📋 Issues Fixed

| Issue | Solution | Status |
|-------|----------|--------|
| CORS errors | Added CORS_ALLOWED_ORIGINS | ✅ |
| CSRF token mismatch | Session domain + SameSite=none | ✅ |
| File upload fails | Excluded from CSRF + increased limits | ✅ |
| Size limit errors | Increased to 5GB for videos, 100MB for images | ✅ |

## 🔧 If Issues Persist

### CORS Still Failing?
1. Check `config/cors.php` is cached: `php artisan config:cache`
2. Verify environment variable: `php artisan tinker` → `config('cors.allowed_origins')`
3. Check Apache/Nginx isn't overriding headers

### CSRF Still Failing?
1. Verify cookies are being sent (check DevTools → Network → Cookies)
2. Check session domain: `echo env('SESSION_DOMAIN')` should be `.ubiqent.com`
3. Ensure frontend is using `withCredentials: true` in axios

### Upload Still Failing?
1. Check PHP limits: `php -i | grep upload_max_filesize`
2. Check disk space: `df -h`
3. Check permissions: `ls -la storage/`
4. Check logs: `tail -f storage/logs/laravel.log`

## 📱 Frontend Changes

Remove CSRF token from upload requests (it's now excluded):

```javascript
// ❌ OLD - Don't do this
formData.append('_token', csrfToken);

// ✅ NEW - Just send the file
const formData = new FormData();
formData.append('file', file);
formData.append('folder', 'images');

axios.post('/admin/upload/image', formData, {
  withCredentials: true, // Important for cookies
});
```

## ✅ Final Verification

- [ ] Login works from https://ubiqent.com
- [ ] No CORS errors in browser console
- [ ] No CSRF errors in browser console
- [ ] Can upload small images (< 10MB)
- [ ] Can upload large videos (> 100MB)
- [ ] Files appear in storage/app/public/
- [ ] Database records created correctly

## 🆘 Emergency Rollback

If something breaks:

```bash
# Restore old session settings
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

# Clear cache
php artisan config:clear
php artisan config:cache

# Restart
sudo systemctl restart apache2
```

## 📞 Support

Check these files for detailed info:
- `CORS_DEPLOYMENT_FIX.md` - CORS configuration details
- `FILE_UPLOAD_CSRF_FIX.md` - Upload and CSRF fixes
- `storage/logs/laravel.log` - Application logs
- `/var/log/apache2/error.log` - Apache errors
- `/var/log/nginx/error.log` - Nginx errors
